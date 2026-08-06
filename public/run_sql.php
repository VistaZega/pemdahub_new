<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    $users = \Illuminate\Support\Facades\DB::select("SELECT id, name, username, email, role, school_id FROM users WHERE name LIKE '%Yulianus%'");
    $teachers = \Illuminate\Support\Facades\DB::select("SELECT id, user_id, school_id, full_name, teacher_code FROM teachers WHERE full_name LIKE '%Yulianus%'");
    $employees = \Illuminate\Support\Facades\DB::select("SELECT id, user_id, school_id, full_name, employee_code FROM employees WHERE full_name LIKE '%Yulianus%'");

    echo json_encode([
        'users' => $users,
        'teachers' => $teachers,
        'employees' => $employees,
    ], JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
