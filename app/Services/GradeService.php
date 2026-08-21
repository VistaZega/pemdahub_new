<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\FinalGrade;
use App\Models\GradeWeight;
use App\Models\LmsCourse;
use App\Models\LmsQuizAttempt;
use App\Models\LmsSubmission;
use App\Models\Semester;
use App\Models\Subject;
use App\Repositories\GradeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradeService
{
    public function __construct(
        private GradeRepository $gradeRepository
    ) {}

    /**
     * Create grade with automatic semester and user assignment
     */
    public function createGrade(array $data): Grade
    {
        // Get active semester or fallback to first
        if (empty($data['semester_id'])) {
            $data['semester_id'] = Semester::getActiveCached()?->id ?? Semester::first()?->id;
        }

        $data['is_remedial'] = $data['is_remedial'] ?? false;
        $data['created_by'] = $data['created_by'] ?? Auth::id();

        return $this->gradeRepository->create($data);
    }

    /**
     * Update grade
     */
    public function updateGrade(Grade $grade, array $data): bool
    {
        return $this->gradeRepository->update($grade, $data);
    }

    /**
     * Calculate final grade for student in a subject using school-specific weights
     */
    public function calculateFinalGrade(int $studentId, int $subjectId, int $semesterId, ?int $schoolId = null): ?array
    {
        $grades = $this->gradeRepository->getByStudent($studentId, $semesterId)
            ->where('subject_id', $subjectId);

        if ($grades->isEmpty()) {
            return null;
        }

        // Get school-specific weights
        $student = \App\Models\Student::with('classroom')->find($studentId);
        if (!$schoolId) {
            $schoolId = $student?->school_id;
        }
        $gradeLevel = $student?->classroom?->grade_level;

        $weights = $schoolId ? GradeWeight::getForSchool($schoolId) : null;
        $w = $weights ? $weights->getWeightsAsDecimal() : [
            'tugas' => 0.20, 'pts' => 0.30, 'pas' => 0.40, 'sikap' => 0.10,
        ];

        // Extract scores by type (avg all entries per type for multiple CBT exams)
        $tugas = $grades->where('grade_type', 'tugas')->avg('score') ?? 0;
        $pts = $grades->where('grade_type', 'uts')->avg('score') ?? 0;
        $pas = $grades->where('grade_type', 'uas')->avg('score') ?? 0;
        $sikap = $grades->where('grade_type', 'sikap')->avg('score') ?? 0;

        // Calculate weighted average
        $finalScore = ($tugas * $w['tugas']) + ($pts * $w['pts']) + ($pas * $w['pas']) + ($sikap * $w['sikap']);

        // Get KKM from subject
        $subject = Subject::find($subjectId);
        $kkm = $subject?->kkm ?? 75;

        $predicate = FinalGrade::scoreToPredicate($finalScore, $kkm, $gradeLevel);

        return [
            'tugas' => round($tugas, 2),
            'pts' => round($pts, 2),
            'pas' => round($pas, 2),
            'sikap' => round($sikap, 2),
            'final_score' => round($finalScore, 2),
            'kkm' => $kkm,
            'is_passed' => $finalScore >= $kkm,
            'predicate' => $predicate,
            'predicate_label' => $this->predicateToLabel($predicate),
        ];
    }

    /**
     * Calculate and save final grades for all subjects of a student in a semester
     */
    public function calculateAndSaveFinalGrades(int $studentId, int $semesterId, ?int $schoolId = null): array
    {
        $grades = $this->gradeRepository->getByStudent($studentId, $semesterId);
        
        if ($grades->isEmpty()) {
            return ['saved' => 0, 'subjects' => []];
        }

        $subjectIds = $grades->pluck('subject_id')->unique();
        $saved = 0;
        $results = [];

        foreach ($subjectIds as $subjectId) {
            $result = $this->calculateFinalGrade($studentId, $subjectId, $semesterId, $schoolId);
            
            if ($result) {
                // Get teacher who graded most entries for this subject
                $teacherId = $grades->where('subject_id', $subjectId)->pluck('teacher_id')
                    ->countBy()->sortDesc()->keys()->first();

                FinalGrade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'semester_id' => $semesterId,
                    ],
                    [
                        'teacher_id' => $teacherId,
                        'tugas_score' => $result['tugas'],
                        'pts_score' => $result['pts'],
                        'pas_score' => $result['pas'],
                        'sikap_score' => $result['sikap'],
                        'final_score' => $result['final_score'],
                        'kkm' => $result['kkm'],
                        'is_passed' => $result['is_passed'],
                        'predicate' => $result['predicate'],
                    ]
                );

                $saved++;
                $results[] = $result;
            }
        }

        return ['saved' => $saved, 'subjects' => $results];
    }

    /**
     * Get student report card data with weighted scores
     */
    public function getStudentReportCard(int $studentId, int $semesterId, ?int $schoolId = null): array
    {
        $grades = $this->gradeRepository->getByStudent($studentId, $semesterId);

        if ($grades->isEmpty()) {
            return [];
        }

        // Group by subject
        $subjectGrades = [];
        foreach ($grades->groupBy('subject_id') as $subjectId => $subjectGradesList) {
            $subject = $subjectGradesList->first()->subject;

            $finalGrade = $this->calculateFinalGrade($studentId, $subjectId, $semesterId, $schoolId);

            if ($finalGrade) {
                $subjectGrades[] = [
                    'subject' => $subject,
                    'grades' => $finalGrade,
                ];
            }
        }

        // Calculate overall average from weighted final scores
        $overallAverage = collect($subjectGrades)->avg('grades.final_score');
        
        // Calculate average KKM
        $avgKkm = collect($subjectGrades)->avg('grades.kkm') ?: 75;
        
        $student = \App\Models\Student::with('classroom')->find($studentId);
        $gradeLevel = $student?->classroom?->grade_level;
        
        $predicate = FinalGrade::scoreToPredicate($overallAverage, (int)$avgKkm, $gradeLevel);

        return [
            'subjects' => $subjectGrades,
            'overall_average' => round($overallAverage, 2),
            'overall_predicate' => $predicate,
            'overall_predicate_label' => $this->predicateToLabel($predicate),
        ];
    }

    /**
     * Convert predicate letter to label
     */
    private function predicateToLabel(string $predicate): string
    {
        return match ($predicate) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'D' => 'Kurang',
            default => '-',
        };
    }

    /**
     * Delete grade
     */
    public function deleteGrade(Grade $grade): bool
    {
        return $this->gradeRepository->delete($grade);
    }

    /**
     * Bulk create grades (for multiple students in one class + subject + type)
     */
    public function bulkCreateGrades(array $studentsData, array $commonData): array
    {
        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($studentsData as $studentId => $score) {
                if ($score === null || $score === '') {
                    continue; // Skip empty scores
                }

                try {
                    // Check if grade already exists for this student+subject+semester+type
                    $isDefaultComponent = false;
                    $defaultComponent = match ($commonData['grade_type']) {
                        'tugas' => 'Tugas 1',
                        'uts' => 'PTS',
                        'uas' => 'PAS',
                        'sikap' => 'Sikap 1',
                        default => 'Nilai 1',
                    };
                    $notes = $commonData['notes'] ?? null;
                    if ($notes === $defaultComponent) {
                        $isDefaultComponent = true;
                    }

                    $existingGrade = Grade::where('student_id', $studentId)
                        ->where('subject_id', $commonData['subject_id'])
                        ->where('semester_id', $commonData['semester_id'] ?? Semester::getActiveCached()?->id)
                        ->where('grade_type', $commonData['grade_type'])
                        ->where(function ($q) use ($notes, $isDefaultComponent) {
                            if ($isDefaultComponent) {
                                $q->where('notes', $notes)
                                  ->orWhereNull('notes')
                                  ->orWhere('notes', '');
                            } else {
                                if ($notes === null || $notes === '') {
                                    $q->whereNull('notes')->orWhere('notes', '');
                                } else {
                                    $q->where('notes', $notes);
                                }
                            }
                        })
                        ->whereNull('lms_source_type') // Only update manual grades
                        ->first();

                    if ($existingGrade) {
                        $existingGrade->update([
                            'score' => $score,
                            'teacher_id' => $commonData['teacher_id'] ?? $existingGrade->teacher_id,
                            'notes' => $commonData['notes'] ?? $existingGrade->notes,
                        ]);
                        $updated++;
                    } else {
                        $data = array_merge($commonData, [
                            'student_id' => $studentId,
                            'score' => $score,
                        ]);
                        $this->createGrade($data);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Student ID {$studentId}: " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    // =============================================
    // LMS → Grades Sync Methods
    // =============================================

    /**
     * Sync a quiz attempt score to the grades table as 'tugas' type
     */
    public function syncQuizAttemptToGrade(LmsQuizAttempt $attempt): ?Grade
    {
        $quiz = $attempt->quiz;
        $course = $quiz->course;

        // Course must have a subject_id to sync
        if (!$course->subject_id) {
            return null;
        }

        // attempt->score is already saved as a percentage (0-100) in LmsController
        $normalizedScore = $attempt->score;

        // Get teacher from course
        $teacherId = $course->teacher_id;

        // Get active semester  
        $semesterId = $course->semester_id ?? Semester::getActiveCached()?->id;

        if (!$semesterId) {
            return null;
        }

        // Check if already synced - update if exists
        $existing = Grade::where('lms_source_type', 'quiz_attempt')
            ->where('lms_source_id', $attempt->id)
            ->first();

        if ($existing) {
            $existing->update(['score' => $normalizedScore]);
            return $existing;
        }

        // Create new grade entry
        return Grade::create([
            'student_id' => $attempt->student_id,
            'subject_id' => $course->subject_id,
            'teacher_id' => $teacherId,
            'semester_id' => $semesterId,
            'grade_type' => 'tugas',
            'score' => $normalizedScore,
            'is_remedial' => false,
            'created_by' => Auth::id(),
            'notes' => "LMS Quiz: {$quiz->title}",
            'lms_source_type' => 'quiz_attempt',
            'lms_source_id' => $attempt->id,
        ]);
    }

    /**
     * Sync a graded LMS submission score to the grades table as 'tugas' type
     */
    public function syncSubmissionToGrade(LmsSubmission $submission): ?Grade
    {
        if ($submission->status !== 'graded' || $submission->score === null) {
            return null;
        }

        $assignment = $submission->assignment;
        $course = $assignment->course;

        // Course must have subject_id
        if (!$course->subject_id) {
            return null;
        }

        // Normalize score to 0-100
        $normalizedScore = $assignment->max_score > 0
            ? round(($submission->score / $assignment->max_score) * 100, 2)
            : $submission->score;

        $teacherId = $course->teacher_id;
        $semesterId = $course->semester_id ?? Semester::getActiveCached()?->id;

        if (!$semesterId) {
            return null;
        }

        // Collect all student IDs who should receive this grade
        $studentIds = collect([$submission->student_id]);
        if ($submission->group_id && $submission->group) {
            $memberIds = $submission->group->members()->pluck('students.id');
            $studentIds = $studentIds->merge($memberIds)->unique();
        }

        $lastGrade = null;
        foreach ($studentIds as $sId) {
            $existing = Grade::where('lms_source_type', 'submission')
                ->where('lms_source_id', $submission->id)
                ->where('student_id', $sId)
                ->first();

            if ($existing) {
                $existing->update(['score' => $normalizedScore]);
                $lastGrade = $existing;
            } else {
                $lastGrade = Grade::create([
                    'student_id' => $sId,
                    'subject_id' => $course->subject_id,
                    'teacher_id' => $teacherId,
                    'semester_id' => $semesterId,
                    'grade_type' => 'tugas',
                    'score' => $normalizedScore,
                    'is_remedial' => false,
                    'created_by' => $submission->graded_by,
                    'notes' => "LMS Tugas: {$assignment->title}" . ($submission->group ? " (Kelompok: {$submission->group->name})" : ""),
                    'lms_source_type' => 'submission',
                    'lms_source_id' => $submission->id,
                ]);
            }
        }

        return $lastGrade;
    }
}
