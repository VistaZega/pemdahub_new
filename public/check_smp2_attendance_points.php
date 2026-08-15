<?php
/**
 * DIAGNOSTIC SCRIPT - Audit Poin Absensi & Reputasi SMPS Pembda 2
 *
 * Akses: perguruanpembda.com/check_smp2_attendance_points.php?secret=pembda99
 */

// ── Keamanan ──
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<h2 style="color:red; font-family:sans-serif; text-align:center; margin-top:50px;">403 Forbidden - Parameter ?secret=pembda99 diperlukan!</h2>');
}

@set_time_limit(300);
@ini_set('memory_limit', '512M');

// ── Bootstrap Laravel ──
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\User;
use App\Models\Reputation;
use App\Models\ReputationLog;

// 1. Dapatkan Data Sekolah SMPS Pembda 2
$smpSchool = School::where('id', 1)->orWhere('name', 'like', '%SMPS Pembda 2%')->first();
$schoolId = $smpSchool ? $smpSchool->id : 1;
$schoolName = $smpSchool ? $smpSchool->name : 'SMPS Pembda 2 Gunungsitoli';

// 2. Ambil Top 20 Pengguna dengan Poin Tertinggi di SMPS Pembda 2
$topUsers = DB::table('reputations')
    ->join('users', 'reputations.user_id', '=', 'users.id')
    ->where('users.school_id', $schoolId)
    ->select(
        'users.id as user_id',
        'users.name',
        'users.role',
        'reputations.total_points',
        'reputations.level_name'
    )
    ->orderByDesc('reputations.total_points')
    ->limit(25)
    ->get();

// 3. Analisis Kategori Poin Terbesar di SMPS Pembda 2
$categoryStats = DB::table('reputation_logs')
    ->join('users', 'reputation_logs.user_id', '=', 'users.id')
    ->where('users.school_id', $schoolId)
    ->select('reputation_logs.category', DB::raw('COUNT(*) as total_logs'), DB::raw('SUM(reputation_logs.points) as total_points'))
    ->groupBy('reputation_logs.category')
    ->orderByDesc('total_points')
    ->get();

// 4. Deteksi Anomali 1: Guru dengan Input Absensi Berulang (category: attendance_input)
$topTeacherAttendanceInputs = DB::table('reputation_logs')
    ->join('users', 'reputation_logs.user_id', '=', 'users.id')
    ->where('users.school_id', $schoolId)
    ->where('reputation_logs.category', 'attendance_input')
    ->select(
        'users.id as user_id',
        'users.name',
        DB::raw('COUNT(*) as input_count'),
        DB::raw('SUM(reputation_logs.points) as points_accumulated')
    )
    ->groupBy('users.id', 'users.name')
    ->orderByDesc('points_accumulated')
    ->limit(10)
    ->get();

// 5. Deteksi Anomali 2: Siswa yang Mendapatkan Poin Absensi Lebih dari 1x dalam 1 Hari yang Sama
$duplicateStudentAttendanceDays = DB::table('reputation_logs')
    ->join('users', 'reputation_logs.user_id', '=', 'users.id')
    ->where('users.school_id', $schoolId)
    ->where('reputation_logs.category', 'attendance')
    ->where('users.role', 'siswa')
    ->select(
        'users.id as user_id',
        'users.name',
        DB::raw('DATE(reputation_logs.created_at) as log_date'),
        DB::raw('COUNT(*) as logs_in_same_day'),
        DB::raw('SUM(reputation_logs.points) as points_in_same_day')
    )
    ->groupBy('users.id', 'users.name', DB::raw('DATE(reputation_logs.created_at)'))
    ->having('logs_in_same_day', '>', 1)
    ->orderByDesc('logs_in_same_day')
    ->limit(15)
    ->get();

// 6. Deteksi Anomali 3: Siswa dengan Total Poin Absensi Ekstrem (> 300 Poin)
$extremeStudentAttendances = DB::table('reputation_logs')
    ->join('users', 'reputation_logs.user_id', '=', 'users.id')
    ->where('users.school_id', $schoolId)
    ->where('reputation_logs.category', 'attendance')
    ->where('users.role', 'siswa')
    ->select(
        'users.id as user_id',
        'users.name',
        DB::raw('COUNT(*) as attendance_logs_count'),
        DB::raw('SUM(reputation_logs.points) as total_attendance_points')
    )
    ->groupBy('users.id', 'users.name')
    ->orderByDesc('total_attendance_points')
    ->limit(15)
    ->get();

// 7. Sampel 20 Log Absensi Terakhir di SMPS Pembda 2
$recentAttendanceLogs = DB::table('reputation_logs')
    ->join('users', 'reputation_logs.user_id', '=', 'users.id')
    ->where('users.school_id', $schoolId)
    ->whereIn('reputation_logs.category', ['attendance', 'attendance_input'])
    ->select(
        'reputation_logs.id',
        'users.name as user_name',
        'users.role as user_role',
        'reputation_logs.points',
        'reputation_logs.category',
        'reputation_logs.description',
        'reputation_logs.created_at'
    )
    ->latest('reputation_logs.id')
    ->limit(20)
    ->get();

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🔍 Audit Poin Absensi & Reputasi SMPS Pembda 2</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    body { background: #0b0f19; color: #e2e8f0; min-height: 100vh; padding: 30px 15px; }
    .container { max-width: 1100px; margin: 0 auto; }
    .card { background: #131c2e; border-radius: 20px; padding: 28px; border: 1px solid #1e293b; box-shadow: 0 10px 30px rgba(0,0,0,0.5); margin-bottom: 24px; }
    .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b; padding-bottom: 20px; margin-bottom: 24px; }
    .title-group { display: flex; align-items: center; gap: 14px; }
    .icon { font-size: 2.2rem; }
    h1 { font-size: 1.4rem; font-weight: 800; color: #f8fafc; }
    .subtitle { color: #94a3b8; font-size: 0.8rem; margin-top: 4px; }
    .badge-school { background: #0369a1; color: #e0f2fe; font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: 99px; }

    /* Section Headings */
    h2 { font-size: 1.1rem; font-weight: 800; color: #f1f5f9; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }

    /* Table */
    .table-container { overflow-x: auto; margin-top: 10px; border-radius: 12px; border: 1px solid #1e293b; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; font-size: 0.82rem; text-align: left; }
    th { background: #0a1120; padding: 12px 16px; font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #1e293b; }
    td { padding: 12px 16px; border-bottom: 1px solid #131c2e; vertical-align: middle; }
    tr:nth-child(even) { background: #0e1626; }
    tr:hover { background: #17233a; }

    /* Pills */
    .pill { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 0.72rem; font-weight: 800; }
    .pill-role-guru { background: #1e1b4b; color: #a5b4fc; border: 1px solid #4338ca; }
    .pill-role-siswa { background: #064e3b; color: #6ee7b7; border: 1px solid #059669; }
    .pill-points { background: #3b0764; color: #f0abfc; font-weight: 900; }
    .pill-danger { background: #881337; color: #fda4af; font-weight: 900; }
    .pill-warning { background: #78350f; color: #fde68a; font-weight: 900; }

    /* Callout Boxes */
    .callout { background: #0f172a; border-radius: 14px; padding: 18px; border-left: 4px solid #3b82f6; margin-bottom: 20px; }
    .callout-warning { background: #1c1404; border-left-color: #f59e0b; }
    .callout-danger { background: #1c0a0f; border-left-color: #ef4444; }
    .callout h4 { font-size: 0.95rem; font-weight: 800; margin-bottom: 6px; }
    .callout p { font-size: 0.82rem; line-height: 1.6; color: #cbd5e1; }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="title-group">
                <span class="icon">🔍</span>
                <div>
                    <h1>Audit Diagnostik Poin & Absensi SMPS Pembda 2</h1>
                    <p class="subtitle">Pemeriksaan anomali poin absensi kehadiran, akumulasi ganda, dan input berulang.</p>
                </div>
            </div>
            <span class="badge-school">🏫 <?= htmlspecialchars($schoolName) ?> (ID: <?= $schoolId ?>)</span>
        </div>

        <!-- 1. Analisis Kategori Poin -->
        <h2>📊 1. Distribusi Poin Berdasarkan Kategori di SMPS Pembda 2</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Total Frekuensi (Log)</th>
                        <th>Total Akumulasi Poin</th>
                        <th>Rata-rata per Log</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($categoryStats->isEmpty()): ?>
                        <tr><td colspan="4" style="text-align:center; color:#64748b; padding:20px;">Belum ada catatan reputasi untuk SMPS Pembda 2.</td></tr>
                    <?php else: ?>
                        <?php foreach($categoryStats as $cat): ?>
                            <tr>
                                <td>
                                    <strong style="color:#38bdf8; font-size:0.85rem;"><?= htmlspecialchars(strtoupper($cat->category)) ?></strong>
                                </td>
                                <td><?= number_format($cat->total_logs) ?>x</td>
                                <td>
                                    <span class="pill pill-points" style="font-size:0.85rem;">
                                        <?= number_format($cat->total_points) ?> Poin
                                    </span>
                                </td>
                                <td style="color:#94a3b8;">
                                    <?= $cat->total_logs > 0 ? round($cat->total_points / $cat->total_logs, 1) : 0 ?> Poin/log
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 2. Temuan Potensi Anomali Guru -->
        <h2>👨‍🏫 2. Guru dengan Akumulasi Poin Input Absensi Terbanyak</h2>
        <?php if($topTeacherAttendanceInputs->isNotEmpty() && $topTeacherAttendanceInputs->first()->points_accumulated >= 200): ?>
            <div class="callout callout-warning">
                <h4 style="color:#fde68a;">⚠️ Potensi Anomali Ditemukan: Akumulasi Poin Input Guru</h4>
                <p>
                    Setiap kali guru menyimpan absensi kelas melalui menu Guru, sistem sebelumnya memberikan <strong>+20 Poin</strong> tanpa batas per hari (karena `$ref = null`). Jika guru menginput absensi berkali-kali atau per mapel, poinnya bertambah terus secara berlipat.
                </p>
            </div>
        <?php endif; ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Nama Guru / Pegawai</th>
                        <th>Jumlah Kali Simpan Absensi</th>
                        <th>Total Poin yang Didapat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($topTeacherAttendanceInputs->isEmpty()): ?>
                        <tr><td colspan="4" style="text-align:center; color:#64748b; padding:20px;">Tidak ada log input absensi guru.</td></tr>
                    <?php else: ?>
                        <?php foreach($topTeacherAttendanceInputs as $tg): ?>
                            <tr>
                                <td>#<?= $tg->user_id ?></td>
                                <td><strong><?= htmlspecialchars($tg->name) ?></strong></td>
                                <td><?= $tg->input_count ?> kali</td>
                                <td><span class="pill pill-warning">+<?= number_format($tg->points_accumulated) ?> Poin</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 3. Temuan Siswa dengan Poin Ganda di Hari yang Sama -->
        <h2>🎓 3. Siswa yang Mendapatkan Poin Absensi Lebih dari 1x dalam 1 Hari</h2>
        <?php if($duplicateStudentAttendanceDays->isNotEmpty()): ?>
            <div class="callout callout-danger">
                <h4 style="color:#fda4af;">🚨 Potensi Bug Ditemukan: Siswa Mendapat Poin Berulang di Hari yang Sama</h4>
                <p>
                    Tabel di bawah menunjukkan siswa yang mendapatkan poin absensi <strong>lebih dari 1 kali pada tanggal yang sama</strong> (misalnya karena diabsen oleh beberapa guru mapel berbeda atau gabungan tap RFID + manual). Poin kehadiran harian seharusnya hanya diberikan <strong>1x per hari (Maks. +10 Poin/hari)</strong>.
                </p>
            </div>
        <?php else: ?>
            <div class="callout" style="border-left-color:#10b981; background:#064e3b20;">
                <h4 style="color:#6ee7b7;">✅ Tidak Ditemukan Duplikasi Poin Harian Siswa</h4>
                <p>Semua siswa hanya mendapatkan maksimal 1x poin absensi per tanggal.</p>
            </div>
        <?php endif; ?>
        <?php if($duplicateStudentAttendanceDays->isNotEmpty()): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Nama Siswa</th>
                            <th>Tanggal</th>
                            <th>Jumlah Log pada Hari Tersebut</th>
                            <th>Total Poin yang Masuk Hari Itu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($duplicateStudentAttendanceDays as $dup): ?>
                            <tr>
                                <td>#<?= $dup->user_id ?></td>
                                <td><strong><?= htmlspecialchars($dup->name) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($dup->log_date)) ?></td>
                                <td><span class="pill pill-danger"><?= $dup->logs_in_same_day ?>x Log</span></td>
                                <td><span class="pill pill-danger">+<?= $dup->points_in_same_day ?> Poin/hari</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- 4. Top 20 Pengguna Poin Tertinggi SMPS Pembda 2 -->
        <h2>🏆 4. Daftar 20 Pengguna dengan Poin Tertinggi di SMPS Pembda 2</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Nama Pengguna</th>
                        <th>Peran (Role)</th>
                        <th>Pangkat / Level</th>
                        <th>Total Poin Reputasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($topUsers->isEmpty()): ?>
                        <tr><td colspan="5" style="text-align:center; color:#64748b; padding:20px;">Belum ada data reputasi di SMPS Pembda 2.</td></tr>
                    <?php else: ?>
                        <?php $rank = 1; foreach($topUsers as $usr): ?>
                            <tr>
                                <td style="font-weight:900; color:#94a3b8;">#<?= $rank++ ?></td>
                                <td><strong><?= htmlspecialchars($usr->name) ?></strong></td>
                                <td>
                                    <span class="pill <?= $usr->role === 'guru' || $usr->role === 'karyawan' ? 'pill-role-guru' : 'pill-role-siswa' ?>">
                                        <?= strtoupper($usr->role ?? 'USER') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:<?= $usr->total_points >= 5000 ? '#34d399' : ($usr->total_points >= 2000 ? '#fbbf24' : '#818cf8') ?>">
                                        <?= htmlspecialchars($usr->level_name) ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="pill pill-points" style="font-size:0.88rem;">
                                        <?= number_format($usr->total_points) ?> Poin
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 5. Log Terakhir -->
        <h2>⏱️ 5. Sampel 20 Log Poin Absensi Terakhir</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Kategori</th>
                        <th>Deskripsi Aktivitas</th>
                        <th>Poin</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($recentAttendanceLogs->isEmpty()): ?>
                        <tr><td colspan="7" style="text-align:center; color:#64748b; padding:20px;">Belum ada riwayat absensi.</td></tr>
                    <?php else: ?>
                        <?php foreach($recentAttendanceLogs as $rlog): ?>
                            <tr>
                                <td style="color:#64748b;">#<?= $rlog->id ?></td>
                                <td><strong><?= htmlspecialchars($rlog->user_name) ?></strong></td>
                                <td><span class="pill <?= $rlog->user_role === 'guru' ? 'pill-role-guru' : 'pill-role-siswa' ?>"><?= strtoupper($rlog->user_role) ?></span></td>
                                <td style="font-size:0.75rem; color:#38bdf8; font-weight:700;"><?= $rlog->category ?></td>
                                <td><?= htmlspecialchars($rlog->description) ?></td>
                                <td>
                                    <strong style="color:<?= $rlog->points > 0 ? '#34d399' : '#f87171' ?>">
                                        <?= $rlog->points > 0 ? '+' . $rlog->points : $rlog->points ?>
                                    </strong>
                                </td>
                                <td style="font-size:0.75rem; color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($rlog->created_at)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:30px; padding-top:16px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b;">
            <span>PembdaHUB Reputation & Attendance Diagnostics</span>
            <span>SMPS Pembda 2 Gunungsitoli</span>
        </div>
    </div>
</div>

</body>
</html>
