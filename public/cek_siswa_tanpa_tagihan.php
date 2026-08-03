<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$studentNames = [
    'BELINDA ANGELIA BU\'ULOLO',
    'ALVAN ZEBUA'
];

foreach ($studentNames as $name) {
    echo "=== Investigasi Siswa: $name ===\n";
    
    // Use like to handle apostrophe variations if any, or just find by string
    $student = App\Models\Student::where('full_name', 'like', "%" . str_replace("'", "%", $name) . "%")->first();
    
    if (!$student) {
        echo "Siswa tidak ditemukan.\n\n";
        continue;
    }
    
    echo "ID Siswa: " . $student->id . " | Status: " . $student->status . " | Dibuat: " . $student->created_at . "\n";
    
    // Check classes
    $classes = DB::table('student_classes')
        ->join('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
        ->join('academic_years', 'student_classes.academic_year_id', '=', 'academic_years.id')
        ->where('student_classes.student_id', $student->id)
        ->select('student_classes.status', 'student_classes.created_at', 'student_classes.updated_at', 'classrooms.class_name', 'academic_years.year')
        ->get();
        
    echo "Riwayat Kelas:\n";
    foreach ($classes as $c) {
        echo "- Kelas: {$c->class_name} | TP: {$c->year} | Status: {$c->status} | Tgl Dibuat: {$c->created_at}\n";
    }
    
    // Check any bills ever created for this student
    $allBillsCount = App\Models\StudentBill::where('student_id', $student->id)->count();
    echo "Total Semua Tagihan Siswa (Semua bulan/tahun/jenis): " . $allBillsCount . "\n";
    
    echo "\n";
}

// Check activity logs to see if someone generated bills for Class IX recently and exactly when,
// or if someone deleted bills for these specific students
$recentBillLogs = \App\Models\ActivityLog::where('description', 'like', '%tagihan%')
    ->orderBy('id', 'desc')
    ->take(10)
    ->get();

echo "=== 10 Log Aktivitas Tagihan Terakhir ===\n";
foreach ($recentBillLogs as $log) {
    echo "[{$log->created_at}] User {$log->user_id} ({$log->action}): {$log->description}\n";
}

