<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== KOLOM lms_courses ===\n";
$cols = DB::select('SHOW COLUMNS FROM lms_courses');
foreach ($cols as $c) {
    echo $c->Field . ' | ' . $c->Type . "\n";
}

echo "\n=== SAMPLE DATA lms_courses ===\n";
$rows = DB::select('SELECT * FROM lms_courses LIMIT 3');
foreach ($rows as $r) {
    print_r((array)$r);
}

echo "\n=== KOLOM lms_modules ===\n";
$cols2 = DB::select('SHOW COLUMNS FROM lms_modules');
foreach ($cols2 as $c) {
    echo $c->Field . ' | ' . $c->Type . "\n";
}

echo "\n=== KOLOM lms_materials ===\n";
$cols3 = DB::select('SHOW COLUMNS FROM lms_materials');
foreach ($cols3 as $c) {
    echo $c->Field . ' | ' . $c->Type . "\n";
}

echo "\n=== KOLOM lms_assignments ===\n";
$cols4 = DB::select('SHOW COLUMNS FROM lms_assignments');
foreach ($cols4 as $c) {
    echo $c->Field . ' | ' . $c->Type . "\n";
}

echo "\n=== KOLOM lms_quizzes ===\n";
$cols5 = DB::select('SHOW COLUMNS FROM lms_quizzes');
foreach ($cols5 as $c) {
    echo $c->Field . ' | ' . $c->Type . "\n";
}

echo "\n=== KOLOM lms_quiz_questions ===\n";
$cols6 = DB::select('SHOW COLUMNS FROM lms_quiz_questions');
foreach ($cols6 as $c) {
    echo $c->Field . ' | ' . $c->Type . "\n";
}
