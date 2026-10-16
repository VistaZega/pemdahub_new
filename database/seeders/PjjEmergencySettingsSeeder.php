<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PjjEmergencySettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setValue('pjj_enabled', true, 'boolean', 'features');
        Setting::setValue('pjj_school_ids', [2, 7], 'json', 'features'); // SMAS Pembda 1 (2) & SMKS Pembda Nias (7)
        Setting::setValue('pjj_start_date', '2026-10-16', 'string', 'features');
        Setting::setValue('pjj_end_date', '2026-10-21', 'string', 'features');
        Setting::setValue('pjj_allow_teachers', true, 'boolean', 'features');
        Setting::setValue('pjj_auto_lms_attendance', true, 'boolean', 'features');
        Setting::setValue(
            'pjj_reason',
            'Pemberlakuan PJJ Tanggap Darurat Bencana Kabut Asap (Surat Edaran No. 07/SE/YP-PEMBDA/X/2026)',
            'string',
            'features'
        );
    }
}
