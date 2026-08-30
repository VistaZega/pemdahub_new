<?php
/**
 * Diagnostik Kelengkapan Nomor HP Kepala Sekolah & Wali Kelas
 * Akses: https://perguruanpembda.com/check_phone_data.php?secret=pembda99
 *
 * Script ini mengecek apakah semua Kepsek dan Wali Kelas memiliki nomor HP
 * yang diperlukan untuk menerima rekapitulasi kehadiran harian via WhatsApp.
 */

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

// Bootstrap Laravel
require __DIR__ . '/../pembdahub/vendor/autoload.php';
$app = require_once __DIR__ . '/../pembdahub/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\User;

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
</style></head><body>';

echo '<h1>📱 Diagnostik Nomor HP — Rekapitulasi Kehadiran Harian</h1>';
echo '<p><em>Dijalankan: ' . now()->format('d F Y H:i:s') . ' WIB</em></p>';

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

    $schoolIds = School::schoolsOnly()->pluck('id');
    $classrooms = Classroom::where('academic_year_id', $activeYear->id)
        ->whereIn('school_id', $schoolIds)
        ->with(['homeroomTeacher.employee', 'school'])
        ->orderBy('school_id')
        ->orderBy('name')
        ->get();

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
echo '<tr><td>Saklar: Rekap Kepsek</td><td>' . $sendPrincipal . '</td><td>' . ($sendPrincipal ? '<span class="ok">✅ Aktif</span>' : '<span class="warn">⚠️ Nonaktif (perlu diaktifkan di Admin > Settings > WhatsApp)</span>') . '</td></tr>';
echo '<tr><td>Saklar: Rekap Wali Kelas</td><td>' . $sendHomeroom . '</td><td>' . ($sendHomeroom ? '<span class="ok">✅ Aktif</span>' : '<span class="warn">⚠️ Nonaktif (perlu diaktifkan di Admin > Settings > WhatsApp)</span>') . '</td></tr>';
echo '</table>';

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

echo '<p style="margin-top:30px; color:#6b7280; font-size:13px;">Script ini hanya membaca data (read-only). Untuk mengisi nomor HP, gunakan menu Admin > Data Guru & Pegawai di PembdaHUB.</p>';
echo '</body></html>';
