<?php
/**
 * Diagnosa NIS Collision & Auto-Detect Kiosk School
 * Akses: perguruanpembda.com/diagnose_nis.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

// Bootstrap Laravel
require __DIR__ . '/../pembdahub/vendor/autoload.php';
$app = require_once __DIR__ . '/../pembdahub/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

$today = now()->format('Y-m-d');

echo "=== DIAGNOSA BARCODE / NIS COLLISION ===\n";
echo "Tanggal: $today\n";
echo "Server Time: " . now()->format('Y-m-d H:i:s') . "\n\n";

// 1. Device ID aktif hari ini
echo "--- 1. DEVICE ID YANG AKTIF HARI INI ---\n";
$deviceGroups = \App\Models\Attendance::where('date', $today)
    ->whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->selectRaw('device_id, recorded_via, COUNT(*) as cnt')
    ->groupBy('device_id', 'recorded_via')
    ->orderByDesc('cnt')
    ->get();

if ($deviceGroups->isEmpty()) {
    echo "  (Tidak ada data absensi dengan device_id hari ini)\n";
} else {
    foreach ($deviceGroups as $dg) {
        echo "  Device: {$dg->device_id} | Via: {$dg->recorded_via} | Jumlah scan: {$dg->cnt}\n";
    }
}

// 2. Auto-detect device → school mapping
echo "\n--- 2. AUTO-DETECT: DEVICE → SCHOOL MAPPING (dari seluruh riwayat) ---\n";
$deviceIds = \App\Models\Attendance::whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->distinct()
    ->pluck('device_id');

if ($deviceIds->isEmpty()) {
    echo "  (Tidak ada device_id yang tercatat di database)\n";
} else {
    foreach ($deviceIds as $did) {
        $lastAtt = \App\Models\Attendance::where('device_id', $did)
            ->whereNotNull('student_id')
            ->latest('id')
            ->first();
        if ($lastAtt && $lastAtt->student && $lastAtt->student->school) {
            $school = $lastAtt->student->school;
            echo "  ✅ Device '$did' → school_id={$school->id} ({$school->name})\n";
        } else {
            echo "  ❌ Device '$did' → TIDAK BISA DITENTUKAN\n";
        }
    }
}

// 3. NIS duplikat lintas sekolah
echo "\n--- 3. NIS COLLISION: NIS YANG DUPLIKAT LINTAS SEKOLAH (siswa aktif) ---\n";
$activeStatuses = \App\Models\StudentStatusHistory::ACTIVE_STATUSES;
$collisions = \DB::table('students')
    ->select('nis', \DB::raw('COUNT(*) as cnt'), \DB::raw('GROUP_CONCAT(DISTINCT school_id ORDER BY school_id) as school_ids'))
    ->whereIn('status', $activeStatuses)
    ->whereNotNull('nis')
    ->where('nis', '!=', '')
    ->groupBy('nis')
    ->havingRaw('COUNT(DISTINCT school_id) > 1')
    ->orderBy('nis')
    ->get();

if ($collisions->isEmpty()) {
    echo "  ✅ Tidak ada NIS yang duplikat lintas sekolah. Semua barcode aman.\n";
} else {
    echo "  ⚠️  DITEMUKAN " . $collisions->count() . " NIS BENTROK lintas sekolah:\n\n";
    foreach ($collisions->take(50) as $col) {
        echo "  NIS: {$col->nis} | Duplikat: {$col->cnt}x | School IDs: [{$col->school_ids}]\n";
        $students = \App\Models\Student::where('nis', $col->nis)
            ->whereIn('status', $activeStatuses)
            ->with('school:id,name,type')
            ->get();
        foreach ($students as $s) {
            $schoolName = $s->school->name ?? '?';
            $schoolType = $s->school->type ?? '?';
            echo "    → [$schoolType] {$s->full_name} (ID:{$s->id}, NISN:{$s->nisn}, school_id:{$s->school_id} = $schoolName)\n";
        }
        echo "\n";
    }
    if ($collisions->count() > 50) {
        echo "  ... dan " . ($collisions->count() - 50) . " NIS lainnya\n";
    }
}

// 4. Detail Alvaro Gavriel
echo "\n--- 4. DETAIL SISWA ALVARO GAVRIEL ---\n";
$alvaros = \App\Models\Student::where('full_name', 'like', '%Alvaro Gavriel%')->with('school:id,name,type')->get();
if ($alvaros->isEmpty()) {
    echo "  Siswa 'Alvaro Gavriel' tidak ditemukan.\n";
} else {
    foreach ($alvaros as $a) {
        $schoolName = $a->school->name ?? '?';
        echo "  Siswa: {$a->full_name}\n";
        echo "    ID: {$a->id} | NIS: {$a->nis} | NISN: {$a->nisn} | School: $schoolName (ID:{$a->school_id})\n";
        echo "    Status: {$a->status} | RFID: " . ($a->rfid_uid ?: 'belum terdaftar') . "\n";

        // Cari siswa lain dengan NIS sama
        if ($a->nis) {
            $sameNis = \App\Models\Student::where('nis', $a->nis)
                ->where('id', '!=', $a->id)
                ->whereIn('status', $activeStatuses)
                ->with('school:id,name,type')
                ->get();
            if ($sameNis->isNotEmpty()) {
                echo "    ⚠️  ADA SISWA LAIN DENGAN NIS SAMA ({$a->nis}):\n";
                foreach ($sameNis as $s) {
                    echo "      → {$s->full_name} (ID:{$s->id}, NISN:{$s->nisn}, school:{$s->school->name})\n";
                }
            } else {
                echo "    ✅ NIS {$a->nis} unik, tidak ada duplikat.\n";
            }
        }

        // Absensi hari ini
        $att = \App\Models\Attendance::where('student_id', $a->id)->where('date', $today)->first();
        if ($att) {
            echo "    Absen hari ini: {$att->time_in} | Via: {$att->recorded_via} | Device: {$att->device_id} | Status: {$att->status}\n";
        } else {
            echo "    Belum absen hari ini.\n";
        }
        echo "\n";
    }
}

// 5. Cek setting kiosk mapping
echo "--- 5. SETTING KIOSK MAPPING DI DATABASE ---\n";
try {
    $devMap = \App\Models\Setting::getValue('kiosk_device_school_map', '');
    echo "  kiosk_device_school_map = " . ($devMap ?: '(belum diset)') . "\n";
} catch (\Throwable $e) {
    echo "  kiosk_device_school_map = ERROR: " . $e->getMessage() . "\n";
}
try {
    $ipMap = \App\Models\Setting::getValue('kiosk_ip_school_map', '');
    echo "  kiosk_ip_school_map = " . ($ipMap ?: '(belum diset)') . "\n";
} catch (\Throwable $e) {
    echo "  kiosk_ip_school_map = ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
