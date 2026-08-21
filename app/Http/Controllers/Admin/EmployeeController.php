<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamilyMember;
use App\Models\EmployeeLeave;
use App\Models\EmployeeTraining;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = auth()->user();
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        // Base query for non-teaching staff
        $query = Employee::query()
            ->select('employees.*')
            ->where('employee_type', '!=', 'guru')
            ->addSelect(['min_position_level' => function ($q) use ($activeYear) {
                $q->selectRaw('COALESCE(MIN(positions.position_level), 999)')
                    ->from('employee_positions')
                    ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                    ->whereColumn('employee_positions.employee_id', 'employees.id')
                    ->when($activeYear, fn($qy) => $qy->where('employee_positions.academic_year_id', $activeYear->id))
                    ->whereNull('employee_positions.end_date');
            }])
            ->orderBy('min_position_level', 'asc')
            ->orderBy('employee_code', 'asc')
            ->orderBy('full_name', 'asc');

        $canAccessAllSchools = $user->canAccessAllSchools();

        // Admin sekolah: only their school
        if (!$canAccessAllSchools) {
            $query->where('school_id', $user->school_id);
        }

        // Filter by school (for superadmin / yayasan)
        if ($request->filled('school_id') && $canAccessAllSchools) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by employee type
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $employees = $query->with('school')->paginate(15)->withQueryString();
        
        // Schools dropdown: superadmin / yayasan sees all, admin sekolah sees only their school
        $schools = $canAccessAllSchools 
            ? School::where('is_active', 1)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        return view('admin.employees.index', compact('employees', 'schools'));
    }

    private function checkEmploymentPermission(): void
    {
        if (!auth()->user()->canManageEmploymentData()) {
            abort(403, 'Unauthorized. Hanya Super Admin dan Admin Sekolah yang dapat mengelola data kepegawaian.');
        }
    }

    public function create()
    {
        $this->checkEmploymentPermission();

        $schools = School::where('is_active', 1)->orderBy('name')->get();
        
        return view('admin.employees.create', compact('schools'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validated();

        if (!auth()->user()->canManageBasicSalary()) {
            $validated['basic_salary'] = 0;
        }

        try {
            return DB::transaction(function () use ($request, $validated) {
                // Handle photo upload
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('employees', 'public');
                    $validated['photo'] = $photoPath;
                }

                // Auto Generate User Account
                $firstName = strtolower(explode(' ', trim($validated['full_name']))[0]);
                $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
                if (empty($firstName)) {
                    $firstName = 'pegawai' . rand(100, 999);
                }

                $baseEmail = $firstName . '@pembdahub.com';
                $email = $baseEmail;
                $username = $firstName;
                $counter = 1;

                while (User::where('email', $email)->orWhere('username', $username)->exists()) {
                    $email = $firstName . $counter . '@pembdahub.com';
                    $username = $firstName . $counter;
                    $counter++;
                }

                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $email,
                    'username' => $username,
                    'password' => Hash::make('pembdahub2026'),
                    'role' => 'pegawai',
                    'school_id' => $validated['school_id'],
                    'is_active' => $request->has('is_active') ? 1 : 0,
                ]);

                $employee = Employee::create([
                    'school_id' => $validated['school_id'],
                    'user_id' => $user->id,
                    'employee_code' => $validated['employee_code'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'birth_place' => $validated['birth_place'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'email' => $validated['email'] ?? $email,
                    'photo' => $validated['photo'] ?? null,
                    'employee_type' => $validated['employee_type'],
                    'employment_status' => $validated['employment_status'],
                    'marital_status' => $validated['marital_status'] ?? null,
                    'children_count' => $validated['children_count'] ?? 0,
                    'basic_salary' => $validated['basic_salary'] ?? 0,
                    'tmt_date' => $validated['tmt_date'],
                    'is_active' => $request->has('is_active') ? 1 : 0,
                ]);

                return redirect()->route('admin.employees.index')
                    ->with('success', 'Data pegawai berhasil ditambahkan dan akun berhasil dibuat.');
            });
        } catch (\Exception $e) {
            // Clean up uploaded photo on failure
            if (isset($validated['photo'])) {
                Storage::disk('public')->delete($validated['photo']);
            }
            Log::error('Gagal menambahkan pegawai: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan pegawai. Silakan coba lagi.');
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['school', 'user', 'activePositions', 'educations', 'trainings', 'documents', 'familyMembers', 'contracts']);
        
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $this->checkEmploymentPermission();

        $schools = School::where('is_active', 1)->orderBy('name')->get();
        
        return view('admin.employees.edit', compact('employee', 'schools'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validated();

        if (!auth()->user()->canManageBasicSalary()) {
            $validated['basic_salary'] = $employee->basic_salary ?? 0;
        }

        try {
            // Handle photo removal
            if ($request->remove_photo && $employee->photo) {
                Storage::disk('public')->delete($employee->photo);
                $validated['photo'] = null;
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $photoPath = $request->file('photo')->store('employees', 'public');
                $validated['photo'] = $photoPath;
            }

            $isActive = $request->has('is_active') ? 1 : 0;

            $employee->update([
                'school_id' => $validated['school_id'],
                'employee_code' => $validated['employee_code'],
                'full_name' => $validated['full_name'],
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'religion' => $validated['religion'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'photo' => $validated['photo'] ?? $employee->photo,
                'employee_type' => $validated['employee_type'],
                'employment_status' => $validated['employment_status'],
                'marital_status' => $validated['marital_status'],
                'children_count' => $validated['children_count'] ?? 0,
                'basic_salary' => $validated['basic_salary'] ?? 0,
                'tmt_date' => $validated['tmt_date'],
                'is_active' => $isActive,
            ]);

            return redirect()->route('admin.employees.index')
                ->with('success', 'Data pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui pegawai: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui pegawai. Silakan coba lagi.');
        }
    }

    public function destroy(Employee $employee)
    {
        $this->checkEmploymentPermission();

        // Prevent deleting teachers
        if ($employee->employee_type === 'guru') {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Guru tidak dapat dihapus melalui menu ini. Gunakan menu Data Guru.');
        }

        try {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }

            $employee->delete();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Data pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pegawai: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus pegawai. Silakan coba lagi.');
        }
    }

    // ====== DASHBOARD SDM ======

    public function dashboard()
    {
        $user = auth()->user();

        $baseQuery = Employee::where('is_active', true);
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $baseQuery->where('school_id', $user->school_id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'guru' => (clone $baseQuery)->where('employee_type', 'guru')->count(),
            'staff' => (clone $baseQuery)->where('employee_type', '!=', 'guru')->count(),
            'yayasan' => (clone $baseQuery)->yayasanStaff()->count(),
        ];

        // Per school breakdown (Hanya 3 Unit Sekolah Aktif)
        $schoolsQuery = School::schoolsOnly()->orderBy('name')->withCount(['teachers' => fn($q) => $q->whereHas('employee', fn($e) => $e->where('is_active', true))]);
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $schoolsQuery->where('id', $user->school_id);
        }
        $schools = $schoolsQuery->get()->map(function ($school) {
            $school->employee_count = Employee::where('school_id', $school->id)->where('is_active', true)->count();
            return $school;
        });

        // On leave today
        $onLeaveTodayQuery = EmployeeLeave::with('employee.school')->activeOnDate(today());
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $onLeaveTodayQuery->whereHas('employee', fn($q) => $q->where('school_id', $user->school_id));
        }
        $onLeaveToday = $onLeaveTodayQuery->limit(10)->get();

        // Expiring contracts
        $expiringContractsQuery = EmployeeContract::with('employee.school')->expiringSoon(30);
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $expiringContractsQuery->whereHas('employee', fn($q) => $q->where('school_id', $user->school_id));
        }
        $expiringContracts = $expiringContractsQuery->limit(10)->get();

        // Education distribution
        $educationDist = EmployeeEducation::selectRaw('education_level, COUNT(DISTINCT employee_id) as count')
            ->whereIn('employee_id', (clone $baseQuery)->pluck('id'))
            ->groupBy('education_level')
            ->orderByRaw("FIELD(education_level, 'S3','S2','S1','D4','D3','D2','D1','SMA','SMP','SD')")
            ->pluck('count', 'education_level');

        // Gender distribution
        $genderDist = (clone $baseQuery)->selectRaw("gender, COUNT(*) as count")->groupBy('gender')->pluck('count', 'gender');

        // Status distribution
        $statusDist = (clone $baseQuery)->selectRaw("employment_status, COUNT(*) as count")->groupBy('employment_status')->pluck('count', 'employment_status');

        return view('admin.employees.dashboard', compact(
            'stats', 'schools', 'onLeaveToday', 'expiringContracts', 'educationDist', 'genderDist', 'statusDist'
        ));
    }

    // ====== PROFILE LENGKAP ======

    public function profile(Employee $employee)
    {
        $employee->load([
            'school', 'user', 'teacher',
            'positionHistory.position', 'positionHistory.academicYear',
            'educations', 'trainings', 'documents',
            'familyMembers', 'contracts',
            'leaves' => fn($q) => $q->latest()->limit(10),
        ]);

        // Teaching assignments grouped by academic year
        $teachingHistory = collect();
        if ($employee->teacher) {
            $teachingHistory = $employee->teacher->teachingAssignments()
                ->with(['subject', 'classroom.school', 'academicYear'])
                ->orderBy('academic_year_id', 'desc')
                ->get()
                ->groupBy(fn($a) => $a->academicYear?->name ?? 'Unknown');
        }

        // Position history grouped by academic year
        $positionHistory = $employee->positionHistory
            ->sortByDesc('start_date')
            ->groupBy(fn($p) => $p->academicYear?->name ?? 'Tanpa Tahun Ajaran');

        // Attendance summary for current month
        $attendanceSummary = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        $teachingSchools = $employee->getTeachingSchools();

        return view('admin.employees.profile', compact(
            'employee', 'teachingHistory', 'positionHistory',
            'attendanceSummary', 'teachingSchools'
        ));
    }

    // ====== SUB-RESOURCE: EDUCATION ======

    public function storeEducation(Request $request, Employee $employee)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validate([
            'education_level' => 'required|in:SD,SMP,SMA,D1,D2,D3,D4,S1,S2,S3',
            'institution_name' => 'required|string|max:255',
            'major' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer|min:1950|max:' . (now()->year + 5),
            'gpa' => 'nullable|numeric|min:0|max:4',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('employee_educations', 'public');
        }

        $employee->educations()->create($validated);

        return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
    }

    public function destroyEducation(EmployeeEducation $education)
    {
        $this->checkEmploymentPermission();

        if ($education->certificate_file) {
            Storage::disk('public')->delete($education->certificate_file);
        }
        $education->delete();
        return back()->with('success', 'Riwayat pendidikan berhasil dihapus.');
    }

    // ====== SUB-RESOURCE: TRAINING ======

    public function storeTraining(Request $request, Employee $employee)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validate([
            'training_name' => 'required|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'training_type' => 'required|in:diklat,workshop,seminar,sertifikasi,bimtek,lainnya',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'hours' => 'nullable|integer|min:1',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('employee_trainings', 'public');
        }

        $employee->trainings()->create($validated);

        return back()->with('success', 'Riwayat pelatihan berhasil ditambahkan.');
    }

    public function destroyTraining(EmployeeTraining $training)
    {
        $this->checkEmploymentPermission();

        if ($training->certificate_file) {
            Storage::disk('public')->delete($training->certificate_file);
        }
        $training->delete();
        return back()->with('success', 'Riwayat pelatihan berhasil dihapus.');
    }

    // ====== SUB-RESOURCE: DOCUMENT ======

    public function storeDocument(Request $request, Employee $employee)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validate([
            'document_type' => 'required|in:ktp,npwp,kk,sk_pengangkatan,sk_jabatan,ijazah,sertifikat,nuptk,kontrak,lainnya',
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['file_path'] = $request->file('file')->store('employee_documents', 'public');

        $employee->documents()->create($validated);

        return back()->with('success', 'Dokumen berhasil diupload.');
    }

    public function destroyDocument(EmployeeDocument $document)
    {
        $this->checkEmploymentPermission();

        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    // ====== SUB-RESOURCE: FAMILY ======

    public function storeFamily(Request $request, Employee $employee)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validate([
            'relation' => 'required|in:suami,istri,anak,ayah,ibu',
            'full_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:L,P',
            'occupation' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:100',
        ]);

        $employee->familyMembers()->create($validated);

        return back()->with('success', 'Data keluarga berhasil ditambahkan.');
    }

    public function updateFamily(Request $request, EmployeeFamilyMember $member)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validate([
            'relation' => 'required|in:suami,istri,anak,ayah,ibu',
            'full_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:L,P',
            'occupation' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:100',
        ]);

        $member->update($validated);

        return back()->with('success', 'Data keluarga berhasil diperbarui.');
    }

    public function destroyFamily(EmployeeFamilyMember $member)
    {
        $this->checkEmploymentPermission();

        $member->delete();
        return back()->with('success', 'Data keluarga berhasil dihapus.');
    }

    // ====== SUB-RESOURCE: CONTRACT ======

    public function storeContract(Request $request, Employee $employee)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validate([
            'contract_number' => 'required|string|max:255',
            'contract_type' => 'required|in:tetap_yayasan,honorer,kontrak,pns',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'basic_salary' => 'nullable|numeric|min:0',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!auth()->user()->canManageBasicSalary()) {
            unset($validated['basic_salary']);
        }

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('employee_contracts', 'public');
        }

        $validated['is_active'] = true;

        // Deactivate old contracts
        $employee->contracts()->where('is_active', true)->update(['is_active' => false]);

        $employee->contracts()->create($validated);

        return back()->with('success', 'Kontrak kerja berhasil ditambahkan.');
    }

    public function updateContract(Request $request, EmployeeContract $contract)
    {
        $this->checkEmploymentPermission();

        $validated = $request->validate([
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $contract->update($validated);

        return back()->with('success', 'Kontrak kerja berhasil diperbarui.');
    }

    public function updateRfid(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);

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

        return redirect()->back()->with('success', "RFID UID berhasil didaftarkan untuk pegawai {$employee->full_name}.");
    }
}

