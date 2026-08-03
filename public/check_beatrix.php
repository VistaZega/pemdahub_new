<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Pengecekan Data Siswa: BEATRIX FIRSTY ANGEL NDRURU</h1>";

try {
    $student = \App\Models\Student::where('name', 'like', '%BEATRIX FIRSTY ANGEL NDRURU%')->first();
    if (!$student) {
        echo "<p style='color:red'>Data siswa tidak ditemukan di database!</p>";
    } else {
        echo "<p><strong>Nama:</strong> " . $student->name . "</p>";
        echo "<p><strong>ID Siswa:</strong> " . $student->id . "</p>";
        echo "<p><strong>Status:</strong> " . $student->status . "</p>";
        
        $class = $student->currentClass;
        echo "<p><strong>Kelas Saat Ini:</strong> " . ($class ? $class->name : 'Tidak ada kelas aktif') . "</p>";
        
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        echo "<p><strong>Tahun Pelajaran Aktif:</strong> " . ($activeYear ? $activeYear->name : 'Tidak ada') . "</p>";

        $bills = $student->bills()->with('paymentType')->get();
        echo "<h3>Daftar Tagihan (Bills) - Total: " . $bills->count() . "</h3>";
        
        if ($bills->count() > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>Jenis Pembayaran</th><th>Nominal</th><th>Status</th><th>Tahun Pelajaran</th></tr>";
            foreach($bills as $bill) {
                $typeName = $bill->paymentType ? $bill->paymentType->name : 'Unknown';
                $yearName = $bill->academicYear ? $bill->academicYear->name : 'Unknown';
                $statusColor = $bill->status == 'paid' ? 'green' : ($bill->status == 'partial' ? 'orange' : 'red');
                echo "<tr>";
                echo "<td>{$typeName}</td>";
                echo "<td>Rp " . number_format($bill->amount, 0, ',', '.') . "</td>";
                echo "<td style='color:{$statusColor}'>{$bill->status}</td>";
                echo "<td>{$yearName}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:red'>TIDAK ADA TAGIHAN SAMA SEKALI.</p>";
            echo "<p><strong>Solusi:</strong> Silakan cek apakah siswa ini sudah dimasukkan ke kelas yang benar pada tahun pelajaran ini, dan pastikan tagihan (bill) sudah digenerate untuk kelas tersebut.</p>";
            
            // Check if there are payment types assigned to the class
            if ($class && $activeYear) {
                $paymentTypes = \App\Models\PaymentType::whereHas('classes', function($q) use ($class) {
                    $q->where('classrooms.id', $class->id);
                })->get();
                echo "<h4>Jenis Pembayaran yang ter-assign ke Kelas {$class->name}:</h4>";
                if ($paymentTypes->count() > 0) {
                    echo "<ul>";
                    foreach ($paymentTypes as $pt) {
                        echo "<li>{$pt->name} (Rp " . number_format($pt->amount, 0, ',', '.') . ")</li>";
                    }
                    echo "</ul>";
                    echo "<p>Karena jenis pembayaran ada tapi tagihan tidak ada, silakan gunakan fitur 'Generate Tagihan' di menu Admin/Bendahara untuk siswa/kelas ini.</p>";
                } else {
                    echo "<p style='color:red'>Belum ada Jenis Pembayaran yang di-assign ke kelas ini.</p>";
                }
            }
        }
    }
} catch (\Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
