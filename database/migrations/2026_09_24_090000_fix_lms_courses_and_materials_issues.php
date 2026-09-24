<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Teacher;
use App\Models\LmsCourse;
use App\Models\LmsMaterial;
use App\Models\LmsModule;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sync Sir Solidarman courses (Teacher ID 133 -> 207 / User ID 271)
        DB::table('lms_courses')->where('teacher_id', 133)->update([
            'teacher_id' => 207,
        ]);

        // 2. Multi-teacher auto-sync for courses where teacher has multiple teacher records
        $teachersWithMultiple = DB::table('teachers')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($teachersWithMultiple as $row) {
            $primaryTeacher = Teacher::where('user_id', $row->user_id)->where('is_active', true)->first()
                ?? Teacher::where('user_id', $row->user_id)->first();

            if ($primaryTeacher) {
                $allTeacherIds = Teacher::where('user_id', $row->user_id)->pluck('id')->toArray();
                DB::table('lms_courses')
                    ->whereIn('teacher_id', $allTeacherIds)
                    ->update(['teacher_id' => $primaryTeacher->id]);
            }
        }

        // 3. Auto-assign orphaned materials to first module of course if course has modules
        $unassignedMaterials = LmsMaterial::whereNull('module_id')->get();
        foreach ($unassignedMaterials as $mat) {
            $firstModule = LmsModule::where('course_id', $mat->course_id)->orderBy('sequence')->first();
            if ($firstModule) {
                $mat->update(['module_id' => $firstModule->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
