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
        // Modify ENUM to include 'scramble' and 'sequence'
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE lms_games MODIFY COLUMN game_type ENUM('spin_wheel', 'flashcard', 'match', 'crossword', 'word_search', 'quiz', 'true_false', 'word_guess', 'scramble', 'sequence') NOT NULL DEFAULT 'flashcard'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback to previous enum (without scramble and sequence)
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE lms_games MODIFY COLUMN game_type ENUM('spin_wheel', 'flashcard', 'match', 'crossword', 'word_search', 'quiz', 'true_false', 'word_guess') NOT NULL DEFAULT 'flashcard'");
        }
    }
};
