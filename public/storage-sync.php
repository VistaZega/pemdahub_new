<?php
/**
 * PembdaHUB — Remote Storage Sync & Export Tool (Standalone PHP)
 * 
 * Script ini mengelola sinkronisasi folder storage/app/public antara
 * Server Production (Hostinger) dan Server Lokal (Sekolah / Laragon).
 * 
 * Fitur:
 *   1. Preview status & ukuran per folder (HTML GUI & JSON)
 *   2. Download ZIP per folder (misal: lms/materials, photos, pkl_logs)
 *   3. Generate manifest file (path, size, mtime, md5)
 *   4. Pack missing files: kemas hanya file-file tertentu yang diminta client
 *   5. Download single file langsung
 *   6. Upload & ekstrak ZIP ke storage (Push dari lokal)
 * 
 * Akses Browser:
 *   https://perguruanpembda.com/storage-sync.php?secret=pembda2026storage
 */

// Support CLI invocation args: php storage-sync.php secret=... action=...
if (php_sapi_name() === 'cli' && !empty($argv)) {
    foreach ($argv as $arg) {
        if (str_contains($arg, '=')) {
            [$k, $v] = explode('=', $arg, 2);
            $_REQUEST[$k] = $v;
            $_GET[$k] = $v;
        }
    }
}

// ══════════════════════════════════════════════════════
// 1. KEAMANAN & OTENTIKASI
// ══════════════════════════════════════════════════════
$VALID_SECRETS = ['pembda2026storage', 'pembda2026export', 'pembda99'];

$secret = $_REQUEST['secret'] ?? '';
if (!in_array($secret, $VALID_SECRETS, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak! Token otentikasi tidak valid.'
    ], JSON_PRETTY_PRINT);
    exit;
}

// Optimization for LiteSpeed & large transfers
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set('memory_limit', '512M');
header('X-LiteSpeed-NoAbort: 1');

// ══════════════════════════════════════════════════════
// 2. DETEKSI LOKASI STORAGE/APP/PUBLIC
// ══════════════════════════════════════════════════════
$possiblePaths = [
    __DIR__ . '/../storage/app/public',
    '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/storage/app/public',
    dirname(__DIR__) . '/storage/app/public',
    __DIR__ . '/storage',
];

$storagePath = null;
foreach ($possiblePaths as $p) {
    if (is_dir($p)) {
        $storagePath = realpath($p);
        break;
    }
}

if (!$storagePath) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Direktori storage/app/public tidak ditemukan di server.'
    ], JSON_PRETTY_PRINT);
    exit;
}

// Normalisasi separator path
$storagePath = str_replace('\\', '/', $storagePath);

// Helper fungsi validasi keamanan path (mencegah directory traversal)
function isPathWithinStorage(string $fullPath, string $baseStorage): bool {
    $normalizedBase = rtrim(str_replace('\\', '/', $baseStorage), '/') . '/';
    $normalizedTarget = str_replace('\\', '/', $fullPath);
    
    // Resolve kanonikal
    $real = realpath($fullPath);
    if ($real !== false) {
        $normalizedTarget = str_replace('\\', '/', $real);
    }
    
    return str_starts_with($normalizedTarget . (is_dir($fullPath) ? '/' : ''), $normalizedBase);
}

// ══════════════════════════════════════════════════════
// 3. ACTION ROUTER
// ══════════════════════════════════════════════════════
$action = $_REQUEST['action'] ?? 'preview';

// Format ukuran file
function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// ──────────────────────────────────────────────────────
// ACTION: PREVIEW / STATUS (HTML GUI & JSON)
// ──────────────────────────────────────────────────────
if ($action === 'preview' || $action === 'status') {
    $format = $_GET['format'] ?? (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json') ? 'json' : 'html');
    
    // Scan direktori teratas di storage
    $entries = scandir($storagePath);
    $folders = [];
    $totalStorageFiles = 0;
    $totalStorageBytes = 0;
    
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $fullPath = $storagePath . '/' . $entry;
        
        if (is_dir($fullPath)) {
            $folderFiles = 0;
            $folderBytes = 0;
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $folderFiles++;
                    $folderBytes += $file->getSize();
                }
            }
            
            $folders[$entry] = [
                'name'        => $entry,
                'files_count' => $folderFiles,
                'bytes'       => $folderBytes,
                'size_human'  => formatBytes($folderBytes),
                'zip_url'     => "?secret={$secret}&action=zip&folder=" . urlencode($entry),
                'manifest_url'=> "?secret={$secret}&action=manifest&folder=" . urlencode($entry),
            ];
            
            $totalStorageFiles += $folderFiles;
            $totalStorageBytes += $folderBytes;
        } elseif (is_file($fullPath)) {
            $totalStorageFiles++;
            $totalStorageBytes += filesize($fullPath);
        }
    }
    
    // Urutkan folder berdasarkan ukuran terbesar
    uasort($folders, fn($a, $b) => $b['bytes'] <=> $a['bytes']);
    
    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'server' => $_SERVER['HTTP_HOST'] ?? 'unknown',
            'storage_path' => $storagePath,
            'total_files' => $totalStorageFiles,
            'total_bytes' => $totalStorageBytes,
            'total_size_human' => formatBytes($totalStorageBytes),
            'folders' => $folders,
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $isProduction = in_array(strtolower($_SERVER['HTTP_HOST'] ?? ''), ['perguruanpembda.com', 'www.perguruanpembda.com'], true);
    
    // Render Modern HTML GUI
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PembdaHUB — Storage Sync Manager</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
        </style>
    </head>
    <body class="bg-slate-900 text-slate-100 min-h-screen p-4 sm:p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 mb-8 shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-block w-3 h-3 rounded-full <?= $isProduction ? 'bg-emerald-500 animate-pulse' : 'bg-blue-500 animate-pulse' ?>"></span>
                            <span class="text-xs font-semibold tracking-wider <?= $isProduction ? 'text-emerald-400' : 'text-blue-400' ?> uppercase">
                                <?= $isProduction ? 'Production Storage Server' : 'Local / School Server (Ubuntu / Laragon)' ?>
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Penyimpanan Media & Dokumen PembdaHUB</h1>
                        <p class="text-sm text-slate-400 mt-1">
                            Direktori fisik: <code class="text-emerald-300 font-mono text-xs bg-slate-900/60 px-2 py-1 rounded"><?= htmlspecialchars($storagePath) ?></code>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="?secret=<?= urlencode($secret) ?>&action=zip&folder=all" 
                           onclick="return confirm('Download seluruh file sekaligus mungkin membutuhkan waktu beberapa menit. Lanjutkan?');"
                           class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-blue-500/20 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download Semua ZIP (Full)
                        </a>
                    </div>
                </div>

                <!-- Stats Counters -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-700/60">
                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/40">
                        <span class="text-xs text-slate-400 font-medium">Total Kapasitas Terpakai</span>
                        <div class="text-2xl font-bold text-emerald-400 mt-1"><?= formatBytes($totalStorageBytes) ?></div>
                    </div>
                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/40">
                        <span class="text-xs text-slate-400 font-medium">Total Berkas File</span>
                        <div class="text-2xl font-bold text-blue-400 mt-1"><?= number_format($totalStorageFiles, 0, ',', '.') ?> berkas</div>
                    </div>
                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/40 col-span-2 sm:col-span-1">
                        <span class="text-xs text-slate-400 font-medium">Total Folder Kategori</span>
                        <div class="text-2xl font-bold text-amber-400 mt-1"><?= count($folders) ?> folder</div>
                    </div>
                </div>
            </div>

            <?php if (!$isProduction): ?>
            <!-- Tombol One-Click Pull di Server Lokal -->
            <div class="bg-gradient-to-r from-emerald-950/80 to-teal-950/80 border border-emerald-500/50 rounded-2xl p-6 mb-8 text-sm shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Sinkronisasi Otomatis Server Lokal</span>
                        </div>
                        <h2 class="text-xl font-bold text-white">Tarik Berkas dari Production (Hostinger) ke Server Ini</h2>
                        <p class="text-emerald-200/90 text-xs sm:text-sm mt-1">
                            Klik tombol di samping untuk mengunduh otomatis berkas materi LMS, foto, dan dokumen dari <code class="text-emerald-300 font-mono">perguruanpembda.com</code> langsung ke server ini via browser.
                        </p>
                    </div>
                    <a href="?secret=<?= urlencode($secret) ?>&action=pull_from_prod" 
                       onclick="return confirm('Mulai sinkronisasi berkas dari Production ke server ini? Proses akan mengunduh dan mengekstrak berkas yang belum ada.');"
                       class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3.5 rounded-xl text-sm shadow-lg shadow-emerald-500/30 transition-all shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Tarik Berkas dari Production Sekarang
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Petunjuk CLI -->
            <div class="bg-blue-950/40 border border-blue-800/50 rounded-2xl p-5 mb-8 text-sm text-blue-200">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <strong class="text-blue-300 font-semibold block mb-1">Perintah Terminal / SSH Server Ubuntu:</strong>
                        <p class="text-blue-200/90 text-xs sm:text-sm">
                            Di Server Ubuntu sekolah, Anda juga bisa menjalankan sinkronisasi langsung via terminal:
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2 font-mono text-xs bg-slate-950/80 px-3 py-2 rounded-lg text-emerald-400 border border-blue-900/50">
                            <code>cd /var/www/pembdahub</code>
                            <span class="text-slate-500">&amp;&amp;</span>
                            <code>php sync-storage.php --pull --missing-only</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid Folder List -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-5 border-b border-slate-700/80 flex items-center justify-between">
                    <h2 class="font-bold text-white text-lg">Daftar Folder & Kategori Penyimpanan</h2>
                    <span class="text-xs text-slate-400">Download per modul disarankan agar lebih cepat & stabil</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/60 text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-700">
                                <th class="py-3 px-5">Nama Folder</th>
                                <th class="py-3 px-5">Fungsi / Konten</th>
                                <th class="py-3 px-5 text-center">Jumlah File</th>
                                <th class="py-3 px-5 text-right">Ukuran</th>
                                <th class="py-3 px-5 text-center">Aksi Unduh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60 text-sm">
                            <?php foreach ($folders as $f): 
                                $badgeColor = match($f['name']) {
                                    'lms', 'lms_materials', 'materials' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
                                    'photos' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                    'pkl_logs', 'pkl_monitorings', 'pkl_perangkat' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                    'cbt' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                    default => 'bg-slate-700/50 text-slate-300 border-slate-600/30'
                                };
                                $desc = match($f['name']) {
                                    'lms', 'lms_materials', 'materials' => 'Materi, PPT, Video & Modul Ajar LMS Guru',
                                    'photos' => 'Foto profil guru, siswa, pegawai & header',
                                    'pkl_logs' => 'Dokumentasi foto kegiatan PKL DUDI siswa',
                                    'pkl_monitorings' => 'Foto monitoring kunjungan guru ke tempat PKL',
                                    'pkl_perangkat' => 'Dokumen perangkat & instrumen PKL',
                                    'cbt' => 'Gambar soal, stimulus & ujian CBT',
                                    'achievements' => 'Sertifikat & dokumentasi prestasi siswa',
                                    'counseling_attachments' => 'Lampiran catatan konseling / BK',
                                    'attendance_attachments' => 'Bukti surat sakit / izin absensi siswa',
                                    'documents' => 'Dokumen resmi, SK, format rapor & panduan',
                                    'forum' => 'Gambar & lampiran forum diskusi',
                                    default => 'File penyimpanan aplikasi'
                                };
                            ?>
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="py-3.5 px-5 font-mono font-medium text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    <span class="text-emerald-300"><?= htmlspecialchars($f['name']) ?>/</span>
                                </td>
                                <td class="py-3.5 px-5 text-slate-300 text-xs sm:text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $badgeColor ?>">
                                        <?= htmlspecialchars($desc) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-center font-mono text-slate-300">
                                    <?= number_format($f['files_count'], 0, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-5 text-right font-mono font-semibold text-emerald-400">
                                    <?= $f['size_human'] ?>
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= $f['zip_url'] ?>" 
                                           class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download ZIP
                                        </a>
                                        <a href="<?= $f['manifest_url'] ?>" target="_blank"
                                           class="inline-flex items-center gap-1 text-slate-400 hover:text-slate-200 text-xs px-2 py-1.5 rounded hover:bg-slate-700/50" title="Lihat Manifest JSON">
                                            Manifest
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <p class="text-center text-xs text-slate-500 mt-8">
                PembdaHUB Storage Synchronizer &bull; SMK, SMA, SMP Swasta Pembda Nias
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ──────────────────────────────────────────────────────
// ACTION: MANIFEST (JSON LIST OF FILES & HASHES)
// ──────────────────────────────────────────────────────
if ($action === 'manifest') {
    header('Content-Type: application/json; charset=utf-8');
    
    $folder = $_GET['folder'] ?? '';
    $folder = trim(str_replace(['..', '\\'], ['', '/'], $folder), '/');
    
    $targetDir = ($folder === 'all' || empty($folder)) ? $storagePath : $storagePath . '/' . $folder;
    
    if (!is_dir($targetDir) || !isPathWithinStorage($targetDir, $storagePath)) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Folder '{$folder}' tidak ditemukan."]);
        exit;
    }
    
    $withMd5 = isset($_GET['md5']) && $_GET['md5'] === '1';
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    $manifest = [];
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $realPath = str_replace('\\', '/', $file->getRealPath());
            $relPath = substr($realPath, strlen($storagePath) + 1);
            
            $item = [
                'path'  => $relPath,
                'size'  => $file->getSize(),
                'mtime' => $file->getMTime(),
            ];
            
            if ($withMd5 && $file->getSize() < 50 * 1024 * 1024) { // only hash files < 50MB
                $item['md5'] = md5_file($realPath);
            }
            
            $manifest[] = $item;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'folder' => $folder ?: 'all',
        'count'  => count($manifest),
        'files'  => $manifest
    ]);
    exit;
}

// ──────────────────────────────────────────────────────
// ACTION: ZIP (DOWNLOAD FOLDER AS ZIP)
// ──────────────────────────────────────────────────────
if ($action === 'zip') {
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        die('Ekstensi PHP ZipArchive tidak aktif di server.');
    }
    
    $folder = $_GET['folder'] ?? 'all';
    $folder = trim(str_replace(['..', '\\'], ['', '/'], $folder), '/');
    
    $targetDir = ($folder === 'all' || empty($folder)) ? $storagePath : $storagePath . '/' . $folder;
    
    if (!is_dir($targetDir) || !isPathWithinStorage($targetDir, $storagePath)) {
        http_response_code(404);
        die("Folder '{$folder}' tidak ditemukan di storage.");
    }
    
    $cleanFolderName = ($folder === 'all' || empty($folder)) ? 'all' : preg_replace('/[^a-zA-Z0-9_\-]/', '_', $folder);
    $tempZip = sys_get_temp_dir() . '/pembda_storage_' . $cleanFolderName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        die('Gagal menginisialisasi file ZIP sementara.');
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    $addedCount = 0;
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $realPath = str_replace('\\', '/', $file->getRealPath());
            $relPath = substr($realPath, strlen($storagePath) + 1);
            $zip->addFile($realPath, $relPath);
            $addedCount++;
        }
    }
    
    $zip->close();
    
    if (!file_exists($tempZip) || filesize($tempZip) === 0) {
        if (file_exists($tempZip)) @unlink($tempZip);
        http_response_code(404);
        die("Tidak ada file ditemukan dalam folder '{$folder}'.");
    }
    
    $filename = "pembdahub_storage_{$cleanFolderName}_" . date('Ymd_His') . ".zip";
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempZip));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($tempZip);
    @unlink($tempZip);
    exit;
}

// ──────────────────────────────────────────────────────
// ACTION: PACK_MISSING (ZIP ONLY SPECIFIC REQUESTED FILES)
// ──────────────────────────────────────────────────────
if ($action === 'pack_missing' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        die('Ekstensi PHP ZipArchive tidak aktif.');
    }
    
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    if (!isset($data['files']) || !is_array($data['files'])) {
        http_response_code(400);
        die('Daftar file tidak valid.');
    }
    
    $requestedFiles = $data['files'];
    $tempZip = sys_get_temp_dir() . '/pembda_missing_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        die('Gagal membuat file ZIP.');
    }
    
    $packedCount = 0;
    foreach ($requestedFiles as $relPath) {
        $relPath = trim(str_replace(['..', '\\'], ['', '/'], $relPath), '/');
        $fullPath = $storagePath . '/' . $relPath;
        
        if (is_file($fullPath) && isPathWithinStorage($fullPath, $storagePath)) {
            $zip->addFile($fullPath, $relPath);
            $packedCount++;
        }
    }
    
    $zip->close();
    
    if ($packedCount === 0 || !file_exists($tempZip) || filesize($tempZip) === 0) {
        if (file_exists($tempZip)) @unlink($tempZip);
        http_response_code(404);
        die('Tidak ada berkas yang cocok ditemukan untuk dikemas.');
    }
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="pembda_missing_' . date('Ymd_His') . '.zip"');
    header('Content-Length: ' . filesize($tempZip));
    header('X-Packed-Count: ' . $packedCount);
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($tempZip);
    @unlink($tempZip);
    exit;
}

// ──────────────────────────────────────────────────────
// ACTION: DOWNLOAD_FILE (STREAM SINGLE FILE)
// ──────────────────────────────────────────────────────
if ($action === 'download_file') {
    $file = $_GET['file'] ?? '';
    $file = trim(str_replace(['..', '\\'], ['', '/'], $file), '/');
    $fullPath = $storagePath . '/' . $file;
    
    if (empty($file) || !is_file($fullPath) || !isPathWithinStorage($fullPath, $storagePath)) {
        http_response_code(404);
        die('File tidak ditemukan di storage.');
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fullPath) ?: 'application/octet-stream';
    finfo_close($finfo);
    
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($fullPath);
    exit;
}

// ──────────────────────────────────────────────────────
// ACTION: UPLOAD_ZIP (PUSH DARI LOCAL KE PRODUCTION)
// ──────────────────────────────────────────────────────
if ($action === 'upload_zip' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'PHP ZipArchive tidak tersedia di server.']);
        exit;
    }
    
    if (!isset($_FILES['zip_file']) || $_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'File ZIP gagal diunggah atau tidak ada berkas yang dikirim.']);
        exit;
    }
    
    $uploadedFile = $_FILES['zip_file']['tmp_name'];
    $zip = new ZipArchive();
    
    if ($zip->open($uploadedFile) !== true) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'File bukan arsip ZIP yang valid.']);
        exit;
    }
    
    $extracted = 0;
    $errors = [];
    
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = $zip->getNameIndex($i);
        $cleanEntry = str_replace('\\', '/', $entryName);
        
        // Anti zip-slip: lewati path yang mengandung ../ atau root /
        if (str_contains($cleanEntry, '../') || str_starts_with($cleanEntry, '/')) {
            $errors[] = "Dilewati (path tidak aman): {$entryName}";
            continue;
        }
        
        $targetFile = $storagePath . '/' . $cleanEntry;
        
        // Jika entri adalah direktori
        if (str_ends_with($cleanEntry, '/')) {
            if (!is_dir($targetFile)) {
                @mkdir($targetFile, 0755, true);
            }
            continue;
        }
        
        // Pastikan direktori induk ada
        $parentDir = dirname($targetFile);
        if (!is_dir($parentDir)) {
            @mkdir($parentDir, 0755, true);
        }
        
        // Ekstrak file
        $content = $zip->getFromIndex($i);
        if ($content !== false) {
            file_put_contents($targetFile, $content);
            $extracted++;
        }
    }
    
    $zip->close();
    @unlink($uploadedFile);
    
    echo json_encode([
        'status' => 'success',
        'message' => "Berhasil mengekstrak {$extracted} file ke storage.",
        'extracted_count' => $extracted,
        'skipped_errors'  => $errors,
    ], JSON_PRETTY_PRINT);
    exit;
}

// ──────────────────────────────────────────────────────
// ACTION: PULL_FROM_PROD (ONE-CLICK TARIK DARI HOSTINGER KE SERVER INI)
// ──────────────────────────────────────────────────────
if ($action === 'pull_from_prod') {
    @ini_set('max_execution_time', '600');
    @set_time_limit(600);
    @ini_set('output_buffering', 'off');
    @ini_set('zlib.output_compression', false);
    @ini_set('implicit_flush', true);
    while (@ob_end_flush());
    ob_implicit_flush(true);
    
    header('Content-Type: text/html; charset=utf-8');
    header('X-Accel-Buffering: no');
    
    echo "<!DOCTYPE html><html><head><title>Sinkronisasi Storage dari Production</title>";
    echo "<style>body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:24px;line-height:1.6;font-size:14px;}";
    echo ".ok{color:#3fb950;font-weight:bold;} .warn{color:#d29922;} .err{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}";
    echo "pre{background:#161b22;border:1px solid #30363d;padding:16px;border-radius:8px;overflow-x:auto;white-space:pre-wrap;}";
    echo "h1{color:#58a6ff;border-bottom:1px solid #30363d;padding-bottom:10px;} h2{color:#79c0ff;margin-top:24px;}";
    echo "</style></head><body>";
    echo "<h1>📥 PembdaHUB — Sinkronisasi Storage dari Production</h1>";
    echo "<p>Menghubungi production (perguruanpembda.com) untuk menarik berkas materi dan media...</p>";
    flush();
    
    $prodUrl = $_GET['prod_url'] ?? 'https://perguruanpembda.com/storage-sync.php';
    $prodSecret = $_GET['prod_secret'] ?? 'pembda2026storage';
    
    // 1. Ambil manifest
    echo "<h2>▶ 1. Mengambil Manifest Berkas dari Production</h2><pre>";
    flush();
    $manifestUrl = "{$prodUrl}?secret={$prodSecret}&action=manifest&folder=all";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $manifestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $rawRes = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || empty($rawRes)) {
        echo "<span class='err'>✖ Gagal mengambil manifest dari production (HTTP {$httpCode}). Pastikan perguruanpembda.com/storage-sync.php sudah aktif.</span></pre>";
        echo "<p><a href='?secret=" . htmlspecialchars($secret) . "' style='color:#58a6ff;'>&laquo; Kembali</a></p></body></html>";
        exit;
    }
    
    $manifest = json_decode($rawRes, true);
    if (!isset($manifest['files']) || !is_array($manifest['files'])) {
        echo "<span class='err'>✖ Respon manifest tidak valid dari production.</span></pre>";
        echo "<p><a href='?secret=" . htmlspecialchars($secret) . "' style='color:#58a6ff;'>&laquo; Kembali</a></p></body></html>";
        exit;
    }
    
    echo "<span class='ok'>✔ Berhasil membaca manifest: total " . count($manifest['files']) . " berkas di production.</span></pre>";
    flush();
    
    // 2. Cek berkas yang belum ada di server ini
    echo "<h2>▶ 2. Memeriksa Berkas Fisik Lokal di Server Ini</h2><pre>";
    flush();
    
    $missingFiles = [];
    $missingBytes = 0;
    foreach ($manifest['files'] as $rf) {
        $relPath = $rf['path'];
        $localPath = $storagePath . '/' . $relPath;
        if (!file_exists($localPath) || filesize($localPath) !== $rf['size']) {
            $missingFiles[] = $relPath;
            $missingBytes += $rf['size'];
        }
    }
    
    if (empty($missingFiles)) {
        echo "<span class='ok'>✔ Seluruh berkas di server ini sudah 100% lengkap dan sesuai dengan production!</span></pre>";
        echo "<p><a href='?secret=" . htmlspecialchars($secret) . "' style='color:#58a6ff;'>&laquo; Kembali ke Dashboard</a></p></body></html>";
        exit;
    }
    
    echo "<span class='warn'>Ditemukan " . count($missingFiles) . " berkas yang belum ada atau berbeda ukuran (" . formatBytes($missingBytes) . ").</span></pre>";
    flush();
    
    // 3. Unduh dan Ekstrak
    echo "<h2>▶ 3. Mengunduh dan Mengekstrak Berkas yang Hilang</h2><pre>";
    flush();
    
    $batchSize = 200;
    $batches = array_chunk($missingFiles, $batchSize);
    $totalBatches = count($batches);
    $totalExtracted = 0;
    
    foreach ($batches as $idx => $batch) {
        $batchNum = $idx + 1;
        echo "<span class='info'>Memproses Batch {$batchNum}/{$totalBatches} (" . count($batch) . " berkas)...</span>\n";
        flush();
        
        $tempZip = sys_get_temp_dir() . '/pembda_pull_' . time() . '_' . $batchNum . '.zip';
        $packUrl = "{$prodUrl}?secret={$prodSecret}&action=pack_missing";
        
        $ch = curl_init();
        $fh = fopen($tempZip, 'w+');
        curl_setopt($ch, CURLOPT_URL, $packUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['files' => $batch]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fh);
        
        if ($code === 200 && file_exists($tempZip) && filesize($tempZip) > 0) {
            $zip = new ZipArchive();
            if ($zip->open($tempZip) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->getNameIndex($i);
                    $clean = str_replace('\\', '/', $entry);
                    if (str_contains($clean, '../') || str_starts_with($clean, '/')) continue;
                    
                    $dest = $storagePath . '/' . $clean;
                    if (str_ends_with($clean, '/')) {
                        if (!is_dir($dest)) @mkdir($dest, 0755, true);
                        continue;
                    }
                    $p = dirname($dest);
                    if (!is_dir($p)) @mkdir($p, 0755, true);
                    
                    $content = $zip->getFromIndex($i);
                    if ($content !== false) {
                        file_put_contents($dest, $content);
                        $totalExtracted++;
                    }
                }
                $zip->close();
                echo "<span class='ok'>✔ Batch {$batchNum} berhasil diekstrak.</span>\n";
            } else {
                echo "<span class='err'>✖ Gagal membuka file ZIP batch {$batchNum}.</span>\n";
            }
            @unlink($tempZip);
        } else {
            echo "<span class='warn'>⚠ Batch {$batchNum} gagal dikemas otomatis, mencoba unduh file individu...</span>\n";
            @unlink($tempZip);
            // Fallback unduh satu per satu
            foreach ($batch as $f) {
                $fileUrl = "{$prodUrl}?secret={$prodSecret}&action=download_file&file=" . urlencode($f);
                $singleDest = $storagePath . '/' . $f;
                $p = dirname($singleDest);
                if (!is_dir($p)) @mkdir($p, 0755, true);
                
                $ch = curl_init();
                $fh = fopen($singleDest, 'w+');
                curl_setopt($ch, CURLOPT_URL, $fileUrl);
                curl_setopt($ch, CURLOPT_FILE, $fh);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_exec($ch);
                $sCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                fclose($fh);
                if ($sCode === 200 && file_exists($singleDest) && filesize($singleDest) > 0) {
                    $totalExtracted++;
                }
            }
            echo "<span class='ok'>✔ Batch {$batchNum} berhasil diunduh individu.</span>\n";
        }
        flush();
    }
    
    echo "\n<span class='ok'>🎉 SINKRONISASI SELESAI! Total {$totalExtracted} berkas baru berhasil diunduh dan disimpan ke server ini.</span>";
    echo "</pre>";
    echo "<p><a href='?secret=" . htmlspecialchars($secret) . "' style='display:inline-block;background:#238636;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:16px;'>&laquo; Kembali ke Dashboard Storage</a></p>";
    echo "</body></html>";
    exit;
}

http_response_code(400);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali.']);
