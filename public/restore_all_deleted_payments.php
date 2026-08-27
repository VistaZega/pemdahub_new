<?php
/**
 * Standalone Emergency Tool: Pemulihan / Restorasi Seluruh Pembayaran Terhapus dari Activity Logs
 * Access URL: https://perguruanpembda.com/restore_all_deleted_payments.php?secret=pembda99
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

use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\StudentBill;
use App\Models\Student;
use App\Models\PaymentType;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

$restoredPaymentCount = 0;
$restoredBillCount = 0;
$log = [];

$activeAy = AcademicYear::where('is_active', true)->first() ?? AcademicYear::orderBy('year', 'desc')->first();
$activeAyId = $activeAy ? $activeAy->id : 1;

DB::transaction(function() use ($activeAyId, &$restoredPaymentCount, &$restoredBillCount, &$log) {
    // 1. Scan Activity Logs for deleted payments or created payments
    $logs = ActivityLog::where('model_type', 'App\\Models\\Payment')
        ->whereIn('action', ['deleted', 'created'])
        ->orderBy('id', 'asc')
        ->get();

    foreach ($logs as $l) {
        $data = json_decode($l->changes, true);
        if (!$data || !is_array($data)) {
            continue;
        }

        $studentId = $data['student_id'] ?? null;
        $amountPaid = $data['amount_paid'] ?? null;
        $paymentDate = $data['payment_date'] ?? $data['created_at'] ?? date('Y-m-d');
        $receiptNumber = $data['receipt_number'] ?? null;
        $billId = $data['bill_id'] ?? null;

        if (!$studentId || !$amountPaid) {
            continue;
        }

        // Check if payment currently exists in DB
        $existsQuery = Payment::where('student_id', $studentId)
            ->where('amount_paid', $amountPaid);
        
        if ($receiptNumber) {
            $existsQuery->where('receipt_number', $receiptNumber);
        } else {
            $existsQuery->whereDate('created_at', substr($paymentDate, 0, 10));
        }

        if ($existsQuery->exists()) {
            continue; // Payment already exists, skip
        }

        // Verify or recover student bill
        $targetBill = null;
        if ($billId) {
            $targetBill = StudentBill::find($billId);
        }

        if (!$targetBill) {
            // Find bill by student_id & amount or create fallback bill
            $targetBill = StudentBill::where('student_id', $studentId)
                ->where('amount', '>=', $amountPaid)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$targetBill) {
            // Find default payment type for student's school
            $student = Student::find($studentId);
            $schoolId = $student->school_id ?? 2;
            $pType = PaymentType::where('school_id', $schoolId)->first();

            if ($student && $pType) {
                $targetBill = StudentBill::create([
                    'student_id' => $studentId,
                    'payment_type_id' => $pType->id,
                    'academic_year_id' => $activeAyId,
                    'month' => date('n', strtotime($paymentDate)),
                    'year' => date('Y', strtotime($paymentDate)),
                    'amount' => $amountPaid,
                    'paid_amount' => 0,
                    'yayasan_share_amount' => $pType->yayasan_share_amount ?? $amountPaid,
                    'status' => 'belum_bayar',
                    'due_date' => date('Y-m-d', strtotime($paymentDate)),
                ]);
                $restoredBillCount++;
                $log[] = "Memulihkan Record Tagihan ID #{$targetBill->id} untuk Pembayaran Siswa ID {$studentId}";
            }
        }

        // Re-insert Payment
        $newPay = Payment::create([
            'bill_id' => $targetBill->id ?? null,
            'student_id' => $studentId,
            'amount_paid' => $amountPaid,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'qris_transaction_id' => $data['qris_transaction_id'] ?? null,
            'qris_status' => $data['qris_status'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'receipt_number' => $receiptNumber ?? ('KWT-REC-' . time() . '-' . rand(100, 999)),
            'payment_date' => $paymentDate,
            'notes' => $data['notes'] ?? 'Restored from activity log',
            'processed_by' => $data['processed_by'] ?? 1,
            'is_verified' => $data['is_verified'] ?? true,
        ]);

        $restoredPaymentCount++;
        $studentName = Student::find($studentId)->full_name ?? "ID #{$studentId}";
        $log[] = "BERHASIL RESTORASI PEMBAYARAN ID #{$newPay->id} (Siswa: {$studentName}, Nominal: Rp " . number_format($amountPaid, 0, ',', '.') . ", Kwitansi: {$newPay->receipt_number}, Tgl: {$paymentDate})";
    }

    // 2. Re-sync all bill paid_amount & status
    $allBills = StudentBill::all();
    foreach ($allBills as $b) {
        $totalPaid = (float) Payment::where('bill_id', $b->id)->sum('amount_paid');
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
    <title>Restorasi Seluruh Pembayaran Terhapus - PembdaHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light py-5">
<div class="container">
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <h3 class="fw-bold text-success mb-3"><i class="fa-solid fa-file-shield me-2"></i> Pemulihan / Restorasi Pembayaran Berhasil!</h3>
        <p class="text-muted">Sistem telah memverifikasi activity logs dan secara otomatis memulihkan <strong><?= $restoredPaymentCount ?> transaksi pembayaran</strong> serta <strong><?= $restoredBillCount ?> tagihan</strong> yang sempat terhapus.</p>
        
        <div class="alert alert-success font-monospace small" style="max-height: 400px; overflow-y: auto;">
            <h6 class="fw-bold mb-2">Log Restorasi Transaksi Pembayaran:</h6>
            <?php foreach($log as $l): ?>
                <div>&bull; <?= htmlspecialchars($l) ?></div>
            <?php endforeach; ?>
            <?php if(empty($log)): ?>
                <div>Tidak ada pembayaran terhapus yang perlu dipulihkan. Seluruh transaksi kas pembayaran sudah lengkap dan aman.</div>
            <?php endif; ?>
        </div>

        <div class="mt-3">
            <a href="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" class="btn btn-primary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Tool Emergency</a>
        </div>
    </div>
</div>
</body>
</html>
