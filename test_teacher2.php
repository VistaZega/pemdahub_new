<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = \App\Models\Teacher::where('full_name', 'like', '%Yulianus%')->first();
if ($teacher) {
    echo "Guru: " . $teacher->full_name . " (ID: " . $teacher->id . ")\n";

    // Get their LMS courses
    $courses = \App\Models\LmsCourse::where('teacher_id', $teacher->id)->get();
    echo "\nLMS Courses found: " . $courses->count() . "\n";
    foreach ($courses as $c) {
        $className = $c->classroom ? $c->classroom->name : 'No Class';
        echo "- Course: " . $c->course_name . " (ID: " . $c->id . ") | Kelas: " . $className . "\n";
    }
} else {
    echo "Teacher not found.\n";
}
