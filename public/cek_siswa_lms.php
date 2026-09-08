<?php
/**
 * Script bersihkan ghost enrollment LMS + tampilkan course yang TIDAK bisa dilihat siswa
 * Akses: perguruanpembda.com/cek_siswa_lms.php?secret=pembda99&nama=Joy+Wise&kelas=Teknik+Rekayasa
 */
$secret = $_GET["secret"] ?? "";
if ($secret !== "pembda99") { die("Unauthorized"); }

require_once __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\Student;
use App\Models\Classroom;
use App\Models\LmsCourse;
use App\Models\LmsClass;
use App\Models\LmsEnrollment;
use App\Models\AcademicYear;
use App\Services\VocationalMajorFilterService;
use App\Services\LmsEnrollmentService;
use Illuminate\Support\Facades\DB;

header("Content-Type: text/html; charset=utf-8");

$searchName  = $_GET["nama"]     ?? "Joy Wise";
$classSearch = $_GET["kelas"]    ?? "Teknik Rekayasa";
$doSync      = isset($_GET["sync"]) && $_GET["sync"] === "1";
$doClean     = isset($_GET["clean"]) && $_GET["clean"] === "1";
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Diagnosa LMS Siswa</title>
<style>
*{box-sizing:border-box}body{font-family:Segoe UI,sans-serif;padding:24px;background:#f1f5f9;color:#1e293b}
h1{font-size:20px;margin-bottom:4px}h2{font-size:15px;color:#334155;margin:20px 0 8px;border-bottom:2px solid #e2e8f0;padding-bottom:6px}
.card{background:white;border-radius:10px;padding:14px 18px;margin:8px 0;box-shadow:0 1px 4px rgba(0,0,0,.08)}
.ok{border-left:5px solid #16a34a}.warn{border-left:5px solid #d97706}.danger{border-left:5px solid #dc2626}.info{border-left:5px solid #2563eb}
table{width:100%;border-collapse:collapse;font-size:12px}th{background:#1e293b;color:white;padding:7px 10px;text-align:left}
td{padding:7px 10px;border-bottom:1px solid #e2e8f0}tr:last-child td{border-bottom:none}
tr.missing td{background:#fff5f5}
.badge{display:inline-block;padding:2px 7px;border-radius:99px;font-size:11px;font-weight:bold;margin:1px}
.g{background:#dcfce7;color:#166534}.r{background:#fee2e2;color:#991b1b}.y{background:#fef9c3;color:#854d0e}.b{background:#dbeafe;color:#1d4ed8}
.btn{display:inline-block;padding:7px 16px;border-radius:6px;color:white;text-decoration:none;font-size:12px;margin:3px}
.btn-green{background:#16a34a}.btn-red{background:#dc2626}.btn-blue{background:#2563eb}
code{background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:11px}
small{font-size:11px;color:#64748b}
</style></head><body>
<h1>&#128269; Diagnosa LMS: Siswa Tidak Bisa Melihat Course Guru</h1>
<p style="color:#64748b;font-size:12px">Params: <code>?secret=pembda99&nama=Joy+Wise&kelas=Teknik+Rekayasa&sync=1&clean=1</code></p>
<?php
$activeYear = AcademicYear::where("is_active", true)->first();
echo "<div class=\"card info\"><strong>Tahun Ajaran Aktif:</strong> " . ($activeYear ? htmlspecialchars($activeYear->year_name ?? $activeYear->name ?? "ID:".$activeYear->id) : "<span style=color:red>TIDAK ADA</span>") . "</div>";

// ========== BERSIHKAN GHOST ENROLLMENT ==========
if ($doClean) {
    echo "<div class=\"card ok\"><strong>&#128465; Membersihkan ghost enrollment...</strong><br><br>";
    // Hapus enrollment yang course-nya sudah deleted (lms_class tidak ada atau course tidak ada)
    $deleted = DB::table("lms_enrollments")
        ->whereNotExists(function($q) {
            $q->select(DB::raw(1))->from("lms_classes")->whereColumn("lms_classes.id","lms_enrollments.lms_class_id");
        })->get();
    $cnt = 0;
    foreach ($deleted as $d) {
        DB::table("lms_enrollments")->where("id", $d->id)->delete();
        $cnt++;
    }
    echo "&#10003; Ghost enrollment (kelas dihapus): <strong>{$cnt}</strong> dihapus<br>";

    // Hapus enrollment yang course (via lms_class) tidak ada
    $deleted2 = DB::table("lms_enrollments")
        ->join("lms_classes","lms_enrollments.lms_class_id","=","lms_classes.id")
        ->whereNotExists(function($q) {
            $q->select(DB::raw(1))->from("lms_courses")->whereColumn("lms_courses.id","lms_classes.course_id");
        })->get();
    $cnt2 = 0;
    foreach ($deleted2 as $d) {
        DB::table("lms_enrollments")->where("id", $d->id)->delete();
        $cnt2++;
    }
    echo "&#10003; Ghost enrollment (course dihapus): <strong>{$cnt2}</strong> dihapus<br>";

    echo "<br>&#10003; Selesai! Refresh tanpa clean=1 untuk verifikasi.</div>";
}

// ========== 1. DATA SISWA ==========
echo "<h2>1. Data Siswa: \"{$searchName}\"</h2>";
$students = Student::where(function($q) use ($searchName) {
    $q->where("full_name","like","%{$searchName}%")->orWhereHas("user",fn($u)=>$u->where("name","like","%{$searchName}%"));
})->with(["user","classrooms","school"])->get();

if ($students->isEmpty()) {
    echo "<div class=\"card danger\">&#10060; Tidak ada siswa ditemukan</div>";
} else {
    foreach ($students as $student) {
        $all   = $student->classrooms;
        $regs  = $all->filter(fn($c) => !$c->is_combined && $c->class_type !== "gabungan");
        $regsAktif = $regs->filter(fn($c) => $activeYear && $c->pivot?->academic_year_id == $activeYear->id);
        $regsLama  = $regs->filter(fn($c) => !$activeYear || $c->pivot?->academic_year_id != $activeYear->id);

        echo "<div class=\"card\">";
        echo "<strong>&#128100; ".htmlspecialchars($student->full_name??"N/A")."</strong>";
        echo " <code>ID:{$student->id}</code> <code>school_id:".($student->school_id??"NULL")."</code>";
        echo $student->user ? " <span class=\"badge g\">&#10003; user</span>" : " <span class=\"badge r\">&#10007; NO USER</span>";
        echo "<br><br><strong>Kelas ({$all->count()} total):</strong><br>";
        foreach ($all as $cls) {
            $isComb = $cls->is_combined || $cls->class_type === "gabungan";
            $piv = $cls->pivot;
            $isAktifTP = $activeYear && $piv && $piv->academic_year_id == $activeYear->id;
            $tpLabel = $isAktifTP ? "<span class=\"badge g\">TP AKTIF</span>" : "<span class=\"badge\" style=\"background:#e2e8f0;color:#64748b\">TP lama</span>";
            $type = $isComb ? "<span class=\"badge y\">GABUNGAN</span>" : "<span class=\"badge g\">REGULER</span>";
            echo "&nbsp;&bull;&nbsp;".htmlspecialchars($cls->class_name)." {$type} {$tpLabel} <code>ID:{$cls->id}</code><br>";
        }
        echo "<br><strong>Kelas Reguler TP Aktif (penentu jurusan):</strong> ";
        if ($regsAktif->isNotEmpty()) {
            echo $regsAktif->map(fn($c)=>"<span class=\"badge g\">".htmlspecialchars($c->class_name)."</span>")->implode(" ");
        } elseif ($regsLama->isNotEmpty()) {
            $namaLama = $regsLama->map(fn($c)=>htmlspecialchars($c->class_name))->implode(", ");
            echo "<span class=\"badge r\">&#10007; Tidak ada di TP aktif</span> &mdash; sistem memakai TP lama: <em>{$namaLama}</em>";
            echo "<br><small>&#9888; Jika jurusan TP lama berbeda dengan mapel guru, enrollment mapel kejuruan terhambat.</small>";
        } else {
            echo "<span class=\"badge r\">&#10007; TIDAK ADA kelas reguler!</span>";
        }
        if ($all->count() > 1) {
            $lamaStr = $all->filter(fn($c) => !$activeYear || $c->pivot?->academic_year_id != $activeYear->id)->map(fn($c)=>htmlspecialchars($c->class_name))->implode(", ");
            echo "<br><div style=\"background:#fffbeb;border:1px solid #fde68a;border-radius:5px;padding:7px 10px;font-size:11px;color:#78350f;margin-top:7px\">";
            echo "&#128218; <strong>Ada {$all->count()} kelas?</strong> Normal — riwayat kelas TP lama tidak dihapus saat naik kelas. TP lama: <em>{$lamaStr}</em>";
            echo "</div>";
        }
        echo "</div>";
    }
}

// ========== 2. INFO KELAS ==========
echo "<h2>2. Info Kelas: \"{$classSearch}\"</h2>";
$classrooms = Classroom::where("class_name","like","%{$classSearch}%")
    ->when($activeYear, fn($q) => $q->where("academic_year_id", $activeYear->id))
    ->get();
if ($classrooms->isEmpty()) {
    echo "<div class=\"card warn\">&#9888; Kelas tidak ditemukan</div>";
} else {
    foreach ($classrooms as $cls) {
        $isComb = $cls->is_combined || $cls->class_type === "gabungan";
        $siswa  = $cls->students()->where("student_classes.status","aktif")->when($activeYear,fn($q)=>$q->where("student_classes.academic_year_id",$activeYear->id))->count();
        $lmsCount = LmsClass::where("classroom_id",$cls->id)->count();
        echo "<div class=\"card\">";
        echo "<strong>".htmlspecialchars($cls->class_name)."</strong> ".($isComb?"<span class=\"badge y\">GABUNGAN</span>":"<span class=\"badge g\">REGULER</span>");
        echo " <code>ID:{$cls->id}</code> <span class=\"badge b\">&#128101; {$siswa} siswa</span> <span class=\"badge ".($lmsCount>0?"g":"r")."\">&#128218; {$lmsCount} course</span>";
        echo "</div>";
    }
}

// ========== 3. COURSE & ENROLLMENT PER SISWA (yang BISA + TIDAK BISA) ==========
echo "<h2>3. Course yang Bisa dan TIDAK Bisa Dilihat Siswa</h2>";
$targetClassroom = $classrooms->where(fn($c) => str_contains($c->class_name, "XI") || !str_contains($c->class_name, "X "))->first() ?? $classrooms->first();

foreach ($students->take(3) as $student) {
    $enrolledIds = LmsEnrollment::where("student_id",$student->id)
        ->join("lms_classes","lms_enrollments.lms_class_id","=","lms_classes.id")
        ->pluck("lms_classes.course_id")
        ->unique()->toArray();

    echo "<div class=\"card\">";
    echo "<strong>&#128100; ".htmlspecialchars($student->full_name??"N/A")."</strong> (ID:{$student->id})<br><br>";

    // Tampilkan per kelas gabungan yang relevan
    foreach ($classrooms as $cls) {
        $lmsClasses = LmsClass::where("classroom_id",$cls->id)->with(["course.teacher.user","course.subject"])->get();
        if ($lmsClasses->isEmpty()) continue;

        echo "<strong>Kelas: ".htmlspecialchars($cls->class_name)."</strong><br>";
        echo "<table><tr><th>ID</th><th>Course</th><th>Guru</th><th>Mapel</th><th>Jurusan</th><th>Status Siswa</th></tr>";
        foreach ($lmsClasses as $lc) {
            $c = $lc->course;
            if (!$c) continue;
            $enrolled = in_array($c->id, $enrolledIds);
            $mapelName = $c->subject?->subject_name ?? $c->subject?->name ?? "?";
            $guru = $c->teacher?->user?->name ?? "?";

            // Deteksi apakah mapel kejuruan
            $vocKw = VocationalMajorFilterService::getSubjectMajorKeywords($mapelName, $c->subject?->code, $c->course_name);
            $vocLabel = $vocKw ? "<span class=\"badge y\">".implode(",",$vocKw)."</span>" : "<span class=\"badge g\">UMUM</span>";

            $statusCell = $enrolled
                ? "<span class=\"badge g\">&#10003; TERDAFTAR</span>"
                : "<span class=\"badge r\">&#10007; TIDAK BISA LIHAT</span>";

            $rowStyle = $enrolled ? "" : " class=\"missing\"";
            echo "<tr{$rowStyle}>";
            echo "<td><code>{$c->id}</code></td>";
            echo "<td>".htmlspecialchars(mb_strimwidth($c->course_name,0,45,"..."))."</td>";
            echo "<td>".htmlspecialchars(explode(",",$guru)[0])."</td>";
            echo "<td>".htmlspecialchars($mapelName)."</td>";
            echo "<td>{$vocLabel}</td>";
            echo "<td>{$statusCell}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }

    // Ghost enrollment (N/A)
    $ghosts = LmsEnrollment::where("student_id",$student->id)
        ->join("lms_classes","lms_enrollments.lms_class_id","=","lms_classes.id")
        ->leftJoin("lms_courses","lms_classes.course_id","=","lms_courses.id")
        ->whereNull("lms_courses.id")
        ->count();
    if ($ghosts > 0) {
        echo "<div style=\"background:#fff5f5;border:1px solid #fecaca;border-radius:5px;padding:7px 10px;font-size:12px;color:#991b1b;margin-top:6px\">";
        echo "&#9888; <strong>{$ghosts} ghost enrollment</strong> (course dihapus tapi enrollment masih ada). Klik Bersihkan.";
        echo "</div>";
    }
    echo "</div>";
}

// ========== 4. AKSI ==========
echo "<h2>4. Aksi</h2>";
$baseUrl = "?secret=pembda99&nama=".urlencode($searchName)."&kelas=".urlencode($classSearch);
echo "<div class=\"card\">";
if ($doSync) {
    echo "<div style=\"background:#dcfce7;border-radius:6px;padding:10px;margin-bottom:10px\">";
    echo "<strong>&#128260; Re-sync hasil:</strong><br>";
    $svc = app(LmsEnrollmentService::class);
    foreach ($students as $s) {
        $n = $svc->syncStudentEnrollments($s);
        echo "&#10003; ".htmlspecialchars($s->full_name??"N/A")." → +{$n} enrollment baru<br>";
    }
    foreach ($classrooms as $cls) {
        $n = $svc->syncClassroomEnrollments($cls);
        echo "&#10003; Kelas ".htmlspecialchars($cls->class_name)." → +{$n} enrollment baru<br>";
    }
    echo "</div>";
}
echo "<a class=\"btn btn-green\" href=\"{$baseUrl}&sync=1\">&#128260; Re-Sync Enrollment</a>";
echo "<a class=\"btn btn-red\" href=\"{$baseUrl}&clean=1\">&#128465; Bersihkan Ghost Enrollment</a>";
echo "<br><br><small>&#9888; Ghost enrollment = siswa masih tercatat di course yang sudah dihapus.</small>";
echo "</div>";
?>
<hr style="border:1px solid #e2e8f0;margin:24px 0">
<p style="color:#94a3b8;font-size:11px">&#9888; <strong>Hapus file ini setelah selesai!</strong></p>
</body></html>
