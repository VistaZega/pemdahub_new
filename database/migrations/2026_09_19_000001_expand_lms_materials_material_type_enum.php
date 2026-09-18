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
            DB::statement("ALTER TABLE lms_materials MODIFY COLUMN material_type VARCHAR(50) NOT NULL DEFAULT 'text'");
        } else {
            Schema::table('lms_materials', function (Blueprint $table) {
                $table->string('material_type', 50)->default('text')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE lms_materials MODIFY COLUMN material_type ENUM('pdf', 'document', 'video', 'text', 'image', 'link', 'interactive') NOT NULL DEFAULT 'text'");
        }
    }
};
