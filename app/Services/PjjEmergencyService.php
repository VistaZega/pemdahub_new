<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PjjEmergencyService
{
    /**
     * Dapatkan konfigurasi lengkap Mode PJJ / Tanggap Darurat Bencana
     */
    public static function getPjjConfig(): array
    {
        $schoolIds = Setting::getValue('pjj_school_ids', [2, 7]); // Default: SMAS 1 (2) & SMK (7)
        if (is_string($schoolIds)) {
            $schoolIds = json_decode($schoolIds, true) ?: [];
        }
        if (!is_array($schoolIds)) {
            $schoolIds = [];
        }
        $schoolIds = array_map('intval', $schoolIds);

        return [
            'enabled'             => (bool) Setting::getValue('pjj_enabled', true),
            'school_ids'          => $schoolIds,
            'start_date'          => Setting::getValue('pjj_start_date', '2026-10-16'),
            'end_date'            => Setting::getValue('pjj_end_date', '2026-10-21'),
            'allow_teachers'      => (bool) Setting::getValue('pjj_allow_teachers', true),
            'auto_lms_attendance' => (bool) Setting::getValue('pjj_auto_lms_attendance', true),
            'reason'              => Setting::getValue(
                'pjj_reason',
                'Pemberlakuan PJJ Tanggap Darurat Bencana Kabut Asap (Surat Edaran No. 07/SE/YP-PEMBDA/X/2026)'
            ),
        ];
    }

    /**
     * Cek apakah Mode PJJ sedang aktif untuk unit sekolah tertentu pada tanggal tertentu
     */
    public static function isPjjActiveForSchool(?int $schoolId, ?string $date = null): bool
    {
        if (!$schoolId) {
            return false;
        }

        $config = static::getPjjConfig();
        if (!$config['enabled']) {
            return false;
        }

        // Cek apakah unit sekolah masuk dalam daftar unit yang diberlakukan PJJ
        if (!in_array((int) $schoolId, $config['school_ids'], true)) {
            return false;
        }

        // Evaluasi rentang tanggal aktif
        $checkDate = $date ? Carbon::parse($date)->toDateString() : Carbon::now('Asia/Jakarta')->toDateString();

        if (!empty($config['start_date']) && $checkDate < $config['start_date']) {
            return false;
        }

        if (!empty($config['end_date']) && $checkDate > $config['end_date']) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah Mode PJJ sedang aktif untuk siswa tertentu
     */
    public static function isPjjActiveForStudent(?Student $student, ?string $date = null): bool
    {
        if (!$student) {
            return false;
        }

        $schoolId = $student->school_id;

        // Fallback jika school_id pada student null: cari dari rombel aktif
        if (!$schoolId) {
            $studentClass = $student->studentClasses()
                ->where('status', 'aktif')
                ->with('classroom.school')
                ->first();
            $schoolId = $studentClass?->classroom?->school_id ?? $studentClass?->classroom?->school?->id;
        }

        return static::isPjjActiveForSchool($schoolId, $date);
    }

    /**
     * Cek apakah Mode PJJ mengizinkan presensi WFH untuk Guru / Pegawai
     */
    public static function isPjjActiveForTeacher($teacherOrEmployee, ?string $date = null): bool
    {
        if (!$teacherOrEmployee) {
            return false;
        }

        $config = static::getPjjConfig();
        if (!$config['allow_teachers']) {
            return false;
        }

        $schoolId = null;
        if ($teacherOrEmployee instanceof Teacher) {
            $schoolId = $teacherOrEmployee->school_id ?? $teacherOrEmployee->employee?->school_id;
        } elseif ($teacherOrEmployee instanceof Employee) {
            $schoolId = $teacherOrEmployee->school_id;
        } elseif (is_object($teacherOrEmployee) && isset($teacherOrEmployee->school_id)) {
            $schoolId = $teacherOrEmployee->school_id;
        }

        return static::isPjjActiveForSchool($schoolId, $date);
    }

    /**
     * Auto-record presensi harian siswa saat berinteraksi di LMS PembdaHUB
     * (Sesuai Butir 3 Surat Edaran: Kehadiran siswa tercatat otomatis melalui interaksi pada LMS)
     */
    public static function autoRecordLmsAttendance(?Student $student, string $source = 'lms_activity'): ?Attendance
    {
        if (!$student) {
            return null;
        }

        $config = static::getPjjConfig();
        if (!$config['auto_lms_attendance']) {
            return null;
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Pastikan unit sekolah siswa sedang PJJ pada hari ini
        if (!static::isPjjActiveForStudent($student, $today)) {
            return null;
        }

        // Cek apakah siswa sudah memiliki catatan kehadiran hari ini
        $existing = Attendance::where('student_id', $student->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Dapatkan rombel aktif siswa
        $studentClass = $student->studentClasses()
            ->where('status', 'aktif')
            ->where('academic_year_id', function ($q) {
                $q->select('id')->from('academic_years')->where('is_active', true)->limit(1);
            })
            ->first();

        if (!$studentClass) {
            return null;
        }

        $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

        try {
            $attendance = Attendance::create([
                'student_id'   => $student->id,
                'classroom_id' => $studentClass->classroom_id,
                'date'         => $today,
                'time_in'      => $currentTime,
                'status'       => 'hadir',
                'recorded_via' => 'pjj_lms',
                'device_id'    => 'LMS-AUTO-' . $student->user_id,
                'notes'        => 'Auto-Presensi Interaksi LMS: ' . ucfirst(str_replace('_', ' ', $source)),
            ]);

            // Catat Poin Reputasi Kehadiran
            if ($student->user_id) {
                \App\Models\ReputationLog::log(
                    $student->user_id,
                    10,
                    'attendance',
                    'Kehadiran PJJ melalui interaksi aktif LMS PembdaHUB',
                    $attendance
                );
            }

            return $attendance;
        } catch (\Throwable $e) {
            Log::warning('PjjEmergencyService: Gagal mencatat auto-presensi LMS', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Dapatkan URL Live Display Kehadiran per unit sekolah
     */
    public static function getDisplayUrlForSchool(?School $school): string
    {
        $baseUrl = rtrim(config('app.url', 'https://perguruanpembda.com'), '/');
        if (!$school) {
            return "{$baseUrl}/display";
        }

        $type = strtoupper($school->type ?? '');
        return match ($type) {
            'SMP' => "{$baseUrl}/display1",
            'SMA' => "{$baseUrl}/display2",
            'SMK' => "{$baseUrl}/display3",
            default => "{$baseUrl}/display",
        };
    }

    /**
     * Hitung statistik presensi siswa PJJ dengan deduplikasi:
     * Siswa yang hadir melalui tombol absensi DAN berinteraksi dengan LMS tetap dihitung 1 siswa hadir.
     * Karena PJJ, tidak ada Izin/Sakit — yang ada hanya Hadir dan Belum Absensi.
     */
    public static function calculatePjjStudentAttendanceStats($studentIds, ?string $date = null, $dailyAtts = null): array
    {
        if ($studentIds instanceof \Illuminate\Support\Collection) {
            $studentIds = $studentIds->map(fn($id) => (int)$id)->toArray();
        } else {
            $studentIds = array_map('intval', (array)$studentIds);
        }

        $totalSiswa = count($studentIds);
        if ($totalSiswa === 0) {
            return [
                'total_siswa'            => 0,
                'pjj_gps_count'          => 0,
                'pjj_lms_count'          => 0,
                'total_hadir_unik'       => 0,
                'belum_absensi'          => 0,
                'persen_hadir'           => 0,
                'hadir_student_ids'      => [],
                'unrecorded_student_ids' => [],
            ];
        }

        $checkDate = $date ?: Carbon::now('Asia/Jakarta')->toDateString();

        // 1. Siswa yang hadir melalui tombol absensi (pjj_gps atau scan gps mobile)
        $gpsStudentIds = [];
        if ($dailyAtts) {
            foreach ($dailyAtts as $att) {
                if ($att->recorded_via === 'pjj_gps' || (in_array($att->recorded_via, ['gps', 'qr_gps', 'mobile']) && $att->status === 'hadir')) {
                    $gpsStudentIds[(int)$att->student_id] = true;
                }
            }
        } else {
            $found = Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $checkDate)
                ->where(function ($q) {
                    $q->where('recorded_via', 'pjj_gps')
                      ->orWhere(function ($sq) {
                          $sq->whereIn('recorded_via', ['gps', 'qr_gps', 'mobile'])
                             ->where('status', 'hadir');
                      });
                })
                ->pluck('student_id')
                ->toArray();
            foreach ($found as $id) {
                $gpsStudentIds[(int)$id] = true;
            }
        }

        // 2. Siswa yang berinteraksi dengan LMS
        $lmsStudentIds = [];
        if ($dailyAtts) {
            foreach ($dailyAtts as $att) {
                if ($att->recorded_via === 'pjj_lms') {
                    $lmsStudentIds[(int)$att->student_id] = true;
                }
            }
        } else {
            $foundAttLms = Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $checkDate)
                ->where('recorded_via', 'pjj_lms')
                ->pluck('student_id')
                ->toArray();
            foreach ($foundAttLms as $id) {
                $lmsStudentIds[(int)$id] = true;
            }
        }

        // Aktivitas di tabel LMS (Progress Materi, Submissions, Kuis)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('lms_material_progress')) {
                $progIds = \Illuminate\Support\Facades\DB::table('lms_material_progress')
                    ->whereIn('student_id', $studentIds)
                    ->whereDate('updated_at', $checkDate)
                    ->pluck('student_id')
                    ->toArray();
                foreach ($progIds as $id) {
                    $lmsStudentIds[(int)$id] = true;
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('lms_submissions')) {
                $subIds = \Illuminate\Support\Facades\DB::table('lms_submissions')
                    ->whereIn('student_id', $studentIds)
                    ->where(function ($q) use ($checkDate) {
                        $q->whereDate('updated_at', $checkDate)
                          ->orWhereDate('created_at', $checkDate);
                    })
                    ->pluck('student_id')
                    ->toArray();
                foreach ($subIds as $id) {
                    $lmsStudentIds[(int)$id] = true;
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('lms_quiz_attempts')) {
                $quizIds = \Illuminate\Support\Facades\DB::table('lms_quiz_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->whereDate('created_at', $checkDate)
                    ->pluck('student_id')
                    ->toArray();
                foreach ($quizIds as $id) {
                    $lmsStudentIds[(int)$id] = true;
                }
            }
        } catch (\Throwable $e) {}

        // Juga periksa jika siswa memiliki catatan kehadiran berstatus 'hadir' lainnya hari ini
        $allHadirAttIds = [];
        if ($dailyAtts) {
            foreach ($dailyAtts as $att) {
                if ($att->status === 'hadir' || $att->status === 'terlambat') {
                    $allHadirAttIds[(int)$att->student_id] = true;
                }
            }
        }

        // 3. PENGGABUNGAN UNIK (DEDUPLIKASI):
        // Siswa yang absen tombol DAN buka LMS tetap dihitung 1 siswa hadir!
        $uniqueHadirIds = array_values(array_unique(array_merge(
            array_keys($gpsStudentIds),
            array_keys($lmsStudentIds),
            array_keys($allHadirAttIds)
        )));

        $totalHadirUnik = count($uniqueHadirIds);
        $unrecordedStudentIds = array_values(array_diff($studentIds, $uniqueHadirIds));
        $belumAbsensi = count($unrecordedStudentIds);
        $persenHadir = $totalSiswa > 0 ? (int)round(($totalHadirUnik / $totalSiswa) * 100) : 0;

        return [
            'total_siswa'            => $totalSiswa,
            'pjj_gps_count'          => count($gpsStudentIds),
            'pjj_lms_count'          => count($lmsStudentIds),
            'total_hadir_unik'       => $totalHadirUnik,
            'belum_absensi'          => $belumAbsensi,
            'persen_hadir'           => $persenHadir,
            'hadir_student_ids'      => $uniqueHadirIds,
            'unrecorded_student_ids' => $unrecordedStudentIds,
        ];
    }
}
