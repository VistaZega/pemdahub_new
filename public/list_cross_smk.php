<?php

/**
 * Script Diagnostik Pegawai Perbantuan SMK
 * Akses: https://perguruanpembda.com/list_cross_smk.php?secret=pembda99
 */

if (($_GET['secret'] ?? '') !== 'pembda99') {
    die('Access Denied');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\EmployeeAssignmentService;

$smk = School::where('type', 'LIKE', '%SMK%')->orWhere('name', 'LIKE', '%SMK%')->first();
$activeYear = AcademicYear::where('is_active', true)->first();
$activeSemester = Semester::where('is_active', true)->first();
$service = app(EmployeeAssignmentService::class);

$allEmployees = Employee::with(['activePositions', 'teacher', 'school'])
    ->where('is_active', true)
    ->where(function ($q) use ($smk, $activeYear) {
        $q->where('school_id', $smk->id)
          ->orWhereHas('activePositions', function ($posQ) use ($smk, $activeYear) {
              $posQ->where('positions.school_id', $smk->id)
                   ->where('employee_positions.academic_year_id', $activeYear->id);
          })
          ->orWhereHas('teacher.teachingAssignments', function ($teachQ) use ($smk, $activeYear) {
              $teachQ->where('academic_year_id', $activeYear->id)
                     ->where('is_active', true)
                     ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $smk->id));
          });
    })
    ->get();

$crossEmployees = [];
$totalCrossThp = 0;

foreach ($allEmployees as $emp) {
    if ($emp->school_id != $smk->id) {
        $sal = $service->calculateFullSalary($emp, $activeYear, $activeSemester, 'SMK', $smk->id);
        $thp = $sal['thp'] ?? 0;
        $totalCrossThp += $thp;

        $homeName = $emp->school ? $emp->school->name : 'Yayasan / Unit Lain';

        $smkPositions = $emp->activePositions()
            ->where('positions.school_id', $smk->id)
            ->get()
            ->pluck('position_name')
            ->join(', ');

        $teachingHours = 0;
        if ($emp->teacher) {
            $assignments = \App\Models\TeachingAssignment::where('teacher_id', $emp->teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('is_active', true)
                ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $smk->id))
                ->get();
            $teachingHours = $assignments->sum('hours_per_week');
        }

        $crossEmployees[] = [
            'name' => $emp->full_name,
            'code' => $emp->employee_code ?? '-',
            'status' => $emp->employment_status,
            'type' => $emp->employee_type,
            'home_unit' => $homeName,
            'smk_positions' => $smkPositions ?: '-',
            'teaching_hours' => $teachingHours,
            'honor_mengajar' => $sal['honor_mengajar'] ?? 0,
            'tunjangan_jabatan' => $sal['tunjangan_jabatan'] ?? 0,
            'honor_pkl' => $sal['honor_pkl'] ?? 0,
            'thp' => $thp,
        ];
    }
}

// Check also pure home employees who have assignments in other schools or vice versa
$pureEmployeesCount = Employee::where('school_id', $smk->id)->where('is_active', true)->count();

header('Content-Type: application/json');
echo json_encode([
    'school_smk_id' => $smk->id,
    'school_smk_name' => $smk->name,
    'academic_year' => $activeYear->year,
    'semester' => $activeSemester->semester_name,
    'pure_smk_employees_count' => $pureEmployeesCount,
    'cross_employees_count' => count($crossEmployees),
    'total_cross_thp' => $totalCrossThp,
    'cross_employees' => $crossEmployees,
], JSON_PRETTY_PRINT);
