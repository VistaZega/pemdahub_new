<?php
/**
 * CLEANUP SCRIPT (HIGH SPEED BATCH ENGINE)
 * Pembersihan Postingan & Komentar Spam / Terlalu Singkat di Pembda Space
 * Serta Pembatalan & Sinkronisasi Poin Reputasi Pengguna
 *
 * Akses:
 * - Preview / Diagnostik: perguruanpembda.com/cleanup_space_spam.php?secret=pembda99
 * - Eksekusi Hapus Cepat: perguruanpembda.com/cleanup_space_spam.php?secret=pembda99&min_chars=15&confirm=HAPUS
 */

// ── Timeout & Memory Optimization ──
@set_time_limit(300);
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');

// ── Keamanan ──
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<h2 style="color:red; font-family:sans-serif; text-align:center; margin-top:50px;">403 Forbidden - Parameter ?secret=pembda99 diperlukan!</h2>');
}

$minChars   = isset($_GET['min_chars']) ? max(1, (int)$_GET['min_chars']) : 15;
$scope      = $_GET['scope'] ?? 'all'; // 'all', 'threads', 'replies'
$isConfirm  = isset($_GET['confirm']) && $_GET['confirm'] === 'HAPUS';
$replyMinChars = min($minChars, 10);

// ── Bootstrap Laravel ──
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\AlumniForum;

$startTime = microtime(true);
$errorMessage = null;

// ── 1. Eksekusi Hapus Batch Cepat jika Confirm ──
$deletedThreadsCount = 0;
$deletedRepliesCount = 0;
$deletedAlumniCount = 0;
$revokedPointsCount = 0;
$recalculatedUsersCount = 0;

if ($isConfirm) {
    DB::beginTransaction();
    try {
        $affectedUserIds = [];

        // 1. Ambil IDs Thread Spam
        $spamThreadIds = [];
        if ($scope === 'all' || $scope === 'threads') {
            $spamThreadIds = DB::table('forum_threads')
                ->whereRaw('CHAR_LENGTH(TRIM(content)) < ? OR CHAR_LENGTH(TRIM(title)) < ?', [$minChars, $minChars])
                ->pluck('id')
                ->toArray();

            if (!empty($spamThreadIds)) {
                $threadUserIds = DB::table('forum_threads')->whereIn('id', $spamThreadIds)->pluck('user_id')->toArray();
                $affectedUserIds = array_merge($affectedUserIds, $threadUserIds);

                // Tarik & hapus reputation logs
                $pointSum = DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumThread::class, 'App\Models\ForumThread'])
                    ->whereIn('reference_id', $spamThreadIds)
                    ->sum('points');
                $revokedPointsCount += (int)$pointSum;

                DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumThread::class, 'App\Models\ForumThread'])
                    ->whereIn('reference_id', $spamThreadIds)
                    ->delete();

                // Hapus balasan & relasinya
                $threadReplyIds = DB::table('forum_replies')->whereIn('forum_thread_id', $spamThreadIds)->pluck('id')->toArray();
                if (!empty($threadReplyReply)) {
                    DB::table('forum_reactions')->whereIn('forum_reply_id', $threadReplyIds)->delete();
                    DB::table('reputation_logs')
                        ->whereIn('reference_type', [ForumReply::class, 'App\Models\ForumReply'])
                        ->whereIn('reference_id', $threadReplyIds)
                        ->delete();
                }
                DB::table('forum_replies')->whereIn('forum_thread_id', $spamThreadIds)->delete();

                // Hapus likes, reactions, members
                DB::table('forum_likes')->whereIn('forum_thread_id', $spamThreadIds)->delete();
                DB::table('forum_reactions')->whereIn('forum_thread_id', $spamThreadIds)->delete();
                if (DB::getSchemaBuilder()->hasTable('forum_members')) {
                    DB::table('forum_members')->whereIn('forum_thread_id', $spamThreadIds)->delete();
                }

                // Hapus polls
                $pollIds = DB::table('forum_polls')->whereIn('forum_thread_id', $spamThreadIds)->pluck('id')->toArray();
                if (!empty($pollIds)) {
                    DB::table('forum_poll_votes')->whereIn('forum_poll_id', $pollIds)->delete();
                    DB::table('forum_poll_options')->whereIn('forum_poll_id', $pollIds)->delete();
                    DB::table('forum_polls')->whereIn('id', $pollIds)->delete();
                }

                // Hapus threads
                $deletedThreadsCount = DB::table('forum_threads')->whereIn('id', $spamThreadIds)->delete();
            }
        }

        // 2. Ambil IDs Balasan Spam
        if ($scope === 'all' || $scope === 'replies') {
            $spamReplyIds = DB::table('forum_replies')
                ->whereNull('voice_note_path')
                ->whereRaw('CHAR_LENGTH(TRIM(content)) < ?', [$replyMinChars])
                ->pluck('id')
                ->toArray();

            if (!empty($spamReplyIds)) {
                $replyUserIds = DB::table('forum_replies')->whereIn('id', $spamReplyIds)->pluck('user_id')->toArray();
                $affectedUserIds = array_merge($affectedUserIds, $replyUserIds);

                $rPointSum = DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumReply::class, 'App\Models\ForumReply'])
                    ->whereIn('reference_id', $spamReplyIds)
                    ->sum('points');
                $revokedPointsCount += (int)$rPointSum;

                DB::table('forum_reactions')->whereIn('forum_reply_id', $spamReplyIds)->delete();
                DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumReply::class, 'App\Models\ForumReply'])
                    ->whereIn('reference_id', $spamReplyIds)
                    ->delete();

                $deletedRepliesCount = DB::table('forum_replies')->whereIn('id', $spamReplyIds)->delete();
            }
        }

        // 3. Hapus Alumni Forum Spam
        if ($scope === 'all' || $scope === 'threads') {
            if (DB::getSchemaBuilder()->hasTable('alumni_forums')) {
                $alumniIds = DB::table('alumni_forums')
                    ->whereRaw('CHAR_LENGTH(TRIM(content)) < ? OR CHAR_LENGTH(TRIM(title)) < ?', [$minChars, $minChars])
                    ->pluck('id')
                    ->toArray();

                if (!empty($alumniIds)) {
                    if (DB::getSchemaBuilder()->hasTable('alumni_forum_replies')) {
                        DB::table('alumni_forum_replies')->whereIn('alumni_forum_id', $alumniIds)->delete();
                    }
                    $deletedAlumniCount = DB::table('alumni_forums')->whereIn('id', $alumniIds)->delete();
                }
            }
        }

        // 4. Sinkronisasi Ulang Total Poin Reputasi Seluruh User Terdampak
        $uniqueUserIds = array_unique(array_filter($affectedUserIds));
        foreach ($uniqueUserIds as $uId) {
            $sumPoints = DB::table('reputation_logs')->where('user_id', $uId)->sum('points');
            $validPoints = max(0, (int)$sumPoints);

            $levelName = 'Newbie';
            if ($validPoints >= 5000) $levelName = 'Emerald Elite';
            elseif ($validPoints >= 2000) $levelName = 'Legendary Scholar';
            elseif ($validPoints >= 1000) $levelName = 'Ace Specialist';
            elseif ($validPoints >= 500) $levelName = 'Rising Star';

            DB::table('reputations')->updateOrInsert(
                ['user_id' => $uId],
                [
                    'total_points' => $validPoints,
                    'level_name' => $levelName,
                    'updated_at' => now(),
                ]
            );
            $recalculatedUsersCount++;
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        $errorMessage = $e->getMessage();
    }
}

// ── 2. Query Data Statistik (Ringan & Cepat) ──
$totalThreadsInDb = DB::table('forum_threads')->count();
$totalRepliesInDb = DB::table('forum_replies')->count();

$spamThreadsCount = DB::table('forum_threads')
    ->whereRaw('CHAR_LENGTH(TRIM(content)) < ? OR CHAR_LENGTH(TRIM(title)) < ?', [$minChars, $minChars])
    ->count();

$spamRepliesCount = DB::table('forum_replies')
    ->whereNull('voice_note_path')
    ->whereRaw('CHAR_LENGTH(TRIM(content)) < ?', [$replyMinChars])
    ->count();

// Estimasi Poin yang Akan Ditarik
$estimatedPoints = ($spamThreadsCount * 15) + ($spamRepliesCount * 5);

// Ambil sampel data untuk tabel preview (maksimal 50 teratas agar loading cepat)
$sampleThreads = DB::table('forum_threads')
    ->leftJoin('users', 'forum_threads.user_id', '=', 'users.id')
    ->leftJoin('forum_groups', 'forum_threads.group_id', '=', 'forum_groups.id')
    ->whereRaw('CHAR_LENGTH(TRIM(forum_threads.content)) < ? OR CHAR_LENGTH(TRIM(forum_threads.title)) < ?', [$minChars, $minChars])
    ->select(
        'forum_threads.id',
        'forum_threads.title',
        'forum_threads.content',
        'forum_threads.created_at',
        'forum_threads.category',
        'users.name as author_name',
        'users.role as author_role',
        'forum_groups.name as group_name'
    )
    ->latest('forum_threads.created_at')
    ->limit(50)
    ->get();

$sampleReplies = DB::table('forum_replies')
    ->leftJoin('users', 'forum_replies.user_id', '=', 'users.id')
    ->leftJoin('forum_threads', 'forum_replies.forum_thread_id', '=', 'forum_threads.id')
    ->whereNull('forum_replies.voice_note_path')
    ->whereRaw('CHAR_LENGTH(TRIM(forum_replies.content)) < ?', [$replyMinChars])
    ->select(
        'forum_replies.id',
        'forum_replies.content',
        'forum_replies.created_at',
        'forum_replies.forum_thread_id',
        'users.name as author_name',
        'users.role as author_role',
        'forum_threads.title as thread_title'
    )
    ->latest('forum_replies.created_at')
    ->limit(50)
    ->get();

$executionTime = round((microtime(true) - $startTime) * 1000, 2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>⚡ Pembersih Cepat Spam Pembda Space</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    body { background: #0b0f19; color: #e2e8f0; min-height: 100vh; padding: 30px 15px; }
    .container { max-width: 1050px; margin: 0 auto; }
    .card { background: #131c2e; border-radius: 20px; padding: 28px; border: 1px solid #1e293b; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b; padding-bottom: 20px; margin-bottom: 24px; }
    .title-group { display: flex; align-items: center; gap: 14px; }
    .icon { font-size: 2.2rem; }
    h1 { font-size: 1.4rem; font-weight: 800; color: #f8fafc; }
    .subtitle { color: #94a3b8; font-size: 0.8rem; margin-top: 4px; }
    .badge-speed { background: #0284c7; color: #e0f2fe; font-size: 0.72rem; font-weight: 800; padding: 4px 10px; border-radius: 99px; }

    /* Filter Controls */
    .filter-bar { background: #0a1120; border-radius: 14px; padding: 16px; margin-bottom: 24px; border: 1px solid #1e293b; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .filter-group { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }
    .filter-group select { background: #1e293b; color: #fff; border: 1px solid #334155; padding: 8px 14px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; }

    /* Stat Cards */
    .stat-grid { display: grid; gap: 14px; margin-bottom: 24px; }
    @media(min-width: 640px) { .stat-grid { grid-template-columns: repeat(4, 1fr); } }
    .stat-card { background: #0a1120; border-radius: 14px; padding: 18px; text-align: center; border: 1px solid #1e293b; }
    .stat-card .num { font-size: 2rem; font-weight: 900; }
    .stat-card .lbl { font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
    .stat-rose .num { color: #f43f5e; }
    .stat-amber .num { color: #f59e0b; }
    .stat-purple .num { color: #c084fc; }
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
    .table-container { overflow-x: auto; margin-top: 14px; border-radius: 12px; border: 1px solid #1e293b; }
    table { width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: left; }
    th { background: #0a1120; padding: 12px 16px; font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #1e293b; }
    td { padding: 12px 16px; border-bottom: 1px solid #131c2e; vertical-align: middle; }
    tr:nth-child(even) { background: #0e1626; }
    tr:hover { background: #17233a; }
    .char-pill { display: inline-block; background: #881337; color: #fda4af; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 99px; }
    .points-pill { display: inline-block; background: #581c87; color: #e9d5ff; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 99px; }
    .spam-highlight { background: #ff005520; color: #ff6b8b; font-weight: bold; padding: 2px 6px; border-radius: 6px; border: 1px dashed #ff005550; display: inline-block; }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="title-group">
                <span class="icon">🧹</span>
                <div>
                    <h1>Pembersih Cepat Spam & Pembatalan Poin</h1>
                    <p class="subtitle">Mesin batch berkecepatan tinggi: membersihkan postingan 1 karakter dan membatalkan poin terkait.</p>
                </div>
            </div>
            <span class="badge-speed">⚡ Batch Engine (<?= $executionTime ?> ms)</span>
        </div>

        <?php if ($isConfirm && !$errorMessage): ?>
            <div class="alert-success">
                <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:6px;">✨ Pembersihan & Penarikan Poin Berhasil Selesai!</h3>
                <p>Seluruh postingan spam telah dihapus dan poin reputasi pengguna telah disinkronkan kembali secara instan:</p>
                <ul style="margin: 12px 0 0 20px; line-height: 1.9; font-size: 0.85rem;">
                    <li><strong><?= $deletedThreadsCount ?></strong> Postingan Utama (Threads) terhapus.</li>
                    <li><strong><?= $deletedRepliesCount ?></strong> Balasan / Komentar Spam terhapus.</li>
                    <li><strong><?= $deletedAlumniCount ?></strong> Postingan Forum Alumni terhapus.</li>
                    <li><strong style="color:#fef08a;">-<?= $revokedPointsCount ?> Poin</strong> Reputasi berhasil ditarik & dibatalkan.</li>
                    <li><strong><?= $recalculatedUsersCount ?> User</strong> total poin & level pangkatnya telah dihitung ulang secara akurat.</li>
                </ul>
                <div style="margin-top: 16px;">
                    <a href="?secret=pembda99" class="btn btn-preview" style="padding: 8px 16px; font-size: 0.8rem;">🔄 Selesai & Kembali ke Preview</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert-error">
                <h3>❌ Terjadi Kesalahan saat Pembersihan</h3>
                <p><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>

        <!-- Filter Bar -->
        <form method="GET" class="filter-bar">
            <input type="hidden" name="secret" value="pembda99">
            
            <div class="filter-group">
                <label>Batas Karakter Minimum:</label>
                <select name="min_chars" onchange="this.form.submit()">
                    <option value="5" <?= $minChars == 5 ? 'selected' : '' ?>>Kurang dari 5 Karakter</option>
                    <option value="10" <?= $minChars == 10 ? 'selected' : '' ?>>Kurang dari 10 Karakter</option>
                    <option value="15" <?= $minChars == 15 ? 'selected' : '' ?>>Kurang dari 15 Karakter (Standar)</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Cakupan:</label>
                <select name="scope" onchange="this.form.submit()">
                    <option value="all" <?= $scope == 'all' ? 'selected' : '' ?>>Semua (Postingan & Balasan)</option>
                    <option value="threads" <?= $scope == 'threads' ? 'selected' : '' ?>>Postingan Utama Saja</option>
                    <option value="replies" <?= $scope == 'replies' ? 'selected' : '' ?>>Balasan / Komentar Saja</option>
                </select>
            </div>

            <button type="submit" class="btn btn-preview" style="padding: 8px 16px; font-size: 0.8rem;">
                🔍 Refresh Analisis
            </button>
        </form>

        <!-- Stats Grid -->
        <div class="stat-grid">
            <div class="stat-card stat-rose">
                <div class="num"><?= $spamThreadsCount ?></div>
                <div class="lbl">Postingan Spam (&lt; <?= $minChars ?> Karakter)</div>
            </div>
            <div class="stat-card stat-amber">
                <div class="num"><?= $spamRepliesCount ?></div>
                <div class="lbl">Komentar Spam (&lt; <?= $replyMinChars ?> Karakter)</div>
            </div>
            <div class="stat-card stat-purple">
                <div class="num">-<?= $estimatedPoints ?></div>
                <div class="lbl">Estimasi Poin yang Ditarik</div>
            </div>
            <div class="stat-card stat-emerald">
                <div class="num"><?= max(0, $totalThreadsInDb - $spamThreadsCount) ?></div>
                <div class="lbl">Postingan Valid Tersisa</div>
            </div>
        </div>

        <?php if ($spamThreadsCount > 0 || $spamRepliesCount > 0): ?>
            <div style="background:#1e1b4b; border:1px solid #4338ca; border-radius:14px; padding:18px; margin-bottom:20px;">
                <h4 style="color:#c7d2fe; font-size:0.95rem; font-weight:800; margin-bottom:6px;">⚠️ Eksekusi Pembersihan Data & Pembatalan Poin</h4>
                <p style="color:#a5b4fc; font-size:0.8rem; line-height:1.6;">
                    Ditemukan <strong><?= $spamThreadsCount ?> postingan</strong> dan <strong><?= $spamRepliesCount ?> balasan</strong> spam (&lt; <?= $minChars ?> karakter). Klik tombol di bawah untuk menghapus seluruh data ini dan menarik kembali poin reputasi terkait dalam 1 kali klik instan.
                </p>
                <div style="margin-top:14px;">
                    <a href="?secret=pembda99&min_chars=<?= $minChars ?>&scope=<?= $scope ?>&confirm=HAPUS"
                       onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS seluruh <?= $spamThreadsCount + $spamRepliesCount ?> data spam dan MENARIK KEMBALI poin reputasi terkait?')"
                       class="btn btn-danger">
                        🗑️ HAPUS SEKARANG & TARIK POIN (<?= $spamThreadsCount + $spamRepliesCount ?> Data)
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="background:#064e3b; border:1px solid #059669; border-radius:14px; padding:18px; margin-bottom:20px; text-align:center;">
                <h4 style="color:#a7f3d0; font-size:0.95rem; font-weight:800;">🎉 Bersih! Tidak ditemukan postingan spam dengan kriteria &lt; <?= $minChars ?> karakter.</h4>
            </div>
        <?php endif; ?>

        <!-- Tabel Sampel Postingan Spam -->
        <?php if ($sampleThreads->count() > 0): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; margin-bottom:8px;">
                <h3 style="font-size:0.95rem; font-weight:800; color:#f8fafc;">
                    📋 Sampel Postingan Utama Spam (Total: <?= $spamThreadsCount ?>, Menampilkan <?= $sampleThreads->count() ?>)
                </h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Penulis</th>
                            <th>Grup / Kanal</th>
                            <th>Judul & Isi Postingan</th>
                            <th>Panjang</th>
                            <th>Poin Ditarik</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sampleThreads as $th): ?>
                            <tr>
                                <td style="font-weight:bold; color:#94a3b8;">#<?= $th->id ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($th->author_name ?? 'User') ?></strong>
                                    <div style="font-size:0.7rem; color:#64748b;"><?= htmlspecialchars($th->author_role ?? '-') ?></div>
                                </td>
                                <td>
                                    <span style="font-size:0.75rem; font-weight:700; color:#38bdf8;">
                                        <?= htmlspecialchars($th->group_name ?? ($th->category ? ucfirst($th->category) : 'Publik')) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:700; color:#f1f5f9; font-size:0.8rem;">
                                        <?= htmlspecialchars($th->title) ?>
                                    </div>
                                    <div class="spam-highlight" style="margin-top:4px;">
                                        "<?= htmlspecialchars($th->content) ?>"
                                    </div>
                                </td>
                                <td>
                                    <span class="char-pill"><?= mb_strlen(trim($th->content)) ?> char</span>
                                </td>
                                <td>
                                    <span class="points-pill">-15 Poin</span>
                                </td>
                                <td style="font-size:0.75rem; color:#94a3b8;">
                                    <?= $th->created_at ? date('d/m/Y H:i', strtotime($th->created_at)) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Tabel Sampel Balasan Spam -->
        <?php if ($sampleReplies->count() > 0): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:30px; margin-bottom:8px;">
                <h3 style="font-size:0.95rem; font-weight:800; color:#f8fafc;">
                    💬 Sampel Komentar / Balasan Spam (Total: <?= $spamRepliesCount ?>, Menampilkan <?= $sampleReplies->count() ?>)
                </h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Penulis</th>
                            <th>Topik Terkait</th>
                            <th>Isi Balasan</th>
                            <th>Panjang</th>
                            <th>Poin Ditarik</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sampleReplies as $rep): ?>
                            <tr>
                                <td style="font-weight:bold; color:#94a3b8;">#<?= $rep->id ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($rep->author_name ?? 'User') ?></strong>
                                    <div style="font-size:0.7rem; color:#64748b;"><?= htmlspecialchars($rep->author_role ?? '-') ?></div>
                                </td>
                                <td>
                                    <span style="font-size:0.75rem; color:#94a3b8;">
                                        <?= htmlspecialchars($rep->thread_title ?? 'Thread #' . $rep->forum_thread_id) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="spam-highlight">
                                        "<?= htmlspecialchars($rep->content) ?>"
                                    </div>
                                </td>
                                <td>
                                    <span class="char-pill"><?= mb_strlen(trim($rep->content)) ?> char</span>
                                </td>
                                <td>
                                    <span class="points-pill">-5 Poin</span>
                                </td>
                                <td style="font-size:0.75rem; color:#94a3b8;">
                                    <?= $rep->created_at ? date('d/m/Y H:i', strtotime($rep->created_at)) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="margin-top:30px; padding-top:16px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b;">
            <span>PembdaHUB High Speed Engine</span>
            <span>Hostinger Production Tool</span>
        </div>
    </div>
</div>

</body>
</html>
