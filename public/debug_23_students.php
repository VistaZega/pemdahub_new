<?php
/**
 * Script Diagnostik & Analisis Detail 23 Siswa Belum Sinkron
 * Akses: https://perguruanpembda.com/debug_23_students.php?secret=pembda99
 */

$allowedTokens = ['pembda99', 'pembda', 'pembdahub', 'pembda2026', 'secret', 'token'];
$providedToken = $_GET['secret'] ?? $_GET['token'] ?? null;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

$activeYear = AcademicYear::where('is_active', true)->first();
$academicYearId = $_GET['academic_year_id'] ?? ($activeYear?->id ?? 5);
$academicYear = AcademicYear::find($academicYearId);

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

$unsyncedList = [];

foreach ($discountedStudents as $ds) {
    $billsByMonth = DB::table('student_bills')
        ->where('academic_year_id', $academicYearId)
        ->where('student_id', $ds->student_id)
        ->where('payment_type_id', $ds->payment_type_id)
        ->get()
        ->keyBy('month');

    $reasons = [];
    $isAllSynced = true;

    foreach ($months as $m) {
        $bill = $billsByMonth->get($m);
        if ($bill) {
            $diff = abs((float)$bill->amount - (float)$ds->july_amount);
            if ($diff > 0.01) {
                $isAllSynced = false;
                if ((float)$bill->paid_amount > 0) {
                    $reasons[] = "Bulan {$monthNames[$m]}: Tagihan nominal Rp " . number_format($bill->amount, 0, ',', '.') . " SUDAH DIBAYAR (Rp " . number_format($bill->paid_amount, 0, ',', '.') . ") sehingga nominalnya dikunci untuk keamanan kwitansi.";
                } else {
                    $reasons[] = "Bulan {$monthNames[$m]}: Nominal tagihan (Rp " . number_format($bill->amount, 0, ',', '.') . ") tidak sama dengan potongan Juli (Rp " . number_format($ds->july_amount, 0, ',', '.') . ").";
                }
            }
        } else {
            $isAllSynced = false;
            $reasons[] = "Bulan {$monthNames[$m]}: Tagihan belum terbuat / belum ada di database.";
        }
    }

    if (!$isAllSynced) {
        $unsyncedList[] = [
            'student' => $ds,
            'reasons' => $reasons,
            'bills' => $billsByMonth,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Detail 23 Siswa Belum Sinkron - PembdaHUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; }</style>
</head>
<body class="p-4 md:p-8 text-gray-800">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="bg-gradient-to-r from-amber-600 via-orange-600 to-rose-700 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black flex items-center gap-2">
                    <i class="fas fa-search-dollar text-amber-200"></i> Laporan Diagnostik: <?= count($unsyncedList) ?> Siswa Belum Sinkron
                </h1>
                <p class="text-xs text-amber-100 mt-1">
                    Analisis rinci penyebab mengapa nominal tagihan siswa tidak sama dengan potongan Juli
                </p>
            </div>
            <div>
                <a href="verify_discounts.php?secret=pembda99" class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-4 py-2 rounded-xl transition inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Matriks Utama
                </a>
            </div>
        </div>

        <div class="space-y-4">
            <?php foreach($unsyncedList as $idx => $row): ?>
                <?php $s = $row['student']; ?>
                <div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3 mb-3">
                        <div>
                            <span class="text-xs font-black bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full mr-2">#<?= $idx + 1 ?></span>
                            <span class="font-black text-gray-900 text-base"><?= htmlspecialchars($s->full_name) ?></span>
                            <span class="text-xs text-gray-400 font-semibold ml-2">(NISN: <?= htmlspecialchars($s->nisn ?? '-') ?>)</span>
                        </div>
                        <div class="text-xs font-bold text-gray-500">
                            Unit: <span class="text-gray-800"><?= htmlspecialchars($s->school_name) ?></span> | 
                            Diskon Juli: <span class="text-indigo-700 font-black">Rp <?= number_format($s->july_amount, 0, ',', '.') ?></span>
                            <span class="text-gray-400 line-through text-[11px]">(Default: Rp <?= number_format($s->default_amount, 0, ',', '.') ?>)</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="text-xs font-bold text-rose-700 flex items-center gap-1.5">
                            <i class="fas fa-exclamation-triangle"></i> Penyebab Belum Sinkron (<?= count($row['reasons']) ?> Catatan):
                        </div>
                        <ul class="space-y-1.5 pl-4">
                            <?php foreach($row['reasons'] as $r): ?>
                                <li class="text-xs text-gray-700 flex items-start gap-2">
                                    <span class="text-rose-500 mt-0.5">•</span>
                                    <span><?= htmlspecialchars($r) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</body>
</html>
