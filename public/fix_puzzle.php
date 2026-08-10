<?php
/**
 * Standalone Emergency Repair Tool for Pembda COLABS (Puzzle)
 * Access: https://perguruanpembda.com/fix_puzzle.php
 */

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo "<html><head><title>Pembda COLABS Hard Reset & Diagnostics</title>";
echo "<style>
    body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; line-height: 1.6; max-width: 800px; margin: 0 auto; }
    h1 { color: #818cf8; margin-bottom: 10px; }
    .card { background: #1e293b; border: 1px solid #334155; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); }
    .ok { color: #4ade80; font-weight: bold; }
    .warn { color: #fbbf24; font-weight: bold; }
    .err { color: #f87171; font-weight: bold; }
    .info { color: #38bdf8; }
    pre { background: #090d16; padding: 15px; border-radius: 8px; overflow-x: auto; color: #a5f3fc; font-size: 13px; border: 1px solid #1e293b; }
    a.btn { display: inline-block; background: #6366f1; color: #ffffff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 15px; transition: background 0.2s; }
    a.btn:hover { background: #4f46e5; }
</style></head><body>";

echo "<h1>🧩 Pembda COLABS Emergency Repair & Hard Reset</h1>";
echo "<div class='card'>";

try {
    // 1. Reset OPcache
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "<p class='ok'>✅ OPcache (Memory Cache) berhasil dibersihkan.</p>";
    }

    // 2. Hapus Compiled Blade Views
    $viewDir = __DIR__ . '/../storage/framework/views/';
    $vDeleted = 0;
    if (is_dir($viewDir)) {
        foreach (glob($viewDir . '*.php') as $vFile) {
            if (@unlink($vFile)) $vDeleted++;
        }
    }
    echo "<p class='ok'>✅ Membuang {$vDeleted} file cache kompilasi Blade view.</p>";

    // 3. Hapus Cache Bootstrap
    $cacheDir = __DIR__ . '/../bootstrap/cache/';
    foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $cFile) {
        if (file_exists($cacheDir . $cFile)) @unlink($cacheDir . $cFile);
    }
    echo "<p class='ok'>✅ Cache konfigurasi & routing dibersihkan.</p>";

    // 4. Bootstrap Laravel
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "<p class='ok'>✅ Framework Laravel berhasil di-load.</p>";

    // 5. HARD RESET DATABASE PUZZLE
    echo "<h3>📊 Eksekusi Hard Reset Database Puzzle...</h3>";

    // WIPE SELURUH REKORD LAMA
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    \App\Models\PuzzlePiece::truncate();
    \App\Models\Puzzle::truncate();
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "<p class='info'>ℹ️ Tabel puzzles & puzzle_pieces telah di-truncate (dibersihkan total).</p>";

    // BUAT PUZZLE BARU SEGAR (10x5 Grid = 50 Keping)
    $puzzle = \App\Models\Puzzle::create([
        'title' => 'Esports Championship (Minggu 1)',
        'image_path' => 'puzzles/pembda_puzzle_1.png',
        'grid_x' => 10,
        'grid_y' => 5,
        'is_active' => true,
    ]);

    $totalPieces = 50;
    $piecesData = [];
    for ($i = 0; $i < $totalPieces; $i++) {
        $piecesData[] = [
            'puzzle_id' => $puzzle->id,
            'piece_index' => $i,
            'is_placed' => false,
            'placed_by_user_id' => null,
            'placed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    \App\Models\PuzzlePiece::insert($piecesData);

    // PASANG TEPAT 10 KEPING BONUS AWAL (20% SELESAI)
    $bonusIds = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
        ->inRandomOrder()
        ->limit(10)
        ->pluck('id');

    \App\Models\PuzzlePiece::whereIn('id', $bonusIds)->update([
        'is_placed' => true,
        'placed_at' => now(),
    ]);

    // VERIFIKASI AKHIR DATABASE
    $totalDB = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->count();
    $placedDB = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->where('is_placed', true)->count();
    $unplacedDB = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->where('is_placed', false)->count();

    echo "<p class='ok'>🎉 HARD RESET PUZZLE SUKSES!</p>";
    echo "<ul>";
    echo "<li><strong>Nama Puzzle:</strong> {$puzzle->title}</li>";
    echo "<li><strong>Ukuran Grid:</strong> {$puzzle->grid_x} x {$puzzle->grid_y} (Total {$totalDB} Keping)</li>";
    echo "<li><strong style='color:#4ade80;'>Keping Terpasang di Papan (Bonus Sistem 20%):</strong> {$placedDB} Keping</li>";
    echo "<li><strong style='color:#38bdf8;'>Keping Tersedia di Inventory Kiri (80%):</strong> {$unplacedDB} Keping</li>";
    echo "</ul>";

    echo "<h3>🔍 Hasil Simulasi Respon API Server (/forum/puzzle):</h3>";
    $controller = new \App\Http\Controllers\ForumController();
    $response = $controller->getPuzzleState();
    $responseData = json_decode($response->getContent(), true);

    echo "<pre>" . htmlspecialchars(json_encode([
        'success' => $responseData['success'],
        'puzzle' => $responseData['puzzle'],
        'board_placed_count' => count(array_filter($responseData['board'])),
        'inventory_count' => count($responseData['inventory']),
    ], JSON_PRETTY_PRINT)) . "</pre>";

    echo "<a href='/forum' class='btn'>🚀 Buka Halaman Forum Sekarang</a>";

} catch (\Exception $e) {
    echo "<p class='err'>❌ Gagal Eksekusi: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div></body></html>";
