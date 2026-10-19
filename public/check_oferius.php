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

echo "\n=== RECENT ERROR LOGS (LAST 3 EXCEPTIONS) ===\n";
$logPath = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    preg_match_all('/\[\d{4}-\d{2}-\d{2}.*?\].*?\.(?:ERROR|CRITICAL|ALERT):.*?(?=\n\[\d{4}-\d{2}-\d{2}|$)/s', $content, $matches);
    if (!empty($matches[0])) {
        $lastErrors = array_slice($matches[0], -5);
        foreach ($lastErrors as $err) {
            echo "----------------------------------------\n";
            echo mb_substr($err, 0, 1500) . "\n";
        }
    } else {
        echo "No ERROR/CRITICAL found in today's log.\n";
    }
} else {
    echo "Log file not found.\n";
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
}


