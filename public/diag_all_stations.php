<?php
/**
 * Live Station Readiness & Status Dashboard - PembdaHUB
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

$today = date('Y-m-d');
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

// 1. Ambil 3 Unit Sekolah Aktif
$schools = School::where('is_active', true)->where('type', '!=', 'yayasan')->get();

// 2. Spesifikasi Resmi 12 Station Fisik PembdaHUB
$stationSpecs = [
    'STATION-SMA-01' => ['unit' => 'SMA', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMA-02' => ['unit' => 'SMA', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMA-03' => ['unit' => 'SMA', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + Buzzer'],
    'STATION-SMK-01' => ['unit' => 'SMK', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMK-02' => ['unit' => 'SMK', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMK-03' => ['unit' => 'SMK', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + QR + Buzzer'],
    'STATION-SMK-04' => ['unit' => 'SMK', 'mcu' => 'ESP32 DevKit V1',    'lcd' => 'LCD 16x2', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMK-05' => ['unit' => 'SMK', 'mcu' => 'ESP32-C3 SuperMini', 'lcd' => 'LCD 16x2', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMK-06' => ['unit' => 'SMK', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMP-01' => ['unit' => 'SMP', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMP-02' => ['unit' => 'SMP', 'mcu' => 'ESP32 DevKit V1',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
    'STATION-SMP-03' => ['unit' => 'SMP', 'mcu' => 'NodeMCU ESP8266',    'lcd' => 'LCD 20x4', 'fitur' => 'RFID + QR + MP3'],
];

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
    function auditStationStatus($deviceId, $spec, $today, $sevenDaysAgo) {
        $hasDevicesTable = Schema::hasTable('devices');
        $deviceRecord = null;
        $liveStatus = 'OFFLINE';
        $liveStatusLabel = '🔴 PADAM / OFFLINE';
        $liveStatusClass = 'badge-danger';
        $lastPingText = 'Belum ada sinyal';
        $lastPingSeconds = null;

        $ssid = null;
        $uptimeSec = null;
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
        $res = auditStationStatus($dev, $spec, $today, $sevenDaysAgo);
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
    <title>Status Kesiapan Station Absensi | PembdaHUB</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0b1120; color: #f1f5f9; margin: 0; padding: 20px; line-height: 1.5; font-size: 13px; }
        .container { max-width: 1400px; margin: 0 auto; }
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
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 12.5px; min-width: 960px; }
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
        .btn { background: #0284c7; color: #fff; border: 0; padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn:hover { background: #0369a1; }
        .btn-secondary { background: #334155; color: #e2e8f0; }
        .btn-secondary:hover { background: #475569; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .subtext { font-size: 11px; color: #94a3b8; margin-top: 2px; }
    </style>
</head>
<body>
<div class="container">
    <div class="top-header">
        <div>
            <h1><span class="pulse-dot"></span> STATUS KESIAPAN STATION ABSENSI (LIVE MONITOR)</h1>
            <p class="subtitle">Pemantauan Real-Time 12 Station IoT (SMA, SMK, SMP) — Prioritas LAN <strong>PembdaLINK</strong> &amp; Heartbeat Otomatis</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/display" class="btn btn-secondary">📺 Layar Display Kehadiran</a>
            <button onclick="location.reload()" class="btn">🔄 Refresh Sekarang</button>
        </div>
    </div>

    <div class="meta-bar">
        <div>
            Waktu Server: <strong class="mono"><?= date('Y-m-d H:i:s') ?> WIB</strong> &nbsp;|&nbsp;
            Auto-Refresh: <strong id="countdown" class="mono">10</strong> detik &nbsp;|&nbsp;
            Interval Heartbeat Station: <strong>45 Detik</strong> &nbsp;|&nbsp;
            Auto-Switchback LAN: <strong>90 Detik</strong>
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
            <div class="sum-sub">Adaptor mati / di luar jangkauan</div>
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
                    <th style="width: 155px;">Device ID</th>
                    <th style="width: 205px;">Spesifikasi Hardware</th>
                    <th style="width: 165px;">Status Kesiapan</th>
                    <th style="width: 195px;">WiFi SSID &amp; Mode Jaringan</th>
                    <th style="width: 170px;">IP Lokal &amp; Sinyal</th>
                    <th style="width: 150px;">RAM &amp; Uptime</th>
                    <th style="width: 130px;">Scan Hari Ini</th>
                    <th>Tap / Scan Terakhir</th>
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
                        <?php if ($row['today_total'] > 0): ?>
                            <strong style="color:#34d399; font-size: 13px;">✅ <?= $row['today_total'] ?> Scan</strong>
                            <div class="subtext"><?= $row['today_siswa'] ?> Siswa • <?= $row['today_guru'] ?> Guru</div>
                        <?php else: ?>
                            <span style="color:#64748b;">0 Scan</span>
                            <div class="subtext">7 hr: <?= $row['week_total'] ?> scan</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['last_scan']): ?>
                            <span class="mono" style="color:#e2e8f0;"><?= htmlspecialchars($row['last_scan']) ?></span>
                        <?php else: ?>
                            <span style="color:#64748b;">Belum ada</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- INFORMASI ARSITEKTUR TELEMETRI & PRIORITAS JARINGAN -->
    <div class="card" style="border: 1px solid #0284c7;">
        <h3 style="color: #38bdf8; margin-top: 0; margin-bottom: 8px;">⚡ Standar Kesiapan &amp; Prioritas Jaringan Station PembdaHUB</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; font-size: 12.5px; color: #cbd5e1;">
            <div>
                <strong style="color: #34d399;">1. Prioritas Mutlak LAN (PembdaLINK):</strong><br>
                Saat dinyalakan, seluruh station mencoba terhubung ke <code>PembdaLINK</code> terlebih dahulu selama 6 detik. Jika sedang berada di WiFi cadangan (<code>PembdaNET</code>, <code>PembdaSTARGATE</code>, <code>VISTAFAMILY</code>, <code>Black_Hole</code>), station otomatis memindai ulang setiap 90 detik dan pindah kembali ke <code>PembdaLINK</code>.
            </div>
            <div>
                <strong style="color: #38bdf8;">2. Dual-Heartbeat (Lokal + Cloud):</strong><br>
                Setiap 45 detik saat idle (dan sesaat setelah boot), setiap station mengirim paket telemetri lengkap (<code>device_id</code>, <code>ssid</code>, <code>ip</code>, <code>rssi</code>, <code>free_heap</code>, <code>uptime</code>) ke Server Lokal maupun Cloud sehingga halaman <code>/stations</code> ini selalu akurat.
            </div>
            <div>
                <strong style="color: #c084fc;">3. Indikator Kesiapan Fisik:</strong><br>
                • <strong>🟢 SIAP &amp; ONLINE:</strong> Aktif mengirim heartbeat &lt; 90 detik lalu.<br>
                • <strong>🟡 SIAGA / IDLE:</strong> Terakhir terlihat 1,5 – 5 menit lalu.<br>
                • <strong>🔴 PADAM / OFFLINE:</strong> Tidak ada sinyal &gt; 5 menit (adaptor dicabut / WiFi putus).
            </div>
        </div>
    </div>
</div>

<script>
    let timeLeft = 10;
    const countdownEl = document.getElementById('countdown');
    setInterval(() => {
        timeLeft--;
        if (countdownEl) countdownEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            location.reload();
        }
    }, 1000);
</script>
</body>
</html>
