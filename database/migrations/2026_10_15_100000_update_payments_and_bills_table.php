<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel payments: buat bill_id nullable & payment_method fleksibel
        try {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `bill_id` BIGINT UNSIGNED NULL");
        } catch (\Throwable $e) {
            // ignore if already null or driver differences
        }

        try {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cash'");
        } catch (\Throwable $e) {
            // ignore
        }

        // 2. Tambah kolom discount_amount dan discount_reason di tabel student_bills jika belum ada
        Schema::table('student_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('student_bills', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('student_bills', 'discount_reason')) {
                $table->string('discount_reason', 255)->nullable()->after('discount_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            if (Schema::hasColumn('student_bills', 'discount_reason')) {
                $table->dropColumn('discount_reason');
            }
            if (Schema::hasColumn('student_bills', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });

        try {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `payment_method` ENUM('cash','transfer','qris') NOT NULL DEFAULT 'cash'");
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
