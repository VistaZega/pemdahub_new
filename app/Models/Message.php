<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'recipient_id',
        'subject',
        'content',
        'message',
        'attachment_path',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relationship: Message belongs to Sender (User)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relationship: Message belongs to Receiver (User)
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Relationship: Alias Message belongs to Recipient (User)
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Fallback accessor if accessed via content or message
     */
    public function getContentAttribute()
    {
        return $this->attributes['message'] ?? ($this->attributes['content'] ?? '');
    }

    /**
     * Fallback accessor for receiver_id / recipient_id
     */
    public function getRecipientIdAttribute()
    {
        return $this->attributes['receiver_id'] ?? ($this->attributes['recipient_id'] ?? null);
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Scope: Get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get messages for recipient/receiver
     */
    public function scopeForRecipient($query, $userId)
    {
        return $query->where('receiver_id', $userId);
    }

    /**
     * Scope: Get messages from sender
     */
    public function scopeFromSender($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Scope: Get recent messages
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
