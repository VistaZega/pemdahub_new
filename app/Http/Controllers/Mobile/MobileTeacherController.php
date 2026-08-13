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

        $attendances = collect();
        $todayAttendance = null;
        $stats = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'alpha' => 0];

        if ($employee) {
            $attendances = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->orderBy('date', 'desc')
                ->get();

            $todayAttendance = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();

            foreach ($attendances as $att) {
                $status = strtolower($att->status ?? 'hadir');
                if (str_contains($status, 'hadir') || $status === 'present') {
                    $stats['hadir']++;
                } elseif (str_contains($status, 'lambat') || $status === 'late') {
                    $stats['terlambat']++;
                } elseif (in_array($status, ['izin', 'sakit', 'cuti'])) {
                    $stats['izin']++;
                } else {
                    $stats['alpha']++;
                }
            }
        }

        return view('mobile.teacher.absensi_saya', compact('teacher', 'employee', 'attendances', 'todayAttendance', 'stats'));
    }
}
