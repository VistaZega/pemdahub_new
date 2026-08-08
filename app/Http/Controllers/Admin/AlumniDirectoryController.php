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

        $query = AlumniDirectory::with('school')->latest();

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

        return view('admin.pkl_alumni.directory.index', compact('directories', 'schools', 'years'));
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
     * Approve the registration to be shown publicly if needed.
     */
    public function toggleApproval(AlumniDirectory $directory)
    {
        $directory->is_approved = !$directory->is_approved;
        $directory->save();

        return back()->with('success', 'Status persetujuan alumni berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlumniDirectory $directory)
    {
        if ($directory->photo_path) {
            Storage::disk('public')->delete($directory->photo_path);
        }
        
        $directory->delete();

        return redirect()->route('admin.alumni-directory.index')->with('success', 'Data alumni berhasil dihapus.');
    }
}
