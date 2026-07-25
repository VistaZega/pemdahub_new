<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ClassroomController extends Controller
{
    public function show(Classroom $classroom)
    {
        // Tampilkan detail kelas dan daftar siswa yang sudah di-assign
        $classroom->load('school');
        $students = $classroom->students()->with('school')->get();
        return view('admin.classrooms.show', compact('classroom', 'students'));
    }
    /**
     * Show form to assign students to a classroom
     */
    public function assignStudentsForm($id)
    {
        $user = auth()->user();
        $classroom = Classroom::with('academicYear')->findOrFail($id);
        
        $academicYearId = $classroom->academic_year_id;

        // Ambil hanya siswa yang belum punya kelas aktif di tahun ajaran ini, atau siswa yang sudah ada di kelas ini
        $students = Student::when(!$user->isSuperAdmin(), fn($q) => $q->where('school_id', $user->school_id))
            ->where(function($query) use ($academicYearId, $classroom) {
                $query->whereDoesntHave('studentClasses', function($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId)
                      ->where('status', 'aktif');
                })
                ->orWhereHas('studentClasses', function($q) use ($academicYearId, $classroom) {
                    $q->where('academic_year_id', $academicYearId)
                      ->where('classroom_id', $classroom->id)
                      ->where('status', 'aktif');
                });
            })
            ->orderBy('full_name')
            ->get();
            
        $assignedStudentIds = $classroom->students()->pluck('students.id')->toArray();
        return view('admin.classrooms.assign-students', compact('classroom', 'students', 'assignedStudentIds'));
    }

    /**
     * Store assigned students to a classroom
     */
    public function assignStudents($id, Request $request)
    {
        $classroom = Classroom::findOrFail($id);
        $studentIds = $request->input('student_ids', []);
        $academicYearId = $classroom->academic_year_id;

        $syncData = [];
        foreach ($studentIds as $studentId) {
            $syncData[$studentId] = [
                'academic_year_id' => $academicYearId,
                'status' => 'aktif', // default status
            ];
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($classroom, $studentIds, $academicYearId, $syncData) {
            // Hapus assignment di kelas lain pada tahun ajaran yang sama untuk menghindari Unique Constraint Violation
            \App\Models\StudentClass::whereIn('student_id', $studentIds)
                ->where('academic_year_id', $academicYearId)
                ->where('classroom_id', '!=', $classroom->id)
                ->delete();

            $classroom->students()->sync($syncData);
        });

        return redirect()->route('admin.classrooms.index')->with('success', 'Siswa berhasil di-assign ke kelas.');
    }
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Classroom::with(['school', 'programKeahlian', 'homeroomTeacher']);
        
        // Auto-filter by school_id for non-superadmin
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        // Filter by academic_year_id (sticky default to active TP)
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $selectedYearId = $request->filled('academic_year_id') 
            ? $request->input('academic_year_id') 
            : session('classroom_filter_academic_year_id');

        if (!$selectedYearId && !$request->has('academic_year_id')) {
            $selectedYearId = AcademicYear::where('is_active', true)->first()?->id;
        }


        session(['classroom_filter_academic_year_id' => $selectedYearId]);

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }
        
        // Determine selected school
        $selectedSchoolId = $request->filled('school_id') 
            ? $request->input('school_id') 
            : ($user->isSuperAdmin() ? null : $user->school_id);
        
        // Filter by school_id (for superadmin)
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }
        
        // Filter by grade_level
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->input('grade_level'));
        }
        
        // Filter by program_keahlian_id (for SMK)
        if ($request->filled('program_keahlian_id')) {
            $programId = $request->input('program_keahlian_id');
            $query->where(function($q) use ($programId) {
                $q->where('program_keahlian_id', $programId)
                  ->orWhere('is_combined', true)
                  ->orWhere('class_type', 'gabungan');
            });
        }

        // Filter by major_id (for SMA/SMP)
        if ($request->filled('major_id')) {
            $majorId = $request->input('major_id');
            $query->where(function($q) use ($majorId) {
                $q->where('major_id', $majorId)
                  ->orWhere('is_combined', true)
                  ->orWhere('class_type', 'gabungan');
            });
        }
        
        // Filter by class_name
        if ($request->filled('class_name')) {
            $query->where('class_name', $request->input('class_name'));
        }
        
        // Get schools list for filter dropdown
        $schools = $user->isSuperAdmin()
            ? School::where('type', '!=', 'yayasan')->orderBy('name')->get()
            : School::where('id', $user->school_id)->where('type', '!=', 'yayasan')->get();
        
        // Determine school type (SMP, SMA, or SMK)
        $schoolType = null;
        $isSMK = false;
        $programKeahlians = collect();
        
        if ($selectedSchoolId) {
            $selectedSchool = School::find($selectedSchoolId);
            if ($selectedSchool) {
                $schoolType = $selectedSchool->type;
                if ($schoolType === 'SMK') {
                    $isSMK = true;
                    $programKeahlians = $selectedSchool->programKeahlians()
                        ->where('is_active', true)
                        ->orderBy('nama')
                        ->get(['id', 'nama', 'kode']);
                }
            }
        } else if (!$user->isSuperAdmin()) {
            // For non-superadmin, check their school type
            $userSchool = School::find($user->school_id);
            if ($userSchool) {
                $schoolType = $userSchool->type;
                if ($schoolType === 'SMK') {
                    $isSMK = true;
                    $programKeahlians = $userSchool->programKeahlians()
                        ->where('is_active', true)
                        ->orderBy('nama')
                        ->get(['id', 'nama', 'kode']);
                }
            }
        }
        
        // Get available class names for dropdown (based on current filters)
        $availableClassesQuery = Classroom::query();
        if (!$user->isSuperAdmin()) {
            $availableClassesQuery->where('school_id', $user->school_id);
        }
        if ($request->filled('school_id')) {
            $availableClassesQuery->where('school_id', $request->input('school_id'));
        }
        if ($selectedYearId) {
            $availableClassesQuery->where('academic_year_id', $selectedYearId);
        }
        if ($request->filled('grade_level')) {
            $availableClassesQuery->where('grade_level', $request->input('grade_level'));
        }
        if ($request->filled('program_keahlian_id')) {
            $availableClassesQuery->where('program_keahlian_id', $request->input('program_keahlian_id'));
        }
        $availableClasses = $availableClassesQuery->distinct()->orderBy('class_name')->pluck('class_name');
        
        $classrooms = $query->withCount('students')->orderBy('grade_level')->orderBy('class_name')->paginate(20)->withQueryString();
        return view('admin.classrooms.index', compact('classrooms', 'schools', 'availableClasses', 'isSMK', 'programKeahlians', 'schoolType', 'academicYears', 'selectedYearId'));
    }

    public function create()
    {
        $schools = School::where('type', '!=', 'yayasan')->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        // Default to the currently active academic year (if any)
        $defaultAcademicYear = AcademicYear::where('is_active', true)->orderBy('year', 'desc')->first();

        // If there's an initial school selection (old input or user's school), preload majors so the select isn't empty
        $initialSchoolId = old('school_id') ?? (Auth::check() ? Auth::user()->school_id : null);
        $majors = collect();
        $programKeahlians = collect();
        $konsentrasiKeahlians = collect();
        
        if ($initialSchoolId) {
            $school = School::find($initialSchoolId);
            if ($school) {
                $majors = $school->majors()->where('is_active', true)->orderBy('major_name')->get(['id', 'major_name']);
                $programKeahlians = $school->programKeahlians()->where('is_active', true)->orderBy('nama')->get(['id', 'nama', 'kode']);
            }
        }
        
        // Load konsentrasi based on old program_keahlian_id if exists
        if (old('program_keahlian_id')) {
            $konsentrasiKeahlians = \App\Models\KonsentrasiKeahlian::where('program_keahlian_id', old('program_keahlian_id'))
                ->where('is_active', true)
                ->orderBy('nama')
                ->get(['id', 'nama', 'kode']);
        }

        return view('admin.classrooms.create', compact('schools', 'academicYears', 'defaultAcademicYear', 'majors', 'programKeahlians', 'konsentrasiKeahlians'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'major_id' => 'nullable|exists:majors,id',
            'program_keahlian_id' => 'nullable|exists:program_keahlians,id',
            'konsentrasi_keahlian_id' => 'nullable|exists:konsentrasi_keahlians,id',
            'class_type' => 'required|string|in:reguler,industri,exclusive,khusus,gabungan',
            'class_code' => 'required|string|max:20',
            'class_name' => 'required|string|max:100',
            'grade_level' => 'required|integer',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
            'entry_time' => 'nullable|string|regex:/^[0-9]{2}:[0-9]{2}$/',
            'late_tolerance' => 'nullable|integer|min:0',
            'is_combined' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Set defaults
        $data['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;
        $isCombined = $request->has('is_combined') ? (bool)$request->input('is_combined') : ($data['class_type'] === 'gabungan');
        $data['is_combined'] = $isCombined;

        $school = School::find($data['school_id']);
        if ($school) {
            $schoolType = strtoupper($school->type);
            if ($schoolType === 'SMK' && !$isCombined) {
                if (empty($data['program_keahlian_id'])) {
                    return back()->withErrors(['program_keahlian_id' => 'Program Keahlian wajib diisi untuk SMK (kecuali Kelas Gabungan)'])->withInput();
                }
                if (empty($data['konsentrasi_keahlian_id'])) {
                    return back()->withErrors(['konsentrasi_keahlian_id' => 'Konsentrasi Keahlian wajib diisi untuk SMK (kecuali Kelas Gabungan)'])->withInput();
                }
            } elseif ($schoolType === 'SMA' && !$isCombined) {
                if (in_array((int)$data['grade_level'], [11, 12]) && empty($data['major_id'])) {
                    return back()->withErrors(['major_id' => 'Jurusan wajib diisi untuk SMA Kelas XI dan XII (kecuali Kelas Gabungan)'])->withInput();
                }
                // Jika kelas X (10), set major_id ke null
                if ((int)$data['grade_level'] === 10) {
                    $data['major_id'] = null;
                }
            }
        }

        Classroom::create($data);
        return redirect()->route('admin.classrooms.index')->with('success', 'Ruang kelas ditambahkan.');
    }

    public function edit(Classroom $classroom)
    {
        $schools = School::where('type', '!=', 'yayasan')->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        // Preload majors for the classroom's school so the form shows the current options immediately
        $majors = collect();
        $programKeahlians = collect();
        $konsentrasiKeahlians = collect();
        
        if ($classroom->school_id) {
            $school = School::find($classroom->school_id);
            if ($school) {
                $majors = $school->majors()->where('is_active', true)->orderBy('major_name')->get(['id', 'major_name']);
                $programKeahlians = $school->programKeahlians()->where('is_active', true)->orderBy('nama')->get(['id', 'nama', 'kode']);
            }
        }
        
        if ($classroom->program_keahlian_id) {
            $konsentrasiKeahlians = \App\Models\KonsentrasiKeahlian::where('program_keahlian_id', $classroom->program_keahlian_id)
                ->where('is_active', true)
                ->orderBy('nama')
                ->get(['id', 'nama', 'kode']);
        }

        return view('admin.classrooms.edit', compact('classroom', 'schools', 'academicYears', 'majors', 'programKeahlians', 'konsentrasiKeahlians'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'major_id' => 'nullable|exists:majors,id',
            'program_keahlian_id' => 'nullable|exists:program_keahlians,id',
            'konsentrasi_keahlian_id' => 'nullable|exists:konsentrasi_keahlians,id',
            'class_type' => 'required|string|in:reguler,industri,exclusive,khusus,gabungan',
            'class_code' => 'required|string|max:20',
            'class_name' => 'required|string|max:100',
            'grade_level' => 'required|integer',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
            'entry_time' => 'nullable|string|regex:/^[0-9]{2}:[0-9]{2}$/',
            'late_tolerance' => 'nullable|integer|min:0',
            'is_combined' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle checkbox values
        $data['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : false;
        $isCombined = $request->has('is_combined') ? (bool)$request->input('is_combined') : ($data['class_type'] === 'gabungan');
        $data['is_combined'] = $isCombined;

        $school = School::find($data['school_id']);
        if ($school) {
            $schoolType = strtoupper($school->type);
            if ($schoolType === 'SMK' && !$isCombined) {
                if (empty($data['program_keahlian_id'])) {
                    return back()->withErrors(['program_keahlian_id' => 'Program Keahlian wajib diisi untuk SMK (kecuali Kelas Gabungan)'])->withInput();
                }
                if (empty($data['konsentrasi_keahlian_id'])) {
                    return back()->withErrors(['konsentrasi_keahlian_id' => 'Konsentrasi Keahlian wajib diisi untuk SMK (kecuali Kelas Gabungan)'])->withInput();
                }
            } elseif ($schoolType === 'SMA' && !$isCombined) {
                if (in_array((int)$data['grade_level'], [11, 12]) && empty($data['major_id'])) {
                    return back()->withErrors(['major_id' => 'Jurusan wajib diisi untuk SMA Kelas XI dan XII (kecuali Kelas Gabungan)'])->withInput();
                }
                // Jika kelas X (10), set major_id ke null
                if ((int)$data['grade_level'] === 10) {
                    $data['major_id'] = null;
                }
            }
        }

        $classroom->update($data);
        return redirect()->route('admin.classrooms.index')->with('success', 'Ruang kelas diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        try {
            $classroom->delete();
            return redirect()->route('admin.classrooms.index')->with('success', 'Ruang kelas dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.classrooms.index')
                ->with('error', 'Tidak dapat menghapus kelas karena masih memiliki data terkait (siswa, jadwal, dll).');
        }
    }
    
    /**
     * AJAX: Get Program Keahlians by School
     */
    public function getProgramKeahlians(School $school)
    {
        $programKeahlians = $school->programKeahlians()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode']);
            
        return response()->json($programKeahlians);
    }
    
    /**
     * AJAX: Get Konsentrasi Keahlians by Program Keahlian
     */
    public function getKonsentrasiKeahlians(\App\Models\ProgramKeahlian $programKeahlian)
    {
        $konsentrasiKeahlians = \App\Models\KonsentrasiKeahlian::where('program_keahlian_id', $programKeahlian->id)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode']);
            
        return response()->json($konsentrasiKeahlians);
    }
    
    /**
     * Show form to assign homeroom teacher (Quick Assign)
     */
    public function assignHomeroomForm(Classroom $classroom)
    {
        $user = auth()->user();
        
        // Get teachers from the same school
        $teachers = \App\Models\Teacher::where('school_id', $classroom->school_id)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();
        
        // Get current academic year
        $currentAcademicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        
        return view('admin.classrooms.assign-homeroom', compact('classroom', 'teachers', 'currentAcademicYear'));
    }
    
    /**
     * Store homeroom teacher assignment
     */
    public function assignHomeroom(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|in:ganjil,genap,full_year',
            'start_date' => 'nullable|date',
            'sk_number' => 'nullable|string|max:50',
        ]);
        
        $teacher = \App\Models\Teacher::findOrFail($validated['teacher_id']);
        
        // Check if teacher has Employee record
        if (!$teacher->employee) {
            return back()->with('error', 'Guru belum memiliki data Employee. Silakan lengkapi data terlebih dahulu.');
        }
        
        // Get WALIKELAS position for this school unit, fallback to any WALIKELAS position
        $waliKelasPosition = \App\Models\Position::where('position_code', 'LIKE', 'WALIKELAS%')
            ->where('school_id', $classroom->school_id)
            ->first();
        
        if (!$waliKelasPosition) {
            $waliKelasPosition = \App\Models\Position::where('position_code', 'LIKE', 'WALIKELAS%')->first();
        }
        
        if (!$waliKelasPosition) {
            return back()->with('error', 'Posisi Wali Kelas tidak ditemukan dalam sistem.');
        }
        
        // Check if teacher already assigned as homeroom in this academic year & semester
        $existingAssignment = \DB::table('employee_positions')
            ->where('employee_id', $teacher->employee->id)
            ->where('position_id', $waliKelasPosition->id)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('semester', $validated['semester'])
            ->whereNull('end_date')
            ->first();
        
        if ($existingAssignment) {
            return back()->with('error', 'Guru sudah ditugaskan sebagai Wali Kelas di tahun ajaran dan semester yang sama.');
        }
        
        $startDate = now();
        if (!empty($validated['start_date'])) {
            try {
                // If it is already in Y-m-d format
                $startDate = \Carbon\Carbon::parse($validated['start_date']);
            } catch (\Exception $e) {
                try {
                    // Fallback for dd/mm/yyyy format
                    $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['start_date']);
                } catch (\Exception $ex) {
                    $startDate = now();
                }
            }
        }

        \DB::beginTransaction();
        try {
            // Check if there is already a record with the same employee, position, and start date to prevent duplicate key error
            $formattedStartDate = $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate;
            $existingPositionRecord = \DB::table('employee_positions')
                ->where('employee_id', $teacher->employee->id)
                ->where('position_id', $waliKelasPosition->id)
                ->whereDate('start_date', $formattedStartDate)
                ->first();

            if ($existingPositionRecord) {
                // Update the existing record to avoid unique active position constraint violation
                \DB::table('employee_positions')
                    ->where('id', $existingPositionRecord->id)
                    ->update([
                        'academic_year_id' => $validated['academic_year_id'],
                        'semester' => $validated['semester'],
                        'classroom_id' => $classroom->id,
                        'end_date' => null,
                        'sk_number' => $validated['sk_number'] ?? $existingPositionRecord->sk_number,
                        'notes' => "Wali Kelas {$classroom->class_name}",
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert new record
                \DB::table('employee_positions')->insert([
                    'employee_id' => $teacher->employee->id,
                    'position_id' => $waliKelasPosition->id,
                    'academic_year_id' => $validated['academic_year_id'],
                    'semester' => $validated['semester'],
                    'classroom_id' => $classroom->id,
                    'start_date' => $startDate,
                    'end_date' => null,
                    'sk_number' => $validated['sk_number'],
                    'notes' => "Wali Kelas {$classroom->class_name}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Update classroom homeroom_teacher_id
            $classroom->homeroom_teacher_id = $teacher->id;
            $classroom->save();
            
            \DB::commit();
            
            return redirect()
                ->route('admin.classrooms.index')
                ->with('success', "Berhasil menugaskan {$teacher->full_name} sebagai Wali Kelas {$classroom->class_name}");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Gagal menugaskan wali kelas: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
