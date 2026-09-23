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
                      ->whereNotNull('classroom_id')
                      ->when($activeAY, fn($sq) => $sq->where('academic_year_id', $activeAY->id));
                });

            $stats['total_target'] = $studentQuery->count();

            $attendances = Attendance::with(['student.studentClasses.classroom'])
                ->where('date', $targetDate)
                ->whereHas('student', function ($q) use ($f, $activeAY) {
                    $q->where('school_id', $f['schoolId'])
                      ->whereHas('studentClasses', function ($sq) use ($activeAY) {
                          $sq->where('status', 'aktif')
                             ->whereNotNull('classroom_id')
                             ->when($activeAY, fn($ssq) => $ssq->where('academic_year_id', $activeAY->id));
                      });
                })
                ->orderBy('id', 'desc')
                ->get();

            // Deduplikasi: 1 siswa = 1 status per hari (prioritaskan record dengan scan/waktu fisik atau status terlambat)
            $attendances = $attendances->groupBy('student_id')->map(function ($group) {
                return $group->first(fn($r) => !empty($r->time_out) && !in_array($r->time_out, ['00:00:00', '00:00']))
                    ?? $group->first(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))
                    ?? $group->first();
            })->values();

            $totalEvents = $attendances->count();

            foreach ($attendances as $idx => $att) {
                $status = $att->status;
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                $cls = $att->student?->studentClasses?->firstWhere('status', 'aktif')?->classroom?->class_name ?? '-';
                $liveEvents->push([
                    'id'           => $att->id,
                    'person_id'    => $att->student_id,
                    'classroom_id' => $att->classroom_id,
                    'seq_no'       => $totalEvents - $idx,
                    'is_latest'    => ($idx === 0),
                    'name'         => $att->student?->full_name ?? 'Siswa',
                    'code'         => $att->student?->nisn ?? $att->student?->nis ?? '-',
                    'photo_url'    => $att->student?->photo_url,
                    'subtitle'     => 'Kelas: ' . $cls,
                    'role_label'   => 'Siswa',
                    'status'       => $att->status,
                    'time_in'      => $att->time_in ? substr($att->time_in, 0, 5) : null,
                    'time_out'     => $att->time_out ? substr($att->time_out, 0, 5) : null,
                    'raw_time_in'  => $att->time_in ? substr($att->time_in, 0, 5) : '',
                    'raw_time_out' => $att->time_out ? substr($att->time_out, 0, 5) : '',
                    'notes'        => $att->notes,
                    'recorded_via' => $att->recorded_via ?: 'gps',
                    'device_id'    => $att->device_id,
                    'timestamp'    => $att->time_in ? 'Jam ' . substr($att->time_in, 0, 5) . ' WIB' : '-',
                ]);
            }
        } elseif ($f['group'] === 'guru') {
            // Guru Live
            $teacherQuery = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) {
                    $q->where('employee_type', 'guru')
                      ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
                });

            $stats['total_target'] = $teacherQuery->count();

            $attendances = EmployeeAttendance::with('employee')
                ->where('date', $targetDate)
                ->where('school_id', $f['schoolId'])
                ->whereHas('employee', function ($q) {
                    $q->where('employee_type', 'guru')
                      ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
                })
                ->orderBy('id', 'desc')
                ->get();

            $attendances = $attendances->groupBy('employee_id')->map(function ($group) {
                return $group->first(fn($r) => !empty($r->time_out) && !in_array($r->time_out, ['00:00:00', '00:00']))
                    ?? $group->first(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))
                    ?? $group->first();
            })->values();

            $totalEvents = $attendances->count();

            foreach ($attendances as $idx => $att) {
                $status = $att->status;
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                $liveEvents->push([
                    'id'           => $att->id,
                    'person_id'    => $att->employee_id,
                    'classroom_id' => null,
                    'seq_no'       => $totalEvents - $idx,
                    'is_latest'    => ($idx === 0),
                    'name'         => $att->employee?->full_name ?? 'Guru',
                    'code'         => $att->employee?->employee_code ?? '-',
                    'photo_url'    => $att->employee?->photo_url,
                    'subtitle'     => 'Guru Pengampu',
                    'role_label'   => 'Guru',
                    'status'       => $att->status,
                    'time_in'      => $att->time_in ? substr($att->time_in, 0, 5) : null,
                    'time_out'     => $att->time_out ? substr($att->time_out, 0, 5) : null,
                    'raw_time_in'  => $att->time_in ? substr($att->time_in, 0, 5) : '',
                    'raw_time_out' => $att->time_out ? substr($att->time_out, 0, 5) : '',
                    'notes'        => $att->notes,
                    'recorded_via' => $att->recorded_via ?: 'gps',
                    'device_id'    => $att->device_id,
                    'timestamp'    => $att->time_in ? 'Jam ' . substr($att->time_in, 0, 5) . ' WIB' : '-',
                ]);
            }
        } else {
            // Pegawai Live
            $empQuery = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) use ($f) {
                    if ($f['isYayasan']) {
                        $q->whereNotNull('id');
                    } else {
                        $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                    }
                });

            $stats['total_target'] = $empQuery->count();

            $attendances = EmployeeAttendance::with('employee')
                ->where('date', $targetDate)
                ->where('school_id', $f['schoolId'])
                ->whereHas('employee', function ($q) use ($f) {
                    if ($f['isYayasan']) {
                        $q->whereNotNull('id');
                    } else {
                        $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                    }
                })
                ->orderBy('id', 'desc')
                ->get();

            $attendances = $attendances->groupBy('employee_id')->map(function ($group) {
                return $group->first(fn($r) => !empty($r->time_out) && !in_array($r->time_out, ['00:00:00', '00:00']))
                    ?? $group->first(fn($r) => !empty($r->time_in) && !in_array($r->time_in, ['00:00:00', '00:00']))
                    ?? $group->first();
            })->values();

            $totalEvents = $attendances->count();

            foreach ($attendances as $idx => $att) {
                $status = $att->status;
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                $liveEvents->push([
                    'id'           => $att->id,
                    'person_id'    => $att->employee_id,
                    'classroom_id' => null,
                    'seq_no'       => $totalEvents - $idx,
                    'is_latest'    => ($idx === 0),
                    'name'         => $att->employee?->full_name ?? 'Pegawai',
                    'code'         => $att->employee?->employee_code ?? '-',
                    'photo_url'    => $att->employee?->photo_url,
                    'subtitle'     => $att->employee?->position ?? 'Staf / Pegawai',
                    'role_label'   => 'Pegawai',
                    'status'       => $att->status,
                    'time_in'      => $att->time_in ? substr($att->time_in, 0, 5) : null,
                    'time_out'     => $att->time_out ? substr($att->time_out, 0, 5) : null,
                    'raw_time_in'  => $att->time_in ? substr($att->time_in, 0, 5) : '',
                    'raw_time_out' => $att->time_out ? substr($att->time_out, 0, 5) : '',
                    'notes'        => $att->notes,
                    'recorded_via' => $att->recorded_via ?: 'gps',
                    'device_id'    => $att->device_id,
                    'timestamp'    => $att->time_in ? 'Jam ' . substr($att->time_in, 0, 5) . ' WIB' : '-',
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
                      ->whereNotNull('classroom_id')
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

                $clsObj = $st->studentClasses->firstWhere('status', 'aktif')?->classroom;
                $cls = $clsObj?->class_name ?? '-';

                $items->push((object)[
                    'id'           => $st->id,
                    'attendance_id'=> $att?->id,
                    'person_id'    => $st->id,
                    'name'         => $st->full_name,
                    'code'         => $st->nisn ?: ($st->nis ?: '-'),
                    'photo_url'    => $st->photo_url,
                    'info'         => 'Kelas ' . $cls,
                    'classroom_id' => $clsObj?->id,
                    'status'       => $stStatus,
                    'time_in'      => $att?->time_in ? substr($att->time_in, 0, 5) : '-',
                    'time_out'     => $att?->time_out ? substr($att->time_out, 0, 5) : '-',
                    'raw_time_in'  => $att?->time_in ? substr($att->time_in, 0, 5) : '',
                    'raw_time_out' => $att?->time_out ? substr($att->time_out, 0, 5) : '',
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
                    $q->where('employee_type', 'guru')
                      ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
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
                    'person_id'    => $tc->id,
                    'name'         => $tc->full_name,
                    'code'         => $tc->employee_code ?: ($tc->nip ?: '-'),
                    'photo_url'    => $tc->photo_url,
                    'info'         => 'Guru Pengampu',
                    'classroom_id' => null,
                    'status'       => $tcStatus,
                    'time_in'      => $att?->time_in ? substr($att->time_in, 0, 5) : '-',
                    'time_out'     => $att?->time_out ? substr($att->time_out, 0, 5) : '-',
                    'raw_time_in'  => $att?->time_in ? substr($att->time_in, 0, 5) : '',
                    'raw_time_out' => $att?->time_out ? substr($att->time_out, 0, 5) : '',
                    'recorded_via' => $att?->recorded_via,
                    'notes'        => $att?->notes,
                    'raw_att'      => $att,
                ]);
            }
        } else {
            // Pegawai
            $empQuery = Employee::where('is_active', true)
                ->where('school_id', $f['schoolId'])
                ->where(function ($q) use ($f) {
                    if ($f['isYayasan']) {
                        $q->whereNotNull('id');
                    } else {
                        $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                    }
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
                    'person_id'    => $emp->id,
                    'name'         => $emp->full_name,
                    'code'         => $emp->employee_code ?: '-',
                    'photo_url'    => $emp->photo_url,
                    'info'         => $emp->position ?: 'Staf / Pegawai',
                    'classroom_id' => null,
                    'status'       => $empStatus,
                    'time_in'      => $att?->time_in ? substr($att->time_in, 0, 5) : '-',
                    'time_out'     => $att?->time_out ? substr($att->time_out, 0, 5) : '-',
                    'raw_time_in'  => $att?->time_in ? substr($att->time_in, 0, 5) : '',
                    'raw_time_out' => $att?->time_out ? substr($att->time_out, 0, 5) : '',
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
                    $q->where('employee_type', 'guru')
                      ->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
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
                ->where(function ($q) use ($f) {
                    if ($f['isYayasan']) {
                        $q->whereNotNull('id');
                    } else {
                        $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                    }
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
            $isToday = ($date === date('Y-m-d'));
            $currentTime = now()->format('H:i:s');

            // Preload status PKL aktif untuk semua siswa di kelas ini (pengecualian jam masuk)
            $activePklStudentIds = \App\Models\PklPlacement::whereIn('student_id', array_keys($request->attendance))
                ->whereIn('status', ['active', 'aktif', 'approved', 'ongoing', 'berjalan'])
                ->where(function($q) use ($date) {
                    $q->whereNull('start_date')->orWhereDate('start_date', '<=', $date);
                })
                ->where(function($q) use ($date) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
                })
                ->pluck('student_id')
                ->flip()
                ->toArray();

            foreach ($request->attendance as $studentId => $data) {
                if (empty($data['status'])) continue;

                $status = $data['status'];
                $timeIn = !empty($data['time_in']) ? $data['time_in'] : ($isToday && in_array($status, ['hadir', 'terlambat']) ? $currentTime : null);
                $timeOut = !empty($data['time_out']) ? $data['time_out'] : null;

                // KETENTUAN: Bila melebihi batas toleransi kehadiran kelas, otomatis menjadi 'terlambat'
                // Pilihan izin, sakit, dan alpha tetap dihormati (KECUALI siswa aktif PKL)
                $isStudentPkl = isset($activePklStudentIds[$studentId]);
                if ($status === 'hadir' && !$isStudentPkl) {
                    $checkTime = $timeIn ?: ($isToday ? $currentTime : null);
                    if ($checkTime && $classroom && $classroom->isLate($checkTime)) {
                        $status = 'terlambat';
                    }
                }

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id'   => $studentId,
                        'classroom_id' => $classroomId,
                        'date'         => $date,
                    ],
                    [
                        'status'       => $status,
                        'time_in'      => in_array($status, ['hadir', 'terlambat']) ? $timeIn : null,
                        'time_out'     => in_array($status, ['hadir', 'terlambat']) ? $timeOut : null,
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
                    $q->where('employee_type', 'guru')->orWhere(fn($sq) => $sq->whereNull('employee_type')->whereHas('teacher'));
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
                ->where(function ($q) use ($f) {
                    if ($f['isYayasan']) {
                        $q->whereNotNull('id');
                    } else {
                        $q->where('employee_type', '!=', 'guru')->whereDoesntHave('teacher');
                    }
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
     * Store or update single attendance record (from Edit Modal)
     */
    public function singleSave(Request $request)
    {
        $request->validate([
            'group'     => 'required|in:siswa,guru,pegawai',
            'person_id' => 'required|integer',
            'school_id' => 'required|exists:schools,id',
            'date'      => 'required|date',
            'status'    => 'required|in:hadir,terlambat,izin,sakit,dinas_luar,cuti,alpha,belum',
            'time_in'   => 'nullable|string',
            'time_out'  => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $group = $request->group;
        $personId = (int) $request->person_id;
        $schoolId = (int) $request->school_id;
        $date = $request->date;
        $status = $request->status;
        $timeIn = $request->time_in ? substr($request->time_in, 0, 5) : null;
        $timeOut = $request->time_out ? substr($request->time_out, 0, 5) : null;
        $notes = $request->notes;
        $userId = auth()->id();

        if ($status === 'belum') {
            // Delete attendance record if set back to 'belum'
            if ($group === 'siswa') {
                Attendance::where('student_id', $personId)->where('date', $date)->delete();
            } else {
                EmployeeAttendance::where('employee_id', $personId)->where('date', $date)->delete();
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Status presensi direset ke Belum Absen.']);
            }
            return back()->with('success', 'Status presensi direset ke Belum Absen.');
        }

        if ($group === 'siswa') {
            $classroomId = $request->classroom_id;
            if (!$classroomId) {
                $student = Student::with('studentClasses')->find($personId);
                $classroomId = $student?->studentClasses?->firstWhere('status', 'aktif')?->classroom_id;
            }
            $classroom = $classroomId ? Classroom::find($classroomId) : null;
            $isToday = ($date === date('Y-m-d'));
            $currentTime = now()->format('H:i:s');

            $timeIn = $timeIn ?: ($isToday && in_array($status, ['hadir', 'terlambat']) ? $currentTime : ($classroom?->entry_time ? $classroom->entry_time . ':00' : '07:30:00'));

            // KETENTUAN: Bila melebihi toleransi keterlambatan, status otomatis menjadi 'terlambat'
            // KECUALI siswa yang sedang aktif PKL
            if ($status === 'hadir') {
                $isStudentPkl = \App\Models\PklPlacement::where('student_id', $personId)
                    ->whereIn('status', ['active', 'aktif', 'approved', 'ongoing', 'berjalan'])
                    ->where(function($q) use ($date) {
                        $q->whereNull('start_date')->orWhereDate('start_date', '<=', $date);
                    })
                    ->where(function($q) use ($date) {
                        $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
                    })
                    ->exists();

                if (!$isStudentPkl) {
                    $checkTime = $timeIn ?: ($isToday ? $currentTime : null);
                    if ($checkTime && $classroom && $classroom->isLate($checkTime)) {
                        $status = 'terlambat';
                    }
                }
            }

            Attendance::updateOrCreate(
                [
                    'student_id' => $personId,
                    'date'       => $date,
                ],
                [
                    'classroom_id' => $classroomId,
                    'status'       => $status,
                    'time_in'      => in_array($status, ['hadir', 'terlambat']) ? $timeIn : null,
                    'time_out'     => in_array($status, ['hadir', 'terlambat']) ? $timeOut : null,
                    'notes'        => $notes,
                    'recorded_via' => 'manual',
                    'created_by'   => $userId,
                ]
            );
        } else {
            // Guru & Pegawai
            EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $personId,
                    'date'        => $date,
                ],
                [
                    'school_id'    => $schoolId,
                    'status'       => $status,
                    'time_in'      => in_array($status, ['hadir', 'terlambat', 'dinas_luar']) ? ($timeIn ?: '07:15') : null,
                    'time_out'     => in_array($status, ['hadir', 'terlambat', 'dinas_luar']) ? $timeOut : null,
                    'notes'        => $notes,
                    'recorded_via' => 'manual',
                    'recorded_by'  => $userId,
                ]
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data presensi berhasil disimpan / diperbarui.']);
        }

        return back()->with('success', 'Data presensi berhasil disimpan / diperbarui.');
    }

    /**
     * Delete single attendance
     */
    public function destroy(Request $request, $id)
    {
        $group = $request->get('group', 'siswa');

        if ($group === 'siswa') {
            $att = Attendance::find($id);
            if ($att) $att->delete();
        } else {
            $att = EmployeeAttendance::find($id);
            if ($att) $att->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data presensi berhasil dihapus.']);
        }

        return back()->with('success', 'Data presensi berhasil dihapus.');
    }
}
