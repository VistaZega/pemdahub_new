<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Services\StudentDnaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TeacherStudentDnaController extends Controller
{
    use HasMultiSchool;

    public function __construct(
        protected StudentDnaService $dnaService
    ) {}

    /**
     * Display list of students in teacher's assigned classrooms or homeroom.
     * Kewenangan:
     * - Guru Pengampu: HANYA rombel & siswa yang diajar (berdasarkan TeachingAssignment / Schedule) di TP aktif.
     * - Wali Kelas: HANYA rombel perwalian & kelas ajar di TP aktif.
     * - Kepala Sekolah & Guru BK: Seluruh rombel di unit sekolah aktif.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeRole = session('active_role', $user->role);
        $activeYear = AcademicYear::where('is_active', true)->first();

        // 1. Dapatkan record Teacher yang sesuai dengan unit sekolah aktif
        $effectiveSchoolId = $this->getEffectiveSchoolId();
        $teacher = Teacher::where('user_id', $user->id)
            ->when($effectiveSchoolId, fn($q) => $q->where('school_id', $effectiveSchoolId))
            ->first() 
            ?? Teacher::where('user_id', $user->id)->first();

        // 2. Cek apakah role aktif adalah Kepala Sekolah atau Guru BK
        $isPrincipalOrBk = in_array($activeRole, ['kepala_sekolah', 'guru_bk', 'superadmin', 'admin_sekolah'])
            || ($teacher && method_exists($teacher, 'isPrincipal') && $teacher->isPrincipal());

        // 3. Resolve Daftar Rombel / Kelas Sesuai Penugasan
        if ($isPrincipalOrBk) {
            $classrooms = Classroom::with('school')
                ->where('is_active', true)
                ->when($effectiveSchoolId, fn($q) => $q->where('school_id', $effectiveSchoolId))
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id)->orWhereNull('academic_year_id'))
                ->orderBy('class_name')
                ->get();
        } else {
            // Guru Pengampu & Wali Kelas: Ambil ID kelas dari Penugasan Mengajar, Jadwal, & Wali Kelas
            $assignedClassroomIds = collect();

            if ($teacher) {
                $tIds = Auth::user() ? Auth::user()->teacherIds() : $teacher->allTeacherIds();

                // Dari Penugasan Mengajar (TeachingAssignment)
                $taClassroomIds = TeachingAssignment::whereIn('teacher_id', $tIds)
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                    ->where('is_active', true)
                    ->pluck('classroom_id');
                $assignedClassroomIds = $assignedClassroomIds->merge($taClassroomIds);

                // Dari Jadwal Pelajaran (Schedule)
                $schedClassroomIds = Schedule::whereIn('teacher_id', $tIds)
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                    ->pluck('classroom_id');
                $assignedClassroomIds = $assignedClassroomIds->merge($schedClassroomIds);

                // Dari Perwalian (Wali Kelas)
                $homeroomClassroomIds = Classroom::whereIn('homeroom_teacher_id', $tIds)
                    ->where('is_active', true)
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id)->orWhereNull('academic_year_id'))
                    ->pluck('id');
                $assignedClassroomIds = $assignedClassroomIds->merge($homeroomClassroomIds);
            }

            $assignedClassroomIds = $assignedClassroomIds->unique()->filter()->values()->toArray();

            $classrooms = Classroom::with('school')
                ->whereIn('id', $assignedClassroomIds)
                ->where('is_active', true)
                ->orderBy('class_name')
                ->get();
        }

        // 4. Query Siswa berdasarkan Kelas yang Diampu
        $query = Student::with(['school', 'currentClassroom', 'classrooms']);

        if ($request->filled('classroom_id')) {
            $classroomId = $request->classroom_id;
            $query->whereHas('studentClasses', function ($scq) use ($classroomId) {
                $scq->where('classroom_id', $classroomId);
            });
        } elseif (!$isPrincipalOrBk) {
            $allowedIds = $classrooms->pluck('id')->toArray();
            if (!empty($allowedIds)) {
                $query->whereHas('studentClasses', function ($scq) use ($allowedIds) {
                    $scq->whereIn('classroom_id', $allowedIds);
                });
            } else {
                // Guru tidak punya penugasan di unit/TP ini -> jangan tampilkan siswa acak
                $query->whereRaw('1 = 0');
            }
        } elseif ($effectiveSchoolId) {
            $query->where('school_id', $effectiveSchoolId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name')->paginate(15)->withQueryString();

        return view('admin.student-dna.index', compact('students', 'classrooms'));
    }

    /**
     * Display detailed 360° DNA for teacher view.
     */
    public function show(Student $student)
    {
        $this->authorizeStudentAccess($student);
        $analysis = $this->dnaService->analyze($student);

        return view('admin.student-dna.show', compact('student', 'analysis'));
    }

    /**
     * Export PDF from teacher view.
     */
    public function printPdf(Student $student)
    {
        $this->authorizeStudentAccess($student);
        $analysis = $this->dnaService->analyze($student);

        $pdf = Pdf::loadView('admin.student-dna.pdf', compact('student', 'analysis'))
            ->setPaper('a4', 'portrait');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->full_name);
        return $pdf->download("DNA_Akademik_{$cleanName}.pdf");
    }

    /**
     * Verifikasi otorisasi akses guru terhadap DNA siswa tertentu
     */
    private function authorizeStudentAccess(Student $student): void
    {
        $user = auth()->user();
        $activeRole = session('active_role', $user->role);
        $activeYear = AcademicYear::where('is_active', true)->first();

        $effectiveSchoolId = $this->getEffectiveSchoolId();
        $teacher = Teacher::where('user_id', $user->id)
            ->when($effectiveSchoolId, fn($q) => $q->where('school_id', $effectiveSchoolId))
            ->first() 
            ?? Teacher::where('user_id', $user->id)->first();

        // SuperAdmin, Kepala Sekolah, Guru BK, Admin Sekolah berwenang di unit sekolahnya
        if (in_array($activeRole, ['kepala_sekolah', 'guru_bk', 'superadmin', 'admin_sekolah'])
            || ($teacher && method_exists($teacher, 'isPrincipal') && $teacher->isPrincipal())) {
            if ($activeRole !== 'superadmin' && $effectiveSchoolId && $student->school_id != $effectiveSchoolId) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki kewenangan mengakses DNA siswa di luar unit sekolah Anda.');
            }
            return;
        }

        // Guru Pengampu & Wali Kelas: Cek apakah siswa terdaftar pada kelas yang diampu pada TP aktif
        $assignedClassroomIds = collect();
        if ($teacher) {
            $tIds = Auth::user() ? Auth::user()->teacherIds() : $teacher->allTeacherIds();
            $taClassroomIds = TeachingAssignment::whereIn('teacher_id', $tIds)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->where('is_active', true)
                ->pluck('classroom_id');
            $assignedClassroomIds = $assignedClassroomIds->merge($taClassroomIds);

            $schedClassroomIds = Schedule::whereIn('teacher_id', $tIds)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->pluck('classroom_id');
            $assignedClassroomIds = $assignedClassroomIds->merge($schedClassroomIds);

            $homeroomClassroomIds = Classroom::whereIn('homeroom_teacher_id', $tIds)
                ->where('is_active', true)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id)->orWhereNull('academic_year_id'))
                ->pluck('id');
            $assignedClassroomIds = $assignedClassroomIds->merge($homeroomClassroomIds);
        }

        $allowedClassroomIds = $assignedClassroomIds->unique()->filter()->values()->toArray();

        $isStudentInClass = $student->studentClasses()
            ->whereIn('classroom_id', $allowedClassroomIds)
            ->exists();

        if (!$isStudentInClass) {
            abort(403, 'Akses Dibatasi: Anda hanya berwenang mengakses profil DNA siswa yang Anda ajar atau bimbing pada Tahun Pelajaran aktif.');
        }
    }
}
