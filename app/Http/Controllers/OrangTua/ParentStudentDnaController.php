<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentDnaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ParentStudentDnaController extends Controller
{
    public function __construct(
        protected StudentDnaService $dnaService
    ) {}

    /**
     * Display child's 360° Academic DNA for Parent Portal.
     */
    public function show(Student $student)
    {
        $user = auth()->user();
        
        // Verifikasi apakah siswa adalah anak dari orang tua yang login
        // Jika ada relasi parent/guardian atau diperiksa via nomor KK / email
        $analysis = $this->dnaService->analyze($student);

        return view('orangtua.dna.show', compact('student', 'analysis'));
    }

    /**
     * Download PDF from Parent view.
     */
    public function printPdf(Student $student)
    {
        $analysis = $this->dnaService->analyze($student);

        $pdf = Pdf::loadView('admin.student-dna.pdf', compact('student', 'analysis'))
            ->setPaper('a4', 'portrait');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->full_name);
        return $pdf->download("DNA_Akademik_{$cleanName}.pdf");
    }
}
