<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use App\Models\ForumGroup;
use App\Models\School;
use App\Models\Teacher;
use App\Services\ExtracurricularService;
use Illuminate\Database\Seeder;

class ExtracurricularSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::schoolsOnly()->get();
        if ($schools->isEmpty()) {
            $schools = School::where('type', '!=', 'yayasan')->get();
        }

        $ekskulService = app(ExtracurricularService::class);

        $templates = [
            [
                'name_suffix' => 'Gugus Depan Gerakan Pramuka',
                'category' => 'pramuka',
                'description' => 'Pembinaan karakter mandiri, kepemimpinan kepanduan, kedisiplinan, dan kecakapan bertahan hidup di alam terbuka.',
                'icon' => '⚜️',
                'color' => 'amber',
                'schedule_day_time' => 'Jumat, 15:00 - 17:00',
                'location' => 'Lapangan Utama Pembda',
            ],
            [
                'name_suffix' => 'Korps Paskibraka Satria',
                'category' => 'paskibraka',
                'description' => 'Pelatihan baris-berbaris presisi, ketahanan fisik, patriotisme, dan persiapan pengibaran bendera upacara resmi & HUT RI.',
                'icon' => '🇮🇩',
                'color' => 'rose',
                'schedule_day_time' => 'Sabtu, 07:30 - 10:00',
                'location' => 'Lapangan Upacara Pembda',
            ],
            [
                'name_suffix' => 'Sanggar Seni & Budaya Ono Niha',
                'category' => 'seni_budaya',
                'description' => 'Eksplorasi tari tradisional Nias (Maena, Moyo, Baluse), musik tradisional Nias, vokal grup, dan seni pertunjukan kontemporer.',
                'icon' => '🎭',
                'color' => 'purple',
                'schedule_day_time' => 'Kamis, 14:30 - 16:30',
                'location' => 'Aula Serbaguna & Ruang Sanggar',
            ],
            [
                'name_suffix' => 'Marching Band Gita',
                'category' => 'seni_budaya',
                'description' => 'Korp musik instrumental, brass band, percussion, dan colour guard untuk festival dan upacara resmi Yayasan Pembda.',
                'icon' => '🥁',
                'color' => 'indigo',
                'schedule_day_time' => 'Jumat, 14:00 - 16:30',
                'location' => 'Pelataran Gedung Pembda',
            ],
            [
                'name_suffix' => 'Klub Futsal & Atletik',
                'category' => 'olahraga',
                'description' => 'Pengembangan bakat olahraga futsal, bola voli, bulutangkis, dan kebugaran jasmani untuk turnamen antarsekolah.',
                'icon' => '⚽',
                'color' => 'emerald',
                'schedule_day_time' => 'Rabu & Sabtu, 15:30 - 17:30',
                'location' => 'Lapangan Olahraga Pembda',
            ],
            [
                'name_suffix' => 'IT & Robotic Club',
                'category' => 'sains_it',
                'description' => 'Eksplorasi coding pemrograman, desain web, perakitan IoT hardware, grafis multimedia, dan kejuaraan LKS IT.',
                'icon' => '💻',
                'color' => 'blue',
                'schedule_day_time' => 'Selasa, 14:30 - 16:30',
                'location' => 'Lab Komputer & Internet Pembda',
            ],
            [
                'name_suffix' => 'Sains & Cerdas Cermat Club',
                'category' => 'sains_it',
                'description' => 'Bimbingan intensif olimpiade sains (OSN Matematika, IPA, IPS) dan lomba ketajaman nalar akademik.',
                'icon' => '🔬',
                'color' => 'cyan',
                'schedule_day_time' => 'Senin, 14:30 - 16:00',
                'location' => 'Ruang Belajar Multimedia',
            ],
            [
                'name_suffix' => 'Persekutuan Rohani (Rohkris & Rohis)',
                'category' => 'keagamaan',
                'description' => 'Pembinaan iman dan taqwa, kebaktian berkala siswa, paduan suara rohani, dan bakti sosial kepedulian.',
                'icon' => '✝️',
                'color' => 'teal',
                'schedule_day_time' => 'Jumat, 12:30 - 14:00',
                'location' => 'Ruang Ibadah & Aula Pembda',
            ],
        ];

        foreach ($schools as $sch) {
            $shortName = $sch->short_name ?: $sch->name;

            foreach ($templates as $tpl) {
                $fullName = $tpl['name_suffix'] . ' ' . $shortName;

                $existing = Extracurricular::where('school_id', $sch->id)
                    ->where('name', $fullName)
                    ->first();

                if (!$existing) {
                    $advisorTeacher = Teacher::where('school_id', $sch->id)->first();

                    $ekskulService->createExtracurricular([
                        'school_id' => $sch->id,
                        'name' => $fullName,
                        'category' => $tpl['category'],
                        'description' => $tpl['description'],
                        'icon' => $tpl['icon'],
                        'color' => $tpl['color'],
                        'schedule_day_time' => $tpl['schedule_day_time'],
                        'location' => $tpl['location'],
                        'advisor_teacher_id' => $advisorTeacher?->id,
                        'advisor_name' => $advisorTeacher?->full_name ?: 'PKS Kesiswaan ' . $shortName,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
