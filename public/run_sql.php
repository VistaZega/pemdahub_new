<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    $assignments = \App\Models\TeachingAssignment::with(['subject', 'classroom.school'])->where('teacher_id', 209)->get();
    
    $results = [];
    foreach($assignments as $a) {
        $results[] = [
            'mapel' => $a->subject ? $a->subject->name : 'N/A',
            'kelas' => $a->classroom ? $a->classroom->name : 'N/A',
            'sekolah' => ($a->classroom && $a->classroom->school) ? $a->classroom->school->name : 'N/A',
            'jam_per_minggu' => $a->hours_per_week,
            'academic_year_id' => $a->academic_year_id,
            'semester_id' => $a->semester_id,
            'hari' => $a->day_of_week,
            'jam' => $a->start_time . ' - ' . $a->end_time
        ];
    }

    echo json_encode([
        'assignments' => $results
    ], JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
