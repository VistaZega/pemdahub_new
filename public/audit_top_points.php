<?php
/**
 * Diagnostic & Audit Tool: Top Reputation Points Analyzer
 * Akses: https://perguruanpembda.com/audit_top_points.php?secret=pembda99
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

$doFix = isset($_GET['action']) && $_GET['action'] === 'fix_anomalies';
$isDryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';
$executionLog = [];

if ($doFix) {
    // Perform audit and normalization for all users with reputations
    $allReps = Reputation::where('total_points', '>', 300)->with('user')->get();
    
    DB::beginTransaction();
    try {
        $totalDeductedPoints = 0;
        $totalDeletedLogs = 0;

        foreach ($allReps as $rep) {
            $user = $rep->user;
            if (!$user) continue;

            $userDeducted = 0;
            $userLogsDeleted = 0;

            // 1. Check duplicate LMS material logs for the same user and same description/material
            $materialLogs = ReputationLog::where('user_id', $user->id)
                ->where(function($q) {
                    $q->where('category', 'like', '%material%')
                      ->orWhere('category', 'like', '%lms%')
                      ->orWhere('description', 'like', '%Materi%')
                      ->orWhere('description', 'like', '%materi%');
                })
                ->orderBy('id')
                ->get();

            $seenMaterialDescriptions = [];
            foreach ($materialLogs as $mlog) {
                $descKey = trim($mlog->description);
                if (isset($seenMaterialDescriptions[$descKey])) {
                    // Duplicate claim!
                    $userDeducted += $mlog->points;
                    $userLogsDeleted++;
                    if (!$isDryRun) {
                        $mlog->delete();
                    }
                } else {
                    $seenMaterialDescriptions[$descKey] = true;
                    // Cap points to max 20 for material if it was abnormally high
                    if ($mlog->points > 20) {
                        $diff = $mlog->points - 20;
                        $userDeducted += $diff;
                        if (!$isDryRun) {
                            $mlog->update(['points' => 20]);
                        }
                    }
                }
            }

            // 2. Check duplicate LMS quiz logs for the same user and same quiz
            $quizLogs = ReputationLog::where('user_id', $user->id)
                ->where(function($q) {
                    $q->where('category', 'like', '%quiz%')
                      ->orWhere('description', 'like', '%Kuis%')
                      ->orWhere('description', 'like', '%kuis%')
                      ->orWhere('description', 'like', '%quiz%');
                })
                ->orderBy('id')
                ->get();

            $seenQuizDescriptions = [];
            foreach ($quizLogs as $qlog) {
                $descKey = trim($qlog->description);
                if (isset($seenQuizDescriptions[$descKey])) {
                    // Duplicate quiz claim!
                    $userDeducted += $qlog->points;
                    $userLogsDeleted++;
                    if (!$isDryRun) {
                        $qlog->delete();
                    }
                } else {
                    $seenQuizDescriptions[$descKey] = true;
                    // Cap quiz points to max 50
                    if ($qlog->points > 50) {
                        $diff = $qlog->points - 50;
                        $userDeducted += $diff;
                        if (!$isDryRun) {
                            $qlog->update(['points' => 50]);
                        }
                    }
                }
            }

            // 3. Check identical description flood (>5 identical non-material/non-quiz logs)
            $floods = ReputationLog::where('user_id', $user->id)
                ->select('description', 'category', DB::raw('COUNT(*) as cnt'), DB::raw('MIN(id) as keep_id'))
                ->whereNotIn('category', ['daily_login', 'attendance', 'login'])
                ->groupBy('description', 'category')
                ->having('cnt', '>', 5)
                ->get();

            foreach ($floods as $f) {
                $excessLogs = ReputationLog::where('user_id', $user->id)
                    ->where('description', $f->description)
                    ->where('category', $f->category)
                    ->where('id', '!=', $f->keep_id)
                    ->get();

                foreach ($excessLogs as $el) {
                    $userDeducted += $el->points;
                    $userLogsDeleted++;
                    if (!$isDryRun) {
                        $el->delete();
                    }
                }
            }

            // Re-sync Reputation total_points
            if (!$isDryRun) {
                $newTotal = (int) ReputationLog::where('user_id', $user->id)->sum('points');
                $newTotal = max(0, $newTotal);
                
                $rep->total_points = $newTotal;
                $rep->updateLevel();
                $rep->save();
            }

            if ($userDeducted > 0) {
                $executionLog[] = [
                    'name' => $user->name,
                    'username' => $user->username,
                    'deducted' => $userDeducted,
                    'deleted_logs' => $userLogsDeleted,
                    'old_points' => $rep->total_points,
                    'new_points' => max(0, $rep->total_points - $userDeducted)
                ];
                $totalDeductedPoints += $userDeducted;
                $totalDeletedLogs += $userLogsDeleted;
            }
        }

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        die('<div class="p-6 bg-red-100 text-red-800">Error saat normalisasi: ' . $e->getMessage() . '</div>');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Poin Reputasi Tertinggi - PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- Header -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900">🛡️ Audit Poin Reputasi Tertinggi</h1>
                <p class="text-sm text-slate-500 mt-1">Pemeriksaan integritas perolehan poin siswa & guru (LMS Materi, Kuis, Forum Diskusi, & Brick).</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="?secret=pembda99" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    🔄 Refresh Data
                </a>
                <a href="?action=fix_anomalies&dry_run=1&secret=pembda99" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    🔍 Simulasi Perbaikan (Dry Run)
                </a>
                <a href="?action=fix_anomalies&secret=pembda99" onclick="return confirm('Apakah Anda yakin ingin menormalisasi dan menarik seluruh poin anomali/farming berulang?');" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    ⚡ Bersihkan Poin Anomali Sekarang
                </a>
            </div>
        </div>

        <?php if ($doFix): ?>
            <div class="p-6 <?= $isDryRun ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900' ?> border rounded-2xl shadow-sm space-y-3">
                <div class="flex items-center gap-2 font-black text-base">
                    <span><?= $isDryRun ? '🔍 HASIL SIMULASI PERBAIKAN (DRY RUN)' : '✅ NORMALISASI POIN BERHASIL DILAKUKAN' ?></span>
                </div>
                <p class="text-xs">
                    <?= $isDryRun ? 'Berikut adalah estimasi penyesuaian poin jika dieksekusi. Tidak ada data database yang diubah.' : 'Database telah diperbarui secara permanen.' ?>
                </p>
                <div class="text-xs font-bold">
                    Total Poin Anomali: <span class="text-rose-600 font-extrabold">-<?= number_format($totalDeductedPoints, 0, ',', '.') ?> poin</span> | 
                    Total Log Dihapus/Disesuaikan: <?= number_format($totalDeletedLogs, 0, ',', '.') ?> entri
                </div>

                <?php if (count($executionLog) > 0): ?>
                    <div class="mt-3 bg-white/90 rounded-xl p-3 border border-slate-200 text-xs overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                                    <th class="p-2">Nama Siswa / Guru</th>
                                    <th class="p-2">Username</th>
                                    <th class="p-2 text-right">Poin Semula</th>
                                    <th class="p-2 text-right">Poin Anomali Ditarik</th>
                                    <th class="p-2 text-right">Poin Sah Riil</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($executionLog as $elog): ?>
                                    <tr>
                                        <td class="p-2 font-bold text-slate-800"><?= htmlspecialchars($elog['name']) ?></td>
                                        <td class="p-2 font-mono text-slate-600"><?= htmlspecialchars($elog['username']) ?></td>
                                        <td class="p-2 text-right text-slate-600"><?= number_format($elog['old_points'], 0, ',', '.') ?></td>
                                        <td class="p-2 text-right text-rose-600 font-bold">-<?= number_format($elog['deducted'], 0, ',', '.') ?></td>
                                        <td class="p-2 text-right text-emerald-600 font-black"><?= number_format($elog['new_points'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        // 1. Get Top 5 Reputations + Johan Prasettyo Zebua if not in top 5
        $topReputations = Reputation::with('user')->orderByDesc('total_points')->take(5)->get();
        $targetUser = User::where('username', '0134283844')->orWhere('name', 'LIKE', '%JOHAN PRASETTYO%')->first();
        
        $reputationsToAudit = collect($topReputations);
        if ($targetUser) {
            $targetRep = Reputation::where('user_id', $targetUser->id)->first();
            if ($targetRep && !$reputationsToAudit->contains('id', $targetRep->id)) {
                $reputationsToAudit->push($targetRep);
            }
        }
        ?>

        <!-- Top Users Audit Cards -->
        <div class="space-y-6">
            <?php foreach ($reputationsToAudit as $rank => $rep): ?>
                <?php
                $user = $rep->user;
                if (!$user) continue;

                // Fetch logs summary
                $totalLogPoints = ReputationLog::where('user_id', $user->id)->sum('points');
                $logCount = ReputationLog::where('user_id', $user->id)->count();

                // Group by category
                $typeBreakdown = ReputationLog::where('user_id', $user->id)
                    ->select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(points) as total_points'))
                    ->groupBy('category')
                    ->orderByDesc('total_points')
                    ->get();

                // Check identical description floods
                $duplicateDescriptions = ReputationLog::where('user_id', $user->id)
                    ->select('description', 'category', DB::raw('COUNT(*) as duplicate_count'), DB::raw('SUM(points) as flood_points'))
                    ->groupBy('description', 'category')
                    ->having('duplicate_count', '>', 3)
                    ->orderByDesc('flood_points')
                    ->get();

                // Detect anomalies
                $anomalies = [];
                $fraudPoints = 0;

                foreach ($duplicateDescriptions as $dup) {
                    $excessCount = $dup->duplicate_count - 1;
                    $singlePoint = $dup->duplicate_count > 0 ? ($dup->flood_points / $dup->duplicate_count) : 0;
                    $anomPoints = $excessCount * $singlePoint;
                    $fraudPoints += $anomPoints;
                    $anomalies[] = "Aktivitas berulang/farming: '{$dup->description}' tercatat {$dup->duplicate_count}x (menyumbang {$dup->flood_points} poin, anomali: ~" . number_format($anomPoints, 0, ',', '.') . " pts)";
                }

                if ($rep->total_points > 3000 && $logCount > 300) {
                    $anomalies[] = "Total log poin sangat masif ({$logCount} transaksi), mengindikasikan eksploitasi berulang pada materi/kuis yang sama";
                }

                $isAnomalous = count($anomalies) > 0 || $fraudPoints > 100;
                $legitimatePointsEstimate = max(0, $rep->total_points - $fraudPoints);
                ?>

                <div class="bg-white rounded-2xl border <?= $isAnomalous ? 'border-rose-300 ring-2 ring-rose-200' : 'border-slate-200' ?> p-6 shadow-sm space-y-5">
                    <!-- User Header -->
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl <?= $rank === 0 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-600' ?> flex items-center justify-center font-black text-sm">
                                #<?= $rank + 1 ?>
                            </span>
                            <div>
                                <h2 class="text-lg font-black text-slate-900"><?= htmlspecialchars($user->name) ?></h2>
                                <p class="text-xs text-slate-500 font-medium">
                                    Username: <span class="font-mono font-bold text-slate-800"><?= htmlspecialchars($user->username) ?></span> | 
                                    Role: <span class="uppercase font-bold text-slate-700"><?= htmlspecialchars($user->role) ?></span> | 
                                    Level: <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-[10px]"><?= htmlspecialchars($rep->level_name ?? 'Newbie') ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-2xl font-black <?= $isAnomalous ? 'text-rose-600' : 'text-indigo-600' ?>">
                                <?= number_format($rep->total_points, 0, ',', '.') ?> <span class="text-xs text-slate-500 font-normal">Poin</span>
                            </div>
                            <span class="text-[11px] text-slate-400">Total Log: <?= number_format($totalLogPoints, 0, ',', '.') ?> pts (<?= $logCount ?> transaksi)</span>
                        </div>
                    </div>

                    <!-- Evaluation Badge -->
                    <div>
                        <?php if ($isAnomalous): ?>
                            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <span class="text-rose-800 font-black text-xs flex items-center gap-1.5">
                                        ⚠️ STATUS: POIN TIDAK DAPAT DIPERTANGGUNGJAWABKAN (DITEMUKAN INDIKASI FARMING / EXPLOIT)
                                    </span>
                                    <span class="text-xs font-bold text-rose-700 bg-white px-2.5 py-1 rounded-lg border border-rose-200">
                                        Estimasi Poin Sah Riil: <span class="text-emerald-700 font-black"><?= number_format($legitimatePointsEstimate, 0, ',', '.') ?> pts</span>
                                    </span>
                                </div>
                                <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                                    <?php foreach ($anomalies as $anomaly): ?>
                                        <li><?= htmlspecialchars($anomaly) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-800 flex items-center justify-between">
                                <span>✅ STATUS: POIN WAJAR & DAPAT DIPERTANGGUNGJAWABKAN</span>
                                <span class="text-[11px] text-emerald-700">Aktivitas normal & tidak ada indikasi spamming</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Breakdown Table -->
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Rincian Sumber Poin</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <?php foreach ($typeBreakdown as $tb): ?>
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tb->category ?: 'unspecified') ?></span>
                                    <div class="text-base font-black text-slate-800 mt-0.5">
                                        <?= number_format($tb->total_points, 0, ',', '.') ?> <span class="text-[10px] text-slate-400 font-normal">pts</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-medium"><?= $tb->count ?> kali aktivitas</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top 5 Sample Logs -->
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Sampel Log Aktivitas Terakhir</h3>
                        <?php
                        $sampleLogs = ReputationLog::where('user_id', $user->id)->orderBy('id', 'desc')->take(5)->get();
                        ?>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl overflow-hidden text-xs">
                            <table class="w-full text-left">
                                <thead class="bg-slate-100/80 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase">
                                    <tr>
                                        <th class="p-2.5">Waktu</th>
                                        <th class="p-2.5">Kategori</th>
                                        <th class="p-2.5">Keterangan</th>
                                        <th class="p-2.5 text-right">Poin</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/60">
                                    <?php foreach ($sampleLogs as $log): ?>
                                        <tr>
                                            <td class="p-2.5 text-slate-500 font-mono"><?= $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' ?></td>
                                            <td class="p-2.5 font-bold text-slate-700 uppercase"><?= htmlspecialchars($log->category) ?></td>
                                            <td class="p-2.5 text-slate-800"><?= htmlspecialchars($log->description) ?></td>
                                            <td class="p-2.5 text-right font-black <?= $log->points >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                                <?= $log->points > 0 ? '+' : '' ?><?= $log->points ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</body>
</html>
