<?php
/**
 * Diagnostik Status Pengiriman & Akun WhatsApp
 * Akses: https://perguruanpembda.com/check_ban_status.php?secret=pembda99
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

// Bootstrap Laravel
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/pembdahub/vendor/autoload.php')) {
    require __DIR__ . '/pembdahub/vendor/autoload.php';
    $app = require_once __DIR__ . '/pembdahub/bootstrap/app.php';
} else {
    die('Autoload not found');
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Models\Teacher;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');

// Matikan segera semua saklar pengiriman otomatis agar aman
Setting::setValue('wa_enabled', false, 'boolean', 'whatsapp');
Setting::setValue('wa_digest_enabled', false, 'boolean', 'whatsapp');
Setting::setValue('wa_send_teaching_reminder', false, 'boolean', 'whatsapp');
Setting::setValue('wa_send_principal_attendance', false, 'boolean', 'whatsapp');
Setting::setValue('wa_send_homeroom_attendance', false, 'boolean', 'whatsapp');

$deletedJobs = 0;
try {
    $deletedJobs = DB::table('jobs')->delete();
} catch (\Throwable $e) {}

$waService = app(WhatsAppService::class);
$accountInfo = $waService->getAccountInfo();

$dateToday = date('Y-m-d');
$teachers = Teacher::with('school')->get();
$sentTeachers = [];
$unsentTeachers = [];

foreach ($teachers as $t) {
    $key = "wa_digest_sent_teaching_reminder_{$t->id}_{$dateToday}";
    if (Cache::has($key)) {
        $sentTeachers[] = [
            'id' => $t->id,
            'name' => $t->full_name,
            'school' => $t->school?->name ?? '-',
            'phone' => $t->phone ?: ($t->user?->phone ?? '-'),
        ];
    } else {
        $unsentTeachers[] = [
            'id' => $t->id,
            'name' => $t->full_name,
            'school' => $t->school?->name ?? '-',
            'phone' => $t->phone ?: ($t->user?->phone ?? '-'),
        ];
    }
}

// Baca log whatsapp terbaru
$logPath = storage_path('logs/whatsapp.log');
$recentLogs = [];
if (file_exists($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentLogs = array_slice($lines, -100);
}

$laravelLogPath = storage_path('logs/laravel.log');
$recentLaravelLogs = [];
if (file_exists($laravelLogPath)) {
    $lines = file($laravelLogPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentLaravelLogs = array_slice($lines, -50);
}

echo "<!DOCTYPE html><html><head><title>Diagnostik Status WhatsApp & Jadwal Terkirim</title>";
echo "<style>
body { font-family: monospace; background: #0f172a; color: #f8fafc; padding: 25px; }
.card { background: #1e293b; padding: 20px; border-radius: 10px; max-width: 950px; margin: auto; border: 1px solid #334155; }
h2 { color: #f87171; border-bottom: 1px solid #334155; padding-bottom: 10px; }
.box { background: #090d16; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #1e293b; overflow-x: auto; font-size: 13px; }
.ok { color: #4ade80; }
.warn { color: #fbbf24; }
.err { color: #f87171; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #334155; padding: 6px 10px; text-align: left; font-size: 12px; }
th { background: #0f172a; color: #38bdf8; }
</style></head><body><div class='card'>";
echo "<h2>🛑 EMERGENCY CONTROL & STATUS PENGIRIMAN WHATSAPP</h2>";

echo "<div class='box'>";
echo "<span class='err'>✔ SELURUH FITUR PENGIRIMAN WA TELAH DIMATIKAN OTOMATIS (STATUS: OFF)</span><br>";
echo "Antrean Background Jobs Dihapus: {$deletedJobs} pekerjaan dibersihkan.<br>";
echo "wa_enabled: <strong>FALSE</strong> | wa_digest_enabled: <strong>FALSE</strong> | wa_send_teaching_reminder: <strong>FALSE</strong>";
echo "</div>";

echo "<h3>📱 Status Akun WhatsApp Gateway Fonnte:</h3>";
echo "<div class='box'><pre>" . htmlspecialchars(json_encode($accountInfo, JSON_PRETTY_PRINT)) . "</pre></div>";

echo "<h3>📊 Statistik Pengingat Jadwal Mengajar Hari Ini:</h3>";
echo "<div class='box'>";
echo "Total Guru Mengajar yang TERKIRIM Hari Ini : <strong class='ok'>" . count($sentTeachers) . " Guru</strong><br>";
echo "Total Guru Mengajar yang BELUM Terkirim : <strong class='warn'>" . count($unsentTeachers) . " Guru</strong><br>";
echo "</div>";

if (count($sentTeachers) > 0) {
    echo "<h3>📋 Daftar Guru yang Berhasil Menerima Pesan Hari Ini:</h3>";
    echo "<table><tr><th>No</th><th>ID</th><th>Nama Guru</th><th>Unit Sekolah</th><th>Nomor WhatsApp</th></tr>";
    foreach ($sentTeachers as $idx => $st) {
        $no = $idx + 1;
        echo "<tr><td>{$no}</td><td>{$st['id']}</td><td>{$st['name']}</td><td>{$st['school']}</td><td>{$st['phone']}</td></tr>";
    }
    echo "</table>";
}

echo "<h3>📜 30 Baris Terakhir whatsapp.log:</h3>";
echo "<div class='box'><pre>";
foreach (array_slice($recentLogs, -30) as $rl) {
    echo htmlspecialchars($rl) . "\n";
}
echo "</pre></div>";

echo "</div></body></html>";
