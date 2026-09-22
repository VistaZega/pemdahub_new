<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicDisplayController;
use App\Http\Controllers\SimLabController;
use Illuminate\Support\Facades\Route;

Route::get('/dev-unassigned', function() {
    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
    $students = \App\Models\Student::active()->whereDoesntHave('studentClasses', function($q) use ($activeYear) {
        $q->where('academic_year_id', $activeYear->id);
    })->with('school')->get();
    $results = [];
    foreach ($students as $s) {
        $results[] = ['NIS' => $s->nis, 'Nama' => $s->full_name, 'Sekolah' => $s->school ? $s->school->name : '-'];
    }
    return response()->json($results);
});
use Illuminate\Support\Facades\Artisan;
Route::get('/download', function () {
    return view('public.app_download');
})->name('app.download');

Route::get('/install_wa_engine.php', function() {
    require public_path('install_wa_engine.php');
    return '';
});

Route::get('/wa_qr.php', function() {
    require public_path('wa_qr.php');
    return '';
});

Route::get('/app', function () {
    return view('public.app_download');
});

Route::get('/pwa-reset', function () {
    return response('<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Cache PWA PembdaHUB</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; text-align: center; }
        .card { background: #1e293b; padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); max-width: 400px; border: 2px solid #3b82f6; }
        .spinner { border: 4px solid rgba(255, 255, 255, 0.1); width: 40px; height: 40px; border-radius: 50%; border-left-color: #3b82f6; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem;">🔄 Memperbarui Aplikasi Mobile...</h2>
        <p style="font-size: 0.875rem; color: #94a3b8;">Menghapus cache PWA & Service Worker lama di perangkat Anda...</p>
    </div>
    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) { registration.unregister(); }
            });
        }
        if ("caches" in window) {
            caches.keys().then(function(names) {
                for (let name of names) { caches.delete(name); }
            });
        }
        setTimeout(function() { window.location.href = "/m/dashboard"; }, 1200);
    </script>
</body>
</html>');
});

// ============================================================
//  PUBLIC DISPLAY - Live Monitoring Kehadiran (Raspberry Pi / Monitor TV)
//  Tidak memerlukan login. Akses: /display (Umum), /display1 (SMP), /display2 (SMA), /display3 (SMK)
// ============================================================
Route::get('/display1', [PublicDisplayController::class, 'display1'])->name('display.unit1');
Route::get('/display2', [PublicDisplayController::class, 'display2'])->name('display.unit2');
Route::get('/display3', [PublicDisplayController::class, 'display3'])->name('display.unit3');

Route::prefix('display')->name('display.')->group(function () {
    Route::get('/',          [PublicDisplayController::class, 'index'])->name('index');
    Route::get('/live-data', [PublicDisplayController::class, 'liveData'])->name('live-data');
    Route::get('/{unit}',    [PublicDisplayController::class, 'unitIndex'])->where('unit', '1|2|3|smp|sma|smk')->name('unit');
});

// ============================================================
//  SHARED YAYASAN VIEW (Public dengan Token)
// ============================================================
Route::get('/yayasan/progress-input/shared', [App\Http\Controllers\Yayasan\ProgressInputController::class, 'indexShared'])->name('shared.progress-input');
Route::get('/yayasan/progress-input/shared/export-pdf', [App\Http\Controllers\Yayasan\ProgressInputController::class, 'exportPdfShared'])->name('shared.progress-input.export-pdf');

// ============================================================
//  PEMBDAHUB SIMLAB - Virtual Microcontroller & IoT Simulator
// ============================================================
Route::prefix('simlab')->name('simlab.')->group(function () {
    Route::get('/', [SimLabController::class, 'index'])->name('index');
    Route::get('/editor/{id?}', [SimLabController::class, 'editor'])->name('editor');
    Route::post('/save', [SimLabController::class, 'store'])->name('save');
    Route::get('/my-projects', [SimLabController::class, 'myProjects'])->name('my-projects');
    Route::get('/project/{id}', [SimLabController::class, 'show'])->name('show');
    Route::post('/compile', [SimLabController::class, 'compile'])->name('compile');
    Route::delete('/project/{id}', [SimLabController::class, 'destroy'])->name('destroy');
    Route::post('/project/{id}/duplicate', [SimLabController::class, 'duplicate'])->name('duplicate');
});


Route::get('/delete-bills-2026-2027', function () {
    if (request('token') !== 'pembda2026delete') {
        abort(403, 'Akses ditolak. Token keamanan tidak valid.');
    }
    
    $output = "=== PROSES PENGHAPUSAN TAGIHAN & PEMBAYARAN TP. 2026/2027 ===<br>";
    
    try {
        Illuminate\Support\Facades\DB::transaction(function () use (&$output) {
            $year = App\Models\AcademicYear::where('year', 'like', '%2026/2027%')
                ->orWhere('year', 'like', '%2026-2027%')
                ->first();

            if (!$year) {
                $output .= "Tahun Pelajaran TP. 2026/2027 tidak ditemukan di database.<br>";
                return;
            }

            $output .= "Tahun Pelajaran ditemukan: ID: {$year->id}, Nama: {$year->year}<br>";

            $billIds = Illuminate\Support\Facades\DB::table('student_bills')
                ->where('academic_year_id', $year->id)
                ->pluck('id')
                ->toArray();

            $billsCount = count($billIds);

            if ($billsCount === 0) {
                $output .= "Tidak ada tagihan (student_bills) yang ditemukan untuk TP. 2026/2027.<br>";
                $output .= "Tidak ada data yang dihapus.<br>";
                return;
            }

            $output .= "Jumlah tagihan ditemukan: {$billsCount}<br>";

            $paymentsDeleted = Illuminate\Support\Facades\DB::table('payments')
                ->whereIn('bill_id', $billIds)
                ->delete();

            $output .= "Jumlah data pembayaran (payments) yang berhasil dihapus: {$paymentsDeleted}<br>";

            $billsDeleted = Illuminate\Support\Facades\DB::table('student_bills')
                ->where('academic_year_id', $year->id)
                ->delete();

            $output .= "Jumlah data tagihan (student_bills) yang berhasil dihapus: {$billsDeleted}<br>";
            $output .= "Transaksi berhasil diselesaikan (committed).<br>";
        });

    } catch (\Exception $e) {
        $output .= "ERROR: Terjadi kesalahan saat memproses penghapusan data.<br>";
        $output .= htmlspecialchars($e->getMessage()) . "<br>";
    }
    
    $output .= "=== PROSES SELESAI ===<br>";
    return $output;
});

Route::get('/debug-menu-check', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Akses ditolak.');
    }

    header('Content-Type: text/html; charset=UTF-8');
    $output = "<pre style='font-family:monospace; font-size:14px; padding:20px; background:#1e1e1e; color:#d4d4d4;'>";

    // 1. Check active academic year
    $output .= "<span style='color:#569cd6;'>══════ ACADEMIC YEAR ══════</span>\n";
    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
    if ($activeYear) {
        $output .= "✅ Active Year: {$activeYear->year} (semester: {$activeYear->semester}, id: {$activeYear->id})\n\n";
    } else {
        $output .= "❌ TIDAK ADA TAHUN AJARAN AKTIF!\n\n";
    }

    // 2. Check schools
    $output .= "<span style='color:#569cd6;'>══════ SCHOOLS ══════</span>\n";
    $schools = \App\Models\School::schoolsOnly()->get();
    foreach ($schools as $s) {
        $output .= "  ID={$s->id} | type=[{$s->type}] | {$s->name}\n";
    }

    // 3. Find the specific student
    $output .= "\n<span style='color:#569cd6;'>══════ SEARCH USER BY NAME: ABRAHAM ══════</span>\n";
    $users = \App\Models\User::where('name', 'like', '%ABRAHAM%')
        ->orWhere('username', 'like', '%abraham%')
        ->get();
        
    if ($users->isEmpty()) {
        $output .= "❌ User dengan nama/username mengandung 'ABRAHAM' tidak ditemukan di tabel users!\n";
        // List some users to see
        $output .= "\nDaftar 10 User Pertama:\n";
        foreach (\App\Models\User::take(10)->get() as $u) {
            $output .= "  ID={$u->id} | Name={$u->name} | Username={$u->username} | Role={$u->role}\n";
        }
    } else {
        foreach ($users as $u) {
            $output .= "✅ User Found: ID={$u->id} | Name=[{$u->name}] | Username=[{$u->username}] | Role=[{$u->role}]\n";
            // Check student relation
            $student = $u->student;
            if ($student) {
                $output .= "  -> Has Student Record: ID={$student->id} | Full Name=[{$student->full_name}] | NISN={$student->nisn} | status={$student->status}\n";
                $school = $student->school;
                $output .= "  -> School: ID={$student->school_id} | Name=" . ($school ? $school->name : 'NULL') . " | Type=" . ($school ? $school->type : 'NULL') . "\n";
                
                // Check student classroom relations directly
                $output .= "  -> Classroom relations (all years):\n";
                $scs = \Illuminate\Support\Facades\DB::table('student_classes')
                    ->where('student_id', $student->id)
                    ->get();
                if ($scs->isEmpty()) {
                    $output .= "     ❌ Tidak ada record di student_classes!\n";
                } else {
                    foreach ($scs as $sc) {
                        $cls = \App\Models\Classroom::find($sc->classroom_id);
                        $ay = \App\Models\AcademicYear::find($sc->academic_year_id);
                        $output .= "     - classroom_id={$sc->classroom_id} ({$cls?->name}, grade_level={$cls?->grade_level})";
                        $output .= " | academic_year_id={$sc->academic_year_id} ({$ay?->year}, is_active=" . ($ay?->is_active ? 'YES' : 'NO') . ")";
                        $output .= " | status={$sc->status}\n";
                    }
                }

                // Simulate currentClassroom relation
                $currentClass = $student->currentClassroom()->first();
                $output .= "  -> Active Classroom via currentClassroom(): " . ($currentClass ? "{$currentClass->name} (Grade: {$currentClass->grade_level})" : "❌ NULL") . "\n";
            } else {
                $output .= "  ❌ Tidak memiliki record di tabel students!\n";
            }
        }
    }

    // 4. Check academic years in DB
    $output .= "\n<span style='color:#569cd6;'>══════ ACADEMIC YEARS IN DATABASE ══════</span>\n";
    $years = \App\Models\AcademicYear::all();
    foreach ($years as $y) {
        $output .= "  ID={$y->id} | Year=[{$y->year}] | Semester=[{$y->semester}] | Active=" . ($y->is_active ? "🟢 ACTIVE" : "🔴 INACTIVE") . "\n";
    }

    $output .= "\n</pre>";
    return $output;
});

Route::get('/db-diagnose', function () {
    if (request('token') !== 'pembda2026diagnose') {
        abort(403);
    }
    
    // 1. Data kelas
    $classrooms = \Illuminate\Support\Facades\DB::table('classrooms')
        ->leftJoin('schools', 'classrooms.school_id', '=', 'schools.id')
        ->leftJoin('majors', 'classrooms.major_id', '=', 'majors.id')
        ->select('classrooms.*', 'schools.name as school_name', 'schools.type as school_type', 'majors.major_name')
        ->get();
        
    $output = "<h2>🔧 Pembda Hub - DB Diagnose & Normalization</h2>";
    $output .= "<h3>1. Data Kelas Saat Ini:</h3>";
    $output .= "<table border='1' cellpadding='8' style='border-collapse: collapse;'>
            <tr style='background: #f0f0f0;'>
                <th>ID</th>
                <th>Nama Kelas</th>
                <th>Tingkat</th>
                <th>Sekolah</th>
                <th>Tipe</th>
                <th>Jurusan Saat Ini</th>
                <th>Major ID</th>
                <th>School ID</th>
            </tr>";
    foreach ($classrooms as $c) {
        $major = $c->major_name ?? '<span style="color: gray;">NULL (Tanpa Jurusan)</span>';
        $output .= "<tr>
                <td>{$c->id}</td>
                <td>{$c->class_name}</td>
                <td>{$c->grade_level}</td>
                <td>{$c->school_name}</td>
                <td>{$c->school_type}</td>
                <td>{$major}</td>
                <td>" . ($c->major_id ?? 'NULL') . "</td>
                <td>{$c->school_id}</td>
              </tr>";
    }
    $output .= "</table>";
    
    // 2. Normalisasi
    
    // A. Set program_keahlian_id & konsentrasi_keahlian_id ke null untuk sekolah selain SMK (yaitu SMA & SMP)
    $affectedKeahlian = \Illuminate\Support\Facades\DB::table('classrooms')
        ->whereIn('school_id', function($query) {
            $query->select('id')->from('schools')
                  ->where('type', '!=', 'SMK')
                  ->where('type', '!=', 'SMKS');
        })
        ->where(function($query) {
            $query->whereNotNull('program_keahlian_id')
                  ->orWhereNotNull('konsentrasi_keahlian_id');
        })
        ->update([
            'program_keahlian_id' => null,
            'konsentrasi_keahlian_id' => null
        ]);

    // B. Set major_id ke null untuk kelas SMA tingkat X (10)
    $affectedSMA10 = \Illuminate\Support\Facades\DB::table('classrooms')
        ->whereIn('school_id', function($query) {
            $query->select('id')->from('schools')
                  ->where('type', 'SMA')
                  ->orWhere('type', 'SMAS')
                  ->orWhere('type', 'LIKE', '%SMA%');
        })
        ->where('grade_level', 10)
        ->update(['major_id' => null]);
        
    $output .= "<h3>2. Melakukan Normalisasi Otomatis:</h3>";
    $output .= "<p style='color: green; font-weight: bold;'>✅ Berhasil membersihkan program keahlian pada {$affectedKeahlian} kelas Non-SMK.</p>";
    $output .= "<p style='color: green; font-weight: bold;'>✅ Berhasil menormalisasi {$affectedSMA10} kelas SMA tingkat X menjadi tanpa jurusan.</p>";
    
    // 3. Diagnosa Jabatan Kepala Sekolah & Herni Yanti
    $output .= "<h3>3. Diagnosa Jabatan & Kepegawaian (Herni Yanti):</h3>";
    $herniTeacher = \App\Models\Teacher::where('teacher_code', 'GR001')->first();
    $herniEmployee = \App\Models\Employee::find(25);
    
    $output .= "<p><b>Data Guru Herni Yanti (GR001):</b> ID=" . ($herniTeacher->id ?? 'NULL') . ", Employee ID=" . ($herniTeacher->employee_id ?? 'NULL') . ", School ID=" . ($herniTeacher->school_id ?? 'NULL') . "</p>";
    if ($herniEmployee) {
        $output .= "<p><b>Data Employee Herni Yanti (ID 25):</b> Name={$herniEmployee->full_name}, School ID={$herniEmployee->school_id}</p>";
    }
    
    $kasekPositions = \App\Models\Position::where('position_code', 'LIKE', 'KASEK%')->get();
    $output .= "<p><b>Daftar Jabatan KASEK di Database:</b></p><ul>";
    foreach ($kasekPositions as $kp) {
        $output .= "<li>ID: {$kp->id}, Name: {$kp->position_name}, Code: {$kp->position_code}, School ID: " . ($kp->school_id ?? 'NULL') . ", Level: {$kp->position_level}</li>";
    }
    $output .= "</ul>";
    
    $activeAssignments = \DB::table('employee_positions')
        ->where('employee_id', 25)
        ->get();
    $output .= "<p><b>Daftar Jabatan Aktif Herni Yanti (Employee ID 25):</b></p><ul>";
    foreach ($activeAssignments as $aa) {
        $pos = \App\Models\Position::find($aa->position_id);
        $output .= "<li>ID Penugasan: {$aa->id}, Position ID: {$aa->position_id} (" . ($pos->position_name ?? 'NULL') . "), Start: {$aa->start_date}, End: " . ($aa->end_date ?? 'NULL') . ", Academic Year ID: " . ($aa->academic_year_id ?? 'NULL') . "</li>";
    }
    $output .= "</ul>";

    return $output;
});

Route::get('/seed-simulasi', function () {
    try {
        echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
        echo "<h1>=== DATABASE SEED SIMULATION RUNNER ===</h1>\n";
        echo "Running seeder... (ini mungkin memakan waktu 5-10 menit, jangan tutup tab browser ini)\n";
        
        // Naikkan batas eksekusi karena data sangat besar
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit', '512M');
        
        $output = Artisan::call('db:seed', [
            '--class' => 'ComprehensiveSimulationSeeder',
            '--force' => true
        ]);
        
        echo "\n[+] SUCCESS: Seeder completed successfully!\n";
        echo Artisan::output();
    } catch (\Exception $e) {
        echo "\n[x] ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
});

Route::get('/seed-pelatihan', function () {
    try {
        echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
        echo "<h1>=== DATABASE SEED TRAINING MODULE RUNNER ===</h1>\n";
        echo "Running TrainingModuleSeeder...\n";
        
        $output = Artisan::call('db:seed', [
            '--class' => 'TrainingModuleSeeder',
            '--force' => true
        ]);
        
        echo "\n[+] SUCCESS: TrainingModuleSeeder completed successfully!\n";
        echo Artisan::output();
    } catch (\Exception $e) {
        echo "\n[x] ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
});

Route::get('/storage-link', function () {
    try {
        echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
        echo "<h1>=== STORAGE LINK CREATOR ===</h1>\n";
        
        $linkPath = public_path('storage');
        if (file_exists($linkPath)) {
            echo "Existing public/storage detected. Attempting to remove it to avoid conflicts...\n";
            if (is_link($linkPath)) {
                unlink($linkPath);
                echo "Deleted existing symlink.\n";
            } else {
                @rename($linkPath, $linkPath . '_old_' . time());
                echo "Renamed existing folder to avoid conflict.\n";
            }
        }
        
        $output = Artisan::call('storage:link');
        
        echo "\n[+] SUCCESS: storage:link completed successfully!\n";
        echo Artisan::output();
    } catch (\Exception $e) {
        echo "\n[x] ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
});

Route::get('/perbaikan-final', function () {
    try {
        echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
        echo "<h1>=== ALGORITMA PERBAIKAN TOTAL (GURU & SISWA SMK) ===</h1>\n";

        // ============================================
        // 1. BERSIHKAN DATA SAMPAH (ORPHANED USERS)
        // ============================================
        echo "<h3>[TAHAP 1] Membersihkan Data Sampah (User Tanpa Identitas)...</h3>\n";
        $allUsers = \App\Models\User::whereIn('school_id', [1, 3])->whereIn('role', ['guru', 'siswa'])->get();
        $deletedUsers = 0;

        foreach ($allUsers as $u) {
            $isLinked = false;
            if ($u->role === 'guru') {
                $isLinked = \App\Models\Employee::where('user_id', $u->id)->exists();
            } else if ($u->role === 'siswa') {
                $isLinked = \App\Models\Student::where('user_id', $u->id)->exists();
            }

            if (!$isLinked) {
                echo "[-] MENGHAPUS DATA YATIM/KOSONG: ID {$u->id} | Role: {$u->role} | Email: {$u->email} | Username: {$u->username}\n";
                $u->delete();
                $deletedUsers++;
            }
        }
        echo "<b>Total Data Sampah Terhapus: $deletedUsers</b>\n";

        // ============================================
        // 2. PERBAIKI GURU (School 1 & 3)
        // ============================================
        echo "\n<h3>[TAHAP 2] Memperbaiki & Sinkronisasi GURU...</h3>\n";
        foreach ([1 => 'gurusmpsp2', 3 => 'gurusmks'] as $schoolId => $passRaw) {
            $guruPass = \Illuminate\Support\Facades\Hash::make($passRaw);
            $domain = $schoolId == 1 ? 'smpp2.pembdahub.com' : 'smk.pembdahub.com';
            
            $gurus = \App\Models\Employee::where('school_id', $schoolId)->where('employee_type', 'guru')->get();
            
            foreach ($gurus as $guru) {
                $firstName = preg_replace('/[^a-z0-9]/', '', strtolower(explode(' ', trim(preg_replace('/,.*$/', '', $guru->full_name)))[0]));
                if(empty($firstName)) $firstName = "guru";
                
                $user = $guru->user;
                
                if (!$user) {
                    $user = \App\Models\User::create([
                        'name' => $guru->full_name,
                        'username' => $firstName . time() . uniqid(), 
                        'email' => time() . uniqid() . '@temp.com',
                        'password' => $guruPass,
                        'role' => 'guru',
                        'school_id' => $schoolId,
                        'is_active' => true,
                        'must_change_password' => true
                    ]);
                    $guru->user_id = $user->id;
                    $guru->save();
                }

                $expectedEmail = $firstName . '@' . $domain;
                $expectedUsername = $firstName;
                
                $counter = 1;
                while (
                    \App\Models\User::where('email', $expectedEmail)->where('id', '!=', $user->id)->exists() ||
                    \App\Models\User::where('username', $expectedUsername)->where('id', '!=', $user->id)->exists()
                ) {
                    $expectedEmail = $firstName . $counter . '@' . $domain;
                    $expectedUsername = $firstName . $counter;
                    $counter++;
                }

                $user->name = $guru->full_name; 
                $user->username = $expectedUsername;
                $user->email = $expectedEmail;
                $user->password = $guruPass;
                $user->save();

                echo "[+] SUCCESS GURU: {$user->name} -> Username: {$user->username} | Email: {$user->email}\n";
            }
        }

        // ============================================
        // 3. PERBAIKI SISWA (School 1 & 3)
        // ============================================
        echo "\n<h3>[TAHAP 3] Memperbaiki & Sinkronisasi SISWA...</h3>\n";
        foreach ([1 => 'siswasmpsp2', 3 => 'siswasmks'] as $schoolId => $passRaw) {
            $siswaPass = \Illuminate\Support\Facades\Hash::make($passRaw);
            $domain = $schoolId == 1 ? 'smpp2.pembdahub.com' : 'smk.pembdahub.com';
            
            $siswas = \App\Models\Student::where('school_id', $schoolId)->get();
            
            foreach ($siswas as $siswa) {
                $firstName = preg_replace('/[^a-z0-9]/', '', strtolower(explode(' ', trim($siswa->full_name))[0]));
                if(empty($firstName)) $firstName = "student";
                
                $user = $siswa->user;
                
                if (!$user) {
                    $expectedEmail = $firstName . '@' . $domain;
                    
                    $existingUser = \App\Models\User::where('username', $siswa->nisn)->where('role', 'siswa')->first();
                    if(!$existingUser) {
                        $existingUser = \App\Models\User::where('email', $expectedEmail)->where('role', 'siswa')->first();
                    }

                    if ($existingUser) {
                        $user = $existingUser;
                    } else {
                        $user = \App\Models\User::create([
                            'name' => $siswa->full_name,
                            'username' => $firstName . time() . uniqid(), 
                            'email' => time() . uniqid() . '@temp.com',
                            'password' => $siswaPass,
                            'role' => 'siswa',
                            'school_id' => $schoolId,
                            'is_active' => true,
                            'must_change_password' => true
                        ]);
                    }
                    $siswa->user_id = $user->id;
                    $siswa->save();
                }

                $expectedEmail = $firstName . '@' . $domain;
                $expectedUsername = $siswa->nisn ?: ($firstName . rand(100,9999));
                
                $counter = 1;
                while (
                    \App\Models\User::where('email', $expectedEmail)->where('id', '!=', $user->id)->exists() ||
                    \App\Models\User::where('username', $expectedUsername)->where('id', '!=', $user->id)->exists()
                ) {
                    $expectedEmail = $firstName . $counter . '@' . $domain;
                    if(\App\Models\User::where('username', $expectedUsername)->where('id', '!=', $user->id)->exists()) {
                        $expectedUsername = $expectedUsername . $counter;
                    }
                    $counter++;
                }

                $user->name = $siswa->full_name;
                $user->username = $expectedUsername;
                $user->email = $expectedEmail;
                $user->password = $siswaPass;
                $user->save();
            }
            echo "[+] SUCCESS: Semua siswa untuk unit {$schoolId} telah diperbaiki.\n";
        }

        echo "\n\n<b><h2 style='color:#0f0;'>✅ PERBAIKAN 100% SUKSES DAN SELESAI! SILAKAN CEK DI WEBSITE!</h2></b>\n";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Public routes
Route::get('/', function () {
    $news = \App\Models\News::published()
        ->orderByRaw('COALESCE(published_at, created_at) DESC')
        ->take(3)
        ->get();

    $galleryItems = \App\Models\GalleryItem::active()
        ->ordered()
        ->take(7)
        ->get();

    // 5. MODUL / COURSE LMS (4 Acak SMP/SMA/SMK dari data KBM Guru Aktif)
    $lmsCoursesQuery = \App\Models\LmsCourse::with(['teacher.user', 'school', 'subject', 'classroom'])
        ->where(function($q) {
            $q->where('is_published', true)->orWhere('status', 'active');
        });

    if ($lmsCoursesQuery->count() >= 4) {
        $trainingModules = $lmsCoursesQuery->inRandomOrder()->take(4)->get();
    } else {
        $courses = \App\Models\LmsCourse::with(['teacher.user', 'school', 'subject', 'classroom'])->get();
        $fallbackMods = \App\Models\TrainingModule::published()->with(['author.teacher'])->take(max(0, 4 - $courses->count()))->get();
        $trainingModules = $courses->concat($fallbackMods)->take(4);
    }

    // === DATA REALTIME UNTUK HOMEPAGE ===
    $activeAcademicYear = \App\Models\AcademicYear::where('is_active', true)->first();

    // Statistik utama
    $totalStudents = $activeAcademicYear 
        ? \App\Models\Student::where('status', 'aktif')->whereHas('studentClasses', function($q) use ($activeAcademicYear) {
            $q->where('academic_year_id', $activeAcademicYear->id)->where('status', 'aktif');
        })->count() 
        : 0;
    $totalTeachers = \App\Models\Teacher::where('is_active', true)->count();
    $totalSchools = \App\Models\School::schoolsOnly()->where('is_active', true)->count();
    $totalAlumni = \App\Models\Student::where('status', 'lulus')->count() 
        + \App\Models\Alumni::count() 
        + \App\Models\AlumniDirectory::where('is_approved', true)->count();

    // Statistik platform digital
    $totalCourses = \App\Models\LmsCourse::where('is_published', true)->count();
    $totalExams = \App\Models\CbtExam::count();
    $totalForumThreads = \App\Models\ForumThread::count();

    // 6. JUARA PRESTASI (4 Acak)
    $achievements = \App\Models\StudentCounselingRecord::where('record_type', 'penghargaan')
        ->with(['student.school', 'student.user'])
        ->inRandomOrder()
        ->take(4)
        ->get();
    $totalAchievements = \App\Models\StudentCounselingRecord::where('record_type', 'penghargaan')->count();

    // Sekolah dengan statistik
    $schools = \App\Models\School::schoolsOnly()
        ->where('is_active', true)
        ->withCount([
            'students' => fn($q) => $q->where('status', 'aktif')->whereHas('studentClasses', function($sq) use ($activeAcademicYear) {
                if ($activeAcademicYear) {
                    $sq->where('academic_year_id', $activeAcademicYear->id)->where('status', 'aktif');
                }
            }),
            'teachers' => fn($q) => $q->where('is_active', true),
            'classrooms' => fn($q) => $q->where('is_active', true)
                ->when($activeAcademicYear, fn($query) => $query->where('academic_year_id', $activeAcademicYear->id))
                ->has('schedules')
        ])
        ->orderBy('type')
        ->get();

    // PSB - Active Wave (hanya jika aktif, tanggal berlaku, dan unit sekolah membuka PSB)
    $activeWave = \App\Models\RegistrationWave::where('is_active', true)
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now())
        ->whereHas('school', fn($q) => $q->where('is_active', true)->where('psb_is_active', true))
        ->first();
    $totalApplicants = $activeAcademicYear
        ? \App\Models\Applicant::where('academic_year_id', $activeAcademicYear->id)->count()
        : 0;

    // Alumni Terbaru
    $recentAlumnis = \App\Models\AlumniDirectory::with('school')
        ->whereNotNull('message')
        ->latest()
        ->take(3)
        ->get();

    // === SHOWCASE PKL TERBAIK UNTUK HOMEPAGE ===
    // 3. LOGBOOK DUDI (2 Acak)
    $pklLogs = \App\Models\PklLog::where('status', 'approved')
        ->whereNotNull('photo')
        ->with(['placement.student.school', 'placement.student.user', 'placement.dudi'])
        ->inRandomOrder()
        ->take(2)
        ->get()
        ->map(function($log) {
            $rawActivity = $log->activity ?? '';
            $kegiatan = '';
            $alat = '';
            $pengetahuan = '';

            // Ekstrak blok teks terstruktur
            if (preg_match('/Kegiatan(?:[^\n:]*):\s*(.*?)(?=\n\s*Alat|\n\s*Pengetahuan|\n\s*Ilmu|$)/is', $rawActivity, $m)) {
                $kegiatan = trim($m[1]);
            }
            if (preg_match('/Alat(?:[^\n:]*):\s*(.*?)(?=\n\s*Pengetahuan|\n\s*Ilmu|$)/is', $rawActivity, $m)) {
                $alat = trim($m[1]);
            }
            if (preg_match('/(?:Pengetahuan|Ilmu)(?:[^\n:]*):\s*(.*?)$/is', $rawActivity, $m)) {
                $pengetahuan = trim($m[1]);
            }

            if (!empty($kegiatan) || !empty($alat) || !empty($pengetahuan)) {
                $parts = [];
                if (!empty($kegiatan)) {
                    $parts[] = 'Kegiatan ' . rtrim($kegiatan, '.,;');
                }
                if (!empty($alat)) {
                    $parts[] = 'dengan alat ' . rtrim($alat, '.,;');
                }
                if (!empty($pengetahuan)) {
                    $parts[] = 'dan pengetahuan ttg ' . rtrim($pengetahuan, '.,;');
                }
                $formattedDesc = implode(' ', $parts) . '.';
            } else {
                $cleanAct = preg_replace('/\s+/', ' ', trim($rawActivity));
                $formattedDesc = !empty($cleanAct) 
                    ? 'Kegiatan ' . rtrim($cleanAct, '.,;') . '.' 
                    : 'Kegiatan praktik kerja lapangan industri bersama mitra DUDI.';
            }

            return [
                'type' => 'logbook',
                'photo' => $log->photo_url,
                'description' => $formattedDesc,
                'person_name' => $log->placement?->student?->full_name ?? 'Siswa PKL',
                'person_photo' => $log->placement?->student?->photo_url ?? $log->placement?->student?->user?->avatar_url ?? null,
                'school_name' => $log->placement?->student?->school?->name ?? 'SMKS Pembda Nias',
                'dudi_name' => $log->placement?->dudi?->name ?? $log->placement?->company_name ?? 'Mitra DUDI',
                'date' => $log->log_date?->translatedFormat('d M Y') ?? '-',
            ];
        });

    // 4. MONITORING GURU (2 Acak)
    $pklMonitorings = \App\Models\PklMonitoring::whereNotNull('photo_path')
        ->with(['teacher.user', 'dudi'])
        ->inRandomOrder()
        ->take(2)
        ->get()
        ->map(fn($m) => [
            'type' => 'monitoring',
            'photo' => asset('storage/' . $m->photo_path),
            'description' => \Illuminate\Support\Str::limit($m->notes, 150),
            'person_name' => $m->teacher?->full_name ?? 'Guru Pembimbing',
            'person_photo' => $m->teacher?->photo_url ?? $m->teacher?->user?->avatar_url ?? null,
            'school_name' => 'Guru Pembimbing',
            'dudi_name' => $m->dudi?->name ?? '-',
            'date' => $m->monitoring_date?->translatedFormat('d M Y') ?? '-',
        ]);

    $pklShowcase = $pklLogs->concat($pklMonitorings)->shuffle()->values();
    $totalApprovedLogs = \App\Models\PklLog::where('status', 'approved')->count();
    $totalMonitorings = \App\Models\PklMonitoring::count();
    $totalDudi = \App\Models\Dudi::count();
    $dudiLocations = \App\Models\Dudi::with('school')->orderBy('name', 'asc')->get();

    // === TOP HALL OF FAME PEMBDA ELITE FOR HOMEPAGE (6 HONOREES STRIP) ===
    $topStudentsElite = \App\Models\Reputation::with(['user.student.classroom.school', 'user.student.school', 'user.school', 'user.badges'])
        ->whereHas('user', function($q) {
            $q->where('role', 'siswa');
        })
        ->orderBy('total_points', 'desc')
        ->take(6)
        ->get();

    $topTeachersElite = \App\Models\Reputation::with(['user.teacher.school', 'user.badges'])
        ->whereHas('user', function($q) {
            $q->where('role', 'guru')
              ->where('username', '!=', 'yulzega')
              ->where('name', 'NOT LIKE', '%Yulianus Zega%');
        })
        ->orderBy('total_points', 'desc')
        ->take(6)
        ->get();

    // Tema Beranda Hari Besar (Event Theme)
    $homepageTheme = \App\Models\Setting::getValue('homepage_theme', 'regular');
    if (!in_array($homepageTheme, ['regular', 'kemerdekaan', 'paskah', 'natal', 'pahlawan', 'pendidikan'])) {
        $homepageTheme = 'regular';
    }

    // Program & Konsentrasi Keahlian SMKS Pembda Nias (Database Asli)
    $smkProgramKeahlians = \App\Models\ProgramKeahlian::with(['konsentrasiKeahlians' => function($q) {
        $q->where('is_active', true);
    }])->where('is_active', true)->get();

    // === SHOWCASE FINAL PROJECT (PENELITIAN & PROJECT AKHIR) ===
    // 1. 4 PENELITIAN KELAS XII SMA (Acak)
    $penelitianSMA = \App\Models\FinalProject::with(['student.school', 'student.user', 'members.student.school', 'members.student.user'])
        ->where(function($q) {
            $q->whereHas('student.school', fn($sq) => $sq->where('name', 'LIKE', '%SMA%'))
              ->orWhere('type', 'penelitian_ilmiah')
              ->orWhere('type', 'research');
        })
        ->whereNotIn('status', ['rejected', 'draft'])
        ->inRandomOrder()
        ->take(4)
        ->get();

    // 2. 4 PROJECT KELAS XII SMK (Acak)
    $projectSMK = \App\Models\FinalProject::with(['student.school', 'student.user', 'members.student.school', 'members.student.user'])
        ->where(function($q) {
            $q->whereHas('student.school', fn($sq) => $sq->where('name', 'LIKE', '%SMK%'))
              ->orWhere('type', 'project_akhir')
              ->orWhere('type', 'project')
              ->orWhere('type', 'smk');
        })
        ->whereNotIn('status', ['rejected', 'draft'])
        ->inRandomOrder()
        ->take(4)
        ->get();

    $finalProjectsShowcase = $penelitianSMA->concat($projectSMK)->shuffle()->values();

    // === KEGIATAN SISWA & EKSTRAKURIKULER ===
    $extracurriculars = \App\Models\Extracurricular::with('school')
        ->withCount('activeMembers')
        ->where('is_active', true)
        ->orderBy('scope', 'asc') // Yayasan first if scope is yayasan
        ->get();

    $totalFinalProjects = \App\Models\FinalProject::whereNotIn('status', ['rejected', 'draft'])->count();
    $totalPklAll = $totalApprovedLogs + $totalMonitorings;
    $totalAllShowcase = $totalFinalProjects + $totalPklAll + $totalCourses + $totalAchievements;

    // Pastikan halaman beranda tidak dicache oleh server (LiteSpeed) maupun browser
    // agar status tombol "Login" vs "Dashboard" selalu ter-update secara real-time.
    return response(view('index', compact(
        'activeAcademicYear',
        'news', 'galleryItems', 'trainingModules',
        'totalStudents', 'totalTeachers', 'totalSchools', 'totalAlumni',
        'totalCourses', 'totalExams', 'totalForumThreads',
        'achievements', 'totalAchievements',
        'schools', 'activeWave', 'totalApplicants',
        'recentAlumnis',
        'pklShowcase', 'totalApprovedLogs', 'totalMonitorings', 'totalDudi', 'dudiLocations',
        'topStudentsElite', 'topTeachersElite', 'homepageTheme',
        'smkProgramKeahlians', 'finalProjectsShowcase', 'extracurriculars',
        'totalFinalProjects', 'totalPklAll', 'totalAllShowcase'
    )))
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('X-LiteSpeed-Cache-Control', 'no-cache');
})->name('home');

// Public PKL Placements GPS Map (SMKS Swasta Pembda Nias)
Route::get('/pkl/peta-sebaran', function () {
    $activeAcademicYear = \App\Models\AcademicYear::where('is_active', true)->first();
    
    $query = \App\Models\PklPlacement::with(['student.school', 'logs' => function($q) {
        $q->whereNotNull('latitude')
          ->whereNotNull('longitude')
          ->latest('log_date');
    }]);

    if ($activeAcademicYear) {
        $query->where('academic_year_id', $activeAcademicYear->id);
    }

    $placements = $query->get();
    $mapData = [];

    foreach ($placements as $p) {
        if ($p->logs->isNotEmpty()) {
            $latestLog = $p->logs->first();
            $mapData[] = [
                'student_name' => $p->student?->full_name ?? 'Siswa PKL',
                'school_name' => $p->student?->school?->name ?? 'SMKS Swasta Pembda Nias',
                'company_name' => $p->company_name ?? $p->dudi?->name ?? 'Mitra DUDI',
                'lat' => (float)$latestLog->latitude,
                'lng' => (float)$latestLog->longitude,
                'photo' => $p->student?->photo_url ?? null,
                'log_date' => $latestLog->log_date ? $latestLog->log_date->format('d M Y') : date('d M Y'),
                'activity' => \Illuminate\Support\Str::limit($latestLog->activity ?? 'Praktik Industri', 120),
            ];
        }
    }

    $totalDudi = \App\Models\Dudi::count();

    return view('public.pkl_map', compact('mapData', 'activeAcademicYear', 'totalDudi'));
})->name('public.pkl.map');

Route::get('/peta-pkl', fn() => redirect()->route('public.pkl.map'))->name('peta.pkl');

// Public Download Route for offline learning
Route::get('/pelatihan/{trainingModule}/download', [App\Http\Controllers\TrainingController::class, 'download'])->name('training.download');

// Fallback Route for Mars Audio (Hostinger workaround)
Route::get('/audio/mars-pembda.mp4', function () {
    $path1 = public_path('audio/mars-pembda.mp4');
    $path2 = base_path('../audio/mars-pembda.mp4'); // if placed in public_html/audio/
    
    if (file_exists($path1)) return response()->file($path1);
    if (file_exists($path2)) return response()->file($path2);
    abort(404, 'Audio file not found in either public_html/audio or pembdahub/public/audio');
});

// Route to Seed Landing Page Content from Browser (Safe & Secured with Key)
Route::get('/seed-landing-content', function () {
    $secret = request('key');
    if ($secret !== 'pembda2026') {
        return response("Unauthorized. Please provide the correct key.", 403);
    }
    
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'LandingPageContentSeeder',
            '--force' => true
        ]);
        return "SUCCESS: Berita dan Galeri terbaru berhasil di-seed ke database!";
    } catch (\Exception $e) {
        return "ERROR: " . $e->getMessage();
    }
});

// Diagnostic Route: Check Everything
Route::get('/check-system', function () {
    if (!str_contains(base_path(), 'domains')) {
        return "Hanya untuk lingkungan Hosting.";
    }

    $results = [];
    try {
        // 1. Clear All Caches
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $results[] = "System Cache: CLEARED SUCCESS (config, route, view, cache)";

        // 2. Check Database Connection & Table
        $dbName = config('database.connections.mysql.database');
        $results[] = "Target Database: <b>$dbName</b>";
        
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('students');
        $hasRfid = in_array('rfid_uid', $columns);
        $results[] = "Column 'rfid_uid' in 'students': " . ($hasRfid ? "<b style='color:green'>YES</b>" : "<b style='color:red'>NO</b>");

        // 3. Test Raw Query
        $uid = request('uid', '5ABB2402');
        $studentRaw = \Illuminate\Support\Facades\DB::table('students')->where('rfid_uid', $uid)->first();
        $results[] = "Raw query test (UID $uid): " . ($studentRaw ? "FOUND (" . $studentRaw->full_name . ")" : "NOT FOUND");

        // 4. Test Eloquent Query
        $studentEloquent = \App\Models\Student::where('rfid_uid', $uid)->first();
        $results[] = "Eloquent query test (UID $uid): " . ($studentEloquent ? "FOUND (" . $studentEloquent->full_name . ")" : "NOT FOUND");

        // 5. Check Attendance Table Details
        $attDetails = \Illuminate\Support\Facades\DB::select("DESCRIBE attendances");
        $html_att = "<h3>'attendances' Table Schema:</h3><table border='1'><tr><th>Field</th><th>Type</th><th>Null</th></tr>";
        foreach ($attDetails as $detail) {
            $html_att .= "<tr><td>{$detail->Field}</td><td>{$detail->Type}</td><td>{$detail->Null}</td></tr>";
        }
        $html_att .= "</table>";
        $results[] = $html_att;

        // 6. Check Student Table Details
        $stuDetails = \Illuminate\Support\Facades\DB::select("DESCRIBE students");
        $html_stu = "<h3>'students' Table Schema:</h3><table border='1'><tr><th>Field</th><th>Type</th><th>Null</th></tr>";
        foreach ($stuDetails as $detail) {
            $html_stu .= "<tr><td>{$detail->Field}</td><td>{$detail->Type}</td><td>{$detail->Null}</td></tr>";
        }
        $html_stu .= "</table>";
        $results[] = $html_stu;

        // 7. Check if latest meeting files are deployed
        $meetingFile = base_path('resources/views/guru/lms/meeting.blade.php');
        if (file_exists($meetingFile)) {
            $meetingContent = file_get_contents($meetingFile);
            $hasHeightFix = str_contains($meetingContent, 'calc(100vh - 76px)');
            $results[] = "File 'meeting.blade.php': " . ($hasHeightFix ? "<b style='color:green'>UP-TO-DATE (Has Height Fix)</b>" : "<b style='color:red'>OUTDATED (Missing Height Fix)</b>");
        } else {
            $results[] = "File 'meeting.blade.php': <b style='color:red'>NOT FOUND</b>";
        }

        $headersFile = base_path('app/Http/Middleware/SecurityHeaders.php');
        if (file_exists($headersFile)) {
            $headersContent = file_get_contents($headersFile);
            $hasWildcard = str_contains($headersContent, 'camera=*');
            $results[] = "File 'SecurityHeaders.php': " . ($hasWildcard ? "<b style='color:green'>UP-TO-DATE (Has Wildcard Permissions-Policy)</b>" : "<b style='color:red'>OUTDATED (Restricted/Old Policy)</b>");
        } else {
            $results[] = "File 'SecurityHeaders.php': <b style='color:red'>NOT FOUND</b>";
        }

        return "<h3>System Diagnostic Results:</h3><ul><li>" . implode("</li><li>", $results) . "</li></ul>";
    } catch (\Exception $e) {
        return "<h3>CRITICAL ERROR during diagnostic:</h3><pre>" . $e->getMessage() . "</pre>";
    }
});

// Diagnostic Route: Show Logs
Route::get('/show-logs', function () {
    if (!str_contains(base_path(), 'domains')) {
        return "Hanya untuk lingkungan Hosting.";
    }

    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return "Log file not found at: $logFile";
    }

    $lines = file($logFile);
    $lastLines = array_slice($lines, -50); // Take last 50 lines
    
    return "<pre>" . implode("", $lastLines) . "</pre>";
});

// Diagnostic Route: Full Flow Test
Route::get('/test-full', function () {
    if (!str_contains(base_path(), 'domains')) {
        return "Hanya untuk lingkungan Hosting.";
    }

    $results = [];
    $uid = request('uid', '5ABB2402');
    
    try {
        // Step 1: Find Student (MATCH CONTROLLER LOGIC)
        $student = \App\Models\Student::where('rfid_uid', $uid)
            ->whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
            ->first();
        if (!$student) throw new \Exception("Step 1 Failed: Student with UID $uid not found or status not active (" . (\App\Models\Student::where('rfid_uid', $uid)->first() ? 'Exists but inactive' : 'NOT FOUND') . ")");
        $results[] = "Step 1: Student Found (" . $student->full_name . ")";

        // Step 2: Check Student Classes
        $studentClass = $student->studentClasses()
            ->where('status', 'aktif')
            ->whereHas('academicYear', function($q) { $q->where('is_active', true); })
            ->first();
        if (!$studentClass) throw new \Exception("Step 2 Failed: No active Classroom for student.");
        $results[] = "Step 2: Active Class Found (" . ($studentClass->classroom->class_name ?? 'NAMA KOSONG') . ")";

        // Step 3: Check Current Attendance
        $today = date('Y-m-d');
        $existing = \App\Models\Attendance::where('student_id', $student->id)->where('date', $today)->first();
        $results[] = "Step 3: Existing Attendance Check: " . ($existing ? "Exists (ID: " . $existing->id . ")" : "None today");

        // Step 4: Simulate Create (Dry Run using Transaction to avoid DB clutter)
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $status = 'hadir';
            $currentTime = date('H:i:s');
            $att = \App\Models\Attendance::create([
                'student_id'   => $student->id,
                'classroom_id' => $studentClass->classroom_id,
                'date'         => $today,
                'time_in'      => $currentTime,
                'status'       => $status,
                'recorded_via' => 'rfid',
                'device_id'    => 'TEST-DEBUG', 
            ]);
            $results[] = "Step 4: Attendance Creation SUCCESS (ID: " . $att->id . ")";
            \Illuminate\Support\Facades\DB::rollBack();
            $results[] = "Step 5: Rollback Success (Data cleaned up).";
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw new \Exception("Step 4 Failed: " . $e->getMessage());
        }

        return "<h3>Full Flow Test SUCCESS:</h3><ul><li>" . implode("</li><li>", $results) . "</li></ul>";
    } catch (\Exception $e) {
        return "<h3>TEST FAILED:</h3><p style='color:red'>" . $e->getMessage() . "</p><ul><li>" . implode("</li><li>", $results) . "</li></ul>";
    }
});
Route::prefix('pendaftaran')->name('public.registration.')->group(function () {
    Route::get('/', [App\Http\Controllers\PublicRegistrationController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\PublicRegistrationController::class, 'store'])->name('store');
    Route::get('/sukses/{registrationNumber}', [App\Http\Controllers\PublicRegistrationController::class, 'success'])->name('success');
    Route::get('/cek-status', [App\Http\Controllers\PublicRegistrationController::class, 'check'])->name('check');
    Route::post('/cek-status', [App\Http\Controllers\PublicRegistrationController::class, 'checkStatus'])->name('check.submit');
    Route::post('/upload-document', [App\Http\Controllers\PublicRegistrationController::class, 'uploadDocument'])->name('upload-document');
});

// Live Game (PembdaHUB Live) - Accessible to anyone with PIN
Route::prefix('live')->name('live.')->group(function () {
    Route::get('/', [App\Http\Controllers\LmsLiveController::class, 'playerJoin'])->name('join');
    Route::post('/join', [App\Http\Controllers\LmsLiveController::class, 'processJoin'])->name('processJoin');
    Route::get('/{session}', [App\Http\Controllers\LmsLiveController::class, 'playerUI'])->name('play');
    Route::get('/{session}/poll', [App\Http\Controllers\LmsLiveController::class, 'pollPlayer'])->name('poll');
    Route::post('/{session}/answer', [App\Http\Controllers\LmsLiveController::class, 'submitAnswer'])->name('answer');
});

// API for dynamic form (outside prefix so accessible from anywhere)
Route::get('/api/program-keahlian/{schoolId}', [App\Http\Controllers\PublicRegistrationController::class, 'getProgramKeahlian'])->name('api.program');
Route::get('/api/konsentrasi-keahlian/{programId}', [App\Http\Controllers\PublicRegistrationController::class, 'getKonsentrasiKeahlian'])->name('api.konsentrasi');

// IKA PEMBDA - Public Alumni Registration & Directory
Route::prefix('ika-pembda')->name('ika.')->group(function () {
    Route::get('/', [App\Http\Controllers\PublicAlumniController::class, 'directory'])->name('index');
    Route::get('/direktori', [App\Http\Controllers\PublicAlumniController::class, 'directory'])->name('directory');
    Route::get('/daftar', [App\Http\Controllers\PublicAlumniController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [App\Http\Controllers\PublicAlumniController::class, 'registerSubmit'])->middleware('throttle:5,1')->name('register.submit');
});
Route::get('/alumni/direktori-publik', [App\Http\Controllers\PublicAlumniController::class, 'directory'])->name('alumni.directory');
Route::get('/alumni/daftar-publik', [App\Http\Controllers\PublicAlumniController::class, 'registerForm'])->name('alumni.register');

// PSB Testing & Simulation Routes (protected - admin only)
Route::prefix('psb-test')->name('psb.test.')->middleware('auth', 'role:superadmin,admin_sekolah')->group(function () {
    Route::get('/', [App\Http\Controllers\PSBTestController::class, 'index'])->name('index');
    Route::get('/preview/email/{type}/{registrationNumber}', [App\Http\Controllers\PSBTestController::class, 'previewEmail'])->name('preview.email');
    Route::get('/preview/whatsapp/{type}/{registrationNumber}', [App\Http\Controllers\PSBTestController::class, 'previewWhatsApp'])->name('preview.whatsapp');
    Route::get('/preview/sms/{type}/{registrationNumber}', [App\Http\Controllers\PSBTestController::class, 'previewSMS'])->name('preview.sms');
    Route::post('/simulate', [App\Http\Controllers\PSBTestController::class, 'simulateSend'])->name('simulate');
});

// Public QR Code Document Verification Route
Route::get('/verifikasi-surat/{hash}', [App\Http\Controllers\PublicLetterVerificationController::class, 'verify'])->name('public.letters.verify');

// General Dashboard Redirect Route
Route::get('/dashboard', function () {
    if (auth()->check()) {
        $user = auth()->user();
        $role = session('active_role', $user->role);

        $referer = request()->header('referer') ?? '';
        $userAgent = request()->userAgent() ?? '';
        $secChUaMobile = request()->header('sec-ch-ua-mobile') === '?1';

        $isMobile = request()->is('m/*') 
            || request()->is('m')
            || str_contains($referer, '/m/')
            || request()->cookie('app_mode') === 'mobile'
            || session('is_mobile_app')
            || $secChUaMobile
            || preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

        if ($isMobile && !session('prefer_desktop') && in_array($role, ['siswa', 'guru', 'pegawai', 'orang_tua', 'alumni'])) {
            cookie()->queue('app_mode', 'mobile', 60 * 24 * 365);
            session(['is_mobile_app' => true]);
            return redirect()->route('mobile.dashboard');
        }

        $url = match ($role) {
            'superadmin', 'kepala_sekolah' => route('admin.dashboard'),
            'admin_sekolah' => route('sekolah.dashboard'),
            'bendahara' => route('treasurer.dashboard'),
            'ketua_yayasan' => route('yayasan.dashboard'),
            'guru', 'pegawai' => route('guru.dashboard'),
            'siswa' => route('siswa.dashboard'),
            'orang_tua' => route('orangtua.dashboard'),
            'alumni' => route('alumni.dashboard'),
            default => url('/'),
        };

        return redirect($url);
    }
    return redirect()->route('login');
})->name('dashboard');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1') // Rate limit: 5 attempts per minute
        ->name('login.submit');

    Route::get('/register', function() {
        return redirect()->route('ika.register');
    })->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

// Protected routes - require authentication
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Change Password (forced on first login)
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change.update');
    Route::post('/switch-role', [AuthController::class, 'switchRole'])->name('switch-role');
    Route::post('/switch-school', [App\Http\Controllers\Auth\AuthController::class, 'switchSchool'])->name('switch-school');

        // Notification Center
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markRead'])->name('mark-read');
            Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('mark-all-read');
            Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::get('/latest', [App\Http\Controllers\NotificationController::class, 'latest'])->name('latest');
        });

    // Profile Settings
    Route::get('/profile/settings', [App\Http\Controllers\ProfileSettingsController::class, 'edit'])->name('profile.settings');
    Route::put('/profile/settings', [App\Http\Controllers\ProfileSettingsController::class, 'update'])->name('profile.settings.update');
    Route::put('/profile/biodata', [App\Http\Controllers\ProfileSettingsController::class, 'updateBiodata'])->name('profile.biodata.update');
    Route::post('/profile/photo', [App\Http\Controllers\ProfileSettingsController::class, 'updatePhoto'])->name('profile.photo.update');

    // ============================================================
    //  STEAMPRENEUR SMK 2026 - KEMENDIKDASMEN INNOVATION HUB
    //  Akses Khusus: Super Admin, Kepala Sekolah, Yayasan & Siswa Peserta
    // ============================================================
    Route::prefix('steam-competition')->name('steam.')->group(function () {
        Route::get('/', [App\Http\Controllers\SteamCompetitionController::class, 'index'])->name('index');
        Route::get('/proposal', [App\Http\Controllers\SteamCompetitionController::class, 'proposal'])->name('proposal');
        Route::get('/pitch-deck', [App\Http\Controllers\SteamCompetitionController::class, 'pitchDeck'])->name('pitch-deck');
        Route::get('/video-script', [App\Http\Controllers\SteamCompetitionController::class, 'videoScript'])->name('video-script');
        Route::post('/upload', [App\Http\Controllers\SteamCompetitionController::class, 'uploadDocument'])->name('upload');
        Route::delete('/document/{id}', [App\Http\Controllers\SteamCompetitionController::class, 'deleteDocument'])->name('document.delete');
        Route::post('/settings', [App\Http\Controllers\SteamCompetitionController::class, 'updateSettings'])->name('settings');
    });

    // Admin Sekolah Routes - Redirect ke /admin (shared with SuperAdmin)
    Route::prefix('sekolah')->name('sekolah.')->group(function () {
        Route::get('/dashboard', function () {
            // Redirect admin_sekolah ke /admin/dashboard (shared route dengan filtering)
            return redirect()->route('admin.dashboard');
        })->name('dashboard');
    });

    // Alumni Routes
    Route::prefix('alumni-portal')->name('alumni.')->middleware('role:alumni,superadmin,ketua_yayasan,admin_yayasan,admin_sekolah,guru,kepala_sekolah,bendahara')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AlumniDashboardController::class, 'index'])->name('dashboard');

        // Forum Alumni Khusus Unit Sekolah
        Route::get('/forum', [App\Http\Controllers\AlumniForumController::class, 'index'])->name('forum.index');
        Route::get('/forum/create', [App\Http\Controllers\AlumniForumController::class, 'create'])->name('forum.create');
        Route::post('/forum', [App\Http\Controllers\AlumniForumController::class, 'store'])->name('forum.store');
        Route::get('/forum/{forum}', [App\Http\Controllers\AlumniForumController::class, 'show'])->name('forum.show');
        Route::post('/forum/{forum}/reply', [App\Http\Controllers\AlumniForumController::class, 'reply'])->name('forum.reply');

        // Chat / Pesan Alumni
        Route::get('/chat', [App\Http\Controllers\AlumniMessageController::class, 'index'])->name('chat.index');
        Route::get('/chat/{contact}', [App\Http\Controllers\AlumniMessageController::class, 'show'])->name('chat.show');
        Route::post('/chat/{contact}', [App\Http\Controllers\AlumniMessageController::class, 'store'])->name('chat.store');

        // Kontribusi Alumni & Rekening Yayasan
        Route::get('/kontribusi', [App\Http\Controllers\AlumniController::class, 'kontribusi'])->name('kontribusi');
        Route::get('/sejarah-yayasan', [App\Http\Controllers\AlumniController::class, 'sejarah'])->name('sejarah');
    });

    // Reputation & Hall of Fame
    Route::get('/hall-of-fame', [App\Http\Controllers\Reputation\LeaderboardController::class, 'index'])->name('reputation.leaderboard');

    // Pembda Space (Alias & Main Route)
    Route::get('/space', [App\Http\Controllers\ForumController::class, 'index'])->name('space.index');

    // Hub Forum (Pembda Space)
    Route::prefix('forum')->name('forum.')->group(function () {
        Route::get('/', [App\Http\Controllers\ForumController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ForumController::class, 'create'])->name('create');
        Route::post('/store', [App\Http\Controllers\ForumController::class, 'store'])->name('store');
        
        // Pembda Tower (Menara Prestasi)
        Route::get('/tower/state', [App\Http\Controllers\TowerController::class, 'getState'])->name('tower.state');
        Route::post('/tower/brick', [App\Http\Controllers\TowerController::class, 'placeBrick'])->name('tower.place');
        Route::post('/tower/like/{id}', [App\Http\Controllers\TowerController::class, 'likeBrick'])->name('tower.like');

        // Legacy fallback puzzle routes to prevent stale view cache crashes
        Route::get('/puzzle', function() { return response()->json(['success' => false]); })->name('puzzle.state');
        Route::post('/puzzle/place', function() { return response()->json(['success' => false]); })->name('puzzle.place');
        Route::post('/puzzle/reset', function() { return response()->json(['success' => false]); })->name('puzzle.reset');
        Route::get('/{thread}', [App\Http\Controllers\ForumController::class, 'show'])->name('show');
        Route::post('/{thread}/reply', [App\Http\Controllers\ForumController::class, 'reply'])->name('reply');
        Route::post('/{thread}/like', [App\Http\Controllers\ForumController::class, 'like'])->name('like');
        Route::delete('/{thread}', [App\Http\Controllers\ForumController::class, 'destroy'])->name('destroy');
        Route::get('/{thread}/edit', [App\Http\Controllers\ForumController::class, 'edit'])->name('edit');
        Route::put('/{thread}', [App\Http\Controllers\ForumController::class, 'update'])->name('update');
        Route::put('/reply/{reply}', [App\Http\Controllers\ForumController::class, 'updateReply'])->name('reply.update');
        Route::delete('/reply/{reply}', [App\Http\Controllers\ForumController::class, 'destroyReply'])->name('reply.destroy');
        Route::post('/reply/{reply}/accept', [App\Http\Controllers\ForumController::class, 'acceptReply'])->name('reply.accept');
        
        // Project, Committee & Charity Actions
        Route::post('/{thread}/join', [App\Http\Controllers\ForumController::class, 'join'])->name('join');
        Route::post('/member/{member}/approve', [App\Http\Controllers\ForumController::class, 'approveMember'])->name('member.approve');
        Route::post('/member/{member}/reject', [App\Http\Controllers\ForumController::class, 'rejectMember'])->name('member.reject');
        Route::post('/{thread}/status', [App\Http\Controllers\ForumController::class, 'updateStatus'])->name('status.update');
        Route::post('/{thread}/donate', [App\Http\Controllers\ForumController::class, 'donate'])->name('donate');
        
        // Reactions
        Route::post('/{thread}/react', [App\Http\Controllers\ForumController::class, 'react'])->name('react');
        Route::post('/reply/{reply}/react', [App\Http\Controllers\ForumController::class, 'reactReply'])->name('reply.react');

        // Polls
        Route::post('/{thread}/poll', [App\Http\Controllers\ForumController::class, 'createPoll'])->name('poll.create');
        Route::post('/poll/{option}/vote', [App\Http\Controllers\ForumController::class, 'votePoll'])->name('poll.vote');
    });

    // Alumni & Tracer Study Routes
    Route::prefix('alumni')->name('alumni.')->group(function () {
        Route::get('/tracer-study', [App\Http\Controllers\AlumniController::class, 'tracerForm'])->name('tracer.form');
        Route::post('/tracer-study', [App\Http\Controllers\AlumniController::class, 'tracerSubmit'])->name('tracer.submit');
        Route::get('/jobs', [App\Http\Controllers\AlumniController::class, 'jobsIndex'])->name('jobs.index');
    });

    // Modul Pelatihan (All Roles - Read Only)
    Route::prefix('pelatihan')->name('training.')->group(function () {
        Route::get('/', [App\Http\Controllers\TrainingController::class, 'index'])->name('index');
        Route::get('/{trainingModule}', [App\Http\Controllers\TrainingController::class, 'show'])->name('show');
    });

    // Note: Admin, Guru, Siswa, Treasurer, and OrangTua routes are loaded
    // from their respective files in routes/ directory.
    // See: admin.php, guru.php, siswa.php, treasurer.php, orangtua.php
});

// Public PKL Mentor Signed URL Routes
Route::prefix('mentor')->name('mentor.')->group(function () {
    Route::get('/pkl/{token}', [App\Http\Controllers\PklMentorController::class, 'portal'])->name('pkl.portal');
    Route::post('/pkl/{token}/log/{log}/approve', [App\Http\Controllers\PklMentorController::class, 'approveLog'])->name('pkl.log.approve');
    Route::post('/pkl/{token}/log/{log}/reject', [App\Http\Controllers\PklMentorController::class, 'rejectLog'])->name('pkl.log.reject');
    Route::post('/pkl/{token}/grade', [App\Http\Controllers\PklMentorController::class, 'submitGrade'])->name('pkl.grade.store');
});

Route::get('/run-migrations', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized.');
    }
    try {
        echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
        echo "<h1>=== RUNNING DATABASE MIGRATIONS ===</h1>\n";
        
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        
        echo $output;
        echo "\nExit Code: " . $exitCode . "\n\n";

        echo "<h1>=== RUNNING SURVEY SEEDER ===</h1>\n";
        $surveySeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SurveySeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nSurvey Seeder Exit Code: " . $surveySeederExitCode . "\n\n";

        echo "<h1>=== RUNNING TEFA EMPLOYEE SEEDER ===</h1>\n";
        $tefaSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TefaEmployeeSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nTefa Seeder Exit Code: " . $tefaSeederExitCode . "\n\n";

        echo "<h1>=== RUNNING MONDAY INSPIRATION SEEDER ===</h1>\n";
        $miSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'MondayInspirationSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nMonday Inspiration Seeder Exit Code: " . $miSeederExitCode . "\n\n";

        echo "<h1>=== CLEANING UP DUMMY MIKROKONTROLER & X TAV LMS COURSE ===</h1>\n";
        $dummyCourses = \App\Models\LmsCourse::withTrashed()
            ->where(function ($q) {
                $q->where('code', 'LIKE', 'LMS-MIKRO-XTAV%')
                  ->orWhere('course_name', 'Bahasa Pemrograman Mikrokontroler');
            })
            ->get();

        foreach ($dummyCourses as $dc) {
            \App\Models\LmsClass::where('course_id', $dc->id)->delete();
            \App\Models\LmsModule::where('course_id', $dc->id)->forceDelete();
            \App\Models\LmsAssignment::where('course_id', $dc->id)->forceDelete();
            \App\Models\LmsQuiz::where('course_id', $dc->id)->forceDelete();
            $dc->forceDelete();
            echo "Deleted dummy course: {$dc->id} ({$dc->course_name})\n";
        }

        $xtavClass = \App\Models\Classroom::where('class_name', 'X TAV')->first();
        if ($xtavClass) {
            \App\Models\LmsClass::where('classroom_id', $xtavClass->id)->delete();
            echo "Cleared lingering lms_classes for X TAV\n";
        }
        echo "Dummy course and X TAV cleanup completed.\n\n";

        echo "<h1>=== SPP TARIFF SEEDER ===</h1>\n";
        $sppSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SppTariffSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nSPP Tariff Seeder Exit Code: " . $sppSeederExitCode . "\n\n";

        echo "<h1>=== RUNNING PKL SUPERVISOR SEEDER ===</h1>\n";
        $pklSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'PklSupervisorPositionSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nPKL Supervisor Seeder Exit Code: " . $pklSeederExitCode . "\n\n";

        echo "<h1>=== RUNNING SURAT EDARAN HUT RI SEEDER ===</h1>\n";
        $hutRiSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'FoundationLetterHutRiSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();

        echo "<h1>=== RUNNING EXTRACURRICULAR SEEDER ===</h1>\n";
        $ekskulSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'ExtracurricularSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nExtracurricular Seeder Exit Code: " . $ekskulSeederExitCode . "\n\n";

        echo "<h1>=== UPDATING HOMEPAGE SAMBUTAN SETTINGS ===</h1>\n";
        \App\Models\Setting::setValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T');
        \App\Models\Setting::setValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias');
        \App\Models\Setting::setValue('ketua_quote', "Selamat datang di PembdaHUB, platform ekosistem pendidikan digital masa depan Yayasan Perguruan PEMBDA Nias. Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas Kepulauan Nias yang tidak hanya tangguh dan cerdas secara akademis, tetapi juga memiliki integritas karakter yang mulia serta menguasai teknologi modern secara profesional.\n\nSelaras dengan motto abadi perjuangan kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti membangun lingkungan belajar berbasis teknologi digital terkini untuk menjawab tantangan era globalisasi.\n\nMari bersama-sama kita bergandengan tangan—pendidik, siswa, orang tua, dan alumni—melangkah pasti mewujudkan masa depan Nias yang gemilang, berdaya saing tinggi, dan berintegritas!");
        echo "Updated Homepage Sambutan Settings successfully.\n\n";

        echo "<h1>=== SYNCING EMPLOYEE ACCOUNTS ===</h1>\n";
        $syncExitCode = \Illuminate\Support\Facades\Artisan::call('employees:sync-accounts');
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nSync Exit Code: " . $syncExitCode . "\n\n";

        echo "<h1>=== SYNCING TEACHERS AND EMPLOYEES TO USERS ===</h1>\n";
        $teachers = \App\Models\Teacher::all();
        $syncedTeachers = 0;
        foreach ($teachers as $teacher) {
            $updated = false;
            
            // 1. Coba sync dari Employee
            if ($teacher->employee_id) {
                $employee = \App\Models\Employee::find($teacher->employee_id);
                if ($employee && $employee->user_id) {
                    if ($teacher->user_id !== $employee->user_id) {
                        $teacher->user_id = $employee->user_id;
                        $teacher->save();
                        $updated = true;
                    }
                }
            }
            
            // 2. Coba sync berdasarkan nama di tabel Employee jika employee_id tidak ada
            if (!$teacher->user_id) {
                $employee = \App\Models\Employee::where('full_name', $teacher->full_name)->first();
                if ($employee) {
                    $teacher->employee_id = $employee->id;
                    if ($employee->user_id) {
                        $teacher->user_id = $employee->user_id;
                        $updated = true;
                    }
                    $teacher->save();
                }
            }
            
            // 3. Coba sync dari User berdasarkan nama jika user_id masih kosong
            if (!$teacher->user_id) {
                $user = \App\Models\User::where('name', $teacher->full_name)
                    ->where('role', 'guru')
                    ->first();
                if ($user) {
                    $teacher->user_id = $user->id;
                    $teacher->save();
                    $updated = true;
                    
                    if ($teacher->employee_id) {
                        $employee = \App\Models\Employee::find($teacher->employee_id);
                        if ($employee && !$employee->user_id) {
                            $employee->user_id = $user->id;
                            $employee->save();
                        }
                    }
                }
            }

            if ($updated) {
                $syncedTeachers++;
                echo "🔗 Synced Teacher: <b>{$teacher->full_name}</b> (Teacher ID: {$teacher->id}) to User ID: <b>{$teacher->user_id}</b><br>\n";
            }
        }
        echo "Total Guru yang disinkronkan: <b>{$syncedTeachers}</b><br>\n";

        echo "<h1>=== FIXING SPECIFIC ACCOUNTS ===</h1>\n";
        $berlianceUser = \App\Models\User::where('email', 'berliance@pembdahub.com')->first();
        if ($berlianceUser) {
            $berlianceTeacher = \App\Models\Teacher::where('full_name', 'like', '%Berliance Zamira%')->first();
            if ($berlianceTeacher) {
                $berlianceTeacher->user_id = $berlianceUser->id;
                $berlianceTeacher->save();
                
                if ($berlianceTeacher->employee) {
                    $berlianceTeacher->employee->user_id = $berlianceUser->id;
                    $berlianceTeacher->employee->save();
                }
                echo "✅ Sukses: Akun Ibu Berliance (User ID: {$berlianceUser->id}) berhasil ditautkan ke profil Guru (Teacher ID: {$berlianceTeacher->id})!<br>\n";
            }
        }

        // Auto-fix Kepala Sekolah SMKS Swasta Pembda (Ibu Agustiani)
        $agustianiTeacher = \App\Models\Teacher::where('full_name', 'like', '%Agustiani%')->first();
        if ($agustianiTeacher) {
            $smkSchool = \App\Models\School::where('type', 'SMK')
                ->orWhere('name', 'like', '%SMK%')
                ->first();
            if ($smkSchool) {
                $smkSchool->principal_id = $agustianiTeacher->id;
                $smkSchool->save();
                echo "✅ Sukses: SMKS Swasta Pembda (School ID: {$smkSchool->id}) principal_id diset ke Ibu Agustiani (Teacher ID: {$agustianiTeacher->id})!<br>\n";
            }
            if ($agustianiTeacher->user) {
                echo "✅ Akun User Ibu Agustiani (User ID: {$agustianiTeacher->user->id}) siap switch role Kepala Sekolah & Guru!<br>\n";
            }
        }

        echo "<h1>=== DEBUGGING SMAS PRINCIPAL ===</h1>\n";
        $smas = \App\Models\School::where('name', 'like', '%SMAS Pembda 1%')->first();
        if ($smas) {
            echo "🏫 Tipe Sekolah SMAS Pembda 1: <b>{$smas->type}</b><br>\n";
            if (strtoupper($smas->type) === 'YAYASAN') {
                $smas->type = 'SMA';
                $smas->save();
                echo "✅ Tipe Sekolah dikoreksi menjadi SMA!<br>\n";
            }
            $principal = $smas->principal;
            if ($principal) {
                echo "🏫 SMAS Pembda 1 Principal is: <b>{$principal->full_name}</b> (Teacher ID: {$principal->id})<br>\n";
                echo "🔗 Principal User ID: <b>" . ($principal->user_id ?? 'NULL') . "</b><br>\n";
                
                // Force link if it's Berliance
                if (str_contains(strtolower($principal->full_name), 'berliance') && $berlianceUser) {
                    $principal->user_id = $berlianceUser->id;
                    $principal->save();
                    echo "✅ Paksa tautkan Principal SMAS ke User Berliance!<br>\n";
                }
            } else {
                echo "❌ SMAS Pembda 1 TIDAK MEMILIKI Kepala Sekolah di database!<br>\n";
            }
        }

        echo "<h1>=== SEEDING DEFAULT FINAL PROJECT GUIDELINES ===</h1>\n";
        $schools = \App\Models\School::whereIn('type', ['SMA', 'SMK'])->get();
        $adminUser = \App\Models\User::where('role', 'superadmin')->first() 
            ?? \App\Models\User::where('role', 'admin')->first()
            ?? \App\Models\User::first();
            
        if ($adminUser) {
            $year = date('Y');
            $seededCount = 0;
            
            foreach ($schools as $school) {
                $schoolType = strtoupper($school->type);
                $schoolName = $school->name;
                $principalName = $school->principal_name ?? 'Kepala Sekolah';
                
                // Tentukan data berdasarkan tipe sekolah
                if ($schoolType === 'SMK') {
                    $viewName = 'pdf.panduan_smk';
                    $pdfFilename = 'Buku_Panduan_Penyusunan_Project_Akhir_' . str_replace(' ', '_', $schoolName) . '.pdf';
                    $title = 'Buku Panduan Penyusunan Project Akhir';
                    $description = 'Panduan kejuruan resmi langkah-demi-langkah mengenai sistematika penulisan laporan rancang bangun, perancangan alat, perakitan hardware/software, pengujian fungsionalitas, bimbingan, dan syarat sidang.';
                } else {
                    $viewName = 'pdf.panduan_sma';
                    $pdfFilename = 'Buku_Panduan_Penyusunan_Penelitian_Ilmiah_' . str_replace(' ', '_', $schoolName) . '.pdf';
                    $title = 'Buku Panduan Penyusunan Laporan Penelitian Ilmiah';
                    $description = 'Panduan akademik resmi langkah-demi-langkah mengenai sistematika penulisan bab mirip naskah ilmiah, kajian teori, metodologi riset, analisis data statistik, bimbingan, dan syarat sidang.';
                }
                
                $targetStoragePath = 'final_project_formats/' . $pdfFilename;
                
                // Compile PDF secara dinamis di tempat
                try {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, compact('schoolName', 'principalName', 'year'));
                    \Illuminate\Support\Facades\Storage::disk('public')->put($targetStoragePath, $pdf->output());
                    echo "📁 Generated & saved custom PDF for: {$schoolName}<br>\n";
                    
                    // Buat record format jika belum ada
                    $exists = \App\Models\FinalProjectFormat::where('school_id', $school->id)
                        ->where('title', $title)
                        ->exists();
                        
                    if (!$exists) {
                        \App\Models\FinalProjectFormat::create([
                            'school_id' => $school->id,
                            'title' => $title,
                            'description' => $description,
                            'file_path' => $targetStoragePath,
                            'created_by' => $adminUser->id,
                        ]);
                        $seededCount++;
                    }
                } catch (\Exception $e) {
                    echo "❌ Failed to compile PDF for {$schoolName}: " . $e->getMessage() . "<br>\n";
                }
            }
            echo "✅ Successfully seeded custom PDF guidelines for <b>{$seededCount}</b> schools!<br>\n";
        } else {
            echo "❌ No Admin/Superadmin user found to associate as creator.<br>\n";
        }

        echo "<h1>=== SYNCING ALUMNI DATA ACROSS ALL TABLES ===</h1>\n";
        echo "<h1>=== SYNCING LMS ENROLLMENTS & COURSES ===</h1>\n";
        try {
            $lmsSyncResult = app(\App\Services\LmsEnrollmentService::class)->syncAll();
            echo "✅ LMS Sync Berhasil: <b>{$lmsSyncResult['courses_synced']}</b> Kursus disinkronkan, <b>{$lmsSyncResult['classrooms_synced']}</b> Rombel diproses, <b>{$lmsSyncResult['new_enrollments_created']}</b> pendaftaran siswa baru dibuat.<br>\n";
            if (!empty($lmsSyncResult['rogue_cross_school_enrollments_cleaned'])) {
                echo "🧹 Dibersihkan: <b>{$lmsSyncResult['rogue_cross_school_enrollments_cleaned']}</b> pendaftaran silang sekolah yang tidak valid.<br>\n";
            }
        } catch (\Throwable $e) {
            echo "⚠️ Gagal sync LMS: " . $e->getMessage() . "<br>\n";
        }

        echo "<h1>=== ENSURING PANITIA POSITIONS EXIST ===</h1>\n";
        $panitiaPositions = [
            [
                'position_code' => 'PAN-CBT',
                'position_name' => 'Panitia CBT / Ujian',
                'position_category' => 'functional',
                'position_level' => 3,
                'is_structural' => false,
                'allowance_amount' => 0,
                'description' => 'Tugas Tambahan Panitia Pelaksana & Pengawas CBT / Ujian Sekolah',
                'is_active' => true
            ],
            [
                'position_code' => 'PAN-PKL',
                'position_name' => 'Panitia PKL & Hubin',
                'position_category' => 'functional',
                'position_level' => 3,
                'is_structural' => false,
                'allowance_amount' => 0,
                'description' => 'Tugas Tambahan Panitia Penempatan & Monitoring PKL / Hubungan Industri',
                'is_active' => true
            ],
            [
                'position_code' => 'PAN-PROYEK',
                'position_name' => 'Panitia Project & TA',
                'position_category' => 'functional',
                'position_level' => 3,
                'is_structural' => false,
                'allowance_amount' => 0,
                'description' => 'Tugas Tambahan Panitia Project Akhir / P5 & Tugas Akhir',
                'is_active' => true
            ],
            [
                'position_code' => 'TIM-PKS',
                'position_name' => 'Tim PKS / Kedisiplinan',
                'position_category' => 'functional',
                'position_level' => 3,
                'is_structural' => false,
                'allowance_amount' => 0,
                'description' => 'Tugas Tambahan Patroli Keamanan Sekolah & Tim Kedisiplinan Siswa',
                'is_active' => true
            ],
        ];

        foreach ($panitiaPositions as $pos) {
            \App\Models\Position::updateOrCreate(
                ['position_code' => $pos['position_code']],
                $pos
            );
        }
        echo "<h1>=== SYNCING TRANSFERRED STUDENTS & LMS ENROLLMENTS ===</h1>\n";
        $terminalStudents = \App\Models\Student::whereIn('status', \App\Models\StudentStatusHistory::TERMINAL_STATUSES)->get();
        $cleanedStudents = 0;
        foreach ($terminalStudents as $ts) {
            \App\Models\StudentClass::where('student_id', $ts->id)
                ->where('status', 'aktif')
                ->update(['status' => $ts->status]);
            \App\Models\LmsEnrollment::where('student_id', $ts->id)
                ->whereIn('status', ['enrolled', 'in_progress'])
                ->update(['status' => 'dropped']);
            $cleanedStudents++;
        }
        echo "✅ Berhasil membersihkan {$cleanedStudents} data siswa non-aktif/pindah dari rombel & LMS.<br>\n";

        echo "<b><h2 style='color:#0f0;'>✅ MIGRATION AND SYNC COMPLETED SUCCESSFULLY!</h2></b>\n";
    } catch (\Exception $e) {
        echo "<b style='color:#f00;'>ERROR: " . $e->getMessage() . "</b>\n";
    }
});

Route::get('/process-queue', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized.');
    }
    echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
    echo "<h1>=== PROCESSING DATABASE QUEUE JOBS ===</h1>\n";
    
    $pendingBefore = \Illuminate\Support\Facades\DB::table('jobs')->count();
    $failedBefore = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
    echo "Pending Jobs Before: <b>{$pendingBefore}</b>\n";
    echo "Failed Jobs Before:  <b>{$failedBefore}</b>\n\n";

    $exitCode = \Illuminate\Support\Facades\Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--max-time' => 50,
        '--tries' => 3
    ]);
    
    $output = \Illuminate\Support\Facades\Artisan::output();
    echo $output ?: "(No jobs processed or jobs processed silently)\n";

    $pendingAfter = \Illuminate\Support\Facades\DB::table('jobs')->count();
    $failedAfter = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
    echo "\n----------------------------------------\n";
    echo "Exit Code: {$exitCode}\n";
    echo "Pending Jobs Remaining: <b>{$pendingAfter}</b>\n";
    echo "Failed Jobs:            <b>{$failedAfter}</b>\n";
    echo "========================================\n";
    echo "</pre>";
});

// Test Error Alert Notifications (Telegram & WhatsApp)
Route::get('/test-error-alert', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized.');
    }

    echo "<pre style='background:#1a0a0a; color:#ff6b6b; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
    echo "<h1>🚨 TEST ERROR ALERT NOTIFICATION</h1>\n";
    echo "Mengirim test alert ke channel yang aktif...\n\n";

    $telegramEnabled = config('services.alerts.telegram.enabled');
    $whatsappEnabled = config('services.alerts.whatsapp.enabled');
    $globalEnabled = config('services.alerts.enabled');

    echo "Global Alerts Enabled: <b>" . ($globalEnabled ? '✅ YES' : '❌ NO') . "</b>\n";
    echo "Telegram Enabled:      <b>" . ($telegramEnabled ? '✅ YES' : '❌ NO') . "</b>\n";
    echo "WhatsApp Enabled:      <b>" . ($whatsappEnabled ? '✅ YES' : '❌ NO') . "</b>\n\n";

    if (!$globalEnabled) {
        echo "<span style='color:#ffd93d;'>⚠️ ERROR_ALERTS_ENABLED=false. Set ke true di .env</span>\n</pre>";
        return;
    }

    if (!$telegramEnabled && !$whatsappEnabled) {
        echo "<span style='color:#ffd93d;'>⚠️ Tidak ada channel alert yang aktif.</span>\n";
        echo "Set TELEGRAM_ALERT_ENABLED=true dan/atau WHATSAPP_ALERT_ENABLED=true di .env\n</pre>";
        return;
    }

    try {
        $testException = new \RuntimeException('[TEST] Ini adalah test error alert dari PembdaHUB - abaikan pesan ini.');

        $alertService = app(\App\Services\ErrorAlertService::class);

        // Bypass cooldown for test by checking dry_run
        if (request('dry_run') == '1') {
            echo "🔍 DRY RUN - Hanya mengecek konfigurasi, tidak mengirim alert.\n";
            echo "Config OK. Hapus ?dry_run=1 untuk mengirim test alert sebenarnya.\n</pre>";
            return;
        }

        $alertService->notify($testException);

        echo "<span style='color:#51cf66;'>✅ Test alert berhasil dikirim!</span>\n";
        echo "Cek Telegram / WhatsApp Anda untuk memverifikasi.\n";
        echo "\n<span style='color:#8696a0;'>Catatan: Alert yang sama tidak akan terkirim ulang selama ";
        echo config('services.alerts.cooldown_minutes', 5) . " menit (anti-spam).</span>\n";
    } catch (\Throwable $e) {
        echo "<span style='color:#ff0000;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }

    echo "</pre>";
});


// Switch WhatsApp Provider (Fonnte ↔ Baileys) — Dashboard & Switcher
Route::get('/switch-wa-provider', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized.');
    }

    $waService = app(\App\Services\WhatsAppService::class);
    $activeProvider = $waService->getActiveProvider();
    $providers = $waService->getProvidersInfo();
    $isEnabled = $waService->isEnabled();

    // Handle switch action
    $switchTo = request('to');
    $switched = null;
    if ($switchTo && in_array($switchTo, ['fonnte', 'selfhosted'])) {
        $switched = \App\Services\WhatsAppService::switchProvider($switchTo);
        // Re-read after switch
        $waService = new \App\Services\WhatsAppService();
        $activeProvider = $waService->getActiveProvider();
        $providers = $waService->getProvidersInfo();
    }

    // Handle test connection
    $testResult = null;
    if (request('test') === '1') {
        $testResult = $waService->getAccountInfo();
    }

    $html = "<!DOCTYPE html><html><head><meta charset='utf-8'><title>WhatsApp Provider Switch</title>";
    $html .= "<style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 30px; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #38bdf8; font-size: 24px; }
        .card { background: #1e293b; border-radius: 12px; padding: 20px; margin: 16px 0; border: 2px solid #334155; }
        .card.active { border-color: #22c55e; box-shadow: 0 0 20px rgba(34,197,94,0.2); }
        .card h3 { margin: 0 0 8px 0; font-size: 18px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-green { background: #166534; color: #4ade80; }
        .badge-gray { background: #374151; color: #9ca3af; }
        .badge-yellow { background: #713f12; color: #fbbf24; }
        .btn { display: inline-block; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; margin: 4px; }
        .btn-switch { background: #2563eb; color: white; }
        .btn-switch:hover { background: #1d4ed8; }
        .btn-test { background: #0d9488; color: white; }
        .btn-test:hover { background: #0f766e; }
        .btn-disabled { background: #374151; color: #6b7280; cursor: not-allowed; }
        .info { color: #94a3b8; font-size: 13px; margin: 6px 0; }
        .alert { padding: 14px; border-radius: 8px; margin: 12px 0; }
        .alert-success { background: #14532d; color: #4ade80; border: 1px solid #166534; }
        .alert-error { background: #450a0a; color: #fca5a5; border: 1px solid #7f1d1d; }
        .alert-info { background: #0c4a6e; color: #7dd3fc; border: 1px solid #0369a1; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td { padding: 6px 0; border-bottom: 1px solid #334155; }
        td:first-child { color: #94a3b8; width: 140px; }
    </style></head><body><div class='container'>";

    $html .= "<h1>📱 WhatsApp Provider Switch</h1>";
    $html .= "<p class='info'>Ganti provider WhatsApp dengan satu klik. Perubahan langsung berlaku tanpa restart.</p>";

    // Show switch result
    if ($switched) {
        if ($switched['success']) {
            $html .= "<div class='alert alert-success'>✅ {$switched['message']}</div>";
        } else {
            $html .= "<div class='alert alert-error'>❌ {$switched['message']}</div>";
        }
    }

    // Show test result
    if ($testResult !== null) {
        if ($testResult['success']) {
            $html .= "<div class='alert alert-success'>✅ Koneksi ke {$activeProvider} berhasil! " . json_encode($testResult['data'] ?? []) . "</div>";
        } else {
            $err = $testResult['error'] ?? $testResult['message'] ?? 'Unknown';
            $html .= "<div class='alert alert-error'>❌ Koneksi gagal: {$err}</div>";
        }
    }

    // Global status
    $statusBadge = $isEnabled
        ? "<span class='badge badge-green'>ENABLED</span>"
        : "<span class='badge badge-yellow'>DISABLED</span>";
    $html .= "<div class='card'><table>";
    $html .= "<tr><td>Status WA</td><td>{$statusBadge}</td></tr>";
    $html .= "<tr><td>Provider Aktif</td><td><b>" . strtoupper($activeProvider) . "</b></td></tr>";
    $html .= "<tr><td>Nomor Pengirim</td><td>" . config('services.whatsapp.sender') . "</td></tr>";
    $html .= "</table></div>";

    // Provider cards
    foreach ($providers as $key => $prov) {
        $isActive = $prov['is_active'];
        $cardClass = $isActive ? 'card active' : 'card';
        $badge = $isActive
            ? "<span class='badge badge-green'>✅ AKTIF</span>"
            : "<span class='badge badge-gray'>Tidak Aktif</span>";
        $tokenBadge = $prov['has_token']
            ? "<span class='badge badge-green'>Token Ada</span>"
            : "<span class='badge badge-yellow'>⚠️ Token Kosong</span>";

        $html .= "<div class='{$cardClass}'>";
        $html .= "<h3>{$prov['label']} {$badge}</h3>";
        $html .= "<table>";
        $html .= "<tr><td>Key</td><td><code>{$key}</code></td></tr>";
        $html .= "<tr><td>API URL</td><td><code>{$prov['api_url']}</code></td></tr>";
        $html .= "<tr><td>API Token</td><td>{$tokenBadge}</td></tr>";
        $html .= "</table>";

        if ($isActive) {
            $html .= "<a href='/switch-wa-provider?secret=pembda99&test=1' class='btn btn-test'>🔍 Test Koneksi</a>";
        } else {
            $html .= "<a href='/switch-wa-provider?secret=pembda99&to={$key}' class='btn btn-switch' onclick=\"return confirm('Yakin ganti ke {$prov['label']}?')\">⚡ Aktifkan Provider Ini</a>";
        }

        $html .= "</div>";
    }

    // Tips
    $html .= "<div class='alert alert-info'>";
    $html .= "<b>💡 Tips:</b><br>";
    $html .= "• Jika langganan <b>Fonnte habis</b> → klik Aktifkan pada Baileys (pastikan Node.js server berjalan)<br>";
    $html .= "• Jika ingin kembali ke <b>Fontte</b> → klik Aktifkan pada Fonnte<br>";
    $html .= "• Perubahan <b>langsung berlaku</b> tanpa perlu edit .env atau restart<br>";
    $html .= "• Config disimpan di database (tabel settings, key: <code>wa_active_provider</code>)";
    $html .= "</div>";

    $html .= "</div></body></html>";

    return $html;
});

Route::get('/sync-lms-enrollments', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Akses Ditolak.');
    }

    echo "<pre style='background:#0f172a; color:#38bdf8; padding:24px; border-radius:16px; font-size:13px; font-family:monospace; line-height:1.6;'>";
    echo "<h2 style='color:#4ade80;'>=== SINKRONISASI OTOMATIS PENDAFTARAN LMS & MATERI PEMBELAJARAN ===</h2>\n";

    try {
        $result = app(\App\Services\LmsEnrollmentService::class)->syncAll();
        echo "✅ Kursus LMS diproses: <b>{$result['courses_synced']}</b>\n";
        echo "✅ Rombel Kelas diproses: <b>{$result['classrooms_synced']}</b>\n";
        echo "✅ Pendaftaran Siswa Baru dibuat: <b>{$result['new_enrollments_created']}</b>\n";
        echo "🧹 Pendaftaran Silang Sekolah yang Dibersihkan: <b>{$result['rogue_cross_school_enrollments_cleaned']}</b>\n\n";
        echo "<span style='color:#4ade80; font-weight:bold;'>SELURUH SISWA (TERMASUK SISWA BARU MASUK KELAS) KINI TELAH TERHUBUNG KE KELAS & MATERI GURU MASING-MASING!</span>\n";
    } catch (\Throwable $e) {
        echo "<span style='color:#f87171;'>ERROR: " . $e->getMessage() . "</span>\n";
    }
    echo "</pre>";
});

Route::get('/fix-attendance', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Akses Ditolak.');
    }

    echo "<pre style='background:#0f172a; color:#38bdf8; padding:24px; border-radius:16px; font-size:13px; font-family:monospace; line-height:1.6;'>";
    echo "<h2 style='color:#4ade80;'>=== SINKRONISASI RELASI PEGAWAI GURU & RADIUS GEOFENCING ===</h2>\n";

    try {
        // 1. Update Radius Presensi ke 175 Meter & Koordinat Real Kompleks Perguruan Pembda (Jl. Pelita No. 09)
        \App\Models\Setting::setValue('attendance_max_radius', '175', 'integer', 'features');
        \App\Models\Setting::setValue('school_latitude', '1.28127778', 'string', 'features');
        \App\Models\Setting::setValue('school_longitude', '97.62566667', 'string', 'features');
        echo "✅ Radius Geofencing GPS diset ke <b>175 meter</b> (Toleransi Area Sekolah & Deviasi Ruangan).\n";

        // 2. Sinkronisasi Sekolah ke Koordinat Real Kompleks Perguruan Pembda
        $schools = \App\Models\School::all();
        foreach ($schools as $sc) {
            $sc->update(['latitude' => 1.28127778, 'longitude' => 97.62566667]);
            echo "🏫 Set koordinat Kampus Perguruan Pembda (1.28127778, 97.62566667) untuk sekolah: {$sc->name}\n";
        }

        // 3. Sinkronisasi Relasi Guru & Pegawai
        $guruUsers = \App\Models\User::whereIn('role', ['guru', 'pegawai', 'kepala_sekolah'])->get();
        $linkedCount = 0;
        $createdEmpCount = 0;

        foreach ($guruUsers as $user) {
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();

            // Jika employee belum punya user_id tapi teacher punya employee_id
            if (!$employee && $teacher && $teacher->employee_id) {
                $employee = \App\Models\Employee::find($teacher->employee_id);
                if ($employee) {
                    $employee->update(['user_id' => $user->id]);
                    $linkedCount++;
                    echo "🔗 Hubungkan Employee #{$employee->id} ke User #{$user->id} ({$user->name})\n";
                }
            }

            // Jika employee ada tapi teacher belum link
            if ($employee && !$teacher) {
                $teacher = \App\Models\Teacher::where('employee_id', $employee->id)->first();
                if ($teacher) {
                    $teacher->update(['user_id' => $user->id]);
                }
            }

            // Jika belum ada Employee sama sekali, buatkan Employee yang valid
            if (!$employee) {
                $employee = \App\Models\Employee::create([
                    'school_id' => $user->school_id ?? 1,
                    'user_id' => $user->id,
                    'employee_code' => $teacher?->teacher_code ?? ('PGW-' . $user->id),
                    'full_name' => $teacher?->full_name ?? $user->name,
                    'gender' => $teacher?->gender ?? 'L',
                    'birth_place' => $teacher?->birth_place ?? '-',
                    'employee_type' => $user->role === 'guru' ? 'guru' : 'other',
                    'employment_status' => 'yayasan',
                    'tmt_date' => now()->format('Y-m-d'),
                    'is_active' => true,
                ]);
                $createdEmpCount++;
                echo "✨ Buat Employee baru #{$employee->id} untuk User #{$user->id} ({$user->name})\n";
            }

            // Pastikan Teacher record juga terhubung ke Employee ini
            if ($employee && $user->role === 'guru') {
                \App\Models\Teacher::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_id' => $employee->id,
                        'school_id' => $employee->school_id ?? $user->school_id ?? 1,
                        'teacher_code' => $employee->employee_code ?? ('PGW-' . $user->id),
                        'full_name' => $employee->full_name ?? $user->name,
                        'gender' => $employee->gender ?? 'L',
                        'birth_place' => $employee->birth_place ?? '-',
                        'is_active' => true,
                    ]
                );
            }
        }

        echo "\n📊 HASIL SINKRONISASI:\n";
        echo "- Total Pengguna Guru/Pegawai Diperiksa: " . $guruUsers->count() . "\n";
        echo "- Relasi Ditautkan: {$linkedCount}\n";
        echo "- Pegawai Baru Dibuat: {$createdEmpCount}\n";

        // 4. Verifikasi Akun Kepala Sekolah SMPS Pembda 2 (Ibu Herni Yanti Telaumbanua)
        $herniUser = \App\Models\User::where('name', 'LIKE', '%Herni%')->first();
        $herniTeacher = \App\Models\Teacher::where('full_name', 'LIKE', '%Herni%')->orWhere('teacher_code', 'GR001')->first();
        $herniEmployee = \App\Models\Employee::where('full_name', 'LIKE', '%Herni%')->orWhere('id', 25)->first();

        echo "\n👩‍🏫 STATUS DATA IBU HERNI YANTI TELAUMBANUA DI DATABASE:\n";
        echo "- User     : " . ($herniUser ? "ID #{$herniUser->id} | Name: {$herniUser->name} | Role: {$herniUser->role} | Username: {$herniUser->username}" : "Belum Ada") . "\n";
        echo "- Teacher  : " . ($herniTeacher ? "ID #{$herniTeacher->id} | Name: {$herniTeacher->full_name} | Code: {$herniTeacher->teacher_code} | RFID: " . ($herniTeacher->rfid_uid ?? '-') . " | School: #{$herniTeacher->school_id}" : "Belum Ada") . "\n";
        echo "- Employee : " . ($herniEmployee ? "ID #{$herniEmployee->id} | Name: {$herniEmployee->full_name} | Code: {$herniEmployee->employee_code} | RFID: " . ($herniEmployee->rfid_uid ?? '-') . " | Posisi: " . ($herniEmployee->getPrimaryPosition()?->position_name ?? 'Kepala Sekolah') : "Belum Ada") . "\n";

        // 5. Verifikasi Data Siswa Anggun Trienji Z
        $anggun = \App\Models\Student::where('full_name', 'LIKE', '%Anggun%')->orWhere('full_name', 'LIKE', '%Trienji%')->first();
        // 5. Verifikasi Data Siswa Anggun Trienji Z
        $anggun = \App\Models\Student::where('full_name', 'LIKE', '%Trienji%')->first();
        if (!$anggun) {
            $anggun = \App\Models\Student::where('full_name', 'LIKE', '%Anggun%')->first();
        }
        echo "\n👩‍🎓 STATUS DATA SISWA ANGGUN TRIENJI Z DI DATABASE:\n";
        if ($anggun) {
            $c = $anggun->studentClasses()->where('status', 'aktif')->latest('id')->first();
            $cName = $c && $c->classroom ? $c->classroom->class_name : 'Tanpa Kelas';
            echo "- Student ID: #{$anggun->id} | Name: {$anggun->full_name} | NIS: {$anggun->nis} | NISN: {$anggun->nisn} | RFID: " . ($anggun->rfid_uid ?? '-') . " | Kelas: {$cName} | School: #{$anggun->school_id}\n";
        }

        // 6. Uji Simulasi Scan Kiosk dengan Endpoint API Asli (Mock Request)
        $testCode = $herniTeacher?->teacher_code ?? $herniEmployee?->employee_code ?? 'GR001';
        echo "\n🧪 SIMULASI SCAN KIOSK DENGAN KODE QR ('{$testCode}'):\n";
        
        $matchedTeacher = \App\Models\Teacher::where('is_active', true)->where('teacher_code', $testCode)->first();
        $matchedEmployee = \App\Models\Employee::where('is_active', true)
            ->where(function($q) use ($testCode) {
                $q->where('employee_code', $testCode)->orWhere('rfid_uid', $testCode)->orWhere('nip', $testCode);
            })->first();
        $matchedStudent = \App\Models\Student::whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
            ->where(function($q) use ($testCode) {
                $q->where('nis', $testCode)->orWhere('nisn', $testCode)->orWhere('rfid_uid', $testCode);
            })->first();

        if ($matchedTeacher) {
            echo "✅ HASIL IDENTIFIKASI SCAN: GURU/KEPALA SEKOLAH -> {$matchedTeacher->full_name} (Teacher ID #{$matchedTeacher->id} | Code: {$matchedTeacher->teacher_code})\n";
        } elseif ($matchedEmployee) {
            echo "✅ HASIL IDENTIFIKASI SCAN: PEGAWAI/STAF -> {$matchedEmployee->full_name} (Employee ID #{$matchedEmployee->id})\n";
        } elseif ($matchedStudent) {
            echo "⚠️ HASIL IDENTIFIKASI SCAN: SISWA -> {$matchedStudent->full_name} (NIS {$matchedStudent->nis})\n";
        } else {
            echo "❌ HASIL IDENTIFIKASI SCAN: Tidak Dikenal (KARTU BARU)\n";
        }

        // 7. Bersihkan Cache Laravel
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo "\n⚡ Cache route, config, dan data berhasil dibersihkan!\n";
        echo "<h3 style='color:#4ade80;'>🎉 SEMUA PROSES PERBAIKAN ABSENSI SELESAI DENGAN SUKSES!</h3>";
    } catch (\Exception $e) {
        echo "<b style='color:#f43f5e;'>ERROR: " . $e->getMessage() . "</b>\n";
    }
    echo "</pre>";
});

Route::get('/migrate-quiz-questions', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized.');
    }
    try {
        echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
        echo "<h1>=== MIGRATING LMS QUIZ QUESTIONS FORMAT ===</h1>\n";

        $questions = \App\Models\LmsQuizQuestion::where('question_type', 'multiple_choice')->get();
        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($questions as $q) {
            $options = $q->options;
            if (!$options || !is_array($options)) {
                $skippedCount++;
                continue;
            }

            // Check if options is already in associative format [ {key: 'A', text: '...'}, ... ]
            $firstOpt = $options[0] ?? null;
            $isAssoc = is_array($firstOpt) && isset($firstOpt['key']);

            if ($isAssoc) {
                // Check if correct_answer is numeric index
                $correct = trim($q->correct_answer ?? '');
                if (preg_match('/^\d+$/', $correct)) {
                    $alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                    $newCorrect = $alphabets[(int)$correct] ?? $correct;
                    $q->update(['correct_answer' => $newCorrect]);
                    echo "📝 Question ID {$q->id}: Updated correct_answer from {$correct} to {$newCorrect} (Already Associative options)\n";
                    $migratedCount++;
                } else {
                    $skippedCount++;
                }
                continue;
            }

            // Opsi bertipe non-associative, misal: ["Opsi A", "Opsi B", "Opsi C", "Opsi D"]
            $alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            $newOptions = [];
            foreach ($options as $idx => $optText) {
                $newOptions[] = [
                    'key' => $alphabets[$idx] ?? (string)($idx + 1),
                    'text' => (string)$optText
                ];
            }

            $correct = trim($q->correct_answer ?? '');
            $newCorrect = $correct;
            // Jika correct_answer adalah angka, ubah ke huruf
            if (preg_match('/^\d+$/', $correct)) {
                $newCorrect = $alphabets[(int)$correct] ?? $correct;
            }

            $q->update([
                'options' => $newOptions,
                'correct_answer' => $newCorrect
            ]);

            echo "✅ Question ID {$q->id}: Converted to Associative options. Correct answer from '{$correct}' to '{$newCorrect}'\n";
            $migratedCount++;
        }

        echo "\n<b>Migrasi Selesai!</b>\n";
        echo "Total data dikonversi/diperbarui: <b>{$migratedCount}</b>\n";
        echo "Total data dilewati (sudah sesuai format): <b>{$skippedCount}</b>\n";

        echo "<h1>=== CLEARING VIEW & ROUTE CACHE ===</h1>\n";
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        echo "View cache cleared: " . \Illuminate\Support\Facades\Artisan::output();
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        echo "Route cache cleared: " . \Illuminate\Support\Facades\Artisan::output();
        if (function_exists('opcache_reset')) {
            @opcache_reset();
            echo "OPcache reset: SUCCESS\n";
        }
    } catch (\Exception $e) {
        echo "<b style='color:#f00;'>ERROR: " . $e->getMessage() . "</b>\n";
    }
});

Route::get('/download-pdf-guideline', function() {
    $user = auth()->user();
    $school = null;
    if ($user) {
        if ($user->role === 'siswa' && $user->student) {
            $school = $user->student->school;
        } else if ($user->school_id) {
            $school = \App\Models\School::find($user->school_id);
        }
    }
    
    // Fallback to first school if guest/undetermined
    if (!$school) {
        $school = \App\Models\School::whereIn('type', ['SMA', 'SMK'])->first();
    }
    
    $schoolName = $school ? $school->name : 'SMA Swasta Pembda 1 Gunungsitoli';
    $schoolType = $school ? $school->type : 'SMA';
    $principalName = $school ? ($school->principal_name ?? 'Kepala Sekolah') : 'Kepala Sekolah';
    $year = date('Y');
    
    $viewName = (strtoupper($schoolType) === 'SMK') ? 'pdf.panduan_smk' : 'pdf.panduan_sma';
    $filename = (strtoupper($schoolType) === 'SMK') ? 'Buku_Panduan_Penyusunan_Project_Akhir.pdf' : 'Buku_Panduan_Penyusunan_Penelitian_Ilmiah.pdf';
    
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, compact('schoolName', 'principalName', 'year'));
    return $pdf->download($filename);
})->name('public.guideline.download');

Route::get('/download-format/{id}', function($id) {
    $format = \App\Models\FinalProjectFormat::findOrFail($id);
    
    if (!$format->file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($format->file_path)) {
        abort(404, 'File format tidak ditemukan.');
    }
    
    $extension = pathinfo($format->file_path, PATHINFO_EXTENSION);
    $filename = $format->title . '.' . $extension;
    
    return \Illuminate\Support\Facades\Storage::disk('public')->download($format->file_path, $filename);
})->name('public.format.download');

// ============================================================
// SIMULASI DATA SURVEY UNTUK TESTING GRAFIK
// ============================================================
Route::get('/simulate-survey-1', function() {
    if (request('token') !== 'pembda2026sim') {
        abort(403, 'Akses ditolak.');
    }

    $surveyId = 1;
    $survey = \App\Models\Survey::with('questions')->find($surveyId);
    if (!$survey) return "Survei ID 1 tidak ditemukan.";

    // Coba cari guru SMK
    $teachers = \App\Models\User::where('role', 'guru')
        ->whereHas('school', function($q) {
            $q->where('name', 'like', '%SMK%');
        })
        ->take(10)
        ->get();
        
    // Fallback jika tidak cukup guru SMK
    if ($teachers->count() < 10) {
        $teachers = \App\Models\User::where('role', 'guru')->take(10)->get();
    }

    if ($teachers->isEmpty()) return "Tidak ada data guru.";

    try {
        \Illuminate\Support\Facades\DB::transaction(function () use ($surveyId, $teachers, $survey) {
            // Hapus tanggapan simulasi lama untuk mencegah duplikasi masif
            \App\Models\SurveyResponse::where('survey_id', $surveyId)->delete();
            
            $questions = $survey->questions;

            foreach ($teachers as $teacher) {
                $response = \App\Models\SurveyResponse::create([
                    'survey_id' => $surveyId,
                    'user_id' => $teacher->id,
                    'school_id' => $teacher->school_id,
                    'teacher_type' => 'kejuruan'
                ]);

                foreach ($questions as $q) {
                    if ($q->type === 'scale') {
                        $max = 5;
                        $min = 1;
                        if ($q->scale_type === 'yes_no') {
                            $max = 2; // Actually 1 or 2
                        } elseif ($q->scale_type === 'likert_4') {
                            $max = 4;
                        }
                        
                        // Buat data bervariasi tapi condong ke arah positif (realistis)
                        $rating = rand(1, 10) > 3 ? rand((int)ceil($max/2), $max) : rand($min, (int)ceil($max/2));
                        // Untuk yes/no: 1 = Ya, 2 = Tidak
                        if ($q->scale_type === 'yes_no') {
                            $rating = rand(1, 10) > 2 ? 1 : 2; 
                        }

                        \App\Models\SurveyAnswer::create([
                            'response_id' => $response->id,
                            'question_id' => $q->id,
                            'rating' => $rating,
                        ]);
                    } else {
                        // Text / Essay
                        $texts = [
                            'Fasilitas bengkel dan lab sudah cukup baik, namun kami butuh lebih banyak bahan praktik agar setiap siswa bisa mencoba alat secara langsung tanpa harus bergantian terlalu lama.',
                            'Sinkronisasi kurikulum dengan industri (DUDI) sangat membantu. Siswa terlihat lebih antusias saat materi langsung diaplikasikan pada standar industri.',
                            'Mungkin perlu lebih banyak waktu untuk praktik dibandingkan teori di kelas, jiwa kejuruan anak-anak lebih terbangun saat praktek lapangan.',
                            'Mental kerja keras siswa perlu terus didorong. Sejauh ini penguatan jiwa vokasional sudah berjalan baik.',
                            'Kami membutuhkan pelatihan up-skilling untuk guru kejuruan agar bisa mengikuti perkembangan teknologi terbaru dari industri.',
                            'Secara umum sudah berjalan di jalur yang benar. Budaya kerja industri seperti 5R (Ringkas, Rapi, Resik, Rawat, Rajin) sudah diterapkan di bengkel.'
                        ];
                        \App\Models\SurveyAnswer::create([
                            'response_id' => $response->id,
                            'question_id' => $q->id,
                            'answer_text' => $texts[array_rand($texts)],
                        ]);
                    }
                }
            }
        });
        
        return "<h2 style='font-family: sans-serif; color:green;'>Simulasi Berhasil!</h2><p style='font-family: sans-serif;'>Berhasil membuat data simulasi dari 10 Guru SMK (Data asli dari database) untuk Survei Kesiapan dan Penerapan Jiwa Kejuruan.</p><p style='font-family: sans-serif;'><a href='/admin/surveys/{$surveyId}/results' style='padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 10px;'>Lihat Hasil Grafik</a></p>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// ROUTE RECOVERY/FIX UNTUK DUPLIKASI SURVEI
Route::get('/admin/surveys-fix-duplicate', function() {
    if (!auth()->check() || (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdminSekolah())) {
        if (request('token') !== 'pembda2026fix') {
            return response('Akses ditolak. Anda harus login sebagai admin atau menyertakan token yang valid (?token=pembda2026fix).', 403);
        }
    }

    echo "<pre>=== PROSES PENGGABUNGAN DAN PEMBERSIHAN SURVEI ===\n";
    echo "Waktu: " . date('Y-m-d H:i:s') . "\n\n";

    try {
        \Illuminate\Support\Facades\DB::beginTransaction();

        // Ambil semua survei beserta count responses
        $allSurveys = \App\Models\Survey::withCount('responses')->get();
        echo "Total survei terdaftar di database: " . $allSurveys->count() . "\n";
        foreach ($allSurveys as $s) {
            echo " - ID: {$s->id} | Title: {$s->title} | Target: {$s->target_respondent} | Responses: {$s->responses_count}\n";
        }
        echo "\n";

        // Group surveys by title
        $grouped = $allSurveys->groupBy(function($item) {
            return trim(strtolower($item->title));
        });

        $deletedCount = 0;
        $mergedResponsesCount = 0;

        foreach ($grouped as $title => $surveys) {
            if ($surveys->count() > 1) {
                echo "Menemukan duplikasi untuk judul: \"" . $surveys->first()->title . "\"\n";
                
                // Cari survei utama (yang memiliki tanggapan terbanyak)
                $mainSurvey = $surveys->sortByDesc('responses_count')->first();
                echo " -> Survei Utama terpilih: ID {$mainSurvey->id} dengan {$mainSurvey->responses_count} tanggapan.\n";

                // Cari duplikat-duplikat lainnya
                $duplicates = $surveys->filter(function($s) use ($mainSurvey) {
                    return $s->id !== $mainSurvey->id;
                });

                // Ambil pertanyaan dari survei utama untuk mapping
                $mainQuestions = $mainSurvey->questions()->get();

                foreach ($duplicates as $dup) {
                    echo " -> Memproses survei duplikat: ID {$dup->id} dengan {$dup->responses_count} tanggapan.\n";
                    
                    if ($dup->responses_count > 0) {
                        // Pindahkan tanggapan dari duplikat ke utama
                        $dupQuestions = $dup->questions()->get();
                        
                        // Map duplicate question IDs to main question IDs by order or by text
                        $questionMap = [];
                        foreach ($dupQuestions as $dq) {
                            $mq = $mainQuestions->first(function($item) use ($dq) {
                                return $item->order === $dq->order || trim(strtolower($item->question_text)) === trim(strtolower($dq->question_text));
                            });
                            
                            if ($mq) {
                                $questionMap[$dq->id] = $mq->id;
                            }
                        }

                        // Ambil semua tanggapan dari survei duplikat
                        $responses = \App\Models\SurveyResponse::where('survey_id', $dup->id)->get();
                        foreach ($responses as $r) {
                            $r->update(['survey_id' => $mainSurvey->id]);
                            
                            $answers = \Illuminate\Support\Facades\DB::table('survey_answers')
                                ->where('response_id', $r->id)
                                ->get();
                                
                            foreach ($answers as $ans) {
                                if (isset($questionMap[$ans->question_id])) {
                                    \Illuminate\Support\Facades\DB::table('survey_answers')
                                        ->where('id', $ans->id)
                                        ->update(['question_id' => $questionMap[$ans->question_id]]);
                                }
                            }
                            $mergedResponsesCount++;
                        }
                        echo "   ✓ Berhasil memindahkan {$dup->responses_count} tanggapan ke Survei Utama.\n";
                    }

                    $dup->questions()->delete();
                    $dup->delete();
                    $deletedCount++;
                    echo "   ✓ Survei duplikat ID {$dup->id} telah dihapus.\n";
                }
            }
        }

        // Hapus survei kosong lainnya yang sejenis jika ada survei aktif dengan target yang sama
        $remainingSurveys = \App\Models\Survey::withCount('responses')->get();
        foreach ($remainingSurveys as $s) {
            if ($s->responses_count == 0) {
                $hasActiveEquivalent = \App\Models\Survey::where('target_respondent', $s->target_respondent)
                    ->where('id', '!=', $s->id)
                    ->whereHas('responses')
                    ->exists();

                if ($hasActiveEquivalent) {
                    echo "Menghapus survei kosong lain: ID {$s->id} | Title: {$s->title} (karena ada survei aktif lain untuk target: {$s->target_respondent})\n";
                    $s->questions()->delete();
                    $s->delete();
                    $deletedCount++;
                }
            }
        }

        \Illuminate\Support\Facades\DB::commit();
        
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        echo "\n✅ PROSES SELESAI DENGAN SUKSES!\n";
        echo " - Total tanggapan yang digabungkan: {$mergedResponsesCount}\n";
        echo " - Total survei duplikat/kosong yang dihapus: {$deletedCount}\n";
        echo "Silakan periksa kembali halaman Kelola Survei di dashboard admin.\n";

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::rollBack();
        echo "\n❌ ERROR TERDETEKSI: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
        echo "Seluruh transaksi database telah di-rollback.\n";
    }
    echo "</pre>";
});

Route::get('/admin/simulate-schedules-smk', function() {
    if (request('token') !== 'pembda2026') {
        return response('Akses ditolak. Sertakan token yang valid (?token=pembda2026).', 403);
    }
    
    echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
    echo "Menjalankan Simulasi Jadwal SMK...\n\n";
    
    $exitCode = \Illuminate\Support\Facades\Artisan::call('simulate:schedules-smk');
    echo \Illuminate\Support\Facades\Artisan::output();
    
    echo "\nSelesai dengan exit code: " . $exitCode;
    echo "</pre>";
});
Route::get('/admin/fix-duplicates-smk', function() {
    if (request('token') !== 'pembda2026') {
        return response('Akses ditolak. Sertakan token yang valid (?token=pembda2026).', 403);
    }
    
    echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-size:14px; font-family:monospace;'>";
    echo "Menjalankan Pembersihan Duplikat...\n\n";
    
    $exitCode = \Illuminate\Support\Facades\Artisan::call('app:fix-duplicates');
    echo \Illuminate\Support\Facades\Artisan::output();
    
    echo "\nSelesai dengan exit code: " . $exitCode;
    echo "</pre>";
});
Route::get('/admin/migrate-db', function() {
    if (request('token') !== 'pembda2026') {
        return response('Akses ditolak.', 403);
    }
    
    echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-family:monospace;'>";
    echo "Menjalankan Migrasi Database...\n\n";
    
    $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
    
    echo "\nSelesai dengan exit code: " . $exitCode;
    echo "</pre>";
});
Route::get('/admin/fix-relasi-tp', function() {
    if (request('token') !== 'pembda2026') {
        return response('Akses ditolak.', 403);
    }
    
    echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-family:monospace;'>";
    echo "Memeriksa dan Memperbaiki Relasi Data ke TP Aktif...\n\n";
    
    // Ambil TP yang aktif (harusnya ID 8)
    $activeTP = \App\Models\AcademicYear::where('is_active', true)->first();
    if (!$activeTP) {
        echo "TIDAK DITEMUKAN TAHUN PELAJARAN AKTIF!\n</pre>";
        return;
    }
    
    echo "Tahun Pelajaran Aktif: " . $activeTP->year . " (ID: " . $activeTP->id . ")\n\n";
    
    $tables = ['semesters', 'classrooms', 'employee_positions', 'teaching_assignments', 'schedules', 'time_slots'];
    
    foreach ($tables as $table) {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) continue;
        if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'academic_year_id')) continue;
        
        // Cari data yang ID-nya BUKAN ID aktif, TAPI ID tersebut tidak ada di tabel academic_years (Orphaned Data)
        $orphans = \Illuminate\Support\Facades\DB::table($table)
            ->whereNotIn('academic_year_id', function($q) {
                $q->select('id')->from('academic_years');
            })
            ->whereNotNull('academic_year_id')
            ->count();
            
        if ($orphans > 0) {
            echo "Ditemukan " . $orphans . " data terputus di tabel '" . $table . "'.\n";
            echo "--> Memperbaiki relasi ke ID " . $activeTP->id . "...\n";
            
            \Illuminate\Support\Facades\DB::table($table)
                ->whereNotIn('academic_year_id', function($q) {
                    $q->select('id')->from('academic_years');
                })
                ->whereNotNull('academic_year_id')
                ->update(['academic_year_id' => $activeTP->id]);
                
            echo "--> Selesai diperbaiki.\n\n";
        } else {
            echo "Tabel '" . $table . "' AMAN. Tidak ada relasi yang terputus.\n";
        }
    }
    
    echo "\nProses Pengecekan Selesai!";
    echo "</pre>";
});
Route::get('/admin/cek-db-live', function() {
    if (request('token') !== 'pembda2026') {
        return response('Akses ditolak.', 403);
    }
    
    echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius:10px; font-family:monospace;'>";
    echo "=== DIAGNOSTIK DATABASE LIVE ===\n\n";
    
    echo "1. DAFTAR TAHUN PELAJARAN:\n";
    echo "--------------------------------------------------------\n";
    echo sprintf("%-5s | %-15s | %-10s\n", "ID", "NAMA TAHUN", "STATUS");
    echo "--------------------------------------------------------\n";
    $years = \App\Models\AcademicYear::all();
    foreach ($years as $y) {
        $status = $y->is_active ? "AKTIF" : "Tidak";
        echo sprintf("%-5s | %-15s | %-10s\n", $y->id, $y->year, $status);
    }
    echo "\n\n";
    
    echo "2. REKAP DATA BERDASARKAN TAHUN PELAJARAN:\n";
    echo "--------------------------------------------------------------------------------------\n";
    echo sprintf("%-15s | %-10s | %-10s | %-15s | %-10s\n", "TAHUN", "KELAS", "TIME SLOT", "PENUGASAN GURU", "JADWAL");
    echo "--------------------------------------------------------------------------------------\n";
    
    foreach ($years as $y) {
        $kelas = \App\Models\Classroom::where('academic_year_id', $y->id)->count();
        $ts = \App\Models\TimeSlot::where('academic_year_id', $y->id)->count();
        $penugasan = \App\Models\TeachingAssignment::where('academic_year_id', $y->id)->count();
        $jadwal = \App\Models\Schedule::where('academic_year_id', $y->id)->count();
        
        echo sprintf("%-15s | %-10s | %-10s | %-15s | %-10s\n", $y->year, $kelas, $ts, $penugasan, $jadwal);
    }
    
    // Cek apakah ada data tanpa academic_year_id
    $kelasOrphan = \App\Models\Classroom::whereNull('academic_year_id')->count();
    $tsOrphan = \App\Models\TimeSlot::whereNull('academic_year_id')->count();
    $penugasanOrphan = \App\Models\TeachingAssignment::whereNull('academic_year_id')->count();
    $jadwalOrphan = \App\Models\Schedule::whereNull('academic_year_id')->count();
    
    if ($kelasOrphan > 0 || $tsOrphan > 0 || $penugasanOrphan > 0 || $jadwalOrphan > 0) {
        echo "--------------------------------------------------------------------------------------\n";
        echo sprintf("%-15s | %-10s | %-10s | %-15s | %-10s\n", "TANPA TAHUN (NULL)", $kelasOrphan, $tsOrphan, $penugasanOrphan, $jadwalOrphan);
    }
    
    echo "--------------------------------------------------------------------------------------\n";
    echo "\nSelesai Pengecekan.";
    echo "</pre>";
});

// Fallback Route for Mars Audio (Hostinger workaround)
Route::get('/audio/mars-pembda.mp4', function () {
    $paths = [
        public_path('audio/mars-pembda.mp4'),
        base_path('../audio/mars-pembda.mp4')
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) return response()->file($path);
    }
    abort(404, 'Audio file not found');
});

// Route to Seed Landing Page Content from Browser (Safe & Secured with Key)
Route::get('/seed-landing-content', function () {
    $secret = request('key');
    if ($secret !== 'pembda2026') {
        return response("Unauthorized. Please provide the correct key.", 403);
    }
    
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'LandingPageContentSeeder',
            '--force' => true
        ]);
        return "Landing Page content (News & Gallery) seeded successfully!";
    } catch (\Exception $e) {
        return "Error seeding content: " . $e->getMessage();
    }
});

// Route to Fix Database Issue with missing "academic_years"
Route::get('/fix-academic-years', function () {
    $secret = request('key');
    if ($secret !== 'pembda2026') return response("Unauthorized", 403);

    try {
        // Ensure TP 2026/2027 exists
        $ay = \App\Models\AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            ['is_active' => true]
        );
        return "Academic Year 2026/2027 ensured. ID: " . $ay->id;
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Route wrapper for Restore TP
Route::get('/restore-tp-2026', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized.');
    }
    $_GET['secret'] = 'pembda99';
    if (request()->has('dry_run')) {
        $_GET['dry_run'] = request('dry_run');
    }
    $file = public_path('restore_tp.php');
    if (!file_exists($file)) {
        $file = base_path('public/restore_tp.php');
    }
    if (file_exists($file)) {
        require $file;
    } else {
        return "File restore_tp.php tidak ditemukan di: " . $file;
    }
});

Route::get('/restore-tp-2025', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized.');
    }
    $_GET['secret'] = 'pembda99';
    if (request()->has('dry_run')) {
        $_GET['dry_run'] = request('dry_run');
    }
    $file = public_path('restore_tp_2025.php');
    if (!file_exists($file)) {
        $file = base_path('public/restore_tp_2025.php');
    }
    if (file_exists($file)) {
        require $file;
    } else {
        return "File restore_tp_2025.php tidak ditemukan di: " . $file;
    }
});

// Hostinger Symlink Fallback Route for Storage Files
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $paths = [
        storage_path('app/public/' . $folder . '/' . $filename),
        storage_path('app/' . $folder . '/' . $filename),
        public_path('storage/' . $folder . '/' . $filename),
        base_path('../storage/' . $folder . '/' . $filename) // public_html/storage
    ];
    
    $foundPath = null;
    foreach ($paths as $p) {
        if (file_exists($p)) {
            $foundPath = $p;
            break;
        }
    }
    
    if (!$foundPath) {
        // Return 404 image placeholder or abort
        abort(404, 'File not found in any storage path.');
    }
    
    return response()->file($foundPath, [
        'Cache-Control' => 'public, max-age=31536000'
    ]);
})->where('filename', '.*');

Route::get('/debug-gallery', function () {
    if (request('secret') !== 'pembda99') return 'Unauthorized';
    
    $dirs = [
        'storage_path' => storage_path('app/public/gallery'),
        'public_path' => public_path('storage/gallery'),
        'public_html' => base_path('../storage/gallery'),
        'public_html_pembdahub' => base_path('../pembdahub/storage/app/public/gallery')
    ];
    
    $html = "<h3>Debug Gallery Paths</h3><ul>";
    foreach ($dirs as $label => $dir) {
        $html .= "<li><b>{$label}</b>: {$dir} <br>";
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            $html .= "<span style='color:green'>Found " . count($files) . " files.</span><br>";
            $html .= "<pre>" . print_r(array_slice($files, 0, 5), true) . "</pre>";
        } else {
            $html .= "<span style='color:red'>Directory does not exist!</span>";
        }
        $html .= "</li><br>";
    }
    $html .= "</ul>";
    
    // Cek satu file spesifik
    $testFile = request('file', 'mars-pembda.mp4');
    $html .= "<h3>Mencari file: $testFile</h3>";
    $output = shell_exec("find " . escapeshellarg(base_path('../')) . " -iname " . escapeshellarg($testFile) . " 2>&1");
    $html .= "<pre>" . htmlspecialchars($output) . "</pre>";
    
    return $html;
});

Route::get('/debug-calendar-check', function () {
    if (request('secret') !== 'pembda99') return 'Unauthorized';

    $schools = \App\Models\School::all();
    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

    $html = "<h2>Rincian Input Kalender Pendidikan per Unit Sekolah (TP " . ($activeYear->year ?? '-') . ")</h2>";
    $html .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; font-family:sans-serif; width:100%;'>";
    $html .= "<tr style='background:#312e81; color:white;'><th>No</th><th>Nama Sekolah</th><th>Tipe</th><th>Agenda Khusus Unit</th><th>Agenda Yayasan</th><th>Total (Progress Input)</th><th>Contoh Agenda Khusus</th></tr>";

    $yayasanCount = \App\Models\EducationalCalendar::whereNull('school_id')->count();

    $no = 1;
    foreach ($schools as $s) {
        $schoolCount = \App\Models\EducationalCalendar::where('school_id', $s->id)->count();
        $sampleEvents = \App\Models\EducationalCalendar::where('school_id', $s->id)->take(3)->pluck('title')->toArray();
        $sampleStr = !empty($sampleEvents) ? implode(', ', $sampleEvents) : '<span style="color:#999;">Belum ada agenda khusus unit</span>';

        $totalCombined = $schoolCount + $yayasanCount;

        $html .= "<tr>";
        $html .= "<td>{$no}</td>";
        $html .= "<td><b>{$s->name}</b></td>";
        $html .= "<td>{$s->type}</td>";
        $html .= "<td style='text-align:center;'><b>{$schoolCount}</b> Agenda</td>";
        $html .= "<td style='text-align:center;'>{$yayasanCount} Agenda</td>";
        $html .= "<td style='text-align:center; background:#e0f2fe;'><b>{$totalCombined}</b> Agenda</td>";
        $html .= "<td>{$sampleStr}</td>";
        $html .= "</tr>";
        $no++;
    }

    $html .= "<tr style='background:#fef3c7;'>";
    $html .= "<td colspan='3'><b>YAYASAN / UMUM (school_id = NULL)</b></td>";
    $html .= "<td colspan='3' style='text-align:center;'><b>{$yayasanCount}</b> Agenda Yayasan</td>";
    $html .= "<td>Berlaku untuk seluruh unit sekolah</td>";
    $html .= "</tr>";

    $html .= "</table>";

    return $html;
});

Route::get('/sync-lms-reputation', function () {
    if (request('secret') !== 'pembda99' && !auth()->check()) return 'Unauthorized';

    $modules = \App\Models\LmsModule::with('course.teacher.user')->get();
    $materials = \App\Models\LmsMaterial::with('course.teacher.user')->get();
    $assignments = \App\Models\LmsAssignment::with('course.teacher.user')->get();
    $quizzes = \App\Models\LmsQuiz::with('course.teacher.user')->get();

    $syncedCount = 0;

    foreach ($modules as $m) {
        $userId = $m->course?->teacher?->user_id;
        if ($userId) {
            \App\Models\ReputationLog::log($userId, 30, 'lms_content', "Membuat modul LMS: " . $m->title, $m);
            $syncedCount++;
        }
    }

    foreach ($materials as $mat) {
        $userId = $mat->course?->teacher?->user_id;
        if ($userId) {
            \App\Models\ReputationLog::log($userId, 30, 'lms_content', "Membuat materi LMS: " . $mat->title, $mat);
            $syncedCount++;
        }
    }

    foreach ($assignments as $asg) {
        $userId = $asg->course?->teacher?->user_id;
        if ($userId) {
            \App\Models\ReputationLog::log($userId, 30, 'lms_content', "Membuat tugas LMS: " . $asg->title, $asg);
            $syncedCount++;
        }
    }

    foreach ($quizzes as $qz) {
        $userId = $qz->course?->teacher?->user_id;
        if ($userId) {
            \App\Models\ReputationLog::log($userId, 30, 'lms_content', "Membuat kuis LMS: " . $qz->title, $qz);
            $syncedCount++;
        }
    }

    // Recalculate level for all reputations
    $reputations = \App\Models\Reputation::with('user')->get();
    foreach ($reputations as $rep) {
        $rep->updateLevel();
        $rep->save();
    }

    $html = "<h2>Hitung Ulang & Synchronize Poin LMS Seluruh Guru Selesai!</h2>";
    $html .= "<p>Berhasil melakukan akumulasi ulang untuk total <b>{$syncedCount}</b> entri LMS (Modul, Materi, Tugas, & Kuis) bagi SELURUH Guru.</p>";
    $html .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; font-family:sans-serif; width:100%;'>";
    $html .= "<tr style='background:#1e1b4b; color:white;'><th>No</th><th>Nama Guru</th><th>Email</th><th>Total Poin Pembda Elite</th><th>Level Rank</th></tr>";

    $teachersRep = \App\Models\Reputation::whereHas('user', function($q){
        $q->where('role', 'guru');
    })->orderBy('total_points', 'desc')->get();

    $no = 1;
    foreach ($teachersRep as $tr) {
        $html .= "<tr>";
        $html .= "<td>{$no}</td>";
        $html .= "<td><b>" . ($tr->user->name ?? '-') . "</b></td>";
        $html .= "<td>" . ($tr->user->email ?? '-') . "</td>";
        $html .= "<td style='text-align:center; background:#fef3c7;'><b>{$tr->total_points}</b> Poin</td>";
        $html .= "<td style='text-align:center;'><b>{$tr->level_name}</b></td>";
        $html .= "</tr>";
        $no++;
    }
    $html .= "</table>";

    return $html;
});

Route::get('/download-sk-gaji-pdf', function () {
    $filePath = public_path('SURAT_KEPUTUSAN_PENETAPAN_GAJI_DAN_OTORISASI_PEMBDA_2026.pdf');
    if (!file_exists($filePath)) {
        $filePath = base_path('SURAT_KEPUTUSAN_PENETAPAN_GAJI_DAN_OTORISASI_PEMBDA_2026.pdf');
    }
    if (!file_exists($filePath)) {
        abort(404, 'File PDF Surat Keputusan belum tersedia.');
    }
    return response()->download($filePath, 'SURAT_KEPUTUSAN_PENETAPAN_GAJI_DAN_OTORISASI_PEMBDA_2026.pdf');
})->name('download.sk_gaji');

// ================================================================
// PEMBDA KNOWLEDGE & MEDIA (PUBLIC ROUTES)
// ================================================================
Route::get('/knowledge', [App\Http\Controllers\PublicKnowledgeController::class, 'index'])->name('knowledge.index');
Route::get('/knowledge/template/download', [App\Http\Controllers\PublicKnowledgeController::class, 'downloadTemplate'])->name('knowledge.download_template');
Route::get('/knowledge/{slug}', [App\Http\Controllers\PublicKnowledgeController::class, 'show'])->name('knowledge.show');
Route::post('/knowledge/{knowledge}/like', [App\Http\Controllers\PublicKnowledgeController::class, 'toggleLike'])->name('knowledge.like');
Route::post('/knowledge/{knowledge}/bookmark', [App\Http\Controllers\PublicKnowledgeController::class, 'toggleBookmark'])->name('knowledge.bookmark');
Route::get('/knowledge/{knowledge}/download', [App\Http\Controllers\PublicKnowledgeController::class, 'download'])->name('knowledge.download');

Route::get('/debug-salary', function() {
    if (request('secret') !== 'pembda99') return 'Unauthorized';
    
    $schoolId = request('school_id', 3);
    $employees = \App\Models\Employee::where('school_id', $schoolId)->where('is_active', true)->get();
    
    $out = "School $schoolId Employees: " . $employees->count() . "<br>";
    
    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
    $activeSemester = \App\Models\Semester::where('is_active', true)->first();
    $svc = app(\App\Services\EmployeeAssignmentService::class);
    
    foreach($employees as $e) {
        $thp = $svc->calculateFullSalary($e, $activeYear, $activeSemester, null, $schoolId);
        $out .= "Emp: {$e->full_name} | THP: " . ($thp['take_home_pay'] ?? 0);
        $out .= " | Details: " . json_encode($thp) . "<br>";
    }
    
    // payments
    $payments = \App\Models\Payment::with(['bill.paymentType', 'student'])
        ->whereHas('student', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
        ->whereMonth('payment_date', request('month', 8))
        ->whereYear('payment_date', request('year', 2026))
        ->where('is_verified', true)
        ->get();
        
    $out .= "<br>Total Payments: " . $payments->count() . "<br>";
    foreach($payments as $p) {
        $out .= "Payment: {$p->id} Amount: {$p->amount_paid}<br>";
    }
    
    return $out;
});

Route::get('/run-migrations', function () {
    if (request('secret') !== 'pembda99') {
        abort(403, 'Unauthorized secret key');
    }

    $output = "<h2>🚀 PembdaHUB Database Migration Runner</h2>";
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output .= "<h3>Migration Output:</h3><pre style='background:#f4f4f4;padding:12px;border:1px solid #ccc;'>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
        $output .= "<p style='color:green;font-weight:bold;'>✔ Migrasi selesai dieksekusi.</p>";
    } catch (\Throwable $e) {
        $output .= "<h3>Migration Error:</h3><pre style='color:red;background:#fff0f0;padding:12px;border:1px solid #f99;'>" . $e->getMessage() . "</pre>";
    }

    return response($output);
});
