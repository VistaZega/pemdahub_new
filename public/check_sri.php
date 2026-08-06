<?php
header('Content-Type: text/plain; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Pengecekan Data: Sri Rahayu Tanjung, S.Pd ===\n\n";

$teachers = App\Models\Teacher::where('full_name', 'like', '%Sri Rahayu Tanjung%')->get();
echo "1. Di tabel 'teachers': " . $teachers->count() . " data ditemukan.\n";
foreach ($teachers as $t) {
    echo "   - ID: {$t->id}, Nama: {$t->full_name}, School ID: {$t->school_id}, Status Aktif: {$t->is_active}, Employee ID: {$t->employee_id}\n";
}

$employees = App\Models\Employee::where('full_name', 'like', '%Sri Rahayu Tanjung%')->get();
echo "\n2. Di tabel 'employees': " . $employees->count() . " data ditemukan.\n";
foreach ($employees as $e) {
    echo "   - ID: {$e->id}, Nama: {$e->full_name}, School ID: {$e->school_id}, Status Aktif: {$e->status}, Type: {$e->employee_type}\n";
}
