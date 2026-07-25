<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsQuizRequest;
use App\Models\LmsCourse;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizAnswer;
use App\Models\Teacher;
use App\Imports\QuizQuestionsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsQuizController extends Controller
{
    private function getTeacher(): ?Teacher
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    private function authorizeAccess(LmsCourse $course, Teacher $teacher): bool
    {
        return $course->teacher_id === $teacher->id;
    }

    /**
     * Show create quiz form
     */
    public function create(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $modules = $course->modules()->orderBy('sequence')->get();
        $cbtQuestionBanks = \App\Models\CbtQuestionBank::where('school_id', $teacher->school_id)
            ->where('is_active', true)
            ->get();

        return view('guru.lms.quiz-create', compact('teacher', 'course', 'modules', 'cbtQuestionBanks'));
    }

    /**
     * Store new quiz
     */
    public function store(StoreLmsQuizRequest $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $quiz = $course->quizzes()->create([
            'module_id' => $request->module_id,
            'question_package_id' => $request->question_package_id,
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'total_score' => 100,
            'passing_score' => $request->passing_score,
            'max_attempts' => $request->max_attempts ?? 1,
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'show_result' => $request->boolean('show_result', true),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_published' => false,
        ]);

        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('success', 'Quiz berhasil dibuat. Silakan tambahkan soal.');
    }

    /**
     * Show quiz detail with questions
     */
    public function show(LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $quiz->load(['questions' => fn($q) => $q->orderBy('order_number')]);
        $quiz->loadCount(['attempts']);

        // Recalculate total score from questions
        $totalScore = $quiz->questions->sum('score');

        return view('guru.lms.quiz-show', compact('teacher', 'course', 'quiz', 'totalScore'));
    }

    /**
     * Show edit form
     */
    public function edit(LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');
        $modules = $course->modules()->where('is_active', true)->orderBy('sequence')->get();

        return view('guru.lms.quiz-edit', compact('teacher', 'course', 'quiz', 'modules'));
    }

    /**
     * Update quiz
     */
    public function update(StoreLmsQuizRequest $request, LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $wasPublished = $quiz->is_published;

        $quiz->update([
            'module_id' => $request->has('module_id') ? $request->module_id : $quiz->module_id,
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'passing_score' => $request->passing_score,
            // Preserve existing values if field not present in request (e.g. inline quick-edit form)
            'max_attempts' => $request->has('max_attempts') ? ($request->max_attempts ?? $quiz->max_attempts) : $quiz->max_attempts,
            'shuffle_questions' => $request->has('shuffle_questions') ? $request->boolean('shuffle_questions') : $quiz->shuffle_questions,
            'show_result' => $request->has('show_result') ? $request->boolean('show_result') : $quiz->show_result,
            'is_published' => $request->has('is_published') ? $request->boolean('is_published') : $quiz->is_published,
            // Empty string from datetime-local input must be converted to null to clear the constraint
            'start_time' => $request->has('start_time') ? ($request->start_time ?: null) : $quiz->start_time,
            'end_time' => $request->has('end_time') ? ($request->end_time ?: null) : $quiz->end_time,
            'total_score' => $quiz->questions()->sum('score'),
        ]);

        if (!$wasPublished && $quiz->fresh()->is_published) {
            // Send WhatsApp notification to enrolled students
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->sendLmsNotification($course, 'lms.quiz.published', [
                    'title' => $quiz->title,
                ]);
            } catch (\Exception $e) {
                \Log::error('LMS quiz notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('success', 'Quiz berhasil diperbarui.');
    }

    /**
     * Toggle publish status of a quiz (dedicated endpoint, no full-form validation needed)
     */
    public function togglePublish(LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $wasPublished = $quiz->is_published;
        $quiz->update(['is_published' => !$wasPublished]);

        if (!$wasPublished && $quiz->is_published) {
            // Send notification when quiz is newly published
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->sendLmsNotification($course, 'lms.quiz.published', [
                    'title' => $quiz->title,
                ]);
            } catch (\Exception $e) {
                \Log::error('LMS quiz notification failed: ' . $e->getMessage());
            }
        }

        $status = $quiz->is_published ? 'dipublikasikan' : 'disimpan sebagai draft';
        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('success', "Quiz berhasil {$status}. " . ($quiz->is_published ? 'Siswa sekarang dapat melihat quiz ini.' : 'Quiz tidak terlihat oleh siswa.'));
    }

    /**
     * Delete quiz
     */
    public function destroy(LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $courseId = $course->id;
        $quiz->delete();

        return redirect()->route('guru.lms.show', $courseId)
            ->with('success', 'Quiz berhasil dihapus.');
    }

    /**
     * Store new question
     */
    public function storeQuestion(Request $request, LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'score' => 'required|numeric|min:0.5',
            'correct_answer' => $request->question_type === 'essay' ? 'nullable|string' : 'required|string',
            'options' => 'nullable|array',
            'options.*.key' => 'required_with:options|string',
            'options.*.text' => 'required_with:options|string',
            'image' => 'nullable|image|max:5120',
            'video_url' => 'nullable|string|max:255',
        ]);

        $maxOrder = $quiz->questions()->max('order_number') ?? 0;

        $data = [
            'question' => $request->question,
            'question_type' => $request->question_type,
            'options' => $request->options,
            'correct_answer' => $request->correct_answer,
            'order_number' => $maxOrder + 1,
            'score' => $request->score,
            'video_url' => $request->video_url,
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('lms/quizzes/media', 'public');
        }

        $quiz->questions()->create($data);

        // Update quiz total score
        $quiz->update(['total_score' => $quiz->questions()->sum('score')]);

        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Update question
     */
    public function updateQuestion(Request $request, LmsQuizQuestion $question)
    {
        $quiz = $question->quiz;
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'question' => 'required|string',
            'score' => 'required|numeric|min:0.5',
            'correct_answer' => $question->question_type === 'essay' ? 'nullable|string' : 'required|string',
            'options' => 'nullable|array',
            'image' => 'nullable|image|max:5120',
            'video_url' => 'nullable|string|max:255',
            'clear_image' => 'nullable|boolean',
        ]);

        $data = [
            'question' => $request->question,
            'score' => $request->score,
            'correct_answer' => $request->correct_answer,
            'options' => $request->options,
            'video_url' => $request->video_url,
        ];

        if ($request->clear_image && $question->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($question->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
            }
            $data['image_path'] = $request->file('image')->store('lms/quizzes/media', 'public');
        }

        $question->update($data);

        $quiz->update(['total_score' => $quiz->questions()->sum('score')]);

        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Download template Excel for importing quiz questions
     */
    public function downloadTemplate(LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        return Excel::download(new \App\Exports\QuizQuestionsTemplateExport, 'template_soal_kuis_' . $quiz->id . '.xlsx');
    }

    /**
     * Import quiz questions from Excel/CSV
     */
    public function importQuestions(Request $request, LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        $file = $request->file('file');

        try {
            $extension = $file->getClientOriginalExtension();
            $rows = [];

            if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
                $import = new QuizQuestionsImport();
                Excel::import($import, $file);
                $rows = $import->getRows();
            } else {
                $path = $file->getRealPath();
                $handle = fopen($path, 'r');
                $header = fgetcsv($handle);
                
                if ($header) {
                    $header = array_map(function($h) {
                        return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                    }, $header);
                }

                while (($row = fgetcsv($handle)) !== false) {
                    if (count($header) === count($row)) {
                        $rows[] = array_combine($header, $row);
                    }
                }
                fclose($handle);
            }

            if (empty($rows)) {
                return back()->with('error', 'File tidak berisi data atau format kolom tidak sesuai.');
            }

            $importedCount = 0;
            $maxOrder = $quiz->questions()->max('order_number') ?? 0;

            foreach ($rows as $row) {
                $normalizedRow = [];
                foreach ($row as $k => $v) {
                    $normalizedRow[strtolower(trim($k))] = $v;
                }

                $questionText = $normalizedRow['question'] ?? null;
                $questionType = $normalizedRow['question_type'] ?? 'multiple_choice';
                $correctAnswer = $normalizedRow['correct_answer'] ?? '';
                $score = floatval($normalizedRow['score'] ?? 10);

                if (!$questionText) {
                    continue;
                }

                // Process options if MC
                $options = null;
                if ($questionType === 'multiple_choice') {
                    $options = [];
                    if (!empty($normalizedRow['option_a'])) $options[] = ['key' => 'A', 'text' => trim($normalizedRow['option_a'])];
                    if (!empty($normalizedRow['option_b'])) $options[] = ['key' => 'B', 'text' => trim($normalizedRow['option_b'])];
                    if (!empty($normalizedRow['option_c'])) $options[] = ['key' => 'C', 'text' => trim($normalizedRow['option_c'])];
                    if (!empty($normalizedRow['option_d'])) $options[] = ['key' => 'D', 'text' => trim($normalizedRow['option_d'])];
                    if (!empty($normalizedRow['option_e'])) $options[] = ['key' => 'E', 'text' => trim($normalizedRow['option_e'])];
                }

                $maxOrder++;
                $quiz->questions()->create([
                    'question' => $questionText,
                    'question_type' => $questionType,
                    'options' => $options,
                    'correct_answer' => $correctAnswer,
                    'order_number' => $maxOrder,
                    'score' => $score,
                ]);

                $importedCount++;
            }

            $quiz->update(['total_score' => $quiz->questions()->sum('score')]);

            return redirect()->route('guru.lms.quizzes.show', $quiz->id)
                ->with('success', "Berhasil mengimpor {$importedCount} soal.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor soal: ' . $e->getMessage());
        }
    }

    /**
     * Delete question
     */
    public function destroyQuestion(LmsQuizQuestion $question)
    {
        $quiz = $question->quiz;
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $question->delete();
        $quiz->update(['total_score' => $quiz->questions()->sum('score')]);

        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * View quiz results/attempts
     */
    public function results(LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $quiz->load(['attempts' => fn($q) => $q->with('student.user')->orderByDesc('finished_at')]);

        $totalAttempts = $quiz->attempts->count();
        $passedCount = $quiz->attempts->where('is_passed', true)->count();
        $avgScore = $quiz->attempts->whereNotNull('score')->avg('score');

        return view('guru.lms.quiz-results', compact('teacher', 'course', 'quiz', 'totalAttempts', 'passedCount', 'avgScore'));
    }

    /**
     * Show student quiz attempt details
     */
    public function showAttempt(LmsQuizAttempt $attempt)
    {
        $teacher = $this->getTeacher();
        $quiz = $attempt->quiz;
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $attempt->load(['student.user', 'answers.question']);
        $quiz->load('questions');

        // Map answers by question_id
        $answerMap = $attempt->answers->keyBy('question_id');

        return view('guru.lms.quiz-attempt-show', compact('teacher', 'course', 'quiz', 'attempt', 'answerMap'));
    }

    /**
     * Grade student quiz attempt
     */
    public function gradeAttempt(Request $request, LmsQuizAttempt $attempt)
    {
        $teacher = $this->getTeacher();
        $quiz = $attempt->quiz;
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'grades' => 'required|array',
            'grades.*.score' => 'required|numeric|min:0',
            'grades.*.is_correct' => 'required|boolean',
        ]);

        foreach ($request->grades as $questionId => $data) {
            $question = LmsQuizQuestion::find($questionId);
            if ($question && $question->quiz_id === $quiz->id) {
                // Ensure score doesn't exceed question's max score
                $score = min($data['score'], $question->score);

                LmsQuizAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                    [
                        'score' => $score,
                        'is_correct' => $data['is_correct'],
                    ]
                );
            }
        }

        // Recalculate total score
        $totalScore = $attempt->answers()->sum('score');
        $maxScore = $quiz->questions()->sum('score');
        $scorePercentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        $attempt->update([
            'score' => $scorePercentage,
            'is_passed' => $scorePercentage >= $quiz->passing_score,
        ]);

        // Auto-sync quiz score to grades table
        try {
            $gradeService = app(\App\Services\GradeService::class);
            $gradeService->syncQuizAttemptToGrade($attempt);
        } catch (\Exception $e) {
            \Log::warning('LMS quiz manual grade sync failed: ' . $e->getMessage());
        }

        return redirect()->route('guru.lms.quizzes.results', $quiz->id)
            ->with('success', 'Pengerjaan berhasil dinilai.');
    }
}
