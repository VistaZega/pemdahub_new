<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\EmployeeAssignmentService;
use Illuminate\Support\Facades\Log;

class EmployeeObserver
{
    public function __construct(private EmployeeAssignmentService $service) {}

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        // --- Auto-sync biodata ke tabel teachers ---
        // Ketika admin HR mengubah nama/foto/alamat di modul Kepegawaian,
        // data di tabel teachers harus ikut terupdate agar konsisten
        // (mencegah "data drift": nama di Rapor berbeda dengan Slip Gaji).
        $this->syncBiodataToTeacher($employee);

        // --- Recalculate workload jika field kritis berubah ---
        $watchedFields = [
            'basic_salary',
            'marital_status', 
            'children_count', 
            'employment_status', 
            'school_id',
            'is_active'
        ];

        if ($employee->isDirty($watchedFields)) {
            $year = AcademicYear::where('is_active', true)->first();
            $semester = Semester::where('is_active', true)->first();

            if ($year && $semester) {
                try {
                    $this->service->calculateWorkload($employee, $year, $semester);
                } catch (\Exception $e) {
                    Log::error("Failed to auto-recalculate workload for employee {$employee->full_name}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Handle the Employee "created" event.
     * Optional: Automatically create a draft summary for the current active semester.
     */
    public function created(Employee $employee): void
    {
        $year = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();

        if ($year && $semester && $employee->is_active) {
            try {
                $this->service->calculateWorkload($employee, $year, $semester);
            } catch (\Exception $e) {
                Log::error("Failed to auto-create workload for new employee {$employee->full_name}: " . $e->getMessage());
            }
        }
    }

    /**
     * Sinkronisasi biodata dari Employee ke Teacher.
     * 
     * Kolom yang disinkronkan: full_name, gender, birth_place, birth_date,
     * religion, address, phone, photo, is_active.
     * 
     * Ini adalah safety net agar data di tabel teachers tetap konsisten
     * meskipun model Teacher sudah punya accessor delegasi ke Employee.
     * Diperlukan untuk backward compatibility (export Excel, laporan raw SQL, dll).
     */
    private function syncBiodataToTeacher(Employee $employee): void
    {
        // Hanya sync jika ada kolom biodata yang berubah
        $biodataFields = Teacher::DELEGATED_BIODATA_FIELDS;
        
        if (!$employee->isDirty($biodataFields)) {
            return;
        }

        try {
            $teacher = $employee->teacher;
            if (!$teacher) {
                return;
            }

            $updates = [];
            foreach ($biodataFields as $field) {
                if ($employee->isDirty($field)) {
                    $updates[$field] = $employee->getAttribute($field);
                }
            }

            if (!empty($updates)) {
                // Update langsung via DB untuk menghindari infinite loop observer
                \Illuminate\Support\Facades\DB::table('teachers')
                    ->where('id', $teacher->id)
                    ->update($updates);

                Log::info("Auto-sync biodata Employee→Teacher berhasil", [
                    'employee_id' => $employee->id,
                    'teacher_id'  => $teacher->id,
                    'fields'      => array_keys($updates),
                ]);
            }
        } catch (\Exception $e) {
            // Jangan gagalkan update Employee hanya karena sync gagal
            Log::warning("Gagal auto-sync biodata ke teacher: " . $e->getMessage(), [
                'employee_id' => $employee->id,
            ]);
        }
    }
}
