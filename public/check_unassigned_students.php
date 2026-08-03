<?php
/**
 * Script diagnostik: Cek siswa aktif yang tidak punya assignment kelas di TP 2026/2027
 * dan identifikasi 13 non-siswa + 5 siswa tanpa kelas
 * 
 * Akses: /check_unassigned_students.php?secret=pembda99
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use App\Models\StudentBill;
use Illuminate\Support\Facades\DB;

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>Diagnostik Siswa SMP Pembda 2</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0;}";
echo ".found{color:#00ff88;}.notfound{color:#ff6b6b;}.header{color:#00d4ff;font-size:18px;}";
echo ".warning{color:#ffd700;}.info{color:#aaa;}.section{margin:15px 0;padding:10px;border:1px solid #333;border-radius:8px;background:#2a2a4a;}";
echo "table{border-collapse:collapse;margin:10px 0;width:100%;}th,td{border:1px solid #444;padding:6px 12px;text-align:left;}";
echo "th{background:#2a2a4a;color:#00d4ff;}.highlight{background:#3a3a5a;}</style></head><body>";

// Cari SMP Pembda 2
$school = School::where('name', 'like', '%SMP Swasta Pembda 2%')->first();
if (!$school) {
    echo "<div class='notfound'>❌ Sekolah tidak ditemukan!</div></body></html>";
    exit;
}

$activeYear = AcademicYear::where('is_active', true)->first();
echo "<div class='header'>🔍 Diagnostik Siswa SMP Swasta Pembda 2</div><hr>";
echo "<div class='info'>📍 Sekolah: {$school->name} (ID: {$school->id})</div>";
echo "<div class='info'>📅 TP Aktif: " . ($activeYear ? "{$activeYear->year} (ID: {$activeYear->id})" : "Tidak ada") . "</div><br>";

// ═══════════════════════════════════════════════════════════
// BAGIAN 1: Ringkasan Angka
// ═══════════════════════════════════════════════════════════
$totalSiswaAktif = Student::where('school_id', $school->id)->where('status', 'aktif')->count();

// Siswa yang punya assignment kelas di TP aktif
$siswaWithClass = DB::table('student_classes')
    ->join('students', 'student_classes.student_id', '=', 'students.id')
    ->where('students.school_id', $school->id)
    ->where('students.status', 'aktif')
    ->where('student_classes.academic_year_id', $activeYear->id ?? 0)
    ->where('student_classes.status', 'aktif')
    ->distinct('student_classes.student_id')
    ->pluck('student_classes.student_id')
    ->toArray();

$siswaWithClassCount = count($siswaWithClass);

// Tagihan Juli 2026
$julyBillsCount = StudentBill::whereHas('student', fn($q) => $q->where('school_id', $school->id))
    ->where('month', 7)->where('year', 2026)->count();
$julyBillsLunas = StudentBill::whereHas('student', fn($q) => $q->where('school_id', $school->id))
    ->where('month', 7)->where('year', 2026)->where('status', 'lunas')->count();
$julyBillsBelumLunas = StudentBill::whereHas('student', fn($q) => $q->where('school_id', $school->id))
    ->where('month', 7)->where('year', 2026)->where('status', '!=', 'lunas')->count();

// Siswa menunggak (punya tagihan belum lunas, status aktif)
$siswaMenunggak = StudentBill::whereHas('student', fn($q) => $q->where('school_id', $school->id)->where('status', 'aktif'))
    ->where('month', 7)->where('year', 2026)->where('status', '!=', 'lunas')
    ->distinct('student_id')->count('student_id');

echo "<div class='section'>";
echo "<div class='header'>📊 Ringkasan Angka</div>";
echo "<table>";
echo "<tr><th>Metrik</th><th>Jumlah</th><th>Keterangan</th></tr>";
echo "<tr><td>Siswa Aktif (tabel students)</td><td><b>{$totalSiswaAktif}</b></td><td>status = 'aktif'</td></tr>";
echo "<tr><td>Siswa Aktif + Punya Kelas TP Aktif</td><td><b>{$siswaWithClassCount}</b></td><td>Ada di student_classes TP aktif</td></tr>";
echo "<tr class='highlight'><td><b>Siswa Aktif TANPA Kelas</b></td><td class='warning'><b>" . ($totalSiswaAktif - $siswaWithClassCount) . "</b></td><td>Tidak ada assignment kelas di TP aktif</td></tr>";
echo "<tr><td>Tagihan SPP Juli 2026 (total)</td><td>{$julyBillsCount}</td><td>Semua tagihan terbit</td></tr>";
echo "<tr><td>Tagihan Lunas</td><td class='found'>{$julyBillsLunas}</td><td>Status 'lunas'</td></tr>";
echo "<tr><td>Tagihan Belum Lunas</td><td class='notfound'>{$julyBillsBelumLunas}</td><td>Status 'belum_bayar'/'cicilan'</td></tr>";
echo "<tr><td>Siswa Aktif Menunggak (punya tagihan)</td><td class='warning'>{$siswaMenunggak}</td><td>Konsolidasi: siswa aktif + tagihan belum lunas</td></tr>";
echo "</table></div>";

// ═══════════════════════════════════════════════════════════
// BAGIAN 2: Daftar 13 Non-Siswa (masih ada atau sudah dihapus?)
// ═══════════════════════════════════════════════════════════
$nonSiswaNames = [
    'ALFREDO BERNAT F. MENDROFA',
    'APRIL YADI HULU',
    'ETHELBERT GIEFORD TELAUMBANUA',
    'INTAN TRINASATI GULO',
    'JEFRISON ZEBUA',
    'JUNIOR EKA SAPUTRA GULO',
    'Kevin Putra Damai Ndraha',
    'Okta Melfin Zebua',
    'PELITA REFORMASI ZEGA',
    'RAJA FAOMASI KALOKO',
    'Samuel Kristian Bawamenewi',
    'Seli Silwanda Zebua',
    'WINDA SRIULINA TELAUMBANUA',
];

echo "<div class='section'>";
echo "<div class='header'>👥 Status 13 Non-Siswa</div>";
echo "<table>";
echo "<tr><th>#</th><th>Nama</th><th>Status</th><th>Punya Kelas?</th><th>Punya Tagihan Juli?</th></tr>";

$nonSiswaFoundCount = 0;
foreach ($nonSiswaNames as $i => $name) {
    $student = Student::where('school_id', $school->id)
        ->whereRaw('LOWER(full_name) = ?', [strtolower($name)])
        ->first();
    
    $no = $i + 1;
    if ($student) {
        $nonSiswaFoundCount++;
        $hasClass = in_array($student->id, $siswaWithClass) ? '✅ Ya' : '❌ Tidak';
        $hasBill = StudentBill::where('student_id', $student->id)->where('month', 7)->where('year', 2026)->exists();
        $billStr = $hasBill ? '✅ Ya' : '❌ Tidak';
        echo "<tr><td>{$no}</td><td class='warning'>{$student->full_name}</td><td>{$student->status}</td><td>{$hasClass}</td><td>{$billStr}</td></tr>";
    } else {
        echo "<tr><td>{$no}</td><td class='found'>{$name}</td><td colspan='3' class='found'>✅ Sudah dihapus / Tidak ditemukan</td></tr>";
    }
}
echo "</table>";
echo "<div class='info'>Ditemukan: {$nonSiswaFoundCount} dari 13 non-siswa masih ada di database</div>";
echo "</div>";

// ═══════════════════════════════════════════════════════════
// BAGIAN 3: Siswa Aktif TANPA Assignment Kelas (selain 13 non-siswa)
// ═══════════════════════════════════════════════════════════
$allActiveStudents = Student::where('school_id', $school->id)->where('status', 'aktif')->get();
$unassignedStudents = $allActiveStudents->filter(fn($s) => !in_array($s->id, $siswaWithClass));

// Filter yang bukan 13 non-siswa
$nonSiswaLower = array_map('strtolower', $nonSiswaNames);
$realUnassigned = $unassignedStudents->filter(fn($s) => !in_array(strtolower($s->full_name), $nonSiswaLower));

echo "<div class='section'>";
echo "<div class='header'>⚠️ Siswa Aktif TANPA Kelas (Bukan Non-Siswa)</div>";
if ($realUnassigned->isEmpty()) {
    echo "<div class='found'>✅ Semua siswa aktif (selain non-siswa) sudah punya assignment kelas.</div>";
} else {
    echo "<table>";
    echo "<tr><th>#</th><th>ID</th><th>Nama</th><th>NISN</th><th>Status</th><th>Punya Tagihan Juli?</th></tr>";
    foreach ($realUnassigned->values() as $i => $student) {
        $hasBill = StudentBill::where('student_id', $student->id)->where('month', 7)->where('year', 2026)->exists();
        $billStr = $hasBill ? '✅ Ya' : '❌ Tidak';
        echo "<tr><td>" . ($i+1) . "</td><td>{$student->id}</td><td class='warning'>{$student->full_name}</td><td>{$student->nisn}</td><td>{$student->status}</td><td>{$billStr}</td></tr>";
    }
    echo "</table>";
    echo "<div class='warning'>⚠️ " . $realUnassigned->count() . " siswa aktif belum di-assign ke kelas TP aktif</div>";
}
echo "</div>";

// ═══════════════════════════════════════════════════════════
// BAGIAN 4: Semua siswa tanpa kelas (termasuk non-siswa)  
// ═══════════════════════════════════════════════════════════
echo "<div class='section'>";
echo "<div class='header'>📋 Semua Siswa Aktif Tanpa Kelas (Total)</div>";
echo "<table>";
echo "<tr><th>#</th><th>ID</th><th>Nama</th><th>NISN</th><th>Tipe</th></tr>";
foreach ($unassignedStudents->values() as $i => $student) {
    $isNonSiswa = in_array(strtolower($student->full_name), $nonSiswaLower);
    $type = $isNonSiswa ? "<span class='notfound'>Non-Siswa</span>" : "<span class='warning'>Perlu Kelas</span>";
    echo "<tr><td>" . ($i+1) . "</td><td>{$student->id}</td><td>{$student->full_name}</td><td>{$student->nisn}</td><td>{$type}</td></tr>";
}
echo "</table>";
echo "<div class='info'>Total tanpa kelas: {$unassignedStudents->count()} ({$nonSiswaFoundCount} non-siswa + " . $realUnassigned->count() . " perlu kelas)</div>";
echo "</div>";

echo "<br><hr><div class='info'>⏰ " . date('Y-m-d H:i:s') . "</div>";
echo "</body></html>";
