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
use App\Models\LmsSubmission;
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
        $classroom = null;
        $studentProgress = [
            'overall' => 0,
            'attendance_rate' => 100,
            'task_rate' => 100,
            'total_assignments' => 0,
            'submitted_assignments' => 0,
            'caption' => 'Semangat terus dalam belajar dan pertahankan kehadiranmu! 🌟',
        ];

        // Guru specific flags & stats
        $hasPklBimbingan = false;
        $hasProjectBimbingan = false;
        $hasProjectUjian = false;
        $isPanitiaPkl = false;
        $isPanitiaProyek = false;
        $teacherProgress = [
            'overall' => 100,
            'attendance_rate' => 100,
            'classes_today' => 0,
            'pending_assignments' => 0,
            'caption' => 'Dedikasi Anda sangat luar biasa dalam membimbing siswa Pembda! 👨‍🏫🌟',
        ];
        $teacherAttendanceStats = ['hadir' => 0, 'terlambat' => 0, 'sakit_izin' => 0, 'total_jadwal' => 0];

        if ($activeRole === 'siswa') {
            $student = Student::where('user_id', $user->id)->with(['school', 'user.reputation'])->first();
            
            if ($student) {
                // Determine school type & grade level for PKL & Final Project
                $schoolType = strtoupper($student->school->type ?? '');
                $classroom = $student->currentClassroom()->first();
                $gradeLevel = $classroom?->grade_level ?? $student->grade_level;
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

                // Kalkulasi Real Kehadiran
                $totalMonthAttendance = $attendances->count();
                $presentMonthCount = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();

                if ($totalMonthAttendance > 0) {
                    $attendanceRate = round(($presentMonthCount / $totalMonthAttendance) * 100);
                } else {
                    $totalAllAttendance = Attendance::where('student_id', $student->id)->count();
                    $presentAllCount = Attendance::where('student_id', $student->id)->whereIn('status', ['hadir', 'terlambat'])->count();
                    $attendanceRate = $totalAllAttendance > 0 ? round(($presentAllCount / $totalAllAttendance) * 100) : 100;
                }

                // LMS courses & assignments progress
                $totalAssignments = 0;
                $submittedAssignments = 0;
                $taskRate = 100;

                if ($classroom) {
                    $courseIds = LmsCourse::where(function ($q) use ($classroom) {
                        $q->where('classroom_id', $classroom->id)
                          ->orWhereHas('classes', fn($cq) => $cq->where('classroom_id', $classroom->id));
                    })->where('is_published', true)->pluck('id');

                    $assignmentIds = LmsAssignment::whereIn('course_id', $courseIds)->where('is_published', true)->pluck('id');
                    $totalAssignments = $assignmentIds->count();

                    if ($totalAssignments > 0) {
                        $submittedAssignments = LmsSubmission::where('student_id', $student->id)
                            ->whereIn('assignment_id', $assignmentIds)
                            ->whereIn('status', ['submitted', 'graded'])
                            ->count();
                        $taskRate = round(($submittedAssignments / $totalAssignments) * 100);
                    }
                }

                // Kalkulasi Real Gabungan (50% Absensi + 50% Tugas LMS)
                if ($totalAssignments > 0 && ($totalMonthAttendance > 0 || Attendance::where('student_id', $student->id)->exists())) {
                    $overallProgress = (int) round(($attendanceRate * 0.5) + ($taskRate * 0.5));
                } elseif ($totalAssignments > 0) {
                    $overallProgress = (int) $taskRate;
                } else {
                    $overallProgress = (int) $attendanceRate;
                }
                $overallProgress = min(100, max(0, $overallProgress));

                // Pesan Motivasi Kontekstual
                if ($overallProgress >= 90) {
                    $caption = 'Luar biasa! Progres belajar dan kehadiranmu sangat optimal minggu ini! 🌟';
                } elseif ($overallProgress >= 75) {
                    $caption = 'Semangat terus! Kamu sudah menyelesaikan sebagian besar target belajar dan absensimu! 🚀';
                } elseif ($overallProgress >= 50) {
                    $caption = 'Bagus! Terus tingkatkan kehadiran dan lengkapi tugas-tugas yang belum dikumpulkan! 💪';
                } else {
                    $caption = 'Ayo tingkatkan keaktifan! Pastikan hadir tepat waktu dan segera kumpulkan tugasmu! ⚡';
                }

                $studentProgress = [
                    'overall' => $overallProgress,
                    'attendance_rate' => $attendanceRate,
                    'task_rate' => $taskRate,
                    'total_assignments' => $totalAssignments,
                    'submitted_assignments' => $submittedAssignments,
                    'caption' => $caption,
                ];

                // LMS courses for student
                $activeCourses = LmsCourse::whereHas('classes', function ($q) use ($classroom) {
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

            $teacherProgress = [
                'overall' => 100,
                'attendance_rate' => 100,
                'classes_today' => 0,
                'pending_assignments' => 0,
                'caption' => 'Dedikasi Anda sangat luar biasa dalam membimbing siswa Pembda! 👨‍🏫🌟',
            ];
            $teacherAttendanceStats = ['hadir' => 0, 'terlambat' => 0, 'sakit_izin' => 0, 'total_jadwal' => 0];

            $teacherId = $teacher?->id;
            if ($teacherId) {
                $hasPklBimbingan = PklPlacement::where('teacher_id', $teacherId)->exists();
                $hasProjectBimbingan = FinalProject::where('advisor_id', $teacherId)->exists();
                $hasProjectUjian = FinalProject::where('examiner_id', $teacherId)->exists();

                // Teacher Attendance Rate
                $employee = \App\Models\Employee::where('user_id', $user->id)->first();
                $attRate = 100;
                $hadirCount = 0;
                $lateCount = 0;
                $izinCount = 0;

                if ($employee) {
                    $empAtts = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                        ->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)
                        ->get();
                    $hadirCount = $empAtts->whereIn('status', ['present', 'hadir'])->count();
                    $lateCount = $empAtts->whereIn('status', ['late', 'terlambat'])->count();
                    $izinCount = $empAtts->whereIn('status', ['leave', 'sick', 'izin', 'sakit'])->count();
                    $totalAtt = $empAtts->count();
                    $attRate = $totalAtt > 0 ? round((($hadirCount + $lateCount) / $totalAtt) * 100) : 100;
                }

                // Teacher Classes Today
                $todayDay = strtolower(now()->format('l'));
                $classesToday = \App\Models\Schedule::where(function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId)
                      ->orWhereHas('teachingAssignment', fn($tq) => $tq->where('teacher_id', $teacherId));
                })->where('day_of_week', $todayDay)->count();

                // Pending assignments to grade
                $teacherCourseIds = LmsCourse::where('teacher_id', $teacherId)->pluck('id');
                $teacherAssignIds = LmsAssignment::whereIn('course_id', $teacherCourseIds)->pluck('id');
                $pendingAssignments = LmsSubmission::whereIn('assignment_id', $teacherAssignIds)->where('status', 'submitted')->count();

                // Teacher overall score
                $teacherOverall = min(100, max(0, (int) round(($attRate * 0.7) + (max(0, 100 - ($pendingAssignments * 10)) * 0.3))));

                if ($pendingAssignments > 0) {
                    $tCaption = "Ada {$pendingAssignments} tugas siswa yang siap diperiksa & diberi penilaian! 📝";
                } elseif ($classesToday > 0) {
                    $tCaption = "Anda memiliki {$classesToday} sesi jadwal mengajar hari ini. Selamat mengajar! 🚀";
                } else {
                    $tCaption = "Aktivitas mengajar & presensi Anda bulan ini terpantau sangat baik! 🌟";
                }

                $teacherProgress = [
                    'overall' => $teacherOverall,
                    'attendance_rate' => $attRate,
                    'classes_today' => $classesToday,
                    'pending_assignments' => $pendingAssignments,
                    'caption' => $tCaption,
                ];

                $teacherAttendanceStats = [
                    'hadir' => $hadirCount,
                    'terlambat' => $lateCount,
                    'sakit_izin' => $izinCount,
                    'total_jadwal' => $classesToday,
                ];
            }

            $isPanitiaPkl = $user->isPanitiaPkl() || $user->isSuperAdmin() || $user->isAdminSekolah() || $user->isKepalaSekolah();
            $isPanitiaProyek = $user->isPanitiaProyek() || $user->isSuperAdmin() || $user->isAdminSekolah() || $user->isKepalaSekolah();

            $activeCourses = LmsCourse::where('teacher_id', $teacher->id ?? 0)->take(3)->get();
        }

        // Pembda Space Engine Data: Kanal & Squad Stories Bar
        $spaceGroups = \App\Models\ForumGroup::whereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->with(['latestThread'])
            ->take(6)
            ->get();

        if ($spaceGroups->isEmpty()) {
            $spaceGroups = \App\Models\ForumGroup::take(6)->get();
        }

        // Active Poll Thread (Poling Resmi PembdaHUB oleh Super Admin dengan 5 Pilihan)
        $activePollThread = ForumThread::where('title', 'LIKE', '%Penerapan PembdaHUB%')
            ->whereHas('poll')
            ->with(['poll.options', 'user', 'group'])
            ->first();

        if (!$activePollThread) {
            $superAdmin = \App\Models\User::where('role', 'superadmin')->first() 
                ?? \App\Models\User::where('name', 'LIKE', '%Admin%')->first() 
                ?? $user;

            $lobiGroup = \App\Models\ForumGroup::where('slug', 'lobi-utama')->first() ?? \App\Models\ForumGroup::first();

            $activePollThread = ForumThread::create([
                'user_id' => $superAdmin->id,
                'group_id' => $lobiGroup?->id,
                'category' => 'pengumuman',
                'title' => 'Bagaimana Pendapat Kamu tentang Penerapan PembdaHUB Mobile?',
                'content' => "Halo Warga YAYASAN PEMBDA! Bagaimana kesan & pendapat kalian mengenai penggunaan aplikasi PembdaHUB Mobile saat ini? Yuk berikan suaramu!\n\n📱 Belum install aplikasi di HP? Download & install aplikasi PembdaHUB Mobile resmi via: " . route('app.download'),
            ]);

            $poll = \App\Models\ForumPoll::create([
                'forum_thread_id' => $activePollThread->id,
                'question' => 'Bagaimana Pendapat Kamu tentang Penerapan PembdaHUB Mobile?',
            ]);

            $options = [
                '🚀 Sangat Bagus, Canggih & Membantu',
                '👍 Cukup Baik & Sangat Praktis',
                '💡 Fitur Lengkap, Perlu Sosialisasi',
                '⭐ Menarik, Ingin Ditambah Fitur Baru',
                '🛠️ Perlu Peningkatan & Optimalisasi',
            ];

            foreach ($options as $optText) {
                \App\Models\ForumPollOption::create([
                    'forum_poll_id' => $poll->id,
                    'option_text' => $optText,
                    'votes_count' => 0,
                ]);
            }

            $activePollThread->load(['poll.options', 'user', 'group']);
        } else if ($activePollThread->poll) {
            // Pastikan ada 5 pilihan jika belum ada 5
            $currentOptionsCount = $activePollThread->poll->options->count();
            if ($currentOptionsCount < 5) {
                $missingOptions = [
                    '🚀 Sangat Bagus, Canggih & Membantu',
                    '👍 Cukup Baik & Sangat Praktis',
                    '💡 Fitur Lengkap, Perlu Sosialisasi',
                    '⭐ Menarik, Ingin Ditambah Fitur Baru',
                    '🛠️ Perlu Peningkatan & Optimalisasi',
                ];

                \App\Models\ForumPollOption::where('forum_poll_id', $activePollThread->poll->id)->delete();
                foreach ($missingOptions as $optText) {
                    \App\Models\ForumPollOption::create([
                        'forum_poll_id' => $activePollThread->poll->id,
                        'option_text' => $optText,
                        'votes_count' => 0,
                    ]);
                }
                $activePollThread->load(['poll.options']);
            }

            // Sync votes_count dengan record murni di forum_poll_votes
            foreach ($activePollThread->poll->options as $opt) {
                $realVoteCount = \App\Models\ForumPollVote::where('forum_poll_option_id', $opt->id)->count();
                if ($opt->votes_count !== $realVoteCount) {
                    $opt->votes_count = $realVoteCount;
                    \App\Models\ForumPollOption::where('id', $opt->id)->update(['votes_count' => $realVoteCount]);
                }
            }
        }

        // Recent Forum discussions (Pembda Space Terbaru)
        $recentDiscussions = ForumThread::with(['user.student', 'user.teacher', 'group', 'likes'])
            ->withCount(['replies', 'likes'])
            ->latest()
            ->take(5)
            ->get();

        // Popular Forum discussions (Pembda Space Paling Rame)
        $popularDiscussions = ForumThread::with(['user.student', 'user.teacher', 'group', 'likes'])
            ->withCount(['replies', 'likes'])
            ->orderByDesc('replies_count')
            ->latest()
            ->take(5)
            ->get();

        return view('mobile.dashboard', compact(
            'user',
            'student',
            'teacher',
            'classroom',
            'attendanceStats',
            'studentProgress',
            'teacherProgress',
            'teacherAttendanceStats',
            'recentDiscussions',
            'popularDiscussions',
            'spaceGroups',
            'activePollThread',
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
