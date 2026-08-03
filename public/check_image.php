<?php
$file = __DIR__ . "/../storage/app/public/photos/6a7067c57b6f7.jpeg";
if (file_exists($file)) {
    echo "Size: " . filesize($file) . " bytes\n";
    echo "Mime: " . mime_content_type($file) . "\n";
} else {
    echo "File not found: " . $file;
}

