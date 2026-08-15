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
use App\Models\PklPlacement;
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

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $dayLabels = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        $today = date('l');
        $activeDay = in_array($today, $days) ? $today : 'Monday';

        $schedulesByDay = [];
        foreach ($days as $day) {
            $schedulesByDay[$day] = collect();
        }

        if ($classroom) {
            $schedules = Schedule::where('classroom_id', $classroom->id)
                ->with(['subject', 'teacher'])
                ->orderBy('start_time', 'asc')
                ->get();

            foreach ($schedules as $sch) {
                if (isset($schedulesByDay[$sch->day_of_week])) {
                    $schedulesByDay[$sch->day_of_week]->push($sch);
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
            $exams = CbtExam::where(function ($query) use ($classroom) {
                $query->whereHas('classrooms', function ($q) use ($classroom) {
                    $q->where('classroom_id', $classroom->id);
                })->orWhere('exam_scope', 'school');
            })->whereIn('status', ['published', 'active'])
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
            $pklPlacement = PklPlacement::where('student_id', $student->id)
                ->with(['dudi', 'teacher', 'academicYear'])
                ->first();

            if ($pklPlacement) {
                $logs = PklLog::where('pkl_placement_id', $pklPlacement->id)
                    ->orderBy('log_date', 'desc')
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
        $pklPlacement = PklPlacement::where('student_id', $student->id)->first();
        if (!$pklPlacement) {
            return back()->with('error', 'Data penempatan PKL Anda tidak ditemukan.');
        }

        PklLog::create([
            'pkl_placement_id' => $pklPlacement->id,
            'log_date' => $request->input('date'),
            'activity' => $request->input('activity_description'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Jurnal kegiatan PKL berhasil dikirim untuk diverifikasi.');
    }
}
