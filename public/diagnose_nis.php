<?php
/**
 * Diagnosa NIS Collision & Auto-Detect Kiosk School (LITE)
 * Akses: perguruanpembda.com/diagnose_nis.php?secret=pembda99
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(30);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

try {
    $basePath = is_dir(__DIR__ . '/../vendor') ? __DIR__ . '/..' : __DIR__ . '/../pembdahub';
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
} catch (\Throwable $e) {
    die('Bootstrap Error: ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine());
}

try {

$today = now()->format('Y-m-d');
echo "=== DIAGNOSA BARCODE / NIS COLLISION ===\n";
echo "Tanggal: $today | Server: " . now()->format('H:i:s') . "\n\n";

// 1. Device ID aktif HARI INI saja (cepat)
echo "--- 1. DEVICE ID AKTIF HARI INI ---\n";
$deviceGroups = \DB::table('attendances')
    ->where('date', $today)
    ->whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->selectRaw('device_id, recorded_via, COUNT(*) as cnt')
    ->groupBy('device_id', 'recorded_via')
    ->orderByDesc('cnt')
    ->get();

if ($deviceGroups->isEmpty()) {
    echo "  (Tidak ada absensi dgn device_id hari ini)\n";
} else {
    foreach ($deviceGroups as $dg) {
        echo "  Device: {$dg->device_id} | Via: {$dg->recorded_via} | Scan: {$dg->cnt}x\n";
    }
}

// 2. Auto-detect: ambil hanya device hari ini, cek mapping-nya
echo "\n--- 2. AUTO-DETECT: DEVICE → SCHOOL (dari absen hari ini) ---\n";
$todayDevices = $deviceGroups->pluck('device_id')->unique();
if ($todayDevices->isEmpty()) {
    echo "  (Tidak ada device hari ini)\n";
} else {
    foreach ($todayDevices as $did) {
        // Ambil 1 attendance siswa dari device ini
        $att = \DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('schools', 'students.school_id', '=', 'schools.id')
            ->where('attendances.device_id', $did)
            ->whereNotNull('attendances.student_id')
            ->select('schools.id as school_id', 'schools.name as school_name', 'schools.type as school_type')
            ->orderByDesc('attendances.id')
            ->first();
        if ($att) {
            echo "  ✅ '$did' → school_id={$att->school_id} ({$att->school_name})\n";
        } else {
            echo "  ❌ '$did' → TIDAK BISA DITENTUKAN\n";
        }
    }
}

// 3. NIS duplikat lintas sekolah (hanya siswa aktif)
echo "\n--- 3. NIS COLLISION LINTAS SEKOLAH ---\n";
$collisions = \DB::select("
    SELECT nis, COUNT(*) as cnt, GROUP_CONCAT(DISTINCT school_id ORDER BY school_id) as school_ids
    FROM students
    WHERE status IN ('aktif','active') AND nis IS NOT NULL AND nis != ''
    GROUP BY nis
    HAVING COUNT(DISTINCT school_id) > 1
    ORDER BY nis
    LIMIT 100
");

if (empty($collisions)) {
    echo "  ✅ Tidak ada NIS duplikat lintas sekolah. Barcode aman.\n";
} else {
    echo "  ⚠️  " . count($collisions) . " NIS BENTROK:\n\n";
    foreach ($collisions as $col) {
        echo "  NIS: {$col->nis} | {$col->cnt}x | Schools: [{$col->school_ids}]\n";
        $students = \DB::table('students')
            ->join('schools', 'students.school_id', '=', 'schools.id')
            ->where('students.nis', $col->nis)
            ->whereIn('students.status', ['aktif', 'active'])
            ->select('students.id', 'students.full_name', 'students.nisn', 'students.school_id', 'schools.name as school_name', 'schools.type as school_type')
            ->get();
        foreach ($students as $s) {
            echo "    → [{$s->school_type}] {$s->full_name} (ID:{$s->id}, NISN:{$s->nisn}, school:{$s->school_name})\n";
        }
        echo "\n";
    }
}

// 4. Detail Alvaro Gavriel
echo "--- 4. SISWA ALVARO GAVRIEL ---\n";
$alvaros = \DB::table('students')
    ->leftJoin('schools', 'students.school_id', '=', 'schools.id')
    ->where('students.full_name', 'like', '%Alvaro Gavriel%')
    ->select('students.*', 'schools.name as school_name', 'schools.type as school_type')
    ->get();

if ($alvaros->isEmpty()) {
    echo "  Tidak ditemukan.\n";
} else {
    foreach ($alvaros as $a) {
        echo "  {$a->full_name} | NIS:{$a->nis} | NISN:{$a->nisn}\n";
        echo "  School: {$a->school_name} (ID:{$a->school_id}) | Status:{$a->status}\n";

        // Cek siswa lain dgn NIS sama
        if ($a->nis) {
            $sameNis = \DB::table('students')
                ->join('schools', 'students.school_id', '=', 'schools.id')
                ->where('students.nis', $a->nis)
                ->where('students.id', '!=', $a->id)
                ->whereIn('students.status', ['aktif', 'active'])
                ->select('students.full_name', 'students.nisn', 'students.school_id', 'schools.name as school_name')
                ->get();
            if ($sameNis->isNotEmpty()) {
                echo "  ⚠️  COLLISION! Siswa lain dgn NIS={$a->nis}:\n";
                foreach ($sameNis as $s) {
                    echo "    → {$s->full_name} (NISN:{$s->nisn}, {$s->school_name})\n";
                }
            } else {
                echo "  ✅ NIS {$a->nis} unik (tidak ada duplikat)\n";
            }
        }

        // Absensi hari ini
        $att = \DB::table('attendances')->where('student_id', $a->id)->where('date', $today)->first();
        if ($att) {
            echo "  Absen: {$att->time_in} | Via: {$att->recorded_via} | Device: {$att->device_id}\n";
        } else {
            echo "  Belum absen hari ini\n";
        }
        echo "\n";
    }
}

// 5. Settings kiosk
echo "--- 5. KIOSK SETTINGS ---\n";
try {
    $v = \App\Models\Setting::getValue('kiosk_device_school_map', '');
    echo "  kiosk_device_school_map = " . ($v ?: '(kosong)') . "\n";
} catch (\Throwable $e) { echo "  kiosk_device_school_map = N/A\n"; }
try {
    $v = \App\Models\Setting::getValue('kiosk_ip_school_map', '');
    echo "  kiosk_ip_school_map = " . ($v ?: '(kosong)') . "\n";
} catch (\Throwable $e) { echo "  kiosk_ip_school_map = N/A\n"; }

echo "\n=== SELESAI ===\n";

} catch (\Throwable $e) {
    echo "\n\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n";
}
