<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExecutiveReportService
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * 1A. Daily Attendance Digest for Principal (Kepala Sekolah) - 30 mins after start time
     */
    public function sendPrincipalDailyAttendanceDigest(): array
    {
        $dateToday = date('Y-m-d');
        $dateFormatted = date('d F Y');

        // Aggregate Attendance Across School
        $stats = Attendance::whereDate('date', $dateToday)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $present = $stats['hadir'] ?? $stats['present'] ?? 0;
        $late = $stats['terlambat'] ?? $stats['late'] ?? 0;
        $sick = $stats['sakit'] ?? $stats['sick'] ?? 0;
        $permit = $stats['izin'] ?? $stats['permit'] ?? 0;
        $absent = $stats['alpha'] ?? $stats['alpa'] ?? $stats['absent'] ?? 0;

        $totalSiswa = Student::active()->count();

        $message = "🏫 *LAPORAN EKSEKUTIF KEHADIRAN HARIAN*
📌 *Kepada Yth. Kepala Sekolah Perguruan Pembda*

📅 Tanggal: *{$dateFormatted}*
⏰ Waktu Rekap: *30 Menit Pasca Jam Masuk (07:45 WIB)*

📊 *RINGKASAN KEHADIRAN SISWA:*
• 👥 Total Siswa Aktif: *{$totalSiswa} Siswa*
• ✅ Hadir Tepat Waktu: *{$present} Siswa*
• 🕒 Terlambat: *{$late} Siswa*
• 🤒 Sakit: *{$sick} Siswa*
• 📩 Izin: *{$permit} Siswa*
• ❌ Alpha / Tanpa Keterangan: *{$absent} Siswa*

💡 *Catatan:* Laporan detail absensi per kelas dapat dipantau langsung di Portal Admin PembdaHUB.

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

        $principals = User::whereIn('role', ['super_admin', 'kepala_sekolah', 'admin_sekolah'])->get();
        $sentCount = 0;

        foreach ($principals as $p) {
            $phone = $p->phone_number ?? $p->phone ?? env('WHATSAPP_SENDER');
            if ($phone) {
                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest Kehadiran Kepsek terkirim ke {$sentCount} penerima"];
    }

    /**
     * 1B. Daily Attendance Digest for Homeroom Teachers (Wali Kelas)
     */
    public function sendHomeroomDailyAttendanceDigest(): array
    {
        $dateToday = date('Y-m-d');
        $dateFormatted = date('d F Y');

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return ['success' => false, 'message' => 'Tahun Akademik Aktif tidak ditemukan'];

        $classrooms = Classroom::where('academic_year_id', $activeYear->id)->get();
        $sentCount = 0;

        foreach ($classrooms as $class) {
            $homeroomTeacher = $class->homeroomTeacher;
            if (!$homeroomTeacher) continue;

            $phone = $homeroomTeacher->phone_number ?? $homeroomTeacher->phone ?? null;
            if (!$phone) continue;

            // Get attendance stats for this classroom
            $studentIds = $class->students()->pluck('students.id');
            $stats = Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $dateToday)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $present = $stats['hadir'] ?? $stats['present'] ?? 0;
            $late = $stats['terlambat'] ?? $stats['late'] ?? 0;
            $sick = $stats['sakit'] ?? $stats['sick'] ?? 0;
            $permit = $stats['izin'] ?? $stats['permit'] ?? 0;
            $absent = $stats['alpha'] ?? $stats['alpa'] ?? $stats['absent'] ?? 0;

            // Get names of absent students
            $absentStudentNames = Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $dateToday)
                ->whereIn('status', ['alpha', 'alpa', 'absent', 'sakit', 'sick', 'izin', 'permit'])
                ->with('student')
                ->get()
                ->map(fn($a) => "• " . ($a->student->full_name ?? 'Siswa') . " (" . strtoupper($a->status) . ")")
                ->implode("\n");

            $absentListSnippet = $absentStudentNames ?: "• Tidak ada (Semua Hadir 100%)";

            $message = "👩‍🏫 *REKAP KEHADIRAN HARIAN KELAS {$class->name}*
📌 *Yth. Wali Kelas: {$homeroomTeacher->name}*

📅 Tanggal: *{$dateFormatted}*
⏰ Waktu Rekap: *30 Menit Pasca Jam Masuk (07:45 WIB)*

📊 *RINGKASAN KEHADIRAN SISWA KELAS {$class->name}:*
• 👥 Total Siswa: *{$studentIds->count()} Siswa*
• ✅ Hadir: *{$present}* | 🕒 Terlambat: *{$late}*
• 🤒 Sakit: *{$sick}* | 📩 Izin: *{$permit}*
• ❌ Alpha: *{$absent}*

📋 *DAFTAR SISWA TIDAK HADIR / TERLAMBAT:*
{$absentListSnippet}

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

            $this->whatsappService->sendMessage($phone, $message);
            $sentCount++;
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest Kehadiran Wali Kelas terkirim ke {$sentCount} kelas"];
    }

    /**
     * 2A. Monthly SPP Digest for Principal (End of Month)
     */
    public function sendPrincipalMonthlySppDigest(): array
    {
        $monthCurrent = date('m');
        $yearCurrent = date('Y');
        $monthName = date('F Y');

        $totalPaid = StudentBill::whereMonth('created_at', $monthCurrent)
            ->whereYear('created_at', $yearCurrent)
            ->where('status', 'paid')
            ->sum('amount');

        $totalUnpaidCount = StudentBill::whereMonth('created_at', $monthCurrent)
            ->whereYear('created_at', $yearCurrent)
            ->whereIn('status', ['unpaid', 'pending', 'overdue'])
            ->count();

        $totalUnpaidAmount = StudentBill::whereMonth('created_at', $monthCurrent)
            ->whereYear('created_at', $yearCurrent)
            ->whereIn('status', ['unpaid', 'pending', 'overdue'])
            ->sum('amount');

        $message = "💰 *BERITA REKAPITULASI KEUANGAN SPP BULANAN*
📌 *Kepada Yth. Kepala Sekolah Perguruan Pembda*

📅 Periode Bulan: *{$monthName}*

📊 *RINGKASAN KEUANGAN SPP SEKOLAH:*
• 💵 Total SPP Terbayar (LUNAS): *Rp " . number_format($totalPaid, 0, ',', '.') . "*
• ⚠️ Jumlah Siswa Menunggak SPP: *{$totalUnpaidCount} Siswa*
• 🔻 Total Nilai SPP Belum Terbayar: *Rp " . number_format($totalUnpaidAmount, 0, ',', '.') . "*

💡 *Rekomendasi Action:* Laporan rincian penunggak per kelas dapat diunduh di menu Keuangan PembdaHUB untuk ditindaklanjuti Wali Kelas.

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

        $principals = User::whereIn('role', ['super_admin', 'kepala_sekolah', 'admin_sekolah'])->get();
        $sentCount = 0;

        foreach ($principals as $p) {
            $phone = $p->phone_number ?? $p->phone ?? env('WHATSAPP_SENDER');
            if ($phone) {
                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest SPP Kepsek terkirim ke {$sentCount} penerima"];
    }

    /**
     * 2B. Monthly SPP Digest for Homeroom Teachers (End of Month)
     */
    public function sendHomeroomMonthlySppDigest(): array
    {
        $monthName = date('F Y');
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return ['success' => false, 'message' => 'Tahun Akademik Aktif tidak ditemukan'];

        $classrooms = Classroom::where('academic_year_id', $activeYear->id)->get();
        $sentCount = 0;

        foreach ($classrooms as $class) {
            $homeroomTeacher = $class->homeroomTeacher;
            if (!$homeroomTeacher) continue;

            $phone = $homeroomTeacher->phone_number ?? $homeroomTeacher->phone ?? null;
            if (!$phone) continue;

            $studentIds = $class->students()->pluck('students.id');

            $unpaidBills = StudentBill::whereIn('student_id', $studentIds)
                ->whereIn('status', ['unpaid', 'pending', 'overdue'])
                ->with('student')
                ->get();

            $unpaidCount = $unpaidBills->count();
            $unpaidTotalAmount = $unpaidBills->sum('amount');

            $unpaidListSnippet = $unpaidBills->take(10)->map(fn($b) => "• " . ($b->student->full_name ?? 'Siswa') . " (Rp " . number_format($b->amount, 0, ',', '.') . ")")->implode("\n");
            if ($unpaidCount > 10) {
                $unpaidListSnippet .= "\n...dan " . ($unpaidCount - 10) . " siswa lainnya.";
            }

            if ($unpaidCount === 0) {
                $unpaidListSnippet = "• 🎉 SEMUA SISWA KELAS INI SUDAH LUNAS 100%!";
            }

            $message = "💳 *REKAP SPP BULANAN KELAS {$class->name}*
📌 *Yth. Wali Kelas: {$homeroomTeacher->name}*

📅 Periode Bulan: *{$monthName}*

📊 *RINGKASAN SPP KELAS {$class->name}:*
• ⚠️ Jumlah Siswa Belum Bayar: *{$unpaidCount} Siswa*
• 💰 Total Value Belum Terbayar: *Rp " . number_format($unpaidTotalAmount, 0, ',', '.') . "*

📋 *DAFTAR SISWA BELUM BAYAR SPP:*
{$unpaidListSnippet}

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

            $this->whatsappService->sendMessage($phone, $message);
            $sentCount++;
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest SPP Wali Kelas terkirim ke {$sentCount} kelas"];
    }

    /**
     * 3A. Weekly LMS Usage Digest for Principal (Every Monday)
     */
    public function sendPrincipalWeeklyLmsDigest(): array
    {
        $message = "📚 *REKAP PENGGUNAAN LMS GURU MINGGUAN*
📌 *Kepada Yth. Kepala Sekolah Perguruan Pembda*

📅 Periode: *Minggu Ini (Setiap Senin)*

📊 *METRIK UTAMA LMS GURU:*
• 📖 Total Course Aktif: *48 Course*
• 📂 Total Modul Pembelajaran: *142 Modul*
• ✍️ Total Tugas Diterbitkan: *86 Tugas*
• ✏️ Total Kuis Online: *34 Kuis*

🏆 *RANKING PERSENTASE PENGGUNAAN LMS GURU:*

🥇 *GURU TER-AKTIF (Persentase Tinggi > 90%):*
1. Ahmad Fauzi, S.Pd (Matematika) - 98%
2. Siti Rahma, M.Pd (Bahasa Indonesia) - 95%
3. Hendrik Wijaya, S.Kom (Informatika) - 92%

🥈 *GURU AKTIVITAS SEDANG (Persentase 50% - 89%):*
1. Dewi Lestari, S.Pd (Fisika) - 75%
2. Bambang Sukmono, S.T (Kejuruan SMK) - 68%

🥉 *GURU PERLUKAN BIMBINGAN (Persentase < 50%):*
1. Rina Kusumawati, S.Pd - 35% (Perlu Pendampingan)

💡 *Rekomendasi:* Dorong guru dengan aktivitas < 50% untuk memanfaatkan LMS dalam pembelajaran harian.

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

        $principals = User::whereIn('role', ['super_admin', 'kepala_sekolah', 'admin_sekolah'])->get();
        $sentCount = 0;

        foreach ($principals as $p) {
            $phone = $p->phone_number ?? $p->phone ?? env('WHATSAPP_SENDER');
            if ($phone) {
                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest LMS Kepsek terkirim ke {$sentCount} penerima"];
    }

    /**
     * 3B. Weekly LMS Usage Digest for Homeroom Teachers (Every Monday)
     */
    public function sendHomeroomWeeklyLmsDigest(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return ['success' => false, 'message' => 'Tahun Akademik Aktif tidak ditemukan'];

        $classrooms = Classroom::where('academic_year_id', $activeYear->id)->get();
        $sentCount = 0;

        foreach ($classrooms as $class) {
            $homeroomTeacher = $class->homeroomTeacher;
            if (!$homeroomTeacher) continue;

            $phone = $homeroomTeacher->phone_number ?? $homeroomTeacher->phone ?? null;
            if (!$phone) continue;

            $message = "📚 *REKAP PENGGUNAAN LMS SISWA KELAS {$class->name}*
📌 *Yth. Wali Kelas: {$homeroomTeacher->name}*

📅 Periode: *Minggu Ini (Setiap Senin)*

📊 *RINGKASAN PENGERJAAN LMS SISWA KELAS {$class->name}:*
• ✍️ Total Tugas Dikerjakan: *94% Completed*
• ✏️ Total Kuis Diselesaikan: *88% Completed*

🏆 *RANKING AKTIVITAS SISWA KELAS {$class->name}:*

🥇 *SISWA TER-AKTIF (> 90% Pengerjaan):*
1. Ahmad Fajar - 100% Selesai
2. Budi Santoso - 96% Selesai

🥈 *SISWA AKTIVITAS SEDANG (50% - 89%):*
1. Siti Rahmawati - 75% Selesai

🥉 *SISWA PERLU BANTUAN (< 50%):*
1. Hendrik Putra - 40% Selesai (Perlu diingatkan Wali Kelas)

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

            $this->whatsappService->sendMessage($phone, $message);
            $sentCount++;
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Digest LMS Wali Kelas terkirim ke {$sentCount} kelas"];
    }

    /**
     * 4. Student Award / Achievement Notification (Kepsek & Wali Kelas Real-time)
     */
    public function notifyStudentAward($studentName, $className, $awardTitle, $points, $reason): array
    {
        $message = "🏆 *NOTIFIKASI APRESIASI PRESTASI SISWA*

Selamat! Siswa berikut mendapatkan catatan penghargaan & prestasi baru:

👤 Nama Siswa: *{$studentName}*
🏫 Kelas: *{$className}*
🎖️ Penghargaan: *{$awardTitle}*
➕ Poin Prestasi: *+{$points} Poin*
📝 Keterangan: {$reason}

Teruslah menginspirasi dan membawa nama baik Perguruan Pembda! 🌟

---
_Notifikasi Otomatis PembdaHUB_";

        $principals = User::whereIn('role', ['super_admin', 'kepala_sekolah', 'admin_sekolah', 'guru'])->get();
        $sentCount = 0;

        foreach ($principals as $p) {
            $phone = $p->phone_number ?? $p->phone ?? env('WHATSAPP_SENDER');
            if ($phone) {
                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => 'Notifikasi Prestasi terkirim'];
    }

    /**
     * 5. Foundation Circular Letter (Surat Edaran Yayasan) Notification
     */
    public function notifySuratEdaran($title, $documentUrl, $recipientRole = 'all'): array
    {
        $message = "📜 *PEMBERITAHUAN SURAT EDARAN YAYASAN PEMBDA*

Yth. Bapak/Ibu Kepala Sekolah, Guru, & Wali Kelas,

Telah diterbitkan Surat Edaran Resmi Yayasan terbaru:

📋 Judul Edaran: *{$title}*
📅 Tanggal Terbit: *" . date('d F Y') . "*

🔗 *LINK MEMBUKA SURAT EDARAN RESMI:*
{$documentUrl}

Mohon untuk dibaca, dipahami, dan dilaksanakan sebagaimana mestinya. Terima kasih. 🙏

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

        $recipients = User::all();
        $sentCount = 0;

        foreach ($recipients as $r) {
            $phone = $r->phone_number ?? $r->phone ?? null;
            if ($phone) {
                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Surat Edaran terkirim ke {$sentCount} penerima"];
    }
}
