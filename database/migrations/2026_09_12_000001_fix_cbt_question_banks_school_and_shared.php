<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('cbt_question_banks') && Schema::hasTable('subjects')) {
            // 1. Sinkronisasi school_id cbt_question_banks dengan subjects.school_id jika tidak cocok (khusus MySQL)
            if (DB::getDriverName() === 'mysql') {
                DB::statement("
                    UPDATE cbt_question_banks cqb
                    INNER JOIN subjects s ON cqb.subject_id = s.id
                    SET cqb.school_id = s.school_id
                    WHERE cqb.school_id != s.school_id
                ");
            }

            // 2. Aktifkan is_shared = 1 untuk seluruh bank soal aktif agar dapat digunakan oleh guru dan admin di unit sekolah terkait
            DB::table('cbt_question_banks')
                ->where('is_active', true)
                ->update(['is_shared' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for data fix migration
    }
};
