<?php
$logFile = __DIR__ . "/../storage/logs/laravel.log";
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    foreach ($lastLines as $line) {
        if (strpos($line, "local.INFO: Received") !== false || strpos($line, "local.ERROR") !== false || strpos($line, "cropped") !== false) {
            echo $line;
        }
    }
} else {
    echo "Log file not found.";
}

