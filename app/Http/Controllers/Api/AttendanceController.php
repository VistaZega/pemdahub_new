<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Endpoint untuk Hardware Kiosk ESP32 (RFID)
     * POST /api/attendance/rfid-scan
     */
    public function handleRfidScan(Request $request)
    {
        try {
            // Debug: Log the attempt
            \Illuminate\Support\Facades\Log::info('Kiosk Scan Attempt', [
                'ip' => $request->ip(),
                'uid' => $request->uid,
                'type' => $request->type, // 'rfid' atau 'qr' jika dikirim oleh alat
                'api_key_header' => $request->header('X-Kiosk-API-Key'),
                'user_agent' => $request->header('User-Agent')
            ]);

            // 1. Keamanan Sederhana
            $apiKey = $request->header('X-Kiosk-API-Key') ?? $request->input('api_key');
            if ($apiKey !== config('services.kiosk.api_key', 'RAHASIA-PEMBDAHUB-12345')) {
                return response()->json(['status' => 'error', 'message' => 'API Key invalid'], 401);
            }

            $request->validate(['uid' => 'required|string']);
            $uid = strtoupper(trim($request->uid));
            
            // --- HACK UNTUK STATION LAMA (BACKWARD COMPATIBILITY) ---
            // Jika UID dari hardware lama masih berupa angka desimal murni (misal: 50012561)
            // Kita ubah otomatis ke format Hex USB Scanner (misal: 02FB2191)
            if (preg_match('/^\d+$/', $uid) && strlen($uid) >= 6 && strlen($uid) <= 12) {
                $num = (int)$uid;
                if ($num > 0 && $num <= 4294967295) {
                    $hexUid = strtoupper(dechex($num));
                    $uid = str_pad($hexUid, 8, '0', STR_PAD_LEFT);
                }
            }

            $type = $request->input('type'); // Opsional: 'rfid' atau 'qr'

            // Tulis UID ke scan-buffer agar browser (modal registrasi RFID) bisa mengambilnya
            $bufferFile = storage_path('app/rfid_scan_buffer.json');
            file_put_contents($bufferFile, json_encode(['uid' => strtoupper($uid), 'time' => time()]));
            
            $today = now()->format('Y-m-d');
            $currentTime = now()->format('H:i:s');

            $student = null;
            $employee = null;
            $tefaEmployee = null;

            // 2. IDENTIFIKASI (RFID vs QR untuk Siswa/Guru)
            // Jika tipe adalah rfid, atau tidak dikirim (kita asumsikan coba cari rfid dulu)
            if (!$type || $type === 'rfid') {
                // Cari di Siswa berdasarkan RFID
                $student = \App\Models\Student::where('rfid_uid', $uid)
                    ->whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
                    ->first();

                // Jika tidak ketemu di Siswa, cari di Pegawai/Guru berdasarkan RFID
                if (!$student) {
                    $employee = \App\Models\Employee::where('rfid_uid', $uid)
                        ->where('is_active', true)
                        ->first();
                }

                // Jika tidak ketemu di Pegawai, cari di Karyawan TEFA berdasarkan RFID
                if (!$student && !$employee) {
                    $tefaEmployee = \App\Models\TefaEmployee::where('rfid_uid', $uid)
                        ->where('is_active', true)
                        ->first();
                }
            }

            // Jika masih belum ketemu (mungkin input QR Code berisi NIS/NIP) atau jika tipe eksplisit 'qr'
            if (!$student && !$employee) {
                // Cari di Siswa berdasarkan NIS atau NISN (QR Code Kertas)
                $student = \App\Models\Student::where(function($q) use ($uid) {
                        $q->where('nis', $uid)->orWhere('nisn', $uid);
                    })
                    ->whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
                    ->first();

                // Jika tidak ketemu di Siswa, cari di Pegawai/Guru berdasarkan NIP atau Kode Pegawai
                if (!$student) {
                    $employee = \App\Models\Employee::where(function($q) use ($uid) {
                            $q->where('nip', $uid)->orWhere('employee_code', $uid);
                        })
                        ->where('is_active', true)
                        ->first();
                }
            }

            // 3. JIKA TIDAK DITEMUKAN → Kartu Baru, simpan di scan-buffer
            if (!$student && !$employee && !$tefaEmployee) {
                return response()->json([
                    'status' => 'info',
                    'nama' => 'KARTU BARU',
                    'kelas' => 'UID: ' . strtoupper($uid),
                    'message' => 'Daftarkan di Admin',
                    'action_code' => 'NEW_CARD',
                    'uid' => strtoupper($uid),
                    'waktu' => date('H:i'),
                ], 200);
            }

            // 4. ABSENSI SISWA
            if ($student) {
                // Cari kelas aktif siswa (ambil yang paling baru, tidak terikat academic year aktif)
                $studentClass = $student->studentClasses()
                    ->where('status', 'aktif')
                    ->latest('id')
                    ->first();

                if (!$studentClass) {
                    return response()->json([
                        'status' => 'error', 'nama' => substr($student->full_name, 0, 16),
                        'message' => 'Siswa tdk di kelas'
                    ], 200);
                }

                $classroom = $studentClass->classroom;
                if (!$classroom || !$classroom->is_active) {
                    return response()->json([
                        'status' => 'error', 'nama' => substr($student->full_name, 0, 16),
                        'message' => 'Rombel tdk aktif'
                    ], 200);
                }

                // Cek absensi hari ini
                $existingAttendance = \App\Models\Attendance::where('student_id', $student->id)
                    ->where('date', $today)
                    ->first();

                if ($existingAttendance) {
                    $isNotCheckedOut = !$existingAttendance->time_out || $existingAttendance->time_out === '00:00:00' || $existingAttendance->time_out === '00:00';
                    if ($existingAttendance->time_in && $isNotCheckedOut) {
                         // Anti-spam cooldown: minimal 5 menit setelah check-in baru boleh check-out
                         $lastScan = \Carbon\Carbon::parse($today . ' ' . $existingAttendance->time_in);
                         $diffSeconds = now()->timestamp - $lastScan->timestamp;
                         $cooldown = config('services.kiosk.cooldown_seconds', 300);
                         if ($diffSeconds >= 0 && $diffSeconds < $cooldown) {
                             return response()->json([
                                 'status' => 'info', 'nama' => substr($student->full_name, 0, 16),
                                 'message' => 'Sdh Masuk ' . $lastScan->format('H:i'),
                                 'action_code' => 'COOLDOWN'
                             ], 200);
                         }
                         $existingAttendance->update(['time_out' => $currentTime]);
                         return response()->json([
                             'status' => 'success', 'nama' => substr($student->full_name, 0, 16),
                             'kelas' => substr($studentClass->classroom->class_name, 0, 16), 'waktu' => date('H:i', strtotime($currentTime)),
                             'message' => 'Berhasil Pulang', 'action_code' => 'CHECK_OUT'
                         ], 200);
                    }
                    return response()->json([
                        'status' => 'error', 'nama' => substr($student->full_name, 0, 16),
                        'message' => 'Sudah Absen!', 'action_code' => 'ALREADY_ATTENDED'
                    ], 200);
                }

                $entryTime = $classroom->entry_time ?? '07:30';
                $tolerance = $classroom->late_tolerance ?? 15;
                $lateLimit = date('H:i:s', strtotime("$entryTime +$tolerance minutes"));
                $status = ($currentTime > $lateLimit) ? 'terlambat' : 'hadir';

                $attendance = \App\Models\Attendance::create([
                    'student_id'   => $student->id,
                    'classroom_id' => $studentClass->classroom_id,
                    'date'         => $today,
                    'time_in'      => $currentTime,
                    'status'       => $status,
                    'recorded_via' => $type === 'qr' ? 'qr_gps' : 'rfid',
                    'device_id'    => $request->input('device_id', 'KIOSK-' . substr($uid, -4)), 
                ]);

                if ($student->user_id) {
                    $points = match($status) {
                        'hadir' => 10,
                        'alpha' => -10,
                        default => 0
                    };
                    $classroomName = $studentClass->classroom ? $studentClass->classroom->class_name : 'Kelas';
                    $desc = "Kehadiran di kelas " . $classroomName . " (" . ucfirst($status) . ")";
                    \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
                }

                return response()->json([
                    'status' => 'success', 'nama' => substr($student->full_name, 0, 16),
                    'kelas' => substr($studentClass->classroom->class_name, 0, 16), 'waktu' => date('H:i', strtotime($currentTime)),
                    'message' => $status === 'terlambat' ? 'Terlambat' : 'Berhasil Masuk', 'action_code' => 'CHECK_IN'
                ], 200);
            }

            // 5. ABSENSI GURU / PEGAWAI
            if ($employee) {
                // Cek absensi hari ini
                $existingAttendance = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                    ->where('date', $today)
                    ->first();

                // Dapatkan nama jabatan atau defaults
                $jabatan = $employee->getPrimaryPosition()?->name ?? ($employee->isTeacher() ? 'Guru' : 'Staf');

                if ($existingAttendance) {
                    $isNotCheckedOut = !$existingAttendance->time_out || $existingAttendance->time_out === '00:00:00' || $existingAttendance->time_out === '00:00';
                    if ($existingAttendance->time_in && $isNotCheckedOut) {
                         // Anti-spam cooldown: minimal 5 menit
                         $lastScan = \Carbon\Carbon::parse($today . ' ' . $existingAttendance->time_in);
                         $diffSeconds = now()->timestamp - $lastScan->timestamp;
                         $cooldown = config('services.kiosk.cooldown_seconds', 300);
                         if ($diffSeconds >= 0 && $diffSeconds < $cooldown) {
                             return response()->json([
                                 'status' => 'info', 'nama' => substr($employee->full_name, 0, 16),
                                 'message' => 'Sdh Masuk ' . $lastScan->format('H:i'),
                                 'action_code' => 'COOLDOWN'
                             ], 200);
                         }
                         $existingAttendance->update(['time_out' => $currentTime]);
                         return response()->json([
                             'status' => 'success', 'nama' => substr($employee->full_name, 0, 16),
                             'kelas' => substr($jabatan, 0, 16), 'waktu' => date('H:i', strtotime($currentTime)),
                             'message' => 'Berhasil Pulang', 'action_code' => 'CHECK_OUT'
                         ], 200);
                    }
                    return response()->json([
                        'status' => 'error', 'nama' => substr($employee->full_name, 0, 16),
                        'message' => 'Sudah Absen!', 'action_code' => 'ALREADY_ATTENDED'
                    ], 200);
                }

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

                $attendance = \App\Models\EmployeeAttendance::create([
                    'employee_id' => $employee->id,
                    'school_id' => $employee->school_id,
                    'date' => $today,
                    'time_in' => $currentTime,
                    'status' => 'hadir',
                    'notes' => $notes,
                    'recorded_via' => 'rfid', // Tetap 'rfid' mengikuti batasan enum database
                    'device_id' => $request->input('device_id', 'KIOSK-EMP'),
                ]);

                $displayMsg = 'Berhasil Masuk';
                if ($isTeacher) {
                    if (!$hasScheduleToday) {
                        $displayMsg = 'Tugas Khusus';
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
                        $displayMsg = 'Hadir Mengajar';
                    }
                } else {
                    $isWeekend = in_array(now()->format('D'), ['Sat', 'Sun']);
                    if ($isWeekend) {
                        $displayMsg = 'Tugas Khusus';
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
                    } else {
                        // Check if late (after school entry time)
                        $schoolClassroom = \App\Models\Classroom::where('school_id', $employee->school_id)
                            ->whereNotNull('entry_time')
                            ->first();
                        $entryTime = $schoolClassroom ? $schoolClassroom->entry_time : '07:30';
                        if ($currentTime > ($entryTime . ':00')) {
                            $displayMsg = 'Terlambat';
                        }
                    }
                }

                return response()->json([
                    'status' => 'success', 'nama' => substr($employee->full_name, 0, 16),
                    'kelas' => substr($jabatan, 0, 16), 'waktu' => date('H:i', strtotime($currentTime)),
                    'message' => $displayMsg, 'action_code' => 'CHECK_IN'
                ], 200);
            }

            // 6. ABSENSI KARYAWAN TEFA (BENGKELIN)
            if ($tefaEmployee) {
                $existingAtt = \App\Models\TefaAttendance::where('tefa_employee_id', $tefaEmployee->id)
                    ->where('date', $today)
                    ->first();

                $jabatan = $tefaEmployee->position ?? 'Karyawan Tefa';

                if ($existingAtt) {
                    $isNotCheckedOut = !$existingAtt->time_out || $existingAtt->time_out === '00:00:00' || $existingAtt->time_out === '00:00';
                    if ($existingAtt->time_in && $isNotCheckedOut) {
                        // Anti-spam cooldown: minimal 5 menit
                        $lastScan = \Carbon\Carbon::parse($today . ' ' . $existingAtt->time_in);
                        $diffSeconds = now()->timestamp - $lastScan->timestamp;
                        $cooldown = config('services.kiosk.cooldown_seconds', 300);
                        if ($diffSeconds >= 0 && $diffSeconds < $cooldown) {
                            return response()->json([
                                'status' => 'info', 'nama' => substr($tefaEmployee->name, 0, 16),
                                'message' => 'Sdh Masuk ' . $lastScan->format('H:i'),
                                'action_code' => 'COOLDOWN'
                            ], 200);
                        }
                        $existingAtt->update(['time_out' => $currentTime]);
                        return response()->json([
                            'status' => 'success', 'nama' => substr($tefaEmployee->name, 0, 16),
                            'kelas' => substr($jabatan, 0, 16), 'waktu' => date('H:i', strtotime($currentTime)),
                            'message' => 'Berhasil Pulang', 'action_code' => 'CHECK_OUT'
                        ], 200);
                    }
                    $existingAtt->update(['time_out' => $currentTime]);
                    return response()->json([
                        'status' => 'success', 'nama' => substr($tefaEmployee->name, 0, 16),
                        'kelas' => substr($jabatan, 0, 16), 'waktu' => date('H:i', strtotime($currentTime)),
                        'message' => 'Update Pulang', 'action_code' => 'CHECK_OUT'
                    ], 200);
                }

                // Absen Masuk
                $isWeekend = in_array(now()->format('D'), ['Sun']); // Waktu kerja Senin s.d Sabtu
                $status = 'hadir';
                $notes = 'Tepat Waktu';

                if ($isWeekend) {
                    $notes = 'Lembur (Minggu)';
                } else {
                    // Waktu kerja 08.00 s.d 17.00
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
                    'device_id' => $request->input('device_id', 'KIOSK-TEFA'),
                ]);

                return response()->json([
                    'status' => 'success', 'nama' => substr($tefaEmployee->name, 0, 16),
                    'kelas' => substr($jabatan, 0, 16), 'waktu' => date('H:i', strtotime($currentTime)),
                    'message' => ($status === 'terlambat' ? 'Terlambat Masuk' : 'Berhasil Masuk'), 'action_code' => 'CHECK_IN'
                ], 200);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('RFID Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'nama' => '!!ERROR!!',
                'message' => substr($e->getMessage(), 0, 32)
            ], 200);
        }
    }

    /**
     * Endpoint untuk Aplikasi Siswa/Gps Web App (QR + GPS)
     * POST /api/attendance/gps-scan
     */
    public function handleGpsScan(Request $request)
    {
        // Endpoint ini diasumsikan dipanggil via AJAX sesudah Siswa Login di Web
        $studentUserId = auth()->id(); 
        
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'qr_code_data' => 'nullable|string', // (Opsional) Jika tetap mau divalidasi dengan scan QR Kamera
            'device_id' => 'required|string', // Wajib: Kode Unik/Sidik Jari HP yang digunakan
        ]);

        $today = date('Y-m-d');
        $currentTime = date('H:i:s');

        // ==== KEKEAMANAN 1: One Device, One Attendance (1 HP = 1 Siswa Per Hari) ====
        $isDeviceUsedToday = \App\Models\Attendance::where('date', $today)
               ->where('device_id', $request->device_id)
               ->whereHas('student', function ($query) use ($studentUserId) {
                   // Perangkat tidak apa-apa sama ASAL akunnya milik orang yang sama 
                   // (mencegah akun Budi absen di HP yang sama yang tadi sudah dipakai Andi)
                   $query->where('user_id', '!=', $studentUserId); 
               })
               ->exists();

        if ($isDeviceUsedToday) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat HP ini sudah digunakan untuk mengabsen siswa lain hari ini. (Pelanggaran: Titip Absen)'
            ], 403);
        }

        // Cari Siswa si Penelepon API ini
        $student = \App\Models\Student::where('user_id', $studentUserId)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda bukan Siswa.'], 403);
        }

        // ==== KEAMANAN 2: GEOFENCING (GPS Radius Validasi) ====
        $school = $student->school;
        
        $schoolLat = (float) ($school->latitude ?? 0); 
        $schoolLong = (float) ($school->longitude ?? 0);
        $maxRadiusMeters = (int) \App\Models\Setting::getValue('attendance_max_radius', 1000); // Default 1000m (1km) tolerance
        $hasValidSchoolCoords = ($schoolLat != 0.0 && $schoolLong != 0.0);

        $today = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();

        // Cek apakah siswa sedang PKL aktif saat ini (Failsafe & Flexible)
        $isPklActive = \App\Models\PklPlacement::where(function($q) use ($student, $studentUserId) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_id', $studentUserId);
            })
            ->where(function($q) {
                $q->whereIn('status', ['active', 'aktif', 'approved', 'ongoing', 'berjalan'])
                  ->orWhereNull('status')
                  ->orWhereNotIn('status', ['cancelled', 'rejected', 'finished', 'completed', 'selesai', 'nonaktif']);
            })
            ->where(function($q) use ($today) {
                $q->whereNull('start_date')
                  ->orWhereDate('start_date', '<=', $today);
            })
            ->where(function($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $today);
            })
            ->exists();

        // Rumus Penghitungan Jarak (Haversine Formula via SQL atau hitung di PHP)
        $distance = $this->calculateDistance($request->latitude, $request->longitude, $schoolLat, $schoolLong);

        // Jika ada koordinat sekolah yang valid dan tidak sedang PKL aktif, terapkan batas radius
        if ($hasValidSchoolCoords && !$isPklActive && $distance > $maxRadiusMeters) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Lokasi Anda berada di luar jangkauan area sekolah (' . round($distance) . ' meter dari sekolah. Maksimal ' . $maxRadiusMeters . ' meter).'
            ], 403);
        }

        $studentClass = $student->studentClasses()
            ->where('status', 'aktif')
            ->where('academic_year_id', function($q) {
                $q->select('id')->from('academic_years')->where('is_active', true)->limit(1);
            })
            ->first();
        if (!$studentClass) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki Rombel yang aktif.'], 400);
        }

        // Determine if late
        $classroom = $studentClass->classroom;
        $entryTime = $classroom->entry_time ?? '07:30';
        $tolerance = $classroom->late_tolerance ?? 15;
        $lateLimit = date('H:i:s', strtotime("$entryTime +$tolerance minutes"));
        $status = ($currentTime > $lateLimit) ? 'terlambat' : 'hadir';

        // Catat Absen
        $attendance = \App\Models\Attendance::firstOrCreate(
            ['student_id' => $student->id, 'date' => $today],
            [
                'classroom_id' => $studentClass->classroom_id,
                'status' => $status,
                'recorded_via' => 'qr_gps',
                'device_id' => $request->device_id, // KODE UNIK HP DISIMPAN
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]
        );

        if ($attendance->wasRecentlyCreated) {
            $attendance->update(['time_in' => $currentTime]);

            if ($student->user_id) {
                $points = match($status) {
                    'hadir' => 10,
                    'alpha' => -10,
                    default => 0
                };
                $classroomName = $studentClass->classroom ? $studentClass->classroom->class_name : 'Kelas';
                $desc = "Kehadiran di kelas " . $classroomName . " (" . ucfirst($status) . ")";
                \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
            }

            return response()->json(['success' => true, 'message' => 'Absen berhasil (Radius: '. round($distance) .'m)']);
        }

        // Jika dia tap lagi untuk pulang
        $isNotCheckedOut = !$attendance->time_out || $attendance->time_out === '00:00:00' || $attendance->time_out === '00:00';
        if ($attendance->time_in && $isNotCheckedOut) {
            $attendance->update(['time_out' => $currentTime]);
            return response()->json(['success' => true, 'message' => 'Absen pulang berhasil.']);
        }

        return response()->json(['success' => false, 'message' => 'Anda sudah absen masuk dan pulang hari ini.']);
    }

    /**
     * Helper Function: Hitung Jarak Koordinat (Meters) - Haversine Formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // Radius Bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        $d = $earthRadius * $c;

        return $d;
    }
}

