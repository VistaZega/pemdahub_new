<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$u = \App\Models\User::find(254); // Eliasa Laia
echo "Eliasa Laia Photo: " . $u->photo . "\n";
echo "Eliasa Laia Teacher Photo: " . ($u->teacher ? $u->teacher->photo : "NULL") . "\n";

