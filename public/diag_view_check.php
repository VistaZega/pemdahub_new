<?php
/**
 * Diagnostik: Cek apakah file Blade terbaru sudah terdeploy
 * Akses: https://perguruanpembda.com/diag_view_check.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { http_response_code(403); die('Forbidden'); }

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>Diagnostik View Check</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.8}";
echo ".ok{color:#00e676}.warn{color:#ffc107}.err{color:#ff5252}.info{color:#40c4ff}";
echo "h1{color:#bb86fc}h2{color:#03dac6}pre{background:#16213e;padding:15px;border-radius:8px;overflow-x:auto;white-space:pre-wrap;word-break:break-all}</style></head><body>";

echo "<h1>🔍 Diagnostik: Cek File Blade di Server</h1>";

$root = dirname(__DIR__);
// Try to find Laravel root
if (!file_exists($root . '/resources')) {
    $root = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub';
}

$viewFile = $root . '/resources/views/admin/bills/index.blade.php';

echo "<h2>1. Info File Blade</h2><pre>";
if (file_exists($viewFile)) {
    echo "<span class='ok'>✅ File ditemukan</span>\n";
    echo "Path: {$viewFile}\n";
    echo "Size: " . filesize($viewFile) . " bytes\n";
    echo "Modified: " . date('Y-m-d H:i:s', filemtime($viewFile)) . "\n";
    
    $content = file_get_contents($viewFile);
    
    echo "\n<span class='info'>--- Pencarian onclick pada tombol ---</span>\n\n";
    
    // Cek apakah tombol sudah pakai inline JS
    if (strpos($content, "try{document.getElementById('bulkUpdateModal')") !== false) {
        echo "<span class='ok'>✅ TOMBOL SUDAH PAKAI INLINE JS TERBARU (try-catch)</span>\n";
    } elseif (strpos($content, 'openBulkUpdateModal(false)') !== false) {
        echo "<span class='err'>❌ TOMBOL MASIH PAKAI FUNGSI LAMA: openBulkUpdateModal(false)</span>\n";
        echo "<span class='err'>   File BELUM ter-update! Git pull mungkin gagal.</span>\n";
    } else {
        echo "<span class='warn'>⚠️ Tombol openBulkUpdateModal tidak ditemukan sama sekali</span>\n";
    }
    
    // Cek apakah modal HTML ada
    if (strpos($content, 'id="bulkUpdateModal"') !== false) {
        echo "<span class='ok'>✅ Modal HTML bulkUpdateModal ADA di template</span>\n";
    } else {
        echo "<span class='err'>❌ Modal HTML bulkUpdateModal TIDAK ADA di template!</span>\n";
    }
    
    if (strpos($content, 'id="bulkDeleteModal"') !== false) {
        echo "<span class='ok'>✅ Modal HTML bulkDeleteModal ADA di template</span>\n";
    } else {
        echo "<span class='err'>❌ Modal HTML bulkDeleteModal TIDAK ADA di template!</span>\n";
    }
    
    // Cek apakah tabel visual checkbox ada
    if (strpos($content, 'modal-update-cb') !== false) {
        echo "<span class='ok'>✅ Checkbox visual (modal-update-cb) ADA</span>\n";
    } else {
        echo "<span class='err'>❌ Checkbox visual TIDAK ADA - masih pakai dropdown lama</span>\n";
    }
    
    // Ambil baris-baris yang mengandung onclick
    echo "\n<span class='info'>--- Baris yang mengandung 'onclick' dan 'Bulk' ---</span>\n\n";
    $lines = explode("\n", $content);
    foreach ($lines as $num => $line) {
        $lineNum = $num + 1;
        if (stripos($line, 'onclick') !== false && (stripos($line, 'bulk') !== false || stripos($line, 'Massal') !== false || stripos($line, 'openBulk') !== false)) {
            $trimmed = trim($line);
            if (strlen($trimmed) > 200) $trimmed = substr($trimmed, 0, 200) . '...';
            echo "<span class='warn'>Baris {$lineNum}: {$trimmed}</span>\n";
        }
    }
    
} else {
    echo "<span class='err'>❌ File TIDAK ditemukan: {$viewFile}</span>\n";
}
echo "</pre>";

echo "<h2>2. Cek Compiled View Cache</h2><pre>";
$compiledDir = $root . '/storage/framework/views';
if (is_dir($compiledDir)) {
    $files = glob($compiledDir . '/*.php');
    echo "Total compiled views: " . count($files) . "\n\n";
    
    $found = false;
    foreach ($files as $f) {
        $fContent = file_get_contents($f);
        if (strpos($fContent, 'Tagihan Siswa') !== false && strpos($fContent, 'bulkUpdateModal') !== false) {
            $found = true;
            echo "<span class='info'>Found compiled admin/bills view: " . basename($f) . "</span>\n";
            echo "Size: " . filesize($f) . " bytes\n";
            echo "Modified: " . date('Y-m-d H:i:s', filemtime($f)) . "\n\n";
            
            if (strpos($fContent, "try{document.getElementById('bulkUpdateModal')") !== false) {
                echo "<span class='ok'>✅ Compiled view SUDAH punya inline JS terbaru</span>\n";
            } else if (strpos($fContent, 'openBulkUpdateModal(false)') !== false) {
                echo "<span class='err'>❌ Compiled view MASIH punya kode LAMA!</span>\n";
                echo "<span class='err'>   SOLUSI: Hapus file ini: " . basename($f) . "</span>\n";
                // Auto-delete stale compiled view
                @unlink($f);
                echo "<span class='ok'>✅ File compiled view LAMA berhasil dihapus!</span>\n";
            }
        }
    }
    if (!$found) {
        echo "<span class='warn'>⚠️ Compiled view admin/bills tidak ditemukan (akan di-compile ulang saat diakses)</span>\n";
    }
} else {
    echo "<span class='warn'>⚠️ Compiled views directory tidak ditemukan</span>\n";
}
echo "</pre>";

echo "<h2>3. Git Status</h2><pre>";
$gitHead = trim(shell_exec("git -C {$root} rev-parse --short HEAD 2>&1") ?? 'N/A');
$gitLog = shell_exec("git -C {$root} log --oneline -3 2>&1") ?? 'N/A';
echo "HEAD: {$gitHead}\n";
echo "Recent commits:\n{$gitLog}\n";
echo "</pre>";

echo "<h2>4. Force Clear Semua Cache</h2><pre>";
// Force clear OPcache
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "<span class='ok'>✅ OPcache reset</span>\n";
}

// Force delete ALL compiled views
$deletedCount = 0;
foreach (glob($compiledDir . '/*.php') as $f) {
    if (@unlink($f)) $deletedCount++;
}
echo "<span class='ok'>✅ Deleted {$deletedCount} compiled view files</span>\n";

// Delete bootstrap cache
foreach (['config.php','routes-v7.php','packages.php','services.php','events.php'] as $cf) {
    $fp = "{$root}/bootstrap/cache/{$cf}";
    if (file_exists($fp) && @unlink($fp)) echo "<span class='ok'>✅ Deleted: bootstrap/cache/{$cf}</span>\n";
}

echo "\n<span class='ok'>🎉 Semua cache telah dibersihkan!</span>\n";
echo "<span class='info'>Sekarang buka halaman /admin/bills di INCOGNITO MODE (Ctrl+Shift+N)</span>\n";
echo "</pre>";

echo "</body></html>";
