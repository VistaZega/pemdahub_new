<?php
/**
 * Script untuk menghapus siswa yang bukan siswa dari SMP Swasta Pembda 2
 * 
 * Akses:
 * - Dry run (preview): /delete_non_students.php?secret=pembda99&dry_run=1
 * - Eksekusi hapus:     /delete_non_students.php?secret=pembda99
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;
use App\Models\School;
use App\Services\StudentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Security check
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

$dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>Hapus Non-Siswa SMP Pembda 2</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0;}";
echo ".found{color:#00ff88;}.notfound{color:#ff6b6b;}.header{color:#00d4ff;font-size:18px;}";
echo ".warning{color:#ffd700;}.success{color:#00ff88;font-weight:bold;}.info{color:#aaa;}";
echo "table{border-collapse:collapse;margin:10px 0;}th,td{border:1px solid #444;padding:6px 12px;text-align:left;}";
echo "th{background:#2a2a4a;color:#00d4ff;}</style></head><body>";

echo "<div class='header'>🗑️ Penghapusan Non-Siswa SMP Swasta Pembda 2</div>";
echo "<hr>";

if ($dryRun) {
    echo "<div class='warning'>⚠️ MODE DRY RUN - Tidak ada data yang dihapus. Ini hanya preview.</div><br>";
} else {
    echo "<div class='warning'>🔴 MODE EKSEKUSI - Data akan DIHAPUS PERMANEN!</div><br>";
}

// Daftar nama yang harus dihapus
$namesToDelete = [
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

// Cari SMP Swasta Pembda 2
$school = School::where('name', 'like', '%SMP Swasta Pembda 2%')->first();
if (!$school) {
    echo "<div class='notfound'>❌ Sekolah SMP Swasta Pembda 2 tidak ditemukan!</div>";
    echo "</body></html>";
    exit;
}
echo "<div class='info'>📍 Sekolah: {$school->name} (ID: {$school->id})</div><br>";

// Cari siswa-siswa tersebut
echo "<table>";
echo "<tr><th>#</th><th>Nama</th><th>Status</th><th>ID</th><th>NISN</th><th>Kelas</th><th>User</th><th>Relasi Data</th></tr>";

$foundStudents = [];
$notFoundNames = [];

foreach ($namesToDelete as $i => $name) {
    // Cari case-insensitive
    $student = Student::where('school_id', $school->id)
        ->whereRaw('LOWER(full_name) = ?', [strtolower($name)])
        ->first();
    
    $no = $i + 1;
    
    if ($student) {
        $foundStudents[] = $student;
        
        // Hitung relasi data
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
        if ($classCount > 0) $relasi[] = "Kelas: {$classCount}";
        
        $relasiStr = empty($relasi) ? '<em>Tidak ada</em>' : implode(', ', $relasi);
        $classroom = $student->currentClassroom()->first();
        $classStr = $classroom ? $classroom->class_name : '-';
        $userStr = $student->user ? "ID:{$student->user_id}" : '-';
        
        echo "<tr><td>{$no}</td><td class='found'>✅ {$student->full_name}</td>";
        echo "<td>{$student->status}</td><td>{$student->id}</td><td>{$student->nisn}</td>";
        echo "<td>{$classStr}</td><td>{$userStr}</td><td>{$relasiStr}</td></tr>";
    } else {
        $notFoundNames[] = $name;
        echo "<tr><td>{$no}</td><td class='notfound'>❌ {$name}</td>";
        echo "<td colspan='6' class='notfound'>Tidak ditemukan di database</td></tr>";
    }
}
echo "</table><br>";

echo "<div class='info'>📊 Ditemukan: " . count($foundStudents) . " dari " . count($namesToDelete) . " nama</div>";

if (!empty($notFoundNames)) {
    echo "<div class='warning'>⚠️ Nama tidak ditemukan (" . count($notFoundNames) . "): " . implode(', ', $notFoundNames) . "</div><br>";
}

// Proses penghapusan
if (!$dryRun && !empty($foundStudents)) {
    echo "<hr><div class='header'>🔄 Proses Penghapusan...</div><br>";
    
    $deleted = 0;
    $errors = [];
    
    DB::beginTransaction();
    try {
        foreach ($foundStudents as $student) {
            // Hapus foto jika ada
            if ($student->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($student->photo);
            }
            
            // Simpan user untuk dihapus nanti
            $user = $student->user;
            $studentName = $student->full_name;
            $studentId = $student->id;
            
            // Hapus student (cascade akan menghapus semua relasi)
            $student->delete();
            
            // Hapus user terkait jika ada
            if ($user) {
                $user->delete();
            }
            
            echo "<div class='success'>✅ Berhasil menghapus: {$studentName} (ID: {$studentId})</div>";
            $deleted++;
        }
        
        DB::commit();
        Log::info("Deleted {$deleted} non-student records from SMP Pembda 2", [
            'names' => collect($foundStudents)->pluck('full_name')->toArray(),
        ]);
        
        echo "<br><div class='success'>🎉 Total dihapus: {$deleted} siswa berhasil dihapus!</div>";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "<div class='notfound'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='warning'>⚠️ Semua perubahan di-rollback. Tidak ada data yang dihapus.</div>";
    }
} elseif ($dryRun) {
    echo "<br><div class='warning'>💡 Untuk menghapus, akses tanpa parameter dry_run:</div>";
    echo "<div class='info'><code>/delete_non_students.php?secret=pembda99</code></div>";
}

echo "<br><hr><div class='info'>⏰ Waktu eksekusi: " . date('Y-m-d H:i:s') . "</div>";
echo "</body></html>";
