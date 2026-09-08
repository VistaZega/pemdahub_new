<?php
/**
 * Script diagnostik: Siswa tidak bisa melihat LMS guru
 * Kasus: Joy Wise Harefa - XI Teknik Rekayasa (DPIB, TAV)
 * Akses: perguruanpembda.com/cek_siswa_lms.php?secret=pembda99
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

header("Content-Type: text/html; charset=utf-8");

$searchName  = $_GET["nama"]  ?? "Joy";
$classSearch = $_GET["kelas"] ?? "Teknik Rekayasa";
$doSync      = isset($_GET["sync"]) && $_GET["sync"] === "1";
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Diagnosa LMS Siswa</title>
<style>
*{box-sizing:border-box}body{font-family:Segoe UI,sans-serif;padding:24px;background:#f1f5f9;color:#1e293b}
h1{font-size:22px;margin-bottom:4px}h2{font-size:16px;color:#334155;margin:24px 0 8px;border-bottom:2px solid #e2e8f0;padding-bottom:6px}
.card{background:white;border-radius:10px;padding:16px 20px;margin:10px 0;box-shadow:0 1px 4px rgba(0,0,0,.08)}
.ok{border-left:5px solid #16a34a}.warn{border-left:5px solid #d97706}.danger{border-left:5px solid #dc2626}.info{border-left:5px solid #2563eb}
table{width:100%;border-collapse:collapse;margin-top:8px;font-size:13px}th{background:#1e293b;color:white;padding:8px 12px;text-align:left}
td{padding:8px 12px;border-bottom:1px solid #e2e8f0}tr:last-child td{border-bottom:none}
.badge{display:inline-block;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:bold;margin:1px}
.g{background:#dcfce7;color:#166534}.r{background:#fee2e2;color:#991b1b}.y{background:#fef9c3;color:#854d0e}.b{background:#dbeafe;color:#1d4ed8}
.btn{display:inline-block;padding:8px 18px;border-radius:6px;background:#16a34a;color:white;text-decoration:none;font-size:13px;margin:4px}
code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px}
</style></head><body>
<h1>&#128269; Diagnosa LMS: Siswa Tidak Bisa Melihat Course Guru</h1>
<p style="color:#64748b;font-size:13px">URL params: <code>?secret=pembda99&nama=Joy&kelas=Teknik+Rekayasa&sync=1</code></p>
<?php
$activeYear = AcademicYear::where("is_active", true)->first();
echo "<div class=\"card info\"><strong>Tahun Ajaran Aktif:</strong> " . ($activeYear ? htmlspecialchars($activeYear->year_name ?? $activeYear->name ?? "ID:".$activeYear->id) : "<span style=color:red>TIDAK ADA</span>") . "</div>";

// === 1. SISWA ===
echo "<h2>1. Data Siswa: \"{$searchName}\"</h2>";
$students = Student::where(function($q) use ($searchName) {
    $q->where("full_name","like","%{$searchName}%")->orWhereHas("user",fn($u)=>$u->where("name","like","%{$searchName}%"));
})->with(["user","classrooms","school"])->get();

if ($students->isEmpty()) {
    echo "<div class=\"card danger\">&#10060; Tidak ada siswa dengan nama <strong>{$searchName}</strong></div>";
} else {
    foreach ($students as $student) {
        $all  = $student->classrooms;
        $regs = $all->filter(fn($c) => !$c->is_combined && $c->class_type !== "gabungan");
        $comb = $all->filter(fn($c) => $c->is_combined || $c->class_type === "gabungan");
        echo "<div class=\"card\">";
        echo "<strong>&#128100; ".htmlspecialchars($student->full_name ?? $student->user?->name ?? "N/A")."</strong> &nbsp; <code>ID:{$student->id}</code> &nbsp; <code>school_id:".($student->school_id??'NULL')."</code>";
        echo $student->user ? " &nbsp; <span class=\"badge g\">&#10003; user_id:{$student->user_id}</span>" : " &nbsp; <span class=\"badge r\">&#10007; TIDAK ADA USER LOGIN</span>";
        echo "<br><br><strong>Semua Kelas:</strong><br>";
        if ($all->isEmpty()) {
            echo "<span class=\"badge r\">&#10007; Tidak ada kelas sama sekali</span>";
        } else {
            foreach ($all as $cls) {
                $isComb = $cls->is_combined || $cls->class_type === "gabungan";
                $typeBadge = $isComb ? "<span class=\"badge y\">GABUNGAN</span>" : "<span class=\"badge g\">REGULER</span>";
                $piv = $cls->pivot;
                $pivStatus = $piv ? "<span class=\"badge b\">".($piv->status??"?")."</span>" : "";
                $isAktifTP = $activeYear && $piv && $piv->academic_year_id == $activeYear->id;
                $tpLabel = $isAktifTP ? "<span class=\"badge g\">&#9733; TP AKTIF</span>" : "<span class=\"badge\" style=\"background:#e2e8f0;color:#64748b\">TP lama</span>";
                echo "&nbsp;&bull;&nbsp;".htmlspecialchars($cls->class_name)." {$typeBadge} {$pivStatus} {$tpLabel} <code>ID:{$cls->id}</code><br>";
            }
        }
        $regsAktif = $regs->filter(fn($c) => $activeYear && $c->pivot?->academic_year_id == $activeYear->id);
        $regsLama  = $regs->filter(fn($c) => !$activeYear || $c->pivot?->academic_year_id != $activeYear->id);
        echo "<br><strong>Kelas Reguler di TP Aktif (penentu jurusan sekarang):</strong> ";
        if ($regsAktif->isNotEmpty()) {
            echo $regsAktif->map(fn($c)=>"<span class=\"badge g\">".htmlspecialchars($c->class_name)."</span>")->implode(" ");
        } elseif ($regsLama->isNotEmpty()) {
            $namaLama = $regsLama->map(fn($c)=>"<em>".htmlspecialchars($c->class_name)."</em>")->implode(", ");
            echo "<span class=\"badge r\">&#10007; Tidak ada kelas reguler di TP aktif</span> &mdash; memakai TP lama: {$namaLama}";
            echo "<br><small style=\"color:#d97706\">&#9888; Filter jurusan berbasis kelas TP lama. Jika jurusan lama (misal: TE) berbeda dari mapel guru, enrollment bisa terhambat.</small>";
        } else {
            echo "<span class=\"badge r\">&#10007; TIDAK ADA kelas reguler sama sekali!</span>";
        }
        // Penjelasan kenapa ada >1 kelas
        if ($all->count() > 1) {
            $lamaStr = $all->filter(fn($c) => !$activeYear || $c->pivot?->academic_year_id != $activeYear->id)->map(fn($c)=>htmlspecialchars($c->class_name))->implode(", ");
            echo "<br><div style=\"background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:8px 12px;font-size:12px;color:#78350f;margin-top:8px\">";
            echo "&#128218; <strong>Kenapa ada {$all->count()} kelas?</strong> NORMAL. Sistem menyimpan riwayat kelas dari tahun sebelumnya (tidak dihapus saat naik kelas/promosi). Kelas TP lama: <em>{$lamaStr}</em>.";
            echo "</div>";
        }
        echo "</div>";
    }
}

// === 2. KELAS ===
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
        echo " &nbsp; <code>ID:{$cls->id}</code>";
        echo "<br><small style=color:#64748b>is_combined=".($cls->is_combined?"true":"false")." | class_type=".($cls->class_type??"NULL")." | academic_year_id=".($cls->academic_year_id??"NULL")."</small>";
        echo "<br><span class=\"badge b\">&#128101; {$siswa} siswa aktif</span> ";
        echo "<span class=\"badge ".($lmsCount>0?"g":"r")."\">&#128218; {$lmsCount} course LMS terhubung</span>";
        echo "</div>";
    }
}

// === 3. LMS COURSES ===
echo "<h2>3. LMS Courses pada Kelas \"{$classSearch}\"</h2>";
foreach ($classrooms as $cls) {
    $lmsClasses = LmsClass::where("classroom_id",$cls->id)->with(["course.teacher.user","course.subject"])->get();
    echo "<strong>".htmlspecialchars($cls->class_name)."</strong> <code>ID:{$cls->id}</code><br>";
    if ($lmsClasses->isEmpty()) {
        echo "<div class=\"card danger\">&#10060; TIDAK ADA course LMS yang terhubung ke kelas ini! Guru harus membuat/menambahkan course untuk rombel ini.</div>";
    } else {
        echo "<table><tr><th>Course ID</th><th>Nama Course</th><th>Guru</th><th>Mapel</th><th>Status</th><th>Enrollments</th></tr>";
        foreach ($lmsClasses as $lc) {
            $c = $lc->course;
            if (!$c) { echo "<tr><td colspan=6 style=color:red>Course deleted?</td></tr>"; continue; }
            $enrCount = LmsEnrollment::where("lms_class_id",$lc->id)->count();
            $guru = $c->teacher?->user?->name ?? "?";
            $mapel = $c->subject?->subject_name ?? $c->subject?->name ?? "?";
            $aktif = ($c->is_active && $c->is_published) ? "<span class=\"badge g\">Aktif/Published</span>" : "<span class=\"badge y\">".($c->is_active?"Active":"Inactive")."/".($c->is_published?"Published":"Draft")."</span>";
            echo "<tr><td><code>{$c->id}</code></td><td><strong>".htmlspecialchars($c->course_name)."</strong></td><td>".htmlspecialchars($guru)."</td><td>".htmlspecialchars($mapel)."</td><td>{$aktif}</td><td><span class=\"badge ".($enrCount>0?"g":"r")."\">{$enrCount} siswa</span></td></tr>";
        }
        echo "</table>";
    }
    echo "<br>";
}

// === 4. ENROLLMENT SISWA ===
echo "<h2>4. Enrollment LMS per Siswa</h2>";
foreach ($students as $student) {
    $enrs = LmsEnrollment::where("student_id",$student->id)
        ->with(["lmsClass.course.teacher.user","lmsClass.classroom","lmsClass.course.subject"])->get();
    echo "<div class=\"card\">";
    echo "<strong>&#128100; ".htmlspecialchars($student->full_name??"N/A")."</strong> (ID:{$student->id})<br><br>";
    if ($enrs->isEmpty()) {
        echo "<div style=\"padding:8px;background:#fee2e2;border-radius:6px;color:#991b1b\">&#10060; <strong>TIDAK ADA enrollment LMS sama sekali!</strong></div>";
    } else {
        echo "<table><tr><th>Course</th><th>Mapel</th><th>Guru</th><th>Rombel</th><th>Status</th></tr>";
        foreach ($enrs as $e) {
            $c = $e->lmsClass?->course;
            echo "<tr><td>".htmlspecialchars($c?->course_name??"N/A")." <code>ID:".($c?->id??"?")."</code></td>";
            echo "<td>".htmlspecialchars($c?->subject?->subject_name??$c?->subject?->name??"?")."</td>";
            echo "<td>".htmlspecialchars($c?->teacher?->user?->name??"?")."</td>";
            echo "<td>".htmlspecialchars($e->lmsClass?->classroom?->class_name??"?")."</td>";
            echo "<td><span class=\"badge g\">".htmlspecialchars($e->status)."</span></td></tr>";
        }
        echo "</table>";
    }
    echo "</div>";
}

// === 5. DIAGNOSA VOCATIONAL FILTER ===
echo "<h2>5. Diagnosa Filter Kejuruan (VocationalMajorFilterService)</h2>";
echo "<div class=\"card info\">";
echo "<p>Sistem menggunakan kelas <strong>reguler (non-gabungan)</strong> siswa untuk menentukan jurusan. Jika siswa hanya di kelas gabungan, filter akan menolak enrollment ke mapel kejuruan.</p>";
$tCls = $classrooms->first();
if ($tCls) {
    $isComb = $tCls->is_combined || $tCls->class_type === "gabungan";
    echo "<strong>Kelas \"".htmlspecialchars($tCls->class_name)."\":</strong> ";
    echo $isComb ? "<span class=\"badge y\">KELAS GABUNGAN</span> — siswa butuh kelas reguler untuk identifikasi jurusan" : "<span class=\"badge g\">KELAS REGULER</span>";
}
echo "</div>";

// === 6. AKSI ===
echo "<h2>6. Aksi Perbaikan</h2>";
if ($doSync) {
    echo "<div class=\"card ok\"><strong>&#128260; Memulai re-sync...</strong><br><br>";
    $svc = app(LmsEnrollmentService::class);
    foreach ($students as $s) {
        $n = $svc->syncStudentEnrollments($s);
        echo "&#10003; ".htmlspecialchars($s->full_name??"N/A")." → +{$n} enrollment<br>";
    }
    foreach ($classrooms as $cls) {
        $n = $svc->syncClassroomEnrollments($cls);
        echo "&#10003; Kelas ".htmlspecialchars($cls->class_name)." → +{$n} enrollment<br>";
    }
    echo "<br>&#10003; Selesai! Reload halaman ini tanpa sync=1 untuk verifikasi.</div>";
} else {
    $url = "?secret=pembda99&nama=".urlencode($searchName)."&kelas=".urlencode($classSearch)."&sync=1";
    echo "<div class=\"card\">";
    echo "<p>Jika teridentifikasi masalah, klik tombol ini untuk paksa re-sync enrollment:</p>";
    echo "<a class=\"btn\" href=\"{$url}\">&#128260; Paksa Re-Sync Enrollment Sekarang</a>";
    echo "<br><br><small style=color:#64748b>Akan mendaftarkan ulang semua siswa yang seharusnya bisa mengakses course.</small>";
    echo "</div>";
}
?>
<hr style="border:1px solid #e2e8f0;margin:32px 0">
<p style="color:#94a3b8;font-size:12px">&#9888; <strong>Hapus file ini setelah selesai!</strong> | <code>perguruanpembda.com/cek_siswa_lms.php?secret=pembda99</code></p>
</body></html>


