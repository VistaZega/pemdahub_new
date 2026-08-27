<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');

$secret = $_REQUEST['secret'] ?? '';
if ($secret !== 'pembda99') die('Unauthorized');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Only POST allowed');
}

$possibleEnvPaths = [
    __DIR__ . '/.env',
    __DIR__ . '/../.env',
    '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/.env',
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
        PDO::ATTR_EMULATE_PREPARES => true // Important for multi-statements
    ]);
} catch (\Throwable $e) {
    die("DB Error: " . $e->getMessage());
}

$sql = file_get_contents('php://input');

if (empty($sql)) {
    die("Empty SQL payload");
}

echo "Received SQL size: " . strlen($sql) . " bytes\n";

try {
    // We need to drop current tables and run the imported SQL
    // But the SQL already has DROP TABLE IF EXISTS because it's a mysqldump!
    
    // Disable foreign keys checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
    
    // Execute the full SQL dump for the two tables
    $pdo->exec($sql);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    
    echo "SUCCESS: Financial tables successfully restored from Aug 20 backup!";
} catch (\Throwable $e) {
    echo "ERROR executing SQL: " . $e->getMessage();
}
