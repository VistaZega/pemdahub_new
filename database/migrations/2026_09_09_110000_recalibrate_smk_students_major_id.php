<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $smkSchools = DB::table('schools')->where('type', 'SMK')->pluck('id')->toArray();
        if (empty($smkSchools)) return;

        $majors = DB::table('majors')->whereIn('school_id', $smkSchools)->get();
        if ($majors->isEmpty()) return;

        $majorsByCode = [];
        foreach ($majors as $m) {
            $code = strtoupper($m->major_code ?? $m->code ?? '');
            if ($code) {
                $majorsByCode[$code] = $m->id;
            }
        }

        $dpibId = $majorsByCode['DPIB'] ?? null;
        $tavId  = $majorsByCode['TAV'] ?? null;
        $teId   = $majorsByCode['TE'] ?? null;
        $tsmId  = $majorsByCode['TSM'] ?? null;
        $tkrId  = $majorsByCode['TKR'] ?? null;
        $toId   = $majorsByCode['TO'] ?? null;
        $tkjId  = $majorsByCode['TKJ'] ?? null;
        $tjktId = $majorsByCode['TJKT'] ?? null;
        $acpId  = $majorsByCode['ACP'] ?? null;

        $students = DB::table('students')->whereIn('school_id', $smkSchools)->get();

        // Ambil seluruh student_classes
        $studentClasses = DB::table('student_classes')
            ->join('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
            ->whereIn('student_classes.student_id', $students->pluck('id'))
            ->select('student_classes.student_id', 'classrooms.id as classroom_id', 'classrooms.class_name', 'classrooms.is_combined', 'classrooms.class_type')
            ->get();

        $regMap = [];
        $combMap = [];
        foreach ($studentClasses as $sc) {
            if (!$sc->is_combined && $sc->class_type !== 'gabungan') {
                $regMap[$sc->student_id][] = $sc->class_name;
            } else {
                $combMap[$sc->student_id][] = $sc->classroom_id;
            }
        }

        // Siswa yang mengambil / hadir mapel DDPK-DPIB di kelas gabungan X Teknik Rekayasa
        $dpibAttStudents = DB::table('attendances')
            ->join('schedules', 'attendances.schedule_id', '=', 'schedules.id')
            ->join('subjects', 'schedules.subject_id', '=', 'subjects.id')
            ->where('subjects.name', 'like', '%DPIB%')
            ->pluck('attendances.student_id')
            ->unique()
            ->toArray();

        foreach ($students as $st) {
            $targetId = null;
            $allClassNames = implode(' ', $regMap[$st->id] ?? []);

            // 1. Regular classroom mapping dengan word boundary
            if (preg_match('/\b(DPIB)\b/i', $allClassNames)) {
                $targetId = $dpibId;
            } elseif (preg_match('/\b(TE)\b/i', $allClassNames)) {
                $targetId = $teId ?? $tavId;
            } elseif (preg_match('/\b(TAV)\b/i', $allClassNames)) {
                $targetId = $tavId ?? $teId;
            } elseif (preg_match('/\b(ACP)\b/i', $allClassNames)) {
                $targetId = $acpId ?? $tkjId;
            } elseif (preg_match('/\b(TJKT)\b/i', $allClassNames)) {
                $targetId = $tjktId ?? $tkjId;
            } elseif (preg_match('/\b(TKJ)\b/i', $allClassNames)) {
                $targetId = $tkjId;
            } elseif (preg_match('/\b(TSM|TBSM)\b/i', $allClassNames)) {
                $targetId = $tsmId;
            } elseif (preg_match('/\b(TKR)\b/i', $allClassNames)) {
                $targetId = $tkrId;
            } elseif (preg_match('/\b(TO)\b/i', $allClassNames)) {
                $targetId = $toId ?? $tkrId;
            }

            // 2. Jika siswa hanya terdaftar di kelas gabungan X Teknik Rekayasa (DPIB, TKR 2, TAV) (ID: 367)
            if (!$targetId && in_array(367, $combMap[$st->id] ?? [])) {
                if (in_array($st->id, $dpibAttStudents)) {
                    $targetId = $dpibId;
                } else {
                    $targetId = $tkrId;
                }
            }

            // 3. Jika siswa di kelas gabungan XII Teknik Rekayasa TAV TKJ (ID: 368)
            if (!$targetId && in_array(368, $combMap[$st->id] ?? [])) {
                $targetId = $tavId;
            }

            if ($targetId) {
                DB::table('students')->where('id', $st->id)->update(['major_id' => $targetId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
