<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\LmsCourse;
use App\Models\LmsCourseGroup;
use App\Models\LmsAssignmentGroup;
use App\Services\LmsEnrollmentService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $enrollmentService = app(LmsEnrollmentService::class);

            // Ambil hanya kursus yang memiliki kelompok kursus atau kelompok tugas + Kursus 221
            $courseGroupCourseIds = LmsCourseGroup::pluck('course_id')->filter()->unique();
            $assignmentGroupCourseIds = LmsAssignmentGroup::join('lms_assignments', 'lms_assignment_groups.assignment_id', '=', 'lms_assignments.id')
                ->pluck('lms_assignments.course_id')
                ->filter()
                ->unique();

            $targetCourseIds = $courseGroupCourseIds->merge($assignmentGroupCourseIds)->push(221)->unique()->values();

            $courses = LmsCourse::whereIn('id', $targetCourseIds)->with(['subject', 'lmsClasses.classroom'])->get();

            foreach ($courses as $course) {
                // Jalankan sinkronisasi enrollments dan pembersihan anggota kelompok kursus & tugas
                $enrollmentService->syncCourseEnrollments($course);
            }
        } catch (\Throwable $e) {
            \Log::warning('Migration cleanup_vocational_lms_groups warning: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data pembersihan kelompok liar tidak perlu dikembalikan
    }
};
