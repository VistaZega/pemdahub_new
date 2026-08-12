<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsAssignmentRequest;
use App\Http\Requests\Lms\UpdateLmsAssignmentRequest;
use App\Models\LmsAssignment;
use App\Models\LmsCourse;
use App\Models\LmsSubmission;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsAssignmentController extends Controller
{
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

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Show assignment detail with submissions
     */
    public function show(LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $assignment->load(['submissions' => fn($q) => $q->with('student.user')->orderByDesc('submitted_at')]);

        $totalSubmissions = $assignment->submissions->where('status', '!=', 'draft')->count();
        $gradedCount = $assignment->submissions->where('status', 'graded')->count();

        return view('guru.lms.assignment-show', compact('teacher', 'course', 'assignment', 'totalSubmissions', 'gradedCount'));
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
