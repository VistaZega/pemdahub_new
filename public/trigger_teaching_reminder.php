<?php
/**
 * Standalone Tool: Pengingat Jadwal Mengajar Harian - Gelombang Bertahap (Staggered Batching)
 * Akses: https://perguruanpembda.com/trigger_teaching_reminder.php?secret=pembda99
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(1200);
ignore_user_abort(true);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Akses Ditolak: Secret key wajib (?secret=pembda99)');
}

// Bootstrap Laravel
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/pembdahub/vendor/autoload.php')) {
    require __DIR__ . '/pembdahub/vendor/autoload.php';
    $app = require_once __DIR__ . '/pembdahub/bootstrap/app.php';
} else {
    die('Autoload file not found.');
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Setting;
use App\Services\ExecutiveReportService;
use App\Services\WhatsAppService;
use Illuminate\Support\Carbon;

$waService = app(WhatsAppService::class);
$reportService = app(ExecutiveReportService::class);

$run = isset($_GET['run']) && $_GET['run'] == '1';
$dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';
$force = isset($_GET['force']) && $_GET['force'] == '1';
$testPhone = !empty($_GET['test_phone']) ? trim($_GET['test_phone']) : null;
$schoolParam = $_GET['school'] ?? ($_GET['school_id'] ?? null);

$activeYear = AcademicYear::where('is_active', true)->first();
$dayOfWeek = strtolower(Carbon::now()->format('l'));
$dayNames = [
    'sunday' => 'Minggu', 'monday' => 'Senin', 'tuesday' => 'Selasa',
    'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu'
];
$dayIndo = $dayNames[$dayOfWeek] ?? ucfirst($dayOfWeek);
$dateFormatted = Carbon::now()->translatedFormat('d F Y');

// Jeda aman anti-ban (default 60 - 90 detik)
$delayMin = isset($_GET['delay_min']) ? (int)$_GET['delay_min'] : (int)Setting::getValue('wa_teaching_delay_min', 60);
$delayMax = isset($_GET['delay_max']) ? (int)$_GET['delay_max'] : (int)Setting::getValue('wa_teaching_delay_max', 90);

// JIKA BELUM ADA AKSI (TAMPILKAN CONTROL PANEL)
if (!$run && !$dryRun && !$testPhone) {
    $schedules = Schedule::with(['teacher', 'classroom', 'school'])
        ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
        ->where('day_of_week', $dayOfWeek)
        ->get();

    $teacherCount = $schedules->groupBy('teacher_id')->count();
    $slotCount = $schedules->count();

    $schools = School::schoolsOnly()->get();
    $schoolStats = [];
    foreach ($schools as $sc) {
        $count = $schedules->filter(function($sch) use ($sc) {
            $sid = $sch->school_id ?: ($sch->classroom?->school_id ?: ($sch->teacher?->school_id ?? null));
            return $sid == $sc->id;
        })->groupBy('teacher_id')->count();

        $typeKey = strtolower($sc->type);
        $timeSlot = match($typeKey) {
            'smp' => '05:00 WIB',
            'sma' => '05:40 WIB',
            'smk' => '06:30 WIB',
            default => '06:30 WIB'
        };

        $schoolStats[$sc->id] = [
            'id' => $sc->id,
            'name' => $sc->name,
            'type' => $typeKey,
            'count' => $count,
            'time' => $timeSlot,
        ];
    }

    $accountInfo = $waService->getAccountInfo();
    $quota = $accountInfo['data']['quota'] ?? 'N/A';
    $deviceStatus = $accountInfo['data']['device_status'] ?? ($waService->isConnected() ? 'connect' : 'disconnect');
    $deviceNumber = $accountInfo['data']['device'] ?? '082373642864';

    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Control Panel - Pengingat Jadwal Mengajar (Gelombang Bertahap)</title>";
    echo "<style>
    body { font-family: system-ui, -apple-system, sans-serif; background: #0b1120; color: #f8fafc; padding: 25px; margin: 0; }
    .card { background: #1e293b; padding: 28px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); max-width: 880px; margin: auto; border: 1px solid #334155; }
    h2 { color: #38bdf8; margin-top: 0; border-bottom: 1px solid #334155; padding-bottom: 14px; font-size: 22px; display: flex; align-items: center; gap: 10px; }
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 20px 0; }
    .stat-box { background: #0f172a; padding: 18px; border-radius: 12px; border: 1px solid #334155; }
    .stat-val { font-size: 24px; font-weight: bold; color: #38bdf8; }
    .stat-lbl { font-size: 13px; color: #94a3b8; margin-top: 4px; }
    .alert-box { background: #1e1b4b; border-left: 4px solid #818cf8; padding: 14px 18px; border-radius: 8px; margin: 15px 0; font-size: 13px; line-height: 1.6; }
    .action-section { margin-top: 25px; background: #0f172a; padding: 22px; border-radius: 12px; border: 1px solid #334155; }
    .wave-card { background: #1e293b; border: 1px solid #334155; padding: 16px; border-radius: 10px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .wave-info { font-size: 14px; }
    .wave-time { display: inline-block; background: #0284c7; color: white; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; margin-left: 8px; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 13px; cursor: pointer; border: none; }
    .btn-green { background: #10b981; color: white; }
    .btn-green:hover { background: #059669; }
    .btn-blue { background: #0284c7; color: white; }
    .btn-blue:hover { background: #0369a1; }
    .btn-amber { background: #f59e0b; color: white; }
    .btn-amber:hover { background: #d97706; }
    .btn-purple { background: #8b5cf6; color: white; }
    .btn-purple:hover { background: #7c3aed; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .badge-ok { background: #065f46; color: #34d399; }
    .badge-err { background: #7f1d1d; color: #f87171; }
    input[type=text] { background: #1e293b; border: 1px solid #475569; color: white; padding: 10px 14px; border-radius: 8px; font-size: 14px; width: 220px; }
    </style></head><body><div class='card'>";

    echo "<h2>⏰ Pengingat Jadwal Mengajar Harian (Gelombang Bertahap & Spintax Acak)</h2>";
    
    echo "<div class='alert-box'>";
    echo "🛡️ <strong>Sistem Proteksi Ganda Anti-Banned Aktif:</strong><br>";
    echo "1. <strong>Gelombang Bertahap (Staggered Batching):</strong> Dipecah per unit sekolah (SMP 05:00, SMA 05:40, SMK 06:30 WIB) dengan jeda aman <strong>{$delayMin} - {$delayMax} detik</strong>.<br>";
    echo "2. <strong>Mesin Spin Teks Acak (Polymorphic Spintax & Motto):</strong> Setiap guru menerima salam, judul, susunan kalimat, motto institusi secara acak ('Keep Moving Forward', 'Maju Terus Pantang Mundur', 'Semangat', 'Stop Ask Just Action', 'Progresive In Harmony'), dan doa penutup yang selalu unik dan dinamis.";
    echo "</div>";

    echo "<div class='stat-grid'>";
    echo "<div class='stat-box'><div class='stat-val'>{$teacherCount} Guru</div><div class='stat-lbl'>Total Mengajar Hari Ini ({$dayIndo})</div></div>";
    echo "<div class='stat-box'><div class='stat-val'>{$delayMin}-{$delayMax}s</div><div class='stat-lbl'>Jeda Aman Antar Guru</div></div>";
    echo "<div class='stat-box'><div class='stat-val'>" . ($activeYear?->name ?? '-') . "</div><div class='stat-lbl'>Tahun Pelajaran Aktif</div></div>";
    echo "<div class='stat-box'><div class='stat-val'>" . ($deviceStatus === 'connect' ? "<span class='badge badge-ok'>TERHUBUNG</span>" : "<span class='badge badge-err'>TERPUTUS / COOLDOWN</span>") . "</div><div class='stat-lbl'>Gateway ({$deviceNumber}) | Kuota: {$quota}</div></div>";
    echo "</div>";

    echo "<div class='action-section'>";
    echo "<h3 style='margin-top:0; font-size:16px; color:#e2e8f0;'>📅 Jadwal Cron Otomatis Harian (Senin - Jumat)</h3>";
    echo "<p style='font-size:13px; color:#94a3b8;'>Setiap pagi, cronjob server otomatis mengeksekusi 3 gelombang berikut secara terpisah:</p>";

    foreach ($schoolStats as $sc) {
        echo "<div class='wave-card'>";
        echo "<div class='wave-info'>";
        echo "<strong>🏫 {$sc['name']}</strong> <span class='wave-time'>{$sc['time']}</span><br>";
        echo "<span style='color:#94a3b8; font-size:12px;'>Estimasi: {$sc['count']} Guru × ~20 detik jeda = selesai dalam " . round(($sc['count'] * 20)/60, 1) . " menit</span>";
        echo "</div>";
        echo "<div>";
        echo "<a href='?secret=pembda99&dry_run=1&school={$sc['type']}' class='btn btn-amber' style='margin-right:6px;'>🔍 Simulasi</a>";
        echo "<a href='?secret=pembda99&run=1&school={$sc['type']}&force=1' class='btn btn-blue' onclick='return confirm(\"Kirim Pengingat Gelombang {$sc['name']} ({$sc['count']} Guru)?\")'>🚀 Kirim Unit Ini</a>";
        echo "</div>";
        echo "</div>";
    }
    echo "</div>";

    echo "<div class='action-section'>";
    echo "<h3 style='margin-top:0; font-size:16px; color:#e2e8f0;'>🧪 Uji Coba Pengiriman Sampel (1 Pesan Saja)</h3>";
    echo "<p style='font-size:13px; color:#94a3b8;'>Kirim 1 contoh format pengingat ke nomor WhatsApp Anda tanpa mengirim ke guru lain:</p>";
    echo "<form method='GET' style='display:flex; gap:10px; align-items:center; flex-wrap:wrap;'>";
    echo "<input type='hidden' name='secret' value='pembda99'>";
    echo "<input type='text' name='test_phone' value='08126938933' placeholder='Nomor WhatsApp'>";
    echo "<button type='submit' class='btn btn-purple'>🧪 Kirim Contoh Ke Nomor Ini</button>";
    echo "</form>";
    echo "</div>";

    echo "</div></body></html>";
    exit;
}

// JIKA RUN, DRY-RUN, ATAU TEST-PHONE DIPILIH -> LIVE STREAMING
if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', 1); }
@ini_set('zlib.output_compression', 'Off');
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(true);

echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Proses Pengingat Jadwal Mengajar Guru</title>";
echo "<style>
body { font-family: 'JetBrains Mono', monospace, sans-serif; background: #0b1120; color: #f8fafc; padding: 25px; margin: 0; }
.card { background: #1e293b; padding: 25px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); max-width: 900px; margin: auto; border: 1px solid #334155; }
h2 { color: #38bdf8; margin-top: 0; border-bottom: 1px solid #334155; padding-bottom: 12px; font-size: 18px; }
pre { background: #030712; padding: 18px; border-radius: 10px; color: #e2e8f0; font-size: 13px; line-height: 1.7; max-height: 540px; overflow-y: auto; border: 1px solid #1f2937; }
.ok { color: #4ade80; font-weight: bold; }
.warn { color: #fbbf24; }
.err { color: #f87171; font-weight: bold; }
.info { color: #38bdf8; }
.badge { display: inline-block; padding: 4px 10px; background: #0284c7; color: #fff; border-radius: 6px; font-weight: bold; font-size: 11px; text-transform: uppercase; margin-right: 6px; }
.badge-sim { background: #f59e0b; }
.badge-force { background: #ef4444; }
.badge-test { background: #8b5cf6; }
.btn-back { display: inline-block; margin-top: 15px; padding: 10px 18px; background: #334155; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
</style></head><body><div class='card'>";
echo "<h2>⏰ PROSES PENGINGAT JADWAL MENGAJAR (HARI INI)</h2>";

echo "<div>";
if ($dryRun) echo "<span class='badge badge-sim'>MODE SIMULASI (DRY-RUN)</span>";
if ($force) echo "<span class='badge badge-force'>FORCE SEND</span>";
if ($testPhone) echo "<span class='badge badge-test'>UJI COBA KE: " . htmlspecialchars($testPhone) . "</span>";
if ($schoolParam) echo "<span class='badge'>GELOMBANG UNIT: " . htmlspecialchars(strtoupper($schoolParam)) . "</span>";
echo "<span class='badge'>JEDA: {$delayMin}-{$delayMax}s</span>";
echo "</div><br>";

echo "<pre>";

function logMsg($msg) {
    $time = date('H:i:s');
    $clean = htmlspecialchars($msg);
    if (strpos($msg, '✅') !== false) {
        echo "<span class='ok'>[{$time}] {$clean}</span>\n";
    } elseif (strpos($msg, '❌') !== false || strpos($msg, '🛑') !== false || strpos($msg, '⛔') !== false) {
        echo "<span class='err'>[{$time}] {$clean}</span>\n";
    } elseif (strpos($msg, '⚠️') !== false || strpos($msg, '⏳') !== false || strpos($msg, '⏭️') !== false) {
        echo "<span class='warn'>[{$time}] {$clean}</span>\n";
    } else {
        echo "<span class='info'>[{$time}] {$clean}</span>\n";
    }
    @flush();
}

logMsg("Memulai eksekusi Pengingat Jadwal Mengajar Gelombang Bertahap...");
logMsg("Provider Active  : " . $waService->getActiveProvider());
logMsg("Gateway Status   : " . ($waService->isConnected() ? '✅ TERHUBUNG (Ready)' : '⚠️ TERPUTUS / PEMBATASAN WA'));

$options = [
    'dry_run' => $dryRun,
    'force' => $force,
    'target_phone' => $testPhone,
    'school' => $schoolParam,
    'delay_min' => $delayMin,
    'delay_max' => $delayMax,
    'logger' => function ($msg) {
        logMsg($msg);
    },
];

$result = $reportService->sendTeachingScheduleReminder($options);

echo "\n======================================================\n";
logMsg("🏁 HASIL AKHIR: " . ($result['message'] ?? 'Selesai'));
logMsg("📊 Terkirim: " . ($result['sent'] ?? 0) . " | Dilewati: " . ($result['skipped'] ?? 0) . " | Gagal: " . count($result['errors'] ?? []));
echo "</pre>";
echo "<a href='?secret=pembda99' class='btn-back'>← Kembali ke Control Panel</a>";
echo "</div></body></html>";
