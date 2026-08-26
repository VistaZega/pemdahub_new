<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\LoginHistory;
use App\Models\ActivityLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm(Request $request): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $login = trim((string) $request->input('login'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Determine if login is email or username
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Check if user exists and is active
        $user = User::where($fieldType, $login)->first();

        if (!$user || !$user->is_active) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'User tidak ditemukan atau tidak aktif.']);
        }

        // Attempt to authenticate
        if (Auth::attempt([$fieldType => $login, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            // Update last login
            $user->updateLastLogin();

            // Log login activity
            $this->logActivity($user, 'login', 'Berhasil masuk ke sistem');

            // Record login history
            $this->recordLoginHistory($user, $request);

            // Redirect based on role
            return $this->redirectByRole($user);
        }

        // Log failed login
        if ($user) {
            $this->logActivity($user, 'login', 'Gagal masuk ke sistem - password salah');
        }

        return back()
            ->withInput($request->only('login'))
            ->withErrors(['password' => 'Password tidak sesuai.']);
    }

    /**
     * Show registration form
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Only allow self-registration for certain roles
        if (!in_array($validated['role'], ['siswa', 'orang_tua'])) {
            return back()->withErrors(['role' => 'Anda tidak dapat mendaftar dengan role ini.']);
        }

        try {
            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['role'],
                    'is_active' => false,
                ]);

                event(new Registered($user));
                $this->logActivity($user, 'create', 'Pendaftaran akun baru');

                return $user;
            });

            return redirect('/login')
                ->with('status', 'Pendaftaran berhasil. Silakan login dengan akun Anda.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.']);
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Log logout activity
        if ($user) {
            $this->logActivity($user, 'logout', 'Berhasil keluar dari sistem');

            // Update login history
            $this->updateLoginHistory($user);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('status', 'Jika email terdaftar, kami akan mengirim instruksi reset password.');
        }

        try {
            DB::transaction(function () use ($user, $request) {
                $token = Str::random(64);

                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token' => Hash::make($token),
                        'created_at' => now(),
                    ]
                );

                ActivityLog::create([
                    'user_id' => $user->id,
                    'school_id' => $user->school_id,
                    'action' => 'password_reset_request',
                    'description' => 'Permintaan reset password melalui email',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'logged_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            // Don't reveal errors for security
        }

        return back()->with('status', 'Jika email terdaftar, kami akan mengirim instruksi reset password.');
    }

    /**
     * Show change password form (forced on first login)
     */
    public function showChangePasswordForm(): View
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
        ], [
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        $this->logActivity($user, 'password_change', 'Password berhasil diubah');

        return $this->redirectByRole($user)
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Redirect user based on their role
     */
    private function redirectByRole(User $user): RedirectResponse
    {
        $role = session('active_role', $user->role);

        $url = match ($role) {
            'superadmin', 'kepala_sekolah' => route('admin.dashboard'),
            'admin_sekolah' => route('sekolah.dashboard'),
            'bendahara' => route('treasurer.dashboard'),
            'ketua_yayasan' => route('yayasan.dashboard'),
            'guru', 'pegawai' => route('guru.dashboard'),
            'siswa' => route('siswa.dashboard'),
            'orang_tua' => route('orangtua.dashboard'),
            'alumni' => route('alumni.dashboard'),
            default => url('/'),
        };
        
        return redirect($url);
    }

    /**
     * Switch active role for users with multiple roles
     */
    public function switchRole(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $targetRole = $request->input('role');

        // Validasi otoritas untuk role target
        $isAuthorized = match ($targetRole) {
            'superadmin' => $user->isOwnerOrSuperAdmin(),
            'ketua_yayasan' => $user->isOwnerOrSuperAdmin(),
            'guru' => $user->isOwnerOrSuperAdmin() || $user->isGuru() || $user->isKepalaSekolah() || $user->isAdminSekolah(),
            'kepala_sekolah' => $user->isKepalaSekolah() || $user->isOwnerOrSuperAdmin(),
            'pegawai' => $user->hasRole('pegawai') || $user->employee !== null || $user->isAdminSekolah() || $user->isOwnerOrSuperAdmin(),
            'admin_sekolah' => $user->isAdminSekolah(),
            'orang_tua' => $user->isOwnerOrSuperAdmin() || $user->hasRole('orang_tua') || $user->parents()->exists(),
            default => $user->hasRole($targetRole),
        };

        if (!$isAuthorized) {
            return back()->with('error', 'Anda tidak memiliki akses ke role tersebut.');
        }

        // Set role aktif di session
        session(['active_role' => $targetRole]);

        // Auto-create teacher profile if missing for Super Admin
        if ($targetRole === 'guru') {
            $teacherExists = \App\Models\Teacher::where('user_id', $user->id)->exists();
            if (!$teacherExists) {
                $schoolId = $user->school_id ?? \App\Models\School::first()->id ?? 1;
                
                // 1. Create Employee first (required)
                $employee = \App\Models\Employee::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'school_id' => $schoolId,
                        'employee_code' => 'EMP-YYS-' . $user->id,
                        'full_name' => $user->name,
                        'gender' => 'L',
                        'employee_type' => 'guru',
                        'employment_status' => 'yayasan', // Harus sesuai ENUM database
                        'tmt_date' => now()->format('Y-m-d'), // Diperlukan agar tidak error
                        'is_active' => true,
                    ]
                );

                // 2. Create Teacher linked to Employee
                \App\Models\Teacher::create([
                    'employee_id' => $employee->id,
                    'user_id' => $user->id,
                    'school_id' => $schoolId,
                    'teacher_code' => 'YYS-' . $user->id,
                    'full_name' => $user->name,
                    'gender' => 'L', // Default, bisa diubah nanti di profil
                    'position' => 'Yayasan / Super Admin',
                    'is_active' => true,
                ]);
            }
        }

        // Auto-create/update parent link with Celeste Nibenia Ogaena strictly for Bapak Yulianus Zega
        if ($targetRole === 'orang_tua' && ($user->username === 'yulzega' || $user->email === 'yulzega@gmail.com')) {
            $celeste = \App\Models\Student::where('full_name', 'LIKE', '%Celeste%')->first();
            if ($celeste) {
                \App\Models\ParentModel::updateOrCreate(
                    ['student_id' => $celeste->id, 'relation_type' => 'ayah'],
                    [
                        'user_id' => $user->id,
                        'full_name' => $user->name ?? 'Yulianus Zega, S.Kom, M.Pd.T',
                        'phone' => !empty($user->phone) && $user->phone !== '-' ? $user->phone : '0821 6853 2567',
                        'email' => $user->email ?? 'yulzega@gmail.com',
                        'occupation' => 'Ketua Yayasan / PNS',
                    ]
                );
            }
        }

        $this->logActivity($user, 'switch_role', "Beralih ke tampilan role: {$targetRole}");

        return $this->redirectByRole($user)
            ->with('success', "Berhasil beralih ke mode " . ucwords(str_replace('_', ' ', $targetRole)));
    }

    /**
     * Log activity
     */
    private function logActivity(User $user, string $action, string $description): void
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'logged_at' => now(),
        ]);
    }

    /**
     * Record login history
     */
    private function recordLoginHistory(User $user, Request $request): void
    {
        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'login_time' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * Update login history on logout
     */
    private function updateLoginHistory(User $user): void
    {
        $activeSession = LoginHistory::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('logout_time')
            ->latest()
            ->first();

        if ($activeSession) {
            $activeSession->update([
                'logout_time' => now(),
                'status' => 'logout',
            ]);
        }
    }

    /**
     * Switch active school context for multi-school teachers
     */
    public function switchSchool(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $targetSchoolId = $request->input('school_id');

        if (!$targetSchoolId) {
            return back()->with('error', 'Sekolah tujuan tidak valid.');
        }

        // Validasi akses ke sekolah tujuan
        if (!$user->canAccessSchool($targetSchoolId)) {
            return back()->with('error', 'Anda tidak memiliki akses ke unit sekolah tersebut.');
        }

        $school = \App\Models\School::find($targetSchoolId);
        if (!$school) {
            return back()->with('error', 'Unit sekolah tidak ditemukan.');
        }

        // Set active school di session
        session(['active_school_id' => (int) $targetSchoolId]);

        $this->logActivity($user, 'switch_school', "Beralih ke unit sekolah: {$school->name}");

        return back()->with('success', "Berhasil beralih ke unit {$school->name}");
    }
}
