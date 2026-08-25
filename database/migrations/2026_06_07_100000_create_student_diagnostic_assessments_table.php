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
        Schema::create('student_diagnostic_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->json('interests')->nullable(); // minatan karir/hobi
            $table->string('work_style_preference')->nullable(); // mandiri, kelompok, riset, praktik
            $table->string('favorite_subject_cluster')->nullable(); // eksak, vokasi, bahasa, sosial, seni
            $table->string('career_aspiration')->nullable(); // cita-cita/jurusan impian
            $table->integer('logic_self_score')->default(75);
            $table->integer('creative_self_score')->default(75);
            $table->integer('communication_self_score')->default(75);
            $table->integer('technical_self_score')->default(75);
            $table->integer('social_self_score')->default(75);
            $table->integer('discipline_self_score')->default(75);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_diagnostic_assessments');
    }
};
