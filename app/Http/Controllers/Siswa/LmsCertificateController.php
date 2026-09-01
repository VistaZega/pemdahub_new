<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\LmsCertificate;
use App\Models\LmsCourse;
use App\Models\LmsMaterialProgress;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class LmsCertificateController extends Controller
{
    private function getStudent(): ?Student
    {
        return Student::where('user_id', Auth::id())->first();
    }

    /**
     * Claim certificate for a course (if progress >= 100%)
     */
    public function claim(LmsCourse $course)
    {
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $progress = LmsMaterialProgress::getProgressForCourse($course->id, $student->id);
        if ($progress < 100) {
            return back()->with('error', 'Progress belajar Anda belum 100%. Selesaikan seluruh materi terlebih dahulu.');
        }

        $cert = LmsCertificate::issueForStudent($student, $course);
        if (!$cert) {
            return back()->with('error', 'Gagal menerbitkan sertifikat. Silakan coba lagi.');
        }

        return redirect()->route('siswa.lms.certificates.show', $cert->id)
            ->with('success', '🎉 Selamat! Sertifikat penyelesaian kursus berhasil diterbitkan!');
    }

    /**
     * Show certificate detail
     */
    public function show(LmsCertificate $certificate)
    {
        $student = $this->getStudent();
        if (!$student || $certificate->student_id !== $student->id) {
            abort(403);
        }

        return view('siswa.lms.certificate-show', compact('certificate'));
    }

    /**
     * Download certificate as PDF
     */
    public function download(LmsCertificate $certificate)
    {
        $student = $this->getStudent();
        if (!$student || $certificate->student_id !== $student->id) {
            abort(403);
        }

        $certificate->load(['student.user', 'course.subject', 'course.teacher.user']);

        $pdf = Pdf::loadView('siswa.lms.certificate-pdf', [
            'certificate' => $certificate,
        ]);

        $fileName = 'Sertifikat-' . $certificate->course->course_name . '-' . $student->full_name . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * List student certificates
     */
    public function index()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $certificates = LmsCertificate::where('student_id', $student->id)
            ->with('course.subject')
            ->latest('issued_at')
            ->get();

        return view('siswa.lms.certificates-index', compact('certificates'));
    }
}