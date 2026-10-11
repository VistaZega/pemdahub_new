<?php
/**
 * PembdaHUB - Forensik Deteksi Mikrokontroler Station Absensi
 * Menelusuri seluruh device_id, log HTTP, User-Agent (ESP8266 vs ESP32) dari absensi beberapa hari terakhir
 * Akses: https://perguruanpembda.com/forensic_device_detector.php?secret=pembda99
 */

$isCli = (php_sapi_name() === 'cli');
$secret = $_GET['secret'] ?? ($isCli ? 'pembda99' : '');
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('403 Forbidden: Gunakan ?secret=pembda99');
}

@set_time_limit(180);
@ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');

// Bootstrap Laravel
$root = realpath(__DIR__ . '/..');
if (!$root || !file_exists("{$root}/vendor/autoload.php")) {
    $root = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub';
}
if (!file_exists("{$root}/vendor/autoload.php")) {
    $root = '/var/www/pembdahub';
}

require_once "{$root}/vendor/autoload.php";
$app = require_once "{$root}/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$days = 14;
$startDate = date('Y-m-d', strtotime("-{$days} days"));

echo "<!DOCTYPE html><html><head><title>Forensik Mikrokontroler Station PembdaHUB</title>";
echo "<style>
body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:20px;font-size:13px;line-height:1.6;}
h1{color:#58a6ff;} h2{color:#79c0ff;border-bottom:1px solid #30363d;padding-bottom:6px;margin-top:24px;}
.ok{color:#3fb950;font-weight:bold;} .warn{color:#d29922;font-weight:bold;} .err{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}
table{width:100%;border-collapse:collapse;margin:12px 0;}
th,td{border:1px solid #30363d;padding:8px 12px;text-align:left;}
th{background:#161b22;color:#8b949e;}
tr:nth-child(even){background:rgba(255,255,255,0.02);}
pre{background:#161b22;border:1px solid #30363d;padding:12px;border-radius:6px;max-height:300px;overflow:auto;}
.badge{padding:2px 8px;border-radius:4px;font-weight:bold;font-size:11px;}
.b-esp8266{background:#1f6feb;color:#fff;}
.b-esp32{background:#8957e5;color:#fff;}
.b-unknown{background:#30363d;color:#8b949e;}
</style></head><body>";
echo "<h1>🔬 FORENSIK DETEKSI MIKROKONTROLER STATION (DATA REAL DATABASE & LOG)</h1>";
echo "<p>Menelusuri absensi masuk sejak <b>{$startDate}</b> sampai hari ini (" . date('Y-m-d H:i:s') . " WIB)...</p>";

// 1. Ambil seluruh device_id dari tabel attendances & employee_attendances
$studentDevs = DB::table('attendances')
    ->whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->where('date', '>=', $startDate)
    ->select('device_id', DB::raw('COUNT(*) as total_scans'), DB::raw('MAX(date) as last_date'), DB::raw('MAX(created_at) as last_scan'))
    ->groupBy('device_id')
    ->get()
    ->keyBy('device_id');

$employeeDevs = DB::table('employee_attendances')
    ->whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->where('date', '>=', $startDate)
    ->select('device_id', DB::raw('COUNT(*) as total_scans'), DB::raw('MAX(date) as last_date'), DB::raw('MAX(created_at) as last_scan'))
    ->groupBy('device_id')
    ->get()
    ->keyBy('device_id');

// Gabungkan semua device_id unik
$allDevIds = array_unique(array_merge($studentDevs->keys()->toArray(), $employeeDevs->keys()->toArray()));
sort($allDevIds);

// 2. Baca log Laravel untuk mencari User-Agent dari setiap device_id
$logDir = "{$root}/storage/logs";
$logFiles = glob("{$logDir}/laravel*.log");
$deviceUserAgents = [];
$deviceIps = [];

if ($logFiles) {
    // Urutkan dari file log terbaru
    rsort($logFiles);
    // Batasi membaca 5 log file terakhir
    $recentLogs = array_slice($logFiles, 0, 5);
    
    foreach ($recentLogs as $lf) {
        $handle = @fopen($lf, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (str_contains($line, 'RFID Scan') || str_contains($line, 'rfid-scan') || str_contains($line, 'heartbeat') || str_contains($line, 'STATION-')) {
                    // Coba tangkap device_id dan User-Agent
                    foreach ($allDevIds as $devId) {
                        if (str_contains($line, $devId)) {
                            if (!isset($deviceUserAgents[$devId])) {
                                if (preg_match('/ESP8266/i', $line)) {
                                    $deviceUserAgents[$devId] = 'ESP8266 (NodeMCU)';
                                } elseif (preg_match('/ESP32/i', $line)) {
                                    $deviceUserAgents[$devId] = 'ESP32';
                                } elseif (preg_match('/User-Agent["\']?\s*:\s*["\']?([^"\',]+)/i', $line, $uam)) {
                                    $deviceUserAgents[$devId] = trim($uam[1]);
                                }
                            }
                            if (!isset($deviceIps[$devId]) && preg_match('/(?:ip["\']?\s*:\s*["\']?|\[)([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/i', $line, $ipm)) {
                                $deviceIps[$devId] = $ipm[1];
                            }
                        }
                    }
                }
            }
            fclose($handle);
        }
    }
}

// 3. Baca tabel devices jika ada
$deviceTableRecords = [];
if (Schema::hasTable('devices')) {
    $deviceTableRecords = DB::table('devices')->get()->keyBy('device_code');
}

// 4. Analisis Unit Berdasarkan Nama
$units = ['SMK' => [], 'SMA' => [], 'SMP' => [], 'LAIN' => []];
foreach ($allDevIds as $dId) {
    if (stripos($dId, 'SMK') !== false || stripos($dId, 'TEFA') !== false) {
        $units['SMK'][] = $dId;
    } elseif (stripos($dId, 'SMA') !== false) {
        $units['SMA'][] = $dId;
    } elseif (stripos($dId, 'SMP') !== false) {
        $units['SMP'][] = $dId;
    } else {
        $units['LAIN'][] = $dId;
    }
}

// Cek juga daftar standar jika ada station yang 0 scan di 14 hari terakhir
$standardList = [
    'SMK' => ['STATION-SMK-01', 'STATION-SMK-02', 'STATION-SMK-03', 'STATION-SMK-4', 'STATION-SMK-05', 'KIOSK-TEFA'],
    'SMA' => ['STATION-SMA-01', 'STATION-SMA-02', 'STATION-SMA-03'],
    'SMP' => ['STATION-SMP-01', 'STATION-SMP-02', 'STATION-SMP-03']
];

foreach ($standardList as $u => $sDevs) {
    foreach ($sDevs as $sd) {
        if (!in_array($sd, $units[$u]) && !in_array($sd, $allDevIds)) {
            $units[$u][] = $sd;
        }
    }
}

echo "<h2>📊 REKAPITULASI STATION BERDASARKAN UNIT SEKOLAH</h2>";

foreach (['SMK' => 'SMK Swasta Pembda Nias', 'SMA' => 'SMAS Pembda 1 Gunungsitoli', 'SMP' => 'SMPS Pembda 2 Gunungsitoli'] as $uKey => $uName) {
    echo "<h3>🏛️ UNIT {$uKey} — {$uName}</h3>";
    echo "<table><thead><tr>
        <th>Device ID</th>
        <th>Total Scan (14 Hari)</th>
        <th>Scan Terakhir</th>
        <th>Tipe di Tabel Devices</th>
        <th>Deteksi User-Agent / Log</th>
        <th>Kesimpulan Mikrokontroler</th>
        <th>Rekomendasi Layar LCD</th>
    </tr></thead><tbody>";

    foreach ($units[$uKey] as $devId) {
        $stCount = $studentDevs[$devId]->total_scans ?? 0;
        $empCount = $employeeDevs[$devId]->total_scans ?? 0;
        $totalScan = $stCount + $empCount;
        
        $lastScanSt = $studentDevs[$devId]->last_scan ?? '';
        $lastScanEmp = $employeeDevs[$devId]->last_scan ?? '';
        $lastScan = max($lastScanSt, $lastScanEmp) ?: '<span style="color:#64748b">Tidak ada data</span>';

        $devRec = $deviceTableRecords[$devId] ?? null;
        $tableType = $devRec->type ?? '<span style="color:#64748b">-</span>';

        $detectedUa = $deviceUserAgents[$devId] ?? null;
        $uaDisplay = $detectedUa ? htmlspecialchars($detectedUa) : '<span style="color:#64748b">Tidak terdeteksi di log</span>';

        // Penentuan Kesimpulan Mikrokontroler
        $mcu = 'Belum Pasti';
        $badgeClass = 'b-unknown';

        // Dari catatan firmware yang ada di folder:
        // SMK-4 -> ESP32_16x2
        // SMP-02 -> ESP32_20x4
        // SMK-01 -> NodeMCU_16x2
        // SMK-03 -> NodeMCU_16x2_2LED
        // SMA-01 -> NodeMCU_20x4
        // SMA-02 -> NodeMCU_20x4
        // SMA-03 -> NodeMCU_20x4_NoMP3
        // SMP-01 -> NodeMCU_16x2 / 20x4
        // SMP-03 -> NodeMCU_20x4

        if ($devId === 'STATION-SMK-4' || $devId === 'STATION-SMK-04' || $devId === 'STATION-SMP-02') {
            $mcu = 'ESP32 DevKit V1';
            $badgeClass = 'b-esp32';
        } elseif ($devId === 'STATION-SMA-01' || $devId === 'STATION-SMA-02' || $devId === 'STATION-SMA-03') {
            $mcu = 'NodeMCU V3 (ESP8266)';
            $badgeClass = 'b-esp8266';
        } elseif ($devId === 'STATION-SMK-01' || $devId === 'STATION-SMK-02' || $devId === 'STATION-SMK-03' || $devId === 'KIOSK-TEFA') {
            $mcu = 'NodeMCU V3 (ESP8266)';
            $badgeClass = 'b-esp8266';
        } elseif ($devId === 'STATION-SMP-01' || $devId === 'STATION-SMP-03') {
            $mcu = 'NodeMCU V3 (ESP8266)';
            $badgeClass = 'b-esp8266';
        }

        if ($detectedUa && str_contains($detectedUa, 'ESP32')) {
            $mcu = 'ESP32 DevKit V1';
            $badgeClass = 'b-esp32';
        } elseif ($detectedUa && str_contains($detectedUa, 'ESP8266')) {
            $mcu = 'NodeMCU V3 (ESP8266)';
            $badgeClass = 'b-esp8266';
        }

        // Tipe LCD sesuai konfirmasi user:
        // SMK: Semua 16x2
        // SMA: Semua 20x4
        $lcdType = ($uKey === 'SMK') ? '<strong>LCD 16x2</strong>' : (($uKey === 'SMA') ? '<strong>LCD 20x4</strong>' : 'LCD 20x4');

        echo "<tr>
            <td><strong><code>{$devId}</code></strong></td>
            <td>{$totalScan} scan</td>
            <td>{$lastScan}</td>
            <td>{$tableType}</td>
            <td>{$uaDisplay}</td>
            <td><span class='badge {$badgeClass}'>{$mcu}</span></td>
            <td>{$lcdType}</td>
        </tr>";
    }
    echo "</tbody></table>";
}

echo "</body></html>";
