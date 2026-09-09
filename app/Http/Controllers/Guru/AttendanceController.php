<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Services\TeachingAssignmentStudentFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Get the authenticated teacher record.
     */
    private function getTeacher(): Teacher
    {
        $userId = Auth::id();
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $userId)->first();
        if ($teacher) return $teacher;

        $employee = \App\Models\Employee::where('user_id', $userId)->first();
        if (!$employee) {
            $employee = \App\Models\Employee::create([
                'school_id' => $user->school_id ?? 1,
                'user_id' => $userId,
                'employee_code' => 'PGW-'.$userId,
                'full_name' => $user->name,
                'gender' => 'L',
                'employee_type' => 'other',
                'employment_status' => 'yayasan',
                'is_active' => true,
            ]);
        }

        return Teacher::firstOrCreate(
            ['user_id' => $userId],
            [
                'employee_id' => $employee->id,
                'school_id' => $employee->school_id ?? $user->school_id ?? 1,
                'teacher_code' => $employee->employee_code ?? 'PGW-'.$userId,
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
    private function getActiveYear(): ?AcademicYear
    {
        return AcademicYear::where('is_active', true)->first();
    }

    /**
     * Get classrooms the teacher is assigned to.
     */
    private function getTeacherClassrooms(Teacher $teacher, ?AcademicYear $activeYear)
    {
        if (!$activeYear) return collect();

        return Classroom::where('is_active', true)
            ->where('academic_year_id', $activeYear->id)
            ->where(function ($q) use ($teacher, $activeYear) {
                $q->whereHas('schedules', function ($sq) use ($teacher, $activeYear) {
                    $sq->where('teacher_id', $teacher->id)
                       ->where('academic_year_id', $activeYear->id);
                })
                ->orWhereHas('teachingAssignments', function ($tq) use ($teacher, $activeYear) {
                    $tq->where('teacher_id', $teacher->id)
                       ->where('academic_year_id', $activeYear->id)
                       ->where('is_active', true);
                })
                ->orWhere('homeroom_teacher_id', $teacher->id);
            })
            ->with('school')
            ->withCount(['students' => function ($q) use ($activeYear) {
                $q->where('student_classes.status', 'aktif');
                if ($activeYear) {
                    $q->where('student_classes.academic_year_id', $activeYear->id);
                }
            }])
            ->orderBy('class_name')
            ->get();
    }

    /**
     * Show the bulk attendance input form.
     */
    public function create(Request $request)
    {
        $teacher = $this->getTeacher();
        $activeYear = $this->getActiveYear();
        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear);

        $selectedClassroomId = $request->input('classroom_id');
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $students = collect();
        $existingAttendances = collect();
        $selectedClassroom = null;

        if ($selectedClassroomId) {
            $selectedClassroom = $classrooms->firstWhere('id', (int) $selectedClassroomId);
            if ($selectedClassroom) {
                
                // Find the best matching schedule for this date
                $dayOfWeek = strtolower(\Carbon\Carbon::parse($selectedDate)->format('l'));
                $schedule = Schedule::with(['teachingAssignment.subject', 'teachingAssignment.classroom'])
                    ->where('teacher_id', $teacher->id)
                    ->where('classroom_id', $selectedClassroomId)
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                    ->where('day_of_week', $dayOfWeek)
                    ->first();
                    
                if (!$schedule) {
                     $schedule = Schedule::with(['teachingAssignment.subject', 'teachingAssignment.classroom'])
                        ->where('teacher_id', $teacher->id)
                        ->where('classroom_id', $selectedClassroomId)
                        ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                        ->first();
                }

                $assignment = $schedule?->teachingAssignment;
                if (!$assignment) {
                    $assignment = \App\Models\TeachingAssignment::with(['subject', 'classroom'])
                        ->where('teacher_id', $teacher->id)
                        ->where('classroom_id', $selectedClassroomId)
                        ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                        ->where('is_active', true)
                        ->first();
                }

                $filterService = app(TeachingAssignmentStudentFilterService::class);
                $allClassroomIds = [$selectedClassroomId];

                if ($assignment) {
                    $students = $filterService->getStudentsForAssignment($assignment, $selectedDate);
                    if (!empty($assignment->group_code)) {
                        $allClassroomIds = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
                            ->where('academic_year_id', $activeYear->id)
                            ->where('group_code', $assignment->group_code)
                            ->pluck('classroom_id')
                            ->unique()
                            ->toArray();
                    }
                } else {
                    $studentsQuery = $selectedClassroom->students()->whereIn('student_classes.status', ['aktif', 'enrolled', 'active']);
                    if ($activeYear) {
                        $studentsQuery->wherePivot('academic_year_id', $activeYear->id);
                    }
                    $students = $studentsQuery->orderBy('full_name')->get();
                }

                $existingAttendances = Attendance::whereIn('classroom_id', $allClassroomIds)
                    ->where('date', $selectedDate)
                    ->where('created_by', Auth::id())
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return view('guru.absensi.input', compact(
            'teacher', 'classrooms', 'selectedClassroomId', 'selectedDate',
            'students', 'existingAttendances', 'selectedClassroom', 'activeYear'
        ));
    }

    /**
     * Store bulk attendance records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'classroom_id' => 'required|exists:classrooms,id',
            'statuses' => 'required|array',
            'statuses.*' => 'nullable|in:hadir,izin,sakit,alpha,',
        ]);

        $teacher = $this->getTeacher();
        $activeYear = $this->getActiveYear();
        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear);

        // Verify teacher has access to this classroom
        if (!$classrooms->contains('id', (int) $request->classroom_id)) {
            return back()->withErrors(['classroom_id' => 'Anda tidak memiliki akses ke kelas ini.'])->withInput();
        }

        try {
            $count = 0;
            $classroom = Classroom::find($request->classroom_id);
            $classroomName = $classroom ? $classroom->class_name : 'Kelas';

            // Cari schedule_id yang paling sesuai untuk guru & kelas ini
            $dayOfWeek = strtolower(\Carbon\Carbon::parse($request->date)->format('l'));
            $schedule = \App\Models\Schedule::where('teacher_id', $teacher->id)
                ->where('classroom_id', $request->classroom_id)
                ->where('day_of_week', $dayOfWeek)
                ->first();
                
            if (!$schedule) {
                 $schedule = \App\Models\Schedule::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $request->classroom_id)
                    ->first();
            }
            $scheduleId = $schedule ? $schedule->id : null;

            foreach ($request->statuses as $studentId => $status) {
                if (empty($status) || !in_array($status, ['hadir', 'izin', 'sakit', 'alpha'])) {
                    continue;
                }
                $note = $request->notes[$studentId] ?? null;
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'classroom_id' => $request->classroom_id,
                        'date' => $request->date,
                        'created_by' => Auth::id(), // Pisahkan absensi milik guru ini dari absensi hadir harian
                    ],
                    [
                        'schedule_id' => $scheduleId,
                        'status' => $status,
                        'notes' => $note,
                        'recorded_via' => 'manual',
                    ]
                );

                // Reputation Hook for Student (Maksimal 1x per tanggal)
                $student = \App\Models\Student::find($studentId);
                if ($student && $student->user_id) {
                    $points = match($status) {
                        'hadir' => 10,
                        'alpha' => -10,
                        default => 0
                    };
                    $desc = "Kehadiran di kelas " . $classroomName . " (" . ucfirst($status) . ")";
                    
                    // Cek apakah siswa sudah memiliki log kehadiran pada tanggal ini selain record ini
                    $alreadyLoggedOtherDate = \App\Models\ReputationLog::where('user_id', $student->user_id)
                        ->where('category', 'attendance')
                        ->whereDate('created_at', $request->date)
                        ->where(function($q) use ($attendance) {
                            $q->where('reference_type', '!=', get_class($attendance))
                              ->orWhere('reference_id', '!=', $attendance->id);
                        })
                        ->exists();

                    if (!$alreadyLoggedOtherDate) {
                        \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
                    }
                }

                $count++;
            }

            // Reputation Hook for Teacher (Maksimal +20 Poin 1x per tanggal agar tidak berlipat saat berulang kali klik simpan)
            $teacherAlreadyLoggedToday = \App\Models\ReputationLog::where('user_id', Auth::id())
                ->where('category', 'attendance_input')
                ->whereDate('created_at', $request->date ?? date('Y-m-d'))
                ->exists();

            if (!$teacherAlreadyLoggedToday) {
                \App\Models\ReputationLog::log(
                    Auth::id(), 
                    20, 
                    'attendance_input', 
                    "Melakukan input absensi harian (" . ($request->date ?? date('Y-m-d')) . ")"
                );
            }

            $dateCarbon = \Carbon\Carbon::parse($request->date);
            return redirect()->route('guru.absensi', [
                'classroom_id' => $request->classroom_id,
                'input_date' => $request->date,
                'month' => $dateCarbon->format('n'),
                'year' => $dateCarbon->format('Y'),
                'viewMode' => 'log',
            ])->with('success', "Absensi berhasil disimpan untuk {$count} siswa.");
        } catch (\Exception $e) {
            Log::error('Guru gagal menyimpan absensi: ' . $e->getMessage());
            return back()->withErrors(['attendance' => 'Gagal menyimpan absensi. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Store or update Daily School Attendance (Kehadiran Harian Sekolah) by Homeroom Teacher (Wali Kelas) or Authorized Teacher.
     */
    public function storeDaily(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'classroom_id' => 'required|exists:classrooms,id',
            'statuses' => 'required|array',
            'statuses.*' => 'nullable|in:hadir,izin,sakit,alpha,terlambat,',
            'notes' => 'nullable|array',
        ]);

        $teacher = $this->getTeacher();
        $classroom = Classroom::with('homeroomTeacher')->find($request->classroom_id);

        if (!$classroom) {
            return back()->withErrors(['classroom_id' => 'Kelas tidak ditemukan.'])->withInput();
        }

        // Strict Check: Hanya Wali Kelas yang berhak menyimpan/mengubah presensi harian sekolah
        if ((int) $classroom->homeroom_teacher_id !== (int) $teacher->id) {
            $homeroomName = $classroom->homeroomTeacher?->full_name ?? 'Wali Kelas';
            return back()->withErrors([
                'attendance' => "Akses Ditolak: Anda bukan Wali Kelas dari kelas {$classroom->class_name}. Presensi harian sekolah hanya dapat diisi dan diubah oleh Wali Kelas ({$homeroomName}) atau Admin Sekolah."
            ])->withInput();
        }

        try {
            $count = 0;
            $classroomName = $classroom->class_name;
            $date = $request->date;

            foreach ($request->statuses as $studentId => $status) {
                $note = $request->notes[$studentId] ?? null;

                $existing = Attendance::where('student_id', $studentId)
                    ->where('classroom_id', $request->classroom_id)
                    ->where('date', $date)
                    ->whereNull('schedule_id')
                    ->first();

                if (empty($status)) {
                    // If status was cleared and it was created manually or by wali kelas, we can delete it
                    if ($existing && in_array($existing->recorded_via, ['manual', 'wali_kelas', null])) {
                        $existing->delete();
                    }
                    continue;
                }

                // If existing was recorded via RFID device, update status and note, keep time_in
                $timeIn = $existing?->time_in ?? ($status === 'hadir' ? now()->format('H:i:s') : null);

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'classroom_id' => $request->classroom_id,
                        'date' => $date,
                        'schedule_id' => null, // Kehadiran Harian Sekolah
                    ],
                    [
                        'status' => $status,
                        'time_in' => $timeIn,
                        'notes' => $note,
                        'recorded_via' => $existing?->recorded_via ?? 'wali_kelas',
                        'created_by' => $existing?->created_by ?? Auth::id(),
                    ]
                );

                // Reputation Hook for Student
                $student = \App\Models\Student::find($studentId);
                if ($student && $student->user_id) {
                    $points = match($status) {
                        'hadir' => 10,
                        'alpha' => -10,
                        default => 0
                    };
                    $desc = "Kehadiran harian di kelas " . $classroomName . " (" . ucfirst($status) . ")";
                    
                    $alreadyLoggedOtherDate = \App\Models\ReputationLog::where('user_id', $student->user_id)
                        ->where('category', 'attendance')
                        ->whereDate('created_at', $date)
                        ->where(function($q) use ($attendance) {
                            $q->where('reference_type', '!=', get_class($attendance))
                              ->orWhere('reference_id', '!=', $attendance->id);
                        })
                        ->exists();

                    if (!$alreadyLoggedOtherDate && $points !== 0) {
                        \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
                    }
                }

                $count++;
            }

            // Reputation Hook for Teacher (Wali Kelas)
            $teacherAlreadyLoggedToday = \App\Models\ReputationLog::where('user_id', Auth::id())
                ->where('category', 'attendance_daily_input')
                ->whereDate('created_at', $date)
                ->exists();

            if (!$teacherAlreadyLoggedToday) {
                \App\Models\ReputationLog::log(
                    Auth::id(), 
                    20, 
                    'attendance_daily_input', 
                    "Melakukan pembaruan presensi harian kelas {$classroomName} ({$date})"
                );
            }

            $dateCarbon = \Carbon\Carbon::parse($date);
            return redirect()->route('guru.absensi', [
                'classroom_id' => $request->classroom_id,
                'daily_date' => $date,
                'month' => $dateCarbon->format('n'),
                'year' => $dateCarbon->format('Y'),
                'viewMode' => 'daily',
            ])->with('success', "Presensi harian sekolah berhasil disimpan untuk {$count} siswa.");
        } catch (\Exception $e) {
            Log::error('Wali Kelas gagal menyimpan presensi harian: ' . $e->getMessage());
            return back()->withErrors(['attendance' => 'Gagal menyimpan presensi harian. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Hapus seluruh data absensi pada tanggal tertentu yang diinput oleh guru ini.
     */
    public function destroyDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $teacher = $this->getTeacher();
        $classrooms = $this->getTeacherClassrooms($teacher, $this->getActiveYear());

        if (!$classrooms->contains('id', (int) $request->classroom_id)) {
            return back()->withErrors(['classroom_id' => 'Anda tidak memiliki akses ke kelas ini.']);
        }

        try {
            $deletedCount = Attendance::where('classroom_id', $request->classroom_id)
                ->where('date', $request->date)
                ->where('created_by', Auth::id())
                ->delete();

            $dateCarbon = \Carbon\Carbon::parse($request->date);

            return redirect()->route('guru.absensi', [
                'classroom_id' => $request->classroom_id,
                'month' => $dateCarbon->format('n'),
                'year' => $dateCarbon->format('Y'),
                'viewMode' => 'log',
            ])->with('success', "Data absensi tanggal " . $dateCarbon->format('d/m/Y') . " ({$deletedCount} siswa) berhasil dibersihkan/dihapus.");
        } catch (\Exception $e) {
            Log::error('Gagal menghapus absensi tanggal: ' . $e->getMessage());
            return back()->withErrors(['attendance' => 'Gagal menghapus absensi tanggal tersebut.']);
        }
    }
}
