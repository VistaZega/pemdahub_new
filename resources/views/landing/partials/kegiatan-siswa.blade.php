{{-- EKSTRAKURIKULER & KEGIATAN SISWA — Real Photo Backdrop with 50% Opacity --}}
<style>
    .ekskul-section {
        position: relative;
        overflow: hidden;
        background: #ffffff;
        padding: 90px 0 100px 0;
        border-bottom: 1px solid var(--border);
    }

    /* Foto Asli Marching Band / Kegiatan Siswa dengan Opacity 50% */
    .ekskul-photo-backdrop {
        position: absolute;
        inset: 0;
        background-image: url('{{ asset('images/marching-band-pembda.jpg') }}');
        background-size: cover;
        background-position: center 60%;
        background-repeat: no-repeat;
        opacity: 0.50;
        pointer-events: none;
        z-index: 0;
    }

    .ekskul-photo-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.75) 0%, rgba(255, 255, 255, 0.45) 50%, rgba(255, 255, 255, 0.8) 100%);
        pointer-events: none;
        z-index: 0;
    }

    .ekskul-content-wrap {
        position: relative;
        z-index: 1;
    }

    .ekskul-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        border: 1.5px solid rgba(226, 232, 240, 0.9);
        border-radius: 24px;
        padding: 32px 18px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .ekskul-card:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        transform: translateY(-6px);
        box-shadow: 0 18px 40px -8px rgba(15, 23, 42, 0.15);
    }

    .ekskul-icon-box {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 18px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .ekskul-card:hover .ekskul-icon-box {
        transform: scale(1.1) rotate(4deg);
    }

    .ekskul-title {
        font-size: 15.5px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .ekskul-watermark {
        position: absolute;
        bottom: -10px;
        right: -10px;
        width: 140px;
        height: 140px;
        object-fit: cover;
        opacity: 0.06;
        z-index: 0;
        pointer-events: none;
        mix-blend-mode: multiply;
        transition: all 0.4s ease;
    }

    .ekskul-card:hover .ekskul-watermark {
        transform: scale(1.1);
        opacity: 0.12;
    }

    .ekskul-card > div:not(.ekskul-watermark), .ekskul-card > h3 {
        position: relative;
        z-index: 1;
    }

    .bento-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr); /* 7 columns for maximum density */
        grid-auto-rows: minmax(56px, auto); /* 20% smaller */
        gap: 10px; /* Tighter gap */
        max-width: 1400px;
        margin: 0 auto;
        grid-auto-flow: dense;
    }

    .bento-card {
        backdrop-filter: blur(12px);
        border-radius: 12px; 
        padding: 8px; 
        text-align: left;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .bento-card:hover {
        transform: translate(-2px, -2px) scale(1.02);
        box-shadow: 6px 6px 0px rgba(0,0,0,0.15) !important;
        z-index: 10;
        filter: brightness(0.96);
    }

    .bento-col-2 { grid-column: span 2; }
    .bento-row-2 { grid-row: span 2; }
    
    .bento-watermark {
        position: absolute;
        bottom: -5px;
        right: -5px;
        width: 60%;
        max-height: 80%;
        object-fit: contain;
        opacity: 0.08;
        z-index: 0;
        pointer-events: none;
        mix-blend-mode: multiply;
        transition: all 0.4s ease;
    }

    .bento-card:hover .bento-watermark {
        transform: scale(1.15) rotate(-4deg);
        opacity: 0.15;
    }

    .bento-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
    }

    .bento-icon-box {
        width: 24px; /* Nano icon box */
        height: 24px;
        border-radius: 6px;
        background: rgba(255,255,255,0.7); /* Translucent white */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        margin-bottom: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        flex-shrink: 0;
    }

    .bento-row-2 .bento-icon-box {
        width: 32px;
        height: 32px;
        font-size: 14px;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .bento-title {
        font-size: 10px; /* Nano font */
        font-weight: 800;
        /* Color set inline */
        margin: 0 0 2px 0;
        letter-spacing: -0.01em;
        line-height: 1.1;
    }

    .bento-col-2 .bento-title, .bento-row-2 .bento-title {
        font-size: 12px;
    }

    .bento-meta {
        font-size: 9px; /* Nano font */
        /* Color set inline */
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 3px;
        margin-top: auto;
        opacity: 0.85;
    }
    
    .bento-badge {
        font-size: 7.5px;
        padding: 2px 5px;
        border-radius: 20px;
        font-weight: 800;
        white-space: nowrap;
        margin-left: 4px;
        background: rgba(255,255,255,0.6) !important;
        border: none !important;
    }

    @media (max-width: 1200px) {
        .bento-grid { grid-template-columns: repeat(6, 1fr); }
    }
    @media (max-width: 1024px) {
        .bento-grid { grid-template-columns: repeat(5, 1fr); }
    }
    @media (max-width: 768px) {
        .bento-grid { grid-template-columns: repeat(4, 1fr); }
        .bento-col-2 { grid-column: span 2; }
    }
    @media (max-width: 480px) {
        .bento-grid { grid-template-columns: repeat(2, 1fr); }
        .bento-col-2 { grid-column: span 2; }
        .bento-row-2 { grid-row: span 1; }
    }
</style>

<section id="kegiatan" class="ekskul-section">
    <div class="ekskul-photo-backdrop"></div>
    <div class="ekskul-photo-overlay"></div>

    <div class="fw ekskul-content-wrap">
        <div style="text-align:center; margin-bottom:56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center; margin-bottom: 14px;">
                <div class="section-label-dot" style="background:#f59e0b;"></div>
                <span class="section-label-text" style="color:#4f46e5;">Kegiatan Siswa</span>
            </div>
            <h2 class="h1" style="margin-bottom:14px; font-size:clamp(30px, 4vw, 44px); font-weight:900; color:#0f172a; letter-spacing:-0.025em;">
                Ekstrakurikuler &amp; <span style="background:linear-gradient(135deg, #4f46e5, #7c3aed); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Minat Bakat</span>
            </h2>
            <p class="body-lg" style="max-width:680px; margin:0 auto; color:#334155; font-weight:600;">
                Mengembangkan potensi, kepemimpinan, kreativitas seni, dan kebugaran jasmani siswa melalui ragam kegiatan ekstrakurikuler unggulan.
            </p>
        </div>

        @if(count($extracurriculars) > 0)
        @php
            // Urutkan berdasarkan anggota terbanyak agar yang paling populer dapat kotak besar
            $sortedEkskul = collect($extracurriculars)->sortByDesc('active_members_count')->values();
            
            // Pola Bento Box Apple-style berulang
            $bentoPatterns = [
                'bento-col-2 bento-row-2', // Sangat besar (Juara 1)
                'bento-col-1 bento-row-2', // Tinggi vertikal
                'bento-col-1 bento-row-1', // Normal
                'bento-col-2 bento-row-1', // Lebar horizontal
                'bento-col-1 bento-row-1', // Normal
                'bento-col-1 bento-row-1', // Normal
                'bento-col-2 bento-row-1', // Lebar horizontal
                'bento-col-1 bento-row-2', // Tinggi vertikal
            ];
        @endphp
        
        <div class="bento-grid" data-aos="fade-up" data-aos-delay="100">
            @foreach($sortedEkskul as $index => $ekskul)
                @php
                    $bentoClass = $bentoPatterns[$index % count($bentoPatterns)];
                    
                    $iconStr = $ekskul->display_icon;
                    $isFa = str_contains($iconStr, 'fa-');
                    $bgColor = '#eff6ff'; $textColor = '#2563eb';
                    switch($ekskul->category) {
                        case 'marching_band': $bgColor = '#fef2f2'; $textColor = '#ef4444'; break;
                        case 'pramuka': $bgColor = '#fff7ed'; $textColor = '#ea580c'; break;
                        case 'paskibraka': $bgColor = '#fdf4ff'; $textColor = '#c026d3'; break;
                        case 'seni_budaya': $bgColor = '#fef3c7'; $textColor = '#d97706'; break;
                        case 'olahraga': $bgColor = '#ecfdf5'; $textColor = '#059669'; break;
                        case 'sains_it': $bgColor = '#ecfeff'; $textColor = '#0891b2'; break;
                    }
                    
                    $slug = \Illuminate\Support\Str::slug($ekskul->name);
                    $bgImgSrc = asset('images/ekskul/' . $ekskul->category . '_bg.jpg'); // Default
                    
                    if (str_contains($slug, 'futsal')) $bgImgSrc = asset('images/ekskul/futsal_bg.jpg');
                    elseif (str_contains($slug, 'tenis-meja')) $bgImgSrc = asset('images/ekskul/tenis_meja_bg.jpg');
                    elseif (str_contains($slug, 'vocal') || str_contains($slug, 'vokal')) $bgImgSrc = asset('images/ekskul/olah_vocal_bg.jpg');
                    elseif (str_contains($slug, 'english')) $bgImgSrc = asset('images/ekskul/english_club_bg.jpg');
                    elseif (str_contains($slug, 'cerdas-cermat')) $bgImgSrc = asset('images/ekskul/cerdas_cermat_bg.jpg');
                    elseif (str_contains($slug, 'sains')) $bgImgSrc = asset('images/ekskul/sains_bg.jpg');
                    elseif (str_contains($slug, 'marching-band')) $bgImgSrc = asset('images/ekskul/marching_band_bg.jpg');
                @endphp
                
                <div class="bento-card {{ $bentoClass }}" style="background: {{ $bgColor }}; border: 1.5px solid #000000; box-shadow: 4px 4px 0px rgba(0,0,0,0.1);">
                    <img class="bento-watermark" src="{{ $bgImgSrc }}" alt="Background" onerror="this.style.display='none'">
                    
                    <div class="bento-content">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                            <div class="bento-icon-box" style="color:{{ $textColor }}; border: 1px solid rgba(0,0,0,0.1);">
                                @if($isFa) <i class="{{ $iconStr }}"></i> @else <span>{{ $iconStr }}</span> @endif
                            </div>
                            
                            <div class="bento-badge" style="color: {{ $textColor }}; border: 1px solid {{ $textColor }} !important;">
                                {{ $ekskul->isFoundationLevel() ? 'Lintas Yayasan' : ($ekskul->school->name ?? 'Unit Sekolah') }}
                            </div>
                        </div>
                        
                        <div style="margin-top: auto;">
                            <h3 class="bento-title" style="color: {{ $textColor }};">{{ $ekskul->name }}</h3>
                            <div class="bento-meta" style="color: {{ $textColor }};">
                                <i class="fa-solid fa-users"></i> 
                                <span>{{ $ekskul->active_members_count ?? 0 }} Anggota Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center; padding: 40px; color: #64748b; background: rgba(255,255,255,0.9); border-radius: 20px; max-width: 600px; margin: 0 auto;">
            <i class="fa-solid fa-folder-open" style="font-size: 32px; color: #cbd5e1; margin-bottom: 12px;"></i>
            <p style="font-weight: 600; font-size: 15px;">Belum ada data kegiatan siswa yang aktif.</p>
        </div>
        @endif
    </div>
</section>
