<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PSBNotificationController extends Controller
{
    protected $notificationService;
    protected $whatsappService;

    public function __construct(
        NotificationService $notificationService,
        WhatsAppService $whatsappService
    ) {
        $this->notificationService = $notificationService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Show notification dashboard
     */
    public function index()
    {
        $applicants = Applicant::with('school')
            ->latest()
            ->paginate(20)->withQueryString();

        $stats = [
            'total' => Applicant::count(),
            'with_phone' => Applicant::whereNotNull('phone')->count(),
            'with_email' => Applicant::whereNotNull('email')->count(),
            'wa_enabled' => $this->whatsappService->isEnabled(),
        ];

        return view('admin.psb.notifications.index', compact('applicants', 'stats'));
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $result = $this->notificationService->sendPSBPaymentConfirmation(
            $applicant,
            $request->amount
        );

        // Update applicant status
        $applicant->update(['status' => 'payment_verified']);

        if ($result['whatsapp']['success'] ?? false) {
            return back()->with('success', 'Konfirmasi pembayaran berhasil dikirim ke ' . $applicant->phone);
        }

        return back()->with('error', 'Gagal mengirim notifikasi: ' . ($result['whatsapp']['error'] ?? 'Unknown error'));
    }

    /**
     * Send test schedule
     */
    public function sendTestSchedule(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);
        
        $request->validate([
            'test_date' => 'required|date',
            'test_time' => 'required',
            'test_location' => 'required|string',
        ]);

        $testInfo = [
            'date' => date('d F Y', strtotime($request->test_date)),
            'time' => $request->test_time,
            'location' => $request->test_location,
        ];

        $result = $this->notificationService->sendPSBTestSchedule($applicant, $testInfo);

        // Update applicant status
        $applicant->update(['status' => 'scheduled']);

        if ($result['whatsapp']['success'] ?? false) {
            return back()->with('success', 'Jadwal tes berhasil dikirim ke ' . $applicant->phone);
        }

        return back()->with('error', 'Gagal mengirim notifikasi: ' . ($result['whatsapp']['error'] ?? 'Unknown error'));
    }

    /**
     * Send acceptance notification
     */
    public function sendAcceptance($id)
    {
        $applicant = Applicant::findOrFail($id);
        
        $result = $this->notificationService->sendPSBAcceptance($applicant);

        // Update applicant status
        $applicant->update(['status' => 'accepted']);

        if ($result['whatsapp']['success'] ?? false) {
            return back()->with('success', 'Pemberitahuan penerimaan berhasil dikirim ke ' . $applicant->phone);
        }

        return back()->with('error', 'Gagal mengirim notifikasi: ' . ($result['whatsapp']['error'] ?? 'Unknown error'));
    }

    /**
     * Send custom message
     */
    public function sendCustomMessage(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);
        
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        if (!$applicant->phone) {
            return back()->with('error', 'Nomor telepon tidak tersedia');
        }

        $result = $this->whatsappService->sendMessage(
            $applicant->phone,
            $request->message
        );

        if ($result['success'] ?? false) {
            return back()->with('success', 'Pesan berhasil dikirim ke ' . $applicant->phone);
        }

        return back()->with('error', 'Gagal mengirim pesan: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'notification_type' => 'required|in:registration,payment,test_schedule,acceptance,reminder',
            'test_date' => 'required_if:notification_type,test_schedule',
            'test_time' => 'required_if:notification_type,test_schedule',
            'test_location' => 'required_if:notification_type,test_schedule',
            'amount' => 'required_if:notification_type,payment',
            'reminder_message' => 'required_if:notification_type,reminder',
        ]);

        $additionalData = [];
        
        if ($request->notification_type === 'test_schedule') {
            $additionalData = [
                'date' => date('d F Y', strtotime($request->test_date)),
                'time' => $request->test_time,
                'location' => $request->test_location,
            ];
        } elseif ($request->notification_type === 'payment') {
            $additionalData['amount'] = $request->amount;
        } elseif ($request->notification_type === 'reminder') {
            $additionalData['message'] = $request->reminder_message;
        }

        $results = $this->notificationService->sendBulkNotifications(
            $request->applicant_ids,
            $request->notification_type,
            $additionalData
        );

        $successCount = collect($results)->where('result.whatsapp.success', true)->count();
        $totalCount = count($results);

        return back()->with('success', 
            "Berhasil mengirim {$successCount} dari {$totalCount} notifikasi"
        );
    }

    /**
     * Resend registration notification
     */
    public function resendRegistration($id)
    {
        $applicant = Applicant::findOrFail($id);
        
        $result = $this->notificationService->sendPSBRegistration($applicant);

        if ($result['whatsapp']['success'] ?? false) {
            return back()->with('success', 'Notifikasi pendaftaran berhasil dikirim ulang ke ' . $applicant->phone);
        }

        return back()->with('error', 'Gagal mengirim notifikasi: ' . ($result['whatsapp']['error'] ?? 'Unknown error'));
    }

    /**
     * Send document upload request
     */
    public function sendDocumentRequest(Applicant $applicant)
    {
        $phone = $applicant->phone ?? $applicant->father_phone ?? $applicant->mother_phone;
        
        if (!$phone) {
            return back()->with('error', 'Nomor telepon tidak tersedia.');
        }

        $message = "📄 *Permintaan Upload Dokumen*\n\n";
        $message .= "Halo {$applicant->full_name},\n\n";
        $message .= "Pembayaran Anda telah kami verifikasi.\n\n";
        $message .= "Silakan upload dokumen berikut:\n";
        $message .= "1. Fotocopy Kartu Keluarga (KK)\n";
        $message .= "2. Fotocopy Akta Kelahiran\n";
        $message .= "3. Fotocopy Ijazah/SKHUN\n";
        $message .= "4. Fotocopy Raport Semester 5 & 6\n\n";
        $message .= "📸 *Pas Foto 3x4 (2 lembar)* diserahkan saat daftar ulang.\n\n";
        $message .= "📱 Link: " . url('/pendaftaran/cek-status') . "\n\n";
        $message .= "Terima kasih.\n";
        $message .= "*{$applicant->school->name}*";

        try {
            $result = $this->whatsappService->sendMessage($phone, $message);
            
            if ($result['success'] ?? false) {
                return back()->with('success', 'Permintaan upload dokumen berhasil dikirim ke ' . $phone);
            }
            
            return back()->with('error', 'Gagal mengirim pesan: ' . ($result['error'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim pesan WhatsApp: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada sistem. Silakan coba lagi.');
        }
    }

    /**
     * Test WhatsApp connection
     */
    public function testConnection()
    {
        if (!$this->whatsappService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp service is disabled. Check .env configuration.'
            ]);
        }

        $info = $this->whatsappService->getAccountInfo();

        return response()->json($info);
    }

    /**
     * Preview notification
     */
    public function preview(Request $request)
    {
        $request->validate([
            'type' => 'required|in:registration,payment,test_schedule,acceptance',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $applicant = Applicant::with('school')->findOrFail($request->applicant_id);
        
        $templates = config('whatsapp-templates');
        $template = $templates["psb.{$request->type}"] ?? 'Template not found';

        // Replace variables with sample data
        $variables = [
            'nama' => $applicant->full_name,
            'nomor_registrasi' => $applicant->registration_number,
            'sekolah' => $applicant->school->name,
            'tahun_ajaran' => date('Y'),
            'biaya' => '50.000',
            'jumlah' => '50.000',
            'tanggal' => date('d F Y'),
            'tanggal_tes' => date('d F Y', strtotime('+7 days')),
            'waktu_tes' => '08:00 - 12:00 WIB',
            'tempat_tes' => $applicant->school->name,
            'program_keahlian' => 'Program Keahlian',
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'bank_holder' => 'Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)',
            'email' => $applicant->email,
            'contact_email' => 'psb@pembdanias.sch.id',
            'contact_phone' => '082373642864',
            'website' => 'https://pembdanias.sch.id',
            'upload_url' => route('public.registration.index'),
            'download_url' => route('public.registration.index'),
            'deadline' => date('d F Y', strtotime('+14 days')),
            'tanggal_daftar_ulang' => date('d F Y', strtotime('+7 days')),
            'tempat_daftar_ulang' => $applicant->school->name,
            'biaya_daftar_ulang' => '500.000',
        ];

        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return response()->json([
            'success' => true,
            'preview' => $template,
            'phone' => $applicant->phone,
            'applicant' => $applicant
        ]);
    }
}
