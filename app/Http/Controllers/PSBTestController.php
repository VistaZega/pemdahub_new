<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class PSBTestController extends Controller
{
    /**
     * Dashboard test PSB Communication
     */
    public function index()
    {
        $applicants = Applicant::with('school')->latest()->take(10)->get();
        return view('psb-test.index', compact('applicants'));
    }

    /**
     * Preview Email Template
     */
    public function previewEmail($type, $registrationNumber)
    {
        $applicant = Applicant::where('registration_number', $registrationNumber)
            ->with('school')
            ->firstOrFail();
        
        return view("psb-test.emails.{$type}", compact('applicant'));
    }

    /**
     * Preview WhatsApp Template
     */
    public function previewWhatsApp($type, $registrationNumber)
    {
        $applicant = Applicant::where('registration_number', $registrationNumber)
            ->with('school')
            ->firstOrFail();
        
        $message = $this->getWhatsAppMessage($type, $applicant);
        
        return view('psb-test.whatsapp-preview', [
            'applicant' => $applicant,
            'type' => $type,
            'message' => $message
        ]);
    }

    /**
     * Preview SMS Template
     */
    public function previewSMS($type, $registrationNumber)
    {
        $applicant = Applicant::where('registration_number', $registrationNumber)
            ->with('school')
            ->firstOrFail();
        
        $message = $this->getSMSMessage($type, $applicant);
        
        return view('psb-test.sms-preview', [
            'applicant' => $applicant,
            'type' => $type,
            'message' => $message
        ]);
    }

    /**
     * Simulate Send Notification
     */
    public function simulateSend(Request $request)
    {
        $registrationNumber = $request->registration_number;
        $notificationType = $request->notification_type;
        
        $applicant = Applicant::where('registration_number', $registrationNumber)
            ->with('school')
            ->firstOrFail();
        
        // Log simulasi
        $logFile = storage_path('logs/psb-notification-simulation.log');
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $logEntry = "[$timestamp] SIMULASI NOTIFIKASI\n";
        $logEntry .= "Tipe: $notificationType\n";
        $logEntry .= "Kepada: {$applicant->full_name}\n";
        $logEntry .= "No. Reg: {$applicant->registration_number}\n";
        $logEntry .= "Email: {$applicant->email}\n";
        $logEntry .= "Phone: {$applicant->phone}\n";
        $logEntry .= "-----------------------------------\n\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        return response()->json([
            'success' => true,
            'message' => 'Notifikasi disimulasikan! Cek log: storage/logs/psb-notification-simulation.log',
            'preview_url' => [
                'email' => route('psb.test.preview.email', [$notificationType, $registrationNumber]),
                'whatsapp' => route('psb.test.preview.whatsapp', [$notificationType, $registrationNumber]),
                'sms' => route('psb.test.preview.sms', [$notificationType, $registrationNumber]),
            ]
        ]);
    }

    /**
     * Generate WhatsApp Message
     */
    private function getWhatsAppMessage($type, $applicant)
    {
        $fee = $applicant->school_id == 3 ? 300000 : 50000;
        $panitiaWA = '082373642864'; // Nomor real panitia
        
        switch ($type) {
            case 'registration':
                return "✅ *PENDAFTARAN BERHASIL!*\n\n"
                    . "Halo *{$applicant->full_name}*, pendaftaran Anda berhasil!\n\n"
                    . "📋 No. Registrasi: *{$applicant->registration_number}*\n"
                    . "🏫 Sekolah: {$applicant->school->name}\n\n"
                    . "🎯 *LANGKAH BERIKUTNYA:*\n"
                    . "1. Transfer Rp " . number_format($fee, 0, ',', '.') . "\n"
                    . "   BCA: 1234567890\n"
                    . "   a.n. Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)\n\n"
                    . "2. Kirim bukti ke WA ini ({$panitiaWA}) dengan format:\n"
                    . "   *BAYAR#{$applicant->registration_number}#{$applicant->full_name}*\n\n"
                    . "3. Tunggu konfirmasi 1x24 jam\n\n"
                    . "📧 Email detail sudah dikirim!\n"
                    . "💬 Simpan nomor ini untuk komunikasi\n\n"
                    . "Info: psb@pembdanias.sch.id";
            
            case 'payment':
                return "✅ *PEMBAYARAN DITERIMA!*\n\n"
                    . "*{$applicant->full_name}*, pembayaran Anda sudah terverifikasi ✅\n\n"
                    . "📋 Registrasi: *{$applicant->registration_number}*\n"
                    . "💰 Jumlah: Rp " . number_format($fee, 0, ',', '.') . "\n\n"
                    . "🎯 *UPLOAD DOKUMEN:*\n"
                    . "Klik: http://127.0.0.1:8000/pendaftaran/upload/{$applicant->registration_number}\n\n"
                    . "📄 Yang perlu diupload:\n"
                    . "1. KK\n"
                    . "2. Akta\n"
                    . "3. Ijazah\n"
                    . "4. Pas Foto\n\n"
                    . "⏰ Deadline: 15 Maret 2026\n\n"
                    . "💡 Setelah lengkap, jadwal tes akan dikirim!";
            
            case 'document':
                return "✅ *DOKUMEN LENGKAP!*\n\n"
                    . "*{$applicant->full_name}*, dokumen Anda sudah diverifikasi ✅\n\n"
                    . "📋 Status: *Siap Tes*\n\n"
                    . "📅 *INFO JADWAL TES:*\n"
                    . "Akan dikirim H-3 via:\n"
                    . "• Email\n"
                    . "• WhatsApp ini\n"
                    . "• SMS ke HP Anda\n\n"
                    . "📝 Persiapan:\n"
                    . "• Alat tulis\n"
                    . "• Datang 30 menit lebih awal\n"
                    . "• Pakaian rapi\n\n"
                    . "🎯 Semangat! Good luck!";
            
            case 'test_schedule':
                return "📝 *JADWAL TES MASUK*\n\n"
                    . "Halo *{$applicant->full_name}*!\n\n"
                    . "Ini jadwal tes masuk Anda:\n\n"
                    . "📅 TANGGAL: *Sabtu, 20 Maret 2026*\n"
                    . "⏰ WAKTU: *08:00 - 11:00 WIB*\n"
                    . "📍 TEMPAT: *Aula Yayasan PEMBDA*\n"
                    . "        Jl. Pembangunan No. 123, Nias\n\n"
                    . "✅ *WAJIB DIBAWA:*\n"
                    . "1. Kartu peserta (terlampir di email)\n"
                    . "2. Alat tulis (pensil 2B, pulpen, penghapus)\n"
                    . "3. KTP/Kartu Pelajar\n\n"
                    . "⚠️ *PENTING:*\n"
                    . "• Datang 30 menit lebih awal\n"
                    . "• Pakaian rapi & sopan\n"
                    . "• HP disimpan/dimatikan\n\n"
                    . "🗺️ LOKASI:\n"
                    . "https://maps.google.com/?q=Yayasan+PEMBDA+Nias\n\n"
                    . "📞 Kontak Darurat:\n"
                    . "{$panitiaWA} (Panitia)\n\n"
                    . "Good luck! 💪";
            
            case 'result_accepted':
                return "🎉 *SELAMAT! ANDA DITERIMA!* 🎉\n\n"
                    . "*{$applicant->full_name}*, selamat ya! Anda diterima di:\n\n"
                    . "🏫 *{$applicant->school->name}*\n"
                    . "📋 No.Reg: *{$applicant->registration_number}*\n\n"
                    . "📅 *DAFTAR ULANG:*\n"
                    . "*15-20 April 2026*\n"
                    . "Jam 08:00-15:00\n\n"
                    . "💰 Biaya: Rp *1.500.000*\n\n"
                    . "📧 Email detail + dokumen sudah dikirim!\n\n"
                    . "⚠️ Deadline daftar ulang: *20 April 2026*\n\n"
                    . "Konfirmasi kehadiran ke WA ini ya!\n\n"
                    . "Welcome to PEMBDA! 🎓✨";
            
            default:
                return "Tipe notifikasi tidak dikenali.";
        }
    }

    /**
     * Generate SMS Message
     */
    private function getSMSMessage($type, $applicant)
    {
        $panitiaWA = '082373642864'; // Nomor real panitia
        
        switch ($type) {
            case 'test_schedule':
                return "PEMBDA PSB - {$applicant->full_name}: Tes masuk 20/03/2026 pkl 08:00 di Aula Yayasan PEMBDA. Info: {$panitiaWA}. No.Reg: {$applicant->registration_number}";
            
            case 'test_reminder':
                return "REMINDER: {$applicant->full_name}, BESOK tes masuk pkl 08:00 di Aula PEMBDA. Bawa kartu peserta & alat tulis. Datang 30 menit lebih awal. Good luck!";
            
            case 'result_accepted':
                return "SELAMAT! {$applicant->full_name} DITERIMA di {$applicant->school->name}. Daftar ulang 15-20 Apr. Info lengkap di email. Konfirmasi: {$panitiaWA}";
            
            case 'result_waiting':
                return "{$applicant->full_name}, Anda masuk WAITING LIST. Kami akan hubungi jika ada kursi tersedia. Info: {$panitiaWA}";
            
            default:
                return "Tipe SMS tidak dikenali.";
        }
    }
}
