<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$projects = \App\Models\FinalProject::select('id', 'type', 'title', 'status', 'school_id')
    ->orderBy('created_at', 'desc')
    ->take(20)
    ->get();

echo "Total: " . \App\Models\FinalProject::count() . "\n";
echo "Types: " . json_encode(\App\Models\FinalProject::select('type', \DB::raw('count(*) as total'))->groupBy('type')->get()) . "\n";
echo "Statuses: " . json_encode(\App\Models\FinalProject::select('status', \DB::raw('count(*) as total'))->groupBy('status')->get()) . "\n";
echo "Schools: " . json_encode(\App\Models\FinalProject::select('school_id', \DB::raw('count(*) as total'))->groupBy('school_id')->get()) . "\n";

foreach($projects as $p) {
    echo "ID: $p->id | Type: $p->type | Status: $p->status | School: $p->school_id | Title: $p->title\n";
}
