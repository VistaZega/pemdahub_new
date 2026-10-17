<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoiseLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'noise_logs';

    protected $fillable = [
        'noise_device_id',
        'classroom_id',
        'db_level',
        'peak_db',
        'status',
        'is_alert_triggered',
        'alert_recipient',
        'alert_message',
        'alert_status',
        'created_at',
    ];

    protected $casts = [
        'db_level' => 'float',
        'peak_db' => 'float',
        'is_alert_triggered' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship to Device
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(NoiseDevice::class, 'noise_device_id');
    }

    /**
     * Relationship to Classroom
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
