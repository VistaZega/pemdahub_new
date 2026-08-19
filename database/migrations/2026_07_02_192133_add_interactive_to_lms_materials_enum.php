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
        // Alter ENUM directly to avoid issues with Doctrine DBAL requirements in Laravel < 11
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE lms_materials MODIFY COLUMN material_type ENUM('pdf', 'document', 'video', 'text', 'image', 'link', 'interactive') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE lms_materials MODIFY COLUMN material_type ENUM('pdf', 'document', 'video', 'text', 'image', 'link') NOT NULL");
        }
    }
};
