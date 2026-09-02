<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use App\Models\FinalProjectFormat;
use App\Models\FinalProjectLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class FinalProjectStudentController extends Controller
{
    private function getStudentAndClassroom()
    {
        $user = Auth::user();
        $student = $user->student;
        if (!$student) {
            abort(403, 'Akses khusus siswa.');
        }

        $classroom = $student->currentClassroom()->first();
        if (!$classroom || $classroom->grade_level !== 12 || !in_array($student->school->type, ['SMA', 'SMK'])) {
            abort(403, 'Hanya untuk siswa Kelas XII SMA atau SMK.');
        }

        return [$student, $classroom];
    }

    public function index()
    {
        list($student, $classroom) = $this->getStudentAndClassroom();

        $project = $student->currentFinalProject();

        $formats = FinalProjectFormat::where('school_id', $student->school_id)->get();

        if (!$project) {
            // Dapatkan teman sekelas yang belum memiliki kelompok
            $classmates = $classroom->students()
                ->where('students.id', '!=', $student->id)
                ->whereDoesntHave('finalProjectMemberships')
                ->orderBy('full_name')
                ->get();

            return view('siswa.final_projects.propose', compact('student', 'classroom', 'formats', 'classmates'));
        }

        $project->load(['advisor.user', 'examiner.user', 'members.student']);
        $logs = $project->logs()->orderByDesc('log_date')->get();

        return view('siswa.final_projects.index', compact('student', 'classroom', 'project', 'logs', 'formats'));
    }

    public function propose(Request $request)
    {
        list($student, $classroom) = $this->getStudentAndClassroom();

        // Check if student already has a project
        if ($student->currentFinalProject()) {
            return redirect()->route('siswa.final-project.index')->with('error', 'Anda sudah bergabung dalam kelompok Tugas Akhir.');
        }

        if ($student->school->type === 'SMK') {
            return redirect()->route('siswa.final-project.index')->with('error', 'Untuk siswa SMK, judul dan kelompok ditentukan oleh Panitia.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:students,id',
        ]);

        $type = $student->school->type === 'SMA' ? 'penelitian_ilmiah' : 'project_akhir';

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $project = FinalProject::create([
                'student_id' => $student->id, // As leader
                'academic_year_id' => $classroom->academic_year_id,
                'type' => $type,
                'title' => $validated['title'],
                'abstract' => $validated['abstract'],
                'status' => 'pending',
            ]);

            // Add leader
            \App\Models\FinalProjectMember::create([
                'final_project_id' => $project->id,
                'student_id' => $student->id,
                'role' => 'leader'
            ]);

            // Add members
            if (!empty($validated['member_ids'])) {
                foreach ($validated['member_ids'] as $memberId) {
                    // Verifikasi apakah member satu kelas dan belum punya kelompok
                    $member = \App\Models\Student::find($memberId);
                    if ($member && $member->currentClassroom()->first()?->id === $classroom->id) {
                        if (!$member->currentFinalProject()) {
                            \App\Models\FinalProjectMember::create([
                                'final_project_id' => $project->id,
                                'student_id' => $memberId,
                                'role' => 'member'
                            ]);
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('siswa.final-project.index')->with('success', 'Usulan kelompok berhasil diajukan. Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengajukan judul: ' . $e->getMessage());
        }
    }

    public function storeLog(Request $request)
    {
        list($student, $classroom) = $this->getStudentAndClassroom();

        $project = $student->currentFinalProject();
        if (!$project) {
            abort(404, 'Kelompok Tugas Akhir tidak ditemukan.');
        }

        // Only allow logs if project is approved or in progress or ready for exam
        if (!in_array($project->status, ['approved', 'in_progress', 'ready_for_exam'])) {
            return redirect()->route('siswa.final-project.index')->with('error', 'Anda belum dapat mengisi logbook progress.');
        }

        $validated = $request->validate([
            'log_date' => 'required|date|before_or_equal:today',
            'stage' => 'required|string|in:bab1,bab2,bab3,bab4,bab5,sidang',
            'activity' => 'required|string',
            'documentation_file' => 'nullable|file|mimes:pdf,doc,docx,zip,png,jpg,jpeg|max:5120', // Max 5MB
        ]);

        // Mencegah siswa mengirim progress jika tahapan bab tidak sesuai dengan current_stage aktif proyek
        if ($validated['stage'] !== $project->current_stage) {
            $stages = FinalProject::getStages();
            $activeStageName = $stages[$project->current_stage]['name'] ?? $project->current_stage;
            return redirect()->route('siswa.final-project.index')->with('error', 'Anda hanya dapat mengirimkan kemajuan untuk tahapan aktif kelompok Anda saat ini: ' . $activeStageName);
        }

        // Mencegah kiriman ganda jika ada log bab ini yang masih berstatus 'submitted' (menunggu review)
        $hasPendingLog = FinalProjectLog::where('final_project_id', $project->id)
            ->where('stage', $project->current_stage)
            ->where('status', 'submitted')
            ->exists();

        if ($hasPendingLog) {
            return redirect()->route('siswa.final-project.index')->with('error', 'Ada bimbingan yang sedang menunggu tinjauan pada bab ini. Silakan tunggu feedback dari pembimbing Anda.');
        }

        $filePath = null;
        if ($request->hasFile('documentation_file')) {
            $filePath = $request->file('documentation_file')->store('final_project_docs', 'public');
        }

        $log = FinalProjectLog::create([
            'final_project_id' => $project->id,
            'log_date' => $validated['log_date'],
            'stage' => $validated['stage'],
            'activity' => $validated['activity'],
            'documentation_file' => $filePath,
            'status' => 'submitted',
        ]);

        // Award reputation points for student progress (+10 points) to all members
        $members = $project->members()->with('student')->get();
        if ($members->isEmpty() && $project->student && $project->student->user_id) {
            \App\Models\ReputationLog::log(
                $project->student->user_id,
                10,
                'final_project',
                'Logbook Kelompok Tugas Akhir diisi oleh ' . $student->full_name . ' (' . $validated['log_date'] . ')',
                $log
            );
        } else {
            foreach ($members as $member) {
                if ($member->student && $member->student->user_id) {
                    \App\Models\ReputationLog::log(
                        $member->student->user_id,
                        10,
                        'final_project',
                        'Logbook Kelompok Tugas Akhir diisi oleh ' . $student->full_name . ' (' . $validated['log_date'] . ')',
                        $log
                    );
                }
            }
        }

        // Automatically transition project status to 'in_progress' if it was 'approved'
        if ($project->status === 'approved') {
            $project->update(['status' => 'in_progress']);
        }

        return redirect()->route('siswa.final-project.index')->with('success', 'Logbook progress berhasil ditambahkan.');
    }

    public function downloadGuideline()
    {
        list($student, $classroom) = $this->getStudentAndClassroom();
        $year = $classroom->academicYear->year ?? date('Y');
        
        $schoolCode = strtolower($student->school->code ?? '');
        $viewName = str_contains($schoolCode, 'smk') ? 'pdf.panduan_smk' : 'pdf.panduan_sma';
        if (!view()->exists($viewName)) {
            $viewName = 'pdf.panduan_penelitian';
        }
        
        $pdf = Pdf::loadView($viewName, compact('year', 'student'));
        return $pdf->download('Buku_Panduan_Penyusunan_Penelitian_dan_Tugas_Akhir.pdf');
    }

    public function downloadFormat($id)
    {
        list($student, $classroom) = $this->getStudentAndClassroom();

        $format = FinalProjectFormat::where('school_id', $student->school_id)->findOrFail($id);

        if (!$format->file_path || !Storage::disk('public')->exists($format->file_path)) {
            abort(404, 'File format tidak ditemukan.');
        }

        return Storage::disk('public')->download($format->file_path, str_replace(['/', '\\'], '-', $format->title . '.' . pathinfo($format->file_path, PATHINFO_EXTENSION)));
    }
}
