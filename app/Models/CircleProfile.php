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
     * Palet Warna & Styling Tema Profil
     */
    public function getThemeStylesAttribute(): array
    {
        $themes = [
            'pink' => [
                'name' => 'Sakura Pastel',
                'border' => 'border-pink-300',
                'bg_subtle' => 'bg-pink-50',
                'accent' => 'text-pink-600',
                'accent_bg' => 'bg-pink-500',
                'gradient' => 'from-pink-100 via-rose-50 to-amber-50',
                'ring' => 'ring-pink-300',
                'badge' => 'bg-pink-100 text-pink-800 border-pink-200',
                'hex' => '#ec4899',
            ],
            'indigo' => [
                'name' => 'Cyber Indigo',
                'border' => 'border-indigo-300',
                'bg_subtle' => 'bg-indigo-50',
                'accent' => 'text-indigo-600',
                'accent_bg' => 'bg-indigo-500',
                'gradient' => 'from-indigo-100 via-sky-50 to-purple-50',
                'ring' => 'ring-indigo-300',
                'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                'hex' => '#6366f1',
            ],
            'amber' => [
                'name' => 'Golden Sunset',
                'border' => 'border-amber-300',
                'bg_subtle' => 'bg-amber-50',
                'accent' => 'text-amber-600',
                'accent_bg' => 'bg-amber-500',
                'gradient' => 'from-amber-100 via-orange-50 to-yellow-50',
                'ring' => 'ring-amber-300',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'hex' => '#f59e0b',
            ],
            'emerald' => [
                'name' => 'Mint Forest',
                'border' => 'border-emerald-300',
                'bg_subtle' => 'bg-emerald-50',
                'accent' => 'text-emerald-600',
                'accent_bg' => 'bg-emerald-500',
                'gradient' => 'from-emerald-100 via-teal-50 to-cyan-50',
                'ring' => 'ring-emerald-300',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'hex' => '#10b981',
            ],
            'sky' => [
                'name' => 'Ocean Breeze',
                'border' => 'border-sky-300',
                'bg_subtle' => 'bg-sky-50',
                'accent' => 'text-sky-600',
                'accent_bg' => 'bg-sky-500',
                'gradient' => 'from-sky-100 via-cyan-50 to-indigo-50',
                'ring' => 'ring-sky-300',
                'badge' => 'bg-sky-100 text-sky-800 border-sky-200',
                'hex' => '#0ea5e9',
            ],
            'rose' => [
                'name' => 'Crimson Velvet',
                'border' => 'border-rose-300',
                'bg_subtle' => 'bg-rose-50',
                'accent' => 'text-rose-600',
                'accent_bg' => 'bg-rose-500',
                'gradient' => 'from-rose-100 via-pink-50 to-amber-50',
                'ring' => 'ring-rose-300',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-200',
                'hex' => '#f43f5e',
            ],
            'purple' => [
                'name' => 'Royal Lavender',
                'border' => 'border-purple-300',
                'bg_subtle' => 'bg-purple-50',
                'accent' => 'text-purple-600',
                'accent_bg' => 'bg-purple-500',
                'gradient' => 'from-purple-100 via-fuchsia-50 to-pink-50',
                'ring' => 'ring-purple-300',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
                'hex' => '#a855f7',
            ],
            'dark' => [
                'name' => 'Midnight Obsidian',
                'border' => 'border-slate-700',
                'bg_subtle' => 'bg-slate-900',
                'accent' => 'text-cyan-400',
                'accent_bg' => 'bg-slate-800',
                'gradient' => 'from-slate-900 via-slate-800 to-cyan-950',
                'ring' => 'ring-cyan-500',
                'badge' => 'bg-slate-800 text-cyan-300 border-slate-700',
                'hex' => '#0f172a',
            ],
        ];

        return $themes[$this->theme_color] ?? $themes['pink'];
    }

    /**
     * Parsing dan Resolusi URL Profile Anthem
     */
    public function getAnthemDataAttribute(): ?array
    {
        $url = trim($this->anthem_url ?? '');
        if (empty($url)) {
            return null;
        }

        // 1. Preset Lagu
        $presets = [
            'preset:mars_pembda' => [
                'type' => 'audio',
                'title' => 'Hymne & Mars Perguruan Pembda',
                'artist' => 'Paduan Suara Pembda Nias',
                'cover_icon' => '🏫',
                'stream_url' => url('/audio/mars-pembda.mp4'),
            ],
            'preset:lofi' => [
                'type' => 'audio',
                'title' => 'Lofi Chill & Focus',
                'artist' => 'Pembda Lo-Fi Beats',
                'cover_icon' => '🎧',
                'stream_url' => 'https://dn801203.us.archive.org/0/items/FREE_background_music_dhalius/backsound.mp3',
            ],
            'preset:semangat' => [
                'type' => 'audio',
                'title' => 'Semangat Pagi Ceria',
                'artist' => 'Pembda Acoustic Morning',
                'cover_icon' => '⚡',
                'stream_url' => 'https://dn710807.ca.archive.org/0/items/testmp3testfile/mpthreetest.mp3',
            ],
            'preset:piano' => [
                'type' => 'audio',
                'title' => 'Serenade Piano Inspirasi',
                'artist' => 'Pembda Classical Melody',
                'cover_icon' => '🎹',
                'stream_url' => 'https://dn801203.us.archive.org/0/items/FREE_background_music_dhalius/backsound.mp3',
            ],
            'preset:synthwave' => [
                'type' => 'audio',
                'title' => 'Cyber Neo Synthwave',
                'artist' => 'Pembda Future Sound',
                'cover_icon' => '🛠️',
                'stream_url' => 'https://dn801203.us.archive.org/0/items/FREE_background_music_dhalius/backsound.mp3',
            ],
        ];

        if (isset($presets[$url])) {
            return $presets[$url];
        }

        // 2. Spotify Embed (track)
        if (preg_match('/open\.spotify\.com\/(?:intl-[a-z]+\/)?track\/([a-zA-Z0-9]+)/i', $url, $m)) {
            return [
                'type' => 'spotify',
                'track_id' => $m[1],
                'title' => 'Spotify Track',
                'artist' => 'Spotify Player',
                'embed_url' => "https://open.spotify.com/embed/track/{$m[1]}?utm_source=generator&theme=0",
            ];
        }

        // 3. YouTube Audio/Video Embed
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/i', $url, $m)) {
            return [
                'type' => 'youtube',
                'video_id' => $m[1],
                'title' => 'YouTube Audio',
                'artist' => 'YouTube Stream',
                'embed_url' => "https://www.youtube.com/embed/{$m[1]}?enablejsapi=1&origin=" . urlencode(url('/')),
            ];
        }

        // 4. Direct Audio URL (.mp3 / .wav / .m4a / audio stream)
        if (preg_match('/\.(mp3|wav|ogg|m4a|aac)(\?.*)?$/i', $url) || filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'type' => 'audio',
                'title' => 'Lagu Favorit Profil',
                'artist' => 'Pilihan ' . ($this->nickname ?? 'Warga Pembda'),
                'cover_icon' => '🎵',
                'stream_url' => $url,
            ];
        }

        return null;
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
