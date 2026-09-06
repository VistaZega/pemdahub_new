<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\GradeWeight;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\AttendanceStatisticsService;

class DashboardController extends Controller
{
    /**
     * Get the authenticated student record.
     */
    private function getStudent()
    {
        $user = Auth::user();
        return Student::where('user_id', $user->id)->firstOrFail();
    }

    /**
     * Get current classroom for the student.
     */
    private function getCurrentClassroom(Student $student)
    {
        return $student->currentClassroom()->first();
    }

    /**
     * Dashboard Siswa - Ringkasan utama
     */
    public function index()
    {
        $student = $this->getStudent();
        $student->load('school');
        $activeYear = Cache::remember('active_academic_year', 3600, fn() => AcademicYear::where('is_active', true)->first());
        $activeSemester = Cache::remember('active_semester', 3600, fn() => Semester::where('is_active', true)->first());
        $classroom = $this->getCurrentClassroom($student);

        // Rata-rata nilai semester ini
        $avgScore = 0;
        if ($activeSemester) {
            $avgScore = Grade::where('student_id', $student->id)
                ->where('semester_id', $activeSemester->id)
                ->avg('score') ?? 0;
        }

        // Kehadiran semester ini
        $attendanceData = ['total' => 0, 'present' => 0, 'percentage' => 0, 'z_days' => 0];
        if ($activeYear && $classroom) {
            $effectiveStartDate = $activeYear->start_date->gt(now()) ? now() : $activeYear->start_date;
            $statsService = app(AttendanceStatisticsService::class);
            $z = $statsService->calculateZ($effectiveStartDate->format('Y-m-d'), date('Y-m-d'), $classroom->id);
            
            $dayOfWeekQuery = DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%w', date) NOT IN ('0', '6')"
                : "DAYOFWEEK(attendances.date) NOT IN (1, 7)";

            $presentCount = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$effectiveStartDate->format('Y-m-d'), date('Y-m-d')])
                ->whereIn('status', ['hadir', 'terlambat'])
                ->whereRaw($dayOfWeekQuery)
                ->select(DB::raw('COUNT(DISTINCT date) as count'))
                ->value('count');

            $percentage = $z > 0 ? ($presentCount / $z) * 100 : 0;
            $attendanceData = [
                'total' => $z,
                'present' => $presentCount,
                'percentage' => round(min(100, $percentage), 1),
                'z_days' => $z
            ];
        }

        // Riwayat Kehadiran Terakhir (10 record terakhir)
        $attendanceHistory = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        // Tagihan dan Kemajuan Pembayaran
        $totalOutstanding = 0;
        $studentBillingStats = null;
        
        if ($activeYear) {
            $billsQuery = StudentBill::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id);
            
            $allBills = (clone $billsQuery)->get();
            $totalAmount = $allBills->sum('amount');
            $totalPaidAmount = $allBills->sum('paid_amount');
            
            // Helper to resolve accurate bill year even if year column in DB is 0, null, or corrupted (e.g. 2001)
            $getResolvedYear = function($b) use ($activeYear) {
                $yr = (int)$b->year;
                if ($yr >= 2024 && $yr <= 2035) {
                    return $yr;
                }
                if (!empty($b->due_date)) {
                    $parsedYear = (int)\Carbon\Carbon::parse($b->due_date)->format('Y');
                    if ($parsedYear >= 2024 && $parsedYear <= 2035) {
                        return $parsedYear;
                    }
                }
                $ayName = $b->academicYear?->year ?? $activeYear?->year ?? '2026/2027';
                if (preg_match('/(20\d{2})/', $ayName, $m)) {
                    $startYear = (int)$m[1];
                    $mNum = (int)$b->month;
                    return ($mNum >= 7 && $mNum <= 12) ? $startYear : $startYear + 1;
                }
                return (int)date('Y');
            };

            $getDueDate = function($b) use ($getResolvedYear) {
                if ($b->due_date) {
                    return \Carbon\Carbon::parse($b->due_date)->endOfDay();
                }
                if ($b->month) {
                    $year = $getResolvedYear($b);
                    return \Carbon\Carbon::create($year, (int)$b->month, 10)->endOfDay();
                }
                return null;
            };

            // Logika Tunggakan yang Tepat: Hanya tagihan yang lewat jatuh tempo / s.d. bulan berkenaan yang belum dibayar
            $tunggakanAmount = $allBills->filter(function($b) use ($getDueDate) {
                if ($b->status === 'lunas') return false;
                if ($b->isOverdue()) return true;
                $dueDate = $getDueDate($b);
                return $dueDate ? now()->isAfter($dueDate) : false;
            })->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

            $upcomingAmount = $allBills->filter(function($b) use ($getDueDate) {
                if ($b->status === 'lunas') return false;
                if ($b->isOverdue()) return false;
                $dueDate = $getDueDate($b);
                return $dueDate ? !now()->isAfter($dueDate) : true;
            })->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

            $totalOutstanding = $tunggakanAmount;
            
            $studentBillingStats = [
                'total_bills' => $allBills->count(),
                'paid_bills' => $allBills->where('status', 'lunas')->count(),
                'total_amount' => $totalAmount,
                'paid_amount' => $totalPaidAmount,
                'outstanding' => $tunggakanAmount,
                'upcoming' => $upcomingAmount,
                'percentage' => $totalAmount > 0 ? round(($totalPaidAmount / $totalAmount) * 100, 1) : 0,
            ];
        }

        // Jadwal hari ini - Timeline logic like Guru Dashboard
        $todaySchedules = collect();
        $groupedTodaySchedules = collect();
        $currentTime = now()->format('H:i');
        $currentSchedule = null;
        $nextSchedule = null;

        if ($classroom) {
            $dayMap = [
                'Monday' => 'monday', 'Tuesday' => 'tuesday', 'Wednesday' => 'wednesday',
                'Thursday' => 'thursday', 'Friday' => 'friday', 'Saturday' => 'saturday',
            ];
            $today = $dayMap[now()->format('l')] ?? null;
            if ($today) {
                $todaySchedules = Schedule::where('classroom_id', $classroom->id)
                    ->where('day_of_week', $today)
                    ->with(['subject', 'teacher.user', 'timeSlot'])
                    ->orderBy('time_slot_id')
                    ->get();
                
                // Pre-load all available LMS courses to link with schedule cards
                $availableCourses = \App\Models\LmsCourse::where(function($q) use ($classroom, $student) {
                        $q->where('classroom_id', $classroom->id)
                          ->orWhereHas('lmsClasses', fn($lq) => $lq->where('classroom_id', $classroom->id));
                        if ($student->school_id) {
                            $q->orWhere(function($sq) use ($student) {
                                $sq->where('school_id', $student->school_id)
                                   ->orWhereNull('school_id')
                                   ->orWhere('school_id', 4);
                            });
                        }
                    })
                    ->where('is_active', true)
                    ->withCount(['modules', 'materials'])
                    ->get();

                foreach ($todaySchedules as $schedule) {
                    $matched = $availableCourses->first(function($c) use ($schedule, $classroom) {
                        $isForClass = ($c->classroom_id == $classroom->id) || $c->lmsClasses->contains('classroom_id', $classroom->id);
                        return $c->subject_id == $schedule->subject_id && $c->teacher_id == $schedule->teacher_id && $isForClass;
                    });

                    if (!$matched) {
                        $matched = $availableCourses->first(function($c) use ($schedule, $classroom) {
                            $isForClass = ($c->classroom_id == $classroom->id) || $c->lmsClasses->contains('classroom_id', $classroom->id);
                            return $c->subject_id == $schedule->subject_id && $isForClass;
                        });
                    }

                    if (!$matched) {
                        $matched = $availableCourses->first(function($c) use ($schedule) {
                            return $c->subject_id == $schedule->subject_id && $c->teacher_id == $schedule->teacher_id;
                        });
                    }

                    if (!$matched) {
                        $matched = $availableCourses->first(function($c) use ($schedule) {
                            return $c->subject_id == $schedule->subject_id;
                        });
                    }

                    $schedule->lms_course = $matched;
                }

                $groupedTodaySchedules = $todaySchedules->groupBy(function($item) {
                    return ($item->timeSlot->start_time ?? $item->start_time) . ' - ' . ($item->timeSlot->end_time ?? $item->end_time);
                });

                // Detect current and next schedule
                foreach ($groupedTodaySchedules as $timeKey => $schedulesAtTime) {
                    $first = $schedulesAtTime->first();
                    $start = $first->timeSlot->start_time ?? $first->start_time ?? null;
                    $end = $first->timeSlot->end_time ?? $first->end_time ?? null;
                    
                    if ($start && $end && $currentTime >= $start && $currentTime <= $end) {
                        $currentSchedule = $first;
                    }
                    if ($start && $currentTime < $start && !$nextSchedule) {
                        $nextSchedule = $first;
                    }
                }
            }
        }

        // LMS Courses & Progress
        app(\App\Services\LmsEnrollmentService::class)->syncStudentEnrollments($student);

        $enrollments = \App\Models\LmsEnrollment::where('student_id', $student->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->whereHas('lmsClass.course', function($q) use ($student) {
                if ($student->school_id) {
                    $q->where(function($sq) use ($student) {
                        $sq->where('school_id', $student->school_id)
                           ->orWhereNull('school_id');
                    });
                }
            })
            ->with(['lmsClass.course' => fn($q) => $q->with(['subject', 'teacher.user'])
                ->withCount(['modules', 'materials', 'assignments', 'quizzes']),
                    'lmsClass.classroom'])
            ->latest('enrolled_at')
            ->get();
            
        $courses = $enrollments->map(fn($e) => $e->lmsClass->course)->filter()->unique('id')->take(4);
        
        $courseProgress = [];
        foreach ($courses as $course) {
            $courseProgress[$course->id] = \App\Models\LmsMaterialProgress::getProgressForCourse($course->id, $student->id);
        }

        // Reputation & Elite standing
        $reputation = $student->user->reputation ?? \App\Models\Reputation::firstOrCreate(
            ['user_id' => $student->user_id],
            ['total_points' => 0, 'level' => 1, 'current_streak' => 0]
        );
        $reputationLogs = $student->user->reputationLogs()->latest()->take(5)->get();
        $rank = \App\Models\Reputation::where('total_points', '>', $reputation->total_points ?? 0)->count() + 1;

        // Rapor terbaru
        $latestReportCard = ReportCard::where('student_id', $student->id)
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first();

        // Absensi hari ini
        $todayAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', date('Y-m-d'))
            ->first();

        $showReportCard = \App\Models\Setting::getValue('show_report_card', false);
        $dnaAnalysis = app(\App\Services\StudentDnaService::class)->analyze($student);

        // Pusat Agenda & Deadline Terpadu (Tugas LMS, Kuis LMS & Ujian CBT)
        $unifiedDeadlines = $this->getUnifiedDeadlines($student, $classroom, $enrollments);

        return view('siswa.dashboard', compact(
            'student', 'classroom', 'activeYear', 'activeSemester',
            'avgScore', 'attendanceData', 'totalOutstanding', 'studentBillingStats',
            'todaySchedules', 'groupedTodaySchedules', 'currentTime', 'currentSchedule', 'nextSchedule',
            'latestReportCard', 'courses', 'courseProgress',
            'reputation', 'reputationLogs', 'rank', 'todayAttendance', 'attendanceHistory',
            'showReportCard', 'dnaAnalysis', 'unifiedDeadlines'
        ));
    }

    /**
     * Kumpulkan seluruh deadline & agenda terpadu siswa (Tugas, Kuis, CBT)
     */
    private function getUnifiedDeadlines(Student $student, $classroom, $enrollments): \Illuminate\Support\Collection
    {
        $deadlines = collect();
        $enrolledCourseIds = $enrollments->map(fn($e) => $e->lmsClass?->course_id)->filter()->unique()->toArray();

        if (!empty($enrolledCourseIds)) {
            // 1. LMS Assignments (Tugas)
            $assignments = \App\Models\LmsAssignment::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->with(['course.subject', 'course.teacher'])
                ->get();

            $assignmentIds = $assignments->pluck('id')->toArray();
            $mySubmissions = \App\Models\LmsSubmission::whereIn('assignment_id', $assignmentIds)
                ->where(function ($q) use ($student) {
                    $q->where('student_id', $student->id)
                      ->orWhereHas('group.members', fn($gq) => $gq->where('students.id', $student->id));
                })
                ->get()
                ->keyBy('assignment_id');

            foreach ($assignments as $assignment) {
                $sub = $mySubmissions->get($assignment->id);
                // Hanya masukkan tugas yang belum selesai: belum mengumpulkan, atau masih draft, atau minta revisi
                $isPending = !$sub || in_array($sub->status, ['draft', 'revision_requested']);

                if ($isPending) {
                    $isLate = $assignment->deadline && now()->isAfter($assignment->deadline);
                    $deadlines->push([
                        'id' => 'assign_' . $assignment->id,
                        'type' => 'assignment',
                        'type_label' => 'Tugas LMS',
                        'type_icon' => 'fas fa-tasks',
                        'type_badge_bg' => '#eff6ff',
                        'type_badge_color' => '#1d4ed8',
                        'type_badge_border' => '#bfdbfe',
                        'title' => $assignment->title,
                        'subject_name' => $assignment->course?->subject?->subject_name ?? $assignment->course?->name ?? 'LMS',
                        'teacher_name' => $assignment->course?->teacher?->full_name ?? 'Guru Mapel',
                        'deadline' => $assignment->deadline,
                        'deadline_label' => $assignment->deadline ? $assignment->deadline->translatedFormat('d M Y, H:i') : 'Tanpa Batas Waktu',
                        'is_urgent' => $assignment->deadline ? ($assignment->deadline->isToday() || $assignment->deadline->isTomorrow()) : false,
                        'is_late' => $isLate,
                        'status_label' => $sub && $sub->status === 'revision_requested' ? 'Perlu Revisi' : ($isLate ? 'Terlambat' : 'Belum Dikumpulkan'),
                        'status_color' => $sub && $sub->status === 'revision_requested' ? '#f59e0b' : ($isLate ? '#ef4444' : '#6366f1'),
                        'action_url' => route('siswa.lms.show', $assignment->course_id),
                        'action_label' => $sub && $sub->status === 'revision_requested' ? 'Revisi Tugas' : 'Kumpulkan Tugas',
                        'sort_timestamp' => $assignment->deadline ? $assignment->deadline->timestamp : 9999999999,
                    ]);
                }
            }

            // 2. LMS Quizzes (Kuis)
            $quizzes = \App\Models\LmsQuiz::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->with(['course.subject', 'course.teacher'])
                ->get();

            $quizIds = $quizzes->pluck('id')->toArray();
            $myAttempts = \App\Models\LmsQuizAttempt::whereIn('quiz_id', $quizIds)
                ->where('student_id', $student->id)
                ->get()
                ->groupBy('quiz_id');

            foreach ($quizzes as $quiz) {
                if ($quiz->start_time && now()->lt($quiz->start_time)) {
                    continue;
                }
                if ($quiz->end_time && now()->diffInDays($quiz->end_time, false) < -7) {
                    continue;
                }

                $attempts = $myAttempts->get($quiz->id, collect());
                $unfinishedAttempt = $attempts->firstWhere('finished_at', null);
                $finishedAttempts = $attempts->whereNotNull('finished_at');
                $bestScore = $finishedAttempts->max('score');
                $isPassed = $bestScore !== null && $bestScore >= ($quiz->passing_score ?? 75);
                $canAttempt = $quiz->canAttempt($student->id);

                if ($unfinishedAttempt || (!$isPassed && $canAttempt)) {
                    $isLate = $quiz->end_time && now()->isAfter($quiz->end_time);
                    $deadlines->push([
                        'id' => 'quiz_' . $quiz->id,
                        'type' => 'quiz',
                        'type_label' => 'Kuis LMS',
                        'type_icon' => 'fas fa-question-circle',
                        'type_badge_bg' => '#f5f3ff',
                        'type_badge_color' => '#6d28d9',
                        'type_badge_border' => '#ddd6fe',
                        'title' => $quiz->title,
                        'subject_name' => $quiz->course?->subject?->subject_name ?? $quiz->course?->name ?? 'LMS',
                        'teacher_name' => $quiz->course?->teacher?->full_name ?? 'Guru Mapel',
                        'deadline' => $quiz->end_time,
                        'deadline_label' => $quiz->end_time ? $quiz->end_time->translatedFormat('d M Y, H:i') : 'Kuis Terbuka',
                        'is_urgent' => $quiz->end_time ? ($quiz->end_time->isToday() || $quiz->end_time->isTomorrow()) : false,
                        'is_late' => $isLate,
                        'status_label' => $unfinishedAttempt ? 'Sedang Dikerjakan' : ($finishedAttempts->isNotEmpty() ? 'Remedial Tersedia' : 'Belum Dikerjakan'),
                        'status_color' => $unfinishedAttempt ? '#10b981' : ($finishedAttempts->isNotEmpty() ? '#f59e0b' : '#8b5cf6'),
                        'action_url' => route('siswa.lms.quizzes.start', $quiz->id),
                        'action_label' => $unfinishedAttempt ? 'Lanjutkan Kuis' : ($finishedAttempts->isNotEmpty() ? 'Ikuti Remedial' : 'Mulai Kuis'),
                        'sort_timestamp' => $quiz->end_time ? $quiz->end_time->timestamp : 9999999998,
                    ]);
                }
            }
        }

        // 3. CBT Exams (Ujian Sekolah / Ujian Mapel)
        if ($classroom) {
            $exams = \App\Models\CbtExam::whereIn('status', ['published', 'active'])
                ->whereHas('participants', fn($q) => $q->where('classroom_id', $classroom->id))
                ->with(['subject', 'teacher', 'school'])
                ->get();

            $examIds = $exams->pluck('id')->toArray();
            $myExamSessions = \App\Models\CbtExamSession::whereIn('exam_id', $examIds)
                ->where('student_id', $student->id)
                ->get()
                ->groupBy('exam_id');

            foreach ($exams as $exam) {
                if ($exam->end_time && now()->diffInDays($exam->end_time, false) < -3) {
                    continue;
                }

                $sessions = $myExamSessions->get($exam->id, collect());
                $activeSession = $sessions->firstWhere('status', 'in_progress');
                $submittedCount = $sessions->whereIn('status', ['submitted', 'timeout', 'graded'])->count();
                $canAttempt = $activeSession || ($submittedCount < $exam->max_attempts);

                if ($canAttempt) {
                    $isAvailableNow = $exam->isAccessible();
                    $deadlines->push([
                        'id' => 'cbt_' . $exam->id,
                        'type' => 'cbt',
                        'type_label' => 'Ujian CBT',
                        'type_icon' => 'fas fa-laptop-code',
                        'type_badge_bg' => '#fff7ed',
                        'type_badge_color' => '#c2410c',
                        'type_badge_border' => '#ffedd5',
                        'title' => $exam->exam_title,
                        'subject_name' => $exam->subject?->subject_name ?? 'Ujian CBT',
                        'teacher_name' => $exam->teacher?->full_name ?? ($exam->school?->name ?? 'Sekolah'),
                        'deadline' => $exam->end_time,
                        'deadline_label' => $exam->end_time ? $exam->end_time->translatedFormat('d M Y, H:i') : ($exam->start_time ? $exam->start_time->translatedFormat('d M Y, H:i') : 'Jadwal Aktif'),
                        'is_urgent' => true,
                        'is_late' => false,
                        'status_label' => $activeSession ? 'Sesi Aktif' : ($isAvailableNow ? 'Siap Dikerjakan' : 'Terjadwal'),
                        'status_color' => $activeSession ? '#10b981' : ($isAvailableNow ? '#ea580c' : '#0284c7'),
                        'action_url' => route('siswa.cbt.show', $exam->id),
                        'action_label' => $activeSession ? 'Lanjutkan Ujian' : 'Masuk Ujian',
                        'sort_timestamp' => $activeSession ? 0 : ($exam->start_time ? $exam->start_time->timestamp : ($exam->end_time ? $exam->end_time->timestamp : 5000000000)),
                    ]);
                }
            }
        }

        return $deadlines->sortBy('sort_timestamp')->values();
    }

    /**
     * Jadwal Pelajaran - Redesigned to Weekly Grid with Duration Support
     */
    public function jadwal()
    {
        $student = $this->getStudent();
        $student->load('school');
        $classroom = $this->getCurrentClassroom($student);
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu',
        ];

        $timetable = [];
        $subjectColors = [];
        $timeSlots = collect();
        $totalSessions = 0;
        $totalJP = 0;
        $uniqueSubjects = 0;
        $activeDays = [];

        if ($classroom) {
            $allSchedules = Schedule::where('classroom_id', $classroom->id)
                ->with(['subject', 'teacher.user', 'timeSlot'])
                ->get();

            $totalSessions = $allSchedules->count();
            $totalJP = $allSchedules->sum('duration_slots') ?: $allSchedules->count();
            $uniqueSubjects = $allSchedules->pluck('subject_id')->unique()->count();

            // Filter hari yang benar-benar punya jadwal (hilangkan Sabtu kosong dll)
            $activeDays = collect($days)->filter(function ($day) use ($allSchedules) {
                return $allSchedules->where('day_of_week', $day)->count() > 0;
            })->values()->all();

            // Get unique time slots used in the schedules, sorted by slot_order
            $timeSlotIds = $allSchedules->pluck('time_slot_id')->unique()->filter();
            
            $usedSlots = \App\Models\TimeSlot::whereIn('id', $timeSlotIds)->get();
            $minOrder = $usedSlots->min('slot_order');
            $maxOrder = $usedSlots->max('slot_order');
            
            // Perhitungkan colspan/rowspan yang melebihi slot_order awal
            foreach ($allSchedules as $s) {
                if ($s->timeSlot && $s->duration_slots > 1) {
                    $endOrder = $s->timeSlot->slot_order + ($s->duration_slots - 1);
                    if ($endOrder > $maxOrder) {
                        $maxOrder = $endOrder;
                    }
                }
            }

            if ($minOrder !== null && $maxOrder !== null) {
                // Fetch ALL slots in range so UI HTML table doesn't collapse rows causing rowspan spill-over
                $timeSlots = \App\Models\TimeSlot::where('school_id', $classroom->school_id)
                    ->whereBetween('slot_order', [$minOrder, $maxOrder])
                    ->orderBy('slot_order')
                    ->get()
                    ->unique(function ($slot) {
                        return $slot->start_time . '-' . $slot->end_time;
                    });
            } else {
                $timeSlots = collect();
            }

            // Map schedules for quick lookup by day and time key
            $sMap = [];
            foreach ($allSchedules as $s) {
                // Use the same key generation as index() timeline
                $timeSlot = $s->timeSlot;
                $timeKey = ($timeSlot->start_time ?? $s->start_time) . '-' . ($timeSlot->end_time ?? $s->end_time);
                $sMap[$s->day_of_week][$timeKey] = $s;
            }

            // Occupied tracking for rowspan
            $occupied = [];
            
            // Grid building loop
            foreach ($timeSlots as $slot) {
                $timeKey = $slot->start_time . '-' . $slot->end_time;
                
                foreach ($days as $day) {
                    // Cell might be covered by a previous rowspan
                    if (isset($occupied[$day][$slot->slot_order])) continue;

                    $schedule = $sMap[$day][$timeKey] ?? null;
                    if ($schedule) {
                        $timetable[$slot->slot_order][$day] = $schedule;
                        
                        $duration = $schedule->duration_slots ?? 1;
                        if ($duration > 1) {
                            for ($i = 1; $i < $duration; $i++) {
                                $occupied[$day][$slot->slot_order + $i] = true;
                            }
                        }

                        // Colors
                        if (!isset($subjectColors[$schedule->subject_id])) {
                            $palettes = ['blue', 'emerald', 'indigo', 'amber', 'rose', 'cyan', 'purple', 'teal', 'orange', 'pink'];
                            $colorIndex = count($subjectColors) % count($palettes);
                            $color = $palettes[$colorIndex];
                            $subjectColors[$schedule->subject_id] = [
                                'bg' => "bg-{$color}-100",
                                'border' => "border-{$color}-300",
                                'text' => "text-{$color}-800",
                                'sub' => "text-{$color}-600"
                            ];
                        }
                    } else {
                        $timetable[$slot->slot_order][$day] = null;
                    }
                }
            }
        }

        return view('siswa.jadwal', compact(
            'student', 'classroom', 'timetable', 'days', 'activeDays', 'dayLabels', 
            'subjectColors', 'timeSlots', 'totalSessions', 'totalJP', 'uniqueSubjects'
        ));
    }

    /**
     * Nilai / Grades
     */
    public function nilai(Request $request)
    {
        $student = $this->getStudent();
        $student->load('school');
        $activeSemester = Semester::where('is_active', true)->first();
        $semesters = Semester::select('id', 'semester_name', 'semester_number', 'academic_year_id')
            ->when($activeSemester, fn($q) => $q->where('academic_year_id', $activeSemester->academic_year_id))
            ->orderBy('semester_number')
            ->get();

        $selectedSemesterId = $request->get('semester_id', $activeSemester?->id);

        $selectedSemester = Semester::find($selectedSemesterId);
        $academicYearId = $selectedSemester?->academic_year_id;
        $classroom = $student->classrooms()
            ->where('student_classes.academic_year_id', $academicYearId)
            ->where('student_classes.status', 'aktif')
            ->first();
        $gradeLevel = $classroom?->grade_level;

        $grades = Grade::select('id', 'student_id', 'subject_id', 'teacher_id', 'semester_id', 'grade_type', 'score', 'notes', 'is_remedial', 'lms_source_type', 'created_at')
            ->where('student_id', $student->id)
            ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId))
            ->with([
                'subject:id,subject_name,name,subject_code,kkm',
                'teacher:id,full_name',
                'semester:id,semester_name,semester_number,academic_year_id',
            ])
            ->orderBy('subject_id')
            ->get();

        // Grade weights for this student's school
        $gradeWeight = GradeWeight::getForSchool($student->school_id);

        // Group by subject → pivot into Tugas/PTS/PAS/Sikap columns
        $subjectGrades = $grades->groupBy('subject_id')->map(function ($items) use ($gradeWeight, $gradeLevel) {
            $subject = $items->first()->subject;
            $tugas = $items->where('grade_type', 'tugas');
            $utsItems = $items->where('grade_type', 'uts');
            $uasItems = $items->where('grade_type', 'uas');
            $sikapItems = $items->where('grade_type', 'sikap');
            $tugasAvg = $tugas->count() > 0 ? round($tugas->avg('score'), 1) : null;
            $utsAvg = $utsItems->count() > 0 ? round($utsItems->avg('score'), 1) : null;
            $uasAvg = $uasItems->count() > 0 ? round($uasItems->avg('score'), 1) : null;
            $sikapAvg = $sikapItems->count() > 0 ? round($sikapItems->avg('score'), 1) : null;

            // Calculate weighted final score
            $finalScore = null;
            $weights = $gradeWeight->getWeightsAsDecimal();
            $hasAnyScore = $tugasAvg !== null || $utsAvg !== null || $uasAvg !== null || $sikapAvg !== null;
            if ($hasAnyScore) {
                $finalScore = round(
                    ($tugasAvg ?? 0) * $weights['tugas'] +
                    ($utsAvg ?? 0) * $weights['pts'] +
                    ($uasAvg ?? 0) * $weights['pas'] +
                    ($sikapAvg ?? 0) * $weights['sikap'],
                    1
                );
            }

            $kkm = $subject->kkm ?? 75;
            $predicate = $finalScore !== null ? \App\Models\FinalGrade::scoreToPredicate($finalScore, $kkm, $gradeLevel) : null;

            return [
                'subject' => $subject,
                'tugas_grades' => $tugas,
                'tugas_avg' => $tugasAvg,
                'uts_grades' => $utsItems,
                'uts_avg' => $utsAvg,
                'uts_count' => $utsItems->count(),
                'uas_grades' => $uasItems,
                'uas_avg' => $uasAvg,
                'uas_count' => $uasItems->count(),
                'sikap_grades' => $sikapItems,
                'sikap_avg' => $sikapAvg,
                'average' => round($items->avg('score'), 1),
                'final_score' => $finalScore,
                'predicate' => $predicate,
                'grade_count' => $items->count(),
            ];
        })->sortBy(fn($sg) => $sg['subject']->subject_name ?? $sg['subject']->name);

        // Published report cards (still fetched but will be hidden or restricted in UI, we fetch to pass to view)
        $reportCards = ReportCard::select('id', 'student_id', 'classroom_id', 'semester_id', 'academic_year_id', 'status', 'published_at')
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->with([
                'semester:id,semester_name,semester_number',
                'academicYear:id,year',
                'classroom:id,class_name',
            ])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // Calculate analytics data for charts
        $chartSubjects = [];
        $chartAverages = [];
        $chartKkms = [];
        foreach ($subjectGrades as $sg) {
            $chartSubjects[] = $sg['subject']->subject_name ?? $sg['subject']->name ?? '-';
            $chartAverages[] = $sg['average'];
            $chartKkms[] = $sg['subject']->kkm ?? 75;
        }

        $monthlyGrades = $grades->filter(fn($g) => $g->created_at !== null)
            ->groupBy(function ($grade) {
                return $grade->created_at->format('Y-m');
            })
            ->sortKeys()
            ->map(function ($items, $yearMonth) {
                $dateObj = \Carbon\Carbon::createFromFormat('Y-m-d', $yearMonth . '-01');
                return [
                    'label' => $dateObj->translatedFormat('M Y'),
                    'avg' => round($items->avg('score'), 1),
                ];
            })->values();

        $showReportCard = \App\Models\Setting::getValue('show_report_card', false);

        return view('siswa.nilai', compact(
            'student', 'grades', 'subjectGrades', 'semesters',
            'selectedSemesterId', 'reportCards', 'gradeWeight',
            'chartSubjects', 'chartAverages', 'chartKkms', 'monthlyGrades',
            'showReportCard'
        ));
    }

    /**
     * Tagihan & Pembayaran
     */
    public function tagihan(Request $request)
    {
        $student = $this->getStudent();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();

        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeYear?->id ?? null);

        $query = StudentBill::where('student_id', $student->id)
            ->with(['paymentType', 'payments', 'academicYear', 'semester'])
            ->orderByDesc('year')
            ->orderByDesc('month');

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        $bills = $query->get();

        $totalTagihan = $bills->sum('amount');
        $totalBayar = $bills->sum('paid_amount');
        $totalSisa = $bills->sum(fn($b) => $b->amount - $b->paid_amount);

        // Build month map for recurring bills: payment_type_id_month => bill
        $monthMap = [];
        foreach ($bills as $bill) {
            if ($bill->paymentType && $bill->paymentType->is_recurring && $bill->month) {
                $key = $bill->payment_type_id . '_' . $bill->month;
                $monthMap[$key] = $bill;
            }
        }

        // Collect recurring payment types
        $recurringTypes = collect();
        foreach ($bills as $bill) {
            if ($bill->paymentType && $bill->paymentType->is_recurring && !$recurringTypes->has($bill->payment_type_id)) {
                $recurringTypes->put($bill->payment_type_id, $bill->paymentType->type_name);
            }
        }

        // Non-recurring bills
        $nonRecurringBills = $bills->filter(function ($b) {
            return !$b->paymentType || !$b->paymentType->is_recurring;
        })->values();

        // Month labels (Juli-Juni for typical academic year)
        $months = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

        // Helper to resolve accurate bill year even if year column in DB is 0, null, or corrupted (e.g. 2001)
        $getResolvedYear = function($b) use ($activeYear) {
            $yr = (int)$b->year;
            if ($yr >= 2024 && $yr <= 2035) {
                return $yr;
            }
            if (!empty($b->due_date)) {
                $parsedYear = (int)\Carbon\Carbon::parse($b->due_date)->format('Y');
                if ($parsedYear >= 2024 && $parsedYear <= 2035) {
                    return $parsedYear;
                }
            }
            $ayName = $b->academicYear?->year ?? $activeYear?->year ?? '2026/2027';
            if (preg_match('/(20\d{2})/', $ayName, $m)) {
                $startYear = (int)$m[1];
                $mNum = (int)$b->month;
                return ($mNum >= 7 && $mNum <= 12) ? $startYear : $startYear + 1;
            }
            return (int)date('Y');
        };

        $getDueDate = function($b) use ($getResolvedYear) {
            if ($b->due_date) {
                return \Carbon\Carbon::parse($b->due_date)->endOfDay();
            }
            if ($b->month) {
                $year = $getResolvedYear($b);
                return \Carbon\Carbon::create($year, (int)$b->month, 10)->endOfDay();
            }
            return null;
        };

        // Tunggakan amount (Hanya s.d. bulan berkenaan / lewat jatuh tempo)
        $tunggakanAmount = $bills->filter(function($b) use ($getDueDate) {
            if ($b->status === 'lunas') return false;
            if ($b->isOverdue()) return true;
            $dueDate = $getDueDate($b);
            return $dueDate ? now()->isAfter($dueDate) : false;
        })->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

        $upcomingAmount = $bills->filter(function($b) use ($getDueDate) {
            if ($b->status === 'lunas') return false;
            if ($b->isOverdue()) return false;
            $dueDate = $getDueDate($b);
            return $dueDate ? !now()->isAfter($dueDate) : true;
        })->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

        return view('siswa.tagihan', compact(
            'student', 'bills', 'academicYears', 'selectedYearId',
            'totalTagihan', 'totalBayar', 'totalSisa',
            'monthMap', 'recurringTypes', 'nonRecurringBills', 'months',
            'tunggakanAmount', 'upcomingAmount'
        ));
    }

    /**
     * Absensi
     */
    public function absensi(Request $request)
    {
        if (!\App\Models\Setting::getValue('siswa_view_attendance_recap', true)) {
            abort(403, 'Akses Rekap Absensi Siswa telah dinonaktifkan oleh administrator.');
        }

        $student = $this->getStudent();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $classroom = null;

        // Effective start date: minimum of active year start date and current date
        $effectiveStartDate = $activeYear && $activeYear->start_date->gt(now()) 
            ? now() 
            : ($activeYear ? $activeYear->start_date : now());

        $attendancesRaw = Attendance::where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->whereBetween('date', [
                $effectiveStartDate->format('Y-m-d'),
                $activeYear->end_date->format('Y-m-d')
            ]))
            ->orderByDesc('date')
            ->get();

        // Key by date for easy lookup in heatmap
        $attendances = $attendancesRaw->keyBy(fn($item) => $item->date->format('Y-m-d'));

        $summary = [
            'present' => $attendancesRaw->where('status', 'hadir')->count(),
            'sick' => $attendancesRaw->where('status', 'sakit')->count(),
            'permission' => $attendancesRaw->where('status', 'izin')->count(),
            'absent' => $attendancesRaw->where('status', 'alpha')->count(),
            'late' => $attendancesRaw->where('status', 'terlambat')->count(),
            'total' => 0,
            'percentage' => 0,
        ];

        $monthsToShow = 4;
        if ($activeYear) {
            $classroom = $this->getCurrentClassroom($student);
            if ($classroom) {
                $statsService = app(AttendanceStatisticsService::class);
                $z = $statsService->calculateZ($effectiveStartDate->format('Y-m-d'), date('Y-m-d'), $classroom->id);
                $dayOfWeekQuery = DB::connection()->getDriverName() === 'sqlite'
                    ? "strftime('%w', date) NOT IN ('0', '6')"
                    : "DAYOFWEEK(attendances.date) NOT IN (1, 7)";

                $presentCount = Attendance::where('student_id', $student->id)
                    ->whereBetween('date', [$effectiveStartDate->format('Y-m-d'), date('Y-m-d')])
                    ->whereIn('status', ['hadir', 'terlambat'])
                    ->whereRaw($dayOfWeekQuery)
                    ->select(DB::raw('COUNT(DISTINCT date) as count'))
                    ->value('count');

                $summary['total'] = $z;
                $summary['present_total'] = $presentCount; 
                $percentage = $z > 0 ? ($presentCount / $z) * 100 : 0;
                $summary['percentage'] = round(min(100, $percentage), 1);
            }
            
            $start = \Carbon\Carbon::parse($activeYear->start_date)->startOfMonth();
            $now = now()->startOfMonth();
            // Use absolute month difference to support future years
            $monthsToShow = $start->diffInMonths($now, true) + 1;
        }

        return view('siswa.absensi', compact('student', 'attendances', 'summary', 'activeYear', 'monthsToShow', 'classroom'));
    }

    /**
     * Profil Siswa
     */
    public function profil()
    {
        $student = $this->getStudent();
        $student->load(['school', 'parents', 'user.reputation', 'user.badges']);
        $classroom = $this->getCurrentClassroom($student);

        return view('siswa.profil', compact('student', 'classroom'));
    }
    /**
     * Catatan Konseling & Prestasi
     */
    public function konseling()
    {
        $student = $this->getStudent();
        $student->load(['user.reputation', 'school']);
        $classroom = $this->getCurrentClassroom($student);

        $counselingRecords = $student->counselingRecords()
            ->where(function ($query) {
                $query->where('is_confidential', false)
                      ->orWhere('record_type', 'penghargaan'); // Awards always visible
            })
            ->with(['counselor'])
            ->orderByDesc('incident_date')
            ->get();

        $achievements = \App\Models\StudentAchievement::where('student_id', $student->id)
            ->with(['academicYear', 'verifiedBy'])
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total_achievements' => $achievements->count(),
            'verified_count'     => $achievements->where('status', 'verified')->count(),
            'pending_count'      => $achievements->where('status', 'pending')->count(),
            'rejected_count'     => $achievements->where('status', 'rejected')->count(),
            'reputation_points'  => $student->user?->reputation?->total_points ?? 0,
            'level_name'         => $student->user?->reputation?->level_name ?? 'Newbie',
            'level_color'        => $student->user?->reputation?->level_color ?? 'slate',
        ];

        return view('siswa.konseling', compact('student', 'classroom', 'counselingRecords', 'achievements', 'stats'));
    }

    /**
     * Upload Prestasi Mandiri oleh Siswa
     */
    public function storePrestasi(Request $request)
    {
        $student = $this->getStudent();

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'type'             => 'required|in:academic,sport,art,competition,other',
            'level'            => 'required|in:school,district,city,province,national,international',
            'rank'             => 'nullable|in:winner,runner_up,third_place,participant',
            'achievement_date' => 'required|date|before_or_equal:today',
            'description'      => 'nullable|string|max:1000',
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'title.required'            => 'Nama prestasi atau kejuaraan wajib diisi.',
            'type.required'             => 'Kategori/bidang prestasi wajib dipilih.',
            'level.required'            => 'Tingkat kejuaraan wajib dipilih.',
            'achievement_date.required' => 'Tanggal perolehan prestasi wajib diisi.',
            'achievement_date.before_or_equal' => 'Tanggal perolehan tidak boleh melebihi hari ini.',
            'certificate_file.required' => 'Dokumen bukti/sertifikat/piagam wajib diunggah.',
            'certificate_file.mimes'    => 'Format dokumen harus berupa PDF, JPG, JPEG, atau PNG.',
            'certificate_file.max'      => 'Ukuran file dokumen bukti maksimal 10MB.',
        ]);

        $certificatePath = null;
        if ($request->hasFile('certificate_file')) {
            $certificatePath = $request->file('certificate_file')->store('achievements', 'public');
        }

        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();
        $points = \App\Models\StudentAchievement::calculatePoints($validated['level'], $validated['rank'] ?? null);

        $achievement = \App\Models\StudentAchievement::create([
            'student_id'       => $student->id,
            'academic_year_id' => $activeYear?->id,
            'title'            => $validated['title'],
            'type'             => $validated['type'],
            'level'            => $validated['level'],
            'rank'             => $validated['rank'] ?? null,
            'achievement_date' => $validated['achievement_date'],
            'description'      => $validated['description'] ?? null,
            'certificate_file' => $certificatePath,
            'status'           => 'pending',
            'points'           => $points,
            'created_by'       => Auth::id(),
        ]);

        // Langsung berikan poin reputasi seketika kepada siswa
        try {
            if ($student->user_id) {
                \App\Models\ReputationLog::log(
                    $student->user_id,
                    $points,
                    'achievement',
                    "Penghargaan Prestasi: {$achievement->title} (" . strtoupper($achievement->level_label) . ")",
                    $achievement
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Pencatatan poin reputasi prestasi siswa gagal: ' . $e->getMessage());
        }

        return redirect()->route('siswa.konseling')
            ->with('success', "Prestasi '{$achievement->title}' berhasil diunggah! Poin reputasi (+{$points} Poin) telah otomatis aktif di akun Anda dan menunggu justifikasi oleh Wali Kelas.");
    }

    /**
     * Download own published report card PDF.
     */
    public function printRaport(ReportCard $reportCard, \App\Services\ReportCardService $reportCardService)
    {
        $student = $this->getStudent();

        // Verify report card visibility setting is enabled
        $showReportCard = \App\Models\Setting::getValue('show_report_card', false);
        if (!$showReportCard) {
            abort(403, 'Akses Rapor Digital dinonaktifkan oleh administrator.');
        }

        // Verify report card belongs to this student and is published
        if ($reportCard->student_id !== $student->id || $reportCard->status !== 'published') {
            abort(403, 'Rapor tidak tersedia atau belum dipublikasikan.');
        }

        $reportCard->load(['student.school', 'semester.academicYear', 'classroom']);

        $subjectScores = $reportCardService->buildSubjectScores($reportCard);
        $achievements = $reportCardService->getAchievements($reportCard->student_id, $reportCard->academic_year_id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report_cards.pdf', compact('reportCard', 'subjectScores', 'achievements'));

        $rawFilename = 'Rapor_' . $reportCard->student->full_name . '_' . ($reportCard->semester->semester_name ?? '') . '.pdf';
        $filename = str_replace(['/', '\\'], '-', $rawFilename);

        return $pdf->download($filename);
    }
}
