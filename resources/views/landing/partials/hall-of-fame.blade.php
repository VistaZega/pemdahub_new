{{-- HALL OF FAME PEMBDA ELITE — Rich Visuals, Trophy Podium, Badges & Celebratory Theme --}}
<style>
    .hof-section {
        background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        padding: 90px 0 100px 0;
        position: relative;
        overflow: hidden;
    }

    /* Floating Trophy & Glow Particle Accents */
    .hof-bg-glow {
        position: absolute;
        width: 600px;
        height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, rgba(99, 102, 241, 0.05) 50%, transparent 70%);
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        z-index: 0;
    }

    .hof-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 32px;
        margin-bottom: 48px;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 960px) {
        .hof-grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }
    }

    .hof-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 32px;
        padding: 36px 30px;
        box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.35);
        position: relative;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .hof-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 28px 60px -10px rgba(0, 0, 0, 0.45);
    }

    .hof-card-watermark {
        position: absolute;
        right: -25px;
        bottom: -25px;
        font-size: 180px;
        color: rgba(15, 23, 42, 0.03);
        transform: rotate(-12deg);
        pointer-events: none;
        z-index: 0;
    }

    .hof-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 20px;
        margin-bottom: 24px;
        border-bottom: 1.5px solid #f1f5f9;
        position: relative;
        z-index: 1;
    }

    .hof-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        position: relative;
        z-index: 1;
    }

    .hof-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: 22px;
        padding: 16px 20px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    /* Rank 1 Podium Glow */
    .hof-rank-1 {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 2px solid #fcd34d;
        box-shadow: 0 8px 25px -4px rgba(245, 158, 11, 0.25);
    }

    .hof-rank-1:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 14px 30px -4px rgba(245, 158, 11, 0.38);
    }

    /* Rank 2-4 Normal Row */
    .hof-rank-normal {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
    }

    .hof-rank-normal:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
    }

    .hof-medal-box {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .hof-avatar-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .hof-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        background: #ffffff;
        border: 2.5px solid #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        display: block;
    }

    .hof-crown-badge {
        position: absolute;
        top: -8px;
        right: -6px;
        width: 22px;
        height: 22px;
        background: #fbbf24;
        color: #78350f;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        border: 1.5px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .hof-points-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 900;
        font-size: 13px;
        padding: 7px 16px;
        border-radius: 100px;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .hof-cta-btn {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #0f172a;
        padding: 16px 40px;
        font-size: 14.5px;
        font-weight: 900;
        border-radius: 100px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 12px 30px rgba(245, 158, 11, 0.35);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        letter-spacing: 0.02em;
    }

    .hof-cta-btn:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 18px 40px rgba(245, 158, 11, 0.5);
        color: #000000;
    }

    @keyframes trophyBounce {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-6px) rotate(4deg); }
    }
</style>

<section class="hof-section" id="hall-of-fame-section">
    {{-- Radial Center Glow --}}
    <div class="hof-bg-glow"></div>

    <div class="fw" style="position: relative; z-index: 1;">

        {{-- Section Header with Trophy & Star Decorations --}}
        <div style="text-align: center; max-width: 800px; margin: 0 auto 56px;" data-aos="fade-up">
            
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.35); color: #fbbf24; font-size: 12.5px; font-weight: 900; padding: 7px 20px; border-radius: 100px; text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.08em;">
                <i class="fa-solid fa-crown" style="color: #fbbf24; animation: trophyBounce 3s ease-in-out infinite;"></i>
                <span>PAPAN KEHORMATAN PEMBDA ELITE</span>
                <i class="fa-solid fa-trophy" style="color: #fbbf24;"></i>
            </div>
            
            <h2 class="h1" style="font-size: clamp(32px, 4.2vw, 46px); font-weight: 900; color: #ffffff; letter-spacing: -0.025em; margin-bottom: 14px;">
                Hall of Fame <span style="background: linear-gradient(135deg, #fbbf24, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Bintang Prestasi</span>
            </h2>
            
            <p class="body-lg" style="color: rgba(255, 255, 255, 0.8); max-width: 720px; margin: 0 auto;">
                Panggung apresiasi kehormatan real-time bagi Siswa dan Guru Perguruan PEMBDA Nias dengan dedikasi belajar, karya digital, dan perolehan poin prestasi tertinggi.
            </p>
        </div>

        {{-- Side-by-Side Dual Column Showcase --}}
        <div class="hof-grid">

            {{-- 🎓 LEFT COLUMN: TOP ELITE STUDENTS --}}
            <div class="hof-card" data-aos="fade-right">
                <i class="fa-solid fa-graduation-cap hof-card-watermark"></i>

                <div class="hof-header">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25);">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.02em;">Top Elite Students</h3>
                            <span style="font-size: 12.5px; font-weight: 700; color: #64748b;">Siswa Teraktif &amp; Berprestasi</span>
                        </div>
                    </div>
                    <span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 100px; text-transform: uppercase;">
                        <i class="fa-solid fa-trophy" style="margin-right: 4px;"></i> Siswa Juara
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

                        // School Color Tag
                        $sNameLower = strtolower($stdSchoolName ?? '');
                        if (str_contains($sNameLower, 'smp')) {
                            $schoolBadgeBg = '#eff6ff';
                            $schoolBadgeColor = '#1d4ed8';
                        } elseif (str_contains($sNameLower, 'sma')) {
                            $schoolBadgeBg = '#ecfdf5';
                            $schoolBadgeColor = '#047857';
                        } else {
                            $schoolBadgeBg = '#fef3c7';
                            $schoolBadgeColor = '#b45309';
                        }

                        // Medal & Podium Details
                        if ($index == 0) {
                            $medalBg = 'linear-gradient(135deg, #f59e0b, #fbbf24)';
                            $medalColor = '#ffffff';
                            $medalIcon = '🥇';
                            $rowClass = 'hof-rank-1';
                            $pointBg = 'linear-gradient(135deg, #f59e0b, #d97706)';
                            $pointColor = '#ffffff';
                        } elseif ($index == 1) {
                            $medalBg = 'linear-gradient(135deg, #94a3b8, #cbd5e1)';
                            $medalColor = '#ffffff';
                            $medalIcon = '🥈';
                            $rowClass = 'hof-rank-normal';
                            $pointBg = '#fef3c7';
                            $pointColor = '#b45309';
                        } elseif ($index == 2) {
                            $medalBg = 'linear-gradient(135deg, #d97706, #fed7aa)';
                            $medalColor = '#ffffff';
                            $medalIcon = '🥉';
                            $rowClass = 'hof-rank-normal';
                            $pointBg = '#fef3c7';
                            $pointColor = '#b45309';
                        } else {
                            $medalBg = '#e2e8f0';
                            $medalColor = '#475569';
                            $medalIcon = '🎖️';
                            $rowClass = 'hof-rank-normal';
                            $pointBg = '#f1f5f9';
                            $pointColor = '#475569';
                        }
                    @endphp
                    <div class="hof-item-row {{ $rowClass }}">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            
                            {{-- Medal Badge --}}
                            <div class="hof-medal-box" style="background: {{ $medalBg }}; color: {{ $medalColor }};">
                                {{ $medalIcon }}
                            </div>

                            {{-- Avatar with Crown for #1 --}}
                            <div class="hof-avatar-wrap">
                                <img src="{{ $std->user->photo_url ?? asset('assets/img/default-avatar.png') }}" class="hof-avatar" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'" />
                                @if($index == 0)
                                    <div class="hof-crown-badge" title="Top Leaderboard">
                                        <i class="fa-solid fa-crown"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Name & School Tag --}}
                            <div style="min-width: 0;">
                                <div style="font-size: 14.5px; font-weight: 900; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 6px;" title="{{ $std->user->name }}">
                                    <span>{{ $std->user->name }}</span>
                                    @if($index == 0)
                                        <i class="fa-solid fa-fire" style="color: #f59e0b; font-size: 13px;"></i>
                                    @endif
                                </div>
                                
                                <div style="margin-top: 3px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    @if($stdSchoolName)
                                        <span style="background: {{ $schoolBadgeBg }}; color: {{ $schoolBadgeColor }}; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px;">
                                            {{ $stdSchoolName }}
                                        </span>
                                    @endif
                                    @if($stdClassName)
                                        <span style="font-size: 11.5px; font-weight: 700; color: #64748b;">
                                            Kelas {{ $stdClassName }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Points Badge --}}
                        <div class="hof-points-pill" style="background: {{ $pointBg }}; color: {{ $pointColor }};">
                            <i class="fa-solid fa-bolt"></i>
                            <span>{{ number_format($std->total_points) }} pts</span>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 32px; font-size: 13px; font-weight: 700; color: #94a3b8;">
                        <i class="fa-solid fa-award" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                        Belum ada data peringkat siswa.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- 👨‍🏫 RIGHT COLUMN: INSPIRATIONAL TEACHERS --}}
            <div class="hof-card" data-aos="fade-left">
                <i class="fa-solid fa-chalkboard-user hof-card-watermark"></i>

                <div class="hof-header">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #6d28d9; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; box-shadow: 0 4px 14px rgba(109, 40, 217, 0.25);">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.02em;">Inspirational Teachers</h3>
                            <span style="font-size: 12.5px; font-weight: 700; color: #64748b;">Kinerja &amp; Dedikasi Pengajar</span>
                        </div>
                    </div>
                    <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 100px; text-transform: uppercase;">
                        <i class="fa-solid fa-star" style="margin-right: 4px;"></i> Guru Teladan
                    </span>
                </div>

                <div class="hof-list">
                    @forelse($topTeachersElite as $index => $tch)
                    @php
                        $tchSchoolName = $tch->user->teacher->school->name ?? $tch->user->school->name ?? null;

                        // School Color Tag
                        $tSchoolLower = strtolower($tchSchoolName ?? '');
                        if (str_contains($tSchoolLower, 'smp')) {
                            $tBadgeBg = '#eff6ff';
                            $tBadgeColor = '#1d4ed8';
                        } elseif (str_contains($tSchoolLower, 'sma')) {
                            $tBadgeBg = '#ecfdf5';
                            $tBadgeColor = '#047857';
                        } else {
                            $tBadgeBg = '#fef3c7';
                            $tBadgeColor = '#b45309';
                        }

                        // Medal & Podium Details
                        if ($index == 0) {
                            $medalBg = 'linear-gradient(135deg, #6366f1, #4f46e5)';
                            $medalColor = '#ffffff';
                            $medalIcon = '🥇';
                            $rowClass = 'hof-rank-1';
                            $pointBg = 'linear-gradient(135deg, #4f46e5, #3730a3)';
                            $pointColor = '#ffffff';
                        } elseif ($index == 1) {
                            $medalBg = 'linear-gradient(135deg, #94a3b8, #cbd5e1)';
                            $medalColor = '#ffffff';
                            $medalIcon = '🥈';
                            $rowClass = 'hof-rank-normal';
                            $pointBg = '#eff6ff';
                            $pointColor = '#1d4ed8';
                        } elseif ($index == 2) {
                            $medalBg = 'linear-gradient(135deg, #d97706, #fed7aa)';
                            $medalColor = '#ffffff';
                            $medalIcon = '🥉';
                            $rowClass = 'hof-rank-normal';
                            $pointBg = '#eff6ff';
                            $pointColor = '#1d4ed8';
                        } else {
                            $medalBg = '#e2e8f0';
                            $medalColor = '#475569';
                            $medalIcon = '🎖️';
                            $rowClass = 'hof-rank-normal';
                            $pointBg = '#f1f5f9';
                            $pointColor = '#475569';
                        }
                    @endphp
                    <div class="hof-item-row {{ $rowClass }}">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            
                            {{-- Medal Badge --}}
                            <div class="hof-medal-box" style="background: {{ $medalBg }}; color: {{ $medalColor }};">
                                {{ $medalIcon }}
                            </div>

                            {{-- Avatar with Crown for #1 --}}
                            <div class="hof-avatar-wrap">
                                <img src="{{ $tch->user->photo_url ?? asset('assets/img/default-avatar.png') }}" class="hof-avatar" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'" />
                                @if($index == 0)
                                    <div class="hof-crown-badge" style="background: #6366f1; color: #ffffff;" title="Guru Teladan Utama">
                                        <i class="fa-solid fa-crown"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Name & School Tag --}}
                            <div style="min-width: 0;">
                                <div style="font-size: 14.5px; font-weight: 900; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 6px;" title="{{ $tch->user->name }}">
                                    <span>{{ $tch->user->name }}</span>
                                    @if($index == 0)
                                        <i class="fa-solid fa-star" style="color: #6366f1; font-size: 13px;"></i>
                                    @endif
                                </div>
                                
                                <div style="margin-top: 3px;">
                                    @if($tchSchoolName)
                                        <span style="background: {{ $tBadgeBg }}; color: {{ $tBadgeColor }}; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px; display: inline-block;">
                                            {{ $tchSchoolName }}
                                        </span>
                                    @else
                                        <span style="font-size: 11.5px; font-weight: 700; color: #64748b;">
                                            Tenaga Pendidik Pembda
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Points Badge --}}
                        <div class="hof-points-pill" style="background: {{ $pointBg }}; color: {{ $pointColor }};">
                            <i class="fa-solid fa-sparkles"></i>
                            <span>{{ number_format($tch->total_points) }} pts</span>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 32px; font-size: 13px; font-weight: 700; color: #94a3b8;">
                        <i class="fa-solid fa-chalkboard-user" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                        Belum ada data peringkat guru.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Gold Trophy Celebratory CTA Button --}}
        <div style="text-align: center;" data-aos="fade-up">
            <a href="{{ route('reputation.leaderboard') }}" class="hof-cta-btn">
                <i class="fa-solid fa-trophy" style="font-size: 18px;"></i>
                <span>Lihat Papan Peringkat Lengkap (Hall of Fame)</span>
                <i class="fa-solid fa-arrow-right" style="font-size: 13px;"></i>
            </a>
        </div>

    </div>
</section>
