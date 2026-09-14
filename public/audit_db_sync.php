<?php
/**
 * PembdaHUB — Database Audit & Forensic Sync Inspector
 * Membandingkan Database Server Lokal vs Backup Asli Sabtu, 12 Sept 2026 (u474310197_database.sql.gz)
 * URL: http://[IP-SERVER]/audit_db_sync.php?secret=pembda99
 */

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('<h1>403 Forbidden</h1><p>Akses ditolak.</p>');
}

@set_time_limit(0);
@ini_set('memory_limit', '1024M');

// Bootstrap DB from .env
$envPaths = [
    __DIR__ . '/../.env',
    '/var/www/pembdahub/.env',
    '/var/www/html/pembdahub/.env',
    __DIR__ . '/.env',
];

$envPath = null;
foreach ($envPaths as $p) {
    if (file_exists($p)) {
        $envPath = $p;
        break;
    }
}

$env = [];
if ($envPath && file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim(trim($v), '"\'');
    }
}

$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? 'pembdahub';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Exception $e) {
    die("<h1>Database Connection Failed</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

// 180 Table counts from Saturday 12 Sept 2026 Dump (u474310197_database.sql.gz)
$dumpCounts = array (
  'academic_years' => 2,
  'achievement_fee_exemption_rules' => 0,
  'activity_logs' => 636920,
  'admission_discounts' => 9,
  'admission_fees' => 4,
  'admission_tests' => 10,
  'alumni' => 318,
  'alumni_directories' => 443,
  'alumni_forums' => 0,
  'alumni_forum_replies' => 0,
  'alumni_messages' => 0,
  'alumni_profiles' => 337,
  'applicants' => 39,
  'applicant_achievements' => 0,
  'applicant_discounts' => 0,
  'applicant_documents' => 0,
  'applicant_fee_exemptions' => 0,
  'applicant_payments' => 0,
  'applicant_test_scores' => 0,
  'attendances' => 47033,
  'badges' => 4,
  'block_schedules' => 3,
  'block_student_groups' => 451,
  'cache' => 141,
  'cache_locks' => 0,
  'cbt_answers' => 20,
  'cbt_exams' => 12,
  'cbt_exam_participants' => 72,
  'cbt_exam_questions' => 21,
  'cbt_exam_question_bank' => 3,
  'cbt_exam_results' => 21,
  'cbt_exam_sessions' => 49,
  'cbt_questions' => 459,
  'cbt_question_banks' => 41,
  'cbt_question_options' => 1338,
  'classrooms' => 110,
  'counseling_participants' => 30,
  'device_tokens' => 0,
  'dudis' => 73,
  'educational_calendars' => 123,
  'email_queue' => 0,
  'employees' => 119,
  'employee_attendances' => 2625,
  'employee_contracts' => 2,
  'employee_documents' => 0,
  'employee_educations' => 0,
  'employee_family_members' => 0,
  'employee_leaves' => 3,
  'employee_positions' => 182,
  'employee_trainings' => 0,
  'employee_workload_summaries' => 120,
  'expense_categories' => 5,
  'extracurriculars' => 36,
  'extracurricular_activities' => 0,
  'extracurricular_members' => 728,
  'failed_jobs' => 0,
  'final_grades' => 208,
  'final_projects' => 124,
  'final_project_formats' => 4,
  'final_project_logs' => 8,
  'final_project_members' => 176,
  'forum_groups' => 132,
  'forum_group_members' => 4555,
  'forum_likes' => 437167,
  'forum_members' => 2716,
  'forum_place_pixels' => 2,
  'forum_polls' => 4,
  'forum_poll_options' => 11,
  'forum_poll_votes' => 27,
  'forum_reactions' => 1306,
  'forum_replies' => 36108,
  'forum_threads' => 14668,
  'foundation_letters' => 2,
  'foundation_letter_reads' => 82,
  'gallery_items' => 11,
  'grades' => 11236,
  'grade_weights' => 4,
  'jobs' => 248662,
  'job_batches' => 0,
  'job_postings' => 0,
  'knowledge_bookmarks' => 20,
  'knowledge_likes' => 77,
  'knowledge_materials' => 23,
  'konsentrasi_keahlians' => 5,
  'lms_announcements' => 86,
  'lms_assignments' => 879,
  'lms_assignment_groups' => 243,
  'lms_assignment_group_members' => 1083,
  'lms_assignment_submissions' => 0,
  'lms_certificates' => 1044,
  'lms_classes' => 819,
  'lms_courses' => 634,
  'lms_course_groups' => 161,
  'lms_course_group_members' => 695,
  'lms_discussions' => 63,
  'lms_discussion_replies' => 182,
  'lms_enrollments' => 25821,
  'lms_games' => 19,
  'lms_game_attempts' => 102,
  'lms_live_answers' => 0,
  'lms_live_players' => 0,
  'lms_live_sessions' => 9,
  'lms_materials' => 2886,
  'lms_material_notes' => 610,
  'lms_material_progress' => 26244,
  'lms_material_reactions' => 407,
  'lms_meeting_attendances' => 16,
  'lms_meeting_sessions' => 29,
  'lms_modules' => 1518,
  'lms_quizzes' => 273,
  'lms_quiz_answers' => 64664,
  'lms_quiz_attempts' => 6825,
  'lms_quiz_questions' => 2630,
  'lms_student_achievements' => 0,
  'lms_submissions' => 10144,
  'login_history' => 0,
  'majors' => 11,
  'messages' => 0,
  'migrations' => 224,
  'news' => 3,
  'notification_logs' => 0,
  'operational_expenses' => 0,
  'p5_assessments' => 0,
  'p5_projects' => 0,
  'p5_project_notes' => 0,
  'p5_project_targets' => 0,
  'parents' => 662,
  'password_reset_tokens' => 32,
  'payments' => 4422,
  'payment_types' => 22,
  'pembda_tower_bricks' => 51,
  'pembda_tower_brick_likes' => 0,
  'performance_contracts' => 80,
  'performance_evaluations' => 1,
  'personal_access_tokens' => 5,
  'pkl_grades' => 2,
  'pkl_logs' => 2278,
  'pkl_monitorings' => 112,
  'pkl_placements' => 222,
  'positions' => 50,
  'program_keahlians' => 4,
  'puzzles' => 1,
  'puzzle_pieces' => 50,
  'registration_waves' => 5,
  'report_cards' => 290,
  'reputations' => 2131,
  'reputation_logs' => 573522,
  'schedules' => 1908,
  'schools' => 6,
  'school_contributions' => 6,
  'semesters' => 4,
  'sessions' => 115,
  'settings' => 132,
  'sim_projects' => 13,
  'students' => 2028,
  'student_achievements' => 329,
  'student_bills' => 33300,
  'student_classes' => 2941,
  'student_counseling_records' => 20,
  'student_development_notes' => 12,
  'student_diagnostic_assessments' => 100,
  'student_promotions' => 0,
  'student_recommendations' => 6,
  'student_status_histories' => 0,
  'subjects' => 124,
  'subject_teacher' => 259,
  'surveys' => 2,
  'survey_answers' => 883,
  'survey_questions' => 29,
  'survey_responses' => 77,
  'teachers' => 99,
  'teacher_schools' => 2,
  'teaching_assignments' => 1505,
  'tefa_attendances' => 0,
  'tefa_employees' => 3,
  'time_slots' => 245,
  'tracer_studies' => 8,
  'training_modules' => 0,
  'users' => 2962,
  'user_badges' => 1907,
);

$localCounts = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $t = $row[0];
    try {
        $localCounts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
    } catch (Exception $e) {
        $localCounts[$t] = -1;
    }
}

$skipEphemeral = ['jobs', 'activity_logs', 'sessions', 'cache', 'cache_locks', 'failed_jobs'];

$matched = [];
$missingTables = [];
$fewerRows = [];
$moreRows = [];
$localOnly = [];

foreach ($dumpCounts as $table => $dumpCnt) {
    if (in_array($table, $skipEphemeral)) continue;

    if (!isset($localCounts[$table])) {
        $missingTables[$table] = [
            'dump' => $dumpCnt,
            'local' => 0,
            'diff' => -$dumpCnt
        ];
    } elseif ($localCounts[$table] < $dumpCnt) {
        $fewerRows[$table] = [
            'dump' => $dumpCnt,
            'local' => $localCounts[$table],
            'diff' => $localCounts[$table] - $dumpCnt
        ];
    } elseif ($localCounts[$table] > $dumpCnt) {
        $moreRows[$table] = [
            'dump' => $dumpCnt,
            'local' => $localCounts[$table],
            'diff' => $localCounts[$table] - $dumpCnt
        ];
    } else {
        $matched[$table] = [
            'dump' => $dumpCnt,
            'local' => $localCounts[$table],
            'diff' => 0
        ];
    }
}

foreach ($localCounts as $table => $locCnt) {
    if (!isset($dumpCounts[$table])) {
        $localOnly[$table] = $locCnt;
    }
}

// Check JSON format
if (($_GET['format'] ?? '') === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'server_info' => [
            'database' => $dbName,
            'host' => $dbHost,
            'time' => date('Y-m-d H:i:s')
        ],
        'summary' => [
            'total_dump_tables' => count($dumpCounts),
            'total_local_tables' => count($localCounts),
            'matched' => count($matched),
            'missing_tables' => count($missingTables),
            'fewer_rows' => count($fewerRows),
            'more_rows' => count($moreRows),
            'local_only' => count($localOnly),
        ],
        'missing_tables' => $missingTables,
        'fewer_rows' => $fewerRows,
        'more_rows' => $moreRows,
        'matched' => $matched,
        'local_only' => $localOnly,
    ], JSON_PRETTY_PRINT);
    exit(0);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Database Server Lokal vs Backup Sabtu 12 Sept - PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-4 sm:p-8">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-slate-800/90 border border-slate-700 p-6 rounded-3xl shadow-xl flex items-center justify-between flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-sky-500/10 text-sky-400 border border-sky-500/20 rounded-lg text-xs font-bold uppercase mb-2">
                    <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span> Forensic Database Audit
                </span>
                <h1 class="text-2xl font-black text-white">🔍 Audit Integritas Database Server Lokal</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Membandingkan <strong class="text-white"><?= count($localCounts) ?> tabel</strong> di database <code class="text-sky-300"><?= htmlspecialchars($dbName) ?></code> terhadap <strong class="text-white"><?= count($dumpCounts) ?> tabel</strong> pada Backup Sabtu, 12 Sept 2026.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="?secret=<?= htmlspecialchars($_GET['secret']) ?>&format=json" target="_blank" class="px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl text-xs font-bold transition">
                    JSON API
                </a>
                <a href="?secret=<?= htmlspecialchars($_GET['secret']) ?>" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold transition">
                    🔄 Muat Ulang Audit
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tabel 100% Cocok</span>
                <div class="text-3xl font-black text-emerald-400 mt-1"><?= count($matched) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">Jumlah baris identik</span>
            </div>
            <div class="bg-slate-800 border <?= count($fewerRows) > 0 ? 'border-rose-500/50 bg-rose-950/20' : 'border-slate-700' ?> p-5 rounded-2xl">
                <span class="text-xs font-bold <?= count($fewerRows) > 0 ? 'text-rose-400' : 'text-slate-400' ?> uppercase tracking-wider block">Tabel Kurang Baris</span>
                <div class="text-3xl font-black <?= count($fewerRows) > 0 ? 'text-rose-400' : 'text-slate-200' ?> mt-1"><?= count($fewerRows) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">⚠️ Terindikasi belum lengkap</span>
            </div>
            <div class="bg-slate-800 border <?= count($missingTables) > 0 ? 'border-amber-500/50 bg-amber-950/20' : 'border-slate-700' ?> p-5 rounded-2xl">
                <span class="text-xs font-bold <?= count($missingTables) > 0 ? 'text-amber-400' : 'text-slate-400' ?> uppercase tracking-wider block">Tabel Hilang</span>
                <div class="text-3xl font-black <?= count($missingTables) > 0 ? 'text-amber-400' : 'text-slate-200' ?> mt-1"><?= count($missingTables) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">Tidak ditemukan di database</span>
            </div>
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tabel Lebih Banyak</span>
                <div class="text-3xl font-black text-sky-400 mt-1"><?= count($moreRows) ?></div>
                <span class="text-xs text-slate-400 mt-0.5 block">Data baru / aktivitas lokal</span>
            </div>
        </div>

        <!-- 1. TABEL DENGAN BARIS LEBIH SEDIKIT (POTENSI DATA TERTINGGAL) -->
        <?php if (!empty($fewerRows)): ?>
        <div class="bg-slate-800 border border-rose-500/40 rounded-3xl overflow-hidden shadow-xl">
            <div class="p-5 bg-rose-950/30 border-b border-rose-500/30 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-rose-300 text-base flex items-center gap-2">
                        <span>⚠️</span> Tabel dengan Baris Lebih Sedikit di Server Lokal (Potensi Data Belum Masuk)
                    </h3>
                    <p class="text-xs text-rose-200/70 mt-0.5">Tabel-tabel ini memiliki jumlah baris lebih kecil dibanding backup 12 Sept 2026.</p>
                </div>
                <span class="px-3 py-1 bg-rose-500/20 text-rose-300 font-mono text-xs rounded-lg font-bold">
                    <?= count($fewerRows) ?> Tabel
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-800/80 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-700">
                        <tr>
                            <th class="py-3 px-4">Nama Tabel</th>
                            <th class="py-3 px-4 text-right">Backup 12 Sept</th>
                            <th class="py-3 px-4 text-right">Server Lokal</th>
                            <th class="py-3 px-4 text-right">Selisih</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60 font-mono">
                        <?php foreach ($fewerRows as $tbl => $info): ?>
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="py-3 px-4 font-bold text-white font-sans"><?= htmlspecialchars($tbl) ?></td>
                            <td class="py-3 px-4 text-right text-slate-300"><?= number_format($info['dump']) ?></td>
                            <td class="py-3 px-4 text-right text-amber-300 font-bold"><?= number_format($info['local']) ?></td>
                            <td class="py-3 px-4 text-right text-rose-400 font-bold"><?= number_format($info['diff']) ?></td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] bg-rose-500/20 text-rose-300">Kurang Data</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="p-5 bg-emerald-950/40 border border-emerald-500/40 rounded-2xl text-emerald-200 flex items-center gap-3">
            <span class="text-2xl">🎉</span>
            <div>
                <strong class="text-white block">Tidak Ada Tabel yang Kekurangan Data!</strong>
                <span class="text-xs text-emerald-300">Semua tabel di server lokal memiliki jumlah baris sama atau lebih banyak dari backup Sabtu 12 Sept.</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- 2. TABEL HILANG -->
        <?php if (!empty($missingTables)): ?>
        <div class="bg-slate-800 border border-amber-500/40 rounded-3xl overflow-hidden shadow-xl">
            <div class="p-5 bg-amber-950/30 border-b border-amber-500/30 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-amber-300 text-base flex items-center gap-2">
                        <span>❌</span> Tabel yang Hilang / Belum Ada di Server Lokal
                    </h3>
                </div>
                <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono text-xs rounded-lg font-bold">
                    <?= count($missingTables) ?> Tabel
                </span>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <?php foreach ($missingTables as $tbl => $info): ?>
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-700/60 flex items-center justify-between">
                        <span class="font-bold text-white text-xs font-mono"><?= htmlspecialchars($tbl) ?></span>
                        <span class="text-xs text-amber-300"><?= number_format($info['dump']) ?> baris</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 3. TABEL DENGAN BARIS LEBIH BANYAK (DATA BARU) -->
        <?php if (!empty($moreRows)): ?>
        <div class="bg-slate-800 border border-slate-700 rounded-3xl overflow-hidden shadow-xl">
            <div class="p-5 border-b border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-sky-300 text-base flex items-center gap-2">
                        <span>ℹ️</span> Tabel dengan Baris Lebih Banyak di Server Lokal (Aktivitas Baru)
                    </h3>
                </div>
                <span class="px-3 py-1 bg-sky-500/20 text-sky-300 font-mono text-xs rounded-lg font-bold">
                    <?= count($moreRows) ?> Tabel
                </span>
            </div>
            <div class="overflow-x-auto max-h-72">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-800 text-slate-400 uppercase font-semibold text-[10px] border-b border-slate-700 sticky top-0">
                        <tr>
                            <th class="py-2.5 px-4">Nama Tabel</th>
                            <th class="py-2.5 px-4 text-right">Backup 12 Sept</th>
                            <th class="py-2.5 px-4 text-right">Server Lokal</th>
                            <th class="py-2.5 px-4 text-right">Penambahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60 font-mono">
                        <?php foreach ($moreRows as $tbl => $info): ?>
                        <tr class="hover:bg-slate-700/30">
                            <td class="py-2 px-4 font-bold text-white font-sans"><?= htmlspecialchars($tbl) ?></td>
                            <td class="py-2 px-4 text-right text-slate-400"><?= number_format($info['dump']) ?></td>
                            <td class="py-2 px-4 text-right text-sky-300 font-bold"><?= number_format($info['local']) ?></td>
                            <td class="py-2 px-4 text-right text-emerald-400 font-bold">+<?= number_format($info['diff']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- 4. TABEL COCOK SEMPURNA -->
        <div class="bg-slate-800 border border-slate-700 rounded-3xl overflow-hidden shadow-xl">
            <div class="p-5 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-black text-emerald-300 text-base flex items-center gap-2">
                    <span>✅</span> Daftar Tabel 100% Cocok Sempurna (<?= count($matched) ?> Tabel)
                </h3>
            </div>
            <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-64 overflow-y-auto">
                <?php foreach ($matched as $tbl => $info): ?>
                    <div class="bg-slate-900/40 p-2.5 rounded-lg border border-slate-700/40 flex items-center justify-between text-xs">
                        <span class="text-slate-300 truncate mr-2" title="<?= htmlspecialchars($tbl) ?>"><?= htmlspecialchars($tbl) ?></span>
                        <span class="font-mono text-emerald-400 font-bold"><?= number_format($info['dump']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 py-4">
            PembdaHUB Forensic Audit Tool · Perguruan Pembda Nias
        </div>
    </div>
</body>
</html>