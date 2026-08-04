<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = \App\Models\User::where('username', 'yulzega')->first();
if($user) {
    echo "User school_id: " . $user->school_id . "\n";
    if ($user->teacher) {
        echo "Teacher school_id: " . $user->teacher->school_id . "\n";
        if ($user->teacher->employee) {
            echo "Employee school_id: " . $user->teacher->employee->school_id . "\n";
        }
    }
} else {
    echo "User not found\n";
}
