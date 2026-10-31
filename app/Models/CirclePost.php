<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CirclePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_type',
        'tab_category',
        'is_gallery',
        'gallery_category',
        'media_layout',
        'community_id',
        'title',
        'slug',
        'content',
        'mood_emoji',
        'mood_text',
        'location_tag',
        'event_name',
        'event_date',
        'latitude',
        'longitude',
        'is_live',
        'live_status',
        'live_stream_url',
        'live_updates',
        'tagged_user_ids',
        'school_id',
        'major_code',
        'visibility',
        'is_pinned',
        'expires_at',
        'likes_count',
        'comments_count',
        'views_count',
    ];

    protected $casts = [
        'is_gallery' => 'boolean',
        'event_date' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_live' => 'boolean',
        'live_updates' => 'array',
        'tagged_user_ids' => 'array',
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'views_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug) && !empty($post->title)) {
                $post->slug = Str::slug($post->title) . '-' . Str::random(6);
            } elseif (empty($post->slug)) {
                $post->slug = 'post-' . Str::random(10);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function community()
    {
        return $this->belongsTo(CircleCommunity::class, 'community_id');
    }

    public function media()
    {
        return $this->hasMany(CirclePostMedia::class)->orderBy('order', 'asc');
    }

    public function reactions()
    {
        return $this->hasMany(CircleReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(CircleComment::class)->whereNull('parent_id')->orderBy('created_at', 'asc');
    }

    public function skillEndorsements()
    {
        return $this->hasMany(CircleSkillEndorsement::class);
    }

    public function poll()
    {
        return $this->hasOne(CirclePoll::class, 'circle_post_id')->with(['options', 'votes']);
    }

    /**
     * Dapatkan daftar User yang di-tag
     */
    public function taggedUsers()
    {
        if (empty($this->tagged_user_ids)) {
            return collect();
        }
        return User::whereIn('id', $this->tagged_user_ids)->get();
    }

    /**
     * Scope untuk post aktif (Story yang belum expired atau post biasa)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope untuk publik atau circle
     */
    public function scopePublicVisible($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Dapatkan URL Google Maps jika koordinat GPS atau lokasi tersedia
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        if ($this->location_tag) {
            return "https://www.google.com/maps/search/?api=1&query=" . urlencode($this->location_tag);
        }
        return null;
    }

    /**
     * Konversi URL Live Stream (YouTube Live / Video) menjadi Embed URL
     */
    public function getLiveEmbedUrlAttribute(): ?string
    {
        if (empty($this->live_stream_url)) {
            return null;
        }
        $url = trim($this->live_stream_url);
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|live\/|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=0&rel=0';
        }
        return null;
    }

    /**
     * Susun payload & kalimat siaran (share) yang kaya, relevan (Nama Lomba/Kegiatan, Waktu, Tempat, GPS, Berita & Gambar/Logo),
     * dan bebas dari duplikasi URL.
     */
    public function getSharePayloadAttribute(): array
    {
        $authorName = $this->user?->name ?? 'Warga Pembda';
        $schoolName = $this->school?->name ?? ($this->user?->school?->name ?? 'Perguruan Pembda Nias');

        // Tentukan Kategori & Headline Siaran
        if ($this->is_live || $this->post_type === 'live') {
            $badge = '🔴 LIPUTAN LIVE PEMBDA CIRCLE';
            $headline = $this->event_name ?: ($this->title ?: 'Siaran Langsung Kegiatan ' . $schoolName);
        } elseif ($this->post_type === 'report' || !empty($this->event_name) || !empty($this->latitude)) {
            $badge = '🏆 LAPORAN KEGIATAN & LOMBA — PEMBDA CIRCLE';
            $headline = $this->event_name ?: ($this->title ?: 'Laporan Kegiatan ' . $schoolName);
        } elseif ($this->tab_category === 'portfolio') {
            $badge = '🚀 KARYA & INOVASI SISWA — PEMBDA CIRCLE';
            $headline = $this->title ?: ('Karya Inovasi oleh ' . $authorName);
        } elseif ($this->is_gallery || $this->tab_category === 'gallery') {
            $badge = '📸 GALERI KARYA DIGITAL — PEMBDA CIRCLE';
            $headline = $this->title ?: ('Koleksi Galeri ' . $authorName);
        } else {
            $badge = '✨ KABAR & MOMEN SERU — PEMBDA CIRCLE';
            $headline = $this->event_name ?: ($this->title ?: ('Kabar Terkini dari ' . $authorName));
        }

        // Format Waktu
        $timeFormatted = $this->event_date
            ? $this->event_date->translatedFormat('l, d F Y • H:i') . ' WIB'
            : ($this->created_at ? $this->created_at->translatedFormat('l, d F Y • H:i') . ' WIB' : now()->translatedFormat('l, d F Y'));

        // Format Tempat / Lokasi
        $locationFormatted = $this->location_tag ?: $schoolName;

        // Ringkasan Berita
        $cleanContent = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->content)));
        $newsSnippet = Str::limit($cleanContent, 240);

        // Gambar Dokumentasi Utama atau Fallback Logo PembdaCIRCLE
        $firstImage = $this->media?->firstWhere('media_type', 'image');
        $logoUrl = asset('images/circle/pembda-circle-logo.png');
        $imageUrl = $firstImage ? $firstImage->url : $logoUrl;

        // URL Bersih (Tanpa #hash agar bot WhatsApp/Telegram selalu menampilkan preview gambar & tidak duplikat)
        $shareUrl = route('circle.feed', ['post' => $this->id]);

        // Susun Kalimat Siaran (Format WhatsApp & Social Media yang Rapi & Menarik)
        $lines = [];
        $lines[] = "*{$badge}*";
        if ($this->event_name || $this->title) {
            $lines[] = "📌 *Kegiatan/Topik:* {$headline}";
        }
        $lines[] = "🗓️ *Waktu:* {$timeFormatted}";
        $lines[] = "📍 *Tempat:* {$locationFormatted}";
        $lines[] = "👤 *Oleh:* {$authorName} ({$schoolName})";

        if ($newsSnippet !== '') {
            $lines[] = "";
            $lines[] = "📰 *Berita / Kabar:*";
            $lines[] = "\"{$newsSnippet}\"";
        }

        if ($this->latitude && $this->longitude) {
            $lines[] = "";
            $lines[] = "🗺️ *Titik GPS Lokasi:* https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }

        $lines[] = "";
        $lines[] = "🔗 *Lihat Foto & Liputan Lengkap di PembdaCIRCLE:*";
        $lines[] = $shareUrl;

        $fullMessage = implode("\n", $lines);

        // Versi tanpa URL di ujung khusus untuk navigator.share({ text, url }) agar OS tidak mencetak URL 2 kali
        $linesWithoutUrl = array_slice($lines, 0, -1);
        $messageWithoutUrl = implode("\n", $linesWithoutUrl);

        return [
            'id' => $this->id,
            'badge' => $badge,
            'headline' => $headline,
            'event_name' => $this->event_name,
            'time' => $timeFormatted,
            'location' => $locationFormatted,
            'author' => $authorName,
            'school' => $schoolName,
            'news' => $newsSnippet,
            'gps_url' => ($this->latitude && $this->longitude) ? "https://www.google.com/maps?q={$this->latitude},{$this->longitude}" : null,
            'image_url' => $imageUrl,
            'has_post_image' => (bool) $firstImage,
            'logo_url' => $logoUrl,
            'url' => $shareUrl,
            'full_message' => $fullMessage,
            'message_without_url' => $messageWithoutUrl,
        ];
    }
}
