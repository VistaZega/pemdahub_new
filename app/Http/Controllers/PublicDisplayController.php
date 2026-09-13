<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\EmployeeAttendance;
use App\Models\Student;
use App\Models\Employee;
use Carbon\Carbon;

class PublicDisplayController extends Controller
{
    /**
     * Halaman utama live display umum (Semua Unit: SMP, SMA, SMK)
     * GET /display
     */
    public function index()
    {
        return $this->unitIndex(null);
    }

    /**
     * Halaman live display khusus Unit SMPS Pembda 2 Gunungsitoli (SMP)
     * GET /display1
     */
    public function display1()
    {
        return $this->unitIndex(1);
    }

    /**
     * Halaman live display khusus Unit SMAS Pembda 1 Gunungsitoli (SMA)
     * GET /display2
     */
    public function display2()
    {
        return $this->unitIndex(2);
    }

    /**
     * Halaman live display khusus Unit SMKS Pembda Gunungsitoli (SMK)
     * GET /display3
     */
    public function display3()
    {
        return $this->unitIndex(3);
    }

    /**
     * Handler halaman display per unit (1=SMP, 2=SMA, 3=SMK, null=Semua)
     * GET /display/{unit?}
     */
    public function unitIndex($unit = null)
    {
        if (request('clear_cache') === 'yes') {
            try {
                \Illuminate\Support\Facades\Artisan::call('route:clear');
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                return "✅ Laravel Route, Config, and Application Cache cleared successfully!";
            } catch (\Exception $e) {
                return "❌ Error: " . $e->getMessage();
            }
        }

        $type = null;
        $unitNumber = null;

        if ($unit !== null) {
            $type = match (strtolower((string)$unit)) {
                '1', 'smp' => 'SMP',
                '2', 'sma' => 'SMA',
                '3', 'smk' => 'SMK',
                default => null,
            };

            if ($type) {
                $unitNumber = match ($type) {
                    'SMP' => 1,
                    'SMA' => 2,
                    'SMK' => 3,
                };
            }
        }

        $targetSchool = null;
        if ($type) {
            $targetSchool = \App\Models\School::where('is_active', true)->where('type', $type)->first();
        }

        return view('display.attendance', [
            'targetSchool' => $targetSchool,
            'unitNumber'   => $unitNumber,
            'targetType'   => $type,
        ]);
    }

    /**
     * Endpoint JSON untuk polling data kehadiran hari ini.
     * Mendukung query parameter ?unit=1|2|3|smp|sma|smk
     * GET /display/live-data
     */
    public function liveData(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $today = Carbon::today($tz)->toDateString();
        $now   = Carbon::now($tz);

        $targetUnit = $request->query('unit');
        $filterType = null;
        if ($targetUnit) {
            $filterType = match (strtolower((string)$targetUnit)) {
                '1', 'smp' => 'SMP',
                '2', 'sma' => 'SMA',
                '3', 'smk' => 'SMK',
                default => null,
            };
        }

        // ── SISWA: Ambil absensi hari ini (hanya siswa terdaftar di TP aktif & status hadir/terlambat) ──
        $studentAttendances = Attendance::where('date', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->whereHas('student', function ($q) use ($filterType) {
                $q->whereHas('studentClasses', function ($sc) {
                    $sc->where('status', 'aktif')
                       ->whereHas('academicYear', function ($ay) {
                            $ay->where('is_active', true);
                       });
                });
                if ($filterType) {
                    $q->whereHas('school', function ($sq) use ($filterType) {
                        $sq->where('type', $filterType);
                    });
                }
            })
            ->with([
                'student:id,full_name,school_id,photo',
                'student.school:id,name,type',
                'classroom:id,class_name,school_id'
            ])
            ->orderByDesc('time_in')
            ->get();

        // ── GURU & PEGAWAI: Ambil absensi hari ini (hanya pegawai aktif & status hadir)
        $employeeAttendances = EmployeeAttendance::where('date', $today)
            ->where('status', 'hadir')
            ->whereHas('employee', function ($q) use ($filterType) {
                $q->where('is_active', true);
                if ($filterType) {
                    $q->whereHas('school', function ($sq) use ($filterType) {
                        $sq->where('type', $filterType);
                    });
                }
            })
            ->with([
                'employee:id,full_name,school_id,photo',
                'employee.school:id,name,type'
            ])
            ->orderByDesc('time_in')
            ->get();

        // ── HITUNG REKAP UNIT DAN STATISTIK SECARA DINAMIS ─────────────
        $dayOfWeek = strtolower($now->format('l')); // 'monday', 'tuesday', etc.
        $schoolsQuery = \App\Models\School::where('is_active', true)->schoolsOnly();
        if ($filterType) {
            $schoolsQuery->where('type', $filterType);
        }

        $schools = $schoolsQuery->get()
            ->sortBy(function ($s) {
                $order = ['SMP' => 1, 'SMA' => 2, 'SMK' => 3];
                return $order[strtoupper($s->type)] ?? 99;
            })
            ->values();

        // Ambil ID tahun pelajaran aktif
        $activeAcademicYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
        
        $rekapUnit = [];
        
        $studentTotal = 0;
        $studentHadir = 0;
        $studentTerlambat = 0;
        $studentPulang = 0;
        $studentBelumAbsen = 0;

        $employeeTotal = 0;
        $employeeHadir = 0;
        $employeeBelumAbsen = 0;

        foreach ($schools as $school) {
            $isYayasan = $school->isYayasan();

            // 1. Statistika Siswa untuk Sekolah ini
            $sTotal = 0;
            $sHadir = 0;
            $sTerlambat = 0;
            $sPulang = 0;
            $sBelum = 0;

            if (!$isYayasan && $activeAcademicYearId) {
                // Hitung siswa terdaftar aktif di TP aktif untuk sekolah ini
                $sTotal = Student::where('school_id', $school->id)
                    ->whereHas('studentClasses', function ($sc) use ($activeAcademicYearId) {
                        $sc->where('academic_year_id', $activeAcademicYearId)
                           ->where('status', 'aktif');
                    })
                    ->count();

                $sHadir = $studentAttendances->where('student.school_id', $school->id)
                    ->where('status', 'hadir')
                    ->count();

                $sTerlambat = $studentAttendances->where('student.school_id', $school->id)
                    ->where('status', 'terlambat')
                    ->count();

                $sPulang = $studentAttendances->where('student.school_id', $school->id)
                    ->whereNotNull('time_out')
                    ->where('time_out', '!=', '00:00:00')
                    ->where('time_out', '!=', '00:00')
                    ->count();

                $sBelum = max(0, $sTotal - ($sHadir + $sTerlambat));
            }

            // 2. Statistika Guru & Staf untuk Sekolah ini
            // Guru wajib hadir: guru aktif yang ada jadwal mengajar hari ini.
            $requiredTeacherIds = \App\Models\Schedule::where('school_id', $school->id)
                ->where('day_of_week', $dayOfWeek)
                ->whereHas('teacher', function ($q) {
                    $q->where('is_active', true);
                })
                ->pluck('teacher_id')
                ->unique()
                ->toArray();

            $requiredTeacherEmployeeIds = Employee::where('school_id', $school->id)
                ->where('is_active', true)
                ->where('employee_type', 'guru')
                ->whereHas('teacher', function ($q) use ($requiredTeacherIds) {
                    $q->whereIn('id', $requiredTeacherIds);
                })
                ->pluck('id')
                ->toArray();

            // Staf non-guru wajib hadir: semua staf non-guru aktif di sekolah ini.
            $requiredStaffEmployeeIds = Employee::where('school_id', $school->id)
                ->where('is_active', true)
                ->where('employee_type', '!=', 'guru')
                ->pluck('id')
                ->toArray();

            $expectedEmployeeIds = array_unique(array_merge($requiredTeacherEmployeeIds, $requiredStaffEmployeeIds));

            // Dapatkan ID semua karyawan sekolah ini yang SEBENARNYA hadir hari ini
            $schoolEmpAtts = $employeeAttendances->where('employee.school_id', $school->id)->where('status', 'hadir');
            $actualAttendedEmployeeIds = $schoolEmpAtts->pluck('employee_id')->toArray();

            // Gabungkan expected dengan actual untuk mencegah persentase > 100% jika ada karyawan yang masuk tapi tidak terjadwal
            $allExpectedOrAttendedEmployeeIds = array_unique(array_merge($expectedEmployeeIds, $actualAttendedEmployeeIds));

            $gTotal = count($allExpectedOrAttendedEmployeeIds);
            $gHadir = count($actualAttendedEmployeeIds);
            $gBelum = max(0, $gTotal - $gHadir);

            // Hitung tepat waktu vs terlambat untuk guru & staf (jam masuk standar sekolah)
            $schoolClassroom = \App\Models\Classroom::where('school_id', $school->id)->whereNotNull('entry_time')->first();
            $schoolEntryTime = ($schoolClassroom && $schoolClassroom->entry_time) ? $schoolClassroom->entry_time . ':00' : '07:30:00';

            $gTepat = 0;
            $gTerlambat = 0;
            foreach ($schoolEmpAtts as $empAtt) {
                $timeIn = $empAtt->time_in ?? '00:00:00';
                if (strlen($timeIn) === 5) {
                    $timeIn .= ':00';
                }
                if ($timeIn > $schoolEntryTime) {
                    $gTerlambat++;
                } else {
                    $gTepat++;
                }
            }

            $rekapUnit[] = [
                'school_id'  => $school->id,
                'name'       => $school->name,
                'type'       => strtoupper($school->type),
                'is_yayasan' => $isYayasan,
                'siswa'      => [
                    'total'       => $sTotal,
                    'tap_hadir'   => $sHadir + $sTerlambat,
                    'tepat_waktu' => $sHadir,
                    'terlambat'   => $sTerlambat,
                    'hadir'       => $sHadir,
                    'pulang'      => $sPulang,
                    'belum'       => $sBelum,
                ],
                'pegawai'    => [
                    'total'       => $gTotal,
                    'tap_hadir'   => $gHadir,
                    'tepat_waktu' => $gTepat,
                    'terlambat'   => $gTerlambat,
                    'hadir'       => $gHadir,
                    'belum'       => $gBelum,
                ],
            ];

            // Akumulasi ke Statistik Global
            $studentTotal      += $sTotal;
            $studentHadir      += $sHadir;
            $studentTerlambat  += $sTerlambat;
            $studentPulang     += $sPulang;
            $studentBelumAbsen += $sBelum;

            $employeeTotal      += $gTotal;
            $employeeHadir      += $gHadir;
            $employeeBelumAbsen += $gBelum;
        }

        // ── KHUSUS UNIT TERTENTU: Hitung Rekapitulasi Rombel / Kelas ────────
        $rombelStats = [];
        if ($filterType && $activeAcademicYearId && $schools->isNotEmpty()) {
            $singleSchool = $schools->first();
            $classrooms = \App\Models\Classroom::where('school_id', $singleSchool->id)
                ->where('academic_year_id', $activeAcademicYearId)
                ->where('is_active', true)
                ->orderBy('grade_level')
                ->orderBy('class_name')
                ->get();

            foreach ($classrooms as $cls) {
                $totalInClass = Student::whereHas('studentClasses', function ($sc) use ($cls, $activeAcademicYearId) {
                    $sc->where('classroom_id', $cls->id)
                       ->where('academic_year_id', $activeAcademicYearId)
                       ->where('status', 'aktif');
                })->count();

                $hadirInClass = $studentAttendances->where('classroom_id', $cls->id)->whereIn('status', ['hadir', 'terlambat'])->count();
                $tepatInClass = $studentAttendances->where('classroom_id', $cls->id)->where('status', 'hadir')->count();
                $lambatInClass = $studentAttendances->where('classroom_id', $cls->id)->where('status', 'terlambat')->count();
                $belumInClass = max(0, $totalInClass - $hadirInClass);
                $pctInClass = $totalInClass > 0 ? round(($hadirInClass / $totalInClass) * 100) : 0;

                $rombelStats[] = [
                    'id'     => $cls->id,
                    'name'   => $cls->class_name,
                    'grade'  => $cls->grade_level,
                    'total'  => $totalInClass,
                    'hadir'  => $hadirInClass,
                    'tepat'  => $tepatInClass,
                    'lambat' => $lambatInClass,
                    'belum'  => $belumInClass,
                    'pct'    => $pctInClass,
                ];
            }
        }

        // ── GABUNGKAN FEED AKTIVITAS TERBARU (25 item) ─────────────────
        $feed = collect();

        foreach ($studentAttendances as $att) {
            $hasPulang = $att->time_out && $att->time_out !== '00:00:00' && $att->time_out !== '00:00';
            
            $statusLabel = $hasPulang ? 'Pulang' : ($att->status === 'terlambat' ? 'Terlambat' : 'Masuk');
            $tipe = $hasPulang ? 'pulang' : ($att->status === 'terlambat' ? 'terlambat' : 'masuk');
            
            $waktuMasuk = $att->time_in ? substr($att->time_in, 0, 5) : '--:--';
            $waktuPulang = $hasPulang ? substr($att->time_out, 0, 5) : '';
            $waktuFormat = $waktuPulang ? "{$waktuMasuk} → {$waktuPulang}" : $waktuMasuk;
            
            $unitName  = $att->student->school->type ?? '';

            // Tentukan Cara Absen
            $recordedVia = strtolower($att->recorded_via ?? 'manual');
            if ($recordedVia === 'rfid') {
                $caraAbsen = 'Scan Kartu RFID';
                $caraAbsenTipe = 'rfid';
                $caraAbsenIcon = 'fa-solid fa-id-card';
            } elseif (in_array($recordedVia, ['gps', 'qr_gps', 'mobile', 'gps_pkl'])) {
                $caraAbsen = 'Mobile Phone';
                $caraAbsenTipe = 'mobile';
                $caraAbsenIcon = 'fa-solid fa-mobile-screen-button';
            } else {
                $caraAbsen = 'Manual';
                $caraAbsenTipe = 'manual';
                $caraAbsenIcon = 'fa-solid fa-clipboard-user';
            }

            $feed->push([
                'waktu'           => $waktuFormat,
                'jam_masuk'       => $waktuMasuk,
                'jam_keluar'      => $waktuPulang ?: '--:--',
                'nama'            => $att->student->full_name ?? 'Tidak dikenal',
                'info'            => $att->classroom->class_name ?? '-',
                'aksi'            => $statusLabel,
                'tipe'            => $tipe,
                'sort_time'       => $hasPulang ? $att->time_out : ($att->time_in ?? '00:00:00'),
                'kategori'        => 'siswa',
                'unit'            => strtolower($unitName),
                'foto'            => $att->student->photo_url ?? asset('images/default-student.jpg'),
                'school_name'     => $att->student->school->name ?? '',
                'recorded_via'    => $att->recorded_via,
                'cara_absen'      => $caraAbsen,
                'cara_absen_tipe' => $caraAbsenTipe,
                'cara_absen_icon' => $caraAbsenIcon,
            ]);
        }

        foreach ($employeeAttendances as $att) {
            $hasPulang = $att->time_out && $att->time_out !== '00:00:00' && $att->time_out !== '00:00';
            
            $statusLabel = $hasPulang ? 'Pulang' : 'Hadir';
            $tipe = $hasPulang ? 'pulang' : 'masuk';
            
            $waktuMasuk = $att->time_in ? substr($att->time_in, 0, 5) : '--:--';
            $waktuPulang = $hasPulang ? substr($att->time_out, 0, 5) : '';
            $waktuFormat = $waktuPulang ? "{$waktuMasuk} → {$waktuPulang}" : $waktuMasuk;
            
            $unitName  = $att->employee->school->type ?? '';

            // Tentukan Cara Absen
            $recordedVia = strtolower($att->recorded_via ?? 'manual');
            if ($recordedVia === 'rfid') {
                $caraAbsen = 'Scan Kartu RFID';
                $caraAbsenTipe = 'rfid';
                $caraAbsenIcon = 'fa-solid fa-id-card';
            } elseif (in_array($recordedVia, ['gps', 'qr_gps', 'mobile', 'gps_pkl'])) {
                $caraAbsen = 'Mobile Phone';
                $caraAbsenTipe = 'mobile';
                $caraAbsenIcon = 'fa-solid fa-mobile-screen-button';
            } else {
                $caraAbsen = 'Manual';
                $caraAbsenTipe = 'manual';
                $caraAbsenIcon = 'fa-solid fa-clipboard-user';
            }

            $feed->push([
                'waktu'           => $waktuFormat,
                'jam_masuk'       => $waktuMasuk,
                'jam_keluar'      => $waktuPulang ?: '--:--',
                'nama'            => $att->employee->full_name ?? 'Tidak dikenal',
                'info'            => 'Guru/Staf',
                'aksi'            => $statusLabel,
                'tipe'            => $tipe,
                'sort_time'       => $hasPulang ? $att->time_out : ($att->time_in ?? '00:00:00'),
                'kategori'        => 'pegawai',
                'unit'            => strtolower($unitName),
                'foto'            => $att->employee->photo_url ?? asset('images/default-student.jpg'),
                'school_name'     => $att->employee->school->name ?? '',
                'recorded_via'    => $att->recorded_via,
                'cara_absen'      => $caraAbsen,
                'cara_absen_tipe' => $caraAbsenTipe,
                'cara_absen_icon' => $caraAbsenIcon,
            ]);
        }

        // Urutkan berdasarkan waktu terbaru
        $feedSorted = $feed->sortByDesc('sort_time')->take(25)->values();

        return response()->json([
            'tanggal'      => $now->translatedFormat('l, d F Y'),
            'jam'          => $now->format('H:i:s'),
            'filter_unit'  => $filterType,
            'statistik'    => [
                'siswa_hadir'        => $studentHadir,
                'siswa_terlambat'    => $studentTerlambat,
                'siswa_pulang'       => $studentPulang,
                'siswa_belum'        => $studentBelumAbsen,
                'siswa_total'        => $studentTotal,
                'total_siswa_absen'  => $studentHadir + $studentTerlambat,
                'pegawai_hadir'      => $employeeHadir,
                'pegawai_belum'      => $employeeBelumAbsen,
                'pegawai_total'      => $employeeTotal,
                'total_pegawai_absen'=> $employeeHadir,
                'total_absen'        => ($studentHadir + $studentTerlambat) + $employeeHadir,
            ],
            'rekap_unit'   => $rekapUnit,
            'rombel_stats' => $rombelStats,
            'feed'         => $feedSorted,
            'last_updated' => $now->format('H:i:s'),
        ]);
    }
}
