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
        // 1. Tabel Push Subscriptions (Browser/Device Tokens)
        if (!Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('endpoint');
                $table->string('public_key', 255)->nullable();
                $table->string('auth_token', 255)->nullable();
                $table->string('content_encoding', 50)->default('aesgcm');
                $table->string('device_name', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('user_id');
                $table->index('is_active');
            });
        }

        // 2. Tabel Preferensi Notifikasi Pengguna (LMS, CBT, Forum)
        if (!Schema::hasTable('push_notification_preferences')) {
            Schema::create('push_notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->boolean('push_enabled')->default(true)->comment('Master switch on/off notifikasi HP');
                $table->boolean('push_lms')->default(true)->comment('Notifikasi tugas, nilai, dan materi LMS');
                $table->boolean('push_cbt')->default(true)->comment('Notifikasi jadwal dan hasil ujian CBT');
                $table->boolean('push_forum')->default(true)->comment('Notifikasi balasan dan mention PembdaSPACE');
                $table->boolean('push_school')->default(true)->comment('Notifikasi pengumuman darurat sekolah');
                $table->timestamps();

                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notification_preferences');
        Schema::dropIfExists('push_subscriptions');
    }
};
