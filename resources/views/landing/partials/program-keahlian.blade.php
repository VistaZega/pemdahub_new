{{-- PROGRAM KEAHLIAN — Official Major Colors with Custom Major Silhouettes --}}
<style>
    .program-section {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 85px 0 95px 0;
        position: relative;
    }

    .program-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    @media (max-width: 1200px) {
        .program-grid-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 640px) {
        .program-grid-4 {
            grid-template-columns: 1fr;
            gap: 18px;
        }
    }

    .vibrant-program-card {
        border-radius: 24px;
        padding: 28px 22px;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .vibrant-program-card:hover {
        transform: translateY(-6px);
    }

    /* Silhouette Artwork for Majors */
    .program-silhouette {
        position: absolute;
        right: -15px;
        bottom: -15px;
        width: 160px;
        height: 160px;
        opacity: 0.12;
        color: #0f172a;
        pointer-events: none;
        z-index: 0;
        transition: all 0.35s ease;
    }

    .vibrant-program-card:hover .program-silhouette {
        opacity: 0.22;
        transform: scale(1.08) rotate(-4deg);
    }

    .program-card-content {
        position: relative;
        z-index: 1;
    }

    .program-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1.5px solid rgba(15, 23, 42, 0.12);
        position: relative;
        z-index: 1;
    }

    .program-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .program-title {
        font-size: 17.5px;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -0.02em;
    }

    .program-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 100px;
        display: inline-block;
    }

    .subprogram-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }

    .subprogram-item {
        background: #ffffff;
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        transition: all 0.2s ease;
    }

    .subprogram-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.1);
    }

    .subprogram-name {
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .subprogram-desc {
        font-size: 12px;
        font-weight: 500;
        color: #475569;
        line-height: 1.5;
    }

    .program-card-footer {
        padding-top: 14px;
        border-top: 1.5px solid rgba(15, 23, 42, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 800;
        color: #0f172a;
        position: relative;
        z-index: 1;
    }

    /* Single Line Title */
    .single-line-title {
        white-space: nowrap;
        font-size: clamp(22px, 3.4vw, 42px);
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.025em;
        margin-bottom: 14px;
    }

    @media (max-width: 768px) {
        .single-line-title {
            white-space: normal;
            font-size: 26px;
        }
    }
</style>

<section id="program" class="program-section">
    <div class="fw">
        
        {{-- Section Header --}}
        <div style="text-align: center; max-width: 850px; margin: 0 auto 56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content: center; margin-bottom: 14px;">
                <div class="section-label-dot" style="background: #ea580c;"></div>
                <span class="section-label-text" style="color: #c2410c;">Pendidikan Vokasi Terpadu</span>
            </div>
            
            {{-- Syarat 1: Judul harus dalam SATU BARIS --}}
            <h2 class="single-line-title">
                Konsentrasi Keahlian <span style="background: linear-gradient(135deg, #ea580c, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">SMKS Pembda Nias</span>
            </h2>
            
            <p class="body-lg" style="color: #475569; max-width: 720px; margin: 0 auto;">
                Mencetak lulusan terampil, bersertifikasi keahlian, siap kerja langsung di dunia usaha &amp; dunia industri (DUDI), serta berjiwa wirausaha tangguh.
            </p>
        </div>

        {{-- Syarat 2: 4 Program & 5 Konsentrasi Keahlian dengan Warna & Siluet Resmi Jurusan --}}
        <div class="program-grid-4" data-aos="fade-up" data-aos-delay="100">
            
            @php
                // Pemetaan Warna & Siluet Resmi Jurusan SMKS Pembda Nias:
                // 1. Otomotif (TO) -> Orange (#fb923c)
                // 2. Elektronika (TE) -> Merah (#f87171)
                // 3. Komputer (TJKT) -> Biru (#60a5fa)
                // 4. Konstruksi (TKP) -> Kuning (#fbbf24)
                $configProgram = [
                    'TO' => [
                        'card_bg' => '#fb923c',
                        'card_shadow' => 'rgba(249, 115, 22, 0.32)',
                        'hover_shadow' => 'rgba(249, 115, 22, 0.45)',
                        'icon' => 'fa-solid fa-car-side',
                        'icon_bg' => '#ffffff',
                        'icon_color' => '#ea580c',
                        'item_icon' => 'fa-solid fa-wrench',
                        'tag' => 'Otomotif & Mesin',
                        'silhouette_svg' => '
                            <path d="M20 120 L50 70 L150 70 L180 120 L195 135 L195 160 L180 160 C180 145 160 145 160 160 L40 160 C40 145 20 145 20 160 L5 160 L5 135 Z"/>
                            <circle cx="40" cy="160" r="18" fill="currentColor"/>
                            <circle cx="160" cy="160" r="18" fill="currentColor"/>
                            <path d="M70 20 L95 45 L80 60 L55 35 Z" opacity="0.6"/>
                            <circle cx="150" cy="40" r="22" opacity="0.5"/>
                        '
                    ],
                    'TE' => [
                        'card_bg' => '#f87171',
                        'card_shadow' => 'rgba(239, 68, 68, 0.32)',
                        'hover_shadow' => 'rgba(239, 68, 68, 0.45)',
                        'icon' => 'fa-solid fa-tv',
                        'icon_bg' => '#ffffff',
                        'icon_color' => '#dc2626',
                        'item_icon' => 'fa-solid fa-microchip',
                        'tag' => 'Elektronika',
                        'silhouette_svg' => '
                            <rect x="25" y="40" width="150" height="100" rx="16"/>
                            <circle cx="100" cy="90" r="32" fill="none" stroke="currentColor" stroke-width="10"/>
                            <path d="M10 90 L30 90 M170 90 L190 90 M100 10 L100 30 M100 150 L100 170" stroke="currentColor" stroke-width="8"/>
                            <path d="M60 160 L140 160 L120 180 L80 180 Z" opacity="0.7"/>
                        '
                    ],
                    'TJKT' => [
                        'card_bg' => '#60a5fa',
                        'card_shadow' => 'rgba(37, 99, 235, 0.32)',
                        'hover_shadow' => 'rgba(37, 99, 235, 0.45)',
                        'icon' => 'fa-solid fa-network-wired',
                        'icon_bg' => '#ffffff',
                        'icon_color' => '#1d4ed8',
                        'item_icon' => 'fa-solid fa-server',
                        'tag' => 'Jaringan & IT',
                        'silhouette_svg' => '
                            <rect x="40" y="25" width="120" height="35" rx="8"/>
                            <rect x="40" y="70" width="120" height="35" rx="8"/>
                            <rect x="40" y="115" width="120" height="35" rx="8"/>
                            <circle cx="65" cy="42" r="5" fill="#ffffff"/>
                            <circle cx="65" cy="87" r="5" fill="#ffffff"/>
                            <circle cx="65" cy="132" r="5" fill="#ffffff"/>
                            <path d="M100 150 L100 175 M30 175 L170 175 M50 175 L50 190 M150 175 L150 190" stroke="currentColor" stroke-width="6"/>
                        '
                    ],
                    'TKP' => [
                        'card_bg' => '#fbbf24',
                        'card_shadow' => 'rgba(245, 158, 11, 0.32)',
                        'hover_shadow' => 'rgba(245, 158, 11, 0.45)',
                        'icon' => 'fa-solid fa-compass-drafting',
                        'icon_bg' => '#ffffff',
                        'icon_color' => '#b45309',
                        'item_icon' => 'fa-solid fa-building',
                        'tag' => 'Arsitektur CAD',
                        'silhouette_svg' => '
                            <rect x="25" y="70" width="45" height="110" rx="4"/>
                            <rect x="78" y="30" width="55" height="150" rx="4"/>
                            <rect x="140" y="90" width="40" height="90" rx="4"/>
                            <path d="M100 10 L105 30 L95 30 Z"/>
                            <!-- Compass drafting arm -->
                            <path d="M30 30 L90 80 M150 30 L90 80" stroke="currentColor" stroke-width="8" opacity="0.6"/>
                        '
                    ],
                ];

                if (!isset($smkProgramKeahlians) || $smkProgramKeahlians->isEmpty()) {
                    $smkProgramKeahlians = \App\Models\ProgramKeahlian::with(['konsentrasiKeahlians' => function($q) {
                        $q->where('is_active', true);
                    }])->where('is_active', true)->get();
                }
            @endphp

            @foreach($smkProgramKeahlians as $prog)
                @php
                    $cfg = $configProgram[$prog->kode] ?? [
                        'card_bg' => '#ffffff',
                        'card_shadow' => 'rgba(15, 23, 42, 0.05)',
                        'hover_shadow' => 'rgba(15, 23, 42, 0.12)',
                        'icon' => 'fa-solid fa-gears',
                        'icon_bg' => '#f8fafc',
                        'icon_color' => '#475569',
                        'item_icon' => 'fa-solid fa-check',
                        'tag' => 'Vokasi',
                        'silhouette_svg' => '<circle cx="100" cy="100" r="50"/>'
                    ];
                @endphp

                <div class="vibrant-program-card" 
                     style="background: {{ $cfg['card_bg'] }}; box-shadow: 0 14px 32px -6px {{ $cfg['card_shadow'] }};"
                     onmouseover="this.style.boxShadow='0 22px 45px -6px {{ $cfg['hover_shadow'] }}'"
                     onmouseout="this.style.boxShadow='0 14px 32px -6px {{ $cfg['card_shadow'] }}'">
                    
                    {{-- Siluet Kejuruan Spesifik --}}
                    <svg class="program-silhouette" viewBox="0 0 200 200" fill="currentColor">
                        {!! $cfg['silhouette_svg'] !!}
                    </svg>

                    <div class="program-card-content">
                        {{-- Card Header --}}
                        <div class="program-card-header">
                            <div class="program-icon-box" style="background: {{ $cfg['icon_bg'] }}; color: {{ $cfg['icon_color'] }};">
                                <i class="{{ $cfg['icon'] }}"></i>
                            </div>
                            <div>
                                <h3 class="program-title">{{ $prog->nama }}</h3>
                                <span class="program-badge" style="background: rgba(15, 23, 42, 0.12); color: #0f172a;">
                                    {{ $prog->konsentrasiKeahlians->count() }} Konsentrasi Keahlian
                                </span>
                            </div>
                        </div>

                        {{-- Konsentrasi Keahlian List (Asli dari Database dalam Kartu Putih Bersih) --}}
                        <div class="subprogram-list">
                            @foreach($prog->konsentrasiKeahlians as $kon)
                                <div class="subprogram-item">
                                    <div class="subprogram-name">
                                        <i class="{{ $cfg['item_icon'] }}" style="color: {{ $cfg['icon_color'] }}; font-size: 13px;"></i>
                                        <span>{{ $kon->nama }} ({{ $kon->kode }})</span>
                                    </div>
                                    <div class="subprogram-desc">
                                        {{ $kon->deskripsi ?: 'Program keahlian terstandarisasi industri untuk kesiapan kerja dan wirausaha.' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="program-card-footer">
                        <span><i class="fa-solid fa-circle-check"></i> Praktik DUDI</span>
                        <span style="font-weight: 900;">{{ $cfg['tag'] }} &rarr;</span>
                    </div>
                </div>
            @endforeach

        </div>

    </div>
</section>
