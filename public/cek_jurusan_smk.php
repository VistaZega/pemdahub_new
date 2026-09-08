<?php
$secret = $_GET["secret"] ?? "";
if ($secret !== "pembda99") {
    die("Akses ditolak. Tambahkan ?secret=pembda99");
}

require_once __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\Student;
use App\Models\School;
use App\Models\Major;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

header("Content-Type: text/html; charset=utf-8");

$activeYear = AcademicYear::where("is_active", true)->first();
$smkSchools = School::where("type", "SMK")->get();
$smkSchoolIds = $smkSchools->pluck("id")->toArray();

$hasMajorColumn = Schema::hasColumn("students", "major_id");

// Fitur Auto Assign jika diminta
$autoAssign = isset($_GET["auto_assign"]) && $_GET["auto_assign"] === "1";
$assignedCount = 0;

if ($autoAssign && $hasMajorColumn) {
    $majors = Major::all();
    $teMajor   = $majors->first(fn($m) => in_array(strtoupper($m->code ?? $m->major_code ?? ''), ['TE', 'TAV']) || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'ELEKTRONIKA'));
    $dpibMajor = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'DPIB' || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'BANGUNAN'));
    $tsmMajor  = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'TSM' || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'MOTOR'));
    $tkrMajor  = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'TKR' || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'KENDARAAN') || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'OTOMOTIF'));
    $tkjMajor  = $majors->first(fn($m) => in_array(strtoupper($m->code ?? $m->major_code ?? ''), ['TKJ', 'TJKT']) || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'JARINGAN'));

    $nullSmkStudents = Student::whereIn("school_id", $smkSchoolIds)->whereNull("major_id")->with("classrooms")->get();
    foreach ($nullSmkStudents as $std) {
        $foundMajorId = null;
        // 1. Cek dari nama kelas
        foreach ($std->classrooms as $cls) {
            $cn = strtoupper($cls->class_name);
            if (preg_match('/\b(TE|TAV)\b/i', $cn)) { $foundMajorId = $teMajor?->id; break; }
            if (preg_match('/\b(DPIB)\b/i', $cn)) { $foundMajorId = $dpibMajor?->id; break; }
            if (preg_match('/\b(TSM|TBSM)\b/i', $cn)) { $foundMajorId = $tsmMajor?->id; break; }
            if (preg_match('/\b(TKR|TO)\b/i', $cn)) { $foundMajorId = $tkrMajor?->id; break; }
            if (preg_match('/\b(TKJ|TJKT|ACP)\b/i', $cn)) { $foundMajorId = $tkjMajor?->id; break; }
        }
        if ($foundMajorId) {
            $std->update(["major_id" => $foundMajorId]);
            $assignedCount++;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Audit & Verifikasi Jurusan Siswa SMK</title>
    <style>
        body { font-family: Segoe UI, sans-serif; padding: 24px; background: #f8fafc; color: #0f172a; line-height: 1.5; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        .card { background: white; border-radius: 12px; padding: 16px 20px; margin: 12px 0; box-shadow: 0 1px 4px rgba(0,0,0,.08); border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 8px; }
        th { background: #1e293b; color: white; padding: 8px 12px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: bold; }
        .g { background: #dcfce7; color: #166534; }
        .r { background: #fee2e2; color: #991b1b; }
        .y { background: #fef9c3; color: #854d0e; }
        .b { background: #dbeafe; color: #1d4ed8; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 12px 0; }
        .stat-card { background: white; padding: 14px; border-radius: 10px; border: 1px solid #cbd5e1; text-align: center; }
        .stat-num { font-size: 26px; font-weight: 900; margin: 4px 0; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; color: white; text-decoration: none; font-weight: bold; font-size: 12px; }
        .btn-green { background: #16a34a; }
        .btn-blue { background: #2563eb; }
    </style>
</head>
<body>

<h1>🔍 Hasil Audit Kolom & Jurusan Siswa SMK Swasta Pembda Nias</h1>
<p style="color:#64748b;font-size:13px">Tahun Pelajaran: <strong><?= htmlspecialchars($activeYear?->year_name ?? 'TP Aktif') ?></strong></p>

<?php if ($assignedCount > 0): ?>
<div class="card" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
    ✓ <strong>Berhasil mengupdate <?= $assignedCount ?> siswa SMK</strong> yang sebelumnya belum terisi jurusan!
</div>
<?php endif; ?>

<div class="card">
    <h3>1. Status Kolom <code>major_id</code> di Tabel <code>students</code></h3>
    <?php if ($hasMajorColumn): ?>
        <span class="badge g" style="font-size:13px">✓ KOLOM TERSEDIA: Kolom `major_id` sudah ada di tabel `students`</span>
    <?php else: ?>
        <span class="badge r" style="font-size:13px">✗ BELUM ADA: Jalankan migrasi via /run-migrations?secret=pembda99</span>
    <?php endif; ?>
</div>

<?php
// Query seluruh siswa SMK
$smkStudentsQuery = Student::whereIn("school_id", $smkSchoolIds)->where("status", "aktif");
$totalSmk = $smkStudentsQuery->count();

$withMajor = (clone $smkStudentsQuery)->whereNotNull("major_id")->count();
$withoutMajor = (clone $smkStudentsQuery)->whereNull("major_id")->count();
$pctWithMajor = $totalSmk > 0 ? round(($withMajor / $totalSmk) * 100, 1) : 0;
?>

<div class="grid">
    <div class="stat-card">
        <div style="font-size:11px;color:#64748b;font-weight:bold">TOTAL SISWA SMK AKTIF</div>
        <div class="stat-num text-slate-800"><?= $totalSmk ?></div>
        <div style="font-size:11px;color:#64748b">Orang</div>
    </div>
    <div class="stat-card" style="background:#f0fdf4;border-color:#86efac;">
        <div style="font-size:11px;color:#166534;font-weight:bold">SUDAH PUNYA JURUSAN</div>
        <div class="stat-num" style="color:#16a34a"><?= $withMajor ?></div>
        <div style="font-size:11px;color:#166534"><strong><?= $pctWithMajor ?>%</strong> Terisi Permanen</div>
    </div>
    <div class="stat-card" style="<?= $withoutMajor > 0 ? 'background:#fff1f2;border-color:#fca5a5;' : 'background:#f8fafc;' ?>">
        <div style="font-size:11px;color:<?= $withoutMajor > 0 ? '#991b1b' : '#64748b' ?>;font-weight:bold">BELUM TERISI</div>
        <div class="stat-num" style="color:<?= $withoutMajor > 0 ? '#dc2626' : '#64748b' ?>"><?= $withoutMajor ?></div>
        <div style="font-size:11px;color:<?= $withoutMajor > 0 ? '#991b1b' : '#64748b' ?>">
            <?= $withoutMajor === 0 ? '✓ 100% Bersih' : 'Perlu di-assign' ?>
        </div>
    </div>
</div>

<?php if ($withoutMajor > 0): ?>
<div class="card" style="background:#fffbeb;border-color:#fef08a;">
    <strong style="color:#854d0e">⚠️ Masih ada <?= $withoutMajor ?> siswa SMK yang belum terisi jurusannya.</strong>
    <p style="font-size:12px;margin:6px 0 10px;color:#854d0e">Klik tombol di bawah ini untuk mengisinya secara otomatis dari rombel mereka:</p>
    <a href="?secret=pembda99&auto_assign=1" class="btn btn-blue">⚡ Isi Otomatis Jurusan untuk <?= $withoutMajor ?> Siswa Ini</a>
</div>
<?php endif; ?>

<div class="card">
    <h3>2. Rekapitulasi Siswa per Jurusan SMK</h3>
    <?php
    $majorStats = DB::table('students')
        ->whereIn('students.school_id', $smkSchoolIds)
        ->where('students.status', 'aktif')
        ->leftJoin('majors', 'students.major_id', '=', 'majors.id')
        ->select(
            DB::raw("COALESCE(majors.major_code, 'TANPA JURUSAN') as major_code"),
            DB::raw("COALESCE(majors.major_name, 'Belum Di-set') as major_name"),
            DB::raw("count(*) as total")
        )
        ->groupBy(DB::raw("COALESCE(majors.major_code, 'TANPA JURUSAN')"), DB::raw("COALESCE(majors.major_name, 'Belum Di-set')"))
        ->orderByDesc('total')
        ->get();
    ?>
    <table>
        <tr>
            <th>Kode Jurusan</th>
            <th>Nama Lengkap Jurusan</th>
            <th style="text-align:center">Jumlah Siswa</th>
            <th style="text-align:center">Status</th>
        </tr>
        <?php foreach ($majorStats as $ms): ?>
        <tr>
            <td><strong><?= htmlspecialchars($ms->major_code) ?></strong></td>
            <td><?= htmlspecialchars($ms->major_name) ?></td>
            <td style="text-align:center"><strong><?= $ms->total ?></strong></td>
            <td style="text-align:center">
                <?php if ($ms->major_code === 'TANPA JURUSAN'): ?>
                    <span class="badge r">Perlu Diperbaiki</span>
                <?php else: ?>
                    <span class="badge g">✓ Valid</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h3>3. Uji Kasus Khusus: Kelas Gabungan XI Teknik Rekayasa (DPIB, TAV ) - ID: 370</h3>
    <p style="font-size:12px;color:#64748b">Memastikan ke-28 siswa di kelas ini terbagi dengan tepat antara TE (17 orang) dan DPIB (11 orang):</p>
    <?php
    $trStudents = Student::whereHas('studentClasses', function($q) use ($activeYear) {
        $q->where('classroom_id', 370)
          ->where('status', 'aktif')
          ->when($activeYear, fn($sq) => $sq->where('academic_year_id', $activeYear->id));
    })->with('major')->orderBy('full_name')->get();

    $teCount = $trStudents->filter(fn($s) => in_array(strtoupper($s->major?->code ?? ''), ['TE', 'TAV']))->count();
    $dpibCount = $trStudents->filter(fn($s) => strtoupper($s->major?->code ?? '') === 'DPIB')->count();
    $unassigned = $trStudents->filter(fn($s) => empty($s->major_id))->count();
    ?>
    
    <div style="margin-bottom:10px;">
        <span class="badge b" style="font-size:12px">Total Siswa: <?= $trStudents->count() ?></span>
        <span class="badge g" style="font-size:12px">TE / TAV: <?= $teCount ?> Orang</span>
        <span class="badge y" style="font-size:12px">DPIB: <?= $dpibCount ?> Orang</span>
        <?php if ($unassigned > 0): ?>
            <span class="badge r" style="font-size:12px">Belum Terdeteksi: <?= $unassigned ?></span>
        <?php else: ?>
            <span class="badge g" style="font-size:12px">✓ Semua 100% Terdeteksi!</span>
        <?php endif; ?>
    </div>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>NISN</th>
            <th>Jurusan Permanen</th>
            <th>Mapel yang Relevan</th>
        </tr>
        <?php foreach ($trStudents as $idx => $st): ?>
        <?php
            $mCode = $st->major?->code ?? 'BELUM DI-SET';
            $isTE = in_array(strtoupper($mCode), ['TE', 'TAV']);
            $isDPIB = (strtoupper($mCode) === 'DPIB');
        ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td><strong><?= htmlspecialchars($st->full_name) ?></strong></td>
            <td><code><?= htmlspecialchars($st->nisn ?? '-') ?></code></td>
            <td>
                <?php if ($isTE): ?>
                    <span class="badge b">TE / TAV (Teknik Elektronika)</span>
                <?php elseif ($isDPIB): ?>
                    <span class="badge y">DPIB (Bangunan)</span>
                <?php else: ?>
                    <span class="badge r"><?= htmlspecialchars($mCode) ?></span>
                <?php endif; ?>
            </td>
            <td>
                <?= $isTE ? '<span style="color:#1d4ed8;font-weight:bold">Kosentrasi Keahlian TE (Pak Yulianus Zega)</span>' : '<span style="color:#854d0e;font-weight:bold">KK-DPIB (Pak Resman / Pak Herman)</span>' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<hr style="border:1px solid #e2e8f0;margin:24px 0">
<p style="color:#94a3b8;font-size:11px">Audit URL: <code>perguruanpembda.com/cek_jurusan_smk.php?secret=pembda99</code></p>

</body>
</html>
