<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$u = \App\Models\User::find(273); // Yulianus Zega
echo "Before: " . $u->photo . "\n";
$u->photo = "test_photo.jpg";
$u->save();
$u = \App\Models\User::find(273);
echo "After: " . $u->photo . "\n";
// Revert
$u->photo = "photos/OhpOicz7DVJQWSh0yLUJ40UGZgqFRD5Jr9KKJus4.jpg";
$u->save();

