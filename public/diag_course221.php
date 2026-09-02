<?php
/**
 * Diagnostik: Dari mana 117 enrollment di LmsClass 417?
 * Akses: perguruanpembda.com/diag_course221.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { die('Forbidden'); }

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

echo "=== ASAL-USUL 117 ENROLLMENT DI LMSCLASS 417 ===\n\n";

// LmsClass 417 -> Classroom 370 (XI Teknik Rekayasa (DPIB, TAV))
$enrollments = App\Models\LmsEnrollment::where('lms_class_id', 417)
    ->with(['student.classrooms'])
    ->orderBy('created_at')
    ->get();

echo "Total enrollment: {$enrollments->count()}\n\n";

// Kapan enrollment dibuat?
$byDate = $enrollments->groupBy(fn($e) => $e->created_at?->format('Y-m-d H:i') ?? 'NULL');
echo "=== WAKTU ENROLLMENT DIBUAT ===\n";
foreach ($byDate as $date => $group) {
    echo "- {$date}: {$group->count()} siswa\n";
}

// Siswa yang enrolled tapi BUKAN anggota classroom 370
$classroom370 = App\Models\Classroom::find(370);
$actualMemberIds = $classroom370->students()->pluck('students.id')->toArray();

$ghost = $enrollments->filter(fn($e) => !in_array($e->student_id, $actualMemberIds));
$legit = $enrollments->filter(fn($e) => in_array($e->student_id, $actualMemberIds));

echo "\n=== ENROLLMENT VALID vs HANTU ===\n";
echo "Anggota kelas 370 yang enrolled (VALID): {$legit->count()}\n";
echo "BUKAN anggota kelas 370 tapi enrolled (HANTU): {$ghost->count()}\n";

// Dari classroom mana saja siswa hantu berasal?
echo "\n=== SISWA HANTU - DARI CLASSROOM MANA? ===\n";
$ghostByClass = [];
foreach ($ghost as $e) {
    $st = $e->student;
    $classrooms = $st->classrooms->pluck('class_name')->toArray();
    $key = implode(' + ', $classrooms) ?: '(tidak ada kelas)';
    $ghostByClass[$key] = ($ghostByClass[$key] ?? 0) + 1;
}
arsort($ghostByClass);
foreach ($ghostByClass as $cls => $count) {
    echo "- {$cls}: {$count} siswa\n";
}

// Cek apakah siswa hantu ini terdaftar di classroom lain yang juga punya LmsClass untuk course 221
echo "\n=== APAKAH ADA LMSCLASS LAIN UNTUK COURSE 221? ===\n";
$otherLmsClasses = App\Models\LmsClass::where('course_id', 221)->where('id', '!=', 417)->with('classroom')->get();
if ($otherLmsClasses->isEmpty()) {
    echo "TIDAK ADA. Course 221 hanya punya 1 LmsClass (ID 417).\n";
} else {
    foreach ($otherLmsClasses as $olc) {
        echo "- LmsClass {$olc->id}: Classroom {$olc->classroom_id} '{$olc->classroom?->class_name}'\n";
    }
}

// Cek enrollment method / source jika ada kolom tersebut
echo "\n=== STRUKTUR TABEL LMS_ENROLLMENTS ===\n";
$cols = Illuminate\Support\Facades\Schema::getColumnListing('lms_enrollments');
echo "Kolom: " . implode(', ', $cols) . "\n";

// Cek sample enrollment hantu
echo "\n=== SAMPLE 5 ENROLLMENT HANTU ===\n";
foreach ($ghost->take(5) as $e) {
    $st = $e->student;
    $cls = $st->classrooms->pluck('class_name')->join(', ');
    echo "- Enrollment ID {$e->id}: {$st->full_name}\n";
    echo "  Kelas: {$cls}\n";
    echo "  Created: {$e->created_at}\n";
    echo "  Kolom: " . json_encode($e->getAttributes()) . "\n\n";
}
