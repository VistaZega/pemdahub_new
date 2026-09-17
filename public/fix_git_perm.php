<?php
/**
 * PembdaHUB - Git Permission Auto-Repair & Ownership Fixer
 * Akses: https://perguruanpembda.com/fix_git_perm.php?secret=pembda99
 * Auto Fix: https://perguruanpembda.com/fix_git_perm.php?secret=pembda99&action=fix
 */
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden: Invalid secret key.');
}

@ini_set('max_execution_time', '180');
@set_time_limit(180);
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>PembdaHUB Git Permission Repair</title>";
echo "<style>
body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:24px;line-height:1.6;font-size:14px;}
.ok{color:#3fb950;font-weight:bold;} .warn{color:#d29922;font-weight:bold;} .err{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}
pre{background:#161b22;border:1px solid #30363d;padding:16px;border-radius:8px;overflow-x:auto;white-space:pre-wrap;}
h1{color:#58a6ff;border-bottom:1px solid #30363d;padding-bottom:10px;} h2{color:#79c0ff;margin-top:20px;}
.box{background:#161b22;border:1px solid #30363d;border-radius:8px;padding:16px;margin:16px 0;}
.btn{display:inline-block;padding:10px 18px;background:#238636;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;margin-top:10px;}
.btn:hover{background:#2ea043;}
.btn-blue{background:#1f6feb;} .btn-blue:hover{background:#388bfd;}
</style></head><body>";

echo "<h1>🛠️ PembdaHUB - Perbaikan Izin Tulis Git (.git Permission Repair)</h1>";

// Deteksi Root Folder
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

$currentUser = trim(@shell_exec('whoami') ?: 'www-data');
$envFile = "{$root}/.env";
$token = '';
if (file_exists($envFile)) {
    $envContent = @file_get_contents($envFile);
    if (preg_match('/^GITHUB_DEPLOY_TOKEN=(.*)$/m', $envContent, $matches)) {
        $token = trim($matches[1], "\"' \r\n");
    }
}
if (empty($token) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
}

echo "<div class='box'>";
echo "<h3>1. Informasi Sistem & Lingkungan Server</h3>";
echo "Root Aplikasi   : <b>" . htmlspecialchars($root) . "</b><br>";
echo "User Web Server : <b>" . htmlspecialchars($currentUser) . "</b><br>";

$gitDir = "{$root}/.git";
$gitObjects = "{$root}/.git/objects";
$gitPack = "{$root}/.git/objects/pack";

$isGitDirWritable = is_dir($gitDir) && is_writable($gitDir);
$isObjectsWritable = is_dir($gitObjects) && is_writable($gitObjects);
$isPackWritable = is_dir($gitPack) && is_writable($gitPack);

echo "Status Folder .git          : " . ($isGitDirWritable ? "<span class='ok'>WRITEABLE (OK)</span>" : "<span class='err'>READ-ONLY / TIDAK BISA DITULIS</span>") . "<br>";
echo "Status Folder .git/objects  : " . ($isObjectsWritable ? "<span class='ok'>WRITEABLE (OK)</span>" : "<span class='err'>READ-ONLY / TIDAK BISA DITULIS</span>") . "<br>";
echo "Status Folder objects/pack  : " . ($isPackWritable ? "<span class='ok'>WRITEABLE (OK)</span>" : "<span class='err'>READ-ONLY / TIDAK BISA DITULIS (Penyebab Error!)</span>") . "<br>";
echo "</div>";

$action = $_GET['action'] ?? '';

if ($action === 'fix') {
    echo "<h2>▶ 2. Menjalankan Perbaikan Izin Git</h2><pre>";

    // Langkah A: Coba chmod / chown via shell / sudo
    echo "<span class='info'>[Langkah A] Mencoba menyesuaikan permissions folder .git...</span>\n";
    @shell_exec("chmod -R 777 " . escapeshellarg($gitDir) . " 2>&1");
    @shell_exec("sudo -n chmod -R 777 " . escapeshellarg($gitDir) . " 2>&1");
    @shell_exec("sudo -n chown -R " . escapeshellarg($currentUser) . ":" . escapeshellarg($currentUser) . " " . escapeshellarg($gitDir) . " 2>&1");

    // Clear PHP stat cache
    clearstatcache();
    $isPackWritable = is_dir($gitPack) && is_writable($gitPack);

    if ($isPackWritable) {
        echo "<span class='ok'>✔ Berhasil! Folder .git/objects/pack sekarang memiliki izin tulis penuh (777).</span>\n";
    } else {
        echo "<span class='warn'>⚠ Folder .git/objects/pack masih dimiliki oleh root/user lain.</span>\n";
        echo "<span class='info'>[Langkah B] Menginisialisasi ulang metadata kepemilikan .git sebagai user {$currentUser}...</span>\n";

        // Ambil URL remote yang aktif
        $currentRemote = trim(@shell_exec("git -C {$root} remote get-url origin 2>/dev/null") ?: '');
        if (empty($currentRemote) || strpos($currentRemote, 'github.com') === false) {
            if (!empty($token)) {
                $currentRemote = "https://{$token}@github.com/VistaZega/pemdahub_new.git";
            } else {
                $currentRemote = "https://github.com/VistaZega/pemdahub_new.git";
            }
        }

        // Rename folder .git lama yang terkunci izinnya
        $backupGit = "{$root}/.git_old_" . time();
        $renamed = @rename($gitDir, $backupGit);

        if ($renamed) {
            echo "<span class='ok'>✔ Folder .git lama dialihkan sementara ke: {$backupGit}</span>\n";

            // Inisialisasi ulang .git baru sebagai user web server saat ini
            echo "<span class='info'>Menginisialisasi .git baru atas nama user '{$currentUser}'...</span>\n";
            @shell_exec("git -C {$root} init");
            @shell_exec("git -C {$root} config --global --add safe.directory '*' ");
            @shell_exec("git -C {$root} config --global --add safe.directory " . escapeshellarg($root));
            @shell_exec("git -C {$root} config core.sharedRepository all");
            @shell_exec("git -C {$root} remote add origin " . escapeshellarg($currentRemote));

            echo "<span class='info'>Mengunduh metadata commit terbaru dari origin/main...</span>\n";
            $fetchOut = @shell_exec("git -C {$root} fetch origin main 2>&1");
            echo htmlspecialchars($fetchOut) . "\n";

            echo "<span class='info'>Menyelaraskan index git ke origin/main (tanpa mengubah file kerja)...</span>\n";
            $resetOut = @shell_exec("git -C {$root} reset --mixed origin/main 2>&1");
            echo htmlspecialchars($resetOut) . "\n";

            clearstatcache();
            if (is_dir("{$root}/.git/objects/pack") && is_writable("{$root}/.git/objects/pack")) {
                echo "<span class='ok'>🎉 SUKSES BESAR: Folder .git baru telah dibuat dan 100% dimiliki oleh user {$currentUser}!</span>\n";
                // Bersihkan backup lama
                @shell_exec("rm -rf " . escapeshellarg($backupGit) . " 2>/dev/null");
            } else {
                echo "<span class='warn'>⚠ Folder .git telah diperbarui. Silakan periksa status di bawah.</span>\n";
            }
        } else {
            echo "<span class='err'>✖ Gagal mengalihkan folder .git. Parent directory mungkin memerlukan izin tambahan.</span>\n";
        }
    }
    echo "</pre>";

    // Re-check status
    clearstatcache();
    $isPackWritable = is_dir($gitPack) && is_writable($gitPack);
    if ($isPackWritable) {
        echo "<div class='box' style='border-color:#3fb950;'>";
        echo "<h3 style='color:#3fb950;margin-top:0;'>✅ Masalah Permission Selesai Secara Permanen!</h3>";
        echo "<p>Folder Git sekarang memiliki izin tulis penuh bagi proses web server. Anda dapat kembali menggunakan fitur deploy satu klik:</p>";
        echo "<a href='git_pull_now.php?secret=pembda99' class='btn'>🚀 Buka git_pull_now.php (Deploy Sekarang)</a>";
        echo "</div>";
    }
}

if (!$isPackWritable) {
    echo "<div class='box' style='border-color:#f85149;'>";
    echo "<h3 style='color:#f85149;margin-top:0;'>⚠️ Penyebab & Cara Penyelesaian Permanen</h3>";
    echo "<p>Error <code>Permission denied</code> terjadi karena folder <code>.git</code> dibuat/dimiliki oleh user <b>root</b>, sedangkan proses PHP web server berjalan sebagai user <b>{$currentUser}</b>.</p>";

    echo "<h4>Pilihan 1: Perbaiki Otomatis Melalui Browser (Direkomendasikan)</h4>";
    echo "<p>Klik tombol di bawah untuk meminta skrip secara otomatis memperbaiki izin atau menginisialisasi ulang kepemilikan <code>.git</code> ke user <b>{$currentUser}</b>:</p>";
    echo "<a href='fix_git_perm.php?secret=pembda99&action=fix' class='btn'>🔧 Jalankan Perbaikan Otomatis Sekarang</a>";

    echo "<h4 style='margin-top:24px;'>Pilihan 2: Jalankan Perintah Terminal (Jika Anda Memiliki Akses SSH / Terminal)</h4>";
    echo "<p>Jika Anda memiliki akses SSH ke server VPS, jalankan 2 baris perintah berikut di terminal:</p>";
    echo "<pre style='background:#000;color:#00ff66;font-size:13px;'>";
    echo "sudo chown -R {$currentUser}:{$currentUser} " . htmlspecialchars($root) . "/.git\n";
    echo "sudo chmod -R 775 " . htmlspecialchars($root) . "/.git";
    echo "</pre>";
    echo "<p>Setelah perintah di atas dijalankan satu kali di terminal, masalah izin tulis akan hilang selamanya.</p>";
    echo "</div>";
} else {
    echo "<div class='box' style='border-color:#3fb950;'>";
    echo "<h3 style='color:#3fb950;margin-top:0;'>✅ Kondisi Folder .git Saat Ini: Normal & Writeable</h3>";
    echo "<p>Folder <code>.git/objects/pack</code> dapat ditulis dengan lancar oleh user <b>{$currentUser}</b>.</p>";
    echo "<a href='git_pull_now.php?secret=pembda99' class='btn'>🚀 Buka git_pull_now.php (Deploy Sekarang)</a>";
    echo "</div>";
}

echo "<p style='margin-top:30px;color:#8b949e;font-size:12px;'>PembdaHUB Server Infrastructure Tools &bull; 2026</p>";
echo "</body></html>";
