<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Teacher;

$names = [
    'SOZARO HAREFA',
    'MOLIRHATI TELAUMBANUA',
    'HERDIYANA LAHAGU',
    'HERDIYANI LAHAGU',
];

echo "=== INVESTIGASI USER/GURU ===\n\n";

foreach ($names as $name) {
    $user = User::where('name', 'like', "%$name%")->first();
    
    if (!$user) {
        echo "User '$name' tidak ditemukan.\n\n";
        continue;
    }
    
    echo "--- $name ---\n";
    echo "User ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "School ID: {$user->school_id}\n";
    echo "Status: " . ($user->is_active ? 'Aktif' : 'Non-aktif') . "\n";
    
    // Check if they have a teacher record
    $teacher = Teacher::where('user_id', $user->id)->first();
    if ($teacher) {
        echo "Teacher Record: ADA (ID: {$teacher->id})\n";
        echo "  - Full Name: {$teacher->full_name}\n";
        echo "  - Position: {$teacher->position}\n";
        echo "  - Is Active: " . ($teacher->is_active ? 'Ya' : 'Tidak') . "\n";
        echo "  - School ID: {$teacher->school_id}\n";
        
        // Check teaching assignments
        $assignments = DB::table('teaching_assignments')
            ->where('teacher_id', $teacher->id)
            ->count();
        echo "  - Teaching Assignments: {$assignments}\n";
        
        // Check schedules
        $schedules = DB::table('schedules')
            ->where('teacher_id', $teacher->id)
            ->count();
        echo "  - Jadwal Mengajar: {$schedules}\n";
        
        // Check LMS courses
        $courses = DB::table('lms_courses')
            ->where('teacher_id', $teacher->id)
            ->count();
        echo "  - LMS Courses: {$courses}\n";
    } else {
        echo "Teacher Record: TIDAK ADA\n";
    }
    
    // Check employee record
    $employee = DB::table('employees')->where('user_id', $user->id)->first();
    if ($employee) {
        echo "Employee Record: ADA (ID: {$employee->id})\n";
        echo "  - Jabatan: " . ($employee->position ?? '-') . "\n";
        
        // Check positions
        $positions = DB::table('employee_positions')
            ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
            ->where('employee_positions.employee_id', $employee->id)
            ->select('positions.position_name', 'employee_positions.is_active')
            ->get();
        
        if ($positions->isNotEmpty()) {
            echo "  - Jabatan Resmi:\n";
            foreach ($positions as $pos) {
                echo "    * {$pos->position_name} (" . ($pos->is_active ? 'Aktif' : 'Non-aktif') . ")\n";
            }
        }
    } else {
        echo "Employee Record: TIDAK ADA\n";
    }
    
    echo "\n";
}
