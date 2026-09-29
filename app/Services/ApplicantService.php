<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantAchievement;
use App\Models\ApplicantTestScore;
use App\Models\AcademicYear;
use App\Models\RegistrationWave;
use App\Models\School;
use App\Services\AchievementFeeExemptionService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicantService
{
    public function __construct(
        protected WhatsAppService $whatsappService,
        protected AchievementFeeExemptionService $exemptionService,
    ) {}

    // ──────────────────────────────────────────────
    //  Offline Registration
    // ──────────────────────────────────────────────

    /**
     * Register a new applicant (offline flow).
     *
     * @return array{applicant: Applicant, registration_number: string, skipped: bool}
     * @throws \Exception
     */
    public function registerOffline(array $validated, Request $request): array
    {
        return DB::transaction(function () use ($validated, $request) {
            $academicYear = AcademicYear::where('is_active', true)
                ->orderBy('id', 'desc')
                ->first();

            if (!$academicYear) {
                throw new \Exception('Tidak ada tahun ajaran aktif');
            }

            $school = School::find($validated['school_id']);
            $lastNumber = Applicant::where('school_id', $validated['school_id'])
                ->where('academic_year_id', $academicYear->id)
                ->count() + 1;

            $schoolCode = strtoupper(substr($school->type, 0, 3));
            $registrationNumber = sprintf('%s-%s-%04d', $schoolCode, date('y'), $lastNumber);

            $validated['academic_year_id'] = $academicYear->id;
            $validated['registration_number'] = $registrationNumber;
            $validated['registration_type'] = 'offline';
            $validated['registered_by'] = Auth::user()?->name ?? 'admin';

            $skipVerification = $validated['skip_verification'] ?? false;

            if ($skipVerification && !$school->requires_test) {
                $validated['status'] = 'accepted';
                $validated['submission_date'] = now();
                $validated['payment_verified_at'] = now();
                $validated['document_verified_at'] = now();
                $validated['accepted_at'] = now();
            } else {
                $validated['status'] = 'submitted';
                $validated['submission_date'] = now();
            }

            // Remove achievements before creating applicant
            $achievementsData = $validated['achievements'] ?? [];
            unset($validated['achievements'], $validated['skip_verification']);

            $applicant = Applicant::create($validated);

            // Handle achievements for prestasi path
            if ($validated['admission_path'] === 'prestasi' && !empty($achievementsData)) {
                $this->createAchievements($applicant, $achievementsData, $request);

                $exemption = $this->exemptionService->autoCheckAndApply($applicant);
                if ($exemption) {
                    Log::info('Fee exemption automatically applied', [
                        'applicant_id' => $applicant->id,
                        'exemption_id' => $exemption->id,
                    ]);
                }
            }

            // Update wave counter
            if (!empty($validated['wave_id'])) {
                RegistrationWave::find($validated['wave_id'])?->increment('registered_count');
            }

            return [
                'applicant' => $applicant,
                'registration_number' => $registrationNumber,
                'skipped' => $skipVerification,
            ];
        });
    }

    /**
     * Create achievement records for a prestasi applicant.
     */
    protected function createAchievements(Applicant $applicant, array $achievementsData, Request $request): void
    {
        foreach ($achievementsData as $index => $data) {
            $certificatePath = null;
            if ($request->hasFile("achievements.$index.certificate")) {
                $certificatePath = $request->file("achievements.$index.certificate")
                    ->store('achievements', 'public');
            }

            $points = $this->calculateAchievementPoints($data['level'], $data['rank']);

            ApplicantAchievement::create([
                'applicant_id' => $applicant->id,
                'achievement_name' => $data['name'],
                'achievement_type' => $data['type'] ?? null,
                'achievement_level' => $data['level'],
                'rank' => $data['rank'],
                'organizer' => $data['organizer'] ?? null,
                'year' => $data['year'],
                'certificate_path' => $certificatePath,
                'points' => $points,
            ]);
        }
    }

    /**
     * Calculate achievement points based on level and rank.
     */
    public function calculateAchievementPoints(string $level, string $rank): float
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

        return ($levelPoints[$level] ?? 0) * ($rankMultiplier[$rank] ?? 0);
    }

    // ──────────────────────────────────────────────
    //  CSV Export
    // ──────────────────────────────────────────────

    /**
     * Export filtered applicants to CSV and return the content string.
     *
     * @return array{content: string, filename: string}
     */
    public function exportToCsv(array $filters): array
    {
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

        $filename = 'pendaftar_' . date('YmdHis') . '.csv';
        $handle = fopen('php://temp', 'w');

        // BOM for Excel UTF-8 support
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($handle, [
            'No. Registrasi', 'NISN', 'Nama Lengkap', 'Jenis Kelamin',
            'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Alamat',
            'Telepon', 'Email', 'Sekolah Tujuan', 'Program Keahlian',
            'Konsentrasi Keahlian', 'Jalur Pendaftaran', 'Asal Sekolah',
            'Nama Ayah', 'Telepon Ayah', 'Pekerjaan Ayah',
            'Nama Ibu', 'Telepon Ibu', 'Pekerjaan Ibu',
            'Status', 'Tanggal Daftar', 'Tahun Ajaran',
        ]);

        foreach ($applicants as $a) {
            fputcsv($handle, [
                $a->registration_number,
                $a->nisn,
                $a->full_name,
                $a->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                $a->birth_place,
                $a->birth_date->format('d/m/Y'),
                $a->religion,
                $a->address,
                $a->phone ?? '-',
                $a->email ?? '-',
                $a->school->name,
                $a->programKeahlian?->nama ?? '-',
                $a->konsentrasiKeahlian?->nama ?? '-',
                ucfirst($a->admission_path),
                $a->previous_school,
                $a->father_name,
                $a->father_phone ?? '-',
                $a->father_occupation ?? '-',
                $a->mother_name,
                $a->mother_phone ?? '-',
                $a->mother_occupation ?? '-',
                $a->getStatusLabel(),
                $a->submission_date ? $a->submission_date->format('d/m/Y H:i') : '-',
                $a->academicYear->year,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return compact('content', 'filename');
    }

    // ──────────────────────────────────────────────
    //  Workflow / Status Transitions
    // ──────────────────────────────────────────────

    /**
     * Verify payment for a reguler-path applicant.
     *
     * @throws \LogicException
     */
    public function verifyPayment(Applicant $applicant): void
    {
        if ($applicant->status !== 'submitted') {
            throw new \LogicException('Status pendaftar tidak valid untuk verifikasi pembayaran.');
        }
        if ($applicant->admission_path === 'prestasi') {
            throw new \LogicException('Pendaftar jalur prestasi tidak memerlukan verifikasi pembayaran. Gunakan verifikasi prestasi.');
        }

        $applicant->update([
            'status' => 'payment_verified',
            'payment_verified_at' => now(),
        ]);

        $this->sendWhatsApp($applicant, function ($phone) use ($applicant) {
            $message = "✅ *Pembayaran Terverifikasi*\n\n";
            $message .= "Halo {$applicant->full_name},\n\n";
            $message .= "Pembayaran pendaftaran Anda telah kami verifikasi.\n\n";
            $message .= "📄 *Langkah Selanjutnya:*\n";
            $message .= "Silakan upload dokumen berikut:\n";
            $message .= "1. Fotocopy Kartu Keluarga (KK)\n";
            $message .= "2. Fotocopy Akta Kelahiran\n";
            $message .= "3. Fotocopy Ijazah/SKHUN\n";
            $message .= "4. Fotocopy Raport Semester 5 & 6\n\n";
            $message .= "📸 *Pas Foto 3x4 (2 lembar)* diserahkan saat daftar ulang.\n\n";
            $message .= "Link Upload: " . url('/pendaftaran/cek-status') . "\n\n";
            $message .= "Terima kasih.\n";
            $message .= "*{$applicant->school->name}*";

            $this->whatsappService->sendMessage($phone, $message);
        }, 'wa_send_psb_payment');
    }

    /**
     * Verify prestasi for a prestasi-path applicant.
     *
     * @throws \LogicException
     */
    public function verifyPrestasi(Applicant $applicant): void
    {
        if ($applicant->status !== 'submitted') {
            throw new \LogicException('Status pendaftar tidak valid untuk verifikasi prestasi.');
        }
        if ($applicant->admission_path !== 'prestasi') {
            throw new \LogicException('Verifikasi prestasi hanya untuk pendaftar jalur prestasi.');
        }

        $applicant->update([
            'status' => 'prestasi_verified',
            'prestasi_verified_at' => now(),
        ]);

        $this->sendWhatsApp($applicant, function ($phone) use ($applicant) {
            $prestasiDetail = 'Jalur Prestasi';
            $achievement = $applicant->achievements()->first();
            if ($achievement) {
                $prestasiDetail = $achievement->achievement_name;
            }

            $variables = [
                'nama' => $applicant->full_name,
                'nomor_registrasi' => $applicant->registration_number,
                'prestasi_detail' => $prestasiDetail,
                'upload_url' => url('/pendaftaran/cek-status'),
                'deadline' => now()->addDays(14)->format('d F Y'),
            ];

            $this->whatsappService->sendTemplate($phone, 'psb.prestasi.approved', $variables);
        }, 'wa_send_psb_registration');
    }

    /**
     * Reject prestasi and switch applicant to reguler path.
     *
     * @throws \LogicException
     */
    public function rejectPrestasi(Applicant $applicant, string $reason): void
    {
        if ($applicant->status !== 'submitted') {
            throw new \LogicException('Status pendaftar tidak valid untuk penolakan prestasi.');
        }
        if ($applicant->admission_path !== 'prestasi') {
            throw new \LogicException('Penolakan prestasi hanya untuk pendaftar jalur prestasi.');
        }

        $applicant->update([
            'admission_path' => 'reguler',
            'prestasi_rejection_reason' => $reason,
        ]);

        $this->sendWhatsApp($applicant, function ($phone) use ($applicant, $reason) {
            $fee = $applicant->school_id == 3 ? 300000 : 50000;

            $variables = [
                'nama' => $applicant->full_name,
                'nomor_registrasi' => $applicant->registration_number,
                'alasan_penolakan' => $reason,
                'biaya' => number_format($fee, 0, ',', '.'),
                'bank_name' => 'BCA',
                'bank_account' => '1234567890',
                'bank_holder' => 'Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)',
                'contact_phone' => '082373642864',
                'contact_email' => 'psb@pembdanias.sch.id',
            ];

            $this->whatsappService->sendTemplate($phone, 'psb.prestasi.rejected', $variables);
        }, 'wa_send_psb_registration');
    }

    /**
     * Verify documents for an applicant (reguler or prestasi path).
     *
     * @throws \LogicException
     */
    public function verifyDocument(Applicant $applicant): void
    {
        $validStatuses = ['payment_verified', 'prestasi_verified'];

        if (!in_array($applicant->status, $validStatuses)) {
            throw new \LogicException('Status pendaftar tidak valid untuk verifikasi dokumen. Status harus "Pembayaran Terverifikasi" atau "Prestasi Terverifikasi".');
        }

        $applicant->update([
            'status' => 'document_verified',
            'document_verified_at' => now(),
        ]);

        $requiresTest = $applicant->school->requires_test ?? false;

        $this->sendWhatsApp($applicant, function ($phone) use ($applicant, $requiresTest) {
            $message = "✅ *Dokumen Terverifikasi*\n\n";
            $message .= "Halo {$applicant->full_name},\n\n";
            $message .= "Dokumen pendaftaran Anda telah diverifikasi.\n\n";

            if ($requiresTest) {
                $testType = $applicant->school->test_type ?? 'tes';
                $message .= "📝 *Informasi {$testType}:*\n";
                $message .= "Kami akan menghubungi Anda untuk jadwal {$testType} masuk.\n\n";
            } else {
                $message .= "🎉 *Selamat!*\n";
                $message .= "Anda sudah dapat melanjutkan ke tahap daftar ulang.\n";
                $message .= "Informasi daftar ulang akan segera dikirimkan.\n\n";
            }

            $message .= "Terima kasih.\n";
            $message .= "*{$applicant->school->name}*";

            $this->whatsappService->sendMessage($phone, $message);
        }, 'wa_send_psb_test_schedule');
    }

    /**
     * Accept an applicant.
     *
     * @throws \LogicException
     */
    public function acceptApplicant(Applicant $applicant): void
    {
        $validStatuses = ['document_verified', 'scored'];

        if (!in_array($applicant->status, $validStatuses)) {
            throw new \LogicException('Status pendaftar tidak valid untuk diterima.');
        }

        $applicant->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->sendWhatsApp($applicant, function ($phone) use ($applicant) {
            $message = "🎉 *SELAMAT! Anda DITERIMA*\n\n";
            $message .= "Halo {$applicant->full_name},\n\n";
            $message .= "Selamat! Anda diterima di:\n";
            $message .= "🏫 {$applicant->school->name}\n";
            $message .= "📊 Nilai Akhir: {$applicant->final_score}\n";

            if ($applicant->ranking) {
                $message .= "🏆 Ranking: #{$applicant->ranking}\n";
            }

            $message .= "\nSilakan lakukan daftar ulang segera.\n\n";
            $message .= "Terima kasih.\n";
            $message .= "*{$applicant->school->name}*";

            $this->whatsappService->sendMessage($phone, $message);
        }, 'wa_send_psb_acceptance');
    }

    /**
     * Reject an applicant.
     *
     * @throws \LogicException
     */
    public function rejectApplicant(Applicant $applicant): void
    {
        if ($applicant->status !== 'scored') {
            throw new \LogicException('Status pendaftar tidak valid untuk ditolak.');
        }

        $applicant->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  Test Scores
    // ──────────────────────────────────────────────

    /**
     * Save test scores for an applicant.
     *
     * @throws \LogicException
     */
    public function saveTestScores(Applicant $applicant, string $testDate, array $scores): void
    {
        DB::transaction(function () use ($applicant, $testDate, $scores) {
            $applicant->update([
                'test_date' => $testDate,
                'status' => 'tested',
                'tested_at' => now(),
            ]);

            foreach ($scores as $scoreData) {
                ApplicantTestScore::updateOrCreate(
                    [
                        'applicant_id' => $applicant->id,
                        'subject' => $scoreData['subject'],
                    ],
                    [
                        'score' => $scoreData['score'],
                    ]
                );
            }

            $totalScore = array_sum(array_column($scores, 'score'));
            $averageScore = $totalScore / count($scores);

            $applicant->update([
                'total_score' => $totalScore,
                'average_score' => $averageScore,
                'status' => 'scored',
                'scored_at' => now(),
            ]);
        });
    }

    // ──────────────────────────────────────────────
    //  WhatsApp Helper
    // ──────────────────────────────────────────────

    /**
     * Send a WhatsApp message with error handling.
     */
    protected function sendWhatsApp(Applicant $applicant, callable $sender, string $settingKey = 'wa_send_psb_registration'): void
    {
        if (!\App\Models\Setting::getValue($settingKey, true)) {
            Log::info("WhatsApp notification disabled for setting: {$settingKey}");
            return;
        }

        try {
            $phone = $applicant->phone ?? $applicant->father_phone ?? $applicant->mother_phone;
            if ($phone) {
                $sender($phone);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
        }
    }
    // ──────────────────────────────────────────────
    //  Migration
    // ──────────────────────────────────────────────

    /**
     * Migrate Applicant to Student (Active).
     */
    public function migrateToStudent(Applicant $applicant): \App\Models\Student
    {
        if (!$applicant->canMigrateToStudent()) {
            throw new \LogicException('Status pendaftar tidak valid untuk migrasi ke siswa.');
        }

        return DB::transaction(function () use ($applicant) {
            // Check for existing student
            if (\App\Models\Student::where('nisn', $applicant->nisn)->where('school_id', $applicant->school_id)->exists()) {
                throw new \LogicException("Siswa dengan NISN {$applicant->nisn} sudah terdaftar.");
            }

            // Handle Photo
            $photoPath = null;
            if ($applicant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($applicant->photo_path)) {
                $ext = pathinfo($applicant->photo_path, PATHINFO_EXTENSION);
                // Use NISN or RegNum for filename to avoid collisions
                $newFilename = 'students/photos/' . ($applicant->nisn ?? $applicant->registration_number) . '.' . $ext;
                
                // Ensure directory exists
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('students/photos')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('students/photos');
                }
                
                \Illuminate\Support\Facades\Storage::disk('public')->copy($applicant->photo_path, $newFilename);
                $photoPath = $newFilename;
            }

            // Determine Entry Year
            $entryYear = $applicant->academicYear->start_date->year;

            // Create Student
            $student = \App\Models\Student::create([
                'user_id' => null, // No user account yet
                'school_id' => $applicant->school_id,
                'nisn' => $applicant->nisn,
                'nis' => null, // Empty for now
                'full_name' => $applicant->full_name,
                'gender' => $applicant->gender,
                'birth_place' => $applicant->birth_place,
                'birth_date' => $applicant->birth_date,
                'religion' => $applicant->religion,
                'address' => $applicant->address,
                'phone' => $applicant->phone,
                'photo' => $photoPath,
                'entry_year' => $entryYear,
                'status' => 'calon', // Initial status
                'parent_name' => $applicant->father_name,
                'parent_phone' => $applicant->father_phone,
                'previous_school' => $applicant->previous_school,
            ]);

            // Update Applicant
            $applicant->update([
                'student_id' => $student->id,
                'status' => 'registered',
            ]);

            // Log History
            $student->statusHistories()->create([
                'school_id' => $applicant->school_id,
                'from_status' => 'pendaftar',
                'to_status' => 'calon',
                'reason' => 'Migrasi dari PSB',
                'notes' => 'No. Reg: ' . $applicant->registration_number,
                'effective_date' => now(),
                'changed_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);

            return $student;
        });
    }
}
