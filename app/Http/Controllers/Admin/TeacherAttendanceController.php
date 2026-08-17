<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\School;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Tampilkan daftar absensi guru hari ini.
     * Filter: employee_type = 'guru'
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', today()->toDateString());

        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $schoolId = $user->school_id;
        }

        $teachers = collect();
        $attendances = collect();
        $stats = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'dinas_luar' => 0, 'cuti' => 0, 'belum' => 0];

        if ($schoolId) {
            $teachers = Employee::with('school', 'teacher')
                ->where('is_active', true)
                ->where('school_id', $schoolId)
                ->where(function($q) {
                    $q->where('employee_type', 'guru')
                      ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
                })
                ->orderBy('full_name')
                ->get();

            $attendances = EmployeeAttendance::where('date', $date)
                ->where('school_id', $schoolId)
                ->whereHas('employee', fn($q) => $q->where('employee_type', 'guru')->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher')))
                ->get()
                ->keyBy('employee_id');

            foreach ($teachers as $teacher) {
                $att = $attendances->get($teacher->id);
                if ($att) {
                    $stats[$att->status] = ($stats[$att->status] ?? 0) + 1;
                } else {
                    $stats['belum']++;
                }
            }

            // Tampilkan yang sudah absen, urutkan dari yang terbaru
            $teachers = $teachers->filter(function ($teacher) use ($attendances) {
                return $attendances->has($teacher->id);
            })->sortByDesc(function ($teacher) use ($attendances) {
                return $attendances->get($teacher->id)->created_at;
            })->values();
        }

        $schools = $user->isSuperAdmin() || $user->isKetuaYayasan()
            ? School::where('is_active', true)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        return view('admin.teachers.attendance.index', compact(
            'teachers', 'attendances', 'stats', 'schools', 'schoolId', 'date'
        ));
    }

    /**
     * Form input absensi massal guru.
     */
    public function bulkInput(Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', today()->toDateString());

        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $schoolId = $user->school_id;
        }

        if (!$schoolId) {
            return redirect()->route('admin.teachers.attendance.index')
                ->with('error', 'Pilih sekolah terlebih dahulu.');
        }

        $school = School::findOrFail($schoolId);
        $teachers = Employee::with('teacher')
            ->where('is_active', true)
            ->where('school_id', $schoolId)
            ->where(function($q) {
                $q->where('employee_type', 'guru')
                  ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
            })
            ->orderBy('full_name')
            ->get();

        $existing = EmployeeAttendance::where('date', $date)
            ->where('school_id', $schoolId)
            ->whereHas('employee', fn($q) => $q->where('employee_type', 'guru')->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher')))
            ->get()
            ->keyBy('employee_id');

        return view('admin.teachers.attendance.bulk-input', compact(
            'teachers', 'existing', 'school', 'date'
        ));
    }

    /**
     * Simpan absensi massal guru.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'date'                   => 'required|date',
            'school_id'              => 'required|exists:schools,id',
            'attendance'             => 'required|array',
            'attendance.*.status'    => 'required|in:hadir,sakit,izin,alpha,dinas_luar,cuti',
        ]);

        $date     = $request->date;
        $schoolId = $request->school_id;
        $userId   = auth()->id();
        $count    = 0;

        foreach ($request->attendance as $employeeId => $data) {
            // Pastikan pegawai ini memang guru dan milik sekolah yang dimaksud
            $teacher = Employee::where('id', $employeeId)
                ->where('school_id', $schoolId)
                ->where(function($q) {
                    $q->where('employee_type', 'guru')
                      ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
                })
                ->first();

            if (!$teacher) {
                continue;
            }

            EmployeeAttendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                [
                    'school_id'   => $schoolId,
                    'status'      => $data['status'],
                    'time_in'     => $data['time_in'] ?? null,
                    'time_out'    => $data['time_out'] ?? null,
                    'notes'       => $data['notes'] ?? null,
                    'recorded_via' => 'manual',
                    'recorded_by' => $userId,
                ]
            );
            $count++;
        }

        return redirect()->route('admin.teachers.attendance.index', [
            'date' => $date, 'school_id' => $schoolId
        ])->with('success', "Absensi {$count} guru berhasil disimpan.");
    }

    /**
     * Rekap bulanan absensi guru.
     */
    public function rekap(Request $request)
    {
        $user  = auth()->user();
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $schoolId = $user->school_id;
        }

        $teachers       = collect();
        $attendanceData = [];
        $daysInMonth    = Carbon::create($year, $month)->daysInMonth;

        if ($schoolId) {
            $teachers = Employee::where('is_active', true)
                ->where('school_id', $schoolId)
                ->where(function($q) {
                    $q->where('employee_type', 'guru')
                      ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
                })
                ->with('teacher')
                ->orderBy('full_name')
                ->get();

            $attendances = EmployeeAttendance::where('school_id', $schoolId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereHas('employee', fn($q) => $q->where('employee_type', 'guru')->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher')))
                ->get();

            // Bangun matrix: employee_id => [day => EmployeeAttendance model]
            foreach ($attendances as $att) {
                $day = $att->date->day;
                $attendanceData[$att->employee_id][$day] = $att;
            }
        }

        $schools = $user->isSuperAdmin() || $user->isKetuaYayasan()
            ? School::where('is_active', true)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        return view('admin.teachers.attendance.rekap', compact(
            'teachers', 'attendanceData', 'schools', 'schoolId', 'month', 'year', 'daysInMonth'
        ));
    }

    /**
     * Hapus record absensi guru.
     */
    public function destroy(EmployeeAttendance $attendance)
    {
        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi guru berhasil dihapus.');
    }
}
