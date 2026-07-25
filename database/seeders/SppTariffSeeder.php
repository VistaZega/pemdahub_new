<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\SchoolContribution;
use App\Models\PaymentType;

class SppTariffSeeder extends Seeder
{
    public function run()
    {
        // 1. Cari Tahun Pelajaran 2026/2027
        $academicYear = AcademicYear::where('year', 'like', '%2026/2027%')->first();
        if (!$academicYear) {
            $academicYear = AcademicYear::where('is_active', true)->first();
        }

        if (!$academicYear) {
            return;
        }

        $cleanRatesSmkSma = [
            '10' => 210000,
            '11' => 215000,
            '12' => 220000,
        ];

        // 2. Update Tarif SPP untuk SMK
        $smkSchools = School::where('type', 'SMK')
            ->orWhere('name', 'like', '%SMK%')
            ->get();

        foreach ($smkSchools as $smk) {
            SchoolContribution::updateOrCreate(
                [
                    'school_id' => $smk->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'spp_rates' => $cleanRatesSmkSma,
                    'notes' => 'Update SPP TP 2026/2027 (X: Rp 210k, XI: Rp 215k, XII: Rp 220k)',
                ]
            );

            // Update Master PaymentType SPP
            PaymentType::where('school_id', $smk->id)
                ->where('type_code', 'SPP')
                ->update(['amount' => 210000]);
        }

        // 3. Update Tarif SPP untuk SMA
        $smaSchools = School::where('type', 'SMA')
            ->orWhere('name', 'like', '%SMA%')
            ->get();

        foreach ($smaSchools as $sma) {
            SchoolContribution::updateOrCreate(
                [
                    'school_id' => $sma->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'spp_rates' => $cleanRatesSmkSma,
                    'notes' => 'Update SPP TP 2026/2027 (X: Rp 210k, XI: Rp 215k, XII: Rp 220k)',
                ]
            );

            // Update Master PaymentType SPP
            PaymentType::where('school_id', $sma->id)
                ->where('type_code', 'SPP')
                ->update(['amount' => 210000]);
        }
    }
}
