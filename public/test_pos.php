<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$t = App\Models\Teacher::first();
echo "Position: " . $t->position . "\n";
echo "Active: " . implode(", ", $t->getActivePositions()->pluck("position_name")->toArray()) . "\n";
?>
