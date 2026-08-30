<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



// ==== ROUTING ABSENSI KIOSK (RFID) ====
// Tidak dijaga auth sanctum, melainkan pakai Manual Secret Key di controllernya.
Route::post('/attendance/rfid-scan', [\App\Http\Controllers\Api\AttendanceController::class, 'handleRfidScan']);

// ==== ROUTING ABSENSI PEGAWAI KIOSK (RFID) ====
Route::post('/attendance/employee-rfid-scan', [\App\Http\Controllers\Api\EmployeeAttendanceController::class, 'handleRfidScan']);

// ==== SCAN BUFFER (untuk registrasi kartu RFID via browser) ====
Route::post('/rfid/scan-buffer', function (Request $request) {
    $apiKey = $request->header('X-Kiosk-API-Key') ?? $request->input('api_key');
    if ($apiKey !== config('services.kiosk.api_key', 'RAHASIA-PEMBDAHUB-12345')) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }
    $uid = strtoupper(trim($request->input('uid', '')));
    if (empty($uid)) {
        return response()->json(['status' => 'error', 'message' => 'UID kosong'], 400);
    }
    // --- HACK UNTUK STATION LAMA (BACKWARD COMPATIBILITY) ---
    if (preg_match('/^\d+$/', $uid) && strlen($uid) >= 6 && strlen($uid) <= 12) {
        $num = (int)$uid;
        if ($num > 0 && $num <= 4294967295) {
            $hexUid = strtoupper(dechex($num));
            $uid = str_pad($hexUid, 8, '0', STR_PAD_LEFT);
        }
    }
    
    $bufferFile = storage_path('app/rfid_scan_buffer.json');
    file_put_contents($bufferFile, json_encode(['uid' => $uid, 'time' => time()]));
    return response()->json(['status' => 'success', 'uid' => $uid]);
});

// ==== CEK KEPEMILIKAN UID (untuk validasi di modal RFID sebelum simpan) ====
// Parameter opsional: exclude_type (student|employee|tefa_employee) + exclude_id
// Jika UID dimiliki entitas yang di-exclude → dianggap "self" (bukan milik orang lain)
Route::get('/rfid/check-uid', function (Request $request) {
    $uid = strtoupper(trim($request->input('uid', '')));
    if (empty($uid)) {
        return response()->json(['owned' => false, 'is_self' => false]);
    }
    // --- HACK UNTUK STATION LAMA (BACKWARD COMPATIBILITY) ---
    if (preg_match('/^\d+$/', $uid) && strlen($uid) >= 6 && strlen($uid) <= 12) {
        $num = (int)$uid;
        if ($num > 0 && $num <= 4294967295) {
            $hexUid = strtoupper(dechex($num));
            $uid = str_pad($hexUid, 8, '0', STR_PAD_LEFT);
        }
    }

    $excludeType = $request->input('exclude_type', '');
    $excludeId = $request->input('exclude_id', '');

    // Cek di Students
    $student = \App\Models\Student::where('rfid_uid', $uid)->first();
    if ($student) {
        // Cek apakah ini entitas yang sama (self)
        if ($excludeType === 'student' && $excludeId == $student->id) {
            return response()->json(['owned' => false, 'is_self' => true]);
        }
        return response()->json([
            'owned' => true,
            'owner_name' => $student->full_name,
            'owner_type' => 'Siswa',
            'owner_id' => $student->id,
        ]);
    }

    // Cek di Employees (Guru/Pegawai)
    $employee = \App\Models\Employee::where('rfid_uid', $uid)->first();
    if ($employee) {
        if ($excludeType === 'employee' && $excludeId == $employee->id) {
            return response()->json(['owned' => false, 'is_self' => true]);
        }
        $type = $employee->isTeacher() ? 'Guru' : 'Pegawai';
        return response()->json([
            'owned' => true,
            'owner_name' => $employee->full_name,
            'owner_type' => $type,
            'owner_id' => $employee->id,
        ]);
    }

    // Cek di TefaEmployees
    $tefaEmployee = \App\Models\TefaEmployee::where('rfid_uid', $uid)->first();
    if ($tefaEmployee) {
        if ($excludeType === 'tefa_employee' && $excludeId == $tefaEmployee->id) {
            return response()->json(['owned' => false, 'is_self' => true]);
        }
        return response()->json([
            'owned' => true,
            'owner_name' => $tefaEmployee->name,
            'owner_type' => 'Karyawan TEFA',
            'owner_id' => $tefaEmployee->id,
        ]);
    }

    return response()->json(['owned' => false, 'is_self' => false]);
});

Route::get('/rfid/scan-buffer', function (Request $request) {
    $bufferFile = storage_path('app/rfid_scan_buffer.json');
    if (!file_exists($bufferFile)) {
        return response()->json(['uid' => null]);
    }
    $data = json_decode(file_get_contents($bufferFile), true);
    // Hapus file setelah dibaca (one-time read)
    @unlink($bufferFile);
    // Abaikan jika data lebih dari 60 detik
    if (isset($data['time']) && (time() - $data['time']) > 60) {
        return response()->json(['uid' => null]);
    }
    return response()->json(['uid' => $data['uid'] ?? null]);
});

// ==== ROUTING RUNNING TEXT (LED DISPLAY / ESP32 / HD-WF2) ====
Route::get('/running-text/live', [\App\Http\Controllers\Api\RunningTextController::class, 'getLiveFeed']);
Route::get('/running-text/live/raw', [\App\Http\Controllers\Api\RunningTextController::class, 'getLiveFeed']);
Route::get('/running-text/alumni', [\App\Http\Controllers\Api\RunningTextController::class, 'getAlumniMessages']);
Route::get('/running-text/alumni/raw', [\App\Http\Controllers\Api\RunningTextController::class, 'getAlumniMessages']);

// ==== PEMBDA SPACE MOBILE API V1 ====
Route::prefix('v1')->group(function () {
    if (file_exists(__DIR__ . '/api_v1.php')) {
        require __DIR__ . '/api_v1.php';
    }
});



