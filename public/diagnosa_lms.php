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
$pdo = new PDO("mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4", $env['DB_USERNAME'], $env['DB_PASSWORD']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<pre style='background:#0d1117;color:#c9d1d9;padding:20px;font-family:monospace;font-size:12px'>";
echo "=== VERIFIKASI DATABASE PRODUCTION ===\n\n";

// Modul
$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_modules WHERE course_id=101");
$mods = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
echo "lms_modules (course 101): {$mods}\n";

// Materials
$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_materials WHERE course_id=101");
$mats = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
echo "lms_materials (course 101): {$mats}\n";

// Assignments
$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_assignments WHERE course_id=101");
$asgn = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
echo "lms_assignments (course 101): {$asgn}\n";

// Quizzes
$stmt = $pdo->query("SELECT COUNT(*) as c FROM lms_quizzes WHERE course_id=101");
$quiz = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
echo "lms_quizzes (course 101): {$quiz}\n";

// List modules
echo "\nDaftar Modul:\n";
$stmt = $pdo->query("SELECT id, sequence, title, is_active FROM lms_modules WHERE course_id=101 ORDER BY sequence");
while ($m = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  [{$m['sequence']}] ID:{$m['id']} | Active:{$m['is_active']} | {$m['title']}\n";
}

echo "\n=== SELESAI ===\n";
echo "</pre>";
