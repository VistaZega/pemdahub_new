<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE lms_games MODIFY COLUMN game_type ENUM('spin_wheel', 'flashcard', 'match', 'crossword', 'word_search', 'quiz', 'true_false', 'word_guess', 'scramble', 'sequence', 'image_hotspot', 'chem_balancer', 'math_ninja') NOT NULL DEFAULT 'flashcard'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE lms_games MODIFY COLUMN game_type ENUM('spin_wheel', 'flashcard', 'match', 'crossword', 'word_search', 'quiz', 'true_false', 'word_guess', 'scramble', 'sequence') NOT NULL DEFAULT 'flashcard'");
        }
    }
};
