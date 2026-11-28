<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function resetPasswordForm(User $user)
    {
        $this->authorize('resetPassword', $user);

        return view('admin.users.reset_password', compact('user'));
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('resetPassword', $user);

        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        try {
            $user->password = Hash::make($validated['password']);
            $user->save();
            return redirect()->route('admin.users.index')->with('success', 'Password user berhasil direset.');
        } catch (\Exception $e) {
            Log::error('Gagal mereset password: ' . $e->getMessage());
            return back()->with('error', 'Gagal mereset password. Silakan coba lagi.');
        }
    }
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $user = auth()->user();

        $query = User::with('school:id,name')
            ->select('id', 'name', 'username', 'email', 'role', 'school_id', 'is_active');

        // Scope by school_id for non-superadmin and exclude high roles
        if ($user->isAdminSekolah()) {
            $query->where('school_id', $user->school_id)
                  ->whereIn('role', ['siswa', 'guru', 'pegawai']);
        } elseif (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id)
                  ->whereNotIn('role', ['superadmin', 'admin_sekolah']);
        }

        // Search Filter
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qBuilder) use ($q) {
                $qBuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // School Filter (Only for Superadmin if they want to override)
        if ($request->filled('school_id') && $user->isSuperAdmin()) {
            $query->where('school_id', $request->school_id);
        }

        // Role Filter
        if ($request->filled('role')) {
            if ($request->role === 'kepala_sekolah') {
                $query->where(function($q) {
                    $q->where('role', 'kepala_sekolah')
                      ->orWhereIn('id', function($sub) {
                          $sub->select('user_id')
                              ->from('teachers')
                              ->whereIn('id', function($sub2) {
                                  $sub2->select('principal_id')
                                       ->from('schools')
                                       ->whereNotNull('principal_id')
                                       ->where('type', '!=', 'YAYASAN');
                              });
                      });
                });
            } else {
                $query->where('role', $request->role);
            }
        }

        $users = $query->latest('id')
            ->paginate(20)->withQueryString();

        $schools = $user->isSuperAdmin() 
            ? School::getActiveCached()
            : School::where('id', $user->school_id)->get();
            
        $roles = User::when($user->isAdminSekolah(), fn($q) => $q->whereIn('role', ['siswa', 'guru', 'pegawai']))
            ->when(!$user->isSuperAdmin() && !$user->isAdminSekolah(), fn($q) => $q->whereNotIn('role', ['superadmin', 'admin_sekolah']))
            ->distinct()
            ->pluck('role');
            
        // Tambahkan opsi filter 'kepala_sekolah' jika user adalah superadmin / ketua yayasan
        if ($user->isSuperAdmin() || $user->isKetuaYayasan()) {
            $roles->push('kepala_sekolah');
        }

        return view('admin.users.index', compact('users', 'schools', 'roles'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        // Use cached schools
        $schools = School::getActiveCached();
        return view('admin.users.create', compact('schools'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        $validated = $request->validated();

        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = $request->has('is_active');
            User::create($validated);
            return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan user: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan user. Silakan coba lagi.');
        }
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $schools = School::schoolsOnly()->get();
        return view('admin.users.edit', compact('user', 'schools'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $validated = $request->validated();

        try {
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }
            $validated['is_active'] = $request->has('is_active');
            $user->update($validated);
            return redirect()->route('admin.users.index')->with('success', 'User berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate user: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal mengupdate user. Silakan coba lagi.');
        }
    }

    public function impersonate(Request $request, User $user)
    {
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat masuk sebagai user lain.');
        }

        $superAdminId = session('impersonated_by_superadmin', $currentUser->id);
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        session([
            'impersonated_by_superadmin' => $superAdminId,
            'active_role' => $user->role,
            'active_school_id' => $user->school_id,
        ]);

        return match ($user->role) {
            'guru', 'pegawai' => redirect()->route('guru.dashboard')->with('success', "Berhasil masuk sebagai {$user->name}"),
            'siswa' => redirect()->route('siswa.dashboard')->with('success', "Berhasil masuk sebagai {$user->name}"),
            'orang_tua' => redirect()->route('orangtua.dashboard')->with('success', "Berhasil masuk sebagai {$user->name}"),
            'bendahara' => redirect()->route('treasurer.dashboard')->with('success', "Berhasil masuk sebagai {$user->name}"),
            'ketua_yayasan' => redirect()->route('yayasan.dashboard')->with('success', "Berhasil masuk sebagai {$user->name}"),
            default => redirect()->route('admin.dashboard')->with('success', "Berhasil masuk sebagai {$user->name}"),
        };
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus user: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }
}
