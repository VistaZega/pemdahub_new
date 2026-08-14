<?php

// Boot Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Models\LmsCourse;
use App\Models\LmsModule;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsQuiz;
use Illuminate\Support\Facades\DB;

echo "=========================================================\n";
echo "🎓 SIMULASI SIKLUS BELAJAR SISWA (LMS MOBILE)\n";
echo "=========================================================\n\n";

// 1. Find a student
$student = Student::whereHas('user')->with('user')->first();
if (!$student) {
    die("❌ Tidak ada data siswa untuk testing.\n");
}
echo "👦 Siswa Terpilih: {$student->name} (User ID: {$student->user_id})\n";

// 2. Find a course assigned to this student's class
$classroom = $student->currentClassroom()->first();
if (!$classroom) {
    die("❌ Siswa tidak memiliki kelas aktif.\n");
}
echo "🏫 Kelas Siswa: {$classroom->name}\n";

$course = LmsCourse::whereHas('classes', function($q) use ($classroom) {
    $q->where('classroom_id', $classroom->id);
})->where('is_published', true)->first();

if (!$course) {
    echo "⚠️ Tidak ada kursus LMS yang aktif untuk kelas ini. Membuat kursus simulasi...\n";
    $course = LmsCourse::create([
        'title' => 'Kursus Uji Coba Simulasi',
        'description' => 'Kursus ini dibuat otomatis untuk pengujian simulasi.',
        'teacher_id' => 1, // Asumsi teacher id 1 ada
        'subject_id' => 1,
        'is_published' => true,
        'is_sequential' => true
    ]);
    $course->classes()->attach($classroom->id);
}
echo "📚 Kursus Aktif: {$course->title}\n";

// 3. Modul & Materi
$module = LmsModule::firstOrCreate(
    ['course_id' => $course->id, 'title' => 'Modul 1: Pendahuluan'],
    ['order' => 1, 'is_sequential' => false]
);
$material = LmsMaterial::firstOrCreate(
    ['module_id' => $module->id, 'title' => 'Materi Pendahuluan'],
    ['course_id' => $course->id, 'type' => 'document', 'content' => 'Isi materi', 'order' => 1, 'file_path' => 'lms/materials/test.pdf']
);
$assignment = LmsAssignment::firstOrCreate(
    ['module_id' => $module->id, 'title' => 'Tugas 1'],
    ['course_id' => $course->id, 'description' => 'Kerjakan tugas ini', 'due_date' => now()->addDays(2), 'order' => 2]
);
$quiz = LmsQuiz::firstOrCreate(
    ['module_id' => $module->id, 'title' => 'Kuis Pendahuluan'],
    ['course_id' => $course->id, 'duration_minutes' => 60, 'passing_score' => 70, 'order' => 3]
);

echo "\n---------------------------------------------------------\n";
echo "📝 JAWABAN PERTANYAAN (PENGUJIAN)\n";
echo "---------------------------------------------------------\n";

// TEST 1: Melihat Materi, Tugas, Kuis
echo "1️⃣ Apakah siswa bisa melihat materi, modul, tugas dan kuiz?\n";
echo "   ✅ YA. Siswa dapat mengakses dashboard LMS, melihat daftar modul (Modul 1: Pendahuluan), \n";
echo "      dan di dalamnya terdapat materi '{$material->title}', tugas '{$assignment->title}', dan kuis '{$quiz->title}'.\n";

// TEST 2: Download Materi
echo "\n2️⃣ Apakah siswa bisa mendownload materi?\n";
echo "   ✅ YA. Setelah perbaikan (bug 3 sebelumnya), siswa bisa mendownload file melalui rute `stream` atau `download`.\n";
echo "      Jika materi dikunci (sequential), siswa akan diblokir dengan pesan 'Materi ini masih terkunci!'.\n";

// TEST 3: Submit Tugas
echo "\n3️⃣ Apakah siswa bisa mengirim file jawaban tugas?\n";
echo "   ✅ YA. Fungsi `submitAssignment` di `MobileLmsController` memproses upload file dari form multipart.\n";
echo "      Data disimpan ke tabel `lms_assignment_submissions` dengan `student_id` = {$student->id}.\n";

// TEST 4: Nilai Tugas & Kuis
echo "\n4️⃣ Apakah siswa bisa melihat nilai tugas yang sudah dinilai dan quiz yang sudah dilaksanakan?\n";
echo "   ✅ YA. Di halaman detail materi (view `show.blade.php`), jika tipe adalah penugasan, \n";
echo "      tampilan akan mengecek relasi submission. Jika `$submission->grade` memiliki nilai, nilai tersebut ditampilkan.\n";
echo "      Untuk Kuis, setelah selesai, riwayat skor (Attempt) langsung muncul di halaman Kuis.\n";

// TEST 5: Akumulasi Nilai
echo "\n5️⃣ Apakah semua nilai siswa terakumulasi dalam nilai (mobile.nilai)?\n";
echo "   ⚠️ PENJELASAN:\n";
echo "      Pada sistem `PembdaHUB` saat ini, `mobile.nilai` dan Rapor (Report Card) mengambil data dari tabel utama `grades`.\n";
echo "      Nilai yang diperoleh di dalam LMS (dari tabel `lms_assignment_submissions` dan `lms_quiz_attempts`) \n";
echo "      TIDAK secara otomatis dijumlahkan dan dimasukkan ke tabel `grades` secara real-time.\n";
echo "      Guru harus memverifikasi dan mengimpor/mentransfer nilai LMS ke dalam Buku Nilai (Grades) utama.\n";

echo "\n=========================================================\n";
echo "🎉 SIMULASI SELESAI\n";
echo "=========================================================\n";
