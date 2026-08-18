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
            $rawUid = strtoupper(trim($request->uid));
            
            // Generate semua variasi UID (Hex, Reversed Hex, Decimal, Reversed Decimal)
            $candidates = $this->getUidCandidates($rawUid);

            $type = $request->input('type'); // Opsional: 'rfid' atau 'qr'

            // Tulis UID ke scan-buffer agar browser (modal registrasi RFID) bisa mengambilnya
            $bufferFile = storage_path('app/rfid_scan_buffer.json');
            file_put_contents($bufferFile, json_encode(['uid' => $rawUid, 'candidates' => $candidates, 'time' => time()]));
            
            $today = now()->format('Y-m-d');
            $currentTime = now()->format('H:i:s');

            $student = null;
            $employee = null;
            $teacher = null;
            $tefaEmployee = null;

            // =========================================================================
            // 2. IDENTIFIKASI ENTITAS (GURU / PEGAWAI / KARYAWAN TEFA / SISWA)
            // =========================================================================

            // TAHAP A: EXACT MATCH LOOKUP (Prioritas Tertinggi untuk QR Code & RFID Langsung)
            // -------------------------------------------------------------------------
            
            // 1. Cari di Teacher (Guru / Kepala Sekolah) berdasarkan teacher_code
            $teacher = \App\Models\Teacher::where('is_active', true)
                ->where('teacher_code', $rawUid)
                ->first();

            // 2. Cari di Employee (Pegawai / Staf / Guru) jika belum ketemu
            if (!$teacher) {
                $employee = \App\Models\Employee::where('is_active', true)
                    ->where(function($q) use ($rawUid) {
                        $q->where('employee_code', $rawUid)
                          ->orWhere('rfid_uid', $rawUid)
                          ->orWhere('nip', $rawUid);
                    })
                    ->first();
            }

            // 3. Cari di TefaEmployee (Karyawan Bengkelin TEFA)
            if (!$teacher && !$employee) {
                $tefaEmployee = \App\Models\TefaEmployee::where('is_active', true)
                    ->where('rfid_uid', $rawUid)
                    ->first();
            }

            // 4. Cari di Siswa (NIS, NISN, atau RFID UID)
            if (!$teacher && !$employee && !$tefaEmployee) {
                $student = \App\Models\Student::whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
                    ->where(function($q) use ($rawUid) {
                        $q->where('nis', $rawUid)
                          ->orWhere('nisn', $rawUid)
                          ->orWhere('rfid_uid', $rawUid);
                    })
                    ->first();
            }

            // 5. Cek User Account (jika scan username akun)
            if (!$teacher && !$employee && !$tefaEmployee && !$student) {
                $matchedUser = \App\Models\User::where('username', $rawUid)->first();
                if ($matchedUser) {
                    if ($matchedUser->role === 'guru') {
                        $teacher = \App\Models\Teacher::where('user_id', $matchedUser->id)->first();
                    } elseif (in_array($matchedUser->role, ['pegawai', 'kepala_sekolah', 'admin_sekolah'])) {
                        $employee = \App\Models\Employee::where('user_id', $matchedUser->id)->first();
                    } elseif ($matchedUser->role === 'siswa') {
                        $student = \App\Models\Student::where('user_id', $matchedUser->id)
                            ->whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
                            ->first();
                    }
                }
            }

            // TAHAP B: RFID HARDWARE CANDIDATE MATCH (Jika RFID dibaca dengan variasi Hex/Dec)
            // -------------------------------------------------------------------------
            if (!$teacher && !$employee && !$tefaEmployee && !$student) {
                // Cari di Siswa via RFID candidates
                $student = \App\Models\Student::whereIn('rfid_uid', $candidates)
                    ->whereIn('status', \App\Models\StudentStatusHistory::ACTIVE_STATUSES)
                    ->first();

                // Cari di Pegawai/Guru via RFID candidates
                if (!$student) {
                    $employee = \App\Models\Employee::whereIn('rfid_uid', $candidates)
                        ->where('is_active', true)
                        ->first();
                }

                // Cari di Karyawan TEFA via RFID candidates
                if (!$student && !$teacher && !$employee) {
                    $tefaEmployee = \App\Models\TefaEmployee::whereIn('rfid_uid', $candidates)
                        ->where('is_active', true)
                        ->first();
                }
            }

            // RESOLUSI RELASI TEACHER -> EMPLOYEE
            // Pastikan jika Teacher ditemukan, model Employee terkait terisi untuk EmployeeAttendance
            if ($teacher && !$employee) {
                if ($teacher->employee_id) {
                    $employee = \App\Models\Employee::find($teacher->employee_id);
                }
                if (!$employee && $teacher->user_id) {
                    $employee = \App\Models\Employee::where('user_id', $teacher->user_id)->first();
                }
                if (!$employee) {
                    $employee = \App\Models\Employee::firstOrCreate(
                        ['user_id' => $teacher->user_id ?? 0],
                        [
                            'school_id' => $teacher->school_id,
                            'employee_code' => $teacher->teacher_code ?? ('G-' . $teacher->id),
                            'full_name' => $teacher->full_name,
                            'gender' => $teacher->gender ?? 'L',
                            'employee_type' => 'guru',
                            'employment_status' => 'yayasan',
                            'tmt_date' => now()->format('Y-m-d'),
                            'is_active' => true,
                        ]
                    );
                    if (!$teacher->employee_id) {
                        $teacher->update(['employee_id' => $employee->id]);
                    }
                }
            }

            // 3. JIKA TIDAK DITEMUKAN → Cek diagnosa status non-aktif sebelum melempar "KARTU BARU"
            if (!$student && !$employee && !$tefaEmployee) {
                // Cek apakah ada siswa dengan RFID ini tapi statusnya tidak termasuk ACTIVE_STATUSES
                $inactiveStudent = \App\Models\Student::whereIn('rfid_uid', $candidates)->first();
                if ($inactiveStudent) {
                    return response()->json([
                        'status' => 'error',
                        'nama' => substr($inactiveStudent->full_name, 0, 16),
                        'message' => 'Status: ' . strtoupper($inactiveStudent->status ?? 'NON-AKTIF'),
                        'action_code' => 'INACTIVE_STUDENT',
                        'uid' => $rawUid,
                        'waktu' => date('H:i'),
                    ], 200);
                }

                return response()->json([
                    'status' => 'info',
                    'nama' => 'KARTU BARU',
                    'kelas' => 'UID: ' . $rawUid,
                    'message' => 'Daftarkan di Admin',
                    'action_code' => 'NEW_CARD',
                    'uid' => $rawUid,
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
                    'device_id'    => $request->input('device_id', 'KIOSK-' . substr($rawUid, -4)), 
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
                $jabatan = $employee->getPrimaryPosition()?->position_name ?? ($employee->isTeacher() ? 'Guru' : 'Staf');

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

                // Kirim Notifikasi WA ke Nomor HP Guru / Pegawai
                $empPhone = $employee->phone ?? $employee->user?->phone_number ?? null;
                if ($empPhone) {
                    try {
                        $waService = app(\App\Services\WhatsAppService::class);
                        $templateName = $isTeacher ? 'teacher.attendance' : 'employee.attendance';
                        $waService->sendTemplate($empPhone, $templateName, [
                            'nama' => $employee->full_name,
                            'tanggal' => date('d F Y', strtotime($today)),
                            'waktu' => date('H:i', strtotime($currentTime)),
                            'status' => 'Hadir Tepat Waktu',
                            'tipe_absen' => 'Masuk',
                            'jabatan' => $jabatan,
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('WA Employee Attendance Notification Failed: ' . $e->getMessage());
                    }
                }

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

        // ==== KEAMANAN 2: GEOFENCING (GPS Radius Validasi) & PENGECUALIAN PKL ====
        $school = $student->school;
        
        $schoolLat = (float) ($school->latitude ?? 0); 
        $schoolLong = (float) ($school->longitude ?? 0);
        if ($schoolLat == 0.0 || $schoolLong == 0.0) {
            $schoolLat = (float) \App\Models\Setting::getValue('school_latitude', 1.282500);
            $schoolLong = (float) \App\Models\Setting::getValue('school_longitude', 97.619000);
        }

        $maxRadiusMeters = (int) \App\Models\Setting::getValue('attendance_max_radius', 50); // Default 50 meter radius
        if ($maxRadiusMeters <= 0) {
            $maxRadiusMeters = 50;
        }

        $todayDate = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();

        // Cek apakah siswa SEDANG AKTIF PKL di Industri / DUDI
        $activePkl = \App\Models\PklPlacement::with('dudi')
            ->where(function($q) use ($student, $studentUserId) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_id', $studentUserId);
            })
            ->where(function($q) {
                $q->whereIn('status', ['active', 'aktif', 'approved', 'ongoing', 'berjalan']);
            })
            ->where(function($q) use ($todayDate) {
                $q->whereNull('start_date')
                  ->orWhereDate('start_date', '<=', $todayDate);
            })
            ->where(function($q) use ($todayDate) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $todayDate);
            })
            ->first();

        $isPklActive = !empty($activePkl);
        $dudiName = $activePkl ? ($activePkl->dudi->name ?? ($activePkl->company_name ?? 'Mitra DUDI')) : null;

        // Hitung Jarak GPS Siswa ke Titik Sekolah
        $distance = $this->calculateDistance($request->latitude, $request->longitude, $schoolLat, $schoolLong);

        // Jika BUKAN siswa PKL aktif dan berada di luar radius sekolah, TOLAK SEGERA!
        if (!$isPklActive && $distance > $maxRadiusMeters) {
            $formattedDist = number_format($distance, 0, ',', '.');
            return response()->json([
                'success' => false,
                'message' => "Gagal! Lokasi Anda berada di luar jangkauan area sekolah ({$formattedDist} meter dari sekolah. Maksimal {$maxRadiusMeters} meter)."
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
                'recorded_via' => $isPklActive ? 'gps_pkl' : 'qr_gps',
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
                $desc = $isPklActive 
                    ? "Presensi PKL di {$dudiName}" 
                    : "Kehadiran di kelas " . $classroomName . " (" . ucfirst($status) . ")";
                \App\Models\ReputationLog::log($student->user_id, $points, 'attendance', $desc, $attendance);
            }

            $isMerdekaDay = (date('m-d') === '08-17');
            if ($isMerdekaDay) {
                $msg = '🇮🇩 DIRGAHAYU REPUBLIK INDONESIA! Merdeka! ✊ Selamat Hari Kemerdekaan RI! Presensi kehadiranmu hari ini berhasil dicatat. Tetap semangat belajar demi masa depan Bangsa! 🇮🇩✨';
            } elseif ($isPklActive) {
                $msg = "📍 Presensi PKL Berhasil! Kehadiran Anda di {$dudiName} telah tercatat pada jam " . date('H:i', strtotime($currentTime)) . " dengan tag GPS. Selamat bertugas! 💼";
            } else {
                $msg = '📍 Presensi Mandiri Sekolah berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . '!';
            }

            return response()->json(['success' => true, 'message' => $msg]);
        }

        // Jika dia tap lagi untuk pulang
        $isNotCheckedOut = !$attendance->time_out || $attendance->time_out === '00:00:00' || $attendance->time_out === '00:00';
        if ($attendance->time_in && $isNotCheckedOut) {
            $attendance->update(['time_out' => $currentTime]);
            
            $isMerdekaDay = (date('m-d') === '08-17');
            if ($isMerdekaDay) {
                $msg = '🇮🇩 DIRGAHAYU REPUBLIK INDONESIA! Merdeka! ✊ Presensi Pulang berhasil dicatat. Selamat memperingati Hari Kemerdekaan RI!';
            } elseif ($isPklActive) {
                $msg = "📍 Presensi Pulang PKL Berhasil! Selesai bertugas di {$dudiName} pada jam " . date('H:i', strtotime($currentTime)) . ". 💼";
            } else {
                $msg = '📍 Presensi Pulang berhasil dicatat pada jam ' . date('H:i', strtotime($currentTime)) . '!';
            }

            return response()->json(['success' => true, 'message' => $msg]);
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

    /**
     * Hasilkan semua variasi format UID (Hex, Reversed Hex, Decimal, Reversed Decimal, Wiegand, Zero-Padded)
     * khusus untuk pembacaan kartu RFID fisik.
     * Tidak memutasi string non-hex/QR code guru (seperti "GR001", "GTK001", "PGW-25").
     */
    private function getUidCandidates(string $rawUid): array
    {
        $uid = strtoupper(trim($rawUid));
        $candidates = [$uid];

        $isPureHex = (bool) preg_match('/^[0-9A-F]+$/i', $uid);
        $isPureDec = (bool) preg_match('/^[0-9]+$/', $uid);

        // Jika string bukan format angka atau hex murni (misal "GR001", "PGW-25"), jangan lakukan mutasi
        if (!$isPureHex && !$isPureDec) {
            return array_values(array_unique(array_filter($candidates)));
        }

        // 1. Jika berupa angka desimal murni
        if ($isPureDec) {
            $ltrimUid = ltrim($uid, '0');
            if ($ltrimUid && $ltrimUid !== $uid) {
                $candidates[] = $ltrimUid;
            }

            if (strlen($uid) >= 5 && strlen($uid) <= 12) {
                $num = (float)$uid;
                if ($num > 0 && $num <= 4294967295) {
                    $hex = strtoupper(str_pad(dechex((int)$num), 8, '0', STR_PAD_LEFT));
                    $candidates[] = $hex;
                    
                    if (strlen($hex) === 8) {
                        // Reversed byte hex (Little Endian)
                        $revHex = $hex[6].$hex[7].$hex[4].$hex[5].$hex[2].$hex[3].$hex[0].$hex[1];
                        $candidates[] = $revHex;
                        $revDec = (string) hexdec($revHex);
                        $candidates[] = $revDec;
                        $candidates[] = str_pad($revDec, 10, '0', STR_PAD_LEFT);

                        // Wiegand 26 Truncation (3 bytes terakhir)
                        $sub3Hex = substr($hex, 2);
                        $candidates[] = $sub3Hex;
                        $candidates[] = (string) hexdec($sub3Hex);
                    }
                }
            }
        }

        // 2. Jika berupa string Hex murni (4-byte / 8-char hex dari RC522)
        if ($isPureHex && (strlen($uid) === 8 || strlen($uid) === 14 || strlen($uid) === 4 || strlen($uid) === 6)) {
            $padHex = str_pad($uid, 8, '0', STR_PAD_LEFT);
            $candidates[] = $padHex;

            $dec = (string) hexdec($padHex);
            $candidates[] = $dec;
            $candidates[] = str_pad($dec, 10, '0', STR_PAD_LEFT);

            if (strlen($padHex) === 8) {
                // Reversed Byte Hex
                $revHex = $padHex[6].$padHex[7].$padHex[4].$padHex[5].$padHex[2].$padHex[3].$padHex[0].$padHex[1];
                $candidates[] = $revHex;
                $revDec = (string) hexdec($revHex);
                $candidates[] = $revDec;
                $candidates[] = str_pad($revDec, 10, '0', STR_PAD_LEFT);

                // Wiegand 26 (3 bytes terakhir)
                $sub3Hex = substr($padHex, 2);
                $candidates[] = $sub3Hex;
                $candidates[] = (string) hexdec($sub3Hex);
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}



