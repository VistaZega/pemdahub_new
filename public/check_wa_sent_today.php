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
$teacherSentCount = 0;
$adminSentCount = 0;
$otherSentCount = 0;
$sentDetails = [];

// Cache daftar nomor guru/pegawai untuk pencocokan cepat
$employeePhones = Employee::whereNotNull('phone')->pluck('full_name', 'phone')->toArray();
// Tambahkan versi dengan prefix 62 dan 0
$normalizedEmployeeMap = [];
foreach ($employeePhones as $ph => $nm) {
    $clean = preg_replace('/[^0-9]/', '', (string)$ph);
    if ($clean) {
        $normalizedEmployeeMap[$clean] = $nm;
        if (str_starts_with($clean, '0')) {
            $normalizedEmployeeMap['62' . substr($clean, 1)] = $nm;
        } elseif (str_starts_with($clean, '62')) {
            $normalizedEmployeeMap['0' . substr($clean, 2)] = $nm;
        }
    }
}

foreach ($rawLines as $line) {
    if (strpos($line, 'WhatsApp message sent') !== false) {
        $totalSent++;
        $phone = '-';
        $status = 'unknown';
        $time = '-';

        // Extract timestamp
        if (preg_match('/^\[([^\]]+)\]/', $line, $mt)) {
            $time = $mt[1];
        }

        // Extract phone
        if (preg_match('/"phone":"([^"]+)"/', $line, $mp)) {
            $phone = $mp[1];
        }

        // Extract status
        if (preg_match('/"status":"([^"]+)"/', $line, $ms)) {
            $status = $ms[1];
        }

        $recipientName = $normalizedEmployeeMap[$phone] ?? null;

        $isAdmin = in_array($phone, ['081263582950', '6281263582950', '082168532567', '6282168532567', '082325756228', '6282325756228']);

        if ($isAdmin) {
            $adminSentCount++;
            $category = 'Admin PembdaHUB';
            $recipientName = $recipientName ?: 'Admin';
        } elseif ($recipientName) {
            $teacherSentCount++;
            $category = 'Guru / Pegawai (Tap RFID)';
        } else {
            $otherSentCount++;
            $category = 'Wali Murid / Umum';
        }

        $sentDetails[] = [
            'time' => $time,
            'phone' => $phone,
            'recipient' => $recipientName ?: '(Tidak Dikenal / Umum)',
            'category' => $category,
            'status' => $status,
            'raw' => $line,
        ];
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
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">👨‍🏫 Notifikasi WA Guru (Tap Masuk)</div>
        <div class="stat-val stat-danger"><?= $teacherSentCount ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">*Sekarang sudah DIMATIKAN TOTAL</div>
    </div>
    <div class="card" style="border-top: 4px solid #8b5cf6;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">📢 Notifikasi WhatsApp Admin</div>
        <div class="stat-val" style="color: #7c3aed;"><?= $adminSentCount ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Uji coba & Laporan Admin</div>
    </div>
    <div class="card" style="border-top: 4px solid #0284c7;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">📱 Pesan Lainnya / Umum</div>
        <div class="stat-val stat-primary"><?= $otherSentCount ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Sistem / Verifikasi</div>
    </div>
    <div class="card" style="border-top: 4px solid #10b981;">
        <div style="font-size: 13px; color: #64748b; font-weight: 600;">📈 Total Seluruh Pesan Terkirim</div>
        <div class="stat-val stat-success"><?= $totalSent ?> Pesan</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Hari Ini via Gateway WhatsApp</div>
    </div>
</div>

<h2>📋 Rincian Pesan WhatsApp yang Terkirim Hari Ini (<?= count($sentDetails) ?> Pesan)</h2>
<table>
    <tr>
        <th>No</th>
        <th>Waktu Kirim</th>
        <th>Nomor WhatsApp</th>
        <th>Nama Penerima</th>
        <th>Kategori</th>
        <th>Status Gateway</th>
    </tr>
    <?php if (empty($sentDetails)): ?>
        <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">Tidak ada riwayat pengiriman WhatsApp tercatat hari ini.</td></tr>
    <?php else: ?>
        <?php foreach (array_reverse($sentDetails) as $idx => $item): ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td><?= htmlspecialchars($item['time']) ?></td>
            <td><b><?= htmlspecialchars($item['phone']) ?></b></td>
            <td><?= htmlspecialchars($item['recipient']) ?></td>
            <td>
                <?php if ($item['category'] === 'Admin PembdaHUB'): ?>
                    <span style="background: #ede9fe; color: #6b21a8; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">Admin</span>
                <?php elseif (str_contains($item['category'], 'Guru')): ?>
                    <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">Guru (Tap Masuk)</span>
                <?php else: ?>
                    <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">Lainnya</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($item['status'] === 'success'): ?>
                    <span style="color: #16a34a; font-weight: bold;">✅ Sukses</span>
                <?php else: ?>
                    <span style="color: #dc2626; font-weight: bold;">❌ <?= htmlspecialchars($item['status']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

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

</body>
</html>
