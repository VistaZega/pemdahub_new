{{-- HERO SECTION — Khan Academy Bright Educational Dashboard Style --}}
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

    /* Ambient Soft Mesh Glows */
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
        grid-template-columns: 1.1fr 0.9fr;
        gap: 44px;
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

    /* Khan Academy Style Learning Dashboard Mockup */
    .khan-dashboard-card {
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 20px 50px -10px rgba(37, 99, 235, 0.12), 0 0 20px rgba(16, 185, 129, 0.06);
        transition: var(--transition-smooth);
        position: relative;
        text-align: left;
    }

    .khan-dashboard-card:hover {
        border-color: #93c5fd;
        transform: translateY(-4px);
        box-shadow: 0 30px 60px -10px rgba(37, 99, 235, 0.18);
    }

    .khan-user-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 16px;
        border-bottom: 1.5px solid #f1f5f9;
        margin-bottom: 18px;
    }

    .khan-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #eff6ff;
        border: 2px solid #93c5fd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #2563eb;
    }

    .khan-energy-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fef3c7;
        border: 1px solid #fde047;
        color: #b45309;
        font-size: 11.5px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 100px;
    }

    /* Khan Academy Subject & Module Progress Card */
    .khan-module-card {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        transition: var(--transition-smooth);
    }

    .khan-module-card:hover {
        background: #ffffff;
        border-color: #93c5fd;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);
    }

    .khan-progress-bar-bg {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 10px;
    }

    .khan-progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 10px;
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

            {{-- RIGHT COLUMN: Khan Academy Inspired Learning Dashboard Mockup --}}
            <div data-aos="fade-left" data-aos-delay="150">
                <div class="khan-dashboard-card">
                    
                    {{-- User Greeting & Energy Points Bar --}}
                    <div class="khan-user-header">
                        <div class="flex items-center gap-3">
                            <div class="khan-avatar">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <div>
                                <div style="font-size:15px; font-weight:900; color:#0f172a;">Semangat Belajar, Siswa Pembda!</div>
                                <div style="font-size:11.5px; color:#64748b; font-weight:600;">TP. 2026/2027 • Portal Pembelajaran Terpadu</div>
                            </div>
                        </div>
                        <div class="khan-energy-badge">
                            <i class="fa-solid fa-star text-amber-500"></i>
                            <span>1.450 Poin Prestasi</span>
                        </div>
                    </div>

                    {{-- 3 Unit Sekolah Navigation Tabs --}}
                    <div style="margin-bottom:14px;">
                        <div style="font-size:11.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#64748b; margin-bottom:8px;">Pilih Unit Sekolah:</div>
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                            <div style="background:#eff6ff; border:1.5px solid #93c5fd; padding:10px 8px; border-radius:14px; text-align:center;">
                                <div style="font-size:13px; font-weight:900; color:#1d4ed8;">SMAS 1</div>
                                <div style="font-size:10px; color:#3b82f6; font-weight:700;">Akademik</div>
                            </div>
                            <div style="background:#ecfdf5; border:1.5px solid #86efac; padding:10px 8px; border-radius:14px; text-align:center;">
                                <div style="font-size:13px; font-weight:900; color:#15803d;">SMPS 2</div>
                                <div style="font-size:10px; color:#10b981; font-weight:700;">Karakter</div>
                            </div>
                            <div style="background:#fffbeb; border:1.5px solid #fde047; padding:10px 8px; border-radius:14px; text-align:center;">
                                <div style="font-size:13px; font-weight:900; color:#b45309;">SMKS</div>
                                <div style="font-size:10px; color:#d97706; font-weight:700;">5 Kejuruan</div>
                            </div>
                        </div>
                    </div>

                    {{-- Learning Progress Mastery Card 1: LMS --}}
                    <div class="khan-module-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:36px; height:36px; border-radius:10px; background:#ecfdf5; color:#059669; display:flex; align-items:center; justify-content:center; font-size:16px;">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                                <div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0f172a;">LMS &amp; Bank Soal CBT</div>
                                    <div style="font-size:11px; color:#64748b; font-weight:600;">4 Modul KBM Aktif • Siap Dikerjakan</div>
                                </div>
                            </div>
                            <span style="font-size:12px; font-weight:900; color:#059669;">85%</span>
                        </div>
                        <div class="khan-progress-bar-bg">
                            <div class="khan-progress-bar-fill" style="width:85%;"></div>
                        </div>
                    </div>

                    {{-- Learning Progress Card 2: RFID Presensi --}}
                    <div class="khan-module-card" style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:36px; height:36px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:16px;">
                                    <i class="fa-solid fa-id-card-clip"></i>
                                </div>
                                <div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0f172a;">Presensi Kehadiran RFID</div>
                                    <div style="font-size:11px; color:#64748b; font-weight:600;">Hadir Hari Ini (07:15 WIB)</div>
                                </div>
                            </div>
                            <span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:800; padding:4px 10px; border-radius:100px; border:1px solid #86efac;">
                                <i class="fa-solid fa-circle-check mr-1"></i> Tepat Waktu
                            </span>
                        </div>
                    </div>

                    {{-- Khan Academy Bottom Link --}}
                    <a href="{{ route('login') }}" class="btn btn-gold" style="width:100%; border-radius:14px; padding:14px; font-size:14.5px; font-weight:900; display:flex; justify-content:center; text-decoration:none;">
                        <i class="fa-solid fa-graduation-cap"></i> Buka Portal Pembelajaran &rarr;
                    </a>

                </div>
            </div>

        </div>
    </div>
</section>
