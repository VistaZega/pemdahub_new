<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentStatusHistory;
use App\Models\StudentPromotion;
use App\Models\Alumni;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\StudentClass;
use App\Services\StudentLifecycleService;
use Illuminate\Http\Request;

class StudentLifecycleController extends Controller
{
    public function __construct(private StudentLifecycleService $service) {}

    /**
     * Status history timeline for a student
     */
    public function statusHistory(Student $student)
    {
        $histories = StudentStatusHistory::where('student_id', $student->id)
            ->with('changedByUser')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get();

        return view('admin.students.lifecycle.status-history', compact('student', 'histories'));
    }

    /**
     * Form to transition student status
     */
    public function transitionForm(Student $student)
    {
        $currentStatus = $student->status;
        $allowedTransitions = StudentStatusHistory::TRANSITIONS[$currentStatus] ?? [];
        $statuses = StudentStatusHistory::STATUSES;

        return view('admin.students.lifecycle.transition', compact('student', 'allowedTransitions', 'statuses'));
    }

    /**
     * Process status transition
     */
    public function transition(Request $request, Student $student)
    {
        $request->validate([
            'to_status' => 'required|string',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'document_number' => 'nullable|string|max:100',
            'effective_date' => 'nullable|date',
        ]);

        try {
            $this->service->transitionStatus($student, $request->to_status, $request->all());
            return redirect()->route('admin.students.show', $student)
                ->with('success', "Status siswa berhasil diubah menjadi '{$request->to_status}'.");
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['to_status' => $e->getMessage()]);
        }
    }

    /**
     * Promotion page - list students per classroom for promotion decisions
     */
    public function promotionIndex(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYearId = $request->get('academic_year_id', $activeYear?->id);
        $selectedClassroomId = $request->get('classroom_id');

        $classrooms = Classroom::where('is_active', true)
            ->when($selectedYearId, function ($q) use ($selectedYearId) {
                $q->where('academic_year_id', $selectedYearId);
            })
            ->orderBy('class_name')
            ->get();

        $students = collect();
        $classroom = null;

        if ($selectedClassroomId) {
            $classroom = Classroom::findOrFail($selectedClassroomId);
            $students = StudentClass::where('classroom_id', $selectedClassroomId)
                ->where('academic_year_id', $selectedYearId)
                ->where('status', 'aktif')
                ->with('student')
                ->get()
                ->pluck('student');
        }

        return view('admin.students.lifecycle.promotion', compact(
            'academicYears', 'classrooms', 'students', 'classroom',
            'selectedYearId', 'selectedClassroomId'
        ));
    }

    /**
     * Process bulk promotion
     */
    public function promotionStore(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'decisions' => 'required|array',
            'decisions.*.decision' => 'required|in:naik,tinggal,lulus,pindah,keluar',
            'decisions.*.to_classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $currentYear = AcademicYear::findOrFail($request->academic_year_id);
        $nextYear = AcademicYear::where('start_year', $currentYear->start_year + 1)->first();

        if (!$nextYear && collect($request->decisions)->contains('decision', 'naik')) {
            return back()->withErrors(['error' => 'Tahun ajaran berikutnya belum dibuat.']);
        }

        $studentDecisions = [];
        foreach ($request->decisions as $studentId => $decision) {
            $studentDecisions[] = array_merge($decision, ['student_id' => $studentId]);
        }

        $results = $this->service->bulkPromote(
            $studentDecisions,
            $currentYear,
            $nextYear,
            auth()->id()
        );

        $message = "Promosi: {$results['promoted']} naik, {$results['retained']} tinggal, {$results['graduated']} lulus.";
        if (!empty($results['errors'])) {
            $message .= ' ' . count($results['errors']) . ' error.';
        }

        return redirect()->route('admin.promotions.index')
            ->with('success', $message);
    }

    /**
     * Alumni management page
     */
    public function alumniIndex(Request $request)
    {
        $query = Alumni::with(['student', 'school']);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        if ($request->filled('graduation_year')) {
            $query->where('graduation_year', $request->graduation_year);
        }
        if ($request->filled('search')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%");
            });
        }

        $alumni = $query->orderByDesc('graduation_year')->paginate(20)->withQueryString();
        $schools = \App\Models\School::where('type', '!=', 'yayasan')->orderBy('name')->get();

        return view('admin.students.lifecycle.alumni', compact('alumni', 'schools'));
    }
}
