<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\EducationalCalendar;
use App\Models\PaymentType;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use App\Models\LmsCourse;
use App\Models\CbtExam;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ProgressInputController extends Controller
{
    /**
     * Tampilkan halaman Progress Input Data untuk akun Yayasan
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $data = $this->getProgressData($academicYearId);

        return view('yayasan.progress_input.index', $data);
    }

    /**
     * Export rekap progress input ke PDF
     */
    public function exportPdf(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $data = $this->getProgressData($academicYearId);

        $pdf = Pdf::loadView('yayasan.progress_input.pdf', $data)
            ->setPaper('a4', 'landscape');

        $fileName = 'rekap_progress_input_data_' . str_replace('/', '_', $data['currentYear']->year ?? '2026_2027') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Helper untuk menghitung 12 indikator progress mendalam pada semua unit sekolah
     */
    private function getProgressData($academicYearId = null)
    {
        // 1. Cari Tahun Pelajaran (Prioritaskan TP 2026/2027 jika tidak ada pilihan)
        if ($academicYearId) {
            $currentYear = AcademicYear::find($academicYearId);
        } else {
            $currentYear = AcademicYear::where('year', 'like', '%2026/2027%')->first();
            if (!$currentYear) {
                $currentYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
            }
        }

        $allYears = AcademicYear::orderBy('year', 'desc')->get();
        $schools = School::schoolsOnly()->where('is_active', true)->orderBy('name')->get();

        $items = [];

        // ════════════════ ITEM 1: DATA SISWA BARU ════════════════
        $item1Schools = [];
        foreach ($schools as $school) {
            $isSmp = stripos($school->type, 'SMP') !== false || stripos($school->name, 'SMP') !== false;
            $targetGrades = $isSmp ? [7] : [10];
            $gradeLabel = $isSmp ? 'VII SMP' : 'X SMA/SMK';

            $countFromClass = 0;
            if ($currentYear) {
                $countFromClass = StudentClass::where('academic_year_id', $currentYear->id)
                    ->whereHas('classroom', function ($q) use ($school, $targetGrades) {
                        $q->where('school_id', $school->id)->whereIn('grade_level', $targetGrades);
                    })
                    ->distinct('student_id')
                    ->count('student_id');
            }

            $countFromStudent = Student::where('school_id', $school->id)
                ->where('status', 'aktif')
                ->whereHas('currentClassroom', function ($q) use ($targetGrades) {
                    $q->whereIn('grade_level', $targetGrades);
                })
                ->count();

            $siswaBaru = max($countFromClass, $countFromStudent);
            $totalSiswaAktif = Student::where('school_id', $school->id)->where('status', 'aktif')->count();
            
            $targetRombels = Classroom::where('school_id', $school->id)
                ->whereIn('grade_level', $targetGrades)
                ->where('is_active', true)
                ->get();
            $rombelBaruCount = $targetRombels->count();

            $rombelList = [];
            foreach ($targetRombels as $rb) {
                $countRb = StudentClass::where('classroom_id', $rb->id)
                    ->when($currentYear, function($q) use ($currentYear) {
                        $q->where('academic_year_id', $currentYear->id);
                    })
                    ->count();
                $rombelList[] = "{$rb->name}: {$countRb} Siswa";
            }

            if ($siswaBaru == 0) {
                $rekomendasi = "Belum ada data siswa baru kelas {$gradeLabel} yang terinput ke rombel. Segera proses data PPDB/PSB atau import Excel.";
                $statusColor = 'red';
            } else {
                $rekomendasi = "Data siswa kelas {$gradeLabel} terinput {$siswaBaru} siswa. Pastikan seluruh siswa baru sudah masuk rombel.";
                $statusColor = 'green';
            }

            $details = [
                "Siswa Baru Kelas {$gradeLabel}: {$siswaBaru} Orang",
                "Jumlah Rombel {$gradeLabel}: {$rombelBaruCount} Kelas",
                "Total Siswa Aktif Unit: {$totalSiswaAktif} Orang",
            ];
            if (!empty($rombelList)) {
                $details[] = "Rincian Kelas: " . implode(', ', array_slice($rombelList, 0, 3));
            }

            $item1Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$siswaBaru} Siswa",
                'satuan' => 'Siswa',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $siswaBaru,
                'details' => $details,
                'action_items' => $siswaBaru == 0 ? ["Proses PPDB / Tambah Rombel Kelas {$gradeLabel}"] : [],
            ];
        }
        $items[] = [
            'number' => 1,
            'title' => 'Data Siswa Baru',
            'description' => 'Jumlah siswa kelas VII SMP dan X SMA/SMK yang sudah masuk dalam rincian rombongan belajar',
            'schools_data' => $item1Schools,
        ];

        // ════════════════ ITEM 2: FINALISASI PROFILE GURU ════════════════
        $item2Schools = [];
        foreach ($schools as $school) {
            $teachers = Teacher::where('school_id', $school->id)->get();
            if ($teachers->isEmpty()) {
                $teachers = Employee::where('school_id', $school->id)
                    ->where('employee_type', 'guru')
                    ->where('is_active', true)
                    ->get();
            }

            $totalGuru = $teachers->count();
            $completeGuru = 0;
            $hasNikCount = 0;
            $hasEduCount = 0;
            $hasPhoneCount = 0;
            $incompleteNames = [];

            foreach ($teachers as $t) {
                $hasId = !empty($t->nik) || !empty($t->nuptk) || !empty($t->teacher_code) || !empty($t->employee_code);
                $hasBirth = !empty($t->birth_place) && !empty($t->birth_date);
                $hasEdu = !empty($t->education_level) || !empty($t->last_education);
                $hasPhone = !empty($t->phone) || !empty($t->phone_number);

                if ($hasId) $hasNikCount++;
                if ($hasEdu) $hasEduCount++;
                if ($hasPhone) $hasPhoneCount++;

                if ($hasId && $hasBirth && $hasEdu && $hasPhone) {
                    $completeGuru++;
                } else {
                    $name = $t->name ?? $t->full_name ?? 'Guru';
                    $incompleteNames[] = $name;
                }
            }

            $pct = $totalGuru > 0 ? round(($completeGuru / $totalGuru) * 100, 1) : 0;
            $incomplete = max(0, $totalGuru - $completeGuru);

            if ($totalGuru == 0) {
                $rekomendasi = "Belum ada data guru terdaftar di unit ini. Segera tambahkan data master guru/pegawai.";
                $statusColor = 'red';
            } elseif ($pct < 50) {
                $rekomendasi = "Persentase kelengkapan profil guru masih rendah ({$pct}%). Himbau guru untuk melengkapi NIK, NUPTK, dan riwayat pendidikan.";
                $statusColor = 'red';
            } elseif ($pct < 100) {
                $rekomendasi = "Kelengkapan mencapai {$pct}% ({$completeGuru} dari {$totalGuru} guru). Koordinasikan kepada {$incomplete} guru yang belum lengkap.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Seluruh profil data guru ({$totalGuru} orang) telah lengkap terisi 100%.";
                $statusColor = 'green';
            }

            $details = [
                "Profil 100% Lengkap: {$completeGuru} dari {$totalGuru} Guru",
                "NIK / NUPTK Terisi: {$hasNikCount} Guru",
                "Pendidikan & Kontak Terisi: {$hasEduCount} Guru",
                "Perlu Dilengkapi: {$incomplete} Guru",
            ];
            if (!empty($incompleteNames)) {
                $showCount = 3;
                $sampleIncomplete = array_slice($incompleteNames, 0, $showCount);
                $more = count($incompleteNames) > $showCount ? ' + ' . (count($incompleteNames) - $showCount) . ' lainnya' : '';
                $details[] = "Perlu Lengkapi: " . implode(', ', $sampleIncomplete) . $more;
            }

            $item2Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$pct}% ({$completeGuru}/{$totalGuru} Guru)",
                'satuan' => 'Persentase (%)',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $pct,
                'details' => $details,
                'action_items' => $incompleteNames,
            ];
        }
        $items[] = [
            'number' => 2,
            'title' => 'Finalisasi Profile Guru',
            'description' => 'Persentase kelengkapan data NIK, NUPTK, Tempat/Tgl Lahir, dan kualifikasi pendidikan guru',
            'schools_data' => $item2Schools,
        ];

        // ════════════════ ITEM 3: FINALISASI PROFILE SISWA ════════════════
        $item3Schools = [];
        foreach ($schools as $school) {
            $students = Student::where('school_id', $school->id)->where('status', 'aktif')->get();
            $totalSiswa = $students->count();
            $completeSiswa = 0;
            $hasNisnCount = 0;
            $hasParentCount = 0;
            $hasAddressCount = 0;

            foreach ($students as $s) {
                $hasId = !empty($s->nisn) && !empty($s->nis);
                $hasBirth = !empty($s->birth_place) && !empty($s->birth_date);
                $hasParent = !empty($s->parent_name) || !empty($s->guardian_name);
                $hasAddress = !empty($s->address);

                if ($hasId) $hasNisnCount++;
                if ($hasParent) $hasParentCount++;
                if ($hasAddress) $hasAddressCount++;

                if ($hasId && $hasBirth && $hasParent && $hasAddress) {
                    $completeSiswa++;
                }
            }

            $pct = $totalSiswa > 0 ? round(($completeSiswa / $totalSiswa) * 100, 1) : 0;
            $incomplete = max(0, $totalSiswa - $completeSiswa);
            $noNisnCount = max(0, $totalSiswa - $hasNisnCount);
            $noParentCount = max(0, $totalSiswa - $hasParentCount);

            if ($totalSiswa == 0) {
                $rekomendasi = "Belum ada siswa aktif terdaftar di unit ini. Segera verifikasi data siswa.";
                $statusColor = 'red';
            } elseif ($pct < 50) {
                $rekomendasi = "Persentase kelengkapan profil siswa masih di bawah 50% ({$pct}%). Instruksikan wali kelas memonitor pengisian NISN dan data ortu.";
                $statusColor = 'red';
            } elseif ($pct < 100) {
                $rekomendasi = "Sudah {$completeSiswa} dari {$totalSiswa} siswa lengkap ({$pct}%). Tinggal {$incomplete} siswa yang perlu melengkapi data.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Seluruh profil siswa aktif ({$totalSiswa} orang) telah terisi lengkap 100%.";
                $statusColor = 'green';
            }

            $details = [
                "Profil 100% Lengkap: {$completeSiswa} dari {$totalSiswa} Siswa",
                "NISN & NIS Terisi: {$hasNisnCount} Siswa",
                "Data Ortu/Wali: {$hasParentCount} Siswa",
                "Alamat Lengkap: {$hasAddressCount} Siswa",
            ];
            if ($incomplete > 0) {
                $details[] = "Perlu Dilengkapi: {$noNisnCount} Siswa tanpa NISN, {$noParentCount} Siswa tanpa Data Ortu";
            }

            $item3Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$pct}% ({$completeSiswa}/{$totalSiswa} Siswa)",
                'satuan' => 'Persentase (%)',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $pct,
                'details' => $details,
                'action_items' => $incomplete > 0 ? ["{$noNisnCount} Siswa Belum Ada NISN", "{$noParentCount} Siswa Belum Ada Data Ortu"] : [],
            ];
        }
        $items[] = [
            'number' => 3,
            'title' => 'Finalisasi Profile Siswa',
            'description' => 'Persentase kelengkapan NISN, Tempat/Tgl Lahir, Alamat, dan Data Orang Tua/Wali Siswa',
            'schools_data' => $item3Schools,
        ];

        // ════════════════ ITEM 4: PENUGASAN JABATAN ════════════════
        $item4Schools = [];
        foreach ($schools as $school) {
            $posCount = 0;
            if ($currentYear) {
                $posCount = EmployeePosition::where('academic_year_id', $currentYear->id)
                    ->whereHas('employee', function ($q) use ($school) {
                        $q->where('school_id', $school->id);
                    })
                    ->distinct('employee_id')
                    ->count('employee_id');
            }

            if ($posCount == 0) {
                $posCount = EmployeePosition::whereHas('employee', function ($q) use ($school) {
                    $q->where('school_id', $school->id)->where('is_active', true);
                })->distinct('employee_id')->count('employee_id');
            }

            $waliKelasPos = Classroom::where('school_id', $school->id)
                ->whereNotNull('homeroom_teacher_id')
                ->where('is_active', true)
                ->count();

            // Cek posisi penting yang terisi
            $activePositions = EmployeePosition::whereHas('employee', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })->with('position')->get();

            $posNames = [];
            foreach ($activePositions as $ap) {
                if ($ap->position && !in_array($ap->position->position_name, $posNames)) {
                    $posNames[] = $ap->position->position_name;
                }
            }

            if ($posCount == 0) {
                $rekomendasi = "Belum ada SK penugasan struktural untuk TP ini. Segera buat penugasan Kepala Sekolah, Wakasek, dan Wali Kelas.";
                $statusColor = 'red';
            } else {
                $rekomendasi = "Terdapat {$posCount} orang telah diberi penugasan jabatan struktural. Pastikan SK Penugasan resmi telah disahkan.";
                $statusColor = 'green';
            }

            $details = [
                "Total Pegawai Diberi SK: {$posCount} Orang",
                "Wali Kelas Terisi: {$waliKelasPos} Rombel",
            ];
            if (!empty($posNames)) {
                $details[] = "Jabatan Terisi: " . implode(', ', array_slice($posNames, 0, 4)) . (count($posNames) > 4 ? ' + ' . (count($posNames) - 4) . ' lainnya' : '');
            } else {
                $details[] = "Tugas Struktural/Tambahan: Perlu Diterbitkan";
            }

            $item4Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$posCount} Orang",
                'satuan' => 'Orang',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $posCount,
                'details' => $details,
                'action_items' => $posCount == 0 ? ["Terbitkan SK Struktural Kepsek/Wakasek/Wali Kelas"] : [],
            ];
        }
        $items[] = [
            'number' => 4,
            'title' => 'Penugasan Jabatan',
            'description' => 'Jumlah penugasan struktural (Kepsek, Wakasek, Wali Kelas, Kepala Lab/Perpus) yang telah diterbitkan',
            'schools_data' => $item4Schools,
        ];

        // ════════════════ ITEM 5: PENUGASAN MENGAJAR ════════════════
        $item5Schools = [];
        foreach ($schools as $school) {
            $totalJam = 0;
            $taCount = 0;
            $guruMengajarCount = 0;

            if ($currentYear) {
                $taQuery = TeachingAssignment::where('academic_year_id', $currentYear->id)
                    ->whereHas('classroom', function ($q) use ($school) {
                        $q->where('school_id', $school->id);
                    });
                $totalJam = (int) $taQuery->sum('hours_per_week');
                $taCount = $taQuery->count();
                $guruMengajarCount = (clone $taQuery)->distinct('teacher_id')->count('teacher_id');

                if ($taCount == 0) {
                    $taQuery = TeachingAssignment::where('academic_year_id', $currentYear->id)
                        ->whereHas('teacher', function ($q) use ($school) {
                            $q->where('school_id', $school->id);
                        });
                    $totalJam = (int) $taQuery->sum('hours_per_week');
                    $taCount = $taQuery->count();
                    $guruMengajarCount = (clone $taQuery)->distinct('teacher_id')->count('teacher_id');
                }
            }

            $totalGuruUnit = Teacher::where('school_id', $school->id)->count();
            if ($totalGuruUnit == 0) {
                $totalGuruUnit = Employee::where('school_id', $school->id)->where('employee_type', 'guru')->count();
            }
            $guruTanpaJam = max(0, $totalGuruUnit - $guruMengajarCount);
            $avgJam = $guruMengajarCount > 0 ? round($totalJam / $guruMengajarCount, 1) : 0;

            if ($totalJam == 0) {
                $rekomendasi = "Belum ada distribusi jam mengajar mata pelajaran untuk TP ini. Segera susun pembagian jam mengajar guru per kelas.";
                $statusColor = 'red';
            } else {
                $rekomendasi = "Total jam mengajar terdistribusi: {$totalJam} Jam dari {$taCount} penugasan. Periksa keseimbangan jam kerja guru.";
                $statusColor = 'green';
            }

            $details = [
                "Total Beban Jam: {$totalJam} Jam/Minggu",
                "Jumlah Penugasan SK: {$taCount} Item",
                "Guru Mengajar: {$guruMengajarCount} dari {$totalGuruUnit} Guru",
                "Rata-rata Beban: {$avgJam} Jam/Guru",
            ];
            if ($guruTanpaJam > 0) {
                $details[] = "Guru Tanpa Jam Mengajar: {$guruTanpaJam} Orang";
            }

            $item5Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$totalJam} Jam ({$taCount} Penugasan)",
                'satuan' => 'Jam',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $totalJam,
                'ta_count' => $taCount,
                'details' => $details,
                'action_items' => $guruTanpaJam > 0 ? ["{$guruTanpaJam} Guru Belum Memiliki Penugasan Mengajar"] : [],
            ];
        }
        $items[] = [
            'number' => 5,
            'title' => 'Penugasan Mengajar',
            'description' => 'Jumlah jam mengajar dan distribusi beban mata pelajaran yang sudah diterbitkan per guru',
            'schools_data' => $item5Schools,
        ];

        // ════════════════ ITEM 6: JADWAL PELAJARAN ════════════════
        $item6Schools = [];
        foreach ($schools as $index => $school) {
            $totalTa = $item5Schools[$index]['ta_count'] ?? 0;
            $plottedCount = 0;

            if ($currentYear) {
                $plottedCount = Schedule::where('school_id', $school->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->whereNotNull('teaching_assignment_id')
                    ->distinct('teaching_assignment_id')
                    ->count('teaching_assignment_id');

                if ($plottedCount == 0) {
                    $plottedCount = Schedule::where('school_id', $school->id)
                        ->where('academic_year_id', $currentYear->id)
                        ->count();
                }
            }

            $totalScheduleRows = Schedule::where('school_id', $school->id)
                ->where('academic_year_id', $currentYear->id ?? 0)
                ->count();

            $pct = $totalTa > 0 ? round(($plottedCount / $totalTa) * 100, 1) : ($plottedCount > 0 ? 100 : 0);
            $sisaTa = max(0, $totalTa - $plottedCount);

            if ($totalTa == 0) {
                $rekomendasi = "Buat penugasan mengajar terlebih dahulu agar dapat diplot ke dalam jadwal pelajaran mingguan.";
                $statusColor = 'red';
            } elseif ($pct == 0) {
                $rekomendasi = "Jadwal pelajaran belum disusun (0%). Segera lakukan plotting hari, jam pelajaran, dan ruangan.";
                $statusColor = 'red';
            } elseif ($pct < 100) {
                $rekomendasi = "Jadwal terplot {$pct}%. Lanjutkan plotting untuk sisa {$sisaTa} penugasan agar jadwal mingguan siap digunakan.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Seluruh penugasan mengajar ({$totalTa} item) telah 100% terplot ke dalam jadwal pelajaran mingguan.";
                $statusColor = 'green';
            }

            $details = [
                "Penugasan Terplot: {$plottedCount} dari {$totalTa}",
                "Total Sesi Terjadwal: {$totalScheduleRows} Slot Jam",
            ];
            if ($sisaTa > 0) {
                $details[] = "Penugasan Belum Terplot: {$sisaTa} Item Mapel";
            }

            $item6Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$pct}% ({$plottedCount}/{$totalTa} Terplot)",
                'satuan' => 'Persentase (%)',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $pct,
                'details' => $details,
                'action_items' => $sisaTa > 0 ? ["Plot {$sisaTa} Penugasan Mengajar ke Slot Hari/Jam"] : [],
            ];
        }
        $items[] = [
            'number' => 6,
            'title' => 'Jadwal Pelajaran',
            'description' => 'Persentase penugasan mengajar yang sudah diplot ke dalam slot hari dan jam pelajaran mingguan',
            'schools_data' => $item6Schools,
        ];

        // ════════════════ ITEM 7: KALENDER PENDIDIKAN ════════════════
        $item7Schools = [];
        foreach ($schools as $school) {
            $kaldikQuery = EducationalCalendar::query();
            if ($currentYear) {
                $kaldikQuery->where('academic_year_id', $currentYear->id)
                    ->where(function ($q) use ($school) {
                        $q->where('school_id', $school->id)->orWhereNull('school_id');
                    });
            }
            $kaldikCount = $kaldikQuery->count();
            $recentAgendas = (clone $kaldikQuery)->orderBy('start_date', 'asc')->take(3)->get();
            $agendaNames = $recentAgendas->pluck('title')->toArray();

            if ($kaldikCount == 0) {
                $rekomendasi = "Belum ada agenda kegiatan atau hari libur pada Kalender Pendidikan TP ini. Segera input agenda akademik tahunan.";
                $statusColor = 'red';
            } else {
                $rekomendasi = "Kalender pendidikan diisi {$kaldikCount} agenda kegiatan. Pastikan jadwal ujian PTS/PAS dan libur semester tercakup.";
                $statusColor = 'green';
            }

            $details = [
                "Total Agenda Akademik: {$kaldikCount} Kegiatan",
            ];
            if (!empty($agendaNames)) {
                $details[] = "Agenda Terdekat: " . implode(', ', $agendaNames);
            }

            $item7Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$kaldikCount} Agenda",
                'satuan' => 'Agenda',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $kaldikCount,
                'details' => $details,
                'action_items' => $kaldikCount == 0 ? ["Input Agenda Akademik & Libur di Kalender Pendidikan"] : [],
            ];
        }
        $items[] = [
            'number' => 7,
            'title' => 'Kalender Pendidikan',
            'description' => 'Jumlah data agenda kegiatan akademik, ujian sekolah, dan libur semester yang telah disusun',
            'schools_data' => $item7Schools,
        ];

        // ════════════════ ITEM 8: TAGIHAN SISWA ════════════════
        $item8Schools = [];
        foreach ($schools as $school) {
            $feeTypes = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();
            $feeCount = $feeTypes->count();
            $feeValuedCount = $feeTypes->where('amount', '>', 0)->count();
            $sumAmount = $feeTypes->sum('amount');

            $zeroValuedNames = $feeTypes->where('amount', '<=', 0)->pluck('type_name')->toArray();

            // Cek penerbitan StudentBill pada Tahun Pelajaran yang sedang dipilih ($currentYear)
            $billsQuery = StudentBill::whereHas('student', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            });
            if ($currentYear) {
                $billsQuery->where('academic_year_id', $currentYear->id);
            }
            $billsCount = $billsQuery->count();
            $billsTotal = $billsQuery->sum('amount');

            $academicYearLabel = $currentYear ? "TP. {$currentYear->year}" : "TP Aktif";

            if ($feeCount == 0) {
                $rekomendasi = "Belum ada master jenis tagihan siswa (SPP, DSP, Ujian) pada unit ini. Segera buat jenis tagihan dan tentukan nominal pembayarannya.";
                $statusColor = 'red';
            } elseif ($feeValuedCount < $feeCount) {
                $sisa = $feeCount - $feeValuedCount;
                $rekomendasi = "Terdapat {$feeCount} jenis tagihan, namun {$sisa} jenis belum diberi nominal (> Rp 0). Lengkapi besaran nominalnya.";
                $statusColor = 'amber';
            } elseif ($billsCount == 0) {
                $rekomendasi = "Terdapat {$feeCount} jenis tagihan siswa yang bernominal, tetapi BELUM TERBIT tagihan ke siswa untuk {$academicYearLabel}. Terbitkan tagihan siswa.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Terdapat {$feeCount} jenis tagihan siswa siap pakai dan {$billsCount} tagihan siswa telah diterbitkan pada {$academicYearLabel}.";
                $statusColor = 'green';
            }

            // Buat rincian jenis tagihan beserta nominalnya
            $feeListText = [];
            foreach ($feeTypes as $ft) {
                $nom = $ft->amount > 0 ? "Rp " . number_format($ft->amount, 0, ',', '.') . ($ft->is_recurring ? '/bln' : '') : "Belum Bernominal";
                $feeListText[] = "{$ft->type_name} ({$nom})";
            }
            $daftarTagihanStr = !empty($feeListText) ? implode(', ', $feeListText) : 'Belum Ada';

            $details = [
                "Acuan Tahun Pelajaran: {$academicYearLabel}",
                "Master Tagihan ({$feeCount} Jenis): {$daftarTagihanStr}",
                "Total Setup Nominal: Rp " . number_format($sumAmount, 0, ',', '.'),
            ];

            if ($billsCount > 0) {
                $details[] = "Tagihan Terbit {$academicYearLabel}: {$billsCount} Tagihan Siswa (Total Rp " . number_format($billsTotal, 0, ',', '.') . ")";
            } else {
                $details[] = "Tagihan Terbit {$academicYearLabel}: Belum Diterbitkan ke Siswa";
            }

            if (!empty($zeroValuedNames)) {
                $details[] = "Belum Ber-Nominal: " . implode(', ', array_slice($zeroValuedNames, 0, 3));
            }

            $actionItems = [];
            if ($feeCount == 0) {
                $actionItems[] = "Buat Master Jenis Tagihan Pembayaran";
            } elseif (!empty($zeroValuedNames)) {
                $actionItems[] = "Input Nominal untuk: " . implode(', ', $zeroValuedNames);
            } elseif ($billsCount == 0) {
                $actionItems[] = "Terbitkan Tagihan Siswa untuk {$academicYearLabel}";
            }

            $item8Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$feeCount} Jenis ({$feeValuedCount} Bernilai)",
                'satuan' => 'Jenis',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $feeCount,
                'details' => $details,
                'action_items' => $actionItems,
            ];
        }
        $items[] = [
            'number' => 8,
            'title' => 'Tagihan Siswa & Keuangan',
            'description' => 'Jenis tagihan pembayaran siswa (SPP, DSP, Ujian) yang sudah dibuat dan diberi nilai nominal',
            'schools_data' => $item8Schools,
        ];

        // ════════════════ ITEM 9: ROMBONGAN BELAJAR (ROMBEL) ════════════════
        $item9Schools = [];
        foreach ($schools as $school) {
            $rombelQuery = Classroom::where('school_id', $school->id)->where('is_active', true);
            if ($currentYear) {
                $rombelQuery->where(function($q) use ($currentYear) {
                    $q->where('academic_year_id', $currentYear->id)->orWhereNull('academic_year_id');
                });
            }
            $allRombels = $rombelQuery->get();
            $totalRombel = $allRombels->count();

            $noWaliRombels = $allRombels->whereNull('homeroom_teacher_id');
            $noWaliNames = $noWaliRombels->pluck('name')->toArray();
            $rombelWithWali = $totalRombel - count($noWaliNames);
            $rombelNoWali = count($noWaliNames);

            if ($totalRombel == 0) {
                $rekomendasi = "Belum ada Rombongan Belajar (Rombel) yang dibuat untuk TP ini. Segera susun daftar kelas.";
                $statusColor = 'red';
            } elseif ($rombelNoWali > 0) {
                $rekomendasi = "Terdapat {$totalRombel} Rombel, namun {$rombelNoWali} Rombel belum memiliki Wali Kelas. Tentukan Wali Kelas.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Seluruh Rombel ({$totalRombel} Kelas) telah dibuat dan 100% memiliki Wali Kelas.";
                $statusColor = 'green';
            }

            $details = [
                "Total Kelas / Rombel: {$totalRombel} Kelas",
                "Rombel Ada Wali Kelas: {$rombelWithWali} Kelas",
                "Rombel Tanpa Wali Kelas: {$rombelNoWali} Kelas",
            ];
            if (!empty($noWaliNames)) {
                $showCount = 3;
                $sampleNoWali = array_slice($noWaliNames, 0, $showCount);
                $more = count($noWaliNames) > $showCount ? ' + ' . (count($noWaliNames) - $showCount) . ' lainnya' : '';
                $details[] = "Tanpa Wali Kelas: " . implode(', ', $sampleNoWali) . $more;
            }

            $item9Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$totalRombel} Rombel ({$rombelWithWali} Ber-Wali)",
                'satuan' => 'Kelas',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $totalRombel,
                'details' => $details,
                'action_items' => $noWaliNames,
            ];
        }
        $items[] = [
            'number' => 9,
            'title' => 'Rombongan Belajar & Wali Kelas',
            'description' => 'Jumlah daftar kelas/rombel yang aktif dan kelengkapan penugasan Wali Kelas per rombel',
            'schools_data' => $item9Schools,
        ];

        // ════════════════ ITEM 10: MATA PELAJARAN & KURIKULUM ════════════════
        $item10Schools = [];
        foreach ($schools as $school) {
            $allSubjects = Subject::where('school_id', $school->id)->where('is_active', true)->get();
            $totalSubject = $allSubjects->count();
            $noHoursSubjects = $allSubjects->where('hours_per_week', '<=', 0);
            $noHoursNames = $noHoursSubjects->pluck('name')->toArray();
            $subjectWithHours = $totalSubject - count($noHoursNames);

            if ($totalSubject == 0) {
                $rekomendasi = "Belum ada struktur Mata Pelajaran yang diinput untuk kurikulum sekolah ini. Segera tambahkan master mapel.";
                $statusColor = 'red';
            } elseif (count($noHoursNames) > 0) {
                $sisa = count($noHoursNames);
                $rekomendasi = "Terdapat {$totalSubject} mapel terdaftar, namun {$sisa} mapel belum diatur beban alokasi jam per minggu.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Seluruh Mata Pelajaran ({$totalSubject} Mapel) telah lengkap dan diset alokasi jam mengajar.";
                $statusColor = 'green';
            }

            $details = [
                "Total Mata Pelajaran: {$totalSubject} Mapel",
                "Mapel Ber-Alokasi Jam: {$subjectWithHours} Mapel",
            ];
            if (!empty($noHoursNames)) {
                $showCount = 3;
                $sampleNoHours = array_slice($noHoursNames, 0, $showCount);
                $more = count($noHoursNames) > $showCount ? ' + ' . (count($noHoursNames) - $showCount) . ' lainnya' : '';
                $details[] = "Belum Diset Jam: " . implode(', ', $sampleNoHours) . $more;
            }

            $item10Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$totalSubject} Mapel ({$subjectWithHours} Ber-Jam)",
                'satuan' => 'Mapel',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $totalSubject,
                'details' => $details,
                'action_items' => $noHoursNames,
            ];
        }
        $items[] = [
            'number' => 10,
            'title' => 'Mata Pelajaran & Struktur Kurikulum',
            'description' => 'Jumlah mata pelajaran aktif yang terdaftar dalam kurikulum beserta alokasi jam per minggu',
            'schools_data' => $item10Schools,
        ];

        // ════════════════ ITEM 11: AKUN USER & HAK AKSES PORTAL ════════════════
        $item11Schools = [];
        foreach ($schools as $school) {
            $userGuru = User::where('school_id', $school->id)->whereIn('role', ['guru', 'teacher'])->count();
            $userSiswa = User::where('school_id', $school->id)->whereIn('role', ['siswa', 'student'])->count();
            $totalUserSchool = User::where('school_id', $school->id)->count();

            $totalGuruUnit = Teacher::where('school_id', $school->id)->count();
            if ($totalGuruUnit == 0) {
                $totalGuruUnit = Employee::where('school_id', $school->id)->where('employee_type', 'guru')->count();
            }
            $totalSiswaUnit = Student::where('school_id', $school->id)->where('status', 'aktif')->count();

            $pctGuru = $totalGuruUnit > 0 ? round(($userGuru / $totalGuruUnit) * 100, 1) : 0;
            $pctSiswa = $totalSiswaUnit > 0 ? round(($userSiswa / $totalSiswaUnit) * 100, 1) : 0;

            $noAccountGuru = max(0, $totalGuruUnit - $userGuru);
            $noAccountSiswa = max(0, $totalSiswaUnit - $userSiswa);

            if ($totalUserSchool == 0) {
                $rekomendasi = "Belum ada akun login portal yang dibuat untuk unit sekolah ini. Generasi akun login siswa & guru.";
                $statusColor = 'red';
            } elseif ($pctGuru < 80 || $pctSiswa < 50) {
                $rekomendasi = "Cakupan akun portal: Guru {$pctGuru}%, Siswa {$pctSiswa}%. Generasi otomatis akun login untuk sisa pengguna.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Akun portal login siswa & guru telah aktif dan dapat digunakan untuk akses mobile/web.";
                $statusColor = 'green';
            }

            $details = [
                "Akun Guru Aktif: {$userGuru} / {$totalGuruUnit} ({$pctGuru}%)",
                "Akun Siswa Aktif: {$userSiswa} / {$totalSiswaUnit} ({$pctSiswa}%)",
                "Total Akun Portal Unit: {$totalUserSchool} User",
            ];
            if ($noAccountGuru > 0 || $noAccountSiswa > 0) {
                $details[] = "Belum Ada Akun: {$noAccountGuru} Guru, {$noAccountSiswa} Siswa";
            }

            $item11Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$totalUserSchool} Akun (Guru {$pctGuru}%, Siswa {$pctSiswa}%)",
                'satuan' => 'Akun',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $totalUserSchool,
                'details' => $details,
                'action_items' => array_filter([
                    $noAccountGuru > 0 ? "{$noAccountGuru} Guru Belum Punya Akun Portal" : null,
                    $noAccountSiswa > 0 ? "{$noAccountSiswa} Siswa Belum Punya Akun Portal" : null,
                ]),
            ];
        }
        $items[] = [
            'number' => 11,
            'title' => 'Akun Portal & Hak Akses User',
            'description' => 'Jumlah pendaftaran akun login portal dan persentase aktif pengguna (Siswa & Guru)',
            'schools_data' => $item11Schools,
        ];

        // ════════════════ ITEM 12: KESIAPAN LMS & BANK SOAL (CBT) ════════════════
        $item12Schools = [];
        foreach ($schools as $school) {
            $lmsCourses = LmsCourse::where('school_id', $school->id)->get();
            $lmsCount = $lmsCourses->count();

            $cbtQuery = CbtExam::where('school_id', $school->id);
            if ($currentYear) {
                $cbtQuery->where(function($q) use ($currentYear) {
                    $q->where('academic_year_id', $currentYear->id)->orWhereNull('academic_year_id');
                });
            }
            $cbtExams = $cbtQuery->get();
            $cbtCount = $cbtExams->count();

            if ($lmsCount == 0 && $cbtCount == 0) {
                $rekomendasi = "Belum ada Modul Pembelajaran LMS maupun Bank Soal Ujian CBT yang dibuat oleh guru di unit ini.";
                $statusColor = 'red';
            } elseif ($lmsCount == 0 || $cbtCount == 0) {
                $rekomendasi = "Terdapat {$lmsCount} Modul LMS dan {$cbtCount} Bank Soal CBT. Dorong guru untuk melengkapi kedua sarana digital.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Sarana digital pembelajaran LMS ({$lmsCount} Modul) & Ujian Online CBT ({$cbtCount} Bank Soal) telah siap.";
                $statusColor = 'green';
            }

            $details = [
                "Modul Pembelajaran LMS: {$lmsCount} Modul",
                "Bank Soal Ujian CBT: {$cbtCount} Bank Soal",
                "Platform Pembelajaran Digital: Siap Digunakan",
            ];

            $item12Schools[] = [
                'school_name' => $school->name,
                'perkembangan' => "{$lmsCount} LMS / {$cbtCount} CBT",
                'satuan' => 'Modul/Soal',
                'rekomendasi' => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value' => $lmsCount + $cbtCount,
                'details' => $details,
                'action_items' => ($lmsCount == 0 || $cbtCount == 0) ? ["Buat Modul LMS & Bank Soal CBT Digital"] : [],
            ];
        }
        $items[] = [
            'number' => 12,
            'title' => 'Kesiapan LMS Digital & Bank Soal CBT',
            'description' => 'Jumlah modul materi pembelajaran online (LMS) dan bank soal ujian online (CBT) yang disiapkan guru',
            'schools_data' => $item12Schools,
        ];

        return [
            'currentYear' => $currentYear,
            'allYears' => $allYears,
            'schools' => $schools,
            'items' => $items,
        ];
    }
}
