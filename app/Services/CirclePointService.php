<?php

namespace App\Services;

use App\Models\CircleComment;
use App\Models\CirclePollVote;
use App\Models\CirclePost;
use App\Models\CircleProfile;
use App\Models\CircleProfileGuestbook;
use App\Models\CircleProfileVisitor;
use App\Models\CircleReaction;
use App\Models\CircleSkillEndorsement;
use App\Models\Reputation;
use App\Models\ReputationLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CirclePointService
{
    /**
     * Bobot dasar poin untuk pemilik postingan saat menerima Reaksi Apresiasi
     */
    public const REACTION_POINTS = [
        'lit'          => 3, // 🔥 Menyala!
        'clap'         => 4, // 👏 Keren Pol!
        'love'         => 5, // ❤️ Bangga!
        'nongki'       => 6, // 💡 Memicu Ide!
        'proud_parent' => 6, // 🌟 Kebanggaan Orang Tua
    ];

    /**
     * Hitung poin yang diterima pemilik postingan dari sebuah reaksi
     */
    public static function calculateReactionPoints(CirclePost $post, string $reactionType, ?User $reactor = null): int
    {
        $base = self::REACTION_POINTS[$reactionType] ?? 3;

        // Bonus +2 poin jika yang diapresiasi adalah Karya Portofolio atau Laporan Kegiatan/Live
        if ($post->tab_category === 'portfolio' || in_array($post->post_type, ['project', 'devlog', 'report', 'live'])) {
            $base += 2;
        }

        // Multiplier 2x lipat jika yang mengapresiasi adalah Bapak/Ibu Guru atau Kepala Sekolah
        if ($reactor && in_array($reactor->role, ['guru', 'kepala_sekolah'])) {
            $base *= 2;
        }

        return $base;
    }

    /**
     * Berikan / perbarui poin saat sebuah postingan menerima Reaksi Apresiasi dari pengguna lain
     */
    public static function awardReaction(CirclePost $post, CircleReaction $reaction, ?User $reactor = null): int
    {
        if (!$post->user_id) {
            return 0;
        }

        // Tidak ada poin jika mengapresiasi postingan sendiri
        if ($reaction->user_id && (int) $reaction->user_id === (int) $post->user_id) {
            return 0;
        }

        // Hanya reaksi dari user terautentikasi yang menghasilkan poin agar bebas manipulasi tamu/bot
        if (!$reaction->user_id) {
            return 0;
        }

        $reactor = $reactor ?? User::find($reaction->user_id);
        $points = self::calculateReactionPoints($post, $reaction->reaction_type, $reactor);

        $emojiLabel = match ($reaction->reaction_type) {
            'lit'          => '🔥 Menyala!',
            'clap'         => '👏 Keren Pol!',
            'nongki'       => '💡 Memicu Ide!',
            'love'         => '❤️ Bangga!',
            'proud_parent' => '🌟 Kebanggaan',
            default        => '✨ Apresiasi',
        };

        $reactorName = $reactor ? $reactor->name : 'Warga Pembda';
        $roleTag = ($reactor && in_array($reactor->role, ['guru', 'kepala_sekolah'])) ? ' (Apresiasi Guru 2x ⚡)' : '';
        $postLabel = $post->title
            ? '"' . Str::limit($post->title, 45) . '"'
            : ('"' . Str::limit(strip_tags((string) $post->content), 40) . '"');

        $desc = "Karya/postingan {$postLabel} diapresiasi {$emojiLabel} oleh {$reactorName}{$roleTag}";

        try {
            ReputationLog::log(
                $post->user_id,
                $points,
                'circle_reaction',
                $desc,
                $reaction
            );

            // Cek milestone viral postingan (10 & 25 apresiasi)
            self::checkPostViralMilestones($post);
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::awardReaction error: ' . $e->getMessage());
        }

        return $points;
    }

    /**
     * Tarik kembali poin jika reaksi apresiasi dibatalkan (un-react)
     */
    public static function revokeReaction(CirclePost $post, CircleReaction $reaction): void
    {
        if (!$post->user_id) {
            return;
        }

        try {
            ReputationLog::removeLog($post->user_id, CircleReaction::class, $reaction->id);
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::revokeReaction error: ' . $e->getMessage());
        }
    }

    /**
     * Cek dan berikan bonus milestone jika postingan menembus 10 atau 25 apresiasi
     */
    public static function checkPostViralMilestones(CirclePost $post): void
    {
        if (!$post->user_id) {
            return;
        }

        // Hitung jumlah reaksi dari user terdaftar (bukan diri sendiri)
        $validReactionsCount = CircleReaction::where('circle_post_id', $post->id)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $post->user_id)
            ->count();

        $postTitle = $post->title
            ? '"' . Str::limit($post->title, 45) . '"'
            : ('"' . Str::limit(strip_tags((string) $post->content), 40) . '"');

        // Milestone 10 Apresiasi (+20 Poin)
        if ($validReactionsCount >= 10) {
            $already10 = ReputationLog::where('user_id', $post->user_id)
                ->where('category', 'circle_viral_10')
                ->where('reference_type', CirclePost::class)
                ->where('reference_id', $post->id)
                ->exists();

            if (!$already10) {
                ReputationLog::log(
                    $post->user_id,
                    20,
                    'circle_viral_10',
                    "🔥 Gelora Menyala! Postingan {$postTitle} menembus 10+ Apresiasi Warga",
                    $post
                );
            }
        }

        // Milestone 25 Apresiasi (+50 Poin)
        if ($validReactionsCount >= 25) {
            $already25 = ReputationLog::where('user_id', $post->user_id)
                ->where('category', 'circle_viral_25')
                ->where('reference_type', CirclePost::class)
                ->where('reference_id', $post->id)
                ->exists();

            if (!$already25) {
                // Buat entri terpisah agar tidak menimpa milestone 10
                $log = ReputationLog::create([
                    'user_id'        => $post->user_id,
                    'points'         => 50,
                    'category'       => 'circle_viral_25',
                    'reference_type' => CirclePost::class,
                    'reference_id'   => $post->id,
                    'description'    => "🚀 Karya Viral Sekolah! Postingan {$postTitle} menembus 25+ Apresiasi Warga",
                    'created_at'     => now(),
                ]);

                $rep = Reputation::firstOrCreate(
                    ['user_id' => $post->user_id],
                    ['total_points' => 0, 'level_name' => 'Newbie']
                );
                $rep->total_points += 50;
                $rep->updateLevel();
                $rep->save();
            }
        }
    }

    /**
     * Berikan poin saat sebuah postingan DITANGGAPI / DIKOMENTARI oleh pengguna lain
     */
    public static function awardComment(CirclePost $post, CircleComment $comment, ?User $commenter = null): int
    {
        if (!$comment->user_id || !$post->user_id) {
            return 0;
        }

        // Komentar di postingan sendiri tidak menghasilkan poin
        if ((int) $comment->user_id === (int) $post->user_id) {
            return 0;
        }

        $cleanText = trim(strip_tags((string) $comment->comment_text));
        if (mb_strlen($cleanText) < 4) {
            return 0;
        }

        $commenter = $commenter ?? User::find($comment->user_id);
        $awardedToAuthor = 0;

        try {
            // 1. Poin untuk PEMILIK POSTINGAN karena karyanya ditanggapi/direspon
            // Maksimal 1x poin per komentator unik pada postingan yang sama (cegah spam komentar beruntun)
            $priorCommentsFromSameUser = CircleComment::where('circle_post_id', $post->id)
                ->where('user_id', $comment->user_id)
                ->where('id', '!=', $comment->id)
                ->exists();

            if (!$priorCommentsFromSameUser) {
                $isTeacherComment = $commenter && in_array($commenter->role, ['guru', 'kepala_sekolah']);
                $authorPoints = $isTeacherComment ? 10 : 5;

                $postLabel = $post->title
                    ? '"' . Str::limit($post->title, 40) . '"'
                    : ('"' . Str::limit(strip_tags((string) $post->content), 35) . '"');
                $commenterName = $commenter ? $commenter->name : $comment->author_name;
                $teacherBadge = $isTeacherComment ? ' (Tanggapan Guru 👨‍🏫)' : '';

                ReputationLog::log(
                    $post->user_id,
                    $authorPoints,
                    'circle_responded',
                    "Postingan {$postLabel} ditanggapi oleh {$commenterName}{$teacherBadge}: \"" . Str::limit($cleanText, 40) . "\"",
                    $comment
                );

                $awardedToAuthor = $authorPoints;
            }

            // 2. Poin Apresiasi Partisipasi (+2 Poin) untuk PEMBERI KOMENTAR jika komentarnya bermakna (>= 15 karakter)
            // Dibatasi maksimal 5 komentar berpoin per hari agar tidak nyepam di banyak post
            if (mb_strlen($cleanText) >= 15) {
                $todayResponderCount = ReputationLog::where('user_id', $comment->user_id)
                    ->where('category', 'circle_active_responder')
                    ->whereDate('created_at', now()->toDateString())
                    ->count();

                if ($todayResponderCount < 5) {
                    $authorName = $post->user?->name ?? 'Teman';
                    ReputationLog::log(
                        $comment->user_id,
                        2,
                        'circle_active_responder',
                        "Memberikan tanggapan positif pada karya/postingan {$authorName} di PembdaCIRCLE",
                        $comment
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::awardComment error: ' . $e->getMessage());
        }

        return $awardedToAuthor;
    }

    /**
     * Berikan poin saat Polling di PembdaCIRCLE direspon / diberi suara oleh warga lain
     */
    public static function awardPollVote(CirclePost $post, CirclePollVote $vote, ?User $voter = null): int
    {
        if (!$post->user_id || !$vote->user_id) {
            return 0;
        }

        // Vote di polling sendiri tidak dapat poin
        if ((int) $vote->user_id === (int) $post->user_id) {
            return 0;
        }

        $voter = $voter ?? User::find($vote->user_id);
        $voterName = $voter ? $voter->name : 'Warga Pembda';
        $question = $vote->poll?->question ?? ($post->title ?? 'Jajak Pendapat');

        try {
            ReputationLog::log(
                $post->user_id,
                2,
                'circle_poll_response',
                "{$voterName} merespon & memberikan suara pada polling: \"" . Str::limit($question, 45) . "\"",
                $vote
            );
            return 2;
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::awardPollVote error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Berikan poin saat Guru mengesahkan Lencana Keahlian (Skill Endorsement) pada Portofolio Siswa
     */
    public static function awardSkillEndorsement(CircleSkillEndorsement $endorsement, ?User $teacher = null): void
    {
        $teacher = $teacher ?? User::find($endorsement->teacher_user_id);
        $student = User::find($endorsement->student_user_id);

        if (!$student) {
            return;
        }

        $teacherName = $teacher ? $teacher->name : 'Guru Pembina';

        try {
            // 1. +50 Poin Prestisius untuk Siswa yang keahliannya divalidasi Guru
            ReputationLog::log(
                $student->id,
                50,
                'circle_endorsement',
                "🏅 Lencana Keahlian \"{$endorsement->skill_name}\" disahkan resmi oleh Guru {$teacherName}",
                $endorsement
            );

            // 2. +15 Poin untuk Guru yang aktif memvalidasi & mengapresiasi keahlian siswa
            if ($teacher && (int) $teacher->id !== (int) $student->id) {
                ReputationLog::log(
                    $teacher->id,
                    15,
                    'circle_mentor_endorse',
                    "👨‍🏫 Mengesahkan Lencana Keahlian \"{$endorsement->skill_name}\" untuk siswa {$student->name}",
                    $endorsement
                );
            }
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::awardSkillEndorsement error: ' . $e->getMessage());
        }
    }

    /**
     * Tarik kembali poin jika Lencana Keahlian dihapus
     */
    public static function revokeSkillEndorsement(CircleSkillEndorsement $endorsement): void
    {
        try {
            ReputationLog::removeLog($endorsement->student_user_id, CircleSkillEndorsement::class, $endorsement->id);
            if ($endorsement->teacher_user_id) {
                ReputationLog::removeLog($endorsement->teacher_user_id, CircleSkillEndorsement::class, $endorsement->id);
            }
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::revokeSkillEndorsement error: ' . $e->getMessage());
        }
    }

    /**
     * Berikan poin saat Dinding Profil ditempeli Stiker Buku Tamu oleh pengguna lain
     */
    public static function awardGuestbook(CircleProfile $profile, CircleProfileGuestbook $guestbook, ?User $sender = null): int
    {
        if (!$profile->user_id || !$guestbook->sender_user_id) {
            return 0;
        }

        // Menempel stiker di dinding sendiri tidak dapat poin
        if ((int) $profile->user_id === (int) $guestbook->sender_user_id) {
            return 0;
        }

        // Cegah farming bolak-balik: Maksimal 1x poin dari pengirim yang sama di dinding ini dalam 7 hari terakhir
        $recentFromSameSender = CircleProfileGuestbook::where('circle_profile_id', $profile->id)
            ->where('sender_user_id', $guestbook->sender_user_id)
            ->where('id', '!=', $guestbook->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($recentFromSameSender) {
            return 0;
        }

        $sender = $sender ?? User::find($guestbook->sender_user_id);
        $senderName = $sender ? $sender->name : 'Sahabat Pembda';
        $isTeacher = $sender && in_array($sender->role, ['guru', 'kepala_sekolah']);
        $points = $isTeacher ? 10 : 5;

        try {
            ReputationLog::log(
                $profile->user_id,
                $points,
                'circle_guestbook',
                "{$senderName} menempelkan stiker apresiasi {$guestbook->sticker_emoji} \"{$guestbook->sticker_label}\" di Buku Tamu dindingmu",
                $guestbook
            );
            return $points;
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::awardGuestbook error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Berikan / tarik poin untuk Pengirim Stiker saat catatannya Disematkan (Pinned) oleh pemilik dinding
     */
    public static function syncGuestbookPin(CircleProfile $profile, CircleProfileGuestbook $guestbook): void
    {
        if (!$guestbook->sender_user_id || (int) $guestbook->sender_user_id === (int) $profile->user_id) {
            return;
        }

        $ownerName = $profile->user?->name ?? $profile->nickname ?? 'Warga Pembda';

        try {
            if ($guestbook->is_pinned) {
                ReputationLog::log(
                    $guestbook->sender_user_id,
                    10,
                    'circle_guestbook_pinned',
                    "📌 Catatan stiker {$guestbook->sticker_emoji} kirimanmu disematkan sebagai favorit di dinding {$ownerName}!",
                    $guestbook
                );
            } else {
                ReputationLog::removeLog($guestbook->sender_user_id, CircleProfileGuestbook::class, $guestbook->id);
            }
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::syncGuestbookPin error: ' . $e->getMessage());
        }
    }

    /**
     * Tarik kembali poin jika Stiker Buku Tamu dihapus
     */
    public static function revokeGuestbook(CircleProfile $profile, CircleProfileGuestbook $guestbook): void
    {
        try {
            if ($profile->user_id) {
                ReputationLog::removeLog($profile->user_id, CircleProfileGuestbook::class, $guestbook->id);
            }
            if ($guestbook->sender_user_id) {
                ReputationLog::removeLog($guestbook->sender_user_id, CircleProfileGuestbook::class, $guestbook->id);
            }
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::revokeGuestbook error: ' . $e->getMessage());
        }
    }

    /**
     * Berikan +1 poin saat profil dikunjungi oleh pengunjung unik baru
     */
    public static function awardUniqueVisitor(CircleProfile $profile, CircleProfileVisitor $visitorRecord, ?User $visitor = null): void
    {
        if (!$profile->user_id || !$visitorRecord->visitor_user_id) {
            return;
        }

        if ((int) $profile->user_id === (int) $visitorRecord->visitor_user_id) {
            return;
        }

        // Hanya berikan pada kunjungan pertama dari user unik tersebut
        if (($visitorRecord->visit_count ?? 1) > 1) {
            return;
        }

        $visitor = $visitor ?? User::find($visitorRecord->visitor_user_id);
        $visitorName = $visitor ? $visitor->name : 'Warga Pembda';

        try {
            ReputationLog::log(
                $profile->user_id,
                1,
                'circle_visitor',
                "👀 {$visitorName} berkunjung dan mengapresiasi dinding profilmu di PembdaCIRCLE",
                $visitorRecord
            );
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::awardUniqueVisitor error: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi (Backfill) otomatis 1x untuk seluruh interaksi PembdaCIRCLE yang sudah ada di database
     * agar semua apresiasi, komentar, endorsement, polling, dan stiker buku tamu yang telah ada langsung terhitung poinnya.
     */
    public static function ensureExistingCircleInteractionsSynced(): void
    {
        try {
            if (Cache::get('circle_points_v1_synced')) {
                return;
            }

            $flag = Setting::getValue('circle_points_v1_synced', false);
            if ($flag) {
                Cache::put('circle_points_v1_synced', true, now()->addDays(7));
                return;
            }

            // 1. Backfill Reaksi Apresiasi Circle
            $reactions = CircleReaction::with(['post', 'user'])
                ->whereNotNull('user_id')
                ->get();
            foreach ($reactions as $rx) {
                if ($rx->post && $rx->post->user_id && (int) $rx->user_id !== (int) $rx->post->user_id) {
                    self::awardReaction($rx->post, $rx, $rx->user);
                }
            }

            // 2. Backfill Komentar / Tanggapan Circle
            $comments = CircleComment::with(['post.user', 'user'])
                ->whereNotNull('user_id')
                ->orderBy('id', 'asc')
                ->get();
            foreach ($comments as $cm) {
                if ($cm->post && $cm->post->user_id && (int) $cm->user_id !== (int) $cm->post->user_id) {
                    self::awardComment($cm->post, $cm, $cm->user);
                }
            }

            // 3. Backfill Suara Polling Circle
            $votes = CirclePollVote::with(['poll.post', 'user'])
                ->whereNotNull('user_id')
                ->get();
            foreach ($votes as $vt) {
                if ($vt->poll && $vt->poll->post) {
                    self::awardPollVote($vt->poll->post, $vt, $vt->user);
                }
            }

            // 4. Backfill Lencana Keahlian (Endorsement Guru)
            $endorsements = CircleSkillEndorsement::with('teacher')->get();
            foreach ($endorsements as $end) {
                self::awardSkillEndorsement($end, $end->teacher);
            }

            // 5. Backfill Stiker Buku Tamu & Pin
            $guestbooks = CircleProfileGuestbook::with(['circleProfile.user', 'sender'])
                ->orderBy('id', 'asc')
                ->get();
            foreach ($guestbooks as $gb) {
                if ($gb->circleProfile) {
                    self::awardGuestbook($gb->circleProfile, $gb, $gb->sender);
                    if ($gb->is_pinned) {
                        self::syncGuestbookPin($gb->circleProfile, $gb);
                    }
                }
            }

            Setting::setValue('circle_points_v1_synced', '1');
            Cache::put('circle_points_v1_synced', true, now()->addDays(7));
        } catch (\Throwable $e) {
            Log::warning('CirclePointService::ensureExistingCircleInteractionsSynced error: ' . $e->getMessage());
        }
    }

    /**
     * Metadata tampilan visual untuk setiap kategori di Wall Jejak Poin (Point Wall)
     */
    public static function getCategoryBadgeMeta(string $category): array
    {
        return match ($category) {
            'circle_reaction' => [
                'group'  => 'circle',
                'icon'   => '🔥',
                'label'  => 'Diapresiasi di Circle',
                'bg'     => 'bg-orange-100',
                'text'   => 'text-orange-900',
                'border' => 'border-orange-300',
            ],
            'circle_responded' => [
                'group'  => 'circle',
                'icon'   => '💬',
                'label'  => 'Ditanggapi di Circle',
                'bg'     => 'bg-pink-100',
                'text'   => 'text-pink-900',
                'border' => 'border-pink-300',
            ],
            'circle_active_responder' => [
                'group'  => 'circle',
                'icon'   => '🗣️',
                'label'  => 'Menanggapi Sahabat',
                'bg'     => 'bg-sky-100',
                'text'   => 'text-sky-900',
                'border' => 'border-sky-300',
            ],
            'circle_poll_response' => [
                'group'  => 'circle',
                'icon'   => '📊',
                'label'  => 'Respon Jajak Pendapat',
                'bg'     => 'bg-purple-100',
                'text'   => 'text-purple-900',
                'border' => 'border-purple-300',
            ],
            'circle_endorsement', 'circle_mentor_endorse' => [
                'group'  => 'circle',
                'icon'   => '🏅',
                'label'  => 'Validasi Keahlian Guru',
                'bg'     => 'bg-amber-100',
                'text'   => 'text-amber-950',
                'border' => 'border-amber-400',
            ],
            'circle_guestbook', 'circle_guestbook_pinned' => [
                'group'  => 'circle',
                'icon'   => '💌',
                'label'  => 'Apresiasi Buku Tamu',
                'bg'     => 'bg-rose-100',
                'text'   => 'text-rose-900',
                'border' => 'border-rose-300',
            ],
            'circle_visitor' => [
                'group'  => 'circle',
                'icon'   => '👀',
                'label'  => 'Kunjungan Dinding',
                'bg'     => 'bg-indigo-100',
                'text'   => 'text-indigo-900',
                'border' => 'border-indigo-300',
            ],
            'circle_viral_10', 'circle_viral_25' => [
                'group'  => 'circle',
                'icon'   => '🚀',
                'label'  => 'Milestone Karya Viral',
                'bg'     => 'bg-yellow-200',
                'text'   => 'text-slate-950',
                'border' => 'border-amber-500',
            ],
            'lms', 'lms_course', 'lms_material', 'lms_assignment', 'lms_quiz', 'cbt', 'final_project', 'pkl', 'attendance' => [
                'group'  => 'academic',
                'icon'   => '🎓',
                'label'  => 'Akademik, LMS & KBM',
                'bg'     => 'bg-emerald-100',
                'text'   => 'text-emerald-900',
                'border' => 'border-emerald-300',
            ],
            'achievement', 'extracurricular', 'osis', 'counseling' => [
                'group'  => 'achievement',
                'icon'   => '🏆',
                'label'  => 'Prestasi & Organisasi',
                'bg'     => 'bg-amber-100',
                'text'   => 'text-amber-900',
                'border' => 'border-amber-300',
            ],
            'forum', 'forum_like', 'forum_upvote', 'forum_best_answer', 'forum_join', 'forum_team_approve', 'forum_completed_project', 'forum_charity_donation', 'challenge_won' => [
                'group'  => 'space_legacy',
                'icon'   => '🏛️',
                'label'  => 'Arsip PembdaSPACE',
                'bg'     => 'bg-slate-100',
                'text'   => 'text-slate-800',
                'border' => 'border-slate-300',
            ],
            default => [
                'group'  => 'academic',
                'icon'   => '✨',
                'label'  => 'Penghargaan PembdaHUB',
                'bg'     => 'bg-sky-100',
                'text'   => 'text-sky-900',
                'border' => 'border-sky-300',
            ],
        };
    }

    /**
     * Cek apakah banner pemberitahuan transisi sistem poin (PembdaSPACE -> PembdaCIRCLE) masih dalam masa tayang 1 minggu (7 hari).
     * Otomatis menyimpan waktu mulai tayang pertama kali di tabel settings, dan otomatis hilang setelah 7 hari.
     */
    public static function getTransitionNoticeInfo(): array
    {
        try {
            $rawStart = Setting::getValue('circle_point_notice_started_at');
            if (!$rawStart) {
                $startedAt = now();
                Setting::setValue('circle_point_notice_started_at', $startedAt->toIso8601String(), 'string', 'circle');
            } else {
                $startedAt = \Carbon\Carbon::parse($rawStart);
            }

            $expiresAt = $startedAt->copy()->addDays(7);
            $isActive = now()->lessThan($expiresAt);
            $secondsLeft = max(0, now()->diffInSeconds($expiresAt, false));
            $daysLeft = (int) max(1, ceil($secondsLeft / 86400));

            return [
                'show'        => $isActive,
                'started_at'  => $startedAt,
                'expires_at'  => $expiresAt,
                'days_left'   => $daysLeft,
                'badge_label' => "Tayang {$daysLeft} Hari Lagi",
            ];
        } catch (\Throwable $e) {
            return [
                'show'        => true,
                'started_at'  => now(),
                'expires_at'  => now()->addDays(7),
                'days_left'   => 7,
                'badge_label' => 'Tayang 7 Hari',
            ];
        }
    }

    /**
     * Cek apakah PembdaSPACE masih aktif dalam masa transisi 7 hari.
     * Mengembalikan false setelah masa transisi 7 hari berakhir (atau jika dipaksa tutup oleh admin).
     */
    public static function isSpaceActive(): bool
    {
        $forceClosed = Setting::getValue('pembda_space_force_closed', false);
        if ($forceClosed) {
            return false;
        }

        $info = self::getTransitionNoticeInfo();
        return (bool) ($info['show'] ?? true);
    }

    /**
     * Cek apakah PembdaSPACE sudah ditutup permanen.
     */
    public static function isSpaceClosed(): bool
    {
        return !self::isSpaceActive();
    }
}
