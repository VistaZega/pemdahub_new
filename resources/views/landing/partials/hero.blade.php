{{-- HERO SECTION — Konsep 3: Clean Minimalist Premium (Gaya Apple Education / Notion / Linear Light) --}}
<style>
    .apple-hero {
        position: relative;
        background: #ffffff;
        padding-top: 135px;
        padding-bottom: 95px;
        overflow: hidden;
    }

    /* Ambient Soft Radial Light */
    .apple-ambient-glow {
        position: absolute;
        top: -20%;
        left: 50%;
        transform: translateX(-50%);
        width: 1100px;
        height: 550px;
        background: radial-gradient(ellipse at top, rgba(37, 99, 235, 0.07) 0%, rgba(245, 158, 11, 0.03) 40%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }

    /* Center-Aligned Minimalist Header */
    .apple-hero-center {
        text-align: center;
        max-width: 900px;
        margin: 0 auto 52px auto;
        position: relative;
        z-index: 1;
    }

    .apple-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 18px;
        border-radius: 100px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 24px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }

    .apple-hero-headline {
        font-size: clamp(38px, 5.2vw, 64px);
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: -0.035em;
        color: #0f172a;
        margin-bottom: 22px;
    }

    .apple-headline-gradient {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .apple-hero-subtext {
        font-size: 19px;
        line-height: 1.7;
        color: #475569;
        font-weight: 400;
        max-width: 740px;
        margin: 0 auto 36px auto;
    }

    .apple-cta-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .apple-btn-dark {
        background: #0f172a;
        color: #ffffff !important;
        font-size: 15.5px;
        font-weight: 800;
        padding: 15px 32px;
        border-radius: 100px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.25);
    }

    .apple-btn-dark:hover {
        background: #1e3a8a;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px -4px rgba(30, 58, 138, 0.35);
    }

    .apple-btn-light {
        background: #ffffff;
        color: #0f172a !important;
        font-size: 15.5px;
        font-weight: 700;
        padding: 15px 30px;
        border-radius: 100px;
        text-decoration: none;
        border: 1.5px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s ease;
    }

    .apple-btn-light:hover {
        border-color: #2563eb;
        color: #2563eb !important;
        background: #f8fafc;
        transform: translateY(-2px);
    }

    /* Floating Apple-Style Studio Preview Mockup */
    .apple-mockup-frame {
        max-width: 1080px;
        margin: 0 auto 64px auto;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 28px;
        padding: 28px;
        box-shadow: 0 30px 80px -20px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.03);
        position: relative;
        z-index: 1;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .apple-mockup-frame:hover {
        transform: translateY(-4px);
        box-shadow: 0 40px 100px -20px rgba(15, 23, 42, 0.18);
        border-color: #cbd5e1;
    }

    .apple-frame-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 24px;
    }

    .apple-frame-dots {
        display: flex;
        gap: 8px;
    }

    .apple-frame-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #e2e8f0;
    }

    .apple-frame-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        color: #059669;
        background: #ecfdf5;
        padding: 4px 12px;
        border-radius: 100px;
        border: 1px solid #86efac;
    }

    /* Grid Inside Mockup Frame */
    .apple-preview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 860px) {
        .apple-preview-grid {
            grid-template-columns: 1fr;
        }
    }

    .apple-preview-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
    }

    .apple-preview-card:hover {
        background: #ffffff;
        border-color: #93c5fd;
        box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.08);
        transform: translateY(-2px);
    }

    .apple-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 16px;
    }

    .apple-card-title {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .apple-card-sub {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .apple-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 14px;
        border-top: 1px solid #e2e8f0;
        font-size: 12px;
        font-weight: 800;
        color: #2563eb;
    }

    /* 3 Clean Pillars at Bottom */
    .apple-pillars-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 36px;
        max-width: 1080px;
        margin: 0 auto;
        padding-top: 36px;
        border-top: 1px solid #f1f5f9;
    }

    @media (max-width: 860px) {
        .apple-pillars-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }

    .apple-pillar-item {
        display: flex;
        flex-direction: column;
    }

    .apple-pillar-num {
        font-size: 13px;
        font-weight: 900;
        color: #2563eb;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 8px;
    }

    .apple-pillar-heading {
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        letter-spacing: -0.01em;
    }

    .apple-pillar-desc {
        font-size: 14.5px;
        color: #64748b;
        line-height: 1.65;
    }
</style>

<section id="beranda" class="apple-hero">
    <div class="apple-ambient-glow"></div>

    <div class="fw">
        
        {{-- Center Minimalist Headline & Description --}}
        <div class="apple-hero-center" data-aos="fade-up">
            
            <div class="apple-pill-badge">
                <span>Perguruan PEMBDA Nias</span>
                <span style="color:#94a3b8;">•</span>
                <span style="color:#2563eb;">Sejak 1970</span>
            </div>

            <h1 class="apple-hero-headline">
                Pendidikan Terpadu. <br>
                <span class="apple-headline-gradient">Standar Masa Depan.</span>
            </h1>

            <p class="apple-hero-subtext">
                Satu ekosistem pintar yang dirancang untuk kesempurnaan akademik, kedisiplinan berkarakter, dan transparansi tata kelola 3 unit sekolah unggulan: SMA, SMP, dan SMK.
            </p>

            <div class="apple-cta-row">
                @auth
                    <a href="{{ route('dashboard') }}" class="apple-btn-dark">
                        <i class="fa-solid fa-gauge-high"></i> Buka Dashboard Sistem
                    </a>
                @else
                    <a href="{{ route('login') }}" class="apple-btn-dark">
                        <span>Masuk ke Portal Sistem</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endauth

                <a href="#sekolah" class="apple-btn-light">
                    <span>Jelajahi 3 Unit Sekolah</span>
                </a>
            </div>

        </div>

        {{-- Floating Studio Mockup Frame --}}
        <div class="apple-mockup-frame" data-aos="fade-up" data-aos-delay="100">
            
            <div class="apple-frame-topbar">
                <div class="apple-frame-dots">
                    <span class="apple-frame-dot" style="background:#ef4444;"></span>
                    <span class="apple-frame-dot" style="background:#f59e0b;"></span>
                    <span class="apple-frame-dot" style="background:#10b981;"></span>
                </div>
                <div style="font-size:12px; font-weight:800; color:#0f172a; letter-spacing:0.02em;">
                    PEMBDAHUB DIGITAL ECOSYSTEM • 2026/2027
                </div>
                <div class="apple-frame-badge">
                    <span style="width:6px; height:6px; background:#10b981; border-radius:50%;"></span>
                    <span>ONLINE &amp; SYNCHRONIZED</span>
                </div>
            </div>

            <div class="apple-preview-grid">
                
                {{-- Card 1: 3 Unit Sekolah --}}
                <div class="apple-preview-card">
                    <div>
                        <div class="apple-card-icon" style="background:#eff6ff; color:#2563eb;">
                            <i class="fa-solid fa-school"></i>
                        </div>
                        <h3 class="apple-card-title">3 Unit Sekolah Unggulan</h3>
                        <p class="apple-card-sub">Integrasi pendidikan tingkat SMA, SMP, dan SMK dalam satu manajemen terpusat yang solid.</p>
                    </div>
                    <div class="apple-card-footer">
                        <span>SMAS 1 • SMPS 2 • SMKS</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </div>

                {{-- Card 2: Presensi Biometrik RFID --}}
                <div class="apple-preview-card">
                    <div>
                        <div class="apple-card-icon" style="background:#ecfdf5; color:#059669;">
                            <i class="fa-solid fa-id-card-clip"></i>
                        </div>
                        <h3 class="apple-card-title">Presensi RFID &amp; Notifikasi</h3>
                        <p class="apple-card-sub">Pencatatan kehadiran otomatis per jam pelajaran dengan monitoring langsung untuk orang tua.</p>
                    </div>
                    <div class="apple-card-footer" style="color:#059669;">
                        <span>Tap-to-Cloud Real-time</span>
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>

                {{-- Card 3: LMS & CBT Cerdas --}}
                <div class="apple-preview-card">
                    <div>
                        <div class="apple-card-icon" style="background:#fef3c7; color:#d97706;">
                            <i class="fa-solid fa-laptop-code"></i>
                        </div>
                        <h3 class="apple-card-title">LMS &amp; Ujian CBT Terpadu</h3>
                        <p class="apple-card-sub">Bank soal digital, pelaksanaan ujian aman, dan pengolahan rapor Kurikulum Merdeka otomatis.</p>
                    </div>
                    <div class="apple-card-footer" style="color:#d97706;">
                        <span>Bank Soal &amp; Rapor Digital</span>
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                </div>

            </div>

        </div>

        {{-- 3 Clean Pillars at Bottom --}}
        <div class="apple-pillars-grid" data-aos="fade-up" data-aos-delay="150">
            <div class="apple-pillar-item">
                <div class="apple-pillar-num">01 / AKADEMIK &amp; KARAKTER</div>
                <h4 class="apple-pillar-heading">Keunggulan Berkelanjutan</h4>
                <p class="apple-pillar-desc">Memadukan kurikulum nasional modern, pendalaman sains, dan bimbingan sukses lolos perguruan tinggi negeri.</p>
            </div>
            <div class="apple-pillar-item">
                <div class="apple-pillar-num">02 / VOKASI &amp; DUNIA KERJA</div>
                <h4 class="apple-pillar-heading">5 Konsentrasi Keahlian</h4>
                <p class="apple-pillar-desc">Pendidikan kejuruan terakreditasi dengan laboratorium standar industri serta kemitraan magang PKL teruji.</p>
            </div>
            <div class="apple-pillar-item">
                <div class="apple-pillar-num">03 / TATA KELOLA CERDAS</div>
                <h4 class="apple-pillar-heading">Transparansi Digital</h4>
                <p class="apple-pillar-desc">Pelaporan real-time, absensi terverifikasi, dan integrasi penuh untuk 1.691+ siswa dan 91 tenaga pendidik.</p>
            </div>
        </div>

    </div>
</section>
