<?php
/**
 * WhatsApp Engine Process Killer Tool (Hostinger & Local)
 * Akses: https://perguruanpembda.com/kill_wa_engine.php?secret=pembda99
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden - Secret key required (?secret=pembda99)');
}

// Bootstrap Laravel if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    \App\Models\Setting::setValue('wa_digest_enabled', false, 'boolean', 'whatsapp');
    \App\Models\Setting::setValue('wa_enabled', false, 'boolean', 'whatsapp');
    \App\Models\Setting::setValue('wa_send_principal_attendance', false, 'boolean', 'whatsapp');
    \App\Models\Setting::setValue('wa_send_homeroom_attendance', false, 'boolean', 'whatsapp');
    \Illuminate\Support\Facades\DB::table('jobs')->delete();
}

$output = [];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $res = @shell_exec("taskkill /F /IM node.exe 2>&1");
    $output[] = "Windows taskkill: " . trim($res ?: 'Process stopped');
} else {
    // Linux / Hostinger: Kill node processes running whatsapp-server/server.js
    $res1 = @shell_exec("pkill -9 -f 'whatsapp-server/server.js' 2>&1");
    $res2 = @shell_exec("pkill -9 -f 'server.js' 2>&1");
    $res3 = @shell_exec("fuser -k -9 3002/tcp 2>&1");
    $res4 = @shell_exec("fuser -k -9 3000/tcp 2>&1");
    
    $output[] = "Linux pkill (server.js): " . trim($res1 ?: 'OK');
    $output[] = "Linux pkill (generic): " . trim($res2 ?: 'OK');
    $output[] = "Linux fuser 3002: " . trim($res3 ?: 'Port 3002 freed');
    $output[] = "Linux fuser 3000: " . trim($res4 ?: 'Port 3000 freed');
}

echo "<!DOCTYPE html><html><head><title>WhatsApp Engine Killed</title>";
echo "<style>body{font-family:sans-serif;padding:30px;background:#0f172a;color:#f8fafc;} .card{background:#1e293b;padding:25px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.5);max-width:600px;margin:auto;border:1px solid #334155;} .badge{display:inline-block;padding:6px 12px;background:#ef4444;color:#fff;border-radius:6px;font-weight:bold;}</style>";
echo "</head><body><div class='card'>";
echo "<h2><span class='badge'>KILLED</span> Node.js WhatsApp Engine Berhasil Dimatikan!</h2>";
echo "<p>Proses Node.js <code>server.js</code> di port 3002/3000 telah di-kill secara paksa dan seluruh antrean dihapus.</p>";
echo "<h3>Detail Operasi:</h3><pre style='background:#090d16;padding:12px;border-radius:6px;color:#4ade80;'>";
foreach ($output as $out) {
    echo htmlspecialchars($out) . "\n";
}
echo "</pre>";
echo "<p style='color:#ef4444;font-weight:bold;'>Engine WhatsApp mati total. Tidak ada proses Node.js yang berjalan di background.</p>";
echo "</div></body></html>";
