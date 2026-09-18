<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('cbt_exams', 'requires_tuition_payment')) {
            // Pastikan seluruh ujian yang ada/sedang berlangsung tidak mewajibkan kepatuhan SPP (false/0)
            // agar seluruh siswa dapat mengikuti ujian dengan lancar tanpa terganggu
            DB::table('cbt_exams')
                ->whereNull('requires_tuition_payment')
                ->orWhere('requires_tuition_payment', 1)
                ->update(['requires_tuition_payment' => false]);

            try {
                DB::statement("ALTER TABLE `cbt_exams` MODIFY `requires_tuition_payment` TINYINT(1) NOT NULL DEFAULT 0");
            } catch (\Throwable $e) {
                // Fallback jika platform DB engine memiliki aturan berbeda
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
