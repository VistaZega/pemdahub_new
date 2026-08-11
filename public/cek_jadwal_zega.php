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
use App\Models\TimeSlot;

$guruQuery = $_GET['guru'] ?? 'Yulianus Zega';
$selectedAyId = $_GET['academic_year_id'] ?? null;

$allAcademicYears = AcademicYear::orderBy('id', 'desc')->get();
$activeYear = AcademicYear::where('is_active', 1)->first();

echo "<!DOCTYPE html><html><head><title>Diagnostik Lengkap Jadwal Guru</title>";
echo "<style>
    body { font-family: system-ui, sans-serif; margin: 20px; background: #f8fafc; color: #1e293b; }
    h1, h2, h3, h4 { color: #0f172a; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 25px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
    th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
    th { background: #0284c7; color: white; font-weight: 600; }
    tr:hover { background: #f1f5f9; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .bg-green { background: #dcfce7; color: #166534; }
    .bg-blue { background: #dbeafe; color: #1e40af; }
    .bg-purple { background: #f3e8ff; color: #6b21a8; }
    .bg-amber { background: #fef3c7; color: #92400e; }
    .bg-red { background: #fee2e2; color: #991b1b; }
    .bg-sky { background: #e0f2fe; color: #0369a1; }
    .box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .filter-bar { background: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bae6fd; color: #0369a1; }
    .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 10px; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 10px; }
</style></head><body>";

echo "<h1>📊 Diagnostik Data Mengajar & Jadwal Pelajaran</h1>";

echo "<div class='filter-bar'>";
echo "<b>📌 Status Tahun Ajaran Aktif (is_active = 1) di System:</b> ";
echo $activeYear ? "<span class='badge bg-green'>ID: {$activeYear->id} — {$activeYear->year}</span>" : "<span class='badge bg-red'>TIDAK ADA TP AKTIF!</span>";
echo "<br><br><b>📅 Filter Tahun Ajaran Yang Dipilih di Halaman Ini:</b><br>";
echo "<ul style='margin:8px 0 0 20px; padding:0;'>";
foreach ($allAcademicYears as $ay) {
    $activeTag = $ay->is_active ? " <span class='badge bg-green'>SISTEM AKTIF</span>" : "";
    $selectedTag = ($selectedAyId == $ay->id) ? " <b>[DIFILTER SAAT INI]</b>" : "";
    $link = "?secret=pembda99&guru=" . urlencode($guruQuery) . "&academic_year_id={$ay->id}";
    echo "<li>ID: <b>{$ay->id}</b> — Name: <b>{$ay->year}</b> {$activeTag} {$selectedTag} | <a href='{$link}' style='color:#0284c7;'>Filter TP Ini</a></li>";
}
$linkAll = "?secret=pembda99&guru=" . urlencode($guruQuery);
echo "<li><a href='{$linkAll}' style='color:#0284c7; font-weight:bold;'>TAMPILKAN SEMUA TAHUN AJARAN</a></li>";
echo "</ul>";
echo "</div>";

// 1. Data Guru
$teachers = Teacher::where('full_name', 'like', "%{$guruQuery}%")->get();
foreach ($teachers as $teacher) {
    echo "<div class='box'>";
    echo "<h2>👨‍🏫 Guru: {$teacher->full_name} (ID: {$teacher->id})</h2>";
    echo "<p>Sekolah ID: {$teacher->school_id} | Kode: {$teacher->teacher_code}</p>";
    echo "</div>";

    // 2. Data Penugasan Mengajar (Teaching Assignments)
    $assignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id);
    if ($selectedAyId) {
        $assignmentsQuery->where('academic_year_id', $selectedAyId);
    }
    $assignments = $assignmentsQuery->with(['subject', 'classroom', 'academicYear'])->get();

    echo "<h3>1. Penugasan Mengajar (Teaching Assignments) — Total: " . $assignments->count() . " Record</h3>";
    if ($assignments->isNotEmpty()) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Mata Pelajaran</th><th>Kelas Target</th><th>Jumlah JP</th><th>Jenis Blok</th><th>Tahun Ajaran</th><th>Group Code</th></tr>";
        foreach ($assignments as $a) {
            $sub = $a->subject->subject_name ?? $a->subject->name ?? '-';
            $cName = $a->classroom->class_name ?? '-';
            $ay = $a->academicYear->year ?? "ID: {$a->academic_year_id}";
            echo "<tr>";
            echo "<td>{$a->id}</td>";
            echo "<td><b>{$sub}</b></td>";
            echo "<td><span class='badge bg-blue'>{$cName}</span> (ID: {$a->classroom_id})</td>";
            echo "<td><b>{$a->hours_per_week} JP</b></td>";
            echo "<td><span class='badge bg-purple'>{$a->block_type}</span></td>";
            echo "<td><span class='badge bg-sky'>{$ay}</span> (ID: {$a->academic_year_id})</td>";
            echo "<td>" . ($a->group_code ?: '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 3. Data Jadwal Pelajaran Ter-Plot (Schedules)
    $schedulesQuery = Schedule::where('teacher_id', $teacher->id);
    if ($selectedAyId) {
        $schedulesQuery->where('academic_year_id', $selectedAyId);
    }
    $schedules = $schedulesQuery->with(['subject', 'classroom', 'timeSlot', 'academicYear'])
        ->orderBy('day_of_week')
        ->orderBy('time_slot_id')
        ->get();

    echo "<h3>2. Data Jadwal Pelajaran Yang Sudah Di-Plot (Schedules) — Total: " . $schedules->count() . " Record</h3>";
    if ($schedules->isNotEmpty()) {
        echo "<table>";
        echo "<tr><th>Schedule ID</th><th>Hari (Day)</th><th>Kelas Target</th><th>Mata Pelajaran</th><th>Time Slot / Jam Ke</th><th>Durasi (JP)</th><th>Tahun Ajaran</th><th>Semester</th><th>Group Code</th></tr>";
        
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
            echo "<td><span class='badge bg-sky'>{$ay}</span> (ID: {$s->academic_year_id})</td>";
            echo "<td><span class='badge bg-purple'>{$s->semester}</span></td>";
            echo "<td>" . ($s->group_code ?: '-') . "</td>";
            echo "</tr>";

            // EXHAUSTIVE DEEP AUDIT OF GRID QUERY MATCHING FOR THIS SCHEDULE:
            echo "<tr><td colspan='9' style='background:#f8fafc; padding:15px 20px; border-bottom:3px solid #cbd5e1;'>";
            echo "🔬 <b>AUDIT MENGAPA SCHEDULE #{$s->id} DITAMPILKAN ATAU TERSEMBUNYI DI GRID JADWAL:</b><br><br>";

            $issuesFound = 0;

            // 1. School ID Check
            if ($s->school_id != $teacher->school_id) {
                echo "<div class='alert-danger'>❌ <b>MISMATCH SCHOOL ID</b>: Schedule school_id ({$s->school_id}) beda dengan Teacher school_id ({$teacher->school_id}).</div>";
                $issuesFound++;
            } else {
                echo "<div class='alert-success'>✅ <b>School ID Match</b>: {$s->school_id}</div>";
            }

            // 2. Academic Year Active Check
            if ($activeYear && $s->academic_year_id != $activeYear->id) {
                echo "<div class='alert-danger'>❌ <b>MISMATCH TAHUN AJARAN AKTIF</b>: Schedule tersimpan di TP ID <b>{$s->academic_year_id}</b>, tapi Sistem Aktif saat ini TP ID <b>{$activeYear->id}</b> ({$activeYear->year}). Jika Admin buka Grid tanpa filter TP, jadwal ini TERSEMBUNYI!</div>";
                $issuesFound++;
            } else {
                echo "<div class='alert-success'>✅ <b>Tahun Ajaran Match</b>: TP ID {$s->academic_year_id}</div>";
            }

            // 3. Classroom Active & Query Check
            if ($s->classroom) {
                if (!$s->classroom->is_active) {
                    echo "<div class='alert-danger'>❌ <b>KELAS NON-AKTIF</b>: Kelas {$s->classroom->class_name} (ID: {$s->classroom_id}) berstatus <code>is_active = 0</code>! Query Grid memfilter <code>where('is_active', 1)</code> sehingga kelas ini DAN SELURUH JADWALNYA HIDDEN!</div>";
                    $issuesFound++;
                } else {
                    echo "<div class='alert-success'>✅ <b>Kelas Aktif</b>: {$s->classroom->class_name} (ID: {$s->classroom_id}, Grade: {$s->classroom->grade_level}, Shift: '{$s->classroom->shift}')</div>";
                }

                if ($s->classroom->school_id != $s->school_id) {
                    echo "<div class='alert-danger'>❌ <b>MISMATCH SCHOOL KELAS</b>: Classroom school_id ({$s->classroom->school_id}) != Schedule school_id ({$s->school_id}).</div>";
                    $issuesFound++;
                }

                if ($s->classroom->academic_year_id != $s->academic_year_id) {
                    echo "<div class='alert-danger'>❌ <b>MISMATCH TP KELAS</b>: Classroom academic_year_id ({$s->classroom->academic_year_id}) != Schedule academic_year_id ({$s->academic_year_id}). Grid memfilter classroom berdasarkan TP, sehingga kelas ini tersembunyi jika TP tidak sama!</div>";
                    $issuesFound++;
                }
            } else {
                echo "<div class='alert-danger'>❌ <b>KELAS TIDAK DITEMUKAN!</b> Classroom ID {$s->classroom_id} tidak ada di DB.</div>";
                $issuesFound++;
            }

            // 4. TimeSlot Active & Teaching Slot Check
            if ($s->timeSlot) {
                if (!$s->timeSlot->is_active) {
                    echo "<div class='alert-danger'>❌ <b>TIMESLOT NON-AKTIF</b>: TimeSlot ID {$s->time_slot_id} berstatus <code>is_active = 0</code>! Query Grid memfilter <code>where('is_active', 1)</code> sehingga jam ini HIDDEN!</div>";
                    $issuesFound++;
                }
                if (!$s->timeSlot->is_teaching_slot) {
                    echo "<div class='alert-danger'>❌ <b>TIMESLOT BUKAN JAM MENGAJAR</b>: TimeSlot ID {$s->time_slot_id} berstatus <code>is_teaching_slot = 0</code> (Istirahat/Upacara). Grid hanya menampilkan <code>is_teaching_slot = 1</code>!</div>";
                    $issuesFound++;
                } else {
                    echo "<div class='alert-success'>✅ <b>TimeSlot Valid</b>: {$s->timeSlot->slot_name} ({$s->timeSlot->start_time} - {$s->timeSlot->end_time}) | is_teaching_slot = 1</div>";
                }

                if ($s->timeSlot->academic_year_id != $s->academic_year_id) {
                    echo "<div class='alert-danger'>❌ <b>MISMATCH TP TIMESLOT</b>: TimeSlot academic_year_id ({$s->timeSlot->academic_year_id}) != Schedule academic_year_id ({$s->academic_year_id}). Query Grid memfilter timeSlot berdasarkan TP, sehingga jam ini tersembunyi!</div>";
                    $issuesFound++;
                }
            } else {
                echo "<div class='alert-danger'>❌ <b>TIMESLOT TIDAK DITEMUKAN!</b> TimeSlot ID {$s->time_slot_id} tidak ada di DB.</div>";
                $issuesFound++;
            }

            // 5. Semester Check
            echo "<div class='alert-success'>ℹ️ <b>Semester Schedule</b>: '{$s->semester}' (Jika di Grid memilih semester beda, jadwal ini hidden).</div>";

            if ($issuesFound === 0) {
                echo "<div class='alert-success'>🎉 <b>SELURUH FILTER DATABASE PERFECT MATCH (100% VALID)!</b><br>";
                echo "Jika jadwal #{$s->id} ini masih belum muncul di layar browser Admin saat membuka Grid, penyebabnya pasti salah satu dari 2 hal ini:<br>";
                echo "1. <b>Filter Dropdown di Layar Grid</b>: Dropdown <i>Grade Level</i> (misal diset ke Kelas X) atau Dropdown <i>Semester</i> (diset ke Genap) di bagian atas layar Grid Admin sedang aktif.<br>";
                echo "2. <b>Browser Cache</b>: Browser Chrome Admin masih menampilkan HTML simpanan lama. Tekan <b>Ctrl + F5</b> di keyboard pada halaman Grid Jadwal Admin.</div>";
            }

            echo "</td></tr>";
        }
        echo "</table>";
    }
}

echo "</body></html>";
