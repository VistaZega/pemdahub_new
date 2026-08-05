<?php
header('Content-Type: text/html; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$school = App\Models\School::where('name', 'like', '%SMP%Pembda 2%')->first();
if (!$school) {
    die("<h3>School 'SMP Swasta Pembda 2' not found</h3>");
}

echo "<h2>Pengecekan Data Guru & Pegawai di {$school->name} (ID: {$school->id})</h2>";

$names = ['Arman Jaya Harefa', 'Yarisman Waruwu'];

foreach ($names as $n) {
    echo "<h3>=== Pengecekan: <u>$n</u> ===</h3>";
    
    // Check Employees (Pegawai & Guru in Employee table)
    $employees = App\Models\Employee::where('school_id', $school->id)
        ->where('full_name', 'like', '%' . $n . '%')
        ->get();
        
    echo "<h4>1. Tabel 'employees' (Pegawai / Data Pokok Guru): Ditemukan " . $employees->count() . " data</h4>";
    if ($employees->count() > 0) {
        echo "<ul>";
        foreach ($employees as $e) {
            echo "<li><b>ID:</b> {$e->id} | <b>Tipe:</b> {$e->employee_type} | <b>Kode:</b> {$e->employee_code} | <b>Status:</b> {$e->status} | <b>Nama:</b> {$e->full_name} | <b>User ID:</b> " . ($e->user_id ?? 'NULL') . "</li>";
        }
        echo "</ul>";
    }
    
    // Check Teachers (Guru in Teacher table)
    if (class_exists('App\Models\Teacher')) {
        $teachers = App\Models\Teacher::where('school_id', $school->id)
            ->where('full_name', 'like', '%' . $n . '%')
            ->get();
            
        echo "<h4>2. Tabel 'teachers' (Data Spesifik Guru): Ditemukan " . $teachers->count() . " data</h4>";
        if ($teachers->count() > 0) {
            echo "<ul>";
            foreach ($teachers as $t) {
                $status = $t->is_active ? '<span style="color:green">Aktif</span>' : '<span style="color:red">Nonaktif</span>';
                echo "<li><b>ID:</b> {$t->id} | <b>Kode:</b> {$t->teacher_code} | <b>Status:</b> {$status} | <b>Nama:</b> {$t->full_name} | <b>Employee ID:</b> " . ($t->employee_id ?? 'NULL') . " | <b>User ID:</b> " . ($t->user_id ?? 'NULL') . "</li>";
            }
            echo "</ul>";
        }
    }
    
    // Check Users
    $users = App\Models\User::where('school_id', $school->id)
        ->where('name', 'like', '%' . $n . '%')
        ->get();
        
    echo "<h4>3. Tabel 'users' (Akun Login): Ditemukan " . $users->count() . " data</h4>";
    if ($users->count() > 0) {
        echo "<ul>";
        foreach ($users as $u) {
            echo "<li><b>ID:</b> {$u->id} | <b>Role:</b> {$u->role} | <b>Username:</b> {$u->username} | <b>Email:</b> {$u->email} | <b>Nama:</b> {$u->name}</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
}
