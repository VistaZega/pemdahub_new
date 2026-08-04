<?php
$file = 'app/Http/Controllers/Yayasan/ProgressInputController.php';
$content = file_get_contents($file);

$content = str_replace(
    "\$siswaTanpaRombelList->pluck('name')", 
    "\$siswaTanpaRombelList->pluck('full_name')", 
    $content
);

$content = str_replace(
    "\$incompleteSiswaNames[] = \$s->name;", 
    "\$incompleteSiswaNames[] = \$s->full_name;", 
    $content
);

$content = str_replace(
    "->pluck('name')", 
    "->pluck('full_name')", 
    $content
);

// Oh wait, Classroom has pluck('name') which should stay 'name'.
// Let's replace back only for Classroom if any, or do it more carefully.
