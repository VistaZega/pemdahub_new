<?php
/**
 * Diagnostik Jadwal (Schedules) - PembdaHUB
 * Akses: perguruanpembda.com/check-schedules.php?secret=pembda99
 * 
 * Mengecek distribusi data jadwal per hari untuk menemukan
 * penyebab bug absensi hanya menampilkan tanggal Selasa.
 */

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Unauthorized.');
}

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Bootstrap Laravel
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
} catch (\Throwable $e) {
    echo "<pre>Bootstrap Error: " . $e->getMessage() . "\nFile: " . $e->getFile() . ":" . $e->getLine() . "</pre>";
    exit;
}

use Illuminate\Support\Facades\DB;

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Diagnostik Jadwal</title>";
echo "<style>
body { font-family: 'Courier New', monospace; background: #0d1117; color: #c9d1d9; padding: 30px; }
h1 { color: #58a6ff; border-bottom: 2px solid #30363d; padding-bottom: 10px; }
h2 { color: #f0883e; margin-top: 30px; }
h3 { color: #7ee787; }
table { border-collapse: collapse; margin: 10px 0; width: 100%; max-width: 900px; }
th, td { border: 1px solid #30363d; padding: 8px 12px; text-align: left; }
th { background: #161b22; color: #58a6ff; }
tr:nth-child(even) { background: #161b22; }
.ok { color: #7ee787; font-weight: bold; }
.warn { color: #f0883e; font-weight: bold; }
.error { color: #f85149; font-weight: bold; }
.info { color: #58a6ff; }
pre { background: #161b22; padding: 15px; border-radius: 8px; overflow-x: auto; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.badge-green { background: #238636; color: white; }
.badge-red { background: #da3633; color: white; }
.badge-yellow { background: #9e6a03; color: white; }
</style></head><body>";

echo "<h1>🔍 Diagnostik Jadwal (Schedules) - PembdaHUB</h1>";
echo "<p class='info'>Waktu: " . date('Y-m-d H:i:s') . " WIB</p>";

try {

// 1. Cek Tahun Pelajaran Aktif
echo "<h2>1. Tahun Pelajaran Aktif</h2>";
$activeAY = DB::table('academic_years')->where('is_active', true)->first();
if ($activeAY) {
    echo "<p class='ok'>✅ TP Aktif: {$activeAY->year} (ID: {$activeAY->id})</p>";
    echo "<p>Start: " . ($activeAY->start_date ?? '-') . " | End: " . ($activeAY->end_date ?? '-') . "</p>";
} else {
    echo "<p class='error'>❌ TIDAK ADA Tahun Pelajaran Aktif!</p>";
    echo "</body></html>";
    exit;
}

// 2. Total Record Schedules
echo "<h2>2. Total Record di Tabel schedules</h2>";
$totalSchedules = DB::table('schedules')->count();
$totalForActiveAY = DB::table('schedules')->where('academic_year_id', $activeAY->id)->count();
echo "<table>";
echo "<tr><th>Metrik</th><th>Jumlah</th></tr>";
echo "<tr><td>Total semua jadwal</td><td>{$totalSchedules}</td></tr>";
echo "<tr><td>Jadwal untuk TP Aktif (ID: {$activeAY->id})</td><td><strong>{$totalForActiveAY}</strong></td></tr>";
echo "</table>";

if ($totalForActiveAY === 0) {
    echo "<p class='error'>❌ TIDAK ADA jadwal untuk TP aktif! Ini penyebab utama masalah.</p>";
}

// 3. Distribusi day_of_week untuk TP Aktif
echo "<h2>3. Distribusi Hari (day_of_week) untuk TP Aktif</h2>";
$dayDistribution = DB::table('schedules')
    ->where('academic_year_id', $activeAY->id)
    ->select('day_of_week', DB::raw('COUNT(*) as total'))
    ->groupBy('day_of_week')
    ->orderBy('total', 'desc')
    ->get();

if ($dayDistribution->isEmpty()) {
    echo "<p class='error'>❌ Tidak ada data jadwal sama sekali untuk TP ini.</p>";
} else {
    echo "<table>";
    echo "<tr><th>Hari (day_of_week)</th><th>Jumlah Jadwal</th><th>Status</th></tr>";
    
    $expectedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    $foundDays = $dayDistribution->pluck('day_of_week')->map(fn($d) => strtolower(trim($d)))->toArray();
    
    foreach ($dayDistribution as $row) {
        $badge = "<span class='badge badge-green'>OK</span>";
        echo "<tr><td><strong>" . strtoupper($row->day_of_week) . "</strong></td><td>{$row->total}</td><td>{$badge}</td></tr>";
    }
    
    // Cek hari yang hilang
    $missingDays = array_diff($expectedDays, $foundDays);
    foreach ($missingDays as $missing) {
        echo "<tr><td><strong>" . strtoupper($missing) . "</strong></td><td>0</td><td><span class='badge badge-red'>HILANG</span></td></tr>";
    }
    echo "</table>";
    
    if (count($dayDistribution) === 1) {
        $onlyDay = strtolower(trim($dayDistribution[0]->day_of_week));
        echo "<p class='error'>⚠️ DITEMUKAN MASALAH: Semua jadwal hanya untuk hari <strong>" . strtoupper($onlyDay) . "</strong>!</p>";
        echo "<p class='warn'>Ini penyebab bug absensi hanya menampilkan tanggal hari " . strtoupper($onlyDay) . ".</p>";
    }
    
    if (!empty($missingDays)) {
        echo "<p class='warn'>⚠️ Hari yang tidak memiliki jadwal: <strong>" . implode(', ', array_map('strtoupper', $missingDays)) . "</strong></p>";
    }
}

// 4. Distribusi per Sekolah
echo "<h2>4. Distribusi Hari per Sekolah (TP Aktif)</h2>";
$perSchool = DB::table('schedules')
    ->join('schools', 'schedules.school_id', '=', 'schools.id')
    ->where('schedules.academic_year_id', $activeAY->id)
    ->select('schools.name as school_name', 'schools.id as school_id', 'schedules.day_of_week', DB::raw('COUNT(*) as total'))
    ->groupBy('schools.id', 'schools.name', 'schedules.day_of_week')
    ->orderBy('schools.id')
    ->orderBy('schedules.day_of_week')
    ->get();

if ($perSchool->isNotEmpty()) {
    $grouped = $perSchool->groupBy('school_name');
    foreach ($grouped as $schoolName => $rows) {
        echo "<h3>📍 {$schoolName}</h3>";
        echo "<table>";
        echo "<tr><th>Hari</th><th>Jumlah Jadwal</th></tr>";
        foreach ($rows as $row) {
            echo "<tr><td>" . strtoupper($row->day_of_week) . "</td><td>{$row->total}</td></tr>";
        }
        echo "</table>";
        
        // Warning jika sekolah hanya punya 1 hari
        if ($rows->count() === 1) {
            echo "<p class='error'>⚠️ Sekolah ini hanya punya jadwal hari " . strtoupper($rows[0]->day_of_week) . "!</p>";
        }
    }
} else {
    echo "<p class='warn'>Tidak ada data jadwal terhubung ke sekolah.</p>";
}

// 5. Distribusi per Kelas (Top 20)
echo "<h2>5. Distribusi Hari per Kelas (Top 20 Kelas dengan Jadwal Paling Sedikit)</h2>";
$perClassroom = DB::table('schedules')
    ->join('classrooms', 'schedules.classroom_id', '=', 'classrooms.id')
    ->where('schedules.academic_year_id', $activeAY->id)
    ->select('classrooms.class_name', 'classrooms.id as classroom_id', DB::raw('GROUP_CONCAT(DISTINCT schedules.day_of_week ORDER BY schedules.day_of_week) as days'), DB::raw('COUNT(DISTINCT schedules.day_of_week) as unique_days'), DB::raw('COUNT(*) as total_schedules'))
    ->groupBy('classrooms.id', 'classrooms.class_name')
    ->orderBy('unique_days')
    ->limit(20)
    ->get();

if ($perClassroom->isNotEmpty()) {
    echo "<table>";
    echo "<tr><th>Kelas</th><th>Jumlah Hari Unik</th><th>Hari-hari</th><th>Total Jadwal</th><th>Status</th></tr>";
    foreach ($perClassroom as $row) {
        $status = $row->unique_days <= 1 
            ? "<span class='badge badge-red'>BERMASALAH</span>" 
            : ($row->unique_days <= 3 
                ? "<span class='badge badge-yellow'>KURANG</span>" 
                : "<span class='badge badge-green'>OK</span>");
        echo "<tr><td>{$row->class_name}</td><td>{$row->unique_days}</td><td>{$row->days}</td><td>{$row->total_schedules}</td><td>{$status}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warn'>Tidak ada data jadwal per kelas.</p>";
}

// 6. Cek NULL atau Kosong pada day_of_week
echo "<h2>6. Cek Data Anomali</h2>";
$nullDays = DB::table('schedules')
    ->where('academic_year_id', $activeAY->id)
    ->where(function ($q) {
        $q->whereNull('day_of_week')
          ->orWhere('day_of_week', '')
          ->orWhere('day_of_week', ' ');
    })
    ->count();

echo "<table>";
echo "<tr><th>Jenis Anomali</th><th>Jumlah</th><th>Status</th></tr>";
echo "<tr><td>day_of_week NULL atau Kosong</td><td>{$nullDays}</td><td>" . ($nullDays > 0 ? "<span class='badge badge-red'>ADA MASALAH</span>" : "<span class='badge badge-green'>OK</span>") . "</td></tr>";

// Cek teacher_id NULL
$nullTeacher = DB::table('schedules')
    ->where('academic_year_id', $activeAY->id)
    ->whereNull('teacher_id')
    ->count();
echo "<tr><td>teacher_id NULL</td><td>{$nullTeacher}</td><td>" . ($nullTeacher > 0 ? "<span class='badge badge-yellow'>PERLU DICEK</span>" : "<span class='badge badge-green'>OK</span>") . "</td></tr>";

// Cek classroom_id NULL
$nullClassroom = DB::table('schedules')
    ->where('academic_year_id', $activeAY->id)
    ->whereNull('classroom_id')
    ->count();
echo "<tr><td>classroom_id NULL</td><td>{$nullClassroom}</td><td>" . ($nullClassroom > 0 ? "<span class='badge badge-yellow'>PERLU DICEK</span>" : "<span class='badge badge-green'>OK</span>") . "</td></tr>";
echo "</table>";

// 7. Cek kelas aktif tanpa jadwal
echo "<h2>7. Kelas Aktif TANPA Jadwal untuk TP Aktif</h2>";
$classesNoSchedule = DB::table('classrooms')
    ->where('classrooms.is_active', true)
    ->where('classrooms.academic_year_id', $activeAY->id)
    ->whereNotExists(function ($q) use ($activeAY) {
        $q->select(DB::raw(1))
          ->from('schedules')
          ->whereColumn('schedules.classroom_id', 'classrooms.id')
          ->where('schedules.academic_year_id', $activeAY->id);
    })
    ->select('classrooms.id', 'classrooms.class_name')
    ->get();

if ($classesNoSchedule->isNotEmpty()) {
    echo "<p class='warn'>⚠️ Ditemukan {$classesNoSchedule->count()} kelas aktif tanpa jadwal:</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nama Kelas</th></tr>";
    foreach ($classesNoSchedule as $cls) {
        echo "<tr><td>{$cls->id}</td><td>{$cls->class_name}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='ok'>✅ Semua kelas aktif sudah memiliki jadwal.</p>";
}

// 8. Sampel 10 record terakhir dari tabel schedules
echo "<h2>8. Sampel 10 Record Jadwal Terakhir (TP Aktif)</h2>";
$samples = DB::table('schedules')
    ->leftJoin('teachers', 'schedules.teacher_id', '=', 'teachers.id')
    ->leftJoin('classrooms', 'schedules.classroom_id', '=', 'classrooms.id')
    ->leftJoin('subjects', 'schedules.subject_id', '=', 'subjects.id')
    ->where('schedules.academic_year_id', $activeAY->id)
    ->select(
        'schedules.id', 
        'schedules.day_of_week', 
        'schedules.start_time', 
        'schedules.end_time',
        'teachers.full_name as teacher_name',
        'classrooms.class_name',
        'subjects.name as subject_name'
    )
    ->orderByDesc('schedules.id')
    ->limit(10)
    ->get();

if ($samples->isNotEmpty()) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Hari</th><th>Mulai</th><th>Selesai</th><th>Guru</th><th>Kelas</th><th>Mapel</th></tr>";
    foreach ($samples as $s) {
        echo "<tr><td>{$s->id}</td><td><strong>" . strtoupper($s->day_of_week ?? 'NULL') . "</strong></td><td>{$s->start_time}</td><td>{$s->end_time}</td><td>" . ($s->teacher_name ?? '-') . "</td><td>" . ($s->class_name ?? '-') . "</td><td>" . ($s->subject_name ?? '-') . "</td></tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<h2>📋 Kesimpulan</h2>";

if ($totalForActiveAY === 0) {
    echo "<p class='error'>❌ <strong>MASALAH UTAMA:</strong> Tidak ada data jadwal untuk TP Aktif {$activeAY->year}.</p>";
    echo "<p>Solusi: Input jadwal pelajaran untuk semua kelas dan guru di menu Admin → Jadwal Pelajaran.</p>";
} elseif (count($dayDistribution) === 1) {
    $onlyDay = strtolower(trim($dayDistribution[0]->day_of_week));
    $dayNames = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu'];
    $dayLabel = $dayNames[$onlyDay] ?? $onlyDay;
    echo "<p class='error'>❌ <strong>MASALAH UTAMA DITEMUKAN:</strong> Semua {$totalForActiveAY} jadwal hanya untuk hari <strong>{$dayLabel} ({$onlyDay})</strong>.</p>";
    echo "<p>Inilah penyebab absensi hanya menampilkan tanggal-tanggal hari {$dayLabel} saja.</p>";
    echo "<p class='warn'>Solusi: Perbaiki data jadwal di menu Admin → Jadwal Pelajaran, pastikan jadwal sudah benar untuk setiap hari (Senin-Jumat/Sabtu).</p>";
} elseif (!empty($missingDays)) {
    echo "<p class='warn'>⚠️ Jadwal tidak lengkap — hari yang tidak ada jadwal: <strong>" . implode(', ', array_map('strtoupper', $missingDays)) . "</strong></p>";
    echo "<p>Pastikan jadwal pelajaran sudah diinput untuk semua hari aktif.</p>";
} else {
    echo "<p class='ok'>✅ Distribusi hari jadwal terlihat normal.</p>";
    echo "<p>Jika masalah masih terjadi, mungkin ada masalah spesifik pada guru/kelas tertentu.</p>";
}

echo "<br><p class='info'>💡 Catatan: Halaman ini aman untuk diakses berulang kali (hanya membaca data, tidak mengubah apapun).</p>";

} catch (\Throwable $e) {
    echo "<h2 style='color: #f85149;'>❌ Error Terjadi</h2>";
    echo "<pre style='background: #161b22; padding: 15px; border-radius: 8px; color: #f85149;'>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

echo "</body></html>";
