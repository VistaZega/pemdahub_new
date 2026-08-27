<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Student;

$action = $_REQUEST['action'] ?? 'summary';
$studentName = $_REQUEST['name'] ?? '';

echo "<h3>Diagnostik Data Tagihan (Bulan 7 - 10)</h3>";

if ($action === 'summary') {
    // Check total bills per month
    $bills = DB::table('student_bills')
        ->select('month', DB::raw('count(*) as total'), DB::raw('sum(case when status="lunas" then 1 else 0 end) as lunas'))
        ->whereIn('month', [7, 8, 9, 10])
        ->groupBy('month')
        ->orderBy('month')
        ->get();

    echo "<h4>Ringkasan Tagihan (Bulan 7, 8, 9, 10)</h4>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Bulan</th><th>Total Tagihan</th><th>Total Lunas</th></tr>";
    foreach ($bills as $b) {
        echo "<tr>";
        echo "<td>{$b->month}</td>";
        echo "<td>{$b->total}</td>";
        echo "<td>{$b->lunas}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Form to search for a specific student
    echo "<hr><h4>Cari Siswa Yang Belum Pulih</h4>";
    echo "<form method='GET'>";
    echo "<input type='hidden' name='secret' value='pembda99'>";
    echo "<input type='hidden' name='action' value='search'>";
    echo "<input type='text' name='name' placeholder='Masukkan Nama Siswa' required>";
    echo "<button type='submit'>Cari</button>";
    echo "</form>";

} elseif ($action === 'search' && !empty($studentName)) {
    echo "<h4>Mencari Data Siswa: " . htmlspecialchars($studentName) . "</h4>";
    $students = Student::where('full_name', 'like', "%{$studentName}%")->get();
    
    if ($students->isEmpty()) {
        echo "<p>Siswa tidak ditemukan.</p>";
    }

    foreach ($students as $st) {
        echo "<h5>Siswa: {$st->full_name} (NISN: {$st->nisn}, Status: {$st->status})</h5>";
        
        $bills = DB::table('student_bills')
            ->join('payment_types', 'student_bills.payment_type_id', '=', 'payment_types.id')
            ->where('student_id', $st->id)
            ->whereIn('month', [7, 8, 9, 10])
            ->select('student_bills.*', 'payment_types.type_name')
            ->orderBy('month')
            ->get();
            
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr><th>ID</th><th>Bulan</th><th>Jenis Tagihan</th><th>Nominal</th><th>Sudah Dibayar</th><th>Status</th></tr>";
        
        foreach ($bills as $b) {
            echo "<tr>";
            echo "<td>{$b->id}</td>";
            echo "<td>{$b->month}</td>";
            echo "<td>{$b->type_name}</td>";
            echo "<td>Rp " . number_format($b->amount, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($b->paid_amount, 0, ',', '.') . "</td>";
            echo "<td><strong>{$b->status}</strong></td>";
            echo "</tr>";
            
            // Check payments for this bill
            $payments = DB::table('payments')->where('bill_id', $b->id)->get();
            if ($payments->count() > 0) {
                echo "<tr><td colspan='6' style='background: #f0f0f0;'>";
                echo "<em>Pembayaran untuk tagihan ini:</em><br>";
                foreach ($payments as $p) {
                    echo "- ID: {$p->id} | Tanggal: {$p->created_at} | Ref: {$p->receipt_number} | Rp " . number_format($p->amount_paid, 0, ',', '.') . "<br>";
                }
                echo "</td></tr>";
            }
        }
        echo "</table>";
        
        // Check activity logs for this student's deleted payments
        $logs = DB::table('activity_log')
            ->where('log_name', 'default')
            ->where('event', 'deleted')
            ->where('subject_type', 'App\Models\Payment')
            ->where('properties', 'like', '%"student_id":' . $st->id . '%')
            ->get();
            
        if ($logs->count() > 0) {
            echo "<strong>Ditemukan " . $logs->count() . " log penghapusan pembayaran untuk siswa ini.</strong><br>";
        } else {
            echo "<strong>Tidak ada log penghapusan pembayaran untuk siswa ini di activity_log.</strong><br>";
        }
    }
}
