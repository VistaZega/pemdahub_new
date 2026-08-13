<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\Classroom;
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
        return Teacher::where('user_id', Auth::id())->first();
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

        // High precision classroom filter for Teacher
        if ($user->isOwnerOrSuperAdmin() || $user->isAdminSekolah()) {
            $classrooms = Classroom::where('is_active', true)
                ->when($user->school_id && !$user->isOwnerOrSuperAdmin(), function ($q) use ($user) {
                    $q->where('school_id', $user->school_id);
                })
                ->orderBy('class_name')
                ->get();
        } else {
            // For Guru: filter to assigned classrooms (schedules, teaching assignments, or homeroom)
            $classrooms = collect();
            if ($teacher) {
                $classrooms = Classroom::where('is_active', true)
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                    ->where(function ($q) use ($teacher, $activeYear) {
                        $q->whereHas('schedules', function ($sq) use ($teacher, $activeYear) {
                            $sq->where('teacher_id', $teacher->id)
                               ->when($activeYear, fn($ay) => $ay->where('academic_year_id', $activeYear->id));
                        })
                        ->orWhereHas('teachingAssignments', function ($tq) use ($teacher, $activeYear) {
                            $tq->where('teacher_id', $teacher->id)
                               ->when($activeYear, fn($ay) => $ay->where('academic_year_id', $activeYear->id))
                               ->where('is_active', true);
                        })
                        ->orWhere('homeroom_teacher_id', $teacher->id);
                    })
                    ->orderBy('class_name')
                    ->get();
            }

            // Fallback to active classrooms in teacher's school if no specific assignment found
            if ($classrooms->isEmpty()) {
                $schoolId = $teacher?->school_id ?? $user->school_id ?? 1;
                $classrooms = Classroom::where('is_active', true)
                    ->where('school_id', $schoolId)
                    ->orderBy('class_name')
                    ->get();
            }
        }

        $selectedClassroomId = $request->input('classroom_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        $students = collect();
        $existingAttendances = [];

        if ($selectedClassroomId) {
            $classroom = Classroom::find($selectedClassroomId);
            if ($classroom) {
                $students = $classroom->students()->orderBy('full_name')->get();
                $attendances = Attendance::where('classroom_id', $classroom->id)
                    ->where('date', $date)
                    ->get();
                foreach ($attendances as $att) {
                    $existingAttendances[$att->student_id] = $att->status;
                }
            }
        }

        return view('mobile.teacher.absensi_input', compact('teacher', 'classrooms', 'selectedClassroomId', 'date', 'students', 'existingAttendances'));
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
}
