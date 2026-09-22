<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Run `php artisan schedule:run` every minute via cron/Task Scheduler:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Auto process queued background jobs (WhatsApp, Bulk Reports, etc.)
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->description('Process pending database queue jobs');

// Auto close survey and notify via WhatsApp
Schedule::command('surveys:close-and-notify')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->description('Auto-close survey and send WhatsApp notifications');

// Clean up old failed jobs (keep last 7 days)
Schedule::command('queue:prune-failed --hours=168')
    ->daily()
    ->at('02:00')
    ->description('Prune failed jobs older than 7 days');

// Restart queue workers to free memory (if running via supervisor)
Schedule::command('queue:restart')
    ->dailyAt('03:00')
    ->description('Restart queue workers to free memory');

// Prune stale job batches (older than 48 hours)
Schedule::command('queue:prune-batches --hours=48')
    ->daily()
    ->at('02:30')
    ->description('Prune completed job batches');

// Clear expired password reset tokens
Schedule::command('auth:clear-resets')
    ->daily()
    ->at('04:00')
    ->description('Clear expired password reset tokens');

// Clear old cache entries
Schedule::command('cache:prune-stale-tags')
    ->hourly()
    ->description('Prune stale cache tags');

// Automated database backup - daily at 01:00
Schedule::command('backup:database --compress --keep=7')
    ->dailyAt('01:00')
    ->withoutOverlapping(60)
    ->onOneServer()
    ->runInBackground()
    ->description('Automated database backup with compression');

// Log application health check
Schedule::call(function () {
    $checks = [
        'database' => false,
        'storage_writable' => false,
        'queue_size' => 0,
    ];

    try {
        \Illuminate\Support\Facades\DB::select('SELECT 1');
        $checks['database'] = true;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::channel('daily')->critical('Health check: Database down', [
            'error' => $e->getMessage(),
        ]);
    }

    $checks['storage_writable'] = is_writable(storage_path('logs'));

    try {
        $checks['queue_size'] = \Illuminate\Support\Facades\DB::table('jobs')->count();
    } catch (\Exception $e) {
        // jobs table might not exist
    }

    if ($checks['queue_size'] > 100) {
        \Illuminate\Support\Facades\Log::channel('daily')->warning('Health check: Queue backlog', [
            'queue_size' => $checks['queue_size'],
        ]);
    }

    \Illuminate\Support\Facades\Log::channel('daily')->info('Health check passed', $checks);
})->everyFifteenMinutes()->description('Application health check');

// ============================================================================
// Auto-Keepalive Self-Hosted WhatsApp Engine (Node.js Baileys)
// Mengecek ketersediaan server Node.js di port 3000 setiap 5 menit.
// Jika terhenti/mati, otomatis dinyalakan kembali di background secara mandiri.
// ============================================================================
Schedule::call(function () {
    $rootDir = base_path();
    $serverPath = "{$rootDir}/whatsapp-server/server.js";

    foreach ([3002, 3000] as $port) {
        $ch = @curl_init("http://localhost:{$port}/device");
        if ($ch) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                return; // Node.js engine is alive
            }
        }
    }

    if (file_exists($serverPath)) {
        $nodeBin = '/usr/bin/node';
        if (!file_exists($nodeBin)) {
            $which = trim(@shell_exec('which node 2>/dev/null') ?? '');
            $nodeBin = $which ?: 'node';
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            @pclose(@popen("start /B {$nodeBin} {$serverPath}", "r"));
        } else {
            $cmd = "nohup {$nodeBin} {$serverPath} > /dev/null 2>&1 &";
            @exec($cmd);
        }

        \Illuminate\Support\Facades\Log::channel('whatsapp')->info('Auto-Keepalive: Node.js WhatsApp Engine restarted automatically');
    }
})->everyFiveMinutes()->name('wa-engine-keepalive')->withoutOverlapping();

// ============================================================================
// WhatsApp Daily Attendance Digest — Senin s/d Jumat pukul 08:00 WIB
// Mengirim rekap kehadiran harian (siswa, guru, pegawai) ke Kepala Sekolah
// dan rekap kelas ke Wali Kelas, 15 menit setelah batas toleransi jam masuk.
// ============================================================================
// Schedule::command('wa:digest attendance-daily')
//     ->weekdays()
//     ->at('08:00')
//     ->timezone('Asia/Jakarta')
//     ->withoutOverlapping(180)
//     ->runInBackground()
//     ->description('Kirim Rekap Kehadiran Harian ke Kepala Sekolah & Wali Kelas via WhatsApp');

