<?php
/**
 * Script untuk menghapus 18 siswa aktif yang tidak punya assignment kelas
 * di SMP Swasta Pembda 2.
 * 
 * Akses:
 * - Dry run (preview): /delete_18_smp_unassigned.php?secret=pembda99&dry_run=1
 * - Eksekusi hapus:    /delete_18_smp_unassigned.php?secret=pembda99
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

$dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>Hapus 18 Siswa Tanpa Kelas SMP Pembda 2</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0;}";
echo ".found{color:#00ff88;}.notfound{color:#ff6b6b;}.header{color:#00d4ff;font-size:18px;}";
echo ".warning{color:#ffd700;}.success{color:#00ff88;font-weight:bold;}.info{color:#aaa;}";
echo "table{border-collapse:collapse;margin:10px 0;width:100%;}th,td{border:1px solid #444;padding:6px 12px;text-align:left;}";
echo "th{background:#2a2a4a;color:#00d4ff;}</style></head><body>";

echo "<div class='header'>🗑️ Penghapusan 18 Siswa Aktif Tanpa Kelas (SMP Swasta Pembda 2)</div><hr>";

if ($dryRun) {
    echo "<div class='warning'>⚠️ MODE DRY RUN - Tidak ada data yang dihapus. Ini hanya preview.</div><br>";
} else {
    echo "<div class='warning'>🔴 MODE EKSEKUSI - Data akan DIHAPUS PERMANEN!</div><br>";
}

$school = School::where('name', 'like', '%SMP Swasta Pembda 2%')->first();
if (!$school) {
    echo "<div class='notfound'>❌ Sekolah tidak ditemukan!</div></body></html>";
    exit;
}

$activeYear = AcademicYear::where('is_active', true)->first();

$siswaWithClass = DB::table('student_classes')
    ->join('students', 'student_classes.student_id', '=', 'students.id')
    ->where('students.school_id', $school->id)
    ->where('students.status', 'aktif')
    ->where('student_classes.academic_year_id', $activeYear->id ?? 0)
    ->where('student_classes.status', 'aktif')
    ->distinct('student_classes.student_id')
    ->pluck('student_classes.student_id')
    ->toArray();

$allActiveStudents = Student::where('school_id', $school->id)->where('status', 'aktif')->get();
$unassignedStudents = $allActiveStudents->filter(fn($s) => !in_array($s->id, $siswaWithClass));

// Filter out 13 non-siswa if they were accidentally re-added or still active
$nonSiswaNames = [
    'ALFREDO BERNAT F. MENDROFA', 'APRIL YADI HULU', 'ETHELBERT GIEFORD TELAUMBANUA',
    'INTAN TRINASATI GULO', 'JEFRISON ZEBUA', 'JUNIOR EKA SAPUTRA GULO',
    'Kevin Putra Damai Ndraha', 'Okta Melfin Zebua', 'PELITA REFORMASI ZEGA',
    'RAJA FAOMASI KALOKO', 'Samuel Kristian Bawamenewi', 'Seli Silwanda Zebua',
    'WINDA SRIULINA TELAUMBANUA',
];
$nonSiswaLower = array_map('strtolower', $nonSiswaNames);
$realUnassigned = $unassignedStudents->filter(fn($s) => !in_array(strtolower($s->full_name), $nonSiswaLower))->values();

echo "<div class='info'>Ditemukan " . $realUnassigned->count() . " siswa aktif tanpa kelas.</div>";

if ($realUnassigned->count() === 0) {
    echo "<div class='found'>✅ Tidak ada siswa yang perlu dihapus.</div></body></html>";
    exit;
}

echo "<table>";
echo "<tr><th>#</th><th>ID</th><th>Nama</th><th>NISN</th><th>Relasi Data</th></tr>";

foreach ($realUnassigned as $i => $student) {
    // Hitung relasi data (untuk info saja)
    $relasi = [];
    $billCount = $student->bills()->count();
    $paymentCount = $student->payments()->count();
    $gradeCount = $student->grades()->count();
    $attendanceCount = $student->attendances()->count();
    $classCount = $student->studentClasses()->count();
    
    if ($billCount > 0) $relasi[] = "Tagihan: {$billCount}";
    if ($paymentCount > 0) $relasi[] = "Pembayaran: {$paymentCount}";
    if ($gradeCount > 0) $relasi[] = "Nilai: {$gradeCount}";
    if ($attendanceCount > 0) $relasi[] = "Absensi: {$attendanceCount}";
    if ($classCount > 0) $relasi[] = "Kelas Lama: {$classCount}";
    
    $relasiStr = empty($relasi) ? '<em>Tidak ada</em>' : implode(', ', $relasi);

    echo "<tr><td>" . ($i+1) . "</td><td>{$student->id}</td><td class='warning'>{$student->full_name}</td><td>{$student->nisn}</td><td>{$relasiStr}</td></tr>";
}
echo "</table><br>";

if (!$dryRun) {
    echo "<hr><div class='header'>🔄 Proses Penghapusan...</div><br>";
    $deleted = 0;
    
    DB::beginTransaction();
    try {
        foreach ($realUnassigned as $student) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            
            $user = $student->user;
            $studentName = $student->full_name;
            $studentId = $student->id;
            
            // Note: Bills without CASCADE need to be deleted explicitly if not handled by DB constraint
            // Hapus tagihan yang terkait
            $student->bills()->delete();
            $student->payments()->delete();
            $student->attendances()->delete();
            $student->grades()->delete();
            $student->studentClasses()->delete();
            
            $student->delete();
            
            if ($user) {
                $user->delete();
            }
            
            echo "<div class='success'>✅ Berhasil menghapus: {$studentName} (ID: {$studentId})</div>";
            $deleted++;
        }
        
        DB::commit();
        Log::info("Deleted {$deleted} unassigned active students from SMP Pembda 2", [
            'names' => $realUnassigned->pluck('full_name')->toArray(),
        ]);
        
        echo "<br><div class='success'>🎉 Total dihapus: {$deleted} siswa berhasil dihapus secara permanen!</div>";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "<div class='notfound'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='warning'>⚠️ Semua perubahan di-rollback. Tidak ada data yang dihapus.</div>";
    }
} else {
    echo "<br><div class='warning'>💡 Untuk mengeksekusi penghapusan, akses tanpa parameter dry_run:</div>";
    echo "<div class='info'><code>/delete_18_smp_unassigned.php?secret=pembda99</code></div>";
}

echo "<br><hr><div class='info'>⏰ Waktu eksekusi: " . date('Y-m-d H:i:s') . "</div>";
echo "</body></html>";
