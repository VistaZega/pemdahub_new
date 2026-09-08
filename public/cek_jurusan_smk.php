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

// Fitur Kalibrasi Ulang Presisi jika diminta
$doRecalc = isset($_GET["recalc"]) && $_GET["recalc"] === "1";
$recalcUpdated = 0;

if ($doRecalc && $hasMajorColumn) {
    $majors = DB::table('majors')->whereIn('school_id', $smkSchoolIds)->get();
    $majorsByCode = [];
    foreach ($majors as $m) {
        $code = strtoupper($m->major_code ?? $m->code ?? '');
        if ($code) {
            $majorsByCode[$code] = $m->id;
        }
    }

    $dpibId = $majorsByCode['DPIB'] ?? null;
    $tavId  = $majorsByCode['TAV'] ?? null;
    $teId   = $majorsByCode['TE'] ?? null;
    $tsmId  = $majorsByCode['TSM'] ?? null;
    $tkrId  = $majorsByCode['TKR'] ?? null;
    $toId   = $majorsByCode['TO'] ?? null;
    $tkjId  = $majorsByCode['TKJ'] ?? null;
    $tjktId = $majorsByCode['TJKT'] ?? null;
    $acpId  = $majorsByCode['ACP'] ?? null;

    $allSmkStudents = Student::whereIn("school_id", $smkSchoolIds)->get();

    // Mapping kelas reguler vs gabungan
    $studentClasses = DB::table('student_classes')
        ->join('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
        ->whereIn('student_classes.student_id', $allSmkStudents->pluck('id'))
        ->select('student_classes.student_id', 'classrooms.id as classroom_id', 'classrooms.class_name', 'classrooms.is_combined', 'classrooms.class_type')
        ->get();

    $regMap = [];
    $combMap = [];
    foreach ($studentClasses as $sc) {
        if (!$sc->is_combined && $sc->class_type !== 'gabungan') {
            $regMap[$sc->student_id][] = $sc->class_name;
        } else {
            $combMap[$sc->student_id][] = $sc->classroom_id;
        }
    }

    // Siswa yang mengambil / hadir mapel DDPK-DPIB di kelas gabungan X Teknik Rekayasa
    $dpibAttStudents = DB::table('attendances')
        ->join('schedules', 'attendances.schedule_id', '=', 'schedules.id')
        ->join('subjects', 'schedules.subject_id', '=', 'subjects.id')
        ->where('subjects.name', 'like', '%DPIB%')
        ->pluck('attendances.student_id')
        ->unique()
        ->toArray();

    foreach ($allSmkStudents as $std) {
        $targetId = null;
        $allClassNames = implode(' ', $regMap[$std->id] ?? []);

        // 1. Regular classroom mapping dengan word boundary
        if (preg_match('/\b(DPIB)\b/i', $allClassNames)) {
            $targetId = $dpibId;
        } elseif (preg_match('/\b(TE)\b/i', $allClassNames)) {
            $targetId = $teId ?? $tavId;
        } elseif (preg_match('/\b(TAV)\b/i', $allClassNames)) {
            $targetId = $tavId ?? $teId;
        } elseif (preg_match('/\b(ACP)\b/i', $allClassNames)) {
            $targetId = $acpId ?? $tkjId;
        } elseif (preg_match('/\b(TJKT)\b/i', $allClassNames)) {
            $targetId = $tjktId ?? $tkjId;
        } elseif (preg_match('/\b(TKJ)\b/i', $allClassNames)) {
            $targetId = $tkjId;
        } elseif (preg_match('/\b(TSM|TBSM)\b/i', $allClassNames)) {
            $targetId = $tsmId;
        } elseif (preg_match('/\b(TKR)\b/i', $allClassNames)) {
            $targetId = $tkrId;
        } elseif (preg_match('/\b(TO)\b/i', $allClassNames)) {
            $targetId = $toId ?? $tkrId;
        }

        // 2. Jika siswa hanya terdaftar di kelas gabungan X Teknik Rekayasa (DPIB, TKR 2, TAV) (ID: 367)
        if (!$targetId && in_array(367, $combMap[$std->id] ?? [])) {
            if (in_array($std->id, $dpibAttStudents)) {
                $targetId = $dpibId;
            } else {
                $targetId = $tkrId;
            }
        }

        // 3. Jika siswa di kelas gabungan XII Teknik Rekayasa TAV TKJ (ID: 368)
        if (!$targetId && in_array(368, $combMap[$std->id] ?? [])) {
            $targetId = $tavId;
        }

        if ($targetId && $std->major_id !== $targetId) {
            $std->update(['major_id' => $targetId]);
            $recalcUpdated++;
        }
    }
}

// Hitung statistik siswa SMK
$smkStudentsQuery = Student::whereIn("school_id", $smkSchoolIds)->where("status", "aktif");
$totalSmk = $smkStudentsQuery->count();

$withMajor = (clone $smkStudentsQuery)->whereNotNull("major_id")->count();
$withoutMajor = (clone $smkStudentsQuery)->whereNull("major_id")->count();
$pctWithMajor = $totalSmk > 0 ? round(($withMajor / $totalSmk) * 100, 1) : 0;

// Ambil seluruh jurusan SMK yang terdaftar di master data
$masterSmkMajors = Major::whereIn('school_id', $smkSchoolIds)->orderBy('major_code')->get();
$studentCountsByMajor = DB::table('students')
    ->whereIn('school_id', $smkSchoolIds)
    ->where('status', 'aktif')
    ->select('major_id', DB::raw('count(*) as total'))
    ->groupBy('major_id')
    ->pluck('total', 'major_id');

$unassignedCount = $studentCountsByMajor[null] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Audit & Verifikasi Jurusan Siswa SMK</title>
    <style>
        body { font-family: Segoe UI, -apple-system, sans-serif; padding: 24px; background: #0f172a; color: #f8fafc; line-height: 1.5; }
        .container { max-width: 1100px; margin: 0 auto; }
        h1 { font-size: 24px; margin-bottom: 4px; color: #38bdf8; }
        .card { background: #1e293b; border-radius: 12px; padding: 20px; margin: 16px 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,.3); border: 1px solid #334155; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
        th { background: #0f172a; color: #94a3b8; padding: 10px 14px; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: .05em; border-bottom: 2px solid #334155; }
        td { padding: 10px 14px; border-bottom: 1px solid #334155; }
        tr:nth-child(even) { background: rgba(255,255,255,.02); }
        tr:hover { background: rgba(255,255,255,.05); }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; }
        .g { background: #14532d; color: #86efac; border: 1px solid #22c55e; }
        .r { background: #7f1d1d; color: #fca5a5; border: 1px solid #ef4444; }
        .y { background: #713f12; color: #fde047; border: 1px solid #eab308; }
        .b { background: #1e3a8a; color: #93c5fd; border: 1px solid #3b82f6; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin: 16px 0; }
        .stat-card { background: #1e293b; padding: 16px; border-radius: 12px; border: 1px solid #334155; text-align: center; }
        .stat-num { font-size: 32px; font-weight: 900; margin: 4px 0; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; color: white; text-decoration: none; font-weight: bold; font-size: 13px; cursor: pointer; transition: all .2s; }
        .btn-green { background: #16a34a; border: 1px solid #22c55e; }
        .btn-green:hover { background: #15803d; }
        .btn-blue { background: #2563eb; border: 1px solid #3b82f6; }
        .btn-blue:hover { background: #1d4ed8; }
        .alert-box { padding: 14px 18px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 Hasil Audit & Kalibrasi Jurusan Siswa SMK Swasta Pembda Nias</h1>
    <p style="color:#94a3b8;font-size:13px;margin-bottom:20px;">Tahun Pelajaran: <strong><?= htmlspecialchars($activeYear?->year_name ?? 'TP Aktif') ?></strong></p>

    <?php if ($recalcUpdated > 0): ?>
    <div class="alert-box" style="background:#14532d;border:1px solid #22c55e;color:#86efac;">
        ✓ <strong>BERHASIL DIKALIBRASI ULANG!</strong> Sebanyak <strong><?= $recalcUpdated ?> siswa SMK</strong> telah disesuaikan jurusan aslinya (DPIB, TE, TAV, TKR, TSM, TKJ, dll.) ke kolom <code>major_id</code> secara permanen.
    </div>
    <?php endif; ?>

    <!-- STATS SUMMARY -->
    <div class="grid">
        <div class="stat-card">
            <div style="font-size:11px;color:#94a3b8;font-weight:bold">TOTAL SISWA SMK AKTIF</div>
            <div class="stat-num" style="color:#f8fafc"><?= $totalSmk ?></div>
            <div style="font-size:11px;color:#94a3b8">Orang</div>
        </div>
        <div class="stat-card" style="background:#064e3b;border-color:#059669;">
            <div style="font-size:11px;color:#a7f3d0;font-weight:bold">SUDAH PUNYA JURUSAN</div>
            <div class="stat-num" style="color:#34d399"><?= $withMajor ?></div>
            <div style="font-size:11px;color:#a7f3d0"><strong><?= $pctWithMajor ?>%</strong> Terisi Permanen</div>
        </div>
        <div class="stat-card" style="<?= $withoutMajor > 0 ? 'background:#7f1d1d;border-color:#dc2626;' : 'background:#1e293b;' ?>">
            <div style="font-size:11px;color:<?= $withoutMajor > 0 ? '#fca5a5' : '#94a3b8' ?>;font-weight:bold">BELUM TERISI</div>
            <div class="stat-num" style="color:<?= $withoutMajor > 0 ? '#f87171' : '#94a3b8' ?>"><?= $withoutMajor ?></div>
            <div style="font-size:11px;color:<?= $withoutMajor > 0 ? '#fca5a5' : '#94a3b8' ?>">
                <?= $withoutMajor === 0 ? '✓ 100% Bersih' : 'Perlu di-assign' ?>
            </div>
        </div>
    </div>

    <!-- TOMBOL AKSI KALIBRASI -->
    <div class="card" style="background:#1e293b;border-color:#38bdf8;">
        <h3 style="color:#38bdf8;margin-top:0">⚡ Tindakan Kalibrasi Ulang Presisi</h3>
        <p style="font-size:13px;color:#cbd5e1;margin-bottom:14px;">
            Jika angka DPIB masih 0 atau TE/TAV membengkak karena migrasi sebelumnya tertimpa keyword mapel, klik tombol di bawah ini untuk <strong>mengkalibrasi ulang seluruh 674 siswa SMK secara akurat</strong> ke jurusan masing-masing:
        </p>
        <a href="?secret=pembda99&recalc=1" class="btn btn-green">⚡ Kalibrasi Ulang Sekarang (Fix DPIB, TE, TAV, TKR, TSM, TKJ)</a>
    </div>

    <!-- 2. REKAPITULASI JURUSAN MASTER -->
    <div class="card">
        <h3 style="margin-top:0;color:#f8fafc">2. Rekapitulasi Siswa per Jurusan SMK Swasta Pembda Nias</h3>
        <p style="font-size:12px;color:#94a3b8">Menampilkan seluruh jurusan SMK yang terdaftar di master data beserta jumlah siswa aktif saat ini:</p>
        <table>
            <tr>
                <th>ID</th>
                <th>Kode Jurusan</th>
                <th>Nama Lengkap Jurusan</th>
                <th style="text-align:center">Jumlah Siswa</th>
                <th style="text-align:center">Status</th>
            </tr>
            <?php foreach ($masterSmkMajors as $m): ?>
            <?php 
                $count = $studentCountsByMajor[$m->id] ?? 0;
            ?>
            <tr>
                <td style="color:#94a3b8"><?= $m->id ?></td>
                <td><strong><span class="badge b"><?= htmlspecialchars($m->major_code ?? $m->code) ?></span></strong></td>
                <td><strong><?= htmlspecialchars($m->major_name ?? $m->name) ?></strong></td>
                <td style="text-align:center;font-size:15px">
                    <strong style="<?= $count > 0 ? 'color:#34d399;' : 'color:#94a3b8;' ?>"><?= $count ?> Orang</strong>
                </td>
                <td style="text-align:center">
                    <?php if ($count > 0): ?>
                        <span class="badge g">✓ <?= $count ?> Siswa Aktif</span>
                    <?php else: ?>
                        <span class="badge y">Belum Ada Siswa (Klik Kalibrasi)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if ($unassignedCount > 0): ?>
            <tr style="background:#7f1d1d33">
                <td>-</td>
                <td><span class="badge r">NULL</span></td>
                <td><strong style="color:#f87171">Belum Memiliki Jurusan</strong></td>
                <td style="text-align:center;font-size:15px"><strong style="color:#f87171"><?= $unassignedCount ?> Orang</strong></td>
                <td style="text-align:center"><span class="badge r">Perlu Kalibrasi</span></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- 3. KELAS GABUNGAN XI TEKNIK REKAYASA -->
    <div class="card">
        <h3 style="margin-top:0;color:#f8fafc">3. Uji Kasus: Kelas Gabungan XI Teknik Rekayasa (DPIB, TAV ) - ID: 370</h3>
        <p style="font-size:12px;color:#94a3b8">Verifikasi ke-28 siswa di kelas ini terbagi dengan tepat antara <strong>TE / TAV (17 orang)</strong> dan <strong>DPIB (11 orang)</strong>:</p>
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
        
        <div style="margin:14px 0;">
            <span class="badge b" style="font-size:13px;padding:6px 12px">Total Siswa: <?= $trStudents->count() ?></span>
            <span class="badge g" style="font-size:13px;padding:6px 12px">TE / TAV: <?= $teCount ?> Orang</span>
            <span class="badge y" style="font-size:13px;padding:6px 12px">DPIB: <?= $dpibCount ?> Orang</span>
            <?php if ($teCount === 17 && $dpibCount === 11): ?>
                <span class="badge g" style="font-size:13px;padding:6px 12px">✓ 100% Sempurna (17 TE & 11 DPIB)!</span>
            <?php else: ?>
                <span class="badge r" style="font-size:13px;padding:6px 12px">⚠️ Belum Ideal (Klik Kalibrasi di Atas)</span>
            <?php endif; ?>
        </div>

        <table>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Jurusan Permanen</th>
                <th>Mapel Relevan</th>
            </tr>
            <?php foreach ($trStudents as $idx => $st): ?>
            <?php
                $mCode = $st->major?->code ?? 'BELUM DI-SET';
                $isTE = in_array(strtoupper($mCode), ['TE', 'TAV']);
                $isDPIB = (strtoupper($mCode) === 'DPIB');
            ?>
            <tr>
                <td style="color:#94a3b8"><?= $idx + 1 ?></td>
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
                    <?= $isTE ? '<span style="color:#60a5fa;font-weight:600">Kosentrasi Keahlian TE (Pak Yulianus Zega)</span>' : '<span style="color:#facc15;font-weight:600">KK-DPIB (Pak Resman / Pak Herman)</span>' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- LINK KEMBALI / DIAGNOSTIK LAIN -->
    <div style="margin-top:20px;text-align:center;">
        <a href="cek_joy_lms.php?secret=pembda99" class="btn btn-blue">🔍 Buka Diagnostik Joy Wise & Course 221 (Mikrokontroler)</a>
    </div>
</div>

</body>
</html>
