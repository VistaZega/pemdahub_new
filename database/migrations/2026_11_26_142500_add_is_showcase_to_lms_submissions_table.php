<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lms_submissions') && !Schema::hasColumn('lms_submissions', 'is_showcase')) {
            Schema::table('lms_submissions', function (Blueprint $table) {
                $table->boolean('is_showcase')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lms_submissions') && Schema::hasColumn('lms_submissions', 'is_showcase')) {
            Schema::table('lms_submissions', function (Blueprint $table) {
                $table->dropColumn('is_showcase');
            });
        }
    }
};
