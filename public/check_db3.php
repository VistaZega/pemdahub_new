<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$teachers = \App\Models\Teacher::where("full_name", "like", "%Yulianus Zega%")->get();
foreach ($teachers as $t) {
    echo "Teacher ID: " . $t->id . " - Code: " . $t->teacher_code . "\n";
    echo "  Teacher Photo: " . $t->photo . "\n";
    if ($t->user) {
        echo "  User ID: " . $t->user->id . " - User Photo: " . $t->user->photo . "\n";
    }
}

