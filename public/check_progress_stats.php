<?php
/**
 * Diagnostic Progress Script for School Units
 * Akses via browser: https://perguruanpembda.com/check_progress_stats.php?secret=pembda99
 */

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

    $schools = School::orderBy('id')->get();
    $report = [
        'generated_at' => now()->toDateTimeString(),
        'academic_year' => $activeYear ? [
            'id' => $activeYear->id,
            'year' => $activeYear->year,
            'is_active' => $activeYear->is_active
        ] : null,
        'units' => []
    ];

    $totalCommunityUsers = DB::table('users')->count();

    foreach ($schools as $school) {
        $sid = $school->id;
        $isYayasan = strtolower($school->type) === 'yayasan';

        // 1. Data Siswa
        $totalStudents = Student::where('school_id', $sid)->count();
        $activeStudents = Student::where('school_id', $sid)->where('is_active', true)->count();
        
        $assignedStudents = 0;
        if ($activeYear) {
            $assignedStudents = StudentClass::where('academic_year_id', $activeYear->id)
                ->whereHas('classroom', function($q) use ($sid) {
                    $q->where('school_id', $sid);
                })
                ->where('status', 'aktif')
                ->count();
        }

        // 2. Data Rombel / Kelas
        $totalClassrooms = Classroom::where('school_id', $sid)->count();
        $classroomsWithHomeroom = Classroom::where('school_id', $sid)
            ->whereNotNull('homeroom_teacher_id')
            ->count();

        // 3. Data Guru & Karyawan
        $totalTeachers = Teacher::where('school_id', $sid)->count();
        $totalEmployees = Employee::where('school_id', $sid)->count();
        
        // 4. Kurikulum & Jadwal
        $totalSubjects = Subject::where('school_id', $sid)->count();
        $totalTeachingAssignments = 0;
        $totalSchedules = 0;
        if ($activeYear) {
            $totalTeachingAssignments = TeachingAssignment::where('academic_year_id', $activeYear->id)
                ->whereHas('classroom', function($q) use ($sid) {
                    $q->where('school_id', $sid);
                })
                ->count();

            $totalSchedules = Schedule::where('school_id', $sid)
                ->where('academic_year_id', $activeYear->id)
                ->count();
        }

        // 5. Keuangan
        $totalBills = 0;
        $paidBills = 0;
        $unpaidBills = 0;
        $totalBillAmount = 0;
        $totalPaidAmount = 0;
        
        if (class_exists(StudentBill::class)) {
            $billsQuery = StudentBill::whereHas('student', function($q) use ($sid) {
                $q->where('school_id', $sid);
            });
            if ($activeYear) {
                $billsQuery->where('academic_year_id', $activeYear->id);
            }
            $totalBills = (clone $billsQuery)->count();
            $paidBills = (clone $billsQuery)->whereIn('status', ['paid', 'lunas'])->count();
            $unpaidBills = (clone $billsQuery)->whereIn('status', ['unpaid', 'belum_lunas', 'partial'])->count();
            $totalBillAmount = (clone $billsQuery)->sum('amount');
            $totalPaidAmount = (clone $billsQuery)->sum('paid_amount');
        }

        // 6. Absensi
        $studentAttendances = 0;
        $employeeAttendances = 0;
        if (class_exists(Attendance::class)) {
            $studentAttendances = Attendance::whereHas('student', function($q) use ($sid) {
                $q->where('school_id', $sid);
            })->count();
        }
        if (class_exists(EmployeeAttendance::class)) {
            $employeeAttendances = EmployeeAttendance::whereHas('employee', function($q) use ($sid) {
                $q->where('school_id', $sid);
            })->count();
        }

        // 7. LMS
        $lmsCoursesCount = 0;
        $lmsMaterialsCount = 0;
        $lmsAssignmentsCount = 0;
        $lmsSubmissionsCount = 0;
        if (class_exists(LmsCourse::class)) {
            $courses = LmsCourse::where('school_id', $sid)->get();
            $lmsCoursesCount = $courses->count();
            $courseIds = $courses->pluck('id')->toArray();
            
            if (!empty($courseIds) && class_exists(LmsMaterial::class)) {
                $lmsMaterialsCount = LmsMaterial::whereIn('course_id', $courseIds)->count();
            }
            if (!empty($courseIds) && class_exists(LmsAssignment::class)) {
                $assignments = LmsAssignment::whereIn('course_id', $courseIds)->get();
                $lmsAssignmentsCount = $assignments->count();
                $assignIds = $assignments->pluck('id')->toArray();
                if (!empty($assignIds) && class_exists(LmsSubmission::class)) {
                    $lmsSubmissionsCount = LmsSubmission::whereIn('assignment_id', $assignIds)->count();
                }
            }
        }

        // 8. Pembda Space / Forum Partisipasi
        $forumThreads = 0;
        $forumPosts = 0;
        if (class_exists(ForumThread::class)) {
            $forumThreads = ForumThread::whereHas('user', function($q) use ($sid) {
                $q->where('school_id', $sid);
            })->count();
        }
        if (class_exists(ForumPost::class)) {
            $forumPosts = ForumPost::whereHas('user', function($q) use ($sid) {
                $q->where('school_id', $sid);
            })->count();
        }

        // 9. CBT
        $cbtExamsCount = 0;
        $cbtQuestionBanksCount = 0;
        if (class_exists(CbtExam::class)) {
            $cbtExamsCount = CbtExam::where('school_id', $sid)->count();
        }
        if (class_exists(CbtQuestionBank::class)) {
            $cbtQuestionBanksCount = CbtQuestionBank::where('school_id', $sid)->count();
        }

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

    // Space Total across all units
    $report['global_space'] = [
        'total_users' => $totalCommunityUsers,
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
