<?php
/**
 * Diagnostic tool for LMS Assignment Students
 * Access: https://perguruanpembda.com/check_assignment_students.php?secret=pembda99&assignment_id=412
 */

define('LARAVEL_START', microtime(true));

// Secret token protection
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized. Provide ?secret=pembda99']));
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

header('Content-Type: application/json; charset=utf-8');

$assignmentId = $_GET['assignment_id'] ?? 412;
$assignment = \App\Models\LmsAssignment::find($assignmentId);

if (!$assignment) {
    echo json_encode(['error' => "Assignment {$assignmentId} not found."]);
    exit;
}

$course = $assignment->course;
$teacher = $course?->teacher;
$activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

$lmsClasses = \App\Models\LmsClass::where('course_id', $course->id)->get();
$classroomIds = $lmsClasses->pluck('classroom_id')->filter()->toArray();
if ($course->classroom_id) {
    $classroomIds[] = $course->classroom_id;
}
$classroomIds = array_unique($classroomIds);

$classrooms = \App\Models\Classroom::whereIn('id', $classroomIds)->get();

// Check student_classes
$studentClassesAll = \App\Models\StudentClass::whereIn('classroom_id', $classroomIds)->get();
$studentClassesActiveYear = $activeYear ? \App\Models\StudentClass::whereIn('classroom_id', $classroomIds)->where('academic_year_id', $activeYear->id)->get() : collect();

// Check students in school
$schoolId = $course->school_id ?? $teacher?->school_id;
$studentsInSchool = $schoolId ? \App\Models\Student::where('school_id', $schoolId)->get() : collect();

// Check enrollments
$enrollments = \App\Models\LmsEnrollment::whereHas('lmsClass', fn($q) => $q->where('course_id', $course->id))->get();

echo json_encode([
    'assignment' => [
        'id' => $assignment->id,
        'title' => $assignment->title,
        'is_group_assignment' => $assignment->is_group_assignment,
        'course_id' => $assignment->course_id,
    ],
    'course' => [
        'id' => $course->id,
        'name' => $course->course_name ?? $course->name,
        'school_id' => $course->school_id,
        'subject_id' => $course->subject_id,
        'classroom_id' => $course->classroom_id,
    ],
    'teacher' => [
        'id' => $teacher?->id,
        'name' => $teacher?->full_name,
        'school_id' => $teacher?->school_id,
    ],
    'active_academic_year' => $activeYear ? ['id' => $activeYear->id, 'year' => $activeYear->year] : null,
    'lms_classes' => $lmsClasses,
    'classroom_ids' => $classroomIds,
    'classrooms_found' => $classrooms->map(fn($c) => ['id' => $c->id, 'name' => $c->class_name, 'school_id' => $c->school_id, 'academic_year_id' => $c->academic_year_id]),
    'student_classes_count_all' => $studentClassesAll->count(),
    'student_classes_count_active_year' => $studentClassesActiveYear->count(),
    'student_classes_sample' => $studentClassesAll->take(5)->map(fn($sc) => ['id' => $sc->id, 'student_id' => $sc->student_id, 'classroom_id' => $sc->classroom_id, 'academic_year_id' => $sc->academic_year_id, 'status' => $sc->status]),
    'lms_enrollments_count' => $enrollments->count(),
    'students_in_school_count' => $studentsInSchool->count(),
    'students_in_school_sample' => $studentsInSchool->take(5)->map(fn($s) => ['id' => $s->id, 'name' => $s->full_name, 'nisn' => $s->nisn, 'school_id' => $s->school_id, 'status' => $s->status]),
], JSON_PRETTY_PRINT);
