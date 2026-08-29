<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginHistory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileAuthController extends Controller
{
    /**
     * Show mobile login form
     */
    public function showLoginForm()
    {
        return view('mobile.auth.login');
    }

    /**
     * Handle mobile login submission
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $login = trim((string) $request->input('login'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($fieldType, $login)->first();

        if (!$user || !$user->is_active) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Akun tidak ditemukan atau tidak aktif.']);
        }

        if (Auth::attempt([$fieldType => $login, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            $user->updateLastLogin();

            ActivityLog::create([
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'action' => 'login_mobile',
                'description' => 'Login via PembdaHUB Mobile App',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_at' => now(),
            ]);

            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
                'login_time' => now(),
                'status' => 'active',
            ]);

            return redirect()->route('mobile.dashboard');
        }

        return back()
            ->withInput($request->only('login'))
            ->withErrors(['password' => 'Password tidak sesuai.']);
    }

    /**
     * Handle mobile logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'action' => 'logout_mobile',
                'description' => 'Logout dari PembdaHUB Mobile App',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_at' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.login');
    }

    /**
     * Switch active role for users with multiple roles in Mobile
     */
    public function switchRole(Request $request)
    {
        $user = Auth::user();
        $targetRole = $request->input('role');

        $availableRoles = collect($user->getAvailableMobileRoles())->pluck('key')->all();

        if (!in_array($targetRole, $availableRoles)) {
            return back()->with('error', 'Anda tidak memiliki akses ke role tersebut.');
        }

        session(['active_role' => $targetRole]);

        // Auto-create teacher profile if missing for Super Admin
        if ($targetRole === 'guru') {
            $teacherExists = \App\Models\Teacher::where('user_id', $user->id)->exists();
            if (!$teacherExists) {
                $schoolId = $user->school_id ?? \App\Models\School::first()->id ?? 1;
                $employee = \App\Models\Employee::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'school_id' => $schoolId,
                        'employee_code' => 'EMP-YYS-' . $user->id,
                        'full_name' => $user->name,
                        'gender' => 'L',
                        'employee_type' => 'guru',
                        'employment_status' => 'yayasan',
                        'tmt_date' => now()->format('Y-m-d'),
                        'is_active' => true,
                    ]
                );

                \App\Models\Teacher::create([
                    'employee_id' => $employee->id,
                    'user_id' => $user->id,
                    'school_id' => $schoolId,
                    'teacher_code' => 'YYS-' . $user->id,
                    'full_name' => $user->name,
                    'gender' => 'L',
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

        ActivityLog::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'action' => 'switch_role_mobile',
            'description' => "Beralih ke tampilan role: {$targetRole}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_at' => now(),
        ]);

        return redirect()->route('mobile.dashboard')->with('success', 'Berhasil beralih ke peran ' . ucwords(str_replace('_', ' ', $targetRole)));
    }

    /**
     * Switch active duty / structural position for teachers & staff in Mobile
     */
    public function switchDuty(Request $request)
    {
        $user = Auth::user();
        $targetDuty = $request->input('duty');

        $availableDuties = collect($user->getAvailableDuties())->pluck('key')->all();

        if (!in_array($targetDuty, $availableDuties)) {
            return back()->with('error', 'Anda tidak memiliki penugasan jabatan tersebut.');
        }

        session(['active_duty' => $targetDuty]);

        $dutyNames = [
            'pengampu' => 'Guru Pengampu KBM',
            'wali_kelas' => 'Wali Kelas',
            'bk_pks' => 'Guru BK & PKS Piket',
            'panitia_pkl' => 'Panitia PKL',
            'panitia_proyek' => 'Panitia Proyek / Penelitian',
            'pembimbing_pkl' => 'Pembimbing PKL DUDI',
            'pembimbing_proyek' => 'Pembimbing & Penguji TA',
            'pembina_ekskul' => 'Pembina Ekskul',
        ];

        $dutyLabel = $dutyNames[$targetDuty] ?? ucwords(str_replace('_', ' ', $targetDuty));

        ActivityLog::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'action' => 'switch_duty_mobile',
            'description' => "Beralih ke fokus jabatan: {$dutyLabel}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_at' => now(),
        ]);

        return redirect()->route('mobile.dashboard')->with('success', "Fokus jabatan aktif: {$dutyLabel}");
    }
}
