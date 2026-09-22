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
        // 1. Tambah kolom theme pada lms_course_groups jika belum ada
        if (Schema::hasTable('lms_course_groups') && !Schema::hasColumn('lms_course_groups', 'theme')) {
            Schema::table('lms_course_groups', function (Blueprint $table) {
                $table->string('theme', 255)->nullable()->after('name');
            });
        }

        // 2. Tambah kolom theme pada lms_assignment_groups jika belum ada
        if (Schema::hasTable('lms_assignment_groups') && !Schema::hasColumn('lms_assignment_groups', 'theme')) {
            Schema::table('lms_assignment_groups', function (Blueprint $table) {
                $table->string('theme', 255)->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lms_course_groups') && Schema::hasColumn('lms_course_groups', 'theme')) {
            Schema::table('lms_course_groups', function (Blueprint $table) {
                $table->dropColumn('theme');
            });
        }

        if (Schema::hasTable('lms_assignment_groups') && Schema::hasColumn('lms_assignment_groups', 'theme')) {
            Schema::table('lms_assignment_groups', function (Blueprint $table) {
                $table->dropColumn('theme');
            });
        }
    }
};
