<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $login = trim((string) $request->username);
        $password = (string) $request->password;

        // 1. Search by username or email (case-insensitive)
        $user = User::where(function ($query) use ($login) {
            $query->where('username', $login)
                  ->orWhere('email', $login)
                  ->orWhereRaw('LOWER(username) = ?', [strtolower($login)])
                  ->orWhereRaw('LOWER(email) = ?', [strtolower($login)]);
        })->first();

        // 2. If not found, try searching via Student NIS / NISN
        if (!$user) {
            $student = Student::where('nis', $login)->orWhere('nisn', $login)->first();
            if ($student && $student->user_id) {
                $user = User::find($student->user_id);
            }
        }

        // 3. If not found, try searching via Teacher NIP / Code
        if (!$user) {
            $teacher = Teacher::where('nip', $login)->orWhere('teacher_code', $login)->first();
            if ($teacher && $teacher->user_id) {
                $user = User::find($teacher->user_id);
            }
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/Email atau password salah.'
            ], 401);
        }

        if (isset($user->is_active) && !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi admin.'
            ], 403);
        }

        $deviceName = $request->device_name ?? 'mobile_app';
        $token = $user->createToken($deviceName)->plainTextToken;

        if (method_exists($user, 'updateLastLogin')) {
            $user->updateLastLogin();
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => $this->formatUserResponse($user)
            ]
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout',
            'data' => null
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatUserResponse($request->user())
        ]);
    }

    private function formatUserResponse(User $user)
    {
        $user->loadMissing([
            'student.classroom', 
            'teacher', 
            'school', 
            'reputation'
        ]);

        $avatarUrl = null;
        if (!empty($user->photo)) {
            $avatarUrl = str_starts_with($user->photo, 'http') ? $user->photo : asset('storage/' . $user->photo);
        }

        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'username' => (string) ($user->username ?? $user->email),
            'role' => (string) $user->role,
            'avatar_url' => $avatarUrl,
            'school_name' => $user->school ? (string) $user->school->name : null,
            'classroom_name' => ($user->student && $user->student->classroom) ? (string) $user->student->classroom->name : null,
            'ekskul_flair' => $user->ekskul_flair,
            'reputation_points' => $user->reputation ? (int) $user->reputation->total_points : 0,
            'reputation_tier' => ($user->reputation && $user->reputation->level_name) ? (string) $user->reputation->level_name : 'Newbie',
        ];
    }
}
