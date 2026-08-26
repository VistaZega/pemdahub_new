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
use App\Models\FinalProject;
use App\Models\FinalProjectLog;
use App\Models\FinalProjectFormat;
use App\Models\FinalProjectMember;
use App\Models\StudentAchievement;
use App\Models\StudentCounselingRecord;
use App\Models\StudentDevelopmentNote;
use App\Models\StudentRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
     * Jadwal Pelajaran Siswa Mobile (Harian & Roster 1 Minggu)
     */
    public function jadwal()
    {
        $student = $this->getStudent();
        $classroom = $student ? ($student->currentClassroom()->first() ?? $student->classroom ?? $student->classrooms()->latest()->first()) : null;

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu'
        ];

        $today = strtolower(now()->format('l'));
        $activeDay = in_array($today, $days) ? $today : 'monday';
        $currentTime = now()->format('H:i:s');

        $schedulesByDay = [];
        foreach ($days as $day) {
            $schedulesByDay[$day] = collect();
        }

        $currentSchedule = null;
        $nextSchedule = null;
        $totalWeeklyJP = 0;
        $totalWeeklySessions = 0;
        $uniqueSubjectIds = collect();

        $timetable = [];
        $subjectColors = [];
        $timeSlots = collect();
        $activeDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        if ($classroom) {
            $schedules = Schedule::where('classroom_id', $classroom->id)
                ->orWhereHas('teachingAssignment', fn($q) => $q->where('classroom_id', $classroom->id))
                ->with(['subject', 'teacher.user', 'teachingAssignment.subject', 'teachingAssignment.teacher.user', 'timeSlot'])
                ->get()
                ->sortBy(function ($sch) {
                    return $sch->timeSlot->slot_order ?? ($sch->timeSlot->start_time ?? ($sch->start_time ?? '00:00'));
                });

            $totalWeeklySessions = $schedules->count();

            // Check if Saturday has any schedules
            $hasSaturday = $schedules->where('day_of_week', 'saturday')->isNotEmpty();
            if ($hasSaturday) {
                $activeDays[] = 'saturday';
            }

            // Build Subject Color Mapping
            $palettes = ['blue', 'emerald', 'indigo', 'amber', 'rose', 'cyan', 'purple', 'teal', 'orange', 'pink'];
            foreach ($schedules as $sch) {
                $dayKey = strtolower($sch->day_of_week ?? '');
                $duration = (int) ($sch->duration_slots ?: 1);
                $totalWeeklyJP += $duration;

                $subjId = $sch->subject_id ?? ($sch->teachingAssignment->subject_id ?? null);
                if ($subjId) {
                    $uniqueSubjectIds->push($subjId);
                    if (!isset($subjectColors[$subjId])) {
                        $colorIndex = count($subjectColors) % count($palettes);
                        $col = $palettes[$colorIndex];
                        $subjectColors[$subjId] = [
                            'bg' => "bg-{$col}-50",
                            'border' => "border-{$col}-200",
                            'text' => "text-{$col}-900",
                            'sub' => "text-{$col}-600",
                            'badge' => "bg-{$col}-100 text-{$col}-800",
                            'dot' => "bg-{$col}-500",
                        ];
                    }
                }

                // Kalkulasi status waktu (Khusus hari ini)
                $sch->time_status = 'normal';
                $startStr = $sch->timeSlot->start_time ?? $sch->start_time;
                $endStr = $sch->timeSlot->end_time ?? $sch->end_time;

                if ($dayKey === $today && $startStr && $endStr) {
                    $startFormatted = date('H:i:s', strtotime($startStr));
                    $endFormatted = date('H:i:s', strtotime($endStr));

                    if ($currentTime >= $startFormatted && $currentTime <= $endFormatted) {
                        $sch->time_status = 'ongoing';
                        if (!$currentSchedule) {
                            $currentSchedule = $sch;
                        }
                    } elseif ($currentTime < $startFormatted) {
                        $sch->time_status = 'upcoming';
                        if (!$nextSchedule) {
                            $nextSchedule = $sch;
                        }
                    } elseif ($currentTime > $endFormatted) {
                        $sch->time_status = 'completed';
                    }
                }

                if (isset($schedulesByDay[$dayKey])) {
                    $schedulesByDay[$dayKey]->push($sch);
                }
            }

            // Build Matrix Timetable (Baris = Waktu/TimeSlot, Kolom = Hari)
            $timeSlotIds = $schedules->pluck('time_slot_id')->unique()->filter();
            if ($timeSlotIds->isNotEmpty()) {
                $usedSlots = \App\Models\TimeSlot::whereIn('id', $timeSlotIds)->orderBy('slot_order')->get();
                $minOrder = $usedSlots->min('slot_order');
                $maxOrder = $usedSlots->max('slot_order');

                foreach ($schedules as $s) {
                    if ($s->timeSlot && $s->duration_slots > 1) {
                        $endOrder = $s->timeSlot->slot_order + ($s->duration_slots - 1);
                        if ($endOrder > $maxOrder) {
                            $maxOrder = $endOrder;
                        }
                    }
                }

                $schoolId = $classroom->school_id ?? ($student->school_id ?? null);
                $timeSlots = \App\Models\TimeSlot::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                    ->whereBetween('slot_order', [$minOrder, $maxOrder])
                    ->orderBy('slot_order')
                    ->get()
                    ->unique(fn($slot) => $slot->start_time . '-' . $slot->end_time);
            }

            if ($timeSlots->isEmpty()) {
                // Extract unique times from schedules directly
                $timeSlots = $schedules->map(function ($s) {
                    $start = $s->start_time ? date('H:i', strtotime($s->start_time)) : '07:30';
                    $end = $s->end_time ? date('H:i', strtotime($s->end_time)) : '08:15';
                    return (object) [
                        'slot_order' => $start,
                        'start_time' => $start,
                        'end_time' => $end,
                        'slot_name' => $start . ' - ' . $end,
                    ];
                })->unique('start_time')->sortBy('start_time');
            }

            // Map schedules by day and slotOrder/timeKey
            $sMap = [];
            foreach ($schedules as $s) {
                $slotOrderKey = $s->time_slot_id ? $s->timeSlot?->slot_order : ($s->start_time ? date('H:i', strtotime($s->start_time)) : null);
                $timeKey = ($s->timeSlot->start_time ?? $s->start_time) . '-' . ($s->timeSlot->end_time ?? $s->end_time);
                $dayKey = strtolower($s->day_of_week ?? '');

                if ($slotOrderKey !== null) {
                    $sMap[$dayKey][$slotOrderKey] = $s;
                }
                $sMap[$dayKey][$timeKey] = $s;
            }

            $occupied = [];
            foreach ($timeSlots as $slot) {
                $orderKey = $slot->slot_order ?? $slot->start_time;
                $timeKey = $slot->start_time . '-' . $slot->end_time;

                foreach ($activeDays as $day) {
                    if (isset($occupied[$day][$orderKey])) continue;

                    $sch = $sMap[$day][$orderKey] ?? ($sMap[$day][$timeKey] ?? null);
                    if ($sch) {
                        $timetable[$orderKey][$day] = $sch;
                        $duration = (int) ($sch->duration_slots ?: 1);
                        if ($duration > 1 && is_numeric($orderKey)) {
                            for ($i = 1; $i < $duration; $i++) {
                                $occupied[$day][$orderKey + $i] = true;
                            }
                        }
                    } else {
                        $timetable[$orderKey][$day] = null;
                    }
                }
            }
        }

        $totalUniqueSubjects = $uniqueSubjectIds->unique()->count();

        return view('mobile.student.jadwal', compact(
            'student',
            'classroom',
            'days',
            'activeDays',
            'dayLabels',
            'activeDay',
            'today',
            'schedulesByDay',
            'timetable',
            'timeSlots',
            'subjectColors',
            'currentSchedule',
            'nextSchedule',
            'totalWeeklyJP',
            'totalWeeklySessions',
            'totalUniqueSubjects'
        ));
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
     * Tagihan & Keuangan SPP Mobile Siswa
     * Menghitung kewajiban/tunggakan s.d. bulan berkenaan (bukan 12 bulan penuh)
     */
    public function tagihan()
    {
        $student = $this->getStudent();
        $bills = collect();

        $currentMonth = (int) now()->month;
        $currentYear = (int) now()->year;

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $currentMonthName = $monthNames[$currentMonth] ?? date('F');
        $currentPeriodLabel = $currentMonthName . ' ' . $currentYear;

        // Koleksi & Metrik Keuangan s.d. Bulan Berkenaan
        $dueBills = collect();        // Tagihan jatuh tempo s.d. bulan ini (belum lunas)
        $futureBills = collect();     // Tagihan masa depan (belum jatuh tempo)
        $paidBills = collect();       // Tagihan yang sudah lunas
        $allBills = collect();        // Seluruh tagihan terformat

        $totalDueAmount = 0;          // Total kewajiban s.d. bulan berkenaan
        $totalDuePaid = 0;            // Total yang telah dibayar s.d. bulan berkenaan
        $totalDueOutstanding = 0;     // Tunggakan riil s.d. bulan berkenaan

        $totalFutureAmount = 0;       // Total tagihan masa depan
        $allBillsTotalAmount = 0;     // Total seluruh tagihan tahunan

        $currentMonthBill = null;     // Tagihan SPP bulan berkenaan
        $currentMonthStatus = 'none'; // 'lunas', 'cicilan', 'belum_bayar', 'none'
        $unpaidPastMonths = [];       // Bulan-bulan sebelumnya yang menunggak
        $unpaidCurrentAndPast = [];   // Semua bulan menunggak s.d. bulan ini

        if ($student) {
            $rawBills = StudentBill::where('student_id', $student->id)
                ->with(['academicYear', 'paymentType', 'payments'])
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($rawBills as $bill) {
                $typeName = $bill->paymentType->type_name ?? 'SPP / Uang Sekolah';
                $isMonthly = ($bill->month !== null && $bill->year !== null);

                if ($isMonthly && isset($monthNames[$bill->month])) {
                    $bill->display_title = $typeName . ' (' . $monthNames[$bill->month] . ' ' . $bill->year . ')';
                    $bill->period_name = $monthNames[$bill->month] . ' ' . $bill->year;
                } else {
                    $bill->display_title = $typeName;
                    $bill->period_name = $bill->academicYear->name ?? 'Tagihan Khusus';
                }

                $remaining = max(0, (float)$bill->amount - (float)$bill->paid_amount);
                $bill->sisa_tunggakan = $remaining;

                // Tentukan Jatuh Tempo s.d. Bulan Berkenaan:
                // Non-bulanan (uang seragam/DSP dll) = jatuh tempo
                // Bulanan = year < currentYear ATAU (year == currentYear DAN month <= currentMonth)
                $isDue = false;
                $isCurrentMonth = false;

                if (!$isMonthly) {
                    $isDue = true;
                } else {
                    if ($bill->year < $currentYear || ($bill->year == $currentYear && $bill->month <= $currentMonth)) {
                        $isDue = true;
                    }
                    if ($bill->year == $currentYear && $bill->month == $currentMonth) {
                        $isCurrentMonth = true;
                        $currentMonthBill = $bill;
                    }
                }

                $bill->is_due = $isDue;
                $bill->is_current_month = $isCurrentMonth;
                $bill->is_future = !$isDue;

                $allBillsTotalAmount += (float) $bill->amount;
                $allBills->push($bill);

                if ($isDue) {
                    $totalDueAmount += (float) $bill->amount;
                    $totalDuePaid += (float) $bill->paid_amount;
                    $totalDueOutstanding += $remaining;

                    if ($bill->status === 'lunas' || $remaining <= 0) {
                        $paidBills->push($bill);
                    } else {
                        $dueBills->push($bill);

                        if ($isMonthly) {
                            $unpaidCurrentAndPast[] = $bill->period_name;
                            if (!$isCurrentMonth) {
                                $unpaidPastMonths[] = $bill->period_name;
                            }
                        }
                    }
                } else {
                    // Tagihan bulan mendatang
                    $totalFutureAmount += (float) $bill->amount;
                    if ($bill->status === 'lunas' || $remaining <= 0) {
                        $paidBills->push($bill);
                    } else {
                        $futureBills->push($bill);
                    }
                }
            }

            // Evaluasi status bulan berkenaan
            if ($currentMonthBill) {
                if ($currentMonthBill->status === 'lunas' || $currentMonthBill->sisa_tunggakan <= 0) {
                    $currentMonthStatus = 'lunas';
                } elseif ($currentMonthBill->paid_amount > 0) {
                    $currentMonthStatus = 'cicilan';
                } else {
                    $currentMonthStatus = 'belum_bayar';
                }
            }
        }

        // Tentukan Kesimpulan Finansial Komprehensif
        $financialSummary = [
            'status_code' => 'lunas',
            'headline' => '',
            'description' => '',
            'badge_text' => '',
            'badge_class' => '',
            'card_gradient' => '',
        ];

        if ($totalDueOutstanding <= 0) {
            $financialSummary['status_code'] = 'lunas';
            $financialSummary['headline'] = 'Bebas Tunggakan s.d. ' . $currentPeriodLabel;
            $financialSummary['description'] = 'Seluruh kewajiban pembayaran uang sekolah s.d. bulan berkenaan telah lunas terbayar.';
            $financialSummary['badge_text'] = '🟢 LUNAS BEBAS TUNGGAKAN';
            $financialSummary['badge_class'] = 'bg-emerald-100 text-emerald-900 border-emerald-300';
            $financialSummary['card_gradient'] = 'bg-gradient-to-br from-emerald-600 to-teal-700';
        } elseif (empty($unpaidPastMonths) && $currentMonthStatus !== 'lunas') {
            $financialSummary['status_code'] = 'menunggu_bulan_ini';
            $financialSummary['headline'] = 'Menunggu Pembayaran ' . $currentPeriodLabel;
            $financialSummary['description'] = 'Uang sekolah bulan-bulan sebelumnya telah lunas. Harap menyelesaikan tagihan bulan ' . $currentPeriodLabel . '.';
            $financialSummary['badge_text'] = '🟡 BELUM BAYAR BULAN INI';
            $financialSummary['badge_class'] = 'bg-amber-100 text-amber-900 border-amber-300';
            $financialSummary['card_gradient'] = 'bg-gradient-to-br from-amber-500 to-orange-600';
        } else {
            $financialSummary['status_code'] = 'menunggak';
            $financialSummary['headline'] = 'Menunggak Pembayaran Uang Sekolah';
            $financialSummary['description'] = 'Siswa tercatat belum menyelesaikan pembayaran uang sekolah untuk bulan: ' . implode(', ', $unpaidCurrentAndPast) . ' (Total ' . count($unpaidCurrentAndPast) . ' Bulan).';
            $financialSummary['badge_text'] = '🔴 ADA TUNGGAKAN SPP';
            $financialSummary['badge_class'] = 'bg-rose-100 text-rose-900 border-rose-300';
            $financialSummary['card_gradient'] = 'bg-gradient-to-br from-rose-600 to-red-700';
        }

        return view('mobile.student.tagihan', compact(
            'student',
            'currentPeriodLabel',
            'currentMonthName',
            'currentYear',
            'currentMonthBill',
            'currentMonthStatus',
            'unpaidPastMonths',
            'unpaidCurrentAndPast',
            'financialSummary',
            'totalDueAmount',
            'totalDuePaid',
            'totalDueOutstanding',
            'totalFutureAmount',
            'allBillsTotalAmount',
            'dueBills',
            'futureBills',
            'paidBills',
            'allBills'
        ));
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
     * PKL & Jurnal Siswa Mobile (Khusus Siswa Kelas XII SMK)
     */
    public function pkl()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;
        $schoolType = strtoupper($student->school->type ?? '');

        if ($schoolType !== 'SMK' || $gradeLevel != 12) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses ditolak: Menu PKL hanya diperuntukkan bagi siswa Kelas XII SMK.');
        }

        $pklPlacement = PklPlacement::where('student_id', $student->id)
            ->with(['dudi', 'teacher', 'academicYear', 'grade'])
            ->first();

        $logs = collect();
        if ($pklPlacement) {
            $logs = PklLog::where('pkl_placement_id', $pklPlacement->id)
                ->orderBy('log_date', 'desc')
                ->get();
        }

        return view('mobile.student.pkl', compact('student', 'classroom', 'pklPlacement', 'logs'));
    }

    /**
     * Submit PKL Daily Log Entry with Photo Upload & GPS Tagging
     */
    public function storePklLog(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date|before_or_equal:today',
            'log_date' => 'nullable|date|before_or_equal:today',
            'activity' => 'nullable|string',
            'activity_description' => 'nullable|string',
            'photo' => 'nullable|image|max:10240', // max 10MB
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $logDate = $request->input('date') ?? ($request->input('log_date') ?? date('Y-m-d'));
        $activity = $request->input('activity_description') ?? $request->input('activity');

        if (empty($activity) || strlen(trim($activity)) < 5) {
            return back()->with('error', 'Deskripsi aktivitas PKL wajib diisi minimal 5 karakter.');
        }

        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;
        $schoolType = strtoupper($student->school->type ?? '');

        if ($schoolType !== 'SMK' || $gradeLevel != 12) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses ditolak: Jurnal PKL hanya untuk siswa Kelas XII SMK.');
        }

        $pklPlacement = PklPlacement::where('student_id', $student->id)->first();
        if (!$pklPlacement) {
            return back()->with('error', 'Data penempatan PKL Anda belum ditentukan oleh Panitia.');
        }

        // Cek jurnal pada tanggal yang sama
        $existingLog = PklLog::where('pkl_placement_id', $pklPlacement->id)
            ->where('log_date', $logDate)
            ->first();

        if ($existingLog) {
            if ($existingLog->status === 'rejected') {
                // Update / Revisi jurnal yang diminta perbaikan
                $photoPath = $existingLog->photo;
                if ($request->hasFile('photo')) {
                    if ($photoPath && \Storage::disk('public')->exists($photoPath)) {
                        \Storage::disk('public')->delete($photoPath);
                    }
                    $photoPath = $request->file('photo')->store('pkl_proofs', 'public');
                }

                $existingLog->update([
                    'activity' => $activity,
                    'photo' => $photoPath,
                    'latitude' => $request->input('latitude') ?? $existingLog->latitude,
                    'longitude' => $request->input('longitude') ?? $existingLog->longitude,
                    'status' => 'submitted',
                ]);

                // Notifikasi ke guru pembimbing
                if ($pklPlacement->teacher && $pklPlacement->teacher->user_id) {
                    \App\Models\Notification::create([
                        'user_id' => $pklPlacement->teacher->user_id,
                        'school_id' => $student->school_id,
                        'title' => '📝 Revisi Jurnal PKL Masuk',
                        'message' => 'Siswa ' . $student->full_name . ' telah mengirimkan revisi jurnal PKL tanggal ' . date('d/m/Y', strtotime($logDate)) . '.',
                        'type' => 'info',
                        'related_model' => 'PklLog',
                        'related_id' => $existingLog->id,
                    ]);
                }

                return back()->with('success', 'Revisi jurnal PKL berhasil dikirim dan menunggu verifikasi pembimbing.');
            }

            return back()->with('error', 'Anda sudah mengirim jurnal PKL untuk tanggal ' . date('d/m/Y', strtotime($logDate)) . ' dan sedang diverifikasi/telah disetujui.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('pkl_proofs', 'public');
        }

        PklLog::create([
            'pkl_placement_id' => $pklPlacement->id,
            'log_date' => $logDate,
            'activity' => $activity,
            'photo' => $photoPath,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'status' => 'submitted',
        ]);

        return back()->with('success', 'Jurnal harian PKL berhasil dikirim beserta bukti foto & koordinat GPS.');
    }

    /**
     * Project Akhir (SMK) / Penelitian Akhir (SMA) Siswa Mobile
     */
    public function finalProject()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;
        $schoolType = strtoupper($student->school->type ?? '');

        if (!in_array($schoolType, ['SMA', 'SMK']) || $gradeLevel != 12) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses ditolak: Menu Project/Penelitian Akhir hanya untuk siswa Kelas XII SMA atau SMK.');
        }

        $project = $student->currentFinalProject();
        $formats = FinalProjectFormat::where('school_id', $student->school_id)->get();
        $stages = FinalProject::getStages();
        $classmates = collect();

        if ($project) {
            $project->load(['advisor.user', 'examiner.user', 'members.student.user']);
            $logs = $project->logs()->orderByDesc('log_date')->get();
        } else {
            $logs = collect();
            if ($schoolType === 'SMA' && $classroom) {
                // Teman sekelas yang belum memiliki kelompok
                $classmates = $classroom->students()
                    ->where('students.id', '!=', $student->id)
                    ->whereDoesntHave('finalProjectMemberships')
                    ->orderBy('full_name')
                    ->get();
            }
        }

        return view('mobile.student.final_project', compact('student', 'classroom', 'schoolType', 'project', 'logs', 'formats', 'stages', 'classmates'));
    }

    /**
     * Pengajuan Proposal Penelitian Akhir Siswa (Khusus SMA)
     */
    public function proposeFinalProject(Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $schoolType = strtoupper($student->school->type ?? '');
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;

        if ($schoolType !== 'SMA' || $gradeLevel != 12) {
            return back()->with('error', 'Pengajuan proposal mandiri hanya untuk siswa Kelas XII SMA.');
        }

        if ($student->currentFinalProject()) {
            return back()->with('error', 'Anda sudah terdaftar dalam kelompok Penelitian Akhir.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            $project = FinalProject::create([
                'student_id' => $student->id,
                'academic_year_id' => $classroom->academic_year_id,
                'type' => 'penelitian_ilmiah',
                'title' => $validated['title'],
                'abstract' => $validated['abstract'],
                'status' => 'pending',
            ]);

            // Add leader
            FinalProjectMember::create([
                'final_project_id' => $project->id,
                'student_id' => $student->id,
                'role' => 'leader'
            ]);

            // Add members
            if (!empty($validated['member_ids'])) {
                foreach ($validated['member_ids'] as $memberId) {
                    FinalProjectMember::create([
                        'final_project_id' => $project->id,
                        'student_id' => $memberId,
                        'role' => 'member'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('mobile.final-project')->with('success', 'Proposal Penelitian Akhir berhasil diajukan dan menunggu persetujuan Panitia.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan proposal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Submit Logbook Konsultasi / Bimbingan Project/Penelitian Akhir
     */
    public function storeFinalProjectLog(Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $project = $student->currentFinalProject();
        if (!$project) {
            return back()->with('error', 'Data Project / Penelitian Akhir tidak ditemukan.');
        }

        $validated = $request->validate([
            'stage' => 'required|string',
            'activity' => 'required|string',
            'log_date' => 'required|date',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,png|max:10240',
            'drive_link' => 'nullable|url|max:255',
        ]);

        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('final_project_logs', 'public');
        }

        FinalProjectLog::create([
            'final_project_id' => $project->id,
            'stage' => $validated['stage'],
            'activity' => $validated['activity'],
            'log_date' => $validated['log_date'],
            'file_attachment' => $filePath,
            'drive_link' => $validated['drive_link'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Jurnal bimbingan berhasil dikirim ke Guru Pembimbing.');
    }

    /**
     * Unduh Berkas Panduan / Format
     */
    public function downloadFinalProjectFormat($formatId)
    {
        $student = $this->getStudent();
        $format = FinalProjectFormat::where('school_id', $student->school_id)->findOrFail($formatId);

        if (!Storage::disk('public')->exists($format->file_path)) {
            return back()->with('error', 'Berkas panduan tidak ditemukan di server.');
        }

        return response()->download(storage_path('app/public/' . $format->file_path));
    }

    /**
     * Catatan Perkembangan Siswa Mobile (Prestasi, Pembinaan BK, dan Observasi Perkembangan)
     */
    public function catatan()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Data profil siswa tidak ditemukan.');
        }

        // 1. Prestasi Siswa
        $achievements = StudentAchievement::where('student_id', $student->id)
            ->with(['academicYear'])
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
            ->get();

        // 2. Catatan Pembinaan & Konseling BK
        $counselings = StudentCounselingRecord::where('student_id', $student->id)
            ->where('is_confidential', false) // Siswa hanya melihat catatan non-rahasia
            ->with(['counselor', 'academicYear', 'semester'])
            ->orderByDesc('incident_date')
            ->orderByDesc('id')
            ->get();

        // 3. Catatan Perkembangan & Observasi Belajar
        $developmentNotes = StudentDevelopmentNote::where('student_id', $student->id)
            ->with(['notedByUser', 'academicYear', 'semester'])
            ->orderByDesc('created_at')
            ->get();

        // 4. Rekomendasi Karakter & Akademik
        $recommendations = StudentRecommendation::where('student_id', $student->id)
            ->with(['recommendedBy'])
            ->orderByDesc('created_at')
            ->get();

        // Stats
        $stats = [
            'total_prestasi' => $achievements->count(),
            'total_pembinaan' => $counselings->count(),
            'total_perkembangan' => $developmentNotes->count(),
            'total_rekomendasi' => $recommendations->count(),
            'reputation_points' => $student->user->reputation->total_points ?? $student->reputation_points ?? 0,
            'reputation_level' => $student->user->reputation->level_name ?? 'Rising Star',
        ];

        return view('mobile.student.catatan', compact(
            'student',
            'achievements',
            'counselings',
            'developmentNotes',
            'recommendations',
            'stats'
        ));
    }

    /**
     * DNA Akademik Siswa 360° Mobile.
     */
    public function dna()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $dnaService = app(\App\Services\StudentDnaService::class);
        $analysis = $dnaService->analyze($student);

        return view('mobile.student.dna', compact('student', 'analysis'));
    }

    /**
     * Simpan kuesioner minat mandiri DNA di Mobile.
     */
    public function saveDnaDiagnostic(Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'work_style_preference' => 'required|string|max:100',
            'favorite_subject_cluster' => 'required|string|max:100',
            'career_aspiration' => 'required|string|max:255',
            'interests' => 'nullable|array',
            'logic_self_score' => 'required|integer|min:50|max:100',
            'creative_self_score' => 'required|integer|min:50|max:100',
            'communication_self_score' => 'required|integer|min:50|max:100',
            'technical_self_score' => 'required|integer|min:50|max:100',
            'social_self_score' => 'required|integer|min:50|max:100',
            'discipline_self_score' => 'required|integer|min:50|max:100',
        ]);

        \App\Models\StudentDiagnosticAssessment::updateOrCreate(
            ['student_id' => $student->id],
            $validated
        );

        return redirect()->route('mobile.dna')
            ->with('success', 'Kuesioner minat berhasil diperbarui! Profil DNA Anda telah dikalibrasi ulang.');
    }

    /**
     * Tampilan Mobile Ekstrakurikuler Siswa
     */
    public function ekskul()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $myMemberships = \App\Models\ExtracurricularMember::with([
            'extracurricular.school',
            'extracurricular.advisor',
            'extracurricular.leader.classroom',
            'extracurricular.secretary.classroom',
            'extracurricular.treasurer.classroom',
            'extracurricular.activeMembers.student.school',
            'extracurricular.activeMembers.student.classroom',
            'extracurricular.activities'
        ])
            ->where('student_id', $student->id)
            ->get();

        $joinedEkskulIds = $myMemberships->pluck('extracurricular_id')->toArray();

        // Katalog Ekskul Tersedia: Unit Sekolah Siswa + Unit Yayasan (Marching Band dll.)
        $availableEkskuls = \App\Models\Extracurricular::with([
            'school',
            'advisor',
            'leader.classroom',
            'secretary.classroom',
            'treasurer.classroom',
            'activeMembers.student.school',
            'activeMembers.student.classroom'
        ])
            ->withCount(['activeMembers', 'activities'])
            ->where(function ($q) use ($student) {
                $q->where('school_id', $student->school_id)
                  ->orWhere('scope', 'yayasan')
                  ->orWhereNull('school_id');
            })
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN scope = 'yayasan' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('mobile.student.ekskul', compact('student', 'myMemberships', 'availableEkskuls', 'joinedEkskulIds'));
    }

    /**
     * Klaim / Bergabung Ekskul dari Mobile
     */
    public function claimEkskul(Request $request, \App\Models\Extracurricular $extracurricular)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        if (!$extracurricular->isFoundationLevel() && $extracurricular->school_id !== $student->school_id) {
            return back()->with('error', 'Anda hanya dapat mendaftar di unit sekolah Anda atau unit Yayasan.');
        }

        $section = $request->input('section');
        $notes = $request->input('notes');

        $ekskulService = app(\App\Services\ExtracurricularService::class);
        $ekskulService->claimMembership($student, $extracurricular, 'anggota', $notes, $section);

        $sectionMsg = $section ? " (Section: {$section})" : "";
        return redirect()->route('mobile.ekskul')
            ->with('success', "Selamat! Kamu resmi bergabung di {$extracurricular->name}{$sectionMsg} (+15 Poin Reputasi). Kanal Space kamu telah aktif!");
    }
}

