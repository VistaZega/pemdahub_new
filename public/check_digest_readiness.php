<?php
/**
 * PembdaHUB - Pre-Flight Check Kesiapan Pengiriman Rekap WA 08:00 WIB
 * Akses: https://perguruanpembda.com/check_digest_readiness.php?secret=pembda99
 */

header('Content-Type: text/html; charset=utf-8');

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden: secret key required (?secret=pembda99)');
}

// Bootstrap Laravel
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/pembdahub/vendor/autoload.php')) {
    require __DIR__ . '/pembdahub/vendor/autoload.php';
    $app = require_once __DIR__ . '/pembdahub/bootstrap/app.php';
} else {
    die('Autoload not found.');
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Services\ExecutiveReportService;
use App\Services\WhatsAppService;
use App\Services\SpintaxService;

$waService = app(WhatsAppService::class);
$reportService = app(ExecutiveReportService::class);

$now = now();
$today = date('Y-m-d');
$wibTime = date('H:i:s');

// 1. Fonnte Device Status
$deviceInfo = $waService->getAccountInfo();
$deviceData = $deviceInfo['data'] ?? [];
$deviceStatus = $deviceData['device_status'] ?? 'unknown';
$deviceNumber = $deviceData['device'] ?? '-';
$quota = $deviceData['quota'] ?? 'N/A';
$package = $deviceData['package'] ?? 'N/A';
$expired = $deviceData['expired'] ?? 'N/A';
$isConnected = $waService->isConnected();

// 2. Settings Status
$settings = [
    'wa_enabled' => Setting::getValue('wa_enabled', false),
    'wa_digest_enabled' => Setting::getValue('wa_digest_enabled', false),
    'wa_active_provider' => Setting::getValue('wa_active_provider', 'none'),
    'whatsapp_sender' => Setting::getValue('whatsapp_sender', '-'),
    'wa_fonnte_token' => Setting::getValue('wa_fonnte_token', '-'),
    'wa_send_principal_attendance' => Setting::getValue('wa_send_principal_attendance', false),
    'wa_send_homeroom_attendance' => Setting::getValue('wa_send_homeroom_attendance', false),
    'wa_send_teacher_attendance' => Setting::getValue('wa_send_teacher_attendance', false),
    'wa_send_teaching_reminder' => Setting::getValue('wa_send_teaching_reminder', false),
    'wa_digest_delay_min' => Setting::getValue('wa_digest_delay_min', 120),
    'wa_digest_delay_max' => Setting::getValue('wa_digest_delay_max', 180),
    'wa_digest_batch_pause' => Setting::getValue('wa_digest_batch_pause', 300),
];

// Mask token for display
$maskedToken = substr($settings['wa_fonnte_token'], 0, 4) . '...' . substr($settings['wa_fonnte_token'], -4);

// Fetch QR Code if device is disconnected
$qrBase64 = null;
if (!$isConnected && $deviceStatus !== 'connect') {
    try {
        $ch = curl_init('https://api.fonnte.com/qr');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $settings['wa_fonnte_token']]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resQr = curl_exec($ch);
        curl_close($ch);
        if ($resQr) {
            $jsonQr = json_decode($resQr, true);
            if (!empty($jsonQr['url'])) {
                $qrBase64 = $jsonQr['url'];
            }
        }
    } catch (\Throwable $e) {}
}

// 3. Recipients Pre-flight
$activeYear = AcademicYear::where('is_active', true)->first();
$schools = School::schoolsOnly()->with('principal')->get();

$principalRecipients = [];
foreach ($schools as $sc) {
    $p = $sc->principal;
    $phone = $p?->phone ?? null;
    $principalRecipients[] = [
        'school' => $sc->name,
        'name' => $p?->full_name ?? $sc->principal_name ?? 'N/A',
        'phone' => $phone ?: '❌ KOSONG',
        'ready' => !empty($phone),
    ];
}

$homeroomRecipients = [];
if ($activeYear) {
    $classes = Classroom::whereIn('school_id', $schools->pluck('id'))
        ->where('academic_year_id', $activeYear->id)
        ->where('is_active', true)
        ->with(['school', 'homeroomTeacher'])
        ->get();

    foreach ($classes as $cl) {
        $ht = $cl->homeroomTeacher;
        $phone = $ht?->phone ?? null;
        $homeroomRecipients[] = [
            'school' => $cl->school->name ?? '-',
            'class' => $cl->name,
            'name' => $ht?->full_name ?? 'N/A',
            'phone' => $phone ?: '❌ KOSONG',
            'ready' => !empty($phone),
        ];
    }
}

// 4. Dry-Run Simulation
$simLogs = [];
$simOptions = [
    'dry_run' => true,
    'logger' => function($msg) use (&$simLogs) {
        $simLogs[] = $msg;
    }
];

$simPrincipal = $reportService->sendPrincipalDailyAttendanceDigest($simOptions);
$simHomeroom = $reportService->sendHomeroomDailyAttendanceDigest($simOptions);

// 5. Overall Readiness Score
$readyChecks = [
    'Fonnte Terhubung (Status: connect)' => $deviceStatus === 'connect' || $isConnected,
    'Token Fonnte Sesuai (uvpi...HBNS)' => $settings['wa_fonnte_token'] === 'uvpiXLkGyfi8a9Y3HBNS',
    'Nomor Pengirim Baru (082373642864)' => $settings['whatsapp_sender'] === '082373642864',
    'Layanan WA Aktif (wa_enabled)' => (bool)$settings['wa_enabled'],
    'Digest Otomatis Aktif (wa_digest_enabled)' => (bool)$settings['wa_digest_enabled'],
    'Rekap Kepsek Aktif (wa_send_principal_attendance)' => (bool)$settings['wa_send_principal_attendance'],
    'Rekap Wali Kelas Aktif (wa_send_homeroom_attendance)' => (bool)$settings['wa_send_homeroom_attendance'],
    'Tap Guru Dinonaktifkan (wa_send_teacher_attendance = OFF)' => !$settings['wa_send_teacher_attendance'],
    'Pengingat Jadwal Dinonaktifkan (wa_send_teaching_reminder = OFF)' => !$settings['wa_send_teaching_reminder'],
    'Jeda Per Pesan 2-3 Menit (120-180 detik)' => (int)$settings['wa_digest_delay_min'] >= 120 && (int)$settings['wa_digest_delay_max'] >= 180,
    'Jeda Transisi Kepsek-Wali Kelas 5 Menit (300 detik)' => (int)$settings['wa_digest_batch_pause'] >= 300,
    'Tahun Pelajaran Aktif Ditemukan' => $activeYear !== null,
    'Seluruh Kepala Sekolah Memiliki Nomor HP' => count(array_filter($principalRecipients, fn($r) => !$r['ready'])) === 0,
    'Simulasi Rekap Kepsek Berhasil' => ($simPrincipal['sent'] ?? 0) > 0,
    'Simulasi Rekap Wali Kelas Berhasil' => ($simHomeroom['sent'] ?? 0) > 0,
];

$passedCount = count(array_filter($readyChecks));
$totalChecks = count($readyChecks);
$is100Percent = ($passedCount === $totalChecks);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pre-Flight Check Kesiapan WA 08:00 WIB</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0b1120; color: #f1f5f9; padding: 24px; margin: 0; line-height: 1.5; }
        .container { max-width: 960px; margin: 0 auto; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        h1 { font-size: 22px; color: #38bdf8; margin-top: 0; border-bottom: 1px solid #334155; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        h2 { font-size: 16px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin: 16px 0 8px 0; }
        .score-box { text-align: center; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .score-100 { background: #064e3b; border: 2px solid #10b981; color: #6ee7b7; }
        .score-warn { background: #78350f; border: 2px solid #f59e0b; color: #fde68a; }
        .score-title { font-size: 28px; font-weight: 800; margin: 0 0 6px 0; }
        .score-desc { font-size: 14px; opacity: 0.9; }
        .check-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px; margin: 15px 0; }
        .check-item { background: #0f172a; padding: 12px 14px; border-radius: 8px; border: 1px solid #334155; font-size: 13px; display: flex; align-items: center; justify-content: space-between; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .badge-pass { background: #10b981; color: #fff; }
        .badge-fail { background: #ef4444; color: #fff; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; }
        .table th, .table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #334155; }
        .table th { background: #0f172a; color: #94a3b8; font-weight: 600; }
        pre { background: #090d16; padding: 14px; border-radius: 8px; color: #e2e8f0; font-size: 12px; max-height: 240px; overflow-y: auto; border: 1px solid #1e293b; }
        .sample-box { background: #0f172a; border-left: 4px solid #38bdf8; padding: 14px; border-radius: 8px; font-size: 13px; margin: 10px 0; white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>
            <span>🚀 Pre-Flight Check: Rekapitulasi WA 08:00 WIB</span>
            <span style="font-size: 14px; color: #94a3b8;">Waktu Server: <?= $wibTime ?> WIB (<?= $today ?>)</span>
        </h1>

        <div class="score-box <?= $is100Percent ? 'score-100' : 'score-warn' ?>">
            <div class="score-title"><?= $is100Percent ? '✅ 100% SIAP DILUNCURKAN' : '⚠️ PERHATIAN (' . $passedCount . '/' . $totalChecks . ' Lolos)' ?></div>
            <div class="score-desc">
                <?= $is100Percent 
                    ? 'Seluruh parameter mode aman, gateway Fonnte, kredensial, jeda waktu, dan data penerima telah lolos verifikasi sempurna.' 
                    : 'Beberapa parameter memerlukan penyesuaian sebelum pukul 08:00 WIB.' ?>
            </div>
        </div>

        <?php if ($qrBase64): ?>
        <div style="background: #1e1b4b; border: 2px solid #818cf8; border-radius: 12px; padding: 22px; text-align: center; margin: 20px 0;">
            <h3 style="color: #a5b4fc; margin-top: 0; font-size: 18px;">📲 PINDAI QR CODE WHATSAPP SEKARANG UNTUK MENGHUBUNGKAN</h3>
            <p style="font-size: 14px; color: #cbd5e1; max-width: 600px; margin: 0 auto 15px auto;">
                Nomor WhatsApp baru Anda (<strong><?= htmlspecialchars($settings['whatsapp_sender']) ?></strong>) saat ini berstatus <strong>DISCONNECT</strong> di Fonnte.<br>
                Buka WhatsApp di HP Anda &rarr; <strong>Perangkat Tertaut (Linked Devices)</strong> &rarr; <strong>Tautkan Perangkat</strong> &rarr; Arahkan kamera ke QR di bawah:
            </p>
            <div style="background: white; display: inline-block; padding: 14px; border-radius: 14px; margin: 10px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                <img src="data:image/png;base64,<?= $qrBase64 ?>" alt="QR Code Fonnte" style="width: 260px; height: 260px; display: block;" />
            </div>
            <p style="margin-top: 15px;">
                <a href="?secret=pembda99&refresh=<?= time() ?>" style="display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">
                    🔄 Refresh & Cek Status Koneksi Setelah Scan
                </a>
            </p>
        </div>
        <?php endif; ?>

        <h2>1. Indikator Kesiapan Sistem (<?= $passedCount ?> / <?= $totalChecks ?>)</h2>
        <div class="check-grid">
            <?php foreach ($readyChecks as $label => $pass): ?>
                <div class="check-item">
                    <span><?= htmlspecialchars($label) ?></span>
                    <span class="badge <?= $pass ? 'badge-pass' : 'badge-fail' ?>"><?= $pass ? 'LOLOS' : 'GAGAL' ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>2. Status Gateway & Akun Fonnte</h2>
        <table class="table">
            <tr><th>Status Device</th><td><strong style="color: <?= $deviceStatus === 'connect' ? '#34d399' : '#f87171' ?>"><?= strtoupper($deviceStatus) ?></strong> (<?= $isConnected ? 'Connected' : 'Disconnected' ?>)</td></tr>
            <tr><th>Nomor Sender Terhubung</th><td><?= htmlspecialchars($deviceNumber) ?> (Setting DB: <?= htmlspecialchars($settings['whatsapp_sender']) ?>)</td></tr>
            <tr><th>Sisa Kuota API</th><td><?= htmlspecialchars($quota) ?></td></tr>
            <tr><th>Paket / Kadaluarsa</th><td><?= htmlspecialchars($package) ?> / <?= htmlspecialchars($expired) ?></td></tr>
            <tr><th>Token Aktif</th><td><code><?= htmlspecialchars($maskedToken) ?></code></td></tr>
        </table>

        <h2>3. Parameter Jeda & Mode Aman Anti-Banned</h2>
        <table class="table">
            <tr><th>Jeda Minimum Per Pesan</th><td><strong><?= $settings['wa_digest_delay_min'] ?> Detik (<?= round($settings['wa_digest_delay_min']/60, 1) ?> Menit)</strong></td></tr>
            <tr><th>Jeda Maksimum Per Pesan</th><td><strong><?= $settings['wa_digest_delay_max'] ?> Detik (<?= round($settings['wa_digest_delay_max']/60, 1) ?> Menit)</strong></td></tr>
            <tr><th>Jeda Pendinginan Kepsek → Wali Kelas</th><td><strong><?= $settings['wa_digest_batch_pause'] ?> Detik (<?= round($settings['wa_digest_batch_pause']/60, 1) ?> Menit)</strong></td></tr>
            <tr><th>Notifikasi Tap Hadir Guru</th><td><span class="badge <?= !$settings['wa_send_teacher_attendance'] ? 'badge-pass' : 'badge-fail' ?>"><?= $settings['wa_send_teacher_attendance'] ? 'AKTIF' : 'NONAKTIF (AMAN)' ?></span></td></tr>
            <tr><th>Pengingat Jadwal Mengajar</th><td><span class="badge <?= !$settings['wa_send_teaching_reminder'] ? 'badge-pass' : 'badge-fail' ?>"><?= $settings['wa_send_teaching_reminder'] ? 'AKTIF' : 'NONAKTIF (AMAN)' ?></span></td></tr>
        </table>

        <h2>4. Calon Penerima Rekap Eksekutif (Kepala Sekolah: <?= count($principalRecipients) ?>, Wali Kelas: <?= count($homeroomRecipients) ?>)</h2>
        <table class="table">
            <thead>
                <tr><th>Role / Unit</th><th>Nama Lengkap</th><th>Nomor WhatsApp</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($principalRecipients as $pr): ?>
                <tr>
                    <td><strong>Kepsek <?= htmlspecialchars($pr['school']) ?></strong></td>
                    <td><?= htmlspecialchars($pr['name']) ?></td>
                    <td><?= htmlspecialchars($pr['phone']) ?></td>
                    <td><span class="badge <?= $pr['ready'] ? 'badge-pass' : 'badge-fail' ?>"><?= $pr['ready'] ? 'SIAP' : 'KOSONG' ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>5. Contoh Pesan Interaktif Ajakan Balas (Anti-Bot)</h2>
        <div class="sample-box"><?= htmlspecialchars(SpintaxService::spinReplyRequest()) ?></div>

        <h2>6. Log Hasil Simulasi Perhitungan Data (Dry-Run Sukses)</h2>
        <pre><?= htmlspecialchars(implode("\n", $simLogs)) ?></pre>
    </div>
</div>
</body>
</html>
