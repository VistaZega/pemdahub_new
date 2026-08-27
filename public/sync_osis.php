<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\PaymentType;
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\Student;

$action = $_REQUEST['action'] ?? 'preview';

echo "<h3>Sinkronisasi Iuran OSIS (Juli & Agustus) untuk SMAS Pembda 1</h3>";

// 1. Get School
$school = School::where('name', 'SMAS Pembda 1 Gunungsitoli')->first();
if (!$school) {
    $school = School::where('name', 'LIKE', '%SMAS Pembda 1%')->first();
}
if (!$school) die("Sekolah tidak ditemukan.");

// 2. Get Payment Types
$sppType = PaymentType::where('school_id', $school->id)->where('type_name', 'LIKE', '%SPP%')->first();
$osisType = PaymentType::where('school_id', $school->id)->where('type_name', 'LIKE', '%Iuran OSIS%')->first();

if (!$sppType || !$osisType) die("Tipe pembayaran SPP atau OSIS tidak ditemukan.");

// 3. Find all SPP Payments for July and August
$sppPayments = Payment::select('payments.*', 'sb.month', 'sb.year', 'sb.academic_year_id', 'sb.semester_id')
    ->join('student_bills as sb', 'payments.bill_id', '=', 'sb.id')
    ->join('students as s', 'payments.student_id', '=', 's.id')
    ->where('sb.payment_type_id', $sppType->id)
    ->whereIn('sb.month', [7, 8])
    ->where('s.school_id', $school->id)
    ->get();

$toCreateBills = 0;
$toCreatePayments = 0;
$previewData = [];

foreach ($sppPayments as $sppPay) {
    // Check if OSIS bill exists for this month and academic year
    $osisBill = StudentBill::where('student_id', $sppPay->student_id)
        ->where('payment_type_id', $osisType->id)
        ->where('academic_year_id', $sppPay->academic_year_id)
        ->where('month', $sppPay->month)
        ->where('year', $sppPay->year)
        ->first();

    $needsBill = false;
    $needsPayment = false;

    if (!$osisBill) {
        $needsBill = true;
        $needsPayment = true;
    } else {
        // Check if payment exists
        $osisPaymentExists = Payment::where('bill_id', $osisBill->id)->exists();
        if (!$osisPaymentExists) {
            $needsPayment = true;
        }
    }

    if ($needsBill || $needsPayment) {
        if ($needsBill) $toCreateBills++;
        if ($needsPayment) $toCreatePayments++;
        
        $previewData[] = [
            'student_id' => $sppPay->student_id,
            'month' => $sppPay->month,
            'payment_date' => $sppPay->payment_date,
            'needs_bill' => $needsBill,
            'needs_payment' => $needsPayment
        ];
    }
}

echo "Ditemukan <strong>$toCreateBills</strong> Tagihan OSIS yang harus dibuat.<br>";
echo "Ditemukan <strong>$toCreatePayments</strong> Transaksi Pembayaran OSIS yang harus disuntikkan.<br><br>";

if ($action === 'preview') {
    echo "<a href='sync_osis.php?secret=pembda99&action=execute' style='padding:10px; background:blue; color:white; text-decoration:none;'>JALANKAN SINKRONISASI SEKARANG</a><br><br>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Student ID</th><th>Bulan</th><th>Tgl Bayar SPP (Akan jadi Tgl Bayar OSIS)</th><th>Status</th></tr>";
    $i = 0;
    foreach ($previewData as $d) {
        if ($i++ > 100) {
            echo "<tr><td colspan='4'>... dan " . (count($previewData) - 100) . " lainnya.</td></tr>";
            break;
        }
        echo "<tr>";
        echo "<td>{$d['student_id']}</td>";
        echo "<td>{$d['month']}</td>";
        echo "<td>{$d['payment_date']}</td>";
        echo "<td>" . ($d['needs_bill'] ? "Buat Tagihan & Bayar" : "Hanya Bayar (Tagihan Ada)") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

if ($action === 'execute') {
    DB::beginTransaction();
    try {
        $createdBills = 0;
        $createdPayments = 0;
        $adminId = 1; // Fallback admin user ID or get from session if needed, but this is a CLI script basically
        
        foreach ($sppPayments as $sppPay) {
            $osisBill = StudentBill::where('student_id', $sppPay->student_id)
                ->where('payment_type_id', $osisType->id)
                ->where('academic_year_id', $sppPay->academic_year_id)
                ->where('month', $sppPay->month)
                ->where('year', $sppPay->year)
                ->first();

            if (!$osisBill) {
                // Ensure duplicate bill doesn't happen due to race conditions
                $osisBill = StudentBill::firstOrCreate([
                    'student_id' => $sppPay->student_id,
                    'payment_type_id' => $osisType->id,
                    'academic_year_id' => $sppPay->academic_year_id,
                    'month' => $sppPay->month,
                    'year' => $sppPay->year,
                ], [
                    'semester_id' => $sppPay->semester_id,
                    'amount' => $osisType->amount ?? 10000,
                    'yayasan_share_amount' => $osisType->yayasan_share_amount ?? 0,
                    'paid_amount' => 0,
                    'status' => 'belum_bayar',
                    'created_at' => $sppPay->created_at, // align creation with SPP
                    'updated_at' => $sppPay->updated_at
                ]);
                $createdBills++;
            }

            $osisPaymentExists = Payment::where('bill_id', $osisBill->id)->exists();
            if (!$osisPaymentExists) {
                Payment::create([
                    'bill_id' => $osisBill->id,
                    'student_id' => $sppPay->student_id,
                    'amount_paid' => $osisType->amount ?? 10000,
                    'payment_date' => $sppPay->payment_date,
                    'payment_method' => $sppPay->payment_method ?? 'cash',
                    'reference_number' => $sppPay->reference_number ? $sppPay->reference_number . '-OSIS' : null,
                    'notes' => 'Pembayaran otomatis tersinkronisasi bersama SPP (Pemulihan)',
                    'received_by' => $sppPay->received_by,
                    'created_at' => $sppPay->created_at,
                    'updated_at' => $sppPay->updated_at
                ]);
                
                $osisBill->paid_amount = $osisType->amount ?? 10000;
                $osisBill->status = 'lunas';
                $osisBill->save();
                
                $createdPayments++;
            }
        }
        
        DB::commit();
        echo "<h4 style='color:green;'>Selesai! Berhasil membuat $createdBills tagihan OSIS dan mencatatkan $createdPayments pelunasan OSIS secara otomatis. (Tidak ada data ganda)</h4>";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "<h4 style='color:red;'>Gagal: " . $e->getMessage() . "</h4>";
    }
}
