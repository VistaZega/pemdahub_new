<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\NoiseDevice;
use App\Models\NoiseLog;
use App\Models\School;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NoiseControlController extends Controller
{
    /**
     * Dashboard Utama Kontrol Kebisingan IoT (/noicecontrol)
     */
    public function index(Request $request)
    {
        // 1. Otorisasi Akses (Login Admin/Guru, PIN Sesi, atau query ?secret=pembda99)
        $isUnlocked = auth()->check() || $request->session()->get('noisecontrol_unlocked', false) || $request->query('secret') === 'pembda99';
        if ($request->query('secret') === 'pembda99') {
            $request->session()->put('noisecontrol_unlocked', true);
            $isUnlocked = true;
        }

        // 2. Ambil seluruh perangkat noise devices
        $devices = NoiseDevice::with(['classroom.homeroomTeacher', 'school'])
            ->orderBy('id', 'asc')
            ->get();

        // 3. Tentukan perangkat aktif yang sedang dimonitor di tampilan utama
        $activeDeviceId = $request->query('device_id') ?: ($devices->first()?->id ?? null);
        $activeDevice = $devices->where('id', $activeDeviceId)->first() ?: $devices->first();

        // 4. Data Sekolah Aktif (Mengikuti Aturan Unit Sekolah & Yayasan)
        $schools = School::schoolsOnly()->orderBy('name')->get();

        // 5. Data Kelas Aktif beserta Wali Kelas
        $classrooms = Classroom::where('is_active', true)
            ->with(['school', 'homeroomTeacher'])
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        // 6. Riwayat Log Kebisingan Terkini (50 baris terakhir)
        $recentLogs = NoiseLog::with(['device', 'classroom'])
            ->when($activeDevice, function ($q) use ($activeDevice) {
                // Prioritaskan log dari device aktif bila ada
                return $q->where('noise_device_id', $activeDevice->id);
            })
            ->latest('id')
            ->limit(35)
            ->get();

        // 7. Riwayat Notifikasi Alarm Kebisingan Terakhir
        $recentAlerts = NoiseLog::with(['device', 'classroom'])
            ->where('is_alert_triggered', true)
            ->latest('id')
            ->limit(15)
            ->get();

        // 8. Ringkasan Statistik Riil
        $stats = [
            'total_devices'   => $devices->count(),
            'online_devices'  => $devices->filter(fn($d) => $d->isOnline())->count(),
            'alerts_today'    => NoiseLog::where('is_alert_triggered', true)->whereDate('created_at', Carbon::today())->count(),
            'avg_db_today'    => round(NoiseLog::whereDate('created_at', Carbon::today())->avg('db_level') ?? 0, 1),
            'max_db_today'    => round(NoiseLog::whereDate('created_at', Carbon::today())->max('db_level') ?? 0, 1),
        ];

        return view('noisecontrol.index', compact(
            'isUnlocked',
            'devices',
            'activeDevice',
            'schools',
            'classrooms',
            'recentLogs',
            'recentAlerts',
            'stats'
        ));
    }

    /**
     * Buka Kunci Akses via PIN (AJAX)
     */
    public function unlock(Request $request)
    {
        $pin = trim($request->input('pin', ''));
        if ($pin === 'pembda99' || $pin === 'admin' || $pin === '123456') {
            $request->session()->put('noisecontrol_unlocked', true);
            return response()->json([
                'status'  => 'success',
                'message' => 'Akses kontrol alat kebisingan berhasil dibuka.',
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'PIN keamanan tidak valid. Silakan masukkan PIN resmi yayasan/sekolah.',
        ], 403);
    }

    /**
     * Tambah Perangkat Baru
     */
    public function storeDevice(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:120',
            'device_uid'            => 'nullable|string|max:60|unique:noise_devices,device_uid',
            'school_id'             => 'nullable|exists:schools,id',
            'classroom_id'          => 'nullable|exists:classrooms,id',
            'location_note'         => 'nullable|string|max:150',
            'custom_recipient_name' => 'nullable|string|max:100',
            'custom_phone'          => 'nullable|string|max:30',
            'threshold_green'       => 'required|numeric|min:30|max:120',
            'threshold_yellow'      => 'required|numeric|min:30|max:120',
            'threshold_red'         => 'required|numeric|min:30|max:120',
            'alert_delay_seconds'   => 'nullable|integer|min:5|max:600',
            'cooldown_minutes'      => 'required|integer|min:1|max:120',
            'wa_enabled'            => 'boolean',
        ]);

        $deviceUid = !empty($validated['device_uid']) 
            ? strtoupper(trim($validated['device_uid']))
            : 'ESP32-NOISE-' . strtoupper(Str::random(4));

        $token = 'nd_' . Str::random(32);

        $device = NoiseDevice::create([
            'name'                  => $validated['name'],
            'device_uid'            => $deviceUid,
            'device_token'          => $token,
            'school_id'             => $validated['school_id'] ?? null,
            'classroom_id'          => $validated['classroom_id'] ?? null,
            'location_note'         => $validated['location_note'] ?? 'Ruang Kelas',
            'custom_recipient_name' => $validated['custom_recipient_name'] ?? null,
            'custom_phone'          => $validated['custom_phone'] ?? null,
            'threshold_green'       => $validated['threshold_green'],
            'threshold_yellow'      => $validated['threshold_yellow'],
            'threshold_red'         => $validated['threshold_red'],
            'alert_delay_seconds'   => $validated['alert_delay_seconds'] ?? 60,
            'cooldown_minutes'      => $validated['cooldown_minutes'],
            'wa_enabled'            => $request->boolean('wa_enabled', true),
            'is_active'             => true,
            'last_status'           => 'safe',
            'last_db'               => 45.0,
        ]);

        return redirect()->route('noisecontrol.index', ['device_id' => $device->id])
            ->with('success', "Perangkat {$device->name} ({$device->device_uid}) berhasil didaftarkan!");
    }

    /**
     * Update Pengaturan Perangkat
     */
    public function updateDevice(Request $request, $id)
    {
        $device = NoiseDevice::findOrFail($id);

        $validated = $request->validate([
            'name'                  => 'required|string|max:120',
            'school_id'             => 'nullable|exists:schools,id',
            'classroom_id'          => 'nullable|exists:classrooms,id',
            'location_note'         => 'nullable|string|max:150',
            'custom_recipient_name' => 'nullable|string|max:100',
            'custom_phone'          => 'nullable|string|max:30',
            'threshold_green'       => 'required|numeric|min:30|max:120',
            'threshold_yellow'      => 'required|numeric|min:30|max:120',
            'threshold_red'         => 'required|numeric|min:30|max:120',
            'calibration_offset'    => 'nullable|numeric|min:-30|max:30',
            'alert_delay_seconds'   => 'nullable|integer|min:5|max:600',
            'cooldown_minutes'      => 'required|integer|min:1|max:120',
            'wa_enabled'            => 'boolean',
            'is_active'             => 'boolean',
        ]);

        $device->update([
            'name'                  => $validated['name'],
            'school_id'             => $validated['school_id'] ?? null,
            'classroom_id'          => $validated['classroom_id'] ?? null,
            'location_note'         => $validated['location_note'] ?? $device->location_note,
            'custom_recipient_name' => $validated['custom_recipient_name'] ?? null,
            'custom_phone'          => $validated['custom_phone'] ?? null,
            'threshold_green'       => $validated['threshold_green'],
            'threshold_yellow'      => $validated['threshold_yellow'],
            'threshold_red'         => $validated['threshold_red'],
            'calibration_offset'    => $validated['calibration_offset'] ?? 0.0,
            'alert_delay_seconds'   => $validated['alert_delay_seconds'] ?? ($device->alert_delay_seconds ?: 60),
            'cooldown_minutes'      => $validated['cooldown_minutes'],
            'wa_enabled'            => $request->boolean('wa_enabled', true),
            'is_active'             => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pengaturan perangkat berhasil diperbarui.',
                'device'  => $device,
            ]);
        }

        return redirect()->route('noisecontrol.index', ['device_id' => $device->id])
            ->with('success', "Pengaturan perangkat {$device->name} berhasil diperbarui.");
    }

    /**
     * Hapus Perangkat
     */
    public function deleteDevice($id)
    {
        $device = NoiseDevice::findOrFail($id);
        $name = $device->name;
        $device->delete();

        return redirect()->route('noisecontrol.index')
            ->with('success', "Perangkat {$name} berhasil dihapus.");
    }

    /**
     * Regenerasi Token Perangkat (API Key)
     */
    public function regenerateToken($id)
    {
        $device = NoiseDevice::findOrFail($id);
        $device->device_token = 'nd_' . Str::random(32);
        $device->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Token API perangkat berhasil diperbarui.',
            'token'   => $device->device_token,
        ]);
    }

    /**
     * Test Kirim Notifikasi WhatsApp ke Wali Kelas & Kepala Sekolah Unit Langsung
     */
    public function testWhatsApp(Request $request, $id)
    {
        $device = NoiseDevice::with(['classroom.homeroomTeacher', 'school'])->findOrFail($id);
        $wali = $device->getEffectiveWaliKelas();
        $principalInfo = $device->getPrincipalInfo();

        if (empty($wali['phone']) && empty($principalInfo['phone'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor WhatsApp belum terpasang, baik untuk Wali Kelas maupun Kepala Sekolah. Pastikan data nomor HP telah terdaftar.',
            ], 422);
        }

        $className = $device->classroom ? $device->classroom->class_name : 'Ruang Kelas Pembda';
        $location = $device->location_note ?: 'Dinding Depan Kelas';

        $sentList = [];
        $failedList = [];

        // A. Kirim ke Wali Kelas
        if (!empty($wali['phone'])) {
            $message = "🔔 *UJI COBA SISTEM MONITORING KEBISINGAN KELAS* 🔔\n\n"
                . "Halo Bapak/Ibu Wali Kelas *{$wali['name']}*,\n"
                . "Ini adalah pesan verifikasi koneksi dari Perangkat IoT Pemantau Kebisingan Kelas Perguruan Pembda.\n\n"
                . "🏫 *Unit/Kelas:* {$className}\n"
                . "📍 *Lokasi Sensor:* {$location}\n"
                . "📟 *ID Alat:* `{$device->device_uid}`\n"
                . "📊 *Batas Bahaya (Merah):* {$device->threshold_red} dB\n"
                . "🕒 *Waktu Uji:* " . Carbon::now()->format('H:i:s d/m/Y') . " WIB\n\n"
                . "Jika kebisingan kelas melampaui *{$device->threshold_red} dB* (Lampu Merah & Buzzer Aktif), peringatan otomatis akan dikirim ke WhatsApp Anda agar situasi kelas dapat segera ditertibkan.\n\n"
                . "_SIM Terpadu PembdaHUB - Otomasi IoT Sekolah_";

            try {
                /** @var WhatsAppService $waService */
                $waService = app(WhatsAppService::class);
                $resWali = $waService->sendMessage($wali['phone'], $message);
                if (!empty($resWali['success'])) {
                    $sentList[] = "Wali Kelas ({$wali['name']})";
                } else {
                    $failedList[] = "Wali Kelas ({$wali['name']}): " . ($resWali['message'] ?? 'Gagal');
                }
            } catch (\Throwable $e) {
                $failedList[] = "Wali Kelas ({$wali['name']}): " . $e->getMessage();
            }
        }

        // B. Kirim ke Kepala Sekolah Unit Terkait
        if (!empty($principalInfo['phone']) && $principalInfo['phone'] !== ($wali['phone'] ?? null)) {
            $principalMessage = "🔔 *UJI COBA SISTEM MONITORING KEBISINGAN KELAS* 🔔\n\n"
                . "Halo Bapak/Ibu *{$principalInfo['name']}* ({$principalInfo['title']}),\n"
                . "Ini adalah pesan verifikasi koneksi pengawasan pimpinan dari Perangkat IoT Pemantau Kebisingan Kelas Perguruan Pembda.\n\n"
                . "🏫 *Unit/Kelas:* {$className}\n"
                . "📍 *Lokasi Sensor:* {$location}\n"
                . "📟 *ID Alat:* `{$device->device_uid}`\n"
                . "📊 *Batas Bahaya (Merah):* {$device->threshold_red} dB\n"
                . "👤 *Wali Kelas Terkait:* {$wali['name']}\n"
                . "🕒 *Waktu Uji:* " . Carbon::now()->format('H:i:s d/m/Y') . " WIB\n\n"
                . "Jika terjadi kebisingan berlebih (Lampu Merah & Buzzer Bunyi), laporan darurat otomatis diteruskan ke Kepala Sekolah dan Wali Kelas secara bersamaan.\n\n"
                . "_SIM Terpadu PembdaHUB - Pengawasan KBM_";

            try {
                /** @var WhatsAppService $waService */
                $waService = app(WhatsAppService::class);
                $resKepsek = $waService->sendMessage($principalInfo['phone'], $principalMessage);
                if (!empty($resKepsek['success'])) {
                    $sentList[] = "Kepala Sekolah ({$principalInfo['name']})";
                } else {
                    $failedList[] = "Kepala Sekolah ({$principalInfo['name']}): " . ($resKepsek['message'] ?? 'Gagal');
                }
            } catch (\Throwable $e) {
                $failedList[] = "Kepala Sekolah ({$principalInfo['name']}): " . $e->getMessage();
            }
        }

        // Catat log pengujian
        $allRecipients = [];
        if (!empty($wali['phone'])) $allRecipients[] = "Wali: {$wali['name']} ({$wali['phone']})";
        if (!empty($principalInfo['phone']) && $principalInfo['phone'] !== ($wali['phone'] ?? null)) {
            $allRecipients[] = "Kepsek: {$principalInfo['name']} ({$principalInfo['phone']})";
        }

        $isSuccess = count($sentList) > 0;
        NoiseLog::create([
            'noise_device_id'    => $device->id,
            'classroom_id'       => $device->classroom_id,
            'db_level'           => $device->last_db ?: 65.0,
            'peak_db'            => $device->peak_db ?: 65.0,
            'status'             => 'safe',
            'is_alert_triggered' => true,
            'alert_recipient'    => implode(' | ', $allRecipients),
            'alert_message'      => "Uji coba pengiriman notifikasi darurat WhatsApp",
            'alert_status'       => $isSuccess ? 'test_sent' : 'test_failed',
            'created_at'         => Carbon::now(),
        ]);

        if ($isSuccess) {
            $msg = "Pesan uji coba berhasil dikirim ke: " . implode(' & ', $sentList) . "!";
            if (count($failedList) > 0) {
                $msg .= " (Catatan kendala: " . implode('; ', $failedList) . ")";
            }
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => "Gagal mengirim pesan uji coba WhatsApp: " . implode('; ', $failedList),
        ], 500);
    }

    /**
     * Endpoint Telemetri dari Hardware ESP32 & Simulator Browser
     * Dipanggil oleh ESP32 via HTTP POST JSON: { "token": "...", "db": 82.5, "peak_db": 86.0 }
     */
    public function recordTelemetry(Request $request)
    {
        $token = $request->header('X-Device-Token') 
            ?: $request->input('token') 
            ?: $request->query('token');

        if (empty($token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Device Token wajib disertakan.',
            ], 401);
        }

        /** @var NoiseDevice|null $device */
        $device = NoiseDevice::with(['classroom.homeroomTeacher', 'school'])
            ->where('device_token', $token)
            ->first();

        if (!$device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Device Token tidak terdaftar di sistem PembdaHUB.',
            ], 404);
        }

        // 1. Baca data desibel dari sensor
        $rawDb = (float) $request->input('db', 0);
        $peakDb = (float) $request->input('peak_db', $rawDb);
        $firmware = $request->input('firmware');

        // Terapkan kalibrasi offset bila ada
        $calibratedDb = round($rawDb + (float) $device->calibration_offset, 1);
        $calibratedPeak = round(max($peakDb + (float) $device->calibration_offset, $calibratedDb), 1);

        // 2. Evaluasi status suara (Aman, Sedang, Bising)
        $status = $device->evaluateStatus($calibratedDb);

        // 3. Update status perangkat di database
        $device->last_db = $calibratedDb;
        $device->peak_db = $calibratedPeak;
        $device->last_status = $status;
        $device->last_seen_at = Carbon::now();
        $device->ip_address = $request->ip();
        if ($firmware) {
            $device->firmware_version = $firmware;
        }

        $alertSent = false;
        $alertStatus = null;
        $alertMessage = null;
        $waliInfo = $device->getEffectiveWaliKelas();
        $principalInfo = $device->getPrincipalInfo();

        $recipientsList = [];
        if (!empty($waliInfo['phone'])) {
            $recipientsList[] = "Wali: {$waliInfo['name']} ({$waliInfo['phone']})";
        }
        if (!empty($principalInfo['phone']) && $principalInfo['phone'] !== ($waliInfo['phone'] ?? null)) {
            $recipientsList[] = "Kepsek: {$principalInfo['name']} ({$principalInfo['phone']})";
        }
        $recipientStr = !empty($recipientsList) ? implode(' | ', $recipientsList) : 'Belum Ada No HP';

        // 4. Logika Peringatan Bising (HANYA Status Danger & Bertahan Konstan >= alert_delay_seconds)
        $dangerDuration = (int) $request->input('danger_duration', 0);
        $requiredDelay = (int) ($device->alert_delay_seconds ?: 60);

        if ($status === 'danger') {
            // Verifikasi durasi: hanya kirim WhatsApp jika kebisingan konstan >= batas waktu (default 60 detik)
            // Atau dipicu manual melalui tombol web simulator (force_alert = true)
            $isSustained = ($dangerDuration >= $requiredDelay) || $request->boolean('force_alert');

            if ($isSustained) {
                if ($device->canTriggerAlert()) {
                    $hasSentAny = false;
                    $className = $device->classroom ? $device->classroom->class_name : 'Ruang Kelas';
                    $location = $device->location_note ?: 'Dinding Depan Kelas';
                    $durasiDetik = max($dangerDuration, $requiredDelay);

                    // A. Kirim ke Wali Kelas
                    if (!empty($waliInfo['phone'])) {
                        $alertMessage = "🚨 *PERINGATAN KEBISINGAN KELAS (KONSTAN BERLANJUT)* 🚨\n\n"
                            . "Yth. Bapak/Ibu Wali Kelas *{$waliInfo['name']}*,\n"
                            . "Sistem sensor IoT mendeteksi bahwa tingkat kebisingan di ruang kelas Anda telah melampaui batas aman secara *KONSTAN selama lebih dari 1 menit* ({$durasiDetik} detik).\n\n"
                            . "🏫 *Unit/Kelas:* {$className}\n"
                            . "📍 *Lokasi:* {$location}\n"
                            . "📊 *Tingkat Kebisingan:* *{$calibratedDb} dB* (Batas Bahaya: {$device->threshold_red} dB)\n"
                            . "🔴 *Indikator:* *LAMPU LED MERAH & BUZZER ALARM AKTIF*\n"
                            . "🕒 *Waktu Terdeteksi:* " . Carbon::now()->format('H:i:s d/m/Y') . " WIB\n\n"
                            . "Mohon kesediaan Bapak/Ibu untuk memeriksa dan membantu mengendalikan situasi di kelas agar suasana belajar kembali tenang dan tertib.\n\n"
                            . "Terima kasih atas perhatiannya.\n"
                            . "_Sistem Otomasi SIM PembdaHUB - Perguruan Pembda Nias_";

                        try {
                            /** @var WhatsAppService $waService */
                            $waService = app(WhatsAppService::class);
                            $res = $waService->sendMessage($waliInfo['phone'], $alertMessage);
                            if (!empty($res['success'])) {
                                $hasSentAny = true;
                            }
                        } catch (\Throwable $e) {
                            Log::error("NoiseControl WA Alert to Wali failed: " . $e->getMessage());
                        }
                    }

                    // B. Kirim ke Kepala Sekolah Unit Terkait (Pengawasan Pimpinan)
                    if (!empty($principalInfo['phone']) && $principalInfo['phone'] !== ($waliInfo['phone'] ?? null)) {
                        $schoolName = $principalInfo['school_name'] ?? 'Unit Sekolah';
                        $principalMessage = "🚨 *LAPORAN KEBISINGAN KELAS (KONSTAN BERLANJUT)* 🚨\n\n"
                            . "Yth. Bapak/Ibu *{$principalInfo['name']}* ({$principalInfo['title']}),\n"
                            . "Sistem sensor IoT mendeteksi terjadinya kebisingan tingkat tinggi yang berlangsung *KONSTAN selama lebih dari 1 menit* ({$durasiDetik} detik) pada unit sekolah Anda:\n\n"
                            . "🏫 *Unit/Kelas:* {$className}\n"
                            . "📍 *Lokasi Sensor:* {$location}\n"
                            . "📊 *Tingkat Kebisingan:* *{$calibratedDb} dB* (Batas Bahaya: {$device->threshold_red} dB)\n"
                            . "🔴 *Indikator:* *LAMPU LED MERAH & BUZZER ALARM AKTIF*\n"
                            . "👤 *Wali Kelas Terkait:* {$waliInfo['name']}\n"
                            . "🕒 *Waktu Terdeteksi:* " . Carbon::now()->format('H:i:s d/m/Y') . " WIB\n\n"
                            . "Peringatan darurat ini juga telah otomatis diteruskan ke WhatsApp Wali Kelas terkait untuk segera mengondisikan ketertiban belajar mengajar.\n\n"
                            . "_Sistem Pengawasan KBM Otomatis - SIM PembdaHUB_";

                        try {
                            /** @var WhatsAppService $waService */
                            $waService = app(WhatsAppService::class);
                            $resKepsek = $waService->sendMessage($principalInfo['phone'], $principalMessage);
                            if (!empty($resKepsek['success'])) {
                                $hasSentAny = true;
                            }
                        } catch (\Throwable $e) {
                            Log::error("NoiseControl WA Alert to Principal failed: " . $e->getMessage());
                        }
                    }

                    if ($hasSentAny) {
                        $alertStatus = 'sent';
                        $alertSent = true;
                        $device->last_alert_at = Carbon::now();
                    } else if (empty($waliInfo['phone']) && empty($principalInfo['phone'])) {
                        $alertStatus = 'no_phone';
                    } else {
                        $alertStatus = 'failed';
                    }
                } else {
                    $alertStatus = 'cooldown';
                }
            } else {
                // Bising sesaat / belum mencapai syarat durasi konstan (misal masih 10s dari 60s)
                $alertStatus = 'pending_duration';
            }
        }

        $device->save();

        // 5. Simpan Log Data
        // Catat log jika: status bahaya, alert terkirim, jeda >= 5 detik, atau ada perubahan desibel >= 1.5 dB
        $shouldLog = ($status === 'danger') || ($alertStatus !== null);
        if (!$shouldLog) {
            $lastLog = NoiseLog::where('noise_device_id', $device->id)->latest('id')->first();
            if (!$lastLog || $lastLog->created_at->diffInSeconds(Carbon::now()) >= 5 || abs((float)$lastLog->db_level - (float)$calibratedDb) >= 1.5 || $lastLog->status !== $status) {
                $shouldLog = true;
            }
        }

        if ($shouldLog) {
            NoiseLog::create([
                'noise_device_id'    => $device->id,
                'classroom_id'       => $device->classroom_id,
                'db_level'           => $calibratedDb,
                'peak_db'            => $calibratedPeak,
                'status'             => $status,
                'is_alert_triggered' => ($status === 'danger' && in_array($alertStatus, ['sent', 'failed', 'cooldown'])),
                'alert_recipient'    => $recipientStr,
                'alert_message'      => $alertMessage,
                'alert_status'       => $alertStatus,
                'created_at'         => Carbon::now(),
            ]);
        }

        // 6. Response JSON ke ESP32
        return response()->json([
            'status'             => 'success',
            'device_id'          => $device->id,
            'device_uid'         => $device->device_uid,
            'classroom'          => $device->classroom?->class_name ?? 'Umum',
            'current_db'         => $calibratedDb,
            'status_level'       => $status, // 'safe', 'warning', 'danger'
            'threshold_green'    => (float) $device->threshold_green,
            'threshold_yellow'   => (float) $device->threshold_yellow,
            'threshold_red'      => (float) $device->threshold_red,
            'alert_delay'        => $requiredDelay,
            'danger_duration'    => $dangerDuration,
            'alert_triggered'    => ($status === 'danger' && ($dangerDuration >= $requiredDelay || $request->boolean('force_alert'))),
            'alert_sent'         => $alertSent,
            'alert_status'       => $alertStatus,
            'wali_name'          => $waliInfo['name'],
            'cooldown_remaining' => $device->getRemainingCooldownMinutes(),
            'server_time'        => Carbon::now()->format('H:i:s'),
        ]);
    }

    /**
     * AJAX Live Data Polling untuk Browser Dashboard
     */
    public function getLiveData(Request $request)
    {
        $deviceId = $request->query('device_id');
        
        $devices = NoiseDevice::with(['classroom.homeroomTeacher', 'school'])->get();
        $activeDevice = $devices->where('id', $deviceId)->first() ?: $devices->first();

        $sparklineData = [];
        $recentLogs = [];

        if ($activeDevice) {
            $logs = NoiseLog::where('noise_device_id', $activeDevice->id)
                ->latest('id')
                ->limit(20)
                ->get()
                ->reverse()
                ->values();

            foreach ($logs as $l) {
                $sparklineData[] = [
                    'time' => $l->created_at->format('H:i:s'),
                    'db'   => (float) $l->db_level,
                    'status' => $l->status,
                ];
            }

            $recentLogs = NoiseLog::where('noise_device_id', $activeDevice->id)
                ->latest('id')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id'         => $log->id,
                        'time'       => $log->created_at->format('H:i:s'),
                        'db'         => (float) $log->db_level,
                        'status'     => $log->status,
                        'alert'      => $log->is_alert_triggered,
                        'alert_status' => $log->alert_status,
                        'recipient'  => $log->alert_recipient,
                    ];
                });
        }

        $wali = $activeDevice ? $activeDevice->getEffectiveWaliKelas() : null;

        return response()->json([
            'status'        => 'success',
            'server_time'   => Carbon::now()->format('H:i:s'),
            'active_device' => $activeDevice ? [
                'id'                 => $activeDevice->id,
                'name'               => $activeDevice->name,
                'device_uid'         => $activeDevice->device_uid,
                'is_online'          => $activeDevice->isOnline(),
                'last_db'            => (float) ($activeDevice->last_db ?? 0),
                'peak_db'            => (float) ($activeDevice->peak_db ?? 0),
                'last_status'        => $activeDevice->last_status ?? 'safe',
                'threshold_green'    => (float) $activeDevice->threshold_green,
                'threshold_yellow'   => (float) $activeDevice->threshold_yellow,
                'threshold_red'      => (float) $activeDevice->threshold_red,
                'alert_delay_seconds'=> (int) ($activeDevice->alert_delay_seconds ?: 60),
                'cooldown_remaining' => $activeDevice->getRemainingCooldownMinutes(),
                'last_seen_diff'     => $activeDevice->last_seen_at ? $activeDevice->last_seen_at->diffForHumans() : 'Belum pernah',
                'classroom_name'     => $activeDevice->classroom?->class_name ?? 'Umum',
                'school_name'        => $activeDevice->school?->name ?? 'Perguruan Pembda',
                'wali_name'          => $wali['name'] ?? '-',
                'wali_phone'         => $wali['phone'] ?? '-',
            ] : null,
            'sparkline'     => $sparklineData,
            'recent_logs'   => $recentLogs,
            'stats'         => [
                'total_devices'  => $devices->count(),
                'online_devices' => $devices->filter(fn($d) => $d->isOnline())->count(),
                'alerts_today'   => NoiseLog::where('is_alert_triggered', true)->whereDate('created_at', Carbon::today())->count(),
            ],
        ]);
    }
}
