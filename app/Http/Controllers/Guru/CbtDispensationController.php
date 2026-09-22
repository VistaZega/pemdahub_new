<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CbtExam;
use App\Models\CbtExamDispensation;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\CbtTuitionComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CbtDispensationController extends Controller
{
    public function __construct(
        protected CbtTuitionComplianceService $complianceService
    ) {}

    /**
     * Dapatkan data guru yang sedang login
     */
    private function resolveTeacher(): ?Teacher
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    /**
     * Halaman kelola dispensasi ujian CBT oleh Wali Kelas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah']))
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        $teacher = $this->resolveTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Tidak ada Tahun Pelajaran aktif.');
        }

        // Ambil kelas bimbingan wali kelas
        $classroomsQuery = Classroom::where('academic_year_id', $activeYear->id);
        if (!$isAdmin && $teacher) {
            $tIds = Auth::user() ? Auth::user()->teacherIds() : $teacher->allTeacherIds();
            $classroomsQuery->whereIn('homeroom_teacher_id', $tIds);
        }
        $homeroomClassrooms = $classroomsQuery->orderBy('grade_level')->orderBy('class_name')->get();

        if ($homeroomClassrooms->isEmpty() && !$isAdmin) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Anda tidak terdaftar sebagai Wali Kelas pada Tahun Pelajaran saat ini.');
        }

        // Kelas yang dipilih (default kelas pertama)
        $selectedClassroomId = $request->query('classroom_id', $homeroomClassrooms->first()?->id);
        $selectedClassroom = $homeroomClassrooms->firstWhere('id', $selectedClassroomId) ?? $homeroomClassrooms->first();

        // Ambil semua ujian CBT yang mensyaratkan uang sekolah untuk kelas ini
        $examsQuery = CbtExam::query()
            ->where('academic_year_id', $activeYear->id)
            ->where('requires_tuition_payment', true);

        if ($selectedClassroom) {
            $examsQuery->whereHas('participants', fn($q) => $q->where('classroom_id', $selectedClassroom->id));
        }

        $exams = $examsQuery->with(['subject', 'teacher'])
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->get();

        // Ujian yang dipilih (default ujian pertama)
        $selectedExamId = $request->query('exam_id', $exams->first()?->id);
        $selectedExam = $exams->firstWhere('id', $selectedExamId) ?? $exams->first();

        $overview = null;
        if ($selectedExam && $selectedClassroom) {
            $overview = $this->complianceService->getClassroomComplianceOverview($selectedExam, $selectedClassroom);
        }

        return view('guru.cbt.dispensations.index', compact(
            'teacher',
            'activeYear',
            'homeroomClassrooms',
            'selectedClassroom',
            'exams',
            'selectedExam',
            'overview',
            'isAdmin'
        ));
    }

    /**
     * Otorisasi pemberian dispensasi ujian kepada siswa
     */
    public function grant(Request $request, CbtExam $exam, Student $student)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah']))
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        $teacher = $this->resolveTeacher();

        // Pastikan guru adalah wali kelas dari siswa ini atau admin
        if (!$isAdmin) {
            $activeClass = $student->currentClassroom()->first() ?? $student->classroom;
            $tIds = Auth::user() ? Auth::user()->teacherIds() : ($teacher ? $teacher->allTeacherIds() : []);
            $isHomeroom = $activeClass && in_array((int) $activeClass->homeroom_teacher_id, array_map('intval', $tIds), true);
            abort_unless($isHomeroom, 403, 'Anda bukan Wali Kelas dari siswa ini.');
        }

        $this->complianceService->grantDispensation(
            $exam,
            $student,
            $request->input('reason'),
            Auth::id()
        );

        $studentName = $student->full_name ?: $student->name;
        return back()->with('success', "Dispensasi ujian berhasil diberikan kepada {$studentName}. Siswa sekarang dapat mengerjakan ujian.");
    }

    /**
     * Cabut dispensasi ujian siswa
     */
    public function revoke(CbtExam $exam, Student $student)
    {
        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah']))
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        $teacher = $this->resolveTeacher();

        if (!$isAdmin) {
            $activeClass = $student->currentClassroom()->first() ?? $student->classroom;
            $tIds = Auth::user() ? Auth::user()->teacherIds() : ($teacher ? $teacher->allTeacherIds() : []);
            $isHomeroom = $activeClass && in_array((int) $activeClass->homeroom_teacher_id, array_map('intval', $tIds), true);
            abort_unless($isHomeroom, 403, 'Anda bukan Wali Kelas dari siswa ini.');
        }

        $this->complianceService->revokeDispensation($exam, $student, Auth::id());

        $studentName = $student->full_name ?: $student->name;
        return back()->with('success', "Dispensasi ujian untuk {$studentName} berhasil dicabut.");
    }
}
