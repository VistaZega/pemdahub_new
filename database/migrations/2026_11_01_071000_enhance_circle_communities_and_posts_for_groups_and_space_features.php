<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to support full PembdaSPACE group features in PembdaCIRCLE:
     * Kelas, Sanggar, Ekstrakurikuler, Rumpun Kejuruan SMK, Angkatan, Alumni, Voice Note, Document Attachment & Academic Reference.
     */
    public function up(): void
    {
        if (Schema::hasTable('circle_communities')) {
            Schema::table('circle_communities', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_communities', 'cohort_year')) {
                    $table->integer('cohort_year')->nullable()->after('extracurricular_id')->index();
                }
                if (!Schema::hasColumn('circle_communities', 'major_code')) {
                    $table->string('major_code', 30)->nullable()->after('cohort_year')->index();
                }
                if (!Schema::hasColumn('circle_communities', 'only_admin_can_post')) {
                    $table->boolean('only_admin_can_post')->default(false)->after('is_private');
                }
                if (!Schema::hasColumn('circle_communities', 'metadata')) {
                    $table->json('metadata')->nullable()->after('posts_count');
                }
            });
        }

        if (Schema::hasTable('circle_community_members')) {
            Schema::table('circle_community_members', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_community_members', 'title_badge')) {
                    $table->string('title_badge', 80)->nullable()->after('role');
                }
            });
        }

        if (Schema::hasTable('circle_posts')) {
            Schema::table('circle_posts', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_posts', 'attachment_path')) {
                    $table->string('attachment_path', 255)->nullable()->after('live_updates');
                }
                if (!Schema::hasColumn('circle_posts', 'attachment_name')) {
                    $table->string('attachment_name', 255)->nullable()->after('attachment_path');
                }
                if (!Schema::hasColumn('circle_posts', 'reference_type')) {
                    $table->string('reference_type', 100)->nullable()->after('attachment_name');
                }
                if (!Schema::hasColumn('circle_posts', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
                }
                if (!Schema::hasColumn('circle_posts', 'recruitment_meta')) {
                    $table->json('recruitment_meta')->nullable()->after('reference_id');
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
                foreach (['attachment_path', 'attachment_name', 'reference_type', 'reference_id', 'recruitment_meta'] as $col) {
                    if (Schema::hasColumn('circle_posts', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('circle_community_members')) {
            Schema::table('circle_community_members', function (Blueprint $table) {
                if (Schema::hasColumn('circle_community_members', 'title_badge')) {
                    $table->dropColumn('title_badge');
                }
            });
        }

        if (Schema::hasTable('circle_communities')) {
            Schema::table('circle_communities', function (Blueprint $table) {
                foreach (['cohort_year', 'major_code', 'only_admin_can_post', 'metadata'] as $col) {
                    if (Schema::hasColumn('circle_communities', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
