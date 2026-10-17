<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Perangkat IoT Pengendali Kebisingan (Noise Devices)
        if (!Schema::hasTable('noise_devices')) {
            Schema::create('noise_devices', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('device_uid', 60)->unique();
                $table->string('device_token', 64)->unique();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->string('location_note', 150)->nullable();
                $table->string('custom_recipient_name', 100)->nullable();
                $table->string('custom_phone', 30)->nullable();
                
                // Ambang Batas Desibel (dB)
                $table->decimal('threshold_green', 5, 2)->default(65.00);   // Di bawah ini = Aman (LED Hijau)
                $table->decimal('threshold_yellow', 5, 2)->default(75.00);  // Di bawah ini = Sedang (LED Kuning)
                $table->decimal('threshold_red', 5, 2)->default(80.00);     // Di atas ini = Terlalu Ribut (LED Merah & Kirim WA)
                $table->decimal('calibration_offset', 5, 2)->default(0.00); // Kalibrasi offset dB (+/-)
                
                // Logika Peringatan
                $table->integer('alert_delay_seconds')->default(5); // Durasi bising konstan sebelum kirim WA
                $table->integer('cooldown_minutes')->default(10);   // Jeda antar pengiriman notifikasi WA
                $table->boolean('wa_enabled')->default(true);       // Saklar utama notifikasi WA
                $table->boolean('is_active')->default(true);
                
                // Status Terakhir Alat
                $table->decimal('last_db', 5, 2)->nullable();
                $table->decimal('peak_db', 5, 2)->nullable();
                $table->string('last_status', 20)->default('safe'); // safe, warning, danger
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('last_alert_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('firmware_version', 40)->default('1.0.0');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Log Data & Riwayat Kebisingan (Noise Logs)
        if (!Schema::hasTable('noise_logs')) {
            Schema::create('noise_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('noise_device_id')->constrained('noise_devices')->cascadeOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->decimal('db_level', 5, 2);
                $table->decimal('peak_db', 5, 2)->nullable();
                $table->string('status', 20)->default('safe'); // safe, warning, danger
                $table->boolean('is_alert_triggered')->default(false);
                $table->string('alert_recipient', 120)->nullable();
                $table->text('alert_message')->nullable();
                $table->string('alert_status', 30)->nullable(); // sent, failed, cooldown, disabled
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        // 3. Buat default device bila belum ada perangkat sama sekali
        $count = DB::table('noise_devices')->count();
        if ($count === 0) {
            $defaultClass = DB::table('classrooms')->where('is_active', true)->first();
            $schoolId = $defaultClass ? $defaultClass->school_id : null;
            $classroomId = $defaultClass ? $defaultClass->id : null;

            DB::table('noise_devices')->insert([
                'name' => 'ESP32 INMP441 Sound Monitor 01',
                'device_uid' => 'ESP32-NOISE-01',
                'device_token' => 'nd_' . Str::random(32),
                'school_id' => $schoolId,
                'classroom_id' => $classroomId,
                'location_note' => 'Ruang Kelas (Samping Papan Tulis)',
                'threshold_green' => 65.00,
                'threshold_yellow' => 75.00,
                'threshold_red' => 80.00,
                'calibration_offset' => 0.00,
                'alert_delay_seconds' => 5,
                'cooldown_minutes' => 10,
                'wa_enabled' => true,
                'is_active' => true,
                'last_db' => 52.4,
                'peak_db' => 61.2,
                'last_status' => 'safe',
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('noise_logs');
        Schema::dropIfExists('noise_devices');
    }
};
