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
        Schema::table('lms_quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_quizzes', 'question_package_id')) {
                $table->unsignedBigInteger('question_package_id')->nullable()->after('module_id');
            }
        });

        if (!Schema::hasTable('lms_student_achievements')) {
            Schema::create('lms_student_achievements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('badge_key');
                $table->string('badge_name');
                $table->string('badge_icon')->default('fa-medal');
                $table->timestamp('unlocked_at')->useCurrent();
                $table->timestamps();

                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->unique(['student_id', 'badge_key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_student_achievements');

        Schema::table('lms_quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('lms_quizzes', 'question_package_id')) {
                $table->dropColumn('question_package_id');
            }
        });
    }
};
