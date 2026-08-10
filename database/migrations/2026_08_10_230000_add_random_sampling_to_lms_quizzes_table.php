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
        if (Schema::hasTable('lms_quizzes')) {
            Schema::table('lms_quizzes', function (Blueprint $table) {
                if (!Schema::hasColumn('lms_quizzes', 'question_sample_count')) {
                    $table->integer('question_sample_count')->nullable()->after('question_package_id')->comment('Jumlah soal yang diambil acak dari bank/pool soal');
                }
                if (!Schema::hasColumn('lms_quizzes', 'points_per_question')) {
                    $table->decimal('points_per_question', 5, 2)->nullable()->after('total_score')->comment('Poin per butir soal di kuis ini');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lms_quizzes')) {
            Schema::table('lms_quizzes', function (Blueprint $table) {
                if (Schema::hasColumn('lms_quizzes', 'question_sample_count')) {
                    $table->dropColumn('question_sample_count');
                }
                if (Schema::hasColumn('lms_quizzes', 'points_per_question')) {
                    $table->dropColumn('points_per_question');
                }
            });
        }
    }
};
