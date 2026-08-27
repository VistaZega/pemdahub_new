<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);
@ini_set('memory_limit', '1024M');
@ini_set('max_execution_time', '300');
@set_time_limit(300);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\StudentBill;
use App\Models\Payment;

echo "<h3>Restoring Deleted Financials Stuck in Queue...</h3>";

$chunkSize = 5000;
$processedJobs = 0;
$restoredBills = 0;
$restoredPayments = 0;
$lastId = 0;

$ayStmt = DB::table('academic_years')->where('is_active', 1)->first();
$activeAyId = $ayStmt ? $ayStmt->id : 1;

while (true) {
    $jobs = DB::table('jobs')->where('id', '>', $lastId)->orderBy('id')->limit($chunkSize)->get();
    
    if ($jobs->isEmpty()) {
        break;
    }

    foreach ($jobs as $job) {
        $lastId = $job->id;
        $processedJobs++;
        
        $payload = json_decode($job->payload, true);
        if (!$payload || !isset($payload['data']['command'])) continue;
        
        try {
            $command = unserialize($payload['data']['command']);
            
            if ($command instanceof \Illuminate\Events\CallQueuedListener) {
                if (isset($command->data[0]) && $command->data[0] instanceof \App\Events\ModelActivityLogged) {
                    $event = $command->data[0];
                    
                    if ($event->action === 'deleted') {
                        // Restore StudentBill
                        if (str_contains($event->modelType, 'StudentBill')) {
                            $data = $event->changes;
                            if (isset($data['id'], $data['student_id'])) {
                                $exists = DB::table('student_bills')->where('id', $data['id'])->exists();
                                if (!$exists) {
                                    DB::table('student_bills')->insert([
                                        'id' => $data['id'],
                                        'student_id' => $data['student_id'],
                                        'payment_type_id' => $data['payment_type_id'] ?? 1,
                                        'academic_year_id' => $data['academic_year_id'] ?? $activeAyId,
                                        'month' => $data['month'] ?? null,
                                        'year' => $data['year'] ?? 2026,
                                        'amount' => $data['amount'] ?? 0,
                                        'paid_amount' => $data['paid_amount'] ?? 0,
                                        'yayasan_share_amount' => $data['yayasan_share_amount'] ?? 0,
                                        'status' => $data['status'] ?? 'belum_bayar',
                                        'due_date' => $data['due_date'] ?? null,
                                        'created_at' => $data['created_at'] ?? now(),
                                        'updated_at' => $data['updated_at'] ?? now(),
                                    ]);
                                    $restoredBills++;
                                }
                            }
                        }
                        
                        // Restore Payment
                        if (str_contains($event->modelType, 'Payment')) {
                            $data = $event->changes;
                            if (isset($data['id'], $data['student_id'])) {
                                $exists = DB::table('payments')->where('id', $data['id'])->exists();
                                if (!$exists) {
                                    $bId = $data['bill_id'] ?? null;
                                    if (!$bId) {
                                        $bChk = DB::table('student_bills')->where('student_id', $data['student_id'])->orderBy('id', 'desc')->first();
                                        $bId = $bChk ? $bChk->id : null;
                                    }
                                    
                                    if ($bId) {
                                        DB::table('payments')->insert([
                                            'id' => $data['id'],
                                            'bill_id' => $bId,
                                            'student_id' => $data['student_id'],
                                            'amount_paid' => $data['amount_paid'] ?? 0,
                                            'payment_method' => $data['payment_method'] ?? 'cash',
                                            'qris_transaction_id' => $data['qris_transaction_id'] ?? null,
                                            'qris_status' => $data['qris_status'] ?? null,
                                            'reference_number' => $data['reference_number'] ?? null,
                                            'receipt_number' => $data['receipt_number'] ?? ('KWT-REC-' . time()),
                                            'payment_date' => $data['payment_date'] ?? date('Y-m-d'),
                                            'proof_file' => $data['proof_file'] ?? null,
                                            'notes' => $data['notes'] ?? 'Restored from stuck queue',
                                            'processed_by' => $data['processed_by'] ?? 1,
                                            'is_verified' => $data['is_verified'] ?? 1,
                                            'created_at' => $data['created_at'] ?? now(),
                                            'updated_at' => $data['updated_at'] ?? now(),
                                        ]);
                                        $restoredPayments++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore unserialize errors
        }
    }
}

// Resync balances
$bills = DB::table('student_bills')->get();
foreach ($bills as $b) {
    $totalPaid = DB::table('payments')->where('bill_id', $b->id)->where('is_verified', 1)->sum('amount_paid');
    
    $status = 'belum_bayar';
    if ($totalPaid >= $b->amount) {
        $status = 'lunas';
    } elseif ($totalPaid > 0) {
        $status = 'cicilan';
    }
    
    DB::table('student_bills')->where('id', $b->id)->update([
        'paid_amount' => $totalPaid,
        'status' => $status
    ]);
}

echo "<br>Finished scanning jobs.";
echo "<br>Jobs Scanned: " . $processedJobs;
echo "<br>Bills Restored: " . $restoredBills;
echo "<br>Payments Restored: " . $restoredPayments;
echo "<br>All bill balances synced.";

