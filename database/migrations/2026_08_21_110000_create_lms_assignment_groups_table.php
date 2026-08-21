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
        // 1. Tambah kolom is_group_assignment pada lms_assignments jika belum ada
        if (Schema::hasTable('lms_assignments') && !Schema::hasColumn('lms_assignments', 'is_group_assignment')) {
            Schema::table('lms_assignments', function (Blueprint $table) {
                $table->boolean('is_group_assignment')->default(false)->after('assignment_type');
            });
        }

        // 2. Buat tabel lms_assignment_groups (Daftar Kelompok Tugas)
        if (!Schema::hasTable('lms_assignment_groups')) {
            Schema::create('lms_assignment_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained('lms_assignments')->onDelete('cascade');
                $table->string('name', 100);
                $table->foreignId('leader_id')->nullable()->constrained('students')->onDelete('set null');
                $table->timestamps();

                $table->index('assignment_id');
                $table->index('leader_id');
            });
        }

        // 3. Buat tabel lms_assignment_group_members (Anggota Kelompok)
        if (!Schema::hasTable('lms_assignment_group_members')) {
            Schema::create('lms_assignment_group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('lms_assignment_groups')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['group_id', 'student_id']);
                $table->index('student_id');
            });
        }

        // 4. Tambah kolom group_id pada lms_submissions jika belum ada
        if (Schema::hasTable('lms_submissions') && !Schema::hasColumn('lms_submissions', 'group_id')) {
            Schema::table('lms_submissions', function (Blueprint $table) {
                $table->foreignId('group_id')->nullable()->after('student_id')->constrained('lms_assignment_groups')->onDelete('cascade');
                $table->index('group_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lms_submissions') && Schema::hasColumn('lms_submissions', 'group_id')) {
            Schema::table('lms_submissions', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            });
        }

        Schema::dropIfExists('lms_assignment_group_members');
        Schema::dropIfExists('lms_assignment_groups');

        if (Schema::hasTable('lms_assignments') && Schema::hasColumn('lms_assignments', 'is_group_assignment')) {
            Schema::table('lms_assignments', function (Blueprint $table) {
                $table->dropColumn('is_group_assignment');
            });
        }
    }
};
