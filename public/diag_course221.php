<?php
/**
 * Diagnostik: Mengapa Course 221 menampilkan 74 siswa?
 * Akses: perguruanpembda.com/diag_course221.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { die('Forbidden'); }

require __DIR__ . '/../pembdahub/vendor/autoload.php';
$app = require_once __DIR__ . '/../pembdahub/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

$course = App\Models\LmsCourse::with(['lmsClasses.classroom', 'subject'])->find(221);
echo "=== DIAGNOSTIK COURSE 221 ===\n";
echo "Course Name: {$course->course_name}\n";
echo "Subject: " . ($course->subject?->name ?? $course->subject?->subject_name ?? 'NULL') . "\n";
echo "Academic Year ID: {$course->academic_year_id}\n\n";

// Semua LmsClass yang terhubung ke course ini
$lmsClasses = $course->lmsClasses;
echo "=== LMS CLASSES TERHUBUNG ({$lmsClasses->count()}) ===\n";
foreach ($lmsClasses as $lc) {
    $cr = $lc->classroom;
    $enCount = App\Models\LmsEnrollment::where('lms_class_id', $lc->id)->count();
    echo "- LmsClass ID {$lc->id}: Classroom ID {$lc->classroom_id}\n";
    echo "  Nama: '{$cr?->class_name}'\n";
    echo "  TP: {$cr?->academic_year_id}, Grade: {$cr?->grade_level}\n";
    echo "  is_combined: {$cr?->is_combined}, class_type: {$cr?->class_type}\n";
    echo "  Enrollment: {$enCount} siswa\n\n";
}

// Semua enrollment dan kelas reguler siswa
$allLmsClassIds = $lmsClasses->pluck('id');
$enrollments = App\Models\LmsEnrollment::whereIn('lms_class_id', $allLmsClassIds)
    ->with(['student.classrooms', 'lmsClass.classroom'])
    ->get();

echo "=== TOTAL ENROLLMENT: {$enrollments->count()} ===\n\n";

// Per siswa: tampilkan SEMUA kelas yang dimiliki
echo "=== DETAIL SISWA & SEMUA KELAS MEREKA ===\n";
$regularClassCount = [];
foreach ($enrollments as $i => $en) {
    $st = $en->student;
    $lmsClassroom = $en->lmsClass?->classroom;
    
    // Kelas reguler (non-gabungan)
    $regularClasses = $st->classrooms->filter(function($cls) {
        return !$cls->is_combined && $cls->class_type !== 'gabungan';
    });
    
    $regName = $regularClasses->pluck('class_name')->join(', ') ?: '(TIDAK ADA KELAS REGULER)';
    
    foreach ($regularClasses as $rc) {
        $key = $rc->class_name;
        $regularClassCount[$key] = ($regularClassCount[$key] ?? 0) + 1;
    }
    
    if ($i < 5 || $enrollments->count() - $i <= 3) {
        echo ($i+1) . ". {$st->full_name}\n";
        echo "   Enrolled via LmsClass: '{$lmsClassroom?->class_name}'\n";
        echo "   Kelas Reguler: {$regName}\n";
        echo "   Semua kelas:\n";
        foreach ($st->classrooms as $c) {
            $flag = (!$c->is_combined && $c->class_type !== 'gabungan') ? ' [REGULER]' : ' [GABUNGAN]';
            echo "     - ID {$c->id}: '{$c->class_name}' (TP:{$c->academic_year_id}, Grade:{$c->grade_level}, is_combined:{$c->is_combined}, class_type:{$c->class_type}){$flag}\n";
        }
        echo "\n";
    } elseif ($i === 5) {
        echo "... (siswa 6 s/d " . ($enrollments->count() - 3) . " disingkat) ...\n\n";
    }
}

echo "=== RINGKASAN KELAS REGULER SISWA ===\n";
arsort($regularClassCount);
foreach ($regularClassCount as $name => $count) {
    echo "- '{$name}': {$count} siswa\n";
}
echo "\nTotal siswa dengan kelas reguler: " . array_sum($regularClassCount) . "\n";
echo "Total enrollment: {$enrollments->count()}\n";
