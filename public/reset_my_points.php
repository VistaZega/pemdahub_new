<?php
/**
 * Script Reset Poin Reputasi Akun Yayasan / Superadmin
 * Akses: https://perguruanpembda.com/reset_my_points.php?secret=pembda99
 * HAPUS file ini setelah digunakan!
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('Akses ditolak.');
}

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Reputation;
use App\Models\ReputationLog;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');

$dryRun = isset($_GET['dry_run']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Reset Poin Reputasi Yayasan</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 24px; background: #f8fafc; color: #1e293b; }
  .card { background: white; border: 2px solid #000; border-radius: 16px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; max-width: 800px; }
  .ok { background: #dcfce7; border-left: 4px solid #16a34a; padding: 12px; margin: 8px 0; border-radius: 6px; }
  .warn { background: #fef9c3; border-left: 4px solid #ca8a04; padding: 12px; margin: 8px 0; border-radius: 6px; }
  table { width: 100%; border-collapse: collapse; margin-top: 12px; }
  th { background: #0f172a; color: white; padding: 10px 14px; text-align: left; font-size: 12px; }
  td { padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
  .btn { display: inline-block; padding: 8px 16px; border-radius: 10px; font-weight: 800; font-size: 12px; text-decoration: none; border: 2px solid #000; }
  .btn-primary { background: #fbbf24; color: #000; }
  .btn-danger { background: #ef4444; color: #fff; }
</style>
</head>
<body>

<div class="card">
    <h2 style="margin-top:0">👑 Reset Poin Akun Yayasan / Pimpinan <?= $dryRun ? '(Mode Preview / DRY RUN)' : '' ?></h2>
    <p style="font-size:13px;color:#64748b">
        Script ini mendeteksi akun Yayasan / Owner / Superadmin dan dapat mereset total poin menjadi 0 serta membersihkan log poin terkait agar tidak mendominasi skor kompetitif anggota.
    </p>

    <?php if ($dryRun): ?>
    <div class="warn">⚠️ <strong>Mode DRY RUN aktif.</strong> Data belum diubah di database. Klik tombol eksekusi di bawah untuk memproses.</div>
    <?php endif; ?>

    <?php
    // Cari user yayasan / superadmin
    $yayasanUsers = User::where(function($q) {
        $q->where('role', 'ketua_yayasan')
          ->orWhere('role', 'superadmin')
          ->orWhere('username', 'yulzega')
          ->orWhere('name', 'LIKE', '%Yulianus Zega%');
    })->with('reputation')->get();

    echo '<table>';
    echo '<tr><th>User ID</th><th>Nama</th><th>Username</th><th>Role</th><th>Total Poin Sekarang</th><th>Aksi</th></tr>';

    foreach ($yayasanUsers as $u) {
        $pts = $u->reputation?->total_points ?? 0;
        $status = $pts > 0 ? "<span style='color:#dc2626;font-weight:bold'>$pts pts</span>" : "<span style='color:#16a34a;font-weight:bold'>0 pts (Bersih)</span>";

        if (!$dryRun && $pts > 0) {
            try {
                DB::beginTransaction();
                // Reset reputation table
                if ($u->reputation) {
                    $u->reputation->update([
                        'total_points' => 0,
                        'level_name' => 'Newbie'
                    ]);
                }
                // Hapus reputation logs
                ReputationLog::where('user_id', $u->id)->delete();
                DB::commit();
                $status = "✅ Berhasil direset ke 0 pts (Log dibersihkan)";
            } catch (\Exception $e) {
                DB::rollBack();
                $status = "❌ Gagal: " . $e->getMessage();
            }
        }

        echo '<tr>';
        echo '<td>' . $u->id . '</td>';
        echo '<td><strong>' . htmlspecialchars($u->name) . '</strong></td>';
        echo '<td>' . htmlspecialchars($u->username) . '</td>';
        echo '<td>' . htmlspecialchars($u->role) . '</td>';
        echo '<td>' . $status . '</td>';
        echo '<td>' . ($dryRun ? 'Akan direset' : 'Selesai') . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    ?>

    <div style="margin-top:24px;display:flex;gap:10px;">
        <a href="?secret=pembda99&dry_run=1" class="btn btn-primary">🔍 Preview / Cek Saja</a>
        <a href="?secret=pembda99" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin mereset poin akun yayasan ke 0?');">⚡ Eksekusi Reset ke 0</a>
    </div>
</div>

<p style="color:#94a3b8;font-size:11px">⚠️ Hapus file <code>public/reset_my_points.php</code> setelah selesai digunakan.</p>

</body>
</html>
