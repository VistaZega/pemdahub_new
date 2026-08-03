<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\PaymentType;
use App\Models\School;

$studentNames = [
    'BELINDA ANGELIA BU\'ULOLO',
    'KAYLEEN KEZIA TELAUMBANUA',
    'MARIO TEGUH BATE\'E',
    'PUTRI ANIISA ZAHRA. H',
    'WINNER AS BOWONAMA MENDROFA',
    'STEFANY GLORIA TAFONA\'O',
    'ALVAN ZEBUA'
];

$school = School::where('name', 'like', '%SMP Swasta Pembda 2%')->first();
$sppType = PaymentType::where('school_id', $school->id)->where('type_name', 'like', '%SPP%')->first();

if (!$sppType) {
    echo "SPP Payment Type not found for this school.\n";
    exit;
}

echo "Generating bills for " . count($studentNames) . " students...\n";

foreach ($studentNames as $name) {
    // Find the student by name ignoring case and exact punctuation
    $student = Student::where('full_name', 'like', "%" . str_replace("'", "%", $name) . "%")
        ->where('school_id', $school->id)
        ->first();
        
    if (!$student) {
        echo "Siswa tidak ditemukan: $name\n";
        continue;
    }
    
    $classroom = clone $student->currentClassroom;
    $activeClass = $classroom->first();
    
    if (!$activeClass) {
        echo "Siswa $name tidak memiliki kelas aktif.\n";
        continue;
    }
    
    // Find another active student in the same classroom to copy their bills
    $classmate = Student::whereHas('classrooms', function($q) use ($activeClass) {
            $q->where('classrooms.id', $activeClass->id)
              ->where('student_classes.status', 'aktif');
        })
        ->where('students.id', '!=', $student->id)
        ->where('students.status', 'aktif')
        ->first();
        
    if (!$classmate) {
        echo "Tidak ada teman sekelas untuk referensi tagihan $name.\n";
        continue;
    }
    
    // Get ALL SPP bills of the classmate for the current academic year
    // We assume the active academic year is the one the student_classes belongs to
    $academicYearId = DB::table('student_classes')
        ->where('student_id', $student->id)
        ->where('classroom_id', $activeClass->id)
        ->where('status', 'aktif')
        ->value('academic_year_id');

    $classmateBills = StudentBill::where('student_id', $classmate->id)
        ->where('payment_type_id', $sppType->id)
        ->where('academic_year_id', $academicYearId)
        ->get();
        
    if ($classmateBills->isEmpty()) {
        echo "Teman sekelas ({$classmate->full_name}) tidak memiliki tagihan SPP. Tidak dapat menyalin.\n";
        continue;
    }
    
    echo "Siswa $name (Referensi copy dari teman sekelas: {$classmate->full_name})\n";
    
    $created = 0;
    foreach ($classmateBills as $cbill) {
        // Ensure bill doesn't already exist
        $exists = StudentBill::where('student_id', $student->id)
            ->where('payment_type_id', $cbill->payment_type_id)
            ->where('academic_year_id', $cbill->academic_year_id)
            ->where('month', $cbill->month)
            ->where('year', $cbill->year)
            ->exists();
            
        if (!$exists) {
            StudentBill::create([
                'student_id' => $student->id,
                'payment_type_id' => $cbill->payment_type_id,
                'academic_year_id' => $cbill->academic_year_id,
                'semester_id' => $cbill->semester_id,
                'month' => $cbill->month,
                'year' => $cbill->year,
                'amount' => $cbill->amount,
                'yayasan_share_amount' => $cbill->yayasan_share_amount,
                'paid_amount' => 0,
                'status' => 'belum_bayar',
                'notes' => $cbill->notes,
                'due_date' => $cbill->due_date,
            ]);
            $created++;
        }
    }
    
    echo "  => Berhasil membuat $created tagihan SPP bulanan.\n";
}
echo "Selesai.\n";
