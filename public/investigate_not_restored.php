<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$action = $_REQUEST['action'] ?? 'summary';

if ($action === 'check_queue') {
    echo "<h3>Queue / Jobs Check</h3>";
    
    try {
        $jobs = DB::table('jobs')->count();
        echo "Pending Jobs in `jobs` table: " . $jobs . "<br>";
        if ($jobs > 0) {
            $firstJob = DB::table('jobs')->first();
            echo "First Job Payload:<br><pre>" . print_r(json_decode($firstJob->payload, true), true) . "</pre>";
        }
    } catch (\Throwable $e) {
        echo "Error checking jobs table: " . $e->getMessage() . "<br>";
    }

    try {
        $failedJobs = DB::table('failed_jobs')->count();
        echo "Failed Jobs in `failed_jobs` table: " . $failedJobs . "<br>";
    } catch (\Throwable $e) {
        echo "Error checking failed_jobs table: " . $e->getMessage() . "<br>";
    }
    
    try {
        $logs = DB::table('activity_logs')->where('action', 'deleted')->count();
        echo "Deleted events in `activity_logs`: " . $logs . "<br>";
    } catch (\Throwable $e) {
        echo "Error checking activity_logs: " . $e->getMessage() . "<br>";
    }
    
}
