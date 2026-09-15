<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuditStorageIntegrityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:audit
                            {--category= : Filter berdasarkan kategori (photos, documents, lms, cbt, pkl, projects, forum, gallery, all)}
                            {--table= : Filter spesifik nama tabel (contoh: users, students, teachers)}
                            {--missing-only : Hanya tampilkan tabel yang memiliki berkas fisik hilang}
                            {--detailed : Tampilkan rincian ID record dan path berkas yang hilang}
                            {--download-missing : Otomatis unduh berkas yang hilang dari server production Hostinger}
                            {--import-dir= : Path direktori lokal/FTP (misal: /storage/data_ftp) untuk memulihkan berkas yang hilang secara otomatis}
                            {--fix-symlink : Otomatis buat ulang symbolic link public/storage jika rusak}
                            {--remote-url= : URL endpoint storage sync production}
                            {--remote-secret= : Token rahasia storage sync production}
                            {--export= : Path file CSV untuk mengekspor daftar berkas yang hilang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit kelengkapan berkas fisik pada disk penyimpanan lokal berdasarkan data di database';

    /**
     * Daftar pemetaan tabel, kolom berkas, dan kategorinya
     */
    protected array $auditRegistry = [
        'users' => [
            'table' => 'users',
            'columns' => ['photo'],
            'label' => 'Foto Profil Pengguna (Akun Login)',
            'category' => 'photos',
            'id_col' => 'id',
            'title_col' => 'name',
        ],
        'students' => [
            'table' => 'students',
            'columns' => ['photo'],
            'label' => 'Foto Profil Siswa',
            'category' => 'photos',
            'id_col' => 'id',
            'title_col' => 'full_name',
        ],
        'teachers' => [
            'table' => 'teachers',
            'columns' => ['photo'],
            'label' => 'Foto Profil Guru',
            'category' => 'photos',
            'id_col' => 'id',
            'title_col' => 'full_name',
        ],
        'employees' => [
            'table' => 'employees',
            'columns' => ['photo'],
            'label' => 'Foto Profil Pegawai / Tendik',
            'category' => 'photos',
            'id_col' => 'id',
            'title_col' => 'full_name',
        ],
        'alumni_directories' => [
            'table' => 'alumni_directories',
            'columns' => ['photo_path'],
            'label' => 'Foto Direktori Alumni',
            'category' => 'photos',
            'id_col' => 'id',
            'title_col' => 'full_name',
        ],
        'applicants' => [
            'table' => 'applicants',
            'columns' => ['photo_path'],
            'label' => 'Foto Pasfoto Calon Siswa (PPDB)',
            'category' => 'photos',
            'id_col' => 'id',
            'title_col' => 'full_name',
        ],
        'student_documents' => [
            'table' => 'student_documents',
            'columns' => ['file_path'],
            'label' => 'Dokumen Berkas Siswa (KK, Akta, dll)',
            'category' => 'documents',
            'id_col' => 'id',
            'title_col' => 'document_name',
        ],
        'applicant_documents' => [
            'table' => 'applicant_documents',
            'columns' => ['file_path'],
            'label' => 'Dokumen Berkas Pendaftaran PPDB',
            'category' => 'documents',
            'id_col' => 'id',
            'title_col' => 'document_type',
        ],
        'employee_documents' => [
            'table' => 'employee_documents',
            'columns' => ['file_path'],
            'label' => 'Dokumen Arsip Kepegawaian',
            'category' => 'documents',
            'id_col' => 'id',
            'title_col' => 'title',
        ],
        'employee_educations' => [
            'table' => 'employee_educations',
            'columns' => ['certificate_file'],
            'label' => 'Ijazah Pendidikan Pegawai',
            'category' => 'documents',
            'id_col' => 'id',
            'title_col' => 'institution_name',
        ],
        'employee_trainings' => [
            'table' => 'employee_trainings',
            'columns' => ['certificate_file'],
            'label' => 'Sertifikat Pelatihan / Diklat Pegawai',
            'category' => 'documents',
            'id_col' => 'id',
            'title_col' => 'training_name',
        ],
        'employee_leaves' => [
            'table' => 'employee_leaves',
            'columns' => ['attachment'],
            'label' => 'Lampiran Surat Cuti Pegawai',
            'category' => 'documents',
            'id_col' => 'id',
            'title_col' => 'reason',
        ],
        'attendances' => [
            'table' => 'attendances',
            'columns' => ['attachment'],
            'label' => 'Lampiran Bukti Surat Izin / Sakit',
            'category' => 'documents',
            'id_col' => 'id',
            'title_col' => 'notes',
        ],
        'lms_materials' => [
            'table' => 'lms_materials',
            'columns' => ['file_path'],
            'label' => 'Berkas Modul & Materi KBM LMS Guru',
            'category' => 'lms',
            'id_col' => 'id',
            'title_col' => 'title',
        ],
        'lms_submissions' => [
            'table' => 'lms_submissions',
            'columns' => ['file_path'],
            'label' => 'Berkas Tugas Kumpul Siswa (LMS)',
            'category' => 'lms',
            'id_col' => 'id',
            'title_col' => 'id',
        ],
        'cbt_questions' => [
            'table' => 'cbt_questions',
            'columns' => ['question_image', 'question_audio', 'option_a_image', 'option_b_image', 'option_c_image', 'option_d_image', 'option_e_image'],
            'label' => 'Media & Gambar Soal Ujian CBT',
            'category' => 'cbt',
            'id_col' => 'id',
            'title_col' => 'id',
        ],
        'pkl_logs' => [
            'table' => 'pkl_logs',
            'columns' => ['photo'],
            'label' => 'Foto Dokumentasi Logbook PKL Siswa',
            'category' => 'pkl',
            'id_col' => 'id',
            'title_col' => 'activity',
        ],
        'pkl_monitorings' => [
            'table' => 'pkl_monitorings',
            'columns' => ['photo_path'],
            'label' => 'Foto Kunjungan Monitoring PKL Guru',
            'category' => 'pkl',
            'id_col' => 'id',
            'title_col' => 'notes',
        ],
        'pkl_perangkats' => [
            'table' => 'pkl_perangkats',
            'columns' => ['file_path'],
            'label' => 'Dokumen Perangkat PKL',
            'category' => 'pkl',
            'id_col' => 'id',
            'title_col' => 'title',
        ],
        'final_projects' => [
            'table' => 'final_projects',
            'columns' => ['file_path', 'proposal_file_path', 'thumbnail_path'],
            'label' => 'Berkas & Poster Tugas Akhir / Penelitian',
            'category' => 'projects',
            'id_col' => 'id',
            'title_col' => 'title',
        ],
        'forum_threads' => [
            'table' => 'forum_threads',
            'columns' => ['image_path', 'attachment_path'],
            'label' => 'Gambar & Lampiran Forum Diskusi',
            'category' => 'forum',
            'id_col' => 'id',
            'title_col' => 'title',
        ],
        'forum_replies' => [
            'table' => 'forum_replies',
            'columns' => ['attachment_path', 'voice_note_path'],
            'label' => 'Lampiran & Voice Note Balasan Forum',
            'category' => 'forum',
            'id_col' => 'id',
            'title_col' => 'id',
        ],
        'galleries' => [
            'table' => 'galleries',
            'columns' => ['image'],
            'label' => 'Foto Galeri Kegiatan Sekolah',
            'category' => 'gallery',
            'id_col' => 'id',
            'title_col' => 'title',
        ],
        'schools' => [
            'table' => 'schools',
            'columns' => ['logo'],
            'label' => 'Logo Identitas Sekolah & Yayasan',
            'category' => 'general',
            'id_col' => 'id',
            'title_col' => 'name',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('');
        $this->info('╔════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║       AUDIT INTEGRITAS BERKAS FISIK VS DATABASE (PembdaHUB)                ║');
        $this->info('╚════════════════════════════════════════════════════════════════════════════╝');
        $this->line('');

        // 1. Audit Symlink
        $this->auditStorageSymlink();

        // 2. Persiapan Parameter
        $filterCategory = $this->option('category');
        $filterTable = $this->option('table');
        $missingOnly = $this->option('missing-only');
        $detailed = $this->option('detailed');
        $downloadMissing = $this->option('download-missing');
        $exportPath = $this->option('export');

        $remoteUrl = $this->option('remote-url') ?: config('app.storage_sync_url', 'https://perguruanpembda.com/storage-sync.php');
        $remoteSecret = $this->option('remote-secret') ?: config('app.storage_sync_secret', 'pembda2026storage');

        // Filter tabel jika diminta
        $targets = $this->auditRegistry;
        if ($filterCategory && $filterCategory !== 'all') {
            $targets = array_filter($targets, fn($item) => $item['category'] === $filterCategory);
        }
        if ($filterTable) {
            $targets = array_filter($targets, fn($item, $key) => $key === $filterTable || $item['table'] === $filterTable, ARRAY_FILTER_USE_BOTH);
        }

        if (empty($targets)) {
            $this->warn('Tidak ada tabel yang cocok dengan kriteria filter.');
            return 0;
        }

        $summaryRows = [];
        $allMissingFiles = [];
        $totalDbRecords = 0;
        $totalPhysicalExist = 0;
        $totalPhysicalMissing = 0;

        $this->info("Memindai data referensi berkas di database...\n");

        try {
            $existingTables = array_map(function($tbl) {
                return array_values((array)$tbl)[0];
            }, DB::select('SHOW TABLES'));
            $existingTablesLookup = array_flip($existingTables);
        } catch (\Exception $e) {
            $this->error("❌ Gagal terhubung ke database: " . $e->getMessage());
            $this->warn("Pastikan database server aktif dan konfigurasi di file .env sudah sesuai.");
            return 1;
        }

        foreach ($targets as $key => $spec) {
            $tableName = $spec['table'];

            // Cek apakah tabel ada di database
            if (!isset($existingTablesLookup[$tableName])) {
                continue;
            }

            // Validasi kolom yang ada di database
            $columnsInTable = array_flip(Schema::getColumnListing($tableName));
            $validCols = array_filter($spec['columns'], fn($col) => isset($columnsInTable[$col]));

            if (empty($validCols)) {
                continue;
            }

            $idCol = isset($columnsInTable[$spec['id_col']]) ? $spec['id_col'] : 'id';
            $titleCol = isset($columnsInTable[$spec['title_col']]) ? $spec['title_col'] : $idCol;

            $tableTotal = 0;
            $tableExist = 0;
            $tableMissing = 0;

            $hasCreatedAt = isset($columnsInTable['created_at']);
            $hasUpdatedAt = isset($columnsInTable['updated_at']);

            $selectCols = [$idCol, $titleCol];
            if ($hasCreatedAt) $selectCols[] = 'created_at';
            elseif ($hasUpdatedAt) $selectCols[] = 'updated_at';

            foreach ($validCols as $col) {
                $colsToFetch = array_unique(array_merge($selectCols, [$col]));
                $query = DB::table($tableName)
                    ->whereNotNull($col)
                    ->where($col, '!=', '')
                    ->select($colsToFetch);

                $records = $query->get();

                foreach ($records as $rec) {
                    $rawPath = trim((string) $rec->{$col});
                    if (empty($rawPath)) continue;

                    // Lewati URL eksternal http:// atau https://
                    if (Str::startsWith($rawPath, ['http://', 'https://'])) {
                        continue;
                    }

                    $tableTotal++;
                    $cleanPath = ltrim(preg_replace('#^/?storage/#', '', $rawPath), '/');

                    $exists = $this->checkFilePhysicalExists($cleanPath);

                    if ($exists) {
                        $tableExist++;
                    } else {
                        $tableMissing++;
                        $recDate = null;
                        if ($hasCreatedAt && !empty($rec->created_at)) {
                            $recDate = (string) $rec->created_at;
                        } elseif ($hasUpdatedAt && !empty($rec->updated_at)) {
                            $recDate = (string) $rec->updated_at;
                        }

                        $allMissingFiles[] = [
                            'table' => $tableName,
                            'column' => $col,
                            'id' => $rec->{$idCol},
                            'title' => $rec->{$titleCol} ?? '-',
                            'raw_path' => $rawPath,
                            'clean_path' => $cleanPath,
                            'category' => $spec['category'],
                            'created_at' => $recDate,
                        ];
                    }
                }
            }

            if ($tableTotal === 0 && $missingOnly) {
                continue;
            }

            if ($missingOnly && $tableMissing === 0) {
                continue;
            }

            $pct = $tableTotal > 0 ? round(($tableExist / $tableTotal) * 100, 1) : 100;
            $status = $tableMissing === 0 ? '<info>100% LENGKAP</info>' : "<fg=red>{$pct}% ({$tableMissing} Hilang)</fg=red>";

            $summaryRows[] = [
                $spec['label'],
                $tableName,
                $tableTotal,
                $tableExist,
                $tableMissing,
                $status,
            ];

            $totalDbRecords += $tableTotal;
            $totalPhysicalExist += $tableExist;
            $totalPhysicalMissing += $tableMissing;
        }

        // Tampilkan Tabel Rekapitulasi
        $this->table(
            ['Komponen / Modul', 'Tabel DB', 'Total di DB', 'Fisik Ada', 'Fisik Hilang', 'Status'],
            $summaryRows
        );

        $this->line('');
        $overallPct = $totalDbRecords > 0 ? round(($totalPhysicalExist / $totalDbRecords) * 100, 1) : 100;

        $this->info("--------------------------------------------------------------------------------");
        $this->info(sprintf(
            "TOTAL KESELURUHAN : %d berkas di DB | %d berkas ADA | %d berkas HILANG (%s%%)",
            $totalDbRecords,
            $totalPhysicalExist,
            $totalPhysicalMissing,
            $overallPct
        ));
        $this->info("--------------------------------------------------------------------------------\n");

        // 3. Analisis Usia Berkas Hilang
        if (!empty($allMissingFiles)) {
            $now = now();
            $olderThan30Days = 0;
            $newerThan30Days = 0;
            $noDateCount = 0;
            $byPeriod = [];

            foreach ($allMissingFiles as $m) {
                if (!empty($m['created_at'])) {
                    try {
                        $cDate = \Carbon\Carbon::parse($m['created_at']);
                        $diffDays = $cDate->diffInDays($now);
                        if ($diffDays > 30) {
                            $olderThan30Days++;
                        } else {
                            $newerThan30Days++;
                        }
                        $period = $cDate->format('Y-m');
                        $byPeriod[$period] = ($byPeriod[$period] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $noDateCount++;
                    }
                } else {
                    $noDateCount++;
                }
            }
            ksort($byPeriod);

            $this->info("════════════════════════════════════════════════════════════════════════════");
            $this->info("  ANALISIS USIA BERKAS HILANG (Berdasarkan Waktu Dibuat / created_at)       ");
            $this->info("════════════════════════════════════════════════════════════════════════════");
            $pctOld = count($allMissingFiles) > 0 ? round(($olderThan30Days / count($allMissingFiles)) * 100, 1) : 0;
            $pctNew = count($allMissingFiles) > 0 ? round(($newerThan30Days / count($allMissingFiles)) * 100, 1) : 0;

            $this->line(sprintf("  • Berkas Lama (> 30 Hari yang lalu) : <fg=green;options=bold>%d berkas (%s%%)</>", $olderThan30Days, $pctOld));
            $this->line(sprintf("  • Berkas Baru (< 30 Hari terakhir)  : <fg=%s;options=bold>%d berkas (%s%%)</>", $newerThan30Days > 0 ? 'yellow' : 'green', $newerThan30Days, $pctNew));
            if ($noDateCount > 0) {
                $this->line(sprintf("  • Tanpa Metadata Tanggal             : %d berkas", $noDateCount));
            }
            $this->line('');

            if (!empty($byPeriod)) {
                $periodRows = [];
                foreach ($byPeriod as $p => $c) {
                    $periodRows[] = [$p, $c . ' berkas'];
                }
                $this->table(['Periode Bulan Dibuat', 'Jumlah Berkas Hilang'], $periodRows);
                $this->line('');
            }
        }

        // 4. Tampilkan Detail Berkas Hilang jika diminta
        if ($detailed && !empty($allMissingFiles)) {
            $this->warn("DAFTAR BERKAS FISIK YANG HILANG DI DISK:");
            $detailRows = [];
            foreach (array_slice($allMissingFiles, 0, 100) as $m) {
                $cDateStr = $m['created_at'] ? substr($m['created_at'], 0, 10) : '-';
                $detailRows[] = [
                    $m['table'] . '.' . $m['column'],
                    $m['id'],
                    Str::limit($m['title'], 22),
                    $cDateStr,
                    $m['clean_path'],
                ];
            }
            $this->table(['Lokasi Kolom', 'ID', 'Nama / Judul', 'Tgl Dibuat', 'Path Berkas'], $detailRows);

            if (count($allMissingFiles) > 100) {
                $rem = count($allMissingFiles) - 100;
                $this->line("<comment>... dan {$rem} berkas lainnya (gunakan opsi --export=missing.csv untuk melihat semua).</comment>");
            }
            $this->line('');
        }

        // 5. Ekspor ke CSV jika opsi --export diisi
        if ($exportPath && !empty($allMissingFiles)) {
            $this->exportToCsv($exportPath, $allMissingFiles);
        }

        $importDir = $this->option('import-dir');

        // 5. Fitur Pemulihan Berkas dari Direktori Lokal / Backup FTP jika diminta
        if ($importDir && !empty($allMissingFiles)) {
            $this->importFromLocalDirectory($allMissingFiles, $importDir);
        } elseif ($downloadMissing && !empty($allMissingFiles)) {
            $this->downloadMissingFiles($allMissingFiles, $remoteUrl, $remoteSecret);
        } elseif (!empty($allMissingFiles)) {
            $this->line("<fg=yellow>💡 Rekomendasi Pemulihan:</fg=yellow>");
            $this->line("1. Jika memiliki folder backup FTP di server lokal, jalankan:");
            $this->line("   <info>php artisan storage:audit --import-dir=/storage/data_ftp</info>");
            $this->line("2. Untuk menarik file yang hilang dari Hostinger Production via internet:");
            $this->line("   <info>php artisan storage:audit --download-missing</info>");
            $this->line("3. Atau untuk menarik seluruh folder foto & dokumen sekaligus via sync tool:");
            $this->line("   <info>php sync-storage.php --pull --missing-only</info>\n");
        } else {
            $this->info("✅ SELURUH BERKAS FISIK TELAH SELARAS 100%! Tidak ada link atau foto yang kosong.\n");
        }

        return 0;
    }

    /**
     * Periksa apakah berkas fisik benar-benar ada di disk lokal
     */
    protected function checkFilePhysicalExists(string $cleanPath): bool
    {
        // 1. Cek di disk public (storage/app/public/...)
        if (Storage::disk('public')->exists($cleanPath)) {
            return true;
        }

        // 2. Cek di disk local default (storage/app/...)
        if (Storage::disk('local')->exists($cleanPath)) {
            return true;
        }

        // 3. Cek di direktori public web
        if (file_exists(public_path($cleanPath)) && is_file(public_path($cleanPath))) {
            return true;
        }

        // 4. Cek jika file tersimpan di public/storage/...
        if (file_exists(public_path('storage/' . $cleanPath)) && is_file(public_path('storage/' . $cleanPath))) {
            return true;
        }

        return false;
    }

    /**
     * Periksa dan tawarkan perbaikan symlink public/storage
     */
    protected function auditStorageSymlink(): void
    {
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        $this->line("<comment>[1/2] Memeriksa Konfigurasi Symbolic Link Server...</comment>");

        $isLink = is_link($linkPath);
        $exists = file_exists($linkPath);

        if ($isLink) {
            $actualTarget = readlink($linkPath);
            $normalizedTarget = str_replace('\\', '/', realpath($actualTarget) ?: $actualTarget);
            $expectedTarget = str_replace('\\', '/', realpath($targetPath) ?: $targetPath);

            if ($normalizedTarget === $expectedTarget || file_exists($linkPath)) {
                $this->line("  ✓ Symlink public/storage aktif dan terhubung ke storage/app/public\n");
                return;
            } else {
                $this->warn("  ⚠ Symlink public/storage mengarah ke target yang rusak atau tidak valid: {$actualTarget}");
            }
        } elseif ($exists && is_dir($linkPath)) {
            $this->warn("  ⚠ Folder public/storage berupa direktori fisik biasa (bukan symlink).");
        } else {
            $this->warn("  ⚠ Symbolic link public/storage belum dibuat!");
        }

        if ($this->option('fix-symlink')) {
            $this->fixStorageSymlink($linkPath, $targetPath);
        } else {
            $this->line("  <fg=yellow>Jalankan 'php artisan storage:link' atau tambahkan flag '--fix-symlink' jika gambar 404.</fg=yellow>\n");
        }
    }

    /**
     * Memperbaiki Symbolic Link
     */
    protected function fixStorageSymlink(string $linkPath, string $targetPath): void
    {
        $this->info("  Membuat ulang symbolic link storage...");

        if (is_link($linkPath)) {
            @unlink($linkPath);
        } elseif (is_dir($linkPath)) {
            $backup = $linkPath . '_bak_' . time();
            @rename($linkPath, $backup);
            $this->line("  Folder lama di-backup ke: {$backup}");
        }

        try {
            $this->call('storage:link');
            $this->info("  ✓ Berhasil membuat link storage.\n");
        } catch (\Exception $e) {
            $this->error("  ✗ Gagal membuat symlink: " . $e->getMessage() . "\n");
        }
    }

    /**
     * Unduh berkas-berkas yang hilang dari remote Hostinger production
     */
    protected function downloadMissingFiles(array $missingFiles, string $remoteUrl, string $remoteSecret): void
    {
        $uniquePaths = array_unique(array_column($missingFiles, 'clean_path'));
        $total = count($uniquePaths);

        $this->info("═════════════════════════════════════════════════════════════════");
        $this->info("  MEMULIHKAN {$total} BERKAS HILANG DARI PRODUCTION SERVER     ");
        $this->info("═════════════════════════════════════════════════════════════════");
        $this->line("Remote: {$remoteUrl}");

        $successCount = 0;
        $failCount = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($uniquePaths as $path) {
            $downloadUrl = rtrim($remoteUrl, '/') . '?secret=' . urlencode($remoteSecret) . '&action=download_file&file=' . urlencode($path);

            $ch = curl_init($downloadUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PembdaHUB-StorageAuditor/1.0');

            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($data)) {
                Storage::disk('public')->put($path, $data);
                $successCount++;
            } else {
                $failCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line("\n");

        $this->info("Selesai! Berhasil mengunduh: <fg=green>{$successCount}</fg=green> berkas.");
        if ($failCount > 0) {
            $this->warn("Sebanyak {$failCount} berkas tidak ditemukan di server remote (kemungkinan memang berkas lama yang sudah dihapus).");
        }
        $this->line('');
    }

    /**
     * Pulihkan berkas yang hilang dari direktori cadangan lokal / FTP dump
     */
    protected function importFromLocalDirectory(array $missingFiles, string $importDir): void
    {
        $candidates = [
            $importDir,
            base_path($importDir),
            storage_path($importDir),
            '/storage/data_ftp',
            '/var/www/pembdahub/storage/data_ftp',
            rtrim($importDir, '/'),
        ];

        $resolvedDir = null;
        foreach ($candidates as $c) {
            if (!empty($c) && is_dir($c)) {
                $resolvedDir = realpath($c);
                break;
            }
        }

        if (!$resolvedDir) {
            $this->error("Direktori impor '{$importDir}' tidak ditemukan di server!");
            return;
        }

        $this->info("═════════════════════════════════════════════════════════════════");
        $this->info("  MEMULIHKAN BERKAS DARI DIREKTORI LOKAL / FTP DUMP             ");
        $this->info("═════════════════════════════════════════════════════════════════");
        $this->line("Sumber Direktori: <comment>{$resolvedDir}</comment>");

        $uniquePaths = array_unique(array_column($missingFiles, 'clean_path'));
        $total = count($uniquePaths);
        $restoredCount = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($uniquePaths as $cleanPath) {
            $searchLocations = [
                $resolvedDir . '/' . $cleanPath,
                $resolvedDir . '/storage/' . $cleanPath,
                $resolvedDir . '/public/storage/' . $cleanPath,
                $resolvedDir . '/app/public/' . $cleanPath,
                $resolvedDir . '/storage/app/public/' . $cleanPath,
                $resolvedDir . '/' . basename($cleanPath),
            ];

            $foundSource = null;
            foreach ($searchLocations as $src) {
                if (is_file($src)) {
                    $foundSource = $src;
                    break;
                }
            }

            if ($foundSource) {
                $destPath = Storage::disk('public')->path($cleanPath);
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    @mkdir($destDir, 0775, true);
                }
                if (@copy($foundSource, $destPath)) {
                    @chmod($destPath, 0664);
                    $restoredCount++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line("\n");

        $this->info("✓ Berhasil memulihkan: <fg=green>{$restoredCount}</fg=green> dari {$total} berkas hilang.");
        if ($restoredCount > 0) {
            $this->line("<fg=yellow>💡 Jalankan kembali 'php artisan storage:audit' untuk melihat hasil terbaru.</fg=yellow>\n");
        }
    }

    /**
     * Ekspor daftar berkas hilang ke format CSV
     */
    protected function exportToCsv(string $path, array $missingFiles): void
    {
        $fp = fopen($path, 'w');
        fputcsv($fp, ['Tabel', 'Kolom', 'ID Record', 'Nama/Judul', 'Tanggal Dibuat', 'Path Berkas Asli', 'Path Normalisasi', 'Kategori']);

        foreach ($missingFiles as $row) {
            fputcsv($fp, [
                $row['table'],
                $row['column'],
                $row['id'],
                $row['title'],
                $row['created_at'] ?? '-',
                $row['raw_path'],
                $row['clean_path'],
                $row['category'],
            ]);
        }

        fclose($fp);
        $this->info("✓ Daftar berkas hilang berhasil diekspor ke CSV: <comment>{$path}</comment>\n");
    }
}
