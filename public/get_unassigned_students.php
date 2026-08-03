<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\AcademicYear;

$activeYear = AcademicYear::where('is_active', true)->first();
if (!$activeYear) {
    echo json_encode(['error' => 'No active academic year found.']);
    exit;
}

$students = Student::active()
    ->whereDoesntHave('studentClasses', function($q) use ($activeYear) {
        $q->where('academic_year_id', $activeYear->id);
    })
    ->with('school')
    ->get();

$results = [];
foreach ($students as $s) {
    $results[] = [
        'NIS' => $s->nis,
        'Nama' => $s->full_name,
        'Sekolah' => $s->school ? $s->school->name : '-',
        'Gender' => $s->gender
    ];
}

echo json_encode(['count' => count($results), 'year' => $activeYear->year, 'data' => $results]);
