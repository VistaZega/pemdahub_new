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
        // 1. Buat tabel lms_course_groups (Master Kelompok Tingkat Kursus)
        if (!Schema::hasTable('lms_course_groups')) {
            Schema::create('lms_course_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('lms_courses')->onDelete('cascade');
                $table->string('name', 100);
                $table->foreignId('leader_id')->nullable()->constrained('students')->onDelete('set null');
                $table->timestamps();

                $table->index('course_id');
                $table->index('leader_id');
            });
        }

        // 2. Buat tabel lms_course_group_members (Anggota Kelompok Kursus)
        if (!Schema::hasTable('lms_course_group_members')) {
            Schema::create('lms_course_group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_group_id')->constrained('lms_course_groups')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['course_group_id', 'student_id']);
                $table->index('student_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_course_group_members');
        Schema::dropIfExists('lms_course_groups');
    }
};
