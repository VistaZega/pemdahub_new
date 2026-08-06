<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = App\Models\Teacher::where('user_id', 273)->first();
if (!$teacher) {
    echo "Teacher not found for user 273\n";
    exit;
}
echo "Teacher ID: " . $teacher->id . "\n";
echo "School ID: " . $teacher->school_id . "\n";

$assignments = App\Models\TeachingAssignment::where('teacher_id', $teacher->id)->get();
echo "Assignments: " . $assignments->count() . "\n";
foreach($assignments as $a) {
    echo " - Mapel: " . $a->subject_id . " | School: " . $a->school_id . "\n";
}

echo "Pivot schools: " . $teacher->additionalSchools()->count() . "\n";
foreach($teacher->additionalSchools as $s) {
    echo " - Pivot School: " . $s->id . "\n";
}
