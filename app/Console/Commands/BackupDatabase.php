<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PDO;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
                            {--keep=7 : Number of backup files to keep}
                            {--compress : Compress the backup file with gzip}';

    protected $description = 'Backup the MySQL database to storage/backups directory';

    public function handle(): int
    {
        $this->info('🔄 Starting database backup...');

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $backupDir = storage_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup_{$database}_{$timestamp}.sql";
        $filepath = "{$backupDir}/{$filename}";

        // Try mysqldump first
        $mysqldumpPath = $this->findMysqldump();
        if ($mysqldumpPath !== null) {
            $this->info("📀 Using mysqldump: {$mysqldumpPath}");
            $success = $this->dumpWithMysqldump($mysqldumpPath, $host, $port, $username, $password, $database, $filepath);
        } else {
            $this->warn('⚠️ mysqldump not found, using PHP-based backup fallback...');
            $success = $this->dumpWithPHP($host, $port, $username, $password, $database, $filepath);
        }

        if ($success !== true) {
            $this->error('❌ Backup failed!');
            $this->error($success ?? 'Unknown error');
            return self::FAILURE;
        }

        if (!file_exists($filepath) || filesize($filepath) === 0) {
            $this->error('❌ Backup file is empty or was not created!');
            return self::FAILURE;
        }

        // Compress if requested
        if ($this->option('compress')) {
            $gzFilepath = $filepath . '.gz';
            $fp = fopen($filepath, 'rb');
            $gz = gzopen($gzFilepath, 'wb9');
            if ($fp && $gz) {
                while (!feof($fp)) {
                    gzwrite($gz, fread($fp, 1024 * 512));
                }
                gzclose($gz);
                fclose($fp);
                unlink($filepath);
                $filepath = $gzFilepath;
                $filename .= '.gz';
            }
        }

        $fileSize = filesize($filepath);
        $this->info("✅ Backup berhasil: {$filename}");
        $this->info("📁 Lokasi: {$filepath}");
        $this->info("📊 Ukuran: " . $this->formatBytes($fileSize));

        $this->cleanupOldBackups($backupDir, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    private function findMysqldump(): ?string
    {
        $paths = [
            'mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/bin/mariadb-dump',
            '/bin/mysqldump',
        ];
        foreach ($paths as $path) {
            $result = null;
            $code = -1;
            @exec("which " . escapeshellarg($path) . " 2>/dev/null", $result, $code);
            if ($code === 0 && !empty($result[0])) {
                return $result[0];
            }
            if (is_executable($path)) {
                return $path;
            }
        }
        return null;
    }

    private function dumpWithMysqldump(string $mysqldumpPath, string $host, string $port, string $username, string $password, string $database, string $filepath): bool|string
    {
        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s --routines --events --triggers --single-transaction --opt %s > %s',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        $output = [];
        $returnVar = -1;
        @exec($cmd . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            return "mysqldump failed (code {$returnVar}): " . implode("\n", $output);
        }

        return true;
    }

    private function dumpWithPHP(string $host, string $port, string $username, string $password, string $database, string $filepath): bool|string
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 300,
            ]);

            $sql = "-- PembdaHUB Database Backup (PHP)\n";
            $sql .= "-- Database: {$database}\n";
            $sql .= "-- Generated: " . Carbon::now()->format('Y-m-d H:i:s') . "\n\n";
            $sql .= "SET NAMES utf8mb4;\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $this->info("  📦 Exporting: {$table}");
                $sql .= "-- Table structure for `{$table}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

                $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $row = $stmt->fetch(PDO::FETCH_NUM);
                $sql .= $row[1] . ";\n\n";

                // Get row count
                $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                $rowCount = $countStmt->fetchColumn();

                if ($rowCount > 0) {
                    $sql .= "-- Dumping data for `{$table}` ({$rowCount} rows)\n";

                    $batchSize = 500;
                    $offset = 0;

                    while ($offset < $rowCount) {
                        $dataStmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$offset}");
                        $rows = $dataStmt->fetchAll(PDO::FETCH_NUM);
                        $columns = $dataStmt->columnCount();

                        if (!empty($rows)) {
                            $sql .= "INSERT INTO `{$table}` VALUES \n";
                            $valueLines = [];
                            foreach ($rows as $row) {
                                $values = [];
                                for ($i = 0; $i < $columns; $i++) {
                                    if ($row[$i] === null) {
                                        $values[] = 'NULL';
                                    } elseif (is_numeric($row[$i]) && $row[$i] >= -9223372036854775808 && $row[$i] <= 9223372036854775807) {
                                        // Check if it looks like a numeric column value
                                        $values[] = $row[$i];
                                    } else {
                                        $values[] = "'" . str_replace("'", "''", str_replace('\\', '\\\\', $row[$i])) . "'";
                                    }
                                }
                                $valueLines[] = "(" . implode(',', $values) . ")";
                            }
                            $sql .= implode(",\n", $valueLines) . ";\n";
                        }

                        $offset += $batchSize;

                        // Write in chunks to avoid memory issues
                        if (strlen($sql) > 5 * 1024 * 1024) { // 5MB chunks
                            file_put_contents($filepath, $sql, FILE_APPEND);
                            $sql = '';
                        }
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            $sql .= "-- Backup completed successfully\n";

            // Write remaining SQL
            if (!empty($sql)) {
                file_put_contents($filepath, $sql, FILE_APPEND);
            }

            return true;
        } catch (\Exception $e) {
            return "PHP backup failed: " . $e->getMessage();
        }
    }

    private function cleanupOldBackups(string $directory, int $keep): void
    {
        $files = glob("{$directory}/backup_*.sql*");
        if (count($files) > $keep) {
            usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
            $toDelete = array_slice($files, 0, count($files) - $keep);
            foreach ($toDelete as $file) {
                @unlink($file);
                $this->info("🗑️  Backup lama dihapus: " . basename($file));
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
