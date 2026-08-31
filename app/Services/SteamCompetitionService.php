<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SteamCompetitionService
{
    /**
     * Cek apakah user berhak mengakses menu STEAMpreneur
     * (Super Admin, Kepala Sekolah, Pengurus Yayasan, Guru Pendamping, & Siswa Peserta yang dihubungkan)
     */
    public static function canAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Super Admin / Owner / Developer
        if ($user->isOwnerOrSuperAdmin() || $user->hasRole('superadmin') || $user->username === 'yulzega') {
            return true;
        }

        // 2. Kepala Sekolah
        if ($user->isKepalaSekolah() || $user->hasRole('kepala_sekolah')) {
            return true;
        }

        // 3. Pengurus Yayasan / Ketua Yayasan
        if ($user->canAccessYayasan() || $user->isKetuaYayasan() || $user->hasRole('ketua_yayasan') || $user->hasRole('yayasan')) {
            return true;
        }

        // 4. Guru Pendamping (Erwin Telaumbanua atau ID guru yang didaftarkan)
        if ($user->role === 'guru') {
            $mentorData = Setting::getValue('steam_mentor_teacher', []);
            $mentorName = $mentorData['name'] ?? 'Erwin Telaumbanua';
            if (stripos($user->name, 'Erwin') !== false || stripos($user->name, 'Telaumbanua') !== false) {
                return true;
            }
            $mentorIds = Setting::getValue('steam_mentor_teacher_ids', []);
            if (!empty($mentorIds) && in_array($user->id, $mentorIds)) {
                return true;
            }
        }

        // 5. Siswa Peserta Lomba yang telah dihubungkan/ditunjuk
        if ($user->role === 'siswa') {
            return static::isStudentParticipant($user);
        }

        return false;
    }

    /**
     * Cek apakah siswa berstatus peserta lomba yang telah dihubungkan
     */
    public static function isStudentParticipant(?User $user): bool
    {
        if (!$user || $user->role !== 'siswa') {
            return false;
        }

        $participantStudentIds = Setting::getValue('steam_participant_student_ids', []);
        
        // Jika ada siswa yang secara eksplisit dihubungkan
        if (!empty($participantStudentIds)) {
            $student = $user->student;
            if ($student && in_array($student->id, $participantStudentIds)) {
                return true;
            }
            if (in_array($user->id, $participantStudentIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ambil data lengkap informasi lomba & tim
     */
    public static function getCompetitionData(): array
    {
        $defaultMentor = [
            'name' => 'Erwin Telaumbanua, S.Kom., Gr.',
            'nip' => 'NPY. 2022000301',
            'subject' => 'Guru Kejuruan PPLG & Pembimbing Inovasi SMKS Pembda Nias'
        ];

        $defaultTeamLeader = [
            'id' => null,
            'name' => '[Belum Ditentukan - Silakan Pilih Siswa di Menu STEAM]',
            'nisn' => '....................',
            'class' => 'Kelas XII',
            'role' => 'Ketua Tim / Programmer Backend & UI/UX'
        ];

        $defaultMember1 = [
            'id' => null,
            'name' => '[Belum Ditentukan - Silakan Pilih Siswa di Menu STEAM]',
            'nisn' => '....................',
            'class' => 'Kelas XII',
            'role' => 'Anggota 1 / Hardware IoT & Sirkuit ESP32'
        ];

        $defaultMember2 = [
            'id' => null,
            'name' => '[Belum Ditentukan - Silakan Pilih Siswa di Menu STEAM]',
            'nisn' => '....................',
            'class' => 'Kelas XII',
            'role' => 'Anggota 2 / Analisis Bisnis, TeFa & Student DNA'
        ];

        $defaultData = [
            'competition_name' => 'STEAMpreneur SMK 2026',
            'organizer' => 'Direktorat Sekolah Menengah Kejuruan, Kemendikdasmen',
            'theme' => 'Solusi Nyata Untuk Indonesia',
            'category' => 'Bidang Teknologi Informasi (SMK IT) — High-Fidelity Prototype',
            'title' => 'PEMBDA-HUB: Ekosistem Smart School dan Kiosk IoT Berbasis Resilient-Edge untuk Transformasi Digital Vokasi Kepulauan Nias',
            'school_name' => 'SMK Swasta Pembda Nias (NPSN: 20220003)',
            'foundation_name' => 'Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)',
            'school_address' => 'Jl. Pelita No. 09 Kelurahan Ilir Kota Gunungsitoli',
            'tefa_address' => 'Jl. Pelita No. 09 Kota Gunungsitoli (www.bengkelin.cloud)',
            'video_duration' => '04:15 (Toleransi Juknis: 3–5 Menit)',
            'pitch_deck_slides' => 12,
            'kiosk_hpp' => 320000,
            'kiosk_market_price' => 850000,
            'kiosk_margin' => '62.35%',
            'kiosk_bep_units' => 5,
            'youtube_video_url' => Setting::getValue('steam_youtube_url', 'https://youtu.be/pembdahub-steam2026'),
            'google_drive_url' => Setting::getValue('steam_gdrive_url', ''),
            'team_leader' => Setting::getValue('steam_team_leader', $defaultTeamLeader),
            'team_member_1' => Setting::getValue('steam_team_member_1', $defaultMember1),
            'team_member_2' => Setting::getValue('steam_team_member_2', $defaultMember2),
            'mentor_teacher' => Setting::getValue('steam_mentor_teacher', $defaultMentor),
            'principal' => Setting::getValue('steam_principal', [
                'name' => 'Kepala SMK Swasta Pembda Nias',
                'nip' => 'NIP/NPY. ...........................'
            ]),
        ];

        return $defaultData;
    }

    /**
     * Ambil daftar berkas dokumen yang diupload
     */
    public static function getUploadedDocuments(): array
    {
        return Setting::getValue('steam_competition_documents', []);
    }

    /**
     * Simpan berkas upload baru
     */
    public static function saveDocument($file, string $docType = 'Dokumen', ?string $description = '-', ?User $user = null): array
    {
        $description = !empty(trim((string)$description)) ? trim((string)$description) : '-';
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
        $path = $file->storeAs('steam_competition', $fileName, 'public');

        $docId = uniqid('doc_');
        $newDoc = [
            'id' => $docId,
            'type' => $docType ?: 'Dokumen',
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'url' => Storage::url($path),
            'size' => $file->getSize(),
            'size_human' => round($file->getSize() / 1024, 1) . ' KB',
            'extension' => strtolower($file->getClientOriginalExtension()),
            'description' => $description,
            'uploaded_by' => $user ? $user->name : 'Sistem',
            'uploaded_at' => now()->format('Y-m-d H:i:s'),
        ];

        $documents = static::getUploadedDocuments();
        $documents[] = $newDoc;
        Setting::setValue('steam_competition_documents', $documents, 'json', 'steam_competition');

        return $newDoc;
    }

    /**
     * Hapus berkas upload
     */
    public static function deleteDocument(string $docId): bool
    {
        $documents = static::getUploadedDocuments();
        $foundIndex = null;
        $filePath = null;

        foreach ($documents as $index => $doc) {
            if ($doc['id'] === $docId) {
                $foundIndex = $index;
                $filePath = $doc['path'];
                break;
            }
        }

        if ($foundIndex !== null) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            unset($documents[$foundIndex]);
            Setting::setValue('steam_competition_documents', array_values($documents), 'json', 'steam_competition');
            return true;
        }

        return false;
    }
}
