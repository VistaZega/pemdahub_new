<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('virtual_labs') && !Schema::hasColumn('virtual_labs', 'room_key')) {
            Schema::table('virtual_labs', function (Blueprint $table) {
                $table->string('room_key', 30)->nullable()->after('category')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('virtual_labs') && Schema::hasColumn('virtual_labs', 'room_key')) {
            Schema::table('virtual_labs', function (Blueprint $table) {
                $table->dropColumn('room_key');
            });
        }
    }
};
