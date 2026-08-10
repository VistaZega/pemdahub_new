<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembda_tower_bricks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('set null');
            $table->string('message', 140);
            $table->string('color', 20)->default('indigo'); // indigo, emerald, amber, rose, purple, cyan
            $table->integer('brick_number')->default(1);
            $table->integer('likes_count')->default(0);
            $table->timestamps();
        });

        Schema::create('pembda_tower_brick_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brick_id')->constrained('pembda_tower_bricks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['brick_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembda_tower_brick_likes');
        Schema::dropIfExists('pembda_tower_bricks');
    }
};
