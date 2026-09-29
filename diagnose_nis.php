<?php
// Script diagnosa: cek data absensi hari ini dan potensi NIS collision
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$today = now()->format('Y-m-d');

echo "=== DIAGNOSA BARCODE / NIS COLLISION ===\n";
echo "Tanggal: $today\n\n";

// 1. Cek semua device_id yang digunakan hari ini
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

// 2. Cek device_id → school mapping otomatis
echo "\n--- 2. AUTO-DETECT: DEVICE → SCHOOL MAPPING ---\n";
$deviceIds = \App\Models\Attendance::whereNotNull('device_id')
    ->where('device_id', '!=', '')
    ->distinct()
    ->pluck('device_id');

foreach ($deviceIds as $did) {
    $lastAtt = \App\Models\Attendance::where('device_id', $did)
        ->whereNotNull('student_id')
        ->latest('id')
        ->first();
    if ($lastAtt && $lastAtt->student) {
        $school = $lastAtt->student->school;
        echo "  Device '$did' → school_id={$school->id} ({$school->name})\n";
    } else {
        echo "  Device '$did' → TIDAK BISA DITENTUKAN\n";
    }
}

// 3. Cek NIS yang duplikat lintas sekolah
echo "\n--- 3. NIS COLLISION: NIS YANG DUPLIKAT LINTAS SEKOLAH ---\n";
$activeStatuses = \App\Models\StudentStatusHistory::ACTIVE_STATUSES;
$collisions = \DB::table('students')
    ->select('nis', \DB::raw('COUNT(*) as cnt'), \DB::raw('GROUP_CONCAT(DISTINCT school_id) as school_ids'))
    ->whereIn('status', $activeStatuses)
    ->whereNotNull('nis')
    ->where('nis', '!=', '')
    ->groupBy('nis')
    ->havingRaw('COUNT(DISTINCT school_id) > 1')
    ->get();

if ($collisions->isEmpty()) {
    echo "  ✅ Tidak ada NIS yang duplikat lintas sekolah! Barcode aman.\n";
} else {
    echo "  ⚠️ DITEMUKAN " . $collisions->count() . " NIS yang BENTROK lintas sekolah:\n\n";
    foreach ($collisions as $col) {
        echo "  NIS: {$col->nis} | Jumlah siswa: {$col->cnt} | School IDs: {$col->school_ids}\n";
        // Detail siswa
        $students = \App\Models\Student::where('nis', $col->nis)
            ->whereIn('status', $activeStatuses)
            ->with('school:id,name,type')
            ->get();
        foreach ($students as $s) {
            echo "    → [{$s->school->type}] {$s->full_name} (NISN: {$s->nisn}, school: {$s->school->name})\n";
        }
        echo "\n";
    }
}

// 4. Cek absensi Alvaro hari ini
echo "\n--- 4. ABSENSI ALVARO GAVRIEL HARI INI ---\n";
$alvaro = \App\Models\Student::where('full_name', 'like', '%Alvaro Gavriel%')->get();
if ($alvaro->isEmpty()) {
    echo "  Siswa 'Alvaro Gavriel' tidak ditemukan di database.\n";
} else {
    foreach ($alvaro as $a) {
        $school = $a->school;
        echo "  Siswa: {$a->full_name} | NIS: {$a->nis} | NISN: {$a->nisn} | School: " . ($school->name ?? '?') . " (ID: {$a->school_id})\n";
        
        $att = \App\Models\Attendance::where('student_id', $a->id)->where('date', $today)->first();
        if ($att) {
            echo "    Absen: {$att->time_in} | Via: {$att->recorded_via} | Device: {$att->device_id} | Status: {$att->status}\n";
        } else {
            echo "    Belum absen hari ini\n";
        }
    }
}

// 5. Cek jika ada siswa SMA dengan NIS yang sama dgn Alvaro
if ($alvaro->isNotEmpty()) {
    $alvaroNis = $alvaro->first()->nis;
    if ($alvaroNis) {
        echo "\n--- 5. SISWA LAIN DENGAN NIS YANG SAMA ($alvaroNis) ---\n";
        $sameNis = \App\Models\Student::where('nis', $alvaroNis)
            ->where('id', '!=', $alvaro->first()->id)
            ->with('school:id,name,type')
            ->get();
        if ($sameNis->isEmpty()) {
            echo "  Tidak ada siswa lain dengan NIS yang sama.\n";
        } else {
            foreach ($sameNis as $s) {
                echo "  → {$s->full_name} | NISN: {$s->nisn} | School: " . ($s->school->name ?? '?') . " | Status: {$s->status}\n";
            }
        }
    }
}

echo "\n=== SELESAI ===\n";
