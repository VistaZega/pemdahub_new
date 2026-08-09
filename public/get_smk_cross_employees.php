<?php

/**
 * Script Diagnostik Pegawai Perbantuan SMK
 * Akses: https://perguruanpembda.com/get_smk_cross_employees.php?secret=pembda99
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

if (!$smk || !$activeYear || !$activeSemester) {
    die('Data sekolah SMK atau Tahun Pelajaran / Semester Aktif tidak ditemukan.');
}

$allEmployees = Employee::with(['activePositions', 'teacher'])
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

        $homeSchool = School::find($emp->school_id);
        $homeName = $homeSchool ? $homeSchool->name : 'Yayasan / Unit Lain';

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

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rincian Pegawai Perbantuan SMK</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f6f9; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h2 { color: #1e293b; margin-bottom: 5px; }
        p { color: #64748b; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        th { background: #4f46e5; color: #fff; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        tr:nth-child(even) { background: #f8fafc; }
        .badge { background: #e0e7ff; color: #4338ca; padding: 3px 8px; border-radius: 6px; font-weight: bold; font-size: 11px; }
        .total-box { background: #eef2ff; border: 1px solid #c7d2fe; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: right; font-weight: bold; color: #3730a3; font-size: 16px; }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Daftar Pegawai Perbantuan (Lintas-Unit) di SMK</h2>
    <p>Tahun Pelajaran: <strong><?= htmlspecialchars($activeYear->year) ?></strong> | Semester: <strong><?= htmlspecialchars($activeSemester->semester_name) ?></strong></p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>Unit Asal</th>
                <th>Tugas / Jabatan di SMK</th>
                <th>Jam Mengajar</th>
                <th>Honor Mengajar</th>
                <th>Tunj. Jabatan</th>
                <th>Honor PKL</th>
                <th>Total THP SMK</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($crossEmployees) === 0): ?>
                <tr>
                    <td colspan="9" style="text-align:center; color:#94a3b8; padding:20px;">Tidak ada pegawai perbantuan lintas unit terdeteksi di SMK.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($crossEmployees as $i => $emp): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($emp['name']) ?></strong><br><span style="font-size:11px; color:#64748b;"><?= htmlspecialchars($emp['code']) ?> (<?= htmlspecialchars($emp['status']) ?>)</span></td>
                        <td><span class="badge"><?= htmlspecialchars($emp['home_unit']) ?></span></td>
                        <td><?= htmlspecialchars($emp['smk_position']) ?></td>
                        <td><?= $emp['teaching_hours'] ?> JP</td>
                        <td>Rp <?= number_format($emp['honor_mengajar'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($emp['tunjangan_jabatan'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($emp['honor_pkl'], 0, ',', '.') ?></td>
                        <td><strong>Rp <?= number_format($emp['thp'], 0, ',', '.') ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-box">
        Total Kompensasi Pegawai Perbantuan di SMK: Rp <?= number_format($totalCrossThp, 0, ',', '.') ?>
    </div>
</div>
</body>
</html>
