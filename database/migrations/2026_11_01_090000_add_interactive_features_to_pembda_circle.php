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
        // 1. Perluas kolom tabel circle_posts untuk Wahana Inisiatif Siswa
        if (Schema::hasTable('circle_posts')) {
            Schema::table('circle_posts', function (Blueprint $table) {
                if (!Schema::hasColumn('circle_posts', 'interactive_type')) {
                    $table->string('interactive_type', 30)->default('none')->after('post_type')->index();
                }
                if (!Schema::hasColumn('circle_posts', 'interactive_data')) {
                    $table->json('interactive_data')->nullable()->after('interactive_type');
                }
                if (!Schema::hasColumn('circle_posts', 'collaborator_user_id')) {
                    $table->foreignId('collaborator_user_id')->nullable()->after('interactive_data')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('circle_posts', 'is_solved')) {
                    $table->boolean('is_solved')->default(false)->after('collaborator_user_id')->index();
                }
                if (!Schema::hasColumn('circle_posts', 'solved_by_user_id')) {
                    $table->foreignId('solved_by_user_id')->nullable()->after('is_solved')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('circle_posts', 'solved_at')) {
                    $table->timestamp('solved_at')->nullable()->after('solved_by_user_id');
                }
            });
        }

        // 2. Tabel Tebakan Teka-Teki (Riddle Guesses)
        if (!Schema::hasTable('circle_riddle_guesses')) {
            Schema::create('circle_riddle_guesses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_post_id')->constrained('circle_posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('guess_text', 255);
                $table->boolean('is_correct')->default(false)->index();
                $table->timestamps();

                $table->index(['circle_post_id', 'created_at']);
            });
        }

        // 3. Tabel Voting Battle 1 vs 1
        if (!Schema::hasTable('circle_battle_votes')) {
            Schema::create('circle_battle_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circle_post_id')->constrained('circle_posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('chosen_option', ['a', 'b']);
                $table->timestamps();

                $table->unique(['circle_post_id', 'user_id'], 'uniq_battle_vote_user');
                $table->index(['circle_post_id', 'chosen_option']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_battle_votes');
        Schema::dropIfExists('circle_riddle_guesses');

        if (Schema::hasTable('circle_posts')) {
            Schema::table('circle_posts', function (Blueprint $table) {
                $columns = ['interactive_type', 'interactive_data', 'collaborator_user_id', 'is_solved', 'solved_by_user_id', 'solved_at'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('circle_posts', $col)) {
                        if (in_array($col, ['collaborator_user_id', 'solved_by_user_id'])) {
                            $table->dropForeign([$col]);
                        }
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
