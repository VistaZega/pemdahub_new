<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan SoftDeletes pada tabel academic_years.
     * LATAR BELAKANG: Pada Juli 2026, penghapusan TP 2026/2027
     * menyebabkan kehilangan data masif karena 20+ tabel memiliki
     * ON DELETE CASCADE pada academic_year_id. SoftDeletes memastikan
     * data tidak pernah benar-benar dihapus dari database.
     */
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
