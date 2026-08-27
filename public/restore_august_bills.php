<?php
/**
 * Standalone Script Emergency Tool: Restorasi / Re-Generasi Tagihan & Pembayaran Bulan Agustus
 * Access URL: https://perguruanpembda.com/restore_august_bills.php?secret=pembda99
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
use App\Models\PaymentType;
use App\Models\AcademicYear;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

$activeAy = AcademicYear::where('is_active', true)->first() ?? AcademicYear::orderBy('year', 'desc')->first();
$activeAyId = $activeAy ? $activeAy->id : 1;

$monthlyTypes = PaymentType::where('is_recurring', true)->where('is_active', true)->get();

$restoredBills = 0;
$log = [];

DB::transaction(function() use ($monthlyTypes, $activeAyId, &$restoredBills, &$log) {
    $students = Student::where('status', 'active')->orWhereNull('status')->get();
    
    foreach ($students as $st) {
        $schoolTypes = $monthlyTypes->where('school_id', $st->school_id);
        
        foreach ($schoolTypes as $pt) {
            // Check Month 8 (Agustus 2026)
            $exists = StudentBill::where('student_id', $st->id)
                ->where('payment_type_id', $pt->id)
                ->where('month', 8)
                ->exists();

            if (!$exists) {
                // Re-create Month 8 Bill
                $bill = StudentBill::create([
                    'student_id' => $st->id,
                    'payment_type_id' => $pt->id,
                    'academic_year_id' => $activeAyId,
                    'month' => 8,
                    'year' => 2026,
                    'amount' => $pt->amount,
                    'paid_amount' => 0,
                    'yayasan_share_amount' => $pt->yayasan_share_amount ?? $pt->amount,
                    'status' => 'belum_bayar',
                    'due_date' => '2026-08-10',
                ]);

                $restoredBills++;
                $log[] = "Restorasi Tagihan Bulan Agustus (Bulan 8) ID #{$bill->id} untuk Siswa: {$st->full_name} (NISN: {$st->nisn}), Jenis: {$pt->type_name}, Nominal: Rp " . number_format($pt->amount, 0, ',', '.');
            }
        }
    }

    // Re-sync paid_amount and status for all bills
    $allBills = StudentBill::all();
    foreach ($allBills as $b) {
        $totalPaid = (float) Payment::where('bill_id', $b->id)->where('is_verified', true)->sum('amount_paid');
        if ($totalPaid != $b->paid_amount) {
            $b->paid_amount = $totalPaid;
            if ($totalPaid >= $b->amount) {
                $b->status = 'lunas';
            } elseif ($totalPaid > 0) {
                $b->status = 'cicilan';
            } else {
                $b->status = 'belum_bayar';
            }
            $b->save();
        }
    }
});

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restorasi Tagihan Agustus - PembdaHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light py-5">
<div class="container">
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <h3 class="fw-bold text-success mb-3"><i class="fa-solid fa-rotate-left me-2"></i> Restorasi Tagihan Bulan Agustus Berhasil!</h3>
        <p class="text-muted">Sistem telah memverifikasi seluruh data siswa dan secara otomatis memulihkan <strong><?= $restoredBills ?> tagihan Bulan Agustus</strong> yang sempat terhapus.</p>
        
        <div class="alert alert-info font-monospace small" style="max-height: 350px; overflow-y: auto;">
            <h6 class="fw-bold mb-2">Log Restorasi Tagihan:</h6>
            <?php foreach($log as $l): ?>
                <div>&bull; <?= htmlspecialchars($l) ?></div>
            <?php endforeach; ?>
            <?php if(empty($log)): ?>
                <div>Seluruh tagihan bulan Agustus sudah lengkap dan aman di database.</div>
            <?php endif; ?>
        </div>

        <div class="mt-3">
            <a href="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" class="btn btn-primary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Tool Emergency</a>
        </div>
    </div>
</div>
</body>
</html>
