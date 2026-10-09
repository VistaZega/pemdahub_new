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
        $activePkl = null;
        if ($student) {
            $todayAttendance = Attendance::where('student_id', $student->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();

            $todayDate = now()->format('Y-m-d');
            $activePkl = \App\Models\PklPlacement::activeOnDate($todayDate)
                ->with('dudi')
                ->where('student_id', $student->id)
                ->first();
        }

        return view('mobile.absensi.index', compact('student', 'attendances', 'todayAttendance', 'activePkl'));
    }

    public function scan(Request $request)
    {
        $user = Auth::user();
        $activeRole = session('active_role', $user->role);
        $wantsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->isJson();

        $isGuruOrPegawai = $user->isGuru() || $user->isPegawai() || $user->isKepalaSekolah() || $activeRole === 'guru' || $activeRole === 'pegawai';

        // 1. CEK APAPUN ROLE ATAU MODEL GURU / PEGAWAI
        if ($isGuruOrPegawai) {
            $today = date('Y-m-d');
            $currentTime = date('H:i:s');
            $deviceId = $request->input('device_id') ?: ('WEB-GPS-' . $user->id);

            // Geofencing Check jika ada koordinat sekolah & lokasi dikirim
            $lat = (float) $request->input('latitude', 0);
            $lng = (float) $request->input('longitude', 0);

            if ($lat == 0.0 && $lng == 0.0) {
                $msg = 'Gagal! Lokasi GPS tidak ditemukan atau belum aktif. Harap izinkan akses lokasi (GPS) pada browser/perangkat Anda.';
                return $wantsJson
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->with('error', $msg);
            }

            // Selesaikan relasi Employee dan Teacher secara aman (tidak pernah mengambil data pegawai milik orang lain)
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();

            if (!$employee && $teacher && $teacher->employee_id) {
                $employee = \App\Models\Employee::find($teacher->employee_id);
                if ($employee && !$employee->user_id) {
                    $employee->update(['user_id' => $user->id]);
                }
            }

            if ($employee && !$teacher) {
                $teacher = \App\Models\Teacher::where('employee_id', $employee->id)->first();
                if ($teacher && !$teacher->user_id) {
                    $teacher->update(['user_id' => $user->id]);
                }
            }

            if (!$employee) {
                $isGuru = $user->isGuru() || $activeRole === 'guru';
                $employee = \App\Models\Employee::create([
                    'school_id' => $user->school_id ?? 1,
                    'user_id' => $user->id,
                    'employee_code' => $teacher?->teacher_code ?? ('PGW-' . $user->id),
                    'full_name' => $teacher?->full_name ?? $user->name,
                    'gender' => $teacher?->gender ?? 'L',
                    'birth_place' => $teacher?->birth_place ?? '-',
                    'employee_type' => $isGuru ? 'guru' : 'staff_tu',
                    'employment_status' => 'yayasan',
                    'tmt_date' => now()->format('Y-m-d'),
                    'is_active' => true,
                ]);
            }

            if ($employee && !$teacher && ($user->isGuru() || $activeRole === 'guru')) {
                $teacher = \App\Models\Teacher::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_id' => $employee->id,
                        'school_id' => $employee->school_id ?? $user->school_id ?? 1,
                        'teacher_code' => $employee->employee_code ?? ('PGW-' . $user->id),
                        'full_name' => $employee->full_name ?? $user->name,
                        'gender' => $employee->gender ?? 'L',
                        'birth_place' => $employee->birth_place ?? '-',
                        'is_active' => true,
                    ]
                );
            }

            // Dapatkan Radius Geofencing (Toleransi Kompleks Perguruan Pembda, default 175 meter)
            $maxRadiusMeters = (int) \App\Models\Setting::getValue('attendance_max_radius', 175);
            if ($maxRadiusMeters <= 0) {
                $maxRadiusMeters = 175;
            }

            // Kumpulkan seluruh titik koordinat unit sekolah dalam Kompleks Perguruan Pembda
            $targetLocations = [];

            $primarySchool = $employee ? $employee->school : ($user->school ?? null);
            $schoolTargetName = $primarySchool?->name ?? 'Kompleks Perguruan Pembda';

            if ($primarySchool && (float)$primarySchool->latitude != 0.0 && (float)$primarySchool->longitude != 0.0) {
                $targetLocations[] = [
                    'name' => $primarySchool->name,
                    'lat' => (float)$primarySchool->latitude,
                    'lng' => (float)$primarySchool->longitude,
                ];
            }

            $allSchools = \App\Models\School::where('is_active', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->get();
            foreach ($allSchools as $sch) {
                $targetLocations[] = [
                    'name' => $sch->name,
                    'lat' => (float)$sch->latitude,
                    'lng' => (float)$sch->longitude,
                ];
            }

            $globalLat = (float) \App\Models\Setting::getValue('school_latitude', 1.28127778);
            $globalLng = (float) \App\Models\Setting::getValue('school_longitude', 97.62566667);
            $targetLocations[] = [
                'name' => 'Kampus Perguruan Pembda',
                'lat' => $globalLat,
                'lng' => $globalLng,
            ];

            // Cari jarak terdekat ke salah satu titik fasilitas Kompleks Pembda
            $minDistance = null;
            $closestTarget = null;
            foreach ($targetLocations as $loc) {
                $dist = $this->calculateDistance($lat, $lng, $loc['lat'], $loc['lng']);
                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                    $closestTarget = $loc;
                }
            }

            $formattedDist = number_format($minDistance, 0, ',', '.');

            if ($minDistance > $maxRadiusMeters) {
                $msg = "⛔ Presensi Ditolak! Lokasi Anda berada di luar area sekolah.\n\n📍 Jarak Terdeteksi: {$formattedDist} meter dari {$schoolTargetName} (Batas Maksimal: {$maxRadiusMeters} meter)\n📌 Koordinat Anda: {$lat}, {$lng}\n\n💡 Petunjuk: Pastikan Anda berada dalam lingkungan sekolah (di teras, lapangan, atau di luar ruangan) dan fitur Akurasi Lokasi HP aktif agar GPS dapat mengunci posisi Anda secara presisi.\n\n📌 Solusi Alternatif: Bila tetap gagal juga, silakan lakukan presensi melalui Scan Kartu RFID / QR Code di pos gerbang atau konfirmasi presensi manual melalui Wali Kelas / Guru Piket.";
                return $wantsJson
                    ? response()->json(['success' => false, 'message' => $msg, 'distance' => round($minDistance), 'max_radius' => $maxRadiusMeters], 403)
                    : back()->with('error', $msg);
            }

            // Tentukan status kehadiran (Hadir Mengajar vs Tugas Khusus)
            $isTeacher = ($user->isGuru() || $activeRole === 'guru' || ($employee && $employee->isTeacher()));
            $hasScheduleToday = false;
            $notes = null;

            if ($isTeacher) {
                $dayOfWeekString = strtolower(now()->format('l'));
                if ($teacher) {
                    $hasScheduleToday = \App\Models\Schedule::where('teacher_id', $teacher->id)
                        ->where('day_of_week', $dayOfWeekString)
                        ->exists();
                }
                if (!$hasScheduleToday || date('m-d') === '08-17') {
                    $notes = 'tugas_khusus';
                }
            } else {
                $isWeekend = in_array(now()->format('D'), ['Sat', 'Sun']);
                if ($isWeekend || date('m-d') === '08-17') {
                    $notes = 'tugas_khusus';
                }
            }

            // Cari / Buat Presensi Guru Hari Ini
            $attendance = \App\Models\EmployeeAttendance::firstOrCreate(
                ['employee_id' => $employee->id, 'date' => $today],
                [
                    'school_id'    => $employee->school_id ?? $user->school_id ?? 1,
                    'time_in'      => $currentTime,
                    'status'       => 'hadir',
                    'notes'        => $notes,
                    'recorded_via' => 'gps',
                    'device_id'    => $deviceId,
                    'recorded_by'  => $user->id,
                ]
            );

            $isMerdekaDay = (date('m-d') === '08-17');

            if ($attendance->wasRecentlyCreated) {
                // Log Reputasi untuk Tugas Khusus / Upacara
                if ($notes === 'tugas_khusus' && $user->id) {
                    \App\Models\ReputationLog::log(
                        $user->id,
                        15,
                        'attendance',
                        $isMerdekaDay ? 'Upacara Hari Kemerdekaan RI (Tugas Khusus)' : 'Tugas Khusus: Kehadiran di luar jadwal mengajar',
                        $attendance
                    );
                }

                if ($isMerdekaDay) {
                    $msg = '🇮🇩 DIRGAHAYU REPUBLIK INDONESIA! Merdeka! ✊ Presensi Masuk Upacara berhasil dicatat jam ' . date('H:i', strtotime($currentTime)) . " WIB (Jarak GPS: {$formattedDist} m dari sekolah).";
                } else {
                    $msg = '📍 Presensi Masuk berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . " WIB (Jarak: {$formattedDist} m dari titik sekolah).";
                }
                return $wantsJson
                    ? response()->json(['success' => true, 'message' => $msg, 'distance' => round($minDistance)])
                    : back()->with('success', $msg);
            }

            // Jika tap lagi untuk checkout (presensi pulang)
            $isNotCheckedOut = !$attendance->time_out || $attendance->time_out === '00:00:00' || $attendance->time_out === '00:00';
            if ($attendance->time_in && $isNotCheckedOut) {
                // Anti-spam cooldown: minimal 5 menit dari presensi masuk baru boleh presensi pulang
                $lastScan = \Carbon\Carbon::parse($today . ' ' . $attendance->time_in);
                $diffSeconds = now('Asia/Jakarta')->timestamp - $lastScan->timestamp;
                $cooldown = config('services.kiosk.cooldown_seconds', 300);
                if ($diffSeconds >= 0 && $diffSeconds < $cooldown) {
                    $timeInFormatted = date('H:i', strtotime($attendance->time_in));
                    $msg = "Anda sudah presensi masuk pada jam {$timeInFormatted} WIB. Presensi pulang dapat dilakukan nanti saat jam pulang.";
                    return $wantsJson
                        ? response()->json(['success' => false, 'message' => $msg])
                        : back()->with('info', $msg);
                }

                $attendance->update(['time_out' => $currentTime]);
                if ($isMerdekaDay) {
                    $msg = '🇮🇩 DIRGAHAYU REPUBLIK INDONESIA! Merdeka! ✊ Presensi Pulang Upacara berhasil dicatat jam ' . date('H:i', strtotime($currentTime)) . " WIB (Jarak GPS: {$formattedDist} m).";
                } else {
                    $msg = '📍 Presensi Pulang berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . " WIB (Jarak: {$formattedDist} m).";
                }
                return $wantsJson
                    ? response()->json(['success' => true, 'message' => $msg, 'distance' => round($minDistance)])
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
