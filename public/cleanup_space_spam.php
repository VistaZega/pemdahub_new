<?php
/**
 * CLEANUP SCRIPT - Pembersihan Postingan & Komentar Spam / Terlalu Singkat di Pembda Space
 *
 * Akses:
 * - Preview / Diagnostik: perguruanpembda.com/cleanup_space_spam.php?secret=pembda99
 * - Eksekusi Hapus: perguruanpembda.com/cleanup_space_spam.php?secret=pembda99&min_chars=15&confirm=HAPUS
 */

// ── Keamanan ──
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('<h2 style="color:red; font-family:sans-serif; text-align:center; margin-top:50px;">403 Forbidden - Parameter ?secret=pembda99 diperlukan!</h2>');
}

$minChars   = isset($_GET['min_chars']) ? max(1, (int)$_GET['min_chars']) : 15;
$scope      = $_GET['scope'] ?? 'all'; // 'all', 'threads', 'replies'
$isConfirm  = isset($_GET['confirm']) && $_GET['confirm'] === 'HAPUS';

// ── Bootstrap Laravel ──
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\ForumLike;
use App\Models\ForumReaction;
use App\Models\ForumPoll;
use App\Models\ForumPollOption;
use App\Models\ForumPollVote;
use App\Models\ForumMember;
use App\Models\AlumniForum;
use App\Models\AlumniForumReply;
use App\Models\ReputationLog;

// ── Eksekusi Query Pembersihan / Preview ──

// 1. Ambil Data Thread Spam (< $minChars)
$spamThreadsQuery = ForumThread::with(['user', 'group'])
    ->where(function ($q) use ($minChars) {
        $q->whereRaw('CHAR_LENGTH(TRIM(content)) < ?', [$minChars])
          ->orWhereRaw('CHAR_LENGTH(TRIM(title)) < ?', [$minChars]);
    });

$spamThreads = $spamThreadsQuery->latest()->get();
$totalThreadsInDb = ForumThread::count();

// 2. Ambil Data Balasan/Komentar Spam (< 10 karakter atau < $minChars)
$replyMinChars = min($minChars, 10);
$spamRepliesQuery = ForumReply::with(['user', 'thread'])
    ->whereNull('voice_note_path') // jangan hapus voice note
    ->whereRaw('CHAR_LENGTH(TRIM(content)) < ?', [$replyMinChars]);

$spamReplies = $spamRepliesQuery->latest()->get();
$totalRepliesInDb = ForumReply::count();

// 3. Alumni Forum Spam (jika ada)
$spamAlumniThreads = AlumniForum::with('user')
    ->where(function ($q) use ($minChars) {
        $q->whereRaw('CHAR_LENGTH(TRIM(content)) < ?', [$minChars])
          ->orWhereRaw('CHAR_LENGTH(TRIM(title)) < ?', [$minChars]);
    })->get();

$deletedThreadsCount = 0;
$deletedRepliesCount = 0;
$deletedAlumniCount = 0;
$deletedFilesCount = 0;
$errorMessage = null;

if ($isConfirm) {
    DB::beginTransaction();
    try {
        // Hapus Threads Spam
        if ($scope === 'all' || $scope === 'threads') {
            foreach ($spamThreads as $thread) {
                // Hapus file gambar & attachment
                if ($thread->image_path && Storage::disk('public')->exists($thread->image_path)) {
                    Storage::disk('public')->delete($thread->image_path);
                    $deletedFilesCount++;
                }
                if ($thread->attachment_path && Storage::disk('public')->exists($thread->attachment_path)) {
                    Storage::disk('public')->delete($thread->attachment_path);
                    $deletedFilesCount++;
                }

                // Hapus relasi balasan & filenya
                $replies = ForumReply::where('forum_thread_id', $thread->id)->get();
                foreach ($replies as $r) {
                    if ($r->voice_note_path && Storage::disk('public')->exists($r->voice_note_path)) {
                        Storage::disk('public')->delete($r->voice_note_path);
                        $deletedFilesCount++;
                    }
                    ForumReaction::where('forum_reply_id', $r->id)->delete();
                    $r->delete();
                }

                // Hapus relasi likes, reactions, members
                ForumLike::where('forum_thread_id', $thread->id)->delete();
                ForumReaction::where('forum_thread_id', $thread->id)->delete();
                ForumMember::where('forum_thread_id', $thread->id)->delete();

                // Hapus polls
                $polls = ForumPoll::where('forum_thread_id', $thread->id)->get();
                foreach ($polls as $poll) {
                    ForumPollVote::where('forum_poll_id', $poll->id)->delete();
                    ForumPollOption::where('forum_poll_id', $poll->id)->delete();
                    $poll->delete();
                }

                // Hapus reputation logs jika ada
                ReputationLog::where('reference_type', ForumThread::class)
                    ->where('reference_id', $thread->id)
                    ->delete();

                $thread->delete();
                $deletedThreadsCount++;
            }
        }

        // Hapus Balasan Spam
        if ($scope === 'all' || $scope === 'replies') {
            foreach ($spamReplies as $reply) {
                if ($reply->exists) {
                    ForumReaction::where('forum_reply_id', $reply->id)->delete();
                    ReputationLog::where('reference_type', ForumReply::class)
                        ->where('reference_id', $reply->id)
                        ->delete();
                    $reply->delete();
                    $deletedRepliesCount++;
                }
            }
        }

        // Hapus Alumni Threads Spam
        if ($scope === 'all' || $scope === 'threads') {
            foreach ($spamAlumniThreads as $athread) {
                if ($athread->image_path && Storage::disk('public')->exists($athread->image_path)) {
                    Storage::disk('public')->delete($athread->image_path);
                    $deletedFilesCount++;
                }
                AlumniForumReply::where('alumni_forum_id', $athread->id)->delete();
                $athread->delete();
                $deletedAlumniCount++;
            }
        }

        DB::commit();

        // Refresh count data setelah eksekusi
        $spamThreads = collect();
        $spamReplies = collect();
        $spamAlumniThreads = collect();
        $totalThreadsInDb = ForumThread::count();
        $totalRepliesInDb = ForumReply::count();

    } catch (\Throwable $e) {
        DB::rollBack();
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🧹 Pembersih Postingan Spam - Pembda Space</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    body { background: #0b0f19; color: #e2e8f0; min-height: 100vh; padding: 30px 15px; }
    .container { max-width: 1000px; margin: 0 auto; }
    .card { background: #131c2e; border-radius: 20px; padding: 28px; border: 1px solid #1e293b; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b; padding-bottom: 20px; margin-bottom: 24px; }
    .title-group { display: flex; align-items: center; gap: 14px; }
    .icon { font-size: 2.2rem; }
    h1 { font-size: 1.4rem; font-weight: 800; color: #f8fafc; }
    .subtitle { color: #94a3b8; font-size: 0.8rem; margin-top: 4px; }
    .badge-secret { background: #064e3b; color: #34d399; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; }

    /* Filter Controls */
    .filter-bar { background: #0a1120; border-radius: 14px; padding: 16px; margin-bottom: 24px; border: 1px solid #1e293b; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .filter-group { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }
    .filter-group select { background: #1e293b; color: #fff; border: 1px solid #334155; padding: 8px 14px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; }

    /* Stat Cards */
    .stat-grid { display: grid; gap: 14px; margin-bottom: 24px; }
    @media(min-width: 640px) { .stat-grid { grid-template-columns: repeat(3, 1fr); } }
    .stat-card { background: #0a1120; border-radius: 14px; padding: 18px; text-align: center; border: 1px solid #1e293b; }
    .stat-card .num { font-size: 2.2rem; font-weight: 900; }
    .stat-card .lbl { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
    .stat-rose .num { color: #f43f5e; }
    .stat-amber .num { color: #f59e0b; }
    .stat-emerald .num { color: #10b981; }

    /* Alerts */
    .alert-success { background: #064e3b; border: 1px solid #059669; color: #a7f3d0; padding: 18px; border-radius: 14px; margin-bottom: 24px; }
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
                    <h1>Pembersih Postingan Spam Pembda Space</h1>
                    <p class="subtitle">Mendeteksi dan menghapus postingan 1 karakter / spam pendek di database.</p>
                </div>
            </div>
            <span class="badge-secret">🔒 Mode Aman: Terverifikasi</span>
        </div>

        <?php if ($isConfirm && !$errorMessage): ?>
            <div class="alert-success">
                <h3 style="font-size:1.1rem; font-weight:800; margin-bottom:6px;">✨ Pembersihan Berhasil Dilakukan!</h3>
                <p>Berikut rekapitulasi data yang berhasil dibersihkan dari database:</p>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8; font-size: 0.85rem;">
                    <li><strong><?= $deletedThreadsCount ?></strong> Postingan Utama (Threads) terhapus.</li>
                    <li><strong><?= $deletedRepliesCount ?></strong> Balasan / Komentar Spam terhapus.</li>
                    <li><strong><?= $deletedAlumniCount ?></strong> Postingan Forum Alumni terhapus.</li>
                    <li><strong><?= $deletedFilesCount ?></strong> File gambar & lampiran yatim terhapus dari storage.</li>
                </ul>
                <div style="margin-top: 14px;">
                    <a href="?secret=pembda99" class="btn btn-preview" style="padding: 8px 16px; font-size: 0.8rem;">🔄 Kembali ke Halaman Preview</a>
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
                <div class="num"><?= $spamThreads->count() ?></div>
                <div class="lbl">Postingan Terdeteksi Spam (&lt; <?= $minChars ?> Karakter)</div>
            </div>
            <div class="stat-card stat-amber">
                <div class="num"><?= $spamReplies->count() ?></div>
                <div class="lbl">Komentar Spam (&lt; <?= $replyMinChars ?> Karakter)</div>
            </div>
            <div class="stat-card stat-emerald">
                <div class="num"><?= max(0, $totalThreadsInDb - $spamThreads->count()) ?></div>
                <div class="lbl">Postingan Valid Tersisa</div>
            </div>
        </div>

        <?php if ($spamThreads->count() > 0 || $spamReplies->count() > 0): ?>
            <div style="background:#1e1b4b; border:1px solid #4338ca; border-radius:14px; padding:18px; margin-bottom:20px;">
                <h4 style="color:#c7d2fe; font-size:0.9rem; font-weight:800; margin-bottom:6px;">⚠️ Konfirmasi Pembersihan Data</h4>
                <p style="color:#a5b4fc; font-size:0.8rem; line-height:1.6;">
                    Total <strong><?= $spamThreads->count() ?> postingan</strong> dan <strong><?= $spamReplies->count() ?> balasan</strong> di bawah ini memenuhi kriteria spam (&lt; <?= $minChars ?> karakter). Semua lampiran dan relasi polling/reaksi terkait juga akan dibersihkan secara aman.
                </p>
                <div style="margin-top:14px;">
                    <a href="?secret=pembda99&min_chars=<?= $minChars ?>&scope=<?= $scope ?>&confirm=HAPUS"
                       onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN seluruh <?= $spamThreads->count() + $spamReplies->count() ?> data spam ini dari database?')"
                       class="btn btn-danger">
                        🗑️ HAPUS SEKARANG (<?= $spamThreads->count() + $spamReplies->count() ?> Data)
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="background:#064e3b; border:1px solid #059669; border-radius:14px; padding:18px; margin-bottom:20px; text-align:center;">
                <h4 style="color:#a7f3d0; font-size:0.95rem; font-weight:800;">🎉 Bersih! Tidak ditemukan postingan spam dengan kriteria &lt; <?= $minChars ?> karakter.</h4>
            </div>
        <?php endif; ?>

        <!-- Tabel Daftar Postingan Spam -->
        <?php if ($spamThreads->count() > 0): ?>
            <h3 style="font-size:0.95rem; font-weight:800; color:#f8fafc; margin-top:20px; margin-bottom:8px;">
                📋 Daftar Postingan Utama Spam (<?= $spamThreads->count() ?>)
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Penulis</th>
                            <th>Grup / Kanal</th>
                            <th>Judul & Isi Postingan</th>
                            <th>Panjang Teks</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($spamThreads as $th): ?>
                            <tr>
                                <td style="font-weight:bold; color:#94a3b8;">#<?= $th->id ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($th->user->name ?? 'User #' . $th->user_id) ?></strong>
                                    <div style="font-size:0.7rem; color:#64748b;"><?= htmlspecialchars($th->user->role ?? '-') ?></div>
                                </td>
                                <td>
                                    <span style="font-size:0.75rem; font-weight:700; color:#38bdf8;">
                                        <?= htmlspecialchars($th->group->name ?? ($th->category ? ucfirst($th->category) : 'Publik')) ?>
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
                                <td style="font-size:0.75rem; color:#94a3b8;">
                                    <?= $th->created_at ? $th->created_at->format('d/m/Y H:i') : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Tabel Daftar Balasan Spam -->
        <?php if ($spamReplies->count() > 0): ?>
            <h3 style="font-size:0.95rem; font-weight:800; color:#f8fafc; margin-top:30px; margin-bottom:8px;">
                💬 Daftar Komentar / Balasan Spam (<?= $spamReplies->count() ?>)
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Penulis</th>
                            <th>Topik Terkait</th>
                            <th>Isi Balasan</th>
                            <th>Panjang Teks</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($spamReplies as $rep): ?>
                            <tr>
                                <td style="font-weight:bold; color:#94a3b8;">#<?= $rep->id ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($rep->user->name ?? 'User #' . $rep->user_id) ?></strong>
                                    <div style="font-size:0.7rem; color:#64748b;"><?= htmlspecialchars($rep->user->role ?? '-') ?></div>
                                </td>
                                <td>
                                    <span style="font-size:0.75rem; color:#94a3b8;">
                                        <?= htmlspecialchars($rep->thread->title ?? 'Thread #' . $rep->forum_thread_id) ?>
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
                                <td style="font-size:0.75rem; color:#94a3b8;">
                                    <?= $rep->created_at ? $rep->created_at->format('d/m/Y H:i') : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="margin-top:30px; padding-top:16px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b;">
            <span>PembdaHUB Production Maintenance Engine</span>
            <span>Hostinger Production Server Tool</span>
        </div>
    </div>
</div>

</body>
</html>
