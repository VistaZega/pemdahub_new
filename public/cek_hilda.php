<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = \App\Models\Teacher::where("full_name", "like", "%Hilda%")->first();
if (!$teacher) {
    echo "Guru bernama Hilda tidak ditemukan di database production.\n";
    exit;
}

echo "=== Data Guru ===\n";
echo "Nama: " . $teacher->full_name . " (ID: " . $teacher->id . ")\n\n";

$courses = \App\Models\LmsCourse::where("teacher_id", $teacher->id)->get();
echo "Total Ruang Belajar (LMS Course): " . $courses->count() . "\n\n";

foreach ($courses as $c) {
    echo "--- Course: " . $c->course_name . " (ID Course: " . $c->id . ") ---\n";
    
    // Cek terkait ke rombel mana saja
    $classes = $c->lmsClasses()->with("classroom")->get();
    echo "Dihubungkan (Shared) ke " . $classes->count() . " Rombel/Kelas:\n";
    foreach ($classes as $class) {
        echo "  - " . ($class->classroom ? $class->classroom->class_name : "Unknown Class") . "\n";
    }
    
    // Cek materi di dalam course ini
    $materials = $c->materials()->get();
    echo "Jumlah Materi di Course Ini: " . $materials->count() . "\n";
    foreach ($materials as $m) {
        echo "  -> Judul Materi: " . $m->title . "\n";
    }
    echo "\n";
}

