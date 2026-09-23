<?php
/**
 * Diagnostic tool for Teacher Putra Zebua Absensi
 * Akses: https://perguruanpembda.com/diag_putra_absensi.php?secret=pembda99
 */
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    die("Akses ditolak");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "<pre>\n";
echo "=== DIAGNOSTIK ABSENSI GURU PUTRA ZEBUA ===\n\n";

$teachers = Teacher::with('user')->where('full_name', 'like', '%Putra%')->get();
foreach ($teachers as $t) {
    echo "Teacher ID: {$t->id} | Name: {$t->full_name} | User ID: " . ($t->user_id ?? 'NULL') . "\n";
    if ($t->user) {
        echo "  User Email: {$t->user->email} | Username: {$t->user->username} | Role: {$t->user->role}\n";
    }
}

$errorUserIds = [2140, 2146, 2141, 2150, 2130, 2139, 2195, 2144, 2203, 2148, 3942, 3941, 265, 201];
echo "\n--- USERS ENCOUNTERING ERROR ---:\n";
$users = User::whereIn('id', $errorUserIds)->get(['id', 'name', 'email', 'role']);
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role}\n";
}

// Cari user Martperan Putra Zebua
$teacher = Teacher::where('full_name', 'like', '%Martperan%')->first();
if (!$teacher) {
    $teacher = Teacher::where('full_name', 'like', '%Putra%')->first();
}

if ($teacher && $teacher->user) {
    $user = $teacher->user;
    Auth::login($user);
    echo "\nLogged in as User ID: {$user->id} ({$user->name})\n";

    // Test MobileTeacherController::absensiInput
    echo "\n--- TESTING MobileTeacherController::absensiInput ---\n";
    $controller = app(\App\Http\Controllers\Mobile\MobileTeacherController::class);

    // 1. Without classroom_id
    try {
        $req = Request::create('/m/guru/absensi-input', 'GET');
        $view = $controller->absensiInput($req);
        echo "✅ absensiInput (default) SUCCESS! View returned: " . $view->name() . "\n";
        $data = $view->getData();
        echo "   Classrooms count: " . $data['classrooms']->count() . "\n";
        foreach ($data['classrooms'] as $c) {
            echo "   - Class ID: {$c->id} | {$c->class_name}\n";
        }
    } catch (\Throwable $e) {
        echo "❌ absensiInput (default) ERROR: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "   Trace: " . $e->getTraceAsString() . "\n";
    }

    // 2. Test each classroom
    if (isset($data['classrooms'])) {
        foreach ($data['classrooms'] as $cls) {
            echo "\n-> Testing Class ID {$cls->id} ({$cls->class_name}) on today (" . date('Y-m-d') . ")...\n";
            try {
                $req = Request::create('/m/guru/absensi-input', 'GET', [
                    'classroom_id' => $cls->id,
                    'date' => date('Y-m-d')
                ]);
                $v = $controller->absensiInput($req);
                $renderedHtml = $v->render();
                echo "   ✅ SUCCESS! Rendered " . strlen($renderedHtml) . " bytes\n";
            } catch (\Throwable $e) {
                echo "   ❌ ERROR on Class {$cls->id}: " . $e->getMessage() . "\n";
                echo "      File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                echo "      Trace:\n" . substr($e->getTraceAsString(), 0, 1000) . "\n";
            }
        }
    }

    // 3. Test storeAbsensi for Class 367
    echo "\n--- TESTING MobileTeacherController::storeAbsensi ---\n";
    try {
        $testClass = Classroom::find(367);
        if ($testClass) {
            $student = $testClass->students()->first();
            if ($student) {
                echo "Testing storeAbsensi for Student ID {$student->id} ({$student->full_name})...\n";
                $req = Request::create('/m/guru/absensi-store', 'POST', [
                    'classroom_id' => 367,
                    'date' => date('Y-m-d'),
                    'attendances' => [
                        $student->id => 'hadir'
                    ]
                ]);
                $res = $controller->storeAbsensi($req);
                echo "✅ storeAbsensi SUCCESS! Status code: " . $res->getStatusCode() . "\n";
            }
        }
    } catch (\Throwable $e) {
        echo "❌ storeAbsensi ERROR: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "   Trace: " . substr($e->getTraceAsString(), 0, 1000) . "\n";
    }
}

echo "\n--- LOG ENTRIES ON 2026-09-23 (LAST 200KB) ---\n";
$todayLog = __DIR__ . '/../storage/logs/laravel-2026-09-23.log';
if (file_exists($todayLog)) {
    $fp = fopen($todayLog, 'r');
    $size = filesize($todayLog);
    $readBytes = min($size, 2 * 1024 * 1024);
    fseek($fp, $size - $readBytes);
    $chunk = fread($fp, $readBytes);
    fclose($fp);
    
    $lines = explode("\n", $chunk);
    $matched = [];
    foreach ($lines as $line) {
        if (preg_match('/\[2026-09-23 (06:|07:0|07:1|00:0|00:1)/', $line)) {
            $matched[] = substr($line, 0, 220);
        }
    }
    echo "Found " . count($matched) . " matching log lines:\n";
    foreach ($matched as $m) {
        echo $m . "\n";
    }
} else {
    echo "Log file not found.\n";
}

