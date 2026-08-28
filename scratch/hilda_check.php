<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = \App\Models\Teacher::where("full_name", "like", "%Hilda%")->first();
if (!$teacher) {
    echo "Guru tidak ditemukan.\n";
    exit;
}
echo "Guru: " . $teacher->full_name . " (ID: " . $teacher->id . ")\n";

$courses = \App\Models\LmsCourse::where("teacher_id", $teacher->id)->get();
echo "Total Ruang Belajar (Course): " . $courses->count() . "\n\n";

foreach ($courses as $c) {
    echo "=== Course: " . $c->course_name . " (ID: " . $c->id . ") ===\n";
    $classes = $c->lmsClasses()->with("classroom")->get();
    echo "Terkait dengan " . $classes->count() . " Kelas/Rombel:\n";
    foreach ($classes as $class) {
        echo "  - " . ($class->classroom ? $class->classroom->class_name : "Unknown") . "\n";
    }
    
    $materials = $c->materials()->get();
    echo "Jumlah Materi: " . $materials->count() . "\n";
    foreach ($materials as $m) {
        echo "  -> Materi: " . $m->title . "\n";
    }
    echo "\n";
}

