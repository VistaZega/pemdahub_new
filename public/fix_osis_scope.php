<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$action = $_REQUEST['action'] ?? 'preview';

echo "<h3>Perbaikan Scope Iuran OSIS Rp 10.000</h3>";

// Find SMAS Pembda 1 school ID
$smas = DB::table('schools')->where('name', 'like', '%SMAS Pembda 1%')->first();
if (!$smas) {
    die("Sekolah SMAS Pembda 1 tidak ditemukan di database.");
}
$smasId = $smas->id;
echo "ID SMAS Pembda 1 = " . $smasId . "<br><br>";

// Find all OSIS payment types
$osisTypes = DB::table('payment_types')
    ->join('schools', 'payment_types.school_id', '=', 'schools.id')
    ->select('payment_types.*', 'schools.name as school_name')
    ->where('type_name', 'like', '%OSIS%')
    ->get();

echo "<h4>Daftar Jenis Pembayaran OSIS Saat Ini:</h4>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Nama Tagihan</th><th>Nominal</th><th>Unit Sekolah</th><th>Status</th></tr>";

$toDeleteIds = [];

foreach ($osisTypes as $pt) {
    if ($pt->school_id == $smasId) {
        $status = "<span style='color:green;'>Dipertahankan (Sesuai)</span>";
    } else {
        $status = "<span style='color:red;'>Akan Dihapus (Bukan SMAS)</span>";
        $toDeleteIds[] = $pt->id;
    }
    echo "<tr>";
    echo "<td>{$pt->id}</td>";
    echo "<td>{$pt->type_name}</td>";
    echo "<td>Rp " . number_format($pt->amount, 0, ',', '.') . "</td>";
    echo "<td>{$pt->school_name} (ID: {$pt->school_id})</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

if ($action === 'execute') {
    if (!empty($toDeleteIds)) {
        echo "<hr><h4>Melakukan Penghapusan Tagihan OSIS untuk unit selain SMAS...</h4>";
        
        DB::beginTransaction();
        try {
            // 1. Delete payments related to bills of these payment types
            $deletedPayments = DB::table('payments')
                ->join('student_bills', 'payments.bill_id', '=', 'student_bills.id')
                ->whereIn('student_bills.payment_type_id', $toDeleteIds)
                ->delete();
            echo "Menghapus $deletedPayments record pembayaran terkait.<br>";
            
            // 2. Delete the bills
            $deletedBills = DB::table('student_bills')
                ->whereIn('payment_type_id', $toDeleteIds)
                ->delete();
            echo "Menghapus $deletedBills record tagihan OSIS untuk unit SMK/SMPS.<br>";
            
            // 3. Delete the payment types
            $deletedTypes = DB::table('payment_types')
                ->whereIn('id', $toDeleteIds)
                ->delete();
            echo "Menghapus $deletedTypes master jenis pembayaran OSIS yang salah.<br>";
            
            DB::commit();
            echo "<br><strong style='color:green;'>Eksekusi Berhasil! Iuran OSIS sekarang HANYA berlaku untuk SMAS Pembda 1.</strong>";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "<br><strong style='color:red;'>Gagal: " . $e->getMessage() . "</strong>";
        }
    } else {
        echo "<hr>Tidak ada yang perlu dihapus. Semuanya sudah benar.";
    }
} else {
    echo "<br><br><a href='fix_osis_scope.php?secret=pembda99&action=execute' style='padding:10px 20px; background:red; color:white; text-decoration:none; font-weight:bold;'>EKSEKUSI PENGHAPUSAN (BATALKAN OSIS UNTUK UNIT LAIN)</a>";
}
