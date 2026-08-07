<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizAnswer;
use App\Models\LmsDiscussion;
use App\Models\LmsDiscussionReply;
use App\Models\LmsMaterialProgress;
use App\Models\LmsMaterial;
use App\Models\LmsModule;
use App\Models\LmsMeetingSession;
use App\Models\LmsMeetingAttendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsController extends Controller
{
    private function getStudent(): ?Student
    {
        return Student::where('user_id', Auth::id())->first();
    }

    /**
     * List enrolled courses with progress
     */
    public function index()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $enrollments = LmsEnrollment::where('student_id', $student->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->with(['lmsClass.course' => fn($q) => $q->with(['subject', 'teacher.user'])
                ->withCount(['modules', 'materials', 'assignments', 'quizzes', 'discussions']),
                    'lmsClass.classroom'])
            ->get();

        $courses = $enrollments->map(fn($e) => $e->lmsClass->course)->filter()->unique('id');

        // Calculate progress per course
        $courseProgress = [];
        foreach ($courses as $course) {
            $courseProgress[$course->id] = LmsMaterialProgress::getProgressForCourse($course->id, $student->id);
        }

        // Calculate upcoming deadlines (Assignments & Quizzes in next 7 days)
        $courseIds = $courses->pluck('id');
        $now = \Carbon\Carbon::now();
        $next7Days = \Carbon\Carbon::now()->addDays(7);

        $upcomingAssignments = LmsAssignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->whereBetween('deadline', [$now, $next7Days])
            ->with(['course.subject'])
            ->orderBy('deadline')
            ->get();

        $upcomingQuizzes = LmsQuiz::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->whereBetween('created_at', [$now->subDays(7), $next7Days])
            ->with(['course.subject'])
            ->orderByDesc('created_at')
            ->get();

        $leaderboard = \App\Models\Reputation::with('user')
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();

        return view('siswa.lms.index', compact('student', 'courses', 'courseProgress', 'upcomingAssignments', 'upcomingQuizzes', 'leaderboard'));
    }

    /**
     * Show course detail - materials, assignments, quizzes, announcements, discussions
     */
    public function show(LmsCourse $course)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403, 'Anda tidak terdaftar di course ini.');
        }

        $course->load([
            'subject', 'teacher.user',
            'materials' => fn($q) => $q->where('is_published', true)->orderBy('order_number'),
            'modules' => fn($q) => $q->where('is_active', true)->orderBy('sequence')->with([
                'materials' => fn($mq) => $mq->where('is_published', true)->orderBy('order_number'),
                'games' => fn($gq) => $gq->where('is_published', true)->orderBy('created_at')
            ]),
            'assignments' => fn($q) => $q->where('is_published', true)->orderByDesc('deadline'),
            'quizzes' => fn($q) => $q->where('is_published', true)->orderByDesc('created_at'),
            'announcements' => fn($q) => $q->where('is_published', true)->with('author')->orderByDesc('is_pinned')->orderByDesc('published_at')->limit(10),
        ]);

        // Get student's submissions for this course's assignments
        $submissionMap = LmsSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $course->assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        // Get student's quiz attempts
        $attemptMap = LmsQuizAttempt::where('student_id', $student->id)
            ->whereIn('quiz_id', $course->quizzes->pluck('id'))
            ->get()
            ->groupBy('quiz_id');

        // Get student's game attempts
        $gameAttemptMap = \App\Models\LmsGameAttempt::where('student_id', $student->id)
            ->whereIn('game_id', $course->modules->flatMap->games->pluck('id'))
            ->get()
            ->keyBy('game_id');

        // Get material progress
        $materialProgressMap = LmsMaterialProgress::where('student_id', $student->id)
            ->whereIn('material_id', $course->materials->pluck('id'))
            ->get()
            ->keyBy('material_id');

        // Completed material IDs for sequential lock checks
        $completedMaterialIds = LmsMaterialProgress::where('student_id', $student->id)
            ->where('status', 'completed')
            ->pluck('material_id')
            ->toArray();

        // Course overall progress
        $courseProgress = LmsMaterialProgress::getProgressForCourse($course->id, $student->id);

        // Discussion count
        $discussionCount = $course->discussions()->count();

        // Get student's reactions
        $reactionsMap = \App\Models\LmsMaterialReaction::where('student_id', $student->id)
            ->whereIn('material_id', $course->materials->pluck('id'))
            ->get()
            ->keyBy('material_id');

        return view('siswa.lms.show', compact(
            'student', 'course', 'submissionMap', 'attemptMap', 'gameAttemptMap',
            'materialProgressMap', 'courseProgress', 'discussionCount',
            'reactionsMap', 'completedMaterialIds'
        ));
    }

    /**
     * Focus Reader Material Player View
     */
    public function playerMaterial(LmsMaterial $material)
    {
        $student = $this->getStudent();
        if (!$student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $course = $material->course;
        if (!$course || !$this->isEnrolled($student, $course)) {
            abort(403, 'Anda tidak terdaftar di course ini.');
        }

        // Lock check
        if ($this->isMaterialLocked($material, $student)) {
            return redirect()->route('siswa.lms.show', $course->id)
                ->with('error', 'Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu.');
        }

        // Auto mark as in_progress if not set
        LmsMaterialProgress::firstOrCreate(
            ['student_id' => $student->id, 'material_id' => $material->id],
            ['status' => 'in_progress', 'created_at' => now()]
        );

        $note = \App\Models\LmsMaterialNote::where('student_id', $student->id)
            ->where('material_id', $material->id)
            ->first();

        // Load course modules and materials for sidebar navigation
        $course->load([
            'subject', 'teacher.user',
            'modules' => fn($q) => $q->where('is_active', true)->orderBy('sequence')->with([
                'materials' => fn($mq) => $mq->where('is_published', true)->orderBy('order_number')
            ])
        ]);

        $completedMaterialIds = LmsMaterialProgress::where('student_id', $student->id)
            ->where('status', 'completed')
            ->pluck('material_id')
            ->toArray();

        return view('siswa.lms.material_player', compact('student', 'course', 'material', 'note', 'completedMaterialIds'));
    }

    /**
     * AJAX Save Student Note for Material
     */
    public function saveNote(Request $request, LmsMaterial $material)
    {
        $student = $this->getStudent();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $note = \App\Models\LmsMaterialNote::updateOrCreate(
            ['student_id' => $student->id, 'material_id' => $material->id],
            ['notes' => $request->input('notes')]
        );

        return response()->json(['success' => true, 'message' => 'Catatan berhasil disimpan.', 'data' => $note]);
    }

    /**
     * Check if material is locked for sequential learning
     */
    private function isMaterialLocked(LmsMaterial $material, Student $student): bool
    {
        $course = $material->course;
        $module = $material->module;

        $isSequential = ($course && $course->is_sequential) || ($module && $module->is_sequential) || $material->prerequisite_material_id;
        if (!$isSequential) {
            return false;
        }

        // Check prerequisite material
        if ($material->prerequisite_material_id) {
            $prereqCompleted = LmsMaterialProgress::where('student_id', $student->id)
                ->where('material_id', $material->prerequisite_material_id)
                ->where('status', 'completed')
                ->exists();
            if (!$prereqCompleted) {
                return true;
            }
        }

        // Check previous material in course order
        $previousMaterial = LmsMaterial::where('course_id', $material->course_id)
            ->where('is_published', true)
            ->where('id', '!=', $material->id)
            ->where('order_number', '<', $material->order_number)
            ->orderByDesc('order_number')
            ->first();

        if ($previousMaterial) {
            $prevCompleted = LmsMaterialProgress::where('student_id', $student->id)
                ->where('material_id', $previousMaterial->id)
                ->where('status', 'completed')
                ->exists();
            if (!$prevCompleted) {
                return true;
            }
        }

        return false;
    }

    /**
     * Track material view / completion
     */
    public function trackMaterial(Request $request, $materialId)
    {
        $student = $this->getStudent();
        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $material = LmsMaterial::find($materialId);
        if (!$material) {
            return response()->json(['error' => 'Material not found'], 404);
        }

        $course = $material->course;
        if (!$course || !$this->isEnrolled($student, $course)) {
            return response()->json(['error' => 'Not enrolled in this course'], 403);
        }

        $request->validate([
            'status' => 'required|in:viewed,in_progress,completed',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        $progress = LmsMaterialProgress::firstOrNew(
            ['material_id' => $materialId, 'student_id' => $student->id]
        );

        if ($request->status === 'completed') {
            $isAlreadyCompleted = $progress->status === 'completed';
            $progress->markCompleted();

            // Give EXP for completing material (Gamification)
            if (!$isAlreadyCompleted && $student->user_id) {
                try {
                    \App\Models\ReputationLog::log(
                        $student->user_id,
                        50, // Base EXP for reading material
                        'LMS Material',
                        'Membaca materi: ' . $material->title,
                        $material
                    );
                } catch (\Exception $e) {
                    \Log::error('Gagal memberikan EXP LMS: ' . $e->getMessage());
                }
            }
        } else {
            $progress->fill([
                'status' => $request->status,
                'first_viewed_at' => $progress->first_viewed_at ?? now(),
                'progress_percent' => $request->status === 'in_progress' ? 50 : 10,
            ])->save();
        }

        if ($request->time_spent) {
            $progress->increment('time_spent_seconds', $request->time_spent);
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json(['success' => true, 'progress' => $progress]);
        }

        // Standard web form submission redirect logic
        $currentMaterialId = (int) $material->id;
        $moduleId = $material->module_id;
        $courseId = $material->course_id;

        // 1. Find next material in current module
        $nextMaterial = null;
        if ($moduleId) {
            $nextMaterial = LmsMaterial::where('module_id', $moduleId)
                ->where('id', '>', $currentMaterialId)
                ->orderBy('order_number', 'asc')
                ->orderBy('id', 'asc')
                ->first();
        }

        // 2. If not found in current module, check next module's first material
        if (!$nextMaterial && $courseId) {
            $currentModuleSeq = $material->module->sequence ?? 0;
            $nextModule = LmsModule::where('course_id', $courseId)
                ->where(function($q) use ($moduleId, $currentModuleSeq) {
                    if ($currentModuleSeq > 0) {
                        $q->where('sequence', '>', $currentModuleSeq);
                    }
                    if ($moduleId) {
                        $q->orWhere('id', '>', $moduleId);
                    }
                })
                ->orderBy('sequence', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($nextModule) {
                $nextMaterial = $nextModule->materials()
                    ->orderBy('order_number', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();
            }
        }

        // 3. Fallback: next material in course by ID
        if (!$nextMaterial && $courseId) {
            $nextMaterial = LmsMaterial::where('course_id', $courseId)
                ->where('id', '>', $currentMaterialId)
                ->orderBy('id', 'asc')
                ->first();
        }

        if ($nextMaterial) {
            return redirect()->route('siswa.lms.materials.player', $nextMaterial->id)
                ->with('success', 'Materi berhasil diselesaikan! Melanjutkan ke materi berikutnya (+50 EXP)');
        }

        return redirect()->route('siswa.lms.show', $courseId)
            ->with('success', 'Selamat! Anda telah menyelesaikan seluruh materi pada kelas ini! (+50 EXP)');
    }

    /**
     * Submit reaction to material
     */
    public function reactMaterial(Request $request, $materialId)
    {
        $student = $this->getStudent();
        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $material = LmsMaterial::find($materialId);
        if (!$material) {
            return response()->json(['error' => 'Material not found'], 404);
        }

        $request->validate([
            'reaction_type' => 'required|in:like,confused,insightful',
        ]);

        $reaction = \App\Models\LmsMaterialReaction::updateOrCreate(
            ['material_id' => $materialId, 'student_id' => $student->id],
            ['reaction_type' => $request->reaction_type]
        );

        return response()->json(['success' => true, 'reaction' => $reaction]);
    }

    /**
     * Submit assignment (with resubmission support)
     */
    public function submitAssignment(Request $request, LmsAssignment $assignment)
    {
        $student = $this->getStudent();
        $course = $assignment->course;
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403);
        }

        $request->validate([
            'submission_text' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);

        // Check if resubmission
        $existing = LmsSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing && $existing->status !== 'draft') {
            // This is a resubmission
            if (!$assignment->allow_resubmit) {
                return redirect()->back()->with('error', 'Tugas ini tidak mengizinkan pengumpulan ulang.');
            }
            if ($existing->attempt_number >= ($assignment->max_resubmissions + 1)) {
                return redirect()->back()->with('error', 'Batas pengumpulan ulang sudah tercapai.');
            }
        }

        $filePath = null;
        $fileSize = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/submissions', 'public');
            $fileSize = $request->file('file')->getSize();
        }

        $isLate = $assignment->deadline && now()->isAfter($assignment->deadline);
        $attemptNumber = $existing ? $existing->attempt_number + ($existing->status !== 'draft' ? 1 : 0) : 1;

        LmsSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'submission_text' => $request->submission_text,
                'file_path' => $filePath ?? ($existing ? $existing->file_path : null),
                'file_size' => $fileSize ?? ($existing ? $existing->file_size : null),
                'status' => $isLate ? 'late' : 'submitted',
                'submitted_at' => now(),
                'score' => null, // Reset score on resubmit
                'feedback' => null,
                'graded_at' => null,
                'graded_by' => null,
                'attempt_number' => $attemptNumber,
            ]
        );

        $msg = 'Tugas berhasil dikumpulkan';
        if ($attemptNumber > 1) $msg = 'Tugas berhasil dikumpulkan ulang (percobaan ke-' . $attemptNumber . ')';
        if ($isLate) $msg .= ' (terlambat)';

        return redirect()->route('siswa.lms.show', $course->id)
            ->with('success', $msg . '.');
    }

    /**
     * Start quiz attempt (with multi-attempt support)
     */
    public function startQuiz(LmsQuiz $quiz)
    {
        $student = $this->getStudent();
        $course = $quiz->course;
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403);
        }

        if (!$quiz->isAvailable()) {
            return redirect()->route('siswa.lms.show', $course->id)
                ->with('error', 'Quiz ini belum tersedia.');
        }

        // Check if student can still attempt
        if (!$quiz->canAttempt($student->id)) {
            return redirect()->route('siswa.lms.show', $course->id)
                ->with('error', 'Anda sudah mencapai batas percobaan untuk quiz ini.');
        }

        // Get existing unfinished attempt or create new one
        $attempt = LmsQuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNull('finished_at')
            ->first();

        if (!$attempt) {
            $attempt = LmsQuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'started_at' => now(),
            ]);
        }

        // Load questions (optionally shuffled)
        $questionsQuery = $quiz->questions();
        if ($quiz->shuffle_questions) {
            $questionsQuery->inRandomOrder($attempt->id);
        } else {
            $questionsQuery->orderBy('order_number');
        }
        $questions = $questionsQuery->get();

        // Get existing answers
        $answerMap = $attempt->answers()->get()->keyBy('question_id');

        $remainingAttempts = $quiz->getRemainingAttempts($student->id);

        return view('siswa.lms.quiz', compact('student', 'course', 'quiz', 'attempt', 'questions', 'answerMap', 'remainingAttempts'));
    }

    /**
     * Submit quiz answers
     */
    public function submitQuiz(Request $request, LmsQuizAttempt $attempt)
    {
        $student = $this->getStudent();
        if (!$student || $attempt->student_id !== $student->id) {
            abort(403);
        }

        if ($attempt->finished_at) {
            return redirect()->route('siswa.lms.show', $attempt->quiz->course_id)
                ->with('error', 'Quiz sudah selesai dikerjakan.');
        }

        $quiz = $attempt->quiz;
        $quiz->load('questions');

        $totalScore = 0;
        $maxScore = $quiz->questions->sum('score');

        foreach ($quiz->questions as $question) {
            $answer = $request->input("answers.{$question->id}");
            $finalAnswer = $answer;

            $isCorrect = null;
            $questionScore = null;

            if ($question->isAutoGradable() && $question->correct_answer !== null && $question->correct_answer !== '') {
                $studentAnswer = trim($answer ?? '');
                $correctAnswer = trim($question->correct_answer);

                // For multiple choice, resolve index-based answers to actual option text
                // This handles both formats:
                // 1. Associative: options=[{key:'A',text:'...'}, ...], correct_answer='A', answer='A'
                // 2. Non-associative: options=['text1','text2',...], correct_answer='0', answer='0'
                if ($question->question_type === 'multiple_choice' && $question->options) {
                    $options = $question->options;
                    $firstOpt = $options[0] ?? null;
                    $isAssoc = is_array($firstOpt) && isset($firstOpt['key']);

                    if ($isAssoc) {
                        // Normalisasi correct_answer: jika berupa angka, ubah ke alfabet A/B/C/D
                        if (preg_match('/^\d+$/', $correctAnswer)) {
                            $alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                            $correctAnswer = $alphabets[(int)$correctAnswer] ?? $correctAnswer;
                        }
                        // Normalisasi studentAnswer: jika berupa angka, ubah ke alfabet A/B/C/D
                        if (preg_match('/^\d+$/', $studentAnswer)) {
                            $alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                            $studentAnswer = $alphabets[(int)$studentAnswer] ?? $studentAnswer;
                        }

                        // Associative format: compare key directly (A vs A)
                        $isCorrect = strtolower($studentAnswer) === strtolower($correctAnswer);
                    } else {
                        // Normalisasi correct_answer: jika alfabet A/B/C/D, ubah ke indeks angka
                        if (!preg_match('/^\d+$/', $correctAnswer)) {
                            $alphabetMap = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5, 'g' => 6];
                            $lowerCorrect = strtolower($correctAnswer);
                            if (isset($alphabetMap[$lowerCorrect])) {
                                $correctAnswer = (string)$alphabetMap[$lowerCorrect];
                            }
                        }
                        // Normalisasi studentAnswer: jika alfabet A/B/C/D, ubah ke indeks angka
                        if (!preg_match('/^\d+$/', $studentAnswer)) {
                            $alphabetMap = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5, 'g' => 6];
                            $lowerStudent = strtolower($studentAnswer);
                            if (isset($alphabetMap[$lowerStudent])) {
                                $studentAnswer = (string)$alphabetMap[$lowerStudent];
                            }
                        }

                        // Non-associative format: resolve index to actual option text for comparison
                        // This makes scoring shuffle-proof
                        $shuffledOptions = $quiz->shuffle_questions ? $question->getShuffledOptions($attempt->id) : $options;
                        $studentText = $shuffledOptions[(int)$studentAnswer] ?? null;
                        $correctText = $options[(int)$correctAnswer] ?? null;
                        $isCorrect = $studentText !== null && $correctText !== null
                            && strtolower(trim((string)$studentText)) === strtolower(trim((string)$correctText));

                        // Map student answer back to the original index for storage
                        if ($studentText !== null && $quiz->shuffle_questions) {
                            $origIdx = null;
                            foreach ($options as $k => $val) {
                                if (strtolower(trim((string)$val)) === strtolower(trim((string)$studentText))) {
                                    $origIdx = $k;
                                    break;
                                }
                            }
                            if ($origIdx !== null) {
                                $finalAnswer = (string)$origIdx;
                            }
                        }
                    }
                } else {
                    // true_false or other: direct string comparison
                    $isCorrect = strtolower($studentAnswer) === strtolower($correctAnswer);
                }

                $questionScore = $isCorrect ? $question->score : 0;
                $totalScore += $questionScore;
            }

            LmsQuizAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                [
                    'answer' => $finalAnswer,
                    'is_correct' => $isCorrect,
                    'score' => $questionScore,
                ]
            );
        }

        $scorePercentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        $attempt->update([
            'finished_at' => now(),
            'score' => $scorePercentage,
            'is_passed' => $scorePercentage >= $quiz->passing_score,
        ]);

        // Auto-sync quiz score to grades table
        try {
            $gradeService = app(\App\Services\GradeService::class);
            $gradeService->syncQuizAttemptToGrade($attempt);
        } catch (\Exception $e) {
            \Log::warning('LMS quiz sync failed: ' . $e->getMessage());
        }

        // Give EXP for completing Quiz (Gamification)
        if ($student->user_id) {
            try {
                $expEarned = 50 + (int)($scorePercentage / 2); // 50 base + up to 50 for score
                \App\Models\ReputationLog::log(
                    $student->user_id,
                    $expEarned,
                    'LMS Quiz',
                    'Menyelesaikan kuis: ' . $quiz->title . ' (' . number_format($scorePercentage, 1) . '%)',
                    $attempt
                );
            } catch (\Exception $e) {
                \Log::error('Gagal memberikan EXP Quiz: ' . $e->getMessage());
            }
        }

        $remaining = $quiz->getRemainingAttempts($student->id);
        $msg = "Quiz selesai! Skor: " . number_format($scorePercentage, 1) . "%";
        if ($remaining > 0) {
            $msg .= " (sisa {$remaining} percobaan)";
        }

        if ($quiz->show_result) {
            return redirect()->route('siswa.lms.quizzes.result', $attempt->id)
                ->with('success', $msg);
        }

        return redirect()->route('siswa.lms.show', [$quiz->course_id, 'tab' => 'quizzes'])
            ->with('success', $msg);
    }

    /**
     * Show quiz result
     */
    public function quizResult(LmsQuizAttempt $attempt)
    {
        $student = $this->getStudent();
        if (!$student || $attempt->student_id !== $student->id) {
            abort(403);
        }

        $quiz = $attempt->quiz;
        $course = $quiz->course;

        if (!$quiz->show_result) {
            return redirect()->route('siswa.lms.show', [$course->id, 'tab' => 'quizzes'])
                ->with('info', 'Hasil quiz tidak ditampilkan untuk quiz ini.');
        }

        $attempt->load(['answers.question']);
        $quiz->load('questions');

        return view('siswa.lms.quiz-result', compact('student', 'course', 'quiz', 'attempt'));
    }

    // ================================================================
    // DISCUSSIONS
    // ================================================================

    /**
     * Show discussions for a course
     */
    public function discussions(LmsCourse $course)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403);
        }

        $discussions = $course->discussions()
            ->with(['author', 'latestReply.author'])
            ->withCount('replies')
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at')
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        return view('siswa.lms.discussions.index', compact('student', 'course', 'discussions'));
    }

    /**
     * Create new discussion
     */
    public function storeDiscussion(Request $request, LmsCourse $course)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:300',
            'content' => 'required|string',
            'type' => 'required|in:discussion,question',
        ]);

        $course->discussions()->create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
        ]);

        return redirect()->route('siswa.lms.discussions.index', $course)
            ->with('success', 'Diskusi berhasil dibuat.');
    }

    /**
     * Show discussion detail
     */
    public function showDiscussion(LmsCourse $course, LmsDiscussion $discussion)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403);
        }

        $discussion->load([
            'author',
            'topLevelReplies' => fn($q) => $q->with(['author', 'children.author'])->orderBy('created_at'),
        ]);

        return view('siswa.lms.discussions.show', compact('student', 'course', 'discussion'));
    }

    /**
     * Edit discussion topic
     */
    public function editDiscussion(LmsCourse $course, LmsDiscussion $discussion)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course) || $discussion->user_id !== Auth::id()) {
            abort(403);
        }

        return view('siswa.lms.discussions.edit', compact('student', 'course', 'discussion'));
    }

    /**
     * Update discussion topic
     */
    public function updateDiscussion(Request $request, LmsCourse $course, LmsDiscussion $discussion)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course) || $discussion->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:300',
            'content' => 'required|string',
            'type' => 'required|in:discussion,question',
        ]);

        $discussion->update([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
        ]);

        return redirect()->route('siswa.lms.discussions.show', [$course->id, $discussion->id])
            ->with('success', 'Topik diskusi berhasil diperbarui.');
    }

    /**
     * Reply to discussion
     */
    public function replyDiscussion(Request $request, LmsCourse $course, LmsDiscussion $discussion)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403);
        }

        if ($discussion->is_locked) {
            return redirect()->back()->with('error', 'Diskusi ini sudah dikunci.');
        }

        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:lms_discussion_replies,id',
        ]);

        $discussion->replies()->create([
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        $discussion->incrementRepliesCount();

        return redirect()->route('siswa.lms.discussions.show', [$course->id, $discussion->id])
            ->with('success', 'Balasan berhasil ditambahkan.');
    }

    // ================================================================
    // COURSE CATALOG & SELF-ENROLLMENT
    // ================================================================

    /**
     * Browse available courses (not yet enrolled)
     */
    public function catalog()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        // Get courses the student is already enrolled in
        $enrolledCourseIds = LmsEnrollment::where('student_id', $student->id)
            ->whereIn('status', ['enrolled', 'in_progress', 'completed'])
            ->with('lmsClass')
            ->get()
            ->pluck('lmsClass.course_id')
            ->filter()
            ->unique()
            ->toArray();

        // Get available published courses (same school, not enrolled)
        $courses = LmsCourse::where('school_id', $student->school_id)
            ->where(function ($q) {
                $q->where('is_published', true)
                  ->orWhere('status', 'active');
            })
            ->whereNotIn('id', $enrolledCourseIds)
            ->with(['subject', 'teacher.user', 'classroom'])
            ->withCount(['materials', 'assignments', 'quizzes'])
            ->orderByDesc('created_at')
            ->paginate(12)->withQueryString();

        return view('siswa.lms.catalog', compact('student', 'courses'));
    }

    /**
     * Self-enroll in a course
     */
    public function enroll(LmsCourse $course)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        // Check if already enrolled
        if ($this->isEnrolled($student, $course)) {
            return redirect()->route('siswa.lms.show', $course->id)
                ->with('info', 'Anda sudah terdaftar di course ini.');
        }

        // Check if course is published
        if (!$course->is_published && $course->status !== 'active') {
            return redirect()->route('siswa.lms.catalog')
                ->with('error', 'Course ini belum tersedia.');
        }

        // Find or create an LmsClass for the student's classroom
        $studentClassroom = $student->classrooms()
            ->wherePivot('status', 'aktif')
            ->orderByDesc('id')
            ->first();

        if (!$studentClassroom) {
            return redirect()->route('siswa.lms.catalog')
                ->with('error', 'Anda belum memiliki kelas aktif.');
        }

        $lmsClass = \App\Models\LmsClass::firstOrCreate([
            'course_id' => $course->id,
            'classroom_id' => $studentClassroom->id,
        ], [
            'school_id' => $student->school_id,
            'status' => 'active',
        ]);

        LmsEnrollment::firstOrCreate([
            'lms_class_id' => $lmsClass->id,
            'student_id' => $student->id,
        ], [
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        return redirect()->route('siswa.lms.show', $course->id)
            ->with('success', 'Berhasil mendaftar di course ' . $course->name . '.');
    }

    private function isEnrolled(Student $student, LmsCourse $course): bool
    {
        return LmsEnrollment::whereHas('lmsClass', fn($q) => $q->where('course_id', $course->id))
            ->where('student_id', $student->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->exists();
    }

    /**
     * Download material file (student must be enrolled in the course)
     */
    public function downloadMaterial(\App\Models\LmsMaterial $material)
    {
        $student = $this->getStudent();
        if (!$student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $course = $material->course;
        if (!$course || !$this->isEnrolled($student, $course)) {
            abort(403, 'Anda tidak terdaftar di course ini.');
        }

        if (!$material->file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($material->file_path, $material->title);
    }

    /**
     * View material file inline (student must be enrolled in the course)
     */
    public function viewMaterial(\App\Models\LmsMaterial $material)
    {
        $student = $this->getStudent();
        if (!$student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $course = $material->course;
        if (!$course || !$this->isEnrolled($student, $course)) {
            abort(403, 'Anda tidak terdaftar di course ini.');
        }

        if (!$material->file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($material->file_path);
        $mimeType = \Illuminate\Support\Facades\File::mimeType($path);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline'
        ]);
    }

    /**
     * Join the active video conference meeting for this course
     * Sekaligus mencatat kehadiran siswa
     */
    public function joinMeeting(LmsCourse $course)
    {
        $student = $this->getStudent();
        if (!$student || !$this->isEnrolled($student, $course)) {
            abort(403, 'Anda tidak terdaftar di course ini.');
        }

        if (!$course->meeting_active) {
            return redirect()->route('siswa.lms.show', $course->id)
                ->with('error', 'Kelas tatap muka virtual sedang tidak aktif.');
        }

        // Catat kehadiran siswa
        $sessionId = cache()->get('lms_meeting_session_' . $course->id);
        if ($sessionId) {
            LmsMeetingAttendance::firstOrCreate(
                ['session_id' => $sessionId, 'student_id' => $student->id],
                [
                    'course_id'  => $course->id,
                    'joined_at'  => now(),
                ]
            );
        }

        $roomName    = 'PembdaHub_Course_' . $course->id . '_' . md5($course->code . config('app.key'));
        $displayName = $student->full_name;

        return view('siswa.lms.meeting', compact('student', 'course', 'roomName', 'displayName'));
    }

    /**
     * AJAX: Siswa meninggalkan meeting — catat left_at dan durasi
     */
    public function leaveAttendance(Request $request, LmsCourse $course)
    {
        $student = $this->getStudent();
        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sessionId = cache()->get('lms_meeting_session_' . $course->id);
        if ($sessionId) {
            $attendance = LmsMeetingAttendance::where('session_id', $sessionId)
                ->where('student_id', $student->id)
                ->whereNull('left_at')
                ->first();

            if ($attendance) {
                $attendance->recordLeave();
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Status kelas live untuk siswa (polling tiap 30 detik)
     * Returns array of course IDs yang meeting_active = true dan siswa enrolled
     */
    public function liveStatus()
    {
        $student = $this->getStudent();
        if (!$student) {
            return response()->json(['live' => []]);
        }

        $enrolledCourseIds = LmsEnrollment::whereHas('lmsClass', function ($q) use ($student) {
                $q->whereHas('course', fn($cq) => $cq->where('meeting_active', true));
            })
            ->where('student_id', $student->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->with('lmsClass.course')
            ->get()
            ->pluck('lmsClass.course')
            ->filter()
            ->map(fn($c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'join_url'    => route('siswa.lms.meeting.join', $c->id),
                'started_at'  => $c->meeting_started_at?->diffForHumans(),
            ])
            ->values();

        return response()->json(['live' => $enrolledCourseIds]);
    }

    /**
     * Mark a game as finished by the student and award EXP points.
     * Points are proportional for scored game types (quiz, true_false, word_guess).
     * Points are full for completion-based types (flashcard, match, spin_wheel).
     *
     * The EXP earned is PERMANENT — it is NOT removed if the game is later deleted,
     * because lms_game_attempts records belong to the student's learning history.
     */
    public function finishGame(\App\Models\LmsGame $game, \Illuminate\Http\Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if already completed — prevent duplicate EXP farming
        $attempt = \App\Models\LmsGameAttempt::where('student_id', $student->id)
            ->where('game_id', $game->id)
            ->where('status', 'completed')
            ->first();

        if ($attempt) {
            // Already completed — return previously earned score, no new points
            return response()->json([
                'success'       => true,
                'reward_points' => $attempt->score,
                'already_done'  => true,
                'message'       => 'Kamu sudah pernah menyelesaikan game ini sebelumnya.',
            ]);
        }

        // --- Calculate score ---
        $rewardPoints = $game->reward_points;
        $correct      = (int) $request->input('correct', 0);
        $total        = (int) $request->input('total', 0);
        $comboBonus   = (int) $request->input('combo_bonus', 0);
        $gameType     = $game->game_type;

        $scoredTypes = ['quiz', 'true_false', 'word_guess', 'scramble', 'sequence'];

        if (in_array($gameType, $scoredTypes) && $total > 0) {
            // Proportional: min 10% of reward for completing, max 100%
            $ratio       = max(0.1, $correct / $total);
            $earnedScore = (int) round($rewardPoints * $ratio) + $comboBonus;
        } else {
            // Completion-based: full points (flashcard, match, spin_wheel)
            $earnedScore = $rewardPoints + $comboBonus;
        }

        \App\Models\LmsGameAttempt::create([
            'student_id' => $student->id,
            'game_id'    => $game->id,
            'score'      => $earnedScore,
            'status'     => 'completed',
        ]);

        return response()->json([
            'success'       => true,
            'reward_points' => $earnedScore,
            'correct'       => $correct,
            'total'         => $total,
            'already_done'  => false,
        ]);
    }
}
