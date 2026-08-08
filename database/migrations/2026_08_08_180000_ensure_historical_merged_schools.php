<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Standar 5 Unit Sekolah Pembda (Aktif & Merger/Historis)
        $schools = [
            [
                'name' => 'SMK Swasta Pembda Nias',
                'type' => 'SMK',
                'is_active' => true,
                'psb_is_active' => true,
                'city' => 'Gunungsitoli',
                'province' => 'Sumatera Utara',
            ],
            [
                'name' => 'SMAS Pembda 1 Gunungsitoli',
                'type' => 'SMA',
                'is_active' => true,
                'psb_is_active' => true,
                'city' => 'Gunungsitoli',
                'province' => 'Sumatera Utara',
            ],
            [
                'name' => 'SMAS Pembda 2 Gunungsitoli',
                'type' => 'SMA',
                'is_active' => false,
                'psb_is_active' => false,
                'city' => 'Gunungsitoli',
                'province' => 'Sumatera Utara',
            ],
            [
                'name' => 'SMPS Pembda 2 Gunungsitoli',
                'type' => 'SMP',
                'is_active' => true,
                'psb_is_active' => true,
                'city' => 'Gunungsitoli',
                'province' => 'Sumatera Utara',
            ],
            [
                'name' => 'SMPS Pembda 1 Gunungsitoli',
                'type' => 'SMP',
                'is_active' => false,
                'psb_is_active' => false,
                'city' => 'Gunungsitoli',
                'province' => 'Sumatera Utara',
            ],
        ];

        foreach ($schools as $school) {
            $existing = DB::table('schools')
                ->where('name', $school['name'])
                ->orWhere('name', 'LIKE', '%' . str_replace(['SMAS ', 'SMPS '], ['SMA ', 'SMP '], $school['name']) . '%')
                ->first();

            if ($existing) {
                // Pastikan nama seragam dengan format SMAS / SMPS / SMK
                DB::table('schools')->where('id', $existing->id)->update([
                    'name' => $school['name'],
                    'type' => $school['type'],
                ]);
            } else {
                DB::table('schools')->insert($school);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destruct on rollback
    }
};
