<?php
// Keamanan: Wajib ada secret key di URL
if (!isset($_GET['secret']) || $_GET['secret'] !== 'pembda99') {
    die('Akses Ditolak!');
}

$outputFile = __DIR__ . '/backup_db_full.sql';
$downloadLink = "https://perguruanpembda.com/backup_db_full.sql";

if (isset($_GET['hapus'])) {
    if(file_exists($outputFile)) unlink($outputFile);
    die("File lama berhasil dihapus. <a href='?secret=pembda99'>Kembali</a>");
}

if (file_exists($outputFile)) {
    $sizeMB = round(filesize($outputFile) / 1024 / 1024, 2);
    echo "<h2>Status: File Backup Database Ditemukan!</h2>";
    echo "<p>Ukuran file saat ini: <b>{$sizeMB} MB</b></p>";
    echo "<p><i>Refresh halaman ini secara berkala. Jika ukurannya berhenti bertambah (dan sudah mencapai ukuran GB yang seharusnya), artinya backup selesai.</i></p><hr>";
    echo "<h3>👇 LINK DOWNLOAD 👇</h3>";
    echo "<a href='{$downloadLink}' style='font-size:20px; color:blue;'><b>[ DOWNLOAD BACKUP DATABASE ]</b></a><br><br><br>";
    echo "<a href='?secret=pembda99&hapus=1' style='color:red;'>Hapus file ini untuk mengulang dari awal</a>";
    exit;
}

// 1. OTOMATIS MENCARI FILE .env
$kemungkinan_path = [
    __DIR__ . '/../.env',
    __DIR__ . '/../../.env',
    __DIR__ . '/.env'                
];

$envFile = false;
foreach ($kemungkinan_path as $path) {
    if (file_exists($path)) {
        $envFile = $path;
        break;
    }
}

if (!$envFile) die('Error: File .env tidak ditemukan.');

$envContent = file_get_contents($envFile);
preg_match('/^DB_HOST=(.*)$/m', $envContent, $matchHost);
preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $matchDb);
preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $matchUser);
preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matchPass);

$host = isset($matchHost[1]) ? trim(str_replace('"', '', $matchHost[1])) : '127.0.0.1';
$db   = isset($matchDb[1]) ? trim(str_replace('"', '', $matchDb[1])) : '';
$user = isset($matchUser[1]) ? trim(str_replace('"', '', $matchUser[1])) : '';
$pass = isset($matchPass[1]) ? trim(str_replace('"', '', $matchPass[1])) : '';

if (empty($user) || empty($db)) die('Error: Gagal membaca DB dari .env');

// 3. MULAI DUMP DI BACKGROUND
// Menggunakan nohup agar proses tidak diputus Hostinger saat ukuran mencapai GB
$command = "nohup mysqldump --opt -h {$host} -u {$user} -p'{$pass}' {$db} > {$outputFile} 2>/dev/null &";
shell_exec($command);

echo "<h2>🚀 PROSES BACKUP DATABASE (GB) DIMULAI!</h2>";
echo "<p>Sistem sedang mengekstrak database berukuran besar Anda di latar belakang.</p>";
echo "<p>Karena ukuran aslinya mencapai GigaByte, proses ini tidak akan terpotong oleh sistem Hostinger.</p>";
echo "<p>Silakan <b>Refresh (F5)</b> halaman ini setiap beberapa menit untuk melihat perkembangan ukuran filenya membesar melampaui 699MB.</p>";
echo "<p><a href='?secret=pembda99'>Klik di sini untuk merefresh halaman</a></p>";
?>
