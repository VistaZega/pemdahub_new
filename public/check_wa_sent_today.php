<?php
/**
 * Diagnostik Log Pengiriman WhatsApp Hari Ini
 * Akses: https://perguruanpembda.com/check_wa_sent_today.php?secret=pembda99
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

use App\Models\EmployeeAttendance;
use App\Models\Employee;

$today = date('Y-m-d');
$logDir = storage_path('logs');
$waLogFile = "{$logDir}/whatsapp-{$today}.log";
$waLogGeneric = "{$logDir}/whatsapp.log";
$laravelLogFile = "{$logDir}/laravel-{$today}.log";
$laravelLogGeneric = "{$logDir}/laravel.log";

$rawLines = [];

if (file_exists($waLogFile)) {
    $rawLines = array_merge($rawLines, file($waLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}
if (file_exists($waLogGeneric)) {
    $rawLines = array_merge($rawLines, file($waLogGeneric, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}
if (file_exists($laravelLogFile)) {
    $rawLines = array_merge($rawLines, file($laravelLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}

$parsedLogs = [];
$totalSent = 0;
$teacherCount = 0;
$adminCount = 0;
$principalCount = 0;
$homeroomCount = 0;
$otherCount = 0;

foreach ($rawLines as $line) {
    if (strpos($line, 'WhatsApp message sent') !== false || strpos($line, 'teacher.attendance') !== false || strpos($line, 'employee.attendance') !== false || strpos($line, 'executive.') !== false) {
        $parsedLogs[] = $line;
        $totalSent++;
        if (strpos($line, 'teacher.attendance') !== false || strpos($line, 'employee.attendance') !== false) {
            $teacherCount++;
        } elseif (strpos($line, '081263582950') !== false || strpos($line, 'LAPORAN AKHIR') !== false || strpos($line, 'PENGIRIMAN REKAP ABSENSI DIMULAI') !== false) {
            $adminCount++;
        } elseif (strpos($line, 'principal_daily_attendance') !== false) {
            $principalCount++;
        } elseif (strpos($line, 'homeroom_daily_attendance') !== false) {
            $homeroomCount++;
        } else {
            $otherCount++;
        }
    }
}

// Cek presensi guru hari ini
$todayEmpAttendances = EmployeeAttendance::where('date', $today)
    ->with('employee')
    ->orderBy('time_in', 'desc')
    ->get();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Pengiriman WhatsApp Hari Ini (<?= $today ?>)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 1000px; margin: 30px auto; padding: 0 20px; background: #f8fafc; color: #1e293b; }
        h1 { color: #1e40af; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .card { background: white; padding: 18px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-val { font-size: 28px; font-weight: bold; margin-top: 5px; }
        .stat-danger { color: #dc2626; }
        .stat-success { color: #16a34a; }
        .stat-primary { color: #2563eb; }
        .stat-warning { color: #d97706; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin: 15px 0; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #1e40af; color: white; font-weight: 600; }
        .console { background: #0f172a; color: #38bdf8; font-family: monospace; font-size: 12px; padding: 15px; border-radius: 6px; max-height: 350px; overflow-y: auto; line-height: 1.5; word-break: break-all; }
    </style>
</head>
<body>

<h1>📊 Rincian Pengiriman WhatsApp Hari Ini (<?= date('d F Y') ?>)</h1>

<div class="grid">
    <div class="card" style="border-top: 4px solid #ef4444;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">👨‍🏫 Notifikasi WA Guru (Tap RFID)</div>
        <div class="stat-val stat-danger"><?= $teacherCount ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">*Sekarang sudah DIMATIKAN TOTAL</div>
    </div>
    <div class="card" style="border-top: 4px solid #3b82f6;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">🏫 Rekap Kepala Sekolah</div>
        <div class="stat-val stat-primary"><?= $principalCount ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Terkirim ke Kepsek</div>
    </div>
    <div class="card" style="border-top: 4px solid #10b981;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">👩‍🏫 Rekap Wali Kelas</div>
        <div class="stat-val stat-success"><?= $homeroomCount ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Terkirim ke Wali Kelas</div>
    </div>
    <div class="card" style="border-top: 4px solid #8b5cf6;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">📢 Notifikasi WhatsApp Admin</div>
        <div class="stat-val" style="color: #7c3aed;"><?= $adminCount ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Uji coba & Laporan Admin</div>
    </div>
</div>

<h2>👨‍🏫 Data Guru / Staf yang Tap Masuk Hari Ini (<?= $todayEmpAttendances->count() ?> Orang)</h2>
<table>
    <tr>
        <th>No</th>
        <th>Nama Pegawai / Guru</th>
        <th>Sekolah</th>
        <th>Waktu Masuk</th>
        <th>Status</th>
        <th>No. HP Terdaftar</th>
    </tr>
    <?php if ($todayEmpAttendances->isEmpty()): ?>
        <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada data tap pegawai hari ini.</td></tr>
    <?php else: ?>
        <?php foreach ($todayEmpAttendances as $i => $att): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><b><?= htmlspecialchars($att->employee->full_name ?? '-') ?></b></td>
            <td><?= htmlspecialchars($att->school_id ?? '-') ?></td>
            <td><?= htmlspecialchars($att->time_in ?? '-') ?> WIB</td>
            <td><span style="color: #16a34a; font-weight: bold;">Hadir</span></td>
            <td><?= htmlspecialchars($att->employee->phone ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<h2>📜 Cuplikan Baris Log WhatsApp Hari Ini (Total: <?= count($parsedLogs) ?> entri)</h2>
<div class="console">
    <?php if (empty($parsedLogs)): ?>
        <div>(Tidak ada log aktivitas WhatsApp yang tercatat hari ini)</div>
    <?php else: ?>
        <?php foreach (array_slice(array_reverse($parsedLogs), 0, 50) as $l): ?>
            <div><?= htmlspecialchars($l) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
