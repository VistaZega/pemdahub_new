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
        if (Schema::hasTable('lms_submissions') && !Schema::hasColumn('lms_submissions', 'file_paths')) {
            Schema::table('lms_submissions', function (Blueprint $table) {
                $table->json('file_paths')->nullable()->after('file_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lms_submissions') && Schema::hasColumn('lms_submissions', 'file_paths')) {
            Schema::table('lms_submissions', function (Blueprint $table) {
                $table->dropColumn('file_paths');
            });
        }
    }
};
