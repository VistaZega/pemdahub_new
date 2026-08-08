<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlumniDirectory;
use App\Models\StudentAchievement;
use App\Models\Reputation;
use App\Models\News;
use App\Models\Setting;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    /**
     * Main Live Stream for LED Running Text (HD-WF2 / ESP32)
     * Supports multi-category live data:
     * 1. Pesan Alumni
     * 2. Siswa Berprestasi
     * 3. Siswa & Guru Terbaik (Reputasi/Penghargaan)
     * 4. Pengumuman & Berita Sekolah
     */
    public function getLiveFeed(Request $request)
    {
        $type = strtolower($request->input('type', 'all'));
        $format = strtolower($request->input('format', 'json'));
        $separator = $request->input('separator', '   ***   ');
        $limit = min((int) $request->input('limit', 10), 30);
        $schoolId = $request->input('school_id');

        $allFormattedItems = [];

        // ---------------------------------------------------------------------
        // 1. ANNOUNCEMENT & CUSTOM SETTING TEXT
        // ---------------------------------------------------------------------
        if (in_array($type, ['all', 'announcement', 'pengumuman'])) {
            $customAnnouncement = Setting::getValue('running_text_custom', null);
            if ($customAnnouncement) {
                $cleanAnnounce = trim(preg_replace('/\s+/', ' ', $customAnnouncement));
                $allFormattedItems[] = "[ 📢 PENGUMUMAN : " . $cleanAnnounce . " ]";
            }

            // Latest published news title
            $latestNews = News::where('is_published', true)->latest()->take(2)->get();
            foreach ($latestNews as $news) {
                $cleanTitle = trim(preg_replace('/\s+/', ' ', $news->title));
                $allFormattedItems[] = "[ 📰 BERITA PEMBDA : " . $cleanTitle . " ]";
            }
        }

        // ---------------------------------------------------------------------
        // 2. SISWA BERPRESTASI (STUDENT ACHIEVEMENTS)
        // ---------------------------------------------------------------------
        if (in_array($type, ['all', 'achievements', 'prestasi'])) {
            $achievementsQuery = StudentAchievement::with(['student.school'])
                ->latest('achievement_date');

            if ($schoolId) {
                $achievementsQuery->whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }

            $achievements = $achievementsQuery->take($limit)->get();
            foreach ($achievements as $ach) {
                $studentName = $ach->student ? $ach->student->full_name : 'Siswa Pembda';
                $schoolName = $ach->student && $ach->student->school ? $ach->student->school->name : 'Pembda';
                $rankStr = $ach->rank_label != '-' ? $ach->rank_label : 'Prestasi';
                $levelStr = $ach->level_label != '-' ? 'Tingkat ' . $ach->level_label : '';

                $cleanTitle = trim(preg_replace('/\s+/', ' ', $ach->title));

                $allFormattedItems[] = sprintf(
                    "[ 🏆 SISWA BERPRESTASI - %s (%s) : %s %s - %s ]",
                    $studentName,
                    $schoolName,
                    $rankStr,
                    $cleanTitle,
                    $levelStr
                );
            }
        }

        // ---------------------------------------------------------------------
        // 3. SISWA & GURU TERBAIK (TOP REPUTATION / AWARDS)
        // ---------------------------------------------------------------------
        if (in_array($type, ['all', 'best', 'terbaik'])) {
            // Top Students by Reputation
            $topStudents = Reputation::with(['user.school', 'user.student'])
                ->whereHas('user', function ($q) use ($schoolId) {
                    $q->where('role', 'siswa');
                    if ($schoolId) {
                        $q->where('school_id', $schoolId);
                    }
                })
                ->orderByDesc('total_points')
                ->take(3)
                ->get();

            foreach ($topStudents as $rep) {
                $u = $rep->user;
                if (!$u) continue;
                $name = $u->student ? $u->student->full_name : $u->name;
                $school = $u->school ? $u->school->name : 'Pembda';

                $allFormattedItems[] = sprintf(
                    "[ ⭐ SISWA TERBAIK - %s (%s) : Predikat %s (%d Poin) ]",
                    $name,
                    $school,
                    $rep->level_name ?? 'Bintang Sekolah',
                    $rep->total_points
                );
            }

            // Top Teachers by Reputation
            $topTeachers = Reputation::with(['user.school', 'user.teacher'])
                ->whereHas('user', function ($q) use ($schoolId) {
                    $q->whereIn('role', ['guru', 'teacher']);
                    if ($schoolId) {
                        $q->where('school_id', $schoolId);
                    }
                })
                ->orderByDesc('total_points')
                ->take(3)
                ->get();

            foreach ($topTeachers as $rep) {
                $u = $rep->user;
                if (!$u) continue;
                $name = $u->teacher ? $u->teacher->full_name : $u->name;
                $school = $u->school ? $u->school->name : 'Pembda';

                $allFormattedItems[] = sprintf(
                    "[ 🌟 GURU TERBAIK - %s (%s) : Predikat %s (%d Poin) ]",
                    $name,
                    $school,
                    $rep->level_name ?? 'Guru Teladan',
                    $rep->total_points
                );
            }
        }

        // ---------------------------------------------------------------------
        // 4. PESAN & KESAN ALUMNI
        // ---------------------------------------------------------------------
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

        // Fallback default message if empty
        if (empty($allFormattedItems)) {
            $allFormattedItems[] = "[ *** SELAMAT DATANG DI PERGURUAN PEMBDA *** ]";
        }

        $fullRunningTextString = implode($separator, $allFormattedItems);

        // Raw plain text response (for microcontrollers like ESP32)
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
