<?php
/**
 * Standalone Emergency Tool: Restorasi BEDAH Khusus Tabel Keuangan (student_bills, payments, payment_types)
 * Access URL: https://perguruanpembda.com/restore_financial_tables_only.php?secret=pembda99
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$secret = $_REQUEST['secret'] ?? '';
$VALID_SECRET = 'pembda99';

if ($secret !== $VALID_SECRET) {
    http_response_code(403);
    die('403 Forbidden - Token Secret Salah');
}

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$action = $_REQUEST['action'] ?? '';
$sqlFileParam = $_REQUEST['sql_file'] ?? '';
$message = '';
$log = [];

// Find available SQL dump files in root, public, or archive/backup
$rootDir = base_path();
$searchPaths = [
    $rootDir . '/*.sql',
    $rootDir . '/public/*.sql',
    $rootDir . '/archive/backup/*.sql',
    $rootDir . '/storage/*.sql',
];

$availableSqlFiles = [];
foreach ($searchPaths as $pattern) {
    $found = glob($pattern);
    if ($found) {
        foreach ($found as $f) {
            $availableSqlFiles[] = [
                'path' => $f,
                'name' => basename($f),
                'size' => number_format(filesize($f) / 1024 / 1024, 2) . ' MB',
                'mtime' => date('d/m/Y H:i:s', filemtime($f)),
            ];
        }
    }
}

// SURGICAL RESTORATION EXECUTION (FINANCIAL TABLES ONLY)
if ($action === 'execute_financial_restore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetFile = $_POST['sql_file_path'] ?? '';

    if (file_exists($targetFile)) {
        DB::transaction(function() use ($targetFile, &$log, &$message) {
            // Disable foreign key checks for clean restore
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Target ONLY financial tables
            $financialTables = ['student_bills', 'payments', 'payment_types'];

            foreach ($financialTables as $tbl) {
                if (Schema::hasTable($tbl)) {
                    DB::table($tbl)->truncate();
                    $log[] = "Mengosongkan tabel keuangan: '{$tbl}'";
                }
            }

            // Read SQL file line by line and execute ONLY statements for financial tables
            $handle = fopen($targetFile, 'r');
            $currentQuery = '';
            $executedQueries = 0;

            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $trimmed = trim($line);
                    if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                        continue;
                    }

                    $currentQuery .= $line;

                    if (str_ends_with(trim($line), ';')) {
                        $qUpper = strtoupper($currentQuery);
                        // Check if query is targeting student_bills, payments, or payment_types
                        $isFinancialQuery = str_contains($qUpper, '`STUDENT_BILLS`') || 
                                           str_contains($qUpper, '`PAYMENTS`') || 
                                           str_contains($qUpper, '`PAYMENT_TYPES`') ||
                                           str_contains($qUpper, ' STUDENT_BILLS ') || 
                                           str_contains($qUpper, ' PAYMENTS ') || 
                                           str_contains($qUpper, ' PAYMENT_TYPES ');

                        if ($isFinancialQuery && (str_contains($qUpper, 'INSERT INTO') || str_contains($qUpper, 'REPLACE INTO'))) {
                            DB::unprepared($currentQuery);
                            $executedQueries++;
                        }

                        $currentQuery = '';
                    }
                }
                fclose($handle);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $log[] = "Berhasil mengeksekusi {$executedQueries} query restorasi khusus tabel keuangan dari file: " . basename($targetFile);
            $message = "RESTORASI KHUSUS TABEL KEUANGAN BERHASIL! Data tabel student_bills, payments, dan payment_types telah kembali utuh persis sesuai file backup snapshot.";
        });
    } else {
        $message = "Error: File SQL dump tidak ditemukan di path: " . htmlspecialchars($targetFile);
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restorasi Bedah Khusus Data Keuangan - PembdaHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-size: 13px; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="py-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fa-solid fa-syringe text-danger me-2"></i> Tool Restorasi Bedah Khusus Data Keuangan</h3>
            <p class="text-muted mb-0">HANYA mengembalikan data Keuangan (<code>student_bills</code>, <code>payments</code>, <code>payment_types</code>). Data Siswa, Nilai, LMS, Absensi, dan User <strong>100% UTUH DAN TIDAK DISENTUH</strong>.</p>
        </div>
        <a href="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Pembersih</a>
    </div>

    <?php if(!empty($message)): ?>
    <div class="alert alert-success card-custom mb-4">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-check-circle me-2"></i> Restorasi Keuangan Selesai</h5>
        <p class="mb-2"><?= htmlspecialchars($message) ?></p>
        <?php if(!empty($log)): ?>
        <hr>
        <div class="font-monospace small" style="max-height: 200px; overflow-y: auto;">
            <?php foreach($log as $l): ?>
                <div>&bull; <?= htmlspecialchars($l) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- STEP INSTRUCTION CARD -->
    <div class="card card-custom p-4 mb-4 bg-white border-primary">
        <h5 class="fw-bold text-primary mb-2"><i class="fa-solid fa-circle-info me-2"></i> Cara Kerja Restorasi Khusus Keuangan:</h5>
        <ol class="mb-0 ps-3">
            <li class="mb-1">Tool ini <strong>HANYA</strong> mengganti isi 3 tabel keuangan: <code>student_bills</code> (Tagihan), <code>payments</code> (Pembayaran/Kas), dan <code>payment_types</code> (Master Jenis Tagihan).</li>
            <li class="mb-1">Seluruh data non-keuangan lainnya (Data Siswa, Absensi, Nilai, Guru, LMS, User) <strong>AMAN 100% DITAHAN DAN TIDAK AKAN BERUBAH</strong>.</li>
            <li>Di bawah ini adalah daftar file SQL Snapshot Backup yang terdeteksi di server:</li>
        </ol>
    </div>

    <!-- SQL FILES LIST -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-database me-2"></i> File SQL Backup Snapshot Terdeteksi di Server:</h5>

        <?php if(empty($availableSqlFiles)): ?>
            <div class="alert alert-warning card-custom mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>Belum ada file SQL backup terdeteksi di folder server.</strong><br>
                Silakan download file SQL dump backup kemarin (26 Agustus) dari hPanel Hostinger, lalu upload file tersebut ke folder root atau folder <code>public/</code> website.
            </div>
        <?php else: ?>
            <div class="table-responsive mb-3">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th>Nama File Backup SQL</th>
                            <th>Ukuran File</th>
                            <th>Tanggal Pembuatan</th>
                            <th class="text-center">Aksi Restorasi Keuangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($availableSqlFiles as $sf): ?>
                        <tr>
                            <td class="fw-bold text-primary"><i class="fa-solid fa-file-code me-2"></i> <?= htmlspecialchars($sf['name']) ?></td>
                            <td><?= $sf['size'] ?></td>
                            <td><?= $sf['mtime'] ?></td>
                            <td class="text-center">
                                <form action="restore_financial_tables_only.php?secret=<?= urlencode($secret) ?>" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN?\n\nTool ini akan mengembalikan data 3 tabel KEUANGAN SAJA (student_bills, payments, payment_types) dari file <?= htmlspecialchars($sf['name']) ?>.\n\nData non-keuangan lainnya TETAP UTUH.');">
                                    <input type="hidden" name="action" value="execute_financial_restore">
                                    <input type="hidden" name="sql_file_path" value="<?= htmlspecialchars($sf['path']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm font-bold"><i class="fa-solid fa-syringe me-1"></i> Restorasi Khusus Keuangan</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
