<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!isset($_GET['token']) || $_GET['token'] !== 'pembda99') {
    die("Akses ditolak.");
}

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<h1>⚠️ HAPUS TOTAL DATA KEUANGAN SMK (TP 2026/2027) ⚠️</h1>";
    echo "<p>Klik tombol di bawah ini untuk <b>MENGHAPUS SEMUA PEMBAYARAN DAN TAGIHAN</b> khusus untuk SMK di TP 2026/2027.</p>";
    echo "<a href='?token=pembda99&confirm=yes' style='background:red; color:white; padding:10px; text-decoration:none; display:inline-block; font-weight:bold; border-radius:5px;'>SAYA YAKIN, HAPUS SEMUA SEKARANG</a>";
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
echo "<h1>=== PROSES PEMBERSIHAN TOTAL KEUANGAN SMK ===</h1>\n";

try {
    $school = App\Models\School::where('type', 'SMK')->orWhere('name', 'like', '%SMK%')->first();
    $targetYear = App\Models\AcademicYear::where('year', 'like', '%2026/2027%')->first();

    if (!$school || !$targetYear) {
        echo "❌ Sekolah SMK atau TP 2026/2027 tidak ditemukan.\n";
        exit;
    }

    Illuminate\Support\Facades\DB::beginTransaction();

    // Dapatkan semua ID tagihan milik SMK di tahun 2026/2027
    $billsToDelete = App\Models\StudentBill::whereHas('student', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->where('academic_year_id', $targetYear->id)->pluck('id')->toArray();

    if (empty($billsToDelete)) {
        echo "✅ Tidak ada tagihan yang ditemukan untuk SMK. Database sudah kosong.\n";
        Illuminate\Support\Facades\DB::commit();
        exit;
    }

    echo "🗑️ Akan menghapus " . count($billsToDelete) . " tagihan beserta riwayat pembayarannya...\n";

    // 1. Hapus semua data di tabel `payments` (Pembayaran) terlebih dahulu
    $paymentsDeleted = App\Models\Payment::whereIn('bill_id', $billsToDelete)->delete();
    echo "✅ Berhasil menghapus {$paymentsDeleted} riwayat transaksi pembayaran (Lunas/Cicilan).\n";

    // 2. Hapus semua data di tabel `student_bills` (Tagihan)
    $billsDeleted = App\Models\StudentBill::whereIn('id', $billsToDelete)->delete();
    echo "✅ Berhasil menghapus {$billsDeleted} data tagihan.\n";

    Illuminate\Support\Facades\DB::commit();
    
    echo "\n🎉 PEMBERSIHAN TOTAL BERHASIL! Database keuangan SMK sekarang kembali bersih dari nol.\n";

} catch (\Exception $e) {
    Illuminate\Support\Facades\DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
