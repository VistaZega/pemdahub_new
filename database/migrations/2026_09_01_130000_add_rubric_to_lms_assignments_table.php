<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('lms_assignments', 'rubric')) {
            Schema::table('lms_assignments', function (Blueprint $table) {
                $table->json('rubric')->nullable()->after('max_score')
                    ->comment('Rubrik penilaian: [{"nama":"Isi","bobot":40,"maks":100},{"nama":"Struktur","bobot":30,"maks":100}]');
            });
        }
    }

    public function down(): void
    {
        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->dropColumn('rubric');
        });
    }
};