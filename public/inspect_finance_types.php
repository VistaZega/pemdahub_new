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

echo "=== PAYMENT TYPES ===\n";
$pt = $pdo->query("SELECT id, school_id, type_name, amount, is_recurring FROM payment_types")->fetchAll();
foreach ($pt as $p) {
    echo "PT #{$p['id']} (School {$p['school_id']}): {$p['type_name']} - Rp " . number_format($p['amount']) . " (Recurring: {$p['is_recurring']})\n";
}

echo "\n=== CELESTE STUDENT ===\n";
$celeste = $pdo->query("SELECT id, full_name, school_id FROM students WHERE full_name LIKE '%CELESTE%'")->fetch();
if ($celeste) {
    echo "ID: {$celeste['id']} | Name: {$celeste['full_name']} | School ID: {$celeste['school_id']}\n\n";

    echo "--- CELESTE BILLS ---\n";
    $cBills = $pdo->prepare("SELECT b.id, b.payment_type_id, b.month, b.year, b.amount, b.paid_amount, b.status, pt.type_name FROM student_bills b LEFT JOIN payment_types pt ON b.payment_type_id = pt.id WHERE b.student_id = ? ORDER BY b.year ASC, b.month ASC, b.id ASC");
    $cBills->execute([$celeste['id']]);
    foreach ($cBills->fetchAll() as $b) {
        echo "Bill #{$b['id']}: Month {$b['month']}/{$b['year']} | Type: {$b['type_name']} (ID {$b['payment_type_id']}) | Amt: {$b['amount']} | Paid: {$b['paid_amount']} | Status: {$b['status']}\n";
    }

    echo "\n--- CELESTE PAYMENTS ---\n";
    $cPays = $pdo->prepare("SELECT p.id, p.bill_id, p.amount_paid, p.payment_date, p.receipt_number, b.month, pt.type_name FROM payments p LEFT JOIN student_bills b ON p.bill_id = b.id LEFT JOIN payment_types pt ON b.payment_type_id = pt.id WHERE p.student_id = ?");
    $cPays->execute([$celeste['id']]);
    foreach ($cPays->fetchAll() as $p) {
        echo "Payment #{$p['id']}: Bill #{$p['bill_id']} | Type: {$p['type_name']} | Month: {$p['month']} | Paid: Rp " . number_format($p['amount_paid']) . " | Rec: {$p['receipt_number']} | Date: {$p['payment_date']}\n";
    }
}
