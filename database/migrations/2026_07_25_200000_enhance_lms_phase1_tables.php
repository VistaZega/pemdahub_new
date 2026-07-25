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
            if (!Schema::hasColumn('lms_courses', 'is_sequential')) {
                $table->boolean('is_sequential')->default(false)->after('is_active');
            }
        });

        Schema::table('lms_modules', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_modules', 'is_sequential')) {
                $table->boolean('is_sequential')->default(false)->after('is_active');
            }
        });

        Schema::table('lms_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_materials', 'prerequisite_material_id')) {
                $table->unsignedBigInteger('prerequisite_material_id')->nullable()->after('is_published');
                $table->foreign('prerequisite_material_id')->references('id')->on('lms_materials')->onDelete('set null');
            }
        });

        if (!Schema::hasTable('lms_material_notes')) {
            Schema::create('lms_material_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('material_id');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('material_id')->references('id')->on('lms_materials')->onDelete('cascade');
                $table->unique(['student_id', 'material_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_material_notes');

        Schema::table('lms_materials', function (Blueprint $table) {
            if (Schema::hasColumn('lms_materials', 'prerequisite_material_id')) {
                $table->dropForeign(['prerequisite_material_id']);
                $table->dropColumn('prerequisite_material_id');
            }
        });

        Schema::table('lms_modules', function (Blueprint $table) {
            if (Schema::hasColumn('lms_modules', 'is_sequential')) {
                $table->dropColumn('is_sequential');
            }
        });

        Schema::table('lms_courses', function (Blueprint $table) {
            if (Schema::hasColumn('lms_courses', 'is_sequential')) {
                $table->dropColumn('is_sequential');
            }
        });
    }
};
