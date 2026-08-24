{{-- PROGRAM KEAHLIAN — Modern Dynamic Database-Driven Bento Design --}}
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

    .program-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 24px;
        padding: 28px 24px;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.05);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .program-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.12);
        border-color: #cbd5e1;
    }

    .program-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
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
        border-radius: 6px;
        display: inline-block;
    }

    .subprogram-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }

    .subprogram-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        transition: all 0.2s ease;
    }

    .subprogram-item:hover {
        background: #ffffff;
        border-color: #93c5fd;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.06);
    }

    .subprogram-name {
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .subprogram-desc {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        line-height: 1.5;
    }

    .program-card-footer {
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 800;
    }

    /* Single Line Title Utility */
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
                <div class="section-label-dot" style="background: #d97706;"></div>
                <span class="section-label-text" style="color: #b45309;">Pendidikan Vokasi Terpadu</span>
            </div>
            
            {{-- Syarat 1: Judul harus dalam SATU BARIS --}}
            <h2 class="single-line-title">
                Konsentrasi Keahlian <span style="background: linear-gradient(135deg, #d97706, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">SMKS Pembda Nias</span>
            </h2>
            
            <p class="body-lg" style="color: #475569; max-width: 720px; margin: 0 auto;">
                Mencetak lulusan terampil, bersertifikasi keahlian, siap kerja langsung di dunia usaha &amp; dunia industri (DUDI), serta berjiwa wirausaha tangguh.
            </p>
        </div>

        {{-- Syarat 2: 4 Program & 5 Konsentrasi Keahlian Asli dari Database --}}
        <div class="program-grid-4" data-aos="fade-up" data-aos-delay="100">
            
            @php
                // Data mapping konfigurasi tema per program keahlian
                $configProgram = [
                    'TO' => [
                        'icon' => 'fa-solid fa-car-side',
                        'icon_bg' => '#fff1f2',
                        'icon_color' => '#e11d48',
                        'badge_bg' => '#ffe4e6',
                        'badge_color' => '#be123c',
                        'item_icon' => 'fa-solid fa-wrench',
                        'tag' => 'Otomotif & Mesin'
                    ],
                    'TE' => [
                        'icon' => 'fa-solid fa-tv',
                        'icon_bg' => '#f5f3ff',
                        'icon_color' => '#7c3aed',
                        'badge_bg' => '#ede9fe',
                        'badge_color' => '#6d28d9',
                        'item_icon' => 'fa-solid fa-microchip',
                        'tag' => 'Elektronika'
                    ],
                    'TKP' => [
                        'icon' => 'fa-solid fa-compass-drafting',
                        'icon_bg' => '#fffbeb',
                        'icon_color' => '#d97706',
                        'badge_bg' => '#fef3c7',
                        'badge_color' => '#b45309',
                        'item_icon' => 'fa-solid fa-building',
                        'tag' => 'Arsitektur CAD'
                    ],
                    'TJKT' => [
                        'icon' => 'fa-solid fa-network-wired',
                        'icon_bg' => '#eff6ff',
                        'icon_color' => '#2563eb',
                        'badge_bg' => '#dbeafe',
                        'badge_color' => '#1d4ed8',
                        'item_icon' => 'fa-solid fa-server',
                        'tag' => 'Jaringan & IT'
                    ],
                ];

                // Fallback jika belum di-compact dari controller
                if (!isset($smkProgramKeahlians) || $smkProgramKeahlians->isEmpty()) {
                    $smkProgramKeahlians = \App\Models\ProgramKeahlian::with(['konsentrasiKeahlians' => function($q) {
                        $q->where('is_active', true);
                    }])->where('is_active', true)->get();
                }
            @endphp

            @foreach($smkProgramKeahlians as $prog)
                @php
                    $cfg = $configProgram[$prog->kode] ?? [
                        'icon' => 'fa-solid fa-gears',
                        'icon_bg' => '#f8fafc',
                        'icon_color' => '#475569',
                        'badge_bg' => '#e2e8f0',
                        'badge_color' => '#1e293b',
                        'item_icon' => 'fa-solid fa-check',
                        'tag' => 'Vokasi'
                    ];
                @endphp

                <div class="program-card">
                    <div>
                        {{-- Card Header --}}
                        <div class="program-card-header">
                            <div class="program-icon-box" style="background: {{ $cfg['icon_bg'] }}; color: {{ $cfg['icon_color'] }};">
                                <i class="{{ $cfg['icon'] }}"></i>
                            </div>
                            <div>
                                <h3 class="program-title">{{ $prog->nama }}</h3>
                                <span class="program-badge" style="background: {{ $cfg['badge_bg'] }}; color: {{ $cfg['badge_color'] }};">
                                    {{ $prog->konsentrasiKeahlians->count() }} Konsentrasi Keahlian
                                </span>
                            </div>
                        </div>

                        {{-- Konsentrasi Keahlian List (Asli dari Database) --}}
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
                        <span style="color: #059669;"><i class="fa-solid fa-circle-check"></i> Praktik DUDI</span>
                        <span style="color: {{ $cfg['icon_color'] }};">{{ $cfg['tag'] }} &rarr;</span>
                    </div>
                </div>
            @endforeach

        </div>

    </div>
</section>
