<?php
$secret = $_GET["secret"] ?? "";
if ($secret !== "pembda99") { die("Unauthorized"); }

require_once __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\Student;
use App\Models\Classroom;
use App\Models\LmsCourse;
use App\Models\LmsClass;
use App\Models\LmsEnrollment;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\BlockSchedule;
use App\Models\BlockStudentGroup;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

header("Content-Type: text/html; charset=utf-8");

echo "<pre style=\"font-family:Consolas,monospace;font-size:13px;background:#0f172a;color:#f8fafc;padding:20px;\">";
echo "=== 1. DETAIL LENGKAP COURSE 221 ===\n";
$course = LmsCourse::with(['subject', 'teacher.user', 'lmsClasses.classroom', 'modules'])->find(221);
if (!$course) {
    echo "COURSE 221 NOT FOUND!\n";
} else {
    echo "ID: " . $course->id . "\n";
    echo "course_name: " . $course->course_name . "\n";
    echo "code: " . $course->code . "\n";
    echo "teacher_id: " . $course->teacher_id . " (" . ($course->teacher?->user?->name ?? 'N/A') . ")\n";
    echo "school_id: " . var_export($course->school_id, true) . "\n";
    echo "subject_id: " . $course->subject_id . " (" . ($course->subject?->name ?? 'N/A') . ")\n";
    echo "semester_id: " . $course->semester_id . "\n";
    echo "classroom_id: " . var_export($course->classroom_id, true) . "\n";
    echo "status: " . $course->status . "\n";
    echo "is_active: " . var_export($course->is_active, true) . "\n";
    echo "is_published: " . var_export($course->is_published, true) . "\n";
    echo "is_sequential: " . var_export($course->is_sequential, true) . "\n";
    echo "modules_count: " . $course->modules->count() . "\n";
    echo "lms_classes:\n";
    foreach ($course->lmsClasses as $lc) {
        $enrCount = LmsEnrollment::where('lms_class_id', $lc->id)->count();
        echo "  - lms_class_id: {$lc->id}, classroom_id: {$lc->classroom_id} ({$lc->classroom?->class_name}), enrollments: {$enrCount}\n";
    }
}

echo "\n=== 2. SIMULASI SISWA JOY WISE HAREFA (ID: 768, user_id: 338) ===\n";
$joy = Student::with(['user', 'classrooms'])->find(768);
echo "Joy school_id: " . var_export($joy->school_id, true) . "\n";
echo "Joy user_id: " . var_export($joy->user_id, true) . "\n";

// Login as Joy
Auth::loginUsingId(338);

// Exact query from LmsController@index
$enrollments = LmsEnrollment::where('student_id', $joy->id)
    ->whereIn('status', ['enrolled', 'in_progress'])
    ->whereHas('lmsClass.course', function($q) use ($joy) {
        if ($joy->school_id) {
            $q->where(function($sq) use ($joy) {
                $sq->where('school_id', $joy->school_id)
                   ->orWhereNull('school_id');
            });
        }
    })
    ->with(['lmsClass.course' => fn($q) => $q->with(['subject', 'teacher.user', 'modules' => fn($mq) => $mq->orderBy('sequence')])
        ->withCount(['modules', 'materials', 'assignments', 'quizzes', 'discussions']),
            'lmsClass.classroom'])
    ->get();

$courses = $enrollments->map(fn($e) => $e->lmsClass->course)->filter()->unique('id');
echo "Total courses returned in LmsController@index: " . $courses->count() . "\n";
$has221 = $courses->contains('id', 221);
echo "Does courses contain 221? " . ($has221 ? "YES!" : "NO!") . "\n";

echo "List of all course IDs in index:\n";
foreach ($courses as $c) {
    echo "  [{$c->id}] {$c->course_name} (Teacher: " . ($c->teacher?->user?->name ?? 'N/A') . ")\n";
}

echo "\n=== 3. CEK JADWAL & JUMP SCHEDULE (SISWA BUKA DARI JADWAL) ===\n";
$activeYear = AcademicYear::where('is_active', true)->first();
$activeSem = Semester::where('is_active', true)->first();
echo "Active Year: " . ($activeYear?->year_name ?? $activeYear?->id) . " (ID: {$activeYear?->id})\n";
echo "Active Sem: " . ($activeSem?->name ?? $activeSem?->id) . " (ID: {$activeSem?->id})\n";

$schedules = Schedule::where('classroom_id', 370)
    ->with(['subject', 'teacher.user', 'timeSlot'])
    ->get();
echo "Schedules for Class 370 (XI Teknik Rekayasa): " . $schedules->count() . "\n";
foreach ($schedules as $s) {
    $guru = $s->teacher?->user?->name ?? 'N/A';
    $mapel = $s->subject?->name ?? 'N/A';
    $isYulianus = str_contains($guru, 'Yulianus');
    $star = $isYulianus ? " ⭐ [YULIANUS ZEGA]" : "";
    echo "  Schedule ID: {$s->id} | Hari: {$s->day_of_week} | {$mapel} | {$guru}{$star}\n";
    if ($isYulianus) {
        echo "    subject_id on schedule: {$s->subject_id}\n";
        echo "    course 221 subject_id: {$course?->subject_id}\n";
        echo "    teacher_id on schedule: {$s->teacher_id}\n";
        echo "    course 221 teacher_id: {$course?->teacher_id}\n";
        echo "    schedule classroom_id: {$s->classroom_id}\n";
        echo "    Joy currentClassroom ID: " . ($joy->currentClassroom()->first()?->id ?? 'NULL') . "\n";
    }
}

echo "\n=== 4. CEK BLOK SCHEDULE & KELOMPOK SISWA (LAB vs KELAS) ===\n";
$blockSchedule = BlockSchedule::where('academic_year_id', $activeYear?->id)
    ->where('is_active', true)
    ->first();
if (!$blockSchedule) {
    echo "NO ACTIVE BLOCK SCHEDULE FOUND!\n";
} else {
    echo "BlockSchedule ID: {$blockSchedule->id}\n";
    echo "Name: {$blockSchedule->name}\n";
    echo "Start Date: " . $blockSchedule->start_date->format('Y-m-d') . "\n";
    echo "End Date: " . $blockSchedule->end_date->format('Y-m-d') . "\n";
    echo "Swap Interval: {$blockSchedule->swap_interval_weeks} weeks\n";
    
    $today = date('Y-m-d');
    $weekNum = $blockSchedule->getWeekNumber($today);
    $rotation = $blockSchedule->getActiveRotationForDate($today);
    echo "Today: {$today} (Week {$weekNum}) -> Rotation: {$rotation}\n";
}

// Cek group siswa di kelas 370
$groups = BlockStudentGroup::where('classroom_id', 370)->get();
echo "\nBlockStudentGroups for Class 370: " . $groups->count() . " siswa assigned\n";
$groupA = $groups->where('group', 'A');
$groupB = $groups->where('group', 'B');
echo "  Group A: " . $groupA->count() . " siswa\n";
echo "  Group B: " . $groupB->count() . " siswa\n";
$joyGroup = $groups->firstWhere('student_id', 768);
echo "  Joy Wise Harefa Group: " . ($joyGroup?->group ?? 'BELUM DI-ASSIGN') . "\n";

echo "\nTeaching Assignments for Class 370:\n";
$tas = TeachingAssignment::where('classroom_id', 370)
    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
    ->with(['teacher.user', 'subject'])
    ->get();
foreach ($tas as $ta) {
    $tname = $ta->teacher?->user?->name ?? 'N/A';
    $sname = $ta->subject?->name ?? 'N/A';
    echo "  TA ID: {$ta->id} | {$sname} | {$tname} | block_type: " . var_export($ta->block_type, true) . " | group_code: " . var_export($ta->group_code, true) . "\n";
}

echo "\n=== 5. ANALISIS ABSENSI GURU (DASHBOARD CONTROLLER LOGIC) ===\n";
$yuliTA = $tas->first(fn($t) => str_contains($t->teacher?->user?->name ?? '', 'Yulianus'));
if ($yuliTA) {
    echo "Yulianus TA ID: {$yuliTA->id}\n";
    echo "block_type: {$yuliTA->block_type}\n";
    $rotation = $blockSchedule ? $blockSchedule->getActiveRotationForDate(date('Y-m-d')) : 'normal';
    echo "Active Rotation: {$rotation}\n";
    
    // Logic from DashboardController:
    if ($yuliTA->block_type === 'split') {
        $tg = ($rotation === 'normal') ? 'A' : 'B';
    } else {
        $tg = ($rotation === 'normal') ? 'B' : 'A';
    }
    echo "Calculated targetGroup in DashboardController: '{$tg}'\n";
    echo "Meaning: System expects Group '{$tg}' to be in this class!\n";
}

echo "</pre>";
