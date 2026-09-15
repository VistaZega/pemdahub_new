<?php
/**
 * Fix Storage Permissions - Perbaiki permission storage/framework/views/
 * Akses: https://perguruanpembda.com/fix-storage-permission.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Access denied.');
}
echo "<pre style='background:#111;color:#0f0;padding:20px;font-family:monospace;'>";

$base = __DIR__ . '/..';
$dirs = [
    $base . '/storage/framework/views/',
    $base . '/storage/framework/cache/',
    $base . '/storage/framework/sessions/',
    $base . '/storage/logs/',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        echo "❌ NOT EXISTS: {$dir}\n";
        continue;
    }
    $initial = substr(sprintf('%o', fileperms($dir)), -3);
    @chmod($dir, 0777);
    $after = substr(sprintf('%o', fileperms($dir)), -3);
    echo "📁 {$dir}\n";
    echo "   Permission: {$initial} → {$after}\n";
    
    // Fix all existing files
    $files = glob($dir . '*');
    $fixed = 0;
    foreach ($files as $f) {
        if (is_file($f) && @chmod($f, 0666)) $fixed++;
    }
    echo "   Fixed {$fixed} files inside.\n";
}

// Also try to create a test file
$testFile = $base . '/storage/framework/views/_test_write.tmp';
$testResult = @file_put_contents($testFile, 'ok');
if ($testResult !== false) {
    @unlink($testFile);
    echo "\n✅ Write test: SUCCESS (storage writable)\n";
} else {
    echo "\n❌ Write test: FAILED (storage NOT writable)\n";
}

echo "\n🎉 Done!</pre>";
