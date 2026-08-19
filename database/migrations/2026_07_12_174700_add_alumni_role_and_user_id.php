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
        // Alter users role enum
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin_sekolah','kepala_sekolah','bendahara','ketua_yayasan','guru','siswa','orang_tua','pegawai','alumni') NOT NULL");
        }

        Schema::table('alumni_directories', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni_directories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Revert users role enum (removing alumni)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin_sekolah','kepala_sekolah','bendahara','ketua_yayasan','guru','siswa','orang_tua','pegawai') NOT NULL");
        }
    }
};
