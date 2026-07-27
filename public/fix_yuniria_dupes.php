<?php
/**
 * Standalone Tool: Merge Duplicate Employee Records for Yuniria Telaumbanua
 * URL Access: https://perguruanpembda.com/fix_yuniria_dupes.php?secret=pembda99
 */

if (!isset($_GET['secret']) || $_GET['secret'] !== 'pembda99') {
    die("Akses Ditolak. Token rahasia salah/tidak ditemukan.");
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Position;
use App\Models\EmployeePosition;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\EmployeeWorkloadSummary;
use App\Services\EmployeeAssignmentService;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔧 SINKRONISASI & PERBAIKAN DUPLIKAT YUNIRIA TELAUMBANUA</h2>";
echo "<pre style='background:#f4f4f5; padding:15px; border-radius:8px; line-height:1.5;'>";

try {
    DB::beginTransaction();

    $activeYear = AcademicYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    if (!$activeYear || !$activeSem) {
        throw new \Exception("Tahun Ajaran atau Semester aktif tidak ditemukan.");
    }

    echo "1. Tahun Ajaran Aktif: {$activeYear->year} ({$activeSem->semester_name})\n";

    // Cari semua pegawai dengan nama Yuniria Telaumbanua
    $allYuniria = Employee::where('full_name', 'like', '%Yuniria%')->get();

    if ($allYuniria->isEmpty()) {
        throw new \Exception("Pegawai Yuniria Telaumbanua tidak ditemukan di database.");
    }

    echo "Found " . $allYuniria->count() . " record(s) for Yuniria Telaumbanua.\n";

    // Pilih Pegawai Utama: prioritaskan yang di unit Yayasan (school_id = 4) atau dengan kode PTY, jika tidak ada pakai record pertama
    $primaryEmp = $allYuniria->firstWhere('school_id', 4) 
        ?? $allYuniria->firstWhere('employee_code', 'PTY-002') 
        ?? $allYuniria->first();

    echo "2. Pegawai Utama Ditentukan: ID {$primaryEmp->id} | Kode: {$primaryEmp->employee_code} | Nama: {$primaryEmp->full_name}\n";

    // Pindahkan pegawai utama ke Unit Yayasan (school_id = 4) dan pastikan basic_salary = 0
    $primaryEmp->school_id = 4; // Unit Yayasan
    $primaryEmp->basic_salary = 0;
    $primaryEmp->is_active = true;
    $primaryEmp->save();

    // 3. Non-aktifkan semua record duplikat Yuniria lainnya
    $dupes = $allYuniria->where('id', '!=', $primaryEmp->id);
    if ($dupes->isNotEmpty()) {
        foreach ($dupes as $dupe) {
            echo "3. Menonaktifkan record duplikat ID {$dupe->id} | Kode: {$dupe->employee_code} | School ID: {$dupe->school_id}...\n";
            $dupe->is_active = false;
            $dupe->save();

            EmployeeWorkloadSummary::where('employee_id', $dupe->id)->delete();
        }
        echo "   -> Summary beban kerja pegawai duplikat berhasil dibersihkan.\n";
    } else {
        echo "3. Tidak ada record duplikat lain yang perlu dibersihkan.\n";
    }

    // 4. Cari / Pasang Jabatan Bendahara Yayasan & Bendahara SMA ke Pegawai Utama
    $posYayasan = Position::where('position_name', 'like', '%Bendahara Yayasan%')->first();
    if (!$posYayasan) {
        $posYayasan = Position::create([
            'position_code' => 'BEND-YAY',
            'position_name' => 'Bendahara Yayasan',
            'position_category' => 'support',
            'allowance_amount' => 3000000,
            'school_id' => 4,
            'is_active' => true,
        ]);
    } else {
        $posYayasan->allowance_amount = 3000000;
        $posYayasan->save();
    }

    $posSMA = Position::where('position_name', 'like', '%Bendahara SMA%')->first();
    if (!$posSMA) {
        $posSMA = Position::create([
            'position_code' => 'BEND-SMA',
            'position_name' => 'Bendahara SMA',
            'position_category' => 'support',
            'allowance_amount' => 3750000,
            'school_id' => 2,
            'is_active' => true,
        ]);
    } else {
        $posSMA->allowance_amount = 3750000;
        $posSMA->save();
    }

    // Assign Jabatan 1: Bendahara Yayasan
    EmployeePosition::updateOrCreate(
        [
            'employee_id' => $primaryEmp->id,
            'position_id' => $posYayasan->id,
            'academic_year_id' => $activeYear->id,
        ],
        [
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'is_primary' => true,
            'position_allowance' => 3000000,
        ]
    );

    // Assign Jabatan 2: Bendahara SMA
    EmployeePosition::updateOrCreate(
        [
            'employee_id' => $primaryEmp->id,
            'position_id' => $posSMA->id,
            'academic_year_id' => $activeYear->id,
        ],
        [
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'is_primary' => false,
            'position_allowance' => 3750000,
        ]
    );

    echo "4. Penugasan Jabatan Berhasil Dipasang ke Pegawai Utama:\n";
    echo "   - Bendahara Yayasan (Rp 3.000.000)\n";
    echo "   - Bendahara SMA (Rp 3.750.000)\n";

    // 5. Kalkulasi Ulang Beban Kerja & Gaji untuk Pegawai Utama
    $service = app(EmployeeAssignmentService::class);
    $summary = $service->calculateWorkload($primaryEmp, $activeYear, $activeSem);

    echo "5. Kalkulasi Ulang Beban Kerja Selesai:\n";
    echo "   > Unit Sekolah           : Yayasan Perguruan Pembda Nias\n";
    echo "   > Gaji Pokok             : Rp " . number_format($summary->basic_salary, 0, ',', '.') . "\n";
    echo "   > Total Tunj. Jabatan    : Rp " . number_format($summary->total_position_allowance, 0, ',', '.') . "\n";
    echo "   > Total Kompensasi (THP) : Rp " . number_format($summary->total_compensation, 0, ',', '.') . "\n";

    DB::commit();

    echo "\n✅ SINKRONISASI SUKSES DILAKSANAKAN!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
