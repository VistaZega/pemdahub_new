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
        if (!Schema::hasColumn('school_contributions', 'expense_details')) {
            Schema::table('school_contributions', function (Blueprint $table) {
                $table->json('expense_details')->nullable()->after('authorized_expense')->comment('Breakdown Rincian Belanja Operasional per Kode Rekening');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('school_contributions', 'expense_details')) {
            Schema::table('school_contributions', function (Blueprint $table) {
                $table->dropColumn('expense_details');
            });
        }
    }
};
