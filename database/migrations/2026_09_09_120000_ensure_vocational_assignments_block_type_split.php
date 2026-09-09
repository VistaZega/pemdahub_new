<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memastikan seluruh Penugasan Mengajar Konsentrasi Keahlian (Praktik / Lab) SMK berstatus block_type = 'split'.
     */
    public function up(): void
    {
        $vocSubjectIds = DB::table('subjects')
            ->where(function ($q) {
                $q->where('name', 'like', '%Konsentrasi%')
                  ->orWhere('name', 'like', '%Kosentrasi%')
                  ->orWhere('name', 'like', '%KK-%');
            })
            ->pluck('id')
            ->toArray();

        if (!empty($vocSubjectIds)) {
            DB::table('teaching_assignments')
                ->whereIn('subject_id', $vocSubjectIds)
                ->where('block_type', 'all')
                ->update(['block_type' => 'split']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
