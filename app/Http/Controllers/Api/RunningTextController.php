<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlumniDirectory;
use App\Models\Reputation;
use App\Models\StudentAchievement;
use App\Models\StudentDevelopmentNote;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    /**
     * Live Feed API for Running Text (HD-WF2 / ESP32)
     * Data Sources:
     * 1. Pesan dan Kesan Alumni
     * 2. Hall Of Fame (Reputasi & Prestasi Tertinggi)
     * 3. Catatan Perkembangan Siswa (Prestasi & Catatan Positif)
     */
    public function getLiveFeed(Request $request)
    {
        $type = strtolower($request->input('type', 'all'));
        $format = strtolower($request->input('format', 'json'));
        $separator = $request->input('separator', '   ***   ');
        $limit = min((int) $request->input('limit', 10), 30);
        $schoolId = $request->input('school_id');

        $allFormattedItems = [];

        // =====================================================================
        // 1. PESAN DAN KESAN ALUMNI
        // =====================================================================
        if (in_array($type, ['all', 'alumni', 'pesan'])) {
            $alumniQuery = AlumniDirectory::with('school')
                ->where('is_approved', true)
                ->whereNotNull('message')
                ->where('message', '!=', '')
                ->latest();

            if ($schoolId) {
                $alumniQuery->where('school_id', $schoolId);
            }

            $alumnis = $alumniQuery->take($limit)->get();
            foreach ($alumnis as $alumni) {
                $schoolName = $alumni->school ? $alumni->school->name : 'PEMBDA';
                $cleanMsg = trim(preg_replace('/\s+/', ' ', $alumni->message));

                $allFormattedItems[] = sprintf(
                    "[ 🎓 PESAN ALUMNI - %s (%s - %s) : \"%s\" ]",
                    $alumni->full_name,
                    $schoolName,
                    $alumni->graduation_year,
                    $cleanMsg
                );
            }
        }

        // =====================================================================
        // 2. HALL OF FAME (REPUTASI TERBAIK SISWA & GURU)
        // =====================================================================
        if (in_array($type, ['all', 'hall_of_fame', 'hof', 'reputasi'])) {
            $hofQuery = Reputation::with(['user.school', 'user.student', 'user.teacher'])
                ->orderByDesc('total_points');

            if ($schoolId) {
                $hofQuery->whereHas('user', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }

            $hofList = $hofQuery->take($limit)->get();
            foreach ($hofList as $index => $rep) {
                $u = $rep->user;
                if (!$u) continue;

                $name = $u->student ? $u->student->full_name : ($u->teacher ? $u->teacher->full_name : $u->name);
                $schoolName = $u->school ? $u->school->name : 'PEMBDA';
                $roleLabel = strtolower($u->role) === 'siswa' ? 'Siswa' : 'Guru/Pegawai';
                $rankNum = $index + 1;

                $allFormattedItems[] = sprintf(
                    "[ 🏆 HALL OF FAME #%d - %s (%s - %s) : Predikat %s (%d Poin) ]",
                    $rankNum,
                    $name,
                    $roleLabel,
                    $schoolName,
                    $rep->level_name ?? 'Bintang Sekolah',
                    $rep->total_points
                );
            }
        }

        // =====================================================================
        // 3. CATATAN PERKEMBANGAN SISWA (PRESTASI & OBSERVASI POSITIF)
        // =====================================================================
        if (in_array($type, ['all', 'prestasi', 'development', 'catatan'])) {
            // A. Prestasi Resmi (StudentAchievement)
            $achievementsQuery = StudentAchievement::with(['student.school'])
                ->latest('achievement_date');

            if ($schoolId) {
                $achievementsQuery->whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }

            $achievements = $achievementsQuery->take($limit)->get();
            foreach ($achievements as $ach) {
                $studentName = $ach->student ? $ach->student->full_name : 'Siswa';
                $schoolName = $ach->student && $ach->student->school ? $ach->student->school->name : 'PEMBDA';
                $rankStr = $ach->rank_label != '-' ? $ach->rank_label : 'Prestasi';
                $levelStr = $ach->level_label != '-' ? 'Tingkat ' . $ach->level_label : '';
                $cleanTitle = trim(preg_replace('/\s+/', ' ', $ach->title));

                $allFormattedItems[] = sprintf(
                    "[ ⭐ PRESTASI SISWA - %s (%s) : %s %s - %s ]",
                    $studentName,
                    $schoolName,
                    $rankStr,
                    $cleanTitle,
                    $levelStr
                );
            }

            // B. Catatan Perkembangan Siswa (StudentDevelopmentNote)
            $devNotesQuery = StudentDevelopmentNote::with(['student.school'])
                ->latest('created_at');

            if ($schoolId) {
                $devNotesQuery->where('school_id', $schoolId);
            }

            $devNotes = $devNotesQuery->take($limit)->get();
            foreach ($devNotes as $note) {
                $studentName = $note->student ? $note->student->full_name : 'Siswa';
                $schoolName = $note->school ? $note->school->name : 'PEMBDA';
                $aspectStr = ucfirst($note->aspect);
                $cleanObs = trim(preg_replace('/\s+/', ' ', $note->observation));

                $allFormattedItems[] = sprintf(
                    "[ 📈 CATATAN PERKEMBANGAN SISWA - %s (%s - Aspect %s) : \"%s\" ]",
                    $studentName,
                    $schoolName,
                    $aspectStr,
                    $cleanObs
                );
            }
        }

        // Fallback default message if empty
        if (empty($allFormattedItems)) {
            $allFormattedItems[] = "[ *** SELAMAT DATANG DI PERGURUAN PEMBDA *** ]";
        }

        $fullRunningTextString = implode($separator, $allFormattedItems);

        // Return raw plain text if requested (for microcontrollers like ESP32)
        if ($format === 'raw' || $format === 'text' || $request->is('*/raw')) {
            return response($fullRunningTextString, 200)
                ->header('Content-Type', 'text/plain; charset=utf-8');
        }

        return response()->json([
            'status' => 'success',
            'type' => $type,
            'total_items' => count($allFormattedItems),
            'separator' => $separator,
            'running_text' => $fullRunningTextString,
            'items' => $allFormattedItems,
        ]);
    }

    /**
     * Backward-compatible helper for Alumni only
     */
    public function getAlumniMessages(Request $request)
    {
        $request->merge(['type' => 'alumni']);
        return $this->getLiveFeed($request);
    }
}
