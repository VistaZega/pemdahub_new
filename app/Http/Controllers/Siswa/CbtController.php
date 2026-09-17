<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CbtAnswer;
use App\Models\CbtExam;
use App\Models\CbtExamResult;
use App\Models\CbtExamSession;
use App\Models\Student;
use App\Models\StudentClass;
use App\Services\CbtService;
use App\Services\CbtTuitionComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CbtController extends Controller
{
    public function __construct(
        private CbtService $cbtService,
        private CbtTuitionComplianceService $complianceService
    ) {}

    /**
     * Resolve the authenticated student record (DRY helper)
     */
    private function resolveStudent(): Student
    {
        return Student::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Get active classroom IDs for a student.
     * Prioritizes the active academic year, supports multiple classes (e.g. combined + regular in SMK),
     * and falls back to student's direct classroom_id or latest active class.
     *
     * @return int[]
     */
    private function getActiveClassroomIds(Student $student): array
    {
        $activeAy = AcademicYear::where('is_active', true)->first();

        $classQuery = StudentClass::where('student_id', $student->id)
            ->where('status', 'aktif');

        // 1. Prioritaskan kelas aktif di Tahun Pelajaran aktif
        if ($activeAy) {
            $ids = (clone $classQuery)->where('academic_year_id', $activeAy->id)
                ->pluck('classroom_id')
                ->filter()
                ->toArray();

            if (!empty($ids)) {
                if ($student->classroom_id && !in_array($student->classroom_id, $ids)) {
                    $ids[] = $student->classroom_id;
                }
                return array_values(array_unique(array_map('intval', $ids)));
            }
        }

        // 2. Fallback: ambil riwayat kelas aktif terbaru
        $ids = $classQuery->latest('id')
            ->pluck('classroom_id')
            ->filter()
            ->toArray();

        if ($student->classroom_id && !in_array($student->classroom_id, $ids)) {
            $ids[] = $student->classroom_id;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Get active classroom_id for a student (backwards compatibility)
     */
    private function getActiveClassroomId(Student $student): ?int
    {
        $ids = $this->getActiveClassroomIds($student);
        return !empty($ids) ? $ids[0] : null;
    }

    /**
     * Daftar ujian yang tersedia untuk siswa
     */
    public function index()
    {
        $student = $this->resolveStudent();
        $classroomIds = $this->getActiveClassroomIds($student);

        if (empty($classroomIds)) {
            return view('siswa.cbt.index', ['availableExams' => collect()]);
        }

        $availableExams = CbtExam::whereIn('status', ['published', 'active'])
            ->whereHas('participants', fn($q) => $q->whereIn('classroom_id', $classroomIds))
            ->with(['subject', 'teacher'])
            ->orderBy('start_time')
            ->get();

        // Preload attempts & last results for all exams in bulk (fix N+1)
        $examIds = $availableExams->pluck('id');
        $attemptCounts = CbtExamSession::where('student_id', $student->id)
            ->whereIn('exam_id', $examIds)
            ->selectRaw('exam_id, count(*) as cnt')
            ->groupBy('exam_id')
            ->pluck('cnt', 'exam_id');

        $lastResults = CbtExamResult::where('student_id', $student->id)
            ->whereIn('exam_id', $examIds)
            ->orderByDesc('created_at')
            ->get()
            ->unique('exam_id')
            ->keyBy('exam_id');

        // Fetch active sessions to allow "Continue" regardless of attempt count
        $activeSessions = CbtExamSession::where('student_id', $student->id)
            ->whereIn('exam_id', $examIds)
            ->where('status', 'in_progress')
            ->get()
            ->keyBy('exam_id');

        $availableExams->each(function ($exam) use ($student, $attemptCounts, $lastResults, $activeSessions) {
            $exam->attempts_used = $attemptCounts[$exam->id] ?? 0;
            $exam->has_active_session = isset($activeSessions[$exam->id]);
            
            // Check compliance uang sekolah
            $exam->compliance = $this->complianceService->checkStudentCompliance($exam, $student);

            // Can attempt if: (has active session OR hasn't reached max attempts) AND compliance allowed
            $exam->can_attempt = ($exam->has_active_session || ($exam->attempts_used < $exam->max_attempts)) && $exam->compliance['allowed'];
            
            $exam->last_result = $lastResults[$exam->id] ?? null;
        });

        return view('siswa.cbt.index', compact('availableExams'));
    }

    /**
     * Detail ujian sebelum mulai
     */
    public function show(CbtExam $exam)
    {
        $student = $this->resolveStudent();
        $classroomIds = $this->getActiveClassroomIds($student);

        abort_if(empty($classroomIds), 422, 'Anda belum terdaftar di kelas aktif.');

        // Cek apakah siswa eligible (salah satu kelas aktifnya ada di participants ujian)
        $isEligible = $exam->participants()
            ->whereIn('classroom_id', $classroomIds)
            ->exists();

        if (!$isEligible) {
            abort(403, 'Anda tidak terdaftar untuk ujian ini.');
        }

        // Cek kepatuhan pembayaran uang sekolah / dispensasi
        $compliance = $this->complianceService->checkStudentCompliance($exam, $student);

        // Cek session yang masih berjalan
        $activeSession = CbtExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        $attemptsUsed = CbtExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->count();

        $lastResult = CbtExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        return view('siswa.cbt.show', compact('exam', 'activeSession', 'attemptsUsed', 'lastResult', 'compliance'));
    }

    /**
     * Verifikasi kode akses (jika ada)
     */
    public function verifyAccess(Request $request, CbtExam $exam)
    {
        $student = $this->resolveStudent();

        // Cek kepatuhan uang sekolah terlebih dahulu
        $compliance = $this->complianceService->checkStudentCompliance($exam, $student);
        if (!$compliance['allowed']) {
            return redirect()->route('siswa.cbt.show', $exam)
                ->with('error', $compliance['message'] ?? 'Akses ujian dibatasi karena kepatuhan uang sekolah.');
        }

        if ($exam->access_code && $request->access_code !== $exam->access_code) {
            return back()->with('error', 'Kode akses salah.');
        }

        // Access verified — start the exam directly via internal call
        return $this->start($request, $exam);
    }

    /**
     * Mulai atau lanjutkan ujian (POST)
     */
    public function start(Request $request, CbtExam $exam)
    {
        $student = $this->resolveStudent();
        $classroomIds = $this->getActiveClassroomIds($student);

        abort_if(empty($classroomIds), 422, 'Anda belum terdaftar di kelas aktif.');

        // Verify eligibility: student's classroom must be in exam participants
        $matchingParticipant = $exam->participants()
            ->whereIn('classroom_id', $classroomIds)
            ->first();

        abort_unless($matchingParticipant, 403, 'Anda tidak terdaftar untuk ujian ini.');

        // Verify compliance uang sekolah
        $compliance = $this->complianceService->checkStudentCompliance($exam, $student);
        if (!$compliance['allowed']) {
            return redirect()->route('siswa.cbt.show', $exam)
                ->with('error', $compliance['message'] ?? 'Akses ujian dibatasi karena kepatuhan uang sekolah.');
        }

        // Verify exam is accessible (active + within time window)
        if (!$exam->isAccessible()) {
            return redirect()->route('siswa.cbt.show', $exam)
                ->with('error', 'Ujian tidak tersedia saat ini (belum aktif atau sudah melewati batas waktu).');
        }

        // Cek session aktif
        $session = CbtExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$session) {
            // Mulai session baru dengan classroom_id peserta yang cocok
            $session = $this->cbtService->startExamSession($exam, $student, $matchingParticipant->classroom_id);
        }

        // Cek apakah sudah melewati deadline
        if ($session->deadline_at && now()->greaterThan($session->deadline_at)) {
            $this->cbtService->submitSession($session);
            return redirect()->route('siswa.cbt.result', $exam)
                ->with('warning', 'Waktu ujian telah habis. Jawaban Anda telah dikumpulkan otomatis.');
        }

        // Load soal sesuai urutan
        $questionIds = $session->question_order ?? [];
        $questions = collect();

        if (!empty($questionIds)) {
            $questionsMap = \App\Models\CbtQuestion::with('options')
                ->whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');

            $questions = collect($questionIds)->map(fn($id) => $questionsMap->get($id))->filter();
        }

        // Load jawaban yang sudah ada
        $answers = CbtAnswer::where('session_id', $session->id)
            ->get()
            ->keyBy('question_id');

        // Option orders for randomized options
        $optionOrders = $session->option_orders ?? [];

        $remainingSeconds = $session->deadline_at
            ? max(0, now()->diffInSeconds($session->deadline_at, false))
            : $exam->duration_minutes * 60;

        return view('siswa.cbt.exam', compact('exam', 'session', 'questions', 'answers', 'optionOrders', 'remainingSeconds'));
    }

    /**
     * Simpan jawaban (AJAX)
     */
    public function saveAnswer(Request $request, CbtExamSession $session)
    {
        $student = $this->resolveStudent();

        // Pastikan session milik siswa ini
        if ($session->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'question_id' => 'required|integer',
            'selected_option' => 'nullable|string',
            'text_answer' => 'nullable|string',
        ]);

        try {
            $this->cbtService->saveAnswer(
                $session,
                $validated['question_id'],
                [
                    'selected_option' => $validated['selected_option'] ?? null,
                    'text_answer' => $validated['text_answer'] ?? null,
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Track tab switch (AJAX - anti cheat)
     */
    public function tabSwitch(Request $request, CbtExamSession $session)
    {
        $student = $this->resolveStudent();

        if ($session->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session->increment('tab_switch_count');

        // Auto submit if too many tab switches (configurable, default 5)
        $maxTabSwitches = config('cbt.max_tab_switches', 5);
        if ($session->tab_switch_count >= $maxTabSwitches) {
            $result = $this->cbtService->submitSession($session);
            return response()->json([
                'auto_submitted' => true,
                'message' => 'Ujian dikumpulkan otomatis karena terlalu banyak berpindah tab.',
            ]);
        }

        return response()->json([
            'auto_submitted' => false,
            'tab_switch_count' => $session->tab_switch_count,
            'remaining' => $maxTabSwitches - $session->tab_switch_count,
        ]);
    }

    /**
     * Heartbeat check to sync time and detect pause state (AJAX)
     */
    public function heartbeat(Request $request, CbtExamSession $session)
    {
        $student = $this->resolveStudent();

        if ($session->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $exam = $session->exam;
        $isPaused = (bool)$exam->is_paused;

        $remainingSeconds = 0;
        if ($session->deadline_at) {
            if ($isPaused && $exam->paused_at) {
                $remainingSeconds = max(0, $exam->paused_at->diffInSeconds($session->deadline_at, false));
            } else {
                $remainingSeconds = max(0, now()->diffInSeconds($session->deadline_at, false));
            }
        } else {
            $remainingSeconds = $exam->duration_minutes * 60;
        }

        return response()->json([
            'is_paused' => $isPaused,
            'remaining_seconds' => $remainingSeconds,
            'status' => $session->status,
        ]);
    }

    /**
     * Submit ujian
     */
    public function submit(CbtExamSession $session)
    {
        $student = $this->resolveStudent();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        $result = $this->cbtService->submitSession($session);

        return redirect()->route('siswa.cbt.result', $session->exam_id)
            ->with('success', 'Ujian berhasil dikumpulkan.');
    }

    /**
     * Lihat hasil ujian
     */
    public function result(CbtExam $exam)
    {
        $student = $this->resolveStudent();

        if (!$exam->show_result) {
            return view('siswa.cbt.result-hidden', compact('exam'));
        }

        $results = CbtExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        $latestResult = $results->first();

        $pendingEssaysCount = 0;
        if ($latestResult && $latestResult->session_id) {
            $pendingEssaysCount = CbtAnswer::where('session_id', $latestResult->session_id)
                ->whereHas('question', fn($q) => $q->whereIn('question_type', ['essay', 'fill_blank']))
                ->whereNull('manual_score')
                ->count();
        }

        return view('siswa.cbt.result', compact('exam', 'results', 'latestResult', 'pendingEssaysCount'));
    }

    /**
     * Review jawaban (jika diizinkan)
     */
    public function review(CbtExamSession $session)
    {
        $student = $this->resolveStudent();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        $exam = $session->exam;

        if (!$exam->allow_review) {
            abort(403, 'Review jawaban tidak diizinkan untuk ujian ini.');
        }

        $answers = CbtAnswer::where('session_id', $session->id)
            ->with(['question.options'])
            ->get()
            ->keyBy('question_id');

        $questionIds = $session->question_order ?? [];
        $questionsMap = \App\Models\CbtQuestion::with('options')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $questions = collect($questionIds)->map(fn($id) => $questionsMap->get($id))->filter();

        $showAnswerKey = $exam->show_answer_key;

        return view('siswa.cbt.review', compact('exam', 'session', 'questions', 'answers', 'showAnswerKey'));
    }

    /**
     * Riwayat semua ujian siswa
     */
    public function history()
    {
        $student = $this->resolveStudent();

        $results = CbtExamResult::where('student_id', $student->id)
            ->with(['exam.subject', 'exam.teacher'])
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('siswa.cbt.history', compact('results'));
    }
}
