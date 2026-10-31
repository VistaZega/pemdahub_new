<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Circle Communities (Kelas, Ekskul & Squad).
     */
    public function up(): void
    {
        // 1. Tabel Komunitas Circle
        if (!Schema::hasTable('circle_communities')) {
            Schema::create('circle_communities', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('slug', 150)->unique()->index();
                $table->text('description')->nullable();
                $table->string('type', 30)->default('class'); // 'class', 'extracurricular', 'vocation', 'squad'
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->foreignId('extracurricular_id')->nullable()->constrained('extracurriculars')->nullOnDelete();
                $table->string('avatar_path', 255)->nullable();
                $table->string('banner_path', 255)->nullable();
                $table->string('theme_color', 30)->default('indigo');
                $table->string('icon', 50)->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_official')->default(true);
                $table->boolean('is_private')->default(false);
                $table->unsignedInteger('members_count')->default(0);
                $table->unsignedInteger('posts_count')->default(0);
                $table->timestamps();

                $table->index(['type', 'school_id']);
            });
        }

        // 2. Tabel Keanggotaan Komunitas
        if (!Schema::hasTable('circle_community_members')) {
            Schema::create('circle_community_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('community_id')->constrained('circle_communities')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role', 30)->default('member'); // 'leader', 'moderator', 'member'
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();

                $table->unique(['community_id', 'user_id']);
                $table->index('user_id');
            });
        }

        // 3. Relasi Community ke Postingan Circle
        if (Schema::hasTable('circle_posts')) {
            Schema::table('circle_posts', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_posts', 'community_id')) {
                    $table->foreignId('community_id')->nullable()->after('school_id')->constrained('circle_communities')->nullOnDelete();
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
                if (Schema::hasColumn('circle_posts', 'community_id')) {
                    $table->dropForeign(['community_id']);
                    $table->dropColumn('community_id');
                }
            });
        }
        Schema::dropIfExists('circle_community_members');
        Schema::dropIfExists('circle_communities');
    }
};
