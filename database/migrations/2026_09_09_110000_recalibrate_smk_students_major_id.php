<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengalibrasi seluruh siswa SMK ke 6 Jurusan (Konsentrasi Keahlian) resmi:
     * - TAV (Teknik Audio Video)
     * - DPIB (Desain Pemodelan dan Informasi Bangunan)
     * - TKR (Teknik Kendaraan Ringan)
     * - TSM (Teknik Sepeda Motor)
     * - TKJ (Teknik Komputer dan Jaringan)
     * - ACP (Axio Class Program)
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

        // 6 Konsentrasi Keahlian / Jurusan Resmi di SMK:
        $dpibId = $majorsByCode['DPIB'] ?? 8;
        $tavId  = $majorsByCode['TAV'] ?? 9;   // Jurusan Elektronika: TAV
        $tsmId  = $majorsByCode['TSM'] ?? 6;   // Jurusan Sepeda Motor: TSM
        $tkrId  = $majorsByCode['TKR'] ?? 7;   // Jurusan Mobil/Otomotif: TKR
        $tkjId  = $majorsByCode['TKJ'] ?? 10;  // Jurusan Komputer Jaringan: TKJ
        $acpId  = $majorsByCode['ACP'] ?? 14;  // Jurusan Axioo: ACP

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

            // 1. Regular classroom mapping ke Jurusan resmi
            if (preg_match('/\b(DPIB)\b/i', $allClassNames)) {
                $targetId = $dpibId;
            } elseif (preg_match('/\b(TE|TAV)\b/i', $allClassNames)) {
                $targetId = $tavId; // TE maupun TAV jurusannya adalah TAV
            } elseif (preg_match('/\b(ACP)\b/i', $allClassNames)) {
                $targetId = $acpId;
            } elseif (preg_match('/\b(TKJ|TJKT)\b/i', $allClassNames)) {
                $targetId = $tkjId; // TJKT maupun TKJ jurusannya adalah TKJ
            } elseif (preg_match('/\b(TSM|TBSM)\b/i', $allClassNames)) {
                $targetId = $tsmId;
            } elseif (preg_match('/\b(TKR|TO)\b/i', $allClassNames)) {
                $targetId = $tkrId; // TO maupun TKR jurusannya adalah TKR
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
