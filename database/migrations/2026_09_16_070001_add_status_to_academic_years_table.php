<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->after('is_active')->comment('Status: aktif, nonaktif, archived');
        });

        // Sync existing data: is_active=true → status='aktif'
        DB::table('academic_years')
            ->where('is_active', true)
            ->whereNull('status')
            ->update(['status' => 'aktif']);

        DB::table('academic_years')
            ->where('is_active', false)
            ->whereNull('status')
            ->update(['status' => 'nonaktif']);
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
