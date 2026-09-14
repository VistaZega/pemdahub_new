<?php
/**
 * PembdaHUB — Storage Synchronization CLI Tool
 * 
 * Tool ini menyinkronkan file upload (materi LMS, foto profil, dokumen PKL, dll)
 * antara Server Production (Hostinger) dan Server Lokal (Laragon / PC Sekolah).
 * 
 * Penggunaan via Terminal/CMD:
 *   php sync-storage.php                           → Menu Interaktif
 *   php sync-storage.php --preview                 → Periksa & bandingkan status storage
 *   php sync-storage.php --pull --missing-only     → Tarik hanya file yang belum ada di lokal (Rekomendasi!)
 *   php sync-storage.php --pull --folder=lms       → Tarik materi LMS
 *   php sync-storage.php --pull --folder=photos    → Tarik foto pengguna
 *   php sync-storage.php --pull --all              → Tarik seluruh isi storage
 *   php sync-storage.php --push --folder=lms       → Unggah materi lokal ke production
 * 
 * Atau klik ganda: sync-storage.bat
 */

// ══════════════════════════════════════════════════════
// 1. KONFIGURASI
// ══════════════════════════════════════════════════════
$config = [
    'remote_url'    => 'https://perguruanpembda.com/storage-sync.php',
    'remote_secret' => 'pembda2026storage',
    'local_storage' => __DIR__ . '/storage/app/public',
    'temp_dir'      => __DIR__ . '/storage/temp_sync',
];

// Deteksi konfigurasi dari .env lokal jika ada
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim(trim($val), '"\'');
        if ($key === 'STORAGE_SYNC_URL') $config['remote_url'] = $val;
        if ($key === 'STORAGE_SYNC_SECRET') $config['remote_secret'] = $val;
    }
}

// ══════════════════════════════════════════════════════
// 2. CLI FORMATTING HELPERS
// ══════════════════════════════════════════════════════
function isWindowsAnsiSupported(): bool {
    return DIRECTORY_SEPARATOR === '\\' ? (function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT)) : true;
}

$useColor = isWindowsAnsiSupported();

function c(string $text, string $colorCode): string {
    global $useColor;
    return $useColor ? "\033[{$colorCode}m{$text}\033[0m" : $text;
}

function info(string $msg): void { echo c("[INFO] ", "36") . "{$msg}\n"; }
function success(string $msg): void { echo c("[SUKSES] ", "32") . "{$msg}\n"; }
function warn(string $msg): void { echo c("[PERINGATAN] ", "33") . "{$msg}\n"; }
function error(string $msg): void { echo c("[ERROR] ", "31") . "{$msg}\n"; }
function step(string $msg): void {
    echo "\n" . c("═══════════════════════════════════════════════════════════════", "35") . "\n";
    echo c("  {$msg}", "1;37") . "\n";
    echo c("═══════════════════════════════════════════════════════════════", "35") . "\n";
}

function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// ══════════════════════════════════════════════════════
// 3. PERSYARATAN SISTEM
// ══════════════════════════════════════════════════════
if (!class_exists('ZipArchive')) {
    error("Ekstensi PHP ZipArchive tidak aktif. Aktifkan 'extension=zip' di php.ini.");
    exit(1);
}

if (!function_exists('curl_init')) {
    error("Ekstensi PHP cURL tidak aktif. Aktifkan 'extension=curl' di php.ini.");
    exit(1);
}

// Pastikan direktori storage lokal ada
if (!is_dir($config['local_storage'])) {
    @mkdir($config['local_storage'], 0755, true);
}
if (!is_dir($config['temp_dir'])) {
    @mkdir($config['temp_dir'], 0755, true);
}

// ══════════════════════════════════════════════════════
// 4. PARSER ARGUMEN CLI
// ══════════════════════════════════════════════════════
$args = [];
foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--')) {
        $parts = explode('=', substr($arg, 2), 2);
        $args[$parts[0]] = $parts[1] ?? true;
    }
}

if (isset($args['remote-url'])) $config['remote_url'] = $args['remote-url'];
if (isset($args['secret'])) $config['remote_secret'] = $args['secret'];

// ══════════════════════════════════════════════════════
// 5. CORE HELPER FUNCTIONS
// ══════════════════════════════════════════════════════

/**
 * Panggil API storage production
 */
function callRemoteApi(string $url, string $method = 'GET', $postData = null, ?string $saveToFile = null): array {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 600); // 10 menit
    curl_setopt($ch, CURLOPT_USERAGENT, 'PembdaHUB-StorageSync-Client/1.0');
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($postData) && isset($postData['is_file'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData['data']);
        } elseif (is_array($postData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    
    // Jika streaming langsung ke file
    $fileHandle = null;
    if ($saveToFile) {
        $fileHandle = fopen($saveToFile, 'w+');
        curl_setopt($ch, CURLOPT_FILE, $fileHandle);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    if ($fileHandle) {
        fclose($fileHandle);
    }
    
    return [
        'code'     => $httpCode,
        'response' => $response,
        'error'    => $curlErr,
    ];
}

/**
 * Scan lokal storage untuk menghitung berkas & ukuran per folder
 */
function scanLocalStorage(string $basePath): array {
    $folders = [];
    $totalFiles = 0;
    $totalBytes = 0;
    
    if (!is_dir($basePath)) return ['folders' => [], 'total_files' => 0, 'total_bytes' => 0];
    
    $entries = scandir($basePath);
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..' || $entry === '.gitignore') continue;
        $fullPath = $basePath . '/' . $entry;
        
        if (is_dir($fullPath)) {
            $fCount = 0;
            $fBytes = 0;
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($it as $f) {
                if ($f->isFile()) {
                    $fCount++;
                    $fBytes += $f->getSize();
                }
            }
            $folders[$entry] = [
                'files_count' => $fCount,
                'bytes'       => $fBytes,
                'size_human'  => formatBytes($fBytes),
            ];
            $totalFiles += $fCount;
            $totalBytes += $fBytes;
        }
    }
    
    return [
        'folders'     => $folders,
        'total_files' => $totalFiles,
        'total_bytes' => $totalBytes,
    ];
}

/**
 * Ekstrak file ZIP ke direktori storage lokal
 */
function extractZipToStorage(string $zipFilePath, string $destStorage): int {
    $zip = new ZipArchive();
    if ($zip->open($zipFilePath) !== true) {
        error("Gagal membuka file ZIP hasil download: {$zipFilePath}");
        return 0;
    }
    
    $extracted = 0;
    $total = $zip->numFiles;
    info("Mengekstrak {$total} file ke storage lokal...");
    
    for ($i = 0; $i < $total; $i++) {
        $entryName = $zip->getNameIndex($i);
        $cleanEntry = str_replace('\\', '/', $entryName);
        
        // Anti zip-slip
        if (str_contains($cleanEntry, '../') || str_starts_with($cleanEntry, '/')) continue;
        
        $targetPath = $destStorage . '/' . $cleanEntry;
        
        if (str_ends_with($cleanEntry, '/')) {
            if (!is_dir($targetPath)) @mkdir($targetPath, 0755, true);
            continue;
        }
        
        $parent = dirname($targetPath);
        if (!is_dir($parent)) @mkdir($parent, 0755, true);
        
        $content = $zip->getFromIndex($i);
        if ($content !== false) {
            file_put_contents($targetPath, $content);
            $extracted++;
        }
        
        // Progress print setiap 50 file
        if ($extracted % 50 === 0 || $i === $total - 1) {
            echo "\r" . c("  Progress: ", "36") . "{$extracted}/{$total} berkas diekstrak...";
        }
    }
    echo "\n";
    $zip->close();
    return $extracted;
}

// ══════════════════════════════════════════════════════
// 6. EKSEKUSI PERINTAH
// ══════════════════════════════════════════════════════

/**
 * Perintah: PREVIEW (Bandingkan Production vs Local)
 */
function cmdPreview(array $config): void {
    step("MEMERIKSA PERBANDINGAN STORAGE (PRODUCTION VS LOKAL)");
    
    $url = $config['remote_url'] . '?secret=' . urlencode($config['remote_secret']) . '&action=status&format=json';
    info("Mengambil status dari production: {$config['remote_url']}...");
    
    $res = callRemoteApi($url);
    if ($res['code'] !== 200) {
        error("Gagal terhubung ke remote storage (HTTP {$res['code']}): " . ($res['error'] ?: $res['response']));
        return;
    }
    
    $remoteData = json_decode($res['response'], true);
    if (!$remoteData || ($remoteData['status'] ?? '') !== 'success') {
        error("Respon production tidak valid: " . substr($res['response'], 0, 200));
        return;
    }
    
    $localData = scanLocalStorage($config['local_storage']);
    
    echo "\n";
    printf("%-24s | %-18s | %-18s | %s\n", "Folder Kategori", "Production", "Lokal", "Status");
    echo str_repeat("─", 78) . "\n";
    
    $allFolderKeys = array_unique(array_merge(
        array_keys($remoteData['folders'] ?? []),
        array_keys($localData['folders'] ?? [])
    ));
    sort($allFolderKeys);
    
    foreach ($allFolderKeys as $folderName) {
        $rem = $remoteData['folders'][$folderName] ?? null;
        $loc = $localData['folders'][$folderName] ?? null;
        
        $remStr = $rem ? "{$rem['files_count']} fl ({$rem['size_human']})" : c("Kosong", "33");
        $locStr = $loc ? "{$loc['files_count']} fl ({$loc['size_human']})" : c("Belum ada", "31");
        
        $statusStr = c("Sama", "32");
        if (!$loc) {
            $statusStr = c("Perlu Ditarik", "31");
        } elseif ($rem && $rem['files_count'] > $loc['files_count']) {
            $diff = $rem['files_count'] - $loc['files_count'];
            $statusStr = c("+{$diff} baru di prod", "33");
        } elseif ($rem && $loc['files_count'] > $rem['files_count']) {
            $diff = $loc['files_count'] - $rem['files_count'];
            $statusStr = c("+{$diff} lokal baru", "36");
        }
        
        printf("%-24s | %-18s | %-18s | %s\n", $folderName, $remStr, $locStr, $statusStr);
    }
    
    echo str_repeat("─", 78) . "\n";
    printf("%-24s | %-18s | %-18s |\n", 
        "TOTAL", 
        "{$remoteData['total_files']} fl (" . formatBytes($remoteData['total_bytes']) . ")",
        "{$localData['total_files']} fl (" . formatBytes($localData['total_bytes']) . ")"
    );
    echo "\n";
}

/**
 * Perintah: PULL MISSING ONLY (Smart Diff)
 */
function cmdPullMissing(array $config): void {
    step("SMART PULL: HANYA UNDUH FILE YANG BELUM ADA DI LOKAL");
    
    // 1. Ambil manifest lengkap dari production
    $url = $config['remote_url'] . '?secret=' . urlencode($config['remote_secret']) . '&action=manifest&folder=all';
    info("Mengambil daftar manifest file dari server production...");
    
    $res = callRemoteApi($url);
    if ($res['code'] !== 200) {
        error("Gagal mengambil manifest dari remote: HTTP {$res['code']}");
        return;
    }
    
    $manifestData = json_decode($res['response'], true);
    if (!$manifestData || !isset($manifestData['files'])) {
        error("Respon manifest tidak valid.");
        return;
    }
    
    $remoteFiles = $manifestData['files'];
    info("Total file di production: " . count($remoteFiles) . " file.");
    
    // 2. Bandingkan dengan file fisik di storage lokal
    $missingFiles = [];
    $missingBytes = 0;
    
    foreach ($remoteFiles as $rf) {
        $relPath = $rf['path'];
        $localPath = $config['local_storage'] . '/' . $relPath;
        
        if (!file_exists($localPath) || filesize($localPath) !== $rf['size']) {
            $missingFiles[] = $relPath;
            $missingBytes += $rf['size'];
        }
    }
    
    if (empty($missingFiles)) {
        success("Seluruh berkas lokal sudah 100% lengkap dan sesuai dengan production!");
        return;
    }
    
    info("Ditemukan " . count($missingFiles) . " file yang belum ada/berbeda di lokal (" . formatBytes($missingBytes) . ").");
    
    // 3. Kemas dan unduh berkas yang hilang dalam batch jika terlalu banyak
    $batchSize = 200; // 200 file per batch agar stabil di shared hosting
    $batches = array_chunk($missingFiles, $batchSize);
    $totalBatches = count($batches);
    $totalExtracted = 0;
    
    info("Mengunduh dalam {$totalBatches} batch permintaan kemas ZIP...");
    
    foreach ($batches as $index => $batch) {
        $batchNum = $index + 1;
        info("Memproses Batch {$batchNum}/{$totalBatches} (" . count($batch) . " file)...");
        
        $tempZip = $config['temp_dir'] . "/missing_batch_{$batchNum}_" . time() . ".zip";
        $packUrl = $config['remote_url'] . '?secret=' . urlencode($config['remote_secret']) . '&action=pack_missing';
        
        $packRes = callRemoteApi($packUrl, 'POST', ['files' => $batch], $tempZip);
        
        if ($packRes['code'] === 200 && file_exists($tempZip) && filesize($tempZip) > 0) {
            $count = extractZipToStorage($tempZip, $config['local_storage']);
            $totalExtracted += $count;
            @unlink($tempZip);
        } else {
            warn("Gagal mengunduh Batch {$batchNum}. Mengunduh file secara individu sebagai cadangan...");
            @unlink($tempZip);
            
            // Fallback download satu per satu jika ZIP gagal
            foreach ($batch as $file) {
                $singleUrl = $config['remote_url'] . '?secret=' . urlencode($config['remote_secret']) . '&action=download_file&file=' . urlencode($file);
                $singleDest = $config['local_storage'] . '/' . $file;
                $pDir = dirname($singleDest);
                if (!is_dir($pDir)) @mkdir($pDir, 0755, true);
                
                $sRes = callRemoteApi($singleUrl, 'GET', null, $singleDest);
                if ($sRes['code'] === 200 && file_exists($singleDest)) {
                    $totalExtracted++;
                }
            }
        }
    }
    
    success("Sinkronisasi berkas selesai! Berhasil menyinkronkan {$totalExtracted} file baru ke storage lokal.");
}

/**
 * Perintah: PULL FOLDER TERTENTU (ZIP)
 */
function cmdPullFolder(array $config, string $folder): void {
    step("MENGUNDUH FOLDER DARI PRODUCTION: {$folder}");
    
    $tempZip = $config['temp_dir'] . "/pembda_download_" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $folder) . "_" . time() . ".zip";
    $url = $config['remote_url'] . '?secret=' . urlencode($config['remote_secret']) . '&action=zip&folder=' . urlencode($folder);
    
    info("Mengunduh arsip ZIP dari production...");
    info("URL: {$url}");
    
    $res = callRemoteApi($url, 'GET', null, $tempZip);
    
    if ($res['code'] !== 200 || !file_exists($tempZip) || filesize($tempZip) === 0) {
        error("Gagal mengunduh file ZIP dari server (HTTP {$res['code']}).");
        if (file_exists($tempZip)) @unlink($tempZip);
        return;
    }
    
    $zipSize = filesize($tempZip);
    success("Download ZIP berhasil (" . formatBytes($zipSize) . ").");
    
    $count = extractZipToStorage($tempZip, $config['local_storage']);
    @unlink($tempZip);
    
    success("Selesai! {$count} berkas dalam folder '{$folder}' berhasil diperbarui di lokal.");
}

/**
 * Perintah: PUSH FOLDER (Lokal -> Production)
 */
function cmdPushFolder(array $config, string $folder): void {
    step("MENGIRIM BERKAS DARI LOKAL KE PRODUCTION: {$folder}");
    
    $sourceDir = ($folder === 'all' || empty($folder)) ? $config['local_storage'] : $config['local_storage'] . '/' . $folder;
    
    if (!is_dir($sourceDir)) {
        error("Direktori lokal '{$sourceDir}' tidak ditemukan!");
        return;
    }
    
    $tempZip = $config['temp_dir'] . "/pembda_push_" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $folder) . "_" . time() . ".zip";
    $zip = new ZipArchive();
    
    if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        error("Gagal membuat arsip ZIP lokal.");
        return;
    }
    
    info("Mengemas folder lokal ke file ZIP sementara...");
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    $count = 0;
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $realPath = str_replace('\\', '/', $file->getRealPath());
            $relPath = substr($realPath, strlen($config['local_storage']) + 1);
            $zip->addFile($realPath, $relPath);
            $count++;
        }
    }
    $zip->close();
    
    if ($count === 0) {
        warn("Tidak ada file yang ditemukan untuk diunggah.");
        @unlink($tempZip);
        return;
    }
    
    info("Mengirim {$count} file (" . formatBytes(filesize($tempZip)) . ") ke production...");
    
    $url = $config['remote_url'] . '?secret=' . urlencode($config['remote_secret']) . '&action=upload_zip';
    
    $cFile = new CURLFile($tempZip, 'application/zip', basename($tempZip));
    $post = ['zip_file' => $cFile];
    
    $res = callRemoteApi($url, 'POST', ['is_file' => true, 'data' => $post]);
    @unlink($tempZip);
    
    if ($res['code'] === 200) {
        $json = json_decode($res['response'], true);
        success("Upload dan ekstraksi berhasil! " . ($json['message'] ?? ''));
    } else {
        error("Gagal mengirim berkas ke production (HTTP {$res['code']}): " . $res['response']);
    }
}

// ══════════════════════════════════════════════════════
// 7. ROUTER UTAMA CLI
// ══════════════════════════════════════════════════════

if (isset($args['preview']) || isset($args['status'])) {
    cmdPreview($config);
    exit(0);
}

if (isset($args['pull'])) {
    if (isset($args['missing-only'])) {
        cmdPullMissing($config);
    } elseif (isset($args['all'])) {
        cmdPullFolder($config, 'all');
    } elseif (isset($args['folder'])) {
        cmdPullFolder($config, $args['folder']);
    } else {
        // Default tarik missing-only agar hemat kuota & waktu
        cmdPullMissing($config);
    }
    exit(0);
}

if (isset($args['push'])) {
    $folder = $args['folder'] ?? 'all';
    cmdPushFolder($config, $folder);
    exit(0);
}

// ══════════════════════════════════════════════════════
// 8. MENU INTERAKTIF JIKA TANPA ARGUMEN
// ══════════════════════════════════════════════════════
while (true) {
    echo "\n";
    echo c("╔═════════════════════════════════════════════════════════════╗", "36") . "\n";
    echo c("║         PEMBDAHUB — SINKRONISASI STORAGE LOKAL/PROD         ║", "1;36") . "\n";
    echo c("╚═════════════════════════════════════════════════════════════╝", "36") . "\n";
    echo "  Remote Target : " . c($config['remote_url'], "33") . "\n";
    echo "  Local Storage : " . c($config['local_storage'], "32") . "\n";
    echo str_repeat("─", 63) . "\n";
    echo "  [1] " . c("Periksa & Bandingkan Storage", "1;37") . " (Production vs Lokal)\n";
    echo "  [2] " . c("Smart Pull (Rekomendasi)", "1;32") . " — Unduh hanya file yang belum ada\n";
    echo "  [3] " . c("Tarik Materi LMS Guru", "1;36") . " (folder: lms/materials)\n";
    echo "  [4] " . c("Tarik Foto Profil Pengguna", "1;36") . " (folder: photos)\n";
    echo "  [5] " . c("Tarik Dokumen & Foto PKL DUDI", "1;36") . " (folder: pkl_logs & monitorings)\n";
    echo "  [6] " . c("Tarik Folder Tertentu", "1;37") . " (Ketik nama folder manual)\n";
    echo "  [7] " . c("Tarik SELURUH Berkas Storage", "1;33") . " (Full ZIP Pull)\n";
    echo "  [8] " . c("Kirim Berkas Lokal ke Production", "1;35") . " (Push Upload)\n";
    echo "  [0] Keluar\n";
    echo str_repeat("─", 63) . "\n";
    echo "Pilihan Anda [1-8, 0]: ";
    
    $choice = trim(fgets(STDIN));
    
    match ($choice) {
        '1' => cmdPreview($config),
        '2' => cmdPullMissing($config),
        '3' => cmdPullFolder($config, 'lms/materials'),
        '4' => cmdPullFolder($config, 'photos'),
        '5' => (function() use ($config) {
            cmdPullFolder($config, 'pkl_logs');
            cmdPullFolder($config, 'pkl_monitorings');
        })(),
        '6' => (function() use ($config) {
            echo "Ketikkan nama folder yang ingin ditarik (contoh: cbt, documents, forum): ";
            $customFolder = trim(fgets(STDIN));
            if (!empty($customFolder)) cmdPullFolder($config, $customFolder);
        })(),
        '7' => cmdPullFolder($config, 'all'),
        '8' => (function() use ($config) {
            echo "Ketikkan nama folder yang ingin dikirim ke production (contoh: lms, photos, atau ketik 'all'): ";
            $pushFolder = trim(fgets(STDIN));
            if (!empty($pushFolder)) cmdPushFolder($config, $pushFolder);
        })(),
        '0' => (function() {
            echo "Terima kasih! Sampai jumpa.\n";
            exit(0);
        })(),
        default => warn("Pilihan tidak valid. Silakan masukkan angka [1-8] atau [0]."),
    };
}
