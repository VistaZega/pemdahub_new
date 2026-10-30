<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for PembdaCIRCLE Interactive Polls.
     */
    public function up(): void
    {
        // 1. Tabel Induk Polling
        if (!Schema::hasTable('circle_polls')) {
            Schema::create('circle_polls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_post_id')->constrained('circle_posts')->cascadeOnDelete();
                $table->string('question', 500);
                $table->timestamp('closes_at')->nullable();
                $table->timestamps();

                $table->index('circle_post_id');
            });
        }

        // 2. Opsi Pilihan Polling
        if (!Schema::hasTable('circle_poll_options')) {
            Schema::create('circle_poll_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_poll_id')->constrained('circle_polls')->cascadeOnDelete();
                $table->string('option_text', 255);
                $table->unsignedInteger('votes_count')->default(0);
                $table->timestamps();

                $table->index('circle_poll_id');
            });
        }

        // 3. Rekam Suara (Voting)
        if (!Schema::hasTable('circle_poll_votes')) {
            Schema::create('circle_poll_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_poll_id')->constrained('circle_polls')->cascadeOnDelete();
                $table->foreignId('circle_poll_option_id')->constrained('circle_poll_options')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('guest_ip', 45)->nullable();
                $table->timestamps();

                $table->index(['circle_poll_id', 'user_id']);
                $table->index(['circle_poll_id', 'guest_ip']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_poll_votes');
        Schema::dropIfExists('circle_poll_options');
        Schema::dropIfExists('circle_polls');
    }
};
