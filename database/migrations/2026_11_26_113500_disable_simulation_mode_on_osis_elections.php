<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('osis_elections') && Schema::hasColumn('osis_elections', 'is_simulation')) {
            DB::table('osis_elections')->update(['is_simulation' => false]);

            Schema::table('osis_elections', function (Blueprint $table) {
                $table->boolean('is_simulation')->default(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('osis_elections') && Schema::hasColumn('osis_elections', 'is_simulation')) {
            Schema::table('osis_elections', function (Blueprint $table) {
                $table->boolean('is_simulation')->default(true)->change();
            });
        }
    }
};
