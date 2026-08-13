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
}
