<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationPreference extends Model
{
    use HasFactory;

    protected $table = 'push_notification_preferences';

    protected $fillable = [
        'user_id',
        'push_enabled',
        'push_lms',
        'push_cbt',
        'push_forum',
        'push_school',
    ];

    protected $casts = [
        'push_enabled' => 'boolean',
        'push_lms' => 'boolean',
        'push_cbt' => 'boolean',
        'push_forum' => 'boolean',
        'push_school' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create default preference for a user
     */
    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'push_enabled' => true,
                'push_lms' => true,
                'push_cbt' => true,
                'push_forum' => true,
                'push_school' => true,
            ]
        );
    }
}
