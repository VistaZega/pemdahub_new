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
echo "<h1>=== TAGIHAN SMK PEMBDA NIAS (TP 2026/2027) ===</h1>\n";

try {
    $school = App\Models\School::where('type', 'SMK')->orWhere('name', 'like', '%SMK%')->first();
    $targetYear = App\Models\AcademicYear::where('year', 'like', '%2026/2027%')->first();

    if (!$school || !$targetYear) {
        echo "❌ Sekolah atau TP 2026/2027 tidak ditemukan.\n";
        exit;
    }

    $bills = App\Models\StudentBill::whereHas('student', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->where('academic_year_id', $targetYear->id)->get();

    $totalBills = $bills->count();
    $totalNominal = $bills->sum('amount');
    
    echo "📊 TOTAL KESELURUHAN: {$totalBills} tagihan (Rp " . number_format($totalNominal, 0, ',', '.') . ")\n\n";

    echo "📋 RINCIAN BERDASARKAN NAMA TAGIHAN (PAYMENT TYPE):\n";
    $grouped = $bills->groupBy('payment_type_id');
    
    foreach ($grouped as $ptId => $typeBills) {
        $pt = App\Models\PaymentType::find($ptId);
        $ptName = $pt ? $pt->type_name : "Unknown (ID: $ptId)";
        
        $count = $typeBills->count();
        $sum = $typeBills->sum('amount');
        
        $lunas = $typeBills->where('status', 'lunas')->count();
        $belumBayar = $typeBills->where('status', 'belum_bayar')->count();
        $cicilan = $typeBills->where('status', 'cicilan')->count();
        
        echo "--------------------------------------------------------\n";
        echo "📌 JENIS: {$ptName}\n";
        echo "   Total Data : {$count} Tagihan\n";
        echo "   Nominal    : Rp " . number_format($sum, 0, ',', '.') . "\n";
        echo "   Status     : Lunas ({$lunas}) | Belum Bayar ({$belumBayar}) | Cicilan ({$cicilan})\n";
    }

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
