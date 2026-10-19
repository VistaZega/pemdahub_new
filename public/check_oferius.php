<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

if (request('secret') !== 'pembda99') die('Unauthorized');

header('Content-Type: text/plain; charset=utf-8');

echo "=== PHP CONFIGURATION ===\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "SERVER SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";

echo "\n=== SYSTEM ERROR LOGS CHECK ===\n";
$systemLogs = [
    '/var/log/nginx/error.log',
    '/var/log/nginx/perguruanpembda.com.error.log',
    '/var/log/apache2/error.log',
    '/var/log/php8.3-fpm.log',
    '/var/log/php8.2-fpm.log',
    '/var/log/php-fpm.log',
];
foreach ($systemLogs as $slog) {
    if (file_exists($slog)) {
        if (is_readable($slog)) {
            echo "Log $slog is readable! Last 15 lines:\n";
            $slines = file($slog);
            echo implode("", array_slice($slines, -15));
        } else {
            echo "Log $slog exists but is NOT readable (permissions).\n";
        }
    } else {
        echo "Log $slog does not exist.\n";
    }
}


echo "\n=== TEACHER OFERIUS ZEGA ===\n";
$teachers = \App\Models\Teacher::where('full_name', 'like', '%Oferius%')->get();
foreach ($teachers as $t) {
    echo "Teacher ID: {$t->id} | Name: {$t->full_name} | User ID: {$t->user_id} | School: {$t->school_id} | Active: {$t->is_active}\n";
    $user = $t->user;
    if ($user) {
        echo "  User: {$user->name} | Username: {$user->username} | Email: {$user->email} | Role: {$user->role}\n";
    }
}

echo "\n=== COURSE 842 ===\n";
$c842 = \App\Models\LmsCourse::find(842);
if ($c842) {
    echo "Found Course 842:\n";
    echo "  Name: {$c842->course_name}\n";
    echo "  Teacher ID: {$c842->teacher_id} (" . ($c842->teacher->full_name ?? 'N/A') . ")\n";
    echo "  School ID: {$c842->school_id}\n";
    echo "  Modules count: " . $c842->modules()->count() . "\n";
    foreach ($c842->modules as $mod) {
        echo "    Module {$mod->id}: {$mod->title} (seq: {$mod->sequence})\n";
    }
    echo "  Materials count: " . $c842->materials()->count() . "\n";
    foreach ($c842->materials as $mat) {
        echo "    Material {$mat->id}: {$mat->title} (type: {$mat->material_type})\n";
    }
    echo "  Classes count: " . $c842->lmsClasses()->count() . "\n";
    foreach ($c842->lmsClasses as $lc) {
        $cName = $lc->classroom->class_name ?? 'N/A';
        $enrollCount = \App\Models\LmsEnrollment::where('lms_class_id', $lc->id)->count();
        echo "    LmsClass {$lc->id}: Classroom {$lc->classroom_id} ({$cName}) | Enrollments: {$enrollCount}\n";
    }
} else {
    echo "Course 842 NOT FOUND.\n";
    echo "Listing recent courses:\n";
    $recent = \App\Models\LmsCourse::latest()->take(5)->get();
    foreach ($recent as $rc) {
        echo "  Course {$rc->id}: {$rc->course_name} (Teacher: " . ($rc->teacher->full_name ?? $rc->teacher_id) . ")\n";
    }
}

echo "\n=== LOGS AROUND 08:40-08:50 WIB (01:40-01:50 UTC) ===\n";
$logPath = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
if (file_exists($logPath)) {
    $lines = file($logPath);
    $found = false;
    foreach ($lines as $line) {
        if (preg_match('/\[2026-10-19 (01:4[0-9]|08:4[0-9])/', $line)) {
            echo $line;
            $found = true;
        }
    }
    if (!$found) echo "No log lines matching 01:40-01:49 UTC or 08:40-08:49 WIB\n";
}


echo "\n=== MATERIAL 4908 DETAILS ===\n";
$m4908 = \App\Models\LmsMaterial::find(4908);
if ($m4908) {
    echo "ID: {$m4908->id}\n";
    echo "Title: {$m4908->title}\n";
    echo "Type: {$m4908->material_type}\n";
    echo "File Path: {$m4908->file_path}\n";
    echo "File URL: {$m4908->file_url}\n";
    echo "File Size: {$m4908->file_size}\n";
    echo "Created: {$m4908->created_at}\n";
    $rep = \App\Models\ReputationLog::where('reference_type', 'App\Models\LmsMaterial')->where('reference_id', 4908)->first();
    if ($rep) {
        echo "Reputation Log User ID: {$rep->user_id}\n";
        $u = \App\Models\User::find($rep->user_id);
        echo "User: " . ($u->name ?? 'N/A') . " (role: " . ($u->role ?? 'N/A') . ")\n";
    } else {
echo "\n=== SIMULATE STORE MATERIAL ===\n";
try {
    $course = \App\Models\LmsCourse::find(842);
    $longTitle = 'Materi ini membahas peran orang tua sebagai pendidik utama dalam membimbing anak, menanamkan nilai-nilai kristiani, serta pentingnya menghormati dan menghargai orang tua sesuai firman Tuhan.';
    echo "Title length: " . strlen($longTitle) . " chars\n";
    
    $maxOrder = $course->materials()->max('order_number') ?? 0;
    $simMat = $course->materials()->create([
        'module_id' => 2401,
        'title' => $longTitle,
        'material_type' => 'pdf',
        'content' => 'Orang tua adalah pendidik pertama dan utama dalam kehidupan anak. Melalui kasih, nasihat, teladan, dan bimbingan, orang tua mengajarkan nilai-nilai kehidupan serta iman kepada Tuhan. Sebagai anak, kita dipanggil untuk menghormati, mengasihi, dan menghargai pengorbanan orang tua, serta menerapkan ajaran mereka dalam kehidupan sehari-hari sesuai firman Tuhan.',
        'file_url' => 'https://canva.link/gn65smmtltqwhht',
        'file_path' => 'lms/materials/test_sim.pdf',
        'file_size' => 374836,
        'order_number' => $maxOrder + 1,
        'is_published' => true,
    ]);
    echo "Simulated material created successfully with ID: {$simMat->id}!\n";

    // Test Reputation
    \App\Models\ReputationLog::log(279, 30, 'lms_content', "Membuat materi LMS baru: " . $simMat->title, $simMat);
    echo "ReputationLog logged successfully!\n";

    // Test Notification
    $notificationService = app(\App\Services\NotificationService::class);
    $notifRes = $notificationService->sendLmsNotification($course, 'lms.material.published', ['title' => $simMat->title]);
    echo "NotificationService returned: " . json_encode($notifRes) . "\n";

    // Cleanup simulation
    $simMat->forceDelete();
    echo "Cleaned up simulation material.\n";
} catch (\Throwable $e) {
    echo "SIMULATION CAUGHT THROWABLE: " . get_class($e) . " - " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}




