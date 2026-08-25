<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\LmsSubmission;
use App\Models\CbtExamResult;
use App\Models\ReputationLog;
use App\Models\StudentDiagnosticAssessment;
use App\Models\StudentCounselingRecord;
use Illuminate\Support\Collection;

class StudentDnaService
{
    /**
     * Generate complete 360° Academic DNA analysis for a student.
     */
    public function analyze(Student $student): array
    {
        $student->loadMissing(['school', 'classrooms']);
        $schoolType = strtoupper($student->school->type ?? 'SMA');

        // 1. Ambil data nilai akademik
        $grades = Grade::where('student_id', $student->id)->with('subject')->get();
        
        // 2. Ambil data presensi
        $totalAttendance = Attendance::where('student_id', $student->id)->count();
        $presentAttendance = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['present', 'hadir', 'h', 'H'])
            ->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : null;

        // 3. Ambil data CBT (gunakan final_score atau percentage_score)
        $cbtAvg = CbtExamResult::where('student_id', $student->id)->avg('final_score')
            ?? CbtExamResult::where('student_id', $student->id)->avg('percentage_score');

        // 4. Ambil data Reputasi Gamifikasi
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

        // Hitung Skor Tiap Dimensi (0 - 100)
        $dimensionScores = $this->calculateDimensions(
            $grades,
            $attendanceRate,
            $cbtAvg,
            $positivePoints,
            $negativePoints,
            $diagnostic,
            $schoolType
        );

        // Hitung Confidence Score (Akurasi Data)
        $confidenceScore = $this->calculateConfidenceScore($grades, $attendanceRate, $cbtAvg, $diagnostic);

        // Tentukan Tipe DNA Pembelajar (Archetype)
        $archetype = $this->determineArchetype($dimensionScores, $schoolType);

        // Hasilkan Rekomendasi Karir & Jurusan
        $recommendations = $this->generateRecommendations($dimensionScores, $schoolType, $diagnostic);

        return [
            'student' => $student,
            'school_type' => $schoolType,
            'scores' => $dimensionScores,
            'confidence_score' => $confidenceScore,
            'confidence_label' => $this->getConfidenceLabel($confidenceScore),
            'archetype' => $archetype,
            'recommendations' => $recommendations,
            'metrics' => [
                'total_grades' => $grades->count(),
                'attendance_rate' => $attendanceRate ?? 85,
                'cbt_average' => $cbtAvg ? round($cbtAvg, 1) : null,
                'reputation_points' => $positivePoints + $negativePoints,
                'has_diagnostic' => (bool)$diagnostic,
                'counseling_sessions' => $counselingCount,
            ],
            'diagnostic' => $diagnostic,
        ];
    }

    /**
     * Calculate scores for the 6 core DNA dimensions.
     */
    private function calculateDimensions(
        Collection $grades,
        ?float $attendanceRate,
        ?float $cbtAvg,
        int $positivePoints,
        int $negativePoints,
        ?StudentDiagnosticAssessment $diagnostic,
        string $schoolType
    ): array {
        // Kelompokkan nilai mapel
        $logicGrades = [];
        $commGrades = [];
        $techGrades = [];
        $socialGrades = [];
        $creativeGrades = [];

        foreach ($grades as $g) {
            $name = strtolower($g->subject->subject_name ?? '');
            $code = strtolower($g->subject->subject_code ?? '');
            $score = (float)$g->score;

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

        // 1. Logika & Analitik
        $logicBase = !empty($logicGrades) ? (array_sum($logicGrades) / count($logicGrades)) : ($cbtAvg ?: 75);
        if ($cbtAvg) {
            $logicBase = ($logicBase * 0.7) + ($cbtAvg * 0.3);
        }
        $logic = $diagnostic ? round(($logicBase * 0.7) + ($diagnostic->logic_self_score * 0.3)) : round($logicBase);

        // 2. Komunikasi & Bahasa
        $commBase = !empty($commGrades) ? (array_sum($commGrades) / count($commGrades)) : 76;
        $comm = $diagnostic ? round(($commBase * 0.7) + ($diagnostic->communication_self_score * 0.3)) : round($commBase);

        // 3. Keahlian Vokasi & Teknis Terapan
        $techBase = !empty($techGrades) ? (array_sum($techGrades) / count($techGrades)) : ($schoolType === 'SMK' ? 80 : 74);
        $tech = $diagnostic ? round(($techBase * 0.7) + ($diagnostic->technical_self_score * 0.3)) : round($techBase);

        // 4. Sosial & Kepemimpinan
        $socialBase = !empty($socialGrades) ? (array_sum($socialGrades) / count($socialGrades)) : 75;
        if ($positivePoints > 0) {
            $socialBase = min(98, $socialBase + min(10, $positivePoints / 10));
        }
        $social = $diagnostic ? round(($socialBase * 0.7) + ($diagnostic->social_self_score * 0.3)) : round($socialBase);

        // 5. Kreativitas & Inovasi
        $creativeBase = !empty($creativeGrades) ? (array_sum($creativeGrades) / count($creativeGrades)) : 75;
        $creative = $diagnostic ? round(($creativeBase * 0.7) + ($diagnostic->creative_self_score * 0.3)) : round($creativeBase);

        // 6. Kedisiplinan & Ketekunan
        $att = $attendanceRate ?? 85;
        $discBase = $att;
        if ($negativePoints < 0) {
            $discBase = max(50, $discBase - abs($negativePoints));
        }
        $discipline = $diagnostic ? round(($discBase * 0.7) + ($diagnostic->discipline_self_score * 0.3)) : round($discBase);

        return [
            'logic' => min(98, max(50, $logic)),
            'communication' => min(98, max(50, $comm)),
            'technical' => min(98, max(50, $tech)),
            'social' => min(98, max(50, $social)),
            'creative' => min(98, max(50, $creative)),
            'discipline' => min(98, max(50, $discipline)),
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
        $score = 30; // base confidence
        if ($grades->count() >= 5) $score += 25;
        elseif ($grades->count() > 0) $score += 15;

        if ($attendanceRate !== null) $score += 15;
        if ($cbtAvg !== null) $score += 15;
        if ($diagnostic !== null) $score += 15;

        return min(100, $score);
    }

    private function getConfidenceLabel(int $score): string
    {
        if ($score >= 85) return 'Tinggi (Data Komprehensif)';
        if ($score >= 60) return 'Sedang (Data Terkalibrasi)';
        return 'Eksplorasi (Data Awal Berkembang)';
    }

    /**
     * Determine student learning archetype based on dominant dimensions.
     */
    private function determineArchetype(array $scores, string $schoolType): array
    {
        $tech = $scores['technical'];
        $logic = $scores['logic'];
        $creative = $scores['creative'];
        $social = $scores['social'];
        $comm = $scores['communication'];
        $disc = $scores['discipline'];

        if ($tech >= 82 && $logic >= 80) {
            return [
                'title' => 'The Master Engineer',
                'tagline' => 'Praktisi Ahli & Pemecah Masalah Teknis',
                'color' => 'from-blue-600 to-indigo-700',
                'badge_icon' => 'fa-screwdriver-wrench',
                'description' => 'Siswa memiliki bakat kuat dalam logika analitis dan rekayasa praktis. Sangat cepat memahami sistem mekanikal, komputasi, dan solusi terapan.',
            ];
        }

        if ($creative >= 82 && $comm >= 80) {
            return [
                'title' => 'The Visionary Innovator',
                'tagline' => 'Kreator Gagasan & Komunikator Orisinal',
                'color' => 'from-purple-600 to-pink-600',
                'badge_icon' => 'fa-lightbulb',
                'description' => 'Siswa unggul dalam menghasilkan karya kreatif, ekspresi visual, dan kemampuan menyampaikan ide secara memikat dan persuasif.',
            ];
        }

        if ($social >= 82 && $comm >= 80) {
            return [
                'title' => 'The Dynamic Leader',
                'tagline' => 'Pemimpin Kolaboratif & Penggerak Tim',
                'color' => 'from-amber-500 to-orange-600',
                'badge_icon' => 'fa-crown',
                'description' => 'Siswa berbakat dalam kepemimpinan, komunikasi publik, kepekaan sosial, dan mengorganisir kerja sama kelompok.',
            ];
        }

        if ($logic >= 82 && $disc >= 82) {
            return [
                'title' => 'The Strategic Analyst',
                'tagline' => 'Pemikir Sistematis & Peneliti Presisi',
                'color' => 'from-emerald-600 to-teal-700',
                'badge_icon' => 'fa-brain',
                'description' => 'Siswa memiliki ketelitian tinggi, disiplin kokoh, daya konsentrasi tajam, dan kemampuan menuntaskan persoalan kompleks langkah demi langkah.',
            ];
        }

        if ($tech >= 80 && $creative >= 80) {
            return [
                'title' => 'The Applied Craftsman',
                'tagline' => 'Kreator Terapan & Eksekutor Desain',
                'color' => 'from-cyan-600 to-blue-700',
                'badge_icon' => 'fa-cube',
                'description' => 'Siswa memadukan estetika dengan keterampilan fisik nyata, mampu merancang produk fungsional berkualitas tinggi.',
            ];
        }

        return [
            'title' => 'The Versatile Achiever',
            'tagline' => 'Pembelajar Adaptif & Berwawasan Luas',
            'color' => 'from-violet-600 to-indigo-600',
            'badge_icon' => 'fa-dna',
            'description' => 'Siswa memiliki profil kompetensi yang seimbang di berbagai aspek kognitif, sosial, dan ketekunan belajar.',
        ];
    }

    /**
     * Generate personalized career & college recommendations.
     */
    private function generateRecommendations(
        array $scores,
        string $schoolType,
        ?StudentDiagnosticAssessment $diagnostic
    ): array {
        $recs = [];

        if ($schoolType === 'SMK') {
            // Rekomendasi Jalur Karier SMK
            if ($scores['technical'] >= 80 || $scores['logic'] >= 80) {
                $recs['career_tracks'][] = [
                    'title' => 'Teknisi Sistem & Jaringan / Software Engineer',
                    'relevance' => 'Sangat Tinggi (95%)',
                    'description' => 'Sangat cocok untuk industri teknologi, pemeliharaan infrastruktur digital, dan otomatisasi manufaktur.',
                ];
                $recs['certifications'][] = 'Sertifikasi BNSP / MikroTik MTCNA / Cisco CCNA / Junior Web Developer';
                $recs['pkl_recommendation'] = 'Perusahaan Telekomunikasi, Software House, Divisi IT Instansi Pemerintah, atau Industri Manufaktur Modern.';
            }

            if ($scores['creative'] >= 78 || $scores['communication'] >= 78) {
                $recs['career_tracks'][] = [
                    'title' => 'Digital Content Creator & UI/UX Multimedia Specialist',
                    'relevance' => 'Tinggi (90%)',
                    'description' => 'Sangat menjanjikan di agensi kreatif, media penyiaran, branding digital, dan industri periklanan modern.',
                ];
                $recs['certifications'][] = 'Sertifikasi BNSP Desain Grafis / Adobe Certified Professional';
                $recs['pkl_recommendation'] = 'Media Digital, Studio Percetakan/Desain, Biro Komunikasi Publik, atau Production House.';
            }

            if ($scores['social'] >= 78 || $scores['discipline'] >= 82) {
                $recs['career_tracks'][] = [
                    'title' => 'Supervisi Operasional & Layanan Pelanggan Korporat',
                    'relevance' => 'Tinggi (88%)',
                    'description' => 'Memiliki ketelitian administrasi dan kehandalan dalam manajemen inventaris dan relasi klien.',
                ];
                $recs['certifications'][] = 'Sertifikasi Administrasi Perkantoran & Supply Chain Management';
                $recs['pkl_recommendation'] = 'Perbankan, Lembaga Keuangan, Logistik, dan Kantor Administrasi Daerah.';
            }
        } else {
            // Rekomendasi Jurusan Kuliah (SMA)
            if ($scores['logic'] >= 80 && $scores['technical'] >= 75) {
                $recs['college_majors'][] = [
                    'major' => 'Teknik Informatika / Ilmu Komputer / Sains Data',
                    'cluster' => 'Sains & Teknologi',
                    'readiness' => 'Sangat Siap',
                ];
                $recs['college_majors'][] = [
                    'major' => 'Teknik Elektro / Teknik Mesin / Teknik Sipil',
                    'cluster' => 'Rekayasa & Keteknikan',
                    'readiness' => 'Sangat Siap',
                ];
            }

            if ($scores['logic'] >= 82 && $scores['discipline'] >= 85) {
                $recs['college_majors'][] = [
                    'major' => 'Pendidikan Dokter / Farmasi / Biomedis',
                    'cluster' => 'Kesehatan & Hayati',
                    'readiness' => 'Tinggi',
                ];
            }

            if ($scores['social'] >= 78 && $scores['communication'] >= 78) {
                $recs['college_majors'][] = [
                    'major' => 'Ilmu Hukum / Hubungan Internasional / Ilmu Komunikasi',
                    'cluster' => 'Sosial & Humaniora',
                    'readiness' => 'Sangat Siap',
                ];
                $recs['college_majors'][] = [
                    'major' => 'Manajemen Bisnis / Akuntansi / Ekonomi Pembangunan',
                    'cluster' => 'Ekonomi & Bisnis',
                    'readiness' => 'Tinggi',
                ];
            }

            if ($scores['creative'] >= 78) {
                $recs['college_majors'][] = [
                    'major' => 'Desain Komunikasi Visual (DKV) / Arsitektur / Animasi Digital',
                    'cluster' => 'Seni & Desain Terapan',
                    'readiness' => 'Tinggi',
                ];
            }
        }

        // Catatan Aspirasi Mandiri jika siswa sudah mengisi kuesioner
        if ($diagnostic && $diagnostic->career_aspiration) {
            $recs['student_dream'] = $diagnostic->career_aspiration;
        }

        return $recs;
    }
}
