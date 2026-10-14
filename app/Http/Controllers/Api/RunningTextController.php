<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlumniDirectory;
use App\Models\Reputation;
use App\Models\StudentAchievement;
use App\Models\StudentDevelopmentNote;
use App\Services\RunningTextService;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    /**
     * Live Feed API for Running Text (HD-WF2 / ESP32)
     * Menampilkan 7 kategori playlist informasi berjalan + real-time interrupt sapaan absensi RFID
     */
    public function getLiveFeed(Request $request)
    {
        $type = strtolower($request->input('type', 'all'));
        $format = strtolower($request->input('format', 'json'));
        $separator = $request->input('separator', '   ***   ');
        $limit = min((int) $request->input('limit', 3), 10);
        $schoolId = $request->input('school_id');

        // Cek apakah ada selaan / interupsi tap absensi dalam 5 detik terakhir
        $activeInterrupt = RunningTextService::getActiveInterrupt();
        $hasInterrupt = !empty($activeInterrupt);

        $allFormattedItems = [];

        // Jika meminta jenis spesifik legacy
        if ($type === 'alumni' || $type === 'pesan') {
            $alumniQuery = AlumniDirectory::with('school')
                ->where('is_approved', true)
                ->whereNotNull('message')
                ->where('message', '!=', '')
                ->latest();

            if ($schoolId) {
                $alumniQuery->where('school_id', $schoolId);
            }

            $alumnis = $alumniQuery->take($limit * 3)->get();
            foreach ($alumnis as $alumni) {
                $schoolName = $alumni->school ? $alumni->school->name : 'PEMBDA';
                $cleanMsg = trim(preg_replace('/\s+/', ' ', $alumni->message));

                $allFormattedItems[] = sprintf(
                    "[ 🎓 PESAN ALUMNI - %s (%s '%s) : \"%s\" ]",
                    $alumni->full_name,
                    $schoolName,
                    $alumni->graduation_year,
                    $cleanMsg
                );
            }
        } elseif ($type === 'hall_of_fame' || $type === 'hof' || $type === 'reputasi') {
            $hofQuery = Reputation::with(['user.school', 'user.student', 'user.teacher'])
                ->orderByDesc('total_points');

            if ($schoolId) {
                $hofQuery->whereHas('user', fn($q) => $q->where('school_id', $schoolId));
            }

            $hofList = $hofQuery->take($limit * 3)->get();
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
        } else {
            // Mode Default 'all': Menghimpun 7 Kategori Lengkap
            // 1. Berita & Pengumuman
            // 2. Prestasi Siswa
            // 3. Agenda Kegiatan Hari Ini
            // 4. Pojok Literasi & Motivasi
            // 5. Judul Penelitian Akhir SMA
            // 6. Judul Project Kejuruan SMK
            // 7. Pesan dan Kesan Alumni
            $playlistObjects = RunningTextService::getContinuousPlaylist($limit, $schoolId);
            foreach ($playlistObjects as $p) {
                $allFormattedItems[] = $p['text'];
            }
        }

        if (empty($allFormattedItems)) {
            $allFormattedItems[] = "[ *** SELAMAT DATANG DI PERGURUAN PEMBDA NIAS *** ]";
        }

        $fullRunningTextString = implode($separator, $allFormattedItems);

        // Jika ada interupsi aktif, teks tampilan layar yang diprioritaskan adalah interupsi absensi
        $activeDisplayText = $hasInterrupt ? $activeInterrupt['display_text'] : $fullRunningTextString;

        // Return raw plain text (ideal untuk microcontroller ESP32 atau bridge Huidu)
        if ($format === 'raw' || $format === 'text' || $request->is('*/raw')) {
            return response($activeDisplayText, 200)
                ->header('Content-Type', 'text/plain; charset=utf-8');
        }

        return response()->json([
            'status'         => 'success',
            'has_interrupt'  => $hasInterrupt,
            'interrupt'      => $activeInterrupt,
            'display_text'   => $activeDisplayText,
            'running_text'   => $fullRunningTextString,
            'total_items'    => count($allFormattedItems),
            'separator'      => $separator,
            'items'          => $allFormattedItems,
        ]);
    }

    /**
     * Endpoint khusus untuk mengecek event selaan / interupsi tap RFID saja
     * GET /api/running-text/interrupt
     */
    public function getInterrupt(Request $request)
    {
        $activeInterrupt = RunningTextService::getActiveInterrupt();

        return response()->json([
            'status'        => 'success',
            'has_interrupt' => !empty($activeInterrupt),
            'interrupt'     => $activeInterrupt,
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
