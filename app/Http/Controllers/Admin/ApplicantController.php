<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\ProgramKeahlian;
use App\Models\KonsentrasiKeahlian;
use App\Models\PsbWave;
use App\Services\ApplicantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApplicantController extends Controller
{
    public function __construct(
        protected ApplicantService $applicantService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Applicant::with(['school', 'academicYear', 'programKeahlian', 'konsentrasiKeahlian'])
            ->orderBy('created_at', 'desc');

        // School scoping for non-SA
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        $selectedYearId = $request->filled('academic_year_id') 
            ? $request->academic_year_id 
            : ($request->has('academic_year_id') ? null : AcademicYear::where('is_active', true)->first()?->id);

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        if ($request->filled('program_keahlian_id')) {
            $query->where('program_keahlian_id', $request->program_keahlian_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $applicants = $query->paginate(20)->withQueryString();

        // Role-based counts
        if ($user->isSuperAdmin()) {
            $totalApplicants = Applicant::count();
            $smpCount = Applicant::where('school_id', 1)->count();
            $smaCount = Applicant::where('school_id', 2)->count();
            $smkCount = Applicant::where('school_id', 3)->count();
            $schools = School::schoolsOnly()->get();
        } else {
            $schoolId = $user->school_id;
            $totalApplicants = Applicant::where('school_id', $schoolId)->count();
            $smpCount = ($schoolId == 1) ? $totalApplicants : 0;
            $smaCount = ($schoolId == 2) ? $totalApplicants : 0;
            $smkCount = ($schoolId == 3) ? $totalApplicants : 0;
            $schools = School::where('id', $schoolId)->get();
        }

        $academicYears = AcademicYear::all();
        $programKeahlians = \App\Models\ProgramKeahlian::where('is_active', true)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('school_id', $user->school_id))
            ->get();

        return view('admin.psb.index', compact('applicants', 'totalApplicants', 'smpCount', 'smaCount', 'smkCount', 'academicYears', 'schools', 'programKeahlians', 'selectedYearId'));
    }

    /**
     * Export applicants to CSV.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel'); // 'excel', 'csv' or 'pdf'
        $filters = $request->only([
            'academic_year_id', 'school_id', 'program_keahlian_id', 'status', 'search',
        ]);

        // If format is excel, use Laravel-Excel
        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ApplicantsExport($filters), 
                'pendaftar_' . date('YmdHis') . '.xlsx'
            );
        }

        // If format is csv, use Laravel-Excel CSV format
        if ($format === 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ApplicantsExport($filters), 
                'pendaftar_' . date('YmdHis') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        }

        // If format is pdf, use dompdf
        if ($format === 'pdf') {
            $query = Applicant::with(['school', 'academicYear', 'programKeahlian', 'konsentrasiKeahlian'])
                ->orderBy('created_at', 'desc');

            if (!empty($filters['academic_year_id'])) {
                $query->where('academic_year_id', $filters['academic_year_id']);
            }
            if (!empty($filters['school_id'])) {
                $query->where('school_id', $filters['school_id']);
            }
            if (!empty($filters['program_keahlian_id'])) {
                $query->where('program_keahlian_id', $filters['program_keahlian_id']);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('nisn', 'like', "%{$search}%")
                      ->orWhere('registration_number', 'like', "%{$search}%");
                });
            }

            $applicants = $query->get();
            $academicYear = !empty($filters['academic_year_id']) ? AcademicYear::find($filters['academic_year_id']) : null;
            $school = !empty($filters['school_id']) ? School::find($filters['school_id']) : null;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.psb.pdf.applicants', compact('applicants', 'academicYear', 'school'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('daftar_pendaftar_' . date('YmdHis') . '.pdf');
        }

        return back()->with('error', 'Format tidak didukung');
    }

    /**
     * Show the form for creating a new resource (offline registration).
     */
    public function create()
    {
        $schools = School::schoolsOnly()->get();
        $academicYear = AcademicYear::where('is_active', true)->orderBy('id', 'desc')->first();

        if (!$academicYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif. Silakan aktifkan tahun ajaran terlebih dahulu.');
        }

        $waves = \App\Models\RegistrationWave::where('is_active', true)
            ->where('academic_year_id', $academicYear->id)
            ->with('school')
            ->get()
            ->groupBy('school_id');

        return view('admin.psb.create', compact('schools', 'academicYear', 'waves'));
    }

    /**
     * Store a newly created resource in storage (offline registration).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'wave_id' => 'nullable|exists:registration_waves,id',
            'nisn' => 'required|unique:applicants,nisn',
            'full_name' => 'required|string',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string',
            'birth_date' => 'required|date',
            'religion' => 'required|string',
            'address' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'father_name' => 'required|string',
            'father_phone' => 'nullable|string',
            'mother_name' => 'required|string',
            'mother_phone' => 'nullable|string',
            'previous_school' => 'required|string',
            'graduation_year' => 'nullable|integer|min:2015|max:2030',
            'admission_path' => 'required|in:reguler,prestasi',
            'skip_verification' => 'nullable|boolean',
            'achievements' => 'nullable|array',
            'achievements.*.name' => 'required_if:admission_path,prestasi|string',
            'achievements.*.type' => 'nullable|string',
            'achievements.*.level' => 'required_if:admission_path,prestasi|string',
            'achievements.*.rank' => 'required_if:admission_path,prestasi|string',
            'achievements.*.organizer' => 'nullable|string',
            'achievements.*.year' => 'required_if:admission_path,prestasi|integer',
            'achievements.*.certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:15360',
        ]);

        try {
            $result = $this->applicantService->registerOffline($validated, $request);

            $msg = 'Pendaftaran offline berhasil dibuat dengan nomor: ' . $result['registration_number'];
            if ($result['skipped']) {
                $msg .= ' (Langsung diterima tanpa verifikasi)';
            }

            return redirect()->route('admin.psb.applicants.show', $result['applicant']->id)
                ->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Error creating applicant: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pendaftaran. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        $applicant->load(['school', 'academicYear', 'programKeahlian', 'konsentrasiKeahlian', 'documents', 'testScores', 'achievements', 'payments', 'discounts', 'feeExemptions']);

        return view('admin.psb.show', compact('applicant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        $applicant->load(['school', 'academicYear', 'programKeahlian', 'konsentrasiKeahlian']);
        $schools = School::schoolsOnly()->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $programKeahlians = ProgramKeahlian::where('school_id', $applicant->school_id)->get();
        $konsentrasiKeahlians = KonsentrasiKeahlian::where('program_keahlian_id', $applicant->program_keahlian_id)->get();
        $waves = \App\Models\PsbWave::where('school_id', $applicant->school_id)->get();

        return view('admin.psb.edit', compact('applicant', 'schools', 'academicYears', 'programKeahlians', 'konsentrasiKeahlians', 'waves'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nisn' => 'nullable|string|max:20',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'religion' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'previous_school' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'wave_id' => 'nullable|exists:psb_waves,id',
            'admission_path' => 'nullable|string|max:50',
            'program_keahlian_id' => 'nullable|exists:program_keahlians,id',
            'konsentrasi_keahlian_id' => 'nullable|exists:konsentrasi_keahlians,id',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:20',
            'father_occupation' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:20',
            'mother_occupation' => 'nullable|string|max:100',
            'parent_income' => 'nullable|string|max:100',
            'status' => 'required|in:draft,submitted,verified,tested,accepted,rejected,re-registered,withdrawn',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($applicant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($applicant->photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($applicant->photo_path);
            }
            $path = $request->file('photo')->store('applicants/photos', 'public');
            $validated['photo_path'] = $path;
        }

        unset($validated['photo']);
        $applicant->update($validated);

        return redirect()->route('admin.psb.applicants.show', $applicant)
            ->with('success', "Data pendaftar {$applicant->full_name} ({$applicant->registration_number}) berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);

        // Keamanan: Cek apakah sudah dimigrasi ke Siswa
        if ($applicant->student_id || $applicant->status === 're-registered') {
            return back()->with('error', 'Pendaftar tidak dapat dihapus karena sudah dimigrasikan menjadi Siswa Aktif.');
        }

        try {
            $regNumber = $applicant->registration_number;
            $name = $applicant->full_name;

            // Hapus file foto jika ada
            if ($applicant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($applicant->photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($applicant->photo_path);
            }

            // Hapus berkas pendukung
            foreach ($applicant->documents as $doc) {
                if ($doc->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            $applicant->testScores()->delete();
            $applicant->achievements()->delete();
            $applicant->delete();

            return redirect()->route('admin.psb.applicants.index')
                ->with('success', "Data pendaftar {$name} ({$regNumber}) berhasil dihapus dari sistem.");
        } catch (\Throwable $e) {
            Log::error('Error deleting applicant: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus pendaftar: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment and update status.
     */
    public function verifyPayment(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        try {
            $this->applicantService->verifyPayment($applicant);
            return back()->with('success', 'Pembayaran berhasil diverifikasi dan notifikasi telah dikirim.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Verify achievement/prestasi data and update status.
     */
    public function verifyPrestasi(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        try {
            $this->applicantService->verifyPrestasi($applicant);
            return back()->with('success', 'Data prestasi berhasil diverifikasi. Notifikasi untuk upload dokumen telah dikirim.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject achievement/prestasi data.
     */
    public function rejectPrestasi(Request $request, Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $this->applicantService->rejectPrestasi($applicant, $request->rejection_reason);
            return back()->with('success', 'Prestasi ditolak. Pendaftar dialihkan ke jalur reguler dan notifikasi telah dikirim.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Verify documents and update status.
     */
    public function verifyDocument(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        try {
            $this->applicantService->verifyDocument($applicant);
            return back()->with('success', 'Dokumen berhasil diverifikasi dan notifikasi telah dikirim.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Accept applicant.
     */
    public function accept(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        try {
            $this->applicantService->acceptApplicant($applicant);
            return back()->with('success', 'Pendaftar berhasil diterima dan notifikasi telah dikirim.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Migrate applicant to student.
     */
    public function migrate(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        try {
            $student = $this->applicantService->migrateToStudent($applicant);
            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Pendaftar berhasil diaktifkan sebagai siswa.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject applicant.
     */
    public function reject(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        try {
            $this->applicantService->rejectApplicant($applicant);
            return back()->with('success', 'Pendaftar telah ditolak.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show input score form.
     */
    public function inputScore(Applicant $applicant)
    {
        $this->authorizeApplicant($applicant);
        if ($applicant->status !== 'document_verified') {
            return back()->with('error', 'Status pendaftar tidak valid untuk input nilai.');
        }

        $applicant->load(['school', 'academicYear', 'testScores']);

        return view('admin.psb.input-score', compact('applicant'));
    }

    /**
     * Save test scores.
     */
    public function saveScore(Request $request, Applicant $applicant)
    {
        $request->validate([
            'test_date' => 'required|date',
            'scores' => 'required|array',
            'scores.*.subject' => 'required|string',
            'scores.*.score' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $this->applicantService->saveTestScores($applicant, $request->test_date, $request->scores);
            return redirect()->route('admin.psb.applicants.show', $applicant)
                ->with('success', 'Nilai berhasil disimpan. Status diubah menjadi "Scored".');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan nilai PSB: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan nilai. Silakan coba lagi.');
        }
    }

    /**
     * Protect access to applicant record
     */
    private function authorizeApplicant(Applicant $applicant)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $applicant->school_id != $user->school_id) {
            abort(403, 'Anda tidak memiliki akses ke data pendaftar ini.');
        }
    }
}
