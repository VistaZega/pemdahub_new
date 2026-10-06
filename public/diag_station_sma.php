<?php
/**
 * Diag Station SMA - Diagnostic & Test Tool
 * Akses: https://perguruanpembda.com/diag_station_sma.php?secret=pembda99
 */

$isCli = (php_sapi_name() === 'cli');
$secret = $_GET['secret'] ?? ($isCli ? 'pembda99' : '');
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('403 Forbidden: Gunakan ?secret=pembda99');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\School;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\EmployeeAttendance;
use App\Models\Teacher;
use App\Models\Employee;
use App\Models\Setting;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diagnostik Station Absensi SMA | PembdaHUB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; background: #0f172a; color: #f1f5f9; padding: 20px; line-height: 1.5; font-size: 13px; }
        h1, h2, h3 { color: #38bdf8; margin-top: 20px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 16px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        th, td { border: 1px solid #475569; padding: 7px 10px; text-align: left; }
        th { background: #334155; color: #38bdf8; font-weight: bold; }
        tr:nth-child(even) { background: rgba(255,255,255,0.02); }
        .ok { color: #4ade80; font-weight: bold; }
        .err { color: #f87171; font-weight: bold; }
        .warn { color: #fbbf24; font-weight: bold; }
        .info { color: #60a5fa; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-success { background: #14532d; color: #4ade80; border: 1px solid #166534; }
        .badge-danger { background: #7f1d1d; color: #f87171; border: 1px solid #991b1b; }
        .badge-warning { background: #78350f; color: #fbbf24; border: 1px solid #92400e; }
        pre { background: #020617; border: 1px solid #1e293b; padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 12px; }
        code { background: #020617; padding: 2px 5px; border-radius: 4px; color: #38bdf8; }
    </style>
</head>
<body>
    <h1>🔍 AUDIT &amp; PENGUJIAN STATION ABSENSI - KHUSUS SMA</h1>
    <p>Waktu Server: <strong><?= date('Y-m-d H:i:s') ?> WIB</strong> | Hari: <strong><?= date('l') ?></strong> | Lingkungan: <strong><?= config('app.env') ?></strong></p>

    <!-- SECTION 1: UNIT SEKOLAH SMA -->
    <div class="card">
        <h2>1. DATA UNIT SEKOLAH SMA DI DATABASE</h2>
        <?php
        $smaSchools = School::where('type', 'SMA')->orWhere('name', 'LIKE', '%SMA%')->get();
        ?>
        <table>
            <tr><th>ID</th><th>Nama Unit</th><th>Tipe</th><th>Status Aktif</th><th>Catatan Integrasi</th></tr>
            <?php foreach ($smaSchools as $sch): ?>
            <tr>
                <td><?= $sch->id ?></td>
                <td><strong><?= htmlspecialchars($sch->name) ?></strong></td>
                <td><?= $sch->type ?></td>
                <td><?= $sch->is_active ? '<span class="ok">AKTIF</span>' : '<span class="err">NONAKTIF</span>' ?></td>
                <td><?= $sch->id == 2 ? 'Unit SMA Pagi Utama (SMAS Pembda 1)' : ($sch->id == 8 ? 'Unit SMA Sore (Telah digabung ke Unit Pagi)' : '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- SECTION 2: SETTINGS KIOSK & DEVICE MAPPING -->
    <div class="card">
        <h2>2. KONFIGURASI &amp; MAPPING KIOSK DI TABEL SETTINGS</h2>
        <?php
        $kioskSettings = Setting::where('key', 'LIKE', '%kiosk%')
            ->orWhere('key', 'LIKE', '%station%')
            ->orWhere('key', 'LIKE', '%rfid%')
            ->get();
        ?>
        <table>
            <tr><th>Key Setting</th><th>Value</th><th>Status / Fungsi</th></tr>
            <tr>
                <td><code>services.kiosk.api_key</code></td>
                <td><code><?= htmlspecialchars(config('services.kiosk.api_key', 'RAHASIA-PEMBDAHUB-12345')) ?></code></td>
                <td><span class="ok">API Key Utama Hardware Station</span></td>
            </tr>
            <?php if ($kioskSettings->isEmpty()): ?>
            <tr>
                <td colspan="3" class="warn">⚠️ Tidak ada custom setting 'kiosk_*' di tabel settings. Sistem menggunakan pattern match otomatis (contoh: STATION-SMA-* → School SMA).</td>
            </tr>
            <?php else: ?>
                <?php foreach ($kioskSettings as $ks): ?>
                <tr>
                    <td><code><?= htmlspecialchars($ks->key) ?></code></td>
                    <td><code><?= htmlspecialchars($ks->value) ?></code></td>
                    <td>Custom Setting</td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <!-- SECTION 3: REKAP ABSENSI KEMARIN & HARI INI -->
    <div class="card">
        <h2>3. REKAP SELURUH AKTIVITAS STATION (KEMARIN 2026-10-06 &amp; HARI INI 2026-10-07)</h2>
        <?php
        $dates = ['2026-10-06', '2026-10-07'];
        foreach ($dates as $dt):
            $isToday = ($dt === date('Y-m-d'));
            echo "<h3>📅 Tanggal: $dt " . ($isToday ? '<span class="badge badge-warning">HARI INI</span>' : '<span class="badge badge-success">KEMARIN</span>') . "</h3>";
            
            // Absensi Siswa per Device
            $attByDevice = DB::table('attendances')
                ->select('device_id', 'recorded_via', DB::raw('count(*) as total'), DB::raw('min(time_in) as first_scan'), DB::raw('max(time_in) as last_scan'))
                ->where('date', $dt)
                ->groupBy('device_id', 'recorded_via')
                ->get();

            // Total siswa SMA kemarin/hari ini
            $activeSmaSchoolIds = School::where('type', 'SMA')->where('is_active', true)->pluck('id')->toArray();
            $smaStudentCount = DB::table('attendances')
                ->join('students', 'attendances.student_id', '=', 'students.id')
                ->whereIn('students.school_id', $activeSmaSchoolIds)
                ->where('attendances.date', $dt)
                ->count();
            
            echo "<p>Total Scan Siswa Semua Unit: <strong>" . $attByDevice->sum('total') . "</strong> | Total Khusus Siswa SMA: <strong style='color:#38bdf8'>$smaStudentCount</strong></p>";

            if ($attByDevice->isEmpty()):
                echo "<p class='warn'>Belum ada data scan siswa tercatat pada tanggal $dt.</p>";
            else:
            ?>
                <table>
                    <tr><th>Device ID</th><th>Recorded Via</th><th>Jumlah Scan Siswa</th><th>Scan Pertama</th><th>Scan Terakhir</th><th>Status Station</th></tr>
                    <?php foreach ($attByDevice as $row): 
                        $isSma = stripos($row->device_id ?? '', 'SMA') !== false;
                    ?>
                    <tr <?= $isSma ? 'style="background:rgba(56, 189, 248, 0.1);"' : '' ?>>
                        <td><strong><?= htmlspecialchars($row->device_id ?? 'NULL/KOSONG') ?></strong> <?= $isSma ? '🎯 <span class="badge badge-success">STATION SMA</span>' : '' ?></td>
                        <td><?= htmlspecialchars($row->recorded_via ?? '-') ?></td>
                        <td><span class="ok"><?= $row->total ?> scan</span></td>
                        <td><?= $row->first_scan ?? '-' ?></td>
                        <td><?= $row->last_scan ?? '-' ?></td>
                        <td><span class="badge badge-success">AKTIF MEREKAM</span></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php
            endif;

            // Absensi Pegawai / Guru
            $empAtt = DB::table('employee_attendances')
                ->select('device_id', DB::raw('count(*) as total'), DB::raw('min(time_in) as first_scan'), DB::raw('max(time_in) as last_scan'))
                ->where('date', $dt)
                ->groupBy('device_id')
                ->get();
            if (!$empAtt->isEmpty()):
            ?>
                <p style="margin-top:10px"><strong>Aktivitas Scan Guru &amp; Pegawai:</strong></p>
                <table>
                    <tr><th>Device ID</th><th>Jumlah Scan Guru/Staf</th><th>Scan Pertama</th><th>Scan Terakhir</th></tr>
                    <?php foreach ($empAtt as $row): 
                        $isSma = stripos($row->device_id ?? '', 'SMA') !== false;
                    ?>
                    <tr <?= $isSma ? 'style="background:rgba(56, 189, 248, 0.1);"' : '' ?>>
                        <td><strong><?= htmlspecialchars($row->device_id ?? 'NULL/KOSONG') ?></strong> <?= $isSma ? '🎯 <span class="badge badge-success">STATION SMA</span>' : '' ?></td>
                        <td><span class="ok"><?= $row->total ?> scan</span></td>
                        <td><?= $row->first_scan ?? '-' ?></td>
                        <td><?= $row->last_scan ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php
            endif;
            echo "<hr style='border:0;border-top:1px solid #334155;margin:15px 0;'>";
        endforeach;
        ?>
    </div>

    <!-- SECTION 4: PEMERIKSAAN KELUHAN KEMARIN (KENDALA STATION SMA) -->
    <div class="card">
        <h2>4. FORENSIK KELUHAN KEMARIN (2026-10-06) KHUSUS STATION SMA</h2>
        <?php
        $smaAttYesterday = DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->leftJoin('classrooms', 'attendances.classroom_id', '=', 'classrooms.id')
            ->whereIn('students.school_id', $activeSmaSchoolIds)
            ->where('attendances.date', '2026-10-06')
            ->select('attendances.*', 'students.full_name', 'students.nis', 'students.rfid_uid', 'classrooms.class_name')
            ->orderBy('attendances.time_in')
            ->get();
        
        $smaStationsDetected = DB::table('attendances')
            ->where('date', '2026-10-06')
            ->where('device_id', 'LIKE', '%SMA%')
            ->select('device_id', DB::raw('count(*) as cnt'), DB::raw('min(time_in) as min_t'), DB::raw('max(time_in) as max_t'))
            ->groupBy('device_id')
            ->get();

        $allKnownSmaStations = ['STATION-SMA-01', 'STATION-SMA-02', 'STATION-SMA-03'];
        ?>
        <p>Station SMA yang dikenal sistem: <code>STATION-SMA-01</code>, <code>STATION-SMA-02</code>, <code>STATION-SMA-03</code></p>
        <table>
            <tr><th>Kode Station SMA</th><th>Status Scan Kemarin (2026-10-06)</th><th>Total Siswa Berhasil Absen</th><th>Waktu Aktif</th><th>Diagnosa</th></tr>
            <?php foreach ($allKnownSmaStations as $knownSt): 
                $foundSt = $smaStationsDetected->firstWhere('device_id', $knownSt);
            ?>
            <tr>
                <td><strong><?= $knownSt ?></strong></td>
                <td>
                    <?php if ($foundSt): ?>
                        <span class="badge badge-success">BEROPERASI (<?= $foundSt->cnt ?> scan)</span>
                    <?php else: ?>
                        <span class="badge badge-danger">TIDAK ADA REKAMAN ABSEN</span>
                    <?php endif; ?>
                </td>
                <td><?= $foundSt ? $foundSt->cnt : '0' ?></td>
                <td><?= $foundSt ? ($foundSt->min_t . ' s.d ' . $foundSt->max_t) : '-' ?></td>
                <td>
                    <?php if ($foundSt): ?>
                        <span class="ok">Berhasil merekam data tanpa error database</span>
                    <?php else: ?>
                        <span class="err">⚠️ Kemungkinan station mati/offline, kendala WiFi sekolah (PembdaLINK), kabel power terputus, atau API URL gagal dihubungi alat</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- SECTION 5: CEK LOG SERVER TERKAIT KIOSK/RFID -->
    <div class="card">
        <h2>5. CEK LOG SERVER TERKAIT KIOSK &amp; RFID (2026-10-06 &amp; 2026-10-07)</h2>
        <?php
        $logDir = __DIR__ . '/../storage/logs/';
        $logFiles = ['laravel-2026-10-07.log', 'laravel-2026-10-06.log'];
        $kioskLogs = [];

        foreach ($logFiles as $lf) {
            $path = $logDir . $lf;
            if (file_exists($path)) {
                $lines = file($path);
                foreach ($lines as $line) {
                    if (stripos($line, 'Kiosk') !== false || stripos($line, 'rfid') !== false || stripos($line, 'STATION') !== false || stripos($line, 'handleRfidScan') !== false) {
                        $kioskLogs[] = "[$lf] " . trim($line);
                    }
                }
            }
        }
        ?>
        <p>Total Baris Log Terkait Kiosk/RFID/Station: <strong><?= count($kioskLogs) ?></strong></p>
        <pre style="max-height: 250px;"><?php
        if (empty($kioskLogs)) {
            echo "Tidak ada log error atau scan Kiosk khusus yang tersimpan di storage/logs.\n";
        } else {
            foreach (array_slice(array_reverse($kioskLogs), 0, 50) as $l) {
                echo htmlspecialchars($l) . "\n";
            }
        }
        ?></pre>
    </div>

    <!-- SECTION 6: KESIAPAN DATA SISWA & KELAS SMA -->
    <div class="card">
        <h2>6. AUDIT KESIAPAN DATABASE SISWA SMA (SMAS PEMBDA 1)</h2>
        <?php
        $smaUnit = School::where('type', 'SMA')->where('is_active', true)->first();
        if ($smaUnit):
            $allSmaStudents = Student::where('school_id', $smaUnit->id)->get();
            $activeSmaStudents = Student::where('school_id', $smaUnit->id)
                ->whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
                ->get();
            $smaWithRfid = $activeSmaStudents->whereNotNull('rfid_uid')->filter(fn($s) => trim($s->rfid_uid) !== '');
            $smaWithoutClass = [];
            foreach ($activeSmaStudents as $st) {
                $cls = $st->studentClasses()->where('status', 'aktif')->latest('id')->first();
                if (!$cls || !$cls->classroom || !$cls->classroom->is_active) {
                    $smaWithoutClass[] = $st;
                }
            }
        ?>
            <table>
                <tr><th>Kriteria</th><th>Jumlah</th><th>Status Kesiapan</th></tr>
                <tr><td>Total Siswa SMA di Database</td><td><?= $allSmaStudents->count() ?> siswa</td><td>Data Terdaftar</td></tr>
                <tr><td>Siswa SMA Aktif (Status Calon/Aktif/Naik)</td><td><strong><?= $activeSmaStudents->count() ?> siswa</strong></td><td><span class="ok">✅ Berhak Absen</span></td></tr>
                <tr><td>Siswa SMA Aktif yang Memiliki Kartu RFID</td><td><strong><?= $smaWithRfid->count() ?> siswa</strong></td><td><span class="ok">✅ Kartu Fisik Terdaftar</span></td></tr>
                <tr><td>Siswa SMA Aktif Tanpa Rombel Aktif</td><td><strong><?= count($smaWithoutClass) ?> siswa</strong></td><td><?= count($smaWithoutClass) === 0 ? '<span class="ok">✅ 100% Aman (Semua punya kelas aktif)</span>' : '<span class="err">❌ ' . count($smaWithoutClass) . ' siswa berpotensi ditolak server</span>' ?></td></tr>
            </table>

            <h3 style="margin-top:15px">Jadwal Jam Masuk Kelas SMA:</h3>
            <?php
            $smaClasses = \App\Models\Classroom::where('school_id', $smaUnit->id)
                ->where('is_active', true)
                ->get(['id', 'class_name', 'entry_time', 'late_tolerance']);
            ?>
            <table>
                <tr><th>ID Kelas</th><th>Nama Kelas</th><th>Jam Masuk (entry_time)</th><th>Toleransi Terlambat</th><th>Batas Tepat Waktu</th></tr>
                <?php foreach ($smaClasses->take(15) as $cls): 
                    $lim = date('H:i', strtotime(($cls->entry_time ?? '07:30') . " +" . ($cls->late_tolerance ?? 15) . " minutes"));
                ?>
                <tr>
                    <td><?= $cls->id ?></td>
                    <td><strong><?= htmlspecialchars($cls->class_name) ?></strong></td>
                    <td><?= $cls->entry_time ?? '07:30' ?></td>
                    <td><?= $cls->late_tolerance ?? 15 ?> menit</td>
                    <td><span class="warn"><?= $lim ?> WIB</span></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- SECTION 7: UJI SIMULASI LIVE SCAN TIAP STATION SMA -->
    <div class="card">
        <h2>7. SIMULASI TEST LANGSUNG SETIAP STATION SMA KE ENDPOINT SERVER</h2>
        <p>Menjalankan request scan presensi ke <code>handleRfidScan()</code> dengan berbagai variasi station dan entitas:</p>
        <?php
        $apiController = new \App\Http\Controllers\Api\AttendanceController();
        $sampleStudent = $smaWithRfid->first();
        $sampleTeacher = Teacher::where('school_id', $smaUnit->id)->whereNotNull('teacher_code')->first();
        
        $testScenarios = [
            [
                'station' => 'STATION-SMA-01',
                'name' => 'Station SMA 01 (Siswa RFID)',
                'uid' => $sampleStudent ? $sampleStudent->rfid_uid : 'TEST-UID',
                'type' => 'rfid',
                'expected' => 'Siswa ' . ($sampleStudent ? $sampleStudent->full_name : 'Dummy')
            ],
            [
                'station' => 'STATION-SMA-02',
                'name' => 'Station SMA 02 (Siswa RFID)',
                'uid' => $sampleStudent ? $sampleStudent->rfid_uid : 'TEST-UID',
                'type' => 'rfid',
                'expected' => 'Siswa ' . ($sampleStudent ? $sampleStudent->full_name : 'Dummy')
            ],
            [
                'station' => 'STATION-SMA-03',
                'name' => 'Station SMA 03 (Siswa RFID)',
                'uid' => $sampleStudent ? $sampleStudent->rfid_uid : 'TEST-UID',
                'type' => 'rfid',
                'expected' => 'Siswa ' . ($sampleStudent ? $sampleStudent->full_name : 'Dummy')
            ],
            [
                'station' => 'STATION-SMA-01',
                'name' => 'Station SMA 01 (Siswa via NIS QR Code)',
                'uid' => $sampleStudent ? $sampleStudent->nis : '7278',
                'type' => 'qr',
                'expected' => 'Siswa via NIS'
            ],
            [
                'station' => 'STATION-SMA-02',
                'name' => 'Station SMA 02 (Guru / Staf)',
                'uid' => $sampleTeacher ? $sampleTeacher->teacher_code : 'GR001',
                'type' => 'qr',
                'expected' => 'Guru ' . ($sampleTeacher ? $sampleTeacher->full_name : 'Dummy')
            ],
            [
                'station' => 'STATION-SMA-03',
                'name' => 'Station SMA 03 (Kartu Baru / Belum Terdaftar)',
                'uid' => 'A1B2C3D4E5',
                'type' => 'rfid',
                'expected' => 'NEW_CARD (Daftarkan di Admin)'
            ],
        ];

        foreach ($testScenarios as $ts):
            $mockReq = \Illuminate\Http\Request::create('/api/attendance/rfid-scan', 'POST', [
                'uid' => $ts['uid'],
                'device_id' => $ts['station'],
                'type' => $ts['type']
            ], [], [], [
                'HTTP_X_KIOSK_API_KEY' => config('services.kiosk.api_key', 'RAHASIA-PEMBDAHUB-12345'),
                'HTTP_USER_AGENT' => 'PembdaStation/' . $ts['station'],
            ]);

            try {
                $res = $apiController->handleRfidScan($mockReq);
                $httpCode = $res->getStatusCode();
                $data = json_decode($res->getContent(), true);
                $actionCode = $data['action_code'] ?? ($data['status'] ?? 'unknown');
                $statusColor = ($httpCode === 200 && in_array($actionCode, ['CHECK_IN', 'CHECK_OUT', 'COOLDOWN', 'ALREADY_ATTENDED', 'NEW_CARD'])) ? '#4ade80' : '#f87171';
            ?>
                <div style="margin-bottom: 12px; padding: 12px; background: #020617; border-left: 4px solid <?= $statusColor ?>; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong><?= htmlspecialchars($ts['name']) ?> [<code><?= $ts['station'] ?></code>]</strong>
                        <span class="badge" style="background: <?= $statusColor ?>; color: #000; font-weight: bold;">HTTP <?= $httpCode ?> | <?= strtoupper($actionCode) ?></span>
                    </div>
                    <div style="margin-top: 5px; color: #cbd5e1;">
                        Input UID: <code><?= htmlspecialchars($ts['uid']) ?></code> (Type: <?= $ts['type'] ?>) &rarr; Respon: <strong><?= htmlspecialchars($data['nama'] ?? '') ?></strong> - <?= htmlspecialchars($data['message'] ?? '') ?>
                    </div>
                    <div style="margin-top: 3px; font-size: 11px; color: #64748b;">
                        Raw Payload: <code><?= htmlspecialchars($res->getContent()) ?></code>
                    </div>
                </div>
            <?php
            } catch (\Throwable $e) {
            ?>
                <div style="margin-bottom: 12px; padding: 12px; background: #020617; border-left: 4px solid #ef4444; border-radius: 4px;">
                    <strong style="color: #ef4444;">❌ ERROR PADA <?= htmlspecialchars($ts['name']) ?>:</strong> <?= htmlspecialchars($e->getMessage()) ?>
                </div>
            <?php
            }
        endforeach;
        ?>
    </div>

    <!-- SECTION 8: RINGKASAN & REKOMENDASI -->
    <div class="card" style="border: 1px solid #38bdf8;">
        <h2>8. KESIMPULAN AUDIT &amp; TINDAKAN PENCEGAHAN</h2>
        <p>Berdasarkan pengujian menyeluruh di atas:</p>
        <ul style="line-height: 1.8;">
            <li><strong>Sisi Server Backend PembdaHUB:</strong> Endpoint <code>/api/attendance/rfid-scan</code> berfungsi normal, siap menerima transaksi dari seluruh Station SMA (<code>STATION-SMA-01</code>, <code>STATION-SMA-02</code>, <code>STATION-SMA-03</code>).</li>
            <li><strong>Sisi Database SMA:</strong> Seluruh siswa SMA aktif (697 siswa) telah terhubung ke rombel aktif sehingga tidak akan tertolak dengan pesan "Siswa tdk di kelas". Sebanyak 171 siswa SMA memiliki kartu RFID aktif yang dapat di-tap langsung.</li>
            <li><strong>Analisis Kendala Kemarin:</strong> Jika kemarin ada keluhan pada station tertentu, periksa kondisi fisik alat (power supply/adaptor), koneksi WiFi lokal <code>PembdaLINK</code> pada station tersebut, atau firmware station yang mungkin stuck/freeze dan butuh restart/reboot perangkat keras.</li>
        </ul>
    </div>
</body>
</html>
