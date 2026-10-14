<?php

namespace App\Services;

use App\Models\AlumniDirectory;
use App\Models\EducationalCalendar;
use App\Models\FinalProject;
use App\Models\News;
use App\Models\StudentAchievement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RunningTextService
{
    public const INTERRUPT_DURATION_SECONDS = 5;
    public const CACHE_KEY_INTERRUPT = 'running_text_interrupt_data';
    public const BUFFER_FILE = 'running_text_interrupt.json';

    /**
     * Catat event selaan / interupsi tap RFID (Masuk atau Pulang)
     */
    public static function recordInterrupt(
        string $name,
        string $subtext = '',
        string $action = 'MASUK',
        string $timeStr = '',
        string $statusStr = 'Tepat Waktu'
    ): array {
        $now = time();
        $timeStr = $timeStr ?: date('H:i');

        // Bersihkan nama agar muat dan rapi di display
        $cleanName = trim(preg_replace('/\s+/', ' ', $name));
        $cleanSubtext = trim(preg_replace('/\s+/', ' ', $subtext));
        
        $isMasuk = strtoupper($action) === 'MASUK' || stripos($action, 'IN') !== false;
        $isTerlambat = stripos($statusStr, 'terlambat') !== false;
        
        $statusLabel = $isMasuk ? ($isTerlambat ? 'TERLAMBAT' : 'HADIR') : 'PULANG';
        $statusColor = $isMasuk ? ($isTerlambat ? 'red' : 'green') : 'cyan';

        // BARIS 1: NAMA BESAR (Mengisi area atas layar secara penuh)
        $line1 = strtoupper($cleanName);

        // BARIS 2: KELAS/UNIT • JAM TAP MASUK • KETERANGAN (HADIR / TERLAMBAT)
        $subtextFormatted = $cleanSubtext ?: 'PEMBDA';
        $line2 = sprintf("%s  •  %s WIB  •  %s", $subtextFormatted, $timeStr, $statusLabel);

        // Format Teks Tunggal Gabungan (untuk fallback raw marquee)
        $displayText = sprintf("%s   |   %s", $line1, $line2);

        $data = [
            'name'             => $cleanName,
            'subtext'          => $cleanSubtext,
            'action'           => $isMasuk ? 'MASUK' : 'PULANG',
            'time'             => $timeStr,
            'status'           => $statusLabel,
            'status_color'     => $statusColor,
            'line1'            => $line1,
            'line2'            => $line2,
            'display_text'     => $displayText,
            'timestamp'        => $now,
            'duration_seconds' => self::INTERRUPT_DURATION_SECONDS,
            'expires_at'       => $now + self::INTERRUPT_DURATION_SECONDS,
        ];

        try {
            Cache::put(self::CACHE_KEY_INTERRUPT, $data, self::INTERRUPT_DURATION_SECONDS + 2);
            $filePath = storage_path('app/' . self::BUFFER_FILE);
            @file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            Log::warning('RunningTextService recordInterrupt warning: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Dapatkan data interupsi yang sedang aktif saat ini (jika ada tap dalam 5 detik terakhir)
     */
    public static function getActiveInterrupt(): ?array
    {
        $data = Cache::get(self::CACHE_KEY_INTERRUPT);

        if (!$data) {
            $filePath = storage_path('app/' . self::BUFFER_FILE);
            if (file_exists($filePath)) {
                $raw = @file_get_contents($filePath);
                $json = @json_decode($raw, true);
                if (is_array($json) && !empty($json['timestamp'])) {
                    $data = $json;
                }
            }
        }

        if (is_array($data) && !empty($data['timestamp'])) {
            $diff = time() - $data['timestamp'];
            if ($diff >= 0 && $diff <= ($data['duration_seconds'] ?? self::INTERRUPT_DURATION_SECONDS)) {
                $data['remaining_seconds'] = ($data['duration_seconds'] ?? self::INTERRUPT_DURATION_SECONDS) - $diff;
                return $data;
            }
        }

        return null;
    }

    /**
     * Dapatkan pengumuman darurat / kilat jika sedang aktif
     */
    public static function getEmergencyBroadcast(): ?array
    {
        $data = Cache::get('running_text_emergency');
        if (!$data) {
            $val = \App\Models\Setting::getValue('running_text_emergency', null);
            if (is_string($val)) {
                $data = json_decode($val, true);
            } elseif (is_array($val)) {
                $data = $val;
            }
        }

        if (is_array($data) && !empty($data['text']) && ($data['expires_at'] ?? 0) > time()) {
            $data['remaining_minutes'] = max(1, (int) round(($data['expires_at'] - time()) / 60));
            return $data;
        }

        return null;
    }

    /**
     * Pasang pengumuman darurat / kilat
     */
    public static function setEmergencyBroadcast(string $text, int $minutes = 15): array
    {
        $cleanText = trim(preg_replace('/\s+/', ' ', $text));
        $expiresAt = time() + ($minutes * 60);
        $data = [
            'text'             => $cleanText,
            'created_at'       => time(),
            'expires_at'       => $expiresAt,
            'duration_minutes' => $minutes,
        ];

        Cache::put('running_text_emergency', $data, $minutes * 60);
        \App\Models\Setting::setValue('running_text_emergency', json_encode($data), 'string', 'running_text');

        return $data;
    }

    /**
     * Hapus pengumuman darurat
     */
    public static function clearEmergencyBroadcast(): void
    {
        Cache::forget('running_text_emergency');
        \App\Models\Setting::setValue('running_text_emergency', '', 'string', 'running_text');
    }

    /**
     * Himpun seluruh 7 kategori playlist berjalan secara normal (Background Continuous Loop)
     */
    public static function getContinuousPlaylist(int $limitPerCategory = 3, ?int $schoolId = null): array
    {
        $playlist = [];

        // ---------------------------------------------------------------------
        // 0. PENGUMUMAN DARURAT / KILAT (JIKA AKTIF, SELALU DI URUTAN PERTAMA)
        // ---------------------------------------------------------------------
        $emergency = self::getEmergencyBroadcast();
        if ($emergency) {
            $playlist[] = [
                'category' => 'emergency',
                'icon'     => '🚨',
                'tag'      => 'PENGUMUMAN MENDESAK',
                'text'     => sprintf("[ 🚨 PENGUMUMAN MENDESAK : %s ]", $emergency['text']),
            ];
        }

        // ---------------------------------------------------------------------
        // 1. BERITA & PENGUMUMAN SEKOLAH
        // ---------------------------------------------------------------------
        try {
            $newsList = News::published()
                ->whereIn('category', ['pengumuman', 'kegiatan'])
                ->latest()
                ->take($limitPerCategory)
                ->get();

            foreach ($newsList as $item) {
                $cleanTitle = trim(preg_replace('/\s+/', ' ', $item->title));
                $cleanExcerpt = $item->excerpt ? trim(preg_replace('/\s+/', ' ', $item->excerpt)) : '';
                $textBody = $cleanExcerpt ? "{$cleanTitle} - {$cleanExcerpt}" : $cleanTitle;

                $playlist[] = [
                    'category' => 'pengumuman',
                    'icon'     => '📢',
                    'tag'      => 'PENGUMUMAN',
                    'text'     => sprintf("[ 📢 PENGUMUMAN : %s ]", $textBody),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('RunningText news fetch error: ' . $e->getMessage());
        }

        // ---------------------------------------------------------------------
        // 2. PRESTASI SISWA (JUARA LKS, O2SN, FLS2N, DLL)
        // ---------------------------------------------------------------------
        try {
            $achievementsQuery = StudentAchievement::with(['student.school'])
                ->latest('achievement_date');

            if ($schoolId) {
                $achievementsQuery->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
            }

            $achievements = $achievementsQuery->take($limitPerCategory)->get();
            foreach ($achievements as $ach) {
                $studentName = $ach->student ? $ach->student->full_name : 'Siswa';
                $schoolName = $ach->student?->school?->name ?? 'PEMBDA';
                $rankStr = ($ach->rank_label && $ach->rank_label !== '-') ? $ach->rank_label : 'Juara';
                $levelStr = ($ach->level_label && $ach->level_label !== '-') ? 'Tingkat ' . $ach->level_label : '';
                $cleanTitle = trim(preg_replace('/\s+/', ' ', $ach->title));

                $info = trim("{$rankStr} {$cleanTitle} {$levelStr}");
                $playlist[] = [
                    'category' => 'prestasi',
                    'icon'     => '🏆',
                    'tag'      => 'PRESTASI SISWA',
                    'text'     => sprintf("[ 🏆 PRESTASI SISWA - %s (%s) : %s ]", $studentName, $schoolName, $info),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('RunningText achievement fetch error: ' . $e->getMessage());
        }

        // ---------------------------------------------------------------------
        // 3. AGENDA KEGIATAN HARI INI (RAPAT, SUPERVISI, EKSKUL, DLL)
        // ---------------------------------------------------------------------
        try {
            $today = now()->toDateString();
            $calendarQuery = EducationalCalendar::with('school')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today);

            if ($schoolId) {
                $calendarQuery->where(function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->orWhereNull('school_id');
                });
            }

            $todayAgendas = $calendarQuery->take($limitPerCategory)->get();

            // Jika hari ini tidak ada agenda khusus, ambil agenda terdekat dalam 7 hari ke depan
            if ($todayAgendas->isEmpty()) {
                $upcomingQuery = EducationalCalendar::with('school')
                    ->where('start_date', '>', $today)
                    ->where('start_date', '<=', now()->addDays(7)->toDateString())
                    ->orderBy('start_date');

                if ($schoolId) {
                    $upcomingQuery->where(function ($q) use ($schoolId) {
                        $q->where('school_id', $schoolId)->orWhereNull('school_id');
                    });
                }
                $upcomingAgendas = $upcomingQuery->take(2)->get();
                foreach ($upcomingAgendas as $agenda) {
                    $schoolLabel = $agenda->school ? $agenda->school->name : 'Yayasan';
                    $dateLabel = $agenda->start_date ? $agenda->start_date->translatedFormat('d M') : '';
                    $cleanTitle = trim(preg_replace('/\s+/', ' ', $agenda->title));

                    $playlist[] = [
                        'category' => 'agenda',
                        'icon'     => '📅',
                        'tag'      => 'AGENDA MENDATANG',
                        'text'     => sprintf("[ 📅 AGENDA MENDATANG (%s - %s) : %s ]", $dateLabel, $schoolLabel, $cleanTitle),
                    ];
                }
            } else {
                foreach ($todayAgendas as $agenda) {
                    $schoolLabel = $agenda->school ? $agenda->school->name : 'Yayasan';
                    $cleanTitle = trim(preg_replace('/\s+/', ' ', $agenda->title));

                    $playlist[] = [
                        'category' => 'agenda',
                        'icon'     => '📅',
                        'tag'      => 'AGENDA HARI INI',
                        'text'     => sprintf("[ 📅 AGENDA HARI INI (%s) : %s ]", $schoolLabel, $cleanTitle),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('RunningText calendar fetch error: ' . $e->getMessage());
        }

        // ---------------------------------------------------------------------
        // 4. POJOK LITERASI & MOTIVASI (QUOTES INSPIRATIF HARIAN)
        // ---------------------------------------------------------------------
        try {
            // Cek apakah ada motivasi khusus yang diinputkan di tabel News (kategori motivasi)
            $customMotivation = News::published()
                ->where('category', 'motivasi')
                ->latest()
                ->first();

            if ($customMotivation) {
                $cleanQuote = trim(preg_replace('/\s+/', ' ', $customMotivation->title));
                $playlist[] = [
                    'category' => 'motivasi',
                    'icon'     => '💡',
                    'tag'      => 'POJOK MOTIVASI',
                    'text'     => sprintf("[ 💡 MOTIVASI HARI INI : \"%s\" ]", $cleanQuote),
                ];
            } else {
                // Bank quote inspiratif pendidikan yang dirotasi otomatis berdasarkan hari
                $bankQuotes = [
                    "Disiplin adalah jembatan antara cita-cita dan pencapaian.",
                    "Pendidikan adalah tiket menuju masa depan. Hari esok dimiliki oleh mereka yang mempersiapkannya hari ini.",
                    "Kejujuran dan kerja keras adalah pondasi karakter utama generasi emas Pembda.",
                    "Tiada hari tanpa belajar, tiada langkah tanpa karya nyata.",
                    "Gantungkan cita-citamu setinggi bintang di langit, melangkahlah dengan tekun dan rendah hati.",
                    "Ilmu pengetahuan memberi kekuatan, namun integritas akhlak memberi kehormatan.",
                    "Kegagalan kemarin adalah pelajaran berharga untuk meraih kesuksesan hari ini."
                ];
                $dayIndex = (int) date('z') % count($bankQuotes);
                $selectedQuote = $bankQuotes[$dayIndex];

                $playlist[] = [
                    'category' => 'motivasi',
                    'icon'     => '💡',
                    'tag'      => 'POJOK MOTIVASI',
                    'text'     => sprintf("[ 💡 MOTIVASI HARI INI : \"%s\" ]", $selectedQuote),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('RunningText motivation fetch error: ' . $e->getMessage());
        }

        // ---------------------------------------------------------------------
        // 5. JUDUL PENELITIAN AKHIR (KARYA ILMIAH SISWA SMA)
        // ---------------------------------------------------------------------
        try {
            $researchQuery = FinalProject::with(['student.school'])
                ->where('type', 'penelitian_ilmiah')
                ->whereNotIn('status', ['rejected', 'draft'])
                ->latest();

            if ($schoolId) {
                $researchQuery->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
            }

            $researchList = $researchQuery->take($limitPerCategory)->get();
            foreach ($researchList as $item) {
                $studentName = $item->student ? $item->student->full_name : 'Siswa';
                $cleanTitle = trim(preg_replace('/\s+/', ' ', $item->title));

                $playlist[] = [
                    'category' => 'penelitian',
                    'icon'     => '🔬',
                    'tag'      => 'PENELITIAN SMA',
                    'text'     => sprintf("[ 🔬 PENELITIAN ILMIAH SMA : \"%s\" oleh %s ]", $cleanTitle, $studentName),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('RunningText research fetch error: ' . $e->getMessage());
        }

        // ---------------------------------------------------------------------
        // 6. JUDUL PROJECT KEJURUAN SMK
        // ---------------------------------------------------------------------
        try {
            $projectQuery = FinalProject::with(['student.school'])
                ->where('type', 'project_akhir')
                ->whereNotIn('status', ['rejected', 'draft'])
                ->latest();

            if ($schoolId) {
                $projectQuery->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
            }

            $projectList = $projectQuery->take($limitPerCategory)->get();
            foreach ($projectList as $item) {
                $studentName = $item->student ? $item->student->full_name : 'Siswa';
                $cleanTitle = trim(preg_replace('/\s+/', ' ', $item->title));

                $playlist[] = [
                    'category' => 'project',
                    'icon'     => '🛠️',
                    'tag'      => 'PROJECT SMK',
                    'text'     => sprintf("[ 🛠️ PROJECT KEJURUAN SMK : \"%s\" oleh %s ]", $cleanTitle, $studentName),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('RunningText vocational project fetch error: ' . $e->getMessage());
        }

        // ---------------------------------------------------------------------
        // 7. PESAN & KESAN ALUMNI
        // ---------------------------------------------------------------------
        try {
            $alumniQuery = AlumniDirectory::with('school')
                ->where('is_approved', true)
                ->whereNotNull('message')
                ->where('message', '!=', '')
                ->latest();

            if ($schoolId) {
                $alumniQuery->where('school_id', $schoolId);
            }

            $alumniList = $alumniQuery->take($limitPerCategory)->get();
            foreach ($alumniList as $alumni) {
                $schoolName = $alumni->school ? $alumni->school->name : 'PEMBDA';
                $cleanMsg = trim(preg_replace('/\s+/', ' ', $alumni->message));

                $playlist[] = [
                    'category' => 'alumni',
                    'icon'     => '🎓',
                    'tag'      => 'PESAN ALUMNI',
                    'text'     => sprintf(
                        "[ 🎓 KATA ALUMNI - %s (%s '%s) : \"%s\" ]",
                        $alumni->full_name,
                        $schoolName,
                        $alumni->graduation_year,
                        $cleanMsg
                    ),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('RunningText alumni fetch error: ' . $e->getMessage());
        }

        // Fallback default message jika semua kategori kosong
        if (empty($playlist)) {
            $playlist[] = [
                'category' => 'default',
                'icon'     => '🏫',
                'tag'      => 'PEMBDA',
                'text'     => "[ *** SELAMAT DATANG DI PERGURUAN PEMBDA NIAS *** ]",
            ];
        }

        return $playlist;
    }
}
