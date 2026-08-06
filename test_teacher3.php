<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = \App\Models\Teacher::where('full_name', 'like', '%Yulianus%')->first();
if ($teacher) {
    echo "Guru: " . $teacher->full_name . " (ID: " . $teacher->id . ")\n";

    // Get their teaching assignments
    $assignments = \App\Models\TeachingAssignment::with(['classroom', 'subject'])->where('teacher_id', $teacher->id)->get();
    echo "\nTeaching Assignments found: " . $assignments->count() . "\n";
    foreach ($assignments as $a) {
        $className = $a->classroom ? $a->classroom->name : 'N/A';
        $classId = $a->classroom ? $a->classroom->id : 'N/A';
        $subjectName = $a->subject ? $a->subject->name : 'N/A';
        $subjectId = $a->subject ? $a->subject->id : 'N/A';
        echo "- Kelas: $className (ID: $classId) | Mapel: $subjectName (ID: $subjectId)\n";
    }

    // Get their LMS courses
    $courses = \App\Models\LmsCourse::with(['classroom', 'subject'])->where('teacher_id', $teacher->id)->get();
    echo "\nLMS Courses found: " . $courses->count() . "\n";
    foreach ($courses as $c) {
        $className = $c->classroom ? $c->classroom->name : 'No Class';
        $subjectName = $c->subject ? $c->subject->name : 'No Subject';
        echo "- Course: " . $c->course_name . " (ID: " . $c->id . ") | Kelas: " . $className . " | Mapel: " . $subjectName . "\n";
    }
} else {
    echo "Teacher not found.\n";
}
