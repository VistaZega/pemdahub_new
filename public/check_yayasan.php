<?php
header('Content-Type: text/plain; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verifikasi Multi-School Teacher ===\n\n";

// 1. Check if teacher_schools table exists
$exists = Illuminate\Support\Facades\Schema::hasTable('teacher_schools');
echo "1. Tabel teacher_schools: " . ($exists ? "ADA ✅" : "BELUM ADA ❌") . "\n\n";

// 2. Check Yayasan user
$yayasan = App\Models\User::where('username', 'yulzega')->first();
if ($yayasan) {
    echo "2. User Yayasan: ID {$yayasan->id} - {$yayasan->name} (Role: {$yayasan->role})\n";
    echo "   hasMultiSchoolAccess: " . ($yayasan->hasMultiSchoolAccess() ? "YA ✅" : "TIDAK ❌") . "\n";
    echo "   getActiveSchoolId: " . ($yayasan->getActiveSchoolId() ?? 'NULL') . "\n";
    
    $availableSchools = $yayasan->getAvailableSchools();
    echo "   Sekolah tersedia: " . $availableSchools->count() . "\n";
    foreach ($availableSchools as $s) {
        echo "   - ID: {$s->id} | {$s->name}\n";
    }
    
    // Check teacher record
    $teacher = App\Models\Teacher::where('user_id', $yayasan->id)->first();
    if ($teacher) {
        echo "\n3. Teacher record: ID {$teacher->id} | School ID: {$teacher->school_id} | {$teacher->full_name}\n";
    } else {
        echo "\n3. Teacher record: TIDAK ADA (akan dibuat saat switch ke mode guru)\n";
    }
} else {
    echo "2. User Yayasan: TIDAK DITEMUKAN ❌\n";
}

// 3. Check switch-school route
echo "\n4. Route 'switch-school': ";
try {
    $url = route('switch-school');
    echo "TERDAFTAR ✅ ({$url})\n";
} catch (\Exception $e) {
    echo "TIDAK DITEMUKAN ❌\n";
}
