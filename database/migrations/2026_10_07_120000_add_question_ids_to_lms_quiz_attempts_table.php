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
        if (Schema::hasTable('lms_quiz_attempts')) {
            Schema::table('lms_quiz_attempts', function (Blueprint $table) {
                if (!Schema::hasColumn('lms_quiz_attempts', 'question_ids')) {
                    $table->json('question_ids')->nullable()->after('student_id')->comment('Daftar ID butir soal yang diacak/disajikan untuk percobaan kuis ini');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lms_quiz_attempts')) {
            Schema::table('lms_quiz_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('lms_quiz_attempts', 'question_ids')) {
                    $table->dropColumn('question_ids');
                }
            });
        }
    }
};
