<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->decimal('yayasan_share_amount', 15, 2)->nullable()->after('amount')->comment('Porsi nominal untuk setoran Yayasan, sisa dari amount (jika ada) adalah dana internal unit sekolah');
        });

        Schema::table('student_bills', function (Blueprint $table) {
            $table->decimal('yayasan_share_amount', 15, 2)->nullable()->after('amount')->comment('Snapshot porsi nominal untuk setoran Yayasan');
        });

        // Initialize yayasan_share_amount to be equal to amount for existing data
        Illuminate\Support\Facades\DB::statement('UPDATE payment_types SET yayasan_share_amount = amount');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropColumn('yayasan_share_amount');
        });

        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropColumn('yayasan_share_amount');
        });
    }
};
