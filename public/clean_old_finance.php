<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!isset($_GET['token']) || $_GET['token'] !== 'pembda99') {
    die("Akses ditolak. Token tidak valid.");
}

// Security measure: require confirmation
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<h1>⚠️ PERINGATAN PENGHAPUSAN DATA ⚠️</h1>";
    echo "<p>Script ini akan <b>MENGHAPUS PERMANEN</b> semua tagihan (Student Bills) dan pembayarannya (Payments) <b>selain Tahun Pelajaran 2026/2027</b>.</p>";
    echo "<p>Sesuai aturan keamanan: Data Tahun Pelajaran (academic_years) TIDAK AKAN DIHAPUS.</p>";
    echo "<a href='?token=pembda99&confirm=yes' style='background:red; color:white; padding:10px; text-decoration:none; display:inline-block; font-weight:bold; border-radius:5px;'>SAYA YAKIN, HAPUS SEKARANG</a>";
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
echo "<h1>=== PROSES PEMBERSIHAN DATABASE KEUANGAN ===</h1>\n";

try {
    // 1. Dapatkan ID TP 2026/2027
    $targetYear = App\Models\AcademicYear::where('year', 'like', '%2026/2027%')->pluck('id')->toArray();
    
    if (empty($targetYear)) {
        echo "❌ Tahun Pelajaran 2026/2027 tidak ditemukan di database. Operasi dibatalkan demi keamanan.\n";
        exit;
    }

    echo "✅ ID Tahun Pelajaran 2026/2027 ditemukan: " . implode(', ', $targetYear) . "\n\n";

    // Gunakan transaksi database agar aman
    Illuminate\Support\Facades\DB::beginTransaction();

    // 2. Cari ID Tagihan (Bills) yang BUKAN bagian dari TP 2026/2027
    $billsToDelete = App\Models\StudentBill::whereNotIn('academic_year_id', $targetYear)->pluck('id')->toArray();
    
    $billsCount = count($billsToDelete);
    
    if ($billsCount === 0) {
        echo "✅ Tidak ada data latihan (tagihan di luar TP 2026/2027) yang ditemukan. Database sudah bersih!\n";
        Illuminate\Support\Facades\DB::commit();
        exit;
    }
    
    echo "🗑️ Ditemukan {$billsCount} tagihan di luar TP 2026/2027 yang akan dihapus...\n";

    // 3. Hapus Payments yang terkait dengan Bills tersebut (karena relasi, harus dihapus dulu child-nya)
    // Jika ada cascade ini opsional, tapi aman dilakukan manual
    $paymentsDeleted = App\Models\Payment::whereIn('bill_id', $billsToDelete)->delete();
    echo "✅ Berhasil menghapus {$paymentsDeleted} data pembayaran (Payments) terkait.\n";

    // 4. Hapus Student Bills
    // Gunakan chunking jika jumlahnya sangat besar untuk mencegah memory limit/timeout
    $billsDeleted = App\Models\StudentBill::whereIn('id', $billsToDelete)->delete();
    
    echo "✅ Berhasil menghapus {$billsDeleted} data tagihan (Student Bills).\n";

    Illuminate\Support\Facades\DB::commit();
    
    echo "\n🎉 PEMBERSIHAN SELESAI!\n";

} catch (\Exception $e) {
    Illuminate\Support\Facades\DB::rollBack();
    echo "\n❌ ERROR: Terjadi kesalahan. Semua perubahan DIBATALKAN (Rollback).\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n</pre>";
