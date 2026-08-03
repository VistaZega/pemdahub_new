<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "APP_URL: " . config("app.url") . "\n";
echo "Asset: " . asset("storage/photos/test.jpeg") . "\n";

