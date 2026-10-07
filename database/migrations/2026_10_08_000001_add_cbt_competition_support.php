<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel cbt_competition_teams terlebih dahulu
        Schema::create('cbt_competition_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('cbt_exams')->onDelete('cascade');
            $table->string('team_name')->nullable()->comment('Nama tim kustom (misal: TIGER). Jika null, pakai nama kelas.');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Satu kelas hanya boleh punya satu tim per ujian
            $table->unique(['exam_id', 'classroom_id'], 'uq_competition_team_exam_class');
        });

        // 2. Buat tabel cbt_competition_team_members
        Schema::create('cbt_competition_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('cbt_competition_teams')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->boolean('is_operator')->default(false)->comment('Siswa yang login dan mengerjakan soal');
            $table->timestamps();

            // Satu siswa hanya boleh masuk satu tim per ujian ini
            $table->unique(['team_id', 'student_id'], 'uq_team_member_student');
        });

        // 3. Tambah kolom ke cbt_exams (scoring & participation mode)
        Schema::table('cbt_exams', function (Blueprint $table) {
            $table->enum('scoring_mode', ['standard', 'competition'])->default('standard')
                ->after('auto_sync_grade')
                ->comment('standard=nilai biasa, competition=+benar/-salah/0 kosong');
            $table->enum('participation_mode', ['class', 'individual', 'team'])->default('class')
                ->after('scoring_mode')
                ->comment('class=seluruh kelas, individual=pilih siswa, team=tim kelompok');
            $table->decimal('correct_points', 5, 2)->default(1)->after('participation_mode')
                ->comment('Poin untuk jawaban benar di mode kompetisi');
            $table->decimal('wrong_penalty', 5, 2)->default(0)->after('correct_points')
                ->comment('Penalti (positif) untuk jawaban salah. Diterapkan sebagai pengurangan.');
            $table->decimal('unanswered_points', 5, 2)->default(0)->after('wrong_penalty')
                ->comment('Poin untuk soal tidak dijawab (biasanya 0)');
        });

        // 4. Tambah FK ke cbt_exam_sessions untuk tim kompetisi
        Schema::table('cbt_exam_sessions', function (Blueprint $table) {
            $table->foreignId('competition_team_id')->nullable()
                ->after('classroom_id')
                ->constrained('cbt_competition_teams')
                ->onDelete('set null')
                ->comment('FK ke tim kompetisi, null jika bukan mode tim');
        });
    }

    public function down(): void
    {
        // Lepas FK dulu sebelum drop tabel
        Schema::table('cbt_exam_sessions', function (Blueprint $table) {
            $table->dropForeign(['competition_team_id']);
            $table->dropColumn('competition_team_id');
        });

        Schema::table('cbt_exams', function (Blueprint $table) {
            $table->dropColumn([
                'scoring_mode',
                'participation_mode',
                'correct_points',
                'wrong_penalty',
                'unanswered_points',
            ]);
        });

        Schema::dropIfExists('cbt_competition_team_members');
        Schema::dropIfExists('cbt_competition_teams');
    }
};
