<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('extracurriculars')) {
            Schema::create('extracurriculars', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->nullable();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('category')->default('umum'); // pramuka, paskibraka, seni_budaya, olahraga, sains_it, keagamaan, jurnalistik, umum
                $table->text('description')->nullable();
                $table->string('icon')->nullable(); // fa icon or emoji
                $table->string('color')->default('indigo'); // tailwind color prefix
                $table->string('cover_image')->nullable();
                $table->string('schedule_day_time')->nullable(); // e.g. "Jumat, 15:00 - 17:00"
                $table->string('location')->nullable(); // e.g. "Lapangan Utama Pembda"
                $table->unsignedBigInteger('advisor_teacher_id')->nullable(); // Guru / Pembina Ekskul
                $table->string('advisor_name')->nullable(); // Nama pembina (manual/eksternal)
                $table->unsignedBigInteger('leader_student_id')->nullable(); // Ketua / Koordinator Siswa
                $table->unsignedBigInteger('secretary_student_id')->nullable(); // Sekretaris Siswa
                $table->unsignedBigInteger('treasurer_student_id')->nullable(); // Bendahara Siswa
                $table->unsignedBigInteger('forum_group_id')->nullable(); // Link otomatis ke Space
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('school_id');
                $table->index('category');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('extracurricular_members')) {
            Schema::create('extracurricular_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('extracurricular_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('role')->default('anggota'); // ketua, wakil_ketua, sekretaris, bendahara, sie_kegiatan, anggota
                $table->string('status')->default('approved'); // pending, approved, rejected, alumni
                $table->date('joined_date')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->text('notes')->nullable();
                $table->integer('points_awarded')->default(0);
                $table->timestamps();

                $table->index(['extracurricular_id', 'student_id']);
                $table->index('status');
                $table->index('role');
            });
        }

        if (!Schema::hasTable('extracurricular_activities')) {
            Schema::create('extracurricular_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('extracurricular_id');
                $table->string('title');
                $table->date('activity_date');
                $table->string('location')->nullable();
                $table->text('description')->nullable();
                $table->json('photos')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['extracurricular_id', 'activity_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_activities');
        Schema::dropIfExists('extracurricular_members');
        Schema::dropIfExists('extracurriculars');
    }
};
