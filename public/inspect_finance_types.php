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

echo "<h2>1. ALL PAYMENT TYPES</h2><pre>";
$pt = $pdo->query("SELECT id, school_id, type_name, amount, is_recurring, is_active FROM payment_types")->fetchAll();
print_r($pt);
echo "</pre>";

echo "<h2>2. CELESTE BILLS & PAYMENTS CURRENTLY IN DB</h2>";
$celeste = $pdo->query("SELECT id, full_name, school_id FROM students WHERE full_name LIKE '%CELESTE%'")->fetch();
if ($celeste) {
    echo "Siswa: {$celeste['full_name']} (ID: {$celeste['id']}, School: {$celeste['school_id']})<br>";
    $cBills = $pdo->prepare("SELECT b.*, pt.type_name FROM student_bills b LEFT JOIN payment_types pt ON b.payment_type_id = pt.id WHERE b.student_id = ? ORDER BY b.year ASC, b.month ASC, b.id ASC");
    $cBills->execute([$celeste['id']]);
    $bList = $cBills->fetchAll();
    echo "<h3>Bills (" . count($bList) . ")</h3><pre>";
    print_r($bList);
    echo "</pre>";

    $cPays = $pdo->prepare("SELECT p.*, b.month, b.year, pt.type_name FROM payments p LEFT JOIN student_bills b ON p.bill_id = b.id LEFT JOIN payment_types pt ON b.payment_type_id = pt.id WHERE p.student_id = ?");
    $cPays->execute([$celeste['id']]);
    $pList = $cPays->fetchAll();
    echo "<h3>Payments (" . count($pList) . ")</h3><pre>";
    print_r($pList);
    echo "</pre>";
}

echo "<h2>3. CELESTE ACTIVITY LOGS (ANY MODEL)</h2><pre>";
if ($celeste) {
    $cLogs = $pdo->prepare("SELECT * FROM activity_logs WHERE changes LIKE ? OR model_id = ? ORDER BY id DESC LIMIT 50");
    $cLogs->execute(["%{$celeste['id']}%", $celeste['id']]);
    print_r($cLogs->fetchAll());
}
echo "</pre>";

echo "<h2>4. SAMPLE ACTIVITY LOGS WITH 'amount_paid' OR 'Payment'</h2><pre>";
$payLogs = $pdo->query("SELECT id, action, model_type, model_id, changes FROM activity_logs WHERE (model_type LIKE '%Payment%' OR changes LIKE '%amount_paid%') AND action IN ('deleted','created') ORDER BY id DESC LIMIT 20")->fetchAll();
print_r($payLogs);
echo "</pre>";
