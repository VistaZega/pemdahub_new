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
        Schema::table('osis_candidates', function (Blueprint $table) {
            $table->boolean('is_winner')->default(false)->after('vote_count');
            $table->foreignId('leader_user_id')->nullable()->after('is_winner')->constrained('users')->nullOnDelete();
            $table->foreignId('secretary_user_id')->nullable()->after('leader_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('treasurer_user_id')->nullable()->after('secretary_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('vice_user_id')->nullable()->after('treasurer_user_id')->constrained('users')->nullOnDelete();
            $table->boolean('points_awarded')->default(false)->after('vice_user_id');
            $table->dateTime('points_awarded_at')->nullable()->after('points_awarded');
        });

        Schema::table('osis_elections', function (Blueprint $table) {
            $table->foreignId('winner_candidate_id')->nullable()->after('show_realtime_count')->constrained('osis_candidates')->nullOnDelete();
            $table->dateTime('announcement_date')->nullable()->after('winner_candidate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osis_elections', function (Blueprint $table) {
            $table->dropForeign(['winner_candidate_id']);
            $table->dropColumn(['winner_candidate_id', 'announcement_date']);
        });

        Schema::table('osis_candidates', function (Blueprint $table) {
            $table->dropForeign(['leader_user_id']);
            $table->dropForeign(['secretary_user_id']);
            $table->dropForeign(['treasurer_user_id']);
            $table->dropForeign(['vice_user_id']);
            $table->dropColumn([
                'is_winner',
                'leader_user_id',
                'secretary_user_id',
                'treasurer_user_id',
                'vice_user_id',
                'points_awarded',
                'points_awarded_at'
            ]);
        });
    }
};
