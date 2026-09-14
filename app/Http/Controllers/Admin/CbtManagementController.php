<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtExam;
use App\Models\CbtExamQuestion;
use App\Models\CbtExamResult;
use App\Models\CbtAnswer;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\CbtQuestionOption;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Exports\CbtQuestionsTemplateExport;
use App\Imports\CbtQuestionsImport;
use App\Services\CbtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class CbtManagementController extends Controller
{
    public function __construct(private CbtService $cbtService) {}

    /**
     * Dashboard CBT Admin
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $schoolId = $isSuperAdmin ? null : $user->school_id;

        $academicYear = AcademicYear::where('is_active', true)->first();

        $query = CbtExam::where('academic_year_id', $academicYear?->id)
            ->with(['subject', 'teacher', 'participants.classroom']);

        // Filter by school if not super admin OR if requested by super admin
        if (!$isSuperAdmin) {
            $query->where('school_id', $schoolId);
        } elseif ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('exam_scope')) {
            $query->where('exam_scope', $request->exam_scope);
        }

        $exams = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Subjects & Teachers filters should also be school-aware
        $subjectsQuery = Subject::orderBy('name');
        $teachersQuery = Teacher::with('user');

        if (!$isSuperAdmin) {
            $subjectsQuery->where('school_id', $schoolId);
            $teachersQuery->where('school_id', $schoolId);
        }

        $subjects = $subjectsQuery->get();
        $teachers = $teachersQuery->orderBy('teacher_code')->get();

        return view('admin.cbt.index', compact('exams', 'subjects', 'teachers', 'isSuperAdmin'));
    }

    /**
     * Detail ujian
     */
    public function show(CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $exam->load(['subject', 'teacher', 'examQuestions.question', 'participants.classroom', 'results.student', 'sessions']);

        $statistics = $this->cbtService->getExamStatistics($exam);

        $pendingEssaysCount = CbtAnswer::whereHas('session', fn($q) => $q->where('exam_id', $exam->id))
            ->whereHas('question', fn($q) => $q->whereIn('question_type', ['essay', 'fill_blank']))
            ->whereNull('manual_score')
            ->count();

        $hasEssayQuestions = $exam->examQuestions->whereNotNull('question')->filter(fn($eq) => in_array($eq->question?->question_type, ['essay', 'fill_blank']))->isNotEmpty();

        $teachers = Teacher::with('user')
            ->where('is_active', true)
            ->where('school_id', $exam->school_id)
            ->orderBy('teacher_code')
            ->get();

        return view('admin.cbt.show', compact('exam', 'statistics', 'pendingEssaysCount', 'hasEssayQuestions', 'teachers'));
    }

    /**
     * Hasil ujian
     */
    public function results(CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $results = CbtExamResult::where('exam_id', $exam->id)
            ->with(['student.classroom', 'session.answers.question'])
            ->orderBy('rank')
            ->paginate(50)->withQueryString();

        $statistics = $this->cbtService->getExamStatistics($exam);

        $pendingEssaysCount = CbtAnswer::whereHas('session', fn($q) => $q->where('exam_id', $exam->id))
            ->whereHas('question', fn($q) => $q->whereIn('question_type', ['essay', 'fill_blank']))
            ->whereNull('manual_score')
            ->count();

        $hasEssayQuestions = $exam->examQuestions->whereNotNull('question')->filter(fn($eq) => in_array($eq->question?->question_type, ['essay', 'fill_blank']))->isNotEmpty();

        return view('admin.cbt.results', compact('exam', 'results', 'statistics', 'pendingEssaysCount', 'hasEssayQuestions'));
    }

    /**
     * Koreksi jawaban esai (Admin)
     */
    public function gradeEssays(CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $answers = CbtAnswer::whereHas('session', fn($q) => $q->where('exam_id', $exam->id))
            ->whereHas('question', fn($q) => $q->whereIn('question_type', ['essay', 'fill_blank']))
            ->with(['session.student.classroom', 'question'])
            ->orderByRaw('CASE WHEN manual_score IS NULL THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->get();

        $examQuestions = CbtExamQuestion::where('exam_id', $exam->id)
            ->get()
            ->keyBy('question_id');

        return view('admin.cbt.grade-essays', compact('exam', 'answers', 'examQuestions'));
    }

    /**
     * Simpan nilai esai (Admin)
     */
    public function gradeEssayStore(Request $request, CbtAnswer $answer)
    {
        $user = Auth::user();
        $answer->load('session.exam');
        $exam = $answer->session->exam;

        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'manual_score' => 'required|numeric|min:0',
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $this->cbtService->gradeEssayAnswer(
            $answer,
            (float)$request->manual_score,
            $request->teacher_feedback,
            $user->id
        );

        return back()->with('success', 'Jawaban berhasil dinilai.');
    }

    /**
     * Bank Soal overview
     */
    public function bankIndex(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $schoolId = $isSuperAdmin ? null : $user->school_id;

        $schools = School::where('is_active', true)->schoolsOnly()->orderBy('name')->get();

        $query = CbtQuestionBank::with(['teacher.user', 'subject', 'questions', 'school']);

        if (!$isSuperAdmin) {
            $query->where('school_id', $schoolId);
        } elseif ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $banks = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $subjectsQuery = Subject::with('school')->orderBy('school_id')->orderBy('name');
        $teachersQuery = Teacher::with(['user', 'school']);
        if (!$isSuperAdmin) {
            $subjectsQuery->where('school_id', $schoolId);
            $teachersQuery->where('school_id', $schoolId);
        }

        $subjects = $subjectsQuery->get();
        $teachers = $teachersQuery->orderBy('teacher_code')->get();

        return view('admin.cbt.banks', compact('banks', 'subjects', 'teachers', 'schools', 'isSuperAdmin', 'schoolId'));
    }

    /**
     * Download template Excel untuk import soal CBT
     */
    public function downloadImportTemplate()
    {
        return Excel::download(new CbtQuestionsTemplateExport(), 'template_soal_cbt.xlsx');
    }

    /**
     * Import bank soal dari file Excel atau ZIP (Excel + gambar)
     */
    public function importBank(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        $request->validate([
            'bank_name'   => 'required|string|max:255',
            'subject_id'  => 'required|exists:subjects,id',
            'grade_level' => 'required|in:7,8,9,10,11,12',
            'teacher_id'  => 'nullable|exists:teachers,id',
            'description' => 'nullable|string',
            'import_file' => 'required|file|max:51200|mimes:xlsx,xls,zip',
        ], [
            'import_file.required' => 'File import wajib diunggah.',
            'import_file.mimes'    => 'Format file harus .xlsx, .xls, atau .zip',
            'import_file.max'      => 'Ukuran file maksimal 50MB.',
            'bank_name.required'   => 'Nama bank soal wajib diisi.',
            'subject_id.required'  => 'Mata pelajaran wajib dipilih.',
            'grade_level.required' => 'Tingkat kelas wajib dipilih.',
        ]);

        $file = $request->file('import_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $excelPath = null;
        $imagesDir = null;
        $tempDir = null;

        try {
            // ========================================
            // 1. Handle ZIP or Excel file
            // ========================================
            if ($extension === 'zip') {
                $tempDir = storage_path('app/temp/cbt_import_' . uniqid());
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $zip = new ZipArchive();
                if ($zip->open($file->getRealPath()) !== true) {
                    return back()->with('error', 'Gagal membuka file ZIP. Pastikan file tidak rusak.')->withInput();
                }
                $zip->extractTo($tempDir);
                $zip->close();

                // Find Excel file in ZIP
                $excelPath = $this->findFileInDir($tempDir, ['xlsx', 'xls']);
                if (!$excelPath) {
                    $this->cleanupTempDir($tempDir);
                    return back()->with('error', 'File ZIP tidak berisi file Excel (.xlsx / .xls).')->withInput();
                }

                // Find images directory in ZIP
                $imagesDir = $this->findImagesDir($tempDir);
            } else {
                // Pass the UploadedFile object directly to retain the original extension and prevent NoTypeDetectedException
                $excelPath = $file;
            }

            // ========================================
            // 2. Read Excel data
            // ========================================
            $import = new CbtQuestionsImport();
            Excel::import($import, $excelPath);
            $rows = $import->getRows();

            if (empty($rows)) {
                if ($tempDir) $this->cleanupTempDir($tempDir);
                return back()->with('error', 'File Excel tidak berisi data soal. Pastikan data diisi di sheet "Soal".')->withInput();
            }

            // ========================================
            // 3. Validate rows
            // ========================================
            $errors = [];
            $validRows = [];
            $validTypes = ['multiple_choice', 'true_false', 'essay', 'fill_blank'];
            $validDifficulties = ['mudah', 'sedang', 'sulit'];

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +2 because of header row and 0-index
                $rowErrors = [];

                // Check required fields
                $question = trim($row['question'] ?? '');
                $type = strtolower(trim($row['question_type'] ?? ''));

                if (empty($question)) {
                    $rowErrors[] = 'Teks soal kosong';
                }
                if (!in_array($type, $validTypes)) {
                    $rowErrors[] = "Tipe soal '{$type}' tidak valid (harus: " . implode(', ', $validTypes) . ')';
                }

                // Validate based on question type
                if ($type === 'multiple_choice') {
                    $optA = trim($row['option_a'] ?? '');
                    $optB = trim($row['option_b'] ?? '');
                    if (empty($optA) || empty($optB)) {
                        $rowErrors[] = 'Pilihan ganda minimal harus memiliki opsi A dan B';
                    }
                    $answer = strtoupper(trim($row['correct_answer'] ?? ''));
                    if (!in_array($answer, ['A', 'B', 'C', 'D', 'E'])) {
                        $rowErrors[] = "Jawaban benar '{$answer}' tidak valid (harus A-E)";
                    }
                }

                if ($type === 'true_false') {
                    $answer = strtolower(trim($row['correct_answer'] ?? ''));
                    if (!in_array($answer, ['true', 'false', 'benar', 'salah', '1', '0'])) {
                        $rowErrors[] = 'Jawaban True/False harus: true/false';
                    }
                }

                // Validate difficulty
                $difficulty = strtolower(trim($row['difficulty'] ?? 'sedang'));
                if (!in_array($difficulty, $validDifficulties)) {
                    $difficulty = 'sedang'; // fallback to default
                }

                // Validate points
                $points = intval($row['points'] ?? 1);
                if ($points < 1) $points = 1;

                // Validate image file exists in ZIP
                $imageFilename = trim($row['image_filename'] ?? '');
                if (!empty($imageFilename) && $imagesDir) {
                    $imagePath = $imagesDir . DIRECTORY_SEPARATOR . $imageFilename;
                    if (!file_exists($imagePath)) {
                        $rowErrors[] = "File gambar '{$imageFilename}' tidak ditemukan di folder images/";
                    }
                }

                if (!empty($rowErrors)) {
                    $errors[] = "Baris {$rowNum}: " . implode('; ', $rowErrors);
                } else {
                    $row['_difficulty'] = $difficulty;
                    $row['_points'] = $points;
                    $row['_type'] = $type;
                    $validRows[] = $row;
                }
            }

            if (empty($validRows)) {
                if ($tempDir) $this->cleanupTempDir($tempDir);
                $errorMsg = 'Tidak ada data soal yang valid. Error: ' . implode(' | ', array_slice($errors, 0, 5));
                return back()->with('error', $errorMsg)->withInput();
            }

            // ========================================
            // 4. Create Question Bank
            // ========================================
            $academicYear = AcademicYear::where('is_active', true)->first();
            $subject = Subject::findOrFail($request->subject_id);
            $schoolId = $subject->school_id; // Always tie to the subject's school

            // Security check for school admin
            if (!$isSuperAdmin && $user->school_id && $schoolId != $user->school_id) {
                if ($tempDir) $this->cleanupTempDir($tempDir);
                return back()->with('error', 'Mata pelajaran tidak sesuai dengan unit sekolah Anda.')->withInput();
            }

            // Resolve teacher: preferred chosen teacher, or teacher teaching this subject, or first teacher in school
            $teacherId = $request->filled('teacher_id') ? $request->teacher_id : null;
            if (!$teacherId) {
                $teacherId = Teacher::where('school_id', $schoolId)
                    ->whereHas('subjects', fn($q) => $q->where('subjects.id', $subject->id))
                    ->first()?->id
                    ?? Teacher::where('school_id', $schoolId)->first()?->id;
            }

            $isShared = $request->has('is_shared') ? $request->boolean('is_shared') : true;

            $bank = CbtQuestionBank::create([
                'school_id'        => $schoolId,
                'subject_id'       => $subject->id,
                'teacher_id'       => $teacherId,
                'academic_year_id' => $academicYear?->id,
                'bank_name'        => $request->bank_name,
                'description'      => $request->description,
                'grade_level'      => $request->grade_level,
                'is_active'        => true,
                'is_shared'        => $isShared,
            ]);

            // ========================================
            // 5. Create Questions & Options
            // ========================================
            $createdCount = 0;
            foreach ($validRows as $row) {
                $type = $row['_type'];

                // Handle image upload from ZIP
                $questionImagePath = null;
                $imageFilename = trim($row['image_filename'] ?? '');
                if (!empty($imageFilename) && $imagesDir) {
                    $sourcePath = $imagesDir . DIRECTORY_SEPARATOR . $imageFilename;
                    if (file_exists($sourcePath)) {
                        $storageName = 'cbt/questions/' . uniqid() . '_' . $imageFilename;
                        Storage::disk('public')->put($storageName, file_get_contents($sourcePath));
                        $questionImagePath = $storageName;
                    }
                }

                // Handle video URL
                $videoUrl = trim($row['video_url'] ?? '');
                $questionVideoPath = null;
                if (!empty($videoUrl) && filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                    $questionVideoPath = $videoUrl;
                }

                // Determine answer_key for essay/fill_blank
                $answerKey = null;
                if (in_array($type, ['essay', 'fill_blank'])) {
                    $answerKey = trim($row['correct_answer'] ?? '');
                }

                $question = CbtQuestion::create([
                    'question_bank_id' => $bank->id,
                    'question_type'    => $type,
                    'question_text'    => trim($row['question']),
                    'question_image'   => $questionImagePath,
                    'question_video'   => $questionVideoPath,
                    'explanation'      => trim($row['explanation'] ?? '') ?: null,
                    'points'           => $row['_points'],
                    'difficulty'       => $row['_difficulty'],
                    'topic'            => trim($row['topic'] ?? '') ?: null,
                    'answer_key'       => $answerKey ?: null,
                    'is_active'        => true,
                ]);

                // Create options based on question type
                if ($type === 'multiple_choice') {
                    $correctAnswer = strtoupper(trim($row['correct_answer'] ?? ''));
                    $labels = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'];
                    $sortOrder = 1;

                    foreach ($labels as $label => $col) {
                        $optText = trim($row[$col] ?? '');
                        if (!empty($optText)) {
                            CbtQuestionOption::create([
                                'question_id'  => $question->id,
                                'option_label' => $label,
                                'option_text'  => $optText,
                                'is_correct'   => ($label === $correctAnswer),
                                'sort_order'   => $sortOrder++,
                            ]);
                        }
                    }
                } elseif ($type === 'true_false') {
                    $answer = strtolower(trim($row['correct_answer'] ?? ''));
                    $isTrue = in_array($answer, ['true', 'benar', '1']);

                    CbtQuestionOption::create([
                        'question_id'  => $question->id,
                        'option_label' => 'A',
                        'option_text'  => 'Benar',
                        'is_correct'   => $isTrue,
                        'sort_order'   => 1,
                    ]);
                    CbtQuestionOption::create([
                        'question_id'  => $question->id,
                        'option_label' => 'B',
                        'option_text'  => 'Salah',
                        'is_correct'   => !$isTrue,
                        'sort_order'   => 2,
                    ]);
                }

                $createdCount++;
            }

            // Update counter
            $bank->update(['total_questions' => $createdCount]);

            // Cleanup temp directory
            if ($tempDir) $this->cleanupTempDir($tempDir);

            // Build success message
            $successMsg = "Bank soal \"{$bank->bank_name}\" berhasil dibuat dengan {$createdCount} soal.";
            if (!empty($errors)) {
                $skipped = count($errors);
                $successMsg .= " ({$skipped} baris dilewati karena error)";
            }

            return redirect()->route('admin.cbt.banks')
                ->with('success', $successMsg)
                ->with('import_warnings', $errors);

        } catch (\Exception $e) {
            if ($tempDir) $this->cleanupTempDir($tempDir);
            return back()->with('error', 'Gagal import: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle status is_shared pada bank soal (Admin)
     */
    public function toggleBankShare(CbtQuestionBank $bank)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $bank->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $bank->update(['is_shared' => !$bank->is_shared]);

        $statusText = $bank->is_shared ? 'dibagikan ke semua guru di unit sekolah ini' : 'diatur sebagai privat';
        return back()->with('success', "Status pembagian bank soal \"{$bank->bank_name}\" berhasil diubah: {$statusText}.");
    }

    /**
     * Hapus bank soal (Admin)
     */
    public function bankDestroy(CbtQuestionBank $bank)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $bank->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $bankName = $bank->bank_name;
        $bank->delete();

        return back()->with('success', "Bank soal \"{$bankName}\" berhasil dihapus.");
    }

    /**
     * Find first file with given extensions in a directory (recursive)
     */
    private function findFileInDir(string $dir, array $extensions): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array(strtolower($file->getExtension()), $extensions)) {
                return $file->getRealPath();
            }
        }
        return null;
    }

    /**
     * Find images directory in extracted ZIP
     */
    private function findImagesDir(string $baseDir): ?string
    {
        // Look for 'images' folder at any level
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir() && strtolower($item->getFilename()) === 'images') {
                return $item->getRealPath();
            }
        }
        // If no images folder found, check for image files directly in the base dir
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        foreach (scandir($baseDir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $imageExts)) {
                return $baseDir; // Images are directly in the root of ZIP
            }
        }
        return null;
    }

    /**
     * Cleanup temporary directory
     */
    private function cleanupTempDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }
        rmdir($dir);
    }

    /**
     * Laporan CBT (cached for 5 minutes)
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $schoolId = $isSuperAdmin ? null : $user->school_id;

        $academicYear = AcademicYear::where('is_active', true)->first();
        $cacheKey = 'cbt_report_' . ($isSuperAdmin ? 'all' : $schoolId) . '_' . ($academicYear?->id ?? 'none');

        $reportData = Cache::remember($cacheKey, 300, function () use ($academicYear, $isSuperAdmin, $schoolId) {
            $examQuery = CbtExam::where('academic_year_id', $academicYear?->id);
            if (!$isSuperAdmin) $examQuery->where('school_id', $schoolId);

            $examStats = $examQuery->selectRaw("
                    COUNT(*) as total_exams,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
                ")
                ->first();

            $resultQuery = CbtExamResult::whereHas('exam', function($q) use ($academicYear, $isSuperAdmin, $schoolId) {
                $q->where('academic_year_id', $academicYear?->id);
                if (!$isSuperAdmin) $q->where('school_id', $schoolId);
            });

            $resultStats = $resultQuery->selectRaw("
                    COUNT(*) as total_results,
                    AVG(final_score) as avg_score,
                    MAX(final_score) as max_score,
                    MIN(final_score) as min_score,
                    SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed,
                    SUM(CASE WHEN is_passed = 0 THEN 1 ELSE 0 END) as failed
                ")
                ->first();

            $subjectPerformance = CbtExamResult::whereHas('exam', function($q) use ($academicYear, $isSuperAdmin, $schoolId) {
                    $q->where('academic_year_id', $academicYear?->id);
                    if (!$isSuperAdmin) $q->where('school_id', $schoolId);
                })
                ->join('cbt_exams', 'cbt_exam_results.exam_id', '=', 'cbt_exams.id')
                ->join('subjects', 'cbt_exams.subject_id', '=', 'subjects.id')
                ->selectRaw('subjects.subject_name as subject_name, AVG(final_score) as avg_score, COUNT(*) as total_taken')
                ->groupBy('subjects.id', 'subjects.subject_name')
                ->orderByDesc('avg_score')
                ->get();

            $classroomPerformance = CbtExamResult::whereHas('exam', function($q) use ($academicYear, $isSuperAdmin, $schoolId) {
                    $q->where('academic_year_id', $academicYear?->id);
                    if (!$isSuperAdmin) $q->where('school_id', $schoolId);
                })
                ->join('students', 'cbt_exam_results.student_id', '=', 'students.id')
                ->join('student_classes', function($join) {
                    $join->on('students.id', '=', 'student_classes.student_id')
                         ->where('student_classes.status', '=', 'aktif');
                })
                ->join('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
                ->selectRaw('classrooms.class_name as classroom_name, AVG(final_score) as avg_score, COUNT(*) as total_taken')
                ->groupBy('classrooms.id', 'classrooms.class_name')
                ->orderByDesc('avg_score')
                ->get();

            return compact('examStats', 'resultStats', 'subjectPerformance', 'classroomPerformance');
        });

        return view('admin.cbt.report', $reportData);
    }

    // =====================================================
    // ADMIN EXAM CRUD (School-scope exams)
    // =====================================================

    /**
     * Form buat ujian sekolah (UAS, UTS, Test Masuk, Ujian Khusus)
     */
    public function examCreate()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $userSchoolId = $isSuperAdmin ? null : $user->school_id;

        $schools = School::where('is_active', true)->schoolsOnly()->orderBy('name')->get();

        // Subjects — all for super admin, filtered for admin sekolah
        $subjectsQuery = Subject::orderBy('name');
        if ($userSchoolId) {
            $subjectsQuery->where('school_id', $userSchoolId);
        }
        $subjects = $subjectsQuery->get();

        // Banks — all for super admin, filtered for admin sekolah
        $banksQuery = CbtQuestionBank::where('is_active', true)
            ->with(['teacher.user', 'subject'])
            ->orderBy('subject_id');
        if ($userSchoolId) {
            $banksQuery->where('school_id', $userSchoolId);
        }
        $banks = $banksQuery->get();

        $academicYear = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();

        // Classrooms — all for super admin, filtered for admin sekolah and active academic year
        $classroomsQuery = Classroom::where('is_active', true)
            ->when($academicYear, function($q) use ($academicYear) {
                return $q->where('academic_year_id', $academicYear->id);
            })
            ->with('school')
            ->orderBy('school_id')
            ->orderBy('grade_level')
            ->orderBy('class_name');
        if ($userSchoolId) {
            $classroomsQuery->where('school_id', $userSchoolId);
        }
        $classrooms = $classroomsQuery->get();

        // Teachers — all active teachers in school with their subject teaching assignments & competencies
        $teachersQuery = Teacher::with(['user', 'employee', 'teachingAssignments' => function($q) use ($academicYear) {
            $q->where('is_active', true)->when($academicYear, fn($sq) => $sq->where('academic_year_id', $academicYear->id));
        }, 'competentSubjects', 'schedules'])
            ->where('is_active', true)
            ->orderBy('teacher_code');

        if ($userSchoolId) {
            $teachersQuery->where('school_id', $userSchoolId);
        }
        $teachers = $teachersQuery->get();

        $examTypes = CbtExam::getSchoolScopeTypes();

        // Pre-map data to JSON-safe arrays for Alpine.js
        $subjectsJson = $subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->subject_name, 'school_id' => $s->school_id])->values();
        $teachersJson = $teachers->map(function ($t) {
            $taSubjectIds = $t->teachingAssignments->pluck('subject_id');
            $schedSubjectIds = $t->schedules->pluck('subject_id');
            $compSubjectIds = $t->competentSubjects->pluck('id');
            $subjectIds = $taSubjectIds->merge($schedSubjectIds)->merge($compSubjectIds)->unique()->filter()->values()->toArray();

            return [
                'id' => $t->id,
                'name' => $t->full_name ?: ($t->user?->name ?? 'Guru'),
                'code' => $t->teacher_code ?? '',
                'school_id' => $t->school_id,
                'subject_ids' => $subjectIds,
            ];
        })->values();
        $banksJson = $banks->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->bank_name,
            'total' => $b->total_questions,
            'grade' => $b->grade_level,
            'school_id' => $b->school_id,
            'subject_id' => $b->subject_id,
            'teacher_name' => $b->teacher?->user?->name ?? 'Tanpa Guru',
            'subject_name' => $b->subject?->subject_name ?? '-',
        ])->values();
        $classroomsJson = $classrooms->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->class_name,
            'grade' => $c->grade_level,
            'school_id' => $c->school_id,
        ])->values();

        return view('admin.cbt.exams.create', compact(
            'schools', 'subjects', 'teachers', 'banks', 'classrooms',
            'academicYear', 'semester', 'examTypes',
            'isSuperAdmin', 'userSchoolId',
            'subjectsJson', 'teachersJson', 'banksJson', 'classroomsJson'
        ));
    }

    /**
     * Store ujian sekolah
     */
    public function examStore(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        // Security check for school admin
        if (!$isSuperAdmin && $request->school_id != $user->school_id) {
            return back()->with('error', 'Anda tidak memiliki akses ke sekolah ini.')->withInput();
        }

        $allowedTypes = implode(',', CbtExam::SCHOOL_SCOPE_TYPES);

        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'exam_title' => 'required|string|max:255',
            'exam_description' => 'nullable|string',
            'exam_type' => "required|in:{$allowedTypes}",
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'duration_minutes' => 'required|integer|min:5',
            'passing_score' => 'required|numeric|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_result' => 'boolean',
            'show_answer_key' => 'boolean',
            'allow_review' => 'boolean',
            'prevent_tab_switch' => 'boolean',
            'prevent_copy_paste' => 'boolean',
            'auto_sync_grade' => 'boolean',
            'access_code' => 'nullable|string|max:20',
            'question_banks' => 'required|array|min:1',
            'question_banks.*.bank_id' => 'required|exists:cbt_question_banks,id',
            'question_banks.*.questions_to_pick' => 'required|integer|min:1',
            'classrooms' => 'required|array|min:1',
            'classrooms.*' => 'exists:classrooms,id',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->firstOrFail();
        $semester = Semester::where('is_active', true)->firstOrFail();

        $exam = CbtExam::create(array_merge($validated, [
            'exam_scope' => 'school',
            'teacher_id' => $request->filled('teacher_id') ? $request->teacher_id : null,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'total_questions_shown' => collect($validated['question_banks'])->sum('questions_to_pick'),
            'created_by' => Auth::id(),
            'status' => 'draft',
        ]));

        // Attach question banks
        foreach ($validated['question_banks'] as $bankData) {
            $exam->questionBanks()->attach($bankData['bank_id'], [
                'questions_to_pick' => $bankData['questions_to_pick'],
            ]);
        }

        // Attach classrooms as participants
        foreach ($validated['classrooms'] as $classroomId) {
            $exam->participants()->create(['classroom_id' => $classroomId]);
        }

        // Prepare questions from banks
        $this->cbtService->prepareExamQuestions($exam);

        return redirect()->route('admin.cbt.show', $exam)
            ->with('success', 'Ujian sekolah berhasil dibuat sebagai draft. Silakan publikasikan jika sudah siap.');
    }

    /**
     * Form edit ujian sekolah (status draft, published, atau active)
     */
    public function examEdit(CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        // Ujian yang sudah selesai / arsip tidak dapat diedit
        if (!in_array($exam->status, ['draft', 'published', 'active'])) {
            return redirect()->route('admin.cbt.show', $exam)
                ->with('error', 'Ujian yang sudah selesai tidak dapat diedit.');
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $userSchoolId = $isSuperAdmin ? null : $user->school_id;

        $schools = School::where('is_active', true)->schoolsOnly()->orderBy('name')->get();

        $subjectsQuery = Subject::orderBy('name');
        if ($userSchoolId) {
            $subjectsQuery->where('school_id', $userSchoolId);
        }
        $subjects = $subjectsQuery->get();

        $academicYear = AcademicYear::where('is_active', true)->first();

        $classroomsQuery = Classroom::where('is_active', true)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->with('school')
            ->orderBy('school_id')
            ->orderBy('grade_level')
            ->orderBy('class_name');
        if ($userSchoolId) {
            $classroomsQuery->where('school_id', $userSchoolId);
        }
        $classrooms = $classroomsQuery->get();

        $teachersQuery = Teacher::with(['user', 'employee'])->where('is_active', true)->orderBy('teacher_code');
        if ($userSchoolId) {
            $teachersQuery->where('school_id', $userSchoolId);
        }
        $teachers = $teachersQuery->get();

        $examTypes = CbtExam::getSchoolScopeTypes();

        // Data existing untuk pre-populate
        $exam->load(['participants.classroom', 'questionBanks']);
        $selectedClassroomIds = $exam->participants->pluck('classroom_id')->toArray();

        $subjectsJson = $subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->subject_name, 'school_id' => $s->school_id])->values();
        $teachersJson = $teachers->map(fn($t) => [
            'id'         => $t->id,
            'name'       => $t->full_name ?: ($t->user?->name ?? 'Guru'),
            'code'       => $t->teacher_code ?? '',
            'school_id'  => $t->school_id,
            'subject_ids' => [],
        ])->values();
        $classroomsJson = $classrooms->map(fn($c) => [
            'id'        => $c->id,
            'name'      => $c->class_name,
            'grade'     => $c->grade_level,
            'school_id' => $c->school_id,
        ])->values();

        return view('admin.cbt.exams.edit', compact(
            'exam', 'schools', 'subjects', 'teachers', 'classrooms',
            'academicYear', 'examTypes',
            'isSuperAdmin', 'userSchoolId',
            'subjectsJson', 'teachersJson', 'classroomsJson',
            'selectedClassroomIds'
        ));
    }

    /**
     * Update ujian sekolah (status draft, published, atau active)
     */
    public function examUpdate(Request $request, CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        // Ujian yang sudah selesai / arsip tidak dapat diedit
        if (!in_array($exam->status, ['draft', 'published', 'active'])) {
            return redirect()->route('admin.cbt.show', $exam)
                ->with('error', 'Ujian yang sudah selesai tidak dapat diedit.');
        }

        $allowedTypes = implode(',', CbtExam::SCHOOL_SCOPE_TYPES);

        $validated = $request->validate([
            'exam_title'          => 'required|string|max:255',
            'exam_description'    => 'nullable|string',
            'exam_type'           => "required|in:{$allowedTypes}",
            'status'              => 'nullable|in:draft,published,active',
            'start_time'          => 'nullable|date',
            'end_time'            => 'nullable|date|after:start_time',
            'duration_minutes'    => 'required|integer|min:5',
            'passing_score'       => 'required|numeric|min:0|max:100',
            'max_attempts'        => 'required|integer|min:1',
            'randomize_questions' => 'boolean',
            'randomize_options'   => 'boolean',
            'show_result'         => 'boolean',
            'show_answer_key'     => 'boolean',
            'allow_review'        => 'boolean',
            'prevent_tab_switch'  => 'boolean',
            'prevent_copy_paste'  => 'boolean',
            'auto_sync_grade'     => 'boolean',
            'access_code'         => 'nullable|string|max:20',
            'classrooms'          => 'required|array|min:1',
            'classrooms.*'        => 'exists:classrooms,id',
        ]);

        // Update data ujian
        $exam->update([
            'exam_title'          => $validated['exam_title'],
            'exam_description'    => $validated['exam_description'] ?? null,
            'exam_type'           => $validated['exam_type'],
            'status'              => $validated['status'] ?? $exam->status,
            'start_time'          => $validated['start_time'] ?? null,
            'end_time'            => $validated['end_time'] ?? null,
            'duration_minutes'    => $validated['duration_minutes'],
            'passing_score'       => $validated['passing_score'],
            'max_attempts'        => $validated['max_attempts'],
            'randomize_questions' => $request->boolean('randomize_questions'),
            'randomize_options'   => $request->boolean('randomize_options'),
            'show_result'         => $request->boolean('show_result'),
            'show_answer_key'     => $request->boolean('show_answer_key'),
            'allow_review'        => $request->boolean('allow_review'),
            'prevent_tab_switch'  => $request->boolean('prevent_tab_switch'),
            'prevent_copy_paste'  => $request->boolean('prevent_copy_paste'),
            'auto_sync_grade'     => $request->boolean('auto_sync_grade'),
            'access_code'         => $request->filled('access_code') ? $request->access_code : null,
        ]);

        // Sync kelas peserta secara aman
        $existingClassIds = $exam->participants()->pluck('classroom_id')->toArray();
        $toAdd = array_diff($validated['classrooms'], $existingClassIds);
        $toRemove = array_diff($existingClassIds, $validated['classrooms']);

        if (!empty($toRemove)) {
            $exam->participants()->whereIn('classroom_id', $toRemove)->delete();
        }
        foreach ($toAdd as $classroomId) {
            $exam->participants()->create(['classroom_id' => $classroomId]);
        }

        return redirect()->route('admin.cbt.show', $exam)
            ->with('success', 'Data ujian berhasil diperbarui.');
    }

    /**
     * Tetapkan / Ganti Guru Pengampu & Penilai Ujian CBT (Admin)
     */
    public function assignTeacher(Request $request, CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $exam->update([
            'teacher_id' => $request->filled('teacher_id') ? $request->teacher_id : null,
        ]);

        $teacherName = $exam->fresh()->teacher?->full_name ?? 'Tanpa Guru Pengampu';
        return back()->with('success', "Guru pengampu/penilai ujian berhasil diperbarui: {$teacherName}.");
    }

    /**
     * Publish ujian sekolah
     */
    public function examPublish(CbtExam $exam)
    {
        $exam->update(['status' => 'published']);
        return back()->with('success', 'Ujian berhasil diterbitkan.');
    }

    /**
     * Activate ujian sekolah
     */
    public function examActivate(CbtExam $exam)
    {
        $exam->update(['status' => 'active']);
        return back()->with('success', 'Ujian berhasil diaktifkan.');
    }

    public function examPause(CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $this->cbtService->pauseExam($exam);
        return back()->with('success', 'Ujian berhasil ditangguhkan/dijeda.');
    }

    public function examResume(CbtExam $exam)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $exam->school_id != $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $this->cbtService->resumeExam($exam);
        return back()->with('success', 'Ujian berhasil dilanjutkan kembali.');
    }

    /**
     * Batch start all participant sessions
     */
    public function examBatchStart(CbtExam $exam)
    {
        if ($exam->status !== 'active') {
            return back()->with('error', 'Ujian harus diaktifkan terlebih dahulu.');
        }

        $result = $this->cbtService->batchStartSessions($exam);

        $msg = "Sesi ujian dibuat: {$result['created']} siswa. ";
        if ($result['skipped'] > 0) {
            $msg .= "Dilewati (sudah ada sesi): {$result['skipped']}. ";
        }
        if (!empty($result['errors'])) {
            $msg .= "Error: " . count($result['errors']) . " siswa.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Force complete / archive an exam
     */
    public function forceComplete(CbtExam $exam)
    {
        $exam->update(['status' => 'completed']);
        $this->cbtService->calculateRankings($exam);

        return back()->with('success', 'Ujian ditutup paksa dan ranking dihitung.');
    }

    /**
     * Sync all results of an exam to grades
     */
    public function syncGrades(CbtExam $exam)
    {
        $synced = $this->cbtService->syncExamResults($exam);
        return back()->with('success', "{$synced} hasil ujian berhasil disinkronkan ke nilai.");
    }
}
