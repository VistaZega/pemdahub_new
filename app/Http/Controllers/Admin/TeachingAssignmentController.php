<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\School;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Schedule;

/**
 * TeachingAssignmentController — CRUD for teaching_assignments table
 * 
 * Flow: TeachingAssignment (penugasan) → Schedule (plot jadwal)
 * Controller ini mengelola PENUGASAN mengajar, bukan jadwal.
 */
class TeachingAssignmentController extends Controller
{
    /**
     * Index — Daftar guru dengan penugasan mengajar
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentYear = AcademicYear::where('is_active', 1)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($currentYear ? $currentYear->id : null);

        // Filter semesters by selected academic year
        $semesters = $selectedYearId
            ? Semester::where('academic_year_id', $selectedYearId)->orderBy('semester_number')->get()
            : Semester::orderBy('id')->get();

        $selectedSemesterId = $request->filled('semester_id')
            ? $request->semester_id
            : ($activeSemester && $activeSemester->academic_year_id == $selectedYearId ? $activeSemester->id : $semesters->first()?->id);

        // Base query — teachers with their teaching assignments
        $query = Teacher::with(['school', 'employee', 'teachingAssignments' => function ($q) use ($selectedYearId, $selectedSemesterId) {
            $q->where('academic_year_id', $selectedYearId);
            if ($selectedSemesterId) {
                $q->where('semester_id', $selectedSemesterId);
            }
            $q->with(['classroom', 'subject', 'semester']);
        }]);

        // Auto-filter by school
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        if ($request->filled('school_id') && $user->isSuperAdmin()) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('teacher_code', 'like', "%{$search}%");
            });
        }

        $teachers = $query->where('is_active', 1)->paginate(15)->withQueryString();

        // Calculate totals per teacher
        foreach ($teachers as $teacher) {
            $teacher->total_teaching_hours = $teacher->teachingAssignments->sum('hours_per_week');
            $teacher->total_assignments = $teacher->teachingAssignments->count();
        }

        $schools = $user->isSuperAdmin()
            ? School::where('is_active', 1)->schoolsOnly()->get()
            : School::where('id', $user->school_id)->get();

        // Count unlinked schedules (for sync button indicator)
        $schoolIdForCount = $user->isSuperAdmin()
            ? ($request->filled('school_id') ? $request->school_id : null)
            : $user->school_id;
        
        $unlinkedScheduleCount = Schedule::whereNull('teaching_assignment_id')
            ->where('academic_year_id', $selectedYearId)
            ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId))
            ->when($schoolIdForCount, fn($q) => $q->where('school_id', $schoolIdForCount))
            ->count();

        return view('admin.assignments.teaching.index', compact(
            'teachers',
            'schools',
            'academicYears',
            'semesters',
            'selectedYearId',
            'selectedSemesterId',
            'unlinkedScheduleCount'
        ));
    }

    /**
     * Create — Form buat penugasan mengajar baru
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentYear = AcademicYear::where('is_active', 1)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        // Filter semesters by selected or current academic year
        $selectedAcademicYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($currentYear ? $currentYear->id : null);
        $semesters = $selectedAcademicYearId
            ? Semester::where('academic_year_id', $selectedAcademicYearId)->orderBy('semester_number')->get()
            : Semester::orderBy('id')->get();

        $teacherId = $request->teacher_id;
        $selectedTeacher = $teacherId ? Teacher::with('school')->find($teacherId) : null;

        // Prioritas: (1) teacher's school_id, (2) explicit school_id from request
        // Gunakan filled() bukan ?? agar empty string "" dari URL tidak menimpa fallback
        $selectedSchoolId = $selectedTeacher
            ? $selectedTeacher->school_id
            : ($request->filled('school_id') ? $request->school_id : null);

        $selectedSemesterId = $request->filled('semester_id')
            ? $request->semester_id
            : ($activeSemester && $activeSemester->academic_year_id == $selectedAcademicYearId ? $activeSemester->id : $semesters->first()?->id);

        // Get schools for filter
        $schools = $user->isSuperAdmin()
            ? School::where('is_active', 1)->schoolsOnly()->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        // Get teachers (filtered by school if selected)
        $teacherQuery = Teacher::where('is_active', 1)
            ->with(['school', 'employee'])
            ->orderBy('full_name');

        if (!$user->isSuperAdmin()) {
            $teacherQuery->where('school_id', $user->school_id);
        } elseif ($selectedSchoolId) {
            $teacherQuery->where('school_id', $selectedSchoolId);
        }

        $teachers = $teacherQuery->get();

        $classrooms = collect([]);
        $subjects = collect([]);
        $currentAssignments = collect([]);

        if ($selectedTeacher) {
            $classrooms = Classroom::where('school_id', $selectedTeacher->school_id)
                ->where('academic_year_id', $selectedAcademicYearId)
                ->where('is_active', 1)
                ->orderBy('grade_level')
                ->orderBy('class_name')
                ->get();

            // Use competent subjects if available, fallback to all school subjects
            $competentSubjectIds = $selectedTeacher->competentSubjects()->pluck('subjects.id');
            if ($competentSubjectIds->isNotEmpty()) {
                $subjects = Subject::whereIn('id', $competentSubjectIds)
                    ->where('is_active', 1)
                    ->orderBy('subject_name')
                    ->get();
            } else {
                $subjects = Subject::where('school_id', $selectedTeacher->school_id)
                    ->where('is_active', 1)
                    ->orderBy('subject_name')
                    ->get();
            }

            // Existing teaching assignments
            if ($selectedAcademicYearId) {
                $currentAssignments = TeachingAssignment::where('teacher_id', $teacherId)
                    ->where('academic_year_id', $selectedAcademicYearId)
                    ->when($selectedSemesterId, fn ($q) => $q->where('semester_id', $selectedSemesterId))
                    ->with(['classroom', 'subject', 'semester'])
                    ->get();
            }
        }

        // --- SISTEM GEMBOK PERJANJIAN KINERJA (Khusus SMK) ---
        $hasContract = true;
        if ($selectedTeacher && $selectedTeacher->school) {
            $school = $selectedTeacher->school;
            if (strtoupper($school->type) === 'SMK' || str_contains(strtolower($school->name), 'smk') || str_contains(strtolower($school->name), 'kejuruan')) {
                $hasContract = \App\Models\PerformanceContract::where('employee_id', $selectedTeacher->employee_id)
                    ->where('academic_year_id', $selectedAcademicYearId)
                    ->whereIn('contract_type', [\App\Models\PerformanceContract::TYPE_PKG_KEJURUAN, \App\Models\PerformanceContract::TYPE_PKG_UMUM])
                    ->where('status', \App\Models\PerformanceContract::STATUS_APPROVED_BY_YAYASAN)
                    ->exists();
            }
        }

        return view('admin.assignments.teaching.create', compact(
            'teachers',
            'schools',
            'classrooms',
            'subjects',
            'academicYears',
            'semesters',
            'currentYear',
            'activeSemester',
            'selectedTeacher',
            'selectedSchoolId',
            'selectedAcademicYearId',
            'selectedSemesterId',
            'currentAssignments',
            'hasContract'
        ));
    }

    /**
     * Store — Simpan penugasan mengajar baru
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.classroom_id' => 'required|exists:classrooms,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.hours_per_week' => 'required|integer|min:1|max:40',
            'assignments.*.is_main_teacher' => 'nullable|boolean',
            'assignments.*.group_code' => 'nullable|string|max:50',
            'assignments.*.block_type' => 'nullable|in:none,all,split,parallel',
        ]);

        $teacher = Teacher::findOrFail($validated['teacher_id']);
        if (!$user->isSuperAdmin() && $teacher->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }

        // --- SISTEM GEMBOK PERJANJIAN KINERJA (Khusus SMK) ---
        $school = \App\Models\School::find($teacher->school_id);
        if ($school && (strtoupper($school->type) === 'SMK' || str_contains(strtolower($school->name), 'smk') || str_contains(strtolower($school->name), 'kejuruan'))) {
            $hasContract = \App\Models\PerformanceContract::where('employee_id', $teacher->employee_id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->whereIn('contract_type', [\App\Models\PerformanceContract::TYPE_PKG_KEJURUAN, \App\Models\PerformanceContract::TYPE_PKG_UMUM])
                ->where('status', \App\Models\PerformanceContract::STATUS_APPROVED_BY_YAYASAN)
                ->exists();

            if (!$hasContract) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Akses Ditolak! Guru atas nama ' . $teacher->full_name . ' belum memiliki Perjanjian Kinerja Mengajar (2A/2B) yang disetujui Yayasan untuk Tahun Pelajaran ini.');
            }
        }
        // --- END GEMBOK ---

        DB::beginTransaction();
        try {
            $created = 0;
            foreach ($validated['assignments'] as $data) {
                // Check for duplicates (same teacher+subject+classroom+year+semester)
                $exists = TeachingAssignment::where('teacher_id', $validated['teacher_id'])
                    ->where('subject_id', $data['subject_id'])
                    ->where('classroom_id', $data['classroom_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->exists();

                if ($exists) {
                    $classroom = Classroom::find($data['classroom_id']);
                    $subject = Subject::find($data['subject_id']);
                    throw new \Exception(
                        "Penugasan sudah ada: {$subject->subject_name} di {$classroom->class_name}"
                    );
                }

                TeachingAssignment::create([
                    'teacher_id' => $validated['teacher_id'],
                    'subject_id' => $data['subject_id'],
                    'classroom_id' => $data['classroom_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'semester_id' => $validated['semester_id'],
                    'hours_per_week' => $data['hours_per_week'],
                    'teaching_load_type' => 'wajib',
                    'is_main_teacher' => $data['is_main_teacher'] ?? false,
                    'group_code' => $data['group_code'] ?? null,
                    'block_type' => $data['block_type'] ?? 'none',
                    'is_active' => true,
                ]);

                // Auto-register teacher competency for assigned subject
                if (!$teacher->isCompetentIn($data['subject_id'])) {
                    $teacher->competentSubjects()->syncWithoutDetaching([$data['subject_id']]);
                }

                $created++;
            }

            DB::commit();

            return redirect()
                ->route('admin.assignments.teaching.index', [
                    'academic_year_id' => $validated['academic_year_id'],
                    'semester_id' => $validated['semester_id'],
                ])
                ->with('success', "{$created} penugasan mengajar berhasil disimpan untuk {$teacher->full_name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan penugasan mengajar: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Edit — Form edit penugasan mengajar seorang guru
     */
    public function edit(Request $request, $teacherId)
    {
        $user = auth()->user();
        $teacher = Teacher::with(['school', 'employee'])->findOrFail($teacherId);

        if (!$user->isSuperAdmin() && $teacher->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentYear = AcademicYear::where('is_active', 1)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($currentYear ? $currentYear->id : null);

        // Filter semesters by selected academic year
        $semesters = $selectedYearId
            ? Semester::where('academic_year_id', $selectedYearId)->orderBy('semester_number')->get()
            : Semester::orderBy('id')->get();

        $selectedSemesterId = $request->filled('semester_id')
            ? $request->semester_id
            : ($activeSemester && $activeSemester->academic_year_id == $selectedYearId ? $activeSemester->id : $semesters->first()?->id);

        $classrooms = Classroom::where('school_id', $teacher->school_id)
            ->where('academic_year_id', $selectedYearId)
            ->where('is_active', 1)
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        // Use competent subjects if available
        $competentSubjectIds = $teacher->competentSubjects()->pluck('subjects.id');
        if ($competentSubjectIds->isNotEmpty()) {
            $subjects = Subject::whereIn('id', $competentSubjectIds)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get();
        } else {
            $subjects = Subject::where('school_id', $teacher->school_id)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get();
        }

        // Current teaching assignments
        $assignments = TeachingAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $selectedYearId)
            ->when($selectedSemesterId, fn ($q) => $q->where('semester_id', $selectedSemesterId))
            ->with(['classroom', 'subject', 'semester'])
            ->get();

        return view('admin.assignments.teaching.edit', compact(
            'teacher',
            'classrooms',
            'subjects',
            'academicYears',
            'semesters',
            'currentYear',
            'activeSemester',
            'selectedYearId',
            'selectedSemesterId',
            'assignments'
        ));
    }

    /**
     * Update — Update satu penugasan mengajar
     */
    public function update(Request $request, TeachingAssignment $assignment)
    {
        $user = auth()->user();
        $teacher = $assignment->teacher;

        if (!$user->isSuperAdmin() && $teacher->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'hours_per_week' => 'required|integer|min:1|max:40',
            'is_main_teacher' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'group_code' => 'nullable|string|max:50',
            'block_type' => 'nullable|in:none,all,split,parallel',
        ]);

        // Check for duplicates if subject/classroom changed
        if ($validated['subject_id'] != $assignment->subject_id || $validated['classroom_id'] != $assignment->classroom_id) {
            $duplicate = TeachingAssignment::where('teacher_id', $assignment->teacher_id)
                ->where('subject_id', $validated['subject_id'])
                ->where('classroom_id', $validated['classroom_id'])
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('semester_id', $assignment->semester_id)
                ->where('id', '!=', $assignment->id)
                ->exists();

            if ($duplicate) {
                return back()->with('error', 'Penugasan untuk mapel dan kelas tersebut sudah ada.');
            }
        }

        $assignment->update([
            'classroom_id' => $validated['classroom_id'],
            'subject_id' => $validated['subject_id'],
            'hours_per_week' => $validated['hours_per_week'],
            'is_main_teacher' => $validated['is_main_teacher'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'group_code' => $validated['group_code'] ?? null,
            'block_type' => $validated['block_type'] ?? 'none',
        ]);

        return back()->with('success', 'Penugasan mengajar berhasil diperbarui.');
    }

    /**
     * Destroy — Hapus satu penugasan mengajar
     */
    public function destroy(TeachingAssignment $assignment)
    {
        $user = auth()->user();
        $teacher = $assignment->teacher;

        if (!$user->isSuperAdmin() && $teacher->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }

        // Check if any schedules reference this assignment
        $scheduleCount = \App\Models\Schedule::where('teaching_assignment_id', $assignment->id)->count();
        if ($scheduleCount > 0) {
            return back()->with('error', "Tidak bisa dihapus: {$scheduleCount} jadwal masih mereferensikan penugasan ini. Hapus jadwal terlebih dahulu.");
        }

        $assignment->delete();

        return back()->with('success', 'Penugasan mengajar berhasil dihapus.');
    }

    /**
     * Bulk Destroy — Hapus semua penugasan guru untuk tahun/semester tertentu
     */
    public function bulkDestroy(Request $request, $teacherId)
    {
        $user = auth()->user();
        $teacher = Teacher::findOrFail($teacherId);

        if (!$user->isSuperAdmin() && $teacher->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        // Check schedules
        $scheduleCount = \App\Models\Schedule::whereHas('teachingAssignment', function ($q) use ($teacherId, $validated) {
            $q->where('teacher_id', $teacherId)
              ->where('academic_year_id', $validated['academic_year_id'])
              ->where('semester_id', $validated['semester_id']);
        })->count();

        if ($scheduleCount > 0) {
            return back()->with('error', "Tidak bisa dihapus: {$scheduleCount} jadwal masih mereferensikan penugasan ini.");
        }

        TeachingAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('semester_id', $validated['semester_id'])
            ->delete();

        return back()->with('success', 'Semua penugasan mengajar berhasil dihapus.');
    }

    /**
     * API: Get semesters filtered by academic year (for AJAX)
     */
    public function getSemestersByYear($academicYearId)
    {
        $semesters = Semester::where('academic_year_id', $academicYearId)
            ->orderBy('semester_number')
            ->get(['id', 'semester_name', 'semester_number', 'is_active']);

        return response()->json($semesters);
    }

    /**
     * Copy all teaching assignments (and optionally schedules) from one semester to another
     */
    public function copyToSemester(Request $request)
    {
        $validated = $request->validate([
            'source_academic_year_id' => 'required|exists:academic_years,id',
            'source_semester_id' => 'required|exists:semesters,id',
            'target_semester_id' => 'required|exists:semesters,id|different:source_semester_id',
            'copy_schedules' => 'nullable|boolean',
        ]);

        $sourceSemester = Semester::findOrFail($validated['source_semester_id']);
        $targetSemester = Semester::findOrFail($validated['target_semester_id']);
        $copySchedules = !empty($validated['copy_schedules']);

        // Determine semester label (ganjil/genap) for schedules
        $targetSemesterLabel = $targetSemester->semester_number == 1 ? 'ganjil' : 'genap';

        // Get all assignments from source
        $sourceAssignments = TeachingAssignment::where('academic_year_id', $validated['source_academic_year_id'])
            ->where('semester_id', $validated['source_semester_id'])
            ->get();

        if ($sourceAssignments->isEmpty()) {
            return back()->with('error', 'Tidak ada penugasan di semester sumber untuk disalin.');
        }

        DB::beginTransaction();
        try {
            $copiedAssignments = 0;
            $skippedAssignments = 0;
            $copiedSchedules = 0;
            $skippedSchedules = 0;

            foreach ($sourceAssignments as $assignment) {
                // Check if duplicate already exists in target
                $existingAssignment = TeachingAssignment::where('teacher_id', $assignment->teacher_id)
                    ->where('subject_id', $assignment->subject_id)
                    ->where('classroom_id', $assignment->classroom_id)
                    ->where('academic_year_id', $targetSemester->academic_year_id)
                    ->where('semester_id', $targetSemester->id)
                    ->first();

                if ($existingAssignment) {
                    $skippedAssignments++;
                    $newAssignment = $existingAssignment;
                } else {
                    $newAssignment = TeachingAssignment::create([
                        'teacher_id' => $assignment->teacher_id,
                        'subject_id' => $assignment->subject_id,
                        'classroom_id' => $assignment->classroom_id,
                        'academic_year_id' => $targetSemester->academic_year_id,
                        'semester_id' => $targetSemester->id,
                        'hours_per_week' => $assignment->hours_per_week,
                        'is_main_teacher' => $assignment->is_main_teacher,
                        'teaching_load_type' => $assignment->teaching_load_type,
                        'is_active' => true,
                    ]);
                    $copiedAssignments++;
                }

                // Copy schedules if requested
                if ($copySchedules) {
                    $sourceSchedules = Schedule::where('teaching_assignment_id', $assignment->id)->get();

                    foreach ($sourceSchedules as $schedule) {
                        // Check if same schedule already exists in target
                        $scheduleExists = Schedule::where('teacher_id', $schedule->teacher_id)
                            ->where('classroom_id', $schedule->classroom_id)
                            ->where('time_slot_id', $schedule->time_slot_id)
                            ->where('day_of_week', $schedule->day_of_week)
                            ->where('academic_year_id', $targetSemester->academic_year_id)
                            ->where('semester_id', $targetSemester->id)
                            ->exists();

                        if ($scheduleExists) {
                            $skippedSchedules++;
                            continue;
                        }

                        Schedule::create([
                            'school_id' => $schedule->school_id,
                            'teacher_id' => $schedule->teacher_id,
                            'subject_id' => $schedule->subject_id,
                            'classroom_id' => $schedule->classroom_id,
                            'time_slot_id' => $schedule->time_slot_id,
                            'duration_slots' => $schedule->duration_slots,
                            'day_of_week' => $schedule->day_of_week,
                            'academic_year_id' => $targetSemester->academic_year_id,
                            'semester_id' => $targetSemester->id,
                            'semester' => $targetSemesterLabel,
                            'teaching_assignment_id' => $newAssignment->id,
                        ]);
                        $copiedSchedules++;
                    }
                }
            }

            DB::commit();

            $message = "{$copiedAssignments} penugasan berhasil disalin ke {$targetSemester->semester_name}.";
            if ($skippedAssignments > 0) {
                $message .= " ({$skippedAssignments} penugasan dilewati karena sudah ada.)";
            }
            if ($copySchedules) {
                $message .= " {$copiedSchedules} jadwal berhasil disalin.";
                if ($skippedSchedules > 0) {
                    $message .= " ({$skippedSchedules} jadwal dilewati karena sudah ada.)";
                }
            }

            return redirect()->route('admin.assignments.teaching.index', [
                'academic_year_id' => $targetSemester->academic_year_id,
                'semester_id' => $targetSemester->id,
            ])->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyalin penugasan & jadwal: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyalin: ' . $e->getMessage());
        }
    }

    /**
     * Sync — Generate teaching assignments from existing schedule data
     * 
     * This method scans all schedules for a given academic year & semester,
     * creates missing TeachingAssignment records, links schedules to them,
     * and updates hours_per_week based on actual schedule data.
     */
    public function syncFromSchedules(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $semester = Semester::findOrFail($validated['semester_id']);
        $schoolId = $user->isSuperAdmin()
            ? $request->get('school_id')
            : $user->school_id;

        // Get all unique teacher-subject-classroom combos from schedules
        $scheduleCombos = \App\Models\Schedule::select(
                'teacher_id', 'subject_id', 'classroom_id',
                'academic_year_id', 'semester_id',
                DB::raw('SUM(duration_slots) as total_jp')
            )
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('semester_id', $validated['semester_id'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->groupBy('teacher_id', 'subject_id', 'classroom_id', 'academic_year_id', 'semester_id')
            ->get();

        if ($scheduleCombos->isEmpty()) {
            return back()->with('error', 'Tidak ada data jadwal untuk periode ini.');
        }

        DB::beginTransaction();
        try {
            $created = 0;
            $updated = 0;
            $linked = 0;

            foreach ($scheduleCombos as $combo) {
                // Find or create teaching assignment
                $assignment = TeachingAssignment::firstOrNew([
                    'teacher_id' => $combo->teacher_id,
                    'subject_id' => $combo->subject_id,
                    'classroom_id' => $combo->classroom_id,
                    'academic_year_id' => $combo->academic_year_id,
                    'semester_id' => $combo->semester_id,
                ]);

                $isNew = !$assignment->exists;

                $assignment->hours_per_week = (int) $combo->total_jp;
                $assignment->teaching_load_type = $assignment->teaching_load_type ?? 'wajib';
                $assignment->is_active = true;
                $assignment->save();

                if ($isNew) {
                    $created++;
                } else {
                    $updated++;
                }

                // Link all matching schedules to this assignment
                $linkedCount = \App\Models\Schedule::where('teacher_id', $combo->teacher_id)
                    ->where('subject_id', $combo->subject_id)
                    ->where('classroom_id', $combo->classroom_id)
                    ->where('academic_year_id', $combo->academic_year_id)
                    ->where('semester_id', $combo->semester_id)
                    ->whereNull('teaching_assignment_id')
                    ->update(['teaching_assignment_id' => $assignment->id]);

                $linked += $linkedCount;

                // Auto-register teacher competency
                $teacher = Teacher::find($combo->teacher_id);
                if ($teacher && !$teacher->isCompetentIn($combo->subject_id)) {
                    $teacher->competentSubjects()->syncWithoutDetaching([$combo->subject_id]);
                }
            }

            DB::commit();

            $message = "Sinkronisasi berhasil! {$created} penugasan baru dibuat, {$updated} diperbarui, {$linked} jadwal ditautkan.";

            return redirect()->route('admin.assignments.teaching.index', [
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $validated['semester_id'],
            ])->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal sinkronisasi jadwal ke penugasan: ' . $e->getMessage());
            return back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}
