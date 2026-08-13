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

        if ($activeRole === 'siswa') {
            $student = Student::where('user_id', $user->id)->with('school')->first();
            
            if ($student) {
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
        } elseif (in_array($user->role, ['guru', 'pegawai'])) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            $activeCourses = LmsCourse::where('teacher_id', $teacher->id ?? 0)->take(3)->get();
        }

        // Recent Forum discussions (Pembda Space Terbaru)
        $recentDiscussions = ForumThread::with(['user'])
            ->withCount(['replies', 'likes'])
            ->latest()
            ->take(5)
            ->get();

        // Popular Forum discussions (Pembda Space Paling Rame)
        $popularDiscussions = ForumThread::with(['user'])
            ->withCount(['replies', 'likes'])
            ->orderByRaw('(replies_count + likes_count) DESC')
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
            'activeCourses'
        ));
    }
}
