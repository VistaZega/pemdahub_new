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
        Schema::table('classrooms', function (Blueprint $table) {
            if (!Schema::hasColumn('classrooms', 'shift')) {
                $table->string('shift', 20)->default('pagi')->after('grade_level')->comment('pagi, siang, all');
            }
        });

        Schema::table('time_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('time_slots', 'shift')) {
                $table->string('shift', 20)->default('pagi')->after('slot_name')->comment('pagi, siang, all');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            if (Schema::hasColumn('classrooms', 'shift')) {
                $table->dropColumn('shift');
            }
        });

        Schema::table('time_slots', function (Blueprint $table) {
            if (Schema::hasColumn('time_slots', 'shift')) {
                $table->dropColumn('shift');
            }
        });
    }
};
