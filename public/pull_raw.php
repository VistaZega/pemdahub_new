<?php
/**
 * Standalone Direct File Updater via GitHub API
 * Akses: https://perguruanpembda.com/pull_raw.php?secret=pembda99
 * Dengan Token: https://perguruanpembda.com/pull_raw.php?secret=pembda99&token=ghp_xxx
 */
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden: Invalid secret key.');
}

@ini_set('max_execution_time', '180');
@set_time_limit(180);
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Direct GitHub File Updater</title>";
echo "<style>body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:24px;line-height:1.6;font-size:14px;}";
echo ".ok{color:#3fb950;font-weight:bold;} .warn{color:#d29922;} .err{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}";
echo "pre{background:#161b22;border:1px solid #30363d;padding:16px;border-radius:8px;overflow-x:auto;}";
echo "</style></head><body>";
echo "<h1>🚀 PembdaHUB Direct File Sync (GitHub API)</h1><pre>";

$root = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub';
$repo = 'VistaZega/pemdahub_new';
$branch = 'main';

// Baca token dari query atau .env
$token = trim($_GET['token'] ?? '');
if (empty($token) && file_exists("{$root}/.env")) {
    $envContent = @file_get_contents("{$root}/.env");
    if (preg_match('/^GITHUB_DEPLOY_TOKEN=(.*)$/m', $envContent, $matches)) {
        $token = trim($matches[1], "\"' \r\n");
    }
}

$files = [
    'public/git_pull_now.php',
    'resources/views/auth/login.blade.php',
    'resources/views/index.blade.php',
    'resources/views/landing/partials/hero.blade.php',
    'resources/views/landing/partials/navigation.blade.php',
    'resources/views/landing/partials/unit-sekolah.blade.php',
    'resources/views/landing/partials/hall-of-fame.blade.php',
    'resources/views/landing/partials/showcase.blade.php',
    'resources/views/landing/partials/kegiatan-siswa.blade.php',
    'resources/views/landing/partials/footer.blade.php',
    'resources/views/public/pkl_map.blade.php',
    'routes/web.php',
    'app/Http/Controllers/Admin/NewsController.php',
    'app/Http/Controllers/Admin/GalleryController.php',
    'resources/views/admin/news/form.blade.php',
    'app/Models/User.php',
    'resources/views/profile/settings.blade.php',
    'app/Http/Controllers/ProfileSettingsController.php',
];

$successCount = 0;
$failCount = 0;

$headers = ["Accept: application/vnd.github.v3.raw"];
if (!empty($token)) {
    $headers[] = "Authorization: Bearer {$token}";
}

foreach ($files as $file) {
    $apiUrl = "https://api.github.com/repos/{$repo}/contents/{$file}?ref={$branch}";
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PembdaHUB-Updater');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($content)) {
        $dest = "{$root}/{$file}";
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($dest, $content);
        echo "<span class='ok'>✔ Berhasil memperbarui: {$file}</span>\n";
        $successCount++;
    } else {
        echo "<span class='err'>✖ Gagal mengunduh (HTTP {$httpCode}): {$file}</span>\n";
        $failCount++;
    }
}

// Reset OPcache & view cache
echo "\n--- Pembersihan Cache ---\n";
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "<span class='ok'>✔ OPcache reset berhasil</span>\n";
}

$cacheFiles = ['config.php', 'routes-v7.php', 'packages.php', 'services.php'];
foreach ($cacheFiles as $cf) {
    $fp = "{$root}/bootstrap/cache/{$cf}";
    if (file_exists($fp)) {
        @unlink($fp);
        echo "<span class='ok'>✔ Cache dihapus: {$cf}</span>\n";
    }
}

if (!empty($token)) {
    $gitCmd = "git -C {$root} remote set-url origin https://{$token}@github.com/{$repo}.git && git -C {$root} fetch origin main && git -C {$root} reset --hard origin/main";
    @exec($gitCmd, $gitOut, $gitRet);
    if ($gitRet === 0) {
        echo "<span class='ok'>✔ Git Local Sync: Berhasil disinkronkan ke commit terbaru GitHub!</span>\n";
    }
}

echo "</pre>";
echo "<h2 style='color:#3fb950;'>🎉 UPDATE SELESAI ({$successCount} file diperbarui)</h2>";
echo "<p><a href='/' style='background:#238636;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;'>← Buka Halaman Utama PembdaHUB</a></p>";
echo "</body></html>";
