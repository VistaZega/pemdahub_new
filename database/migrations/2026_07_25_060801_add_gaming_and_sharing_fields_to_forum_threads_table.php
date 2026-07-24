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
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->string('game_name')->nullable()->after('status');
            $table->string('game_room_code')->nullable()->after('game_name');
            $table->string('file_category')->nullable()->after('game_room_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropColumn(['game_name', 'game_room_code', 'file_category']);
        });
    }
};
