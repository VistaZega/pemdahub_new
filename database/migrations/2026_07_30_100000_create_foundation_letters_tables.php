<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foundation_letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number'); // Nomor Surat (Input Manual)
            $table->string('title'); // Perihal / Judul Surat
            $table->string('category')->default('edaran'); // edaran, instruksi, sk, pemberitahuan
            $table->longText('content'); // Isi Surat
            $table->date('effective_date'); // Tanggal Ditetapkan
            $table->date('deadline_date')->nullable(); // Tanggal Tenggat (misal 3 Agustus 2026)
            $table->json('recipients')->nullable(); // Target unit IDs [1, 2, 3] atau ['all']
            $table->string('signatory_name')->default('Yulianus Zega, S.Kom,M.Pd.T'); // Nama Penandatangan
            $table->string('signatory_position')->default('Ketua Yayasan'); // Jabatan Penandatangan
            $table->string('signature_hash')->unique(); // Hash Unik SHA-256 untuk QR Code Verifikasi
            $table->timestamp('signed_at')->nullable(); // Waktu TTD Digital
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->timestamps();
        });

        Schema::create('foundation_letter_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_letter_id')->constrained('foundation_letters')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['foundation_letter_id', 'school_id', 'user_id'], 'flr_letter_school_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foundation_letter_reads');
        Schema::dropIfExists('foundation_letters');
    }
};
