<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileAbsensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeRole = session('active_role', $user->role);

        if ($activeRole === 'guru' || $user->isGuru()) {
            return redirect()->route('mobile.guru.absensi.input');
        }

        $student = Student::where('user_id', $user->id)->first();
        $attendances = collect();

        if ($student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->orderBy('date', 'desc')
                ->take(30)
                ->get();
        }

        $todayAttendance = null;
        if ($student) {
            $todayAttendance = Attendance::where('student_id', $student->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();
        }

        return view('mobile.absensi.index', compact('student', 'attendances', 'todayAttendance'));
    }

    public function scan(Request $request)
    {
        $user = Auth::user();
        $activeRole = session('active_role', $user->role);
        $wantsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->isJson();

        // 1. CEK APAPUN ROLE ATAU MODEL GURU / PEGAWAI
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->school_id && ($user->isGuru() || $user->isPegawai() || $activeRole === 'guru')) {
            $employee = \App\Models\Employee::where('school_id', $user->school_id)->first();
        }

        if ($employee || $activeRole === 'guru' || $user->isGuru()) {
            $today = date('Y-m-d');
            $currentTime = date('H:i:s');
            $deviceId = $request->input('device_id') ?: ('WEB-GPS-' . $user->id);

            // Geofencing Check jika ada koordinat sekolah & lokasi dikirim
            $lat = (float) $request->input('latitude', 0);
            $lng = (float) $request->input('longitude', 0);

            if ($lat == 0.0 && $lng == 0.0) {
                $msg = 'Gagal! Lokasi GPS tidak ditemukan atau belum aktif.';
                return $wantsJson
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->with('error', $msg);
            }

            $school = $employee ? $employee->school : ($user->school ?? null);
            $schoolLat = (float) ($school->latitude ?? 0);
            $schoolLong = (float) ($school->longitude ?? 0);
            if ($schoolLat == 0.0 || $schoolLong == 0.0) {
                $schoolLat = (float) \App\Models\Setting::getValue('school_latitude', 1.282500);
                $schoolLong = (float) \App\Models\Setting::getValue('school_longitude', 97.619000);
            }

            $maxRadiusMeters = (int) \App\Models\Setting::getValue('attendance_max_radius', 150);
            if ($maxRadiusMeters <= 0) {
                $maxRadiusMeters = 150;
            }

            $distance = $this->calculateDistance($lat, $lng, $schoolLat, $schoolLong);
            if ($distance > $maxRadiusMeters) {
                $formattedDist = number_format($distance, 0, ',', '.');
                $msg = "Gagal! Lokasi Anda berada di luar area sekolah ({$formattedDist} meter dari sekolah. Maksimal {$maxRadiusMeters} meter).";
                return $wantsJson
                    ? response()->json(['success' => false, 'message' => $msg], 403)
                    : back()->with('error', $msg);
            }

            if (!$employee) {
                $msg = 'Data Pegawai/Guru tidak ditemukan di sistem. Harap hubungi Admin.';
                return $wantsJson
                    ? response()->json(['success' => false, 'message' => $msg], 404)
                    : back()->with('error', $msg);
            }

            // Cari / Buat Presensi Guru Hari Ini
            $attendance = \App\Models\EmployeeAttendance::firstOrCreate(
                ['employee_id' => $employee->id, 'date' => $today],
                [
                    'school_id'    => $employee->school_id,
                    'time_in'      => $currentTime,
                    'status'       => 'hadir',
                    'recorded_via' => 'gps',
                    'device_id'    => $deviceId,
                    'recorded_by'  => $user->id,
                ]
            );

            $isMerdekaDay = (date('m-d') === '08-17');

            if ($attendance->wasRecentlyCreated) {
                if ($isMerdekaDay) {
                    $msg = '🇮🇩 DIRGAHAYU REPUBLIK INDONESIA! Merdeka! ✊ Selamat Hari Kemerdekaan RI! Presensi GPS Masuk Guru berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . '. Tetap semangat mencerdaskan bangsa! 🇮🇩✨';
                } else {
                    $msg = '📍 Presensi GPS Masuk Guru berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . '!';
                }
                return $wantsJson
                    ? response()->json(['success' => true, 'message' => $msg])
                    : back()->with('success', $msg);
            }

            // Jika tap lagi untuk checkout (presensi pulang)
            $isNotCheckedOut = !$attendance->time_out || $attendance->time_out === '00:00:00' || $attendance->time_out === '00:00';
            if ($attendance->time_in && $isNotCheckedOut) {
                $attendance->update(['time_out' => $currentTime]);
                if ($isMerdekaDay) {
                    $msg = '🇮🇩 DIRGAHAYU REPUBLIK INDONESIA! Merdeka! ✊ Presensi GPS Pulang Guru berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . '. Selamat memperingati Hari Kemerdekaan RI!';
                } else {
                    $msg = '📍 Presensi GPS Pulang Guru berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . '!';
                }
                return $wantsJson
                    ? response()->json(['success' => true, 'message' => $msg])
                    : back()->with('success', $msg);
            }

            $msg = 'Anda sudah melakukan presensi masuk dan pulang hari ini.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $msg])
                : back()->with('info', $msg);
        }

        // 2. CEK APABILA SISWA
        if (!$request->has('device_id')) {
            $request->merge(['device_id' => 'WEB-GPS-' . $user->id]);
        }

        $apiController = app(\App\Http\Controllers\Api\AttendanceController::class);
        $res = $apiController->handleGpsScan($request);

        if (!$wantsJson && $res instanceof \Illuminate\Http\JsonResponse) {
            $data = $res->getData(true);
            if (!empty($data['success'])) {
                return back()->with('success', $data['message'] ?? 'Absensi berhasil!');
            } else {
                return back()->with('error', $data['message'] ?? 'Gagal melakukan absensi.');
            }
        }

        return $res;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        return $earthRadius * $c;
    }
}
