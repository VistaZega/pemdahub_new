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

        $enrolledCourses = collect();
        if ($student) {
            $enrolledCourses = LmsCourse::whereHas('enrollments', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })->with('teacher')->get();
        } else {
            $enrolledCourses = LmsCourse::where('teacher_id', $user->teacher->id ?? 0)->get();
        }

        return view('mobile.lms.index', compact('enrolledCourses'));
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
}
