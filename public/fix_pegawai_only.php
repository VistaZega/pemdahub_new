<?php
header('Content-Type: text/html; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = $_GET['token'] ?? '';
if ($token !== 'pembda2026') {
    die("<h3>Akses ditolak. Tambahkan ?token=pembda2026 di URL.</h3>");
}

$execute = $_GET['execute'] ?? '';

echo "<h2>🔧 Fix Data Ganda (Arman & Yarisman menjadi Murni Pegawai)</h2>";

$employeeIds = [230, 231];
$teachers = App\Models\Teacher::whereIn('employee_id', $employeeIds)->get();

if ($teachers->isEmpty()) {
    echo "<h3 style='color:green;'>✅ Data Guru untuk kedua pegawai ini sudah tidak ada (sudah murni pegawai).</h3>";
    exit;
}

echo "<ul>";
foreach ($teachers as $teacher) {
    echo "<li>Ditemukan Data Guru: <b>{$teacher->full_name}</b> (Teacher ID: {$teacher->id})</li>";
    
    $hasRelations = false;
    $relationMsgs = [];
    
    if (class_exists('App\Models\TeachingAssignment') && App\Models\TeachingAssignment::where('teacher_id', $teacher->id)->exists()) {
        $relationMsgs[] = "Masih memiliki Jadwal/Penugasan Mengajar (TeachingAssignment).";
        $hasRelations = true;
    }
    if (class_exists('App\Models\Classroom') && App\Models\Classroom::where('homeroom_teacher_id', $teacher->id)->exists()) {
        $relationMsgs[] = "Masih terdaftar sebagai Wali Kelas.";
        $hasRelations = true;
    }
    
    if ($hasRelations) {
        echo "<ul style='color:red;'>";
        foreach ($relationMsgs as $msg) echo "<li>$msg</li>";
        echo "</ul>";
        echo "<p style='color:orange;'>-> Solusi: Mengubah status Guru menjadi Nonaktif (is_active = 0) alih-alih menghapus data agar tidak merusak sistem.</p>";
    } else {
        echo "<p style='color:green;'>-> Aman untuk dihapus permanen dari tabel Guru.</p>";
    }
}
echo "</ul>";

if ($execute !== '1') {
    echo "<hr>";
    echo "<h3>Mode Pratinjau (Dry Run)</h3>";
    echo "<p>Tidak ada data yang diubah. Jika Anda sudah yakin ingin mengeksekusi (menghapus/menonaktifkan data Guru mereka), klik tombol di bawah ini:</p>";
    echo "<a href='?token=pembda2026&execute=1' style='display:inline-block; padding:10px 20px; background:#e74c3c; color:white; text-decoration:none; border-radius:5px;'>Eksekusi Perbaikan Sekarang</a>";
} else {
    echo "<hr>";
    echo "<h3>Mengeksekusi Perbaikan...</h3>";
    
    foreach ($teachers as $teacher) {
        try {
            $hasRelations = false;
            if (class_exists('App\Models\TeachingAssignment') && App\Models\TeachingAssignment::where('teacher_id', $teacher->id)->exists()) $hasRelations = true;
            if (class_exists('App\Models\Classroom') && App\Models\Classroom::where('homeroom_teacher_id', $teacher->id)->exists()) $hasRelations = true;
            
            if ($hasRelations) {
                $teacher->is_active = false;
                $teacher->save();
                echo "<p>✅ <b>{$teacher->full_name}</b> dinonaktifkan sebagai Guru (karena masih terikat jadwal/wali kelas). Data Pegawai TU tetap aman.</p>";
            } else {
                $name = $teacher->full_name;
                $teacher->delete();
                echo "<p>✅ <b>{$name}</b> berhasil dihapus dari tabel Guru. Beliau kini murni sebagai Pegawai TU.</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color:red;'>❌ Gagal memproses <b>{$teacher->full_name}</b>. Error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h4>Selesai! Anda bisa mengecek kembali di sistem (Menu Pegawai & Menu Guru).</h4>";
}
