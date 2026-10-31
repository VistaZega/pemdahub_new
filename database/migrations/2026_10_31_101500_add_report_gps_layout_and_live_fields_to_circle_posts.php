<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for Activity Report, GPS, Smart Media Layout & Live Reporting.
     */
    public function up(): void
    {
        // 1. Pastikan post_type mendukung tipe baru ('media', 'report', 'live')
        try {
            DB::statement("ALTER TABLE circle_posts MODIFY COLUMN post_type VARCHAR(30) DEFAULT 'status'");
        } catch (\Throwable $e) {
            // Fallback
        }

        if (Schema::hasTable('circle_posts')) {
            Schema::table('circle_posts', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_posts', 'media_layout')) {
                    $table->string('media_layout', 30)->default('bottom')->after('gallery_category'); // 'top', 'bottom', 'magazine', 'carousel'
                }
                if (!Schema::hasColumn('circle_posts', 'event_name')) {
                    $table->string('event_name', 180)->nullable()->after('location_tag');
                }
                if (!Schema::hasColumn('circle_posts', 'event_date')) {
                    $table->dateTime('event_date')->nullable()->after('event_name');
                }
                if (!Schema::hasColumn('circle_posts', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable()->after('event_date');
                }
                if (!Schema::hasColumn('circle_posts', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                }
                if (!Schema::hasColumn('circle_posts', 'is_live')) {
                    $table->boolean('is_live')->default(false)->after('longitude');
                }
                if (!Schema::hasColumn('circle_posts', 'live_status')) {
                    $table->string('live_status', 20)->nullable()->after('is_live'); // 'live', 'ended'
                }
                if (!Schema::hasColumn('circle_posts', 'live_stream_url')) {
                    $table->string('live_stream_url', 500)->nullable()->after('live_status');
                }
                if (!Schema::hasColumn('circle_posts', 'live_updates')) {
                    $table->json('live_updates')->nullable()->after('live_stream_url');
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
                $cols = [
                    'media_layout',
                    'event_name',
                    'event_date',
                    'latitude',
                    'longitude',
                    'is_live',
                    'live_status',
                    'live_stream_url',
                    'live_updates',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('circle_posts', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
