<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularActivity;
use App\Models\ForumGroup;
use App\Models\ForumGroupMember;
use App\Models\ReputationLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExtracurricularService
{
    /**
     * Create new Extracurricular unit and auto-provision its Space Channel.
     */
    public function createExtracurricular(array $data, ?int $userId = null): Extracurricular
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
            }

            // 1. Create Extracurricular
            $ekskul = Extracurricular::create($data);

            // 2. Auto-provision Pembda Space Channel (ForumGroup)
            $forumGroup = ForumGroup::firstOrCreate(
                [
                    'school_id' => $ekskul->school_id,
                    'name' => $ekskul->name,
                    'type' => 'extracurricular',
                ],
                [
                    'slug' => 'ekskul-' . $ekskul->slug,
                    'description' => $ekskul->description ?: 'Kanal & Ruang Koordinasi Resmi ' . $ekskul->name,
                    'icon' => $ekskul->display_icon,
                    'color' => $ekskul->color ?: 'indigo',
                    'is_official' => true,
                    'created_by_user_id' => $userId,
                ]
            );

            $ekskul->update(['forum_group_id' => $forumGroup->id]);

            // 3. Register Leadership as Members if provided
            $this->syncLeadershipMembers($ekskul, $userId);

            return $ekskul;
        });
    }

    /**
     * Update Extracurricular unit and sync Space Channel.
     */
    public function updateExtracurricular(Extracurricular $ekskul, array $data, ?int $userId = null): Extracurricular
    {
        return DB::transaction(function () use ($ekskul, $data, $userId) {
            $ekskul->update($data);

            // Update associated ForumGroup if exists
            if ($ekskul->forum_group_id && $ekskul->forumGroup) {
                $ekskul->forumGroup->update([
                    'name' => $ekskul->name,
                    'description' => $ekskul->description ?: $ekskul->forumGroup->description,
                    'icon' => $ekskul->display_icon,
                    'color' => $ekskul->color ?: 'indigo',
                ]);
            }

            $this->syncLeadershipMembers($ekskul, $userId);

            return $ekskul;
        });
    }

    /**
     * Student claims or enrolls in an Extracurricular unit.
     */
    public function claimMembership(
        Student $student,
        Extracurricular $ekskul,
        string $role = 'anggota',
        ?string $notes = null,
        ?string $section = null
    ): ExtracurricularMember {
        return DB::transaction(function () use ($student, $ekskul, $role, $notes, $section) {
            $activeYear = AcademicYear::where('is_active', true)->first();

            $existing = ExtracurricularMember::where('extracurricular_id', $ekskul->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                if ($existing->status === 'rejected' || ($section && $existing->section !== $section)) {
                    $existing->update([
                        'status' => 'approved',
                        'role' => $role,
                        'section' => $section ?: $existing->section,
                        'joined_date' => now(),
                        'notes' => $notes ?: $existing->notes,
                    ]);
                    $this->grantMemberRewards($existing);
                }
                return $existing;
            }

            $member = ExtracurricularMember::create([
                'extracurricular_id' => $ekskul->id,
                'student_id' => $student->id,
                'academic_year_id' => $activeYear?->id,
                'role' => $role,
                'section' => $section,
                'status' => 'approved', // Langsung aktif & terdaftar
                'joined_date' => now(),
                'notes' => $notes,
            ]);

            $this->grantMemberRewards($member);

            return $member;
        });
    }

    /**
     * Approve membership and grant rewards.
     */
    public function approveMember(ExtracurricularMember $member, ?int $approverUserId = null): ExtracurricularMember
    {
        $member->update([
            'status' => 'approved',
            'approved_by' => $approverUserId,
            'joined_date' => $member->joined_date ?: now(),
        ]);

        $this->grantMemberRewards($member);

        return $member;
    }

    /**
     * Reward student with Reputation Points and Pembda Space Channel membership.
     */
    private function grantMemberRewards(ExtracurricularMember $member): void
    {
        $student = $member->student;
        $ekskul = $member->extracurricular;

        if (!$student || !$ekskul) {
            return;
        }

        // 1. Join ForumGroup in Pembda Space
        if ($student->user_id && $ekskul->forum_group_id) {
            ForumGroupMember::firstOrCreate(
                [
                    'group_id' => $ekskul->forum_group_id,
                    'user_id' => $student->user_id,
                ],
                [
                    'role' => in_array($member->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']) ? 'moderator' : 'member',
                    'joined_at' => now(),
                ]
            );
        }

        // 2. Award Gamification Reputation Points
        if ($student->user_id && $member->points_awarded == 0) {
            $points = in_array($member->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']) ? 30 : 15;
            $roleLabel = $member->role_label;
            $sectionInfo = $member->section ? " (Section: {$member->section})" : "";

            ReputationLog::log(
                $student->user_id,
                $points,
                'extracurricular',
                "Aktif bergabung di {$ekskul->name} - {$roleLabel}{$sectionInfo}",
                $member
            );

            $member->update(['points_awarded' => $points]);
        }
    }

    /**
     * Synchronize leadership student IDs to ExtracurricularMember records.
     */
    private function syncLeadershipMembers(Extracurricular $ekskul, ?int $userId = null): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $leaders = [
            'ketua' => $ekskul->leader_student_id,
            'sekretaris' => $ekskul->secretary_student_id,
            'bendahara' => $ekskul->treasurer_student_id,
        ];

        foreach ($leaders as $role => $studentId) {
            if (!$studentId) continue;

            $member = ExtracurricularMember::updateOrCreate(
                [
                    'extracurricular_id' => $ekskul->id,
                    'student_id' => $studentId,
                ],
                [
                    'academic_year_id' => $activeYear?->id,
                    'role' => $role,
                    'status' => 'approved',
                    'joined_date' => now(),
                    'approved_by' => $userId,
                ]
            );

            $this->grantMemberRewards($member);
        }
    }

    /**
     * Ensure forum group exists and return it.
     */
    public function ensureForumGroup(Extracurricular $ekskul): ForumGroup
    {
        if ($ekskul->forum_group_id && $ekskul->forumGroup) {
            return $ekskul->forumGroup;
        }

        $forumGroup = ForumGroup::firstOrCreate(
            [
                'name' => $ekskul->name,
                'type' => 'extracurricular',
            ],
            [
                'school_id' => $ekskul->school_id,
                'slug' => 'ekskul-' . ($ekskul->slug ?: Str::slug($ekskul->name)),
                'description' => $ekskul->description ?: 'Squad Lounge & Ruang Koordinasi ' . $ekskul->name,
                'icon' => $ekskul->display_icon ?: '🏆',
                'color' => $ekskul->color ?: 'purple',
                'is_official' => true,
            ]
        );

        $ekskul->update(['forum_group_id' => $forumGroup->id]);
        return $forumGroup;
    }

    /**
     * Broadcast an extracurricular activity to Pembda Space as a showcase post.
     */
    public function broadcastActivityToSpace(ExtracurricularActivity $activity, User $author): ForumThread
    {
        $ekskul = $activity->extracurricular;
        $forumGroup = $this->ensureForumGroup($ekskul);

        $dateFormatted = \Carbon\Carbon::parse($activity->activity_date)->locale('id')->isoFormat('D MMMM Y');
        $locationText = $activity->location ? "📍 **Lokasi:** {$activity->location}\n" : "";

        $content = "🏆 **DOKUMENTASI & CATATAN KEGIATAN EKSKUL**\n\n" .
            "📅 **Tanggal Kegiatan:** {$dateFormatted}\n" .
            $locationText .
            ($activity->description ? "\n📝 **Catatan & Ringkasan Aktivitas:**\n" . $activity->description . "\n\n" : "\n") .
            "---\n" .
            "✨ *Dipublikasikan secara resmi oleh Pembina " . ($ekskul->name) . " melalui Pembda Space.*";

        return ForumThread::create([
            'user_id' => $author->id,
            'group_id' => $forumGroup->id,
            'title' => "🏆 [SHOWCASE " . strtoupper($ekskul->name) . "] " . $activity->title,
            'content' => $content,
            'category' => 'ekskul_showcase',
            'status' => 'active',
            'is_pinned' => false,
            'views_count' => 1,
        ]);
    }
}
