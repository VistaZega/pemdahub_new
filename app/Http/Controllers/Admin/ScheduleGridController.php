<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use App\Exports\ScheduleExport;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleGridController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentYear = AcademicYear::where('is_active', 1)->first();
        
        $selectedYearId = $request->filled('academic_year_id') 
            ? $request->academic_year_id 
            : ($currentYear ? $currentYear->id : null);
        
        $semester = $request->get('semester', 'ganjil');
        
        // Get schools
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->where('type', '!=', 'yayasan')->get()
            : School::where('id', $user->school_id)->where('type', '!=', 'yayasan')->get();
        
        if ($user->isSuperAdmin()) {
            // SuperAdmin: gunakan school_id dari request, atau default ke sekolah pertama
            $selectedSchoolId = $request->filled('school_id')
                ? $request->school_id
                : ($schools->first() ? $schools->first()->id : null);
        } else {
            $selectedSchoolId = $user->school_id;
        }
        
        // Get unique grade levels for the filter
        $availableGrades = Classroom::where('school_id', $selectedSchoolId)
            ->where('academic_year_id', $selectedYearId)
            ->where('is_active', 1)
            ->distinct()
            ->pluck('grade_level')
            ->sort()
            ->values();
            
        // Get classrooms for selected school (optimized with select)
        $classroomsQuery = Classroom::where('school_id', $selectedSchoolId)
            ->where('academic_year_id', $selectedYearId)
            ->where('is_active', 1);

        $selectedGradeLevel = $request->input('grade_level', 'all');

        // Jika selected = 'all', kita biarkan kosong (tidak di-filter)
        if ($selectedGradeLevel && $selectedGradeLevel !== 'all') {
            $classroomsQuery->where('grade_level', $selectedGradeLevel);
        }

        $selectedShift = $request->input('shift', 'all');
        if ($selectedShift && $selectedShift !== 'all') {
            $classroomsQuery->where('shift', $selectedShift);
        }

        $classrooms = $classroomsQuery->select('id', 'class_name', 'grade_level', 'shift', 'school_id', 'academic_year_id')
            ->orderBy('grade_level', 'asc')
            ->orderBy('shift', 'asc')
            ->orderByRaw('LENGTH(class_name) ASC, class_name ASC')
            ->get();
        
        // Get time slots for selected school and academic year (optimized with caching)
        $cacheKey = "timeslots_school_{$selectedSchoolId}_year_{$selectedYearId}_shift_{$selectedShift}";
        $timeSlots = cache()->remember($cacheKey, 3600, function() use ($selectedSchoolId, $selectedYearId, $selectedShift) {
            $query = TimeSlot::where('school_id', $selectedSchoolId)
                ->where('academic_year_id', $selectedYearId)
                ->where('is_active', 1);

            if ($selectedShift && $selectedShift !== 'all') {
                $query->where(function($q) use ($selectedShift) {
                    $q->where('shift', $selectedShift)->orWhere('shift', 'all')->orWhereNull('shift');
                });
            }

            return $query->select('id', 'school_id', 'academic_year_id', 'day_of_week', 'slot_name', 'shift', 'start_time', 'end_time', 'slot_order', 'is_teaching_slot')
                ->orderBy('slot_order')
                ->get();
        });
        
        // Get all schedules for this school, year, and semester (optimized)
        $schedules = Schedule::where('school_id', $selectedSchoolId)
            ->where('academic_year_id', $selectedYearId)
            ->where('semester', $semester)
            ->select('id', 'teacher_id', 'subject_id', 'classroom_id', 'time_slot_id', 'day_of_week', 'duration_slots', 'school_id', 'academic_year_id', 'semester', 'group_code', 'teaching_assignment_id')
            ->with([
                'teacher:id,full_name,photo,school_id',
                'subject:id,name,subject_name,code,school_id',
                'classroom:id,class_name,school_id',
                'timeSlot:id,slot_name,start_time,end_time,slot_order,day_of_week,is_teaching_slot,school_id',
                'teachingAssignment:id,block_type'
            ])
            ->get();
        
        // PRE-PROCESS: Create optimized schedule lookup array
        // This eliminates nested loops in the view (1800+ iterations → O(1) lookup)
        $scheduleGrid = [];
        $blockedSlots = [];
        
        // Pre-calculate teaching slot hour numbers for each day
        $hourNumberCache = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        foreach ($days as $day) {
            $dayTeachingSlots = $timeSlots->where('day_of_week', $day)
                ->where('is_teaching_slot', true)
                ->sortBy('slot_order')
                ->values();
            
            foreach ($dayTeachingSlots as $index => $slot) {
                $hourNumberCache[$day . '_' . $slot->id] = $index + 1;
            }
        }
        
        foreach ($schedules as $schedule) {
            $key = $schedule->day_of_week . '_' . $schedule->time_slot_id . '_' . $schedule->classroom_id;
            
            if (!isset($scheduleGrid[$key])) {
                $scheduleGrid[$key] = [];
            }
            $scheduleGrid[$key][] = $schedule;
            
            // Pre-calculate blocked slots for multi-duration schedules
            if ($schedule->duration_slots > 1 && $schedule->timeSlot) {
                $startOrder = $schedule->timeSlot->slot_order;
                $daySlots = $timeSlots->where('day_of_week', $schedule->timeSlot->day_of_week)
                    ->where('is_teaching_slot', true)
                    ->where('slot_order', '>', $startOrder)
                    ->sortBy('slot_order')
                    ->take($schedule->duration_slots - 1);
                
                foreach ($daySlots as $slot) {
                    $blockKey = $schedule->day_of_week . '_' . $slot->id . '_' . $schedule->classroom_id;
                    if (!isset($blockedSlots[$blockKey])) {
                        $blockedSlots[$blockKey] = [];
                    }
                    $blockedSlots[$blockKey][] = $schedule->id;
                }
            }
        }
        
        // DYNAMIC CALCULATION: Subject hour sequence (Jam Ke-N per mapel)
        $hourSequences = [];
        // Group by Day and Class to calculate local "Jam Ke-N"
        $groupedSchedules = $schedules->groupBy(function($item) {
            return $item->day_of_week . '_' . $item->classroom_id;
        });

        foreach ($groupedSchedules as $groupKey => $roomSchedules) {
            // Sort schedules in that room by slot order
            $sorted = $roomSchedules->sortBy(function($s) {
                return $s->timeSlot->slot_order ?? 0;
            });
            
            $subjectCounts = [];
            foreach ($sorted as $s) {
                $sid = $s->subject_id;
                if (!isset($subjectCounts[$sid])) {
                    $subjectCounts[$sid] = 0;
                }
                $subjectCounts[$sid]++;
                $hourSequences[$s->id] = $subjectCounts[$sid];
            }
        }
        
        // Teachers and subjects will be loaded via AJAX when modal opens
        // This significantly improves initial page load time
        
        // Get current rotation for block schedule
        $blockSchedule = \App\Models\BlockSchedule::where('school_id', $selectedSchoolId)
            ->where('academic_year_id', $selectedYearId)
            ->where('semester_id', \App\Models\Semester::where('academic_year_id', $selectedYearId)->where('is_active', true)->first()->id ?? 0)
            ->first();
            
        $currentRotation = $blockSchedule ? $blockSchedule->getActiveRotationForDate(\Carbon\Carbon::now()) : 'normal';
        
        return view('admin.schedules.grid', compact(
            'academicYears',
            'schools',
            'classrooms',
            'timeSlots',
            'schedules',
            'scheduleGrid',
            'blockedSlots',
            'hourNumberCache',
            'hourSequences',
            'selectedYearId',
            'selectedSchoolId',
            'semester',
            'availableGrades',
            'selectedGradeLevel',
            'selectedShift',
            'currentRotation'
        ));
    }
    
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'duration_slots' => 'required|integer|min:1|max:12',
            'day_of_week' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,monday,tuesday,wednesday,thursday,friday,saturday',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|in:ganjil,genap',
            'teaching_assignment_id' => 'nullable|exists:teaching_assignments,id',
            'group_code' => 'nullable|string|max:50',
        ]);
        
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $subject = Subject::findOrFail($validated['subject_id']);
        $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);
        $groupCode = $validated['group_code'] ?? null;
        
        // Auto-get group_code from teaching assignment if available
        if (!$groupCode && !empty($validated['teaching_assignment_id'])) {
            $assignment = TeachingAssignment::find($validated['teaching_assignment_id']);
            if ($assignment && $assignment->group_code) {
                $groupCode = $assignment->group_code;
            }
        }
        
        // Convert Indonesian day name to English for storage consistency
        $dayEnglish = $this->mapDayToEnglish($validated['day_of_week']);
        $validated['day_of_week'] = $dayEnglish;
        
        // Check if teacher is competent in this subject
        if (!$teacher->isCompetentIn($validated['subject_id'])) {
            if (!empty($validated['teaching_assignment_id'])) {
                // Auto-register competency when plotting from a teaching assignment (assignment = admin approval)
                $teacher->competentSubjects()->syncWithoutDetaching([$validated['subject_id']]);
            } else {
                return back()->withErrors([
                    'teacher_id' => "Guru {$teacher->full_name} tidak memiliki kompetensi untuk mengajar {$subject->subject_name}. Silakan assign kompetensi terlebih dahulu di halaman Guru."
                ])->withInput();
            }
        }
        
        $semesterNum = ($validated['semester'] === 'ganjil') ? 1 : 2;
        $semester = \App\Models\Semester::where('academic_year_id', $validated['academic_year_id'])
            ->where('semester_number', $semesterNum)
            ->first();
        
        if (!$semester) {
            // Fallback if semester_number is not reliable
            $semesterName = ($validated['semester'] === 'ganjil') ? 'Ganjil' : 'Genap';
            $semester = \App\Models\Semester::where('academic_year_id', $validated['academic_year_id'])
                ->where('semester_name', 'like', "%{$semesterName}%")
                ->first();
        }

        if (!$semester) {
            return back()->with('error', 'Semester ' . $validated['semester'] . ' tidak ditemukan untuk tahun ajaran ini!');
        }
        
        // Check authorization
        if (!$user->isSuperAdmin() && $teacher->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }
        
        // STRICT LIMITATION VALIDATION & GET ASSIGNMENT (Moved up before checkConflicts)
        $assignmentId = $validated['teaching_assignment_id'] ?? null;
        $assignment = null;

        if ($assignmentId) {
            $assignment = TeachingAssignment::find($assignmentId);
        } else {
            // Cari apakah sudah ada penugasan mengajar untuk kombinasi ini
            $assignment = TeachingAssignment::where([
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $validated['subject_id'],
                'classroom_id' => $validated['classroom_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $semester->id,
            ])->first();
        }

        if ($assignment) {
            // Cek batasan JP
            $plottedJP = Schedule::where('teaching_assignment_id', $assignment->id)->sum('duration_slots');
            if (($plottedJP + $validated['duration_slots']) > $assignment->hours_per_week) {
                return back()->with('error', "Validasi Ketat: Guru {$teacher->full_name} hanya ditugaskan {$assignment->hours_per_week} JP untuk mata pelajaran {$subject->subject_name}. Saat ini sudah terjadwal {$plottedJP} JP. Tidak bisa menambah {$validated['duration_slots']} JP lagi.")->withInput();
            }
            $assignmentId = $assignment->id;
        } else {
            // Tolak jika belum ada Penugasan Mengajar
            return back()->with('error', "Validasi Ketat: Guru {$teacher->full_name} belum ditugaskan mengajar {$subject->subject_name} di kelas ini. Silakan tambahkan di menu Penugasan Mengajar terlebih dahulu dengan jumlah JP yang sesuai.")->withInput();
        }

        $blockType = $assignment->block_type ?? 'none';

        // Check conflicts and multi-duration slot availability
        $conflictError = $this->checkConflicts(
            $validated['academic_year_id'],
            $validated['semester'],
            $validated['day_of_week'],
            $validated['time_slot_id'],
            $validated['classroom_id'],
            $validated['teacher_id'],
            $validated['duration_slots'],
            $groupCode,
            null,
            $blockType
        );
        
        if ($conflictError) {
            return back()->with('error', "Jadwal bentrok atau bermasalah: {$conflictError}")->withInput();
        }

        Schedule::create([
            'school_id' => $teacher->school_id,
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'classroom_id' => $validated['classroom_id'],
            'time_slot_id' => $validated['time_slot_id'],
            'duration_slots' => $validated['duration_slots'],
            'day_of_week' => $validated['day_of_week'],
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id' => $semester->id,
            'semester' => $validated['semester'],
            'teaching_assignment_id' => $assignmentId,
            'group_code' => $groupCode,
        ]);
        
        return back()->with('success', 'Jadwal berhasil ditambahkan!');
    }
    
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        return response()->json($schedule);
    }
    
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $schedule = Schedule::findOrFail($id);
        
        if (!$user->isSuperAdmin() && $schedule->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration_slots' => 'nullable|integer|min:1|max:12',
            'teaching_assignment_id' => 'nullable|exists:teaching_assignments,id',
            'group_code' => 'nullable|string|max:50',
        ]);
        
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $subject = Subject::findOrFail($validated['subject_id']);
        
        // Auto-get group_code from teaching assignment if available
        if (empty($validated['group_code']) && !empty($validated['teaching_assignment_id'])) {
            $assignment = TeachingAssignment::find($validated['teaching_assignment_id']);
            if ($assignment && $assignment->group_code) {
                $validated['group_code'] = $assignment->group_code;
            }
        }
        
        // NEW: Check if teacher is competent in this subject
        if (!$teacher->isCompetentIn($validated['subject_id'])) {
            return back()->withErrors([
                'teacher_id' => "Guru {$teacher->full_name} tidak memiliki kompetensi untuk mengajar {$subject->subject_name}. Silakan assign kompetensi terlebih dahulu di halaman Guru."
            ])->withInput();
        }
        
        $durationSlots = $validated['duration_slots'] ?? $schedule->duration_slots;
        
        // STRICT LIMITATION VALIDATION & GET ASSIGNMENT
        $assignmentId = $validated['teaching_assignment_id'] ?? $schedule->teaching_assignment_id;
        $assignment = null;
        if ($assignmentId) {
            $assignment = TeachingAssignment::find($assignmentId);
            if ($assignment) {
                // Hitung jadwal lain yang menggunakan penugasan ini (kecuali jadwal yang sedang di-edit)
                $plottedJP = Schedule::where('teaching_assignment_id', $assignment->id)
                                    ->where('id', '!=', $schedule->id)
                                    ->sum('duration_slots');
                
                if (($plottedJP + $durationSlots) > $assignment->hours_per_week) {
                    return back()->with('error', "Validasi Ketat: Guru {$teacher->full_name} hanya ditugaskan {$assignment->hours_per_week} JP. Sudah ada {$plottedJP} JP lain yang terjadwal. Tidak bisa mengubah jadwal ini menjadi {$durationSlots} JP.")->withInput();
                }
            }
        }
        
        $blockType = $assignment ? ($assignment->block_type ?? 'none') : 'none';
        
        // Check conflicts and multi-duration slot availability (exclude current)
        $conflictError = $this->checkConflicts(
            $schedule->academic_year_id,
            $schedule->semester,
            $schedule->day_of_week,
            $schedule->time_slot_id,
            $schedule->classroom_id,
            $validated['teacher_id'],
            $durationSlots,
            $validated['group_code'] ?? $schedule->group_code,
            $schedule->id,
            $blockType
        );
        
        if ($conflictError) {
            return back()->with('error', "Jadwal bentrok atau bermasalah: {$conflictError}")->withInput();
        }
        
        $schedule->update($validated);
        
        return back()->with('success', 'Jadwal berhasil diupdate!');
    }
    
    public function destroy($id)
    {
        $user = auth()->user();
        $schedule = Schedule::findOrFail($id);
        
        if (!$user->isSuperAdmin() && $schedule->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }
        
        $schedule->delete();
        
        return back()->with('success', 'Jadwal berhasil dihapus!');
    }
    
    /**
     * Get subjects and competent teachers for a specific classroom (AJAX)
     * This filters subjects based on classroom's major/program keahlian
     */
    public function getSubjectsAndTeachersByClassroom(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'semester' => 'nullable|in:ganjil,genap',
        ]);
        
        $classroom = Classroom::with(['major', 'programKeahlian', 'konsentrasiKeahlian', 'school'])
            ->findOrFail($validated['classroom_id']);
        
        // Get subjects based on classroom's major or program keahlian
        $subjectsQuery = Subject::where('school_id', $classroom->school_id)
            ->where('is_active', 1);
        
        // Filter: Get common subjects (no major/program) OR subjects matching classroom's major/program
        if (!$classroom->isCombinedClass()) {
            $subjectsQuery->where(function($query) use ($classroom) {
                // Common subjects (no major_id and no program_keahlian_id)
                $query->whereNull('major_id')->whereNull('program_keahlian_id');
                
                // OR subjects for classroom's major (SMA/SMP)
                if ($classroom->major_id) {
                    $query->orWhere('major_id', $classroom->major_id);
                }
                
                // OR subjects for classroom's program keahlian (SMK)
                if ($classroom->program_keahlian_id) {
                    $query->orWhere('program_keahlian_id', $classroom->program_keahlian_id);
                }
            });
        }
        
        $subjects = $subjectsQuery->orderBy('category')->orderBy('name')->get();
        
        // For each subject, get competent teachers with their competency info
        $subjectsWithTeachers = $subjects->map(function ($subject) {
            $teachers = $subject->competentTeachers()
                ->where('teachers.is_active', 1)
                ->select('teachers.id', 'teachers.full_name', 'teachers.photo')
                ->get()
                ->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->full_name,
                        'photo' => $teacher->photo,
                    ];
                });
            
            return [
                'id' => $subject->id,
                'name' => $subject->name ?? $subject->subject_name,
                'code' => $subject->code ?? $subject->subject_code,
                'category' => $subject->category ?? 'Umum',
                'teachers' => $teachers,
            ];
        });
        
        // Get teaching assignments for this classroom (if academic_year_id provided)
        $assignments = [];
        if (!empty($validated['academic_year_id'])) {
            $assignmentsQuery = TeachingAssignment::with(['teacher', 'subject'])
                ->where('classroom_id', $validated['classroom_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('is_active', true);
            
            // Filter by semester if provided
            if (!empty($validated['semester'])) {
                $semesterNum = ($validated['semester'] === 'ganjil') ? 1 : 2;
                $semesterRecord = \App\Models\Semester::where('academic_year_id', $validated['academic_year_id'])
                    ->where('semester_number', $semesterNum)
                    ->first();
                
                if (!$semesterRecord) {
                    $semesterName = ($validated['semester'] === 'ganjil') ? 'Ganjil' : 'Genap';
                    $semesterRecord = \App\Models\Semester::where('academic_year_id', $validated['academic_year_id'])
                        ->where('semester_name', 'like', "%{$semesterName}%")
                        ->first();
                }

                if ($semesterRecord) {
                    $assignmentsQuery->where('semester_id', $semesterRecord->id);
                }
            }
            
            $assignmentsData = $assignmentsQuery->get();
            
            $assignments = $assignmentsData->map(function ($assignment) {
                // Count plotted JP: sum of duration_slots for schedules linked to this assignment
                $plottedJP = (int) Schedule::where('teaching_assignment_id', $assignment->id)
                    ->sum('duration_slots');
                
                return [
                    'id' => $assignment->id,
                    'teacher_id' => $assignment->teacher_id,
                    'teacher_name' => $assignment->teacher->full_name ?? '-',
                    'subject_id' => $assignment->subject_id,
                    'subject_name' => $assignment->subject->name ?? $assignment->subject->subject_name ?? '-',
                    'subject_code' => $assignment->subject->code ?? $assignment->subject->subject_code ?? '',
                    'hours_per_week' => $assignment->hours_per_week,
                    'plotted_jp' => $plottedJP,
                    'remaining_jp' => max(0, $assignment->hours_per_week - $plottedJP),
                    'is_complete' => $plottedJP >= $assignment->hours_per_week,
                    'photo' => $assignment->teacher->photo ?? null,
                    'group_code' => $assignment->group_code,
                    'block_type' => $assignment->block_type ?? 'none',
                ];
            })->values();
        }
        
        return response()->json([
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->class_name,
                'level' => $classroom->school->level ?? '',
                'major' => $classroom->major->major_name ?? null,
                'program_keahlian' => $classroom->programKeahlian->nama ?? null,
                'konsentrasi_keahlian' => $classroom->konsentrasiKeahlian->nama ?? null,
            ],
            'subjects' => $subjectsWithTeachers,
            'assignments' => $assignments,
        ]);
    }
    
    /**
     * Convert Indonesian day name to English for database
     */
    private function mapDayToEnglish($dayIndonesian)
    {
        $mapping = [
            'Senin' => 'monday',
            'Selasa' => 'tuesday',
            'Rabu' => 'wednesday',
            'Kamis' => 'thursday',
            'Jumat' => 'friday',
            'Sabtu' => 'saturday',
        ];
        
        // If already English, return as-is (lowercase)
        $lower = strtolower($dayIndonesian);
        if (in_array($lower, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])) {
            return $lower;
        }
        
        return $mapping[$dayIndonesian] ?? 'monday';
    }
    
    /**
     * Export schedules to Excel
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        
        $schoolId = $request->get('school_id', $user->school_id);
        $academicYearId = $request->get('academic_year_id');
        $semester = $request->get('semester', 'ganjil');
        $classroomId = $request->get('classroom_id');
        $day = $request->get('day');
        
        // Authorization check
        if (!$user->isSuperAdmin() && $schoolId != $user->school_id) {
            abort(403, 'Unauthorized');
        }
        
        $school = School::find($schoolId);
        $academicYear = AcademicYear::find($academicYearId);
        $classroom = $classroomId ? Classroom::find($classroomId) : null;
        
        $filename = 'Jadwal_Pelajaran';
        if ($classroom) {
            $filename .= '_' . str_replace([' ', '/', '\\'], '_', $classroom->class_name);
        }
        if ($day) {
            $filename .= '_' . $day;
        }
        // Clean academic year (remove / and \ characters)
        $yearClean = str_replace(['/', '\\'], '-', $academicYear->year);
        $filename .= '_' . $yearClean . '_' . ucfirst($semester);
        $filename .= '_' . date('YmdHis') . '.xlsx';
        
        return Excel::download(
            new ScheduleExport($schoolId, $academicYearId, $semester, $classroomId, $day),
            $filename
        );
    }

    /**
     * Get teachers and subjects for modal (AJAX endpoint)
     * This improves initial page load by loading data only when needed
     */
    public function getModalData(Request $request)
    {
        $user = auth()->user();
        $schoolId = $request->get('school_id', $user->school_id);

        // Authorization check
        if (!$user->isSuperAdmin() && $schoolId != $user->school_id) {
            abort(403, 'Unauthorized');
        }

        // Cache teachers and subjects data
        $cacheKeyTeachers = "teachers_modal_{$schoolId}";
        $cacheKeySubjects = "subjects_modal_{$schoolId}";

        $teachers = cache()->remember($cacheKeyTeachers, 1800, function() use ($schoolId) {
            return Teacher::where('school_id', $schoolId)
                ->where('is_active', 1)
                ->select('id', 'full_name', 'photo', 'school_id')
                ->orderBy('full_name')
                ->get();
        });

        $subjects = cache()->remember($cacheKeySubjects, 1800, function() use ($schoolId) {
            return Subject::where('school_id', $schoolId)
                ->where('is_active', 1)
                ->select('id', 'name', 'subject_name', 'code', 'category', 'school_id')
                ->orderBy('subject_name')
                ->get();
        });

        return response()->json([
            'teachers' => $teachers,
            'subjects' => $subjects
        ]);
    }

    /**
     * Check for schedule conflicts (teacher or classroom occupancy) across the duration of slots.
     */
    private function checkConflicts($academicYearId, $semester, $dayOfWeek, $timeSlotId, $classroomId, $teacherId, $durationSlots, $groupCode = null, $excludeScheduleId = null, $incomingBlockType = 'none')
    {
        $timeSlot = TimeSlot::find($timeSlotId);
        if (!$timeSlot) {
            return 'Time slot tidak ditemukan.';
        }

        $schoolId = $timeSlot->school_id;
        $dayOfWeekEnglish = $this->mapDayToEnglish($dayOfWeek);

        // Fetch ALL time slots for this school and day, ordered
        $allDaySlots = TimeSlot::where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('day_of_week', $dayOfWeekEnglish)
            ->where('is_teaching_slot', true)
            ->orderBy('slot_order')
            ->get();

        // 1. Target slots covered by the incoming schedule
        $targetSlotIds = [];
        $currentOrder = $timeSlot->slot_order;
        
        foreach ($allDaySlots as $slot) {
            if ($slot->slot_order >= $currentOrder && count($targetSlotIds) < $durationSlots) {
                $targetSlotIds[] = $slot->id;
            }
        }

        if (count($targetSlotIds) < $durationSlots) {
            return "Tidak cukup slot tersedia. Hanya ada " . count($targetSlotIds) . " slot (termasuk istirahat).";
        }

        // 2. Fetch ALL schedules on this day for the classroom or teacher
        $query = Schedule::where('academic_year_id', $academicYearId)
            ->where('semester', $semester)
            ->where('day_of_week', $dayOfWeekEnglish)
            ->where(function($q) use ($teacherId, $classroomId, $groupCode) {
                $q->where('teacher_id', $teacherId)
                  ->orWhere('classroom_id', $classroomId);
                  
                if ($groupCode) {
                    $q->orWhere('group_code', $groupCode);
                }
            });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $existingSchedules = $query->with(['teacher', 'subject', 'classroom', 'timeSlot', 'teachingAssignment'])->get();
        
        foreach ($existingSchedules as $schedule) {
            if (!$schedule->timeSlot) continue;
            
            $startOrder = $schedule->timeSlot->slot_order;
            $duration = $schedule->duration_slots;
            
            // Calculate covered slots for this existing schedule
            $coveredSlotIds = [];
            foreach ($allDaySlots as $slot) {
                if ($slot->slot_order >= $startOrder && count($coveredSlotIds) < $duration) {
                    $coveredSlotIds[] = $slot->id;
                }
            }

            // Check intersection with target slots
            $overlap = array_intersect($targetSlotIds, $coveredSlotIds);
            
            if (!empty($overlap)) {
                $slotName = $allDaySlots->firstWhere('id', current($overlap))->slot_name ?? '';

                // A. Check teacher conflict
                if ($schedule->teacher_id == $teacherId) {
                    // Check group code exception (Gabungan antar kelas)
                    if ($groupCode && $schedule->group_code === $groupCode) {
                        // It's allowed to overlap for the same teacher if group code matches (Gabungan)
                    } else {
                        if ($schedule->classroom_id == $classroomId) {
                            return "Guru ini sudah memiliki jadwal pada kelas yang sama di waktu yang bersinggungan ($slotName).";
                        }
                        return "Guru {$schedule->teacher->full_name} sudah mengajar {$schedule->subject->subject_name} di kelas {$schedule->classroom->class_name} pada waktu yang bersinggungan ($slotName).";
                    }
                }
                
                // B. Check classroom conflict (BLOCK SYSTEM LOGIC)
                if ($schedule->classroom_id == $classroomId) {
                    $existingBlockType = $schedule->teachingAssignment->block_type ?? 'none';
                    
                    // If BOTH incoming and existing are parallel (e.g. Agama Islam & Agama Kristen & Agama Katolik), ALLOWED!
                    if ($incomingBlockType === 'parallel' && $existingBlockType === 'parallel') {
                        continue;
                    }

                    if ($incomingBlockType === 'none' || $existingBlockType === 'none') {
                        return "Jadwal Reguler (Semua Siswa) tidak bisa berjalan bersamaan dengan jadwal lain di kelas ini ($slotName).";
                    }

                    if ($incomingBlockType === 'parallel' || $existingBlockType === 'parallel') {
                        return "Jadwal Paralel/Agama tidak bisa berjalan bersamaan dengan jadwal Sistem Blok ($slotName).";
                    }

                    if ($incomingBlockType === 'all' && $existingBlockType === 'all') {
                        return "Kelompok A sudah memiliki jadwal lain di waktu bersinggungan ($slotName).";
                    }

                    if ($incomingBlockType === 'split' && $existingBlockType === 'split') {
                        return "Kelompok B sudah memiliki jadwal lain di waktu bersinggungan ($slotName).";
                    }
                    
                    // If one is 'all' and the other is 'split', it is ALLOWED.
                }
            }
        }

        return null;
    }

    /**
     * Clear cache for time slots and modal data
     */
    public function clearCache(Request $request)
    {
        $user = auth()->user();
        $schoolId = $request->get('school_id', $user->school_id);

        cache()->forget("timeslots_school_{$schoolId}");
        cache()->forget("teachers_modal_{$schoolId}");
        cache()->forget("subjects_modal_{$schoolId}");

        return response()->json(['message' => 'Cache cleared successfully']);
    }
}

