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
     * Menggunakan kelas reguler (non-gabungan) siswa untuk menentukan jurusan sebenarnya.
     */
    public static function isStudentMatchingVocationalSubject(Student $student, ?array $subjectKeywords, ?Classroom $classroom = null): bool
    {
        if (!$subjectKeywords) {
            return true; // Mapel umum (Matematika, B.Indo, dll) -> semua siswa relevan
        }

        // Cari kelas REGULER (non-gabungan) siswa untuk identifikasi jurusan asli
        $regularClasses = $student->classrooms->filter(function ($cls) {
            return !$cls->is_combined && $cls->class_type !== 'gabungan';
        });

        // Jika tidak ada kelas reguler terpisah, dan kelas saat ini bukan kelas gabungan
        if ($regularClasses->isEmpty() && $classroom && !$classroom->is_combined && $classroom->class_type !== 'gabungan') {
            $regularClasses = collect([$classroom]);
        }

        // Jika tidak ada kelas reguler teridentifikasi, keluarkan dari mapel kejuruan
        if ($regularClasses->isEmpty()) {
            return false;
        }

        $classNames = $regularClasses->pluck('class_name')->map(fn($n) => strtoupper($n))->toArray();

        foreach ($classNames as $className) {
            foreach ($subjectKeywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $className)) {
                    return true;
                }
            }
        }

        return false;
    }
}
