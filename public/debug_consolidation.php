<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Payment;
use App\Models\Employee;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\EmployeeAssignmentService;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);

if ($request->query('secret') !== 'pembda99') {
    die("Unauthorized");
}

echo "<html><body><pre>";

$month = request('month', now()->month);
$year = request('year', now()->year);
$schoolId = request('school_id', 2);

echo "Month: $month, Year: $year, School ID: $schoolId\n";

$payments = Payment::with(['bill.paymentType', 'student'])
    ->whereHas('student', function ($query) use ($schoolId) {
        $query->where('school_id', $schoolId);
    })
    ->whereMonth('payment_date', $month)
    ->whereYear('payment_date', $year)
    ->where('is_verified', true)
    ->get();

echo "Total Payments found: " . $payments->count() . "\n";
foreach($payments as $p) {
    echo "Payment ID: {$p->id}, Amount: {$p->amount_paid}, Date: {$p->payment_date}\n";
}

$employees = Employee::where('school_id', $schoolId)->where('is_active', true)->get();
echo "\nTotal Active Employees in school {$schoolId}: " . $employees->count() . "\n";

$activeYear = AcademicYear::where('is_active', true)->first();
$activeSemester = Semester::where('is_active', true)->first();

echo "Active Year: " . ($activeYear ? $activeYear->year : 'NULL') . "\n";
echo "Active Semester: " . ($activeSemester ? $activeSemester->name : 'NULL') . "\n";

if ($activeYear && $activeSemester) {
    $svc = app(EmployeeAssignmentService::class);
    foreach($employees as $e) {
        $thp = $svc->calculateFullSalary($e, $activeYear, $activeSemester, null, $schoolId);
        echo "Emp: {$e->full_name}, THP: " . ($thp['take_home_pay'] ?? 0) . "\n";
    }
}

echo "</pre></body></html>";
