<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Query the 22 students who paid with a discount in August
$students = DB::select("
    SELECT DISTINCT s.full_name, s.nisn, pt.type_name, sb.amount as tagihan_diskon, pt.amount as tagihan_normal
    FROM payments p
    JOIN student_bills sb ON p.bill_id = sb.id
    JOIN payment_types pt ON sb.payment_type_id = pt.id
    JOIN students s ON p.student_id = s.id
    WHERE sb.month = 8 AND sb.amount < pt.amount
    ORDER BY s.full_name ASC
");

echo json_encode($students);
