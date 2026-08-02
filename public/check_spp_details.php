<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!isset($_GET['token']) || $_GET['token'] !== 'pembda99') {
    die("Akses ditolak.");
}

header('Content-Type: text/html; charset=UTF-8');
echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
echo "<h1>=== DETAIL MASTER SPP SMK PEMBDA NIAS ===</h1>\n";

try {
    $school = App\Models\School::where('type', 'SMK')->orWhere('name', 'like', '%SMK%')->first();
    $targetYear = App\Models\AcademicYear::where('year', 'like', '%2026/2027%')->first();

    // Dapatkan tagihan TP 2026/2027 di SMK
    $bills = App\Models\StudentBill::whereHas('student', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->where('academic_year_id', $targetYear->id)->get();

    $grouped = $bills->groupBy('payment_type_id');
    
    foreach ($grouped as $ptId => $typeBills) {
        $pt = App\Models\PaymentType::find($ptId);
        $ptName = $pt ? $pt->type_name : "Unknown";
        
        echo "========================================================\n";
        echo "📌 PAYMENT TYPE ID: {$ptId} ({$ptName})\n";
        
        if ($pt) {
            echo "   Master Nominal : Rp " . number_format($pt->amount, 0, ',', '.') . "\n";
            echo "   Berulang       : " . ($pt->is_recurring ? "Ya (Per-Bulan)" : "Tidak (Sekali Bayar)") . "\n";
            echo "   Deskripsi      : " . ($pt->description ?? '-') . "\n";
        }

        // Cek sample tagihan dari tabel student_bills
        $sample = $typeBills->first();
        echo "   Nominal Tagihan: Rp " . number_format($sample->amount, 0, ',', '.') . " per tagihan di database\n";
        
        // Distribusi bulan
        $months = $typeBills->pluck('month')->filter()->unique()->sort()->values()->toArray();
        if (count($months) > 0) {
            echo "   Bulan Ditagih  : " . implode(', ', $months) . "\n";
            echo "   Sifat Tagihan  : Dibuat rutin per bulan (karena ada isian bulan)\n";
        } else {
            echo "   Bulan Ditagih  : (Kosong / Tidak spesifik bulan)\n";
            echo "   Sifat Tagihan  : Tagihan tahunan / tunggal\n";
        }
        
        // Sample siswa yang kena tagihan
        $studentIds = $typeBills->pluck('student_id')->unique();
        echo "   Ditagihkan ke  : " . count($studentIds) . " orang siswa berbeda\n";
        
        // Jumlah tagihan per siswa rata-rata
        $avgBillsPerStudent = round($typeBills->count() / max(1, count($studentIds)), 1);
        echo "   Rata-rata      : {$avgBillsPerStudent} tagihan per siswa\n";
        echo "========================================================\n\n";
    }

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
