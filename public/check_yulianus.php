<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>";
echo "=== HASIL PENCARIAN AKUN 'Yulianus Zega' ===\n\n";

$users = \App\Models\User::where('name', 'like', '%Yulianus%')->where('name', 'like', '%Zega%')->get();
echo "1. DATA USERS (Akun Login):\n";
echo "Ditemukan " . $users->count() . " akun:\n";
foreach($users as $u) {
    $schoolName = $u->school ? $u->school->name : 'Unknown';
    echo " - ID: {$u->id} | Nama: {$u->name} | Username: {$u->username} | Role: {$u->role} | Unit: {$schoolName}\n";
}
echo "\n----------------------------------------\n\n";

$teachers = \App\Models\Teacher::where('full_name', 'like', '%Yulianus%')->where('full_name', 'like', '%Zega%')->get();
echo "2. DATA TEACHERS (Profil Guru):\n";
echo "Ditemukan " . $teachers->count() . " profil guru:\n";
foreach($teachers as $t) {
    $schoolName = $t->school ? $t->school->name : 'Unknown';
    $userName = $t->user ? $t->user->username : 'Tidak Ada Akun Login (NULL)';
    echo " - Teacher ID: {$t->id} | Nama: {$t->full_name} | Unit Utama: {$schoolName} | Terhubung ke User: {$userName}\n";
    
    // Cek sekolah tambahan (pivot)
    $additionalSchools = $t->additionalSchools;
    if ($additionalSchools->count() > 0) {
        echo "   [Lintas Unit]: ";
        $schoolNames = [];
        foreach($additionalSchools as $s) {
            $schoolNames[] = $s->name;
        }
        echo implode(', ', $schoolNames) . "\n";
    }

    // Cek Jadwal Mengajar (Assignments)
    $assignments = \App\Models\TeachingAssignment::where('teacher_id', $t->id)->get();
    echo "   [Jadwal Mengajar]: " . $assignments->count() . " kelas\n";
    foreach($assignments as $a) {
        $subject = $a->subject ? $a->subject->name : "ID:{$a->subject_id}";
        $classroom = $a->classroom ? $a->classroom->name : "ID:{$a->classroom_id}";
        $school = $a->school ? $a->school->name : "ID:{$a->school_id}";
        echo "     -> Mapel: {$subject} | Kelas: {$classroom} | Unit: {$school}\n";
    }
    echo "\n";
}

echo "</pre>";
