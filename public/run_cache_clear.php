<?php
/**
 * Safe Cache Clear Runner
 * HAPUS FILE INI SETELAH SELESAI DIGUNAKAN!
 * 
 * Akses via browser: https://perguruanpembda.com/run_cache_clear.php?token=pembda2026clear
 */

$SECRET_TOKEN = 'pembda2026clear';

if (!isset($_GET['token']) || $_GET['token'] !== $SECRET_TOKEN) {
    http_response_code(403);
    die('⛔ Akses ditolak. Gunakan ?token=YOUR_TOKEN');
}

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo '<html><head><title>Cache Clear Runner</title>';
echo '<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.8}';
echo '.ok{color:#00e676}.info{color:#40c4ff}';
echo 'h1{color:#bb86fc}h2{color:#03dac6}pre{background:#16213e;padding:15px;border-radius:8px;overflow-x:auto}</style></head><body>';
echo '<h1>🔧 Pembda Hub - Cache Clear Runner</h1>';
echo '<p class="info">Waktu: ' . now()->format('Y-m-d H:i:s') . '</p>';

if (isset($_GET['run']) && $_GET['run'] === 'yes') {
    echo '<h2>🚀 Mengosongkan Cache Laravel...</h2>';
    echo '<pre>';
    
    $commands = [
        'view:clear'   => 'View Cache',
        'config:clear' => 'Config Cache',
        'cache:clear'  => 'Application Cache',
        'route:clear'  => 'Route Cache'
    ];
    
    foreach ($commands as $cmd => $label) {
        try {
            $output = new \Symfony\Component\Console\Output\BufferedOutput();
            \Illuminate\Support\Facades\Artisan::call($cmd, [], $output);
            $result = $output->fetch();
            echo '<span class="info">[' . $label . ']</span> ' . htmlspecialchars(trim($result)) . "\n";
        } catch (\Exception $e) {
            echo "❌ Error running {$cmd}: " . $e->getMessage() . "\n";
        }
    }

    // Auto-repair corrupt bill years in database
    try {
        $repaired = \DB::table('student_bills')
            ->where(function($q) {
                $q->whereNull('year')
                  ->orWhere('year', '<', 2024)
                  ->orWhere('year', '>', 2035);
            })
            ->update([
                'year' => \DB::raw("CASE WHEN month >= 7 AND month <= 12 THEN 2026 WHEN month >= 1 AND month <= 6 THEN 2027 ELSE 2026 END"),
                'due_date' => \DB::raw("CASE WHEN month >= 7 AND month <= 12 THEN CONCAT('2026-', LPAD(month, 2, '0'), '-10') WHEN month >= 1 AND month <= 6 THEN CONCAT('2027-', LPAD(month, 2, '0'), '-10') ELSE due_date END")
            ]);
        echo '<span class="info">[Database Repair]</span> Auto-fixed ' . $repaired . " corrupt bill years & due dates.\n";
    } catch (\Exception $e) {
        echo "❌ DB Repair warning: " . $e->getMessage() . "\n";
    }
    
    echo '</pre>';
    echo '<p class="ok">✅ Semua cache berhasil dibersihkan!</p>';
} else {
    echo '<br><a href="?token=' . $SECRET_TOKEN . '&run=yes" style="background:#bb86fc;color:#000;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px">▶️ Jalankan Bersihkan Cache</a>';
    echo '<p class="warn">⚠️ Klik tombol di atas untuk membersihkan cache.</p>';
}

echo '<hr><p class="warn">🗑️ <b>PENTING:</b> Hapus file run_cache_clear.php dari folder public/ setelah selesai demi keamanan!</p>';
echo '</body></html>';
