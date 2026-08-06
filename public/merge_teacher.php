<?php
/**
 * Script Standalone Penggabungan Akun Guru Lintas Unit (Multi-School Teacher Merger)
 * 
 * Akses Preview (Dry Run):
 * https://perguruanpembda.com/merge_teacher.php?secret=pembda99&nuptk=7647767668130260&dry_run=1
 * 
 * Eksekusi Riil (Simpan ke DB):
 * https://perguruanpembda.com/merge_teacher.php?secret=pembda99&nuptk=7647767668130260&dry_run=0
 */

// Security Check
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

// Boot Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use App\Models\Teacher;
use App\Models\User;
use App\Models\School;

$nuptkQuery = trim($_GET['nuptk'] ?? '7647767668130260');
$nameQuery = trim($_GET['name'] ?? 'SOLIDARMAN');
$dryRun = ($_GET['dry_run'] ?? '1') !== '0';

echo "<!DOCTYPE html><html><head><title>Merger Akun Guru Lintas Unit - PembdaHUB</title>";
echo "<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 24px; line-height: 1.6; }
    .card { background: #1e293b; border: 2px solid #334155; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); }
    h1 { color: #38bdf8; font-size: 24px; margin-top: 0; }
    h2 { color: #facc15; font-size: 18px; border-bottom: 1px solid #475569; padding-bottom: 8px; }
    .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
    .badge-primary { background: #0284c7; color: #fff; }
    .badge-secondary { background: #eab308; color: #000; }
    .badge-dry { background: #f97316; color: #fff; }
    .badge-real { background: #22c55e; color: #fff; }
    .ok { color: #4ade80; font-weight: bold; }
    .warn { color: #facc15; font-weight: bold; }
    .err { color: #f87171; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #334155; font-size: 14px; }
    th { background: #0f172a; color: #94a3b8; }
    a.btn { display: inline-block; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-right: 10px; font-size: 14px; }
    .btn-green { background: #22c55e; color: #fff; }
    .btn-yellow { background: #eab308; color: #000; }
</style></head><body>";

echo "<div class='card'>";
echo "<h1>🏫 Tool Penggabungan Akun Guru Lintas Unit (Multi-School Teacher Merger)</h1>";
echo "<p>Mode Eksekusi: " . ($dryRun ? "<span class='badge badge-dry'>DRY RUN / PREVIEW (Tanpa Mengubah DB)</span>" : "<span class='badge badge-real'>REAL EXECUTION (Mengubah DB)</span>") . "</p>";
echo "</div>";

// Search for Teachers matching Kode/NUPTK or Name
$teachers = Teacher::with(['school', 'user'])
    ->where(function ($q) use ($nuptkQuery, $nameQuery) {
        if ($nuptkQuery) {
            $q->where('teacher_code', 'LIKE', "%{$nuptkQuery}%")
              ->orWhereHas('user', function ($uq) use ($nuptkQuery) {
                  $uq->where('username', 'LIKE', "%{$nuptkQuery}%")
                     ->orWhere('email', 'LIKE', "%{$nuptkQuery}%");
              });
        }
        if ($nameQuery) {
            $q->orWhere('full_name', 'LIKE', "%{$nameQuery}%")
              ->orWhereHas('user', function ($uq) use ($nameQuery) {
                  $uq->where('name', 'LIKE', "%{$nameQuery}%");
              });
        }
    })
    ->get();

if ($teachers->isEmpty()) {
    echo "<div class='card'>";
    echo "<p class='err'>❌ Tidak ditemukan data guru dengan kode/NUPTK '{$nuptkQuery}' atau nama '{$nameQuery}'.</p>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='card'>";
echo "<h2>1. Data Akun Guru Ditemukan (" . $teachers->count() . " Akun)</h2>";
echo "<table>";
echo "<tr><th>Teacher ID</th><th>User ID</th><th>Nama Lengkap</th><th>Kode/NUPTK</th><th>Unit Sekolah</th><th>Status</th><th>Role</th></tr>";

$primaryTeacher = null;
$secondaryTeachers = collect();

foreach ($teachers as $t) {
    $schoolName = $t->school ? $t->school->name : 'Tanpa Sekolah';
    $isSmk = str_contains(strtoupper($schoolName), 'SMK');
    
    if ($isSmk && !$primaryTeacher) {
        $primaryTeacher = $t;
    } else {
        $secondaryTeachers->push($t);
    }

    echo "<tr>";
    echo "<td><strong>#{$t->id}</strong></td>";
    echo "<td>" . ($t->user ? "#{$t->user->id} ({$t->user->email})" : "<span class='err'>Tidak ada User</span>") . "</td>";
    echo "<td>{$t->full_name}</td>";
    echo "<td>" . ($t->teacher_code ?: '-') . "</td>";
    echo "<td><strong>{$schoolName}</strong></td>";
    echo "<td>" . ($t->is_active ? "<span class='ok'>Aktif</span>" : "<span class='warn'>Non-Aktif</span>") . "</td>";
    echo "<td>" . ($t->user ? $t->user->role : '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

if (!$primaryTeacher) {
    // Fallback: pick the first one as primary
    $primaryTeacher = $teachers->first();
    $secondaryTeachers = $teachers->slice(1);
}

echo "<div style='margin-top: 16px; padding: 16px; background: #0f172a; border-radius: 12px;'>";
echo "<p>🎯 <strong>Akun Utama (Target):</strong> <span class='badge badge-primary'>Teacher #{$primaryTeacher->id}</span> - {$primaryTeacher->full_name} (<strong>" . ($primaryTeacher->school ? $primaryTeacher->school->name : '-') . "</strong>)</p>";

foreach ($secondaryTeachers as $sec) {
    echo "<p>🔄 <strong>Akun Sekunder (Akan Digabung):</strong> <span class='badge badge-secondary'>Teacher #{$sec->id}</span> - {$sec->full_name} (<strong>" . ($sec->school ? $sec->school->name : '-') . "</strong>)</p>";
}
echo "</div>";
echo "</div>";

// Analyze records to migrate
echo "<div class='card'>";
echo "<h2>2. Analisis Data yang Akan Dipindahkan ke Akun Utama</h2>";

foreach ($secondaryTeachers as $sec) {
    $secSchoolId = $sec->school_id;

    $assignments = DB::table('teaching_assignments')->where('teacher_id', $sec->id)->count();
    $schedules = DB::table('schedules')->where('teacher_id', $sec->id)->count();
    $lmsCourses = DB::table('lms_courses')->where('teacher_id', $sec->id)->count();
    $cbtBanks = DB::table('cbt_question_banks')->where('teacher_id', $sec->id)->count();
    $cbtExams = DB::table('cbt_exams')->where('teacher_id', $sec->id)->count();
    $homerooms = DB::table('classrooms')->where('homeroom_teacher_id', $sec->id)->count();
    $knowledge = DB::table('knowledge_materials')->where('teacher_id', $sec->id)->count();
    $pklMon = DB::table('pkl_monitorings')->where('teacher_id', $sec->id)->count();
    $grades = DB::table('grades')->where('teacher_id', $sec->id)->count();

    echo "<p><strong>Dari Guru #{$sec->id} (" . ($sec->school ? $sec->school->name : '-') . "):</strong></p>";
    echo "<ul>";
    echo "<li>Tugas Mengajar (Teaching Assignments): <strong>{$assignments}</strong> entri</li>";
    echo "<li>Jadwal Mengajar (Schedules): <strong>{$schedules}</strong> entri</li>";
    echo "<li>Course LMS: <strong>{$lmsCourses}</strong> entri</li>";
    echo "<li>Bank Soal CBT: <strong>{$cbtBanks}</strong> entri</li>";
    echo "<li>Ujian CBT: <strong>{$cbtExams}</strong> entri</li>";
    echo "<li>Wali Kelas: <strong>{$homerooms}</strong> kelas</li>";
    echo "<li>Materi Pengetahuan/Knowledge: <strong>{$knowledge}</strong> entri</li>";
    echo "<li>Monitoring PKL: <strong>{$pklMon}</strong> entri</li>";
    echo "<li>Nilai Siswa (Grades): <strong>{$grades}</strong> entri</li>";
    echo "</ul>";
}
echo "</div>";

// Perform Migration if not Dry Run
if ($dryRun) {
    echo "<div class='card' style='border-color: #eab308;'>";
    echo "<h2>3. Mode Simulasi / Preview</h2>";
    echo "<p class='warn'>⚠️ Saat ini Anda berada dalam mode SIMULASI (Dry Run). Belum ada perubahan yang disimpan ke database.</p>";
    echo "<p>Untuk melakukan penggabungan secara permanen di database, silakan klik tombol di bawah ini:</p>";
    echo "<a href='?secret=pembda99&nuptk={$nuptkQuery}&dry_run=0' class='btn btn-green' onclick=\"return confirm('Apakah Anda yakin ingin menggabungkan akun ini secara permanen?')\">🚀 LAKUKAN PENGGABUNGAN SEKARANG (REAL EXECUTION)</a>";
    echo "</div>";
} else {
    echo "<div class='card' style='border-color: #22c55e;'>";
    echo "<h2>3. Proses Eksekusi Penggabungan Akun...</h2>";

    try {
        DB::beginTransaction();

        foreach ($secondaryTeachers as $sec) {
            $priId = $primaryTeacher->id;
            $secId = $sec->id;
            $secSchoolId = $sec->school_id;

            // 1. Relink Teaching Assignments
            DB::table('teaching_assignments')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);

            // 2. Relink Schedules
            DB::table('schedules')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);

            // 3. Relink LMS Courses
            DB::table('lms_courses')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);

            // 4. Relink CBT Question Banks & Exams
            DB::table('cbt_question_banks')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);
            DB::table('cbt_exams')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);

            // 5. Relink Homeroom Classrooms
            DB::table('classrooms')->where('homeroom_teacher_id', $secId)->update(['homeroom_teacher_id' => $priId]);

            // 6. Relink Knowledge Materials
            DB::table('knowledge_materials')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);

            // 7. Relink PKL Monitoring & Placements
            DB::table('pkl_monitorings')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);
            DB::table('pkl_placements')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);

            // 8. Relink Grades
            DB::table('grades')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);
            DB::table('final_grades')->where('teacher_id', $secId)->update(['teacher_id' => $priId]);

            // 9. Relink User Reputation Logs if user exists
            if ($sec->user_id && $primaryTeacher->user_id) {
                DB::table('reputation_logs')->where('user_id', $sec->user_id)->update(['user_id' => $primaryTeacher->user_id]);
            }

            // 10. Relink Competencies (subject_teacher)
            $secSubjects = DB::table('subject_teacher')->where('teacher_id', $secId)->pluck('subject_id');
            foreach ($secSubjects as $subId) {
                DB::table('subject_teacher')->updateOrInsert(
                    ['teacher_id' => $priId, 'subject_id' => $subId]
                );
            }

            // 11. Add Secondary School to Pivot teacher_schools for Primary Teacher
            if ($secSchoolId && $secSchoolId != $primaryTeacher->school_id) {
                DB::table('teacher_schools')->updateOrInsert(
                    ['teacher_id' => $priId, 'school_id' => $secSchoolId],
                    ['is_primary' => false, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // 12. Deactivate Secondary Teacher
            DB::table('teachers')->where('id', $secId)->update([
                'is_active' => false,
            ]);

            // 13. Deactivate Secondary User
            if ($sec->user_id && $sec->user_id != $primaryTeacher->user_id) {
                DB::table('users')->where('id', $sec->user_id)->update([
                    'is_active' => false,
                    'email' => DB::raw("CONCAT(email, '.merged_', id)"),
                ]);
            }
        }

        DB::commit();

        echo "<p class='ok'>✅ PENGGABUNGAN AKUN BERHASIL LENGKAP!</p>";
        echo "<ul>";
        echo "<li>Akun Utama: <strong>{$primaryTeacher->full_name}</strong> (#{$primaryTeacher->id})</li>";
        echo "<li>Email/User Utama: <strong>" . ($primaryTeacher->user ? $primaryTeacher->user->email : '-') . "</strong></li>";
        echo "<li>Unit Utama: <strong>" . ($primaryTeacher->school ? $primaryTeacher->school->name : '-') . "</strong></li>";
        echo "<li>Unit Tambahan yang Dihubungkan: <strong>" . $secondaryTeachers->map(fn($t) => $t->school ? $t->school->name : '')->filter()->implode(', ') . "</strong></li>";
        echo "</ul>";
        echo "<p class='ok'>Seluruh tugas mengajar, jadwal, course LMS, dan soal CBT dari unit tambahan telah aman dipindahkan ke akun utama ini.</p>";

    } catch (\Exception $e) {
        DB::rollBack();
        echo "<p class='err'>❌ GAGAL MELAKUKAN PENGGABUNGAN AKUN: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    echo "</div>";
}

echo "</body></html>";
