<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$updated = DB::table('payments')
    ->where('notes', 'Pembayaran otomatis tersinkronisasi bersama SPP (Pemulihan)')
    ->update(['is_verified' => true]);

echo "Berhasil memverifikasi $updated transaksi Iuran OSIS agar masuk ke Laporan Konsolidasi Yayasan.";
