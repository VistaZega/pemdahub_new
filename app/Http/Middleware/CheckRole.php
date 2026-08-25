<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        $activeRole = session('active_role');

        // Check if user has one of the required roles directly, via active_role, or is superadmin/owner
        $hasAccess = $user->hasAnyRole($roles) 
                  || ($activeRole && in_array($activeRole, $roles))
                  || $user->isSuperAdmin()
                  || $user->isOwnerOrSuperAdmin();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Prevent 404 trap for missing profiles
        $effectiveRole = $activeRole ?? $user->role;
        if ($effectiveRole === 'guru' || $effectiveRole === 'kepala_sekolah' || $effectiveRole === 'pegawai') {
            if (!$user->isSuperAdmin() 
                && !\App\Models\Teacher::where('user_id', $user->id)->exists() 
                && !\App\Models\Employee::where('user_id', $user->id)->exists()) {
                return response()->view('errors.missing_profile', ['role' => 'Guru / Pegawai']);
            }
        }
        if ($effectiveRole === 'siswa') {
            if (!\App\Models\Student::where('user_id', $user->id)->exists()) {
                return response()->view('errors.missing_profile', ['role' => 'Siswa']);
            }
        }

        // Pembatasan ketat untuk user yang mengakses area admin (/admin/*)
        // namun BUKAN admin utama (superadmin, admin_sekolah, kepala_sekolah, bendahara, ketua_yayasan).
        // Mereka hanya boleh mengakses modul khusus sesuai kepanitiaan / tugas tambahan mereka.
        if ($request->is('admin/*') || $request->is('admin')) {
            if (!$user->hasAnyRole(['superadmin', 'admin_sekolah', 'kepala_sekolah', 'bendahara', 'ketua_yayasan'])) {
                $routeName = $request->route()?->getName() ?? '';
                $path = $request->path();
                
                $allowed = false;
                
                // Surat Edaran Masuk Yayasan (Dapat dibaca oleh Guru & Pegawai)
                if (str_starts_with($routeName, 'admin.letters.') || str_starts_with($path, 'admin/surat-edaran')) {
                    $allowed = true;
                }
                
                // Panitia CBT
                if ($user->isPanitiaCbt() && (str_starts_with($routeName, 'admin.cbt.') || str_starts_with($path, 'admin/cbt'))) {
                    $allowed = true;
                }
                
                // Panitia PKL (Hanya modul PKL & DUDI, tidak termasuk Direktori Alumni)
                if ($user->isPanitiaPkl() && !str_contains($path, 'alumni-directory') && !str_contains($routeName, 'alumni-directory') && (
                    str_starts_with($routeName, 'admin.pkl-alumni.') || str_starts_with($path, 'admin/pkl-alumni') ||
                    str_starts_with($routeName, 'admin.dudis.') || str_starts_with($path, 'admin/dudis')
                )) {
                    $allowed = true;
                }
                
                // Panitia Project Akhir & Tugas Akhir
                if ($user->isPanitiaProyek() && (str_starts_with($routeName, 'admin.final-projects.') || str_starts_with($path, 'admin/final-projects'))) {
                    $allowed = true;
                }
                
                // Ekstrakurikuler & Non-Akademik (Dapat dikelola oleh PKS, Guru, Pembina, Admin Sekolah/Yayasan)
                if (str_starts_with($routeName, 'admin.extracurricular.') || str_starts_with($path, 'admin/extracurricular')) {
                    $allowed = true;
                }

                // DNA Akademik 360° (Dapat dilihat oleh Guru, PKS, Pembina, Admin)
                if (str_starts_with($routeName, 'admin.dna.') || str_starts_with($path, 'admin/dna')) {
                    $allowed = true;
                }

                // PKS & Guru Piket (Catatan Perkembangan Siswa, Konseling, & Lihat Data Siswa untuk input kasus)
                if ($user->isPksOrPiket() && (
                    str_starts_with($routeName, 'admin.counseling.') || str_starts_with($path, 'admin/counseling') ||
                    str_starts_with($routeName, 'admin.students.development.') || str_starts_with($path, 'admin/students')
                )) {
                    $allowed = true;
                }

                // Dashboard Admin untuk melihat overview
                if ($routeName === 'admin.dashboard' || $path === 'admin/dashboard') {
                    $allowed = true;
                }

                if (!$allowed) {
                    abort(403, 'Akses Ditolak: Sebagai Guru dengan Tugas Tambahan/Panitia, Anda hanya diperbolehkan mengelola modul kegiatan yang ditugaskan kepada Anda.');
                }
            }
        }

        return $next($request);
    }
}
