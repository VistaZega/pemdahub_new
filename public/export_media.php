<?php
/**
 * Storage Media Exporter for PembdaHUB
 * Mengompres dan mengunduh seluruh file media/foto dari storage/app/public
 * Akses: https://perguruanpembda.com/export_media.php?token=pembda2026export
 */

if (($_GET['token'] ?? '') !== 'pembda2026export') {
    http_response_code(403);
    die('Forbidden - Token tidak valid');
}

// Set time and memory limits for large zips
@ini_set('max_execution_time', 600);
@ini_set('memory_limit', '512M');

// Tentukan path storage public di server
$possiblePaths = [
    __DIR__ . '/../storage/app/public',
    dirname(__DIR__) . '/storage/app/public',
    __DIR__ . '/storage',
];

$storagePath = null;
foreach ($possiblePaths as $path) {
    if (is_dir($path)) {
        $storagePath = realpath($path);
        break;
    }
}

if (!$storagePath) {
    http_response_code(404);
    die('Folder storage tidak ditemukan.');
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    die('Ekstensi PHP ZipArchive tidak aktif di server. Silakan download via File Manager hPanel.');
}

$zipFileName = sys_get_temp_dir() . '/pembdahub_media_' . date('Y-m-d_H-i-s') . '.zip';
$zip = new ZipArchive();

if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    die('Gagal membuat file ZIP.');
}

// Recursive add files
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($storagePath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;
foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($storagePath) + 1);
        // Normalize slash for zip
        $relativePath = str_replace('\\', '/', $relativePath);
        $zip->addFile($filePath, $relativePath);
        $count++;
    }
}

$zip->close();

if (!file_exists($zipFileName)) {
    http_response_code(500);
    die('File ZIP gagal diproses.');
}

// Stream download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="pembdahub_storage_' . date('Ymd_His') . '.zip"');
header('Content-Length: ' . filesize($zipFileName));
header('Pragma: no-cache');
header('Expires: 0');

readfile($zipFileName);
@unlink($zipFileName);
exit;
