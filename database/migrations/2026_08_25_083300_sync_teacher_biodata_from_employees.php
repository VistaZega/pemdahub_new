<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Sinkronisasi darurat: menyalin biodata dari tabel employees ke teachers
     * untuk menyeragamkan data yang mungkin sudah berbeda (data drift).
     * 
     * Kolom yang disinkronkan: full_name, gender, birth_place, birth_date,
     * religion, address, phone, photo, is_active.
     * 
     * Setelah migrasi ini, tabel employees menjadi Single Source of Truth
     * dan model Teacher menggunakan accessor delegasi untuk membaca dari employees.
     */
    public function up(): void
    {
        $synced = 0;
        $skipped = 0;
        $errors = 0;

        // Ambil semua teacher yang punya employee_id
        $teachers = DB::table('teachers')
            ->whereNotNull('employee_id')
            ->get();

        foreach ($teachers as $teacher) {
            $employee = DB::table('employees')
                ->where('id', $teacher->employee_id)
                ->first();

            if (!$employee) {
                $skipped++;
                continue;
            }

            try {
                DB::table('teachers')
                    ->where('id', $teacher->id)
                    ->update([
                        'full_name'   => $employee->full_name,
                        'gender'      => $employee->gender,
                        'birth_place' => $employee->birth_place,
                        'birth_date'  => $employee->birth_date,
                        'religion'    => $employee->religion,
                        'address'     => $employee->address,
                        'phone'       => $employee->phone,
                        'photo'       => $employee->photo,
                        'is_active'   => $employee->is_active,
                    ]);
                $synced++;
            } catch (\Exception $e) {
                $errors++;
                Log::warning("Gagal sync biodata teacher #{$teacher->id}: " . $e->getMessage());
            }
        }

        Log::info("Sinkronisasi biodata Employee→Teacher selesai", [
            'synced'  => $synced,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    /**
     * Reverse the migrations.
     * Tidak dapat di-rollback karena data lama tidak disimpan.
     */
    public function down(): void
    {
        // Data lama tidak bisa dikembalikan - ini adalah one-way sync
        Log::info('Rollback sync_teacher_biodata_from_employees: no-op (data lama tidak tersimpan)');
    }
};
