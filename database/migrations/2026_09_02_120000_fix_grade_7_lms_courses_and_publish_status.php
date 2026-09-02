<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\LmsCourse;
use App\Models\LmsClass;
use App\Models\Classroom;
use App\Services\LmsEnrollmentService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Auto-publish & set active on all non-deleted courses that are active
        if (Schema::hasTable('lms_courses')) {
            DB::table('lms_courses')
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->update([
                    'is_published' => 1,
                    'status' => 'active',
                ]);

            // 2. Fix specific course classroom assignments
            // Course #118: Informatika VII - Pythagoras -> point to active class 361
            $pythagorasClass = Classroom::where('class_name', 'like', '%Pythagoras%')
                ->where('grade_level', 7)
                ->where('is_active', true)
                ->first();
            $pythagorasId = $pythagorasClass?->id ?? 361;

            $course118 = LmsCourse::find(118);
            if ($course118) {
                $course118->update([
                    'classroom_id' => $pythagorasId,
                    'is_published' => 1,
                    'is_active' => 1,
                    'status' => 'active',
                ]);
                LmsClass::firstOrCreate([
                    'course_id' => 118,
                    'classroom_id' => $pythagorasId,
                ], [
                    'school_id' => $course118->school_id ?? 9,
                    'status' => 'active',
                ]);
            }

            // Course #287: PJOK VII - Pythagoras -> point to active class 361
            $course287 = LmsCourse::find(287);
            if ($course287) {
                $course287->update([
                    'classroom_id' => $pythagorasId,
                    'is_published' => 1,
                    'is_active' => 1,
                    'status' => 'active',
                ]);
                LmsClass::firstOrCreate([
                    'course_id' => 287,
                    'classroom_id' => $pythagorasId,
                ], [
                    'school_id' => $course287->school_id ?? 9,
                    'status' => 'active',
                ]);
            }

            // Course #276: HOHO Kelas VII - Albert Einstein -> point to active class 356
            $einsteinClass = Classroom::where('class_name', 'like', '%Albert Einstein%')
                ->where('grade_level', 7)
                ->where('is_active', true)
                ->first();
            $einsteinId = $einsteinClass?->id ?? 356;

            $course276 = LmsCourse::find(276);
            if ($course276) {
                $course276->update([
                    'classroom_id' => $einsteinId,
                    'is_published' => 1,
                    'is_active' => 1,
                    'status' => 'active',
                ]);
                LmsClass::firstOrCreate([
                    'course_id' => 276,
                    'classroom_id' => $einsteinId,
                ], [
                    'school_id' => $course276->school_id ?? 9,
                    'status' => 'active',
                ]);
            }

            // Course #310: Matematika 7 Pythagoras -> ensure LmsClass exists
            $course310 = LmsCourse::find(310);
            if ($course310) {
                $course310->update([
                    'classroom_id' => $pythagorasId,
                    'is_published' => 1,
                    'is_active' => 1,
                    'status' => 'active',
                ]);
                LmsClass::firstOrCreate([
                    'course_id' => 310,
                    'classroom_id' => $pythagorasId,
                ], [
                    'school_id' => $course310->school_id ?? 9,
                    'status' => 'active',
                ]);
            }

            // Course #278: HOHO Kelas VII - Pythagoras -> ensure LmsClass exists
            $course278 = LmsCourse::find(278);
            if ($course278) {
                $course278->update([
                    'classroom_id' => $pythagorasId,
                    'is_published' => 1,
                    'is_active' => 1,
                    'status' => 'active',
                ]);
                LmsClass::firstOrCreate([
                    'course_id' => 278,
                    'classroom_id' => $pythagorasId,
                ], [
                    'school_id' => $course278->school_id ?? 9,
                    'status' => 'active',
                ]);
            }

            // 3. Sync all active classrooms to enroll students
            try {
                $enrollmentService = app(LmsEnrollmentService::class);
                $activeClassrooms = Classroom::where('is_active', true)->get();
                foreach ($activeClassrooms as $cls) {
                    $enrollmentService->syncClassroomEnrollments($cls);
                }
            } catch (\Throwable $e) {
                // Ignore if service fails in standalone mode
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive: nothing to reverse
    }
};
