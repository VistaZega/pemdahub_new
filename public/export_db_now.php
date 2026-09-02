<?php
// Tingkatkan batas waktu eksekusi agar tidak terputus
set_time_limit(600);

// Keamanan: Wajib ada secret key di URL
if (!isset($_GET['secret']) || $_GET['secret'] !== 'pembda99') {
    die('Akses Ditolak!');
}

// 1. OTOMATIS MENCARI FILE .env
$kemungkinan_path = [
    __DIR__ . '/../.env',            // Standar Laravel (karena file ini di dalam public/)
    __DIR__ . '/../../.env',         // Jika ada struktur subfolder lain
    __DIR__ . '/.env'                
];

$envFile = false;
foreach ($kemungkinan_path as $path) {
    if (file_exists($path)) {
        $envFile = $path;
        break;
    }
}

if (!$envFile) {
    die('Error: File .env tidak ditemukan. Tidak bisa melacak password database otomatis.');
}

// 2. MEMBACA PASSWORD DB DARI FILE .env SECARA AMAN
$envContent = file_get_contents($envFile);
preg_match('/^DB_HOST=(.*)$/m', $envContent, $matchHost);
preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $matchDb);
preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $matchUser);
preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matchPass);

$host = isset($matchHost[1]) ? trim(str_replace('"', '', $matchHost[1])) : '127.0.0.1';
$db   = isset($matchDb[1]) ? trim(str_replace('"', '', $matchDb[1])) : '';
$user = isset($matchUser[1]) ? trim(str_replace('"', '', $matchUser[1])) : '';
$pass = isset($matchPass[1]) ? trim(str_replace('"', '', $matchPass[1])) : '';

if (empty($user) || empty($db)) {
    die('Error: Gagal membaca isi DB_USERNAME atau DB_DATABASE dari file .env');
}

// 3. MULAI DOWNLOAD DATABASE
$filename = "pembdahub_backup_" . date('Ymd_His') . ".sql";

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Eksekusi mysqldump dengan data yang sudah dilacak otomatis
$command = "mysqldump --opt -h {$host} -u {$user} -p'{$pass}' {$db} 2>/dev/null";
passthru($command);

exit;
?>
