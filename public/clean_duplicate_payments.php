<?php
/**
 * Standalone Script Emergency Tool: Pembersihan Tagihan & Pembayaran Ganda (Dual Cleanup)
 * Access URL: https://perguruanpembda.com/clean_duplicate_payments.php?secret=pembda99
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$secret = $_REQUEST['secret'] ?? '';
$VALID_SECRET = 'pembda99';

if ($secret !== $VALID_SECRET) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body style="font-family:sans-serif;text-align:center;padding:50px;">
    <h2 style="color:red;">403 Forbidden - Token Rahasia Tidak Valid</h2>
    <p>Akses ditolak. Silakan sertakan token secret yang benar (contoh: <code>?secret=pembda99</code>).</p>
    </body></html>');
}

$action = $_REQUEST['action'] ?? 'preview';
$filterTypeId = $_REQUEST['payment_type_id'] ?? 'all';
$selectedBillIds = $_POST['bill_ids'] ?? [];
$selectedPaymentIds = $_POST['payment_ids'] ?? [];

use App\Models\Payment;
use App\Models\StudentBill;
use App\Models\PaymentType;
use Illuminate\Support\Facades\DB;

// Ensure OSIS payment types are marked as recurring (monthly)
PaymentType::where('type_name', 'like', '%OSIS%')->update(['is_recurring' => true]);

$allPaymentTypes = PaymentType::orderBy('type_name')->get();

/**
 * Helper: Detect Duplicate Bills (student_bills)
 */
function getDuplicateBillGroups($typeId = 'all') {
    $rawDuplicates = StudentBill::select('student_id', 'payment_type_id', 'academic_year_id', 'month', 'year', DB::raw('COUNT(*) as total_count'))
        ->when($typeId !== 'all' && is_numeric($typeId), fn($q) => $q->where('payment_type_id', $typeId))
        ->groupBy('student_id', 'payment_type_id', 'academic_year_id', 'month', 'year')
        ->havingRaw('COUNT(*) > 1')
        ->get();

    $groups = collect();
    foreach ($rawDuplicates as $rd) {
        $bills = StudentBill::with(['student.school', 'paymentType', 'payments'])
            ->where('student_id', $rd->student_id)
            ->where('payment_type_id', $rd->payment_type_id)
            ->where('academic_year_id', $rd->academic_year_id)
            ->where(function($q) use ($rd) {
                if (is_null($rd->month)) {
                    $q->whereNull('month');
                } else {
                    $q->where('month', $rd->month);
                }
            })
            ->where(function($q) use ($rd) {
                if (is_null($rd->year)) {
                    $q->whereNull('year');
                } else {
                    $q->where('year', $rd->year);
                }
            })
            ->orderBy('id', 'asc')
            ->get();

        if ($bills->count() > 1) {
            $key = $rd->student_id . '_' . $rd->payment_type_id . '_' . $rd->academic_year_id . '_' . ($rd->month ?? '0') . '_' . ($rd->year ?? '0');
            $groups->put($key, $bills);
        }
    }

    return $groups;
}

/**
 * Helper: Detect Duplicate Payment Records (payments)
 */
function getDuplicatePaymentGroups($typeId = 'all') {
    $query = Payment::with(['student.school', 'bill.paymentType', 'processedBy'])
        ->whereHas('bill');

    if ($typeId !== 'all' && is_numeric($typeId)) {
        $query->whereHas('bill', fn($q) => $q->where('payment_type_id', $typeId));
    }

    $payments = $query->orderBy('student_id')->orderBy('created_at', 'asc')->get();

    if ($payments->isEmpty()) {
        return collect();
    }

    $grouped = $payments->groupBy(function($p) {
        $studentId = $p->student_id;
        $payTypeId = $p->bill->payment_type_id ?? 0;
        $ayId = $p->bill->academic_year_id ?? 0;
        $month = $p->bill->month ?? 0;
        $year = $p->bill->year ?? 0;
        $billId = $p->bill_id ?? 0;
        return $studentId . '_bill_' . $billId;
    });

    return $grouped->filter(function($group) {
        return $group->count() > 1;
    });
}

$executionLog = [];

if ($action === 'execute' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $deletedBillCount = 0;
    $deletedPaymentCount = 0;
    $affectedBillIds = collect();

    DB::transaction(function () use ($selectedBillIds, $selectedPaymentIds, $filterTypeId, &$deletedBillCount, &$deletedPaymentCount, &$affectedBillIds, &$executionLog) {
        // 1. Delete selected duplicate bills (student_bills)
        if (!empty($selectedBillIds)) {
            $billsToDelete = StudentBill::with(['student', 'paymentType', 'payments'])->whereIn('id', $selectedBillIds)->get();
            foreach ($billsToDelete as $b) {
                $stName = $b->student->full_name ?? $b->student_id;
                $tName = $b->paymentType->type_name ?? 'Tagihan';
                $period = $b->month ? "Bulan {$b->month}/{$b->year}" : 'Single';

                // Delete payments attached to this duplicate bill first
                foreach ($b->payments as $p) {
                    $executionLog[] = "Menghapus pembayaran attached ID {$p->id} (Kwitansi: {$p->receipt_number}) pada tagihan duplikat ID {$b->id}";
                    $p->delete();
                    $deletedPaymentCount++;
                }

                $executionLog[] = "Menghapus Tagihan Duplikat ID {$b->id} (Siswa: {$stName}, Jenis: {$tName}, Periode: {$period}, Nominal: Rp " . number_format($b->amount, 0, ',', '.') . ")";
                $b->delete();
                $deletedBillCount++;
            }
        }

        // 2. Delete selected duplicate payments (payments)
        if (!empty($selectedPaymentIds)) {
            $paymentsToDelete = Payment::whereIn('id', $selectedPaymentIds)->get();
            foreach ($paymentsToDelete as $p) {
                if ($p->bill_id) {
                    $affectedBillIds->push($p->bill_id);
                }
                $executionLog[] = "Menghapus Transaksi Pembayaran Duplikat ID {$p->id} (Kwitansi: {$p->receipt_number}, Nominal: Rp " . number_format($p->amount_paid, 0, ',', '.') . ")";
                $p->delete();
                $deletedPaymentCount++;
            }
        }

        // Auto cleanup if no specific items checked but action=execute
        if (empty($selectedBillIds) && empty($selectedPaymentIds)) {
            $dupBillGroups = getDuplicateBillGroups($filterTypeId);
            foreach ($dupBillGroups as $key => $bills) {
                if ($bills->count() <= 1) continue;
                $firstBill = $bills->first();
                $duplicateBills = $bills->slice(1);

                foreach ($duplicateBills as $dupB) {
                    $stName = $dupB->student->full_name ?? $dupB->student_id;
                    $tName = $dupB->paymentType->type_name ?? 'Tagihan';
                    $period = $dupB->month ? "Bulan {$dupB->month}/{$dupB->year}" : 'Single';

                    foreach ($dupB->payments as $p) {
                        $p->delete();
                        $deletedPaymentCount++;
                    }

                    $executionLog[] = "Menghapus Tagihan Duplikat ID {$dupB->id} (Siswa: {$stName}, Jenis: {$tName}, Periode: {$period})";
                    $dupB->delete();
                    $deletedBillCount++;
                }
            }

            $dupPayGroups = getDuplicatePaymentGroups($filterTypeId);
            foreach ($dupPayGroups as $key => $payments) {
                if ($payments->count() <= 1) continue;
                foreach ($payments->slice(1) as $dupP) {
                    if ($dupP->bill_id) $affectedBillIds->push($dupP->bill_id);
                    $executionLog[] = "Menghapus Transaksi Pembayaran Duplikat ID {$dupP->id} (Kwitansi: {$dupP->receipt_number})";
                    $dupP->delete();
                    $deletedPaymentCount++;
                }
            }
        }

        // Recalculate remaining bills
        $uniqueBillIds = $affectedBillIds->unique()->filter();
        foreach ($uniqueBillIds as $bId) {
            $bill = StudentBill::find($bId);
            if ($bill) {
                $totalPaid = (float) Payment::where('bill_id', $bId)->where('is_verified', true)->sum('amount_paid');
                $bill->paid_amount = $totalPaid;
                if ($bill->paid_amount >= $bill->amount) {
                    $bill->status = 'lunas';
                } elseif ($bill->paid_amount > 0) {
                    $bill->status = 'cicilan';
                } else {
                    $bill->status = 'belum_bayar';
                }
                $bill->save();
            }
        }
    });
}

// Fetch current duplicate bills and duplicate payments
$groupedDuplicateBills = getDuplicateBillGroups($filterTypeId);
$groupedDuplicatePayments = getDuplicatePaymentGroups($filterTypeId);

$totalDuplicateBillGroups = $groupedDuplicateBills->count();
$totalRedundantBills = 0;
$totalExcessBillAmount = 0;
foreach($groupedDuplicateBills as $bills) {
    $totalRedundantBills += ($bills->count() - 1);
    $totalExcessBillAmount += $bills->slice(1)->sum('amount');
}

$totalDuplicatePayGroups = $groupedDuplicatePayments->count();
$totalRedundantPayments = 0;
$totalExcessPayAmount = 0;
foreach($groupedDuplicatePayments as $payments) {
    $totalRedundantPayments += ($payments->count() - 1);
    $totalExcessPayAmount += $payments->slice(1)->sum('amount_paid');
}

$monthNames = [
    1 => 'Januari (Jul)', 2 => 'Februari (Agt)', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli (Jul)', 8 => 'Agustus (Agt)',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Emergency: Pembersihan Tagihan & Pembayaran Ganda PembdaHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-size: 13px; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .badge-sah { background-color: #d1fae5; color: #065f46; font-weight: 700; padding: 4px 8px; border-radius: 6px; }
        .badge-duplikat { background-color: #ffe4e6; color: #9f1239; font-weight: 700; padding: 4px 8px; border-radius: 6px; }
    </style>
</head>
<body class="py-4">
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fa-solid fa-wrench text-danger me-2"></i> Script Emergency: Pembersihan Tagihan & Pembayaran Ganda</h3>
            <p class="text-muted mb-0">PembdaHUB Emergency Tool - Pembersihan Tagihan Duplikat (Daftar Tagihan) & Transaksi Ganda</p>
        </div>
        <span class="badge bg-dark px-3 py-2">Token Secret Verified</span>
    </div>

    <!-- Filter Form -->
    <div class="card card-custom p-3 mb-4 bg-white">
        <form action="clean_duplicate_payments.php" method="GET" class="row g-3 align-items-center">
            <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
            
            <div class="col-md-9">
                <label class="form-label fw-bold text-secondary mb-1">Filter Jenis Tagihan:</label>
                <select name="payment_type_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $filterTypeId === 'all' ? 'selected' : '' ?>>-- Semua Jenis Tagihan (Iuran OSIS, SPP, Uang Pangkal, dll) --</option>
                    <?php foreach($allPaymentTypes as $pt): ?>
                        <option value="<?= $pt->id ?>" <?= (string)$filterTypeId === (string)$pt->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pt->type_name) ?> (Unit ID: <?= $pt->school_id ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-search me-1"></i> Pindai Ulang Database</button>
            </div>
        </form>
    </div>

    <?php if (!empty($executionLog)): ?>
    <div class="alert alert-success alert-dismissible fade show card-custom mb-4" role="alert">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-check-circle me-2"></i> Eksekusi Pembersihan Berhasil!</h5>
        <hr>
        <div style="max-height: 250px; overflow-y: auto;" class="font-monospace small">
            <?php foreach($executionLog as $log): ?>
                <div>&bull; <?= htmlspecialchars($log) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary Box -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold text-secondary mb-3">Hasil Pemindaian Tagihan & Transaksi Ganda</h5>
                <div class="d-flex gap-4">
                    <div>
                        <div class="fs-3 fw-bold text-danger"><?= number_format($totalRedundantBills) ?></div>
                        <div class="text-muted small">Record Tagihan Duplikat</div>
                    </div>
                    <div class="border-end"></div>
                    <div>
                        <div class="fs-3 fw-bold text-warning"><?= number_format($totalRedundantPayments) ?></div>
                        <div class="text-muted small">Record Pembayaran Ganda</div>
                    </div>
                    <div class="border-end"></div>
                    <div>
                        <div class="fs-3 fw-bold text-primary">Rp <?= number_format($totalExcessBillAmount, 0, ',', '.') ?></div>
                        <div class="text-muted small">Nominal Tagihan Berlebih</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <?php if($totalRedundantBills > 0 || $totalRedundantPayments > 0): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="selectAll(true)"><i class="fa-solid fa-check-double me-1"></i> Centang Semua Duplikat</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($totalRedundantBills > 0 || $totalRedundantPayments > 0): ?>
    <form action="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tagihan dan pembayaran ganda yang dipilih? Tagihan utama yang sah tetap aman.');">
        <input type="hidden" name="action" value="execute">
        <input type="hidden" name="payment_type_id" value="<?= htmlspecialchars($filterTypeId) ?>">
        
        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="checkMaster" onchange="toggleAll(this)">
                <label class="form-check-label fw-bold text-uppercase small" for="checkMaster">Centang Semua Item Duplikat Untuk Dihapus</label>
            </div>
            <button type="submit" class="btn btn-danger font-bold px-4"><i class="fa-solid fa-trash-can me-2"></i> Eksekusi Hapus Tagihan & Pembayaran Ganda</button>
        </div>

        <!-- SECTION 1: DUPLICATE BILLS (student_bills) -->
        <?php if($totalRedundantBills > 0): ?>
        <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-file-invoice-dollar text-danger me-2"></i> 1. Pembersihan Tagihan Duplikat (Daftar Tagihan Siswa)</h4>
        
        <?php foreach($groupedDuplicateBills as $groupKey => $bills): ?>
        <?php
            $firstBill = $bills->first();
            $student = $firstBill->student;
            $paymentType = $firstBill->paymentType;
            $excessAmount = $bills->slice(1)->sum('amount');
            $monthStr = $firstBill->month ? ($monthNames[$firstBill->month] ?? $firstBill->month) : 'Single';
        ?>
        <div class="card card-custom mb-4 overflow-hidden bg-white">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <div>
                    <span class="badge bg-danger me-2">Duplikat Tagihan <?= $bills->count() ?>x</span>
                    <strong class="fs-6 text-dark"><?= htmlspecialchars($student->full_name ?? 'Siswa') ?></strong>
                    <span class="text-muted small ms-2">(Unit: <?= htmlspecialchars($student->school->name ?? '-') ?> | NISN: <?= htmlspecialchars($student->nisn ?? '-') ?>)</span>
                    <div class="text-muted small mt-1">
                        Jenis Tagihan: <strong class="text-primary"><?= htmlspecialchars($paymentType->type_name ?? 'Tagihan') ?></strong> 
                        | Periode: <strong><?= $monthStr ?> / <?= $firstBill->year ?></strong>
                        | Nominal Per Tagihan: <strong>Rp <?= number_format($firstBill->amount, 0, ',', '.') ?></strong>
                    </div>
                </div>
                <div class="text-end">
                    <span class="text-muted small block">Nominal Tagihan Berlebih:</span>
                    <div class="fw-bold text-danger fs-6">Rp <?= number_format($excessAmount, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th class="text-center" style="width: 50px;">Pilih</th>
                            <th>Status Tagihan</th>
                            <th>Tagihan ID</th>
                            <th>Status Bayar</th>
                            <th>Dibuat Tanggal</th>
                            <th class="text-end">Jumlah Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bills as $idx => $b): ?>
                        <tr class="<?= $idx == 0 ? 'table-success' : 'table-danger' ?>">
                            <td class="text-center">
                                <?php if($idx == 0): ?>
                                    <i class="fa-solid fa-lock text-success" title="Tagihan Utama Sah"></i>
                                <?php else: ?>
                                    <input type="checkbox" name="bill_ids[]" value="<?= $b->id ?>" class="form-check-input item-check">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($idx == 0): ?>
                                    <span class="badge-sah"><i class="fa-solid fa-check me-1"></i> Utama (Sah)</span>
                                <?php else: ?>
                                    <span class="badge-duplikat"><i class="fa-solid fa-copy me-1"></i> Tagihan Duplikat Ke-<?= $idx ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="font-monospace">#<?= $b->id ?></td>
                            <td>
                                <?php if($b->status === 'lunas'): ?>
                                    <span class="badge bg-success">LUNAS</span>
                                <?php elseif($b->status === 'cicilan'): ?>
                                    <span class="badge bg-warning text-dark">DIBAYAR SEBAGIAN</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">BELUM DIBAYAR</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $b->created_at ? $b->created_at->format('d/m/Y H:i:s') : '-' ?></td>
                            <td class="text-end fw-bold <?= $idx == 0 ? 'text-success' : 'text-danger' ?>">
                                Rp <?= number_format($b->amount, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- SECTION 2: DUPLICATE PAYMENTS (payments) -->
        <?php if($totalRedundantPayments > 0): ?>
        <h4 class="fw-bold text-dark mb-3 mt-5"><i class="fa-solid fa-receipt text-warning me-2"></i> 2. Pembersihan Transaksi Pembayaran Ganda</h4>
        
        <?php foreach($groupedDuplicatePayments as $groupKey => $payments): ?>
        <?php
            $firstPay = $payments->first();
            $bill = $firstPay->bill;
            $student = $firstPay->student;
            $excessAmount = $payments->slice(1)->sum('amount_paid');
            $typeName = $bill->paymentType->type_name ?? 'Tagihan';
        ?>
        <div class="card card-custom mb-4 overflow-hidden bg-white">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <div>
                    <span class="badge bg-danger me-2">Ganda {{ $payments->count() }}x</span>
                    <strong class="fs-6 text-dark"><?= htmlspecialchars($student->full_name ?? 'Siswa') ?></strong>
                    <span class="text-muted small ms-2">(Unit: <?= htmlspecialchars($student->school->name ?? '-') ?> | NISN: <?= htmlspecialchars($student->nisn ?? '-') ?>)</span>
                    <div class="text-muted small mt-1">
                        Jenis Tagihan: <strong class="text-primary"><?= htmlspecialchars($typeName) ?></strong> 
                        <?php if(!empty($bill->month)): ?> - Bulan <?= $bill->month ?>/<?= $bill->year ?><?php endif; ?>
                    </div>
                </div>
                <div class="text-end">
                    <span class="text-muted small block">Nominal Berlebih:</span>
                    <div class="fw-bold text-danger fs-6">Rp <?= number_format($excessAmount, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th class="text-center" style="width: 50px;">Pilih</th>
                            <th>Status Record</th>
                            <th>Waktu Transaksi</th>
                            <th>No. Kwitansi / Ref</th>
                            <th>Metode Bayar</th>
                            <th>Diproses Oleh</th>
                            <th class="text-end">Jumlah Dibayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $idx => $p): ?>
                        <tr class="<?= $idx == 0 ? 'table-success' : 'table-danger' ?>">
                            <td class="text-center">
                                <?php if($idx == 0): ?>
                                    <i class="fa-solid fa-lock text-success" title="Pembayaran Utama Sah"></i>
                                <?php else: ?>
                                    <input type="checkbox" name="payment_ids[]" value="<?= $p->id ?>" class="form-check-input item-check">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($idx == 0): ?>
                                    <span class="badge-sah"><i class="fa-solid fa-check me-1"></i> Utama (Sah)</span>
                                <?php else: ?>
                                    <span class="badge-duplikat"><i class="fa-solid fa-copy me-1"></i> Duplikat Ke-<?= $idx ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $p->created_at ? $p->created_at->format('d/m/Y H:i:s') : '-' ?></td>
                            <td class="font-monospace"><?= htmlspecialchars($p->receipt_number ?? $p->reference_number ?? '-') ?></td>
                            <td class="text-uppercase fw-bold"><?= htmlspecialchars($p->payment_method) ?></td>
                            <td><?= htmlspecialchars($p->processedBy->full_name ?? 'Sistem') ?></td>
                            <td class="text-end fw-bold <?= $idx == 0 ? 'text-success' : 'text-danger' ?>">
                                Rp <?= number_format($p->amount_paid, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="d-flex justify-content-end mb-5 mt-4">
            <button type="submit" class="btn btn-danger btn-lg font-bold shadow"><i class="fa-solid fa-trash-can me-2"></i> Eksekusi Hapus Tagihan & Pembayaran Ganda</button>
        </div>
    </form>
    <?php else: ?>
    <div class="card card-custom p-5 text-center bg-white">
        <i class="fa-solid fa-circle-check text-success fs-1 mb-3"></i>
        <h4 class="fw-bold">Tidak Ada Tagihan Maupun Pembayaran Ganda Terdeteksi!</h4>
        <p class="text-muted">Seluruh data tagihan dan transaksi pembayaran kas siswa di PembdaHUB bersih dan konsisten.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleAll(master) {
    const checks = document.querySelectorAll('.item-check');
    checks.forEach(c => c.checked = master.checked);
}
function selectAll(state) {
    const master = document.getElementById('checkMaster');
    if (master) master.checked = state;
    toggleAll({ checked: state });
}
</script>
</body>
</html>
