<?php
$envFile  = '.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}
$pdo = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}", $env['DB_USERNAME'], $env['DB_PASSWORD']);
$stmt = $pdo->query("SELECT content FROM lms_materials WHERE course_id=221 LIMIT 1"); 
$content = $stmt->fetchColumn();
file_put_contents('debug_output.txt', $content);
echo "OK";
