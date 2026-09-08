<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;

class VocationalMajorFilterService
{
    /**
     * Deteksi kata kunci Kejuruan untuk mata pelajaran produktif SMK (DDTK / Konsentrasi Keahlian).
     * Mengembalikan array kata kunci jika mapel kejuruan, atau null jika mapel umum.
     */
    public static function getSubjectMajorKeywords(?string $subjectName, ?string $subjectCode = null, ?string $courseName = null): ?array
    {
        $text = strtoupper(($subjectName ?? '') . ' ' . ($subjectCode ?? '') . ' ' . ($courseName ?? ''));

        // 1. Teknik Elektronika / Audio Video (TE / TAV)
        if (preg_match('/\b(TE|TAV|ELEKTRONIKA|AUDIO\s*VIDEO|MIKROKONTROLER)\b/i', $text)) {
            return ['TE', 'TAV', 'ELEKTRONIKA', 'AUDIO'];
        }
        // 2. DPIB / Bangunan / Desain Pemodelan
        if (preg_match('/\b(DPIB|BANGUNAN|ARSITEKTUR|GAMBAR\s*TEKNIK|KONSTRUKSI)\b/i', $text)) {
            return ['DPIB', 'BANGUNAN'];
        }
        // 3. TKR / Otomotif / TO (Kendaraan Ringan)
        if (preg_match('/\b(TKR|OTOMOTIF|KENDARAAN|CHASIS|ENGINE)\b/i', $text) || preg_match('/\bTO\b/i', $text)) {
            return ['TKR', 'TO', 'OTOMOTIF', 'KENDARAAN'];
        }
        // 4. TSM / TBSM / Sepeda Motor
        if (preg_match('/\b(TSM|TBSM|SEPEDA\s*MOTOR)\b/i', $text)) {
            return ['TSM', 'TBSM', 'MOTOR'];
        }
        // 5. TKJ / TJKT / ACP / Jaringan Komputer
        if (preg_match('/\b(TKJ|TJKT|ACP|JARINGAN|KOMPUTER)\b/i', $text)) {
            return ['TKJ', 'TJKT', 'ACP', 'JARINGAN'];
        }

        return null; // Mapel Umum (Normatif / Adaptif / Non-SMK)
    }

    /**
     * Periksa apakah siswa relevan dengan mata pelajaran kejuruan ini.
     * SOLUSI PERMANEN: Menggunakan Jurusan resmi siswa (major_id) atau mapel Konsentrasi Keahlian
     * yang dipelajari siswa di Tahun Pelajaran aktif saat ini (TIDAK PERLU MELIHAT KELAS TAHUN LALU).
     */
    public static function isStudentMatchingVocationalSubject(Student $student, ?array $subjectKeywords, ?Classroom $classroom = null): bool
    {
        if (!$subjectKeywords) {
            return true; // Mapel umum (Matematika, B.Indo, dll) -> semua siswa relevan
        }

        // 1. PRIORITAS UTAMA: Gunakan relasi Jurusan resmi (major_id) siswa jika sudah terisi
        if ($student->major_id) {
            $major = $student->relationLoaded('major') ? $student->major : $student->major()->first();
            if ($major) {
                $majorStr = strtoupper(($major->code ?? $major->major_code ?? '') . ' ' . ($major->name ?? $major->major_name ?? ''));
                foreach ($subjectKeywords as $kw) {
                    if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $majorStr)) {
                        return true;
                    }
                }
                return false;
            }
        }

        // 2. PRIORITAS KEDUA (KELAS GABUNGAN): Deteksi dari Mata Pelajaran Konsentrasi Keahlian yang sedang dipelajari siswa
        $activeLmsSubjects = \App\Models\LmsEnrollment::where('student_id', $student->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->join('lms_classes', 'lms_enrollments.lms_class_id', '=', 'lms_classes.id')
            ->join('lms_courses', 'lms_classes.course_id', '=', 'lms_courses.id')
            ->leftJoin('subjects', 'lms_courses.subject_id', '=', 'subjects.id')
            ->pluck('subjects.name')
            ->filter()
            ->toArray();

        foreach ($activeLmsSubjects as $sName) {
            $takenKw = self::getSubjectMajorKeywords($sName);
            if ($takenKw && !empty(array_intersect($takenKw, $subjectKeywords))) {
                return true;
            }
        }

        // 3. PRIORITAS KETIGA: Jika kelas saat ini di TP AKTIF adalah kelas reguler non-gabungan
        $activeClass = $student->currentClassroom()->first();
        if ($activeClass && !$activeClass->is_combined && $activeClass->class_type !== 'gabungan') {
            $className = strtoupper($activeClass->class_name);
            foreach ($subjectKeywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $className)) {
                    return true;
                }
            }
            return false;
        }

        // 4. Jika $classroom parameter adalah kelas reguler non-gabungan
        if ($classroom && !$classroom->is_combined && $classroom->class_type !== 'gabungan') {
            $className = strtoupper($classroom->class_name);
            foreach ($subjectKeywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $className)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }
}
