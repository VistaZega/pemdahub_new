<?php

namespace App\Http\Controllers;

use App\Models\AlumniDirectory;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicAlumniController extends Controller
{
    /**
     * Show the public registration form.
     */
    public function registerForm()
    {
        // Exclude yayasan, get all unit schools including historical ones
        $schools = School::where('type', '!=', 'yayasan')->orderBy('name')->get();
        // Array of years from 1970 to current year
        $years = range(now()->year, 1970);
        
        // Fetch alumni for gallery preview (no approval needed)
        $approvedAlumni = AlumniDirectory::with('school')
                            ->latest()
                            ->take(12)
                            ->get();
                            
        // Smart Report Data
        $oldestAlumni = AlumniDirectory::min('graduation_year');
        $youngestAlumni = AlumniDirectory::max('graduation_year');
        $totalRegistered = AlumniDirectory::count();
        
        return view('landing.alumni_register', compact('schools', 'years', 'approvedAlumni', 'oldestAlumni', 'youngestAlumni', 'totalRegistered'));
    }

    /**
     * Display public directory of Alumni with filters per Ikatan Alumni unit school.
     */
    public function directory(Request $request)
    {
        $schools = School::where('type', '!=', 'yayasan')->orderBy('name')->get();
        $years = range(now()->year, 1970);

        $query = AlumniDirectory::with('school')->where('is_approved', true)->latest();

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('graduation_year')) {
            $query->where('graduation_year', $request->graduation_year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('alias_name', 'like', "%{$search}%")
                  ->orWhere('occupation', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $alumnis = $query->paginate(16)->withQueryString();

        $totalAlumni = AlumniDirectory::count();
        $selectedSchool = $request->filled('school_id') ? School::find($request->school_id) : null;

        return view('landing.alumni_directory', compact('alumnis', 'schools', 'years', 'totalAlumni', 'selectedSchool'));
    }

    /**
     * Handle the registration submission.
     */
    public function registerSubmit(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'alias_name' => 'nullable|string|max:255',
            'gender' => 'required|in:L,P',
            'marital_status' => 'nullable|string|max:50',
            'children_count' => 'nullable|integer|min:0',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'jurusan' => 'nullable|string|max:255',
            'graduation_year' => 'required|integer|min:1970|max:' . now()->year,
            'last_class' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:4096', // max 4MB
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('alumni_photos', $filename, 'public');
        }

        // Create User account for alumni
        $email = $validated['email'] ?? null;
        // Check if email exists in users table to prevent unique constraint violation
        if ($email && \App\Models\User::where('email', $email)->exists()) {
            $email = 'alumni_' . uniqid() . '@pembda.local';
        } elseif (!$email) {
            $email = 'alumni_' . uniqid() . '@pembda.local';
        }

        // Generate username: alumni_firstname
        $firstName = strtolower(explode(' ', $validated['full_name'])[0]);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $firstName);
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }
        $username = 'alumni_' . $baseUsername;
        $counter = 1;
        while (\App\Models\User::where('username', $username)->exists()) {
            $username = 'alumni_' . $baseUsername . $counter;
            $counter++;
        }

        $defaultPassword = 'pembda' . now()->year;

        $user = \App\Models\User::create([
            'name' => $validated['full_name'],
            'username' => $username,
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
            'role' => 'alumni',
            'school_id' => $validated['school_id'],
            'is_active' => true,
        ]);

        AlumniDirectory::create([
            'user_id' => $user->id,
            'full_name' => $validated['full_name'],
            'alias_name' => $validated['alias_name'] ?? null,
            'gender' => $validated['gender'],
            'marital_status' => $validated['marital_status'] ?? null,
            'children_count' => $validated['children_count'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'school_id' => $validated['school_id'],
            'jurusan' => $validated['jurusan'] ?? null,
            'graduation_year' => $validated['graduation_year'],
            'last_class' => $validated['last_class'] ?? null,
            'message' => $validated['message'] ?? null,
            'photo_path' => $photoPath,
            'is_approved' => true, // Auto approved as requested
        ]);

        return redirect()->back()->with('success', 'Terima kasih! Data Anda telah ditambahkan ke Direktori Alumni. Sistem telah membuatkan akun untuk Anda mengakses Pembda Space. Username Anda: <strong>' . $username . '</strong> dan Password: <strong>' . $defaultPassword . '</strong>. Silakan catat dan gunakan untuk Login.');
    }
}
