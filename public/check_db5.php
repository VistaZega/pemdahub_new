<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$u = \App\Models\User::where("photo", "like", "%6a706%")->first();
if ($u) {
    echo "Found user with 6a706: " . $u->id . " - " . $u->name . "\n";
} else {
    echo "No user has 6a706 in photo column.\n";
}

