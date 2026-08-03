<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom honor_pkl ke employee_workload_summaries
     * untuk menyimpan total honor pembimbing PKL per semester.
     */
    public function up(): void
    {
        Schema::table('employee_workload_summaries', function (Blueprint $table) {
            $table->decimal('honor_pkl', 12, 2)
                ->default(0)
                ->after('total_teaching_allowance')
                ->comment('Honor Pembimbing PKL = pkl_supervisor_hours × pkl_honor_rate');

            $table->integer('pkl_supervisor_hours')
                ->default(0)
                ->after('honor_pkl')
                ->comment('Total JP PKL yang dibimbing dalam semester ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_workload_summaries', function (Blueprint $table) {
            $table->dropColumn(['honor_pkl', 'pkl_supervisor_hours']);
        });
    }
};
