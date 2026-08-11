<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Repositories\StudentRepository;
use App\Services\StudentService;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentSampleExport;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private StudentRepository $studentRepository,
        private StudentService $studentService
    ) {}
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        // Get active academic year for defaults
        $activeAcademicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $academicYears = \App\Models\AcademicYear::orderBy('year', 'desc')->get();

        // Determine selected academic year - default to active year ONLY if not present in request
        $selectedAcademicYearId = $request->has('academic_year_id')
            ? $request->academic_year_id
            : ($activeAcademicYear?->id ?? null);

        $filters = $request->only(['q', 'school_id', 'status', 'classroom_id']);
        $filters['academic_year_id'] = $selectedAcademicYearId;
        $students = $this->studentRepository->getFilteredPaginated($filters);

        $schools = \App\Models\School::getActiveCached();
        
        // Load classrooms filtered by selected academic year
        $classroomQuery = \App\Models\Classroom::where('is_active', true);
        if ($selectedAcademicYearId) {
            $classroomQuery->where('academic_year_id', $selectedAcademicYearId);
        }
        if (!auth()->user()->isSuperAdmin()) {
            $classroomQuery->where('school_id', auth()->user()->school_id);
        } elseif ($request->filled('school_id')) {
            $classroomQuery->where('school_id', $request->school_id);
        }
        $classrooms = $classroomQuery->orderBy('class_name')->get();

        $isPasswordReset = false;
        if ($request->filled('classroom_id') || $request->filled('school_id')) {
            $sampleStudent = collect($students->items())->first(function ($student) {
                return $student->user !== null;
            });
            
            if ($sampleStudent) {
                $expectedPassword = 'Pembda' . $sampleStudent->nisn;
                if (\Illuminate\Support\Facades\Hash::check($expectedPassword, $sampleStudent->user->password)) {
                    $isPasswordReset = true;
                }
            }
        }

        return view('admin.students.index', compact('students', 'schools', 'classrooms', 'academicYears', 'activeAcademicYear', 'selectedAcademicYearId', 'isPasswordReset'));
    }

    public function create()
    {
        $this->authorize('create', Student::class);

        $schools = \App\Models\School::getActiveCached();
        return view('admin.students.create', compact('schools'));
    }

    public function store(StoreStudentRequest $request)
    {
        $this->authorize('create', Student::class);

        try {
            $this->studentService->createStudentWithUser(
                $request->validated(),
                $request->file('photo')
            );

            return redirect()->route('admin.students.index')
                ->with('success', 'Siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal membuat siswa: ' . $e->getMessage());
            return back()->withErrors(['student' => 'Gagal membuat siswa. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $student = $this->studentRepository->findWithRelations($student->id);
        
        // Calculate Attendance Stats
        $attendanceRepo = app(\App\Repositories\AttendanceRepository::class);
        $attendanceStats = $attendanceRepo->getStatistics($student->id);

        return view('admin.students.show', compact('student', 'attendanceStats'));
    }

    /**
     * Show payment history timeline for a student
     */
    public function paymentHistory(Student $student)
    {
        $this->authorize('view', $student);

        // Get all payments for this student with bill details
        $payments = \App\Models\Payment::where('student_id', $student->id)
            ->with(['bill.paymentType', 'processedBy'])
            ->orderBy('payment_date', 'desc')
            ->paginate(20)->withQueryString();

        // Get all bills for this student
        $bills = \App\Models\StudentBill::where('student_id', $student->id)
            ->with(['paymentType', 'academicYear'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Calculate statistics
        $totalBilled = $bills->sum('amount');
        $totalPaid = $bills->sum('paid_amount');
        $totalOutstanding = $totalBilled - $totalPaid;
        $totalLateFees = $bills->sum('late_fee');

        return view('admin.students.payment-history', compact(
            'student',
            'payments',
            'bills',
            'totalBilled',
            'totalPaid',
            'totalOutstanding',
            'totalLateFees'
        ));
    }

    public function edit(Student $student)
    {
        $this->authorize('update', $student);

        $schools = \App\Models\School::getActiveCached();
        return view('admin.students.edit', compact('student', 'schools'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->authorize('update', $student);

        try {
            $this->studentService->updateStudent(
                $student,
                $request->validated(),
                $request->file('photo'),
                $request->boolean('remove_photo')
            );

            return redirect()->route('admin.students.index')
                ->with('success', 'Siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui siswa: ' . $e->getMessage());
            return back()->withErrors(['student' => 'Gagal memperbarui siswa. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);
        try {
            $this->studentService->deleteStudent($student);
            return redirect()->route('admin.students.index')
                ->with('success', 'Siswa berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus siswa: ' . $e->getMessage());
            return back()->withErrors(['student' => 'Gagal menghapus siswa. Silakan coba lagi.']);
        }
    }

    public function updateRfid(Request $request, Student $student)
    {
        $this->authorize('update', $student);
        
        $request->validate([
            'rfid_uid' => 'required|string|max:50|unique:students,rfid_uid,' . $student->id
        ], [
            'rfid_uid.unique' => 'Kartu RFID ini sudah terdaftar untuk siswa lain.'
        ]);

        $student->update([
            'rfid_uid' => strtoupper(trim($request->rfid_uid))
        ]);

        return redirect()->back()->with('success', "RFID UID berhasil didaftarkan untuk siswa {$student->full_name}.");
    }

    /**
     * Import students from CSV (minimal implementation)
     */
    public function importForm()
    {
        $this->authorize('import', Student::class);

        return view('admin.students.import');
    }

    public function import(Request $request)
    {
        $this->authorize('import', Student::class);
        $request->validate([
            'file' => 'nullable|file|mimes:xlsx,xls',
            'csv' => 'nullable|file|mimes:xlsx,xls,csv,txt',
        ]);

        $file = $request->file('file') ?? $request->file('csv');

        if (!$file) {
            return back()->withErrors(['file' => 'File import wajib diunggah.']);
        }

        try {
            $extension = $file->getClientOriginalExtension();
            if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
                $import = new StudentsImport();
                Excel::import($import, $file);
                $rows = $import->getRows();
            } else {
                $path = $file->getRealPath();
                $handle = fopen($path, 'r');
                $header = fgetcsv($handle);

                $rows = [];
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = array_combine($header, $row);
                }
                fclose($handle);
            }

            $result = $this->studentService->importStudents($rows);

            if ($result['failed'] > 0) {
                $errorMsg = "Import selesai dengan " . $result['failed'] . " error. ";
                $errorMsg .= implode('; ', array_slice($result['errors'], 0, 5));

                return redirect()->route('admin.students.index')
                    ->with('warning', $errorMsg)
                    ->with('success', "{$result['imported']} siswa berhasil diimpor.");
            }

            return redirect()->route('admin.students.index')
                ->with('success', "{$result['imported']} siswa berhasil diimpor.");
        } catch (\Exception $e) {
            Log::error('Gagal mengimpor siswa: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage()]);
        }
    }

    /**
     * Download a sample Excel template for imports
     */
    public function downloadSampleExcel()
    {
        return Excel::download(new StudentSampleExport, 'sample_students.xlsx');
    }

    /**
     * Print login accounts for students in a specific classroom or school
     */
    public function printAccounts(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $classroomId = $request->get('classroom_id');
        $schoolId = $request->get('school_id');
        
        if (!$classroomId && !$schoolId) {
            return back()->with('error', 'Pilih kelas atau unit sekolah terlebih dahulu untuk mencetak daftar akun.');
        }

        $query = Student::with('user')->orderBy('full_name');
        
        if ($classroomId) {
            $query->whereHas('studentClasses', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
            $title = "Daftar Akun Siswa - Kelas " . \App\Models\Classroom::find($classroomId)?->class_name;
        } else {
            $query->where('school_id', $schoolId);
            $title = "Daftar Akun Siswa - Unit " . \App\Models\School::find($schoolId)?->name;
        }
        
        $students = $query->get();

        return view('admin.students.print_accounts', compact('students', 'title'));
    }

    /**
     * Export login accounts to Excel
     */
    public function exportAccounts(Request $request)
    {
        $this->authorize('viewAny', Student::class);
        $classroomId = $request->get('classroom_id');
        $schoolId = $request->get('school_id');
        
        if (!$classroomId && !$schoolId) {
            return back()->with('error', 'Pilih kelas atau unit sekolah terlebih dahulu untuk export.');
        }
        
        return Excel::download(new \App\Exports\StudentAccountsExport($classroomId, $schoolId), 'akun_siswa.xlsx');
    }

    /**
     * Reset passwords for students in a specific classroom or school to a standard pattern
     */
    public function resetPasswords(Request $request)
    {
        $this->authorize('create', Student::class);

        $classroomId = $request->get('classroom_id');
        $schoolId = $request->get('school_id');
        
        if (!$classroomId && !$schoolId) {
            return back()->with('error', 'Pilih kelas atau unit sekolah terlebih dahulu untuk mereset password.');
        }

        $query = Student::with('user');
        if ($classroomId) {
            $query->whereHas('studentClasses', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        } else {
            $query->where('school_id', $schoolId);
        }
        
        $students = $query->get();

        $count = 0;
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($students as $student) {
                if ($student->user) {
                    // Pola: Pembda + NISN
                    $newPassword = 'Pembda' . $student->nisn;
                    
                    // Gunakan cost rounds=4 agar proses hashing jauh lebih cepat (mencegah timeout di shared hosting)
                    // Password ini sifatnya sementara dan idealnya diwajibkan ganti saat login
                    $student->user->update([
                        'password' => \Illuminate\Support\Facades\Hash::make($newPassword, ['rounds' => 4])
                    ]);
                    $count++;
                }
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat mereset password: ' . $e->getMessage());
        }

        return back()->with('success', "Berhasil mereset password {$count} siswa menjadi pola standar (Pembda+NISN).");
    }

    /**
     * Halaman Registrasi RFID Massal (via Arduino Nano Scanner)
     */
    public function rfidBulk(Request $request)
    {
        $schools = \App\Models\School::where('is_active', true)->orderBy('name')->get();
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $academicYears = \App\Models\AcademicYear::orderBy('year', 'desc')->get();

        $schoolId = $request->get('school_id');
        $yearId   = $request->get('academic_year_id', $activeYear?->id);

        $students = collect();
        if ($schoolId && $yearId) {
            $students = Student::where('school_id', $schoolId)
                ->where('status', 'aktif')
                ->with(['classroom' => fn($q) => $q->where('academic_year_id', $yearId)])
                ->orderBy('full_name')
                ->get();
        }

        return view('admin.students.rfid-bulk', compact('schools', 'academicYears', 'students', 'schoolId', 'yearId'));
    }

    /**
     * Simpan UID RFID ke siswa via AJAX (Registrasi Massal)
     */
    public function rfidBulkAssign(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'rfid_uid'   => 'required|string|min:4|max:20',
        ]);

        $uid = strtoupper(trim($request->rfid_uid));

        // --- NORMALISASI FORMAT UID (sama seperti di AttendanceController) ---
        // Jika UID berupa angka desimal murni (hardware lama, misal: 50012561)
        // konversi ke format Hex (misal: 02FB2191) agar konsisten dengan proses absensi.
        if (preg_match('/^\d+$/', $uid) && strlen($uid) >= 6 && strlen($uid) <= 12) {
            $num = (int)$uid;
            if ($num > 0 && $num <= 4294967295) {
                $hexUid = strtoupper(dechex($num));
                $uid = str_pad($hexUid, 8, '0', STR_PAD_LEFT);
            }
        }

        // Cek apakah UID sudah dipakai entitas lain
        $existingStudent = Student::where('rfid_uid', $uid)
            ->where('id', '!=', $request->student_id)
            ->first();

        if ($existingStudent) {
            return response()->json([
                'success' => false,
                'message' => "UID {$uid} sudah digunakan oleh siswa: {$existingStudent->full_name}",
            ], 422);
        }

        $existingEmployee = \App\Models\Employee::where('rfid_uid', $uid)->first();
        if ($existingEmployee) {
            $type = $existingEmployee->isTeacher() ? 'Guru' : 'Pegawai';
            return response()->json([
                'success' => false,
                'message' => "UID {$uid} sudah digunakan oleh {$type}: {$existingEmployee->full_name}",
            ], 422);
        }

        $existingTefa = \App\Models\TefaEmployee::where('rfid_uid', $uid)->first();
        if ($existingTefa) {
            return response()->json([
                'success' => false,
                'message' => "UID {$uid} sudah digunakan oleh Karyawan TEFA: {$existingTefa->name}",
            ], 422);
        }

        $student = Student::findOrFail($request->student_id);
        $student->update(['rfid_uid' => $uid]);

        return response()->json([
            'success'      => true,
            'message'      => "UID berhasil disimpan untuk {$student->full_name}",
            'student_name' => $student->full_name,
            'rfid_uid'     => $uid,
        ]);
    }
}

