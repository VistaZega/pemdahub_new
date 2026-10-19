<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReputationBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Always Present',
                'code' => 'attendance_master',
                'icon' => 'fa-calendar-check',
                'color' => 'bg-emerald-500',
                'description' => 'Untuk yang tidak pernah absen dalam 1 bulan.',
                'requirement_type' => 'points',
                'requirement_value' => 500,
            ],
            [
                'name' => 'Quiz Whiz',
                'code' => 'lms_quiz_master',
                'icon' => 'fa-lightbulb',
                'color' => 'bg-amber-500',
                'description' => 'Mendapatkan nilai sempurna di 10 Quiz LMS.',
                'requirement_type' => 'points',
                'requirement_value' => 1000,
            ],
            [
                'name' => 'Top Payer',
                'code' => 'finance_hero',
                'icon' => 'fa-coins',
                'color' => 'bg-blue-500',
                'description' => 'Membayar tagihan selalu tepat waktu selama setahun.',
                'requirement_type' => 'points',
                'requirement_value' => 800,
            ],
            [
                'name' => 'Model Citizen',
                'code' => 'zero_offense',
                'icon' => 'fa-shield-halved',
                'color' => 'bg-indigo-500',
                'description' => 'Tidak memiliki catatan pelanggaran dalam satu semester.',
                'requirement_type' => 'points',
                'requirement_value' => 1500,
            ],
            [
                'name' => 'Virtual Educator',
                'code' => 'virtual_educator',
                'icon' => 'fa-chalkboard-user',
                'color' => 'bg-rose-500',
                'description' => 'Guru inovatif yang aktif menyelenggarakan KBM interaktif di PembdaTEMU.',
                'requirement_type' => 'points',
                'requirement_value' => 300,
            ],
            [
                'name' => 'Live Learner',
                'code' => 'live_learner',
                'icon' => 'fa-video',
                'color' => 'bg-sky-500',
                'description' => 'Siswa interaktif yang hadir aktif dalam pembelajaran virtual PembdaTEMU.',
                'requirement_type' => 'points',
                'requirement_value' => 250,
            ],
        ];

        foreach ($badges as $badge) {
            \App\Models\Badge::updateOrCreate(['code' => $badge['code']], $badge);
        }
    }
}
