<?php
/**
 * Check Detail Pengiriman Rekap Kasek
 * Akses: https://perguruanpembda.com/check_kasek_detail.php?secret=pembda99
 */
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') die("Akses ditolak");

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Cache;

header('Content-Type: text/plain; charset=utf-8');

echo "=== RINCIAN PENGIRIMAN REKAP KEHADIRAN KEPALA SEKOLAH (23 SEPTEMBER 2026) ===\n\n";

$schools = School::schoolsOnly()->with('principal')->get();
$dateToday = date('Y-m-d');

foreach ($schools as $school) {
    echo "Unit Sekolah : {$school->name} (ID: {$school->id})\n";

    // 1. Data Kepala Sekolah
    $principal = $school->principal;
    $phone = $principal?->phone ?? null;
    $kepsekUser = null;

    if (!$phone) {
        $kepsekUser = User::where('role', 'kepala_sekolah')
            ->where('school_id', $school->id)
            ->first();
        if ($kepsekUser && $kepsekUser->teacher) {
            $phone = $kepsekUser->teacher->phone;
        }
    }

    $principalName = $principal?->full_name ?? $school->principal_name ?? ($kepsekUser?->name ?? 'Kepala Sekolah');

    echo "Nama Kasek   : {$principalName}\n";
    echo "No. WhatsApp : " . ($phone ? $phone : "TIDAK DITEMUKAN") . "\n";

    // 2. Status Cache / Idempotency
    $key = "wa_digest_sent_principal_{$school->id}_{$dateToday}";
    $hasKey = Cache::has($key);
    $sentTimestamp = Cache::get($key);

    echo "Status Kirim : " . ($hasKey ? "✅ SUDAH DITERIMA / TERKIRIM" : "❌ BELUM TERKIRIM") . "\n";
    echo "Waktu Kirim  : " . ($sentTimestamp ?: "-") . "\n";
    echo "--------------------------------------------------------\n\n";
}
