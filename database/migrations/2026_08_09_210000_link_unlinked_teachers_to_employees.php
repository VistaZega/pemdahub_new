<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Link unlinked teachers to employees by name/code and trigger workload recalculation
     */
    public function up(): void
    {
        // 1. Link teachers with missing employee_id
        $unlinkedTeachers = DB::table('teachers')->whereNull('employee_id')->get();
        foreach ($unlinkedTeachers as $t) {
            $cleanName = trim(explode(',', $t->full_name)[0]);
            $emp = DB::table('employees')
                ->where('employee_code', $t->teacher_code)
                ->orWhere('full_name', $t->full_name)
                ->orWhere('full_name', 'LIKE', '%' . $cleanName . '%')
                ->first();

            if ($emp) {
                DB::table('teachers')->where('id', $t->id)->update(['employee_id' => $emp->id]);
            }
        }

        // 2. Link employees of type 'guru' with missing teacher records
        $guruEmployees = DB::table('employees')->whereIn('employee_type', ['guru', 'honorer'])->whereNull('user_id')->get();
        // Recalculate workload summaries for active year
        $activeYear = DB::table('academic_years')->where('is_active', true)->first();
        $activeSemester = DB::table('semesters')->where('is_active', true)->first();

        if ($activeYear && $activeSemester) {
            $assignmentService = app(\App\Services\EmployeeAssignmentService::class);
            $employees = \App\Models\Employee::where('is_active', true)->get();
            foreach ($employees as $emp) {
                try {
                    $assignmentService->calculateWorkload($emp, \App\Models\AcademicYear::find($activeYear->id), \App\Models\Semester::find($activeSemester->id));
                } catch (\Exception $e) {
                    // Ignore individual errors
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down
    }
};
