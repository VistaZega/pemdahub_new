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

    public function visitors()
    {
        return $this->hasMany(CircleProfileVisitor::class, 'circle_profile_id')->latest('last_visited_at');
    }

    public function guestbooks()
    {
        return $this->hasMany(CircleProfileGuestbook::class, 'circle_profile_id')->orderByDesc('is_pinned')->latest();
    }

    /**
     * Predikat & Level Popularitas Profil Berdasarkan Kunjungan
     */
    public function getVisitorLevelAttribute(): array
    {
        $count = $this->views_count ?? 0;
        if ($count >= 500) {
            return [
                'name' => 'Idola Kampus',
                'badge' => '👑',
                'bg' => 'bg-purple-100',
                'text' => 'text-purple-900',
                'border' => 'border-purple-300',
                'desc' => 'Paling hits & jadi panutan talenta'
            ];
        } elseif ($count >= 150) {
            return [
                'name' => 'Bintang Pembda',
                'badge' => '🔥',
                'bg' => 'bg-amber-100',
                'text' => 'text-amber-900',
                'border' => 'border-amber-300',
                'desc' => 'Dinding ramai dikunjungi & aktif berkarya'
            ];
        } elseif ($count >= 40) {
            return [
                'name' => 'Eksplorer Aktif',
                'badge' => '✨',
                'bg' => 'bg-sky-100',
                'text' => 'text-sky-900',
                'border' => 'border-sky-300',
                'desc' => 'Mulai dikenal dan sering disinggahi kawan'
            ];
        }

        return [
            'name' => 'Warga Baru',
            'badge' => '🌱',
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-800',
            'border' => 'border-emerald-200',
            'desc' => 'Baru merintis jejak di PembdaCIRCLE'
        ];
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
