<?php
$dir = __DIR__ . "/../storage/logs/";
$files = glob($dir . "laravel*.log");
if (empty($files)) {
    echo "No log files found in " . $dir;
} else {
    $logFile = end($files);
    echo "Reading from " . basename($logFile) . "\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -500);
    foreach ($lastLines as $line) {
        if (strpos($line, "INFO:") !== false || strpos($line, "ERROR:") !== false) {
            echo $line;
        }
    }
}

