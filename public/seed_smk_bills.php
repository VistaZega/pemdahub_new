<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\PaymentType;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentBill;

try {
    $school = School::find(3);
    if (!$school) die("School not found");

    $academicYear = AcademicYear::where('is_active', true)->first();
    if (!$academicYear) die("Academic year not found");

    $semester1 = Semester::where('academic_year_id', $academicYear->id)->where('semester_name', 'like', '%Ganjil%')->first();
    $semester2 = Semester::where('academic_year_id', $academicYear->id)->where('semester_name', 'like', '%Genap%')->first();

    $billsConfig = [
        'Reguler' => [
            10 => ['amount' => 220000, 'share' => 10000],
            11 => ['amount' => 225000, 'share' => 10000],
            12 => ['amount' => 230000, 'share' => 10000],
        ],
        'Industri' => [
            10 => ['amount' => 250000, 'share' => 40000],
            11 => ['amount' => 250000, 'share' => 35000],
            12 => ['amount' => 250000, 'share' => 30000],
        ]
    ];

    // 1. Create PaymentTypes
    $paymentTypes = [];
    foreach ($billsConfig as $type => $levels) {
        foreach ($levels as $level => $config) {
            $yayasanShare = $config['amount'] - $config['share'];
            
            $pt = PaymentType::firstOrCreate([
                'school_id' => $school->id,
                'type_name' => "SPP Kelas {$level} {$type} 26/27",
            ], [
                'type_code' => "SPP-{$level}-" . strtoupper(substr($type, 0, 3)),
                'description' => "Uang Sekolah Kelas {$level} Tipe {$type}",
                'amount' => $config['amount'],
                'yayasan_share_amount' => $yayasanShare,
                'is_recurring' => true,
                'allow_installment' => false,
                'is_active' => true,
            ]);
            
            $paymentTypes["{$type}_{$level}"] = $pt;
            echo "Created Payment Type: {$pt->type_name} (Amount: {$pt->amount}, Yayasan: {$pt->yayasan_share_amount})<br>";
        }
    }

    // 2. Loop Students
    $classrooms = Classroom::where('school_id', $school->id)
        ->where('academic_year_id', $academicYear->id)
        ->get();

    $count = 0;
    foreach ($classrooms as $classroom) {
        // Find level from class_level or class_name
        $level = 10;
        if (isset($classroom->level)) {
            $level = $classroom->level;
        } else if (isset($classroom->class_level)) {
            $level = $classroom->class_level;
        } else if (preg_match('/X{1,3}/', $classroom->class_name, $matches)) {
            $roman = $matches[0];
            if ($roman == 'X') $level = 10;
            if ($roman == 'XI') $level = 11;
            if ($roman == 'XII') $level = 12;
        }
        
        $type = 'Reguler';
        if (isset($classroom->class_type) && stripos($classroom->class_type, 'industri') !== false) {
            $type = 'Industri';
        }
        
        if (!isset($paymentTypes["{$type}_{$level}"])) {
            continue;
        }
        
        $pt = $paymentTypes["{$type}_{$level}"];
        
        $students = Student::where('classroom_id', $classroom->id)->where('status', 'aktif')->get();
        
        foreach ($students as $student) {
            // Create bills for 12 months (July to June)
            for ($m = 1; $m <= 12; $m++) {
                $monthStr = str_pad($m, 2, '0', STR_PAD_LEFT);
                $yearNameParts = explode('/', $academicYear->name);
                if ($m >= 7) {
                    $year = trim($yearNameParts[0]); 
                    $sem = $semester1;
                } else {
                    $year = trim($yearNameParts[1] ?? ($yearNameParts[0]+1));
                    $sem = $semester2;
                }
                
                StudentBill::updateOrCreate([
                    'student_id' => $student->id,
                    'payment_type_id' => $pt->id,
                    'month' => $monthStr,
                    'year' => $year,
                ], [
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $sem ? $sem->id : 1,
                    'amount' => $pt->amount,
                    'yayasan_share_amount' => $pt->yayasan_share_amount,
                    'due_date' => "$year-$monthStr-10",
                    'is_paid' => false,
                    'paid_amount' => 0,
                ]);
                $count++;
            }
        }
    }
    echo "<h3>Success! Created $count bills for SMK students!</h3>";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
