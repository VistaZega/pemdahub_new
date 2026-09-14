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
        // ================================================================
        // 2. USERS & AUTHENTICATION
        // ================================================================

        // Tabel Users
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->nullable();
                $table->string('username', 50)->nullable()->unique();
                $table->string('email', 100)->unique();
                $table->string('password')->comment('bcrypt hashed');
                $table->enum('role', ['superadmin', 'admin_sekolah', 'bendahara', 'ketua_yayasan', 'guru', 'siswa', 'orang_tua']);
                $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->index('username');
                $table->index('email');
                $table->index('role');
                $table->index('school_id');
            });
        }

        // Tabel Password Reset Tokens
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Tabel Sessions
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Tabel Guru & Staff
        if (!Schema::hasTable('teachers')) {
            Schema::create('teachers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->string('teacher_code', 20)->unique()->comment('NIP/NUPTK/Kode Internal');
                $table->string('full_name', 100);
                $table->enum('gender', ['L', 'P']);
                $table->string('birth_place', 50)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('religion', 20)->nullable();
                $table->text('address')->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('photo', 255)->nullable()->comment('Path file foto (max 2MB)');
                $table->string('position', 50)->nullable()->comment('Guru, Wali Kelas, Kepala Sekolah, Staff TU');
                $table->boolean('is_active')->default(true);

                $table->index('teacher_code');
                $table->index('school_id');
                $table->index('is_active');
            });
        }

        // Tabel Siswa
        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->string('nisn', 20)->unique()->comment('Nomor Induk Siswa Nasional');
                $table->string('nis', 20)->nullable()->comment('Nomor Induk Sekolah (internal)');
                $table->string('full_name', 100);
                $table->enum('gender', ['L', 'P']);
                $table->string('birth_place', 50)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('religion', 20)->nullable();
                $table->text('address')->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('photo', 255)->nullable()->comment('Path file foto (max 2MB)');
                $table->string('parent_name', 100)->nullable();
                $table->string('parent_phone', 20)->nullable();
                $table->year('entry_year')->comment('Tahun masuk (untuk tracking angkatan)');
                $table->year('graduation_year')->nullable()->comment('Tahun lulus (auto-fill saat lulus)');
                if (\DB::connection()->getDriverName() === 'sqlite') {
                    $table->string('status', 30)->default('aktif');
                } else {
                    $table->enum('status', ['aktif', 'lulus', 'keluar', 'pindah'])->default('aktif');
                }
                $table->timestamps();

                $table->index('nisn');
                $table->index('full_name');
                $table->index('school_id');
                $table->index('status');
                $table->index('entry_year');
            });
        }

        // Tabel Alumni
        if (!Schema::hasTable('alumni')) {
            Schema::create('alumni', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->string('nisn', 20);
                $table->string('nis', 20)->nullable();
                $table->string('full_name', 100);
                $table->enum('gender', ['L', 'P']);
                $table->string('birth_place', 50)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('religion', 20)->nullable();
                $table->string('phone', 20)->nullable();
                $table->year('entry_year');
                $table->year('graduation_year')->comment('Tahun lulus');
                $table->string('final_class', 50)->nullable()->comment('Kelas terakhir (IX/XII)');
                $table->text('notes')->nullable()->comment('Catatan tambahan');
                $table->timestamp('moved_at')->useCurrent()->comment('Kapan dipindah ke alumni');

                $table->index('nisn');
                $table->index('school_id');
                $table->index('graduation_year');
            });
        }

        // Tabel Orang Tua
        if (!Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->enum('relation_type', ['ayah', 'ibu', 'wali']);
                $table->string('full_name', 100);
                $table->string('phone', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('occupation', 100)->nullable()->comment('Pekerjaan');
                $table->text('address')->nullable();

                $table->index('student_id');
            });
        }

        // Tabel Login Histories
        if (!Schema::hasTable('login_histories')) {
            Schema::create('login_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->string('session_id', 255)->nullable();
                $table->timestamp('login_time')->nullable();
                $table->string('status', 50)->nullable()->comment('active, inactive, expired');
                $table->timestamps();

                $table->index('user_id');
                $table->index('login_time');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('alumni');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
