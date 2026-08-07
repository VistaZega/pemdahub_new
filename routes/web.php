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
use Illuminate\Support\Facades\Log;

// ============================================================
//  PUBLIC DISPLAY - Live Monitoring Kehadiran (Raspberry Pi)
//  Tidak memerlukan login. Akses: /display
// ============================================================
Route::prefix('display')->name('display.')->group(function () {
    Route::get('/',          [PublicDisplayController::class, 'index'])->name('index');
    Route::get('/live-data', [PublicDisplayController::class, 'liveData'])->name('live-data');
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
        ->latest('published_at')
        ->take(3)
        ->get();

    $galleryItems = \App\Models\GalleryItem::active()
        ->ordered()
        ->take(7)
        ->get();

    $trainingModules = \App\Models\TrainingModule::published()
        ->whereNotNull('pdf_file')
        ->orderBy('sort_order')
        ->get();

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
    $totalAlumni = \App\Models\Alumni::count();

    // Statistik platform digital
    $totalCourses = \App\Models\LmsCourse::where('is_published', true)->count();
    $totalExams = \App\Models\CbtExam::count();
    $totalForumThreads = \App\Models\ForumThread::count();

    // Prestasi siswa (6 terbaik, prioritas level tertinggi)
    $achievements = \App\Models\StudentCounselingRecord::where('record_type', 'penghargaan')
        ->with(['student.school'])
        ->orderByRaw("FIELD(achievement_level, 'internasional','nasional','propinsi','kabupaten','sekolah') ASC")
        ->orderByRaw("FIELD(ranking, 'juara_1','juara_2','juara_3','best_speaker','mvp','harapan_1','harapan_2','harapan_3','finalis','peserta') ASC")
        ->latest('incident_date')
        ->take(6)
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

    // PSB
    $activeWave = \App\Models\RegistrationWave::where('is_active', true)->first();
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
    $pklLogs = \App\Models\PklLog::where('status', 'approved')
        ->whereNotNull('photo')
        ->with(['placement.student.school', 'placement.dudi'])
        ->latest('log_date')
        ->take(8)
        ->get()
        ->map(fn($log) => [
            'type' => 'logbook',
            'photo' => $log->photo_url,
            'description' => \Illuminate\Support\Str::limit($log->activity, 150),
            'person_name' => $log->placement?->student?->full_name ?? 'Siswa PKL',
            'person_photo' => $log->placement?->student?->photo_url ?? null,
            'school_name' => $log->placement?->student?->school?->name ?? '-',
            'dudi_name' => $log->placement?->dudi?->name ?? $log->placement?->company_name ?? '-',
            'date' => $log->log_date?->translatedFormat('d M Y') ?? '-',
        ]);

    $pklMonitorings = \App\Models\PklMonitoring::whereNotNull('photo_path')
        ->with(['teacher', 'dudi'])
        ->latest('monitoring_date')
        ->take(4)
        ->get()
        ->map(fn($m) => [
            'type' => 'monitoring',
            'photo' => asset('storage/' . $m->photo_path),
            'description' => \Illuminate\Support\Str::limit($m->notes, 150),
            'person_name' => $m->teacher?->full_name ?? 'Guru Pembimbing',
            'person_photo' => null,
            'school_name' => 'Guru Pembimbing',
            'dudi_name' => $m->dudi?->name ?? '-',
            'date' => $m->monitoring_date?->translatedFormat('d M Y') ?? '-',
        ]);

    $pklShowcase = $pklLogs->concat($pklMonitorings)->shuffle()->values();
    $totalApprovedLogs = \App\Models\PklLog::where('status', 'approved')->count();
    $totalMonitorings = \App\Models\PklMonitoring::count();
    $totalDudi = \App\Models\Dudi::count();

    // === TOP HALL OF FAME PEMBDA ELITE FOR HOMEPAGE ===
    $topStudentsElite = \App\Models\Reputation::with(['user.student.classroom.school', 'user.student.school', 'user.school', 'user.badges'])
        ->whereHas('user', function($q) {
            $q->where('role', 'siswa');
        })
        ->orderBy('total_points', 'desc')
        ->take(4)
        ->get();

    $topTeachersElite = \App\Models\Reputation::with(['user.teacher.school', 'user.badges'])
        ->whereHas('user', function($q) {
            $q->where('role', 'guru');
        })
        ->orderBy('total_points', 'desc')
        ->take(4)
        ->get();

    // Pastikan halaman beranda tidak dicache oleh server (LiteSpeed) maupun browser
    // agar status tombol "Login" vs "Dashboard" selalu ter-update secara real-time.
    return response(view('index', compact(
        'news', 'galleryItems', 'trainingModules',
        'totalStudents', 'totalTeachers', 'totalSchools', 'totalAlumni',
        'totalCourses', 'totalExams', 'totalForumThreads',
        'achievements', 'totalAchievements',
        'schools', 'activeWave', 'totalApplicants',
        'recentAlumnis',
        'pklShowcase', 'totalApprovedLogs', 'totalMonitorings', 'totalDudi',
        'topStudentsElite', 'topTeachersElite'
    )))
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('X-LiteSpeed-Cache-Control', 'no-cache');
})->name('home');

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

// IKA PEMBDA - Public Alumni Registration
Route::prefix('ika-pembda')->name('ika.')->group(function () {
    Route::get('/daftar', [App\Http\Controllers\PublicAlumniController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [App\Http\Controllers\PublicAlumniController::class, 'registerSubmit'])->name('register.submit');
});

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
        $role = session('active_role', auth()->user()->role);
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
    Route::post('/switch-school', [AuthController::class, 'switchSchool'])->name('switch-school');

    // Profile Settings
    Route::get('/profile/settings', [App\Http\Controllers\ProfileSettingsController::class, 'edit'])->name('profile.settings');
    Route::put('/profile/settings', [App\Http\Controllers\ProfileSettingsController::class, 'update'])->name('profile.settings.update');
    Route::put('/profile/biodata', [App\Http\Controllers\ProfileSettingsController::class, 'updateBiodata'])->name('profile.biodata.update');
    Route::post('/profile/photo', [App\Http\Controllers\ProfileSettingsController::class, 'updatePhoto'])->name('profile.photo.update');

    // Admin Sekolah Routes - Redirect ke /admin (shared with SuperAdmin)
    Route::prefix('sekolah')->name('sekolah.')->group(function () {
        Route::get('/dashboard', function () {
            // Redirect admin_sekolah ke /admin/dashboard (shared route dengan filtering)
            return redirect()->route('admin.dashboard');
        })->name('dashboard');
    });

    // Alumni Routes
    Route::prefix('alumni-portal')->name('alumni.')->middleware('role:alumni,superadmin,admin_yayasan,admin_sekolah,guru')->group(function () {
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
    });

    // Reputation & Hall of Fame
    Route::get('/hall-of-fame', [App\Http\Controllers\Reputation\LeaderboardController::class, 'index'])->name('reputation.leaderboard');

    // Hub Forum
    Route::prefix('forum')->name('forum.')->group(function () {
        Route::get('/', [App\Http\Controllers\ForumController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ForumController::class, 'create'])->name('create');
        Route::post('/store', [App\Http\Controllers\ForumController::class, 'store'])->name('store');
        
        // Pembda Place & Colabs (MUST BE BEFORE /{thread})
        Route::get('/place/canvas', [App\Http\Controllers\ForumController::class, 'getPlaceCanvas'])->name('place.canvas');
        Route::post('/place/draw', [App\Http\Controllers\ForumController::class, 'drawPlacePixel'])->name('place.draw');
        Route::get('/place/updates', [App\Http\Controllers\ForumController::class, 'getPlaceUpdates'])->name('place.updates');
        Route::get('/puzzle', [App\Http\Controllers\ForumController::class, 'getPuzzleState'])->name('puzzle.state');
        Route::post('/puzzle/place', [App\Http\Controllers\ForumController::class, 'placePuzzlePiece'])->name('puzzle.place');
        Route::post('/puzzle/reset', [App\Http\Controllers\ForumController::class, 'resetPuzzle'])->name('puzzle.reset');
        Route::get('/{thread}', [App\Http\Controllers\ForumController::class, 'show'])->name('show');
        Route::post('/{thread}/reply', [App\Http\Controllers\ForumController::class, 'reply'])->name('reply');
        Route::post('/{thread}/like', [App\Http\Controllers\ForumController::class, 'like'])->name('like');
        Route::delete('/{thread}', [App\Http\Controllers\ForumController::class, 'destroy'])->name('destroy');
        Route::get('/{thread}/edit', [App\Http\Controllers\ForumController::class, 'edit'])->name('edit');
        Route::put('/{thread}', [App\Http\Controllers\ForumController::class, 'update'])->name('update');
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

        echo "<h1>=== RUNNING MIKROKONTROLER COURSE SEEDER ===</h1>\n";
        $mikroSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'MikrokontrolerCourseSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nMikrokontroler Course Seeder Exit Code: " . $mikroSeederExitCode . "\n\n";

        echo "<h1>=== SPP TARIFF SEEDER ===</h1>\n";
        $sppSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SppTariffSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nSPP Tariff Seeder Exit Code: " . $sppSeederExitCode . "\n\n";

        echo "<h1>=== RUNNING PKL SUPERVISOR SEEDER ===</h1>\n";
        $pklSeederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'PklSupervisorPositionSeeder', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nPKL Supervisor Seeder Exit Code: " . $pklSeederExitCode . "\n\n";

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
        $alumniSyncExitCode = \Illuminate\Support\Facades\Artisan::call('alumni:sync');
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "\nAlumni Sync Exit Code: " . $alumniSyncExitCode . "\n\n";

        echo "<b><h2 style='color:#0f0;'>✅ MIGRATION AND SYNC COMPLETED SUCCESSFULLY!</h2></b>\n";
    } catch (\Exception $e) {
        echo "<b style='color:#f00;'>ERROR: " . $e->getMessage() . "</b>\n";
    }
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


