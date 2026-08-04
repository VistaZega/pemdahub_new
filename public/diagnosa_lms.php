<?php
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { die('Unauthorized'); }

$basePath = realpath(__DIR__ . '/../');
if (!$basePath) $basePath = __DIR__;

echo "<pre style='background:#0d1117;color:#c9d1d9;padding:20px;font-family:monospace;font-size:12px;line-height:1.6'>";
echo "=== DIAGNOSA LMS PRODUCTION v3 ===\n";
echo "Base Path: {$basePath}\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

// Bootstrap Laravel sederhana, langsung koneksi DB tanpa full framework
$envFile = $basePath . '/.env';
if (!file_exists($envFile)) {
    // coba satu level lebih atas
    $basePath = realpath(__DIR__ . '/../../');
    $envFile = $basePath . '/.env';
}

if (!file_exists($envFile)) {
    echo "ERROR: .env tidak ditemukan. Direktori coba: " . $basePath . "\n";
    $dirs = scandir(realpath(__DIR__ . '/../'));
    foreach($dirs as $d) echo "  $d\n";
    die();
}

// Parse .env
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || strpos($line,'#')===0 || strpos($line,'=')===false) continue;
    [$k,$v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db   = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

echo "DB: {$db} @ {$host}:{$port} (user:{$user})\n\n";

// Koneksi langsung dengan PDO
try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Koneksi DB: BERHASIL\n\n";
} catch (\Exception $e) {
    echo "Koneksi DB GAGAL: " . $e->getMessage() . "\n";
    die();
}

// 1. Cek course
echo "--- 1. Course ID 101 ---\n";
$stmt = $pdo->query("SELECT id, title, is_active FROM lms_courses WHERE id=101");
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if ($course) {
    echo "DITEMUKAN: {$course['title']} | is_active: {$course['is_active']}\n";
} else {
    echo "TIDAK DITEMUKAN! Cek semua course:\n";
    $stmt2 = $pdo->query("SELECT id, title FROM lms_courses ORDER BY id LIMIT 10");
    while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo "  ID:{$r['id']} | {$r['title']}\n";
    }
}

// 2. Cek modules
echo "\n--- 2. lms_modules (course_id=101) ---\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM lms_modules WHERE course_id=101");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$stmt = $pdo->query("SELECT COUNT(*) as del FROM lms_modules WHERE course_id=101 AND deleted_at IS NOT NULL");
$deleted = $stmt->fetch(PDO::FETCH_ASSOC)['del'];
echo "Total: {$total} | Soft-deleted: {$deleted} | Active: " . ($total - $deleted) . "\n";

if ($total > 0) {
    $stmt = $pdo->query("SELECT id, sequence, title, is_active, deleted_at FROM lms_modules WHERE course_id=101 ORDER BY sequence");
    while ($m = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $del = $m['deleted_at'] ? "[DEL]" : "[OK] ";
        echo "  {$del} ID:{$m['id']} | Seq:{$m['sequence']} | Active:{$m['is_active']} | " . substr($m['title'],0,45) . "\n";
    }
} else {
    echo "!!! MODUL KOSONG - Seeder belum dijalankan di production !!!\n";
}

// 3. Cek materials
echo "\n--- 3. lms_materials ---\n";
$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_materials WHERE course_id=101");
echo "Total materials: " . $stmt->fetch(PDO::FETCH_ASSOC)['c'] . "\n";

// 4. Cek assignments
$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_assignments WHERE course_id=101");
echo "Total assignments: " . $stmt->fetch(PDO::FETCH_ASSOC)['c'] . "\n";

// 5. Cek quizzes
$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_quizzes WHERE course_id=101");
echo "Total quizzes: " . $stmt->fetch(PDO::FETCH_ASSOC)['c'] . "\n";

echo "\n=== SELESAI - Jika modul=0 berarti Seeder belum jalan di production ===\n";
echo "</pre>";
