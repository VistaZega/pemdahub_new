<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Check duplicate bills
$duplicateBills = DB::select("
    SELECT student_id, payment_type_id, month, year, COUNT(*) as c 
    FROM student_bills 
    GROUP BY student_id, payment_type_id, month, year 
    HAVING c > 1
");

echo "Duplicate Bills: " . count($duplicateBills) . "\n";

// 2. Check duplicate payments
$duplicatePayments = DB::select("
    SELECT bill_id, student_id, amount_paid, payment_date, COUNT(*) as c
    FROM payments
    GROUP BY bill_id, student_id, amount_paid, payment_date
    HAVING c > 1
");

echo "Duplicate Payments: " . count($duplicatePayments) . "\n";
