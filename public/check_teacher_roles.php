<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$names = [
    'SOZARO HAREFA',
    'MOLIRHATI TELAUMBANUA',
    'HERDIYANA LAHAGU',
    'HERDIYANI LAHAGU',
];

echo "=== INVESTIGASI USER/GURU ===\n\n";

foreach ($names as $name) {
    $user = DB::table('users')->where('name', 'like', "%$name%")->first();
    
    if (!$user) {
        echo "User '$name' tidak ditemukan.\n\n";
        continue;
    }
    
    echo "--- $name ---\n";
    echo "User ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "School ID: {$user->school_id}\n";
    
    // Check teacher record
    $teacher = DB::table('teachers')->where('user_id', $user->id)->first();
    if ($teacher) {
        echo "Teacher Record: ADA (ID: {$teacher->id})\n";
        echo "  Full Name: {$teacher->full_name}\n";
        echo "  Position: " . ($teacher->position ?? '-') . "\n";
        echo "  Is Active: " . ($teacher->is_active ? 'Ya' : 'Tidak') . "\n";
        echo "  School ID: {$teacher->school_id}\n";
        
        // Check teaching assignments
        $assignments = DB::table('teaching_assignments')
            ->where('teacher_id', $teacher->id)
            ->count();
        echo "  Teaching Assignments: {$assignments}\n";
        
        // Check schedules
        $schedules = DB::table('schedules')
            ->where('teacher_id', $teacher->id)
            ->count();
        echo "  Jadwal Mengajar: {$schedules}\n";
        
        // Check LMS courses
        $courses = DB::table('lms_courses')
            ->where('teacher_id', $teacher->id)
            ->count();
        echo "  LMS Courses: {$courses}\n";
    } else {
        echo "Teacher Record: TIDAK ADA\n";
    }
    
    echo "\n";
}

// Juga cek: ada berapa total user dengan role 'guru' dan 'admin' di SMK?
$smkSchool = DB::table('schools')->where('name', 'like', '%SMK%')->first();
if ($smkSchool) {
    $guruCount = DB::table('users')->where('school_id', $smkSchool->id)->where('role', 'guru')->count();
    $adminCount = DB::table('users')->where('school_id', $smkSchool->id)->where('role', 'admin')->count();
    $teacherCount = DB::table('teachers')->where('school_id', $smkSchool->id)->where('is_active', true)->count();
    
    echo "=== RINGKASAN SMK ===\n";
    echo "Users role 'guru': {$guruCount}\n";
    echo "Users role 'admin': {$adminCount}\n";
    echo "Teachers (is_active=true): {$teacherCount}\n";
    
    // List all admin users at SMK that also have teacher records
    $adminTeachers = DB::table('users')
        ->join('teachers', 'users.id', '=', 'teachers.user_id')
        ->where('users.school_id', $smkSchool->id)
        ->where('users.role', 'admin')
        ->select('users.name', 'users.email', 'users.role', 'teachers.id as teacher_id', 'teachers.full_name', 'teachers.position')
        ->get();
    
    echo "\nUser role='admin' TAPI punya record teacher:\n";
    foreach ($adminTeachers as $at) {
        echo "- {$at->name} ({$at->email}) | Teacher ID: {$at->teacher_id} | Position: " . ($at->position ?? '-') . "\n";
    }
}
