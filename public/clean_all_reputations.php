<?php
/**
 * Master Data Cleansing Tool: Reputation Points & Spam Log Normalizer
 * Akses Preview: https://perguruanpembda.com/clean_all_reputations.php?secret=pembda99
 * Akses Eksekusi: https://perguruanpembda.com/clean_all_reputations.php?action=clean&secret=pembda99
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<h1>403 Forbidden</h1><p>Akses ditolak. Parameter secret salah atau tidak disertakan.</p>');
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\Reputation;
use App\Models\ReputationLog;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');

$isExecute = isset($_GET['action']) && $_GET['action'] === 'clean';
$isDryRun = !$isExecute;

$affectedUsers = [];
$totalPointsRevoked = 0;
$totalLogsDeleted = 0;
$totalLogsCapped = 0;

// Fetch all reputation records
$allReputations = Reputation::with('user')->get();

DB::beginTransaction();
try {
    foreach ($allReputations as $rep) {
        $user = $rep->user;
        if (!$user) continue;

        $userRevoked = 0;
        $userLogsDeleted = 0;
        $userLogsCapped = 0;
        $anomaliesList = [];

        // -------------------------------------------------------------
        // 1. Audit & Clean LMS Material Duplicate Farming
        // -------------------------------------------------------------
        $materialLogs = ReputationLog::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('category', 'like', '%material%')
                  ->orWhere('category', 'like', '%lms%')
                  ->orWhere('description', 'like', '%Materi%')
                  ->orWhere('description', 'like', '%materi%');
            })
            ->orderBy('id')
            ->get();

        $seenMaterials = [];
        foreach ($materialLogs as $mlog) {
            $descKey = trim(strtolower($mlog->description));
            if (isset($seenMaterials[$descKey])) {
                // Duplicate claim!
                $userRevoked += $mlog->points;
                $userLogsDeleted++;
                if ($isExecute) {
                    $mlog->delete();
                }
            } else {
                $seenMaterials[$descKey] = true;
                // Cap single material points to max 20
                if ($mlog->points > 20) {
                    $diff = $mlog->points - 20;
                    $userRevoked += $diff;
                    $userLogsCapped++;
                    if ($isExecute) {
                        $mlog->update(['points' => 20]);
                    }
                }
            }
        }
        if ($userLogsDeleted > 0) {
            $anomaliesList[] = "Klaim materi LMS duplikat/berulang ({$userLogsDeleted} log dihapus)";
        }

        // -------------------------------------------------------------
        // 2. Audit & Clean LMS Quiz Duplicate Farming
        // -------------------------------------------------------------
        $quizLogs = ReputationLog::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('category', 'like', '%quiz%')
                  ->orWhere('description', 'like', '%Kuis%')
                  ->orWhere('description', 'like', '%kuis%')
                  ->orWhere('description', 'like', '%quiz%');
            })
            ->orderBy('id')
            ->get();

        $seenQuizzes = [];
        $quizLogsDeleted = 0;
        foreach ($quizLogs as $qlog) {
            $descKey = trim(strtolower($qlog->description));
            if (isset($seenQuizzes[$descKey])) {
                // Duplicate quiz claim!
                $userRevoked += $qlog->points;
                $userLogsDeleted++;
                $quizLogsDeleted++;
                if ($isExecute) {
                    $qlog->delete();
                }
            } else {
                $seenQuizzes[$descKey] = true;
                // Cap single quiz points to max 50
                if ($qlog->points > 50) {
                    $diff = $qlog->points - 50;
                    $userRevoked += $diff;
                    $userLogsCapped++;
                    if ($isExecute) {
                        $qlog->update(['points' => 50]);
                    }
                }
            }
        }
        if ($quizLogsDeleted > 0) {
            $anomaliesList[] = "Klaim kuis LMS berulang ({$quizLogsDeleted} log dihapus)";
        }

        // -------------------------------------------------------------
        // 3. Audit & Clean Floods / Identical Spam (>5 identical non-login logs)
        // -------------------------------------------------------------
        $floods = ReputationLog::where('user_id', $user->id)
            ->select('description', 'category', DB::raw('COUNT(*) as cnt'), DB::raw('MIN(id) as keep_id'))
            ->whereNotIn('category', ['daily_login', 'attendance', 'login'])
            ->groupBy('description', 'category')
            ->having('cnt', '>', 5)
            ->get();

        $floodLogsDeleted = 0;
        foreach ($floods as $f) {
            $excessLogs = ReputationLog::where('user_id', $user->id)
                ->where('description', $f->description)
                ->where('category', $f->category)
                ->where('id', '!=', $f->keep_id)
                ->get();

            foreach ($excessLogs as $el) {
                $userRevoked += $el->points;
                $userLogsDeleted++;
                $floodLogsDeleted++;
                if ($isExecute) {
                    $el->delete();
                }
            }
        }
        if ($floodLogsDeleted > 0) {
            $anomaliesList[] = "Flooding aktivitas identik berulang ({$floodLogsDeleted} log dihapus)";
        }

        // -------------------------------------------------------------
        // 4. Recalculate Genuine Points & Update Level
        // -------------------------------------------------------------
        $newCalculatedPoints = (int) ReputationLog::where('user_id', $user->id)->sum('points');
        $newCalculatedPoints = max(0, $newCalculatedPoints);

        if ($isExecute) {
            $rep->total_points = $newCalculatedPoints;
            $rep->updateLevel();
            $rep->save();
        }

        if ($userRevoked > 0 || $rep->total_points != $newCalculatedPoints) {
            $affectedUsers[] = [
                'user' => $user,
                'old_points' => $rep->total_points,
                'new_points' => $newCalculatedPoints,
                'revoked' => $userRevoked,
                'logs_deleted' => $userLogsDeleted,
                'logs_capped' => $userLogsCapped,
                'anomalies' => $anomaliesList,
            ];
            $totalPointsRevoked += $userRevoked;
            $totalLogsDeleted += $userLogsDeleted;
            $totalLogsCapped += $userLogsCapped;
        }
    }

    // -------------------------------------------------------------
    // 5. Recalculate Global Rankings
    // -------------------------------------------------------------
    if ($isExecute) {
        $rankedReps = Reputation::orderByDesc('total_points')->get();
        $rank = 1;
        foreach ($rankedReps as $r) {
            $r->rank_global = $rank++;
            $r->save();
        }
        DB::commit();
    } else {
        DB::rollBack(); // Safe preview, no modifications
    }

} catch (\Exception $e) {
    DB::rollBack();
    die('<div style="padding:20px;background:#fee2e2;color:#991b1b;font-family:sans-serif;"><h3>Terjadi Kesalahan:</h3><p>' . htmlspecialchars($e->getMessage()) . '</p></div>');
}

// Sort affected users by revoked points descending
usort($affectedUsers, function($a, $b) {
    return $b['revoked'] <=> $a['revoked'];
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembersihan & Normalisasi Poin Reputasi - PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8">
    <div class="max-w-6xl mx-auto space-y-8">

        <!-- Top Header Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex items-center justify-between flex-wrap gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-black rounded-lg uppercase tracking-wider mb-2">
                    <span class="w-2 h-2 rounded-full <?= $isExecute ? 'bg-emerald-500' : 'bg-amber-500' ?>"></span>
                    <?= $isExecute ? 'MODE EKSEKUSI PERMANEN' : 'MODE PREVIEW (DRY RUN)' ?>
                </div>
                <h1 class="text-2xl font-black text-slate-900">🧹 Pembersihan & Normalisasi Poin Reputasi</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Membersihkan seluruh riwayat spam, duplikasi klaim materi/kuis berulang, dan menormalkan peringkat siswa ke poin riil yang sah.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="audit_top_points.php?secret=pembda99" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs">
                    📊 Kembali ke Audit
                </a>
                <?php if ($isDryRun): ?>
                    <a href="?action=clean&secret=pembda99" onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin membersihkan seluruh anomali dan menormalkan poin di database sekarang?');" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-2">
                        <span>⚡ EKSEKUSI PEMBERSIHAN SEKARANG</span>
                    </a>
                <?php else: ?>
                    <a href="?secret=pembda99" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        🔄 Muat Ulang
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Akun Terdampak</span>
                <div class="text-3xl font-black text-slate-900 mt-1"><?= number_format(count($affectedUsers), 0, ',', '.') ?> <span class="text-sm font-normal text-slate-400">akun</span></div>
                <span class="text-xs text-slate-500 mt-0.5 block">Akun dengan anomali/farming duplikat</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Total Poin Anomali Ditarik</span>
                <div class="text-3xl font-black text-rose-600 mt-1">-<?= number_format($totalPointsRevoked, 0, ',', '.') ?> <span class="text-sm font-normal text-rose-400">poin</span></div>
                <span class="text-xs text-rose-600/80 mt-0.5 block">Poin tidak sah yang dinormalisasi</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-indigo-200 bg-indigo-50/20 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-500">Log Duplikat/Spam Dihapus</span>
                <div class="text-3xl font-black text-indigo-700 mt-1"><?= number_format($totalLogsDeleted, 0, ',', '.') ?> <span class="text-sm font-normal text-indigo-400">entri</span></div>
                <span class="text-xs text-indigo-600/80 mt-0.5 block"><?= $totalLogsCapped ?> log dikurangi batas wajar</span>
            </div>
        </div>

        <!-- Banner Info -->
        <div class="p-4 rounded-2xl border <?= $isExecute ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' ?> text-xs flex items-center justify-between flex-wrap gap-2 shadow-xs">
            <div class="flex items-center gap-2">
                <span class="text-lg"><?= $isExecute ? '🎉' : 'ℹ️' ?></span>
                <div>
                    <strong><?= $isExecute ? 'Pembersihan Sukses Dijalankan!' : 'Pratinjau Hasil Pembersihan (Dry Run)' ?></strong>
                    <p class="text-[11px] opacity-90"><?= $isExecute ? 'Seluruh database telah diselaraskan. Peringkat siswa kini murni mencerminkan prestasi yang sebenarnya.' : 'Tabel di bawah menampilkan daftar akun yang akan disesuaikan jika tombol eksekusi ditekan.' ?></p>
                </div>
            </div>
        </div>

        <!-- Detail Table of Affected Users -->
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900">📋 Rincian Siswa & Guru yang Dinormalisasi</h2>
                <span class="text-xs text-slate-500"><?= count($affectedUsers) ?> Akun Terdeteksi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Nama Siswa / Guru</th>
                            <th class="py-3 px-4">Username / NIS</th>
                            <th class="py-3 px-4">Indikasi Anomali</th>
                            <th class="py-3 px-4 text-right">Poin Semula</th>
                            <th class="py-3 px-4 text-right">Poin Ditarik</th>
                            <th class="py-3 px-4 text-right">Poin Sah Riil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (count($affectedUsers) === 0): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    ✅ Tidak ditemukan anomali. Seluruh poin di database sudah bersih dan sah!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($affectedUsers as $idx => $item): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-slate-400 font-bold"><?= $idx + 1 ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        <?= htmlspecialchars($item['user']->name) ?>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold uppercase ml-1"><?= htmlspecialchars($item['user']->role) ?></span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600 font-bold"><?= htmlspecialchars($item['user']->username) ?></td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-700 font-medium">
                                            <?php foreach ($item['anomalies'] as $anom): ?>
                                                <li><?= htmlspecialchars($anom) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td class="py-3 px-4 text-right text-slate-500 font-medium"><?= number_format($item['old_points'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-4 text-right font-black text-rose-600">-<?= number_format($item['revoked'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-600 text-sm"><?= number_format($item['new_points'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>
