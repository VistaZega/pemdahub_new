<?php

namespace App\Http\Controllers;

use App\Models\AlumniDirectory;
use App\Models\EducationalCalendar;
use App\Models\FinalProject;
use App\Models\News;
use App\Models\Setting;
use App\Models\StudentAchievement;
use App\Services\RunningTextService;
use Illuminate\Http\Request;

class RunningTextWebController extends Controller
{
    /**
     * Dashboard Kontrol & Live Simulator Running Text (/runteks)
     */
    public function index(Request $request)
    {
        // Cek autentikasi (Login admin atau PIN sesi 'pembda99')
        $isUnlocked = auth()->check() || $request->session()->get('runteks_unlocked', false) || $request->query('secret') === 'pembda99';
        if ($request->query('secret') === 'pembda99') {
            $request->session()->put('runteks_unlocked', true);
            $isUnlocked = true;
        }

        $controllerIp = Setting::getValue('running_text_ip', '50.35.89.150');
        $activeInterrupt = RunningTextService::getActiveInterrupt();
        $emergencyBroadcast = RunningTextService::getEmergencyBroadcast();
        $playlist = RunningTextService::getContinuousPlaylist(4);

        // Statistik Data Riil PembdaHUB
        $stats = [
            'total_news'         => News::published()->whereIn('category', ['pengumuman', 'kegiatan'])->count(),
            'total_achievements' => StudentAchievement::count(),
            'total_agenda_today' => EducationalCalendar::where('start_date', '<=', now()->toDateString())
                                      ->where('end_date', '>=', now()->toDateString())->count(),
            'total_research_sma' => FinalProject::where('type', 'penelitian_ilmiah')->whereNotIn('status', ['rejected', 'draft'])->count(),
            'total_project_smk'  => FinalProject::where('type', 'project_akhir')->whereNotIn('status', ['rejected', 'draft'])->count(),
            'total_alumni_msg'   => AlumniDirectory::where('is_approved', true)->whereNotNull('message')->where('message', '!=', '')->count(),
        ];

        return view('runteks.index', compact(
            'isUnlocked',
            'controllerIp',
            'activeInterrupt',
            'emergencyBroadcast',
            'playlist',
            'stats'
        ));
    }

    /**
     * API AJAX untuk update status live simulator di browser
     */
    public function getLiveStatus(Request $request)
    {
        $activeInterrupt = RunningTextService::getActiveInterrupt();
        $emergencyBroadcast = RunningTextService::getEmergencyBroadcast();
        $playlist = RunningTextService::getContinuousPlaylist(3);

        $items = array_map(fn($p) => $p['text'], $playlist);
        $fullText = implode('   ***   ', $items);

        return response()->json([
            'status'             => 'success',
            'has_interrupt'      => !empty($activeInterrupt),
            'interrupt'          => $activeInterrupt,
            'has_emergency'      => !empty($emergencyBroadcast),
            'emergency'          => $emergencyBroadcast,
            'active_display'     => $activeInterrupt ? $activeInterrupt['display_text'] : $fullText,
            'running_text'       => $fullText,
            'playlist_count'     => count($playlist),
            'timestamp'          => time(),
        ]);
    }

    /**
     * Uji Coba / Simulasi Tap RFID (Masuk / Pulang)
     */
    public function simulateTap(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'subtext'   => 'nullable|string|max:100',
            'school'    => 'nullable|string|max:100',
            'action'    => 'required|in:MASUK,PULANG',
            'status'    => 'nullable|string|max:50',
            'time'      => 'nullable|string|max:10',
        ]);

        $data = RunningTextService::recordInterrupt(
            $request->input('name'),
            $request->input('subtext', 'X - Budi Utomo'),
            $request->input('action', 'MASUK'),
            $request->input('time') ?: date('H:i'),
            $request->input('status', 'Terlambat'),
            $request->input('school', 'SMAS Pembda 1')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Simulasi selaan absensi berhasil dikirim ke layar!',
            'data'    => $data,
        ]);
    }

    /**
     * Pasang Pengumuman Darurat / Kilat (Breaking News)
     */
    public function setEmergency(Request $request)
    {
        $request->validate([
            'text'     => 'required|string|max:500',
            'duration' => 'required|integer|min:1|max:1440',
        ]);

        $data = RunningTextService::setEmergencyBroadcast(
            $request->input('text'),
            (int) $request->input('duration', 15)
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengumuman darurat berhasil dipasang di posisi terdepan layar!',
            'data'    => $data,
        ]);
    }

    /**
     * Hapus Pengumuman Darurat
     */
    public function clearEmergency(Request $request)
    {
        RunningTextService::clearEmergencyBroadcast();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengumuman darurat telah dinonaktifkan.',
        ]);
    }

    /**
     * Simpan Pengaturan Controller IP
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'controller_ip' => 'required|string|max:50',
        ]);

        Setting::setValue('running_text_ip', trim($request->input('controller_ip')), 'string', 'running_text');

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat IP Controller berhasil disimpan.',
        ]);
    }

    /**
     * Unlock kontrol panel menggunakan PIN admin
     */
    public function unlock(Request $request)
    {
        $pin = trim($request->input('pin', ''));
        if ($pin === 'pembda99' || $pin === 'pembda2026') {
            $request->session()->put('runteks_unlocked', true);
            return response()->json(['status' => 'success', 'message' => 'Akses kontrol berhasil dibuka!']);
        }

        return response()->json(['status' => 'error', 'message' => 'PIN salah! Silakan coba lagi.'], 403);
    }
}
