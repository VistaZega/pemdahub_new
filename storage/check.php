$s = \App\Models\Student::where('name', 'like', '%BEATRIX%')->first();
if ($s) {
    echo 'Student: ' . $s->name . ', ID: ' . $s->id . "\n";
    echo 'Status: ' . $s->status . "\n";
    $class = $s->currentClass;
    echo 'Class: ' . ($class ? $class->name : 'None') . "\n";
    
    // Check payments/bills
    $bills = $s->bills()->with('paymentType')->get();
    echo 'Bills Count: ' . $bills->count() . "\n";
    foreach($bills as $b) {
        $typeName = $b->paymentType ? $b->paymentType->name : 'Unknown';
        echo ' - ' . $typeName . ': ' . $b->amount . ' (' . $b->status . ')' . "\n";
    }
} else {
    echo 'Student not found' . "\n";
}
