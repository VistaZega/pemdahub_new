<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\PaymentType;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Seeding jenis tagihan bulanan 'Iuran OSIS' sebesar Rp 10.000/bulan untuk seluruh unit sekolah.
     */
    public function up(): void
    {
        $schools = School::where('is_active', true)->where('type', '!=', 'yayasan')->get();

        foreach ($schools as $school) {
            PaymentType::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'type_code' => 'OSIS',
                ],
                [
                    'type_name' => 'Iuran OSIS',
                    'description' => 'Iuran Organisasi Siswa Intra Sekolah (OSIS) Bulanan',
                    'amount' => 10000.00,
                    'yayasan_share_amount' => 0.00,
                    'is_recurring' => true,
                    'allow_installment' => false,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PaymentType::where('type_code', 'OSIS')->delete();
    }
};
