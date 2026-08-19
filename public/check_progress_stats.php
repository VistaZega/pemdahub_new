<?php
/**
 * Diagnostic Progress Script for School Units
 * Akses via browser: https://perguruanpembda.com/check_progress_stats.php?secret=pembda99
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$SECRET_KEY = 'pembda99';
$isCli = (php_sapi_name() === 'cli');
if (!$isCli && (!isset($_GET['secret']) || $_GET['secret'] !== $SECRET_KEY)) {
    http_response_code(403);
    die(json_encode(['error' => 'Akses ditolak. Token rahasia tidak sesuai.']));
}

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Employee;
use App\Models\Classroom;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\EmployeeAttendance;
use App\Models\LmsCourse;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\ForumThread;
use App\Models\ForumPost;
use App\Models\CbtExam;
use App\Models\CbtQuestionBank;

header('Content-Type: application/json; charset=utf-8');

try {
    $activeYear = AcademicYear::where('is_active', true)->first() 
        ?? AcademicYear::orderBy('year', 'desc')->first();

    $activeSemester = Semester::where('is_active', true)->first()
        ?? Semester::orderBy('id', 'desc')->first();

    $schools = School::orderBy('id')->get();
    $report = [
        'generated_at' => now()->toDateTimeString(),
        'academic_year' => $activeYear ? [
            'id' => $activeYear->id,
            'year' => $activeYear->year,
            'is_active' => $activeYear->is_active
        ] : null,
        'semester' => $activeSemester ? [
            'id' => $activeSemester->id,
            'name' => $activeSemester->name ?? $activeSemester->type ?? 'Semester Aktif',
        ] : null,
        'units' => []
    ];

    $totalCommunityUsers = DB::table('users')->count();

    foreach ($schools as $school) {
        $sid = $school->id;
        $isYayasan = strtolower($school->type) === 'yayasan';

        // 1. Data Siswa
        $totalStudents = 0;
        $activeStudents = 0;
        $assignedStudents = 0;
        try {
            $totalStudents = Student::where('school_id', $sid)->count();
            $activeStudents = Student::where('school_id', $sid)
                ->where(function($q) {
                    $q->where('status', 'aktif')
                      ->orWhere('status', 'active')
                      ->orWhereNull('status');
                })->count();

            if ($activeYear) {
                $assignedStudents = StudentClass::where('academic_year_id', $activeYear->id)
                    ->whereHas('classroom', fn($q) => $q->where('school_id', $sid))
                    ->where(function($q) {
                        $q->where('status', 'aktif')->orWhere('status', 'active');
                    })
                    ->count();
            }
        } catch (\Throwable $e) {}

        // 2. Data Rombel / Kelas
        $totalClassrooms = 0;
        $classroomsWithHomeroom = 0;
        $classroomsList = [];
        try {
            $classrooms = Classroom::where('school_id', $sid)->get();
            $totalClassrooms = $classrooms->count();
            $classroomsWithHomeroom = $classrooms->whereNotNull('homeroom_teacher_id')->count();
            foreach ($classrooms as $c) {
                $studentInClass = StudentClass::where('classroom_id', $c->id)
                    ->where('academic_year_id', $activeYear->id ?? 0)
                    ->where(fn($q) => $q->where('status', 'aktif')->orWhere('status', 'active'))
                    ->count();
                $classroomsList[] = [
                    'id' => $c->id,
                    'name' => $c->class_name,
                    'grade' => $c->grade_level,
                    'has_homeroom' => !empty($c->homeroom_teacher_id),
                    'students_count' => $studentInClass
                ];
            }
        } catch (\Throwable $e) {}

        // 3. Data Guru & Karyawan
        $totalTeachers = 0;
        $totalEmployees = 0;
        try {
            $totalTeachers = Teacher::where('school_id', $sid)->count();
            $totalEmployees = Employee::where('school_id', $sid)->count();
        } catch (\Throwable $e) {}

        // 4. Kurikulum & Jadwal
        $totalSubjects = 0;
        $totalTeachingAssignments = 0;
        $totalSchedules = 0;
        try {
            $totalSubjects = Subject::where('school_id', $sid)->count();
            if ($activeYear) {
                $totalTeachingAssignments = TeachingAssignment::where('academic_year_id', $activeYear->id)
                    ->whereHas('classroom', fn($q) => $q->where('school_id', $sid))
                    ->count();

                $totalSchedules = Schedule::where('school_id', $sid)
                    ->where('academic_year_id', $activeYear->id)
                    ->count();
            }
        } catch (\Throwable $e) {}

        // 5. Keuangan
        $totalBills = 0;
        $paidBills = 0;
        $unpaidBills = 0;
        $totalBillAmount = 0;
        $totalPaidAmount = 0;
        try {
            $billsQuery = StudentBill::whereHas('student', fn($q) => $q->where('school_id', $sid));
            if ($activeYear) {
                $billsQuery->where('academic_year_id', $activeYear->id);
            }
            $totalBills = (clone $billsQuery)->count();
            $paidBills = (clone $billsQuery)->where('status', 'lunas')->count();
            $unpaidBills = (clone $billsQuery)->whereIn('status', ['belum_bayar', 'cicilan'])->count();
            $totalBillAmount = (clone $billsQuery)->sum('amount');
            $totalPaidAmount = (clone $billsQuery)->sum('paid_amount');
        } catch (\Throwable $e) {}

        // 6. Absensi
        $studentAttendances = 0;
        $employeeAttendances = 0;
        try {
            $studentAttendances = Attendance::whereHas('student', fn($q) => $q->where('school_id', $sid))->count();
        } catch (\Throwable $e) {}
        try {
            $employeeAttendances = EmployeeAttendance::whereHas('employee', fn($q) => $q->where('school_id', $sid))->count();
        } catch (\Throwable $e) {}

        // 7. LMS
        $lmsCoursesCount = 0;
        $lmsMaterialsCount = 0;
        $lmsAssignmentsCount = 0;
        $lmsSubmissionsCount = 0;
        $topLmsCourses = [];
        try {
            $courses = LmsCourse::where('school_id', $sid)->withCount(['materials', 'assignments'])->get();
            $lmsCoursesCount = $courses->count();
            $lmsMaterialsCount = $courses->sum('materials_count');
            $lmsAssignmentsCount = $courses->sum('assignments_count');
            
            $courseIds = $courses->pluck('id')->toArray();
            if (!empty($courseIds)) {
                $assignIds = LmsAssignment::whereIn('course_id', $courseIds)->pluck('id')->toArray();
                if (!empty($assignIds)) {
                    $lmsSubmissionsCount = LmsSubmission::whereIn('assignment_id', $assignIds)->count();
                }
            }

            foreach ($courses as $c) {
                if ($c->materials_count > 0 || $c->assignments_count > 0) {
                    $topLmsCourses[] = [
                        'title' => $c->title,
                        'materials' => $c->materials_count,
                        'assignments' => $c->assignments_count,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 8. Pembda Space
        $forumThreads = 0;
        $forumPosts = 0;
        try {
            $forumThreads = ForumThread::whereHas('user', fn($q) => $q->where('school_id', $sid))->count();
            $forumPosts = ForumPost::whereHas('user', fn($q) => $q->where('school_id', $sid))->count();
        } catch (\Throwable $e) {}

        // 9. CBT
        $cbtExamsCount = 0;
        $cbtQuestionBanksCount = 0;
        try {
            $cbtExamsCount = CbtExam::where('school_id', $sid)->count();
            $cbtQuestionBanksCount = CbtQuestionBank::where('school_id', $sid)->count();
        } catch (\Throwable $e) {}

        $report['units'][] = [
            'id' => $sid,
            'name' => $school->name,
            'type' => $school->type,
            'is_yayasan' => $isYayasan,
            'stats' => [
                'students' => [
                    'total' => $totalStudents,
                    'active' => $activeStudents,
                    'in_rombel' => $assignedStudents,
                    'rombel_percentage' => $totalStudents > 0 ? round(($assignedStudents / $totalStudents) * 100, 1) : 0,
                ],
                'classrooms' => [
                    'total' => $totalClassrooms,
                    'with_homeroom' => $classroomsWithHomeroom,
                    'homeroom_percentage' => $totalClassrooms > 0 ? round(($classroomsWithHomeroom / $totalClassrooms) * 100, 1) : 0,
                    'list' => $classroomsList
                ],
                'teachers_and_staff' => [
                    'teachers' => $totalTeachers,
                    'employees' => $totalEmployees,
                    'teaching_assignments' => $totalTeachingAssignments,
                ],
                'curriculum' => [
                    'subjects' => $totalSubjects,
                    'schedules' => $totalSchedules,
                ],
                'finance' => [
                    'total_bills' => $totalBills,
                    'paid_bills' => $paidBills,
                    'unpaid_bills' => $unpaidBills,
                    'total_bill_amount' => $totalBillAmount,
                    'total_paid_amount' => $totalPaidAmount,
                    'collection_percentage' => $totalBillAmount > 0 ? round(($totalPaidAmount / $totalBillAmount) * 100, 1) : 0,
                ],
                'attendance' => [
                    'student_records' => $studentAttendances,
                    'employee_records' => $employeeAttendances,
                ],
                'lms' => [
                    'courses' => $lmsCoursesCount,
                    'materials' => $lmsMaterialsCount,
                    'assignments' => $lmsAssignmentsCount,
                    'submissions' => $lmsSubmissionsCount,
                    'active_courses' => $topLmsCourses,
                ],
                'space' => [
                    'threads' => $forumThreads,
                    'replies' => $forumPosts,
                ],
                'cbt' => [
                    'exams' => $cbtExamsCount,
                    'question_banks' => $cbtQuestionBanksCount,
                ]
            ]
        ];
    }

    $report['global_summary'] = [
        'total_users' => $totalCommunityUsers,
        'total_students' => Student::count(),
        'total_teachers' => Teacher::count(),
        'total_employees' => Employee::count(),
        'total_threads' => class_exists(ForumThread::class) ? ForumThread::count() : 0,
        'total_posts' => class_exists(ForumPost::class) ? ForumPost::count() : 0,
    ];

    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT);
}
