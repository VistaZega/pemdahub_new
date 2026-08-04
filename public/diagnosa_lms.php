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
} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}

echo "<pre style='background:#0d1117;color:#c9d1d9;padding:20px;font-family:monospace;font-size:13px;line-height:1.8'>";
echo "=== VERIFIKASI DATABASE PRODUCTION ===\n\n";

$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_modules WHERE course_id=101");
$mods = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_materials WHERE course_id=101");
$mats = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_assignments WHERE course_id=101");
$asgn = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_quizzes WHERE course_id=101");
$quiz = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

echo "Modules    : {$mods} " . ($mods == 17 ? "✓ LENGKAP" : "⚠ BELUM LENGKAP") . "\n";
echo "Materials  : {$mats} " . ($mats == 17 ? "✓ LENGKAP" : "⚠ BELUM LENGKAP") . "\n";
echo "Assignments: {$asgn} " . ($asgn == 17 ? "✓ LENGKAP" : "⚠ BELUM LENGKAP") . "\n";
echo "Quizzes    : {$quiz} " . ($quiz == 17 ? "✓ LENGKAP" : "⚠ BELUM LENGKAP") . "\n";

if ($mods > 0) {
    echo "\n--- Daftar Modul ---\n";
    $stmt = $pdo->query("SELECT id, sequence, title, is_active FROM lms_modules WHERE course_id=101 ORDER BY sequence");
    while ($m = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $icon = $m['is_active'] ? "✓" : "✗";
        echo "  [{$m['sequence']}] {$icon} " . $m['title'] . "\n";
    }
    echo "\n✅ SEEDER BERHASIL! Silakan buka LMS dan refresh halaman.\n";
} else {
    echo "\n❌ MODUL KOSONG - Seeder belum berjalan atau gagal.\n";
    echo "Coba kunjungi: /run_seeder.php?secret=pembda99\n";
}

echo "</pre>";
