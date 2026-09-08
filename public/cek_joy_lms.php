<?php
/**
 * Diagnosa Komprehensif: Joy Wise Harefa & LMS Yulianus Zega
 * Jawab 4 pertanyaan sekaligus
 * Akses: perguruanpembda.com/cek_joy_lms.php?secret=pembda99
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
use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Services\VocationalMajorFilterService;
use App\Services\LmsEnrollmentService;
use Illuminate\Support\Facades\DB;

header("Content-Type: text/html; charset=utf-8");

$doSync  = isset($_GET["sync"])  && $_GET["sync"]  === "1";
$doFix   = isset($_GET["fix"])   && $_GET["fix"]   === "1";
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Diagnosa Joy LMS</title>
<style>
*{box-sizing:border-box}body{font-family:Segoe UI,sans-serif;padding:24px;background:#f1f5f9;color:#1e293b}
h1{font-size:20px;margin-bottom:4px}h2{font-size:15px;color:#fff;background:#1e293b;padding:8px 14px;border-radius:8px;margin:20px 0 8px}
.card{background:white;border-radius:10px;padding:14px 18px;margin:8px 0;box-shadow:0 1px 4px rgba(0,0,0,.08)}
.ok{border-left:5px solid #16a34a}.warn{border-left:5px solid #d97706}.danger{border-left:5px solid #dc2626}.info{border-left:5px solid #2563eb}
table{width:100%;border-collapse:collapse;font-size:12px;margin-top:6px}th{background:#334155;color:white;padding:7px 10px;text-align:left}
td{padding:7px 10px;border-bottom:1px solid #e2e8f0}tr:last-child td{border-bottom:none}tr.missing{background:#fff5f5}tr.ok-row{background:#f0fdf4}
.badge{display:inline-block;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:bold;margin:1px}
.g{background:#dcfce7;color:#166534}.r{background:#fee2e2;color:#991b1b}.y{background:#fef9c3;color:#854d0e}.b{background:#dbeafe;color:#1d4ed8}.gray{background:#e2e8f0;color:#475569}
.btn{display:inline-block;padding:8px 18px;border-radius:7px;color:white;text-decoration:none;font-size:12px;margin:3px;font-weight:bold}
.btn-green{background:#16a34a}.btn-blue{background:#2563eb}.btn-red{background:#dc2626}
code{background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:11px}
.alert-warn{background:#fffbeb;border:1px solid #fde68a;border-radius:7px;padding:10px 14px;font-size:12px;color:#78350f;margin:8px 0}
.alert-danger{background:#fff1f2;border:1px solid #fecdd3;border-radius:7px;padding:10px 14px;font-size:12px;color:#881337;margin:8px 0}
.alert-ok{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:10px 14px;font-size:12px;color:#14532d;margin:8px 0}
</style></head><body>
<h1>&#128269; Diagnosa Komprehensif: Joy Wise Harefa &amp; LMS Yulianus Zega</h1>
<?php
$activeYear = AcademicYear::where("is_active", true)->first();
echo "<div class=\"card info\"><strong>Tahun Ajaran Aktif:</strong> " . ($activeYear ? htmlspecialchars($activeYear->year_name ?? $activeYear->name ?? "ID:".$activeYear->id) : "<span style=color:red>TIDAK ADA</span>") . " (ID:{$activeYear?->id})</div>";

// ========================================================
// PERTANYAAN 1: Di kelas apa Joy di TP aktif?
// ========================================================
echo "<h2>&#10102; Di kelas apa Joy Wise Harefa di TP. 2026/2027?</h2>";

$joy = Student::where("full_name","like","%Joy Wise Harefa%")
    ->orWhereHas("user", fn($u) => $u->where("name","like","%Joy Wise Harefa%"))
    ->with(["user","classrooms","school"])
    ->first();

if (!$joy) {
    echo "<div class=\"card danger\">&#10060; Siswa Joy Wise Harefa tidak ditemukan</div>";
    echo "</body></html>"; exit;
}

echo "<div class=\"card\">";
echo "<strong>&#128100; " . htmlspecialchars($joy->full_name) . "</strong>";
echo " <code>ID:{$joy->id}</code> <code>school_id:{$joy->school_id}</code>";
echo $joy->user ? " <span class=\"badge g\">&#10003; user_id:{$joy->user_id}</span>" : " <span class=\"badge r\">&#10007; Tidak ada user login</span>";
echo "<br><br><table><tr><th>Nama Kelas</th><th>Tipe</th><th>Status</th><th>Tahun Pelajaran</th><th>Keterangan</th></tr>";

$allClasses = $joy->classrooms;
foreach ($allClasses as $cls) {
    $isComb = $cls->is_combined || $cls->class_type === "gabungan";
    $piv = $cls->pivot;
    $isAktif = $activeYear && $piv && $piv->academic_year_id == $activeYear->id;
    $kls_type = $isComb ? "<span class=\"badge y\">GABUNGAN</span>" : "<span class=\"badge g\">REGULER</span>";
    $tp_badge  = $isAktif ? "<span class=\"badge g\">&#9733; TP AKTIF</span>" : "<span class=\"badge gray\">TP lama (ID:{$piv?->academic_year_id})</span>";
    $st_badge  = "<span class=\"badge b\">".($piv->status??"?")."</span>";
    $ket = $isAktif ? ($isComb ? "Kelas aktif saat ini (gabungan)" : "Kelas aktif saat ini (reguler)") : "Riwayat tahun lalu";
    echo "<tr class=\"".($isAktif?"ok-row":"")."\"><td><strong>".htmlspecialchars($cls->class_name)."</strong> <code>ID:{$cls->id}</code></td><td>{$kls_type}</td><td>{$st_badge}</td><td>{$tp_badge}</td><td>{$ket}</td></tr>";
}
echo "</table></div>";

$joyActiveClass = $allClasses->filter(fn($c) => $activeYear && $c->pivot?->academic_year_id == $activeYear->id)->first();
$joyRegularActive = $allClasses->filter(fn($c) => $activeYear && $c->pivot?->academic_year_id == $activeYear->id && !$c->is_combined && $c->class_type !== "gabungan")->first();

if ($joyActiveClass) {
    echo "<div class=\"alert-ok\">&#10003; <strong>Jawaban:</strong> Di TP. 2026/2027, Joy terdaftar di kelas <strong>\"".htmlspecialchars($joyActiveClass->class_name)."\"</strong> (".($joyActiveClass->is_combined ? "Kelas Gabungan" : "Kelas Reguler").").</div>";
} else {
    echo "<div class=\"alert-danger\">&#10060; Joy TIDAK terdaftar di kelas manapun di TP. 2026/2027!</div>";
}

// ========================================================
// PERTANYAAN 2: Jurusan Joy dan sumber datanya
// ========================================================
echo "<h2>&#10103; Jurusan Joy dan data itu diambil dari mana?</h2>";

$regularClasses = $allClasses->filter(fn($c) => !$c->is_combined && $c->class_type !== "gabungan");
$regularActiveTP = $regularClasses->filter(fn($c) => $activeYear && $c->pivot?->academic_year_id == $activeYear->id);
$regularLamaTP   = $regularClasses->filter(fn($c) => !$activeYear || $c->pivot?->academic_year_id != $activeYear->id);

// Deteksi jurusan dari kelas reguler
$usedClasses = $regularActiveTP->isNotEmpty() ? $regularActiveTP : $regularLamaTP;
$detectedKeywords = null;
$detectedFromClass = null;
foreach ($usedClasses as $cls) {
    $kw = VocationalMajorFilterService::getSubjectMajorKeywords(null, null, $cls->class_name);
    if ($kw) {
        $detectedKeywords = $kw;
        $detectedFromClass = $cls;
        break;
    }
    // Jika tidak ada keyword dari nama kelas, siswa dianggap umum
    if (!$detectedKeywords) {
        $detectedFromClass = $cls;
    }
}

// Cek matching untuk mapel Konsentrasi Keahlian TE
$teKeywords = ["TE","TAV","ELEKTRONIKA","AUDIO"];
$isMatchingTE = VocationalMajorFilterService::isStudentMatchingVocationalSubject($joy, $teKeywords);

echo "<div class=\"card\">";
echo "<table><tr><th>Aspek</th><th>Detail</th></tr>";
echo "<tr><td><strong>Sumber data jurusan</strong></td><td>Diambil dari nama <strong>kelas REGULER</strong> (non-gabungan) siswa. Sistem TIDAK menggunakan field jurusan langsung di database.</td></tr>";
echo "<tr><td><strong>Kelas reguler TP aktif</strong></td><td>";
echo $regularActiveTP->isEmpty() ? "<span class=\"badge r\">&#10007; Tidak ada</span>" : $regularActiveTP->map(fn($c)=>"<span class=\"badge g\">".htmlspecialchars($c->class_name)."</span>")->implode(" ");
echo "</td></tr>";
echo "<tr><td><strong>Kelas reguler TP lama (fallback)</strong></td><td>";
echo $regularLamaTP->isEmpty() ? "<span class=\"badge gray\">Tidak ada</span>" : $regularLamaTP->map(fn($c)=>"<span class=\"badge y\">".htmlspecialchars($c->class_name)." (TP lama)</span>")->implode(" ");
echo "</td></tr>";
echo "<tr><td><strong>Kelas yang digunakan sistem sekarang</strong></td><td>";
echo $usedClasses->isEmpty() ? "<span class=\"badge r\">Tidak ada kelas reguler sama sekali</span>" : $usedClasses->map(fn($c)=>"<span class=\"badge ".($regularActiveTP->isNotEmpty()?"g":"y")."\">".htmlspecialchars($c->class_name)."</span>")->implode(" ");
echo "</td></tr>";
echo "<tr><td><strong>Keyword jurusan terdeteksi</strong></td><td>";
if ($detectedKeywords) {
    echo "<span class=\"badge b\">".implode(", ", $detectedKeywords)."</span>";
} elseif ($detectedFromClass) {
    echo "<span class=\"badge gray\">Tidak terdeteksi sebagai mapel kejuruan dari kelas \"".htmlspecialchars($detectedFromClass->class_name)."\"</span>";
} else {
    echo "<span class=\"badge r\">Tidak bisa terdeteksi — tidak ada kelas reguler</span>";
}
echo "</td></tr>";
echo "<tr><td><strong>Cocok untuk KK-TE / TAV?</strong></td><td>";
echo $isMatchingTE ? "<span class=\"badge g\">&#10003; YA — Joy cocok untuk mapel KK-TE/TAV</span>" : "<span class=\"badge r\">&#10007; TIDAK — Joy tidak cocok untuk mapel KK-TE/TAV menurut sistem</span>";
echo "</td></tr>";
echo "</table></div>";

if (!$isMatchingTE) {
    echo "<div class=\"alert-danger\">&#10060; <strong>PENYEBAB UTAMA:</strong> Sistem mendeteksi Joy bukan jurusan TE/TAV karena kelas regulernya adalah <em>\"".htmlspecialchars($detectedFromClass?->class_name??"tidak ada")."\"</em>. Keyword TE/TAV tidak ditemukan dalam nama kelas tersebut, sehingga Joy diblokir dari mapel Konsentrasi Keahlian TE.</div>";
} else {
    echo "<div class=\"alert-ok\">&#10003; <strong>Joy COCOK untuk KK-TE.</strong> Jika dia tidak bisa melihat course, penyebabnya bukan filter jurusan.</div>";
}

// ========================================================
// PERTANYAAN 3: Group Lab vs Kelas
// ========================================================
echo "<h2>&#10104; Group Blok SMK: Siapa di Lab dan siapa di Kelas?</h2>";

// Cari teaching assignment Yulianus Zega untuk kelas Teknik Rekayasa
$yuliTeacher = Teacher::whereHas("user", fn($u) => $u->where("name","like","%Yulianus%Zega%"))
    ->with("user")->first();

if (!$yuliTeacher) {
    echo "<div class=\"card warn\">&#9888; Guru Yulianus Zega tidak ditemukan di database.</div>";
} else {
    $assignments = TeachingAssignment::where("teacher_id", $yuliTeacher->id)
        ->when($activeYear, fn($q) => $q->where("academic_year_id", $activeYear->id))
        ->with(["subject","classroom"])
        ->where("is_active", true)
        ->get();

    echo "<div class=\"card\">";
    echo "<strong>&#128104;&#8205;&#127979; Guru: " . htmlspecialchars($yuliTeacher->user?->name ?? "Yulianus Zega") . "</strong><br><br>";

    if ($assignments->isEmpty()) {
        echo "<span class=\"badge r\">Tidak ada penugasan mengajar aktif di TP ini</span>";
    } else {
        echo "<table><tr><th>Kelas</th><th>Mapel</th><th>group_code</th><th>block_type</th><th>Block Type Label</th></tr>";
        foreach ($assignments as $ta) {
            $blockLabel = match($ta->block_type ?? "none") {
                "all"    => "<span class=\"badge b\">Kelompok A — Di Lab</span>",
                "split"  => "<span class=\"badge y\">Kelompok B — Di Kelas</span>",
                "parallel" => "<span class=\"badge gray\">Paralel/Agama</span>",
                default  => "<span class=\"badge g\">Reguler (semua siswa)</span>",
            };
            $gcBadge = $ta->group_code ? "<span class=\"badge b\">".htmlspecialchars($ta->group_code)."</span>" : "<span class=\"badge gray\">NULL</span>";
            echo "<tr>";
            echo "<td>".htmlspecialchars($ta->classroom?->class_name??"?")." <code>ID:{$ta->classroom_id}</code></td>";
            echo "<td>".htmlspecialchars($ta->subject?->subject_name??$ta->subject?->name??"?")."</td>";
            echo "<td>{$gcBadge}</td>";
            echo "<td><code>".($ta->block_type??"none")."</code></td>";
            echo "<td>{$blockLabel}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Cari assignment dengan group_code untuk ambil siswa A vs B
    $teAssignment = $assignments->first(fn($ta) => str_contains(strtolower($ta->classroom?->class_name??""), "teknik rekayasa") || str_contains(strtolower($ta->subject?->subject_name??$ta->subject?->name??""), "te") || str_contains(strtolower($ta->subject?->subject_name??$ta->subject?->name??""), "mikrokontroler"));

    if ($teAssignment && $teAssignment->group_code) {
        // Ambil semua TA dengan group_code yang sama
        $groupAssignments = TeachingAssignment::where("teacher_id", $yuliTeacher->id)
            ->where("group_code", $teAssignment->group_code)
            ->when($activeYear, fn($q) => $q->where("academic_year_id", $activeYear->id))
            ->with("classroom")
            ->get();

        echo "<br><strong>Pembagian Kelompok Blok (group_code: ".htmlspecialchars($teAssignment->group_code)."):</strong><br>";
        foreach ($groupAssignments as $gta) {
            $label = match($gta->block_type ?? "none") {
                "all"   => "&#127979; Lab (Kelompok A)",
                "split" => "&#128218; Kelas (Kelompok B)",
                default => "&#127775; Semua Siswa",
            };
            $siswa = $gta->classroom ? $gta->classroom->students()->where("student_classes.status","aktif")->when($activeYear,fn($q)=>$q->where("student_classes.academic_year_id",$activeYear->id))->count() : 0;
            echo "&bull; ".htmlspecialchars($gta->classroom?->class_name??"?")." ({$siswa} siswa) → {$label}<br>";
        }
    } elseif ($teAssignment) {
        $blockType = $teAssignment->block_type ?? "none";
        if ($blockType === "none") {
            echo "<br><div class=\"alert-ok\">&#10003; Penugasan ini <strong>tidak menggunakan sistem blok</strong> — semua siswa mendapat satu kelompok (tidak ada pembagian Lab/Kelas).</div>";
        } else {
            echo "<br><div class=\"alert-warn\">&#9888; Penugasan menggunakan block_type=<strong>{$blockType}</strong> tapi tidak ada group_code. Pembagian kelompok mungkin tidak lengkap.</div>";
        }
    }
    echo "</div>";
}

// ========================================================
// PERTANYAAN 4: Kenapa Joy tidak bisa lihat LMS Yulianus Zega (Teknik Rekayasa TE/TAV)
// ========================================================
echo "<h2>&#10105; Course LMS Yulianus Zega: \"Kosentrasi Keahlian TE\" di kelas X Teknik Rekayasa (TE, TAV)</h2>";

// Cari semua course Yulianus Zega
if ($yuliTeacher) {
    $yuliCourses = LmsCourse::where("teacher_id", $yuliTeacher->id)
        ->with(["subject","lmsClasses.classroom"])
        ->get();

    echo "<div class=\"card\">";
    echo "<strong>Semua course LMS milik Yulianus Zega (" . $yuliCourses->count() . " course):</strong><br><br>";

    if ($yuliCourses->isEmpty()) {
        echo "<span class=\"badge r\">&#10060; Yulianus Zega belum punya course LMS sama sekali!</span>";
    } else {
        echo "<table><tr><th>ID</th><th>Nama Course</th><th>Mapel</th><th>Rombel Terhubung</th><th>Total Enrollment</th><th>Joy Terdaftar?</th></tr>";

        $joyEnrolledCourseIds = LmsEnrollment::where("student_id", $joy->id)
            ->join("lms_classes","lms_enrollments.lms_class_id","=","lms_classes.id")
            ->pluck("lms_classes.course_id")
            ->unique()->toArray();

        foreach ($yuliCourses as $course) {
            $mapel = $course->subject?->subject_name ?? $course->subject?->name ?? "?";
            $rombels = $course->lmsClasses->pluck("classroom.class_name")->filter()->map(fn($n)=>"<span class=\"badge gray\">".htmlspecialchars($n)."</span>")->implode(" ");
            if (empty($rombels)) {
                $rombels = $course->classroom_id ? "<span class=\"badge y\">classroom_id:{$course->classroom_id} (cek manual)</span>" : "<span class=\"badge r\">&#10060; Belum ada rombel!</span>";
            }
            $totalEnroll = LmsEnrollment::whereHas("lmsClass", fn($q)=>$q->where("course_id",$course->id))->count();
            $joyIn = in_array($course->id, $joyEnrolledCourseIds);
            $joyBadge = $joyIn ? "<span class=\"badge g\">&#10003; TERDAFTAR</span>" : "<span class=\"badge r\">&#10007; TIDAK ADA</span>";
            $rowClass = $joyIn ? "ok-row" : "missing";
            echo "<tr class=\"{$rowClass}\">";
            echo "<td><code>{$course->id}</code></td>";
            echo "<td>".htmlspecialchars(mb_strimwidth($course->course_name,0,50,"..."))."</td>";
            echo "<td>".htmlspecialchars($mapel)."</td>";
            echo "<td>{$rombels}</td>";
            echo "<td>{$totalEnroll}</td>";
            echo "<td>{$joyBadge}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

    // Cari kelas "X Teknik Rekayasa (TE, TAV)" yang disebutkan
    $xTeClass = Classroom::where("class_name","like","%Teknik Rekayasa%")
        ->where("class_name","like","%TE%")
        ->when($activeYear, fn($q)=>$q->where("academic_year_id",$activeYear->id))
        ->get();

    echo "<br><strong>Cari kelas mengandung 'Teknik Rekayasa' + 'TE':</strong><br>";
    if ($xTeClass->isEmpty()) {
        echo "<div class=\"alert-warn\">&#9888; Tidak ada kelas bernama 'X Teknik Rekayasa (TE, TAV)' di database TP aktif. ";
        echo "Mungkin nama kelas berbeda atau belum dibuat. Cek daftar kelas 'Teknik Rekayasa' yang ada:<br>";
        $allTR = Classroom::where("class_name","like","%Teknik Rekayasa%")->when($activeYear,fn($q)=>$q->where("academic_year_id",$activeYear->id))->get();
        foreach($allTR as $c) {
            echo "&bull; <strong>".htmlspecialchars($c->class_name)."</strong> <code>ID:{$c->id}</code> " . ($c->is_combined?"<span class=\"badge y\">GABUNGAN</span>":"<span class=\"badge g\">REGULER</span>") . "<br>";
        }
        echo "</div>";
    } else {
        foreach ($xTeClass as $cls) {
            $lmsLink = LmsClass::where("classroom_id",$cls->id)->whereHas("course",fn($q)=>$q->where("teacher_id",$yuliTeacher->id))->count();
            echo "&bull; <strong>".htmlspecialchars($cls->class_name)."</strong> <code>ID:{$cls->id}</code> — ".($lmsLink ? "<span class=\"badge g\">{$lmsLink} course Yulianus terhubung</span>" : "<span class=\"badge r\">Belum ada course Yulianus</span>")."<br>";
        }
    }
}

// ========================================================
// AKSI PERBAIKAN
// ========================================================
echo "<h2>&#128295; Aksi Perbaikan</h2>";
echo "<div class=\"card\">";
$baseUrl = "?secret=pembda99";

// Tombol sync
if ($doSync) {
    echo "<div class=\"alert-ok\"><strong>&#128260; Re-sync hasil:</strong><br>";
    $svc = app(LmsEnrollmentService::class);
    $n = $svc->syncStudentEnrollments($joy);
    echo "&#10003; Joy Wise Harefa → +{$n} enrollment baru";
    // Sync semua kelas Teknik Rekayasa
    $trClasses = Classroom::where("class_name","like","%Teknik Rekayasa%")->when($activeYear,fn($q)=>$q->where("academic_year_id",$activeYear->id))->get();
    foreach($trClasses as $cls) {
        $nc = $svc->syncClassroomEnrollments($cls);
        echo "<br>&#10003; ".htmlspecialchars($cls->class_name)." → +{$nc} enrollment baru";
    }
    echo "<br><br>Reload halaman ini tanpa sync=1 untuk verifikasi.</div>";
}

// Force enroll Joy ke course Yulianus jika diminta
if ($doFix && $yuliTeacher) {
    echo "<div class=\"alert-ok\"><strong>&#128295; Force-enroll Joy ke semua course Yulianus Zega:</strong><br>";
    $yuliCourses2 = LmsCourse::where("teacher_id",$yuliTeacher->id)->with("lmsClasses")->get();
    $fixCount = 0;
    foreach ($yuliCourses2 as $course) {
        foreach ($course->lmsClasses as $lc) {
            $enr = LmsEnrollment::firstOrCreate(
                ["lms_class_id"=>$lc->id, "student_id"=>$joy->id],
                ["status"=>"enrolled","enrolled_at"=>now()]
            );
            if ($enr->wasRecentlyCreated) $fixCount++;
        }
    }
    echo "&#10003; {$fixCount} enrollment baru dibuat untuk Joy.<br>Ini adalah perbaikan darurat — idealnya perbaiki data kelas reguler Joy di TP aktif.</div>";
}

echo "<a class=\"btn btn-green\" href=\"{$baseUrl}&sync=1\">&#128260; Re-Sync Enrollment Joy + Kelas TR</a>";
echo "<a class=\"btn btn-blue\" href=\"{$baseUrl}&fix=1\">&#9889; Force-Enroll Joy ke Semua Course Yulianus</a>";
echo "<br><br><small style=\"color:#dc2626\">&#9888; Force-enroll adalah solusi darurat. Solusi permanen: tambahkan Joy ke kelas reguler TE/TAV di TP. 2026/2027 (misal: XI TAV atau XI TE).</small>";
echo "</div>";
?>
<hr style="border:1px solid #e2e8f0;margin:24px 0">
<p style="color:#94a3b8;font-size:11px">&#9888; <strong>Hapus file ini setelah selesai!</strong> | <code>perguruanpembda.com/cek_joy_lms.php?secret=pembda99</code></p>
</body></html>

