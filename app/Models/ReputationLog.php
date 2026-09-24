<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Reputation;
use App\Models\Badge;
use App\Models\UserBadge;

class ReputationLog extends Model
{
    use HasFactory;

    public $timestamps = false; // We use created_at in migration manually or leave it as default

    protected $fillable = [
        'user_id',
        'points',
        'category',
        'reference_type',
        'reference_id',
        'description',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    /**
     * Relationship: Log belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper: Record points
     */
    public static function log($userId, $points, $category, $description = '', $ref = null): void
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // Khusus akun Bapak Yulianus Zega, S.Kom, M.Pd.T (Ketua Yayasan) tidak mengumpulkan poin kompetitif
        if ($user->username === 'yulzega' || str_contains(strtolower($user->name ?? ''), 'yulianus zega')) {
            return;
        }

        // Check if we are updating an existing log for this reference
        $existingLog = null;
        if ($ref) {
            $existingLog = self::where('user_id', $userId)
                ->where('reference_type', get_class($ref))
                ->where('reference_id', $ref->id)
                ->first();
        }

        $oldPoints = $existingLog ? $existingLog->points : 0;
        $diff = $points - $oldPoints;

        if ($existingLog) {
            $existingLog->update([
                'points' => $points,
                'description' => $description,
                'created_at' => now(),
            ]);
        } else {
            $logData = [
                'user_id' => $userId,
                'points' => $points,
                'category' => $category,
                'description' => $description,
                'created_at' => now(),
            ];
            if ($ref) {
                $logData['reference_type'] = get_class($ref);
                $logData['reference_id'] = $ref->id;
            }
            self::create($logData);
        }

        // Update total points in Reputation table
        $rep = Reputation::firstOrCreate(
            ['user_id' => $userId],
            ['total_points' => 0, 'level_name' => 'Newbie']
        );
        $rep->total_points += $diff;
        $rep->updateLevel();
        $rep->save();

        // Badge Hook: Check for new achievements
        try {
            $user = User::find($userId);
            if ($user) {
                $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();
                $potentialBadges = Badge::where('is_active', true)
                    ->whereNotIn('id', $earnedBadgeIds)
                    ->get();
                
                foreach ($potentialBadges as $badge) {
                    if ($badge->checkEligibility($user)) {
                        UserBadge::create([
                            'user_id' => $user->id,
                            'badge_id' => $badge->id,
                            'earned_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Badge award failed: " . $e->getMessage());
        }
    }

    /**
     * Helper: Remove log and reverse points
     */
    public static function removeLog($userId, $referenceType, $referenceId): void
    {
        $log = self::where('user_id', $userId)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->first();

        if ($log) {
            $pointsToSubtract = $log->points;
            
            // Subtract from total points in Reputation table
            $rep = Reputation::where('user_id', $userId)->first();
            if ($rep) {
                $rep->total_points -= $pointsToSubtract;
                $rep->updateLevel();
                $rep->save();
            }

            $log->delete();
        }
    }
}
