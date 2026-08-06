<section class="section" id="hall-of-fame-section" style="background: var(--bg); padding: 70px 0;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">

        {{-- Section Header --}}
        <div style="text-align: center; max-width: 700px; margin: 0 auto 48px;" data-aos="fade-up">
            <div class="section-label" style="justify-content: center; margin-bottom: 12px;">
                <span class="section-label-dot" style="background: var(--gold);"></span>
                <span class="section-label-text" style="color: var(--gold);">🏆 PEMBDA ELITE LEADERBOARD</span>
            </div>
            <h2 style="font-size: 32px; font-weight: 900; color: var(--text-primary); letter-spacing: -0.5px; margin-bottom: 12px;">
                Hall of Fame <span style="color: var(--indigo);">Pembda Elite</span>
            </h2>
            <p style="font-size: 15px; color: var(--text-secondary); font-weight: 500; line-height: 1.6;">
                Panggung apresiasi kehormatan real-time bagi Siswa dan Guru Perguruan Pembda dengan aktivitas, karya digital, dan prestasi terbaik.
            </p>
        </div>

        {{-- Side-by-Side Dual Column Showcase --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px; margin-bottom: 40px;">

            {{-- 🎓 LEFT COLUMN: TOP ELITE STUDENTS --}}
            <div style="background: #ffffff; border: 2px solid #000000; border-radius: 28px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.06);" data-aos="fade-right">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000000; padding-bottom: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: #fbbf24; border: 2px solid #000000; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #000000;">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 17px; font-weight: 900; color: #000000; margin: 0;">Top Elite Students</h3>
                            <span style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Siswa Berprestasi</span>
                        </div>
                    </div>
                    <span style="background: #f3e8ff; color: #6b21a8; border: 1.5px solid #000000; font-size: 10px; font-weight: 900; padding: 4px 10px; border-radius: 12px; text-transform: uppercase;">
                        Leaderboard Siswa
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @forelse($topStudentsElite as $index => $std)
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 2px solid #000000; border-radius: 18px; padding: 14px; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            <div style="width: 32px; height: 32px; background: {{ $index == 0 ? '#fbbf24' : '#000000' }}; color: {{ $index == 0 ? '#000000' : '#ffffff' }}; font-weight: 900; font-size: 13px; border-radius: 10px; border: 1.5px solid #000000; display: flex; align-items: center; justify-content: center; shrink: 0;">
                                #{{ $index + 1 }}
                            </div>
                            <img src="{{ $std->user->photo_url }}" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #000000; object-fit: cover; background: #fff;" />
                            <div style="min-width: 0;">
                                <div style="font-size: 13px; font-weight: 900; color: #000000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $std->user->name }}
                                </div>
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $std->user->student->classroom->class_name ?? 'Siswa Pembda' }}
                                </div>
                            </div>
                        </div>
                        <div style="background: #000000; color: #fbbf24; font-weight: 900; font-size: 12px; padding: 6px 12px; border-radius: 12px; border: 1.5px solid #000000; white-space: nowrap;">
                            {{ number_format($std->total_points) }} pts
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 20px; font-size: 12px; font-weight: 700; color: #94a3b8;">
                        Belum ada data peringkat siswa.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- 👨‍🏫 RIGHT COLUMN: INSPIRATIONAL GURU --}}
            <div style="background: #ffffff; border: 2px solid #000000; border-radius: 28px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.06);" data-aos="fade-left">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000000; padding-bottom: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: #6366f1; border: 2px solid #000000; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #ffffff;">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 17px; font-weight: 900; color: #000000; margin: 0;">Inspirational Teachers</h3>
                            <span style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Kinerja & Dedikasi</span>
                        </div>
                    </div>
                    <span style="background: #e0e7ff; color: #3730a3; border: 1.5px solid #000000; font-size: 10px; font-weight: 900; padding: 4px 10px; border-radius: 12px; text-transform: uppercase;">
                        Leaderboard Guru
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @forelse($topTeachersElite as $index => $tch)
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 2px solid #000000; border-radius: 18px; padding: 14px; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                            <div style="width: 32px; height: 32px; background: {{ $index == 0 ? '#6366f1' : '#000000' }}; color: #ffffff; font-weight: 900; font-size: 13px; border-radius: 10px; border: 1.5px solid #000000; display: flex; align-items: center; justify-content: center; shrink: 0;">
                                #{{ $index + 1 }}
                            </div>
                            <img src="{{ $tch->user->photo_url }}" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #000000; object-fit: cover; background: #fff;" />
                            <div style="min-width: 0;">
                                <div style="font-size: 13px; font-weight: 900; color: #000000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tch->user->name }}
                                </div>
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $tch->user->teacher->school->name ?? 'Guru Pembda' }}
                                </div>
                            </div>
                        </div>
                        <div style="background: #000000; color: #fbbf24; font-weight: 900; font-size: 12px; padding: 6px 12px; border-radius: 12px; border: 1.5px solid #000000; white-space: nowrap;">
                            {{ number_format($tch->total_points) }} pts
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 20px; font-size: 12px; font-weight: 700; color: #94a3b8;">
                        Belum ada data peringkat guru.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- CTA Button to Full Hall of Fame Page --}}
        <div style="text-align: center;" data-aos="fade-up">
            <a href="{{ route('reputation.leaderboard') }}" class="btn-cta dark" style="padding: 14px 32px; font-size: 13px; font-weight: 900; text-transform: uppercase; border-radius: 16px; border: 2px solid #000000; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.15);">
                <span><i class="fa-solid fa-trophy" style="color: #fbbf24;"></i> Lihat Papan Peringkat Lengkap (Hall of Fame)</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
</section>
