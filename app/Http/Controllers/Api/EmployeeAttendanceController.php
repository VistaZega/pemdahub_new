<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Http\Request;

class EmployeeAttendanceController extends Controller
{
    /**
     * Handle RFID scan for employee attendance.
     * First scan of the day = time_in, subsequent scan = time_out.
     */
    public function handleRfidScan(Request $request)
    {
        // Authenticate via API key
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

        // Tulis UID ke scan-buffer agar browser (modal registrasi RFID) bisa mengambilnya
        $bufferFile = storage_path('app/rfid_scan_buffer.json');
        file_put_contents($bufferFile, json_encode(['uid' => $uid, 'time' => time()]));

        // Find employee by RFID UID
        $employee = Employee::where('rfid_uid', $uid)->where('is_active', true)->first();
        if (!$employee) {
            $tefaEmployee = \App\Models\TefaEmployee::where('rfid_uid', $uid)->where('is_active', true)->first();
            if ($tefaEmployee) {
                $today = now()->toDateString();
                $currentTime = now()->format('H:i:s');
                $attendance = \App\Models\TefaAttendance::where('tefa_employee_id', $tefaEmployee->id)
                    ->where('date', $today)
                    ->first();

                if (!$attendance) {
                    $isWeekend = in_array(now()->format('D'), ['Sun']); // Waktu kerja Senin s.d Sabtu
                    $status = 'hadir';
                    $notes = 'Tepat Waktu';

                    if ($isWeekend) {
                        $notes = 'Lembur (Minggu)';
                    } else {
                        if ($currentTime > '08:00:00') {
                            $status = 'terlambat';
                            $notes = 'Terlambat Masuk';
                        }
                    }

                    \App\Models\TefaAttendance::create([
                        'tefa_employee_id' => $tefaEmployee->id,
                        'date' => $today,
                        'time_in' => $currentTime,
                        'status' => $status,
                        'notes' => $notes,
                        'recorded_via' => 'rfid',
                        'device_id' => $request->input('device_id', 'KIOSK-EMP'),
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'action' => 'masuk',
                        'message' => "Selamat datang, {$tefaEmployee->name}! (" . ($status === 'terlambat' ? 'Terlambat' : 'Tepat Waktu') . ")",
                        'employee_name' => $tefaEmployee->name,
                        'time' => $currentTime,
                    ]);
                }

                $attendance->update(['time_out' => $currentTime]);

                return response()->json([
                    'status' => 'success',
                    'action' => 'pulang',
                    'message' => "Sampai jumpa, {$tefaEmployee->name}!",
                    'employee_name' => $tefaEmployee->name,
                    'time_in' => $attendance->time_in,
                    'time_out' => $currentTime,
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Kartu tidak terdaftar',
                'uid' => $uid,
            ], 404);
        }

        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        // Check existing attendance
        $attendance = EmployeeAttendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            // Cek apakah hari ini merupakan hari mengajar terjadwal bagi Guru, atau hari tambahan untuk Staf
            $isTeacher = $employee->isTeacher();
            $hasScheduleToday = false;
            $notes = null;

            if ($isTeacher) {
                $dayOfWeekString = strtolower(now()->format('l'));
                if ($employee->teacher) {
                    $hasScheduleToday = \App\Models\Schedule::where('teacher_id', $employee->teacher->id)
                        ->where('day_of_week', $dayOfWeekString)
                        ->exists();
                }
                if (!$hasScheduleToday) {
                    $notes = 'tugas_khusus';
                }
            } else {
                // Staf/Pegawai biasa: wajib hadir Senin s.d. Jumat
                // Jika hadir Sabtu atau Minggu, dianggap Tugas Khusus (jam tambahan)
                $isWeekend = in_array(now()->format('D'), ['Sat', 'Sun']);
                if ($isWeekend) {
                    $notes = 'tugas_khusus';
                }
            }

            $isQrScan = ($request->input('type') === 'qr')
                || ($employee && $employee->school && strtoupper($employee->school->type) === 'SMP')
                || ($employee && ($rawUid === $employee->employee_code || $rawUid === $employee->nip));

            // First scan: create with time_in
            $attendance = EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'school_id' => $employee->school_id,
                'date' => $today,
                'time_in' => $currentTime,
                'status' => 'hadir',
                'notes' => $notes,
                'recorded_via' => $isQrScan ? 'qr' : 'rfid',
                'device_id' => $request->input('device_id'),
            ]);

            $message = "Selamat datang, {$employee->full_name}!";
            if ($isTeacher) {
                if (!$hasScheduleToday) {
                    $message = "Selamat datang, {$employee->full_name}! (Tugas Khusus)";
                    // Berikan 15 point reputasi jika hadir di luar jadwal mengajar
                    if ($employee->user_id) {
                        \App\Models\ReputationLog::log(
                            $employee->user_id,
                            15,
                            'attendance',
                            'Tugas Khusus: Kehadiran di luar jadwal mengajar',
                            $attendance
                        );
                    }
                } else {
                    $message = "Selamat datang, {$employee->full_name}! (Hadir Mengajar)";
                }
            } else {
                $isWeekend = in_array(now()->format('D'), ['Sat', 'Sun']);
                if ($isWeekend) {
                    $message = "Selamat datang, {$employee->full_name}! (Tugas Khusus)";
                    // Berikan 15 point reputasi jika hadir di luar hari kerja (Senin-Jumat)
                    if ($employee->user_id) {
                        \App\Models\ReputationLog::log(
                            $employee->user_id,
                            15,
                            'attendance',
                            'Tugas Khusus: Jam tambahan di luar hari masuk',
                            $attendance
                        );
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'action' => 'masuk',
                'message' => $message,
                'employee_name' => $employee->full_name,
                'time' => $currentTime,
            ]);
        }

        // Subsequent scan: update time_out
        $attendance->update([
            'time_out' => $currentTime,
        ]);

        return response()->json([
            'status' => 'success',
            'action' => 'pulang',
            'message' => "Sampai jumpa, {$employee->full_name}!",
            'employee_name' => $employee->full_name,
            'time_in' => $attendance->time_in,
            'time_out' => $currentTime,
        ]);
    }
}

