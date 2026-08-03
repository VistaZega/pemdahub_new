<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ambil semua tagihan yang tahun-nya aneh (misal 0 atau 2001)
$invalidBills = App\Models\StudentBill::whereIn('year', [0, 2001])->get();
echo "Ditemukan " . $invalidBills->count() . " tagihan dengan tahun yang salah.\n";

$fixedCount = 0;
foreach ($invalidBills as $bill) {
    // Semester 1 (Juli-Desember) harusnya tahun 2026
    if ($bill->month >= 7 && $bill->month <= 12) {
        $bill->year = 2026;
        $bill->save();
        $fixedCount++;
    } 
    // Semester 2 (Januari-Juni) harusnya tahun 2027
    else if ($bill->month >= 1 && $bill->month <= 6) {
        $bill->year = 2027;
        $bill->save();
        $fixedCount++;
    }
}

echo "Berhasil memperbaiki " . $fixedCount . " tagihan.\n";
