<?php
/**
 * Diagnostik Kelengkapan Nomor HP Kepala Sekolah & Wali Kelas
 * Akses: https://perguruanpembda.com/check_phone_data.php?secret=pembda99
 *
 * Script ini mengecek apakah semua Kepsek dan Wali Kelas memiliki nomor HP
 * yang diperlukan untuk menerima rekapitulasi kehadiran harian via WhatsApp.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden - Secret key required (?secret=pembda99)');
}

// Bootstrap Laravel
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/pembdahub/vendor/autoload.php')) {
    require __DIR__ . '/pembdahub/vendor/autoload.php';
    $app = require_once __DIR__ . '/pembdahub/bootstrap/app.php';
} else {
    die('Autoload file not found. Checked: ' . __DIR__ . '/../vendor/autoload.php and ' . __DIR__ . '/pembdahub/vendor/autoload.php');
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\Setting;

$alertMessage = '';
if (isset($_GET['enable_attendance_wa']) && $_GET['enable_attendance_wa'] === 'yes') {
    Setting::setValue('wa_send_principal_attendance', true, 'boolean', 'features');
    Setting::setValue('wa_send_homeroom_attendance', true, 'boolean', 'features');
    $alertMessage = '<div style="background:#d1fae5; border:1px solid #10b981; color:#065f46; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">✅ Berhasil! Saklar otomatisasi WhatsApp untuk <u>Rekap Kepala Sekolah</u> dan <u>Rekap Wali Kelas</u> telah DI-AKTIFKAN di database.</div>';
}

if (isset($_GET['disable_attendance_wa']) && $_GET['disable_attendance_wa'] === 'yes') {
    Setting::setValue('wa_send_principal_attendance', false, 'boolean', 'features');
    Setting::setValue('wa_send_homeroom_attendance', false, 'boolean', 'features');
    $alertMessage = '<div style="background:#fee2e2; border:1px solid #ef4444; color:#991b1b; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:bold;">⚠️ Saklar otomatisasi WhatsApp untuk Rekap Kepala Sekolah dan Wali Kelas telah DI-NONAKTIFKAN.</div>';
}

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Diagnostik Nomor HP - PembdaHUB</title>';
echo '<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 1000px; margin: 30px auto; padding: 0 20px; background: #f8f9fa; color: #333; }
h1 { color: #1a56db; border-bottom: 3px solid #1a56db; padding-bottom: 10px; }
h2 { color: #047857; margin-top: 30px; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.12); border-radius: 8px; overflow: hidden; }
th { background: #1e40af; color: white; padding: 12px 15px; text-align: left; }
td { padding: 10px 15px; border-bottom: 1px solid #e5e7eb; }
tr:last-child td { border-bottom: none; }
.ok { color: #059669; font-weight: bold; }
.warn { color: #d97706; font-weight: bold; }
.error { color: #dc2626; font-weight: bold; }
.summary { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.12); margin: 20px 0; }
.badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.badge-ok { background: #d1fae5; color: #065f46; }
.badge-error { background: #fee2e2; color: #991b1b; }
.btn { display: inline-block; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; cursor: pointer; }
.btn-green { background: #059669; color: white; }
.btn-green:hover { background: #047857; }
.btn-red { background: #dc2626; color: white; }
.btn-red:hover { background: #b91c1c; }
</style></head><body>';

echo '<h1>📱 Diagnostik Nomor HP — Rekapitulasi Kehadiran Harian</h1>';
echo '<p><em>Dijalankan: ' . now()->format('d F Y H:i:s') . ' WIB</em></p>';
echo $alertMessage;

// ============================================================================
// 1. CEK KEPALA SEKOLAH
// ============================================================================
echo '<h2>🏫 1. Kepala Sekolah (Per Unit Sekolah)</h2>';
$schools = School::schoolsOnly()->with('principal.employee')->get();

echo '<table>';
echo '<tr><th>Unit Sekolah</th><th>Nama Kepsek</th><th>principal_id</th><th>Employee Phone</th><th>Teacher Phone</th><th>Status</th></tr>';

$kepsekOk = 0;
$kepsekFail = 0;

foreach ($schools as $school) {
    $principal = $school->principal;
    $principalName = $principal?->full_name ?? $school->principal_name ?? '-';
    $principalId = $school->principal_id ?? '-';

    $employeePhone = $principal?->employee?->phone ?? '-';
    $teacherPhone = isset($principal->attributes['phone']) ? $principal->attributes['phone'] : '-';

    // Resolve final phone (same logic as ExecutiveReportService)
    $resolvedPhone = $principal?->phone ?? null;

    // Fallback: cek user dengan role kepala_sekolah
    if (!$resolvedPhone) {
        $kepsekUser = User::where('role', 'kepala_sekolah')
            ->where('school_id', $school->id)
            ->first();
        if ($kepsekUser && $kepsekUser->teacher) {
            $resolvedPhone = $kepsekUser->teacher->phone;
        }
    }

    $status = $resolvedPhone
        ? '<span class="ok">✅ OK (' . $resolvedPhone . ')</span>'
        : '<span class="error">❌ TIDAK ADA NOMOR HP</span>';

    $resolvedPhone ? $kepsekOk++ : $kepsekFail++;

    echo "<tr><td>{$school->name}</td><td>{$principalName}</td><td>{$principalId}</td><td>{$employeePhone}</td><td>{$teacherPhone}</td><td>{$status}</td></tr>";
}
echo '</table>';

// ============================================================================
// 2. CEK WALI KELAS
// ============================================================================
echo '<h2>👩‍🏫 2. Wali Kelas (Tahun Pelajaran Aktif)</h2>';

$activeYear = AcademicYear::where('is_active', true)->first();
if (!$activeYear) {
    echo '<p class="error">⚠️ Tidak ada Tahun Pelajaran aktif!</p>';
} else {
    echo '<p>Tahun Pelajaran: <strong>' . $activeYear->name . '</strong></p>';

    try {
        $schoolIds = School::schoolsOnly()->pluck('id');
        $classrooms = Classroom::where('academic_year_id', $activeYear->id)
            ->whereIn('school_id', $schoolIds)
            ->with(['homeroomTeacher.employee', 'school'])
            ->orderBy('school_id')
            ->orderBy('class_name')
            ->get();
    } catch (\Throwable $e) {
        $classrooms = collect();
        echo '<p class="error">⚠️ Gagal memuat kelas: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }

    echo '<table>';
    echo '<tr><th>Unit</th><th>Kelas</th><th>Wali Kelas</th><th>Employee Phone</th><th>Teacher Phone</th><th>Status</th></tr>';

    $waliOk = 0;
    $waliFail = 0;
    $waliNoTeacher = 0;

    foreach ($classrooms as $class) {
        $teacher = $class->homeroomTeacher;
        $schoolName = $class->school?->short_name ?? $class->school?->name ?? '-';

        if (!$teacher) {
            $waliNoTeacher++;
            echo "<tr><td>{$schoolName}</td><td>{$class->name}</td><td colspan='3'><span class='warn'>⚠️ Belum ada Wali Kelas</span></td><td><span class='warn'>⚠️ N/A</span></td></tr>";
            continue;
        }

        $employeePhone = $teacher->employee?->phone ?? '-';
        $teacherPhone = isset($teacher->attributes['phone']) ? $teacher->attributes['phone'] : '-';
        $resolvedPhone = $teacher->phone ?? null;

        $status = $resolvedPhone
            ? '<span class="ok">✅ OK (' . $resolvedPhone . ')</span>'
            : '<span class="error">❌ TIDAK ADA</span>';

        $resolvedPhone ? $waliOk++ : $waliFail++;

        echo "<tr><td>{$schoolName}</td><td>{$class->name}</td><td>{$teacher->full_name}</td><td>{$employeePhone}</td><td>{$teacherPhone}</td><td>{$status}</td></tr>";
    }
    echo '</table>';
}

// ============================================================================
// 3. CEK KONFIGURASI WHATSAPP
// ============================================================================
echo '<h2>⚙️ 3. Konfigurasi WhatsApp</h2>';
echo '<table>';
echo '<tr><th>Setting</th><th>Nilai</th><th>Status</th></tr>';

$waEnabled = \App\Models\Setting::getValue('whatsapp_enabled', config('services.whatsapp.enabled', false));
$waProvider = \App\Models\Setting::getValue('wa_active_provider', config('services.whatsapp.provider', 'fonnte'));
$waToken = \App\Models\Setting::getValue('wa_fonnte_token', config('services.fonnte.api_token', ''));

$sendPrincipal = \App\Models\Setting::getValue('wa_send_principal_attendance', '0');
$sendHomeroom = \App\Models\Setting::getValue('wa_send_homeroom_attendance', '0');

echo '<tr><td>WhatsApp Enabled</td><td>' . ($waEnabled ? 'true' : 'false') . '</td><td>' . ($waEnabled ? '<span class="ok">✅ Aktif</span>' : '<span class="error">❌ Nonaktif</span>') . '</td></tr>';
echo '<tr><td>Provider Aktif</td><td>' . $waProvider . '</td><td><span class="ok">ℹ️</span></td></tr>';
echo '<tr><td>Fonnte Token</td><td>' . (strlen($waToken) > 5 ? substr($waToken, 0, 8) . '...' : ($waToken ?: '<kosong>')) . '</td><td>' . (strlen($waToken) > 5 ? '<span class="ok">✅ Terisi</span>' : '<span class="error">❌ Kosong</span>') . '</td></tr>';
echo '<tr><td>Saklar: Rekap Kepsek</td><td>' . ($sendPrincipal ? '1 (Aktif)' : '0 (Nonaktif)') . '</td><td>' . ($sendPrincipal ? '<span class="ok">✅ Aktif</span>' : '<span class="warn">⚠️ Nonaktif</span>') . '</td></tr>';
echo '<tr><td>Saklar: Rekap Wali Kelas</td><td>' . ($sendHomeroom ? '1 (Aktif)' : '0 (Nonaktif)') . '</td><td>' . ($sendHomeroom ? '<span class="ok">✅ Aktif</span>' : '<span class="warn">⚠️ Nonaktif</span>') . '</td></tr>';
echo '</table>';

echo '<div style="margin: 15px 0;">';
if (!$sendPrincipal || !$sendHomeroom) {
    echo '<a href="?secret=pembda99&enable_attendance_wa=yes" class="btn btn-green">⚡ Klik Di Sini untuk Mengaktifkan Saklar Rekap Kepsek & Wali Kelas</a> ';
} else {
    echo '<span class="ok" style="font-size:15px; font-weight:bold;">🎉 Seluruh saklar rekap kehadiran WhatsApp sudah AKTIF!</span> ';
    echo '<a href="?secret=pembda99&disable_attendance_wa=yes" class="btn btn-red" style="font-size:12px; padding:6px 12px; margin-left:15px;" onclick="return confirm(\'Yakin ingin menonaktifkan saklar?\')">Nonaktifkan</a>';
}
echo '</div>';

// ============================================================================
// RINGKASAN
// ============================================================================
echo '<div class="summary">';
echo '<h2>📊 Ringkasan</h2>';
echo '<table>';
echo '<tr><th>Kategori</th><th>Siap</th><th>Belum</th><th>Status</th></tr>';
echo '<tr><td>Kepala Sekolah</td><td>' . $kepsekOk . '</td><td>' . $kepsekFail . '</td><td>' . ($kepsekFail === 0 ? '<span class="badge badge-ok">READY</span>' : '<span class="badge badge-error">PERLU INPUT DATA</span>') . '</td></tr>';

if ($activeYear) {
    echo '<tr><td>Wali Kelas (punya HP)</td><td>' . $waliOk . '</td><td>' . $waliFail . '</td><td>' . ($waliFail === 0 ? '<span class="badge badge-ok">READY</span>' : '<span class="badge badge-error">PERLU INPUT DATA</span>') . '</td></tr>';
    echo '<tr><td>Kelas tanpa Wali Kelas</td><td colspan="2">' . $waliNoTeacher . ' kelas</td><td>' . ($waliNoTeacher === 0 ? '<span class="badge badge-ok">READY</span>' : '<span class="badge badge-error">PERLU ASSIGN</span>') . '</td></tr>';
}
echo '</table>';
echo '</div>';

// ============================================================================
// 4. PANDUAN CRON JOB HOSTINGER (PHP 8.2 / 8.3)
// ============================================================================
echo '<h2>⏱️ 4. Panduan Cron Job Hostinger</h2>';
$phpCandidates = [
    '/usr/bin/php8.3',
    '/usr/bin/php8.2',
    '/opt/alt/php83/usr/bin/php',
    '/opt/alt/php82/usr/bin/php',
    '/usr/bin/php',
];
$bestPhp = '/usr/bin/php8.2';
foreach ($phpCandidates as $p) {
    if (file_exists($p)) {
        $bestPhp = $p;
        break;
    }
}

$cronCmd = "{$bestPhp} /home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/artisan schedule:run >> /dev/null 2>&1";

echo '<div style="background:#1e293b; color:#f1f5f9; padding:15px 20px; border-radius:8px; margin:15px 0;">';
echo '<p style="margin:0 0 10px 0; color:#38bdf8; font-weight:bold;">📋 Salin perintah Cron Job ini ke hPanel Hostinger:</p>';
echo '<code style="display:block; background:#0f172a; padding:10px; border-radius:6px; color:#4ade80; font-size:13px; word-break:break-all;">' . htmlspecialchars($cronCmd) . '</code>';
echo '<p style="margin:10px 0 0 0; font-size:12px; color:#94a3b8;">* Hostinger menggunakan PHP 8.1 pada alias default <code>/usr/bin/php</code>. Gunakan <code>/usr/bin/php8.2</code> atau <code>/usr/bin/php8.3</code> agar sesuai dengan syarat Laravel 11.</p>';
echo '</div>';

echo '<p style="margin-top:30px; color:#6b7280; font-size:13px;">Script ini hanya membaca data (read-only). Untuk mengisi nomor HP, gunakan menu Admin > Data Guru & Pegawai di PembdaHUB.</p>';
echo '</body></html>';
