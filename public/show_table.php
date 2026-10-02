<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());
if (php_sapi_name() !== 'cli' && request('secret') !== 'pembda99') die('Unauthorized');

use Illuminate\Support\Facades\DB;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔍 FORENSIK PRESENSI IBU NINISADARWATI HIA - 02 OKTOBER 2026</h2><pre>";

$teacher = Teacher::where('full_name', 'like', '%Ninisadarwati%')->first();
if (!$teacher) {
    echo "Guru Ninisadarwati tidak ditemukan!\n";
    exit;
}
echo "Guru: ID {$teacher->id} - {$teacher->full_name}\n";
echo "User ID: {$teacher->user_id}\n\n";

$classes = Classroom::where('homeroom_teacher_id', $teacher->id)->get();
echo "Kelas yang diampu sebagai Wali Kelas (" . $classes->count() . " kelas):\n";
foreach ($classes as $c) {
    echo "  - Kelas ID {$c->id}: {$c->class_name} | Entry: {$c->entry_time} | Tol: {$c->late_tolerance}m | Threshold: " . $c->getLateThreshold() . "\n";
}

// Cari juga siswa Aldi Saputra Armi Kurinci
$aldi = Student::where('full_name', 'like', '%Aldi Saputra Armi%')->first();
if ($aldi) {
    echo "\nSiswa Aldi Saputra Armi Kurinci: ID {$aldi->id}\n";
    $scs = DB::table('student_classes')
        ->join('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
        ->where('student_classes.student_id', $aldi->id)
        ->select('classrooms.id as classroom_id', 'classrooms.class_name', 'student_classes.academic_year_id', 'student_classes.status')
        ->get();
    foreach ($scs as $sc) {
        echo "  - Terdaftar di: ID {$sc->classroom_id} ({$sc->class_name}) | Status: {$sc->status}\n";
    }
}

// Sekarang cari semua kelas yang diajar atau diwali-kelasi
$targetClassId = $scs->first()?->classroom_id ?? $classes->first()?->id;
$targetClass = Classroom::find($targetClassId);

if ($targetClass) {
    echo "\n=== DETAIL PRESENSI KELAS: {$targetClass->id} - {$targetClass->class_name} ===\n";
    echo "Homeroom Teacher ID: {$targetClass->homeroom_teacher_id}\n";
    $stIds = $targetClass->students()->pluck('students.id');
    echo "Total siswa di kelas: " . $stIds->count() . "\n\n";

    $atts = DB::table('attendances')
        ->leftJoin('students', 'attendances.student_id', '=', 'students.id')
        ->leftJoin('users', 'attendances.created_by', '=', 'users.id')
        ->whereIn('attendances.student_id', $stIds)
        ->whereDate('attendances.date', '2026-10-02')
        ->select(
            'attendances.id',
            'attendances.student_id',
            'students.full_name as student_name',
            'attendances.schedule_id',
            'attendances.status',
            'attendances.time_in',
            'attendances.time_out',
            'attendances.recorded_via',
            'attendances.created_by',
            'users.name as creator_name'
        )
        ->orderBy('attendances.id', 'asc')
        ->get();

    echo "Records attendance ditemukan untuk kelas ini hari ini: " . $atts->count() . "\n";
    foreach ($atts as $a) {
        echo sprintf(
            "ID: %d | %-32s | Sched: %-5s | Status: %-10s | TimeIn: %s | Via: %-10s | By: %s (ID %s)\n",
            $a->id,
            substr($a->student_name, 0, 32),
            $a->schedule_id ?? 'NULL',
            $a->status,
            $a->time_in ?? 'NULL',
            $a->recorded_via ?? '-',
            $a->creator_name ?? 'NULL',
            $a->created_by ?? 'NULL'
        );
    }
}

// Cek reputation log milik Ibu Ninisadarwati
if ($teacher->user_id) {
    echo "\n=== REPUTATION LOGS IBU NINISADARWATI HARI INI ===\n";
    $rLogs = DB::table('reputation_logs')
        ->where('user_id', $teacher->user_id)
        ->whereDate('created_at', '2026-10-02')
        ->get();
    echo "Total: " . $rLogs->count() . " log(s)\n";
    foreach ($rLogs as $rl) {
        echo "  - [{$rl->created_at}] {$rl->category} ({$rl->points} pts) : {$rl->description}\n";
    }
}

// Cek reputation log milik Aldi Saputra Armi Kurinci
if ($aldi && $aldi->user_id) {
    echo "\n=== REPUTATION LOGS ALDI SAPUTRA ARMI KURINCI HARI INI ===\n";
    $sLogs = DB::table('reputation_logs')
        ->where('user_id', $aldi->user_id)
        ->whereDate('created_at', '2026-10-02')
        ->get();
    echo "Total: " . $sLogs->count() . " log(s)\n";
    foreach ($sLogs as $sl) {
        echo "  - [{$sl->created_at}] {$sl->category} ({$sl->points} pts) : {$sl->description}\n";
    }
}

echo "</pre>";
