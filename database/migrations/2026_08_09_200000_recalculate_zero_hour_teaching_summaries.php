<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set total_teaching_allowance = 0 for summaries where total_teaching_hours = 0
     * and recalculate total_allowance and total_compensation (THP).
     */
    public function up(): void
    {
        $activeYear = DB::table('academic_years')->where('is_active', true)->first();
        $activeSemester = DB::table('semesters')->where('is_active', true)->first();

        if ($activeYear && $activeSemester) {
            $summaries = DB::table('employee_workload_summaries')
                ->where('academic_year_id', $activeYear->id)
                ->where('semester_id', $activeSemester->id)
                ->where('total_teaching_hours', 0)
                ->where('total_teaching_allowance', '>', 0)
                ->get();

            foreach ($summaries as $s) {
                $newTeachingAllowance = 0;
                $newTotalAllowance = $s->total_position_allowance + $newTeachingAllowance + $s->honor_pkl + $s->family_allowance + $s->child_allowance + $s->rice_allowance;
                $newGrossPay = $s->basic_salary + $newTotalAllowance;
                $newThp = $newGrossPay - $s->total_deductions;

                DB::table('employee_workload_summaries')
                    ->where('id', $s->id)
                    ->update([
                        'total_teaching_allowance' => 0,
                        'total_allowance'          => $newTotalAllowance,
                        'gross_pay'                => $newGrossPay,
                        'total_compensation'       => $newThp,
                        'updated_at'               => now(),
                    ]);
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
