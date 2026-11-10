<?php
/**
 * Diagnosa Keaktifan Guru di LMS (Production Check)
 * Akses: https://perguruanpembda.com/cek_devi_lms.php?secret=pembda99
 */
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('Unauthorized: Akses ditolak.');
}

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Teacher;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\LmsCourse;
use App\Models\LmsModule;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;

$format = $_GET['format'] ?? 'html';

// 1. Cari data User & Guru
$users = User::where('name', 'like', '%Devi Asri%')
    ->orWhere('email', 'like', '%deviasri%')
    ->orWhere('username', 'like', '%devi%')
    ->get();

$teachers = Teacher::where('name', 'like', '%Devi Asri%')
    ->orWhere('name', 'like', '%Devi%Halawa%')
    ->orWhere('nuptk', 'like', '%6538776677230133%')
    ->with('school', 'user')
    ->get();

$teacherIds = $teachers->pluck('id')->toArray();
$userIds = $users->pluck('id')->merge($teachers->pluck('user_id')->filter())->unique()->toArray();

$activeYear = AcademicYear::where('is_active', true)->first();

// 2. Cek Penugasan Mengajar (Teaching Assignments)
$teachingAssignments = [];
if (!empty($teacherIds) && Schema::hasTable('teaching_assignments')) {
    $teachingAssignments = TeachingAssignment::whereIn('teacher_id', $teacherIds)
        ->with(['subject', 'classroom', 'academicYear'])
        ->get();
}

// 3. Cek Jadwal Mengajar (Schedules)
$schedules = [];
if (!empty($teacherIds) && Schema::hasTable('schedules')) {
    $schedules = Schedule::whereIn('teacher_id', $teacherIds)
        ->with(['subject', 'classroom', 'academicYear'])
        ->get();
}

// 4. Cek Kursus LMS (LmsCourse)
$lmsCourses = [];
if (Schema::hasTable('lms_courses')) {
    $query = LmsCourse::query();
    if (!empty($teacherIds)) {
        $query->whereIn('teacher_id', $teacherIds);
    }
    if (!empty($userIds) && Schema::hasColumn('lms_courses', 'created_by')) {
        $query->orWhereIn('created_by', $userIds);
    }
    $lmsCourses = $query->with(['subject', 'classroom', 'modules'])->get();
}

// 5. Cek Modul LMS (LmsModule)
$courseIds = !empty($lmsCourses) ? $lmsCourses->pluck('id')->toArray() : [];
$lmsModules = [];
if (Schema::hasTable('lms_modules') && !empty($courseIds)) {
    $lmsModules = LmsModule::whereIn('course_id', $courseIds)->with('materials', 'assignments')->get();
}

// Cek apakah ada materi yang di-create langsung oleh user/teacher
$directMaterials = [];
if (Schema::hasTable('lms_materials')) {
    $matQuery = DB::table('lms_materials');
    $hasCreatedBy = Schema::hasColumn('lms_materials', 'created_by');
    $hasAuthorId = Schema::hasColumn('lms_materials', 'author_id');
    
    if ($hasCreatedBy && !empty($userIds)) {
        $matQuery->whereIn('created_by', $userIds);
    } elseif ($hasAuthorId && !empty($userIds)) {
        $matQuery->whereIn('author_id', $userIds);
    } else {
        $matQuery = null;
    }
    if ($matQuery) {
        $directMaterials = $matQuery->get();
    }
}

// 6. Cek CBT Bank Soal
$cbtBanks = [];
if (Schema::hasTable('cbt_question_banks')) {
    $cbtQuery = DB::table('cbt_question_banks');
    if (Schema::hasColumn('cbt_question_banks', 'teacher_id') && !empty($teacherIds)) {
        $cbtQuery->whereIn('teacher_id', $teacherIds);
    } elseif (Schema::hasColumn('cbt_question_banks', 'created_by') && !empty($userIds)) {
        $cbtQuery->whereIn('created_by', $userIds);
    } else {
        $cbtQuery = null;
    }
    if ($cbtQuery) {
        $cbtBanks = $cbtQuery->get();
    }
}

// 7. Cek Riwayat Presensi Mengajar Guru (Teaching Absences / Teacher Attendance)
$teachingRecords = [];
if (Schema::hasTable('teacher_attendances') && !empty($teacherIds)) {
    $teachingRecords = DB::table('teacher_attendances')->whereIn('teacher_id', $teacherIds)->orderByDesc('id')->limit(10)->get();
} elseif (Schema::hasTable('teaching_absences') && !empty($teacherIds)) {
    $teachingRecords = DB::table('teaching_absences')->whereIn('teacher_id', $teacherIds)->orderByDesc('id')->limit(10)->get();
} elseif (Schema::hasTable('presensi_mengajar') && !empty($teacherIds)) {
    $teachingRecords = DB::table('presensi_mengajar')->whereIn('teacher_id', $teacherIds)->orderByDesc('id')->limit(10)->get();
}

// 8. Cek Aktivitas Login / Sesi Terakhir (jika ada)
$lastLoginInfo = null;
foreach ($users as $u) {
    if (isset($u->last_login_at)) {
        $lastLoginInfo = $u->last_login_at;
    }
}

// Ringkasan Keaktifan
$isLmsActive = count($lmsCourses) > 0 || count($directMaterials) > 0 || count($cbtBanks) > 0;
$hasTeachingAssignment = count($teachingAssignments) > 0;
$hasSchedule = count($schedules) > 0;

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'server_time' => now()->toDateTimeString(),
        'active_year' => $activeYear?->year_name ?? $activeYear?->name,
        'summary' => [
            'is_lms_active' => $isLmsActive,
            'courses_count' => count($lmsCourses),
            'materials_count' => count($directMaterials),
            'cbt_banks_count' => count($cbtBanks),
            'teaching_assignments_count' => count($teachingAssignments),
            'schedules_count' => count($schedules),
        ],
        'users' => $users,
        'teachers' => $teachers,
        'teaching_assignments' => $teachingAssignments,
        'schedules' => $schedules,
        'lms_courses' => $lmsCourses,
        'cbt_banks' => $cbtBanks,
    ], JSON_PRETTY_PRINT);
    exit;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Keaktifan LMS - Ibu Devi Asri Monica Halawa</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #e2e8f0; padding: 25px; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #1e293b, #334155); padding: 20px 25px; border-radius: 12px; margin-bottom: 20px; border-left: 6px solid #38bdf8; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #059669; color: #fff; }
        .badge-danger { background: #dc2626; color: #fff; }
        .badge-warning { background: #d97706; color: #fff; }
        .badge-info { background: #0284c7; color: #fff; }
        .card { background: #1e293b; border-radius: 10px; padding: 18px 22px; margin-bottom: 16px; border: 1px solid #334155; }
        .card h3 { margin-top: 0; color: #38bdf8; font-size: 16px; border-bottom: 1px solid #334155; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th { background: #0f172a; color: #94a3b8; text-align: left; padding: 8px 12px; font-weight: 600; }
        td { padding: 8px 12px; border-bottom: 1px solid #334155; }
        .status-box { padding: 15px 20px; border-radius: 8px; font-size: 15px; font-weight: 500; margin-bottom: 20px; }
        .status-active { background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; }
        .status-inactive { background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f1f5f9; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1 style="margin:0 0 8px 0; font-size:22px;">🔍 Audit Keaktifan Guru di LMS (Live Production Database)</h1>
        <p style="margin:0; color:#94a3b8; font-size:13px;">Target Subjek: <strong>Devi Asri Monica Halawa, S.Kom</strong> | Waktu Server: <?= now()->format('d M Y H:i:s') ?> WIB</p>
    </div>

    <!-- KESIMPULAN STATUS -->
    <?php if ($isLmsActive): ?>
        <div class="status-box status-active">
            ✅ <strong>STATUS LMS: AKTIF</strong> — Ditemukan rekam aktivitas pembuatan kursus/materi/bank soal di database LMS production.
        </div>
    <?php else: ?>
        <div class="status-box status-inactive">
            ❌ <strong>STATUS LMS: TIDAK AKTIF / BELUM AKTIF</strong> — Tidak ditemukan adanya riwayat pembuatan Kursus LMS, Modul KBM, Materi Digital, maupun Bank Soal CBT atas nama beliau di database production.
        </div>
    <?php endif; ?>

    <!-- 1. DATA PENGGUNA & GURU -->
    <div class="card">
        <h3>1. Profil Akun & Data Guru</h3>
        <?php if ($teachers->isEmpty() && $users->isEmpty()): ?>
            <p style="color:#f87171;">Data guru / user dengan nama atau NUPTK tersebut tidak ditemukan di database production.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Tipe Data</th>
                    <th>Nama Lengkap</th>
                    <th>Email / Username</th>
                    <th>Sekolah</th>
                    <th>NUPTK / Status</th>
                </tr>
                <?php foreach ($teachers as $t): ?>
                    <tr>
                        <td><span class="badge badge-info">Teacher Record</span></td>
                        <td><strong><?= htmlspecialchars($t->name) ?></strong> <code>ID: <?= $t->id ?></code></td>
                        <td><?= htmlspecialchars($t->user?->email ?? '-') ?></td>
                        <td><?= htmlspecialchars($t->school?->name ?? 'SMK Swasta Pemda Nias') ?></td>
                        <td>NUPTK: <code><?= htmlspecialchars($t->nuptk ?? '-') ?></code> | Status: <?= $t->is_active ? 'Aktif' : 'Non-Aktif' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><span class="badge badge-info">User Login</span></td>
                        <td><strong><?= htmlspecialchars($u->name) ?></strong> <code>ID: <?= $u->id ?></code></td>
                        <td><?= htmlspecialchars($u->email) ?> (<?= htmlspecialchars($u->username ?? '-') ?>)</td>
                        <td>School ID: <code><?= $u->school_id ?? '-' ?></code></td>
                        <td>Role: <span class="badge badge-warning"><?= htmlspecialchars($u->role) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- 2. KURSUS LMS -->
    <div class="card">
        <h3>2. Kursus LMS (LmsCourse) yang Dikelola</h3>
        <?php if (empty($lmsCourses) || count($lmsCourses) === 0): ?>
            <p style="color:#94a3b8; font-style:italic;">Nol (0) Kursus LMS. Beliau belum pernah membuat atau ditugaskan mengelola Kursus di modul LMS.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Kode & Nama Kursus</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas / Rombel</th>
                    <th>Jumlah Modul</th>
                </tr>
                <?php foreach ($lmsCourses as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c->title ?? $c->name ?? '-') ?></strong> <code><?= htmlspecialchars($c->course_code ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($c->subject?->name ?? '-') ?></td>
                        <td><?= htmlspecialchars($c->classroom?->class_name ?? '-') ?></td>
                        <td><span class="badge badge-info"><?= $c->modules?->count() ?? 0 ?> Modul</span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- 3. PENUGASAN MENGAJAR DI AKADEMIK -->
    <div class="card">
        <h3>3. Penugasan Mengajar (Teaching Assignments) di Sekolah</h3>
        <?php if (empty($teachingAssignments) || count($teachingAssignments) === 0): ?>
            <p style="color:#94a3b8; font-style:italic;">Tidak ada data penugasan mengajar resmi (TeachingAssignment) yang tercatat atas ID beliau.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Tahun Pelajaran</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($teachingAssignments as $ta): ?>
                    <tr>
                        <td><?= htmlspecialchars($ta->academicYear?->name ?? $ta->academicYear?->year_name ?? '-') ?></td>
                        <td><strong><?= htmlspecialchars($ta->subject?->name ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($ta->classroom?->class_name ?? '-') ?></td>
                        <td><span class="badge badge-success">Terdaftar</span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- 4. JADWAL KBM & PRESENSI MENGAJAR -->
    <div class="card">
        <h3>4. Jadwal Pelajaran (Schedules) & Presensi Mengajar</h3>
        <?php if (empty($schedules) || count($schedules) === 0): ?>
            <p style="color:#94a3b8; font-style:italic;">Tidak ada jadwal mengajar mingguan (Schedules) yang terdata untuk beliau di jadwal KBM sekolah.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Hari & Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                </tr>
                <?php foreach ($schedules as $s): ?>
                    <tr>
                        <td>Hari: <?= htmlspecialchars($s->day ?? '-') ?> (Jam ke-<?= $s->period ?? '-' ?>)</td>
                        <td><strong><?= htmlspecialchars($s->subject?->name ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($s->classroom?->class_name ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- 5. BANK SOAL CBT / KUIS -->
    <div class="card">
        <h3>5. Bank Soal CBT / Ujian Online</h3>
        <?php if (empty($cbtBanks) || count($cbtBanks) === 0): ?>
            <p style="color:#94a3b8; font-style:italic;">Nol (0) Bank Soal CBT. Beliau belum pernah membuat bank soal atau ujian berbasis komputer (CBT) di sistem.</p>
        <?php else: ?>
            <p><span class="badge badge-success"><?= count($cbtBanks) ?> Bank Soal ditemukan</span></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
