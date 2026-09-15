<?php
/**
 * PembdaHUB — Web Storage Integrity Auditor & Self-Healing Tool
 * 
 * Script ini digunakan untuk memverifikasi keselarasan antara data di database
 * dan berkas fisik yang ada di folder storage server (termasuk foto profil, dokumen, LMS, CBT, PKL).
 * 
 * Akses via Browser:
 *   http://50.35.89.10/storage-audit.php?secret=pembda2026storage
 *   atau https://perguruanpembda.com/storage-audit.php?secret=pembda2026storage
 */

// 1. Keamanan & Otentikasi
$VALID_SECRETS = ['pembda2026storage', 'pembda2026export', 'pembda99'];
$secret = $_REQUEST['secret'] ?? '';

if (!in_array($secret, $VALID_SECRETS, true)) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>403 Forbidden</title><style>body{background:#0f172a;color:#f87171;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}</style></head><body><div style="text-align:center;"><h2>⛔ Akses Ditolak</h2><p>Gunakan parameter ?secret=pembda2026storage untuk mengakses tool ini.</p></div></body></html>');
}

@set_time_limit(0);
@ini_set('memory_limit', '512M');

// 2. Bootstrap Laravel
$basePath = realpath(__DIR__ . '/..');
if (!file_exists($basePath . '/vendor/autoload.php')) {
    die('Vendor autoloader tidak ditemukan.');
}
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

$action = $_GET['action'] ?? 'view';
$message = null;
$messageType = 'info';

// 3. Handle Actions
if ($action === 'fix_symlink') {
    $linkPath = public_path('storage');
    $targetPath = storage_path('app/public');
    
    if (is_link($linkPath)) {
        @unlink($linkPath);
    } elseif (is_dir($linkPath)) {
        @rename($linkPath, $linkPath . '_bak_' . time());
    }
    
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        $message = "Symbolic link public/storage berhasil dibuat ulang.";
        $messageType = 'success';
    } catch (\Exception $e) {
        $message = "Gagal membuat symlink: " . $e->getMessage();
        $messageType = 'error';
    }
}

// 4. Deteksi Symlink Status
$linkPath = public_path('storage');
$targetPath = storage_path('app/public');
$isSymlinkActive = false;
$symlinkStatusText = 'Belum Ada';

if (is_link($linkPath)) {
    $actualTarget = readlink($linkPath);
    if (file_exists($linkPath)) {
        $isSymlinkActive = true;
        $symlinkStatusText = 'Aktif & Valid';
    } else {
        $symlinkStatusText = 'Broken (Target Tidak Ditemukan: ' . htmlspecialchars($actualTarget) . ')';
    }
} elseif (is_dir($linkPath)) {
    $symlinkStatusText = 'Peringatan: Berupa Folder Fisik Biasa (Bukan Symlink)';
}

// 5. Audit Registry
$auditRegistry = [
    'users' => ['table' => 'users', 'columns' => ['photo'], 'label' => 'Foto Profil Pengguna (Akun Login)', 'category' => 'Foto Pengguna'],
    'students' => ['table' => 'students', 'columns' => ['photo'], 'label' => 'Foto Profil Siswa', 'category' => 'Foto Pengguna'],
    'teachers' => ['table' => 'teachers', 'columns' => ['photo'], 'label' => 'Foto Profil Guru', 'category' => 'Foto Pengguna'],
    'employees' => ['table' => 'employees', 'columns' => ['photo'], 'label' => 'Foto Profil Pegawai / Tendik', 'category' => 'Foto Pengguna'],
    'alumni_directories' => ['table' => 'alumni_directories', 'columns' => ['photo_path'], 'label' => 'Foto Direktori Alumni', 'category' => 'Foto Pengguna'],
    'applicants' => ['table' => 'applicants', 'columns' => ['photo_path'], 'label' => 'Foto Pasfoto Calon Siswa (PPDB)', 'category' => 'Foto Pengguna'],
    'student_documents' => ['table' => 'student_documents', 'columns' => ['file_path'], 'label' => 'Dokumen Berkas Siswa (KK, Akta, dll)', 'category' => 'Dokumen'],
    'applicant_documents' => ['table' => 'applicant_documents', 'columns' => ['file_path'], 'label' => 'Dokumen Berkas PPDB', 'category' => 'Dokumen'],
    'employee_documents' => ['table' => 'employee_documents', 'columns' => ['file_path'], 'label' => 'Dokumen Arsip Kepegawaian', 'category' => 'Dokumen'],
    'lms_materials' => ['table' => 'lms_materials', 'columns' => ['file_path'], 'label' => 'Berkas Modul & Materi LMS Guru', 'category' => 'LMS & Pembelajaran'],
    'lms_submissions' => ['table' => 'lms_submissions', 'columns' => ['file_path'], 'label' => 'Berkas Tugas Kumpul Siswa (LMS)', 'category' => 'LMS & Pembelajaran'],
    'cbt_questions' => ['table' => 'cbt_questions', 'columns' => ['question_image', 'question_audio', 'option_a_image', 'option_b_image', 'option_c_image', 'option_d_image', 'option_e_image'], 'label' => 'Media & Gambar Soal Ujian CBT', 'category' => 'CBT Online'],
    'pkl_logs' => ['table' => 'pkl_logs', 'columns' => ['photo'], 'label' => 'Foto Logbook Kegiatan PKL Siswa', 'category' => 'PKL DUDI'],
    'pkl_monitorings' => ['table' => 'pkl_monitorings', 'columns' => ['photo_path'], 'label' => 'Foto Kunjungan Monitoring PKL Guru', 'category' => 'PKL DUDI'],
    'pkl_perangkats' => ['table' => 'pkl_perangkats', 'columns' => ['file_path'], 'label' => 'Dokumen Perangkat PKL', 'category' => 'PKL DUDI'],
    'final_projects' => ['table' => 'final_projects', 'columns' => ['file_path', 'proposal_file_path', 'thumbnail_path'], 'label' => 'Berkas & Poster Tugas Akhir / Riset', 'category' => 'Proyek & Riset'],
    'galleries' => ['table' => 'galleries', 'columns' => ['image'], 'label' => 'Foto Galeri Kegiatan Sekolah', 'category' => 'Galeri & Umum'],
    'schools' => ['table' => 'schools', 'columns' => ['logo'], 'label' => 'Logo Identitas Unit Sekolah', 'category' => 'Galeri & Umum'],
];

// Helper cek fisik file
function isFileOnDisk($path) {
    if (empty($path)) return true;
    if (Str::startsWith($path, ['http://', 'https://'])) return true;
    $clean = ltrim(preg_replace('#^/?storage/#', '', $path), '/');
    if (Storage::disk('public')->exists($clean)) return true;
    if (file_exists(public_path($clean)) && is_file(public_path($clean))) return true;
    if (file_exists(public_path('storage/' . $clean)) && is_file(public_path('storage/' . $clean))) return true;
    return false;
}

// 6. Jalankan Pemindaian Audit
$auditResults = [];
$totalAllDb = 0;
$totalAllExist = 0;
$totalAllMissing = 0;
$missingListByTable = [];

$existingTables = array_map(function($tbl) {
    return array_values((array)$tbl)[0];
}, DB::select('SHOW TABLES'));
$existingTablesLookup = array_flip($existingTables);

foreach ($auditRegistry as $key => $item) {
    $tbl = $item['table'];
    if (!isset($existingTablesLookup[$tbl])) continue;

    $columnsInTable = array_flip(Schema::getColumnListing($tbl));
    $validCols = array_filter($item['columns'], fn($col) => isset($columnsInTable[$col]));
    if (empty($validCols)) continue;

    $tblDb = 0;
    $tblExist = 0;
    $tblMissing = 0;
    $missingSample = [];

    foreach ($validCols as $col) {

        $rows = DB::table($tbl)->whereNotNull($col)->where($col, '!=', '')->select(['id', $col])->get();
        foreach ($rows as $r) {
            $raw = trim((string) $r->{$col});
            if (empty($raw) || Str::startsWith($raw, ['http://', 'https://'])) continue;

            $tblDb++;
            $clean = ltrim(preg_replace('#^/?storage/#', '', $raw), '/');

            if (isFileOnDisk($clean)) {
                $tblExist++;
            } else {
                $tblMissing++;
                if (count($missingSample) < 5) {
                    $missingSample[] = ['id' => $r->id, 'path' => $clean];
                }
            }
        }
    }

    $pct = $tblDb > 0 ? round(($tblExist / $tblDb) * 100, 1) : 100;
    $auditResults[$key] = [
        'label' => $item['label'],
        'category' => $item['category'],
        'table' => $tbl,
        'total' => $tblDb,
        'exist' => $tblExist,
        'missing' => $tblMissing,
        'pct' => $pct,
        'samples' => $missingSample,
    ];

    $totalAllDb += $tblDb;
    $totalAllExist += $tblExist;
    $totalAllMissing += $tblMissing;
}

$overallPct = $totalAllDb > 0 ? round(($totalAllExist / $totalAllDb) * 100, 1) : 100;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Integritas Penyimpanan Berkas — PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-4 sm:p-8">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 text-xs font-bold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 rounded-full">SINKRONISASI SERVER LOKAL</span>
                    <span class="text-xs text-slate-400"><?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Local Server') ?></span>
                </div>
                <h1 class="text-2xl font-bold text-white mt-2">Audit Integritas Berkas Fisik vs Database</h1>
                <p class="text-sm text-slate-400 mt-1">Memastikan seluruh file foto profil, berkas siswa, dokumen KBM LMS, dan CBT benar-benar ada di disk lokal.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="?secret=<?= urlencode($secret) ?>" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition flex items-center gap-2 shadow-lg shadow-indigo-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Pindai Ulang
                </a>
                <a href="storage-sync.php?secret=<?= urlencode($secret) ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-sm font-semibold rounded-xl transition">
                    Sync Manager ↗
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="p-4 rounded-xl text-sm font-medium <?= $messageType === 'success' ? 'bg-emerald-950/50 text-emerald-400 border border-emerald-800/50' : 'bg-rose-950/50 text-rose-400 border border-rose-800/50' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Berkas di DB</div>
                <div class="text-3xl font-extrabold text-white mt-2"><?= number_format($totalAllDb) ?></div>
                <div class="text-xs text-slate-500 mt-1">Dicatat di tabel-tabel sistem</div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl">
                <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Berkas Fisik Ada</div>
                <div class="text-3xl font-extrabold text-emerald-400 mt-2"><?= number_format($totalAllExist) ?></div>
                <div class="text-xs text-slate-500 mt-1">Ditemukan di storage disk</div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl">
                <div class="text-xs font-semibold text-rose-400 uppercase tracking-wider">Berkas Hilang / Belum Ada</div>
                <div class="text-3xl font-extrabold text-rose-400 mt-2"><?= number_format($totalAllMissing) ?></div>
                <div class="text-xs text-slate-500 mt-1"><?= $totalAllMissing > 0 ? 'Perlu ditarik dari production' : 'Semua berkas aman' ?></div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl">
                <div class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Persentase Keselarasan</div>
                <div class="text-3xl font-extrabold text-indigo-400 mt-2"><?= $overallPct ?>%</div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: <?= $overallPct ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Symlink & Server Diagnostic -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>🔗 Status Symbolic Link (public/storage)</span>
            </h2>
            <div class="mt-4 flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-xl <?= $isSymlinkActive ? 'bg-emerald-950/30 border border-emerald-900/50' : 'bg-amber-950/30 border border-amber-900/50' ?>">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full <?= $isSymlinkActive ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' ?>"></span>
                        <span class="font-bold text-sm <?= $isSymlinkActive ? 'text-emerald-300' : 'text-amber-300' ?>"><?= $symlinkStatusText ?></span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Target Symlink: <code class="text-slate-300"><?= htmlspecialchars($targetPath) ?></code>
                    </p>
                </div>
                <div>
                    <a href="?secret=<?= urlencode($secret) ?>&action=fix_symlink" onclick="return confirm('Buat ulang symlink public/storage?');" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg border border-slate-700 transition">
                        Perbaiki / Buat Ulang Symlink
                    </a>
                </div>
            </div>
        </div>

        <!-- Table Summary -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white">Rincian Berkas per Komponen Sistem</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Menunjukkan jumlah referensi database vs keberadaan fisik file di server lokal.</p>
                </div>
                <?php if ($totalAllMissing > 0): ?>
                    <span class="px-3 py-1 text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-full">
                        <?= $totalAllMissing ?> Berkas Perlu Disinkronkan
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full">
                        ✓ 100% Selaras
                    </span>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-950/60 text-xs font-bold uppercase text-slate-400 tracking-wider">
                        <tr>
                            <th class="py-4 px-6">Modul / Komponen</th>
                            <th class="py-4 px-6">Tabel</th>
                            <th class="py-4 px-6 text-center">Di Database</th>
                            <th class="py-4 px-6 text-center">Fisik Ada</th>
                            <th class="py-4 px-6 text-center">Fisik Hilang</th>
                            <th class="py-4 px-6 text-right">Kelengkapan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($auditResults as $row): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-4 px-6 font-semibold text-white">
                                    <?= htmlspecialchars($row['label']) ?>
                                    <div class="text-xs text-slate-500 font-normal"><?= htmlspecialchars($row['category']) ?></div>
                                </td>
                                <td class="py-4 px-6">
                                    <code class="px-2 py-0.5 bg-slate-800 text-slate-300 text-xs rounded"><?= htmlspecialchars($row['table']) ?></code>
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-slate-200"><?= number_format($row['total']) ?></td>
                                <td class="py-4 px-6 text-center font-bold text-emerald-400"><?= number_format($row['exist']) ?></td>
                                <td class="py-4 px-6 text-center font-bold <?= $row['missing'] > 0 ? 'text-rose-400' : 'text-slate-500' ?>">
                                    <?= number_format($row['missing']) ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <?php if ($row['total'] === 0): ?>
                                        <span class="text-xs text-slate-500">Tidak ada data</span>
                                    <?php elseif ($row['missing'] === 0): ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                                            ✓ 100%
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-400 bg-rose-500/10 px-2.5 py-1 rounded-full border border-rose-500/20">
                                            <?= $row['pct'] ?>% (<?= $row['missing'] ?> hilang)
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panduan Eksekusi Terminal -->
        <div class="bg-gradient-to-r from-indigo-950/40 via-slate-900 to-slate-900 border border-indigo-900/50 rounded-2xl p-6">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span>💡 Cara Menyinkronkan Berkas yang Hilang ke Server Ubuntu</span>
            </h3>
            <p class="text-xs text-slate-400 mt-1">Buka terminal di server lokal Ubuntu Anda, lalu jalankan perintah berikut:</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <div class="text-xs font-semibold text-emerald-400">1. Tarik Berkas via CLI Tool (Rekomendasi Cepat):</div>
                    <code class="block text-xs font-mono bg-slate-900 p-2.5 rounded-lg text-slate-200 mt-2 select-all">php sync-storage.php --pull --missing-only</code>
                    <p class="text-[11px] text-slate-500 mt-2">Hanya mengunduh file yang belum ada di lokal secara otomatis dalam format batch ZIP.</p>
                </div>

                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <div class="text-xs font-semibold text-indigo-400">2. Tarik Hanya Foto Pengguna (Siswa/Guru/Pegawai):</div>
                    <code class="block text-xs font-mono bg-slate-900 p-2.5 rounded-lg text-slate-200 mt-2 select-all">php sync-storage.php --pull --folder=photos</code>
                    <p class="text-[11px] text-slate-500 mt-2">Menarik seluruh foto profil pengguna tanpa perlu menunggu materi LMS yang besar.</p>
                </div>

                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <div class="text-xs font-semibold text-amber-400">3. Audit Detail Berkas Fisik vs Database:</div>
                    <code class="block text-xs font-mono bg-slate-900 p-2.5 rounded-lg text-slate-200 mt-2 select-all">php artisan storage:audit --detailed</code>
                    <p class="text-[11px] text-slate-500 mt-2">Memeriksa per record dan menampilkan baris data yang link fisiknya kosong.</p>
                </div>

                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <div class="text-xs font-semibold text-purple-400">4. Pastikan Izin Akses & Symlink Ubuntu:</div>
                    <code class="block text-xs font-mono bg-slate-900 p-2.5 rounded-lg text-slate-200 mt-2 select-all">sudo chown -R www-data:www-data storage && php artisan storage:link</code>
                    <p class="text-[11px] text-slate-500 mt-2">Mencegah error 403 / 404 pada web server Apache/Nginx Ubuntu.</p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
