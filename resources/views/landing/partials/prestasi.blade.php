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
                    
                    <div class="bcard hover-glow prestasi-card" data-aos="fade-up" data-aos-delay="{{ $delay }}" style="position:relative; overflow:hidden; display:flex; flex-direction:column; text-align:center; padding-top:40px;">
                        
                        <!-- Watermark Piala -->
                        <i class="fa-solid fa-trophy" style="position:absolute; right:-20px; bottom:-10px; font-size:140px; opacity:0.04; color:#ffffff; transform:rotate(-15deg); pointer-events:none;"></i>
                        
                        <!-- Bintang Hiasan -->
                        <i class="fa-solid fa-star" style="position:absolute; top:20px; left:20px; color:var(--gold); opacity:0.6; font-size:14px; animation: pulse 2s infinite;"></i>
                        <i class="fa-solid fa-star" style="position:absolute; top:40px; right:30px; color:var(--gold); opacity:0.8; font-size:20px; animation: pulse 2s infinite 1s;"></i>

                        <!-- Top Badge Tingkat -->
                        <div style="position:absolute; top:0; left:50%; transform:translateX(-50%); background:{{ $levelStyle['bg'] }}; color:{{ $levelStyle['text'] }}; padding:6px 20px; border-radius:0 0 16px 16px; font-size:12px; font-weight:700; box-shadow:0 4px 10px rgba(0,0,0,0.1); border:1px solid rgba(0,0,0,0.05); border-top:none;">
                            <i class="fa-solid fa-globe" style="margin-right:4px;"></i> Tingkat {{ $levelStyle['label'] }}
                        </div>

                        <!-- Foto Siswa Besar -->
                        <div style="position:relative; width:110px; height:110px; margin:0 auto 16px; border-radius:50%; padding:4px; background:linear-gradient(135deg, var(--gold), #fef08a); box-shadow:0 8px 24px rgba(251, 191, 36, 0.4);">
                            <img src="{{ $achievement->student?->photo_url ?? asset('assets/img/default-avatar.png') }}" style="width:100%; height:100%; border-radius:50%; object-fit:cover; border:3px solid var(--bg-card);" alt="{{ $achievement->student?->full_name ?? 'Siswa' }}" onerror="this.src='{{ asset('assets/img/default-avatar.png') }}'">
                            
                            <!-- Ikon Kategori -->
                            <div style="position:absolute; bottom:-4px; right:-4px; width:36px; height:36px; background:var(--gold); color:#854d0e; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #ffffff; font-size:15px; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>
                        </div>

                        <!-- Nama Siswa -->
                        <h4 style="font-size:18px; font-weight:800; color:var(--text-primary); margin-bottom:4px; line-height:1.2;">
                            {{ $achievement->student?->full_name ?? 'Siswa/i Pembda' }}
                        </h4>
                        
                        <!-- Sekolah -->
                        <div style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">
                            <i class="fa-solid fa-school" style="margin-right:4px; color:var(--gold);"></i> 
                            {{ $achievement->student?->school?->name ?? 'Perguruan PEMBDA Nias' }}
                        </div>
                        
                        <!-- Judul Prestasi -->
                        <div style="background:var(--bg); border-radius:12px; padding:16px 12px; border:1px solid var(--border); margin-bottom:20px; flex-grow:1; display:flex; align-items:center; justify-content:center; position:relative; z-index:1;">
                            <div style="font-size:14px; line-height:1.5; color:var(--text-primary); font-weight:600;">
                                {{ $achievement->title }}
                            </div>
                        </div>
                        
                        <!-- Footer: Juara & Tanggal -->
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px dashed rgba(255,255,255,0.2); padding-top:16px; position:relative; z-index:1;">
                            <div style="font-weight:800; color:var(--gold); font-size:15px; background:rgba(251,191,36,0.15); padding:6px 14px; border-radius:20px;">
                                <i class="fa-solid fa-award"></i> {{ $rankLabel }}
                            </div>
                            <div style="font-size:12px; color:var(--text-secondary); font-weight:600;">
                                <i class="fa-regular fa-calendar-alt" style="margin-right:4px;"></i> {{ \Carbon\Carbon::parse($achievement->incident_date)->translatedFormat('M Y') }}
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

<style>
.prestasi-card {
    /* Gradient biru tua ke indigo yang sangat premium dan menonjol */
    background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 100%) !important;
    
    /* Override local variables for dark mode inside this specific card */
    --bg: rgba(255, 255, 255, 0.1) !important; /* Inner student box background */
    --border: rgba(255, 255, 255, 0.2) !important;
    --text-primary: #ffffff !important;
    --text-secondary: #e2e8f0 !important;
    --text-muted: #cbd5e1 !important;
    
    color: var(--text-primary) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    box-shadow: 0 10px 30px -10px rgba(49, 46, 129, 0.5) !important;
}

.prestasi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px -15px rgba(55, 48, 163, 0.6) !important;
}

@keyframes pulse {
    0%, 100% { opacity: 0.8; transform: scale(1); }
    50% { opacity: 0.3; transform: scale(0.8); }
}
</style>
