<?php
$f1 = __DIR__ . "/../storage/app/public/photos/6a706e98be03f.jpeg";
$f2 = __DIR__ . "/../storage/app/public/photos/OhpOicz7DVJQWSh0yLUJ40UGZgqFRD5Jr9KKJus4.jpg";
echo "File 1 (6a706...): " . (file_exists($f1) ? "EXISTS (" . filesize($f1) . " bytes)" : "MISSING") . "\n";
echo "File 2 (OhpOicz...): " . (file_exists($f2) ? "EXISTS (" . filesize($f2) . " bytes)" : "MISSING") . "\n";

