<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Restore Sozaro Harefa, A.Md dan Herlinawati Telaumbanua, SE
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $smkId = DB::table('schools')
            ->where('type', 'SMK')
            ->orWhere('name', 'LIKE', '%SMK%')
            ->value('id');

        $activeYearId = DB::table('academic_years')
            ->where('is_active', true)
            ->value('id');

        // 1. Restore Sozaro Harefa, A.Md
        $sozaroExists = DB::table('employees')
            ->where('full_name', 'LIKE', '%Sozaro Harefa%')
            ->exists();

        if (!$sozaroExists) {
            $sozaroId = DB::table('employees')->insertGetId([
                'school_id'         => $smkId,
                'employee_code'     => 'SMK-047',
                'full_name'         => 'Sozaro Harefa, A.Md',
                'gender'            => 'L',
                'employee_type'     => 'staff_tu',
                'employment_status' => 'honorer',
                'marital_status'    => 'menikah',
                'children_count'    => 2,
                'basic_salary'      => 2300000,
                'tmt_date'          => '2025-08-01',
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // KTU SMK position
            $ktuPosId = DB::table('positions')
                ->where(function($q) use ($smkId) {
                    $q->where('school_id', $smkId)->orWhereNull('school_id');
                })
                ->where('position_name', 'LIKE', '%KTU%')
                ->value('id') ?? 62;

            if ($ktuPosId) {
                DB::table('employee_positions')->insert([
                    'employee_id'      => $sozaroId,
                    'position_id'      => $ktuPosId,
                    'academic_year_id' => $activeYearId,
                    'start_date'       => '2025-08-01',
                    'is_primary'       => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            // Operator SMK position
            $opPosId = DB::table('positions')
                ->where(function($q) use ($smkId) {
                    $q->where('school_id', $smkId)->orWhereNull('school_id');
                })
                ->where('position_name', 'LIKE', '%Operator%')
                ->value('id') ?? 65;

            if ($opPosId) {
                DB::table('employee_positions')->insert([
                    'employee_id'      => $sozaroId,
                    'position_id'      => $opPosId,
                    'academic_year_id' => $activeYearId,
                    'start_date'       => '2025-08-01',
                    'is_primary'       => false,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // 2. Restore Herlinawati Telaumbanua, SE
        $herlynExists = DB::table('employees')
            ->where('full_name', 'LIKE', '%Herlinawati Telaumbanua%')
            ->orWhere('full_name', 'LIKE', '%Herlyn Telaumbanua%')
            ->exists();

        if (!$herlynExists) {
            DB::table('employees')->insert([
                'school_id'         => $smkId,
                'employee_code'     => 'SMK-050',
                'full_name'         => 'Herlinawati Telaumbanua, SE',
                'gender'            => 'P',
                'employee_type'     => 'other',
                'employment_status' => 'honorer',
                'marital_status'    => 'belum_menikah',
                'children_count'    => 0,
                'basic_salary'      => 1500000,
                'tmt_date'          => '2025-08-01',
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down: do nothing to prevent accidental loss
    }
};
