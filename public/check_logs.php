<?php
$dir = __DIR__ . "/../storage/logs/";
$files = glob($dir . "*.log");
if (empty($files)) {
    echo "No log files found in " . $dir;
} else {
    $logFile = end($files);
    echo "Reading from " . $logFile . "\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    foreach ($lastLines as $line) {
        if (strpos($line, "local.INFO: Received") !== false || strpos($line, "local.ERROR") !== false || strpos($line, "cropped") !== false) {
            echo $line;
        }
    }
}

