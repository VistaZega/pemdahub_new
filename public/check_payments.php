<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $month = 8;
    $year = 2026;
    $schoolId = 1;

    $payments = \App\Models\Payment::with(['bill.paymentType', 'student'])
        ->whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
        ->whereMonth('payment_date', $month)
        ->whereYear('payment_date', $year)
        ->where('is_verified', true)
        ->get();

    echo "Total Payments Found: " . $payments->count() . "\n";
    $grossIncome = 0;
    foreach ($payments as $p) {
        echo "Payment ID: " . $p->id . " | Amount Paid: " . $p->amount_paid . " | Bill Amount: " . ($p->bill ? $p->bill->amount : 'N/A') . " | Method: " . $p->payment_method . "\n";
        $grossIncome += $p->amount_paid;
    }
    echo "Gross Income Calculated: " . $grossIncome . "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
