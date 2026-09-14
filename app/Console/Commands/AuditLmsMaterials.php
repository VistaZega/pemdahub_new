<?php

namespace App\Console\Commands;

use App\Models\LmsMaterial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditLmsMaterials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:audit-materials 
                            {--material= : Audit spesifik satu ID materi}
                            {--course= : Audit materi pada satu ID course tertentu}
                            {--missing-only : Hanya tampilkan materi yang berkas fisiknya hilang}
                            {--export= : Path file CSV untuk ekspor daftar materi hilang (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit kelengkapan berkas fisik materi LMS di storage server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('  AUDIT INTEGRITAS BERKAS FISIK MATERI LMS (PembdaHUB)        ');
        $this->info('═══════════════════════════════════════════════════════════════');

        $singleId = $this->option('material');
        if ($singleId) {
            return $this->auditSingleMaterial($singleId);
        }

        $query = LmsMaterial::whereNotNull('file_path')
            ->with(['course.teacher.user', 'course.school', 'module']);

        $courseId = $this->option('course');
        if ($courseId) {
            $query->where('course_id', $courseId);
            $this->line("Memfilter materi untuk Course ID: <comment>{$courseId}</comment>");
        }

        $materials = $query->get();
        $total = $materials->count();

        if ($total === 0) {
            $this->warn('Tidak ada data materi LMS dengan berkas lampiran (file_path) yang ditemukan.');
            return 0;
        }

        $this->line("Memeriksa fisik <comment>{$total}</comment> berkas materi di storage disk...");

        $existCount = 0;
        $missingCount = 0;
        $missingList = [];
        $byTeacher = [];
        $bySchool = [];
        $byMonth = [];

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($materials as $m) {
            $hasPhysical = $m->fileExists();

            if ($hasPhysical) {
                $existCount++;
            } else {
                $missingCount++;

                $teacherName = $m->course?->teacher?->user?->name ?? 'Tidak Diketahui';
                $schoolName = $m->course?->school?->name ?? 'Tanpa Sekolah';
                $month = $m->created_at ? $m->created_at->format('Y-m') : 'unknown';

                $byTeacher[$teacherName] = ($byTeacher[$teacherName] ?? 0) + 1;
                $bySchool[$schoolName] = ($bySchool[$schoolName] ?? 0) + 1;
                $byMonth[$month] = ($byMonth[$month] ?? 0) + 1;

                $missingList[] = [
                    'id' => $m->id,
                    'title' => $m->title,
                    'type' => $m->material_type,
                    'school' => $schoolName,
                    'teacher' => $teacherName,
                    'course' => $m->course?->title ?? '-',
                    'module' => $m->module?->title ?? '-',
                    'file_path' => $m->file_path,
                    'file_size' => $m->file_size ?? 0,
                    'file_size_human' => $this->formatBytes($m->file_size ?? 0),
                    'created_at' => $m->created_at?->format('Y-m-d H:i:s') ?? '-',
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Ringkasan Utama
        $missingPercent = $total > 0 ? round(($missingCount / $total) * 100, 1) : 0;
        $existPercent = $total > 0 ? round(($existCount / $total) * 100, 1) : 0;

        $this->table(
            ['Metrik Audit', 'Jumlah', 'Persentase'],
            [
                ['Total Materi dengan Berkas (file_path)', $total, '100%'],
                ['Berkas Fisik Ada di Disk Server', $existCount, "{$existPercent}%"],
                ['Berkas Fisik HILANG / Tidak Ada', $missingCount, "{$missingPercent}%"],
            ]
        );

        // Rekap per Unit Sekolah
        if (!empty($bySchool)) {
            arsort($bySchool);
            $this->newLine();
            $this->info('─── Rekap Berkas Hilang Berdasarkan Unit Sekolah ───');
            $schoolRows = [];
            foreach ($bySchool as $s => $cnt) {
                $schoolRows[] = [$s, $cnt . ' materi'];
            }
            $this->table(['Unit Sekolah', 'Jumlah Materi Hilang'], $schoolRows);
        }

        // Rekap per Bulan Pembuatan
        if (!empty($byMonth)) {
            ksort($byMonth);
            $this->newLine();
            $this->info('─── Rekap Berkas Hilang Berdasarkan Periode Pembuatan ───');
            $monthRows = [];
            foreach ($byMonth as $mo => $cnt) {
                $monthRows[] = [$mo, $cnt . ' materi'];
            }
            $this->table(['Periode (Bulan)', 'Jumlah Materi Hilang'], $monthRows);
        }

        // Top Guru dengan Materi Hilang Terbanyak
        if (!empty($byTeacher)) {
            arsort($byTeacher);
            $this->newLine();
            $this->info('─── Top 10 Guru dengan Materi Hilang Terbanyak ───');
            $teacherRows = [];
            $rank = 1;
            foreach (array_slice($byTeacher, 0, 10, true) as $t => $cnt) {
                $teacherRows[] = [$rank++, $t, $cnt . ' berkas'];
            }
            $this->table(['#', 'Nama Guru', 'Jumlah Berkas Belum Ada'], $teacherRows);
        }

        // Ekspor ke CSV jika opsi diminta
        $exportPath = $this->option('export');
        if ($exportPath && !empty($missingList)) {
            $this->exportToCsv($missingList, $exportPath);
        }

        $this->newLine();
        $this->info('💡 Catatan Solusi:');
        $this->line('  1. Guru dapat mengunggah ulang berkas asli langsung melalui portal LMS Guru');
        $this->line('     (sudah tersedia tombol peringatan dan unggah ulang 1-klik di setiap materi).');
        $this->line('  2. Untuk melihat detail satu materi, jalankan: <comment>php artisan lms:audit-materials --material=ID</comment>');
        $this->line('  3. Untuk mengekspor daftar lengkap ke CSV, jalankan: <comment>php artisan lms:audit-materials --export=materi_hilang.csv</comment>');

        return 0;
    }

    /**
     * Audit spesifik satu materi
     */
    protected function auditSingleMaterial($id)
    {
        $m = LmsMaterial::with(['course.teacher.user', 'course.school', 'module'])->find($id);

        if (!$m) {
            $this->error("Materi LMS dengan ID {$id} tidak ditemukan di database.");
            return 1;
        }

        $hasPhysical = $m->fileExists();
        $cleanPath = ltrim(str_replace('storage/', '', $m->file_path ?? ''), '/');
        $diskPath = $cleanPath ? Storage::disk('public')->path($cleanPath) : '-';

        $this->newLine();
        $this->info("=== DETAIL AUDIT MATERI ID: {$m->id} ===");
        $this->table(
            ['Atribut', 'Nilai'],
            [
                ['ID Materi', $m->id],
                ['Judul Materi', $m->title],
                ['Tipe Materi', $m->getContentTypeLabel()],
                ['Unit Sekolah', $m->course?->school?->name ?? '-'],
                ['Guru Pengampu', $m->course?->teacher?->user?->name ?? '-'],
                ['Mata Pelajaran (Course)', $m->course?->title ?? '-'],
                ['Modul', $m->module ? "Modul {$m->module->sequence}: {$m->module->title}" : '-'],
                ['File Path DB', $m->file_path ?? '-'],
                ['Ukuran Tercatat di DB', $this->formatBytes($m->file_size ?? 0) . " ({$m->file_size} bytes)"],
                ['Status Berkas Fisik', $hasPhysical ? '<info>✔ ADA DI DISK SERVER</info>' : '<fg=red;options=bold>✖ TIDAK DITEMUKAN DI DISK</>'],
                ['Path Lengkap Disk', $diskPath],
                ['Tanggal Dibuat', $m->created_at?->format('d F Y, H:i:s') ?? '-'],
                ['Terakhir Diperbarui', $m->updated_at?->format('d F Y, H:i:s') ?? '-'],
            ]
        );

        if (!$hasPhysical) {
            $this->newLine();
            $this->warn('⚠️ Tindakan yang disarankan:');
            $this->line("  Minta guru pengampu (<comment>" . ($m->course?->teacher?->user?->name ?? 'Guru') . "</comment>) untuk membuka LMS Guru");
            $this->line("  pada course '<comment>" . ($m->course?->title ?? '') . "</comment>' lalu klik tombol '<comment>Unggah Ulang Berkas</comment>' pada materi ini.");
        }

        return 0;
    }

    /**
     * Ekspor daftar materi hilang ke file CSV
     */
    protected function exportToCsv(array $data, string $path)
    {
        $fp = @fopen($path, 'w');
        if (!$fp) {
            $this->error("Gagal membuka file tujuan ekspor: {$path}");
            return;
        }

        // BOM untuk support Excel UTF-8
        fputs($fp, "\xEF\xBB\xBF");

        // Header CSV
        fputcsv($fp, [
            'ID',
            'Judul Materi',
            'Tipe',
            'Unit Sekolah',
            'Nama Guru Pengampu',
            'Mata Pelajaran',
            'Modul',
            'File Path DB',
            'Ukuran (Bytes)',
            'Ukuran (Human)',
            'Tanggal Dibuat',
        ]);

        foreach ($data as $row) {
            fputcsv($fp, [
                $row['id'],
                $row['title'],
                $row['type'],
                $row['school'],
                $row['teacher'],
                $row['course'],
                $row['module'],
                $row['file_path'],
                $row['file_size'],
                $row['file_size_human'],
                $row['created_at'],
            ]);
        }

        fclose($fp);
        $this->newLine();
        $this->info("✔ Berhasil mengekspor " . count($data) . " daftar materi hilang ke: <comment>{$path}</comment>");
    }

    /**
     * Helper format ukuran bytes
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
