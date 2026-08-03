<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\School;
use App\Models\AcademicYear;

// Cari sekolah SMK Pembda
$school = School::where('name', 'like', '%SMK%')->first();
if (!$school) {
    echo "SMK School not found.\n";
    exit;
}

$activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::orderBy('year', 'desc')->first();

// Query total aktif
$totalSiswaAktif = Student::where('school_id', $school->id)
    ->where('status', 'aktif')
    ->count();

// Ambil data siswa yang belum ber-rombel
$studentsWithoutRombel = Student::where('school_id', $school->id)
    ->where('status', 'aktif')
    ->whereDoesntHave('classrooms', function ($q) use ($activeYear) {
        $q->where('student_classes.academic_year_id', $activeYear->id)
          ->where('student_classes.status', 'aktif');
    })
    ->orderBy('full_name')
    ->get();

$output = "=== DAFTAR SISWA BELUM BER-ROMBEL ===\n";
$output .= "Unit: " . $school->name . "\n";
$output .= "Tahun Ajaran: " . $activeYear->year . "\n\n";
$output .= "Total Siswa Aktif: " . $totalSiswaAktif . "\n";
$output .= "Siswa Belum Ber-Rombel: " . $studentsWithoutRombel->count() . "\n\n";

foreach ($studentsWithoutRombel as $index => $student) {
    $num = $index + 1;
    $output .= "{$num}. {$student->full_name} (NISN: {$student->nisn})\n";
}

file_put_contents(__DIR__ . '/daftar_smk.txt', $output);
echo "File created at daftar_smk.txt";
