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
        
        $student = $user->student ?? Student::where('user_id', $user->id)->with('school')->first();
        $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->with('school')->first();

        return view('mobile.profile.index', compact('user', 'student', 'teacher'));
    }

    /**
     * Update user profile, photo, and complete Student/Teacher/Employee domain data
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

            // Data Biodata Lengkap Siswa
            'nisn' => 'nullable|string|max:30',
            'nis' => 'nullable|string|max:30',
            'gender' => 'nullable|string|max:20',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'parent_name' => 'nullable|string|max:150',
            'parent_phone' => 'nullable|string|max:30',
            'guardian_name' => 'nullable|string|max:150',
            'guardian_phone' => 'nullable|string|max:30',
            'hobby' => 'nullable|string|max:100',

            // Data Biodata Lengkap Guru / Pegawai
            'nip' => 'nullable|string|max:50',
            'education_level' => 'nullable|string|max:50',
            'major' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
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

        // 3. Update User Basic Info
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        // 4. Update Data Lengkap Siswa (Student Model)
        $student = $user->student ?? Student::where('user_id', $user->id)->first();
        if ($student) {
            $studentFields = [
                'name' => $validated['name'],
                'full_name' => $validated['name'],
                'email' => $validated['email'],
                'nisn' => $validated['nisn'] ?? $student->nisn,
                'nis' => $validated['nis'] ?? $student->nis,
                'gender' => $validated['gender'] ?? $student->gender,
                'birth_place' => $validated['birth_place'] ?? $student->birth_place,
                'birth_date' => $validated['birth_date'] ?? $student->birth_date,
                'religion' => $validated['religion'] ?? $student->religion,
                'address' => $validated['address'] ?? $student->address,
                'phone' => $validated['phone'] ?? $student->phone,
                'parent_name' => $validated['parent_name'] ?? $student->parent_name,
                'parent_phone' => $validated['parent_phone'] ?? $student->parent_phone,
                'guardian_name' => $validated['guardian_name'] ?? $student->guardian_name,
                'guardian_phone' => $validated['guardian_phone'] ?? $student->guardian_phone,
                'hobby' => $validated['hobby'] ?? $student->hobby,
            ];

            if (isset($path)) {
                $studentFields['photo'] = $path;
            }

            // Menggunakan fillable statik untuk efisiensi daripada mengeksekusi Schema::hasColumn berulang-ulang
            $allowedStudentFields = ['name', 'full_name', 'email', 'nisn', 'nis', 'gender', 'birth_place', 'birth_date', 'religion', 'address', 'phone', 'parent_name', 'parent_phone', 'guardian_name', 'guardian_phone', 'hobby', 'photo'];
            
            foreach ($studentFields as $col => $val) {
                if (in_array($col, $allowedStudentFields)) {
                    $student->$col = $val;
                }
            }
            $student->save();
        }

        // 5. Update Data Lengkap Guru & Pegawai (Teacher Model)
        $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
        if ($teacher) {
            $teacherFields = [
                'name' => $validated['name'],
                'full_name' => $validated['name'],
                'email' => $validated['email'],
                'nip' => $validated['nip'] ?? $teacher->nip,
                'gender' => $validated['gender'] ?? $teacher->gender,
                'birth_place' => $validated['birth_place'] ?? $teacher->birth_place,
                'birth_date' => $validated['birth_date'] ?? $teacher->birth_date,
                'religion' => $validated['religion'] ?? $teacher->religion,
                'address' => $validated['address'] ?? $teacher->address,
                'phone' => $validated['phone'] ?? $teacher->phone,
                'education_level' => $validated['education_level'] ?? $teacher->education_level,
                'major' => $validated['major'] ?? $teacher->major,
                'position' => $validated['position'] ?? $teacher->position,
            ];

            if (isset($path)) {
                $teacherFields['photo'] = $path;
            }

            $allowedTeacherFields = ['name', 'full_name', 'email', 'nip', 'gender', 'birth_place', 'birth_date', 'religion', 'address', 'phone', 'education_level', 'major', 'position', 'photo'];

            foreach ($teacherFields as $col => $val) {
                if (in_array($col, $allowedTeacherFields)) {
                    $teacher->$col = $val;
                }
            }
            $teacher->save();
        }

        return back()->with('success', 'Data Profil Lengkap & Foto Profil Anda berhasil diperbarui!');
    }
}
