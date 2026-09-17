<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\School;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvitationController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function index(Request $request)
    {
        $selectedSchoolId = $request->get('school_id');
        $selectedRole = $request->get('role_type');

        // Fetch all active schools
        $schools = School::schoolsOnly()->where('is_active', true)->get();

        // Get active employees
        $query = Employee::active()->with(['school', 'user', 'positions']);

        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        }

        $employees = $query->get();
        $invitationsList = [];

        // Fetch WhatsApp notification logs to see delivery status
        $whatsappLogs = DB::table('notification_logs')
            ->where('channel', 'whatsapp')
            ->where('message', 'like', '%Pelatihan Aplikasi Pembda HUB%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('recipient');

        foreach ($employees as $emp) {
            $roleType = $this->determineRoleType($emp);

            // Filter by role type if requested
            if ($selectedRole && $roleType !== $selectedRole) {
                continue;
            }

            $message = $this->generateMessage($emp, $roleType);
            
            // Format phone number for checking logs
            $normalizedPhone = $this->normalizePhoneNumber($emp->phone);
            $latestLog = $whatsappLogs->get($normalizedPhone)?->first();

            $invitationsList[] = [
                'employee_id' => $emp->id,
                'name' => $emp->full_name,
                'school_name' => $emp->school->name ?? 'N/A',
                'school_id' => $emp->school_id,
                'role_type' => $roleType,
                'role_label' => $this->getRoleLabel($roleType),
                'phone' => $emp->phone,
                'position' => $this->getEmployeePositionName($emp),
                'message' => $message,
                'status' => $latestLog ? $latestLog->status : 'belum_dikirim',
                'sent_at' => $latestLog ? $latestLog->sent_at : null,
            ];
        }

        return view('yayasan.invitations', compact('invitationsList', 'schools', 'selectedSchoolId', 'selectedRole'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'message' => 'required|string',
        ]);

        $employee = Employee::find($request->employee_id);

        if (empty($employee->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP pegawai ini kosong.',
            ], 422);
        }

        $phone = $employee->phone;
        $message = $request->message;

        // Send message using WhatsAppService
        $result = $this->whatsappService->sendMessage($phone, $message);

        // Record in log
        DB::table('notification_logs')->insert([
            'notification_id' => null,
            'channel' => 'whatsapp',
            'recipient' => $this->normalizePhoneNumber($phone),
            'message' => $message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'response' => json_encode($result),
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Undangan berhasil dikirim ke ' . $employee->full_name,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim undangan: ' . ($result['error'] ?? 'API Fonnte Error'),
        ], 500);
    }

    public function sendBulk(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*.employee_id' => 'required|exists:employees,id',
            'recipients.*.message' => 'required|string',
        ]);

        $recipientsData = $request->recipients;
        $bulkRecipients = [];

        foreach ($recipientsData as $data) {
            $employee = Employee::find($data['employee_id']);
            if ($employee && !empty($employee->phone)) {
                $bulkRecipients[] = [
                    'phone' => $employee->phone,
                    'message' => $data['message'],
                    'options' => [],
                ];

                // Insert pending logs
                DB::table('notification_logs')->insert([
                    'notification_id' => null,
                    'channel' => 'whatsapp',
                    'recipient' => $this->normalizePhoneNumber($employee->phone),
                    'message' => $data['message'],
                    'status' => 'pending',
                    'response' => 'Queued in bulk dispatch',
                    'sent_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (empty($bulkRecipients)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada penerima dengan nomor HP valid.',
            ], 422);
        }

        // Send bulk using WhatsAppService (delayed queues with Anti-Ban Pacing)
        $result = $this->whatsappService->sendBulk($bulkRecipients, 12);

        // Update status to 'sent' (since they are pushed to database queue jobs successfully)
        foreach ($bulkRecipients as $recipient) {
            DB::table('notification_logs')
                ->where('channel', 'whatsapp')
                ->where('recipient', $this->normalizePhoneNumber($recipient['phone']))
                ->where('status', 'pending')
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($bulkRecipients) . ' undangan pelatihan berhasil masuk antrean pengiriman WhatsApp.',
        ]);
    }

    private function determineRoleType(Employee $employee): string
    {
        // 1. Check if Principal (Kepala Sekolah)
        foreach ($employee->positions as $pos) {
            $code = strtolower($pos->position_code);
            $name = strtolower($pos->position_name);
            if (str_contains($code, 'kepsek') || str_contains($code, 'kasek') || str_contains($name, 'kepala sekolah')) {
                return 'kepala_sekolah';
            }
        }

        // 2. Check positions for Admin / Operator and Bendahara / Keuangan
        foreach ($employee->positions as $pos) {
            $name = strtolower($pos->position_name);
            if (str_contains($name, 'admin') || str_contains($name, 'operator')) {
                return 'admin';
            }
            if (str_contains($name, 'bendahara') || str_contains($name, 'keuangan')) {
                return 'bendahara';
            }
        }

        // 3. Check if linked to user role
        if ($employee->user) {
            $role = $employee->user->role;
            if ($role === 'admin_sekolah' || $role === 'admin') {
                return 'admin';
            }
            if ($role === 'bendahara') {
                return 'bendahara';
            }
        }

        // 4. Fallback check by employee_type
        if ($employee->employee_type === 'guru') {
            return 'guru';
        }

        return 'pegawai';
    }

    private function getRoleLabel(string $roleType): string
    {
        return match ($roleType) {
            'kepala_sekolah' => 'Kepala Sekolah',
            'admin' => 'Admin Operator',
            'bendahara' => 'Bendahara Sekolah',
            'guru' => 'Guru / Wali Kelas',
            'pegawai' => 'Staf Pegawai',
            default => 'Pegawai',
        };
    }

    private function getEmployeePositionName(Employee $employee): string
    {
        $primary = $employee->getPrimaryPosition();
        if ($primary) {
            return $primary->position_name;
        }
        return $employee->employee_type === 'guru' ? 'Guru' : 'Staf';
    }

    private function generateMessage(Employee $employee, string $roleType): string
    {
        $name = $employee->full_name;
        $school = $employee->school->name ?? 'Yayasan Perguruan PEMBDA Nias';
        $schoolId = $employee->school_id;

        // Schedule mapping based on School Unit
        $sessionName = 'Sesi 2 (Pendidik SMP & SMA)';
        $sessionDate = 'Sabtu, 20 Juni 2026';
        $sessionLocation = 'Lab. Komputer Unit Sekolah Masing-masing';

        if ($schoolId == 3) { // SMK
            $sessionName = 'Sesi 3 (Pendidik SMK)';
            $sessionDate = 'Sabtu, 27 Juni 2026';
            $sessionLocation = 'Lab. Komputer SMKS Swasta Pembda Nias';
        }

        if ($roleType === 'kepala_sekolah') {
            return "Yth. Bapak/Ibu *{$name}*\n" .
                   "Kepala Sekolah *{$school}*\n" .
                   "di- Tempat\n\n" .
                   "Dengan hormat,\n" .
                   "Dalam rangka persiapan menyambut Tahun Ajaran Baru 2026/2027 dan persiapan penggunaan PembdaHUB di lingkungan Yayasan Perguruan Pembda Nias, kami mengundang Bapak/Ibu untuk memimpin dan memantau pelaksanaan Pelatihan Aplikasi Pembda HUB yang diselenggarakan terintegrasi.\n\n" .
                   "Sebagai Kepala Sekolah, kami mengharapkan kehadiran Bapak/Ibu serta dukungan untuk memfasilitasi Laboratorium Komputer dengan koneksi internet yang stabil, serta mengawasi kehadiran seluruh guru, panitia MPLS, dan staf tata usaha di unit Bapak/Ibu masing-masing.\n\n" .
                   "*Uraian Materi Pelatihan Pengawasan Yayasan & Kepala Sekolah*:\n" .
                   "1. Peninjauan Dashboard Eksekutif Yayasan & Monitoring Sekolah.\n" .
                   "2. Pemantauan Statistik Pengguna Aktif & Kehadiran Guru/Staf secara real-time.\n" .
                   "3. Monitoring SPP, Billing Keuangan, & Rekapitulasi Pembayaran per Unit.\n" .
                   "4. Pengawasan Kesiapan Rombel, Jam Pelajaran (Visual Grid), & Impor Master Data.\n" .
                   "5. Koordinasi Panitia & OSIS Pendamping untuk Onboarding MPLS Siswa Baru.\n\n" .
                   "*Jadwal Pelatihan Unit Bapak/Ibu*:\n" .
                   "• Sesi: {$sessionName}\n" .
                   "• Hari/Tanggal: {$sessionDate}\n" .
                   "• Waktu: 08.00 - 12.00 WIB\n" .
                   "• Lokasi: {$sessionLocation}\n\n" .
                   "_Catatan_: Mohon menginstruksikan Admin Operator & Bendahara Sekolah di unit Bapak/Ibu agar bersiap mendampingi guru sebagai fasilitator.\n\n" .
                   "Atas perhatian dan kepemimpinan Bapak/Ibu, kami ucapkan terima kasih.\n\n" .
                   "Ketua Yayasan,\n" .
                   "*Yulianus Zega, S.Kom*";
        }

        if ($roleType === 'admin') {
            return "Yth. Bapak/Ibu *{$name}*\n" .
                   "Admin Operator *{$school}*\n" .
                   "di- Tempat\n\n" .
                   "Dengan hormat,\n" .
                   "Dalam rangka persiapan menyambut Tahun Ajaran Baru 2026/2027 dan persiapan penggunaan PembdaHUB di lingkungan Yayasan Perguruan Pembda Nias, kami mengundang Bapak/Ibu untuk wajib hadir pada program pelatihan *Fase 1 (Sistem Ready)*.\n\n" .
                   "*Uraian Materi Pelatihan untuk Admin Operator Sekolah*:\n" .
                   "1. Pengaturan parameter global (Tahun Ajaran aktif, Semester, KKM unit).\n" .
                   "2. Manajemen pengguna (Tambah pengguna secara manual/bulk, tombol reset password).\n" .
                   "3. Konfigurasi Rombel/Kelas (Kapasitas kelas dengan ring diagram, pengelompokan jurusan untuk SMK).\n" .
                   "4. Input/Impor data master siswa aktif dan mata pelajaran.\n" .
                   "5. Penugasan mengajar guru.\n" .
                   "6. Penyusunan Jam Pelajaran (Time Slots) dan penyusunan Jadwal Pelajaran Visual (Grid) tanpa bentrok (Conflict Detection).\n" .
                   "7. Proses verifikasi, scoring, dan migrasi calon siswa baru di modul PSB menjadi siswa aktif.\n\n" .
                   "*Jadwal Pelatihan Bapak/Ibu*:\n" .
                   "• Sesi: Sesi 1 (Admin & Bendahara Semua Unit)\n" .
                   "• Hari/Tanggal: Sabtu, 13 Juni 2026\n" .
                   "• Waktu: 08.00 - 12.00 WIB\n" .
                   "• Lokasi: Lab. Komputer SMKS Swasta Pembda Nias\n\n" .
                   "_Penting_: Admin yang telah dilatih wajib hadir kembali pada sesi unit masing-masing ({$sessionDate}) sebagai fasilitator pendamping guru.\n\n" .
                   "Atas kehadiran dan komitmen Bapak/Ibu, kami ucapkan terima kasih.\n\n" .
                   "Ketua Yayasan,\n" .
                   "*Yulianus Zega, S.Kom*";
        }

        if ($roleType === 'bendahara') {
            return "Yth. Bapak/Ibu *{$name}*\n" .
                   "Bendahara Keuangan *{$school}*\n" .
                   "di- Tempat\n\n" .
                   "Dengan hormat,\n" .
                   "Dalam rangka persiapan menyambut Tahun Ajaran Baru 2026/2027 dan persiapan penggunaan PembdaHUB di lingkungan Yayasan Perguruan Pembda Nias, kami mengundang Bapak/Ibu untuk wajib hadir pada program pelatihan *Fase 1 (Sistem Ready)*.\n\n" .
                   "*Uraian Materi Pelatihan untuk Bendahara Keuangan Sekolah*:\n" .
                   "1. Pembuatan jenis tagihan (SPP bulanan, uang seragam/buku) per tingkat kelas secara massal.\n" .
                   "2. Monitoring status pembayaran (lunas, cicilan, tunggakan) dan denda berjalan otomatis.\n" .
                   "3. Fitur penghapusan denda (waive) untuk kasus khusus.\n" .
                   "4. Formulir terima pembayaran SPP (lunas/cicilan), pencetakan kuitansi PDF resmi, dan monitoring WhatsApp notifikasi (Fonnte API).\n" .
                   "5. Ekspor data rekap keuangan bulanan/semester ke Excel/CSV.\n\n" .
                   "*Jadwal Pelatihan Bapak/Ibu*:\n" .
                   "• Sesi: Sesi 1 (Admin & Bendahara Semua Unit)\n" .
                   "• Hari/Tanggal: Sabtu, 13 Juni 2026\n" .
                   "• Waktu: 08.00 - 12.00 WIB\n" .
                   "• Lokasi: Lab. Komputer SMKS Swasta Pembda Nias\n\n" .
                   "_Penting_: Bendahara yang telah dilatih wajib hadir kembali pada sesi unit masing-masing ({$sessionDate}) sebagai fasilitator pendamping guru.\n\n" .
                   "Atas kehadiran dan komitmen Bapak/Ibu, kami ucapkan terima kasih.\n\n" .
                   "Ketua Yayasan,\n" .
                   "*Yulianus Zega, S.Kom*";
        }

        if ($roleType === 'guru') {
            $specialSMK = $schoolId == 3 ? "\n4. Pengelolaan PKL & Magang Terintegrasi (Input penempatan, Mentor Industri, Signed URL, Jurnal & Nilai PKL).\n5. Pengendalian Tugas Akhir / Project Akhir SMK." : ($schoolId == 2 ? "\n4. Pengendalian Tugas Akhir (Penelitian Ilmiah Kelas XII)." : "");
            return "Yth. Bapak/Ibu *{$name}*\n" .
                   "Guru / Wali Kelas *{$school}*\n" .
                   "di- Tempat\n\n" .
                   "Dengan hormat,\n" .
                   "Dalam rangka persiapan menyambut Tahun Ajaran Baru 2026/2027 dan persiapan penggunaan PembdaHUB di lingkungan Yayasan Perguruan Pembda Nias, kami mengundang Bapak/Ibu untuk wajib hadir pada pelatihan *Fase 2 (Pendidik Ready)*.\n\n" .
                   "*Uraian Materi Pelatihan untuk Guru & Wali Kelas*:\n" .
                   "1. Mengakses jadwal mengajar pribadi dan daftar kelas binaan.\n" .
                   "2. Input absensi harian bulk (Set All Hadir/Sakit/Izin/Alpha) & input nilai tugas, UTS, UAS, dan sikap secara bulk.\n" .
                   "3. Modul LMS & CBT (Membuat Course, unggah modul/tugas/kuis, bank soal KaTeX, token, anti-cheat, sinkronisasi nilai ke rapor).{$specialSMK}\n" .
                   "4. Modul Wali Kelas: Rapor Digital (input catatan wali kelas, generate rapor, lock & finalize, unduh ZIP Rapor kelas).\n" .
                   "5. Simulasi MPLS: Cara memandu siswa baru untuk login, mengubah password default, LMS, dan CBT MPLS di bulan Juni.\n\n" .
                   "*Jadwal Pelatihan Bapak/Ibu*:\n" .
                   "• Sesi: {$sessionName}\n" .
                   "• Hari/Tanggal: {$sessionDate}\n" .
                   "• Waktu: 08.00 - 12.00 WIB\n" .
                   "• Lokasi: {$sessionLocation}\n\n" .
                   "*Instruksi Khusus*:\n" .
                   "1. Hadir tepat waktu sesuai jadwal.\n" .
                   "2. Wajib membawa laptop pribadi beserta charger.\n" .
                   "3. Bagi panitia MPLS & OSIS Pendamping, sesi ini sekaligus membekali alur ujian anti-cheat untuk diajarkan ke siswa baru di bulan Juni.\n\n" .
                   "Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.\n\n" .
                   "Ketua Yayasan,\n" .
                   "*Yulianus Zega, S.Kom*";
        }

        // Default Pegawai / Staf
        return "Yth. Bapak/Ibu *{$name}*\n" .
               "Staf Pegawai *{$school}*\n" .
               "di- Tempat\n\n" .
               "Dengan hormat,\n" .
               "Dalam rangka persiapan menyambut Tahun Ajaran Baru 2026/2027 dan persiapan penggunaan PembdaHUB di lingkungan Yayasan Perguruan Pembda Nias, kami mengundang Bapak/Ibu untuk wajib hadir pada program pelatihan modul kepegawaian dan administrasi.\n\n" .
               "*Uraian Materi Pelatihan untuk Staf Pegawai*:\n" .
               "1. Monitoring Data Profil Pribadi, Riwayat Pendidikan, & Dokumen Kepegawaian.\n" .
               "2. Modul Kehadiran Pegawai & Penginputan/Review Absensi Harian Pegawai.\n" .
               "3. Pengajuan Cuti Pegawai & Pemantauan Status Persetujuan Cuti.\n" .
               "4. Akses Monitoring Gaji Pokok & Slip Gaji Bulanan (Modul Payroll/SDM).\n" .
               "5. Integrasi berkas pendukung administrasi kepegawaian unit.\n\n" .
               "*Jadwal Pelatihan Bapak/Ibu*:\n" .
               "• Sesi: {$sessionName}\n" .
               "• Hari/Tanggal: {$sessionDate}\n" .
               "• Waktu: 08.00 - 12.00 WIB\n" .
               "• Lokasi: {$sessionLocation}\n\n" .
               "_Penting_: Wajib membawa laptop pribadi dan charger untuk keperluan pelatihan langsung.\n\n" .
               "Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.\n\n" .
               "Ketua Yayasan,\n" .
               "*Yulianus Zega, S.Kom*";
    }

    private function normalizePhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
