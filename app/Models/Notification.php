<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'school_id',
        'title',
        'message',
        'type',
        'related_model',
        'related_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected const TYPES = [
        'info' => 'Informasi',
        'warning' => 'Peringatan',
        'alert' => 'Notifikasi Penting',
        'grade' => 'Nilai',
        'bill' => 'Tagihan',
        'assignment' => 'Tugas',
        'attendance' => 'Kehadiran',
        'forum' => 'Pembda Space',
    ];

    /**
     * Relationship: Notification belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Notification belongs to School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get type label
     */
    public function getTypeLabel()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Scope: Get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get notifications for user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get recent notifications
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Get notifications by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
