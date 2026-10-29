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
        // 1. Tabel Event Pemilihan OSIS (Per Unit Sekolah & Periode)
        Schema::create('osis_elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('title'); // e.g. "PEMILIHAN OSIS SMK SWASTA PEMDA NIAS"
            $table->string('slug')->unique(); // e.g. "pemilihan-osis-smk-2026-2027"
            $table->string('period')->default('2026 / 2027');
            $table->string('theme_tagline')->nullable()->default('Bersama Membangun Masa Depan yang Lebih Baik');
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('status')->default('voting_open'); // draft, upcoming, voting_open, voting_closed, completed
            $table->boolean('allow_teachers')->default(true);
            $table->boolean('allow_staff')->default(true);
            $table->boolean('show_realtime_count')->default(false); // Mode blind vote vs live count
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tabel Kandidat / Pasangan Calon (Paslon)
        Schema::create('osis_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('osis_elections')->onDelete('cascade');
            $table->unsignedInteger('candidate_number'); // 1, 2, 3, 4
            $table->string('candidate_type')->default('presidium_ksb'); // single, pair, presidium_ksb
            
            // Personel Paslon
            $table->string('leader_name'); // Nama Ketua
            $table->string('leader_nisn')->nullable();
            $table->string('leader_class')->nullable();
            $table->string('vice_name')->nullable(); // Nama Wakil Ketua (jika format Ketua - Wakil)
            $table->string('secretary_name')->nullable(); // Nama Sekretaris (jika format KSB)
            $table->string('treasurer_name')->nullable(); // Nama Bendahara (jika format KSB)
            
            // Konten Kampanye
            $table->text('quote_slogan')->nullable(); // Kutipan ringkas di kartu
            $table->text('vision')->nullable(); // Visi
            $table->json('mission')->nullable(); // Array Misi bertitik poin
            $table->json('work_programs')->nullable(); // Array Program Kerja
            $table->string('photo')->nullable(); // Foto Kartu Paslon
            $table->string('poster_flyer')->nullable(); // Flyer Poster Resolusi Tinggi
            $table->string('video_url')->nullable(); // Video Kampanye (YouTube)
            $table->string('color_badge')->default('#ef4444'); // Warna Aksen Paslon (Merah, Biru, Kuning, Hijau)
            
            // Agregasi Suara
            $table->unsignedBigInteger('vote_count')->default(0);
            $table->timestamps();

            $table->unique(['election_id', 'candidate_number']);
        });

        // 3. Tabel Daftar Pemilih Tetap (DPT) & Status Voting (Mencegah Double Voting)
        Schema::create('osis_voters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('osis_elections')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('voter_type')->default('student'); // student, teacher, staff, admin
            $table->boolean('has_voted')->default(false);
            $table->dateTime('voted_at')->nullable();
            $table->string('vote_token', 32)->nullable()->unique(); // Bukti digital coblos
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['election_id', 'user_id']);
        });

        // 4. Tabel Kotak Suara Digital Anonim (Asas LUBER JURDIL - Tanpa user_id!)
        Schema::create('osis_ballots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('osis_elections')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('osis_candidates')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osis_ballots');
        Schema::dropIfExists('osis_voters');
        Schema::dropIfExists('osis_candidates');
        Schema::dropIfExists('osis_elections');
    }
};
