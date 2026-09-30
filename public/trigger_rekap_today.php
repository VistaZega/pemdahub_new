<?php
/**
 * Tool Peluncur Rekapitulasi Kehadiran Eksekutif Harian Hari Ini (Live Stream)
 * Akses: https://perguruanpembda.com/trigger_rekap_today.php?secret=pembda99
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(600);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden - Secret key required (?secret=pembda99)');
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
use Illuminate\Support\Facades\DB;

$confirm = $_GET['confirm'] ?? '';

// SHOW CONTROL PANEL IF NOT CONFIRMED
if ($confirm !== '1') {
    $turnedOff = false;
    $deleted = 0;
    if (($_GET['turn_off'] ?? '') === '1') {
        Setting::setValue('wa_enabled', false, 'boolean', 'whatsapp');
        Setting::setValue('wa_digest_enabled', false, 'boolean', 'whatsapp');
        Setting::setValue('wa_send_principal_attendance', false, 'boolean', 'whatsapp');
        Setting::setValue('wa_send_homeroom_attendance', false, 'boolean', 'whatsapp');
        $deleted = DB::table('jobs')->delete();
        $turnedOff = true;
    }

    $isWaEnabled = Setting::getValue('wa_enabled', false);
    $isDigestEnabled = Setting::getValue('wa_digest_enabled', false);

    echo "<!DOCTYPE html><html><head><title>WhatsApp Digest Control Panel</title>";
    echo "<style>
    body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
    .card { background: #1e293b; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); max-width: 700px; margin: auto; border: 1px solid #334155; }
    h2 { color: #38bdf8; margin-top: 0; }
    .badge-ok { background: #10b981; color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; }
    .badge-off { background: #ef4444; color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; }
    .btn { display: inline-block; padding: 12px 20px; background: #3b82f6; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; margin-right: 10px; margin-top: 15px; }
    .btn-green { background: #10b981; }
    .btn-off { background: #ef4444; }
    </style></head><body><div class='card'>";
    
    if ($turnedOff) {
        echo "<h2><span class='badge-off'>OFF</span> Otomatisasi WhatsApp Dinonaktifkan</h2>";
        echo "<p>Fitur pengiriman WhatsApp dan {$deleted} antrean job telah dibersihkan.</p>";
    } else {
        echo "<h2><span class='" . ($isDigestEnabled ? 'badge-ok' : 'badge-off') . "'>" . ($isDigestEnabled ? 'SIAP' : 'OFF') . "</span> Control Panel Rekapitulasi Harian</h2>";
        echo "<p>Status Layanan WA: <strong>" . ($isWaEnabled ? 'AKTIF' : 'NONAKTIF') . "</strong> | Digest Harian: <strong>" . ($isDigestEnabled ? 'AKTIF' : 'NONAKTIF') . "</strong></p>";
        echo "<p>Jeda per pesan: <strong>2 - 3 Menit</strong> | Jeda Transisi Kepsek &rarr; Wali Kelas: <strong>5 Menit</strong></p>";
    }
    
    echo "<p><a href='?secret=pembda99&confirm=1' class='btn btn-green' onclick='return confirm(\"Luncurkan pengiriman rekap harian ke Kepala Sekolah & Wali Kelas sekarang?\")'>🚀 Luncurkan Rekap Sekarang Manual</a> ";
    echo "<a href='check_digest_readiness.php?secret=pembda99' class='btn' style='background:#6366f1;'>🔍 Pre-Flight Check</a> ";
    echo "<a href='?secret=pembda99&turn_off=1' class='btn btn-off' onclick='return confirm(\"Matikan seluruh pengiriman WhatsApp?\")'>🛑 Matikan Darurat</a></p>";
    echo "</div></body></html>";
    exit;
}

// IF CONFIRMED=1 (EXPLICIT USER INTENT TO RUN)
if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', 1); }
@ini_set('zlib.output_compression', 'Off');
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
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

Setting::setValue('wa_enabled', true, 'boolean', 'whatsapp');
Setting::setValue('wa_digest_enabled', true, 'boolean', 'whatsapp');
Setting::setValue('wa_send_principal_attendance', true, 'boolean', 'whatsapp');
Setting::setValue('wa_send_homeroom_attendance', true, 'boolean', 'whatsapp');

logMsg("🔍 1. Memeriksa ketersediaan WA Engine Gateway...");
$waService = app(WhatsAppService::class);
$accountInfo = $waService->getAccountInfo();

$isConn = $waService->isConnected();
$provider = $waService->getActiveProvider();
$statusText = $accountInfo['data']['status'] ?? ($isConn ? 'connected' : 'disconnected');

logMsg("ℹ️ Active Provider: {$provider} | Status WA: {$statusText}");

if (!$isConn) {
    logMsg("❌ ERRROR: WA Gateway dalam status TERPUTUS (Disconnected). Pengiriman dibatalkan.");
    echo "</pre></div></body></html>";
    exit;
}

logMsg("✅ WA Gateway TERHUBUNG & SIAP (Status: Connected).");

logMsg("🚀 2. Memulai pengiriman rekapitulasi harian ke Kepala Sekolah & Wali Kelas...");
$reportService = app(ExecutiveReportService::class);

$options = [
    'dry_run' => false,
    'force' => true,
    'delay_min' => (int)Setting::getValue('wa_digest_delay_min', 120),
    'delay_max' => (int)Setting::getValue('wa_digest_delay_max', 180),
    'batch_pause' => (int)Setting::getValue('wa_digest_batch_pause', 300),
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
echo "</div></body></html>";
