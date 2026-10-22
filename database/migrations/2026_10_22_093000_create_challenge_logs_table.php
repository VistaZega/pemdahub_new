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
        if (!Schema::hasTable('challenge_logs')) {
            Schema::create('challenge_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('game_key', 50)->default('missionaries_cannibals');
                $table->integer('moves')->default(0);
                $table->integer('lives_left')->default(3);
                $table->integer('stars')->default(1);
                $table->integer('points_awarded')->default(0);
                $table->integer('seconds_taken')->default(0);
                $table->string('status', 20)->default('won'); // won, failed
                $table->timestamps();

                $table->index(['user_id', 'game_key', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_logs');
    }
};
