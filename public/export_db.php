<?php
/**
 * Database Exporter for PembdaHUB
 * Akses: https://perguruanpembda.com/export_db.php?token=pembda2026export
 */

if (($_GET['token'] ?? '') !== 'pembda2026export') {
    http_response_code(403);
    die('Forbidden');
}

// Set unlimited time & memory limits
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set('memory_limit', '512M');
@ini_set('zlib.output_compression', '0');

// LiteSpeed & Nginx anti-timeout headers
header('X-LiteSpeed-NoAbort: 1');
header('X-Accel-Buffering: no');

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Disable output buffering for real-time streaming
    while (ob_get_level()) {
        ob_end_clean();
    }

    $dbName = DB::connection()->getDatabaseName();
    $tables = DB::select('SHOW TABLES');
    $tableKey = 'Tables_in_' . $dbName;
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $dbName . '_' . date('Y-m-d_H-i-s') . '.sql"');
    
    echo "-- PembdaHUB Database Dump\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: " . $dbName . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";
    flush();
    
    // Daftar tabel sementara/ephemeral yang TIDAK boleh di-export datanya (hanya strukturnya saja)
    $ignoreDataTables = [
        'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 
        'job_batches', 'activity_log', 'notification_logs', 'pulse_aggregates', 
        'pulse_entries', 'pulse_values', 'telescope_entries', 'telescope_entries_tags', 
        'telescope_monitoring'
    ];
    
    foreach ($tables as $tableObj) {
        $table = $tableObj->$tableKey;
        
        // Structure
        $createTable = DB::select("SHOW CREATE TABLE `$table`")[0];
        $createTableKey = 'Create Table';
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo $createTable->$createTableKey . ";\n\n";
        
        // Skip data untuk tabel sementara/ephemeral
        if (in_array($table, $ignoreDataTables)) {
            echo "-- Skipping data for temporary table `$table`\n\n";
            continue;
        }
        
        // Data dengan Batching per 100 baris via cursor() agar hemat memori & tidak melebihi max_allowed_packet
        $buffer = [];
        foreach (DB::table($table)->cursor() as $row) {
            $values = [];
            foreach ((array)$row as $val) {
                if ($val === null) {
                    $values[] = 'NULL';
                } elseif (is_int($val) || is_float($val)) {
                    $values[] = $val;
                } else {
                    $values[] = DB::getPdo()->quote((string)$val);
                }
            }
            $buffer[] = "(" . implode(', ', $values) . ")";
            
            if (count($buffer) >= 100) {
                echo "INSERT INTO `$table` VALUES \n" . implode(",\n", $buffer) . ";\n";
                $buffer = [];
                flush();
            }
        }
        if (!empty($buffer)) {
            echo "INSERT INTO `$table` VALUES \n" . implode(",\n", $buffer) . ";\n\n";
            flush();
        } else {
            echo "\n";
        }
        flush();
    }
    
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit;
} catch (\Exception $e) {
    http_response_code(500);
    echo "Error exporting database: " . $e->getMessage();
}
