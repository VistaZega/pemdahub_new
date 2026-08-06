<?php
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { die('Unauthorized'); }

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Membaca file .env dengan cara manual yang 100% aman
$basePath = realpath(__DIR__ . '/../');
$envFile  = $basePath . '/.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}

$pdo = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}", $env['DB_USERNAME'], $env['DB_PASSWORD']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT id, content FROM lms_materials WHERE course_id=221");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $content = $r['content'];

    // 1. Bersihkan class sebelumnya (jika ada)
    $content = preg_replace('/<h1[^>]*>/', '<h1>', $content);
    $content = preg_replace('/<h2[^>]*>/', '<h2>', $content);
    $content = preg_replace('/<p[^>]*>/', '<p>', $content);
    $content = preg_replace('/<ul[^>]*>/', '<ul>', $content);
    $content = preg_replace('/<ol[^>]*>/', '<ol>', $content);
    $content = preg_replace('/<pre[^>]*>/', '<pre>', $content);

    // 2. Gunakan INLINE STYLE agar tidak diblokir oleh sistem kompresi CSS Tailwind (PurgeCSS)
    
    // Header 1 (Landasan Teori, dll)
    $content = str_replace('<h1>', '<h1 style="font-size:1.5rem; font-weight:900; color:#3730a3; margin-top:2.5rem; margin-bottom:1rem; border-bottom:2px solid #e2e8f0; padding-bottom:0.5rem;">', $content);
    
    // Header 2 (A. Algoritma, B. Kebutuhan Komponen, dll)
    $content = str_replace('<h2>', '<h2 style="font-size:1.25rem; font-weight:700; color:#1e293b; margin-top:1.5rem; margin-bottom:0.5rem;">', $content);
    
    // Paragraf
    $content = str_replace('<p>', '<p style="color:#334155; margin-bottom:1.25rem; line-height:1.7;">', $content);
    
    // Unordered List (Bullet)
    $content = str_replace('<ul>', '<ul style="list-style-type:disc; padding-left:1.5rem; margin-bottom:1.5rem; color:#334155; line-height:1.7;">', $content);
    
    // Ordered List (Angka untuk Algoritma)
    $content = str_replace('<ol>', '<ol style="list-style-type:decimal; padding-left:1.5rem; margin-bottom:1.5rem; color:#334155; line-height:1.7;">', $content);

    // 3. Perbaiki Kode Program (Menggunakan DIV dengan Inline CSS hitam legam + <br>)
    // Ubah literal \n menjadi <br> HANYA di dalam blok <code>
    $content = preg_replace_callback('/<code[^>]*>(.*?)<\/code>/is', function($matches) {
        $code = str_replace(['\n', "\n"], '<br>', $matches[1]);
        return '<code>' . $code . '</code>';
    }, $content);

    // Ubah <pre> menjadi div box hitam (karena <pre> kadang di-reset oleh browser/framework)
    $content = str_replace('<pre>', '<div style="background-color:#0f172a; color:#34d399; padding:1.25rem; border-radius:0.75rem; margin:1.5rem 0; font-family:monospace; font-size:0.875rem; overflow-x:auto; line-height:1.6; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);"><pre style="margin:0; font-family:inherit;">', $content);
    $content = str_replace('</pre>', '</pre></div>', $content);

    // Update database
    $update = $pdo->prepare("UPDATE lms_materials SET content=? WHERE id=?");
    $update->execute([$content, $r['id']]);
}

echo "<h2>Tampilan Berhasil Dipercantik Secara Permanen!</h2>";
echo "<p>Silakan refresh halaman materi di LMS Bapak. Susunan teks, bullet angka, dan kotak kode program (warna hitam) pasti sudah muncul sempurna.</p>";
