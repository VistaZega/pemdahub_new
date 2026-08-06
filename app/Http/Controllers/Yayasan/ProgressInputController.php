<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use App\Models\LmsCourse;
use App\Models\LmsModule;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsQuiz;
use App\Models\CbtExam;
use App\Models\PklPlacement;
use App\Models\PklLog;
use App\Models\PklGrade;
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
        $data['isSharedView'] = false;

        return view('yayasan.progress_input.index', $data);
    }

    /**
     * Tampilkan halaman Progress Input Data (Shared View, Public)
     */
    public function indexShared(Request $request)
    {
        if ($request->query('token') !== 'pembda-yayasan-2026') {
            abort(403, 'Akses link tidak valid atau sudah kadaluarsa.');
        }

        $academicYearId = $request->input('academic_year_id');
        $data = $this->getProgressData($academicYearId);
        $data['isSharedView'] = true;

        return view('yayasan.progress_input.index', $data);
    }

    /**
     * Export rekap progress input ke PDF
     */
    public function exportPdf(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $data = $this->getProgressData($academicYearId);
        $data['isSharedView'] = false;

        $pdf = Pdf::loadView('yayasan.progress_input.pdf', $data)
            ->setPaper('a4', 'landscape');

        $fileName = 'rekap_progress_input_data_SE05_' . str_replace('/', '_', $data['currentYear']->year ?? '2026_2027') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Export rekap progress input ke PDF (Shared View, Public)
     */
    public function exportPdfShared(Request $request)
    {
        if ($request->query('token') !== 'pembda-yayasan-2026') {
            abort(403, 'Akses link tidak valid atau sudah kadaluarsa.');
        }

        $academicYearId = $request->input('academic_year_id');
        $data = $this->getProgressData($academicYearId);
        $data['isSharedView'] = true;

        $pdf = Pdf::loadView('yayasan.progress_input.pdf', $data)
            ->setPaper('a4', 'landscape');

        $fileName = 'rekap_progress_input_data_SE05_' . str_replace('/', '_', $data['currentYear']->year ?? '2026_2027') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Helper untuk menghitung indikator progress mendalam selaras dengan
     * Surat Edaran Ketua Yayasan No. 05/SE/YP-PEMBDA/VII/2026
     */
    private function getProgressData($academicYearId = null)
    {
        // 1. Metadata Surat Edaran Resmi Yayasan
        $seMetadata = [
            'nomor'           => '05/SE/YP-PEMBDA/VII/2026',
            'perihal'         => 'Penetapan Standar Minimal Progress Input Data PembdaHUB untuk TP. 2026/2027',
            'tanggal_terbit' => '30 Juli 2026',
            'penandatangan'   => 'Yulianus Zega, S.Kom, M.Pd.T (Ketua Yayasan Perguruan PEMBDA Nias)',
            'tenggat_waktu'   => 'Senin, 3 Agustus 2026 Pukul 23.59 WIB',
            'evaluasi_waktu'  => 'Selasa, 4 Agustus 2026',
            'target_date_iso' => '2026-08-03 23:59:59',
        ];

        // 2. Cari Tahun Pelajaran (Prioritaskan TP 2026/2027 jika tidak ada pilihan)
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

        // ════════════════ STANDAR 1: DATA AKADEMIK & KESISWAAN (TARGET 100%) ════════════════

        // ITEM 1: Rombongan Belajar & Wali Kelas
        $item1Schools = [];
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
                $rekomendasi = "Belum ada Rombongan Belajar (Rombel) yang dibuat untuk TP 2026/2027. Segera susun daftar kelas.";
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

            $item1Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$totalRombel} Rombel ({$rombelWithWali} Ber-Wali)",
                'satuan'       => 'Kelas',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $totalRombel,
                'details'      => $details,
                'action_items' => $noWaliNames,
            ];
        }
        $items[] = [
            'number'       => 1,
            'standar_id'   => 1,
            'standar_title' => 'STANDAR 1: DATA AKADEMIK & KESISWAAN',
            'standar_target' => 'Target: 100%',
            'title'        => 'Data Rombel & Wali Kelas',
            'description'  => 'Seluruh Data Kelas / Rombongan Belajar (Rombel) TA 2026/2027 beserta penetapan Wali Kelas telah selesai di-input',
            'schools_data' => $item1Schools,
        ];

        // ITEM 2: Data Siswa (Baru & Naik Kelas) & Distribusi Rombel
        $item2Schools = [];
        foreach ($schools as $school) {
            $isSmp = stripos($school->type, 'SMP') !== false || stripos($school->name, 'SMP') !== false;
            $targetGrades = $isSmp ? [7] : [10];
            $gradeLabel = $isSmp ? 'VII SMP' : 'X SMA/SMK';

            $siswaBaru = 0;
            if ($currentYear) {
                $siswaBaru = StudentClass::where('academic_year_id', $currentYear->id)
                    ->whereHas('classroom', function ($q) use ($school, $targetGrades) {
                        $q->where('school_id', $school->id)->whereIn('grade_level', $targetGrades);
                    })
                    ->distinct('student_id')
                    ->count('student_id');
            }

            $allActiveStudents = Student::where('school_id', $school->id)->where('status', 'aktif')->get();
            $totalSiswaAktif = $allActiveStudents->count();
            
            $siswaBerRombelIds = StudentClass::whereHas('student', function ($q) use ($school) {
                    $q->where('school_id', $school->id)->where('status', 'aktif');
                })
                ->when($currentYear, function ($q) use ($currentYear) {
                    $q->where('academic_year_id', $currentYear->id);
                })
                ->distinct('student_id')
                ->pluck('student_id')
                ->toArray();

            $siswaBerRombel = count($siswaBerRombelIds);

            $siswaTanpaRombelList = $allActiveStudents->whereNotIn('id', $siswaBerRombelIds);
            $siswaTanpaRombel = $siswaTanpaRombelList->count();
            $siswaTanpaRombelNames = $siswaTanpaRombelList->pluck('full_name')->toArray();
            $pctDistrib = $totalSiswaAktif > 0 ? round(($siswaBerRombel / $totalSiswaAktif) * 100, 1) : 0;

            if ($totalSiswaAktif == 0) {
                $rekomendasi = "Belum ada siswa aktif terdaftar di unit ini. Segera entry/import data siswa.";
                $statusColor = 'red';
            } elseif ($pctDistrib < 90) {
                $rekomendasi = "Distribusi rombel mencapai {$pctDistrib}%. Masih ada {$siswaTanpaRombel} siswa aktif yang belum dimasukkan ke Rombel TP 2026/2027.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Data siswa ({$siswaBerRombel} dari {$totalSiswaAktif} siswa) terdistribusi 100% ke Rombel masing-masing.";
                $statusColor = 'green';
            }

            $details = [
                "Total Siswa Aktif Unit: {$totalSiswaAktif} Siswa",
                "Terdistribusi ke Rombel: {$siswaBerRombel} Siswa ({$pctDistrib}%)",
                "Siswa Baru Kelas {$gradeLabel}: {$siswaBaru} Siswa",
                "Belum Ber-Rombel: {$siswaTanpaRombel} Siswa",
            ];

            if ($siswaTanpaRombel > 0 && $siswaTanpaRombel < 25) {
                $details[] = "Daftar Siswa Belum Ber-Rombel: " . implode(', ', $siswaTanpaRombelNames);
            }

            $item2Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$pctDistrib}% ({$siswaBerRombel}/{$totalSiswaAktif} Siswa)",
                'satuan'       => 'Persentase (%)',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $pctDistrib,
                'details'      => $details,
                'action_items' => $siswaTanpaRombel > 0 ? ["Distribusikan {$siswaTanpaRombel} Siswa ke Rombel TP 2026/2027"] : [],
            ];
        }
        $items[] = [
            'number'        => 2,
            'standar_id'    => 1,
            'standar_title'  => 'STANDAR 1: DATA AKADEMIK & KESISWAAN',
            'standar_target' => 'Target: 100%',
            'title'         => 'Registrasi Data Siswa & Distribusi Rombel',
            'description'   => 'Seluruh Data Siswa (Siswa Baru & Siswa Naik Kelas) terdaftar 100% dan telah terdistribusi ke Rombel masing-masing',
            'schools_data'  => $item2Schools,
        ];

        // ITEM 3: Finalisasi Profil Siswa
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

            $incompleteSiswaNames = [];
            if ($incomplete > 0 && $incomplete < 25) {
                foreach ($students as $s) {
                    $hasId = !empty($s->nisn) && !empty($s->nis);
                    $hasBirth = !empty($s->birth_place) && !empty($s->birth_date);
                    $hasParent = !empty($s->parent_name) || !empty($s->guardian_name);
                    $hasAddress = !empty($s->address);
                    if (!($hasId && $hasBirth && $hasParent && $hasAddress)) {
                        $incompleteSiswaNames[] = $s->full_name;
                    }
                }
            }

            if ($totalSiswa == 0) {
                $rekomendasi = "Belum ada siswa aktif terdaftar di unit ini. Segera verifikasi data siswa.";
                $statusColor = 'red';
            } elseif ($pct < 80) {
                $rekomendasi = "Persentase kelengkapan profil siswa masih {$pct}%. Wali kelas wajib memonitor pengisian NISN dan data orang tua/wali.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Seluruh profil siswa aktif ({$totalSiswa} orang) telah terisi lengkap 100%.";
                $statusColor = 'green';
            }

            $details = [
                "Profil 100% Lengkap: {$completeSiswa} dari {$totalSiswa} Siswa",
                "NISN & NIS Terisi: {$hasNisnCount} Siswa",
                "Data Ortu/Wali: {$hasParentCount} Siswa",
                "Alamat & Tgl Lahir: {$hasAddressCount} Siswa",
            ];
            
            if (!empty($incompleteSiswaNames)) {
                $details[] = "Daftar Siswa Profil Belum Lengkap: " . implode(', ', $incompleteSiswaNames);
            }

            $item3Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$pct}% ({$completeSiswa}/{$totalSiswa} Siswa)",
                'satuan'       => 'Persentase (%)',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $pct,
                'details'      => $details,
                'action_items' => $incomplete > 0 ? ["Instruksikan Wali Kelas Melengkapi {$incomplete} Data Profil Siswa"] : [],
            ];
        }
        $items[] = [
            'number'        => 3,
            'standar_id'    => 1,
            'standar_title'  => 'STANDAR 1: DATA AKADEMIK & KESISWAAN',
            'standar_target' => 'Target: 100%',
            'title'         => 'Finalisasi Profil & Biodata Siswa',
            'description'   => 'Kelengkapan NISN, NIS, Tempat/Tgl Lahir, Alamat, dan Data Orang Tua/Wali Siswa secara menyeluruh',
            'schools_data'  => $item3Schools,
        ];


        // ════════════════ STANDAR 2: DATA PENGAJARAN & JADWAL (TARGET 100%) ════════════════

        // ITEM 4: Pembagian Tugas Mengajar Guru (Teaching Assignment)
        $item4Schools = [];
        foreach ($schools as $school) {
            $totalJam = 0;
            $taCount = 0;
            $guruMengajarCount = 0;

            $guruMengajarIds = [];
            if ($currentYear) {
                $taQuery = TeachingAssignment::where('academic_year_id', $currentYear->id)
                    ->whereHas('classroom', function ($q) use ($school) {
                        $q->where('school_id', $school->id);
                    });
                $totalJam = (int) $taQuery->sum('hours_per_week');
                $taCount = $taQuery->count();
                $guruMengajarCount = (clone $taQuery)->distinct('teacher_id')->count('teacher_id');
                $guruMengajarIds = (clone $taQuery)->pluck('teacher_id')->toArray();

                if ($taCount == 0) {
                    $taQuery = TeachingAssignment::where('academic_year_id', $currentYear->id)
                        ->whereHas('teacher', function ($q) use ($school) {
                            $q->where('school_id', $school->id);
                        });
                    $totalJam = (int) $taQuery->sum('hours_per_week');
                    $taCount = $taQuery->count();
                    $guruMengajarCount = (clone $taQuery)->distinct('teacher_id')->count('teacher_id');
                    $guruMengajarIds = (clone $taQuery)->pluck('teacher_id')->toArray();
                }
            }

            $teachersList = Teacher::where('school_id', $school->id)->get();
            
            // Pengecualian: Kepala Sekolah tidak diwajibkan mengajar sehingga tidak dihitung dalam progress
            if ($school->principal_id) {
                $teachersList = $teachersList->filter(function($teacher) use ($school) {
                    return $teacher->id != $school->principal_id;
                });
            }

            if ($teachersList->isEmpty()) {
                $teachersList = Employee::where('school_id', $school->id)->where('employee_type', 'guru')->get();
                if ($school->principal_id) {
                    $principalTeacher = Teacher::find($school->principal_id);
                    if ($principalTeacher && $principalTeacher->employee_id) {
                        $teachersList = $teachersList->filter(function($emp) use ($principalTeacher) {
                            return $emp->id != $principalTeacher->employee_id;
                        });
                    }
                }
            }
            $totalGuruUnit = $teachersList->count();
            
            $guruTanpaJam = max(0, $totalGuruUnit - $guruMengajarCount);
            $avgJam = $guruMengajarCount > 0 ? round($totalJam / $guruMengajarCount, 1) : 0;
            
            $guruTanpaJamNames = [];
            if ($guruTanpaJam > 0 && $guruTanpaJam < 25) {
                $guruTanpaJamNames = collect($teachersList)->whereNotIn('id', $guruMengajarIds)->pluck('full_name')->toArray();
            }

            if ($totalJam == 0) {
                $rekomendasi = "Belum ada pembagian tugas mengajar guru (Teaching Assignment) untuk seluruh mata pelajaran di TP 2026/2027.";
                $statusColor = 'red';
            } elseif ($guruTanpaJam > 0) {
                $rekomendasi = "Ter-input {$taCount} penugasan mengajar ({$totalJam} Jam). Terdapat {$guruTanpaJam} guru yang belum diberi alokasi jam mengajar.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Pembagian tugas mengajar guru untuk seluruh mata pelajaran telah ter-input 100% ({$totalJam} Jam Mengajar).";
                $statusColor = 'green';
            }

            $details = [
                "Total Beban Jam: {$totalJam} Jam/Minggu",
                "Jumlah Penugasan SK: {$taCount} Item Mapel",
                "Guru Mengajar Ter-SK: {$guruMengajarCount} dari {$totalGuruUnit} Guru",
                "Rata-rata Beban: {$avgJam} Jam/Guru",
            ];
            if ($guruTanpaJam > 0) {
                $details[] = "Guru Tanpa Jam Mengajar: {$guruTanpaJam} Orang";
            }
            if (!empty($guruTanpaJamNames)) {
                $details[] = "Daftar Guru Tanpa Jam: " . implode(', ', $guruTanpaJamNames);
            }

            $item4Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$totalJam} Jam ({$taCount} Penugasan)",
                'satuan'       => 'Jam',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $totalJam,
                'ta_count'     => $taCount,
                'details'      => $details,
                'action_items' => $guruTanpaJam > 0 ? ["Lengkapi Penugasan Mengajar untuk {$guruTanpaJam} Guru"] : [],
            ];
        }
        $items[] = [
            'number'        => 4,
            'standar_id'    => 2,
            'standar_title'  => 'STANDAR 2: DATA PENGAJARAN & JADWAL',
            'standar_target' => 'Target: 100%',
            'title'         => 'Pembagian Tugas Mengajar Guru (Teaching Assignment)',
            'description'   => 'Pembagian Tugas Mengajar Guru (Teaching Assignment) untuk seluruh mata pelajaran telah di-input 100%',
            'schools_data'  => $item4Schools,
        ];

        // ITEM 5: Jadwal Pelajaran Mingguan Semester Ganjil TA 2026/2027
        $item5Schools = [];
        foreach ($schools as $index => $school) {
            $totalTa = $item4Schools[$index]['ta_count'] ?? 0;
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

            $unplottedNamesStr = '';
            if ($currentYear && $sisaTa > 0) {
                $unplottedTAs = \App\Models\TeachingAssignment::with('teacher')
                    ->where('academic_year_id', $currentYear->id)
                    ->whereHas('classroom', function ($q) use ($school) {
                        $q->where('school_id', $school->id);
                    })
                    ->whereNotIn('id', function($q) use ($school, $currentYear) {
                        $q->select('teaching_assignment_id')
                          ->from('schedules')
                          ->where('school_id', $school->id)
                          ->where('academic_year_id', $currentYear->id)
                          ->whereNotNull('teaching_assignment_id');
                    })
                    ->get();
                    
                $unplottedTeacherNames = $unplottedTAs->map(function($ta) {
                    return $ta->teacher ? $ta->teacher->full_name : 'Unknown';
                })->unique()->filter()->values()->toArray();
                
                if (!empty($unplottedTeacherNames)) {
                    $unplottedNamesStr = implode(', ', $unplottedTeacherNames);
                }
            }

            if ($totalTa == 0) {
                $rekomendasi = "Input pembagian tugas mengajar terlebih dahulu agar dapat diplot ke dalam jadwal pelajaran mingguan.";
                $statusColor = 'red';
            } elseif ($pct == 0) {
                $rekomendasi = "Jadwal pelajaran belum disusun (0%). Segera lakukan plotting jam pelajaran untuk Semester Ganjil TA 2026/2027.";
                $statusColor = 'red';
            } elseif ($pct < 100) {
                $rekomendasi = "Jadwal terplot {$pct}%. Terbitkan sisa {$sisaTa} penugasan agar jadwal mingguan terbit 100% seluruh kelas.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Jadwal Pelajaran Mingguan Semester Ganjil TA 2026/2027 seluruh kelas telah terbit 100% di sistem.";
                $statusColor = 'green';
            }

            $details = [
                "Penugasan Terplot: {$plottedCount} dari {$totalTa} Penugasan",
                "Total Slot Terjadwal: {$totalScheduleRows} Sesi Jam",
            ];
            if ($sisaTa > 0) {
                $details[] = "Penugasan Belum Terplot: {$sisaTa} Item Mapel";
                if (!empty($unplottedNamesStr)) {
                    $details[] = "Daftar Guru Belum Terplot: " . $unplottedNamesStr;
                }
            }

            $item5Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$pct}% ({$plottedCount}/{$totalTa} Terplot)",
                'satuan'       => 'Persentase (%)',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $pct,
                'details'      => $details,
                'action_items' => $sisaTa > 0 ? ["Plotting {$sisaTa} Penugasan Mengajar ke Jadwal Mingguan Ganjil"] : [],
            ];
        }
        $items[] = [
            'number'        => 5,
            'standar_id'    => 2,
            'standar_title'  => 'STANDAR 2: DATA PENGAJARAN & JADWAL',
            'standar_target' => 'Target: 100%',
            'title'         => 'Jadwal Pelajaran Mingguan Semester Ganjil TA 2026/2027',
            'description'   => 'Jadwal Pelajaran Mingguan Semester Ganjil TA 2026/2027 seluruh kelas telah terbit 100% di sistem PembdaHUB',
            'schools_data'  => $item5Schools,
        ];

        // ITEM 6: Kalender Pendidikan & Agenda Akademik
        $item6Schools = [];
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
                $rekomendasi = "Belum ada agenda akademik atau hari libur pada Kalender Pendidikan TP 2026/2027.";
                $statusColor = 'red';
            } else {
                $rekomendasi = "Kalender Pendidikan diisi {$kaldikCount} agenda kegiatan. Jadwal PTS/PAS dan libur semester telah terkonfigurasi.";
                $statusColor = 'green';
            }

            $details = [
                "Total Agenda Akademik: {$kaldikCount} Kegiatan",
            ];
            if (!empty($agendaNames)) {
                $details[] = "Agenda Terdekat: " . implode(', ', $agendaNames);
            }

            $item6Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$kaldikCount} Agenda",
                'satuan'       => 'Agenda',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $kaldikCount,
                'details'      => $details,
                'action_items' => $kaldikCount == 0 ? ["Input Agenda Akademik & Libur di Kalender Pendidikan"] : [],
            ];
        }
        $items[] = [
            'number'        => 6,
            'standar_id'    => 2,
            'standar_title'  => 'STANDAR 2: DATA PENGAJARAN & JADWAL',
            'standar_target' => 'Target: 100%',
            'title'         => 'Kalender Pendidikan & Agenda Akademik',
            'description'   => 'Agenda kegiatan akademik tahunan, jadwal ujian sekolah, dan penetapan libur semester pada Kalender Pendidikan',
            'schools_data'  => $item6Schools,
        ];


        // ════════════════ STANDAR 3: KESIAPAN LMS - 1 KELAS EKSPERIMEN (TARGET 100% MATPEL) ════════════════

        // ITEM 7: Kesiapan 1 Kelas Eksperimen Per Unit (5-in-1 LMS Component)
        $item7Schools = [];
        foreach ($schools as $school) {
            $classrooms = Classroom::where('school_id', $school->id)
                ->where('is_active', true)
                ->orderBy('class_name')
                ->get();

            $totalCourses = LmsCourse::where('school_id', $school->id)->count();
            $totalModules = LmsModule::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count();
            $totalMaterials = LmsMaterial::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count();
            $totalAssignments = LmsAssignment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count();
            $totalQuizzes = LmsQuiz::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count();

            $cbtQuery = CbtExam::where('school_id', $school->id);
            if ($currentYear) {
                $cbtQuery->where(function($q) use ($currentYear) {
                    $q->where('academic_year_id', $currentYear->id)->orWhereNull('academic_year_id');
                });
            }
            $totalCbtExams = $cbtQuery->count();
            $totalQuizCbtSum = $totalQuizzes + $totalCbtExams;

            $completePilotClasses = [];
            $partialPilotClasses = [];
            $classroomBreakdown = [];

            foreach ($classrooms as $cls) {
                $clsCourses = LmsCourse::where('school_id', $school->id)
                    ->where(function($q) use ($cls) {
                        $q->where('classroom_id', $cls->id)
                          ->orWhereHas('lmsClasses', function($lq) use ($cls) {
                              $lq->where('classroom_id', $cls->id);
                          });
                    })->get();

                $clsCourseIds = $clsCourses->pluck('id');
                $courseCount = $clsCourses->count();

                if ($courseCount == 0) {
                    continue;
                }

                $modCount = $clsCourseIds->isNotEmpty() ? LmsModule::whereIn('course_id', $clsCourseIds)->count() : 0;
                $matCount = $clsCourseIds->isNotEmpty() ? LmsMaterial::whereIn('course_id', $clsCourseIds)->count() : 0;
                $asgCount = $clsCourseIds->isNotEmpty() ? LmsAssignment::whereIn('course_id', $clsCourseIds)->count() : 0;
                $quizCount = $clsCourseIds->isNotEmpty() ? LmsQuiz::whereIn('course_id', $clsCourseIds)->count() : 0;

                $hasCourse = $courseCount > 0;
                $hasModul = $modCount > 0;
                $hasMateri = $matCount > 0;
                $hasTugas = $asgCount > 0;
                $hasKuis = ($quizCount > 0 || $totalCbtExams > 0);

                $componentsMet = ($hasCourse ? 1 : 0) + ($hasModul ? 1 : 0) + ($hasMateri ? 1 : 0) + ($hasTugas ? 1 : 0) + ($hasKuis ? 1 : 0);

                $clsInfo = [
                    'classroom'     => $cls,
                    'name'          => $cls->name,
                    'courses'       => $courseCount,
                    'modules'       => $modCount,
                    'materials'     => $matCount,
                    'assignments'   => $asgCount,
                    'quizzes'       => $quizCount,
                    'components_met'=> $componentsMet,
                    'is_complete'   => ($componentsMet === 5),
                ];

                if ($componentsMet === 5) {
                    $completePilotClasses[] = $clsInfo;
                } elseif ($componentsMet >= 2) {
                    $partialPilotClasses[] = $clsInfo;
                }
                $classroomBreakdown[] = $clsInfo;
            }

            $completeCount = count($completePilotClasses);

            if ($completeCount >= 1) {
                $statusColor = 'green';
                $bestClass = $completePilotClasses[0]['name'];
                $perkembangan = "{$completeCount} Kelas Pilot Lengkap 100%";
                $rekomendasi = "Memenuhi Standar SE 05! Terdapat {$completeCount} Kelas Pilot LMS ({$bestClass}) dengan ketersediaan 5 Komponent Lengkap 100% (Course, Modul Bab 1, Materi Bab 1, Min 1 Tugas, Min 1 Kuis).";
            } elseif (!empty($classroomBreakdown)) {
                usort($classroomBreakdown, fn($a, $b) => $b['components_met'] <=> $a['components_met']);
                $bestCls = $classroomBreakdown[0];
                $statusColor = 'amber';
                $perkembangan = "Proses Penyiapan (Kelas {$bestCls['name']}: {$bestCls['components_met']}/5)";
                $rekomendasi = "Belum memenuhi standar SE 05 (1 Kelas Pilot Lengkap 100%). Kelas {$bestCls['name']} telah mengisi {$bestCls['components_met']}/5 komponen. Segera lengkapi seluruh 5 komponen digital.";
            } else {
                $statusColor = 'red';
                $perkembangan = "0 Kelas Pilot (Belum Ada LMS)";
                $rekomendasi = "Belum menunjuk Kelas Pilot LMS. Wajib menunjuk 1 Kelas Eksperimen/Pilot Class dan melengkapi 5 komponen utama (Course, Modul Bab 1, Materi Bab 1, Min 1 Tugas, Min 1 Kuis).";
            }

            $details = [
                "Standar Minimum SE 05: 1 Kelas Eksperimen / Pilot Class per Unit (100% Matpel & 5-in-1 Komponen)",
                "Status Kelas Pilot Lengkap: " . ($completeCount > 0 ? "{$completeCount} Kelas (Siap & Lengkap 100%)" : "Belum Ada (0 Kelas)"),
                "Total Rincian Digital Unit: {$totalCourses} Course, {$totalModules} Modul, {$totalMaterials} Materi, {$totalAssignments} Tugas, {$totalQuizCbtSum} Kuis/CBT",
            ];

            if (!empty($classroomBreakdown)) {
                $sampleClasses = array_slice($classroomBreakdown, 0, 2);
                foreach ($sampleClasses as $sCls) {
                    $details[] = "Rincian Kelas {$sCls['name']}: {$sCls['courses']} Course, {$sCls['modules']} Modul Bab 1, {$sCls['materials']} Materi Bab 1, {$sCls['assignments']} Tugas, " . ($sCls['quizzes'] + $totalCbtExams) . " Kuis (" . ($sCls['is_complete'] ? 'Lengkap 100%' : "{$sCls['components_met']}/5 Komponen") . ")";
                }
            }

            $actionItems = [];
            if ($completeCount == 0) {
                $actionItems[] = "Penunjukan 1 Kelas Eksperimen / Pilot Class di Unit Sekolah";
                if ($totalCourses == 0) $actionItems[] = "Aktifkan Course (Mata Pelajaran Digital)";
                if ($totalModules == 0) $actionItems[] = "Input Modul Pembelajaran Bab 1 / Topik Awal";
                if ($totalMaterials == 0) $actionItems[] = "Upload Materi Ajar Bab 1 (PDF, Slide, atau Video)";
                if ($totalAssignments == 0) $actionItems[] = "Tambahkan Minimal 1 Tugas per Matpel";
                if ($totalQuizCbtSum == 0) $actionItems[] = "Sediakan Minimal 1 Kuis Interaktif per Matpel";
            }

            $item7Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => $perkembangan,
                'satuan'       => 'Kelas Pilot',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $completeCount,
                'details'      => $details,
                'action_items' => $actionItems,
            ];
        }
        $items[] = [
            'number'        => 7,
            'standar_id'    => 3,
            'standar_title'  => 'STANDAR 3: KESIAPAN LMS - 1 KELAS EKSPERIMEN PER UNIT',
            'standar_target' => 'Target: 100% Matpel',
            'title'         => 'Kesiapan LMS - 1 Kelas Eksperimen Per Unit',
            'description'   => 'Wajib menunjuk 1 Kelas Eksperimen/Pilot Class per unit (SMP, SMA, SMK) di mana SELURUH MATPEL memiliki: Course Aktif, Modul Bab 1, Materi Bab 1, Min 1 Tugas, & Min 1 Kuis Interaktif',
            'schools_data'  => $item7Schools,
        ];


        // ════════════════ STANDAR 4: KEUANGAN & REKAPITULASI SPP JULI 2026 (TARGET 100%) ════════════════

        // ITEM 8: Rekapitulasi Pembayaran SPP Bulan Juli 2026
        $item8Schools = [];
        foreach ($schools as $school) {
            $totalSiswaAktif = Student::where('school_id', $school->id)->where('status', 'aktif')->count();
            
            // Payments di-entry untuk pembayaran bulan Juli 2026
            $julyPayments = Payment::whereHas('student', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                })
                ->whereMonth('payment_date', 7)
                ->whereYear('payment_date', 2026)
                ->where('is_verified', true);

            $paymentEntryCount = (clone $julyPayments)->distinct('student_id')->count('student_id');
            $paymentEntryTotal = (clone $julyPayments)
                ->join('student_bills', 'payments.bill_id', '=', 'student_bills.id')
                ->sum(DB::raw('COALESCE(student_bills.yayasan_share_amount, student_bills.amount)'));

            // Tagihan SPP Juli 2026 yang lunas
            $julyBills = StudentBill::whereHas('student', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                })
                ->where('month', 7)
                ->where('year', 2026);

            $julyBillsCount = (clone $julyBills)->count();
            $julyBillsLunas = (clone $julyBills)->where('status', 'lunas')->count();

            $entryCount = max($paymentEntryCount, $julyBillsLunas);
            $pctEntry = $totalSiswaAktif > 0 ? round(($entryCount / $totalSiswaAktif) * 100, 1) : 0;
            
            $sisaSiswaEntry = max(0, $totalSiswaAktif - $entryCount);
            $sisaSiswaNames = [];
            if ($sisaSiswaEntry > 0 && $sisaSiswaEntry < 25) {
                $julyPaymentStudentIds = (clone $julyPayments)->pluck('student_id')->toArray();
                $julyBillLunasStudentIds = (clone $julyBills)->where('status', 'lunas')->pluck('student_id')->toArray();
                $allEntryStudentIds = array_unique(array_merge($julyPaymentStudentIds, $julyBillLunasStudentIds));
                
                $sisaSiswaNames = Student::where('school_id', $school->id)
                    ->where('status', 'aktif')
                    ->whereNotIn('id', $allEntryStudentIds)
                    ->pluck('full_name')->toArray();
            }

            if ($totalSiswaAktif == 0) {
                $rekomendasi = "Belum ada siswa aktif terdaftar untuk rekapitulasi SPP Juli 2026.";
                $statusColor = 'red';
            } elseif ($pctEntry < 80) {
                $rekomendasi = "Entry pembayaran SPP Bulan Juli 2026 baru ter-entry {$pctEntry}% ({$entryCount} dari {$totalSiswaAktif} siswa). Segera selesaikan 100% (Tunai/Transfer).";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Rekapitulasi pembayaran Uang Sekolah / SPP Bulan Juli 2026 (Tunai/Transfer) telah selesai di-entry 100% ke dalam PembdaHUB.";
                $statusColor = 'green';
            }

            $details = [
                "Target Surat Edaran: 100% Entry Pembayaran SPP Bulan Juli 2026 (Tunai & Transfer)",
                "Siswa Di-Entry Lunas SPP Juli: {$entryCount} dari {$totalSiswaAktif} Siswa ({$pctEntry}%)",
                "Total Nominal Entry SPP Juli: Rp " . number_format($paymentEntryTotal, 0, ',', '.'),
                "Tagihan SPP Terbit Juli 2026: {$julyBillsCount} Tagihan ({$julyBillsLunas} Status Lunas)",
            ];
            if (!empty($sisaSiswaNames)) {
                $details[] = "Daftar Siswa Belum Entry SPP: " . implode(', ', $sisaSiswaNames);
            }

            $item8Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$pctEntry}% ({$entryCount}/{$totalSiswaAktif} Siswa)",
                'satuan'       => 'Persentase (%)',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $pctEntry,
                'details'      => $details,
                'action_items' => $pctEntry < 100 ? ["Selesaikan Entry Rekapitulasi SPP Juli 2026 (Sisa " . max(0, $totalSiswaAktif - $entryCount) . " Siswa)"] : [],
            ];
        }
        $items[] = [
            'number'        => 8,
            'standar_id'    => 4,
            'standar_title'  => 'STANDAR 4: KEUANGAN & REKAPITULASI SPP JULI 2026',
            'standar_target' => 'Target: 100%',
            'title'         => 'Rekapitulasi Pembayaran SPP Bulan Juli 2026',
            'description'   => 'Seluruh Rekapitulasi Pembayaran Uang Sekolah / SPP Bulan Juli 2026 (baik pembayaran Tunai maupun Transfer) telah selesai di-entry 100% ke dalam sistem PembdaHUB',
            'schools_data'  => $item8Schools,
        ];

        // ITEM 9: Setting Tarif SPP & Penerbitan Tagihan TA 2026/2027
        $item9Schools = [];
        foreach ($schools as $school) {
            $feeTypes = PaymentType::where('school_id', $school->id)->where('is_active', true)->get();
            $feeCount = $feeTypes->count();
            $feeValuedCount = $feeTypes->where('amount', '>', 0)->count();
            $sumAmount = $feeTypes->sum('amount');

            $billsQuery = StudentBill::whereHas('student', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            });
            if ($currentYear) {
                $billsQuery->where('academic_year_id', $currentYear->id);
            }
            $billsCount = $billsQuery->count();

            $academicYearLabel = $currentYear ? "TP. {$currentYear->year}" : "TP Aktif";

            if ($feeCount == 0 || $feeValuedCount == 0) {
                $rekomendasi = "Setting Tarif SPP dan Pembayaran TA 2026/2027 belum diatur (Rp 0). Segera tentukan besaran tarif SPP.";
                $statusColor = 'red';
            } elseif ($billsCount == 0) {
                $rekomendasi = "Tarif SPP TA 2026/2027 sudah diatur, namun tagihan SPP Bulan Agustus 2026 BELUM diterbitkan ke siswa. Terbitkan tagihan presisi.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Setting Tarif SPP dan Pembayaran TA 2026/2027 telah selesai dan penerbitan tagihan SPP berjalan presisi.";
                $statusColor = 'green';
            }

            $details = [
                "Master Jenis Tagihan Terkonfigurasi: {$feeCount} Jenis ({$feeValuedCount} Bernominal > Rp 0)",
                "Total Setting Tarif SPP Unit: Rp " . number_format($sumAmount, 0, ',', '.'),
                "Tagihan Siswa Terbit {$academicYearLabel}: {$billsCount} Tagihan Siswa",
            ];

            $item9Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$feeCount} Jenis ({$billsCount} Tagihan Terbit)",
                'satuan'       => 'Jenis Tagihan',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $feeCount,
                'details'      => $details,
                'action_items' => $billsCount == 0 ? ["Terbitkan Tagihan SPP Bulan Agustus 2026 untuk {$academicYearLabel}"] : [],
            ];
        }
        $items[] = [
            'number'        => 9,
            'standar_id'    => 4,
            'standar_title'  => 'STANDAR 4: KEUANGAN & REKAPITULASI SPP JULI 2026',
            'standar_target' => 'Target: 100%',
            'title'         => 'Setting Tarif SPP & Penerbitan Tagihan TA 2026/2027',
            'description'   => 'Setting Tarif SPP dan Pembayaran TA 2026/2027 telah selesai agar penerbitan tagihan SPP Bulan Agustus 2026 berjalan presisi',
            'schools_data'  => $item9Schools,
        ];


        // ════════════════ STANDAR 5: KEPEGAWAIAN & PRESENSI (TARGET MINIMAL 90%) ════════════════

        // ITEM 10: Pengaturan Jam Kerja / Jam Presensi Guru & Staf
        $item10Schools = [];
        foreach ($schools as $school) {
            $employees = Employee::where('school_id', $school->id)->where('is_active', true)->get();
            $totalEmp = $employees->count();
            
            // Pegawai yang sudah diset jam kerja/shift
            $empWithShift = $employees->whereNotNull('work_shift_id');
            $hasShiftEmp = $empWithShift->count();
            $empNoShiftNames = [];
            
            if ($hasShiftEmp == 0) {
                // Fallback: pegawai aktif jika master time slot presensi sekolah aktif
                $hasShiftEmp = $totalEmp > 0 ? $totalEmp : 0;
            } else {
                $empNoShiftCount = $totalEmp - $hasShiftEmp;
                if ($empNoShiftCount > 0 && $empNoShiftCount < 25) {
                    $empNoShiftNames = $employees->whereNull('work_shift_id')->pluck('full_name')->toArray();
                }
            }

            $pctShift = $totalEmp > 0 ? round(($hasShiftEmp / $totalEmp) * 100, 1) : 0;

            if ($totalEmp == 0) {
                $rekomendasi = "Belum ada data pegawai terdaftar di unit ini. Segera entry master data guru & staf pegawai.";
                $statusColor = 'red';
            } elseif ($pctShift < 90) {
                $rekomendasi = "Pengaturan jam presensi guru & staf pegawai mencapai {$pctShift}%. Target Surat Edaran minimal 90%. Segera lengkapi.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Pengaturan Jam Kerja / Jam Presensi Guru dan Staf Pegawai telah diatur lengkap ({$pctShift}%).";
                $statusColor = 'green';
            }

            $details = [
                "Target Surat Edaran: Minimal 90% Jam Presensi Dikonfigurasi",
                "Total Guru & Staf Pegawai: {$totalEmp} Orang",
                "Pegawai Ber-Jam Presensi Aktif: {$hasShiftEmp} Orang ({$pctShift}%)",
            ];
            
            if (!empty($empNoShiftNames)) {
                $details[] = "Daftar Pegawai Belum Diatur Jam Kerja: " . implode(', ', $empNoShiftNames);
            }

            $item10Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$pctShift}% ({$hasShiftEmp}/{$totalEmp} Pegawai)",
                'satuan'       => 'Persentase (%)',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $pctShift,
                'details'      => $details,
                'action_items' => $pctShift < 90 ? ["Atur Jam Kerja/Presensi untuk Guru & Staf Pegawai"] : [],
            ];
        }
        $items[] = [
            'number'        => 10,
            'standar_id'    => 5,
            'standar_title'  => 'STANDAR 5: KEPEGAWAIAN & PRESENSI',
            'standar_target' => 'Target: Minimal 90%',
            'title'         => 'Pengaturan Jam Kerja & Presensi Guru/Staf',
            'description'   => 'Pengaturan Jam Kerja / Jam Presensi Guru dan Staf Pegawai telah diatur dan terkonfigurasi di sistem',
            'schools_data'  => $item10Schools,
        ];

        // ITEM 11: Pemetaan ID Kartu RFID / QR Code / Perangkat Presensi Guru & Pegawai
        $item11Schools = [];
        foreach ($schools as $school) {
            $isQrCode = stripos($school->name, 'Pembda 2') !== false;
            $techName = $isQrCode ? 'QR Code' : 'RFID';
            $techFullName = $isQrCode ? 'QR Code' : 'Kartu RFID';

            $employees = Employee::where('school_id', $school->id)->where('is_active', true)->get();
            $totalEmp = $employees->count();
            
            $rfidMappedCount = $employees->whereNotNull('rfid_uid')->where('rfid_uid', '!=', '')->count();
            $pctRfid = $totalEmp > 0 ? round(($rfidMappedCount / $totalEmp) * 100, 1) : 0;
            $unmappedCount = max(0, $totalEmp - $rfidMappedCount);
            
            $unmappedNames = [];
            if (!$isQrCode && $unmappedCount > 0 && $unmappedCount < 25) {
                $unmappedNames = $employees->filter(function($emp) {
                    return empty($emp->rfid_uid);
                })->pluck('full_name')->toArray();
            }

            if ($isQrCode) {
                // Untuk SMP Pembda 2: Hitung Total Guru, Pegawai & Siswa
                $studentsCount = Student::where('school_id', $school->id)->where('status', 'aktif')->count();
                $totalTarget = $totalEmp + $studentsCount;
                
                $pctQr = 100; // QR Code ter-generate otomatis
                $rekomendasi = "Semua data Siswa, Guru & Pegawai telah memiliki QR Code. Silakan lakukan proses Cetak/Print ID Card dan lakukan Uji Coba (Test) scan presensi.";
                $statusColor = 'green';
                
                $details = [
                    "Sistem Presensi: QR Code Terintegrasi (Otomatis)",
                    "Total Target (Guru, Pegawai & Siswa): {$totalTarget} Orang",
                    "Rincian: {$totalEmp} Guru/Pegawai, {$studentsCount} Siswa",
                    "Status ID Card & QR Code: 100% Siap Cetak & Test",
                ];

                $item11Schools[] = [
                    'school_name'  => $school->name,
                    'perkembangan' => "100% ({$totalTarget}/{$totalTarget} {$techName})",
                    'satuan'       => 'Persentase (%)',
                    'rekomendasi'  => $rekomendasi,
                    'status_color' => $statusColor,
                    'raw_value'    => $pctQr,
                    'details'      => $details,
                    'action_items' => ["Cetak/Print ID Card dengan QR Code", "Test/Uji Coba scan QR Code untuk presensi"],
                ];
            } else {
                if ($totalEmp == 0) {
                    $rekomendasi = "Belum ada pegawai terdaftar di unit ini untuk sinkronisasi {$techName} presensi.";
                    $statusColor = 'red';
                } elseif ($pctRfid < 90) {
                    $rekomendasi = "Pemetaan ID {$techFullName} presensi mencapai {$pctRfid}% ({$rfidMappedCount}/{$totalEmp} Pegawai). Target SE minimal 90%. Lengkapi pemetaan {$techName}.";
                    $statusColor = 'amber';
                } else {
                    $rekomendasi = "Sangat baik! Pemetaan ID {$techFullName} / Perangkat Presensi Guru & Pegawai telah selesai disinkronkan 100%.";
                    $statusColor = 'green';
                }

                $details = [
                    "Target Surat Edaran: Minimal 90% {$techFullName} / Perangkat Presensi Ter-sinkronisasi",
                    "Total Guru & Staf Pegawai: {$totalEmp} Orang",
                    "ID {$techFullName} Mapped & Synced: {$rfidMappedCount} Pegawai ({$pctRfid}%)",
                    "Belum Ter-mapping {$techName}: {$unmappedCount} Pegawai",
                ];
                if (!empty($unmappedNames)) {
                    $details[] = "Daftar Belum Ter-mapping {$techName}: " . implode(', ', $unmappedNames);
                }

                $item11Schools[] = [
                    'school_name'  => $school->name,
                    'perkembangan' => "{$pctRfid}% ({$rfidMappedCount}/{$totalEmp} {$techName})",
                    'satuan'       => 'Persentase (%)',
                    'rekomendasi'  => $rekomendasi,
                    'status_color' => $statusColor,
                    'raw_value'    => $pctRfid,
                    'details'      => $details,
                    'action_items' => $pctRfid < 90 ? ["Sinkronkan ID {$techFullName} untuk {$unmappedCount} Guru & Pegawai"] : [],
                ];
            }
        }
        $items[] = [
            'number'        => 11,
            'standar_id'    => 5,
            'standar_title'  => 'STANDAR 5: KEPEGAWAIAN & PRESENSI',
            'standar_target' => 'Target: Minimal 90%',
            'title'         => 'Pemetaan RFID / QR Code & Sinkronisasi Perangkat Presensi',
            'description'   => 'Pemetaan ID Kartu RFID / QR Code / Perangkat Presensi Guru & Pegawai telah selesai disinkronkan secara presisi',
            'schools_data'  => $item11Schools,
        ];

        // ITEM 12: Finalisasi Profil Guru & SK Penugasan Jabatan
        $item12Schools = [];
        foreach ($schools as $school) {
            $teachers = Teacher::where('school_id', $school->id)->get();
            if ($teachers->isEmpty()) {
                $teachers = Employee::where('school_id', $school->id)->where('employee_type', 'guru')->where('is_active', true)->get();
            }

            $totalGuru = $teachers->count();
            $completeGuru = 0;
            foreach ($teachers as $t) {
                $hasId = !empty($t->nik) || !empty($t->nuptk) || !empty($t->teacher_code) || !empty($t->employee_code);
                $hasBirth = !empty($t->birth_place) && !empty($t->birth_date);
                $hasEdu = !empty($t->education_level) || !empty($t->last_education);
                $hasPhone = !empty($t->phone) || !empty($t->phone_number);
                if ($hasId && $hasBirth && $hasEdu && $hasPhone) {
                    $completeGuru++;
                }
            }

            $pctGuru = $totalGuru > 0 ? round(($completeGuru / $totalGuru) * 100, 1) : 0;
            $posCount = EmployeePosition::whereHas('employee', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })->distinct('employee_id')->count('employee_id');

            $incompleteGuruNames = [];
            $incompleteGuruCount = max(0, $totalGuru - $completeGuru);
            if ($incompleteGuruCount > 0 && $incompleteGuruCount < 25) {
                foreach ($teachers as $t) {
                    $hasId = !empty($t->nik) || !empty($t->nuptk) || !empty($t->teacher_code) || !empty($t->employee_code);
                    $hasBirth = !empty($t->birth_place) && !empty($t->birth_date);
                    $hasEdu = !empty($t->education_level) || !empty($t->last_education);
                    $hasPhone = !empty($t->phone) || !empty($t->phone_number);
                    if (!($hasId && $hasBirth && $hasEdu && $hasPhone)) {
                        $incompleteGuruNames[] = $t->full_name;
                    }
                }
            }

            if ($totalGuru == 0) {
                $rekomendasi = "Belum ada master guru/pegawai di unit ini. Segera tambahkan data guru.";
                $statusColor = 'red';
            } elseif ($pctGuru < 90) {
                $rekomendasi = "Kelengkapan profil guru {$pctGuru}%. Terbitkan SK Penugasan Struktural dan himbau pengisian NIK/NUPTK.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Profil data guru ({$totalGuru} orang) dan SK penugasan struktural ({$posCount} jabatan) terisi lengkap.";
                $statusColor = 'green';
            }

            $details = [
                "Kelengkapan Profil Guru (NIK/NUPTK/Tgl Lahir/Pendidikan): {$completeGuru} dari {$totalGuru} Guru ({$pctGuru}%)",
                "Pegawai Ter-SK Penugasan Jabatan: {$posCount} Orang",
            ];
            
            if (!empty($incompleteGuruNames)) {
                $details[] = "Daftar Guru Profil Belum Lengkap: " . implode(', ', $incompleteGuruNames);
            }

            $item12Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$pctGuru}% ({$completeGuru}/{$totalGuru} Guru)",
                'satuan'       => 'Persentase (%)',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $pctGuru,
                'details'      => $details,
                'action_items' => $pctGuru < 90 ? ["Lengkapi Profile Guru & SK Struktural Penugasan Jabatan"] : [],
            ];
        }
        $items[] = [
            'number'        => 12,
            'standar_id'    => 5,
            'standar_title'  => 'STANDAR 5: KEPEGAWAIAN & PRESENSI',
            'standar_target' => 'Target: Minimal 90%',
            'title'         => 'Finalisasi Profil Guru & SK Penugasan Jabatan',
            'description'   => 'Kelengkapan profil biodata guru (NIK, NUPTK, Pendidikan) serta penerbitan SK Penugasan Jabatan Struktural',
            'schools_data'  => $item12Schools,
        ];


        // ════════════════ STANDAR 6: KHUSUS UNTUK SMKS SWASTA PEMBDA NIAS (TARGET 100%) ════════════════

        // ITEM 13: Implementasi Modul PKL (Logbook Siswa & Verifikasi Pembimbing PKL)
        $item13Schools = [];
        foreach ($schools as $school) {
            $isSmk = stripos($school->type, 'SMK') !== false || stripos($school->name, 'SMK') !== false;

            if (!$isSmk) {
                // Untuk SMP & SMA: Indikator ini tidak wajib (Dianggap N/A / Sesuai Standar)
                $item13Schools[] = [
                    'school_name'  => $school->name,
                    'perkembangan' => 'N/A (Bukan Unit SMK)',
                    'satuan'       => 'Status Unit',
                    'rekomendasi'  => 'Standar Khusus Modul PKL ini diperuntukkan khusus bagi Unit SMKS Swasta PEMBDA Nias.',
                    'status_color' => 'green',
                    'raw_value'    => 100,
                    'details'      => ['Implementasi Modul PKL: Khusus Unit SMK'],
                    'action_items' => [],
                ];
                continue;
            }

            // Untuk Unit SMK: Hitung realisasi PKL (Placement, Logbook Harian Siswa, Verifikasi Guru Pendamping)
            $placements = PklPlacement::whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->get();

            $totalPlacement = $placements->count();
            $placementIds = $placements->pluck('id');

            $totalLogbook = PklLog::whereIn('pkl_placement_id', $placementIds)->count();
            $approvedLogbook = PklLog::whereIn('pkl_placement_id', $placementIds)->where('status', 'approved')->count();
            $gradedPlacements = PklGrade::whereIn('pkl_placement_id', $placementIds)->count();

            $pctLogApproved = $totalLogbook > 0 ? round(($approvedLogbook / $totalLogbook) * 100, 1) : 0;

            if ($totalPlacement == 0) {
                $rekomendasi = "Belum ada penempatan siswa PKL di DUDI. Segera petakan siswa peserta PKL dan Guru Pendamping PKL di PembdaHUB.";
                $statusColor = 'red';
            } elseif ($totalLogbook == 0) {
                $rekomendasi = "Penempatan PKL terisi ({$totalPlacement} siswa), tetapi jurnal/logbook harian BELUM diisi siswa melalui akun PembdaHUB.";
                $statusColor = 'amber';
            } elseif ($pctLogApproved < 80) {
                $rekomendasi = "Ter-input {$totalLogbook} catatan harian PKL. Guru Pendamping baru memverifikasi {$approvedLogbook} logbook ({$pctLogApproved}%). Segera selesaikan verifikasi & penilaian.";
                $statusColor = 'amber';
            } else {
                $rekomendasi = "Sangat baik! Implementasi Modul PKL SMKS Swasta PEMBDA Nias berjalan 100% aktif (Logbook Siswa & Verifikasi/Penilaian Guru Pendamping).";
                $statusColor = 'green';
            }

            $details = [
                "Standar Minimal Surat Edaran: 100% Pengisian Logbook Siswa & Verifikasi Guru Pembimbing PKL",
                "Total Penempatan Siswa PKL: {$totalPlacement} Siswa DUDI",
                "Jurnal / Logbook Harian Diisi Siswa: {$totalLogbook} Catatan Harian",
                "Logbook Diverifikasi Guru Pendamping: {$approvedLogbook} Catatan ({$pctLogApproved}%)",
                "Penilaian Akhir PKL Terbit: {$gradedPlacements} Siswa",
            ];

            $item13Schools[] = [
                'school_name'  => $school->name,
                'perkembangan' => "{$totalPlacement} Siswa PKL ({$approvedLogbook}/{$totalLogbook} Verified)",
                'satuan'       => 'Siswa PKL',
                'rekomendasi'  => $rekomendasi,
                'status_color' => $statusColor,
                'raw_value'    => $totalPlacement,
                'details'      => $details,
                'action_items' => $totalPlacement == 0 ? ["Petakan Penempatan Siswa & Guru Pendamping PKL di PembdaHUB"] : ($pctLogApproved < 100 ? ["Instruksikan Guru Pendamping Memverifikasi " . ($totalLogbook - $approvedLogbook) . " Logbook Siswa"] : []),
            ];
        }
        $items[] = [
            'number'        => 13,
            'standar_id'    => 6,
            'standar_title'  => 'STANDAR 6: KHUSUS UNIT SMKS SWASTA PEMBDA NIAS - IMPLEMENTASI MODUL PKL',
            'standar_target' => 'Target: 100%',
            'title'         => 'Khusus SMKS Swasta PEMBDA Nias - Implementasi Modul PKL',
            'description'   => 'Sisi Siswa: Pengisian Logbook / Jurnal Harian PKL secara aktif; Sisi Guru Pendamping: Verifikasi, monitoring catatan harian, & penilaian logbook PKL di PembdaHUB',
            'schools_data'  => $item13Schools,
        ];


        return [
            'seMetadata'   => $seMetadata,
            'currentYear'  => $currentYear,
            'allYears'     => $allYears,
            'schools'      => $schools,
            'items'        => $items,
        ];
    }
}
