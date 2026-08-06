<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    $materials = DB::select('SELECT m.id, m.title, m.content FROM lms_materials m JOIN lms_modules mo ON m.module_id = mo.id WHERE mo.course_id = 221');
    $broken = [];
    foreach ($materials as $m) {
        $c = $m->content;
        $divs = substr_count($c, '<div');
        $close = substr_count($c, '</div');
        if ($divs != $close) {
            $broken[] = ['id' => $m->id, 'title' => $m->title, 'open' => $divs, 'close' => $close];
        }
    }

    echo json_encode([
        'broken_divs' => $broken
    ], JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
