<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\StudentBill;
use App\Models\Payment;

$action = $_REQUEST['action'] ?? 'preview';

echo "<h3>Force Sync 26 Tagihan & Pembayaran yang Terlewat</h3>";

$julyBills = StudentBill::where('month', 7)->get();
$discrepancies = [];

foreach ($julyBills as $julyBill) {
    $laterBills = StudentBill::where('student_id', $julyBill->student_id)
        ->where('payment_type_id', $julyBill->payment_type_id)
        ->where('academic_year_id', $julyBill->academic_year_id)
        ->where('month', '>', 7)
        ->with('payments')
        ->get();

    foreach ($laterBills as $bill) {
        if ((float)$bill->amount != (float)$julyBill->amount) {
            $discrepancies[] = [
                'bill' => $bill,
                'correct_amount' => $julyBill->amount,
                'correct_yayasan' => $julyBill->yayasan_share_amount
            ];
        }
    }
}

echo "Ditemukan " . count($discrepancies) . " tagihan yang masih perlu di-force sync.<br><br>";

if ($action === 'preview') {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Tagihan</th><th>Bulan</th><th>Nominal Salah</th><th>Telah Dibayar (Sistem)</th><th>Akan Diubah Ke (Tagihan)</th><th>Akan Diubah Ke (Pembayaran)</th></tr>";
    foreach ($discrepancies as $d) {
        $b = $d['bill'];
        $correct = $d['correct_amount'];
        $newPaidAmount = min($b->paid_amount, $correct); // cap the payment to the new bill amount
        // If they paid 150k but the new bill is 75k, we will cap the payment at 75k.
        // If they paid 50k and the new bill is 75k, payment stays 50k.
        
        echo "<tr>";
        echo "<td>{$b->id}</td>";
        echo "<td>{$b->month}/{$b->year}</td>";
        echo "<td>Rp " . number_format($b->amount, 0, ',', '.') . "</td>";
        echo "<td>Rp " . number_format($b->paid_amount, 0, ',', '.') . "</td>";
        echo "<td style='color:green;'>Rp " . number_format($correct, 0, ',', '.') . "</td>";
        if ($b->paid_amount > $correct) {
            echo "<td style='color:red;'>Rp " . number_format($newPaidAmount, 0, ',', '.') . " (Dipotong)</td>";
        } else {
            echo "<td>Tetap (Rp " . number_format($b->paid_amount, 0, ',', '.') . ")</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "<br><a href='force_sync_26.php?secret=pembda99&action=execute' style='padding:10px; background:blue; color:white; text-decoration:none;'>JALANKAN FORCE SYNC SEKARANG</a>";
}

if ($action === 'execute') {
    DB::beginTransaction();
    try {
        $updatedBills = 0;
        $updatedPayments = 0;
        
        foreach ($discrepancies as $d) {
            $b = $d['bill'];
            $correct = $d['correct_amount'];
            
            // Update the bill's amount
            $b->amount = $correct;
            $b->yayasan_share_amount = $d['correct_yayasan'];
            
            // Adjust payments if they exceed the new bill amount
            if ($b->paid_amount > $correct) {
                // To be safe and simple: just update the very first payment record to be exactly $correct,
                // and delete any subsequent payment records for this bill.
                // Because if they overpaid in multiple installments, it's safer to just consolidate it 
                // to a single 'lunas' payment matching the correct amount.
                $payments = $b->payments()->orderBy('id')->get();
                $isFirst = true;
                foreach ($payments as $p) {
                    if ($isFirst) {
                        $p->amount_paid = $correct;
                        $p->save();
                        $updatedPayments++;
                        $isFirst = false;
                    } else {
                        $p->delete(); 
                    }
                }
                $b->paid_amount = $correct;
                $b->status = 'lunas';
            } else {
                // if they haven't overpaid, just update the status correctly if needed
                if ($b->paid_amount >= $correct && $correct > 0) {
                    $b->status = 'lunas';
                }
            }
            
            $b->save();
            $updatedBills++;
        }
        
        DB::commit();
        echo "<h4 style='color:green;'>Berhasil mem-force sync $updatedBills tagihan dan menyesuaikan riwayat $updatedPayments pembayaran!</h4>";
        echo "<script>setTimeout(()=>window.location.href='force_sync_26.php?secret=pembda99', 3000);</script>";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "<h4 style='color:red;'>Gagal: " . $e->getMessage() . "</h4>";
    }
}
