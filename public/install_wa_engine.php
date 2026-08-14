<?php
/**
 * Web Installer & Starter for PembdaHUB Free WhatsApp Engine
 * Access: https://perguruanpembda.com/install_wa_engine.php?secret=pembda99
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<h1 style="color:red; font-family:sans-serif; text-align:center;">403 Forbidden: Invalid Secret Token</h1>');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp Engine Web Deployment - PembdaHUB</title>
    <style>
        body { background: #0b141a; color: #e9edef; font-family: system-ui, sans-serif; padding: 30px; max-width: 800px; margin: 0 auto; }
        .card { background: #111b21; border: 1px solid #222d34; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
        .btn { background: #00a884; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #008f70; }
        pre { background: #000; color: #00ff66; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 13px; }
        .status { padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .connected { background: rgba(37, 211, 102, 0.2); color: #25d366; }
        .disconnected { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    </style>
</head>
<body>
    <div class="card">
        <h2>📱 Web Setup & Status: PembdaHUB WhatsApp Engine ($0 Cost)</h2>
        <p style="color: #8696a0;">Kelola instalasi dependensi dan pengaktifan WhatsApp Engine tanpa perlu akses SSH/Terminal.</p>
        
        <?php
        // Check engine status on localhost:3000
        $ch = curl_init('http://localhost:3000/device');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $isEngineRunning = ($httpCode === 200 && $response);
        $statusData = $isEngineRunning ? json_decode($response, true) : null;
        ?>

        <div style="margin: 20px 0;">
            <strong>Status Server Engine:</strong>
            <?php if ($isEngineRunning): ?>
                <span class="status connected">🟢 AKTIF (Port 3000) - Status WA: <?php echo htmlspecialchars($statusData['status'] ?? 'Running'); ?></span>
            <?php else: ?>
                <span class="status disconnected">🟡 MATI / BELUM BERJALAN</span>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="?action=install&secret=pembda99" class="btn">1. Install Dependensi (npm install)</a>
            <a href="?action=start&secret=pembda99" class="btn" style="background:#3b82f6;">2. Jalankan Server Engine</a>
            <a href="wa_qr.php?secret=pembda99" class="btn" style="background:#a855f7;" target="_blank">3. Scan QR Code 📱</a>
        </div>
    </div>

    <?php
    $action = $_GET['action'] ?? '';
    $rootDir = dirname(__DIR__);

    if ($action === 'install') {
        echo '<div class="card">';
        echo '<h3>📦 Menjalankan npm install di folder whatsapp-server...</h3>';
        echo '<pre>';
        
        $cmd = "cd {$rootDir}/whatsapp-server && npm install 2>&1";
        $output = shell_exec($cmd);
        echo htmlspecialchars($output ?: 'Selesai tanpa output error.');
        
        echo '</pre>';
        echo '</div>';
    }

    if ($action === 'start') {
        echo '<div class="card">';
        echo '<h3>🚀 Memulai WhatsApp Engine Server di Background...</h3>';
        echo '<pre>';
        
        $serverPath = "{$rootDir}/whatsapp-server/server.js";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B node {$serverPath}", "r"));
        } else {
            exec("nohup node {$serverPath} > /dev/null 2>&1 &");
        }
        
        echo "Layanan node whatsapp-server/server.js telah diperintahkan untuk berjalan di background.\n";
        echo "Silakan klik tombol '3. Scan QR Code' atau refresh halaman ini untuk mengecek status.";
        
        echo '</pre>';
        echo '</div>';
    }
    ?>
</body>
</html>
