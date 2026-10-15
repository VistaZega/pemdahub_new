<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // 1. Identifikasi record di tabel `teachers` yang sebenarnya adalah Pegawai (staf TU / non-guru)
            $bogusTeachers = DB::table('teachers')
                ->leftJoin('employees', 'teachers.employee_id', '=', 'employees.id')
                ->leftJoin('users', 'teachers.user_id', '=', 'users.id')
                ->where(function ($q) {
                    $q->where(function ($eq) {
                        $eq->whereNotNull('employees.employee_type')
                           ->where('employees.employee_type', '!=', 'guru');
                    })
                    ->orWhere(function ($uq) {
                        $uq->whereNotNull('users.role')
                           ->where('users.role', '=', 'pegawai');
                    })
                    ->orWhere('teachers.teacher_code', 'LIKE', 'PEG%')
                    ->orWhere('teachers.teacher_code', 'LIKE', 'PGW%');
                })
                ->select(
                    'teachers.id as teacher_id',
                    'teachers.teacher_code',
                    'teachers.full_name',
                    'teachers.employee_id',
                    'teachers.user_id',
                    'employees.employee_type',
                    'users.role as user_role'
                )
                ->get();

            $deletedCount = 0;
            foreach ($bogusTeachers as $bt) {
                // Pastikan tidak ada data KBM aktif (jadwal mengajar, penugasan mengajar, nilai KBM)
                $hasSchedules = DB::table('schedules')->where('teacher_id', $bt->teacher_id)->exists();
                $hasTeachingAssignments = DB::table('teaching_assignments')->where('teacher_id', $bt->teacher_id)->exists();
                $hasGrades = DB::table('grades')->where('teacher_id', $bt->teacher_id)->exists();

                // Hanya hapus jika pegawai ini bukan pengampu KBM riil
                if (!$hasSchedules && !$hasTeachingAssignments && !$hasGrades) {
                    // Safety: Hanya hapus record di tabel teachers!
                    // JANGAN sentuh tabel employees atau users!
                    DB::table('teachers')->where('id', $bt->teacher_id)->delete();
                    $deletedCount++;
                    Log::info("Cleanup: Dihapus record profil guru palsu untuk pegawai: {$bt->full_name} (Teacher ID: {$bt->teacher_id}, Code: {$bt->teacher_code})");
                }
            }

            // 2. Pastikan akun User milik pegawai non-guru tetap memiliki role 'pegawai'
            $nonTeacherEmployees = DB::table('employees')
                ->whereNotNull('user_id')
                ->where('employee_type', '!=', 'guru')
                ->get();

            foreach ($nonTeacherEmployees as $emp) {
                DB::table('users')
                    ->where('id', $emp->user_id)
                    ->where('role', 'guru')
                    ->update(['role' => 'pegawai']);
            }

            Log::info("Cleanup bogus teachers completed. Total removed: {$deletedCount}");
        } catch (\Throwable $e) {
            Log::error("Error during cleanup of bogus teachers: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data cleanup
    }
};
