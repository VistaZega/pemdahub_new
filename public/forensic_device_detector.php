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

@ini_set('display_errors', '1');
@ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

@set_time_limit(60);
@ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');

// Bootstrap Laravel
try {
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
} catch (\Throwable $bootEx) {
    die("Bootstrap Error: " . $bootEx->getMessage() . " on line " . $bootEx->getLine());
}

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$days = 30;
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
$studentDevs = collect();
$employeeDevs = collect();

try {
    $studentDevs = DB::table('attendances')
        ->whereNotNull('device_id')
        ->where('device_id', '!=', '')
        ->where('date', '>=', $startDate)
        ->select('device_id', DB::raw('COUNT(*) as total_scans'), DB::raw('MAX(date) as last_date'), DB::raw("MAX(CONCAT(date, ' ', COALESCE(time_in, '00:00:00'))) as last_scan"))
        ->groupBy('device_id')
        ->get()
        ->keyBy('device_id');
} catch (\Throwable $ex) {
    echo "<p class='err'>Error query attendances: " . htmlspecialchars($ex->getMessage()) . "</p>";
}

try {
    $employeeDevs = DB::table('employee_attendances')
        ->whereNotNull('device_id')
        ->where('device_id', '!=', '')
        ->where('date', '>=', $startDate)
        ->select('device_id', DB::raw('COUNT(*) as total_scans'), DB::raw('MAX(date) as last_date'), DB::raw("MAX(CONCAT(date, ' ', COALESCE(time_in, '00:00:00'))) as last_scan"))
        ->groupBy('device_id')
        ->get()
        ->keyBy('device_id');
} catch (\Throwable $ex) {
    echo "<p class='err'>Error query employee_attendances: " . htmlspecialchars($ex->getMessage()) . "</p>";
}

// Gabungkan semua device_id unik
$allDevIds = array_unique(array_merge($studentDevs->keys()->toArray(), $employeeDevs->keys()->toArray()));
sort($allDevIds);

// 2. Baca log Laravel dengan aman (hanya 200KB terakhir dari file log hari ini/kemarin)
$deviceUserAgents = [];
$deviceIps = [];
$logFiles = glob(storage_path('logs/laravel*.log'));
if ($logFiles) {
    rsort($logFiles);
    $recentLogs = array_slice($logFiles, 0, 2);
    foreach ($recentLogs as $lf) {
        $filesize = filesize($lf);
        $handle = @fopen($lf, "r");
        if ($handle) {
            $readBytes = min(200000, $filesize);
            if ($filesize > $readBytes) {
                fseek($handle, $filesize - $readBytes);
            }
            $content = fread($handle, $readBytes);
            fclose($handle);

            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                if (str_contains($line, 'RFID Scan') || str_contains($line, 'rfid-scan') || str_contains($line, 'heartbeat') || str_contains($line, 'STATION-')) {
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

// 5. Dump Data Tabel Devices
if (Schema::hasTable('devices')) {
    echo "<h2>📋 DATA DARI TABEL `devices`</h2>";
    $rawDevices = DB::table('devices')->get();
    if ($rawDevices->isNotEmpty()) {
        $firstItem = (array)$rawDevices->first();
        echo "<table><thead><tr>";
        foreach (array_keys($firstItem) as $colKey) {
            echo "<th>" . htmlspecialchars($colKey) . "</th>";
        }
        echo "</tr></thead><tbody>";
        foreach ($rawDevices as $rd) {
            echo "<tr>";
            foreach ((array)$rd as $colVal) {
                echo "<td>" . htmlspecialchars((string)($colVal ?? '-')) . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p class='warn'>Tabel devices kosong.</p>";
    }
}

// 6. 25 Scan Absensi Siswa Terbaru
echo "<h2>⏱️ 25 SCAN ABSENSI SISWA TERBARU (SEMUA UNIT)</h2>";
try {
    $recentScans = DB::table('attendances')
        ->leftJoin('students', 'attendances.student_id', '=', 'students.id')
        ->select('attendances.id', 'attendances.date', 'attendances.time_in', 'attendances.device_id', 'students.name as student_name')
        ->orderByDesc('attendances.date')
        ->orderByDesc('attendances.time_in')
        ->orderByDesc('attendances.id')
        ->limit(25)
        ->get();

    echo "<table><thead><tr>
        <th>ID</th><th>Tanggal & Jam</th><th>Device ID</th><th>Nama Siswa</th>
    </tr></thead><tbody>";
    foreach ($recentScans as $rs) {
        echo "<tr>
            <td>{$rs->id}</td>
            <td><code>{$rs->date} {$rs->time_in}</code></td>
            <td><strong><code>{$rs->device_id}</code></strong></td>
            <td>{$rs->student_name}</td>
        </tr>";
    }
    echo "</tbody></table>";
} catch (\Throwable $ex) {
    echo "<p class='err'>Error query recent scans: " . htmlspecialchars($ex->getMessage()) . "</p>";
}

echo "</body></html>";
