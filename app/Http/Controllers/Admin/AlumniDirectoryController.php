<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniDirectory;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AlumniDirectoryController extends Controller
{
    /**
     * Display a listing of the alumni directory.
     */
    public function index(Request $request)
    {
        $schools = School::where('type', '!=', 'yayasan')->orderBy('name')->get();
        $years = range(now()->year, 1970);

        $pendingCount = AlumniDirectory::where('is_approved', false)->count();

        $query = AlumniDirectory::with('school')->latest();

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

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
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $directories = $query->paginate(20)->withQueryString();

        return view('admin.pkl_alumni.directory.index', compact('directories', 'schools', 'years', 'pendingCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::where('type', '!=', 'yayasan')->orderBy('name')->get();
        $years = range(now()->year, 1970);
        return view('admin.pkl_alumni.directory.create', compact('schools', 'years'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('alumni_photos', $filename, 'public');
        }

        $validated['photo_path'] = $photoPath;
        $validated['is_approved'] = true;

        AlumniDirectory::create($validated);

        return redirect()->route('admin.alumni-directory.index')->with('success', 'Data alumni berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AlumniDirectory $directory)
    {
        return view('admin.pkl_alumni.directory.show', compact('directory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AlumniDirectory $directory)
    {
        $schools = School::where('type', '!=', 'yayasan')->orderBy('name')->get();
        $years = range(now()->year, 1970);
        return view('admin.pkl_alumni.directory.edit', compact('directory', 'schools', 'years'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlumniDirectory $directory)
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        if ($request->hasFile('photo')) {
            if ($directory->photo_path) {
                Storage::disk('public')->delete($directory->photo_path);
            }
            $file = $request->file('photo');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $validated['photo_path'] = $file->storeAs('alumni_photos', $filename, 'public');
        }

        $directory->update($validated);

        return redirect()->route('admin.alumni-directory.index')->with('success', 'Data alumni berhasil diperbarui.');
    }

    /**
     * Approve or unapprove the registration to be shown publicly and activate/deactivate user account.
     */
    public function toggleApproval(AlumniDirectory $directory)
    {
        $directory->is_approved = !$directory->is_approved;
        $directory->save();

        if ($directory->user_id) {
            $user = \App\Models\User::find($directory->user_id);
            if ($user) {
                $user->is_active = $directory->is_approved;
                $user->save();
            }
        }

        $msg = $directory->is_approved ? 'Data alumni berhasil disetujui & akun user diaktifkan.' : 'Persetujuan data alumni dibatalkan & akun user dinonaktifkan.';
        return back()->with('success', $msg);
    }

    /**
     * Remove the specified resource from storage and associated user account.
     */
    public function destroy(AlumniDirectory $directory)
    {
        if ($directory->photo_path) {
            Storage::disk('public')->delete($directory->photo_path);
        }

        if ($directory->user_id) {
            $user = \App\Models\User::find($directory->user_id);
            if ($user && $user->role === 'alumni') {
                $user->delete();
            }
        }
        
        $directory->delete();

        return redirect()->route('admin.alumni-directory.index')->with('success', 'Data alumni dan akun terkait berhasil dihapus.');
    }

    /**
     * Purge spam registrations (unapproved alumni matching sensitive terms or spam URLs).
     */
    public function purgeSpam(Request $request)
    {
        $forbiddenKeywords = ['admin', 'administrator', 'bantuan', 'support', 'helpdesk', 'operator', 'yayasan', 'official', 'moderator', 'mod', 'pembda', 'customer service', 'cs', 'slot', 'gacor', 'judol'];
        
        $query = AlumniDirectory::where('is_approved', false);

        $query->where(function($q) use ($forbiddenKeywords) {
            foreach ($forbiddenKeywords as $word) {
                $q->orWhere('full_name', 'like', "%{$word}%")
                  ->orWhere('alias_name', 'like', "%{$word}%");
            }
            $q->orWhere('message', 'like', '%http%')
              ->orWhere('message', 'like', '%https%')
              ->orWhere('message', 'like', '%.xyz%')
              ->orWhere('message', 'like', '%.top%')
              ->orWhere('company_name', 'like', '%http%')
              ->orWhere('company_name', 'like', '%https%');
        });

        $spamRecords = $query->get();
        $count = 0;

        foreach ($spamRecords as $rec) {
            if ($rec->user_id) {
                $user = \App\Models\User::find($rec->user_id);
                if ($user && $user->role === 'alumni') {
                    $user->delete();
                }
            }
            if ($rec->photo_path) {
                Storage::disk('public')->delete($rec->photo_path);
            }
            $rec->delete();
            $count++;
        }

        return back()->with('success', "Pembersihan selesai! {$count} data alumni spam & akun palsu yang menggantung telah berhasil dihapus.");
    }
}
