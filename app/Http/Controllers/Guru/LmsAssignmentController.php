<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsAssignmentRequest;
use App\Http\Requests\Lms\UpdateLmsAssignmentRequest;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentGroup;
use App\Models\LmsCourse;
use App\Models\LmsSubmission;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsAssignmentController extends Controller
{
    use HasMultiSchool;

    private function getTeacher(): ?Teacher
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    private function authorizeAccess(LmsCourse $course, Teacher $teacher): bool
    {
        return $course->teacher_id === $teacher->id;
    }

    /**
     * Show create assignment form
     */
    public function create(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $modules = $course->modules()->orderBy('sequence')->get();

        return view('guru.lms.assignment-create', compact('teacher', 'course', 'modules'));
    }

    /**
     * Store new assignment
     */
    public function store(StoreLmsAssignmentRequest $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/assignments', 'public');
        }

        $assignment = $course->assignments()->create([
            'module_id' => $request->module_id,
            'title' => $request->title,
            'description' => $request->description,
            'assignment_type' => $request->assignment_type ?? 'file_text',
            'is_group_assignment' => $request->boolean('is_group_assignment'),
            'deadline' => $request->due_date,
            'max_score' => $request->max_score,
            'file_path' => $filePath,
            'is_published' => true,
            'allow_resubmit' => $request->boolean('allow_resubmit'),
            'max_resubmissions' => $request->max_resubmissions ?? 1,
        ]);

        // Reputation Hook for Teacher (+30 Points per Tugas)
        \App\Models\ReputationLog::log(
            \Auth::id(),
            30,
            'lms_content',
            "Membuat tugas LMS baru: " . ($assignment->title ?? 'Tugas'),
            $assignment
        );

        // Send WhatsApp notification to enrolled students
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendLmsNotification($course, 'lms.assignment.published', [
                'title' => $assignment->title,
                'due_date' => $assignment->deadline ? $assignment->deadline->format('d M Y H:i') : '-',
            ]);
        } catch (\Exception $e) {
            \Log::error('LMS assignment notification failed: ' . $e->getMessage());
        }

        return redirect()->route('guru.lms.assignments.show', $assignment->id)
            ->with('success', 'Tugas berhasil dibuat.' . ($assignment->isGroupAssignment() ? ' Silakan atur pembagian kelompok di bawah.' : ''));
    }

    /**
     * Show assignment detail with submissions, filtered by classroom if provided
     */
    /**
     * Helper to get all students associated with the course and classroom(s)
     */
    private function getEnrolledStudentsForCourse(LmsCourse $course, ?int $selectedClassroomId = null)
    {
        $teacher = $this->getTeacher();

        // 1. Ambil semua rombel yang terhubung ke course
        $classrooms = \App\Models\LmsClass::where('course_id', $course->id)
            ->with('classroom')
            ->get()
            ->pluck('classroom')
            ->filter()
            ->values();

        if ($classrooms->isEmpty() && $course->classroom_id) {
            $c = \App\Models\Classroom::find($course->classroom_id);
            if ($c) {
                $classrooms = collect([$c]);
            }
        }

        // 2. Periksa apakah rombel yang ada saat ini memiliki data siswa di student_classes
        $hasStudentsInCourseClassrooms = false;
        foreach ($classrooms as $cr) {
            if (\App\Models\StudentClass::where('classroom_id', $cr->id)->exists()) {
                $hasStudentsInCourseClassrooms = true;
                break;
            }
        }

        // 3. Jika rombel kursus kosong siswa (misal terhubung ke rombel dummy/lama),
        // sinkronkan otomatis ke rombel dari Penugasan Mengajar (TeachingAssignment) guru untuk mapel ini
        if (!$hasStudentsInCourseClassrooms && $teacher && $course->subject_id) {
            $taClassrooms = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('subject_id', $course->subject_id)
                ->with('classroom')
                ->get()
                ->pluck('classroom')
                ->filter()
                ->filter(fn($c) => \App\Models\StudentClass::where('classroom_id', $c->id)->exists())
                ->values();

            if ($taClassrooms->isNotEmpty()) {
                $classrooms = $taClassrooms;
                foreach ($taClassrooms as $tac) {
                    \App\Models\LmsClass::firstOrCreate([
                        'course_id' => $course->id,
                        'classroom_id' => $tac->id,
                    ], [
                        'school_id' => $tac->school_id,
                        'status' => 'active',
                    ]);
                }
                $validSchoolId = $taClassrooms->first()->school_id;
                $course->update([
                    'classroom_id' => $taClassrooms->first()->id,
                    'school_id' => $validSchoolId,
                ]);
            }
        }

        // 4. Sinkronisasi enrollments kursus
        try {
            app(\App\Services\LmsEnrollmentService::class)->syncCourseEnrollments($course);
        } catch (\Throwable $e) {
            \Log::warning('LMS syncCourseEnrollments warning: ' . $e->getMessage());
        }

        // 5. Tentukan target ID rombel (spesifik rombel terpilih atau seluruh rombel kursus ini)
        $allClassroomIds = $classrooms->pluck('id')->filter()->values()->toArray();
        if ($course->classroom_id && !in_array($course->classroom_id, $allClassroomIds)) {
            $allClassroomIds[] = $course->classroom_id;
        }

        $targetClassroomIds = $selectedClassroomId ? [$selectedClassroomId] : $allClassroomIds;

        // 6. Ambil siswa via StudentClass dari rombel target secara tepat
        $students = \App\Models\StudentClass::whereIn('classroom_id', $targetClassroomIds)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereIn('status', ['aktif', 'active', 'Aktif', '']);
            })
            ->with(['student.user'])
            ->get()
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->values();

        // 7. Jika dari StudentClass kosong, ambil via LmsEnrollment kursus ini
        if ($students->isEmpty()) {
            $students = \App\Models\LmsEnrollment::whereHas('lmsClass', function ($q) use ($course, $selectedClassroomId) {
                $q->where('course_id', $course->id);
                if ($selectedClassroomId) {
                    $q->where('classroom_id', $selectedClassroomId);
                }
            })->with('student.user')->get()->pluck('student')->filter()->unique('id')->values();
        }

        $finalStudents = $students
            ->filter(fn($s) => $s && $s->id)
            ->sortBy(fn($s) => strtolower($s->user->name ?? $s->full_name ?? ''))
            ->values();

        // 8. Daftarkan siswa ke LmsEnrollment jika belum terdaftar
        if ($finalStudents->isNotEmpty()) {
            try {
                $firstLmsClass = \App\Models\LmsClass::where('course_id', $course->id)->first();
                if ($firstLmsClass) {
                    foreach ($finalStudents as $std) {
                        if (!$std || !$std->id) continue;
                        \App\Models\LmsEnrollment::firstOrCreate([
                            'lms_class_id' => $firstLmsClass->id,
                            'student_id' => $std->id,
                        ], [
                            'status' => 'enrolled',
                            'enrolled_at' => now(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Auto enroll student warning: ' . $e->getMessage());
            }
        }

        return $finalStudents;
    }

    /**
     * Show assignment detail with submissions, filtered by classroom if provided
     */
    public function show(Request $request, LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        // Ambil semua rombel yang terhubung ke course ini
        $classrooms = \App\Models\LmsClass::where('course_id', $course->id)
            ->with('classroom')
            ->get()
            ->pluck('classroom')
            ->filter()
            ->values();

        if ($classrooms->isEmpty() && $course->classroom) {
            $classrooms = collect([$course->classroom]);
        }

        // Filter per rombel jika dipilih
        $selectedClassroomId = $request->query('classroom_id');
        $selectedClassroom = $selectedClassroomId
            ? $classrooms->firstWhere('id', $selectedClassroomId)
            : null;

        // Ambil semua siswa terdaftar untuk kursus ini
        $allEnrolledStudents = $this->getEnrolledStudentsForCourse($course, $selectedClassroomId ? (int)$selectedClassroomId : null);
        $enrolledStudentIds = $allEnrolledStudents->pluck('id')->toArray();

        // Load submissions, filter by classroom students jika ada filter
        $submissionsQuery = $assignment->submissions()
            ->with(['student.user', 'group.members.user', 'group.leader.user'])
            ->orderByDesc('submitted_at');

        if ($selectedClassroomId && $selectedClassroom) {
            $submissionsQuery->where(function ($q) use ($enrolledStudentIds) {
                $q->whereIn('student_id', $enrolledStudentIds)
                  ->orWhereHas('group.members', function ($gq) use ($enrolledStudentIds) {
                      $gq->whereIn('students.id', $enrolledStudentIds);
                  });
            });
        }

        $assignment->setRelation('submissions', $submissionsQuery->get());

        // Load groups if group assignment
        if ($assignment->isGroupAssignment()) {
            $assignment->load(['groups.leader.user', 'groups.members.user', 'groups.submission.student.user']);
        }

        $totalSubmissions = $assignment->submissions->where('status', '!=', 'draft')->count();
        $gradedCount = $assignment->submissions->where('status', 'graded')->count();

        return view('guru.lms.assignment-show', compact(
            'teacher', 'course', 'assignment',
            'totalSubmissions', 'gradedCount',
            'classrooms', 'selectedClassroomId', 'selectedClassroom',
            'allEnrolledStudents'
        ));
    }

    public function edit(LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');
        $modules = $course->modules()->where('is_active', true)->orderBy('sequence')->get();

        return view('guru.lms.assignment-edit', compact('teacher', 'course', 'assignment', 'modules'));
    }

    /**
     * Update assignment
     */
    public function update(UpdateLmsAssignmentRequest $request, LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        // Handle file replacement
        $updateData = [
            'module_id' => $request->has('module_id') ? $request->module_id : $assignment->module_id,
            'title' => $request->title,
            'description' => $request->description,
            'assignment_type' => $request->assignment_type ?? $assignment->assignment_type,
            'is_group_assignment' => $request->boolean('is_group_assignment'),
            'deadline' => $request->due_date,
            'max_score' => $request->max_score,
            'allow_resubmit' => $request->boolean('allow_resubmit'),
            'max_resubmissions' => $request->max_resubmissions ?? $assignment->max_resubmissions,
        ];

        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($assignment->file_path && \Storage::disk('public')->exists($assignment->file_path)) {
                \Storage::disk('public')->delete($assignment->file_path);
            }
            $updateData['file_path'] = $request->file('file')->store('lms/assignments', 'public');
        }

        $assignment->update($updateData);

        return redirect()->route('guru.lms.assignments.show', $assignment->id)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Store new group for group assignment
     */
    public function storeGroup(Request $request, LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'leader_id' => 'required|exists:students,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:students,id',
        ]);

        $group = $assignment->groups()->create([
            'name' => $request->name,
            'leader_id' => $request->leader_id,
        ]);

        // Sync members (including leader)
        $memberIds = collect($request->member_ids ?? [])->push($request->leader_id)->unique()->filter()->values()->toArray();
        $group->members()->sync($memberIds);

        return redirect()->route('guru.lms.assignments.show', $assignment->id)
            ->with('success', "Kelompok '{$group->name}' berhasil ditambahkan.");
    }

    /**
     * Delete a group from assignment
     */
    public function deleteGroup(LmsAssignment $assignment, LmsAssignmentGroup $group)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher) || $group->assignment_id !== $assignment->id) {
            abort(403);
        }

        $groupName = $group->name;
        $group->delete();

        return redirect()->route('guru.lms.assignments.show', $assignment->id)
            ->with('success', "Kelompok '{$groupName}' berhasil dihapus.");
    }

    /**
     * Auto generate groups for assignment from enrolled students
     */
    public function autoGenerateGroups(Request $request, LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'group_count' => 'required|integer|min:2|max:30',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $classroomId = $request->classroom_id;

        $students = $this->getEnrolledStudentsForCourse($course, $classroomId ? (int)$classroomId : null)->shuffle();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang terdaftar untuk dibagi kelompok.');
        }

        $numGroups = min((int)$request->group_count, $students->count());
        $chunks = $students->split($numGroups);

        $startIdx = $assignment->groups()->count();
        foreach ($chunks as $idx => $chunk) {
            $groupNum = $startIdx + $idx + 1;
            $leader = $chunk->first();
            $group = $assignment->groups()->create([
                'name' => 'Kelompok ' . $groupNum,
                'leader_id' => $leader->id,
            ]);
            $group->members()->sync($chunk->pluck('id')->toArray());
        }

        return redirect()->route('guru.lms.assignments.show', $assignment->id)
            ->with('success', "Berhasil membuat {$numGroups} kelompok secara otomatis.");
    }

    /**
     * Delete assignment
     */
    public function destroy(LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $courseId = $course->id;
        $assignment->delete();

        return redirect()->route('guru.lms.show', $courseId)
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Grade a submission or request revision
     */
    public function grade(Request $request, LmsSubmission $submission)
    {
        $teacher = $this->getTeacher();
        $course = $submission->assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $request->validate([
            'score' => 'nullable|numeric|min:0|max:' . $submission->assignment->max_score,
            'feedback' => 'nullable|string',
            'action_type' => 'nullable|string',
        ]);

        if ($request->action_type === 'request_revision') {
            $submission->update([
                'status' => 'revision_requested',
                'feedback' => $request->feedback,
                'revision_notes' => $request->feedback,
                'teacher_notes' => $request->feedback,
                'graded_at' => now(),
                'graded_by' => Auth::id(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Permintaan revisi berhasil dikirim ke siswa.', 'submission' => $submission]);
            }
            return redirect()->back()->with('success', 'Permintaan revisi berhasil dikirim ke siswa.');
        }

        $submission->update([
            'score' => $request->score ?? 0,
            'feedback' => $request->feedback,
            'teacher_notes' => $request->feedback,
            'status' => 'graded',
            'graded_at' => now(),
            'graded_by' => Auth::id(),
        ]);

        // Auto-sync submission score to grades table
        try {
            $gradeService = app(\App\Services\GradeService::class);
            $gradeService->syncSubmissionToGrade($submission);
        } catch (\Exception $e) {
            \Log::warning('LMS submission sync failed: ' . $e->getMessage());
        }

        // Reputation Hook for Teacher (+10 Points per Grading)
        try {
            \App\Models\ReputationLog::log(
                \Auth::id(),
                10,
                'grading',
                "Menilai tugas LMS: " . ($course->title ?? 'Tugas'),
                $submission
            );
        } catch (\Exception $e) {
            \Log::warning('LMS grading reputation failed: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan.', 'submission' => $submission]);
        }

        return redirect()->route('guru.lms.assignments.show', $submission->assignment_id)
            ->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Download student submission file safely with original name and correct extension
     */
    public function downloadSubmission(LmsSubmission $submission)
    {
        $teacher = $this->getTeacher();
        $course = $submission->assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403, 'Anda tidak memiliki akses ke berkas ini.');
        }

        if (!$submission->file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'Berkas jawaban siswa tidak ditemukan.');
        }

        $studentName = \Illuminate\Support\Str::slug($submission->student->full_name ?? 'Siswa');
        $assignmentTitle = \Illuminate\Support\Str::slug($submission->assignment->title ?? 'Tugas');
        $ext = pathinfo($submission->file_path, PATHINFO_EXTENSION);
        
        // If extension is missing or bin, detect real extension from MIME type
        if (empty($ext) || in_array(strtolower($ext), ['bin', 'tmp'])) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($submission->file_path);
            $mime = \Illuminate\Support\Facades\File::mimeType($fullPath);
            $mimeMap = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'application/vnd.ms-powerpoint' => 'ppt',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'application/zip' => 'zip',
                'application/x-rar-compressed' => 'rar',
            ];
            $ext = $mimeMap[$mime] ?? ($ext ?: 'docx');
        }

        $downloadFilename = "Tugas_{$assignmentTitle}_{$studentName}.{$ext}";

        return \Illuminate\Support\Facades\Storage::disk('public')->download($submission->file_path, $downloadFilename);
    }
}
