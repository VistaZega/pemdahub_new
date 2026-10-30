<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for PembdaCIRCLE.
     */
    public function up(): void
    {
        // 1. Profil Pengguna Circle (@username)
        if (!Schema::hasTable('circle_profiles')) {
            Schema::create('circle_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('username', 60)->unique()->index();
                $table->string('nickname', 100)->nullable();
                $table->text('bio')->nullable();
                $table->string('theme_color', 30)->default('indigo');
                $table->string('anthem_url', 255)->nullable();
                $table->string('mood_text', 100)->nullable();
                $table->string('mood_emoji', 20)->nullable();
                $table->string('banner_path', 255)->nullable();
                $table->boolean('is_public')->default(true);
                $table->unsignedInteger('views_count')->default(0);
                $table->json('social_links')->nullable();
                $table->timestamps();

                $table->index('is_public');
            });
        }

        // 2. Postingan Circle (Nongki, Story 24h, Reels, Project, DevLog)
        if (!Schema::hasTable('circle_posts')) {
            Schema::create('circle_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('post_type', ['status', 'story', 'reel', 'project', 'devlog'])->default('status');
                $table->enum('tab_category', ['wall', 'portfolio'])->default('wall');
                $table->string('title', 255)->nullable();
                $table->string('slug', 255)->nullable()->index();
                $table->longText('content')->nullable();
                $table->string('mood_emoji', 20)->nullable();
                $table->string('mood_text', 100)->nullable();
                $table->string('location_tag', 100)->nullable();
                $table->json('tagged_user_ids')->nullable();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->string('major_code', 20)->nullable();
                $table->enum('visibility', ['public', 'school_only', 'circle_only'])->default('public');
                $table->boolean('is_pinned')->default(false);
                $table->timestamp('expires_at')->nullable()->index(); // Untuk story 24 jam
                $table->unsignedInteger('likes_count')->default(0);
                $table->unsignedInteger('comments_count')->default(0);
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamps();

                $table->index(['school_id', 'visibility', 'created_at'], 'idx_circle_school_feed');
                $table->index(['tab_category', 'post_type', 'created_at'], 'idx_circle_type_feed');
            });
        }

        // 3. Media Postingan (Gambar, Video Reels, Slider Before-After, 3D CAD, Audio)
        if (!Schema::hasTable('circle_post_media')) {
            Schema::create('circle_post_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_post_id')->constrained('circle_posts')->cascadeOnDelete();
                $table->enum('media_type', ['image', 'video', 'before_after', 'model_3d', 'audio'])->default('image');
                $table->string('file_path', 255);
                $table->string('thumbnail_path', 255)->nullable();
                $table->string('secondary_path', 255)->nullable(); // Khusus foto 'After'
                $table->string('aspect_ratio', 10)->default('1:1'); // '9:16', '1:1', '16:9', '4:5'
                $table->json('metadata')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // 4. Reaksi & Apresiasi Edukatif (Lit, Clap, Nongki, Love, Proud Parent)
        if (!Schema::hasTable('circle_reactions')) {
            Schema::create('circle_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_post_id')->constrained('circle_posts')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('guest_ip', 45)->nullable();
                $table->string('guest_name', 100)->nullable();
                $table->enum('reaction_type', ['lit', 'clap', 'nongki', 'love', 'proud_parent'])->default('lit');
                $table->timestamp('created_at')->nullable();

                $table->index(['circle_post_id', 'reaction_type']);
            });
        }

        // 5. Komentar & Percakapan Santai / Apresiasi
        if (!Schema::hasTable('circle_comments')) {
            Schema::create('circle_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_post_id')->constrained('circle_posts')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('circle_comments')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('author_name', 100);
                $table->boolean('is_guest')->default(false);
                $table->text('comment_text');
                $table->boolean('is_flagged')->default(false);
                $table->timestamps();

                $table->index(['circle_post_id', 'created_at']);
            });
        }

        // 6. Validasi Keahlian Guru (Skill Endorsement)
        if (!Schema::hasTable('circle_skill_endorsements')) {
            Schema::create('circle_skill_endorsements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_post_id')->nullable()->constrained('circle_posts')->cascadeOnDelete();
                $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('skill_name', 100);
                $table->text('endorsement_note')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['student_user_id', 'skill_name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_skill_endorsements');
        Schema::dropIfExists('circle_comments');
        Schema::dropIfExists('circle_reactions');
        Schema::dropIfExists('circle_post_media');
        Schema::dropIfExists('circle_posts');
        Schema::dropIfExists('circle_profiles');
    }
};
