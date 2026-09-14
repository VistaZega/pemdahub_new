<?php
/**
 * One-Click Server Auto-Pull & Migration Tool for PembdaHUB
 * Akses: https://perguruanpembda.com/git_pull_now.php?secret=pembda99
 * Dengan Token: https://perguruanpembda.com/git_pull_now.php?secret=pembda99&token=ghp_xxx
 */
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden: Invalid secret key.');
}

// Disable output buffering for live stream output
@ini_set('max_execution_time', '180');
@set_time_limit(180);
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
@ini_set('implicit_flush', true);
while (@ob_end_flush());
ob_implicit_flush(true);

header('Content-Type: text/html; charset=utf-8');
header('X-Accel-Buffering: no');

echo "<!DOCTYPE html><html><head><title>PembdaHUB Git Auto-Deploy</title>";
echo "<style>body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:24px;line-height:1.6;font-size:14px;}";
echo ".ok{color:#3fb950;font-weight:bold;} .warn{color:#d29922;} .err{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}";
echo "pre{background:#161b22;border:1px solid #30363d;padding:16px;border-radius:8px;overflow-x:auto;white-space:pre-wrap;}";
echo "h1{color:#58a6ff;border-bottom:1px solid #30363d;padding-bottom:10px;} h2{color:#79c0ff;margin-top:24px;}";
echo ".notice-box{background:#1f242c;border:1px solid #388bfd;border-radius:8px;padding:16px;margin:20px 0;}";
echo "</style></head><body>";
echo "<h1>🚀 PembdaHUB One-Click Git Pull & Deploy</h1>";
flush();

// Auto-detect root folder (bisa di Ubuntu /var/www/pembdahub maupun di Hostinger)
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
$envFile = "{$root}/.env";

// 1. Baca token yang tersimpan di server (.env)
$savedToken = '';
if (file_exists($envFile)) {
    $envContent = @file_get_contents($envFile);
    if (preg_match('/^GITHUB_DEPLOY_TOKEN=(.*)$/m', $envContent, $matches)) {
        $savedToken = trim($matches[1], "\"' \r\n");
    }
}

// 2. Jika ada token baru via query string $_GET['token'], simpan permanen ke .env server
$githubToken = trim($_GET['token'] ?? '');
if (!empty($githubToken)) {
    if (file_exists($envFile)) {
        if (strpos($envContent, 'GITHUB_DEPLOY_TOKEN=') !== false) {
            $newEnvContent = preg_replace('/^GITHUB_DEPLOY_TOKEN=.*$/m', "GITHUB_DEPLOY_TOKEN={$githubToken}", $envContent);
        } else {
            $newEnvContent = $envContent . "\nGITHUB_DEPLOY_TOKEN={$githubToken}\n";
        }
        @file_put_contents($envFile, $newEnvContent);
        echo "<div class='notice-box' style='border-color:#3fb950;'><span class='ok'>✔ Token GitHub berhasil disimpan permanen ke server (.env). Mulai sekarang deploy dapat dijalankan tanpa mengetik token lagi!</span></div>";
    }
} else {
    $githubToken = $savedToken;
}

// Prevent Git from hanging on authentication prompts
putenv('GIT_TERMINAL_PROMPT=0');
putenv('GIT_ASKPASS=/bin/echo');
putenv('GIT_SSH_COMMAND=ssh -o BatchMode=yes -o StrictHostKeyChecking=no');

$lastCmdOutput = '';
$lastCmdError = '';

function execCmd($cmd, $label) {
    global $lastCmdOutput, $lastCmdError;
    echo "<h2>▶ {$label}</h2><pre>";
    flush();
    $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    $process = proc_open($cmd, $descriptors, $pipes);
    $output = '';
    $errors = '';
    $return_value = -1;

    if (is_resource($process)) {
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $return_value = proc_close($process);

        $lastCmdOutput = $output;
        $lastCmdError = $errors;

        if (!empty($output)) {
            echo "<span class='ok'>" . htmlspecialchars($output) . "</span>";
        }
        if (!empty($errors)) {
            echo "<span class='warn'>" . htmlspecialchars($errors) . "</span>";
        }
        if ($return_value === 0) {
            echo "\n<span class='ok'>✔ Status: Berhasil (Exit Code 0)</span>";
        } else {
            echo "\n<span class='err'>✖ Status: Exit Code {$return_value}</span>";
        }
    } else {
        echo "<span class='err'>Gagal menjalankan proses sistem.</span>";
    }
    echo "</pre>";
    flush();
    return $return_value;
}

// 1. Cek Remote URL Saat Ini
execCmd("git -C {$root} remote -v", "1. Memeriksa Remote URL Saat Ini");

// 2. Set Remote URL jika ada GitHub Token & Optimasi Git Memory
if (file_exists("{$root}/.git/gc.log")) {
    @unlink("{$root}/.git/gc.log");
}
@shell_exec("git -C {$root} config gc.auto 0");
@shell_exec("git -C {$root} config core.sharedRepository all");
@shell_exec("git -C {$root} config safe.directory '*' ");

// Coba perbaiki permissions folder .git jika PHP memiliki akses
if (is_dir("{$root}/.git")) {
    @chmod("{$root}/.git", 0777);
    @chmod("{$root}/.git/objects", 0777);
}

if (!empty($githubToken)) {
    $maskedToken = substr($githubToken, 0, 7) . '...' . substr($githubToken, -4);
    echo "<h2>▶ 2. Otentikasi GitHub Token</h2><pre><span class='ok'>✔ Token aktif terdeteksi: {$maskedToken}</span></pre>";
    $authRepoUrl = "https://{$githubToken}@github.com/VistaZega/pemdahub_new.git";
    execCmd("git -C {$root} remote set-url origin {$authRepoUrl}", "Menyelaraskan Remote URL dengan Kredensial Token");
}

// 3. Fetch data terbaru dari GitHub
$fetchStatus = execCmd("git -C {$root} -c gc.auto=0 fetch origin main --prune", "3. Mengunduh Perubahan Terbaru (Git Fetch)");

if ($fetchStatus !== 0) {
    echo "<div class='notice-box' style='border-color:#f85149;'>";
    if (strpos($lastCmdError, 'insufficient permission') !== false || strpos($lastCmdOutput, 'insufficient permission') !== false) {
        echo "<h3 style='color:#f85149;margin-top:0;'>⚠️ PERHATIAN: Izin Tulis Folder Git Terkunci (Permission Denied)</h3>";
        echo "<p>Token GitHub sudah <b>VALID & DITERIMA</b>, namun Git di server gagal menulis file objek ke folder <code>.git/objects</code> karena folder tersebut dimiliki oleh user lain (seperti <code>root</code>).</p>";
        echo "<p><b>Solusi:</b> Buka Terminal / SSH server Anda, lalu jalankan perintah perbaikan izin berikut:</p>";
        echo "<pre style='background:#0d1117;color:#38bdf8;padding:12px;border-radius:6px;font-size:13px;border:1px solid #388bfd;'>sudo chown -R www-data:www-data {$root}/.git\nsudo chmod -R 775 {$root}/.git</pre>";
        echo "<p style='color:#7ee787;'>Setelah menjalankan perintah di atas, cukup refresh halaman ini!</p>";
    } elseif (empty($githubToken) || strpos($lastCmdError, 'Permission denied (publickey)') !== false) {
        echo "<h3 style='color:#f85149;margin-top:0;'>⚠️ PERHATIAN: Git Fetch Gagal (Memerlukan Token)</h3>";
        echo "<p>Karena repositori GitHub ini bersifat privat, silakan jalankan dengan menyertakan token sekali saja:</p>";
        echo "<p><code>https://perguruanpembda.com/git_pull_now.php?secret=pembda99&token=ghp_TOKEN_ANDA</code></p>";
    } else {
        echo "<h3 style='color:#f85149;margin-top:0;'>⚠️ PERHATIAN: Git Fetch Gagal</h3>";
        echo "<p>Periksa detail pesan kesalahan pada langkah 3 di atas.</p>";
    }
    echo "</div>";
    flush();
}

// 4. Status Commit Sebelum Update
execCmd("git -C {$root} log -1 --oneline", "4. Commit Server Saat Ini (Sebelum Update)");

// 5. Reset Hard ke origin/main jika fetch berhasil
if ($fetchStatus === 0) {
    execCmd("git -C {$root} reset --hard origin/main", "5. Menerapkan Update Kode (Git Reset Hard)");
} else {
    echo "<h2>▶ 5. Menerapkan Update Kode</h2><pre><span class='warn'>Dilewati karena Git Fetch gagal. Server tetap pada commit saat ini.</span></pre>";
}

// 6. Status Commit Setelah Update
execCmd("git -C {$root} log -3 --oneline", "6. Commit Server Terbaru (Sesudah Update)");

// 7. Reset OPcache & Bersihkan Cache File Laravel
echo "<h2>▶ 7. Pembersihan Cache Aplikasi & OPcache</h2><pre>";
if (function_exists('opcache_reset')) {
    if (@opcache_reset()) {
        echo "<span class='ok'>✔ OPcache Memory Reset: SUKSES</span>\n";
    } else {
        echo "<span class='warn'>⚠ OPcache Memory Reset: TIDAK AKTIF / GAGAL</span>\n";
    }
}

$cacheFiles = ['config.php', 'routes-v7.php', 'packages.php', 'services.php', 'events.php'];
foreach ($cacheFiles as $cf) {
    $fp = "{$root}/bootstrap/cache/{$cf}";
    if (file_exists($fp)) {
        if (@unlink($fp)) {
            echo "<span class='ok'>✔ Berhasil menghapus cache: bootstrap/cache/{$cf}</span>\n";
        }
    }
}
echo "</pre>";
flush();

// 8. Menjalankan Migrasi Database & Seeder
echo "<h2>▶ 8. Eksekusi Migrasi Database & Update Realtime</h2><pre>";
try {
    if (file_exists("{$root}/vendor/autoload.php")) {
        require_once "{$root}/vendor/autoload.php";
        $app = require_once "{$root}/bootstrap/app.php";
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true], $output);
        echo "<span class='ok'>" . htmlspecialchars($output->fetch()) . "</span>\n";

        $outputClear = new \Symfony\Component\Console\Output\BufferedOutput();
        \Illuminate\Support\Facades\Artisan::call('view:clear', [], $outputClear);
        echo "<span class='ok'>View Cache: " . htmlspecialchars(trim($outputClear->fetch())) . "</span>\n";
    }
} catch (\Throwable $e) {
    $diag = class_exists('\App\Services\ErrorDiagnosticService') 
        ? \App\Services\ErrorDiagnosticService::diagnose($e)
        : [
            'type' => 'Eksepsi Sistem',
            'danger_label' => 'Perlu Pemeriksaan',
            'problem' => $e->getMessage(),
            'impact' => 'Operasi bootstrap terhenti.',
            'solution' => 'Periksa catatan file log Laravel.'
        ];

    $copyPayload = "📋 [LAPORAN DEPLOYMENT PEMBDAHUB]\n" .
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
        "🏷️ Kategori: " . strip_tags($diag['type']) . " (" . strip_tags($diag['danger_label']) . ")\n" .
        "🛑 Masalah: " . strip_tags($diag['problem']) . "\n" .
        "⚠️ Dampak: " . strip_tags($diag['impact']) . "\n" .
        "🛠️ Solusi: " . strip_tags($diag['solution']) . "\n" .
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
        "💻 Detail Teknis:\n" . $e->getMessage();

    $jsonCopy = htmlspecialchars(json_encode($copyPayload), ENT_QUOTES, 'UTF-8');

    echo "<span class='err'>Detail Teknis: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "</pre>";
    echo "<div style='background:#181c24;border:1.5px solid #f59e0b;border-radius:12px;padding:16px 20px;margin:14px 0;color:#f8fafc;line-height:1.6;font-family:sans-serif;'>";
    echo "<div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;'>";
    echo "<h4 style='color:#fbbf24;margin:0;font-size:15px;'>📋 PENJELASAN DIAGNOSTIK SISTEM (BAHASA INDONESIA)</h4>";
    echo "<button type='button' onclick='copyDeployDiag(this, {$jsonCopy})' style='background:#f59e0b;color:#0f172a;border:none;padding:5px 12px;border-radius:6px;font-weight:bold;font-size:12px;cursor:pointer;'>📋 Salin Kode & Diagnostik</button>";
    echo "</div>";
    echo "<p style='margin:6px 0;'><b>🏷️ Kategori:</b> <span style='background:#334155;padding:2px 8px;border-radius:6px;font-size:12px;font-family:monospace;'>" . htmlspecialchars($diag['type']) . "</span> &nbsp; <b>Status Bahaya:</b> {$diag['danger_label']}</p>";
    echo "<p style='margin:8px 0;'><b>🛑 Masalah:</b> {$diag['problem']}</p>";
    echo "<p style='margin:8px 0;'><b>⚠️ Dampak / Akibat:</b> {$diag['impact']}</p>";
    echo "<p style='margin:8px 0;color:#38bdf8;'><b>🛠️ Solusi / Yang Perlu Dilakukan:</b> {$diag['solution']}</p>";
    echo "</div><pre>";
}
echo "</pre>";
flush();

echo "<h2 style='color:#3fb950;'>🎉 PROSES PEMERIKSAAN SERVER SELESAI</h2>";
echo "<p><a href='/' style='background:#238636;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;'>← Buka Halaman Utama PembdaHUB</a></p>";
echo "<script>
function copyDeployDiag(btn, text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            btn.innerHTML = '✔ Tersalin!';
            btn.style.background = '#10b981';
            btn.style.color = '#ffffff';
        });
    } else {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            btn.innerHTML = '✔ Tersalin!';
            btn.style.background = '#10b981';
            btn.style.color = '#ffffff';
        } catch(e) {
            alert('Gagal menyalin otomatis.');
        }
        document.body.removeChild(ta);
    }
}
</script>";
echo "</body></html>";
