<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\ProgramKeahlian;
use App\Models\KonsentrasiKeahlian;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicRegistrationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index()
    {
        try {
            $schools = School::where('is_active', true)
                ->where('psb_is_active', true)
                ->schoolsOnly()
                ->with(['admissionFees' => function($q) {
                    $q->where('is_active', true);
                }, 'registrationWaves' => function($q) {
                    $q->where('is_active', true);
                }])
                ->get();
            $programKeahlians = ProgramKeahlian::where('is_active', true)->get();
            
            // Get active academic year for registration
            $academicYear = AcademicYear::where('is_active', true)->orderBy('id', 'desc')->first();
            
            // Fetch recent alumni for the home page showcase (limit to 5, approved only)
            $recentAlumni = \App\Models\AlumniDirectory::with('school')
                                ->where('is_approved', true)
                                ->whereNotNull('message')
                                ->latest()
                                ->take(5)
                                ->get();
            
            if (!$academicYear) {
                return view('public.registration', [
                    'schools' => $schools,
                    'programKeahlians' => $programKeahlians ?? collect(),
                    'academicYear' => null,
                    'recentAlumni' => $recentAlumni,
                ])->with('error', 'Tidak ada tahun ajaran aktif untuk pendaftaran');
            }
            
            return view('public.registration', compact('schools', 'programKeahlians', 'academicYear', 'recentAlumni'));
        } catch (\Exception $e) {
            Log::error('Registration page error: ' . $e->getMessage());
            return view('public.registration', [
                'schools' => School::where('is_active', true)->schoolsOnly()->get(),
                'programKeahlians' => ProgramKeahlian::where('is_active', true)->get(),
                'academicYear' => AcademicYear::where('is_active', true)->first(),
                'recentAlumni' => collect(),
            ]);
        }
    }

    public function store(Request $request)
    {
        // Find SMK school ID dynamically (program keahlian only required for SMK)
        $smkSchoolIds = \App\Models\School::where('type', 'SMK')
            ->pluck('id')
            ->implode(',');
        
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'nisn' => 'required|unique:applicants,nisn',
                'full_name' => 'required|string',
                'gender' => 'required|in:L,P',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date',
                'religion' => 'required|string',
                'address' => 'required|string',
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                'photo' => 'nullable|image|max:4096',
                'father_name' => 'required|string',
                'father_phone' => 'nullable|string',
                'father_occupation' => 'nullable|string',
                'mother_name' => 'required|string',
                'mother_phone' => 'nullable|string',
                'mother_occupation' => 'nullable|string',
                'previous_school' => 'required|string',
                'admission_path' => 'required|in:reguler,prestasi',
                'program_keahlian_id' => "required_if:school_id,{$smkSchoolIds}|nullable|exists:program_keahlians,id",
                'konsentrasi_keahlian_id' => "required_if:school_id,{$smkSchoolIds}|nullable|exists:konsentrasi_keahlians,id",
                // Prestasi fields (simple - hanya untuk Juara Kelas)
                'achievement_rank' => 'required_if:admission_path,prestasi|nullable|in:1,2,3',
                'achievement_grade' => 'required_if:admission_path,prestasi|nullable|in:6,7,8,9',
                'achievement_year' => 'required_if:admission_path,prestasi|nullable|string',
                'achievement_school' => 'required_if:admission_path,prestasi|nullable|string',
                'achievement_certificate' => 'required_if:admission_path,prestasi|nullable|file|mimes:jpg,jpeg,png,pdf|max:15360',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ' . json_encode($e->errors()));
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        // Track uploaded files for cleanup on failure
        $uploadedFiles = [];

        // Generate Registration Number with transaction to prevent duplicates
        DB::beginTransaction();
        try {
            // Handle photo upload inside transaction
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('applicants', 'public');
                $validated['photo_path'] = $path;
                $uploadedFiles[] = $path;
            }
            // Get PSB academic year
            $academicYear = AcademicYear::where('is_active', true)->first();
            
            if (!$academicYear) {
                throw new \Exception('Tidak ada tahun ajaran aktif untuk pendaftaran');
            }
            
            $school = School::findOrFail($request->school_id);
            
            // Generate school code from school name
            $schoolCode = 'SCH'; // Default fallback
            $schoolName = strtoupper(trim($school->name));
            
            // Extract school type from name: SMPS/SMP, SMA, SMKS/SMK
            if (strpos($schoolName, 'SMPS') !== false || strpos($schoolName, 'SMP ') !== false) {
                $schoolCode = 'SMP';
            } elseif (strpos($schoolName, 'SMKS') !== false || strpos($schoolName, 'SMK ') !== false) {
                $schoolCode = 'SMK';
            } elseif (strpos($schoolName, 'SMA') !== false) {
                $schoolCode = 'SMA';
            }
            
            // Get last registration number for this school and year (with lock)
            $lastApplicant = Applicant::where('school_id', $request->school_id)
                ->where('academic_year_id', $academicYear->id)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();
            
            // Extract last number or start from 1
            $lastNumber = 1;
            if ($lastApplicant && $lastApplicant->registration_number) {
                // Extract number from format: SMK-26-0001
                preg_match('/\d{4}$/', $lastApplicant->registration_number, $matches);
                if (!empty($matches)) {
                    $lastNumber = intval($matches[0]) + 1;
                }
            }
            
            $registrationNumber = sprintf('%s-%s-%04d', $schoolCode, date('y'), $lastNumber);
            
            // Double check if number exists (safety check)
            $maxAttempts = 10;
            $attempt = 0;
            while (Applicant::where('registration_number', $registrationNumber)->exists() && $attempt < $maxAttempts) {
                $lastNumber++;
                $registrationNumber = sprintf('%s-%s-%04d', $schoolCode, date('y'), $lastNumber);
                $attempt++;
            }
            
            if ($attempt >= $maxAttempts) {
                throw new \Exception('Unable to generate unique registration number');
            }

            $validated['academic_year_id'] = $academicYear->id;
            $validated['registration_number'] = $registrationNumber;
            $validated['status'] = 'submitted';
            $validated['submission_date'] = now();

            // Auto-assign to active wave for the school
            $activeWave = \App\Models\RegistrationWave::where('school_id', $school->id)
                ->where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();

            if ($activeWave) {
                $validated['wave_id'] = $activeWave->id;
                $activeWave->increment('registered_count');
            }

            $applicant = Applicant::create($validated);
            
            // Handle achievement if admission path is prestasi
            if ($request->admission_path === 'prestasi' && $request->achievement_rank) {
                // Handle certificate upload
                $certificatePath = null;
                if ($request->hasFile('achievement_certificate')) {
                    $certificatePath = $request->file('achievement_certificate')->store('achievements', 'public');
                    $uploadedFiles[] = $certificatePath;
                }

                // Create achievement dengan nama "Juara X Kelas Y"
                $achievementName = "Juara {$request->achievement_rank} Kelas {$request->achievement_grade}";
                
                // Determine level based on grade (school level achievement)
                $level = 'school';
                
                // Calculate points based on rank
                $points = $this->calculateClassRankPoints($request->achievement_rank);

                \App\Models\ApplicantAchievement::create([
                    'applicant_id' => $applicant->id,
                    'achievement_name' => $achievementName,
                    'achievement_type' => 'academic',
                    'achievement_level' => $level,
                    'rank' => $request->achievement_rank,
                    'organizer' => $request->achievement_school,
                    'year' => (int) substr($request->achievement_year, 0, 4), // Extract year from "2025/2026"
                    'certificate_path' => $certificatePath,
                    'points' => $points,
                ]);

                // Auto-check and apply fee exemption
                $exemptionService = new \App\Services\AchievementFeeExemptionService();
                $exemption = $exemptionService->autoCheckAndApply($applicant);
                
                if ($exemption) {
                    Log::info("Fee exemption automatically applied for class rank achievement", [
                        'applicant_id' => $applicant->id,
                        'exemption_id' => $exemption->id,
                        'class_rank' => $request->achievement_rank,
                    ]);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Cleanup uploaded files on failure
            foreach ($uploadedFiles as $file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
            }
            
            Log::error('Registration store error: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi atau hubungi admin.')
                ->withInput();
        }

        // Send WhatsApp & Email notification automatically
        try {
            $this->notificationService->sendPSBRegistration($applicant);
        } catch (\Exception $e) {
            Log::error('Failed to send PSB registration notification: ' . $e->getMessage());
            // Continue even if notification fails
        }

        return redirect()->route('public.registration.success', $applicant->registration_number)
            ->with('success', 'Pendaftaran berhasil! Nomor registrasi Anda: ' . $registrationNumber . '. Konfirmasi telah dikirim ke WhatsApp Anda.');
    }

    public function success($registrationNumber)
    {
        $applicant = Applicant::where('registration_number', $registrationNumber)->firstOrFail();
        
        return view('public.registration-success', compact('applicant'));
    }

    public function check()
    {
        return view('public.check-status');
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string',
            'nisn' => 'required|string',
        ]);

        $applicant = Applicant::where('registration_number', $request->registration_number)
            ->where('nisn', $request->nisn)
            ->with(['school', 'academicYear', 'documents'])
            ->first();

        if (!$applicant) {
            return back()->with('error', 'Data pendaftaran tidak ditemukan. Periksa kembali nomor registrasi dan NISN Anda.');
        }

        return view('public.status-result', compact('applicant'));
    }

    public function uploadDocument(Request $request)
    {
        // Get applicant and school to determine valid documents
        $applicant = Applicant::with('school')->find($request->applicant_id);

        if (!$applicant || $applicant->registration_number !== $request->registration_number || $applicant->nisn !== $request->nisn) {
            return redirect()->route('public.registration.check')
                ->with('document_error', 'Data pendaftaran tidak valid.')
                ->with('auto_check', [
                    'registration_number' => $request->registration_number,
                    'nisn' => $request->nisn
                ]);
        }

        $allowedTypes = array_keys($applicant->school->getAllDocumentTypes());
        $allowedTypesStr = implode(',', $allowedTypes);

        $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
            'registration_number' => 'required|string',
            'nisn' => 'required|string',
            'document_type' => 'required|in:' . $allowedTypesStr,
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360', // 15MB
        ]);

        // Check if already uploaded this type
        $existing = \App\Models\ApplicantDocument::where('applicant_id', $applicant->id)
            ->where('document_type', $request->document_type)
            ->first();

        if ($existing) {
            return redirect()->route('public.registration.check')
                ->with('document_error', 'Dokumen ' . $request->document_type . ' sudah diupload sebelumnya.')
                ->with('scroll_to_documents', true)
                ->with('auto_check', [
                    'registration_number' => $applicant->registration_number,
                    'nisn' => $applicant->nisn
                ]);
        }

        // Upload file
        $file = $request->file('document_file');
        $filename = $request->document_type . '_' . $applicant->registration_number . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents/applicants/' . $applicant->id, $filename, 'public');

        // Save to database
        \App\Models\ApplicantDocument::create([
            'applicant_id' => $applicant->id,
            'document_type' => $request->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'verified' => false,
        ]);

        // Send notification to admin (optional)
        try {
            // You can add notification here
        } catch (\Exception $e) {
            Log::error('Failed to send document upload notification: ' . $e->getMessage());
        }

        // Redirect back to status page with success message
        return redirect()->route('public.registration.check')
            ->with('document_success', 'Dokumen berhasil diupload! Mohon tunggu verifikasi dari admin.')
            ->with('scroll_to_documents', true)
            ->with('auto_check', [
                'registration_number' => $applicant->registration_number,
                'nisn' => $applicant->nisn
            ]);
    }

    public function getProgramKeahlian($schoolId)
    {
        $programs = DB::table('program_keahlians')
            ->where('school_id', $schoolId)
            ->where('is_active', 1)
            ->select('id', 'nama', 'kode')
            ->get();

        return response()->json($programs);
    }

    public function getKonsentrasiKeahlian($programId)
    {
        $konsentrasi = DB::table('konsentrasi_keahlians')
            ->where('program_keahlian_id', $programId)
            ->where('is_active', 1)
            ->select('id', 'nama', 'kode')
            ->get();

        return response()->json($konsentrasi);
    }

    /**
     * Calculate points for class rank achievement (Juara Kelas)
     */
    private function calculateClassRankPoints(string $rank): float
    {
        $rankPoints = [
            '1' => 20.0,  // Juara 1 Kelas
            '2' => 15.0,  // Juara 2 Kelas
            '3' => 10.0,  // Juara 3 Kelas
        ];

        return $rankPoints[$rank] ?? 0;
    }

    /**
     * Calculate achievement points based on level and rank (for competition achievements)
     */
    private function calculateAchievementPoints(string $level, string $rank): float
    {
        $levelPoints = [
            'international' => 100,
            'national' => 80,
            'provincial' => 60,
            'district' => 40,
            'school' => 20,
        ];

        $rankMultiplier = [
            '1' => 1.0,
            '2' => 0.8,
            '3' => 0.6,
            'harapan_1' => 0.4,
            'harapan_2' => 0.3,
            'harapan_3' => 0.2,
            'partisipan' => 0.1,
        ];

        $basePoints = $levelPoints[$level] ?? 0;
        $multiplier = $rankMultiplier[$rank] ?? 0;

        return $basePoints * $multiplier;
    }
}
