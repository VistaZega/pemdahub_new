<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$teacher = \App\Models\Teacher::where("full_name", "like", "%Yulianus Zega%")->first();
if ($teacher) {
    echo "Teacher ID: " . $teacher->id . "\n";
    echo "Teacher Photo: " . $teacher->photo . "\n";
    echo "User Photo: " . $teacher->user->photo . "\n";
} else {
    echo "Teacher not found.\n";
}

