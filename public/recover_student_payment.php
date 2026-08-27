<?php
/**
 * Standalone Emergency Tool: Pemulihan Pembayaran Siswa (Jobs Scanner & Manual Student Recovery)
 * Access URL: https://perguruanpembda.com/recover_student_payment.php?secret=pembda99
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$secret = $_REQUEST['secret'] ?? '';
$VALID_SECRET = 'pembda99';

if ($secret !== $VALID_SECRET) {
    http_response_code(403);
    die('403 Forbidden - Token Secret Salah');
}

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

$searchQuery = $_REQUEST['q'] ?? '';
$action = $_REQUEST['action'] ?? '';
$message = '';
$restoredFromJobs = 0;

// 1. Process Jobs Table Auto-Recovery if jobs table has queued activity logs
try {
    $jobs = DB::table('jobs')->get();
    foreach ($jobs as $j) {
        $payload = json_decode($j->payload, true);
        $command = $payload['data']['command'] ?? null;
        
        if ($command && str_contains($command, 'ModelActivityLogged')) {
            // Extract attributes from serialized command string if possible
            if (preg_match('/"amount_paid";s:\d+:"([^"]+)".*?"student_id";i:(\d+)/s', $command, $matches)) {
                $amountPaid = (float)$matches[1];
                $studentId = (int)$matches[2];

                $exists = Payment::where('student_id', $studentId)->where('amount_paid', $amountPaid)->exists();
                if (!$exists) {
                    $bill = StudentBill::where('student_id', $studentId)->orderBy('id', 'desc')->first();
                    if ($bill) {
                        Payment::create([
                            'bill_id' => $bill->id,
                            'student_id' => $studentId,
                            'amount_paid' => $amountPaid,
                            'payment_method' => 'cash',
                            'receipt_number' => 'KWT-RESTORE-' . time() . '-' . rand(10, 99),
                            'payment_date' => date('Y-m-d'),
                            'notes' => 'Restored from queued jobs payload',
                            'processed_by' => 1,
                            'is_verified' => true,
                        ]);
                        $restoredFromJobs++;
                    }
                }
            }
        }
    }
} catch (\Exception $e) {
    // Ignore jobs table errors if table not existing
}

// 2. Process Manual Student Payment Restoration Form
if ($action === 'restore_bill' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $billId = $_POST['bill_id'] ?? null;
    $amountPaid = $_POST['amount_paid'] ?? null;
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');

    if ($billId && $amountPaid) {
        $bill = StudentBill::with('student')->find($billId);
        if ($bill) {
            DB::transaction(function() use ($bill, $amountPaid, $paymentMethod, $paymentDate, &$message) {
                // Create payment record
                $pay = Payment::create([
                    'bill_id' => $bill->id,
                    'student_id' => $bill->student_id,
                    'amount_paid' => (float)$amountPaid,
                    'payment_method' => $paymentMethod,
                    'receipt_number' => 'KWT-RECOVER-' . date('Ymd') . '-' . rand(1000, 9999),
                    'payment_date' => $paymentDate,
                    'notes' => 'Restorasi manual pembayaran siswa (Bulan ' . ($bill->month ?? '-') . ')',
                    'processed_by' => auth()->id() ?? 1,
                    'is_verified' => true,
                ]);

                // Recalculate bill status
                $totalPaid = (float) Payment::where('bill_id', $bill->id)->where('is_verified', true)->sum('amount_paid');
                $bill->paid_amount = $totalPaid;
                if ($totalPaid >= $bill->amount) {
                    $bill->status = 'lunas';
                } elseif ($totalPaid > 0) {
                    $bill->status = 'cicilan';
                } else {
                    $bill->status = 'belum_bayar';
                }
                $bill->save();

                $stName = $bill->student->full_name ?? $bill->student_id;
                $message = "Berhasil memulihkan pembayaran untuk Siswa: {$stName} (Bulan " . ($bill->month ?? '-') . ") sebesar Rp " . number_format($amountPaid, 0, ',', '.') . "!";
            });
        }
    }
}

// 3. Search Students if query provided
$searchResultStudents = collect();
if (!empty($searchQuery)) {
    $searchResultStudents = Student::with(['school', 'bills.paymentType', 'bills.payments'])
        ->where('full_name', 'like', "%{$searchQuery}%")
        ->orWhere('nisn', 'like', "%{$searchQuery}%")
        ->take(15)
        ->get();
}

$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Pemulihan Pembayaran Siswa - PembdaHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-size: 13px; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="py-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fa-solid fa-user-check text-primary me-2"></i> Tool Emergency: Pemulihan Pembayaran Per Siswa</h3>
            <p class="text-muted mb-0">Cari nama siswa (contoh: <strong>CELESTE</strong>) untuk memulihkan / menandai lunas pembayaran bulan Agustus & bulan lainnya secara instan</p>
        </div>
        <a href="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembalike Tool Pembersih</a>
    </div>

    <?php if($restoredFromJobs > 0): ?>
    <div class="alert alert-success card-custom mb-4">
        <i class="fa-solid fa-circle-check me-2"></i> Berhasil otomatis memulihkan <strong><?= $restoredFromJobs ?> transaksi pembayaran</strong> dari antrean jobs!
    </div>
    <?php endif; ?>

    <?php if(!empty($message)): ?>
    <div class="alert alert-success alert-dismissible fade show card-custom mb-4" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <form action="recover_student_payment.php" method="GET" class="row g-3">
            <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
            <div class="col-md-9">
                <label class="form-label fw-bold text-secondary">Cari Nama Siswa / NISN:</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" class="form-control" placeholder="Ketik nama siswa (contoh: CELESTE NIBENIA OGAENA ZEGA)..." required>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search me-1"></i> Cari Data Siswa</button>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    <?php if(!empty($searchQuery)): ?>
        <?php if($searchResultStudents->isEmpty()): ?>
            <div class="card card-custom p-5 text-center bg-white">
                <i class="fa-solid fa-user-slash text-muted fs-1 mb-3"></i>
                <h5 class="fw-bold">Tidak ada siswa ditemukan dengan kata kunci "<?= htmlspecialchars($searchQuery) ?>"</h5>
                <p class="text-muted mb-0">Pastikan ejaan nama siswa sudah benar.</p>
            </div>
        <?php else: ?>
            <h5 class="fw-bold text-dark mb-3">Hasil Pencarian Siswa (<?= $searchResultStudents->count() ?> Siswa Ditemukan):</h5>
            
            <?php foreach($searchResultStudents as $st): ?>
            <div class="card card-custom mb-4 bg-white overflow-hidden">
                <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($st->full_name) ?></h6>
                        <div class="text-muted small">Unit: <?= htmlspecialchars($st->school->name ?? '-') ?> | NISN: <?= htmlspecialchars($st->nisn ?? '-') ?></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-uppercase">
                            <tr>
                                <th>Jenis Tagihan</th>
                                <th>Bulan / Periode</th>
                                <th>Jumlah Tagihan</th>
                                <th>Telah Dibayar</th>
                                <th>Status</th>
                                <th class="text-center">Aksi Pemulihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($st->bills->isEmpty()): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Siswa belum memiliki tagihan.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach($st->bills as $b): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($b->paymentType->type_name ?? 'Tagihan') ?></td>
                                <td><?= $b->month ? ($monthNames[$b->month] ?? $b->month) . ' ' . $b->year : 'Single' ?></td>
                                <td class="fw-bold">Rp <?= number_format($b->amount, 0, ',', '.') ?></td>
                                <td class="text-success fw-bold">Rp <?= number_format($b->paid_amount, 0, ',', '.') ?></td>
                                <td>
                                    <?php if($b->status === 'lunas'): ?>
                                        <span class="badge bg-success">LUNAS</span>
                                    <?php elseif($b->status === 'cicilan'): ?>
                                        <span class="badge bg-warning text-dark">CICILAN</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">BELUM DIBAYAR</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if($b->status !== 'lunas'): ?>
                                    <form action="recover_student_payment.php?secret=<?= urlencode($secret) ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan / menandai Lunas tagihan Bulan <?= $b->month ?> untuk <?= htmlspecialchars($st->full_name) ?>?');">
                                        <input type="hidden" name="action" value="restore_bill">
                                        <input type="hidden" name="bill_id" value="<?= $b->id ?>">
                                        <input type="hidden" name="amount_paid" value="<?= $b->amount - $b->paid_amount ?>">
                                        <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
                                        <button type="submit" class="btn btn-success btn-sm font-bold"><i class="fa-solid fa-rotate-left me-1"></i> Pulihkan & Tandai Lunas</button>
                                    </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="fa-solid fa-check-double text-success me-1"></i> Pembayaran Sudah Lunas</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
