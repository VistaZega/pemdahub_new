<?php
/**
 * Master Disabler for ALL WhatsApp Features & Automations
 * Akses: https://perguruanpembda.com/disable_all_wa_settings.php?secret=pembda99
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
use Illuminate\Support\Facades\DB;

$allWaKeys = [
    'wa_enabled',
    'wa_digest_enabled',
    'wa_send_principal_attendance',
    'wa_send_homeroom_attendance',
    'wa_send_teacher_attendance',
    'wa_send_psb_registration',
    'wa_send_psb_payment',
    'wa_send_psb_test_schedule',
    'wa_send_psb_acceptance',
    'wa_send_payment_reminder',
    'wa_send_lms_notification',
    'wa_send_counseling_record',
    'wa_send_reputation_award',
    'wa_send_payment_receipt',
    'wa_send_teaching_reminder',
    'wa_send_grade_published',
    'wa_send_attendance_alert',
    'wa_notify_admin_digest',
];

$updated = [];
foreach ($allWaKeys as $key) {
    Setting::setValue($key, false, 'boolean', 'whatsapp');
    $updated[$key] = 'OFF (false)';
}

// Clean queue
$deletedJobs = DB::table('jobs')->delete();

// Kill any Node.js processes
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    @shell_exec("taskkill /F /IM node.exe 2>&1");
} else {
    @shell_exec("pkill -9 -f 'server.js' 2>&1");
    @shell_exec("fuser -k -9 3002/tcp 2>&1");
    @shell_exec("fuser -k -9 3000/tcp 2>&1");
}

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Seluruh Fitur WA Dimatikan</title>";
echo "<style>
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
.card { background: #1e293b; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); max-width: 700px; margin: auto; border: 1px solid #334155; }
h2 { color: #ef4444; margin-top: 0; }
.badge { display: inline-block; padding: 4px 10px; background: #ef4444; color: #fff; border-radius: 4px; font-weight: bold; font-size: 12px; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #334155; }
th { background: #0f172a; color: #94a3b8; }
.off { color: #f87171; font-weight: bold; }
</style></head><body><div class='card'>";
echo "<h2><span class='badge'>TOTAL SHUTDOWN</span> Seluruh Pengaturan WhatsApp & Otomatisasi BERHASIL DIMATIKAN!</h2>";
echo "<p>Semua toggle pengaturan WhatsApp di database telah di-set ke <strong>FALSE / OFF</strong>.</p>";
echo "<p>Antrean jobs dibersihkan: <strong>{$deletedJobs} antrean dibuang</strong>.</p>";
echo "<table><tr><th>Nama Kunci Pengaturan</th><th>Status Baru</th></tr>";
foreach ($updated as $k => $v) {
    echo "<tr><td><code>{$k}</code></td><td class='off'>{$v}</td></tr>";
}
echo "</table>";
echo "<p style='color:#4ade80; margin-top: 20px; font-weight: bold;'>✔ Sistem WhatsApp 100% Mati dan Aman.</p>";
echo "</div></body></html>";
