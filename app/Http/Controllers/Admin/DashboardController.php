<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $isKepsek = $user->isKepalaSekolah();
        $schoolId = $isSuperAdmin ? null : $user->school_id;

        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first() 
            ?? AcademicYear::orderBy('year', 'desc')->first();
        
        $currentSemester = \App\Models\Semester::where('is_active', true)
            ->where('academic_year_id', $currentAcademicYear?->id ?? 0)
            ->first();

        // School info for admin_sekolah
        $school = null;
        if (!$isSuperAdmin && $schoolId) {
            $school = School::find($schoolId);
        }

        // 📊 Basic Statistics: Siswa Aktif yang SUDAH DI-PLOT / MASUK KE ROMBEL pada TP Aktif
        $activeStudentsInRombel = \App\Models\StudentClass::whereHas('student', function ($q) use ($schoolId) {
                $q->where('status', 'aktif');
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })
            ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            })
            ->distinct('student_id')
            ->count('student_id');

        $totalStudents = $activeStudentsInRombel;
        $activeStudents = $activeStudentsInRombel;
        $totalTeachers = Teacher::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->count();
        $totalClassrooms = Classroom::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when($currentAcademicYear, fn($q) => $q->where('academic_year_id', $currentAcademicYear->id))
            ->count();
        $totalSchools = School::schoolsOnly()->count();
        $totalEmployees = Employee::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->count();

        // Financial section removed

        // 📝 LMS & CBT Activity
        $totalCbtExams = \App\Models\CbtExam::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->count();
        $activeLmsCourses = \App\Models\LmsCourse::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('status', 'active')->count();

        // 📈 Data Tambahan (PSB, Keuangan, BK)
        $totalApplicants = \App\Models\Applicant::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->whereNotIn('status', ['draft', 'withdrawn'])
            ->count();
        $pendingApplicants = \App\Models\Applicant::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->whereIn('status', ['submitted', 'payment_verified', 'document_verified'])
            ->count();

        $totalBillsCount = \App\Models\StudentBill::when($schoolId, fn($q) => $q->whereHas('student', fn($s) => $s->where('school_id', $schoolId)))->count();
        $paidBillsCount = \App\Models\StudentBill::when($schoolId, fn($q) => $q->whereHas('student', fn($s) => $s->where('school_id', $schoolId)))->where('status', 'lunas')->count();
        $billPaidPercentage = $totalBillsCount > 0 ? round(($paidBillsCount / $totalBillsCount) * 100) : 0;

        $activeCounselings = \App\Models\StudentCounselingRecord::when($schoolId, fn($q) => $q->whereHas('student', fn($s) => $s->where('school_id', $schoolId)))
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        
        // 📊 Attendance Statistics
        $todayAttendances = \App\Models\Attendance::whereDate('date', Carbon::today())
            ->when($schoolId, fn($q) => $q->whereHas('student', fn($s) => $s->where('school_id', $schoolId)))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $statisticsService = new \App\Services\AttendanceStatisticsService();
        $startDateOfYear = $currentAcademicYear ? $currentAcademicYear->start_date : Carbon::now()->startOfYear();
        $totalZ = $statisticsService->calculateZ($startDateOfYear, Carbon::today(), null, $schoolId);
        
        $totalHadirCumulative = \App\Models\Attendance::whereBetween('date', [$startDateOfYear->format('Y-m-d'), Carbon::today()->format('Y-m-d')])
            ->when($schoolId, fn($q) => $q->whereHas('student', fn($s) => $s->where('school_id', $schoolId)))
            ->where('status', 'hadir')
            ->count();
        
        $cumulativeRate = ($totalZ > 0 && $activeStudents > 0)
            ? round(($totalHadirCumulative / ($activeStudents * $totalZ)) * 100, 1)
            : 0;

        // Today's Employee (Guru & Staf) Attendance Statistics
        $todayEmpAtt = \App\Models\EmployeeAttendance::whereDate('date', Carbon::today())
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->get();

        $activeEmployeesList = Employee::where('is_active', true)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->get();

        $activeTeachersCount = $activeEmployeesList->filter(fn($e) => $e->isTeacher())->count();
        $activeStaffCount = $activeEmployeesList->filter(fn($e) => !$e->isTeacher())->count();

        $teacherAttToday = $todayEmpAtt->filter(fn($att) => $att->employee?->isTeacher());
        $teachersHadir = $teacherAttToday->where('status', 'hadir')->where('notes', '!=', 'tugas_khusus')->count();
        $teachersTugasKhusus = $teacherAttToday->where('status', 'hadir')->where('notes', 'tugas_khusus')->count();
        $teachersSakit = $teacherAttToday->where('status', 'sakit')->count();
        $teachersIzin = $teacherAttToday->where('status', 'izin')->count();
        $teachersAlpha = $teacherAttToday->where('status', 'alpha')->count();

        $staffAttToday = $todayEmpAtt->filter(fn($att) => !$att->employee?->isTeacher());
        $staffHadir = $staffAttToday->where('status', 'hadir')->where('notes', '!=', 'tugas_khusus')->count();
        $staffTugasKhusus = $staffAttToday->where('status', 'hadir')->where('notes', 'tugas_khusus')->count();
        $staffSakit = $staffAttToday->where('status', 'sakit')->count();
        $staffIzin = $staffAttToday->where('status', 'izin')->count();
        $staffAlpha = $staffAttToday->where('status', 'alpha')->count();
        
        $staffLate = 0;
        foreach ($staffAttToday as $att) {
            if ($att->isLate()) {
                $staffLate++;
            }
        }


        // Distribution Data (Filtered for Active Students in Rombel for current Academic Year)
        $studentsByStatus = Student::whereHas('studentClasses', function ($q) use ($currentAcademicYear) {
                if ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                }
            })
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        
        $studentsByYear = Student::whereHas('studentClasses', function ($q) use ($currentAcademicYear) {
                if ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                }
            })
            ->where('status', 'aktif')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('entry_year', '>=', Carbon::now()->year - 4)
            ->select('entry_year', DB::raw('COUNT(*) as count'))
            ->groupBy('entry_year')
            ->orderBy('entry_year')
            ->get();

        $recentStudents = Student::whereHas('studentClasses', function ($q) use ($currentAcademicYear) {
                if ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                }
            })
            ->where('status', 'aktif')
            ->with(['school', 'currentClassroom'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $schools = School::where('is_active', true)->schoolsOnly()->get();

        // SuperAdmin specific per school breakdown
        $studentsBySchool = $isSuperAdmin 
            ? $schools->map(function ($sch) use ($currentAcademicYear) {
                $count = \App\Models\StudentClass::whereHas('student', function ($q) use ($sch) {
                        $q->where('school_id', $sch->id)->where('status', 'aktif');
                    })
                    ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                        $q->where('academic_year_id', $currentAcademicYear->id);
                    })
                    ->distinct('student_id')
                    ->count('student_id');

                return [
                    'school_id' => $sch->id,
                    'school' => [
                        'name' => $sch->name,
                    ],
                    'count' => $count,
                ];
            })
            : collect();

        // Admin Sekolah specific progress
        $classDistribution = collect();
        if (!$isSuperAdmin && $schoolId) {
            $classDistribution = Classroom::where('school_id', $schoolId)
                ->when($currentAcademicYear, fn($q) => $q->where('academic_year_id', $currentAcademicYear->id))
                ->withCount(['students as students_count' => function($q) use ($currentAcademicYear) {
                    if ($currentAcademicYear) {
                        $q->where('student_classes.academic_year_id', $currentAcademicYear->id);
                    }
                }])
                ->get();
        }

        return view('admin.dashboard', compact(
            'isSuperAdmin', 'isKepsek', 'school', 'totalStudents', 'activeStudents', 'totalTeachers', 
            'totalClassrooms', 'totalSchools', 'totalEmployees', 'totalCbtExams', 'activeLmsCourses', 
            'todayAttendances', 'studentsByStatus', 'studentsByYear', 'recentStudents',
            'currentAcademicYear', 'currentSemester', 'schools', 'studentsBySchool',
            'cumulativeRate', 'classDistribution', 'activeTeachersCount', 'activeStaffCount',
            'teachersHadir', 'teachersTugasKhusus', 'teachersSakit', 'teachersIzin', 'teachersAlpha',
            'staffHadir', 'staffTugasKhusus', 'staffSakit', 'staffIzin', 'staffAlpha', 'staffLate',
            'totalApplicants', 'pendingApplicants', 'totalBillsCount', 'paidBillsCount', 'billPaidPercentage', 'activeCounselings'
        ));
    }
}
