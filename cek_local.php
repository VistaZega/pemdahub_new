<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK LOCAL DB: lms_modules (course_id=101) ===\n\n";
// Menggunakan raw DB query tanpa SoftDeletes scope
$all = DB::table('lms_modules')->where('course_id', 101)->get(['id','title','is_active','deleted_at','sequence']);
echo "Total (tanpa SoftDeletes filter): " . count($all) . "\n\n";
foreach($all as $m) {
    echo "ID:{$m->id} | Seq:{$m->sequence} | Active:{$m->is_active} | Deleted:" . ($m->deleted_at ?? 'NULL') . " | " . substr($m->title,0,40) . "\n";
}

echo "\n\n=== CEK lms_materials ===\n";
$mats = DB::table('lms_materials')->where('course_id', 101)->get(['id','module_id','material_type','is_published','deleted_at']);
echo "Total: " . count($mats) . "\n";
foreach($mats as $m) {
    echo "ID:{$m->id} | mod:{$m->module_id} | type:{$m->material_type} | pub:{$m->is_published} | del:" . ($m->deleted_at ?? 'NULL') . "\n";
}

echo "\n\n=== CEK lms_assignments ===\n";
$asgn = DB::table('lms_assignments')->where('course_id', 101)->count();
echo "Total: {$asgn}\n";

echo "\n\n=== CEK lms_quizzes ===\n";
$quiz = DB::table('lms_quizzes')->where('course_id', 101)->count();
echo "Total: {$quiz}\n";
