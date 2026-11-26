<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $activeProvider;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    protected $classAiBaseUrl;
    protected $classAiModel;
    protected $timeout;

    public function __construct()
    {
        $configuredProvider = strtolower(trim((string) (
            config('services.ai.provider')
            ?: (\App\Models\Setting::getValue('ai_provider') ?? env('AI_PROVIDER', 'gemini'))
        )));

        $classAiKey = config('services.ai.classai.api_key')
            ?: (\App\Models\Setting::getValue('classai_api_key') ?? env('CLASSAI_API_KEY'));
        $geminiKey = config('services.gemini.key')
            ?: (\App\Models\Setting::getValue('gemini_api_key') ?? env('GEMINI_API_KEY'));

        $this->classAiBaseUrl = rtrim((string) (
            \App\Models\Setting::getValue('classai_base_url')
            ?: config('services.ai.classai.base_url')
            ?: env('CLASSAI_BASE_URL', 'https://router.classai.id/v1')
        ), '/');
        $this->classAiModel = (string) (
            \App\Models\Setting::getValue('classai_model')
            ?: config('services.ai.classai.model')
            ?: env('CLASSAI_MODEL', 'deepseek-v4-flash-0731')
        );
        $this->timeout = (int) (config('services.ai.classai.timeout') ?: 60);

        if ($configuredProvider === 'classai' || (empty($geminiKey) && !empty($classAiKey))) {
            $this->activeProvider = 'classai';
            $this->apiKey = $classAiKey;
        } else {
            $this->activeProvider = 'gemini';
            $this->apiKey = $geminiKey;
        }
    }

    /**
     * Generate content from prompt using active AI provider (ClassAI Router or Google Gemini).
     */
    public function generateText(string $prompt): string
    {
        if (empty($this->apiKey)) {
            Log::info('AI API Key is not configured. Utilizing Pembda AI Smart Knowledge Engine.');
            return $this->getMockResponse($prompt);
        }

        if ($this->activeProvider === 'classai') {
            return $this->generateViaClassAi($prompt);
        }

        return $this->generateViaGemini($prompt);
    }

    /**
     * Test live AI connection without falling back to mock responses.
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success'  => false,
                'provider' => $this->activeProvider,
                'model'    => $this->activeProvider === 'classai' ? $this->classAiModel : 'gemini-1.5-flash',
                'message'  => 'API Key belum diisi untuk provider ' . strtoupper($this->activeProvider) . '.',
            ];
        }

        $testPrompt = 'Balas hanya dengan satu kalimat singkat: Koneksi AI PembdaHUB berhasil.';

        try {
            if ($this->activeProvider === 'classai') {
                $response = Http::timeout(20)
                    ->withToken($this->apiKey)
                    ->acceptJson()
                    ->post($this->classAiBaseUrl . '/chat/completions', [
                        'model' => $this->classAiModel,
                        'messages' => [
                            ['role' => 'user', 'content' => $testPrompt],
                        ],
                    ]);

                if ($response->successful()) {
                    $reply = trim((string) ($response->json('choices.0.message.content') ?? ''));
                    if ($reply !== '') {
                        return [
                            'success'  => true,
                            'provider' => 'ClassAI Router',
                            'model'    => $this->classAiModel,
                            'message'  => 'Koneksi ClassAI Router berhasil! Respons: ' . \Illuminate\Support\Str::limit($reply, 120),
                        ];
                    }
                }

                return [
                    'success'  => false,
                    'provider' => 'ClassAI Router',
                    'model'    => $this->classAiModel,
                    'message'  => 'Gagal terhubung ke ClassAI Router (HTTP ' . $response->status() . '): ' . \Illuminate\Support\Str::limit($response->body(), 200),
                ];
            }

            $response = Http::timeout(20)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        ['parts' => [['text' => $testPrompt]]]
                    ]
                ]);

            if ($response->successful()) {
                $reply = trim((string) ($response->json('candidates.0.content.parts.0.text') ?? ''));
                if ($reply !== '') {
                    return [
                        'success'  => true,
                        'provider' => 'Google Gemini',
                        'model'    => 'gemini-1.5-flash',
                        'message'  => 'Koneksi Google Gemini berhasil! Respons: ' . \Illuminate\Support\Str::limit($reply, 120),
                    ];
                }
            }

            return [
                'success'  => false,
                'provider' => 'Google Gemini',
                'model'    => 'gemini-1.5-flash',
                'message'  => 'Gagal terhubung ke Google Gemini (HTTP ' . $response->status() . '): ' . \Illuminate\Support\Str::limit($response->body(), 200),
            ];
        } catch (\Exception $e) {
            return [
                'success'  => false,
                'provider' => $this->activeProvider,
                'model'    => $this->activeProvider === 'classai' ? $this->classAiModel : 'gemini-1.5-flash',
                'message'  => 'Exception saat tes koneksi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Call ClassAI Router (OpenAI-compatible /chat/completions endpoint).
     */
    protected function generateViaClassAi(string $prompt): string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->acceptJson()
                ->post($this->classAiBaseUrl . '/chat/completions', [
                    'model' => $this->classAiModel,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['choices'][0]['message']['content'] ?? '';
                if (!empty(trim((string) $text))) {
                    return (string) $text;
                }
            }

            Log::error('ClassAI API Error or empty text: ' . $response->body());
            return $this->getMockResponse($prompt);
        } catch (\Exception $e) {
            Log::error('ClassAI API Exception: ' . $e->getMessage());
            return $this->getMockResponse($prompt);
        }
    }

    /**
     * Call Google Gemini native generateContent endpoint.
     */
    protected function generateViaGemini(string $prompt): string
    {
        try {
            $response = Http::timeout($this->timeout)->withHeaders([
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
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty(trim($text))) {
                    return $text;
                }
            }

            Log::error('Gemini API Error or empty text: ' . $response->body());
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
        if (empty($this->apiKey)) {
            Log::info('Gemini API Key not configured. Using mock JSON response.');
            return $this->getMockJsonResponse($prompt);
        }

        $resultText = $this->generateText($prompt);
        
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
     * Smart fallback AI Engine for PembdaHUB when API Key is pending or in offline mode.
     */
    protected function getMockResponse(string $prompt): string
    {
        $userQuery = $prompt;
        if (preg_match('/PERTANYAAN BARU SISWA:\s*(.*?)(?:\n\nRESPONS PEMBDA AI:|$)/s', $prompt, $matches)) {
            $userQuery = trim($matches[1]);
        }
        $lowered = strtolower($userQuery);

        if (str_contains($prompt, 'Modul Ajar') || str_contains($prompt, 'RPP')) {
            return $this->getMockRppMarkdown($prompt);
        }

        // 1. Presiden AS / US President
        if (str_contains($lowered, 'presiden as') || str_contains($lowered, 'presiden amerika') || str_contains($lowered, 'preseiden as') || str_contains($lowered, 'presiden us')) {
            return "### 🇺🇸 Pengetahuan Umum: Presiden Amerika Serikat\n\n"
                . "Presiden Amerika Serikat adalah kepala negara sekaligus kepala pemerintahan di Amerika Serikat.\n\n"
                . "* **Presiden AS Saat Ini (Ke-47)**: **Donald Trump** (Dilantik pada 20 Januari 2025 setelah memenangkan Pemilu AS 2024).\n"
                . "* **Presiden AS Ke-46 (Sebelumnya)**: **Joe Biden** (Menjabat 2021 – 2025).\n"
                . "* **Presiden AS Pertama dalam Sejarah**: **George Washington** (Menjabat 1789 – 1797).\n\n"
                . "💡 *Ada pertanyaan seputar Sejarah Dunia, Kewarganegaraan, atau Pengetahuan Umum lainnya? Silakan tanyakan pada Pembda AI!*";
        }

        // 2. Presiden RI / Indonesia
        if (str_contains($lowered, 'presiden ri') || str_contains($lowered, 'presiden indonesia') || str_contains($lowered, 'jokowi') || str_contains($lowered, 'prabowo') || str_contains($lowered, 'soekarno')) {
            return "### 🇮🇩 Pengetahuan Umum: Presiden Republik Indonesia\n\n"
                . "Presiden Republik Indonesia adalah pemegang kekuasaan pemerintahan tertinggi di Indonesia.\n\n"
                . "* **Presiden RI Saat Ini (Ke-8)**: **Prabowo Subianto** (Wakil Presiden: Gibran Rakabuming Raka, dilantik 20 Oktober 2024).\n"
                . "* **Presiden RI Ke-7**: **Joko Widodo** (Menjabat 2014 – 2024).\n"
                . "* **Presiden RI Pertama (Proklamator)**: **Ir. Soekarno** (Menjabat 1945 – 1967).\n\n"
                . "💡 *Ada pertanyaan seputar Sejarah Indonesia atau PPKn? Silakan tanyakan pada Pembda AI!*";
        }

        // 3. Math / Science Formula & Practice Problems (Soal Matematika)
        if (str_contains($lowered, 'rumus') || str_contains($lowered, 'matematika') || str_contains($lowered, 'soal matematika') || str_contains($lowered, 'fisika') || str_contains($lowered, 'hitung') || str_contains($lowered, 'luas') || str_contains($lowered, 'keliling') || str_contains($lowered, 'tabung') || str_contains($lowered, 'lingkaran') || str_contains($lowered, 'soal')) {
            return '### 🧮 Latihan Soal Matematika & Pembahasan Terpadu' . "\n\n"
                . 'Halo! Berikut adalah pembahasan contoh soal Matematika terpadu yang dapat dipelajari:' . "\n\n"
                . '#### Contoh Soal:' . "\n"
                . 'Sebuah tabung memiliki jari-jari alas $r = 7\text{ cm}$ dan tinggi $t = 10\text{ cm}$. Hitunglah **Luas Permukaan** dan **Volume** tabung tersebut!' . "\n\n"
                . '#### Pembahasan Step-by-Step:' . "\n"
                . '1. **Rumus Luas Permukaan Tabung**:' . "\n"
                . '   $$Luas = 2 \times \pi \times r \times (r + t)$$' . "\n"
                . '   $$Luas = 2 \times \frac{22}{7} \times 7 \times (7 + 10) = 44 \times 17 = 748\text{ cm}^2$$' . "\n\n"
                . '2. **Rumus Volume Tabung**:' . "\n"
                . '   $$Volume = \pi \times r^2 \times t$$' . "\n"
                . '   $$Volume = \frac{22}{7} \times 7^2 \times 10 = 22 \times 7 \times 10 = 1.540\text{ cm}^3$$' . "\n\n"
                . '💡 *Silakan tuliskan soal Matematika spesifik yang ingin kamu selesaikan, atau tanyakan rumus pelajaran lainnya kepada Pembda AI!*';
        }

        // 4. BK / Career / Mental Health Consultation
        if (str_contains($lowered, 'kuliah') || str_contains($lowered, 'karir') || str_contains($lowered, 'dudi') || str_contains($lowered, 'bingung') || str_contains($lowered, 'kerja') || str_contains($lowered, 'motivasi') || str_contains($lowered, 'depresi') || str_contains($lowered, 'lelah')) {
            return "### 🎓 Panduan Bimbingan Karir & Motivasi Belajar\n\n"
                . "Setiap langkah belajar Anda di Perguruan PEMBDA adalah investasi berharga untuk masa depan!\n\n"
                . "* **Untuk Siswa SMA:** Fokuslah pada pemetaan minat jurusan perguruan tinggi (Teknik, Sains, Ekonomi, atau Pendidikan) sesuai potensi akademik di **DNA Akademik 360°**.\n"
                . "* **Untuk Siswa SMK:** Manfaatkan pengalaman **Praktik Kerja (PKL)** dan sertifikasi keahlian jurusan (TAV/DPIB/TKR/TSM/TKJ) untuk mempersiapkan diri langsung ke dunia kerja DUDI atau melanjutkan kuliah kejuruan.\n\n"
                . "🌱 *Ingat, keberhasilan ditentukan oleh konsistensi dan kerja keras harian Anda.*";
        }

        // 5. PembdaHUB Ecosystem Info
        if (str_contains($lowered, 'pembdahub') || str_contains($lowered, 'jurusan') || str_contains($lowered, 'rapor') || str_contains($lowered, 'cbt') || str_contains($lowered, 'lms') || str_contains($lowered, 'fitur') || str_contains($lowered, 'sekolah')) {
            return "### 🏫 Panduan Navigasi Ekosistem PembdaHUB\n\n"
                . "Berikut adalah penjelasan mengenai lingkungan dan fitur di **PembdaHUB** Yayasan Perguruan PEMBDA Nias:\n\n"
                . "1. **3 Unit Sekolah Aktif**: SMPS Pembda 2, SMAS Pembda 1, dan SMKS Pembda Gunungsitoli.\n"
                . "2. **5 Jurusan SMK**: Teknik Audio Video (TAV), DPIB (Bangunan), TKR (Otomotif), TSM (Sepeda Motor), dan TKJ / Axioo Class (ACP).\n"
                . "3. **Mengecek Nilai & Rapor**: Buka menu **Nilai & Rapor** di sidebar sebelah kiri portal siswa untuk melihat transkrip dan cetak rapor.\n"
                . "4. **Fitur LMS & CBT**: Menu **LMS** untuk membaca modul ajar & kuis, sedangkan menu **CBT / Ujian** untuk mengikuti ujian online sekolah.\n"
                . "5. **Bimbingan & Prestasi**: Menu **Catatan Perkembangan** untuk upload sertifikat kejuaraan dan janji temu Guru BK.\n\n"
                . "> *Motto Perjuangan: Keep Moving Forward / Maju Terus Pantang Mundur!*";
        }

        // 6. Dynamic Smart Query Responder for any general questions
        $cleanQuestion = !empty($userQuery) ? ucfirst($userQuery) : "Pertanyaan Siswa";
        return "### 💡 Penjelasan Pembda AI: " . htmlspecialchars($cleanQuestion) . "\n\n"
            . "Terima kasih telah bertanya! Berikut adalah panduan pembahasan untuk topik **\"" . htmlspecialchars($cleanQuestion) . "\"**:\n\n"
            . "1. **Pemahaman Dasar**: Topik ini berkaitan dengan materi pembelajaran dan latihan soal di sekolah.\n"
            . "2. **Langkah Pengerjaan**: Tuliskan soal lengkap beserta angka/variabel yang ingin dihitung (misalnya *\"Hitung luas segitiga jika alas = 10 dan tinggi = 5\"*).\n"
            . "3. **Fasilitas PembdaHUB**: Anda juga dapat mengakses materi lengkap di menu **LMS**, mencoba ujian di **CBT**, atau berdiskusi di **Pembda Space**.\n\n"
            . "💡 *Tuliskan soal atau topik lengkap di kolom pesan di bawah ini agar Pembda AI dapat memberikan pembahasan step-by-step!*";
    }

    /**
     * Fallback mock RPP Markdown content.
     */
    protected function getMockRppMarkdown(string $prompt): string
    {
        $subject = 'Mata Pelajaran';
        $topic = 'Materi Pelajaran';
        
        if (preg_match('/mata pelajaran:\s*([^,]+)/i', $prompt, $m)) $subject = trim($m[1]);
        if (preg_match('/tema\/topik:\s*([^,]+)/i', $prompt, $m)) $topic = trim($m[1]);

        return "# MODUL AJAR KURIKULUM MERDEKA (PEMBDA AI)
        
## I. INFORMASI UMUM
* **Mata Pelajaran:** {$subject}
* **Materi/Tema:** {$topic}
* **Tingkat/Kelas:** Kelas X (SMA/SMK)
* **Alokasi Waktu:** 2 x 45 Menit (1 Pertemuan)
* **Profil Pelajar Pancasila:** Gotong Royong, Bernalar Kritis, Mandiri

---

## II. KOMPONEN INTI
### A. Capaian & Tujuan Pembelajaran
Siswa mampu memahami, menganalisis, dan mengevaluasi konsep pokok terkait {$topic} secara mendalam serta mengaplikasikannya dalam kehidupan sehari-hari.

### B. Pemahaman Bermakna
{$topic} membantu kita mengenali keterkaitan sistematis dalam ilmu pengetahuan dan meningkatkan kepekaan analisis kritis.

---

## III. KEGIATAN PEMBELAJARAN
### 1. Pendahuluan (15 Menit)
Guru membuka kelas dengan doa, absensi, dan pertanyaan pemantik apersepsi.

### 2. Kegiatan Inti (60 Menit)
Siswa berkolaborasi kelompok membedah studi kasus {$topic} dan mempresentasikannya.

### 3. Penutup (15 Menit)
Refleksi pembelajaran bersama dan doa penutup.";
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
            ]
        ];
    }
}
