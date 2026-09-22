<?php
/**
 * Emergency WA Disabler Tool
 * Akses: https://perguruanpembda.com/stop_wa.php?secret=pembda99
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

// Disable all WhatsApp settings
Setting::setValue('wa_digest_enabled', false, 'boolean', 'whatsapp');
Setting::setValue('wa_enabled', false, 'boolean', 'whatsapp');
Setting::setValue('wa_send_principal_attendance', false, 'boolean', 'whatsapp');
Setting::setValue('wa_send_homeroom_attendance', false, 'boolean', 'whatsapp');

// Clear pending jobs queue
$deletedJobs = DB::table('jobs')->delete();

echo "<!DOCTYPE html><html><head><title>WhatsApp Engine Off</title>";
echo "<style>body{font-family:sans-serif;padding:30px;background:#f8fafc;color:#1e293b;} .card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);max-width:600px;margin:auto;} .badge{display:inline-block;padding:6px 12px;background:#ef4444;color:#fff;border-radius:6px;font-weight:bold;}</style>";
echo "</head><body><div class='card'>";
echo "<h2><span class='badge'>OFF</span> WhatsApp Engine Berhasil Dimatikan</h2>";
echo "<p>Seluruh fitur otomatisasi WhatsApp dan Rekapitulasi Eksekutif telah <strong>NONAKTIF (OFF)</strong> di database server.</p>";
echo "<ul>";
echo "<li><code>wa_digest_enabled</code> = false</li>";
echo "<li><code>wa_enabled</code> = false</li>";
echo "<li><code>wa_send_principal_attendance</code> = false</li>";
echo "<li><code>wa_send_homeroom_attendance</code> = false</li>";
echo "<li>Tabel Job Antrean Di-cleared: <strong>{$deletedJobs} pekerjaan dibuang</strong></li>";
echo "</ul>";
echo "<p style='color:#059669;'>Tidak ada pesan WhatsApp yang akan dikirimkan lagi oleh sistem.</p>";
echo "</div></body></html>";
