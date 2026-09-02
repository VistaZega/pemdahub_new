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
        
        // 1. Dapatkan daftar ID Rombel aktif siswa pada tahun ajaran aktif
        $classroomIds = StudentClass::where('student_id', $student->id)
            ->where('status', 'aktif')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('classroom_id')
            ->toArray();

        // Fallback jika belum ada di pivot student_classes untuk tahun aktif
        if (empty($classroomIds)) {
            $latestStudentClass = StudentClass::where('student_id', $student->id)
                ->where('status', 'aktif')
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
        $courses = LmsCourse::with('subject')
            ->where(function ($q) use ($classroomIds) {
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
            // Cek apakah mata pelajaran ini adalah mapel kejuruan SMK
            $subjName = $course->subject?->name ?? $course->subject?->subject_name;
            $vocKeywords = VocationalMajorFilterService::getSubjectMajorKeywords($subjName, $course->subject?->code, $course->course_name);

            foreach ($classroomIds as $classroomId) {
                $classroom = Classroom::find($classroomId);

                // Jika mapel kejuruan, pastikan jurusan siswa cocok sebelum di-enroll
                if ($vocKeywords && !VocationalMajorFilterService::isStudentMatchingVocationalSubject($student, $vocKeywords, $classroom)) {
                    continue; // Skip jika jurusan tidak cocok (cth: DPIB di mapel TE)
                }

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

        // 3. Bersihkan enrollment silang sekolah yang tidak valid jika ada
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
     */
    public function syncClassroomEnrollments(Classroom $classroom): int
    {
        if (!$classroom || !$classroom->id) {
            return 0;
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $classroom->academic_year_id ?? $activeYear?->id;

        // 1. Ambil siswa yang BENAR-BENAR terdaftar aktif di rombel ini pada tahun ajaran terkait
        $studentsQuery = $classroom->students()->with('classrooms')->wherePivot('status', 'aktif');
        if ($yearId) {
            $studentsQuery->wherePivot('academic_year_id', $yearId);
        }
        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            return 0;
        }

        // 2. Ambil seluruh kursus LMS untuk rombel ini
        $courses = LmsCourse::with('subject')
            ->where('classroom_id', $classroom->id)
            ->orWhereHas('lmsClasses', fn($q) => $q->where('classroom_id', $classroom->id))
            ->get();

        $createdCount = 0;

        foreach ($courses as $course) {
            $subjName = $course->subject?->name ?? $course->subject?->subject_name;
            $vocKeywords = VocationalMajorFilterService::getSubjectMajorKeywords($subjName, $course->subject?->code, $course->course_name);

            $lmsClass = LmsClass::firstOrCreate([
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
            ], [
                'school_id' => $course->school_id ?? $classroom->school_id,
                'status' => 'active',
            ]);

            $validStudentIds = [];

            foreach ($students as $student) {
                // Jika mapel kejuruan, hanya enroll siswa yang jurusannya relevan
                if ($vocKeywords && !VocationalMajorFilterService::isStudentMatchingVocationalSubject($student, $vocKeywords, $classroom)) {
                    continue;
                }

                $validStudentIds[] = $student->id;

                $enrollment = LmsEnrollment::firstOrCreate([
                    'lms_class_id' => $lmsClass->id,
                    'student_id' => $student->id,
                ], [
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ]);

                if ($enrollment->wasRecentlyCreated) {
                    $createdCount++;
                }
            }

            // Bersihkan ghost enrollments (siswa yang bukan anggota sah / tidak cocok kejuruan)
            if (!empty($validStudentIds)) {
                LmsEnrollment::where('lms_class_id', $lmsClass->id)
                    ->whereNotIn('student_id', $validStudentIds)
                    ->delete();
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
        $lmsClasses = LmsClass::where('course_id', $course->id)->with('classroom')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $createdCount = 0;

        $subjName = $course->subject?->name ?? $course->subject?->subject_name;
        $vocKeywords = VocationalMajorFilterService::getSubjectMajorKeywords($subjName, $course->subject?->code, $course->course_name);

        foreach ($lmsClasses as $lmsClass) {
            $classroom = $lmsClass->classroom;
            if (!$classroom) continue;

            $yearId = $classroom->academic_year_id ?? $course->academic_year_id ?? $activeYear?->id;

            // Ambil siswa yang BENAR-BENAR anggota aktif kelas ini
            $studentsQuery = $classroom->students()->with('classrooms')->wherePivot('status', 'aktif');
            if ($yearId) {
                $studentsQuery->wherePivot('academic_year_id', $yearId);
            }
            $students = $studentsQuery->get();

            $validStudentIds = [];

            foreach ($students as $student) {
                // Jika mapel kejuruan, hanya enroll siswa yang jurusannya relevan
                if ($vocKeywords && !VocationalMajorFilterService::isStudentMatchingVocationalSubject($student, $vocKeywords, $classroom)) {
                    continue;
                }

                $validStudentIds[] = $student->id;

                $enrollment = LmsEnrollment::firstOrCreate([
                    'lms_class_id' => $lmsClass->id,
                    'student_id' => $student->id,
                ], [
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ]);

                if ($enrollment->wasRecentlyCreated) {
                    $createdCount++;
                }
            }

            // Bersihkan ghost enrollments (siswa hantu yang bukan anggota kelas ini atau salah jurusan)
            if (!empty($validStudentIds)) {
                LmsEnrollment::where('lms_class_id', $lmsClass->id)
                    ->whereNotIn('student_id', $validStudentIds)
                    ->delete();
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

        // Bersihkan data enrollment silang sekolah yang tidak valid
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
