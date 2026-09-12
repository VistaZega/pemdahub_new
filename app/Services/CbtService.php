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
        return DB::transaction(function () use ($exam, $student, $classroomId) {
            // Pessimistic lock: prevent duplicate session creation under concurrency
            $existingSession = CbtExamSession::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->lockForUpdate()
                ->first();

            if ($existingSession) {
                return $existingSession;
            }

            // Check max attempts (with lock to prevent race conditions)
            $attemptCount = CbtExamSession::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'timeout', 'graded'])
                ->lockForUpdate()
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
        });
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
     * Get exam statistics
     */
    public function getExamStatistics(CbtExam $exam): array
    {
        $results = CbtExamResult::where('exam_id', $exam->id)->get();

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
                'difficulty_index' => round($p, 2),
                'difficulty_label' => $difficultyLabel,
                'difficulty_class' => $difficultyClass,
                'discrimination_index' => round($d, 2),
                'discrimination_label' => $discriminationLabel,
                'discrimination_class' => $discriminationClass,
                'distractors' => $distractors,
            ];
        }

        return $analysis;
    }
}
