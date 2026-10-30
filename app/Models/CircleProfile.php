<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CircleProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'nickname',
        'bio',
        'theme_color',
        'anthem_url',
        'mood_text',
        'mood_emoji',
        'banner_path',
        'is_public',
        'views_count',
        'social_links',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'views_count' => 'integer',
        'social_links' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dapatkan atau buat profil circle secara otomatis untuk user.
     */
    public static function getOrCreateForUser(User $user): self
    {
        $profile = self::where('user_id', $user->id)->first();
        if ($profile) {
            return $profile;
        }

        // Generate username unik yang keren dari nama atau username akun
        $base = $user->username ? Str::slug($user->username) : Str::slug($user->name ?? 'user');
        if (is_numeric($base) || strlen($base) < 3) {
            $base = Str::slug($user->name ?? 'siswa');
        }
        $username = $base;
        $counter = 1;
        while (self::where('username', $username)->exists() || empty($username)) {
            $username = $base . $counter;
            $counter++;
        }

        return self::create([
            'user_id' => $user->id,
            'username' => $username,
            'nickname' => explode(' ', trim($user->name ?? 'Warga Pembda'))[0],
            'bio' => $user->role === 'siswa' 
                ? 'Siswa aktif ' . ($user->school?->name ?? 'Perguruan Pembda') . ' ✨'
                : ($user->role === 'guru' ? 'Pendidik & Mentor Inspiratif di ' . ($user->school?->name ?? 'Perguruan Pembda') : 'Warga Perguruan Pembda'),
            'theme_color' => 'indigo',
            'mood_emoji' => '🔥',
            'mood_text' => 'Semangat berkreasi di Pembda!',
            'is_public' => true,
        ]);
    }
}
