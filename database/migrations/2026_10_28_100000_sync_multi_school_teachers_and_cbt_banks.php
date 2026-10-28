<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Teacher;
use App\Models\School;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan tabel teacher_schools ada
        if (!Schema::hasTable('teacher_schools')) {
            Schema::create('teacher_schools', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->unique(['teacher_id', 'school_id']);
            });
        }

        // 2. Tautkan akun Sir Solidarman (User 271, Teachers 133 & 207) ke unit SMP dan SMK
        try {
            $solidTeachers = Teacher::where('full_name', 'like', '%Solid%')
                ->orWhere('id', 133)
                ->orWhere('id', 207)
                ->get();

            $smpSchool = School::where('type', 'SMP')->first();
            $smkSchool = School::where('type', 'SMK')->first();

            foreach ($solidTeachers as $st) {
                // Pastikan user_id terpasang jika kosong
                if (empty($st->user_id) && $st->id == 133) {
                    $st->update(['user_id' => 271, 'is_active' => true]);
                }

                // Daftarkan ke pivot teacher_schools untuk SMP & SMK
                if ($smpSchool) {
                    DB::table('teacher_schools')->updateOrInsert(
                        ['teacher_id' => $st->id, 'school_id' => $smpSchool->id],
                        ['is_primary' => ($st->school_id == $smpSchool->id), 'updated_at' => now(), 'created_at' => now()]
                    );
                }
                if ($smkSchool) {
                    DB::table('teacher_schools')->updateOrInsert(
                        ['teacher_id' => $st->id, 'school_id' => $smkSchool->id],
                        ['is_primary' => ($st->school_id == $smkSchool->id), 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }

            // User Solidarman (271)
            $solidUser = User::find(271) ?? User::where('name', 'like', '%Solid%')->first();
            if ($solidUser) {
                $solidUser->update(['is_active' => true]);
            }
        } catch (\Throwable $e) {}

        // 3. Auto-Healing: Daftarkan seluruh guru multi-unit ke tabel teacher_schools
        try {
            // A. Berdasarkan user_id yang memiliki lebih dari satu profil guru di sekolah berbeda
            $multiTeachers = DB::table('teachers as t1')
                ->join('teachers as t2', 't1.user_id', '=', 't2.user_id')
                ->where('t1.id', '!=', 't2.id')
                ->where('t1.school_id', '!=', 't2.school_id')
                ->whereNotNull('t1.user_id')
                ->select('t1.id as t1_id', 't1.school_id as s1_id', 't2.id as t2_id', 't2.school_id as s2_id')
                ->get();

            foreach ($multiTeachers as $mt) {
                DB::table('teacher_schools')->updateOrInsert(
                    ['teacher_id' => $mt->t1_id, 'school_id' => $mt->s2_id],
                    ['is_primary' => false, 'updated_at' => now(), 'created_at' => now()]
                );
                DB::table('teacher_schools')->updateOrInsert(
                    ['teacher_id' => $mt->t2_id, 'school_id' => $mt->s1_id],
                    ['is_primary' => false, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            // B. Berdasarkan penugasan mengajar (teaching_assignments) di unit sekolah lain
            $taCross = DB::table('teaching_assignments as ta')
                ->join('classrooms as c', 'ta.classroom_id', '=', 'c.id')
                ->join('teachers as t', 'ta.teacher_id', '=', 't.id')
                ->whereColumn('c.school_id', '!=', 't.school_id')
                ->select('ta.teacher_id', 'c.school_id')
                ->distinct()
                ->get();

            foreach ($taCross as $tac) {
                DB::table('teacher_schools')->updateOrInsert(
                    ['teacher_id' => $tac->teacher_id, 'school_id' => $tac->school_id],
                    ['is_primary' => false, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            // C. Berdasarkan jadwal mengajar (schedules) di unit sekolah lain
            $schedCross = DB::table('schedules as s')
                ->join('teachers as t', 's.teacher_id', '=', 't.id')
                ->whereColumn('s.school_id', '!=', 't.school_id')
                ->select('s.teacher_id', 's.school_id')
                ->distinct()
                ->get();

            foreach ($schedCross as $sc) {
                DB::table('teacher_schools')->updateOrInsert(
                    ['teacher_id' => $sc->teacher_id, 'school_id' => $sc->school_id],
                    ['is_primary' => false, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op data migration
    }
};
