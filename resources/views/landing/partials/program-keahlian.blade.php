{{-- PROGRAM KEAHLIAN — Official Major Colors (Khan Academy Vibrant Style) --}}
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

    .program-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1.5px solid rgba(15, 23, 42, 0.12);
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

        {{-- Syarat 2: 4 Program & 5 Konsentrasi Keahlian dengan Warna Resmi Jurusan --}}
        <div class="program-grid-4" data-aos="fade-up" data-aos-delay="100">
            
            @php
                // Pemetaan Warna Resmi Jurusan SMKS Pembda Nias:
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
                        'color_badge' => 'Warna: Orange'
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
                        'color_badge' => 'Warna: Merah'
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
                        'color_badge' => 'Warna: Biru'
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
                        'color_badge' => 'Warna: Kuning'
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
                        'color_badge' => 'Vokasi'
                    ];
                @endphp

                <div class="vibrant-program-card" 
                     style="background: {{ $cfg['card_bg'] }}; box-shadow: 0 14px 32px -6px {{ $cfg['card_shadow'] }};"
                     onmouseover="this.style.boxShadow='0 22px 45px -6px {{ $cfg['hover_shadow'] }}'"
                     onmouseout="this.style.boxShadow='0 14px 32px -6px {{ $cfg['card_shadow'] }}'">
                    <div>
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
