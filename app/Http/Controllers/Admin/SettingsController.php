<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private function authorizeAccess()
    {
        // Hanya SuperAdmin yang bisa akses settings sistem
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya SuperAdmin yang dapat mengakses Pengaturan Sistem.');
        }
    }

    /**
     * Display late fee settings page
     */
    public function lateFees()
    {
        $this->authorizeAccess();
        // Get current late fee settings
        $settings = [
            'enabled' => Setting::getValue('late_fee_enabled', false),
            'grace_period' => Setting::getValue('late_fee_grace_period', 3),
            'amount' => Setting::getValue('late_fee_amount', 0),
            'type' => Setting::getValue('late_fee_type', 'fixed'),
        ];

        return view('admin.settings.late-fees', compact('settings'));
    }

    /**
     * Update late fee settings
     */
    public function updateLateFees(Request $request)
    {
        $this->authorizeAccess();
        $validated = $request->validate([
            'late_fee_enabled' => 'boolean',
            'late_fee_grace_period' => 'required|integer|min:0|max:30',
            'late_fee_amount' => 'required|numeric|min:0',
            'late_fee_type' => 'required|in:fixed,percentage',
        ]);

        // Update each setting
        Setting::setValue('late_fee_enabled', $request->boolean('late_fee_enabled'), 'boolean', 'late_fees');
        Setting::setValue('late_fee_grace_period', $validated['late_fee_grace_period'], 'integer', 'late_fees');
        Setting::setValue('late_fee_amount', $validated['late_fee_amount'], 'integer', 'late_fees');
        Setting::setValue('late_fee_type', $validated['late_fee_type'], 'string', 'late_fees');

        return redirect()
            ->route('admin.settings.late-fees')
            ->with('success', 'Pengaturan biaya administrasi berhasil disimpan!');
    }

    /**
     * Display general system settings
     */
    public function index()
    {
        $this->authorizeAccess();
        
        // Get all settings grouped by category
        $settings = Setting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Preview late fee calculation
     */
    public function previewLateFee(Request $request)
    {
        $this->authorizeAccess();
        $validated = $request->validate([
            'bill_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'days_overdue' => 'required|integer|min:0',
            'grace_period' => 'required|integer|min:0',
            'fee_amount' => 'required|numeric|min:0',
            'fee_type' => 'required|in:fixed,percentage',
        ]);

        $billAmount = $validated['bill_amount'];
        $paidAmount = $validated['paid_amount'] ?? 0;
        $daysOverdue = $validated['days_overdue'];
        $gracePeriod = $validated['grace_period'];
        $feeAmount = $validated['fee_amount'];
        $feeType = $validated['fee_type'];

        // Calculate late fee
        $lateFee = 0;
        if ($daysOverdue > $gracePeriod) {
            $outstanding = $billAmount - $paidAmount;
            
            if ($feeType === 'percentage') {
                $lateFee = ($outstanding * $feeAmount) / 100;
            } else {
                $lateFee = $feeAmount;
            }
        }

        $totalWithLateFee = $outstanding + $lateFee;

        return response()->json([
            'bill_amount' => $billAmount,
            'paid_amount' => $paidAmount,
            'outstanding' => $outstanding,
            'days_overdue' => $daysOverdue,
            'grace_period' => $gracePeriod,
            'late_fee' => $lateFee,
            'total_with_late_fee' => $totalWithLateFee,
            'applicable' => $daysOverdue > $gracePeriod,
        ]);
    }

    /**
     * Display report card predicate settings page
     */
    public function reportCards()
    {
        $this->authorizeAccess();
        $settings = Setting::getValue('raport_grade_conversion', []);
        $showReportCard = Setting::getValue('show_report_card', false);
        
        return view('admin.settings.report-cards', compact('settings', 'showReportCard'));
    }

    /**
     * Update report card predicate settings
     */
    public function updateReportCards(Request $request)
    {
        $this->authorizeAccess();
        
        $validated = $request->validate([
            'grade' => 'required|array',
            'grade.*.mode' => 'required|in:kkm_interval,static',
            'grade.*.static_a' => 'required|numeric|min:0|max:100',
            'grade.*.static_b' => 'required|numeric|min:0|max:100',
            'grade.*.static_c' => 'required|numeric|min:0|max:100',
            'show_report_card' => 'nullable|boolean',
        ]);

        Setting::setValue('raport_grade_conversion', $validated['grade'], 'json', 'raport');
        Setting::setValue('show_report_card', $request->boolean('show_report_card'), 'boolean', 'raport');

        return redirect()
            ->route('admin.settings.report-cards')
            ->with('success', 'Pengaturan rapor berhasil disimpan!');
    }

    /**
     * Display feature authorization settings page
     */
    public function features()
    {
        $this->authorizeFeatureAccess();

        $settings = [
            // Siswa & Orang Tua
            'show_report_card' => Setting::getValue('show_report_card', true),
            'siswa_view_attendance_recap' => Setting::getValue('siswa_view_attendance_recap', true),
            'siswa_view_reputation_leaderboard' => Setting::getValue('siswa_view_reputation_leaderboard', true),
            'siswa_access_cbt' => Setting::getValue('siswa_access_cbt', true),
            'siswa_access_lms' => Setting::getValue('siswa_access_lms', true),

            // Guru
            'guru_can_edit_grades' => Setting::getValue('guru_can_edit_grades', false),
            'guru_view_reputation_leaderboard' => Setting::getValue('guru_view_reputation_leaderboard', true),
            'guru_can_see_payroll_details' => Setting::getValue('guru_can_see_payroll_details', true),
            'guru_access_cbt' => Setting::getValue('guru_access_cbt', true),
            'guru_access_lms' => Setting::getValue('guru_access_lms', true),

            // Pegawai
            'pegawai_can_request_leave' => Setting::getValue('pegawai_can_request_leave', true),
            'pegawai_can_see_payroll_details' => Setting::getValue('pegawai_can_see_payroll_details', true),
            'pegawai_view_attendance_recap' => Setting::getValue('pegawai_view_attendance_recap', true),

            // Presensi & Geofencing GPS
            'attendance_max_radius' => Setting::getValue('attendance_max_radius', 175),
            'school_latitude' => Setting::getValue('school_latitude', '1.282500'),
            'school_longitude' => Setting::getValue('school_longitude', '97.619000'),

            // WhatsApp Otomatis
            'wa_send_psb_registration' => Setting::getValue('wa_send_psb_registration', true),
            'wa_send_psb_payment' => Setting::getValue('wa_send_psb_payment', true),
            'wa_send_psb_test_schedule' => Setting::getValue('wa_send_psb_test_schedule', true),
            'wa_send_psb_acceptance' => Setting::getValue('wa_send_psb_acceptance', true),
            'wa_send_payment_reminder' => Setting::getValue('wa_send_payment_reminder', true),
            'wa_send_lms_notification' => Setting::getValue('wa_send_lms_notification', true),
            'wa_send_counseling_record' => Setting::getValue('wa_send_counseling_record', true),
            'wa_send_reputation_award' => Setting::getValue('wa_send_reputation_award', true),
            'wa_send_payment_receipt' => Setting::getValue('wa_send_payment_receipt', true),
            'wa_send_teaching_reminder' => Setting::getValue('wa_send_teaching_reminder', true),
            'wa_send_grade_published' => Setting::getValue('wa_send_grade_published', true),
            'wa_send_attendance_alert' => Setting::getValue('wa_send_attendance_alert', true),
        ];

        return view('admin.settings.features', compact('settings'));
    }

    /**
     * Update feature authorization settings
     */
    public function updateFeatures(Request $request)
    {
        $this->authorizeFeatureAccess();

        $featureKeys = [
            'show_report_card',
            'siswa_view_attendance_recap',
            'siswa_view_reputation_leaderboard',
            'siswa_access_cbt',
            'siswa_access_lms',
            'guru_can_edit_grades',
            'guru_view_reputation_leaderboard',
            'guru_can_see_payroll_details',
            'guru_access_cbt',
            'guru_access_lms',
            'pegawai_can_request_leave',
            'pegawai_can_see_payroll_details',
            'pegawai_view_attendance_recap',
            'wa_send_psb_registration',
            'wa_send_psb_payment',
            'wa_send_psb_test_schedule',
            'wa_send_psb_acceptance',
            'wa_send_payment_reminder',
            'wa_send_lms_notification',
            'wa_send_counseling_record',
            'wa_send_reputation_award',
            'wa_send_payment_receipt',
            'wa_send_teaching_reminder',
            'wa_send_grade_published',
            'wa_send_attendance_alert',
        ];

        foreach ($featureKeys as $key) {
            Setting::setValue($key, $request->boolean($key), 'boolean', 'features');
        }

        if ($request->filled('attendance_max_radius')) {
            $radius = max(20, (int) $request->input('attendance_max_radius', 175));
            Setting::setValue('attendance_max_radius', $radius, 'integer', 'features');
        }
        if ($request->filled('school_latitude')) {
            Setting::setValue('school_latitude', $request->input('school_latitude'), 'string', 'features');
        }
        if ($request->filled('school_longitude')) {
            Setting::setValue('school_longitude', $request->input('school_longitude'), 'string', 'features');
        }

        return redirect()
            ->route('admin.settings.features')
            ->with('success', 'Konfigurasi otorisasi fitur dan pengaturan presensi berhasil diperbarui!');
    }

    private function authorizeFeatureAccess()
    {
        if (!auth()->user() || (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdminSekolah())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Otorisasi Fitur.');
        }
    }

    /**
     * Display WhatsApp Automation Settings page
     */
    public function whatsapp()
    {
        $this->authorizeFeatureAccess();

        $waKeys = [
            'wa_send_attendance_alert' => 'Notifikasi Absensi Siswa (Hadir / Terlambat / Alpa)',
            'wa_send_payment_receipt' => 'Notifikasi Kwitansi Pembayaran SPP Lunas',
            'wa_send_payment_reminder' => 'Notifikasi Pengingat Tagihan SPP Jatuh Tempo',
            'wa_send_grade_published' => 'Notifikasi Penerbitan Nilai & Rapor',
            'wa_send_counseling_record' => 'Notifikasi Catatan Pembinaan BK (Bimbingan Konseling)',
            'wa_send_reputation_award' => 'Notifikasi Apresiasi Penghargaan & Poin Siswa',
            'wa_send_psb_registration' => 'Notifikasi Pendaftaran Siswa Baru (PSB)',
            'wa_send_psb_payment' => 'Notifikasi Pembayaran Pendaftaran PSB',
            'wa_send_psb_test_schedule' => 'Notifikasi Jadwal Tes Masuk PSB',
            'wa_send_psb_acceptance' => 'Notifikasi Pengumuman Kelulusan PSB',
            'wa_send_lms_notification' => 'Notifikasi LMS (Materi, Tugas, & Kuis Baru)',
            'wa_send_teaching_reminder' => 'Notifikasi Pengingat Jadwal Mengajar Guru',
        ];

        $settings = [];
        foreach ($waKeys as $key => $label) {
            $settings[$key] = [
                'label' => $label,
                'enabled' => Setting::getValue($key, true),
            ];
        }

        $service = new \App\Services\WhatsAppService();
        $accountInfo = $service->getAccountInfo();
        $activeProvider = $service->getActiveProvider();
        $providerLabel = $service->getProviderLabel();
        $providersInfo = $service->getProvidersInfo();
        $waEnabled = $service->isEnabled();

        return view('admin.settings.whatsapp', compact('settings', 'accountInfo', 'activeProvider', 'providerLabel', 'providersInfo', 'waEnabled'));
    }

    /**
     * Switch WhatsApp Gateway Provider (Fonnte <-> Selfhosted Baileys)
     */
    public function switchWhatsappProvider(Request $request)
    {
        $this->authorizeFeatureAccess();

        $request->validate([
            'provider' => 'required|string|in:fonnte,selfhosted',
        ]);

        $result = \App\Services\WhatsAppService::switchProvider($request->input('provider'));

        if ($result['success']) {
            return redirect()
                ->route('admin.settings.whatsapp')
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('admin.settings.whatsapp')
            ->with('error', $result['message'] ?? 'Gagal mengganti provider');
    }

    /**
     * Update WhatsApp Automation Settings
     */
    public function updateWhatsapp(Request $request)
    {
        $this->authorizeFeatureAccess();

        $waKeys = [
            'wa_send_attendance_alert',
            'wa_send_payment_receipt',
            'wa_send_payment_reminder',
            'wa_send_grade_published',
            'wa_send_counseling_record',
            'wa_send_reputation_award',
            'wa_send_psb_registration',
            'wa_send_psb_payment',
            'wa_send_psb_test_schedule',
            'wa_send_psb_acceptance',
            'wa_send_lms_notification',
            'wa_send_teaching_reminder',
        ];

        foreach ($waKeys as $key) {
            Setting::setValue($key, $request->boolean($key), 'boolean', 'features');
        }

        return redirect()
            ->route('admin.settings.whatsapp')
            ->with('success', 'Pengaturan otomatisasi pengiriman WhatsApp berhasil disimpan!');
    }

    /**
     * Test sending WhatsApp message from Settings page
     */
    public function testWhatsapp(Request $request)
    {
        $this->authorizeFeatureAccess();

        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $service = new \App\Services\WhatsAppService();
        $result = $service->sendMessage($request->input('phone'), $request->input('message'));

        if (!empty($result['success'])) {
            return redirect()
                ->route('admin.settings.whatsapp')
                ->with('success', 'Pesan uji coba WhatsApp BERHASIL terkirim ke ' . $request->input('phone'));
        }

        $errorMsg = $result['response']['message'] ?? $result['error'] ?? 'Gagal mengirim pesan WhatsApp';
        return redirect()
            ->route('admin.settings.whatsapp')
            ->with('error', 'Gagal mengirim pesan: ' . $errorMsg);
    }

    /**
     * Display WhatsApp Templates Editor page
     */
    public function whatsappTemplates()
    {
        $this->authorizeFeatureAccess();

        $defaultTemplates = config('whatsapp-templates');

        $templateList = [
            'student.attendance' => [
                'title' => '📌 Notifikasi Kehadiran Siswa (Absensi)',
                'variables' => ['{nama}', '{tanggal}', '{classroom_name}', '{status}'],
            ],
            'teacher.attendance' => [
                'title' => '👨‍🏫 Notifikasi Presensi Kehadiran Guru',
                'variables' => ['{nama}', '{tanggal}', '{waktu}', '{status}', '{tipe_absen}', '{jabatan}'],
            ],
            'employee.attendance' => [
                'title' => '💼 Notifikasi Presensi Kehadiran Pegawai / Staf',
                'variables' => ['{nama}', '{tanggal}', '{waktu}', '{status}', '{tipe_absen}', '{jabatan}'],
            ],
            'payment.receipt' => [
                'title' => '💳 Notifikasi Kwitansi Pembayaran SPP (Lunas)',
                'variables' => ['{nama}', '{transaction_id}', '{jumlah}', '{tanggal}', '{bulan}'],
            ],
            'payment.reminder' => [
                'title' => '💰 Notifikasi Pengingat Tagihan SPP',
                'variables' => ['{nama}', '{jenis_tagihan}', '{jumlah}', '{jatuh_tempo}', '{bank_name}', '{bank_account}', '{bank_holder}'],
            ],
            'student.grade_published' => [
                'title' => '📊 Notifikasi Pengumuman Nilai & Rapor',
                'variables' => ['{nama}', '{classroom_name}', '{subject_name}', '{grade_type}', '{score}', '{notes}'],
            ],
            'student.counseling' => [
                'title' => '⚠️ Notifikasi Catatan Pembinaan (BK)',
                'variables' => ['{nama}', '{title}', '{reason}', '{action}'],
            ],
            'student.award' => [
                'title' => '🏆 Notifikasi Apresiasi Penghargaan & Poin',
                'variables' => ['{nama}', '{title}', '{points}', '{reason}'],
            ],
            'psb.registration' => [
                'title' => '🏫 Notifikasi Konfirmasi Pendaftaran PSB',
                'variables' => ['{nama}', '{nomor_registrasi}', '{sekolah}', '{tahun_ajaran}', '{biaya}', '{email}'],
            ],
            'lms.assignment.published' => [
                'title' => '📚 Notifikasi Tugas Baru LMS',
                'variables' => ['{nama}', '{course_name}', '{title}', '{due_date}', '{link}'],
            ],
        ];

        $templates = [];
        foreach ($templateList as $key => $info) {
            $settingKey = 'wa_tpl_' . str_replace('.', '_', $key);
            $savedValue = Setting::getValue($settingKey, null);
            $templates[$key] = [
                'title' => $info['title'],
                'variables' => $info['variables'],
                'content' => $savedValue !== null ? $savedValue : ($defaultTemplates[$key] ?? ''),
            ];
        }

        // Delivery thresholds & condition settings
        $conditions = [
            'wa_cond_notify_absent' => Setting::getValue('wa_cond_notify_absent', true),
            'wa_cond_notify_late' => Setting::getValue('wa_cond_notify_late', true),
            'wa_cond_notify_present' => Setting::getValue('wa_cond_notify_present', true),
            'wa_cond_late_threshold_minutes' => Setting::getValue('wa_cond_late_threshold_minutes', 15),
            'wa_cond_spp_reminder_days' => Setting::getValue('wa_cond_spp_reminder_days', 3),
            'wa_target_parent' => Setting::getValue('wa_target_parent', true),
            'wa_target_student' => Setting::getValue('wa_target_student', false),
        ];

        return view('admin.settings.whatsapp-templates', compact('templates', 'conditions'));
    }

    /**
     * Update WhatsApp Templates & Delivery Conditions
     */
    public function updateWhatsappTemplates(Request $request)
    {
        $this->authorizeFeatureAccess();

        // Save Template Text Customizations
        $templateKeys = [
            'student.attendance',
            'teacher.attendance',
            'employee.attendance',
            'payment.receipt',
            'payment.reminder',
            'student.grade_published',
            'student.counseling',
            'student.award',
            'psb.registration',
            'lms.assignment.published',
        ];

        foreach ($templateKeys as $key) {
            $settingKey = 'wa_tpl_' . str_replace('.', '_', $key);
            if ($request->has("tpl_$settingKey")) {
                Setting::setValue($settingKey, $request->input("tpl_$settingKey"), 'string', 'whatsapp_templates');
            }
        }

        // Save Conditions & Thresholds
        Setting::setValue('wa_cond_notify_absent', $request->boolean('wa_cond_notify_absent'), 'boolean', 'whatsapp_conditions');
        Setting::setValue('wa_cond_notify_late', $request->boolean('wa_cond_notify_late'), 'boolean', 'whatsapp_conditions');
        Setting::setValue('wa_cond_notify_present', $request->boolean('wa_cond_notify_present'), 'boolean', 'whatsapp_conditions');
        Setting::setValue('wa_cond_late_threshold_minutes', (int) $request->input('wa_cond_late_threshold_minutes', 15), 'integer', 'whatsapp_conditions');
        Setting::setValue('wa_cond_spp_reminder_days', (int) $request->input('wa_cond_spp_reminder_days', 3), 'integer', 'whatsapp_conditions');
        Setting::setValue('wa_target_parent', $request->boolean('wa_target_parent'), 'boolean', 'whatsapp_conditions');
        Setting::setValue('wa_target_student', $request->boolean('wa_target_student'), 'boolean', 'whatsapp_conditions');

        return redirect()
            ->route('admin.settings.whatsapp.templates')
            ->with('success', 'Template teks pesan dan syarat pengiriman WhatsApp berhasil diperbarui!');
    }

    /**
     * Test sending Executive Digest WhatsApp message
     */
    public function testExecutiveDigest(Request $request)
    {
        $this->authorizeFeatureAccess();

        $request->validate([
            'digest_type' => 'required|string',
        ]);

        $type = $request->input('digest_type');
        $reportService = app(\App\Services\ExecutiveReportService::class);

        switch ($type) {
            case 'principal_attendance':
                $res = $reportService->sendPrincipalDailyAttendanceDigest();
                break;
            case 'homeroom_attendance':
                $res = $reportService->sendHomeroomDailyAttendanceDigest();
                break;
            case 'principal_spp':
                $res = $reportService->sendPrincipalMonthlySppDigest();
                break;
            case 'homeroom_spp':
                $res = $reportService->sendHomeroomMonthlySppDigest();
                break;
            case 'principal_lms':
                $res = $reportService->sendPrincipalWeeklyLmsDigest();
                break;
            case 'homeroom_lms':
                $res = $reportService->sendHomeroomWeeklyLmsDigest();
                break;
            case 'points_weekly':
                $res = $reportService->sendWeeklyStudentPointsDigest();
                break;
            case 'award_sample':
                $res = $reportService->notifyStudentAward(
                    'Ahmad Fajar',
                    'XI IPA 1',
                    'Juara 1 LKS Informatika SMK 2026',
                    50,
                    'Meraih Juara 1 Tingkat Provinsi'
                );
                break;
            case 'edaran_sample':
                $res = $reportService->notifySuratEdaran(
                    'Surat Edaran Libur Hari Raya & Penetapan Seragam Baru 2026',
                    'https://perguruanpembda.com/download/surat-edaran-2026.pdf'
                );
                break;
            default:
                $res = ['success' => false, 'message' => 'Jenis laporan eksekutif tidak dikenal'];
        }

        if (!empty($res['success'])) {
            return redirect()
                ->route('admin.settings.whatsapp')
                ->with('success', 'Uji Coba Laporan Eksekutif [' . $type . '] BERHASIL dieksekusi: ' . ($res['message'] ?? 'Terkirim'));
        }

        return redirect()
            ->route('admin.settings.whatsapp')
            ->with('error', 'Gagal mengeksekusi Laporan Eksekutif: ' . ($res['message'] ?? 'Error'));
    }
}
