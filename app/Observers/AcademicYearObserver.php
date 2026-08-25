<?php

namespace App\Observers;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk melindungi tabel academic_years dari penghapusan.
 * 
 * ATURAN KEAMANAN DATA (KRITIS):
 * - JANGAN PERNAH menghapus, drop, atau truncate data di tabel academic_years
 * - Penghapusan TP akan MENGHANCURKAN seluruh data relasi (kelas, jadwal,
 *   teaching assignments, nilai, absensi, PSB, CBT, BK, PKL, dll)
 *   karena 20+ tabel memiliki ON DELETE CASCADE pada academic_year_id
 * 
 * Observer ini mencegat penghapusan di level Eloquent, baik soft delete
 * maupun force delete, dari MANAPUN (controller, artisan tinker, queue, script).
 * 
 * @see \App\Providers\AppServiceProvider::boot() untuk registrasi observer
 */
class AcademicYearObserver
{
    /**
     * Handle the AcademicYear "deleting" event.
     * 
     * Mencegah soft delete pada academic years.
     * Selalu mengembalikan false untuk membatalkan operasi.
     */
    public function deleting(AcademicYear $academicYear): bool
    {
        Log::critical('PERCOBAAN PENGHAPUSAN TAHUN PELAJARAN DICEGAH!', [
            'academic_year_id' => $academicYear->id,
            'academic_year'    => $academicYear->year,
            'user_id'          => auth()->id(),
            'user_name'        => auth()->user()?->name ?? 'System/CLI',
            'ip_address'       => request()->ip(),
            'source'           => request()->url() ?: 'CLI/Tinker',
        ]);

        // Kembalikan false untuk MEMBATALKAN operasi delete
        return false;
    }

    /**
     * Handle the AcademicYear "forceDeleting" event.
     * 
     * Mencegah force delete (bypass SoftDeletes) pada academic years.
     * Ini adalah pertahanan terakhir jika seseorang mencoba forceDelete().
     */
    public function forceDeleting(AcademicYear $academicYear): bool
    {
        Log::critical('PERCOBAAN FORCE DELETE TAHUN PELAJARAN DICEGAH!', [
            'academic_year_id' => $academicYear->id,
            'academic_year'    => $academicYear->year,
            'user_id'          => auth()->id(),
            'user_name'        => auth()->user()?->name ?? 'System/CLI',
            'ip_address'       => request()->ip(),
            'source'           => request()->url() ?: 'CLI/Tinker',
        ]);

        // Kembalikan false untuk MEMBATALKAN operasi forceDelete
        return false;
    }
}
