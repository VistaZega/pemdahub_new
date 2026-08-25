<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDiagnosticAssessment;
use App\Services\StudentDnaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentDnaPortalController extends Controller
{
    public function __construct(
        protected StudentDnaService $dnaService
    ) {}

    /**
     * Display student's own 360° Academic DNA.
     */
    public function index()
    {
        $user = auth()->user();
        $student = $user->student ?? Student::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Profil siswa Anda tidak ditemukan.');
        }

        $analysis = $this->dnaService->analyze($student);

        return view('siswa.dna.index', compact('student', 'analysis'));
    }

    /**
     * Save or update student diagnostic self-assessment questionnaire.
     */
    public function saveDiagnostic(Request $request)
    {
        $user = auth()->user();
        $student = $user->student ?? Student::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'work_style_preference' => 'required|string|max:100',
            'favorite_subject_cluster' => 'required|string|max:100',
            'career_aspiration' => 'required|string|max:255',
            'interests' => 'nullable|array',
            'logic_self_score' => 'required|integer|min:50|max:100',
            'creative_self_score' => 'required|integer|min:50|max:100',
            'communication_self_score' => 'required|integer|min:50|max:100',
            'technical_self_score' => 'required|integer|min:50|max:100',
            'social_self_score' => 'required|integer|min:50|max:100',
            'discipline_self_score' => 'required|integer|min:50|max:100',
        ]);

        StudentDiagnosticAssessment::updateOrCreate(
            ['student_id' => $student->id],
            $validated
        );

        return redirect()->route('siswa.dna.index')
            ->with('success', 'Pemetaan minat & asesmen mandiri DNA berhasil diperbarui! Profil DNA Anda telah dikalibrasi ulang.');
    }

    /**
     * Download official PDF report of student's own DNA.
     */
    public function printPdf()
    {
        $user = auth()->user();
        $student = $user->student ?? Student::where('user_id', $user->id)->firstOrFail();

        $analysis = $this->dnaService->analyze($student);

        $pdf = Pdf::loadView('admin.student-dna.pdf', compact('student', 'analysis'))
            ->setPaper('a4', 'portrait');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->full_name);
        return $pdf->download("DNA_Akademik_{$cleanName}.pdf");
    }
}
