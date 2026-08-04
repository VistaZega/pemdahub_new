<?php
// Secret key for security
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    die('Unauthorized');
}

echo "<pre>";
echo "Starting deployment and seeding process...\n\n";

$baseDir = __DIR__;
// Assuming this is in public/ folder, base path is one level up
$basePath = realpath($baseDir . '/../');
if (!$basePath) {
    // If not in public, maybe it's in root
    $basePath = __DIR__;
}

echo "Working Directory: " . $basePath . "\n\n";
chdir($basePath);

echo "1. Stashing and Pulling latest changes from Git...\n";
$gitOutput = shell_exec('git stash 2>&1 && git pull origin main 2>&1');
echo $gitOutput . "\n\n";

echo "2. Running Laravel Database Seeder (MikrokontrolerLmsSeeder)...\n";
$artisanOutput = shell_exec('php artisan db:seed --class=MikrokontrolerLmsSeeder --force 2>&1');
echo $artisanOutput . "\n\n";

echo "Done! Silakan cek LMS Anda sekarang.";
echo "</pre>";
