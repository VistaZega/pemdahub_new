{{-- PRESTASI SISWA — New Section --}}
<section id="prestasi" class="section" style="background:var(--bg);">
    <div class="fw">
        <div style="text-align:center; margin-bottom:56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center;">
                <div class="section-label-dot" style="background:var(--gold);"></div>
                <span class="section-label-text" style="color:var(--gold-bright);">Prestasi & Pencapaian</span>
            </div>
            <h2 class="h1" style="margin-bottom:12px;">Ukir Prestasi, Harumkan Nama</h2>
            <p class="body-lg" style="max-width:600px; margin:0 auto;">
                Total <strong><span data-count="{{ $totalAchievements }}">0</span> prestasi</strong> membanggakan telah ditorehkan oleh siswa-siswi terbaik kami di berbagai tingkatan.
            </p>
        </div>

        @if(isset($achievements) && $achievements->count() > 0)
            <div class="bento bento-3">
                @foreach($achievements as $index => $achievement)
                    @php
                        // Level styling
                        $levelColors = [
                            'internasional' => ['bg' => 'var(--gold-bg)', 'text' => 'var(--gold-bright)', 'label' => 'Internasional'],
                            'nasional'      => ['bg' => 'var(--coral-bg)', 'text' => 'var(--coral)', 'label' => 'Nasional'],
                            'propinsi'      => ['bg' => 'var(--violet-bg)', 'text' => 'var(--violet)', 'label' => 'Provinsi'],
                            'kabupaten'     => ['bg' => 'var(--blue-bg)', 'text' => 'var(--blue)', 'label' => 'Kabupaten'],
                            'sekolah'       => ['bg' => 'var(--bg)', 'text' => 'var(--text-secondary)', 'label' => 'Sekolah'],
                        ];
                        
                        $level = $achievement->achievement_level ?? 'sekolah';
                        $levelStyle = $levelColors[$level] ?? $levelColors['sekolah'];
                        
                        // Rank formatting
                        $ranks = [
                            'juara_1' => 'Juara 1',
                            'juara_2' => 'Juara 2',
                            'juara_3' => 'Juara 3',
                            'harapan_1' => 'Harapan 1',
                            'harapan_2' => 'Harapan 2',
                            'harapan_3' => 'Harapan 3',
                            'finalis' => 'Finalis/Top 10',
                            'peserta' => 'Peserta',
                            'best_speaker' => 'Best Speaker',
                            'mvp' => 'MVP'
                        ];
                        $rankLabel = $ranks[$achievement->ranking ?? 'peserta'] ?? 'Peserta';
                        
                        // Type Icon
                        $typeIcons = [
                            'akademik' => 'fa-book',
                            'olahraga' => 'fa-trophy',
                            'seni' => 'fa-palette',
                            'keagamaan' => 'fa-star-and-crescent',
                            'karir' => 'fa-briefcase',
                            'lainnya' => 'fa-medal'
                        ];
                        $icon = $typeIcons[$achievement->category ?? 'lainnya'] ?? 'fa-star';
                        
                        $delay = 100 + ($index * 100);
                    @endphp
                    
                    <div class="bcard hover-glow" data-aos="fade-up" data-aos-delay="{{ $delay }}" style="display:flex; flex-direction:column;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                            <span class="feature-pill" style="background:{{ $levelStyle['bg'] }}; color:{{ $levelStyle['text'] }}; border: 1px solid rgba(0,0,0,0.05);">
                                <i class="fa-solid fa-globe"></i> Tingkat {{ $levelStyle['label'] }}
                            </span>
                            <div class="icon-circle" style="width:36px; height:36px; font-size:16px; background:var(--bg); border:1px solid var(--border); color:var(--text-secondary);">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>
                        </div>
                        
                        <h3 class="h3" style="margin-bottom:16px; font-size:16px; line-height:1.4; flex-grow:1;">{{ $achievement->title }}</h3>
                        
                        <div style="margin-bottom:16px; padding:12px; background:var(--bg); border-radius:10px; border:1px solid var(--border);">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:40px; height:40px; border-radius:50%; overflow:hidden; border:2px solid var(--border); flex-shrink:0;">
                                    <img src="{{ $achievement->student?->photo_url ?? asset('assets/img/default-avatar.png') }}" style="width:100%; height:100%; object-fit:cover;" alt="{{ $achievement->student?->full_name ?? 'Siswa' }}" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'">
                                </div>
                                <div>
                                    <div style="font-weight:700; font-size:14px; color:var(--text-primary); margin-bottom:2px;">
                                        {{ $achievement->student?->full_name ?? 'Siswa/i Pembda' }}
                                    </div>
                                    <div style="font-size:12px; color:var(--text-secondary);">
                                        <i class="fa-solid fa-school" style="color:var(--text-muted); margin-right:4px;"></i>
                                        {{ $achievement->student?->school?->name ?? 'Perguruan PEMBDA Nias' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:16px;">
                            <div style="font-weight:800; color:var(--indigo-dark); font-size:15px;">
                                <i class="fa-solid fa-award" style="color:var(--gold);"></i> {{ $rankLabel }}
                            </div>
                            <div style="font-size:12px; color:var(--text-secondary); font-weight:600;">
                                {{ \Carbon\Carbon::parse($achievement->incident_date)->translatedFormat('M Y') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bcard text-center" data-aos="fade-up" style="max-width:700px; margin:0 auto; padding:48px;">
                <div class="icon-circle" style="background:var(--gold-bg); color:var(--gold); width:80px; height:80px; font-size:36px; margin:0 auto 24px;">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h3 class="h2" style="margin-bottom:12px;">Generasi Emas Berikutnya</h3>
                <p class="body-lg">Siswa-siswi kami senantiasa dibina untuk meraih puncak prestasi akademik maupun non-akademik. Segera wujudkan mimpimu bersama kami.</p>
            </div>
        @endif
    </div>
</section>
