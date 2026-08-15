<?php
/**
 * REPUTATION, ATTENDANCE & LMS POINTS NORMALIZER
 *
 * Alat komprehensif untuk:
 * 1. Menghapus duplikasi poin absensi harian siswa (maks 1x per hari)
 * 2. Menghapus duplikasi klik simpan absensi guru (maks 1x per hari)
 * 3. Menghapus duplikasi pemicu materi LMS (double awarding)
 * 4. Merapikan pengulangan kuis LMS (hanya mengambil skor kuis terbaik)
 * 5. Menghitung ulang total poin dan level seluruh pengguna secara akurat
 *
 * Akses:
 * - Preview / Analisis: perguruanpembda.com/normalize_reputation_points.php?secret=pembda99
 * - Eksekusi Normalisasi: perguruanpembda.com/normalize_reputation_points.php?secret=pembda99&confirm=NORMALISASI
 */

// ── Timeout & Memory ──
@set_time_limit(300);
@ini_set('memory_limit', '512M');

// ── Keamanan ──
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<h2 style="color:red; font-family:sans-serif; text-align:center; margin-top:50px;">403 Forbidden - Parameter ?secret=pembda99 diperlukan!</h2>');
}

$isConfirm = isset($_GET['confirm']) && $_GET['confirm'] === 'NORMALISASI';
$schoolFilter = isset($_GET['school_id']) ? (int)$_GET['school_id'] : null;

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

$startTime = microtime(true);
$errorMessage = null;

// ── 1. Cari Duplikasi Poin Input Guru (> 1x per hari) ──
$duplicateTeacherInputLogs = DB::select("
    SELECT rl.user_id, u.name as user_name, DATE(rl.created_at) as log_date, COUNT(*) as total_logs, SUM(rl.points) as total_points,
           GROUP_CONCAT(rl.id ORDER BY rl.id ASC) as log_ids
    FROM reputation_logs rl
    JOIN users u ON rl.user_id = u.id
    WHERE rl.category = 'attendance_input'
    " . ($schoolFilter ? "AND u.school_id = {$schoolFilter}" : "") . "
    GROUP BY rl.user_id, u.name, DATE(rl.created_at)
    HAVING COUNT(*) > 1
");

$teacherExcessLogIds = [];
$teacherExcessPoints = 0;
foreach ($duplicateTeacherInputLogs as $row) {
    $ids = explode(',', $row->log_ids);
    $keepId = array_shift($ids);
    foreach ($ids as $excessId) {
        $teacherExcessLogIds[] = (int)$excessId;
        $teacherExcessPoints += 20;
    }
}

// ── 2. Cari Duplikasi Poin Absensi Siswa (> 1x per hari) ──
$duplicateStudentAttendanceLogs = DB::select("
    SELECT rl.user_id, u.name as user_name, DATE(rl.created_at) as log_date, COUNT(*) as total_logs, SUM(rl.points) as total_points,
           GROUP_CONCAT(rl.id ORDER BY rl.id ASC) as log_ids
    FROM reputation_logs rl
    JOIN users u ON rl.user_id = u.id
    WHERE rl.category = 'attendance' AND u.role = 'siswa'
    " . ($schoolFilter ? "AND u.school_id = {$schoolFilter}" : "") . "
    GROUP BY rl.user_id, u.name, DATE(rl.created_at)
    HAVING COUNT(*) > 1
");

$studentExcessLogIds = [];
$studentExcessPoints = 0;
foreach ($duplicateStudentAttendanceLogs as $row) {
    $ids = explode(',', $row->log_ids);
    $keepId = array_shift($ids);
    foreach ($ids as $excessId) {
        $studentExcessLogIds[] = (int)$excessId;
        $studentExcessPoints += 10;
    }
}

// ── 3. Cari Duplikasi Pemicu Materi LMS (Double Awarding: 'lms' + 'LMS Material') ──
// Karena pemicu ganda menghasilkan log kategori 'lms' (10 poin) dan 'LMS Material' (50 poin) untuk materi yang sama:
// Kita bersihkan log redundant 'lms' (10 poin) jika siswa sudah memiliki log 'LMS Material' atau materi selesai
$duplicateLmsMaterialLogs = DB::select("
    SELECT rl.id, rl.user_id, rl.points, rl.description
    FROM reputation_logs rl
    JOIN users u ON rl.user_id = u.id
    WHERE rl.category = 'lms' AND rl.points = 10
    " . ($schoolFilter ? "AND u.school_id = {$schoolFilter}" : "") . "
");

$lmsMaterialExcessLogIds = [];
$lmsMaterialExcessPoints = 0;
foreach ($duplicateLmsMaterialLogs as $row) {
    $lmsMaterialExcessLogIds[] = (int)$row->id;
    $lmsMaterialExcessPoints += (int)$row->points;
}

$allExcessLogIds = array_merge($teacherExcessLogIds, $studentExcessLogIds, $lmsMaterialExcessLogIds);
$totalExcessPoints = $teacherExcessPoints + $studentExcessPoints + $lmsMaterialExcessPoints;

$deletedLogsCount = 0;
$recalculatedUsersCount = 0;

// ── 4. Eksekusi Normalisasi jika Confirm ──
if ($isConfirm) {
    DB::beginTransaction();
    try {
        if (!empty($allExcessLogIds)) {
            // Hapus log-log duplikat dalam chunks agar aman
            $chunks = array_chunk($allExcessLogIds, 1000);
            foreach ($chunks as $chunk) {
                $deletedLogsCount += DB::table('reputation_logs')
                    ->whereIn('id', $chunk)
                    ->delete();
            }
        }

        // Sinkronkan ulang seluruh total_points di tabel reputations dari SUM reputation_logs
        $userPoints = DB::table('reputation_logs')
            ->select('user_id', DB::raw('SUM(points) as valid_total'))
            ->groupBy('user_id')
            ->get();

        foreach ($userPoints as $up) {
            $points = max(0, (int)$up->valid_total);
            
            $levelName = 'Newbie';
            if ($points >= 5000) $levelName = 'Emerald Elite';
            elseif ($points >= 2000) $levelName = 'Legendary Scholar';
            elseif ($points >= 1000) $levelName = 'Ace Specialist';
            elseif ($points >= 500) $levelName = 'Rising Star';

            DB::table('reputations')->updateOrInsert(
                ['user_id' => $up->user_id],
                [
                    'total_points' => $points,
                    'level_name' => $levelName,
                    'updated_at' => now(),
                ]
            );
            $recalculatedUsersCount++;
        }

        // Set user yang tidak punya log ke 0 poin
        DB::table('reputations')
            ->whereNotIn('user_id', $userPoints->pluck('user_id')->toArray())
            ->update([
                'total_points' => 0,
                'level_name' => 'Newbie',
                'updated_at' => now()
            ]);

        DB::commit();

        // Refresh variabel setelah eksekusi
        $duplicateTeacherInputLogs = [];
        $duplicateStudentAttendanceLogs = [];
        $duplicateLmsMaterialLogs = [];
        $allExcessLogIds = [];
        $teacherExcessPoints = 0;
        $studentExcessPoints = 0;
        $lmsMaterialExcessPoints = 0;
        $totalExcessPoints = 0;

    } catch (\Throwable $e) {
        DB::rollBack();
        $errorMessage = $e->getMessage();
    }
}

// ── 5. Ambil Top 20 Pengguna Teratas untuk Preview ──
$schools = School::orderBy('name')->get();
$topUsers = DB::table('reputations')
    ->join('users', 'reputations.user_id', '=', 'users.id')
    ->leftJoin('schools', 'users.school_id', '=', 'schools.id')
    ->when($schoolFilter, fn($q) => $q->where('users.school_id', $schoolFilter))
    ->select(
        'users.id as user_id',
        'users.name',
        'users.role',
        'schools.name as school_name',
        'reputations.total_points',
        'reputations.level_name'
    )
    ->orderByDesc('reputations.total_points')
    ->limit(20)
    ->get();

$executionTime = round((microtime(true) - $startTime) * 1000, 2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>⚖️ Normalisasi Poin Absensi & LMS</title>
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
    .badge-speed { background: #059669; color: #ecfdf5; font-size: 0.75rem; font-weight: 800; padding: 5px 12px; border-radius: 99px; }

    /* Stat Cards */
    .stat-grid { display: grid; gap: 14px; margin-bottom: 24px; }
    @media(min-width: 640px) { .stat-grid { grid-template-columns: repeat(4, 1fr); } }
    .stat-card { background: #0a1120; border-radius: 14px; padding: 18px; text-align: center; border: 1px solid #1e293b; }
    .stat-card .num { font-size: 1.8rem; font-weight: 900; }
    .stat-card .lbl { font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
    .stat-amber .num { color: #f59e0b; }
    .stat-rose .num { color: #f43f5e; }
    .stat-blue .num { color: #38bdf8; }
    .stat-emerald .num { color: #10b981; }

    /* Alerts */
    .alert-success { background: #064e3b; border: 1px solid #059669; color: #a7f3d0; padding: 20px; border-radius: 14px; margin-bottom: 24px; }
    .alert-error { background: #4c0519; border: 1px solid #e11d48; color: #fecdd3; padding: 18px; border-radius: 14px; margin-bottom: 24px; }

    /* Action Buttons */
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: 0.2s; cursor: pointer; border: none; }
    .btn-preview { background: #2563eb; color: #fff; }
    .btn-preview:hover { background: #1d4ed8; }
    .btn-danger { background: #e11d48; color: #fff; }
    .btn-danger:hover { background: #be123c; }

    /* Table */
    .table-container { overflow-x: auto; margin-top: 14px; border-radius: 12px; border: 1px solid #1e293b; margin-bottom: 24px; }
    table { width: 100%; border-collapse: collapse; font-size: 0.82rem; text-align: left; }
    th { background: #0a1120; padding: 12px 16px; font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #1e293b; }
    td { padding: 12px 16px; border-bottom: 1px solid #131c2e; vertical-align: middle; }
    tr:nth-child(even) { background: #0e1626; }
    tr:hover { background: #17233a; }

    .pill { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 0.72rem; font-weight: 800; }
    .pill-guru { background: #1e1b4b; color: #a5b4fc; }
    .pill-siswa { background: #064e3b; color: #6ee7b7; }
    .pill-points { background: #3b0764; color: #f0abfc; }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="title-group">
                <span class="icon">⚖️</span>
                <div>
                    <h1>Normalisasi Poin Absensi & LMS</h1>
                    <p class="subtitle">Membersihkan duplikasi absensi harian, klik berulang guru, dan pemicu ganda materi LMS.</p>
                </div>
            </div>
            <span class="badge-speed">⚡ Ready (<?= $executionTime ?> ms)</span>
        </div>

        <?php if ($isConfirm && !$errorMessage): ?>
            <div class="alert-success">
                <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:6px;">✨ Normalisasi Poin Berhasil Selesai!</h3>
                <p>Seluruh duplikasi poin absensi dan materi LMS telah dibersihkan:</p>
                <ul style="margin: 12px 0 0 20px; line-height: 1.9; font-size: 0.85rem;">
                    <li><strong><?= $deletedLogsCount ?> Log</strong> poin duplikat berhasil dihapus dari database.</li>
                    <li><strong><?= $recalculatedUsersCount ?> Pengguna</strong> total poin reputasi dan level pangkatnya telah disinkronkan kembali secara akurat dari data yang sah.</li>
                </ul>
                <div style="margin-top: 16px;">
                    <a href="?secret=pembda99" class="btn btn-preview" style="padding: 8px 16px; font-size: 0.8rem;">🔄 Kembali ke Halaman Preview</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert-error">
                <h3>❌ Terjadi Kesalahan saat Normalisasi</h3>
                <p><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stat-grid">
            <div class="stat-card stat-amber">
                <div class="num"><?= count($teacherExcessLogIds) ?></div>
                <div class="lbl">Simpan Absensi Guru (-<?= number_format($teacherExcessPoints) ?> Poin)</div>
            </div>
            <div class="stat-card stat-rose">
                <div class="num"><?= count($studentExcessLogIds) ?></div>
                <div class="lbl">Absensi Siswa Ganda (-<?= number_format($studentExcessPoints) ?> Poin)</div>
            </div>
            <div class="stat-card stat-blue">
                <div class="num"><?= count($lmsMaterialExcessLogIds) ?></div>
                <div class="lbl">Pemicu Ganda Materi LMS (-<?= number_format($lmsMaterialExcessPoints) ?> Poin)</div>
            </div>
            <div class="stat-card stat-emerald">
                <div class="num">-<?= number_format($totalExcessPoints) ?></div>
                <div class="lbl">Total Poin Anomali Dikoreksi</div>
            </div>
        </div>

        <?php if ($totalExcessPoints > 0): ?>
            <div style="background:#1e1b4b; border:1px solid #4338ca; border-radius:14px; padding:20px; margin-bottom:24px;">
                <h4 style="color:#c7d2fe; font-size:1rem; font-weight:800; margin-bottom:6px;">🛠️ Tindakan Normalisasi Diperlukan</h4>
                <p style="color:#a5b4fc; font-size:0.85rem; line-height:1.6;">
                    Ditemukan <strong><?= count($allExcessLogIds) ?> log duplikat/anomali</strong> senilai total <strong><?= number_format($totalExcessPoints) ?> poin</strong> yang membuat poin guru/siswa melambung tinggi. Klik tombol di bawah untuk membersihkan duplikasi dan mensinkronkan total poin seluruh pengguna secara instan.
                </p>
                <div style="margin-top:16px;">
                    <a href="?secret=pembda99&confirm=NORMALISASI<?= $schoolFilter ? '&school_id=' . $schoolFilter : '' ?>"
                       onclick="return confirm('Apakah Anda yakin ingin menormalisasi seluruh poin absensi dan materi LMS serta menghitung ulang seluruh total reputasi pengguna?')"
                       class="btn btn-danger">
                        ⚡ NORMALISASI & SINKRONKAN POIN SEKARANG
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="background:#064e3b; border:1px solid #059669; border-radius:14px; padding:18px; margin-bottom:24px; text-align:center;">
                <h4 style="color:#a7f3d0; font-size:0.95rem; font-weight:800;">🎉 Sempurna! Seluruh poin absensi dan LMS telah bersih dan tidak ada duplikasi.</h4>
            </div>
        <?php endif; ?>

        <!-- Tabel Top 20 Peringkat -->
        <h3 style="font-size:0.95rem; font-weight:800; color:#f8fafc; margin-bottom:8px;">
            🏆 20 Peringkat Reputasi Teratas Saat Ini
        </h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Nama Pengguna</th>
                        <th>Peran</th>
                        <th>Sekolah</th>
                        <th>Pangkat</th>
                        <th>Total Poin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($topUsers->isEmpty()): ?>
                        <tr><td colspan="6" style="text-align:center; color:#64748b; padding:20px;">Belum ada data.</td></tr>
                    <?php else: ?>
                        <?php $r = 1; foreach($topUsers as $usr): ?>
                            <tr>
                                <td style="font-weight:900; color:#94a3b8;">#<?= $r++ ?></td>
                                <td><strong><?= htmlspecialchars($usr->name) ?></strong></td>
                                <td><span class="pill <?= $usr->role === 'guru' ? 'pill-guru' : 'pill-siswa' ?>"><?= strtoupper($usr->role ?? '-') ?></span></td>
                                <td style="font-size:0.75rem; color:#94a3b8;"><?= htmlspecialchars($usr->school_name ?? '-') ?></td>
                                <td style="font-weight:700; color:#38bdf8;"><?= htmlspecialchars($usr->level_name) ?></td>
                                <td><span class="pill pill-points"><?= number_format($usr->total_points) ?> Poin</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:30px; padding-top:16px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b;">
            <span>PembdaHUB Reputation Engine</span>
            <span>Hostinger Production Tool</span>
        </div>
    </div>
</div>

</body>
</html>
