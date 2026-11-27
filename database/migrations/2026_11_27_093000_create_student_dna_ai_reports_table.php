<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_dna_ai_reports')) {
            Schema::create('student_dna_ai_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->string('data_fingerprint', 64);
                $table->string('provider', 40)->default('classai');
                $table->string('model_used', 80)->nullable();
                $table->json('ai_payload');
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->unique('student_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_dna_ai_reports');
    }
};
