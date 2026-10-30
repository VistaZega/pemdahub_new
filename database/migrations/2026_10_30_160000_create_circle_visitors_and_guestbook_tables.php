<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for PembdaCIRCLE Profile Visitors and Wall Guestbook.
     */
    public function up(): void
    {
        // 1. Jejak Pengunjung Profil ("Siapa yang Baru Mampir?")
        if (!Schema::hasTable('circle_profile_visitors')) {
            Schema::create('circle_profile_visitors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_profile_id')->constrained('circle_profiles')->cascadeOnDelete();
                $table->foreignId('visitor_user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('visit_count')->default(1);
                $table->timestamp('last_visited_at')->nullable();
                $table->timestamps();

                $table->unique(['circle_profile_id', 'visitor_user_id'], 'uniq_profile_visitor');
                $table->index(['circle_profile_id', 'last_visited_at'], 'idx_profile_recent_visits');
            });
        }

        // 2. Buku Tamu & Stiker Dinding ("Wall Notes & Sticker Reaction")
        if (!Schema::hasTable('circle_profile_guestbooks')) {
            Schema::create('circle_profile_guestbooks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_profile_id')->constrained('circle_profiles')->cascadeOnDelete();
                $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('sticker_code', 50)->default('menyala');
                $table->string('sticker_emoji', 20)->default('🔥');
                $table->string('sticker_label', 100)->default('Menyala!');
                $table->string('message', 500)->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->timestamps();

                $table->index(['circle_profile_id', 'created_at'], 'idx_profile_guestbook_date');
                $table->index(['circle_profile_id', 'is_pinned'], 'idx_profile_guestbook_pinned');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_profile_guestbooks');
        Schema::dropIfExists('circle_profile_visitors');
    }
};
