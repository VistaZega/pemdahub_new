<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use App\Models\FinalProjectLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinalProjectTeacherController extends Controller
{
    private function getTeacher()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            abort(403, 'Akses khusus guru.');
        }
        return $teacher;
    }

    public function bimbinganIndex(Request $request)
    {
        $teacher = $this->getTeacher();

        $query = FinalProject::with(['student.school', 'student.user'])
            ->where('advisor_id', $teacher->id);

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

        return view('guru.final_projects.index', compact('projects'));
    }

    public function bimbinganShow($id)
    {
        $teacher = $this->getTeacher();

        $project = FinalProject::with(['student.school', 'student.user', 'logs' => function($q) {
            $q->orderByDesc('log_date');
        }])->findOrFail($id);

        if ($project->advisor_id !== $teacher->id) {
            abort(403, 'Anda bukan pembimbing untuk siswa ini.');
        }

        return view('guru.final_projects.show', compact('project'));
    }

    public function reviewLog(Request $request, $projectId, $logId)
    {
        $teacher = $this->getTeacher();

        $project = FinalProject::findOrFail($projectId);
        if ($project->advisor_id !== $teacher->id) {
            abort(403, 'Anda bukan pembimbing untuk siswa ini.');
        }

        $log = FinalProjectLog::where('final_project_id', $project->id)->findOrFail($logId);

        $validated = $request->validate([
            'advisor_feedback' => 'required|string',
            'status' => 'required|string|in:approved,rejected',
        ]);

        $log->update([
            'advisor_feedback' => $validated['advisor_feedback'],
            'status' => $validated['status'],
        ]);

        // Jika disetujui (ACC), naikkan tahapan kelompok bimbingan ke Bab berikutnya
        if ($validated['status'] === 'approved') {
            $stages = FinalProject::getStages();
            $currentStageKey = $project->current_stage;
            
            // Cek jika ada tahapan berikutnya
            if (isset($stages[$currentStageKey]['next']) && $stages[$currentStageKey]['next'] !== null) {
                $project->update([
                    'current_stage' => $stages[$currentStageKey]['next'],
                ]);
            }
        }

        // Award reputation points for teacher mentoring (+15 points)
        \App\Models\ReputationLog::log(
            $teacher->user_id,
            15,
            'mentoring',
            'Mereview logbook Tugas Akhir siswa: ' . $project->student->full_name,
            $log
        );

        return redirect()->route('guru.final-projects.bimbingan.show', $project->id)->with('success', 'Tinjauan bimbingan berhasil disimpan.');
    }

    public function markReadyForExam($id)
    {
        $teacher = $this->getTeacher();

        $project = FinalProject::findOrFail($id);
        if ($project->advisor_id !== $teacher->id) {
            abort(403, 'Anda bukan pembimbing untuk siswa ini.');
        }

        $project->update([
            'status' => 'ready_for_exam',
        ]);

        // Award reputation points for all students (+50 points)
        $members = $project->members()->with('student')->get();
        if ($members->isEmpty() && $project->student && $project->student->user_id) {
            \App\Models\ReputationLog::log(
                $project->student->user_id,
                50,
                'final_project',
                'Tugas Akhir dinyatakan Layak Ujian/Sidang',
                $project
            );
        } else {
            foreach ($members as $member) {
                if ($member->student && $member->student->user_id) {
                    \App\Models\ReputationLog::log(
                        $member->student->user_id,
                        50,
                        'final_project',
                        'Tugas Akhir dinyatakan Layak Ujian/Sidang',
                        $project
                    );
                }
            }
        }

        return redirect()->route('guru.final-projects.bimbingan.show', $project->id)->with('success', 'Tugas Akhir dinyatakan LAYAK SIDANG/UJIAN.');
    }

    public function ujianIndex(Request $request)
    {
        $teacher = $this->getTeacher();

        $query = FinalProject::with(['student.school', 'student.user', 'advisor.user'])
            ->where('examiner_id', $teacher->id)
            ->whereIn('status', ['ready_for_exam', 'completed']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $examProjects = $query->latest('exam_date')->paginate(15)->withQueryString();

        return view('guru.final_projects.exam_index', compact('examProjects'));
    }

    public function gradeProject(Request $request, $id)
    {
        $teacher = $this->getTeacher();

        $project = FinalProject::findOrFail($id);
        if ($project->examiner_id !== $teacher->id) {
            abort(403, 'Anda bukan penguji untuk siswa ini.');
        }

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'grade_notes' => 'nullable|string',
        ]);

        $project->update([
            'grade' => $validated['grade'],
            'grade_notes' => $validated['grade_notes'],
            'status' => 'completed',
        ]);

        // Award reputation points for all students (+200 points)
        $members = $project->members()->with('student')->get();
        if ($members->isEmpty() && $project->student && $project->student->user_id) {
            \App\Models\ReputationLog::log(
                $project->student->user_id,
                200,
                'final_project',
                'Lulus Sidang Tugas Akhir dengan Nilai: ' . $validated['grade'],
                $project
            );
        } else {
            foreach ($members as $member) {
                if ($member->student && $member->student->user_id) {
                    \App\Models\ReputationLog::log(
                        $member->student->user_id,
                        200,
                        'final_project',
                        'Lulus Sidang Tugas Akhir dengan Nilai: ' . $validated['grade'],
                        $project
                    );
                }
            }
        }

        // Award reputation points for teacher examining (+30 points)
        \App\Models\ReputationLog::log(
            $teacher->user_id,
            30,
            'examination',
            'Menguji Sidang Tugas Akhir siswa: ' . $project->student->full_name,
            $project
        );

        return redirect()->route('guru.final-projects.ujian.index')->with('success', 'Nilai ujian berhasil diinput.');
    }
}
