<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$student = \App\Models\Student::where("guardian_name", "Asalnius Zega")->first();
if ($student) {
    echo "Student ID: " . $student->id . "\n";
    echo "Student Photo: " . $student->photo . "\n";
    echo "User Photo: " . $student->user->photo . "\n";
} else {
    echo "Student not found.\n";
}

