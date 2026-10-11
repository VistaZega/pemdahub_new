<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->string('device_code', 50)->unique();
                $table->string('name', 100)->nullable();
                $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
                $table->string('type', 50)->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamp('last_seen_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->integer('rssi')->nullable();
                $table->unsignedInteger('free_heap')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('devices', function (Blueprint $table) {
                if (!Schema::hasColumn('devices', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('last_seen_at');
                }
                if (!Schema::hasColumn('devices', 'rssi')) {
                    $table->integer('rssi')->nullable()->after('ip_address');
                }
                if (!Schema::hasColumn('devices', 'free_heap')) {
                    $table->unsignedInteger('free_heap')->nullable()->after('rssi');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('devices')) {
            Schema::table('devices', function (Blueprint $table) {
                if (Schema::hasColumn('devices', 'ip_address')) {
                    $table->dropColumn('ip_address');
                }
                if (Schema::hasColumn('devices', 'rssi')) {
                    $table->dropColumn('rssi');
                }
                if (Schema::hasColumn('devices', 'free_heap')) {
                    $table->dropColumn('free_heap');
                }
            });
        }
    }
};
