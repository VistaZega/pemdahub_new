<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // SuperAdmin, Admin Sekolah, dan Kepala Sekolah bisa melihat daftar users
        return $user->hasAnyRole(['superadmin', 'admin_sekolah', 'kepala_sekolah']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // SuperAdmin bisa melihat semua user
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Sekolah dan Kepala Sekolah bisa melihat semua user (terbatas pada sekolahnya)
        if ($user->hasAnyRole(['admin_sekolah', 'kepala_sekolah'])) {
            if ($user->id === $model->id) return true;
            return $user->school_id === $model->school_id && in_array($model->role, ['siswa', 'guru', 'pegawai', 'kepala_sekolah', 'admin_sekolah']);
        }

        // User bisa melihat profilnya sendiri
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // SuperAdmin dan Admin Sekolah bisa menambah user
        return $user->hasAnyRole(['superadmin', 'admin_sekolah']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // SuperAdmin bisa update semua user
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Sekolah bisa update user kecuali SuperAdmin dan Admin Sekolah lain
        // Tapi tetap boleh update diri sendiri
        if ($user->isAdminSekolah()) {
            if ($user->id === $model->id) return true;
            return $user->school_id === $model->school_id && in_array($model->role, ['siswa', 'guru', 'pegawai']);
        }

        // User bisa update profilnya sendiri (terbatas)
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // SuperAdmin bisa hapus user kecuali dirinya sendiri
        if ($user->isSuperAdmin() && $user->id !== $model->id) {
            return true;
        }

        // Admin Sekolah bisa hapus user kecuali SuperAdmin, Admin Sekolah lain, dan dirinya sendiri
        if ($user->isAdminSekolah()) {
            if ($user->id === $model->id) return false;
            return $user->school_id === $model->school_id && in_array($model->role, ['siswa', 'guru', 'pegawai']);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin_sekolah']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can reset password for another user.
     */
    public function resetPassword(User $user, User $model): bool
    {
        // SuperAdmin bisa reset password semua user
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Sekolah bisa reset password user kecuali SuperAdmin dan Admin Sekolah lain
        if ($user->isAdminSekolah()) {
            if ($user->id === $model->id) return true;
            return $user->school_id === $model->school_id && in_array($model->role, ['siswa', 'guru', 'pegawai']);
        }

        // User bisa reset password sendiri
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can manage roles.
     */
    public function manageRoles(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
