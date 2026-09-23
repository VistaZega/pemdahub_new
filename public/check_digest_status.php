<?php
/**
 * Check Digest Status for 2026-09-23
 * Akses: https://perguruanpembda.com/check_digest_status.php?secret=pembda99
 */
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    die("Akses ditolak");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Cache;

header('Content-Type: text/plain; charset=utf-8');

echo "=== STATUS REKAPITULASI KEHADIRAN (2026-09-23) ===\n\n";

$waService = app(\App\Services\WhatsAppService::class);

// 1. Pengaturan WhatsApp & Saklar
echo "--- 1. PENGATURAN WHATSAPP & SAKLAR REKAP ---\n";
echo "WA Provider Aktif    : " . $waService->getActiveProvider() . " (" . $waService->getProviderLabel() . ")\n";
echo "wa_active_provider   : " . Setting::getValue('wa_active_provider', '-') . "\n";
echo "wa_fonnte_token      : " . substr(Setting::getValue('wa_fonnte_token', ''), 0, 6) . "..." . "\n";
echo "wa_digest_enabled    : " . (Setting::getValue('wa_digest_enabled', true) ? 'AKTIF (true)' : 'NONAKTIF (false)') . "\n";
echo "wa_send_principal    : " . (Setting::getValue('wa_send_principal_attendance', true) ? 'AKTIF (true)' : 'NONAKTIF (false)') . "\n";
echo "wa_send_homeroom     : " . (Setting::getValue('wa_send_homeroom_attendance', true) ? 'AKTIF (true)' : 'NONAKTIF (false)') . "\n";

// Test connection
echo "WA Service Enabled?  : " . ($waService->isEnabled() ? 'YES' : 'NO') . "\n";
echo "WA Service Connected?: " . ($waService->isConnected() ? 'YES' : 'NO') . "\n";

// Device / Account info
$accInfo = $waService->getAccountInfo();
echo "Fonnte Account Info  : " . json_encode($accInfo) . "\n";

// 2. Status Cache Idempotency (Apakah sudah terkirim hari ini?)
$dateToday = date('Y-m-d');
echo "\n--- 2. IDEMPOTENCY LOCK (Cache wa_digest_sent_*) UNTUK TANGGAL {$dateToday} ---\n";

echo "A. KEPALA SEKOLAH:\n";
$schools = School::schoolsOnly()->get();
foreach ($schools as $sch) {
    $key = "wa_digest_sent_principal_{$sch->id}_{$dateToday}";
    $hasKey = Cache::has($key);
    $val = Cache::get($key);
    echo "  - School ID {$sch->id} ({$sch->name}): " . ($hasKey ? "✅ SUDAH TERKIRIM (Tercatat: {$val})" : "❌ BELUM TERKIRIM") . "\n";
}

echo "\nB. WALI KELAS:\n";
$activeYear = AcademicYear::where('is_active', true)->first();
$classrooms = Classroom::where('is_active', true)
    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
    ->whereNotNull('homeroom_teacher_id')
    ->with(['school', 'homeroomTeacher'])
    ->get();

$homeroomSentCount = 0;
$homeroomPendingCount = 0;
foreach ($classrooms as $cls) {
    $key = "wa_digest_sent_homeroom_{$cls->id}_{$dateToday}";
    $hasKey = Cache::has($key);
    $val = Cache::get($key);
    if ($hasKey) {
        $homeroomSentCount++;
        echo "  - Kelas ID {$cls->id} ({$cls->class_name} - {$cls->school?->name}): ✅ SUDAH TERKIRIM (Tercatat: {$val})\n";
    } else {
        $homeroomPendingCount++;
    }
}
echo "Total Wali Kelas Terkirim: {$homeroomSentCount} | Belum Terkirim: {$homeroomPendingCount}\n";

// 3. Log WhatsApp Hari Ini (storage/logs/whatsapp.log)
echo "\n--- 3. LOG WHATSAPP TERAKHIR (storage/logs/whatsapp.log) ---\n";
$waLogFile = storage_path('logs/whatsapp.log');
if (file_exists($waLogFile)) {
    $size = filesize($waLogFile);
    $readBytes = min($size, 100 * 1024);
    $fp = fopen($waLogFile, 'r');
    fseek($fp, $size - $readBytes);
    $content = fread($fp, $readBytes);
    fclose($fp);

    $lines = explode("\n", $content);
    $todayLines = [];
    foreach ($lines as $line) {
        if (str_contains($line, $dateToday)) {
            $todayLines[] = $line;
        }
    }
    echo "Jumlah baris log WhatsApp hari ini ({$dateToday}): " . count($todayLines) . "\n";
    foreach (array_slice($todayLines, -30) as $tl) {
        echo substr($tl, 0, 180) . "\n";
    }
} else {
    echo "File whatsapp.log tidak ditemukan.\n";
}

// 4. Cek apakah ada Scheduled Task yang dijalankan pada jam 08:00
echo "\n--- 4. LOG LARAVEL PADA JAM 08:00 HARI INI ---\n";
$laravelLogFile = storage_path("logs/laravel-{$dateToday}.log");
if (file_exists($laravelLogFile)) {
    $size = filesize($laravelLogFile);
    $readBytes = min($size, 300 * 1024);
    $fp = fopen($laravelLogFile, 'r');
    fseek($fp, $size - $readBytes);
    $content = fread($fp, $readBytes);
    fclose($fp);

    $lines = explode("\n", $content);
    $eightAmLines = [];
    foreach ($lines as $line) {
        if (preg_match('/\[2026-09-23 08:/', $line) || preg_match('/\[2026-09-23 01:/', $line)) {
            $eightAmLines[] = $line;
        }
    }
    echo "Baris log sekitar jam 08:00 WIB: " . count($eightAmLines) . "\n";
    foreach (array_slice($eightAmLines, -20) as $eal) {
        echo substr($eal, 0, 180) . "\n";
    }
}
