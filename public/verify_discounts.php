<?php
/**
 * Script Verifikasi & Bukti Penerapan Diskon / Potongan Siswa 12 Bulan Penuh
 * Akses via browser: https://perguruanpembda.com/verify_discounts.php?secret=pembda99
 */

$SECRET_KEY = 'pembda99';
if (!isset($_GET['secret']) || $_GET['secret'] !== $SECRET_KEY) {
    http_response_code(403);
    die('⛔ Akses ditolak. Kunci rahasia tidak valid.');
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StudentBill;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

$activeYear = AcademicYear::where('is_active', true)->first();
$academicYearId = $_GET['academic_year_id'] ?? ($activeYear?->id ?? 5);
$academicYear = AcademicYear::find($academicYearId);

// Handle Sync Action if requested
$actionMsg = '';
if (isset($_POST['action']) && $_POST['action'] === 'sync_all_months') {
    DB::beginTransaction();
    try {
        // Find all student custom discounts in July (Month 7)
        $julyCustomBills = DB::table('student_bills as sb')
            ->join('payment_types as pt', 'sb.payment_type_id', '=', 'pt.id')
            ->where('sb.academic_year_id', $academicYearId)
            ->where('sb.month', 7)
            ->whereRaw('sb.amount != pt.amount')
            ->select('sb.student_id', 'sb.payment_type_id', 'sb.amount', 'sb.yayasan_share_amount')
            ->get();

        $updatedCount = 0;
        foreach ($julyCustomBills as $cb) {
            $updated = DB::table('student_bills')
                ->where('academic_year_id', $academicYearId)
                ->where('student_id', $cb->student_id)
                ->where('payment_type_id', $cb->payment_type_id)
                ->where('month', '!=', 7)
                ->where('paid_amount', 0) // only update unpaid bills
                ->update([
                    'amount' => $cb->amount,
                    'yayasan_share_amount' => $cb->yayasan_share_amount,
                ]);
            $updatedCount += $updated;
        }
        DB::commit();
        $actionMsg = "✅ Berhasil menyinkronkan {$updatedCount} tagihan bulan berikutnya (Agustus s.d. Juni) dengan nominal potongan Juli!";
    } catch (\Exception $e) {
        DB::rollBack();
        $actionMsg = "❌ Gagal menyinkronkan: " . $e->getMessage();
    }
}

// Fetch Audit Data
$months = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
$monthNames = [
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni'
];

$discountedStudents = DB::table('student_bills as sb')
    ->join('students as s', 'sb.student_id', '=', 's.id')
    ->join('schools as sch', 's.school_id', '=', 'sch.id')
    ->join('payment_types as pt', 'sb.payment_type_id', '=', 'pt.id')
    ->where('sb.academic_year_id', $academicYearId)
    ->where('sb.month', 7)
    ->whereRaw('sb.amount != pt.amount')
    ->select(
        's.id as student_id',
        's.full_name',
        's.nisn',
        'sch.name as school_name',
        'pt.id as payment_type_id',
        'pt.type_name',
        'pt.amount as default_amount',
        'sb.amount as july_amount',
        'sb.yayasan_share_amount as july_yayasan_share'
    )
    ->orderBy('sch.id')
    ->orderBy('s.full_name')
    ->get();

$auditResults = [];
$totalVerified = 0;
$totalPendingSync = 0;

foreach ($discountedStudents as $ds) {
    $billsByMonth = DB::table('student_bills')
        ->where('academic_year_id', $academicYearId)
        ->where('student_id', $ds->student_id)
        ->where('payment_type_id', $ds->payment_type_id)
        ->get()
        ->keyBy('month');

    $monthDetails = [];
    $isAllSynced = true;

    foreach ($months as $m) {
        $bill = $billsByMonth->get($m);
        if ($bill) {
            $isDiscountMatch = ((float)$bill->amount == (float)$ds->july_amount);
            if (!$isDiscountMatch) {
                $isAllSynced = false;
            }
            $monthDetails[$m] = [
                'exists' => true,
                'amount' => (float)$bill->amount,
                'paid_amount' => (float)$bill->paid_amount,
                'status' => $bill->status,
                'is_discounted' => $isDiscountMatch,
            ];
        } else {
            $isAllSynced = false;
            $monthDetails[$m] = [
                'exists' => false,
                'amount' => 0,
                'paid_amount' => 0,
                'status' => 'belum_dibuat',
                'is_discounted' => false,
            ];
        }
    }

    if ($isAllSynced) {
        $totalVerified++;
    } else {
        $totalPendingSync++;
    }

    $auditResults[] = [
        'student' => $ds,
        'is_all_synced' => $isAllSynced,
        'months' => $monthDetails,
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit & Bukti Penerapan Diskon Siswa - PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="p-4 md:p-8 text-gray-800">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Bar -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-purple-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black flex items-center gap-2">
                    <i class="fas fa-shield-check text-emerald-400"></i> Audit & Laporan Bukti Diskon Siswa
                </h1>
                <p class="text-xs text-blue-200 mt-1">
                    Verifikasi kepatuhan potongan nominal khusus dari bulan Juli hingga akhir Tahun Pelajaran <?= htmlspecialchars($academicYear->year ?? '2026/2027') ?>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyinkronkan nominal potongan Juli ke seluruh bulan berikutnya (Agustus s.d. Juni)?');">
                    <input type="hidden" name="action" value="sync_all_months">
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Sinkronkan Diskon ke Semua Bulan
                    </button>
                </form>
            </div>
        </div>

        <?php if($actionMsg): ?>
            <div class="p-4 rounded-xl shadow-sm border font-semibold text-sm <?= str_contains($actionMsg, '✅') ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' ?>">
                <?= htmlspecialchars($actionMsg) ?>
            </div>
        <?php endif; ?>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <div class="text-xs font-bold text-gray-400 uppercase">Siswa dengan Potongan Khusus (Juli)</div>
                <div class="text-3xl font-black text-indigo-700 mt-1"><?= count($auditResults) ?> Siswa</div>
                <div class="text-xs text-gray-500 mt-1 font-medium">Terdeteksi nominal di bawah tarif master default</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-emerald-100 shadow-sm bg-emerald-50/20">
                <div class="text-xs font-bold text-emerald-600 uppercase">100% Terverifikasi Diskon 12 Bulan</div>
                <div class="text-3xl font-black text-emerald-600 mt-1"><?= $totalVerified ?> Siswa</div>
                <div class="text-xs text-emerald-700 mt-1 font-medium">Diskon sudah konsisten sampai akhir tahun ajaran (Juni 2027)</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border <?= $totalPendingSync > 0 ? 'border-amber-200 bg-amber-50/20' : 'border-gray-100' ?> shadow-sm">
                <div class="text-xs font-bold <?= $totalPendingSync > 0 ? 'text-amber-600' : 'text-gray-400' ?> uppercase">Perlu Sinkronisasi Bulan Berikutnya</div>
                <div class="text-3xl font-black <?= $totalPendingSync > 0 ? 'text-amber-600' : 'text-gray-800' ?> mt-1"><?= $totalPendingSync ?> Siswa</div>
                <div class="text-xs text-gray-500 mt-1 font-medium">Klik "Sinkronkan Diskon" di atas untuk menyelaraskan otomatis</div>
            </div>
        </div>

        <!-- Audit Matrix Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-check text-indigo-600"></i> Matriks Verifikasi Diskon Bulan per Bulan (Juli 2026 s.d. Juni 2027)
                </h2>
                <span class="text-xs text-gray-400 font-semibold">TP <?= htmlspecialchars($academicYear->year ?? '2026/2027') ?></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Siswa & Unit Sekolah</th>
                            <th class="px-4 py-3 text-right">Tarif Default</th>
                            <th class="px-4 py-3 text-right">Diskon Juli</th>
                            <?php foreach($months as $m): ?>
                                <th class="px-2 py-3 text-center min-w-[70px]"><?= $monthNames[$m] ?></th>
                            <?php endforeach; ?>
                            <th class="px-4 py-3 text-center">Status Audit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(empty($auditResults)): ?>
                            <tr>
                                <td colspan="18" class="p-8 text-center text-gray-400">
                                    Tidak ada data potongan khusus siswa yang ditemukan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($auditResults as $idx => $row): ?>
                                <?php $s = $row['student']; ?>
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-4 py-3 text-gray-400 font-semibold"><?= $idx + 1 ?></td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900"><?= htmlspecialchars($s->full_name) ?></div>
                                        <div class="text-[10px] text-gray-400"><?= htmlspecialchars($s->school_name) ?> · <?= htmlspecialchars($s->type_name) ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-400 line-through">
                                        Rp <?= number_format($s->default_amount, 0, ',', '.') ?>
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-indigo-700 bg-indigo-50/50">
                                        Rp <?= number_format($s->july_amount, 0, ',', '.') ?>
                                    </td>

                                    <!-- Month Columns -->
                                    <?php foreach($months as $m): ?>
                                        <?php $mInfo = $row['months'][$m]; ?>
                                        <td class="px-1 py-3 text-center">
                                            <?php if($mInfo['exists']): ?>
                                                <?php if($mInfo['is_discounted']): ?>
                                                    <span class="inline-block px-1.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs" title="Semua cocok! Billed: Rp <?= number_format($mInfo['amount'], 0, ',', '.') ?>">
                                                        ✓ Rp <?= number_format($mInfo['amount'] / 1000, 0) ?>k
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-block px-1.5 py-1 rounded-lg text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 shadow-2xs" title="Tidak cocok! Tagihan: Rp <?= number_format($mInfo['amount'], 0, ',', '.') ?>">
                                                        ⚠️ Rp <?= number_format($mInfo['amount'] / 1000, 0) ?>k
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-gray-300 font-medium text-[10px]">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td class="px-4 py-3 text-center">
                                        <?php if($row['is_all_synced']): ?>
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ✓ 100% OK
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                                ⚠️ Perlu Sync
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>
