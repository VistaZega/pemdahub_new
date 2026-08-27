{{-- PRESTASI SISWA — Dynamic School Themed Bento Cards (Hijau SMA, Biru SMP, Coklat SMK) --}}
<style>
    .prestasi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .prestasi-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 640px) {
        .prestasi-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }
    }

    .prestasi-item-card {
        border-radius: 24px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        text-align: center;
        padding: 38px 24px 24px;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .prestasi-item-card:hover {
        transform: translateY(-6px);
    }

    @keyframes starPulse {
        0%, 100% { opacity: 0.8; transform: scale(1); }
        50% { opacity: 0.3; transform: scale(0.85); }
    }
</style>

<section id="prestasi" class="section" style="background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%); padding: 85px 0 95px 0;">
    <div class="fw">
        <div style="text-align:center; margin-bottom:56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center; margin-bottom: 14px;">
                <div class="section-label-dot pulse" style="background:#fbbf24;"></div>
                <span class="section-label-text" style="color:#fbbf24;">Prestasi &amp; Pencapaian Siswa</span>
            </div>
            <h2 class="h1" style="color:#ffffff; font-size:clamp(30px, 4vw, 44px); font-weight:900; letter-spacing:-0.025em; margin-bottom:14px;">
                Ukir Prestasi, Harumkan Nama.
            </h2>
            <p class="body-lg" style="max-width:680px; margin:0 auto; color:rgba(255,255,255,0.75);">
                Total <strong style="color:#fbbf24;"><span data-count="{{ $totalAchievements }}">0</span> prestasi</strong> membanggakan telah diraih oleh siswa-siswi terbaik kami di berbagai tingkatan kompetisi.
            </p>
        </div>

        @if(isset($achievements) && $achievements->count() > 0)
            <div class="prestasi-grid">
                @foreach($achievements as $index => $achievement)
                    @php
                        // Deteksi Unit Sekolah Siswa untuk Menentukan Warna Kartu:
                        // 1. SMA -> Hijau
                        // 2. SMP -> Biru
                        // 3. SMK -> Coklat
                        $schoolName = strtolower($achievement->student?->school?->name ?? '');
                        $schoolType = strtolower($achievement->student?->school?->type ?? '');

                        if (str_contains($schoolName, 'sma') || str_contains($schoolType, 'sma')) {
                            // SMA : HIJAU
                            $unitTheme = [
                                'base_color' => '#042f2e',
                                'bg' => 'linear-gradient(145deg, #064e3b 0%, #065f46 50%, #047857 100%)',
                                'shadow' => 'rgba(16, 185, 129, 0.35)',
                                'hover_shadow' => 'rgba(16, 185, 129, 0.55)',
                                'ring' => 'linear-gradient(135deg, #34d399, #10b981)',
                                'badge_bg' => 'rgba(52, 211, 153, 0.2)',
                                'badge_text' => '#6ee7b7',
                                'school_badge_bg' => '#ecfdf5',
                                'school_badge_color' => '#047857',
                                'icon_badge_bg' => '#10b981',
                                'icon_badge_color' => '#ffffff',
                                'rank_color' => '#6ee7b7',
                                'unit_label' => 'SMAS Pembda 1'
                            ];
                        } elseif (str_contains($schoolName, 'smp') || str_contains($schoolType, 'smp')) {
                            // SMP : BIRU
                            $unitTheme = [
                                'base_color' => '#172554',
                                'bg' => 'linear-gradient(145deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%)',
                                'shadow' => 'rgba(37, 99, 235, 0.35)',
                                'hover_shadow' => 'rgba(37, 99, 235, 0.55)',
                                'ring' => 'linear-gradient(135deg, #60a5fa, #3b82f6)',
                                'badge_bg' => 'rgba(96, 165, 250, 0.2)',
                                'badge_text' => '#93c5fd',
                                'school_badge_bg' => '#eff6ff',
                                'school_badge_color' => '#1d4ed8',
                                'icon_badge_bg' => '#2563eb',
                                'icon_badge_color' => '#ffffff',
                                'rank_color' => '#93c5fd',
                                'unit_label' => 'SMPS Pembda 2'
                            ];
                        } else {
                            // SMK : COKLAT
                            $unitTheme = [
                                'base_color' => '#451a03',
                                'bg' => 'linear-gradient(145deg, #78350f 0%, #92400e 50%, #b45309 100%)',
                                'shadow' => 'rgba(217, 119, 6, 0.35)',
                                'hover_shadow' => 'rgba(217, 119, 6, 0.55)',
                                'ring' => 'linear-gradient(135deg, #fbbf24, #d97706)',
                                'badge_bg' => 'rgba(251, 191, 36, 0.2)',
                                'badge_text' => '#fde68a',
                                'school_badge_bg' => '#fef3c7',
                                'school_badge_color' => '#b45309',
                                'icon_badge_bg' => '#d97706',
                                'icon_badge_color' => '#ffffff',
                                'rank_color' => '#fde68a',
                                'unit_label' => 'SMKS Pembda Nias'
                            ];
                        }

                        // Level styling
                        $levelColors = [
                            'internasional' => ['bg' => '#f59e0b', 'text' => '#ffffff', 'label' => 'Internasional'],
                            'nasional'      => ['bg' => '#ef4444', 'text' => '#ffffff', 'label' => 'Nasional'],
                            'propinsi'      => ['bg' => '#8b5cf6', 'text' => '#ffffff', 'label' => 'Provinsi'],
                            'kabupaten'     => ['bg' => '#0ea5e9', 'text' => '#ffffff', 'label' => 'Kabupaten'],
                            'sekolah'       => ['bg' => '#64748b', 'text' => '#ffffff', 'label' => 'Sekolah'],
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
                            'akademik' => 'fa-book-open',
                            'olahraga' => 'fa-trophy',
                            'seni' => 'fa-palette',
                            'keagamaan' => 'fa-star-and-crescent',
                            'karir' => 'fa-briefcase',
                            'lainnya' => 'fa-medal'
                        ];
                        $icon = $typeIcons[$achievement->category ?? 'lainnya'] ?? 'fa-medal';
                        
                        $delay = 100 + ($index * 80);
                    @endphp
                    
                    <div class="prestasi-item-card" 
                         data-aos="fade-up" 
                         data-aos-delay="{{ $delay }}"
                         style="box-shadow: 0 14px 34px -8px {{ $unitTheme['shadow'] }}; padding: 0; min-height: 420px; background-color: {{ $unitTheme['base_color'] }}; border: 1px solid rgba(255,255,255,0.05);"
                         onmouseover="this.style.boxShadow='0 22px 48px -8px {{ $unitTheme['hover_shadow'] }}'; this.querySelector('.bg-img').style.transform='scale(1.08)';"
                         onmouseout="this.style.boxShadow='0 14px 34px -8px {{ $unitTheme['shadow'] }}'; this.querySelector('.bg-img').style.transform='scale(1)';">
                        
                        <!-- Background Color Base -->
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; background-color: {{ $unitTheme['base_color'] }};"></div>
                        
                        <!-- Background Image Siswa (Di sebelah kanan, 70% lebar kartu agar tidak terlalu zoom in) -->
                        <img src="{{ $achievement->student?->photo_url ?? asset('images/default-student.jpg') }}" 
                             class="bg-img"
                             style="position:absolute; top:0; right:0; width:70%; height:100%; object-fit:cover; object-position:right top; transition:transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);" 
                             alt="{{ $achievement->student?->full_name ?? 'Siswa' }}" 
                             onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}'">
                        
                        <!-- Gradient Overlay 1: Dari Kiri ke Kanan (Blend sisi kiri gambar dengan background base) -->
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to right, {{ $unitTheme['base_color'] }} 30%, transparent 70%);"></div>
                        
                        <!-- Gradient Overlay 2: Dari Bawah ke Atas (Agar teks bawah terbaca) -->
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to top, {{ $unitTheme['base_color'] }} 0%, rgba(0,0,0,0.8) 45%, transparent 65%);"></div>

                        <!-- Top Badge Tingkat -->
                        <div style="position:absolute; top:0; right:20px; background:{{ $levelStyle['bg'] }}; color:{{ $levelStyle['text'] }}; padding:6px 16px; border-radius:0 0 12px 12px; font-size:11.5px; font-weight:800; letter-spacing:0.04em; text-transform:uppercase; box-shadow:0 4px 10px rgba(0,0,0,0.25); z-index:2;">
                            <i class="fa-solid fa-globe" style="margin-right:4px;"></i> {{ $levelStyle['label'] }}
                        </div>

                        <!-- Konten Teks Bawah -->
                        <div style="position:absolute; bottom:0; left:0; width:100%; padding:24px; display:flex; flex-direction:column; justify-content:flex-end; z-index:2; text-align:left;">
                            
                            <!-- Badge Sekolah & Kategori Prestasi -->
                            <div style="margin-bottom:12px; display:flex; gap:8px; flex-wrap:wrap;">
                                <span style="background:rgba(255,255,255,0.15); backdrop-filter:blur(4px); color:#ffffff; font-size:11px; font-weight:800; padding:4px 10px; border-radius:100px; border:1px solid rgba(255,255,255,0.2);">
                                    <i class="fa-solid fa-school" style="margin-right:4px;"></i> {{ $achievement->student?->school?->name ?? $unitTheme['unit_label'] }}
                                </span>
                                <span style="background:rgba(255,255,255,0.15); backdrop-filter:blur(4px); color:#ffffff; font-size:11px; font-weight:800; padding:4px 10px; border-radius:100px; border:1px solid rgba(255,255,255,0.2);">
                                    <i class="fa-solid {{ $icon }}" style="margin-right:4px; color: {{ $unitTheme['rank_color'] }};"></i> {{ ucfirst($achievement->category ?? 'Lainnya') }}
                                </span>
                            </div>

                            <!-- Nama Siswa -->
                            <h4 style="font-size:22px; font-weight:900; color:#ffffff; margin-bottom:10px; line-height:1.2; letter-spacing:-0.01em; text-shadow: 0 2px 4px rgba(0,0,0,0.6);">
                                {{ $achievement->student?->full_name ?? 'Siswa/i Pembda' }}
                            </h4>
                            
                            <!-- Judul Prestasi -->
                            <div style="font-size:14.5px; line-height:1.5; color:rgba(255,255,255,0.9); font-weight:600; margin-bottom:20px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">
                                {{ $achievement->title }}
                            </div>
                            
                            <!-- Footer: Juara & Tanggal -->
                            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid rgba(255,255,255,0.15); padding-top:16px;">
                                <div style="font-weight:900; color:{{ $unitTheme['rank_color'] }}; font-size:15px; display:flex; align-items:center; gap:6px; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">
                                    <i class="fa-solid fa-award"></i> {{ $rankLabel }}
                                </div>
                                <div style="font-size:12.5px; color:rgba(255,255,255,0.7); font-weight:700;">
                                    <i class="fa-regular fa-calendar-alt" style="margin-right:4px;"></i> {{ \Carbon\Carbon::parse($achievement->incident_date)->translatedFormat('M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bcard text-center" data-aos="fade-up" style="max-width:700px; margin:0 auto; padding:48px; background:rgba(255,255,255,0.05); border-radius:24px;">
                <div class="icon-circle" style="background:rgba(251,191,36,0.15); color:#fbbf24; width:80px; height:80px; font-size:36px; margin:0 auto 24px;">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h3 class="h2" style="color:#ffffff; margin-bottom:12px;">Generasi Emas Pembda</h3>
                <p class="body-lg" style="color:rgba(255,255,255,0.7);">Siswa-siswi kami senantiasa dibina untuk meraih puncak prestasi akademik maupun non-akademik di seluruh tingkatan kompetisi.</p>
            </div>
        @endif
    </div>
</section>
