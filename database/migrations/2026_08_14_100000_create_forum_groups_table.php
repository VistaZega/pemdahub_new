<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('forum_groups')) {
            Schema::create('forum_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->nullable();
                $table->unsignedBigInteger('classroom_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->default('purple');
                $table->string('type')->default('classroom'); // classroom, subject, extracurricular, lobby, broadcast
                $table->boolean('is_official')->default(true);
                $table->boolean('only_admin_can_post')->default(false);
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('forum_group_members')) {
            Schema::create('forum_group_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role')->default('member'); // admin, moderator, member
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('last_read_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('forum_threads', 'group_id')) {
            Schema::table('forum_threads', function (Blueprint $table) {
                $table->unsignedBigInteger('group_id')->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_group_members');
        Schema::dropIfExists('forum_groups');
        if (Schema::hasColumn('forum_threads', 'group_id')) {
            Schema::table('forum_threads', function (Blueprint $table) {
                $table->dropColumn('group_id');
            });
        }
    }
};
