<?php
/**
 * Diagnostik Kelengkapan Nomor HP Kepala Sekolah & Wali Kelas
 * Akses: https://perguruanpembda.com/check_phone_data.php?secret=pembda99
 *
 * Script ini mengecek apakah semua Kepsek dan Wali Kelas memiliki nomor HP
 * yang diperlukan untuk menerima rekapitulasi kehadiran harian via WhatsApp.
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
    die('Autoload file not found. Checked: ' . __DIR__ . '/../vendor/autoload.php and ' . __DIR__ . '/pembdahub/vendor/autoload.php');
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\Setting;
use App\Services\ExecutiveReportService;

$reportService = app(ExecutiveReportService::class);
$alertMessage = '';
$streamLogs = [];

// 1. Saklar Otomatisasi
if (isset($_GET['enable_attendance_wa']) && $_GET['enable_attendance_wa'] === 'yes') {
    Setting::setValue('wa_send_principal_attendance', true, 'boolean', 'features');
    Setting::setValue('wa_send_homeroom_attendance', true, 'boolean', 'features');
    Setting::setValue('wa_notify_admin_digest', true, 'boolean', 'features');
    $alertMessage = '<div style="background:#d1fae5; border:1px solid #10b981; color:#065f46; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">✅ Berhasil! Saklar otomatisasi WhatsApp untuk <u>Rekap Kepala Sekolah</u>, <u>Rekap Wali Kelas</u>, dan <u>Notifikasi Admin</u> telah DI-AKTIFKAN di database.</div>';
}

if (isset($_GET['disable_attendance_wa']) && $_GET['disable_attendance_wa'] === 'yes') {
    Setting::setValue('wa_send_principal_attendance', false, 'boolean', 'features');
    Setting::setValue('wa_send_homeroom_attendance', false, 'boolean', 'features');
    $alertMessage = '<div style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">⚠️ Saklar otomatisasi WhatsApp untuk Rekap Kepala Sekolah dan Wali Kelas telah DI-NONAKTIFKAN.</div>';
}

// 1B. Saklar Khusus Notifikasi Admin
if (isset($_GET['toggle_admin_notify'])) {
    $val = ($_GET['toggle_admin_notify'] === 'yes');
    Setting::setValue('wa_notify_admin_digest', $val, 'boolean', 'features');
    $alertMessage = $val
        ? '<div style="background:#d1fae5; border:1px solid #10b981; color:#065f46; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">✅ Notifikasi WhatsApp Admin (Berita Mulai & Laporan Selesai) telah DI-AKTIFKAN.</div>'
        : '<div style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">⚠️ Notifikasi WhatsApp Admin (Berita Mulai & Laporan Selesai) telah DI-NONAKTIFKAN.</div>';
}

// 1C. Simpan Nomor WhatsApp Admin
if (($_POST['action'] ?? '') === 'save_admin_phone') {
    $newAdminPhone = trim($_POST['admin_phone'] ?? '');
    if (!empty($newAdminPhone)) {
        Setting::setValue('wa_admin_phone', $newAdminPhone, 'string', 'notifications');
        Setting::setValue('wa_digest_admin_phone', $newAdminPhone, 'string', 'notifications');
        $alertMessage = '<div style="background:#d1fae5; border:1px solid #10b981; color:#065f46; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">✅ Nomor WhatsApp Admin berhasil disimpan: <u>' . htmlspecialchars($newAdminPhone) . '</u>. Berita mulai & laporan akhir pengiriman rekap akan dikirim ke nomor ini.</div>';
    } else {
        $alertMessage = '<div style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">❌ Nomor WhatsApp Admin tidak boleh kosong!</div>';
    }
}

// 2. Reset Kunci Duplikasi Hari Ini
if (isset($_GET['clear_today_locks']) && $_GET['clear_today_locks'] === 'yes') {
    $dateToday = date('Y-m-d');
    $activeYear = AcademicYear::where('is_active', true)->first();
    $schools = School::schoolsOnly()->get();
    foreach ($schools as $s) {
        $reportService->clearDigestSentToday('principal', $s->id, $dateToday);
    }
    if ($activeYear) {
        $classes = Classroom::where('academic_year_id', $activeYear->id)->get();
        foreach ($classes as $c) {
            $reportService->clearDigestSentToday('homeroom', $c->id, $dateToday);
        }
    }
    $alertMessage = '<div style="background:#d1fae5; border:1px solid #10b981; color:#065f46; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">🔄 Kunci proteksi duplikasi hari ini berhasil di-reset. Seluruh rekap dapat dikirimkan kembali hari ini.</div>';
}

// 3. Single Test Mode (Kirim 1 pesan ke nomor tertentu - 100% Bebas Risiko Ban)
$testAction = $_POST['action'] ?? $_GET['action'] ?? '';
if ($testAction === 'single_test') {
    $testPhone = trim($_POST['target_phone'] ?? $_GET['target_phone'] ?? '');
    $testType = $_POST['test_type'] ?? $_GET['test_type'] ?? 'homeroom';

    if (empty($testPhone)) {
        $alertMessage = '<div style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">❌ Harap isi nomor WhatsApp tujuan pengujian!</div>';
    } else {
        try {
            if ($testType === 'admin_start') {
                $res = $reportService->notifyAdminDigestStarted([
                    'target_phone' => $testPhone,
                    'dry_run' => false,
                    'logger' => function($msg) use (&$streamLogs) { $streamLogs[] = $msg; },
                ]);
            } elseif ($testType === 'admin_completed') {
                $res = $reportService->notifyAdminDigestCompleted([
                    'target_phone' => $testPhone,
                    'dry_run' => false,
                    'logger' => function($msg) use (&$streamLogs) { $streamLogs[] = $msg; },
                    'res_principal' => ['sent' => 3, 'skipped' => 0, 'errors' => []],
                    'res_homeroom' => ['sent' => 53, 'skipped' => 0, 'errors' => []],
                    'duration_seconds' => 745,
                ]);
            } elseif ($testType === 'principal') {
                $res = $reportService->sendPrincipalDailyAttendanceDigest([
                    'target_phone' => $testPhone,
                    'dry_run' => false,
                    'logger' => function($msg) use (&$streamLogs) { $streamLogs[] = $msg; },
                ]);
            } else {
                $res = $reportService->sendHomeroomDailyAttendanceDigest([
                    'target_phone' => $testPhone,
                    'dry_run' => false,
                    'logger' => function($msg) use (&$streamLogs) { $streamLogs[] = $msg; },
                ]);
            }
            $alertMessage = '<div style="background:#dbeafe; border:1px solid #3b82f6; color:#1e40af; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">🚀 Hasil Uji Coba Single Test ke <b>' . htmlspecialchars($testPhone) . '</b>: ' . htmlspecialchars($res['message'] ?? 'Selesai') . '</div>';
        } catch (\Throwable $e) {
            $alertMessage = '<div style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">❌ Gagal Uji Coba: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// 4. Batch Execution (Per Unit Sekolah / Semua / Workflow Terpadu)
$action = $_GET['action'] ?? '';
if (in_array($action, ['send_principal', 'send_homeroom', 'dry_run_all', 'send_all_workflow', 'dry_run_workflow'])) {
    $isDryRun = ($action === 'dry_run_all' || $action === 'dry_run_workflow');
    $schoolId = !empty($_GET['school_id']) ? (int)$_GET['school_id'] : null;
    $force = isset($_GET['force']) && $_GET['force'] === '1';

    $opts = [
        'dry_run' => $isDryRun,
        'force' => $force,
        'school_id' => $schoolId,
        'delay_min' => 10,
        'delay_max' => 18,
        'batch_pause' => 45,
        'logger' => function($msg) use (&$streamLogs) {
            $time = date('H:i:s');
            $streamLogs[] = "[{$time}] {$msg}";
        },
    ];

    try {
        if ($action === 'send_all_workflow' || $action === 'dry_run_workflow') {
            $resW = $reportService->sendDailyAttendanceDigestWorkflow($opts);
            $alertMessage = '<div style="background:#dbeafe; border:1px solid #3b82f6; color:#1e40af; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">🏁 Workflow Pengiriman Terpadu Selesai! Pesan laporan akhir telah dikirim ke WhatsApp Admin.</div>';
        } else {
            if ($action === 'send_principal' || $isDryRun) {
                $resP = $reportService->sendPrincipalDailyAttendanceDigest($opts);
            }
            if ($action === 'send_homeroom' || $isDryRun) {
                $resH = $reportService->sendHomeroomDailyAttendanceDigest($opts);
            }
            $alertMessage = '<div style="background:#dbeafe; border:1px solid #3b82f6; color:#1e40af; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">🏁 Proses Pengiriman Selesai! Silakan lihat log detail di bawah.</div>';
        }
    } catch (\Throwable $e) {
        $alertMessage = '<div style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">❌ Error eksekusi: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Diagnostik & Pengendali WhatsApp Anti-Ban - PembdaHUB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 1080px; margin: 30px auto; padding: 0 20px; background: #f8f9fa; color: #333; }
        h1 { color: #1a56db; border-bottom: 3px solid #1a56db; padding-bottom: 10px; margin-bottom: 5px; }
        h2 { color: #047857; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.12); border-radius: 8px; overflow: hidden; font-size: 14px; }
        th { background: #1e40af; color: white; padding: 10px 14px; text-align: left; }
        td { padding: 9px 14px; border-bottom: 1px solid #e5e7eb; }
        tr:last-child td { border-bottom: none; }
        .ok { color: #059669; font-weight: bold; }
        .warn { color: #d97706; font-weight: bold; }
        .error { color: #dc2626; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge-ok { background: #d1fae5; color: #065f46; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .btn { display: inline-block; padding: 9px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px; cursor: pointer; border: none; }
        .btn-green { background: #059669; color: white; }
        .btn-green:hover { background: #047857; }
        .btn-red { background: #dc2626; color: white; }
        .btn-red:hover { background: #b91c1c; }
        .btn-blue { background: #2563eb; color: white; }
        .btn-blue:hover { background: #1d4ed8; }
        .btn-amber { background: #d97706; color: white; }
        .btn-amber:hover { background: #b45309; }
        .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.12); margin: 20px 0; }
        .console-box { background: #0f172a; color: #38bdf8; font-family: monospace; font-size: 13px; padding: 15px; border-radius: 6px; max-height: 350px; overflow-y: auto; line-height: 1.6; }
    </style>
</head>
<body>

<h1>📱 Pengendali WhatsApp Anti-Ban & Diagnostik</h1>
<p style="color: #64748b; margin-top: 0;"><em>Perguruan PEMBDA Nias • Waktu Saat Ini: <?= now()->format('d F Y H:i:s') ?> WIB</em></p>

<?= $alertMessage ?>

<?php if (!empty($streamLogs)): ?>
    <div class="card" style="border-left: 5px solid #0284c7;">
        <h3 style="margin-top: 0; color: #0369a1;">📋 Log Eksekusi Terakhir:</h3>
        <div class="console-box">
            <?php foreach ($streamLogs as $log): ?>
                <div><?= htmlspecialchars($log) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 0. KOTAK NOMOR WHATSAPP ADMIN (PENERIMA BERITA & LAPORAN) -->
<?php
$adminRecipients = $reportService->getAdminRecipients();
$adminNotifyActive = Setting::getValue('wa_notify_admin_digest', true);
$configuredAdminPhone = Setting::getValue('wa_digest_admin_phone') ?: Setting::getValue('wa_admin_phone') ?: '081263582950';
?>
<div class="card" style="border-top: 4px solid #8b5cf6; background: #faf5ff;">
    <h3 style="margin: 0 0 8px 0; color: #6b21a8;">📢 0. WhatsApp Admin (Penerima Berita Mulai & Laporan Selesai)</h3>
    <p style="font-size: 13px; color: #581c87; margin-top: 0;">
        Setiap kali proses pengiriman rekapitulasi dimulai pada pukul <b>08:00 WIB</b>, sistem akan mengirimkan <b>Berita Mulai</b> ke Admin, dan setelah semua rekap selesai terkirim, sistem mengirimkan <b>Laporan Akhir Keterkiriman</b> secara otomatis.
    </p>

    <div style="background: white; border: 1px solid #e9d5ff; border-radius: 6px; padding: 12px 16px; margin: 12px 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <span style="font-size: 13px; color: #475569;">Penerima Terdaftar Saat Ini:</span><br>
                <?php foreach ($adminRecipients as $adPhone => $adName): ?>
                    <strong style="color: #6b21a8; font-size: 14px;">📱 <?= htmlspecialchars($adPhone) ?> (<?= htmlspecialchars($adName) ?>)</strong> &nbsp;
                <?php endforeach; ?>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b;">Status Notifikasi Admin:</span>
                <?php if ($adminNotifyActive): ?>
                    <span class="badge badge-ok">✅ AKTIF</span>
                    <a href="?secret=pembda99&toggle_admin_notify=no" style="font-size: 11px; color: #dc2626; margin-left: 6px; text-decoration: underline;" onclick="return confirm('Nonaktifkan notifikasi WA ke admin?')">Nonaktifkan</a>
                <?php else: ?>
                    <span class="badge badge-error">❌ NONAKTIF</span>
                    <a href="?secret=pembda99&toggle_admin_notify=yes" style="font-size: 11px; color: #059669; margin-left: 6px; text-decoration: underline;">Aktifkan</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Form Ganti Nomor Admin -->
    <form method="POST" action="?secret=pembda99" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-top: 10px;">
        <input type="hidden" name="action" value="save_admin_phone">
        <div>
            <label style="font-size: 12px; font-weight: bold; color: #6b21a8; display: block; margin-bottom: 4px;">Ubah / Atur Nomor WhatsApp Admin:</label>
            <input type="text" name="admin_phone" placeholder="Contoh: 081263xxxxxx" value="<?= htmlspecialchars($configuredAdminPhone) ?>" required style="padding: 7px 12px; border: 1px solid #d8b4fe; border-radius: 6px; font-size: 13px; min-width: 220px;">
        </div>
        <div style="padding-top: 18px;">
            <button type="submit" class="btn" style="background: #7c3aed; color: white;">💾 Simpan Nomor Admin</button>
        </div>
    </form>
</div>

<!-- 1. KOTAK UJI COBA MANDIRI (SINGLE TEST) -->
<div class="card" style="border-top: 4px solid #10b981; background: #f0fdf4;">
    <h3 style="margin: 0 0 8px 0; color: #065f46;">🧪 1. Uji Coba Pengiriman Mandiri (Single Test — 100% Aman)</h3>
    <p style="font-size: 13px; color: #166534; margin-top: 0;">
        Kirimkan 1 pesan sampel langsung ke nomor pribadi Anda untuk memverifikasi teks dan koneksi WhatsApp gateway. <b>Tidak ada risiko ban</b> karena hanya mengirimkan tepat 1 pesan uji coba tanpa menyentuh nomor guru lain.
    </p>
    <form method="POST" action="?secret=pembda99" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 15px;">
        <input type="hidden" name="action" value="single_test">
        <div>
            <label style="font-size: 12px; font-weight: bold; color: #166534; display: block; margin-bottom: 4px;">Nomor WhatsApp Tujuan:</label>
            <input type="text" name="target_phone" placeholder="Contoh: 081263xxxxxx" value="<?= htmlspecialchars($_POST['target_phone'] ?? $configuredAdminPhone) ?>" required style="padding: 8px 12px; border: 1px solid #86efac; border-radius: 6px; font-size: 14px; min-width: 220px;">
        </div>
        <div>
            <label style="font-size: 12px; font-weight: bold; color: #166534; display: block; margin-bottom: 4px;">Tipe Laporan Uji Coba:</label>
            <select name="test_type" style="padding: 8px 12px; border: 1px solid #86efac; border-radius: 6px; font-size: 14px;">
                <option value="admin_start">📢 Berita Mulai ke Admin (Sampel Notifikasi Awal)</option>
                <option value="admin_completed">🏁 Laporan Akhir ke Admin (Sampel Rekap Keterkiriman)</option>
                <option value="principal">🏫 Rekap Kepala Sekolah (1 Sampel Unit)</option>
                <option value="homeroom" selected>👩‍🏫 Rekap Wali Kelas (1 Sampel Kelas)</option>
            </select>
        </div>
        <div style="padding-top: 20px;">
            <button type="submit" class="btn btn-green">🚀 Kirim 1 Pesan Uji Coba Sekarang</button>
        </div>
    </form>
</div>

<!-- 2. PENGIRIMAN BERTAHAP DENGAN JEDA ANTI-BAN -->
<div class="card" style="border-top: 4px solid #3b82f6;">
    <h3 style="margin: 0 0 8px 0; color: #1e40af;">🛡️ 2. Pengiriman Bertahap dengan Protokol Anti-Ban & Alur Terpadu</h3>
    <p style="font-size: 13px; color: #334155; margin-top: 0;">
        Sistem menerapkan <b>Jeda Acak (10–18 detik per pesan)</b>, <b>Jeda Istirahat Antar Unit (45 detik)</b>, dan <b>Kunci Idempotensi Harian</b> (mencegah pesan ganda).
    </p>

    <!-- Tombol Alur Terpadu Penuh -->
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin: 15px 0;">
        <div style="font-weight: bold; color: #1e40af; margin-bottom: 8px; font-size: 14px;">
            ⚡ Eksekusi Alur Otomasi Lengkap (Seperti Cron Job 08:00 WIB):
        </div>
        <div style="font-size: 12px; color: #475569; margin-bottom: 12px;">
            Urutan alur: <b>1. Berita Mulai ke Admin ➔ 2. Rekap 3 Kepala Sekolah ➔ 3. Jeda Santai ➔ 4. Rekap 53 Wali Kelas ➔ 5. Laporan Akhir Keterkiriman ke Admin.</b>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <a href="?secret=pembda99&action=send_all_workflow" class="btn btn-green" onclick="return confirm('Jalankan seluruh alur rekap harian sekarang? Proses akan berjalan bertahap dengan jeda aman dan mengirimkan notifikasi mulai & laporan akhir ke Admin.')">
                🚀 Jalankan Seluruh Alur Sekarang (Live Broadcast)
            </a>
            <a href="?secret=pembda99&action=dry_run_workflow" class="btn btn-amber">
                🔍 Simulasi Alur Penuh (Dry-Run / Tanpa Kirim WA Guru)
            </a>
        </div>
    </div>

    <div style="font-size: 12px; font-weight: bold; color: #64748b; margin-top: 15px; margin-bottom: 6px;">
        Atau Eksekusi Parsial Per Kategori / Unit Sekolah:
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <a href="?secret=pembda99&action=send_principal" class="btn btn-blue" onclick="return confirm('Kirim rekap ke 3 Kepala Sekolah sekarang?')">
            🏫 Kirim Rekap Kepala Sekolah (3 Orang)
        </a>
        <a href="?secret=pembda99&action=send_homeroom&school_id=9" class="btn btn-blue" onclick="return confirm('Kirim rekap Wali Kelas SMPS Pembda 2 (11 kelas)? Proses butuh ~3 menit.')">
            🔵 Kirim Wali Kelas SMPS Pembda 2
        </a>
        <a href="?secret=pembda99&action=send_homeroom&school_id=2" class="btn btn-blue" onclick="return confirm('Kirim rekap Wali Kelas SMAS Pembda 1 (22 kelas)? Proses butuh ~5 menit.')">
            🟡 Kirim Wali Kelas SMAS Pembda 1
        </a>
        <a href="?secret=pembda99&action=send_homeroom&school_id=7" class="btn btn-blue" onclick="return confirm('Kirim rekap Wali Kelas SMK Swasta Pembda Nias (20 kelas)? Proses butuh ~5 menit.')">
            🟠 Kirim Wali Kelas SMK Pembda Nias
        </a>
        <a href="?secret=pembda99&clear_today_locks=yes" class="btn btn-red" onclick="return confirm('Reset kunci pengiriman hari ini agar semua pesan bisa dikirim ulang?')">
            🔄 Reset Kunci Hari Ini
        </a>
    </div>
</div>

<!-- 3. TABEL KEPALA SEKOLAH -->
<h2>🏫 1. Kepala Sekolah (Per Unit Sekolah)</h2>
<?php
$schools = School::schoolsOnly()->with('principal.employee')->get();
$dateToday = date('Y-m-d');
?>
<table>
    <tr>
        <th>Unit Sekolah</th>
        <th>Nama Kepsek</th>
        <th>No. HP</th>
        <th>Status No HP</th>
        <th>Pengiriman Hari Ini (<?= $dateToday ?>)</th>
    </tr>
    <?php
    $kepsekOk = 0; $kepsekFail = 0;
    foreach ($schools as $school):
        $principal = $school->principal;
        $principalName = $principal?->full_name ?? $school->principal_name ?? '-';
        $resolvedPhone = $principal?->phone ?? null;

        if (!$resolvedPhone) {
            $kepsekUser = User::where('role', 'kepala_sekolah')->where('school_id', $school->id)->first();
            if ($kepsekUser && $kepsekUser->teacher) {
                $resolvedPhone = $kepsekUser->teacher->phone;
            }
        }

        $resolvedPhone ? $kepsekOk++ : $kepsekFail++;
        $isSentToday = $reportService->isDigestSentToday('principal', $school->id, $dateToday);
    ?>
    <tr>
        <td><b><?= htmlspecialchars($school->name) ?></b></td>
        <td><?= htmlspecialchars($principalName) ?></td>
        <td><?= htmlspecialchars($resolvedPhone ?: '-') ?></td>
        <td><?= $resolvedPhone ? '<span class="ok">✅ Siap</span>' : '<span class="error">❌ Kosong</span>' ?></td>
        <td>
            <?php if ($isSentToday): ?>
                <span class="badge badge-ok">✅ SUDAH TERKIRIM</span>
            <?php else: ?>
                <span class="badge badge-info">⏳ Belum Terkirim</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- 4. TABEL WALI KELAS -->
<h2>👩‍🏫 2. Wali Kelas (Tahun Pelajaran Aktif)</h2>
<?php
$activeYear = AcademicYear::where('is_active', true)->first();
if (!$activeYear):
    echo '<p class="error">⚠️ Tidak ada Tahun Pelajaran aktif!</p>';
else:
    $schoolIds = School::schoolsOnly()->pluck('id');
    $classrooms = Classroom::where('academic_year_id', $activeYear->id)
        ->whereIn('school_id', $schoolIds)
        ->with(['homeroomTeacher.employee', 'school'])
        ->orderBy('school_id')
        ->orderBy('class_name')
        ->get();
?>
<table>
    <tr>
        <th>Unit</th>
        <th>Kelas</th>
        <th>Wali Kelas</th>
        <th>No. HP</th>
        <th>Status No HP</th>
        <th>Pengiriman Hari Ini</th>
    </tr>
    <?php
    $waliOk = 0; $waliFail = 0; $waliNoTeacher = 0;
    foreach ($classrooms as $class):
        $teacher = $class->homeroomTeacher;
        $schoolName = $class->school?->short_name ?? $class->school?->name ?? '-';

        if (!$teacher) {
            $waliNoTeacher++;
            echo "<tr><td>{$schoolName}</td><td><b>{$class->name}</b></td><td colspan='3'><span class='warn'>⚠️ Belum ada Wali Kelas</span></td><td>-</td></tr>";
            continue;
        }

        $resolvedPhone = $teacher->phone ?? null;
        $resolvedPhone ? $waliOk++ : $waliFail++;
        $isSentToday = $reportService->isDigestSentToday('homeroom', $class->id, $dateToday);
    ?>
    <tr>
        <td><?= htmlspecialchars($schoolName) ?></td>
        <td><b><?= htmlspecialchars($class->name) ?></b></td>
        <td><?= htmlspecialchars($teacher->full_name) ?></td>
        <td><?= htmlspecialchars($resolvedPhone ?: '-') ?></td>
        <td><?= $resolvedPhone ? '<span class="ok">✅ Siap</span>' : '<span class="error">❌ Kosong</span>' ?></td>
        <td>
            <?php if ($isSentToday): ?>
                <span class="badge badge-ok">✅ SUDAH TERKIRIM</span>
            <?php else: ?>
                <span class="badge badge-info">⏳ Belum Terkirim</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- 5. KONFIGURASI WHATSAPP & SAKLAR -->
<h2>⚙️ 3. Saklar Otomatisasi & Proteksi Anti-Ban</h2>
<?php
$sendPrincipal = Setting::getValue('wa_send_principal_attendance', '1');
$sendHomeroom = Setting::getValue('wa_send_homeroom_attendance', '1');
$sendAdminNotify = Setting::getValue('wa_notify_admin_digest', '1');
?>
<div class="card">
    <table style="box-shadow: none; margin: 0;">
        <tr><td>Saklar: Berita Mulai & Laporan Selesai ke Admin</td><td><b><?= $sendAdminNotify ? '<span class="ok">✅ Aktif</span>' : '<span class="warn">⚠️ Nonaktif</span>' ?></b></td></tr>
        <tr><td>Saklar: Rekap Kepala Sekolah</td><td><b><?= $sendPrincipal ? '<span class="ok">✅ Aktif</span>' : '<span class="warn">⚠️ Nonaktif</span>' ?></b></td></tr>
        <tr><td>Saklar: Rekap Wali Kelas</td><td><b><?= $sendHomeroom ? '<span class="ok">✅ Aktif</span>' : '<span class="warn">⚠️ Nonaktif</span>' ?></b></td></tr>
        <tr><td>Jeda Acak Antar Pesan</td><td><b>10 - 18 detik / pesan</b> (Anti-Ban Proteksi)</td></tr>
        <tr><td>Jeda Istirahat Antar Unit Sekolah</td><td><b>45 detik</b></td></tr>
    </table>
    <div style="margin-top: 15px;">
        <?php if (!$sendPrincipal || !$sendHomeroom || !$sendAdminNotify): ?>
            <a href="?secret=pembda99&enable_attendance_wa=yes" class="btn btn-green">⚡ Aktifkan Semua Saklar Otomatisasi</a>
        <?php else: ?>
            <span class="ok">🎉 Seluruh saklar rekap kehadiran WhatsApp AKTIF.</span>
            <a href="?secret=pembda99&disable_attendance_wa=yes" class="btn btn-red" style="font-size: 11px; padding: 5px 10px; margin-left: 15px;" onclick="return confirm('Nonaktifkan saklar otomatisasi?')">Nonaktifkan Rekap</a>
        <?php endif; ?>
    </div>
</div>

<h2>⏱️ 4. Panduan Cron Job Server</h2>
<div style="background:#1e293b; color:#f1f5f9; padding:15px 20px; border-radius:8px; margin:15px 0;">
<?php
    // Deteksi otomatis lingkungan server
    $isHostinger = file_exists('/home/u474310197') || strpos(__DIR__, 'u474310197') !== false;
    $laravelRoot = realpath(__DIR__ . '/../');
    if (!$laravelRoot || !file_exists("{$laravelRoot}/artisan")) {
        $laravelRoot = '/var/www/pembdahub';
    }

    // Deteksi path PHP
    $phpPath = '/usr/bin/php';
    if (file_exists('/usr/bin/php8.3')) {
        $phpPath = '/usr/bin/php8.3';
    } elseif (file_exists('/usr/bin/php8.2')) {
        $phpPath = '/usr/bin/php8.2';
    }

    if ($isHostinger):
?>
    <p style="margin:0 0 10px 0; color:#38bdf8; font-weight:bold;">📋 Salin perintah Cron Job ini ke hPanel Hostinger:</p>
    <code style="display:block; background:#0f172a; padding:10px; border-radius:6px; color:#4ade80; font-size:13px; word-break:break-all;">
        <?= $phpPath ?> /home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/artisan schedule:run >> /dev/null 2>&1
    </code>
    <p style="margin:10px 0 0 0; font-size:12px; color:#94a3b8;">* Hostinger menggunakan PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?> pada path <code><?= $phpPath ?></code>. Cron terjadwal otomatis setiap hari aktif (Senin–Jumat) pukul 08:00 WIB.</p>
<?php else: ?>
    <p style="margin:0 0 10px 0; color:#38bdf8; font-weight:bold;">📋 Server Ubuntu VPS — Perintah Crontab:</p>
    <code style="display:block; background:#0f172a; padding:10px; border-radius:6px; color:#4ade80; font-size:13px; word-break:break-all;">
        * * * * * cd <?= $laravelRoot ?> && <?= $phpPath ?> artisan schedule:run >> /dev/null 2>&1
    </code>
    <p style="margin:10px 0 0 0; font-size:12px; color:#94a3b8;">
        * Jalankan <code>crontab -e</code> lalu tempel perintah di atas. Laravel scheduler akan otomatis mengeksekusi jadwal yang terdaftar di <code>routes/console.php</code>.<br>
        * PHP terdeteksi: <code><?= $phpPath ?></code> (PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?>)<br>
        * Laravel Root: <code><?= $laravelRoot ?></code>
    </p>
<?php endif; ?>
</div>

</body>
</html>
