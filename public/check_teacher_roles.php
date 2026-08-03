<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$names = [
    'SOZARO HAREFA',
    'MOLIRHATI TELAUMBANUA',
    'HERDIYANA LAHAGU',
    'HERDIYANI LAHAGU',
];

echo "=== INVESTIGASI LENGKAP ===\n\n";

foreach ($names as $name) {
    echo "--- $name ---\n";
    
    // Cari SEMUA user dengan nama mirip
    $users = DB::table('users')->where('name', 'like', "%$name%")->get();
    echo "Total User Accounts: " . $users->count() . "\n";
    foreach ($users as $u) {
        echo "  User ID={$u->id} | Email={$u->email} | Role={$u->role} | School={$u->school_id}\n";
    }
    
    // Cari SEMUA teacher records dengan nama mirip
    $teachers = DB::table('teachers')->where('full_name', 'like', "%$name%")->get();
    echo "Total Teacher Records: " . $teachers->count() . "\n";
    foreach ($teachers as $t) {
        echo "  Teacher ID={$t->id} | Name={$t->full_name} | User ID={$t->user_id} | School={$t->school_id} | Active={$t->is_active} | Position=" . ($t->position ?? '-') . "\n";
    }
    
    echo "\n";
}
