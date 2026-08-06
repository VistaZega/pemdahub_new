<?php
header('Content-Type: text/plain; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$yayasan = App\Models\User::where('role', 'ketua_yayasan')->orWhere('username', 'yulzega')->first();
if (!$yayasan) {
    die("Yayasan user not found");
}

echo "Yayasan User ID: {$yayasan->id} - {$yayasan->name}\n";

$teachers = App\Models\Teacher::where('user_id', $yayasan->id)->get();
echo "Total Teacher records: " . $teachers->count() . "\n";
foreach ($teachers as $t) {
    echo "- Teacher ID: {$t->id}, School ID: {$t->school_id}\n";
}
