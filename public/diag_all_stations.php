<?php
/**
 * Diag All Stations (SMP, SMA, SMK) - Real-Time Live Attendance Station Monitor
 * Akses: https://perguruanpembda.com/diag_all_stations.php?secret=pembda99
 */

$isCli = (php_sapi_name() === 'cli');
$secret = $_GET['secret'] ?? ($isCli ? 'pembda99' : '');
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('403 Forbidden: Gunakan ?secret=pembda99');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\School;
use App\Models\Device;

header('Content-Type: text/html; charset=utf-8');

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$lastFriday = date('Y-m-d', strtotime('last friday'));
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

// 1. Ambil 3 Unit Sekolah Aktif
$schools = School::where('is_active', true)->where('type', '!=', 'yayasan')->get();

// 2. Kumpulkan seluruh Device ID fisik dari database
$dbDevicesStudent = DB::table('attendances')
    ->whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->where('date', '>=', $thirtyDaysAgo)
    ->select('device_id')
    ->distinct()
    ->pluck('device_id')
    ->toArray();

$dbDevicesEmployee = DB::table('employee_attendances')
    ->whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->where('date', '>=', $thirtyDaysAgo)
    ->select('device_id')
    ->distinct()
    ->pluck('device_id')
    ->toArray();

$devicesTableCodes = [];
if (Schema::hasTable('devices')) {
    $devicesTableCodes = DB::table('devices')->pluck('device_code')->toArray();
}

$allDetectedDevices = array_unique(array_merge($dbDevicesStudent, $dbDevicesEmployee, $devicesTableCodes));

// Daftar Standar Hardware Station
$standardStations = [
    'SMP' => ['STATION-SMP-01', 'STATION-SMP-02', 'STATION-SMP-03'],
    'SMA' => ['STATION-SMA-01', 'STATION-SMA-02', 'STATION-SMA-03', 'STATION-SMA-04'],
    'SMK' => ['STATION-SMK-01', 'STATION-SMK-02', 'STATION-SMK-03', 'STATION-SMK-04', 'STATION-SMK-05', 'STATION-SMK-06', 'KIOSK-TEFA']
];

// Gabungkan semua station yang harus diaudit
$stationsToAudit = [
    'SMP' => $standardStations['SMP'],
    'SMA' => $standardStations['SMA'],
    'SMK' => $standardStations['SMK'],
];

foreach ($allDetectedDevices as $dev) {
    if (stripos($dev, 'SMP') !== false && !in_array($dev, $stationsToAudit['SMP'])) {
        $stationsToAudit['SMP'][] = $dev;
    } elseif (stripos($dev, 'SMA') !== false && !in_array($dev, $stationsToAudit['SMA'])) {
        $stationsToAudit['SMA'][] = $dev;
    } elseif (stripos($dev, 'SMK') !== false && !in_array($dev, $stationsToAudit['SMK'])) {
        $stationsToAudit['SMK'][] = $dev;
    }
}

// Fungsi Analisis Per-Station
function auditStation($deviceId, $today, $lastFriday, $sevenDaysAgo, $thirtyDaysAgo) {
    // 1. Cek Live Heartbeat / Liveness Detik Ini dari tabel devices
    $hasDevicesTable = Schema::hasTable('devices');
    $deviceRecord = null;
    $liveStatus = 'OFFLINE';
    $liveStatusLabel = '🔴 PADAM / OFFLINE';
    $liveStatusClass = 'badge-danger';
    $liveDetail = 'Belum pernah kirim sinyal';
    $lastPingSeconds = null;

    if ($hasDevicesTable) {
        $deviceRecord = DB::table('devices')->where('device_code', $deviceId)->first();
        if ($deviceRecord && $deviceRecord->last_seen_at) {
            $lastSeenTime = strtotime($deviceRecord->last_seen_at);
            $lastPingSeconds = time() - $lastSeenTime;

            if ($lastPingSeconds <= 90) { // Di bawah 1.5 menit = ONLINE LIVE
                $liveStatus = 'ONLINE';
                $liveStatusLabel = '🟢 MENYALA & ONLINE';
                $liveStatusClass = 'badge-success';
                $liveDetail = $lastPingSeconds . ' dtk lalu';
            } elseif ($lastPingSeconds <= 300) { // 5 menit = IDLE / BARU SAJA AKTIF
                $liveStatus = 'IDLE';
                $liveStatusLabel = '🟡 SIAGA / IDLE';
                $liveStatusClass = 'badge-warning';
                $liveDetail = round($lastPingSeconds / 60, 1) . ' mnt lalu';
            } else {
                $liveStatus = 'OFFLINE';
                $liveStatusLabel = '🔴 PADAM / OFFLINE';
                $liveStatusClass = 'badge-danger';
                $liveDetail = $deviceRecord->last_seen_at;
            }

            if (!empty($deviceRecord->ip_address)) {
                $liveDetail .= ' | IP: ' . $deviceRecord->ip_address;
            }
            if (!empty($deviceRecord->rssi)) {
                $liveDetail .= ' | ' . $deviceRecord->rssi . ' dBm';
            }
        }
    }

    // 2. Scan Hari Ini
    $todaySiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', $today)->count();
    $todayGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', $today)->count();
    $todayTotal = $todaySiswa + $todayGuru;

    // 3. Scan Hari Kerja Terakhir (Jumat)
    $friSiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', $lastFriday)->count();
    $friGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', $lastFriday)->count();
    $friTotal = $friSiswa + $friGuru;

    // 4. Scan 7 Hari Terakhir
    $weekSiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', '>=', $sevenDaysAgo)->count();
    $weekGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', '>=', $sevenDaysAgo)->count();
    $weekTotal = $weekSiswa + $weekGuru;

    // 5. Scan 30 Hari Terakhir
    $monthSiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', '>=', $thirtyDaysAgo)->count();
    $monthGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', '>=', $thirtyDaysAgo)->count();
    $monthTotal = $monthSiswa + $monthGuru;

    // 6. Scan Terakhir (Timestamp Absen)
    $lastScanSiswa = DB::table('attendances')->where('device_id', $deviceId)->orderByDesc('date')->orderByDesc('time_in')->select('date', 'time_in', 'recorded_via')->first();
    $lastScanGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->orderByDesc('date')->orderByDesc('time_in')->select('date', 'time_in', 'recorded_via')->first();

    $lastTimestamp = null;
    $lastVia = null;
    if ($lastScanSiswa && $lastScanGuru) {
        $timeS = $lastScanSiswa->date . ' ' . $lastScanSiswa->time_in;
        $timeG = $lastScanGuru->date . ' ' . $lastScanGuru->time_in;
        if ($timeS >= $timeG) {
            $lastTimestamp = $timeS;
            $lastVia = $lastScanSiswa->recorded_via;
        } else {
            $lastTimestamp = $timeG;
            $lastVia = $lastScanGuru->recorded_via;
        }
    } elseif ($lastScanSiswa) {
        $lastTimestamp = $lastScanSiswa->date . ' ' . $lastScanSiswa->time_in;
        $lastVia = $lastScanSiswa->recorded_via;
    } elseif ($lastScanGuru) {
        $lastTimestamp = $lastScanGuru->date . ' ' . $lastScanGuru->time_in;
        $lastVia = $lastScanGuru->recorded_via;
    }

    return [
        'device_id'          => $deviceId,
        'live_status'        => $liveStatus,
        'live_status_label'  => $liveStatusLabel,
        'live_status_class'  => $liveStatusClass,
        'live_detail'        => $liveDetail,
        'last_ping_seconds'  => $lastPingSeconds,
        'today_total'        => $todayTotal,
        'fri_total'          => $friTotal,
        'week_total'         => $weekTotal,
        'month_total'        => $monthTotal,
        'last_scan'          => $lastTimestamp,
        'last_via'           => $lastVia,
    ];
}

$auditResults = [];
foreach ($stationsToAudit as $unit => $devList) {
    $auditResults[$unit] = [];
    foreach ($devList as $dev) {
        $auditResults[$unit][$dev] = auditStation($dev, $today, $lastFriday, $sevenDaysAgo, $thirtyDaysAgo);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Hardware Station Monitor (SMP, SMA, SMK) | PembdaHUB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; background: #0b1120; color: #f1f5f9; padding: 20px; line-height: 1.5; font-size: 13px; }
        h1 { color: #38bdf8; font-size: 20px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
        .meta-bar { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .card { background: #131c31; border: 1px solid #1e293b; border-radius: 8px; padding: 16px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); }
        .unit-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .unit-title { font-size: 16px; font-weight: 800; }
        .title-smp { color: #10b981; }
        .title-sma { color: #38bdf8; }
        .title-smk { color: #a855f7; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 12px; }
        th, td { border: 1px solid #334155; padding: 10px 12px; text-align: left; }
        th { background: #1e293b; color: #94a3b8; font-weight: bold; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
        tr:nth-child(even) { background: rgba(255,255,255,0.015); }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; white-space: nowrap; }
        .badge-success { background: #064e3b; color: #34d399; border: 1px solid #059669; }
        .badge-warning { background: #78350f; color: #fbbf24; border: 1px solid #b45309; }
        .badge-danger { background: #7f1d1d; color: #f87171; border: 1px solid #dc2626; }
        .badge-info { background: #0c4a6e; color: #38bdf8; border: 1px solid #0284c7; }
        .pulse-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #34d399; animation: pulse 1.5s infinite; margin-right: 6px; }
        @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); } }
        .summary-box { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .sum-card { background: #1e293b; border-radius: 8px; padding: 14px; border: 1px solid #334155; }
        .sum-title { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold; }
        .sum-val { font-size: 24px; font-weight: 800; margin-top: 4px; font-family: monospace; }
        .btn { background: #0284c7; color: #fff; border: 0; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn:hover { background: #0369a1; }
    </style>
</head>
<body>
    <h1><span class="pulse-dot"></span> LIVE HARDWARE STATION MONITOR (DETIK INI JUGA)</h1>
    <div class="meta-bar">
        <div>
            Waktu Server: <strong><?= date('Y-m-d H:i:s') ?> WIB</strong> | 
            Auto-Refresh: <strong id="countdown">10</strong> detik | 
            Status: <span class="badge badge-info">Pemantauan Denyut Jantung IoT (Heartbeat)</span>
        </div>
        <div>
            <button onclick="location.reload()" class="btn">🔄 Refresh Sekarang</button>
        </div>
    </div>

    <?php
    $totalStations = 0;
    $liveOnline = 0;
    $liveOffline = 0;

    foreach ($auditResults as $unit => $devs) {
        foreach ($devs as $res) {
            $totalStations++;
            if ($res['live_status'] === 'ONLINE') $liveOnline++;
            else $liveOffline++;
        }
    }
    ?>

    <!-- RINGKASAN METRIK LIVE DETIK INI -->
    <div class="summary-box">
        <div class="sum-card" style="border-left: 4px solid #38bdf8;">
            <div class="sum-title">Total Station Terdaftar</div>
            <div class="sum-val" style="color: #38bdf8;"><?= $totalStations ?> Station</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #34d399;">
            <div class="sum-title">SEDANG MENYALA / ONLINE (LIVE)</div>
            <div class="sum-val" style="color: #34d399;"><?= $liveOnline ?> Station</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #f87171;">
            <div class="sum-title">PADAM / OFFLINE (TIDAK ADA DAYA)</div>
            <div class="sum-val" style="color: #f87171;"><?= $liveOffline ?> Station</div>
        </div>
    </div>

    <!-- TABEL MONITOR LIVE PER UNIT -->
    <?php foreach (['SMA' => 'title-sma', 'SMK' => 'title-smk', 'SMP' => 'title-smp'] as $unit => $titleClass): 
        $unitDevs = $auditResults[$unit] ?? [];
        $schoolModel = $schools->where('type', $unit)->first();
    ?>
    <div class="card">
        <div class="unit-header">
            <div>
                <span class="unit-title <?= $titleClass ?>">UNIT <?= $unit ?> — <?= $schoolModel ? htmlspecialchars($schoolModel->name) : '' ?></span>
            </div>
            <div>
                <span class="badge badge-info"><?= count($unitDevs) ?> Station Fisik</span>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 180px;">Device ID</th>
                    <th style="width: 220px;">STATUS FISIK LIVE (DETIK INI)</th>
                    <th>Detail Koneksi &amp; Sinyal WiFi</th>
                    <th>Absen Terakhir Masuk</th>
                    <th>Scan Hari Ini</th>
                    <th>Scan Jumat Lalu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unitDevs as $devId => $row): ?>
                <tr <?= $row['live_status'] === 'ONLINE' ? 'style="background:rgba(52, 211, 153, 0.08);"' : '' ?>>
                    <td><strong><code><?= htmlspecialchars($devId) ?></code></strong></td>
                    <td>
                        <span class="badge <?= $row['live_status_class'] ?>">
                            <?= $row['live_status_label'] ?>
                        </span>
                    </td>
                    <td>
                        <span style="color: <?= $row['live_status'] === 'ONLINE' ? '#34d399' : '#94a3b8' ?>; font-weight: <?= $row['live_status'] === 'ONLINE' ? 'bold' : 'normal' ?>;">
                            <?= htmlspecialchars($row['live_detail']) ?>
                        </span>
                    </td>
                    <td>
                        <?= $row['last_scan'] ? "<code>{$row['last_scan']}</code>" : "<span style='color:#64748b'>Belum ada</span>" ?>
                    </td>
                    <td>
                        <?= $row['today_total'] > 0 ? "<strong style='color:#34d399;'>✅ {$row['today_total']} Scan</strong>" : "<span style='color:#64748b'>0</span>" ?>
                    </td>
                    <td>
                        <?= $row['fri_total'] > 0 ? "<strong>{$row['fri_total']} Scan</strong>" : "<span style='color:#64748b'>0</span>" ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- PANDUAN PENGUJIAN INSTAN -->
    <div class="card" style="border: 1px solid #38bdf8;">
        <h3 style="color: #38bdf8; margin-top: 0;">💡 CARA KERJA SISTEM MONITOR LIVE INI:</h3>
        <ol style="line-height: 1.8; margin-left: 20px;">
            <li><strong>Sinyal Heartbeat Otomatis:</strong> Station dengan firmware terbaru secara otomatis mengirim sinyal "denyut jantung" setiap 45 detik saat standby di gerbang/sekolah.</li>
            <li><strong>Indikator Warna:</strong>
                <ul>
                    <li><strong style="color: #34d399;">🟢 MENYALA &amp; ONLINE:</strong> Alat sedang terhubung ke listrik, WiFi aktif, dan baru saja berkomunikasi dengan server (&lt; 90 detik yang lalu).</li>
                    <li><strong style="color: #f87171;">🔴 PADAM / OFFLINE:</strong> Alat mati (adaptor dicabut), atau terputus dari WiFi sekolah.</li>
                </ul>
            </li>
            <li><strong>Uji Coba Tap Kartu:</strong> Setiap kali kartu RFID di-tap atau QR di-scan di stasiun mana pun, baris stasiun tersebut akan otomatis langsung menyala hijau detik itu juga!</li>
        </ol>
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
