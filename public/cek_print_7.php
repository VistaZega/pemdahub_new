<?php

/**
 * Script Diagnostik Khusus Cetak Jadwal School ID 7
 * Akses via Browser: https://perguruanpembda.com/cek_print_7.php?secret=pembda99
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    die("<h3 style='color:red;'>Akses Ditolak. Gunakan parameter ?secret=pembda99</h3>");
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\AcademicYear;
use App\Models\TimeSlot;
use App\Models\Classroom;
use App\Models\Schedule;

$schoolId = 7;
$academicYearId = 5;
$semester = 'ganjil';
$shift = 'all';
$gradeLevel = 11;

echo "<!DOCTYPE html><html><head><title>Diagnostik Cetak School 7</title>";
echo "<style>
    body { font-family: system-ui, sans-serif; padding: 20px; background: #f8fafc; color: #0f172a; }
    h1, h2, h3 { color: #0f172a; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: white; }
    th, td { padding: 8px 12px; border: 1px solid #cbd5e1; text-align: left; font-size: 13px; }
    th { background: #1e293b; color: white; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 11px; }
    .bg-green { background: #dcfce7; color: #166534; }
    .bg-red { background: #fee2e2; color: #991b1b; }
    .bg-blue { background: #dbeafe; color: #1e40af; }
</style></head><body>";

echo "<h1>🔍 Diagnostik Cetak Jadwal (School ID: {$schoolId}, TP: {$academicYearId}, Grade: {$gradeLevel})</h1>";

$school = School::find($schoolId);
echo "<p><b>Sekolah:</b> " . ($school ? $school->name : 'N/A') . "</p>";

// 1. Check TimeSlots in DB for School 7
$allSlots = TimeSlot::where('school_id', $schoolId)->get();
echo "<h2>1. Data TimeSlot di DB untuk School ID {$schoolId} — Total: " . $allSlots->count() . " Slot</h2>";

if ($allSlots->isEmpty()) {
    echo "<p style='color:red; font-weight:bold;'>⚠️ TIDAK ADA TIMESLOT UNTUK SCHOOL ID {$schoolId} DI DATABASE!</p>";
} else {
    echo "<table>";
    echo "<tr><th>ID</th><th>Day of Week</th><th>Slot Name</th><th>Start Time</th><th>End Time</th><th>Slot Order</th><th>Shift</th><th>Academic Year ID</th><th>Is Active</th></tr>";
    foreach ($allSlots as $ts) {
        $activeBadge = $ts->is_active ? "<span class='badge bg-green'>AKTIF</span>" : "<span class='badge bg-red'>NON-AKTIF</span>";
        echo "<tr>";
        echo "<td>{$ts->id}</td>";
        echo "<td><b>{$ts->day_of_week}</b></td>";
        echo "<td>{$ts->slot_name}</td>";
        echo "<td>{$ts->start_time}</td>";
        echo "<td>{$ts->end_time}</td>";
        echo "<td>{$ts->slot_order}</td>";
        echo "<td>{$ts->shift}</td>";
        echo "<td>{$ts->academic_year_id}</td>";
        echo "<td>{$activeBadge}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 2. Check Days breakdown
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
echo "<h2>2. Breakdown Jam Pelajaran Per-Hari di DB:</h2>";
echo "<ul>";
foreach ($days as $d) {
    $countEng = TimeSlot::where('school_id', $schoolId)->where('day_of_week', $d)->count();
    $countLower = TimeSlot::where('school_id', $schoolId)->whereRaw("LOWER(day_of_week) = ?", [$d])->count();
    echo "<li>Hari <b>{$d}</b>: Total {$countEng} slot (Lower match: {$countLower})</li>";
}
echo "</ul>";

// 3. Check Classrooms Grade 11
$classrooms = Classroom::where('school_id', $schoolId)
    ->where('academic_year_id', $academicYearId)
    ->where('grade_level', $gradeLevel)
    ->where('is_active', 1)
    ->get();
echo "<h2>3. Data Kelas Grade {$gradeLevel} untuk School ID {$schoolId} — Total: " . $classrooms->count() . " Kelas</h2>";
echo "<ul>";
foreach ($classrooms as $c) {
    echo "<li>ID: {$c->id} — <b>{$c->class_name}</b> (Shift: {$c->shift})</li>";
}
echo "</ul>";

// 4. Check Schedules
$schedules = Schedule::where('school_id', $schoolId)
    ->where('academic_year_id', $academicYearId)
    ->where('semester', $semester)
    ->get();
echo "<h2>4. Total Schedules di DB untuk School ID {$schoolId}, TP {$academicYearId}, Semester {$semester}: " . $schedules->count() . " Record</h2>";

echo "</body></html>";
