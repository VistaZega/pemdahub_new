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
        // 1. Expand lms_materials title to VARCHAR(255) and content to LONGTEXT
        if (Schema::hasTable('lms_materials')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE lms_materials MODIFY COLUMN title VARCHAR(255) NOT NULL");
                DB::statement("ALTER TABLE lms_materials MODIFY COLUMN content LONGTEXT NULL");
            } else {
                Schema::table('lms_materials', function (Blueprint $table) {
                    $table->string('title', 255)->change();
                    $table->longText('content')->nullable()->change();
                });
            }
        }

        // 2. Expand lms_assignments title to VARCHAR(255) and description to LONGTEXT
        if (Schema::hasTable('lms_assignments')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE lms_assignments MODIFY COLUMN title VARCHAR(255) NOT NULL");
                DB::statement("ALTER TABLE lms_assignments MODIFY COLUMN description LONGTEXT NULL");
            } else {
                Schema::table('lms_assignments', function (Blueprint $table) {
                    $table->string('title', 255)->change();
                    $table->longText('description')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lms_materials')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE lms_materials MODIFY COLUMN title VARCHAR(200) NOT NULL");
            }
        }

        if (Schema::hasTable('lms_assignments')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE lms_assignments MODIFY COLUMN title VARCHAR(200) NOT NULL");
            }
        }
    }
};
