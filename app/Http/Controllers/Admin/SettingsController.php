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

        $automationGroups = $this->getWhatsappAutomationGroups();

        // Populate enabled state from database settings
        $groupedSettings = [];
        $allSettingsFlat = [];
        foreach ($automationGroups as $groupKey => $group) {
            $groupedSettings[$groupKey] = $group;
            foreach ($group['items'] as $itemKey => $item) {
                $isEnabled = Setting::getValue($itemKey, true);
                $groupedSettings[$groupKey]['items'][$itemKey]['enabled'] = $isEnabled;
                $allSettingsFlat[$itemKey] = [
                    'label' => $item['label'],
                    'enabled' => $isEnabled,
                ];
            }
        }

        $settings = $allSettingsFlat;

        $service = new \App\Services\WhatsAppService();
        $accountInfo = $service->getAccountInfo();
        $activeProvider = $service->getActiveProvider();
        $providerLabel = $service->getProviderLabel();
        $providersInfo = $service->getProvidersInfo();
        $waEnabled = $service->isEnabled();

        return view('admin.settings.whatsapp', compact('settings', 'groupedSettings', 'accountInfo', 'activeProvider', 'providerLabel', 'providersInfo', 'waEnabled'));
    }

    /**
     * Get categorized WhatsApp automations grouped by target audience
     */
    protected function getWhatsappAutomationGroups(): array
    {
        return [
            'siswa' => [
                'target' => 'Ke Siswa & Wali Murid',
                'description' => 'Otomatisasi pengiriman pesan ke nomor HP Siswa dan Orang Tua / Wali Murid',
                'icon' => 'fas fa-user-graduate',
                'color' => 'emerald',
                'badge' => 'Siswa & Wali',
                'items' => [
                    'wa_send_attendance_alert' => [
                        'label' => 'Presensi Siswa Harian (Hadir / Terlambat / Alpa)',
                        'desc' => 'Notifikasi instan ke WhatsApp orang tua saat siswa tap kartu RFID / presensi dicatat guru.',
                    ],
                    'wa_send_payment_receipt' => [
                        'label' => 'Kwitansi Pembayaran SPP & Iuran Sekolah Lunas',
                        'desc' => 'Bukti tanda terima pembayaran SPP dan rincian transaksi dikirim ke wali murid.',
                    ],
                    'wa_send_payment_reminder' => [
                        'label' => 'Pengingat Tagihan SPP Jatuh Tempo',
                        'desc' => 'Peringatan otomatis sebelum tanggal jatuh tempo pembayaran SPP bulanan.',
                    ],
                    'wa_send_grade_published' => [
                        'label' => 'Penerbitan Nilai & Rapor Digital',
                        'desc' => 'Pemberitahuan saat nilai tugas, PTS, PAS, atau Rapor semester telah difinalisasi.',
                    ],
                    'wa_send_reputation_award' => [
                        'label' => 'Poin Prestasi & Penghargaan Siswa',
                        'desc' => 'Apresiasi dan ucapan selamat kepada orang tua saat siswa meraih prestasi atau poin positif.',
                    ],
                    'wa_send_lms_notification' => [
                        'label' => 'Aktivitas LMS (Materi Baru, Tugas, & Kuis)',
                        'desc' => 'Pemberitahuan materi belajar, tugas rumah, atau kuis baru yang diunggah guru.',
                    ],
                    'wa_send_psb_registration' => [
                        'label' => 'Konfirmasi Pendaftaran Calon Siswa Baru (PSB)',
                        'desc' => 'Kirim nomor pendaftaran dan panduan langkah selanjutnya ke nomor pendaftar.',
                    ],
                    'wa_send_psb_payment' => [
                        'label' => 'Konfirmasi Pembayaran Formulir PSB',
                        'desc' => 'Kwitansi verifikasi pembayaran formulir pendaftaran siswa baru.',
                    ],
                    'wa_send_psb_test_schedule' => [
                        'label' => 'Jadwal Tes Seleksi & Wawancara PSB',
                        'desc' => 'Pemberitahuan waktu, ruangan, dan tata tertib ujian masuk calon siswa.',
                    ],
                    'wa_send_psb_acceptance' => [
                        'label' => 'Pengumuman Hasil Kelulusan PSB',
                        'desc' => 'Informasi kelulusan penerimaan dan panduan daftar ulang.',
                    ],
                ],
            ],

            'guru' => [
                'target' => 'Ke Guru (Tenaga Pendidik)',
                'description' => 'Otomatisasi pengiriman pesan ke nomor WhatsApp Guru dan Pengajar Mata Pelajaran',
                'icon' => 'fas fa-chalkboard-teacher',
                'color' => 'indigo',
                'badge' => 'Dewan Guru',
                'items' => [
                    'wa_send_teaching_reminder' => [
                        'label' => 'Pengingat Jadwal Mengajar Harian (Pagi)',
                        'desc' => 'Pengingat jam mengajar, kelas rombel, dan mata pelajaran yang diampu hari ini.',
                    ],
                    'wa_send_guru_lms_submission' => [
                        'label' => 'Notifikasi Pengumpulan Tugas & Kuis Siswa',
                        'desc' => 'Pemberitahuan rekap saat seluruh siswa kelas telah mengumpulkan tugas di LMS.',
                    ],
                    'wa_send_guru_meeting_alert' => [
                        'label' => 'Undangan Rapat Dewan Guru & Jadwal Piket',
                        'desc' => 'Pemberitahuan agenda rapat dinas, briefing dewan guru, dan jadwal piket harian.',
                    ],
                    'wa_send_guru_training_alert' => [
                        'label' => 'Jadwal Pelatihan & Supervisi Akademik',
                        'desc' => 'Pemberitahuan jadwal supervisi kelas oleh Pengawas/Kepsek dan agenda workshop guru.',
                    ],
                ],
            ],

            'wali_kelas' => [
                'target' => 'Ke Wali Kelas',
                'description' => 'Laporan rekapitulasi berkala kondisi kelas binaan kepada masing-masing Wali Kelas',
                'icon' => 'fas fa-user-friends',
                'color' => 'teal',
                'badge' => 'Wali Kelas',
                'items' => [
                    'wa_send_homeroom_attendance' => [
                        'label' => 'Laporan Rekap Presensi Harian Siswa Binaan',
                        'desc' => 'Rekap harian (Hadir, Sakit, Izin, Alpa) seluruh siswa di kelas binaan pada pukul 08:00.',
                    ],
                    'wa_send_homeroom_spp' => [
                        'label' => 'Laporan Rekap Pembayaran SPP Kelas Binaan',
                        'desc' => 'Daftar siswa yang sudah lunas dan yang masih memiliki tunggakan SPP bulanan.',
                    ],
                    'wa_send_homeroom_lms' => [
                        'label' => 'Laporan Keaktifan & Ranking LMS Kelas',
                        'desc' => 'Rekapitulasi siswa paling aktif dan yang belum mengerjakan tugas di LMS.',
                    ],
                    'wa_send_homeroom_bk_alert' => [
                        'label' => 'Notifikasi Catatan Pembinaan BK Siswa Binaan',
                        'desc' => 'Pemberitahuan langsung jika salah satu siswa di kelas binaan mendapat catatan khusus BK.',
                    ],
                ],
            ],

            'pegawai' => [
                'target' => 'Ke Pegawai / Tenaga Kependidikan',
                'description' => 'Otomatisasi pengingat kehadiran dan kedinasan staf Tata Usaha, Keamanan, dan Karyawan',
                'icon' => 'fas fa-id-badge',
                'color' => 'sky',
                'badge' => 'Tendik / Staf',
                'items' => [
                    'wa_send_staff_attendance_reminder' => [
                        'label' => 'Pengingat Presensi Masuk & Pulang Kerja',
                        'desc' => 'Pengingat tap presensi kehadiran staf sebelum jam kerja dimulai dan saat jam pulang.',
                    ],
                    'wa_send_staff_payroll_notice' => [
                        'label' => 'Pemberitahuan Penerbitan Slip Gaji Bulanan',
                        'desc' => 'Notifikasi bahwa slip honorarium / gaji dan tunjangan telah diproses bagian keuangan.',
                    ],
                    'wa_send_staff_announcement' => [
                        'label' => 'Pengumuman Kedinasan & Jam Operasional Tendik',
                        'desc' => 'Informasi kedinasan khusus tenaga kependidikan dan penyesuaian jam kerja kantor.',
                    ],
                ],
            ],

            'kepala_sekolah' => [
                'target' => 'Ke Kepala Sekolah',
                'description' => 'Laporan eksekutif ringkas harian/mingguan langsung ke WhatsApp Kepala Sekolah',
                'icon' => 'fas fa-user-tie',
                'color' => 'amber',
                'badge' => 'Kepala Sekolah',
                'items' => [
                    'wa_send_principal_attendance' => [
                        'label' => 'Rekap Eksekutif Presensi Harian Sekolah (08:00 WIB)',
                        'desc' => 'Persentase kehadiran siswa, guru hadir, dan guru izin/piket dikirim setiap pagi.',
                    ],
                    'wa_send_principal_spp' => [
                        'label' => 'Rekap Eksekutif Realisasi SPP & Keuangan Bulanan',
                        'desc' => 'Ringkasan total penerimaan SPP, target bulanan, dan persentase kepatuhan bayar.',
                    ],
                    'wa_send_principal_lms' => [
                        'label' => 'Rekap Kinerja Pembelajaran Guru & LMS Mingguan',
                        'desc' => 'Monitoring guru yang aktif mengunggah materi, kuis, dan interaksi pembelajaran.',
                    ],
                    'wa_send_principal_critical_cases' => [
                        'label' => 'Laporan Insiden Kritis & Kasus Kedisiplinan Berat',
                        'desc' => 'Pemberitahuan darurat bila terjadi pelanggaran berat atau kasus siswa yang butuh atensi pimpinan.',
                    ],
                ],
            ],

            'bk' => [
                'target' => 'Ke Guru BK (Bimbingan Konseling)',
                'description' => 'Peringatan dini dan catatan konseling untuk Guru Bimbingan Konseling',
                'icon' => 'fas fa-user-shield',
                'color' => 'purple',
                'badge' => 'Bimbingan Konseling',
                'items' => [
                    'wa_send_counseling_record' => [
                        'label' => 'Notifikasi Catatan Konseling & Pembinaan Siswa',
                        'desc' => 'Pengarsipan dan konfirmasi sesi konseling yang telah dilaksanakan.',
                    ],
                    'wa_send_bk_chronic_absenteeism' => [
                        'label' => 'Peringatan Dini Siswa Alpa / Bolos Berulang (Early Warning)',
                        'desc' => 'Peringatan otomatis saat siswa tidak hadir berturut-turut 3 hari tanpa keterangan.',
                    ],
                    'wa_send_bk_parent_summons' => [
                        'label' => 'Notifikasi Penerbitan Surat Panggilan Orang Tua / Home Visit',
                        'desc' => 'Pemberitahuan kepada guru BK saat surat panggilan wali murid diterbitkan sistem.',
                    ],
                ],
            ],

            'panitia_pkl' => [
                'target' => 'Ke Panitia PKL (Prakerin / Magang)',
                'description' => 'Otomatisasi alur kerja Praktek Kerja Lapangan bagi Koordinator dan Pembimbing PKL SMK',
                'icon' => 'fas fa-industry',
                'color' => 'blue',
                'badge' => 'Panitia PKL',
                'items' => [
                    'wa_send_pkl_registration' => [
                        'label' => 'Pengajuan Lokasi & Verifikasi DUDI PKL',
                        'desc' => 'Pemberitahuan pengajuan tempat magang baru oleh siswa ke panitia PKL.',
                    ],
                    'wa_send_pkl_supervisor_assigned' => [
                        'label' => 'Penugasan Guru Pembimbing Monitoring PKL',
                        'desc' => 'Pemberitahuan penugasan guru pembimbing untuk memonitoring siswa di instansi mitra.',
                    ],
                    'wa_send_pkl_journal_submission' => [
                        'label' => 'Laporan Mingguan & Jurnal Masuk Siswa PKL',
                        'desc' => 'Rekap pengumpulan jurnal kegiatan harian siswa di tempat kerja praktek.',
                    ],
                    'wa_send_pkl_grading_ready' => [
                        'label' => 'Pengisian Nilai Mentor & Penerbitan Sertifikat PKL',
                        'desc' => 'Notifikasi saat mentor industri telah menginput nilai dan sertifikat siap dicetak.',
                    ],
                ],
            ],

            'panitia_project' => [
                'target' => 'Ke Panitia Project Akhir',
                'description' => 'Otomatisasi pengajuan judul, pembimbingan, dan sidang Project Akhir Kejuruan',
                'icon' => 'fas fa-project-diagram',
                'color' => 'rose',
                'badge' => 'Project Akhir',
                'items' => [
                    'wa_send_final_project_submission' => [
                        'label' => 'Pengajuan Judul & Proposal Project Akhir',
                        'desc' => 'Pemberitahuan proposal karya akhir baru yang diajukan siswa untuk direview panitia.',
                    ],
                    'wa_send_final_project_mentor_assigned' => [
                        'label' => 'Penunjukan Guru Pembimbing Project Akhir',
                        'desc' => 'Pemberitahuan kepada guru yang ditunjuk sebagai pembimbing teknis karya project.',
                    ],
                    'wa_send_final_project_exam_schedule' => [
                        'label' => 'Jadwal Sidang & Uji Kelayakan Project',
                        'desc' => 'Pemberitahuan waktu, ruangan, dan dewan penguji sidang karya akhir.',
                    ],
                    'wa_send_final_project_approval' => [
                        'label' => 'Pengesahan Naskah & Nilai Kelulusan Project',
                        'desc' => 'Notifikasi saat karya project akhir dinyatakan lulus dan disahkan dewan penguji.',
                    ],
                ],
            ],

            'panitia_penelitian' => [
                'target' => 'Ke Panitia Penelitian Akhir',
                'description' => 'Otomatisasi pengajuan riset ilmiah, seminar hasil, dan publikasi penelitian',
                'icon' => 'fas fa-microscope',
                'color' => 'violet',
                'badge' => 'Penelitian Akhir',
                'items' => [
                    'wa_send_research_proposal_submitted' => [
                        'label' => 'Pengajuan Izin Riset & Naskah Penelitian',
                        'desc' => 'Pemberitahuan berkas proposal penelitian ilmiah yang masuk ke sekretariat riset.',
                    ],
                    'wa_send_research_instrument_reviewed' => [
                        'label' => 'Validasi Instrumen & Uji Kelayakan Penelitian',
                        'desc' => 'Pemberitahuan hasil review kuisioner/alat uji oleh dewan pakar penelitian.',
                    ],
                    'wa_send_research_seminar_schedule' => [
                        'label' => 'Jadwal Seminar Hasil & Sidang Riset',
                        'desc' => 'Pemberitahuan agenda seminar hasil penelitian kepada penguji dan peserta.',
                    ],
                    'wa_send_research_final_published' => [
                        'label' => 'Pengesahan Publikasi & Repositori Ilmiah',
                        'desc' => 'Notifikasi penyerahan laporan akhir penelitian ke perpustakaan/repositori.',
                    ],
                ],
            ],

            'yayasan' => [
                'target' => 'Ke Yayasan (Badan Pengurus)',
                'description' => 'Laporan eksekutif lintas 3 unit sekolah (SMA, SMP, SMK) dan undangan rapat pengurus',
                'icon' => 'fas fa-landmark',
                'color' => 'orange',
                'badge' => 'Yayasan Pembda',
                'items' => [
                    'wa_send_yayasan_monthly_digest' => [
                        'label' => 'Rekapitulasi Eksekutif Bulanan 3 Unit Sekolah',
                        'desc' => 'Rangkuman rekapitulasi data siswa, keuangan, dan guru dari ketiga unit sekolah.',
                    ],
                    'wa_send_yayasan_meeting_invitation' => [
                        'label' => 'Undangan Rapat Kerja & Sidang Pleno Yayasan',
                        'desc' => 'Undangan otomatis dan konfirmasi kehadiran rapat pengurus yayasan.',
                    ],
                    'wa_send_yayasan_budget_alert' => [
                        'label' => 'Pengajuan Anggaran & Pencairan Dana Unit',
                        'desc' => 'Notifikasi permohonan dana operasional/investasi dari kepala sekolah ke yayasan.',
                    ],
                    'wa_send_yayasan_psb_report' => [
                        'label' => 'Laporan Statistik Penerimaan Siswa Baru (PSB)',
                        'desc' => 'Update mingguan grafik pendaftar PSB di seluruh unit di bawah naungan yayasan.',
                    ],
                ],
            ],

            'semua' => [
                'target' => 'Semua / Siaran Massal',
                'description' => 'Pesan pengumuman umum dan kedaruratan kepada seluruh sivitas akademika',
                'icon' => 'fas fa-bullhorn',
                'color' => 'red',
                'badge' => 'Siaran Massal',
                'items' => [
                    'wa_send_broadcast_official_letter' => [
                        'label' => 'Siaran Surat Edaran Resmi Sekolah / Yayasan',
                        'desc' => 'Pengiriman surat edaran resmi terlampir PDF kepada seluruh guru, staf, dan wali murid.',
                    ],
                    'wa_send_broadcast_emergency_holiday' => [
                        'label' => 'Notifikasi Darurat Bencana & Libur Mendadak',
                        'desc' => 'Peringatan kilat bila terjadi kondisi cuaca ekstrem, force majeure, atau libur darurat.',
                    ],
                    'wa_send_broadcast_event_announcement' => [
                        'label' => 'Pengumuman Hari Besar Nasional & Upacara Sekolah',
                        'desc' => 'Informasi upacara bendera, perayaan hari besar nasional, dan agenda akbar yayasan.',
                    ],
                ],
            ],
        ];
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
        Setting::setValue('wa_enabled', true, 'boolean', 'whatsapp');

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
     * Update WhatsApp Provider Credentials (Token & API URL) from Web UI
     */
    public function updateWhatsappCredentials(Request $request)
    {
        $this->authorizeFeatureAccess();

        $request->validate([
            'provider' => 'required|string|in:fonnte,selfhosted',
            'api_token' => 'nullable|string',
            'api_url' => 'nullable|string',
        ]);

        $provider = $request->input('provider');

        if ($request->has('api_token')) {
            $token = trim((string)$request->input('api_token'));
            Setting::setValue("wa_{$provider}_token", $token, 'string', 'whatsapp');
        }

        if ($request->filled('api_url')) {
            $url = rtrim(trim((string)$request->input('api_url')), '/');
            Setting::setValue("wa_{$provider}_url", $url, 'string', 'whatsapp');
        }

        Setting::setValue('wa_enabled', true, 'boolean', 'whatsapp');

        return redirect()
            ->route('admin.settings.whatsapp')
            ->with('success', "Konfigurasi token & API URL untuk " . strtoupper($provider) . " berhasil disimpan!");
    }

    /**
     * Update WhatsApp Automation Settings
     */
    public function updateWhatsapp(Request $request)
    {
        $this->authorizeFeatureAccess();

        $groups = $this->getWhatsappAutomationGroups();
        $savedCount = 0;

        foreach ($groups as $group) {
            foreach ($group['items'] as $key => $item) {
                Setting::setValue($key, $request->boolean($key), 'boolean', 'features');
                $savedCount++;
            }
        }

        return redirect()
            ->route('admin.settings.whatsapp')
            ->with('success', "Pengaturan {$savedCount} saklar otomatisasi WhatsApp berhasil disimpan!");
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

        // Pastikan WhatsApp otomatis aktif di database
        Setting::setValue('wa_enabled', true, 'boolean', 'whatsapp');

        $service = new \App\Services\WhatsAppService();
        $result = $service->sendMessage($request->input('phone'), $request->input('message'));

        if (!empty($result['success'])) {
            return redirect()
                ->route('admin.settings.whatsapp')
                ->with('success', 'Pesan uji coba WhatsApp BERHASIL terkirim ke ' . $request->input('phone'));
        }

        $errorMsg = $result['error'] ?? $result['message'] ?? $result['response']['message'] ?? $result['response']['error'] ?? 'Gagal memproses pengiriman pesan ke gateway WhatsApp.';
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
            'executive.principal_daily_attendance' => [
                'title' => '🏫 Laporan Eksekutif Presensi Harian (Ke Kepala Sekolah)',
                'variables' => ['{sekolah}', '{nama_kepsek}', '{tanggal}', '{waktu_rekap}', '{total_siswa}', '{siswa_hadir}', '{siswa_terlambat}', '{siswa_sakit}', '{siswa_izin}', '{siswa_alpha}', '{guru_hadir}', '{guru_dinas}', '{guru_sakit}', '{guru_izin}', '{guru_alpha}', '{pegawai_hadir}', '{pegawai_cuti}', '{pegawai_sakit}', '{pegawai_izin}', '{pegawai_alpha}'],
            ],
            'executive.homeroom_daily_attendance' => [
                'title' => '👩‍🏫 Laporan Rekap Presensi Harian (Ke Wali Kelas)',
                'variables' => ['{kelas}', '{nama_wali_kelas}', '{tanggal}', '{waktu_rekap}', '{total_siswa}', '{hadir}', '{terlambat}', '{sakit}', '{izin}', '{alpha}', '{daftar_tidak_hadir}'],
            ],
            'executive.principal_monthly_spp' => [
                'title' => '💰 Laporan Realisasi Keuangan SPP Bulanan (Ke Kepala Sekolah)',
                'variables' => ['{periode_bulan}', '{total_lunas}', '{total_penunggak}', '{total_tunggakan}'],
            ],
            'executive.homeroom_monthly_spp' => [
                'title' => '💳 Laporan Tunggakan SPP Bulanan (Ke Wali Kelas)',
                'variables' => ['{kelas}', '{nama_wali_kelas}', '{periode_bulan}', '{jumlah_penunggak}', '{total_tunggakan}', '{daftar_penunggak}'],
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

    /**
     * Display Error Alerts & Service Monitoring Settings page
     */
    public function errorAlerts()
    {
        $this->authorizeFeatureAccess();

        $alertService = app(\App\Services\ErrorAlertService::class);
        $alertConfig = $alertService->getAlertConfig();
        $recentLogs = $alertService->getRecentErrorLogs(30);
        $waService = app(\App\Services\WhatsAppService::class);
        $healthChecks = \App\Services\ErrorDiagnosticService::runHealthVerification();
        $allPassed = !collect($healthChecks)->contains('passed', false);

        return view('admin.settings.error-alerts', compact('alertConfig', 'recentLogs', 'waService', 'healthChecks', 'allPassed'));
    }

    /**
     * Clear application log file
     */
    public function clearErrorLogs()
    {
        $this->authorizeFeatureAccess();

        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            @file_put_contents($logPath, '');
        }

        $dailyFiles = glob(storage_path('logs/laravel-*.log'));
        foreach ($dailyFiles as $df) {
            @file_put_contents($df, '');
        }

        return redirect()
            ->route('admin.settings.error_alerts')
            ->with('success', 'Riwayat log error lama berhasil dibersihkan! Sistem kini siap memantau aktivitas baru.');
    }

    /**
     * Update Error Alerts Settings
     */
    public function updateErrorAlerts(Request $request)
    {
        $this->authorizeFeatureAccess();

        Setting::setValue('error_alerts_enabled', $request->boolean('error_alerts_enabled'), 'boolean', 'alerts');
        Setting::setValue('error_alert_cooldown_minutes', (int) $request->input('error_alert_cooldown_minutes', 5), 'integer', 'alerts');

        // WhatsApp Channel (Eksklusif)
        Setting::setValue('wa_alert_enabled', $request->boolean('wa_alert_enabled'), 'boolean', 'alerts');
        if ($request->has('wa_alert_phone')) {
            Setting::setValue('wa_alert_phone', trim((string)$request->input('wa_alert_phone')), 'string', 'alerts');
        }

        // Disable Telegram Channel
        Setting::setValue('telegram_alert_enabled', false, 'boolean', 'alerts');

        return redirect()
            ->route('admin.settings.error_alerts')
            ->with('success', 'Konfigurasi Notifikasi Laporan Error WhatsApp Super Admin berhasil disimpan!');
    }

    /**
     * Test Sending Error Alert to WhatsApp Super Admin
     */
    public function testErrorAlertChannel(Request $request)
    {
        $this->authorizeFeatureAccess();

        $alertService = app(\App\Services\ErrorAlertService::class);
        $results = $alertService->sendTestAlert('whatsapp');

        $successMsgs = [];
        $errorMsgs = [];

        foreach ($results as $ch => $res) {
            if (!empty($res['success'])) {
                $successMsgs[] = $res['message'];
            } else {
                $errorMsgs[] = $res['message'] ?? "Gagal mengirim test alert ke {$ch}";
            }
        }

        if (!empty($errorMsgs) && empty($successMsgs)) {
            return redirect()
                ->route('admin.settings.error_alerts')
                ->with('error', implode(' | ', $errorMsgs));
        }

        $finalMsg = implode(' | ', $successMsgs);
        if (!empty($errorMsgs)) {
            $finalMsg .= ' (Catatan: ' . implode(' | ', $errorMsgs) . ')';
        }

        return redirect()
            ->route('admin.settings.error_alerts')
            ->with('success', $finalMsg ?: 'Pemeriksaan alert WhatsApp selesai.');
    }
}
