<?php
/**
 * Live Broadcast Rekapitulasi Presensi Wali Kelas via Fonnte Cloud
 * Akses: https://perguruanpembda.com/send_homeroom_rekap.php?secret=pembda99
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('⛔ Akses Ditolak: Secret key wajib (?secret=pembda99)');
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

use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Setting;
use App\Services\ExecutiveReportService;
use App\Services\WhatsAppService;

$waService = app(WhatsAppService::class);
$reportService = app(ExecutiveReportService::class);

$accountInfo = $waService->getAccountInfo();
$isConnected = $waService->isConnected();
$provider = $waService->getActiveProvider();

$activeYear = AcademicYear::where('is_active', true)->first();
$schools = School::schoolsOnly()->get();

$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Handle Streaming Live Broadcast
if ($action === 'broadcast_live') {
    // Disable time limit & enable output streaming
    @set_time_limit(0);
    @ini_set('max_execution_time', 0);
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    while (ob_get_level()) ob_end_clean();

    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no'); // Disable FastCGI buffer on Nginx/Apache

    $targetSchoolId = !empty($_POST['school_id']) ? (int)$_POST['school_id'] : null;
    $targetPhone = trim($_POST['target_phone'] ?? '');

    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Live Broadcast Rekap Wali Kelas</title>";
    echo "<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace; background: #0b0f19; color: #f1f5f9; padding: 25px; margin: 0; line-height: 1.6; }
    .box { max-width: 900px; margin: auto; background: #131b2e; border: 1px solid #1e293b; border-radius: 12px; padding: 24px; box-shadow: 0 4px 25px rgba(0,0,0,0.5); }
    h2 { color: #38bdf8; margin-top: 0; }
    .log-row { padding: 6px 12px; border-radius: 6px; margin-bottom: 4px; font-size: 13px; font-family: monospace; }
    .log-info { background: #1e293b; color: #94a3b8; }
    .log-ok { background: #064e3b; color: #34d399; border-left: 3px solid #10b981; }
    .log-warn { background: #78350f; color: #fcd34d; border-left: 3px solid #f59e0b; }
    .log-err { background: #7f1d1d; color: #fca5a5; border-left: 3px solid #ef4444; }
    .btn-back { display: inline-block; padding: 10px 20px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
    </style></head><body><div class='box'>";
    echo "<h2>📡 Live Broadcast Rekapitulasi Presensi Wali Kelas (Fonnte Cloud)</h2>";
    echo "<p style='color:#94a3b8; font-size:13px;'>Pesan disalurkan dengan jeda aman 5–8 detik per kelas untuk kenyamanan gateway.</p><hr style='border:0; border-top:1px solid #1e293b; margin:15px 0;'>";

    echo "<div id='console'>";

    $options = [
        'force' => true,
        'dry_run' => false,
        'target_phone' => $targetPhone ?: null,
        'school_id' => $targetSchoolId,
        'delay_min' => 5,
        'delay_max' => 8,
        'batch_pause' => 12,
        'logger' => function ($msg) {
            $time = date('H:i:s');
            $css = 'log-info';
            if (str_contains($msg, 'Sukses') || str_contains($msg, 'Berhasil') || str_contains($msg, 'terkirim')) {
                $css = 'log-ok';
            } elseif (str_contains($msg, 'Jeda') || str_contains($msg, 'Istirahat') || str_contains($msg, 'dilewati')) {
                $css = 'log-warn';
            } elseif (str_contains($msg, 'Gagal') || str_contains($msg, 'Error') || str_contains($msg, 'tidak ditemukan')) {
                $css = 'log-err';
            }
            echo "<div class='log-row {$css}'>[{$time}] " . htmlspecialchars($msg) . "</div>";
            @flush();
        },
    ];

    $res = $reportService->sendHomeroomDailyAttendanceDigest($options);

    echo "</div>"; // #console

    echo "<div style='margin-top: 25px; padding: 15px; background: #064e3b; border-radius: 8px; border: 1px solid #059669;'>";
    echo "<h3 style='color:#34d399; margin:0 0 8px 0;'>🎉 BROADCAST SELESAI!</h3>";
    echo "<p style='margin:0; font-size:14px;'>Total Berhasil Terkirim: <strong>" . ($res['sent'] ?? 0) . " Kelas</strong> | Dilewati: <strong>" . ($res['skipped'] ?? 0) . " Kelas</strong></p>";
    echo "</div>";

    echo "<a href='?secret=pembda99' class='btn-back'>← Kembali ke Panel Broadcast</a>";
    echo "</div></body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Peluncur Rekap Presensi Wali Kelas</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
        .container { max-width: 900px; margin: auto; }
        .card { background: #1e293b; border-radius: 12px; padding: 24px; border: 1px solid #334155; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        h1, h2, h3 { margin-top: 0; color: #38bdf8; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; font-family: monospace; }
        .badge-green { background: #166534; color: #4ade80; border: 1px solid #22c55e; }
        .badge-red { background: #991b1b; color: #fca5a5; border: 1px solid #ef4444; }
        .badge-blue { background: #1e3a8a; color: #93c5fd; border: 1px solid #3b82f6; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 6px; color: #94a3b8; }
        input[type="text"], select { width: 100%; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 14px; box-sizing: border-box; }
        input[type="text"]:focus, select:focus { outline: none; border-color: #38bdf8; }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; cursor: pointer; border: none; text-decoration: none; }
        .btn-primary { background: #059669; color: white; }
        .btn-primary:hover { background: #047857; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #334155; }
        th { background: #0f172a; color: #94a3b8; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin: 15px 0; }
        .stat-box { background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #334155; }
        .stat-val { font-size: 24px; font-weight: bold; color: #38bdf8; margin-top: 4px; }
    </style>
</head>
<body>
<div class="container">

    <div class="card">
        <h1>🚀 Peluncur Rekapitulasi Presensi Seluruh Wali Kelas</h1>
        <p style="color: #94a3b8; font-size: 14px;">Mengirim rekapitulasi kehadiran kelas binaan hari ini langsung ke nomor WhatsApp para Wali Kelas via <strong>Fonnte Cloud</strong>.</p>
        
        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
            <div>
                <span style="font-size: 12px; color: #64748b;">Gateway Aktif:</span><br>
                <span class="badge badge-green"><?= strtoupper($provider) ?> (Cloud API)</span>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b;">Status Koneksi Device:</span><br>
                <?php if ($isConnected): ?>
                    <span class="badge badge-green">🟢 TERHUBUNG (<?= $accountInfo['data']['device'] ?? '-' ?>)</span>
                <?php else: ?>
                    <span class="badge badge-red">🔴 TERPUTUS</span>
                <?php endif; ?>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b;">Sisa Kuota:</span><br>
                <span class="badge badge-green"><?= $accountInfo['data']['quota'] ?? '-' ?> Pesan</span>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>📊 Ringkasan Rombel & Wali Kelas Aktif Hari Ini:</h2>
        <div class="grid">
            <?php 
            $grandTotalClasses = 0;
            foreach ($schools as $sc): 
                $count = Classroom::where('academic_year_id', $activeYear->id)->where('school_id', $sc->id)->count();
                $grandTotalClasses += $count;
            ?>
                <div class="stat-box">
                    <div style="font-size: 12px; color: #94a3b8;"><?= htmlspecialchars($sc->name) ?></div>
                    <div class="stat-val"><?= $count ?> <span style="font-size: 13px; font-weight: normal; color: #64748b;">Rombel</span></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="font-size: 13px; color: #34d399; font-weight: bold; margin-top: 5px;">
            Total Keseluruhan: <?= $grandTotalClasses ?> Kelas Rombel Siap Menerima Rekap
        </div>
    </div>

    <div class="card">
        <h2>⚡ Mulai Pengiriman Rekapitulasi:</h2>
        <form method="POST" action="?secret=pembda99">
            <input type="hidden" name="action" value="broadcast_live">

            <div class="form-group">
                <label for="school_id">Pilih Cakupan Unit Sekolah:</label>
                <select name="school_id" id="school_id">
                    <option value="">-- Kirim ke Seluruh (3) Unit Sekolah (52 Kelas) --</option>
                    <?php foreach ($schools as $sc): ?>
                        <option value="<?= $sc->id ?>"><?= htmlspecialchars($sc->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #64748b; margin-top: 4px; display: block;">
                    💡 Anda bisa memilih mengirim per unit sekolah terlebih dahulu (misal: Unit SMPS dulu, atau SMK dulu), atau langsung seluruh 3 unit sekolah sekaligus.
                </small>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label for="target_phone">Kirim Sampel Uji Coba ke Nomor Tertentu (Opsional):</label>
                <input type="text" name="target_phone" id="target_phone" placeholder="Contoh: 081263582950 (Kosongkan untuk mengirim ke nomor ASLI seluruh Wali Kelas)">
                <small style="color: #64748b; margin-top: 4px; display: block;">
                    Jika dikosongkan, pesan rekapitulasi akan langsung terkirim ke nomor WhatsApp masing-masing Wali Kelas asli.
                </small>
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn btn-primary" style="font-size: 15px; padding: 14px 28px;" onclick="return confirm('Mulai siaran rekap presensi hari ini ke Wali Kelas via Fonnte Cloud? Jeda aman 5-8 detik per kelas akan diterapkan.');">
                    🚀 Mulai Siaran Rekap Sekarang
                </button>
            </div>
        </form>
    </div>

</div>
</body>
</html>
