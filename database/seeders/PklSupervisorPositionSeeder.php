<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder untuk menambahkan posisi "Pembimbing PKL" di SMK.
 * Posisi ini digunakan untuk guru yang ditugaskan membimbing
 * siswa kelas XII yang praktek di DUDI.
 *
 * Honor dihitung berdasarkan JP PKL (konversi dari jumlah siswa),
 * bukan flat bulanan. Rate default: Rp 43.000/JP.
 */
class PklSupervisorPositionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('📋 Seeding posisi Pembimbing PKL...');

        // Cari school_id SMK (school_id = 3 berdasarkan konvensi existing seeder)
        $smkSchool = DB::table('schools')->where('type', 'SMK')->first();
        $smkSchoolId = $smkSchool?->id ?? 3;

        $positions = [
            [
                'position_name'     => 'Pembimbing PKL',
                'position_code'     => 'PEMBIMBING-PKL',
                'position_category' => 'functional',
                'position_level'    => 3,
                'school_id'         => $smkSchoolId,
                'allowance_amount'  => 0, // Honor tidak flat; dihitung dari pkl_supervisor_hours × rate
                'is_structural'     => false,
                'is_active'         => true,
                'description'       => 'Guru pembimbing siswa PKL di DUDI. Honor dihitung per JP PKL (tarif Rp 43.000/JP), bukan flat bulanan.',
            ],
        ];

        $now = now();
        $created = 0;
        $updated = 0;

        foreach ($positions as $pos) {
            // Cek apakah sudah ada berdasarkan code + school_id
            $existing = DB::table('positions')
                ->where('position_code', $pos['position_code'])
                ->where('school_id', $pos['school_id'])
                ->first();

            if ($existing) {
                DB::table('positions')
                    ->where('id', $existing->id)
                    ->update(array_merge($pos, ['updated_at' => $now]));
                $this->command->line("  ↻ Updated: {$pos['position_name']} (ID: {$existing->id})");
                $updated++;
            } else {
                $id = DB::table('positions')->insertGetId(
                    array_merge($pos, ['created_at' => $now, 'updated_at' => $now])
                );
                $this->command->info("  ✅ Created: {$pos['position_name']} (ID: {$id}) untuk SMK (school_id: {$smkSchoolId})");
                $created++;
            }
        }

        // Tambah setting tarif PKL ke tabel settings (salary_formula group)
        $pklRateSetting = DB::table('settings')
            ->where('group', 'salary_formula')
            ->where('key', 'pkl_honor_rate')
            ->first();

        if (!$pklRateSetting) {
            DB::table('settings')->insert([
                'group'       => 'salary_formula',
                'key'         => 'pkl_honor_rate',
                'value'       => '43000',
                'description' => 'Tarif honor per Jam Pelajaran (JP) untuk guru yang ditugaskan sebagai Pembimbing PKL. Default: Rp 43.000/JP.',
                'type'        => 'number',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $this->command->info("  ✅ Setting pkl_honor_rate = Rp 43.000/JP ditambahkan.");
        } else {
            $this->command->line("  ↻ Setting pkl_honor_rate sudah ada (value: Rp {$pklRateSetting->value}/JP).");
        }

        $this->command->info("  → Selesai: {$created} created, {$updated} updated.");
    }
}
