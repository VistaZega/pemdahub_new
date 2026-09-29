<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(15);

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
    $pdo = \DB::connection()->getPdo();
} catch (\Throwable $e) {
    die('Error: ' . $e->getMessage());
}

echo "=== DETEKSI SELURUH DEVICE STATION ABSENSI SE-PERGURUAN PEMBDA ===\n";
echo "Waktu Server: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Daftar Sekolah Aktif
echo "--- 1. UNIT SEKOLAH AKTIF ---\n";
$schools = $pdo->query("SELECT id, name, type, is_active FROM schools ORDER BY id")->fetchAll(PDO::FETCH_OBJ);
foreach ($schools as $sc) {
    echo "  ID={$sc->id} | Type={$sc->type} | Active={$sc->is_active} | {$sc->name}\n";
}

// 2. Semua Station Fisik di Tabel attendances (Siswa)
echo "\n--- 2. DEVICE STATION DI TABEL ATTENDANCES (SISWA) ---\n";
$studentStations = $pdo->query("
    SELECT 
        a.device_id,
        GROUP_CONCAT(DISTINCT a.recorded_via) as vias,
        COUNT(*) as total_scans,
        SUM(CASE WHEN a.date = CURDATE() THEN 1 ELSE 0 END) as today_scans,
        MIN(a.date) as first_date,
        MAX(a.date) as last_date,
        GROUP_CONCAT(DISTINCT sc.type) as school_types_recorded
    FROM attendances a
    LEFT JOIN students s ON a.student_id = s.id
    LEFT JOIN schools sc ON s.school_id = sc.id
    WHERE a.device_id IS NOT NULL 
      AND a.device_id != ''
      AND a.device_id NOT LIKE 'dev_%'
      AND a.device_id NOT LIKE 'Mozilla%'
      AND a.device_id NOT LIKE 'WEB-GPS-%'
    GROUP BY a.device_id
    ORDER BY last_date DESC, total_scans DESC
")->fetchAll(PDO::FETCH_OBJ);

if (empty($studentStations)) {
    echo "  (Tidak ditemukan)\n";
} else {
    foreach ($studentStations as $st) {
        echo sprintf(
            "  %-22s | Via: %-10s | Total: %5dx | Hari Ini: %3dx | Aktif: %s s/d %s | Unit Siswa Tercatat: [%s]\n",
            $st->device_id,
            $st->vias,
            $st->total_scans,
            $st->today_scans,
            $st->first_date,
            $st->last_date,
            $st->school_types_recorded ?? '-'
        );
    }
}

// 3. Semua Station Fisik di Tabel employee_attendances (Guru/Pegawai)
echo "\n--- 3. DEVICE STATION DI TABEL EMPLOYEE_ATTENDANCES (GURU & PEGAWAI) ---\n";
$empStations = $pdo->query("
    SELECT 
        ea.device_id,
        GROUP_CONCAT(DISTINCT ea.recorded_via) as vias,
        COUNT(*) as total_scans,
        SUM(CASE WHEN ea.date = CURDATE() THEN 1 ELSE 0 END) as today_scans,
        MIN(ea.date) as first_date,
        MAX(ea.date) as last_date,
        GROUP_CONCAT(DISTINCT sc.type) as school_types_recorded
    FROM employee_attendances ea
    LEFT JOIN schools sc ON ea.school_id = sc.id
    WHERE ea.device_id IS NOT NULL 
      AND ea.device_id != ''
      AND ea.device_id NOT LIKE 'dev_%'
      AND ea.device_id NOT LIKE 'Mozilla%'
      AND ea.device_id NOT LIKE 'WEB-GPS-%'
    GROUP BY ea.device_id
    ORDER BY last_date DESC, total_scans DESC
")->fetchAll(PDO::FETCH_OBJ);

if (empty($empStations)) {
    echo "  (Tidak ditemukan)\n";
} else {
    foreach ($empStations as $st) {
        echo sprintf(
            "  %-22s | Via: %-10s | Total: %5dx | Hari Ini: %3dx | Aktif: %s s/d %s | Unit Pegawai: [%s]\n",
            $st->device_id,
            $st->vias,
            $st->total_scans,
            $st->today_scans,
            $st->first_date,
            $st->last_date,
            $st->school_types_recorded ?? '-'
        );
    }
}

// 4. Semua Station Fisik di Tabel tefa_attendances
echo "\n--- 4. DEVICE STATION DI TABEL TEFA_ATTENDANCES ---\n";
try {
    $tefaStations = $pdo->query("
        SELECT 
            device_id,
            GROUP_CONCAT(DISTINCT recorded_via) as vias,
            COUNT(*) as total_scans,
            SUM(CASE WHEN date = CURDATE() THEN 1 ELSE 0 END) as today_scans,
            MIN(date) as first_date,
            MAX(date) as last_date
        FROM tefa_attendances
        WHERE device_id IS NOT NULL AND device_id != ''
        GROUP BY device_id
        ORDER BY last_date DESC
    ")->fetchAll(PDO::FETCH_OBJ);
    if (empty($tefaStations)) {
        echo "  (Belum ada data)\n";
    } else {
        foreach ($tefaStations as $st) {
            echo sprintf(
                "  %-22s | Via: %-10s | Total: %5dx | Hari Ini: %3dx | Aktif: %s s/d %s\n",
                $st->device_id, $st->vias, $st->total_scans, $st->today_scans, $st->first_date, $st->last_date
            );
        }
    }
} catch (\Throwable $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// 5. Detail Scan STATION-SMP hari ini (cek siapa yang ter-scan tadi pagi)
echo "\n--- 5. DETAIL SCAN DI STATION-SMP HARI INI ---\n";
$smpScansToday = $pdo->query("
    SELECT a.id, a.time_in, a.time_out, a.recorded_via, a.device_id, s.full_name, s.nis, s.nisn, sc.name as school_name, sc.type as school_type
    FROM attendances a
    JOIN students s ON a.student_id = s.id
    JOIN schools sc ON s.school_id = sc.id
    WHERE a.date = CURDATE() AND a.device_id LIKE '%SMP%'
    ORDER BY a.id ASC
")->fetchAll(PDO::FETCH_OBJ);
if (empty($smpScansToday)) {
    echo "  (Tidak ada scan siswa di STATION-SMP hari ini)\n";
} else {
    foreach ($smpScansToday as $row) {
        echo "  ID={$row->id} | {$row->time_in} | {$row->device_id} ({$row->recorded_via}) -> [{$row->school_type}] {$row->full_name} (NIS:{$row->nis}, NISN:{$row->nisn}, {$row->school_name})\n";
    }
}

echo "\n=== SELESAI ===\n";
