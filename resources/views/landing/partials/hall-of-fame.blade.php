<section class="section" id="hall-of-fame-section" style="background: var(--bg); padding: 90px 0;">
    <div class="fw">

        {{-- Section Header --}}
        <div style="text-align: center; max-width: 760px; margin: 0 auto 52px;" data-aos="fade-up">
            <div class="section-label" style="justify-content: center; margin-bottom: 14px;">
                <span class="section-label-dot" style="background: var(--gold);"></span>
                <span class="section-label-text" style="color: var(--amber); font-weight:800;">🏆 PEMBDA ELITE LEADERBOARD</span>
            </div>
            <h2 class="h1" style="margin-bottom: 14px; color: #0f172a;">
                Hall of Fame <span style="color: var(--indigo-light);">Pembda Elite</span>
            </h2>
            <p class="body-lg" style="margin: 0 auto; color: #334155; font-weight: 500;">
                Panggung apresiasi kehormatan real-time bagi Siswa dan Guru Perguruan Pembda dengan aktivitas, karya digital, dan dedikasi terbaik.
            </p>
        </div>

        {{-- Side-by-Side Dual Column Showcase --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 28px; margin-bottom: 48px;">

            {{-- 🎓 LEFT COLUMN: TOP ELITE STUDENTS --}}
            <div class="bcard" style="padding: 36px 32px;" data-aos="fade-right">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 18px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div class="icon-circle" style="width: 46px; height: 46px; background: #fffbeb; color: #d97706; border-radius: 14px; font-size: 20px; box-shadow: 0 4px 12px rgba(245,158,11,0.15);">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Top Elite Students</h3>
                            <span style="font-size: 12px; font-weight: 700; color: #64748b;">Siswa Berprestasi & Teraktif</span>
                        </div>
                    </div>
                    <span style="background: #f5f3ff; color: #7c3aed; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; border: 1px solid #ddd6fe;">
                        Leaderboard Siswa
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
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
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px 18px; transition: var(--transition-smooth);" onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            <div style="width: 32px; height: 32px; background: {{ $index == 0 ? '#fbbf24' : ($index == 1 ? '#94a3b8' : ($index == 2 ? '#d97706' : '#e2e8f0')) }}; color: {{ $index <= 2 ? '#0f172a' : '#475569' }}; font-weight: 900; font-size: 13px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                #{{ $index + 1 }}
                            </div>
                            <img src="{{ $std->user->photo_url }}" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #ffffff; object-fit: cover; background: #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.08);" alt="{{ $std->user->name }}" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'" />
                            <div style="min-width: 0;">
                                <div style="font-size: 14px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $std->user->name }}">
                                    {{ $std->user->name }}
                                </div>
                                <div style="font-size: 12px; font-weight: 600; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $stdSubInfo }}">
                                    {{ $stdSubInfo }}
                                </div>
                            </div>
                        </div>
                        <div style="background: #0f172a; color: #fbbf24; font-weight: 900; font-size: 13px; padding: 6px 14px; border-radius: 12px; white-space: nowrap; font-variant-numeric: tabular-nums; margin-left: 10px;">
                            {{ number_format($std->total_points) }} pts
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
            <div class="bcard" style="padding: 36px 32px;" data-aos="fade-left">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 18px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div class="icon-circle" style="width: 46px; height: 46px; background: #eef2ff; color: #4338ca; border-radius: 14px; font-size: 20px; box-shadow: 0 4px 12px rgba(67,56,202,0.15);">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Inspirational Teachers</h3>
                            <span style="font-size: 12px; font-weight: 700; color: #64748b;">Kinerja & Dedikasi Pengajar</span>
                        </div>
                    </div>
                    <span style="background: #eef2ff; color: #4338ca; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; border: 1px solid #c7d2fe;">
                        Leaderboard Guru
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @forelse($topTeachersElite as $index => $tch)
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px 18px; transition: var(--transition-smooth);" onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            <div style="width: 32px; height: 32px; background: {{ $index == 0 ? '#4338ca' : ($index == 1 ? '#6366f1' : ($index == 2 ? '#818cf8' : '#e2e8f0')) }}; color: {{ $index <= 2 ? '#ffffff' : '#475569' }}; font-weight: 900; font-size: 13px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                #{{ $index + 1 }}
                            </div>
                            <img src="{{ $tch->user->photo_url }}" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #ffffff; object-fit: cover; background: #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.08);" alt="{{ $tch->user->name }}" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'" />
                            <div style="min-width: 0;">
                                <div style="font-size: 14px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tch->user->name }}
                                </div>
                                <div style="font-size: 12px; font-weight: 600; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tch->user->teacher->school->name ?? 'Tenaga Pendidik Pembda' }}
                                </div>
                            </div>
                        </div>
                        <div style="background: #0f172a; color: #fbbf24; font-weight: 900; font-size: 13px; padding: 6px 14px; border-radius: 12px; white-space: nowrap; font-variant-numeric: tabular-nums; margin-left: 10px;">
                            {{ number_format($tch->total_points) }} pts
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
            <a href="{{ route('reputation.leaderboard') }}" class="btn btn-dark" style="padding: 16px 36px; font-size: 14px; font-weight: 800; letter-spacing: 0.02em;">
                <i class="fa-solid fa-trophy" style="color: #fbbf24;"></i>
                <span>Lihat Papan Peringkat Lengkap (Hall of Fame)</span>
                <i class="fa-solid fa-arrow-right" style="margin-left: 4px;"></i>
            </a>
        </div>

    </div>
</section>
