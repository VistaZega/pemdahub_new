<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/admin/payments/create', 'GET')
);

// We simulate a login by using the first superadmin user.
$user = \App\Models\User::where('role', 'superadmin')->first();
if ($user) {
    auth()->login($user);
} else {
    echo "No superadmin found\n";
    exit;
}

$studentsQuery = \App\Models\Student::orderBy('full_name');
if ($user && !$user->isSuperAdmin()) {
    $studentsQuery->where('school_id', $user->school_id);
}
$students = $studentsQuery->select('id', 'full_name', 'nisn', 'school_id')->get();
$hasBeatrix = false;
foreach ($students as $s) {
    if (str_contains(strtoupper($s->full_name), 'BEATRIX')) {
        $hasBeatrix = true;
        echo "Found Beatrix in students query: " . $s->full_name . " (School ID: " . $s->school_id . ")\n";
    }
}
if (!$hasBeatrix) {
    echo "Beatrix NOT found in students query!\n";
}

echo "Total students fetched: " . count($students) . "\n";
echo "SuperAdmin status: " . ($user->isSuperAdmin() ? 'Yes' : 'No') . "\n";
echo "User School ID: " . $user->school_id . "\n";
