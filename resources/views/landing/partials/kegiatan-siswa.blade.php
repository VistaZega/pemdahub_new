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
        grid-template-columns: repeat(4, 1fr);
        grid-auto-rows: minmax(220px, auto);
        gap: 24px;
        max-width: 1400px;
        margin: 0 auto;
        grid-auto-flow: dense;
    }

    .bento-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        border: 1.5px solid rgba(226, 232, 240, 0.9);
        border-radius: 32px;
        padding: 28px;
        text-align: left;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .bento-card:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        transform: scale(1.02);
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.15);
        z-index: 10;
    }

    .bento-col-2 { grid-column: span 2; }
    .bento-row-2 { grid-row: span 2; }
    
    .bento-watermark {
        position: absolute;
        bottom: -15px;
        right: -15px;
        width: 65%;
        max-height: 85%;
        object-fit: contain;
        opacity: 0.07;
        z-index: 0;
        pointer-events: none;
        mix-blend-mode: multiply;
        transition: all 0.5s ease;
    }

    .bento-card:hover .bento-watermark {
        transform: scale(1.1) rotate(-5deg);
        opacity: 0.12;
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
        width: 64px;
        height: 64px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 20px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }

    .bento-row-2 .bento-icon-box {
        width: 80px;
        height: 80px;
        font-size: 36px;
        border-radius: 24px;
    }

    .bento-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 12px 0;
        letter-spacing: -0.01em;
        line-height: 1.3;
    }

    .bento-col-2 .bento-title, .bento-row-2 .bento-title {
        font-size: 24px;
    }

    .bento-meta {
        font-size: 14px;
        color: #475569;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: auto;
    }

    @media (max-width: 1024px) {
        .bento-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .bento-grid { grid-template-columns: repeat(2, 1fr); }
        .bento-col-2 { grid-column: span 2; }
    }
    @media (max-width: 480px) {
        .bento-grid { grid-template-columns: 1fr; }
        .bento-col-2 { grid-column: span 1; }
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
                @endphp
                
                <div class="bento-card {{ $bentoClass }}">
                    <img class="bento-watermark" src="{{ asset('images/ekskul/' . $ekskul->category . '_bg.jpg') }}" alt="Background" onerror="this.style.display='none'">
                    
                    <div class="bento-content">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                            <div class="bento-icon-box" style="background:{{ $bgColor }}; color:{{ $textColor }};">
                                @if($isFa) <i class="{{ $iconStr }}"></i> @else <span>{{ $iconStr }}</span> @endif
                            </div>
                            
                            <div style="font-size: 11.5px; padding: 6px 14px; border-radius: 100px; background: {{ $ekskul->isFoundationLevel() ? '#f5f3ff' : '#f8fafc' }}; color: {{ $ekskul->isFoundationLevel() ? '#7c3aed' : '#334155' }}; font-weight: 700; border: 1px solid {{ $ekskul->isFoundationLevel() ? '#ddd6fe' : '#e2e8f0' }}; white-space: nowrap;">
                                {{ $ekskul->isFoundationLevel() ? 'Lintas Yayasan' : ($ekskul->school->name ?? 'Unit Sekolah') }}
                            </div>
                        </div>
                        
                        <div style="margin-top: auto;">
                            <h3 class="bento-title">{{ $ekskul->name }}</h3>
                            <div class="bento-meta">
                                <i class="fa-solid fa-users" style="color: #94a3b8;"></i> 
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
