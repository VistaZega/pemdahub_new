<?php
/**
 * Info Deploy & Instructions
 * Akses: https://perguruanpembda.com/git_pull_now.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { http_response_code(403); die('Forbidden'); }

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Petunjuk Git Deploy Hostinger - PembdaHUB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; max-width: 800px; margin: 0 auto; line-height: 1.6; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); margin-bottom: 20px; }
        h1 { color: #818cf8; font-size: 22px; margin-top: 0; }
        h2 { color: #38bdf8; font-size: 16px; margin-top: 0; }
        .step { background: #0f172a; border-left: 4px solid #6366f1; padding: 12px 16px; margin: 12px 0; border-radius: 0 8px 8px 0; }
        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 10px; font-weight: bold; margin-top: 10px; }
        .btn-green { background: linear-gradient(135deg, #10b981, #059669); }
        .code { background: #000; color: #f43f5e; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="card">
        <h1>ℹ️ Petunjuk Deploy Hostinger PembdaHUB</h1>
        <p>Karena keterbatasan sistem Shared Hosting Hostinger, skrip PHP via browser <strong>tidak memiliki izin SSH</strong> untuk mengeksekusi <span class="code">git pull</span> secara langsung dari GitHub.</p>
        
        <h2>Langkah Wajib untuk Mengaktifkan Perubahan Kode Terbaru:</h2>

        <div class="step">
            <strong>Langkah 1: Klik Deploy di hPanel Hostinger</strong><br>
            1. Buka <a href="https://hpanel.hostinger.com" target="_blank" style="color:#60a5fa;">hPanel Hostinger</a>.<br>
            2. Masuk ke menu <strong>Git</strong> (di bagian <i>Files</i> / <i>Advanced</i>).<br>
            3. Pada repository <strong>main</strong>, klik tombol <strong>Deploy</strong> atau <strong>Pull Changes</strong>.
        </div>

        <div class="step">
            <strong>Langkah 2: Jalankan Migrasi Database Baru</strong><br>
            Setelah klik Deploy di Hostinger, klik tombol hijau di bawah untuk menjalankan migrasi database baru:<br>
            <a href="https://perguruanpembda.com/run-migrations?secret=pembda99" class="btn btn-green">Jalankan Migrasi Database</a>
        </div>

        <div class="step">
            <strong>Langkah 3: Bersihkan Cache Server</strong><br>
            Klik tombol pembersih cache untuk memperbarui tampilan Laravel:<br>
            <a href="https://perguruanpembda.com/clear-cache.php?secret=pembda99" class="btn">Bersihkan Cache Laravel</a>
        </div>
    </div>
</body>
</html>
