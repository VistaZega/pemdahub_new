<?php
/**
 * PembdaHUB — LMS Material Recovery & Audit Tool v2
 * 
 * Strategi:
 *   1. SCREENING: Audit per mata pelajaran/kursus → mana yang file fisiknya hilang
 *   2. SYNC PER KURSUS: Tarik file dari Hostinger per kursus (tidak sekaligus)
 *   3. RESUME LOG: Catat setiap file yang berhasil ditarik, kalau putus bisa lanjut
 * 
 * Akses:
 *   http://[SERVER]/lms-recovery.php?secret=pembda99
 */

$VALID_SECRETS = ['pembda99', 'pembda2026storage'];
$secret = $_REQUEST['secret'] ?? '';
if (!in_array($secret, $VALID_SECRETS, true)) {
    http_response_code(403);
    die('<h1>403 Forbidden</h1>');
}

@set_time_limit(0);
@ini_set('memory_limit', '1024M');

// ══════════════════════════════════════════════════════
// BOOTSTRAP DATABASE
// ══════════════════════════════════════════════════════
$envPaths = [__DIR__ . '/../.env', '/var/www/pembdahub/.env'];
$env = [];
foreach ($envPaths as $p) {
    if (file_exists($p)) {
        foreach (file($p, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim(trim($v), '"\'');
        }
        break;
    }
}

try {
    $pdo = new PDO(
        "mysql:host=" . ($env['DB_HOST'] ?? '127.0.0.1') . ";port=" . ($env['DB_PORT'] ?? '3306') . ";dbname=" . ($env['DB_DATABASE'] ?? 'pembdahub') . ";charset=utf8mb4",
        $env['DB_USERNAME'] ?? 'root', $env['DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("<h1>DB Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

// ══════════════════════════════════════════════════════
// STORAGE PATH & SYMLINK VERIFIKASI
// ══════════════════════════════════════════════════════
$storagePath = null;
foreach ([__DIR__ . '/../storage/app/public', '/var/www/pembdahub/storage/app/public'] as $sp) {
    if (is_dir($sp)) { $storagePath = realpath($sp); break; }
}
if (!$storagePath) die("<h1>Error</h1><p>storage/app/public tidak ditemukan.</p>");
$storagePath = str_replace('\\', '/', $storagePath);

// Deteksi status public/storage symlink di server
$publicStorage = __DIR__ . '/storage';
$symlinkStatus = 'unknown';
$symlinkTarget = '';
if (is_link($publicStorage)) {
    $symlinkTarget = readlink($publicStorage);
    if (file_exists($publicStorage)) {
        $symlinkStatus = 'active';
    } else {
        $symlinkStatus = 'broken';
    }
} elseif (is_dir($publicStorage)) {
    $symlinkStatus = 'dir';
} else {
    if (PHP_OS_FAMILY === 'Linux') {
        @symlink($storagePath, $publicStorage);
        if (file_exists($publicStorage)) {
            $symlinkStatus = 'active';
            $symlinkTarget = $storagePath;
        } else {
            $symlinkStatus = 'missing';
        }
    } else {
        $symlinkStatus = 'missing';
    }
}

if ($action === 'fix_symlink') {
    if (is_link($publicStorage) || file_exists($publicStorage)) {
        @unlink($publicStorage);
    }
    @symlink($storagePath, $publicStorage);
    header("Location: ?secret=" . urlencode($secret));
    exit;
}

// Log file untuk tracking progress sync
$logDir = dirname($storagePath) . '/lms-recovery';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logFile = $logDir . '/sync-log.json';

function loadSyncLog(string $logFile): array {
    if (file_exists($logFile)) {
        $data = json_decode(file_get_contents($logFile), true);
        if (is_array($data)) return $data;
    }
    return ['synced_files' => [], 'failed_files' => [], 'last_sync' => null, 'total_synced' => 0];
}

function saveSyncLog(string $logFile, array $log): void {
    $log['last_sync'] = date('Y-m-d H:i:s');
    file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$syncLog = loadSyncLog($logFile);

// ══════════════════════════════════════════════════════
// HELPER
// ══════════════════════════════════════════════════════
function fileExistsInStorage(string $filePath, string $storagePath): bool {
    if (empty($filePath)) return false;
    $clean = ltrim(str_replace(['storage/', 'public/'], ['', ''], $filePath), '/');
    return file_exists($storagePath . '/' . $clean) && filesize($storagePath . '/' . $clean) > 0;
}

function cleanPath(string $filePath): string {
    return ltrim(str_replace(['storage/', 'public/'], ['', ''], $filePath), '/');
}

function fmtBytes(int $bytes): string {
    $u = ['B','KB','MB','GB'];
    $p = floor(($bytes ? log($bytes) : 0) / log(1024));
    return round($bytes / (1 << (10 * min($p, 3))), 1) . ' ' . $u[min($p, 3)];
}

$action = $_GET['action'] ?? 'audit';

// ══════════════════════════════════════════════════════
// QUERY: Semua materi LMS + assignments yang punya file
// ══════════════════════════════════════════════════════
$materials = [];
try {
    $materials = $pdo->query("
        SELECT m.id, m.title, m.material_type, m.file_path, m.file_size, m.file_url,
               md.title AS module_title,
               c.id AS course_id, c.course_name, c.code AS course_code,
               clr.name AS class_name,
               t.full_name AS teacher_name,
               s.name AS school_name,
               COALESCE(sub.name, sub.subject_name) AS subject_name
        FROM lms_materials m
        LEFT JOIN lms_modules md ON m.module_id = md.id
        LEFT JOIN lms_courses c ON COALESCE(m.course_id, md.course_id) = c.id
        LEFT JOIN classrooms clr ON c.classroom_id = clr.id
        LEFT JOIN teachers t ON c.teacher_id = t.id
        LEFT JOIN schools s ON c.school_id = s.id
        LEFT JOIN subjects sub ON c.subject_id = sub.id
        WHERE m.deleted_at IS NULL AND m.file_path IS NOT NULL AND m.file_path != ''
        ORDER BY s.name, c.course_name, md.title, m.title
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$assignments = [];
try {
    $assignments = $pdo->query("
        SELECT a.id, a.title, a.assignment_type, a.file_path,
               md.title AS module_title,
               c.id AS course_id, c.course_name, c.code AS course_code,
               clr.name AS class_name,
               t.full_name AS teacher_name,
               s.name AS school_name
        FROM lms_assignments a
        LEFT JOIN lms_modules md ON a.module_id = md.id
        LEFT JOIN lms_courses c ON COALESCE(a.course_id, md.course_id) = c.id
        LEFT JOIN classrooms clr ON c.classroom_id = clr.id
        LEFT JOIN teachers t ON c.teacher_id = t.id
        LEFT JOIN schools s ON c.school_id = s.id
        WHERE a.deleted_at IS NULL AND a.file_path IS NOT NULL AND a.file_path != ''
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ══════════════════════════════════════════════════════
// GROUP BY COURSE: Hitung status per kursus
// ══════════════════════════════════════════════════════
$courseStats = []; // course_id => {name, teacher, school, subject, total, present, missing, files_missing[]}

foreach ($materials as $m) {
    $cid = $m['course_id'] ?? 0;
    if (!isset($courseStats[$cid])) {
        $courseStats[$cid] = [
            'course_id' => $cid,
            'course_name' => $m['course_name'] ?? 'Tanpa Kursus',
            'course_code' => $m['course_code'] ?? '',
            'class_name' => $m['class_name'] ?? '',
            'teacher_name' => $m['teacher_name'] ?? '-',
            'school_name' => $m['school_name'] ?? '-',
            'subject_name' => $m['subject_name'] ?? '-',
            'total' => 0, 'present' => 0, 'missing' => 0,
            'missing_files' => [], 'missing_details' => [],
        ];
    }
    $courseStats[$cid]['total']++;
    $exists = fileExistsInStorage($m['file_path'], $storagePath);
    if ($exists) {
        $courseStats[$cid]['present']++;
    } else {
        $courseStats[$cid]['missing']++;
        $cp = cleanPath($m['file_path']);
        $courseStats[$cid]['missing_files'][] = $cp;
        $courseStats[$cid]['missing_details'][] = [
            'id' => $m['id'], 'title' => $m['title'], 'type' => $m['material_type'],
            'file_path' => $cp, 'module' => $m['module_title'] ?? '-', 'kind' => 'materi',
        ];
    }
}

foreach ($assignments as $a) {
    $cid = $a['course_id'] ?? 0;
    if (!isset($courseStats[$cid])) {
        $courseStats[$cid] = [
            'course_id' => $cid,
            'course_name' => $a['course_name'] ?? 'Tanpa Kursus',
            'course_code' => $a['course_code'] ?? '',
            'class_name' => $a['class_name'] ?? '',
            'teacher_name' => $a['teacher_name'] ?? '-',
            'school_name' => $a['school_name'] ?? '-',
            'subject_name' => '-',
            'total' => 0, 'present' => 0, 'missing' => 0,
            'missing_files' => [], 'missing_details' => [],
        ];
    }
    $courseStats[$cid]['total']++;
    if (fileExistsInStorage($a['file_path'], $storagePath)) {
        $courseStats[$cid]['present']++;
    } else {
        $courseStats[$cid]['missing']++;
        $cp = cleanPath($a['file_path']);
        $courseStats[$cid]['missing_files'][] = $cp;
        $courseStats[$cid]['missing_details'][] = [
            'id' => $a['id'], 'title' => $a['title'], 'type' => 'tugas',
            'file_path' => $cp, 'module' => $a['module_title'] ?? '-', 'kind' => 'tugas',
        ];
    }
}

// Hitung global
$globalTotal = array_sum(array_column($courseStats, 'total'));
$globalPresent = array_sum(array_column($courseStats, 'present'));
$globalMissing = array_sum(array_column($courseStats, 'missing'));

// Urutkan: yang paling banyak missing di atas
uasort($courseStats, fn($a, $b) => $b['missing'] <=> $a['missing']);

// Group by school
$bySchool = [];
foreach ($courseStats as $cs) {
    $bySchool[$cs['school_name']][] = $cs;
}

// ══════════════════════════════════════════════════════
// ACTION: SYNC PER KURSUS (dengan logging & resume)
// ══════════════════════════════════════════════════════
if ($action === 'sync_course') {
    @ini_set('max_execution_time', '600');
    @set_time_limit(600);
    while (@ob_end_flush());
    ob_implicit_flush(true);
    
    header('Content-Type: text/html; charset=utf-8');
    header('X-Accel-Buffering: no');
    
    $courseId = (int)($_GET['course_id'] ?? 0);
    $hostingerIp = trim($_GET['hostinger_ip'] ?? '');
    $prodSecret = 'pembda2026storage';
    
    // Simpan IP di log agar tidak perlu ketik ulang
    if ($hostingerIp) {
        $syncLog['hostinger_ip'] = $hostingerIp;
        saveSyncLog($logFile, $syncLog);
    } else {
        $hostingerIp = $syncLog['hostinger_ip'] ?? '';
    }
    
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Sync Kursus #{$courseId}</title>";
    echo "<style>body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:20px;font-size:13px;line-height:1.8;}";
    echo ".ok{color:#3fb950;font-weight:bold;} .warn{color:#d29922;} .err{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}";
    echo "pre{background:#161b22;border:1px solid #30363d;padding:16px;border-radius:12px;white-space:pre-wrap;max-height:75vh;overflow-y:auto;}";
    echo "h1{color:#58a6ff;font-size:1.3em;} .btn{display:inline-block;background:#238636;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:12px;}</style></head><body>";
    
    if (empty($hostingerIp)) {
        echo "<h1>⚠️ IP Hostinger Diperlukan</h1>";
        echo "<p>Masukkan IP server Hostinger Anda:</p>";
        echo "<form method='GET' style='margin:20px 0;'>";
        echo "<input type='hidden' name='secret' value='" . htmlspecialchars($secret) . "'>";
        echo "<input type='hidden' name='action' value='sync_course'>";
        echo "<input type='hidden' name='course_id' value='{$courseId}'>";
        echo "<input type='text' name='hostinger_ip' placeholder='Contoh: 153.92.xxx.xxx' style='padding:10px;font-size:16px;background:#161b22;color:#fff;border:1px solid #30363d;border-radius:8px;width:250px;font-family:monospace;' required>";
        echo " <button type='submit' style='padding:10px 20px;background:#238636;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:bold;'>🚀 Mulai Sync</button>";
        echo "</form>";
        echo "<p class='info'>💡 Cara mendapatkan IP: Login hPanel Hostinger → Dashboard → Server IP Address</p>";
        echo "<a href='?secret=" . htmlspecialchars($secret) . "' class='btn'>« Kembali</a></body></html>";
        exit;
    }
    
    // Ambil daftar file yang hilang untuk kursus ini
    $target = $courseStats[$courseId] ?? null;
    if (!$target || empty($target['missing_files'])) {
        echo "<h1>✅ Kursus ini sudah lengkap!</h1>";
        echo "<p>Tidak ada file yang perlu ditarik.</p>";
        echo "<a href='?secret=" . htmlspecialchars($secret) . "' class='btn'>« Kembali</a></body></html>";
        exit;
    }
    
    $courseName = $target['course_name'];
    $teacherName = $target['teacher_name'];
    $filesToSync = $target['missing_files'];
    
    // Filter: skip file yang sudah pernah berhasil di-sync (resume)
    $alreadySynced = 0;
    $filesToSync = array_filter($filesToSync, function($f) use ($syncLog, &$alreadySynced) {
        if (isset($syncLog['synced_files'][$f])) { $alreadySynced++; return false; }
        return true;
    });
    $filesToSync = array_values($filesToSync);
    
    echo "<h1>📥 Sync: {$courseName}</h1>";
    echo "<p>Guru: <strong>{$teacherName}</strong> · Sekolah: {$target['school_name']}</p>";
    echo "<p>File yang perlu ditarik: <strong>" . count($filesToSync) . "</strong>";
    if ($alreadySynced > 0) echo " (skip {$alreadySynced} file yang sudah di-sync sebelumnya)";
    echo "</p><pre>";
    flush();
    
    if (empty($filesToSync)) {
        echo "<span class='ok'>✅ Semua file untuk kursus ini sudah pernah di-sync! Cek ulang di Audit.</span></pre>";
        echo "<a href='?secret=" . htmlspecialchars($secret) . "' class='btn'>« Kembali</a></body></html>";
        exit;
    }
    
    // Test koneksi Hostinger via CURLOPT_RESOLVE (Bypass Cloudflare DNS)
    echo "<span class='info'>▶ Menguji koneksi langsung ke Hostinger ({$hostingerIp})...</span>\n";
    flush();
    
    $domain = 'perguruanpembda.com';
    $resolveConfig = [
        "{$domain}:443:{$hostingerIp}",
        "{$domain}:80:{$hostingerIp}",
    ];
    
    $testUrl = "https://{$domain}/storage-sync.php?secret={$prodSecret}&action=status&format=json";
    $ch = curl_init($testUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_RESOLVE => $resolveConfig,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    if ($code !== 200 || empty($res)) {
        echo "<span class='err'>✖ Gagal terhubung ke Hostinger di IP {$hostingerIp} (HTTP {$code})</span>\n";
        if ($curlErr) echo "<span class='err'>  cURL Error: {$curlErr}</span>\n";
        echo "<span class='warn'>Pastikan IP benar dan server Hostinger aktif.</span>\n";
        echo "</pre><a href='?secret=" . htmlspecialchars($secret) . "' class='btn'>« Kembali</a></body></html>";
        exit;
    }
    
    $statusData = json_decode($res, true);
    $remoteCount = $statusData['total_files'] ?? 0;
    $remoteSize = $statusData['total_size_human'] ?? '?';
    echo "<span class='ok'>✔ Terhubung ke Hostinger via HTTPS (Direct IP Resolution)!</span>\n";
    echo "<span class='info'>  Total berkas di storage Hostinger: {$remoteCount} berkas ({$remoteSize})</span>\n";
    flush();
    
    // Sync file satu per satu dengan logging
    $synced = 0;
    $failed = 0;
    $totalFiles = count($filesToSync);
    
    echo "\n<span class='info'>▶ Mengunduh {$totalFiles} file untuk kursus \"{$courseName}\"...</span>\n\n";
    flush();
    
    foreach ($filesToSync as $idx => $relPath) {
        $num = $idx + 1;
        $shortName = basename($relPath);
        echo "<span class='info'>[{$num}/{$totalFiles}]</span> {$shortName} ... ";
        flush();
        
        $fileUrl = "https://{$domain}/storage-sync.php?secret={$prodSecret}&action=download_file&file=" . urlencode($relPath);
        $dest = $storagePath . '/' . $relPath;
        $parentDir = dirname($dest);
        if (!is_dir($parentDir)) @mkdir($parentDir, 0755, true);
        
        $ch = curl_init($fileUrl);
        $fh = fopen($dest, 'w+');
        curl_setopt_array($ch, [
            CURLOPT_RESOLVE => $resolveConfig,
            CURLOPT_FILE => $fh,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 180,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dlSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $fileErr = curl_error($ch);
        curl_close($ch);
        fclose($fh);
        
        if ($httpCode === 200 && file_exists($dest) && filesize($dest) > 0) {
            @chmod($dest, 0644);
            $synced++;
            $syncLog['synced_files'][$relPath] = [
                'synced_at' => date('Y-m-d H:i:s'),
                'size' => (int)$dlSize,
                'course' => $courseName,
            ];
            $syncLog['total_synced'] = ($syncLog['total_synced'] ?? 0) + 1;
            saveSyncLog($logFile, $syncLog);
            echo "<span class='ok'>✔ " . fmtBytes((int)$dlSize) . "</span>\n";
        } else {
            @unlink($dest);
            $failed++;
            $syncLog['failed_files'][$relPath] = [
                'failed_at' => date('Y-m-d H:i:s'),
                'http_code' => $httpCode,
                'course' => $courseName,
            ];
            saveSyncLog($logFile, $syncLog);
            echo "<span class='err'>✖ GAGAL (HTTP {$httpCode})</span>\n";
        }
        flush();
    }
    
    // Fix ownership di Linux
    if (PHP_OS_FAMILY === 'Linux') {
        @exec("chown -R www-data:www-data " . escapeshellarg($storagePath . '/lms') . " 2>/dev/null");
        @exec("chown -R www-data:www-data " . escapeshellarg($storagePath . '/lms_materials') . " 2>/dev/null");
    }
    
    echo "\n<span class='ok'>══════════════════════════════════════════</span>\n";
    echo "<span class='ok'>🎉 SYNC SELESAI: {$courseName}</span>\n";
    echo "<span class='ok'>   ✔ Berhasil: {$synced} file</span>\n";
    if ($failed > 0) echo "<span class='err'>   ✖ Gagal: {$failed} file (bisa di-retry)</span>\n";
    echo "<span class='info'>   📋 Log tersimpan di: {$logFile}</span>\n";
    echo "<span class='ok'>══════════════════════════════════════════</span>";
    echo "</pre>";
    echo "<div style='margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;'>";
    if ($failed > 0) {
        echo "<a href='?secret=" . htmlspecialchars($secret) . "&action=sync_course&course_id={$courseId}&hostinger_ip=" . urlencode($hostingerIp) . "' class='btn' style='background:#b45309;'>🔄 Retry File Gagal</a>";
    }
    echo "<a href='?secret=" . htmlspecialchars($secret) . "' class='btn'>« Kembali ke Audit</a>";
    echo "</div></body></html>";
    exit;
}

// ══════════════════════════════════════════════════════
// ACTION: SYNC PER KURSUS VIA AJAX (UNTUK AUTO-RUNNER)
// ══════════════════════════════════════════════════════
if ($action === 'sync_course_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    @ini_set('max_execution_time', '180');
    @set_time_limit(180);
    
    $courseId = (int)($_POST['course_id'] ?? $_GET['course_id'] ?? 0);
    $hostingerIp = trim($_POST['hostinger_ip'] ?? $_GET['hostinger_ip'] ?? ($syncLog['hostinger_ip'] ?? ''));
    $prodSecret = 'pembda2026storage';
    
    if (empty($hostingerIp)) {
        echo json_encode(['status' => 'error', 'message' => 'IP Hostinger belum diset.']);
        exit;
    }
    
    $target = $courseStats[$courseId] ?? null;
    if (!$target || empty($target['missing_files'])) {
        echo json_encode([
            'status' => 'ok',
            'course_id' => $courseId,
            'course_name' => $target['course_name'] ?? "ID {$courseId}",
            'synced' => 0,
            'failed' => 0,
            'remaining' => 0,
            'message' => 'Sudah lengkap'
        ]);
        exit;
    }
    
    $courseName = $target['course_name'];
    $filesToSync = $target['missing_files'];
    
    // Filter: skip file yang sudah pernah berhasil di-sync (resume)
    $filesToSync = array_values(array_filter($filesToSync, function($f) use ($syncLog) {
        return !isset($syncLog['synced_files'][$f]);
    }));
    
    if (empty($filesToSync)) {
        echo json_encode([
            'status' => 'ok',
            'course_id' => $courseId,
            'course_name' => $courseName,
            'synced' => 0,
            'failed' => 0,
            'remaining' => 0,
            'message' => 'Semua file sudah pernah di-sync'
        ]);
        exit;
    }
    
    $domain = 'perguruanpembda.com';
    $resolveConfig = [
        "{$domain}:443:{$hostingerIp}",
        "{$domain}:80:{$hostingerIp}",
    ];
    
    $synced = 0;
    $failed = 0;
    
    foreach ($filesToSync as $relPath) {
        $fileUrl = "https://{$domain}/storage-sync.php?secret={$prodSecret}&action=download_file&file=" . urlencode($relPath);
        $dest = $storagePath . '/' . $relPath;
        $parentDir = dirname($dest);
        if (!is_dir($parentDir)) @mkdir($parentDir, 0755, true);
        
        $ch = curl_init($fileUrl);
        $fh = fopen($dest, 'w+');
        curl_setopt_array($ch, [
            CURLOPT_RESOLVE => $resolveConfig,
            CURLOPT_FILE => $fh,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 60,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dlSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        curl_close($ch);
        fclose($fh);
        
        if ($httpCode === 200 && file_exists($dest) && filesize($dest) > 0) {
            @chmod($dest, 0644);
            $synced++;
            $syncLog['synced_files'][$relPath] = [
                'synced_at' => date('Y-m-d H:i:s'),
                'size' => (int)$dlSize,
                'course' => $courseName,
            ];
            $syncLog['total_synced'] = ($syncLog['total_synced'] ?? 0) + 1;
            unset($syncLog['failed_files'][$relPath]);
        } else {
            @unlink($dest);
            $failed++;
            $syncLog['failed_files'][$relPath] = [
                'failed_at' => date('Y-m-d H:i:s'),
                'http_code' => $httpCode,
                'course' => $courseName,
            ];
        }
    }
    
    saveSyncLog($logFile, $syncLog);
    
    if (PHP_OS_FAMILY === 'Linux') {
        @exec("chown -R www-data:www-data " . escapeshellarg($storagePath . '/lms') . " 2>/dev/null");
        @exec("chown -R www-data:www-data " . escapeshellarg($storagePath . '/lms_materials') . " 2>/dev/null");
    }
    
    echo json_encode([
        'status' => 'ok',
        'course_id' => $courseId,
        'course_name' => $courseName,
        'synced' => $synced,
        'failed' => $failed,
        'remaining' => count($filesToSync) - $synced,
    ]);
    exit;
}

// ══════════════════════════════════════════════════════
// ACTION: RETRY FAILED FILES
// ══════════════════════════════════════════════════════
if ($action === 'retry_failed') {
    // Clear failed entries dari log agar bisa di-sync ulang
    $syncLog['failed_files'] = [];
    saveSyncLog($logFile, $syncLog);
    header("Location: ?secret=" . urlencode($secret));
    exit;
}

// ══════════════════════════════════════════════════════
// ACTION: RESET LOG
// ══════════════════════════════════════════════════════
if ($action === 'reset_log') {
    if (file_exists($logFile)) @unlink($logFile);
    header("Location: ?secret=" . urlencode($secret));
    exit;
}

// ══════════════════════════════════════════════════════
// ACTION: EXPORT MISSING (JSON)
// ══════════════════════════════════════════════════════
if ($action === 'export_missing') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="lms_missing_' . date('Ymd_His') . '.json"');
    $export = [];
    foreach ($courseStats as $cs) {
        if ($cs['missing'] > 0) $export[] = $cs;
    }
    echo json_encode(['generated' => date('Y-m-d H:i:s'), 'courses_with_missing' => $export], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ══════════════════════════════════════════════════════
// RENDER: AUDIT DASHBOARD
// ══════════════════════════════════════════════════════
$savedIp = $syncLog['hostinger_ip'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Recovery — PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .pulse-red { animation: pr 2s ease-in-out infinite; }
        @keyframes pr { 0%,100%{opacity:1;} 50%{opacity:.5;} }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-4 sm:p-6">
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 border border-slate-700/80 p-5 rounded-2xl shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full <?= $globalMissing > 0 ? 'bg-rose-500 pulse-red' : 'bg-emerald-500 animate-pulse' ?>"></span>
                    <span class="text-[10px] font-bold tracking-wider <?= $globalMissing > 0 ? 'text-rose-400' : 'text-emerald-400' ?> uppercase">
                        <?= $globalMissing > 0 ? "{$globalMissing} File Materi Hilang" : 'Semua Materi Lengkap' ?>
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">🔍 Screening Materi LMS per Mata Pelajaran</h1>
                <p class="text-xs text-slate-400 mt-1">Database: <code class="text-emerald-300 font-mono"><?= htmlspecialchars($env['DB_DATABASE'] ?? '') ?></code></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if ($globalMissing > 0 && !empty($savedIp)): ?>
                <button onclick="openAutoRunner()" class="px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-emerald-700/30">
                    ⚡ Auto-Sync Semua Kursus
                </button>
                <?php endif; ?>
                <a href="?secret=<?= htmlspecialchars($secret) ?>" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition">🔄 Refresh</a>
                <?php if ($globalMissing > 0): ?>
                <a href="?secret=<?= htmlspecialchars($secret) ?>&action=export_missing" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold transition">📋 Export JSON</a>
                <?php endif; ?>
            </div>
        </div>
    <!-- Lokasi Fisik & Verifikasi Path -->
    <div class="bg-slate-900/90 border border-slate-800 p-4 rounded-xl text-xs space-y-2">
        <div class="font-bold text-slate-300 flex items-center justify-between">
            <span class="flex items-center gap-1.5">📍 Konfirmasi Lokasi Penyimpanan Berkas LMS</span>
            <span class="text-[10px] text-slate-500 font-mono">PHP OS: <?= PHP_OS_FAMILY ?></span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
            <div class="bg-slate-950/60 p-3 rounded-lg border border-slate-800/80 space-y-1">
                <div class="font-bold text-emerald-400 flex items-center justify-between">
                    <span>🖥️ Server Lokal (Ubuntu Saat Ini)</span>
                    <span class="text-[10px] px-2 py-0.5 rounded <?= $symlinkStatus === 'active' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' ?>">
                        Symlink Web: <?= strtoupper($symlinkStatus) ?>
                    </span>
                </div>
                <p class="text-slate-400">Lokasi Fisik File: <code class="text-white font-mono"><?= htmlspecialchars($storagePath) ?></code></p>
                <p class="text-slate-400">Folder Sub-LMS: <code class="text-slate-300 font-mono"><?= htmlspecialchars($storagePath) ?>/lms/materials/</code></p>
                <div class="flex items-center justify-between pt-1">
                    <span class="text-[11px] text-slate-400">Jalur Akses Web: <code class="text-sky-300 font-mono"><?= htmlspecialchars($publicStorage) ?></code></span>
                    <?php if ($symlinkStatus !== 'active'): ?>
                    <a href="?secret=<?= htmlspecialchars($secret) ?>&action=fix_symlink" class="text-[10px] px-2 py-0.5 bg-rose-600 hover:bg-rose-500 text-white rounded font-bold">⚡ Perbaiki Symlink</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-slate-950/60 p-3 rounded-lg border border-slate-800/80 space-y-1">
                <div class="font-bold text-sky-400">☁️ Server Sumber (Hostinger Production)</div>
                <p class="text-slate-400">Lokasi Utama: <code class="text-slate-300 font-mono">.../pembdahub/storage/app/public/lms/materials/</code></p>
                <p class="text-slate-400">Lokasi Fallback: <code class="text-slate-300 font-mono">.../public_html/storage/lms/materials/</code></p>
                <p class="text-[11px] text-emerald-300/80 pt-1">✅ Script auto-detect di Hostinger sudah mendukung kedua path di atas.</p>
            </div>
        </div>
    </div>

    <!-- KPI -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-slate-800 border border-slate-700 p-4 rounded-xl text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Total File Materi</div>
            <div class="text-2xl font-black text-white mt-1"><?= number_format($globalTotal) ?></div>
        </div>
        <div class="bg-slate-800 border border-slate-700 p-4 rounded-xl text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase">File Ada ✅</div>
            <div class="text-2xl font-black text-emerald-400 mt-1"><?= number_format($globalPresent) ?></div>
        </div>
        <div class="bg-slate-800 border <?= $globalMissing > 0 ? 'border-rose-500/50 bg-rose-950/20' : 'border-slate-700' ?> p-4 rounded-xl text-center">
            <div class="text-[10px] font-bold <?= $globalMissing > 0 ? 'text-rose-400' : 'text-slate-400' ?> uppercase">File Hilang ❌</div>
            <div class="text-2xl font-black <?= $globalMissing > 0 ? 'text-rose-400' : 'text-emerald-400' ?> mt-1"><?= number_format($globalMissing) ?></div>
        </div>
        <div class="bg-slate-800 border border-slate-700 p-4 rounded-xl text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Kursus Terdampak</div>
            <div class="text-2xl font-black text-amber-400 mt-1"><?= count(array_filter($courseStats, fn($c) => $c['missing'] > 0)) ?></div>
        </div>
        <div class="bg-slate-800 border border-slate-700 p-4 rounded-xl text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Sudah Di-Sync</div>
            <div class="text-2xl font-black text-sky-400 mt-1"><?= number_format($syncLog['total_synced'] ?? 0) ?></div>
        </div>
    </div>

    <?php if ($globalMissing > 0): ?>
    <!-- Setup IP Hostinger -->
    <div class="bg-gradient-to-r from-emerald-950/60 to-teal-950/60 border border-emerald-500/40 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-emerald-300 mb-2">⚙️ Konfigurasi IP Hostinger (sekali saja)</h2>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
            <input type="hidden" name="action" value="save_ip">
            <input type="text" name="hostinger_ip" value="<?= htmlspecialchars($savedIp) ?>" placeholder="IP Hostinger (cth: 153.92.x.x)"
                   class="bg-slate-900 border border-emerald-500/30 text-white px-3 py-2 rounded-lg text-sm font-mono w-52 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-lg text-xs transition">💾 Simpan IP</button>
            <?php if ($savedIp): ?>
                <span class="text-xs text-emerald-300 ml-2">✅ IP tersimpan: <code class="font-mono"><?= htmlspecialchars($savedIp) ?></code></span>
            <?php endif; ?>
        </form>
        <p class="text-[11px] text-emerald-200/60 mt-2">💡 Cek IP di hPanel Hostinger → Dashboard → "Server IP Address" atau DNS Zone Editor → A Record</p>
    </div>
    <?php endif; ?>

    <?php if ($action === 'save_ip' && !empty($_GET['hostinger_ip'])): ?>
        <?php
        $syncLog['hostinger_ip'] = trim($_GET['hostinger_ip']);
        saveSyncLog($logFile, $syncLog);
        $savedIp = $syncLog['hostinger_ip'];
        ?>
        <div class="bg-emerald-950/40 border border-emerald-500/30 p-3 rounded-xl text-sm text-emerald-300">
            ✅ IP Hostinger tersimpan: <code class="font-mono font-bold"><?= htmlspecialchars($savedIp) ?></code>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Bar -->
    <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-xl flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-1 min-w-[240px]">
            <span class="text-slate-400 text-sm">🔍</span>
            <input type="text" id="courseSearch" placeholder="Cari nama mapel, guru, kelas, atau ID kursus (misal: 496, Seni Musik, Hasrat)..."
                   oninput="filterCourses(this.value)"
                   class="bg-slate-950 border border-slate-800 text-white text-xs px-3 py-2 rounded-lg w-full focus:outline-none focus:border-emerald-500 font-medium">
        </div>
        <div class="text-[11px] text-slate-400">
            Ketik ID <code class="text-sky-300 font-mono font-bold">496</code> untuk melihat Seni Musik yang sudah di-sync
        </div>
    </div>

    <!-- Per-School Course Cards -->
    <?php foreach ($bySchool as $schoolName => $courses): ?>
    <div class="space-y-3 school-group">
        <h2 class="text-sm font-black text-slate-300 uppercase tracking-wider flex items-center gap-2 pt-2">
            <span>🏫</span> <?= htmlspecialchars($schoolName) ?>
            <span class="text-[10px] font-mono text-slate-500">(<?= count($courses) ?> kursus)</span>
        </h2>

        <?php foreach ($courses as $cs):
            $pct = $cs['total'] > 0 ? round(($cs['present'] / $cs['total']) * 100) : 100;
            $isComplete = $cs['missing'] === 0;
            $barColor = $isComplete ? 'bg-emerald-500' : ($pct > 50 ? 'bg-amber-500' : 'bg-rose-500');
            $borderColor = $isComplete ? 'border-emerald-500/20' : ($cs['missing'] > 5 ? 'border-rose-500/30' : 'border-amber-500/20');
        ?>
        <div class="bg-slate-800 border <?= $borderColor ?> rounded-xl overflow-hidden course-card">
            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-base"><?= $isComplete ? '✅' : '❌' ?></span>
                        <span class="text-[11px] font-mono font-bold bg-slate-950 px-2 py-0.5 rounded text-sky-400 border border-slate-700">#<?= $cs['course_id'] ?></span>
                        <h3 class="font-bold text-white text-sm truncate"><?= htmlspecialchars($cs['course_name']) ?></h3>
                        <?php if (!empty($cs['class_name'])): ?>
                        <span class="text-[10px] px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded border border-emerald-500/30 font-semibold"><?= htmlspecialchars($cs['class_name']) ?></span>
                        <?php endif; ?>
                        <?php if ($cs['subject_name'] && $cs['subject_name'] !== '-'): ?>
                        <span class="text-[10px] px-2 py-0.5 bg-indigo-500/20 text-indigo-300 rounded border border-indigo-500/30"><?= htmlspecialchars($cs['subject_name']) ?></span>
                        <?php endif; ?>
                        <?php if ($cs['present'] > 0 && !$isComplete): ?>
                        <span class="text-[10px] px-2 py-0.5 bg-teal-500/20 text-teal-300 rounded border border-teal-500/30 font-bold">🎉 <?= $cs['present'] ?> Berhasil Dipulihkan</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1 flex flex-wrap gap-x-4">
                        <span>👨‍🏫 <?= htmlspecialchars($cs['teacher_name']) ?></span>
                        <?php if (!empty($cs['course_code'])): ?>
                        <span class="font-mono text-slate-500">[<?= htmlspecialchars($cs['course_code']) ?>]</span>
                        <?php endif; ?>
                        <span>📄 Total: <strong><?= $cs['total'] ?></strong> file</span>
                        <span class="text-emerald-400 font-semibold">✔ <?= $cs['present'] ?> ada di server</span>
                        <span class="<?= $isComplete ? 'text-emerald-400' : 'text-rose-400' ?> font-semibold">❌ <?= $cs['missing'] ?> belum ada</span>
                    </div>
                    <!-- Progress bar -->
                    <div class="mt-2 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full <?= $barColor ?> rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                
                <?php if (!$isComplete): ?>
                <div class="shrink-0 flex gap-2">
                    <?php if ($savedIp): ?>
                    <a href="?secret=<?= htmlspecialchars($secret) ?>&action=sync_course&course_id=<?= $cs['course_id'] ?>&hostinger_ip=<?= urlencode($savedIp) ?>"
                       onclick="return confirm('Tarik <?= $cs['missing'] ?> file dari Hostinger untuk kursus ini?');"
                       class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition whitespace-nowrap">
                        📥 Sync <?= $cs['missing'] ?> file
                    </a>
                    <?php else: ?>
                    <span class="px-3 py-2 bg-slate-700 text-slate-400 rounded-lg text-xs font-bold cursor-not-allowed" title="Set IP Hostinger dulu di atas">
                        ⏸️ Set IP dulu
                    </span>
                    <?php endif; ?>
                    <button onclick="this.closest('.bg-slate-800').querySelector('.detail-panel').classList.toggle('hidden')"
                            class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition">
                        📋 Detail
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!$isComplete): ?>
            <!-- Detail Panel (collapsed) -->
            <div class="detail-panel hidden border-t border-slate-700/50">
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/80 sticky top-0">
                            <tr class="text-[10px] font-bold text-slate-400 uppercase">
                                <th class="py-2 px-3">#</th>
                                <th class="py-2 px-3">Modul</th>
                                <th class="py-2 px-3">Judul Materi</th>
                                <th class="py-2 px-3">Tipe</th>
                                <th class="py-2 px-3">Path File</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/30">
                            <?php foreach ($cs['missing_details'] as $i => $d): ?>
                            <tr class="hover:bg-slate-700/20">
                                <td class="py-1.5 px-3 text-slate-500 font-mono"><?= $i+1 ?></td>
                                <td class="py-1.5 px-3 text-slate-300 max-w-[120px] truncate"><?= htmlspecialchars($d['module']) ?></td>
                                <td class="py-1.5 px-3 text-amber-300 font-medium max-w-[200px] truncate" title="<?= htmlspecialchars($d['title']) ?>"><?= htmlspecialchars($d['title']) ?></td>
                                <td class="py-1.5 px-3">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold <?= match($d['type']) { 'pdf'=>'bg-red-500/20 text-red-300', 'video'=>'bg-purple-500/20 text-purple-300', 'document'=>'bg-blue-500/20 text-blue-300', 'image'=>'bg-green-500/20 text-green-300', 'tugas'=>'bg-amber-500/20 text-amber-300', default=>'bg-slate-700 text-slate-300' } ?>"><?= strtoupper($d['type']) ?></span>
                                </td>
                                <td class="py-1.5 px-3 font-mono text-[10px] text-rose-300/80 max-w-[200px] truncate"><?= htmlspecialchars($d['file_path']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php if ($globalMissing === 0): ?>
    <div class="bg-emerald-950/40 border border-emerald-500/30 rounded-2xl p-8 text-center">
        <div class="text-5xl mb-3">🎉</div>
        <h2 class="text-2xl font-black text-emerald-400">Semua Materi LMS Lengkap!</h2>
        <p class="text-emerald-200/70 mt-2">Seluruh <?= number_format($globalPresent) ?> file materi tersedia di server ini.</p>
    </div>
    <?php endif; ?>

    <!-- Sync Log Summary -->
    <?php if (!empty($syncLog['synced_files']) || !empty($syncLog['failed_files'])): ?>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold text-white">📋 Riwayat Sinkronisasi</h3>
            <div class="flex gap-2">
                <?php if (!empty($syncLog['failed_files'])): ?>
                <a href="?secret=<?= htmlspecialchars($secret) ?>&action=retry_failed" class="text-[10px] px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded font-bold transition">🔄 Reset Gagal</a>
                <?php endif; ?>
                <a href="?secret=<?= htmlspecialchars($secret) ?>&action=reset_log" onclick="return confirm('Reset semua riwayat sync?');" class="text-[10px] px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded font-bold transition">🗑️ Reset Log</a>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="bg-slate-900/60 p-3 rounded-lg">
                <span class="text-slate-400">Total Berhasil Sync</span>
                <div class="text-lg font-bold text-emerald-400"><?= number_format(count($syncLog['synced_files'])) ?></div>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-lg">
                <span class="text-slate-400">File Gagal</span>
                <div class="text-lg font-bold text-rose-400"><?= number_format(count($syncLog['failed_files'] ?? [])) ?></div>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-lg">
                <span class="text-slate-400">IP Hostinger</span>
                <div class="text-sm font-mono text-sky-400 mt-0.5"><?= htmlspecialchars($savedIp ?: 'Belum diset') ?></div>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-lg">
                <span class="text-slate-400">Sync Terakhir</span>
                <div class="text-sm text-white mt-0.5"><?= htmlspecialchars($syncLog['last_sync'] ?? '-') ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <p class="text-center text-[10px] text-slate-600 py-3">PembdaHUB LMS Recovery v2 · <?= date('Y-m-d H:i:s') ?></p>
</div>

<!-- Auto-Runner Modal Overlay -->
<div id="autoRunnerModal" class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-slate-900 to-slate-800">
            <div>
                <h3 class="font-black text-white text-base flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    ⚡ Auto-Runner: Sinkronisasi Massal Semua Kursus
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Menarik berkas dari Hostinger kursus per kursus secara otomatis & aman</p>
            </div>
            <button onclick="closeAutoRunner()" class="text-slate-400 hover:text-white text-2xl p-1 leading-none">&times;</button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 space-y-4 flex-1 overflow-y-auto">
            <!-- Metrics -->
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Progres Kursus</span>
                    <div id="runnerCourseCount" class="text-lg font-black text-sky-400 mt-0.5">0 / <?= count(array_filter($courseStats, fn($c) => $c['missing'] > 0)) ?></div>
                </div>
                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Berkas Ditarik</span>
                    <div id="runnerSyncedCount" class="text-lg font-black text-emerald-400 mt-0.5">0</div>
                </div>
                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Berkas 404 (Dilewati)</span>
                    <div id="runnerFailedCount" class="text-lg font-black text-amber-400 mt-0.5">0</div>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-semibold">
                    <span id="runnerStatusText" class="text-slate-300">Siap dijalankan...</span>
                    <span id="runnerPercentText" class="text-emerald-400 font-mono">0%</span>
                </div>
                <div class="h-3 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                    <div id="runnerProgressBar" class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <!-- Terminal Log -->
            <div class="space-y-1">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-400">Terminal Log Real-Time</span>
                    <span class="text-[10px] text-slate-500 font-mono">Hostinger: <?= htmlspecialchars($savedIp) ?></span>
                </div>
                <div id="runnerTerminal" class="bg-slate-950 border border-slate-800 rounded-xl p-3 font-mono text-[11px] text-slate-300 h-56 overflow-y-auto space-y-1 select-text">
                    <div class="text-slate-500">Klik "Mulai Sinkronisasi" untuk menjalankan proses otomatis kursus per kursus...</div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/80 flex items-center justify-between">
            <div class="text-[11px] text-slate-400 hidden sm:block">
                💡 Bisa di-pause atau ditutup kapan saja. Berkas yang sudah ditarik tidak akan hilang.
            </div>
            <div class="flex gap-2 ml-auto">
                <button id="btnStartRunner" onclick="startAutoRunner()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-emerald-700/30">
                    ▶ Mulai Sinkronisasi
                </button>
                <button id="btnPauseRunner" onclick="pauseAutoRunner()" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition hidden">
                    ⏸️ Jeda (Pause)
                </button>
                <button onclick="closeAutoRunner()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function filterCourses(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.course-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
    });
}

// ══════════════════════════════════════════════════════
// AUTO-RUNNER ENGINE
// ══════════════════════════════════════════════════════
const runnerQueue = <?= json_encode(array_values(array_filter(array_map(function($c) {
    if ($c['missing'] <= 0) return null;
    return [
        'id' => $c['course_id'],
        'name' => $c['course_name'] . (!empty($c['class_name']) ? ' (' . $c['class_name'] . ')' : ''),
        'teacher' => $c['teacher_name'],
        'missing' => $c['missing'],
    ];
}, $courseStats)))) ?>;

let runnerIndex = 0;
let runnerIsRunning = false;
let runnerTotalSynced = 0;
let runnerTotalFailed = 0;
const hostingerIp = <?= json_encode($savedIp) ?>;
const secretKey = <?= json_encode($secret) ?>;

function openAutoRunner() {
    document.getElementById('autoRunnerModal').classList.remove('hidden');
}

function closeAutoRunner() {
    if (runnerIsRunning) {
        if (!confirm('Auto-Runner sedang berjalan. Ingin menjeda proses?')) return;
        pauseAutoRunner();
    }
    document.getElementById('autoRunnerModal').classList.add('hidden');
    if (runnerTotalSynced > 0) {
        window.location.href = `?secret=${encodeURIComponent(secretKey)}`;
    }
}

function logTerminal(html) {
    const term = document.getElementById('runnerTerminal');
    const line = document.createElement('div');
    line.innerHTML = `<span class="text-slate-500">[${new Date().toTimeString().split(' ')[0]}]</span> ${html}`;
    term.appendChild(line);
    term.scrollTop = term.scrollHeight;
}

async function startAutoRunner() {
    if (!hostingerIp) {
        alert('IP Hostinger belum diatur!');
        return;
    }
    runnerIsRunning = true;
    document.getElementById('btnStartRunner').classList.add('hidden');
    document.getElementById('btnPauseRunner').classList.remove('hidden');
    logTerminal('<span class="text-emerald-400 font-bold">🚀 Memulai sinkronisasi massal otomatis...</span>');

    while (runnerIndex < runnerQueue.length && runnerIsRunning) {
        const item = runnerQueue[runnerIndex];
        const progressPct = Math.round((runnerIndex / runnerQueue.length) * 100);
        document.getElementById('runnerProgressBar').style.width = progressPct + '%';
        document.getElementById('runnerPercentText').innerText = progressPct + '%';
        document.getElementById('runnerCourseCount').innerText = `${runnerIndex + 1} / ${runnerQueue.length}`;
        document.getElementById('runnerStatusText').innerText = `Memproses: #${item.id} ${item.name}...`;

        logTerminal(`<span class="text-sky-400">▶ Memproses [#${item.id}] ${item.name} (${item.missing} file)...</span>`);

        try {
            const res = await fetch(`?secret=${encodeURIComponent(secretKey)}&action=sync_course_ajax&course_id=${item.id}&hostinger_ip=${encodeURIComponent(hostingerIp)}`);
            const data = await res.json();

            if (data.status === 'ok') {
                runnerTotalSynced += (data.synced || 0);
                runnerTotalFailed += (data.failed || 0);
                document.getElementById('runnerSyncedCount').innerText = runnerTotalSynced;
                document.getElementById('runnerFailedCount').innerText = runnerTotalFailed;

                let msg = `<span class="text-emerald-400">✔ Selesai: ${data.synced || 0} berhasil ditarik</span>`;
                if (data.failed > 0) {
                    msg += `, <span class="text-amber-400">${data.failed} dilewati (404)</span>`;
                }
                logTerminal(`  ${msg}`);
            } else {
                logTerminal(`  <span class="text-rose-400">✖ Error: ${data.message || 'Gagal'}</span>`);
            }
        } catch (err) {
            logTerminal(`  <span class="text-rose-400">✖ Jaringan terganggu: ${err.message}. Lanjut ke kursus berikutnya...</span>`);
        }

        runnerIndex++;
        await new Promise(r => setTimeout(r, 200));
    }

    if (runnerIndex >= runnerQueue.length) {
        runnerIsRunning = false;
        document.getElementById('runnerProgressBar').style.width = '100%';
        document.getElementById('runnerPercentText').innerText = '100%';
        document.getElementById('runnerStatusText').innerText = '🎉 Semua kursus selesai disinkronkan!';
        logTerminal('<span class="text-emerald-400 font-bold">🎉 SINKRONISASI MASSAL SELESAI SELURUHNYA! Memuat ulang dashboard dalam 3 detik...</span>');
        setTimeout(() => {
            window.location.href = `?secret=${encodeURIComponent(secretKey)}`;
        }, 3000);
    }
}

function pauseAutoRunner() {
    runnerIsRunning = false;
    document.getElementById('btnStartRunner').classList.remove('hidden');
    document.getElementById('btnStartRunner').innerText = '▶ Lanjutkan Sinkronisasi';
    document.getElementById('btnPauseRunner').classList.add('hidden');
    document.getElementById('runnerStatusText').innerText = 'Dijeda (Paused)';
    logTerminal('<span class="text-amber-400 font-bold">⏸️ Proses dijeda oleh pengguna.</span>');
}
</script>
</body>
</html>
