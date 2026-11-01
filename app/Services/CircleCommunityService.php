<?php

namespace App\Services;

use App\Models\AlumniDirectory;
use App\Models\Classroom;
use App\Models\CircleCommunity;
use App\Models\CircleCommunityMember;
use App\Models\CirclePost;
use App\Models\Extracurricular;
use App\Models\ExtracurricularActivity;
use App\Models\ExtracurricularMember;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CircleCommunityService
{
    /**
     * Definisi 5 Rumpun Kejuruan Resmi SMK Swasta Pembda Nias
     */
    public const VOCATIONAL_MAJORS = [
        'TE' => [
            'name' => 'Rumpun Teknik Elektronika & Audio Video (TE / TAV)',
            'slug' => 'rumpun-smk-te-tav',
            'icon' => '⚡',
            'theme_color' => 'amber',
            'keywords' => ['TE', 'TAV', 'ELEKTRONIKA', 'AUDIO'],
            'description' => 'Komunitas praktik bengkel, mikrokontroler, IoT, dan rekayasa audio video siswa & guru TE/TAV SMK Pembda.',
        ],
        'DPIB' => [
            'name' => 'Rumpun Desain Pemodelan & Informasi Bangunan (DPIB)',
            'slug' => 'rumpun-smk-dpib',
            'icon' => '🏗️',
            'theme_color' => 'sky',
            'keywords' => ['DPIB', 'BANGUNAN'],
            'description' => 'Studio arsitektur, gambar teknik CAD/3D, maket konstruksi, dan karya desain bangunan DPIB SMK Pembda.',
        ],
        'TKR' => [
            'name' => 'Rumpun Teknik Kendaraan Ringan & Otomotif (TKR / TO)',
            'slug' => 'rumpun-smk-tkr-to',
            'icon' => '🚗',
            'theme_color' => 'rose',
            'keywords' => ['TKR', 'TO', 'OTOMOTIF', 'KENDARAAN'],
            'description' => 'Bengkel otomotif, tune-up engine, sistem sasis, dan inovasi kendaraan ringan TKR/TO SMK Pembda.',
        ],
        'TSM' => [
            'name' => 'Rumpun Teknik & Bisnis Sepeda Motor (TSM / TBSM)',
            'slug' => 'rumpun-smk-tsm-tbsm',
            'icon' => '🏍️',
            'theme_color' => 'emerald',
            'keywords' => ['TSM', 'TBSM', 'MOTOR'],
            'description' => 'Komunitas mekanik muda, injeksi sepeda motor, kelistrikan, dan wirausaha bengkel TSM/TBSM SMK Pembda.',
        ],
        'TKJ' => [
            'name' => 'Rumpun Teknik Komputer, Jaringan & Axioo (TKJ / ACP)',
            'slug' => 'rumpun-smk-tkj-acp',
            'icon' => '💻',
            'theme_color' => 'indigo',
            'keywords' => ['TKJ', 'TJKT', 'ACP', 'JARINGAN'],
            'description' => 'Markas jaringan fiber optik, server, cybersecurity, perakitan PC, dan Axioo Class Program (ACP) SMK Pembda.',
        ],
    ];

    /**
     * Tentukan apakah sebuah unit Ekstrakurikuler termasuk kategori Sanggar & Seni Budaya
     */
    public static function isSanggarOrArts(Extracurricular $ekskul): bool
    {
        if (in_array($ekskul->category, ['seni_budaya', 'marching_band'])) {
            return true;
        }
        $lower = strtolower($ekskul->name . ' ' . ($ekskul->description ?? ''));
        return (bool) preg_match('/\b(sanggar|seni|tari|musik|vokal|vocal|paduan\s*suara|choir|marching|teater|drama|lukis|band|budaya)\b/i', $lower);
    }

    /**
     * Pastikan seluruh komunitas/grup resmi ekosistem PembdaCIRCLE tersedia di database
     * (Grup Kelas, Sanggar, Ekskul, 5 Rumpun SMK, Angkatan, IKA Alumni, & Ruang Guru).
     */
    public function ensureEcosystemCommunities(): void
    {
        if (!Schema::hasTable('circle_communities')) {
            return;
        }

        // Gunakan cache 10 menit agar tidak membebani query di setiap request
        if (Cache::has('circle_ecosystem_communities_synced_v2')) {
            return;
        }

        try {
            $hasCohortCol = Schema::hasColumn('circle_communities', 'cohort_year');
            $hasMajorCol = Schema::hasColumn('circle_communities', 'major_code');
            $hasMetaCol = Schema::hasColumn('circle_communities', 'metadata');

            $schools = School::schoolsOnly()->get();
            $smkSchool = $schools->first(fn($s) => str_contains(strtoupper($s->name . ' ' . $s->type), 'SMK'));

            // 1. GRUP RUANG GURU & MENTOR LINTAS UNIT
            $teacherComm = CircleCommunity::updateOrCreate(
                ['slug' => 'ruang-guru-mentor-pembda'],
                [
                    'name' => 'Ruang Kolaborasi Guru & Mentor Pembda',
                    'description' => 'Wadah berbagi praktik baik KBM, modul LMS, bimbingan karya siswa, dan koordinasi Bapak/Ibu Guru Perguruan Pembda.',
                    'type' => 'teachers',
                    'icon' => '👨‍🏫',
                    'theme_color' => 'teal',
                    'is_official' => true,
                    'is_private' => false,
                ]
            );

            // 2. GRUP IKATAN ALUMNI (IKA PEMBDA LINTAS ANGKATAN & PER UNIT)
            $ikaMainData = [
                'name' => 'Ikatan Alumni (IKA) Perguruan Pembda Nias',
                'description' => 'Jejaring resmi lintas angkatan & lintas profesi seluruh Alumni SMP, SMA, dan SMK Perguruan Pembda Nias (1970 – Sekarang).',
                'type' => 'alumni',
                'icon' => '🎓',
                'theme_color' => 'indigo',
                'is_official' => true,
                'is_private' => false,
            ];
            CircleCommunity::updateOrCreate(['slug' => 'ika-alumni-pembda-pusat'], $ikaMainData);

            foreach ($schools as $sch) {
                $schSlug = 'ika-alumni-' . Str::slug($sch->name);
                CircleCommunity::updateOrCreate(
                    ['slug' => $schSlug],
                    [
                        'name' => 'IKA Alumni ' . $sch->name,
                        'description' => 'Keluarga besar lulusan & jejaring karir alumni ' . $sch->name . '. Berbagi lowongan, inspirasi kampus, dan reuni angkatan.',
                        'type' => 'alumni',
                        'school_id' => $sch->id,
                        'icon' => '🎓',
                        'theme_color' => 'indigo',
                        'is_official' => true,
                        'is_private' => false,
                    ]
                );
            }

            // 3. GRUP 5 RUMPUN KEJURUAN SMK SWASTA PEMBDA
            foreach (self::VOCATIONAL_MAJORS as $code => $vInfo) {
                $vocData = [
                    'name' => $vInfo['name'],
                    'description' => $vInfo['description'],
                    'type' => 'vocation',
                    'school_id' => $smkSchool?->id,
                    'icon' => $vInfo['icon'],
                    'theme_color' => $vInfo['theme_color'],
                    'is_official' => true,
                    'is_private' => false,
                ];
                if ($hasMajorCol) {
                    $vocData['major_code'] = $code;
                }
                CircleCommunity::updateOrCreate(['slug' => $vInfo['slug']], $vocData);
            }

            // 4. GRUP SANGGAR SENI & EKSTRAKURIKULER AKTIF
            if (Schema::hasTable('extracurriculars')) {
                $ekskuls = Extracurricular::where('is_active', true)->get();
                foreach ($ekskuls as $ekskul) {
                    $this->ensureExtracurricularCommunity($ekskul);
                }
            }

            // 5. GRUP ANGKATAN (COHORT TAHUN MASUK SISWA & LULUSAN)
            if (Schema::hasTable('students')) {
                $entryYears = Student::whereNotNull('entry_year')
                    ->where('entry_year', '>=', 2020)
                    ->where('entry_year', '<=', (int) date('Y') + 1)
                    ->distinct()
                    ->orderByDesc('entry_year')
                    ->pluck('entry_year')
                    ->toArray();

                if (empty($entryYears)) {
                    $curY = (int) date('Y');
                    $entryYears = [$curY, $curY - 1, $curY - 2];
                }

                foreach ($entryYears as $yr) {
                    $cohortData = [
                        'name' => 'Grup Angkatan ' . $yr,
                        'description' => 'Ruang solidaritas, kolaborasi lintas kelas, dan kenangan bersama seluruh siswa Perguruan Pembda Angkatan ' . $yr . '.',
                        'type' => 'cohort',
                        'icon' => '📅',
                        'theme_color' => 'emerald',
                        'is_official' => true,
                        'is_private' => false,
                    ];
                    if ($hasCohortCol) {
                        $cohortData['cohort_year'] = (int) $yr;
                    }
                    CircleCommunity::updateOrCreate(['slug' => 'angkatan-' . $yr], $cohortData);
                }
            }

            // 6. GRUP KELAS (ROMBEL AKTIF DARI 3 UNIT SEKOLAH)
            if (Schema::hasTable('classrooms')) {
                $activeSchoolIds = $schools->pluck('id')->toArray();
                $classrooms = Classroom::with('homeroomTeacher')
                    ->where('is_active', true)
                    ->when(!empty($activeSchoolIds), fn($q) => $q->whereIn('school_id', $activeSchoolIds))
                    ->get();

                foreach ($classrooms as $cls) {
                    $clsName = $cls->class_name ?? $cls->name ?? ('Kelas-' . $cls->id);
                    $slug = 'kelas-' . $cls->id . '-' . Str::slug($clsName);

                    $clsData = [
                        'name' => 'Kelas ' . $clsName,
                        'description' => 'Ruang diskusi, pengumuman wali kelas, dan pameran karya siswa Rombel ' . $clsName . '.',
                        'type' => 'class',
                        'school_id' => $cls->school_id,
                        'classroom_id' => $cls->id,
                        'icon' => '🏫',
                        'theme_color' => 'sky',
                        'is_official' => true,
                        'is_private' => false,
                    ];

                    if ($hasMetaCol && $cls->homeroomTeacher) {
                        $clsData['metadata'] = [
                            'homeroom_teacher_name' => $cls->homeroomTeacher->full_name ?? $cls->homeroomTeacher->name,
                        ];
                    }

                    $comm = CircleCommunity::updateOrCreate(
                        ['classroom_id' => $cls->id, 'type' => 'class'],
                        array_merge(['slug' => $slug], $clsData)
                    );

                    // Pastikan Wali Kelas terdaftar sebagai Leader
                    if ($cls->homeroomTeacher && $cls->homeroomTeacher->user_id) {
                        $this->enrollUser($comm, (int) $cls->homeroomTeacher->user_id, 'leader', 'Wali Kelas');
                    }
                }
            }

            Cache::put('circle_ecosystem_communities_synced_v2', true, now()->addMinutes(10));
        } catch (\Throwable $e) {
            \Log::warning('CircleCommunityService::ensureEcosystemCommunities error: ' . $e->getMessage());
        }
    }

    /**
     * Buat atau perbarui Komunitas Circle untuk satu unit Sanggar / Ekstrakurikuler,
     * lengkap dengan sinkronisasi Pembina, Ketua, Sekretaris, Bendahara, dan Anggota aktif.
     */
    public function ensureExtracurricularCommunity(Extracurricular $ekskul): ?CircleCommunity
    {
        if (!Schema::hasTable('circle_communities')) {
            return null;
        }

        $isSanggar = self::isSanggarOrArts($ekskul);
        $type = $isSanggar ? 'sanggar' : 'extracurricular';
        $slug = ($isSanggar ? 'sanggar-' : 'ekskul-') . ($ekskul->slug ?: Str::slug($ekskul->name) . '-' . $ekskul->id);

        $ekskul->loadMissing(['advisor', 'leader', 'secretary', 'treasurer']);

        $meta = [
            'category' => $ekskul->category,
            'category_label' => $ekskul->category_label,
            'schedule' => $ekskul->schedule_day_time,
            'location' => $ekskul->location,
            'advisor_name' => $ekskul->advisor?->full_name ?? $ekskul->advisor_name,
            'leader_name' => $ekskul->leader?->full_name,
            'secretary_name' => $ekskul->secretary?->full_name,
            'treasurer_name' => $ekskul->treasurer?->full_name,
            'sections' => $ekskul->section_list,
            'scope' => $ekskul->scope ?? ($ekskul->isFoundationLevel() ? 'yayasan' : 'sekolah'),
        ];

        $data = [
            'name' => $ekskul->name,
            'description' => $ekskul->description ?: ('Ruang kreasi, jadwal latihan, & showcase resmi anggota ' . $ekskul->name . '.'),
            'type' => $type,
            'school_id' => $ekskul->school_id,
            'extracurricular_id' => $ekskul->id,
            'icon' => $ekskul->display_icon ?: ($isSanggar ? '🎭' : '🏆'),
            'theme_color' => $isSanggar ? 'pink' : ($ekskul->color ?: 'amber'),
            'is_official' => true,
            'is_private' => false,
        ];

        if (Schema::hasColumn('circle_communities', 'metadata')) {
            $data['metadata'] = $meta;
        }

        $community = CircleCommunity::where('extracurricular_id', $ekskul->id)->first();
        if ($community) {
            $community->update($data);
        } else {
            // Pastikan slug unik
            if (CircleCommunity::where('slug', $slug)->exists()) {
                $slug .= '-' . $ekskul->id;
            }
            $community = CircleCommunity::create(array_merge(['slug' => $slug], $data));
        }

        // Sinkronisasi Pembina
        if ($ekskul->advisor && $ekskul->advisor->user_id) {
            $this->enrollUser($community, (int) $ekskul->advisor->user_id, 'leader', $isSanggar ? 'Pembina Sanggar' : 'Pembina Ekskul');
        }

        // Sinkronisasi Pengurus Inti Siswa
        $leadersMap = [
            'Ketua' => $ekskul->leader,
            'Sekretaris' => $ekskul->secretary,
            'Bendahara' => $ekskul->treasurer,
        ];
        foreach ($leadersMap as $badgeTitle => $studentModel) {
            if ($studentModel && $studentModel->user_id) {
                $this->enrollUser($community, (int) $studentModel->user_id, 'moderator', $badgeTitle);
            }
        }

        // Sinkronisasi Anggota Aktif
        $activeMembers = ExtracurricularMember::with('student')
            ->where('extracurricular_id', $ekskul->id)
            ->where('status', 'approved')
            ->get();

        foreach ($activeMembers as $m) {
            if ($m->student && $m->student->user_id) {
                $isOfficer = in_array($m->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']);
                $role = $isOfficer ? 'moderator' : 'member';
                $badge = $isOfficer
                    ? ucfirst(str_replace('_', ' ', $m->role))
                    : ($m->section ? "Section {$m->section}" : 'Anggota Aktif');
                $this->enrollUser($community, (int) $m->student->user_id, $role, $badge);
            }
        }

        $this->refreshCommunityCount($community);

        return $community;
    }

    /**
     * Sinkronisasi otomatis keanggotaan grup pengguna yang sedang login
     * berdasarkan Kelas, Sanggar/Ekskul, Rumpun Kejuruan SMK, Angkatan, Alumni, atau Guru.
     */
    public function syncUserCommunities(?User $user): void
    {
        if (!$user || !Schema::hasTable('circle_communities')) {
            return;
        }

        $this->ensureEcosystemCommunities();

        $sessionKey = 'circle_user_comm_synced_v2_' . $user->id;
        if (session()->has($sessionKey)) {
            return;
        }

        try {
            $user->loadMissing(['student.classroom', 'student.major', 'student.extracurricularMembers.extracurricular', 'teacher', 'alumniDirectory']);

            // A. JIKA GURU / PEGAWAI / ADMIN
            if ($user->role === 'guru' || $user->role === 'pegawai' || $user->teacher || $user->isSuperAdmin() || $user->isAdminSekolah()) {
                $teacherComm = CircleCommunity::where('slug', 'ruang-guru-mentor-pembda')->first();
                if ($teacherComm) {
                    $this->enrollUser($teacherComm, $user->id, $user->isSuperAdmin() ? 'leader' : 'member', 'Guru / Mentor');
                }

                if ($user->teacher) {
                    // Wali Kelas
                    $homeroomClasses = Classroom::where('is_active', true)
                        ->where('homeroom_teacher_id', $user->teacher->id)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($homeroomClasses)) {
                        $clsComms = CircleCommunity::where('type', 'class')->whereIn('classroom_id', $homeroomClasses)->get();
                        foreach ($clsComms as $cc) {
                            $this->enrollUser($cc, $user->id, 'leader', 'Wali Kelas');
                        }
                    }

                    // Pembina Sanggar / Ekskul
                    $advisedEkskuls = Extracurricular::where('is_active', true)
                        ->where('advisor_teacher_id', $user->teacher->id)
                        ->get();
                    foreach ($advisedEkskuls as $eks) {
                        $ec = $this->ensureExtracurricularCommunity($eks);
                        if ($ec) {
                            $this->enrollUser($ec, $user->id, 'leader', 'Pembina');
                        }
                    }
                }
            }

            // B. JIKA SISWA
            if ($user->student) {
                $student = $user->student;

                // 1. Grup Kelas (Rombel Aktif)
                $classIds = Classroom::where('is_active', true)
                    ->where(function ($q) use ($student) {
                        $q->whereHas('students', fn($sq) => $sq->where('students.id', $student->id))
                          ->orWhere('id', $student->classroom_id ?? 0);
                    })
                    ->pluck('id')
                    ->toArray();

                if (!empty($classIds)) {
                    $clsComms = CircleCommunity::where('type', 'class')->whereIn('classroom_id', $classIds)->get();
                    foreach ($clsComms as $cc) {
                        $this->enrollUser($cc, $user->id, 'member', 'Siswa Rombel');
                    }
                }

                // 2. Grup Sanggar & Ekstrakurikuler yang diikuti
                $memberships = ExtracurricularMember::with('extracurricular')
                    ->where('student_id', $student->id)
                    ->where('status', 'approved')
                    ->get();

                foreach ($memberships as $m) {
                    if ($m->extracurricular) {
                        $ec = $this->ensureExtracurricularCommunity($m->extracurricular);
                        if ($ec) {
                            $isOfficer = in_array($m->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']);
                            $badge = $isOfficer
                                ? ucfirst(str_replace('_', ' ', $m->role))
                                : ($m->section ? "Section {$m->section}" : 'Anggota');
                            $this->enrollUser($ec, $user->id, $isOfficer ? 'moderator' : 'member', $badge);
                        }
                    }
                }

                // 3. Grup Rumpun Kejuruan SMK (Menggunakan standar VocationalMajorFilterService!)
                foreach (self::VOCATIONAL_MAJORS as $code => $vInfo) {
                    if (VocationalMajorFilterService::isStudentMatchingVocationalSubject($student, $vInfo['keywords'])) {
                        $vocComm = CircleCommunity::where('slug', $vInfo['slug'])->first();
                        if ($vocComm) {
                            $this->enrollUser($vocComm, $user->id, 'member', "Jurusan {$code}");
                        }
                    }
                }

                // 4. Grup Angkatan (Entry Year)
                if ($student->entry_year) {
                    $cohortComm = CircleCommunity::where('slug', 'angkatan-' . $student->entry_year)->first();
                    if ($cohortComm) {
                        $this->enrollUser($cohortComm, $user->id, 'member', 'Angkatan ' . $student->entry_year);
                    }
                }

                // 5. Jika Siswa sudah berstatus Lulus / Alumni
                if (in_array($student->status, ['lulus', 'alumni'])) {
                    $this->syncAlumniMembership($user, $student->school_id, $student->graduation_year ?: ($student->entry_year ? $student->entry_year + 3 : null));
                }
            }

            // C. JIKA ALUMNI (Role Alumni atau punya AlumniDirectory)
            if ($user->role === 'alumni' || $user->alumniDirectory) {
                $schoolId = $user->alumniDirectory?->school_id ?? $user->school_id;
                $gradYear = $user->alumniDirectory?->graduation_year ?? null;
                $this->syncAlumniMembership($user, $schoolId, $gradYear);
            }

            session()->put($sessionKey, true);
        } catch (\Throwable $e) {
            \Log::warning('CircleCommunityService::syncUserCommunities error: ' . $e->getMessage());
        }
    }

    /**
     * Daftarkan pengguna ke Komunitas IKA Alumni Pusat & IKA Unit Sekolah
     */
    private function syncAlumniMembership(User $user, ?int $schoolId, ?int $gradYear): void
    {
        $badge = $gradYear ? "Alumni '{$gradYear}" : 'Anggota IKA';

        $ikaMain = CircleCommunity::where('slug', 'ika-alumni-pembda-pusat')->first();
        if ($ikaMain) {
            $this->enrollUser($ikaMain, $user->id, 'member', $badge);
        }

        if ($schoolId) {
            $ikaUnit = CircleCommunity::where('type', 'alumni')->where('school_id', $schoolId)->first();
            if ($ikaUnit) {
                $this->enrollUser($ikaUnit, $user->id, 'member', $badge);
            }
        }
    }

    /**
     * Helper daftarkan 1 user ke CircleCommunity dengan aman (firstOrCreate / update badge)
     */
    public function enrollUser(CircleCommunity $community, int $userId, string $role = 'member', ?string $titleBadge = null): CircleCommunityMember
    {
        $hasBadgeCol = Schema::hasColumn('circle_community_members', 'title_badge');

        $member = CircleCommunityMember::where('community_id', $community->id)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            $payload = [
                'community_id' => $community->id,
                'user_id' => $userId,
                'role' => $role,
                'joined_at' => now(),
            ];
            if ($hasBadgeCol && $titleBadge) {
                $payload['title_badge'] = $titleBadge;
            }
            $member = CircleCommunityMember::create($payload);
            $this->refreshCommunityCount($community);
        } else {
            $updates = [];
            if ($role === 'leader' && $member->role !== 'leader') {
                $updates['role'] = 'leader';
            } elseif ($role === 'moderator' && $member->role === 'member') {
                $updates['role'] = 'moderator';
            }
            if ($hasBadgeCol && $titleBadge && empty($member->title_badge)) {
                $updates['title_badge'] = $titleBadge;
            }
            if (!empty($updates)) {
                $member->update($updates);
            }
        }

        return $member;
    }

    /**
     * Hitung ulang jumlah anggota riil & postingan pada suatu komunitas
     */
    public function refreshCommunityCount(CircleCommunity $community): void
    {
        $realMembers = CircleCommunityMember::where('community_id', $community->id)->count();

        // Jika grup kelas dan belum semua siswa login, tampilkan jumlah siswa riil di kelas + wali kelas
        if ($community->type === 'class' && $community->classroom_id) {
            $cls = Classroom::find($community->classroom_id);
            if ($cls) {
                $studentCount = $cls->students()->count();
                if ($studentCount + 1 > $realMembers) {
                    $realMembers = $studentCount + 1;
                }
            }
        } elseif (in_array($community->type, ['sanggar', 'extracurricular']) && $community->extracurricular_id) {
            $eksCount = ExtracurricularMember::where('extracurricular_id', $community->extracurricular_id)
                ->where('status', 'approved')
                ->count();
            if ($eksCount > $realMembers) {
                $realMembers = $eksCount;
            }
        } elseif ($community->type === 'cohort' && !empty($community->cohort_year)) {
            $cohortCount = Student::where('entry_year', $community->cohort_year)->count();
            if ($cohortCount > $realMembers) {
                $realMembers = $cohortCount;
            }
        } elseif ($community->type === 'alumni') {
            $alumniQuery = AlumniDirectory::where('is_approved', true);
            if ($community->school_id) {
                $alumniQuery->where('school_id', $community->school_id);
            }
            $dirCount = $alumniQuery->count();
            if ($dirCount > $realMembers) {
                $realMembers = $dirCount;
            }
        }

        $postsCount = CirclePost::where('community_id', $community->id)->count();

        $community->update([
            'members_count' => $realMembers,
            'posts_count' => $postsCount,
        ]);
    }

    /**
     * Siarkan otomatis Dokumentasi Kegiatan Sanggar / Ekskul ke PembdaCIRCLE
     */
    public function broadcastActivityToCircle(ExtracurricularActivity $activity, User $author): ?CirclePost
    {
        if (!Schema::hasTable('circle_posts')) {
            return null;
        }

        try {
            $ekskul = $activity->extracurricular;
            if (!$ekskul) {
                return null;
            }

            $community = $this->ensureExtracurricularCommunity($ekskul);
            $isSanggar = self::isSanggarOrArts($ekskul);

            $postData = [
                'user_id' => $author->id,
                'post_type' => 'report',
                'tab_category' => 'portfolio',
                'community_id' => $community?->id,
                'title' => ($isSanggar ? '🎭 [SANGGAR & SENI] ' : '🏆 [SHOWCASE EKSKUL] ') . $ekskul->name . ': ' . $activity->title,
                'content' => $activity->description ?: ("Dokumentasi kegiatan resmi {$ekskul->name}: {$activity->title}."),
                'mood_emoji' => $ekskul->display_icon ?: ($isSanggar ? '🎭' : '🏆'),
                'mood_text' => $ekskul->name,
                'location_tag' => $activity->location ?: ($ekskul->location ?: 'Perguruan Pembda'),
                'event_name' => $activity->title . ' — ' . $ekskul->name,
                'event_date' => $activity->activity_date ? \Carbon\Carbon::parse($activity->activity_date) : now(),
                'school_id' => $ekskul->school_id ?? $author->school_id,
                'visibility' => 'public',
            ];

            if (Schema::hasColumn('circle_posts', 'reference_type')) {
                $postData['reference_type'] = 'ekskul_activity';
                $postData['reference_id'] = $activity->id;
            }

            $post = CirclePost::create($postData);

            if ($community) {
                $this->refreshCommunityCount($community);
            }

            return $post;
        } catch (\Throwable $e) {
            \Log::warning('CircleCommunityService::broadcastActivityToCircle error: ' . $e->getMessage());
            return null;
        }
    }
}
