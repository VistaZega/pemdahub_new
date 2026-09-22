<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\StudentAchievement;
use App\Models\Teacher;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardController extends Controller
{
    /**
     * Get the authenticated teacher record.
     */
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Get homeroom classrooms for the teacher.
     */
    private function getHomeroomClassrooms(Teacher $teacher)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return collect();

        $tIds = \Illuminate\Support\Facades\Auth::user() ? \Illuminate\Support\Facades\Auth::user()->teacherIds() : $teacher->allTeacherIds();

        return Classroom::whereIn('homeroom_teacher_id', $tIds)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_active', true)
            ->with('school')
            ->withCount(['students' => function ($q) use ($activeYear) {
                $q->where('student_classes.status', 'aktif');
                if ($activeYear) {
                    $q->where('student_classes.academic_year_id', $activeYear->id);
                }
            }])
            ->orderBy('class_name')
            ->get();
    }

    /**
     * Verify teacher is a homeroom teacher.
     */
    private function authorizeHomeroomTeacher(Teacher $teacher)
    {
        $classrooms = $this->getHomeroomClassrooms($teacher);
        if ($classrooms->isEmpty()) {
            abort(403, 'Anda bukan wali kelas. Hanya wali kelas yang dapat mengakses raport.');
        }
        return $classrooms;
    }

    /**
     * Verify report card belongs to teacher's homeroom class.
     */
    private function authorizeReportCard(ReportCard $reportCard, $homeroomClassroomIds)
    {
        if (!$homeroomClassroomIds->contains($reportCard->classroom_id)) {
            abort(403, 'Anda hanya dapat mengelola rapor dari kelas yang Anda ampu.');
        }
    }

    /**
     * Index — list report cards for homeroom class.
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->getHomeroomClassrooms($teacher);
        
        if ($classrooms->isEmpty()) {
            return view('guru.raport.no_class', compact('teacher'));
        }
        
        $classroomIds = $classrooms->pluck('id');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $semesters = Semester::with('academicYear')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderByDesc('id')
            ->get();

        $semesterId = $request->input('semester_id');
        if (!$semesterId) {
            $semesterId = ($activeSemester && $semesters->contains('id', $activeSemester->id))
                ? $activeSemester->id
                : ($semesters->first()?->id ?? null);
        }

        $selectedClassroomId = $request->input('classroom_id');
        if (!$selectedClassroomId && $classrooms->count() === 1) {
            $selectedClassroomId = $classrooms->first()->id;
        }
        $status = $request->input('status');

        $query = ReportCard::with(['student', 'semester', 'academicYear', 'classroom'])
            ->whereIn('classroom_id', $classroomIds)
            ->bySemester($semesterId);

        if ($selectedClassroomId) {
            $query->byClassroom($selectedClassroomId);
        }
        if ($status) {
            $query->byStatus($status);
        }

        $reportCards = $query->orderBy('classroom_id')->orderBy('rank')->paginate(20)->withQueryString();

        // Statistics
        $statusCounts = ReportCard::whereIn('classroom_id', $classroomIds)
            ->where('semester_id', $semesterId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total' => $statusCounts->sum(),
            'draft' => $statusCounts->get('draft', 0),
            'finalized' => $statusCounts->get('finalized', 0),
            'published' => $statusCounts->get('published', 0),
        ];

        return view('guru.raport.index', compact(
            'teacher', 'classrooms', 'reportCards', 'semesters',
            'semesterId', 'selectedClassroomId', 'status', 'stats'
        ));
    }

    /**
     * Generate report cards for homeroom class.
     */
    public function generate(Request $request)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $classroomIds = $classrooms->pluck('id');

        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $semesterId = $validated['semester_id'];
        $classroomId = $validated['classroom_id'] ?? $classroomIds->first();

        if (!$classroomIds->contains((int) $classroomId)) {
            return redirect()->back()->with('error', 'Anda hanya bisa generate rapor untuk kelas yang Anda ampu.');
        }

        $semester = Semester::with('academicYear')->findOrFail($semesterId);
        $academicYearId = $semester->academic_year_id;

        $students = \App\Models\Student::where('status', 'aktif')
            ->whereHas('studentClasses', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId)->where('status', 'aktif');
            })->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif untuk generate rapor.');
        }

        $generated = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $studentClass = $student->studentClasses()
                    ->where('status', 'aktif')
                    ->where('academic_year_id', $academicYearId)
                    ->first();

                if (!$studentClass) continue;

                $gradeService = app(GradeService::class);
                $reportData = $gradeService->getStudentReportCard($student->id, $semesterId, $student->school_id);

                $avgScore = 0;
                $predicate = 'D';
                if (!empty($reportData['subjects'])) {
                    $avgScore = $reportData['overall_average'];
                    $predicate = $reportData['overall_predicate'];
                    $gradeService->calculateAndSaveFinalGrades($student->id, $semesterId, $student->school_id);
                }

                $attendanceData = $this->calculateAttendance($student->id, $semester);

                $reportCard = ReportCard::where('student_id', $student->id)
                    ->where('semester_id', $semesterId)
                    ->first();

                $data = [
                    'student_id' => $student->id,
                    'semester_id' => $semesterId,
                    'academic_year_id' => $academicYearId,
                    'classroom_id' => $classroomId,
                    'average_score' => round($avgScore, 2),
                    'predicate' => $predicate,
                    'total_days' => $attendanceData['total_days'],
                    'days_present' => $attendanceData['present'],
                    'days_sick' => $attendanceData['sick'],
                    'days_permission' => $attendanceData['permission'],
                    'days_absent' => $attendanceData['absent'],
                ];

                if ($reportCard) {
                    if ($reportCard->status === 'draft') {
                        $reportCard->update($data);
                        $updated++;
                    }
                } else {
                    ReportCard::create($data);
                    $generated++;
                }
            }

            // Calculate ranks
            $this->calculateRanks($semesterId, $classroomId);

            DB::commit();

            $message = "Berhasil generate {$generated} rapor baru";
            if ($updated > 0) $message .= " dan update {$updated} rapor draft";

            return redirect()->route('guru.raport.index', ['semester_id' => $semesterId])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Guru gagal generate rapor: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate rapor. Silakan coba lagi.');
        }
    }

    /**
     * Show a report card detail.
     */
    public function show(ReportCard $reportCard)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $this->authorizeReportCard($reportCard, $classrooms->pluck('id'));

        $reportCard->load(['student.school', 'semester.academicYear', 'classroom', 'finalizedBy', 'publishedBy']);

        $subjectScores = $this->calculateSubjectScores($reportCard);

        $achievements = StudentAchievement::where('student_id', $reportCard->student_id)
            ->where('academic_year_id', $reportCard->academic_year_id)
            ->orderByDesc('level')
            ->orderByDesc('achievement_date')
            ->get();

        return view('guru.raport.show', compact('teacher', 'reportCard', 'subjectScores', 'achievements'));
    }

    /**
     * Edit a report card's notes.
     */
    public function edit(ReportCard $reportCard)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $this->authorizeReportCard($reportCard, $classrooms->pluck('id'));

        if (!$reportCard->isEditable()) {
            return redirect()->back()->with('error', 'Rapor yang sudah finalized/published tidak bisa diedit.');
        }

        $reportCard->load(['student', 'semester', 'classroom']);

        return view('guru.raport.edit', compact('teacher', 'reportCard'));
    }

    /**
     * Update report card notes.
     */
    public function update(Request $request, ReportCard $reportCard)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $this->authorizeReportCard($reportCard, $classrooms->pluck('id'));

        if (!$reportCard->isEditable()) {
            return redirect()->back()->with('error', 'Rapor yang sudah finalized/published tidak bisa diedit.');
        }

        $validated = $request->validate([
            'teacher_notes' => 'nullable|string|max:1000',
        ]);

        $reportCard->update($validated);

        return redirect()->route('guru.raport.show', $reportCard)
            ->with('success', 'Catatan rapor berhasil disimpan.');
    }

    /**
     * Finalize a report card.
     */
    public function finalize(ReportCard $reportCard)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $this->authorizeReportCard($reportCard, $classrooms->pluck('id'));

        if ($reportCard->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya rapor draft yang bisa difinalize.');
        }

        $reportCard->update([
            'status' => 'finalized',
            'finalized_by' => Auth::id(),
            'finalized_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Rapor berhasil difinalize.');
    }

    /**
     * Publish a report card.
     */
    public function publish(ReportCard $reportCard)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $this->authorizeReportCard($reportCard, $classrooms->pluck('id'));

        if ($reportCard->status !== 'finalized') {
            return redirect()->back()->with('error', 'Hanya rapor finalized yang bisa dipublish.');
        }

        $reportCard->update([
            'status' => 'published',
            'published_by' => Auth::id(),
            'published_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Rapor berhasil dipublish. Orang tua kini dapat melihat rapor.');
    }

    /**
     * Download report card as PDF.
     */
    public function print(ReportCard $reportCard)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $this->authorizeReportCard($reportCard, $classrooms->pluck('id'));

        $reportCard->load(['student.school', 'semester.academicYear', 'classroom']);

        $subjectScores = $this->calculateSubjectScores($reportCard);

        $achievements = StudentAchievement::where('student_id', $reportCard->student_id)
            ->where('academic_year_id', $reportCard->academic_year_id)
            ->orderByDesc('level')
            ->get();

        $pdf = Pdf::loadView('admin.report_cards.pdf', compact('reportCard', 'subjectScores', 'achievements'));

        $rawFilename = 'Rapor_' . $reportCard->student->full_name . '_' . ($reportCard->semester->semester_name ?? '') . '.pdf';
        $filename = str_replace(['/', '\\'], '-', $rawFilename);

        return $pdf->download($filename);
    }

    /**
     * Bulk download all report cards for homeroom class as ZIP.
     */
    public function bulkDownload(Request $request)
    {
        $teacher = $this->getTeacher();
        $classrooms = $this->authorizeHomeroomTeacher($teacher);
        $classroomIds = $classrooms->pluck('id');

        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        if (!$classroomIds->contains((int) $validated['classroom_id'])) {
            return redirect()->back()->with('error', 'Anda hanya bisa download rapor dari kelas yang Anda ampu.');
        }

        $semester = Semester::with('academicYear')->findOrFail($validated['semester_id']);
        $classroom = Classroom::with('school')->findOrFail($validated['classroom_id']);

        $reportCards = ReportCard::with(['student.school', 'semester.academicYear', 'classroom'])
            ->where('semester_id', $semester->id)
            ->where('classroom_id', $classroom->id)
            ->orderBy('rank')
            ->get();

        if ($reportCards->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada rapor untuk kelas dan semester ini.');
        }

        $zipFilename = 'Rapor_' . $classroom->class_name . '_' . ($semester->semester_name ?? '') . '.zip';
        $tempZipPath = storage_path('app/temp/' . uniqid('rapor_') . '.zip');

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath, \ZipArchive::CREATE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
        }

        try {
            foreach ($reportCards as $reportCard) {
                $subjectScores = $this->calculateSubjectScores($reportCard);

                $achievements = StudentAchievement::where('student_id', $reportCard->student_id)
                    ->where('academic_year_id', $reportCard->academic_year_id)
                    ->orderByDesc('level')
                    ->get();

                $pdf = Pdf::loadView('admin.report_cards.pdf', compact('reportCard', 'subjectScores', 'achievements'));

                $pdfFilename = 'Rapor_' . str_pad($reportCard->rank ?? 0, 2, '0', STR_PAD_LEFT) . '_' . $reportCard->student->full_name . '.pdf';
                $zip->addFromString($pdfFilename, $pdf->output());
            }

            $zip->close();

            return response()->download($tempZipPath, $zipFilename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            if (file_exists($tempZipPath)) {
                unlink($tempZipPath);
            }
            Log::error('Guru bulk download rapor gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat bulk download.');
        }
    }

    // ==================== Helper Methods ====================

    private function calculateSubjectScores(ReportCard $reportCard): array
    {
        $grades = Grade::with(['subject'])
            ->where('student_id', $reportCard->student_id)
            ->where('semester_id', $reportCard->semester_id)
            ->get()
            ->groupBy('subject_id');

        $gradeWeight = GradeWeight::getForSchool($reportCard->student->school_id);
        $w = $gradeWeight->getWeightsAsDecimal();

        if (!$reportCard->relationLoaded('classroom')) {
            $reportCard->load('classroom');
        }
        $gradeLevel = $reportCard->classroom?->grade_level;

        $subjectScores = [];
        foreach ($grades as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject;

            $tugas = $subjectGrades->where('grade_type', 'tugas')->avg('score') ?? 0;
            $uts = $subjectGrades->where('grade_type', 'uts')->avg('score') ?? 0;
            $uas = $subjectGrades->where('grade_type', 'uas')->avg('score') ?? 0;
            $sikap = $subjectGrades->where('grade_type', 'sikap')->avg('score') ?? 0;

            $finalScore = ($tugas * $w['tugas']) + ($uts * $w['pts']) + ($uas * $w['pas']) + ($sikap * $w['sikap']);
            $kkm = $subject->kkm ?? 75;

            $subjectScores[] = [
                'subject' => $subject->subject_name,
                'kkm' => $kkm,
                'tugas' => round($tugas, 0),
                'uts' => round($uts, 0),
                'uas' => round($uas, 0),
                'sikap' => round($sikap, 0),
                'final' => round($finalScore, 0),
                'predicate' => \App\Models\FinalGrade::scoreToPredicate($finalScore, $kkm, $gradeLevel),
                'is_passed' => $finalScore >= $kkm,
            ];
        }

        return $subjectScores;
    }

    private function calculateAttendance($studentId, $semester): array
    {
        $attendances = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$semester->start_date, $semester->end_date])
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'hadir')->count(),
            'sick' => $attendances->where('status', 'sakit')->count(),
            'permission' => $attendances->where('status', 'izin')->count(),
            'absent' => $attendances->where('status', 'alpha')->count(),
        ];
    }

    private function calculateRanks($semesterId, $classroomId = null)
    {
        $query = ReportCard::where('semester_id', $semesterId);
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        $reportCards = $query->orderBy('classroom_id')
            ->orderByDesc('average_score')
            ->get()
            ->groupBy('classroom_id');

        foreach ($reportCards as $cId => $classReports) {
            $rank = 1;
            $total = $classReports->count();
            foreach ($classReports as $report) {
                $report->update(['rank' => $rank, 'total_students' => $total]);
                $rank++;
            }
        }
    }
}
