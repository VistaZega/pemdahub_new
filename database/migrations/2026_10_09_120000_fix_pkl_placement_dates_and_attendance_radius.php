<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\School;
use App\Models\PklPlacement;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbarui batas radius presensi Kompleks Pembda menjadi standar ketat 90 meter
        //    (Mencakup petak sekolah 75x75m dan toleransi gerbang/halaman, mencegah absen dari warung/luar sekolah)
        try {
            Setting::setValue('attendance_max_radius', 90, 'integer', 'features');
            Setting::setValue('school_latitude', '1.28127778', 'string', 'features');
            Setting::setValue('school_longitude', '97.62566667', 'string', 'features');
        } catch (\Throwable $e) {}

        // 2. Pastikan koordinat seluruh unit sekolah (SMK, SMA, SMP) terisi dengan koordinat Kompleks Kampus Pembda
        try {
            if (Schema::hasTable('schools')) {
                School::whereNull('latitude')
                    ->orWhere('latitude', 0)
                    ->orWhereNull('longitude')
                    ->orWhere('longitude', 0)
                    ->update([
                        'latitude' => 1.28127778,
                        'longitude' => 97.62566667,
                    ]);
            }
        } catch (\Throwable $e) {}

        // 3. Perpanjang end_date penempatan PKL berstatus active yang berakhir pada 2026-10-08 atau sebelumnya
        //    menjadi akhir Oktober 2026 (2026-10-31) untuk memberikan masa transisi penarikan DUDI dan administrasi logbook
        try {
            if (Schema::hasTable('pkl_placements')) {
                PklPlacement::where('status', 'active')
                    ->whereDate('end_date', '<=', '2026-10-08')
                    ->whereDate('end_date', '>=', '2026-09-01')
                    ->update([
                        'end_date' => '2026-10-31 00:00:00',
                    ]);
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
