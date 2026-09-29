<?php
/**
 * Diagnosa NIS Collision - LITE VERSION
 * Akses: perguruanpembda.com/diagnose_nis.php?secret=pembda99
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(15);

if (($_GET['secret'] ?? '') !== 'pembda99') { http_response_code(403); die('Forbidden'); }

header('Content-Type: text/plain; charset=utf-8');
echo "=== DIAGNOSA NIS COLLISION (LITE) ===\n\n";

try {
    $basePath = is_dir(__DIR__ . '/../vendor') ? __DIR__ . '/..' : __DIR__ . '/../pembdahub';
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $pdo = \DB::connection()->getPdo();
    echo "✅ DB Connected\n\n";
} catch (\Throwable $e) {
    die('❌ Error: ' . $e->getMessage());
}

$today = date('Y-m-d');

// 1. Device hari ini
echo "--- DEVICE AKTIF HARI INI ($today) ---\n";
$rows = $pdo->query("SELECT device_id, recorded_via, COUNT(*) as cnt FROM attendances WHERE date='$today' AND device_id IS NOT NULL AND device_id != '' GROUP BY device_id, recorded_via ORDER BY cnt DESC")->fetchAll(PDO::FETCH_OBJ);
if (empty($rows)) {
    echo "  (kosong)\n";
} else {
    foreach ($rows as $r) echo "  {$r->device_id} | {$r->recorded_via} | {$r->cnt}x\n";
}

// 2. Device → School auto-detect (hanya device hari ini)
echo "\n--- DEVICE → SCHOOL MAPPING ---\n";
$devices = array_unique(array_column($rows, 'device_id'));
foreach ($devices as $did) {
    $didEsc = $pdo->quote($did);
    $r = $pdo->query("SELECT s.school_id, sc.name, sc.type FROM attendances a JOIN students s ON a.student_id=s.id JOIN schools sc ON s.school_id=sc.id WHERE a.device_id=$didEsc AND a.student_id IS NOT NULL ORDER BY a.id DESC LIMIT 1")->fetch(PDO::FETCH_OBJ);
    if ($r) {
        echo "  ✅ $did → school_id={$r->school_id} ({$r->name})\n";
    } else {
        echo "  ❌ $did → ?\n";
    }
}

// 3. NIS collision count (ringan)
echo "\n--- NIS COLLISION COUNT ---\n";
$cnt = $pdo->query("SELECT COUNT(*) as total FROM (SELECT nis FROM students WHERE status IN ('aktif','active') AND nis IS NOT NULL AND nis != '' GROUP BY nis HAVING COUNT(DISTINCT school_id) > 1) t")->fetch(PDO::FETCH_OBJ);
echo "  Total NIS bentrok lintas sekolah: " . ($cnt->total ?? 0) . "\n";

if (($cnt->total ?? 0) > 0) {
    echo "\n  Detail (max 20):\n";
    $cols = $pdo->query("SELECT nis, COUNT(*) as cnt, GROUP_CONCAT(DISTINCT school_id) as sids FROM students WHERE status IN ('aktif','active') AND nis IS NOT NULL AND nis != '' GROUP BY nis HAVING COUNT(DISTINCT school_id) > 1 LIMIT 20")->fetchAll(PDO::FETCH_OBJ);
    foreach ($cols as $c) {
        echo "  NIS={$c->nis} ({$c->cnt}x, schools:[{$c->sids}])\n";
        $nisEsc = $pdo->quote($c->nis);
        $ss = $pdo->query("SELECT st.id, st.full_name, st.nisn, st.school_id, sc.name sn, sc.type st2 FROM students st JOIN schools sc ON st.school_id=sc.id WHERE st.nis=$nisEsc AND st.status IN ('aktif','active')")->fetchAll(PDO::FETCH_OBJ);
        foreach ($ss as $s) echo "    → [{$s->st2}] {$s->full_name} (NISN:{$s->nisn}, {$s->sn})\n";
    }
}

// 4. Alvaro
echo "\n--- ALVARO GAVRIEL ---\n";
$als = $pdo->query("SELECT st.*, sc.name sn, sc.type st2 FROM students st LEFT JOIN schools sc ON st.school_id=sc.id WHERE st.full_name LIKE '%Alvaro Gavriel%'")->fetchAll(PDO::FETCH_OBJ);
if (empty($als)) {
    echo "  Tidak ditemukan\n";
} else {
    foreach ($als as $a) {
        echo "  {$a->full_name} | NIS:{$a->nis} | NISN:{$a->nisn} | {$a->sn} (ID:{$a->school_id})\n";
        if ($a->nis) {
            $nisEsc = $pdo->quote($a->nis);
            $dup = $pdo->query("SELECT st.full_name, st.nisn, sc.name sn FROM students st JOIN schools sc ON st.school_id=sc.id WHERE st.nis=$nisEsc AND st.id != {$a->id} AND st.status IN ('aktif','active')")->fetchAll(PDO::FETCH_OBJ);
            if (!empty($dup)) {
                echo "  ⚠️  COLLISION dgn NIS={$a->nis}:\n";
                foreach ($dup as $d) echo "    → {$d->full_name} (NISN:{$d->nisn}, {$d->sn})\n";
            } else {
                echo "  ✅ NIS unik\n";
            }
        }
        $att = $pdo->query("SELECT * FROM attendances WHERE student_id={$a->id} AND date='$today' LIMIT 1")->fetch(PDO::FETCH_OBJ);
        echo $att ? "  Absen: {$att->time_in} | Via:{$att->recorded_via} | Dev:{$att->device_id}\n" : "  Belum absen\n";
    }
}

echo "\n=== SELESAI ===\n";
