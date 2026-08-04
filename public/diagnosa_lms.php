<?php
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { die('Unauthorized'); }

// Error reporting untuk debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Masuk ke root Laravel (satu level di atas public/)
$basePath = realpath(__DIR__ . '/../');
if (!$basePath) $basePath = __DIR__;

echo "<pre style='background:#1e1e2e;color:#cdd6f4;padding:20px;font-family:monospace;'>";
echo "=== DIAGNOSA LMS PRODUCTION ===\n\n";
echo "Base Path: {$basePath}\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

// Cek apakah bootstrap/app.php ada
if (!file_exists($basePath . '/bootstrap/app.php')) {
    echo "ERROR: bootstrap/app.php tidak ditemukan di {$basePath}\n";
    echo "Isi direktori:\n";
    $files = scandir($basePath);
    foreach ($files as $f) echo "  - $f\n";
    die();
}

// Bootstrap Laravel
require_once $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// 1. Cek course
echo "--- 1. Course ID 101 ---\n";
try {
    $course = DB::table('lms_courses')->where('id', 101)->first();
    if ($course) {
        echo "DITEMUKAN: {$course->title} | is_active: " . ($course->is_active ?? 'N/A') . "\n";
    } else {
        echo "TIDAK DITEMUKAN! Course ID 101 tidak ada di tabel lms_courses\n";
        $allCourses = DB::table('lms_courses')->select('id', 'title')->get();
        echo "Daftar semua course:\n";
        foreach ($allCourses as $c) echo "  ID:{$c->id} | {$c->title}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 2. Cek modules
echo "\n--- 2. Modules di Course 101 ---\n";
try {
    $total = DB::table('lms_modules')->where('course_id', 101)->count();
    $softDeleted = DB::table('lms_modules')->where('course_id', 101)->whereNotNull('deleted_at')->count();
    $active = DB::table('lms_modules')->where('course_id', 101)->whereNull('deleted_at')->count();
    echo "Total (termasuk terhapus): {$total}\n";
    echo "Soft-deleted: {$softDeleted}\n";
    echo "Active (tidak terhapus): {$active}\n";

    if ($total > 0) {
        $modules = DB::table('lms_modules')->where('course_id', 101)->orderBy('sequence')->get();
        echo "\nDaftar Modul:\n";
        foreach ($modules as $m) {
            $deleted = $m->deleted_at ? "[DELETED:{$m->deleted_at}]" : "[OK]";
            echo "  {$deleted} ID:{$m->id} | Seq:{$m->sequence} | is_active:{$m->is_active} | " . substr($m->title, 0, 40) . "\n";
        }
    } else {
        echo "TIDAK ADA MODUL SAMA SEKALI! Seeder belum berjalan.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 3. Cek materials
echo "\n--- 3. Materials ---\n";
try {
    $matCount = DB::table('lms_materials')->where('course_id', 101)->count();
    $matDeleted = DB::table('lms_materials')->where('course_id', 101)->whereNotNull('deleted_at')->count();
    echo "Total material: {$matCount} | Soft-deleted: {$matDeleted}\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 4. Cek struktur tabel lms_modules
echo "\n--- 4. Kolom Tabel lms_modules ---\n";
try {
    $cols = DB::select('SHOW COLUMNS FROM lms_modules');
    foreach ($cols as $col) {
        echo "  {$col->Field} | {$col->Type} | Null:{$col->Null} | Default:" . ($col->Default ?? 'NULL') . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 5. Cek view LMS course di blade - bagaimana module dipanggil
echo "\n--- 5. Cek Apakah LmsModule memakai SoftDeletes ---\n";
$modelFile = $basePath . '/app/Models/LmsModule.php';
if (file_exists($modelFile)) {
    $content = file_get_contents($modelFile);
    if (strpos($content, 'SoftDeletes') !== false) {
        echo "LmsModule MEMAKAI SoftDeletes\n";
    } else {
        echo "LmsModule TIDAK memakai SoftDeletes\n";
    }
    
    if (strpos($content, 'forceDelete') !== false) {
        echo "Model mendukung forceDelete\n";
    }
    
    // Cek fillable
    preg_match('/fillable\s*=\s*\[(.*?)\]/s', $content, $matches);
    if ($matches) {
        echo "\nFillable fields:\n" . trim($matches[1]) . "\n";
    }
} else {
    echo "File LmsModule.php tidak ditemukan!\n";
}

echo "\n=== SELESAI ===\n";
echo "</pre>";
