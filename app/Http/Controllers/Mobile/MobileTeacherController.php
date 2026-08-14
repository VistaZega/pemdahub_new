<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileTeacherController extends Controller
{
    private function getTeacher()
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        if ($teacher) return $teacher;

        // Fallback for Super Admin / Testers switching role to Guru
        $user = Auth::user();
        if ($user) {
            $teacher = Teacher::when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))
                ->first();
        }

        return $teacher;
    }

    /**
     * Jadwal Mengajar Guru Mobile
     */
    public function jadwal()
    {
        $teacher = $this->getTeacher();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu',
        ];
        $today = strtolower(now()->format('l'));
        $activeDay = in_array($today, $days) ? $today : 'monday';

        $schedulesByDay = [];
        foreach ($days as $day) {
            $schedulesByDay[$day] = collect();
        }

        if ($teacher) {
            $schedules = Schedule::where('teacher_id', $teacher->id)
                ->with(['subject', 'classroom', 'timeSlot'])
                ->get();

            foreach ($schedules as $sch) {
                if (isset($schedulesByDay[$sch->day_of_week])) {
                    $schedulesByDay[$sch->day_of_week]->push($sch);
                }
            }
        }

        return view('mobile.teacher.jadwal', compact('teacher', 'days', 'dayLabels', 'activeDay', 'schedulesByDay'));
    }

    /**
     * Input Absensi Siswa per Kelas (Guru)
     */
    public function absensiInput(Request $request)
    {
        $user = Auth::user();
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Priority 1: Filter to assigned classrooms for this teacher (schedules, teaching assignments, or homeroom)
        $classrooms = collect();
        if ($teacher) {
            $classrooms = Classroom::where('is_active', true)
                ->where(function ($q) use ($teacher, $activeYear) {
                    $q->whereHas('schedules', function ($sq) use ($teacher, $activeYear) {
                        $sq->where('teacher_id', $teacher->id);
                        if ($activeYear) {
                            $sq->where('academic_year_id', $activeYear->id);
                        }
                    })
                    ->orWhereHas('teachingAssignments', function ($tq) use ($teacher, $activeYear) {
                        $tq->where('teacher_id', $teacher->id);
                        if ($activeYear) {
                            $tq->where('academic_year_id', $activeYear->id);
                        }
                    })
                    ->orWhere('homeroom_teacher_id', $teacher->id);
                })
                ->orderBy('class_name')
                ->get();
        }

        // Priority 2 (Fallback): If teacher has no specific assignments yet, get active classrooms in teacher/user school
        if ($classrooms->isEmpty()) {
            $schoolId = $teacher?->school_id ?? $user->school_id;
            $classrooms = Classroom::where('is_active', true)
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->orderBy('class_name')
                ->get();
        }

        $selectedClassroomId = $request->input('classroom_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        $students = collect();
        $existingAttendances = [];
        $assignmentRuleInfo = null;

        if ($selectedClassroomId) {
            $classroom = Classroom::find($selectedClassroomId);
            if ($classroom) {
                $dayOfWeek = strtolower(\Carbon\Carbon::parse($date)->format('l'));
                $schedule = Schedule::with('teachingAssignment.subject')
                    ->where('teacher_id', $teacher?->id ?? 0)
                    ->where('classroom_id', $selectedClassroomId)
                    ->where('day_of_week', $dayOfWeek)
                    ->first();

                if (!$schedule) {
                    $schedule = Schedule::with('teachingAssignment.subject')
                        ->where('teacher_id', $teacher?->id ?? 0)
                        ->where('classroom_id', $selectedClassroomId)
                        ->first();
                }

                $filterService = app(\App\Services\TeachingAssignmentStudentFilterService::class);
                $allClassroomIds = [$selectedClassroomId];

                if ($schedule && $schedule->teachingAssignment) {
                    $assignment = $schedule->teachingAssignment;
                    $students = $filterService->getStudentsForAssignment($assignment);

                    $rules = [];
                    if (!empty($assignment->group_code)) {
                        $rules[] = 'Gabungan (Kelompok ' . $assignment->group_code . ')';
                        if ($teacher) {
                            $allClassroomIds = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
                                ->where('group_code', $assignment->group_code)
                                ->pluck('classroom_id')
                                ->unique()
                                ->toArray();
                        }
                    }
                    if ($assignment->block_type === 'parallel') {
                        $rules[] = 'Paralel Agama (' . ($assignment->subject->name ?? 'Agama') . ')';
                    } elseif ($assignment->block_type === 'all') {
                        $rules[] = 'Sistem Blok SMK (Grup A)';
                    } elseif ($assignment->block_type === 'split') {
                        $rules[] = 'Sistem Blok SMK (Grup B)';
                    }

                    $assignmentRuleInfo = !empty($rules) ? implode(' • ', $rules) : 'Reguler';
                } else {
                    $students = $classroom->students()->orderBy('full_name')->get();
                    $assignmentRuleInfo = 'Reguler';
                }

                $attendances = Attendance::whereIn('classroom_id', $allClassroomIds)
                    ->where('date', $date)
                    ->get();
                foreach ($attendances as $att) {
                    $existingAttendances[$att->student_id] = $att->status;
                }
            }
        }

        return view('mobile.teacher.absensi_input', compact('teacher', 'classrooms', 'selectedClassroomId', 'date', 'students', 'existingAttendances', 'assignmentRuleInfo'));
    }

    /**
     * Store Absensi Siswa Massal (Guru)
     */
    public function storeAbsensi(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
        ]);

        $classroomId = $request->input('classroom_id');
        $date = $request->input('date');
        $attendancesInput = $request->input('attendances');

        foreach ($attendancesInput as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $date,
                ],
                [
                    'classroom_id' => $classroomId,
                    'status' => $status,
                    'time_in' => now()->format('H:i:s'),
                    'recorded_via' => 'manual',
                    'created_by' => Auth::id(),
                ]
            );
        }

        return back()->with('success', 'Absensi kelas berhasil disimpan!');
    }

    /**
     * Lihat & Nilai Tugas Siswa (Guru)
     */
    public function tugas()
    {
        $teacher = $this->getTeacher();
        $assignments = collect();

        if ($teacher) {
            $assignments = LmsAssignment::whereHas('course', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->with(['course', 'submissions.student'])
              ->latest()
              ->get();
        }

        if ($assignments->isEmpty()) {
            $assignments = LmsAssignment::with(['course', 'submissions.student'])
                ->latest()
                ->take(10)
                ->get();
        }

        return view('mobile.teacher.tugas', compact('teacher', 'assignments'));
    }

    /**
     * Save Submission Grade (Guru)
     */
    public function gradeSubmission(Request $request, $submissionId)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission = LmsSubmission::findOrFail($submissionId);
        $submission->update([
            'score' => $request->input('score'),
            'feedback' => $request->input('feedback'),
            'status' => 'graded',
        ]);

        return back()->with('success', 'Nilai tugas berhasil disimpan!');
    }

    /**
     * Presensi & Rekap Kehadiran Guru Sendiri (Absen Saya)
     */
    public function absensiSaya(Request $request)
    {
        $user = Auth::user();
        $teacher = $this->getTeacher();

        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->school_id) {
            $employee = \App\Models\Employee::where('school_id', $user->school_id)->first();
        }

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;

        $activeYearObj = \App\Models\AcademicYear::where('is_active', true)->first();
        $teachingDays = [];
        if ($teacher) {
            $teachingDays = $teacher->schedules()
                ->when($activeYearObj, fn($q) => $q->where('academic_year_id', $activeYearObj->id))
                ->pluck('day_of_week')
                ->unique()
                ->toArray();
        }

        $employeeAttendances = collect();
        if ($employee) {
            $employeeAttendances = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(fn($att) => \Carbon\Carbon::parse($att->date)->day);
        }

        $todayAttendance = null;
        if ($employee) {
            $todayAttendance = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();
        }

        $totals = [
            'hadir_mengajar' => 0,
            'tugas_khusus' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpha' => 0,
            'total_scheduled' => 0,
            'present_on_scheduled' => 0,
        ];

        $calendarData = [];

        // Holidays from calendar
        $holidays = [];
        if ($activeYearObj && $teacher) {
            $holidayEvents = \App\Models\EducationalCalendar::where('academic_year_id', $activeYearObj->id)
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
            $dayName = strtolower($dateObj->format('l'));
            $isScheduled = in_array($dayName, $teachingDays);
            $isHoliday = in_array($d, $holidays) || $dateObj->isSunday();
            $att = $employeeAttendances->get($d);

            $status = '-';
            $statusLabel = 'Bebas Tugas';
            $colorClass = 'bg-slate-50 border-slate-200 text-slate-400';

            if ($att) {
                $attStatus = strtolower($att->status ?? 'hadir');
                if (in_array($attStatus, ['hadir', 'present'])) {
                    if ($isScheduled) {
                        $status = 'HM';
                        $statusLabel = 'Hadir Mengajar';
                        $colorClass = 'bg-emerald-50 border-emerald-300 text-emerald-900';
                        $totals['hadir_mengajar']++;
                        $totals['present_on_scheduled']++;
                    } else {
                        $status = 'TK';
                        $statusLabel = 'Tugas Khusus';
                        $colorClass = 'bg-indigo-50 border-indigo-300 text-indigo-900';
                        $totals['tugas_khusus']++;
                    }
                } elseif (in_array($attStatus, ['izin', 'sakit', 'cuti'])) {
                    $status = strtoupper(substr($attStatus, 0, 1));
                    $statusLabel = ucfirst($attStatus);
                    $colorClass = 'bg-amber-50 border-amber-300 text-amber-900';
                    $totals['izin']++;
                } else {
                    $status = 'A';
                    $statusLabel = 'Alpha';
                    $colorClass = 'bg-rose-50 border-rose-300 text-rose-900';
                    $totals['alpha']++;
                }
            } else {
                if ($isScheduled && !$isHoliday && $dateObj->isPast()) {
                    $status = 'A';
                    $statusLabel = 'Alpha (Terjadwal)';
                    $colorClass = 'bg-rose-50 border-rose-300 text-rose-900';
                    $totals['alpha']++;
                } elseif ($isHoliday) {
                    $status = 'H';
                    $statusLabel = 'Libur Sekolah';
                    $colorClass = 'bg-slate-100 border-slate-200 text-slate-500';
                }
            }

            if ($isScheduled && !$isHoliday) {
                $totals['total_scheduled']++;
            }

            $calendarData[$d] = [
                'day' => $d,
                'date' => $dateObj,
                'status' => $status,
                'status_label' => $statusLabel,
                'color_class' => $colorClass,
                'attendance' => $att,
            ];
        }

        $pct = $totals['total_scheduled'] > 0
            ? round(($totals['present_on_scheduled'] / $totals['total_scheduled']) * 100, 1)
            : 100;
        $reputationPoints = $totals['tugas_khusus'] * 15;

        return view('mobile.teacher.absensi_saya', compact(
            'teacher', 'employee', 'todayAttendance', 'totals', 'calendarData', 'pct', 'reputationPoints', 'month', 'year', 'daysInMonth'
        ));
    }

    /**
     * My Class / Kelas Saya (Guru Mobile)
     * Tampilan utama: Card Daftar Kelas dengan filter "Saya Wali Kelas" atau "Saya Mengajar".
     * Ketika Card Kelas diklik, menampilkan daftar siswa di kelas tersebut di TP Aktif.
     */
    public function kelas()
    {
        $teacher = $this->getTeacher();
        $user = Auth::user();

        // Get Active Academic Year
        $activeAY = null;
        if (class_exists('\App\Models\AcademicYear')) {
            $activeAY = \App\Models\AcademicYear::where('is_active', true)
                ->orderBy('is_active', 'desc')
                ->first()
                ?? \App\Models\AcademicYear::latest()->first();
        }
        $activeAYId = $activeAY?->id;

        $homeroomClasses = collect();
        $teachingClasses = collect();

        if ($teacher) {
            // 1. KELAS PERWALIAN (Wali Kelas) di TP Aktif
            $homeroomClasses = Classroom::where('is_active', true)
                ->where('homeroom_teacher_id', $teacher->id)
                ->when($activeAYId, function ($q) use ($activeAYId) {
                    $q->where(function ($sub) use ($activeAYId) {
                        $sub->where('academic_year_id', $activeAYId)
                            ->orWhereNull('academic_year_id');
                    });
                })
                ->with(['major'])
                ->orderBy('class_name')
                ->get();

            foreach ($homeroomClasses as $cls) {
                $cls->is_homeroom = true;
                $cls->is_teaching = false;
            }

            // 2. KELAS MENGAJAR (Penugasan Mengajar / Schedules) di TP Aktif
            $teachingClasses = Classroom::where('is_active', true)
                ->where(function ($q) use ($teacher, $activeAYId) {
                    $q->whereHas('schedules', function ($sq) use ($teacher, $activeAYId) {
                        $sq->where('teacher_id', $teacher->id)
                           ->when($activeAYId, fn($ayq) => $ayq->where(fn($sub) => $sub->where('academic_year_id', $activeAYId)->orWhereNull('academic_year_id')));
                    })
                    ->orWhereHas('teachingAssignments', function ($tq) use ($teacher, $activeAYId) {
                        $tq->where('teacher_id', $teacher->id)
                           ->where('is_active', true)
                           ->when($activeAYId, fn($ayq) => $ayq->where(fn($sub) => $sub->where('academic_year_id', $activeAYId)->orWhereNull('academic_year_id')));
                    });
                })
                ->when($activeAYId, function ($q) use ($activeAYId) {
                    $q->where(function ($sub) use ($activeAYId) {
                        $sub->where('academic_year_id', $activeAYId)
                            ->orWhereNull('academic_year_id');
                    });
                })
                ->with(['major'])
                ->orderBy('class_name')
                ->get();

            foreach ($teachingClasses as $cls) {
                $cls->is_homeroom = ($cls->homeroom_teacher_id == $teacher->id);
                $cls->is_teaching = true;
            }
        }

        // Fallback jika belum ada penugasan khusus tercatat
        if ($homeroomClasses->isEmpty() && $teachingClasses->isEmpty()) {
            $schoolId = $teacher?->school_id ?? $user->school_id;
            $teachingClasses = Classroom::where('is_active', true)
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->when($activeAYId, fn($q) => $q->where(fn($sub) => $sub->where('academic_year_id', $activeAYId)->orWhereNull('academic_year_id')))
                ->with(['major'])
                ->orderBy('class_name')
                ->get();

            foreach ($teachingClasses as $cls) {
                $cls->is_homeroom = false;
                $cls->is_teaching = true;
            }
        }

        // Gabungkan seluruh kelas secara unik berdasarkan id
        $classrooms = collect();
        $addedIds = [];

        foreach ($homeroomClasses as $cls) {
            if (!in_array($cls->id, $addedIds)) {
                $classrooms->push($cls);
                $addedIds[] = $cls->id;
            }
        }

        foreach ($teachingClasses as $cls) {
            if (!in_array($cls->id, $addedIds)) {
                $classrooms->push($cls);
                $addedIds[] = $cls->id;
            } else {
                $existing = $classrooms->firstWhere('id', $cls->id);
                if ($existing) {
                    $existing->is_teaching = true;
                }
            }
        }

        // Muat daftar seluruh siswa secara presisi dari student_classes pivot
        foreach ($classrooms as $cls) {
            $pivotStudents = $cls->students()->with('user')->get();

            if (\Illuminate\Support\Facades\Schema::hasColumn('students', 'classroom_id')) {
                $directStudents = Student::where('classroom_id', $cls->id)->with('user')->get();
                $mergedStudents = $pivotStudents->merge($directStudents)->unique('id')->sortBy('full_name')->values();
            } else {
                $mergedStudents = $pivotStudents->unique('id')->sortBy('full_name')->values();
            }

            $cls->setRelation('students', $mergedStudents);
            $cls->students_count = $mergedStudents->count();
        }

        return view('mobile.teacher.kelas', compact(
            'teacher', 
            'activeAY',
            'classrooms'
        ));
    }

    /**
     * Surat Edaran Resmi (Guru Mobile)
     */
    public function edaran()
    {
        $teacher = $this->getTeacher();
        $user = Auth::user();

        $foundationLetters = collect();
        if (class_exists('\App\Models\FoundationLetter')) {
            $foundationLetters = \App\Models\FoundationLetter::latest()->get();
        }

        return view('mobile.teacher.edaran', compact(
            'teacher', 
            'foundationLetters'
        ));
    }

    /**
     * CBT Ujian & Bank Soal (Guru Mobile)
     */
    public function cbt()
    {
        $teacher = $this->getTeacher();

        $banks = collect();
        if ($teacher && class_exists('\App\Models\CbtQuestionBank')) {
            $banks = \App\Models\CbtQuestionBank::where('teacher_id', $teacher->id)
                ->withCount('questions')
                ->latest()
                ->get();
        }

        if ($banks->isEmpty() && class_exists('\App\Models\CbtQuestionBank')) {
            $banks = \App\Models\CbtQuestionBank::withCount('questions')
                ->latest()
                ->take(10)
                ->get();
        }

        $exams = collect();
        if (class_exists('\App\Models\CbtExam')) {
            $exams = \App\Models\CbtExam::when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
                ->with(['questionBanks', 'results', 'subject'])
                ->withCount(['results', 'participants', 'sessions'])
                ->latest()
                ->get();
        }

        return view('mobile.teacher.cbt', compact('teacher', 'banks', 'exams'));
    }

    /**
     * Store Ujian CBT Baru (Guru Mobile)
     */
    public function cbtExamStore(Request $request)
    {
        $teacher = $this->getTeacher();
        $user = Auth::user();

        $validated = $request->validate([
            'exam_title' => 'required|string|max:255',
            'question_bank_id' => 'required|integer',
            'duration_minutes' => 'required|integer|min:1',
            'exam_type' => 'nullable|string',
            'passing_score' => 'nullable|numeric|min:0|max:100',
            'access_code' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'start_time_only' => 'nullable|string',
            'end_date' => 'nullable|date',
            'end_time_only' => 'nullable|string',
        ]);

        $bank = \App\Models\CbtQuestionBank::find($validated['question_bank_id']);

        $startDateTime = now();
        if (!empty($validated['start_date'])) {
            $t = $validated['start_time_only'] ?? '08:00';
            $startDateTime = \Carbon\Carbon::parse($validated['start_date'] . ' ' . $t);
        }

        $endDateTime = now()->addDays(7);
        if (!empty($validated['end_date'])) {
            $t = $validated['end_time_only'] ?? '23:59';
            $endDateTime = \Carbon\Carbon::parse($validated['end_date'] . ' ' . $t);
        }

        $exam = \App\Models\CbtExam::create([
            'school_id' => $teacher?->school_id ?? $user->school_id,
            'subject_id' => $bank?->subject_id,
            'teacher_id' => $teacher?->id,
            'exam_title' => $validated['exam_title'],
            'exam_type' => $validated['exam_type'] ?? 'quiz',
            'exam_scope' => 'class',
            'status' => 'published',
            'duration_minutes' => $validated['duration_minutes'],
            'passing_score' => $validated['passing_score'] ?? 70,
            'access_code' => $validated['access_code'] ?? strtoupper(\Illuminate\Support\Str::random(6)),
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'randomize_questions' => true,
            'randomize_options' => true,
            'show_result' => true,
            'created_by' => $user->id,
        ]);

        if ($bank) {
            $exam->questionBanks()->attach($bank->id);
        }

        return redirect()->route('mobile.guru.cbt')
            ->with('success', 'Jadwal Ujian CBT "' . $exam->exam_title . '" berhasil dibuat & diterbitkan!');
    }

    /**
     * Monitoring Ujian CBT Live (Guru Mobile)
     */
    public function cbtExamMonitor($examId)
    {
        $teacher = $this->getTeacher();
        $exam = \App\Models\CbtExam::with([
            'questionBanks.questions',
            'results.student.user',
            'sessions.student.user',
            'subject'
        ])->findOrFail($examId);

        $results = $exam->results ?? collect();

        // Enforce Photo Profile on all student result records
        foreach ($results as $res) {
            $std = $res->student ?? null;
            if ($std) {
                $photo = $std->photo_url ?? null;
                if (!$photo || str_contains($photo, 'default-student.jpg') || str_contains($photo, 'default-avatar')) {
                    if (isset($std->user->avatar_url) && $std->user->avatar_url) {
                        $photo = $std->user->avatar_url;
                    } else {
                        $photo = 'https://ui-avatars.com/api/?name=' . urlencode($std->full_name) . '&background=7c3aed&color=fff&bold=true';
                    }
                }
                $std->display_photo = $photo;
            }
        }

        $avgScore = $results->avg('score') ?? 0;
        $maxScore = $results->max('score') ?? 0;
        $minScore = $results->min('score') ?? 0;
        $passedCount = $results->where('score', '>=', $exam->passing_score ?? 70)->count();

        return view('mobile.teacher.cbt_monitor', compact(
            'teacher',
            'exam',
            'results',
            'avgScore',
            'maxScore',
            'minScore',
            'passedCount'
        ));
    }

    /**
     * Toggle / Selesaikan Ujian CBT (Guru Mobile)
     */
    public function cbtExamToggleStatus($examId)
    {
        $exam = \App\Models\CbtExam::findOrFail($examId);
        if ($exam->status === 'active' || $exam->status === 'published') {
            $exam->update(['status' => 'completed']);
            $msg = 'Ujian CBT telah diselesaikan.';
        } else {
            $exam->update(['status' => 'published']);
            $msg = 'Ujian CBT telah diaktifkan kembali.';
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Raport Digital Wali Kelas (Guru Mobile)
     */
    public function raport()
    {
        $teacher = $this->getTeacher();
        $user = Auth::user();

        $homeroomClasses = collect();
        if ($teacher) {
            $homeroomClasses = Classroom::where('homeroom_teacher_id', $teacher->id)
                ->where('is_active', true)
                ->with('students')
                ->get();
        }

        $reportCards = collect();
        if (class_exists('\App\Models\ReportCard')) {
            $reportCards = \App\Models\ReportCard::with(['student', 'classroom'])
                ->latest()
                ->take(20)
                ->get();
        }

        return view('mobile.teacher.raport', compact('teacher', 'homeroomClasses', 'reportCards'));
    }

    /**
     * Hall of Fame & Leaderboard (Guru Mobile)
     */
    public function hallOfFame()
    {
        $topStudents = collect();
        $topTeachers = collect();

        if (class_exists('\App\Models\Reputation')) {
            $topStudents = \App\Models\Reputation::whereHas('user.student')
                ->with(['user.student'])
                ->orderBy('total_points', 'desc')
                ->take(15)
                ->get()
                ->pluck('user.student')
                ->filter()
                ->values();

            $topTeachers = \App\Models\Reputation::whereHas('user.teacher')
                ->with(['user.teacher'])
                ->orderBy('total_points', 'desc')
                ->take(10)
                ->get()
                ->pluck('user.teacher')
                ->filter()
                ->values();
        }

        if ($topStudents->isEmpty()) {
            $topStudents = Student::with('user.reputation')->latest()->take(15)->get();
        }

        if ($topTeachers->isEmpty()) {
            $topTeachers = Teacher::with('user.reputation')->latest()->take(10)->get();
        }

        return view('mobile.hall_of_fame', compact('topStudents', 'topTeachers'));
    }

    /**
     * Detail Bank Soal CBT & Tautkan ke LMS (Guru Mobile 3D View)
     */
    public function cbtBankShow($bankId)
    {
        $teacher = $this->getTeacher();
        $bank = \App\Models\CbtQuestionBank::with(['questions.options', 'subject'])->findOrFail($bankId);

        $courses = \App\Models\LmsCourse::where('teacher_id', $teacher->id ?? 0)
            ->orWhere(fn($q) => $q->whereNull('teacher_id'))
            ->latest()
            ->get();

        if ($courses->isEmpty()) {
            $courses = \App\Models\LmsCourse::latest()->take(20)->get();
        }

        return view('mobile.teacher.cbt_bank_show', compact('bank', 'courses', 'teacher'));
    }

    /**
     * Rekap Tagihan Uang Sekolah Rombel untuk Wali Kelas (Berdasarkan Bulan Berkenaan)
     */
    public function tagihan(Request $request)
    {
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first();

        $selectedMonth = (int) $request->input('month', now()->month);
        $selectedYear = (int) $request->input('year', now()->year);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Cari Rombel Wali Kelas
        $classroom = null;
        if ($teacher) {
            $classroom = Classroom::where('homeroom_teacher_id', $teacher->id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->first();
        }

        // Fallback jika bukan wali kelas / admin
        if (!$classroom) {
            $classroom = Classroom::first();
        }

        $students = collect();
        $stats = [
            'total_students' => 0,
            'lunas_count' => 0,
            'belum_lunas_count' => 0,
            'total_tunggakan' => 0,
        ];

        if ($classroom) {
            // Ambil daftar siswa kelas
            $pivotStudents = $classroom->students()->get();
            $directStudents = Student::where('classroom_id', $classroom->id)->get();
            $allStudents = $pivotStudents->merge($directStudents)->unique('id');

            $stats['total_students'] = $allStudents->count();

            foreach ($allStudents as $student) {
                // Ambil tagihan bulan berkenaan
                $monthBill = \App\Models\StudentBill::where('student_id', $student->id)
                    ->where('month', $selectedMonth)
                    ->where('year', $selectedYear)
                    ->first();

                // Ambil seluruh tunggakan siswa
                $totalUnpaid = \App\Models\StudentBill::where('student_id', $student->id)
                    ->where('status', '!=', 'lunas')
                    ->get()
                    ->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

                $statusBulanIni = $monthBill ? $monthBill->status : 'belum_bayar';
                if ($statusBulanIni === 'lunas') {
                    $stats['lunas_count']++;
                } else {
                    $stats['belum_lunas_count']++;
                }

                $stats['total_tunggakan'] += $totalUnpaid;

                $student->month_bill = $monthBill;
                $student->status_bulan_ini = $statusBulanIni;
                $student->total_tunggakan = $totalUnpaid;

                $students->push($student);
            }
        }

        return view('mobile.teacher.tagihan', compact(
            'teacher', 'classroom', 'students', 'stats', 
            'selectedMonth', 'selectedYear', 'monthNames'
        ));
    }
}
