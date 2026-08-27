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
