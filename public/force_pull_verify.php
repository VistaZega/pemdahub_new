<?php
/**
 * FORCE GIT PULL + VERIFY - Paksa tarik kode terbaru dari GitHub
 */
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { die('Unauthorized'); }

set_time_limit(60);
$basePath = realpath(__DIR__ . '/../');
chdir($basePath);

echo "<pre style='background:#0d1117;color:#c9d1d9;padding:20px;font-family:monospace;font-size:12px;line-height:1.6'>";
echo "=== FORCE GIT PULL & VERIFY ===\n\n";

// Force pull
echo "--- Git Status ---\n";
echo shell_exec('git log --oneline -3 2>&1') . "\n";

echo "--- Force Pull ---\n";
echo shell_exec('git fetch origin main 2>&1') . "\n";
echo shell_exec('git reset --hard origin/main 2>&1') . "\n";

echo "--- Latest Commit ---\n";
echo shell_exec('git log --oneline -3 2>&1') . "\n";

// Now verify DB
$envFile = $basePath . '/.env';
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
        $env['DB_USERNAME'], $env['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "\n--- Verifikasi Database ---\n";
    
    $mods = $pdo->query("SELECT COUNT(*) as c FROM lms_modules WHERE course_id=101")->fetch()['c'];
    $mats = $pdo->query("SELECT COUNT(*) as c FROM lms_materials WHERE course_id=101")->fetch()['c'];
    $asgn = $pdo->query("SELECT COUNT(*) as c FROM lms_assignments WHERE course_id=101")->fetch()['c'];
    $quiz = $pdo->query("SELECT COUNT(*) as c FROM lms_quizzes WHERE course_id=101")->fetch()['c'];
    
    echo "Modules    : {$mods} / 17\n";
    echo "Materials  : {$mats} / 17\n";
    echo "Assignments: {$asgn} / 17\n";
    echo "Quizzes    : {$quiz} / 17\n";
    
    if ($mods == 17) {
        echo "\n✅ SUKSES! 17 modul sudah ada di database production!\n";
        echo "Silakan buka: https://perguruanpembda.com/guru/lms\n";
    } else {
        echo "\n⚠ Modul = {$mods}. Kunjungi /run_seeder.php?secret=pembda99 untuk inject.\n";
    }
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
