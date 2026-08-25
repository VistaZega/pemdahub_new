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
     * Display listing of students with DNA summary overview.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Student::with(['school', 'currentClassroom', 'classrooms']);

        // Inclusive active/enrolled status filter
        $query->where(function ($q) {
            $q->whereNull('status')
              ->orWhereIn('status', ['aktif', 'Aktif', 'active', 'ACTIVE', 'calon', 'naik', 'enrolled']);
        });

        // Filter by classroom (via student_classes pivot)
        if ($request->filled('classroom_id')) {
            $classroomId = $request->classroom_id;
            $query->whereHas('studentClasses', function ($scq) use ($classroomId) {
                $scq->where('classroom_id', $classroomId);
            });
        } elseif ($request->filled('school_id') && $user->isSuperAdmin()) {
            $query->where('school_id', $request->school_id);
        } elseif (!$user->isSuperAdmin() && $user->school_id) {
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

        $schools = School::schoolsOnly()->get();
        $classrooms = Classroom::with('school')
            ->when(!$user->isSuperAdmin() && $user->school_id, fn($q) => $q->where('school_id', $user->school_id))
            ->when($request->filled('school_id') && $user->isSuperAdmin(), fn($q) => $q->where('school_id', $request->school_id))
            ->orderBy('class_name')
            ->get();

        return view('admin.student-dna.index', compact('students', 'schools', 'classrooms'));
    }

    /**
     * Display detailed 360° Academic DNA analysis for a student.
     */
    public function show(Student $student)
    {
        $analysis = $this->dnaService->analyze($student);

        return view('admin.student-dna.show', compact('student', 'analysis'));
    }

    /**
     * Export official PDF Report of Student Academic DNA 360°.
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
