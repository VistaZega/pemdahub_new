<?php
$f = __DIR__ . "/../storage/app/public/photos/6a706bed89b61.jpeg";
$size = getimagesize($f);
echo "Width: " . $size[0] . "\n";
echo "Height: " . $size[1] . "\n";

