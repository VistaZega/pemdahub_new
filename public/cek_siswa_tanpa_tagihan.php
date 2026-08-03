<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = App\Models\Student::where('full_name', 'like', '%AHMAD SYAHRUL%')->first();
if (!$student) {
    echo "Student not found.";
    exit;
}

$bills = App\Models\StudentBill::where('student_id', $student->id)->get();
echo "Bills for " . $student->full_name . ":\n";
foreach ($bills as $bill) {
    echo "- Month: " . $bill->month . ", Year: " . $bill->year . ", Status: " . $bill->status . "\n";
}
