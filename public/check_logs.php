<?php
$dir = __DIR__ . "/../storage/logs/";
$files = glob($dir . "laravel*.log");
if (empty($files)) {
    echo "No log files found in " . $dir;
} else {
    $logFile = end($files);
    echo "Reading from " . basename($logFile) . "\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -1000);
    $count = 0;
    foreach ($lastLines as $line) {
        if (strpos($line, "local.INFO: Received") !== false || strpos($line, "local.ERROR") !== false || strpos($line, "cropped") !== false) {
            echo $line;
            $count++;
        }
    }
    if ($count === 0) echo "No matched log lines.";
}

