<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    public function __construct(
        private GradeService $gradeService
    ) {}

    /**
     * Get authenticated teacher
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
     * Get active academic year
     */
    private function getActiveYear(): ?AcademicYear
    {
        return AcademicYear::where('is_active', true)->first();
    }

    /**
     * Get classrooms the teacher is assigned to
     * Each classroom is annotated with is_homeroom (boolean)
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
            ->orderBy('class_name')
            ->get()
            ->each(function ($classroom) use ($teacher) {
                $classroom->is_homeroom = ($classroom->homeroom_teacher_id == $teacher->id);
            });
    }

    /**
     * Check if teacher is homeroom teacher for a given classroom
     */
    private function isHomeroomTeacher(Teacher $teacher, int $classroomId): bool
    {
        return Classroom::where('id', $classroomId)
            ->where('homeroom_teacher_id', $teacher->id)
            ->exists();
    }

    /**
     * Get subjects for a classroom based on teacher role:
     * - Wali kelas: ALL subjects in the classroom (from any teacher's schedule/assignment)
     * - Regular teacher: only subjects the teacher personally teaches
     */
    private function getTeacherSubjects(Teacher $teacher, int $classroomId): \Illuminate\Support\Collection
    {
        $isHomeroom = $this->isHomeroomTeacher($teacher, $classroomId);

        if ($isHomeroom) {
            // Wali kelas can access ALL subjects in this classroom
            $scheduleSubjectIds = Schedule::where('classroom_id', $classroomId)
                ->pluck('subject_id')
                ->unique();

            $activeYear = $this->getActiveYear();
            $assignmentSubjectIds = collect();
            if ($activeYear) {
                $assignmentSubjectIds = TeachingAssignment::where('classroom_id', $classroomId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('is_active', true)
                    ->pluck('subject_id')
                    ->unique();
            }

            $allSubjectIds = $scheduleSubjectIds->merge($assignmentSubjectIds)->unique();
        } else {
            // Regular teacher: only subjects they teach
            $scheduleSubjectIds = Schedule::where('teacher_id', $teacher->id)
                ->where('classroom_id', $classroomId)
                ->pluck('subject_id')
                ->unique();

            $activeYear = $this->getActiveYear();
            $assignmentSubjectIds = collect();
            if ($activeYear) {
                $assignmentSubjectIds = TeachingAssignment::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $classroomId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('is_active', true)
                    ->pluck('subject_id')
                    ->unique();
            }

            $allSubjectIds = $scheduleSubjectIds->merge($assignmentSubjectIds)->unique();
        }

        if ($allSubjectIds->isEmpty()) {
            $classroom = Classroom::find($classroomId);
            if ($classroom) {
                // Cobakan fallback 1: Mapel kompetensi guru tersebut
                $competentSubjects = $teacher->competentSubjects()->get();
                if ($competentSubjects->isNotEmpty()) {
                    return $competentSubjects;
                }

                // Fallback 2: Semua mapel di sekolah kelas tersebut
                return Subject::where('school_id', $classroom->school_id)->orderBy('subject_name')->get();
            }
        }

        return Subject::whereIn('id', $allSubjectIds)->orderBy('subject_name')->get();
    }

    /**
     * Bulk input form - spreadsheet-like interface
     */
    public function inputForm(Request $request)
    {
        $teacher = $this->getTeacher();
        $teacher->load('school');
        $activeYear = $this->getActiveYear();
        $activeSemester = Semester::where('is_active', true)->first();

        $semesters = Semester::with('academicYear')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderByDesc('id')
            ->get();

        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear);

        $selectedClassroomId = $request->get('classroom_id');
        $selectedSubjectId = $request->get('subject_id');
        $selectedGradeType = $request->get('grade_type', 'tugas');
        $selectedSemesterId = $request->get('semester_id');
        if (!$selectedSemesterId) {
            $selectedSemesterId = ($activeSemester && $semesters->contains('id', $activeSemester->id))
                ? $activeSemester->id
                : ($semesters->first()?->id ?? null);
        }

        $subjects = collect();
        $students = collect();
        $existingGrades = collect();
        $lmsGrades = collect();
        $gradeWeight = null;
        $isHomeroom = false;
        
        $existingComponents = collect();
        $selectedComponent = $request->get('component_name');

        if ($selectedClassroomId) {
            $subjects = $this->getTeacherSubjects($teacher, $selectedClassroomId);
            $isHomeroom = $this->isHomeroomTeacher($teacher, $selectedClassroomId);

            if ($selectedSubjectId) {
                // Get students in this classroom
                $students = Student::whereHas('studentClasses', function ($q) use ($selectedClassroomId, $activeYear) {
                    $q->where('classroom_id', $selectedClassroomId)
                      ->whereIn('status', ['aktif', 'enrolled', 'active']);
                    if ($activeYear) {
                        $q->where('academic_year_id', $activeYear->id);
                    }
                })->orderBy('full_name')->get();

                // Get existing components (manual only notes) for select dropdown
                if ($selectedSemesterId) {
                    $existingComponents = Grade::where('subject_id', $selectedSubjectId)
                        ->where('semester_id', $selectedSemesterId)
                        ->where('grade_type', $selectedGradeType)
                        ->whereNull('lms_source_type')
                        ->whereIn('student_id', $students->pluck('id'))
                        ->pluck('notes')
                        ->unique()
                        ->filter()
                        ->values();

                    if (empty($selectedComponent) && $request->get('component_select') !== '__new__') {
                        if ($existingComponents->isNotEmpty()) {
                            $selectedComponent = $existingComponents->first();
                        } else {
                            $selectedComponent = match ($selectedGradeType) {
                                'tugas' => 'Tugas 1',
                                'uts' => 'PTS',
                                'uas' => 'PAS',
                                'sikap' => 'Sikap 1',
                                default => 'Nilai 1',
                            };
                        }
                    }

                    $isDefaultComponent = false;
                    $defaultComponent = match ($selectedGradeType) {
                        'tugas' => 'Tugas 1',
                        'uts' => 'PTS',
                        'uas' => 'PAS',
                        'sikap' => 'Sikap 1',
                        default => 'Nilai 1',
                    };
                    if ($selectedComponent === $defaultComponent) {
                        $isDefaultComponent = true;
                    }

                    if (empty($selectedComponent)) {
                        $existingGrades = collect();
                    } else {
                        $existingGrades = Grade::where('subject_id', $selectedSubjectId)
                            ->where('semester_id', $selectedSemesterId)
                            ->where('grade_type', $selectedGradeType)
                            ->whereNull('lms_source_type')
                            ->whereIn('student_id', $students->pluck('id'))
                            ->where(function ($q) use ($selectedComponent, $isDefaultComponent) {
                                if ($isDefaultComponent) {
                                    $q->where('notes', $selectedComponent)
                                      ->orWhereNull('notes')
                                      ->orWhere('notes', '');
                                } else {
                                    $q->where('notes', $selectedComponent);
                                }
                            })
                            ->get()
                            ->keyBy('student_id');
                    }

                    // Get LMS grades separately (read-only display)
                    $lmsGrades = Grade::where('subject_id', $selectedSubjectId)
                        ->where('semester_id', $selectedSemesterId)
                        ->where('grade_type', $selectedGradeType)
                        ->whereNotNull('lms_source_type')
                        ->whereIn('student_id', $students->pluck('id'))
                        ->get()
                        ->groupBy('student_id');
                }

                // Get school weight config
                $classroom = Classroom::find($selectedClassroomId);
                if ($classroom) {
                    $gradeWeight = GradeWeight::getForSchool($classroom->school_id);
                }
            }
        }



        return view('guru.nilai-input', compact(
            'teacher', 'classrooms', 'subjects', 'students', 'existingGrades',
            'lmsGrades', 'selectedClassroomId', 'selectedSubjectId', 'selectedGradeType',
            'selectedSemesterId', 'semesters', 'gradeWeight', 'isHomeroom',
            'existingComponents', 'selectedComponent'
        ));
    }

    public function storeBulk(Request $request)
    {
        $teacher = $this->getTeacher();

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade_type' => 'required|in:tugas,uts,uas,sikap',
            'semester_id' => 'required|exists:semesters,id',
            'component_name' => 'required|string|max:255',
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
        ]);

        // Verify teacher has access to this classroom/subject
        // (includes homeroom teacher access to all subjects)
        $subjects = $this->getTeacherSubjects($teacher, $request->classroom_id);
        if (!$subjects->contains('id', $request->subject_id)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mata pelajaran ini di kelas tersebut.');
        }

        if ($this->isGradeLocked($request->classroom_id, $request->semester_id)) {
            return back()->with('error', 'Nilai mata pelajaran untuk kelas dan semester ini telah dikunci karena rapor sudah difinalisasi atau dipublikasikan.');
        }

        $commonData = [
            'subject_id' => $request->subject_id,
            'teacher_id' => $teacher->id,
            'semester_id' => $request->semester_id,
            'grade_type' => $request->grade_type,
            'notes' => $request->component_name,
        ];

        $result = $this->gradeService->bulkCreateGrades($request->scores, $commonData);

        $message = "Berhasil: {$result['created']} nilai baru";
        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} nilai diperbarui";
        }
        if ($result['failed'] > 0) {
            $message .= ", {$result['failed']} gagal";
        }

        return redirect()->route('guru.nilai.input', [
            'classroom_id' => $request->classroom_id,
            'subject_id' => $request->subject_id,
            'grade_type' => $request->grade_type,
            'semester_id' => $request->semester_id,
            'component_name' => $request->component_name,
        ])->with('success', $message);
    }

    /**
     * View teacher's grade summary with weighted calculations
     */
    public function summary(Request $request)
    {
        $teacher = $this->getTeacher();
        $teacher->load('school');
        $activeYear = $this->getActiveYear();
        $activeSemester = Semester::where('is_active', true)->first();

        $classrooms = $this->getTeacherClassrooms($teacher, $activeYear);
        $semesters = Semester::with('academicYear')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderByDesc('id')
            ->get();

        $selectedClassroomId = $request->get('classroom_id');
        $selectedSubjectId = $request->get('subject_id');
        $selectedSemesterId = $request->get('semester_id');
        if (!$selectedSemesterId) {
            $selectedSemesterId = ($activeSemester && $semesters->contains('id', $activeSemester->id))
                ? $activeSemester->id
                : ($semesters->first()?->id ?? null);
        }

        $subjects = collect();
        $studentSummary = collect();
        $gradeWeight = null;

        if ($selectedClassroomId) {
            $subjects = $this->getTeacherSubjects($teacher, $selectedClassroomId);

            if ($selectedSubjectId && $selectedSemesterId) {
                $classroom = Classroom::find($selectedClassroomId);
                $schoolId = $classroom?->school_id;
                $gradeWeight = $schoolId ? GradeWeight::getForSchool($schoolId) : null;

                // Get students
                $students = Student::whereHas('studentClasses', function ($q) use ($selectedClassroomId, $activeYear) {
                    $q->where('classroom_id', $selectedClassroomId)
                      ->whereIn('status', ['aktif', 'enrolled', 'active']);
                    if ($activeYear) {
                        $q->where('academic_year_id', $activeYear->id);
                    }
                })->orderBy('full_name')->get();

                // Calculate weighted scores for each student
                foreach ($students as $student) {
                    $result = $this->gradeService->calculateFinalGrade(
                        $student->id, $selectedSubjectId, $selectedSemesterId, $schoolId
                    );

                    $studentSummary->push([
                        'student' => $student,
                        'grades' => $result,
                    ]);
                }
            }
        }

        return view('guru.nilai-summary', compact(
            'teacher', 'classrooms', 'subjects', 'semesters',
            'selectedClassroomId', 'selectedSubjectId', 'selectedSemesterId',
            'studentSummary', 'gradeWeight'
        ));
    }

    /**
     * Update a single grade (inline edit)
     */
    public function update(Request $request, Grade $grade)
    {
        $teacher = $this->getTeacher();

        // Only the teacher who created the grade (or homeroom teacher, or subject teacher) can edit
        if (!$this->canManageGrade($teacher, $grade)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengedit nilai ini.');
        }

        $activeYearId = \App\Models\Semester::find($grade->semester_id)?->academic_year_id;
        $classroomId = \App\Models\StudentClass::where('student_id', $grade->student_id)
            ->when($activeYearId, fn($q) => $q->where('academic_year_id', $activeYearId))
            ->whereIn('status', ['aktif', 'enrolled', 'active'])
            ->value('classroom_id');

        if ($classroomId && $this->isGradeLocked($classroomId, $grade->semester_id)) {
            return back()->with('error', 'Nilai mata pelajaran ini telah dikunci karena rapor sudah difinalisasi atau dipublikasikan.');
        }

        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:255',
            'is_remedial' => 'nullable|boolean',
        ]);

        $notes = $request->notes;
        if ($grade->isFromLms()) {
            $notes = $notes ? $notes . ' (Diubah manual dari LMS)' : 'Diubah manual dari LMS';
        }

        $grade->update([
            'score' => $request->score,
            'notes' => $notes,
            'is_remedial' => $request->boolean('is_remedial'),
        ]);

        return back()->with('success', "Nilai {$grade->student->full_name} berhasil diperbarui menjadi {$request->score}.");
    }

    /**
     * Delete a single grade
     */
    public function destroy(Grade $grade)
    {
        $teacher = $this->getTeacher();

        if (!$this->canManageGrade($teacher, $grade)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus nilai ini.');
        }

        $activeYearId = \App\Models\Semester::find($grade->semester_id)?->academic_year_id;
        $classroomId = \App\Models\StudentClass::where('student_id', $grade->student_id)
            ->when($activeYearId, fn($q) => $q->where('academic_year_id', $activeYearId))
            ->whereIn('status', ['aktif', 'enrolled', 'active'])
            ->value('classroom_id');

        if ($classroomId && $this->isGradeLocked($classroomId, $grade->semester_id)) {
            return back()->with('error', 'Nilai mata pelajaran ini telah dikunci karena rapor sudah difinalisasi atau dipublikasikan.');
        }

        $name = $grade->student->full_name ?? 'Siswa';
        $type = $grade->getGradeTypeLabel();
        $grade->update(['score' => 0]);

        return back()->with('success', "Nilai {$type} milik {$name} berhasil direset menjadi 0.");
    }

    /**
     * Check if teacher can manage (edit/delete) a grade
     */
    private function canManageGrade(Teacher $teacher, Grade $grade): bool
    {
        // 1. Teacher who created the grade
        if ($grade->teacher_id == $teacher->id) {
            return true;
        }

        // Get student classrooms
        $studentClassroomIds = StudentClass::where('student_id', $grade->student_id)
            ->whereIn('status', ['aktif', 'enrolled', 'active'])
            ->pluck('classroom_id');

        // 2. Homeroom teacher of the student's class can also manage
        $isHomeroom = Classroom::whereIn('id', $studentClassroomIds)
            ->where('homeroom_teacher_id', $teacher->id)
            ->exists();
        if ($isHomeroom) {
            return true;
        }

        // 3. Teacher who teaches this subject in the student's classroom
        $teachesSubject = Schedule::whereIn('classroom_id', $studentClassroomIds)
            ->where('subject_id', $grade->subject_id)
            ->where('teacher_id', $teacher->id)
            ->exists() ||
            TeachingAssignment::whereIn('classroom_id', $studentClassroomIds)
            ->where('subject_id', $grade->subject_id)
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->exists();

        if ($teachesSubject) {
            return true;
        }

        return false;
    }

    /**
     * Check if grades are locked for editing
     */
    private function isGradeLocked(int $classroomId, int $semesterId): bool
    {
        if (\App\Models\Setting::getValue('guru_can_edit_grades', false)) {
            return false;
        }

        return \App\Models\ReportCard::where('classroom_id', $classroomId)
            ->where('semester_id', $semesterId)
            ->whereIn('status', ['finalized', 'published'])
            ->exists();
    }
}
