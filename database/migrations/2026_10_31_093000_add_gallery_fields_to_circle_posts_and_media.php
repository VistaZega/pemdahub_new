<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for Personal Gallery & Auto-Broadcast.
     */
    public function up(): void
    {
        // 1. Modifikasi tab_category agar mendukung 'gallery' selain 'wall' dan 'portfolio'
        try {
            DB::statement("ALTER TABLE circle_posts MODIFY COLUMN tab_category VARCHAR(30) DEFAULT 'wall'");
        } catch (\Throwable $e) {
            // Fallback jika SQLite atau driver lain
        }

        // 2. Modifikasi media_type pada circle_post_media agar fleksibel untuk 'audio', 'video', 'image'
        try {
            DB::statement("ALTER TABLE circle_post_media MODIFY COLUMN media_type VARCHAR(30) DEFAULT 'image'");
        } catch (\Throwable $e) {
            // Fallback
        }

        // 3. Tambah kolom penanda galeri pada circle_posts
        if (Schema::hasTable('circle_posts')) {
            Schema::table('circle_posts', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_posts', 'is_gallery')) {
                    $table->boolean('is_gallery')->default(false)->after('tab_category');
                }
                if (!Schema::hasColumn('circle_posts', 'gallery_category')) {
                    $table->string('gallery_category', 20)->nullable()->after('is_gallery'); // 'photo', 'video', 'audio'
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('circle_posts')) {
            Schema::table('circle_posts', function (Blueprint $table) {
                if (Schema::hasColumn('circle_posts', 'gallery_category')) {
                    $table->dropColumn('gallery_category');
                }
                if (Schema::hasColumn('circle_posts', 'is_gallery')) {
                    $table->dropColumn('is_gallery');
                }
            });
        }
    }
};
