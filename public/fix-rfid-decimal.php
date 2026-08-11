<?php
/**
 * fix-rfid-decimal.php
 * 
 * Script darurat: Konversi rfid_uid siswa dari format desimal ke hex
 * agar konsisten dengan proses AttendanceController saat absensi.
 *
 * Akses: https://perguruanpembda.com/fix-rfid-decimal.php?secret=pembda99
 * Dry-run (preview): https://perguruanpembda.com/fix-rfid-decimal.php?secret=pembda99&dry_run=1
 */

// ─── Keamanan ──────────────────────────────────────────────────────────────
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('403 Forbidden');
}

$dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';

// ─── Bootstrap Laravel ─────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ─── Fungsi helper: apakah string ini angka desimal murni? ─────────────────
function isDecimalUid(string $uid): bool {
    return preg_match('/^\d+$/', $uid)
        && strlen($uid) >= 6
        && strlen($uid) <= 12;
}

// ─── Fungsi konversi desimal → hex 8 digit ─────────────────────────────────
function decimalToHexUid(string $uid): ?string {
    $num = (int) $uid;
    if ($num > 0 && $num <= 4294967295) {
        return strtoupper(str_pad(dechex($num), 8, '0', STR_PAD_LEFT));
    }
    return null;
}

// ─── Mulai Output HTML ─────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fix RFID Decimal → Hex | PembdaHUB</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; color: #1e293b; padding: 2rem; }
  .container { max-width: 900px; margin: 0 auto; }
  h1 { font-size: 1.6rem; font-weight: 700; color: #0f172a; margin-bottom: .25rem; }
  .subtitle { color: #64748b; font-size: .9rem; margin-bottom: 1.5rem; }
  .badge { display: inline-block; padding: .25rem .75rem; border-radius: 999px; font-size: .75rem; font-weight: 700; }
  .badge-dry { background: #fef3c7; color: #92400e; }
  .badge-live { background: #dcfce7; color: #14532d; }
  .card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
  .card h2 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #334155; }
  table { width: 100%; border-collapse: collapse; font-size: .85rem; }
  th { background: #f8fafc; text-align: left; padding: .6rem .75rem; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; }
  td { padding: .6rem .75rem; border-bottom: 1px solid #f1f5f9; }
  tr:last-child td { border-bottom: none; }
  .uid-old { font-family: monospace; color: #dc2626; font-weight: 600; }
  .uid-new { font-family: monospace; color: #16a34a; font-weight: 600; }
  .uid-ok  { font-family: monospace; color: #2563eb; }
  .stat { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .stat-box { flex: 1; min-width: 130px; padding: 1rem; border-radius: 10px; text-align: center; }
  .stat-box .num { font-size: 2rem; font-weight: 800; }
  .stat-box .lbl { font-size: .75rem; color: #64748b; margin-top: .25rem; }
  .s-total { background: #eff6ff; color: #1d4ed8; }
  .s-fix   { background: #fef3c7; color: #92400e; }
  .s-done  { background: #dcfce7; color: #15803d; }
  .s-skip  { background: #f1f5f9; color: #475569; }
  .alert  { padding: .9rem 1.1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .875rem; }
  .alert-info    { background: #eff6ff; border-left: 4px solid #3b82f6; color: #1e40af; }
  .alert-success { background: #dcfce7; border-left: 4px solid #22c55e; color: #14532d; }
  .alert-warning { background: #fef3c7; border-left: 4px solid #f59e0b; color: #78350f; }
  .btn { display: inline-block; margin-top: 1rem; padding: .6rem 1.4rem; border-radius: 8px; font-weight: 600; font-size: .875rem; text-decoration: none; cursor: pointer; }
  .btn-primary { background: #2563eb; color: white; }
  .btn-warning { background: #f59e0b; color: white; }
  code { background: #f1f5f9; padding: .15rem .4rem; border-radius: 4px; font-size: .82rem; }
</style>
</head>
<body>
<div class="container">
  <h1>🔧 Fix RFID: Desimal → Hex</h1>
  <p class="subtitle">Konversi <code>rfid_uid</code> siswa dari format desimal ke hex agar bisa absen.</p>

<?php

// ─── Ambil semua siswa dengan rfid_uid yang terisi ─────────────────────────
$students = DB::table('students')
    ->whereNotNull('rfid_uid')
    ->where('rfid_uid', '!=', '')
    ->select('id', 'full_name', 'nis', 'rfid_uid')
    ->get();

$toFix    = [];  // perlu dikonversi
$alreadyOk = []; // sudah format hex / tidak perlu diubah
$skipped  = [];  // desimal tapi konversi gagal / di luar range

foreach ($students as $s) {
    $uid = strtoupper(trim($s->rfid_uid));
    if (isDecimalUid($uid)) {
        $hexUid = decimalToHexUid($uid);
        if ($hexUid) {
            $toFix[] = [
                'id'        => $s->id,
                'name'      => $s->full_name,
                'nis'       => $s->nis,
                'uid_lama'  => $uid,
                'uid_baru'  => $hexUid,
            ];
        } else {
            $skipped[] = ['id' => $s->id, 'name' => $s->full_name, 'uid' => $uid, 'reason' => 'Di luar range uint32'];
        }
    } else {
        $alreadyOk[] = ['id' => $s->id, 'name' => $s->full_name, 'uid' => $uid];
    }
}

// Mode badge
$modeBadge = $dryRun
    ? '<span class="badge badge-dry">🔍 DRY-RUN (preview saja, tidak ada yang diubah)</span>'
    : '<span class="badge badge-live">⚡ LIVE (perubahan langsung tersimpan)</span>';

echo "<div style='margin-bottom:1rem'>$modeBadge</div>";

// Stats
$countFix  = count($toFix);
$countOk   = count($alreadyOk);
$countSkip = count($skipped);
$countAll  = count($students);

echo <<<HTML
<div class="stat">
  <div class="stat-box s-total"><div class="num">$countAll</div><div class="lbl">Total Siswa ber-RFID</div></div>
  <div class="stat-box s-fix"><div class="num">$countFix</div><div class="lbl">Perlu Dikonversi</div></div>
  <div class="stat-box s-done"><div class="num">$countOk</div><div class="lbl">Sudah Format Hex</div></div>
  <div class="stat-box s-skip"><div class="num">$countSkip</div><div class="lbl">Dilewati</div></div>
</div>
HTML;

// ─── Eksekusi konversi (jika bukan dry-run dan ada yang perlu difix) ────────
$updated = 0;
$errors  = [];

if (!$dryRun && $countFix > 0) {
    DB::beginTransaction();
    try {
        foreach ($toFix as $row) {
            // Pastikan uid baru belum dipakai siswa lain
            $conflict = DB::table('students')
                ->where('rfid_uid', $row['uid_baru'])
                ->where('id', '!=', $row['id'])
                ->first();

            if ($conflict) {
                $errors[] = "Konflik: UID {$row['uid_baru']} sudah dipakai oleh {$conflict->full_name} (ID {$conflict->id}). Siswa {$row['name']} (ID {$row['id']}) dilewati.";
                continue;
            }

            DB::table('students')->where('id', $row['id'])->update([
                'rfid_uid'   => $row['uid_baru'],
                'updated_at' => now(),
            ]);
            $updated++;
        }
        DB::commit();

        Log::info('fix-rfid-decimal.php: konversi selesai', [
            'updated'  => $updated,
            'errors'   => count($errors),
            'dry_run'  => false,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        echo '<div class="alert alert-warning">❌ <strong>Error saat update:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// ─── Tampilkan hasil / preview ──────────────────────────────────────────────
if ($countFix === 0) {
    echo '<div class="alert alert-success">✅ <strong>Semua UID sudah dalam format hex yang benar.</strong> Tidak ada yang perlu dikonversi.</div>';
} elseif ($dryRun) {
    echo '<div class="alert alert-info">ℹ️ Ini adalah <strong>preview</strong>. Tidak ada data yang diubah. Klik tombol di bawah untuk eksekusi nyata.</div>';
} else {
    if ($updated > 0) {
        echo "<div class='alert alert-success'>✅ <strong>Berhasil mengkonversi $updated siswa.</strong></div>";
    }
    if (!empty($errors)) {
        echo '<div class="alert alert-warning"><strong>⚠️ Beberapa UID dilewati karena konflik:</strong><ul style="margin-top:.5rem;padding-left:1.2rem">';
        foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>";
        echo '</ul></div>';
    }
}

// ─── Tabel siswa yang perlu/sudah dikonversi ───────────────────────────────
if (!empty($toFix)) {
    $title = $dryRun ? 'Preview: Siswa yang Akan Dikonversi' : 'Siswa yang Telah Dikonversi';
    echo <<<HTML
    <div class="card">
      <h2>⚙️ $title ($countFix siswa)</h2>
      <table>
        <thead><tr><th>No</th><th>Nama Siswa</th><th>NIS</th><th>UID Lama (Desimal)</th><th>UID Baru (Hex)</th></tr></thead>
        <tbody>
    HTML;
    foreach ($toFix as $i => $row) {
        $n = $i + 1;
        $name = htmlspecialchars($row['name']);
        $nis  = htmlspecialchars($row['nis'] ?? '-');
        echo "<tr><td>$n</td><td>$name</td><td>$nis</td><td class='uid-old'>{$row['uid_lama']}</td><td class='uid-new'>{$row['uid_baru']}</td></tr>";
    }
    echo '</tbody></table></div>';
}

if (!empty($alreadyOk)) {
    $n = count($alreadyOk);
    echo <<<HTML
    <div class="card">
      <h2>✅ Sudah Format Hex — Tidak Diubah ($n siswa)</h2>
      <table>
        <thead><tr><th>No</th><th>Nama Siswa</th><th>NIS</th><th>UID (Hex)</th></tr></thead>
        <tbody>
    HTML;
    foreach ($alreadyOk as $i => $row) {
        $idx = $i + 1;
        $name = htmlspecialchars($row['name']);
        $nis  = '-';
        echo "<tr><td>$idx</td><td>$name</td><td>$nis</td><td class='uid-ok'>{$row['uid']}</td></tr>";
    }
    echo '</tbody></table></div>';
}

if (!empty($skipped)) {
    echo '<div class="card"><h2>⏭️ Dilewati</h2><table><thead><tr><th>ID</th><th>Nama</th><th>UID</th><th>Alasan</th></tr></thead><tbody>';
    foreach ($skipped as $row) {
        echo "<tr><td>{$row['id']}</td><td>".htmlspecialchars($row['name'])."</td><td class='uid-old'>{$row['uid']}</td><td>".htmlspecialchars($row['reason'])."</td></tr>";
    }
    echo '</tbody></table></div>';
}

// ─── Tombol aksi ────────────────────────────────────────────────────────────
$baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
if ($dryRun && $countFix > 0) {
    echo "<a class='btn btn-warning' href='{$baseUrl}?secret=pembda99'>⚡ Jalankan Konversi Nyata (Update Database)</a>&nbsp;";
}
if (!$dryRun && $countFix > 0) {
    echo "<a class='btn btn-primary' href='{$baseUrl}?secret=pembda99&dry_run=1'>🔍 Cek Ulang (Dry-run)</a>&nbsp;";
}

echo '<p style="margin-top:1.5rem;font-size:.8rem;color:#94a3b8">Script ini aman dijalankan berulang kali. Hanya UID dalam format desimal murni yang akan dikonversi.</p>';
?>
</div>
</body>
</html>
