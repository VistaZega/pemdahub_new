<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

$users = \App\Models\User::where('name', 'like', '%Yulianus%')->where('name', 'like', '%Zega%')->get();
$userData = [];
foreach($users as $u) {
    $userData[] = [
        'id' => $u->id,
        'name' => $u->name,
        'username' => $u->username,
        'role' => $u->role,
        'school' => $u->school ? $u->school->name : 'Unknown'
    ];
}

try {
    $teachers = \App\Models\Teacher::where('full_name', 'like', '%Yulianus%')->where('full_name', 'like', '%Zega%')->get();
    $teacherData = [];
    foreach($teachers as $t) {
        $assignments = \App\Models\TeachingAssignment::where('teacher_id', $t->id)->get();
        $assignmentData = [];
        foreach($assignments as $a) {
            $assignmentData[] = [
                'subject' => $a->subject ? $a->subject->name : "ID:{$a->subject_id}",
                'classroom' => $a->classroom ? $a->classroom->name : "ID:{$a->classroom_id}",
                'school' => $a->school ? $a->school->name : "ID:{$a->school_id}"
            ];
        }
        
        $teacherData[] = [
            'id' => $t->id,
            'name' => $t->full_name,
            'user' => $t->user ? $t->user->username : 'NULL',
            'main_school' => $t->school ? $t->school->name : 'Unknown',
            'additional_schools' => $t->additionalSchools()->pluck('name')->toArray(),
            'assignments' => $assignmentData
        ];
    }

    echo json_encode([
        'users' => $userData,
        'teachers' => $teacherData
    ], JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    echo json_encode([
        'users' => $userData,
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ], JSON_PRETTY_PRINT);
}

