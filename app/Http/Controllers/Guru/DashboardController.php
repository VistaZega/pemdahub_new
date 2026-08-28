<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentBill;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use HasMultiSchool;

    /**
     * Get the authenticated teacher record.
     */
    private function getTeacher()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if ($teacher) {
            return $teacher;
        }

        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            $employee = \App\Models\Employee::create([
                'school_id' => $user->school_id ?? 1,
                'user_id' => $user->id,
                'employee_code' => 'PGW-'.$user->id,
                'full_name' => $user->name,
                'gender' => 'L',
                'employee_type' => 'other',
                'employment_status' => 'yayasan',
                'is_active' => true,
            ]);
        }

        return Teacher::firstOrCreate(
            ['user_id' => $user->id],
            [
                'employee_id' => $employee->id,
                'school_id' => $employee->school_id ?? $user->school_id ?? 1,
                'teacher_code' => $employee->employee_code ?? 'PGW-'.$user->id,
                'full_name' => $employee->full_name ?? $user->name,
                'gender' => $employee->gender ?? 'L',
                'birth_place' => $employee->birth_place ?? '-',
                'is_active' => $employee->is_active ?? true,
            ]
        );
    }

    /**
     * Get active academic year.
     */
    private function getActiveYear()
    {
        return AcademicYear::where('is_active', true)->first();
    }

    /**
     * Get active semester.
     */
    private function getActiveSemester()
    {
        return Semester::where('is_active', true)->first();
    }

    /**
     * Get classrooms the teacher is assigned to (via schedules or teaching assignments).
     */
    private function getTeacherClassrooms(Teacher $teacher, ?AcademicYear $activeYear, ?int $schoolId = null)
    {
        if (!$activeYear) return collect();

        return Classroom::where('is_active', true)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where(function ($yearQ) use ($activeYear) {
                $yearQ->where('academic_year_id', $activeYear->id)
                      ->orWhereNull('academic_year_id');
            })
            ->where(function ($q) use ($teacher) {
                $q->whereHas('schedules', function ($sq) use ($teacher) {
                    $sq->where('teacher_id', $teacher->id);
                })
                ->orWhereHas('teachingAssignments', function ($tq) use ($teacher) {
                    $tq->where('teacher_id', $teacher->id)
                       ->where('is_active', true);
                })
                ->orWhere('homeroom_teacher_id', $teacher->id)
                ->orWhereHas('lmsClasses', function ($lq) use ($teacher) {
                    $lq->where('status', 'active')
                       ->whereHas('course', function ($cq) use ($teacher) {
                           $cq->where('teacher_id', $teacher->id);
                       });
                });
            })
            ->with('school')
            ->withCount(['students' => function ($q) use ($activeYear) {
                $q->whereIn('student_classes.status', ['aktif', 'enrolled', 'active']);
                if ($activeYear) {
                    $q->where('student_classes.academic_year_id', $activeYear->id);
                }
            }])
            ->orderBy('class_name')
            ->get();
    }

    /**
     * Dashboard Guru - Ringkasan utama
     */
    public function index()
    {
        $teacher = $this->getTeacher();
        $teacher->load('school');
        $activeYear = $this->getActiveYear();
        $activeSemester = $this->getActiveSemester();

        // Multi-school support: data untuk school switcher
        $effectiveSchoolId = $this->getEffectiveSchoolId($teacher);
        $availableSchools = $this->getAvailableSchools();
        $activeSchoolName = $this->getActiveSchoolName();
        $isMultiSchool = $this->hasMultiSchoolAccess();

        // Kelas yang diampu (filter by active school jika multi-school)
        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear);
        if ($isMultiSchool && $effectiveSchoolId) {
            $classrooms = $classrooms->filter(fn($c) => $c->school_id == $effectiveSchoolId)->values();
        }
        $totalStudents = $classrooms->sum('students_count');

        // Jadwal hari ini (filter by active school jika multi-school)
        $todaySchedules = collect();
        $dayMap = [
            'Monday' => 'monday', 'Tuesday' => 'tuesday', 'Wednesday' => 'wednesday',
            'Thursday' => 'thursday', 'Friday' => 'friday', 'Saturday' => 'saturday',
        ];
        $today = $dayMap[now()->format('l')] ?? null;
        if ($today) {
            $todaySchedules = Schedule::where('teacher_id', $teacher->id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->when($isMultiSchool && $effectiveSchoolId, fn($q) => $q->where('school_id', $effectiveSchoolId))
                ->where('day_of_week', $today)
                ->with(['subject', 'classroom', 'timeSlot'])
                ->orderBy('time_slot_id')
                ->get();
        }

        // Group by time key to prevent repetition of same-time blocks (e.g. IPA/PJOK)
        $groupedTodaySchedules = $todaySchedules->groupBy(function ($s) {
            return ($s->timeSlot->start_time ?? $s->start_time) . '-' . ($s->timeSlot->end_time ?? $s->end_time);
        });

        // Current time & schedule detection for timeline
        $currentTime = now()->format('H:i');
        $currentSchedule = null;
        $nextSchedule = null;
        foreach ($groupedTodaySchedules as $timeKey => $schedulesAtTime) {
            $first = $schedulesAtTime->first();
            $start = $first->timeSlot->start_time ?? $first->start_time ?? null;
            $end = $first->timeSlot->end_time ?? $first->end_time ?? null;
            
            if ($start && $end && $currentTime >= $start && $currentTime <= $end) {
                $currentSchedule = $first; // Use first one as proxy for status
            }
            if ($start && $currentTime < $start && !$nextSchedule) {
                $nextSchedule = $first;
            }
        }

        // Jumlah nilai yang sudah diinput semester ini
        $gradesCount = 0;
        if ($activeSemester) {
            $gradesCount = Grade::where('teacher_id', $teacher->id)
                ->where('semester_id', $activeSemester->id)
                ->count();
        }

        // Kelas wali kelas
        $homeroomClassroom = null;
        if ($activeYear) {
            $homeroomClassroom = Classroom::where('homeroom_teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->withCount(['students' => function ($q) use ($activeYear) {
                    $q->whereIn('student_classes.status', ['aktif', 'enrolled', 'active'])
                      ->where('student_classes.academic_year_id', $activeYear->id);
                }])
                ->first();
        }

        // Tagihan Pembayaran Siswa Wali Kelas
        $homeroomBillingStats = null;
        if ($homeroomClassroom && $activeYear) {
            $studentIds = $homeroomClassroom->students()->pluck('students.id');
            $billsQuery = StudentBill::whereIn('student_id', $studentIds)
                ->where('academic_year_id', $activeYear->id);
            $bills = (clone $billsQuery)->get();
            $totalBills = $bills->count();
            $lunasCount = $bills->where('status', 'lunas')->count();
            $tunggakanCount = $bills->filter(fn($b) => $b->isOverdue())->count();
            
            // Only consider due bills for progress (Lunas + Overdue). Ignore future bills.
            $dueBillsCount = $lunasCount + $tunggakanCount;
            $percentage = $dueBillsCount > 0 ? round(($lunasCount / $dueBillsCount) * 100) : 0;
            
            $homeroomBillingStats = (object)[
                'total_bills' => $totalBills,
                'due_bills' => $dueBillsCount,
                'lunas_count' => $lunasCount,
                'belum_bayar_count' => $tunggakanCount,
                'percentage' => $percentage
            ];
        }

        // Total jadwal per minggu (fallback ke Pembagian Tugas Mengajar jika jam belum diplot ke tabel jadwal harian)
        $weeklyScheduleCount = Schedule::where('teacher_id', $teacher->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->when($isMultiSchool && $effectiveSchoolId, fn($q) => $q->where('school_id', $effectiveSchoolId))
            ->count();

        if ($weeklyScheduleCount === 0) {
            $weeklyScheduleCount = (int) TeachingAssignment::where('teacher_id', $teacher->id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->when($isMultiSchool && $effectiveSchoolId, fn($q) => $q->whereHas('classroom', fn($cq) => $cq->where('school_id', $effectiveSchoolId)))
                ->where('is_active', true)
                ->sum('hours_per_week');
        }

        // Reputation & Elite standing
        $reputation = $teacher->user->reputation ?? \App\Models\Reputation::firstOrCreate(
            ['user_id' => $teacher->user_id],
            ['total_points' => 0, 'level' => 1, 'current_streak' => 0]
        );
        $reputationLogs = $teacher->user ? $teacher->user->reputationLogs()->latest()->take(5)->get() : collect();
        $rank = \App\Models\Reputation::where('total_points', '>', $reputation->total_points ?? 0)->count() + 1;

        // Foundation Letters / Surat Edaran Yayasan (Filter khusus target penerima & max 7 hari di dashboard)
        $foundationLetters = \App\Models\FoundationLetter::where('status', 'published')
            ->forUser(Auth::user(), 'guru_pegawai')
            ->recent(7)
            ->orderBy('effective_date', 'desc')
            ->take(3)
            ->get();

        // Today Employee Attendance for teacher
        $employee = $teacher->employee;
        $todayAttendance = null;
        if ($employee) {
            $todayAttendance = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();
        }

        // PKL Supervisor stats for teacher
        $pklSupervisorHours = $teacher->getPklSupervisorHours($activeYear?->id);
        $pklPlacementsCount = \App\Models\PklPlacement::where('teacher_id', $teacher->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        return view('guru.dashboard', compact(
            'teacher', 'activeYear', 'activeSemester', 'classrooms',
            'totalStudents', 'todaySchedules', 'groupedTodaySchedules', 'gradesCount',
            'homeroomClassroom', 'homeroomBillingStats', 'weeklyScheduleCount',
            'currentTime', 'currentSchedule', 'nextSchedule',
            'reputation', 'reputationLogs', 'rank', 'foundationLetters',
            'availableSchools', 'activeSchoolName', 'isMultiSchool', 'effectiveSchoolId',
            'todayAttendance', 'pklSupervisorHours', 'pklPlacementsCount'
        ));
    }

    /**
     * Jadwal Mengajar - Compact Weekly Timetable Grid
     */
    public function jadwal()
    {
        $teacher = $this->getTeacher();
        $teacher->load('school');

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu',
        ];
        $dayShortLabels = [
            'monday' => 'Sen', 'tuesday' => 'Sel', 'wednesday' => 'Rab',
            'thursday' => 'Kam', 'friday' => 'Jum', 'saturday' => 'Sab',
        ];

        $activeYear = $this->getActiveYear();
        
        $totalPenugasanCount = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();
            
        $totalPenugasanHours = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->sum('hours_per_week');
            
        $jabatanList = collect();
        if ($teacher->employee) {
            $jabatanList = $teacher->employee->activePositions()->get();
        }
        $jabatanString = $jabatanList->isEmpty() ? 'Guru Mata Pelajaran / Tidak ada jabatan struktural' : $jabatanList->pluck('display_name')->implode(', ');

        $allSchedules = Schedule::where('teacher_id', $teacher->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->with(['subject', 'classroom', 'timeSlot'])
            ->orderBy('time_slot_id')
            ->get();

        // Weekly statistics
        $totalSessions = $allSchedules->count();
        $totalJP = $allSchedules->sum('duration_slots') ?: $allSchedules->count();
        $uniqueClassrooms = $allSchedules->pluck('classroom_id')->unique()->count();
        $uniqueSubjects = $allSchedules->pluck('subject_id')->unique()->count();

        // Get unique time slots used in the schedules, sorted by slot_order
        $timeSlotIds = $allSchedules->pluck('time_slot_id')->unique()->filter();
        
        $usedSlots = \App\Models\TimeSlot::whereIn('id', $timeSlotIds)->get();
        $minOrder = $usedSlots->min('slot_order');
        $maxOrder = $usedSlots->max('slot_order');
        
        foreach ($allSchedules as $s) {
            if ($s->timeSlot && $s->duration_slots > 1) {
                $endOrder = $s->timeSlot->slot_order + ($s->duration_slots - 1);
                if ($endOrder > $maxOrder) {
                    $maxOrder = $endOrder;
                }
            }
        }

        if ($minOrder !== null && $maxOrder !== null) {
            $timeSlots = \App\Models\TimeSlot::where('school_id', $this->getEffectiveSchoolId($teacher))
                ->whereBetween('slot_order', [$minOrder, $maxOrder])
                ->orderBy('slot_order')
                ->get()
                ->unique(function ($slot) {
                    return $slot->start_time . '-' . $slot->end_time;
                });
        } else {
            $timeSlots = collect();
        }

        // Determine which days actually have schedules
        $activeDays = collect($days)->filter(function ($day) use ($allSchedules) {
            return $allSchedules->where('day_of_week', $day)->count() > 0;
        })->values()->all();

        // Map time slots to their orders for easy lookup
        $slotOrderMap = $timeSlots->pluck('slot_order', 'id')->all();
        $orderToSlotId = $timeSlots->pluck('id', 'slot_order')->all();

        // Build timetable grid: [slot_order][day] = schedule
        $timetable = [];
        $occupied = []; // Track which cells are occupied by a multi-slot schedule

        // Group schedules by day and then by time key for faster lookup & aggregate combined classrooms
        $schedulesByDayAndTime = [];
        $combinedClassesMap = [];
        foreach ($allSchedules as $s) {
            $timeKey = ($s->timeSlot->start_time ?? $s->start_time) . '-' . ($s->timeSlot->end_time ?? $s->end_time);
            $className = $s->classroom->class_name ?? '';
            
            if (!isset($schedulesByDayAndTime[$s->day_of_week][$timeKey])) {
                $schedulesByDayAndTime[$s->day_of_week][$timeKey] = $s;
                $combinedClassesMap[$s->day_of_week][$timeKey] = collect();
            }
            
            if ($className && !$combinedClassesMap[$s->day_of_week][$timeKey]->contains($className)) {
                $combinedClassesMap[$s->day_of_week][$timeKey]->push($className);
            }
        }

        foreach ($schedulesByDayAndTime as $day => $times) {
            foreach ($times as $timeKey => $sched) {
                if (isset($combinedClassesMap[$day][$timeKey]) && $combinedClassesMap[$day][$timeKey]->count() > 0) {
                    $sched->classroom_name_display = $combinedClassesMap[$day][$timeKey]->implode(', ');
                }
            }
        }

        foreach ($timeSlots as $slot) {
            $order = $slot->slot_order;
            $timeKey = $slot->start_time . '-' . $slot->end_time;
            
            foreach ($activeDays as $day) {
                // If this cell is already occupied by a continuation of a previous schedule, skip
                if (isset($occupied[$day][$order])) continue;

                // Lookup schedule by day and the time key (string) instead of just slot_id
                $schedule = $schedulesByDayAndTime[$day][$timeKey] ?? null;

                if ($schedule) {
                    $timetable[$order][$day] = $schedule;
                    
                    // Mark subsequent slots as occupied if duration > 1
                    $duration = $schedule->duration_slots ?? 1;
                    if ($duration > 1) {
                        for ($i = 1; $i < $duration; $i++) {
                            $occupied[$day][$order + $i] = true;
                        }
                    }
                } else {
                    $timetable[$order][$day] = null;
                }
            }
        }

        // Assign colors to subjects for consistent coloring
        $subjectColors = [];
        $colorPalette = [
            ['bg' => 'bg-blue-100', 'border' => 'border-blue-300', 'text' => 'text-blue-800', 'sub' => 'text-blue-600'],
            ['bg' => 'bg-emerald-100', 'border' => 'border-emerald-300', 'text' => 'text-emerald-800', 'sub' => 'text-emerald-600'],
            ['bg' => 'bg-purple-100', 'border' => 'border-purple-300', 'text' => 'text-purple-800', 'sub' => 'text-purple-600'],
            ['bg' => 'bg-amber-100', 'border' => 'border-amber-300', 'text' => 'text-amber-800', 'sub' => 'text-amber-600'],
            ['bg' => 'bg-rose-100', 'border' => 'border-rose-300', 'text' => 'text-rose-800', 'sub' => 'text-rose-600'],
            ['bg' => 'bg-cyan-100', 'border' => 'border-cyan-300', 'text' => 'text-cyan-800', 'sub' => 'text-cyan-600'],
            ['bg' => 'bg-indigo-100', 'border' => 'border-indigo-300', 'text' => 'text-indigo-800', 'sub' => 'text-indigo-600'],
            ['bg' => 'bg-orange-100', 'border' => 'border-orange-300', 'text' => 'text-orange-800', 'sub' => 'text-orange-600'],
            ['bg' => 'bg-teal-100', 'border' => 'border-teal-300', 'text' => 'text-teal-800', 'sub' => 'text-teal-600'],
            ['bg' => 'bg-pink-100', 'border' => 'border-pink-300', 'text' => 'text-pink-800', 'sub' => 'text-pink-600'],
        ];
        $colorIndex = 0;
        foreach ($allSchedules->pluck('subject_id')->unique() as $subjectId) {
            $subjectColors[$subjectId] = $colorPalette[$colorIndex % count($colorPalette)];
            $colorIndex++;
        }

        return view('guru.jadwal', compact(
            'teacher', 'days', 'dayLabels', 'dayShortLabels', 'activeDays',
            'totalSessions', 'totalJP', 'uniqueClassrooms', 'uniqueSubjects',
            'timeSlots', 'timetable', 'subjectColors',
            'totalPenugasanCount', 'totalPenugasanHours', 'jabatanString'
        ));
    }

    /**
     * Kelas Saya - Daftar kelas yang diampu
     */
    public function kelas()
    {
        $teacher = $this->getTeacher();
        $teacher->load('school');
        $activeYear = $this->getActiveYear();
        $effectiveSchoolId = $this->getEffectiveSchoolId($teacher);
        $isMultiSchool = $this->hasMultiSchoolAccess();

        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear, $isMultiSchool ? $effectiveSchoolId : null);

        // Kelas wali kelas
        $homeroomClassroom = Classroom::where('homeroom_teacher_id', $teacher->id)
            ->when($isMultiSchool && $effectiveSchoolId, fn($q) => $q->where('school_id', $effectiveSchoolId))
            ->where('is_active', true)
            ->first();

        return view('guru.kelas', compact('teacher', 'classrooms', 'activeYear', 'homeroomClassroom'));
    }

    /**
     * Siswa Per Kelas - Detail siswa dalam satu kelas
     */
    public function siswaKelas($classroomId)
    {
        $teacher = $this->getTeacher();
        $teacher->load('school');
        $activeYear = $this->getActiveYear();
        $classroom = Classroom::with(['school', 'students' => function ($q) use ($activeYear) {
            $q->whereIn('student_classes.status', ['aktif', 'enrolled', 'active']);
            if ($activeYear) {
                $q->where('student_classes.academic_year_id', $activeYear->id);
            }
            $q->orderBy('full_name');
        }])->findOrFail($classroomId);

        // Verify teacher has access to this classroom
        $hasAccess = Schedule::where('teacher_id', $teacher->id)
            ->where('classroom_id', $classroom->id)
            ->exists()
            || TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('classroom_id', $classroom->id)
                ->where('is_active', true)
                ->exists()
            || $classroom->homeroom_teacher_id === $teacher->id;

        if (!$hasAccess) {
            abort(403, 'Anda tidak mengajar di kelas ini.');
        }

        return view('guru.siswa-kelas', compact('teacher', 'classroom'));
    }

    /**
     * Nilai - Lihat & kelola nilai per kelas/mata pelajaran
     */
    public function nilai(Request $request)
    {
        ini_set('memory_limit', '768M'); // safety net: fix utama adalah filter kelas wajib di bawah
        $teacher = $this->getTeacher();
        $teacher->load('school');
        $activeYear = $this->getActiveYear();
        $activeSemester = $this->getActiveSemester();
        $semesters = Semester::select('id', 'semester_name', 'semester_number', 'academic_year_id')
            ->with(['academicYear:id,year'])
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderByDesc('id')
            ->get();

        $selectedSemesterId = $request->get('semester_id');
        if (!$selectedSemesterId) {
            $selectedSemesterId = ($activeSemester && $semesters->contains('id', $activeSemester->id))
                ? $activeSemester->id
                : ($semesters->first()?->id ?? null);
        }

        $effectiveSchoolId = $this->getEffectiveSchoolId($teacher);
        $isMultiSchool = $this->hasMultiSchoolAccess();

        // Get classrooms taught by the teacher (filtered by effective school)
        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear, $isMultiSchool ? $effectiveSchoolId : null);

        // Get classroom filter
        $selectedClassroomId = $request->get('classroom_id');

        // Jika tidak ada kelas dipilih, auto-pilih jika hanya ada 1 kelas.
        // Jika ada banyak kelas & tidak ada yang dipilih → kembalikan data kosong
        // untuk menghindari memuat RIBUAN nilai sekaligus (penyebab memory exhausted).
        if (!$selectedClassroomId) {
            if ($classrooms->count() === 1) {
                $selectedClassroomId = $classrooms->first()->id;
            } else {
                return view('guru.nilai', [
                    'teacher'             => $teacher,
                    'subjectGrades'       => collect(),
                    'semesters'           => $semesters,
                    'selectedSemesterId'  => $selectedSemesterId,
                    'classrooms'          => $classrooms,
                    'selectedClassroomId' => null,
                ]);
            }
        }

        // Nilai yang sudah diinput oleh guru ini, wajib difilter per kelas
        $grades = Grade::select('id', 'student_id', 'subject_id', 'semester_id', 'teacher_id', 'grade_type', 'score', 'notes', 'is_remedial', 'lms_source_type', 'created_at')
            ->where('teacher_id', $teacher->id)
            ->where('semester_id', $selectedSemesterId)
            ->whereIn('student_id', function ($sub) use ($selectedClassroomId, $activeYear) {
                $sub->select('student_id')
                    ->from('student_classes')
                    ->where('classroom_id', $selectedClassroomId)
                    ->whereIn('status', ['aktif', 'enrolled', 'active'])
                    ->when($activeYear, fn($sq) => $sq->where('academic_year_id', $activeYear->id));
            })
            ->orderBy('subject_id')
            ->orderBy('student_id')
            ->get();

        // Eager load unique students and subjects separately to prevent massive object duplication in memory
        $studentIds = $grades->pluck('student_id')->unique()->toArray();
        $students = collect();
        if (!empty($studentIds)) {
            $students = Student::select('id', 'full_name', 'nisn', 'nis', 'photo', 'user_id')
                ->with(['classrooms' => fn($q) => $q->select('classrooms.id', 'classrooms.class_name')->where('student_classes.academic_year_id', $activeYear?->id)])
                ->whereIn('id', $studentIds)
                ->get()
                ->keyBy('id');
        }

        $subjectIds = $grades->pluck('subject_id')->unique()->toArray();
        $subjects = collect();
        if (!empty($subjectIds)) {
            $subjects = \App\Models\Subject::select('id', 'name', 'subject_name', 'subject_code', 'kkm')
                ->whereIn('id', $subjectIds)
                ->get()
                ->keyBy('id');
        }

        // Group by subject → then by student → pivot grade types into columns
        $gradeWeight = \App\Models\GradeWeight::getForSchool($teacher->school_id);
        $weights = $gradeWeight->getWeightsAsDecimal();

        $subjectGrades = $grades->groupBy('subject_id')->map(function ($subjectItems, $subjId) use ($weights, $students, $subjects) {
            $subject = $subjects->get($subjId);
            if (!$subject) return null;

            $studentRows = $subjectItems->groupBy('student_id')->map(function ($studentGrades, $studId) use ($weights, $students) {
                $student = $students->get($studId);
                if (!$student) return null;

                $tugas = $studentGrades->where('grade_type', 'tugas');
                $utsItems = $studentGrades->where('grade_type', 'uts');
                $uasItems = $studentGrades->where('grade_type', 'uas');
                $sikapItems = $studentGrades->where('grade_type', 'sikap');

                $tugasAvg = $tugas->count() > 0 ? round($tugas->avg('score'), 1) : null;
                $utsAvg = $utsItems->count() > 0 ? round($utsItems->avg('score'), 1) : null;
                $uasAvg = $uasItems->count() > 0 ? round($uasItems->avg('score'), 1) : null;
                $sikapAvg = $sikapItems->count() > 0 ? round($sikapItems->avg('score'), 1) : null;

                $finalScore = null;
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

                return [
                    'student'      => $student,
                    'tugas_grades' => $tugas, // collection (bisa >1)
                    'tugas_avg'    => $tugasAvg,
                    'uts'          => $utsItems->first(),
                    'uts_avg'      => $utsAvg,
                    'uas'          => $uasItems->first(),
                    'uas_avg'      => $uasAvg,
                    'sikap'        => $sikapItems->first(),
                    'sikap_avg'    => $sikapAvg,
                    'final_score'  => $finalScore,
                    'all_grades'   => $studentGrades, // for edit/delete
                ];
            })->filter()->sortBy('student.full_name')->values();

            // Stats
            $allScores = $subjectItems->pluck('score');

            return [
                'subject'       => $subject,
                'students'      => $studentRows,
                'average'       => round($allScores->avg(), 1),
                'student_count' => $studentRows->count(),
                'grade_count'   => $subjectItems->count(),
            ];
        })->filter();

        return view('guru.nilai', compact(
            'teacher', 'subjectGrades', 'semesters',
            'selectedSemesterId', 'classrooms', 'selectedClassroomId'
        ));
    }

    /**
     * Get grade details for a student and subject via AJAX.
     */
    public function gradeDetails(Request $request)
    {
        $teacher = $this->getTeacher();
        $studentId = $request->get('student_id');
        $subjectId = $request->get('subject_id');
        $semesterId = $request->get('semester_id');

        $grades = Grade::where('teacher_id', $teacher->id)
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->id,
                    'type' => $g->getGradeTypeLabel(),
                    'score' => $g->score,
                    'notes' => $g->notes,
                    'is_remedial' => $g->is_remedial,
                    'is_lms' => $g->isFromLms(),
                    'created' => $g->created_at ? $g->created_at->format('d/m/Y H:i') : null,
                ];
            });

        return response()->json($grades);
    }

    /**
     * Absensi - Rekap kehadiran siswa di kelas guru
     */
    public function absensi(Request $request)
    {
        $teacher = $this->getTeacher();
        $activeYear = $this->getActiveYear();
        $effectiveSchoolId = $this->getEffectiveSchoolId($teacher);
        $isMultiSchool = $this->hasMultiSchoolAccess();

        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear, $isMultiSchool ? $effectiveSchoolId : null);

        $selectedClassroomId = $request->get('classroom_id');
        $selectedMonth = (int) $request->get('month', date('n'));
        $selectedYear = (int) $request->get('year', date('Y'));
        
        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $attendances = collect();
        $classroomStudents = collect();
        $matrixMap = [];
        $studentStats = [];
        $lessonMatrixMap = [];
        $lessonStudentStats = [];
        $wajibStudentIds = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

        $summary = [
            'present' => 0, 'sick' => 0, 'permission' => 0,
            'absent' => 0, 'total' => 0, 'percentage' => 0,
        ];
        $selectedClassroom = null;
        $assignmentInfo = null;
        $lessonDates = [];
        $selectedInputDate = $request->input('input_date', date('Y-m-d'));
        $isTodayScheduled = false;

        if ($selectedClassroomId) {
            $selectedClassroom = $classrooms->firstWhere('id', $selectedClassroomId);
            if ($selectedClassroom) {
                // Fetch active students in class for the daily matrix (applies to all students)
                $studentsQuery = $selectedClassroom->students()->wherePivot('status', 'aktif');
                if ($activeYear) {
                    $studentsQuery->wherePivot('academic_year_id', $activeYear->id);
                }
                $classroomStudents = $studentsQuery->with(['applicant', 'user'])->orderBy('full_name')->get();

                // Fetch monthly attendances for matrix grid (Daily School Attendance ONLY)
                $monthlyAttendances = Attendance::where('classroom_id', $selectedClassroomId)
                    ->whereYear('date', $selectedYear)
                    ->whereMonth('date', $selectedMonth)
                    ->whereNull('schedule_id')
                    ->get();

                foreach ($monthlyAttendances as $att) {
                    $dayNum = (int) \Carbon\Carbon::parse($att->date)->format('j');
                    $matrixMap[$att->student_id][$dayNum] = $att->status;
                }

                // Calculate per-student totals for the month
                foreach ($classroomStudents as $st) {
                    $stAtts = $monthlyAttendances->where('student_id', $st->id);
                    $h = $stAtts->where('status', 'hadir')->count();
                    $s = $stAtts->where('status', 'sakit')->count();
                    $i = $stAtts->where('status', 'izin')->count();
                    $a = $stAtts->where('status', 'alpha')->count();
                    $tot = $stAtts->count();
                    $pct = $tot > 0 ? round(($h / $tot) * 100, 1) : 0;

                    $studentStats[$st->id] = [
                        'hadir' => $h, 'sakit' => $s, 'izin' => $i, 'alpha' => $a,
                        'total' => $tot, 'percentage' => $pct
                    ];
                }

                // Determine Students for Teacher in this Classroom
                $assignments = \App\Models\TeachingAssignment::with(['subject', 'classroom'])
                    ->where('teacher_id', $teacher->id)
                    ->where(function($q) use ($selectedClassroomId, $activeYear) {
                        $q->where('classroom_id', $selectedClassroomId)
                          ->orWhereHas('schedules', function($sq) use ($selectedClassroomId, $activeYear) {
                              $sq->where('classroom_id', $selectedClassroomId);
                              if ($activeYear) {
                                  $sq->where('academic_year_id', $activeYear->id);
                              }
                          });
                    })
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                    ->where('is_active', true)
                    ->get();

                $wajibStudentIds = $classroomStudents->pluck('id')->toArray();

                // Recent attendance log list (Teacher's Class Attendance)
                $attendances = Attendance::where('classroom_id', $selectedClassroomId)
                    ->whereYear('date', $selectedYear)
                    ->whereMonth('date', $selectedMonth)
                    ->where('created_by', Auth::id())
                    ->with('student')
                    ->orderByDesc('date')
                    ->get();

                // Calculate Lesson Matrix & Stats
                foreach ($attendances as $att) {
                    $dayNum = (int) \Carbon\Carbon::parse($att->date)->format('j');
                    $lessonMatrixMap[$att->student_id][$dayNum] = $att->status;
                }
                
                foreach ($classroomStudents as $st) {
                    $stAtts = $attendances->where('student_id', $st->id);
                    $h = $stAtts->where('status', 'hadir')->count();
                    $s = $stAtts->where('status', 'sakit')->count();
                    $i = $stAtts->where('status', 'izin')->count();
                    $a = $stAtts->where('status', 'alpha')->count();
                    $tot = $stAtts->count();
                    $pct = $tot > 0 ? round(($h / $tot) * 100, 1) : 0;

                    $lessonStudentStats[$st->id] = [
                        'hadir' => $h, 'sakit' => $s, 'izin' => $i, 'alpha' => $a,
                        'total' => $tot, 'percentage' => $pct
                    ];
                }

                // Get dynamic dates: Scheduled dates + any dates with actual attendance
                $schedulesQuery = \App\Models\Schedule::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $selectedClassroomId);
                if ($activeYear) {
                    $schedulesQuery->where('academic_year_id', $activeYear->id);
                }
                $scheduledDays = $schedulesQuery->pluck('day_of_week')->map(fn($day) => strtolower(trim($day)))->unique()->toArray();
                
                $calculatedDates = [];
                $scheduledFullDates = [];
                
                if (!empty($scheduledDays)) {
                    $startOfMonth = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1);
                    $endOfMonth = $startOfMonth->copy()->endOfMonth();
                    
                    for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
                        $dayNameStr = strtolower($date->format('l'));
                        if (in_array($dayNameStr, $scheduledDays)) {
                            $calculatedDates[] = $date->day;
                            $scheduledFullDates[] = $date->format('Y-m-d');
                        }
                    }
                }
                
                // Smart Default Selected Input Date based on Teacher Schedule
                if ($request->has('input_date')) {
                    $selectedInputDate = $request->input('input_date');
                } else {
                    $todayStr = date('Y-m-d');
                    if (in_array($todayStr, $scheduledFullDates)) {
                        $selectedInputDate = $todayStr;
                    } elseif (!empty($scheduledFullDates)) {
                        // Cari tanggal jadwal mengajar terakhir yang <= hari ini, atau tanggal mengajar pertama
                        $pastOrTodayScheduled = array_filter($scheduledFullDates, fn($d) => $d <= $todayStr);
                        if (!empty($pastOrTodayScheduled)) {
                            $selectedInputDate = end($pastOrTodayScheduled);
                        } else {
                            $selectedInputDate = $scheduledFullDates[0];
                        }
                    } else {
                        $selectedInputDate = $todayStr;
                    }
                }

                // Hitung Rotasi Sistem Blok SMK pada Tanggal Terpilih:
                // Konvensi SMK Swasta Pembda:
                // Normal (Periode 1, 3, 5): Grup A = Ruang Kelas (Teori), Grup B = Ruang Lab (Praktik)
                // Ditukar (Swapped / Periode 2, 4, 6): Grup A = Ruang Lab (Praktik), Grup B = Ruang Kelas (Teori)
                $studentBlockGroups = \App\Models\BlockStudentGroup::where('classroom_id', $selectedClassroomId)
                    ->pluck('group', 'student_id')
                    ->toArray();

                $schoolId = $selectedClassroom?->school_id;
                $blockSchedule = \App\Models\BlockSchedule::where(function($q) use ($schoolId) {
                        if ($schoolId) {
                            $q->where('school_id', $schoolId);
                        }
                    })
                    ->where('academic_year_id', $activeYear?->id)
                    ->where('is_active', true)
                    ->first() ?: \App\Models\BlockSchedule::where('academic_year_id', $activeYear?->id)->where('is_active', true)->first();

                $rotation = $blockSchedule ? $blockSchedule->getActiveRotationForDate($selectedInputDate) : 'normal';

                $targetGroup = null;
                $primaryAssignment = $assignments->first();
                if ($primaryAssignment && in_array($primaryAssignment->block_type, ['all', 'split'])) {
                    if ($primaryAssignment->block_type === 'split') {
                        // Mapel Praktik / Lab
                        $targetGroup = ($rotation === 'normal') ? 'B' : 'A';
                    } else {
                        // Mapel Teori / Kelas ('all')
                        $targetGroup = ($rotation === 'normal') ? 'A' : 'B';
                    }
                }

                // Tentukan Siswa yang Terjadwal di Kelas pada Hari/Tanggal Ini
                $scheduledStudentIds = [];
                if ($targetGroup && !empty($studentBlockGroups)) {
                    foreach ($classroomStudents as $st) {
                        if (($studentBlockGroups[$st->id] ?? null) === $targetGroup) {
                            $scheduledStudentIds[] = $st->id;
                        }
                    }
                } else {
                    $scheduledStudentIds = $classroomStudents->pluck('id')->toArray();
                }

                // Buat Label Penugasan Mengajar
                if ($assignments->isNotEmpty()) {
                    $infoList = [];
                    foreach ($assignments as $assignment) {
                        $subjName = $assignment->subject->name ?? 'Mata Pelajaran';
                        if ($assignment->block_type === 'split') {
                            $grpLabel = ($targetGroup === 'A') ? 'Grup A (Ruang Lab)' : 'Grup B (Ruang Lab)';
                            $infoList[] = "{$subjName} (Blok Praktik - {$grpLabel})";
                        } elseif ($assignment->block_type === 'all') {
                            $grpLabel = ($targetGroup === 'A') ? 'Grup A (Ruang Kelas)' : 'Grup B (Ruang Kelas)';
                            $infoList[] = "{$subjName} (Blok Teori - {$grpLabel})";
                        } else {
                            $infoList[] = $subjName;
                        }
                    }
                    $assignmentInfo = implode(' | ', array_unique($infoList));
                } else {
                    $assignmentInfo = 'Reguler';
                }

                $inputCarbon = \Carbon\Carbon::parse($selectedInputDate);
                
                // Jika input_date di bulan & tahun yang sama, pastikan tanggal tersebut ada di $lessonDates
                if ((int)$inputCarbon->format('n') === (int)$selectedMonth && (int)$inputCarbon->format('Y') === (int)$selectedYear) {
                    $calculatedDates[] = (int)$inputCarbon->format('j');
                }
                
                $attendanceDates = $attendances->pluck('date')
                    ->map(fn($d) => (int)\Carbon\Carbon::parse($d)->format('j'))
                    ->toArray();
                
                $lessonDates = collect(array_merge($calculatedDates, $attendanceDates))
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();

                $isTodayScheduled = in_array(date('Y-m-d'), $scheduledFullDates);

                // Kehadiran Harian Sekolah pada Tanggal Terpilih (Default: Hari Ini)
                $selectedDailyDate = $request->get('daily_date', date('Y-m-d'));
                $dailySchoolAttendances = Attendance::where('classroom_id', $selectedClassroomId)
                    ->whereDate('date', $selectedDailyDate)
                    ->whereNull('schedule_id')
                    ->get()
                    ->keyBy('student_id');

                $totalActiveStudents = $classroomStudents->count();
                $dailyPresentCount = $dailySchoolAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
                $dailySickCount = $dailySchoolAttendances->where('status', 'sakit')->count();
                $dailyPermissionCount = $dailySchoolAttendances->where('status', 'izin')->count();
                $dailyAbsentCount = $dailySchoolAttendances->where('status', 'alpha')->count();
                $dailyUnscannedCount = max(0, $totalActiveStudents - ($dailyPresentCount + $dailySickCount + $dailyPermissionCount + $dailyAbsentCount));

                $dailySummary = [
                    'present' => $dailyPresentCount,
                    'sick' => $dailySickCount,
                    'permission' => $dailyPermissionCount,
                    'absent' => $dailyAbsentCount,
                    'unscanned' => $dailyUnscannedCount,
                    'total' => $totalActiveStudents,
                    'percentage' => $totalActiveStudents > 0 ? round(($dailyPresentCount / $totalActiveStudents) * 100, 1) : 0,
                ];

                $isHomeroom = $selectedClassroom && ($selectedClassroom->homeroom_teacher_id === $teacher->id);

                // Rekap Bulanan Kehadiran Harian Sekolah
                $monthlySummary = [
                    'present' => $monthlyAttendances->whereIn('status', ['hadir', 'terlambat'])->count(),
                    'sick' => $monthlyAttendances->where('status', 'sakit')->count(),
                    'permission' => $monthlyAttendances->where('status', 'izin')->count(),
                    'absent' => $monthlyAttendances->where('status', 'alpha')->count(),
                    'total' => $monthlyAttendances->count(),
                ];
                $monthlySummary['percentage'] = $monthlySummary['total'] > 0
                    ? round(($monthlySummary['present'] / $monthlySummary['total']) * 100, 1) : 0;

                // Filter Ringkasan Kartu Atas (Summary default)
                $summary = $monthlySummary;
            }
        } else {
            $selectedInputDate = date('Y-m-d');
            $selectedDailyDate = date('Y-m-d');
            $isTodayScheduled = false;
            $scheduledStudentIds = [];
            $studentBlockGroups = [];
            $targetGroup = null;
            $dailySummary = ['present' => 0, 'sick' => 0, 'permission' => 0, 'absent' => 0, 'unscanned' => 0, 'total' => 0, 'percentage' => 0];
            $dailySchoolAttendances = collect();
            $isHomeroom = false;
            $monthlySummary = ['present' => 0, 'sick' => 0, 'permission' => 0, 'absent' => 0, 'total' => 0, 'percentage' => 0];
        }

        return view('guru.absensi', compact(
            'teacher', 'classrooms', 'selectedClassroomId',
            'selectedClassroom', 'attendances', 'summary', 'activeYear',
            'selectedMonth', 'selectedYear', 'monthsList', 'daysInMonth',
            'classroomStudents', 'matrixMap', 'studentStats',
            'lessonMatrixMap', 'lessonStudentStats', 'wajibStudentIds',
            'assignmentInfo', 'lessonDates', 'selectedInputDate', 'isTodayScheduled',
            'scheduledStudentIds', 'studentBlockGroups', 'targetGroup',
            'dailySummary', 'dailySchoolAttendances', 'selectedDailyDate', 'isHomeroom', 'monthlySummary'
        ));
    }

    /**
     * Cetak Rekap Absensi Siswa
     */
    public function printRekap(Request $request)
    {
        // Panggil method absensi() untuk mendapatkan semua data yang sama
        $view = $this->absensi($request);
        
        // Render menggunakan view khusus cetak
        return view('guru.absensi-print', $view->getData());
    }

    /**
     * Absensi Saya - Rekap kehadiran guru itu sendiri
     */
    public function absensiSaya(Request $request)
    {
        $teacher = $this->getTeacher();
        $employee = $teacher->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        
        // Ambil jadwal hari mengajar guru di tahun ajaran aktif
        $activeYearObj = \App\Models\AcademicYear::where('is_active', true)->first();
        $teachingDays = $teacher->schedules()
            ->when($activeYearObj, fn($q) => $q->where('academic_year_id', $activeYearObj->id))
            ->pluck('day_of_week')
            ->unique()
            ->toArray();

        // Get attendances for this month
        $attendances = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function($att) {
                return $att->date->day;
            });

        // Get today attendance for current employee
        $todayAttendance = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
            ->where('date', now()->format('Y-m-d'))
            ->first();

        // Compile statistics
        $totals = [
            'hadir_mengajar' => 0,
            'tugas_khusus' => 0,
            'sakit' => 0,
            'izin' => 0,
            'cuti' => 0,
            'dinas_luar' => 0,
            'alpha' => 0,
            'bebas_tugas' => 0,
            'total_scheduled' => 0,
            'present_on_scheduled' => 0,
        ];

        $calendarData = [];

        // Get holidays for this month from Educational Calendar
        $holidays = [];
        $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if ($academicYear) {
            $holidayEvents = \App\Models\EducationalCalendar::where('academic_year_id', $academicYear->id)
                ->where('is_holiday', true)
                ->where(function ($query) use ($teacher) {
                    $query->where('level', 'yayasan')
                          ->orWhere(function ($q) use ($teacher) {
                              $q->where('level', 'school')
                                ->where('school_id', $teacher->school_id);
                          });
                })
                ->get();
            
            foreach ($holidayEvents as $event) {
                $start = \Carbon\Carbon::parse($event->start_date);
                $end = \Carbon\Carbon::parse($event->end_date);
                while ($start->lte($end)) {
                    if ($start->month == $month && $start->year == $year) {
                        $holidays[] = $start->day;
                    }
                    $start->addDay();
                }
            }
        }
        $holidays = array_unique($holidays);

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = \Carbon\Carbon::create($year, $month, $d);
            $dayOfWeekName = strtolower($dateObj->format('l'));
            $isWeekend = in_array($dateObj->format('D'), ['Sat', 'Sun']);
            $isHoliday = in_array($d, $holidays);
            $isScheduled = in_array($dayOfWeekName, $teachingDays) && !$isWeekend && !$isHoliday;

            if ($isScheduled) {
                $totals['total_scheduled']++;
            }

            $att = $attendances[$d] ?? null;
            $status = '-';
            
            if ($isHoliday) {
                $statusLabel = 'Hari Libur / Tidak Aktif';
                $colorClass = 'bg-red-50 border-red-200 text-red-700';
            } else {
                $statusLabel = 'Bebas Tugas';
                $colorClass = 'bg-gray-50 border-gray-200 text-gray-400';
            }

            if ($att) {
                if ($att->status === 'hadir') {
                    if ($isScheduled) {
                        $status = 'HM';
                        $statusLabel = 'Hadir Mengajar';
                        $colorClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                        $totals['hadir_mengajar']++;
                        $totals['present_on_scheduled']++;
                    } else {
                        $status = 'TK';
                        $statusLabel = 'Tugas Khusus';
                        $colorClass = 'bg-indigo-50 border-indigo-200 text-indigo-700';
                        $totals['tugas_khusus']++;
                    }
                } else {
                    $status = strtoupper(substr($att->status, 0, 1));
                    $statusLabel = ucfirst(str_replace('_', ' ', $att->status));
                    if (isset($totals[$att->status])) {
                        $totals[$att->status]++;
                    }
                    
                    if ($att->status === 'sakit') {
                        $colorClass = 'bg-amber-50 border-amber-200 text-amber-700';
                    } elseif ($att->status === 'izin') {
                        $colorClass = 'bg-blue-50 border-blue-200 text-blue-700';
                    } elseif ($att->status === 'alpha') {
                        $colorClass = 'bg-rose-50 border-rose-200 text-rose-700';
                    } else {
                        $colorClass = 'bg-purple-50 border-purple-200 text-purple-700';
                    }
                }
            } else {
                if ($isScheduled) {
                    $status = 'A';
                    $statusLabel = 'Alpha (Wajib Hadir)';
                    $colorClass = 'bg-rose-50 border-rose-200 text-rose-700';
                    $totals['alpha']++;
                } else {
                    $totals['bebas_tugas']++;
                }
            }

            $calendarData[$d] = [
                'date' => $dateObj,
                'is_weekend' => $isWeekend,
                'is_scheduled' => $isScheduled,
                'attendance' => $att,
                'status' => $status,
                'status_label' => $statusLabel,
                'color_class' => $colorClass,
            ];
        }

        $pct = $totals['total_scheduled'] > 0 
            ? round(($totals['present_on_scheduled'] / $totals['total_scheduled']) * 100) 
            : 0;

        return view('guru.absensi-saya', compact(
            'teacher', 'employee', 'month', 'year', 'calendarData', 'totals', 'pct', 'daysInMonth', 'todayAttendance'
        ));
    }

    /**
     * Profil Guru
     */
    public function profil()
    {
        $teacher = $this->getTeacher();
        $teacher->load(['school', 'employee', 'user.reputation', 'user.badges']);

        return view('guru.profil', compact('teacher'));
    }
}


