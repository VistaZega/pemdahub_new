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
            
            $totalAmount = (clone $billsQuery)->sum('amount');
            $totalPaidAmount = (clone $billsQuery)->sum('paid_amount');
            $totalOutstanding = $totalAmount - $totalPaidAmount;
            
            $studentBillingStats = [
                'total_bills' => (clone $billsQuery)->count(),
                'paid_bills' => (clone $billsQuery)->where('status', 'lunas')->count(),
                'total_amount' => $totalAmount,
                'paid_amount' => $totalPaidAmount,
                'outstanding' => $totalOutstanding,
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
        $enrollments = \App\Models\LmsEnrollment::where('student_id', $student->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
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

        return view('siswa.dashboard', compact(
            'student', 'classroom', 'activeYear', 'activeSemester',
            'avgScore', 'attendanceData', 'totalOutstanding', 'studentBillingStats',
            'todaySchedules', 'groupedTodaySchedules', 'currentTime', 'currentSchedule', 'nextSchedule',
            'latestReportCard', 'courses', 'courseProgress',
            'reputation', 'reputationLogs', 'rank', 'todayAttendance', 'attendanceHistory',
            'showReportCard'
        ));
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

        // Tunggakan amount
        $tunggakanAmount = $bills->filter(fn($b) => $b->isOverdue())->sum(fn($b) => max(0, $b->amount - $b->paid_amount));
        $upcomingAmount = $bills->filter(fn($b) => !$b->isOverdue() && $b->status !== 'lunas')->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

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
            ->with(['academicYear'])
            ->orderByDesc('achievement_date')
            ->get();

        return view('siswa.konseling', compact('student', 'classroom', 'counselingRecords', 'achievements'));
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
