<?php
// Script to merge duplicate teacher accounts for Yulianus Zega
// Place this in public/fix_akun_ganda.php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\Schedule;

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') {
    die('Unauthorized');
}

echo "<h1>Alat Penggabungan Akun Ganda (Yulianus Zega)</h1>";

if (isset($_GET['action']) && $_GET['action'] == 'merge') {
    $primaryId = $_GET['primary'];
    $duplicateId = $_GET['duplicate'];

    $primary = Teacher::find($primaryId);
    $duplicate = Teacher::find($duplicateId);

    if (!$primary || !$duplicate) {
        die("Invalid IDs");
    }

    echo "<h3>Menggabungkan {$duplicate->full_name} (ID: {$duplicate->id}) ke {$primary->full_name} (ID: {$primary->id})</h3>";

    DB::beginTransaction();
    try {
        // Move Teaching Assignments
        $assignments = TeachingAssignment::where('teacher_id', $duplicate->id)->get();
        foreach ($assignments as $assignment) {
            // Check if primary already has this assignment
            $exists = TeachingAssignment::where('teacher_id', $primary->id)
                ->where('subject_id', $assignment->subject_id)
                ->where('classroom_id', $assignment->classroom_id)
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('semester_id', $assignment->semester_id)
                ->exists();
            if ($exists) {
                echo "<p>Penugasan {$assignment->id} sudah ada di akun utama, dihapus.</p>";
                // Also update any schedules that point to this assignment to point to the primary's assignment
                $primaryAssignment = TeachingAssignment::where('teacher_id', $primary->id)
                    ->where('subject_id', $assignment->subject_id)
                    ->where('classroom_id', $assignment->classroom_id)
                    ->where('academic_year_id', $assignment->academic_year_id)
                    ->where('semester_id', $assignment->semester_id)
                    ->first();
                Schedule::where('teaching_assignment_id', $assignment->id)
                    ->update([
                        'teacher_id' => $primary->id,
                        'teaching_assignment_id' => $primaryAssignment->id
                    ]);
                $assignment->delete();
            } else {
                $assignment->update(['teacher_id' => $primary->id]);
                echo "<p>Penugasan {$assignment->id} dipindahkan ke akun utama.</p>";
            }
        }

        // Move Schedules not linked to TeachingAssignments (if any)
        $schedulesCount = Schedule::where('teacher_id', $duplicate->id)->update(['teacher_id' => $primary->id]);
        echo "<p>{$schedulesCount} jadwal dipindahkan.</p>";

        // Delete duplicate user if needed
        $dupUserId = $duplicate->user_id;
        
        // Delete the duplicate teacher
        $duplicate->delete();
        echo "<p>Akun guru ganda (ID: {$duplicateId}) berhasil dihapus.</p>";

        if ($dupUserId && $dupUserId != $primary->user_id) {
            $user = User::find($dupUserId);
            if ($user && $user->role != 'superadmin') {
                $user->delete();
                echo "<p>Akun user ganda (ID: {$dupUserId}, Username: {$user->username}) berhasil dihapus.</p>";
            }
        }

        DB::commit();
        echo "<h2>Penggabungan Selesai!</h2>";
        echo "<a href='?secret=pembda99'>Kembali</a>";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

$teachers = Teacher::with('school', 'user')->where('full_name', 'like', '%Yulianus%')->get();

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nama</th><th>Kode</th><th>Sekolah</th><th>User ID</th><th>Aksi</th></tr>";
foreach ($teachers as $t) {
    echo "<tr>";
    echo "<td>{$t->id}</td>";
    echo "<td>{$t->full_name}</td>";
    echo "<td>{$t->teacher_code}</td>";
    echo "<td>" . ($t->school ? $t->school->name : 'N/A') . "</td>";
    echo "<td>{$t->user_id} (" . ($t->user ? $t->user->username : 'N/A') . ")</td>";
    echo "<td>";
    foreach ($teachers as $other) {
        if ($other->id != $t->id) {
            echo "<a href='?secret=pembda99&action=merge&primary={$t->id}&duplicate={$other->id}'>Jadikan Utama (Hapus ID {$other->id})</a><br>";
        }
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p>Pilih akun mana yang akan dipertahankan (Jadikan Utama). Akun yang lainnya (duplicate) akan dihapus, dan jadwal/penugasannya akan dipindahkan ke akun utama.</p>";
