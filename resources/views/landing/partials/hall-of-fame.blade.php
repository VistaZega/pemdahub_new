{{-- HALL OF FAME PEMBDA ELITE — Modern & Prestigious Leaderboard Design --}}
<style>
    .hof-section {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 85px 0 95px 0;
        position: relative;
    }

    .hof-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 32px;
        margin-bottom: 44px;
    }

    @media (max-width: 960px) {
        .hof-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }

    .hof-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 28px;
        padding: 32px 28px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .hof-card:hover {
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.1);
        border-color: #cbd5e1;
    }

    .hof-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 18px;
        margin-bottom: 22px;
        border-bottom: 1.5px solid #f1f5f9;
    }

    .hof-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .hof-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border: 1.5px solid #f1f5f9;
        border-radius: 20px;
        padding: 14px 18px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .hof-item-row:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        transform: translateY(-3px);
        box-shadow: 0 10px 24px -6px rgba(15, 23, 42, 0.08);
    }

    .hof-rank-badge {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
        flex-shrink: 0;
    }

    .hof-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        object-fit: cover;
        background: #ffffff;
        border: 2px solid #ffffff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        flex-shrink: 0;
    }

    .hof-cta-btn {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        color: #ffffff;
        padding: 16px 36px;
        font-size: 14px;
        font-weight: 800;
        border-radius: 100px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 25px rgba(30, 27, 75, 0.25);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
    }

    .hof-cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 35px rgba(30, 27, 75, 0.35);
        color: #ffffff;
    }
</style>

<section class="hof-section" id="hall-of-fame-section">
    <div class="fw">

        {{-- Section Header --}}
        <div style="text-align: center; max-width: 750px; margin: 0 auto 52px;" data-aos="fade-up">
            <div class="section-label" style="justify-content: center; margin-bottom: 14px;">
                <div class="section-label-dot pulse" style="background: #f59e0b;"></div>
                <span class="section-label-text" style="color: #b45309;">PEMBDA ELITE LEADERBOARD</span>
            </div>
            
            <h2 class="h1" style="font-size: clamp(30px, 4vw, 44px); font-weight: 900; color: #0f172a; letter-spacing: -0.025em; margin-bottom: 14px;">
                Hall of Fame <span style="background: linear-gradient(135deg, #4f46e5, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Pembda Elite</span>
            </h2>
            
            <p class="body-lg" style="color: #475569; margin: 0 auto;">
                Panggung apresiasi kehormatan real-time bagi Siswa dan Guru Perguruan PEMBDA Nias dengan keaktifan belajar, karya digital, dan prestasi terbaik.
            </p>
        </div>

        {{-- Side-by-Side Dual Column Showcase --}}
        <div class="hof-grid">

            {{-- 🎓 LEFT COLUMN: TOP ELITE STUDENTS --}}
            <div class="hof-card" data-aos="fade-right">
                <div class="hof-header">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 48px; height: 48px; background: #fef3c7; color: #b45309; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.01em;">Top Elite Students</h3>
                            <span style="font-size: 12px; font-weight: 700; color: #64748b;">Siswa Berprestasi &amp; Paling Aktif</span>
                        </div>
                    </div>
                    <span style="background: #f5f3ff; color: #6d28d9; border: 1px solid #ede9fe; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 100px; text-transform: uppercase;">
                        Leaderboard Siswa
                    </span>
                </div>

                <div class="hof-list">
                    @forelse($topStudentsElite as $index => $std)
                    @php
                        $stdSchoolName = $std->user->student->school->name 
                            ?? $std->user->student->classroom->school->name 
                            ?? $std->user->school->name 
                            ?? null;
                        $stdClassName = $std->user->student->classroom->class_name ?? null;

                        if ($stdSchoolName && $stdClassName) {
                            $stdSubInfo = $stdSchoolName . ' • Kelas ' . $stdClassName;
                        } elseif ($stdSchoolName) {
                            $stdSubInfo = $stdSchoolName;
                        } elseif ($stdClassName) {
                            $stdSubInfo = 'Kelas ' . $stdClassName;
                        } else {
                            $stdSubInfo = 'Siswa Pembda';
                        }

                        // Medal Rank Style
                        if ($index == 0) {
                            $rankBg = 'linear-gradient(135deg, #f59e0b, #fbbf24)';
                            $rankColor = '#78350f';
                            $rankIcon = '🥇';
                        } elseif ($index == 1) {
                            $rankBg = 'linear-gradient(135deg, #94a3b8, #cbd5e1)';
                            $rankColor = '#1e293b';
                            $rankIcon = '🥈';
                        } elseif ($index == 2) {
                            $rankBg = 'linear-gradient(135deg, #d97706, #fed7aa)';
                            $rankColor = '#7c2d12';
                            $rankIcon = '🥉';
                        } else {
                            $rankBg = '#f1f5f9';
                            $rankColor = '#475569';
                            $rankIcon = '#' . ($index + 1);
                        }
                    @endphp
                    <div class="hof-item-row">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            <div class="hof-rank-badge" style="background: {{ $rankBg }}; color: {{ $rankColor }};">
                                {{ $rankIcon }}
                            </div>
                            <img src="{{ $std->user->photo_url ?? asset('assets/img/default-avatar.png') }}" class="hof-avatar" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'" />
                            <div style="min-width: 0;">
                                <div style="font-size: 14px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $std->user->name }}">
                                    {{ $std->user->name }}
                                </div>
                                <div style="font-size: 12px; font-weight: 600; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $stdSubInfo }}">
                                    {{ $stdSubInfo }}
                                </div>
                            </div>
                        </div>
                        <div style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800; font-size: 12.5px; padding: 6px 14px; border-radius: 100px; white-space: nowrap;">
                            <i class="fa-solid fa-bolt" style="margin-right: 4px;"></i>{{ number_format($std->total_points) }} pts
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 28px; font-size: 13px; font-weight: 700; color: #94a3b8;">
                        Belum ada data peringkat siswa.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- 👨‍🏫 RIGHT COLUMN: INSPIRATIONAL GURU --}}
            <div class="hof-card" data-aos="fade-left">
                <div class="hof-header">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 48px; height: 48px; background: #ede9fe; color: #6d28d9; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.01em;">Inspirational Teachers</h3>
                            <span style="font-size: 12px; font-weight: 700; color: #64748b;">Kinerja, Dedikasi &amp; KBM Digital</span>
                        </div>
                    </div>
                    <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 100px; text-transform: uppercase;">
                        Leaderboard Guru
                    </span>
                </div>

                <div class="hof-list">
                    @forelse($topTeachersElite as $index => $tch)
                    @php
                        // Medal Rank Style
                        if ($index == 0) {
                            $rankBg = 'linear-gradient(135deg, #6366f1, #a5b4fc)';
                            $rankColor = '#ffffff';
                            $rankIcon = '🥇';
                        } elseif ($index == 1) {
                            $rankBg = 'linear-gradient(135deg, #94a3b8, #cbd5e1)';
                            $rankColor = '#1e293b';
                            $rankIcon = '🥈';
                        } elseif ($index == 2) {
                            $rankBg = 'linear-gradient(135deg, #d97706, #fed7aa)';
                            $rankColor = '#7c2d12';
                            $rankIcon = '🥉';
                        } else {
                            $rankBg = '#f1f5f9';
                            $rankColor = '#475569';
                            $rankIcon = '#' . ($index + 1);
                        }
                    @endphp
                    <div class="hof-item-row">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            <div class="hof-rank-badge" style="background: {{ $rankBg }}; color: {{ $rankColor }};">
                                {{ $rankIcon }}
                            </div>
                            <img src="{{ $tch->user->photo_url ?? asset('assets/img/default-avatar.png') }}" class="hof-avatar" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'" />
                            <div style="min-width: 0;">
                                <div style="font-size: 14px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tch->user->name }}
                                </div>
                                <div style="font-size: 12px; font-weight: 600; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tch->user->teacher->school->name ?? 'Guru Pembda' }}
                                </div>
                            </div>
                        </div>
                        <div style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 800; font-size: 12.5px; padding: 6px 14px; border-radius: 100px; white-space: nowrap;">
                            <i class="fa-solid fa-star" style="margin-right: 4px;"></i>{{ number_format($tch->total_points) }} pts
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 28px; font-size: 13px; font-weight: 700; color: #94a3b8;">
                        Belum ada data peringkat guru.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- CTA Button to Full Hall of Fame Page --}}
        <div style="text-align: center;" data-aos="fade-up">
            <a href="{{ route('reputation.leaderboard') }}" class="hof-cta-btn">
                <span><i class="fa-solid fa-trophy" style="color: #fbbf24; margin-right: 6px;"></i> Lihat Papan Peringkat Lengkap (Hall of Fame)</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
</section>
