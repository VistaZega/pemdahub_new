<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());
if (php_sapi_name() !== 'cli' && request('secret') !== 'pembda99') die('Unauthorized');

use Illuminate\Support\Facades\DB;
use App\Models\Classroom;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Teacher;
use App\Models\ReputationLog;

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔍 FORENSIK PRESENSI KELAS X TKR - 02 OKTOBER 2026</h2><pre>";

$cls = Classroom::where('name', 'like', '%X TKR%')->first();
if (!$cls) {
    echo "Kelas X TKR tidak ditemukan!\n";
    exit;
}
echo "Kelas: ID {$cls->id} - {$cls->name}\n";
echo "Wali Kelas ID: {$cls->homeroom_teacher_id} - " . ($cls->homeroomTeacher?->full_name ?? '-') . "\n";
echo "Entry Time: " . ($cls->entry_time ?? '07:30') . " | Late Tolerance: " . ($cls->late_tolerance ?? 15) . " mins | Late Threshold: " . $cls->getLateThreshold() . "\n\n";

$students = $cls->students;
$studentIds = $students->pluck('id');
echo "Total Siswa di Kelas: " . $studentIds->count() . "\n\n";

echo "--- DATA PRESENSI HARI INI (2026-10-02) DI DATABASE ---\n";
$attendances = DB::table('attendances')
    ->leftJoin('students', 'attendances.student_id', '=', 'students.id')
    ->leftJoin('users', 'attendances.created_by', '=', 'users.id')
    ->whereIn('attendances.student_id', $studentIds)
    ->whereDate('attendances.date', '2026-10-02')
    ->select(
        'attendances.id',
        'attendances.student_id',
        'students.full_name as student_name',
        'attendances.schedule_id',
        'attendances.status',
        'attendances.time_in',
        'attendances.recorded_via',
        'attendances.created_by',
        'users.name as creator_name',
        'attendances.created_at',
        'attendances.updated_at'
    )
    ->orderBy('attendances.id', 'asc')
    ->get();

echo "Total records attendance ditemukan: " . $attendances->count() . "\n\n";
foreach ($attendances as $att) {
    echo sprintf(
        "ID: %d | Siswa: %-32s | Sched: %-5s | Status: %-10s | TimeIn: %s | Via: %-8s | By: %s (ID %s) | CreatedAt: %s | UpdatedAt: %s\n",
        $att->id,
        substr($att->student_name, 0, 32),
        $att->schedule_id ?? 'NULL',
        $att->status,
        $att->time_in ?? 'NULL',
        $att->recorded_via ?? '-',
        $att->creator_name ?? 'NULL',
        $att->created_by ?? 'NULL',
        $att->created_at,
        $att->updated_at
    );
}

echo "\n--- CEK REPUTATION LOGS HARI INI (2026-10-02) ---\n";
$teacherUser = $cls->homeroomTeacher?->user;
if ($teacherUser) {
    $tLogs = ReputationLog::where('user_id', $teacherUser->id)
        ->whereDate('created_at', '2026-10-02')
        ->get();
    echo "Reputation Log Wali Kelas ({$teacherUser->name}): " . $tLogs->count() . " records\n";
    foreach ($tLogs as $tl) {
        echo "  - [{$tl->created_at}] {$tl->category} | Points: {$tl->points} | Desc: {$tl->description}\n";
    }
}

$sampleStudent = $students->first();
if ($sampleStudent && $sampleStudent->user_id) {
    $sLogs = ReputationLog::where('user_id', $sampleStudent->user_id)
        ->whereDate('created_at', '2026-10-02')
        ->get();
    echo "\nSample Student Reputation Log ({$sampleStudent->full_name}): " . $sLogs->count() . " records\n";
    foreach ($sLogs as $sl) {
        echo "  - [{$sl->created_at}] {$sl->category} | Points: {$sl->points} | Desc: {$sl->description}\n";
    }
}

echo "\n--- CEK SEMUA ATTENDANCE YANG DI-CREATE OLEH USER MANAPUN PADA HARI INI UNTUK KELAS INI ---\n";
$distinctCreatedBy = DB::table('attendances')
    ->whereIn('student_id', $studentIds)
    ->whereDate('date', '2026-10-02')
    ->distinct()
    ->pluck('created_by');

foreach ($distinctCreatedBy as $cbId) {
    $u = User::find($cbId);
    echo "Creator ID {$cbId}: " . ($u ? "{$u->name} ({$u->role}, email: {$u->email})" : "Unknown") . "\n";
}

echo "</pre>";
