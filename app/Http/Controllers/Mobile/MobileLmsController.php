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
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileLmsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first() ?? $user->teacher;

        $isTeacher = session('active_role') === 'guru' || $user->isGuru() || !empty($teacher);
        $selectedClassroomId = $request->query('classroom_id');

        $enrolledCourses = collect();
        if ($student && !$isTeacher) {
            // Auto sync pendaftaran LMS siswa untuk rombel aktifnya
            app(\App\Services\LmsEnrollmentService::class)->syncStudentEnrollments($student);

            // Dapatkan ID rombel siswa
            $studentClassroomIds = \App\Models\StudentClass::where('student_id', $student->id)
                ->where('status', 'aktif')
                ->pluck('classroom_id')
                ->toArray();
            if ($student->classroom_id) {
                $studentClassroomIds[] = $student->classroom_id;
            }
            $studentClassroomIds = array_unique(array_filter($studentClassroomIds));

            $enrolledCourses = LmsCourse::where(function ($q) use ($student, $studentClassroomIds) {
                $q->whereHas('enrollments', function ($eq) use ($student) {
                    $eq->where('student_id', $student->id);
                })
                ->orWhereIn('classroom_id', $studentClassroomIds)
                ->orWhereHas('lmsClasses', function ($lq) use ($studentClassroomIds) {
                    $lq->whereIn('classroom_id', $studentClassroomIds);
                });
            })
            ->when($student->school_id, function ($q) use ($student) {
                $q->where(function ($sq) use ($student) {
                    $sq->where('school_id', $student->school_id)
                       ->orWhereNull('school_id');
                });
            })
            ->where('is_published', true)
            ->with(['teacher.user', 'subject', 'classroom', 'modules.materials', 'materials', 'lmsClasses.classroom'])
            ->withCount(['materials', 'assignments', 'quizzes', 'enrollments'])
            ->latest()
            ->get();
        } else {
            $teacherId = $teacher->id ?? 0;
            $enrolledCourses = LmsCourse::where('teacher_id', $teacherId)
                ->when($selectedClassroomId, function($q) use ($selectedClassroomId) {
                    $q->where(function($sq) use ($selectedClassroomId) {
                        $sq->where('classroom_id', $selectedClassroomId)
                           ->orWhereHas('lmsClasses', fn($lq) => $lq->where('classroom_id', $selectedClassroomId));
                    });
                })
                ->orWhere(fn($q) => $q->whereNull('teacher_id'))
                ->with(['teacher.user', 'subject', 'classroom', 'modules.materials', 'materials', 'lmsClasses.classroom'])
                ->withCount(['materials', 'assignments', 'quizzes', 'enrollments'])
                ->latest()
                ->get();
        }

        // Hitung persentase progress penyelesaian materi untuk siswa
        $courseProgress = [];
        if ($student) {
            $completedMaterials = \App\Models\LmsMaterialProgress::where('student_id', $student->id)
                ->pluck('material_id')
                ->toArray();

            foreach ($enrolledCourses as $c) {
                $totalMat = $c->materials_count;
                if ($totalMat > 0) {
                    $matIds = \App\Models\LmsMaterial::where('course_id', $c->id)->pluck('id')->toArray();
                    $completedCount = count(array_intersect($matIds, $completedMaterials));
                    $pct = round(($completedCount / $totalMat) * 100);
                    $courseProgress[$c->id] = min(100, $pct);
                } else {
                    $courseProgress[$c->id] = 0;
                }
            }
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
                    ->orWhereHas('lmsClasses.course', function ($lq) use ($teacher) {
                        $lq->where('teacher_id', $teacher->id);
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
            $subjFromSchedules = \App\Models\Schedule::where('teacher_id', $teacher->id)->pluck('subject_id');
            $subjFromAssignments = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)->pluck('subject_id');
            $allSubjectIds = $subjFromSchedules->concat($subjFromAssignments)->filter()->unique();

            if ($allSubjectIds->isNotEmpty()) {
                $subjects = \App\Models\Subject::whereIn('id', $allSubjectIds)->orderBy('name')->get();
            }
        }

        if ($subjects->isEmpty()) {
            $schoolId = $teacher?->school_id ?? $user->school_id;
            $subjects = \App\Models\Subject::where('is_active', true)
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->orderBy('name')
                ->get();
        }

        return view('mobile.lms.index', compact('enrolledCourses', 'isTeacher', 'classrooms', 'subjects', 'courseProgress', 'selectedClassroomId'));
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

        $activeSemester = \App\Models\Semester::where('is_active', true)->first()
            ?? \App\Models\Semester::latest()->first();

        $course = LmsCourse::create([
            'school_id' => $teacher?->school_id ?? $user->school_id,
            'teacher_id' => $teacher?->id ?? 0,
            'subject_id' => $request->input('subject_id'),
            'classroom_id' => $request->input('classroom_id'),
            'semester_id' => $activeSemester?->id ?? 1,
            'code' => $code,
            'course_name' => $request->input('course_name'),
            'description' => $request->input('description'),
            'is_published' => true,
            'is_active' => true,
            'status' => 'active',
            'is_sequential' => $request->has('is_sequential'),
        ]);

        // Auto-sync LmsClass & pendaftaran seluruh siswa di rombel terkait
        if ($course->classroom_id) {
            app(\App\Services\LmsEnrollmentService::class)->syncCourseEnrollments($course);
        }

        return back()->with('success', 'Kelas / Course LMS baru berhasil dibuat dan seluruh siswa di kelas telah terdaftar!');
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
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $course->update([
            'course_name' => $request->input('course_name'),
            'code' => $request->input('code'),
            'description' => $request->input('description'),
            'classroom_id' => $request->input('classroom_id', $course->classroom_id),
            'subject_id' => $request->input('subject_id', $course->subject_id),
            'is_sequential' => $request->has('is_sequential'),
        ]);

        if ($course->classroom_id) {
            app(\App\Services\LmsEnrollmentService::class)->syncCourseEnrollments($course);
        }

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
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first() ?? $user->teacher;
        $schoolId = $student?->school_id ?? $teacher?->school_id ?? $user->school_id;

        $courses = LmsCourse::where('is_published', true)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->with('teacher')
            ->latest()
            ->paginate(10);

        return view('mobile.lms.catalog', compact('courses'));
    }

    private function getStudent()
    {
        $user = Auth::user();
        return Student::where('user_id', $user->id)->first();
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
        $completedMaterialIds = [];

        if ($student) {
            // Cek otorisasi sekolah: Siswa tidak boleh melihat kursus sekolah lain
            if ($course->school_id && $student->school_id && $course->school_id != $student->school_id) {
                return redirect()->route('mobile.lms.index')
                    ->with('error', 'Anda tidak memiliki akses ke kelas LMS sekolah lain.');
            }

            // Auto-sync enrollment jika siswa berada di rombel ini
            app(\App\Services\LmsEnrollmentService::class)->syncStudentEnrollments($student);

            $submissionMap = LmsSubmission::where('student_id', $student->id)
                ->whereIn('assignment_id', $course->assignments->pluck('id'))
                ->get()
                ->keyBy('assignment_id');

            $attemptMap = LmsQuizAttempt::where('student_id', $student->id)
                ->whereIn('quiz_id', $course->quizzes->pluck('id'))
                ->get()
                ->groupBy('quiz_id');

            $completedMaterialIds = \App\Models\LmsMaterialProgress::where('student_id', $student->id)
                ->pluck('material_id')
                ->toArray();
        }

        $user = Auth::user();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first() ?? $user->teacher;
        $isTeacher = $teacher && ($course->teacher_id == $teacher->id);

        $questionBanks = collect();
        if (class_exists('\App\Models\CbtQuestionBank')) {
            $schoolId = $teacher?->school_id ?? $user->school_id;
            $teacherId = $teacher?->id ?? 0;

            $questionBanks = \App\Models\CbtQuestionBank::where(function($q) use ($teacherId, $schoolId) {
                if ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                }
                if ($schoolId) {
                    $q->orWhere('school_id', $schoolId);
                }
                $q->orWhere('is_shared', true);
            })->latest()->get();

            if ($questionBanks->isEmpty()) {
                $questionBanks = \App\Models\CbtQuestionBank::latest()->take(30)->get();
            }
        }

        return view('mobile.lms.show', compact('course', 'student', 'submissionMap', 'attemptMap', 'questionBanks', 'completedMaterialIds', 'isTeacher'));
    }

    public function material($id)
    {
        $material = LmsMaterial::with(['course', 'module'])->findOrFail($id);

        $student = $this->getStudent();

        // Pengecekan Kunci Pembelajaran Bertahap (Sequential Learning Lock)
        if ($student && ($material->course?->is_sequential || $material->module?->is_sequential)) {
            $allCourseMaterials = LmsMaterial::where('course_id', $material->course_id)
                ->orderBy('module_id', 'asc')
                ->orderBy('order_number', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            
            $completedMaterialIds = \App\Models\LmsMaterialProgress::where('student_id', $student->id)
                ->pluck('material_id')
                ->toArray();
            
            foreach ($allCourseMaterials as $m) {
                if ($m->id == $material->id) {
                    break; // Materi ini berada di urutan aktif dan boleh dibuka!
                }
                if (!in_array($m->id, $completedMaterialIds)) {
                    return back()->with('error', '🔒 Modul/Materi ini masih terkunci! Anda harus menyelesaikan materi ("' . $m->title . '") terlebih dahulu.');
                }
            }
        }

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
        $quiz = LmsQuiz::with(['course'])->findOrFail($quizId);
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.lms.show', $quiz->course_id)->with('error', 'Data siswa tidak ditemukan.');
        }

        if (!$quiz->isAvailable()) {
            return redirect()->route('mobile.lms.show', $quiz->course_id)
                ->with('error', 'Kuis ini belum tersedia atau sudah berakhir.');
        }

        // Check if student can still attempt
        if (!$quiz->canAttempt($student->id)) {
            return redirect()->route('mobile.lms.show', $quiz->course_id)
                ->with('error', 'Anda sudah mencapai batas percobaan untuk kuis ini.');
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

        // Hitung sisa durasi pengerjaan kuis berdasarkan started_at
        $elapsedSeconds = $attempt->started_at ? abs((int)now()->diffInSeconds($attempt->started_at)) : 0;
        $totalSeconds = $quiz->time_limit ? ($quiz->time_limit * 60) : null;
        $remainingSeconds = $totalSeconds !== null ? max(0, $totalSeconds - $elapsedSeconds) : null;

        // Load questions (optionally shuffled or sampled randomly, seeded by attempt id)
        $questionsQuery = $quiz->questions();
        if ($quiz->shuffle_questions || ($quiz->question_sample_count && $quiz->question_sample_count > 0)) {
            $questionsQuery->inRandomOrder($attempt->id);
        } else {
            $questionsQuery->orderBy('order_number');
        }

        if ($quiz->question_sample_count && $quiz->question_sample_count > 0) {
            $questionsQuery->take($quiz->question_sample_count);
        }

        $questions = $questionsQuery->get();

        if ($questions->isEmpty() && $quiz->cbtQuestionBank) {
            $cbtQuestions = $quiz->cbtQuestionBank->questions;
            if ($quiz->shuffle_questions || ($quiz->question_sample_count && $quiz->question_sample_count > 0)) {
                mt_srand($attempt->id);
                $shuffled = $cbtQuestions->all();
                shuffle($shuffled);
                $cbtQuestions = collect($shuffled);
            }
            if ($quiz->question_sample_count && $quiz->question_sample_count > 0) {
                $cbtQuestions = $cbtQuestions->take($quiz->question_sample_count);
            }
            $questions = $cbtQuestions->map(function($q) {
                return (object)[
                    'id' => $q->id,
                    'question' => $q->question_text ?? $q->question,
                    'question_type' => $q->question_type ?? 'multiple_choice',
                    'options' => is_string($q->options) ? json_decode($q->options, true) : ($q->options ?? [
                        ['key' => 'A', 'text' => $q->option_a ?? 'Pilihan A'],
                        ['key' => 'B', 'text' => $q->option_b ?? 'Pilihan B'],
                        ['key' => 'C', 'text' => $q->option_c ?? 'Pilihan C'],
                        ['key' => 'D', 'text' => $q->option_d ?? 'Pilihan D'],
                    ]),
                    'score' => $q->score ?? $q->points ?? 10,
                    'correct_answer' => $q->correct_answer ?? $q->answer_key ?? $q->answer,
                    'image_path' => $q->question_image ?? $q->image_path ?? null,
                    'video_url' => $q->video_url ?? null,
                ];
            });
        }

        if ($questions->isEmpty()) {
            $questions = collect([
                (object)[
                    'id' => 101,
                    'question' => 'Berapakah hasil dari 15 + 25?',
                    'question_type' => 'multiple_choice',
                    'options' => ['A' => '30', 'B' => '35', 'C' => '40', 'D' => '45'],
                    'score' => 10,
                    'correct_answer' => 'C',
                    'image_path' => null,
                    'video_url' => null,
                ],
                (object)[
                    'id' => 102,
                    'question' => 'Apakah PembdaHUB mendukung sistem pembelajaran mobile & desktop?',
                    'question_type' => 'multiple_choice',
                    'options' => ['A' => 'Ya, Benar', 'B' => 'Tidak'],
                    'score' => 10,
                    'correct_answer' => 'A',
                    'image_path' => null,
                    'video_url' => null,
                ]
            ]);
        }

        $answerMap = $attempt->answers()->get()->keyBy('question_id');
        $remainingAttempts = $quiz->getRemainingAttempts($student->id);

        return view('mobile.lms.quiz', compact('quiz', 'attempt', 'questions', 'answerMap', 'student', 'remainingSeconds', 'remainingAttempts'));
    }

    public function submitQuiz(Request $request, $attemptId)
    {
        $attempt = LmsQuizAttempt::with(['quiz.course', 'quiz.cbtQuestionBank'])->findOrFail($attemptId);
        
        $student = $this->getStudent();
        if ($student && $attempt->student_id !== $student->id) {
            return redirect()->route('mobile.lms.index')->with('error', 'Anda tidak memiliki akses ke percobaan kuis ini.');
        }

        if ($attempt->finished_at) {
            return redirect()->route('mobile.lms.quiz.result', $attempt->id)->with('info', 'Kuis sudah selesai dikerjakan.');
        }

        $quiz = $attempt->quiz;
        $questionsQuery = $quiz->questions();
        if ($quiz->shuffle_questions || ($quiz->question_sample_count && $quiz->question_sample_count > 0)) {
            $questionsQuery->inRandomOrder($attempt->id);
        } else {
            $questionsQuery->orderBy('order_number');
        }

        if ($quiz->question_sample_count && $quiz->question_sample_count > 0) {
            $questionsQuery->take($quiz->question_sample_count);
        }

        $questions = $questionsQuery->get();

        if ($questions->isEmpty() && $quiz->cbtQuestionBank) {
            $cbtQuestions = $quiz->cbtQuestionBank->questions;
            if ($quiz->shuffle_questions || ($quiz->question_sample_count && $quiz->question_sample_count > 0)) {
                mt_srand($attempt->id);
                $shuffled = $cbtQuestions->all();
                shuffle($shuffled);
                $cbtQuestions = collect($shuffled);
            }
            if ($quiz->question_sample_count && $quiz->question_sample_count > 0) {
                $cbtQuestions = $cbtQuestions->take($quiz->question_sample_count);
            }
            $questions = $cbtQuestions->map(function($q) {
                return (object)[
                    'id' => $q->id,
                    'question' => $q->question_text ?? $q->question,
                    'question_type' => $q->question_type ?? 'multiple_choice',
                    'options' => is_string($q->options) ? json_decode($q->options, true) : ($q->options ?? [
                        ['key' => 'A', 'text' => $q->option_a ?? 'Pilihan A'],
                        ['key' => 'B', 'text' => $q->option_b ?? 'Pilihan B'],
                        ['key' => 'C', 'text' => $q->option_c ?? 'Pilihan C'],
                        ['key' => 'D', 'text' => $q->option_d ?? 'Pilihan D'],
                    ]),
                    'score' => $q->score ?? $q->points ?? 10,
                    'correct_answer' => $q->correct_answer ?? $q->answer_key ?? $q->answer,
                ];
            });
        }

        if ($questions->isEmpty()) {
            $questions = collect([
                (object)['id' => 101, 'score' => 10, 'correct_answer' => 'C'],
                (object)['id' => 102, 'score' => 10, 'correct_answer' => 'A'],
            ]);
        }

        $effectivePointsPerQuestion = $quiz->getEffectivePointsPerQuestion($questions->count());
        $isQuizLevelScoring = ($quiz->points_per_question !== null || $quiz->question_sample_count !== null);

        $totalScore = 0;
        $maxScore = $isQuizLevelScoring
            ? ($questions->count() * $effectivePointsPerQuestion)
            : $questions->sum('score');

        foreach ($questions as $question) {
            $answer = $request->input("answers.{$question->id}");
            $finalAnswer = $answer;

            $isCorrect = null;
            $questionScore = null;

            $isAutoGradable = is_object($question) && method_exists($question, 'isAutoGradable')
                ? $question->isAutoGradable()
                : in_array($question->question_type ?? 'multiple_choice', ['multiple_choice', 'true_false', 'short_answer']);

            if ($isAutoGradable && !empty($question->correct_answer)) {
                $studentAnswer = trim((string)($answer ?? ''));
                $correctAnswer = trim((string)$question->correct_answer);

                if ($studentAnswer === '') {
                    $isCorrect = false;
                } elseif (($question->question_type ?? 'multiple_choice') === 'multiple_choice' && !empty($question->options)) {
                    $options = $question->options;
                    $firstOpt = $options[0] ?? null;
                    $isAssoc = is_array($firstOpt) && isset($firstOpt['key']);

                    if ($isAssoc) {
                        if (preg_match('/^\d+$/', $correctAnswer)) {
                            $alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                            $correctAnswer = $alphabets[(int)$correctAnswer] ?? $correctAnswer;
                        }
                        if (preg_match('/^\d+$/', $studentAnswer)) {
                            $alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                            $studentAnswer = $alphabets[(int)$studentAnswer] ?? $studentAnswer;
                        }
                        $isCorrect = strtolower($studentAnswer) === strtolower($correctAnswer);
                    } else {
                        if (!preg_match('/^\d+$/', $correctAnswer)) {
                            $alphabetMap = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5, 'g' => 6];
                            $lowerCorrect = strtolower($correctAnswer);
                            if (isset($alphabetMap[$lowerCorrect])) {
                                $correctAnswer = (string)$alphabetMap[$lowerCorrect];
                            }
                        }
                        if (!preg_match('/^\d+$/', $studentAnswer)) {
                            $alphabetMap = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5, 'g' => 6];
                            $lowerStudent = strtolower($studentAnswer);
                            if (isset($alphabetMap[$lowerStudent])) {
                                $studentAnswer = (string)$alphabetMap[$lowerStudent];
                            }
                        }

                        $shuffledOptions = ($quiz->shuffle_questions && method_exists($question, 'getShuffledOptions'))
                            ? $question->getShuffledOptions($attempt->id)
                            : $options;
                        $studentText = $shuffledOptions[(int)$studentAnswer] ?? null;
                        $correctText = $options[(int)$correctAnswer] ?? null;
                        $isCorrect = $studentText !== null && $correctText !== null
                            && strtolower(trim((string)$studentText)) === strtolower(trim((string)$correctText));

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
                } elseif (($question->question_type ?? '') === 'true_false') {
                    $normalizeTf = function($val) {
                        $v = strtolower(trim((string)$val));
                        if (in_array($v, ['true', '1', 't', 'b', 'benar', 'yes', 'y'])) return 'true';
                        if (in_array($v, ['false', '0', 'f', 's', 'salah', 'no', 'n'])) return 'false';
                        return $v;
                    };
                    $isCorrect = $normalizeTf($studentAnswer) === $normalizeTf($correctAnswer);
                } else {
                    $isCorrect = strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer));
                }

                $pointVal = $isQuizLevelScoring ? $effectivePointsPerQuestion : ($question->score ?? 10);
                $questionScore = $isCorrect ? $pointVal : 0;
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

        $scorePercentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 1) : 0;
        $passingScore = $quiz->passing_score ?? 70;

        $attempt->update([
            'finished_at' => now(),
            'score' => $scorePercentage,
            'is_passed' => $scorePercentage >= $passingScore,
        ]);

        // Sinkronisasi skor kuis ke tabel Grades utama agar muncul di Rekap Nilai
        try {
            $gradeService = app(GradeService::class);
            $gradeService->syncQuizAttemptToGrade($attempt);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('LMS Quiz grade sync failed: ' . $e->getMessage());
        }

        // Give EXP for completing Quiz
        if ($student && $student->user_id) {
            try {
                $expEarned = 10 + (int)(($scorePercentage / 100) * 40);
                \App\Models\ReputationLog::log(
                    $student->user_id,
                    $expEarned,
                    'lms_quiz',
                    'Menyelesaikan kuis: ' . $quiz->title . ' (' . number_format($scorePercentage, 1) . '%)',
                    $quiz
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal memberikan EXP Quiz di mobile: ' . $e->getMessage());
            }
        }

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
        $courseId = $material->course_id;
        $material->delete();
        return redirect()->route('mobile.lms.show', ['course' => $courseId, 'tab' => 'materi'])
            ->with('success', 'Materi berhasil dihapus!');
    }

    /**
     * Tambah Tugas Baru (Guru Mobile)
     */
    public function storeAssignment(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable',
            'due_date_only' => 'nullable',
            'due_time_only' => 'nullable',
        ]);

        $deadline = null;
        if ($request->filled('due_date')) {
            $rawDate = str_replace('T', ' ', $request->input('due_date'));
            try {
                $deadline = \Carbon\Carbon::parse($rawDate)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $deadline = $rawDate;
            }
        } elseif ($request->filled('due_date_only')) {
            $timePart = $request->input('due_time_only') ?: '23:59:00';
            if (strlen($timePart) === 5) {
                $timePart .= ':00';
            }
            $deadline = $request->input('due_date_only') . ' ' . $timePart;
        }

        LmsAssignment::create([
            'course_id' => $courseId,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'assignment_type' => $request->input('assignment_type', 'file_text'),
            'deadline' => $deadline,
            'due_date' => $deadline,
            'is_published' => true,
        ]);

        return redirect()->route('mobile.lms.show', ['course' => $courseId, 'tab' => 'tugas'])
            ->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Update/Edit Tugas (Guru Mobile)
     */
    public function updateAssignment(Request $request, $id)
    {
        $assignment = LmsAssignment::findOrFail($id);
        $courseId = $assignment->course_id;

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignment_type' => 'nullable|string',
            'due_date' => 'nullable',
            'due_date_only' => 'nullable',
            'due_time_only' => 'nullable',
        ]);

        $deadline = $assignment->deadline;
        if ($request->filled('due_date')) {
            $rawDate = str_replace('T', ' ', $request->input('due_date'));
            try {
                $deadline = \Carbon\Carbon::parse($rawDate)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $deadline = $rawDate;
            }
        } elseif ($request->filled('due_date_only')) {
            $timePart = $request->input('due_time_only') ?: '23:59:00';
            if (strlen($timePart) === 5) {
                $timePart .= ':00';
            }
            $deadline = $request->input('due_date_only') . ' ' . $timePart;
        }

        $assignment->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'assignment_type' => $request->input('assignment_type', $assignment->assignment_type ?: 'file_text'),
            'deadline' => $deadline,
            'due_date' => $deadline,
        ]);

        return redirect()->route('mobile.lms.show', ['course' => $courseId, 'tab' => 'tugas'])
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    /**
     * Hapus Tugas (Guru Mobile)
     */
    public function destroyAssignment($id)
    {
        $assignment = LmsAssignment::findOrFail($id);
        $courseId = $assignment->course_id;

        try {
            $assignment->submissions()->delete();
        } catch (\Throwable $e) {}

        $assignment->delete();

        return redirect()->route('mobile.lms.show', ['course' => $courseId, 'tab' => 'tugas'])
            ->with('success', 'Tugas berhasil dihapus!');
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
            'question_package_id' => 'nullable|exists:cbt_question_banks,id',
        ]);

        LmsQuiz::create([
            'course_id' => $courseId,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'time_limit' => $request->input('time_limit'),
            'question_package_id' => $request->input('question_package_id'),
            'is_published' => true,
        ]);

        return redirect()->route('mobile.lms.show', ['course' => $courseId, 'tab' => 'kuis'])
            ->with('success', 'Kuis berhasil ditambahkan!');
    }

    /**
     * Update/Edit Kuis (Guru Mobile)
     */
    public function updateQuiz(Request $request, $id)
    {
        $quiz = LmsQuiz::findOrFail($id);
        $courseId = $quiz->course_id;

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
        ]);

        $quiz->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'time_limit' => $request->input('time_limit'),
        ]);

        return redirect()->route('mobile.lms.show', ['course' => $courseId, 'tab' => 'kuis'])
            ->with('success', 'Kuis berhasil diperbarui!');
    }

    /**
     * Hapus Kuis (Guru Mobile)
     */
    public function destroyQuiz($id)
    {
        $quiz = LmsQuiz::findOrFail($id);
        $courseId = $quiz->course_id;

        try {
            $quiz->attempts()->delete();
        } catch (\Throwable $e) {}

        $quiz->delete();

        return redirect()->route('mobile.lms.show', ['course' => $courseId, 'tab' => 'kuis'])
            ->with('success', 'Kuis berhasil dihapus!');
    }

    /**
     * Stream PDF / File Video Materi LMS langsung dari storage (Buka di browser)
     */
    public function streamMaterial($id)
    {
        $material = LmsMaterial::findOrFail($id);
        
        $student = $this->getStudent();
        if ($student && ($material->course?->is_sequential || $material->module?->is_sequential)) {
            $allCourseMaterials = LmsMaterial::where('course_id', $material->course_id)
                ->orderBy('module_id', 'asc')->orderBy('order_number', 'asc')->orderBy('id', 'asc')->get();
            $completedMaterialIds = \App\Models\LmsMaterialProgress::where('student_id', $student->id)
                ->pluck('material_id')->toArray();
            foreach ($allCourseMaterials as $m) {
                if ($m->id == $material->id) break;
                if (!in_array($m->id, $completedMaterialIds)) {
                    return back()->with('error', '🔒 Materi ini masih terkunci! Selesaikan materi sebelumnya terlebih dahulu.');
                }
            }
        }

        $filePath = $material->file_path;

        if (!$filePath) {
            return redirect($material->file_url ?: '#');
        }

        $cleanPath = str_replace('storage/', '', $filePath);

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($cleanPath);
            $mimeType = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($cleanPath) ?: 'application/pdf';

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]);
        }

        if (\Illuminate\Support\Facades\Storage::exists($cleanPath)) {
            $fullPath = \Illuminate\Support\Facades\Storage::path($cleanPath);
            $mimeType = \Illuminate\Support\Facades\Storage::mimeType($cleanPath) ?: 'application/pdf';

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]);
        }

        return back()->with('error', 'Berkas materi PDF/Dokumen tidak ditemukan di server.');
    }

    /**
     * Unduh File Materi LMS (Download attachment)
     */
    public function downloadMaterial($id)
    {
        $material = LmsMaterial::findOrFail($id);

        $student = $this->getStudent();
        if ($student && ($material->course?->is_sequential || $material->module?->is_sequential)) {
            $allCourseMaterials = LmsMaterial::where('course_id', $material->course_id)
                ->orderBy('module_id', 'asc')->orderBy('order_number', 'asc')->orderBy('id', 'asc')->get();
            $completedMaterialIds = \App\Models\LmsMaterialProgress::where('student_id', $student->id)
                ->pluck('material_id')->toArray();
            foreach ($allCourseMaterials as $m) {
                if ($m->id == $material->id) break;
                if (!in_array($m->id, $completedMaterialIds)) {
                    return back()->with('error', '🔒 Materi ini masih terkunci! Selesaikan materi sebelumnya terlebih dahulu.');
                }
            }
        }

        $filePath = $material->file_path;

        if (!$filePath) {
            return redirect($material->file_url ?: '#');
        }

        $cleanPath = str_replace('storage/', '', $filePath);

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($cleanPath, $material->title . '.' . pathinfo($cleanPath, PATHINFO_EXTENSION));
        }

        if (\Illuminate\Support\Facades\Storage::exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::download($cleanPath, $material->title . '.' . pathinfo($cleanPath, PATHINFO_EXTENSION));
        }

        return back()->with('error', 'Berkas materi PDF/Dokumen tidak ditemukan di server.');
    }
}
