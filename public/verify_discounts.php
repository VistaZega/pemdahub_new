<?php
/**
 * Script Verifikasi & Laporan Bukti Penerapan Diskon Siswa 12 Bulan Penuh
 * Akses: https://perguruanpembda.com/verify_discounts.php
 */

$allowedTokens = ['pembda99', 'pembda', 'pembdahub', 'pembda2026', 'secret', 'token'];
$providedToken = $_GET['secret'] ?? $_GET['token'] ?? null;

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
if (isset($_POST['action']) && in_array($_POST['action'], ['sync_all_months', 'force_sync_all'])) {
    $forcePaid = ($_POST['action'] === 'force_sync_all');
    DB::beginTransaction();
    try {
        $julyCustomBills = DB::table('student_bills as sb')
            ->join('payment_types as pt', 'sb.payment_type_id', '=', 'pt.id')
            ->where('sb.academic_year_id', $academicYearId)
            ->where('sb.month', 7)
            ->whereRaw('sb.amount != pt.amount')
            ->select('sb.student_id', 'sb.payment_type_id', 'sb.amount', 'sb.yayasan_share_amount')
            ->get();

        $updatedBillsCount = 0;
        $updatedPaymentsCount = 0;

        foreach ($julyCustomBills as $cb) {
            $query = DB::table('student_bills')
                ->where('academic_year_id', $academicYearId)
                ->where('student_id', $cb->student_id)
                ->where('payment_type_id', $cb->payment_type_id)
                ->where('month', '!=', 7);

            if (!$forcePaid) {
                $query->where('paid_amount', 0);
            }

            $affectedBills = $query->get();

            foreach ($affectedBills as $ab) {
                $updateData = [
                    'amount' => $cb->amount,
                    'yayasan_share_amount' => $cb->yayasan_share_amount,
                ];

                if ($ab->paid_amount > 0 && $forcePaid) {
                    $updateData['paid_amount'] = $cb->amount;
                    $updateData['status'] = 'lunas';

                    // Also update payment record amount_paid
                    $pUpdated = DB::table('payments')
                        ->where('bill_id', $ab->id)
                        ->update(['amount_paid' => $cb->amount]);
                    $updatedPaymentsCount += $pUpdated;
                }

                DB::table('student_bills')
                    ->where('id', $ab->id)
                    ->update($updateData);

                $updatedBillsCount++;
            }
        }
        DB::commit();
        $actionMsg = "✅ Berhasil menyinkronkan {$updatedBillsCount} tagihan bulan berikutnya ke nominal diskon Juli! " . ($forcePaid ? "({$updatedPaymentsCount} kwitansi/pembayaran disesuaikan)" : "");
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
$unsyncedList = [];
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
    $reasons = [];
    $isAllSynced = true;

    foreach ($months as $m) {
        $bill = $billsByMonth->get($m);
        if ($bill) {
            $isDiscountMatch = ((float)$bill->amount == (float)$ds->july_amount);
            if (!$isDiscountMatch) {
                $isAllSynced = false;
                if ((float)$bill->paid_amount > 0) {
                    $reasons[] = "Bulan {$monthNames[$m]}: Tagihan Rp " . number_format($bill->amount, 0, ',', '.') . " SUDAH DIBAYAR (Lunas/Cicilan). Klik 'Update Paksa Termasuk Lunas' di atas untuk menyelaraskannya ke nominal diskon.";
                } else {
                    $reasons[] = "Bulan {$monthNames[$m]}: Nominal tagihan (Rp " . number_format($bill->amount, 0, ',', '.') . ") belum sama dengan potongan Juli (Rp " . number_format($ds->july_amount, 0, ',', '.') . ").";
                }
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
            $reasons[] = "Bulan {$monthNames[$m]}: Tagihan belum terbuat di database.";
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
        $unsyncedList[] = [
            'student' => $ds,
            'reasons' => $reasons,
        ];
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
    <style>body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; }</style>
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
                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelaraskan SEMUA tagihan (termasuk tagihan & kwitansi yang sudah dibayar) ke nominal diskon Juli?');">
                    <input type="hidden" name="action" value="force_sync_all">
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-bolt"></i> Update Paksa Termasuk Lunas (100% Sesuai Diskon)
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
                <div class="text-xs font-bold <?= $totalPendingSync > 0 ? 'text-amber-600' : 'text-gray-400' ?> uppercase">Perlu Sinkronisasi / Update</div>
                <div class="text-3xl font-black <?= $totalPendingSync > 0 ? 'text-amber-600' : 'text-gray-800' ?> mt-1"><?= $totalPendingSync ?> Siswa</div>
                <div class="text-xs text-gray-500 mt-1 font-medium">Siswa yang memerlukan penyelarasan diskon di bulan berikutnya</div>
            </div>
        </div>

        <!-- SECTION ANALISIS DIAGNOSTIK SISWA BELUM SINKRON (BILA ADA) -->
        <?php if(!empty($unsyncedList)): ?>
            <div class="bg-amber-50/60 rounded-2xl border-2 border-amber-200 p-5 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-amber-200/80 pb-3">
                    <div>
                        <h2 class="text-base font-black text-amber-900 flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-amber-600"></i> Rincian Diagnostik <?= count($unsyncedList) ?> Siswa Perlu Update
                        </h2>
                        <p class="text-xs text-amber-700 mt-0.5 font-medium">
                            Tagihan di bulan tersebut masih dengan nominal lama. Klik tombol <strong>"Update Paksa Termasuk Lunas"</strong> di atas untuk mengubah nominal tagihan & kwitansi ke harga diskon.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach($unsyncedList as $uIdx => $uRow): ?>
                        <?php $us = $uRow['student']; ?>
                        <div class="bg-white rounded-xl p-4 border border-amber-200 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <span class="text-[10px] font-black bg-amber-100 text-amber-800 px-2 py-0.5 rounded-md mr-1">#<?= $uIdx + 1 ?></span>
                                    <span class="font-bold text-gray-900 text-xs"><?= htmlspecialchars($us->full_name) ?></span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-semibold"><?= htmlspecialchars($us->school_name) ?></span>
                            </div>

                            <div class="text-[11px] text-gray-600 flex items-center justify-between">
                                <span>Nominal Juli: <strong class="text-indigo-700">Rp <?= number_format($us->july_amount, 0, ',', '.') ?></strong></span>
                                <span class="text-gray-400 line-through text-[10px]">Default: Rp <?= number_format($us->default_amount, 0, ',', '.') ?></span>
                            </div>

                            <div class="space-y-1 pt-1">
                                <?php foreach($uRow['reasons'] as $reas): ?>
                                    <div class="text-[11px] text-amber-800 flex items-start gap-1.5 font-semibold">
                                        <i class="fas fa-info-circle text-[10px] mt-0.5 text-amber-600"></i>
                                        <span><?= htmlspecialchars($reas) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

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
                                                    <span class="inline-block px-1.5 py-1 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shadow-2xs" title="Tagihan belum disesuaikan: Rp <?= number_format($mInfo['amount'], 0, ',', '.') ?>">
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
                                                ⚠️ Perlu Update
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
