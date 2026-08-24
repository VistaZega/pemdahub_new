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

        {{-- 6 Cards Grid --}}
        <div style="display:grid; grid-template-columns: repeat(6, 1fr); gap: 20px; max-width: 1680px; margin: 0 auto;" data-aos="fade-up" data-aos-delay="100" class="kegiatan-grid">
            
            {{-- Renang --}}
            <div class="ekskul-card">
                <div class="ekskul-icon-box" style="background:#eff6ff; color:#2563eb;">
                    <i class="fa-solid fa-person-swimming"></i>
                </div>
                <h3 class="ekskul-title">Renang</h3>
            </div>

            {{-- Futsal --}}
            <div class="ekskul-card">
                <div class="ekskul-icon-box" style="background:#ecfdf5; color:#059669;">
                    <i class="fa-solid fa-futbol"></i>
                </div>
                <h3 class="ekskul-title">Futsal</h3>
            </div>

            {{-- Paskibraka --}}
            <div class="ekskul-card">
                <div class="ekskul-icon-box" style="background:#fff1f2; color:#e11d48;">
                    <i class="fa-solid fa-flag"></i>
                </div>
                <h3 class="ekskul-title">Paskibraka</h3>
            </div>

            {{-- Cerdas Cermat --}}
            <div class="ekskul-card">
                <div class="ekskul-icon-box" style="background:#f5f3ff; color:#7c3aed;">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <h3 class="ekskul-title">Cerdas Cermat</h3>
            </div>

            {{-- Seni & Budaya --}}
            <div class="ekskul-card">
                <div class="ekskul-icon-box" style="background:#fef3c7; color:#d97706;">
                    <i class="fa-solid fa-palette"></i>
                </div>
                <h3 class="ekskul-title">Seni &amp; Budaya</h3>
            </div>

            {{-- IT Club --}}
            <div class="ekskul-card">
                <div class="ekskul-icon-box" style="background:#ecfeff; color:#0891b2;">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <h3 class="ekskul-title">IT Club</h3>
            </div>
            
        </div>
    </div>
</section>
