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
     * Check if student has reached daily usage limit.
     */
    public function getUsageInfo(int $studentId): array
    {
        $today = now()->toDateString();
        $usage = DB::table('ai_student_usages')
            ->where('student_id', $studentId)
            ->where('usage_date', $today)
            ->first();

        $used = $usage ? $usage->prompt_count : 0;
        $remaining = max(0, self::DAILY_LIMIT - $used);

        return [
            'used' => $used,
            'limit' => self::DAILY_LIMIT,
            'remaining' => $remaining,
            'is_exceeded' => $used >= self::DAILY_LIMIT
        ];
    }

    /**
     * Increment usage count for student.
     */
    public function incrementUsage(int $studentId): void
    {
        $today = now()->toDateString();
        DB::table('ai_student_usages')->updateOrInsert(
            ['student_id' => $studentId, 'usage_date' => $today],
            [
                'prompt_count' => DB::raw('prompt_count + 1'),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Generate response from AI for student.
     */
    public function generateResponse(Student $student, string $prompt, string $mode = 'tutor', array $chatHistory = []): array
    {
        $usage = $this->getUsageInfo($student->id);
        if ($usage['is_exceeded']) {
            return [
                'success' => false,
                'message' => 'Batas kuota harian penggunaan Pembda AI (' . self::DAILY_LIMIT . ' pertanyaan/hari) telah tercapai. Kuota Anda akan terisi ulang besok.',
                'is_quota_error' => true
            ];
        }

        // Safety check for sensitive crisis keywords
        $crisisNotice = $this->detectCrisisKeywords($prompt);

        // Build system prompt based on mode and student context
        $systemPrompt = $this->buildSystemPrompt($student, $mode);

        // Format prompt with context & chat history
        $fullPrompt = $this->formatFullPrompt($systemPrompt, $chatHistory, $prompt);

        try {
            $aiResult = $this->gemini->generateText($fullPrompt);

            // Increment usage after successful call
            $this->incrementUsage($student->id);

            // Append crisis notice if keyword triggered
            if ($crisisNotice) {
                $aiResult .= "\n\n---\n" . $crisisNotice;
            }

            return [
                'success' => true,
                'message' => $aiResult,
                'remaining_quota' => max(0, $usage['remaining'] - 1)
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
     * Build contextual system prompt for student.
     */
    protected function buildSystemPrompt(Student $student, string $mode): string
    {
        $schoolName = $student->school->name ?? 'Sekolah Perguruan Pembda';
        $schoolType = $student->school->type ?? 'Sekolah';
        $majorName = $student->major->name ?? null;

        $contextText = "Nama Siswa: {$student->full_name}\nUnit Sekolah: {$schoolName} ({$schoolType})";
        if ($majorName) {
            $contextText .= "\nJurusan SMK: {$majorName}";
        }

        $knowledgeBase = $this->getPembdaHubKnowledgeBase();

        if ($mode === 'bk_consultation') {
            return "Anda adalah 'Sahabat Konseling & Karir PembdaHUB', konselor edukatif yang ramah, sopan, dan penuh empati untuk siswa di Perguruan PEMBDA Nias.\n"
                . "Konteks Siswa:\n{$contextText}\n\n"
                . "{$knowledgeBase}\n\n"
                . "TUGAS UTAMA:\n"
                . "1. Memberikan saran motivasi belajar, tips manajemen waktu, dan panduan pilihan karir (perguruan tinggi/kuliah untuk SMA atau peluang kerja DUDI/industri untuk SMK).\n"
                . "2. Membantu siswa memahami lingkungan PembdaHUB dan fitur-fitur di dalamnya jika ditanyakan.\n"
                . "3. Dengarkan pertanyaan siswa dengan ramah dan berikan nasihat yang positif serta membangun mental pantang menyerah ('Keep Moving Forward').\n"
                . "4. Jawab menggunakan bahasa Indonesia yang santun, jelas, dan menyemangati.";
        }

        if ($mode === 'lms_assistant') {
            return "Anda adalah 'Pendamping Belajar LMS PembdaHUB'.\n"
                . "Konteks Siswa:\n{$contextText}\n\n"
                . "{$knowledgeBase}\n\n"
                . "TUGAS UTAMA:\n"
                . "1. Membantu siswa memahami rangkuman materi pelajaran, memberikan penjelasan ulang dengan analogi sederhana, dan membuatkan latihan kuis mandiri 3-5 soal jika diminta.\n"
                . "2. Membantu memberikan petunjuk penggunaan modul LMS dan fitur pembelajaran di PembdaHUB.\n"
                . "3. Sajikan penjelasan dalam format Markdown yang rapi dengan poin-poin.";
        }

        // Default: Tutor Pelajaran Q&A
        return "Anda adalah 'Tutor AI PembdaHUB', asisten belajar akademis cerdas, komunikatif, dan sabar untuk siswa Perguruan PEMBDA Nias.\n"
            . "Konteks Siswa:\n{$contextText}\n\n"
            . "{$knowledgeBase}\n\n"
            . "TUGAS UTAMA:\n"
            . "1. Membantu siswa memahami soal, konsep pelajaran (Matematika, IPA, Bahasa, Mapel Kejuruan SMK, dsb).\n"
            . "2. Membantu menjelaskan fitur, fungsi, dan navigasi di ekosistem PembdaHUB jika siswa bertanya tentang lingkungan sekolah/sistem PembdaHUB.\n"
            . "3. Gunakan metode pembimbingan bertahap (Socratic): jelaskan langkah-langkah logika pemecahan masalah dengan gamblang.\n"
            . "4. Jika ada rumus matematika atau sains, format menggunakan sintaks KaTeX / LaTeX ($...$ atau $$...$$) atau format Markdown yang jelas.\n"
            . "5. Jaga agar jawaban tetap edukatif, sopan, dan mendukung integritas akademis (tolak jika diminta memberikan kunci jawaban ujian CBT secara ilegal).";
    }

    /**
     * Format prompt with recent conversation history.
     */
    protected function formatFullPrompt(string $systemPrompt, array $history, string $currentPrompt): string
    {
        $prompt = "SYSTEM INSTRUCTION:\n{$systemPrompt}\n\n";

        if (!empty($history)) {
            $prompt .= "RIWAYAT PERCAKAPAN SEBELUMNYA:\n";
            foreach (array_slice($history, -6) as $msg) {
                $role = ($msg['sender'] === 'student') ? 'Siswa' : 'Pembda AI';
                $prompt .= "{$role}: " . trim($msg['message']) . "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "PERTANYAAN BARU SISWA:\n{$currentPrompt}\n\nRESPONS PEMBDA AI:";
        return $prompt;
    }
}
