<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileSettingsController extends Controller
{
    /**
     * Show user profile settings form
     */
    public function edit()
    {
        $user = auth()->user();
        $biodata = null;
        if ($user->student) $biodata = $user->student;
        elseif ($user->teacher) $biodata = $user->teacher;
        else $biodata = $user->employee;

        $isBiodataEditable = now()->format('Y-m') <= '2026-07';

        return view('profile.settings', compact('user', 'biodata', 'isBiodataEditable'));
    }

    /**
     * Update user profile settings
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|nullable|string',
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
            ],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh pengguna lain.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'current_password.required_with' => 'Password saat ini wajib diisi untuk mengganti password.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
        ]);

        // If trying to change password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()
                    ->withInput($request->only('username', 'email'))
                    ->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
            }
            $user->password = Hash::make($request->password);
            
            // If they had a flag to change password, clear it
            $user->must_change_password = false;
        }

        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->save();

        // Log the activity
        ActivityLog::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'action' => 'profile_update',
            'description' => 'Memperbarui profil mandiri (username/email/password)',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_at' => now(),
        ]);

        return redirect()
            ->route('profile.settings')
            ->with('success', 'Profil dan keamanan Anda berhasil diperbarui!');
    }

    /**
     * Update user detailed biodata (Self-Service)
     */
    public function updateBiodata(Request $request)
    {
        // Fitur hanya berlaku sampai 31 Juli 2026
        if (now()->format('Y-m') > '2026-07') {
            abort(403, 'Waktu pembaruan profil mandiri telah berakhir pada Juli 2026.');
        }

        $user = auth()->user();

        // Validasi input umum
        $validated = $request->validate([
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|string|max:50',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'gender' => 'nullable|in:L,P',
            'guardian_name' => 'nullable|string|max:100',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_occupation' => 'nullable|string|max:100',
        ]);

        // Upload Photo jika ada
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
            $user->save();
        }

        $updatedData = [
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'birth_place' => $validated['birth_place'],
            'birth_date' => $validated['birth_date'],
            'religion' => $validated['religion'],
        ];

        // Update berdasar relasi profil yang dimiliki (bisa lebih dari satu)
        $profileUpdated = false;

        if ($user->student) {
            $studentData = $updatedData;
            if ($photoPath) $studentData['photo'] = $photoPath;
            if (!empty($validated['gender'])) $studentData['gender'] = $validated['gender'];
            if ($request->has('guardian_name')) {
                $studentData['guardian_name'] = $validated['guardian_name'];
                $studentData['parent_name'] = $validated['guardian_name'];
            }
            if ($request->has('guardian_phone')) {
                $studentData['guardian_phone'] = $validated['guardian_phone'];
                $studentData['parent_phone'] = $validated['guardian_phone'];
            }
            if ($request->has('guardian_occupation')) {
                $studentData['guardian_occupation'] = $validated['guardian_occupation'];
            }
            $user->student->update($studentData);
            $profileUpdated = true;
        } 
        
        if ($user->teacher) {
            $teacherData = $updatedData;
            if ($photoPath) $teacherData['photo'] = $photoPath;
            $user->teacher->update($teacherData);
            
            // Jika ada relasi employee, update juga
            if ($user->teacher->employee) {
                $user->teacher->employee->update($teacherData);
            }
            $profileUpdated = true;
        } 
        
        if ($user->employee && !$user->teacher) { // Jika hanya employee (bukan guru)
            $employeeData = $updatedData;
            if ($photoPath) $employeeData['photo'] = $photoPath;
            $user->employee->update($employeeData);
            $profileUpdated = true;
        }
        
        // Super admin murni tanpa relasi guru/pegawai/siswa tidak memiliki tabel profil detail
        if (!$profileUpdated && !$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            return back()->withErrors(['biodata' => 'Data profil detail tidak ditemukan.']);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'action' => 'profile_biodata_update',
            'description' => 'Memperbarui biodata diri secara mandiri',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Biodata diri berhasil diperbarui!');
    }
}
