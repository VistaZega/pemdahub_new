<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use App\Models\FinalProjectFormat;
use App\Models\School;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FinalProjectAdminController extends Controller
{
    private function isSuperAdmin(): bool
    {
        return Auth::user()->isSuperAdmin();
    }

    private function getSchoolId()
    {
        return Auth::user()->school_id;
    }

    // ==========================================
    // 1. GUIDELINES & FORMATS
    // ==========================================

    public function formatsIndex(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $query = FinalProjectFormat::with(['school', 'creator']);

        if (!$isSA) {
            $query->where('school_id', $schoolId);
        }

        $formats = $query->latest()->paginate(15);
        
        // Only SMA and SMK schools
        $schools = School::whereIn('type', ['SMA', 'SMK'])->get();

        return view('admin.final_projects.formats.index', compact('formats', 'schools', 'isSA'));
    }

    public function formatsStore(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'required|file|mimes:pdf,doc,docx,zip|max:5120', // Max 5MB
            'school_id' => $isSA ? 'required|exists:schools,id' : 'nullable',
        ]);

        $targetSchoolId = $isSA ? $validated['school_id'] : $schoolId;

        // Ensure target school is SMA or SMK
        $school = School::findOrFail($targetSchoolId);
        if (!in_array($school->type, ['SMA', 'SMK'])) {
            return redirect()->back()->with('error', 'Format hanya diperuntukkan bagi sekolah SMA atau SMK.');
        }

        $filePath = $request->file('file_path')->store('final_project_formats', 'public');

        FinalProjectFormat::create([
            'school_id' => $targetSchoolId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.final-projects.formats.index')->with('success', 'Format panduan berhasil diupload.');
    }

    public function formatsDestroy($id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $format = FinalProjectFormat::findOrFail($id);

        if (!$isSA && $format->school_id != $schoolId) {
            abort(403);
        }

        if ($format->file_path) {
            Storage::disk('public')->delete($format->file_path);
        }

        $format->delete();

        return redirect()->route('admin.final-projects.formats.index')->with('success', 'Format panduan berhasil dihapus.');
    }

    // ==========================================
    // 2. PROPOSALS & ADVISOR ASSIGNMENT
    // ==========================================

    public function proposalsIndex(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $query = FinalProject::with(['student.school', 'student.user', 'advisor.user']);
        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        if (!$isSA) {
            $query->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        } elseif ($request->filled('school_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        // Calculate summary statistics for claymorphism stat cards
        $statsBaseQuery = FinalProject::query();
        if ($activeYear) {
            $statsBaseQuery->where('academic_year_id', $activeYear->id);
        }
        if (!$isSA) {
            $statsBaseQuery->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
        }

        $stats = [
            'total' => (clone $statsBaseQuery)->count(),
            'approved' => (clone $statsBaseQuery)->whereIn('status', ['approved', 'in_progress'])->count(),
            'pending' => (clone $statsBaseQuery)->where('status', 'pending')->count(),
            'ready_for_exam' => (clone $statsBaseQuery)->whereIn('status', ['ready_for_exam', 'completed'])->count(),
        ];

        // Get teachers for advisor dropdown
        $teachersQuery = Teacher::with(['user', 'school']);
        if (!$isSA) {
            $teachersQuery->where('school_id', $schoolId);
        } else {
            $smaSmkSchoolIds = School::whereIn('type', ['SMA', 'SMK'])->pluck('id');
            $teachersQuery->whereIn('school_id', $smaSmkSchoolIds);
        }
        $teachers = $teachersQuery->get();

        $schools = School::whereIn('type', ['SMA', 'SMK'])->get();

        return view('admin.final_projects.proposals.index', compact('projects', 'teachers', 'schools', 'stats', 'isSA'));
    }

    public function proposalsCreate(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return redirect()->route('admin.final-projects.proposals.index')->with('error', 'Tidak ada Tahun Pelajaran aktif.');
        }

        $schools = School::whereIn('type', ['SMA', 'SMK'])->get();

        // Determine selected school ID
        $selectedSchoolId = $schoolId;
        if ($isSA) {
            if ($request->filled('school_id')) {
                $selectedSchoolId = $request->school_id;
            } elseif ($request->filled('classroom_id')) {
                $cls = Classroom::find($request->classroom_id);
                if ($cls) {
                    $selectedSchoolId = $cls->school_id;
                }
            } else {
                $selectedSchoolId = $schools->first()?->id;
            }
        }

        // Get class 12 classrooms from the selected school
        $classrooms = Classroom::where('academic_year_id', $activeYear->id)
            ->where('grade_level', 12)
            ->when($selectedSchoolId, fn($q) => $q->where('school_id', $selectedSchoolId))
            ->orderBy('class_name')
            ->get();

        // Selected classroom for initial filter if requested
        $selectedClassroomId = $request->input('classroom_id');

        // Fetch all available grade 12 students in the selected school who don't have a final project yet
        $studentsQuery = Student::with(['currentClassroom', 'classroom', 'school'])
            ->whereDoesntHave('finalProjectMemberships')
            ->whereHas('studentClasses', function($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id)
                  ->where('status', 'aktif')
                  ->whereHas('classroom', function($cq) {
                      $cq->where('grade_level', 12);
                  });
            })
            ->orderBy('full_name');

        if ($selectedSchoolId) {
            $studentsQuery->where('school_id', $selectedSchoolId);
        }

        $students = $studentsQuery->get();

        // Format students for Alpine.js dynamic filtering
        $formattedStudents = $students->map(function($st) {
            $currentClass = $st->currentClassroom->first() ?? $st->classroom;
            return [
                'id' => $st->id,
                'full_name' => $st->full_name,
                'nisn' => $st->nisn ?? ($st->nis ?? '-'),
                'classroom_id' => $currentClass ? $currentClass->id : null,
                'classroom_name' => $currentClass ? $currentClass->class_name : 'Tanpa Kelas',
            ];
        })->values();

        // Get teachers for advisor dropdown
        $teachersQuery = Teacher::with(['user', 'school'])->where('is_active', true);
        if ($selectedSchoolId) {
            $teachersQuery->where('school_id', $selectedSchoolId);
        }
        $teachers = $teachersQuery->orderBy('full_name')->get();

        return view('admin.final_projects.proposals.create', compact(
            'classrooms', 'selectedClassroomId', 'students', 'formattedStudents', 'teachers', 'schools', 'selectedSchoolId', 'isSA'
        ));
    }

    public function proposalsStore(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $validated = $request->validate([
            'classroom_id' => 'nullable|exists:classrooms,id',
            'school_id' => 'nullable|exists:schools,id',
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'advisor_id' => 'required|exists:teachers,id',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:students,id',
            'leader_id' => 'nullable|exists:students,id',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Pelajaran aktif.')->withInput();
        }

        // Leader is specified or default to the first member selected
        $leaderId = $validated['leader_id'] ?? $validated['member_ids'][0];
        if (!in_array($leaderId, $validated['member_ids'])) {
            $leaderId = $validated['member_ids'][0];
        }

        $leaderStudent = Student::with('school')->findOrFail($leaderId);

        if (!$isSA && $leaderStudent->school_id != $schoolId) {
            abort(403);
        }

        $type = $leaderStudent->school->type === 'SMA' ? 'penelitian_ilmiah' : 'project_akhir';

        DB::beginTransaction();
        try {
            $project = FinalProject::create([
                'student_id' => $leaderId,
                'academic_year_id' => $activeYear->id,
                'type' => $type,
                'title' => $validated['title'],
                'abstract' => $validated['abstract'] ?? 'Deskripsi ditentukan oleh Panitia',
                'advisor_id' => $validated['advisor_id'],
                'status' => 'approved', // Directly approved by Panitia
            ]);

            foreach ($validated['member_ids'] as $memberId) {
                $student = Student::find($memberId);
                $existingProject = $student ? $student->currentFinalProject() : null;
                if ($student && (!$existingProject || $existingProject->id === $project->id)) {
                    \App\Models\FinalProjectMember::create([
                        'final_project_id' => $project->id,
                        'student_id' => $memberId,
                        'role' => ($memberId == $leaderId) ? 'leader' : 'member'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.final-projects.proposals.index')->with('success', 'Kelompok (Lintas Kelas) berhasil dibentuk dan Judul ditetapkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membentuk kelompok: ' . $e->getMessage())->withInput();
        }
    }

    public function proposalsEdit(Request $request, $id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $project = FinalProject::with(['student.school', 'members.student.currentClassroom', 'advisor.user'])->findOrFail($id);

        if (!$isSA && $project->student->school_id != $schoolId) {
            abort(403);
        }

        $activeYear = AcademicYear::where('is_active', true)->first();

        // Current member IDs
        $currentMemberIds = $project->members->pluck('student_id')->toArray();
        if (empty($currentMemberIds)) {
            $currentMemberIds = [$project->student_id];
        }

        // Get class 12 classrooms in the leader's school
        $classrooms = Classroom::where('academic_year_id', $activeYear->id ?? $project->academic_year_id)
            ->where('grade_level', 12)
            ->where('school_id', $project->student->school_id)
            ->orderBy('class_name')
            ->get();

        // Available grade 12 students in the school (either current members OR available students)
        $availableStudents = Student::with(['currentClassroom', 'classroom'])
            ->where('school_id', $project->student->school_id)
            ->where(function($q) use ($currentMemberIds, $project) {
                $q->whereIn('students.id', $currentMemberIds)
                  ->orWhereDoesntHave('finalProjectMemberships', function($mq) use ($project) {
                      $mq->where('final_project_id', '!=', $project->id);
                  });
            })
            ->whereHas('studentClasses', function($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id ?? 1)
                  ->where('status', 'aktif')
                  ->whereHas('classroom', function($cq) {
                      $cq->where('grade_level', 12);
                  });
            })
            ->orderBy('full_name')
            ->get();

        $formattedStudents = $availableStudents->map(function($st) {
            $currentClass = $st->currentClassroom->first() ?? $st->classroom;
            return [
                'id' => $st->id,
                'full_name' => $st->full_name,
                'nisn' => $st->nisn ?? ($st->nis ?? '-'),
                'classroom_id' => $currentClass ? $currentClass->id : null,
                'classroom_name' => $currentClass ? $currentClass->class_name : 'Tanpa Kelas',
            ];
        })->values();

        // Get teachers for advisor dropdown
        $teachers = Teacher::with(['user', 'school'])
            ->where('school_id', $project->student->school_id)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        $stages = FinalProject::getStages();

        return view('admin.final_projects.proposals.edit', compact(
            'project', 'classrooms', 'availableStudents', 'formattedStudents', 'currentMemberIds', 'teachers', 'stages', 'isSA'
        ));
    }

    public function proposalsUpdate(Request $request, $id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $project = FinalProject::with('student')->findOrFail($id);

        if (!$isSA && $project->student->school_id != $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'advisor_id' => 'required|exists:teachers,id',
            'status' => 'required|in:pending,approved,in_progress,ready_for_exam,completed,rejected',
            'current_stage' => 'nullable|string|max:50',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:students,id',
            'leader_id' => 'nullable|exists:students,id',
        ]);

        // Leader is specified or default to the first member selected
        $leaderId = !empty($validated['leader_id']) ? $validated['leader_id'] : ($validated['member_ids'][0] ?? null);
        if (!$leaderId || !in_array($leaderId, $validated['member_ids'])) {
            $leaderId = $validated['member_ids'][0] ?? null;
        }

        DB::beginTransaction();
        try {
            $project->update([
                'title' => $validated['title'],
                'abstract' => $validated['abstract'] ?? 'Deskripsi ditentukan oleh Panitia',
                'advisor_id' => $validated['advisor_id'],
                'status' => $validated['status'],
                'current_stage' => $validated['current_stage'] ?? $project->current_stage,
                'student_id' => $leaderId,
            ]);

            // Sinkronisasi anggota kelompok
            // Hapus anggota lama
            \App\Models\FinalProjectMember::where('final_project_id', $project->id)->delete();

            // Masukkan anggota baru
            foreach ($validated['member_ids'] as $memberId) {
                \App\Models\FinalProjectMember::create([
                    'final_project_id' => $project->id,
                    'student_id' => $memberId,
                    'role' => ($memberId == $leaderId) ? 'leader' : 'member'
                ]);
            }

            DB::commit();
            return redirect()->route('admin.final-projects.proposals.index')->with('success', 'Data usulan project akhir & kelompok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    public function proposalsDestroy(Request $request, $id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $project = FinalProject::with(['student', 'logs'])->findOrFail($id);

        if (!$isSA && $project->student->school_id != $schoolId) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Hapus berkas log bimbingan jika ada
            foreach ($project->logs as $log) {
                if ($log->documentation_file && Storage::disk('public')->exists($log->documentation_file)) {
                    Storage::disk('public')->delete($log->documentation_file);
                }
            }

            $projectTitle = $project->title;
            $project->delete(); // Cascade akan menghapus final_project_members dan final_project_logs

            DB::commit();
            return redirect()->route('admin.final-projects.proposals.index')->with('success', "Usulan Project Akhir '{$projectTitle}' berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.final-projects.proposals.index')->with('error', 'Gagal menghapus usulan project: ' . $e->getMessage());
        }
    }

    public function proposalsAssign(Request $request, $id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $project = FinalProject::with('student')->findOrFail($id);

        if (!$isSA && $project->student->school_id != $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'advisor_id' => 'required_if:action,approve|nullable|exists:teachers,id',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        if ($validated['action'] === 'approve') {
            // Verify advisor belongs to the same school
            $advisor = Teacher::findOrFail($validated['advisor_id']);
            if ($advisor->school_id != $project->student->school_id) {
                return redirect()->back()->with('error', 'Guru pembimbing harus berasal dari sekolah yang sama dengan siswa.');
            }

            $project->update([
                'advisor_id' => $validated['advisor_id'],
                'status' => 'approved',
                'rejection_reason' => null,
            ]);

            return redirect()->route('admin.final-projects.proposals.index')->with('success', 'Judul disetujui dan Guru Pembimbing berhasil ditugaskan.');
        } else {
            $project->update([
                'advisor_id' => null,
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            return redirect()->route('admin.final-projects.proposals.index')->with('success', 'Pengajuan judul berhasil ditolak.');
        }
    }

    // ==========================================
    // 3. EXAMS & SCHEDULING
    // ==========================================

    public function examsIndex(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $query = FinalProject::with(['student.school', 'student.user', 'advisor.user', 'examiner.user', 'examiner2.user'])
            ->whereIn('status', ['ready_for_exam', 'completed']);
        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        if (!$isSA) {
            $query->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        } elseif ($request->filled('school_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        // Get teachers for examiner dropdown
        $teachersQuery = Teacher::with(['user', 'school']);
        if (!$isSA) {
            $teachersQuery->where('school_id', $schoolId);
        } else {
            $smaSmkSchoolIds = School::whereIn('type', ['SMA', 'SMK'])->pluck('id');
            $teachersQuery->whereIn('school_id', $smaSmkSchoolIds);
        }
        $teachers = $teachersQuery->get();

        $schools = School::whereIn('type', ['SMA', 'SMK'])->get();

        return view('admin.final_projects.exams.index', compact('projects', 'teachers', 'schools', 'isSA'));
    }

    public function examsSchedule(Request $request, $id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $project = FinalProject::with('student')->findOrFail($id);

        if (!$isSA && $project->student->school_id != $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'exam_date' => 'required|date',
            'exam_location' => 'required|string|max:255',
            'examiner_id' => 'required|exists:teachers,id',
            'examiner2_id' => 'nullable|exists:teachers,id',
        ]);

        // Verify examiner 1 belongs to the same school
        $examiner = Teacher::findOrFail($validated['examiner_id']);
        if ($examiner->school_id != $project->student->school_id) {
            return redirect()->back()->with('error', 'Guru penguji 1 harus berasal dari sekolah yang sama dengan siswa.');
        }

        // Cannot assign the same advisor as examiner 1
        if ($project->advisor_id == $validated['examiner_id']) {
            return redirect()->back()->with('error', 'Guru penguji 1 tidak boleh sama dengan guru pembimbing.');
        }

        // Verify examiner 2 (if assigned)
        if (isset($validated['examiner2_id']) && !empty($validated['examiner2_id'])) {
            if ($validated['examiner_id'] == $validated['examiner2_id']) {
                return redirect()->back()->with('error', 'Guru penguji 1 dan Guru penguji 2 tidak boleh sama.');
            }

            $examiner2 = Teacher::findOrFail($validated['examiner2_id']);
            if ($examiner2->school_id != $project->student->school_id) {
                return redirect()->back()->with('error', 'Guru penguji 2 harus berasal dari sekolah yang sama dengan siswa.');
            }

            if ($project->advisor_id == $validated['examiner2_id']) {
                return redirect()->back()->with('error', 'Guru penguji 2 tidak boleh sama dengan guru pembimbing.');
            }
        }

        $project->update([
            'exam_date' => $validated['exam_date'],
            'exam_location' => $validated['exam_location'],
            'examiner_id' => $validated['examiner_id'],
            'examiner2_id' => $validated['examiner2_id'] ?? null,
        ]);

        return redirect()->route('admin.final-projects.exams.index')->with('success', 'Jadwal ujian/sidang berhasil diterbitkan.');
    }
}
