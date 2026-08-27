<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
$possibleEnvPaths = [
    __DIR__ . '/.env',
    __DIR__ . '/../.env',
    __DIR__ . '/pembdahub/.env',
    '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/.env',
    '/home/u474310197/domains/perguruanpembda.com/public_html/.env',
    'd:/laragon/www/pembdahub/.env',
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

try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\Throwable $e) {
    die("DB Error: " . $e->getMessage());
}

echo "<h2>DEBUG AUDIT LOGS</h2>";

// Count total activity_logs
$totalLogs = $pdo->query("SELECT COUNT(*) as cnt FROM activity_logs")->fetch()['cnt'];
echo "<p>Total Activity Logs in DB: <b>{$totalLogs}</b></p>";

// Group by model_type & action
$stmt = $pdo->query("SELECT model_type, action, COUNT(*) as count FROM activity_logs GROUP BY model_type, action");
$groups = $stmt->fetchAll();

echo "<table border='1' cellpadding='5'><tr><th>Model Type</th><th>Action</th><th>Count</th></tr>";
foreach ($groups as $g) {
    echo "<tr><td>{$g['model_type']}</td><td>{$g['action']}</td><td>{$g['count']}</td></tr>";
}
echo "</table>";

// Check sample deleted logs
$stmt2 = $pdo->query("SELECT * FROM activity_logs WHERE action = 'deleted' ORDER BY id DESC LIMIT 10");
$samples = $stmt2->fetchAll();

echo "<h3>Sample Deleted Logs (Last 10)</h3><pre>";
print_r($samples);
echo "</pre>";

// Check CELESTE payments or bills in DB
$celeste = $pdo->query("SELECT s.id, s.full_name, c.name as class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.full_name LIKE '%CELESTE%'")->fetchAll();
echo "<h3>Celeste Student Record</h3><pre>";
print_r($celeste);
echo "</pre>";

if (!empty($celeste)) {
    $cId = $celeste[0]['id'];
    $cBills = $pdo->query("SELECT * FROM student_bills WHERE student_id = {$cId}")->fetchAll();
    echo "<h3>Celeste Bills Currently in DB</h3><pre>";
    print_r($cBills);
    echo "</pre>";

    $cPayments = $pdo->query("SELECT * FROM payments WHERE student_id = {$cId}")->fetchAll();
    echo "<h3>Celeste Payments Currently in DB</h3><pre>";
    print_r($cPayments);
    echo "</pre>";
}
