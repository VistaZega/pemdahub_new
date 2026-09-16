<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_code', 50)->unique()->comment('Kode unik device/station (contoh: STATION-SMP-01)');
            $table->string('name', 100)->nullable()->comment('Nama device');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->string('type', 50)->nullable()->comment('Jenis device: ESP32, NodeMCU, dll');
            $table->string('status', 20)->default('active')->comment('Status device: active, inactive, maintenance');
            $table->timestamp('last_seen_at')->nullable()->comment('Terakhir kali terkoneksi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
