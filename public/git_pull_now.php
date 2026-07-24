<?php
/**
 * One-Click Server Auto-Pull & Migration Tool for PembdaHUB
 * Akses: https://perguruanpembda.com/git_pull_now.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { http_response_code(403); die('Forbidden'); }

header('Content-Type: text/plain; charset=utf-8');

$root = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub';

echo "=== GIT PULL, MIGRATE & UPDATE ===\n\n";

// 1. Fetch latest
echo "--- 1. Fetch ---\n";
echo shell_exec("GIT_SSH_COMMAND='ssh -o StrictHostKeyChecking=no' git -C {$root} fetch origin 2>&1") . "\n";

// 2. Show before
echo "--- 2. Sebelum Update ---\n";
echo "HEAD: " . trim(shell_exec("git -C {$root} rev-parse --short HEAD 2>&1")) . "\n";
echo "origin/main: " . trim(shell_exec("git -C {$root} rev-parse --short origin/main 2>&1")) . "\n\n";

// 3. Reset to latest
echo "--- 3. Update ke origin/main ---\n";
echo shell_exec("git -C {$root} reset --hard origin/main 2>&1") . "\n";

// 4. Show after
echo "--- 4. Sesudah Update ---\n";
echo "HEAD: " . trim(shell_exec("git -C {$root} rev-parse --short HEAD 2>&1")) . "\n";
echo shell_exec("git -C {$root} log --oneline -5 2>&1") . "\n";

// 5. Clear cache & OPcache
echo "--- 5. Clear Cache & OPcache ---\n";
if (function_exists('opcache_reset')) {
    if (@opcache_reset()) {
        echo "OPcache reset: OK\n";
    } else {
        echo "OPcache reset: FAILED\n";
    }
}

foreach (['config.php','routes-v7.php','packages.php','services.php','events.php'] as $cf) {
    $fp = "{$root}/bootstrap/cache/{$cf}";
    if (file_exists($fp) && @unlink($fp)) echo "Deleted: bootstrap/cache/{$cf}\n";
}

// 6. Run Database Migrations & Clear Laravel Cache via Artisan
echo "\n--- 6. Run Database Migrations ---\n";
try {
    require_once "{$root}/vendor/autoload.php";
    $app = require_once "{$root}/bootstrap/app.php";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true], $output);
    echo "Migration Result:\n" . trim($output->fetch()) . "\n";

    $outputClear = new \Symfony\Component\Console\Output\BufferedOutput();
    \Illuminate\Support\Facades\Artisan::call('view:clear', [], $outputClear);
    echo "View Clear Result: " . trim($outputClear->fetch()) . "\n";
} catch (\Exception $e) {
    echo "Migration Notice: " . $e->getMessage() . "\n";
}

echo "\n=== UPDATE & MIGRATION SELESAI DENGAN SUKSES! ===\n";
echo "Forum: https://perguruanpembda.com/forum\n";
