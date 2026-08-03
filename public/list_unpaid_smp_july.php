<?php
/**
 * Script untuk melihat daftar siswa aktif SMP Swasta Pembda 2 
 * yang belum melunasi SPP Bulan Juli 2026.
 * 
 * Akses: /list_unpaid_smp_july.php?secret=pembda99
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\School;
use App\Models\StudentBill;

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>Daftar Tunggakan SPP Juli 2026 SMP Pembda 2</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0;}";
echo ".found{color:#00ff88;}.notfound{color:#ff6b6b;}.header{color:#00d4ff;font-size:18px;}";
echo ".warning{color:#ffd700;}.info{color:#aaa;}";
echo "table{border-collapse:collapse;margin:10px 0;width:100%;}th,td{border:1px solid #444;padding:6px 12px;text-align:left;}";
echo "th{background:#2a2a4a;color:#00d4ff;}</style></head><body>";

$school = School::where('name', 'like', '%SMP Swasta Pembda 2%')->first();
if (!$school) {
    echo "<div class='notfound'>❌ Sekolah tidak ditemukan!</div></body></html>";
    exit;
}

echo "<div class='header'>📋 Daftar Siswa Aktif SMP Swasta Pembda 2 Belum Lunas SPP Juli 2026</div><hr>";

$unpaidBills = StudentBill::with(['student', 'student.currentClassroom'])
    ->whereHas('student', fn($q) => $q->where('school_id', $school->id)->where('status', 'aktif'))
    ->where('month', 7)
    ->where('year', 2026)
    ->where('status', '!=', 'lunas')
    ->get();

$students = $unpaidBills->map(function ($bill) {
    $classroom = $bill->student->currentClassroom()->first();
    return [
        'id' => $bill->student->id,
        'name' => $bill->student->full_name,
        'nisn' => $bill->student->nisn,
        'class' => $classroom ? $classroom->class_name : '-',
        'bill_status' => $bill->status,
        'amount' => $bill->amount,
        'paid' => $bill->paid_amount,
        'sisa' => $bill->amount - $bill->paid_amount
    ];
})->sortBy('class')->values();

echo "<div class='warning'>Total Siswa Belum Lunas: " . $students->count() . " orang</div><br>";

if ($students->count() > 0) {
    echo "<table>";
    echo "<tr><th>#</th><th>Nama Siswa</th><th>NISN</th><th>Kelas</th><th>Status Tagihan</th><th>Tagihan</th><th>Sisa Bayar</th></tr>";
    
    $totalSisa = 0;
    foreach ($students as $i => $s) {
        $totalSisa += $s['sisa'];
        echo "<tr>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td><b>{$s['name']}</b></td>";
        echo "<td>{$s['nisn']}</td>";
        echo "<td>{$s['class']}</td>";
        echo "<td class='notfound'>{$s['bill_status']}</td>";
        echo "<td>Rp " . number_format($s['amount'], 0, ',', '.') . "</td>";
        echo "<td class='warning'>Rp " . number_format($s['sisa'], 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo "<tr><th colspan='6' style='text-align:right'>Total Kekurangan:</th><th class='warning'>Rp " . number_format($totalSisa, 0, ',', '.') . "</th></tr>";
    echo "</table>";
} else {
    echo "<div class='found'>✅ Semua siswa sudah melunasi SPP bulan Juli 2026.</div>";
}

echo "<br><hr><div class='info'>⏰ Dicetak pada: " . date('Y-m-d H:i:s') . "</div>";
echo "</body></html>";
