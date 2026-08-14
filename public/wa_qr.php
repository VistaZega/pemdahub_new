<?php
/**
 * Web Proxy for WhatsApp QR Code Scanner on perguruanpembda.com
 * Access: https://perguruanpembda.com/wa_qr.php?secret=pembda99
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<h1 style="color:red; font-family:sans-serif; text-align:center;">403 Forbidden: Invalid Secret Token</h1>');
}

$ch = curl_init('http://localhost:3000/qr');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$html) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<html>
        <body style="font-family: sans-serif; text-align: center; background: #0b141a; color: white; padding: 40px;">
          <h2 style="color: #f59e0b;">🟡 Engine WhatsApp Server Belum Berjalan di Hosting</h2>
          <p style="color: #8696a0;">Silakan jalankan engine terlebih dahulu melalui:</p>
          <a href="install_wa_engine.php?secret=pembda99" style="background:#00a884; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:bold;">Akses Web Installer & Starter</a>
        </body>
      </html>';
    exit;
}

echo $html;
