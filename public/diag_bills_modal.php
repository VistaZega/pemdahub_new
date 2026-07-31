<?php
/**
 * Diagnostik Modal Tagihan - Cek apakah unpaidBillsList render error
 * Akses: https://perguruanpembda.com/diag_bills_modal.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { http_response_code(403); die('Forbidden'); }

header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>Diagnostik Modal Tagihan</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.8}";
echo ".ok{color:#00e676}.warn{color:#ffc107}.err{color:#ff5252}.info{color:#40c4ff}";
echo "h1{color:#bb86fc}h2{color:#03dac6}pre{background:#16213e;padding:15px;border-radius:8px;overflow-x:auto}</style></head><body>";

echo "<h1>🔍 Diagnostik Modal Tagihan Siswa</h1>";

$root = __DIR__ . '/..';

try {
    require_once "{$root}/vendor/autoload.php";
    $app = require_once "{$root}/bootstrap/app.php";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "<p class='ok'>✅ Laravel bootstrapped OK</p>";
} catch (\Exception $e) {
    echo "<p class='err'>❌ Bootstrap error: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>1. Cek Query unpaidBillsList</h2><pre>";

try {
    $unpaidBillsList = \App\Models\StudentBill::with(['student.classroom', 'paymentType', 'academicYear'])
        ->where('paid_amount', 0)
        ->orderBy('id', 'desc')
        ->take(10)
        ->get();
    
    echo "<span class='ok'>✅ Query berhasil. Total tagihan belum dibayar (sample 10): " . $unpaidBillsList->count() . "</span>\n\n";
    
    if ($unpaidBillsList->count() === 0) {
        echo "<span class='warn'>⚠️ PENYEBAB DITEMUKAN: Tidak ada tagihan dengan paid_amount = 0!</span>\n";
        echo "<span class='info'>ℹ️ Cek semua tagihan (tanpa filter paid_amount):</span>\n";
        
        $allBills = \App\Models\StudentBill::orderBy('id', 'desc')->take(5)->get();
        echo "   Total tagihan di DB (sample): " . $allBills->count() . "\n";
        foreach ($allBills as $bill) {
            echo "   - ID:{$bill->id}, amount:{$bill->amount}, paid_amount:{$bill->paid_amount}, student_id:{$bill->student_id}\n";
        }
        
        // Cek berapa total tagihan
        $totalBills = \App\Models\StudentBill::count();
        $totalUnpaid = \App\Models\StudentBill::where('paid_amount', 0)->count();
        $totalPartial = \App\Models\StudentBill::where('paid_amount', '>', 0)->where('paid_amount', '<', \Illuminate\Support\Facades\DB::raw('amount'))->count();
        $totalPaid = \App\Models\StudentBill::whereColumn('paid_amount', '>=', 'amount')->count();
        
        echo "\n<span class='info'>📊 Statistik Tagihan:</span>\n";
        echo "   Total semua tagihan: {$totalBills}\n";
        echo "   Belum dibayar (paid_amount=0): {$totalUnpaid}\n";
        echo "   Cicilan (paid_amount > 0 tapi < amount): {$totalPartial}\n";
        echo "   Lunas (paid_amount >= amount): {$totalPaid}\n";
    } else {
        echo "<span class='ok'>✅ Ada data tagihan belum dibayar. Berikut sample:</span>\n";
        foreach ($unpaidBillsList as $bill) {
            $studentName = $bill->student ? $bill->student->full_name : 'NULL STUDENT!';
            $className = ($bill->student && $bill->student->classroom) ? $bill->student->classroom->class_name : 'No Class';
            $paymentType = $bill->paymentType ? $bill->paymentType->type_name : 'NULL PT!';
            echo "   - ID:{$bill->id} | {$studentName} | {$className} | {$paymentType} | Rp " . number_format($bill->amount) . " | paid:" . number_format($bill->paid_amount) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "<span class='err'>❌ Query ERROR: " . $e->getMessage() . "</span>\n";
    echo "<span class='err'>File: " . $e->getFile() . ":" . $e->getLine() . "</span>\n";
}

echo "</pre>";

echo "<h2>2. Cek View Rendering</h2><pre>";
try {
    // Cek apakah ada error saat view di-render
    $viewPath = resource_path('views/admin/bills/index.blade.php');
    if (file_exists($viewPath)) {
        echo "<span class='ok'>✅ View file exists: admin/bills/index.blade.php</span>\n";
        echo "   File size: " . filesize($viewPath) . " bytes\n";
        echo "   Last modified: " . date('Y-m-d H:i:s', filemtime($viewPath)) . "\n";
    } else {
        echo "<span class='err'>❌ View file NOT FOUND!</span>\n";
    }
    
    // Cek apakah compiled view sudah di-clear
    $compiledDir = storage_path('framework/views');
    $compiledFiles = glob($compiledDir . '/*.php');
    echo "   Compiled views in cache: " . count($compiledFiles) . " files\n";
    
} catch (\Exception $e) {
    echo "<span class='err'>❌ Error: " . $e->getMessage() . "</span>\n";
}
echo "</pre>";

echo "<h2>3. Cek Apakah Modal HTML Ada di Rendered Output</h2><pre>";
try {
    // Ambil rendered HTML dari halaman /admin/bills (simulate)
    $url = url('/admin/bills');
    echo "<span class='info'>ℹ️ URL halaman: {$url}</span>\n";
    echo "<span class='info'>ℹ️ Untuk mengecek apakah modal ter-render, buka Developer Tools (F12) di browser</span>\n";
    echo "<span class='info'>   → Tab Console → Cek apakah ada error JavaScript merah</span>\n";
    echo "<span class='info'>   → Tab Elements → Search 'bulkUpdateModal' → apakah element HTML ada</span>\n\n";
    
    // Quick check: apakah ada error_log baru
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logSize = filesize($logPath);
        echo "   Laravel log size: " . number_format($logSize) . " bytes\n";
        // Ambil 20 baris terakhir log
        $lastLines = array_slice(file($logPath), -20);
        echo "\n<span class='warn'>📋 20 Baris Terakhir Laravel Log:</span>\n";
        foreach ($lastLines as $line) {
            $line = trim($line);
            if (strlen($line) > 200) $line = substr($line, 0, 200) . '...';
            if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
                echo "<span class='err'>{$line}</span>\n";
            } else {
                echo "{$line}\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "<span class='err'>❌ Error: " . $e->getMessage() . "</span>\n";
}
echo "</pre>";

echo "<h2>4. Test Render Modal Snippet</h2><pre>";
try {
    // Coba render Blade view secara partial untuk lihat apakah error
    $unpaidBillsList = \App\Models\StudentBill::with(['student.classroom', 'paymentType', 'academicYear'])
        ->where('paid_amount', 0)
        ->orderBy('id', 'desc')
        ->take(5)
        ->get();
    
    echo "<span class='info'>ℹ️ Mencoba render data modal secara manual...</span>\n\n";
    
    foreach ($unpaidBillsList as $i => $bill) {
        try {
            $name = $bill->student->full_name ?? 'N/A';
            $class = $bill->student->classroom->class_name ?? 'Tanpa Kelas';
            $pt = $bill->paymentType->type_name ?? '-';
            $month = $bill->month ? \Carbon\Carbon::create($bill->year, $bill->month, 1)->format('M Y') : "1 Kali ({$bill->year})";
            $amount = number_format($bill->amount, 0, ',', '.');
            echo "<span class='ok'>✅ Row {$i}: {$name} | {$class} | {$pt} | {$month} | Rp {$amount}</span>\n";
        } catch (\Exception $e) {
            echo "<span class='err'>❌ Row {$i} ERROR: " . $e->getMessage() . "</span>\n";
        }
    }
    
    if ($unpaidBillsList->count() === 0) {
        echo "<span class='warn'>⚠️ Tidak ada data untuk di-render! Modal akan menampilkan pesan kosong.</span>\n";
    }
} catch (\Exception $e) {
    echo "<span class='err'>❌ Render test error: " . $e->getMessage() . "</span>\n";
}
echo "</pre>";

echo "</body></html>";
