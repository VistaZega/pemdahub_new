<?php

/**
 * Script Diagnostik Pegawai Perbantuan SMK
 * Akses: https://perguruanpembda.com/list_cross_smk.php?token=pembda2026check
 */

if (($_GET['token'] ?? '') !== 'pembda2026check' && ($_GET['secret'] ?? '') !== 'pembda99') {
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

if (!$smk || !$activeYear || !$activeSemester) {
    die('Data sekolah SMK atau Tahun Pelajaran / Semester Aktif tidak ditemukan.');
}

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

        // Check positions in SMK
        $smkPositions = $emp->activePositions()
            ->where('positions.school_id', $smk->id)
            ->get()
            ->pluck('position_name')
            ->join(', ');

        // Check teaching assignments in SMK
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
            'type' => $emp->employee_type,
            'status' => $emp->employment_status,
            'home_unit' => $homeName,
            'smk_position' => $smkPositions ?: '-',
            'teaching_hours' => $teachingHours,
            'honor_mengajar' => $sal['honor_mengajar'] ?? 0,
            'tunjangan_jabatan' => $sal['tunjangan_jabatan'] ?? 0,
            'honor_pkl' => $sal['honor_pkl'] ?? 0,
            'thp' => $thp,
        ];
    }
}

header('Content-Type: application/json');
echo json_encode([
    'school' => $smk->name,
    'academic_year' => $activeYear->year,
    'semester' => $activeSemester->semester_name,
    'cross_employees_count' => count($crossEmployees),
    'total_cross_thp' => $totalCrossThp,
    'employees' => $crossEmployees,
], JSON_PRETTY_PRINT);
