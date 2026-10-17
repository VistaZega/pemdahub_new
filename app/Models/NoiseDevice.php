<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoiseDevice extends Model
{
    use HasFactory;

    protected $table = 'noise_devices';

    protected $fillable = [
        'name',
        'device_uid',
        'device_token',
        'school_id',
        'classroom_id',
        'location_note',
        'custom_recipient_name',
        'custom_phone',
        'threshold_green',
        'threshold_yellow',
        'threshold_red',
        'calibration_offset',
        'alert_delay_seconds',
        'cooldown_minutes',
        'wa_enabled',
        'is_active',
        'last_db',
        'peak_db',
        'last_status',
        'last_seen_at',
        'last_alert_at',
        'ip_address',
        'firmware_version',
        'notes',
    ];

    protected $casts = [
        'threshold_green' => 'float',
        'threshold_yellow' => 'float',
        'threshold_red' => 'float',
        'calibration_offset' => 'float',
        'last_db' => 'float',
        'peak_db' => 'float',
        'alert_delay_seconds' => 'integer',
        'cooldown_minutes' => 'integer',
        'wa_enabled' => 'boolean',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_alert_at' => 'datetime',
    ];

    /**
     * Relationship to School
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relationship to Classroom
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relationship to Logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NoiseLog::class, 'noise_device_id');
    }

    /**
     * Cek apakah perangkat sedang online (heartbeat < 60 detik yang lalu)
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->greaterThan(now()->subSeconds(60));
    }

    /**
     * Dapatkan informasi penerima notifikasi (Wali Kelas otomatis atau manual override)
     */
    public function getEffectiveWaliKelas(): array
    {
        // 1. Cek Custom Override terlebih dahulu
        if (!empty($this->custom_phone)) {
            return [
                'name' => $this->custom_recipient_name ?: 'Pengawas/Wali Kelas (Manual)',
                'phone' => $this->custom_phone,
                'source' => 'custom',
                'teacher' => null,
            ];
        }

        // 2. Ambil dari relasi Classroom -> homeroomTeacher
        if ($this->classroom && $this->classroom->homeroomTeacher) {
            $teacher = $this->classroom->homeroomTeacher;
            $phone = $teacher->phone ?? $teacher->employee?->phone ?? $teacher->user?->phone;
            
            return [
                'name' => $teacher->full_name,
                'phone' => $phone ?: '',
                'source' => 'wali_kelas',
                'teacher' => $teacher,
            ];
        }

        return [
            'name' => $this->custom_recipient_name ?: 'Belum Ada Wali Kelas Terpasang',
            'phone' => '',
            'source' => 'none',
            'teacher' => null,
        ];
    }

    /**
     * Cek apakah sistem diizinkan mengirim notifikasi WhatsApp saat kondisi bising
     */
    public function canTriggerAlert(): bool
    {
        if (!$this->wa_enabled || !$this->is_active) {
            return false;
        }

        if (!$this->last_alert_at) {
            return true;
        }

        $cooldown = max(1, (int) $this->cooldown_minutes);
        return $this->last_alert_at->diffInMinutes(now()) >= $cooldown;
    }

    /**
     * Sisa menit cooldown sebelum boleh kirim WA lagi
     */
    public function getRemainingCooldownMinutes(): int
    {
        if (!$this->last_alert_at) {
            return 0;
        }

        $elapsed = $this->last_alert_at->diffInMinutes(now());
        $cooldown = max(1, (int) $this->cooldown_minutes);

        return max(0, $cooldown - $elapsed);
    }

    /**
     * Tentukan status desibel berdasarkan ambang batas
     */
    public function evaluateStatus(float $db): string
    {
        if ($db >= $this->threshold_red) {
            return 'danger';
        }
        if ($db >= $this->threshold_yellow) {
            return 'warning';
        }
        return 'safe';
    }
}
