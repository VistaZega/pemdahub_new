<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Teacher;
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
                $studentsQuery = $selectedClassroom->students()
                    ->wherePivot('status', 'aktif');

                if ($activeYear) {
                    $studentsQuery->wherePivot('academic_year_id', $activeYear->id);
                }

                $students = $studentsQuery->orderBy('full_name')->get();

                // Load existing attendance for this date + classroom (for edit/update)
                $existingAttendances = Attendance::where('classroom_id', $selectedClassroomId)
                    ->where('date', $selectedDate)
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
            'statuses.*' => 'required|in:hadir,izin,sakit,alpha',
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

            foreach ($request->statuses as $studentId => $status) {
                $note = $request->notes[$studentId] ?? null;
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'classroom_id' => $request->classroom_id,
                        'date' => $request->date,
                    ],
                    [
                        'status' => $status,
                        'notes' => $note,
                        'recorded_via' => 'manual',
                        'created_by' => Auth::id(),
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
                    $desc = "Kehadiran di kelas " . $classroomName . " (" . ucfirst($status) . ")";
                    \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
                }

                $count++;
            }

            // Reputation Hook for Teacher
            \App\Models\ReputationLog::log(
                Auth::id(), 
                20, 
                'attendance_input', 
                "Melakukan input absensi kelas: " . ($request->classroom_id ?? 'Kelas'),
                null // Reference ID could be classroom + date hash if needed
            );

            return redirect()->route('guru.absensi')
                ->with('success', "Absensi berhasil disimpan untuk {$count} siswa.");
        } catch (\Exception $e) {
            Log::error('Guru gagal menyimpan absensi: ' . $e->getMessage());
            return back()->withErrors(['attendance' => 'Gagal menyimpan absensi. Silakan coba lagi.'])->withInput();
        }
    }
}
