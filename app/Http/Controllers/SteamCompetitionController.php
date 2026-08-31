<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SteamCompetitionService;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SteamCompetitionController extends Controller
{
    /**
     * Check access permission
     */
    private function checkAccess()
    {
        $user = Auth::user();
        if (!SteamCompetitionService::canAccess($user)) {
            abort(403, 'Akses Terbatas: Halaman ini hanya dapat diakses oleh Super Admin, Kepala Sekolah, Pengurus Yayasan, Guru Pembimbing, dan Siswa Peserta Tim STEAMpreneur.');
        }
    }

    /**
     * Dashboard / Portal Utama STEAMpreneur di PembdaHUB
     */
    public function index()
    {
        $this->checkAccess();

        $user = Auth::user();
        $competitionData = SteamCompetitionService::getCompetitionData();
        $documents = SteamCompetitionService::getUploadedDocuments();
        
        // Ambil daftar seluruh siswa SMK aktif untuk pilihan hubungkan siswa peserta
        $smkStudents = Student::whereHas('school', function($q) {
            $q->whereIn('type', ['SMK', 'SMKS']);
        })->whereIn('status', ['aktif', 'active'])
          ->with(['currentClassroom', 'user'])
          ->orderBy('full_name')
          ->get();

        $isPrivileged = $user->isOwnerOrSuperAdmin() || $user->isKepalaSekolah() || $user->canAccessYayasan() || $user->hasRole('ketua_yayasan') || $user->role === 'guru';

        return view('steam.index', compact('competitionData', 'documents', 'smkStudents', 'isPrivileged', 'user'));
    }

    /**
     * Tampilan Proposal Resmi A4
     */
    public function proposal()
    {
        $this->checkAccess();
        $competitionData = SteamCompetitionService::getCompetitionData();
        return view('steam.proposal', compact('competitionData'));
    }

    /**
     * Tampilan Slide Pitch Deck Interaktif (12 Slide)
     */
    public function pitchDeck()
    {
        $this->checkAccess();
        $competitionData = SteamCompetitionService::getCompetitionData();
        return view('steam.pitch_deck', compact('competitionData'));
    }

    /**
     * Tampilan Naskah Video Presentasi 3–5 Menit
     */
    public function videoScript()
    {
        $this->checkAccess();
        $competitionData = SteamCompetitionService::getCompetitionData();
        return view('steam.video_script', compact('competitionData'));
    }

    /**
     * Upload berkas kelengkapan lomba
     */
    public function uploadDocument(Request $request)
    {
        $this->checkAccess();

        $request->validate([
            'document_file' => 'required|file|max:51200|mimes:pdf,zip,rar,doc,docx,ppt,pptx,mp4,jpg,jpeg,png',
            'doc_type' => 'required|string',
            'description' => 'nullable|string|max:255',
        ], [
            'document_file.required' => 'Pilih berkas yang akan diunggah.',
            'document_file.max' => 'Ukuran berkas maksimal 50 MB.',
            'document_file.mimes' => 'Format berkas harus berupa PDF, Word, PowerPoint, ZIP, RAR, MP4, atau Gambar.',
        ]);

        try {
            $description = $request->input('description');
            if (empty(trim((string)$description))) {
                $description = '-';
            }

            $docType = $request->input('doc_type', 'Lainnya') ?: 'Lainnya';

            $doc = SteamCompetitionService::saveDocument(
                $request->file('document_file'),
                $docType,
                $description,
                Auth::user()
            );

            return redirect()->route('steam.index')->with('success', 'Berkas "' . $doc['name'] . '" berhasil diunggah!');
        } catch (\Exception $e) {
            return redirect()->route('steam.index')->with('error', 'Gagal mengunggah berkas: ' . $e->getMessage());
        }
    }

    /**
     * Hapus berkas
     */
    public function deleteDocument($id)
    {
        $this->checkAccess();

        $user = Auth::user();
        $isPrivileged = $user->isOwnerOrSuperAdmin() || $user->isKepalaSekolah() || $user->canAccessYayasan() || $user->hasRole('ketua_yayasan');

        if (!$isPrivileged) {
            return redirect()->route('steam.index')->with('error', 'Hanya Super Admin, Kepala Sekolah, atau Yayasan yang dapat menghapus berkas.');
        }

        if (SteamCompetitionService::deleteDocument($id)) {
            return redirect()->route('steam.index')->with('success', 'Berkas berhasil dihapus.');
        }

        return redirect()->route('steam.index')->with('error', 'Berkas tidak ditemukan.');
    }

    /**
     * Update data tim, link video, & hubungkan siswa peserta secara otomatis
     */
    public function updateSettings(Request $request)
    {
        $this->checkAccess();

        $user = Auth::user();
        $isPrivileged = $user->isOwnerOrSuperAdmin() || $user->isKepalaSekolah() || $user->canAccessYayasan() || $user->hasRole('ketua_yayasan') || $user->role === 'guru';

        if (!$isPrivileged) {
            return redirect()->route('steam.index')->with('error', 'Anda tidak memiliki hak akses mengubah konfigurasi tim.');
        }

        if ($request->has('youtube_url')) {
            Setting::setValue('steam_youtube_url', $request->input('youtube_url'), 'string', 'steam_competition');
        }

        if ($request->has('gdrive_url')) {
            Setting::setValue('steam_gdrive_url', $request->input('gdrive_url'), 'string', 'steam_competition');
        }

        // Simpan Data Guru Pendamping (Default: Erwin Telaumbanua)
        if ($request->filled('mentor_name')) {
            Setting::setValue('steam_mentor_teacher', [
                'name' => $request->input('mentor_name'),
                'nip' => $request->input('mentor_nip', 'NPY. 2022000301'),
                'subject' => $request->input('mentor_subject', 'Guru Produktif PPLG & Pembimbing Inovasi')
            ], 'json', 'steam_competition');
        }

        $participantStudentIds = [];

        // 1. HUBUNGKAN KETUA TIM (SISWA 1)
        if ($request->filled('team_leader_student_id')) {
            $s1 = Student::find($request->input('team_leader_student_id'));
            if ($s1) {
                $c1 = $s1->studentClasses()->where('status', 'aktif')->latest('id')->first()?->classroom?->class_name ?? 'XII PPLG';
                Setting::setValue('steam_team_leader', [
                    'id' => $s1->id,
                    'name' => $s1->full_name,
                    'nisn' => $s1->nisn ?: $s1->nis,
                    'class' => $c1,
                    'role' => $request->input('team_leader_role', 'Ketua Tim / Programmer Backend & UI/UX')
                ], 'json', 'steam_competition');
                $participantStudentIds[] = $s1->id;
                if ($s1->user_id) $participantStudentIds[] = $s1->user_id;
            }
        } elseif ($request->input('clear_students') == '1') {
            Setting::setValue('steam_team_leader', [
                'id' => null,
                'name' => '[Belum Ditentukan - Silakan Pilih Siswa]',
                'nisn' => '....................',
                'class' => 'Kelas XII',
                'role' => 'Ketua Tim / Programmer Backend & UI/UX'
            ], 'json', 'steam_competition');
        }

        // 2. HUBUNGKAN ANGGOTA 1 (SISWA 2)
        if ($request->filled('member_1_student_id')) {
            $s2 = Student::find($request->input('member_1_student_id'));
            if ($s2) {
                $c2 = $s2->studentClasses()->where('status', 'aktif')->latest('id')->first()?->classroom?->class_name ?? 'XII TJKT';
                Setting::setValue('steam_team_member_1', [
                    'id' => $s2->id,
                    'name' => $s2->full_name,
                    'nisn' => $s2->nisn ?: $s2->nis,
                    'class' => $c2,
                    'role' => $request->input('member_1_role', 'Anggota 1 / Hardware IoT & Sirkuit ESP32')
                ], 'json', 'steam_competition');
                $participantStudentIds[] = $s2->id;
                if ($s2->user_id) $participantStudentIds[] = $s2->user_id;
            }
        } elseif ($request->input('clear_students') == '1') {
            Setting::setValue('steam_team_member_1', [
                'id' => null,
                'name' => '[Belum Ditentukan - Silakan Pilih Siswa]',
                'nisn' => '....................',
                'class' => 'Kelas XII',
                'role' => 'Anggota 1 / Hardware IoT & Sirkuit ESP32'
            ], 'json', 'steam_competition');
        }

        // 3. HUBUNGKAN ANGGOTA 2 (SISWA 3)
        if ($request->filled('member_2_student_id')) {
            $s3 = Student::find($request->input('member_2_student_id'));
            if ($s3) {
                $c3 = $s3->studentClasses()->where('status', 'aktif')->latest('id')->first()?->classroom?->class_name ?? 'XII PPLG';
                Setting::setValue('steam_team_member_2', [
                    'id' => $s3->id,
                    'name' => $s3->full_name,
                    'nisn' => $s3->nisn ?: $s3->nis,
                    'class' => $c3,
                    'role' => $request->input('member_2_role', 'Anggota 2 / Analisis Bisnis, TeFa & Student DNA')
                ], 'json', 'steam_competition');
                $participantStudentIds[] = $s3->id;
                if ($s3->user_id) $participantStudentIds[] = $s3->user_id;
            }
        } elseif ($request->input('clear_students') == '1') {
            Setting::setValue('steam_team_member_2', [
                'id' => null,
                'name' => '[Belum Ditentukan - Silakan Pilih Siswa]',
                'nisn' => '....................',
                'class' => 'Kelas XII',
                'role' => 'Anggota 2 / Analisis Bisnis, TeFa & Student DNA'
            ], 'json', 'steam_competition');
        }

        // Simpan array ID peserta lomba (otomatis memunculkan menu di akun siswa tersebut)
        if (!empty($participantStudentIds) || $request->input('clear_students') == '1') {
            Setting::setValue('steam_participant_student_ids', array_values(array_unique($participantStudentIds)), 'json', 'steam_competition');
        }

        return redirect()->route('steam.index')->with('success', 'Konfigurasi tim berhasil disimpan! Menu lomba otomatis aktif di akun siswa yang ditunjuk.');
    }
}
