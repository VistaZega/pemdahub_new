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
        bottom: -20px;
        right: -20px;
        font-size: 140px;
        opacity: 0.04;
        z-index: 0;
        pointer-events: none;
        transform: rotate(-15deg);
        transition: all 0.4s ease;
    }

    .ekskul-card:hover .ekskul-watermark {
        transform: rotate(0deg) scale(1.15);
        opacity: 0.08;
    }

    .ekskul-card > div:not(.ekskul-watermark), .ekskul-card > h3 {
        position: relative;
        z-index: 1;
    }

    /* === Infinite Marquee Styles === */
    .ekskul-marquee-container {
        width: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 24px;
        position: relative;
        padding: 10px 0;
    }

    /* Gradient masks to fade edges */
    .ekskul-marquee-container::before, .ekskul-marquee-container::after {
        content: "";
        position: absolute;
        top: 0; bottom: 0;
        width: 120px;
        z-index: 2;
        pointer-events: none;
    }
    .ekskul-marquee-container::before {
        left: 0;
        background: linear-gradient(to right, rgba(255,255,255,1) 0%, rgba(255,255,255,0.7) 40%, rgba(255,255,255,0) 100%);
    }
    .ekskul-marquee-container::after {
        right: 0;
        background: linear-gradient(to left, rgba(255,255,255,1) 0%, rgba(255,255,255,0.7) 40%, rgba(255,255,255,0) 100%);
    }

    .ekskul-marquee-track {
        display: flex;
        width: max-content;
    }

    .ekskul-marquee-track.scroll-left {
        animation: marqueeLeft 60s linear infinite;
    }

    .ekskul-marquee-track.scroll-right {
        animation: marqueeRight 60s linear infinite;
    }

    .ekskul-marquee-track:hover {
        animation-play-state: paused;
    }

    @keyframes marqueeLeft {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    @keyframes marqueeRight {
        0% { transform: translateX(-50%); }
        100% { transform: translateX(0); }
    }

    /* Specific card width for marquee so it doesn't shrink */
    .ekskul-card-marquee {
        width: 250px;
        flex-shrink: 0;
        margin-right: 20px;
    }
    
    @media (max-width: 768px) {
        .ekskul-marquee-container::before, .ekskul-marquee-container::after {
            width: 40px;
        }
        .ekskul-card-marquee {
            width: 220px;
        }
    }
</style>

<section id="kegiatan" class="ekskul-section">
    {{-- Foto Asli Marching Band Opacity 50% --}}
    <div class="ekskul-photo-backdrop"></div>
    <div class="ekskul-photo-overlay"></div>

    <div class="fw ekskul-content-wrap">
        {{-- Section Header --}}
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

        {{-- Extracurriculars Infinite Marquee --}}
        @if(count($extracurriculars) > 0)
        @php
            // Split into two rows
            $half = ceil(count($extracurriculars) / 2);
            $row1 = $extracurriculars->take($half);
            $row2 = $extracurriculars->skip($half);
        @endphp
        <div class="ekskul-marquee-container" data-aos="fade-up" data-aos-delay="100">
            
            {{-- ROW 1: Scrolls Left --}}
            <div class="ekskul-marquee-track scroll-left">
                @for($i = 0; $i < 2; $i++)
                    @foreach($row1 as $ekskul)
                        <div class="ekskul-card ekskul-card-marquee">
                            @php
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
                            <div class="ekskul-watermark" style="color:{{ $textColor }};">
                                @if($isFa) <i class="{{ $iconStr }}"></i> @else <span>{{ $iconStr }}</span> @endif
                            </div>
                            <div class="ekskul-icon-box" style="background:{{ $bgColor }}; color:{{ $textColor }};">
                                @if($isFa) <i class="{{ $iconStr }}"></i> @else <span>{{ $iconStr }}</span> @endif
                            </div>
                            <h3 class="ekskul-title" style="margin-bottom: 8px;">{{ $ekskul->name }}</h3>
                            <div style="font-size: 13.5px; color: #475569; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fa-solid fa-users" style="color: #94a3b8;"></i> {{ $ekskul->active_members_count ?? 0 }} Anggota
                            </div>
                            <div style="font-size: 11.5px; padding: 5px 12px; border-radius: 100px; background: {{ $ekskul->isFoundationLevel() ? '#f5f3ff' : '#f8fafc' }}; color: {{ $ekskul->isFoundationLevel() ? '#7c3aed' : '#334155' }}; font-weight: 700; border: 1px solid {{ $ekskul->isFoundationLevel() ? '#ddd6fe' : '#e2e8f0' }};">
                                {{ $ekskul->isFoundationLevel() ? 'Lintas Yayasan' : ($ekskul->school->name ?? 'Unit Sekolah') }}
                            </div>
                        </div>
                    @endforeach
                @endfor
            </div>

            {{-- ROW 2: Scrolls Right --}}
            @if($row2->count() > 0)
            <div class="ekskul-marquee-track scroll-right">
                @for($i = 0; $i < 2; $i++)
                    @foreach($row2 as $ekskul)
                        <div class="ekskul-card ekskul-card-marquee">
                            @php
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
                            <div class="ekskul-watermark" style="color:{{ $textColor }};">
                                @if($isFa) <i class="{{ $iconStr }}"></i> @else <span>{{ $iconStr }}</span> @endif
                            </div>
                            <div class="ekskul-icon-box" style="background:{{ $bgColor }}; color:{{ $textColor }};">
                                @if($isFa) <i class="{{ $iconStr }}"></i> @else <span>{{ $iconStr }}</span> @endif
                            </div>
                            <h3 class="ekskul-title" style="margin-bottom: 8px;">{{ $ekskul->name }}</h3>
                            <div style="font-size: 13.5px; color: #475569; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fa-solid fa-users" style="color: #94a3b8;"></i> {{ $ekskul->active_members_count ?? 0 }} Anggota
                            </div>
                            <div style="font-size: 11.5px; padding: 5px 12px; border-radius: 100px; background: {{ $ekskul->isFoundationLevel() ? '#f5f3ff' : '#f8fafc' }}; color: {{ $ekskul->isFoundationLevel() ? '#7c3aed' : '#334155' }}; font-weight: 700; border: 1px solid {{ $ekskul->isFoundationLevel() ? '#ddd6fe' : '#e2e8f0' }};">
                                {{ $ekskul->isFoundationLevel() ? 'Lintas Yayasan' : ($ekskul->school->name ?? 'Unit Sekolah') }}
                            </div>
                        </div>
                    @endforeach
                @endfor
            </div>
            @endif

        </div>
        @else
        <div style="text-align:center; padding: 40px; color: #64748b; background: rgba(255,255,255,0.9); border-radius: 20px; max-width: 600px; margin: 0 auto;">
            <i class="fa-solid fa-folder-open" style="font-size: 32px; color: #cbd5e1; margin-bottom: 12px;"></i>
            <p style="font-weight: 600; font-size: 15px;">Belum ada data kegiatan siswa yang aktif.</p>
        </div>
        @endif
    </div>
</section>
