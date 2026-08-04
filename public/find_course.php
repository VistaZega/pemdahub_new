<?php
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { die('Unauthorized'); }

$basePath = realpath(__DIR__ . '/../');
$envFile  = $basePath . '/.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}

try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
        $env['DB_USERNAME'],
        $env['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<pre style='background:#0d1117;color:#c9d1d9;padding:20px;font-family:monospace;font-size:13px;line-height:1.8'>";
    echo "=== CARI KURSUS GURU ===\n\n";

    // Cari kursus berdasarkan nama
    echo "--- Cari 'Mikrokontroler' ---\n";
    $stmt = $pdo->query("SELECT id, course_name, teacher_id, is_active FROM lms_courses WHERE course_name LIKE '%Mikro%'");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$r['id']} | Guru: {$r['teacher_id']} | Aktif: {$r['is_active']} | Nama: {$r['course_name']}\n";
    }

    // Cari semua kursus
    echo "\n--- 10 Kursus Terakhir ---\n";
    $stmt = $pdo->query("SELECT id, course_name, teacher_id FROM lms_courses ORDER BY id DESC LIMIT 10");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$r['id']} | Guru: {$r['teacher_id']} | Nama: {$r['course_name']}\n";
    }
    
    echo "</pre>";
} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}
