<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\TeachingAssignment;
use App\Models\EmployeeWorkloadSummary;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class EmployeeAssignmentService
{
    /**
     * Default salary formula settings (mirroring sisfopembda tunjangan_formula)
     * Can be overridden via settings table
     */
    public const DEFAULT_FORMULAS = [
        'tunjangan_keluarga_persen' => 10.0,   // 10% of gaji pokok
        'tunjangan_anak_persen' => 5.0,         // 5% of gaji pokok per child
        'tunjangan_anak_max' => 2,              // Max children counted
        'tunjangan_beras' => 50000,             // Fixed per dependent
        'bpjs_kesehatan_persen' => 1.0,         // Default 1%
        'bpjs_ketenagakerjaan_persen' => 2.0,   // Default 2%
    ];

    /**
     * Default jam honor rules per school level (mirroring sisfopembda jam_honor)
     * Can be overridden per school via settings
     */
    public const DEFAULT_JAM_HONOR = [
        'SMP' => ['jam_wajib_tetap' => 22, 'jam_wajib_honor' => 0, 'honor_tetap' => 65000, 'honor_honorer' => 65000, 'honor_percobaan' => 50000],
        'SMA' => ['jam_wajib_tetap' => 22, 'jam_wajib_honor' => 0, 'honor_tetap' => 70000, 'honor_honorer' => 50000, 'honor_percobaan' => 50000],
        'SMK' => ['jam_wajib_tetap' => 22, 'jam_wajib_honor' => 0, 'honor_tetap' => 70000, 'honor_honorer' => 50000, 'honor_percobaan' => 50000],
    ];

    /**
     * Employment statuses eligible for tunjangan keluarga/anak/beras
     * Hanya pegawai yayasan yang mendapat tunjangan (termasuk GTY/PTY)
     */
    public const TUNJANGAN_ELIGIBLE = ['yayasan', 'GTY', 'PTY', 'gty', 'pty'];

    /**
     * Employment statuses with no gaji pokok (paid elsewhere)
     * PNS & Kontrak gajinya dari instansi lain
     */
    public const NO_GAJI_POKOK = ['pns', 'kontrak', 'PNS', 'Kontrak'];

    /**
     * Employment statuses with jam wajib mengajar (yayasan & PNS)
     * Honorer tidak punya jam wajib
     */
    public const JAM_WAJIB_ELIGIBLE = ['yayasan', 'pns', 'GTY', 'PNS', 'gty'];

    /**
     * Default tarif honor Pembimbing PKL per JP.
     * Dapat di-override via settings table (key: pkl_honor_rate, group: salary_formula).
     * Hanya berlaku untuk guru SMK yang ditugaskan sebagai Pembimbing PKL.
     */
    public const DEFAULT_PKL_HONOR_RATE = 43000;

    /**
     * Get salary formula settings (from DB or defaults)
     */
    public function getFormulas(?int $schoolId = null): array
    {
        $formulas = self::DEFAULT_FORMULAS;

        // Override from settings table if available
        $settings = Setting::where('group', 'salary_formula')
            ->pluck('value', 'key')
            ->toArray();

        return array_merge($formulas, $settings);
    }

    /**
     * Get jam honor rules for a school level
     * Reads overrides from settings table (saved via Pengaturan Gaji page)
     */
    public function getJamHonorRules(?string $schoolLevel = null, ?int $schoolId = null): array
    {
        $level = $schoolLevel ?? 'SMA';
        $defaults = self::DEFAULT_JAM_HONOR[$level] ?? self::DEFAULT_JAM_HONOR['SMA'];
        $lk = strtolower($level);

        // Check for DB overrides
        $settings = Setting::where('group', 'salary_formula')
            ->whereIn('key', [
                "jam_wajib_tetap_{$lk}",
                "jam_wajib_honor_{$lk}",
                "honor_tetap_{$lk}",
                "honor_honorer_{$lk}",
                "honor_percobaan_{$lk}",
            ])
            ->pluck('value', 'key')
            ->toArray();

        return [
            'jam_wajib_tetap' => (int) ($settings["jam_wajib_tetap_{$lk}"] ?? $defaults['jam_wajib_tetap']),
            'jam_wajib_honor' => (int) ($settings["jam_wajib_honor_{$lk}"] ?? $defaults['jam_wajib_honor']),
            'honor_tetap' => (float) ($settings["honor_tetap_{$lk}"] ?? $defaults['honor_tetap']),
            'honor_honorer' => (float) ($settings["honor_honorer_{$lk}"] ?? $defaults['honor_honorer']),
            'honor_percobaan' => (float) ($settings["honor_percobaan_{$lk}"] ?? $defaults['honor_percobaan']),
        ];
    }

    /**
     * Calculate teaching honor (honor mengajar)
     * Formula: jam_honor = max(0, jam_mengajar - jam_wajib), honor = jam_honor × honor_per_jam
     */
    public function calculateTeachingHonor(
        int $totalJamMengajar,
        string $employmentStatus,
        ?string $schoolLevel = null,
        ?int $schoolId = null,
        ?Employee $employee = null
    ): array {
        $rules = $this->getJamHonorRules($schoolLevel, $schoolId);

        // Determine jam_wajib and honor_per_jam based on employment status & employee type
        $statusLower = strtolower($employmentStatus);

        // PTY / Non-Guru / Pegawai Yayasan (seperti Ketua Yayasan) tidak memiliki kewajiban jam wajib mengajar (jam_wajib = 0)
        $isNonGuruStaff = $employee && (
            $employee->employee_type !== 'guru' ||
            $employee->isYayasanStaff() ||
            $employee->positions()->where('position_name', 'LIKE', '%Ketua%')->exists()
        );

        $hasJamWajib = !$isNonGuruStaff && in_array($employmentStatus, self::JAM_WAJIB_ELIGIBLE);
        $jamWajib = $hasJamWajib ? $rules['jam_wajib_tetap'] : 0;

        // Yayasan = honor tetap, honorer = honor honorer, percobaan = honor percobaan
        if (in_array($statusLower, ['yayasan', 'pns', 'gty', 'pty', 'tetap'])) {
            $honorPerJam = $rules['honor_tetap'];
        } elseif ($statusLower === 'percobaan') {
            $honorPerJam = $rules['honor_percobaan'];
        } else {
            $honorPerJam = $rules['honor_honorer'];
        }

        $jamHonor = max(0, $totalJamMengajar - $jamWajib);
        $honor = $jamHonor * $honorPerJam;

        return [
            'jam_mengajar' => $totalJamMengajar,
            'jam_wajib' => $jamWajib,
            'jam_honor' => $jamHonor,
            'honor_per_jam' => $honorPerJam,
            'honor_total' => $honor,
        ];
    }

    /**
     * Calculate family, child, and rice allowances for an employee
     * Formula:
     * - Tunjangan Keluarga = % × Gaji Pokok (jika menikah)
     * - Tunjangan Anak = % × Gaji Pokok × min(jumlah_anak, max_anak) (jika menikah)
     * - Tunjangan Beras = Rp/org × (1 + min(jumlah_anak, max_anak)) (jika menikah)
     */
    public function calculateTunjangan(Employee $employee, ?int $schoolId = null): array
    {
        $formulas = $this->getFormulas($schoolId);
        $gajiPokok = (float) ($employee->basic_salary ?? 0);
        $status = $employee->employment_status;

        // Base gaji untuk tunjangan keluarga, anak, dan beras selalu dari Gaji Pokok
        $baseGaji = $gajiPokok;

        // Only GTY and PTY receive tunjangan
        if (!in_array($status, self::TUNJANGAN_ELIGIBLE)) {
            return [
                'tunjangan_keluarga' => 0,
                'tunjangan_anak' => 0,
                'tunjangan_beras' => 0,
                'total_tunjangan' => 0,
                'meta' => [
                    'keluarga_persen' => $formulas['tunjangan_keluarga_persen'],
                    'anak_persen' => $formulas['tunjangan_anak_persen'],
                    'beras_nominal' => $formulas['tunjangan_beras'],
                    'jumlah_anak' => 0,
                    'gaji_pokok' => $baseGaji,
                    'is_married' => false,
                ]
            ];
        }

        $isMarried = ($employee->marital_status ?? 'belum_menikah') === 'menikah';
        $jumlahAnak = min((int) ($employee->children_count ?? 0), (int) $formulas['tunjangan_anak_max']);

        $tunjanganKeluarga = 0;
        $tunjanganAnak = 0;
        $tunjanganBeras = 0;

        if ($isMarried && $baseGaji > 0) {
            // Tunjangan Keluarga = persen × baseGaji (Gaji Pokok)
            $tunjanganKeluarga = ($formulas['tunjangan_keluarga_persen'] / 100) * $baseGaji;

            // Tunjangan Anak = persen × baseGaji × min(jumlah_anak, max)
            if ($jumlahAnak > 0) {
                $tunjanganAnak = ($formulas['tunjangan_anak_persen'] / 100) * $baseGaji * $jumlahAnak;
            }

            // Tunjangan Beras = fixed × (1 + jumlah_anak) -- 1 for spouse
            $tunjanganBeras = $formulas['tunjangan_beras'] * (1 + $jumlahAnak);
        }

        $total = $tunjanganKeluarga + $tunjanganAnak + $tunjanganBeras;

        return [
            'tunjangan_keluarga' => round($tunjanganKeluarga),
            'tunjangan_anak' => round($tunjanganAnak),
            'tunjangan_beras' => round($tunjanganBeras),
            'total_tunjangan' => round($total),
            'meta' => [
                'keluarga_persen' => $formulas['tunjangan_keluarga_persen'],
                'anak_persen' => $formulas['tunjangan_anak_persen'],
                'beras_nominal' => $formulas['tunjangan_beras'],
                'jumlah_anak' => $jumlahAnak,
                'gaji_pokok' => $baseGaji,
                'is_married' => $isMarried,
            ]
        ];
    }

    /**
     * Calculate full salary (Take Home Pay) for an employee
     * THP = (Gaji Pokok + Tunjangan Jabatan + Honor Mengajar + Tunjangan Keluarga + Tunjangan Anak + Tunjangan Beras) - Potongan (BPJS)
     */
    public function calculateFullSalary(
        Employee $employee,
        AcademicYear $year,
        Semester $semester,
        ?string $schoolLevel = null,
        ?int $filterSchoolId = null
    ): array {
        $effectiveSchoolId = $filterSchoolId ?? $employee->school_id;
        $formulas = $this->getFormulas($effectiveSchoolId);

        // 1. Gaji Pokok (Hanya dibebankan di unit asal pegawai)
        $gajiPokok = 0;
        if (!$filterSchoolId || $filterSchoolId == $employee->school_id) {
            $gajiPokok = in_array($employee->employment_status, self::NO_GAJI_POKOK)
                ? 0
                : (float) ($employee->basic_salary ?? 0);
        }

        // 2. Tunjangan Jabatan (Hanya jabatan yang terdaftar di unit sekolah $filterSchoolId)
        $positionsQuery = $employee->activePositions()
            ->wherePivot('academic_year_id', $year->id);

        if ($filterSchoolId) {
            $positionsQuery->where(function($pQ) use ($filterSchoolId) {
                $pQ->where('positions.school_id', $filterSchoolId)
                   ->orWhereNull('positions.school_id');
            });
        }

        $positions = $positionsQuery->get();

        $jabatanDetails = [];
        $tunjanganJabatan = 0;

        foreach ($positions as $position) {
            // Use override allowance if set, otherwise use position default
            $amount = $position->pivot->position_allowance ?? 0;
            if ($amount <= 0) {
                $amount = $position->allowance_amount ?? 0;
            }

            $tunjanganJabatan += $amount;
            $jabatanDetails[] = [
                'name' => $position->position_name,
                'amount' => (float)$amount
            ];
        }

        // 2b. Honor Pembimbing PKL (Khusus SMK)
        // Dihitung dari pkl_supervisor_hours di setiap posisi aktif guru
        // Formula: total_pkl_jp × pkl_honor_rate (default Rp 43.000/JP)
        $pklHonorRate = (float) (Setting::where('group', 'salary_formula')
            ->where('key', 'pkl_honor_rate')
            ->value('value') ?? self::DEFAULT_PKL_HONOR_RATE);

        $pklSupervisorHours = 0;
        $honorPkl = 0;

        foreach ($positions as $position) {
            $pklHours = (int) ($position->pivot->pkl_supervisor_hours ?? 0);
            if ($pklHours > 0) {
                // Gunakan rate override per assignment jika ada, fallback ke setting global
                $rateForThisPos = (float) ($position->pivot->pkl_honor_rate ?? 0);
                $effectiveRate = ($rateForThisPos > 0) ? $rateForThisPos : $pklHonorRate;
                $pklSupervisorHours += $pklHours;
                $honorPkl += $pklHours * $effectiveRate;
            }
        }

        // 3. Honor Mengajar
        $teacherModel = $employee->teacher;
        $totalJamMengajar = 0;
        if ($teacherModel) {
            // Fetch all assignments for this teacher
            $assignmentsQuery = TeachingAssignment::where('teacher_id', $teacherModel->id)
                ->where('academic_year_id', $year->id)
                ->where('is_active', true);

            if ($filterSchoolId) {
                $assignmentsQuery->whereHas('classroom', function($cQ) use ($filterSchoolId) {
                    $cQ->where('school_id', $filterSchoolId);
                });
            }

            $assignments = $assignmentsQuery->get();

            // Sum non-grouped assignments
            $nonGroupedHours = $assignments->whereNull('group_code')->sum('hours_per_week');

            // Sum grouped assignments (only once per group_code)
            $groupedHours = $assignments->whereNotNull('group_code')
                ->groupBy('group_code')
                ->map(function($group) {
                    return $group->max('hours_per_week');
                })->sum();

            $totalJamMengajar = $nonGroupedHours + $groupedHours;
        }

        $honorData = $this->calculateTeachingHonor(
            $totalJamMengajar,
            $employee->employment_status ?? 'yayasan',
            $schoolLevel,
            $effectiveSchoolId,
            $employee
        );

        // Khusus untuk Pegawai/Unit Yayasan: Honor mengajar di sekolah TIDAK dimasukkan dalam Slip Gaji Yayasan,
        // karena dibayarkan langsung oleh Bendahara unit sekolah masing-masing.
        $isYayasanUnit = $employee->isYayasanStaff() || ($employee->school?->type === 'yayasan');
        $honorMengajarSekolah = round($honorData['honor_total']);
        $honorMengajarYayasan = ($isYayasanUnit && (!$filterSchoolId || $filterSchoolId == $employee->school_id)) ? 0 : $honorMengajarSekolah;

        // 4. Tunjangan (Keluarga, Anak, Beras) (Hanya dibebankan di unit asal pegawai)
        $tunjanganData = [
            'tunjangan_keluarga' => 0,
            'tunjangan_anak' => 0,
            'tunjangan_beras' => 0,
            'total_tunjangan' => 0,
            'meta' => []
        ];

        if (!$filterSchoolId || $filterSchoolId == $employee->school_id) {
            $tunjanganData = $this->calculateTunjangan($employee, $effectiveSchoolId);
        }

        // 5. Potongan (Deductions)
        $totalPotongan = 0;

        // 6. Total
        $grossPay = $gajiPokok + $tunjanganJabatan + $honorMengajarYayasan
            + round($honorPkl)
            + $tunjanganData['tunjangan_keluarga']
            + $tunjanganData['tunjangan_anak']
            + $tunjanganData['tunjangan_beras'];

        $thp = $grossPay - $totalPotongan;

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'employment_status' => $employee->employment_status,

            'gaji_pokok' => round($gajiPokok),
            'tunjangan_jabatan' => round($tunjanganJabatan),
            'jabatan_list' => $positions->pluck('position_name')->toArray(),
            'jabatan_details' => $jabatanDetails,

            'jam_mengajar' => $honorData['jam_mengajar'],
            'jam_wajib' => $honorData['jam_wajib'],
            'jam_honor' => $honorData['jam_honor'],
            'honor_per_jam' => $honorData['honor_per_jam'],
            'honor_mengajar' => $honorMengajarYayasan,
            'honor_mengajar_sekolah' => $honorMengajarSekolah,
            'is_honor_dibayar_sekolah' => $isYayasanUnit && $honorMengajarSekolah > 0,

            'pkl_supervisor_hours' => $pklSupervisorHours,
            'pkl_honor_rate' => $pklHonorRate,
            'honor_pkl' => round($honorPkl),

            'tunjangan_keluarga' => $tunjanganData['tunjangan_keluarga'],
            'tunjangan_anak' => $tunjanganData['tunjangan_anak'],
            'tunjangan_beras' => $tunjanganData['tunjangan_beras'],
            'tunjangan_meta' => $tunjanganData['meta'],

            'bpjs_kesehatan' => 0,
            'bpjs_ketenagakerjaan' => 0,
            'total_potongan' => 0,
            'gross_pay' => round($grossPay),

            'thp' => round($thp),
        ];
    }

    /**
     * Calculate and store workload summary for an employee in a semester.
     */
    public function calculateWorkload(
        Employee $employee,
        AcademicYear $year,
        Semester $semester
    ): EmployeeWorkloadSummary {
        return DB::transaction(function () use ($employee, $year, $semester) {
            // Get school level for honor calculation (use 'type' column, not 'level')
            $schoolLevel = $employee->school?->type ?? 'SMA';

            // Calculate full salary
            $salary = $this->calculateFullSalary($employee, $year, $semester, $schoolLevel);

            // Jabatan
            $positions = $employee->activePositions()
                ->wherePivot('academic_year_id', $year->id)
                ->get();
            $totalPositionCount = $positions->count();

            // Tugas Mengajar
            $teachingAssignments = TeachingAssignment::where('teacher_id', $employee->teacher?->id)
                ->where('academic_year_id', $year->id)
                ->where('is_active', true)
                ->get();
            $totalTeachingClasses = $teachingAssignments->count();
            $totalTeachingSubjects = $teachingAssignments->groupBy('subject_id')->count();

            // Total allowance = jabatan + honor + tunjangan keluarga/anak/beras + honor PKL
            $totalAllowance = $salary['tunjangan_jabatan'] + $salary['honor_mengajar']
                + $salary['honor_pkl']
                + $salary['tunjangan_keluarga'] + $salary['tunjangan_anak'] + $salary['tunjangan_beras'];

            // Store summary
            $summary = EmployeeWorkloadSummary::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'semester_id' => $semester->id,
                ],
                [
                    'academic_year_id' => $year->id,
                    'total_position_count' => $totalPositionCount,
                    'total_position_allowance' => $salary['tunjangan_jabatan'],
                    'total_teaching_hours' => $salary['jam_mengajar'],
                    'total_teaching_classes' => $totalTeachingClasses,
                    'total_teaching_subjects' => $totalTeachingSubjects,
                    'total_teaching_allowance' => $salary['honor_mengajar'],
                    'honor_pkl' => $salary['honor_pkl'],
                    'pkl_supervisor_hours' => $salary['pkl_supervisor_hours'],
                    'family_allowance' => $salary['tunjangan_keluarga'],
                    'child_allowance' => $salary['tunjangan_anak'],
                    'rice_allowance' => $salary['tunjangan_beras'],
                    'bpjs_kesehatan' => $salary['bpjs_kesehatan'],
                    'bpjs_ketenagakerjaan' => $salary['bpjs_ketenagakerjaan'],
                    'total_deductions' => $salary['total_potongan'],
                    'total_allowance' => $totalAllowance,
                    'basic_salary' => $salary['gaji_pokok'],
                    'gross_pay' => $salary['gross_pay'],
                    'total_compensation' => $salary['thp'],
                    'status' => 'draft',
                ]
            );

            return $summary;
        });
    }

    /**
     * Bulk calculate workload for all employees in a school
     */
    public function bulkCalculateWorkload(
        int $schoolId,
        AcademicYear $year,
        Semester $semester
    ): array {
        $employees = Employee::where('school_id', $schoolId)
            ->where('is_active', true)
            ->get();

        $results = ['success' => 0, 'errors' => []];

        foreach ($employees as $employee) {
            try {
                $this->calculateWorkload($employee, $year, $semester);
                $results['success']++;
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'employee_id' => $employee->id,
                    'name' => $employee->full_name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Generate salary slip data for an employee
     */
    public function generateSalarySlip(
        Employee $employee,
        AcademicYear $year,
        Semester $semester,
        ?string $schoolLevel = null
    ): array {
        $salary = $this->calculateFullSalary($employee, $year, $semester, $schoolLevel);

        // Build components list (only show non-zero)
        $components = [];
        if ($salary['gaji_pokok'] > 0) {
            $components[] = ['label' => 'Gaji Pokok', 'amount' => $salary['gaji_pokok']];
        }
        if ($salary['tunjangan_jabatan'] > 0) {
            $components[] = [
                'label' => 'Tunjangan Jabatan (' . implode(', ', $salary['jabatan_list']) . ')',
                'amount' => $salary['tunjangan_jabatan'],
            ];
        }
        if ($salary['tunjangan_keluarga'] > 0) {
            $meta = $salary['tunjangan_meta'];
            $label = "Tunjangan Keluarga ({$meta['keluarga_persen']}% × Rp " . number_format($meta['gaji_pokok'], 0, ',', '.') . ")";
            $components[] = ['label' => $label, 'amount' => $salary['tunjangan_keluarga']];
        }
        if ($salary['tunjangan_anak'] > 0) {
            $meta = $salary['tunjangan_meta'];
            $label = "Tunjangan Anak ({$meta['anak_persen']}% × Rp " . number_format($meta['gaji_pokok'], 0, ',', '.') . " × {$meta['jumlah_anak']} anak)";
            $components[] = ['label' => $label, 'amount' => $salary['tunjangan_anak']];
        }
        if ($salary['tunjangan_beras'] > 0) {
            $meta = $salary['tunjangan_meta'];
            $label = "Tunjangan Beras (Rp " . number_format($meta['beras_nominal'], 0, ',', '.') . " × " . (1 + $meta['jumlah_anak']) . " jiwa)";
            $components[] = ['label' => $label, 'amount' => $salary['tunjangan_beras']];
        }
        if ($salary['honor_mengajar'] > 0) {
            $components[] = [
                'label' => "Honor Mengajar ({$salary['jam_honor']} jam × Rp " . number_format($salary['honor_per_jam'], 0, ',', '.') . ")",
                'amount' => $salary['honor_mengajar'],
            ];
        }
        if (isset($salary['honor_pkl']) && $salary['honor_pkl'] > 0) {
            $components[] = [
                'label' => "Honor Pembimbing PKL ({$salary['pkl_supervisor_hours']} JP × Rp " . number_format($salary['pkl_honor_rate'], 0, ',', '.') . ")",
                'amount' => $salary['honor_pkl'],
            ];
        }

        // Deductions
        $deductions = [];
        if ($salary['bpjs_kesehatan'] > 0) {
            $deductions[] = ['label' => 'Potongan BPJS Kesehatan', 'amount' => $salary['bpjs_kesehatan']];
        }
        if ($salary['bpjs_ketenagakerjaan'] > 0) {
            $deductions[] = ['label' => 'Potongan BPJS Ketenagakerjaan', 'amount' => $salary['bpjs_ketenagakerjaan']];
        }

        return [
            'employee_name' => $employee->full_name,
            'nip' => $employee->employee_code,
            'employment_status' => ucfirst($employee->employment_status),
            'school_name' => $employee->school?->name ?? 'YAYASAN PEMBDA',
            'period' => $semester->semester_name . ' ' . $year->year,
            'teaching_hours' => $salary['jam_mengajar'],
            'components' => $components,
            'deductions' => $deductions,
            'gross_pay' => $salary['gross_pay'],
            'total_deductions' => $salary['total_potongan'],
            'take_home_pay' => $salary['thp'],
            'terbilang' => $this->terbilang($salary['thp']) . ' Rupiah',
            'salary' => $salary, // Still keep the raw salary data just in case
        ];
    }

    /**
     * Basic Indonesian Terbilang helper
     */
    private function terbilang($number): string
    {
        $number = abs($number);
        $words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp = "";

        if ($number < 12) {
            $temp = " " . $words[$number];
        } else if ($number < 20) {
            $temp = $this->terbilang($number - 10) . " Belas";
        } else if ($number < 100) {
            $temp = $this->terbilang((int)($number / 10)) . " Puluh" . $this->terbilang($number % 10);
        } else if ($number < 200) {
            $temp = " Seratus" . $this->terbilang($number - 100);
        } else if ($number < 1000) {
            $temp = $this->terbilang((int)($number / 100)) . " Ratus" . $this->terbilang($number % 100);
        } else if ($number < 2000) {
            $temp = " Seribu" . $this->terbilang($number - 1000);
        } else if ($number < 1000000) {
            $temp = $this->terbilang((int)($number / 1000)) . " Ribu" . $this->terbilang($number % 1000);
        } else if ($number < 1000000000) {
            $temp = $this->terbilang((int)($number / 1000000)) . " Juta" . $this->terbilang($number % 1000000);
        }

        return trim($temp);
    }

    /**
     * Confirm/lock a workload summary for payroll
     */
    public function confirmWorkload(EmployeeWorkloadSummary $summary, int $confirmedBy): EmployeeWorkloadSummary
    {
        $summary->update([
            'status' => 'confirmed',
            'confirmed_by' => $confirmedBy,
            'confirmed_at' => now(),
        ]);
        return $summary;
    }

    /**
     * Lock a workload summary (no more changes)
     */
    public function lockWorkload(EmployeeWorkloadSummary $summary): EmployeeWorkloadSummary
    {
        $summary->update(['status' => 'locked']);
        return $summary;
    }
}
