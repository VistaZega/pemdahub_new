<?php
@ini_set('memory_limit', '2G');

/**
 * Diagnostic Deployment Script
 * Akses via browser: https://perguruanpembda.com/check_deploy.php?token=pembda2026check
 */

$SECRET_TOKEN = 'pembda2026check';

if (!isset($_GET['token']) || $_GET['token'] !== $SECRET_TOKEN) {
    http_response_code(403);
    die('⛔ Akses ditolak.');
}

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fixMessage = '';
if (isset($_GET['force_fix']) && $_GET['force_fix'] === 'yes') {
    try {
        Illuminate\Support\Facades\DB::statement("ALTER TABLE applicant_documents MODIFY COLUMN document_type VARCHAR(100) NOT NULL");
        $fixMessage = "<div style='background:#1b5e20;color:#c8e6c9;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #81c784;'>✅ BERHASIL: Tipe kolom 'document_type' telah dipaksa berubah ke VARCHAR(100) menggunakan Raw SQL!</div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#ffcdd2;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #e57373;'>❌ GAGAL: Terjadi error saat mengubah kolom: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_GET['seed_proposal']) && $_GET['seed_proposal'] === 'yes') {
    try {
        $ay = \App\Models\AcademicYear::where('is_active', true)->first();
        if (!$ay) {
            throw new \Exception("Tidak ada tahun ajaran aktif.");
        }
        
        // Cari siswa kelas XII SMA atau SMK yang aktif
        $studentClass = \App\Models\StudentClass::where('academic_year_id', $ay->id)
            ->where('status', 'aktif')
            ->whereHas('classroom', function($q) {
                $q->where('grade_level', 12);
            })
            ->first();
            
        if (!$studentClass) {
            throw new \Exception("Tidak ada siswa kelas XII yang aktif di database.");
        }
        
        $student = $studentClass->student;
        $classroom = $studentClass->classroom;
        
        // Hapus project lama siswa ini jika ada untuk mencegah duplikasi
        $oldProjects = \App\Models\FinalProject::where('student_id', $student->id)->get();
        foreach ($oldProjects as $op) {
            \App\Models\FinalProjectMember::where('final_project_id', $op->id)->delete();
            $op->delete();
        }
        
        $type = $student->school->type === 'SMA' ? 'penelitian_ilmiah' : 'project_akhir';
        
        \Illuminate\Support\Facades\DB::transaction(function() use ($student, $classroom, $ay, $type) {
            $project = \App\Models\FinalProject::create([
                'student_id' => $student->id,
                'academic_year_id' => $ay->id,
                'type' => $type,
                'title' => 'Simulasi Judul Tugas Akhir oleh ' . $student->full_name,
                'abstract' => 'Abstrak simulasi untuk keperluan testing dan verifikasi admin.',
                'status' => 'pending',
            ]);
            
            \App\Models\FinalProjectMember::create([
                'final_project_id' => $project->id,
                'student_id' => $student->id,
                'role' => 'leader'
            ]);
        });
        
        $fixMessage = "<div style='background:#1b5e20;color:#c8e6c9;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #81c784;'>✅ BERHASIL: Berhasil membuat 1 simulasi usulan judul pending untuk siswa '{$student->full_name}' ({$classroom->class_name})! Silakan buka dashboard admin untuk memverifikasi.</div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#ffcdd2;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #e57373;'>❌ GAGAL membuat simulasi: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_GET['list_cross']) && $_GET['list_cross'] === 'yes') {
    try {
        $smk = \App\Models\School::where('type', 'LIKE', '%SMK%')->orWhere('name', 'LIKE', '%SMK%')->first();
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $activeSemester = \App\Models\Semester::where('is_active', true)->first();
        $service = app(\App\Services\EmployeeAssignmentService::class);

        $allEmployees = \App\Models\Employee::with(['activePositions', 'teacher', 'school'])
            ->where('is_active', true)
            ->where(function ($q) use ($smk, $activeYear) {
                $q->where('school_id', $smk->id)
                  ->orWhereHas('activePositions', function ($posQ) use ($smk, $activeYear) {
                      $posQ->where('positions.school_id', $smk->id)
                           ->where('employee_positions.academic_year_id', $activeYear->id);
                  })
                  ->orWhereHas('teacher.teachingAssignments', function ($teachQ) use ($smk, $activeYear) {
                      $teachQ->where('academic_year_id', $activeYear->id)
                             ->where('is_active', true)
                             ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $smk->id));
                  });
            })
            ->get();

        $rows = "";
        $totalCrossThp = 0;
        $idx = 1;

        foreach ($allEmployees as $emp) {
            if ($emp->school_id != $smk->id) {
                $sal = $service->calculateFullSalary($emp, $activeYear, $activeSemester, 'SMK', $smk->id);
                $thp = $sal['thp'] ?? 0;
                $totalCrossThp += $thp;

                $homeName = $emp->school ? $emp->school->name : 'Yayasan / Unit Lain';
                $smkPositions = $emp->activePositions()
                    ->where('positions.school_id', $smk->id)
                    ->get()
                    ->pluck('position_name')
                    ->join(', ');

                $teachingHours = 0;
                if ($emp->teacher) {
                    $assignments = \App\Models\TeachingAssignment::where('teacher_id', $emp->teacher->id)
                        ->where('academic_year_id', $activeYear->id)
                        ->where('is_active', true)
                        ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $smk->id))
                        ->get();
                    $teachingHours = $assignments->sum('hours_per_week');
                }

                $hm = number_format($sal['honor_mengajar'] ?? 0, 0, ',', '.');
                $tj = number_format($sal['tunjangan_jabatan'] ?? 0, 0, ',', '.');
                $hp = number_format($sal['honor_pkl'] ?? 0, 0, ',', '.');
                $t = number_format($thp, 0, ',', '.');

                $rows .= "<tr>
                    <td style='padding:8px;border:1px solid #444;'>{$idx}</td>
                    <td style='padding:8px;border:1px solid #444;'><b>" . htmlspecialchars($emp->full_name) . "</b><br><small>(" . htmlspecialchars($emp->employee_code ?? '-') . " / " . htmlspecialchars($emp->employment_status) . ")</small></td>
                    <td style='padding:8px;border:1px solid #444;'><span style='background:#1e88e5;color:#fff;padding:2px 6px;border-radius:4px;'>" . htmlspecialchars($homeName) . "</span></td>
                    <td style='padding:8px;border:1px solid #444;'>" . htmlspecialchars($smkPositions ?: '-') . "</td>
                    <td style='padding:8px;border:1px solid #444;'>{$teachingHours} JP</td>
                    <td style='padding:8px;border:1px solid #444;'>Rp {$hm}</td>
                    <td style='padding:8px;border:1px solid #444;'>Rp {$tj}</td>
                    <td style='padding:8px;border:1px solid #444;'>Rp {$hp}</td>
                    <td style='padding:8px;border:1px solid #444;'><b>Rp {$t}</b></td>
                </tr>";
                $idx++;
            }
        }

        $totStr = number_format($totalCrossThp, 0, ',', '.');
        $fixMessage = "<div style='background:#1565c0;color:#fff;padding:15px;border-radius:8px;margin-bottom:20px;'>
            <h3>📋 DAFTAR PEGAWAI PERBANTAN DI SMK</h3>
            <table style='width:100%;border-collapse:collapse;color:#fff;'>
                <thead>
                    <tr style='background:#0d47a1;'>
                        <th style='padding:8px;border:1px solid #444;'>No</th>
                        <th style='padding:8px;border:1px solid #444;'>Nama</th>
                        <th style='padding:8px;border:1px solid #444;'>Unit Asal</th>
                        <th style='padding:8px;border:1px solid #444;'>Jabatan SMK</th>
                        <th style='padding:8px;border:1px solid #444;'>Jam Mengajar</th>
                        <th style='padding:8px;border:1px solid #444;'>Honor Mengajar</th>
                        <th style='padding:8px;border:1px solid #444;'>Tunj. Jabatan</th>
                        <th style='padding:8px;border:1px solid #444;'>Honor PKL</th>
                        <th style='padding:8px;border:1px solid #444;'>Total THP</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
            <h4 style='text-align:right;margin-top:15px;'>TOTAL THP PERBANTAN: Rp {$totStr}</h4>
        </div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#fff;padding:15px;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_GET['seed_abraham']) && $_GET['seed_abraham'] === 'yes') {
    try {
        $ay = \App\Models\AcademicYear::where('is_active', true)->first();
        if (!$ay) {
            throw new \Exception("Tidak ada tahun ajaran aktif.");
        }
        
        $student = \App\Models\Student::where('full_name', 'like', '%ABRAHAM%')->first();
        if (!$student) {
            throw new \Exception("Siswa bernama ABRAHAM tidak ditemukan.");
        }
        
        $classroom = $student->currentClassroom()->first();
        if (!$classroom) {
            throw new \Exception("Siswa ABRAHAM tidak memiliki kelas aktif.");
        }
        
        // Hapus project lama siswa ini jika ada untuk mencegah duplikasi
        $oldProjects = \App\Models\FinalProject::where('student_id', $student->id)->get();
        foreach ($oldProjects as $op) {
            \App\Models\FinalProjectMember::where('final_project_id', $op->id)->delete();
            $op->delete();
        }
        
        $type = $student->school->type === 'SMA' ? 'penelitian_ilmiah' : 'project_akhir';
        
        \Illuminate\Support\Facades\DB::transaction(function() use ($student, $classroom, $ay, $type) {
            $project = \App\Models\FinalProject::create([
                'student_id' => $student->id,
                'academic_year_id' => $ay->id,
                'type' => $type,
                'title' => 'Analisis Pengaruh MBG terhadap kualitas Pendidikan',
                'abstract' => 'Mengukur Dampak Kegiatan MBG terhadap kualitas Pendidikan di SMAS Pembda 1 Gunungsitoli',
                'status' => 'pending',
            ]);
            
            \App\Models\FinalProjectMember::create([
                'final_project_id' => $project->id,
                'student_id' => $student->id,
                'role' => 'leader'
            ]);
        });
        
        $fixMessage = "<div style='background:#1b5e20;color:#c8e6c9;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #81c784;'>✅ BERHASIL: Berhasil membuat usulan judul pending untuk ABRAHAM YNGWIE MOZART ZEGA! Silakan buka dashboard admin untuk memverifikasi.</div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#ffcdd2;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #e57373;'>❌ GAGAL membuat usulan: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_GET['fix_academic_year']) && $_GET['fix_academic_year'] === 'yes') {
    try {
        \Illuminate\Support\Facades\DB::transaction(function() {
            // 1. Pastikan Tahun Ajaran 2024/2025, 2025/2026, dan 2026/2027 ada
            $tp24 = \App\Models\AcademicYear::updateOrCreate(
                ['year' => '2024/2025'],
                [
                    'start_date' => '2024-07-01',
                    'end_date' => '2025-06-30',
                    'semester_start' => '2024-07-01',
                    'semester_end' => '2024-12-31',
                    'is_active' => false
                ]
            );
            
            $tp25 = \App\Models\AcademicYear::updateOrCreate(
                ['year' => '2025/2026'],
                [
                    'start_date' => '2025-07-01',
                    'end_date' => '2026-06-30',
                    'semester_start' => '2025-07-01',
                    'semester_end' => '2025-12-31',
                    'is_active' => false
                ]
            );
            
            $tp26 = \App\Models\AcademicYear::updateOrCreate(
                ['year' => '2026/2027'],
                [
                    'start_date' => '2026-07-01',
                    'end_date' => '2027-06-30',
                    'semester_start' => '2026-07-01',
                    'semester_end' => '2026-12-31',
                    'is_active' => true // Set active
                ]
            );

            // Set all other years to inactive
            \App\Models\AcademicYear::where('id', '!=', $tp26->id)->update(['is_active' => false]);

            // 2. Cari siswa Abraham
            $student = \App\Models\Student::where('full_name', 'like', '%ABRAHAM%')->first();
            if ($student) {
                // 3. Pastikan kelas XII Ahmad Yani ada di School ID 3 (SMA) untuk TP 2026/2027
                $classroom = \App\Models\Classroom::updateOrCreate(
                    [
                        'school_id' => 3, // SMA
                        'class_name' => 'XII Ahmad Yani',
                    ],
                    [
                        'grade_level' => 12,
                        'is_active' => true,
                        'academic_year_id' => $tp26->id, // link to 2026/2027
                    ]
                );

                // 4. Hubungkan Abraham ke kelas XII Ahmad Yani untuk TP 2026/2027 di student_classes
                \App\Models\StudentClass::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $tp26->id,
                    ],
                    [
                        'classroom_id' => $classroom->id,
                        'status' => 'aktif',
                    ]
                );
                
                // 5. Update project/proposal Abraham (jika ada) ke academic_year_id yang baru
                \App\Models\FinalProject::where('student_id', $student->id)
                    ->update(['academic_year_id' => $tp26->id]);
            }
        });
        
        $fixMessage = "<div style='background:#1b5e20;color:#c8e6c9;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #81c784;'>✅ BERHASIL: Tahun Pelajaran Aktif diset ke 2026/2027! Kelas XII Ahmad Yani telah dibuat, dan siswa ABRAHAM YNGWIE MOZART ZEGA telah ditempatkan di kelas tersebut untuk TP 2026/2027.</div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#ffcdd2;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #e57373;'>❌ GAGAL memperbaiki tahun ajaran: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_GET['approve_abraham']) && $_GET['approve_abraham'] === 'yes') {
    try {
        $project = \App\Models\FinalProject::findOrFail(11);
        
        // Find teacher Baziduhu Giawa
        $teacher = \App\Models\Teacher::findOrFail(17);
        
        \Illuminate\Support\Facades\DB::transaction(function() use ($project, $teacher) {
            $project->update([
                'advisor_id' => $teacher->id,
                'status' => 'approved',
                'rejection_reason' => null,
            ]);
        });
        
        $fixMessage = "<div style='background:#1b5e20;color:#c8e6c9;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #81c784;'>✅ BERHASIL: Proposal Abraham (ID 11) berhasil disetujui secara manual via script dengan pembimbing Baziduhu Giawa!</div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#ffcdd2;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #e57373;'>❌ GAGAL menyetujui proposal: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_GET['reset_abraham']) && $_GET['reset_abraham'] === 'yes') {
    try {
        $project = \App\Models\FinalProject::findOrFail(11);
        $project->update([
            'advisor_id' => null,
            'status' => 'pending',
            'rejection_reason' => null
        ]);
        $fixMessage = "<div style='background:#1b5e20;color:#c8e6c9;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #81c784;'>✅ BERHASIL: Status proposal Abraham telah direset kembali menjadi PENDING. Silakan test verifikasi lewat dashboard admin!</div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#ffcdd2;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #e57373;'>❌ GAGAL mereset proposal: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_GET['seed_showcase']) && $_GET['seed_showcase'] === 'yes') {
    try {
        $ay = \App\Models\AcademicYear::where('is_active', true)->first() ?? \App\Models\AcademicYear::latest()->first();
        if (!$ay) {
            throw new \Exception("Tidak ada tahun ajaran di database.");
        }

        $smaSchool = \App\Models\School::where('type', 'SMA')->orWhere('name', 'LIKE', '%SMA%')->first();
        $smkSchool = \App\Models\School::where('type', 'SMK')->orWhere('name', 'LIKE', '%SMK%')->first();
        $smpSchool = \App\Models\School::where('type', 'SMP')->orWhere('name', 'LIKE', '%SMP%')->first();

        // 1. Seed / Ensure 4 Penelitian SMA
        $smaTitles = [
            'Analisis Pengaruh MBG terhadap Kualitas Pendidikan di SMAS Pembda 1',
            'Efektivitas Pemanfaatan Platform LMS Digital pada Pembelajaran IPA Terpadu',
            'Studi Eksplorasi Potensi Energi Ramah Lingkungan Berbasis Surya di Nias',
            'Kajian Daya Hambat Ekstrak Herbal Alami terhadap Pertumbuhan Mikroorganisme',
        ];
        $smaStudents = \App\Models\Student::where('school_id', $smaSchool?->id ?? 3)->where('status', 'aktif')->take(4)->get();
        if ($smaStudents->count() < 4) {
            $smaStudents = \App\Models\Student::take(4)->get();
        }
        foreach ($smaTitles as $idx => $title) {
            $student = $smaStudents[$idx % $smaStudents->count()] ?? null;
            if ($student) {
                \App\Models\FinalProject::firstOrCreate(
                    ['title' => $title],
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $ay->id,
                        'type' => 'penelitian_ilmiah',
                        'abstract' => 'Kajian riset ilmiah terstruktur mengenai fenomena ' . strtolower($title) . ' guna memberikan kontribusi nyata bagi pengembangan sains dan kualitas pendidikan.',
                        'status' => 'approved',
                        'current_stage' => 'sidang',
                    ]
                );
            }
        }

        // 2. Seed / Ensure 4 Project SMK
        $smkTitles = [
            'Rancang Bangun Sistem Smart Home Automation Berbasis Mikrokontroler IoT',
            'Implementasi Jaringan Fiber Optic FTTH untuk Wilayah Pedesaan Nias',
            'Pengembangan Prototipe Alat Deteksi Dini Kebocoran Gas Berbasis Sensor MQ-2',
            'Aplikasi Monitoring Pelayanan Servis Kendaraan Ringan Berbasis Android',
        ];
        $smkStudents = \App\Models\Student::where('school_id', $smkSchool?->id ?? 1)->where('status', 'aktif')->take(4)->get();
        if ($smkStudents->count() < 4) {
            $smkStudents = \App\Models\Student::take(4)->get();
        }
        foreach ($smkTitles as $idx => $title) {
            $student = $smkStudents[$idx % $smkStudents->count()] ?? null;
            if ($student) {
                \App\Models\FinalProject::firstOrCreate(
                    ['title' => $title],
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $ay->id,
                        'type' => 'project_akhir',
                        'abstract' => 'Karya rekayasa teknologi terapan yang mengintegrasikan kompetensi kejuruan vokasi ' . strtolower($title) . ' untuk menjawab kebutuhan industri modern.',
                        'status' => 'approved',
                        'current_stage' => 'sidang',
                    ]
                );
            }
        }

        // 3. Seed / Ensure 2 Logbook DUDI
        $dudi = \App\Models\Dudi::firstOrCreate(
            ['name' => 'PT Astra International Tbk - Daihatsu / Honda'],
            [
                'address' => 'Jl. Diponegoro No. 88, Gunungsitoli',
                'field_of_work' => 'Otomotif & Kendaraan Ringan',
                'school_id' => $smkSchool?->id ?? 1,
            ]
        );
        $dudi2 = \App\Models\Dudi::firstOrCreate(
            ['name' => 'PT Telkom Indonesia (Persero) Tbk Witel Gunungsitoli'],
            [
                'address' => 'Jl. Sirao No. 12, Gunungsitoli',
                'field_of_work' => 'Teknologi Informasi & Jaringan',
                'school_id' => $smkSchool?->id ?? 1,
            ]
        );

        $teacher = \App\Models\Teacher::first();

        $placement1 = \App\Models\PklPlacement::firstOrCreate(
            ['student_id' => $smkStudents[0]->id ?? 1],
            [
                'dudi_id' => $dudi->id,
                'academic_year_id' => $ay->id,
                'supervisor_teacher_id' => $teacher?->id ?? 1,
                'status' => 'ongoing',
                'start_date' => '2026-01-10',
                'end_date' => '2026-04-10',
            ]
        );
        $placement2 = \App\Models\PklPlacement::firstOrCreate(
            ['student_id' => $smkStudents[1]->id ?? 2],
            [
                'dudi_id' => $dudi2->id,
                'academic_year_id' => $ay->id,
                'supervisor_teacher_id' => $teacher?->id ?? 1,
                'status' => 'ongoing',
                'start_date' => '2026-01-10',
                'end_date' => '2026-04-10',
            ]
        );

        \App\Models\PklLog::firstOrCreate(
            ['placement_id' => $placement1->id, 'log_date' => '2026-02-15'],
            [
                'activity' => "Kegiatan: Pemeliharaan berkala sistem transmisi dan kelistrikan kendaraan ringan.\nAlat: Scanner OBD-II, Toolkit Kunci Pas, Multimeter Digital.\nPengetahuan: Memahami diagnosa kode error ECU dan prosedur K3 bengkel resmi.",
                'status' => 'approved',
                'verified_by' => $teacher?->id ?? 1,
            ]
        );
        \App\Models\PklLog::firstOrCreate(
            ['placement_id' => $placement2->id, 'log_date' => '2026-02-18'],
            [
                'activity' => "Kegiatan: Penyambungan kabel fiber optik drop core dan instalasi Optical Distribution Point (ODP).\nAlat: Fusion Splicer, Optical Power Meter (OPM), Cleaver.\nPengetahuan: Mengetahui standar redaman dB loss dan manajemen jalur kabel FTTH.",
                'status' => 'approved',
                'verified_by' => $teacher?->id ?? 1,
            ]
        );

        // 4. Seed / Ensure 2 Monitoring Guru
        \App\Models\PklMonitoring::firstOrCreate(
            ['dudi_id' => $dudi->id, 'teacher_id' => $teacher?->id ?? 1, 'monitoring_date' => '2026-02-20'],
            [
                'notes' => 'Supervisi berkala melihat langsung kinerja siswa. Siswa menunjukkan kedisiplinan tinggi dan kemampuan teknis tune-up yang sesuai standar operasional bengkel resmi.',
            ]
        );
        \App\Models\PklMonitoring::firstOrCreate(
            ['dudi_id' => $dudi2->id, 'teacher_id' => $teacher?->id ?? 1, 'monitoring_date' => '2026-02-25'],
            [
                'notes' => 'Kunjungan monitoring evaluasi pelaksanaan PKL di PT Telkom. Pembimbing industri memberikan apresiasi atas penguasaan teori fiber optik dan etika kerja siswa.',
            ]
        );

        // 5. Seed Training Modules jika kurang dari 4
        if (\App\Models\TrainingModule::count() < 4) {
            $seeder = new \Database\Seeders\TrainingModuleSeeder();
            $seeder->run();
        }

        // 6. Seed Prestasi Siswa jika kurang dari 4
        $prestasiSamples = [
            ['title' => 'Juara 1 Lomba Karya Tulis Ilmiah Pelajar Tingkat Provinsi Sumatera Utara', 'ranking' => 'juara_1', 'level' => 'propinsi', 'comp' => 'Kompetisi Sains & Karya Ilmiah Pelajar Sumut 2026'],
            ['title' => 'Juara 1 Lomba Kompetensi Siswa (LKS) SMK Bidang IT Network Administration', 'ranking' => 'juara_1', 'level' => 'propinsi', 'comp' => 'LKS SMK Tingkat Provinsi Sumatera Utara 2026'],
            ['title' => 'Juara 2 Olimpiade Sains Nasional (OSN) Tingkat Kabupaten/Kota Bidang Fisika Terapan', 'ranking' => 'juara_2', 'level' => 'kabupaten', 'comp' => 'Olimpiade Sains Nasional Jenjang SMA 2026'],
            ['title' => 'Juara 1 Turnamen Catur Cepat & Cerdas Cermat Civitas Pelajar Nias', 'ranking' => 'juara_1', 'level' => 'kabupaten', 'comp' => 'Pekan Olahraga & Seni Pelajar Nias 2026'],
        ];
        $allStudents = \App\Models\Student::take(4)->get();
        foreach ($prestasiSamples as $idx => $ps) {
            $s = $allStudents[$idx % $allStudents->count()] ?? null;
            if ($s) {
                \App\Models\StudentCounselingRecord::firstOrCreate(
                    ['title' => $ps['title']],
                    [
                        'student_id' => $s->id,
                        'academic_year_id' => $ay->id,
                        'record_type' => 'penghargaan',
                        'achievement_level' => $ps['level'],
                        'ranking' => $ps['ranking'],
                        'competition_name' => $ps['comp'],
                        'description' => 'Apresiasi prestasi membanggakan yang mengharumkan nama almamater Perguruan Pembda.',
                        'incident_date' => now()->subDays($idx * 10)->format('Y-m-d'),
                    ]
                );
            }
        }

        $fixMessage = "<div style='background:#1b5e20;color:#c8e6c9;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #81c784;'>✅ BERHASIL: Semua 6 kategori data showcase (Penelitian SMA, Project SMK, Logbook DUDI, Monitoring Guru, Modul LMS, Prestasi Juara) telah dipastikan lengkap dan memenuhi kuota di database!</div>";
    } catch (\Exception $e) {
        $fixMessage = "<div style='background:#b71c1c;color:#ffcdd2;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:bold;border:1px solid #e57373;'>❌ GAGAL men-seed showcase: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo "<html><head><title>Deployment & Database Diagnostic</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.8}";
echo ".ok{color:#00e676}.err{color:#ff5252}.info{color:#40c4ff}.warn{color:#ffb300}";
echo "h1{color:#bb86fc}h2{color:#03dac6}pre{background:#16213e;padding:15px;border-radius:8px;overflow-x:auto}";
echo "table.diag{width:100%;border-collapse:collapse;margin:15px 0;background:#16213e;border-radius:8px;overflow:hidden}";
echo "table.diag th{background:#0f3460;color:#00e676;padding:10px;text-align:left;border:1px solid #333}";
echo "table.diag td{padding:8px 10px;border:1px solid #333;font-size:12px}</style></head><body>";
echo "<h1>🔍 Pembda Hub - Deployment & Showcase Diagnostic</h1>";
echo $fixMessage;
echo "<p class=\"info\">Waktu Server: " . now()->format('Y-m-d H:i:s') . "</p>";
echo "<p class=\"info\">DB Host: " . config('database.connections.mysql.host') . " | Database: " . config('database.connections.mysql.database') . " | DB User: " . config('database.connections.mysql.username') . "</p>";

// ==========================================
// 📊 AUDIT LIVE DATA SHOWCASE BERANDA
// ==========================================
echo "<h2>📊 0. AUDIT DATA SHOWCASE BERANDA (6 KATEGORI KESEPAKATAN)</h2>";
try {
    $smaList = \App\Models\FinalProject::with(['student.school', 'student.user'])
        ->where(function($q) {
            $q->whereHas('student.school', fn($sq) => $sq->where('name', 'LIKE', '%SMA%'))
              ->orWhere('type', 'penelitian_ilmiah')
              ->orWhere('type', 'research');
        })
        ->whereNotIn('status', ['rejected', 'draft'])
        ->get();

    $smkList = \App\Models\FinalProject::with(['student.school', 'student.user'])
        ->where(function($q) {
            $q->whereHas('student.school', fn($sq) => $sq->where('name', 'LIKE', '%SMK%'))
              ->orWhere('type', 'project_akhir')
              ->orWhere('type', 'project')
              ->orWhere('type', 'smk');
        })
        ->whereNotIn('status', ['rejected', 'draft'])
        ->get();

    $dudiList = \App\Models\PklLog::where('status', 'approved')
        ->with(['placement.student.school', 'placement.student.user', 'placement.dudi'])
        ->get();

    $monList = \App\Models\PklMonitoring::with(['teacher.user', 'dudi'])->get();

    $modList = \App\Models\LmsCourse::with(['teacher.user', 'school', 'subject', 'classroom'])
        ->where(function($q) {
            $q->where('is_published', true)->orWhere('status', 'active');
        })
        ->get();
    if ($modList->count() === 0) {
        $modList = \App\Models\LmsCourse::with(['teacher.user', 'school', 'subject', 'classroom'])->get();
    }
    $totalLmsModules = \App\Models\LmsModule::count();

    $achList = \App\Models\StudentCounselingRecord::where('record_type', 'penghargaan')
        ->with(['student.school', 'student.user'])
        ->get();

    $c1 = $smaList->count();
    $c2 = $smkList->count();
    $c3 = $dudiList->count();
    $c4 = $monList->count();
    $c5 = $modList->count();
    $c6 = $achList->count();

    $ok1 = $c1 >= 4;
    $ok2 = $c2 >= 4;
    $ok3 = $c3 >= 2;
    $ok4 = $c4 >= 2;
    $ok5 = $c5 >= 4;
    $ok6 = $c6 >= 4;

    echo "<table class='diag'>";
    echo "<thead><tr><th>No</th><th>Kategori Komposisi</th><th>Target Tampil</th><th>Total di Database</th><th>Status</th></tr></thead>";
    echo "<tbody>";
    echo "<tr><td>1</td><td><b>Penelitian Kelas XII SMA</b></td><td>4 Acak</td><td><b>{$c1}</b> data</td><td>" . ($ok1 ? "<span class='ok'>✅ TERPENUHI (>= 4)</span>" : "<span class='err'>❌ KURANG (< 4)</span>") . "</td></tr>";
    echo "<tr><td>2</td><td><b>Project Kelas XII SMK</b></td><td>4 Acak</td><td><b>{$c2}</b> data</td><td>" . ($ok2 ? "<span class='ok'>✅ TERPENUHI (>= 4)</span>" : "<span class='err'>❌ KURANG (< 4)</span>") . "</td></tr>";
    echo "<tr><td>3</td><td><b>Logbook DUDI</b></td><td>2 Acak</td><td><b>{$c3}</b> data</td><td>" . ($ok3 ? "<span class='ok'>✅ TERPENUHI (>= 2)</span>" : "<span class='err'>❌ KURANG (< 2)</span>") . "</td></tr>";
    echo "<tr><td>4</td><td><b>Monitoring Guru</b></td><td>2 Acak</td><td><b>{$c4}</b> data</td><td>" . ($ok4 ? "<span class='ok'>✅ TERPENUHI (>= 2)</span>" : "<span class='err'>❌ KURANG (< 2)</span>") . "</td></tr>";
    echo "<tr><td>5</td><td><b>Modul / Course LMS KBM (SMP/SMA/SMK)</b></td><td>4 Acak</td><td><b>{$c5}</b> Course ({$totalLmsModules} Sub-Modul)</td><td>" . ($ok5 ? "<span class='ok'>✅ TERPENUHI (>= 4)</span>" : "<span class='err'>❌ KURANG (< 4)</span>") . "</td></tr>";
    echo "<tr><td>6</td><td><b>Juara / Prestasi</b></td><td>4 Acak</td><td><b>{$c6}</b> data</td><td>" . ($ok6 ? "<span class='ok'>✅ TERPENUHI (>= 4)</span>" : "<span class='err'>❌ KURANG (< 4)</span>") . "</td></tr>";
    echo "</tbody></table>";

    echo "<p><a href='?token=" . $SECRET_TOKEN . "&seed_showcase=yes' style='display:inline-block;background:#00e676;color:#000;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:14px;box-shadow:0 4px 6px rgba(0,0,0,0.1)'>🚀 Klik Di Sini untuk Auto-Lengkapi / Seed Data Showcase (Semua 6 Kategori)</a></p>";

    // Detail Daftar Data
    echo "<h3>Detail 1: Data Penelitian SMA ($c1)</h3><ul>";
    foreach ($smaList as $p) {
        $st = $p->student?->full_name ?? 'N/A';
        $fn = explode(' ', trim($st))[0];
        $ph = $p->student?->photo_url ? '📷 Foto Ada' : '👤 UI-Avatar';
        echo "<li><b>[ID: {$p->id}]</b> {$p->title} &rarr; Siswa: {$st} (Kata 1: <i>{$fn}</i>) | {$ph} | Status: {$p->status}</li>";
    }
    echo "</ul>";

    echo "<h3>Detail 2: Data Project SMK ($c2)</h3><ul>";
    foreach ($smkList as $p) {
        $st = $p->student?->full_name ?? 'N/A';
        $fn = explode(' ', trim($st))[0];
        $ph = $p->student?->photo_url ? '📷 Foto Ada' : '👤 UI-Avatar';
        echo "<li><b>[ID: {$p->id}]</b> {$p->title} &rarr; Siswa: {$st} (Kata 1: <i>{$fn}</i>) | {$ph} | Status: {$p->status}</li>";
    }
    echo "</ul>";

    echo "<h3>Detail 3: Data Logbook DUDI ($c3)</h3><ul>";
    foreach ($dudiList as $l) {
        $st = $l->placement?->student?->full_name ?? 'N/A';
        $fn = explode(' ', trim($st))[0];
        $d = $l->placement?->dudi?->name ?? 'Mitra DUDI';
        echo "<li><b>[ID: {$l->id}]</b> Mitra: {$d} &rarr; Siswa: {$st} (Kata 1: <i>{$fn}</i>) | Tgl: {$l->log_date} | Status: {$l->status}</li>";
    }
    echo "</ul>";

    echo "<h3>Detail 4: Data Monitoring Guru ($c4)</h3><ul>";
    foreach ($monList as $m) {
        $t = $m->teacher?->full_name ?? 'N/A';
        $fn = explode(' ', trim($t))[0];
        $d = $m->dudi?->name ?? 'Mitra DUDI';
        echo "<li><b>[ID: {$m->id}]</b> Mitra: {$d} &rarr; Guru: {$t} (Kata 1: <i>{$fn}</i>) | Tgl: {$m->monitoring_date}</li>";
    }
    echo "</ul>";

    echo "<h3>Detail 5: Data Modul / Course LMS ($c5 Course, $totalLmsModules Sub-Modul)</h3><ul>";
    foreach ($modList as $mod) {
        $t = $mod->teacher?->full_name ?? 'Guru Pembda';
        $fn = explode(' ', trim($t))[0];
        $sc = $mod->school?->name ?? 'Unit Sekolah';
        $sub = $mod->subject?->name ?? 'Mapel';
        $cl = $mod->classroom?->class_name ?? '-';
        echo "<li><b>[ID: {$mod->id}]</b> {$mod->course_name} &rarr; Guru: {$t} (Kata 1: <i>{$fn}</i>) | Unit: {$sc} | Mapel: {$sub} | Kelas: {$cl}</li>";
    }
    echo "</ul>";

    echo "<h3>Detail 6: Data Prestasi Juara ($c6)</h3><ul>";
    foreach ($achList as $ach) {
        $st = $ach->student?->full_name ?? 'N/A';
        $fn = explode(' ', trim($st))[0];
        echo "<li><b>[ID: {$ach->id}]</b> {$ach->title} &rarr; Siswa: {$st} (Kata 1: <i>{$fn}</i>) | Ranking: {$ach->ranking} | Level: {$ach->achievement_level}</li>";
    }
    echo "</ul>";

} catch (\Exception $e) {
    echo "<p class='err'>❌ Gagal membaca audit showcase: " . $e->getMessage() . "</p>";
}

echo "<h2>1. Pengecekan File Migrasi di Server</h2>";
$migrationFile = '2026_06_13_124710_change_document_type_to_string_in_applicant_documents_table.php';
$migrationPath = __DIR__.'/../database/migrations/' . $migrationFile;

echo "<h2>1.1 Pengecekan File View & Controller di Server</h2>";
$viewsToCheck = [
    'proposals/index.blade.php' => __DIR__.'/../resources/views/admin/final_projects/proposals/index.blade.php',
    'exams/index.blade.php' => __DIR__.'/../resources/views/admin/final_projects/exams/index.blade.php',
    'FinalProjectAdminController.php' => __DIR__.'/../app/Http/Controllers/Admin/FinalProjectAdminController.php',
];
foreach ($viewsToCheck as $name => $path) {
    if (file_exists($path)) {
        echo "<p class='ok'>✅ File <b>$name</b> ditemukan. Waktu Modifikasi: " . date("Y-m-d H:i:s", filemtime($path)) . "</p>";
    } else {
        echo "<p class='err'>❌ File <b>$name</b> TIDAK DITEMUKAN di path: $path</p>";
    }
}

echo "<h2>1.2 Pengecekan Database & Tabel di Server</h2>";
try {
    $dbs = \Illuminate\Support\Facades\DB::select("SHOW DATABASES");
    echo "<p>Daftar Database di Server:</p><ul>";
    foreach ($dbs as $db) {
        foreach ($db as $k => $v) {
            echo "<li>$v</li>";
        }
    }
    echo "</ul>";

    $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES");
    echo "<p>Daftar Tabel di Database Aktif:</p><pre style='max-height:150px;overflow:auto;background:#000;color:#fff;padding:10px;'>";
    foreach ($tables as $table) {
        foreach ($table as $k => $v) {
            echo "$v\n";
        }
    }
    echo "</pre>";
} catch (\Exception $e) {
    echo "<p class='err'>❌ Gagal membaca database/tabel: " . $e->getMessage() . "</p>";
}

if (file_exists($migrationPath)) {
    echo "<p class='ok'>✅ File ditemukan di disk: <b>$migrationFile</b></p>";
    echo "<p>Waktu Modifikasi File: " . date("Y-m-d H:i:s", filemtime($migrationPath)) . "</p>";
} else {
    echo "<p class='err'>❌ FILE TIDAK DITEMUKAN: <b>$migrationFile</b></p>";
    echo "<p class='warn'>⚠️ Ini mengindikasikan deploy Git di Hostinger belum berjalan atau gagal.</p>";
    echo "<p>Daftar file migrasi terakhir di server:</p><pre>";
    $files = scandir(__DIR__.'/../database/migrations/');
    $count = 0;
    foreach (array_reverse($files) as $file) {
        if ($file !== '.' && $file !== '..' && $count < 10) {
            echo " - $file\n";
            $count++;
        }
    }
    echo "</pre>";
}

/**
 * Read last N lines from a file efficiently without loading entire file into memory.
 */
function readLastLines(string $path, int $lines = 40): string
{
    if (!file_exists($path)) return '';
    $fp = fopen($path, 'r');
    if (!$fp) return '';
    fseek($fp, 0, SEEK_END);
    $pos = ftell($fp);
    $output = [];
    $currentLine = '';
    $lineCount = 0;
    // Read from end of file backwards
    for ($i = $pos - 1; $i >= 0; $i--) {
        fseek($fp, $i);
        $char = fgetc($fp);
        if ($char === "\n" || $char === "\r") {
            if ($currentLine !== '') {
                $output[] = $currentLine;
                $currentLine = '';
                $lineCount++;
                if ($lineCount >= $lines) break;
            }
        } else {
            $currentLine = $char . $currentLine;
        }
    }
    if ($currentLine !== '' && $lineCount < $lines) {
        $output[] = $currentLine;
    }
    fclose($fp);
    return implode("\n", array_reverse($output));
}

echo "<h2>2. Pengecekan Struktur Kolom di Database</h2>";
try {
    $results = Illuminate\Support\Facades\DB::select("DESCRIBE applicant_documents");
    $found = false;
    foreach ($results as $column) {
        if ($column->Field === 'document_type') {
            echo "<p>Tipe kolom <b>document_type</b> saat ini di database: <b class='info'>" . htmlspecialchars($column->Type) . "</b></p>";
            if (str_contains($column->Type, 'enum')) {
                echo "<p class='warn'>⚠️ Kolom masih bertipe ENUM! Klik tombol di bawah ini untuk memperbaikinya secara langsung:</p>";
                echo "<p><a href='?token=" . $SECRET_TOKEN . "&force_fix=yes' style='display:inline-block;background:#ff9100;color:#000;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:14px;box-shadow:0 4px 6px rgba(0,0,0,0.1)'>🔨 Paksa Ubah Kolom ke VARCHAR via Raw SQL</a></p>";
            } else {
                echo "<p class='ok'>✅ Tipe kolom sudah benar (bukan ENUM).</p>";
            }
            $found = true;
        }
    }
    if (!$found) {
        echo "<p class='err'>❌ Kolom 'document_type' tidak ditemukan di tabel 'applicant_documents'!</p>";
    }
} catch (\Exception $e) {
    echo "<p class='err'>❌ Gagal membaca struktur tabel: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Pengecekan Tabel Migrasi (Database)</h2>";
try {
    $results = Illuminate\Support\Facades\DB::table('migrations')->get();
    echo "<p>Total migrasi yang tercatat di database: <b>" . $results->count() . "</b></p>";
    $foundInDb = false;
    foreach ($results as $m) {
        if (str_contains($m->migration, 'change_document_type')) {
            echo "<p class='warn'>⚠️ Catatan migrasi ditemukan di database: <b>" . htmlspecialchars($m->migration) . "</b> (Batch: " . $m->batch . ")</p>";
            echo "<p class='warn'>Jika tipe kolom masih ENUM tapi migrasi tercatat sudah jalan, database perlu diperbaiki manual atau di-rollback.</p>";
            $foundInDb = true;
        }
    }
    if (!$foundInDb) {
        echo "<p class='ok'>✅ Catatan migrasi belum terdaftar di database (siap dijalankan).</p>";
    }
} catch (\Exception $e) {
    echo "<p class='err'>❌ Gagal membaca tabel migrations: " . $e->getMessage() . "</p>";
}
echo "<h2>4. Diagnostik Final Projects / Tugas Akhir</h2>";
try {
    $totalProjects = \App\Models\FinalProject::count();
    $totalStudents = \App\Models\Student::count();
    $totalTeachers = \App\Models\Teacher::count();
    $totalUsers = \App\Models\User::count();
    
    echo "<p>Total projects: <b>$totalProjects</b></p>";
    echo "<p>Total students: <b>$totalStudents</b></p>";
    echo "<p>Total teachers: <b>$totalTeachers</b></p>";
    echo "<p>Total users: <b>$totalUsers</b></p>";
    
    if ($totalProjects > 0) {
        $projects = \App\Models\FinalProject::with(['student.school', 'advisor'])->get();
        echo "<ul>";
        foreach ($projects as $p) {
            echo "<li>ID: {$p->id} | Title: {$p->title} | Student: " . ($p->student->full_name ?? 'N/A') . " (" . ($p->student->school->name ?? 'N/A') . ") | Status: {$p->status} | Advisor: " . ($p->advisor->full_name ?? 'None') . "</li>";
        }
        echo "</ul>";
        
        echo "<h4>Raw DB Table dump for final_projects:</h4>";
        $rawProjects = \DB::table('final_projects')->get();
        echo "<ul>";
        foreach ($rawProjects as $rp) {
            echo "<li>Raw ID: {$rp->id} | Title: {$rp->title} | Student ID: {$rp->student_id} | Status: {$rp->status}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warn'>⚠️ Tidak ada project di database. Silakan ajukan usulan judul lewat akun siswa kelas XII SMA/SMK terlebih dahulu.</p>";
    }
    echo "<p><a href='?token=" . $SECRET_TOKEN . "&seed_proposal=yes' style='display:inline-block;background:#03dac6;color:#000;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;margin-right:10px;'>➕ Simulasikan 1 Usulan Judul Siswa Baru</a>";
    echo "<a href='?token=" . $SECRET_TOKEN . "&seed_abraham=yes' style='display:inline-block;background:#bb86fc;color:#000;padding:10px 20px;border-radius:8px;text-decoration:none;margin-right:10px;font-weight:bold;'>➕ Buat Usulan Judul untuk ABRAHAM</a>";
    echo "<a href='?token=" . $SECRET_TOKEN . "&fix_academic_year=yes' style='display:inline-block;background:#ff9100;color:#000;padding:10px 20px;border-radius:8px;text-decoration:none;margin-right:10px;font-weight:bold;'>🔧 Set TP Aktif 2026/2027 & Pindahkan Abraham ke XII Ahmad Yani</a>";
    echo "<a href='?token=" . $SECRET_TOKEN . "&approve_abraham=yes' style='display:inline-block;background:#00e676;color:#000;padding:10px 20px;border-radius:8px;text-decoration:none;margin-right:10px;font-weight:bold;'>✔️ Paksa Setujui Proposal Abraham (Baziduhu)</a>";
    echo "<a href='?token=" . $SECRET_TOKEN . "&reset_abraham=yes' style='display:inline-block;background:#ff1744;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>🔄 Reset Status Proposal Abraham ke Pending</a></p>";
} catch (\Exception $e) {
    echo "<p class='err'>❌ Gagal membaca database: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Diagnostik Khusus: ABRAHAM YNGWIE MOZART ZEGA</h2>";
try {
    $students = \App\Models\Student::where('full_name', 'like', '%ABRAHAM%')->get();
    echo "<p>Jumlah siswa mirip 'ABRAHAM': <b>" . $students->count() . "</b></p>";
    foreach ($students as $s) {
        echo "<div><b>Siswa:</b> {$s->full_name} (ID: {$s->id}, School ID: {$s->school_id})<br>";
        
        // Cek semua kelas di student_classes
        $scs = \DB::table('student_classes')
            ->join('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
            ->join('academic_years', 'student_classes.academic_year_id', '=', 'academic_years.id')
            ->where('student_classes.student_id', $s->id)
            ->select('classrooms.class_name', 'classrooms.grade_level', 'academic_years.year', 'student_classes.status')
            ->get();
        echo "<b>Semua Kelas Terdaftar:</b><br>";
        foreach ($scs as $sc) {
            echo "  - {$sc->class_name} (Grade {$sc->grade_level}) | TP: {$sc->year} | Status: {$sc->status}<br>";
        }
        
        echo "<b>Daftar Tahun Ajaran di Database:</b><br>";
        foreach (\App\Models\AcademicYear::all() as $y) {
            echo "  - ID: {$y->id} | Year: {$y->year} | Active: " . ($y->is_active ? '🟢 YA' : '🔴 TIDAK') . "<br>";
        }
        
        // Cek user login
        $u = $s->user;
        echo "<b>User Login:</b> " . ($u ? "Username: {$u->username}, Email: {$u->email}, Role: {$u->role}, User School ID: " . ($u->school_id ?? 'NULL') : "TIDAK ADA USER LINKED") . "<br>";
        
        // Cek projects direct
        $pDirect = \App\Models\FinalProject::where('student_id', $s->id)->get();
        echo "<b>Direct Projects (Lead):</b> " . $pDirect->count() . "<br>";
        foreach ($pDirect as $p) {
            echo "  - Project ID: {$p->id} | Title: {$p->title} | Status: {$p->status} | Advisor: " . ($p->advisor->full_name ?? 'None') . "<br>";
        }
        
        // Cek projects membership
        $memberships = \App\Models\FinalProjectMember::where('student_id', $s->id)->get();
        echo "<b>Membership Projects (Member):</b> " . $memberships->count() . "<br>";
        foreach ($memberships as $m) {
            $p = $m->finalProject;
            if ($p) {
                echo "  - Project ID: {$p->id} | Title: {$p->title} | Status: {$p->status} | Role: {$m->role} | Leader: " . ($p->student->full_name ?? 'N/A') . "<br>";
            } else {
                echo "  - Membership has orphaned final_project_id: {$m->final_project_id}<br>";
            }
        }
        echo "</div><hr>";
    }
    
    echo "<h3>Daftar Guru di Database</h3>";
    $teachers = \App\Models\Teacher::all();
    echo "Total guru: " . $teachers->count() . "<br>";
    foreach ($teachers as $t) {
        $schoolName = $t->school->name ?? 'N/A';
        echo "  - ID: {$t->id} | Name: {$t->full_name} | School ID: {$t->school_id} ({$schoolName}) | User ID: " . ($t->user_id ?? 'NULL') . "<br>";
    }

    echo "<h3>Daftar Admin Sekolah di Database</h3>";
    $admins = \App\Models\User::where('role', 'admin_sekolah')->get();
    foreach ($admins as $ad) {
        $schoolName = $ad->school->name ?? 'N/A';
        echo "  - ID: {$ad->id} | Name: {$ad->name} | Email: {$ad->email} | School ID: {$ad->school_id} ({$schoolName})<br>";
    }

    echo "<h3>Daftar File Log di Server</h3>";
    $logDir = storage_path('logs/');
    if (is_dir($logDir)) {
        $logFiles = scandir($logDir);
        foreach ($logFiles as $lf) {
            if ($lf !== '.' && $lf !== '..') {
                $lfPath = $logDir . $lf;
                echo "  - $lf (" . filesize($lfPath) . " bytes) | Modifikasi: " . date("Y-m-d H:i:s", filemtime($lfPath)) . "<br>";
                if (str_contains($lf, '.log') && filesize($lfPath) > 0) {
                    echo "<pre style='background:#000;color:#fff;padding:10px;font-size:11px;overflow:auto;max-height:200px;text-align:left;'>";
                    echo htmlspecialchars(readLastLines($lfPath, 40));
                    echo "</pre>";
                }
            }
        }
    } else {
        echo "Direktori log tidak ditemukan atau bukan direktori.<br>";
    }

    echo "<h3>Detail File .env di Server</h3>";
    $envPath = __DIR__.'/pembdahub/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), 'DB_') || str_starts_with(trim($line), 'APP_')) {
                echo htmlspecialchars($line) . "<br>";
            }
        }
    } else {
        echo "File .env tidak ditemukan di path: $envPath<br>";
    }

    echo "<h3>Daftar Folder di public_html</h3>";
    $files = scandir(__DIR__);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            if (is_dir(__DIR__ . '/' . $f)) {
                echo "Dir: $f<br>";
            } else {
                echo "File: $f<br>";
            }
        }
    }

    echo "<h3>Daftar Folder di parent directory</h3>";
    $parentDir = dirname(__DIR__);
    $pFiles = scandir($parentDir);
    foreach ($pFiles as $pf) {
        if ($pf !== '.' && $pf !== '..') {
            if (is_dir($parentDir . '/' . $pf)) {
                echo "Dir: $pf<br>";
            } else {
                echo "File: $pf<br>";
            }
        }
    }

    echo "<h3>Isi File index.php di public_html</h3>";
    $indexPath = __DIR__ . '/index.php';
    if (file_exists($indexPath)) {
        echo "<pre>" . htmlspecialchars(file_get_contents($indexPath)) . "</pre>";
    } else {
        echo "File index.php tidak ditemukan di: $indexPath<br>";
    }
} catch (\Exception $e) {
    echo "<p class='err'>❌ Gagal menjalankan diagnostik khusus: " . $e->getMessage() . "</p>";
}

echo "<hr><p class='warn'>🗑️ Hapus file check_deploy.php setelah selesai digunakan demi keamanan!</p>";
echo "</body></html>";
