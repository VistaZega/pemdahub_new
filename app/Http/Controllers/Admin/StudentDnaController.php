<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\School;
use App\Services\StudentDnaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentDnaController extends Controller
{
    public function __construct(
        protected StudentDnaService $dnaService
    ) {}

    /**
     * Helper: Check if user is SuperAdmin or Foundation Executive
     */
    private function isFoundationOrSuperAdmin($user): bool
    {
        return $user->isSuperAdmin() 
            || in_array($user->role, ['superadmin', 'yayasan', 'ketua_yayasan', 'pengurus_yayasan']);
    }

    /**
     * Display listing of students with DNA summary overview.
     * Kewenangan:
     * - Super Admin & Yayasan: Akses seluruh unit sekolah (3 unit aktif).
     * - Admin Sekolah & Kepala Sekolah: Terkunci pada unit sekolahnya saja.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isGlobal = $this->isFoundationOrSuperAdmin($user);

        $query = Student::with(['school', 'currentClassroom', 'classrooms']);

        // Inclusive active/enrolled status filter
        $query->where(function ($q) {
            $q->whereNull('status')
              ->orWhereIn('status', ['aktif', 'Aktif', 'active', 'ACTIVE', 'calon', 'naik', 'enrolled']);
        });

        // Filter unit sekolah & kelas sesuai kewenangan
        if ($request->filled('classroom_id')) {
            $classroomId = $request->classroom_id;
            $query->whereHas('studentClasses', function ($scq) use ($classroomId) {
                $scq->where('classroom_id', $classroomId);
            });
        } elseif ($isGlobal && $request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        } elseif (!$isGlobal && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name')->paginate(15)->withQueryString();

        $schools = $isGlobal ? School::schoolsOnly()->get() : collect();
        $classrooms = Classroom::with('school')
            ->when(!$isGlobal && $user->school_id, fn($q) => $q->where('school_id', $user->school_id))
            ->when($isGlobal && $request->filled('school_id'), fn($q) => $q->where('school_id', $request->school_id))
            ->orderBy('class_name')
            ->get();

        return view('admin.student-dna.index', compact('students', 'schools', 'classrooms'));
    }

    /**
     * Display detailed 360° Academic DNA analysis for a student.
     */
    public function show(Student $student)
    {
        $this->authorizeAccess($student);
        $analysis = $this->dnaService->analyze($student);

        return view('admin.student-dna.show', compact('student', 'analysis'));
    }

    /**
     * Export official PDF Report of Student Academic DNA 360°.
     */
    public function printPdf(Student $student)
    {
        $this->authorizeAccess($student);
        $analysis = $this->dnaService->analyze($student);

        $pdf = Pdf::loadView('admin.student-dna.pdf', compact('student', 'analysis'))
            ->setPaper('a4', 'portrait');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->full_name);
        return $pdf->download("DNA_Akademik_{$cleanName}.pdf");
    }

    /**
     * Validasi otorisasi akses spesifik siswa
     */
    private function authorizeAccess(Student $student): void
    {
        $user = auth()->user();
        if ($this->isFoundationOrSuperAdmin($user)) {
            return;
        }

        // Admin Sekolah / Kepala Sekolah hanya berhak melihat siswa di unit sekolahnya
        if ($user->school_id && $student->school_id != $user->school_id) {
            abort(403, 'Akses Ditolak: Anda hanya berwenang mengakses data DNA siswa di unit sekolah Anda.');
        }
    }
}
