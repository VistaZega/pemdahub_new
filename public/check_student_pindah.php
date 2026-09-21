<?php
// Temporary diagnostic script to inspect students with status 'pindah'
$token = $_GET['token'] ?? '';
if ($token !== 'pembda2026check') {
    die('Forbidden');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

$studentsPindah = App\Models\Student::whereIn('status', ['pindah', 'keluar', 'lulus', 'dikeluarkan'])
    ->with(['studentClasses' => function($q) {
        $q->with('classroom');
    }, 'school'])
    ->get()
    ->map(function($s) {
        return [
            'id' => $s->id,
            'nisn' => $s->nisn,
            'name' => $s->full_name,
            'status' => $s->status,
            'school' => $s->school?->name,
            'classes' => $s->studentClasses->map(fn($sc) => [
                'id' => $sc->id,
                'class_name' => $sc->classroom?->class_name,
                'academic_year_id' => $sc->academic_year_id,
                'sc_status' => $sc->status,
            ]),
        ];
    });

echo json_encode([
    'total' => $studentsPindah->count(),
    'data' => $studentsPindah,
], JSON_PRETTY_PRINT);
