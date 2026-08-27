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

$action = $_REQUEST['action'] ?? 'preview';

echo "<h3>Sinkronisasi Potongan Tagihan Siswa (Anak Guru, Beasiswa, dll)</h3>";

// Find all July bills
$julyBills = StudentBill::where('month', 7)->get();

$discrepancies = [];

foreach ($julyBills as $julyBill) {
    // Find August and onwards bills for the same student, type, and academic year
    $laterBills = StudentBill::where('student_id', $julyBill->student_id)
        ->where('payment_type_id', $julyBill->payment_type_id)
        ->where('academic_year_id', $julyBill->academic_year_id)
        ->where('month', '>', 7)
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

echo "Ditemukan " . count($discrepancies) . " tagihan bulan Agustus/selanjutnya yang nominalnya berbeda dengan bulan Juli (tidak mewarisi diskon).<br><br>";

if ($action === 'preview') {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Tagihan</th><th>Siswa ID</th><th>Bulan</th><th>Nominal Salah</th><th>Nominal Seharusnya (Ikut Juli)</th></tr>";
    $i = 0;
    foreach ($discrepancies as $d) {
        if ($i++ > 100) {
            echo "<tr><td colspan='5'>... dan " . (count($discrepancies) - 100) . " tagihan lainnya.</td></tr>";
            break;
        }
        $b = $d['bill'];
        echo "<tr>";
        echo "<td>{$b->id}</td>";
        echo "<td>{$b->student_id}</td>";
        echo "<td>{$b->month}/{$b->year}</td>";
        echo "<td>Rp " . number_format($b->amount, 0, ',', '.') . "</td>";
        echo "<td style='color:green;'>Rp " . number_format($d['correct_amount'], 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><a href='sync_discounts.php?secret=pembda99&action=execute' style='padding:10px; background:blue; color:white; text-decoration:none;'>JALANKAN PERBAIKAN SEKARANG</a>";
}

if ($action === 'execute') {
    DB::beginTransaction();
    try {
        $updated = 0;
        foreach ($discrepancies as $d) {
            $b = $d['bill'];
            
            // If already paid more than the new correct amount, skip to avoid negative balances
            if ($b->paid_amount > $d['correct_amount']) {
                continue;
            }
            
            $b->amount = $d['correct_amount'];
            $b->yayasan_share_amount = $d['correct_yayasan'];
            $b->save();
            $updated++;
        }
        DB::commit();
        echo "<h4 style='color:green;'>Berhasil memperbaiki $updated tagihan! Semua diskon telah tersinkronisasi.</h4>";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "<h4 style='color:red;'>Gagal: " . $e->getMessage() . "</h4>";
    }
}
