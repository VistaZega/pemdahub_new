<?php

namespace App\Console\Commands;

use App\Services\ExecutiveReportService;
use Illuminate\Console\Command;

class SendWaExecutiveDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:digest 
        {type=attendance-daily : Type of digest (attendance-daily, spp-monthly, lms-weekly, award-sample, edaran-sample)}
        {--delay-min= : Jeda minimum antar pesan dalam detik}
        {--delay-max= : Jeda maksimum antar pesan dalam detik}
        {--batch-pause= : Jeda istirahat antar unit sekolah dalam detik}
        {--school= : Filter hanya ID sekolah tertentu}
        {--force : Paksa kirim meskipun hari ini sudah terkirim}
        {--dry-run : Simulasi perhitungan tanpa mengirim pesan riil ke WhatsApp}
        {--test-phone= : Kirim 1 contoh sampel hanya ke nomor ini (Uji Coba Aman)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated WhatsApp Executive Digests to Principal and Homeroom Teachers with anti-ban pacing';

    /**
     * Execute the console command.
     */
    public function handle(ExecutiveReportService $reportService): int
    {
        $type = $this->argument('type');
        $dryRun = (bool)$this->option('dry-run');

        $this->info("🚀 Menjalankan WhatsApp Executive Digest: [{$type}]" . ($dryRun ? " [MODE SIMULASI / DRY-RUN]" : "") . "...");

        $options = [
            'dry_run' => $dryRun,
            'force' => (bool)$this->option('force'),
            'school_id' => $this->option('school') ? (int)$this->option('school') : null,
            'target_phone' => $this->option('test-phone') ?: null,
            'delay_min' => $this->option('delay-min') !== null ? (int)$this->option('delay-min') : null,
            'delay_max' => $this->option('delay-max') !== null ? (int)$this->option('delay-max') : null,
            'batch_pause' => $this->option('batch-pause') !== null ? (int)$this->option('batch-pause') : null,
            'logger' => function ($msg) {
                $time = date('H:i:s');
                $this->line(" [{$time}] {$msg}");
            },
        ];

        switch ($type) {
            case 'attendance-daily':
                if ($options['target_phone']) {
                    $this->warn("🧪 Mode Uji Coba: Mengirim 1 contoh rekap ke nomor {$options['target_phone']}...");
                    $res1 = $reportService->sendPrincipalDailyAttendanceDigest($options);
                    $res2 = $reportService->sendHomeroomDailyAttendanceDigest($options);
                    $this->info("🏁 Selesai Uji Coba: Kepsek=" . ($res1['sent'] ? 'OK' : 'Skip') . ", Wali Kelas=" . ($res2['sent'] ? 'OK' : 'Skip'));
                    break;
                }

                $workflowRes = $reportService->sendDailyAttendanceDigestWorkflow($options);
                $this->info("🏁 " . ($workflowRes['message'] ?? 'Workflow Selesai'));
                break;

            case 'spp-monthly':
                $res1 = $reportService->sendPrincipalMonthlySppDigest();
                $res2 = $reportService->sendHomeroomMonthlySppDigest();
                $this->info("✅ " . ($res1['message'] ?? 'Done Kepsek') . " | " . ($res2['message'] ?? 'Done Wali Kelas'));
                break;

            case 'lms-weekly':
                $res1 = $reportService->sendPrincipalWeeklyLmsDigest();
                $res2 = $reportService->sendHomeroomWeeklyLmsDigest();
                $this->info("✅ " . ($res1['message'] ?? 'Done Kepsek') . " | " . ($res2['message'] ?? 'Done Wali Kelas'));
                break;

            case 'points-weekly':
                $res = $reportService->sendWeeklyStudentPointsDigest();
                $this->info("✅ " . ($res['message'] ?? 'Done Rekap Poin'));
                break;

            case 'award-sample':
                $res = $reportService->notifyStudentAward(
                    'Ahmad Fajar',
                    'XI IPA 1',
                    'Juara 1 LKS Informatika SMK 2026',
                    50,
                    'Meraih Juara 1 Tingkat Provinsi'
                );
                $this->info("✅ " . ($res['message'] ?? 'Done Award'));
                break;

            case 'edaran-sample':
                $res = $reportService->notifySuratEdaran(
                    'Surat Edaran Libur Hari Raya & Penetapan Seragam Baru 2026',
                    'https://perguruanpembda.com/download/surat-edaran-2026.pdf'
                );
                $this->info("✅ " . ($res['message'] ?? 'Done Edaran'));
                break;

            default:
                $this->error("Unknown digest type: [{$type}]. Available: attendance-daily, spp-monthly, lms-weekly, award-sample, edaran-sample");
                return 1;
        }

        return 0;
    }
}
