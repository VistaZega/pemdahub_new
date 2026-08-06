<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    $assignments = \Illuminate\Support\Facades\DB::select("
        SELECT 
            ta.id, 
            s.name as subject_name, 
            c.name as class_name, 
            sch.name as school_name,
            ta.day_of_week,
            ta.start_time,
            ta.end_time
        FROM teaching_assignments ta
        LEFT JOIN subjects s ON ta.subject_id = s.id
        LEFT JOIN classrooms c ON ta.classroom_id = c.id
        LEFT JOIN schools sch ON c.school_id = sch.id
        WHERE ta.teacher_id = 209
    ");

    echo json_encode([
        'assignments' => $assignments
    ], JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
