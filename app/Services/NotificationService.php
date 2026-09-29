<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Student;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send PSB Registration Notification
     * 
     * @param Applicant $applicant
     * @return array
     */
    public function sendPSBRegistration(Applicant $applicant)
    {
        if (!\App\Models\Setting::getValue('wa_send_psb_registration', true)) {
            return ['whatsapp' => ['success' => false, 'message' => 'WhatsApp notifikasi pendaftaran PSB dinonaktifkan.']];
        }

        $results = [];

        // Determine registration fee based on school
        $fee = $this->getRegistrationFee($applicant->school_id);

        // Prepare variables for template
        $variables = [
            'nama' => $applicant->full_name,
            'nomor_registrasi' => $applicant->registration_number,
            'sekolah' => $applicant->school->name,
            'tahun_ajaran' => $applicant->academicYear->year ?? date('Y'),
            'biaya' => number_format($fee, 0, ',', '.'),
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'bank_holder' => 'Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)',
            'email' => $applicant->email,
            'contact_phone' => $applicant->school->psb_contact_phone ?? '082373642864',
            'contact_name' => $applicant->school->psb_contact_person ?? 'Panitia PSB',
            'contact_email' => $applicant->school->email ?? 'psb@pembdanias.sch.id',
            'website' => $applicant->school->website ?? 'https://pembdanias.sch.id',
            'jalur' => $applicant->admission_path === 'prestasi' ? 'Prestasi' : 'Reguler',
        ];

        // Send WhatsApp - Berbeda untuk Prestasi vs Reguler
        if ($applicant->phone) {
            // Gunakan template yang berbeda berdasarkan jalur pendaftaran
            $templateName = $applicant->admission_path === 'prestasi' 
                ? 'psb.registration.prestasi' 
                : 'psb.registration';
                
            $waResult = $this->whatsappService->sendTemplate(
                $applicant->phone,
                $templateName,
                $variables
            );
            $results['whatsapp'] = $waResult;
        }

        // Send Email (if implemented)
        if ($applicant->email) {
            try {
                // Mail::to($applicant->email)->send(new PSBRegistrationMail($applicant, $variables));
                $results['email'] = ['success' => true, 'message' => 'Email sent (if configured)'];
            } catch (\Exception $e) {
                $results['email'] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // Log notification
        Log::info('PSB Registration notification sent', [
            'applicant_id' => $applicant->id,
            'registration_number' => $applicant->registration_number,
            'admission_path' => $applicant->admission_path,
            'results' => $results
        ]);

        return $results;
    }

    /**
     * Send PSB Payment Confirmation
     * 
     * @param Applicant $applicant
     * @param float $amount
     * @return array
     */
    public function sendPSBPaymentConfirmation(Applicant $applicant, $amount)
    {
        if (!\App\Models\Setting::getValue('wa_send_psb_payment', true)) {
            return ['whatsapp' => ['success' => false, 'message' => 'WhatsApp notifikasi pembayaran PSB dinonaktifkan.']];
        }

        $results = [];

        $uploadUrl = route('public.upload.documents', $applicant->registration_number);
        
        $variables = [
            'nama' => $applicant->full_name,
            'nomor_registrasi' => $applicant->registration_number,
            'jumlah' => number_format($amount, 0, ',', '.'),
            'tanggal' => now()->format('d F Y'),
            'upload_url' => $uploadUrl,
            'deadline' => now()->addDays(14)->format('d F Y'),
            'contact_phone' => $applicant->school->psb_contact_phone ?? '082373642864',
        ];

        // Send WhatsApp
        if ($applicant->phone) {
            $waResult = $this->whatsappService->sendTemplate(
                $applicant->phone,
                'psb.payment',
                $variables
            );
            $results['whatsapp'] = $waResult;
        }

        Log::info('PSB Payment confirmation sent', [
            'applicant_id' => $applicant->id,
            'amount' => $amount,
            'results' => $results
        ]);

        return $results;
    }

    /**
     * Send PSB Document Verification & Test Schedule
     * 
     * @param Applicant $applicant
     * @param array $testInfo ['date', 'time', 'location']
     * @return array
     */
    public function sendPSBTestSchedule(Applicant $applicant, $testInfo)
    {
        if (!\App\Models\Setting::getValue('wa_send_psb_test_schedule', true)) {
            return ['whatsapp' => ['success' => false, 'message' => 'WhatsApp notifikasi jadwal tes PSB dinonaktifkan.']];
        }

        $results = [];

        $downloadUrl = route('public.download.test-card', $applicant->registration_number);
        
        $variables = [
            'nama' => $applicant->full_name,
            'nomor_registrasi' => $applicant->registration_number,
            'tanggal_tes' => $testInfo['date'] ?? '-',
            'waktu_tes' => $testInfo['time'] ?? '-',
            'tempat_tes' => $testInfo['location'] ?? $applicant->school->name,
            'download_url' => $downloadUrl,
        ];

        // Send WhatsApp
        if ($applicant->phone) {
            $waResult = $this->whatsappService->sendTemplate(
                $applicant->phone,
                'psb.document',
                $variables
            );
            $results['whatsapp'] = $waResult;
        }

        Log::info('PSB Test schedule sent', [
            'applicant_id' => $applicant->id,
            'test_info' => $testInfo,
            'results' => $results
        ]);

        return $results;
    }

    /**
     * Send PSB Acceptance Notification
     * 
     * @param Applicant $applicant
     * @return array
     */
    public function sendPSBAcceptance(Applicant $applicant)
    {
        if (!\App\Models\Setting::getValue('wa_send_psb_acceptance', true)) {
            return ['whatsapp' => ['success' => false, 'message' => 'WhatsApp notifikasi kelulusan PSB dinonaktifkan.']];
        }

        $results = [];

        $variables = [
            'nama' => $applicant->full_name,
            'sekolah' => $applicant->school->name,
            'program_keahlian' => $applicant->programKeahlian->nama ?? 'Program Umum',
            'nomor_registrasi' => $applicant->registration_number,
            'tanggal_daftar_ulang' => now()->addDays(7)->format('d F Y'),
            'tempat_daftar_ulang' => $applicant->school->name,
            'biaya_daftar_ulang' => number_format(500000, 0, ',', '.'),
            'deadline' => now()->addDays(14)->format('d F Y'),
            'contact_phone' => $applicant->school->psb_contact_phone ?? '082373642864',
        ];

        // Send WhatsApp
        if ($applicant->phone) {
            $waResult = $this->whatsappService->sendTemplate(
                $applicant->phone,
                'psb.accepted',
                $variables
            );
            $results['whatsapp'] = $waResult;
        }

        Log::info('PSB Acceptance sent', [
            'applicant_id' => $applicant->id,
            'results' => $results
        ]);

        return $results;
    }

    /**
     * Send Custom Reminder
     * 
     * @param Applicant $applicant
     * @param string $message
     * @return array
     */
    public function sendPSBReminder(Applicant $applicant, $message)
    {
        if (!\App\Models\Setting::getValue('wa_send_psb_registration', true)) {
            return ['success' => false, 'message' => 'WhatsApp notifikasi pendaftaran PSB dinonaktifkan.'];
        }

        $variables = [
            'nama' => $applicant->full_name,
            'nomor_registrasi' => $applicant->registration_number,
            'pesan_reminder' => $message,
            'contact_phone' => $applicant->school->psb_contact_phone ?? '082373642864',
            'contact_email' => $applicant->school->email ?? 'psb@pembdanias.sch.id',
        ];

        if ($applicant->phone) {
            return $this->whatsappService->sendTemplate(
                $applicant->phone,
                'psb.reminder',
                $variables
            );
        }

        return ['success' => false, 'message' => 'No phone number'];
    }

    /**
     * Send Prestasi Approved Notification
     * 
     * Sent when admin verifies/approves achievement data.
     * Tells applicant to upload documents (skips payment).
     * 
     * @param Applicant $applicant
     * @return array
     */
    public function sendPrestasiApproved(Applicant $applicant)
    {
        if (!\App\Models\Setting::getValue('wa_send_psb_registration', true)) {
            return ['whatsapp' => ['success' => false, 'message' => 'WhatsApp notifikasi pendaftaran PSB dinonaktifkan.']];
        }

        $results = [];

        // Build prestasi detail from achievements
        $prestasiDetail = 'Jalur Prestasi';
        $achievement = $applicant->achievements()->first();
        if ($achievement) {
            $prestasiDetail = $achievement->achievement_name . ' (' . ucfirst($achievement->achievement_level) . ')';
        }

        $variables = [
            'nama' => $applicant->full_name,
            'nomor_registrasi' => $applicant->registration_number,
            'prestasi_detail' => $prestasiDetail,
            'upload_url' => url('/pendaftaran/cek-status'),
            'deadline' => now()->addDays(14)->format('d F Y'),
            'contact_phone' => $applicant->school->psb_contact_phone ?? '082373642864',
        ];

        // Send WhatsApp
        $phone = $applicant->phone ?? $applicant->father_phone ?? $applicant->mother_phone;
        if ($phone) {
            $waResult = $this->whatsappService->sendTemplate(
                $phone,
                'psb.prestasi.approved',
                $variables
            );
            $results['whatsapp'] = $waResult;
        }

        Log::info('PSB Prestasi Approved notification sent', [
            'applicant_id' => $applicant->id,
            'registration_number' => $applicant->registration_number,
            'results' => $results
        ]);

        return $results;
    }

    /**
     * Send Prestasi Rejected Notification
     * 
     * Sent when admin rejects achievement data.
     * Tells applicant to continue via reguler path (pay fee).
     * 
     * @param Applicant $applicant
     * @param string $rejectionReason
     * @return array
     */
    public function sendPrestasiRejected(Applicant $applicant, string $rejectionReason)
    {
        if (!\App\Models\Setting::getValue('wa_send_psb_registration', true)) {
            return ['whatsapp' => ['success' => false, 'message' => 'WhatsApp notifikasi pendaftaran PSB dinonaktifkan.']];
        }

        $results = [];

        $fee = $this->getRegistrationFee($applicant->school_id);

        $variables = [
            'nama' => $applicant->full_name,
            'nomor_registrasi' => $applicant->registration_number,
            'alasan_penolakan' => $rejectionReason,
            'biaya' => number_format($fee, 0, ',', '.'),
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'bank_holder' => 'Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)',
            'contact_phone' => $applicant->school->psb_contact_phone ?? '082373642864',
            'contact_email' => $applicant->school->email ?? 'psb@pembdanias.sch.id',
        ];

        // Send WhatsApp
        $phone = $applicant->phone ?? $applicant->father_phone ?? $applicant->mother_phone;
        if ($phone) {
            $waResult = $this->whatsappService->sendTemplate(
                $phone,
                'psb.prestasi.rejected',
                $variables
            );
            $results['whatsapp'] = $waResult;
        }

        Log::info('PSB Prestasi Rejected notification sent', [
            'applicant_id' => $applicant->id,
            'registration_number' => $applicant->registration_number,
            'reason' => $rejectionReason,
            'results' => $results
        ]);

        return $results;
    }

    /**
     * Send Payment Reminder to Student
     * 
     * @param Student $student
     * @param array $billInfo
     * @return array
     */
    public function sendPaymentReminder(Student $student, $billInfo)
    {
        if (!\App\Models\Setting::getValue('wa_send_payment_reminder', true)) {
            return ['success' => false, 'message' => 'WhatsApp notifikasi tagihan dinonaktifkan.'];
        }

        $variables = [
            'nama' => $student->full_name,
            'jenis_tagihan' => $billInfo['bill_type'] ?? 'SPP',
            'jumlah' => number_format($billInfo['amount'], 0, ',', '.'),
            'jatuh_tempo' => $billInfo['due_date'] ?? date('d F Y'),
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'bank_holder' => 'Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)',
            'contact_phone' => $student->school->psb_contact_phone ?? '082373642864',
        ];

        if ($student->phone) {
            return $this->whatsappService->sendTemplate(
                $student->phone,
                'payment.reminder',
                $variables
            );
        }

        return ['success' => false, 'message' => 'No phone number'];
    }

    /**
     * Get registration fee based on school
     * 
     * @param int $schoolId
     * @return int
     */
    private function getRegistrationFee($schoolId)
    {
        // School ID 3 is SMK with higher fee
        return $schoolId == 3 ? 300000 : 50000;
    }

    /**
     * Send bulk notifications to multiple applicants
     * 
     * @param array $applicantIds
     * @param string $templateType
     * @param array $additionalData
     * @return array
     */
    public function sendBulkNotifications($applicantIds, $templateType, $additionalData = [])
    {
        $results = [];
        $applicants = Applicant::whereIn('id', $applicantIds)->get();

        foreach ($applicants as $applicant) {
            try {
                switch ($templateType) {
                    case 'registration':
                        $result = $this->sendPSBRegistration($applicant);
                        break;
                    case 'payment':
                        $result = $this->sendPSBPaymentConfirmation($applicant, $additionalData['amount'] ?? 0);
                        break;
                    case 'test_schedule':
                        $result = $this->sendPSBTestSchedule($applicant, $additionalData);
                        break;
                    case 'acceptance':
                        $result = $this->sendPSBAcceptance($applicant);
                        break;
                    default:
                        $result = ['success' => false, 'message' => 'Unknown template'];
                }

                $results[] = [
                    'applicant_id' => $applicant->id,
                    'name' => $applicant->full_name,
                    'result' => $result
                ];

                // Anti-Ban Pacing: Jeda acak alami 10–18 detik per pesan & jeda istirahat tiap 8 pesan
                if ($index < count($applicantIds) - 1) {
                    $delaySeconds = rand(10, 18);
                    if (($index + 1) % 8 === 0) {
                        $delaySeconds += 40; // Istirahat batch ekstra 40 detik
                    }
                    sleep($delaySeconds);
                }
                
            } catch (\Exception $e) {
                $results[] = [
                    'applicant_id' => $applicant->id,
                    'name' => $applicant->full_name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Render a message template locally using key replacements.
     */
    public function renderTemplate(string $templateName, array $variables): string
    {
        $templates = config('whatsapp-templates');

        if (!isset($templates[$templateName])) {
            return '';
        }

        $template = $templates[$templateName];

        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return $template;
    }

    /**
     * Send bulk WhatsApp notification to all active enrolled students in a course.
     */
    public function sendLmsNotification(LmsCourse $course, string $templateName, array $extraVariables = []): array
    {
        if (!\App\Models\Setting::getValue('wa_send_lms_notification', true)) {
            return ['success' => false, 'message' => 'WhatsApp notifikasi LMS dinonaktifkan.'];
        }

        // Get enrollments with active students and their parent user accounts
        $enrollments = LmsEnrollment::whereIn('lms_class_id', $course->lmsClasses->pluck('id'))
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->with(['student.user'])
            ->get();

        $recipients = [];
        $teacherName = $course->teacher->full_name ?? ($course->teacher->user->name ?? 'Guru');
        $courseLink = route('siswa.lms.show', $course->id);

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student || !$student->phone) {
                continue;
            }

            $variables = array_merge([
                'nama' => $student->full_name,
                'course_name' => $course->course_name,
                'teacher_name' => $teacherName,
                'link' => $courseLink,
            ], $extraVariables);

            $message = $this->renderTemplate($templateName, $variables);
            if (!$message) {
                continue;
            }

            $recipients[] = [
                'phone' => $student->phone,
                'message' => $message,
                'options' => [],
            ];
        }

        if (count($recipients) > 0) {
            return $this->whatsappService->sendBulk($recipients);
        }

        return [
            'success' => false,
            'message' => 'No active students with phone numbers found'
        ];
    }
}

