<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Grade;
use App\Models\FinalGrade;
use App\Models\Attendance;
use App\Models\LmsSubmission;
use App\Models\CbtExamResult;
use App\Models\ReputationLog;
use App\Models\StudentDiagnosticAssessment;
use App\Models\StudentCounselingRecord;
use App\Models\School;
use Illuminate\Support\Collection;

class StudentDnaService
{
    /**
     * Generate complete 360° Academic DNA analysis for a student.
     */
    public function analyze(Student $student): array
    {
        $student->loadMissing([
            'school.principal',
            'currentClassroom.homeroomTeacher',
            'classrooms.homeroomTeacher',
            'applicant',
        ]);
        $schoolType = strtoupper($student->school->type ?? 'SMA');

        // 1. Ambil data nilai akademik dari tabel grades dan final_grades
        $regularGrades = Grade::where('student_id', $student->id)->with('subject')->get();
        $finalGrades = FinalGrade::where('student_id', $student->id)->whereNotNull('final_score')->with('subject')->get();

        // Gabungkan nilai unik per mapel
        $grades = collect();
        foreach ($regularGrades as $g) {
            $grades->push((object)[
                'subject_id' => $g->subject_id,
                'subject' => $g->subject,
                'score' => (float)$g->score,
                'type' => $g->grade_type,
            ]);
        }
        foreach ($finalGrades as $fg) {
            if (!$grades->contains('subject_id', $fg->subject_id)) {
                $grades->push((object)[
                    'subject_id' => $fg->subject_id,
                    'subject' => $fg->subject,
                    'score' => (float)$fg->final_score,
                    'type' => 'final',
                ]);
            }
        }
        
        // 2. Ambil data presensi riil dari database (RFID & Presensi Harian)
        $totalAttendance = Attendance::where('student_id', $student->id)->count();
        $presentAttendance = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['present', 'hadir', 'h', 'H'])
            ->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : null;

        // 3. Ambil data Ujian CBT riil dari database
        $cbtAvg = CbtExamResult::where('student_id', $student->id)->avg('final_score')
            ?? CbtExamResult::where('student_id', $student->id)->avg('percentage_score');

        // 4. Ambil data Reputasi Gamifikasi riil dari database
        $positivePoints = 0;
        $negativePoints = 0;
        if ($student->user_id) {
            $positivePoints = (int) ReputationLog::where('user_id', $student->user_id)
                ->where('points', '>', 0)
                ->sum('points');
            $negativePoints = (int) ReputationLog::where('user_id', $student->user_id)
                ->where('points', '<', 0)
                ->sum('points');
        }

        // 5. Ambil data Asesmen Diagnostik Mandiri
        $diagnostic = StudentDiagnosticAssessment::where('student_id', $student->id)->first();

        // 6. Ambil Catatan Konseling BK
        $counselingCount = StudentCounselingRecord::where('student_id', $student->id)->count();

        // Hitung Skor Tiap Dimensi & Keterangan Sumber Data Riil Database
        $dimensionCalculation = $this->calculateDimensions(
            $grades,
            $attendanceRate,
            $cbtAvg,
            $positivePoints,
            $negativePoints,
            $diagnostic,
            $schoolType,
            $student
        );

        $dimensionScores = $dimensionCalculation['scores'];
        $dimensionSources = $dimensionCalculation['sources'];

        // Hitung Confidence Score (Akurasi Kalibrasi Data)
        $confidenceScore = $this->calculateConfidenceScore($grades, $attendanceRate, $cbtAvg, $diagnostic);

        // Tentukan Tipe DNA Pembelajar (Archetype)
        $archetype = $this->determineArchetype($dimensionScores, $schoolType);

        // Hasilkan Rekomendasi Karir & Jurusan
        $recommendations = $this->generateRecommendations($dimensionScores, $schoolType, $diagnostic);

        // Identitas & Konteks Database Resmi
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
            'student' => $student,
            'school_type' => $schoolType,
            'scores' => $dimensionScores,
            'dimension_sources' => $dimensionSources,
            'confidence_score' => $confidenceScore,
            'confidence_label' => $this->getConfidenceLabel($confidenceScore),
            'archetype' => $archetype,
            'recommendations' => $recommendations,
            'extracurriculars' => $student->extracurricularMembers()->where('status', 'approved')->with('extracurricular')->get(),
            'metrics' => [
                'total_grades' => $grades->count(),
                'attendance_rate' => $attendanceRate ?? 85,
                'cbt_average' => $cbtAvg ? round($cbtAvg, 1) : null,
                'reputation_points' => $positivePoints + $negativePoints,
                'has_diagnostic' => (bool)$diagnostic,
                'counseling_sessions' => $counselingCount,
            ],
            'diagnostic' => $diagnostic,
            'database_identity' => [
                'parent_name' => $parentName,
                'principal_name' => $principalName,
                'foundation_name' => $foundationName,
                'foundation_address' => $foundationAddress,
                'foundation_email' => $foundationEmail,
                'foundation_phone' => $foundationPhone,
                'foundation_website' => $foundationWebsite,
                'homeroom_teacher' => $homeroomTeacher,
                'classroom_name' => $classroomName,
                'school_name' => $student->school?->name ?? '-',
                'school_npsn' => $student->school?->npsn ?? '-',
                'school_address' => $student->school?->address ?? '-',
                'school_phone' => $student->school?->phone ?? '-',
                'student_address' => $student->address ?: ($student->guardian_address ?: '-'),
                'student_nis' => $student->formatted_nis ?: ($student->nis ?: '-'),
            ],
        ];
    }

    /**
     * Calculate scores and rigorous data sources for the 6 core DNA dimensions.
     */
    private function calculateDimensions(
        Collection $grades,
        ?float $attendanceRate,
        ?float $cbtAvg,
        int $positivePoints,
        int $negativePoints,
        ?StudentDiagnosticAssessment $diagnostic,
        string $schoolType,
        ?Student $student = null
    ): array {
        // Kelompokkan nilai mapel dari database
        $logicGrades = [];
        $commGrades = [];
        $techGrades = [];
        $socialGrades = [];
        $creativeGrades = [];
        $allScores = [];

        foreach ($grades as $g) {
            $name = strtolower($g->subject->subject_name ?? '');
            $code = strtolower($g->subject->subject_code ?? '');
            $score = (float)$g->score;
            $allScores[] = $score;

            if (str_contains($name, 'matematika') || str_contains($name, 'fisika') || str_contains($name, 'kimia') || str_contains($name, 'ipa') || str_contains($name, 'algoritma')) {
                $logicGrades[] = $score;
            } elseif (str_contains($name, 'indonesia') || str_contains($name, 'inggris') || str_contains($name, 'bahasa') || str_contains($name, 'sastra')) {
                $commGrades[] = $score;
            } elseif (str_contains($name, 'kejuruan') || str_contains($name, 'produktif') || str_contains($name, 'komputer') || str_contains($name, 'jaringan') || str_contains($name, 'mesin') || str_contains($name, 'otomotif') || str_contains($name, 'rekayasa') || str_contains($name, 'informatika') || str_contains($name, 'praktik')) {
                $techGrades[] = $score;
            } elseif (str_contains($name, 'ips') || str_contains($name, 'sejarah') || str_contains($name, 'ppkn') || str_contains($name, 'pancasila') || str_contains($name, 'sosiologi') || str_contains($name, 'ekonomi') || str_contains($name, 'geografi')) {
                $socialGrades[] = $score;
            } elseif (str_contains($name, 'seni') || str_contains($name, 'budaya') || str_contains($name, 'prakarya') || str_contains($name, 'desain') || str_contains($name, 'kreatif')) {
                $creativeGrades[] = $score;
            }
        }

        $overallGpa = !empty($allScores) ? (array_sum($allScores) / count($allScores)) : 75;

        // Ambil Data Keaktifan Ekstrakurikuler Riil dari Database
        $activeEkskuls = collect();
        if ($student) {
            $activeEkskuls = $student->extracurricularMembers()
                ->where('status', 'approved')
                ->with('extracurricular')
                ->get();
        }

        // 1. Logika & Analitik
        if (!empty($logicGrades)) {
            $logicAvg = array_sum($logicGrades) / count($logicGrades);
            $logicBase = $cbtAvg ? (($logicAvg * 0.6) + ($cbtAvg * 0.4)) : $logicAvg;
            $logicSource = 'Data Riil: ' . count($logicGrades) . ' Mapel Eksakta (Rerata: ' . round($logicAvg, 1) . ')' . ($cbtAvg ? ' & CBT (' . round($cbtAvg, 1) . ')' : '');
        } elseif ($cbtAvg !== null) {
            $logicBase = $cbtAvg;
            $logicSource = 'Data Riil: Rerata Ujian CBT (' . round($cbtAvg, 1) . ')';
        } else {
            $logicBase = $overallGpa;
            $logicSource = !empty($allScores) ? 'Estimasi Rerata Umum (' . count($allScores) . ' Mapel DB)' : 'Belum Ada Input Nilai Mapel di Database';
        }
        $logic = $diagnostic ? round(($logicBase * 0.7) + ($diagnostic->logic_self_score * 0.3)) : round($logicBase);

        // 2. Komunikasi & Bahasa
        if (!empty($commGrades)) {
            $commBase = array_sum($commGrades) / count($commGrades);
            $commSource = 'Data Riil: ' . count($commGrades) . ' Mapel Bahasa (Rerata: ' . round($commBase, 1) . ')';
        } else {
            $commBase = $overallGpa;
            $commSource = !empty($allScores) ? 'Estimasi Rerata Umum (' . count($allScores) . ' Mapel DB)' : 'Belum Ada Input Nilai Bahasa di Database';
        }
        $comm = $diagnostic ? round(($commBase * 0.7) + ($diagnostic->communication_self_score * 0.3)) : round($commBase);

        // 3. Keahlian Vokasi & Teknis Terapan
        if (!empty($techGrades)) {
            $techBase = array_sum($techGrades) / count($techGrades);
            $techSource = 'Data Riil: ' . count($techGrades) . ' Mapel Produktif Kejuruan (Rerata: ' . round($techBase, 1) . ')';
        } else {
            $techBase = $schoolType === 'SMK' ? min(90, $overallGpa + 2) : $overallGpa;
            $techSource = !empty($allScores) ? 'Estimasi Profil ' . $schoolType . ' (' . count($allScores) . ' Mapel DB)' : 'Belum Ada Input Nilai Kejuruan di Database';
        }
        $techEkskuls = $activeEkskuls->filter(fn($m) => in_array($m->extracurricular->category ?? '', ['sains_it']));
        if ($techEkskuls->isNotEmpty()) {
            $techBase = min(98, $techBase + 6);
            $techSource .= ' & Aktif ' . $techEkskuls->first()->extracurricular->name;
        }
        $tech = $diagnostic ? round(($techBase * 0.7) + ($diagnostic->technical_self_score * 0.3)) : round($techBase);

        // 4. Sosial & Kepemimpinan
        if (!empty($socialGrades)) {
            $socialBase = array_sum($socialGrades) / count($socialGrades);
            $socialSource = 'Data Riil: ' . count($socialGrades) . ' Mapel Sosial (Rerata: ' . round($socialBase, 1) . ')';
        } else {
            $socialBase = $overallGpa;
            $socialSource = !empty($allScores) ? 'Estimasi Rerata Umum (' . count($allScores) . ' Mapel DB)' : 'Belum Ada Input Nilai Sosial di Database';
        }
        if ($positivePoints > 0) {
            $bonus = min(10, $positivePoints / 10);
            $socialBase = min(98, $socialBase + $bonus);
            $socialSource .= ' + ' . $positivePoints . ' Poin Reputasi Positif';
        }
        $leadershipRoles = $activeEkskuls->filter(fn($m) => in_array($m->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']));
        if ($leadershipRoles->isNotEmpty()) {
            $socialBase = min(98, $socialBase + 10);
            $firstLeader = $leadershipRoles->first();
            $socialSource .= ' & ' . $firstLeader->role_label . ' ' . $firstLeader->extracurricular->name;
        } elseif ($activeEkskuls->isNotEmpty()) {
            $socialBase = min(98, $socialBase + 4);
            $socialSource .= ' & Anggota ' . $activeEkskuls->first()->extracurricular->name;
        }
        $social = $diagnostic ? round(($socialBase * 0.7) + ($diagnostic->social_self_score * 0.3)) : round($socialBase);

        // 5. Kreativitas & Inovasi
        if (!empty($creativeGrades)) {
            $creativeBase = array_sum($creativeGrades) / count($creativeGrades);
            $creativeSource = 'Data Riil: ' . count($creativeGrades) . ' Mapel Seni/Prakarya (Rerata: ' . round($creativeBase, 1) . ')';
        } else {
            $creativeBase = $overallGpa;
            $creativeSource = !empty($allScores) ? 'Estimasi Rerata Umum (' . count($allScores) . ' Mapel DB)' : 'Belum Ada Input Nilai Seni di Database';
        }
        $artsEkskuls = $activeEkskuls->filter(fn($m) => in_array($m->extracurricular->category ?? '', ['seni_budaya', 'jurnalistik']));
        if ($artsEkskuls->isNotEmpty()) {
            $creativeBase = min(98, max($creativeBase, 82) + 6);
            $creativeSource = 'Data Riil: ' . $artsEkskuls->first()->extracurricular->name . ($creativeGrades ? ' & ' . count($creativeGrades) . ' Mapel Seni' : '');
        }
        $creative = $diagnostic ? round(($creativeBase * 0.7) + ($diagnostic->creative_self_score * 0.3)) : round($creativeBase);

        // 6. Kedisiplinan & Ketekunan
        if ($attendanceRate !== null) {
            $discBase = $attendanceRate;
            $discSource = 'Data Riil: Presensi RFID Mesin (' . $attendanceRate . '% Kehadiran)';
        } else {
            $discBase = 85;
            $discSource = 'Belum Ada Data Presensi (Standar Baseline 85%)';
        }
        $scoutPaskibra = $activeEkskuls->filter(fn($m) => in_array($m->extracurricular->category ?? '', ['pramuka', 'paskibraka', 'olahraga']));
        if ($scoutPaskibra->isNotEmpty()) {
            $discBase = min(98, $discBase + 5);
            $discSource .= ' & Aktif ' . $scoutPaskibra->first()->extracurricular->name;
        }
        if ($negativePoints < 0) {
            $penalty = min(20, abs($negativePoints));
            $discBase = max(50, $discBase - $penalty);
            $discSource .= ' - ' . abs($negativePoints) . ' Poin Pelanggaran';
        }
        $discipline = $diagnostic ? round(($discBase * 0.7) + ($diagnostic->discipline_self_score * 0.3)) : round($discBase);

        return [
            'scores' => [
                'logic' => min(98, max(50, (int)$logic)),
                'communication' => min(98, max(50, (int)$comm)),
                'technical' => min(98, max(50, (int)$tech)),
                'social' => min(98, max(50, (int)$social)),
                'creative' => min(98, max(50, (int)$creative)),
                'discipline' => min(98, max(50, (int)$discipline)),
            ],
            'sources' => [
                'logic' => $logicSource,
                'communication' => $commSource,
                'technical' => $techSource,
                'social' => $socialSource,
                'creative' => $creativeSource,
                'discipline' => $discSource,
            ]
        ];
    }

    /**
     * Calculate confidence score (0% - 100%) based on available data.
     */
    private function calculateConfidenceScore(
        Collection $grades,
        ?float $attendanceRate,
        ?float $cbtAvg,
        ?StudentDiagnosticAssessment $diagnostic
    ): int {
        $score = 25; // base confidence
        if ($grades->count() >= 5) $score += 30;
        elseif ($grades->count() > 0) $score += 15;

        if ($attendanceRate !== null) $score += 20;
        if ($cbtAvg !== null) $score += 15;
        if ($diagnostic !== null) $score += 10;

        return min(100, $score);
    }

    private function getConfidenceLabel(int $score): string
    {
        if ($score >= 85) return 'Tinggi (Data Riil Komprehensif)';
        if ($score >= 60) return 'Sedang (Data Terkalibrasi)';
        return 'Eksplorasi (Menunggu Kelengkapan Nilai)';
    }

    /**
     * Determine student's dominant DNA archetype.
     */
    private function determineArchetype(array $scores, string $schoolType): array
    {
        $maxKey = array_keys($scores, max($scores))[0];

        $archetypes = [
            'logic' => [
                'title' => 'Algorithmic Thinker & Strategist',
                'badge_icon' => 'fa-brain',
                'color' => 'from-blue-600 to-indigo-600',
                'tagline' => 'Pemikir Kritis, Analitis, & Berorientasi Solusi Presisi',
                'description' => 'Siswa memiliki penalaran logika eksakta yang sangat kuat, tajam dalam menganalisis data, dan unggul dalam pemecahan masalah teknis komputasi.',
            ],
            'communication' => [
                'title' => 'Master Communicator & Diplomat',
                'badge_icon' => 'fa-comments',
                'color' => 'from-emerald-600 to-teal-600',
                'tagline' => 'Artikulator Unggul, Negosiator, & Pembangun Relasi',
                'description' => 'Kemampuan bahasa, literasi, dan artikulasi ide sangat menonjol. Mampu menyampaikan gagasan kompleks secara persuasif dan menginspirasi orang lain.',
            ],
            'technical' => [
                'title' => 'Applied Engineer & Builder',
                'badge_icon' => 'fa-tools',
                'color' => 'from-indigo-600 to-purple-600',
                'tagline' => 'Praktisi Handal, Pengembang Solusi, & Inovator Terapan',
                'description' => 'Sangat mahir dalam penguasaan kompetensi kejuruan, keterampilan laboratorium, implementasi perangkat lunak/keras, dan eksekusi proyek nyata.',
            ],
            'social' => [
                'title' => 'Inspiring Leader & Facilitator',
                'badge_icon' => 'fa-users',
                'color' => 'from-amber-600 to-orange-600',
                'tagline' => 'Pemimpin Kolaboratif, Berempati, & Penggerak Komunitas',
                'description' => 'Memiliki kecerdasan emosional dan sosial yang tinggi. Cakap memimpin tim, memediasi konflik, dan membawa dampak positif bagi lingkungan sekitar.',
            ],
            'creative' => [
                'title' => 'Visionary Creator & Innovator',
                'badge_icon' => 'fa-palette',
                'color' => 'from-purple-600 to-pink-600',
                'tagline' => 'Pencipta Kreatif, Desainer Solusi, & Pemikir Out-of-the-Box',
                'description' => 'Daya imajinasi dan estetika yang luar biasa. Selalu mencari cara-cara baru yang orisinal dalam berkarya dan memecahkan tantangan.',
            ],
            'discipline' => [
                'title' => 'Disciplined Achiever & Executor',
                'badge_icon' => 'fa-award',
                'color' => 'from-rose-600 to-red-600',
                'tagline' => 'Tekun, Berintegritas Tinggi, & Konsisten Mencapai Target',
                'description' => 'Tingkat kehadiran presensi dan ketepatan pengumpulan tugas sempurna. Memiliki komitmen tinggi terhadap tanggung jawab akademik.',
            ],
        ];

        return $archetypes[$maxKey] ?? $archetypes['technical'];
    }

    /**
     * Generate future study / career track recommendations based on DNA.
     */
    private function generateRecommendations(array $scores, string $schoolType, ?StudentDiagnosticAssessment $diagnostic): array
    {
        $maxKey = array_keys($scores, max($scores))[0];

        $careerMap = [
            'logic' => ['Software Developer & Programmer', 'Data Analyst & Riset Sains', 'Network & Cloud Specialist', 'Akuntan & Analis Keuangan', 'Teknisi Komputasi Cerdas'],
            'communication' => ['Public Relations (Humas)', 'Jurnalis & Media Konten', 'Digital Marketer & Sales Strategist', 'Konsultan Hukum & Negosiator', 'Hubungan Internasional'],
            'technical' => ['Teknisi Sistem & Jaringan Industri', 'Spesialis IoT & Hardware Terapan', 'Mekanik & Teknisi Otomotif', 'Quality Assurance Manufaktur', 'Teknisi Lapangan Telekomunikasi'],
            'social' => ['Human Resource Specialist', 'Guru & Fasilitator Edukasi', 'Manajer Operasional Proyek', 'Community Manager', 'Konselor & Pegawai Pelayanan Publik'],
            'creative' => ['Desainer UI/UX & Multimedia', 'Arsitek & Desainer Interior', 'Motion Graphic Designer', 'Creative Director & Animator', 'Spesialis Branding Visual'],
            'discipline' => ['Administrator Perkantoran', 'Manajer Logistik & Supply Chain', 'Auditor Kepatuhan & Regulasi', 'Analis Tata Kelola Keuangan', 'Aparatur Sipil / Kedinasan (TNI-Polri)'],
        ];

        $majorMap = [
            'logic' => [
                ['major' => 'Teknik Informatika / Ilmu Komputer', 'cluster' => 'Saintek / Rekayasa', 'readiness' => 'Sangat Siap'],
                ['major' => 'Matematika Terapan / Sains Data', 'cluster' => 'Saintek', 'readiness' => 'Optimal'],
                ['major' => 'Sistem Informasi & Bisnis Digital', 'cluster' => 'Saintek & Soshum', 'readiness' => 'Kompatibel'],
            ],
            'communication' => [
                ['major' => 'Ilmu Komunikasi & Hubungan Masyarakat', 'cluster' => 'Soshum', 'readiness' => 'Sangat Siap'],
                ['major' => 'Sastra Inggris & Bahasa Terapan', 'cluster' => 'Bahasa & Sastra', 'readiness' => 'Optimal'],
                ['major' => 'Ilmu Hukum & Tata Kelola Bisnis', 'cluster' => 'Soshum', 'readiness' => 'Kompatibel'],
            ],
            'technical' => [
                ['major' => 'Teknik Komputer & Jaringan Terapan', 'cluster' => 'Vokasi / Teknik', 'readiness' => 'Sangat Siap'],
                ['major' => 'Teknik Otomasi & Mesin Industri', 'cluster' => 'Vokasi / Rekayasa', 'readiness' => 'Optimal'],
                ['major' => 'Teknologi Rekayasa Perangkat Keras', 'cluster' => 'Vokasi', 'readiness' => 'Kompatibel'],
            ],
            'social' => [
                ['major' => 'Manajemen Bisnis & Kepemimpinan', 'cluster' => 'Soshum', 'readiness' => 'Sangat Siap'],
                ['major' => 'Psikologi / Bimbingan Konseling', 'cluster' => 'Soshum', 'readiness' => 'Optimal'],
                ['major' => 'Administrasi Publik & Kebijakan', 'cluster' => 'Soshum', 'readiness' => 'Kompatibel'],
            ],
            'creative' => [
                ['major' => 'Desain Komunikasi Visual (DKV)', 'cluster' => 'Seni & Desain', 'readiness' => 'Sangat Siap'],
                ['major' => 'Arsitektur & Desain Produk Kreatif', 'cluster' => 'Saintek / Seni', 'readiness' => 'Optimal'],
                ['major' => 'Animasi & Produksi Media Digital', 'cluster' => 'Industri Kreatif', 'readiness' => 'Kompatibel'],
            ],
            'discipline' => [
                ['major' => 'Akuntansi Sektor Publik & Perpajakan', 'cluster' => 'Ekonomi & Bisnis', 'readiness' => 'Sangat Siap'],
                ['major' => 'Manajemen Logistik & Distribusi', 'cluster' => 'Vokasi / Bisnis', 'readiness' => 'Optimal'],
                ['major' => 'Ilmu Pemerintahan & Sekolah Kedinasan', 'cluster' => 'Kedinasan / Soshum', 'readiness' => 'Kompatibel'],
            ],
        ];

        $learningStrategyMap = [
            'logic' => 'Fokus pada pemahaman logika konsep dasar yang kuat dan latihan pemecahan masalah algoritma/eksakta secara teratur.',
            'communication' => 'Perbanyak presentasi lisan, diskusi kelompok, membaca literatur komprehensif, dan menyusun ringkasan terstruktur.',
            'technical' => 'Tingkatkan jam praktik di laboratorium, bongkar pasang perangkat/proyek nyata, dan terapkan langsung teori ke studi kasus terapan.',
            'social' => 'Gunakan metode belajar kelompok (peer learning), pimpin proyek tim, dan diskusikan materi pelajaran bersama rekan sejawat.',
            'creative' => 'Gunakan peta pikiran visual (mind mapping), visualisasikan konsep rumit dalam bentuk infografis/karya, dan hindari metode hafalan kaku.',
            'discipline' => 'Pertahankan rutinitas belajar yang teratur dengan target checklist tugas berkala, dan jaga konsistensi presensi prima.',
        ];

        $careers = $careerMap[$maxKey] ?? $careerMap['technical'];
        $majors = $majorMap[$maxKey] ?? $majorMap['technical'];
        $strategy = $learningStrategyMap[$maxKey] ?? $learningStrategyMap['technical'];

        $careerTracks = [
            [
                'title' => $careers[0],
                'relevance' => 'Sangat Relevan',
                'readiness' => 'Tinggi',
                'description' => 'Sesuai dengan skor potensi dominan siswa (' . max($scores) . '/100), jalur ini membuka prospek keberhasilan optimal.',
            ],
            [
                'title' => $careers[1],
                'relevance' => 'Relevan',
                'readiness' => 'Optimal',
                'description' => 'Kombinasi kedisiplinan dan kompetensi siswa memberikan modal berharga untuk bersaing di industri modern.',
            ],
        ];

        $result = [
            'career_tracks' => $careerTracks,
            'career_recommendations' => $careers,
            'college_recommendations' => array_column($majors, 'major'),
            'college_majors' => $majors,
            'learning_strategy' => $strategy,
        ];

        if ($schoolType === 'SMK') {
            $result['pkl_recommendation'] = 'Industri Mitra Teknologi, Perusahaan Mitra BUMN, Bengkel Resmi, atau Software House Rekanan Sekolah';
        }

        return $result;
    }
}
