<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentPromotion;
use App\Models\StudentStatusHistory;
use Illuminate\Support\Facades\DB;

class StudentLifecycleService
{
    /**
     * Enroll a new student (calon → aktif) and assign to classroom.
     */
    public function enrollStudent(
        Student $student,
        Classroom $classroom,
        ?int $changedBy = null
    ): StudentStatusHistory {
        return DB::transaction(function () use ($student, $classroom, $changedBy) {
            // Transition status
            $history = $student->transitionTo(
                'aktif',
                'Pendaftaran siswa baru',
                null,
                null,
                $changedBy
            );

            // Create student_class entry
            $academicYear = AcademicYear::where('is_active', true)->firstOrFail();
            StudentClass::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'classroom_id' => $classroom->id,
                    'status' => 'aktif',
                    'entry_date' => now(),
                ]
            );

            // Auto-sync LMS courses & materials untuk siswa baru ini
            try {
                app(\App\Services\LmsEnrollmentService::class)->syncStudentEnrollments($student);
            } catch (\Throwable $e) {
                Log::warning('LMS sync on enrollStudent: ' . $e->getMessage());
            }

            return $history;
        });
    }

    /**
     * Promote a student to the next class.
     */
    public function promoteStudent(
        Student $student,
        Classroom $toClassroom,
        AcademicYear $currentYear,
        AcademicYear $nextYear,
        float $averageScore = 0,
        int $totalSubjects = 0,
        int $passedSubjects = 0,
        ?string $notes = null,
        ?int $decidedBy = null
    ): StudentPromotion {
        return DB::transaction(function () use (
            $student, $toClassroom, $currentYear, $nextYear,
            $averageScore, $totalSubjects, $passedSubjects, $notes, $decidedBy
        ) {
            // Get current classroom
            $currentStudentClass = StudentClass::where('student_id', $student->id)
                ->where('academic_year_id', $currentYear->id)
                ->where('status', 'aktif')
                ->firstOrFail();

            // Create promotion record
            $promotion = StudentPromotion::create([
                'student_id' => $student->id,
                'from_classroom_id' => $currentStudentClass->classroom_id,
                'to_classroom_id' => $toClassroom->id,
                'academic_year_id' => $currentYear->id,
                'decision' => 'naik',
                'average_score' => $averageScore,
                'total_subjects' => $totalSubjects,
                'passed_subjects' => $passedSubjects,
                'notes' => $notes,
                'decided_by' => $decidedBy ?? auth()->id(),
                'decided_at' => now(),
            ]);

            // Update old student_class
            $currentStudentClass->update([
                'status' => 'naik',
                'promoted_at' => now(),
            ]);

            // Create new student_class for next year
            StudentClass::create([
                'student_id' => $student->id,
                'classroom_id' => $toClassroom->id,
                'academic_year_id' => $nextYear->id,
                'status' => 'aktif',
                'entry_date' => now(),
            ]);

            // Log status transition
            $student->transitionTo('aktif', 'Naik kelas', $notes, null, $decidedBy);

            // Auto-sync LMS courses & materials untuk rombel baru siswa ini
            try {
                app(\App\Services\LmsEnrollmentService::class)->syncStudentEnrollments($student);
            } catch (\Throwable $e) {
                Log::warning('LMS sync on promoteStudent: ' . $e->getMessage());
            }

            return $promotion;
        });
    }

    /**
     * Retain a student (tinggal kelas).
     */
    public function retainStudent(
        Student $student,
        AcademicYear $currentYear,
        AcademicYear $nextYear,
        ?string $notes = null,
        ?int $decidedBy = null
    ): StudentPromotion {
        return DB::transaction(function () use ($student, $currentYear, $nextYear, $notes, $decidedBy) {
            $currentStudentClass = StudentClass::where('student_id', $student->id)
                ->where('academic_year_id', $currentYear->id)
                ->where('status', 'aktif')
                ->firstOrFail();

            $promotion = StudentPromotion::create([
                'student_id' => $student->id,
                'from_classroom_id' => $currentStudentClass->classroom_id,
                'to_classroom_id' => $currentStudentClass->classroom_id, // same class
                'academic_year_id' => $currentYear->id,
                'decision' => 'tinggal',
                'notes' => $notes,
                'decided_by' => $decidedBy ?? auth()->id(),
                'decided_at' => now(),
            ]);

            $currentStudentClass->update(['status' => 'tinggal']);

            // Re-enroll in same classroom for next year
            StudentClass::create([
                'student_id' => $student->id,
                'classroom_id' => $currentStudentClass->classroom_id,
                'academic_year_id' => $nextYear->id,
                'status' => 'aktif',
                'entry_date' => now(),
            ]);

            $student->transitionTo('aktif', 'Tinggal kelas', $notes, null, $decidedBy);

            return $promotion;
        });
    }

    /**
     * Graduate a student (lulus).
     */
    public function graduateStudent(
        Student $student,
        AcademicYear $currentYear,
        float $averageScore = 0,
        ?string $notes = null,
        ?string $documentNumber = null,
        ?int $decidedBy = null
    ): StudentPromotion {
        return DB::transaction(function () use (
            $student, $currentYear, $averageScore, $notes, $documentNumber, $decidedBy
        ) {
            $currentStudentClass = StudentClass::where('student_id', $student->id)
                ->where('academic_year_id', $currentYear->id)
                ->where('status', 'aktif')
                ->firstOrFail();

            $promotion = StudentPromotion::create([
                'student_id' => $student->id,
                'from_classroom_id' => $currentStudentClass->classroom_id,
                'to_classroom_id' => null,
                'academic_year_id' => $currentYear->id,
                'decision' => 'lulus',
                'average_score' => $averageScore,
                'notes' => $notes,
                'decided_by' => $decidedBy ?? auth()->id(),
                'decided_at' => now(),
            ]);

            $currentStudentClass->update(['status' => 'lulus']);

            $student->transitionTo('lulus', 'Kelulusan', $notes, $documentNumber, $decidedBy);

            return $promotion;
        });
    }

    /**
     * Transfer/withdraw a student.
     */
    public function withdrawStudent(
        Student $student,
        string $reason,
        string $type = 'keluar', // 'keluar', 'pindah', 'dikeluarkan'
        ?string $notes = null,
        ?string $documentNumber = null,
        ?int $changedBy = null
    ): StudentStatusHistory {
        return DB::transaction(function () use ($student, $reason, $type, $notes, $documentNumber, $changedBy) {
            // Update current class enrollment
            $currentYear = AcademicYear::where('is_active', true)->first();
            if ($currentYear) {
                StudentClass::where('student_id', $student->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->where('status', 'aktif')
                    ->update(['status' => $type === 'pindah' ? 'pindah' : 'keluar']);
            }

            return $student->transitionTo($type, $reason, $notes, $documentNumber, $changedBy);
        });
    }

    /**
     * Bulk promote students at end of academic year.
     */
    public function bulkPromote(
        array $studentDecisions,
        AcademicYear $currentYear,
        AcademicYear $nextYear,
        int $decidedBy
    ): array {
        $results = ['promoted' => 0, 'retained' => 0, 'graduated' => 0, 'errors' => []];

        foreach ($studentDecisions as $decision) {
            try {
                $student = Student::findOrFail($decision['student_id']);

                match ($decision['decision']) {
                    'naik' => $this->promoteStudent(
                        $student,
                        Classroom::findOrFail($decision['to_classroom_id']),
                        $currentYear,
                        $nextYear,
                        $decision['average_score'] ?? 0,
                        $decision['total_subjects'] ?? 0,
                        $decision['passed_subjects'] ?? 0,
                        $decision['notes'] ?? null,
                        $decidedBy
                    ),
                    'tinggal' => $this->retainStudent(
                        $student, $currentYear, $nextYear, $decision['notes'] ?? null, $decidedBy
                    ),
                    'lulus' => $this->graduateStudent(
                        $student, $currentYear, $decision['average_score'] ?? 0,
                        $decision['notes'] ?? null, null, $decidedBy
                    ),
                    default => throw new \InvalidArgumentException("Invalid decision: {$decision['decision']}"),
                };

                $results[$decision['decision'] === 'naik' ? 'promoted' : ($decision['decision'] === 'tinggal' ? 'retained' : 'graduated')]++;
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'student_id' => $decision['student_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
