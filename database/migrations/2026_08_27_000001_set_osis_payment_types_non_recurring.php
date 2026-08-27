<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('payment_types')
            ->where('type_name', 'like', '%OSIS%')
            ->update(['is_recurring' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_types')
            ->where('type_name', 'like', '%OSIS%')
            ->update(['is_recurring' => true]);
    }
};
