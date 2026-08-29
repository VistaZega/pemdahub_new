<?php
/**
 * Git Fix - Ubah remote ke HTTPS new_pembdahub.git lalu fetch + reset + migrate
 * Akses: https://perguruanpembda.com/git_fix.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { http_response_code(403); die('Access denied.'); }

echo "<html><head><title>Git Fix HTTPS</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.8}.ok{color:#00e676}.err{color:#ff5252}.info{color:#40c4ff}.warn{color:#ffc107}h1{color:#bb86fc}h2{color:#03dac6}pre{background:#16213e;padding:12px;border-radius:8px;overflow-x:auto;font-size:13px}</style></head><body>";
echo "<h1>🔧 Git Fix: Remote HTTPS + Force Reset</h1>";

$laravelRoot = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub';
$httpsRemote = 'https://github.com/VistaZega/pemdahub_new.git';

echo "<p class='info'>ℹ️ Laravel root: <b>$laravelRoot</b></p>";

function runCmd(string $cmd, string $label): bool {
    echo "<h2>$label</h2>";
    $output = [];
    $retCode = 0;
    exec($cmd . ' 2>&1', $output, $retCode);
    $out = implode("\n", $output);
    $class = $retCode === 0 ? 'ok' : 'err';
    echo "<pre class='$class'>" . htmlspecialchars($out ?: '(kosong)') . "</pre>";
    if ($retCode !== 0) echo "<p class='err'>⚠️ Exit code: $retCode</p>";
    return $retCode === 0;
}

// 1. Cek remote URL saat ini
runCmd("git -C $laravelRoot remote get-url origin", "1️⃣ Remote URL Saat Ini");

// 2. Ubah remote ke HTTPS repo yang benar (new_pembdahub)
runCmd("git -C $laravelRoot remote set-url origin $httpsRemote", "2️⃣ Ubah Remote → HTTPS (new_pembdahub)");

// 3. Konfirmasi perubahan
runCmd("git -C $laravelRoot remote get-url origin", "3️⃣ Konfirmasi Remote Baru");

// 4. Git fetch via HTTPS
$fetchOk = runCmd("git -C $laravelRoot fetch origin main", "4️⃣ Git Fetch (HTTPS)");

// 5. Reset hard ke origin/main
if ($fetchOk) {
    runCmd("git -C $laravelRoot reset --hard origin/main", "5️⃣ Force Reset ke origin/main");
} else {
    echo "<h2>5️⃣ Force Reset</h2>";
    echo "<p class='err'>❌ Fetch gagal. Mencoba git pull direct...</p>";
    runCmd("git -C $laravelRoot pull origin main --rebase", "5️⃣ Direct Pull");
}

// 6. Cek commit terbaru
runCmd("git -C $laravelRoot log --oneline -5", "6️⃣ Git Log Terbaru");

// 7. Run Database Migration & Clear Cache
echo "<h2>7️⃣ Run Migration & Clear Cache</h2><pre>";
try {
    require_once "$laravelRoot/vendor/autoload.php";
    $app = require_once "$laravelRoot/bootstrap/app.php";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true], $output);
    echo "<span class='ok'>✅ Migration Output:\n" . htmlspecialchars(trim($output->fetch())) . "</span>\n\n";

    $output2 = new \Symfony\Component\Console\Output\BufferedOutput();
    \Illuminate\Support\Facades\Artisan::call('view:clear', [], $output2);
    echo "<span class='ok'>✅ View Clear: " . htmlspecialchars(trim($output2->fetch())) . "</span>\n";
} catch (\Exception $e) {
    echo "<span class='err'>❌ Artisan Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}
echo "</pre>";

echo "<p class='ok'>🎉 Selesai! Git repository telah dipulihkan ke new_pembdahub.git!</p>";
echo "</body></html>";
