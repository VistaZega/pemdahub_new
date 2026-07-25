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
        Schema::table('lms_discussion_replies', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_discussion_replies', 'is_best_answer')) {
                $table->boolean('is_best_answer')->default(false)->after('content');
            }
            if (!Schema::hasColumn('lms_discussion_replies', 'is_teacher_verified')) {
                $table->boolean('is_teacher_verified')->default(false)->after('is_best_answer');
            }
            if (!Schema::hasColumn('lms_discussion_replies', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('is_teacher_verified');
            }
        });

        Schema::table('lms_discussions', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_discussions', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_discussions', function (Blueprint $table) {
            if (Schema::hasColumn('lms_discussions', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
        });

        Schema::table('lms_discussion_replies', function (Blueprint $table) {
            if (Schema::hasColumn('lms_discussion_replies', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
            if (Schema::hasColumn('lms_discussion_replies', 'is_teacher_verified')) {
                $table->dropColumn('is_teacher_verified');
            }
            if (Schema::hasColumn('lms_discussion_replies', 'is_best_answer')) {
                $table->dropColumn('is_best_answer');
            }
        });
    }
};
