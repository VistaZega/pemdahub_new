{{-- HERO SECTION — Bold Indigo Theme --}}
<style>
    .hero-section {
        position: relative;
        background: transparent;
        overflow: hidden;
    }
    .hero-bg-blob1 {
        position: absolute; top: -10%; left: -10%; width: 50vw; height: 50vw;
        background: radial-gradient(circle, rgba(99,102,241,0.35) 0%, transparent 70%);
        border-radius: 50%; filter: blur(60px); z-index: 0; pointer-events: none;
    }
    .hero-bg-blob2 {
        position: absolute; bottom: -10%; right: -10%; width: 45vw; height: 45vw;
        background: radial-gradient(circle, rgba(16,185,129,0.25) 0%, transparent 70%);
        border-radius: 50%; filter: blur(60px); z-index: 0; pointer-events: none;
    }
    .hero-bg-blob3 {
        position: absolute; top: 30%; left: 50%; width: 35vw; height: 35vw;
        background: radial-gradient(circle, rgba(245,158,11,0.2) 0%, transparent 70%);
        border-radius: 50%; filter: blur(60px); z-index: 0; pointer-events: none;
    }
    .hero-card {
        background: rgba(255, 255, 255, 0.04) !important;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        z-index: 2;
    }
    .hero-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        opacity: 0; transition: opacity 0.4s ease;
    }
    .hero-card:hover {
        transform: translateY(-8px);
        background: rgba(255, 255, 255, 0.08) !important;
    }
    .hero-card:hover::before { opacity: 1; }
    
    .hero-card-blue:hover { box-shadow: 0 20px 40px -10px rgba(59,130,246,0.3); border-color: rgba(59,130,246,0.5) !important; }
    .hero-card-emerald:hover { box-shadow: 0 20px 40px -10px rgba(16,185,129,0.3); border-color: rgba(16,185,129,0.5) !important; }
    .hero-card-gold:hover { box-shadow: 0 20px 40px -10px rgba(245,158,11,0.3); border-color: rgba(245,158,11,0.5) !important; }
    .hero-card-coral:hover { box-shadow: 0 20px 40px -10px rgba(244,63,94,0.3); border-color: rgba(244,63,94,0.5) !important; }

    .hero-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        font-size: 20px;
        color: #fff;
    }
    .hero-card-title {
        font-size: 16px;
        font-weight: 800;
        color: #fff;
        margin-bottom: 6px;
        letter-spacing: -0.01em;
    }
    .hero-card-desc {
        font-size: 13px;
        color: rgba(255,255,255,0.75);
        line-height: 1.5;
    }
    .live-stat-strip {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 32px;
        margin-top: 48px;
        flex-wrap: wrap;
    }
    .live-stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 24px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 100px;
        backdrop-filter: blur(8px);
        transition: var(--transition-smooth);
    }
    .live-stat-item:hover {
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }
    .live-stat-val {
        font-size: 24px;
        font-weight: 800;
        color: var(--gold-bright);
        line-height: 1;
    }
    .live-stat-label {
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,0.8);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    @media (max-width: 768px) {
        .hero-cards-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .live-stat-strip { gap: 16px; }
        .live-stat-item { padding: 10px 16px; }
    }
    @media (max-width: 480px) {
        .hero-cards-grid { grid-template-columns: 1fr !important; }
    }
</style>

<section id="beranda" class="hero-section">
    {{-- Background elements --}}
    <div class="hero-bg-blob1"></div>
    <div class="hero-bg-blob2"></div>
    <div class="hero-bg-blob3"></div>
    {{-- Animated rings --}}
    <div class="hero-ring" style="width:300px; height:300px; top:10%; left:2%; animation-delay:0s;"></div>
    <div class="hero-ring" style="width:500px; height:500px; top:5%; left:-5%; animation-delay:2s; border-color:rgba(245,158,11,0.04);"></div>
    <div class="hero-ring" style="width:200px; height:200px; bottom:15%; right:10%; animation-delay:3s;"></div>

    {{-- Floating particles --}}
    <div class="particle-container" style="position:absolute; inset:0; overflow:hidden; pointer-events:none; z-index:0;">
        <div class="particle" style="width: 8px; height: 8px; left: 10%; animation-delay: 0s; animation-duration: 12s;"></div>
        <div class="particle" style="width: 6px; height: 6px; left: 25%; animation-delay: 2s; animation-duration: 16s;"></div>
        <div class="particle" style="width: 10px; height: 10px; left: 40%; animation-delay: 4s; animation-duration: 14s;"></div>
        <div class="particle" style="width: 5px; height: 5px; left: 55%; animation-delay: 1s; animation-duration: 18s;"></div>
        <div class="particle" style="width: 7px; height: 7px; left: 70%; animation-delay: 5s; animation-duration: 13s;"></div>
        <div class="particle" style="width: 9px; height: 9px; left: 85%; animation-delay: 3s; animation-duration: 15s;"></div>
    </div>

    <div class="fw" style="width:100%; position:relative; z-index:1;">
        {{-- Main headline --}}
        <div data-aos="fade-up" style="text-align:center; margin-bottom:60px;">

            {{-- Badge --}}
            <div class="badge" style="margin-bottom:28px;">
                <div class="pulse" style="background:#10b981;"></div>
                <span>Selamat Datang di Ekosistem Pendidikan Digital Masa Depan</span>
            </div>

            {{-- Main Title --}}
            <h1 class="display" style="margin-bottom:16px; color:#ffffff; text-shadow: 0 4px 20px rgba(0,0,0,0.3); font-weight: 900; letter-spacing: -0.05em;">
                Yayasan Perguruan <span style="background:linear-gradient(135deg, #38bdf8, #818cf8, #c084fc, #e879f9); -webkit-background-clip:text; color:transparent; filter: drop-shadow(0 0 20px rgba(139,92,246,0.5));">PEMBDA</span>
            </h1>

            {{-- Underline decoration --}}
            <div style="width:140px; height:6px; background:linear-gradient(90deg, #38bdf8, #818cf8, #c084fc); border-radius:3px; margin: 0 auto 28px; box-shadow: 0 2px 20px rgba(139,92,246,0.6);"></div>

            <p class="body-lg" style="max-width:720px; margin:0 auto 14px; font-size:22px; color:rgba(255,255,255,0.92); min-height: 66px; line-height: 1.4;">
                Pusat Keunggulan Akademik dan Inovasi:<br>
                <span class="typewriter-text" style="color:#fbbf24; font-weight:800; border-right: 2px solid #fbbf24; padding-right: 5px;"></span><span class="typewriter-cursor" style="border-right: 2px solid #fbbf24;"></span>
            </p>
            <p style="max-width:640px; margin:0 auto 32px; font-size:16px; color:rgba(255,255,255,0.6); line-height:1.7;">
                Menghubungkan <strong style="color:rgba(255,255,255,0.85);">{{ $totalSchools }} unit sekolah</strong>,
                <strong style="color:rgba(255,255,255,0.85);">{{ number_format($totalStudents, 0, ',', '.') }} siswa aktif</strong>,
                dan ribuan alumni dalam satu platform pendidikan yang terintegrasi penuh.
            </p>

            {{-- CTA Buttons --}}
            <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-top: 40px;">
                <a href="{{ route('public.registration.index') }}" class="btn" style="background: #ffffff; color: #030014; box-shadow: 0 10px 30px rgba(255,255,255,0.3); font-family: 'Plus Jakarta Sans'; border-radius: 100px;">
                    <i class="fa-solid fa-user-plus"></i> Pendaftaran Siswa Baru
                </a>
                <a href="{{ route('login') }}" class="btn btn-ghost-white" style="border-radius: 100px; font-family: 'Plus Jakarta Sans';">
                    <i class="fa-solid fa-right-to-bracket"></i> Portal Civitas
                </a>
            </div>

            {{-- Live Stat Strip --}}
            <div class="live-stat-strip" data-aos="fade-up" data-aos-delay="150">
                <div class="live-stat-item">
                    <div class="live-stat-val" data-count="{{ $totalStudents }}">0</div>
                    <div class="live-stat-label">Siswa Aktif</div>
                </div>
                <div class="live-stat-item">
                    <div class="live-stat-val" data-count="{{ $totalTeachers }}">0</div>
                    <div class="live-stat-label">Tenaga Pendidik</div>
                </div>
                <div class="live-stat-item">
                    <div class="live-stat-val" data-count="{{ $totalAlumni }}">0</div>
                    <div class="live-stat-label">Jejaring Alumni</div>
                </div>
            </div>
        </div>

        {{-- Hero Feature Cards - Vibrant Solid Colors --}}
        <div class="hero-cards-grid" data-aos="fade-up" data-aos-delay="250" style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; max-width:1100px; margin:0 auto;">

            {{-- Card 1: Pembelajaran Digital --}}
            <div class="hero-card hero-card-blue shimmer-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <div class="hero-card-title">LMS & CBT</div>
                <p class="hero-card-desc">Sistem E-Learning interaktif dan Ujian Berbasis Komputer standar nasional.</p>
            </div>

            {{-- Card 2: PKL & TEFA --}}
            <div class="hero-card hero-card-emerald shimmer-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-industry"></i>
                </div>
                <div class="hero-card-title">Kesiapan Industri</div>
                <p class="hero-card-desc">Pemantauan jurnal PKL real-time dan ekosistem Teaching Factory (TEFA).</p>
            </div>

            {{-- Card 3: Kedisiplinan Pintar --}}
            <div class="hero-card hero-card-gold shimmer-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
                <div class="hero-card-title">Smart Attendance</div>
                <p class="hero-card-desc">Absensi terintegrasi kartu pintar (RFID) demi kedisiplinan dan keamanan.</p>
            </div>

            {{-- Card 4: Portal Transparan --}}
            <div class="hero-card hero-card-coral shimmer-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-users-between-lines"></i>
                </div>
                <div class="hero-card-title">Kolaborasi Aktif</div>
                <p class="hero-card-desc">Portal khusus Orang Tua untuk memantau nilai dan perkembangan siswa.</p>
            </div>
        </div>
    </div>
</section>

{{-- Wave transition from hero to content --}}
<div class="wave-top">
    <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0,30 C360,60 1080,0 1440,30 L1440,0 L0,0 Z"/>
    </svg>
</div>
