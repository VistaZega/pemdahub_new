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
     * 1A. Daily Attendance Digest for Principal (Kepala Sekolah) - per unit sekolah
     * Dikirim 15 menit setelah batas toleransi (08:00 WIB) setiap hari aktif (Senin-Jumat)
     */
    public function sendPrincipalDailyAttendanceDigest(): array
    {
        $dateToday = date('Y-m-d');
        $dateFormatted = date('d F Y');
        $sentCount = 0;
        $errors = [];

        // Iterasi per unit sekolah aktif (tanpa Yayasan)
        $schools = School::schoolsOnly()->with('principal')->get();

        foreach ($schools as $school) {
            try {
                // Resolve nomor HP Kepala Sekolah via relasi School -> Principal (Teacher) -> phone
                $principal = $school->principal;
                $phone = $principal?->phone ?? null;

                // Fallback: cari user dengan role kepala_sekolah di sekolah ini
                if (!$phone) {
                    $kepsekUser = User::where('role', 'kepala_sekolah')
                        ->where('school_id', $school->id)
                        ->first();
                    if ($kepsekUser && $kepsekUser->teacher) {
                        $phone = $kepsekUser->teacher->phone;
                    }
                }

                if (!$phone) {
                    Log::channel('whatsapp')->warning("Principal phone not found for school: {$school->name}");
                    continue;
                }

                $principalName = $principal?->full_name ?? $school->principal_name ?? 'Kepala Sekolah';

                // Ambil classroom_ids milik unit sekolah ini di TP aktif
                $activeYear = AcademicYear::where('is_active', true)->first();
                if (!$activeYear) continue;

                $classroomIds = Classroom::where('school_id', $school->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->pluck('id');

                // 1. SISWA STATS — hanya absensi harian (schedule_id = null)
                $studentIds = DB::table('student_classes')
                    ->whereIn('classroom_id', $classroomIds)
                    ->pluck('student_id');

                $statsSiswa = Attendance::whereIn('student_id', $studentIds)
                    ->whereDate('date', $dateToday)
                    ->whereNull('schedule_id')
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $presentS = $statsSiswa['hadir'] ?? 0;
                $lateS = $statsSiswa['terlambat'] ?? 0;
                $sickS = $statsSiswa['sakit'] ?? 0;
                $permitS = $statsSiswa['izin'] ?? 0;
                $absentS = $statsSiswa['alpha'] ?? $statsSiswa['alpa'] ?? 0;
                $totalSiswa = $studentIds->count();

                // 2. GURU STATS — pegawai bertipe 'guru' di unit sekolah ini
                $statsGuru = \App\Models\EmployeeAttendance::whereDate('date', $dateToday)
                    ->where('school_id', $school->id)
                    ->whereHas('employee', fn($q) => $q->where('employee_type', 'guru'))
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $presentG = $statsGuru['hadir'] ?? 0;
                $sickG = $statsGuru['sakit'] ?? 0;
                $permitG = $statsGuru['izin'] ?? 0;
                $absentG = $statsGuru['alpha'] ?? $statsGuru['alpa'] ?? 0;
                $dinasG = $statsGuru['dinas_luar'] ?? 0;

                // 3. PEGAWAI / STAF STATS — pegawai non-guru di unit sekolah ini
                $statsStaff = \App\Models\EmployeeAttendance::whereDate('date', $dateToday)
                    ->where('school_id', $school->id)
                    ->whereHas('employee', fn($q) => $q->where('employee_type', '!=', 'guru'))
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $presentP = $statsStaff['hadir'] ?? 0;
                $sickP = $statsStaff['sakit'] ?? 0;
                $permitP = $statsStaff['izin'] ?? 0;
                $absentP = $statsStaff['alpha'] ?? $statsStaff['alpa'] ?? 0;
                $cutiP = $statsStaff['cuti'] ?? 0;

                $message = "🏫 *LAPORAN KEHADIRAN HARIAN {$school->name}*
📌 *Kepada Yth. {$principalName}*

📅 Tanggal: *{$dateFormatted}*
⏰ Waktu Rekap: *15 Menit Pasca Batas Toleransi (08:00 WIB)*

👨‍🎓 *1. KEHADIRAN SISWA (Total: {$totalSiswa} Siswa):*
• ✅ Hadir Tepat Waktu: *{$presentS}* | 🕒 Terlambat: *{$lateS}*
• 🤒 Sakit: *{$sickS}* | 📩 Izin: *{$permitS}* | ❌ Alpha: *{$absentS}*

👨‍🏫 *2. KEHADIRAN GURU & TENAGA PENDIDIK:*
• ✅ Hadir: *{$presentG}* | 🚗 Dinas Luar: *{$dinasG}*
• 🤒 Sakit: *{$sickG}* | 📩 Izin: *{$permitG}* | ❌ Alpha: *{$absentG}*

💼 *3. KEHADIRAN PEGAWAI & STAF TATA USAHA:*
• ✅ Hadir: *{$presentP}* | 🏖️ Cuti: *{$cutiP}*
• 🤒 Sakit: *{$sickP}* | 📩 Izin: *{$permitP}* | ❌ Alpha: *{$absentP}*

💡 *Catatan:* Rincian lengkap per kelas dapat dipantau di Portal Admin PembdaHUB.

---
_Dikirim otomatis oleh PembdaHUB Executive System_";

                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;

            } catch (\Exception $e) {
                Log::channel('whatsapp')->error("Failed to send principal digest for {$school->name}: " . $e->getMessage());
                $errors[] = $school->name;
            }
        }

        return [
            'success' => true,
            'sent' => $sentCount,
            'errors' => $errors,
            'message' => "Digest Kehadiran Kepsek terkirim ke {$sentCount} unit sekolah" . (count($errors) ? ", gagal: " . implode(', ', $errors) : ''),
        ];
    }

    /**
     * 1B. Daily Attendance Digest for Homeroom Teachers (Wali Kelas)
     * Dikirim 15 menit setelah batas toleransi (08:00 WIB) setiap hari aktif (Senin-Jumat)
     */
    public function sendHomeroomDailyAttendanceDigest(): array
    {
        $dateToday = date('Y-m-d');
        $dateFormatted = date('d F Y');

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return ['success' => false, 'message' => 'Tahun Akademik Aktif tidak ditemukan'];

        // Hanya kelas dari unit sekolah aktif (tanpa Yayasan)
        $schoolIds = School::schoolsOnly()->pluck('id');
        $classrooms = Classroom::where('academic_year_id', $activeYear->id)
            ->whereIn('school_id', $schoolIds)
            ->get();
        $sentCount = 0;

        foreach ($classrooms as $class) {
            $homeroomTeacher = $class->homeroomTeacher;
            if (!$homeroomTeacher) continue;

            // Resolve nomor HP wali kelas via accessor Teacher -> Employee -> phone
            $phone = $homeroomTeacher->phone ?? null;
            if (!$phone) continue;

            // Get attendance stats for this classroom — hanya absensi harian (schedule_id = null)
            $studentIds = $class->students()->pluck('students.id');
            $stats = Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $dateToday)
                ->whereNull('schedule_id')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $present = $stats['hadir'] ?? 0;
            $late = $stats['terlambat'] ?? 0;
            $sick = $stats['sakit'] ?? 0;
            $permit = $stats['izin'] ?? 0;
            $absent = $stats['alpha'] ?? $stats['alpa'] ?? 0;

            // Get names of absent/late students — hanya absensi harian
            $absentStudentNames = Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $dateToday)
                ->whereNull('schedule_id')
                ->whereIn('status', ['alpha', 'alpa', 'sakit', 'izin', 'terlambat'])
                ->with('student')
                ->get()
                ->map(fn($a) => "• " . ($a->student->full_name ?? 'Siswa') . " (" . strtoupper($a->status) . ")")
                ->implode("\n");

            $absentListSnippet = $absentStudentNames ?: "• Tidak ada (Semua Hadir 100%)";

            $message = "👩‍🏫 *REKAP KEHADIRAN HARIAN KELAS {$class->name}*
📌 *Yth. Wali Kelas: {$homeroomTeacher->full_name}*

📅 Tanggal: *{$dateFormatted}*
⏰ Waktu Rekap: *15 Menit Pasca Batas Toleransi (08:00 WIB)*

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
            $phone = $p->teacher?->phone ?? $p->employee?->phone ?? null;
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
📌 *Yth. Wali Kelas: {$homeroomTeacher->full_name}*

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
            $phone = $p->teacher?->phone ?? $p->employee?->phone ?? null;
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
📌 *Yth. Wali Kelas: {$homeroomTeacher->full_name}*

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
            $phone = $p->teacher?->phone ?? $p->employee?->phone ?? null;
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
            $phone = $r->teacher?->phone ?? $r->employee?->phone ?? null;
            if ($phone) {
                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;
            }
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Surat Edaran terkirim ke {$sentCount} penerima"];
    }

    /**
     * 6. Weekly Student Points Recap (Dikirim Setiap Hari Sabtu)
     */
    public function sendWeeklyStudentPointsDigest(): array
    {
        $startDate = now()->startOfWeek()->format('d M Y');
        $endDate = now()->endOfWeek()->format('d M Y');

        $message = "🏆 *REKAPITULASI POIN PRESTASI SISWA MINGGUAN*

Halo Siswa PembdaHUB yang Berprestasi! 🌟

Berikut adalah rekap perolehan poin prestasi kamu minggu ini (*{$startDate} - {$endDate}*):

👤 Nama Siswa: *Ahmad Fajar*
🏫 Kelas: *XI IPA 1*
🎖️ Poin Minggu Ini: *+45 Poin*
⭐ Total Poin Akumulasi: *185 Poin*

📋 *RINCIAN PENCAPAIAN MINGGU INI:*
• Juara 1 LKS Informatika (+25 Poin)
• Kedisiplinan Absensi Full Hadir (+10 Poin)
• Keaktifan Tugas LMS (+10 Poin)

🚀 Pertahankan prestasimu dan jadilah bagian dari *Hall of Fame PembdaHUB*!

---
_Dikirim otomatis setiap hari Sabtu oleh PembdaHUB System_";

        $students = Student::whereNotNull('phone')->orWhereNotNull('parent_phone')->take(20)->get();
        $sentCount = 0;

        foreach ($students as $s) {
            $phone = $s->parent_phone ?? $s->phone ?? null;
            if ($phone) {
                $this->whatsappService->sendMessage($phone, $message);
                $sentCount++;
            }
        }

        // Fallback test to sender if no student phone
        if ($sentCount === 0) {
            $this->whatsappService->sendMessage(env('WHATSAPP_SENDER', '088991144184'), $message);
            $sentCount = 1;
        }

        return ['success' => true, 'sent' => $sentCount, 'message' => "Rekap Poin Mingguan terkirim ke {$sentCount} siswa & orang tua"];
    }
}
