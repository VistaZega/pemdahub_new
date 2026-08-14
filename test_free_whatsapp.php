<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WhatsAppService;

echo "=================================================\n";
echo "🧪 TESTING FREE SELF-HOSTED WHATSAPP ENGINE ($0)\n";
echo "=================================================\n\n";

$service = new WhatsAppService();

echo "Checking status from engine (http://localhost:3000/device)...\n";
$info = $service->getAccountInfo();
print_r($info);

echo "\n-------------------------------------------------\n";
echo "Testing sending test WhatsApp notification (Absensi / SPP)...";
$res = $service->sendMessage('081234567890', "📢 *TESTING NOTIFIKASI PEMBDAHUB ($0 COST)*\n\nHalo Wali Murid, pengiriman otomatis Absensi & SPP dari PembdaHUB tanpa Fonnte telah AKTIF!");

print_r($res);
echo "\n=================================================\n";
