<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\EmployeeAttendance;
use App\Models\FinalGrade;
use App\Models\Grade;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\ReportCard;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KepsekDashboardController extends Controller
{
    private function getSchoolIds()
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isKetuaYayasan()) {
            return School::where('is_active', true)->schoolsOnly()->pluck('id');
        }
        return collect([$user->school_id]);
    }

    public function index(Request $request)
    {
        $schoolIds = $this->getSchoolIds();
        $activeAy = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();
        $selectedSchoolId = $request->get('school_id', $schoolIds->first());

        // ──────────────────────────────────────────────
        // 1. REKAP NILAI PER SEKOLAH
        // ──────────────────────────────────────────────
        $nilaiStats = [];
        foreach ($schoolIds as $sid) {
            $school = School::find($sid);
            $classrooms = Classroom::where('school_id', $sid)
                ->where('academic_year_id', $activeAy?->id)
                ->where('is_active', true)
                ->get();

            $classData = [];
            foreach ($classrooms as $c) {
                $finalGrades = FinalGrade::whereHas('student.studentClasses', function ($q) use ($c, $activeAy) {
                    $q->where('classroom_id', $c->id)->where('status', 'aktif')
                      ->when($activeAy, fn($sq) => $sq->where('academic_year_id', $activeAy->id));
                })->where('semester_id', $activeSemester?->id);

                $avg = (clone $finalGrades)->avg('final_score');
                $passed = (clone $finalGrades)->where('is_passed', true)->count();
                $total = (clone $finalGrades)->count();

                $classData[] = [
                    'class' => $c,
                    'avg' => $avg ? round($avg, 2) : null,
                    'passed' => $passed,
                    'total' => $total,
                    'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
                ];
            }

            $nilaiStats[] = [
                'school' => $school,
                'classes' => $classData,
                'overall_avg' => collect($classData)->avg('avg'),
            ];
        }

        // ──────────────────────────────────────────────
        // 2. AKTIVITAS LMS GURU
        // ──────────────────────────────────────────────
        $teachers = Teacher::whereIn('school_id', $schoolIds)
            ->where('is_active', true)
            ->with('school')
            ->get();

        $lmsActivity = $teachers->map(function ($t) {
            $courseIds = LmsCourse::where('teacher_id', $t->id)->pluck('id');
            return (object) [
                'teacher' => $t,
                'courses' => $courseIds->count(),
                'materials' => DB::table('lms_materials')->whereIn('course_id', $courseIds)->whereNull('deleted_at')->count(),
                'assignments' => DB::table('lms_assignments')->whereIn('course_id', $courseIds)->whereNull('deleted_at')->count(),
                'quizzes' => DB::table('lms_quizzes')->whereIn('course_id', $courseIds)->whereNull('deleted_at')->count(),
                'last_activity' => LmsCourse::where('teacher_id', $t->id)->max('updated_at'),
            ];
        });

        // ──────────────────────────────────────────────
        // 3. REKAP KEHADIRAN HARI INI
        // ──────────────────────────────────────────────
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $attendanceSummary = [];
        foreach ($schoolIds as $sid) {
            $school = School::find($sid);

            // Siswa
            $totalStudentTarget = Student::where('school_id', $sid)
                ->whereHas('studentClasses', fn($q) => $q->where('status', 'aktif')
                    ->when($activeAy, fn($sq) => $sq->where('academic_year_id', $activeAy->id)))
                ->count();

            $hadirCount = Attendance::whereDate('date', $today)
                ->whereHas('student', fn($q) => $q->where('school_id', $sid))
                ->whereIn('status', ['hadir', 'terlambat'])
                ->distinct('student_id')
                ->count('student_id');

            // Guru
            $totalTeacherTarget = Teacher::where('school_id', $sid)->where('is_active', true)->count();
            $guruHadir = EmployeeAttendance::where('school_id', $sid)
                ->where('date', $today)
                ->whereIn('status', ['hadir'])
                ->count();

            $attendanceSummary[] = [
                'school' => $school,
                'siswa_total' => $totalStudentTarget,
                'siswa_hadir' => $hadirCount,
                'siswa_pct' => $totalStudentTarget > 0 ? round(($hadirCount / $totalStudentTarget) * 100, 1) : 0,
                'guru_total' => $totalTeacherTarget,
                'guru_hadir' => $guruHadir,
                'guru_pct' => $totalTeacherTarget > 0 ? round(($guruHadir / $totalTeacherTarget) * 100, 1) : 0,
            ];
        }

        // ──────────────────────────────────────────────
        // 4. STATUS RAPORT
        // ──────────────────────────────────────────────
        $raportStats = [];
        foreach ($schoolIds as $sid) {
            $school = School::find($sid);
            $classrooms = Classroom::where('school_id', $sid)
                ->where('academic_year_id', $activeAy?->id)
                ->where('is_active', true)
                ->get();

            $classRaportData = [];
            foreach ($classrooms as $c) {
                $rps = ReportCard::where('classroom_id', $c->id)
                    ->where('semester_id', $activeSemester?->id);

                $draft = (clone $rps)->where('status', 'draft')->count();
                $finalized = (clone $rps)->where('status', 'finalized')->count();
                $published = (clone $rps)->where('status', 'published')->count();
                $total = (clone $rps)->count();

                $classRaportData[] = [
                    'class' => $c,
                    'total' => $total,
                    'draft' => $draft,
                    'finalized' => $finalized,
                    'published' => $published,
                ];
            }

            $raportStats[] = [
                'school' => $school,
                'classes' => $classRaportData,
            ];
        }

        return view('admin.kepsek.dashboard', compact(
            'schoolIds', 'selectedSchoolId', 'activeAy', 'activeSemester',
            'nilaiStats', 'lmsActivity', 'attendanceSummary', 'raportStats',
        ));
    }
}