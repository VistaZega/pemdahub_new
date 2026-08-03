<?php
$dir = __DIR__ . "/../storage/logs/";
$files = glob($dir . "laravel*.log");
if (empty($files)) {
    echo "No log files found in " . $dir;
} else {
    $logFile = end($files);
    $lines = file($logFile);
    foreach ($lines as $line) {
        if (strpos($line, "17:39:13") !== false) {
            echo $line;
        }
    }
}

