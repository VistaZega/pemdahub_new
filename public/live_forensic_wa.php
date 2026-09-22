<?php
/**
 * Live Forensic Diagnostics for WhatsApp Engine
 * Akses: https://perguruanpembda.com/live_forensic_wa.php?secret=pembda99
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden - Secret key required (?secret=pembda99)');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== 1. CHECK RUNNING NODE PROCESSES ===\n";
echo @shell_exec("ps -ef | grep node | grep -v grep 2>&1") ?: "No node processes found\n";

echo "\n=== 2. CHECK RUNNING PHP PROCESSES (Queue / Artisan) ===\n";
echo @shell_exec("ps -ef | grep php | grep -v grep 2>&1") ?: "No php processes found\n";

echo "\n=== 3. CHECK PORTS 3002 AND 3000 RESPONSE ===\n";
foreach ([3002, 3000] as $port) {
    $ch = @curl_init("http://127.0.0.1:{$port}/device");
    if ($ch) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        echo "Port {$port}: HTTP {$httpCode} | Res: " . substr((string)$res, 0, 150) . " | Err: {$err}\n";
    }
}

echo "\n=== 4. CHECK DATABASE QUEUE (jobs table) ===\n";
try {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        $jobCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
        echo "Pending Jobs in DB: {$jobCount}\n";
        
        $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        echo "Failed Jobs in DB: {$failedCount}\n";
        
        // Also check WA settings
        echo "wa_enabled: " . (\App\Models\Setting::getValue('wa_enabled') ? 'true' : 'false') . "\n";
        echo "wa_digest_enabled: " . (\App\Models\Setting::getValue('wa_digest_enabled') ? 'true' : 'false') . "\n";
    } else {
        echo "Vendor autoload not found\n";
    }
} catch (\Throwable $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}

echo "\n=== 5. LAST 30 LINES OF WHATSAPP LOG ===\n";
$today = date('Y-m-d');
$logFiles = [
    __DIR__ . "/../storage/logs/whatsapp-{$today}.log",
    __DIR__ . "/../storage/logs/whatsapp.log",
    __DIR__ . "/../storage/logs/laravel-{$today}.log",
    __DIR__ . "/../storage/logs/laravel.log"
];

foreach ($logFiles as $lf) {
    if (file_exists($lf)) {
        echo "--- File: " . basename($lf) . " (" . date("Y-m-d H:i:s", filemtime($lf)) . ") ---\n";
        $lines = file($lf);
        $lastLines = array_slice($lines, -25);
        echo implode('', $lastLines) . "\n";
        break;
    }
}

echo "\n=== 6. FORCE KILL ANY USER-OWNED NODE PROCESSES ===\n";
$pids = @shell_exec("pgrep -u $(whoami) -f 'server.js' 2>&1");
if ($pids) {
    $pidArr = explode("\n", trim($pids));
    foreach ($pidArr as $p) {
        $p = trim($p);
        if ($p) {
            $killRes = @shell_exec("kill -9 {$p} 2>&1");
            echo "Killing PID {$p}: " . ($killRes ?: "KILLED") . "\n";
        }
    }
} else {
    echo "No user-owned node server.js PIDs found\n";
}
