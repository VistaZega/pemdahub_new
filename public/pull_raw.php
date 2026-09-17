<?php
/**
 * Standalone Direct File Updater via GitHub API & Raw GitHub
 * Akses: https://perguruanpembda.com/pull_raw.php?secret=pembda99
 * Dengan Token: https://perguruanpembda.com/pull_raw.php?secret=pembda99&token=ghp_xxx
 */
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden: Invalid secret key.');
}

@ini_set('max_execution_time', '300');
@set_time_limit(300);
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Direct GitHub File Updater</title>";
echo "<style>body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:24px;line-height:1.6;font-size:14px;}";
echo ".ok{color:#3fb950;font-weight:bold;} .warn{color:#d29922;} .err{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}";
echo "pre{background:#161b22;border:1px solid #30363d;padding:16px;border-radius:8px;overflow-x:auto;}";
echo "</style></head><body>";
echo "<h1>🚀 PembdaHUB Direct File Sync (GitHub API & Raw)</h1><pre>";

// Auto-detect root folder (Ubuntu /var/www/pembdahub maupun Hostinger)
$root = realpath(__DIR__ . '/../');
if (!$root || !file_exists("{$root}/artisan")) {
    $root = realpath(__DIR__ . '/pembdahub');
}
if (!$root || !file_exists("{$root}/artisan")) {
    $root = '/var/www/pembdahub';
}
if (!$root || !file_exists("{$root}/artisan")) {
    $root = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub';
}

echo "<span class='info'>ℹ Root Folder: {$root}</span>\n";

$repo = 'VistaZega/pemdahub_new';
$branch = 'main';

// Validasi format token GitHub resmi (PAT klasik ghp_ atau fine-grained github_pat_)
if (!function_exists('isValidGithubToken')) {
    function isValidGithubToken($t) {
        if (empty($t) || !is_string($t)) return false;
        $t = trim($t);
        if (in_array(strtolower($t), ['dry_run', 'pembda99', 'null', 'undefined', 'true', 'false', 'none', 'secret', 'test'])) {
            return false;
        }
        if (str_starts_with($t, 'ghp_') || str_starts_with($t, 'github_pat_')) {
            return strlen($t) >= 25;
        }
        return (strlen($t) >= 30 && preg_match('/^[a-zA-Z0-9_]+$/', $t));
    }
}

// Baca token dari query atau .env
$envFile = "{$root}/.env";
$inputToken = trim($_GET['token'] ?? '');
$savedToken = '';

if (file_exists($envFile)) {
    $envContent = @file_get_contents($envFile);
    if (preg_match('/^GITHUB_DEPLOY_TOKEN=(.*)$/m', $envContent, $matches)) {
        $candidateToken = trim($matches[1], "\"' \r\n");
        if (isValidGithubToken($candidateToken)) {
            $savedToken = $candidateToken;
        } else {
            $newEnv = preg_replace('/^GITHUB_DEPLOY_TOKEN=.*$/m', '', $envContent);
            @file_put_contents($envFile, $newEnv);
            echo "<span class='warn'>⚠ Token tidak valid ('" . htmlspecialchars($candidateToken) . "') pada .env telah dihapus.</span>\n";
        }
    }
}

if (!empty($inputToken)) {
    if (isValidGithubToken($inputToken)) {
        $token = $inputToken;
        if (file_exists($envFile)) {
            if (strpos($envContent, 'GITHUB_DEPLOY_TOKEN=') !== false) {
                $newEnv = preg_replace('/^GITHUB_DEPLOY_TOKEN=.*$/m', "GITHUB_DEPLOY_TOKEN={$token}", $envContent);
            } else {
                $newEnv = $envContent . "\nGITHUB_DEPLOY_TOKEN={$token}\n";
            }
            @file_put_contents($envFile, $newEnv);
            echo "<span class='ok'>✔ Token disimpan permanen ke server (.env)</span>\n";
        }
    } else {
        echo "<span class='warn'>⚠ Parameter token '" . htmlspecialchars($inputToken) . "' diabaikan (bukan token GitHub valid).</span>\n";
        $token = $savedToken;
    }
} else {
    $token = $savedToken;
}

if (!empty($token)) {
    $masked = substr($token, 0, 7) . '...' . substr($token, -4);
    echo "<span class='ok'>✔ Token GitHub terdeteksi: {$masked}</span>\n";
} else {
    echo "<span class='warn'>⚠ Tidak ada token GitHub. File dari repo private mungkin gagal diunduh.</span>\n";
}

// Coba perbaiki permissions folder .git dan bersihkan file lock
if (is_dir("{$root}/.git")) {
    @chmod("{$root}/.git", 0777);
    @chmod("{$root}/.git/FETCH_HEAD", 0666);
    @unlink("{$root}/.git/FETCH_HEAD");
    @unlink("{$root}/.git/index.lock");
    @unlink("{$root}/.git/refs/heads/main.lock");
    @shell_exec("chmod -R 777 " . escapeshellarg("{$root}/.git") . " 2>/dev/null");
    @shell_exec("rm -f " . escapeshellarg("{$root}/.git/FETCH_HEAD") . " " . escapeshellarg("{$root}/.git/*.lock") . " " . escapeshellarg("{$root}/.git/refs/heads/*.lock") . " 2>/dev/null");
    echo "<span class='ok'>✔ Pembersihan lock file .git dan permissions selesai</span>\n";
}

// Daftar file prioritas yang diperbarui
$files = [
    // CBT SPP Tuition Compliance & Dispensations (2026-09-17)
    'app/Http/Controllers/Guru/CbtDispensationController.php',
    'app/Models/CbtExamDispensation.php',
    'app/Services/CbtTuitionComplianceService.php',
    'database/migrations/2026_09_17_150000_add_tuition_compliance_and_dispensations_to_cbt.php',
    'resources/views/guru/cbt/dispensations/index.blade.php',
    'app/Http/Controllers/Admin/CbtManagementController.php',
    'app/Http/Controllers/Mobile/MobileStudentController.php',
    'app/Http/Controllers/Siswa/CbtController.php',
    'app/Http/Requests/Cbt/StoreCbtExamRequest.php',
    'app/Models/AcademicYear.php',
    'app/Models/CbtExam.php',
    'app/Models/Student.php',
    'app/Services/CbtService.php',
    'resources/views/admin/cbt/exams/create.blade.php',
    'resources/views/admin/cbt/exams/edit.blade.php',
    'resources/views/guru/cbt/exams/create.blade.php',
    'resources/views/guru/cbt/exams/edit.blade.php',
    'resources/views/guru/cbt/exams/show.blade.php',
    'resources/views/guru/tagihan/index.blade.php',
    'resources/views/layouts/guru.blade.php',
    'resources/views/mobile/student/cbt.blade.php',
    'resources/views/siswa/cbt/index.blade.php',
    'resources/views/siswa/cbt/show.blade.php',
    'routes/guru.php',
    // WhatsApp Anti-Ban & Pacing Updates (2026-09-17)
    'app/Services/ExecutiveReportService.php',
    'app/Services/WhatsAppService.php',
    'app/Contracts/WhatsAppServiceInterface.php',
    'app/Services/NotificationService.php',
    'app/Http/Controllers/Yayasan/InvitationController.php',
    'app/Http/Controllers/Admin/PSBNotificationController.php',
    'app/Console/Commands/SendWaExecutiveDigest.php',
    'app/Console/Commands/CloseSurveyAndNotify.php',
    'config/whatsapp-templates.php',
    'routes/console.php',
    'public/check_phone_data.php',
    'public/git_pull_now.php',
    'public/pull_raw.php',
    // Recent core controllers, models & migrations
    'database/migrations/2026_09_16_070000_create_devices_table.php',
    'database/migrations/2026_09_16_070001_add_status_to_academic_years_table.php',
    'app/Models/Device.php',
    'app/Models/AcademicYear.php',
    'resources/views/guru/cbt/exams/results.blade.php',
    'app/Http/Controllers/PublicDisplayController.php',
    'app/Http/Controllers/Admin/UnifiedAttendanceController.php',
    'app/Http/Controllers/Admin/DashboardController.php',
    'app/Http/Controllers/Mobile/MobileDashboardController.php',
    'app/Repositories/AttendanceRepository.php',
    'app/Services/AttendanceStatisticsService.php',
];

// Deteksi file yang berubah di GitHub API secara otomatis (10 commit terakhir)
if (!empty($token)) {
    echo "<span class='info'>Memeriksa GitHub Commits API untuk mendeteksi perubahan file terbaru...</span>\n";
    $commitsApiUrl = "https://api.github.com/repos/{$repo}/commits?per_page=10";
    $ch = curl_init($commitsApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PembdaHUB-Updater');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token {$token}",
        "Accept: application/vnd.github.v3+json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200 && !empty($res)) {
        $commits = json_decode($res, true);
        if (is_array($commits)) {
            $dynamicList = [];
            foreach ($commits as $c) {
                if (empty($c['sha'])) continue;
                $detailUrl = "https://api.github.com/repos/{$repo}/commits/" . $c['sha'];
                $ch2 = curl_init($detailUrl);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_USERAGENT, 'PembdaHUB-Updater');
                curl_setopt($ch2, CURLOPT_HTTPHEADER, [
                    "Authorization: token {$token}",
                    "Accept: application/vnd.github.v3+json"
                ]);
                curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch2, CURLOPT_TIMEOUT, 15);
                $res2 = curl_exec($ch2);
                curl_close($ch2);

                if (!empty($res2)) {
                    $detailData = json_decode($res2, true);
                    if (isset($detailData['files']) && is_array($detailData['files'])) {
                        foreach ($detailData['files'] as $df) {
                            if (isset($df['filename']) && ($df['status'] ?? '') !== 'removed') {
                                $dynamicList[] = $df['filename'];
                            }
                        }
                    }
                }
            }
            if (!empty($dynamicList)) {
                $dynamicList = array_unique($dynamicList);
                echo "<span class='ok'>✔ Berhasil mendeteksi " . count($dynamicList) . " file dari riwayat commit GitHub terbaru.</span>\n";
                $files = array_values(array_unique(array_merge($dynamicList, $files)));
            }
        }
    }
}

$successCount = 0;
$failCount = 0;

foreach ($files as $file) {
    $content = null;
    $httpCode = 0;

    // Metode 1: Raw GitHub UserContent dengan Authorization Header
    $rawUrl = "https://raw.githubusercontent.com/{$repo}/{$branch}/{$file}";
    $ch = curl_init($rawUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PembdaHUB-Updater');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (!empty($token)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: token {$token}"]);
    }
    $rawContent = curl_exec($ch);
    $rawCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($rawCode === 200 && !empty($rawContent)) {
        $content = $rawContent;
        $httpCode = 200;
    } else {
        // Metode 2: Fallback ke GitHub Contents API
        $apiUrl = "https://api.github.com/repos/{$repo}/contents/{$file}?ref={$branch}";
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PembdaHUB-Updater');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $headers = ["Accept: application/vnd.github.v3.raw"];
        if (!empty($token)) {
            $headers[] = "Authorization: token {$token}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $apiContent = curl_exec($ch);
        $apiCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($apiCode === 200 && !empty($apiContent)) {
            $content = $apiContent;
            $httpCode = 200;
        } else {
            $httpCode = $rawCode ?: $apiCode;
        }
    }

    if ($httpCode === 200 && !empty($content)) {
        $dest = "{$root}/{$file}";
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($dest, $content);
        $fileSize = strlen($content);
        echo "<span class='ok'>✔ Berhasil memperbarui: {$file} (" . number_format($fileSize) . " bytes)</span>\n";
        $successCount++;
    } else {
        echo "<span class='err'>✖ Gagal mengunduh (HTTP {$httpCode}): {$file}</span>\n";
        $failCount++;
    }

    usleep(150000); // 150ms delay mencegah rate limiting
}

// Reset OPcache & view cache
echo "\n--- Pembersihan Cache ---\n";
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "<span class='ok'>✔ OPcache reset berhasil</span>\n";
}

$cacheFiles = ['config.php', 'routes-v7.php', 'packages.php', 'services.php', 'events.php'];
foreach ($cacheFiles as $cf) {
    $fp = "{$root}/bootstrap/cache/{$cf}";
    if (file_exists($fp)) {
        @unlink($fp);
        echo "<span class='ok'>✔ Cache dihapus: {$cf}</span>\n";
    }
}

// Hapus compiled views
$viewDir = "{$root}/storage/framework/views/";
if (is_dir($viewDir)) {
    $vCount = 0;
    foreach (glob($viewDir . '*.php') as $v) {
        if (@unlink($v)) $vCount++;
    }
    echo "<span class='ok'>✔ Compiled views dibersihkan ({$vCount} views)</span>\n";
}

// Coba juga git sync jika token ada
if (!empty($token)) {
    echo "\n--- Percobaan Sinkronisasi Git Lokal ---\n";
    $gitCmd = "git -C {$root} fetch origin main 2>&1 && git -C {$root} reset --hard origin/main 2>&1";
    $gitOut = [];
    $gitRet = -1;
    @exec($gitCmd, $gitOut, $gitRet);
    if ($gitRet === 0) {
        echo "<span class='ok'>✔ Git Local Sync: Berhasil disinkronkan via Git!</span>\n";
    } else {
        echo "<span class='warn'>⚠ Git Sync code {$gitRet}: " . implode(' ', array_slice($gitOut, -2)) . " (File sudah disinkronkan langsung di atas)</span>\n";
    }
}

echo "</pre>";
echo "<h2 style='color:#3fb950;'>🎉 UPDATE SELESAI ({$successCount} file diperbarui, {$failCount} gagal)</h2>";
echo "<p><a href='/display1' style='background:#238636;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;margin-right:10px;'>→ Buka Live Display SMP (/display1)</a>";
echo "<a href='/git_pull_now.php?secret=pembda99' style='background:#1f6feb;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;'>→ Jalankan git_pull_now.php</a></p>";
echo "</body></html>";

