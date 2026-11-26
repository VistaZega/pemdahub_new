<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiStudentAssistantService
{
    protected $gemini;

    const DAILY_LIMIT = 30;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Get configurable daily prompt limit from settings (fallback to DAILY_LIMIT).
     */
    public function getDailyLimit(): int
    {
        $configured = (int) (\App\Models\Setting::getValue('ai_student_daily_limit') ?? self::DAILY_LIMIT);
        return $configured > 0 ? $configured : self::DAILY_LIMIT;
    }

    /**
     * Check if student has reached daily usage limit.
     */
    public function getUsageInfo(int $studentId): array
    {
        $limit = $this->getDailyLimit();
        $today = now()->toDateString();
        $usage = DB::table('ai_student_usages')
            ->where('student_id', $studentId)
            ->where('usage_date', $today)
            ->first();

        $used = $usage ? $usage->prompt_count : 0;
        $remaining = max(0, $limit - $used);

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => $remaining,
            'is_exceeded' => $used >= $limit
        ];
    }

    /**
     * Increment usage count for student.
     */
    public function incrementUsage(int $studentId): void
    {
        $today = now()->toDateString();
        $existing = DB::table('ai_student_usages')
            ->where('student_id', $studentId)
            ->where('usage_date', $today)
            ->first();

        if ($existing) {
            DB::table('ai_student_usages')
                ->where('id', $existing->id)
                ->increment('prompt_count');
        } else {
            DB::table('ai_student_usages')->insert([
                'student_id' => $studentId,
                'usage_date' => $today,
                'prompt_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Generate response from AI for student.
     */
    public function generateResponse(Student $student, string $prompt, string $mode = 'tutor', array $chatHistory = [], ?string $imageDataUri = null): array
    {
        $usage = $this->getUsageInfo($student->id);
        if ($usage['is_exceeded']) {
            return [
                'success' => false,
                'message' => 'Batas kuota harian penggunaan Pembda AI (' . $usage['limit'] . ' pertanyaan/hari) telah tercapai. Kuota Anda akan terisi ulang besok.',
                'is_quota_error' => true
            ];
        }

        // Safety check for sensitive crisis keywords
        $crisisNotice = $this->detectCrisisKeywords($prompt);
        if ($crisisNotice) {
            $this->escalateCrisisAlert($student, $prompt);
        }

        $messages = $this->buildChatMessages($student, $prompt, $mode, $chatHistory, $imageDataUri);

        try {
            $aiResult = $this->gemini->generateChat($messages);

            if (empty(trim((string)$aiResult))) {
                $aiResult = $this->getEmptyFallbackMessage($prompt);
            }

            // Increment usage after successful call
            $this->incrementUsage($student->id);

            // Append crisis notice if keyword triggered
            if ($crisisNotice) {
                $aiResult .= "\n\n---\n" . $crisisNotice;
            }

            return [
                'success' => true,
                'message' => $aiResult,
                'remaining_quota' => max(0, $usage['remaining'] - 1),
                'crisis_triggered' => (bool) $crisisNotice,
            ];
        } catch (\Exception $e) {
            Log::error('AiStudentAssistantService error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kendala saat menghubungkan ke Pembda AI. Silakan coba lagi nanti.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Stream AI response chunk-by-chunk for real-time SSE output.
     */
    public function streamResponse(Student $student, string $prompt, string $mode, array $chatHistory, ?string $imageDataUri, callable $onChunk): array
    {
        $usage = $this->getUsageInfo($student->id);
        if ($usage['is_exceeded']) {
            return [
                'success' => false,
                'message' => 'Batas kuota harian penggunaan Pembda AI (' . $usage['limit'] . ' pertanyaan/hari) telah tercapai. Kuota Anda akan terisi ulang besok.',
                'is_quota_error' => true
            ];
        }

        $crisisNotice = $this->detectCrisisKeywords($prompt);
        if ($crisisNotice) {
            $this->escalateCrisisAlert($student, $prompt);
        }

        $messages = $this->buildChatMessages($student, $prompt, $mode, $chatHistory, $imageDataUri);

        try {
            $aiResult = $this->gemini->streamChat($messages, $onChunk);

            if (empty(trim((string)$aiResult))) {
                $aiResult = $this->getEmptyFallbackMessage($prompt);
                $onChunk($aiResult);
            }

            $this->incrementUsage($student->id);

            if ($crisisNotice) {
                $suffix = "\n\n---\n" . $crisisNotice;
                $aiResult .= $suffix;
                $onChunk($suffix);
            }

            return [
                'success' => true,
                'message' => $aiResult,
                'remaining_quota' => max(0, $usage['remaining'] - 1),
                'crisis_triggered' => (bool) $crisisNotice,
            ];
        } catch (\Exception $e) {
            Log::error('AiStudentAssistantService stream error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kendala saat menghubungkan ke Pembda AI. Silakan coba lagi nanti.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Default helpful message when AI returns empty text.
     */
    protected function getEmptyFallbackMessage(string $prompt): string
    {
        return "### 💡 Penjelasan Pembda AI Studio\n\n"
            . "Terima kasih telah menghubungi Pembda AI! Pertanyaan atau instruksi yang Anda berikan (*\"" . htmlspecialchars($prompt) . "\"*) perlu dibuat lebih spesifik agar Pembda AI dapat memberikan jawaban langkah demi langkah yang tepat.\n\n"
            . "* **Contoh Soal Matematika**: *\"Bantu saya menghitung luas permukaan tabung jika r = 7 cm dan t = 10 cm\"*\n"
            . "* **Contoh Konsultasi Karir**: *\"Saran memilih jurusan kuliah teknik vs ekonomi bagi siswa SMA/SMK\"*\n"
            . "* **Contoh Info Sekolah**: *\"Di mana saya bisa mengecek transkrip nilai rapor dan jadwal CBT online?\"*\n\n"
            . "💡 *Silakan tuliskan pertanyaan lengkap Anda pada kolom pesan di bawah ini!*";
    }

    /**
     * Log and optionally notify BK/Admin when crisis keywords are detected.
     */
    protected function escalateCrisisAlert(Student $student, string $prompt): void
    {
        $className = $student->classroom->class_name ?? '-';
        $schoolName = $student->school->name ?? '-';
        Log::warning("AI_CRISIS_ALERT: Siswa {$student->full_name} (ID: {$student->id}, Kelas: {$className}, Unit: {$schoolName}) memicu kata kunci sensitif di Pembda AI.");

        try {
            $waPhone = \App\Models\Setting::getValue('wa_alert_phone');
            $waEnabled = (bool) \App\Models\Setting::getValue('wa_enabled');
            if ($waEnabled && !empty($waPhone)) {
                $waService = app(WhatsAppService::class);
                $alertText = "⚠️ *PERINGATAN DINI BK - PEMBDA AI*\n\n"
                    . "Siswa terdeteksi mengetik kata kunci sensitif/krisis emosional:\n"
                    . "• Nama: *{$student->full_name}*\n"
                    . "• Kelas: {$className} ({$schoolName})\n"
                    . "• Waktu: " . now()->format('d/m/Y H:i') . " WIB\n\n"
                    . "Mohon tim BK melakukan pendekatan atau pendampingan secara hangat dan rahasia.";
                $waService->sendMessage($waPhone, $alertText);
            }
        } catch (\Throwable $e) {
            Log::debug('Crisis WA notification skipped: ' . $e->getMessage());
        }
    }

    /**
     * Detect crisis or sensitive keywords to attach warm BK guidance.
     */
    protected function detectCrisisKeywords(string $prompt): ?string
    {
        $lowered = strtolower($prompt);
        $keywords = ['bunuh diri', 'depresi', 'lelah hidup', 'menyakiti diri', 'dibully', 'bullying', 'putus asa', 'ingin menyerah'];

        foreach ($keywords as $kw) {
            if (str_contains($lowered, $kw)) {
                return "💚 **Catatan Hangat dari Tim BK PembdaHUB:**\n"
                    . "Kami sangat peduli dengan keselamatan dan kesejahteraan perasaan Anda. Jika Anda sedang menghadapi tekanan berat atau masalah emosional, ingatlah bahwa Anda tidak sendirian. Silakan buat janji bimbingan di menu **Catatan Perkembangan / BK** portal sekolah untuk berbicara langsung secara aman dan hangat dengan Guru BK sekolah Anda.";
            }
        }

        return null;
    }

    /**
     * Embed Knowledge Base about PembdaHUB Ecosystem.
     */
    protected function getPembdaHubKnowledgeBase(): string
    {
        return <<<KNOWLEDGE
INFORMASI UTAMA & LINGKUNGAN EKOSISTEM PEMBDAHUB:
- **PembdaHUB** adalah Sistem Ekosistem Pendidikan Digital Terpadu Yayasan Perguruan PEMBDA Nias.
- Motto Perjuangan: "Keep Moving Forward / Maju Terus Pantang Mundur".
- **3 Unit Sekolah Aktif di Bawah Yayasan**:
  1. SMP Swasta Pembda 2 Gunungsitoli
  2. SMA Swasta Pembda 1 Gunungsitoli
  3. SMK Swasta Pembda Gunungsitoli
- **5 Rumpun Kejuruan di SMK Swasta Pembda**:
  1. TE / TAV: Teknik Elektronika / Audio Video
  2. DPIB: Desain Pemodelan dan Informasi Bangunan
  3. TKR / TO: Teknik Kendaraan Ringan / Otomotif
  4. TSM / TBSM: Teknik Sepeda Motor
  5. TKJ / TJKT / ACP: Teknik Komputer Jaringan / Axioo Class Program
- **Fitur-Fitur & Menu di Portal Siswa PembdaHUB**:
  - **LMS (Learning Management System)**: Tempat membaca modul ajar interaktif buatan guru, pengerjaan kuis, diskusi kelas, dan klaim sertifikat modul.
  - **CBT / Ujian**: Tempat pengerjaan Ujian Sekolah, Evaluasi, UTS, UAS online secara aman. Hasil nilai dapat ditinjau.
  - **Jadwal Pelajaran & Presensi**: Melihat jadwal pelajaran harian dan rekap kehadiran presensi (RFID / GPS).
  - **Nilai & Rapor**: Transkrip capaian nilai mata pelajaran dan pencetakan rapor digital.
  - **DNA Akademik 360°**: Diagnostik pemetaan minat, bakat, gaya belajar, dan potensi akademik siswa.
  - **Catatan Perkembangan & BK**: Tempat siswa mengunggah bukti kejuaraan/prestasi mandiri serta membuat janji bimbingan konseling dengan Guru BK sekolah.
  - **Pembda Space**: Forum diskusi sosial, berita sekolah, dan kolaborasi warga sekolah.
  - **Simulator Lab**: Laboratorium virtual interaktif untuk praktikum sains dan kejuruan.
  - **Praktik Kerja (PKL)**: Khusus siswa Kelas XII SMK untuk mengisi logbook jurnal harian DUDI dan monitoring industri.
  - **Project Akhir (SMK) / Tugas Akhir (SMA)**: Khusus siswa Kelas XII untuk pengajuan proposal karya kejuruan atau penelitian ilmiah mandiri.
  - **STEAMpreneur & Ekskul**: Wadah inovasi wirausaha teknologi dan pendaftaran kegiatan ekstrakurikuler sekolah.
- Jika siswa bertanya mengenai cara menggunakan menu atau fungsi sistem PembdaHUB di atas, jawablah dengan ramah dan beri petunjuk langkah-langkah navigasi menu di portal siswa PembdaHUB.
KNOWLEDGE;
    }

    /**
     * Build contextual system prompt for student with real academic data (class, LMS courses, DNA diagnostic).
     */
    protected function buildSystemPrompt(Student $student, string $mode): string
    {
        $schoolName = $student->school->name ?? 'Sekolah Perguruan Pembda';
        $schoolType = $student->school->type ?? 'Sekolah';
        $majorName = $student->major->name ?? null;

        // Ambil kelas aktif siswa
        $className = null;
        try {
            $className = $student->classroom->class_name ?? null;
            if (!$className) {
                $latestPivot = $student->studentClasses()->with('classroom')->latest('id')->first();
                $className = $latestPivot?->classroom?->class_name;
            }
        } catch (\Throwable $e) {
            // abaikan jika relasi tidak tersedia
        }

        $contextText = "Nama Siswa: {$student->full_name}\nUnit Sekolah: {$schoolName} ({$schoolType})";
        if ($className) {
            $contextText .= "\nKelas Saat Ini: {$className}";
        }
        if ($majorName) {
            $contextText .= "\nJurusan SMK: {$majorName}";
        }

        // Ambil daftar Course LMS yang sedang diikuti siswa
        try {
            $courseNames = \App\Models\LmsEnrollment::where('student_id', $student->id)
                ->with('lmsClass.course')
                ->latest('id')
                ->take(8)
                ->get()
                ->map(fn($en) => $en->lmsClass?->course?->course_name ?? $en->lmsClass?->course?->name)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($courseNames)) {
                $contextText .= "\nMata Pelajaran / Course LMS Aktif: " . implode(', ', $courseNames);
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        // Ambil ringkasan DNA Akademik / Minat Bakat jika tersedia
        try {
            $dna = \App\Models\StudentDiagnosticAssessment::where('student_id', $student->id)->first();
            if ($dna) {
                $contextText .= "\nProfil Minat & Karir (DNA Akademik): Cita-cita={$dna->career_aspiration}, Rumpun Favorit={$dna->favorite_subject_cluster}, Gaya Belajar={$dna->work_style_preference}";
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        $knowledgeBase = $this->getPembdaHubKnowledgeBase();

        if ($mode === 'bk_consultation') {
            return "Anda adalah 'Sahabat Konseling & Karir PembdaHUB', konselor edukatif yang ramah, sopan, dan penuh empati untuk siswa di Perguruan PEMBDA Nias.\n"
                . "Konteks Siswa:\n{$contextText}\n\n"
                . "{$knowledgeBase}\n\n"
                . "TUGAS UTAMA:\n"
                . "1. Memberikan saran motivasi belajar, tips manajemen waktu, dan panduan pilihan karir (perguruan tinggi/kuliah untuk SMA atau peluang kerja DUDI/industri untuk SMK) yang relevan dengan kelas dan profil minat siswa di atas.\n"
                . "2. Membantu siswa memahami lingkungan PembdaHUB dan fitur-fitur di dalamnya jika ditanyakan.\n"
                . "3. Dengarkan pertanyaan siswa dengan ramah dan berikan nasihat yang positif serta membangun mental pantang menyerah ('Keep Moving Forward').\n"
                . "4. Jawab menggunakan bahasa Indonesia yang santun, jelas, dan menyemangati.";
        }

        if ($mode === 'lms_assistant') {
            return "Anda adalah 'Pendamping Belajar LMS PembdaHUB'.\n"
                . "Konteks Siswa:\n{$contextText}\n\n"
                . "{$knowledgeBase}\n\n"
                . "TUGAS UTAMA:\n"
                . "1. Membantu siswa memahami rangkuman materi pelajaran (termasuk materi dari Course LMS aktif siswa di atas), memberikan penjelasan ulang dengan analogi sederhana, dan membuatkan latihan kuis mandiri 3-5 soal jika diminta.\n"
                . "2. Membantu memberikan petunjuk penggunaan modul LMS dan fitur pembelajaran di PembdaHUB.\n"
                . "3. Sajikan penjelasan dalam format Markdown yang rapi dengan poin-poin serta KaTeX ($...$ / $$...$$) jika ada rumus.";
        }

        // Default: Tutor Pelajaran Q&A
        return "Anda adalah 'Tutor AI PembdaHUB', asisten belajar akademis cerdas, komunikatif, dan sabar untuk siswa Perguruan PEMBDA Nias.\n"
            . "Konteks Siswa:\n{$contextText}\n\n"
            . "{$knowledgeBase}\n\n"
            . "TUGAS UTAMA:\n"
            . "1. Membantu siswa memahami soal (termasuk soal dari foto yang diunggah siswa), konsep pelajaran (Matematika, IPA, Bahasa, Mapel Kejuruan SMK, dsb) sesuai tingkat kelasnya.\n"
            . "2. Membantu menjelaskan fitur, fungsi, dan navigasi di ekosistem PembdaHUB jika siswa bertanya tentang lingkungan sekolah/sistem PembdaHUB.\n"
            . "3. Gunakan metode pembimbingan bertahap (Socratic): jelaskan langkah-langkah logika pemecahan masalah dengan gamblang.\n"
            . "4. Jika ada rumus matematika atau sains, format menggunakan sintaks KaTeX / LaTeX ($...$ atau $$...$$) atau format Markdown yang jelas.\n"
            . "5. Jaga agar jawaban tetap edukatif, sopan, dan mendukung integritas akademis (tolak jika diminta memberikan kunci jawaban ujian CBT secara ilegal).";
    }

    /**
     * Build structured multi-turn messages array (system, user, assistant) with optional image attachment.
     */
    protected function buildChatMessages(Student $student, string $currentPrompt, string $mode, array $history, ?string $imageDataUri = null): array
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => $this->buildSystemPrompt($student, $mode),
            ],
        ];

        if (!empty($history)) {
            foreach (array_slice($history, -10) as $msg) {
                $role = (($msg['sender'] ?? 'student') === 'student') ? 'user' : 'assistant';
                $text = trim((string) ($msg['message'] ?? ''));
                if ($text !== '') {
                    $messages[] = [
                        'role'    => $role,
                        'content' => $text,
                    ];
                }
            }
        }

        $userMsg = [
            'role'    => 'user',
            'content' => $currentPrompt,
        ];
        if (!empty($imageDataUri)) {
            $userMsg['image_data_uri'] = $imageDataUri;
        }
        $messages[] = $userMsg;

        return $messages;
    }
}
