<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Grade;
use App\Models\StudentBill;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\CbtExam;
use App\Models\PklStudent;
use App\Models\PklLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MobileStudentController extends Controller
{
    private function getStudent()
    {
        $student = Student::where('user_id', Auth::id())->with('school')->first();
        if ($student) return $student;

        // Fallback for Super Admin / Testers switching role to Siswa
        $user = Auth::user();
        if ($user) {
            $student = Student::when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))
                ->with('school')
                ->first();
        }

        return $student;
    }

    /**
     * Jadwal Pelajaran Siswa Mobile
     */
    public function jadwal()
    {
        $student = $this->getStudent();
        $classroom = $student ? $student->currentClassroom()->first() : null;

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu',
        ];

        $today = strtolower(now()->format('l'));
        $activeDay = in_array($today, $days) ? $today : 'monday';

        $schedulesByDay = [];
        foreach ($days as $day) {
            $schedulesByDay[$day] = collect();
        }

        if ($classroom) {
            $allSchedules = Schedule::where('classroom_id', $classroom->id)
                ->with(['subject', 'teacher.user', 'timeSlot'])
                ->get();

            foreach ($allSchedules as $sch) {
                $dayLower = strtolower($sch->day_of_week);
                if (isset($schedulesByDay[$dayLower])) {
                    $schedulesByDay[$dayLower]->push($sch);
                }
            }
        }

        return view('mobile.student.jadwal', compact('student', 'classroom', 'days', 'dayLabels', 'activeDay', 'schedulesByDay'));
    }

    /**
     * Rekap Nilai Siswa Mobile
     */
    public function nilai()
    {
        $student = $this->getStudent();
        $activeSemester = Cache::remember('active_semester', 3600, fn() => Semester::where('is_active', true)->first());

        $grades = collect();
        $avgScore = 0;

        if ($student) {
            $query = Grade::where('student_id', $student->id)->with(['subject']);
            if ($activeSemester) {
                $query->where('semester_id', $activeSemester->id);
            }
            $grades = $query->orderBy('created_at', 'desc')->get();
            $avgScore = round($grades->avg('score') ?? 0, 1);
        }

        // Group by subject
        $gradesBySubject = $grades->groupBy(fn($g) => $g->subject->name ?? 'Lainnya');

        return view('mobile.student.nilai', compact('student', 'activeSemester', 'grades', 'gradesBySubject', 'avgScore'));
    }

    /**
     * Tagihan & Keuangan SPP Mobile
     */
    public function tagihan()
    {
        $student = $this->getStudent();
        $bills = collect();
        $totalAmount = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;

        if ($student) {
            $bills = StudentBill::where('student_id', $student->id)
                ->with(['academicYear', 'paymentType', 'payments'])
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            foreach ($bills as $bill) {
                $typeName = $bill->paymentType->type_name ?? 'SPP / Uang Sekolah';
                if ($bill->month && isset($monthNames[$bill->month])) {
                    $bill->display_title = $typeName . ' (' . $monthNames[$bill->month] . ' ' . ($bill->year ?? '') . ')';
                } else {
                    $bill->display_title = $typeName;
                }
                $bill->sisa_tunggakan = max(0, $bill->amount - $bill->paid_amount);
            }

            $totalAmount = $bills->sum('amount');
            $totalPaid = $bills->sum('paid_amount');
            $totalOutstanding = max(0, $totalAmount - $totalPaid);
        }

        return view('mobile.student.tagihan', compact('student', 'bills', 'totalAmount', 'totalPaid', 'totalOutstanding'));
    }

    /**
     * CBT / Ujian Online Siswa Mobile
     */
    public function cbt()
    {
        $student = $this->getStudent();
        $classroom = $student ? $student->currentClassroom()->first() : null;
        $exams = collect();

        if ($classroom) {
            $exams = CbtExam::whereHas('classrooms', function ($q) use ($classroom) {
                $q->where('classroom_id', $classroom->id);
            })->where('is_active', true)
              ->with('subject')
              ->latest()
              ->get();
        }

        return view('mobile.student.cbt', compact('student', 'classroom', 'exams'));
    }

    /**
     * PKL & Jurnal Siswa Mobile
     */
    public function pkl()
    {
        $student = $this->getStudent();
        $pklPlacement = null;
        $logs = collect();

        if ($student) {
            $pklPlacement = PklStudent::where('student_id', $student->id)
                ->with(['company', 'advisor'])
                ->first();

            if ($pklPlacement) {
                $logs = PklLog::where('pkl_student_id', $pklPlacement->id)
                    ->orderBy('date', 'desc')
                    ->get();
            }
        }

        return view('mobile.student.pkl', compact('student', 'pklPlacement', 'logs'));
    }

    /**
     * Submit PKL Log Entry
     */
    public function storePklLog(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'activity_description' => 'required|string',
        ]);

        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }
        $pklPlacement = PklStudent::where('student_id', $student->id)->firstOrFail();

        PklLog::create([
            'pkl_student_id' => $pklPlacement->id,
            'date' => $request->input('date'),
            'activity_description' => $request->input('activity_description'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Jurnal kegiatan PKL berhasil dikirim untuk diverifikasi.');
    }
}
