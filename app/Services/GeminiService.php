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
        $this->apiKey = config('services.gemini.key') 
            ?: (\App\Models\Setting::getValue('gemini_api_key') ?? env('GEMINI_API_KEY'));
    }

    /**
     * Generate content from prompt.
     */
    public function generateText(string $prompt): string
    {
        if (empty($this->apiKey)) {
            Log::info('Gemini API Key is not configured. Utilizing Pembda AI Smart Local Engine.');
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
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty($text)) {
                    return $text;
                }
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

        // Math / Science Formula
        if (str_contains($lowered, 'rumus') || str_contains($lowered, 'matematika') || str_contains($lowered, 'fisika') || str_contains($lowered, 'hitung') || str_contains($lowered, 'luas') || str_contains($lowered, 'keliling') || str_contains($lowered, 'tabung')) {
            return '### 🧮 Pembahasan Konsep Matematika & Sains' . "\n\n"
                . 'Mari kita bedah langkah demi langkah pemecahan masalah akademis ini:' . "\n\n"
                . '#### 1. Rumus Luas Permukaan & Volume Tabung' . "\n"
                . '$$Luas\ Permukaan = 2 \times \pi \times r \times (r + t)$$' . "\n"
                . '$$Volume = \pi \times r^2 \times t$$' . "\n\n"
                . '#### 2. Langkah Penyelesaian (Step-by-Step):' . "\n"
                . '* **Langkah 1:** Identifikasi variabel yang diketahui dari soal (Jari-jari alas $r$ dan Tinggi tabung $t$).' . "\n"
                . '* **Langkah 2:** Masukkan nilai $r$ dan $t$ ke dalam rumus luas permukaan di atas.' . "\n"
                . '* **Langkah 3:** Gunakan $\pi = \frac{22}{7}$ jika $r$ kelipatan 7, atau $\pi = 3.14$ untuk angka desimal lainnya.' . "\n\n"
                . '💡 **Tips:** Latihlah pengerjaan soal secara rutin di menu **LMS / Kuis Mandiri** agar semakin lancar!';
        }

        // BK / Career / Mental Health Consultation
        if (str_contains($lowered, 'kuliah') || str_contains($lowered, 'karir') || str_contains($lowered, 'dudi') || str_contains($lowered, 'bingung') || str_contains($lowered, 'kerja') || str_contains($lowered, 'motivasi') || str_contains($lowered, 'depresi') || str_contains($lowered, 'lelah')) {
            return "### 🎓 Panduan Bimbingan Karir & Motivasi Belajar\n\n"
                . "Setiap langkah belajar Anda di Perguruan PEMBDA adalah investasi berharga untuk masa depan!\n\n"
                . "* **Untuk Siswa SMA:** Fokuslah pada pemetaan minat jurusan perguruan tinggi (Teknik, Sains, Ekonomi, atau Pendidikan) sesuai potensi akademik di **DNA Akademik 360°**.\n"
                . "* **Untuk Siswa SMK:** Manfaatkan pengalaman **Praktik Kerja (PKL)** dan sertifikasi keahlian jurusan (TAV/DPIB/TKR/TSM/TKJ) untuk mempersiapkan diri langsung ke dunia kerja DUDI atau melanjutkan kuliah kejuruan.\n\n"
                . "🌱 *Ingat, keberhasilan ditentukan oleh konsistensi dan kerja keras harian Anda.*";
        }

        // PembdaHUB Ecosystem Info
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

        // Math / Science Formula
        if (str_contains($lowered, 'rumus') || str_contains($lowered, 'matematika') || str_contains($lowered, 'fisika') || str_contains($lowered, 'hitung') || str_contains($lowered, 'luas') || str_contains($lowered, 'keliling')) {
            return "### 🧮 Pembahasan Konsep Matematika & Sains\n\n"
                . "Mari kita bedah langkah demi langkah pemecahan masalah akademis ini:\n\n"
                . "#### 1. Rumus Utama\n"
                . "$$Luas\\ Permukaan = 2 \\times \\pi \\times r \\times (r + t)$$\n"
                . "$$Keliling = 2 \\times \\pi \\times r$$\n\n"
                . "#### 2. Langkah Penyelesaian (Step-by-Step):\n"
                . "* **Langkah 1:** Identifikasi variabel yang diketahui dari soal (Jari-jari $r$, Tinggi $t$).\n"
                . "* **Langkah 2:** Masukkan nilai variabel ke dalam rumus di atas.\n"
                . "* **Langkah 3:** Gunakan nilai $\\pi = \\frac{22}{7}$ jika kelipatan 7, atau $\\pi = 3.14$.\n\n"
                . "💡 **Tips:** Latihlah pengerjaan soal secara rutin di menu **LMS / Kuis Mandiri** agar semakin lancar!";
        }

        // BK / Career Consultation
        if (str_contains($lowered, 'kuliah') || str_contains($lowered, 'karir') || str_contains($lowered, 'dudi') || str_contains($lowered, 'bingung') || str_contains($lowered, 'kerja') || str_contains($lowered, 'motivasi')) {
            return "### 🎓 Panduan Bimbingan Karir & Masa Depan\n\n"
                . "Setiap langkah belajar Anda di Perguruan PEMBDA adalah investasi berharga untuk masa depan!\n\n"
                . "* **Untuk Siswa SMA:** Fokuslah pada pemetaan minat jurusan perguruan tinggi (Teknik, Sains, Ekonomi, atau Pendidikan) sesuai potensi akademik di **DNA Akademik 360°**.\n"
                . "* **Untuk Siswa SMK:** Manfaatkan pengalaman **Praktik Kerja (PKL)** dan sertifikasi keahlian jurusan (TAV/DPIB/TKR/TSM/TKJ) untuk mempersiapkan diri langsung ke dunia kerja DUDI atau melanjutkan kuliah kejuruan.\n\n"
                . "🌱 *Ingat, keberhasilan ditentukan oleh konsistensi dan kerja keras harian Anda.*";
        }

        // General AI Response
        return "### 🤖 Pembda AI Assistant\n\n"
            . "Terima kasih telah bertanya! Sebagai asisten belajar cerdas PembdaHUB, saya siap membantu Anda memahami konsep pelajaran, bimbingan karir, serta navigasi sistem sekolah.\n\n"
            . "Silakan ajukan pertanyaan lebih spesifik atau pilih salah satu topik di bawah:\n"
            . "* 💡 *Penjelasan soal & konsep pelajaran (Matematika, IPA, Kejuruan)*\n"
            . "* 🎓 *Konsultasi minat bakat & pilihan karir (SMA/SMK)*\n"
            . "* 🏫 *Panduan penggunaan fitur-fitur di portal PembdaHUB*";
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

### C. Pertanyaan Pemantik
1. Apa yang Anda ketahui tentang {$topic}?
2. Mengapa hal ini penting untuk dipelajari dalam konteks kehidupan kita?

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
