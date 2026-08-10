<?php
/**
 * Diagnostik & Force-Seed Menara Prestasi Pembda
 * Akses: https://perguruanpembda.com/fix_tower.php
 */
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Content-Type: text/html; charset=UTF-8');

echo "<html><head><title>Tower Diagnostics</title><style>
body{font-family:system-ui;background:#0f172a;color:#f8fafc;padding:30px;max-width:800px;margin:0 auto;line-height:1.8}
h1{color:#fbbf24}h2{color:#38bdf8}
.ok{color:#4ade80;font-weight:bold}.err{color:#f87171;font-weight:bold}.info{color:#38bdf8}
pre{background:#1e293b;padding:15px;border-radius:8px;overflow-x:auto;font-size:13px;border:1px solid #334155}
a.btn{display:inline-block;background:#f59e0b;color:#000;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:bold;margin-top:15px}
</style></head><body>";

echo "<h1>🧱 Menara Prestasi Pembda — Diagnostik & Force Seed</h1>";

try {
    // 1. Bootstrap Laravel
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "<p class='ok'>✅ Laravel berhasil di-bootstrap</p>";

    // 2. Cek tabel
    $hasBricks = \Illuminate\Support\Facades\Schema::hasTable('pembda_tower_bricks');
    $hasLikes = \Illuminate\Support\Facades\Schema::hasTable('pembda_tower_brick_likes');
    echo "<h2>📋 Status Tabel Database</h2>";
    echo "<p>pembda_tower_bricks: " . ($hasBricks ? "<span class='ok'>ADA ✅</span>" : "<span class='err'>TIDAK ADA ❌</span>") . "</p>";
    echo "<p>pembda_tower_brick_likes: " . ($hasLikes ? "<span class='ok'>ADA ✅</span>" : "<span class='err'>TIDAK ADA ❌</span>") . "</p>";

    // 3. Jalankan migrasi jika tabel belum ada
    if (!$hasBricks || !$hasLikes) {
        echo "<p class='info'>⏳ Menjalankan migrasi...</p>";
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true], $output);
        echo "<pre>" . htmlspecialchars($output->fetch()) . "</pre>";
    }

    // 4. Cek jumlah bata saat ini
    $currentCount = \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')->count();
    echo "<h2>📊 Status Data</h2>";
    echo "<p>Jumlah bata saat ini: <strong>{$currentCount}</strong></p>";

    // 5. Cek user yang ada
    $firstUser = \Illuminate\Support\Facades\DB::table('users')->first();
    echo "<p>User pertama di database: " . ($firstUser ? "<span class='ok'>ID={$firstUser->id}, name={$firstUser->name}, role={$firstUser->role}</span>" : "<span class='err'>TIDAK ADA USER</span>") . "</p>";

    $superAdmin = \Illuminate\Support\Facades\DB::table('users')->where('role', 'superadmin')->first();
    echo "<p>Super Admin: " . ($superAdmin ? "<span class='ok'>ID={$superAdmin->id}, name={$superAdmin->name}</span>" : "<span class='info'>Tidak ditemukan dengan role=superadmin</span>") . "</p>";

    // 6. Cek semua role yang ada
    $roles = \Illuminate\Support\Facades\DB::table('users')->select('role')->distinct()->pluck('role')->toArray();
    echo "<p>Semua role di tabel users: <span class='info'>" . implode(', ', $roles) . "</span></p>";

    // 7. FORCE SEED — hapus data lama dan buat bata baru langsung pakai DB::table
    echo "<h2>🚀 Force Seed 6 Bata Motivasi Awal...</h2>";

    \Illuminate\Support\Facades\DB::table('pembda_tower_brick_likes')->delete();
    \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')->delete();
    echo "<p class='info'>🗑️ Data lama dihapus bersih</p>";

    $userId = $firstUser ? $firstUser->id : 1;
    $schoolId = $firstUser ? $firstUser->school_id : null;
    $pastDate = date('Y-m-d H:i:s', strtotime('-2 days'));

    $samples = [
        ['Selamat Datang di Menara Prestasi Pembda! Mari bersatu membangun masa depan.', 'indigo'],
        ['Semangat belajar untuk seluruh siswa SD, SMP, SMA, dan SMK Pembda!', 'amber'],
        ['Salam hormat untuk Bapak/Ibu Guru dan Pengurus Yayasan Perguruan Pembda Nias.', 'emerald'],
        ['Inovasi, Integritas, dan Prestasi Tanpa Batas!', 'purple'],
        ['Sukses untuk ujian dan kegiatan belajar mengajar minggu ini!', 'rose'],
        ['Bersama Perguruan Pembda, kita pasti bisa menggapai cita-cita tinggi!', 'cyan'],
    ];

    foreach ($samples as $idx => $s) {
        \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')->insert([
            'user_id' => $userId,
            'school_id' => $schoolId,
            'message' => $s[0],
            'color' => $s[1],
            'brick_number' => $idx + 1,
            'likes_count' => rand(3, 12),
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);
        echo "<p class='ok'>✅ Bata #" . ($idx + 1) . " ({$s[1]}): {$s[0]}</p>";
    }

    $finalCount = \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')->count();
    echo "<h2>✅ Hasil Akhir</h2>";
    echo "<p class='ok'>Total bata sekarang: <strong>{$finalCount}</strong> (Tinggi: " . ceil($finalCount / 3) . " Lantai)</p>";

    // 8. Test API response
    echo "<h2>🔍 Simulasi API Response /forum/tower/state</h2>";
    $bricks = \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')
        ->join('users', 'pembda_tower_bricks.user_id', '=', 'users.id')
        ->select('pembda_tower_bricks.*', 'users.name as user_name')
        ->orderBy('brick_number', 'desc')
        ->get();

    echo "<pre>";
    foreach ($bricks as $b) {
        echo "#{$b->brick_number} [{$b->color}] \"{$b->message}\" - oleh {$b->user_name}\n";
    }
    echo "</pre>";

    echo "<a href='/forum' class='btn'>🚀 Buka Forum Sekarang</a>";

} catch (\Exception $e) {
    echo "<p class='err'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
