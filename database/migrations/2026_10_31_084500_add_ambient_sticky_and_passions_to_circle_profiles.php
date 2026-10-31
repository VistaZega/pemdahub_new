<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Option A: Self Expression enhancements.
     */
    public function up(): void
    {
        if (Schema::hasTable('circle_profiles')) {
            Schema::table('circle_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_profiles', 'ambient_effect')) {
                    $table->string('ambient_effect', 30)->default('none')->after('theme_color');
                }
                if (!Schema::hasColumn('circle_profiles', 'sticky_note_text')) {
                    $table->string('sticky_note_text', 255)->nullable()->after('ambient_effect');
                }
                if (!Schema::hasColumn('circle_profiles', 'sticky_note_color')) {
                    $table->string('sticky_note_color', 20)->default('yellow')->after('sticky_note_text');
                }
                if (!Schema::hasColumn('circle_profiles', 'passions')) {
                    $table->json('passions')->nullable()->after('sticky_note_color');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('circle_profiles')) {
            Schema::table('circle_profiles', function (Blueprint $table) {
                $columns = ['ambient_effect', 'sticky_note_text', 'sticky_note_color', 'passions'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('circle_profiles', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
