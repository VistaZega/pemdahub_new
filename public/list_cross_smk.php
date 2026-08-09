<?php

/**
 * Script Diagnostik Komparasi Rekap Beban Kerja vs Consolidation SMK
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
use App\Models\EmployeeWorkloadSummary;
use App\Services\EmployeeAssignmentService;

$smk = School::where('type', 'LIKE', '%SMK%')->orWhere('name', 'LIKE', '%SMK%')->first();
$activeYear = AcademicYear::where('is_active', true)->first();
$activeSemester = Semester::where('is_active', true)->first();
$service = app(EmployeeAssignmentService::class);

// 1. Simulasikan Query Rekap Beban Kerja untuk SMK (seperti di WorkloadSummaryController)
$query = EmployeeWorkloadSummary::with(['employee.school', 'employee.activePositions', 'employee.teacher'])
    ->where('academic_year_id', $activeYear->id)
    ->where('semester_id', $activeSemester->id);

$schoolId = $smk->id;
$query->where(function ($q) use ($schoolId, $activeYear) {
    $q->whereHas('employee', fn($empQ) => $empQ->where('school_id', $schoolId))
      ->orWhereHas('employee.activePositions', function ($posQ) use ($schoolId, $activeYear) {
          $posQ->where('positions.school_id', $schoolId)
               ->where('employee_positions.academic_year_id', $activeYear->id);
      })
      ->orWhereHas('employee.teacher.teachingAssignments', function ($teachQ) use ($schoolId, $activeYear) {
          $teachQ->where('academic_year_id', $activeYear->id)
                 ->where('is_active', true)
                 ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $schoolId));
      });
});

$workloadSummaries = $query->get();

$homeSmkSummaries = [];
$crossSmkSummaries = [];

$totalWorkloadThp = 0;
$totalHomeThp = 0;
$totalCrossThp = 0;

foreach ($workloadSummaries as $s) {
    $emp = $s->employee;
    if (!$emp) continue;

    $freshSalary = $service->calculateFullSalary($emp, $activeYear, $activeSemester, 'SMK', $smk->id);
    $thp = $freshSalary['thp'] ?? 0;
    $totalWorkloadThp += $thp;

    $item = [
        'employee_id' => $emp->id,
        'name' => $emp->full_name,
        'code' => $emp->employee_code ?? '-',
        'home_unit' => $emp->school ? $emp->school->name : 'Yayasan / Unit Lain',
        'employment_status' => $emp->employment_status,
        'employee_type' => $emp->employee_type,
        'thp' => $thp,
        'formatted_thp' => 'Rp ' . number_format($thp, 0, ',', '.'),
        'gaji_pokok' => $freshSalary['gaji_pokok'] ?? 0,
        'tunjangan_jabatan' => $freshSalary['tunjangan_jabatan'] ?? 0,
        'honor_mengajar' => $freshSalary['honor_mengajar'] ?? 0,
        'honor_pkl' => $freshSalary['honor_pkl'] ?? 0,
    ];

    if ($emp->school_id == $smk->id) {
        $homeSmkSummaries[] = $item;
        $totalHomeThp += $thp;
    } else {
        $crossSmkSummaries[] = $item;
        $totalCrossThp += $thp;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'active_year' => $activeYear->year,
    'active_semester' => $activeSemester->semester_name,
    'school_smk' => $smk->name,
    'rekap_beban_kerja_total_thp' => 'Rp ' . number_format($totalWorkloadThp, 0, ',', '.'),
    'pegawai_murni_smk_total_thp' => 'Rp ' . number_format($totalHomeThp, 0, ',', '.'),
    'pegawai_perbantuan_total_thp' => 'Rp ' . number_format($totalCrossThp, 0, ',', '.'),
    'total_pegawai_rekap_smk' => count($workloadSummaries),
    'jumlah_pegawai_murni_smk' => count($homeSmkSummaries),
    'jumlah_pegawai_perbantuan' => count($crossSmkSummaries),
    'daftar_pegawai_perbantuan' => $crossSmkSummaries,
], JSON_PRETTY_PRINT);
