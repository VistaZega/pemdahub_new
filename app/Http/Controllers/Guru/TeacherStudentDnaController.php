<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher;
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
        $teacher = Teacher::where('user_id', $user->id)->first();
        $schoolId = $user->school_id ?? $teacher?->school_id;

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
        } elseif ($schoolId) {
            $query->where('school_id', $schoolId);
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

        $classroomsQuery = Classroom::with('school');
        if ($schoolId) {
            $classroomsQuery->where('school_id', $schoolId);
        }
        $classrooms = $classroomsQuery->orderBy('class_name')->get();

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
