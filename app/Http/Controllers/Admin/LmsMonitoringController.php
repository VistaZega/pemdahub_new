<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsSubmission;
use App\Models\LmsMaterialProgress;
use App\Models\LmsDiscussion;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\School;

class LmsMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin() || $user->isKetuaYayasan();

        // Ambil school_id: jika superadmin, bisa lewat request query; jika kepsek/guru/admin, gunakan session active_school_id atau user->school_id
        $schoolId = $request->query('school_id');
        if (!$schoolId) {
            $schoolId = session('active_school_id', $user->school_id);
        }

        // Jika superadmin tidak memilih sekolah spesifik dan tidak ada sesi unit aktif, $schoolId bisa null (lihat semua)
        if ($isSuperAdmin && !$request->has('school_id') && !session()->has('active_school_id')) {
            $schoolId = null;
        }

        $query = LmsCourse::query();
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $totalCourses = (clone $query)->count();
        $totalEnrollments = LmsEnrollment::whereHas('lmsClass.course', function($q) use ($schoolId) {
            if ($schoolId) $q->where('school_id', $schoolId);
        })->count();

        $totalSubmissions = LmsSubmission::whereHas('assignment.course', function($q) use ($schoolId) {
            if ($schoolId) $q->where('school_id', $schoolId);
        })->count();

        $totalDiscussions = LmsDiscussion::whereHas('course', function($q) use ($schoolId) {
            if ($schoolId) $q->where('school_id', $schoolId);
        })->count();

        // Top active courses by submissions
        $activeCourses = LmsCourse::with(['teacher.user', 'subject', 'school'])
            ->withCount(['submissions', 'enrollments'])
            ->when($schoolId, function($q) use ($schoolId) {
                return $q->where('school_id', $schoolId);
            })
            ->orderBy('submissions_count', 'desc')
            ->take(5)
            ->get();

        // Latest activities
        $latestSubmissions = LmsSubmission::with(['student.user', 'assignment.course'])
            ->whereHas('student.user')
            ->whereHas('assignment.course', function($q) use ($schoolId) {
                if ($schoolId) $q->where('school_id', $schoolId);
            })
            ->latest()
            ->take(5)
            ->get();

        // ============================================
        // Data Keaktifan Guru di LMS (termasuk guru lintas unit)
        // ============================================
        $teacherQuery = Teacher::with(['user', 'school'])
            ->where('is_active', true);

        if ($schoolId) {
            $teacherQuery->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('additionalSchools', fn($q2) => $q2->where('schools.id', $schoolId))
                  ->orWhereHas('teachingAssignments.classroom', fn($q3) => $q3->where('school_id', $schoolId))
                  ->orWhereHas('lmsCourses', fn($q4) => $q4->where('school_id', $schoolId));
            });
        }

        $teachers = $teacherQuery->orderBy('full_name')->get();

        $sevenDaysAgo = Carbon::now()->subDays(7);

        $teacherActivities = $teachers->map(function ($teacher) use ($sevenDaysAgo, $schoolId) {
            $courseQuery = LmsCourse::where('teacher_id', $teacher->id);
            if ($schoolId) {
                $courseQuery->where('school_id', $schoolId);
            }
            $courseIds = $courseQuery->pluck('id');

            $coursesCount = $courseIds->count();
            $modulesCount = DB::table('lms_modules')
                ->whereIn('course_id', $courseIds)
                ->whereNull('deleted_at')
                ->count();
            $materialsCount = DB::table('lms_materials')
                ->whereIn('course_id', $courseIds)
                ->whereNull('deleted_at')
                ->count();
            $assignmentsCount = DB::table('lms_assignments')
                ->whereIn('course_id', $courseIds)
                ->whereNull('deleted_at')
                ->count();
            $quizzesCount = DB::table('lms_quizzes')
                ->whereIn('course_id', $courseIds)
                ->whereNull('deleted_at')
                ->count();

            // Hitung total siswa terdaftar
            $classIds = DB::table('lms_classes')
                ->whereIn('course_id', $courseIds)
                ->pluck('id');
            $enrollmentsCount = DB::table('lms_enrollments')
                ->whereIn('lms_class_id', $classIds)
                ->count();

            // Aktivitas terakhir: ambil updated_at terbaru dari courses milik guru ini
            $latestCourseActivity = LmsCourse::where('teacher_id', $teacher->id)
                ->orderBy('updated_at', 'desc')
                ->value('updated_at');

            $latestMaterialActivity = DB::table('lms_materials')
                ->whereIn('course_id', $courseIds)
                ->orderBy('updated_at', 'desc')
                ->value('updated_at');

            $latestAssignmentActivity = DB::table('lms_assignments')
                ->whereIn('course_id', $courseIds)
                ->orderBy('updated_at', 'desc')
                ->value('updated_at');

            // Ambil yang paling baru di antara semua
            $lastActivity = collect([
                $latestCourseActivity,
                $latestMaterialActivity,
                $latestAssignmentActivity,
            ])->filter()->max();

            $lastActivityCarbon = $lastActivity ? Carbon::parse($lastActivity) : null;

            // Hitung total konten (materi + tugas + quiz)
            $totalContent = $materialsCount + $assignmentsCount + $quizzesCount;

            // Tentukan level keaktifan
            $hasRecentActivity = $lastActivityCarbon && $lastActivityCarbon->gte($sevenDaysAgo);

            if ($coursesCount >= 2 && $modulesCount >= 5 && $totalContent >= 10 && $hasRecentActivity) {
                $level = 'sangat_aktif';
                $levelLabel = 'Sangat Aktif';
                $levelColor = 'emerald';
                $levelScore = 4;
            } elseif ($coursesCount >= 1 && $modulesCount >= 2 && $totalContent >= 3) {
                $level = 'aktif';
                $levelLabel = 'Aktif';
                $levelColor = 'blue';
                $levelScore = 3;
            } elseif ($coursesCount >= 1) {
                $level = 'kurang_aktif';
                $levelLabel = 'Kurang Aktif';
                $levelColor = 'amber';
                $levelScore = 2;
            } else {
                $level = 'belum_aktif';
                $levelLabel = 'Belum Aktif';
                $levelColor = 'red';
                $levelScore = 1;
            }

            // Mata pelajaran yang diajar (dari courses)
            $subjectNames = LmsCourse::where('teacher_id', $teacher->id)
                ->with('subject')
                ->get()
                ->pluck('subject.subject_name')
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');

            return (object) [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
                'user_name' => optional($teacher->user)->name,
                'photo_url' => $teacher->photo_url,
                'subjects' => $subjectNames ?: '-',
                'courses_count' => $coursesCount,
                'modules_count' => $modulesCount,
                'materials_count' => $materialsCount,
                'assignments_count' => $assignmentsCount,
                'quizzes_count' => $quizzesCount,
                'enrollments_count' => $enrollmentsCount,
                'total_content' => $totalContent,
                'last_activity' => $lastActivityCarbon,
                'level' => $level,
                'level_label' => $levelLabel,
                'level_color' => $levelColor,
                'level_score' => $levelScore,
            ];
        })->sortByDesc('level_score')->values();

        // Hitung summary per level
        $levelSummary = [
            'sangat_aktif' => $teacherActivities->where('level', 'sangat_aktif')->count(),
            'aktif' => $teacherActivities->where('level', 'aktif')->count(),
            'kurang_aktif' => $teacherActivities->where('level', 'kurang_aktif')->count(),
            'belum_aktif' => $teacherActivities->where('level', 'belum_aktif')->count(),
        ];

        return view('admin.lms.monitoring', compact(
            'totalCourses',
            'totalEnrollments',
            'totalSubmissions',
            'totalDiscussions',
            'activeCourses',
            'latestSubmissions',
            'teacherActivities',
            'levelSummary'
        ));
    }

    /**
     * API: Detail konten LMS guru tertentu
     */
    public function teacherDetail($teacherId)
    {
        $schoolId = session('active_school_id', auth()->user()->school_id);
        $isSuperAdmin = auth()->user()->isSuperAdmin() || auth()->user()->isKetuaYayasan();

        $teacher = Teacher::with('user')->findOrFail($teacherId);

        // Security: pastikan guru ini terkait dengan sekolah ini (induk, multi-unit, atau memiliki kursus di unit ini)
        $isAssociatedWithSchool = $teacher->school_id == $schoolId
            || $teacher->additionalSchools()->where('schools.id', $schoolId)->exists()
            || $teacher->teachingAssignments()->whereHas('classroom', fn($c) => $c->where('school_id', $schoolId))->exists()
            || LmsCourse::where('teacher_id', $teacher->id)->where('school_id', $schoolId)->exists();

        if (!$isSuperAdmin && !$isAssociatedWithSchool) {
            abort(403);
        }

        $courses = LmsCourse::where('teacher_id', $teacherId)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->with(['subject', 'classroom', 'modules' => function($q) {
                $q->withCount(['materials', 'assignments', 'quizzes']);
            }])
            ->withCount(['modules', 'materials', 'assignments', 'quizzes', 'enrollments', 'submissions'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
                'photo_url' => $teacher->photo_url,
            ],
            'courses' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'subject' => optional($course->subject)->subject_name,
                    'classroom' => optional($course->classroom)->class_name,
                    'status' => $course->getStatusLabel(),
                    'is_published' => $course->is_published,
                    'modules_count' => $course->modules_count,
                    'materials_count' => $course->materials_count,
                    'assignments_count' => $course->assignments_count,
                    'quizzes_count' => $course->quizzes_count,
                    'enrollments_count' => $course->enrollments_count,
                    'submissions_count' => $course->submissions_count,
                    'updated_at' => $course->updated_at ? $course->updated_at->diffForHumans() : '-',
                    'modules' => $course->modules->map(function ($mod) {
                        return [
                            'title' => $mod->title,
                            'materials_count' => $mod->materials_count,
                            'assignments_count' => $mod->assignments_count,
                            'quizzes_count' => $mod->quizzes_count,
                        ];
                    }),
                ];
            }),
        ]);
    }
}
