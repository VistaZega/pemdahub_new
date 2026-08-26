<?php

/**
 * Script Sinkronisasi Otomatis Pembayaran Iuran OSIS dari Pembayaran SPP yang Sudah Lunas
 * 
 * Akses Simulasi: https://perguruanpembda.com/sync_osis_payments.php?secret=pembda99
 * Akses Eksekusi: https://perguruanpembda.com/sync_osis_payments.php?secret=pembda99&execute=1
 */

// Security Check
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\School;

$isExecute = ($_GET['execute'] ?? '') === '1';
$targetSchoolId = $_GET['school_id'] ?? null;

echo "<!DOCTYPE html><html lang='id'><head><title>Sinkronisasi Iuran OSIS - PembdaHUB</title>";
echo "<style>body{font-family:Segoe UI,Helvetica,sans-serif;background:#0f172a;color:#f8fafc;padding:25px;line-height:1.6}";
echo ".card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1)}";
echo ".ok{color:#4ade80;font-weight:bold}.warn{color:#facc15}.info{color:#38bdf8}";
echo "h1{color:#38bdf8;font-size:22px;margin-bottom:10px}h2{color:#cbd5e1;font-size:16px;margin-bottom:15px}";
echo "table{width:100%;border-collapse:collapse;margin-top:15px;font-size:13px}";
echo "th{background:#0f172a;color:#94a3b8;padding:10px;text-align:left;border:1px solid #334155}";
echo "td{padding:10px;border:1px solid #334155}";
echo "a.btn{display:inline-block;background:#2563eb;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;margin-right:10px;transition:0.2s}";
echo "a.btn:hover{background:#1d4ed8}a.btn-success{background:#16a34a}a.btn-success:hover{background:#15803d}</style></head><body>";

echo "<div class='card'>";
echo "<h1>💳 Tool Sinkronisasi Pembayaran Iuran OSIS (Rp 10.000)</h1>";
echo "<p class='info'>Tool ini menandai LUNAS tagihan <strong>Iuran OSIS</strong> bagi siswa yang pembayaran <strong>SPP</strong> pada bulan terkait sudah LUNAS.</p>";

if ($isExecute) {
    echo "<p class='ok'>⚡ Mode: EKSEKUSI RIIL PADA DATABASE</p>";
} else {
    echo "<p class='warn'>🔍 Mode: SIMULASI / DRY-RUN (Belum mengubah data)</p>";
}
echo "</div>";

// Query all unpaid OSIS bills
$osisQuery = StudentBill::with(['student.school', 'paymentType', 'academicYear'])
    ->whereHas('paymentType', function ($q) {
        $q->where('type_code', 'OSIS');
    })
    ->where('status', '!=', 'lunas');

if ($targetSchoolId) {
    $osisQuery->whereHas('student', function ($q) use ($targetSchoolId) {
        $q->where('school_id', $targetSchoolId);
    });
}

$unpaidOsisBills = $osisQuery->get();

$matchCount = 0;
$executedCount = 0;
$details = [];

foreach ($unpaidOsisBills as $osisBill) {
    $student = $osisBill->student;
    if (!$student) continue;

    // Find corresponding SPP bill for the same student, academic year, and month
    $sppBill = StudentBill::where('student_id', $student->id)
        ->where('academic_year_id', $osisBill->academic_year_id)
        ->where('month', $osisBill->month)
        ->whereHas('paymentType', function ($q) {
            $q->where('type_code', 'like', 'SPP%');
        })
        ->first();

    // Check if SPP bill exists and is LUNAS (or fully paid)
    if ($sppBill && ($sppBill->status === 'lunas' || $sppBill->paid_amount >= $sppBill->amount)) {
        $matchCount++;

        // Get SPP payment date if available
        $sppPayment = Payment::where('bill_id', $sppBill->id)->where('is_verified', true)->orderBy('payment_date', 'desc')->first();
        $paymentDate = $sppPayment ? $sppPayment->payment_date : now();

        $details[] = [
            'student_name' => $student->full_name,
            'nisn' => $student->nisn,
            'school_name' => $student->school->name ?? '-',
            'month' => $osisBill->month,
            'year' => $osisBill->year,
            'osis_amount' => $osisBill->amount - $osisBill->paid_amount,
            'spp_status' => '✓ LUNAS',
            'spp_payment_date' => $paymentDate ? \Carbon\Carbon::parse($paymentDate)->format('d/m/Y H:i') : '-',
        ];

        if ($isExecute) {
            $amountToPay = $osisBill->amount - $osisBill->paid_amount;
            if ($amountToPay > 0) {
                Payment::create([
                    'bill_id' => $osisBill->id,
                    'student_id' => $student->id,
                    'amount_paid' => $amountToPay,
                    'payment_date' => $paymentDate,
                    'payment_method' => 'cash',
                    'is_verified' => true,
                    'notes' => 'Otomatis ditandai Lunas mengikuti Pembayaran SPP Bulan ' . $osisBill->month,
                ]);
                $executedCount++;
            }
        }
    }
}

echo "<div class='card'>";
echo "<h2>Hasil Analisis Tagihan OSIS yang Siap Disinkronkan:</h2>";
echo "<p>Total Tagihan OSIS Belum Lunas Ditemukan: <strong>" . count($unpaidOsisBills) . "</strong></p>";
echo "<p>Total Tagihan OSIS yang Siswanya <strong>SUDAH LUNAS SPP</strong>: <strong class='ok'>" . $matchCount . " Tagihan</strong></p>";

if ($isExecute) {
    echo "<p class='ok'>✅ BERHASIL MEMPROSES DAN MENANDAI LUNAS: <strong>{$executedCount} Tagihan OSIS</strong></p>";
} else {
    if ($matchCount > 0) {
        $execUrl = "sync_osis_payments.php?secret=pembda99&execute=1" . ($targetSchoolId ? "&school_id={$targetSchoolId}" : "");
        echo "<div style='margin-top:20px;'>";
        echo "<a href='{$execUrl}' class='btn btn-success' onclick='return confirm(\"Apakah Anda yakin ingin mengeksekusi penandaan LUNAS untuk {$matchCount} tagihan OSIS ini?\")'>▶️ Tandai Lunas Sekarang ({$matchCount} Tagihan)</a>";
        echo "</div>";
    } else {
        echo "<p class='info'>ℹ️ Tidak ada tagihan OSIS menggantung yang siswanya sudah Lunas SPP.</p>";
    }
}
echo "</div>";

if (!empty($details)) {
    echo "<div class='card'>";
    echo "<h2>Daftar Siswa & Tagihan OSIS yang Disinkronkan:</h2>";
    echo "<table><thead><tr><th>No</th><th>Siswa</th><th>NISN</th><th>Sekolah</th><th>Bulan/Tahun</th><th>Sisa OSIS</th><th>Status SPP</th><th>Tgl Bayar SPP</th></tr></thead><tbody>";
    foreach ($details as $idx => $d) {
        $no = $idx + 1;
        echo "<tr>";
        echo "<td>{$no}</td>";
        echo "<td><strong>{$d['student_name']}</strong></td>";
        echo "<td>{$d['nisn']}</td>";
        echo "<td>{$d['school_name']}</td>";
        echo "<td style='text-align:center'>Bulan {$d['month']} / {$d['year']}</td>";
        echo "<td style='text-align:right'>Rp " . number_format($d['osis_amount'], 0, ',', '.') . "</td>";
        echo "<td class='ok' style='text-align:center'>{$d['spp_status']}</td>";
        echo "<td style='text-align:center'>{$d['spp_payment_date']}</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "</div>";
}

echo "</body></html>";
