<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PklPlacement;
use App\Models\PklMonitoring;
use App\Models\Dudi;
use App\Models\FinalProject;
use App\Models\FinalProjectLog;
use App\Models\FinalProjectMember;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobilePanitiaController extends Controller
{
    /**
     * Helper to verify Panitia / Admin access
     */
    protected function checkPanitiaAccess($type = 'pkl')
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($type === 'pkl') {
            return $user->isPanitiaPkl() || $user->isSuperAdmin() || $user->isAdminSekolah() || $user->isKepalaSekolah() || $user->isYayasan();
        }

        return $user->isPanitiaProyek() || $user->isSuperAdmin() || $user->isAdminSekolah() || $user->isKepalaSekolah() || $user->isYayasan();
    }

    /**
     * Dashboard Panitia PKL Mobile
     */
    public function pklIndex(Request $request)
    {
        if (!$this->checkPanitiaAccess('pkl')) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses khusus Panitia PKL.');
        }

        $activeAY = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();
        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search');
        $teacherFilter = $request->query('teacher_id');

        $query = PklPlacement::with(['student.school', 'dudi', 'teacher'])
            ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id));

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($teacherFilter) {
            $query->where('teacher_id', $teacherFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', fn($sq) => $sq->where('full_name', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%"))
                  ->orWhereHas('dudi', fn($dq) => $dq->where('name', 'like', "%{$search}%"))
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $placements = $query->latest('id')->paginate(15)->withQueryString();

        // Statistics
        $totalPlacements = PklPlacement::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->count();
        $activePlacements = PklPlacement::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->where('status', 'active')->count();
        $completedPlacements = PklPlacement::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->where('status', 'completed')->count();
        $unassignedTeacher = PklPlacement::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->whereNull('teacher_id')->count();

        $teachers = Teacher::orderBy('full_name')->get();
        $dudis = Dudi::orderBy('name')->get();

        return view('mobile.panitia.pkl', compact(
            'placements',
            'totalPlacements',
            'activePlacements',
            'completedPlacements',
            'unassignedTeacher',
            'teachers',
            'dudis',
            'statusFilter',
            'search',
            'teacherFilter',
            'activeAY'
        ));
    }

    /**
     * Detail Penempatan PKL Siswa
     */
    public function pklPlacementShow(Request $request, $id)
    {
        if (!$this->checkPanitiaAccess('pkl')) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses khusus Panitia PKL.');
        }

        $placement = PklPlacement::with(['student.school', 'student.user', 'dudi', 'teacher.user'])->findOrFail($id);
        $teachers = Teacher::orderBy('full_name')->get();
        $dudis = Dudi::orderBy('name')->get();
        $monitorings = PklMonitoring::where('dudi_id', $placement->dudi_id ?? 0)->latest('visit_date')->get();

        return view('mobile.panitia.pkl_detail', compact('placement', 'teachers', 'dudis', 'monitorings'));
    }

    /**
     * Assign Guru Pembimbing PKL oleh Panitia
     */
    public function assignPembimbingPkl(Request $request, $id)
    {
        if (!$this->checkPanitiaAccess('pkl')) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $placement = PklPlacement::findOrFail($id);
        $placement->teacher_id = $validated['teacher_id'];
        $placement->save();

        return back()->with('success', 'Guru Pembimbing PKL berhasil diperbarui!');
    }

    /**
     * Update Status Penempatan PKL oleh Panitia
     */
    public function updatePlacementStatus(Request $request, $id)
    {
        if (!$this->checkPanitiaAccess('pkl')) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,active,completed,canceled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $placement = PklPlacement::findOrFail($id);
        $placement->status = $validated['status'];
        if (isset($validated['start_date'])) $placement->start_date = $validated['start_date'];
        if (isset($validated['end_date'])) $placement->end_date = $validated['end_date'];
        $placement->save();

        return back()->with('success', 'Status penempatan PKL berhasil diperbarui!');
    }

    /**
     * Dashboard Panitia Project Akhir / Penelitian Akhir (TA) Mobile
     */
    public function finalProjectIndex(Request $request)
    {
        if (!$this->checkPanitiaAccess('final_project')) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses khusus Panitia Project/Penelitian Akhir.');
        }

        $activeAY = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();
        $statusFilter = $request->query('status', 'all');
        $stageFilter = $request->query('stage', 'all');
        $search = $request->query('search');

        $query = FinalProject::with(['student.school', 'advisor', 'examiner', 'members.student'])
            ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id));

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($stageFilter !== 'all') {
            $query->where('current_stage', $stageFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('full_name', 'like', "%{$search}%"))
                  ->orWhereHas('members.student', fn($mq) => $mq->where('full_name', 'like', "%{$search}%"));
            });
        }

        $projects = $query->latest('id')->paginate(15)->withQueryString();

        // Statistics
        $totalProjects = FinalProject::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->count();
        $submittedProjects = FinalProject::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->where('status', 'submitted')->count();
        $approvedProjects = FinalProject::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->where('status', 'approved')->count();
        $sidangProjects = FinalProject::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->where('current_stage', 'sidang')->count();
        $completedProjects = FinalProject::when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))->where('current_stage', 'completed')->count();

        $teachers = Teacher::orderBy('full_name')->get();

        return view('mobile.panitia.final_project', compact(
            'projects',
            'totalProjects',
            'submittedProjects',
            'approvedProjects',
            'sidangProjects',
            'completedProjects',
            'teachers',
            'statusFilter',
            'stageFilter',
            'search',
            'activeAY'
        ));
    }

    /**
     * Detail Project / Penelitian Akhir untuk Panitia
     */
    public function finalProjectShow(Request $request, $id)
    {
        if (!$this->checkPanitiaAccess('final_project')) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses khusus Panitia Project/Penelitian Akhir.');
        }

        $project = FinalProject::with(['student.school', 'advisor', 'examiner', 'examiner2', 'members.student', 'logs.reviewedByTeacher'])->findOrFail($id);
        $teachers = Teacher::orderBy('full_name')->get();
        $stages = FinalProject::getStages();

        return view('mobile.panitia.final_project_detail', compact('project', 'teachers', 'stages'));
    }

    /**
     * Review Proposal oleh Panitia (Approve / Reject / Revision)
     */
    public function approveProposal(Request $request, $id)
    {
        if (!$this->checkPanitiaAccess('final_project')) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,revision',
            'advisor_id' => 'nullable|exists:teachers,id',
            'rejection_reason' => 'nullable|string',
        ]);

        $project = FinalProject::findOrFail($id);
        $project->status = $validated['status'];

        if ($validated['status'] === 'approved') {
            $project->current_stage = FinalProject::STAGE_BAB1;
            if (!empty($validated['advisor_id'])) {
                $project->advisor_id = $validated['advisor_id'];
            }
            $project->rejection_reason = null;
        } elseif ($validated['status'] === 'rejected' || $validated['status'] === 'revision') {
            $project->rejection_reason = $validated['rejection_reason'] ?? 'Proposal memerlukan perbaikan oleh siswa.';
        }

        $project->save();

        return back()->with('success', 'Status proposal project/penelitian akhir berhasil diperbarui!');
    }

    /**
     * Assign Pembimbing & Penguji Ujian Sidang oleh Panitia
     */
    public function assignAdvisorExaminer(Request $request, $id)
    {
        if (!$this->checkPanitiaAccess('final_project')) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'advisor_id' => 'nullable|exists:teachers,id',
            'examiner_id' => 'nullable|exists:teachers,id',
            'examiner2_id' => 'nullable|exists:teachers,id',
            'exam_date' => 'nullable|date',
            'exam_location' => 'nullable|string|max:255',
            'current_stage' => 'nullable|string',
        ]);

        $project = FinalProject::findOrFail($id);
        if ($request->filled('advisor_id')) $project->advisor_id = $validated['advisor_id'];
        if ($request->filled('examiner_id')) $project->examiner_id = $validated['examiner_id'];
        if ($request->filled('examiner2_id')) $project->examiner2_id = $validated['examiner2_id'];
        if ($request->filled('exam_date')) $project->exam_date = $validated['exam_date'];
        if ($request->filled('exam_location')) $project->exam_location = $validated['exam_location'];
        if ($request->filled('current_stage')) $project->current_stage = $validated['current_stage'];
        $project->save();

        return back()->with('success', 'Data pembimbing, penguji & jadwal sidang berhasil disimpan!');
    }
}
