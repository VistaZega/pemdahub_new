<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Generate content from prompt.
     */
    public function generateText(string $prompt): string
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API Key is not configured. Running in mock/simulation mode.');
            return $this->getMockResponse($prompt);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            }

            Log::error('Gemini API Error: ' . $response->body());
            return $this->getMockResponse($prompt);
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return $this->getMockResponse($prompt);
        }
    }

    /**
     * Generate structured JSON from prompt.
     */
    public function generateJson(string $prompt): array
    {
        // If no API key configured, skip API call and return mock JSON directly
        if (empty($this->apiKey)) {
            Log::warning('Gemini API Key not configured. Using mock JSON response.');
            return $this->getMockJsonResponse($prompt);
        }

        $resultText = $this->generateText($prompt);
        
        // Clean JSON formatting markdown wrapper if present
        $cleanJson = trim($resultText);
        if (str_starts_with($cleanJson, '```json')) {
            $cleanJson = substr($cleanJson, 7);
        }
        if (str_starts_with($cleanJson, '```')) {
            $cleanJson = substr($cleanJson, 3);
        }
        if (str_ends_with($cleanJson, '```')) {
            $cleanJson = substr($cleanJson, 0, -3);
        }
        $cleanJson = trim($cleanJson);

        $decoded = json_decode($cleanJson, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Try extracting JSON array via regex if direct decode fails
        if (preg_match('/\[\s*\{.*\}\s*\]/s', $cleanJson, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        Log::warning('Gemini response was not valid JSON, using mock fallback: ' . substr($resultText, 0, 100));
        return $this->getMockJsonResponse($prompt);
    }

    /**
     * Fallback mock response for simulation.
     */
    protected function getMockResponse(string $prompt): string
    {
        if (str_contains($prompt, 'Modul Ajar') || str_contains($prompt, 'RPP')) {
            return $this->getMockRppMarkdown($prompt);
        }
        
        return "Ini adalah respons simulasi dari Asisten AI. (Kunci API Gemini belum dikonfigurasi).";
    }

    /**
     * Mock RPP Markdown content.
     */
    protected function getMockRppMarkdown(string $prompt): string
    {
        // Parse class, subject, topic from prompt if possible
        $subject = 'Mata Pelajaran';
        $topic = 'Materi Pelajaran';
        
        if (preg_match('/mata pelajaran:\s*([^,]+)/i', $prompt, $m)) $subject = trim($m[1]);
        if (preg_match('/tema\/topik:\s*([^,]+)/i', $prompt, $m)) $topic = trim($m[1]);

        return "# MODUL AJAR KURIKULUM MERDEKA (SIMULASI)
        
## I. INFORMASI UMUM
* **Mata Pelajaran:** {$subject}
* **Materi/Tema:** {$topic}
* **Tingkat/Kelas:** Kelas X (SMA)
* **Alokasi Waktu:** 2 x 45 Menit (1 Pertemuan)
* **Profil Pelajar Pancasila:** Gotong Royong, Bernalar Kritis, Mandiri

---

## II. KOMPONEN INTI

### A. Capaian & Tujuan Pembelajaran
Siswa mampu memahami, menganalisis, dan mengevaluasi konsep pokok terkait {$topic} secara mendalam serta mengaplikasikannya dalam kehidupan sehari-hari.

### B. Pemahaman Bermakna
{$topic} membantu kita mengenali keterkaitan sistematis dalam ilmu pengetahuan dan meningkatkan kepekaan analisis kritis.

### C. Pertanyaan Pemantik
1. Apa yang Anda ketahui tentang {$topic}?
2. Mengapa hal ini penting untuk dipelajari dalam konteks kehidupan kita?

---

## III. KEGIATAN PEMBELAJARAN

### 1. Kegiatan Pendahuluan (15 Menit)
* Guru membuka kelas dengan salam hangat, berdoa, dan memeriksa kehadiran siswa.
* Guru memberikan apersepsi terkait materi {$topic} menggunakan pertanyaan pemantik.
* Guru menyampaikan tujuan pembelajaran yang akan dicapai hari ini.

### 2. Kegiatan Inti (60 Menit)
* **Eksplorasi:** Siswa membaca materi literatur atau tayangan presentasi tentang {$topic}.
* **Kolaborasi:** Siswa dibagi menjadi beberapa kelompok diskusi kecil untuk membedah studi kasus.
* **Presentasi:** Perwakilan kelompok mempresentasikan hasil diskusi di depan kelas secara bergantian.
* **Umpan Balik:** Guru memberikan apresiasi dan meluruskan konsep yang kurang tepat.

### 3. Kegiatan Penutup (15 Menit)
* Guru membimbing siswa menyimpulkan inti dari materi {$topic} hari ini.
* Guru dan siswa melakukan refleksi pembelajaran (apa yang dipahami, apa yang belum dipahami).
* Kelas ditutup dengan doa bersama.

---

## IV. ASESMEN & PENILAIAN
1. **Asesmen Formatif:** Observasi keaktifan diskusi kelompok dan pengerjaan lembar kerja siswa (LKS).
2. **Asesmen Sumatif:** Soal latihan tertulis pilihan ganda dan esai singkat di akhir bab.";
    }

    /**
     * Fallback mock JSON content for questions.
     */
    protected function getMockJsonResponse(string $prompt): array
    {
        return [
            [
                'question' => 'Manakah di bawah ini yang merupakan komponen penting dalam materi pelajaran yang dipelajari?',
                'options' => [
                    'A' => 'Mengabaikan teori dasar',
                    'B' => 'Melakukan praktik tanpa perencanaan',
                    'C' => 'Memadukan analisis konsep dan latihan terpadu',
                    'D' => 'Hanya mengandalkan hafalan ujian',
                    'E' => 'Menunggu instruksi guru tanpa keaktifan'
                ],
                'answer' => 'C',
                'explanation' => 'Pembelajaran yang bermakna memerlukan perpaduan yang seimbang antara pemahaman konsep teoretis dan latihan praktis terpadu.'
            ],
            [
                'question' => 'Apa tujuan utama dari dilakukannya evaluasi berkala setelah proses pembelajaran selesai?',
                'options' => [
                    'A' => 'Memberikan hukuman bagi siswa yang tertinggal',
                    'B' => 'Mengetahui tingkat pemahaman siswa dan efektivitas metode ajar',
                    'C' => 'Mengurangi jam istirahat sekolah',
                    'D' => 'Membuat siswa merasa tertekan sebelum liburan',
                    'E' => 'Meningkatkan biaya administrasi sekolah'
                ],
                'answer' => 'B',
                'explanation' => 'Evaluasi berkala bertujuan untuk mengukur capaian tujuan pembelajaran siswa serta menjadi bahan umpan balik bagi guru untuk memperbaiki metode pengajarannya.'
            ],
            [
                'question' => 'Bagaimana sikap terbaik siswa dalam merespon sebuah tugas yang dirasa sulit?',
                'options' => [
                    'A' => 'Membiarkan tugas tersebut kosong hingga tenggat waktu',
                    'B' => 'Menyalin jawaban dari teman kelas tanpa membaca ulang',
                    'C' => 'Membaca referensi materi terkait, berdiskusi kelompok, atau bertanya kepada guru',
                    'D' => 'Melayangkan protes keras kepada pihak sekolah',
                    'E' => 'Memilih untuk membolos pada jam pelajaran berikutnya'
                ],
                'answer' => 'C',
                'explanation' => 'Kesulitan dalam belajar sebaiknya diatasi secara proaktif dengan membaca referensi, berdiskusi kolaboratif, atau meminta bimbingan guru.'
            ]
        ];
    }
}
