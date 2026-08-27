<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Query 1: Total Siswa Yang Membayar Agustus (Any payment for month = 8)
$totalMembayarAgustus = DB::selectOne("
    SELECT COUNT(DISTINCT p.student_id) as total
    FROM payments p
    JOIN student_bills sb ON p.bill_id = sb.id
    WHERE sb.month = 8
")->total;

// Query 2: Membayar normal agustus
$membayarNormalAgustus = DB::selectOne("
    SELECT COUNT(DISTINCT p.student_id) as total
    FROM payments p
    JOIN student_bills sb ON p.bill_id = sb.id
    JOIN payment_types pt ON sb.payment_type_id = pt.id
    WHERE sb.month = 8 AND sb.amount = pt.amount
")->total;

// Query 3: Membayar dengan potongan bulan agustus
$membayarDiskonAgustus = DB::selectOne("
    SELECT COUNT(DISTINCT p.student_id) as total
    FROM payments p
    JOIN student_bills sb ON p.bill_id = sb.id
    JOIN payment_types pt ON sb.payment_type_id = pt.id
    WHERE sb.month = 8 AND sb.amount < pt.amount
")->total;

// Query 4: Potongan juli, bayar normal agustus
// Students who have a July bill (amount < default), and an August bill (amount == default) and paid the August bill.
$potonganJuliNormalAgustus = DB::selectOne("
    SELECT COUNT(DISTINCT p.student_id) as total
    FROM payments p
    JOIN student_bills sb8 ON p.bill_id = sb8.id
    JOIN payment_types pt ON sb8.payment_type_id = pt.id
    JOIN student_bills sb7 ON sb8.student_id = sb7.student_id 
        AND sb8.payment_type_id = sb7.payment_type_id 
        AND sb8.academic_year_id = sb7.academic_year_id
    WHERE sb8.month = 8 
      AND sb7.month = 7 
      AND sb7.amount < pt.amount 
      AND sb8.amount = pt.amount
")->total;

// Query 5: Transaksi ganda
// Any duplicate payments on the same bill with same amount_paid and payment_date
$transaksiGanda = DB::selectOne("
    SELECT COUNT(*) as total FROM (
        SELECT bill_id, student_id, amount_paid, payment_date, COUNT(*) as c
        FROM payments
        GROUP BY bill_id, student_id, amount_paid, payment_date
        HAVING c > 1
    ) as sub
")->total;

echo "TotalSiswaMembayarAgustus|$totalMembayarAgustus\n";
echo "NormalAgustus|$membayarNormalAgustus\n";
echo "DiskonAgustus|$membayarDiskonAgustus\n";
echo "DiskonJuliNormalAgustus|$potonganJuliNormalAgustus\n";
echo "TransaksiGanda|$transaksiGanda\n";
