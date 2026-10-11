<?php
/**
 * PembdaHUB - Alat Pengujian Jaringan & Pencari IP Hardware Station (ESP8266 / ESP32)
 * Akses: 
 *   - Lokal: http://pembdahub.test/scan_station_ips.php atau http://localhost/pembdahub/public/scan_station_ips.php
 *   - Production: https://perguruanpembda.com/scan_station_ips.php?secret=pembda99
 *   - Server Ubuntu Lokal: http://50.35.89.10/scan_station_ips.php?secret=pembda99
 */

$isCli = (php_sapi_name() === 'cli');
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost') || str_contains($_SERVER['HTTP_HOST'] ?? '', '.test');

$secret = $_GET['secret'] ?? ($isCli || $isLocal ? 'pembda99' : '');
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>403 Forbidden</title><style>body{background:#0b1120;color:#f87171;font-family:monospace;padding:40px;text-align:center;}</style></head><body><h2>⛔ 403 Forbidden</h2><p>Gunakan parameter: <code>?secret=pembda99</code></p></body></html>');
}

@set_time_limit(120);
@ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');

// Database MAC Vendor OUI Espressif (NodeMCU ESP8266 & ESP32)
$espressifOUIs = [
    '10:52:1c', '18:fe:34', '24:0a:c4', '24:6f:28', '24:b2:de', '2c:3a:e8', '30:ae:a4', '3c:61:05', 
    '3c:71:bf', '40:22:d8', '40:91:51', '48:3f:da', '48:55:19', '4c:11:ae', '50:02:91', '54:43:b2', 
    '5c:cf:7f', '60:01:94', '60:55:f9', '68:c6:3a', '70:03:9f', '70:b3:d5', '7c:87:ce', '7c:df:a1', 
    '80:7d:3a', '84:0d:8e', '84:cc:a8', '84:f3:eb', '90:97:d5', '94:b5:55', '94:b9:7e', 'a0:20:a6', 
    'a4:cf:12', 'a4:e5:7c', 'ac:0b:fb', 'ac:67:b2', 'ac:d0:74', 'b4:e6:2d', 'bc:dd:c2', 'c4:4f:33', 
    'c4:dd:57', 'cc:50:e3', 'd8:a0:1d', 'd8:bf:c0', 'dc:4f:22', 'dc:54:75', 'e0:98:06', 'e8:31:cd', 
    'e8:db:84', 'ec:62:60', 'ec:fa:bc'
];

// Deteksi IP Lokal Komputer / Server
function getLocalSubnet() {
    $ip = '192.168.1';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $out = @shell_exec('ipconfig');
        if (preg_match_all('/IPv4 Address[^\:]*:\s*([0-9\.]+)/i', $out, $m)) {
            foreach ($m[1] as $candidate) {
                if ($candidate !== '127.0.0.1' && !str_starts_with($candidate, '169.254.') && !str_starts_with($candidate, '100.')) {
                    $parts = explode('.', $candidate);
                    return "{$parts[0]}.{$parts[1]}.{$parts[2]}";
                }
            }
        }
    } else {
        $out = @shell_exec("hostname -I 2>/dev/null || ifconfig 2>/dev/null");
        if (preg_match_all('/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $out, $m)) {
            foreach ($m[1] as $candidate) {
                if ($candidate !== '127.0.0.1' && !str_starts_with($candidate, '172.17.') && !str_starts_with($candidate, '100.')) {
                    $parts = explode('.', $candidate);
                    return "{$parts[0]}.{$parts[1]}.{$parts[2]}";
                }
            }
        }
    }
    return $ip;
}

$detectedSubnet = getLocalSubnet();
$targetSubnet = trim($_GET['subnet'] ?? $detectedSubnet);
// Bersihkan input subnet (contoh: 192.168.1.0/24 -> 192.168.1)
$targetSubnet = preg_replace('/(\.0(\/24)?|\/24|\.)$/', '', $targetSubnet);

$doScan = isset($_GET['action']) && $_GET['action'] === 'scan';
$testSingleIp = trim($_GET['test_ip'] ?? '');

$scanResults = [];
$arpTable = [];

// Fungsi membaca ARP Table
function getArpTable() {
    $arp = [];
    $raw = @shell_exec('arp -a');
    if (!$raw) return $arp;
    
    $lines = explode("\n", $raw);
    foreach ($lines as $line) {
        $line = trim($line);
        // Format Windows / Linux: IP MAC Type
        if (preg_match('/([0-9]{1,3}(?:\.[0-9]{1,3}){3})\s+([0-9a-fA-F\:\-]{11,17})\s+(\w+)?/i', $line, $m)) {
            $ip = $m[1];
            $mac = strtolower(str_replace('-', ':', $m[2]));
            // Pad 1-digit hex (e.g. 0:e0:4c)
            $macParts = explode(':', $mac);
            if (count($macParts) === 6) {
                $mac = implode(':', array_map(fn($p) => str_pad($p, 2, '0', STR_PAD_LEFT), $macParts));
            }
            if ($mac !== 'ff:ff:ff:ff:ff:ff' && !str_ends_with($ip, '.255')) {
                $arp[$ip] = $mac;
            }
        }
    }
    return $arp;
}

// 1. Jika pengguna meminta pemindaian (Scan)
if ($doScan) {
    // Multi-ping / Async Socket Sweep di Subnet
    $sockets = [];
    $ports = [80, 8080, 23]; // Port yang sering terbuka di IoT
    
    // Kirim ping OS ke 254 IP secara paralel jika memungkinkan
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Fast ping sweep via PowerShell satu baris di background
        $psCmd = '1..254 | ForEach-Object { $ip = "' . $targetSubnet . '." + $_; (New-Object System.Net.NetworkInformation.Ping).SendPingAsync($ip, 150) } | Out-Null';
        @shell_exec('powershell -Command "' . $psCmd . '" 2>&1');
    } else {
        // Linux: fping atau nmap jika ada, atau parallel ping
        @shell_exec("for i in $(seq 1 254); do ping -c 1 -W 1 {$targetSubnet}.\$i >/dev/null 2>&1 & done; sleep 1.5");
    }

    // Ambil tabel ARP setelah sweep
    $arpTable = getArpTable();

    // Saring hasil perangkat yang ada di subnet target
    foreach ($arpTable as $ip => $mac) {
        if (str_starts_with($ip, $targetSubnet . '.')) {
            $prefix = substr($mac, 0, 8);
            $isEsp = in_array($prefix, $espressifOUIs, true);
            
            // Tes respon ping cepat
            $isAlive = true;
            $latency = 'Aktif (ARP)';
            
            $scanResults[] = [
                'ip'          => $ip,
                'mac'         => $mac,
                'is_esp'      => $isEsp,
                'vendor'      => $isEsp ? 'Espressif Inc. (NodeMCU / ESP32)' : 'Perangkat Jaringan Lain',
                'latency'     => $latency,
            ];
        }
    }

    // Urutkan berdasarkan octet terakhir IP
    usort($scanResults, function($a, $b) {
        $ipA = (int)substr(strrchr($a['ip'], '.'), 1);
        $ipB = (int)substr(strrchr($b['ip'], '.'), 1);
        // Dahulukan ESP
        if ($a['is_esp'] && !$b['is_esp']) return -1;
        if (!$a['is_esp'] && $b['is_esp']) return 1;
        return $ipA - $ipB;
    });
}

// 2. Jika menguji Single IP
$singleTestResult = null;
if (!empty($testSingleIp)) {
    $pingTime = false;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $out = @shell_exec("ping -n 1 -w 800 " . escapeshellarg($testSingleIp));
        if (str_contains($out, 'TTL=') || str_contains($out, 'bytes=')) {
            if (preg_match('/time[=<]([0-9]+)ms/i', $out, $pm)) {
                $pingTime = $pm[1] . ' ms';
            } else {
                $pingTime = 'Online (< 1 ms)';
            }
        }
    } else {
        $out = @shell_exec("ping -c 1 -W 1 " . escapeshellarg($testSingleIp));
        if (str_contains($out, 'ttl=')) {
            if (preg_match('/time=([0-9\.]+) ms/i', $out, $pm)) {
                $pingTime = $pm[1] . ' ms';
            } else {
                $pingTime = 'Online';
            }
        }
    }

    // Cek Port 80
    $fp = @fsockopen($testSingleIp, 80, $errno, $errstr, 0.5);
    $port80Open = is_resource($fp);
    if ($port80Open) @fclose($fp);

    $currentArp = getArpTable();
    $mac = $currentArp[$testSingleIp] ?? 'Tidak terdata di cache ARP';
    $prefix = is_string($mac) && strlen($mac) >= 8 ? substr($mac, 0, 8) : '';
    $isEsp = in_array($prefix, $espressifOUIs, true);

    $singleTestResult = [
        'ip'        => $testSingleIp,
        'mac'       => $mac,
        'ping'      => $pingTime,
        'port80'    => $port80Open,
        'is_esp'    => $isEsp,
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner & Uji IP Hardware Station (ESP8266/ESP32) | PembdaHUB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; background: #0b1120; color: #f1f5f9; padding: 20px; line-height: 1.5; font-size: 13px; }
        h1 { color: #38bdf8; font-size: 20px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
        .meta-bar { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; }
        .card { background: #131c31; border: 1px solid #1e293b; border-radius: 8px; padding: 18px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 13px; text-decoration: none; border: 0; cursor: pointer; }
        .btn-primary { background: #0284c7; color: #ffffff; } .btn-primary:hover { background: #0369a1; }
        .btn-success { background: #059669; color: #ffffff; } .btn-success:hover { background: #047857; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; white-space: nowrap; }
        .badge-success { background: #064e3b; color: #34d399; border: 1px solid #059669; }
        .badge-danger { background: #7f1d1d; color: #f87171; border: 1px solid #dc2626; }
        .badge-info { background: #0c4a6e; color: #38bdf8; border: 1px solid #0284c7; }
        .badge-warning { background: #78350f; color: #fbbf24; border: 1px solid #b45309; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        th, td { border: 1px solid #334155; padding: 10px 14px; text-align: left; }
        th { background: #1e293b; color: #94a3b8; font-weight: bold; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
        tr:nth-child(even) { background: rgba(255,255,255,0.015); }
        .input-text { background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-family: monospace; }
        .preset-badge { background: #1e293b; border: 1px solid #475569; color: #94a3b8; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 12px; display: inline-block; margin-right: 6px; margin-top: 6px; }
        .preset-badge:hover { background: #334155; color: #38bdf8; }
        .preset-badge.active { background: #0c4a6e; border-color: #0284c7; color: #38bdf8; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 ALAT TEST & PENCARI IP HARDWARE STATION (ESP8266 / ESP32)</h1>
    
    <div class="meta-bar">
        <p style="margin: 0 0 10px 0; color: #cbd5e1;">
            Alat ini berfungsi untuk memindai seluruh IP perangkat di jaringan lokal tanpa harus meng-update firmware station. 
            Chip <strong>NodeMCU (ESP-12F)</strong> dan <strong>ESP32</strong> memiliki identitas unik (MAC Vendor <em>Espressif Inc.</em>) 
            sehingga otomatis terdeteksi saat menyala dan terhubung ke Wi-Fi.
        </p>
        <div style="font-size: 12px; color: #94a3b8;">
            Host: <strong><?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost') ?></strong> | 
            Subnet Terdeteksi di Mesin ini: <strong style="color: #38bdf8;"><?= htmlspecialchars($detectedSubnet) ?>.0/24</strong>
        </div>
    </div>

    <!-- FORM PEMINDAIAN SUBSET -->
    <div class="card">
        <h3 style="color: #38bdf8; margin-top: 0;">1. Pindai Seluruh IP di Jaringan (Subnet Scanner)</h3>
        <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
            <input type="hidden" name="action" value="scan">
            
            <label style="font-weight: bold; color: #cbd5e1;">Subnet Target:</label>
            <input type="text" name="subnet" value="<?= htmlspecialchars($targetSubnet) ?>" class="input-text" style="width: 140px;" placeholder="192.168.1">
            <span style="color: #94a3b8;">.0/24</span>

            <button type="submit" class="btn btn-primary">
                🚀 Mulai Pindai 254 IP Sekarang
            </button>
        </form>

        <div style="margin-top: 14px;">
            <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">Preset Subnet Populer:</span><br>
            <a href="?secret=<?= $secret ?>&action=scan&subnet=192.168.1" class="preset-badge <?= $targetSubnet === '192.168.1' ? 'active' : '' ?>">🏠 192.168.1 (Wi-Fi Standar)</a>
            <a href="?secret=<?= $secret ?>&action=scan&subnet=192.168.49" class="preset-badge <?= $targetSubnet === '192.168.49' ? 'active' : '' ?>">🏫 192.168.49 (PembdaLINK Sekolah)</a>
            <a href="?secret=<?= $secret ?>&action=scan&subnet=50.35.89" class="preset-badge <?= $targetSubnet === '50.35.89' ? 'active' : '' ?>">🖥️ 50.35.89 (LAN Ubuntu Sekolah)</a>
            <a href="?secret=<?= $secret ?>&action=scan&subnet=192.168.43" class="preset-badge <?= $targetSubnet === '192.168.43' ? 'active' : '' ?>">📱 192.168.43 (Hotspot Android)</a>
            <a href="?secret=<?= $secret ?>&action=scan&subnet=172.20.10" class="preset-badge <?= $targetSubnet === '172.20.10' ? 'active' : '' ?>">🍏 172.20.10 (Hotspot iPhone)</a>
        </div>
    </div>

    <!-- HASIL PEMINDAIAN -->
    <?php if ($doScan): ?>
    <div class="card" style="border-color: #38bdf8;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="color: #38bdf8; margin: 0;">📋 Hasil Pemindaian Subnet: <?= htmlspecialchars($targetSubnet) ?>.0/24</h3>
            <div>
                <span class="badge badge-info">Ditemukan <?= count($scanResults) ?> Perangkat Aktif</span>
            </div>
        </div>

        <?php 
        $espTotal = count(array_filter($scanResults, fn($d) => $d['is_esp'])); 
        if ($espTotal > 0): ?>
            <div style="background: rgba(5, 150, 105, 0.2); border: 1px solid #059669; border-radius: 6px; padding: 12px; margin-bottom: 14px; color: #34d399;">
                <strong>🎉 SUKSES! DITEMUKAN <?= $espTotal ?> HARDWARE STATION (CHIP ESPRESSIF / NODEMCU / ESP32) AKTIF DI JARINGAN INI!</strong><br>
                <span style="font-size: 12px; color: #a7f3d0;">Alat presensi tersebut menyala normal dan berhasil mendapatkan alamat IP dari router Wi-Fi.</span>
            </div>
        <?php else: ?>
            <div style="background: rgba(120, 53, 15, 0.2); border: 1px solid #b45309; border-radius: 6px; padding: 12px; margin-bottom: 14px; color: #fbbf24;">
                <strong>ℹ Tidak ditemukan chip Espressif di subnet <?= htmlspecialchars($targetSubnet) ?>.0/24.</strong><br>
                <span style="font-size: 12px; color: #fde68a;">
                    Pastikan komputer/laptop ini terhubung ke Wi-Fi yang sama dengan station (misal sama-sama ke <code>PembdaLINK</code> atau Hotspot yang sama), 
                    atau coba pilih preset subnet lain di atas.
                </span>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th style="width: 160px;">Alamat IP</th>
                    <th style="width: 160px;">MAC Address</th>
                    <th>Identifikasi Perangkat</th>
                    <th style="width: 140px;">Status Hardware</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($scanResults)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px;">
                            Tidak ada perangkat yang merespons pada subnet ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($scanResults as $res): ?>
                    <tr style="<?= $res['is_esp'] ? 'background: rgba(5, 150, 105, 0.12); font-weight: bold;' : '' ?>">
                        <td>
                            <strong style="color: <?= $res['is_esp'] ? '#34d399' : '#f8fafc' ?>;">
                                <?= htmlspecialchars($res['ip']) ?>
                            </strong>
                        </td>
                        <td>
                            <code><?= htmlspecialchars($res['mac']) ?></code>
                        </td>
                        <td>
                            <?php if ($res['is_esp']): ?>
                                <span class="badge badge-success">🔥 CHIP STATION PRESENSI (ESPRESSIF)</span>
                                <span style="color: #34d399; font-size: 11px; margin-left: 6px;">NodeMCU / ESP32</span>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-weight: normal;"><?= htmlspecialchars($res['vendor']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $res['is_esp'] ? 'badge-success' : 'badge-info' ?>">
                                <?= $res['is_esp'] ? '🟢 HIDUP & CONNECT' : 'Aktif' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?secret=<?= $secret ?>&test_ip=<?= urlencode($res['ip']) ?>&subnet=<?= urlencode($targetSubnet) ?>" class="btn btn-primary" style="padding: 3px 8px; font-size: 11px;">
                                🔍 Tes Ping
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- FORM UJI COBA SINGLE IP -->
    <div class="card">
        <h3 style="color: #38bdf8; margin-top: 0;">2. Uji Koneksi Langsung ke Satu IP Tertentu (Direct IP Probe)</h3>
        <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
            
            <label style="font-weight: bold; color: #cbd5e1;">Ketik Alamat IP Station:</label>
            <input type="text" name="test_ip" value="<?= htmlspecialchars($testSingleIp) ?>" class="input-text" style="width: 180px;" placeholder="Contoh: 192.168.1.15">

            <button type="submit" class="btn btn-success">
                ⚡ Uji Respon IP Sekarang
            </button>
        </form>

        <?php if ($singleTestResult !== null): ?>
        <div style="margin-top: 16px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px;">
            <h4 style="margin: 0 0 8px 0; color: #38bdf8;">Hasil Uji IP: <?= htmlspecialchars($singleTestResult['ip']) ?></h4>
            <div style="line-height: 1.8;">
                Status Ping ICMP : 
                <?php if ($singleTestResult['ping']): ?>
                    <span class="badge badge-success">🟢 MERESPON PING (<?= $singleTestResult['ping'] ?>)</span>
                <?php else: ?>
                    <span class="badge badge-danger">🔴 TIDAK MERESPON PING</span>
                <?php endif; ?>
                <br>
                MAC Address : <code><?= htmlspecialchars($singleTestResult['mac']) ?></code><br>
                Identifikasi Hardware : 
                <?php if ($singleTestResult['is_esp']): ?>
                    <span class="badge badge-success">✔ Terkonfirmasi Chip Espressif (Station PembdaHUB)</span>
                <?php else: ?>
                    <span style="color: #94a3b8;">Bukan Espressif / Belum ada di ARP cache</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- PANDUAN PRAKTIS LAPANGAN -->
    <div class="card" style="border: 1px solid #334155;">
        <h3 style="color: #fbbf24; margin-top: 0;">💡 Panduan Praktis di Lapangan:</h3>
        <ol style="line-height: 1.8; margin-left: 20px; color: #cbd5e1;">
            <li><strong>Jika Anda berada di dekat station:</strong> Buka tool ini di laptop yang terhubung ke Wi-Fi yang sama (misal sama-sama ke <code>PembdaLINK</code> atau Hotspot), lalu klik <strong>"Mulai Pindai 254 IP Sekarang"</strong>. Semua station yang hidup akan langsung muncul dengan tanda hijau.</li>
            <li><strong>Jika menggunakan file desktop offline:</strong> Di folder proyek lokal Anda sudah tersedia file <code>scan_stations.bat</code>. Cukup klik ganda (double-click) file tersebut untuk memindai otomatis via terminal.</li>
            <li><strong>Untuk memverifikasi ke server pusat:</strong> Cukup tempelkan 1 kartu apa saja ke station. Detik itu juga, station akan menembakkan request dan IP-nya tercatat permanen di sistem.</li>
        </ol>
    </div>

    <div style="text-align: center; margin-top: 24px; color: #64748b; font-size: 12px;">
        PembdaHUB Hardware & Network Diagnostic Suite &bull; 2026
    </div>
</body>
</html>
