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
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;
use App\Models\Teacher;
use App\Models\User;
use App\Models\School;

header('Content-Type: text/html; charset=utf-8');
echo "<body style='background:#111; color:#0f0; font-family:monospace; padding:20px;'>";
echo "<h1>=== PENYATUAN DATA & AKSES LOGIN KETUA YAYASAN (PRODUCTION FIX) ===</h1>";

try {
    DB::beginTransaction();
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    $yayasanSchool = School::where('type', 'yayasan')->first();
    if (!$yayasanSchool) {
        throw new Exception("Sekolah/Unit Yayasan tidak ditemukan.");
    }

    // Find primary user with yulzega@gmail.com or username yulzega or Yulianus Zega
    $primaryUser = User::where('email', 'yulzega@gmail.com')
        ->orWhere('username', 'yulzega')
        ->orWhere('name', 'LIKE', '%Yulianus Zega%')
        ->first();

    if (!$primaryUser) {
        $primaryUser = new User();
    }

    $primaryUser->name = 'Yulianus Zega, S.Kom, M.Pd';
    $primaryUser->email = 'yulzega@gmail.com';
    $primaryUser->username = 'yulzega';
    $primaryUser->role = 'superadmin';
    $primaryUser->school_id = $yayasanSchool->id;
    $primaryUser->is_active = true;

    // Password handling: update if ?password=... parameter is provided
    if (isset($_GET['password']) && !empty($_GET['password'])) {
        $newPwd = trim($_GET['password']);
        $primaryUser->password = Hash::make($newPwd);
        echo "<span style='color:#ff0;'>Password berhasil diperbarui/reset menjadi: '{$newPwd}'</span><br>";
    } elseif (empty($primaryUser->password)) {
        $primaryUser->password = Hash::make('pembda2026');
        echo "<span style='color:#ff0;'>Password baru dibuat dengan default: 'pembda2026'</span><br>";
    } else {
        echo "Password lama tetap dipertahankan.<br>";
    }

    $primaryUser->save();

    echo "Primary User Account Ready: ID {$primaryUser->id} | Email: {$primaryUser->email} | Username: {$primaryUser->username} | Active: YES<br>";

    // Find primary employee (SMK-025 or matching Yulianus Zega)
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

    // Link employee to primary user
    $empPrimary->user_id = $primaryUser->id;

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

        // Handle duplicate user account (if any other user exists)
        if ($dup->user_id && $dup->user_id != $primaryUser->id) {
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

    // Clean up any remaining extra users with same name/email that aren't primaryUser
    $extraUsers = User::where(function($q) {
        $q->where('email', 'yulianus@smk.pembdahub.com')
          ->orWhere('username', 'yulianus');
    })->where('id', '!=', $primaryUser->id)->get();

    foreach ($extraUsers as $eu) {
        echo "Cleaning up old extra user ID {$eu->id} ({$eu->username})...<br>";
        DB::table('employee_attendances')->where('recorded_by', $eu->id)->update(['recorded_by' => $primaryUser->id]);
        if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
            DB::table('activity_logs')->where('user_id', $eu->id)->update(['user_id' => $primaryUser->id]);
        }
        if (DB::getSchemaBuilder()->hasTable('login_histories')) {
            DB::table('login_histories')->where('user_id', $eu->id)->update(['user_id' => $primaryUser->id]);
        }
        $eu->delete();
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
        $teacherPrimary->user_id = $primaryUser->id;
        $teacherPrimary->save();
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    DB::commit();
    echo "<h2 style='color:#00ff88;'>SUCCESS! Akun Login 'yulzega@gmail.com' / 'yulzega' dan Data Ketua Yayasan telah SINKRON 100%.</h2>";

} catch (Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    DB::rollBack();
    echo "<h2 style='color:#ff5555;'>ERROR: " . $e->getMessage() . "</h2>";
}
echo "</body>";
