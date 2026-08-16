<?php
/**
 * Fix 4 Course LMS Orphan — Tautkan ke rombel yang sesuai
 * Akses: https://perguruanpembda.com/fix_lms_orphan.php?secret=pembda99
 * HAPUS file ini setelah digunakan!
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    http_response_code(403);
    die('Akses ditolak.');
}

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LmsCourse;
use App\Models\LmsClass;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');

$dryRun = isset($_GET['dry_run']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Fix LMS Orphan Courses</title>
<style>
  body { font-family: monospace; padding: 20px; background: #f1f5f9; }
  .ok { background: #dcfce7; border-left: 4px solid #16a34a; padding: 12px; margin: 8px 0; border-radius: 4px; }
  .warn { background: #fef9c3; border-left: 4px solid #ca8a04; padding: 12px; margin: 8px 0; border-radius: 4px; }
  .danger { background: #fee2e2; border-left: 4px solid #dc2626; padding: 12px; margin: 8px 0; border-radius: 4px; }
  .info { background: #e0f2fe; border-left: 4px solid #0284c7; padding: 12px; margin: 8px 0; border-radius: 4px; }
  table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); margin-top: 16px; }
  th { background: #1e293b; color: white; padding: 10px 14px; text-align: left; font-size: 13px; }
  td { padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
</style>
</head>
<body>
<h2>🔧 Fix LMS Orphan Courses <?= $dryRun ? '(DRY RUN — tidak ada yang diubah)' : '' ?></h2>

<?php if ($dryRun): ?>
<div class="warn">⚠️ <strong>Mode DRY RUN aktif.</strong> Tidak ada perubahan yang disimpan. Tambahkan tanpa <code>?dry_run=1</code> untuk eksekusi nyata.</div>
<?php endif; ?>

<?php

// Mapping: course_id → keyword nama rombel yang dicari
$mappings = [
    118 => 'Pythagoras',      // Informatika VII - Pythagoras
    122 => 'Gregor Mendel',   // Informatika VIII - Gregor Mendel
    223 => 'Volta',           // Kelas VIII Alessandor Volta → cari Alessandro Volta
    268 => 'Thomas Alva Edison', // Modul 2 Mengayau kelas 9 Thomas Alva Edison
];

echo '<table>';
echo '<tr><th>Course ID</th><th>Nama Course</th><th>Keyword Rombel</th><th>Rombel Ditemukan</th><th>Status</th></tr>';

$results = [];

foreach ($mappings as $courseId => $keyword) {
    $course = LmsCourse::find($courseId);
    if (!$course) {
        echo '<tr><td>' . $courseId . '</td><td colspan="3"><em>Course tidak ditemukan</em></td><td>❌ Skip</td></tr>';
        continue;
    }

    // Cari classroom dengan keyword di nama
    $classroom = Classroom::where('class_name', 'LIKE', '%' . $keyword . '%')
        ->where('school_id', $course->school_id)
        ->first();

    if (!$classroom) {
        // Coba tanpa filter school_id
        $classroom = Classroom::where('class_name', 'LIKE', '%' . $keyword . '%')->first();
    }

    if (!$classroom) {
        echo '<tr><td>' . $courseId . '</td><td>' . htmlspecialchars($course->course_name) . '</td><td>' . htmlspecialchars($keyword) . '</td><td><em style="color:red">Tidak ditemukan</em></td><td>❌ Gagal</td></tr>';
        continue;
    }

    // Cek apakah sudah ada di lms_classes
    $existing = LmsClass::where('course_id', $courseId)->where('classroom_id', $classroom->id)->first();

    $status = '';
    if ($existing) {
        $status = '⚠️ Sudah ada';
    } elseif (!$dryRun) {
        try {
            DB::beginTransaction();
            // Buat lms_class entry
            LmsClass::create([
                'course_id'    => $courseId,
                'classroom_id' => $classroom->id,
                'school_id'    => $course->school_id,
                'is_active'    => true,
            ]);
            // Update classroom_id di course jika null
            if (!$course->classroom_id) {
                $course->update(['classroom_id' => $classroom->id]);
            }
            DB::commit();
            $status = '✅ Berhasil ditautkan';
        } catch (\Exception $e) {
            DB::rollBack();
            $status = '❌ Error: ' . $e->getMessage();
        }
    } else {
        $status = '🔍 [DRY RUN] Akan ditautkan';
    }

    echo '<tr>';
    echo '<td>' . $courseId . '</td>';
    echo '<td><strong>' . htmlspecialchars($course->course_name) . '</strong></td>';
    echo '<td>' . htmlspecialchars($keyword) . '</td>';
    echo '<td>' . htmlspecialchars($classroom->class_name) . ' (ID:' . $classroom->id . ')</td>';
    echo '<td>' . $status . '</td>';
    echo '</tr>';
}

echo '</table>';

// Verifikasi hasil
echo '<h3 style="margin-top:24px">📋 Verifikasi Setelah Fix</h3>';
$stillOrphan = LmsCourse::whereDoesntHave('lmsClasses')->whereIn('id', array_keys($mappings))->count();
if ($stillOrphan === 0 && !$dryRun) {
    echo '<div class="ok">✅ Semua 4 course berhasil ditautkan ke rombel. Tidak ada orphan tersisa dari daftar ini.</div>';
} elseif ($dryRun) {
    echo '<div class="info">ℹ️ Ini adalah dry run. Jalankan tanpa <code>?dry_run=1</code> untuk eksekusi nyata.</div>';
} else {
    echo '<div class="warn">⚠️ Masih ada ' . $stillOrphan . ' course yang belum tertautkan. Cek log di atas.</div>';
}

// Tampilkan lms_classes yang baru dibuat (verifikasi)
if (!$dryRun) {
    $newLinks = LmsClass::whereIn('course_id', array_keys($mappings))->with(['course', 'classroom'])->get();
    if ($newLinks->count() > 0) {
        echo '<h4 style="margin-top:16px">lms_classes yang ada untuk course ini:</h4>';
        echo '<table>';
        echo '<tr><th>lms_class ID</th><th>Course</th><th>Classroom</th></tr>';
        foreach ($newLinks as $lc) {
            echo '<tr><td>' . $lc->id . '</td><td>' . htmlspecialchars($lc->course->course_name ?? '-') . '</td><td>' . htmlspecialchars($lc->classroom->class_name ?? '-') . '</td></tr>';
        }
        echo '</table>';
    }
}
?>

<p style="color:#94a3b8;font-size:12px;margin-top:32px">⚠️ Hapus file <code>public/fix_lms_orphan.php</code> setelah selesai digunakan.</p>
<p style="margin-top:8px"><a href="?secret=pembda99&dry_run=1">Jalankan Dry Run</a> | <a href="?secret=pembda99">Eksekusi Nyata</a></p>
</body>
</html>
