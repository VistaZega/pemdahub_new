<?php
/**
 * Diag Jadwal Mengajar Hari Ini
 * Akses: https://perguruanpembda.com/diag_teaching_reminders.php?secret=pembda99
 */
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') die("Akses ditolak");

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Setting;
use Illuminate\Support\Carbon;

header('Content-Type: text/plain; charset=utf-8');

$activeYear = AcademicYear::where('is_active', true)->first();
$dayOfWeek = strtolower(Carbon::now()->format('l')); // e.g. 'wednesday'
$dayNames = [
    'sunday' => 'Minggu',
    'monday' => 'Senin',
    'tuesday' => 'Selasa',
    'wednesday' => 'Rabu',
    'thursday' => 'Kamis',
    'friday' => 'Jumat',
    'saturday' => 'Sabtu',
];
$dayIndo = $dayNames[$dayOfWeek] ?? $dayOfWeek;

echo "=== DIAGNOSTIK PENGINGAT JADWAL MENGAJAR HARI INI ===\n\n";
echo "Hari/Tanggal : {$dayIndo}, " . date('d F Y') . " ({$dayOfWeek})\n";
echo "Tahun Ajaran : " . ($activeYear ? $activeYear->name : 'TIDAK ADA TP AKTIF') . "\n";
echo "Status Saklar wa_send_teaching_reminder: " . (Setting::getValue('wa_send_teaching_reminder', true) ? 'AKTIF (ON)' : 'MATI (OFF)') . "\n\n";

$schedules = Schedule::with(['teacher.user', 'subject', 'classroom', 'school', 'timeSlot'])
    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
    ->where('day_of_week', $dayOfWeek)
    ->orderBy('start_time')
    ->get();

echo "Total Record Jadwal Hari Ini: " . $schedules->count() . "\n\n";

// Kelompokkan per Guru
$teacherSchedules = $schedules->groupBy('teacher_id');
echo "Total Guru Mengajar Hari Ini: " . $teacherSchedules->count() . " Guru\n\n";

$validPhoneCount = 0;
$missingPhoneCount = 0;

foreach ($teacherSchedules as $teacherId => $items) {
    $teacher = $items->first()->teacher;
    if (!$teacher) {
        echo "⚠️ Jadwal tanpa guru (Teacher ID: {$teacherId})\n";
        continue;
    }

    $phone = $teacher->phone ?: ($teacher->user?->phone ?? null);
    $hasPhone = !empty($phone);
    if ($hasPhone) $validPhoneCount++; else $missingPhoneCount++;

    echo "👨‍🏫 Guru: {$teacher->full_name} (ID: {$teacher->id})\n";
    echo "   Unit: " . ($teacher->school?->name ?? '-') . "\n";
    echo "   No WA: " . ($hasPhone ? $phone : "❌ TIDAK ADA NO WA") . "\n";
    echo "   Jadwal (" . $items->count() . " sesi):\n";

    foreach ($items as $sch) {
        $start = substr($sch->start_time ?? ($sch->timeSlot?->start_time ?? '--:--'), 0, 5);
        $end = substr($sch->end_time ?? ($sch->timeSlot?->end_time ?? '--:--'), 0, 5);
        $cls = $sch->classroom?->class_name ?? '-';
        $sub = $sch->subject?->name ?? '-';
        echo "     • [{$start} - {$end}] {$cls} | {$sub}\n";
    }
    echo "--------------------------------------------------------\n";
}

echo "\nRINGKASAN:\n";
echo "- Guru dengan nomor WA valid : {$validPhoneCount}\n";
echo "- Guru tanpa nomor WA        : {$missingPhoneCount}\n";
