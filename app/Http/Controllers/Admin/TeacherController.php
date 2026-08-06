<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\Employee;
use App\Models\School;
use App\Models\User;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $query = Teacher::with(['school', 'user', 'employee.activePositions' => function ($q) use ($activeYear) {
                if ($activeYear) {
                    $q->wherePivot('academic_year_id', $activeYear->id);
                }
            }, 'competentSubjects', 'subjects'])
            ->select('teachers.*')
            ->addSelect(['min_position_level' => function ($q) use ($activeYear) {
                $q->selectRaw('COALESCE(MIN(positions.position_level), 999)')
                    ->from('employee_positions')
                    ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                    ->whereColumn('employee_positions.employee_id', 'teachers.employee_id')
                    ->when($activeYear, fn($qy) => $qy->where('employee_positions.academic_year_id', $activeYear->id))
                    ->whereNull('employee_positions.end_date');
            }])
            ->orderBy('min_position_level', 'asc')
            ->orderBy('teacher_code', 'asc')
            ->orderBy('full_name', 'asc');

        // Auto-filter by school_id for non-superadmin
        if (!$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhereHas('additionalSchools', function ($q2) use ($user) {
                      $q2->where('schools.id', $user->school_id);
                  });
            });
        }

        // Filter by school (only for superadmin)
        if ($request->filled('school_id') && $user->isSuperAdmin()) {
            $schoolId = $request->school_id;
            $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('additionalSchools', function ($q2) use ($schoolId) {
                      $q2->where('schools.id', $schoolId);
                  });
            });
        }

        // Filter by status (default: hanya tampilkan yang aktif)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        } else {
            $query->where('is_active', 1);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('teacher_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(15)->withQueryString();
        
        // Schools dropdown: superadmin sees all, admin sekolah sees only their school
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->schoolsOnly()->get()
            : School::where('id', $user->school_id)->get();

        $isPasswordReset = false;
        if ($request->filled('school_id') || !$user->isSuperAdmin()) {
            $sampleTeacher = collect($teachers->items())->first(function ($teacher) {
                return $teacher->user !== null;
            });
            
            if ($sampleTeacher) {
                $expectedPassword = 'Pembda' . $sampleTeacher->teacher_code;
                if (\Illuminate\Support\Facades\Hash::check($expectedPassword, $sampleTeacher->user->password)) {
                    $isPasswordReset = true;
                }
            }
        }

        return view('admin.teachers.index', compact('teachers', 'schools', 'isPasswordReset'));
    }

    public function create()
    {
        if (!auth()->user()->canManageEmploymentData()) {
            abort(403, 'Unauthorized. Hanya Super Admin dan Admin Sekolah yang dapat mengelola data guru.');
        }

        $schools = School::where('is_active', 1)->schoolsOnly()->get();
        $subjects = Subject::where('is_active', 1)->get();
        
        return view('admin.teachers.create', compact('schools', 'subjects'));
    }

    public function store(StoreTeacherRequest $request)
    {
        if (!auth()->user()->canManageEmploymentData()) {
            abort(403, 'Unauthorized. Hanya Super Admin dan Admin Sekolah yang dapat mengelola data guru.');
        }

        $validated = $request->validated();

        if (!auth()->user()->canManageBasicSalary()) {
            $validated['basic_salary'] = 0;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('teachers', 'public');
            $validated['photo'] = $photoPath;
        }

        try {
            return DB::transaction(function () use ($request, $validated) {
                // Create user account if requested
                $userId = null;
                if ($request->create_account && $request->email) {
                    $user = User::create([
                        'name' => $validated['full_name'],
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => 'guru',
                        'school_id' => $validated['school_id'],
                        'is_active' => $validated['is_active'] ?? 1,
                    ]);
                    $userId = $user->id;
                }

                // Create Employee
                $employee = Employee::create([
                    'school_id' => $validated['school_id'],
                    'user_id' => $userId,
                    'employee_code' => $validated['teacher_code'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'birth_place' => $validated['birth_place'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'email' => $validated['email_employee'] ?? null,
                    'photo' => $validated['photo'] ?? null,
                    'employee_type' => 'guru',
                    'employment_status' => $validated['employment_status'],
                    'marital_status' => $validated['marital_status'],
                    'children_count' => $validated['children_count'] ?? 0,
                    'tmt_date' => $validated['tmt_date'],
                    'basic_salary' => $validated['basic_salary'] ?? 0,
                    'is_active' => $request->has('is_active') ? 1 : 0,
                ]);

                // Create Teacher
                $teacher = Teacher::create([
                    'employee_id' => $employee->id,
                    'user_id' => $userId,
                    'school_id' => $validated['school_id'],
                    'teacher_code' => $validated['teacher_code'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'education_level' => $validated['education_level'] ?? null,
                    'major' => $validated['major'] ?? null,
                    'birth_place' => $validated['birth_place'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'photo' => $validated['photo'] ?? null,
                    'position' => null,
                    'is_active' => $request->has('is_active') ? 1 : 0,
                ]);

                if ($request->has('additional_school_ids')) {
                    $teacher->additionalSchools()->sync($request->additional_school_ids);
                }

                return redirect()->route('admin.teachers.index')
                    ->with('success', 'Data guru berhasil ditambahkan.');
            });
        } catch (\Exception $e) {
            // Clean up uploaded photo on failure
            if (isset($validated['photo'])) {
                Storage::disk('public')->delete($validated['photo']);
            }
            Log::error('Gagal menambahkan guru: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan data guru. Silakan coba lagi.');
        }
    }

    public function show(Teacher $teacher, Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $teacher->load(['school', 'user', 'employee.activePositions' => function ($q) use ($activeYear) {
            if ($activeYear) {
                $q->wherePivot('academic_year_id', $activeYear->id);
            }
        }, 'subjects', 'schedules.classroom', 'schedules.subject', 'competentSubjects']);
        $returnUrl = $request->query('return_url');
        
        return view('admin.teachers.show', compact('teacher', 'returnUrl'));
    }

    public function edit(Teacher $teacher, Request $request)
    {
        if (!auth()->user()->canManageEmploymentData()) {
            abort(403, 'Unauthorized. Hanya Super Admin dan Admin Sekolah yang dapat mengelola data guru.');
        }

        $schools = School::where('is_active', 1)->schoolsOnly()->get();
        $subjects = Subject::where('is_active', 1)->get();
        
        // Load employee relationship
        $teacher->load('employee');
        $returnUrl = $request->query('return_url');
        
        return view('admin.teachers.edit', compact('teacher', 'schools', 'subjects', 'returnUrl'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        if (!auth()->user()->canManageEmploymentData()) {
            abort(403, 'Unauthorized. Hanya Super Admin dan Admin Sekolah yang dapat mengelola data guru.');
        }

        $validated = $request->validated();

        if (!auth()->user()->canManageBasicSalary()) {
            $validated['basic_salary'] = $teacher->employee ? ($teacher->employee->basic_salary ?? 0) : 0;
        }

        // Handle photo removal
        if ($request->remove_photo && $teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
            $validated['photo'] = null;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }
            $photoPath = $request->file('photo')->store('teachers', 'public');
            $validated['photo'] = $photoPath;
        }

        $isActive = $request->has('is_active') ? 1 : 0;

        try {
            return DB::transaction(function () use ($validated, $teacher, $isActive, $request) {
                // Update Employee
                $teacher->employee->update([
                    'school_id' => $validated['school_id'],
                    'employee_code' => $validated['teacher_code'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'birth_place' => $validated['birth_place'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'email' => $validated['email_employee'] ?? null,
                    'photo' => $validated['photo'] ?? $teacher->photo,
                    'employment_status' => $validated['employment_status'],
                    'marital_status' => $validated['marital_status'],
                    'children_count' => $validated['children_count'] ?? 0,
                    'tmt_date' => $validated['tmt_date'],
                    'basic_salary' => $validated['basic_salary'] ?? 0,
                    'is_active' => $isActive,
                ]);

                // Update Teacher
                $teacher->update([
                    'school_id' => $validated['school_id'],
                    'teacher_code' => $validated['teacher_code'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'education_level' => $validated['education_level'] ?? null,
                    'major' => $validated['major'] ?? null,
                    'birth_place' => $validated['birth_place'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'photo' => $validated['photo'] ?? $teacher->photo,
                    'is_active' => $isActive,
                ]);

                // Sync additional schools
                if ($request->has('additional_school_ids')) {
                    $teacher->additionalSchools()->sync($request->additional_school_ids);
                } else {
                    $teacher->additionalSchools()->sync([]);
                }

                // Update user if exists
                if ($teacher->user) {
                    $teacher->user->update([
                        'name' => $validated['full_name'],
                        'is_active' => $isActive,
                    ]);
                }

                if ($request->filled('return_url')) {
                    return redirect($request->return_url)
                        ->with('success', 'Data guru berhasil diperbarui.');
                }

                return redirect()->route('admin.teachers.index')
                    ->with('success', 'Data guru berhasil diperbarui.');
            });
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui guru: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui guru. Silakan coba lagi.');
        }
    }

    public function destroy(Teacher $teacher)
    {
        if (!auth()->user()->canManageEmploymentData()) {
            abort(403, 'Unauthorized. Hanya Super Admin dan Admin Sekolah yang dapat menghapus data guru.');
        }

        try {
            return DB::transaction(function () use ($teacher) {
                // Delete photo
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }

                // Delete associated employee & employee positions
                if ($teacher->employee_id) {
                    $employee = \App\Models\Employee::find($teacher->employee_id);
                    if ($employee) {
                        $employee->employeePositions()->delete();
                        $employee->delete();
                    }
                }

                // Delete associated user account
                if ($teacher->user_id) {
                    User::find($teacher->user_id)?->delete();
                }

                $teacher->delete();

                return redirect()->route('admin.teachers.index')
                    ->with('success', 'Data guru berhasil dihapus.');
            });
        } catch (\Exception $e) {
            Log::error('Gagal menghapus guru: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus guru. Silakan coba lagi.');
        }
    }

    public function updateRfid(Request $request, Teacher $teacher)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $user->school_id !== $teacher->school_id) {
            abort(403, 'Unauthorized');
        }

        $employee = $teacher->employee;
        if (!$employee) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan untuk guru ini.');
        }

        $request->validate([
            'rfid_uid' => [
                'required',
                'string',
                'max:50',
                'unique:employees,rfid_uid,' . $employee->id,
                'unique:students,rfid_uid',
            ]
        ], [
            'rfid_uid.unique' => 'Kartu RFID ini sudah terdaftar untuk pengguna lain.'
        ]);

        $employee->update([
            'rfid_uid' => strtoupper(trim($request->rfid_uid))
        ]);

        return redirect()->back()->with('success', "RFID UID berhasil didaftarkan untuk guru {$teacher->full_name}.");
    }

    /**
     * Print login accounts for teachers in a specific school
     */
    public function printAccounts(Request $request)
    {
        $schoolId = $request->get('school_id');
        
        if (!$schoolId) {
            return back()->with('error', 'Pilih unit sekolah terlebih dahulu untuk mencetak daftar akun guru.');
        }

        $query = Teacher::with('user')->orderBy('full_name')->where('school_id', $schoolId);
        $title = "Daftar Akun Guru - Unit " . \App\Models\School::find($schoolId)?->name;
        $teachers = $query->get();

        return view('admin.teachers.print_accounts', compact('teachers', 'title'));
    }

    /**
     * Export login accounts to Excel for teachers
     */
    public function exportAccounts(Request $request)
    {
        $schoolId = $request->get('school_id');
        
        if (!$schoolId) {
            return back()->with('error', 'Pilih unit sekolah terlebih dahulu untuk export akun guru.');
        }
        
        return Excel::download(new \App\Exports\TeacherAccountsExport($schoolId), 'akun_guru.xlsx');
    }

    /**
     * Reset passwords for teachers in a specific school to a standard pattern
     */
    public function resetPasswords(Request $request)
    {
        $schoolId = $request->get('school_id');
        
        if (!$schoolId) {
            return back()->with('error', 'Pilih unit sekolah terlebih dahulu untuk mereset password guru.');
        }

        $teachers = Teacher::with('user')->where('school_id', $schoolId)->get();

        $count = 0;
        foreach ($teachers as $teacher) {
            if ($teacher->user) {
                // Pola: Pembda + Kode Guru atau NIK
                // For simplicity, we use Pembda + teacher_code (or id if null)
                $code = $teacher->teacher_code ?: $teacher->id;
                $newPassword = 'Pembda' . $code;
                $teacher->user->update([
                    'password' => \Illuminate\Support\Facades\Hash::make($newPassword)
                ]);
                $count++;
            }
        }

        return back()->with('success', "Berhasil mereset password {$count} guru menjadi pola standar (Pembda + Kode Guru).");
    }
}
