<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\AcademicYear;
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
     * - Guru Pengampu: Hanya siswa pada kelas/jadwal ajar aktif.
     * - Wali Kelas: Siswa pada rombel perwalian & kelas ajar aktif.
     * - Kepala Sekolah & Guru BK: Seluruh siswa di unit sekolahnya.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $schoolId = $this->getEffectiveSchoolId($teacher);

        // Cek apakah user memiliki hak akses menyeluruh (Kepala Sekolah, Guru BK, Superadmin)
        $isPrincipalOrBk = $user->isSuperAdmin() 
            || $user->hasAnyRole(['kepala_sekolah', 'guru_bk', 'superadmin', 'admin_sekolah'])
            || ($teacher && method_exists($teacher, 'isPrincipal') && $teacher->isPrincipal());

        // 1. Dapatkan daftar kelas berdasarkan Penugasan Mengajar & Tahun Pelajaran Aktif
        if ($isPrincipalOrBk) {
            $classroomsQuery = Classroom::with('school')->where('is_active', true);
            if ($schoolId) {
                $classroomsQuery->where('school_id', $schoolId);
            }
            if ($activeYear) {
                $classroomsQuery->where(function ($yq) use ($activeYear) {
                    $yq->where('academic_year_id', $activeYear->id)
                       ->orWhereNull('academic_year_id');
                });
            }
            $classrooms = $classroomsQuery->orderBy('class_name')->get();
        } else {
            // Guru Pengampu / Wali Kelas: Ambil kelas sesuai Penugasan Mengajar, Jadwal, atau Wali Kelas di TP aktif
            $classrooms = Classroom::where('is_active', true)
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->when($activeYear, function ($yq) use ($activeYear) {
                    $yq->where('academic_year_id', $activeYear->id)
                       ->orWhereNull('academic_year_id');
                })
                ->where(function ($q) use ($teacher) {
                    if ($teacher) {
                        $q->whereHas('schedules', fn($sq) => $sq->where('teacher_id', $teacher->id))
                          ->orWhereHas('teachingAssignments', fn($tq) => $tq->where('teacher_id', $teacher->id)->where('is_active', true))
                          ->orWhere('homeroom_teacher_id', $teacher->id);
                    }
                })
                ->with('school')
                ->orderBy('class_name')
                ->get();

            // Fallback: Jika guru belum memiliki penugasan spesifik di TP aktif, tampilkan kelas dari unit sekolahnya
            if ($classrooms->isEmpty() && $schoolId) {
                $classrooms = Classroom::with('school')
                    ->where('school_id', $schoolId)
                    ->where('is_active', true)
                    ->orderBy('class_name')
                    ->get();
            }
        }

        // 2. Query Siswa berdasarkan Penugasan Mengajar / Filter Kelas
        $query = Student::with(['school', 'currentClassroom', 'classrooms']);

        if ($request->filled('classroom_id')) {
            // Jika memilih kelas spesifik dari dropdown
            $classroomId = $request->classroom_id;
            $query->whereHas('studentClasses', function ($scq) use ($classroomId) {
                $scq->where('classroom_id', $classroomId);
            });
        } elseif (!$isPrincipalOrBk && $classrooms->isNotEmpty()) {
            // Guru Pengampu: Hanya tampilkan siswa di rombel/kelas yang diampu guru tersebut
            $teacherClassroomIds = $classrooms->pluck('id')->toArray();
            $query->whereHas('studentClasses', function ($scq) use ($teacherClassroomIds) {
                $scq->whereIn('classroom_id', $teacherClassroomIds);
            });
        } elseif ($schoolId) {
            $query->where('school_id', $schoolId);
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
        $teacher = Teacher::where('user_id', $user->id)->first();
        $schoolId = $this->getEffectiveSchoolId($teacher);

        // SuperAdmin, Kepala Sekolah, Guru BK, Admin Sekolah memiliki kewenangan di unit sekolahnya
        if ($user->isSuperAdmin() 
            || $user->hasAnyRole(['kepala_sekolah', 'guru_bk', 'superadmin', 'admin_sekolah'])
            || ($teacher && method_exists($teacher, 'isPrincipal') && $teacher->isPrincipal())) {
            if (!$user->isSuperAdmin() && $schoolId && $student->school_id != $schoolId) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki kewenangan mengakses DNA siswa di luar unit sekolah Anda.');
            }
            return;
        }

        // Guru Pengampu & Wali Kelas: Pastikan siswa terdaftar pada kelas yang diampu guru pada TP aktif
        $activeYear = AcademicYear::where('is_active', true)->first();
        $allowedClassroomIds = Classroom::where('is_active', true)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when($activeYear, fn($yq) => $yq->where('academic_year_id', $activeYear->id)->orWhereNull('academic_year_id'))
            ->where(function ($q) use ($teacher) {
                if ($teacher) {
                    $q->whereHas('schedules', fn($sq) => $sq->where('teacher_id', $teacher->id))
                      ->orWhereHas('teachingAssignments', fn($tq) => $tq->where('teacher_id', $teacher->id)->where('is_active', true))
                      ->orWhere('homeroom_teacher_id', $teacher->id);
                }
            })
            ->pluck('id')
            ->toArray();

        $isStudentInClass = $student->studentClasses()
            ->whereIn('classroom_id', $allowedClassroomIds)
            ->exists();

        if (!$isStudentInClass) {
            abort(403, 'Akses Dibatasi: Anda hanya berwenang mengakses profil DNA siswa yang Anda ajar atau bimbing pada Tahun Pelajaran aktif.');
        }
    }
}
