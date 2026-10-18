<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('noise_devices')) {
            Schema::table('noise_devices', function (Blueprint $table) {
                $table->integer('alert_delay_seconds')->default(30)->change();
            });

            // Update existing records with the 30s sustained noise default
            DB::table('noise_devices')
                ->where('alert_delay_seconds', '<', 30)
                ->orWhere('alert_delay_seconds', 60)
                ->update(['alert_delay_seconds' => 30]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('noise_devices')) {
            Schema::table('noise_devices', function (Blueprint $table) {
                $table->integer('alert_delay_seconds')->default(5)->change();
            });
        }
    }
};
