<?php
/**
 * Diag All Stations (SMP, SMA, SMK) - Comprehensive Attendance Kiosk Diagnostic Tool
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
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Employee;

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

$allDetectedDevices = array_unique(array_merge($dbDevicesStudent, $dbDevicesEmployee));

// Daftar Standar Hardware Station
$standardStations = [
    'SMP' => ['STATION-SMP-01', 'STATION-SMP-02', 'STATION-SMP-03'],
    'SMA' => ['STATION-SMA-01', 'STATION-SMA-02', 'STATION-SMA-03'],
    'SMK' => ['STATION-SMK-01', 'STATION-SMK-02', 'STATION-SMK-03', 'STATION-SMK-4', 'KIOSK-TEFA']
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
    // 1. Scan Hari Ini
    $todaySiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', $today)->count();
    $todayGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', $today)->count();
    $todayTotal = $todaySiswa + $todayGuru;

    // 2. Scan Hari Kerja Terakhir (Jumat)
    $friSiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', $lastFriday)->count();
    $friGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', $lastFriday)->count();
    $friTotal = $friSiswa + $friGuru;

    // 3. Scan 7 Hari Terakhir
    $weekSiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', '>=', $sevenDaysAgo)->count();
    $weekGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', '>=', $sevenDaysAgo)->count();
    $weekTotal = $weekSiswa + $weekGuru;

    // 4. Scan 30 Hari Terakhir
    $monthSiswa = DB::table('attendances')->where('device_id', $deviceId)->where('date', '>=', $thirtyDaysAgo)->count();
    $monthGuru  = DB::table('employee_attendances')->where('device_id', $deviceId)->where('date', '>=', $thirtyDaysAgo)->count();
    $monthTotal = $monthSiswa + $monthGuru;

    // 5. Scan Terakhir (Timestamp)
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

    // 6. Metode yang Digunakan
    $vias = DB::table('attendances')->where('device_id', $deviceId)->where('date', '>=', $thirtyDaysAgo)->select('recorded_via')->distinct()->pluck('recorded_via')->toArray();

    // 7. Penentuan Status
    if ($todayTotal > 0) {
        $status = 'READY_TODAY';
        $statusLabel = 'ONLINE & AKTIF HARI INI';
        $statusClass = 'badge-success';
    } elseif ($friTotal > 0 || $weekTotal > 20) {
        $status = 'READY_STANDBY';
        $statusLabel = 'READY (BEROPERASI NORMAL)';
        $statusClass = 'badge-success';
    } elseif ($weekTotal > 0 || $monthTotal > 0) {
        $status = 'WARNING_LOW';
        $statusLabel = 'SEMPAT AKTIF / KURANG AKTIF';
        $statusClass = 'badge-warning';
    } else {
        $status = 'OFFLINE_CRITICAL';
        $statusLabel = 'BERMASALAH / OFFLINE KONSISTEN';
        $statusClass = 'badge-danger';
    }

    return [
        'device_id'    => $deviceId,
        'today_total'  => $todayTotal,
        'today_siswa'  => $todaySiswa,
        'today_guru'   => $todayGuru,
        'fri_total'    => $friTotal,
        'week_total'   => $weekTotal,
        'month_total'  => $monthTotal,
        'last_scan'    => $lastTimestamp,
        'last_via'     => $lastVia,
        'vias'         => $vias,
        'status'       => $status,
        'status_label' => $statusLabel,
        'status_class' => $statusClass,
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
    <title>Audit Komprehensif Seluruh Station Absensi (SMP, SMA, SMK) | PembdaHUB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; background: #0b1120; color: #f1f5f9; padding: 24px; line-height: 1.5; font-size: 13px; }
        h1 { color: #38bdf8; font-size: 20px; margin-bottom: 4px; }
        h2 { color: #94a3b8; font-size: 15px; margin-top: 24px; margin-bottom: 12px; border-bottom: 1px solid #1e293b; padding-bottom: 6px; }
        .meta-bar { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .card { background: #131c31; border: 1px solid #1e293b; border-radius: 8px; padding: 16px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); }
        .unit-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .unit-title { font-size: 16px; font-weight: 800; }
        .title-smp { color: #10b981; }
        .title-sma { color: #38bdf8; }
        .title-smk { color: #a855f7; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 12px; }
        th, td { border: 1px solid #334155; padding: 8px 12px; text-align: left; }
        th { background: #1e293b; color: #94a3b8; font-weight: bold; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
        tr:nth-child(even) { background: rgba(255,255,255,0.015); }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; white-space: nowrap; }
        .badge-success { background: #064e3b; color: #34d399; border: 1px solid #059669; }
        .badge-warning { background: #78350f; color: #fbbf24; border: 1px solid #b45309; }
        .badge-danger { background: #7f1d1d; color: #f87171; border: 1px solid #dc2626; }
        .badge-info { background: #0c4a6e; color: #38bdf8; border: 1px solid #0284c7; }
        .ok { color: #34d399; font-weight: bold; }
        .warn { color: #fbbf24; font-weight: bold; }
        .err { color: #f87171; font-weight: bold; }
        .summary-box { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .sum-card { background: #1e293b; border-radius: 8px; padding: 14px; border: 1px solid #334155; }
        .sum-title { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold; }
        .sum-val { font-size: 24px; font-weight: 800; margin-top: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔍 AUDIT LENGKAP KESIAPAN STATION ABSENSI PEMBDAHUB</h1>
    <div class="meta-bar">
        <div>
            Waktu Server: <strong><?= date('Y-m-d H:i:s') ?> WIB</strong> | 
            Hari: <strong><?= date('l') ?></strong> | 
            Hari Kerja Terakhir: <strong><?= $lastFriday ?> (Jumat)</strong>
        </div>
        <div>
            <span class="badge badge-info">Lingkungan: <?= config('app.env') ?></span>
        </div>
    </div>

    <?php
    $totalStations = 0;
    $readyStations = 0;
    $warningStations = 0;
    $brokenStations = 0;

    foreach ($auditResults as $unit => $devs) {
        foreach ($devs as $res) {
            $totalStations++;
            if (in_array($res['status'], ['READY_TODAY', 'READY_STANDBY'])) $readyStations++;
            elseif ($res['status'] === 'WARNING_LOW') $warningStations++;
            else $brokenStations++;
        }
    }
    ?>

    <!-- RINGKASAN METRIK GLOBAL -->
    <div class="summary-box">
        <div class="sum-card" style="border-left: 4px solid #38bdf8;">
            <div class="sum-title">Total Station Dipantau</div>
            <div class="sum-val" style="color: #38bdf8;"><?= $totalStations ?> Station</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #34d399;">
            <div class="sum-title">Station Siap &amp; Normal (Ready)</div>
            <div class="sum-val" style="color: #34d399;"><?= $readyStations ?> Station</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #fbbf24;">
            <div class="sum-title">Perlu Perhatian / Kurang Aktif</div>
            <div class="sum-val" style="color: #fbbf24;"><?= $warningStations ?> Station</div>
        </div>
        <div class="sum-card" style="border-left: 4px solid #f87171;">
            <div class="sum-title">Bermasalah / Offline Konsisten</div>
            <div class="sum-val" style="color: #f87171;"><?= $brokenStations ?> Station</div>
        </div>
    </div>

    <!-- TABEL PER UNIT SEKOLAH -->
    <?php foreach (['SMP' => 'title-smp', 'SMA' => 'title-sma', 'SMK' => 'title-smk'] as $unit => $titleClass): 
        $unitDevs = $auditResults[$unit] ?? [];
        $schoolModel = $schools->where('type', $unit)->first();
    ?>
    <div class="card">
        <div class="unit-header">
            <div>
                <span class="unit-title <?= $titleClass ?>">UNIT <?= $unit ?> — <?= $schoolModel ? htmlspecialchars($schoolModel->name) : '' ?></span>
            </div>
            <div>
                <span class="badge badge-info"><?= count($unitDevs) ?> Station Terdata</span>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Device ID</th>
                    <th>Status Kesiapan</th>
                    <th>Hari Ini (<?= $today ?>)</th>
                    <th>Jumat Terakhir (<?= $lastFriday ?>)</th>
                    <th>7 Hari Terakhir</th>
                    <th>30 Hari Terakhir</th>
                    <th>Scan Terakhir Tercatat</th>
                    <th>Metode Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unitDevs as $devId => $row): ?>
                <tr>
                    <td><strong><code><?= htmlspecialchars($devId) ?></code></strong></td>
                    <td>
                        <span class="badge <?= $row['status_class'] ?>"><?= $row['status_label'] ?></span>
                    </td>
                    <td>
                        <?= $row['today_total'] > 0 ? "<span class='ok'>✅ {$row['today_total']} Scan</span>" : "<span style='color:#64748b'>0</span>" ?>
                    </td>
                    <td>
                        <?= $row['fri_total'] > 0 ? "<span class='ok'>✅ {$row['fri_total']} Scan</span>" : "<span style='color:#64748b'>0</span>" ?>
                    </td>
                    <td>
                        <?= $row['week_total'] > 0 ? "<span class='ok'>{$row['week_total']} Scan</span>" : "<span style='color:#64748b'>0</span>" ?>
                    </td>
                    <td>
                        <?= $row['month_total'] > 0 ? "<strong>{$row['month_total']}</strong> Scan" : "<span style='color:#64748b'>0</span>" ?>
                    </td>
                    <td>
                        <?= $row['last_scan'] ? "<code>{$row['last_scan']}</code>" : "<span class='err'>Belum pernah</span>" ?>
                    </td>
                    <td>
                        <?= !empty($row['vias']) ? implode(', ', array_map('strtoupper', $row['vias'])) : ($row['last_via'] ? strtoupper($row['last_via']) : '-') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- DETAIL REKOMENDASI PERBAIKAN -->
    <div class="card" style="border: 1px solid #38bdf8;">
        <h2 style="color: #38bdf8; margin-top: 0;">🛠️ REKOMENDASI TINDAKAN PERBAIKAN:</h2>
        <ul style="line-height: 1.8; margin-left: 20px;">
            <?php
            $hasIssue = false;
            foreach ($auditResults as $unit => $devs) {
                foreach ($devs as $devId => $row) {
                    if (in_array($row['status'], ['OFFLINE_CRITICAL', 'WARNING_LOW'])) {
                        $hasIssue = true;
                        echo "<li><strong><code>{$devId}</code> (Unit {$unit}):</strong> Status <em>{$row['status_label']}</em>. ";
                        if ($row['month_total'] == 0) {
                            echo "Tidak ada data scan selama 30 hari terakhir. Periksa catu daya (adaptor 5V 2A), kabel power, dan firmware.";
                        } else {
                            echo "Scan terakhir: " . ($row['last_scan'] ?? '-') . ". Periksa apakah alat terputus dari WiFi sekolah atau adaptor mati.";
                        }
                        echo "</li>";
                    }
                }
            }
            if (!$hasIssue) {
                echo "<li class='ok'>Semua station dalam kondisi prima dan aktif beroperasi normal!</li>";
            }
            ?>
        </ul>
    </div>
</body>
</html>
