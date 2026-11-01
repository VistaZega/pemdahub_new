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
        'interactive_type',
        'interactive_data',
        'collaborator_user_id',
        'is_solved',
        'solved_by_user_id',
        'solved_at',
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
        'attachment_path',
        'attachment_name',
        'reference_type',
        'reference_id',
        'recruitment_meta',
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
        'interactive_data' => 'array',
        'is_solved' => 'boolean',
        'solved_at' => 'datetime',
        'is_gallery' => 'boolean',
        'event_date' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_live' => 'boolean',
        'live_updates' => 'array',
        'recruitment_meta' => 'array',
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

    public function collaborator()
    {
        return $this->belongsTo(User::class, 'collaborator_user_id');
    }

    public function solvedBy()
    {
        return $this->belongsTo(User::class, 'solved_by_user_id');
    }

    public function riddleGuesses()
    {
        return $this->hasMany(CircleRiddleGuess::class, 'circle_post_id')->orderBy('created_at', 'asc');
    }

    public function battleVotes()
    {
        return $this->hasMany(CircleBattleVote::class, 'circle_post_id');
    }

    public function isRiddle(): bool
    {
        return $this->interactive_type === 'riddle';
    }

    public function isBattle(): bool
    {
        return $this->interactive_type === 'battle';
    }

    public function isCollab(): bool
    {
        return $this->interactive_type === 'collab' && !empty($this->collaborator_user_id);
    }

    public function isPantun(): bool
    {
        return $this->interactive_type === 'pantun';
    }

    /**
     * Ringkasan Statistik Battle 1 vs 1 (Total vote, Persentase, Vote Pengguna)
     */
    public function getBattleStats(?int $userId = null): array
    {
        $votesA = $this->battleVotes->where('chosen_option', 'a')->count();
        $votesB = $this->battleVotes->where('chosen_option', 'b')->count();
        $total = $votesA + $votesB;

        $pctA = $total > 0 ? round(($votesA / $total) * 100) : 50;
        $pctB = $total > 0 ? (100 - $pctA) : 50;

        $myVote = null;
        if ($userId) {
            $userVote = $this->battleVotes->firstWhere('user_id', $userId);
            $myVote = $userVote?->chosen_option;
        }

        return [
            'votes_a' => $votesA,
            'votes_b' => $votesB,
            'total'   => $total,
            'pct_a'   => $pctA,
            'pct_b'   => $pctB,
            'my_vote' => $myVote,
        ];
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
     * Scope untuk menyaring konten bermakna & membuang postingan sampah/kosong (Anti-Ghost Post)
     */
    public function scopeMeaningful($query)
    {
        return $query->where(function ($q) {
            // 1. Laporan kegiatan harus memiliki media dokumentasi atau ringkasan konten >= 15 karakter
            $q->where(function ($sub) {
                $sub->where('post_type', 'report')
                    ->where(function ($r) {
                        $r->whereHas('media')
                          ->orWhereNotNull('attachment_path')
                          ->orWhere(function ($rc) {
                              $rc->whereNotNull('content')
                                 ->whereRaw("CHAR_LENGTH(TRIM(content)) >= 15");
                          });
                    });
            })
            // 2. Galeri media wajib memiliki berkas media atau lampiran
            ->orWhere(function ($sub) {
                $sub->where(function ($g) {
                    $g->where('tab_category', 'gallery')
                      ->orWhere('post_type', 'media');
                })
                ->where(function ($m) {
                    $m->whereHas('media')
                      ->orWhereNotNull('attachment_path');
                });
            })
            // 3. Fitur interaktif (teka-teki, battle, pantun, collab)
            ->orWhere(function ($sub) {
                $sub->whereNotNull('interactive_type')
                    ->where('interactive_type', '!=', 'none');
            })
            // 4. Postingan referensi portofolio atau polling
            ->orWhereNotNull('reference_id')
            ->orWhereHas('poll')
            // 5. Postingan umum / status (harus ada media/lampiran ATAU konten teks bermakna >= 3 karakter)
            ->orWhere(function ($sub) {
                $sub->where(function ($s) {
                    $s->whereNotIn('post_type', ['report', 'media'])
                      ->where('tab_category', '!=', 'gallery');
                })
                ->where(function ($c) {
                    $c->whereHas('media')
                      ->orWhereNotNull('attachment_path')
                      ->orWhere(function ($tc) {
                          $tc->whereNotNull('content')
                             ->whereRaw("CHAR_LENGTH(TRIM(content)) >= 3")
                             ->whereRaw("TRIM(content) NOT IN ('📢 Laporan Langsung Kegiatan:', 'Laporan Kegiatan:')");
                      });
                });
            });
        });
    }

    /**
     * URL Berkas Lampiran Modul / Dokumen (PDF, Word, Excel, ZIP)
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (empty($this->attachment_path)) {
            return null;
        }
        return asset('storage/' . ltrim($this->attachment_path, '/'));
    }

    /**
     * Resolve Kartu Karya Akademik Terverifikasi (LMS Submission, Final Project, CBT Result, Ekskul Activity)
     */
    public function getVerifiedReferenceCardAttribute(): ?array
    {
        if (empty($this->reference_type) || empty($this->reference_id)) {
            return null;
        }

        try {
            if ($this->reference_type === 'final_project' && class_exists(FinalProject::class)) {
                $fp = FinalProject::with(['student.school', 'advisor.user'])->find($this->reference_id);
                if ($fp) {
                    $isSma = str_contains(strtolower($fp->project_type ?? ''), 'penelitian') || str_contains(strtolower($fp->student?->school?->name ?? ''), 'sma');
                    return [
                        'badge' => $isSma ? '🔬 PENELITIAN ILMIAH SMA TERVERIFIKASI' : '🛠️ PROJECT AKHIR SMK TERVERIFIKASI',
                        'icon' => $isSma ? '🔬' : '🛠️',
                        'title' => $fp->title,
                        'subtitle' => 'Pembimbing: ' . ($fp->advisor?->user?->name ?? $fp->advisor?->full_name ?? 'Tim Pembimbing') . ($fp->final_score ? " • Nilai: {$fp->final_score}" : ''),
                        'color' => 'emerald',
                    ];
                }
            } elseif ($this->reference_type === 'lms_submission' && class_exists(LmsSubmission::class)) {
                $sub = LmsSubmission::with(['assignment.course.subject'])->find($this->reference_id);
                if ($sub && $sub->assignment) {
                    return [
                        'badge' => '📚 TUGAS & KARYA LMS TERVERIFIKASI',
                        'icon' => '📚',
                        'title' => $sub->assignment->title,
                        'subtitle' => ($sub->assignment->course?->subject?->name ?? $sub->assignment->course?->course_name ?? 'Modul LMS') . ($sub->score !== null ? " • Skor: {$sub->score}/100" : ' • Terkumpul'),
                        'color' => 'sky',
                    ];
                }
            } elseif ($this->reference_type === 'ekskul_activity' && class_exists(ExtracurricularActivity::class)) {
                $act = ExtracurricularActivity::with('extracurricular')->find($this->reference_id);
                if ($act) {
                    return [
                        'badge' => '🎭 DOKUMENTASI RESMI SANGGAR / EKSKUL',
                        'icon' => $act->extracurricular?->display_icon ?? '🏆',
                        'title' => $act->title,
                        'subtitle' => ($act->extracurricular?->name ?? 'Unit Ekstrakurikuler') . ($act->location ? " • 📍 {$act->location}" : ''),
                        'color' => 'amber',
                    ];
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
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
        $communityName = $this->community?->name;

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
        $shareUrl = route('circle.feed', array_filter(['post' => $this->id, 'community' => $this->community?->slug]));

        // Susun Kalimat Siaran (Format WhatsApp & Social Media yang Rapi & Menarik)
        $lines = [];
        $lines[] = "*{$badge}*";
        if ($communityName) {
            $lines[] = "👥 *Grup/Komunitas:* {$communityName}";
        }
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
            'community_name' => $communityName,
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
