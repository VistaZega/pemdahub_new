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
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    $yayasanSchool = School::where('type', 'yayasan')->first();
    if (!$yayasanSchool) {
        throw new Exception("Sekolah/Unit Yayasan tidak ditemukan.");
    }

    // Primary employee (SMK-025 or matching Yulianus Zega with assignments)
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

    // Find primary user or create/use existing user
    $primaryUser = $empPrimary->user;
    if (!$primaryUser) {
        $primaryUser = User::where('name', 'LIKE', '%Yulianus Zega%')
            ->orWhere('username', 'LIKE', '%yul%')
            ->first();
    }

    if ($primaryUser) {
        $primaryUser->role = 'superadmin';
        $primaryUser->name = 'Yulianus Zega, S.Kom, M.Pd';
        $primaryUser->school_id = $yayasanSchool->id;
        $primaryUser->save();
        $empPrimary->user_id = $primaryUser->id;
        echo "Primary User Set: ID {$primaryUser->id} ({$primaryUser->username})<br>";
    }

    // Find duplicates
    $duplicates = Employee::where('full_name', 'LIKE', '%Yulianus Zega%')
        ->where('id', '!=', $empPrimary->id)
        ->get();

    foreach ($duplicates as $dup) {
        echo "Processing Duplicate Employee ID {$dup->id} ({$dup->full_name})...<br>";
        
        // Re-assign any employee references
        DB::table('employee_attendances')->where('employee_id', $dup->id)->update(['employee_id' => $empPrimary->id]);
        DB::table('employee_positions')->where('employee_id', $dup->id)->update(['employee_id' => $empPrimary->id]);
        DB::table('employee_leaves')->where('employee_id', $dup->id)->update(['employee_id' => $empPrimary->id]);

        if (DB::getSchemaBuilder()->hasTable('payrolls')) {
            DB::table('payrolls')->where('employee_id', $dup->id)->update(['employee_id' => $empPrimary->id]);
        }

        // Handle duplicate user account
        if ($dup->user_id && $primaryUser && $dup->user_id != $primaryUser->id) {
            $dupUserId = $dup->user_id;
            echo "Re-assigning user references for User ID {$dupUserId} to Primary User ID {$primaryUser->id}...<br>";
            
            DB::table('employee_attendances')->where('recorded_by', $dupUserId)->update(['recorded_by' => $primaryUser->id]);
            if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
                DB::table('activity_logs')->where('user_id', $dupUserId)->update(['user_id' => $primaryUser->id]);
            }
            if (DB::getSchemaBuilder()->hasTable('login_histories')) {
                DB::table('login_histories')->where('user_id', $dupUserId)->update(['user_id' => $primaryUser->id]);
            }

            User::where('id', $dupUserId)->delete();
        }

        Teacher::where('employee_id', $dup->id)->delete();
        $dup->delete();
    }

    $desiredCode = 'YYS-002';

    $empPrimary->full_name = 'Yulianus Zega, S.Kom, M.Pd';
    $empPrimary->school_id = $yayasanSchool->id;
    $empPrimary->employee_code = $desiredCode;
    $empPrimary->employee_type = 'other'; // Set to 'other' so appears in both Data Pegawai and Data Guru
    $empPrimary->employment_status = 'yayasan';
    $empPrimary->save();

    $teacherPrimary = Teacher::where('employee_id', $empPrimary->id)->first();
    if ($teacherPrimary) {
        $teacherPrimary->full_name = 'Yulianus Zega, S.Kom, M.Pd';
        $teacherPrimary->teacher_code = $desiredCode;
        $teacherPrimary->school_id = $yayasanSchool->id;
        if ($primaryUser) {
            $teacherPrimary->user_id = $primaryUser->id;
        }
        $teacherPrimary->save();
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    DB::commit();
    echo "<h2 style='color:#00ff88;'>SUCCESS! Data Ketua Yayasan telah disatukan secara aman di Production.</h2>";

} catch (Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    DB::rollBack();
    echo "<h2 style='color:#ff5555;'>ERROR: " . $e->getMessage() . "</h2>";
}
echo "</body>";
