<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularActivity;
use App\Models\ForumGroup;
use App\Models\ForumGroupMember;
use App\Models\LmsAssignment;
use App\Models\LmsClass;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsModule;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsSubmission;
use App\Models\ReputationLog;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
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

        // 3. Auto-Enroll to LMS Course if Extracurricular LMS is active
        if ($ekskul->lms_course_id && $member->status === 'approved') {
            $lmsClass = LmsClass::firstOrCreate([
                'course_id' => $ekskul->lms_course_id,
                'classroom_id' => null,
            ], [
                'school_id' => $ekskul->school_id ?? $student->school_id,
                'status' => 'active',
            ]);

            LmsEnrollment::firstOrCreate([
                'lms_class_id' => $lmsClass->id,
                'student_id' => $student->id,
            ], [
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);
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

    /**
     * 1-Click Activate or Open Extracurricular LMS Course (Materi, Tugas, Quiz & Nilai Extra).
     */
    public function ensureLmsCourse(Extracurricular $ekskul, ?User $actor = null): LmsCourse
    {
        return DB::transaction(function () use ($ekskul, $actor) {
            if ($ekskul->lms_course_id) {
                $existingCourse = LmsCourse::withTrashed()->find($ekskul->lms_course_id);
                if ($existingCourse) {
                    if ($existingCourse->trashed()) {
                        $existingCourse->restore();
                    }
                    if (!$existingCourse->extracurricular_id) {
                        $existingCourse->update(['extracurricular_id' => $ekskul->id]);
                    }
                    app(LmsEnrollmentService::class)->syncCourseEnrollments($existingCourse);
                    return $existingCourse;
                }
            }

            // Check if a course already links to this extracurricular_id
            $existingByFk = LmsCourse::where('extracurricular_id', $ekskul->id)->first();
            if ($existingByFk) {
                $ekskul->update(['lms_course_id' => $existingByFk->id]);
                app(LmsEnrollmentService::class)->syncCourseEnrollments($existingByFk);
                return $existingByFk;
            }

            // Determine Advisor / Teacher ID (required by lms_courses.teacher_id)
            $teacherId = $ekskul->advisor_teacher_id;
            if (!$teacherId && $actor && $actor->teacher) {
                $teacherId = $actor->teacher->id;
            }
            if (!$teacherId && $ekskul->school_id) {
                $teacherId = Teacher::where('school_id', $ekskul->school_id)->where('is_active', true)->value('id');
            }
            if (!$teacherId) {
                $teacherId = Teacher::where('is_active', true)->value('id') ?? Teacher::value('id');
            }

            if ($teacherId && !$ekskul->advisor_teacher_id) {
                $teacherObj = Teacher::find($teacherId);
                $ekskul->update([
                    'advisor_teacher_id' => $teacherId,
                    'advisor_name' => $ekskul->advisor_name ?: $teacherObj?->full_name,
                ]);
            }

            $activeSemester = Semester::where('is_active', true)->first()
                ?? Semester::orderByDesc('id')->first();

            $codePrefix = 'EKS-' . strtoupper(Str::substr(Str::slug($ekskul->name, ''), 0, 4));
            $code = $codePrefix . '-' . strtoupper(Str::random(4));

            $course = LmsCourse::create([
                'school_id' => $ekskul->school_id,
                'teacher_id' => $teacherId,
                'subject_id' => null, // NULL agar nilai tidak tercampur ke nilai rapor akademik reguler
                'extracurricular_id' => $ekskul->id,
                'classroom_id' => null,
                'semester_id' => $activeSemester?->id ?? 1,
                'code' => $code,
                'course_name' => 'Ekstrakurikuler: ' . $ekskul->name,
                'description' => $ekskul->description ?: ('Kelas LMS Resmi Ekstrakurikuler ' . $ekskul->name . ' — Materi, Tugas Praktik, Quiz Evaluasi, dan Penilaian Extra.'),
                'status' => 'active',
                'is_published' => true,
                'is_active' => true,
                'is_sequential' => false,
                'review_status' => 'approved',
            ]);

            $ekskul->update(['lms_course_id' => $course->id]);

            // Buat LmsClass Cohort Ekskul (classroom_id = null)
            LmsClass::firstOrCreate([
                'course_id' => $course->id,
                'classroom_id' => null,
            ], [
                'school_id' => $ekskul->school_id,
                'status' => 'active',
            ]);

            // Seed Starter Modules jika baru dibuat (khususnya English Club atau Ekskul umum)
            $this->seedStarterModulesForEkskul($ekskul, $course);

            // Sinkronisasi seluruh anggota aktif ke LmsEnrollment
            app(LmsEnrollmentService::class)->syncCourseEnrollments($course);

            return $course;
        });
    }

    /**
     * Buat modul awal (starter modules) agar Guru Pembina langsung memiliki kerangka Materi, Tugas, dan Quiz.
     */
    private function seedStarterModulesForEkskul(Extracurricular $ekskul, LmsCourse $course): void
    {
        if (LmsModule::where('course_id', $course->id)->exists()) {
            return;
        }

        $isEnglishClub = str_contains(strtolower($ekskul->name), 'english');

        if ($isEnglishClub) {
            $modules = [
                [
                    'title' => 'Module 1: Confident Public Speaking & Daily Conversation',
                    'description' => 'Penguasaan ekspresi percakapan bahasa Inggris sehari-hari, pelafalan (pronunciation), dan kepercayaan diri berbicara di depan umum.',
                    'order' => 1,
                ],
                [
                    'title' => 'Module 2: Storytelling, Speech & Debate Mastery',
                    'description' => 'Teknik menyusun argumen debat bahasa Inggris (British/Asian Parliamentary), intonasi storytelling, dan struktur pidato persuasif.',
                    'order' => 2,
                ],
                [
                    'title' => 'Module 3: Academic Writing, Vocabulary & TOEFL/IELTS Prep',
                    'description' => 'Pengayaan kosakata akademik, penulisan esai kreatif bahasa Inggris, serta latihan kuis grammar & reading comprehension.',
                    'order' => 3,
                ],
            ];
        } else {
            $modules = [
                [
                    'title' => 'Modul 1: Pengenalan, Dasar Kompetensi & Materi Inti ' . $ekskul->name,
                    'description' => 'Materi pengantar, panduan latihan, serta kompetensi dasar anggota ' . $ekskul->name . '.',
                    'order' => 1,
                ],
                [
                    'title' => 'Modul 2: Latihan Terstruktur & Penugasan Praktik',
                    'description' => 'Kumpulan instruksi latihan praktik, proyek kelompok/individu, dan pengumpulan dokumentasi tugas.',
                    'order' => 2,
                ],
                [
                    'title' => 'Modul 3: Evaluasi Kemampuan & Kuis Uji Kompetensi',
                    'description' => 'Evaluasi pemahaman materi dan penilaian pencapaian anggota ekstrakurikuler.',
                    'order' => 3,
                ],
            ];
        }

        foreach ($modules as $mod) {
            LmsModule::create([
                'course_id' => $course->id,
                'title' => $mod['title'],
                'description' => $mod['description'],
                'order_number' => $mod['order'],
                'is_published' => true,
            ]);
        }
    }

    /**
     * Sinkronisasi & Hitung Otomatis Nilai Extra (Rata-rata Tugas LMS + Rata-rata Quiz LMS + Kehadiran)
     * untuk 1 anggota ekstrakurikuler tanpa mencemari tabel `grades` akademik.
     */
    public function syncMemberExtraGrade(ExtracurricularMember $member, ?int $gradedByUserId = null): ExtracurricularMember
    {
        $ekskul = $member->extracurricular;
        if (!$ekskul || !$ekskul->lms_course_id) {
            return $member;
        }

        $course = LmsCourse::find($ekskul->lms_course_id);
        if (!$course) {
            return $member;
        }

        $studentId = $member->student_id;

        // 1. Hitung Rata-rata Tugas (LmsSubmission pada course ini)
        $assignments = LmsAssignment::where('course_id', $course->id)->get();
        $assignmentScores = [];
        $completedTopics = [];

        foreach ($assignments as $asg) {
            $sub = LmsSubmission::where('assignment_id', $asg->id)
                ->where('student_id', $studentId)
                ->whereNotNull('score')
                ->first();

            if ($sub) {
                $norm = ($asg->max_score && $asg->max_score > 0)
                    ? round(($sub->score / $asg->max_score) * 100, 2)
                    : (float) $sub->score;
                $assignmentScores[] = min(100, max(0, $norm));
                $completedTopics[] = $asg->title;
            }
        }

        $avgAssignment = !empty($assignmentScores)
            ? round(array_sum($assignmentScores) / count($assignmentScores), 2)
            : $member->avg_assignment_score;

        // 2. Hitung Rata-rata Quiz (Skor terbaik tiap LmsQuiz pada course ini)
        $quizzes = LmsQuiz::where('course_id', $course->id)->get();
        $quizScores = [];

        foreach ($quizzes as $qz) {
            $bestScore = LmsQuizAttempt::where('quiz_id', $qz->id)
                ->where('student_id', $studentId)
                ->whereNotNull('score')
                ->max('score');

            if ($bestScore !== null) {
                $quizScores[] = min(100, max(0, (float) $bestScore));
                $completedTopics[] = $qz->title;
            }
        }

        $avgQuiz = !empty($quizScores)
            ? round(array_sum($quizScores) / count($quizScores), 2)
            : $member->avg_quiz_score;

        // 3. Hitung Final Score dari komponen yang tersedia (Tugas, Quiz, Kehadiran)
        $components = array_filter([
            $avgAssignment,
            $avgQuiz,
            $member->attendance_score,
        ], fn($v) => $v !== null);

        $finalScore = !empty($components)
            ? round(array_sum($components) / count($components), 2)
            : $member->final_score;

        $predicate = ExtracurricularMember::calculatePredicate($finalScore);

        $topicsSummary = !empty($completedTopics)
            ? implode(', ', array_slice(array_unique($completedTopics), 0, 3))
            : null;

        $description = $member->grade_description;
        if (empty($description) && $predicate) {
            $description = ExtracurricularMember::generateDefaultDescription($ekskul->name, $predicate, $topicsSummary);
        }

        $updateData = [
            'avg_assignment_score' => $avgAssignment,
            'avg_quiz_score' => $avgQuiz,
            'final_score' => $finalScore,
            'predicate' => $predicate,
            'grade_description' => $description,
        ];

        if ($finalScore !== null) {
            $updateData['graded_at'] = $member->graded_at ?: now();
            if ($gradedByUserId) {
                $updateData['graded_by'] = $gradedByUserId;
            }
            // Terbitkan Nomor Sertifikat otomatis jika Nilai Akhir >= 60 (Minimal Cukup / Lulus)
            if ($finalScore >= 60 && empty($member->certificate_code)) {
                $updateData['certificate_code'] = $this->generateCertificateCode($ekskul, $member);
                $updateData['certificate_issued_at'] = now();
            }
        }

        $member->update($updateData);

        return $member->fresh();
    }

    /**
     * Sinkronisasi massal Nilai Extra seluruh anggota pada 1 unit Ekstrakurikuler.
     */
    public function syncAllMembersExtraGrades(Extracurricular $ekskul, ?int $gradedByUserId = null): int
    {
        $members = $ekskul->members()->where('status', 'approved')->get();
        $count = 0;
        foreach ($members as $member) {
            $this->syncMemberExtraGrade($member, $gradedByUserId);
            $count++;
        }
        return $count;
    }

    /**
     * Generate kode unik Sertifikat Kelulusan Ekstrakurikuler.
     */
    public function generateCertificateCode(Extracurricular $ekskul, ExtracurricularMember $member): string
    {
        $prefix = str_contains(strtolower($ekskul->name), 'english') ? 'EC-CERT' : 'EKS-CERT';
        $year = now()->format('Y');
        $paddedMember = str_pad((string) $member->id, 4, '0', STR_PAD_LEFT);
        $rand = strtoupper(Str::random(4));
        return "{$prefix}-{$year}-{$paddedMember}-{$rand}";
    }
}
