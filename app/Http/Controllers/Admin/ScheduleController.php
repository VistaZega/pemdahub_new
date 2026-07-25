<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    protected $schedule;
    protected $classroom;
    protected $subject;
    protected $teacher;

    public function __construct()
    {
        $this->schedule = new Schedule();
        $this->classroom = new Classroom();
        $this->subject = new Subject();
        $this->teacher = new Teacher();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Schedule::with(['school', 'classroom', 'subject', 'teacher']);

        $academicYears = \App\Models\AcademicYear::orderBy('year', 'desc')->get();
        $currentYear = \App\Models\AcademicYear::where('is_active', 1)->first();
        
        $selectedYearId = $request->filled('academic_year_id') 
            ? $request->academic_year_id 
            : session('schedule_filter_academic_year_id');

        if (!$selectedYearId && !$request->has('academic_year_id')) {
            $selectedYearId = $currentYear ? $currentYear->id : null;
        }

        // Store to session
        if ($request->has('academic_year_id') && !$request->filled('academic_year_id')) {
            // User selected "Semua"
            $selectedYearId = null;
        }
        session(['schedule_filter_academic_year_id' => $selectedYearId]);

        // Filter schedules by academic year
        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        // Auto-filter by school_id for admin_sekolah
        if ($user && !$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        // Manual filter by school for superadmin
        if ($request->filled('school_id') && $user && $user->isSuperAdmin()) {
            $query->where('school_id', $request->input('school_id'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($query) use ($q) {
                $query->whereHas('classroom', function ($q_sub) use ($q) {
                    $q_sub->where('class_name', 'like', "%{$q}%");
                })->orWhereHas('subject', function ($q_sub) use ($q) {
                    $q_sub->where('subject_name', 'like', "%{$q}%");
                })->orWhereHas('teacher', function ($q_sub) use ($q) {
                    $q_sub->where('full_name', 'like', "%{$q}%");
                });
            });
        }

        if ($request->filled('day')) {
            $dayMap = [
                'monday' => 1,
                'tuesday' => 2,
                'wednesday' => 3,
                'thursday' => 4,
                'friday' => 5,
                'saturday' => 6,
            ];
            $dayKey = $request->input('day');
            if (isset($dayMap[$dayKey])) {
                $query->where('day_of_week', $dayMap[$dayKey]);
            }
        }

        // Get all schedules untuk matrix view
        $allSchedules = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        // Get all active classrooms from selected school (not just from schedules)
        $classroomsQuery = Classroom::where('is_active', true);
        
        if ($selectedYearId) {
            $classroomsQuery->where('academic_year_id', $selectedYearId);
        }
        
        if ($user && !$user->isSuperAdmin()) {
            $classroomsQuery->where('school_id', $user->school_id);
        } elseif ($request->filled('school_id') && $user && $user->isSuperAdmin()) {
            $classroomsQuery->where('school_id', $request->input('school_id'));
        }
        
        $classrooms = $classroomsQuery->orderBy('class_name')->get();

        // Group schedules by day and time
        $scheduleMatrix = $allSchedules->groupBy(function ($schedule) {
            return $schedule->day_of_week . '|' . $schedule->start_time . '-' . $schedule->end_time;
        });

        $days = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu'];
        $schools = \App\Models\School::where('is_active', true)->schoolsOnly()->orderBy('name')->get();

        return view('admin.schedules.index', compact('scheduleMatrix', 'classrooms', 'days', 'schools', 'academicYears', 'selectedYearId'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Get classrooms based on user role
        if ($user->isSuperAdmin()) {
            $classrooms = Classroom::with('school')->orderBy('class_name')->get();
            $subjects = collect(); // Empty for superadmin until school selected
            $teachers = collect(); // Empty for superadmin until school selected
            $schools = \App\Models\School::where('is_active', true)->schoolsOnly()->orderBy('name')->get();
        } else {
            $classrooms = Classroom::where('school_id', $user->school_id)
                ->with('school')
                ->orderBy('class_name')
                ->get();
            $subjects = Subject::where('school_id', $user->school_id)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get();
            $teachers = Teacher::where('school_id', $user->school_id)
                ->where('is_active', 1)
                ->orderBy('full_name')
                ->get();
            $schools = collect();
        }
        
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

        return view('admin.schedules.create', compact('classrooms', 'subjects', 'teachers', 'days', 'schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|integer|between:1,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:50',
        ]);

        // Get school_id and academic_year_id from classroom
        $classroom = Classroom::find($data['classroom_id']);
        if ($classroom) {
            $data['school_id'] = $classroom->school_id;
            $data['academic_year_id'] = $classroom->academic_year_id;
        }

        // Get first semester or semester of the classroom's academic year
        $semester = DB::table('semesters')
            ->when($classroom, function ($q) use ($classroom) {
                $q->where('academic_year_id', $classroom->academic_year_id);
            })
            ->first();
        if ($semester) {
            $data['semester_id'] = $semester->id;
        }

        Schedule::create($data);
        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran ditambahkan.');
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['classroom.school', 'subject', 'teacher']);
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

        return view('admin.schedules.show', compact('schedule', 'days'));
    }

    public function edit(Schedule $schedule)
    {
        $user = auth()->user();
        $schedule->load(['classroom', 'subject', 'teacher']);
        
        // Get school_id and major_id from schedule's classroom
        $schoolId = $schedule->classroom->school_id;
        $majorId = $schedule->classroom->major_id;
        
        if ($user->isSuperAdmin()) {
            $classrooms = Classroom::with('school')->orderBy('class_name')->get();
            $schools = \App\Models\School::where('is_active', true)->schoolsOnly()->orderBy('name')->get();
        } else {
            $classrooms = Classroom::where('school_id', $user->school_id)
                ->with('school')
                ->orderBy('class_name')
                ->get();
            $schools = collect();
        }
        
        // Filter subjects by school_id and major_id (if exists and not combined)
        $subjectsQuery = Subject::where('school_id', $schoolId)
            ->where('is_active', 1);
        
        if ($majorId && !$schedule->classroom->isCombinedClass()) {
            $subjectsQuery->where(function($query) use ($majorId) {
                $query->where('major_id', $majorId)
                      ->orWhereNull('major_id'); // Include general subjects
            });
        }
        
        $subjects = $subjectsQuery->orderBy('subject_name')->get();
        
        $teachers = Teacher::where('school_id', $schoolId)
            ->where('is_active', 1)
            ->orderBy('full_name')
            ->get();
        
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

        return view('admin.schedules.edit', compact('schedule', 'classrooms', 'subjects', 'teachers', 'days', 'schools'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $data = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|integer|between:1,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:50',
        ]);

        // Get school_id and academic_year_id from classroom
        $classroom = Classroom::find($data['classroom_id']);
        if ($classroom) {
            $data['school_id'] = $classroom->school_id;
            $data['academic_year_id'] = $classroom->academic_year_id;
        }

        $schedule->update($data);
        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran dihapus.');
    }
    
    /**
     * API: Get subjects and teachers by classroom (for dynamic filtering)
     */
    public function getSubjectsAndTeachersByClassroom(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        
        if (!$classroomId) {
            return response()->json(['subjects' => [], 'teachers' => []]);
        }
        
        $classroom = Classroom::find($classroomId);
        if (!$classroom) {
            return response()->json(['subjects' => [], 'teachers' => []]);
        }
        
        $schoolId = $classroom->school_id;
        $majorId = $classroom->major_id;
        
        // Filter subjects by school_id and major_id (if exists)
        $subjectsQuery = Subject::where('school_id', $schoolId)
            ->where('is_active', 1);
        
        // If classroom has major_id and is not combined, filter subjects by major OR general subjects (major_id is null)
        if ($majorId && !$classroom->isCombinedClass()) {
            $subjectsQuery->where(function($query) use ($majorId) {
                $query->where('major_id', $majorId)
                      ->orWhereNull('major_id'); // Include general subjects
            });
        }
        
        $subjects = $subjectsQuery->orderBy('subject_name')
            ->get(['id', 'subject_name', 'subject_code']);
        
        $teachers = Teacher::where('school_id', $schoolId)
            ->where('is_active', 1)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'teacher_code']);
        
        return response()->json([
            'subjects' => $subjects,
            'teachers' => $teachers
        ]);
    }
}
