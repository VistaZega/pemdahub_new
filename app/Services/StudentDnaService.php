<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CbtExamResult;
use App\Models\FinalGrade;
use App\Models\FinalProject;
use App\Models\Grade;
use App\Models\LmsEnrollment;
use App\Models\LmsSubmission;
use App\Models\PklLog;
use App\Models\PklPlacement;
use App\Models\ReputationLog;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentCounselingRecord;
use App\Models\StudentDiagnosticAssessment;
use App\Models\StudentDnaAiReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StudentDnaService
{
    /**
     * Kamus standar profil belajar dalam Bahasa Indonesia baku (bebas jargon Inggris/vonis kaku).
     */
    private const PROFILE_CATALOG = [
        'logic' => [
            'title'       => 'Analis Logis & Pemecah Masalah',
            'short'       => 'Analis Logis',
            'badge_icon'  => 'fa-brain',
            'color'       => 'from-blue-600 to-indigo-600',
            'tagline'     => 'Berpikir Kritis, Terstruktur, dan Berorientasi Solusi Eksakta',
            'core_trait'  => 'menunjukkan ketajaman bernalar logis, ketelitian membaca pola data, serta kemampuan menyelesaikan persoalan hitungan dan konsep ilmiah secara runtut',
        ],
        'communication' => [
            'title'       => 'Komunikator & Artikulator Bahasa',
            'short'       => 'Komunikator Bahasa',
            'badge_icon'  => 'fa-comments',
            'color'       => 'from-emerald-600 to-teal-600',
            'tagline'     => 'Cakap Literasi, Komunikasi Persuasif, dan Penyampaian Gagasan',
            'core_trait'  => 'memiliki kekuatan memahami teks bacaan, merangkai argumen secara runtut, serta mengomunikasikan ide dengan bahasa yang jelas',
        ],
        'technical' => [
            'title'       => 'Praktisi Teknis & Rekayasa Terapan',
            'short'       => 'Praktisi Teknis Terapan',
            'badge_icon'  => 'fa-tools',
            'color'       => 'from-indigo-600 to-purple-600',
            'tagline'     => 'Terampil Praktik Lapangan, Penguasaan Alat, dan Eksekusi Karya',
            'core_trait'  => 'menonjol pada pembelajaran berbasis praktik, pengoperasian perangkat atau prosedur teknis kejuruan, dan penerapan konsep ke solusi nyata',
        ],
        'social' => [
            'title'       => 'Pemimpin Sosial & Kolaborator',
            'short'       => 'Pemimpin Kolaboratif',
            'badge_icon'  => 'fa-users',
            'color'       => 'from-amber-600 to-orange-600',
            'tagline'     => 'Mampu Memimpin Tim, Berempati Tinggi, dan Membangun Kerja Sama',
            'core_trait'  => 'kuat dalam memahami dinamika masyarakat, bekerja sama lintas peran, serta mengambil tanggung jawab koordinasi di lingkungan sekolah',
        ],
        'creative' => [
            'title'       => 'Kreator Visual & Perancang Inovatif',
            'short'       => 'Kreator Inovatif',
            'badge_icon'  => 'fa-palette',
            'color'       => 'from-purple-600 to-pink-600',
            'tagline'     => 'Berdaya Cipta Tinggi, Peka Estetika, dan Kaya Gagasan Orisinal',
            'core_trait'  => 'memiliki kepekaan estetika visual, daya imajinasi konstruktif, dan kecenderungan mengolah gagasan menjadi karya orisinal',
        ],
        'discipline' => [
            'title'       => 'Pelaksana Tekun & Berdisiplin Tinggi',
            'short'       => 'Pelaksana Tekun',
            'badge_icon'  => 'fa-award',
            'color'       => 'from-rose-600 to-red-600',
            'tagline'     => 'Konsisten Menjalankan Tugas, Tepat Waktu, dan Bertanggung Jawab',
            'core_trait'  => 'konsisten menjaga kehadiran sekolah, ketepatan mengumpulkan tugas, dan ketertiban sikap dalam kegiatan belajar harian',
        ],
    ];

    /**
     * Menghasilkan analisis lengkap DNA Akademik Siswa 360° (berbasis bukti data nyata & Bahasa Indonesia baku).
     */
    public function analyze(Student $student): array
    {
        $student->loadMissing([
            'school.principal',
            'currentClassroom.homeroomTeacher',
            'classrooms.homeroomTeacher',
            'major',
            'applicant',
        ]);

        $schoolType = strtoupper($student->school->type ?? 'SMA');
        $vocationalMajor = $schoolType === 'SMK' ? $this->detectSmkMajor($student) : null;

        // 1. Profil nilai per mata pelajaran + perbandingan rata-rata sekolah
        $subjectProfile = $this->buildSubjectProfile($student);

        // 2. Presensi RFID & harian riil
        $totalAttendance = Attendance::where('student_id', $student->id)->count();
        $presentAttendance = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['present', 'hadir', 'h', 'H'])
            ->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : null;

        // 3. Rekam jejak 360° nyata (CBT, LMS, Proyek Akhir, PKL, Prestasi vs Catatan Pelanggaran, Ekskul)
        $evidence360 = $this->collectEvidence360($student);

        // 4. Poin apresiasi & sikap
        $positivePoints = 0;
        $negativePoints = 0;
        if ($student->user_id) {
            $positivePoints = (int) ReputationLog::where('user_id', $student->user_id)->where('points', '>', 0)->sum('points');
            $negativePoints = (int) ReputationLog::where('user_id', $student->user_id)->where('points', '<', 0)->sum('points');
        }

        // 5. Asesmen diagnostik minat & cita-cita siswa
        $diagnostic = StudentDiagnosticAssessment::where('student_id', $student->id)->first();

        // 6. Hitung 6 dimensi potensi & sumber bukti masing-masing
        $dimensionCalc = $this->calculateDimensions(
            $subjectProfile,
            $attendanceRate,
            $evidence360,
            $positivePoints,
            $negativePoints,
            $diagnostic,
            $schoolType,
            $vocationalMajor
        );

        $dimensionScores = $dimensionCalc['scores'];
        $dimensionSources = $dimensionCalc['sources'];

        // 7. Kelengkapan sumber data (bukan skor kecerdasan siswa)
        $confidenceScore = $this->calculateConfidenceScore($subjectProfile, $attendanceRate, $evidence360, $diagnostic);
        $confidenceLabel = $this->getConfidenceLabel($confidenceScore);

        // 8. Profil belajar dominan ganda (Primary + Secondary) agar presensi tidak membajak profil bakat
        $archetype = $this->determineArchetype(
            $dimensionScores,
            $subjectProfile,
            $evidence360,
            $schoolType,
            $vocationalMajor,
            $diagnostic
        );

        // 9. Rekomendasi studi, karier, keselarasan cita-cita, dan rencana pendampingan personal
        $recommendations = $this->generateRecommendations(
            $dimensionScores,
            $archetype,
            $schoolType,
            $vocationalMajor,
            $subjectProfile,
            $evidence360,
            $diagnostic
        );

        // 10. Sidik jari perubahan data & baca laporan AI (ClassAI) yang tersimpan
        $fingerprint = $this->computeDataFingerprint(
            $dimensionScores,
            $subjectProfile,
            $evidence360,
            $diagnostic,
            $attendanceRate,
            $vocationalMajor
        );

        $aiBundle = $this->loadCachedAiReport($student->id, $fingerprint);

        // 11. Identitas resmi database
        $parentName = $student->parent_name
            ?: ($student->guardian_name
            ?: ($student->applicant ? ($student->applicant->father_name ?: $student->applicant->mother_name) : null)
            ?: '-');

        $principalName = $student->school?->principal?->full_name
            ?: ($student->school?->principal_name ?: 'Kepala Sekolah');

        $yayasan = School::where('type', 'yayasan')->first();
        $foundationName = $yayasan?->name ?: 'YAYASAN PERGURUAN PEMBANGUNAN DAERAH NIAS (PEMBDA)';
        $foundationAddress = $yayasan?->address ?: 'Jl. Pelita No. 9 Kelurahan Ilir, Kota Gunungsitoli, Sumatera Utara (22815)';
        $foundationEmail = 'perguruanpembdanias@gmail.com';
        $foundationPhone = $yayasan?->phone ?: '0812-6088-2999';
        $foundationWebsite = $yayasan?->website ?: 'https://perguruanpembda.com';

        $homeroomTeacher = $student->currentClassroom->first()?->homeroomTeacher?->full_name
            ?: ($student->classrooms->first()?->homeroomTeacher?->full_name ?: '-');

        $classroomName = $student->currentClassroom->first()?->class_name
            ?: ($student->currentClassroom->first()?->name
            ?: ($student->classrooms->first()?->class_name ?: '-'));

        return [
            'student'           => $student,
            'school_type'       => $schoolType,
            'vocational_major'  => $vocationalMajor,
            'scores'            => $dimensionScores,
            'dimension_sources' => $dimensionSources,
            'confidence_score'  => $confidenceScore,
            'confidence_label'  => $confidenceLabel,
            'archetype'         => $archetype,
            'recommendations'   => $recommendations,
            'subject_profile'   => $subjectProfile,
            'evidence_360'      => $evidence360,
            'extracurriculars'  => $evidence360['extracurriculars'],
            'metrics'           => [
                'total_grades'        => $subjectProfile['subject_count'],
                'total_grade_records' => $subjectProfile['raw_grade_count'],
                'attendance_rate'     => $attendanceRate,
                'attendance_display'  => $attendanceRate !== null ? ($attendanceRate . '%') : 'Belum Tercatat',
                'cbt_average'         => $evidence360['cbt_avg'],
                'lms_average'         => $evidence360['lms_avg'],
                'lms_ontime_rate'     => $evidence360['lms_ontime_rate'],
                'reputation_points'   => $positivePoints + $negativePoints,
                'has_diagnostic'      => (bool) $diagnostic,
                'achievements_count'  => count($evidence360['achievements']),
                'counseling_sessions' => $evidence360['counseling_total'],
            ],
            'diagnostic'        => $diagnostic,
            'data_fingerprint'  => $fingerprint,
            'ai_report'         => $aiBundle['report'],
            'ai_meta'           => $aiBundle['meta'],
            // Alias tingkat atas agar view mobile tetap kompatibel
            'career_recommendations'  => $recommendations['career_recommendations'] ?? [],
            'college_recommendations' => $recommendations['college_recommendations'] ?? [],
            'learning_strategy'       => $recommendations['learning_strategy'] ?? '',
            'database_identity' => [
                'parent_name'        => $parentName,
                'principal_name'     => $principalName,
                'foundation_name'    => $foundationName,
                'foundation_address' => $foundationAddress,
                'foundation_email'   => $foundationEmail,
                'foundation_phone'   => $foundationPhone,
                'foundation_website' => $foundationWebsite,
                'homeroom_teacher'   => $homeroomTeacher,
                'classroom_name'     => $classroomName,
                'school_name'        => $student->school?->name ?? '-',
                'school_npsn'        => $student->school?->npsn ?? '-',
                'school_address'     => $student->school?->address ?? '-',
                'school_phone'       => $student->school?->phone ?? '-',
                'student_address'    => $student->address ?: ($student->guardian_address ?: '-'),
                'student_nis'        => $student->formatted_nis ?: ($student->nis ?: '-'),
            ],
        ];
    }

    /**
     * Membangkitkan narasi analisis mendalam personal berbasis data riil 360° via API ClassAI (GeminiService)
     * dan menyimpannya ke database/cache agar cepat serta tidak boros kuota API.
     */
    public function generateAiAnalysis(Student $student, bool $forceRefresh = false): array
    {
        $analysis = $this->analyze($student);
        $gemini = app(GeminiService::class);
        $providerInfo = $gemini->getProviderInfo();

        if (!$forceRefresh && !empty($analysis['ai_report']) && !($analysis['ai_meta']['is_stale'] ?? true)) {
            return [
                'success'   => true,
                'cached'    => true,
                'ai_report' => $analysis['ai_report'],
                'ai_meta'   => $analysis['ai_meta'],
            ];
        }

        if (!$providerInfo['configured']) {
            return [
                'success'   => false,
                'message'   => 'Kunci API ClassAI/AI belum dikonfigurasi pada sistem. Menampilkan pemetaan terstruktur berbasis data sekolah.',
                'ai_report' => $analysis['ai_report'],
                'ai_meta'   => $analysis['ai_meta'],
            ];
        }

        [$systemPrompt, $userPrompt] = $this->buildClassAiPrompts($student, $analysis);
        $rawJson = $gemini->generateStructuredJson($systemPrompt, $userPrompt);

        if (!is_array($rawJson) || empty(trim((string) ($rawJson['executive_summary'] ?? '')))) {
            return [
                'success'   => false,
                'message'   => 'Layanan ClassAI sedang padat atau mengembalikan format yang belum lengkap. Silakan coba beberapa saat lagi.',
                'ai_report' => $analysis['ai_report'],
                'ai_meta'   => $analysis['ai_meta'],
            ];
        }

        $normalized = $this->normalizeAiPayload($rawJson, $analysis);
        $now = now();

        if (Schema::hasTable('student_dna_ai_reports')) {
            try {
                StudentDnaAiReport::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'data_fingerprint' => $analysis['data_fingerprint'],
                        'provider'         => $providerInfo['provider'],
                        'model_used'       => $providerInfo['model'],
                        'ai_payload'       => $normalized,
                        'generated_at'     => $now,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Gagal menyimpan laporan ClassAI ke DB: ' . $e->getMessage());
            }
        }

        Cache::put("student_dna_ai_{$student->id}", [
            'data_fingerprint' => $analysis['data_fingerprint'],
            'provider'         => $providerInfo['provider'],
            'model_used'       => $providerInfo['model'],
            'ai_payload'       => $normalized,
            'generated_at'     => $now->toIso8601String(),
        ], now()->addDays(14));

        return [
            'success'   => true,
            'cached'    => false,
            'ai_report' => $normalized,
            'ai_meta'   => [
                'provider'            => $providerInfo['provider'],
                'provider_label'      => $providerInfo['provider_label'],
                'model'               => $providerInfo['model'],
                'generated_at'        => $now->translatedFormat('d M Y, H:i'),
                'is_stale'            => false,
                'provider_configured' => true,
            ],
        ];
    }

    /**
     * Kumpulkan dan agregasikan nilai unik per mata pelajaran + tolok ukur rata-rata sekolah.
     */
    private function buildSubjectProfile(Student $student): array
    {
        $regularGrades = Grade::where('student_id', $student->id)->with('subject')->get();
        $finalGrades = FinalGrade::where('student_id', $student->id)
            ->whereNotNull('final_score')
            ->with('subject')
            ->get();

        $bySubject = [];

        foreach ($finalGrades as $fg) {
            if (!$fg->subject_id || !$fg->subject) {
                continue;
            }
            $sid = $fg->subject_id;
            if (!isset($bySubject[$sid])) {
                $bySubject[$sid] = [
                    'subject_id'   => $sid,
                    'subject_name' => $fg->subject->subject_name ?: ($fg->subject->name ?? 'Mata Pelajaran'),
                    'subject_code' => $fg->subject->subject_code ?: ($fg->subject->code ?? '-'),
                    'final_scores' => [],
                    'reg_scores'   => [],
                ];
            }
            $bySubject[$sid]['final_scores'][] = (float) $fg->final_score;
        }

        foreach ($regularGrades as $g) {
            if (!$g->subject_id || !$g->subject) {
                continue;
            }
            $sid = $g->subject_id;
            if (!isset($bySubject[$sid])) {
                $bySubject[$sid] = [
                    'subject_id'   => $sid,
                    'subject_name' => $g->subject->subject_name ?: ($g->subject->name ?? 'Mata Pelajaran'),
                    'subject_code' => $g->subject->subject_code ?: ($g->subject->code ?? '-'),
                    'final_scores' => [],
                    'reg_scores'   => [],
                ];
            }
            $bySubject[$sid]['reg_scores'][] = (float) $g->score;
        }

        $schoolAvgs = $this->loadSchoolSubjectAverages($student->school_id, array_keys($bySubject));

        $items = [];
        foreach ($bySubject as $sid => $entry) {
            $score = !empty($entry['final_scores'])
                ? (array_sum($entry['final_scores']) / count($entry['final_scores']))
                : (array_sum($entry['reg_scores']) / max(1, count($entry['reg_scores'])));

            $score = round($score, 1);
            $schoolAvg = $schoolAvgs[$sid] ?? null;
            $delta = $schoolAvg !== null ? round($score - $schoolAvg, 1) : null;
            $dimension = $this->classifySubjectDimension($entry['subject_name'], $entry['subject_code']);

            $items[] = [
                'subject_id'   => $sid,
                'subject_name' => $entry['subject_name'],
                'subject_code' => $entry['subject_code'],
                'score'        => $score,
                'school_avg'   => $schoolAvg,
                'delta'        => $delta,
                'dimension'    => $dimension,
            ];
        }

        usort($items, fn ($a, $b) => $b['score'] <=> $a['score']);

        $allScores = array_column($items, 'score');
        $studentAvg = !empty($allScores) ? round(array_sum($allScores) / count($allScores), 1) : null;

        $topSubjects = array_slice($items, 0, min(3, count($items)));

        $weakSubjects = [];
        if (count($items) >= 4) {
            $ascending = array_reverse($items);
            $weakSubjects = array_slice($ascending, 0, 2);
        } elseif (count($items) >= 2) {
            $weakSubjects = [end($items)];
        }

        $byDimension = [
            'logic'         => [],
            'communication' => [],
            'technical'     => [],
            'social'        => [],
            'creative'      => [],
        ];
        foreach ($items as $it) {
            if (isset($byDimension[$it['dimension']])) {
                $byDimension[$it['dimension']][] = $it;
            }
        }

        return [
            'subjects'        => $items,
            'by_dimension'    => $byDimension,
            'top_subjects'    => $topSubjects,
            'weak_subjects'   => $weakSubjects,
            'subject_count'   => count($items),
            'raw_grade_count' => $regularGrades->count() + $finalGrades->count(),
            'student_avg'     => $studentAvg,
        ];
    }

    /**
     * Ambil rata-rata unit sekolah per mata pelajaran untuk menghitung keunggulan relatif siswa.
     */
    private function loadSchoolSubjectAverages(?int $schoolId, array $subjectIds): array
    {
        if (empty($subjectIds) || !$schoolId) {
            return [];
        }

        try {
            $finalAvgs = FinalGrade::query()
                ->join('students', 'final_grades.student_id', '=', 'students.id')
                ->where('students.school_id', $schoolId)
                ->whereIn('final_grades.subject_id', $subjectIds)
                ->whereNotNull('final_grades.final_score')
                ->groupBy('final_grades.subject_id')
                ->select('final_grades.subject_id', DB::raw('AVG(final_grades.final_score) as avg_score'))
                ->pluck('avg_score', 'subject_id')
                ->map(fn ($v) => round((float) $v, 1))
                ->toArray();

            $missing = array_diff($subjectIds, array_keys($finalAvgs));
            if (!empty($missing)) {
                $regAvgs = Grade::query()
                    ->join('students', 'grades.student_id', '=', 'students.id')
                    ->where('students.school_id', $schoolId)
                    ->whereIn('grades.subject_id', $missing)
                    ->groupBy('grades.subject_id')
                    ->select('grades.subject_id', DB::raw('AVG(grades.score) as avg_score'))
                    ->pluck('avg_score', 'subject_id')
                    ->map(fn ($v) => round((float) $v, 1))
                    ->toArray();
                $finalAvgs = $finalAvgs + $regAvgs;
            }

            return $finalAvgs;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Klasifikasikan mata pelajaran (termasuk Biologi, Akuntansi, dan seluruh rumpun kejuruan SMK) ke 5 dimensi inti.
     */
    private function classifySubjectDimension(string $subjectName, string $subjectCode = ''): string
    {
        $text = strtolower(trim($subjectName . ' ' . $subjectCode));

        // 1. Keahlian Vokasi & Teknis Terapan (prioritaskan istilah produktif SMK agar tidak salah masuk)
        if (preg_match('/\b(kejuruan|produktif|ddtk|konsentrasi|komputer|jaringan|tkj|tjkt|acp|mesin|otomotif|tkr|tsm|tbsm|motor|chasis|engine|dpib|bangunan|konstruksi|autocad|gambar\s*teknik|elektronika|audio\s*video|tav|te|mikrokontroler|listrik|rekayasa|informatika|koding|pemrograman|praktik)\b/i', $text)) {
            return 'technical';
        }

        // 2. Logika, Matematika & Sains Analitik
        if (preg_match('/\b(matematika|mtk|fisika|kimia|biologi|ipa|ipas|statistika|kalkulus|aljabar|geometri|algoritma)\b/i', $text)) {
            return 'logic';
        }

        // 3. Komunikasi & Literasi Bahasa
        if (preg_match('/\b(indonesia|inggris|bahasa|sastra|literasi|komunikasi|bind|bing|b\.ind|b\.ing)\b/i', $text)) {
            return 'communication';
        }

        // 4. Sosial, Bisnis & Kewarganegaraan
        if (preg_match('/\b(ips|sejarah|ppkn|pancasila|sosiologi|ekonomi|geografi|akuntansi|bisnis|kewirausahaan|kik|agama|hukum|bk|bp)\b/i', $text)) {
            return 'social';
        }

        // 5. Kreativitas, Desain & Seni
        if (preg_match('/\b(seni|budaya|sbud|prakarya|pkry|desain|kreatif|multimedia|dkv|arsitektur|musik|rupa)\b/i', $text)) {
            return 'creative';
        }

        return 'social';
    }

    /**
     * Deteksi jurusan asli siswa SMK tanpa pernah menebak dari nama kelas gabungan.
     */
    private function detectSmkMajor(Student $student): ?array
    {
        $mapMajorCode = function (string $text): ?array {
            $upper = strtoupper($text);
            if (preg_match('/\b(TE|TAV|ELEKTRONIKA|AUDIO\s*VIDEO)\b/', $upper)) {
                return ['code' => 'TE', 'label' => 'Teknik Elektronika / Audio Video'];
            }
            if (preg_match('/\b(DPIB|BANGUNAN|PEMODELAN)\b/', $upper)) {
                return ['code' => 'DPIB', 'label' => 'Desain Pemodelan dan Informasi Bangunan'];
            }
            if (preg_match('/\b(TKR|TO|KENDARAAN\s*RINGAN|OTOMOTIF)\b/', $upper) && !preg_match('/\b(TSM|TBSM|SEPEDA\s*MOTOR)\b/', $upper)) {
                return ['code' => 'TKR', 'label' => 'Teknik Kendaraan Ringan Otomotif'];
            }
            if (preg_match('/\b(TSM|TBSM|SEPEDA\s*MOTOR)\b/', $upper)) {
                return ['code' => 'TSM', 'label' => 'Teknik Sepeda Motor'];
            }
            if (preg_match('/\b(TKJ|TJKT|ACP|KOMPUTER\s*&?\s*JARINGAN)\b/', $upper)) {
                return ['code' => 'TKJ', 'label' => 'Teknik Komputer dan Jaringan'];
            }
            return null;
        };

        if ($student->major) {
            $mText = ($student->major->code ?? $student->major->major_code ?? '') . ' ' . ($student->major->name ?? $student->major->major_name ?? '');
            if ($detected = $mapMajorCode($mText)) {
                return $detected;
            }
        }

        // Cek mapel produktif LMS aktif saat ini
        try {
            $lmsSubjects = LmsEnrollment::where('lms_enrollments.student_id', $student->id)
                ->whereIn('lms_enrollments.status', ['enrolled', 'in_progress', 'completed'])
                ->join('lms_classes', 'lms_enrollments.lms_class_id', '=', 'lms_classes.id')
                ->join('lms_courses', 'lms_classes.course_id', '=', 'lms_courses.id')
                ->leftJoin('subjects', 'lms_courses.subject_id', '=', 'subjects.id')
                ->pluck('subjects.name')
                ->filter()
                ->toArray();

            foreach ($lmsSubjects as $sName) {
                $kw = VocationalMajorFilterService::getSubjectMajorKeywords($sName);
                if ($kw && ($detected = $mapMajorCode(implode(' ', $kw)))) {
                    return $detected;
                }
            }
        } catch (\Throwable $e) {
            // abaikan jika relasi LMS belum tersedia
        }

        // Cek kelas reguler non-gabungan (dilarang keras mengambil jurusan dari kelas gabungan!)
        $activeReg = $student->currentClassroom
            ->first(fn ($c) => !$c->is_combined && ($c->class_type ?? '') !== 'gabungan');
        if ($activeReg && ($detected = $mapMajorCode($activeReg->class_name ?? $activeReg->name ?? ''))) {
            return $detected;
        }

        $histReg = $student->classrooms
            ->first(fn ($c) => !$c->is_combined && ($c->class_type ?? '') !== 'gabungan');
        if ($histReg && ($detected = $mapMajorCode($histReg->class_name ?? $histReg->name ?? ''))) {
            return $detected;
        }

        return null;
    }

    /**
     * Kumpulkan rekam jejak nyata 360° (LMS, CBT, Tugas Akhir/Penelitian, PKL, Prestasi, Konseling, Ekskul).
     */
    private function collectEvidence360(Student $student): array
    {
        // LMS Submissions
        $lmsCount = 0;
        $lmsAvg = null;
        $lmsOntimeRate = null;
        try {
            $lmsRows = LmsSubmission::where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'graded', 'late'])
                ->get(['status', 'score']);
            $lmsCount = $lmsRows->count();
            $lmsGraded = $lmsRows->whereNotNull('score');
            $lmsAvg = $lmsGraded->isNotEmpty() ? round((float) $lmsGraded->avg('score'), 1) : null;
            $lmsLate = $lmsRows->where('status', 'late')->count();
            $lmsOntimeRate = $lmsCount > 0 ? round((($lmsCount - $lmsLate) / $lmsCount) * 100, 1) : null;
        } catch (\Throwable $e) {
            // abaikan bila tabel/kolom LMS belum siap
        }

        // CBT Results (tabel cbt_exams memakai exam_title, bukan title; cukup ambil skor)
        $cbtCount = 0;
        $cbtAvg = null;
        $cbtMax = null;
        try {
            $cbtRows = CbtExamResult::where('student_id', $student->id)
                ->get(['final_score', 'percentage_score']);
            $cbtCount = $cbtRows->count();
            $cbtScores = $cbtRows->map(fn ($r) => $r->final_score ?? $r->percentage_score)->filter(fn ($v) => $v !== null);
            $cbtAvg = $cbtScores->isNotEmpty() ? round((float) $cbtScores->avg(), 1) : null;
            $cbtMax = $cbtScores->isNotEmpty() ? round((float) $cbtScores->max(), 1) : null;
        } catch (\Throwable $e) {
            // abaikan bila tabel/kolom CBT belum siap
        }

        // Final Project / Penelitian Ilmiah
        $finalProject = null;
        try {
            $finalProject = FinalProject::where('student_id', $student->id)->latest('id')->first();
        } catch (\Throwable $e) {
            // abaikan bila modul Tugas Akhir belum tersedia
        }

        // PKL Placement & Logs
        $pklPlacement = null;
        $pklApprovedLogs = 0;
        $pklRecentLogs = [];
        try {
            $pklPlacement = PklPlacement::where('student_id', $student->id)->with('dudi')->latest('id')->first();
            if ($pklPlacement) {
                $pklLogs = PklLog::where('pkl_placement_id', $pklPlacement->id)
                    ->where('status', 'approved')
                    ->latest('log_date')
                    ->limit(10)
                    ->get(['activity', 'log_date']);
                $pklApprovedLogs = PklLog::where('pkl_placement_id', $pklPlacement->id)->where('status', 'approved')->count();
                $pklRecentLogs = $pklLogs->take(3)->pluck('activity')->filter()->values()->toArray();
            }
        } catch (\Throwable $e) {
            // abaikan bila modul PKL belum tersedia
        }

        // Counseling & Achievements
        $achievements = [];
        $violationsCount = 0;
        $counselingTotal = 0;
        try {
            $records = StudentCounselingRecord::where('student_id', $student->id)->latest('incident_date')->get();
            $counselingTotal = $records->count();
            foreach ($records as $rec) {
                $type = strtolower((string) ($rec->record_type ?? ''));
                if (in_array($type, ['penghargaan', 'prestasi', 'juara', 'achievement'])) {
                    $achievements[] = [
                        'title'    => $rec->competition_name ?: ($rec->title ?: 'Penghargaan Siswa'),
                        'ranking'  => $rec->ranking ?: null,
                        'level'    => $rec->achievement_level ?: null,
                        'category' => $rec->category ?: 'akademik',
                    ];
                } elseif (in_array($type, ['pelanggaran', 'disiplin', 'tatib']) || !empty($rec->severity)) {
                    $violationsCount++;
                }
            }
        } catch (\Throwable $e) {
            // abaikan bila tabel konseling belum tersedia
        }

        // Ekstrakurikuler
        $ekskuls = collect();
        try {
            $ekskuls = $student->extracurricularMembers()
                ->where('status', 'approved')
                ->with('extracurricular')
                ->get();
        } catch (\Throwable $e) {
            // abaikan bila relasi ekskul belum tersedia
        }

        return [
            'lms_count'         => $lmsCount,
            'lms_avg'           => $lmsAvg,
            'lms_ontime_rate'   => $lmsOntimeRate,
            'cbt_count'         => $cbtCount,
            'cbt_avg'           => $cbtAvg,
            'cbt_max'           => $cbtMax,
            'final_project'     => $finalProject ? [
                'title' => $finalProject->title,
                'type'  => $finalProject->type,
                'stage' => $finalProject->current_stage_name,
                'grade' => $finalProject->grade ? (float) $finalProject->grade : null,
            ] : null,
            'pkl'               => $pklPlacement ? [
                'company'       => $pklPlacement->dudi?->name ?: ($pklPlacement->company_name ?: 'Mitra Industri DUDI'),
                'status'        => $pklPlacement->status,
                'approved_logs' => $pklApprovedLogs,
                'recent_logs'   => $pklRecentLogs,
            ] : null,
            'achievements'      => $achievements,
            'violations_count'  => $violationsCount,
            'counseling_total'  => $counselingTotal,
            'extracurriculars'  => $ekskuls,
        ];
    }

    /**
     * Hitung 6 skor dimensi potensi secara proporsional tanpa meratakan skor ke nilai default generik.
     */
    private function calculateDimensions(
        array $subjectProfile,
        ?float $attendanceRate,
        array $evidence,
        int $positivePoints,
        int $negativePoints,
        ?StudentDiagnosticAssessment $diagnostic,
        string $schoolType,
        ?array $vocationalMajor
    ): array {
        $byDim = $subjectProfile['by_dimension'];
        $studentAvg = $subjectProfile['student_avg'];
        $hasGrades = $studentAvg !== null;
        $baseAnchor = $studentAvg ?? 74.0;
        $cluster = strtolower((string) ($diagnostic?->favorite_subject_cluster ?? ''));
        $ekskuls = $evidence['extracurriculars'];

        $dimAverage = static function (array $rows): ?float {
            if (empty($rows)) {
                return null;
            }
            $sum = array_sum(array_column($rows, 'score'));
            return round($sum / count($rows), 1);
        };

        $achBonus = function (array $keywords) use ($evidence): int {
            $bonus = 0;
            foreach ($evidence['achievements'] as $ach) {
                $txt = strtolower($ach['category'] . ' ' . $ach['title']);
                foreach ($keywords as $kw) {
                    if (str_contains($txt, $kw)) {
                        $bonus += 5;
                        break;
                    }
                }
            }
            return min(10, $bonus);
        };

        // 1. Logika & Analitik
        $logicRows = $byDim['logic'];
        $logicVal = $dimAverage($logicRows);
        $logicSrcParts = [];
        if ($logicVal !== null) {
            $logicBase = $evidence['cbt_avg'] !== null ? (($logicVal * 0.7) + ($evidence['cbt_avg'] * 0.3)) : $logicVal;
            $logicSrcParts[] = count($logicRows) . ' Mapel Sains & Matematika (Rerata ' . $logicVal . ')';
            if ($evidence['cbt_avg'] !== null) {
                $logicSrcParts[] = 'Rerata Ujian CBT (' . $evidence['cbt_avg'] . ')';
            }
        } elseif ($evidence['cbt_avg'] !== null) {
            $logicBase = $evidence['cbt_avg'];
            $logicSrcParts[] = 'Rerata Ujian CBT (' . $evidence['cbt_avg'] . ')';
        } else {
            $logicBase = max(60, $baseAnchor - ($cluster === 'eksak' ? 0 : 2));
            $logicSrcParts[] = $hasGrades ? 'Estimasi dari Rerata Mapel Umum (' . $baseAnchor . ')' : 'Menunggu Data Nilai Matematika/IPA';
        }
        if ($cluster === 'eksak') {
            $logicBase += 4;
            $logicSrcParts[] = 'Minat Utama Sains & Matematika';
        }
        $lBonus = $achBonus(['akademik', 'olimpiade', 'sains', 'matematika', 'fisika', 'kimia', 'biologi']);
        if ($lBonus > 0) {
            $logicBase += $lBonus;
            $logicSrcParts[] = 'Prestasi Lomba Akademik';
        }
        $logic = $diagnostic ? round(($logicBase * 0.72) + ($diagnostic->logic_self_score * 0.28)) : round($logicBase);

        // 2. Komunikasi & Bahasa
        $commRows = $byDim['communication'];
        $commVal = $dimAverage($commRows);
        $commSrcParts = [];
        if ($commVal !== null) {
            $commBase = $commVal;
            $commSrcParts[] = count($commRows) . ' Mapel Bahasa & Literasi (Rerata ' . $commVal . ')';
        } else {
            $commBase = max(60, $baseAnchor - ($cluster === 'bahasa' ? 0 : 2));
            $commSrcParts[] = $hasGrades ? 'Estimasi dari Rerata Mapel Umum (' . $baseAnchor . ')' : 'Menunggu Data Nilai Bahasa';
        }
        if ($cluster === 'bahasa') {
            $commBase += 4;
            $commSrcParts[] = 'Minat Utama Bahasa & Komunikasi';
        }
        $cBonus = $achBonus(['bahasa', 'pidato', 'debat', 'literasi', 'puisi', 'karya tulis']);
        if ($cBonus > 0) {
            $commBase += $cBonus;
            $commSrcParts[] = 'Prestasi Kebahasaan/Literasi';
        }
        $comm = $diagnostic ? round(($commBase * 0.72) + ($diagnostic->communication_self_score * 0.28)) : round($commBase);

        // 3. Keahlian Vokasi & Teknis Terapan
        $techRows = $byDim['technical'];
        $techVal = $dimAverage($techRows);
        $techSrcParts = [];
        if ($techVal !== null) {
            $techBase = $evidence['lms_avg'] !== null ? (($techVal * 0.75) + ($evidence['lms_avg'] * 0.25)) : $techVal;
            $techSrcParts[] = count($techRows) . ' Mapel Produktif & Praktik (Rerata ' . $techVal . ')';
        } elseif ($evidence['lms_avg'] !== null) {
            $techBase = $evidence['lms_avg'];
            $techSrcParts[] = 'Rerata Tugas Praktik LMS (' . $evidence['lms_avg'] . ')';
        } else {
            $techBase = $schoolType === 'SMK' ? min(88, $baseAnchor + 1) : max(60, $baseAnchor - 3);
            $techSrcParts[] = $hasGrades ? 'Estimasi Profil ' . $schoolType . ' (' . $baseAnchor . ')' : 'Menunggu Data Nilai Praktik/Kejuruan';
        }
        if ($vocationalMajor) {
            $techSrcParts[] = 'Jurusan ' . $vocationalMajor['code'];
        }
        if ($evidence['pkl'] && $evidence['pkl']['approved_logs'] > 0) {
            $techBase = min(98, $techBase + min(5, (int) ceil($evidence['pkl']['approved_logs'] / 3)));
            $techSrcParts[] = $evidence['pkl']['approved_logs'] . ' Logbook PKL Disetujui';
        }
        if ($evidence['final_project'] && $evidence['final_project']['grade'] !== null) {
            $techBase = round(($techBase * 0.75) + ($evidence['final_project']['grade'] * 0.25), 1);
            $techSrcParts[] = 'Nilai Tugas Akhir (' . $evidence['final_project']['grade'] . ')';
        }
        $techEkskuls = $ekskuls->filter(fn ($m) => in_array($m->extracurricular->category ?? '', ['sains_it']));
        if ($techEkskuls->isNotEmpty()) {
            $techBase = min(98, $techBase + 5);
            $techSrcParts[] = 'Ekskul ' . $techEkskuls->first()->extracurricular->name;
        }
        if ($cluster === 'vokasi') {
            $techBase += 4;
        }
        $tech = $diagnostic ? round(($techBase * 0.72) + ($diagnostic->technical_self_score * 0.28)) : round($techBase);

        // 4. Sosial & Kepemimpinan
        $socialRows = $byDim['social'];
        $socialVal = $dimAverage($socialRows);
        $socialSrcParts = [];
        if ($socialVal !== null) {
            $socialBase = $socialVal;
            $socialSrcParts[] = count($socialRows) . ' Mapel Sosial & Karakter (Rerata ' . $socialVal . ')';
        } else {
            $socialBase = max(60, $baseAnchor - ($cluster === 'sosial' ? 0 : 2));
            $socialSrcParts[] = $hasGrades ? 'Estimasi dari Rerata Mapel Umum (' . $baseAnchor . ')' : 'Menunggu Data Nilai IPS/PPKn';
        }
        if ($positivePoints > 0) {
            $socialBase = min(98, $socialBase + min(8, $positivePoints / 12));
            $socialSrcParts[] = '+' . $positivePoints . ' Poin Apresiasi';
        }
        $leadershipRoles = $ekskuls->filter(fn ($m) => in_array($m->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']));
        if ($leadershipRoles->isNotEmpty()) {
            $socialBase = min(98, $socialBase + 8);
            $firstLeader = $leadershipRoles->first();
            $socialSrcParts[] = $firstLeader->role_label . ' ' . $firstLeader->extracurricular->name;
        } elseif ($ekskuls->isNotEmpty()) {
            $socialBase = min(98, $socialBase + 3);
            $socialSrcParts[] = 'Anggota ' . $ekskuls->first()->extracurricular->name;
        }
        if ($cluster === 'sosial') {
            $socialBase += 4;
        }
        $social = $diagnostic ? round(($socialBase * 0.72) + ($diagnostic->social_self_score * 0.28)) : round($socialBase);

        // 5. Kreativitas & Inovasi
        $creaRows = $byDim['creative'];
        $creaVal = $dimAverage($creaRows);
        $creaSrcParts = [];
        if ($creaVal !== null) {
            $creaBase = $creaVal;
            $creaSrcParts[] = count($creaRows) . ' Mapel Seni & Rancangan (Rerata ' . $creaVal . ')';
        } else {
            $creaBase = max(60, $baseAnchor - ($cluster === 'seni' ? 0 : 3));
            $creaSrcParts[] = $hasGrades ? 'Estimasi dari Rerata Mapel Umum (' . $baseAnchor . ')' : 'Menunggu Data Nilai Seni/Prakarya';
        }
        $artsEkskuls = $ekskuls->filter(fn ($m) => in_array($m->extracurricular->category ?? '', ['seni_budaya', 'jurnalistik']));
        if ($artsEkskuls->isNotEmpty()) {
            $creaBase = min(98, max($creaBase, 78) + 6);
            $creaSrcParts[] = 'Aktif ' . $artsEkskuls->first()->extracurricular->name;
        }
        if ($cluster === 'seni') {
            $creaBase += 4;
            $creaSrcParts[] = 'Minat Utama Desain & Seni';
        }
        $crBonus = $achBonus(['seni', 'desain', 'poster', 'video', 'musik', 'kreatif', 'inovasi']);
        if ($crBonus > 0) {
            $creaBase += $crBonus;
            $creaSrcParts[] = 'Prestasi Karya Kreatif';
        }
        $creative = $diagnostic ? round(($creaBase * 0.72) + ($diagnostic->creative_self_score * 0.28)) : round($creaBase);

        // 6. Kedisiplinan & Ketekunan (presensi + ketepatan waktu tugas LMS)
        $discSrcParts = [];
        if ($attendanceRate !== null && $evidence['lms_ontime_rate'] !== null) {
            $discBase = ($attendanceRate * 0.65) + ($evidence['lms_ontime_rate'] * 0.35);
            $discSrcParts[] = 'Presensi ' . $attendanceRate . '% & Ketepatan Tugas LMS ' . $evidence['lms_ontime_rate'] . '%';
        } elseif ($attendanceRate !== null) {
            $discBase = $attendanceRate;
            $discSrcParts[] = 'Presensi RFID Sekolah (' . $attendanceRate . '%)';
        } elseif ($evidence['lms_ontime_rate'] !== null) {
            $discBase = $evidence['lms_ontime_rate'];
            $discSrcParts[] = 'Ketepatan Pengumpulan Tugas LMS (' . $evidence['lms_ontime_rate'] . '%)';
        } else {
            $discBase = 78;
            $discSrcParts[] = 'Menunggu Data Presensi & Ketepatan Tugas';
        }
        $scoutPaskibra = $ekskuls->filter(fn ($m) => in_array($m->extracurricular->category ?? '', ['pramuka', 'paskibraka', 'olahraga']));
        if ($scoutPaskibra->isNotEmpty()) {
            $discBase = min(98, $discBase + 4);
            $discSrcParts[] = 'Aktif ' . $scoutPaskibra->first()->extracurricular->name;
        }
        if ($negativePoints < 0 || $evidence['violations_count'] > 0) {
            $penalty = min(20, abs($negativePoints) + ($evidence['violations_count'] * 4));
            $discBase = max(50, $discBase - $penalty);
            $discSrcParts[] = 'Penyesuaian Catatan Tata Tertib';
        }
        $discipline = $diagnostic ? round(($discBase * 0.75) + ($diagnostic->discipline_self_score * 0.25)) : round($discBase);

        return [
            'scores' => [
                'logic'         => min(98, max(50, (int) $logic)),
                'communication' => min(98, max(50, (int) $comm)),
                'technical'     => min(98, max(50, (int) $tech)),
                'social'        => min(98, max(50, (int) $social)),
                'creative'      => min(98, max(50, (int) $creative)),
                'discipline'    => min(98, max(50, (int) $discipline)),
            ],
            'sources' => [
                'logic'         => implode(' · ', $logicSrcParts),
                'communication' => implode(' · ', $commSrcParts),
                'technical'     => implode(' · ', $techSrcParts),
                'social'        => implode(' · ', $socialSrcParts),
                'creative'      => implode(' · ', $creaSrcParts),
                'discipline'    => implode(' · ', $discSrcParts),
            ],
        ];
    }

    /**
     * Hitung kelengkapan sumber data (0% - 100%) — jelas bukan nilai kepintaran siswa.
     */
    private function calculateConfidenceScore(
        array $subjectProfile,
        ?float $attendanceRate,
        array $evidence,
        ?StudentDiagnosticAssessment $diagnostic
    ): int {
        $score = 20;
        if ($subjectProfile['subject_count'] >= 6) {
            $score += 30;
        } elseif ($subjectProfile['subject_count'] >= 2) {
            $score += 18;
        } elseif ($subjectProfile['subject_count'] > 0) {
            $score += 10;
        }

        if ($attendanceRate !== null) {
            $score += 15;
        }
        if ($evidence['cbt_avg'] !== null) {
            $score += 12;
        }
        if ($evidence['lms_count'] > 0) {
            $score += 10;
        }
        if ($diagnostic !== null) {
            $score += 13;
        }

        return min(100, $score);
    }

    private function getConfidenceLabel(int $score): string
    {
        if ($score >= 85) {
            return 'Sangat Lengkap (Seluruh Sumber Data Tersedia)';
        }
        if ($score >= 60) {
            return 'Cukup Lengkap (Data Akademik & Aktivitas Tersedia)';
        }
        return 'Tahap Awal (Menunggu Kelengkapan Data Nilai & Aktivitas)';
    }

    /**
     * Tentukan Profil Dominan Belajar Ganda (Primary + Secondary) dalam Bahasa Indonesia baku.
     * Menjaga dimensi kedisiplinan agar tidak menelan bakat akademik/kejuruan siswa.
     */
    private function determineArchetype(
        array $scores,
        array $subjectProfile,
        array $evidence,
        string $schoolType,
        ?array $vocationalMajor,
        ?StudentDiagnosticAssessment $diagnostic
    ): array {
        $aptitude = [
            'logic'         => $scores['logic'],
            'communication' => $scores['communication'],
            'technical'     => $scores['technical'],
            'social'        => $scores['social'],
            'creative'      => $scores['creative'],
        ];
        arsort($aptitude);
        $orderedKeys = array_keys($aptitude);
        $primaryKey = $orderedKeys[0];
        $secondaryKey = $orderedKeys[1];

        $spread = max($aptitude) - min($aptitude);
        if ($spread <= 2 && $scores['discipline'] >= 90 && $subjectProfile['subject_count'] === 0) {
            $primaryKey = 'discipline';
            $secondaryKey = $orderedKeys[0];
        }

        $pMeta = self::PROFILE_CATALOG[$primaryKey];
        $sMeta = self::PROFILE_CATALOG[$secondaryKey];

        $title = $pMeta['title'];
        if (($aptitude[$secondaryKey] ?? 0) >= ($aptitude[$primaryKey] ?? 0) - 5 && $secondaryKey !== $primaryKey) {
            $title = $pMeta['short'] . ' & ' . $sMeta['short'];
        }

        // Susun deskripsi faktual spesifik siswa (menyebut mapel unggulan, jurusan, prestasi, & cita-cita)
        $parts = [];
        $parts[] = 'Siswa ' . $pMeta['core_trait'] . ' (' . $scores[$primaryKey] . '/100), didukung kecenderungan sekunder sebagai ' . $sMeta['short'] . ' (' . $scores[$secondaryKey] . '/100).';

        if (!empty($subjectProfile['top_subjects'])) {
            $topText = collect($subjectProfile['top_subjects'])
                ->take(2)
                ->map(fn ($s) => $s['subject_name'] . ' (' . $s['score'] . ')')
                ->implode(' dan ');
            $parts[] = 'Kekuatan akademik paling menonjol tercatat pada mata pelajaran ' . $topText . '.';
        }

        if ($schoolType === 'SMK' && $vocationalMajor) {
            $parts[] = 'Pada jenjang SMK, kompetensi ini selaras dengan penguasaan konsentrasi keahlian ' . $vocationalMajor['label'] . '.';
        }

        if (!empty($evidence['achievements'])) {
            $achFirst = $evidence['achievements'][0];
            $parts[] = 'Rekam jejak non-akademik diperkuat prestasi ' . trim(($achFirst['ranking'] ? $achFirst['ranking'] . ' ' : '') . $achFirst['title']) . '.';
        } elseif ($evidence['final_project']) {
            $parts[] = 'Saat ini siswa mengembangkan tugas akhir bertema "' . $evidence['final_project']['title'] . '".';
        }

        return [
            'primary_key'   => $primaryKey,
            'secondary_key' => $secondaryKey,
            'title'         => $title,
            'badge_icon'    => $pMeta['badge_icon'],
            'color'         => $pMeta['color'],
            'tagline'       => $pMeta['tagline'],
            'description'   => implode(' ', $parts),
        ];
    }

    /**
     * Hasilkan rekomendasi studi, karier, keselarasan cita-cita, dan rencana pendampingan
     * dalam Bahasa Indonesia baku berdasarkan data spesifik siswa.
     */
    private function generateRecommendations(
        array $scores,
        array $archetype,
        string $schoolType,
        ?array $vocationalMajor,
        array $subjectProfile,
        array $evidence,
        ?StudentDiagnosticAssessment $diagnostic
    ): array {
        $primaryKey = $archetype['primary_key'];
        $secondaryKey = $archetype['secondary_key'];

        $careerMap = [
            'logic'         => ['Pengembang Perangkat Lunak', 'Analis Data & Riset Sains', 'Spesialis Jaringan Komputer & Awan', 'Analis Keuangan & Akuntansi', 'Aktuaris & Pemodel Sistem'],
            'communication' => ['Hubungan Masyarakat & Komunikasi Publik', 'Jurnalis & Penulis Konten Edukatif', 'Konsultan Hukum & Negosiasi', 'Spesialis Pemasaran Digital', 'Diplomasi & Hubungan Internasional'],
            'technical'     => ['Teknisi Sistem & Rekayasa Industri', 'Spesialis Perangkat Keras & Otomasi', 'Teknisi Lapangan Telekomunikasi', 'Penjaminan Mutu Manufaktur', 'Pelaksana Proyek Konstruksi & Teknik'],
            'social'        => ['Pengelola Sumber Daya Manusia', 'Guru & Fasilitator Pembelajaran', 'Manajer Operasional Proyek', 'Koordinator Pemberdayaan Masyarakat', 'Pelayanan Publik & Konselor'],
            'creative'      => ['Desainer Antarmuka (UI/UX) & Multimedia', 'Arsitek & Perancang Ruang', 'Desainer Grafis & Animasi', 'Pengarah Kreatif Media Digital', 'Perancang Produk & Merek Visual'],
            'discipline'    => ['Administrator Tata Kelola Perkantoran', 'Manajer Logistik & Rantai Pasok', 'Auditor Kepatuhan & Mutu', 'Analis Tata Kelola Keuangan', 'Aparatur Sipil & Sekolah Kedinasan'],
        ];

        $majorMap = [
            'logic' => [
                ['major' => 'Teknik Informatika / Ilmu Komputer', 'cluster' => 'Saintek & Teknologi', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Matematika Terapan / Sains Data', 'cluster' => 'Saintek', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Teknik Elektro / Teknik Industri', 'cluster' => 'Rekayasa', 'readiness' => 'Sesuai'],
                ['major' => 'Sistem Informasi & Bisnis Digital', 'cluster' => 'Terapan Digital', 'readiness' => 'Sesuai'],
            ],
            'communication' => [
                ['major' => 'Ilmu Komunikasi & Hubungan Masyarakat', 'cluster' => 'Sosial & Humaniora', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Pendidikan Bahasa & Sastra Indonesia/Inggris', 'cluster' => 'Bahasa & Pendidikan', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Ilmu Hukum & Tata Kelola Bisnis', 'cluster' => 'Sosial & Humaniora', 'readiness' => 'Sesuai'],
                ['major' => 'Hubungan Internasional', 'cluster' => 'Sosial & Humaniora', 'readiness' => 'Potensial Dikembangkan'],
            ],
            'technical' => [
                ['major' => 'Teknik Komputer & Jaringan Terapan', 'cluster' => 'Vokasi & Rekayasa', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Teknik Otomasi & Mesin Industri', 'cluster' => 'Vokasi & Rekayasa', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Teknologi Rekayasa Elektronika', 'cluster' => 'Vokasi & Teknik', 'readiness' => 'Sesuai'],
                ['major' => 'Pendidikan Teknik / Manajemen Proyek', 'cluster' => 'Teknik Terapan', 'readiness' => 'Sesuai'],
            ],
            'social' => [
                ['major' => 'Manajemen Bisnis & Kepemimpinan', 'cluster' => 'Ekonomi & Manajemen', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Psikologi / Bimbingan Konseling', 'cluster' => 'Sosial & Pendidikan', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Administrasi Publik & Kebijakan', 'cluster' => 'Sosial & Pemerintahan', 'readiness' => 'Sesuai'],
                ['major' => 'Pendidikan Ekonomi / Sosiologi', 'cluster' => 'Keguruan & Sosial', 'readiness' => 'Sesuai'],
            ],
            'creative' => [
                ['major' => 'Desain Komunikasi Visual (DKV)', 'cluster' => 'Seni & Desain', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Arsitektur & Desain Produk', 'cluster' => 'Rekayasa & Seni', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Animasi & Produksi Media Digital', 'cluster' => 'Industri Kreatif', 'readiness' => 'Sesuai'],
                ['major' => 'Seni Rupa & Kriya Modern', 'cluster' => 'Seni & Desain', 'readiness' => 'Potensial Dikembangkan'],
            ],
            'discipline' => [
                ['major' => 'Akuntansi Sektor Publik & Perpajakan', 'cluster' => 'Ekonomi & Bisnis', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Manajemen Logistik & Distribusi', 'cluster' => 'Vokasi & Bisnis', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'Ilmu Pemerintahan & Sekolah Kedinasan', 'cluster' => 'Kedinasan', 'readiness' => 'Sesuai'],
                ['major' => 'Manajemen Mutu Operasional', 'cluster' => 'Manajemen', 'readiness' => 'Sesuai'],
            ],
        ];

        $smkTracksByMajor = [
            'TE' => [
                'careers' => ['Teknisi Elektronika Industri & Audio Video', 'Spesialis Mikrokontroler & Otomasi', 'Teknisi Servis Perangkat Elektronik', 'Pelaksana Perakitan Sistem Kendali'],
                'pkl'     => 'Perusahaan Elektronika & Otomasi, Studio Audio-Video Profesional, Pusat Layanan Resmi Elektronik, atau Bengkel Sistem Kendali',
                'certs'   => ['Sertifikasi Kompetensi BNSP Elektronika Industri / Audio Video', 'Keselamatan Kerja Kelistrikan (K3 Listrik)', 'Perakitan & Pemrograman Mikrokontroler Dasar'],
            ],
            'DPIB' => [
                'careers' => ['Juru Gambar Bangunan (Drafter AutoCAD / BIM)', 'Pelaksana Pengawas Lapangan Konstruksi', 'Estimator Rencana Anggaran Biaya (RAB)', 'Asisten Perancang Arsitektur'],
                'pkl'     => 'Konsultan Arsitektur & Teknik Sipil, Kontraktor Bangunan Gedung, Dinas Pekerjaan Umum, atau Studio Desain Pemodelan Bangunan',
                'certs'   => ['Sertifikasi Juru Gambar Bangunan Gedung (BNSP)', 'Kompetensi Desain Pemodelan 2D/3D (AutoCAD / SketchUp)', 'Penyusunan Rencana Anggaran Biaya Konstruksi'],
            ],
            'TKR' => [
                'careers' => ['Mekanik Servis Kendaraan Ringan Injeksi', 'Teknisi Kelistrikan & Diagnosa Mesin Mobil', 'Penanggung Jawab Bengkel (Service Advisor)', 'Teknisi Uji Kelayakan & Emisi Kendaraan'],
                'pkl'     => 'Dealer & Bengkel Resmi Mobil (Toyota, Daihatsu, Honda, Mitsubishi), Bengkel Uji Emisi, atau Pusat Armada Transportasi',
                'certs'   => ['Sertifikasi Teknisi Kendaraan Ringan Otomotif (BNSP)', 'Diagnosa Sistem Injeksi Elektronik (EFI)', 'Keselamatan & Kesehatan Kerja (K3) Bengkel Otomotif'],
            ],
            'TSM' => [
                'careers' => ['Mekanik Servis Sepeda Motor Injeksi', 'Teknisi Kelistrikan & Sistem Rangka Motor', 'Wirausaha Bengkel Servis Mandiri', 'Kepala Mekanik Bengkel Mitra'],
                'pkl'     => 'Bengkel Resmi Sepeda Motor (AHASS Honda, Yamaha, Suzuki), Distributor Suku Cadang Resmi, atau Bengkel Layanan Cepat',
                'certs'   => ['Sertifikasi Teknisi Sepeda Motor (BNSP / Vendor Resmi)', 'Diagnosa Sistem Bahan Bakar Injeksi Sepeda Motor', 'Manajemen Layanan & Tata Kelola Bengkel'],
            ],
            'TKJ' => [
                'careers' => ['Administrator Jaringan Komputer & MikroTik', 'Teknisi Fiber Optik & Telekomunikasi', 'Spesialis Dukungan Teknis & Perakitan Komputer', 'Pengelola Server & Keamanan Jaringan Lokal'],
                'pkl'     => 'Penyedia Layanan Internet (ISP), Perusahaan Telekomunikasi, Pusat Layanan Komputer & Jaringan (Axioo Class Program), atau Instansi Pemerintah Daerah',
                'certs'   => ['Sertifikasi BNSP Administrasi Jaringan Komputer', 'MikroTik Certified Network Associate (MTCNA)', 'Axioo Class Program (ACP) / Teknisi Jaringan Fiber Optik'],
            ],
        ];

        $learningStrategyMap = [
            'logic'         => 'Perkuat latihan soal penalaran bertingkat dari konsep dasar ke kasus terapan nyata, dan manfaatkan simulasi laboratorium virtual untuk memvisualisasikan hubungan sebab-akibat.',
            'communication' => 'Biasakan merangkum bacaan dengan kalimat sendiri, aktif berdiskusi kelompok, dan berlatih menyampaikan argumen secara runtut baik lisan maupun tulisan.',
            'technical'     => 'Perbanyak jam praktik langsung di bengkel atau laboratorium, dokumentasikan setiap langkah uji coba kerja, dan hubungkan keterampilan tangan dengan teori dasar.',
            'social'        => 'Manfaatkan belajar kelompok terarah (belajar sebaya), ambil peran koordinator dalam proyek kelas, serta latih pengelolaan waktu antara organisasi dan target akademik.',
            'creative'      => 'Gunakan peta pikiran visual, sketsa alur konsep, atau pembuatan proyek portofolio karya untuk mempercepat penguasaan materi konseptual.',
            'discipline'    => 'Pertahankan keteraturan jadwal belajar harian dan gunakan daftar periksa mingguan agar penguasaan konsep sulit terus meningkat seiring ketekunan.',
        ];

        $careers = $careerMap[$primaryKey] ?? $careerMap['technical'];
        $secondaryCareers = $careerMap[$secondaryKey] ?? [];
        if (!empty($secondaryCareers)) {
            $careers = array_values(array_unique(array_merge(array_slice($careers, 0, 3), array_slice($secondaryCareers, 0, 2))));
        }

        $majors = $majorMap[$primaryKey] ?? $majorMap['technical'];
        if ($schoolType === 'SMP') {
            $majors = $this->buildSmpContinuationRecommendations($primaryKey, $secondaryKey, $subjectProfile);
        }

        $strategy = $learningStrategyMap[$primaryKey] ?? $learningStrategyMap['technical'];

        $topNames = array_column($subjectProfile['top_subjects'], 'subject_name');
        $topHint = !empty($topNames) ? (' terutama pada ' . implode(' & ', array_slice($topNames, 0, 2))) : '';

        $careerTracks = [
            [
                'title'       => $careers[0],
                'relevance'   => 'Sangat Sesuai',
                'readiness'   => 'Tinggi',
                'description' => 'Sejalan dengan sumbu utama ' . self::PROFILE_CATALOG[$primaryKey]['short'] . ' (' . $scores[$primaryKey] . '/100)' . $topHint . '.',
            ],
            [
                'title'       => $careers[1] ?? $careers[0],
                'relevance'   => 'Sesuai',
                'readiness'   => 'Baik',
                'description' => 'Memadukan kekuatan utama dengan kecenderungan ' . self::PROFILE_CATALOG[$secondaryKey]['short'] . ' (' . $scores[$secondaryKey] . '/100) untuk daya saing di dunia kerja maupun studi lanjut.',
            ],
        ];

        if ($schoolType === 'SMK' && $vocationalMajor && isset($smkTracksByMajor[$vocationalMajor['code']])) {
            $spec = $smkTracksByMajor[$vocationalMajor['code']];
            $careers = $spec['careers'];
            $careerTracks = [
                [
                    'title'       => $spec['careers'][0],
                    'relevance'   => 'Sangat Sesuai',
                    'readiness'   => 'Siap Praktik',
                    'description' => 'Selaras langsung dengan jurusan ' . $vocationalMajor['label'] . ' dan skor keahlian teknis (' . $scores['technical'] . '/100).',
                ],
                [
                    'title'       => $spec['careers'][1],
                    'relevance'   => 'Sesuai',
                    'readiness'   => 'Siap Dikembangkan',
                    'description' => 'Memadukan kompetensi kejuruan ' . $vocationalMajor['code'] . ' dengan kemampuan ' . self::PROFILE_CATALOG[$primaryKey]['short'] . '.',
                ],
            ];
        }

        $dreamAlignment = $this->evaluateDreamAlignment($diagnostic, $scores, $subjectProfile, $vocationalMajor);
        $weakNames = array_column($subjectProfile['weak_subjects'], 'subject_name');
        $weakHint = !empty($weakNames) ? implode(' dan ', $weakNames) : 'materi dasar yang belum tercukupi';

        $actionPlan = [
            'Minggu 1–4: Mengulang materi inti pada mata pelajaran prioritas penguatan (' . $weakHint . ') selama 30 menit per hari dengan target peningkatan bertahap.',
            'Minggu 5–8: Memanfaatkan Simulator Lab dan latihan soal CBT untuk menghubungkan kekuatan utama (' . (!empty($topNames) ? $topNames[0] : self::PROFILE_CATALOG[$primaryKey]['short']) . ') ke pemecahan masalah nyata.',
            'Minggu 9–12: Menyusun portofolio karya, proyek praktik, atau ringkasan evaluasi belajar untuk ditinjau bersama guru wali dan orang tua.',
        ];

        $result = [
            'career_tracks'           => $careerTracks,
            'career_recommendations'  => $careers,
            'college_recommendations' => array_column($majors, 'major'),
            'college_majors'          => $majors,
            'learning_strategy'       => $strategy,
            'student_dream'           => $diagnostic?->career_aspiration ?: null,
            'dream_alignment'         => $dreamAlignment,
            'action_plan_90_days'     => $actionPlan,
            'parent_guidance'         => 'Dampingi ananda dengan memberi apresiasi atas capaian baiknya' . $topHint . ', sambil memantau rutinitas belajar teratur pada bidang yang masih membutuhkan penguatan (' . $weakHint . ').',
            'teacher_guidance'        => 'Berikan penugasan proyek berdiferensiasi yang memanfaatkan kekuatan siswa sebagai ' . self::PROFILE_CATALOG[$primaryKey]['short'] . ' sambil memberi bimbingan terarah pada mata pelajaran ' . $weakHint . '.',
        ];

        if ($schoolType === 'SMK') {
            $majorCode = $vocationalMajor['code'] ?? null;
            $result['pkl_recommendation'] = $majorCode && isset($smkTracksByMajor[$majorCode])
                ? $smkTracksByMajor[$majorCode]['pkl']
                : 'Perusahaan Mitra Industri DUDI, Bengkel Resmi, atau Layanan Teknologi yang sesuai dengan konsentrasi keahlian siswa';
            $result['certifications'] = $majorCode && isset($smkTracksByMajor[$majorCode])
                ? $smkTracksByMajor[$majorCode]['certs']
                : ['Sertifikasi Kompetensi Profesi BNSP Sesuai Konsentrasi Keahlian', 'Keselamatan dan Kesehatan Kerja (K3) Industri'];
        }

        return $result;
    }

    /**
     * Rekomendasi jalur lanjutan khusus siswa SMP (SMA vs SMK & peminatan).
     */
    private function buildSmpContinuationRecommendations(string $primaryKey, string $secondaryKey, array $subjectProfile): array
    {
        if ($primaryKey === 'technical') {
            return [
                ['major' => 'SMK Jurusan Teknik Komputer & Jaringan / Elektronika', 'cluster' => 'Pendidikan Vokasi (SMK)', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'SMK Jurusan Otomotif / Desain Bangunan', 'cluster' => 'Pendidikan Vokasi (SMK)', 'readiness' => 'Sesuai'],
                ['major' => 'SMA Peminatan MIPA & Informatika', 'cluster' => 'Sekolah Menengah Atas (SMA)', 'readiness' => 'Sesuai'],
            ];
        }
        if ($primaryKey === 'logic') {
            return [
                ['major' => 'SMA Peminatan Sains, Matematika & Teknologi (MIPA)', 'cluster' => 'Sekolah Menengah Atas (SMA)', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'SMK Jurusan Teknik Komputer & Jaringan / Elektronika', 'cluster' => 'Pendidikan Vokasi (SMK)', 'readiness' => 'Sesuai'],
                ['major' => 'Persiapan Kelas Riset & Olimpiade Sains', 'cluster' => 'Pengembangan Bakat', 'readiness' => 'Potensial Dikembangkan'],
            ];
        }
        if ($primaryKey === 'creative') {
            return [
                ['major' => 'SMK Desain Pemodelan Bangunan / Multimedia Kreatif', 'cluster' => 'Pendidikan Vokasi (SMK)', 'readiness' => 'Sangat Sesuai'],
                ['major' => 'SMA Peminatan Seni, Bahasa & Desain', 'cluster' => 'Sekolah Menengah Atas (SMA)', 'readiness' => 'Sesuai'],
            ];
        }
        return [
            ['major' => 'SMA Peminatan Sosial, Ekonomi & Bahasa (Soshum)', 'cluster' => 'Sekolah Menengah Atas (SMA)', 'readiness' => 'Sangat Sesuai'],
            ['major' => 'SMA Peminatan Terpadu sesuai Minat Lanjut Perguruan Tinggi', 'cluster' => 'Sekolah Menengah Atas (SMA)', 'readiness' => 'Sesuai'],
        ];
    }

    /**
     * Evaluasi keselarasan cita-cita siswa dengan capaian nilai & bakat saat ini secara konstruktif.
     */
    private function evaluateDreamAlignment(
        ?StudentDiagnosticAssessment $diagnostic,
        array $scores,
        array $subjectProfile,
        ?array $vocationalMajor
    ): ?array {
        if (!$diagnostic || empty(trim((string) $diagnostic->career_aspiration))) {
            return null;
        }

        $dream = trim((string) $diagnostic->career_aspiration);
        $dLower = strtolower($dream);

        $expectedDim = 'logic';
        $neededSubjects = 'Matematika & Sains';
        if (preg_match('/(dokter|perawat|bidan|farmasi|biologi|kesehatan|gizi|kedokteran)/i', $dLower)) {
            $expectedDim = 'logic';
            $neededSubjects = 'Biologi, Kimia, & Penalaran Sains';
        } elseif (preg_match('/(programmer|developer|it|jaringan|teknik|mesin|otomotif|arsitek|listrik|elektronika|sipil|bangunan|insinyur|mekanik)/i', $dLower)) {
            $expectedDim = 'technical';
            $neededSubjects = 'Praktik Kejuruan, Matematika, & Fisika Terapan';
        } elseif (preg_match('/(pengacara|hukum|diplomat|jurnalis|wartawan|guru|bahasa|sastra|komunikasi|humas)/i', $dLower)) {
            $expectedDim = 'communication';
            $neededSubjects = 'Bahasa Indonesia, Bahasa Inggris, & Wawasan Sosial';
        } elseif (preg_match('/(pengusaha|bisnis|manajer|akuntan|keuangan|bank|ekonomi|pajak)/i', $dLower)) {
            $expectedDim = 'social';
            $neededSubjects = 'Ekonomi/Akuntansi, Matematika, & Kepemimpinan';
        } elseif (preg_match('/(desainer|arsitek|seniman|animator|kreatif|fotografer|konten|multimedia)/i', $dLower)) {
            $expectedDim = 'creative';
            $neededSubjects = 'Seni Rupa/Desain, Prakarya, & Komunikasi Visual';
        }

        $dimScore = $scores[$expectedDim] ?? 75;
        $statusLabel = $dimScore >= 82
            ? 'Sangat Selaras dengan Kekuatan Saat Ini'
            : ($dimScore >= 72 ? 'Cukup Selaras (Perlu Penguatan Terarah)' : 'Tahap Perintisan (Butuh Pendampingan Fondasi)');

        $summary = 'Cita-cita siswa ("' . $dream . '") berkaitan erat dengan penguasaan ' . $neededSubjects
            . '. Skor dimensi pendukung saat ini berada pada ' . $dimScore . '/100.';

        if ($vocationalMajor) {
            $summary .= ' Konteks jurusan SMK saat ini adalah ' . $vocationalMajor['label'] . '.';
        }

        return [
            'dream'        => $dream,
            'status_label' => $statusLabel,
            'needed_focus' => $neededSubjects,
            'score'        => $dimScore,
            'summary'      => $summary,
        ];
    }

    /**
     * Hitung sidik jari unik data siswa untuk mendeteksi kapan analisis ClassAI perlu diperbarui.
     */
    private function computeDataFingerprint(
        array $scores,
        array $subjectProfile,
        array $evidence,
        ?StudentDiagnosticAssessment $diagnostic,
        ?float $attendanceRate,
        ?array $vocationalMajor
    ): string {
        $payload = [
            'scores'       => $scores,
            'top'          => array_map(fn ($s) => [$s['subject_name'], $s['score']], $subjectProfile['top_subjects']),
            'weak'         => array_map(fn ($s) => [$s['subject_name'], $s['score']], $subjectProfile['weak_subjects']),
            'att'          => $attendanceRate,
            'cbt'          => $evidence['cbt_avg'],
            'lms'          => [$evidence['lms_count'], $evidence['lms_avg'], $evidence['lms_ontime_rate']],
            'proj'         => $evidence['final_project'] ? ($evidence['final_project']['title'] . ':' . $evidence['final_project']['stage']) : null,
            'pkl'          => $evidence['pkl'] ? ($evidence['pkl']['company'] . ':' . $evidence['pkl']['approved_logs']) : null,
            'ach'          => count($evidence['achievements']),
            'major'        => $vocationalMajor['code'] ?? null,
            'diag'         => $diagnostic ? [
                $diagnostic->work_style_preference,
                $diagnostic->favorite_subject_cluster,
                $diagnostic->career_aspiration,
                $diagnostic->updated_at?->timestamp,
            ] : null,
        ];

        return hash('sha256', json_encode($payload));
    }

    /**
     * Baca laporan analisis ClassAI tersimpan tanpa memanggil API eksternal saat render halaman awal.
     */
    private function loadCachedAiReport(int $studentId, string $currentFingerprint): array
    {
        $gemini = app(GeminiService::class);
        $providerInfo = $gemini->getProviderInfo();
        $row = null;

        if (Schema::hasTable('student_dna_ai_reports')) {
            try {
                $dbRow = StudentDnaAiReport::where('student_id', $studentId)->first();
                if ($dbRow && is_array($dbRow->ai_payload)) {
                    $row = [
                        'data_fingerprint' => $dbRow->data_fingerprint,
                        'provider'         => $dbRow->provider,
                        'model_used'       => $dbRow->model_used,
                        'ai_payload'       => $dbRow->ai_payload,
                        'generated_at'     => $dbRow->generated_at?->translatedFormat('d M Y, H:i'),
                    ];
                }
            } catch (\Throwable $e) {
                // fallback ke cache
            }
        }

        if (!$row) {
            $cached = Cache::get("student_dna_ai_{$studentId}");
            if (is_array($cached) && !empty($cached['ai_payload'])) {
                $row = $cached;
                if (!empty($row['generated_at']) && strtotime($row['generated_at'])) {
                    $row['generated_at'] = \Carbon\Carbon::parse($row['generated_at'])->translatedFormat('d M Y, H:i');
                }
            }
        }

        if (!$row) {
            return [
                'report' => null,
                'meta'   => [
                    'provider'            => $providerInfo['provider'],
                    'provider_label'      => $providerInfo['provider_label'],
                    'model'               => $providerInfo['model'],
                    'generated_at'        => null,
                    'is_stale'            => true,
                    'provider_configured' => $providerInfo['configured'],
                ],
            ];
        }

        return [
            'report' => $row['ai_payload'],
            'meta'   => [
                'provider'            => $row['provider'] ?? $providerInfo['provider'],
                'provider_label'      => ($row['provider'] ?? '') === 'classai' ? 'ClassAI Router' : $providerInfo['provider_label'],
                'model'               => $row['model_used'] ?? $providerInfo['model'],
                'generated_at'        => $row['generated_at'] ?? null,
                'is_stale'            => ($row['data_fingerprint'] ?? '') !== $currentFingerprint,
                'provider_configured' => $providerInfo['configured'],
            ],
        ];
    }

    /**
     * Susun prompt terstruktur untuk ClassAI dalam Bahasa Indonesia baku, anti-template, dan bebas istilah memvonis.
     */
    private function buildClassAiPrompts(Student $student, array $analysis): array
    {
        $systemPrompt = "Anda adalah Konselor Akademik & Spesialis Pemetaan Potensi Pendidikan Yayasan Perguruan Pembda Nias (PembdaHUB).\n"
            . "ATURAN BAHASA & ETIKA PENULISAN (WAJIB DIPATUHI):\n"
            . "1. Gunakan 100% Bahasa Indonesia baku yang santun, jelas, membumi, dan profesional agar mudah dipahami siswa, guru, maupun orang tua.\n"
            . "2. HINDARI istilah Inggris yang tidak perlu (misalnya jangan tulis 'mindset', 'skillset', 'gap', 'roadmap', 'career track', 'growth area') — gunakan padanan Indonesia yang tepat ('pola pikir', 'keterampilan', 'selisih capaian', 'rencana langkah', 'bidang prioritas penguatan').\n"
            . "3. DILARANG keras menggunakan kata memvonis atau menyimpulkan nasib secara kaku (seperti 'tidak berbakat', 'lemah', 'gagal', 'tidak cocok sama sekali'). Sajikan area yang nilainya lebih rendah sebagai 'bidang prioritas penguatan'.\n"
            . "4. ANALISIS HARUS 100% UNIK DAN SPESIFIK: wajib menyebut minimal 2 nama mata pelajaran beserta angka nilai nyatanya dari data siswa, serta konteks nyata (jurusan SMK, proyek/penelitian, PKL, prestasi, atau cita-cita siswa jika ada). Jangan menulis kalimat generik yang bisa berlaku untuk sembarang siswa.\n"
            . "5. Kembalikan HANYA objek JSON murni (tanpa markdown code block) dengan struktur persis berikut:\n"
            . "{\n"
            . "  \"executive_summary\": \"2-3 kalimat ringkasan profil belajar personal yang langsung merujuk data nyata siswa\",\n"
            . "  \"unique_strengths\": [\"poin kekuatan spesifik 1 dengan bukti nilai/aktivitas\", \"poin kekuatan spesifik 2\", \"poin kekuatan spesifik 3\"],\n"
            . "  \"growth_priorities\": [\"poin prioritas penguatan konstruktif 1 dengan mapel/angka\", \"poin prioritas penguatan 2\"],\n"
            . "  \"dream_alignment\": \"penjelasan keselarasan cita-cita siswa dengan capaian data saat ini secara objektif\",\n"
            . "  \"tailored_paths\": [{\"title\": \"Nama Jurusan / Bidang Karier Indonesia\", \"reason\": \"alasan spesifik merujuk kekuatan siswa\"}],\n"
            . "  \"action_plan_90_days\": [\"Langkah bulan 1 yang konkret\", \"Langkah bulan 2 yang konkret\", \"Langkah bulan 3 yang konkret\"],\n"
            . "  \"parent_guidance\": \"Saran pendampingan praktis 2 kalimat bagi orang tua di rumah\",\n"
            . "  \"teacher_guidance\": \"Saran pembelajaran berdiferensiasi 2 kalimat bagi wali kelas/guru\"\n"
            . "}";

        $topStr = !empty($analysis['subject_profile']['top_subjects'])
            ? collect($analysis['subject_profile']['top_subjects'])
                ->map(fn ($s) => "{$s['subject_name']}: {$s['score']}" . ($s['delta'] !== null ? " (selisih vs rerata sekolah: " . ($s['delta'] >= 0 ? '+' : '') . "{$s['delta']})" : ''))
                ->implode('; ')
            : 'Belum ada nilai mapel tercatat';

        $weakStr = !empty($analysis['subject_profile']['weak_subjects'])
            ? collect($analysis['subject_profile']['weak_subjects'])
                ->map(fn ($s) => "{$s['subject_name']}: {$s['score']}")
                ->implode('; ')
            : 'Belum ada data pembanding antar-mapel';

        $ev = $analysis['evidence_360'];
        $achStr = !empty($ev['achievements'])
            ? collect($ev['achievements'])->map(fn ($a) => trim(($a['ranking'] ? $a['ranking'] . ' ' : '') . $a['title'] . ($a['level'] ? " ({$a['level']})" : '')))->implode('; ')
            : 'Belum tercatat';

        $ekskulStr = $ev['extracurriculars']->isNotEmpty()
            ? $ev['extracurriculars']->map(fn ($m) => ($m->extracurricular->name ?? 'Ekskul') . ' (' . ($m->role_label ?? 'Anggota') . ')')->implode(', ')
            : 'Belum aktif di ekskul';

        $diag = $analysis['diagnostic'];
        $diagStr = $diag
            ? "Gaya belajar={$diag->work_style_preference}; Rumpun favorit={$diag->favorite_subject_cluster}; Cita-cita=" . ($diag->career_aspiration ?: '-')
            : 'Belum mengisi kuesioner minat mandiri';

        $userPrompt = "Buatkan analisis DNA Akademik 360° personal untuk siswa berikut:\n"
            . "- Nama: {$student->full_name}\n"
            . "- Jenjang & Sekolah: {$analysis['school_type']} ({$analysis['database_identity']['school_name']}), Kelas {$analysis['database_identity']['classroom_name']}\n"
            . ($analysis['vocational_major'] ? "- Jurusan SMK Asli: {$analysis['vocational_major']['label']} ({$analysis['vocational_major']['code']})\n" : '')
            . "- Profil Terhitung: {$analysis['archetype']['title']}\n"
            . "- Skor 6 Dimensi (0-100): Logika={$analysis['scores']['logic']}, Komunikasi={$analysis['scores']['communication']}, Keahlian Teknis={$analysis['scores']['technical']}, Sosial={$analysis['scores']['social']}, Kreativitas={$analysis['scores']['creative']}, Kedisiplinan={$analysis['scores']['discipline']}\n"
            . "- Rata-rata Nilai Siswa: " . ($analysis['subject_profile']['student_avg'] ?? 'Belum tersedia') . " dari {$analysis['subject_profile']['subject_count']} mapel\n"
            . "- Mata Pelajaran Unggulan: {$topStr}\n"
            . "- Mata Pelajaran Prioritas Penguatan: {$weakStr}\n"
            . "- Presensi Sekolah: {$analysis['metrics']['attendance_display']}\n"
            . "- Tugas LMS: {$ev['lms_count']} tugas (rerata=" . ($ev['lms_avg'] ?? '-') . ", tepat waktu=" . ($ev['lms_ontime_rate'] !== null ? $ev['lms_ontime_rate'] . '%' : '-') . ")\n"
            . "- Ujian CBT: {$ev['cbt_count']} ujian (rerata=" . ($ev['cbt_avg'] ?? '-') . ", tertinggi=" . ($ev['cbt_max'] ?? '-') . ")\n"
            . "- Proyek Akhir / Penelitian: " . ($ev['final_project'] ? "{$ev['final_project']['title']} (Tahap: {$ev['final_project']['stage']})" : 'Belum ada') . "\n"
            . "- Praktik Kerja Lapangan (PKL): " . ($ev['pkl'] ? "{$ev['pkl']['company']} ({$ev['pkl']['approved_logs']} logbook disetujui)" : 'Belum/Tidak sedang PKL') . "\n"
            . "- Prestasi: {$achStr}\n"
            . "- Ekstrakurikuler: {$ekskulStr}\n"
            . "- Asesmen Minat & Cita-Cita Siswa: {$diagStr}";

        return [$systemPrompt, $userPrompt];
    }

    /**
     * Normalisasi dan sanitasi struktur respons JSON ClassAI sebelum disimpan dan ditampilkan.
     */
    private function normalizeAiPayload(array $raw, array $analysis): array
    {
        $strList = static fn ($arr, int $limit = 4): array => collect(is_array($arr) ? $arr : [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->take($limit)
            ->values()
            ->toArray();

        $paths = collect(is_array($raw['tailored_paths'] ?? null) ? $raw['tailored_paths'] : [])
            ->filter(fn ($p) => is_array($p) && !empty($p['title']))
            ->map(fn ($p) => [
                'title'  => trim((string) $p['title']),
                'reason' => trim((string) ($p['reason'] ?? '')),
            ])
            ->take(4)
            ->values()
            ->toArray();

        return [
            'executive_summary' => trim((string) ($raw['executive_summary'] ?? $analysis['archetype']['description'])),
            'unique_strengths'  => $strList($raw['unique_strengths'] ?? [], 4),
            'growth_priorities' => $strList($raw['growth_priorities'] ?? [], 3),
            'dream_alignment'   => trim((string) ($raw['dream_alignment'] ?? ($analysis['recommendations']['dream_alignment']['summary'] ?? ''))),
            'tailored_paths'    => $paths,
            'action_plan_90_days' => $strList($raw['action_plan_90_days'] ?? $analysis['recommendations']['action_plan_90_days'], 3),
            'parent_guidance'   => trim((string) ($raw['parent_guidance'] ?? $analysis['recommendations']['parent_guidance'])),
            'teacher_guidance'  => trim((string) ($raw['teacher_guidance'] ?? $analysis['recommendations']['teacher_guidance'])),
        ];
    }
}
