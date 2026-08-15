<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentCounselingRecord;
use App\Models\StudentDevelopmentNote;
use App\Models\StudentRecommendation;
use App\Models\CounselingParticipant;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;

class StudentCounselingController extends Controller
{
    /**
     * List counseling records (filterable)
     */
    public function index(Request $request)
    {
        $query = StudentCounselingRecord::with(['student.currentClassroom', 'counselor', 'school']);

        // Stats for Dashboard
        $stats = [
            'total_achievement' => StudentCounselingRecord::where('record_type', 'penghargaan')->count(),
            'total_cases' => StudentCounselingRecord::where('record_type', '!=', 'penghargaan')->count(),
            'star_students' => User::whereHas('student')
                ->with(['student.currentClassroom', 'reputation', 'reputationLogs' => function($q) {
                    $q->orderByDesc('created_at')->take(5);
                }])
                ->whereHas('reputation', fn($q) => $q->where('total_points', '>', 0))
                ->orderByDesc(function($q) {
                    $q->select('total_points')->from('reputations')->whereColumn('reputations.user_id', 'users.id')->limit(1);
                })
                ->take(3)
                ->get(),
            'priority_students' => StudentCounselingRecord::where('severity', 'berat')
                ->where('status', '!=', 'selesai')
                ->with(['student.currentClassroom'])
                ->latest()
                ->take(3)
                ->get()
        ];

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('report_mode')) {
            if ($request->report_mode === 'prestasi') {
                $query->where('record_type', 'penghargaan');
            } elseif ($request->report_mode === 'masalah') {
                $query->where('record_type', '!=', 'penghargaan');
            }
        }
        
        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('full_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('achievement_level')) {
            $query->where('achievement_level', $request->achievement_level);
        }

        $records = $query->orderByDesc('incident_date')->paginate(20)->withQueryString();

        return view('admin.counseling.index', compact('records', 'stats'));
    }

    /**
     * Helper: Get common form data for create views
     */
    private function getFormData(Request $request)
    {
        $user = auth()->user();
        $schoolId = $request->get('school_id');

        // Logic Siswa: Filter by School
        // Update: Allow 'calon' status as well per user feedback (Siti Aminah case)
        $studentQuery = Student::whereIn('status', ['aktif', 'calon'])
            ->with(['currentClassroom.homeroomTeacher.user', 'school'])
            ->orderBy('full_name');

        if ($user->role !== 'superadmin') {
            $studentQuery->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $studentQuery->where('school_id', $schoolId);
        }

        $students = $studentQuery->get();
            
        $academicYear = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();
        
        // Logic Konselor: Ambil User dengan role guru/admin_sekolah yang sesuai sekolah
        $counselorQuery = User::whereIn('role', ['guru', 'admin_sekolah']);
        
        if ($user->role !== 'superadmin') {
            $counselorQuery->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $counselorQuery->where('school_id', $schoolId);
        }
        
        // Selalu sertakan Superadmin dan user yang sedang login agar tidak error jika dia sendiri yg input
        $counselorQuery->orWhere(function($q) {
            $q->where('role', 'superadmin')
              ->orWhere('id', auth()->id());
        });

        $counselors = $counselorQuery->with(['teacher'])
            ->get()
            ->unique('id')
            ->filter(function($u) {
                return !empty($u->name) && $u->name !== 'Guru';
            })
            ->map(function($u) {
                $name = $u->teacher ? $u->teacher->full_name : $u->name;
                $roleLabel = ucfirst(str_replace('_', ' ', $u->role));
                
                $schoolInfo = '';
                if (auth()->user()->role === 'superadmin' && !request('school_id') && $u->school) {
                    $schoolInfo = " - {$u->school->name}";
                }
                
                $u->display_name = "{$name} ({$roleLabel}{$schoolInfo})";
                return $u;
            })
            ->sortBy('display_name');

        $schools = \App\Models\School::where('is_active', true)->orderBy('name')->get();
        $preselectedStudent = $request->filled('student_id') ? Student::find($request->student_id) : null;
        
        // Cukup kirim preselected student jika ada, atau empty collection jika tidak
        // Data siswa akan dimuat via AJAX
        $students = $preselectedStudent ? collect([$preselectedStudent]) : collect();

        return compact('students', 'academicYear', 'semester', 'preselectedStudent', 'counselors', 'schools');
    }

    /**
     * AJAX: Search students for select2
     */
    public function searchStudents(Request $request)
    {
        $search = $request->get('q');
        $user = auth()->user();
        $schoolId = $request->get('school_id');

        $query = Student::whereIn('status', ['aktif', 'calon'])
            ->with(['currentClassroom.homeroomTeacher.user', 'school']);

        if ($user->role !== 'superadmin') {
            $query->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($search) {
            $query->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
        }

        $students = $query->orderBy('full_name')->take(20)->get();

        // Get all counselors for these schools to determine BK
        $counselorQuery = User::whereIn('role', ['guru', 'admin_sekolah']);
        if ($user->role !== 'superadmin') {
            $counselorQuery->where('school_id', $user->school_id);
        }
        $counselors = $counselorQuery->get();

        $results = $students->map(function ($s) use ($counselors) {
            // Determine Wali Kelas
            $homeroom = $s->currentClassroom->first()?->homeroomTeacher;
            $homeroomUserId = $homeroom?->user_id ?? '';
            $homeroomName = $homeroom ? ($homeroom->teacher?->full_name ?? $homeroom->name) : 'Wali Kelas Belum Ditentukan';

            // Determine BK
            $bk = $counselors->where('school_id', $s->school_id)->filter(function($u) {
                return $u->role === 'guru_bk' || $u->role === 'pks' || $u->hasSpecialDuty(['BK', 'KESISWAAN', 'PKS', 'BKK']);
            })->first() ?? $counselors->where('school_id', $s->school_id)->first();
            $bkId = $bk?->id ?? '';
            $bkName = $bk ? $bk->display_name : 'Tim PKS / BK Sekolah';

            return [
                'id' => $s->id,
                'text' => $s->full_name . ' (' . ($s->currentClassroom->first()?->name ?? 'Belum ada kelas') . ')',
                'homeroom_id' => $homeroomUserId,
                'homeroom_name' => $homeroomName,
                'bk_id' => $bkId,
                'bk_name' => $bkName
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Create form (legacy — redirect to pilih mode)
     */
    public function create(Request $request)
    {
        $data = $this->getFormData($request);
        return view('admin.counseling.create', $data);
    }

    /**
     * Create form — Mode Prestasi
     */
    public function createPrestasi(Request $request)
    {
        $data = $this->getFormData($request);
        return view('admin.counseling.create-prestasi', $data);
    }

    /**
     * Create form — Mode Pembinaan
     */
    public function createPembinaan(Request $request)
    {
        $data = $this->getFormData($request);
        return view('admin.counseling.create-pembinaan', $data);
    }

    /**
     * Store counseling record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'counselor_id' => 'nullable|exists:users,id',
            'record_type' => 'required|in:konseling,pembinaan,pelanggaran,penghargaan,home_visit',
            'category' => 'required|in:akademik,perilaku,sosial,karir,pribadi,lainnya,kedisiplinan,absensi,olahraga,seni,keagamaan',
            'severity' => 'nullable|in:ringan,sedang,berat',
            'achievement_level' => 'nullable|in:sekolah,kabupaten,propinsi,nasional,internasional',
            // Field baru Prestasi
            'competition_name' => 'nullable|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'ranking' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'background' => 'nullable|string',
            'action_taken' => 'nullable|string',
            // Field baru Pembinaan
            'sanction' => 'nullable|string',
            'sanction_type' => 'nullable|string|max:100',
            'sanction_duration_days' => 'nullable|integer|min:1',
            'result' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'incident_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'parent_notified' => 'boolean',
            'parent_notified_date' => 'nullable|date',
            'parent_response' => 'nullable|string',
            'status' => 'nullable|in:open,in_progress,resolved,closed',
            'is_confidential' => 'boolean',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'nullable|exists:users,id',
            'participants.*.role' => 'in:guru_bk,wali_kelas,pks,kepala_sekolah,guru_mapel,orang_tua,lainnya',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('counseling_attachments', 'public');
            $validated['attachment'] = $path;
            $validated['attachment_name'] = $file->getClientOriginalName();
        }

        $student = Student::findOrFail($validated['student_id']);
        $academicYear = AcademicYear::where('is_active', true)->firstOrFail();
        $semester = Semester::where('is_active', true)->firstOrFail();

        // Tentukan status berdasarkan tipe
        $isPrestasi = $validated['record_type'] === 'penghargaan';
        $status = $isPrestasi ? 'resolved' : ($validated['status'] ?? 'open');

        $record = StudentCounselingRecord::create(array_merge($validated, [
            'school_id' => $student->school_id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'counselor_id' => $request->filled('counselor_id') ? $request->counselor_id : auth()->id(),
            'status' => $status,
            'resolved_date' => $isPrestasi ? now() : null,
        ]));

        // Add participants
        if (!empty($validated['participants'])) {
            foreach ($validated['participants'] as $participant) {
                if (!empty($participant['user_id'])) {
                    CounselingParticipant::create([
                        'counseling_record_id' => $record->id,
                        'user_id' => $participant['user_id'],
                        'role' => $participant['role'] ?? 'lainnya',
                        'notes' => $participant['notes'] ?? null,
                    ]);
                }
            }
        }

        // Reputation Hooks
        try {
            if ($student->user_id) {
                $points = 0; // Default: netral untuk konseling/pembinaan biasa
                $type = 'character';
                
                if ($record->record_type === 'penghargaan') {
                    // Prestasi: poin POSITIF berdasarkan level (Skala Baru: 100 - 500 Poin)
                    $levels = [
                        'sekolah' => 100,
                        'kabupaten' => 200,
                        'propinsi' => 300,
                        'nasional' => 400,
                        'internasional' => 500
                    ];
                    $points = $levels[$record->achievement_level] ?? 100;
                    $type = 'achievement';
                } elseif ($record->record_type === 'pelanggaran') {
                    // Pelanggaran: poin NEGATIF berdasarkan severity
                    $severities = [
                        'ringan' => -20,
                        'sedang' => -50,
                        'berat' => -100
                    ];
                    $points = $severities[$record->severity] ?? -20;
                    $type = 'violation';
                }
                // konseling, pembinaan, home_visit = 0 poin (netral, hanya dokumentasi)

                if ($points !== 0) {
                    \App\Models\ReputationLog::log($student->user_id, $points, $type, $record->title, $record);
                }
            }

            // Poin untuk Counselor/Pencatat (dokumentasi)
            $counselorId = $record->counselor_id ?? auth()->id();
            if ($counselorId) {
                $cPoints = 5; // Poin kecil untuk dokumentasi
                if ($record->record_type === 'home_visit') {
                    $cPoints = 25; // Extra untuk home visit (effort lebih)
                }
                
                \App\Models\ReputationLog::log($counselorId, $cPoints, 'counseling_action', "Dokumentasi: " . $record->title, $record);
            }
        } catch (\Exception $e) {
            \Log::warning('Reputation logging failed for counseling: ' . $e->getMessage());
        }

        $successMsg = $isPrestasi 
            ? 'Prestasi siswa berhasil dicatat! Poin reputasi telah ditambahkan.'
            : 'Catatan pembinaan berhasil disimpan.';

        return redirect()->route('admin.counseling.show', $record)
            ->with('success', $successMsg);
    }

    /**
     * Process action (Status Change)
     */
    public function processAction(Request $request, StudentCounselingRecord $record)
    {
        $validated = $request->validate([
            'action_type' => 'required|in:skorsing,dikeluarkan,keluar,pindah',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1', // For suspension
        ]);

        $student = $record->student;
        $newStatus = $validated['action_type'];

        // Check if transition is valid
        if (!\App\Models\StudentStatusHistory::isValidTransition($student->status, $newStatus)) {
            return back()->with('error', "Status tidak dapat diubah dari '{$student->status}' ke '{$newStatus}'.");
        }

        try {
            \DB::transaction(function () use ($student, $newStatus, $validated, $record) {
                // 1. Transition Student Status
                $notes = $validated['notes'];
                if ($newStatus === 'skorsing' && !empty($validated['duration_days'])) {
                    $notes .= " (Durasi: {$validated['duration_days']} hari)";
                }

                $student->transitionTo(
                    $newStatus, 
                    $validated['reason'], 
                    $notes, 
                    null, // Document number
                    auth()->id()
                );

                // 2. Update Counseling Record
                $record->update([
                    'status' => 'resolved',
                    'action_taken' => $record->action_taken . "\n\n[SYSTEM] Eksekusi Tindak Lanjut: Status siswa diubah menjadi " . ucfirst($newStatus) . ". Alasan: " . $validated['reason'],
                    'resolved_date' => now(),
                ]);
            });

            return back()->with('success', "Status siswa berhasil diperbarui menjadi " . ucfirst($newStatus) . ".");

        } catch (\Exception $e) {
            return back()->with('error', "Terjadi kesalahan: " . $e->getMessage());
        }
    }



    /**
     * Show detail
     */
    public function show(StudentCounselingRecord $record)
    {
        $record->load(['student', 'counselor', 'participants.user.teacher', 'school']);

        $recommendations = StudentRecommendation::where('counseling_record_id', $record->id)
            ->with('recommendedByUser')
            ->get();

        return view('admin.counseling.show', compact('record', 'recommendations'));
    }

    /**
     * Edit form
     */
    public function edit(StudentCounselingRecord $record)
    {
        $record->load(['participants', 'student']);
        $user = auth()->user();
        $schoolId = $record->school_id;

        $studentQuery = Student::whereIn('status', ['aktif', 'calon'])
            ->with(['currentClassroom.homeroomTeacher.user', 'school'])
            ->orderBy('full_name');

        if ($user->role !== 'superadmin') {
            $studentQuery->where('school_id', $user->school_id);
        }

        $students = $studentQuery->get();
            
        $counselorQuery = User::whereIn('role', ['guru', 'admin_sekolah']);
        if ($user->role !== 'superadmin') {
            $counselorQuery->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $counselorQuery->where('school_id', $schoolId);
        }
        $counselorQuery->orWhere(function($q) {
            $q->where('role', 'superadmin')->orWhere('id', auth()->id());
        });

        $counselors = $counselorQuery->with(['teacher'])
            ->get()->unique('id')
            ->filter(fn($u) => !empty($u->name) && $u->name !== 'Guru')
            ->map(function($u) {
                $name = $u->teacher ? $u->teacher->full_name : $u->name;
                $roleLabel = ucfirst(str_replace('_', ' ', $u->role));
                $u->display_name = "{$name} ({$roleLabel})";
                return $u;
            })->sortBy('display_name');

        $users = $counselors;

        return view('admin.counseling.edit', compact('record', 'students', 'counselors', 'users'));
    }

    /**
     * Update
     */
    public function update(Request $request, StudentCounselingRecord $record)
    {
        $validated = $request->validate([
            'counselor_id' => 'nullable|exists:users,id',
            'record_type' => 'required|in:konseling,pembinaan,pelanggaran,penghargaan,home_visit',
            'category' => 'required|in:akademik,perilaku,sosial,karir,pribadi,lainnya,kedisiplinan,absensi,olahraga,seni,keagamaan',
            'severity' => 'nullable|in:ringan,sedang,berat',
            'achievement_level' => 'nullable|in:sekolah,kabupaten,propinsi,nasional,internasional',
            // Field baru Prestasi
            'competition_name' => 'nullable|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'ranking' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'background' => 'nullable|string',
            'action_taken' => 'nullable|string',
            // Field baru Pembinaan
            'sanction' => 'nullable|string',
            'sanction_type' => 'nullable|string|max:100',
            'sanction_duration_days' => 'nullable|integer|min:1',
            'result' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'status' => 'in:open,in_progress,resolved,closed',
            'parent_notified' => 'boolean',
            'parent_response' => 'nullable|string',
            'is_confidential' => 'boolean',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'nullable|exists:users,id',
            'participants.*.role' => 'in:guru_bk,wali_kelas,pks,kepala_sekolah,guru_mapel,orang_tua,lainnya',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($record->attachment && \Storage::disk('public')->exists($record->attachment)) {
                \Storage::disk('public')->delete($record->attachment);
            }
            
            $file = $request->file('attachment');
            $path = $file->store('counseling_attachments', 'public');
            $validated['attachment'] = $path;
            $validated['attachment_name'] = $file->getClientOriginalName();
        }

        if ($request->filled('counselor_id')) {
            $validated['counselor_id'] = $request->counselor_id;
        }
        $record->update($validated);

        // Sync participants
        if ($request->has('participants')) {
            $record->participants()->delete();
            if (!empty($validated['participants'])) {
                foreach ($validated['participants'] as $participant) {
                    if (!empty($participant['user_id'])) {
                        CounselingParticipant::create([
                            'counseling_record_id' => $record->id,
                            'user_id' => $participant['user_id'],
                            'role' => $participant['role'] ?? 'lainnya',
                            'notes' => $participant['notes'] ?? null,
                        ]);
                    }
                }
            }
        }

        // Reputation Hooks: Update points if record type or level changed
        try {
            $student = $record->student;
            if ($student && $student->user_id) {
                $points = 10;
                $type = 'character';
                
                if ($record->record_type === 'penghargaan') {
                    $levels = ['sekolah' => 100, 'kabupaten' => 200, 'propinsi' => 300, 'nasional' => 400, 'internasional' => 500];
                    $points = $levels[$record->achievement_level] ?? 100;
                    $type = 'academic';
                } elseif ($record->record_type === 'pelanggaran') {
                    $severities = ['ringan' => -20, 'sedang' => -50, 'berat' => -100];
                    $points = $severities[$record->severity] ?? -20;
                    $type = 'violation';
                } elseif ($record->record_type === 'home_visit') {
                    $points = 20;
                }

                \App\Models\ReputationLog::log($student->user_id, $points, $type, $record->title, $record);
            }
        } catch (\Exception $e) {
            \Log::warning('Reputation update failed for counseling update: ' . $e->getMessage());
        }

        return redirect()->route('admin.counseling.show', $record)
            ->with('success', 'Catatan konseling berhasil diperbarui dan poin telah disesuaikan.');
    }

    /**
     * Delete
     */
    public function destroy(StudentCounselingRecord $record)
    {
        // Reputation Rollback: Reverse points for student and counselor
        try {
            if ($record->student && $record->student->user_id) {
                \App\Models\ReputationLog::removeLog($record->student->user_id, get_class($record), $record->id);
            }
            if ($record->counselor_id) {
                \App\Models\ReputationLog::removeLog($record->counselor_id, get_class($record), $record->id);
            }
        } catch (\Exception $e) {
            \Log::warning('Reputation rollback failed for counseling deletion: ' . $e->getMessage());
        }

        $record->delete();
        return redirect()->route('admin.counseling.index')
            ->with('success', 'Catatan konseling berhasil dihapus dan poin telah disesuaikan.');
    }
}

