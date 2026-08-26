<?php
/**
 * Script Inspeksi Pembayaran 23 Siswa
 * Akses: https://perguruanpembda.com/debug_23_payments.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

$activeYear = AcademicYear::where('is_active', true)->first();
$academicYearId = $activeYear?->id ?? 5;

$discountedStudents = DB::table('student_bills as sb')
    ->join('students as s', 'sb.student_id', '=', 's.id')
    ->join('schools as sch', 's.school_id', '=', 'sch.id')
    ->join('payment_types as pt', 'sb.payment_type_id', '=', 'pt.id')
    ->where('sb.academic_year_id', $academicYearId)
    ->where('sb.month', 7)
    ->whereRaw('sb.amount != pt.amount')
    ->select(
        's.id as student_id',
        's.full_name',
        'sch.name as school_name',
        'pt.id as payment_type_id',
        'pt.type_name',
        'pt.amount as default_amount',
        'sb.amount as july_amount',
        'sb.yayasan_share_amount as july_yayasan_share'
    )
    ->orderBy('sch.id')
    ->orderBy('s.full_name')
    ->get();

$months = [8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

$details = [];

foreach ($discountedStudents as $ds) {
    $paidBills = DB::table('student_bills')
        ->where('academic_year_id', $academicYearId)
        ->where('student_id', $ds->student_id)
        ->where('payment_type_id', $ds->payment_type_id)
        ->whereIn('month', $months)
        ->whereRaw('amount != ?', [$ds->july_amount])
        ->get();

    if ($paidBills->count() > 0) {
        $billDetails = [];
        foreach ($paidBills as $b) {
            $payments = DB::table('payments')->where('bill_id', $b->id)->get();
            $billDetails[] = [
                'bill' => $b,
                'payments' => $payments,
            ];
        }

        $details[] = [
            'student' => $ds,
            'paid_bills' => $billDetails,
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($details, JSON_PRETTY_PRINT);
