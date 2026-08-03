<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create("/admin/api/schedule/by-classroom", "POST", ["classroom_id" => 1]);
$response = $kernel->handle($request);
echo $response->getContent();
