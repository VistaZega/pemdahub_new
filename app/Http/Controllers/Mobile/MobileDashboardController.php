<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ForumThread;
use App\Models\LmsCourse;
use App\Models\LmsAssignment;
use App\Models\PklPlacement;
use App\Models\FinalProject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MobileDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeRole = session('active_role', $user->role);
        $student = null;
        $teacher = null;
        $attendanceStats = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
        $recentDiscussions = [];
        $activeCourses = [];
        $todaySchedule = [];

        // Siswa specific flags
        $showPkl = false;
        $showProjectAkhir = false;
        $showPenelitianAkhir = false;

        // Guru specific flags
        $hasPklBimbingan = false;
        $hasProjectBimbingan = false;
        $hasProjectUjian = false;
        $isPanitiaPkl = false;
        $isPanitiaProyek = false;

        if ($activeRole === 'siswa') {
            $student = Student::where('user_id', $user->id)->with('school')->first();
            
            if ($student) {
                // Determine school type & grade level for PKL & Final Project
                $schoolType = strtoupper($student->school->type ?? '');
                $gradeLevel = $student->currentClassroom()->first()?->grade_level ?? $student->grade_level;
                $isKelasXII = ($gradeLevel == 12);

                $showPkl = ($schoolType === 'SMK' && $isKelasXII);
                $showProjectAkhir = ($schoolType === 'SMK' && $isKelasXII);
                $showPenelitianAkhir = ($schoolType === 'SMA' && $isKelasXII);

                // Kehadiran bulan ini
                $currentMonth = now()->month;
                $currentYear = now()->year;
                $attendances = Attendance::where('student_id', $student->id)
                    ->whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->get();

                foreach ($attendances as $att) {
                    $st = strtolower($att->status);
                    if (isset($attendanceStats[$st])) {
                        $attendanceStats[$st]++;
                    }
                }

                // LMS courses for student
                $activeCourses = LmsCourse::whereHas('classes', function ($q) use ($student) {
                    $classroom = $student->currentClassroom()->first();
                    if ($classroom) {
                        $q->where('classroom_id', $classroom->id);
                    }
                })->where('is_published', true)->take(3)->get();
            }
        } elseif (in_array($activeRole, ['guru', 'pegawai', 'superadmin', 'admin_sekolah', 'kepala_sekolah', 'ketua_yayasan'])) {
            $teacher = Teacher::where('user_id', $user->id)->with('school')->first();
            if (!$teacher && in_array($user->role, ['superadmin', 'admin_sekolah', 'kepala_sekolah'])) {
                // Fallback for admin previewing teacher dashboard
                $teacher = Teacher::when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))->first();
            }

            $teacherId = $teacher?->id;
            if ($teacherId) {
                $hasPklBimbingan = PklPlacement::where('teacher_id', $teacherId)->exists();
                $hasProjectBimbingan = FinalProject::where('advisor_id', $teacherId)->exists();
                $hasProjectUjian = FinalProject::where('examiner_id', $teacherId)->exists();
            }

            $isPanitiaPkl = $user->isPanitiaPkl() || $user->isSuperAdmin() || $user->isAdminSekolah() || $user->isKepalaSekolah();
            $isPanitiaProyek = $user->isPanitiaProyek() || $user->isSuperAdmin() || $user->isAdminSekolah() || $user->isKepalaSekolah();

            $activeCourses = LmsCourse::where('teacher_id', $teacher->id ?? 0)->take(3)->get();
        }

        // Recent Forum discussions (Pembda Space Terbaru)
        $recentDiscussions = ForumThread::with(['user.student', 'user.teacher'])
            ->withCount(['replies', 'likes'])
            ->latest()
            ->take(5)
            ->get();

        // Popular Forum discussions (Pembda Space Paling Rame)
        $popularDiscussions = ForumThread::with(['user.student', 'user.teacher'])
            ->withCount(['replies', 'likes'])
            ->orderByDesc('replies_count')
            ->latest()
            ->take(5)
            ->get();

        return view('mobile.dashboard', compact(
            'user',
            'student',
            'teacher',
            'attendanceStats',
            'recentDiscussions',
            'popularDiscussions',
            'activeCourses',
            'showPkl',
            'showProjectAkhir',
            'showPenelitianAkhir',
            'hasPklBimbingan',
            'hasProjectBimbingan',
            'hasProjectUjian',
            'isPanitiaPkl',
            'isPanitiaProyek'
        ));
    }
}
