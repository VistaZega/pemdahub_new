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
    protected $signature = 'wa:digest {type=attendance-daily : Type of digest (attendance-daily, spp-monthly, lms-weekly, award-sample, edaran-sample)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated WhatsApp Executive Digests to Principal and Homeroom Teachers';

    /**
     * Execute the console command.
     */
    public function handle(ExecutiveReportService $reportService): int
    {
        $type = $this->argument('type');

        $this->info("🚀 Executing WhatsApp Executive Digest: [{$type}]...");

        switch ($type) {
            case 'attendance-daily':
                $res1 = $reportService->sendPrincipalDailyAttendanceDigest();
                $res2 = $reportService->sendHomeroomDailyAttendanceDigest();
                $this->info("✅ " . ($res1['message'] ?? 'Done Kepsek') . " | " . ($res2['message'] ?? 'Done Wali Kelas'));
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
