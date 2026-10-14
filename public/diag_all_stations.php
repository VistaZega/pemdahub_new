<?php
/**
 * Live Station Readiness & Cloud OTA Firmware Manager - PembdaHUB
 * URL Utama: https://perguruanpembda.com/stations
 * URL Alternatif: https://perguruanpembda.com/diag_all_stations.php
 */

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
}

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\School;

if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Spesifikasi Resmi 12 Station Fisik PembdaHUB
$stationSpecs = [
    'STATION-SMA-01' => ['unit' => 'SMA', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMA-02' => ['unit' => 'SMA', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMA-03' => ['unit' => 'SMA', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + Buzzer'],
    'STATION-SMK-01' => ['unit' => 'SMK', 'mcu' => 'ESP32 DevKit V1',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + MP3 + 2 LED'],
    'STATION-SMK-02' => ['unit' => 'SMK', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + MP3 + LED'],
    'STATION-SMK-03' => ['unit' => 'SMK', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + Buzzer + 2 LED'],
    'STATION-SMK-04' => ['unit' => 'SMK', 'mcu' => 'ESP32 DevKit V1',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + MP3 + 2 LED'],
    'STATION-SMK-05' => ['unit' => 'SMK', 'mcu' => 'ESP32-C3 SuperMini', 'lcd' => 'LCD 16x2', 'fitur' => 'RFID + MP3 + 2 LED'],
    'STATION-SMK-06' => ['unit' => 'SMK', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMP-01' => ['unit' => 'SMP', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMP-02' => ['unit' => 'SMP', 'mcu' => 'ESP32 DevKit V1',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMP-03' => ['unit' => 'SMP', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
];

$manifestPath = storage_path('app/ota_manifest.json');
$otaFirmwareDir = public_path('ota_firmware');
if (!is_dir($otaFirmwareDir)) {
    @mkdir($otaFirmwareDir, 0775, true);
}

$otaFlashMsg = null;
$otaFlashType = 'success';

// Handle Form Actions (Upload Cloud OTA .bin / Cancel OTA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ota_action'])) {
    $pin = trim($_POST['ota_pin'] ?? '');
    if ($pin !== 'pembda99') {
        $otaFlashMsg = '❌ PIN Keamanan salah! Gunakan PIN standar admin untuk menjadwalkan OTA.';
        $otaFlashType = 'danger';
    } else {
        $manifest = file_exists($manifestPath) ? (json_decode(@file_get_contents($manifestPath), true) ?: []) : [];
        $action = $_POST['ota_action'];

        if ($action === 'upload_bin') {
            $targetDevice = trim($_POST['target_device'] ?? '');
            $versionLabel = trim($_POST['version_label'] ?? 'v2.6-OTA');
            if ($versionLabel === '') $versionLabel = 'v2.6-OTA';

            if (!isset($stationSpecs[$targetDevice])) {
                $otaFlashMsg = '❌ Pilih Target Station yang valid.';
                $otaFlashType = 'danger';
            } elseif (empty($_FILES['firmware_bin']['tmp_name']) || $_FILES['firmware_bin']['error'] !== UPLOAD_ERR_OK) {
                $otaFlashMsg = '❌ Gagal mengunggah file .bin. Pastikan file hasil Export Compiled Binary (.bin) sudah dipilih.';
                $otaFlashType = 'danger';
            } else {
                $origName = $_FILES['firmware_bin']['name'] ?? '';
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if ($ext !== 'bin') {
                    $otaFlashMsg = '❌ File harus berekstensi .bin (dari menu Sketch -> Export Compiled Binary di Arduino IDE).';
                    $otaFlashType = 'danger';
                } else {
                    $destFilename = $targetDevice . '.bin';
                    $destPath = $otaFirmwareDir . DIRECTORY_SEPARATOR . $destFilename;
                    if (@move_uploaded_file($_FILES['firmware_bin']['tmp_name'], $destPath)) {
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'] ?? 'perguruanpembda.com';
                        $binUrl = "{$scheme}://{$host}/ota_firmware/{$destFilename}?v=" . time();

                        $manifest[$targetDevice] = [
                            'pending'    => true,
                            'version'    => $versionLabel,
                            'bin_url'    => $binUrl,
                            'file_size'  => round(filesize($destPath) / 1024, 1) . ' KB',
                            'queued_at'  => date('Y-m-d H:i:s'),
                            'status'     => 'WAITING',
                        ];
                        @file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
                        $otaFlashMsg = "✅ File firmware ({$manifest[$targetDevice]['file_size']}) berhasil diunggah untuk {$targetDevice}! Station akan otomatis mengunduh & memperbarui dirinya saat Heartbeat berikutnya (maksimal 45 detik).";
                        $otaFlashType = 'success';
                    } else {
                        $otaFlashMsg = '❌ Gagal menyimpan file .bin ke folder public/ota_firmware.';
                        $otaFlashType = 'danger';
                    }
                }
            }
        } elseif ($action === 'cancel_ota') {
            $targetDevice = trim($_POST['target_device'] ?? '');
            if (isset($manifest[$targetDevice])) {
                $manifest[$targetDevice]['pending'] = false;
                $manifest[$targetDevice]['status'] = 'CANCELLED';
                @file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
                $otaFlashMsg = "⚠️ Antrean Cloud OTA untuk {$targetDevice} telah dibatalkan.";
                $otaFlashType = 'warning';
            }
        }
    }
}

$otaManifest = file_exists($manifestPath) ? (json_decode(@file_get_contents($manifestPath), true) ?: []) : [];

$today = date('Y-m-d');
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

// 1. Ambil 3 Unit Sekolah Aktif
$schools = School::where('is_active', true)->where('type', '!=', 'yayasan')->get();

$stationsToAudit = [
    'SMA' => ['STATION-SMA-01', 'STATION-SMA-02', 'STATION-SMA-03'],
    'SMK' => ['STATION-SMK-01', 'STATION-SMK-02', 'STATION-SMK-03', 'STATION-SMK-04', 'STATION-SMK-05', 'STATION-SMK-06'],
    'SMP' => ['STATION-SMP-01', 'STATION-SMP-02', 'STATION-SMP-03'],
];

if (!function_exists('formatUptimeSeconds')) {
    function formatUptimeSeconds($sec) {
        if (!$sec || $sec <= 0) return '-';
        $sec = (int)$sec;
        $days = floor($sec / 86400);
        $hours = floor(($sec % 86400) / 3600);
        $mins = floor(($sec % 3600) / 60);
        $secs = $sec % 60;
        if ($days > 0) return "{$days}h {$hours}j {$mins}m";
        if ($hours > 0) return "{$hours}j {$mins}m";
        if ($mins > 0) return "{$mins}m {$secs}d";
        return "{$secs} dtk";
    }
}

if (!function_exists('auditStationStatus')) {
    function auditStationStatus($deviceId, $spec, $today, $sevenDaysAgo, $otaManifest) {
        $hasDevicesTable = Schema::hasTable('devices');
        $deviceRecord = null;
        $liveStatus = 'OFFLINE';
        $liveStatusLabel = '🔴 PADAM / OFFLINE';
        $liveStatusClass = 'badge-danger';
        $lastPingText = 'Belum ada sinyal';
        $lastPingSeconds = null;

        $ssid = null;
        $uptimeSec = null;
        $fwVersion = null;
        $ipAddress = null;
        $rssi = null;
        $freeHeap = null;

        if ($hasDevicesTable) {
            $deviceRecord = DB::table('devices')->where('device_code', $deviceId)->first();
            if ($deviceRecord) {
                $ipAddress = $deviceRecord->ip_address;
                $rssi = $deviceRecord->rssi;
                $freeHeap = $deviceRecord->free_heap;

                if (!empty($deviceRecord->type) && strpos($deviceRecord->type, 'SSID:') !== false) {
                    $parts = explode('|', $deviceRecord->type);
                    foreach ($parts as $part) {
                        if (strpos($part, 'SSID:') === 0) {
                            $ssid = substr($part, 5);
                        } elseif (strpos($part, 'UP:') === 0) {
                            $uptimeSec = (int)substr($part, 3);
                        } elseif (strpos($part, 'FW:') === 0) {
                            $fwVersion = substr($part, 3);
                        }
                    }
                }

                if ($deviceRecord->last_seen_at) {
                    $lastSeenTime = strtotime($deviceRecord->last_seen_at);
                    $lastPingSeconds = max(0, time() - $lastSeenTime);

                    if ($lastPingSeconds <= 90) {
                        $liveStatus = 'ONLINE';
                        $liveStatusLabel = '🟢 SIAP & ONLINE';
                        $liveStatusClass = 'badge-success';
                        $lastPingText = $lastPingSeconds . ' dtk lalu';
                    } elseif ($lastPingSeconds <= 300) {
                        $liveStatus = 'IDLE';
                        $liveStatusLabel = '🟡 SIAGA / IDLE';
                        $liveStatusClass = 'badge-warning';
                        $lastPingText = round($lastPingSeconds / 60, 1) . ' mnt lalu';
                    } else {
                        $liveStatus = 'OFFLINE';
                        $liveStatusLabel = '🔴 PADAM / OFFLINE';
                        $liveStatusClass = 'badge-danger';
                        $lastPingText = date('d/m H:i', $lastSeenTime);
                    }
                }
            }
        }

        // Scan Hari Ini
        $todaySiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', $today)->count();
        $todayGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', $today)->count();
        $todayTotal = $todaySiswa + $todayGuru;

        // Scan 7 Hari Terakhir
        $weekSiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', '>=', $sevenDaysAgo)->count();
        $weekGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', '>=', $sevenDaysAgo)->count();
        $weekTotal = $weekSiswa + $weekGuru;

        // Scan Terakhir
        $lastScanSiswa = DB::table('attendances')->where('device_id', $deviceId)->orderByDesc('date')->orderByDesc('time_in')->select('date', 'time_in', 'recorded_via')->first();
        $lastScanGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->orderByDesc('date')->orderByDesc('time_in')->select('date', 'time_in', 'recorded_via')->first();

        $lastTimestamp = null;
        if ($lastScanSiswa && $lastScanGuru) {
            $timeS = $lastScanSiswa->date . ' ' . $lastScanSiswa->time_in;
            $timeG = $lastScanGuru->date . ' ' . $lastScanGuru->time_in;
            $lastTimestamp = ($timeS >= $timeG) ? $timeS : $timeG;
        } elseif ($lastScanSiswa) {
            $lastTimestamp = $lastScanSiswa->date . ' ' . $lastScanSiswa->time_in;
        } elseif ($lastScanGuru) {
            $lastTimestamp = $lastScanGuru->date . ' ' . $lastScanGuru->time_in;
        }

        return [
            'device_id'         => $deviceId,
            'spec'              => $spec,
            'live_status'       => $liveStatus,
            'live_status_label' => $liveStatusLabel,
            'live_status_class' => $liveStatusClass,
            'last_ping_text'    => $lastPingText,
            'last_ping_seconds' => $lastPingSeconds,
            'ssid'              => $ssid,
            'fw_version'        => $fwVersion,
            'ota_info'          => $otaManifest[$deviceId] ?? null,
            'ip_address'        => $ipAddress,
            'rssi'              => $rssi,
            'free_heap'         => $freeHeap,
            'uptime_sec'        => $uptimeSec,
            'today_total'       => $todayTotal,
            'today_siswa'       => $todaySiswa,
            'today_guru'        => $todayGuru,
            'week_total'        => $weekTotal,
            'last_scan'         => $lastTimestamp,
        ];
    }
}

$auditResults = [];
$totalStations = 0;
$liveOnline = 0;
$liveIdle = 0;
$liveOffline = 0;
$lanConnected = 0;
$totalScansToday = 0;

foreach ($stationsToAudit as $unit => $devList) {
    $auditResults[$unit] = [];
    foreach ($devList as $dev) {
        $spec = $stationSpecs[$dev] ?? ['unit' => $unit, 'mcu' => 'ESP', 'lcd' => 'LCD', 'fitur' => 'RFID+QR'];
        $res = auditStationStatus($dev, $spec, $today, $sevenDaysAgo, $otaManifest);
        $auditResults[$unit][$dev] = $res;

        $totalStations++;
        $totalScansToday += $res['today_total'];
        if ($res['live_status'] === 'ONLINE') {
            $liveOnline++;
            if ($res['ssid'] === 'PembdaLINK') {
                $lanConnected++;
            }
        } elseif ($res['live_status'] === 'IDLE') {
            $liveIdle++;
        } else {
            $liveOffline++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Kesiapan &amp; OTA Station Absensi | PembdaHUB</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0b1120; color: #f1f5f9; margin: 0; padding: 20px; line-height: 1.5; font-size: 13px; }
        .container { max-width: 1420px; margin: 0 auto; }
        .top-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        h1 { color: #38bdf8; font-size: 22px; margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px; }
        .subtitle { color: #94a3b8; font-size: 13px; margin: 0; }
        .meta-bar { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .card { background: #131c31; border: 1px solid #1e293b; border-radius: 10px; padding: 16px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.35); overflow-x: auto; }
        .unit-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px; }
        .unit-title { font-size: 16px; font-weight: 800; letter-spacing: 0.02em; }
        .title-smp { color: #10b981; }
        .title-sma { color: #38bdf8; }
        .title-smk { color: #a855f7; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 12.5px; min-width: 1020px; }
        th, td { border: 1px solid #26334d; padding: 10px 12px; text-align: left; vertical-align: middle; }
        th { background: #1e293b; color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
        tr:nth-child(even) { background: rgba(255,255,255,0.015); }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .badge-success { background: #064e3b; color: #34d399; border: 1px solid #059669; }
        .badge-warning { background: #78350f; color: #fbbf24; border: 1px solid #b45309; }
        .badge-danger { background: #7f1d1d; color: #f87171; border: 1px solid #dc2626; }
        .badge-info { background: #0c4a6e; color: #38bdf8; border: 1px solid #0284c7; }
        .badge-lan { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid #10b981; }
        .badge-failover { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid #f59e0b; }
        .badge-hw { background: #1e293b; color: #cbd5e1; border: 1px solid #334155; font-weight: 600; font-size: 10.5px; padding: 2px 7px; }
        .pulse-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #34d399; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); } }
        .summary-box { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 22px; }
        .sum-card { background: #1e293b; border-radius: 10px; padding: 14px 16px; border: 1px solid #334155; }
        .sum-title { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em; }
        .sum-val { font-size: 24px; font-weight: 800; margin-top: 4px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .sum-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
        .btn { background: #0284c7; color: #fff; border: 0; padding: 8px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn:hover { background: #0369a1; }
        .btn-secondary { background: #334155; color: #e2e8f0; }
        .btn-secondary:hover { background: #475569; }
        .btn-purple { background: #7e22ce; color: #fff; }
        .btn-purple:hover { background: #6b21a8; }
        .btn-danger-sm { background: #991b1b; color: #fecaca; border: 1px solid #dc2626; padding: 3px 8px; border-radius: 4px; font-size: 10.5px; cursor: pointer; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .subtext { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .ota-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 18px; align-items: start; }
        @media (max-width: 900px) { .ota-grid { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 12px; }
        .form-label { display: block; font-size: 11.5px; font-weight: 700; color: #cbd5e1; margin-bottom: 5px; }
        .form-input, .form-select { width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px 11px; border-radius: 6px; font-size: 12.5px; }
        .alert-box { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; }
        .alert-success { background: #064e3b; color: #6ee7b7; border: 1px solid #059669; }
        .alert-danger { background: #7f1d1d; color: #fca5a5; border: 1px solid #dc2626; }
        .alert-warning { background: #78350f; color: #fcd34d; border: 1px solid #d97706; }
    </style>
</head>
<body>
<div class="container">
    <div class="top-header">
        <div>
            <h1><span class="pulse-dot"></span> STATUS KESIAPAN &amp; OTA MANAGER STATION ABSENSI</h1>
            <p class="subtitle">Pemantauan Real-Time 12 Station IoT (SMA, SMK, SMP) — Prioritas LAN <strong>PembdaLINK</strong> &amp; Dual-OTA (WiFi LAN + Cloud)</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="#ota-manager" class="btn btn-purple">🚀 Tool Update Firmware OTA</a>
            <a href="/display" class="btn btn-secondary">📺 Layar Display</a>
            <button onclick="location.href='/stations'" class="btn">🔄 Refresh Sekarang</button>
        </div>
    </div>

    <?php if ($otaFlashMsg): ?>
        <div class="alert-box alert-<?= $otaFlashType ?>">
            <?= htmlspecialchars($otaFlashMsg) ?>
        </div>
    <?php endif; ?>

    <div class="meta-bar">
        <div>
            Waktu Server: <strong class="mono"><?= date('Y-m-d H:i:s') ?> WIB</strong> &nbsp;|&nbsp;
            Auto-Refresh: <strong id="countdown" class="mono">15</strong> detik <span style="color:#64748b;font-size:11px;">(Otomatis jeda saat mengisi form OTA)</span> &nbsp;|&nbsp;
            Heartbeat: <strong>45 Detik</strong>
        </div>
        <div>
            <span class="badge badge-info">URL Resmi: perguruanpembda.com/stations</span>
        </div>
    </div>

    <!-- RINGKASAN METRIK LIVE DETIK INI -->
    <div class="summary-box">
        <div class="sum-card" style="border-left: 4px solid #38bdf8;">
            <div class="sum-title">Total Station Aktif</div>
            <div class="sum-val" style="color: #38bdf8;"><?= $totalStations ?> Unit</div>
            <div class="sum-sub">3 SMA • 6 SMK • 3 SMP</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #34d399;">
            <div class="sum-title">SIAP &amp; ONLINE (LIVE)</div>
            <div class="sum-val" style="color: #34d399;"><?= $liveOnline ?> Station</div>
            <div class="sum-sub"><?= $liveIdle > 0 ? "{$liveIdle} Station Siaga/Idle" : "Merespons < 90 detik terakhir" ?></div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #10b981;">
            <div class="sum-title">Terhubung LAN Utama</div>
            <div class="sum-val" style="color: #10b981;"><?= $lanConnected ?> Station</div>
            <div class="sum-sub">SSID: PembdaLINK (Fast Local API)</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #f87171;">
            <div class="sum-title">PADAM / OFFLINE</div>
            <div class="sum-val" style="color: #f87171;"><?= $liveOffline ?> Station</div>
            <div class="sum-sub">Adaptor mati / belum dinyalakan</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #a855f7;">
            <div class="sum-title">Total Tap/Scan Hari Ini</div>
            <div class="sum-val" style="color: #c084fc;"><?= $totalScansToday ?> Scan</div>
            <div class="sum-sub">Tanggal: <?= date('d/m/Y') ?></div>
        </div>
    </div>

    <!-- TABEL MONITOR LIVE PER UNIT -->
    <?php foreach (['SMA' => 'title-sma', 'SMK' => 'title-smk', 'SMP' => 'title-smp'] as $unit => $titleClass):
        $unitDevs = $auditResults[$unit] ?? [];
        $schoolModel = $schools->where('type', $unit)->first();
        $unitOnlineCount = collect($unitDevs)->where('live_status', 'ONLINE')->count();
    ?>
    <div class="card">
        <div class="unit-header">
            <div>
                <span class="unit-title <?= $titleClass ?>">UNIT <?= $unit ?> — <?= $schoolModel ? htmlspecialchars($schoolModel->name) : '' ?></span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="badge <?= $unitOnlineCount > 0 ? 'badge-success' : 'badge-danger' ?>">
                    <?= $unitOnlineCount ?> / <?= count($unitDevs) ?> Online
                </span>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 150px;">Device ID</th>
                    <th style="width: 195px;">Spesifikasi Hardware</th>
                    <th style="width: 155px;">Status Kesiapan</th>
                    <th style="width: 185px;">WiFi SSID &amp; Jaringan</th>
                    <th style="width: 160px;">IP Lokal &amp; Sinyal</th>
                    <th style="width: 140px;">RAM &amp; Uptime</th>
                    <th style="width: 175px;">Versi FW &amp; Status OTA</th>
                    <th>Scan Hari Ini &amp; Terakhir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unitDevs as $devId => $row):
                    $spec = $row['spec'];
                    $isOnline = ($row['live_status'] === 'ONLINE');
                    $isIdle = ($row['live_status'] === 'IDLE');
                    $rssiVal = $row['rssi'];
                    $rssiColor = '#94a3b8';
                    $rssiQuality = '';
                    if ($rssiVal !== null) {
                        if ($rssiVal >= -67) { $rssiColor = '#34d399'; $rssiQuality = 'Sangat Kuat'; }
                        elseif ($rssiVal >= -78) { $rssiColor = '#fbbf24'; $rssiQuality = 'Stabil'; }
                        else { $rssiColor = '#f87171'; $rssiQuality = 'Lemah'; }
                    }
                    $heapKb = $row['free_heap'] ? round($row['free_heap'] / 1024, 1) . ' KB' : '-';
                    $uptimeStr = formatUptimeSeconds($row['uptime_sec']);
                    $otaInfo = $row['ota_info'];
                ?>
                <tr <?= $isOnline ? 'style="background:rgba(52, 211, 153, 0.07);"' : ($isIdle ? 'style="background:rgba(251, 191, 36, 0.05);"' : '') ?>>
                    <td>
                        <strong class="mono" style="font-size: 13px; color: #f8fafc;"><?= htmlspecialchars($devId) ?></strong>
                        <div class="subtext">Ping: <?= htmlspecialchars($row['last_ping_text']) ?></div>
                    </td>
                    <td>
                        <div><span class="badge badge-hw"><?= htmlspecialchars($spec['mcu']) ?></span></div>
                        <div class="subtext" style="margin-top: 4px;">
                            <strong><?= htmlspecialchars($spec['lcd']) ?></strong> • <?= htmlspecialchars($spec['fitur']) ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $row['live_status_class'] ?>">
                            <?= $row['live_status_label'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($row['ssid'])): ?>
                            <?php if ($row['ssid'] === 'PembdaLINK'): ?>
                                <span class="badge badge-lan">⚡ LAN: PembdaLINK</span>
                                <div class="subtext" style="color:#34d399;">Prioritas Utama (Cepat)</div>
                            <?php else: ?>
                                <span class="badge badge-failover">🌐 <?= htmlspecialchars($row['ssid']) ?></span>
                                <div class="subtext" style="color:#fbbf24;">Mode Cadangan (Failover)</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#64748b;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row['ip_address'])): ?>
                            <div class="mono" style="color: #e2e8f0; font-weight: 600;"><?= htmlspecialchars($row['ip_address']) ?></div>
                        <?php else: ?>
                            <div style="color: #64748b;">-</div>
                        <?php endif; ?>
                        <?php if ($rssiVal !== null): ?>
                            <div class="subtext" style="color: <?= $rssiColor ?>; font-weight: 600;">
                                📶 <?= (int)$rssiVal ?> dBm (<?= $rssiQuality ?>)
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="mono" style="color: #cbd5e1;">RAM: <?= $heapKb ?></div>
                        <div class="subtext">Up: <strong class="mono"><?= $uptimeStr ?></strong></div>
                    </td>
                    <td>
                        <div>
                            <span class="badge badge-hw mono">
                                FW: <?= !empty($row['fw_version']) ? htmlspecialchars($row['fw_version']) : 'v2.5 (Pre-OTA)' ?>
                            </span>
                        </div>
                        <?php if (!empty($otaInfo['pending'])): ?>
                            <div style="margin-top: 5px;">
                                <span class="badge badge-warning">⏳ Antrean OTA: <?= htmlspecialchars($otaInfo['version'] ?? '') ?></span>
                                <form method="POST" action="/stations" style="display:inline; margin-left: 4px;">
                                    <input type="hidden" name="ota_action" value="cancel_ota">
                                    <input type="hidden" name="target_device" value="<?= htmlspecialchars($devId) ?>">
                                    <input type="hidden" name="ota_pin" value="pembda99">
                                    <button type="submit" class="btn-danger-sm" title="Batalkan jadwal OTA">✕ Batal</button>
                                </form>
                            </div>
                        <?php elseif (!empty($otaInfo['status']) && $otaInfo['status'] === 'SENT_TO_DEVICE'): ?>
                            <div class="subtext" style="color:#38bdf8; font-weight:600; margin-top:4px;">
                                🚀 Mengunduh &amp; Flash OTA...
                            </div>
                        <?php elseif (!empty($otaInfo['status']) && $otaInfo['status'] === 'COMPLETED'): ?>
                            <div class="subtext" style="color:#34d399; margin-top:4px;">
                                ✅ OTA Sukses (<?= htmlspecialchars($otaInfo['completed_at'] ?? '') ?>)
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['today_total'] > 0): ?>
                            <strong style="color:#34d399; font-size: 13px;">✅ <?= $row['today_total'] ?> Scan Hari Ini</strong>
                        <?php else: ?>
                            <span style="color:#64748b;">0 Scan Hari Ini</span>
                        <?php endif; ?>
                        <div class="subtext">
                            Terakhir: <span class="mono"><?= $row['last_scan'] ? htmlspecialchars($row['last_scan']) : 'Belum ada' ?></span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- PUSAT KENDALI OTA UPDATE FIRMWARE (CLOUD & WIFI LAN) -->
    <div id="ota-manager" class="card" style="border: 2px solid #a855f7; background: linear-gradient(180deg, #161f38 0%, #131c31 100%);">
        <div class="unit-header" style="border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 16px;">
            <div>
                <span class="unit-title" style="color: #c084fc; font-size: 17px;">🚀 TOOL UPDATE FIRMWARE TANPA KABEL (CLOUD OTA &amp; WIFI LAN OTA)</span>
                <div class="subtext">Setelah station di-flash 1 kali via kabel USB dengan firmware v2.6-OTA, update selanjutnya cukup lewat halaman ini atau WiFi Arduino IDE!</div>
            </div>
            <div>
                <span class="badge badge-info">Dual-OTA Ready (ESP8266 &amp; ESP32)</span>
            </div>
        </div>

        <div class="ota-grid">
            <!-- FORM UPLOAD CLOUD OTA (.BIN) -->
            <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 16px;">
                <h3 style="margin: 0 0 12px 0; color: #38bdf8; font-size: 14px;">☁️ Metode 1: Upload File Firmware (.bin) via Cloud Browser</h3>
                <form method="POST" action="/stations" enctype="multipart/form-data">
                    <input type="hidden" name="ota_action" value="upload_bin">
                    <div class="form-group">
                        <label class="form-label">1. Pilih Target Station yang Akan Di-Update:</label>
                        <select name="target_device" class="form-select" required onfocus="pauseRefresh()" onchange="pauseRefresh()">
                            <option value="">-- Pilih Station --</option>
                            <?php foreach ($stationSpecs as $devCode => $sp): ?>
                                <option value="<?= htmlspecialchars($devCode) ?>">
                                    <?= htmlspecialchars("{$devCode} — Unit {$sp['unit']} ({$sp['mcu']} | {$sp['lcd']})") ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label">2. Label Versi Firmware Baru:</label>
                            <input type="text" name="version_label" class="form-input" value="v2.6.1-OTA" placeholder="Contoh: v2.6.1-OTA" onfocus="pauseRefresh()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">3. PIN Keamanan Admin:</label>
                            <input type="password" name="ota_pin" class="form-input" placeholder="Masukkan PIN (pembda99)" required onfocus="pauseRefresh()">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">4. Pilih File Hasil Compile Arduino IDE (<code>.bin</code>):</label>
                        <input type="file" name="firmware_bin" accept=".bin" class="form-input" required onchange="pauseRefresh()">
                        <div class="subtext">Di Arduino IDE: Klik menu <strong>Sketch &rarr; Export Compiled Binary</strong> (<code>Ctrl+Alt+S</code>), lalu pilih file <code>.bin</code> yang muncul di dalam folder sketch.</div>
                    </div>
                    <button type="submit" class="btn btn-purple" style="width: 100%; justify-content: center; padding: 10px;">
                        🚀 Unggah &amp; Jadwalkan Cloud OTA Update Sekarang
                    </button>
                </form>
            </div>

            <!-- PANDUAN PRAKTIS DUAL-OTA -->
            <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 16px;">
                <h3 style="margin: 0 0 10px 0; color: #34d399; font-size: 14px;">📡 Metode 2: Update Langsung dari Arduino IDE via WiFi LAN (Tanpa Upload File)</h3>
                <ol style="margin: 0 0 14px 0; padding-left: 18px; color: #cbd5e1; line-height: 1.7; font-size: 12.5px;">
                    <li>Pastikan Laptop Bapak terhubung ke WiFi yang sama dengan Station (misal <strong><code>PembdaLINK</code></strong>).</li>
                    <li>Buka file <code>Station_XXX_0X.ino</code> di <strong>Arduino IDE</strong>.</li>
                    <li>Klik menu <strong>Tools &rarr; Port</strong>, maka di bagian <strong>Network Ports</strong> akan muncul:<br>
                        <code style="color:#38bdf8;">STATION-SMK-06 at 192.168.x.x (ESP8266/ESP32)</code></li>
                    <li>Pilih port jaringan tersebut, lalu klik tombol <strong>Upload (&rarr;)</strong> seperti biasa.</li>
                    <li>Jika diminta password OTA, masukkan: <strong class="mono" style="color:#fbbf24;">pembda99</strong>. Layar LCD station akan menampilkan persentase <em>Progress 0% - 100%</em> lalu restart otomatis!</li>
                </ol>

                <div style="border-top: 1px solid #1e293b; padding-top: 10px; font-size: 12px; color: #94a3b8;">
                    <strong style="color: #e2e8f0;">💡 Bagaimana Cara Kerja Cloud OTA (Metode 1)?</strong><br>
                    Saat Bapak mengunggah file <code>.bin</code> di sebelah kiri, server menyimpannya di antrean OTA. Begitu station mengirim sinyal <em>Heartbeat</em> (setiap 45 detik), server memerintahkan station mengunduh file <code>.bin</code> tersebut secara otomatis, meng-install firmware baru, dan melakukan restart sendiri.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let timeLeft = 15;
    let isRefreshPaused = false;
    const countdownEl = document.getElementById('countdown');

    function pauseRefresh() {
        isRefreshPaused = true;
        if (countdownEl) countdownEl.innerText = 'JEDA';
    }

    setInterval(() => {
        if (isRefreshPaused) return;
        timeLeft--;
        if (countdownEl) countdownEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            window.location.href = '/stations';
        }
    }, 1000);
</script>
</body>
</html>
