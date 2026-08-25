<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Services\StudentDnaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TeacherStudentDnaController extends Controller
{
    public function __construct(
        protected StudentDnaService $dnaService
    ) {}

    /**
     * Display list of students in teacher's school or homeroom.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Student::with(['school', 'currentClassroom'])->where('is_active', true);

        if ($user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        if ($request->filled('classroom_id')) {
            $query->whereHas('classrooms', function ($q) use ($request) {
                $q->where('classrooms.id', $request->classroom_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name')->paginate(15)->withQueryString();

        $classrooms = Classroom::where('is_active', true)
            ->when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))
            ->orderBy('name')
            ->get();

        return view('admin.student-dna.index', compact('students', 'classrooms'));
    }

    /**
     * Display detailed 360° DNA for teacher view.
     */
    public function show(Student $student)
    {
        $analysis = $this->dnaService->analyze($student);

        return view('admin.student-dna.show', compact('student', 'analysis'));
    }

    /**
     * Export PDF from teacher view.
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
