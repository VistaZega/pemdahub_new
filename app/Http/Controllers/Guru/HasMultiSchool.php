<?php

namespace App\Http\Controllers\Guru;

use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use App\Models\School;

/**
 * Trait HasMultiSchool
 * 
 * Menyediakan helper untuk fitur Guru Lintas Unit Sekolah.
 * Digunakan di semua controller Guru yang perlu menentukan
 * konteks sekolah aktif (terutama untuk Ketua Yayasan dan
 * guru yang ditugaskan di beberapa unit).
 */
trait HasMultiSchool
{
    /**
     * Mendapatkan school_id efektif berdasarkan session active_school_id
     * atau fallback ke school_id utama guru.
     */
    protected function getEffectiveSchoolId(?Teacher $teacher = null): int
    {
        $user = Auth::user();

        // Prioritas 0: request input atau query parameter (misal saat submit form atau filter halaman)
        $reqSchoolId = request('school_id') ?: request('target_school_id') ?: request('unit_id');
        if ($reqSchoolId && $user && $user->canAccessSchool($reqSchoolId)) {
            return (int) $reqSchoolId;
        }

        // Prioritas 1: session active_school_id (dari school switcher)
        $sessionSchoolId = session('active_school_id');
        if ($sessionSchoolId && $user && $user->canAccessSchool($sessionSchoolId)) {
            return (int) $sessionSchoolId;
        }

        // Prioritas 2: school_id dari teacher record
        if ($teacher && $teacher->school_id && (!$user || $user->canAccessSchool($teacher->school_id))) {
            return $teacher->school_id;
        }

        // Prioritas 3: school_id dari user
        if ($user && $user->school_id && $user->canAccessSchool($user->school_id)) {
            return $user->school_id;
        }

        // Prioritas 4: Sekolah pertama yang tersedia bagi user
        if ($user) {
            $firstAvailable = $user->getAvailableSchools()->first();
            if ($firstAvailable) {
                return (int) $firstAvailable->id;
            }
        }

        return 1;
    }

    /**
     * Mendapatkan nama sekolah aktif untuk ditampilkan
     */
    protected function getActiveSchoolName(): string
    {
        $schoolId = $this->getEffectiveSchoolId();
        $school = School::find($schoolId);
        return $school ? $school->name : 'Unit Tidak Diketahui';
    }

    /**
     * Mendapatkan daftar sekolah yang tersedia untuk user saat ini
     * (untuk UI school switcher)
     */
    protected function getAvailableSchools()
    {
        return Auth::user()->getAvailableSchools();
    }

    /**
     * Cek apakah user saat ini memiliki akses multi-school
     */
    protected function hasMultiSchoolAccess(): bool
    {
        return Auth::user()->hasMultiSchoolAccess();
    }
}
