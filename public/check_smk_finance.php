<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (request('token') !== 'pembda99') {
    abort(403);
}

header('Content-Type: text/html; charset=UTF-8');
echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
echo "<h1>=== DIAGNOSA KEUANGAN SMK PEMBDA NIAS ===</h1>\n";

try {
    $school = App\Models\School::where('type', 'SMK')->orWhere('name', 'like', '%SMK%')->first();
    if (!$school) {
        echo "❌ SMK Pembda Nias tidak ditemukan di tabel schools.\n";
        exit;
    }

    echo "🏫 Sekolah Ditemukan: ID {$school->id} - {$school->name} ({$school->type})\n\n";

    // 1. Total Tagihan
    $billsCount = App\Models\StudentBill::where('school_id', $school->id)->count();
    $totalAmount = App\Models\StudentBill::where('school_id', $school->id)->sum('amount');
    echo "📊 TOTAL TAGIHAN: {$billsCount} records (Total Nominal: Rp " . number_format($totalAmount, 0, ',', '.') . ")\n";

    // 2. Total Pembayaran
    $paymentsCount = App\Models\Payment::whereHas('bill', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->count();
    $totalPaid = App\Models\Payment::whereHas('bill', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->sum('amount_paid');
    echo "💳 TOTAL PEMBAYARAN: {$paymentsCount} records (Total Nominal: Rp " . number_format($totalPaid, 0, ',', '.') . ")\n\n";

    // 3. Rincian Tagihan berdasarkan Jenis Pembayaran (Payment Type)
    echo "📋 RINCIAN TAGIHAN BERDASARKAN JENIS:\n";
    $billTypes = App\Models\StudentBill::where('school_id', $school->id)
        ->selectRaw('payment_type_id, count(*) as total_records, sum(amount) as total_amount')
        ->groupBy('payment_type_id')
        ->get();
        
    foreach ($billTypes as $bt) {
        $pt = App\Models\PaymentType::find($bt->payment_type_id);
        $ptName = $pt ? $pt->name : 'Unknown ID '.$bt->payment_type_id;
        echo "  - {$ptName}: {$bt->total_records} tagihan (Rp " . number_format($bt->total_amount, 0, ',', '.') . ")\n";
    }

    echo "\n📋 RINCIAN TAGIHAN BERDASARKAN TAHUN PELAJARAN:\n";
    $years = App\Models\StudentBill::where('school_id', $school->id)
        ->selectRaw('academic_year_id, count(*) as total_records')
        ->groupBy('academic_year_id')
        ->get();

    foreach ($years as $y) {
        $ay = App\Models\AcademicYear::find($y->academic_year_id);
        $ayName = $ay ? $ay->year : 'Unknown ID '.$y->academic_year_id;
        echo "  - TP. {$ayName}: {$y->total_records} tagihan\n";
    }

    // 4. Sample 5 Tagihan Terakhir
    echo "\n🔎 5 TAGIHAN TERAKHIR YANG DIBUAT (SAMPLE):\n";
    $latestBills = App\Models\StudentBill::where('school_id', $school->id)->latest()->take(5)->get();
    if ($latestBills->isEmpty()) {
        echo "  - Tidak ada data tagihan.\n";
    } else {
        foreach ($latestBills as $b) {
            $student = App\Models\Student::find($b->student_id);
            $pt = App\Models\PaymentType::find($b->payment_type_id);
            $studentName = $student ? $student->full_name : 'Unknown Student';
            $ptName = $pt ? $pt->name : 'Unknown Type';
            echo "  - ID: {$b->id} | {$studentName} | {$ptName} | Rp " . number_format($b->amount, 0, ',', '.') . " | Status: {$b->status}\n";
        }
    }

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n</pre>";
