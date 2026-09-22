<?php
/**
 * Uji Coba Pengiriman Rekapitulasi Presensi Kepala Sekolah via Fonnte Cloud
 * Akses: https://perguruanpembda.com/test_send_principal.php?secret=pembda99
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
use App\Models\Setting;
use App\Services\ExecutiveReportService;
use App\Services\WhatsAppService;

$waService = app(WhatsAppService::class);
$reportService = app(ExecutiveReportService::class);

$accountInfo = $waService->getAccountInfo();
$isConnected = $waService->isConnected();
$provider = $waService->getActiveProvider();

$schools = School::schoolsOnly()->with('principal')->get();

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$executionLogs = [];
$result = null;

if ($action === 'send') {
    $targetPhone = trim($_POST['target_phone'] ?? '');
    $targetSchoolId = !empty($_POST['school_id']) ? (int)$_POST['school_id'] : null;

    $options = [
        'force' => true,
        'dry_run' => false,
        'target_phone' => $targetPhone ?: null,
        'school_id' => $targetSchoolId,
        'delay_min' => 5,
        'delay_max' => 10,
        'logger' => function ($msg) use (&$executionLogs) {
            $executionLogs[] = '[' . date('H:i:s') . '] ' . $msg;
        },
    ];

    $result = $reportService->sendPrincipalDailyAttendanceDigest($options);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Uji Coba Rekap Presensi Kepala Sekolah (Fonnte)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
        .container { max-width: 800px; margin: auto; }
        .card { background: #1e293b; border-radius: 12px; padding: 24px; border: 1px solid #334155; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        h1, h2, h3 { margin-top: 0; color: #38bdf8; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; font-family: monospace; }
        .badge-green { background: #166534; color: #4ade80; border: 1px solid #22c55e; }
        .badge-red { background: #991b1b; color: #fca5a5; border: 1px solid #ef4444; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 6px; color: #94a3b8; }
        input[type="text"], select { width: 100%; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 14px; box-sizing: border-box; }
        input[type="text"]:focus, select:focus { outline: none; border-color: #38bdf8; }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; cursor: pointer; border: none; text-decoration: none; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover { background: #047857; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #334155; }
        th { background: #0f172a; color: #94a3b8; }
        .console { background: #020617; color: #38bdf8; font-family: monospace; padding: 15px; border-radius: 8px; font-size: 13px; line-height: 1.6; max-height: 300px; overflow-y: auto; border: 1px solid #1e293b; }
    </style>
</head>
<body>
<div class="container">

    <div class="card">
        <h1>🚀 Uji Coba Pengiriman Rekap Presensi Kepala Sekolah</h1>
        <p style="color: #94a3b8; font-size: 14px;">Fitur ini menjalankan pengiriman rekap harian eksekutif 3 unit sekolah menggunakan gateway <strong>Fonnte Cloud</strong>.</p>
        
        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
            <div>
                <span style="font-size: 12px; color: #64748b;">Provider Aktif:</span><br>
                <span class="badge badge-green"><?= strtoupper($provider) ?> (Cloud API)</span>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b;">Status Koneksi Device:</span><br>
                <?php if ($isConnected): ?>
                    <span class="badge badge-green">🟢 TERHUBUNG (<?= $accountInfo['data']['device'] ?? '-' ?> - <?= $accountInfo['data']['name'] ?? '-' ?>)</span>
                <?php else: ?>
                    <span class="badge badge-red">🔴 TERPUTUS (Cek dashboard Fonnte)</span>
                <?php endif; ?>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b;">Sisa Kuota:</span><br>
                <span class="badge badge-green"><?= $accountInfo['data']['quota'] ?? '-' ?> Pesan</span>
            </div>
        </div>
    </div>

    <?php if ($result !== null): ?>
    <div class="card" style="border-left: 4px solid <?= !empty($result['sent']) ? '#22c55e' : '#f59e0b' ?>;">
        <h2><?= !empty($result['sent']) ? '✅ Berhasil Dikirim!' : 'ℹ️ Hasil Eksekusi' ?></h2>
        <p><strong>Status:</strong> <?= htmlspecialchars($result['message'] ?? '') ?></p>
        <p><strong>Pesan Terkirim:</strong> <span class="badge badge-green"><?= $result['sent'] ?? 0 ?> Pesan</span> | <strong>Dilewati:</strong> <?= $result['skipped'] ?? 0 ?></p>
        
        <?php if (!empty($executionLogs)): ?>
            <h3>Log Eksekusi Real-time:</h3>
            <div class="console">
                <?php foreach ($executionLogs as $lg): ?>
                    <?= htmlspecialchars($lg) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>📋 Daftar 3 Kepala Sekolah Aktif:</h2>
        <table>
            <tr>
                <th>Unit Sekolah</th>
                <th>Nama Kepala Sekolah</th>
                <th>Nomor WhatsApp</th>
            </tr>
            <?php foreach ($schools as $s): 
                $p = $s->principal;
                $phone = $p?->phone ?? '-';
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($s->name) ?></strong></td>
                <td><?= htmlspecialchars($p?->full_name ?? $s->principal_name ?? '-') ?></td>
                <td><code><?= htmlspecialchars($phone) ?></code></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>🧪 Jalankan Uji Coba Pengiriman:</h2>
        <form method="POST" action="?secret=pembda99">
            <input type="hidden" name="action" value="send">
            
            <div class="form-group">
                <label for="school_id">Pilih Target Unit Sekolah:</label>
                <select name="school_id" id="school_id">
                    <option value="">-- Kirim Rekap Semua 3 Unit Sekolah --</option>
                    <?php foreach ($schools as $s): ?>
                        <option value="<?= $s->id ?>"><?= htmlspecialchars($s->name) ?> (<?= htmlspecialchars($s->principal?->full_name ?? $s->principal_name ?? '') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="target_phone">Kirim ke Nomor Khusus / Nomor Anda Sendiri (Opsional):</label>
                <input type="text" name="target_phone" id="target_phone" placeholder="Contoh: 081263582950 (Kosongkan jika ingin langsung dikirim ke nomor asli Kepala Sekolah)">
                <small style="color: #64748b; margin-top: 4px; display: block;">
                    💡 <em>Sangat disarankan: Masukkan nomor WhatsApp Anda terlebih dahulu jika ingin memeriksa tampilan format pesan sebelum dikirimkan ke Kepala Sekolah asli.</em>
                </small>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin mengirim rekapitulasi kehadiran sekarang via Fonnte Cloud?');">
                    🚀 Kirim Rekapitulasi Sekarang
                </button>
            </div>
        </form>
    </div>

</div>
</body>
</html>
