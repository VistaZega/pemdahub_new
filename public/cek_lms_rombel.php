<?php
/**
 * Script cek course LMS yang tidak punya rombel
 * Akses: http://localhost/cek_lms_rombel.php
 * Hapus file ini setelah selesai digunakan
 */

// Bootstrap Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LmsCourse;
use App\Models\LmsClass;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<title>Cek Course LMS - Tanpa Rombel</title>
<style>
  body { font-family: monospace; padding: 20px; background: #f1f5f9; }
  h2 { color: #1e293b; }
  .ok { background: #dcfce7; border-left: 4px solid #16a34a; padding: 12px; margin: 8px 0; border-radius: 4px; }
  .warn { background: #fef9c3; border-left: 4px solid #ca8a04; padding: 12px; margin: 8px 0; border-radius: 4px; }
  .danger { background: #fee2e2; border-left: 4px solid #dc2626; padding: 12px; margin: 8px 0; border-radius: 4px; }
  table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
  th { background: #1e293b; color: white; padding: 10px 14px; text-align: left; font-size: 13px; }
  td { padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
  tr:last-child td { border-bottom: none; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: bold; }
  .badge-ok { background: #dcfce7; color: #166534; }
  .badge-warn { background: #fef9c3; color: #854d0e; }
  .badge-danger { background: #fee2e2; color: #991b1b; }
  .summary-box { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 16px 0; }
  .stat { background: white; border-radius: 8px; padding: 16px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
  .stat .number { font-size: 32px; font-weight: bold; color: #1e293b; }
  .stat .label { font-size: 12px; color: #64748b; margin-top: 4px; }
</style>
</head>
<body>
<h2>🔍 Cek Course LMS — Relasi Rombel</h2>

<?php
try {
    // Hitung total
    $totalCourses    = LmsCourse::count();
    $tanpaLmsClass   = LmsCourse::whereDoesntHave('lmsClasses')->count();
    $punyaLmsClass   = LmsCourse::has('lmsClasses')->count();
    $tanpaClassroomId = LmsCourse::whereNull('classroom_id')->count();
    $orphan          = LmsCourse::whereNull('classroom_id')->whereDoesntHave('lmsClasses')->count();

    // Summary box
    echo '<div class="summary-box">';
    echo '<div class="stat"><div class="number">' . $totalCourses . '</div><div class="label">Total Course</div></div>';
    echo '<div class="stat"><div class="number" style="color:#16a34a">' . $punyaLmsClass . '</div><div class="label">Punya lms_classes</div></div>';
    echo '<div class="stat"><div class="number" style="color:#ca8a04">' . $tanpaLmsClass . '</div><div class="label">Tanpa lms_classes</div></div>';
    echo '<div class="stat"><div class="number" style="color:' . ($orphan > 0 ? '#dc2626' : '#16a34a') . '">' . $orphan . '</div><div class="label">Orphan (tanpa keduanya)</div></div>';
    echo '</div>';

    // Status keseluruhan
    if ($orphan === 0 && $tanpaLmsClass === 0) {
        echo '<div class="ok">✅ <strong>Semua course sudah memiliki rombel.</strong> Aman untuk implementasi rombel-first navigation.</div>';
    } elseif ($orphan === 0 && $tanpaLmsClass > 0) {
        echo '<div class="warn">⚠️ <strong>' . $tanpaLmsClass . ' course tidak punya lms_classes</strong>, tapi masih punya classroom_id. Perlu dicek lebih lanjut.</div>';
    } else {
        echo '<div class="danger">🔴 <strong>' . $orphan . ' course benar-benar orphan</strong> (tanpa classroom_id dan tanpa lms_classes). Perlu penanganan khusus.</div>';
    }

    // Detail course yang tidak punya lms_classes
    if ($tanpaLmsClass > 0) {
        $courses = LmsCourse::whereDoesntHave('lmsClasses')
            ->with(['classroom'])
            ->get(['id', 'course_name', 'classroom_id', 'teacher_id', 'created_at']);

        echo '<h3 style="color:#1e293b;margin-top:24px">📋 Detail Course Tanpa lms_classes (' . $courses->count() . ' course)</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Nama Course</th><th>classroom_id</th><th>Nama Rombel</th><th>Dibuat</th><th>Status</th></tr>';
        foreach ($courses as $c) {
            $rombelNama = optional($c->classroom)->class_name ?? '—';
            $status = is_null($c->classroom_id)
                ? '<span class="badge badge-danger">Orphan</span>'
                : '<span class="badge badge-warn">Punya classroom_id tapi tidak ada di lms_classes</span>';
            echo '<tr>';
            echo '<td>' . $c->id . '</td>';
            echo '<td><strong>' . htmlspecialchars($c->course_name) . '</strong></td>';
            echo '<td>' . ($c->classroom_id ?? '<em>NULL</em>') . '</td>';
            echo '<td>' . htmlspecialchars($rombelNama) . '</td>';
            echo '<td>' . $c->created_at->format('d M Y') . '</td>';
            echo '<td>' . $status . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    // Detail course dengan banyak rombel (multi-rombel)
    $multiRombel = LmsCourse::has('lmsClasses', '>', 1)->with('lmsClasses.classroom')->get(['id','course_name']);
    if ($multiRombel->count() > 0) {
        echo '<h3 style="color:#1e293b;margin-top:24px">📋 Course dengan Lebih dari 1 Rombel (' . $multiRombel->count() . ' course)</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Nama Course</th><th>Rombel-rombel</th><th>Jml Rombel</th></tr>';
        foreach ($multiRombel as $c) {
            $rombels = $c->lmsClasses->pluck('classroom.class_name')->filter()->implode(', ');
            echo '<tr>';
            echo '<td>' . $c->id . '</td>';
            echo '<td><strong>' . htmlspecialchars($c->course_name) . '</strong></td>';
            echo '<td>' . htmlspecialchars($rombels) . '</td>';
            echo '<td><span class="badge badge-warn">' . $c->lmsClasses->count() . ' rombel</span></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<div class="warn" style="margin-top:12px">ℹ️ Course di atas adalah yang akan <strong>paling terpengaruh</strong> oleh perubahan rombel-first. Pastikan semua rombel tampil dengan benar.</div>';
    } else {
        echo '<div class="ok" style="margin-top:16px">✅ Tidak ada course yang terhubung ke lebih dari 1 rombel. Implementasi rombel-first akan sangat straightforward.</div>';
    }

} catch (Exception $e) {
    echo '<div class="danger">❌ Error: ' . $e->getMessage() . '</div>';
}
?>

<p style="color:#94a3b8;font-size:12px;margin-top:32px">⚠️ Hapus file <code>public/cek_lms_rombel.php</code> setelah selesai digunakan.</p>
</body>
</html>
