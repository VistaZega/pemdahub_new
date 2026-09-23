<?php
/**
 * Standalone Tool: Peluncur Pengingat Jadwal Mengajar Harian Guru (Live Stream)
 * Akses: https://perguruanpembda.com/trigger_teaching_reminder.php?secret=pembda99
 * Parameter opsional:
 *   &dry_run=1       : Simulasi tanpa kirim pesan riil
 *   &force=1         : Lewati kunci duplikasi hari ini (idempotency lock)
 *   &test_phone=08.. : Kirim sampel uji coba hanya ke nomor ini
 *   &school_id=X     : Filter unit sekolah tertentu (1=SMP, 2=SMA, 3=SMK)
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(600);
ignore_user_abort(true);

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

if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', 1); }
@ini_set('zlib.output_compression', 'Off');
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(true);

$dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';
$force = isset($_GET['force']) && $_GET['force'] == '1';
$testPhone = !empty($_GET['test_phone']) ? trim($_GET['test_phone']) : null;
$schoolId = !empty($_GET['school_id']) ? (int)$_GET['school_id'] : null;

// Pastikan saklar pengingat mengajar aktif di database
Setting::setValue('wa_enabled', true, 'boolean', 'whatsapp');
Setting::setValue('wa_digest_enabled', true, 'boolean', 'whatsapp');
Setting::setValue('wa_send_teaching_reminder', true, 'boolean', 'whatsapp');

echo "<!DOCTYPE html><html><head><title>Peluncur Pengingat Jadwal Mengajar Guru</title>";
echo "<style>
body { font-family: 'JetBrains Mono', monospace, sans-serif; background: #0b1120; color: #f8fafc; padding: 25px; margin: 0; }
.card { background: #1e293b; padding: 25px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); max-width: 900px; margin: auto; border: 1px solid #334155; }
h2 { color: #38bdf8; margin-top: 0; border-bottom: 1px solid #334155; padding-bottom: 12px; font-size: 20px; }
pre { background: #030712; padding: 18px; border-radius: 10px; color: #e2e8f0; font-size: 13px; line-height: 1.7; max-height: 520px; overflow-y: auto; border: 1px solid #1f2937; }
.ok { color: #4ade80; font-weight: bold; }
.warn { color: #fbbf24; }
.err { color: #f87171; font-weight: bold; }
.info { color: #38bdf8; }
.badge { display: inline-block; padding: 4px 10px; background: #0284c7; color: #fff; border-radius: 6px; font-weight: bold; font-size: 11px; text-transform: uppercase; margin-right: 6px; }
.badge-sim { background: #f59e0b; }
.badge-force { background: #ef4444; }
.badge-test { background: #8b5cf6; }
</style></head><body><div class='card'>";
echo "<h2>⏰ PELUNCUR PENGINGAT JADWAL MENGAJAR GURU (HARI INI)</h2>";

echo "<div>";
if ($dryRun) echo "<span class='badge badge-sim'>MODE SIMULASI (DRY-RUN)</span>";
if ($force) echo "<span class='badge badge-force'>FORCE OVERWRITE</span>";
if ($testPhone) echo "<span class='badge badge-test'>UJI COBA KE: " . htmlspecialchars($testPhone) . "</span>";
echo "</div><br>";

echo "<pre>";

function logMsg($msg) {
    $time = date('H:i:s');
    $clean = htmlspecialchars($msg);
    if (strpos($msg, '✅') !== false) {
        echo "<span class='ok'>[{$time}] {$clean}</span>\n";
    } elseif (strpos($msg, '❌') !== false || strpos($msg, '🛑') !== false) {
        echo "<span class='err'>[{$time}] {$clean}</span>\n";
    } elseif (strpos($msg, '⚠️') !== false || strpos($msg, '⏳') !== false || strpos($msg, '⏭️') !== false) {
        echo "<span class='warn'>[{$time}] {$clean}</span>\n";
    } else {
        echo "<span class='info'>[{$time}] {$clean}</span>\n";
    }
    @flush();
}

$waService = app(WhatsAppService::class);
$reportService = app(ExecutiveReportService::class);

logMsg("Memulai proses Pengingat Jadwal Mengajar Guru...");
logMsg("Gateway Provider : " . $waService->getActiveProvider());
logMsg("Device Connected : " . ($waService->isConnected() ? '✅ YES' : '❌ NO'));
logMsg("Status Saklar    : wa_send_teaching_reminder = ON");

$options = [
    'dry_run' => $dryRun,
    'force' => $force,
    'target_phone' => $testPhone,
    'school_id' => $schoolId,
    'delay_min' => 4,
    'delay_max' => 7,
    'logger' => function ($msg) {
        logMsg($msg);
    },
];

$result = $reportService->sendTeachingScheduleReminder($options);

echo "\n======================================================\n";
logMsg("🏁 HASIL AKHIR: " . ($result['message'] ?? 'Selesai'));
logMsg("📊 Terkirim: " . ($result['sent'] ?? 0) . " | Dilewati: " . ($result['skipped'] ?? 0) . " | Gagal: " . count($result['errors'] ?? []));
echo "</pre></div></body></html>";
