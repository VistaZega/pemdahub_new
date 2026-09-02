<?php
// Keamanan
if (!isset($_GET['secret']) || $_GET['secret'] !== 'pembda99') {
    die('Akses Ditolak!');
}

$outputFile = __DIR__ . '/backup_storage_4GB.tar.gz';
$downloadLink = "https://perguruanpembda.com/backup_storage_4GB.tar.gz";

// Jika tombol hapus ditekan
if (isset($_GET['hapus'])) {
    if(file_exists($outputFile)) unlink($outputFile);
    die("File lama berhasil dihapus. <a href='?secret=pembda99'>Kembali</a>");
}

// Cek apakah file sedang/sudah dibuat
if (file_exists($outputFile)) {
    $sizeMB = round(filesize($outputFile) / 1024 / 1024, 2);
    echo "<h2>Status: File Backup Ditemukan!</h2>";
    echo "<p>Ukuran file saat ini: <b>{$sizeMB} MB</b></p>";
    echo "<p><i>Refresh halaman ini. Jika ukurannya berhenti bertambah, artinya kompresi selesai.</i></p><hr>";
    echo "<a href='{$downloadLink}' style='font-size:20px; color:blue;'><b>[ DOWNLOAD BACKUP STORAGE ]</b></a><br><br><br>";
    echo "<a href='?secret=pembda99&hapus=1' style='color:red;'>Hapus file ini untuk mengulang</a>";
    exit;
}

// 1. MENCARI FOLDER STORAGE OTOMATIS
$kemungkinan_path = [
    __DIR__ . '/../storage/app/public',  // Standar Laravel
    __DIR__ . '/storage',                // Jika langsung ditaruh di public/storage
    __DIR__ . '/../storage/app',         // Jika tidak ada folder public di dalamnya
    __DIR__ . '/../../pembdahub/storage/app/public' 
];

$storageDir = false;
foreach($kemungkinan_path as $path) {
    if (is_dir($path)) {
        $storageDir = $path;
        break; // Berhenti mencari jika ketemu
    }
}

// Jika tetap tidak ketemu, tampilkan isi folder agar kita tahu masalahnya
if (!$storageDir) {
    echo "<h3 style='color:red'>Folder storage gagal ditemukan!</h3>";
    echo "<b>Sistem mencari di lokasi berikut namun nihil:</b><ul>";
    foreach($kemungkinan_path as $path) { echo "<li>{$path}</li>"; }
    echo "</ul><hr>";
    
    echo "<b>Isi dari direktori utama (".__DIR__."/../) saat ini adalah:</b><ul>";
    $isiFolder = scandir(__DIR__ . '/../');
    foreach($isiFolder as $file) {
        if ($file !== '.' && $file !== '..') echo "<li>{$file}</li>";
    }
    echo "</ul>";
    die();
}

// Jika ketemu, Mulai Kompresi!
$parentDir = dirname($storageDir);
$folderName = basename($storageDir);

$command = "nohup tar -czf {$outputFile} -C {$parentDir} {$folderName} > /dev/null 2>&1 &";
shell_exec($command);

echo "<h2>🚀 PROSES KOMPRESI DIMULAI!</h2>";
echo "<p>Folder storage <b>berhasil ditemukan</b> di: <br><code>{$storageDir}</code></p>";
echo "<p>Sistem sedang mengompres data 4,4 GB Anda di latar belakang.</p>";
echo "<p>Silakan <b>Refresh (F5)</b> halaman ini setiap 2-5 menit untuk melihat perkembangan ukuran filenya.</p>";
echo "<p><a href='?secret=pembda99'>Klik di sini untuk merefresh halaman</a></p>";
?>
