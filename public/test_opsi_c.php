<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use App\Models\PaymentType;
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);

if ($request->query('secret') !== 'pembda99') {
    die("Unauthorized");
}

echo "<html><body style='font-family:sans-serif; line-height:1.6;'>";
echo "<h2>Test Simulasi Opsi C (Revenue Split SPP)</h2>";
echo "<pre>";

try {
    DB::beginTransaction();

    // 1. Cari SMK
    $smk = School::where('name', 'like', '%SMK%')->first();
    if (!$smk) throw new Exception("Unit SMK tidak ditemukan.");
    echo "Unit Sekolah: {$smk->name}\n";

    // 2. Cari Siswa Aktif di SMK
    $student = Student::where('school_id', $smk->id)->where('status', 'aktif')->first();
    if (!$student) throw new Exception("Tidak ada siswa aktif di SMK.");
    echo "Siswa Dummy: {$student->full_name} (NISN: {$student->nisn})\n";

    // 3. Set Master SPP Yayasan menjadi Rp 210.000
    $paymentType = PaymentType::where('school_id', $smk->id)->where('type_code', 'SPP')->first();
    if (!$paymentType) throw new Exception("Master SPP SMK tidak ditemukan.");
    
    // Asumsikan master yayasan share adalah 210,000
    $paymentType->yayasan_share_amount = 210000;
    $paymentType->save();
    
    echo "Master PaymentType SPP SMK diset:\n";
    echo "   - Nama: {$paymentType->type_name}\n";
    echo "   - Porsi Yayasan (yayasan_share_amount): Rp " . number_format($paymentType->yayasan_share_amount, 0, ',', '.') . "\n\n";

    // 4. Generate Tagihan dengan Nominal Kas Sekolah (Rp 220.000)
    $academicYear = AcademicYear::where('is_active', true)->first();
    if (!$academicYear) {
         $academicYear = AcademicYear::first();
    }
    $amountTotal = 220000; // Harga riil yang ditagihkan ke siswa
    
    $billService = app(\App\Services\StudentBillService::class);
    $validated = [
        'payment_type_id' => $paymentType->id,
        'academic_year_id' => $academicYear->id,
        'semester_id' => null,
        'notes' => 'Test Opsi C'
    ];
    
    // Hapus tagihan test sebelumnya untuk siswa ini jika ada
    StudentBill::where('student_id', $student->id)->where('notes', 'Test Opsi C')->delete();

    // Generate Single Bill (Bulan Juli)
    $billsCreated = $billService->generateSingleBills(collect([$student]), $validated, $amountTotal, 7);
    
    // Ambil tagihan yang baru dibuat
    $bill = StudentBill::where('student_id', $student->id)->where('notes', 'Test Opsi C')->first();
    
    echo "<b>[HASIL 1] Pembuatan Tagihan:</b>\n";
    echo "   - Total Tagihan ke Siswa (amount): Rp " . number_format($bill->amount, 0, ',', '.') . " \n";
    echo "   - Porsi untuk Yayasan (yayasan_share_amount): Rp " . number_format($bill->yayasan_share_amount, 0, ',', '.') . " \n";
    
    if ($bill->amount == 220000 && $bill->yayasan_share_amount == 210000) {
        echo "   <span style='color:green'>✅ BERHASIL! Nilai tagihan dan porsi yayasan terpisah dengan benar di database.</span>\n\n";
    } else {
        echo "   <span style='color:red'>❌ GAGAL! Nilai tidak sesuai.</span>\n\n";
    }

    // 5. Bayar Tagihan (Siswa bayar lunas Rp 220.000)
    $payment = Payment::create([
        'student_id' => $student->id,
        'bill_id' => $bill->id,
        'school_id' => $smk->id,
        'academic_year_id' => $academicYear->id,
        'payment_method' => 'Tunai',
        'amount_paid' => 220000,
        'payment_date' => now(),
        'reference_number' => 'TEST-OPSI-C-' . time(),
        'is_verified' => true,
    ]);
    
    $bill->update(['paid_amount' => 220000, 'status' => 'lunas']);

    echo "<b>[HASIL 2] Pembayaran & Laporan Yayasan:</b>\n";
    echo "   - Uang Kasir Masuk (Payment amount_paid): Rp " . number_format($payment->amount_paid, 0, ',', '.') . "\n";
    
    // Simulasi Query ProgressInputController
    $julyPaymentsQuery = Payment::where('id', $payment->id);
    $paymentEntryTotal = (clone $julyPaymentsQuery)
        ->join('student_bills', 'payments.bill_id', '=', 'student_bills.id')
        ->sum(DB::raw('COALESCE(student_bills.yayasan_share_amount, student_bills.amount)'));

    echo "   - Nominal Yang Dilaporkan ke Yayasan: Rp " . number_format($paymentEntryTotal, 0, ',', '.') . "\n";
    
    if ($paymentEntryTotal == 210000) {
        echo "   <span style='color:green'>✅ BERHASIL! Yayasan hanya melihat Rp 210.000 sebagai pemasukan Lunas. Sisa Rp 10.000 aman di kas sekolah.</span>\n";
    } else {
        echo "   <span style='color:red'>❌ GAGAL! Yayasan melihat nilai yang salah.</span>\n";
    }

    // Selalu rollback agar database tidak kotor
    DB::rollBack();
    echo "\n\n<i>* Data testing telah dihapus otomatis (Rollback).</i>\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "<span style='color:red'>Error: " . $e->getMessage() . "</span>\n";
}

echo "</pre></body></html>";
