<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

if (request('secret') !== 'pembda99') {
    die('Akses ditolak.');
}

$nisn = request('nisn');
if (!$nisn) {
    echo "<h2>Tool Perbaikan Status PKL Siswa</h2>";
    echo "<p>Gunakan tool ini untuk mereset status PKL siswa yang tidak sengaja sudah dinilai oleh mentor (sehingga dianggap Selesai).</p>";
    echo "<form method='GET'>";
    echo "<input type='hidden' name='secret' value='pembda99'>";
    echo "<label>NISN Siswa:</label><br>";
    echo "<input type='text' name='nisn' placeholder='Contoh: 0099823090' required><br><br>";
    echo "<button type='submit'>Reset Status PKL</button>";
    echo "</form>";
    die();
}

$student = \App\Models\Student::where('nisn', $nisn)->first();
if (!$student) {
    die("Siswa dengan NISN $nisn tidak ditemukan. <a href='?secret=pembda99'>Kembali</a>");
}

$placement = \App\Models\PklPlacement::where('student_id', $student->id)->latest()->first();
if (!$placement) {
    die("Siswa $student->full_name tidak memiliki data penempatan PKL. <a href='?secret=pembda99'>Kembali</a>");
}

$grade = \App\Models\PklGrade::where('pkl_placement_id', $placement->id)->first();

if (!$grade) {
    die("Siswa $student->full_name (Placement ID: $placement->id) belum memiliki nilai akhir PKL. Status PKL saat ini sudah aktif (Belum dinilai). <a href='?secret=pembda99'>Kembali</a>");
}

// 1. Kurangi poin reputasi yang didapat dari penilaian PKL (Student & Teacher)
$logs = \App\Models\ReputationLog::where('reference_type', \App\Models\PklGrade::class)
            ->where('reference_id', $grade->id)
            ->get();

foreach ($logs as $log) {
    $rep = \App\Models\Reputation::where('user_id', $log->user_id)->first();
    if ($rep) {
        $rep->total_points -= $log->points;
        $rep->save();
    }
    $log->delete();
}

// 2. Hapus nilai PKL
$grade->delete();

echo "<h3>Berhasil!</h3>";
echo "Nilai PKL untuk siswa <b>$student->full_name</b> (NISN: $nisn) telah berhasil dihapus.<br>";
echo "Poin reputasi terkait juga telah dikembalikan.<br>";
echo "Status PKL siswa ini sekarang kembali AKTIF dan logbook bisa diisi kembali.<br><br>";
echo "<a href='?secret=pembda99'>Kembali</a>";
