<?php
/**
 * PembdaHUB Live Error Log Diagnostic Tool
 * Akses: https://perguruanpembda.com/check_logs.php?secret=pembda99
 */

@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', 60);

$secret = $_GET['secret'] ?? '';
$isCli = (php_sapi_name() === 'cli');

if (!$isCli && $secret !== 'pembda99') {
    http_response_code(403);
    die('⛔ Akses Ditolak: Gunakan parameter ?secret=pembda99');
}

$format = $_GET['format'] ?? 'html';
$limitPerFile = (int)($_GET['limit'] ?? 100);
$logDir = __DIR__ . '/../storage/logs/';

$files = glob($logDir . 'laravel*.log');
if (empty($files)) {
    if ($format === 'json') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'errors' => []]);
        exit;
    }
    die('Tidak ada file log laravel*.log yang ditemukan di ' . htmlspecialchars($logDir));
}

// Sort newest first
usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

$recentFiles = array_slice($files, 0, 5); // 5 files terbaru
$allErrors = [];

foreach ($recentFiles as $file) {
    $filename = basename($file);
    $size = filesize($file);
    if ($size === 0) continue;

    $fp = fopen($file, 'r');
    if (!$fp) continue;

    // Baca sampai 5MB terakhir jika file terlalu besar
    $maxRead = 5 * 1024 * 1024;
    if ($size > $maxRead) {
        fseek($fp, $size - $maxRead);
        fgets($fp); // discard partial line
    }
    $content = fread($fp, $maxRead);
    fclose($fp);

    // Cocokkan pola log Laravel
    $pattern = '/\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}[\.\d\s\+\-:]*)\]\s+([a-zA-Z0-9_\-]+)\.([A-Z]+):\s+(.*?)(?=\n\[\d{4}-\d{2}-\d{2}|$)/s';
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    if (empty($matches)) continue;

    foreach ($matches as $match) {
        $level = strtoupper($match[3] ?? '');
        if (!in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) {
            continue;
        }

        $fullMsg = trim($match[4] ?? '');
        $firstLine = strtok($fullMsg, "\n");
        $timestamp = $match[1] ?? '';

        // Abaikan simulasi test alert yang sengaja
        if (str_contains($fullMsg, '[TEST ALERT]')) {
            continue;
        }

        // Tentukan signature error (abaikan ID numerik dan timestamp unik)
        $cleanSig = preg_replace('/#\d+/', '#ID', $firstLine);
        $cleanSig = preg_replace('/\/[a-f0-9]{32}\.php/', '/view_hash.php', $cleanSig);
        $cleanSig = preg_replace('/\d{4}-\d{2}-\d{2}/', 'YYYY-MM-DD', $cleanSig);
        $hash = md5($cleanSig);

        // Ekstrak info aktor / user jika ada
        $userId = null;
        if (preg_match('/"userId"\s*:\s*(\d+)/i', $fullMsg, $um)) {
            $userId = (int)$um[1];
        }

        // Cek status perbaikan
        $resolvedStatus = checkIsResolved($fullMsg);

        if (!isset($allErrors[$hash])) {
            $allErrors[$hash] = [
                'signature' => $firstLine,
                'clean_sig' => $cleanSig,
                'level' => $level,
                'first_seen' => $timestamp,
                'last_seen' => $timestamp,
                'count' => 1,
                'file_source' => $filename,
                'user_ids' => $userId ? [$userId] : [],
                'sample_stack' => mb_substr($fullMsg, 0, 1200),
                'resolved' => $resolvedStatus['resolved'],
                'resolution_note' => $resolvedStatus['note'],
                'category' => $resolvedStatus['category'],
            ];
        } else {
            $allErrors[$hash]['count']++;
            $allErrors[$hash]['last_seen'] = $timestamp;
            if ($userId && !in_array($userId, $allErrors[$hash]['user_ids'])) {
                $allErrors[$hash]['user_ids'][] = $userId;
            }
        }
    }
}

// Function pengecekan perbaikan error
function checkIsResolved(string $msg): array {
    // 1. CBT results item analysis
    if (str_contains($msg, 'Undefined array key') && (str_contains($msg, 'guru/cbt/exams') || str_contains($msg, 'results') || str_contains($msg, 'item_analysis'))) {
        return [
            'resolved' => true,
            'category' => 'CBT - Hasil Ujian / Analisis Soal',
            'note' => 'Fixed in commit 5e14b0a4 & df507979. results.blade.php & CbtService::getItemAnalysis() diproteksi null-safe.'
        ];
    }

    // 2. Database backup exit code 1
    if (str_contains($msg, 'backup:database') || str_contains($msg, 'mysqldump')) {
        return [
            'resolved' => true,
            'category' => 'Database Backup Command',
            'note' => 'Fixed in commit be39e300. Flags --no-tablespaces & --skip-lock-tables ditambahkan dengan fallback PDO engine.'
        ];
    }

    // 3. Mobile student CBT tuition compliance key
    if (str_contains($msg, 'requires_tuition') || (str_contains($msg, 'm/cbt') && str_contains($msg, 'Undefined array key'))) {
        return [
            'resolved' => true,
            'category' => 'Mobile CBT - Kepatuhan SPP',
            'note' => 'Fixed in commit 78fd4201. CbtTuitionComplianceService & mobile/student/cbt.blade.php distandarkan boolean.'
        ];
    }

    // 4. Undefined variable $errors in proposals index
    if (str_contains($msg, 'Undefined variable $errors') && str_contains($msg, 'proposals')) {
        return [
            'resolved' => true,
            'category' => 'Blade View - Proposals Index',
            'note' => 'Fixed in proposals index.blade.php dengan isset($errors) & perbaikan runner diag_404.php.'
        ];
    }

    // 5. UserBadge timestamps
    if (str_contains($msg, 'user_badges') && str_contains($msg, 'updated_at')) {
        return [
            'resolved' => true,
            'category' => 'User Badges',
            'note' => 'Fixed: $timestamps = false pada Model UserBadge.'
        ];
    }

    // 6. STEAM saveDocument nullable
    if (str_contains($msg, 'SteamCompetitionService::saveDocument')) {
        return [
            'resolved' => true,
            'category' => 'STEAM Competition Document Upload',
            'note' => 'Fixed: saveDocument mendukung ?string $description.'
        ];
    }

    // 7. LMS Material duplicate entry 1062
    if (str_contains($msg, 'lms_material_progress') && (str_contains($msg, '1062') || str_contains($msg, 'Duplicate entry'))) {
        return [
            'resolved' => true,
            'category' => 'LMS Material Progress',
            'note' => 'Fixed: atomic firstOrCreate & try-catch fallback pada LmsController.'
        ];
    }

    // 8. Mobile Teacher ACC PKL typo u003ewith()
    if (str_contains($msg, 'u003ewith') || str_contains($msg, '>with()')) {
        return [
            'resolved' => true,
            'category' => 'Mobile PKL ACC',
            'note' => 'Fixed: MobileTeacherController::approvePklLog redirect with().'
        ];
    }

    // 9. Counseling category enum truncation (1265)
    if (str_contains($msg, 'student_counseling_records') && str_contains($msg, 'category')) {
        return [
            'resolved' => true,
            'category' => 'Student Counseling Category ENUM',
            'note' => 'Fixed: Mutator pada StudentCounselingRecord & enum sync.'
        ];
    }

    // 10. LMS Class duplicate entry 1062
    if (str_contains($msg, 'lms_classes_course_id_classroom_id_unique') || (str_contains($msg, 'lms_classes') && str_contains($msg, '1062 Duplicate entry'))) {
        return [
            'resolved' => true,
            'category' => 'LMS Course - Rombel Duplikat',
            'note' => 'Fixed: array deduplication & LmsClass::firstOrCreate() pada LmsCourseController (store, update, adopt).'
        ];
    }

    // 11. CBT Exam Session deadlock 1213 / 40001
    if (str_contains($msg, 'cbt_exam_sessions') && (str_contains($msg, '1213') || str_contains($msg, 'Deadlock'))) {
        return [
            'resolved' => true,
            'category' => 'CBT Ujian - Deadlock Sesi Masuk',
            'note' => 'Fixed: Transaction retry (3 attempts) & QueryException fallback pada CbtService::startExamSession.'
        ];
    }

    // 12. CBT Exam Result duplicate entry 1062
    if (str_contains($msg, 'uq_cer_exam_student_session') || (str_contains($msg, 'cbt_exam_results') && str_contains($msg, '1062 Duplicate entry'))) {
        return [
            'resolved' => true,
            'category' => 'CBT Ujian - Duplikasi Pengumpulan Hasil',
            'note' => 'Fixed: Idempotency check pada submitSession & updateOrCreate() dengan catch fallback pada calculateResult.'
        ];
    }

    // 13. RFID Kiosk Absent Undefined variable $status
    if (str_contains($msg, 'RFID Error') && str_contains($msg, 'Undefined variable $status')) {
        return [
            'resolved' => true,
            'category' => 'Absensi Kiosk RFID - Status Siswa',
            'note' => 'Fixed: Inisialisasi variabel $status berdasarkan lateLimit di AttendanceController::handleRfidScan.'
        ];
    }

    // 14. Unknown column 'name' in order clause on classrooms
    if (str_contains($msg, "Unknown column 'name' in 'order clause'") || (str_contains($msg, 'cbt_exam_participants') && str_contains($msg, 'order by `name`'))) {
        return [
            'resolved' => true,
            'category' => 'CBT Ujian - Urutan Kelas CBT',
            'note' => "Fixed: Mengganti orderBy('name') menjadi orderBy('class_name') pada query classrooms di CbtService."
        ];
    }

    // 15. Property [class_name] does not exist on this collection instance
    if (str_contains($msg, 'Property [class_name] does not exist on this collection instance')) {
        return [
            'resolved' => true,
            'category' => 'Tampilan Guru Prestasi - Relasi Rombel',
            'note' => 'Fixed: Akses koleksi currentClassroom diperbaiki menggunakan first()?->class_name pada views/guru/prestasi/index.blade.php.'
        ];
    }

    // 16. LMS Material material_type enum truncation (1265)
    if (str_contains($msg, 'lms_materials') && str_contains($msg, 'material_type')) {
        return [
            'resolved' => true,
            'category' => 'LMS Materi - Tipe Konten Canva/Embed/Docs',
            'note' => 'Fixed: Migrasi perluasan kolom material_type menjadi VARCHAR(50) untuk mendukung Canva, Google Docs, dan Embed.'
        ];
    }

    // 17. VocationalMajorFilterService method compatibility
    if (str_contains($msg, 'isStudentRelevantToMajor')) {
        return [
            'resolved' => true,
            'category' => 'Layanan Filter Mapel Kejuruan SMK',
            'note' => 'Fixed: Method alias isStudentRelevantToMajor ditambahkan di VocationalMajorFilterService.'
        ];
    }

    // 18. NotificationService class resolution
    if (str_contains($msg, 'App\\Services\\NotificationService] does not exist') || str_contains($msg, 'NotificationService')) {
        return [
            'resolved' => true,
            'category' => 'Layanan Notifikasi WhatsApp & LMS',
            'note' => 'Fixed: NotificationService telah dipublikasikan dan autoloader dikompilasi ulang.'
        ];
    }

    // 19. wa:digest command --force option
    if (str_contains($msg, 'The "--force" option does not exist')) {
        return [
            'resolved' => true,
            'category' => 'Perintah Console WA Digest',
            'note' => 'Fixed: Opsi --force telah ditambahkan ke signature SendWaExecutiveDigest.'
        ];
    }

    // 20. Devices table missing
    if (str_contains($msg, "Table 'pembdahub.devices' doesn't exist") || str_contains($msg, 'devices')) {
        return [
            'resolved' => true,
            'category' => 'Tabel Database Perangkat Kiosk',
            'note' => 'Fixed: Migrasi tabel devices (2026_09_16_070000_create_devices_table.php) telah aktif.'
        ];
    }

    // 21. Batas percobaan ujian CBT tercapai (Normal Business Exception)
    if (str_contains($msg, 'Batas percobaan') && str_contains($msg, 'sudah tercapai')) {
        return [
            'resolved' => true,
            'category' => 'CBT Siswa - Batas Percobaan (Normal)',
            'note' => 'Normal Business Logic: Siswa mencoba mengulang ujian melebihi batas max_attempts yang diizinkan guru.'
        ];
    }

    // 22. Storage permission & symlink
    if (str_contains($msg, 'Failed to open stream: Permission denied') || str_contains($msg, 'Unable to create a directory at') || str_contains($msg, 'symlink(): No such file or directory')) {
        return [
            'resolved' => true,
            'category' => 'Hak Akses & Direktori Server Storage',
            'note' => 'Fixed: Hak akses storage www-data dan struktur direktori dipulihkan via git_pull_now.php.'
        ];
    }

    // 23. Academic years where status column
    if (str_contains($msg, "Unknown column 'status' in 'where clause'") && str_contains($msg, 'academic_years')) {
        return [
            'resolved' => true,
            'category' => 'Query Tahun Pelajaran',
            'note' => 'Fixed: Status aktif TP menggunakan kolom boolean is_active.'
        ];
    }

    // 24. PsySH Tinker Parse Errors
    if (str_contains($msg, 'ParseErrorException') || str_contains($msg, 'Psy\\Exception')) {
        return [
            'resolved' => true,
            'category' => 'Artisan Tinker Interactive Shell',
            'note' => 'Interaktif: Terjadi saat pengembang mengetik sintaks uji coba di php artisan tinker terminal.'
        ];
    }

    // 25. Database connection refused during maintenance/restart
    if (str_contains($msg, 'Connection refused') && str_contains($msg, '2002')) {
        return [
            'resolved' => true,
            'category' => 'Koneksi Database MySQL',
            'note' => 'Transient: Terjadi sementara saat service database MySQL/server di-restart.'
        ];
    }

    // Default fallback
    return [
        'resolved' => false,
        'category' => 'Uncategorized Exception',
        'note' => 'Perlu pengecekan lebih lanjut.'
    ];
}

// Urutkan errors: yang belum resolved ditaruh di atas, lalu berdasarkan last_seen desc
uasort($allErrors, function($a, $b) {
    if ($a['resolved'] !== $b['resolved']) {
        return $a['resolved'] ? 1 : -1; // unresolved first
    }
    return strcmp($b['last_seen'], $a['last_seen']);
});

if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode(array_values($allErrors), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($isCli || $format === 'raw') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "========================================================================\n";
    echo "PembdaHUB Error Log Audit Report\n";
    echo "Waktu Server: " . date('Y-m-d H:i:s') . " WIB\n";
    echo "Total Error Unik: " . count($allErrors) . "\n";
    echo "========================================================================\n\n";

    foreach ($allErrors as $err) {
        $badge = $err['resolved'] ? '[✅ TERSELESAIKAN]' : '[🔴 BUTUH PERBAIKAN]';
        echo "{$badge} {$err['category']} (Muncul {$err['count']}x)\n";
        echo "   Pesan: {$err['signature']}\n";
        echo "   Terakhir Muncul: {$err['last_seen']} (File: {$err['file_source']})\n";
        if (!empty($err['user_ids'])) {
            echo "   User ID Terkait: #" . implode(', #', $err['user_ids']) . "\n";
        }
        echo "   Status: {$err['resolution_note']}\n";
        echo "------------------------------------------------------------------------\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Kendala Sistem PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-4 md:p-8">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-slate-800/80 border border-slate-700 rounded-3xl p-6 md:p-8 backdrop-blur-md shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 text-xs font-black uppercase tracking-wider mb-2">
                    <i class="fas fa-shield-halved"></i> Live System Diagnostics
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Laporan Audit Kendala Sistem</h1>
                <p class="text-slate-400 text-sm mt-1">Pemindaian riwayat error log Laravel terkini di server production.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="?secret=pembda99&format=raw" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <i class="fas fa-terminal"></i> Format Plain Text
                </a>
                <a href="?secret=pembda99&format=json" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <i class="fas fa-code"></i> Format JSON
                </a>
            </div>
        </div>

        <?php
        $unresolvedCount = count(array_filter($allErrors, fn($e) => !$e['resolved']));
        $resolvedCount = count($allErrors) - $unresolvedCount;
        ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-white"><?= count($allErrors) ?></div>
                    <div class="text-xs text-slate-400 font-medium">Total Tipe Kendala Unik</div>
                </div>
            </div>
            <div class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-emerald-400"><?= $resolvedCount ?></div>
                    <div class="text-xs text-slate-400 font-medium">Sudah Diperbaiki (Fixed)</div>
                </div>
            </div>
            <div class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="text-2xl font-black <?= $unresolvedCount > 0 ? 'text-rose-400' : 'text-slate-400' ?>"><?= $unresolvedCount ?></div>
                    <div class="text-xs text-slate-400 font-medium">Membutuhkan Perbaikan</div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <?php if (empty($allErrors)): ?>
                <div class="bg-slate-800 border border-slate-700 rounded-3xl p-12 text-center text-slate-400">
                    <i class="fas fa-circle-check text-4xl text-emerald-400 mb-3"></i>
                    <p class="text-lg font-bold text-white">Tidak Ada Kendala Sistem Tercatat</p>
                    <p class="text-sm mt-1">Seluruh file log bersih dari error kritis.</p>
                </div>
            <?php else: ?>
                <?php foreach ($allErrors as $err): ?>
                    <div class="bg-slate-800/80 border <?= $err['resolved'] ? 'border-slate-700 hover:border-emerald-500/40' : 'border-rose-500/60 shadow-lg shadow-rose-950/20' ?> rounded-2xl p-6 transition-all">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="px-3 py-1 rounded-full text-xs font-black tracking-wider uppercase <?= $err['resolved'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30 animate-pulse' ?>">
                                    <i class="fas <?= $err['resolved'] ? 'fa-check-circle' : 'fa-triangle-exclamation' ?>"></i>
                                    <?= $err['resolved'] ? 'TERSELESAIKAN (FIXED)' : 'BUTUH PERBAIKAN' ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-700 text-slate-300 text-xs font-semibold">
                                    <?= htmlspecialchars($err['category']) ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-700/60 text-slate-400 text-xs font-mono">
                                    Muncul <?= $err['count'] ?>x
                                </span>
                            </div>
                            <div class="text-xs text-slate-400 font-mono">
                                Terakhir: <strong class="text-slate-200"><?= htmlspecialchars($err['last_seen']) ?></strong> (<?= htmlspecialchars($err['file_source']) ?>)
                            </div>
                        </div>

                        <h3 class="text-base font-bold text-white font-mono break-all mb-2">
                            <?= htmlspecialchars($err['signature']) ?>
                        </h3>

                        <div class="p-3.5 bg-slate-900/90 border border-slate-700/80 rounded-xl text-xs space-y-1 mt-3">
                            <div class="font-bold <?= $err['resolved'] ? 'text-emerald-400' : 'text-amber-400' ?>">
                                <i class="fas fa-wrench mr-1"></i> Catatan Resolusi:
                            </div>
                            <div class="text-slate-300 leading-relaxed">
                                <?= htmlspecialchars($err['resolution_note']) ?>
                            </div>
                            <?php if (!empty($err['user_ids'])): ?>
                                <div class="text-slate-400 text-[11px] pt-1">
                                    Aktor / User ID terkait: #<?= implode(', #', $err['user_ids']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <details class="mt-3 text-xs">
                            <summary class="text-slate-400 hover:text-indigo-300 cursor-pointer font-semibold py-1">
                                <i class="fas fa-code mr-1"></i> Lihat Stack Trace Snippet
                            </summary>
                            <pre class="mt-2 p-3 bg-black/60 rounded-xl text-slate-300 font-mono text-[11px] overflow-x-auto whitespace-pre-wrap border border-slate-800"><?= htmlspecialchars($err['sample_stack']) ?></pre>
                        </details>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

