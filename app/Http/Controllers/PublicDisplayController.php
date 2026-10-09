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

        $userAgent = request()->userAgent() ?? '';
        $isMobile = (bool) preg_match('/(android|iphone|ipad|ipod|blackberry|opera mini|mobile|windows phone)/i', $userAgent);
        if (request('mode') === 'mobile') {
            $isMobile = true;
        } elseif (request('mode') === 'desktop') {
            $isMobile = false;
        }

        return view('display.attendance', [
            'targetSchool' => $targetSchool,
            'unitNumber'   => $unitNumber,
            'targetType'   => $type,
            'isMobile'     => $isMobile,
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

        // Ambil daftar student_id yang aktif PKL (DUDI) hari ini untuk pengecualian jam masuk (O(1) flip lookup)
        $activePklStudentIds = \App\Models\PklPlacement::activeOnDate($today)
            ->pluck('student_id')
            ->flip()
            ->toArray();

        
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

                // Kelompokkan absensi siswa per student_id untuk memastikan 1 siswa = 1 status di hari ini
                $schoolStudentAtts = $studentAttendances->where('student.school_id', $school->id);
                $uniqueStudentGroups = $schoolStudentAtts->groupBy('student_id');

                $sTapHadir = $uniqueStudentGroups->count();

                // Siswa dinyatakan terlambat jika terdapat rekam absensi terlambat di hari ini
                // atau jam masuk pertama melewati batas toleransi masuk kelas
                // KETENTUAN KHUSUS PKL: Siswa PKL (DUDI) memiliki jam kerja fleksibel industri sehingga tidak pernah dianggap terlambat
                $sTerlambat = $uniqueStudentGroups->filter(function ($records, $studentId) use ($activePklStudentIds) {
                    $isPkl = isset($activePklStudentIds[$studentId]) || $records->contains('recorded_via', 'gps_pkl');
                    if ($isPkl) {
                        return false;
                    }
                    if ($records->contains('status', 'terlambat')) {
                        return true;
                    }
                    $firstIn = $records->filter(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))->sortBy('time_in')->first();
                    if ($firstIn && $firstIn->classroom && method_exists($firstIn->classroom, 'isLate')) {
                        return $firstIn->classroom->isLate($firstIn->time_in);
                    }
                    return false;
                })->count();

                $sHadir = max(0, $sTapHadir - $sTerlambat);

                $sPulang = $uniqueStudentGroups->filter(function ($records) {
                    return $records->contains(function ($r) {
                        return !empty($r->time_out) && $r->time_out !== '00:00:00' && $r->time_out !== '00:00';
                    });
                })->count();

                $sBelum = max(0, $sTotal - $sTapHadir);
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

            // Dapatkan ID semua karyawan sekolah ini yang SEBENARNYA hadir hari ini (deduplikasi per employee_id)
            $schoolEmpAtts = $employeeAttendances->where('employee.school_id', $school->id)->where('status', 'hadir');
            $uniqueEmpGroups = $schoolEmpAtts->groupBy('employee_id');
            $actualAttendedEmployeeIds = $uniqueEmpGroups->keys()->toArray();

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
            foreach ($uniqueEmpGroups as $empId => $empRecords) {
                $earliestTimeIn = $empRecords->filter(fn($r) => !empty($r->time_in) && $r->time_in !== '00:00:00')->sortBy('time_in')->first()?->time_in;
                $timeIn = $earliestTimeIn ?? '00:00:00';
                if (strlen($timeIn) === 5) {
                    $timeIn .= ':00';
                }
                if ($timeIn > $schoolEntryTime && $timeIn !== '00:00:00') {
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
                    'tap_hadir'   => $sTapHadir ?? ($sHadir + $sTerlambat),
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

                // Deduplikasi per siswa dalam rombel
                $classStudentGroups = $studentAttendances->where('classroom_id', $cls->id)->groupBy('student_id');
                $hadirInClass = $classStudentGroups->count();
                $lambatInClass = $classStudentGroups->filter(function($recs, $studentId) use ($cls, $activePklStudentIds) {
                    $isPkl = isset($activePklStudentIds[$studentId]) || $recs->contains('recorded_via', 'gps_pkl');
                    if ($isPkl) {
                        return false;
                    }
                    if ($recs->contains('status', 'terlambat')) return true;
                    $firstIn = $recs->filter(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))->sortBy('time_in')->first();
                    return ($firstIn && method_exists($cls, 'isLate') && $cls->isLate($firstIn->time_in));
                })->count();
                $tepatInClass = max(0, $hadirInClass - $lambatInClass);
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

        // ── GABUNGKAN FEED AKTIVITAS TERBARU (25 item, deduplikasi per orang) ──
        $feed = collect();

        $studentFeedGroups = $studentAttendances->groupBy('student_id');
        foreach ($studentFeedGroups as $studentId => $records) {
            $bestAtt = $records->first(fn($r) => !empty($r->time_out) && !in_array($r->time_out, ['00:00:00', '00:00']))
                ?? $records->first(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']) && in_array($r->recorded_via, ['rfid', 'qr', 'qrcode', 'gps', 'qr_gps', 'gps_pkl']))
                ?? $records->first(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))
                ?? $records->last();

            $earliestIn = $records->filter(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))->sortBy('time_in')->first()?->time_in;
            $latestOut  = $records->filter(fn($r) => !empty($r->time_out) && !in_array($r->time_out, ['00:00:00', '00:00']))->sortByDesc('time_out')->first()?->time_out;

            $hasPulang = !empty($latestOut);
            $isPkl = isset($activePklStudentIds[$studentId]) || $records->contains('recorded_via', 'gps_pkl') || ($bestAtt->recorded_via === 'gps_pkl');

            $isTerlambat = false;
            if (!$isPkl) {
                $isTerlambat = $records->contains('status', 'terlambat');
                if (!$isTerlambat && $earliestIn && $bestAtt->classroom && method_exists($bestAtt->classroom, 'isLate')) {
                    $isTerlambat = $bestAtt->classroom->isLate($earliestIn);
                }
            }
            
            $statusLabel = $hasPulang ? 'Pulang' : ($isTerlambat ? 'Terlambat' : ($isPkl ? 'Hadir' : 'Masuk'));
            $tipe = $hasPulang ? 'pulang' : ($isTerlambat ? 'terlambat' : 'masuk');
            
            $waktuMasuk = $earliestIn ? substr($earliestIn, 0, 5) : ($bestAtt->time_in ? substr($bestAtt->time_in, 0, 5) : '--:--');
            $waktuPulang = $latestOut ? substr($latestOut, 0, 5) : '';
            $waktuFormat = $waktuPulang ? "{$waktuMasuk} → {$waktuPulang}" : $waktuMasuk;
            
            $unitName  = $bestAtt->student->school->type ?? '';

            // Tentukan Cara Absen
            $metode = $this->resolveAttendanceMethod($bestAtt->recorded_via, $bestAtt->device_id, $unitName, $isPkl);
            $caraAbsen = $metode['label'];
            $caraAbsenTipe = $metode['tipe'];
            $caraAbsenIcon = $metode['icon'];

            $feed->push([
                'waktu'           => $waktuFormat,
                'jam_masuk'       => $waktuMasuk,
                'jam_keluar'      => $waktuPulang ?: '--:--',
                'nama'            => $bestAtt->student->full_name ?? 'Tidak dikenal',
                'info'            => $bestAtt->classroom->class_name ?? '-',
                'aksi'            => $statusLabel,
                'tipe'            => $tipe,
                'sort_time'       => $hasPulang ? $latestOut : ($earliestIn ?? ($bestAtt->time_in ?? '00:00:00')),
                'kategori'        => 'siswa',
                'unit'            => strtolower($unitName),
                'foto'            => $bestAtt->student->photo_url ?? asset('images/default-student.jpg'),
                'school_name'     => $bestAtt->student->school->name ?? '',
                'recorded_via'    => $bestAtt->recorded_via,
                'cara_absen'      => $caraAbsen,
                'cara_absen_tipe' => $caraAbsenTipe,
                'cara_absen_icon' => $caraAbsenIcon,
            ]);
        }

        $employeeFeedGroups = $employeeAttendances->groupBy('employee_id');
        foreach ($employeeFeedGroups as $empId => $records) {
            $bestAtt = $records->first(fn($r) => !empty($r->time_out) && !in_array($r->time_out, ['00:00:00', '00:00']))
                ?? $records->first(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))
                ?? $records->last();

            $earliestIn = $records->filter(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))->sortBy('time_in')->first()?->time_in;
            $latestOut  = $records->filter(fn($r) => !empty($r->time_out) && !in_array($r->time_out, ['00:00:00', '00:00']))->sortByDesc('time_out')->first()?->time_out;

            $hasPulang = !empty($latestOut);
            
            $statusLabel = $hasPulang ? 'Pulang' : 'Hadir';
            $tipe = $hasPulang ? 'pulang' : 'masuk';
            
            $waktuMasuk = $earliestIn ? substr($earliestIn, 0, 5) : ($bestAtt->time_in ? substr($bestAtt->time_in, 0, 5) : '--:--');
            $waktuPulang = $latestOut ? substr($latestOut, 0, 5) : '';
            $waktuFormat = $waktuPulang ? "{$waktuMasuk} → {$waktuPulang}" : $waktuMasuk;
            
            $unitName  = $bestAtt->employee->school->type ?? '';

            // Tentukan Cara Absen
            $metode = $this->resolveAttendanceMethod($bestAtt->recorded_via, $bestAtt->device_id ?? null, $unitName);
            $caraAbsen = $metode['label'];
            $caraAbsenTipe = $metode['tipe'];
            $caraAbsenIcon = $metode['icon'];

            $feed->push([
                'waktu'           => $waktuFormat,
                'jam_masuk'       => $waktuMasuk,
                'jam_keluar'      => $waktuPulang ?: '--:--',
                'nama'            => $bestAtt->employee->full_name ?? 'Tidak dikenal',
                'info'            => 'Guru/Staf',
                'aksi'            => $statusLabel,
                'tipe'            => $tipe,
                'sort_time'       => $hasPulang ? $latestOut : ($earliestIn ?? ($bestAtt->time_in ?? '00:00:00')),
                'kategori'        => 'pegawai',
                'unit'            => strtolower($unitName),
                'foto'            => $bestAtt->employee->photo_url ?? asset('images/default-student.jpg'),
                'school_name'     => $bestAtt->employee->school->name ?? '',
                'recorded_via'    => $bestAtt->recorded_via,
                'cara_absen'      => $caraAbsen,
                'cara_absen_tipe' => $caraAbsenTipe,
                'cara_absen_icon' => $caraAbsenIcon,
            ]);
        }

        // Urutkan berdasarkan waktu terbaru
        $feedSorted = $feed->sortByDesc('sort_time')->take(25)->values();

        return response()->json([
            'tanggal'          => $now->translatedFormat('l, d F Y'),
            'jam'              => $now->format('H:i:s'),
            'server_timestamp' => round(microtime(true) * 1000),
            'filter_unit'      => $filterType,
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

    /**
     * Resolusi metadata metode/cara absensi (RFID, QR Code, Mobile Phone, Manual)
     * Deteksi murni dari field recorded_via per-transaksi. Berlaku universal untuk semua unit sekolah.
     */
    private function resolveAttendanceMethod($recordedVia, $deviceId = null, $schoolType = null, $isPkl = false): array
    {
        $via = strtolower(trim((string)($recordedVia ?? 'manual')));

        if ($via === 'gps_pkl' || $isPkl) {
            return [
                'label' => 'Mobile PKL (DUDI)',
                'tipe'  => 'mobile_pkl',
                'icon'  => 'fa-solid fa-briefcase',
            ];
        }

        if ($via === 'rfid') {
            return [
                'label' => 'Scan Kartu RFID',
                'tipe'  => 'rfid',
                'icon'  => 'fa-solid fa-id-card',
            ];
        }

        if (in_array($via, ['qr', 'qr_code', 'qrcode', 'barcode', 'scan_qr'])) {
            return [
                'label' => 'Scan QR Code',
                'tipe'  => 'qr',
                'icon'  => 'fa-solid fa-qrcode',
            ];
        }

        if ($via === 'qr_gps') {
            if (!empty($deviceId) && str_starts_with(strtoupper($deviceId), 'KIOSK')) {
                return [
                    'label' => 'Scan QR Code',
                    'tipe'  => 'qr',
                    'icon'  => 'fa-solid fa-qrcode',
                ];
            }
            return [
                'label' => 'Mobile Phone',
                'tipe'  => 'mobile',
                'icon'  => 'fa-solid fa-mobile-screen-button',
            ];
        }

        if (in_array($via, ['gps', 'mobile'])) {
            return [
                'label' => 'Mobile Phone',
                'tipe'  => 'mobile',
                'icon'  => 'fa-solid fa-mobile-screen-button',
            ];
        }

        return [
            'label' => 'Manual',
            'tipe'  => 'manual',
            'icon'  => 'fa-solid fa-clipboard-user',
        ];
    }
}
