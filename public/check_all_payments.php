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
echo "<h1>=== CEK SISA DATA PEMBAYARAN SMK ===</h1>\n";

try {
    $school = App\Models\School::where('type', 'SMK')->orWhere('name', 'like', '%SMK%')->first();
    
    // 1. Tagihan SPP
    $billsCount = App\Models\StudentBill::whereHas('student', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->count();
    
    // 2. Pembayaran SPP
    $paymentsCount = App\Models\Payment::whereHas('bill.student', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->count();

    // 3. Pembayaran PPDB (Pendaftaran)
    $applicantPaymentsCount = App\Models\ApplicantPayment::whereHas('applicant', function($q) use ($school) {
        $q->where('school_id', $school->id);
    })->count();

    echo "Tagihan Aktif SMK        : {$billsCount} records\n";
    echo "Pembayaran SPP SMK       : {$paymentsCount} records\n";
    echo "Pembayaran PPDB (Siswa Baru) SMK : {$applicantPaymentsCount} records\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
