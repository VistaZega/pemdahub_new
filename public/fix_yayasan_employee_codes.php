<?php
// Standalone script for Yayasan Employee Code formatting (PTY-001...) & Position Level Fix
if (($_GET['secret'] ?? '') !== 'pembda99') {
    die('Unauthorized');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Teacher;
use App\Models\Position;
use App\Models\EmployeePosition;
use App\Models\AcademicYear;
use App\Models\School;

header('Content-Type: text/html; charset=utf-8');
echo "<body style='background:#111; color:#0f0; font-family:monospace; padding:20px;'>";
echo "<h1>=== PERAPIAN KODE PEGAWAI YAYASAN (PTY-001...) & HIRARKI JABATAN ===</h1>";

try {
    DB::beginTransaction();

    $yayasanSchool = School::where('type', 'yayasan')->first();
    if (!$yayasanSchool) {
        throw new Exception("Sekolah/Unit Yayasan tidak ditemukan.");
    }

    $activeYear = AcademicYear::where('is_active', true)->first();
    if (!$activeYear) {
        $activeYear = AcademicYear::latest('id')->first();
    }

    echo "1. Menyesuaikan Position Level untuk Jabatan Yayasan...<br>";
    // Ensure Yayasan positions have correct position_level
    Position::whereIn('position_name', ['Ketua Yayasan', 'Bendahara Yayasan', 'Pengawas Yayasan', 'Pembina'])
        ->update(['position_level' => 1]);

    // Ensure Employee ID 209 (Ketua Yayasan) has active position assignment in active academic year
    $empKetua = Employee::where('full_name', 'LIKE', '%Yulianus Zega%')->first();
    if ($empKetua) {
        $posKetua = Position::where('position_name', 'Ketua Yayasan')->first();
        if ($posKetua && $activeYear) {
            EmployeePosition::updateOrCreate(
                [
                    'employee_id' => $empKetua->id,
                    'position_id' => $posKetua->id,
                    'academic_year_id' => $activeYear->id,
                ],
                [
                    'is_primary' => true,
                    'start_date' => now()->startOfYear(),
                    'end_date' => null,
                ]
            );
            echo "   - Active EmployeePosition diset untuk Ketua Yayasan (AY ID: {$activeYear->id})<br>";
        }
    }

    echo "<br>2. Mengambil Seluruh Pegawai Unit Yayasan (school_id = {$yayasanSchool->id})...<br>";

    // Fetch all Yayasan employees ordered by position level ASC, then name ASC
    $yayasanEmployees = Employee::with(['activePositions', 'teacher'])
        ->where('school_id', $yayasanSchool->id)
        ->addSelect(['min_position_level' => function ($q) use ($activeYear) {
            $q->selectRaw('COALESCE(MIN(positions.position_level), 999)')
                ->from('employee_positions')
                ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                ->whereColumn('employee_positions.employee_id', 'employees.id')
                ->whereNull('employee_positions.end_date');
        }])
        ->orderBy('min_position_level', 'asc')
        ->orderBy('full_name', 'asc')
        ->get();

    echo "Ditemukan " . $yayasanEmployees->count() . " Pegawai Yayasan.<br><br>";

    // Temporary rename codes to avoid unique constraint collisions during re-indexing
    foreach ($yayasanEmployees as $idx => $emp) {
        $emp->employee_code = "TEMP_PTY_" . $emp->id;
        $emp->save();
        if ($emp->teacher) {
            $emp->teacher->teacher_code = "TEMP_PTY_" . $emp->id;
            $emp->teacher->save();
        }
    }

    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; color:#fff; border-color:#444;'>";
    echo "<tr style='background:#222; color:#0f0;'>
            <th>No</th>
            <th>ID Pegawai</th>
            <th>Nama Lengkap</th>
            <th>Jabatan Aktif</th>
            <th>Position Level</th>
            <th>Kode Pegawai Baru</th>
          </tr>";

    $index = 1;
    foreach ($yayasanEmployees as $emp) {
        $newCode = sprintf("PTY-%03d", $index);
        
        $emp->employee_code = $newCode;
        $emp->save();

        if ($emp->teacher) {
            $emp->teacher->teacher_code = $newCode;
            $emp->teacher->school_id = $yayasanSchool->id;
            $emp->teacher->save();
        }

        $posNames = $emp->activePositions->pluck('position_name')->implode(', ') ?: 'Tanpa Jabatan';
        $level = $emp->min_position_level;

        echo "<tr>";
        echo "<td>{$index}</td>";
        echo "<td>{$emp->id}</td>";
        echo "<td><b>{$emp->full_name}</b></td>";
        echo "<td>{$posNames}</td>";
        echo "<td align='center'>{$level}</td>";
        echo "<td style='color:#00ff88; font-weight:bold;'>{$newCode}</td>";
        echo "</tr>";

        $index++;
    }
    echo "</table>";

    DB::commit();
    echo "<h2 style='color:#00ff88;'>SUCCESS! Kode Pegawai Yayasan berhasil dirapikan menjadi format PTY-001, PTY-002, dst. dan diurutkan berdasarkan hirarki jabatan.</h2>";

} catch (Exception $e) {
    DB::rollBack();
    echo "<h2 style='color:#ff5555;'>ERROR: " . $e->getMessage() . "</h2>";
}
echo "</body>";
