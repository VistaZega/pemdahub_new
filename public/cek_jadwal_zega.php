<?php

/**
 * Script Diagnostik Data Mengajar & Jadwal Guru Yulianus Zega
 * Akses via Browser: https://perguruanpembda.com/cek_jadwal_zega.php?secret=pembda99
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    die("<h3 style='color:red;'>Akses Ditolak. Gunakan parameter ?secret=pembda99</h3>");
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\TeachingAssignment;
use App\Models\Classroom;
use App\Models\AcademicYear;

$guruQuery = $_GET['guru'] ?? 'Yulianus Zega';
$tpFilter = $_GET['tp'] ?? '2026/2027';

// Find Academic Year ID for 2026/2027
$targetAcademicYear = AcademicYear::where('year', 'like', "%2026/2027%")
    ->orWhere('year', 'like', "%2026%")
    ->first();

$targetYearId = $targetAcademicYear ? $targetAcademicYear->id : null;

echo "<!DOCTYPE html><html><head><title>Diagnostik Jadwal Guru TP 2026/2027</title>";
echo "<style>
    body { font-family: system-ui, sans-serif; margin: 20px; background: #f8fafc; color: #1e293b; }
    h1, h2, h3 { color: #0f172a; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 25px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
    th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
    th { background: #0284c7; color: white; font-weight: 600; }
    tr:hover { background: #f1f5f9; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .bg-green { background: #dcfce7; color: #166534; }
    .bg-blue { background: #dbeafe; color: #1e40af; }
    .bg-purple { background: #f3e8ff; color: #6b21a8; }
    .bg-amber { background: #fef3c7; color: #92400e; }
    .bg-sky { background: #e0f2fe; color: #0369a1; }
    .box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .filter-bar { background: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bae6fd; font-weight: bold; color: #0369a1; }
</style></head><body>";

echo "<h1>📊 Diagnostik Jadwal Pelajaran (TP 2026/2027)</h1>";

echo "<div class='filter-bar'>";
echo "📌 Filter Aktif: <b>TP 2026/2027</b> " . ($targetAcademicYear ? "(Academic Year ID: {$targetAcademicYear->id}, Nama: {$targetAcademicYear->year})" : "<span style='color:red;'>(ID 2026/2027 tidak ditemukan, menampilkan semua TP)</span>");
echo " | Guru: <b>" . htmlspecialchars($guruQuery) . "</b>";
echo "</div>";

// 1. Cari Data Guru
$teachers = Teacher::where('full_name', 'like', "%{$guruQuery}%")->get();

if ($teachers->isEmpty()) {
    echo "<div class='box' style='color:red;'>❌ Guru dengan nama '{$guruQuery}' tidak ditemukan di database.</div>";
    echo "</body></html>";
    exit;
}

foreach ($teachers as $teacher) {
    echo "<div class='box'>";
    echo "<h2>👨‍🏫 Guru: {$teacher->full_name} (ID: {$teacher->id})</h2>";
    echo "<p>Sekolah ID: {$teacher->school_id} | Email/Kode: {$teacher->teacher_code}</p>";
    echo "</div>";

    // 2. Data Penugasan Mengajar (Teaching Assignments) khusus TP 2026/2027
    $assignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id);
    if ($targetYearId) {
        $assignmentsQuery->where('academic_year_id', $targetYearId);
    }
    $assignments = $assignmentsQuery->with(['subject', 'classroom', 'academicYear'])->get();

    echo "<h3>1. Penugasan Mengajar (Teaching Assignments) TP 2026/2027 — Total: " . $assignments->count() . " Record</h3>";
    if ($assignments->isEmpty()) {
        echo "<div class='box' style='border-left:4px solid #f59e0b; color:#b45309;'>⚠️ Belum ada Penugasan Mengajar diisi untuk TP 2026/2027 bagi guru ini.</div>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Mata Pelajaran</th><th>Kelas</th><th>Jumlah JP</th><th>Jenis Blok</th><th>Tahun Ajaran</th><th>Group Code</th></tr>";
        foreach ($assignments as $a) {
            $sub = $a->subject->subject_name ?? $a->subject->name ?? '-';
            $cName = $a->classroom->class_name ?? '-';
            $ay = $a->academicYear->year ?? "ID: {$a->academic_year_id}";
            echo "<tr>";
            echo "<td>{$a->id}</td>";
            echo "<td><b>{$sub}</b></td>";
            echo "<td><span class='badge bg-blue'>{$cName}</span></td>";
            echo "<td><b>{$a->hours_per_week} JP</b></td>";
            echo "<td><span class='badge bg-purple'>{$a->block_type}</span></td>";
            echo "<td><span class='badge bg-sky'>{$ay}</span></td>";
            echo "<td>" . ($a->group_code ?: '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 3. Data Jadwal Pelajaran Ter-Plot (Schedules) khusus TP 2026/2027
    $schedulesQuery = Schedule::where('teacher_id', $teacher->id);
    if ($targetYearId) {
        $schedulesQuery->where('academic_year_id', $targetYearId);
    }
    $schedules = $schedulesQuery->with(['subject', 'classroom', 'timeSlot', 'academicYear'])
        ->orderBy('day_of_week')
        ->orderBy('time_slot_id')
        ->get();

    echo "<h3>2. Data Jadwal Pelajaran Yang Sudah Di-Plot (Schedules) TP 2026/2027 — Total: " . $schedules->count() . " Record</h3>";
    if ($schedules->isEmpty()) {
        echo "<div class='box' style='border-left:4px solid #ef4444; color:#b91c1c;'>⚠️ <b>BELUM ADA JADWAL TER-PLOT DI TABEL SCHEDULES UNTUK TP 2026/2027</b> bagi guru ini.</div>";
    } else {
        echo "<table>";
        echo "<tr><th>Schedule ID</th><th>Hari (Day)</th><th>Kelas Target</th><th>Mata Pelajaran</th><th>Time Slot / Jam Ke</th><th>Durasi (JP)</th><th>Tahun Ajaran</th><th>Semester</th><th>Group Code</th><th>Tanggal Dibuat</th></tr>";
        
        $dayMap = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu',
            'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu',
            'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu'
        ];

        foreach ($schedules as $s) {
            $sub = $s->subject->subject_name ?? $s->subject->name ?? '-';
            $cName = $s->classroom->class_name ?? '-';
            $dayName = $dayMap[$s->day_of_week] ?? $s->day_of_week;
            $tsName = $s->timeSlot ? "{$s->timeSlot->slot_name} ({$s->timeSlot->start_time} - {$s->timeSlot->end_time})" : "Slot ID: {$s->time_slot_id}";
            $ay = $s->academicYear->year ?? "ID: {$s->academic_year_id}";
            
            echo "<tr>";
            echo "<td><b>#{$s->id}</b></td>";
            echo "<td><span class='badge bg-amber'>{$dayName}</span> <code>({$s->day_of_week})</code></td>";
            echo "<td><span class='badge bg-blue'>{$cName}</span> (ID: {$s->classroom_id})</td>";
            echo "<td><b>{$sub}</b></td>";
            echo "<td>{$tsName}</td>";
            echo "<td><b>{$s->duration_slots} JP</b></td>";
            echo "<td><span class='badge bg-sky'>{$ay}</span></td>";
            echo "<td><span class='badge bg-purple'>{$s->semester}</span></td>";
            echo "<td>" . ($s->group_code ?: '-') . "</td>";
            echo "<td>{$s->created_at}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// 4. Cek Semua Jadwal Khusus di Kelas "XI Teknik Rekayasa" TP 2026/2027
echo "<h2>🏫 Diagnostik Khusus Kelas 'XI Teknik Rekayasa' (TP 2026/2027)</h2>";
$rekayasaClassesQuery = Classroom::where(function($q) {
    $q->where('class_name', 'like', '%XI%Teknik%Rekayasa%')
      ->orWhere('class_name', 'like', '%XI%Teknik Rekayasa%');
});

if ($targetYearId) {
    $rekayasaClassesQuery->where('academic_year_id', $targetYearId);
}

$rekayasaClasses = $rekayasaClassesQuery->get();

if ($rekayasaClasses->isEmpty()) {
    echo "<p style='color:orange;'>Tidak ditemukan kelas 'XI Teknik Rekayasa' untuk TP 2026/2027.</p>";
} else {
    foreach ($rekayasaClasses as $c) {
        echo "<div class='box'>";
        echo "<h4>Nama Kelas: {$c->class_name} (ID: {$c->id}) | Tingkat/Grade: {$c->grade_level} | Shift: {$c->shift} | Tahun Ajaran ID: {$c->academic_year_id}</h4>";
        
        $classSchedulesQuery = Schedule::where('classroom_id', $c->id);
        if ($targetYearId) {
            $classSchedulesQuery->where('academic_year_id', $targetYearId);
        }
        $classSchedules = $classSchedulesQuery->with(['teacher', 'subject', 'timeSlot'])->get();
            
        echo "<p>Total Jadwal Ter-plot di Kelas Ini (TP 2026/2027): <b>" . $classSchedules->count() . " Record</b></p>";
        if ($classSchedules->isNotEmpty()) {
            echo "<table>";
            echo "<tr><th>Schedule ID</th><th>Hari</th><th>Mata Pelajaran</th><th>Guru Mengajar</th><th>Jam Ke / TimeSlot</th><th>Durasi</th><th>Semester</th></tr>";
            foreach ($classSchedules as $cs) {
                $gName = $cs->teacher->full_name ?? '-';
                $sName = $cs->subject->subject_name ?? $cs->subject->name ?? '-';
                $ts = $cs->timeSlot ? "{$cs->timeSlot->slot_name} ({$cs->timeSlot->start_time} - {$cs->timeSlot->end_time})" : "Slot ID: {$cs->time_slot_id}";
                echo "<tr>";
                echo "<td>#{$cs->id}</td>";
                echo "<td>{$cs->day_of_week}</td>";
                echo "<td><b>{$sName}</b></td>";
                echo "<td>{$gName} (ID: {$cs->teacher_id})</td>";
                echo "<td>{$ts}</td>";
                echo "<td>{$cs->duration_slots} JP</td>";
                echo "<td>{$cs->semester}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:orange;'>Belum ada jadwal ter-plot sama sekali untuk kelas ini di TP 2026/2027.</p>";
        }
        echo "</div>";
    }
}

echo "</body></html>";
