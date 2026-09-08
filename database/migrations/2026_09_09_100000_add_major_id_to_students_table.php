<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'major_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('major_id')->nullable()->after('school_id')->constrained('majors')->onDelete('set null');
                $table->index('major_id');
            });
        }

        // Ambil daftar seluruh jurusan
        $majors = DB::table('majors')->get();
        if ($majors->isEmpty()) {
            return;
        }

        $teMajor   = $majors->first(fn($m) => in_array(strtoupper($m->code ?? $m->major_code ?? ''), ['TE', 'TAV']) || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'ELEKTRONIKA') || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'AUDIO'));
        $dpibMajor = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'DPIB' || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'PEMODELAN') || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'BANGUNAN'));
        $tsmMajor  = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'TSM' || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'SEPEDA MOTOR'));
        $tkrMajor  = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'TKR' || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'KENDARAAN RINGAN') || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'OTOMOTIF'));
        $tkjMajor  = $majors->first(fn($m) => in_array(strtoupper($m->code ?? $m->major_code ?? ''), ['TKJ', 'TJKT']) || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'JARINGAN') || str_contains(strtoupper($m->name ?? $m->major_name ?? ''), 'KOMPUTER'));
        $ipaMajor  = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'IPA');
        $ipsMajor  = $majors->first(fn($m) => strtoupper($m->code ?? $m->major_code ?? '') === 'IPS');

        // 1. Populasikan siswa berdasarkan mapel Konsentrasi Keahlian yang diambil di LMS / Nilai
        if ($teMajor) {
            $teStudentIds = DB::table('lms_enrollments')
                ->join('lms_classes', 'lms_enrollments.lms_class_id', '=', 'lms_classes.id')
                ->join('lms_courses', 'lms_classes.course_id', '=', 'lms_courses.id')
                ->leftJoin('subjects', 'lms_courses.subject_id', '=', 'subjects.id')
                ->where(function($q) {
                    $q->where('subjects.name', 'like', '%TE%')
                      ->orWhere('subjects.name', 'like', '%Elektronika%')
                      ->orWhere('subjects.name', 'like', '%Audio%')
                      ->orWhere('subjects.subject_name', 'like', '%TE%')
                      ->orWhere('subjects.subject_name', 'like', '%Elektronika%')
                      ->orWhere('subjects.subject_name', 'like', '%Audio%')
                      ->orWhere('lms_courses.course_name', 'like', '%Mikrokontroler%')
                      ->orWhere('lms_courses.course_name', 'like', '%Audio Video%');
                })
                ->pluck('lms_enrollments.student_id')
                ->unique();

            if ($teStudentIds->isNotEmpty()) {
                DB::table('students')->whereIn('id', $teStudentIds)->whereNull('major_id')->update(['major_id' => $teMajor->id]);
            }
        }

        if ($dpibMajor) {
            $dpibStudentIds = DB::table('lms_enrollments')
                ->join('lms_classes', 'lms_enrollments.lms_class_id', '=', 'lms_classes.id')
                ->join('lms_courses', 'lms_classes.course_id', '=', 'lms_courses.id')
                ->leftJoin('subjects', 'lms_courses.subject_id', '=', 'subjects.id')
                ->where(function($q) {
                    $q->where('subjects.name', 'like', '%DPIB%')
                      ->orWhere('subjects.name', 'like', '%Konstruksi%')
                      ->orWhere('subjects.name', 'like', '%Bangunan%')
                      ->orWhere('subjects.subject_name', 'like', '%DPIB%')
                      ->orWhere('subjects.subject_name', 'like', '%Konstruksi%')
                      ->orWhere('subjects.subject_name', 'like', '%Bangunan%')
                      ->orWhere('lms_courses.course_name', 'like', '%DPIB%')
                      ->orWhere('lms_courses.course_name', 'like', '%Konstruksi%');
                })
                ->pluck('lms_enrollments.student_id')
                ->unique();

            if ($dpibStudentIds->isNotEmpty()) {
                DB::table('students')->whereIn('id', $dpibStudentIds)->whereNull('major_id')->update(['major_id' => $dpibMajor->id]);
            }
        }

        // 2. Populasikan siswa lainnya berdasarkan kelas reguler / nama kelas yang memiliki keyword jurusan
        $keywordMap = [
            'DPIB' => $dpibMajor?->id,
            'TAV'  => $teMajor?->id,
            'TE'   => $teMajor?->id,
            'TSM'  => $tsmMajor?->id,
            'TBSM' => $tsmMajor?->id,
            'TKR'  => $tkrMajor?->id,
            'TKJ'  => $tkjMajor?->id,
            'TJKT' => $tkjMajor?->id,
            'ACP'  => $tkjMajor?->id,
            'IPA'  => $ipaMajor?->id,
            'IPS'  => $ipsMajor?->id,
        ];

        foreach ($keywordMap as $kw => $majId) {
            if (!$majId) continue;
            $stdIds = DB::table('student_classes')
                ->join('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
                ->where('classrooms.is_combined', false)
                ->where('classrooms.class_type', '!=', 'gabungan')
                ->where('classrooms.class_name', 'LIKE', "%{$kw}%")
                ->pluck('student_classes.student_id')
                ->unique();

            if ($stdIds->isNotEmpty()) {
                DB::table('students')->whereIn('id', $stdIds)->whereNull('major_id')->update(['major_id' => $majId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('students', 'major_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['major_id']);
                $table->dropColumn('major_id');
            });
        }
    }
};
