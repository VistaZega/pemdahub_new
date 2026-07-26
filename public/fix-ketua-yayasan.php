<?php
// Standalone script for Hostinger production DB sync
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
use App\Models\User;
use App\Models\School;

header('Content-Type: text/html; charset=utf-8');
echo "<body style='background:#111; color:#0f0; font-family:monospace; padding:20px;'>";
echo "<h1>=== PENYATUAN DATA KETUA YAYASAN (PRODUCTION FIX) ===</h1>";

try {
    DB::beginTransaction();

    $yayasanSchool = School::where('type', 'yayasan')->first();
    if (!$yayasanSchool) {
        throw new Exception("Sekolah/Unit Yayasan tidak ditemukan.");
    }

    // Primary employee (SMK-025 or matching Yulianus Zega)
    $empPrimary = Employee::where('full_name', 'LIKE', '%Yulianus Zega%')
        ->whereHas('teacher.teachingAssignments')
        ->first();

    if (!$empPrimary) {
        $empPrimary = Employee::where('full_name', 'LIKE', '%Yulianus Zega%')->first();
    }

    if (!$empPrimary) {
        throw new Exception("Data pegawai Yulianus Zega tidak ditemukan.");
    }

    echo "Primary Employee Found: ID {$empPrimary->id} ({$empPrimary->full_name})<br>";

    // Find duplicates
    $duplicates = Employee::where('full_name', 'LIKE', '%Yulianus Zega%')
        ->where('id', '!=', $empPrimary->id)
        ->get();

    foreach ($duplicates as $dup) {
        echo "Removing Duplicate Employee ID {$dup->id} ({$dup->full_name})...<br>";
        Teacher::where('employee_id', $dup->id)->delete();
        if ($dup->user_id && $dup->user_id != $empPrimary->user_id) {
            User::where('id', $dup->user_id)->delete();
        }
        $dup->delete();
    }

    $desiredCode = 'YYS-002';

    $empPrimary->full_name = 'Yulianus Zega, S.Kom, M.Pd';
    $empPrimary->school_id = $yayasanSchool->id;
    $empPrimary->employee_code = $desiredCode;
    $empPrimary->employee_type = 'guru';
    $empPrimary->employment_status = 'yayasan';

    if ($empPrimary->user) {
        $empPrimary->user->role = 'superadmin';
        $empPrimary->user->name = 'Yulianus Zega, S.Kom, M.Pd';
        $empPrimary->user->school_id = $yayasanSchool->id;
        $empPrimary->user->save();
    }

    $empPrimary->save();

    $teacherPrimary = Teacher::where('employee_id', $empPrimary->id)->first();
    if ($teacherPrimary) {
        $teacherPrimary->full_name = 'Yulianus Zega, S.Kom, M.Pd';
        $teacherPrimary->teacher_code = $desiredCode;
        $teacherPrimary->school_id = $yayasanSchool->id;
        $teacherPrimary->save();
    }

    DB::commit();
    echo "<h2 style='color:#00ff88;'>SUCCESS! Data Ketua Yayasan telah disatukan secara aman.</h2>";

} catch (Exception $e) {
    DB::rollBack();
    echo "<h2 style='color:#ff5555;'>ERROR: " . $e->getMessage() . "</h2>";
}
echo "</body>";
