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
        // 1. Tambahkan kolom kepatuhan uang sekolah ke cbt_exams jika belum ada
        Schema::table('cbt_exams', function (Blueprint $table) {
            if (!Schema::hasColumn('cbt_exams', 'requires_tuition_payment')) {
                $table->boolean('requires_tuition_payment')->default(false)->after('auto_sync_grade')
                    ->comment('TRUE jika ujian mensyaratkan pelunasan uang sekolah (SPP) bulan berkenaan ujian');
                $table->index('requires_tuition_payment');
            }
            if (!Schema::hasColumn('cbt_exams', 'tuition_month')) {
                $table->unsignedTinyInteger('tuition_month')->nullable()->after('requires_tuition_payment')
                    ->comment('Override bulan tagihan (1-12), null = otomatis dari tanggal pelaksanaan');
            }
            if (!Schema::hasColumn('cbt_exams', 'tuition_year')) {
                $table->unsignedSmallInteger('tuition_year')->nullable()->after('tuition_month')
                    ->comment('Override tahun tagihan, null = otomatis dari tanggal pelaksanaan');
            }
        });

        // 2. Buat tabel otorisasi dispensasi ujian oleh wali kelas
        if (!Schema::hasTable('cbt_exam_dispensations')) {
            Schema::create('cbt_exam_dispensations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->enum('status', ['granted', 'revoked'])->default('granted');
                $table->text('reason')->nullable()->comment('Catatan kesepakatan penyelesaian uang sekolah');
                $table->timestamp('granted_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->unique(['cbt_exam_id', 'student_id'], 'uq_exam_student_dispensation');
                $table->index(['cbt_exam_id', 'status'], 'idx_exam_dispensation_status');
                $table->index(['student_id', 'status'], 'idx_student_dispensation_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_exam_dispensations');

        Schema::table('cbt_exams', function (Blueprint $table) {
            if (Schema::hasColumn('cbt_exams', 'tuition_year')) {
                $table->dropColumn('tuition_year');
            }
            if (Schema::hasColumn('cbt_exams', 'tuition_month')) {
                $table->dropColumn('tuition_month');
            }
            if (Schema::hasColumn('cbt_exams', 'requires_tuition_payment')) {
                $table->dropIndex(['requires_tuition_payment']);
                $table->dropColumn('requires_tuition_payment');
            }
        });
    }
};
