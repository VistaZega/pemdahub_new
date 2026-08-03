<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $school = App\Models\School::where('name', 'like', '%SMP Swasta Pembda 2%')->first();
    if (!$school) {
        echo "School not found.";
        exit;
    }

    $activeStudents = App\Models\Student::where('school_id', $school->id)->where('status', 'aktif')->get();

    $studentsWithoutBill = [];

    foreach ($activeStudents as $student) {
        $hasBill = App\Models\StudentBill::where('student_id', $student->id)
            ->where('month', 7)
            ->where('year', 2026)
            ->exists();
            
        if (!$hasBill) {
            $className = '-';
            $classroom = $student->currentClassroom;
            if ($classroom && $classroom->first()) {
                $className = $classroom->first()->class_name;
            }
            $studentsWithoutBill[] = [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'name' => $student->full_name,
                'class' => $className
            ];
        }
    }

    echo "Total Siswa Aktif: " . $activeStudents->count() . "\n";
    echo "Total Siswa Tanpa Tagihan Juli 2026: " . count($studentsWithoutBill) . "\n\n";

    // Urutkan berdasarkan kelas lalu nama
    usort($studentsWithoutBill, function($a, $b) {
        if ($a['class'] == $b['class']) {
            return strcmp($a['name'], $b['name']);
        }
        return strcmp($a['class'], $b['class']);
    });

    foreach ($studentsWithoutBill as $index => $s) {
        $num = $index + 1;
        echo "{$num}. {$s['name']} (Kelas: {$s['class']})\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

