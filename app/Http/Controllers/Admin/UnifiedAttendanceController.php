<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnifiedAttendanceController extends Controller
{
    /**
     * Helper to resolve common filter parameters
     */
    private function resolveFilters(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin() || $user->isKetuaYayasan();

        // 1. Resolve Group: 'siswa', 'guru', 'pegawai'
        $group = $request->get('group', 'siswa');
        if (!in_array($group, ['siswa', 'guru', 'pegawai'])) {
            $group = 'siswa';
        }

        // 2. Resolve Schools
        $schools = $isSuperAdmin
            ? School::where('is_active', true)->orderBy('id')->get()
            : School::where('id', $user->school_id)->get();

        // 3. Resolve School ID
        $schoolId = $request->get('school_id');
        if (!$isSuperAdmin) {
            $schoolId = $user->school_id;
        } elseif (!$schoolId) {
            // Default to first school
            $schoolId = $schools->first()?->id;
        }

        // If Yayasan (id=4 or name like Yayasan), only 'pegawai' is allowed
        $selectedSchool = $schools->firstWhere('id', $schoolId);
        $isYayasan = $selectedSchool && (str_contains(strtolower($selectedSchool->name), 'yayasan') || strtoupper($selectedSchool->type ?? '') === 'YAYASAN');
        if ($isYayasan && $group !== 'pegawai') {
            $group = 'pegawai';
        }

        // 4. Resolve Date
        $date = $request->get('date', Carbon::now('Asia/Jakarta')->toDateString());

        // 5. Resolve Academic Year
        $activeAY = AcademicYear::where('is_active', true)->first();

        return [
            'user'           => $user,
            'isSuperAdmin'   => $isSuperAdmin,
            'group'          => $group,
            'schools'        => $schools,
            'schoolId'       => $schoolId,
            'selectedSchool' => $selectedSchool,
            'isYayasan'      => $isYayasan,
            'date'           => $date,
            'activeAY'       => $activeAY,
        ];
    }

    /**
     * 1. 🔴 LIVE STREAM PRESENSI
     */
    public function live(Request $request)
    {
        $f = $this->resolveFilters($request);

        // Fetch Live Attendance stream for Today
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $targetDate = $f['date'] === $today ? $today : $f['date'];

        $liveEvents = collect();
        $stats = [
            'total_target' => 0,
            'hadir'        => 0,
            'terlambat'    => 0,
            'izin'         => 0,
            'sakit'        => 0,
            'alpha'        => 0,
            'dinas_luar'   => 0,
            'cuti'         => 0,
            'belum'        => 0,
        ];

        if ($f['group'] === 'siswa') {
            // Siswa Live
            $activeAY = $f['activeAY'];
            $studentQuery = Student::where('school_id', $f['schoolId'])
                ->whereHas('studentClasses', function ($q) use ($activeAY) {
                    $q->where('status', 'aktif')
                      ->when($activeAY, fn($sq) => $sq->where('academic_year_id', $activeAY->id));
                });

            $stats['total_target'] = $studentQuery->count();

            $attendances = Attendance::with(['student.studentClasses.classroom'])
                ->where('date', $targetDate)
                ->whereHas('student', function ($q) use ($f, $activeAY) {
                    $q->where('school_id', $f['schoolId'])
                      ->whereHas('studentClasses', function ($sq) use ($activeAY) {
                          $sq->where('status', 'aktif')
                             ->when($activeAY, fn($ssq) => $ssq->where('academic_year_id', $activeAY->id));
                      });
                })
                ->orderBy('updated_at', 'desc')
                ->take(30)
                ->get();

            foreach ($attendances as $att) {
                $status = $att->status;
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                $cls = $att->student?->studentClasses?->firstWhere('status', 'aktif')?->classroom?->class_name ?? '-';
                $liveEvents->push([
                    'id'           => $att->id,
                    'name'         => $att->student?->full_name ?? 'Siswa',
                    'code'         => $att->student?->nisn ?? $att->student?->nis ?? '-',
                    'subtitle'     => 'Kelas: ' . $cls,
                    'role_label'   => 'Siswa',
                    'status'       => $att->status,
                    'time_in'      => $att->time_in ? substr($att->time_in, 0, 5) : null,
                    'time_out'     => $att->time_out ? substr($att->time_out, 0, 5) : null,
                    'recorded_via' => $att->recorded_via ?: 'gps',
                    'device_id'    => $att->device_id,
                    'timestamp'    => $att->updated_at ? $att->updated_at->diffForHumans() : '-',
                ]);
            }
        } elseif ($f['group'] === 'guru') {
            // Guru Live
            $teacherQuery = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', 'guru')->orWhereHas('teacher');
                });

            $stats['total_target'] = $teacherQuery->count();

            $attendances = EmployeeAttendance::with('employee')
                ->where('date', $targetDate)
                ->where('school_id', $f['schoolId'])
                ->whereHas('employee', function ($q) {
                    $q->where('employee_type', 'guru')->orWhereHas('teacher');
                })
                ->orderBy('updated_at', 'desc')
                ->take(30)
                ->get();

            foreach ($attendances as $att) {
                $status = $att->status;
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                $liveEvents->push([
                    'id'           => $att->id,
                    'name'         => $att->employee?->full_name ?? 'Guru',
                    'code'         => $att->employee?->employee_code ?? '-',
                    'subtitle'     => 'Guru Pengampu',
                    'role_label'   => 'Guru',
                    'status'       => $att->status,
                    'time_in'      => $att->time_in ? substr($att->time_in, 0, 5) : null,
                    'time_out'     => $att->time_out ? substr($att->time_out, 0, 5) : null,
                    'recorded_via' => $att->recorded_via ?: 'gps',
                    'device_id'    => $att->device_id,
                    'timestamp'    => $att->updated_at ? $att->updated_at->diffForHumans() : '-',
                ]);
            }
        } else {
            // Pegawai Live
            $empQuery = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                });

            $stats['total_target'] = $empQuery->count();

            $attendances = EmployeeAttendance::with('employee')
                ->where('date', $targetDate)
                ->where('school_id', $f['schoolId'])
                ->whereHas('employee', function ($q) {
                    $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                })
                ->orderBy('updated_at', 'desc')
                ->take(30)
                ->get();

            foreach ($attendances as $att) {
                $status = $att->status;
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                $liveEvents->push([
                    'id'           => $att->id,
                    'name'         => $att->employee?->full_name ?? 'Pegawai',
                    'code'         => $att->employee?->employee_code ?? '-',
                    'subtitle'     => $att->employee?->position ?? 'Staf / Pegawai',
                    'role_label'   => 'Pegawai',
                    'status'       => $att->status,
                    'time_in'      => $att->time_in ? substr($att->time_in, 0, 5) : null,
                    'time_out'     => $att->time_out ? substr($att->time_out, 0, 5) : null,
                    'recorded_via' => $att->recorded_via ?: 'gps',
                    'device_id'    => $att->device_id,
                    'timestamp'    => $att->updated_at ? $att->updated_at->diffForHumans() : '-',
                ]);
            }
        }

        $recordedCount = $stats['hadir'] + $stats['terlambat'] + $stats['izin'] + $stats['sakit'] + $stats['alpha'] + $stats['dinas_luar'] + $stats['cuti'];
        $stats['belum'] = max(0, $stats['total_target'] - $recordedCount);

        // If AJAX request for live polling, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'stats'      => $stats,
                'liveEvents' => $liveEvents,
                'time_now'   => Carbon::now('Asia/Jakarta')->format('H:i:s'),
            ]);
        }

        return view('admin.attendance.live', array_merge($f, [
            'liveEvents' => $liveEvents,
            'stats'      => $stats,
        ]));
    }

    /**
     * 2. 📊 MONITORING HARIAN
     */
    public function monitoring(Request $request)
    {
        $f = $this->resolveFilters($request);
        $statusFilter = $request->get('status');
        $classroomId = $request->get('classroom_id');
        $search = $request->get('search');

        $items = collect();
        $classrooms = collect();
        $stats = [
            'total'      => 0,
            'hadir'      => 0,
            'terlambat'  => 0,
            'izin'       => 0,
            'sakit'      => 0,
            'alpha'      => 0,
            'dinas_luar' => 0,
            'cuti'       => 0,
            'belum'      => 0,
        ];

        if ($f['group'] === 'siswa') {
            $activeAY = $f['activeAY'];
            $classrooms = Classroom::where('school_id', $f['schoolId'])
                ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
                ->orderBy('class_name')
                ->get();

            $studentQuery = Student::where('school_id', $f['schoolId'])
                ->whereHas('studentClasses', function ($q) use ($activeAY, $classroomId) {
                    $q->where('status', 'aktif')
                      ->when($activeAY, fn($sq) => $sq->where('academic_year_id', $activeAY->id))
                      ->when($classroomId, fn($sq) => $sq->where('classroom_id', $classroomId));
                })
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('full_name', 'like', "%{$search}%")
                           ->orWhere('nisn', 'like', "%{$search}%")
                           ->orWhere('nis', 'like', "%{$search}%");
                    });
                })
                ->with(['studentClasses.classroom'])
                ->orderBy('full_name');

            $allStudents = $studentQuery->get();
            $stats['total'] = $allStudents->count();

            $attendances = Attendance::where('date', $f['date'])
                ->whereIn('student_id', $allStudents->pluck('id'))
                ->get()
                ->keyBy('student_id');

            foreach ($allStudents as $st) {
                $att = $attendances->get($st->id);
                $stStatus = $att ? $att->status : 'belum';

                if (isset($stats[$stStatus])) {
                    $stats[$stStatus]++;
                }

                // Filter by status if specified
                if ($statusFilter && $stStatus !== $statusFilter) {
                    continue;
                }

                $cls = $st->studentClasses->firstWhere('status', 'aktif')?->classroom?->class_name ?? '-';

                $items->push((object)[
                    'id'           => $st->id,
                    'attendance_id'=> $att?->id,
                    'name'         => $st->full_name,
                    'code'         => $st->nisn ?: ($st->nis ?: '-'),
                    'info'         => 'Kelas ' . $cls,
                    'status'       => $stStatus,
                    'time_in'      => $att?->time_in ? substr($att->time_in, 0, 5) : '-',
                    'time_out'     => $att?->time_out ? substr($att->time_out, 0, 5) : '-',
                    'recorded_via' => $att?->recorded_via,
                    'notes'        => $att?->notes,
                    'raw_att'      => $att,
                ]);
            }
        } elseif ($f['group'] === 'guru') {
            $teacherQuery = Employee::with('teacher')
                ->where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', 'guru')->orWhereHas('teacher');
                })
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('full_name', 'like', "%{$search}%")
                           ->orWhere('employee_code', 'like', "%{$search}%")
                           ->orWhere('nip', 'like', "%{$search}%");
                    });
                })
                ->orderBy('full_name');

            $allTeachers = $teacherQuery->get();
            $stats['total'] = $allTeachers->count();

            $attendances = EmployeeAttendance::where('date', $f['date'])
                ->where('school_id', $f['schoolId'])
                ->whereIn('employee_id', $allTeachers->pluck('id'))
                ->get()
                ->keyBy('employee_id');

            foreach ($allTeachers as $tc) {
                $att = $attendances->get($tc->id);
                $tcStatus = $att ? $att->status : 'belum';

                if (isset($stats[$tcStatus])) {
                    $stats[$tcStatus]++;
                }

                if ($statusFilter && $tcStatus !== $statusFilter) {
                    continue;
                }

                $items->push((object)[
                    'id'           => $tc->id,
                    'attendance_id'=> $att?->id,
                    'name'         => $tc->full_name,
                    'code'         => $tc->employee_code ?: ($tc->nip ?: '-'),
                    'info'         => 'Guru Pengampu',
                    'status'       => $tcStatus,
                    'time_in'      => $att?->time_in ? substr($att->time_in, 0, 5) : '-',
                    'time_out'     => $att?->time_out ? substr($att->time_out, 0, 5) : '-',
                    'recorded_via' => $att?->recorded_via,
                    'notes'        => $att?->notes,
                    'raw_att'      => $att,
                ]);
            }
        } else {
            // Pegawai
            $empQuery = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                })
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('full_name', 'like', "%{$search}%")
                           ->orWhere('employee_code', 'like', "%{$search}%")
                           ->orWhere('position', 'like', "%{$search}%");
                    });
                })
                ->orderBy('full_name');

            $allEmps = $empQuery->get();
            $stats['total'] = $allEmps->count();

            $attendances = EmployeeAttendance::where('date', $f['date'])
                ->where('school_id', $f['schoolId'])
                ->whereIn('employee_id', $allEmps->pluck('id'))
                ->get()
                ->keyBy('employee_id');

            foreach ($allEmps as $emp) {
                $att = $attendances->get($emp->id);
                $empStatus = $att ? $att->status : 'belum';

                if (isset($stats[$empStatus])) {
                    $stats[$empStatus]++;
                }

                if ($statusFilter && $empStatus !== $statusFilter) {
                    continue;
                }

                $items->push((object)[
                    'id'           => $emp->id,
                    'attendance_id'=> $att?->id,
                    'name'         => $emp->full_name,
                    'code'         => $emp->employee_code ?: '-',
                    'info'         => $emp->position ?: 'Staf / Pegawai',
                    'status'       => $empStatus,
                    'time_in'      => $att?->time_in ? substr($att->time_in, 0, 5) : '-',
                    'time_out'     => $att?->time_out ? substr($att->time_out, 0, 5) : '-',
                    'recorded_via' => $att?->recorded_via,
                    'notes'        => $att?->notes,
                    'raw_att'      => $att,
                ]);
            }
        }

        return view('admin.attendance.monitoring', array_merge($f, [
            'items'        => $items,
            'classrooms'   => $classrooms,
            'classroomId'  => $classroomId,
            'statusFilter' => $statusFilter,
            'search'       => $search,
            'stats'        => $stats,
        ]));
    }

    /**
     * 3. 📝 INPUT MASSAL / MANUAL
     */
    public function bulkInput(Request $request)
    {
        $f = $this->resolveFilters($request);
        $classroomId = $request->get('classroom_id');

        $persons = collect();
        $existing = collect();
        $classrooms = collect();
        $classroom = null;

        if ($f['group'] === 'siswa') {
            $activeAY = $f['activeAY'];
            $classrooms = Classroom::where('school_id', $f['schoolId'])
                ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
                ->orderBy('class_name')
                ->get();

            if (!$classroomId && $classrooms->isNotEmpty()) {
                $classroomId = $classrooms->first()->id;
            }

            if ($classroomId) {
                $classroom = $classrooms->firstWhere('id', $classroomId);
                if ($classroom) {
                    $persons = $classroom->students()
                        ->where('student_classes.status', 'aktif')
                        ->when($activeAY, fn($q) => $q->where('student_classes.academic_year_id', $activeAY->id))
                        ->orderBy('full_name')
                        ->get();

                    $existing = Attendance::where('classroom_id', $classroomId)
                        ->whereDate('date', $f['date'])
                        ->get()
                        ->keyBy('student_id');
                }
            }
        } elseif ($f['group'] === 'guru') {
            $persons = Employee::with('teacher')
                ->where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', 'guru')->orWhereHas('teacher');
                })
                ->orderBy('full_name')
                ->get();

            $existing = EmployeeAttendance::where('date', $f['date'])
                ->where('school_id', $f['schoolId'])
                ->whereIn('employee_id', $persons->pluck('id'))
                ->get()
                ->keyBy('employee_id');
        } else {
            // Pegawai
            $persons = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                })
                ->orderBy('full_name')
                ->get();

            $existing = EmployeeAttendance::where('date', $f['date'])
                ->where('school_id', $f['schoolId'])
                ->whereIn('employee_id', $persons->pluck('id'))
                ->get()
                ->keyBy('employee_id');
        }

        return view('admin.attendance.bulk', array_merge($f, [
            'persons'     => $persons,
            'existing'    => $existing,
            'classrooms'  => $classrooms,
            'classroomId' => $classroomId,
            'classroom'   => $classroom,
        ]));
    }

    /**
     * Store bulk attendance
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'school_id'  => 'required|exists:schools,id',
            'group'      => 'required|in:siswa,guru,pegawai',
            'attendance' => 'required|array',
        ]);

        $date     = $request->date;
        $schoolId = $request->school_id;
        $group    = $request->group;
        $userId   = auth()->id();
        $count    = 0;

        if ($group === 'siswa') {
            $classroomId = $request->classroom_id;
            $classroom = Classroom::findOrFail($classroomId);

            foreach ($request->attendance as $studentId => $data) {
                if (empty($data['status'])) continue;

                $timeIn = !empty($data['time_in']) ? $data['time_in'] : null;
                $timeOut = !empty($data['time_out']) ? $data['time_out'] : null;

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id'   => $studentId,
                        'classroom_id' => $classroomId,
                        'date'         => $date,
                    ],
                    [
                        'status'       => $data['status'],
                        'time_in'      => in_array($data['status'], ['hadir', 'terlambat']) ? $timeIn : null,
                        'time_out'     => in_array($data['status'], ['hadir', 'terlambat']) ? $timeOut : null,
                        'notes'        => $data['notes'] ?? null,
                        'recorded_via' => 'manual',
                        'created_by'   => $userId,
                    ]
                );
                $count++;
            }

            return redirect()->route('admin.attendance.bulk', [
                'group'        => 'siswa',
                'school_id'    => $schoolId,
                'classroom_id' => $classroomId,
                'date'         => $date,
            ])->with('success', "Absensi {$count} siswa kelas {$classroom->class_name} berhasil disimpan.");
        } else {
            // Guru & Pegawai
            foreach ($request->attendance as $employeeId => $data) {
                if (empty($data['status'])) continue;

                EmployeeAttendance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date'        => $date,
                    ],
                    [
                        'school_id'    => $schoolId,
                        'status'       => $data['status'],
                        'time_in'      => $data['time_in'] ?? null,
                        'time_out'     => $data['time_out'] ?? null,
                        'notes'        => $data['notes'] ?? null,
                        'recorded_via' => 'manual',
                        'recorded_by'  => $userId,
                    ]
                );
                $count++;
            }

            $label = $group === 'guru' ? 'guru' : 'pegawai';
            return redirect()->route('admin.attendance.bulk', [
                'group'     => $group,
                'school_id' => $schoolId,
                'date'      => $date,
            ])->with('success', "Absensi {$count} {$label} berhasil disimpan.");
        }
    }

    /**
     * 4. 📑 REKAPITULASI & LAPORAN
     */
    public function rekap(Request $request)
    {
        $f = $this->resolveFilters($request);
        $month = (int) $request->get('month', Carbon::now('Asia/Jakarta')->month);
        $year = (int) $request->get('year', Carbon::now('Asia/Jakarta')->year);
        $classroomId = $request->get('classroom_id');

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $persons = collect();
        $matrix = [];
        $classrooms = collect();

        if ($f['group'] === 'siswa') {
            $activeAY = $f['activeAY'];
            $classrooms = Classroom::where('school_id', $f['schoolId'])
                ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
                ->orderBy('class_name')
                ->get();

            if (!$classroomId && $classrooms->isNotEmpty()) {
                $classroomId = $classrooms->first()->id;
            }

            if ($classroomId) {
                $classroom = $classrooms->firstWhere('id', $classroomId);
                if ($classroom) {
                    $persons = $classroom->students()
                        ->where('student_classes.status', 'aktif')
                        ->when($activeAY, fn($q) => $q->where('student_classes.academic_year_id', $activeAY->id))
                        ->orderBy('full_name')
                        ->get();

                    $attendances = Attendance::where('classroom_id', $classroomId)
                        ->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->get();

                    foreach ($attendances as $att) {
                        $day = (int) Carbon::parse($att->date)->day;
                        $matrix[$att->student_id][$day] = $att;
                    }
                }
            }
        } elseif ($f['group'] === 'guru') {
            $persons = Employee::with('teacher')
                ->where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', 'guru')->orWhereHas('teacher');
                })
                ->orderBy('full_name')
                ->get();

            $attendances = EmployeeAttendance::where('school_id', $f['schoolId'])
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereIn('employee_id', $persons->pluck('id'))
                ->get();

            foreach ($attendances as $att) {
                $day = (int) Carbon::parse($att->date)->day;
                $matrix[$att->employee_id][$day] = $att;
            }
        } else {
            // Pegawai
            $persons = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                })
                ->orderBy('full_name')
                ->get();

            $attendances = EmployeeAttendance::where('school_id', $f['schoolId'])
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereIn('employee_id', $persons->pluck('id'))
                ->get();

            foreach ($attendances as $att) {
                $day = (int) Carbon::parse($att->date)->day;
                $matrix[$att->employee_id][$day] = $att;
            }
        }

        return view('admin.attendance.rekap', array_merge($f, [
            'persons'     => $persons,
            'matrix'      => $matrix,
            'daysInMonth' => $daysInMonth,
            'month'       => $month,
            'year'        => $year,
            'classrooms'  => $classrooms,
            'classroomId' => $classroomId,
        ]));
    }

    /**
     * Delete single attendance
     */
    public function destroy(Request $request, $id)
    {
        $group = $request->get('group', 'siswa');

        if ($group === 'siswa') {
            $att = Attendance::findOrFail($id);
            $att->delete();
        } else {
            $att = EmployeeAttendance::findOrFail($id);
            $att->delete();
        }

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }
}
