<?php
/**
 * check-rfid.php
 * 
 * Script diagnosa RFID: Cek kecocokan UID siswa/pegawai di database,
 * menampilkan semua variasi candidate format UID dan status siswa.
 *
 * Akses: https://perguruanpembda.com/check-rfid.php?secret=pembda99&uid=73C4661B
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('403 Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$searchUid = trim($_GET['uid'] ?? $_GET['q'] ?? '');

function getCandidates(string $rawUid): array {
    $uid = strtoupper(trim($rawUid));
    $candidates = [$uid];

    $cleanUid = preg_replace('/[^A-F0-9]/i', '', $uid);
    if ($cleanUid && $cleanUid !== $uid) {
        $candidates[] = $cleanUid;
    }

    $ltrimUid = ltrim($cleanUid ?: $uid, '0');
    if ($ltrimUid && $ltrimUid !== $uid) {
        $candidates[] = $ltrimUid;
    }

    $testDecs = array_unique(array_filter([$uid, $cleanUid, $ltrimUid]));
    foreach ($testDecs as $decStr) {
        if (preg_match('/^\d+$/', $decStr) && strlen($decStr) >= 5 && strlen($decStr) <= 12) {
            $num = (float)$decStr;
            if ($num > 0 && $num <= 4294967295) {
                $hex = strtoupper(str_pad(dechex((int)$num), 8, '0', STR_PAD_LEFT));
                $candidates[] = $hex;
                
                if (strlen($hex) === 8) {
                    $revHex = $hex[6].$hex[7].$hex[4].$hex[5].$hex[2].$hex[3].$hex[0].$hex[1];
                    $candidates[] = $revHex;
                    $revDec = (string) hexdec($revHex);
                    $candidates[] = $revDec;
                    $candidates[] = str_pad($revDec, 10, '0', STR_PAD_LEFT);

                    $sub3Hex = substr($hex, 2);
                    $candidates[] = $sub3Hex;
                    $candidates[] = (string) hexdec($sub3Hex);
                }
            }
        }
    }

    $testHexs = array_unique(array_filter([$uid, $cleanUid, $ltrimUid]));
    foreach ($testHexs as $hexStr) {
        if (ctype_xdigit($hexStr)) {
            $padHex = str_pad($hexStr, 8, '0', STR_PAD_LEFT);
            $candidates[] = $padHex;

            $dec = (string) hexdec($padHex);
            $candidates[] = $dec;
            $candidates[] = str_pad($dec, 10, '0', STR_PAD_LEFT);

            if (strlen($padHex) === 8) {
                $revHex = $padHex[6].$padHex[7].$padHex[4].$padHex[5].$padHex[2].$padHex[3].$padHex[0].$padHex[1];
                $candidates[] = $revHex;
                $revDec = (string) hexdec($revHex);
                $candidates[] = $revDec;
                $candidates[] = str_pad($revDec, 10, '0', STR_PAD_LEFT);

                $sub3Hex = substr($padHex, 2);
                $candidates[] = $sub3Hex;
                $candidates[] = (string) hexdec($sub3Hex);
            }
        }
    }

    return array_values(array_unique(array_filter($candidates)));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Diagnostik RFID | PembdaHUB</title>
<style>
  body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #0f172a; padding: 2rem; }
  .card { background: white; border-radius: 1rem; padding: 1.5rem; max-width: 800px; margin: 0 auto 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; }
  form { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
  input[type=text] { flex: 1; padding: .6rem 1rem; border: 1px solid #cbd5e1; border-radius: .5rem; font-family: monospace; }
  button { padding: .6rem 1.2rem; background: #2563eb; color: white; border: none; border-radius: .5rem; font-weight: 600; cursor: pointer; }
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: .9rem; }
  th, td { border: 1px solid #e2e8f0; padding: .6rem .75rem; text-align: left; }
  th { background: #f1f5f9; }
  .badge { display: inline-block; padding: .2rem .6rem; border-radius: .3rem; font-size: .75rem; font-weight: 700; }
  .badge-active { background: #dcfce7; color: #166534; }
  .badge-inactive { background: #fee2e2; color: #991b1b; }
  .code { font-family: monospace; background: #f1f5f9; padding: .1rem .3rem; border-radius: .2rem; }
</style>
</head>
<body>
<div class="card">
  <h1>🔍 Check & Diagnosa RFID</h1>
  <form method="GET">
    <input type="hidden" name="secret" value="pembda99">
    <input type="text" name="uid" value="<?= htmlspecialchars($searchUid) ?>" placeholder="Ketik UID atau Nama Siswa (misal: 73C4661B / Pasrah)...">
    <button type="submit">Cari Data</button>
  </form>

<?php if ($searchUid): ?>
  <?php
    $candidates = getCandidates($searchUid);
    echo "<p><strong>Candidate Variasi Formats:</strong> " . implode(', ', array_map(fn($c) => "<span class='code'>$c</span>", $candidates)) . "</p>";

    // Cari Siswa (by UID candidates OR by name/nis)
    $students = DB::table('students')
        ->whereIn('rfid_uid', $candidates)
        ->orWhere('full_name', 'LIKE', "%{$searchUid}%")
        ->orWhere('nis', 'LIKE', "%{$searchUid}%")
        ->get();

    // Cari Employee
    $employees = DB::table('employees')
        ->whereIn('rfid_uid', $candidates)
        ->orWhere('full_name', 'LIKE', "%{$searchUid}%")
        ->get();
  ?>

  <h2 style="font-size:1.1rem;margin-top:1.5rem">Hasil Pencarian Siswa (<?= count($students) ?>)</h2>
  <?php if (count($students) > 0): ?>
    <table>
      <thead>
        <tr><th>ID</th><th>Nama Siswa</th><th>NIS</th><th>RFID UID di DB</th><th>Status</th><th>Evaluasi RFID</th><th>Absen Hari Ini (<?= date('d/m/Y') ?>)</th></tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
          <?php
            $isActive = in_array(strtolower($s->status), ['calon', 'aktif', 'naik']);
            $matchDirect = in_array(strtoupper($s->rfid_uid ?? ''), $candidates);
            $todayAtt = DB::table('attendances')->where('student_id', $s->id)->where('date', date('Y-m-d'))->first();
          ?>
          <tr>
            <td><?= $s->id ?></td>
            <td><strong><?= htmlspecialchars($s->full_name) ?></strong></td>
            <td><?= htmlspecialchars($s->nis ?? '-') ?></td>
            <td><span class="code"><?= htmlspecialchars($s->rfid_uid ?? 'KOSONG') ?></span></td>
            <td><span class="badge <?= $isActive ? 'badge-active' : 'badge-inactive' ?>"><?= strtoupper($s->status) ?></span></td>
            <td>
              <?php if ($matchDirect && $isActive): ?>
                <span style="color:#16a34a;font-weight:700">✅ Terdaftar &amp; Aktif (BISA ABSEN)</span>
              <?php elseif ($matchDirect && !$isActive): ?>
                <span style="color:#dc2626;font-weight:700">❌ Terdaftar tapi Status Non-Aktif</span>
              <?php else: ?>
                <span style="color:#eab308;font-weight:700">⚠️ Ditemukan dari pencarian nama (RFID DB beda)</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($todayAtt): ?>
                <span style="color:#16a34a;font-weight:700">🎉 Sudah Absen Masuk (<?= substr($todayAtt->time_in, 0, 5) ?>)</span>
                <?= $todayAtt->time_out ? "<br><span style='color:#2563eb'>Pulang: " . substr($todayAtt->time_out, 0, 5) . "</span>" : "" ?>
              <?php else: ?>
                <span style="color:#64748b">Belum Absen Hari Ini</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="color:#dc2626;margin-top:.5rem">❌ Tidak ada siswa yang cocok dengan UID/Nama ini di database.</p>
  <?php endif; ?>

  <?php if (count($employees) > 0): ?>
    <h2 style="font-size:1.1rem;margin-top:1.5rem">Hasil Pencarian Pegawai/Guru (<?= count($employees) ?>)</h2>
    <table>
      <thead><tr><th>ID</th><th>Nama Pegawai</th><th>NIP</th><th>RFID UID</th><th>Aktif</th></tr></thead>
      <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td><?= $e->id ?></td>
            <td><?= htmlspecialchars($e->full_name) ?></td>
            <td><?= htmlspecialchars($e->nip ?? '-') ?></td>
            <td><span class="code"><?= htmlspecialchars($e->rfid_uid ?? 'KOSONG') ?></span></td>
            <td><?= $e->is_active ? '✅ YA' : '❌ TIDAK' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>
