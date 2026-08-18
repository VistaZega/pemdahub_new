<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsQuizRequest;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\LmsCourse;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizAnswer;
use App\Models\Teacher;
use App\Imports\QuizQuestionsImport;
use App\Exports\QuizResultsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            'question_sample_count' => $request->question_sample_count ?: null,
            'points_per_question' => $request->points_per_question ?: null,
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'total_score' => $request->total_score ?? 100,
            'passing_score' => $request->passing_score,
            'max_attempts' => $request->max_attempts ?? 1,
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'show_result' => $request->boolean('show_result', true),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_published' => false,
        ]);

        // Reputation Hook for Teacher (+30 Points per Kuis)
        \App\Models\ReputationLog::log(
            \Auth::id(),
            30,
            'lms_content',
            "Membuat kuis LMS baru: " . ($quiz->title ?? 'Kuis'),
            $quiz
        );

        // Auto-sync soal dari bank soal jika dipilih
        $syncCount = 0;
        if ($request->question_package_id) {
            $syncCount = $this->syncQuestionsFromBank($quiz, $request->question_package_id);
        }

        $message = 'Quiz berhasil dibuat.';
        if ($syncCount > 0) {
            $message .= " {$syncCount} soal berhasil diimpor dari bank soal.";
        } else {
            $message .= ' Silakan tambahkan soal.';
        }

        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('success', $message);
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
        $cbtQuestionBanks = CbtQuestionBank::where('school_id', $teacher->school_id)
            ->where('is_active', true)
            ->get();

        return view('guru.lms.quiz-edit', compact('teacher', 'course', 'quiz', 'modules', 'cbtQuestionBanks'));
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
            'question_package_id' => $request->has('question_package_id') ? ($request->question_package_id ?: null) : $quiz->question_package_id,
            'question_sample_count' => $request->has('question_sample_count') ? ($request->question_sample_count ?: null) : $quiz->question_sample_count,
            'points_per_question' => $request->has('points_per_question') ? ($request->points_per_question ?: null) : $quiz->points_per_question,
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
            'total_score' => $request->has('total_score') && $request->total_score ? $request->total_score : ($quiz->points_per_question || $quiz->question_sample_count ? $quiz->total_score : ($quiz->questions()->sum('score') ?: 100)),
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
     * Sync/import questions from CBT question bank into quiz
     */
    public function syncFromBank(LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        if (!$quiz->question_package_id) {
            return redirect()->route('guru.lms.quizzes.show', $quiz->id)
                ->with('error', 'Quiz ini tidak terhubung ke bank soal manapun.');
        }

        $syncCount = $this->syncQuestionsFromBank($quiz, $quiz->question_package_id);

        if ($syncCount > 0) {
            return redirect()->route('guru.lms.quizzes.show', $quiz->id)
                ->with('success', "{$syncCount} soal berhasil disinkronisasi dari bank soal.");
        }

        return redirect()->route('guru.lms.quizzes.show', $quiz->id)
            ->with('info', 'Tidak ada soal baru untuk disinkronisasi. Bank soal mungkin kosong.');
    }

    /**
     * Helper: Sync questions from a CBT question bank into an LMS quiz
     * Converts CbtQuestion + CbtQuestionOptions into LmsQuizQuestion format
     */
    private function syncQuestionsFromBank(LmsQuiz $quiz, int $bankId): int
    {
        $bank = CbtQuestionBank::with(['questions' => function ($q) {
            $q->where('is_active', true)->with('options');
        }])->find($bankId);

        if (!$bank || $bank->questions->isEmpty()) {
            return 0;
        }

        $maxOrder = $quiz->questions()->max('order_number') ?? 0;
        $imported = 0;

        foreach ($bank->questions as $cbtQuestion) {
            // Tentukan question_type mapping dari CBT ke LMS
            $questionType = $this->mapCbtQuestionType($cbtQuestion->question_type);

            // Konversi options dari CbtQuestionOption ke format JSON LmsQuizQuestion
            $options = null;
            $correctAnswer = $cbtQuestion->answer_key;

            if (in_array($questionType, ['multiple_choice', 'true_false']) && $cbtQuestion->options->isNotEmpty()) {
                $options = $cbtQuestion->options->sortBy('sort_order')->values()->map(function ($opt) {
                    return [
                        'key' => $opt->option_label,
                        'text' => $opt->option_text,
                    ];
                })->toArray();

                // Cari jawaban benar dari options
                $correctOption = $cbtQuestion->options->firstWhere('is_correct', true);
                if ($correctOption) {
                    $correctAnswer = $correctOption->option_label;
                }
            }

            $maxOrder++;
            $quiz->questions()->create([
                'question' => $cbtQuestion->question_text,
                'question_type' => $questionType,
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'order_number' => $maxOrder,
                'score' => $cbtQuestion->points ?: 1,
                'image_path' => $cbtQuestion->question_image,
                'video_url' => $cbtQuestion->question_video,
            ]);

            $imported++;
        }

        // Update total score quiz
        $quiz->update(['total_score' => $quiz->questions()->sum('score')]);

        return $imported;
    }

    /**
     * Map CBT question types to LMS quiz question types
     */
    private function mapCbtQuestionType(string $cbtType): string
    {
        return match ($cbtType) {
            'multiple_choice', 'pilihan_ganda' => 'multiple_choice',
            'true_false', 'benar_salah' => 'true_false',
            'short_answer', 'isian_singkat' => 'short_answer',
            'essay', 'uraian' => 'essay',
            default => 'multiple_choice',
        };
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
     * View quiz results/attempts recap per student
     */
    public function results(Request $request, LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $selectedClassroomId = $request->query('classroom_id') ? (int) $request->query('classroom_id') : null;
        $recap = $this->getQuizRecapData($quiz, $selectedClassroomId);

        $quiz->setRelation('attempts', $recap['attempts']);

        return view('guru.lms.quiz-results', array_merge([
            'teacher' => $teacher,
            'course' => $course,
            'quiz' => $quiz,
        ], $recap));
    }

    /**
     * Export quiz recap results to Excel
     */
    public function exportResults(Request $request, LmsQuiz $quiz)
    {
        $teacher = $this->getTeacher();
        $course = $quiz->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $selectedClassroomId = $request->query('classroom_id') ? (int) $request->query('classroom_id') : null;
        $recap = $this->getQuizRecapData($quiz, $selectedClassroomId);

        $className = $recap['selectedClassroom'] ? Str::slug($recap['selectedClassroom']->class_name) : 'semua-rombel';
        $quizSlug = Str::slug($quiz->title);
        $filename = "rekap_nilai_kuis_{$quizSlug}_{$className}_" . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new QuizResultsExport(
                $quiz,
                $recap['recapData'],
                $recap['displayAttemptsCount'],
                $recap['selectedClassroom']
            ),
            $filename
        );
    }

    /**
     * Build structured recap data per enrolled student for a quiz
     */
    private function getQuizRecapData(LmsQuiz $quiz, ?int $selectedClassroomId = null): array
    {
        $course = $quiz->course;

        // Ambil semua rombel yang terhubung ke course ini
        $classrooms = \App\Models\LmsClass::where('course_id', $course->id)
            ->with('classroom')
            ->get()
            ->pluck('classroom')
            ->filter()
            ->values();

        $selectedClassroom = $selectedClassroomId
            ? $classrooms->firstWhere('id', $selectedClassroomId)
            : null;

        // Ambil data enrollment siswa
        $enrollmentQuery = \App\Models\LmsEnrollment::whereHas('lmsClass', function ($q) use ($course, $selectedClassroomId) {
            $q->where('course_id', $course->id);
            if ($selectedClassroomId) {
                $q->where('classroom_id', $selectedClassroomId);
            }
        })->with(['student.user', 'lmsClass.classroom']);

        $enrollments = $enrollmentQuery->get();

        // Ambil semua pengerjaan quiz
        $attemptsQuery = $quiz->attempts()->with('student.user')->orderBy('started_at', 'asc')->orderBy('id', 'asc');

        if ($selectedClassroomId && $selectedClassroom) {
            $enrolledStudentIds = $enrollments->pluck('student_id')->filter()->unique();
            $attemptsQuery->whereIn('student_id', $enrolledStudentIds);
        }

        $attempts = $attemptsQuery->get();
        $attemptsByStudent = $attempts->groupBy('student_id');

        // Petakan siswa terdaftar
        $studentsMap = collect();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student) continue;

            $studentsMap->put($student->id, [
                'student' => $student,
                'classroom_name' => $enrollment->lmsClass?->classroom?->class_name ?? ($student->classroom?->class_name ?? '-'),
            ]);
        }

        // Jika ada siswa yang mengerjakan tapi belum ada di enrollment query (fallback)
        foreach ($attemptsByStudent as $studentId => $stAttempts) {
            if (!$studentsMap->has($studentId) && $stAttempts->isNotEmpty()) {
                $firstAtt = $stAttempts->first();
                if ($firstAtt && $firstAtt->student) {
                    $studentsMap->put($studentId, [
                        'student' => $firstAtt->student,
                        'classroom_name' => $firstAtt->student->classroom?->class_name ?? '-',
                    ]);
                }
            }
        }

        $passingScore = (float) ($quiz->passing_score ?? 75);
        $recapList = collect();

        foreach ($studentsMap as $studentId => $item) {
            $student = $item['student'];
            $classroomName = $item['classroom_name'];
            $stAttempts = $attemptsByStudent->get($studentId, collect());

            $attemptsByIndex = [];
            $attemptsObjByIndex = [];
            $idx = 1;
            foreach ($stAttempts as $att) {
                $attemptsByIndex[$idx] = $att->score !== null ? (float) $att->score : null;
                $attemptsObjByIndex[$idx] = $att;
                $idx++;
            }

            $finishedAttempts = $stAttempts->whereNotNull('finished_at');
            $inProgressAttempt = $stAttempts->firstWhere('finished_at', null);
            $bestScore = $finishedAttempts->isNotEmpty() ? (float) $finishedAttempts->max('score') : null;
            $bestAttempt = $finishedAttempts->isNotEmpty() ? $finishedAttempts->sortByDesc('score')->first() : null;
            $latestAttempt = $stAttempts->sortByDesc('started_at')->first();
            $attemptCount = $finishedAttempts->count();

            if ($attemptCount === 0) {
                if ($inProgressAttempt) {
                    $statusLabel = 'Sedang Mengerjakan';
                    $statusType = 'in_progress';
                } else {
                    $statusLabel = 'Belum Mengerjakan';
                    $statusType = 'unattempted';
                }
            } else {
                if ($bestScore !== null && $bestScore >= $passingScore) {
                    $statusLabel = 'Lulus';
                    $statusType = 'passed';
                } else {
                    $statusLabel = 'Remedial';
                    $statusType = 'failed';
                }
            }

            $studentName = $student->full_name ?? $student->user->name ?? 'N/A';

            $recapList->push([
                'student' => $student,
                'student_id' => $student->id,
                'student_name' => $studentName,
                'nisn' => $student->nisn ?? $student->nis ?? '-',
                'classroom_name' => $classroomName,
                'attempts' => $stAttempts,
                'attempts_by_index' => $attemptsByIndex,
                'attempts_obj_by_index' => $attemptsObjByIndex,
                'attempt_count' => $attemptCount,
                'has_in_progress' => (bool) $inProgressAttempt,
                'best_score' => $bestScore,
                'best_attempt' => $bestAttempt,
                'latest_attempt' => $latestAttempt,
                'status_label' => $statusLabel,
                'status_type' => $statusType,
            ]);
        }

        $recapData = $recapList->sortBy('student_name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $quizMaxAttempts = (int) ($quiz->max_attempts ?? 1);
        $maxStudentAttempts = $recapData->map(fn($r) => count($r['attempts_by_index']))->max() ?? 0;
        $displayAttemptsCount = max(1, min(10, max($quizMaxAttempts, $maxStudentAttempts)));

        // Summary Statistics
        $totalStudents = $recapData->count();
        $completedStudentsCount = $recapData->where('attempt_count', '>', 0)->count();
        $passedStudentsCount = $recapData->where('status_type', 'passed')->count();
        $inProgressStudentsCount = $recapData->where('status_type', 'in_progress')->count();
        $unattemptedStudentsCount = $recapData->where('status_type', 'unattempted')->count();

        $completedWithScore = $recapData->filter(fn($r) => $r['best_score'] !== null);
        $avgScore = $completedWithScore->isNotEmpty() ? $completedWithScore->avg('best_score') : 0;

        $ranges = [
            '81-100' => 0,
            '61-80'  => 0,
            '41-60'  => 0,
            '21-40'  => 0,
            '0-20'   => 0,
        ];
        foreach ($completedWithScore as $r) {
            $score = $r['best_score'];
            if ($score > 80) $ranges['81-100']++;
            elseif ($score > 60) $ranges['61-80']++;
            elseif ($score > 40) $ranges['41-60']++;
            elseif ($score > 20) $ranges['21-40']++;
            else $ranges['0-20']++;
        }

        return [
            'classrooms' => $classrooms,
            'selectedClassroomId' => $selectedClassroomId,
            'selectedClassroom' => $selectedClassroom,
            'recapData' => $recapData,
            'displayAttemptsCount' => $displayAttemptsCount,
            'totalStudents' => $totalStudents,
            'completedStudentsCount' => $completedStudentsCount,
            'passedStudentsCount' => $passedStudentsCount,
            'inProgressStudentsCount' => $inProgressStudentsCount,
            'unattemptedStudentsCount' => $unattemptedStudentsCount,
            'avgScore' => $avgScore,
            'ranges' => $ranges,
            'totalAttempts' => $attempts->whereNotNull('finished_at')->count(),
            'attempts' => $attempts,
        ];
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
