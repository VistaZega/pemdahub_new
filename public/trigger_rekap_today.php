<?php
/**
 * Tool Peluncur Rekapitulasi Kehadiran Eksekutif Harian Hari Ini (Live Stream)
 * Akses: https://perguruanpembda.com/trigger_rekap_today.php?secret=pembda99
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Increase execution time limit for streaming broadcast
set_time_limit(600);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden - Secret key required (?secret=pembda99)');
}

// Disable buffering for live HTML output
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 'Off');
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) {
    ob_end_flush();
}
ob_implicit_flush(true);

echo "<!DOCTYPE html><html><head><title>Peluncur Rekapitulasi Eksekutif Hari Ini</title>";
echo "<style>
body { font-family: monospace, sans-serif; background: #0f172a; color: #f8fafc; padding: 25px; margin: 0; }
.card { background: #1e293b; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); max-width: 900px; margin: auto; border: 1px solid #334155; }
h2 { color: #38bdf8; margin-top: 0; border-bottom: 1px solid #334155; padding-bottom: 12px; }
pre { background: #090d16; padding: 15px; border-radius: 8px; color: #e2e8f0; font-size: 13px; line-height: 1.6; max-height: 500px; overflow-y: auto; border: 1px solid #1e293b; }
.ok { color: #4ade80; font-weight: bold; }
.warn { color: #fbbf24; }
.err { color: #f87171; font-weight: bold; }
.info { color: #38bdf8; }
.badge { display: inline-block; padding: 4px 10px; background: #0284c7; color: #fff; border-radius: 4px; font-weight: bold; font-size: 12px; }
</style></head><body><div class='card'>";
echo "<h2>🚀 PELUNCUR REKAPITULASI KEHADIRAN EKSEKUTIF HARI INI</h2>";
echo "<p><span class='badge'>LIVE BROADCAST</span> Memproses pengiriman rekapitulasi harian ke Kepala Sekolah & Wali Kelas secara aman (Anti-Ban Pacing)...</p>";
echo "<pre>";

function logMsg($msg) {
    $time = date('H:i:s');
    $clean = htmlspecialchars($msg);
    if (strpos($msg, '✅') !== false) {
        echo "<span class='ok'>[{$time}] {$clean}</span>\n";
    } elseif (strpos($msg, '❌') !== false || strpos($msg, '🛑') !== false) {
        echo "<span class='err'>[{$time}] {$clean}</span>\n";
    } elseif (strpos($msg, '⚠️') !== false || strpos($msg, '⏳') !== false) {
        echo "<span class='warn'>[{$time}] {$clean}</span>\n";
    } else {
        echo "<span class='info'>[{$time}] {$clean}</span>\n";
    }
    flush();
}

// Bootstrap Laravel
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/pembdahub/vendor/autoload.php')) {
    require __DIR__ . '/pembdahub/vendor/autoload.php';
    $app = require_once __DIR__ . '/pembdahub/bootstrap/app.php';
} else {
    die('Autoload file not found.');
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Services\ExecutiveReportService;
use App\Services\WhatsAppService;

// 1. Pre-flight Check WA Gateway
logMsg("🔍 1. Memeriksa ketersediaan WA Engine Gateway...");
$waService = app(WhatsAppService::class);

// Force re-enable WA settings for this session
Setting::setValue('wa_enabled', true, 'boolean', 'whatsapp');
Setting::setValue('wa_digest_enabled', true, 'boolean', 'whatsapp');
Setting::setValue('wa_send_principal_attendance', true, 'boolean', 'whatsapp');
Setting::setValue('wa_send_homeroom_attendance', true, 'boolean', 'whatsapp');

$isConn = $waService->isConnected();
if (!$isConn) {
    logMsg("❌ ERRROR: WA Gateway dalam status TERPUTUS (Disconnected). Pengiriman dibatalkan demi keamanan.");
    echo "</pre></div></body></html>";
    exit;
}

logMsg("✅ WA Gateway TERHUBUNG & SIAP (Status: Connected).");

// 2. Clear Idempotency Lock Today
logMsg("🧹 2. Membersihkan Idempotency Lock hari ini (" . date('Y-m-d') . ") agar rekap terkirim segar...");
$reportService = app(ExecutiveReportService::class);

// 3. Execute Workflow with Anti-Ban Pacing
logMsg("🚀 3. Memulai pengiriman rekapitulasi harian...");
$options = [
    'dry_run' => false,
    'force' => true,
    'delay_min' => 10,
    'delay_max' => 18,
    'batch_pause' => 30,
    'logger' => 'logMsg',
];

try {
    $res = $reportService->sendDailyAttendanceDigestWorkflow($options);
    logMsg("🏁 SELESAI! " . ($res['message'] ?? 'Workflow selesai diproses.'));
} catch (\Throwable $e) {
    logMsg("❌ Error Fatal: " . $e->getMessage());
}

echo "</pre>";
echo "<h3 style='color:#4ade80;'>✅ Pengiriman Rekapitulasi Hari Ini Selesai Diproses!</h3>";
echo "<p>Seluruh laporan rekapitulasi telah dikirimkan secara aman dengan jeda santai alami.</p>";
echo "</div></body></html>";
