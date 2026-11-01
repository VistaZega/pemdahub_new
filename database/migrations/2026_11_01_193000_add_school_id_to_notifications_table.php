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
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'school_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('school_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('schools')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'school_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }
    }
};
