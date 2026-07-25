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
        Schema::table('lms_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_submissions', 'revision_notes')) {
                $table->text('revision_notes')->nullable()->after('feedback');
            }
            if (!Schema::hasColumn('lms_submissions', 'graded_at')) {
                $table->timestamp('graded_at')->nullable()->after('revision_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('lms_submissions', 'graded_at')) {
                $table->dropColumn('graded_at');
            }
            if (Schema::hasColumn('lms_submissions', 'revision_notes')) {
                $table->dropColumn('revision_notes');
            }
        });
    }
};
