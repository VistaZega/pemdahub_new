<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$grades = \App\Models\Grade::with(['student', 'subject', 'semester'])->take(5)->get();
echo "GRADES:\n";
foreach ($grades as $g) {
    echo "ID: {$g->id}, Student: {$g->student_id}, Subject: {$g->subject_id}, Semester: {$g->semester_id}, Type: {$g->grade_type}, Score: {$g->score}\n";
}

$semesters = \App\Models\Semester::where('is_active', true)->get();
echo "\nACTIVE SEMESTERS:\n";
foreach ($semesters as $s) {
    echo "ID: {$s->id}, Name: {$s->semester_name}\n";
}
