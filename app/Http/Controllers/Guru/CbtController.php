<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cbt\StoreCbtQuestionRequest;
use App\Http\Requests\Cbt\UpdateCbtQuestionRequest;
use App\Http\Requests\Cbt\StoreCbtExamRequest;
use App\Http\Requests\Cbt\GradeEssayRequest;
use App\Models\CbtExam;
use App\Models\CbtExamQuestion;
use App\Models\CbtExamResult;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\CbtQuestionOption;
use App\Models\CbtAnswer;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\School;
use App\Services\CbtService;
use App\Exports\CbtQuestionsTemplateExport;
use App\Exports\CbtExamParticipationExport;
use App\Imports\CbtQuestionsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class CbtController extends Controller
{
    use HasMultiSchool;

    public function __construct(private CbtService $cbtService) {}

    /**
     * Resolve the authenticated teacher record (DRY helper, multi-school aware)
     */
    private function resolveTeacher(): Teacher
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // 1. Coba ambil teacher berdasarkan active_school_id sesi saat ini
        $sessionSchoolId = session('active_school_id');
        if ($sessionSchoolId) {
            $teacher = Teacher::where('user_id', $user->id)
                ->where('school_id', $sessionSchoolId)
                ->first();
            if ($teacher) {
                return $teacher;
            }
        }

        // 2. Coba ambil teacher berdasarkan user_id
        $teacher = Teacher::where('user_id', $user->id)->first();
        if ($teacher) {
            return $teacher;
        }

        // 3. Coba ambil via relasi employee
        $employee = $user->employee ?? \App\Models\Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $teacher = Teacher::where('employee_id', $employee->id)->first();
            if ($teacher) {
                return $teacher;
            }
        }

        // 4. Fallback ke relasi $user->teacher
        if ($user->teacher) {
            return $user->teacher;
        }

        abort(403, 'Data profil guru tidak ditemukan.');
    }

    /**
     * Mendapatkan unit sekolah efektif yang sedang aktif dipilih guru
     */
    private function getEffectiveSchool(?Teacher $teacher = null): School
    {
        $schoolId = $this->getEffectiveSchoolId($teacher);
        $school = School::find($schoolId);
        if ($school) {
            return $school;
        }
        if ($teacher && $teacher->school) {
            return $teacher->school;
        }
        return School::schoolsOnly()->firstOrFail();
    }

    /**
     * Helper seluruh ID Teacher yang dimiliki oleh pengguna saat ini
     */
    private function getAllTeacherIds(?Teacher $teacher = null): array
    {
        $user = Auth::user();
        $ids = $user ? $user->teacherIds() : [];
        if (empty($ids) && $teacher) {
            $ids = $teacher->allTeacherIds();
        }
        if (empty($ids) && $teacher) {
            $ids = [$teacher->id];
        }
        return array_unique(array_filter($ids));
    }

    /**
     * Verify the teacher has access to a question bank
     */
    private function authorizeBank(CbtQuestionBank $bank, ?Teacher $teacher = null): Teacher
    {
        $teacher = $teacher ?? $this->resolveTeacher();

        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah'])) 
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        $tIds = $this->getAllTeacherIds($teacher);
        $isOwner = in_array($bank->teacher_id, $tIds);

        $accessibleSchoolIds = $user ? $user->getAvailableSchools()->pluck('id')->toArray() : [$teacher->school_id];
        $isSchoolMatch = $bank->school_id && in_array((int) $bank->school_id, array_map('intval', $accessibleSchoolIds));
        $isShared = (bool) $bank->is_shared;

        abort_unless($isOwner || $isShared || $isSchoolMatch || $isAdmin, 403, 'Anda tidak memiliki akses ke bank soal ini.');
        return $teacher;
    }

    /**
     * Get all subject IDs taught or competent by a teacher (optionally filtered by school)
     */
    private function getTeacherSubjectIds(?Teacher $teacher = null, ?int $schoolId = null): array
    {
        $teacher = $teacher ?? $this->resolveTeacher();
        $tIds = $this->getAllTeacherIds($teacher);

        $taQuery = \App\Models\TeachingAssignment::whereIn('teacher_id', $tIds)
            ->where('is_active', true);
        if ($schoolId) {
            $taQuery->where(function($q) use ($schoolId) {
                $q->whereHas('classroom', fn($cq) => $cq->where('school_id', $schoolId))
                  ->orWhereHas('subject', fn($sq) => $sq->where('school_id', $schoolId));
            });
        }
        $taSubjectIds = $taQuery->pluck('subject_id');

        $scheduleQuery = \App\Models\Schedule::whereIn('teacher_id', $tIds);
        if ($schoolId) {
            $scheduleQuery->whereHas('subject', fn($sq) => $sq->where('school_id', $schoolId));
        }
        $scheduleSubjectIds = $scheduleQuery->pluck('subject_id');

        $competentQuery = \Illuminate\Support\Facades\DB::table('subject_teacher')
            ->whereIn('teacher_id', $tIds);
        if ($schoolId) {
            $competentQuery->join('subjects', 'subjects.id', '=', 'subject_teacher.subject_id')
                ->where('subjects.school_id', $schoolId);
        }
        $competentSubjectIds = $competentQuery->pluck('subject_teacher.subject_id');

        $legacyQuery = \Illuminate\Support\Facades\DB::table('schedules')
            ->whereIn('teacher_id', $tIds);
        if ($schoolId) {
            $legacyQuery->join('subjects', 'subjects.id', '=', 'schedules.subject_id')
                ->where('subjects.school_id', $schoolId);
        }
        $legacySubjectIds = $legacyQuery->pluck('schedules.subject_id');

        return $taSubjectIds
            ->merge($scheduleSubjectIds)
            ->merge($competentSubjectIds)
            ->merge($legacySubjectIds)
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Ambil koleksi mapel aktif untuk unit sekolah tertentu yang relevan dengan guru
     */
    private function getSubjectsForSchool(Teacher $teacher, School $school): \Illuminate\Support\Collection
    {
        $taughtSubjectIds = $this->getTeacherSubjectIds($teacher, $school->id);

        $subjects = Subject::where('school_id', $school->id)
            ->where('is_active', true)
            ->when(!empty($taughtSubjectIds), fn($q) => $q->whereIn('id', $taughtSubjectIds))
            ->orderBy('name')
            ->get();

        // Fallback jika belum terdaftar penugasan langsung di unit ini:
        // Cari mapel di unit ini yang namanya cocok dengan mapel kompetensi guru di unit lain
        if ($subjects->isEmpty()) {
            $allTaughtSubjectIds = $this->getTeacherSubjectIds($teacher);
            $taughtSubjects = Subject::whereIn('id', $allTaughtSubjectIds)->get();
            $taughtNames = $taughtSubjects->map(fn($s) => trim($s->name ?: $s->subject_name))->filter()->unique();

            if ($taughtNames->isNotEmpty()) {
                $subjects = Subject::where('school_id', $school->id)
                    ->where('is_active', true)
                    ->where(function ($q) use ($taughtNames) {
                        foreach ($taughtNames as $name) {
                            $q->orWhere('name', 'like', "%{$name}%")
                              ->orWhere('subject_name', 'like', "%{$name}%");
                        }
                    })
                    ->orderBy('name')
                    ->get();
            }
        }

        // Fallback terakhir: jika tetap kosong, tampilkan semua mapel aktif di unit tersebut
        if ($subjects->isEmpty()) {
            $subjects = Subject::where('school_id', $school->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return $subjects;
    }

    /**
     * Check if a teacher teaches or is competent in a subject
     */
    private function teacherTeachesSubject(Teacher $teacher, ?int $subjectId): bool
    {
        if (!$subjectId) {
            return false;
        }

        $teacherSubjectIds = $this->getTeacherSubjectIds($teacher);

        // Fallback: Jika guru belum disetting mapel sama sekali di sistem,
        // berikan akses jika ujian berada di sekolah yang sama
        if (empty($teacherSubjectIds)) {
            return true;
        }

        if (in_array($subjectId, $teacherSubjectIds)) {
            return true;
        }

        // Cek jika ada mapel dengan nama yang sama yang diajar guru di unit lain
        $targetSubject = Subject::find($subjectId);
        if ($targetSubject) {
            $targetName = strtolower(trim($targetSubject->name ?: $targetSubject->subject_name));
            $taughtNames = Subject::whereIn('id', $teacherSubjectIds)
                ->get()
                ->map(fn($s) => strtolower(trim($s->name ?: $s->subject_name)))
                ->toArray();
            if (in_array($targetName, $taughtNames)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify the teacher has access to an exam (owned class exam OR school exam for their subject)
     */
    private function authorizeExam(CbtExam $exam, ?Teacher $teacher = null): Teacher
    {
        $teacher = $teacher ?? $this->resolveTeacher();

        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah'])) 
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        $tIds = $this->getAllTeacherIds($teacher);
        $isOwner = in_array($exam->teacher_id, $tIds);

        $accessibleSchoolIds = $user ? $user->getAvailableSchools()->pluck('id')->toArray() : [$teacher->school_id];
        $isSchoolMatch = $exam->school_id && in_array((int) $exam->school_id, array_map('intval', $accessibleSchoolIds));
        $teachesSubject = $this->teacherTeachesSubject($teacher, $exam->subject_id);

        $canAccess = $isAdmin || $isOwner || ($isSchoolMatch && $teachesSubject);

        abort_unless($canAccess, 403, 'Anda tidak memiliki akses ke ujian mata pelajaran ini.');
        return $teacher;
    }

    // ==========================================
    // QUESTION BANKS
    // ==========================================

    public function bankIndex()
    {
        $teacher = $this->resolveTeacher();
        $school = $this->getEffectiveSchool($teacher);
        $tIds = $this->getAllTeacherIds($teacher);

        $banks = CbtQuestionBank::where(function ($q) use ($tIds, $school) {
                $q->where(function ($sub) use ($tIds, $school) {
                    $sub->whereIn('teacher_id', $tIds)
                        ->where(function($sq) use ($school) {
                            $sq->where('school_id', $school->id)->orWhereNull('school_id');
                        });
                })->orWhere(function ($sq) use ($school) {
                    $sq->where('is_shared', true)
                       ->where('school_id', $school->id);
                });
            })
            ->with('subject', 'questions', 'school')
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $subjects = $this->getSubjectsForSchool($teacher, $school);
        $gradeLevels = $school->getGradeLevels();

        return view('guru.cbt.banks.index', compact('banks', 'subjects', 'gradeLevels', 'school'));
    }

    public function bankCreate()
    {
        $teacher = $this->resolveTeacher();
        $school = $this->getEffectiveSchool($teacher);

        $subjects = $this->getSubjectsForSchool($teacher, $school);
        $gradeLevels = $school->getGradeLevels();
        $academicYear = AcademicYear::where('is_active', true)->first();

        return view('guru.cbt.banks.create', compact('subjects', 'academicYear', 'teacher', 'gradeLevels', 'school'));
    }

    public function bankStore(Request $request)
    {
        $teacher = $this->resolveTeacher();
        $school = $this->getEffectiveSchool($teacher);

        $allowedGrades = $school->getGradeLevelsRule();

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'bank_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_level' => "required|in:{$allowedGrades}",
            'is_shared' => 'boolean',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->firstOrFail();

        CbtQuestionBank::create(array_merge($validated, [
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
            'academic_year_id' => $academicYear->id,
        ]));

        return redirect()->route('guru.cbt.banks.index')
            ->with('success', 'Bank soal berhasil dibuat.');
    }

    /**
     * Download template Excel untuk import soal CBT (Guru)
     */
    public function downloadImportTemplate()
    {
        return Excel::download(new CbtQuestionsTemplateExport(), 'template_soal_cbt_guru.xlsx');
    }

    /**
     * Import bank soal baru beserta soalnya dari file Excel/ZIP (Guru)
     */
    public function importBank(Request $request)
    {
        $teacher = $this->resolveTeacher();
        $school = $this->getEffectiveSchool($teacher);
        $allowedGrades = $school->getGradeLevelsRule();

        $request->validate([
            'bank_name'   => 'required|string|max:255',
            'subject_id'  => 'required|exists:subjects,id',
            'grade_level' => "required|in:{$allowedGrades}",
            'description' => 'nullable|string',
            'is_shared'   => 'boolean',
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
            if ($extension === 'zip') {
                $tempDir = storage_path('app/temp/guru_cbt_import_' . uniqid());
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $zip = new ZipArchive();
                if ($zip->open($file->getRealPath()) !== true) {
                    return back()->with('error', 'Gagal membuka file ZIP. Pastikan file tidak rusak.')->withInput();
                }
                $zip->extractTo($tempDir);
                $zip->close();

                $excelPath = $this->findFileInDir($tempDir, ['xlsx', 'xls']);
                if (!$excelPath) {
                    $this->cleanupTempDir($tempDir);
                    return back()->with('error', 'File ZIP tidak berisi file Excel (.xlsx / .xls).')->withInput();
                }

                $imagesDir = $this->findImagesDir($tempDir);
            } else {
                $excelPath = $file;
            }

            $import = new CbtQuestionsImport();
            Excel::import($import, $excelPath);
            $rows = $import->getRows();

            if (empty($rows)) {
                if ($tempDir) $this->cleanupTempDir($tempDir);
                return back()->with('error', 'File Excel tidak berisi data soal. Pastikan data diisi di sheet "Soal".')->withInput();
            }

            [$validRows, $errors] = $this->validateImportRows($rows, $imagesDir);

            if (empty($validRows)) {
                if ($tempDir) $this->cleanupTempDir($tempDir);
                $errorMsg = 'Tidak ada data soal yang valid. Error: ' . implode(' | ', array_slice($errors, 0, 5));
                return back()->with('error', $errorMsg)->withInput();
            }

            $academicYear = AcademicYear::where('is_active', true)->firstOrFail();

            $bank = CbtQuestionBank::create([
                'school_id'        => $school->id,
                'subject_id'       => $request->subject_id,
                'teacher_id'       => $teacher->id,
                'academic_year_id' => $academicYear->id,
                'bank_name'        => $request->bank_name,
                'description'      => $request->description,
                'grade_level'      => $request->grade_level,
                'is_shared'        => $request->boolean('is_shared'),
                'is_active'        => true,
            ]);

            $createdCount = $this->processImportedQuestions($validRows, $bank, $imagesDir);

            if ($tempDir) $this->cleanupTempDir($tempDir);

            $msg = "Bank soal '{$bank->bank_name}' berhasil dibuat dan diimpor {$createdCount} soal.";
            if (!empty($errors)) {
                session()->flash('import_warnings', array_slice($errors, 0, 10));
            }

            return redirect()->route('guru.cbt.banks.show', $bank)->with('success', $msg);

        } catch (\Exception $e) {
            if ($tempDir) $this->cleanupTempDir($tempDir);
            return back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage())->withInput();
        }
    }

    public function bankShow(CbtQuestionBank $bank)
    {
        $teacher = $this->authorizeBank($bank);
        $bank->load(['questions.options', 'subject', 'school']);

        $tIds = $this->getAllTeacherIds($teacher);
        $courses = \App\Models\LmsCourse::whereIn('teacher_id', $tIds)
            ->orWhere(fn($q) => $q->whereNull('teacher_id'))
            ->latest()
            ->get();

        if ($courses->isEmpty()) {
            $courses = \App\Models\LmsCourse::latest()->take(20)->get();
        }

        return view('guru.cbt.banks.show', compact('bank', 'courses'));
    }

    /**
     * Tautkan Bank Soal CBT ini secara langsung menjadi Kuis di Kelas LMS
     */
    public function assignToLms(Request $request, CbtQuestionBank $bank)
    {
        $teacher = $this->authorizeBank($bank);

        $request->validate([
            'course_id' => 'required|exists:lms_courses,id',
            'title' => 'required|string|max:255',
            'time_limit' => 'required|integer|min:1',
        ]);

        $quiz = \App\Models\LmsQuiz::create([
            'course_id' => $request->input('course_id'),
            'question_package_id' => $bank->id,
            'title' => $request->input('title'),
            'description' => $request->input('description') ?: ('Kuis dari Bank Soal: ' . $bank->bank_name),
            'time_limit' => $request->input('time_limit'),
            'is_published' => true,
        ]);

        $referer = (request()->header('referer') ?? '') . ' ' . (request()->url() ?? '') . ' ' . (request()->header('User-Agent') ?? '');
        $isMobile = str_contains($referer, '/m/') 
            || request()->has('is_mobile') 
            || preg_match('/(Mobile|Android|iPhone|iPad|iPod|Touch|Windows Phone)/i', request()->header('User-Agent') ?? '');

        if ($isMobile) {
            return redirect()->route('mobile.lms.show', $request->input('course_id'))
                ->with('success', "Bank Soal '{$bank->bank_name}' berhasil ditautkan sebagai Kuis LMS!");
        }

        return redirect()->route('guru.lms.show', $request->input('course_id'))
            ->with('success', "Bank Soal '{$bank->bank_name}' berhasil ditautkan sebagai Kuis LMS!");
    }

    public function bankEdit(CbtQuestionBank $bank)
    {
        $teacher = $this->authorizeBank($bank);
        $school = $bank->school ?? School::find($bank->school_id) ?? $this->getEffectiveSchool($teacher);

        $subjects = $this->getSubjectsForSchool($teacher, $school);
        $gradeLevels = $school->getGradeLevels();

        return view('guru.cbt.banks.edit', compact('bank', 'subjects', 'gradeLevels', 'school'));
    }

    public function bankUpdate(Request $request, CbtQuestionBank $bank)
    {
        $teacher = $this->authorizeBank($bank);
        $school = $bank->school ?? School::find($bank->school_id) ?? $this->getEffectiveSchool($teacher);
        $allowedGrades = $school->getGradeLevelsRule();

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'bank_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_level' => "required|in:{$allowedGrades}",
            'is_shared' => 'boolean',
        ]);

        $bank->update($validated);

        return redirect()->route('guru.cbt.banks.show', $bank)
            ->with('success', 'Bank soal berhasil diperbarui.');
    }

    public function bankDestroy(CbtQuestionBank $bank)
    {
        $this->authorizeBank($bank);

        // Check if bank is used in any exam
        if ($bank->exams()->exists()) {
            return back()->with('error', 'Bank soal tidak dapat dihapus karena masih digunakan dalam ujian.');
        }

        // Clean up question media files
        foreach ($bank->questions as $question) {
            foreach (['question_image', 'question_audio', 'question_video'] as $field) {
                if ($question->$field) {
                    Storage::disk('public')->delete($question->$field);
                }
            }
            foreach ($question->options as $opt) {
                if ($opt->option_image) {
                    Storage::disk('public')->delete($opt->option_image);
                }
            }
        }

        $bank->questions()->each(fn($q) => $q->options()->delete());
        $bank->questions()->delete();
        $bank->delete();

        return redirect()->route('guru.cbt.banks.index')
            ->with('success', 'Bank soal berhasil dihapus.');
    }

    // ==========================================
    // QUESTIONS
    // ==========================================

    /**
     * Import soal ke bank soal yang sudah ada (Guru)
     */
    public function importQuestions(Request $request, CbtQuestionBank $bank)
    {
        $this->authorizeBank($bank);

        $request->validate([
            'import_file' => 'required|file|max:51200|mimes:xlsx,xls,zip',
        ], [
            'import_file.required' => 'File import wajib diunggah.',
            'import_file.mimes'    => 'Format file harus .xlsx, .xls, atau .zip',
            'import_file.max'      => 'Ukuran file maksimal 50MB.',
        ]);

        $file = $request->file('import_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $excelPath = null;
        $imagesDir = null;
        $tempDir = null;

        try {
            if ($extension === 'zip') {
                $tempDir = storage_path('app/temp/guru_cbt_q_import_' . uniqid());
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $zip = new ZipArchive();
                if ($zip->open($file->getRealPath()) !== true) {
                    return back()->with('error', 'Gagal membuka file ZIP. Pastikan file tidak rusak.');
                }
                $zip->extractTo($tempDir);
                $zip->close();

                $excelPath = $this->findFileInDir($tempDir, ['xlsx', 'xls']);
                if (!$excelPath) {
                    $this->cleanupTempDir($tempDir);
                    return back()->with('error', 'File ZIP tidak berisi file Excel (.xlsx / .xls).');
                }

                $imagesDir = $this->findImagesDir($tempDir);
            } else {
                $excelPath = $file;
            }

            $import = new CbtQuestionsImport();
            Excel::import($import, $excelPath);
            $rows = $import->getRows();

            if (empty($rows)) {
                if ($tempDir) $this->cleanupTempDir($tempDir);
                return back()->with('error', 'File Excel tidak berisi data soal. Pastikan data diisi di sheet "Soal".');
            }

            [$validRows, $errors] = $this->validateImportRows($rows, $imagesDir);

            if (empty($validRows)) {
                if ($tempDir) $this->cleanupTempDir($tempDir);
                $errorMsg = 'Tidak ada data soal yang valid. Error: ' . implode(' | ', array_slice($errors, 0, 5));
                return back()->with('error', $errorMsg);
            }

            $createdCount = $this->processImportedQuestions($validRows, $bank, $imagesDir);

            if ($tempDir) $this->cleanupTempDir($tempDir);

            $msg = "Berhasil mengimpor {$createdCount} soal ke bank '{$bank->bank_name}'.";
            if (!empty($errors)) {
                session()->flash('import_warnings', array_slice($errors, 0, 10));
            }

            return redirect()->route('guru.cbt.banks.show', $bank)->with('success', $msg);

        } catch (\Exception $e) {
            if ($tempDir) $this->cleanupTempDir($tempDir);
            return back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }

    public function questionCreate(CbtQuestionBank $bank)
    {
        $this->authorizeBank($bank);
        return view('guru.cbt.questions.create', compact('bank'));
    }

    public function questionStore(StoreCbtQuestionRequest $request, CbtQuestionBank $bank)
    {
        $this->authorizeBank($bank);

        $validated = $request->validated();

        // Handle media uploads for question
        $mediaPaths = [];
        foreach (['question_image', 'question_audio', 'question_video'] as $field) {
            if ($request->hasFile($field)) {
                $mediaPaths[$field] = $request->file($field)->store('cbt/questions', 'public');
            }
        }

        $question = CbtQuestion::create(array_merge([
            'question_bank_id' => $bank->id,
            'question_type' => $validated['question_type'],
            'question_text' => $validated['question_text'],
            'explanation' => $validated['explanation'] ?? null,
            'points' => $validated['points'],
            'difficulty' => $validated['difficulty'],
            'topic' => $validated['topic'] ?? null,
            'competency' => $validated['competency'] ?? null,
            'answer_key' => $validated['answer_key'] ?? null,
        ], $mediaPaths));

        // Create options based on question type
        $optionsData = [];
        if ($validated['question_type'] === 'multiple_choice' && !empty($validated['options'])) {
            $optionsData = $validated['options'];
        } elseif ($validated['question_type'] === 'true_false' && !empty($validated['tf_options'])) {
            $optionsData = $validated['tf_options'];
        }

        foreach ($optionsData as $idx => $option) {
            $optionImage = null;
            if ($request->hasFile("options.{$idx}.image")) {
                $optionImage = $request->file("options.{$idx}.image")->store('cbt/options', 'public');
            }

            CbtQuestionOption::create([
                'question_id' => $question->id,
                'option_label' => $option['label'],
                'option_text' => $option['text'],
                'option_image' => $optionImage,
                'is_correct' => $option['is_correct'] ?? false,
                'sort_order' => $idx + 1,
            ]);
        }

        // Update counter
        $bank->update(['total_questions' => $bank->questions()->count()]);

        return redirect()->route('guru.cbt.banks.show', $bank)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function questionEdit(CbtQuestion $question)
    {
        $question->load('options', 'questionBank');
        $this->authorizeBank($question->questionBank);
        return view('guru.cbt.questions.edit', compact('question'));
    }

    public function questionUpdate(UpdateCbtQuestionRequest $request, CbtQuestion $question)
    {
        $question->load('questionBank');
        $this->authorizeBank($question->questionBank);

        $validated = $request->validated();

        // Handle media uploads & removals for question
        $mediaFields = ['question_image', 'question_audio', 'question_video'];
        foreach ($mediaFields as $field) {
            if ($request->boolean("remove_{$field}") && $question->$field) {
                Storage::disk('public')->delete($question->$field);
                $validated[$field] = null;
            }
            if ($request->hasFile($field)) {
                // Delete old file
                if ($question->$field) {
                    Storage::disk('public')->delete($question->$field);
                }
                $validated[$field] = $request->file($field)->store('cbt/questions', 'public');
            }
        }

        $question->update($validated);

        // Update options
        if (!empty($validated['options'])) {
            $existingOptions = $question->options->keyBy('option_label');
            $newLabels = collect($validated['options'])->pluck('label')->toArray();
            
            // Delete options that are no longer in the new set
            foreach ($question->options as $oldOpt) {
                if (!in_array($oldOpt->option_label, $newLabels)) {
                    if ($oldOpt->option_image) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldOpt->option_image);
                    }
                    $oldOpt->delete();
                }
            }

            foreach ($validated['options'] as $idx => $option) {
                $label = $option['label'];
                $existingOpt = $existingOptions->get($label);
                
                $optionImage = $existingOpt ? $existingOpt->option_image : null;
                
                if ($request->hasFile("options.{$idx}.image")) {
                    // Delete old image if any
                    if ($existingOpt && $existingOpt->option_image) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($existingOpt->option_image);
                    }
                    $optionImage = $request->file("options.{$idx}.image")->store('cbt/options', 'public');
                }

                \App\Models\CbtQuestionOption::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'option_label' => $label,
                    ],
                    [
                        'option_text' => $option['text'],
                        'option_image' => $optionImage,
                        'is_correct' => $option['is_correct'] ?? false,
                        'sort_order' => $idx + 1,
                    ]
                );
            }
        }

        return redirect()->route('guru.cbt.banks.show', $question->question_bank_id)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function questionDestroy(CbtQuestion $question)
    {
        $question->load('questionBank');
        $this->authorizeBank($question->questionBank);

        $bankId = $question->question_bank_id;

        // Clean up media files
        foreach (['question_image', 'question_audio', 'question_video'] as $field) {
            if ($question->$field) {
                Storage::disk('public')->delete($question->$field);
            }
        }
        foreach ($question->options as $opt) {
            if ($opt->option_image) {
                Storage::disk('public')->delete($opt->option_image);
            }
        }

        $question->delete();

        $bank = CbtQuestionBank::find($bankId);
        $bank?->update(['total_questions' => $bank->questions()->count()]);

        return back()->with('success', 'Soal berhasil dihapus.');
    }

    // ==========================================
    // EXAMS
    // ==========================================

    public function examIndex(Request $request)
    {
        $teacher = $this->resolveTeacher();
        $school = $this->getEffectiveSchool($teacher);
        $tIds = $this->getAllTeacherIds($teacher);
        $academicYear = AcademicYear::where('is_active', true)->first();

        $teacherSubjectIds = $this->getTeacherSubjectIds($teacher, $school->id);
        $filterScope = $request->query('scope', 'all'); // 'all', 'school', 'class'

        $examsQuery = CbtExam::query()
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->where(function ($q) use ($tIds, $school, $teacherSubjectIds) {
                // 1. Ujian Kelas milik guru di unit ini
                $q->where(function ($sub) use ($tIds, $school) {
                    $sub->whereIn('teacher_id', $tIds)
                        ->where(function($sq) use ($school) {
                            $sq->where('school_id', $school->id)->orWhereNull('school_id');
                        });
                });

                // 2. Ujian Sekolah untuk mata pelajaran yang diampu guru ini di unit ini
                $q->orWhere(function ($sq) use ($school, $teacherSubjectIds) {
                    $sq->where('exam_scope', 'school')
                       ->where('school_id', $school->id)
                       ->whereIn('status', ['published', 'active', 'completed', 'archived']);

                    if (!empty($teacherSubjectIds)) {
                        $sq->whereIn('subject_id', $teacherSubjectIds);
                    }
                });
            })
            ->when($filterScope === 'class', fn($q) => $q->where('exam_scope', 'class'))
            ->when($filterScope === 'school', fn($q) => $q->where('exam_scope', 'school'))
            ->with(['subject', 'participants.classroom', 'semester', 'teacher'])
            ->withCount([
                'answers as pending_essays_count' => function ($query) {
                    $query->needsGrading();
                },
                'results as completed_participants_count',
            ])
            ->orderByDesc('created_at');

        $exams = $examsQuery->paginate(20)->withQueryString();

        // Hitung total esai yang menunggu dikoreksi oleh guru ini di unit ini
        $totalPendingEssays = CbtAnswer::whereHas('session.exam', function ($q) use ($tIds, $school, $teacherSubjectIds, $academicYear) {
            $q->when($academicYear, fn($sq) => $sq->where('academic_year_id', $academicYear->id))
              ->where(function ($subQ) use ($tIds, $school, $teacherSubjectIds) {
                  $subQ->where(function($ownQ) use ($tIds, $school) {
                      $ownQ->whereIn('teacher_id', $tIds)
                           ->where(function($sq) use ($school) {
                               $sq->where('school_id', $school->id)->orWhereNull('school_id');
                           });
                  })->orWhere(function ($schoolQ) use ($school, $teacherSubjectIds) {
                      $schoolQ->where('exam_scope', 'school')
                              ->where('school_id', $school->id)
                              ->whereIn('status', ['published', 'active', 'completed', 'archived']);
                      if (!empty($teacherSubjectIds)) {
                          $schoolQ->whereIn('subject_id', $teacherSubjectIds);
                      }
                  });
              });
        })->needsGrading()->count();

        // Hitung kuota untuk tab filter
        $countSchoolExams = CbtExam::where('exam_scope', 'school')
            ->where('school_id', $school->id)
            ->where(function ($sq) use ($tIds, $teacherSubjectIds) {
                $sq->whereIn('teacher_id', $tIds);
                $sq->orWhere(function ($ssq) use ($teacherSubjectIds) {
                    $ssq->whereIn('status', ['published', 'active', 'completed', 'archived']);
                    if (!empty($teacherSubjectIds)) {
                        $ssq->whereIn('subject_id', $teacherSubjectIds);
                    }
                });
            })
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->count();

        $countClassExams = CbtExam::where('exam_scope', 'class')
            ->whereIn('teacher_id', $tIds)
            ->where(function($sq) use ($school) {
                $sq->where('school_id', $school->id)->orWhereNull('school_id');
            })
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->count();

        return view('guru.cbt.exams.index', compact('exams', 'teacher', 'filterScope', 'totalPendingEssays', 'countSchoolExams', 'countClassExams'));
    }

    public function examCreate()
    {
        $teacher = $this->resolveTeacher();
        $school = $this->getEffectiveSchool($teacher);
        $tIds = $this->getAllTeacherIds($teacher);

        $subjects = $this->getSubjectsForSchool($teacher, $school);

        $banks = CbtQuestionBank::with('subject:id,name,subject_name')
            ->where('is_active', true)
            ->where(function($q) use ($tIds, $school) {
                $q->where(function($sub) use ($tIds, $school) {
                    $sub->whereIn('teacher_id', $tIds)
                        ->where(function($sq) use ($school) {
                            $sq->where('school_id', $school->id)->orWhereNull('school_id');
                        });
                })->orWhere(function($sq) use ($school) {
                    $sq->where('school_id', $school->id)
                       ->where('is_shared', true);
                });
            })
            ->select('id', 'bank_name', 'subject_id', 'grade_level', 'total_questions')
            ->get()
            ->map(function($b) {
                $b->subject_name = $b->subject?->subject_name ?? $b->subject?->name ?? '';
                return $b;
            });

        $academicYear = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();

        // Classrooms filtered by teacher's schedule, teaching assignments, or homeroom in this school
        $classrooms = Classroom::where('school_id', $school->id)
            ->where('is_active', true)
            ->where(function ($q) use ($tIds, $academicYear, $semester) {
                $q->whereHas('schedules', function ($sq) use ($tIds, $academicYear, $semester) {
                    $sq->whereIn('teacher_id', $tIds)
                       ->where('academic_year_id', $academicYear?->id)
                       ->where('semester_id', $semester?->id);
                })
                ->orWhereHas('teachingAssignments', function ($tq) use ($tIds, $academicYear) {
                    $tq->whereIn('teacher_id', $tIds)
                       ->where('academic_year_id', $academicYear?->id)
                       ->where('is_active', true);
                })
                ->orWhereIn('homeroom_teacher_id', $tIds);
            })
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        if ($classrooms->isEmpty()) {
            $classrooms = Classroom::where('school_id', $school->id)
                ->where('is_active', true)
                ->orderBy('grade_level')
                ->orderBy('class_name')
                ->get();
        }

        return view('guru.cbt.exams.create', compact('subjects', 'banks', 'classrooms', 'academicYear', 'semester', 'teacher', 'school'));
    }

    public function examStore(StoreCbtExamRequest $request)
    {
        $teacher = $this->resolveTeacher();
        $school = $this->getEffectiveSchool($teacher);

        $validated = $request->validated();

        $academicYear = AcademicYear::where('is_active', true)->firstOrFail();
        $semester = Semester::where('is_active', true)->firstOrFail();

        $exam = CbtExam::create(array_merge($validated, [
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'exam_scope' => 'class',
            'total_questions_shown' => 0,
            'created_by' => Auth::id(),
            'status' => 'draft',
            'auto_sync_grade' => true, // Default to true for new exams
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

        return redirect()->route('guru.cbt.exams.show', $exam)
            ->with('success', 'Ujian CBT berhasil dibuat.');
    }

    public function examShow(CbtExam $exam)
    {
        $this->authorizeExam($exam);
        $exam->load(['subject', 'teacher', 'examQuestions.question.options', 'participants.classroom', 'results.student']);

        $statistics = $this->cbtService->getExamStatistics($exam);

        $pendingEssaysCount = CbtAnswer::whereHas('session', fn($q) => $q->where('exam_id', $exam->id))
            ->needsGrading()
            ->count();

        $hasEssayQuestions = $exam->examQuestions->filter(
            fn($eq) => $eq->question && in_array($eq->question->question_type, ['essay', 'fill_blank'])
        )->isNotEmpty();

        return view('guru.cbt.exams.show', compact('exam', 'statistics', 'pendingEssaysCount', 'hasEssayQuestions'));
    }

    public function examPublish(CbtExam $exam)
    {
        $this->authorizeExam($exam);

        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah'])) 
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        if ($exam->isSchoolScope() && !$isAdmin) {
            return back()->with('error', 'Penerbitan ujian sekolah hanya dapat dilakukan oleh administrator.');
        }

        $exam->update(['status' => 'published']);
        return back()->with('success', 'Ujian berhasil diterbitkan.');
    }

    public function examActivate(CbtExam $exam)
    {
        $this->authorizeExam($exam);
        $exam->update(['status' => 'active']);
        return back()->with('success', 'Ujian berhasil diaktifkan.');
    }

    public function examPause(CbtExam $exam)
    {
        $this->authorizeExam($exam);
        $this->cbtService->pauseExam($exam);
        return back()->with('success', 'Ujian berhasil ditangguhkan/dijeda.');
    }

    public function examResume(CbtExam $exam)
    {
        $this->authorizeExam($exam);
        $this->cbtService->resumeExam($exam);
        return back()->with('success', 'Ujian berhasil dilanjutkan kembali.');
    }

    /**
     * Start all student sessions at once (batch / serentak)
     */
    public function examBatchStart(CbtExam $exam)
    {
        $this->authorizeExam($exam);
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

    public function examComplete(CbtExam $exam)
    {
        $this->authorizeExam($exam);
        $exam->update(['status' => 'completed']);
        $this->cbtService->calculateRankings($exam);
        return back()->with('success', 'Ujian telah selesai. Ranking dihitung.');
    }

    /**
     * Results page for an exam (supports classroom filtering & teacher access scope)
     */
    public function examResults(CbtExam $exam, Request $request)
    {
        $teacher = $this->authorizeExam($exam);
        $exam->load(['examQuestions.question']);

        $user = Auth::user();
        $accessibleClassrooms = $this->cbtService->getAccessibleClassroomsForUser($exam, $user);
        $allowedClassroomIds = $accessibleClassrooms->pluck('id')->toArray();

        $selectedClassroomId = $request->filled('classroom_id') ? (int) $request->classroom_id : null;
        if ($selectedClassroomId && !in_array($selectedClassroomId, $allowedClassroomIds)) {
            $selectedClassroomId = null;
        }

        $resultsQuery = CbtExamResult::where('exam_id', $exam->id)
            ->with(['student.currentClassroom', 'session.classroom', 'session.student']);

        if ($selectedClassroomId) {
            $resultsQuery->whereHas('session', fn($q) => $q->where('classroom_id', $selectedClassroomId));
        } elseif (!empty($allowedClassroomIds)) {
            $resultsQuery->whereHas('session', fn($q) => $q->whereIn('classroom_id', $allowedClassroomIds));
        }

        $results = $resultsQuery->orderBy('rank')->get();

        $statistics = $this->cbtService->getExamStatistics($exam, $selectedClassroomId, $allowedClassroomIds);
        $itemAnalysis = $this->cbtService->getItemAnalysis($exam);

        $pendingEssaysQuery = CbtAnswer::whereHas('session', function($q) use ($exam, $selectedClassroomId, $allowedClassroomIds) {
            $q->where('exam_id', $exam->id);
            if ($selectedClassroomId) {
                $q->where('classroom_id', $selectedClassroomId);
            } elseif (!empty($allowedClassroomIds)) {
                $q->whereIn('classroom_id', $allowedClassroomIds);
            }
        })
        ->whereHas('question', fn($q) => $q->whereIn('question_type', ['essay', 'fill_blank']))
        ->whereNull('manual_score');

        $pendingEssaysCount = $pendingEssaysQuery->count();

        $hasEssayQuestions = $exam->examQuestions->filter(
            fn($eq) => $eq->question && in_array($eq->question->question_type, ['essay', 'fill_blank'])
        )->isNotEmpty();

        // Participation summary for the selected / allowed classrooms
        $participationData = $this->cbtService->getExamParticipationData($exam, $selectedClassroomId, $allowedClassroomIds);

        $selectedClassroom = $selectedClassroomId ? $accessibleClassrooms->firstWhere('id', $selectedClassroomId) : null;

        return view('guru.cbt.exams.results', compact(
            'exam',
            'results',
            'statistics',
            'itemAnalysis',
            'pendingEssaysCount',
            'hasEssayQuestions',
            'accessibleClassrooms',
            'selectedClassroomId',
            'selectedClassroom',
            'participationData'
        ));
    }

    /**
     * Grade essay answers (filtered by classroom)
     */
    public function gradeEssays(CbtExam $exam, Request $request)
    {
        $teacher = $this->authorizeExam($exam);
        $user = Auth::user();

        $accessibleClassrooms = $this->cbtService->getAccessibleClassroomsForUser($exam, $user);
        $allowedClassroomIds = $accessibleClassrooms->pluck('id')->toArray();

        $selectedClassroomId = $request->filled('classroom_id') ? (int) $request->classroom_id : null;
        if ($selectedClassroomId && !in_array($selectedClassroomId, $allowedClassroomIds)) {
            $selectedClassroomId = null;
        }

        $answersQuery = CbtAnswer::whereHas('session', function($q) use ($exam, $selectedClassroomId, $allowedClassroomIds) {
            $q->where('exam_id', $exam->id);
            if ($selectedClassroomId) {
                $q->where('classroom_id', $selectedClassroomId);
            } elseif (!empty($allowedClassroomIds)) {
                $q->whereIn('classroom_id', $allowedClassroomIds);
            }
        })
        ->whereHas('question', fn($q) => $q->whereIn('question_type', ['essay', 'fill_blank']))
        ->with(['session.student.currentClassroom', 'session.classroom', 'question']);

        $answers = $answersQuery->orderByRaw('CASE WHEN manual_score IS NULL THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->get();

        $examQuestions = \App\Models\CbtExamQuestion::where('exam_id', $exam->id)
            ->get()
            ->keyBy('question_id');

        $selectedClassroom = $selectedClassroomId ? $accessibleClassrooms->firstWhere('id', $selectedClassroomId) : null;

        return view('guru.cbt.exams.grade-essays', compact(
            'exam',
            'answers',
            'examQuestions',
            'accessibleClassrooms',
            'selectedClassroomId',
            'selectedClassroom'
        ));
    }

    public function gradeEssayStore(GradeEssayRequest $request, CbtAnswer $answer)
    {
        // Verify teacher owns or teaches the exam/student this answer belongs to
        $answer->load(['session.exam', 'session']);
        $this->authorizeExam($answer->session->exam);

        $user = Auth::user();
        $accessibleClassrooms = $this->cbtService->getAccessibleClassroomsForUser($answer->session->exam, $user);
        $allowedClassroomIds = $accessibleClassrooms->pluck('id')->toArray();
        if (!empty($allowedClassroomIds) && $answer->session->classroom_id && !in_array($answer->session->classroom_id, $allowedClassroomIds)) {
            abort(403, 'Anda tidak memiliki hak untuk menilai jawaban siswa dari kelas ini.');
        }

        $validated = $request->validated();

        $this->cbtService->gradeEssayAnswer(
            $answer, $validated['manual_score'], $validated['teacher_feedback'] ?? null, Auth::id()
        );

        return back()->with('success', 'Jawaban berhasil dinilai.');
    }

    /**
     * Export status keikutsertaan siswa (sudah vs belum ujian) ke Excel
     */
    public function exportParticipation(Request $request, CbtExam $exam)
    {
        $this->authorizeExam($exam);
        $user = Auth::user();

        $accessibleClassrooms = $this->cbtService->getAccessibleClassroomsForUser($exam, $user);
        $allowedClassroomIds = $accessibleClassrooms->pluck('id')->toArray();

        $selectedClassroomId = $request->filled('classroom_id') ? (int) $request->classroom_id : null;
        if ($selectedClassroomId && !in_array($selectedClassroomId, $allowedClassroomIds)) {
            abort(403, 'Akses ditolak ke kelas ini.');
        }

        $participationData = $this->cbtService->getExamParticipationData($exam, $selectedClassroomId, $allowedClassroomIds);
        $selectedClassroom = $selectedClassroomId ? $accessibleClassrooms->firstWhere('id', $selectedClassroomId) : null;

        $examTitleSlug = Str::slug($exam->exam_title);
        $classSlug = $selectedClassroom ? Str::slug($selectedClassroom->class_name) : 'semua-kelas';
        $filename = "Status_Ujian_{$examTitleSlug}_{$classSlug}_" . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new CbtExamParticipationExport($exam, $participationData, $selectedClassroom),
            $filename
        );
    }

    /**
     * Sync results to grades table
     */
    public function syncGrades(CbtExam $exam)
    {
        $this->authorizeExam($exam);
        $synced = $this->cbtService->syncExamResults($exam);
        return back()->with('success', "{$synced} hasil ujian berhasil disinkronkan ke nilai.");
    }

    /**
     * Edit exam form
     */
    public function examEdit(CbtExam $exam)
    {
        $teacher = $this->authorizeExam($exam);

        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah'])) 
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        if ($exam->isSchoolScope() && !$isAdmin) {
            abort(403, 'Pengaturan ujian sekolah hanya dapat diubah oleh administrator.');
        }

        abort_unless($exam->isDraft(), 403, 'Hanya ujian berstatus draft yang dapat diedit.');

        $school = $exam->school ?? School::find($exam->school_id) ?? $this->getEffectiveSchool($teacher);
        $tIds = $this->getAllTeacherIds($teacher);

        $subjects = $this->getSubjectsForSchool($teacher, $school);

        $academicYear = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();

        $banks = CbtQuestionBank::with('subject:id,name,subject_name')
            ->where('is_active', true)
            ->where(function($q) use ($tIds, $school) {
                $q->where(function($sub) use ($tIds, $school) {
                    $sub->whereIn('teacher_id', $tIds)
                        ->where(function($sq) use ($school) {
                            $sq->where('school_id', $school->id)->orWhereNull('school_id');
                        });
                })->orWhere(function($sq) use ($school) {
                    $sq->where('school_id', $school->id)
                       ->where('is_shared', true);
                });
            })
            ->get();
        // Classrooms filtered by teacher's schedule, teaching assignments, or homeroom in this school
        $classrooms = Classroom::where('school_id', $school->id)
            ->where('is_active', true)
            ->where(function ($q) use ($tIds, $academicYear, $semester) {
                $q->whereHas('schedules', function ($sq) use ($tIds, $academicYear, $semester) {
                    $sq->whereIn('teacher_id', $tIds)
                       ->where('academic_year_id', $academicYear?->id)
                       ->where('semester_id', $semester?->id);
                })
                ->orWhereHas('teachingAssignments', function ($tq) use ($tIds, $academicYear) {
                    $tq->whereIn('teacher_id', $tIds)
                       ->where('academic_year_id', $academicYear?->id)
                       ->where('is_active', true);
                })
                ->orWhereIn('homeroom_teacher_id', $tIds);
            })
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        if ($classrooms->isEmpty()) {
            $classrooms = Classroom::where('school_id', $school->id)
                ->where('is_active', true)
                ->orderBy('grade_level')
                ->orderBy('class_name')
                ->get();
        }

        $selectedBanks = $exam->questionBanks->map(fn($b) => [
            'bank_id' => $b->id,
            'questions_to_pick' => $b->pivot->questions_to_pick,
        ])->toArray();

        $selectedClassrooms = $exam->participants->pluck('classroom_id')->toArray();

        return view('guru.cbt.exams.edit', compact(
            'exam', 'subjects', 'banks', 'classrooms', 'teacher',
            'selectedBanks', 'selectedClassrooms'
        ));
    }

    /**
     * Update exam
     */
    public function examUpdate(StoreCbtExamRequest $request, CbtExam $exam)
    {
        $teacher = $this->authorizeExam($exam);

        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah'])) 
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        if ($exam->isSchoolScope() && !$isAdmin) {
            abort(403, 'Pengaturan ujian sekolah hanya dapat diubah oleh administrator.');
        }

        abort_unless($exam->isDraft(), 403, 'Hanya ujian berstatus draft yang dapat diedit.');

        $validated = $request->validated();

        $exam->update($validated);

        // Sync question banks
        $bankSync = [];
        foreach ($validated['question_banks'] as $bankData) {
            $bankSync[$bankData['bank_id']] = ['questions_to_pick' => $bankData['questions_to_pick']];
        }
        $exam->questionBanks()->sync($bankSync);

        // Sync classrooms
        $exam->participants()->delete();
        foreach ($validated['classrooms'] as $classroomId) {
            $exam->participants()->create(['classroom_id' => $classroomId]);
        }

        // Re-prepare questions
        $this->cbtService->prepareExamQuestions($exam);

        return redirect()->route('guru.cbt.exams.show', $exam)
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    /**
     * Delete exam (draft only)
     */
    public function examDestroy(CbtExam $exam)
    {
        $this->authorizeExam($exam);

        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'kurikulum', 'admin_sekolah'])) 
            || in_array($user->role ?? '', ['admin', 'superadmin', 'kurikulum', 'operator', 'admin_sekolah'])
        );

        if ($exam->isSchoolScope() && !$isAdmin) {
            abort(403, 'Ujian sekolah hanya dapat dihapus oleh administrator.');
        }

        abort_unless($exam->isDraft(), 403, 'Hanya ujian berstatus draft yang dapat dihapus.');

        // Clean up related data
        $exam->examQuestions()->delete();
        $exam->participants()->delete();
        $exam->questionBanks()->detach();
        $exam->delete();

        return redirect()->route('guru.cbt.exams.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    // ==========================================
    // PRIVATE HELPERS FOR IMPORT
    // ==========================================

    private function validateImportRows(array $rows, ?string $imagesDir): array
    {
        $errors = [];
        $validRows = [];
        $validTypes = ['multiple_choice', 'true_false', 'essay', 'fill_blank'];
        $validDifficulties = ['mudah', 'sedang', 'sulit'];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $rowErrors = [];

            $question = trim($row['question'] ?? '');
            $type = strtolower(trim($row['question_type'] ?? ''));

            if (empty($question)) {
                $rowErrors[] = 'Teks soal kosong';
            }
            if (!in_array($type, $validTypes)) {
                $rowErrors[] = "Tipe soal '{$type}' tidak valid (harus: " . implode(', ', $validTypes) . ')';
            }

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

            $difficulty = strtolower(trim($row['difficulty'] ?? 'sedang'));
            if (!in_array($difficulty, $validDifficulties)) {
                $difficulty = 'sedang';
            }

            $points = intval($row['points'] ?? 1);
            if ($points < 1) $points = 1;

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

        return [$validRows, $errors];
    }

    private function processImportedQuestions(array $validRows, CbtQuestionBank $bank, ?string $imagesDir): int
    {
        $createdCount = 0;
        foreach ($validRows as $row) {
            $type = $row['_type'];

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

            $videoUrl = trim($row['video_url'] ?? '');
            $questionVideoPath = null;
            if (!empty($videoUrl) && filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                $questionVideoPath = $videoUrl;
            }

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
        return $createdCount;
    }

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

    private function findImagesDir(string $baseDir): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir() && strtolower($item->getFilename()) === 'images') {
                return $item->getRealPath();
            }
        }
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        foreach (scandir($baseDir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $imageExts)) {
                return $baseDir;
            }
        }
        return null;
    }

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
}
