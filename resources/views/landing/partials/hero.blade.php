{{-- HERO SECTION — Impeccable Next-Gen Split Bento Design --}}
<style>
    .hero-container {
        position: relative;
        overflow: hidden;
        min-height: 92vh;
        display: flex;
        align-items: center;
        padding-top: 130px;
        padding-bottom: 90px;
        background: radial-gradient(1200px circle at 50% 10%, #1e1b4b 0%, #0f172a 70%, #090d16 100%);
    }

    .hero-mesh-glow {
        position: absolute;
        top: -20%;
        left: 20%;
        width: 60vw;
        height: 60vw;
        max-width: 900px;
        max-height: 900px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(245, 158, 11, 0.08) 45%, transparent 70%);
        filter: blur(80px);
        pointer-events: none;
        z-index: 0;
    }

    .hero-split-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
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

    /* Badge */
    .hero-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 20px;
        border-radius: 100px;
        background: rgba(255, 255, 255, 0.08);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(16px);
        font-size: 13px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 24px;
    }

    .hero-title-main {
        font-size: clamp(36px, 4.5vw, 62px);
        font-weight: 900;
        line-height: 1.12;
        letter-spacing: -0.03em;
        color: #ffffff;
        margin-bottom: 20px;
    }

    .hero-title-gradient {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #fcd34d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-desc-text {
        font-size: 17px;
        line-height: 1.75;
        color: #cbd5e1;
        font-weight: 500;
        max-width: 620px;
        margin-bottom: 36px;
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
        margin-bottom: 44px;
    }

    /* Live Trust Stats Bar */
    .hero-stat-row {
        display: flex;
        align-items: center;
        gap: 28px;
        flex-wrap: wrap;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        padding-top: 24px;
    }

    .hero-stat-box {
        display: flex;
        flex-direction: column;
    }

    .hero-stat-num {
        font-size: 26px;
        font-weight: 900;
        color: #fbbf24;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .hero-stat-title {
        font-size: 12px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Right Glassmorphism App Console Showcase */
    .hero-console-card {
        background: rgba(15, 23, 42, 0.85);
        border: 1.5px solid rgba(255, 255, 255, 0.18);
        border-radius: 28px;
        padding: 24px;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 40px rgba(99, 102, 241, 0.15);
        backdrop-filter: blur(20px);
        transition: var(--transition-smooth);
        position: relative;
    }

    .hero-console-card:hover {
        border-color: rgba(99, 102, 241, 0.45);
        transform: translateY(-4px);
        box-shadow: 0 35px 70px -15px rgba(0, 0, 0, 0.7), 0 0 50px rgba(99, 102, 241, 0.25);
    }

    .console-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .console-dots {
        display: flex;
        gap: 7px;
    }

    .console-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
    }

    .console-bento-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .console-tile {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        padding: 18px;
        transition: var(--transition-smooth);
        text-align: left;
    }

    .console-tile:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }

    .console-tile.tile-wide {
        grid-column: span 2;
    }

    .console-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        margin-bottom: 12px;
    }
</style>

<section id="beranda" class="hero-container">
    {{-- Ambient Lighting --}}
    <div class="hero-mesh-glow"></div>

    <div class="fw">
        <div class="hero-split-grid">
            
            {{-- LEFT COLUMN: Powerful Brand & Narrative --}}
            <div data-aos="fade-right">
                
                {{-- Status Pill --}}
                <div class="hero-pill-badge">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping inline-block" style="width:9px; height:9px; background:#10b981; border-radius:50%;"></span>
                    <span>Ekosistem Digital Terpadu TP. 2026/2027</span>
                </div>

                {{-- Headline --}}
                <h1 class="hero-title-main">
                    Sistem Manajemen Sekolah Cerdas <br>
                    <span class="hero-title-gradient">Perguruan PEMBDA Nias</span>
                </h1>

                {{-- Subtitle --}}
                <p class="hero-desc-text">
                    Satu platform terintegrasi yang menyatukan tata kelola akademik, presensi RFID biometrik, LMS interaktif, CBT, dan keuangan untuk <strong class="text-white font-bold" style="color:#ffffff;">SMAS Pembda 1</strong>, <strong class="text-white font-bold" style="color:#ffffff;">SMPS Pembda 2</strong>, dan <strong class="text-white font-bold" style="color:#ffffff;">SMKS Pembda Nias</strong>.
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
                            <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal
                        </a>
                        @endif
                    @endauth

                    <a href="#platform" class="btn btn-ghost-white">
                        <i class="fa-solid fa-compass"></i> Eksplorasi Ekosistem
                    </a>
                </div>

                {{-- Real-time Stats Strip --}}
                <div class="hero-stat-row">
                    <div class="hero-stat-box">
                        <span class="hero-stat-num" data-count="{{ $totalStudents }}">{{ number_format($totalStudents, 0, ',', '.') }}</span>
                        <span class="hero-stat-title">Siswa Aktif</span>
                    </div>
                    <div style="width:1px; height:32px; background:rgba(255,255,255,0.15);"></div>
                    <div class="hero-stat-box">
                        <span class="hero-stat-num" data-count="{{ $totalTeachers }}">{{ $totalTeachers }}</span>
                        <span class="hero-stat-title">Tenaga Pendidik</span>
                    </div>
                    <div style="width:1px; height:32px; background:rgba(255,255,255,0.15);"></div>
                    <div class="hero-stat-box">
                        <span class="hero-stat-num">{{ $totalSchools }}</span>
                        <span class="hero-stat-title">Unit Sekolah</span>
                    </div>
                    <div style="width:1px; height:32px; background:rgba(255,255,255,0.15);"></div>
                    <div class="hero-stat-box">
                        <span class="hero-stat-num">5</span>
                        <span class="hero-stat-title">Jurusan SMK</span>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Next-Gen Bento Console UI Preview --}}
            <div data-aos="fade-left" data-aos-delay="150">
                <div class="hero-console-card">
                    
                    {{-- Console Window Bar --}}
                    <div class="console-header">
                        <div class="console-dots">
                            <span class="console-dot" style="background:#ef4444;"></span>
                            <span class="console-dot" style="background:#f59e0b;"></span>
                            <span class="console-dot" style="background:#10b981;"></span>
                        </div>
                        <div style="font-size:11px; font-weight:800; color:#94a3b8; letter-spacing:0.06em; text-transform:uppercase;">
                            <i class="fa-solid fa-shield-halved" style="color:#6366f1; margin-right:4px;"></i> PembdaHUB Core Console
                        </div>
                        <div style="display:inline-flex; align-items:center; gap:5px; background:rgba(16,185,129,0.15); color:#34d399; font-size:10px; font-weight:800; padding:3px 10px; border-radius:100px;">
                            <span style="width:6px; height:6px; background:#10b981; border-radius:50%;"></span> ONLINE
                        </div>
                    </div>

                    {{-- Bento Showcase Grid --}}
                    <div class="console-bento-grid">
                        
                        {{-- Tile 1: 3 Unit Sekolah Terpadu (Wide) --}}
                        <div class="console-tile tile-wide">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="console-icon" style="background:rgba(99,102,241,0.2); color:#818cf8; margin-bottom:0;">
                                        <i class="fa-solid fa-school"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:14.5px; font-weight:800; color:#ffffff;">3 Unit Sekolah Terpadu</div>
                                        <div style="font-size:11px; color:#94a3b8; font-weight:600;">Pendidikan Dasar & Vokasi</div>
                                    </div>
                                </div>
                                <span style="background:rgba(99,102,241,0.25); color:#c7d2fe; font-size:10.5px; font-weight:800; padding:4px 10px; border-radius:8px;">Terpusat</span>
                            </div>
                            
                            {{-- Mini Unit Tags --}}
                            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                                <div style="background:rgba(37,99,235,0.15); border:1px solid rgba(37,99,235,0.3); padding:8px; border-radius:10px; text-align:center;">
                                    <div style="font-size:12px; font-weight:800; color:#93c5fd;">SMAS 1</div>
                                    <div style="font-size:9.5px; color:#bfdbfe; font-weight:600;">Akademik</div>
                                </div>
                                <div style="background:rgba(5,150,105,0.15); border:1px solid rgba(5,150,105,0.3); padding:8px; border-radius:10px; text-align:center;">
                                    <div style="font-size:12px; font-weight:800; color:#6ee7b7;">SMPS 2</div>
                                    <div style="font-size:9.5px; color:#a7f3d0; font-weight:600;">Karakter</div>
                                </div>
                                <div style="background:rgba(217,119,6,0.15); border:1px solid rgba(217,119,6,0.3); padding:8px; border-radius:10px; text-align:center;">
                                    <div style="font-size:12px; font-weight:800; color:#fde047;">SMKS</div>
                                    <div style="font-size:9.5px; color:#fef08a; font-weight:600;">5 Kejuruan</div>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 2: Presensi RFID & Notifikasi --}}
                        <div class="console-tile">
                            <div class="console-icon" style="background:rgba(16,185,129,0.2); color:#34d399;">
                                <i class="fa-solid fa-id-card-clip"></i>
                            </div>
                            <div style="font-size:13.5px; font-weight:800; color:#ffffff; margin-bottom:4px;">Presensi RFID</div>
                            <div style="font-size:11.5px; color:#94a3b8; line-height:1.5; font-weight:500;">Tap instan kartu siswa & guru dengan rekap otomatis.</div>
                        </div>

                        {{-- Tile 3: LMS & CBT Interaktif --}}
                        <div class="console-tile">
                            <div class="console-icon" style="background:rgba(245,158,11,0.2); color:#fbbf24;">
                                <i class="fa-solid fa-laptop-code"></i>
                            </div>
                            <div style="font-size:13.5px; font-weight:800; color:#ffffff; margin-bottom:4px;">LMS &amp; CBT</div>
                            <div style="font-size:11.5px; color:#94a3b8; line-height:1.5; font-weight:500;">Bank soal, ujian aman, dan modul pembelajaran.</div>
                        </div>

                    </div>

                    {{-- Footer Mini Alert --}}
                    <div style="margin-top:16px; background:rgba(255,255,255,0.03); border:1px dashed rgba(255,255,255,0.12); padding:10px 14px; border-radius:12px; display:flex; align-items:center; justify-content:space-between;">
                        <span style="font-size:11.5px; color:#cbd5e1; font-weight:600;">
                            <i class="fa-solid fa-circle-nodes" style="color:#6366f1; margin-right:6px;"></i> Akses Guru, Siswa &amp; Orang Tua
                        </span>
                        <a href="{{ route('login') }}" style="font-size:11px; font-weight:800; color:#fbbf24; text-decoration:none;">Masuk &rarr;</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
