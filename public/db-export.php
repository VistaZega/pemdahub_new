<?php
/**
 * PembdaHUB — Database Export Tool (Standalone PHP, tanpa shell_exec)
 * 
 * Script ini mengekspor seluruh database production ke file SQL yang bisa didownload.
 * Bekerja di shared hosting tanpa SSH — menggunakan PDO murni.
 * 
 * Akses: https://perguruanpembda.com/db-export.php?secret=pembda2026export
 * 
 * Mode:
 *   ?secret=...                     → Stream download SQL dump (gzip compressed)
 *   ?secret=...&preview=1           → Preview daftar tabel & jumlah row
 *   ?secret=...&tables=a,b,c        → Export hanya tabel tertentu
 *   ?secret=...&exclude=cache,sessions → Exclude tabel tertentu
 * 
 * ⚠️ HAPUS ATAU RENAME FILE INI SETELAH SELESAI DIGUNAKAN!
 */

// ══════════════════════════════════════════════════════
// SECURITY
// ══════════════════════════════════════════════════════
$SECRET = 'pembda2026export';

if (($_GET['secret'] ?? '') !== $SECRET) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied.']);
    exit;
}

// ══════════════════════════════════════════════════════
// LOAD LARAVEL .env UNTUK CREDENTIALS DATABASE
// ══════════════════════════════════════════════════════
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    // Coba path production Hostinger
    $envPath = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/.env';
}
if (!file_exists($envPath)) {
    http_response_code(500);
    echo json_encode(['error' => '.env not found']);
    exit;
}

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$key, $val] = explode('=', $line, 2);
    $env[trim($key)] = trim(trim($val), '"\'');
}

$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? '';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

if (empty($dbName)) {
    http_response_code(500);
    echo json_encode(['error' => 'DB_DATABASE not configured in .env']);
    exit;
}

// ══════════════════════════════════════════════════════
// KONEKSI PDO
// ══════════════════════════════════════════════════════
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false, // hemat memori
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

// ══════════════════════════════════════════════════════
// AMBIL DAFTAR TABEL
// ══════════════════════════════════════════════════════
$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Filter tabel jika diminta
$onlyTables = isset($_GET['tables']) ? explode(',', $_GET['tables']) : null;
$defaultExcluded = [
    'cache', 'cache_locks', 'sessions',
    'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring',
    'jobs', 'job_batches', 'failed_jobs',
    'reputation_logs', 'notifications', 'forum_likes', 'login_histories'
];
$excludeTables = isset($_GET['exclude']) ? explode(',', $_GET['exclude']) : (isset($_GET['all']) ? [] : $defaultExcluded);

$tables = [];
foreach ($allTables as $table) {
    if ($onlyTables && !in_array($table, $onlyTables)) continue;
    if (!$onlyTables && in_array($table, $excludeTables)) continue;
    $tables[] = $table;
}

// ══════════════════════════════════════════════════════
// MODE PREVIEW: Tampilkan daftar tabel + row count
// ══════════════════════════════════════════════════════
if (isset($_GET['preview'])) {
    header('Content-Type: application/json; charset=utf-8');
    $info = ['database' => $dbName, 'tables' => []];
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        $info['tables'][$table] = (int) $count;
    }
    $info['total_tables'] = count($tables);
    $info['total_rows'] = array_sum($info['tables']);
    $info['excluded'] = $excludeTables;
    echo json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ══════════════════════════════════════════════════════
// MODE EXPORT: Stream SQL dump sebagai download (gzip)
// ══════════════════════════════════════════════════════
set_time_limit(600); // 10 menit max
ini_set('memory_limit', '512M');

$filename = $dbName . '.' . date('Ymd_His') . '.sql';
$useGzip = !isset($_GET['nogzip']) && function_exists('gzopen');

if ($useGzip) {
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $filename . '.gz"');
} else {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}
header('Cache-Control: no-store, no-cache');

// Output buffer — tulis langsung ke php://output
ob_end_clean();

if ($useGzip) {
    $tmpFile = tempnam(sys_get_temp_dir(), 'dbexp_');
    $gz = gzopen($tmpFile, 'wb6');
} else {
    $gz = null;
}

/**
 * Helper: tulis baris ke output
 */
function writeLine(string $line, $gz = null): void {
    if ($gz) {
        gzwrite($gz, $line . "\n");
    } else {
        echo $line . "\n";
        if (ob_get_level()) ob_flush();
        flush();
    }
}

// Header SQL
writeLine("-- PembdaHUB Database Export", $gz);
writeLine("-- Generated: " . date('Y-m-d H:i:s T'), $gz);
writeLine("-- Database: {$dbName}", $gz);
writeLine("-- Tables: " . count($tables), $gz);
writeLine("", $gz);
writeLine("SET NAMES utf8mb4;", $gz);
writeLine("SET FOREIGN_KEY_CHECKS = 0;", $gz);
writeLine("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';", $gz);
writeLine("SET TIME_ZONE = '+07:00';", $gz);
writeLine("", $gz);

foreach ($tables as $table) {
    writeLine("-- --------------------------------------------------------", $gz);
    writeLine("-- Table: `{$table}`", $gz);
    writeLine("-- --------------------------------------------------------", $gz);
    writeLine("", $gz);
    
    // DROP + CREATE TABLE
    writeLine("DROP TABLE IF EXISTS `{$table}`;", $gz);
    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
    $createSql = $createStmt['Create Table'] ?? $createStmt[1] ?? '';
    writeLine($createSql . ";", $gz);
    writeLine("", $gz);
    
    // COUNT rows
    $rowCount = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
    if ($rowCount === 0) {
        writeLine("-- Table `{$table}` is empty", $gz);
        writeLine("", $gz);
        continue;
    }
    
    // INSERT DATA — batch per 2500 rows untuk kecepatan dan efisiensi memori
    $batchSize = 2500;
    $offset = 0;
    
    // Ambil kolom untuk quoting
    $columns = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
    $colList = implode('`, `', $columns);
    
    writeLine("LOCK TABLES `{$table}` WRITE;", $gz);
    
    while ($offset < $rowCount) {
        $rows = $pdo->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$offset}")->fetchAll();
        
        if (empty($rows)) break;
        
        $valueGroups = [];
        foreach ($rows as $row) {
            $vals = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $vals[] = 'NULL';
                } elseif (is_numeric($val) && !str_starts_with((string)$val, '0') && strlen((string)$val) < 16) {
                    $vals[] = $val;
                } else {
                    $vals[] = $pdo->quote($val);
                }
            }
            $valueGroups[] = '(' . implode(',', $vals) . ')';
        }
        
        writeLine("INSERT INTO `{$table}` (`{$colList}`) VALUES", $gz);
        writeLine(implode(",\n", $valueGroups) . ";", $gz);
        
        $offset += $batchSize;
    }
    
    writeLine("UNLOCK TABLES;", $gz);
    writeLine("", $gz);
}

writeLine("SET FOREIGN_KEY_CHECKS = 1;", $gz);
writeLine("-- Export complete.", $gz);

// Finalize gzip
if ($gz) {
    gzclose($gz);
    // Stream file ke output
    readfile($tmpFile);
    unlink($tmpFile);
}

exit;
