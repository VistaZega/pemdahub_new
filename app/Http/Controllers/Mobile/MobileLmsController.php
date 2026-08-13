<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizAnswer;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileLmsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first() ?? $user->teacher;

        $isTeacher = session('active_role') === 'guru' || $user->isGuru() || !empty($teacher);

        $enrolledCourses = collect();
        if ($student && !$isTeacher) {
            $enrolledCourses = LmsCourse::whereHas('enrollments', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })->with('teacher')->get();
        } else {
            $teacherId = $teacher->id ?? 0;
            $enrolledCourses = LmsCourse::where('teacher_id', $teacherId)
                ->orWhere(fn($q) => $q->whereNull('teacher_id'))
                ->with('teacher')
                ->latest()
                ->get();
        }

        if ($enrolledCourses->isEmpty()) {
            $enrolledCourses = LmsCourse::with('teacher')->latest()->take(10)->get();
        }

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

        $classrooms = collect();
        if ($teacher) {
            $classrooms = \App\Models\Classroom::where('is_active', true)
                ->where(function ($q) use ($teacher, $activeYear) {
                    $q->whereHas('schedules', function ($sq) use ($teacher, $activeYear) {
                        $sq->where('teacher_id', $teacher->id);
                        if ($activeYear) {
                            $sq->where('academic_year_id', $activeYear->id);
                        }
                    })
                    ->orWhereHas('teachingAssignments', function ($tq) use ($teacher, $activeYear) {
                        $tq->where('teacher_id', $teacher->id);
                        if ($activeYear) {
                            $tq->where('academic_year_id', $activeYear->id);
                        }
                    })
                    ->orWhere('homeroom_teacher_id', $teacher->id);
                })
                ->orderBy('class_name')
                ->get();
        }

        if ($classrooms->isEmpty()) {
            $schoolId = $teacher?->school_id ?? $user->school_id;
            $classrooms = \App\Models\Classroom::where('is_active', true)
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->orderBy('class_name')
                ->get();
        }

        $subjects = collect();
        if ($teacher) {
            $subjects = \App\Models\Subject::whereHas('schedules', fn($q) => $q->where('teacher_id', $teacher->id))
                ->orWhereHas('teachingAssignments', fn($q) => $q->where('teacher_id', $teacher->id))
                ->orderBy('name')
                ->get();
        }

        if ($subjects->isEmpty()) {
            $subjects = \App\Models\Subject::orderBy('name')->get();
        }

        return view('mobile.lms.index', compact('enrolledCourses', 'isTeacher', 'classrooms', 'subjects'));
    }

    /**
     * Buat Course / Kelas LMS Baru (Guru Mobile)
     */
    public function storeCourse(Request $request)
    {
        $user = Auth::user();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first() ?? $user->teacher;

        $request->validate([
            'course_name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $code = $request->input('code') ?: 'LMS-' . strtoupper(\Illuminate\Support\Str::random(6));

        LmsCourse::create([
            'school_id' => $teacher?->school_id ?? $user->school_id,
            'teacher_id' => $teacher?->id ?? 0,
            'subject_id' => $request->input('subject_id'),
            'classroom_id' => $request->input('classroom_id'),
            'code' => $code,
            'course_name' => $request->input('course_name'),
            'description' => $request->input('description'),
            'is_published' => true,
            'is_active' => true,
            'status' => 'active',
        ]);

        return back()->with('success', 'Kelas / Course LMS baru berhasil dibuat!');
    }

    /**
     * Update Info Course (Guru Mobile)
     */
    public function updateCourse(Request $request, $id)
    {
        $course = LmsCourse::findOrFail($id);
        $request->validate([
            'course_name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $course->update([
            'course_name' => $request->input('course_name'),
            'code' => $request->input('code'),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', 'Informasi Kelas LMS berhasil diperbarui!');
    }

    /**
     * Hapus Course (Guru Mobile)
     */
    public function destroyCourse($id)
    {
        $course = LmsCourse::findOrFail($id);
        $course->delete();
        return redirect()->route('mobile.lms.index')->with('success', 'Kelas LMS berhasil dihapus!');
    }

    public function catalog()
    {
        $courses = LmsCourse::where('is_published', true)
            ->with('teacher')
            ->latest()
            ->paginate(10);

        return view('mobile.lms.catalog', compact('courses'));
    }

    private function getStudent()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        if (!$student && $user) {
            $student = Student::when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))->first();
        }
        return $student;
    }

    public function show($id)
    {
        $course = LmsCourse::with([
            'teacher',
            'materials',
            'modules.materials',
            'assignments',
            'quizzes'
        ])->findOrFail($id);

        $student = $this->getStudent();
        $submissionMap = collect();
        $attemptMap = collect();

        if ($student) {
            $submissionMap = LmsSubmission::where('student_id', $student->id)
                ->whereIn('assignment_id', $course->assignments->pluck('id'))
                ->get()
                ->keyBy('assignment_id');

            $attemptMap = LmsQuizAttempt::where('student_id', $student->id)
                ->whereIn('quiz_id', $course->quizzes->pluck('id'))
                ->get()
                ->groupBy('quiz_id');
        }

        return view('mobile.lms.show', compact('course', 'student', 'submissionMap', 'attemptMap'));
    }

    public function material($id)
    {
        $material = LmsMaterial::with(['course', 'module'])->findOrFail($id);

        $student = $this->getStudent();
        if ($student) {
            \App\Models\LmsMaterialProgress::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'material_id' => $material->id,
                ],
                [
                    'status' => 'completed',
                    'completed_at' => now(),
                ]
            );
        }

        return view('mobile.lms.material', compact('material'));
    }

    public function submitAssignment(Request $request, $assignmentId)
    {
        $assignment = LmsAssignment::findOrFail($assignmentId);
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $request->validate([
            'submission_text' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $filePath = null;
        $fileSize = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/submissions', 'public');
            $fileSize = $request->file('file')->getSize();
        }

        $isLate = $assignment->deadline && now()->isAfter($assignment->deadline);

        LmsSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'submission_text' => $request->input('submission_text'),
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'status' => $isLate ? 'late' : 'submitted',
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Tugas berhasil dikumpulkan!');
    }

    public function startQuiz($quizId)
    {
        $quiz = LmsQuiz::with(['course', 'questions', 'cbtQuestionBank.questions'])->findOrFail($quizId);
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.lms.show', $quiz->course_id)->with('error', 'Data siswa tidak ditemukan.');
        }

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

        $questions = $quiz->questions;
        if ($questions->isEmpty() && $quiz->cbtQuestionBank) {
            $questions = $quiz->cbtQuestionBank->questions->map(function($q) {
                return (object)[
                    'id' => $q->id,
                    'question_text' => $q->question_text ?? $q->question,
                    'question_type' => $q->question_type ?? 'multiple_choice',
                    'options' => is_string($q->options) ? json_decode($q->options, true) : ($q->options ?? [
                        'A' => $q->option_a ?? 'Pilihan A',
                        'B' => $q->option_b ?? 'Pilihan B',
                        'C' => $q->option_c ?? 'Pilihan C',
                        'D' => $q->option_d ?? 'Pilihan D',
                    ]),
                    'score' => $q->score ?? 10,
                    'correct_answer' => $q->correct_answer ?? $q->answer,
                ];
            });
        }

        if ($questions->isEmpty()) {
            $questions = collect([
                (object)[
                    'id' => 101,
                    'question_text' => 'Berapakah hasil dari 15 + 25?',
                    'question_type' => 'multiple_choice',
                    'options' => ['A' => '30', 'B' => '35', 'C' => '40', 'D' => '45'],
                    'score' => 10,
                    'correct_answer' => 'C',
                ],
                (object)[
                    'id' => 102,
                    'question_text' => 'Apakah PembdaHUB mendukung sistem pembelajaran mobile & desktop?',
                    'question_type' => 'multiple_choice',
                    'options' => ['A' => 'Ya, Benar', 'B' => 'Tidak'],
                    'score' => 10,
                    'correct_answer' => 'A',
                ]
            ]);
        }

        $answerMap = $attempt->answers()->get()->keyBy('question_id');

        return view('mobile.lms.quiz', compact('quiz', 'attempt', 'questions', 'answerMap', 'student'));
    }

    public function submitQuiz(Request $request, $attemptId)
    {
        $attempt = LmsQuizAttempt::with(['quiz.questions', 'quiz.cbtQuestionBank.questions'])->findOrFail($attemptId);
        if ($attempt->finished_at) {
            return redirect()->route('mobile.lms.show', $attempt->quiz->course_id)->with('info', 'Kuis sudah selesai.');
        }

        $quiz = $attempt->quiz;
        $questions = $quiz->questions;
        if ($questions->isEmpty() && $quiz->cbtQuestionBank) {
            $questions = $quiz->cbtQuestionBank->questions->map(function($q) {
                return (object)[
                    'id' => $q->id,
                    'score' => $q->score ?? 10,
                    'correct_answer' => $q->correct_answer ?? $q->answer,
                ];
            });
        }

        if ($questions->isEmpty()) {
            $questions = collect([
                (object)['id' => 101, 'score' => 10, 'correct_answer' => 'C'],
                (object)['id' => 102, 'score' => 10, 'correct_answer' => 'A'],
            ]);
        }

        $totalScore = 0;
        $maxScore = $questions->sum('score') ?: 20;

        foreach ($questions as $question) {
            $studentAnswer = trim($request->input("answers.{$question->id}", ''));
            $correctAnswer = trim($question->correct_answer ?? '');

            $isCorrect = false;
            if (!empty($studentAnswer) && !empty($correctAnswer)) {
                $isCorrect = strtolower($studentAnswer) === strtolower($correctAnswer);
            }

            $questionScore = $isCorrect ? ($question->score ?: 10) : 0;
            $totalScore += $questionScore;

            LmsQuizAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                [
                    'answer' => $studentAnswer,
                    'is_correct' => $isCorrect,
                    'score' => $questionScore,
                ]
            );
        }

        $finalPercentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 1) : 0;
        $passingScore = $quiz->passing_score ?? 70;

        $attempt->update([
            'finished_at' => now(),
            'score' => $finalPercentage,
            'is_passed' => $finalPercentage >= $passingScore,
        ]);

        return redirect()->route('mobile.lms.quiz.result', $attempt->id)->with('success', 'Kuis berhasil diselesaikan!');
    }

    public function quizResult($attemptId)
    {
        $attempt = LmsQuizAttempt::with(['quiz.questions', 'answers.question'])->findOrFail($attemptId);
        return view('mobile.lms.quiz_result', compact('attempt'));
    }

    /**
     * Tambah Modul Baru (Guru Mobile)
     */
    public function storeModule(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (class_exists('\App\Models\LmsModule')) {
            \App\Models\LmsModule::create([
                'course_id' => $courseId,
                'title' => $request->input('title'),
                'name' => $request->input('title'),
                'description' => $request->input('description'),
                'order_number' => 1,
            ]);
        }

        return back()->with('success', 'Modul berhasil ditambahkan!');
    }

    /**
     * Unggah / Tambah Materi Baru (Guru Mobile)
     */
    public function storeMaterial(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'material_type' => 'required|string',
            'module_id' => 'nullable|exists:lms_modules,id',
            'content' => 'nullable|string',
            'file_url' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
        ]);

        $filePath = null;
        $fileSize = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/materials', 'public');
            $fileSize = $request->file('file')->getSize();
        }

        LmsMaterial::create([
            'course_id' => $courseId,
            'module_id' => $request->input('module_id'),
            'title' => $request->input('title'),
            'material_type' => $request->input('material_type'),
            'content' => $request->input('content'),
            'file_url' => $request->input('file_url'),
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'is_published' => true,
        ]);

        return back()->with('success', 'Materi berhasil ditambahkan!');
    }

    /**
     * Hapus Materi (Guru Mobile)
     */
    public function destroyMaterial($id)
    {
        $material = LmsMaterial::findOrFail($id);
        $material->delete();
        return back()->with('success', 'Materi berhasil dihapus!');
    }

    /**
     * Tambah Tugas Baru (Guru Mobile)
     */
    public function storeAssignment(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        LmsAssignment::create([
            'course_id' => $courseId,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'due_date' => $request->input('due_date'),
        ]);

        return back()->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Hapus Tugas (Guru Mobile)
     */
    public function destroyAssignment($id)
    {
        $assignment = LmsAssignment::findOrFail($id);
        $assignment->delete();
        return back()->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * Tambah Kuis Baru (Guru Mobile)
     */
    public function storeQuiz(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
        ]);

        LmsQuiz::create([
            'course_id' => $courseId,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'time_limit' => $request->input('time_limit'),
            'is_published' => true,
        ]);

        return back()->with('success', 'Kuis berhasil ditambahkan!');
    }

    /**
     * Hapus Kuis (Guru Mobile)
     */
    public function destroyQuiz($id)
    {
        $quiz = LmsQuiz::findOrFail($id);
        $quiz->delete();
        return back()->with('success', 'Kuis berhasil dihapus!');
    }
}
