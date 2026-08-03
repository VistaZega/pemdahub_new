<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);

if ($_GET['secret'] !== 'pembda99') die('unauth');

$schoolId = $_GET['school_id'] ?? 3;
$employees = \App\Models\Employee::where('school_id', $schoolId)->where('is_active', true)->get();

echo "School $schoolId Employees: " . $employees->count() . "<br>";

$activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
$activeSemester = \App\Models\Semester::where('is_active', true)->first();
$svc = app(\App\Services\EmployeeAssignmentService::class);

foreach($employees as $e) {
    $thp = $svc->calculateFullSalary($e, $activeYear, $activeSemester, null, $schoolId);
    echo "Emp: {$e->full_name} | Type: {$e->employee_type} | Status: {$e->employment_status} | Base: {$e->basic_salary} | THP: " . ($thp['take_home_pay'] ?? 0);
    echo " | Details: " . json_encode($thp) . "<br>";
}

// payments
$payments = \App\Models\Payment::with(['bill.paymentType', 'student'])
    ->whereHas('student', function ($query) use ($schoolId) {
        $query->where('school_id', $schoolId);
    })
    ->whereMonth('payment_date', $_GET['month'] ?? 8)
    ->whereYear('payment_date', $_GET['year'] ?? 2026)
    ->where('is_verified', true)
    ->get();
    
echo "<br>Total Payments: " . $payments->count() . "<br>";
foreach($payments as $p) {
    echo "Payment: {$p->id} Amount: {$p->amount_paid}<br>";
}
