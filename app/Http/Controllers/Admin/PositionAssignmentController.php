<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Position;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get academic years for filter
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentYear = AcademicYear::where('is_active', 1)->first();
        
        // Default to current academic year
        $selectedYearId = $request->filled('academic_year_id') 
            ? $request->academic_year_id 
            : ($currentYear ? $currentYear->id : null);
        
        // Semester is fixed to full_year for position assignments
        $semester = 'full_year';
        
        // Base query - all active employees (guru and staff/pegawai)
        $query = Employee::query()
            ->select('employees.*')
            ->addSelect(['min_position_level' => function ($q) {
                $q->selectRaw('COALESCE(MIN(positions.position_level), 999)')
                    ->from('employee_positions')
                    ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                    ->whereColumn('employee_positions.employee_id', 'employees.id')
                    ->whereNull('employee_positions.end_date');
            }])
            ->with(['school', 'employeePositions' => function ($q) use ($selectedYearId) {
                $q->where('academic_year_id', $selectedYearId);
                $q->whereNull('end_date'); // Only show active positions
                $q->with('position');
                $q->orderBy('is_primary', 'desc')->orderBy('start_date', 'desc')->orderBy('id', 'desc');
            }])
            ->orderBy('min_position_level', 'asc')
            ->orderBy('employee_code', 'asc')
            ->orderBy('full_name', 'asc');

        // Filter by employee category if specified
        if ($request->filled('employee_type') && $request->employee_type !== 'all' && $request->employee_type !== '') {
            if ($request->employee_type === 'guru') {
                $query->where('employee_type', 'guru');
            } elseif ($request->employee_type === 'staff') {
                $query->where('employee_type', '!=', 'guru');
            } else {
                $query->where('employee_type', $request->employee_type);
            }
        }
        
        // Auto-filter by school for non-superadmin
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }
        
        // Filter by school (for superadmin)
        if ($request->filled('school_id') && $user->isSuperAdmin()) {
            $query->where('school_id', $request->school_id);
        }
        
        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
        
        $employees = $query->where('is_active', 1)->paginate(15)->withQueryString();
        
        // Schools dropdown
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();
        
        return view('admin.assignments.positions.index', compact(
            'employees', 
            'schools', 
            'academicYears', 
            'selectedYearId',
            'semester'
        ));
    }
    
    public function create(Request $request)
    {
        $user = auth()->user();
        
        // Get academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentYear = AcademicYear::where('is_active', 1)->first();
        
        // Get employee_id from query string or session
        $employeeId = $request->employee_id;
        
        $selectedEmployee = null;
        if ($employeeId) {
            $selectedEmployee = Employee::find($employeeId);
            // Non-superadmin cannot assign positions for employees from other schools
            if ($selectedEmployee && !$user->isSuperAdmin() && $selectedEmployee->school_id !== $user->school_id) {
                $selectedEmployee = null;
            }
        }
        
        // Get all active employees (teachers and staff) based on user role
        $employeeQuery = Employee::where('is_active', 1)->with('school');
        if (!$user->isSuperAdmin()) {
            // Admin sekolah bisa memilih pegawai unitnya sendiri ATAU pegawai Yayasan (school_id = 4)
            $employeeQuery->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhere('school_id', 4)
                  ->orWhere('employment_status', 'yayasan');
            });
        }
        $teachers = $employeeQuery->orderBy('employee_type', 'asc')->orderBy('full_name', 'asc')->get();
        
        // Get positions grouped by category
        $positionsQuery = Position::where('is_active', 1)->with('school');

        if (!$user->isSuperAdmin()) {
            $positionsQuery->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhereNull('school_id');
            });
        }

        $positionsQuery->orderBy('position_category')
            ->orderBy('position_name');
        
        $positions = $positionsQuery->get()->groupBy('position_category');
        
        // If employee selected, get their current positions for the academic year
        $currentPositions = [];
        $currentAssignment = null;
        if ($selectedEmployee && $currentYear) {
            $assignments = $selectedEmployee->employeePositions()
                ->where('academic_year_id', $currentYear->id)
                ->whereNull('end_date')
                ->get();
            if ($assignments->isNotEmpty()) {
                $currentAssignment = $assignments->first();
                $currentPositions = $assignments->pluck('position_id')->toArray();
            }
        }
        
        // Get classrooms for wali kelas assignment
        $classrooms = collect([]);
        $schoolIdForClassrooms = $selectedEmployee ? $selectedEmployee->school_id : (!$user->isSuperAdmin() ? $user->school_id : null);
        
        if ($schoolIdForClassrooms) {
            $classroomsQuery = Classroom::where('school_id', $schoolIdForClassrooms)->where('is_active', 1);
            if ($currentYear) {
                $classroomsQuery->where('academic_year_id', $currentYear->id);
            }
            $classrooms = $classroomsQuery->orderBy('grade_level')
                ->orderBy('class_name')
                ->get();
        } elseif ($user->isSuperAdmin()) {
            $classroomsQuery = Classroom::where('is_active', 1);
            if ($currentYear) {
                $classroomsQuery->where('academic_year_id', $currentYear->id);
            }
            $classrooms = $classroomsQuery->orderBy('grade_level')
                ->orderBy('class_name')
                ->get();
        }
        
        $approvedContractPositionIds = [];
        $isSMK = false;
        
        if ($selectedEmployee && $selectedEmployee->school) {
            $school = $selectedEmployee->school;
            if (strtoupper($school->type) === 'SMK' || str_contains(strtolower($school->name), 'smk') || str_contains(strtolower($school->name), 'kejuruan')) {
                $isSMK = true;
                if ($currentYear) {
                    $approvedContractPositionIds = \App\Models\PerformanceContract::where('employee_id', $selectedEmployee->id)
                        ->where('academic_year_id', $currentYear->id)
                        ->where('contract_type', \App\Models\PerformanceContract::TYPE_JABATAN)
                        ->where('status', \App\Models\PerformanceContract::STATUS_APPROVED_BY_YAYASAN)
                        ->pluck('position_id')
                        ->toArray();
                }
            }
        }
        
        $currentClassroom = null;
        if ($selectedEmployee && $currentYear) {
            $pivotClassroomId = $selectedEmployee->employeePositions()
                ->where('academic_year_id', $currentYear->id)
                ->whereNull('end_date')
                ->whereNotNull('classroom_id')
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->value('classroom_id');

            if ($pivotClassroomId) {
                $currentClassroom = Classroom::find($pivotClassroomId);
            }
            if (!$currentClassroom && $selectedEmployee->teacher) {
                $currentClassroom = Classroom::where('homeroom_teacher_id', $selectedEmployee->teacher->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }
        
        return view('admin.assignments.positions.create', compact(
            'teachers',
            'positions',
            'academicYears',
            'currentYear',
            'selectedEmployee',
            'currentPositions',
            'currentAssignment',
            'classrooms',
            'currentClassroom',
            'isSMK',
            'approvedContractPositionIds'
        ));
    }
    
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'positions' => 'required|array|min:1',
            'positions.*' => 'exists:positions,id',
            'primary_position_id' => 'required|in:' . implode(',', $request->positions ?? []),
            'position_start_date' => 'required|date',
            'sk_number' => 'nullable|string|max:100',
            'sk_date' => 'nullable|date',
            'classroom_id' => 'nullable|exists:classrooms,id', // For wali kelas
        ]);
        
        // Check authorization
        $employee = Employee::findOrFail($validated['employee_id']);
        if (!$user->isSuperAdmin() && $employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }

        // --- SISTEM GEMBOK PERJANJIAN KINERJA JABATAN (Khusus SMK) ---
        $school = \App\Models\School::find($employee->school_id);
        if ($school && (strtoupper($school->type) === 'SMK' || str_contains(strtolower($school->name), 'smk') || str_contains(strtolower($school->name), 'kejuruan'))) {
            foreach ($validated['positions'] as $posId) {
                $position = \App\Models\Position::find($posId);
                if (!$position) continue;
                
                $posName = strtolower($position->position_name);
                $posCode = strtolower($position->position_code ?? '');
                
                // Pengecualian: Kepsek, PKS, Wali Kelas, KTU, Bendahara, dan Jabatan Support
                $isExempt = str_contains($posName, 'kepala sekolah') || 
                            str_contains($posName, 'wakil kepala sekolah') || 
                            str_contains($posName, 'pks ') || 
                            $posName == 'pks' ||
                            str_contains($posName, 'wali kelas') ||
                            str_contains($posName, 'kepala tata usaha') ||
                            str_contains($posName, 'ktu') ||
                            $posCode == 'ktu' ||
                            str_contains($posName, 'bendahara') ||
                            str_contains($posCode, 'bendahara') ||
                            $position->position_category === 'support';
                            
                if (!$isExempt) {
                    // Wajib punya Perjanjian Kinerja Jabatan (Tipe 4) untuk posisi ini yang di-ACC Yayasan
                    $hasContract = \App\Models\PerformanceContract::where('employee_id', $employee->id)
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('contract_type', \App\Models\PerformanceContract::TYPE_JABATAN)
                        ->where('position_id', $posId)
                        ->where('status', \App\Models\PerformanceContract::STATUS_APPROVED_BY_YAYASAN)
                        ->exists();

                    if (!$hasContract) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Akses Ditolak! Jabatan ' . $position->position_name . ' mewajibkan Instrumen Perjanjian Kinerja (#4). Guru bersangkutan belum memiliki kontrak yang disetujui Yayasan.');
                    }
                }
            }
        }
        // --- END GEMBOK ---
        
        DB::beginTransaction();
        try {
            $this->syncEmployeePositions($employee, $validated, $request);
            
            DB::commit();
            
            return redirect()
                ->route('admin.assignments.positions.index', [
                    'academic_year_id' => $validated['academic_year_id'],
                ])
                ->with('success', 'Penugasan jabatan berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan penugasan jabatan: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan penugasan: ' . $e->getMessage());
        }
    }
    
    public function edit($employeeId)
    {
        $user = auth()->user();
        $employee = Employee::with(['school', 'teacher'])->findOrFail($employeeId);
        
        // Check authorization
        if (!$user->isSuperAdmin() && $employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }
        
        // Get academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentYear = AcademicYear::where('is_active', 1)->first();
        
        // Get positions grouped by category - filtered by school context
        $positionsQuery = Position::where('is_active', 1);

        if (!$user->isSuperAdmin()) {
            // Admin Sekolah strictly hanya bisa melihat jabatan unit sekolahnya
            $positionsQuery->where('school_id', $user->school_id);
        } else {
            // Superadmin difilter berdasarkan sekolah karyawan
            $positionsQuery->where(function($q) use ($employee) {
                if ($employee->school_id) {
                    $q->where('school_id', $employee->school_id)
                      ->orWhereNull('school_id'); // Include global positions
                } else {
                    $q->whereNull('school_id'); // Only global if no school
                }
            });
        }

        $positions = $positionsQuery->orderBy('position_category')
            ->orderBy('position_name')
            ->get()
            ->groupBy('position_category');
        
        // Get current positions for current academic year
        $currentPositions = [];
        $currentAssignment = null;
        if ($currentYear) {
            $assignments = $employee->employeePositions()
                ->where('academic_year_id', $currentYear->id)
                ->whereNull('end_date') // Only show active positions
                ->with('position')
                ->get();
            
            if ($assignments->isNotEmpty()) {
                $currentAssignment = $assignments->first();
                $currentPositions = $assignments->pluck('position_id')->toArray();
                $currentPklHours = $assignments->pluck('pkl_supervisor_hours', 'position_id')->toArray();
            }
        }
        
        // Get classrooms for wali kelas
        $classroomsQuery = Classroom::where('school_id', $employee->school_id)
            ->where('is_active', 1);
        if ($currentYear) {
            $classroomsQuery->where('academic_year_id', $currentYear->id);
        }
        $classrooms = $classroomsQuery->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();
        
        // Get current classroom if wali kelas (check pivot table first, fallback to classrooms table)
        $currentClassroom = null;
        $pivotClassroomId = $employee->employeePositions()
            ->where('academic_year_id', $currentYear->id ?? 0)
            ->whereNull('end_date')
            ->whereNotNull('classroom_id')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->value('classroom_id');

        if ($pivotClassroomId) {
            $currentClassroom = Classroom::find($pivotClassroomId);
        }
        if (!$currentClassroom && $employee->teacher && $currentYear) {
            $currentClassroom = Classroom::where('homeroom_teacher_id', $employee->teacher->id)
                ->where('academic_year_id', $currentYear->id)
                ->orderBy('id', 'desc')
                ->first();
        }
        
        $approvedContractPositionIds = [];
        $isSMK = false;
        
        if ($employee && $employee->school) {
            $school = $employee->school;
            if (strtoupper($school->type) === 'SMK' || str_contains(strtolower($school->name), 'smk') || str_contains(strtolower($school->name), 'kejuruan')) {
                $isSMK = true;
                if ($currentYear) {
                    $approvedContractPositionIds = \App\Models\PerformanceContract::where('employee_id', $employee->id)
                        ->where('academic_year_id', $currentYear->id)
                        ->where('contract_type', \App\Models\PerformanceContract::TYPE_JABATAN)
                        ->where('status', \App\Models\PerformanceContract::STATUS_APPROVED_BY_YAYASAN)
                        ->pluck('position_id')
                        ->toArray();
                }
            }
        }
        
        return view('admin.assignments.positions.edit', compact(
            'employee',
            'positions',
            'academicYears',
            'currentYear',
            'currentPositions',
            'currentPklHours',
            'currentAssignment',
            'classrooms',
            'currentClassroom',
            'isSMK',
            'approvedContractPositionIds'
        ));
    }
    
    public function update(Request $request, $employeeId)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'positions' => 'required|array|min:1',
            'positions.*' => 'exists:positions,id',
            'primary_position_id' => 'required|in:' . implode(',', $request->positions ?? []),
            'position_start_date' => 'required|date',
            'sk_number' => 'nullable|string|max:100',
            'sk_date' => 'nullable|date',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);
        
        // Check authorization
        $employee = Employee::findOrFail($validated['employee_id']);
        if (!$user->isSuperAdmin() && $employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }

        // --- SISTEM GEMBOK PERJANJIAN KINERJA JABATAN (Khusus SMK) ---
        $school = \App\Models\School::find($employee->school_id);
        if ($school && (strtoupper($school->type) === 'SMK' || str_contains(strtolower($school->name), 'smk') || str_contains(strtolower($school->name), 'kejuruan'))) {
            foreach ($validated['positions'] as $posId) {
                $position = \App\Models\Position::find($posId);
                if (!$position) continue;
                
                $posName = strtolower($position->position_name);
                $posCode = strtolower($position->position_code ?? '');
                
                // Pengecualian: Kepsek, PKS, Wali Kelas, KTU, Bendahara, dan Jabatan Support
                $isExempt = str_contains($posName, 'kepala sekolah') || 
                            str_contains($posName, 'wakil kepala sekolah') || 
                            str_contains($posName, 'pks ') || 
                            $posName == 'pks' ||
                            str_contains($posName, 'wali kelas') ||
                            str_contains($posName, 'kepala tata usaha') ||
                            str_contains($posName, 'ktu') ||
                            $posCode == 'ktu' ||
                            str_contains($posName, 'bendahara') ||
                            str_contains($posCode, 'bendahara') ||
                            $position->position_category === 'support';
                            
                if (!$isExempt) {
                    // Wajib punya Perjanjian Kinerja Jabatan (Tipe 4) untuk posisi ini yang di-ACC Yayasan
                    $hasContract = \App\Models\PerformanceContract::where('employee_id', $employee->id)
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('contract_type', \App\Models\PerformanceContract::TYPE_JABATAN)
                        ->where('position_id', $posId)
                        ->where('status', \App\Models\PerformanceContract::STATUS_APPROVED_BY_YAYASAN)
                        ->exists();

                    if (!$hasContract) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Akses Ditolak! Jabatan ' . $position->position_name . ' mewajibkan Instrumen Perjanjian Kinerja (#4). Guru bersangkutan belum memiliki kontrak yang disetujui Yayasan.');
                    }
                }
            }
        }
        // --- END GEMBOK ---
        
        DB::beginTransaction();
        try {
            $this->syncEmployeePositions($employee, $validated, $request);
            
            DB::commit();
            
            return redirect()
                ->route('admin.assignments.positions.index', [
                    'academic_year_id' => $validated['academic_year_id'],
                ])
                ->with('success', 'Penugasan jabatan berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui penugasan jabatan: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui penugasan: ' . $e->getMessage());
        }
    }
    
    public function destroy($employeeId, Request $request)
    {
        $user = auth()->user();
        $employee = Employee::findOrFail($employeeId);
        
        // Check authorization
        if (!$user->isSuperAdmin() && $employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        // Close all positions for this academic year
        $employee->employeePositions()
            ->where('academic_year_id', $validated['academic_year_id'])
            ->whereNull('end_date')
            ->update(['end_date' => now()]);
            
        // Clear from classrooms table if teacher assigned
        if ($employee->teacher) {
            Classroom::where('homeroom_teacher_id', $employee->teacher->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->update(['homeroom_teacher_id' => null]);
        }
        
        return back()->with('success', 'Semua penugasan jabatan berhasil dihapus.');
    }
    
    public function destroySinglePosition($employeeId, $positionId, Request $request)
    {
        $user = auth()->user();
        $employee = Employee::findOrFail($employeeId);
        
        // Check authorization
        if (!$user->isSuperAdmin() && $employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        $position = Position::find($positionId);
        $isWaliKelas = $position && (
            stripos($position->position_name, 'wali kelas') !== false ||
            stripos($position->position_code, 'WAKEL') !== false ||
            stripos($position->position_code, 'WALIKELAS') !== false ||
            stripos($position->position_code, 'WK') !== false
        );
        
        // Close specific position for this academic year
        $deleted = $employee->employeePositions()
            ->where('position_id', $positionId)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->whereNull('end_date')
            ->update(['end_date' => now()]);
            
        if ($deleted && $isWaliKelas && $employee->teacher) {
            // Check if teacher still has any active Wali Kelas position left in this academic year
            $remainingWaliKelas = DB::table('employee_positions as ep')
                ->join('positions as p', 'ep.position_id', '=', 'p.id')
                ->where('ep.employee_id', $employee->id)
                ->where('ep.academic_year_id', $validated['academic_year_id'])
                ->whereNull('ep.end_date')
                ->where(function ($q) {
                    $q->where('p.position_name', 'like', '%wali kelas%')
                      ->orWhere('p.position_code', 'like', '%WAKEL%')
                      ->orWhere('p.position_code', 'like', '%WALIKELAS%')
                      ->orWhere('p.position_code', 'like', '%WK%');
                })
                ->exists();
                
            if (!$remainingWaliKelas) {
                Classroom::where('homeroom_teacher_id', $employee->teacher->id)
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->update(['homeroom_teacher_id' => null]);
            }
        }
        
        if ($deleted) {
            return back()->with('success', 'Jabatan berhasil dihapus.');
        }
        
        return back()->with('error', 'Jabatan tidak ditemukan atau sudah dihapus.');
    }
    
    private function syncEmployeePositions($employee, $validated, $request)
    {
        $semester = 'full_year';
        $newPositionIds = $validated['positions'];

        // 1. Close all active positions for this academic year that are NOT in the new selection
        $employee->employeePositions()
            ->where('academic_year_id', $validated['academic_year_id'])
            ->whereNotIn('position_id', $newPositionIds)
            ->whereNull('end_date')
            ->update(['end_date' => now(), 'updated_at' => now()]);

        // Check if ANY selected position is Wali Kelas (checking both name and code)
        $hasWaliKelas = false;
        foreach ($newPositionIds as $posId) {
            $pos = Position::find($posId);
            if ($pos && (
                stripos($pos->position_name, 'wali kelas') !== false ||
                stripos($pos->position_code, 'WAKEL') !== false ||
                stripos($pos->position_code, 'WALIKELAS') !== false ||
                stripos($pos->position_code, 'WK') !== false
            )) {
                $hasWaliKelas = true;
                break;
            }
        }

        // If Wali Kelas is NOT kept, clear this teacher from any classrooms immediately
        if (!$hasWaliKelas && $employee->teacher) {
            Classroom::where('homeroom_teacher_id', $employee->teacher->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->update(['homeroom_teacher_id' => null]);
        }

        // 2. Loop through each selected position and either update existing or attach
        foreach ($newPositionIds as $positionId) {
            $position = Position::find($positionId);
            $isWaliKelas = $position && (
                stripos($position->position_name, 'wali kelas') !== false ||
                stripos($position->position_code, 'WAKEL') !== false || 
                stripos($position->position_code, 'WALIKELAS') !== false ||
                stripos($position->position_code, 'WK') !== false
            );
            $classroomId = ($isWaliKelas && $request->filled('classroom_id')) ? $validated['classroom_id'] : null;
            $isPrimary = ($positionId == $validated['primary_position_id']);
            $pklHours = $request->input("pkl_supervisor_hours.{$positionId}", 0);

            $activeRecordId = null;

            // Priority A: Check if a record exists for this EXACT (position_id, start_date) for this employee
            $existingExactDate = $employee->employeePositions()
                ->where('position_id', $positionId)
                ->where('start_date', $validated['position_start_date'])
                ->first();

            if ($existingExactDate) {
                $existingExactDate->update([
                    'academic_year_id' => $validated['academic_year_id'],
                    'semester' => $semester,
                    'end_date' => null, // Reactivate
                    'sk_number' => $validated['sk_number'],
                    'sk_date' => $validated['sk_date'],
                    'is_primary' => $isPrimary,
                    'classroom_id' => $classroomId,
                    'pkl_supervisor_hours' => $pklHours,
                    'updated_at' => now(),
                ]);
                $activeRecordId = $existingExactDate->id;
            } else {
                // Priority B: Check if there is an existing record for this academic_year_id & position_id
                $existingYearRecord = $employee->employeePositions()
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('position_id', $positionId)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($existingYearRecord) {
                    $existingYearRecord->update([
                        'start_date' => $validated['position_start_date'],
                        'end_date' => null, // Reactivate
                        'sk_number' => $validated['sk_number'],
                        'sk_date' => $validated['sk_date'],
                        'is_primary' => $isPrimary,
                        'classroom_id' => $classroomId,
                        'pkl_supervisor_hours' => $pklHours,
                        'updated_at' => now(),
                    ]);
                    $activeRecordId = $existingYearRecord->id;
                } else {
                    // Priority C: Check if there is any active position_id without end_date that we can reuse
                    $existingActive = $employee->employeePositions()
                        ->where('position_id', $positionId)
                        ->whereNull('end_date')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($existingActive) {
                        $existingActive->update([
                            'academic_year_id' => $validated['academic_year_id'],
                            'semester' => $semester,
                            'start_date' => $validated['position_start_date'],
                            'sk_number' => $validated['sk_number'],
                            'sk_date' => $validated['sk_date'],
                            'is_primary' => $isPrimary,
                            'classroom_id' => $classroomId,
                            'pkl_supervisor_hours' => $pklHours,
                            'updated_at' => now(),
                        ]);
                        $activeRecordId = $existingActive->id;
                    } else {
                        // Attach brand new record
                        $employee->positions()->attach($positionId, [
                            'academic_year_id' => $validated['academic_year_id'],
                            'semester' => $semester,
                            'start_date' => $validated['position_start_date'],
                            'end_date' => null,
                            'sk_number' => $validated['sk_number'],
                            'sk_date' => $validated['sk_date'],
                            'is_primary' => $isPrimary,
                            'classroom_id' => $classroomId,
                            'pkl_supervisor_hours' => $pklHours,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        // Get the ID of newly attached record
                        $activeRecordId = $employee->employeePositions()
                            ->where('position_id', $positionId)
                            ->where('start_date', $validated['position_start_date'])
                            ->whereNull('end_date')
                            ->orderBy('id', 'desc')
                            ->value('id');
                    }
                }
            }

            // CRITICAL CLEANUP: Close any OTHER active records for this position_id that are NOT the active one!
            // This prevents old/duplicate active rows (e.g. old X TSM 1 row) from lingering and overriding queries.
            if ($activeRecordId) {
                $employee->employeePositions()
                    ->where('position_id', $positionId)
                    ->whereNull('end_date')
                    ->where('id', '!=', $activeRecordId)
                    ->update(['end_date' => now(), 'updated_at' => now()]);
            }
        }

        // 3. Check if wali kelas position is assigned, and update classroom's homeroom_teacher_id
        if ($hasWaliKelas && $request->filled('classroom_id')) {
            $classroom = Classroom::find($validated['classroom_id']);
            if ($classroom && $classroom->school_id == $employee->school_id) {
                $teacherId = $employee->teacher->id ?? null;
                if (!$teacherId && ($employee->employee_type === 'guru' || $hasWaliKelas)) {
                    $teacher = \App\Models\Teacher::firstOrCreate(
                        ['employee_id' => $employee->id],
                        [
                            'school_id' => $employee->school_id,
                            'teacher_code' => $employee->employee_code ?? 'TCH-' . $employee->id,
                            'full_name' => $employee->full_name,
                            'is_active' => 1,
                        ]
                    );
                    $teacherId = $teacher->id;
                }
                if ($teacherId) {
                    // CRITICAL CLEANUP: Clear homeroom_teacher_id from ANY other classroom in THIS ACADEMIC YEAR that had this teacher!
                    Classroom::where('homeroom_teacher_id', $teacherId)
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('id', '!=', $validated['classroom_id'])
                        ->update(['homeroom_teacher_id' => null]);

                    // Assign teacher to the chosen classroom
                    $classroom->update(['homeroom_teacher_id' => $teacherId]);
                }
            }
        }
    }
}
