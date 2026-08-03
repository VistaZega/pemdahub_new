<?php
$f = __DIR__ . "/../storage/app/public/photos/6a706bed89b61.jpeg";
if (file_exists($f)) {
    echo "File time: " . date("Y-m-d H:i:s", filemtime($f)) . "\n";
} else {
    echo "File missing\n";
}

