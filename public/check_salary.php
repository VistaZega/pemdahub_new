<?php 
require 'vendor/autoload.php'; 
$app = require_once 'bootstrap/app.php'; 
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 

$svc = app(App\Services\EmployeeAssignmentService::class); 
$year = App\Models\AcademicYear::where('is_active',true)->first(); 
$sem = App\Models\Semester::where('is_active',true)->first(); 
$sch = App\Models\School::find(1); 
$employees = App\Models\Employee::where('school_id', 1)->where('is_active', true)->get(); 

$konsolidasi = 0; 
$rekap = 0; 

foreach($employees as $emp) { 
    $s1 = $svc->calculateFullSalary($emp, $year, $sem, null, 1); 
    $s2 = $svc->calculateFullSalary($emp, $year, $sem, $sch->type, 1); 
    $konsolidasi += $s1['thp']; 
    $rekap += $s2['gross_pay']; 
} 

echo "Konsolidasi: " . $konsolidasi . "\n";
echo "Rekap: " . $rekap . "\n";
