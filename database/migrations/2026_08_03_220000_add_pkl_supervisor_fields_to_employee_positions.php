<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom untuk mendukung penugasan Pembimbing PKL.
     * pkl_supervisor_hours: jumlah JP PKL yang dibimbing guru (konversi dari jumlah siswa).
     * pkl_honor_rate: tarif per JP PKL (default Rp 43.000, bisa dioverride per assignment).
     */
    public function up(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->integer('pkl_supervisor_hours')
                ->default(0)
                ->after('workload_hours')
                ->comment('Jam Pelajaran PKL yang dibimbing (konversi dari jumlah siswa)');

            $table->decimal('pkl_honor_rate', 12, 2)
                ->default(43000)
                ->after('pkl_supervisor_hours')
                ->comment('Tarif honor PKL per JP (default 43.000)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->dropColumn(['pkl_supervisor_hours', 'pkl_honor_rate']);
        });
    }
};
