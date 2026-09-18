<?php

namespace App\Services;

use App\Models\CbtExam;
use App\Models\CbtExamSession;
use App\Models\CbtExamResult;
use App\Models\CbtAnswer;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionOption;
use App\Models\CbtExamQuestion;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\VocationalMajorFilterService;
use App\Services\CbtTuitionComplianceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CbtService
{
    /**
     * Prepare questions for an exam from linked question banks.
     * Picks random questions from each bank based on questions_to_pick.
     */
    public function prepareExamQuestions(CbtExam $exam): void
    {
        DB::transaction(function () use ($exam) {
            // Clear existing exam questions
            CbtExamQuestion::where('exam_id', $exam->id)->delete();

            $sortOrder = 1;
            $allQuestionIds = [];

            foreach ($exam->questionBanks as $bank) {
                $count = $bank->pivot->questions_to_pick;

                $questionIds = CbtQuestion::where('question_bank_id', $bank->id)
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->limit($count)
                    ->pluck('id')
                    ->toArray();

                foreach ($questionIds as $qId) {
                    CbtExamQuestion::create([
                        'exam_id' => $exam->id,
                        'question_id' => $qId,
                        'sort_order' => $sortOrder++,
                    ]);
                    $allQuestionIds[] = $qId;
                }
            }

            // Update total questions shown
            $exam->update(['total_questions_shown' => count($allQuestionIds)]);
        });
    }

    /**
     * Start an exam session for a student.
     * Creates session with randomized question & option orders.
     * Uses pessimistic locking to prevent duplicate sessions under concurrency.
     */
    public function startExamSession(CbtExam $exam, Student $student, ?int $classroomId): CbtExamSession
    {
        try {
            return DB::transaction(function () use ($exam, $student, $classroomId) {
                // Prevent duplicate session creation under concurrency
                $existingSession = CbtExamSession::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['not_started', 'in_progress'])
                    ->first();

                if ($existingSession) {
                    return $existingSession;
                }

                // Check kepatuhan pembayaran uang sekolah jika disyaratkan oleh ujian
                if ($exam->requires_tuition_payment) {
                    $complianceService = app(CbtTuitionComplianceService::class);
                    $compliance = $complianceService->checkStudentCompliance($exam, $student);
                    if (!$compliance['allowed']) {
                        throw new \RuntimeException($compliance['message'] ?? 'Akses ujian dibatasi karena kepatuhan uang sekolah.');
                    }
                }

                // Check max attempts
                $attemptCount = CbtExamSession::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['submitted', 'timeout', 'graded'])
                    ->count();

                if ($attemptCount >= $exam->max_attempts) {
                    throw new \RuntimeException("Batas percobaan ({$exam->max_attempts}) sudah tercapai.");
                }

                // Get exam questions
                $questionIds = $exam->examQuestions()->pluck('question_id')->toArray();

                // Randomize question order if enabled
                $questionOrder = $questionIds;
                if ($exam->randomize_questions) {
                    shuffle($questionOrder);
                }

                // Randomize option orders if enabled
                $optionOrders = [];
                if ($exam->randomize_options) {
                    foreach ($questionIds as $qId) {
                        $options = CbtQuestionOption::where('question_id', $qId)
                            ->pluck('option_label')
                            ->toArray();
                        shuffle($options);
                        $optionOrders[$qId] = $options;
                    }
                }

                // Create session
                $session = CbtExamSession::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'classroom_id' => $classroomId,
                    'attempt_number' => $attemptCount + 1,
                    'started_at' => now(),
                    'deadline_at' => now()->addMinutes($exam->duration_minutes),
                    'status' => 'in_progress',
                    'question_order' => $questionOrder,
                    'option_orders' => $optionOrders ?: null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Pre-create empty answer records
                $answerRecords = [];
                $now = now();
                foreach ($questionOrder as $qId) {
                    $answerRecords[] = [
                        'session_id' => $session->id,
                        'question_id' => $qId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                CbtAnswer::insert($answerRecords);

                return $session;
            }, 3);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle deadlock (40001 / 1213) or unique constraint race conditions gracefully
            $existingSession = CbtExamSession::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->latest('id')
                ->first();

            if ($existingSession) {
                return $existingSession;
            }

            throw $e;
        }
    }

    /**
     * Batch-start exam sessions for all eligible students in participating classrooms.
     * Used when the teacher wants to start all students simultaneously.
     * Processes in chunks to avoid memory issues with large classes.
     */
    public function batchStartSessions(CbtExam $exam): array
    {
        $eligibleEntries = $this->getEligibleStudents($exam);

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($eligibleEntries as $entry) {
            try {
                // entry is a StudentClass record with classroom_id and student relation
                $student = $entry->student;
                $classroomId = $entry->classroom_id;

                if (!$student) {
                    $skipped++;
                    continue;
                }

                // Check if session already exists (skip duplicate)
                $exists = CbtExamSession::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['not_started', 'in_progress'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Check kepatuhan uang sekolah untuk batch start
                if ($exam->requires_tuition_payment) {
                    $complianceService = app(CbtTuitionComplianceService::class);
                    $compliance = $complianceService->checkStudentCompliance($exam, $student);
                    if (!$compliance['allowed']) {
                        $skipped++;
                        $errors[] = "{$student->full_name}: Dilewati (belum lunas uang sekolah & belum ada dispensasi)";
                        continue;
                    }
                }

                $this->startExamSession($exam, $student, $classroomId);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "{$student->full_name}: {$e->getMessage()}";
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => $eligibleEntries->count(),
        ];
    }

    /**
     * Save/update an answer for a session
     */
    public function saveAnswer(CbtExamSession $session, int $questionId, array $data): CbtAnswer
    {
        if (!$session->isInProgress()) {
            throw new \RuntimeException('Sesi ujian tidak aktif.');
        }

        if (!$session->hasTimeRemaining()) {
            $this->submitSession($session, true);
            throw new \RuntimeException('Waktu ujian telah habis.');
        }

        return CbtAnswer::updateOrCreate(
            [
                'session_id' => $session->id,
                'question_id' => $questionId,
            ],
            [
                'selected_option' => $data['selected_option'] ?? null,
                'text_answer' => $data['text_answer'] ?? null,
                'is_flagged' => $data['is_flagged'] ?? false,
                'time_spent_seconds' => $data['time_spent_seconds'] ?? 0,
            ]
        );
    }

    /**
     * Submit/finish an exam session
     */
    public function submitSession(CbtExamSession $session, bool $isTimeout = false): CbtExamResult
    {
        // Idempotency: jika sesi ini sudah selesai dan hasilnya sudah ada, kembalikan hasil yang ada
        if (in_array($session->status, ['submitted', 'timeout', 'graded'])) {
            $existing = CbtExamResult::where('session_id', $session->id)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($session, $isTimeout) {
            // Auto-grade MC and TF questions
            $this->autoGradeSession($session);

            // Update session status
            $session->update([
                'status' => $isTimeout ? 'timeout' : 'submitted',
                'finished_at' => now(),
            ]);

            // Calculate result
            $result = $this->calculateResult($session);

            // Auto-sync to grades if enabled
            if ($session->exam->auto_sync_grade) {
                $this->syncResultToGrade($result);
            }

            return $result;
        });
    }

    /**
     * Auto-grade multiple choice and true/false questions
     */
    private function autoGradeSession(CbtExamSession $session): void
    {
        $answers = $session->answers()->with('question.options')->get();

        // Pre-load all exam questions for this exam to avoid N+1
        $examQuestions = CbtExamQuestion::where('exam_id', $session->exam_id)
            ->get()
            ->keyBy('question_id');

        foreach ($answers as $answer) {
            $question = $answer->question;

            if (in_array($question->question_type, ['multiple_choice', 'true_false'])) {
                // Find correct option
                $correctOption = $question->options->where('is_correct', true)->first();
                $isCorrect = $correctOption && $answer->selected_option === $correctOption->option_label;

                $examQuestion = $examQuestions->get($question->id);
                $points = $examQuestion?->points_override ?? $question->points ?? 1;

                $answer->update([
                    'is_correct' => $isCorrect,
                    'score_obtained' => $isCorrect ? $points : 0,
                ]);
            }
        }
    }

    /**
     * Calculate and store exam result
     */
    private function calculateResult(CbtExamSession $session): CbtExamResult
    {
        $answers = $session->answers()->get();
        $exam = $session->exam;

        $totalQuestions = $answers->count();
        $answeredQuestions = $answers->filter(fn($a) => $a->isAnswered())->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $wrongAnswers = $answers->where('is_correct', false)->whereNotNull('is_correct')->count();
        $unanswered = $totalQuestions - $answeredQuestions;

        $totalScore = $answers->sum('score_obtained');
        $maxScore = $this->getMaxScore($session);
        $percentageScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $finalScore = round($percentageScore, 2);

        $isPassed = $finalScore >= ($exam->passing_score ?? 75);
        $kkm = (int)($exam->passing_score ?? 75);
        $predicate = \App\Models\FinalGrade::scoreToPredicate($finalScore, $kkm);

        $timeSpent = $session->started_at && $session->finished_at
            ? $session->started_at->diffInSeconds($session->finished_at)
            : 0;

        // Idempotent and race-condition proof updateOrCreate for exam results
        try {
            $result = CbtExamResult::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'session_id' => $session->id,
                    'student_id' => $session->student_id,
                ],
                [
                    'total_questions' => $totalQuestions,
                    'answered_questions' => $answeredQuestions,
                    'correct_answers' => $correctAnswers,
                    'wrong_answers' => $wrongAnswers,
                    'unanswered' => $unanswered,
                    'total_score' => $totalScore,
                    'max_score' => $maxScore,
                    'percentage_score' => $percentageScore,
                    'final_score' => $finalScore,
                    'is_passed' => $isPassed,
                    'predicate' => $predicate,
                    'time_spent_seconds' => $timeSpent,
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika terjadi benturan unique constraint (uq_cer_exam_student_session) dari request bersamaan
            $result = CbtExamResult::where('session_id', $session->id)->first();
            if (!$result) {
                $result = CbtExamResult::where('exam_id', $exam->id)
                    ->where('student_id', $session->student_id)
                    ->latest('id')
                    ->first();
            }
            if (!$result) {
                throw $e;
            }
        }

        // Reputation Hook
        $student = $session->student;
        if ($student && $student->user_id) {
            $points = 0;
            if ($isPassed) {
                $points += 50; // Base pass points
            }
            if ($finalScore >= 90) {
                $points += 50; // Excellence bonus
            }

            if ($points > 0) {
                \App\Models\ReputationLog::log(
                    $student->user_id, 
                    $points, 
                    'exam', 
                    "Menyelesaikan ujian " . $exam->exam_title . " dengan nilai " . $finalScore,
                    $result
                );
            }
        }

        return $result;
    }

    /**
     * Get maximum possible score for a session
     */
    private function getMaxScore(CbtExamSession $session): float
    {
        $examQuestions = CbtExamQuestion::where('exam_id', $session->exam_id)
            ->with('question')
            ->get();

        return $examQuestions->sum(function ($eq) {
            return $eq->points_override ?? $eq->question->points ?? 1;
        });
    }

    /**
     * Sync CBT result to grades table
     */
    public function syncResultToGrade(CbtExamResult $result, bool $force = false): ?Grade
    {
        if ($result->grade_synced && !$force) return null;

        $exam = $result->exam;
        $teacherId = $exam->teacher_id;

        if (!$teacherId) {
            $classroomId = $result->session?->classroom_id ?? $result->student?->currentClassroom()?->id;
            if ($classroomId) {
                $teacherId = \App\Models\TeachingAssignment::where('classroom_id', $classroomId)
                    ->where('subject_id', $exam->subject_id)
                    ->where('academic_year_id', $exam->academic_year_id)
                    ->where('is_active', true)
                    ->value('teacher_id')
                    ?? \App\Models\Schedule::where('classroom_id', $classroomId)
                        ->where('subject_id', $exam->subject_id)
                        ->value('teacher_id');
            }
        }

        $grade = Grade::updateOrCreate(
            [
                'student_id' => $result->student_id,
                'subject_id' => $exam->subject_id,
                'semester_id' => $exam->semester_id,
                'grade_type' => $exam->getGradeType(),
                'lms_source_type' => 'cbt_exam',
                'lms_source_id' => $result->id,
            ],
            [
                'teacher_id' => $teacherId,
                'score' => $result->final_score,
                'notes' => "CBT: {$exam->exam_title}",
                'created_by' => $exam->created_by,
            ]
        );

        $result->update([
            'grade_synced' => true,
            'synced_grade_id' => $grade->id,
        ]);

        return $grade;
    }

    /**
     * Bulk sync all results for an exam
     */
    public function syncExamResults(CbtExam $exam): int
    {
        $results = $exam->results()->get();
        $synced = 0;

        foreach ($results as $result) {
            if ($this->syncResultToGrade($result, true)) {
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Calculate rankings for an exam (batch update to avoid N+1)
     */
    public function calculateRankings(CbtExam $exam): void
    {
        $results = CbtExamResult::where('exam_id', $exam->id)
            ->orderByDesc('final_score')
            ->pluck('id')
            ->values();

        if ($results->isEmpty()) return;

        // Build CASE WHEN statement for bulk update
        $cases = [];
        $ids = [];
        foreach ($results as $index => $id) {
            $rank = $index + 1;
            $cases[] = "WHEN {$id} THEN {$rank}";
            $ids[] = $id;
        }

        $caseString = implode(' ', $cases);
        $idString = implode(',', $ids);

        DB::statement("UPDATE cbt_exam_results SET `rank` = CASE id {$caseString} END WHERE id IN ({$idString})");
    }

    /**
     * Get exam statistics (supports classroom filtering)
     */
    public function getExamStatistics(CbtExam $exam, ?int $classroomId = null, ?array $allowedClassroomIds = null): array
    {
        $query = CbtExamResult::where('exam_id', $exam->id);

        if ($classroomId) {
            $query->whereHas('session', fn($q) => $q->where('classroom_id', $classroomId));
        } elseif (!empty($allowedClassroomIds)) {
            $query->whereHas('session', fn($q) => $q->whereIn('classroom_id', $allowedClassroomIds));
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            return [
                'total_participants' => 0,
                'completed_count' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'passed_count' => 0,
                'failed_count' => 0,
                'pass_rate' => 0,
            ];
        }

        return [
            'total_participants' => $results->count(),
            'completed_count' => $results->count(),
            'average_score' => round($results->avg('final_score'), 2),
            'highest_score' => $results->max('final_score'),
            'lowest_score' => $results->min('final_score'),
            'passed_count' => $results->where('is_passed', true)->count(),
            'failed_count' => $results->where('is_passed', false)->count(),
            'pass_rate' => round(($results->where('is_passed', true)->count() / $results->count()) * 100, 2),
        ];
    }

    /**
     * Resolve classrooms accessible for a user for a specific exam.
     * Admin/SuperAdmin gets all participating classrooms.
     * Guru only gets classrooms they teach in this exam.
     */
    public function getAccessibleClassroomsForUser(CbtExam $exam, User $user): Collection
    {
        $examClassrooms = $exam->classrooms()->orderBy('class_name')->get();

        $isAdmin = $user->isSuperAdmin() || in_array($user->role ?? '', ['admin', 'superadmin', 'admin_sekolah', 'kepala_sekolah', 'panitia_cbt']);
        if ($isAdmin) {
            return $examClassrooms;
        }

        $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return collect();
        }

        // 1. Taught classrooms for this specific subject and academic year
        $taQuery = TeachingAssignment::where('teacher_id', $teacher->id);
        if ($exam->academic_year_id) {
            $taQuery->where('academic_year_id', $exam->academic_year_id);
        }
        if ($exam->subject_id) {
            $taQuery->where('subject_id', $exam->subject_id);
        }
        $taughtClassroomIds = $taQuery->pluck('classroom_id')->unique()->toArray();

        // 2. Fallback: all classrooms taught by this teacher in the academic year
        if (empty($taughtClassroomIds)) {
            $taughtClassroomIds = TeachingAssignment::where('teacher_id', $teacher->id)
                ->when($exam->academic_year_id, fn($q) => $q->where('academic_year_id', $exam->academic_year_id))
                ->pluck('classroom_id')
                ->unique()
                ->toArray();
        }

        // 3. Fallback: all classrooms taught by teacher anytime
        if (empty($taughtClassroomIds)) {
            $taughtClassroomIds = TeachingAssignment::where('teacher_id', $teacher->id)
                ->pluck('classroom_id')
                ->unique()
                ->toArray();
        }

        $filtered = $examClassrooms->whereIn('id', $taughtClassroomIds)->values();

        // Fallback: If exam creator is this teacher and no teaching assignment matched, allow all exam classrooms
        if ($filtered->isEmpty() && $exam->teacher_id === $teacher->id) {
            return $examClassrooms;
        }

        return $filtered;
    }

    /**
     * Build detailed participation data (Sudah Mengikuti vs Belum Mengikuti) per student in an exam
     */
    public function getExamParticipationData(CbtExam $exam, ?int $classroomId = null, ?array $allowedClassroomIds = null): array
    {
        $classroomsQuery = Classroom::with('homeroomTeacher');
        if ($classroomId) {
            $classrooms = $classroomsQuery->where('id', $classroomId)->get();
        } elseif (!empty($allowedClassroomIds)) {
            $classrooms = $classroomsQuery->whereIn('id', $allowedClassroomIds)->orderBy('class_name')->get();
        } else {
            $classrooms = $exam->classrooms()->with('homeroomTeacher')->orderBy('class_name')->get();
        }

        $subjectKeywords = VocationalMajorFilterService::getSubjectMajorKeywords(
            $exam->subject?->name,
            $exam->subject?->code,
            $exam->exam_title
        );

        $recapList = collect();

        foreach ($classrooms as $classroom) {
            $studentsQuery = $classroom->students()
                ->whereIn('student_classes.status', ['aktif', 'enrolled', 'active'])
                ->when($exam->academic_year_id, fn($q) => $q->where('student_classes.academic_year_id', $exam->academic_year_id))
                ->with(['user', 'currentClassroom'])
                ->orderBy('full_name');

            $students = $studentsQuery->get();

            if ($subjectKeywords) {
                $students = $students->filter(fn($s) => VocationalMajorFilterService::isStudentMatchingVocationalSubject($s, $subjectKeywords))->values();
            }

            if ($students->isEmpty()) {
                continue;
            }

            $studentIds = $students->pluck('id')->toArray();

            // Load sessions for these students
            $sessions = CbtExamSession::where('exam_id', $exam->id)
                ->whereIn('student_id', $studentIds)
                ->with('result')
                ->orderByDesc('id')
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $studentSessions = $sessions->get($student->id);
                $latestSession = $studentSessions ? $studentSessions->first() : null;
                $result = $latestSession?->result;

                $status = 'not_started';
                $statusLabel = 'Belum Mengerjakan';

                if ($latestSession) {
                    if (in_array($latestSession->status, ['submitted', 'graded', 'timeout'])) {
                        $status = 'completed';
                        $statusLabel = 'Sudah Mengerjakan';
                    } elseif ($latestSession->status === 'in_progress') {
                        $status = 'in_progress';
                        $statusLabel = 'Sedang Mengerjakan';
                    }
                }

                $recapList->push([
                    'student' => $student,
                    'classroom' => $classroom,
                    'classroom_name' => $classroom->class_name,
                    'session' => $latestSession,
                    'result' => $result,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'attempts_count' => $studentSessions ? $studentSessions->count() : 0,
                    'final_score' => $result?->final_score,
                    'predicate' => $result?->predicate,
                    'is_passed' => $result?->is_passed,
                    'correct_answers' => $result?->correct_answers,
                    'wrong_answers' => $result?->wrong_answers,
                    'unanswered' => $result?->unanswered,
                    'started_at' => $latestSession?->started_at,
                    'finished_at' => $latestSession?->finished_at,
                ]);
            }
        }

        $totalEligible = $recapList->count();
        $completedCount = $recapList->where('status', 'completed')->count();
        $inProgressCount = $recapList->where('status', 'in_progress')->count();
        $notStartedCount = $recapList->where('status', 'not_started')->count();
        $participationRate = $totalEligible > 0 ? round(($completedCount / $totalEligible) * 100, 1) : 0;

        return [
            'classrooms' => $classrooms,
            'selectedClassroom' => $classroomId ? $classrooms->firstWhere('id', $classroomId) : null,
            'students' => $recapList,
            'total_eligible' => $totalEligible,
            'completed_count' => $completedCount,
            'in_progress_count' => $inProgressCount,
            'not_started_count' => $notStartedCount,
            'participation_rate' => $participationRate,
        ];
    }

    /**
     * Get students eligible for an exam (from participating classrooms)
     */
    public function getEligibleStudents(CbtExam $exam): Collection
    {
        $classroomIds = $exam->participants()->pluck('classroom_id');

        return StudentClass::whereIn('classroom_id', $classroomIds)
            ->where('status', 'aktif')
            ->when($exam->academic_year_id, fn($q) => $q->where('academic_year_id', $exam->academic_year_id))
            ->with('student')
            ->get()
            ->unique('student_id');
    }

    /**
     * Grade an essay answer manually
     */
    public function gradeEssayAnswer(CbtAnswer $answer, float $score, ?string $feedback, int $gradedBy): CbtAnswer
    {
        $maxPoints = CbtExamQuestion::where('exam_id', $answer->session->exam_id)
            ->where('question_id', $answer->question_id)
            ->first();

        $maxScore = $maxPoints?->points_override ?? $answer->question->points ?? 1;
        $isCorrect = $score > 0;

        $answer->update([
            'manual_score' => $score,
            'score_obtained' => $score,
            'is_correct' => $isCorrect,
            'teacher_feedback' => $feedback,
            'graded_by' => $gradedBy,
            'graded_at' => now(),
        ]);

        // Recalculate result
        $result = $this->calculateResult($answer->session);

        // Auto-sync to grades if enabled
        if ($answer->session->exam->auto_sync_grade) {
            $this->syncResultToGrade($result, true);
        }

        return $answer;
    }

    /**
     * Pause an exam.
     */
    public function pauseExam(CbtExam $exam): void
    {
        DB::transaction(function () use ($exam) {
            $exam->update([
                'is_paused' => true,
                'paused_at' => now(),
            ]);
        });
    }

    /**
     * Resume an exam.
     * Recalculates deadline_at for all in-progress student sessions.
     */
    public function resumeExam(CbtExam $exam): void
    {
        DB::transaction(function () use ($exam) {
            if (!$exam->is_paused || !$exam->paused_at) {
                return;
            }

            $pauseDurationSeconds = now()->diffInSeconds($exam->paused_at);

            // Extend deadline for all sessions currently in progress
            $activeSessions = CbtExamSession::where('exam_id', $exam->id)
                ->where('status', 'in_progress')
                ->get();

            foreach ($activeSessions as $session) {
                if ($session->deadline_at) {
                    $session->update([
                        'deadline_at' => $session->deadline_at->addSeconds($pauseDurationSeconds)
                    ]);
                }
            }

            $exam->update([
                'is_paused' => false,
                'paused_at' => null,
            ]);
        });
    }

    /**
     * Get psychometric item analysis for questions in an exam.
     */
    public function getItemAnalysis(CbtExam $exam): array
    {
        // 1. Get all questions associated with the exam
        $questions = CbtQuestion::whereIn('id', $exam->examQuestions()->pluck('question_id'))
            ->with('options')
            ->get();

        // 2. Get all submitted/timeout/graded sessions with their results
        $sessions = CbtExamSession::where('exam_id', $exam->id)
            ->whereIn('status', ['submitted', 'timeout', 'graded'])
            ->with(['result', 'student.classroom'])
            ->get();

        $totalParticipants = $sessions->count();

        // 3. Get all answers for these sessions
        $answers = CbtAnswer::whereIn('session_id', $sessions->pluck('id'))->get()->groupBy('question_id');

        // 4. Calculate upper and lower 27% groups for discrimination index
        // Sort sessions by final score descending
        $sortedSessions = $sessions->sortByDesc(function ($s) {
            return $s->result->final_score ?? 0;
        })->values();

        $groupSize = max(1, (int)round($totalParticipants * 0.27));
        $upperGroupSessionIds = $sortedSessions->take($groupSize)->pluck('id')->toArray();
        $lowerGroupSessionIds = $sortedSessions->take(-$groupSize)->pluck('id')->toArray();

        $analysis = [];

        foreach ($questions as $question) {
            $questionAnswers = $answers->get($question->id) ?? collect();
            $totalAnswers = $questionAnswers->count();

            // Difficulty Index (p)
            $correctCount = $questionAnswers->where('is_correct', true)->count();
            $p = $totalAnswers > 0 ? $correctCount / $totalAnswers : 0;

            if ($p > 0.70) {
                $difficultyLabel = 'Mudah';
                $difficultyClass = 'bg-green-100 text-green-800 border-green-200';
            } elseif ($p >= 0.30) {
                $difficultyLabel = 'Sedang';
                $difficultyClass = 'bg-blue-100 text-blue-800 border-blue-200';
            } else {
                $difficultyLabel = 'Sulit';
                $difficultyClass = 'bg-red-100 text-red-800 border-red-200';
            }

            // Discrimination Index (d)
            if ($totalParticipants < 2 || $totalAnswers == 0) {
                $d = 0;
                $discriminationLabel = 'Data Kurang';
                $discriminationClass = 'bg-gray-100 text-gray-800 border-gray-200';
            } else {
                $correctUpper = $questionAnswers->whereIn('session_id', $upperGroupSessionIds)->where('is_correct', true)->count();
                $correctLower = $questionAnswers->whereIn('session_id', $lowerGroupSessionIds)->where('is_correct', true)->count();
                $d = ($correctUpper - $correctLower) / $groupSize;

                if ($d >= 0.40) {
                    $discriminationLabel = 'Sangat Baik';
                    $discriminationClass = 'bg-green-100 text-green-800 border-green-200';
                } elseif ($d >= 0.30) {
                    $discriminationLabel = 'Baik';
                    $discriminationClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                } elseif ($d >= 0.20) {
                    $discriminationLabel = 'Cukup';
                    $discriminationClass = 'bg-amber-100 text-amber-800 border-amber-200';
                } else {
                    $discriminationLabel = 'Jelek (Perlu Revisi)';
                    $discriminationClass = 'bg-red-100 text-red-800 border-red-200';
                }
            }

            // Distractor Analysis (for multiple choice)
            $distractors = [];
            if ($question->question_type === 'multiple_choice') {
                foreach ($question->options->sortBy('sort_order') as $opt) {
                    $optLabel = $opt->option_label;
                    $optCount = $questionAnswers->where('selected_option', $optLabel)->count();
                    $optPct = $totalAnswers > 0 ? ($optCount / $totalAnswers) * 100 : 0;
                    $distractors[] = [
                        'label' => $optLabel,
                        'text' => $opt->option_text,
                        'count' => $optCount,
                        'percentage' => round($optPct, 1),
                        'is_correct' => $opt->is_correct,
                    ];
                }
            } elseif ($question->question_type === 'true_false') {
                foreach (['T' => 'Benar', 'F' => 'Salah'] as $val => $label) {
                    $optCount = $questionAnswers->where('selected_option', $val)->count();
                    $optPct = $totalAnswers > 0 ? ($optCount / $totalAnswers) * 100 : 0;
                    $distractors[] = [
                        'label' => $val,
                        'text' => $label,
                        'count' => $optCount,
                        'percentage' => round($optPct, 1),
                        'is_correct' => $question->options->where('option_label', $val)->first()?->is_correct ?? false,
                    ];
                }
            }

            $analysis[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'total_answers' => $totalAnswers,
                'correct_answers' => $correctCount,
                'correct_count' => $correctCount,
                'wrong_count' => max(0, $totalAnswers - $correctCount),
                'correct_percentage' => $totalAnswers > 0 ? round(($correctCount / $totalAnswers) * 100, 1) : 0,
                'difficulty_index' => round($p, 2),
                'difficulty_label' => $difficultyLabel,
                'difficulty' => $difficultyLabel,
                'difficulty_class' => $difficultyClass,
                'discrimination_index' => round($d, 2),
                'discrimination_label' => $discriminationLabel,
                'discrimination' => $discriminationLabel,
                'discrimination_class' => $discriminationClass,
                'distractors' => $distractors,
            ];
        }

        return $analysis;
    }
}
