<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$classroomId = 370;
$teacherId = \App\Models\Teacher::where('user_id', 1)->value('id'); // Assuming user 1 or something. Actually let's just query raw.

$blockA = \App\Models\BlockStudentGroup::where('classroom_id', $classroomId)->where('group', 'A')->count();
$blockB = \App\Models\BlockStudentGroup::where('classroom_id', $classroomId)->where('group', 'B')->count();
$totalStudents = \App\Models\StudentClass::where('classroom_id', $classroomId)->where('status', 'aktif')->count();

echo "Diagnostic Classroom 370 (XI Teknik Rekayasa (DPIB, TAV)):<br>";
echo "Total Active Students in Class: $totalStudents<br>";
echo "Students in BlockStudentGroup A: $blockA<br>";
echo "Students in BlockStudentGroup B: $blockB<br>";
