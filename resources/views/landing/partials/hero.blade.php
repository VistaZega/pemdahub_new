{{-- HERO SECTION — Khan Academy & Stripe Bright Educational Aesthetic --}}
<style>
    .hero-container {
        position: relative;
        overflow: hidden;
        min-height: 90vh;
        display: flex;
        align-items: center;
        padding-top: 135px;
        padding-bottom: 85px;
        background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 30%, #eff6ff 65%, #f8fafc 100%);
    }

    /* Ambient Soft Mesh Glows (Stripe-inspired) */
    .hero-sun-glow {
        position: absolute;
        top: -15%;
        right: 8%;
        width: 520px;
        height: 520px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.22) 0%, rgba(59, 130, 246, 0.1) 50%, transparent 70%);
        filter: blur(60px);
        pointer-events: none;
        z-index: 0;
    }

    .hero-mint-glow {
        position: absolute;
        bottom: -10%;
        left: -5%;
        width: 480px;
        height: 480px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.16) 0%, rgba(99, 102, 241, 0.08) 50%, transparent 70%);
        filter: blur(55px);
        pointer-events: none;
        z-index: 0;
    }

    .hero-split-grid {
        display: grid;
        grid-template-columns: 1.12fr 0.88fr;
        gap: 48px;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 1100px) {
        .hero-split-grid {
            grid-template-columns: 1fr;
            text-align: center;
            gap: 40px;
        }
        .hero-cta-group, .hero-stat-row {
            justify-content: center !important;
        }
    }

    /* Friendly Pill Badge */
    .hero-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 22px;
        border-radius: 100px;
        background: #ffffff;
        border: 1.5px solid #bfdbfe;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);
        font-size: 13.5px;
        font-weight: 800;
        color: #1d4ed8;
        margin-bottom: 22px;
    }

    .hero-title-main {
        font-size: clamp(34px, 4.3vw, 56px);
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -0.03em;
        color: #0f172a;
        margin-bottom: 20px;
    }

    .hero-title-gradient {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 50%, #7c3aed 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-desc-text {
        font-size: 17.5px;
        line-height: 1.75;
        color: #334155;
        font-weight: 500;
        max-width: 620px;
        margin-bottom: 34px;
    }

    @media (max-width: 1100px) {
        .hero-desc-text {
            margin-left: auto;
            margin-right: auto;
        }
    }

    .hero-cta-group {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    /* Live Trust Stats Row */
    .hero-stat-row {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        border-top: 1.5px solid #e2e8f0;
        padding-top: 24px;
    }

    .hero-stat-box {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        padding: 10px 18px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: var(--transition-smooth);
    }

    .hero-stat-box:hover {
        transform: translateY(-2px);
        border-color: #bfdbfe;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.1);
    }

    .hero-stat-num {
        font-size: 24px;
        font-weight: 900;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .hero-stat-title {
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Right Glassmorphism App Console Showcase (Stripe Inspired) */
    .hero-console-card {
        background: rgba(255, 255, 255, 0.96);
        border: 2px solid #e2e8f0;
        border-radius: 28px;
        padding: 26px;
        box-shadow: 0 25px 60px -15px rgba(37, 99, 235, 0.12), 0 0 30px rgba(245, 158, 11, 0.06);
        backdrop-filter: blur(20px);
        transition: var(--transition-smooth);
        position: relative;
    }

    .hero-console-card:hover {
        border-color: #93c5fd;
        transform: translateY(-4px);
        box-shadow: 0 35px 70px -15px rgba(37, 99, 235, 0.18);
    }

    .console-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 18px;
    }

    .console-dots {
        display: flex;
        gap: 7px;
    }

    .console-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .console-bento-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .console-tile {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
        transition: var(--transition-smooth);
        text-align: left;
    }

    .console-tile:hover {
        background: #ffffff;
        border-color: #93c5fd;
        box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
    }

    .console-tile.tile-wide {
        grid-column: span 2;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .console-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 10px;
    }
</style>

<section id="beranda" class="hero-container">
    {{-- Cheerful Luminous Ambient Glows --}}
    <div class="hero-sun-glow"></div>
    <div class="hero-mint-glow"></div>

    <div class="fw">
        <div class="hero-split-grid">
            
            {{-- LEFT COLUMN: Inspiring & Welcoming Narrative --}}
            <div data-aos="fade-right">
                
                {{-- Status Pill --}}
                <div class="hero-pill-badge">
                    <span style="width:10px; height:10px; background:#10b981; border-radius:50%; box-shadow:0 0 10px #10b981;"></span>
                    <span>Ekosistem Sekolah Cerdas Terpadu • TP. 2026/2027</span>
                </div>

                {{-- Headline --}}
                <h1 class="hero-title-main">
                    Membangun Generasi Cerdas &amp; Berkarakter <br>
                    <span class="hero-title-gradient">Perguruan PEMBDA Nias</span>
                </h1>

                {{-- Subtitle --}}
                <p class="hero-desc-text">
                    Satu ekosistem digital terintegrasi yang menyatukan tata kelola akademik, presensi RFID biometrik, LMS cerdas, ujian CBT, dan keuangan untuk <strong style="color:#2563eb; font-weight:800;">SMAS Pembda 1</strong>, <strong style="color:#059669; font-weight:800;">SMPS Pembda 2</strong>, dan <strong style="color:#d97706; font-weight:800;">SMKS Pembda Nias</strong>.
                </p>

                {{-- CTA Action Group --}}
                <div class="hero-cta-group">
                    @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-gold">
                        <i class="fa-solid fa-gauge-high"></i> Buka Dashboard
                    </a>
                    @else
                        @if(isset($activeWave) && $activeWave)
                        <a href="{{ route('public.registration.index') }}" class="btn btn-gold">
                            <i class="fa-solid fa-user-plus"></i> Daftar PSB Sekarang
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-gold">
                            <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal Sistem &rarr;
                        </a>
                        @endif
                    @endauth

                    <a href="#sekolah" class="btn btn-ghost">
                        <i class="fa-solid fa-school" style="color:#2563eb;"></i> Jelajahi Unit Sekolah
                    </a>
                </div>

                {{-- Real-time Stats Strip --}}
                <div class="hero-stat-row">
                    <div class="hero-stat-box">
                        <span class="hero-stat-num" data-count="{{ $totalStudents }}" style="color:#2563eb;">{{ number_format($totalStudents, 0, ',', '.') }}</span>
                        <span class="hero-stat-title">Siswa Aktif</span>
                    </div>
                    <div class="hero-stat-box">
                        <span class="hero-stat-num" data-count="{{ $totalTeachers }}" style="color:#059669;">{{ $totalTeachers }}</span>
                        <span class="hero-stat-title">Tenaga Pendidik</span>
                    </div>
                    <div class="hero-stat-box">
                        <span class="hero-stat-num" style="color:#7c3aed;">{{ $totalSchools }}</span>
                        <span class="hero-stat-title">Unit Sekolah</span>
                    </div>
                    <div class="hero-stat-box">
                        <span class="hero-stat-num" style="color:#d97706;">5</span>
                        <span class="hero-stat-title">Jurusan SMK</span>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Stripe-Inspired Interactive Console Showcase --}}
            <div data-aos="fade-left" data-aos-delay="150">
                <div class="hero-console-card">
                    
                    {{-- Console Window Bar --}}
                    <div class="console-header">
                        <div class="console-dots">
                            <span class="console-dot" style="background:#ef4444;"></span>
                            <span class="console-dot" style="background:#f59e0b;"></span>
                            <span class="console-dot" style="background:#10b981;"></span>
                        </div>
                        <div style="font-size:12px; font-weight:800; color:#475569; letter-spacing:0.04em; text-transform:uppercase;">
                            <i class="fa-solid fa-shield-halved" style="color:#2563eb; margin-right:4px;"></i> PembdaHUB Smart Core
                        </div>
                        <div style="display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#15803d; font-size:10.5px; font-weight:800; padding:4px 12px; border-radius:100px; border:1px solid #86efac;">
                            <span style="width:6px; height:6px; background:#16a34a; border-radius:50%;"></span> AKTIF &amp; SYNC
                        </div>
                    </div>

                    {{-- Bento Showcase Grid --}}
                    <div class="console-bento-grid">
                        
                        {{-- Tile 1: 3 Unit Sekolah Terpadu (Wide) --}}
                        <div class="console-tile tile-wide">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="console-icon" style="background:#dbeafe; color:#2563eb; margin-bottom:0;">
                                        <i class="fa-solid fa-school"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:15px; font-weight:900; color:#0f172a;">3 Unit Sekolah Terpadu</div>
                                        <div style="font-size:11.5px; color:#475569; font-weight:600;">Pendidikan Dasar, Menengah &amp; Vokasi</div>
                                    </div>
                                </div>
                                <span style="background:#dbeafe; color:#1d4ed8; font-size:10.5px; font-weight:800; padding:4px 10px; border-radius:8px;">Terpusat</span>
                            </div>
                            
                            {{-- Mini Unit Tags --}}
                            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                                <div style="background:#ffffff; border:1.5px solid #93c5fd; padding:10px 8px; border-radius:12px; text-align:center; box-shadow:0 2px 6px rgba(37,99,235,0.08);">
                                    <div style="font-size:13px; font-weight:900; color:#1d4ed8;">SMAS 1</div>
                                    <div style="font-size:10px; color:#3b82f6; font-weight:700;">Akademik &amp; PTN</div>
                                </div>
                                <div style="background:#ffffff; border:1.5px solid #86efac; padding:10px 8px; border-radius:12px; text-align:center; box-shadow:0 2px 6px rgba(16,185,129,0.08);">
                                    <div style="font-size:13px; font-weight:900; color:#15803d;">SMPS 2</div>
                                    <div style="font-size:10px; color:#10b981; font-weight:700;">Karakter Unggul</div>
                                </div>
                                <div style="background:#ffffff; border:1.5px solid #fde047; padding:10px 8px; border-radius:12px; text-align:center; box-shadow:0 2px 6px rgba(245,158,11,0.08);">
                                    <div style="font-size:13px; font-weight:900; color:#b45309;">SMKS</div>
                                    <div style="font-size:10px; color:#d97706; font-weight:700;">5 Kejuruan DUDI</div>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 2: Presensi RFID & Notifikasi --}}
                        <div class="console-tile">
                            <div class="console-icon" style="background:#dcfce7; color:#16a34a;">
                                <i class="fa-solid fa-id-card-clip"></i>
                            </div>
                            <div style="font-size:14px; font-weight:900; color:#0f172a; margin-bottom:4px;">Presensi RFID</div>
                            <div style="font-size:12px; color:#475569; line-height:1.5; font-weight:500;">Tap kartu otomatis, rekap real-time, dan notifikasi kehadiran.</div>
                        </div>

                        {{-- Tile 3: LMS & CBT Interaktif --}}
                        <div class="console-tile">
                            <div class="console-icon" style="background:#fef3c7; color:#d97706;">
                                <i class="fa-solid fa-laptop-code"></i>
                            </div>
                            <div style="font-size:14px; font-weight:900; color:#0f172a; margin-bottom:4px;">LMS &amp; Ujian CBT</div>
                            <div style="font-size:12px; color:#475569; line-height:1.5; font-weight:500;">Modul materi, bank soal ujian online aman, dan penilaian.</div>
                        </div>

                    </div>

                    {{-- Footer Mini Alert --}}
                    <div style="margin-top:16px; background:#f1f5f9; border:1.5px solid #e2e8f0; padding:12px 16px; border-radius:14px; display:flex; align-items:center; justify-content:space-between;">
                        <span style="font-size:12px; color:#334155; font-weight:700;">
                            <i class="fa-solid fa-circle-nodes" style="color:#2563eb; margin-right:6px;"></i> Akses Guru, Siswa &amp; Orang Tua
                        </span>
                        <a href="{{ route('login') }}" style="font-size:12px; font-weight:900; color:#2563eb; text-decoration:none; display:flex; align-items:center; gap:4px;">
                            Masuk Portal &rarr;
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
