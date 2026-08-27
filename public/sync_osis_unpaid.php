<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\PaymentType;
use App\Models\StudentBill;

echo "<h3>Menyelaraskan Tagihan OSIS dengan SPP (Membuat Tagihan yang Belum Dibuat)</h3>";

$school = School::where('name', 'LIKE', '%SMAS Pembda 1%')->first();
if (!$school) die("Sekolah tidak ditemukan.");

$sppType = PaymentType::where('school_id', $school->id)->where('type_name', 'LIKE', '%SPP%')->first();
$osisType = PaymentType::where('school_id', $school->id)->where('type_name', 'LIKE', '%Iuran OSIS%')->first();

if (!$sppType || !$osisType) die("Tipe pembayaran tidak ditemukan.");

// Get all SPP bills for 2026/2027 (assuming current active year)
$activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
if (!$activeYear) die("Active year not found.");

$sppBills = StudentBill::select('student_bills.*')
    ->join('students', 'students.id', '=', 'student_bills.student_id')
    ->where('students.school_id', $school->id)
    ->where('student_bills.payment_type_id', $sppType->id)
    ->where('student_bills.academic_year_id', $activeYear->id)
    ->get();

$createdBills = 0;

DB::beginTransaction();
try {
    foreach ($sppBills as $sppBill) {
        $osisBill = StudentBill::firstOrCreate([
            'student_id' => $sppBill->student_id,
            'payment_type_id' => $osisType->id,
            'academic_year_id' => $sppBill->academic_year_id,
            'month' => $sppBill->month,
            'year' => $sppBill->year,
        ], [
            'semester_id' => $sppBill->semester_id,
            'amount' => $osisType->amount ?? 10000,
            'yayasan_share_amount' => $osisType->yayasan_share_amount ?? 0,
            'paid_amount' => 0,
            'status' => 'belum_bayar',
            'created_at' => $sppBill->created_at,
            'updated_at' => $sppBill->updated_at,
            'due_date' => $sppBill->due_date
        ]);

        if ($osisBill->wasRecentlyCreated) {
            $createdBills++;
        }
    }
    DB::commit();
    echo "<h4 style='color:green;'>Selesai! Berhasil membuat $createdBills tagihan OSIS (belum dibayar) agar selaras dengan tagihan SPP.</h4>";
} catch (\Exception $e) {
    DB::rollBack();
    echo "<h4 style='color:red;'>Gagal: " . $e->getMessage() . "</h4>";
}
