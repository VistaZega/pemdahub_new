<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

$basePath = realpath(__DIR__ . '/../');
if (!$basePath) {
    $basePath = realpath('/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub');
}

echo "Base Path: " . $basePath . "<br><br>";

$backupDirs = [
    $basePath . '/storage/app/Laravel',
    $basePath . '/storage/app/backups',
    $basePath . '/storage/app/public/backups',
    $basePath . '/storage/backups',
    '/home/u474310197/domains/perguruanpembda.com/backups',
];

foreach ($backupDirs as $dir) {
    echo "Checking Directory: " . $dir . "<br>";
    if (is_dir($dir)) {
        $files = scandir($dir);
        if (count($files) > 2) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $dir . '/' . $file;
                    $size = filesize($filePath);
                    $date = date("Y-m-d H:i:s", filemtime($filePath));
                    echo " - " . $file . " (" . round($size / 1024 / 1024, 2) . " MB, Modified: " . $date . ")<br>";
                    
                    // Peek header if .gz
                    if (str_ends_with($file, '.gz') && function_exists('gzopen')) {
                        $gz = @gzopen($filePath, 'rb');
                        if ($gz) {
                            $preview = @gzread($gz, 300);
                            @gzclose($gz);
                            echo "<pre style='background:#222;color:#aef;padding:5px;margin:5px 0 10px 20px;'>" . htmlspecialchars(substr($preview, 0, 200)) . "</pre>";
                        }
                    }
                }
            }
        } else {
            echo " - Directory exists but is empty.<br>";
        }
    } else {
        echo " - Directory does not exist.<br>";
    }
    echo "<br>";
}

// Check mysqldump availability
echo "<h3>mysqldump Diagnostic:</h3>";
$dumpPath = null;
$paths = ['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/usr/bin/mariadb-dump', '/bin/mysqldump'];
foreach ($paths as $p) {
    $out = [];
    $ret = -1;
    @exec("which " . escapeshellarg($p) . " 2>/dev/null", $out, $ret);
    if ($ret === 0 && !empty($out[0])) {
        $dumpPath = $out[0];
        break;
    }
}
echo "Detected mysqldump: " . ($dumpPath ?? 'None') . "<br>";
if ($dumpPath) {
    $ver = @shell_exec("{$dumpPath} --version 2>&1");
    echo "Version: <pre>" . htmlspecialchars($ver ?? '') . "</pre>";
}

if (isset($_GET['test']) && $_GET['test'] === 'run') {
    echo "<h3>Testing artisan backup:database Execution:</h3>";
    $cmd = "/usr/bin/php8.5 " . escapeshellarg($basePath . '/artisan') . " backup:database --compress --keep=7 2>&1";
    $output = [];
    $ret = -1;
    exec($cmd, $output, $ret);
    echo "Exit Code: <b>$ret</b><br>";
    echo "Output: <pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
} else {
    echo "<br><a href='?secret=pembda99&test=run' style='display:inline-block;background:#388bfd;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;'>▶ Test Run backup:database</a>";
}


