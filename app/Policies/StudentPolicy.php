<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // SuperAdmin, Admin Sekolah, Guru, dan Kepala Sekolah dapat melihat daftar siswa
        return $user->hasAnyRole(['superadmin', 'admin_sekolah', 'kepala_sekolah', 'guru']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Student $student): bool
    {
        // SuperAdmin, Admin Sekolah, Kepala Sekolah, Guru dapat melihat detail siswa
        if ($user->hasAnyRole(['superadmin', 'admin_sekolah', 'kepala_sekolah', 'guru'])) {
            return true;
        }

        // Siswa hanya bisa melihat data dirinya sendiri
        if ($user->isSiswa()) {
            return $student->user_id === $user->id;
        }

        // Orang tua bisa melihat data anaknya
        if ($user->isOrangTua()) {
            return $student->parents()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // SuperAdmin, Admin Sekolah, dan Wali Kelas bisa menambah siswa
        if ($user->hasAnyRole(['superadmin', 'admin_sekolah'])) {
            return true;
        }

        if ($user->isGuru() && $user->isWaliKelas()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Student $student): bool
    {
        // SuperAdmin dan Admin Sekolah bisa update semua siswa
        if ($user->hasAnyRole(['superadmin', 'admin_sekolah'])) {
            return true;
        }

        // Wali Kelas bisa mengedit data siswa di rombel perwaliannya atau di sekolah tempat bertugas
        if ($user->isGuru() && ($user->isWaliKelas() || $user->isHomeroomTeacher())) {
            // 1. Cek apakah siswa merupakan anggota rombel perwaliannya
            $homeroomClassroomIds = $user->homeroomClassrooms()->pluck('id')->toArray();
            if (!empty($homeroomClassroomIds) && $student->studentClasses()->whereIn('classroom_id', $homeroomClassroomIds)->exists()) {
                return true;
            }

            // 2. Cek kesesuaian sekolah (User school, Teacher school, Employee school, atau sekolah rombel perwalian)
            $allowedSchoolIds = array_filter([
                $user->school_id,
                $user->getActiveSchoolId(),
                $user->teacher?->school_id,
                $user->employee?->school_id,
            ]);
            $homeroomSchoolIds = $user->homeroomClassrooms()->pluck('school_id')->toArray();
            $allowedSchoolIds = array_unique(array_merge($allowedSchoolIds, $homeroomSchoolIds));

            if (!empty($allowedSchoolIds)) {
                return in_array($student->school_id, $allowedSchoolIds);
            }

            return true;
        }

        // Siswa bisa update data dirinya sendiri (terbatas)
        if ($user->isSiswa()) {
            return $student->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Sekolah bisa menghapus siswa dari sekolahnya sendiri
        if ($user->isAdminSekolah()) {
            return $user->school_id === $student->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Student $student): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Student $student): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can import students.
     */
    public function import(User $user): bool
    {
        if (in_array($user->role, ['superadmin', 'admin_sekolah'])) {
            return true;
        }

        if ($user->isGuru() && $user->isWaliKelas()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can export students.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin_sekolah', 'kepala_sekolah', 'guru']);
    }
}
