<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\AttendanceRepository;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceRepository $attendanceRepository
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $activeAcademicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $academicYears = \App\Models\AcademicYear::orderBy('year', 'desc')->get();
        $selectedAcademicYearId = $request->get('academic_year_id', $activeAcademicYear->id ?? null);

        $filters = [
            'date' => $request->get('date', \Carbon\Carbon::now('Asia/Jakarta')->toDateString()),
            'classroom_id' => $request->get('classroom_id'),
            'school_id' => $isSuperAdmin ? $request->get('school_id') : $user->school_id,
            'status' => $request->get('status'),
            'academic_year_id' => $selectedAcademicYearId,
            'exclude_manual' => $request->get('live') === '1',
        ];

        $attendances = $this->attendanceRepository->getPaginated($filters);
        
        $classrooms = Classroom::when(!$isSuperAdmin, function($q) use ($user) {
            return $q->where('school_id', $user->school_id);
        })
        ->when($selectedAcademicYearId, function($q) use ($selectedAcademicYearId) {
            return $q->where('academic_year_id', $selectedAcademicYearId);
        })
        ->orderBy('class_name')->get();

        $schools = $isSuperAdmin ? \App\Models\School::where('is_active', true)->schoolsOnly()->get() : collect();

        if ($request->ajax()) {
            return view('admin.attendances._table', compact('attendances', 'filters', 'isSuperAdmin'));
        }

        return view('admin.attendances.index', compact(
            'attendances', 'filters', 'classrooms', 'schools', 'isSuperAdmin',
            'academicYears', 'activeAcademicYear', 'selectedAcademicYearId'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $schools = $isSuperAdmin ? \App\Models\School::where('is_active', true)->schoolsOnly()->get() : collect();
        $activeAY = \App\Models\AcademicYear::where('is_active', true)->first();

        // Ambil hanya siswa aktif terdaftar pada Tahun Pelajaran aktif
        $students = Student::when(!$isSuperAdmin, function($q) use ($user) {
                return $q->where('school_id', $user->school_id);
            })
            ->whereHas('studentClasses', function ($q) use ($activeAY) {
                $q->where('status', 'aktif')
                  ->when($activeAY, function ($q) use ($activeAY) {
                      $q->where('academic_year_id', $activeAY->id);
                  });
            })
            ->with(['classrooms' => function($q) use ($activeAY) {
                $q->when($activeAY, function ($q) use ($activeAY) {
                    $q->where('classrooms.academic_year_id', $activeAY->id);
                });
            }])
            ->get();

        $classrooms = Classroom::when(!$isSuperAdmin, function($q) use ($user) {
            return $q->where('school_id', $user->school_id);
        })
        ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
        ->orderBy('class_name')->get();

        return view('admin.attendances.create', compact('students', 'classrooms', 'schools', 'isSuperAdmin'));
    }

    public function checkExistence(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'date' => 'required|date',
        ]);

        $attendance = Attendance::where('student_id', $request->student_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($attendance) {
            return response()->json([
                'exists' => true,
                'data' => [
                    'time_in' => $attendance->time_in ? substr($attendance->time_in, 0, 5) : null,
                    'time_out' => $attendance->time_out ? substr($attendance->time_out, 0, 5) : null,
                    'status' => $attendance->status,
                    'notes' => $attendance->notes,
                ]
            ]);
        }

        return response()->json([
            'exists' => false
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'time_in' => 'nullable|string',
            'time_out' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $data = [
                'status' => $validated['status'],
                'time_in' => in_array($validated['status'], ['hadir', 'terlambat']) ? ($validated['time_in'] ?? null) : null,
                'time_out' => in_array($validated['status'], ['hadir', 'terlambat']) ? ($validated['time_out'] ?? null) : null,
                'notes' => $validated['notes'] ?? null,
                'recorded_via' => 'manual',
                'created_by' => auth()->id(),
            ];

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('attendance_attachments', 'public');
                $data['attachment'] = $path;
                $data['attachment_name'] = $file->getClientOriginalName();
            }

            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $validated['student_id'],
                    'classroom_id' => $validated['classroom_id'],
                    'date' => $validated['date'],
                ],
                $data
            );

            // Reputation Hook for Student
            $student = \App\Models\Student::find($validated['student_id']);
            if ($student && $student->user_id) {
                $points = match($validated['status']) {
                    'hadir' => 10,
                    'alpha' => -10,
                    default => 0
                };
                $classroom = \App\Models\Classroom::find($validated['classroom_id']);
                $classroomName = $classroom ? $classroom->class_name : 'Kelas';
                $desc = "Kehadiran di kelas " . $classroomName . " (" . ucfirst($validated['status']) . ")";
                \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
            }

            return redirect()->route('admin.attendances.index')
                ->with('success', 'Absensi berhasil diproses.');
        } catch (\Exception $e) {
            Log::error('Gagal memproses absensi: ' . $e->getMessage());
            return back()->withErrors(['attendance' => 'Gagal memproses absensi. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function edit(Attendance $attendance)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $schools = $isSuperAdmin ? \App\Models\School::where('is_active', true)->schoolsOnly()->get() : collect();
        $activeAY = \App\Models\AcademicYear::where('is_active', true)->first();

        // Ambil hanya siswa aktif terdaftar pada Tahun Pelajaran aktif
        $students = Student::when(!$isSuperAdmin, function($q) use ($user) {
                return $q->where('school_id', $user->school_id);
            })
            ->whereHas('studentClasses', function ($q) use ($activeAY) {
                $q->where('status', 'aktif')
                  ->when($activeAY, function ($q) use ($activeAY) {
                      $q->where('academic_year_id', $activeAY->id);
                  });
            })
            ->with(['classrooms' => function($q) use ($activeAY) {
                $q->when($activeAY, function ($q) use ($activeAY) {
                    $q->where('classrooms.academic_year_id', $activeAY->id);
                });
            }])
            ->get();

        $classrooms = Classroom::when(!$isSuperAdmin, function($q) use ($user) {
            return $q->where('school_id', $user->school_id);
        })
        ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
        ->orderBy('class_name')->get();

        return view('admin.attendances.edit', compact('attendance', 'students', 'classrooms', 'schools', 'isSuperAdmin'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'time_in' => 'nullable|string',
            'time_out' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $data = [
                'status' => $validated['status'],
                'time_in' => in_array($validated['status'], ['hadir', 'terlambat']) ? ($validated['time_in'] ?? null) : null,
                'time_out' => in_array($validated['status'], ['hadir', 'terlambat']) ? ($validated['time_out'] ?? null) : null,
                'notes' => $validated['notes'] ?? null,
            ];

            if ($request->hasFile('attachment')) {
                // Hapus lampiran lama jika ada
                if ($attendance->attachment) {
                    \Storage::disk('public')->delete($attendance->attachment);
                }

                $file = $request->file('attachment');
                $path = $file->store('attendance_attachments', 'public');
                $data['attachment'] = $path;
                $data['attachment_name'] = $file->getClientOriginalName();
            }

            $attendance->update($data);

            // Reputation Hook for Student
            $student = $attendance->student;
            if ($student && $student->user_id) {
                $points = match($validated['status']) {
                    'hadir' => 10,
                    'alpha' => -10,
                    default => 0
                };
                $classroomName = $attendance->classroom ? $attendance->classroom->class_name : 'Kelas';
                $desc = "Kehadiran di kelas " . $classroomName . " (" . ucfirst($validated['status']) . ")";
                \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
            }

            return redirect()->route('admin.attendances.index')
                ->with('success', 'Absensi berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui absensi: ' . $e->getMessage());
            return back()->withErrors(['attendance' => 'Gagal memperbarui absensi.'])
                ->withInput();
        }
    }

    public function destroy(Attendance $attendance)
    {
        try {
            $studentUserId = $attendance->student ? $attendance->student->user_id : null;
            $attendanceId = $attendance->id;

            $this->attendanceRepository->delete($attendance);

            if ($studentUserId) {
                \App\Models\ReputationLog::removeLog($studentUserId, get_class($attendance), $attendanceId);
            }

            return redirect()->route('admin.attendances.index')
                ->with('success', 'Absensi berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus absensi: ' . $e->getMessage());
            return back()->withErrors(['attendance' => 'Gagal menghapus absensi. Silakan coba lagi.']);
        }
    }
    /**
     * Show bulk input form for class attendance
     */
    public function bulk(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $selectedSchoolId = $request->input('school_id');
        $selectedDate = $request->input('date', \Carbon\Carbon::now('Asia/Jakarta')->toDateString());

        $schools = $isSuperAdmin ? \App\Models\School::where('is_active', true)->schoolsOnly()->get() : [];

        $activeAY = \App\Models\AcademicYear::where('is_active', true)->first();
        $classrooms = Classroom::orderBy('class_name')
            ->when(!$isSuperAdmin, function($q) use ($user) {
                return $q->where('school_id', $user->school_id);
            })
            ->when($isSuperAdmin && $selectedSchoolId, function($q) use ($selectedSchoolId) {
                return $q->where('school_id', $selectedSchoolId);
            })
            ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
            ->with('school')
            ->get();

        $selectedClassroom = $request->input('classroom_id');
        $students = collect();
        $existingAttendances = collect();
        $classroom = null;

        if ($selectedClassroom) {
            $classroom = Classroom::with('school')->find($selectedClassroom);
            if ($classroom) {
                $students = $classroom->students()
                    ->where('student_classes.status', 'aktif')
                    ->when($activeAY, fn($q) => $q->where('student_classes.academic_year_id', $activeAY->id))
                    ->orderBy('full_name')
                    ->get();

                $existingAttendances = Attendance::where('classroom_id', $selectedClassroom)
                    ->whereDate('date', $selectedDate)
                    ->get()
                    ->keyBy('student_id');
            }
        }
        return view('admin.attendances.bulk', [
            'schools' => $schools,
            'classrooms' => $classrooms,
            'selectedSchoolId' => $selectedSchoolId,
            'selectedClassroom' => $selectedClassroom,
            'selectedDate' => $selectedDate,
            'classroom' => $classroom,
            'students' => $students,
            'existingAttendances' => $existingAttendances,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    /**
     * Store bulk attendance for a class
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'classroom_id' => 'required|exists:classrooms,id',
            'statuses' => 'required|array',
            'statuses.*' => 'required|in:hadir,terlambat,izin,sakit,alpha',
        ]);
        $classroom = Classroom::findOrFail($request->classroom_id);
        $date = $request->date;
        $count = 0;

        foreach ($request->statuses as $studentId => $status) {
            $note = $request->notes[$studentId] ?? null;

            $existing = \App\Models\Attendance::where('student_id', $studentId)
                ->where('classroom_id', $classroom->id)
                ->whereDate('date', $date)
                ->first();

            $updateData = [
                'status' => $status,
                'notes' => $note,
            ];

            if (!$existing) {
                $updateData['recorded_via'] = 'manual';
                $updateData['created_by'] = auth()->id();
            }

            $attendance = \App\Models\Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'classroom_id' => $classroom->id,
                    'date' => $date,
                ],
                $updateData
            );

            // Reputation Hook (Maksimal 1x per tanggal)
            $student = \App\Models\Student::find($studentId);
            if ($student && $student->user_id) {
                $points = match($status) {
                    'hadir' => 10,
                    'alpha' => -10,
                    default => 0
                };
                $desc = "Kehadiran di kelas " . $classroom->class_name . " (" . ucfirst($status) . ")";
                
                $alreadyLoggedOther = \App\Models\ReputationLog::where('user_id', $student->user_id)
                    ->where('category', 'attendance')
                    ->whereDate('created_at', $date)
                    ->where(function($q) use ($attendance) {
                        $q->where('reference_type', '!=', get_class($attendance))
                          ->orWhere('reference_id', '!=', $attendance->id);
                    })
                    ->exists();

                if (!$alreadyLoggedOther) {
                    \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
                }
            }
            $count++;
        }

        return redirect()->route('admin.attendances.index', [
            'classroom_id' => $classroom->id,
            'date' => $date,
        ])->with('success', "Absensi kelas {$classroom->class_name} ({$count} siswa) berhasil disimpan.");
    }
    /**
     * Show mass update form for handling holidays / sending students home
     */
    public function massUpdate(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        
        if ($isSuperAdmin) {
            $schools = \App\Models\School::where('is_active', true)->schoolsOnly()->get();
        } else {
            $schools = \App\Models\School::where('id', $user->school_id)->get();
        }

        $activeAY = \App\Models\AcademicYear::where('is_active', true)->first();
        $classrooms = Classroom::orderBy('class_name')
            ->when(!$isSuperAdmin, function($q) use ($user) {
                return $q->where('school_id', $user->school_id);
            })
            ->when($activeAY, fn($q) => $q->where('academic_year_id', $activeAY->id))
            ->get();

        return view('admin.attendances.mass-update', compact('schools', 'classrooms', 'isSuperAdmin'));
    }

    /**
     * Store mass update
     */
    public function massUpdateStore(Request $request)
    {
        set_time_limit(300); // Allow up to 5 minutes
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:hadir,izin,sakit,alpha,libur,pulang,reset',
            'target_type' => 'required|in:school,classroom',
            'school_id' => $isSuperAdmin && $request->target_type == 'school' ? 'required|exists:schools,id' : 'nullable',
            'classroom_id' => 'required_if:target_type,classroom|nullable|exists:classrooms,id',
            'notes' => 'nullable|string',
        ]);

        $activeAY = \App\Models\AcademicYear::where('is_active', true)->first();
        $query = \App\Models\Student::whereHas('studentClasses', function($q) use ($activeAY) {
            $q->where('status', 'aktif')
              ->when($activeAY, function($q) use ($activeAY) {
                  $q->where('academic_year_id', $activeAY->id);
              });
        });

        if ($request->target_type === 'school') {
            $schoolTarget = $isSuperAdmin ? $request->school_id : $user->school_id;
            $query->where('school_id', $schoolTarget);
        } else {
            $classroomId = $request->classroom_id;
            if (!$isSuperAdmin) {
                \App\Models\Classroom::where('id', $classroomId)->where('school_id', $user->school_id)->firstOrFail();
            }
            $query->whereHas('studentClasses', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId)
                  ->where('status', 'aktif');
            });
        }

        $students = $query->with(['studentClasses' => function($q) {
            $q->where('status', 'aktif');
        }])->get();
        
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $excludeWeekends = $request->has('exclude_weekends');
        
        $status = $request->status;
        $notes = $request->notes;

        $datesToProcess = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($excludeWeekends && $date->isWeekend()) {
                continue;
            }
            $datesToProcess[] = $date->format('Y-m-d');
        }

        if (empty($datesToProcess)) {
            return back()->with('success', 'Tidak ada data dieksekusi karena semua tanggal yang dipilih adalah aktif.');
        }

        $studentIds = $students->pluck('id')->toArray();

        $datesToProcessWithTime = [];
        foreach ($datesToProcess as $dateStr) {
            $datesToProcessWithTime[] = $dateStr;
            $datesToProcessWithTime[] = $dateStr . ' 00:00:00';
        }

        // 1. Delete existing records for target students and dates where schedule_id is NULL
        // This prevents duplicates if the mass update is run multiple times
        foreach (array_chunk($studentIds, 200) as $idChunk) {
            $attendances = \App\Models\Attendance::whereIn('student_id', $idChunk)
                ->whereIn('date', $datesToProcessWithTime)
                ->whereNull('schedule_id')
                ->with('student:id,user_id')
                ->get();

            foreach ($attendances as $attendance) {
                if ($attendance->student && $attendance->student->user_id) {
                    \App\Models\ReputationLog::removeLog($attendance->student->user_id, get_class($attendance), $attendance->id);
                }
                $attendance->delete();
            }
        }

        if ($status !== 'reset') {
            // 2. Prepare and Insert data in chunks
            $insertData = [];
            foreach ($students as $student) {
                $classroom = $student->studentClasses->first();
                if ($classroom) {
                    foreach ($datesToProcess as $processDate) {
                        $insertData[] = [
                            'student_id' => $student->id,
                            'classroom_id' => $classroom->classroom_id,
                            'date' => $processDate,
                            'status' => $status,
                            'notes' => $notes,
                            'recorded_via' => 'manual',
                        ];

                        if (count($insertData) >= 500) {
                            \App\Models\Attendance::insert($insertData);
                            $insertData = [];
                        }
                    }
                }
            }

            if (!empty($insertData)) {
                \App\Models\Attendance::insert($insertData);
            }
        }

        $msg = $status === 'reset' ? 'Absensi berhasil direset/dihapus' : 'Mass update absensi berhasil diproses';
        return redirect()->route('admin.attendances.index')->with('success', "$msg untuk {$students->count()} siswa selama " . count($datesToProcess) . " hari aktif.");
    }

    /**
     * Attendance Monitoring Dashboard
     */
    public function monitoring(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $schoolId = $request->input('school_id', $isSuperAdmin ? null : $user->school_id);
        
        $date = $request->input('date', \Carbon\Carbon::now('Asia/Jakarta')->toDateString());
        
        $ay = null;
        if ($schoolId) {
            $school = \App\Models\School::find($schoolId);
            $ay = $school?->currentAcademicYear();
        }
        if (!$ay) {
            $ay = \App\Models\AcademicYear::where('is_active', true)->first();
        }

        $startDateOfYear = $ay ? $ay->start_date : \Carbon\Carbon::parse($date)->startOfYear();
        $endDateOfAnalysis = \Carbon\Carbon::parse($date);
        if ($ay && $ay->start_date->gt($endDateOfAnalysis)) {
            $startDateOfYear = $endDateOfAnalysis;
        }
        
        $statisticsService = new \App\Services\AttendanceStatisticsService();
        $totalZ = $statisticsService->calculateZ($startDateOfYear, $endDateOfAnalysis, null, $schoolId);

        // Stats for the selected date (Daily snapshot) - filtered by active academic year and active student status
        $dailyStatsQuery = Attendance::where('date', $date)
            ->whereIn('recorded_via', ['rfid', 'qr_gps', 'face_recognition'])
            ->whereHas('student', function ($q) use ($ay) {
                $q->whereHas('studentClasses', function ($sc) use ($ay) {
                    $sc->where('status', 'aktif')
                       ->when($ay, fn($sq) => $sq->where('academic_year_id', $ay->id));
                });
            });
        if ($schoolId) {
            $dailyStatsQuery->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
        }

        $dailyStats = [
            'hadir' => (clone $dailyStatsQuery)->where('status', 'hadir')->count(),
            'terlambat' => (clone $dailyStatsQuery)->where('status', 'terlambat')->count(),
            'izin' => (clone $dailyStatsQuery)->where('status', 'izin')->count(),
            'sakit' => (clone $dailyStatsQuery)->where('status', 'sakit')->count(),
            'alpha' => (clone $dailyStatsQuery)->where('status', 'alpha')->count(),
        ];
        $dailyStats['total_daily'] = array_sum($dailyStats);

        // Cumulative Stats (Summary) - filtered by active academic year and active student status
        $cumulativeStatsQuery = Attendance::whereBetween('date', [$startDateOfYear->format('Y-m-d'), $endDateOfAnalysis->format('Y-m-d')])
            ->whereIn('recorded_via', ['rfid', 'qr_gps', 'face_recognition'])
            ->whereHas('student', function ($q) use ($ay) {
                $q->whereHas('studentClasses', function ($sc) use ($ay) {
                    $sc->where('status', 'aktif')
                       ->when($ay, fn($sq) => $sq->where('academic_year_id', $ay->id));
                });
            });
        if ($schoolId) {
            $cumulativeStatsQuery->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
        }

        $cumulativeStats = [
            'hadir' => (clone $cumulativeStatsQuery)->where('status', 'hadir')->count(),
            'terlambat' => (clone $cumulativeStatsQuery)->where('status', 'terlambat')->count(),
            'izin' => (clone $cumulativeStatsQuery)->where('status', 'izin')->count(),
            'sakit' => (clone $cumulativeStatsQuery)->where('status', 'sakit')->count(),
            'alpha' => (clone $cumulativeStatsQuery)->where('status', 'alpha')->count(),
            'z' => $totalZ 
        ];
        $cumulativeStats['total_count'] = $cumulativeStats['hadir'] + $cumulativeStats['terlambat'] + $cumulativeStats['izin'] + $cumulativeStats['sakit'] + $cumulativeStats['alpha'];

        // Weekly/Monthly trend
        $startDateTrend = $request->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDateTrend = $request->input('end_date', date('Y-m-d'));
        
        $trendData = Attendance::whereBetween('date', [$startDateTrend, $endDateTrend])
            ->whereIn('recorded_via', ['rfid', 'qr_gps', 'face_recognition'])
            ->selectRaw('date, status, count(*) as count')
            ->when($schoolId, function($q) use ($schoolId) {
                return $q->whereHas('student', fn($sq) => $sq->where('school_id', $schoolId));
            })
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        $chartData = [];
        foreach ($trendData as $item) {
            $formattedDate = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            $chartData[$formattedDate][$item->status] = $item->count;
        }

        // Per classroom statistics (Rank by Cumulative Presence Rate) - filtered by academic year
        $classroomStats = Classroom::with('school')->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when($ay, fn($q) => $q->where('academic_year_id', $ay->id))
            ->withCount(['students' => function($q) use ($ay) {
                $q->where('student_classes.status', 'aktif')
                  ->when($ay, fn($sq) => $sq->where('student_classes.academic_year_id', $ay->id));
            }])
            ->get()
            ->map(function($classroom) use ($startDateOfYear, $endDateOfAnalysis, $statisticsService) {
                $z = $statisticsService->calculateZ($startDateOfYear, $endDateOfAnalysis, $classroom->id);
                
                $totalHadir = Attendance::where('classroom_id', $classroom->id)
                    ->whereBetween('date', [$startDateOfYear->format('Y-m-d'), $endDateOfAnalysis->format('Y-m-d')])
                    ->whereIn('recorded_via', ['rfid', 'qr_gps', 'face_recognition'])
                    ->where('status', 'hadir')
                    ->count();

                $classroom->z_days = $z;
                $classroom->total_hadir = $totalHadir;
                $classroom->presence_rate = ($classroom->students_count > 0 && $z > 0)
                    ? round(($totalHadir / ($classroom->students_count * $z)) * 100, 1)
                    : 0;
                
                // Add daily quick info for current selected date too
                $classroom->daily_present = Attendance::where('classroom_id', $classroom->id)
                    ->where('date', $endDateOfAnalysis->format('Y-m-d'))
                    ->whereIn('recorded_via', ['rfid', 'qr_gps', 'face_recognition'])
                    ->where('status', 'hadir')
                    ->count();

                return $classroom;
            })
            ->sortByDesc('presence_rate') // Show best first
            ->values(); // Reset keys for ranking in view

        $schools = $isSuperAdmin ? \App\Models\School::where('is_active', true)->schoolsOnly()->get() : [];

        if ($request->input('json') === '1') {
            return response()->json([
                'dailyStats' => $dailyStats,
                'cumulativeStats' => $cumulativeStats,
                'chartData' => $chartData,
                'classroomStats' => $classroomStats
            ]);
        }

        return view('admin.attendances.monitoring', compact(
            'dailyStats', 
            'cumulativeStats',
            'chartData', 
            'classroomStats', 
            'date', 
            'startDateTrend', 
            'endDateTrend', 
            'schoolId', 
            'isSuperAdmin',
            'schools'
        ));
    }
}
