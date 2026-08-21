<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LmsClass;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LmsEnrollmentService
{
    /**
     * Sinkronisasi kursus & materi LMS untuk 1 siswa tertentu berdasarkan rombel aktifnya.
     * Siswa baru yang baru dimasukkan ke kelas akan langsung terhubung ke seluruh kursus & materi lama.
     */
    public function syncStudentEnrollments(Student $student): int
    {
        if (!$student || !$student->id) {
            return 0;
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        
        // 1. Dapatkan daftar ID Rombel aktif siswa
        $classroomIds = StudentClass::where('student_id', $student->id)
            ->where('status', 'aktif')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('classroom_id')
            ->toArray();

        // Fallback jika belum ada di pivot student_classes untuk tahun aktif
        if (empty($classroomIds)) {
            $latestStudentClass = StudentClass::where('student_id', $student->id)
                ->latest()
                ->first();
            if ($latestStudentClass) {
                $classroomIds[] = $latestStudentClass->classroom_id;
            }
        }

        $classroomIds = array_unique(array_filter($classroomIds));
        if (empty($classroomIds)) {
            return 0;
        }

        // 2. Cari seluruh LmsCourse yang ditargetkan untuk rombel-rombel tersebut
        $courses = LmsCourse::where(function ($q) use ($classroomIds) {
            $q->whereIn('classroom_id', $classroomIds)
              ->orWhereHas('lmsClasses', function ($lq) use ($classroomIds) {
                  $lq->whereIn('classroom_id', $classroomIds);
              });
        })
        ->when($student->school_id, function ($q) use ($student) {
            $q->where(function ($sq) use ($student) {
                $sq->where('school_id', $student->school_id)
                   ->orWhereNull('school_id');
            });
        })
        ->get();

        $enrolledCount = 0;

        foreach ($courses as $course) {
            foreach ($classroomIds as $classroomId) {
                // Pastikan LmsClass ada untuk pasangan course & classroom ini
                $lmsClass = LmsClass::firstOrCreate([
                    'course_id' => $course->id,
                    'classroom_id' => $classroomId,
                ], [
                    'school_id' => $course->school_id ?? $student->school_id,
                    'status' => 'active',
                ]);

                // Pastikan siswa terdaftar di LmsEnrollment
                $enrollment = LmsEnrollment::firstOrCreate([
                    'lms_class_id' => $lmsClass->id,
                    'student_id' => $student->id,
                ], [
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ]);

                if ($enrollment->wasRecentlyCreated) {
                    $enrolledCount++;
                }
            }
        }

        // 3. Bersihkan enrollment silang sekolah yang tidak valid jika ada (cth: siswa SMK terdaftar di kelas SMP)
        // Catatan: Course dengan school_id = 4 (Yayasan) adalah sah untuk seluruh unit sekolah
        if ($student->school_id) {
            LmsEnrollment::where('student_id', $student->id)
                ->whereHas('lmsClass.course', function ($q) use ($student) {
                    $q->whereNotNull('school_id')
                      ->where('school_id', '!=', 4)
                      ->where('school_id', '!=', $student->school_id);
                })
                ->delete();
        }

        return $enrolledCount;
    }

    /**
     * Sinkronisasi seluruh siswa aktif di suatu rombel ke seluruh kursus LMS rombel tersebut.
     * Dipanggil saat siswa di-assign ke kelas atau saat kursus baru ditautkan ke kelas.
     */
    public function syncClassroomEnrollments(Classroom $classroom): int
    {
        if (!$classroom || !$classroom->id) {
            return 0;
        }

        $activeYear = AcademicYear::where('is_active', true)->first();

        // 1. Ambil seluruh siswa di rombel ini via student_classes
        $studentIds = StudentClass::where('classroom_id', $classroom->id)
            ->when($activeYear, function ($q) use ($activeYear, $classroom) {
                $hasActiveYear = StudentClass::where('classroom_id', $classroom->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->exists();
                if ($hasActiveYear) {
                    $q->where('academic_year_id', $activeYear->id);
                }
            })
            ->pluck('student_id')
            ->toArray();

        // Fallback jika kosong, ambil seluruh student_id yang pernah di rombel ini
        if (empty($studentIds)) {
            $studentIds = StudentClass::where('classroom_id', $classroom->id)
                ->pluck('student_id')
                ->toArray();
        }

        $allStudentIds = array_unique(array_filter($studentIds));
        if (empty($allStudentIds)) {
            return 0;
        }

        // 2. Ambil seluruh kursus LMS untuk rombel ini
        $courses = LmsCourse::where('classroom_id', $classroom->id)
            ->orWhereHas('lmsClasses', fn($q) => $q->where('classroom_id', $classroom->id))
            ->get();

        $createdCount = 0;

        foreach ($courses as $course) {
            $lmsClass = LmsClass::firstOrCreate([
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
            ], [
                'school_id' => $course->school_id ?? $classroom->school_id,
                'status' => 'active',
            ]);

            foreach ($allStudentIds as $studentId) {
                $enrollment = LmsEnrollment::firstOrCreate([
                    'lms_class_id' => $lmsClass->id,
                    'student_id' => $studentId,
                ], [
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ]);

                if ($enrollment->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }

    /**
     * Sinkronisasi seluruh rombel & siswa yang terhubung ke satu kursus LMS.
     * Dipanggil saat guru membuat atau memperbarui kursus.
     */
    public function syncCourseEnrollments(LmsCourse $course): int
    {
        if (!$course || !$course->id) {
            return 0;
        }

        // 1. Jika course memiliki classroom_id langsung, buat LmsClass
        if ($course->classroom_id) {
            LmsClass::firstOrCreate([
                'course_id' => $course->id,
                'classroom_id' => $course->classroom_id,
            ], [
                'school_id' => $course->school_id,
                'status' => 'active',
            ]);
        }

        // 2. Ambil seluruh LmsClass yang terkait dengan kursus ini
        $lmsClasses = LmsClass::where('course_id', $course->id)->get();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $createdCount = 0;

        foreach ($lmsClasses as $lmsClass) {
            $classroomId = $lmsClass->classroom_id;

            // 1. Coba ambil siswa dengan academic year aktif jika ada
            $studentIds = StudentClass::where('classroom_id', $classroomId)
                ->when($activeYear, function ($q) use ($activeYear, $classroomId) {
                    $hasActiveYear = StudentClass::where('classroom_id', $classroomId)
                        ->where('academic_year_id', $activeYear->id)
                        ->exists();
                    if ($hasActiveYear) {
                        $q->where('academic_year_id', $activeYear->id);
                    }
                })
                ->where(function ($q) {
                    $q->whereNull('status')
                      ->orWhereIn('status', ['aktif', 'active', 'Aktif', '']);
                })
                ->pluck('student_id')
                ->toArray();

            // 2. Fallback: ambil semua siswa di student_classes untuk rombel ini
            if (empty($studentIds)) {
                $studentIds = StudentClass::where('classroom_id', $classroomId)
                    ->pluck('student_id')
                    ->toArray();
            }

            $allStudentIds = array_unique(array_filter($studentIds));

            foreach ($allStudentIds as $studentId) {
                $enrollment = LmsEnrollment::firstOrCreate([
                    'lms_class_id' => $lmsClass->id,
                    'student_id' => $studentId,
                ], [
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ]);

                if ($enrollment->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }

    /**
     * Sinkronisasi massal seluruh kursus, rombel, dan siswa di seluruh sistem.
     */
    public function syncAll(): array
    {
        $courses = LmsCourse::all();
        $classrooms = Classroom::all();

        $coursesCount = 0;
        $classesCount = 0;
        $enrollmentsCount = 0;

        foreach ($courses as $course) {
            $coursesCount++;
            $enrollmentsCount += $this->syncCourseEnrollments($course);
        }

        foreach ($classrooms as $classroom) {
            $classesCount++;
            $enrollmentsCount += $this->syncClassroomEnrollments($classroom);
        }

        // Bersihkan data enrollment silang sekolah yang tidak valid (kecuali unit Yayasan / Lintas Unit)
        $deletedRogue = DB::table('lms_enrollments')
            ->join('lms_classes', 'lms_enrollments.lms_class_id', '=', 'lms_classes.id')
            ->join('lms_courses', 'lms_classes.course_id', '=', 'lms_courses.id')
            ->join('students', 'lms_enrollments.student_id', '=', 'students.id')
            ->whereNotNull('lms_courses.school_id')
            ->whereNotNull('students.school_id')
            ->where('lms_courses.school_id', '!=', 4)
            ->whereColumn('lms_courses.school_id', '!=', 'students.school_id')
            ->delete();

        return [
            'courses_synced' => $coursesCount,
            'classrooms_synced' => $classesCount,
            'new_enrollments_created' => $enrollmentsCount,
            'rogue_cross_school_enrollments_cleaned' => $deletedRogue,
        ];
    }
}
