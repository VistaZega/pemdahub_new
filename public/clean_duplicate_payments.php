<?php
/**
 * Standalone Script Emergency Tool: Pembersihan Pembayaran Ganda (Double Payment Cleanup)
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
$selectedPaymentIds = $_POST['payment_ids'] ?? [];

use App\Models\Payment;
use App\Models\StudentBill;
use Illuminate\Support\Facades\DB;

/**
 * Helper: Detect all duplicate payment groups (by Bill ID, by Student+PaymentType+Period, or by Student+Amount+Date)
 */
function getDuplicatePaymentGroups() {
    // 1. Duplicate payments sharing the exact same bill_id
    $byBillId = Payment::whereNotNull('bill_id')
        ->groupBy('bill_id')
        ->havingRaw('COUNT(*) > 1')
        ->pluck('bill_id')
        ->toArray();

    // 2. Duplicate payments for same student + same payment_type_id + same academic_year + same month/year
    $byTypePeriod = DB::table('payments')
        ->join('student_bills', 'payments.bill_id', '=', 'student_bills.id')
        ->select('payments.student_id', 'student_bills.payment_type_id', 'student_bills.academic_year_id', 'student_bills.month', 'student_bills.year')
        ->groupBy('payments.student_id', 'student_bills.payment_type_id', 'student_bills.academic_year_id', 'student_bills.month', 'student_bills.year')
        ->havingRaw('COUNT(payments.id) > 1')
        ->get();

    // 3. Duplicate payments for same student + same amount_paid + same payment_date
    $byStudentAmountDate = Payment::select('student_id', 'amount_paid', DB::raw('DATE(payment_date) as pdate'))
        ->groupBy('student_id', 'amount_paid', DB::raw('DATE(payment_date)'))
        ->havingRaw('COUNT(*) > 1')
        ->get();

    // Collect all payment IDs involved in duplicates
    $duplicatePaymentIds = collect();

    // Add payments from Criteria 1
    if (!empty($byBillId)) {
        $ids1 = Payment::whereIn('bill_id', $byBillId)->pluck('id');
        $duplicatePaymentIds = $duplicatePaymentIds->merge($ids1);
    }

    // Add payments from Criteria 2
    foreach ($byTypePeriod as $row) {
        $ids2 = Payment::whereHas('bill', function($q) use ($row) {
                $q->where('payment_type_id', $row->payment_type_id)
                  ->where('academic_year_id', $row->academic_year_id)
                  ->where('month', $row->month)
                  ->where('year', $row->year);
            })
            ->where('student_id', $row->student_id)
            ->pluck('id');
        $duplicatePaymentIds = $duplicatePaymentIds->merge($ids2);
    }

    // Add payments from Criteria 3
    foreach ($byStudentAmountDate as $row) {
        $ids3 = Payment::where('student_id', $row->student_id)
            ->where('amount_paid', $row->amount_paid)
            ->whereDate('payment_date', $row->pdate)
            ->pluck('id');
        $duplicatePaymentIds = $duplicatePaymentIds->merge($ids3);
    }

    $allDuplicateIds = $duplicatePaymentIds->unique()->values();

    if ($allDuplicateIds->isEmpty()) {
        return collect();
    }

    // Fetch full Payment records
    $payments = Payment::with(['student.school', 'bill.paymentType', 'processedBy'])
        ->whereIn('id', $allDuplicateIds)
        ->orderBy('student_id')
        ->orderBy('created_at', 'asc')
        ->get();

    // Group payments logically by student_id + payment_type_id (or bill_id / date)
    $grouped = $payments->groupBy(function($p) {
        $typeId = $p->bill->paymentType->id ?? 'no_bill';
        $month = $p->bill->month ?? 'no_month';
        $year = $p->bill->year ?? 'no_year';
        return $p->student_id . '_' . $typeId . '_' . $month . '_' . $year;
    });

    // Filter only groups that have > 1 payment record
    return $grouped->filter(function($group) {
        return $group->count() > 1;
    });
}

$executionLog = [];

if ($action === 'execute' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $deletedPaymentCount = 0;
    $deletedBillCount = 0;
    $affectedBillIds = collect();

    $groupsToProcess = getDuplicatePaymentGroups();

    DB::transaction(function () use ($selectedPaymentIds, $groupsToProcess, &$deletedPaymentCount, &$deletedBillCount, &$affectedBillIds, &$executionLog) {
        if (!empty($selectedPaymentIds)) {
            // Delete specific checked payment IDs
            $paymentsToDelete = Payment::whereIn('id', $selectedPaymentIds)->get();
            foreach ($paymentsToDelete as $p) {
                if ($p->bill_id) {
                    $affectedBillIds->push($p->bill_id);
                }
                $typeName = $p->bill->paymentType->type_name ?? 'Tagihan';
                $executionLog[] = "Menghapus pembayaran ID {$p->id} (Kwitansi: {$p->receipt_number}, Jenis: {$typeName}, Siswa ID: {$p->student_id}, Nominal: Rp " . number_format($p->amount_paid, 0, ',', '.') . ")";
                $p->delete();
                $deletedPaymentCount++;
            }
        } else {
            // Delete all duplicates automatically (keep first payment in each group)
            foreach ($groupsToProcess as $groupKey => $payments) {
                if ($payments->count() <= 1) continue;

                $firstPayment = $payments->first();
                $duplicatePayments = $payments->slice(1);

                foreach ($duplicatePayments as $dup) {
                    if ($dup->bill_id) {
                        $affectedBillIds->push($dup->bill_id);
                    }
                    $typeName = $dup->bill->paymentType->type_name ?? 'Tagihan';
                    $executionLog[] = "Menghapus duplikat ID {$dup->id} (Kwitansi: {$dup->receipt_number}, Jenis: {$typeName}, Siswa ID: {$dup->student_id}, Nominal: Rp " . number_format($dup->amount_paid, 0, ',', '.') . ")";
                    
                    $dupBillId = $dup->bill_id;
                    $dup->delete();
                    $deletedPaymentCount++;

                    // If duplicate bill has 0 payments left and is NOT the first bill, clean up redundant bill record
                    if ($dupBillId && $dupBillId != $firstPayment->bill_id) {
                        $remainingPayments = Payment::where('bill_id', $dupBillId)->count();
                        if ($remainingPayments === 0) {
                            StudentBill::where('id', $dupBillId)->delete();
                            $deletedBillCount++;
                            $executionLog[] = "Menghapus tagihan duplikat tanpa pembayaran ID {$dupBillId}";
                        }
                    }
                }
                if ($firstPayment->bill_id) {
                    $affectedBillIds->push($firstPayment->bill_id);
                }
            }
        }

        // Recalculate bill status for all affected bills
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
                $executionLog[] = "Mengupdate status Tagihan ID {$bId} -> paid_amount: Rp " . number_format($bill->paid_amount, 0, ',', '.') . ", status: {$bill->status}";
            }
        }
    });
}

// Fetch current duplicate payment groups
$groupedDuplicates = getDuplicatePaymentGroups();

$totalDuplicateGroups = $groupedDuplicates->count();
$totalRedundantPayments = 0;
$totalExcessAmount = 0;

foreach($groupedDuplicates as $payments) {
    $totalRedundantPayments += ($payments->count() - 1);
    $totalExcessAmount += $payments->slice(1)->sum('amount_paid');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Emergency: Pembersihan Pembayaran Ganda PembdaHUB</title>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fa-solid fa-wrench text-danger me-2"></i> Script Emergency: Pembersihan Pembayaran Ganda</h3>
            <p class="text-muted mb-0">PembdaHUB Emergency Tool - Pembersihan Kas & Tagihan Ganda (SPP, Iuran OSIS, & Jenis Pembayaran Lain)</p>
        </div>
        <span class="badge bg-dark px-3 py-2">Token Secret Verified</span>
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
                <h5 class="fw-bold text-secondary mb-3">Hasil Pemindaian Transaksi Ganda (Semua Jenis Pembayaran)</h5>
                <div class="d-flex gap-4">
                    <div>
                        <div class="fs-3 fw-bold text-dark"><?= number_format($totalDuplicateGroups) ?></div>
                        <div class="text-muted small">Kelompok Transaksi Ganda</div>
                    </div>
                    <div class="border-end"></div>
                    <div>
                        <div class="fs-3 fw-bold text-danger"><?= number_format($totalRedundantPayments) ?></div>
                        <div class="text-muted small">Record Duplikat Ditampilkan</div>
                    </div>
                    <div class="border-end"></div>
                    <div>
                        <div class="fs-3 fw-bold text-primary">Rp <?= number_format($totalExcessAmount, 0, ',', '.') ?></div>
                        <div class="text-muted small">Total Uang Ganda Berlebih</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <?php if($totalDuplicateGroups > 0): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="selectAll(true)"><i class="fa-solid fa-check-double me-1"></i> Pilih Semua Duplikat</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($totalDuplicateGroups > 0): ?>
    <form action="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi pembayaran ganda yang dipilih?');">
        <input type="hidden" name="action" value="execute">
        
        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="checkMaster" onchange="toggleAll(this)">
                <label class="form-check-label fw-bold text-uppercase small" for="checkMaster">Centang Semua Transaksi Duplikat</label>
            </div>
            <button type="submit" class="btn btn-danger font-bold px-4"><i class="fa-solid fa-trash-can me-2"></i> Eksekusi Hapus Pembayaran Ganda</button>
        </div>

        <?php foreach($groupedDuplicates as $groupKey => $payments): ?>
        <?php
            $firstPay = $payments->first();
            $bill = $firstPay->bill;
            $student = $firstPay->student;
            $excessAmount = $payments->slice(1)->sum('amount_paid');
            $typeName = $bill->paymentType->type_name ?? 'Jenis Tagihan Non-Standard / Custom';
        ?>
        <div class="card card-custom mb-4 overflow-hidden bg-white">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <div>
                    <span class="badge bg-danger me-2">Ganda <?= $payments->count() ?>x</span>
                    <strong class="fs-6 text-dark"><?= htmlspecialchars($student->full_name ?? 'Siswa') ?></strong>
                    <span class="text-muted small ms-2">(Unit: <?= htmlspecialchars($student->school->name ?? '-') ?> | NISN: <?= htmlspecialchars($student->nisn ?? '-') ?>)</span>
                    <div class="text-muted small mt-1">
                        Jenis Tagihan: <strong class="text-primary"><?= htmlspecialchars($typeName) ?></strong> 
                        <?php if(!empty($bill->month)): ?> - Bulan <?= $bill->month ?>/<?= $bill->year ?><?php endif; ?>
                        <?php if($bill): ?> | Nominal Tagihan: <strong>Rp <?= number_format($bill->amount, 0, ',', '.') ?></strong><?php endif; ?>
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

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-danger btn-lg font-bold shadow"><i class="fa-solid fa-trash-can me-2"></i> Eksekusi Hapus Pembayaran Ganda</button>
        </div>
    </form>
    <?php else: ?>
    <div class="card card-custom p-5 text-center bg-white">
        <i class="fa-solid fa-circle-check text-success fs-1 mb-3"></i>
        <h4 class="fw-bold">Tidak Ada Transaksi Pembayaran Ganda Terdeteksi!</h4>
        <p class="text-muted">Seluruh data transaksi pembayaran kas & tagihan siswa di PembdaHUB (SPP, Iuran OSIS, dll) bersih dan konsisten.</p>
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
