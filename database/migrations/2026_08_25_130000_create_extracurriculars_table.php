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
                $table->unsignedBigInteger('school_id')->nullable(); // null jika tingkat Yayasan
                $table->string('scope')->default('sekolah'); // 'sekolah' atau 'yayasan' (Marching Band dll.)
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('category')->default('umum'); // pramuka, paskibraka, seni_budaya, marching_band, olahraga, sains_it, keagamaan, jurnalistik, umum
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->default('indigo');
                $table->string('cover_image')->nullable();
                $table->string('schedule_day_time')->nullable();
                $table->string('location')->nullable();
                $table->string('manager_name')->nullable(); // Khusus unit Yayasan (Manager Marching Band dll.)
                $table->unsignedBigInteger('advisor_teacher_id')->nullable(); // Guru / Pembina Ekskul
                $table->string('advisor_name')->nullable();
                $table->unsignedBigInteger('leader_student_id')->nullable(); // Ketua / Field Commander Siswa
                $table->unsignedBigInteger('secretary_student_id')->nullable();
                $table->unsignedBigInteger('treasurer_student_id')->nullable();
                $table->json('available_sections')->nullable(); // Daftar section (misal: Tenor, Brass, dll.)
                $table->unsignedBigInteger('forum_group_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('school_id');
                $table->index('scope');
                $table->index('category');
                $table->index('is_active');
            });
        } else {
            Schema::table('extracurriculars', function (Blueprint $table) {
                if (!Schema::hasColumn('extracurriculars', 'scope')) {
                    $table->string('scope')->default('sekolah')->after('school_id');
                }
                if (!Schema::hasColumn('extracurriculars', 'manager_name')) {
                    $table->string('manager_name')->nullable()->after('location');
                }
                if (!Schema::hasColumn('extracurriculars', 'available_sections')) {
                    $table->json('available_sections')->nullable()->after('treasurer_student_id');
                }
            });
        }

        if (!Schema::hasTable('extracurricular_members')) {
            Schema::create('extracurricular_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('extracurricular_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('role')->default('anggota'); // ketua, wakil_ketua, sekretaris, bendahara, section_leader, anggota
                $table->string('section')->nullable(); // Tenor, Brass, Colour Guard, Bass, Bellyra, Marching Bells, Mayoret
                $table->string('status')->default('approved'); // pending, approved, rejected, alumni
                $table->date('joined_date')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->text('notes')->nullable();
                $table->integer('points_awarded')->default(0);
                $table->timestamps();

                $table->index(['extracurricular_id', 'student_id']);
                $table->index('status');
                $table->index('role');
                $table->index('section');
            });
        } else {
            Schema::table('extracurricular_members', function (Blueprint $table) {
                if (!Schema::hasColumn('extracurricular_members', 'section')) {
                    $table->string('section')->nullable()->after('role');
                }
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

                $table->index(['extracurricular_id', 'activity_date'], 'ekskul_act_id_date_idx');
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
