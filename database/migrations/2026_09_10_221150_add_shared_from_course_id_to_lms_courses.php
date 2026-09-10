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
        Schema::table('lms_courses', function (Blueprint $table) {
            // ID course asal dari mana course ini dishare (NULL = course asli, non-NULL = hasil sharing)
            $table->unsignedBigInteger('shared_from_course_id')->nullable()->after('review_note');
            $table->foreign('shared_from_course_id')->references('id')->on('lms_courses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropForeign(['shared_from_course_id']);
            $table->dropColumn('shared_from_course_id');
        });
    }
};
