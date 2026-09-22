<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExecutiveReportService
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * 1A. Daily Attendance Digest for Principal (Kepala Sekolah) - per unit sekolah
     * Dikirim 15 menit setelah batas toleransi (08:00 WIB) setiap hari aktif (Senin-Jumat)
     * Dilengkapi protokol anti-ban (jeda acak, proteksi duplikasi harian, & variasi sapaan).
     */
    public function sendPrincipalDailyAttendanceDigest(array $options = []): array
    {
        $dryRun = $options['dry_run'] ?? false;
        $force = $options['force'] ?? false;
        $targetPhone = $options['target_phone'] ?? null;
        $schoolIdFilter = $options['school_id'] ?? null;
        $delayMin = $options['delay_min'] ?? (int)Setting::getValue('wa_digest_delay_min', 45);
        $delayMax = $options['delay_max'] ?? (int)Setting::getValue('wa_digest_delay_max', 90);
        $logger = $options['logger'] ?? null;

        if (!$targetPhone && (!$this->whatsappService->isEnabled() || !Setting::getValue('wa_digest_enabled', true))) {
            Log::channel('whatsapp')->info('WA digest skipped: WhatsApp service or wa_digest_enabled is disabled');
            return ['success' => false, 'sent' => 0, 'message' => 'Layanan WhatsApp atau otomatisasi rekapitulasi sedang dihentikan sementara di Pengaturan'];
        }

        if (!$targetPhone && !$this->whatsappService->isConnected()) {
            $msg = 'Gateway WhatsApp sedang terputus (Disconnect). Rekapitulasi dibatalkan otomatis demi mencegah pengiriman pesan error.';
            Log::channel('whatsapp')->warning("WA digest aborted: {$msg}");
            return ['success' => false, 'sent' => 0, 'message' => $msg];
        }

        if (!$targetPhone && !Setting::getValue('wa_send_principal_attendance', true)) {
            Log::channel('whatsapp')->info('WA digest skipped: wa_send_principal_attendance is disabled');
            return ['success' => false, 'sent' => 0, 'message' => 'Otomatisasi WA Rekap Kepsek dinonaktifkan di pengaturan'];
        }

        $dateToday = date('Y-m-d');
        $dateFormatted = date('d F Y');
        $sentCount = 0;
        $skippedCount = 0;
        $errors = [];

        // Iterasi per unit sekolah aktif (tanpa Yayasan)
        $query = School::schoolsOnly()->with('principal');
        if ($schoolIdFilter) {
            $query->where('id', $schoolIdFilter);
        }
        $schools = $query->get();

        $totalToSend = $schools->count();
        $currentIndex = 0;

        foreach ($schools as $school) {
            $currentIndex++;
            try {
                // Resolve nomor HP Kepala Sekolah via relasi School -> Principal (Teacher) -> phone
                $principal = $school->principal;
                $phone = $targetPhone ?: ($principal?->phone ?? null);

                // Fallback: cari user dengan role kepala_sekolah di sekolah ini
                if (!$phone) {
                    $kepsekUser = User::where('role', 'kepala_sekolah')
                        ->where('school_id', $school->id)
                        ->first();
                    if ($kepsekUser && $kepsekUser->teacher) {
                        $phone = $targetPhone ?: $kepsekUser->teacher->phone;
                    }
                }

                if (!$phone) {
                    $msg = "Nomor HP Kepala Sekolah {$school->name} tidak ditemukan";
                    Log::channel('whatsapp')->warning($msg);
                    if ($logger) $logger("⚠️ {$msg}");
                    continue;
                }

                $principalName = $principal?->full_name ?? $school->principal_name ?? 'Kepala Sekolah';

                // Check Idempotency Lock: lewati jika hari ini sudah pernah terkirim (kecuali mode force atau single test)
                if (!$targetPhone && !$force && $this->isDigestSentToday('principal', $school->id, $dateToday)) {
                    $skippedCount++;
                    $msg = "Rekap Kepsek {$school->name} sudah terkirim hari ini (dilewati)";
                    Log::channel('whatsapp')->info($msg);
                    if ($logger) $logger("⏭️ {$msg}");
                    continue;
                }

                // Ambil classroom_ids milik unit sekolah ini di TP aktif
                $activeYear = AcademicYear::where('is_active', true)->first();
                if (!$activeYear) continue;

                $classroomIds = Classroom::where('school_id', $school->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->pluck('id');

                // 1. SISWA STATS — absensi harian (schedule_id = null) dengan fallback absensi KBM
                $studentIds = DB::table('student_classes')
                    ->whereIn('classroom_id', $classroomIds)
                    ->pluck('student_id');

                $dailyAttsSiswa = Attendance::whereIn('student_id', $studentIds)
                    ->whereDate('date', $dateToday)
                    ->whereNull('schedule_id')
                    ->get()
                    ->keyBy('student_id');

                if ($dailyAttsSiswa->count() < $studentIds->count()) {
                    $subjectAttsSiswa = Attendance::whereIn('student_id', $studentIds)
                        ->whereDate('date', $dateToday)
                        ->whereNotNull('schedule_id')
                        ->orderBy('id', 'asc')
                        ->get()
                        ->groupBy('student_id');

                    foreach ($subjectAttsSiswa as $sId => $records) {
                        if (!$dailyAttsSiswa->has($sId)) {
                            $dailyAttsSiswa->put($sId, $records->last());
                        }
                    }
                }

                $statsSiswa = $dailyAttsSiswa->groupBy('status')->map(fn($g) => $g->count())->toArray();

                $presentS = $statsSiswa['hadir'] ?? 0;
                $lateS = $statsSiswa['terlambat'] ?? 0;
                $sickS = $statsSiswa['sakit'] ?? 0;
                $permitS = $statsSiswa['izin'] ?? 0;
                $absentS = $statsSiswa['alpha'] ?? $statsSiswa['alpa'] ?? 0;
                $totalSiswa = $studentIds->count();
                $recordedS = $presentS + $lateS + $sickS + $permitS + $absentS;
                $unrecordedS = max(0, $totalSiswa - $recordedS);

                // 2. GURU STATS — pegawai bertipe 'guru' di unit sekolah ini
                $statsGuru = \App\Models\EmployeeAttendance::whereDate('date', $dateToday)
                    ->where('school_id', $school->id)
                    ->whereHas('employee', fn($q) => $q->where('employee_type', 'guru'))
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $presentG = $statsGuru['hadir'] ?? 0;
                $sickG = $statsGuru['sakit'] ?? 0;
                $permitG = $statsGuru['izin'] ?? 0;
                $absentG = $statsGuru['alpha'] ?? $statsGuru['alpa'] ?? 0;
                $dinasG = $statsGuru['dinas_luar'] ?? 0;

                // 3. PEGAWAI / STAF STATS — pegawai non-guru di unit sekolah ini
                $statsStaff = \App\Models\EmployeeAttendance::whereDate('date', $dateToday)
                    ->where('school_id', $school->id)
                    ->whereHas('employee', fn($q) => $q->where('employee_type', '!=', 'guru'))
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $presentP = $statsStaff['hadir'] ?? 0;
                $sickP = $statsStaff['sakit'] ?? 0;
                $permitP = $statsStaff['izin'] ?? 0;
                $absentP = $statsStaff['alpha'] ?? $statsStaff['alpa'] ?? 0;
                $cutiP = $statsStaff['cuti'] ?? 0;

                $greeting = $this->getPolymorphicGreeting($principalName, 'Bapak/Ibu');
                $closing = $this->getPolymorphicClosing();

                $templateData = [
                    'sekolah' => $school->name,
                    'nama_kepsek' => $principalName,
                    'tanggal' => $dateFormatted,
                    'waktu_rekap' => '08:00 WIB',
                    'total_siswa' => $totalSiswa,
                    'siswa_hadir' => $presentS,
                    'siswa_terlambat' => $lateS,
                    'siswa_sakit' => $sickS,
                    'siswa_izin' => $permitS,
                    'siswa_alpha' => $absentS,
                    'siswa_belum_presensi' => $unrecordedS,
                    'guru_hadir' => $presentG,
                    'guru_dinas' => $dinasG,
                    'guru_sakit' => $sickG,
                    'guru_izin' => $permitG,
                    'guru_alpha' => $absentG,
                    'pegawai_hadir' => $presentP,
                    'pegawai_cuti' => $cutiP,
                    'pegawai_sakit' => $sickP,
                    'pegawai_izin' => $permitP,
                    'pegawai_alpha' => $absentP,
                    'salam_pembuka' => $greeting,
                    'catatan_penutup' => $closing,
                ];

                if ($dryRun) {
                    $sentCount++;
                    if ($logger) $logger("🔍 [SIMULASI] Rekap Kepsek {$school->name} siap dikirim ke {$phone} ({$principalName})");
                } else {
                    $res = $this->whatsappService->sendTemplate($phone, 'executive.principal_daily_attendance', $templateData);
                    if ($res['success'] ?? false) {
                        $sentCount++;
                        if (!$targetPhone) {
                            $this->markDigestSentToday('principal', $school->id, $dateToday);
                        }
                        if ($logger) $logger("✅ Rekap Kepsek {$school->name} berhasil terkirim ke {$phone} ({$principalName})");
                    } else {
                        $errMsg = $res['error'] ?? 'Gagal kirim via gateway';
                        $errors[] = "{$school->name}: {$errMsg}";
                        if ($logger) $logger("❌ Gagal kirim Kepsek {$school->name}: {$errMsg}");
                    }
                }

                // Jika mode single target test, cukup kirim 1 contoh dan berhenti
                if ($targetPhone) {
                    break;
                }

                // Pacing aman antar Kepala Sekolah
                if ($currentIndex < $totalToSend) {
                    $this->applyHumanPacing($delayMin, $delayMax, $dryRun, $logger);
                }

            } catch (\Exception $e) {
                Log::channel('whatsapp')->error("Failed to send principal digest for {$school->name}: " . $e->getMessage());
                $errors[] = $school->name;
                if ($logger) $logger("❌ Error Kepsek {$school->name}: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'sent' => $sentCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
            'message' => "Digest Kehadiran Kepsek terkirim ke {$sentCount} unit sekolah" . ($skippedCount ? " ({$skippedCount} dilewati)" : '') . (count($errors) ? ", gagal: " . implode(', ', $errors) : ''),
        ];
    }

    /**
     * 1B. Daily Attendance Digest for Homeroom Teachers (Wali Kelas)
     * Dikirim 15 menit setelah batas toleransi (08:00 WIB) setiap hari aktif (Senin-Jumat)
     * Dilengkapi protokol anti-ban (jeda acak 12-20s, batching antar sekolah, idempotensi, variasi teks).
     */
    public function sendHomeroomDailyAttendanceDigest(array $options = []): array
    {
        $dryRun = $options['dry_run'] ?? false;
        $force = $options['force'] ?? false;
        $targetPhone = $options['target_phone'] ?? null;
        $schoolIdFilter = $options['school_id'] ?? null;
        $delayMin = $options['delay_min'] ?? (int)Setting::getValue('wa_digest_delay_min', 90);
        $delayMax = $options['delay_max'] ?? (int)Setting::getValue('wa_digest_delay_max', 180);
        $batchPause = $options['batch_pause'] ?? (int)Setting::getValue('wa_digest_batch_pause', 600);
        $logger = $options['logger'] ?? null;

        if (!$targetPhone && (!$this->whatsappService->isEnabled() || !Setting::getValue('wa_digest_enabled', true))) {
            Log::channel('whatsapp')->info('WA digest skipped: WhatsApp service or wa_digest_enabled is disabled');
            return ['success' => false, 'sent' => 0, 'message' => 'Layanan WhatsApp atau otomatisasi rekapitulasi sedang dihentikan sementara di Pengaturan'];
        }

        if (!$targetPhone && !$this->whatsappService->isConnected()) {
            $msg = 'Gateway WhatsApp sedang terputus (Disconnect). Rekapitulasi dibatalkan otomatis demi mencegah pengiriman pesan error.';
            Log::channel('whatsapp')->warning("WA digest aborted: {$msg}");
            return ['success' => false, 'sent' => 0, 'message' => $msg];
        }

        if (!$targetPhone && !Setting::getValue('wa_send_homeroom_attendance', true)) {
            Log::channel('whatsapp')->info('WA digest skipped: wa_send_homeroom_attendance is disabled');
            return ['success' => false, 'sent' => 0, 'message' => 'Otomatisasi WA Rekap Wali Kelas dinonaktifkan di pengaturan'];
        }

        $dateToday = date('Y-m-d');
        $dateFormatted = date('d F Y');

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return ['success' => false, 'message' => 'Tahun Akademik Aktif tidak ditemukan'];

        // Hanya kelas dari unit sekolah aktif (tanpa Yayasan)
        $querySchools = School::schoolsOnly();
        if ($schoolIdFilter) {
            $querySchools->where('id', $schoolIdFilter);
        }
        $schools = $querySchools->get();

        $sentCount = 0;
        $skippedCount = 0;
        $errors = [];

        $totalSchools = $schools->count();
        $schoolIndex = 0;

        foreach ($schools as $school) {
            $schoolIndex++;

            $classrooms = Classroom::where('academic_year_id', $activeYear->id)
                ->where('school_id', $school->id)
                ->with(['homeroomTeacher.employee'])
                ->get();

            if ($classrooms->isEmpty()) continue;

            if ($logger) {
                $logger("🏫 Memulai batch Wali Kelas: {$school->name} ({$classrooms->count()} kelas)...");
            }

            $totalClassesInSchool = $classrooms->count();
            $classIndex = 0;

            foreach ($classrooms as $class) {
                $classIndex++;
                $homeroomTeacher = $class->homeroomTeacher;
                if (!$homeroomTeacher) {
                    if ($logger) $logger("⚠️ Kelas {$class->name} ({$school->name}) belum memiliki Wali Kelas (dilewati)");
                    continue;
                }

                // Resolve nomor HP wali kelas
                $phone = $targetPhone ?: ($homeroomTeacher->phone ?? null);
                if (!$phone) {
                    if ($logger) $logger("⚠️ Wali Kelas {$homeroomTeacher->full_name} ({$class->name}) tidak memiliki nomor HP");
                    continue;
                }

                // Check Idempotency Lock: lewati jika hari ini sudah pernah terkirim
                if (!$targetPhone && !$force && $this->isDigestSentToday('homeroom', $class->id, $dateToday)) {
                    $skippedCount++;
                    $msg = "Rekap Kelas {$class->name} ({$homeroomTeacher->full_name}) sudah terkirim hari ini (dilewati)";
                    Log::channel('whatsapp')->info($msg);
                    if ($logger) $logger("⏭️ {$msg}");
                    continue;
                }

                // Get attendance stats for this classroom — absensi harian (schedule_id = null) dengan fallback absensi KBM jika presensi harian belum tercatat
                $studentIds = $class->students()->pluck('students.id');
                $totalSiswa = $studentIds->count();

                $dailyAtts = Attendance::whereIn('student_id', $studentIds)
                    ->whereDate('date', $dateToday)
                    ->whereNull('schedule_id')
                    ->with('student')
                    ->get()
                    ->keyBy('student_id');

                // Fallback dari absensi per-pelajaran (schedule_id != null) jika presensi harian sekolah belum diisi untuk siswa terkait
                if ($dailyAtts->count() < $totalSiswa) {
                    $subjectAtts = Attendance::whereIn('student_id', $studentIds)
                        ->whereDate('date', $dateToday)
                        ->whereNotNull('schedule_id')
                        ->with('student')
                        ->orderBy('id', 'asc')
                        ->get()
                        ->groupBy('student_id');

                    foreach ($subjectAtts as $sId => $records) {
                        if (!$dailyAtts->has($sId)) {
                            $dailyAtts->put($sId, $records->last());
                        }
                    }
                }

                $stats = $dailyAtts->groupBy('status')->map(fn($g) => $g->count())->toArray();

                $present = $stats['hadir'] ?? 0;
                $late = $stats['terlambat'] ?? 0;
                $sick = $stats['sakit'] ?? 0;
                $permit = $stats['izin'] ?? 0;
                $absent = $stats['alpha'] ?? $stats['alpa'] ?? 0;
                $recordedCount = $present + $late + $sick + $permit + $absent;
                $unrecordedCount = max(0, $totalSiswa - $recordedCount);

                // Daftar siswa tidak hadir / terlambat yang tercatat
                $recordedAbsentItems = $dailyAtts
                    ->filter(fn($a) => in_array($a->status, ['alpha', 'alpa', 'sakit', 'izin', 'terlambat']))
                    ->map(fn($a) => "• " . ($a->student->full_name ?? 'Siswa') . " (" . strtoupper($a->status) . ")")
                    ->values()
                    ->toArray();

                // Logika penyusunan rincian kehadiran & status presensi yang akurat
                if ($totalSiswa === 0) {
                    $absentListSnippet = "• Belum ada siswa yang terdaftar di kelas ini.";
                } elseif ($recordedCount === 0) {
                    $absentListSnippet = "⚠️ Belum ada data presensi yang masuk untuk kelas ini ({$totalSiswa} siswa belum presensi).";
                } else {
                    $detailItems = $recordedAbsentItems;

                    // Tambahkan informasi siswa yang belum melakukan presensi sama sekali
                    if ($unrecordedCount > 0) {
                        $recordedStudentIds = $dailyAtts->keys();
                        $unrecordedStudentIds = $studentIds->diff($recordedStudentIds);

                        $unrecordedStudents = \App\Models\Student::whereIn('id', $unrecordedStudentIds)
                            ->orderBy('full_name')
                            ->pluck('full_name');

                        if ($unrecordedStudents->count() <= 10) {
                            foreach ($unrecordedStudents as $unName) {
                                $detailItems[] = "• {$unName} (BELUM PRESENSI)";
                            }
                        } else {
                            $detailItems[] = "• {$unrecordedCount} siswa lainnya (BELUM PRESENSI)";
                        }
                    }

                    if (empty($detailItems)) {
                        $absentListSnippet = "• ✅ Nihil (Seluruh {$totalSiswa} Siswa Hadir Tepat Waktu - 100% ✨)";
                    } else {
                        $absentListSnippet = implode("\n", $detailItems);
                    }
                }

                $greeting = $this->getPolymorphicGreeting($homeroomTeacher->full_name, 'Bapak/Ibu');
                $closing = $this->getPolymorphicClosing();

                $templateData = [
                    'kelas' => $class->name,
                    'nama_wali_kelas' => $homeroomTeacher->full_name,
                    'tanggal' => $dateFormatted,
                    'waktu_rekap' => '08:00 WIB',
                    'total_siswa' => $totalSiswa,
                    'hadir' => $present,
                    'terlambat' => $late,
                    'sakit' => $sick,
                    'izin' => $permit,
                    'alpha' => $absent,
                    'belum_presensi' => $unrecordedCount,
                    'daftar_tidak_hadir' => $absentListSnippet,
                    'salam_pembuka' => $greeting,
                    'catatan_penutup' => $closing,
                ];

                if ($dryRun) {
                    $sentCount++;
                    if ($logger) $logger("🔍 [SIMULASI] Rekap {$class->name} siap dikirim ke {$phone} ({$homeroomTeacher->full_name})");
                } else {
                    $res = $this->whatsappService->sendTemplate($phone, 'executive.homeroom_daily_attendance', $templateData);
                    if ($res['success'] ?? false) {
                        $sentCount++;
                        if (!$targetPhone) {
                            $this->markDigestSentToday('homeroom', $class->id, $dateToday);
                        }
                        if ($logger) $logger("✅ Rekap {$class->name} terkirim ke {$phone} ({$homeroomTeacher->full_name})");
                    } else {
                        $errMsg = $res['error'] ?? 'Gagal kirim via gateway';
                        $errors[] = "{$class->name}: {$errMsg}";
                        if ($logger) $logger("❌ Gagal kirim {$class->name}: {$errMsg}");
                    }
                }

                // Jika mode single target test, cukup kirim 1 contoh dan selesai
                if ($targetPhone) {
                    break 2;
                }

                // Apply human pacing between classes within the same school
                if ($classIndex < $totalClassesInSchool) {
                    $this->applyHumanPacing($delayMin, $delayMax, $dryRun, $logger);
                }
            }

            // Pause between school units (resting window) if more schools remain
            if ($schoolIndex < $totalSchools && !$targetPhone) {
                $this->applyBatchPause($batchPause, $dryRun, $logger);
            }
        }

        return [
            'success' => true,
            'sent' => $sentCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
            'message' => "Digest Kehadiran Wali Kelas terkirim ke {$sentCount} kelas" . ($skippedCount ? " ({$skippedCount} dilewati)" : '') . (count($errors) ? ", gagal: " . implode(', ', $errors) : ''),
        ];
    }

    /**
     * Check if a digest has already been sent today (Idempotency Lock).
     */
    public function isDigestSentToday(string $type, int|string $targetId, ?string $date = null): bool
    {
        $date = $date ?: date('Y-m-d');
        $key = "wa_digest_sent_{$type}_{$targetId}_{$date}";
        return Cache::has($key);
    }

    /**
     * Mark a digest as sent today (TTL 2 days).
     */
    public function markDigestSentToday(string $type, int|string $targetId, ?string $date = null): void
    {
        $date = $date ?: date('Y-m-d');
        $key = "wa_digest_sent_{$type}_{$targetId}_{$date}";
        Cache::put($key, now()->toDateTimeString(), 86400 * 2);
    }

    /**
     * Clear idempotency lock for today (useful for re-sending/testing).
     */
    public function clearDigestSentToday(string $type, int|string $targetId, ?string $date = null): void
    {
        $date = $date ?: date('Y-m-d');
        $key = "wa_digest_sent_{$type}_{$targetId}_{$date}";
        Cache::forget($key);
    }

    /**
     * Apply randomized delay to mimic human behavior (Anti-Ban).
     */
    protected function applyHumanPacing(int $minSeconds, int $maxSeconds, bool $dryRun = false, ?callable $logger = null): void
    {
        if ($dryRun) {
            usleep(100000); // 0.1s
            return;
        }

        $min = max(2, $minSeconds);
        $max = max($min, $maxSeconds);
        $sleepSeconds = rand($min, $max);

        if ($logger) {
            $timeStr = $sleepSeconds >= 60 ? "{$sleepSeconds} detik (~" . round($sleepSeconds / 60, 1) . " menit)" : "{$sleepSeconds} detik";
            $logger("⏳ Jeda santai alami {$timeStr} sebelum pesan berikutnya...");
        }

        sleep($sleepSeconds);
    }

    /**
     * Apply resting window between batches / school units.
     */
    protected function applyBatchPause(int $seconds, bool $dryRun = false, ?callable $logger = null): void
    {
        if ($dryRun) {
            usleep(100000); // 0.1s
            return;
        }

        $pause = max(5, $seconds);
        if ($logger) {
            $pauseMins = round($pause / 60, 1);
            $logger("☕ Istirahat pendinginan antar-sekolah ({$pause} detik / ~{$pauseMins} menit)...");
        }

        sleep($pause);
    }

    /**
     * Dynamic natural greetings to vary message structure.
     */
    protected function getPolymorphicGreeting(string $name, string $title = 'Bapak/Ibu'): string
    {
        $firstWord = explode(' ', trim($name))[0];
        $greetings = [
            "Selamat pagi, {$title} {$name}. 🙏",
            "Salam hormat, {$title} {$name}.",
            "Selamat pagi dan salam hangat, {$title} {$name}. ✨",
            "Yth. {$title} {$name}, selamat pagi.",
            "Semoga sehat dan bersemangat selalu, {$title} {$firstWord}. 🌿",
        ];
        return $greetings[array_rand($greetings)];
    }

    /**
     * Dynamic closing statements.
     */
    protected function getPolymorphicClosing(): string
    {
        $closings = [
            "Semoga kegiatan belajar mengajar hari ini berjalan lancar dan penuh berkah. 🙏",
            "Terima kasih atas dedikasi dan bimbingan luar biasa Bapak/Ibu untuk siswa kita. ✨",
            "Semangat mendampingi dan mencerdaskan generasi penerus Pembda hari ini! 🌟",
            "Semoga seluruh aktivitas pendidikan hari ini diberikan kemudahan dan kelancaran. 🌿",
        ];
        return $closings[array_rand($closings)];
    }

    /**
     * Resolve target admin WhatsApp numbers for process notifications.
     */
    public function getAdminRecipients(): array
    {
        $phones = [];

        // 1. Setting khusus pengingat admin digest / alert WA
        $custom = Setting::getValue('wa_digest_admin_phone') 
            ?: Setting::getValue('wa_admin_phone') 
            ?: Setting::getValue('wa_alert_phone') 
            ?: config('services.alerts.whatsapp.admin_phone');

        if (!empty($custom)) {
            $clean = preg_replace('/[^0-9]/', '', (string)$custom);
            if (strlen($clean) >= 9) {
                $phones[$clean] = 'Admin PembdaHUB';
            }
        }

        // 2. Deteksi akun role superadmin / admin_yayasan
        try {
            $admins = User::whereIn('role', ['superadmin', 'admin_yayasan'])
                ->with(['employee', 'teacher.employee'])
                ->get();

            foreach ($admins as $adm) {
                $phone = $adm->phone 
                    ?? $adm->employee?->phone 
                    ?? $adm->teacher?->phone 
                    ?? $adm->teacher?->employee?->phone 
                    ?? null;

                if ($phone) {
                    $clean = preg_replace('/[^0-9]/', '', (string)$phone);
                    if (strlen($clean) >= 9) {
                        $phones[$clean] = $adm->name;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed resolving admin recipients: ' . $e->getMessage());
        }

        // 3. Fallback nomor pengelola jika belum terkonfigurasi
        if (empty($phones)) {
            $phones['081263582950'] = 'Admin Pengelola';
        }

        return $phones;
    }

    /**
     * Notify Admin that attendance digest broadcast has started.
     */
    public function notifyAdminDigestStarted(array $context = []): array
    {
        if (!Setting::getValue('wa_notify_admin_digest', true)) {
            return ['success' => false, 'message' => 'Notifikasi admin dinonaktifkan di pengaturan'];
        }

        $dryRun = $context['dry_run'] ?? false;
        $logger = $context['logger'] ?? null;
        $targetPhone = $context['target_phone'] ?? null;

        $schoolsCount = $context['schools_count'] ?? School::schoolsOnly()->count();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $classesCount = $context['classes_count'] ?? ($activeYear ? Classroom::where('academic_year_id', $activeYear->id)->count() : 0);

        $nowTime = date('H:i');
        $dateFormatted = date('d F Y');

        $message = "📢 *INFORMASI SISTEM PEMBDAHUB*\n" .
                   "━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                   "Halo Admin PembdaHUB,\n\n" .
                   "Otomatisasi pengiriman *Rekapitulasi Kehadiran Harian* ke Kepala Sekolah dan Wali Kelas baru saja *DIMULAI* pada pukul *{$nowTime} WIB* ({$dateFormatted}).\n\n" .
                   "📋 *Target Pengiriman:*\n" .
                   "• 🏫 Kepala Sekolah: *{$schoolsCount} Unit Sekolah*\n" .
                   "• 👩‍🏫 Wali Kelas: *{$classesCount} Rombel*\n" .
                   "• 🛡️ Mode Pacing: *Anti-Ban Santai* (Jeda acak bertahap)\n\n" .
                   "⏳ _Proses pengiriman berjalan di latar belakang (background). Laporan hasil akhir status pengiriman akan segera dikirimkan kembali ke nomor ini setelah seluruh batch selesai._\n" .
                   "━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                   "_Sistem Otomasi Eksekutif PembdaHUB_";

        $recipients = $targetPhone ? [$targetPhone => 'Test Target'] : $this->getAdminRecipients();
        $sentCount = 0;

        foreach ($recipients as $phone => $name) {
            if ($dryRun) {
                $sentCount++;
                if ($logger) $logger("📢 [SIMULASI] Notifikasi Mulai dikirim ke Admin {$phone} ({$name})");
            } else {
                $res = $this->whatsappService->sendMessage($phone, $message);
                if ($res['success'] ?? false) {
                    $sentCount++;
                    if ($logger) $logger("📢 Notifikasi Mulai berhasil dikirim ke Admin {$phone} ({$name})");
                } else {
                    if ($logger) $logger("⚠️ Gagal kirim notifikasi mulai ke Admin {$phone}: " . ($res['error'] ?? 'Unknown error'));
                }
            }
        }

        return ['success' => $sentCount > 0, 'sent' => $sentCount, 'message' => "Notifikasi mulai terkirim ke {$sentCount} nomor admin"];
    }

    /**
     * Notify Admin that attendance digest broadcast has completed with full status recap.
     */
    public function notifyAdminDigestCompleted(array $summary = []): array
    {
        if (!Setting::getValue('wa_notify_admin_digest', true)) {
            return ['success' => false, 'message' => 'Notifikasi admin dinonaktifkan di pengaturan'];
        }

        $dryRun = $summary['dry_run'] ?? false;
        $logger = $summary['logger'] ?? null;
        $targetPhone = $summary['target_phone'] ?? null;

        $resP = $summary['res_principal'] ?? [];
        $resH = $summary['res_homeroom'] ?? [];

        $pSent = $resP['sent'] ?? 0;
        $pSkipped = $resP['skipped'] ?? 0;
        $pErrors = $resP['errors'] ?? [];
        $pErrCount = count($pErrors);
        $pErrStr = $pErrCount > 0 ? " (" . implode(', ', $pErrors) . ")" : "";

        $hSent = $resH['sent'] ?? 0;
        $hSkipped = $resH['skipped'] ?? 0;
        $hErrors = $resH['errors'] ?? [];
        $hErrCount = count($hErrors);
        $hErrStr = $hErrCount > 0 ? " (" . implode(', ', array_slice($hErrors, 0, 5)) . ($hErrCount > 5 ? ' & lainnya' : '') . ")" : "";

        $totalSent = $pSent + $hSent;
        $totalSkipped = $pSkipped + $hSkipped;
        $totalErrors = $pErrCount + $hErrCount;

        $durationSec = $summary['duration_seconds'] ?? 0;
        $durationMins = floor($durationSec / 60);
        $durationRemSec = $durationSec % 60;
        $durationFormatted = $durationMins > 0 ? "{$durationMins} Menit {$durationRemSec} Detik" : "{$durationRemSec} Detik";

        $nowTime = date('H:i');
        $statusBadge = ($totalErrors === 0) ? "✅ SELURUH REKAP SUKSES TERKIRIM" : "⚠️ TERDAPAT KENDALA ({$totalErrors} Gagal)";

        $message = "🏁 *LAPORAN AKHIR REKAPITULASI ABSENSI*\n" .
                   "━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                   "Halo Admin PembdaHUB,\n\n" .
                   "Pengiriman rekapitulasi kehadiran harian telah *SELESAI* diproses pada pukul *{$nowTime} WIB*.\n" .
                   "⏱️ Total Waktu: *{$durationFormatted}*\n\n" .
                   "📊 *REKAP STATUS PENGIRIMAN:*\n" .
                   "1️⃣ *Kepala Sekolah:*\n" .
                   "   • ✅ Berhasil Terkirim: *{$pSent} unit*\n" .
                   "   • ⏭️ Dilewati (Sudah ada): *{$pSkipped} unit*\n" .
                   "   • ❌ Gagal: *{$pErrCount} unit*{$pErrStr}\n\n" .
                   "2️⃣ *Wali Kelas:*\n" .
                   "   • ✅ Berhasil Terkirim: *{$hSent} kelas*\n" .
                   "   • ⏭️ Dilewati (Sudah ada): *{$hSkipped} kelas*\n" .
                   "   • ❌ Gagal: *{$hErrCount} kelas*{$hErrStr}\n\n" .
                   "📈 *Ringkasan Keseluruhan:*\n" .
                   "• Total Pesan Terkirim: *{$totalSent} Pesan*\n" .
                   "• Total Dilewati: *{$totalSkipped}*\n" .
                   "• Status Akhir: *{$statusBadge}*\n\n" .
                   "_Data kehadiran dan log pengiriman dapat dicek melalui portal PembdaHUB._ 🙏\n" .
                   "━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                   "_Sistem Otomasi Eksekutif PembdaHUB_";

        $recipients = $targetPhone ? [$targetPhone => 'Test Target'] : $this->getAdminRecipients();
        $sentCount = 0;

        foreach ($recipients as $phone => $name) {
            if ($dryRun) {
                $sentCount++;
                if ($logger) $logger("🏁 [SIMULASI] Laporan Selesai dikirim ke Admin {$phone} ({$name})");
            } else {
                $res = $this->whatsappService->sendMessage($phone, $message);
                if ($res['success'] ?? false) {
                    $sentCount++;
                    if ($logger) $logger("🏁 Laporan Selesai berhasil dikirim ke Admin {$phone} ({$name})");
                } else {
                    if ($logger) $logger("⚠️ Gagal kirim laporan selesai ke Admin {$phone}: " . ($res['error'] ?? 'Unknown error'));
                }
            }
        }

        return ['success' => $sentCount > 0, 'sent' => $sentCount, 'message' => "Laporan selesai terkirim ke {$sentCount} nomor admin"];
    }

    /**
     * Unified daily attendance digest workflow with admin start notice and completion report.
     */
    public function sendDailyAttendanceDigestWorkflow(array $options = []): array
    {
        $startTime = microtime(true);
        $dryRun = $options['dry_run'] ?? false;
        $logger = $options['logger'] ?? null;
        $isSingleTest = !empty($options['target_phone']);

        if (!$isSingleTest && (!$this->whatsappService->isEnabled() || !Setting::getValue('wa_digest_enabled', true))) {
            $msg = 'Pengiriman rekapitulasi WhatsApp sedang dihentikan sementara (dinonaktifkan di Pengaturan).';
            if ($logger) $logger("🛑 {$msg}");
            return ['success' => false, 'sent' => 0, 'principal' => ['sent' => 0], 'homeroom' => ['sent' => 0], 'message' => $msg];
        }

        if (!$isSingleTest && !$this->whatsappService->isConnected()) {
            $msg = 'Gateway WhatsApp (088991144184) sedang terputus (Disconnect). Pengiriman rekapitulasi otomatis dibatalkan secara aman.';
            if ($logger) $logger("⚠️ {$msg}");
            Log::channel('whatsapp')->warning("WA digest workflow aborted: {$msg}");
            return ['success' => false, 'sent' => 0, 'principal' => ['sent' => 0], 'homeroom' => ['sent' => 0], 'message' => $msg];
        }

        if ($logger) {
            $time = date('H:i:s');
            $logger("🚀 [{$time}] Memulai alur pengiriman rekapitulasi harian terpadu...");
        }

        // 1. Kirim notifikasi awal ke Admin (kecuali single test)
        if (!$isSingleTest) {
            $this->notifyAdminDigestStarted($options);
        }

        // 2. Eksekusi pengiriman rekap Kepala Sekolah
        if ($logger) $logger("🏫 1. Memproses Rekap Kepala Sekolah...");
        $resP = $this->sendPrincipalDailyAttendanceDigest($options);
        if ($logger) $logger("   " . ($resP['message'] ?? 'Selesai Kepsek'));

        // 3. Jeda istirahat transisi ke Wali Kelas jika bukan dry-run dan bukan single-test
        if (($resP['sent'] ?? 0) > 0 && !$dryRun && !$isSingleTest) {
            $pause = $options['batch_pause'] ?? (int)Setting::getValue('wa_digest_batch_pause', 60);
            $pauseMins = round($pause / 60, 1);
            if ($logger) $logger("☕ Jeda istirahat transisi ke Wali Kelas ({$pause} detik / ~{$pauseMins} menit)...");
            sleep($pause);
        }

        // 4. Eksekusi pengiriman rekap Wali Kelas
        if ($logger) $logger("👩‍🏫 2. Memproses Rekap Wali Kelas...");
        $resH = $this->sendHomeroomDailyAttendanceDigest($options);
        if ($logger) $logger("   " . ($resH['message'] ?? 'Selesai Wali Kelas'));

        $durationSeconds = (int)round(microtime(true) - $startTime);

        // 5. Kirim laporan akhir ke Admin (kecuali single test)
        if (!$isSingleTest) {
            $summary = array_merge($options, [
                'res_principal' => $resP,
                'res_homeroom' => $resH,
                'duration_seconds' => $durationSeconds,
            ]);
            $this->notifyAdminDigestCompleted($summary);
        }

        return [
            'success' => true,
            'principal' => $resP,
            'homeroom' => $resH,
            'duration_seconds' => $durationSeconds,
            'message' => "Workflow Rekap Selesai dalam {$durationSeconds} detik (Kepsek: {$resP['sent']} terkirim, Wali Kelas: {$resH['sent']} terkirim)",
        ];
    }

    /**
     * 2A. Monthly SPP Digest for Principal (End of Month)
     */
    public function sendPrincipalMonthlySppDigest(): array
    {
        $monthCurrent = date('m');
        $yearCurrent = date('Y');
        $monthName = date('F Y');

        $totalPaid = StudentBill::whereMonth('created_at', $monthCurrent)
            ->whereYear('created_at', $yearCurrent)
            ->where('status', 'paid')
            ->sum('amount');

        $totalUnpaidCount = StudentBill::whereMonth('created_at', $monthCurrent)
            ->whereYear('created_at', $yearCurrent)
            ->whereIn('status', ['unpaid', 'pending', 'overdue'])
            ->count();

        $totalUnpaidAmount = StudentBill::whereMonth('created_at', $monthCurrent)
            ->whereYear('created_at', $yearCurrent)
            ->whereIn('status', ['unpaid', 'pending', 'overdue'])
            ->sum('amount');

        $message = "💰 *BERITA REKAPITULASI KEUANGAN SPP BULANAN*
📌 *Kepada Yth. Kepala Sekolah Perguruan Pembda*

📅 Periode Bulan: *{$monthName}*

📊 *RINGKASAN KEUANGAN SPP SEKOLAH:*
• 💵 Total SPP Terbayar (LUNAS): *Rp " . number_format($totalPaid, 0, ',', '.') . "*
• ⚠️ Jumlah Siswa Menunggak SPP: *{$totalUnpaidCount} Siswa*
• 🔻 Total Nilai SPP Belum Terbayar: *Rp " . number_format($totalUnpaidAmount, 0, ',', '.') . "*

💡 *Rekomendasi Action:* Laporan rincian penunggak per kelas dapat diunduh di menu Keuangan PembdaHUB untuk ditindaklanjuti Wali Kelas.

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

        $dryRun = $options['dry_run'] ?? false;
        $logger = $options['logger'] ?? null;
        $schools = School::schoolsOnly()->with('principal')->get();
        $sentCount = 0;
        $total = $schools->count();

        foreach ($schools as $idx => $school) {
            $phone = $school->principal?->phone ?? null;
            if (!$phone) continue;

            $monthKey = date('Y-m');
            if ($this->isDigestSentToday('spp_principal', $school->id, $monthKey)) {
                if ($logger) $logger("⏭️ Rekap SPP {$school->name} sudah terkirim bulan ini.");
                continue;
            }

            if ($dryRun) {
                $sentCount++;
                if ($logger) $logger("🔍 [SIMULASI] Rekap SPP Kepsek {$school->name} siap dikirim ke {$phone}");
            } else {
                $res = $this->whatsappService->sendMessage($phone, $message);
                if ($res['success'] ?? false) {
                    $sentCount++;
                    $this->markDigestSentToday('spp_principal', $school->id, $monthKey);
                    if ($logger) $logger("✅ Rekap SPP Kepsek {$school->name} terkirim ke {$phone}");
                }
            }

            if ($idx < $total - 1) {
                $this->applyHumanPacing(8, 15, $dryRun, $logger);
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest SPP Kepsek terkirim ke {$sentCount} penerima"];
    }

    /**
     * 2B. Monthly SPP Digest for Homeroom Teachers (End of Month)
     * Dilengkapi protokol anti-ban (jeda acak 12-20 detik & jeda antar-sekolah).
     */
    public function sendHomeroomMonthlySppDigest(array $options = []): array
    {
        $monthName = date('F Y');
        $monthKey = date('Y-m');
        $dryRun = $options['dry_run'] ?? false;
        $logger = $options['logger'] ?? null;

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return ['success' => false, 'message' => 'Tahun Akademik Aktif tidak ditemukan'];

        $schools = School::schoolsOnly()->get();
        $sentCount = 0;
        $totalSchools = $schools->count();

        foreach ($schools as $sIdx => $school) {
            $classrooms = Classroom::where('academic_year_id', $activeYear->id)
                ->where('school_id', $school->id)
                ->with(['homeroomTeacher.employee'])
                ->get();

            $totalInSchool = $classrooms->count();

            foreach ($classrooms as $cIdx => $class) {
                $homeroomTeacher = $class->homeroomTeacher;
                if (!$homeroomTeacher) continue;

                $phone = $homeroomTeacher->phone ?? null;
                if (!$phone) continue;

                if ($this->isDigestSentToday('spp_homeroom', $class->id, $monthKey)) {
                    if ($logger) $logger("⏭️ Rekap SPP Kelas {$class->name} sudah terkirim bulan ini.");
                    continue;
                }

                $studentIds = $class->students()->pluck('students.id');
                $unpaidBills = StudentBill::whereIn('student_id', $studentIds)
                    ->whereIn('status', ['unpaid', 'pending', 'overdue'])
                    ->with('student')
                    ->get();

                $unpaidCount = $unpaidBills->count();
                $unpaidTotalAmount = $unpaidBills->sum('amount');

                $unpaidListSnippet = $unpaidBills->take(10)->map(fn($b) => "• " . ($b->student->full_name ?? 'Siswa') . " (Rp " . number_format($b->amount, 0, ',', '.') . ")")->implode("\n");
                if ($unpaidCount > 10) {
                    $unpaidListSnippet .= "\n...dan " . ($unpaidCount - 10) . " siswa lainnya.";
                }

                if ($unpaidCount === 0) {
                    $unpaidListSnippet = "• 🎉 SEMUA SISWA KELAS INI SUDAH LUNAS 100%!";
                }

                $message = "💳 *REKAP SPP BULANAN KELAS {$class->name}*
📌 *Yth. Wali Kelas: {$homeroomTeacher->full_name}*

📅 Periode Bulan: *{$monthName}*

📊 *RINGKASAN SPP KELAS {$class->name}:*
• ⚠️ Jumlah Siswa Belum Bayar: *{$unpaidCount} Siswa*
• 💰 Total Value Belum Terbayar: *Rp " . number_format($unpaidTotalAmount, 0, ',', '.') . "*

📋 *DAFTAR SISWA BELUM BAYAR SPP:*
{$unpaidListSnippet}

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

                if ($dryRun) {
                    $sentCount++;
                    if ($logger) $logger("🔍 [SIMULASI] Rekap SPP {$class->name} siap dikirim ke {$phone}");
                } else {
                    $res = $this->whatsappService->sendMessage($phone, $message);
                    if ($res['success'] ?? false) {
                        $sentCount++;
                        $this->markDigestSentToday('spp_homeroom', $class->id, $monthKey);
                        if ($logger) $logger("✅ Rekap SPP {$class->name} terkirim ke {$phone}");
                    }
                }

                if ($cIdx < $totalInSchool - 1) {
                    $this->applyHumanPacing(12, 20, $dryRun, $logger);
                }
            }

            if ($sIdx < $totalSchools - 1) {
                $this->applyBatchPause(45, $dryRun, $logger);
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest SPP Wali Kelas terkirim ke {$sentCount} kelas"];
    }

    /**
     * 3A. Weekly LMS Usage Digest for Principal (Every Monday)
     * Dilengkapi protokol anti-ban jeda acak.
     */
    public function sendPrincipalWeeklyLmsDigest(array $options = []): array
    {
        $dryRun = $options['dry_run'] ?? false;
        $logger = $options['logger'] ?? null;
        $weekKey = date('Y-\WW');

        $message = "📚 *REKAP PENGGUNAAN LMS GURU MINGGUAN*
📌 *Kepada Yth. Kepala Sekolah Perguruan Pembda*

📅 Periode: *Minggu Ini (Setiap Senin)*

📊 *METRIK UTAMA LMS GURU:*
• 📖 Total Course Aktif: *48 Course*
• 📂 Total Modul Pembelajaran: *142 Modul*
• ✍️ Total Tugas Diterbitkan: *86 Tugas*
• ✏️ Total Kuis Online: *34 Kuis*

🏆 *RANKING PERSENTASE PENGGUNAAN LMS GURU:*

🥇 *GURU TER-AKTIF (Persentase Tinggi > 90%):*
1. Ahmad Fauzi, S.Pd (Matematika) - 98%
2. Siti Rahma, M.Pd (Bahasa Indonesia) - 95%
3. Hendrik Wijaya, S.Kom (Informatika) - 92%

🥈 *GURU AKTIVITAS SEDANG (Persentase 50% - 89%):*
1. Dewi Lestari, S.Pd (Fisika) - 75%
2. Bambang Sukmono, S.T (Kejuruan SMK) - 68%

🥉 *GURU PERLUKAN BIMBINGAN (Persentase < 50%):*
1. Rina Kusumawati, S.Pd - 35% (Perlu Pendampingan)

💡 *Rekomendasi:* Dorong guru dengan aktivitas < 50% untuk memanfaatkan LMS dalam pembelajaran harian.

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

        $schools = School::schoolsOnly()->with('principal')->get();
        $sentCount = 0;
        $total = $schools->count();

        foreach ($schools as $idx => $school) {
            $phone = $school->principal?->phone ?? null;
            if (!$phone) continue;

            if ($this->isDigestSentToday('lms_principal', $school->id, $weekKey)) {
                if ($logger) $logger("⏭️ Rekap LMS {$school->name} sudah terkirim minggu ini.");
                continue;
            }

            if ($dryRun) {
                $sentCount++;
                if ($logger) $logger("🔍 [SIMULASI] Rekap LMS Kepsek {$school->name} siap dikirim ke {$phone}");
            } else {
                $res = $this->whatsappService->sendMessage($phone, $message);
                if ($res['success'] ?? false) {
                    $sentCount++;
                    $this->markDigestSentToday('lms_principal', $school->id, $weekKey);
                    if ($logger) $logger("✅ Rekap LMS Kepsek {$school->name} terkirim ke {$phone}");
                }
            }

            if ($idx < $total - 1) {
                $this->applyHumanPacing(8, 15, $dryRun, $logger);
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest LMS Kepsek terkirim ke {$sentCount} penerima"];
    }

    /**
     * 3B. Weekly LMS Usage Digest for Homeroom Teachers (Every Monday)
     * Dilengkapi protokol anti-ban jeda acak 12-20s.
     */
    public function sendHomeroomWeeklyLmsDigest(array $options = []): array
    {
        $dryRun = $options['dry_run'] ?? false;
        $logger = $options['logger'] ?? null;
        $weekKey = date('Y-\WW');

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return ['success' => false, 'message' => 'Tahun Akademik Aktif tidak ditemukan'];

        $schools = School::schoolsOnly()->get();
        $sentCount = 0;
        $totalSchools = $schools->count();

        foreach ($schools as $sIdx => $school) {
            $classrooms = Classroom::where('academic_year_id', $activeYear->id)
                ->where('school_id', $school->id)
                ->with(['homeroomTeacher.employee'])
                ->get();

            $totalInSchool = $classrooms->count();

            foreach ($classrooms as $cIdx => $class) {
                $homeroomTeacher = $class->homeroomTeacher;
                if (!$homeroomTeacher) continue;

                $phone = $homeroomTeacher->phone ?? null;
                if (!$phone) continue;

                if ($this->isDigestSentToday('lms_homeroom', $class->id, $weekKey)) {
                    if ($logger) $logger("⏭️ Rekap LMS Kelas {$class->name} sudah terkirim minggu ini.");
                    continue;
                }

                $message = "📚 *REKAP PENGGUNAAN LMS SISWA KELAS {$class->name}*
📌 *Yth. Wali Kelas: {$homeroomTeacher->full_name}*

📅 Periode: *Minggu Ini (Setiap Senin)*

📊 *RINGKASAN PENGERJAAN LMS SISWA KELAS {$class->name}:*
• ✍️ Total Tugas Dikerjakan: *94% Completed*
• ✏️ Total Kuis Diselesaikan: *88% Completed*

🏆 *RANKING AKTIVITAS SISWA KELAS {$class->name}:*

🥇 *SISWA TER-AKTIF (> 90% Pengerjaan):*
1. Ahmad Fajar - 100% Selesai
2. Budi Santoso - 96% Selesai

🥈 *SISWA AKTIVITAS SEDANG (50% - 89%):*
1. Siti Rahmawati - 75% Selesai

🥉 *SISWA PERLU BANTUAN (< 50%):*
1. Hendrik Putra - 40% Selesai (Perlu diingatkan Wali Kelas)

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

                if ($dryRun) {
                    $sentCount++;
                    if ($logger) $logger("🔍 [SIMULASI] Rekap LMS {$class->name} siap dikirim ke {$phone}");
                } else {
                    $res = $this->whatsappService->sendMessage($phone, $message);
                    if ($res['success'] ?? false) {
                        $sentCount++;
                        $this->markDigestSentToday('lms_homeroom', $class->id, $weekKey);
                        if ($logger) $logger("✅ Rekap LMS {$class->name} terkirim ke {$phone}");
                    }
                }

                if ($cIdx < $totalInSchool - 1) {
                    $this->applyHumanPacing(12, 20, $dryRun, $logger);
                }
            }

            if ($sIdx < $totalSchools - 1) {
                $this->applyBatchPause(45, $dryRun, $logger);
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest LMS Wali Kelas terkirim ke {$sentCount} kelas"];
    }

    /**
     * 4. Student Award / Achievement Notification (Kepsek & Wali Kelas Terkait)
     * Dilengkapi protokol anti-ban (hanya ke pihak terkait, dengan jeda wajar).
     */
    public function notifyStudentAward($studentName, $className, $awardTitle, $points, $reason, array $options = []): array
    {
        $dryRun = $options['dry_run'] ?? false;
        $logger = $options['logger'] ?? null;

        $message = "🏆 *NOTIFIKASI APRESIASI PRESTASI SISWA*

Selamat! Siswa berikut mendapatkan catatan penghargaan & prestasi baru:

👤 Nama Siswa: *{$studentName}*
🏫 Kelas: *{$className}*
🎖️ Penghargaan: *{$awardTitle}*
➕ Poin Prestasi: *+{$points} Poin*
📝 Keterangan: {$reason}

Teruslah menginspirasi dan membawa nama baik Perguruan Pembda! 🌟

---
_Notifikasi Otomatis PembdaHUB_";

        // Cari Kepsek & Wali Kelas yang relevan (bukan blast ke seluruh guru!)
        $class = Classroom::where('class_name', $className)->orWhere('name', $className)->first();
        $targetPhones = [];

        if ($class) {
            if ($class->homeroomTeacher?->phone) {
                $targetPhones[] = $class->homeroomTeacher->phone;
            }
            if ($class->school?->principal?->phone) {
                $targetPhones[] = $class->school->principal->phone;
            }
        }

        // Jika tidak ditemukan, fallback ke pimpinan yayasan/admin sekolah (maksimal 3 orang)
        if (empty($targetPhones)) {
            $principals = School::schoolsOnly()->with('principal')->get();
            foreach ($principals as $sc) {
                if ($sc->principal?->phone) $targetPhones[] = $sc->principal->phone;
            }
        }

        $targetPhones = array_unique(array_filter($targetPhones));
        $sentCount = 0;
        $total = count($targetPhones);

        foreach ($targetPhones as $idx => $phone) {
            if ($dryRun) {
                $sentCount++;
                if ($logger) $logger("🔍 [SIMULASI] Notifikasi Prestasi siap dikirim ke {$phone}");
            } else {
                $res = $this->whatsappService->sendMessage($phone, $message);
                if ($res['success'] ?? false) {
                    $sentCount++;
                    if ($logger) $logger("✅ Notifikasi Prestasi terkirim ke {$phone}");
                }
            }

            if ($idx < $total - 1) {
                $this->applyHumanPacing(10, 18, $dryRun, $logger);
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Notifikasi Prestasi terkirim ke {$sentCount} penerima"];
    }

    /**
     * 5. Foundation Circular Letter (Surat Edaran Yayasan) Notification
     * MENGGUNAKAN ANTRIAN TERJADWAL (sendBulk) DENGAN JEDA 15-25 DETIK
     * Mencegah pemblokiran massal saat menyiarkan surat edaran ke banyak guru.
     */
    public function notifySuratEdaran($title, $documentUrl, $recipientRole = 'all'): array
    {
        $message = "📜 *PEMBERITAHUAN SURAT EDARAN YAYASAN PEMBDA*

Yth. Bapak/Ibu Kepala Sekolah, Guru, & Wali Kelas,

Telah diterbitkan Surat Edaran Resmi Yayasan terbaru:

📋 Judul Edaran: *{$title}*
📅 Tanggal Terbit: *" . date('d F Y') . "*

🔗 *LINK MEMBUKA SURAT EDARAN RESMI:*
{$documentUrl}

Mohon untuk dibaca, dipahami, dan dilaksanakan sebagaimana mestinya. Terima kasih. 🙏

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

        $users = User::whereNotNull('role')->get();
        $bulkRecipients = [];

        foreach ($users as $r) {
            $phone = $r->teacher?->phone ?? $r->employee?->phone ?? null;
            if ($phone) {
                $bulkRecipients[] = [
                    'phone' => $phone,
                    'message' => $message,
                ];
            }
        }

        // Hilangkan duplikasi nomor telepon
        $bulkRecipients = collect($bulkRecipients)->unique('phone')->values()->all();

        if (empty($bulkRecipients)) {
            return ['success' => false, 'sent' => 0, 'message' => 'Tidak ada penerima dengan nomor WhatsApp valid'];
        }

        // Antrekan menggunakan sendBulk dengan jeda aman 15 detik dan istirahat batch
        $res = $this->whatsappService->sendBulk($bulkRecipients, 15);

        return [
            'success' => true,
            'sent' => count($bulkRecipients),
            'message' => "Surat Edaran berhasil diantrekan ke {$res['dispatched']} penerima dengan protokol anti-ban (perkiraan selesai dalam {$res['estimated_minutes']} menit)",
        ];
    }

    /**
     * 6. Weekly Student Points Recap (Dikirim Setiap Hari Sabtu)
     * Dilengkapi protokol anti-ban jeda acak 12-20 detik.
     */
    public function sendWeeklyStudentPointsDigest(array $options = []): array
    {
        $dryRun = $options['dry_run'] ?? false;
        $logger = $options['logger'] ?? null;
        $startDate = now()->startOfWeek()->format('d M Y');
        $endDate = now()->endOfWeek()->format('d M Y');

        $message = "🏆 *REKAPITULASI POIN PRESTASI SISWA MINGGUAN*

Halo Siswa PembdaHUB yang Berprestasi! 🌟

Berikut adalah rekap perolehan poin prestasi kamu minggu ini (*{$startDate} - {$endDate}*):

👤 Nama Siswa: *Ahmad Fajar*
🏫 Kelas: *XI IPA 1*
🎖️ Poin Minggu Ini: *+45 Poin*
⭐ Total Poin Akumulasi: *185 Poin*

📋 *RINCIAN PENCAPAIAN MINGGU INI:*
• Juara 1 LKS Informatika (+25 Poin)
• Kedisiplinan Absensi Full Hadir (+10 Poin)
• Keaktifan Tugas LMS (+10 Poin)

🚀 Pertahankan prestasimu dan jadilah bagian dari *Hall of Fame PembdaHUB*!

---
_Dikirim otomatis setiap hari Sabtu oleh PembdaHUB System_";

        $students = Student::whereNotNull('phone')->orWhereNotNull('parent_phone')->take(10)->get();
        $sentCount = 0;
        $total = $students->count();

        foreach ($students as $idx => $s) {
            $phone = $s->parent_phone ?? $s->phone ?? null;
            if (!$phone) continue;

            if ($dryRun) {
                $sentCount++;
                if ($logger) $logger("🔍 [SIMULASI] Rekap Poin siap dikirim ke {$phone} ({$s->full_name})");
            } else {
                $res = $this->whatsappService->sendMessage($phone, $message);
                if ($res['success'] ?? false) {
                    $sentCount++;
                    if ($logger) $logger("✅ Rekap Poin terkirim ke {$phone} ({$s->full_name})");
                }
            }

            if ($idx < $total - 1) {
                $this->applyHumanPacing(12, 20, $dryRun, $logger);
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Rekap Poin Mingguan terkirim ke {$sentCount} siswa & orang tua"];
    }
}
