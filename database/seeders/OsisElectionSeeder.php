<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\OsisElection;
use App\Models\OsisCandidate;
use Carbon\Carbon;

class OsisElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari unit SMK Swasta Pemda Nias
        $smkSchool = School::where('name', 'LIKE', '%SMK%')->first() 
            ?? School::schoolsOnly()->first();

        if (!$smkSchool) {
            $this->command->error('Sekolah SMK tidak ditemukan.');
            return;
        }

        // Ambil Academic Year aktif
        $academicYear = AcademicYear::where('is_active', true)->first();

        // 1. Buat / Update Event Pemilihan OSIS SMK 2026/2027
        $election = OsisElection::updateOrCreate(
            ['slug' => 'pemilihan-osis-smk-2026-2027'],
            [
                'school_id' => $smkSchool->id,
                'academic_year_id' => $academicYear?->id,
                'title' => 'PEMILIHAN OSIS ' . strtoupper($smkSchool->name),
                'period' => '2026 / 2027',
                'theme_tagline' => 'Bersama Membangun Masa Depan yang Lebih Baik',
                'description' => 'Pemilihan Umum Ketua, Sekretaris, dan Bendahara OSIS masa bakti 2026/2027 secara langsung, umum, bebas, rahasia, jujur, dan adil melalui sistem E-Voting PembdaHUB.',
                'banner_image' => 'images/osis/osis_banner_smk.jpg',
                'start_time' => Carbon::now()->subDays(2)->setHour(7)->setMinute(0)->setSecond(0),
                'end_time' => Carbon::now()->addDays(2)->setHour(15)->setMinute(0)->setSecond(0),
                'status' => 'voting_open',
                'allow_teachers' => true,
                'allow_staff' => true,
                'show_realtime_count' => false,
                'is_active' => true,
            ]
        );

        // 2. Data Paslon Resmi Sesuai Mockup
        $candidates = [
            [
                'candidate_number' => 1,
                'candidate_type' => 'presidium_ksb',
                'leader_name' => 'Yosefin Dinanti Mendrofa',
                'secretary_name' => 'Firdo Daniel Yanti Gulo',
                'treasurer_name' => 'Setiawan Halawa',
                'quote_slogan' => 'Mewujudkan OSIS SMK Pembda sebagai wadah aspirasi yang SIGAP (Semangat Industri, Generasi ber-Akhlak, dan Produktif) serta adaptif terhadap perkembangan teknologi digital di era modern.',
                'vision' => 'Menjadikan OSIS SMK Swasta Pemda Nias sebagai organisasi yang adaptif, kolaboratif, berintegritas tinggi, dan mampu mencetak generasi berkarakter unggul serta siap kerja.',
                'mission' => [
                    'Meningkatkan kedisiplinan dan kepedulian sosial seluruh civitas SMK.',
                    'Mengoptimalkan peran ekstrakurikuler dan kompetisi kejuruan tingkat daerah dan nasional.',
                    'Menciptakan ruang aspirasi terbuka dan digital bagi seluruh siswa.',
                    'Menjaga keharmonisan dan solidaritas antarsiswa serta guru.',
                ],
                'work_programs' => [
                    'Digital Student Feedback: Kotak aspirasi online civitas sekolah.',
                    'Pembda Tech & Skill Expo: Pameran inovasi karya siswa kejuruan tahunan.',
                    'Green School Initiative: Gerakan peduli lingkungan & kebersihan bengkel/lab.',
                ],
                'color_badge' => '#ef4444',
            ],
            [
                'candidate_number' => 2,
                'candidate_type' => 'presidium_ksb',
                'leader_name' => 'FILMAN J.H. HAREFA',
                'secretary_name' => 'Adriel M. B. Silitonga',
                'treasurer_name' => 'Diva S. Zendrato',
                'quote_slogan' => 'Membangun OSIS sebagai ruang bertumbuh, tempat setiap suara memiliki arti, setiap potensi menemukan arah, dan setiap aksi melahirkan perubahan nyata, berlandaskan karakter, budaya, serta semangat inovasi.',
                'vision' => 'Mewujudkan OSIS yang inklusif, aspiratif, dan berdaya saing global dengan menjunjung tinggi kearifan budaya lokal dan etika kejuruan.',
                'mission' => [
                    'Memperkuat karakter siswa melalui kegiatan kepemimpinan dan keagamaan.',
                    'Menghubungkan potensi siswa dengan peluang magang industri dan wirausaha kreatif.',
                    'Membangun komunikasi aktif dua arah antara siswa, OSIS, dan manajemen sekolah.',
                    'Mendukung literasi digital dan penguasaan teknologi terbarukan di SMK.',
                ],
                'work_programs' => [
                    'OSIS Leadership Camp: Pelatihan kepemimpinan dasar dan manajemen organisasi.',
                    'Pojok Kreativitas Siswa: Wadah apresiasi karya seni, desain, dan teknologi.',
                    'Liga Olahraga & E-Sport Antarkelas (Classmeeting Championship).',
                ],
                'color_badge' => '#3b82f6',
            ],
            [
                'candidate_number' => 3,
                'candidate_type' => 'presidium_ksb',
                'leader_name' => 'Juang J. Harefa',
                'secretary_name' => 'Monica B. E. Zendrato',
                'treasurer_name' => 'Delki P. Halawa',
                'quote_slogan' => 'Terwujudnya OSIS sebagai organisasi yang aktif dan inovatif dalam mengoptimalkan potensi siswa dan menciptakan lingkungan sekolah yang tertib, solid, serta mampu meraih prestasi menjadi lebih baik.',
                'vision' => 'Terwujudnya OSIS SMK Pemda Nias yang sinergis, inovatif, solid, dan berorientasi pada peningkatan prestasi akademik maupun non-akademik.',
                'mission' => [
                    'Mengembangkan potensi bakat dan minat siswa secara optimal.',
                    'Menciptakan lingkungan belajar yang nyaman, tertib, dan berprestasi.',
                    'Menumbuhkan rasa persaudaraan dan kekeluargaan yang kuat di lingkungan sekolah.',
                    'Menjalin kemitraan positif dengan OSIS sekolah lain dan komunitas pemuda.',
                ],
                'work_programs' => [
                    'Smart Mentoring: Bimbingan sebaya untuk persiapan UKK dan sertifikasi industri.',
                    'Pembda Peduli Sesama: Aksi bakti sosial dan tanggap bencana.',
                    'Bulletin OSIS Digital: Media warta berita dan inspirasi prestasi siswa.',
                ],
                'color_badge' => '#eab308',
            ],
            [
                'candidate_number' => 4,
                'candidate_type' => 'presidium_ksb',
                'leader_name' => 'Ivan P. Waruwu',
                'secretary_name' => 'Angelina A. Mendrofa',
                'treasurer_name' => 'Yafiel B. Telaumbanua',
                'quote_slogan' => 'Mewujudkan OSIS yang responsif, transparan dan berdampak nyata melalui perubahan kecil yang konsisten demi menciptakan lingkungan sekolah yang baik.',
                'vision' => 'Menciptakan kepengurusan OSIS yang transparan, responsif, berjiwa wirausaha muda, dan membawa dampak positif nyata bagi seluruh warga sekolah.',
                'mission' => [
                    'Menampung dan memperjuangkan setiap aspirasi siswa secara transparan.',
                    'Menggalakkan kegiatan kewirausahaan siswa (Teaching Factory / Student Entrepreneur).',
                    'Meningkatkan kedisiplinan dan rasa tanggung jawab siswa dalam menjaga fasilitas sekolah.',
                    'Membudayakan sikap 5S (Senyum, Sapa, Salam, Sopan, Santun) di lingkungan sekolah.',
                ],
                'work_programs' => [
                    'Kantin Kejujuran & Merchandise OSIS: Penguatan dana mandiri organisasi.',
                    'Revitalisasi Mading & Media Sosial OSIS yang interaktif.',
                    'Workshop Soft Skill: Public Speaking & Personal Branding untuk siswa tingkat akhir.',
                ],
                'color_badge' => '#10b981',
            ],
        ];

        foreach ($candidates as $candidateData) {
            OsisCandidate::updateOrCreate(
                [
                    'election_id' => $election->id,
                    'candidate_number' => $candidateData['candidate_number'],
                ],
                array_merge($candidateData, [
                    'photo' => 'images/osis/paslon_' . $candidateData['candidate_number'] . '.jpg',
                    'poster_flyer' => 'images/osis/poster_' . $candidateData['candidate_number'] . '.jpg',
                ])
            );
        }

        $this->command->info('Seeder Pemilihan OSIS SMK Swasta Pemda Nias berhasil dijalankan!');
    }
}
