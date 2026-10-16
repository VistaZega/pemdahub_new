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
}
