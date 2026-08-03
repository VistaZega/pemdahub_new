<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\School;
use App\Models\StudentBill;

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

$schoolId = School::where('name', 'like', '%SMP Swasta Pembda 2%')->first()->id;

// Logic 72 (SPP Juli, Aktif)
$julyBills = StudentBill::whereHas('student', function($q) use ($schoolId) {
        $q->where('school_id', $schoolId)->where('status', 'aktif');
    })
    ->where('month', 7)
    ->where('year', 2026)
    ->where('status', '!=', 'lunas')
    ->get();

$julyStudentIds = $julyBills->pluck('student_id')->toArray();

// Logic 65 (Konsolidasi Keuangan, current month defaults to August)
$month = date('n'); // or 8
$year = 2026;

$consolidationBills = StudentBill::whereHas('student', function($q) use ($schoolId) {
        $q->where('school_id', $schoolId);
    })
    ->where('month', $month)
    ->where('year', $year)
    ->where('status', '!=', 'lunas')
    ->get();

$consolidationStudentIds = $consolidationBills->pluck('student_id')->toArray();

echo "Bulan default konsolidasi: $month\n";
echo "Total Juli (72): " . count($julyStudentIds) . "\n";
echo "Total Konsolidasi ($month) (65?): " . count($consolidationStudentIds) . "\n\n";

$diffIds = array_diff($julyStudentIds, $consolidationStudentIds);

echo "Perbedaan (Ada di Juli, tapi tidak ada di Konsolidasi):\n";
foreach ($diffIds as $id) {
    $student = \App\Models\Student::find($id);
    echo "- ID: {$id} | Nama: {$student->full_name}\n";
}

// What if the user explicitly picked July in Konsolidasi, but it still showed 65?
$consolidationJulyBills = StudentBill::whereHas('student', function($q) use ($schoolId) {
        $q->where('school_id', $schoolId);
    })
    ->where('month', 7)
    ->where('year', 2026)
    ->where('status', '!=', 'lunas')
    ->get();

$consolidationJulyStudentIds = $consolidationJulyBills->pluck('student_id')->toArray();

echo "\n\nJika Konsolidasi difilter Bulan Juli (harusnya sama dengan 72 jika tdk ada beda):\n";
echo "Total Konsolidasi Juli: " . count($consolidationJulyStudentIds) . "\n";
$diffJulyIds = array_diff($julyStudentIds, $consolidationJulyStudentIds);
foreach ($diffJulyIds as $id) {
    $student = \App\Models\Student::find($id);
    echo "- ID: {$id} | Nama: {$student->full_name}\n";
}
