<?php

namespace App\Services;

use App\Models\ForumThread;
use Carbon\Carbon;

class EduTechRadarService
{
    /**
     * Daftar wawasan EduTech & Sains terkurasi
     */
    public static function getCuratedInsights(): array
    {
        return [
            [
                'id' => 'ai-prompt-engineering',
                'category' => 'ai_tech',
                'category_label' => 'AI & REKAYASA DIGITAL',
                'badge_color' => 'bg-violet-100 text-violet-900 border-violet-300',
                'accent_color' => '#8b5cf6',
                'icon' => 'fa-solid fa-microchip',
                'title' => 'Seni Prompt Engineering: Mengapa Cara Bertanya ke AI Menentukan Kualitas Jawaban',
                'author' => 'Pusat Inovasi Digital Pembda',
                'read_time' => '3 Menit',
                'summary' => 'Kecerdasan Buatan (AI) bukan pengganti cara berpikir kita, melainkan pengungkit (leverage). Menguasai teknik prompting seperti "Role-Task-Context-Constraint" dapat melipatgandakan kecepatan riset karya ilmiah SMA dan debugging kode program kejuruan SMK.',
                'key_points' => [
                    'Berikan peran spesifik pada AI (contoh: "Bertindaklah sebagai teknisi jaringan senior...").',
                    'Sertakan batasan yang jelas agar hasil relevan dengan kebutuhan praktikum.',
                    'Gunakan untuk membedah konsep sulit, bukan sekadar copy-paste tugas.'
                ],
                'discussion_prompt' => 'Bagaimana kamu memanfaatkan AI untuk mempermudah praktikum atau tugas belajar di sekolah sejauh ini?',
                'suggested_tag' => 'AI & Prompt Engineering'
            ],
            [
                'id' => 'ev-motor-revolution',
                'category' => 'vokasi_smk',
                'category_label' => 'VOKASI & OTOMOTIF LISTRIK',
                'badge_color' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                'accent_color' => '#10b981',
                'icon' => 'fa-solid fa-bolt',
                'title' => 'Transisi Sepeda Motor Listrik: Peluang Emas Teknisi Otomotif SMK di Era Baru',
                'author' => 'Rumpun Otomotif & Mesin SMK',
                'read_time' => '4 Menit',
                'summary' => 'Sistem powertrain kendaraan listrik menggantikan karburator dan busi konvensional dengan Brushless DC Motor (BLDC) dan Battery Management System (BMS). Teknisi masa depan membutuhkan kombinasi pemahaman mekanikal dan kelistrikan digital.',
                'key_points' => [
                    'Peralihan dari tune-up mekanis ke diagnostik perangkat lunak dan sensor controller.',
                    'Manajemen keselamatan tegangan tinggi (High Voltage Safety) menjadi standar mutlak industri.',
                    'Potensi konversi kendaraan BBM lokal menjadi motor listrik ramah lingkungan di Nias.'
                ],
                'discussion_prompt' => 'Apakah jurusan otomotif dan kelistrikan kita siap memulai proyek konversi motor listrik sederhana?',
                'suggested_tag' => 'Otomotif Listrik SMK'
            ],
            [
                'id' => 'iot-gempa-nias',
                'category' => 'vokasi_smk',
                'category_label' => 'ARSITEKTUR & IOT',
                'badge_color' => 'bg-amber-100 text-amber-900 border-amber-300',
                'accent_color' => '#f59e0b',
                'icon' => 'fa-solid fa-house-signal',
                'title' => 'Harmoni Arsitektur Tahan Gempa Nias & Sensor IoT: Kolaborasi DPIB dan Teknik Elektronika',
                'author' => 'Laboratorium Rekayasa SMK Pembda',
                'read_time' => '3 Menit',
                'summary' => 'Rumah Adat Nias (Omo Hada) adalah mahakarya rekayasa gempa dunia dengan sambungan kayu elastis (pasak tanpa paku). Jika diintegrasikan dengan sensor akselerometer IoT dan mikrokontroler ESP32, kita bisa menciptakan sistem peringatan dini (Early Warning System) mandiri di sekolah.',
                'key_points' => [
                    'Kearifan lokal struktur Omo Hada sebagai rujukan utama desain 3D di DPIB.',
                    'Pemasangan sensor getaran berbasis mikrokontroler berbiaya terjangkau.',
                    'Peluang kolaborasi tim lintas jurusan: DPIB (struktur) + TE/TKJ (sensor & web alert).'
                ],
                'discussion_prompt' => 'Maukah kalian membentuk tim gabungan DPIB dan TE untuk membuat maket cerdas pendeteksi gempa?',
                'suggested_tag' => 'Kolaborasi DPIB & TE'
            ],
            [
                'id' => 'beasiswa-kip-kuliah-2026',
                'category' => 'beasiswa_karir',
                'category_label' => 'BEASISWA & KARIR MASA DEPAN',
                'badge_color' => 'bg-blue-100 text-blue-900 border-blue-300',
                'accent_color' => '#2563eb',
                'icon' => 'fa-solid fa-graduation-cap',
                'title' => 'Menembus PTN & Politeknik Impian: Strategi Portofolio dan Beasiswa Penuh 2026',
                'author' => 'Bimbingan Konseling & Karir Pembda',
                'read_time' => '4 Menit',
                'summary' => 'Seleksi Nasional Berdasarkan Prestasi (SNBP) dan program beasiswa perguruan tinggi kini sangat memprioritaskan rekam jejak portofolio nyata, sertifikasi kompetensi, serta keaktifan riset/karya ilmiah selama di bangku SMA/SMK.',
                'key_points' => [
                    'Rapor konsisten didukung karya nyata di LMS dan showcase PembdaSPACE.',
                    'Sertifikasi keahlian industri dan portofolio proyek tugas akhir menjadi nilai pembeda.',
                    'Peluang beasiswa vokasi ikatan dinas di perusahaan manufaktur & teknologi nasional.'
                ],
                'discussion_prompt' => 'Apa jurusan impianmu di perguruan tinggi atau politeknik, dan apa persiapan portofolio yang sedang kamu bangun?',
                'suggested_tag' => 'Info Beasiswa & Karir'
            ],
            [
                'id' => 'sains-energi-terbarukan-nias',
                'category' => 'sains_sma',
                'category_label' => 'SAINS ALAM & ENERGI',
                'badge_color' => 'bg-cyan-100 text-cyan-900 border-cyan-300',
                'accent_color' => '#06b6d4',
                'icon' => 'fa-solid fa-atom',
                'title' => 'Potensi Energi Surya & Mikrohidro: Topik Riset Sains Unggulan untuk Siswa SMA',
                'author' => 'Kelompok Riset Sains SMA Pembda',
                'read_time' => '3 Menit',
                'summary' => 'Kepulauan Nias memiliki intensitas radiasi matahari dan aliran sungai yang melimpah. Meneliti efisiensi sel surya dengan sudut kemiringan optimum atau turbin air mini dapat menjadi karya ilmiah yang sangat kompetitif untuk lomba sains tingkat nasional.',
                'key_points' => [
                    'Menentukan variabel bebas (sudut panel surya, debit air) dan variabel terikat (tegangan output).',
                    'Menggunakan alat ukur multimeter dan sensor cahaya sederhana dalam pengujian.',
                    'Dampak nyata bagi solusi kemandirian energi di wilayah kepulauan.'
                ],
                'discussion_prompt' => 'Ada ide eksperimen sains lain dari alam Nias yang ingin kamu uji bersama guru pembimbing?',
                'suggested_tag' => 'Riset Sains SMA'
            ],
            [
                'id' => 'cloud-network-security',
                'category' => 'ai_tech',
                'category_label' => 'JARINGAN & CLOUD COMPUTING',
                'badge_color' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                'accent_color' => '#4f46e5',
                'icon' => 'fa-solid fa-shield-halved',
                'title' => 'Keamanan Jaringan & Zero Trust: Pondasi Wajib Teknisi Komputer Jaringan (TKJ)',
                'author' => 'Tim Kejuruan TKJ Pembda',
                'read_time' => '3 Menit',
                'summary' => 'Prinsip "Never Trust, Always Verify" kini menjadi standar arsitektur keamanan dunia cyber. Lulusan TKJ yang menguasai firewall Mikrotik, segmentasi VLAN, dan otentikasi multi-faktor memiliki daya tawar karir yang sangat tinggi di industri digital.',
                'key_points' => [
                    'Pemahaman isolasi port dan keamanan WiFi publik dari serangan Man-In-The-Middle.',
                    'Penerapan VPN terenkripsi untuk akses server jarak jauh yang aman.',
                    'Latihan simulasi jaringan menggunakan Packet Tracer dan routerboard fisik di lab.'
                ],
                'discussion_prompt' => 'Bagaimana langkah kalian mengamankan jaringan Wi-Fi sekolah atau rumah dari akses liar?',
                'suggested_tag' => 'Cyber Security TKJ'
            ]
        ];
    }

    /**
     * Ambil wawasan harian yang aktif berdasarkan tanggal hari ini
     */
    public static function getActiveInsights(int $limit = 4): array
    {
        $all = self::getCuratedInsights();
        $dayOfYear = Carbon::now()->dayOfYear;
        
        // Rotasikan wawasan secara mulus sesuai hari
        $startIndex = $dayOfYear % count($all);
        $rotated = array_merge(array_slice($all, $startIndex), array_slice($all, 0, $startIndex));
        
        return array_slice($rotated, 0, $limit);
    }

    /**
     * Dapatkan Tantangan STEAM Mingguan aktif
     */
    public static function getCurrentWeeklyChallenge(): array
    {
        // Cek apakah ada thread resmi bertipe steam_challenge di database yang masih aktif
        $dbChallenge = ForumThread::where('category', 'steam_challenge')
            ->where('is_locked', false)
            ->with(['user', 'replies.user'])
            ->latest()
            ->first();

        if ($dbChallenge) {
            return [
                'is_db' => true,
                'thread' => $dbChallenge,
                'title' => $dbChallenge->title,
                'description' => $dbChallenge->content,
                'author_name' => $dbChallenge->user->name ?? 'Guru Inovasi STEAM',
                'author_avatar' => $dbChallenge->user->avatar_url ?? null,
                'replies_count' => $dbChallenge->replies->count(),
                'points_reward' => '+35 Poin Reputasi',
                'deadline_text' => 'Batas Waktu: Minggu Ini',
                'thread_id' => $dbChallenge->id,
            ];
        }

        // Fallback default challenge minggu ini
        return [
            'is_db' => false,
            'thread' => null,
            'title' => 'Tantangan STEAM #1: Rancang Prototipe Teknologi Tepat Guna untuk Lingkungan Sekolah',
            'description' => 'Ayo siswa SMP, SMA & SMK Pembda! Buat rancangan, sketsa, foto instalasi, atau kode sederhana yang dapat menyelesaikan 1 masalah nyata di sekolah kita (contoh: penghematan listrik, daur ulang sampah, detektor hujan/gempa, atau aplikasi pembantu belajar). Kirimkan ulasan dan foto karyamu!',
            'author_name' => 'Tim Pembina STEAM Yayasan Pembda',
            'author_avatar' => null,
            'replies_count' => 0,
            'points_reward' => '+35 Poin Reputasi',
            'deadline_text' => 'Tantangan Aktif Minggu Ini',
            'thread_id' => null,
        ];
    }
}
