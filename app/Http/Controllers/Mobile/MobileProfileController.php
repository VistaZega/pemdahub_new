<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MobileProfileController extends Controller
{
    /**
     * Display profile page
     */
    public function index()
    {
        $user = Auth::user();
        $student = null;
        $teacher = null;

        if ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->with('school')->first();
        } elseif (in_array($user->role, ['guru', 'pegawai'])) {
            $teacher = Teacher::where('user_id', $user->id)->with('school')->first();
        }

        return view('mobile.profile.index', compact('user', 'student', 'teacher'));
    }

    /**
     * Update user profile & photo
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        // 1. Handle Password Update
        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->with('error', 'Kata sandi saat ini tidak cocok.');
            }
            $user->password = Hash::make($validated['new_password']);
        }

        // 2. Handle Photo Profile Upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            // Hapus photo lama dari storage jika ada
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $user->photo = $path;
        }

        // 3. Update User Basic Info (Only name, email, photo - NOT phone)
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        // 4. Sync Updates to Associated Student/Teacher Model (where phone column actually exists)
        if ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                if (Schema::hasColumn('students', 'name')) {
                    $student->name = $validated['name'];
                }
                if (Schema::hasColumn('students', 'full_name')) {
                    $student->full_name = $validated['name'];
                }
                if (Schema::hasColumn('students', 'email')) {
                    $student->email = $validated['email'];
                }
                if (isset($validated['phone']) && Schema::hasColumn('students', 'phone')) {
                    $student->phone = $validated['phone'];
                }
                if (isset($path) && Schema::hasColumn('students', 'photo')) {
                    $student->photo = $path;
                }
                $student->save();
            }
        } elseif (in_array($user->role, ['guru', 'pegawai'])) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                if (Schema::hasColumn('teachers', 'name')) {
                    $teacher->name = $validated['name'];
                }
                if (Schema::hasColumn('teachers', 'full_name')) {
                    $teacher->full_name = $validated['name'];
                }
                if (Schema::hasColumn('teachers', 'email')) {
                    $teacher->email = $validated['email'];
                }
                if (isset($validated['phone']) && Schema::hasColumn('teachers', 'phone')) {
                    $teacher->phone = $validated['phone'];
                }
                if (isset($path) && Schema::hasColumn('teachers', 'photo')) {
                    $teacher->photo = $path;
                }
                $teacher->save();
            }
        }

        return back()->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }
}
