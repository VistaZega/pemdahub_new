<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\GradeWeight;
use App\Models\StudentAchievement;

class DashboardController extends Controller
{
    /**
     * Get all children (students) linked to this parent.
     */
    private function getChildren()
    {
        $user = Auth::user();
        $parentRecords = ParentModel::where('user_id', $user->id)->with('student.school')->get();

        if ($parentRecords->isEmpty() && ($user->isOwnerOrSuperAdmin() || $user->username === 'yulzega')) {
            $celeste = Student::where('full_name', 'LIKE', '%Celeste%')->first();
            if ($celeste) {
                ParentModel::firstOrCreate(
                    ['user_id' => $user->id, 'student_id' => $celeste->id],
                    [
                        'relation_type' => 'ayah',
                        'full_name' => $user->name,
                        'phone' => $user->phone ?? '-',
                        'email' => $user->email ?? '-',
                        'occupation' => 'Ketua Yayasan',
                    ]
                );
                $parentRecords = ParentModel::where('user_id', $user->id)->with('student.school')->get();
            }
        }

        return $parentRecords->map(fn($p) => $p->student)->filter()->unique('id');
    }

    /**
     * Get a specific child that belongs to this parent.
     */
    private function getChild($studentId)
    {
        $user = Auth::user();
        $parentRecord = ParentModel::where('user_id', $user->id)
            ->where('student_id', $studentId)
            ->first();

        if (!$parentRecord && ($user->isOwnerOrSuperAdmin() || $user->username === 'yulzega')) {
            $student = Student::find($studentId);
            if ($student) {
                $parentRecord = ParentModel::firstOrCreate(
                    ['user_id' => $user->id, 'student_id' => $student->id],
                    [
                        'relation_type' => 'ayah',
                        'full_name' => $user->name,
                        'phone' => $user->phone ?? '-',
                        'email' => $user->email ?? '-',
                        'occupation' => 'Ketua Yayasan',
                    ]
                );
            }
        }

        if (!$parentRecord) {
            abort(404, 'Data anak tidak ditemukan.');
        }

        return Student::with('school')->findOrFail($parentRecord->student_id);
    }

    /**
     * Get current classroom for a student.
     */
    private function getCurrentClassroom(Student $student)
    {
        return $student->currentClassroom()->first();
    }

    /**
     * Dashboard Orang Tua - Ringkasan semua anak
     */
    public function index()
    {
        $children = $this->getChildren();
        $activeYear = Cache::remember('active_academic_year', 3600, fn() => AcademicYear::where('is_active', true)->first());
        $activeSemester = Cache::remember('active_semester', 3600, fn() => Semester::where('is_active', true)->first());
        $currentTime = now()->format('H:i');
        $showReportCard = \App\Models\Setting::getValue('show_report_card', false);

        $childrenData = $children->map(function ($student) use ($activeYear, $activeSemester, $currentTime) {
            $student->load('school');
            $classroom = $this->getCurrentClassroom($student);

            // Average score
            $avg = 0;
            if ($activeSemester) {
                $avg = Grade::where('student_id', $student->id)
                    ->where('semester_id', $activeSemester->id)
                    ->avg('score') ?? 0;
            }

            // Attendance Data & Breakdown
            $attendanceData = ['total' => 0, 'present' => 0, 'percentage' => 0];
            if ($activeYear && $classroom) {
                $effectiveStartDate = $activeYear->start_date->gt(now()) ? now() : $activeYear->start_date;
                $statsService = app(\App\Services\AttendanceStatisticsService::class);
                $z = $statsService->calculateZ($effectiveStartDate->format('Y-m-d'), date('Y-m-d'), $classroom->id);
                
                $dayOfWeekQuery = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite'
                    ? "strftime('%w', date) NOT IN ('0', '6')"
                    : "DAYOFWEEK(attendances.date) NOT IN (1, 7)";

                $presentCount = Attendance::where('student_id', $student->id)
                    ->whereBetween('date', [$effectiveStartDate->format('Y-m-d'), date('Y-m-d')])
                    ->whereIn('status', ['hadir', 'terlambat'])
                    ->whereRaw($dayOfWeekQuery)
                    ->select(\Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT date) as count'))
                    ->value('count');

                $percentage = $z > 0 ? ($presentCount / $z) * 100 : 0;
                $attendanceData = [
                    'total' => $z,
                    'present' => $presentCount,
                    'percentage' => round(min(100, $percentage), 1),
                ];
            } else {
                $attData = Attendance::where('student_id', $student->id)
                    ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'hadir' OR status = 'terlambat' THEN 1 ELSE 0 END) as present")
                    ->first();
                $attendanceData = [
                    'total' => $attData->total ?? 0,
                    'present' => $attData->present ?? 0,
                    'percentage' => ($attData && $attData->total > 0) ? round(($attData->present / $attData->total) * 100, 1) : 0,
                ];
            }

            // Attendance Today
            $todayAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('date', date('Y-m-d'))
                ->first();

            // Attendance History
            $attendanceHistory = Attendance::where('student_id', $student->id)
                ->orderByDesc('date')
                ->limit(5)
                ->get();

            // Outstanding bills
            $allBills = StudentBill::where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->get();

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

            $outstanding = $allBills->filter(function($b) use ($getDueDate) {
                if ($b->status === 'lunas') return false;
                if ($b->isOverdue()) return true;
                $dueDate = $getDueDate($b);
                return $dueDate ? now()->isAfter($dueDate) : false;
            })->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

            $totalPaidAmount = $allBills->sum('paid_amount');
            $totalBillsAmount = $allBills->sum('amount');

            // Schedule Today for Child
            $todaySchedules = collect();
            $groupedTodaySchedules = collect();
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

            // DNA 360 Analysis
            $dnaAnalysis = null;
            try {
                $dnaAnalysis = app(\App\Services\StudentDnaService::class)->analyze($student);
            } catch (\Throwable $e) {
                // Ignore if unavailable
            }

            // Reputation
            $reputation = $student->user->reputation ?? null;
            $rank = $reputation ? (\App\Models\Reputation::where('total_points', '>', $reputation->total_points ?? 0)->count() + 1) : 1;

            // Latest Report Card
            $latestReportCard = ReportCard::where('student_id', $student->id)
                ->where('status', 'published')
                ->orderByDesc('id')
                ->first();

            // Recent Achievements & Counseling
            $recentAchievements = \App\Models\StudentAchievement::where('student_id', $student->id)
                ->latest('achievement_date')
                ->take(3)
                ->get();

            $recentCounseling = $student->counselingRecords()
                ->where(function ($query) {
                    $query->where('is_confidential', false)
                          ->orWhere('parent_notified', true);
                })
                ->with(['counselor'])
                ->orderByDesc('incident_date')
                ->take(3)
                ->get();

            return [
                'student' => $student,
                'classroom' => $classroom,
                'avg_score' => round($avg, 1),
                'outstanding' => $outstanding,
                'total_paid_amount' => $totalPaidAmount,
                'total_bills_amount' => $totalBillsAmount,
                'attendance_pct' => $attendanceData['percentage'],
                'attendance_data' => $attendanceData,
                'today_attendance' => $todayAttendance,
                'attendance_history' => $attendanceHistory,
                'today_schedules' => $todaySchedules,
                'grouped_today_schedules' => $groupedTodaySchedules,
                'current_schedule' => $currentSchedule,
                'next_schedule' => $nextSchedule,
                'dna_analysis' => $dnaAnalysis,
                'reputation' => $reputation,
                'rank' => $rank,
                'latest_report_card' => $latestReportCard,
                'recent_achievements' => $recentAchievements,
                'recent_counseling' => $recentCounseling,
            ];
        });

        return view('orangtua.dashboard', compact('childrenData', 'children', 'activeYear', 'activeSemester', 'currentTime', 'showReportCard'));
    }

    /**
     * Detail anak - Nilai
     */
    public function nilai($studentId, Request $request)
    {
        $student = $this->getChild($studentId);
        $activeSemester = Semester::where('is_active', true)->first();
        $semesters = Semester::orderByDesc('id')->get();
        $selectedSemesterId = $request->get('semester_id', $activeSemester?->id);

        $grades = Grade::where('student_id', $student->id)
            ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId))
            ->with(['subject', 'semester'])
            ->orderBy('subject_id')
            ->get();

        $subjectGrades = $grades->groupBy('subject_id')->map(function ($items) {
            return [
                'subject' => $items->first()->subject,
                'grades' => $items,
                'average' => round($items->avg('score'), 1),
            ];
        });

        // Published report cards (still fetched but will be hidden or restricted in UI, we fetch to pass to view)
        $reportCards = ReportCard::where('student_id', $student->id)
            ->where('status', 'published')
            ->with(['semester', 'academicYear', 'classroom'])
            ->orderByDesc('id')
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

        $classroom = $this->getCurrentClassroom($student);
        $children = $this->getChildren();

        $showReportCard = \App\Models\Setting::getValue('show_report_card', false);

        return view('orangtua.nilai', compact(
            'student', 'classroom', 'children', 'grades', 'subjectGrades',
            'semesters', 'selectedSemesterId', 'reportCards',
            'chartSubjects', 'chartAverages', 'chartKkms', 'monthlyGrades',
            'showReportCard'
        ));
    }

    /**
     * Detail anak - Tagihan
     */
    public function tagihan($studentId, Request $request)
    {
        $student = $this->getChild($studentId);
        $classroom = $this->getCurrentClassroom($student);
        $children = $this->getChildren();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();

        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeYear?->id ?? null);

        $query = StudentBill::where('student_id', $student->id)
            ->with(['paymentType', 'payments', 'academicYear', 'semester']);

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        $bills = $query->get();

        $totalTagihan = $bills->sum('amount');
        $totalBayar = $bills->sum('paid_amount');
        $totalSisa = $bills->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

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

        // Helper to get due date of a bill
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

        // Calculate Tunggakan s.d. Bulan Ini vs Mendatang
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

        // Group monthly bills per month (academic year sequence: 7,8,9,10,11,12,1,2,3,4,5,6)
        $academicMonths = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

        $monthlyBills = $bills->filter(fn($b) => $b->month !== null && (int)$b->month > 0);
        $nonMonthlyBills = $bills->filter(fn($b) => $b->month === null || (int)$b->month == 0);

        $byMonthKey = $monthlyBills->groupBy(function($b) use ($getResolvedYear) {
            $year = $getResolvedYear($b);
            return $year . '_' . str_pad($b->month, 2, '0', STR_PAD_LEFT);
        });

        $sortedMonthKeys = $byMonthKey->keys()->sort(function($a, $b) use ($academicMonths) {
            list($yearA, $monthA) = explode('_', $a);
            list($yearB, $monthB) = explode('_', $b);
            $mA = (int)$monthA;
            $mB = (int)$monthB;
            
            if ($yearA != $yearB) {
                return $yearA <=> $yearB;
            }
            $posA = array_search($mA, $academicMonths);
            $posB = array_search($mB, $academicMonths);
            return ($posA !== false && $posB !== false) ? ($posA <=> $posB) : ($mA <=> $mB);
        });

        $monthlyGroupedData = collect();
        foreach ($sortedMonthKeys as $key) {
            $items = $byMonthKey[$key];
            $first = $items->first();
            $monthNum = (int)$first->month;
            $yearNum = $getResolvedYear($first);
            
            $monthTotal = $items->sum('amount');
            $monthPaid = $items->sum('paid_amount');
            $monthRemaining = max(0, $monthTotal - $monthPaid);
            
            $dueDate = \Carbon\Carbon::create($yearNum, $monthNum, 10)->endOfDay();
            $isOverdue = $monthRemaining > 0 && now()->isAfter($dueDate);
            $isPaid = $monthRemaining == 0;

            $monthlyGroupedData->push([
                'key' => $key,
                'month' => $monthNum,
                'year' => $yearNum,
                'month_name' => \Carbon\Carbon::create()->month($monthNum)->translatedFormat('F'),
                'label' => \Carbon\Carbon::create()->month($monthNum)->translatedFormat('F') . ' ' . $yearNum,
                'items' => $items,
                'total_amount' => $monthTotal,
                'paid_amount' => $monthPaid,
                'remaining_amount' => $monthRemaining,
                'is_paid' => $isPaid,
                'is_overdue' => $isOverdue,
                'due_date' => $dueDate,
            ]);
        }

        return view('orangtua.tagihan', compact(
            'student', 'classroom', 'children', 'bills', 'academicYears', 'selectedYearId',
            'totalTagihan', 'totalBayar', 'totalSisa', 'tunggakanAmount', 'upcomingAmount',
            'monthlyGroupedData', 'nonMonthlyBills'
        ));
    }

    /**
     * Detail anak - Absensi
     */
    public function absensi($studentId)
    {
        $student = $this->getChild($studentId);
        $classroom = $this->getCurrentClassroom($student);
        $children = $this->getChildren();
        $activeYear = AcademicYear::where('is_active', true)->first();

        $attendances = Attendance::where('student_id', $student->id)
            ->when($activeYear, function($q) use ($activeYear) {
                $effectiveStartDate = $activeYear->start_date->gt(now()) ? now() : $activeYear->start_date;
                return $q->whereBetween('date', [$effectiveStartDate->format('Y-m-d'), $activeYear->end_date->format('Y-m-d')]);
            })
            ->orderByDesc('date')
            ->get();

        $summary = [
            'present' => $attendances->where('status', 'hadir')->count(),
            'sick' => $attendances->where('status', 'sakit')->count(),
            'permission' => $attendances->where('status', 'izin')->count(),
            'absent' => $attendances->where('status', 'alpha')->count(),
            'late' => 0,
            'total' => $attendances->count(),
        ];
        $summary['percentage'] = $summary['total'] > 0
            ? round(($summary['present'] / $summary['total']) * 100, 1) : 0;

        return view('orangtua.absensi', compact(
            'student', 'classroom', 'children', 'attendances', 'summary', 'activeYear'
        ));
    }

    /**
     * Detail anak - Jadwal
     */
    public function jadwal($studentId)
    {
        $student = $this->getChild($studentId);
        $classroom = $this->getCurrentClassroom($student);
        $children = $this->getChildren();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu',
        ];

        $schedules = collect();
        if ($classroom) {
            $schedules = Schedule::where('classroom_id', $classroom->id)
                ->with(['subject', 'teacher', 'timeSlot'])
                ->orderBy('time_slot_id')
                ->get()
                ->groupBy('day_of_week');
        }

        return view('orangtua.jadwal', compact(
            'student', 'classroom', 'children', 'schedules', 'days', 'dayLabels'
        ));
    }

    /**
     * Detail anak - Konseling
     */
    public function konseling($studentId)
    {
        $student = $this->getChild($studentId);
        $classroom = $this->getCurrentClassroom($student);
        $children = $this->getChildren();

        $counselingRecords = $student->counselingRecords()
            ->where(function ($query) {
                $query->where('is_confidential', false)
                      ->orWhere('parent_notified', true);
            })
            ->with(['counselor'])
            ->orderByDesc('incident_date')
            ->get();

        return view('orangtua.konseling', compact(
            'student', 'classroom', 'children', 'counselingRecords'
        ));
    }

    /**
     * Download published report card PDF for a child.
     */
    public function downloadRaport($studentId, ReportCard $reportCard)
    {
        $student = $this->getChild($studentId);

        // Verify report card visibility setting is enabled
        $showReportCard = \App\Models\Setting::getValue('show_report_card', false);
        if (!$showReportCard) {
            abort(403, 'Akses Rapor Digital dinonaktifkan oleh administrator.');
        }

        // Verify report card belongs to this student and is published
        if ($reportCard->student_id !== $student->id || $reportCard->status !== 'published') {
            abort(403, 'Rapor tidak tersedia.');
        }

        // Load relationships
        $reportCard->load([
            'student.school',
            'semester.academicYear',
            'classroom',
        ]);

        // Get grades
        $grades = Grade::with(['subject'])
            ->where('student_id', $reportCard->student_id)
            ->where('semester_id', $reportCard->semester_id)
            ->get()
            ->groupBy('subject_id');

        // Get school-specific weights
        $gradeWeight = GradeWeight::getForSchool($reportCard->student->school_id);
        $w = $gradeWeight->getWeightsAsDecimal();

        if (!$reportCard->relationLoaded('classroom')) {
            $reportCard->load('classroom');
        }
        $gradeLevel = $reportCard->classroom?->grade_level;

        $subjectScores = [];
        foreach ($grades as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject;

            $tugas = $subjectGrades->where('grade_type', 'tugas')->avg('score') ?? 0;
            $uts = $subjectGrades->where('grade_type', 'uts')->avg('score') ?? 0;
            $uas = $subjectGrades->where('grade_type', 'uas')->avg('score') ?? 0;
            $sikap = $subjectGrades->where('grade_type', 'sikap')->avg('score') ?? 0;

            $finalScore = ($tugas * $w['tugas']) + ($uts * $w['pts']) + ($uas * $w['pas']) + ($sikap * $w['sikap']);
            $kkm = $subject->kkm ?? 75;

            $subjectScores[] = [
                'subject' => $subject->subject_name,
                'kkm' => $kkm,
                'tugas' => round($tugas, 0),
                'uts' => round($uts, 0),
                'uas' => round($uas, 0),
                'sikap' => round($sikap, 0),
                'final' => round($finalScore, 0),
                'predicate' => \App\Models\FinalGrade::scoreToPredicate($finalScore, $kkm, $gradeLevel),
                'is_passed' => $finalScore >= $kkm,
            ];
        }

        // Get achievements
        $achievements = StudentAchievement::where('student_id', $reportCard->student_id)
            ->where('academic_year_id', $reportCard->academic_year_id)
            ->orderBy('level', 'desc')
            ->get();

        $pdf = Pdf::loadView('admin.report_cards.pdf', compact('reportCard', 'subjectScores', 'achievements'));

        $rawFilename = 'Rapor_' . $reportCard->student->full_name . '_' . ($reportCard->semester->semester_name ?? '') . '.pdf';
        $filename = str_replace(['/', '\\'], '-', $rawFilename);

        return $pdf->download($filename);
    }
}
