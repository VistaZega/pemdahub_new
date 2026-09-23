<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\EmployeePosition;
use App\Models\Teacher;
use App\Models\Employee;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tautkan Profil Guru & Pegawai SMP Sir Solidarman (ID 133) ke User ID Aktif (271)
        DB::table('teachers')->where('id', 133)->update([
            'user_id' => 271,
            'is_active' => true,
        ]);

        DB::table('employees')->where('id', 133)->update([
            'user_id' => 271,
            'is_active' => true,
        ]);

        // 2. Sinkronkan Rombel VII - Aristoteles (ID: 360) dengan Teacher ID Sir Solid (Teacher 207 / 133)
        // Gunakan Teacher ID 207 karena merupakan profil utama multi-unit yang aktif mengajar di SMP & SMK
        DB::table('classrooms')->where('id', 360)->update([
            'homeroom_teacher_id' => 207,
        ]);

        // 3. Auto-Healing: Sinkronkan seluruh rombel aktif di TP aktif yang sudah memiliki SK Penugasan Jabatan Wali Kelas
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $waliPositions = DB::table('employee_positions as ep')
                ->join('positions as p', 'ep.position_id', '=', 'p.id')
                ->where('ep.academic_year_id', $activeYear->id)
                ->whereNull('ep.end_date')
                ->whereNotNull('ep.classroom_id')
                ->where(function ($q) {
                    $q->where('p.position_name', 'like', '%wali kelas%')
                      ->orWhere('p.position_code', 'like', '%WALIKELAS%')
                      ->orWhere('p.position_code', 'like', '%WAKEL%')
                      ->orWhere('p.position_code', 'like', '%WK%');
                })
                ->select('ep.employee_id', 'ep.classroom_id')
                ->get();

            foreach ($waliPositions as $pos) {
                $classroom = Classroom::find($pos->classroom_id);
                if ($classroom && empty($classroom->homeroom_teacher_id)) {
                    $employee = Employee::find($pos->employee_id);
                    $teacher = $employee?->teacher 
                        ?? Teacher::where('employee_id', $pos->employee_id)->first()
                        ?? Teacher::where('user_id', $employee?->user_id)->first();

                    if ($teacher) {
                        $classroom->update(['homeroom_teacher_id' => $teacher->id]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible logic
        DB::table('classrooms')->where('id', 360)->where('homeroom_teacher_id', 207)->update([
            'homeroom_teacher_id' => null,
        ]);
    }
};
