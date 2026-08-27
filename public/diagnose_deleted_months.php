<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$possibleEnvPaths = [
    __DIR__ . '/.env',
    __DIR__ . '/../.env',
    __DIR__ . '/pembdahub/.env',
    '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/.env',
    '/home/u474310197/domains/perguruanpembda.com/public_html/.env',
];

$dbHost = '127.0.0.1'; $dbPort = '3306'; $dbName = 'u474310197_database'; $dbUser = 'u474310197_user'; $dbPass = '';

foreach ($possibleEnvPaths as $envPath) {
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#')) continue;
            if (str_contains($line, '=')) {
                list($key, $val) = explode('=', $line, 2);
                if (trim($key) === 'DB_HOST') $dbHost = trim($val, " \"'");
                if (trim($key) === 'DB_PORT') $dbPort = trim($val, " \"'");
                if (trim($key) === 'DB_DATABASE') $dbName = trim($val, " \"'");
                if (trim($key) === 'DB_USERNAME') $dbUser = trim($val, " \"'");
                if (trim($key) === 'DB_PASSWORD') $dbPass = trim($val, " \"'");
            }
        }
        break;
    }
}

$pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== DIAGNOSE MISSING MONTH 8 BILLS ===\n";
// Find active students missing Month 8 SPP bill
$stmt = $pdo->query("SELECT s.id, s.full_name, c.name as class_name, s.school_id FROM students s LEFT JOIN classrooms c ON s.classroom_id = c.id WHERE s.is_active = 1 AND s.id NOT IN (SELECT student_id FROM student_bills WHERE month = 8 AND payment_type_id IN (1,5,10,16,17,18,19,20,21)) LIMIT 20");
$missingAug = $stmt->fetchAll();

echo "Total Active Students Missing Month 8 SPP Bill: " . count($missingAug) . " (showing sample):\n";
foreach ($missingAug as $m) {
    echo " - Student ID {$m['id']}: {$m['full_name']} ({$m['class_name']})\n";
}

echo "\n=== ALL CREATED PAYMENTS IN ACTIVITY LOGS (SAMPLE) ===\n";
$payLogs = $pdo->query("SELECT id, model_id, changes, created_at FROM activity_logs WHERE model_type LIKE '%Payment%' AND action = 'created' ORDER BY id DESC LIMIT 20")->fetchAll();
foreach ($payLogs as $pl) {
    echo "Log #{$pl['id']}: ModelID {$pl['model_id']} | Changes: {$pl['changes']}\n";
}

echo "\n=== CELESTE ALL LOGS IN DETAIL ===\n";
$celesteLogs = $pdo->query("SELECT * FROM activity_logs WHERE changes LIKE '%1831%' OR model_id = 1831 ORDER BY id ASC")->fetchAll();
foreach ($celesteLogs as $cl) {
    echo "Log #{$cl['id']}: Action={$cl['action']} | Model={$cl['model_type']} | User={$cl['user_id']} | Changes={$cl['changes']}\n";
}
