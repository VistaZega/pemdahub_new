<?php
/**
 * PembdaHUB — Local Server Points & Academic Data Restore Tool
 * Akses di Server Lokal: http://[IP-SERVER-LOKAL]/restore_server_local.php?secret=pembda99
 */

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('<h1>403 Forbidden</h1><p>Akses ditolak. Parameter secret salah atau tidak disertakan.</p>');
}

@set_time_limit(0);
@ini_set('memory_limit', '1024M');

// Bootstrap DB from .env
$envPaths = [
    __DIR__ . '/../.env',
    '/var/www/pembdahub/.env',
    '/var/www/html/pembdahub/.env',
    __DIR__ . '/.env',
];

$envPath = null;
foreach ($envPaths as $p) {
    if (file_exists($p)) {
        $envPath = $p;
        break;
    }
}

$env = [];
if ($envPath && file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim(trim($v), '"\'');
    }
}

$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? 'pembdahub';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_LOCAL_INFILE => true,
    ]);
} catch (Exception $e) {
    die("<h1>Database Connection Failed</h1><p>" . htmlspecialchars($e->getMessage()) . "</p><p>Periksa konfigurasi .env di server lokal.</p>");
}

$action = $_GET['action'] ?? '';
$message = '';
$status = '';

// Handle Upload if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dump_file'])) {
    if ($_FILES['dump_file']['error'] === UPLOAD_ERR_OK) {
        $uploadTarget = __DIR__ . '/pembdahub_poin_dan_nilai_bersih.sql.gz';
        if (move_uploaded_file($_FILES['dump_file']['tmp_name'], $uploadTarget)) {
            $action = 'restore_clean';
            $message = "File berhasil diunggah dan langsung diproses.";
        } else {
            $status = 'error';
            $message = "Gagal memindahkan file yang diunggah ke folder server.";
        }
    } else {
        $status = 'error';
        $message = "Error saat mengunggah file (Kode: " . $_FILES['dump_file']['error'] . "). Coba salin file secara manual ke folder server.";
    }
}

// ACTION: RESTORE FROM CLEAN GZ OR SQL FILE
if ($action === 'restore_clean') {
    $candidateFiles = [
        __DIR__ . '/pembdahub_poin_dan_nilai_bersih.sql.gz',
        __DIR__ . '/pembdahub_poin_dan_nilai_bersih.sql',
        dirname(__DIR__) . '/pembdahub_poin_dan_nilai_bersih.sql.gz',
        dirname(__DIR__) . '/pembdahub_poin_dan_nilai_bersih.sql',
        '/var/www/pembdahub/pembdahub_poin_dan_nilai_bersih.sql.gz',
        '/var/www/pembdahub/pembdahub_poin_dan_nilai_bersih.sql',
        '/tmp/pembdahub_poin_dan_nilai_bersih.sql.gz',
        '/tmp/pembdahub_poin_dan_nilai_bersih.sql',
        'D:/dump/pembdahub_poin_dan_nilai_bersih.sql.gz',
        'D:/dump/pembdahub_poin_dan_nilai_bersih.sql',
    ];

    if (!empty($_GET['custom_path'])) {
        array_unshift($candidateFiles, $_GET['custom_path']);
    }

    $targetFile = null;
    foreach ($candidateFiles as $f) {
        if (file_exists($f)) {
            $targetFile = $f;
            break;
        }
    }

    if (!$targetFile) {
        $status = 'error';
        $message = "File <code>pembdahub_poin_dan_nilai_bersih.sql.gz</code> atau <code>.sql</code> tidak ditemukan di folder server. Silakan salin berkas tersebut ke: <code>" . __DIR__ . "</code>";
    } else {
        $startTime = microtime(true);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("SET UNIQUE_CHECKS = 0;");
        $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

        $isGz = str_ends_with($targetFile, '.gz');
        $fh = $isGz ? gzopen($targetFile, 'rb') : fopen($targetFile, 'rb');
        $queryBuffer = '';
        $queryCount = 0;
        $tablesModified = [];

        while (($isGz ? !gzeof($fh) : !feof($fh))) {
            $line = $isGz ? gzgets($fh) : fgets($fh);
            if ($line === false) break;
            $trimmed = trim($line);

            if (empty($queryBuffer)) {
                if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }
            }

            $queryBuffer .= $line;

            if (str_ends_with($trimmed, ';')) {
                $sql = trim($queryBuffer);
                $queryBuffer = '';
                if (!empty($sql)) {
                    if (preg_match('/REPLACE\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
                        $tablesModified[$m[1]] = true;
                    }
                    try {
                        $pdo->exec($sql);
                        $queryCount++;
                    } catch (Exception $e) {
                        // ignore minor syntax variations
                    }
                }
            }
        }
        if ($isGz) {
            gzclose($fh);
        } else {
            fclose($fh);
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $pdo->exec("SET UNIQUE_CHECKS = 1;");

        $elapsed = round(microtime(true) - $startTime, 2);
        $status = 'success';
        $tblList = implode(', ', array_keys($tablesModified));
        $message = "Berhasil memulihkan seluruh data! ({$queryCount} batch kueri selesai dalam {$elapsed} detik dari " . basename($targetFile) . "). Tabel terupdate: {$tblList}";
    }
}

// Current Stats
$currRep = 0;
$currLogs = 0;
$currGrades = 0;
$currAchievements = 0;
$currBadges = 0;
$topUsers = [];

try {
    $currRep = (int)$pdo->query("SELECT COUNT(*) FROM reputations")->fetchColumn();
    $currLogs = (int)$pdo->query("SELECT COUNT(*) FROM reputation_logs")->fetchColumn();
    $currGrades = (int)$pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
    $currAchievements = (int)$pdo->query("SELECT COUNT(*) FROM student_achievements")->fetchColumn();
    $currBadges = (int)$pdo->query("SELECT COUNT(*) FROM user_badges")->fetchColumn();
    $topUsers = $pdo->query("SELECT u.name, r.total_points, r.level_name FROM reputations r JOIN users u ON u.id = r.user_id ORDER BY r.total_points DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Check available files on server
$foundFile = null;
$checkLocations = [
    __DIR__ . '/pembdahub_poin_dan_nilai_bersih.sql.gz',
    __DIR__ . '/pembdahub_poin_dan_nilai_bersih.sql',
    dirname(__DIR__) . '/pembdahub_poin_dan_nilai_bersih.sql.gz',
    '/var/www/pembdahub/pembdahub_poin_dan_nilai_bersih.sql.gz',
    '/tmp/pembdahub_poin_dan_nilai_bersih.sql.gz',
    'D:/dump/pembdahub_poin_dan_nilai_bersih.sql.gz',
];
foreach ($checkLocations as $loc) {
    if (file_exists($loc)) {
        $foundFile = $loc;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Poin & Akademik Server Lokal - PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-4 sm:p-8">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-slate-800/90 border border-slate-700 p-6 rounded-3xl shadow-xl flex items-center justify-between flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-lg text-xs font-bold uppercase mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Server Lokal Utility (Ubuntu / Linux)
                </span>
                <h1 class="text-2xl font-black text-white">⚡ Pemulihan Poin & Nilai Server Lokal</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Database aktif: <span class="text-white font-mono font-bold"><?= htmlspecialchars($dbName) ?></span> pada <span class="text-white font-mono"><?= htmlspecialchars($dbHost) ?></span></p>
            </div>
            <a href="?secret=<?= htmlspecialchars($_GET['secret']) ?>" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-2">
                <span>🔄</span> Muat Ulang Status
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="p-5 rounded-2xl border <?= $status === 'success' ? 'bg-emerald-950/60 border-emerald-500 text-emerald-200' : 'bg-rose-950/60 border-rose-500 text-rose-200' ?> flex items-center gap-3">
                <span class="text-2xl"><?= $status === 'success' ? '🎉' : '⚠️' ?></span>
                <div class="text-sm font-medium"><?= $message ?></div>
            </div>
        <?php endif; ?>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Akun Poin</span>
                <div class="text-2xl sm:text-3xl font-black <?= $currRep >= 2000 ? 'text-emerald-400' : 'text-amber-400' ?> mt-1"><?= number_format($currRep) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">Target: 2.131</span>
            </div>
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Log Poin</span>
                <div class="text-2xl sm:text-3xl font-black <?= $currLogs > 500000 ? 'text-emerald-400' : 'text-amber-400' ?> mt-1"><?= number_format($currLogs) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">Target: 573.522</span>
            </div>
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nilai Siswa</span>
                <div class="text-2xl sm:text-3xl font-black <?= $currGrades > 10000 ? 'text-emerald-400' : 'text-amber-400' ?> mt-1"><?= number_format($currGrades) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">Target: 11.236</span>
            </div>
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Prestasi & Badges</span>
                <div class="text-2xl sm:text-3xl font-black <?= $currAchievements > 300 ? 'text-emerald-400' : 'text-amber-400' ?> mt-1"><?= number_format($currAchievements + $currBadges) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">Target: ~2.236</span>
            </div>
        </div>

        <!-- 3 Metode Pemulihan -->
        <div class="bg-slate-800 border border-slate-700 p-6 sm:p-8 rounded-3xl shadow-xl space-y-6">
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <span>🛠️</span> Pilih Salah Satu Metode Pemulihan untuk Server Lokal
            </h2>

            <!-- METODE 1: 1-Klik Browser -->
            <div class="bg-slate-900/80 border border-slate-700/80 p-5 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-emerald-400 text-base">Metode 1: Eksekusi 1-Klik dari File di Server</h3>
                    <span class="text-xs bg-emerald-500/20 text-emerald-300 font-mono px-2 py-0.5 rounded">Paling Praktis</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Salin file <code class="text-amber-300 font-mono">pembdahub_poin_dan_nilai_bersih.sql.gz</code> (10 MB) dari laptop Anda ke server lokal (taruh di folder project ini atau folder public).
                </p>

                <?php if ($foundFile): ?>
                    <div class="p-3 bg-emerald-950/40 border border-emerald-500/40 rounded-xl text-xs text-emerald-300 flex items-center justify-between">
                        <span>✅ File terdeteksi di server: <strong><?= htmlspecialchars($foundFile) ?></strong> (<?= round(filesize($foundFile)/1024/1024, 2) ?> MB)</span>
                    </div>
                    <div class="pt-1">
                        <a href="?secret=<?= htmlspecialchars($_GET['secret']) ?>&action=restore_clean" 
                           onclick="return confirm('Mulai injeksi seluruh poin dan nilai ke database server lokal?');"
                           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-black px-6 py-3 rounded-xl text-sm shadow-lg shadow-emerald-600/30 transition">
                            <span>⚡ EKSEKUSI PEMULIHAN SEKARANG (1-KLIK)</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-amber-950/40 border border-amber-500/40 rounded-xl text-xs text-amber-300">
                        ⚠️ File belum ditemukan di lokasi otomatis server. Silakan salin berkas <code class="font-bold">pembdahub_poin_dan_nilai_bersih.sql.gz</code> ke folder: <code class="font-bold"><?= __DIR__ ?></code> atau gunakan Metode 2/3 di bawah.
                    </div>
                <?php endif; ?>
            </div>

            <!-- METODE 2: Upload Langsung via Browser -->
            <div class="bg-slate-900/80 border border-slate-700/80 p-5 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sky-400 text-base">Metode 2: Upload File Bersih Langsung dari Browser Ini</h3>
                    <span class="text-xs bg-sky-500/20 text-sky-300 font-mono px-2 py-0.5 rounded">Ukuran Hanya 10 MB</span>
                </div>
                <p class="text-xs text-slate-300">
                    Pilih file <code class="text-amber-300 font-mono">D:\dump\pembdahub_poin_dan_nilai_bersih.sql.gz</code> dari laptop Anda, lalu klik Upload & Pulihkan.
                </p>
                <form action="?secret=<?= htmlspecialchars($_GET['secret']) ?>" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                    <input type="file" name="dump_file" accept=".sql.gz,.sql" class="text-xs text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-700 file:text-white hover:file:bg-slate-600 cursor-pointer">
                    <button type="submit" class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-5 py-2 rounded-xl text-xs shadow transition">
                        📤 Upload & Pulihkan Langsung
                    </button>
                </form>
            </div>

            <!-- METODE 3: Lewat Terminal / SSH Ubuntu Server -->
            <div class="bg-slate-900/80 border border-slate-700/80 p-5 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-indigo-400 text-base">Metode 3: Jalankan via Terminal / SSH di Ubuntu Server</h3>
                    <span class="text-xs bg-indigo-500/20 text-indigo-300 font-mono px-2 py-0.5 rounded">Paling Cepat (15 Detik)</span>
                </div>
                <p class="text-xs text-slate-300">
                    Jika Anda memiliki akses terminal di server lokal (SSH / langsung di monitor server), copy file <code class="text-amber-300">pembdahub_poin_dan_nilai_bersih.sql.gz</code> ke server, lalu jalankan satu baris perintah ini:
                </p>
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 font-mono text-xs text-emerald-300 overflow-x-auto select-all">
                    zcat pembdahub_poin_dan_nilai_bersih.sql.gz | mysql -u <?= htmlspecialchars($dbUser) ?> <?= !empty($dbPass) ? '-p' : '' ?> <?= htmlspecialchars($dbName) ?>
                </div>
                <p class="text-[11px] text-slate-400">
                    *Catatan: Ganti nama database jika berbeda. Perintah di atas akan selesai dalam waktu sekitar 15-20 detik tanpa timeout.
                </p>
            </div>
        </div>

        <!-- Top Users Leaderboard Preview -->
        <?php if (!empty($topUsers)): ?>
        <div class="bg-slate-800 border border-slate-700 rounded-3xl overflow-hidden shadow-xl">
            <div class="p-5 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-black text-white text-base flex items-center gap-2">
                    <span>🏆</span> 5 Peringkat Poin Teratas di Database Server Ini
                </h3>
                <span class="text-xs text-slate-400">Live dari tabel reputations</span>
            </div>
            <div class="p-4 divide-y divide-slate-700/60">
                <?php foreach ($topUsers as $i => $u): ?>
                    <div class="py-3 flex items-center justify-between text-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg <?= $i === 0 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-slate-700 text-slate-300' ?> flex items-center justify-center font-bold text-xs">#<?= $i+1 ?></span>
                            <div>
                                <strong class="text-white"><?= htmlspecialchars($u['name']) ?></strong>
                                <span class="text-xs text-slate-400 ml-2">(<?= htmlspecialchars($u['level_name']) ?>)</span>
                            </div>
                        </div>
                        <span class="font-black text-emerald-400 font-mono"><?= number_format($u['total_points']) ?> pts</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="text-center text-xs text-slate-500 py-4">
            PembdaHUB Local Server Recovery Tool · Perguruan Pembda Nias
        </div>
    </div>
</body>
</html>
