<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make subject_id nullable on lms_courses and add extracurricular_id if needed
        if (Schema::hasTable('lms_courses')) {
            // Use raw SQL for nullable column modification to avoid doctrine/dbal dependency issues on Hostinger
            try {
                DB::statement('ALTER TABLE `lms_courses` MODIFY `subject_id` BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // Ignore if already nullable
            }

            if (!Schema::hasColumn('lms_courses', 'extracurricular_id')) {
                Schema::table('lms_courses', function (Blueprint $table) {
                    $table->foreignId('extracurricular_id')
                        ->nullable()
                        ->after('subject_id')
                        ->constrained('extracurriculars')
                        ->nullOnDelete();
                });
            }
        }

        // 2. Make classroom_id and school_id nullable on lms_classes so Ekskul courses can have a cohort LmsClass without a specific Classroom
        if (Schema::hasTable('lms_classes')) {
            try {
                DB::statement('ALTER TABLE `lms_classes` MODIFY `classroom_id` BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('ALTER TABLE `lms_classes` MODIFY `school_id` BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
            }
        }

        // 3. Add lms_course_id on extracurriculars
        if (Schema::hasTable('extracurriculars') && !Schema::hasColumn('extracurriculars', 'lms_course_id')) {
            Schema::table('extracurriculars', function (Blueprint $table) {
                $table->foreignId('lms_course_id')
                    ->nullable()
                    ->after('forum_group_id')
                    ->constrained('lms_courses')
                    ->nullOnDelete();
            });
        }

        // 4. Add Extra Grade & Certificate columns on extracurricular_members
        if (Schema::hasTable('extracurricular_members')) {
            Schema::table('extracurricular_members', function (Blueprint $table) {
                if (!Schema::hasColumn('extracurricular_members', 'avg_assignment_score')) {
                    $table->decimal('avg_assignment_score', 5, 2)->nullable()->after('points_awarded');
                }
                if (!Schema::hasColumn('extracurricular_members', 'avg_quiz_score')) {
                    $table->decimal('avg_quiz_score', 5, 2)->nullable()->after('avg_assignment_score');
                }
                if (!Schema::hasColumn('extracurricular_members', 'attendance_score')) {
                    $table->decimal('attendance_score', 5, 2)->nullable()->after('avg_quiz_score');
                }
                if (!Schema::hasColumn('extracurricular_members', 'final_score')) {
                    $table->decimal('final_score', 5, 2)->nullable()->after('attendance_score');
                }
                if (!Schema::hasColumn('extracurricular_members', 'predicate')) {
                    $table->string('predicate', 30)->nullable()->after('final_score');
                }
                if (!Schema::hasColumn('extracurricular_members', 'grade_description')) {
                    $table->text('grade_description')->nullable()->after('predicate');
                }
                if (!Schema::hasColumn('extracurricular_members', 'graded_by')) {
                    $table->foreignId('graded_by')->nullable()->after('grade_description')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('extracurricular_members', 'graded_at')) {
                    $table->timestamp('graded_at')->nullable()->after('graded_by');
                }
                if (!Schema::hasColumn('extracurricular_members', 'certificate_code')) {
                    $table->string('certificate_code', 60)->nullable()->unique()->after('graded_at');
                }
                if (!Schema::hasColumn('extracurricular_members', 'certificate_issued_at')) {
                    $table->timestamp('certificate_issued_at')->nullable()->after('certificate_code');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('extracurricular_members')) {
            Schema::table('extracurricular_members', function (Blueprint $table) {
                $cols = [
                    'avg_assignment_score',
                    'avg_quiz_score',
                    'attendance_score',
                    'final_score',
                    'predicate',
                    'grade_description',
                    'graded_at',
                    'certificate_code',
                    'certificate_issued_at',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('extracurricular_members', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('extracurriculars') && Schema::hasColumn('extracurriculars', 'lms_course_id')) {
            Schema::table('extracurriculars', function (Blueprint $table) {
                $table->dropConstrainedForeignId('lms_course_id');
            });
        }

        if (Schema::hasTable('lms_courses') && Schema::hasColumn('lms_courses', 'extracurricular_id')) {
            Schema::table('lms_courses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('extracurricular_id');
            });
        }
    }
};
