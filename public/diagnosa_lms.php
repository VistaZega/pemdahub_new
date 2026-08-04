<?php
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { die('Unauthorized'); }

// Masuk ke root Laravel
$basePath = realpath(__DIR__ . '/../');
if (!$basePath) $basePath = __DIR__;
chdir($basePath);

// Bootstrap Laravel
require_once $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<pre style='font-family:monospace;background:#1e1e2e;color:#cdd6f4;padding:20px;font-size:13px;'>";
echo "=== DIAGNOSA LMS COURSE #101 ===\n\n";

// 1. Cek apakah course ada
$course = DB::table('lms_courses')->where('id', 101)->first();
echo "--- 1. Course ---\n";
if ($course) {
    echo "ID: {$course->id} | Nama: {$course->title} | Active: {$course->is_active}\n";
} else {
    echo "COURSE ID 101 TIDAK DITEMUKAN!\n";
}

// 2. Cek jumlah modules
echo "\n--- 2. Modules (lms_modules) ---\n";
$modules = DB::table('lms_modules')->where('course_id', 101)->get();
echo "Jumlah Module: " . count($modules) . "\n";
foreach ($modules as $m) {
    echo "  ID:{$m->id} | Seq:{$m->sequence} | Title: " . substr($m->title, 0, 50) . " | Active:{$m->is_active} | Deleted:" . ($m->deleted_at ?? 'null') . "\n";
}

// 3. Cek materials
echo "\n--- 3. Materials (lms_materials) ---\n";
$materials = DB::table('lms_materials')->where('course_id', 101)->get();
echo "Jumlah Material: " . count($materials) . "\n";
foreach ($materials as $mat) {
    echo "  ID:{$mat->id} | module_id:{$mat->module_id} | Type:{$mat->material_type} | Published:{$mat->is_published} | Deleted:" . ($mat->deleted_at ?? 'null') . "\n";
}

// 4. Cek assignments
echo "\n--- 4. Assignments ---\n";
$asgn = DB::table('lms_assignments')->where('course_id', 101)->count();
echo "Jumlah Assignment: {$asgn}\n";

// 5. Cek quizzes
echo "\n--- 5. Quizzes ---\n";
$quiz = DB::table('lms_quizzes')->where('course_id', 101)->count();
echo "Jumlah Quiz: {$quiz}\n";

// 6. Cek kolom tabel lms_modules
echo "\n--- 6. Struktur kolom lms_modules ---\n";
$cols = DB::select('SHOW COLUMNS FROM lms_modules');
foreach ($cols as $col) {
    echo "  {$col->Field} ({$col->Type}) - Null:{$col->Null} - Default:{$col->Default}\n";
}

// 7. Cek kolom lms_materials
echo "\n--- 7. Struktur kolom lms_materials ---\n";
$cols2 = DB::select('SHOW COLUMNS FROM lms_materials');
foreach ($cols2 as $col) {
    echo "  {$col->Field} ({$col->Type}) - Null:{$col->Null} - Default:{$col->Default}\n";
}

echo "\n=== SELESAI ===\n";
echo "</pre>";
