<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\P5Project;
use App\Models\P5ProjectTarget;
use App\Models\P5Assessment;
use App\Models\P5ProjectNote;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class P5ProjectController extends Controller
{
    /**
     * Standard P5 Dimensions recommended by Kemendikbudristek
     */
    public static array $dimensions = [
        'Beriman, Bertakwa Kepada Tuhan YME, dan Berakhlak Mulia',
        'Berkebinekaan Global',
        'Gotong Royong',
        'Mandiri',
        'Bernalar Kritis',
        'Kreatif',
    ];

    /**
     * Standard P5 Themes recommended by Kemendikbudristek
     */
    public static array $themes = [
        'Gaya Hidup Berkelanjutan',
        'Kearifan Lokal',
        'Bhinneka Tunggal Ika',
        'Bangunlah Jiwa dan Raganya',
        'Suara Demokrasi',
        'Rekayasa dan Teknologi',
        'Kewirausahaan',
        'Kebekerjaan (SMK)',
    ];

    /**
     * Display a listing of P5 projects.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = P5Project::with(['school', 'academicYear', 'classroom', 'creator', 'targets'])
            ->withCount(['targets', 'assessments']);

        if (!$user->isSuperAdmin() && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        if ($request->filled('school_id') && $user->isSuperAdmin()) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(12);

        $schools = School::schoolsOnly()->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $classrooms = Classroom::where('is_active', true)->orderBy('name')->get();

        return view('admin.p5.index', compact('projects', 'schools', 'academicYears', 'classrooms'));
    }

    /**
     * Show the form for creating a new P5 project.
     */
    public function create()
    {
        $user = auth()->user();
        $schools = School::schoolsOnly()->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        $classroomQuery = Classroom::where('is_active', true);
        if (!$user->isSuperAdmin() && $user->school_id) {
            $classroomQuery->where('school_id', $user->school_id);
        }
        $classrooms = $classroomQuery->orderBy('name')->get();

        $dimensions = self::$dimensions;
        $themes = self::$themes;

        return view('admin.p5.create', compact('schools', 'academicYears', 'classrooms', 'dimensions', 'themes'));
    }

    /**
     * Store a newly created P5 project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'description' => 'nullable|string',
            'targets' => 'required|array|min:1',
            'targets.*.dimension' => 'required|string|max:255',
            'targets.*.sub_element' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $project = P5Project::create([
                'school_id' => $validated['school_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'classroom_id' => $validated['classroom_id'],
                'title' => $validated['title'],
                'theme' => $validated['theme'],
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['targets'] as $target) {
                if (!empty($target['sub_element'])) {
                    P5ProjectTarget::create([
                        'p5_project_id' => $project->id,
                        'dimension' => $target['dimension'],
                        'sub_element' => $target['sub_element'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.p5.show', $project)
                ->with('success', "Projek P5 '{$project->title}' berhasil dibuat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating P5 project: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat Projek P5: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified P5 project and student assessment status.
     */
    public function show(P5Project $project)
    {
        $project->load(['school', 'academicYear', 'classroom', 'targets', 'creator']);
        
        $students = Student::whereHas('classrooms', function ($q) use ($project) {
            $q->where('classrooms.id', $project->classroom_id);
        })->active()->orderBy('full_name')->get();

        $assessments = P5Assessment::where('p5_project_id', $project->id)->get()->groupBy('student_id');
        $notes = P5ProjectNote::where('p5_project_id', $project->id)->get()->keyBy('student_id');

        return view('admin.p5.show', compact('project', 'students', 'assessments', 'notes'));
    }

    /**
     * Show form for bulk assessing students in a P5 project.
     */
    public function assessForm(P5Project $project)
    {
        $project->load(['school', 'academicYear', 'classroom', 'targets']);

        $students = Student::whereHas('classrooms', function ($q) use ($project) {
            $q->where('classrooms.id', $project->classroom_id);
        })->active()->orderBy('full_name')->get();

        $existingAssessments = P5Assessment::where('p5_project_id', $project->id)
            ->get()
            ->groupBy('student_id')
            ->map(function ($items) {
                return $items->keyBy('p5_project_target_id');
            });

        $existingNotes = P5ProjectNote::where('p5_project_id', $project->id)
            ->get()
            ->keyBy('student_id');

        return view('admin.p5.assess', compact('project', 'students', 'existingAssessments', 'existingNotes'));
    }

    /**
     * Bulk store P5 assessments and notes.
     */
    public function assessStore(Request $request, P5Project $project)
    {
        $request->validate([
            'scores' => 'nullable|array',
            'scores.*.*' => 'nullable|in:MB,SB,BSH,SAB',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Save target scores
            if ($request->filled('scores')) {
                foreach ($request->scores as $studentId => $targetScores) {
                    foreach ($targetScores as $targetId => $score) {
                        if (!empty($score)) {
                            P5Assessment::updateOrCreate(
                                [
                                    'p5_project_id' => $project->id,
                                    'student_id' => $studentId,
                                    'p5_project_target_id' => $targetId,
                                ],
                                [
                                    'score' => $score,
                                ]
                            );
                        }
                    }
                }
            }

            // Save individual project notes
            if ($request->filled('notes')) {
                foreach ($request->notes as $studentId => $noteText) {
                    P5ProjectNote::updateOrCreate(
                        [
                            'p5_project_id' => $project->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'notes' => $noteText ?? '',
                        ]
                    );
                }
            }

            DB::commit();

            return redirect()->route('admin.p5.show', $project)
                ->with('success', 'Penilaian Projek P5 berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error saving P5 assessments: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan penilaian P5: ' . $e->getMessage());
        }
    }

    /**
     * Generate & Download PDF Rapor P5 for a student.
     */
    public function printRaport(P5Project $project, Student $student)
    {
        $project->load(['school', 'academicYear', 'classroom', 'targets']);
        $student->load('school');

        $assessments = P5Assessment::where('p5_project_id', $project->id)
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('p5_project_target_id');

        $note = P5ProjectNote::where('p5_project_id', $project->id)
            ->where('student_id', $student->id)
            ->first();

        $pdf = Pdf::loadView('admin.p5.pdf', compact('project', 'student', 'assessments', 'note'))
            ->setPaper('a4', 'portrait');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->full_name);
        return $pdf->download("Rapor_P5_{$cleanName}_{$project->id}.pdf");
    }

    /**
     * Remove the specified P5 project.
     */
    public function destroy(P5Project $project)
    {
        try {
            $title = $project->title;
            $project->delete();
            return redirect()->route('admin.p5.index')
                ->with('success', "Projek P5 '{$title}' berhasil dihapus.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus Projek P5: ' . $e->getMessage());
        }
    }
}
