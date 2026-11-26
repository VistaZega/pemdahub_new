<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('virtual_labs')) {
            Schema::create('virtual_labs', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->enum('category', ['umum', 'smp', 'sma', 'vokasi'])->default('umum');
                $table->string('subject_name')->default('Umum');
                $table->string('engine_type')->default('ai_custom_html');
                $table->text('description')->nullable();
                $table->json('sop_steps')->nullable();
                $table->longText('custom_html')->nullable();
                $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('icon')->default('fas fa-flask');
                $table->string('accent_color')->default('#10b981');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_labs');
    }
};
