<?php
/**
 * ADVANCED SMART CLEANUP & REPUTATION POINTS REVERSAL ENGINE
 * Pembersihan Cerdas Postingan & Komentar Spam Pembda Space:
 * 1. Di bawah 15 Karakter
 * 2. Duplikasi Berulang oleh Orang yang Sama (Copy-Paste / Flooding)
 * 3. Karakter Acak / Keyboard Smash / Huruf Berulang (Gibberish)
 * Serta Pembatalan & Sinkronisasi Poin Reputasi Pengguna
 *
 * Akses:
 * - Preview / Diagnostik: perguruanpembda.com/cleanup_space_spam.php?secret=pembda99
 * - Eksekusi Hapus Cepat: perguruanpembda.com/cleanup_space_spam.php?secret=pembda99&filter_type=all&confirm=HAPUS
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

$filterType = $_GET['filter_type'] ?? 'all'; // 'all', 'short', 'duplicate', 'gibberish'
$minChars   = isset($_GET['min_chars']) ? max(1, (int)$_GET['min_chars']) : 15;
$isConfirm  = isset($_GET['confirm']) && $_GET['confirm'] === 'HAPUS';

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

/**
 * Helper: Deteksi Cerdas Konten Spam / Gibberish / Huruf Acak
 */
function analyzeSpamText($title, $content, $minChars = 15) {
    $title = trim((string)$title);
    $content = trim((string)$content);
    $reasons = [];
    $types = [];

    $titleLen = mb_strlen($title);
    $contentLen = mb_strlen($content);

    // 1. Kurang dari batas minimum karakter (< 15)
    if (($title !== '' && $titleLen < $minChars) || $contentLen < $minChars) {
        $reasons[] = "Di bawah {$minChars} karakter (Judul: {$titleLen}, Isi: {$contentLen})";
        $types['short'] = true;
    }

    $combinedText = strtolower($title . ' ' . $content);
    $cleanAlpha = preg_replace('/[^a-z0-9]/', '', $combinedText);
    $totalLen = strlen($cleanAlpha);

    if ($totalLen >= 4) {
        // 2. Karakter Berulang Berlebihan (contoh: 'aaaaaa', 'wwwwww', '.......', 'zzzzzzzz', '111111')
        if (preg_match('/(.)\1{3,}/u', $combinedText)) {
            $reasons[] = "Karakter berulang berlebihan (spam tombol keyboard)";
            $types['gibberish'] = true;
        }

        // 3. Variasi Huruf Sangat Rendah (contoh: 'hahahahahahahaha', 'kwkwkwkwkwkw', 'asdasdasdasd')
        $uniqueChars = count(count_chars($cleanAlpha, 1));
        if ($totalLen >= 8 && ($uniqueChars / $totalLen) < 0.28) {
            $reasons[] = "Variasi karakter sangat rendah ({$uniqueChars} huruf unik dari {$totalLen} huruf)";
            $types['gibberish'] = true;
        }

        // 4. Pola Keyboard Smash / Deret Tombol Acak
        if (preg_match('/(asdf|sdfg|dfgh|fghj|ghjk|hjkl|qwerty|werty|ertyu|rtyui|tyuio|yuio|uiop|zxcv|xcvb|cvbn|vbnm|12345|qwer|asdfg|hjkl)/i', $combinedText)) {
            $reasons[] = "Pola ketikan acak / keyboard smash";
            $types['gibberish'] = true;
        }

        // 5. Teks Uji Coba / Dummy Pendek Berulang
        if (preg_match('/^(test|tes|testing|coba|halo|hai|p|ya|ok|oke|siap|mantap|bisa|hadir)(\s+(test|tes|testing|coba|halo|hai|p|ya|ok|oke|siap|mantap|bisa|hadir))*$/i', $combinedText)) {
            $reasons[] = "Teks uji coba / kata pendek berulang";
            $types['gibberish'] = true;
        }

        // 6. Tanpa Vokal (Konsonan Acak Panjang)
        if ($totalLen >= 7 && !preg_match('/[aiueo]/i', $cleanAlpha)) {
            $reasons[] = "Konsonan acak tanpa huruf vokal";
            $types['gibberish'] = true;
        }
    }

    return [
        'is_spam' => !empty($reasons),
        'reasons' => $reasons,
        'types' => $types
    ];
}

// ── 1. AMBIL SELURUH THREADS & ANALISIS SPAM SECARA MENYELURUH ──
$allThreads = DB::table('forum_threads')
    ->leftJoin('users', 'forum_threads.user_id', '=', 'users.id')
    ->leftJoin('forum_groups', 'forum_threads.group_id', '=', 'forum_groups.id')
    ->select(
        'forum_threads.id',
        'forum_threads.user_id',
        'forum_threads.title',
        'forum_threads.content',
        'forum_threads.created_at',
        'forum_threads.category',
        'users.name as author_name',
        'users.role as author_role',
        'forum_groups.name as group_name'
    )
    ->orderBy('forum_threads.id', 'asc')
    ->get();

$spamThreadMap = [];
$threadUserPostMap = []; // Untuk deteksi duplikasi judul/isi oleh user yang sama

foreach ($allThreads as $t) {
    $analysis = analyzeSpamText($t->title, $t->content, $minChars);
    
    // Cek Duplikasi oleh User yang Sama
    $normalizedSignature = strtolower(trim($t->title)) . '|||' . strtolower(trim($t->content));
    $userKey = $t->user_id . ':::' . $normalizedSignature;
    
    if (isset($threadUserPostMap[$userKey])) {
        $threadUserPostMap[$userKey]++;
        $analysis['is_spam'] = true;
        $analysis['reasons'][] = "Duplikasi postingan ke-{$threadUserPostMap[$userKey]} dengan judul & isi yang sama persis";
        $analysis['types']['duplicate'] = true;
    } else {
        $threadUserPostMap[$userKey] = 1;
    }

    if ($analysis['is_spam']) {
        $t->spam_reasons = $analysis['reasons'];
        $t->spam_types = $analysis['types'];
        $spamThreadMap[$t->id] = $t;
    }
}

// ── 2. AMBIL SELURUH BALASAN / KOMENTAR & ANALISIS SPAM ──
$allReplies = DB::table('forum_replies')
    ->leftJoin('users', 'forum_replies.user_id', '=', 'users.id')
    ->leftJoin('forum_threads', 'forum_replies.forum_thread_id', '=', 'forum_threads.id')
    ->whereNull('forum_replies.voice_note_path')
    ->select(
        'forum_replies.id',
        'forum_replies.user_id',
        'forum_replies.forum_thread_id',
        'forum_replies.content',
        'forum_replies.created_at',
        'users.name as author_name',
        'users.role as author_role',
        'forum_threads.title as thread_title'
    )
    ->orderBy('forum_replies.id', 'asc')
    ->get();

$spamReplyMap = [];
$replyUserMap = []; // Untuk deteksi komentar duplikat oleh user yang sama

foreach ($allReplies as $r) {
    $analysis = analyzeSpamText('', $r->content, $minChars);
    
    $normalizedContent = strtolower(trim($r->content));
    $userReplyKey = $r->user_id . ':::' . $normalizedContent;

    if (isset($replyUserMap[$userReplyKey])) {
        $replyUserMap[$userReplyKey]++;
        $analysis['is_spam'] = true;
        $analysis['reasons'][] = "Duplikasi komentar ke-{$replyUserMap[$userReplyKey]} dengan teks yang sama";
        $analysis['types']['duplicate'] = true;
    } else {
        $replyUserMap[$userReplyKey] = 1;
    }

    if ($analysis['is_spam']) {
        $r->spam_reasons = $analysis['reasons'];
        $r->spam_types = $analysis['types'];
        $spamReplyMap[$r->id] = $r;
    }
}

// ── 3. FILTER SESUAI FILTER TYPE YANG DIPILIH PENGGUNA ──
$filteredSpamThreads = [];
foreach ($spamThreadMap as $id => $t) {
    if ($filterType === 'all' || isset($t->spam_types[$filterType])) {
        $filteredSpamThreads[$id] = $t;
    }
}

$filteredSpamReplies = [];
foreach ($spamReplyMap as $id => $r) {
    if ($filterType === 'all' || isset($r->spam_types[$filterType])) {
        $filteredSpamReplies[$id] = $r;
    }
}

$targetThreadIds = array_keys($filteredSpamThreads);
$targetReplyIds = array_keys($filteredSpamReplies);

// Estimasi Poin yang Akan Dibatalkan / Ditarik
$estimatedPoints = (count($targetThreadIds) * 15) + (count($targetReplyIds) * 5);

// ── 4. EKSEKUSI PENGHAPUSAN DAN PENARIKAN POIN JIKA CONFIRM ──
$deletedThreadsCount = 0;
$deletedRepliesCount = 0;
$revokedPointsCount = 0;
$recalculatedUsersCount = 0;

if ($isConfirm && (!empty($targetThreadIds) || !empty($targetReplyIds))) {
    DB::beginTransaction();
    try {
        $affectedUserIds = [];

        // 1. Eksekusi Hapus Threads Terfilter & Tarik Poinnya
        if (!empty($targetThreadIds)) {
            $chunks = array_chunk($targetThreadIds, 500);
            foreach ($chunks as $chunk) {
                $threadUserIds = DB::table('forum_threads')->whereIn('id', $chunk)->pluck('user_id')->toArray();
                $affectedUserIds = array_merge($affectedUserIds, $threadUserIds);

                // Tarik poin reputasi threads (+15 pts)
                $tPointSum = DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumThread::class, 'App\Models\ForumThread'])
                    ->whereIn('reference_id', $chunk)
                    ->sum('points');
                $revokedPointsCount += (int)$tPointSum;

                DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumThread::class, 'App\Models\ForumThread'])
                    ->whereIn('reference_id', $chunk)
                    ->delete();

                // Hapus balasan & poin balasan terkait thread ini
                $rIds = DB::table('forum_replies')->whereIn('forum_thread_id', $chunk)->pluck('id')->toArray();
                if (!empty($rIds)) {
                    $rUserIds = DB::table('forum_replies')->whereIn('id', $rIds)->pluck('user_id')->toArray();
                    $affectedUserIds = array_merge($affectedUserIds, $rUserIds);

                    $rPointSum = DB::table('reputation_logs')
                        ->whereIn('reference_type', [ForumReply::class, 'App\Models\ForumReply'])
                        ->whereIn('reference_id', $rIds)
                        ->sum('points');
                    $revokedPointsCount += (int)$rPointSum;

                    DB::table('forum_reactions')->whereIn('forum_reply_id', $rIds)->delete();
                    DB::table('reputation_logs')
                        ->whereIn('reference_type', [ForumReply::class, 'App\Models\ForumReply'])
                        ->whereIn('reference_id', $rIds)
                        ->delete();
                    DB::table('forum_replies')->whereIn('id', $rIds)->delete();
                }

                // Hapus likes, reactions, members
                DB::table('forum_likes')->whereIn('forum_thread_id', $chunk)->delete();
                DB::table('forum_reactions')->whereIn('forum_thread_id', $chunk)->delete();
                if (DB::getSchemaBuilder()->hasTable('forum_members')) {
                    DB::table('forum_members')->whereIn('forum_thread_id', $chunk)->delete();
                }

                // Hapus polls
                $pollIds = DB::table('forum_polls')->whereIn('forum_thread_id', $chunk)->pluck('id')->toArray();
                if (!empty($pollIds)) {
                    DB::table('forum_poll_votes')->whereIn('forum_poll_id', $pollIds)->delete();
                    DB::table('forum_poll_options')->whereIn('forum_poll_id', $pollIds)->delete();
                    DB::table('forum_polls')->whereIn('id', $pollIds)->delete();
                }

                $deletedThreadsCount += DB::table('forum_threads')->whereIn('id', $chunk)->delete();
            }
        }

        // 2. Eksekusi Hapus Balasan / Komentar Terfilter & Tarik Poinnya
        if (!empty($targetReplyIds)) {
            $chunks = array_chunk($targetReplyIds, 500);
            foreach ($chunks as $chunk) {
                $rUserIds = DB::table('forum_replies')->whereIn('id', $chunk)->pluck('user_id')->toArray();
                $affectedUserIds = array_merge($affectedUserIds, $rUserIds);

                $rPointSum = DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumReply::class, 'App\Models\ForumReply'])
                    ->whereIn('reference_id', $chunk)
                    ->sum('points');
                $revokedPointsCount += (int)$rPointSum;

                DB::table('forum_reactions')->whereIn('forum_reply_id', $chunk)->delete();
                DB::table('reputation_logs')
                    ->whereIn('reference_type', [ForumReply::class, 'App\Models\ForumReply'])
                    ->whereIn('reference_id', $chunk)
                    ->delete();

                $deletedRepliesCount += DB::table('forum_replies')->whereIn('id', $chunk)->delete();
            }
        }

        // 3. SINKRONISASI ULANG TOTAL POIN & LEVEL SELURUH PENGGUNA TERDAMPAK
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

        // Kosongkan list setelah eksekusi
        $filteredSpamThreads = [];
        $filteredSpamReplies = [];
        $targetThreadIds = [];
        $targetReplyIds = [];
        $estimatedPoints = 0;

    } catch (\Throwable $e) {
        DB::rollBack();
        $errorMessage = $e->getMessage();
    }
}

$executionTime = round((microtime(true) - $startTime) * 1000, 2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🧹 Pembersih Cerdas Spam & Pembatalan Poin Pembda Space</title>
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
    .badge-speed { background: #0284c7; color: #e0f2fe; font-size: 0.72rem; font-weight: 800; padding: 4px 10px; border-radius: 99px; }

    /* Filter Bar */
    .filter-bar { background: #0a1120; border-radius: 14px; padding: 16px; margin-bottom: 24px; border: 1px solid #1e293b; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .filter-group { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }
    .filter-group select { background: #1e293b; color: #fff; border: 1px solid #334155; padding: 8px 14px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; }

    /* Stat Cards */
    .stat-grid { display: grid; gap: 14px; margin-bottom: 24px; }
    @media(min-width: 640px) { .stat-grid { grid-template-columns: repeat(4, 1fr); } }
    .stat-card { background: #0a1120; border-radius: 14px; padding: 18px; text-align: center; border: 1px solid #1e293b; }
    .stat-card .num { font-size: 1.8rem; font-weight: 900; }
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
    .table-container { overflow-x: auto; margin-top: 14px; border-radius: 12px; border: 1px solid #1e293b; margin-bottom: 24px; }
    table { width: 100%; border-collapse: collapse; font-size: 0.82rem; text-align: left; }
    th { background: #0a1120; padding: 12px 16px; font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #1e293b; }
    td { padding: 12px 16px; border-bottom: 1px solid #131c2e; vertical-align: middle; }
    tr:nth-child(even) { background: #0e1626; }
    tr:hover { background: #17233a; }

    .tag-reason { display: inline-block; background: #881337; color: #fecdd3; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; margin: 2px 0; }
    .tag-dup { background: #78350f; color: #fef08a; }
    .tag-gib { background: #4c0519; color: #fca5a5; }
    .points-pill { display: inline-block; background: #581c87; color: #e9d5ff; font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 99px; }
    .spam-quote { background: #ff005515; color: #fca5a5; font-size: 0.8rem; padding: 6px 10px; border-radius: 8px; border-left: 3px solid #f43f5e; margin-top: 4px; word-break: break-word; }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="title-group">
                <span class="icon">🧹</span>
                <div>
                    <h1>Pembersih Cerdas Spam & Pembatalan Poin Space</h1>
                    <p class="subtitle">Mendeteksi 3 jenis anomali: di bawah 15 karakter, duplikasi berulang, dan karakter acak / keyboard smash.</p>
                </div>
            </div>
            <span class="badge-speed">⚡ Smart Analyzer (<?= $executionTime ?> ms)</span>
        </div>

        <?php if ($isConfirm && !$errorMessage): ?>
            <div class="alert-success">
                <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:6px;">✨ Seluruh Postingan & Komentar Spam Berhasil Dibersihkan!</h3>
                <p>Rekapitulasi data yang berhasil dibersihkan dan poin yang ditarik:</p>
                <ul style="margin: 12px 0 0 20px; line-height: 1.9; font-size: 0.85rem;">
                    <li><strong><?= $deletedThreadsCount ?> Postingan Utama (Threads)</strong> spam terhapus permanen.</li>
                    <li><strong><?= $deletedRepliesCount ?> Balasan / Komentar Spam</strong> terhapus permanen.</li>
                    <li><strong style="color:#fef08a;">-<?= number_format($revokedPointsCount) ?> Poin Reputasi</strong> berhasil ditarik & dibatalkan dari user pembuat spam.</li>
                    <li><strong><?= $recalculatedUsersCount ?> Pengguna</strong> total poin dan level peringkatnya telah disinkronkan kembali secara akurat.</li>
                </ul>
                <div style="margin-top: 16px;">
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
                <label>Jenis Anomali Spam:</label>
                <select name="filter_type" onchange="this.form.submit()">
                    <option value="all" <?= $filterType == 'all' ? 'selected' : '' ?>>Semua Anomali (Pendek + Duplikat + Karakter Acak)</option>
                    <option value="short" <?= $filterType == 'short' ? 'selected' : '' ?>>Hanya di Bawah 15 Karakter</option>
                    <option value="duplicate" <?= $filterType == 'duplicate' ? 'selected' : '' ?>>Hanya Duplikasi Berulang (Copy-Paste Flood)</option>
                    <option value="gibberish" <?= $filterType == 'gibberish' ? 'selected' : '' ?>>Hanya Karakter Acak / Keyboard Smash</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Batas Karakter Minimum:</label>
                <select name="min_chars" onchange="this.form.submit()">
                    <option value="15" <?= $minChars == 15 ? 'selected' : '' ?>>15 Karakter (Standar)</option>
                    <option value="10" <?= $minChars == 10 ? 'selected' : '' ?>>10 Karakter</option>
                    <option value="5" <?= $minChars == 5 ? 'selected' : '' ?>>5 Karakter</option>
                </select>
            </div>

            <button type="submit" class="btn btn-preview" style="padding: 8px 16px; font-size: 0.8rem;">
                🔍 Refresh Analisis
            </button>
        </form>

        <!-- Stats Grid -->
        <div class="stat-grid">
            <div class="stat-card stat-rose">
                <div class="num"><?= count($filteredSpamThreads) ?></div>
                <div class="lbl">Postingan Terdeteksi Spam</div>
            </div>
            <div class="stat-card stat-amber">
                <div class="num"><?= count($filteredSpamReplies) ?></div>
                <div class="lbl">Komentar Terdeteksi Spam</div>
            </div>
            <div class="stat-card stat-purple">
                <div class="num">-<?= number_format($estimatedPoints) ?></div>
                <div class="lbl">Poin Spam yang Akan Ditarik</div>
            </div>
            <div class="stat-card stat-emerald">
                <div class="num"><?= max(0, $allThreads->count() - count($filteredSpamThreads)) ?></div>
                <div class="lbl">Postingan Valid Tersisa</div>
            </div>
        </div>

        <?php if (count($filteredSpamThreads) > 0 || count($filteredSpamReplies) > 0): ?>
            <div style="background:#1e1b4b; border:1px solid #4338ca; border-radius:14px; padding:20px; margin-bottom:24px;">
                <h4 style="color:#c7d2fe; font-size:1rem; font-weight:800; margin-bottom:6px;">⚠️ Eksekusi Pembersihan & Pembatalan Poin Spam</h4>
                <p style="color:#a5b4fc; font-size:0.85rem; line-height:1.6;">
                    Ditemukan total <strong><?= count($filteredSpamThreads) ?> postingan</strong> dan <strong><?= count($filteredSpamReplies) ?> komentar</strong> spam yang memenuhi kriteria (karakter pendek / duplikat berulang / karakter acak). Klik tombol di bawah untuk <strong>menghapus permanen seluruh data spam ini dan otomatis MENARIK KEMBALI ~<?= number_format($estimatedPoints) ?> poin reputasi terkait</strong>.
                </p>
                <div style="margin-top:16px;">
                    <a href="?secret=pembda99&filter_type=<?= $filterType ?>&min_chars=<?= $minChars ?>&confirm=HAPUS"
                       onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN seluruh <?= count($filteredSpamThreads) + count($filteredSpamReplies) ?> data spam ini dan MENARIK KEMBALI <?= number_format($estimatedPoints) ?> poin reputasi dari akun pengguna?')"
                       class="btn btn-danger">
                        🗑️ HAPUS SEKARANG & TARIK <?= number_format($estimatedPoints) ?> POIN REPUTASI
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="background:#064e3b; border:1px solid #059669; border-radius:14px; padding:18px; margin-bottom:24px; text-align:center;">
                <h4 style="color:#a7f3d0; font-size:0.95rem; font-weight:800;">🎉 Bersih! Tidak ditemukan postingan/komentar spam dengan filter yang dipilih.</h4>
            </div>
        <?php endif; ?>

        <!-- Tabel Daftar Postingan Spam -->
        <?php if (!empty($filteredSpamThreads)): ?>
            <h3 style="font-size:0.95rem; font-weight:800; color:#f8fafc; margin-bottom:8px;">
                📋 Daftar Postingan Utama Spam (Total: <?= count($filteredSpamThreads) ?>, Menampilkan <?= min(60, count($filteredSpamThreads)) ?>)
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Penulis</th>
                            <th>Grup / Kanal</th>
                            <th>Isi & Judul Postingan</th>
                            <th>Alasan Terdeteksi Spam</th>
                            <th>Poin Ditarik</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $shown = 0; foreach($filteredSpamThreads as $th): if ($shown++ >= 60) break; ?>
                            <tr>
                                <td style="font-weight:bold; color:#94a3b8;">#<?= $th->id ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($th->author_name ?? 'User #' . $th->user_id) ?></strong>
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
                                    <div class="spam-quote">
                                        "<?= htmlspecialchars(mb_substr($th->content, 0, 150)) . (mb_strlen($th->content) > 150 ? '...' : '') ?>"
                                    </div>
                                </td>
                                <td>
                                    <?php foreach($th->spam_reasons as $reason): ?>
                                        <div class="tag-reason <?= strpos($reason, 'Duplikasi') !== false ? 'tag-dup' : (strpos($reason, 'acak') !== false || strpos($reason, 'keyboard') !== false ? 'tag-gib' : '') ?>">
                                            ⚠️ <?= htmlspecialchars($reason) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td><span class="points-pill">-15 Poin</span></td>
                                <td style="font-size:0.75rem; color:#94a3b8;"><?= $th->created_at ? date('d/m/Y H:i', strtotime($th->created_at)) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Tabel Daftar Komentar Spam -->
        <?php if (!empty($filteredSpamReplies)): ?>
            <h3 style="font-size:0.95rem; font-weight:800; color:#f8fafc; margin-top:20px; margin-bottom:8px;">
                💬 Daftar Balasan / Komentar Spam (Total: <?= count($filteredSpamReplies) ?>, Menampilkan <?= min(60, count($filteredSpamReplies)) ?>)
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Penulis</th>
                            <th>Topik Terkait</th>
                            <th>Isi Balasan</th>
                            <th>Alasan Terdeteksi Spam</th>
                            <th>Poin Ditarik</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $shownR = 0; foreach($filteredSpamReplies as $rep): if ($shownR++ >= 60) break; ?>
                            <tr>
                                <td style="font-weight:bold; color:#94a3b8;">#<?= $rep->id ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($rep->author_name ?? 'User #' . $rep->user_id) ?></strong>
                                    <div style="font-size:0.7rem; color:#64748b;"><?= htmlspecialchars($rep->author_role ?? '-') ?></div>
                                </td>
                                <td>
                                    <span style="font-size:0.75rem; color:#94a3b8;">
                                        <?= htmlspecialchars(mb_substr($rep->thread_title ?? 'Thread #' . $rep->forum_thread_id, 0, 30)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="spam-quote">
                                        "<?= htmlspecialchars($rep->content) ?>"
                                    </div>
                                </td>
                                <td>
                                    <?php foreach($rep->spam_reasons as $reason): ?>
                                        <div class="tag-reason <?= strpos($reason, 'Duplikasi') !== false ? 'tag-dup' : '' ?>">
                                            ⚠️ <?= htmlspecialchars($reason) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td><span class="points-pill">-5 Poin</span></td>
                                <td style="font-size:0.75rem; color:#94a3b8;"><?= $rep->created_at ? date('d/m/Y H:i', strtotime($rep->created_at)) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="margin-top:30px; padding-top:16px; border-top:1px solid #1e293b; display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b;">
            <span>PembdaHUB Smart Content & Reputation Cleaning Engine</span>
            <span>Hostinger Production Tool</span>
        </div>
    </div>
</div>

</body>
</html>
