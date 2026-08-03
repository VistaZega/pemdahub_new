<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Cari langsung berdasarkan teacher ID yang terlihat di halaman
$teacherIds = [43, 44, 45, 46, 47]; // Sozaro=43, Molirhati=44, Herdiyana=45, Herdiyani=46, Herlinawati=47

echo "=== INVESTIGASI TEACHER BY ID ===\n\n";

foreach ($teacherIds as $tid) {
    $teacher = DB::table('teachers')->where('id', $tid)->first();
    if (!$teacher) {
        echo "Teacher ID $tid: TIDAK DITEMUKAN\n\n";
        continue;
    }
    
    echo "Teacher ID: {$teacher->id}\n";
    echo "  Full Name: {$teacher->full_name}\n";
    echo "  User ID: {$teacher->user_id}\n";
    echo "  School ID: {$teacher->school_id}\n";
    echo "  Position: " . ($teacher->position ?? '-') . "\n";
    echo "  Is Active: " . ($teacher->is_active ? 'Ya' : 'Tidak') . "\n";
    
    // Cari user terkait
    $user = DB::table('users')->where('id', $teacher->user_id)->first();
    if ($user) {
        echo "  => User Email: {$user->email}\n";
        echo "  => User Role: {$user->role}\n";
        echo "  => User Name: {$user->name}\n";
    } else {
        echo "  => User NOT FOUND!\n";
    }
    echo "\n";
}

// Juga cari semua teacher records dengan email pattern _adminsmk
echo "=== SEMUA TEACHER DENGAN EMAIL PATTERN '_admin' ===\n\n";
$adminTeachers = DB::table('teachers')
    ->join('users', 'teachers.user_id', '=', 'users.id')
    ->where('users.email', 'like', '%_admin%')
    ->select('teachers.id', 'teachers.full_name', 'teachers.is_active', 'teachers.school_id', 'users.email', 'users.role')
    ->get();

foreach ($adminTeachers as $at) {
    echo "Teacher ID={$at->id} | {$at->full_name} | Email={$at->email} | Role={$at->role} | Active={$at->is_active}\n";
}

echo "\nTotal: " . $adminTeachers->count() . " teacher records dengan email pattern '_admin'\n";
