{{-- HERO SECTION — Konsep 1: Prestigious Campus & Modern School Hero --}}
<style>
    .campus-hero {
        position: relative;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 60%, #f1f5f9 100%);
        padding-top: 130px;
        padding-bottom: 90px;
        overflow: hidden;
    }

    /* Ambient Soft Lighting */
    .campus-ambient-glow {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 1000px;
        height: 450px;
        background: radial-gradient(ellipse at top, rgba(37, 99, 235, 0.08) 0%, rgba(16, 185, 129, 0.04) 45%, transparent 75%);
        pointer-events: none;
        z-index: 0;
    }

    /* Hero Header Content */
    .campus-hero-header {
        text-align: center;
        max-width: 960px;
        margin: 0 auto 56px auto;
        position: relative;
        z-index: 1;
    }

    .campus-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 20px;
        border-radius: 100px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        font-size: 13px;
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: 20px;
    }

    .campus-hero-title {
        font-size: clamp(34px, 4.8vw, 56px);
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -0.03em;
        color: #0f172a;
        margin-bottom: 20px;
    }

    .campus-hero-title span {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0d9488 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .campus-hero-desc {
        font-size: 18px;
        line-height: 1.75;
        color: #475569;
        font-weight: 500;
        max-width: 800px;
        margin: 0 auto 36px auto;
    }

    .campus-hero-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .campus-btn-primary {
        background: #1e3a8a;
        color: #ffffff !important;
        font-size: 15.5px;
        font-weight: 800;
        padding: 15px 34px;
        border-radius: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 8px 24px -4px rgba(30, 58, 138, 0.35);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .campus-btn-primary:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px -4px rgba(37, 99, 235, 0.45);
    }

    .campus-btn-secondary {
        background: #ffffff;
        color: #0f172a !important;
        font-size: 15.5px;
        font-weight: 700;
        padding: 15px 30px;
        border-radius: 14px;
        text-decoration: none;
        border: 1.5px solid #cbd5e1;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s ease;
    }

    .campus-btn-secondary:hover {
        border-color: #2563eb;
        color: #2563eb !important;
        background: #eff6ff;
        transform: translateY(-2px);
    }

    /* 3 Unit Sekolah Floating Showcase Grid */
    .campus-units-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        position: relative;
        z-index: 1;
        margin-bottom: 56px;
    }

    @media (max-width: 1024px) {
        .campus-units-grid {
            grid-template-columns: 1fr;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
    }

    .campus-unit-card {
        background: #ffffff;
        border-radius: 22px;
        border: 1.5px solid #e2e8f0;
        padding: 32px 28px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .campus-unit-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 50px -10px rgba(15, 23, 42, 0.14);
    }

    /* Card Themes */
    .unit-card-sma { border-top: 5px solid #2563eb; }
    .unit-card-sma:hover { border-color: #2563eb; }
    .unit-card-smp { border-top: 5px solid #059669; }
    .unit-card-smp:hover { border-color: #059669; }
    .unit-card-smk { border-top: 5px solid #d97706; }
    .unit-card-smk:hover { border-color: #d97706; }

    .campus-unit-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 16px;
        width: fit-content;
    }

    .badge-sma { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-smp { background: #ecfdf5; color: #15803d; border: 1px solid #86efac; }
    .badge-smk { background: #fffbeb; color: #b45309; border: 1px solid #fde047; }

    .campus-unit-name {
        font-size: 22px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 8px;
    }

    .campus-unit-tagline {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .campus-unit-features {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .campus-unit-features li {
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .campus-unit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-unit-sma { background: #eff6ff; color: #1d4ed8; }
    .btn-unit-sma:hover { background: #2563eb; color: #ffffff; }
    
    .btn-unit-smp { background: #ecfdf5; color: #15803d; }
    .btn-unit-smp:hover { background: #059669; color: #ffffff; }

    .btn-unit-smk { background: #fffbeb; color: #b45309; }
    .btn-unit-smk:hover { background: #d97706; color: #ffffff; }

    /* Trust Stats Strip */
    .campus-trust-strip {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px 36px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .campus-trust-strip {
            grid-template-columns: repeat(2, 1fr);
            padding: 20px;
            gap: 16px;
        }
    }

    .campus-trust-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .campus-trust-num {
        font-size: clamp(28px, 3.5vw, 40px);
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin-bottom: 4px;
        font-variant-numeric: tabular-nums;
    }

    .campus-trust-label {
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
</style>

<section id="beranda" class="campus-hero">
    <div class="campus-ambient-glow"></div>

    <div class="fw">
        
        {{-- Hero Header Intro --}}
        <div class="campus-hero-header" data-aos="fade-up">
            
            <div class="campus-pill-badge">
                <i class="fa-solid fa-landmark" style="color:#2563eb;"></i>
                <span>Yayasan Perguruan PEMBDA Nias • Sejak 1970 • Akreditasi Unggul</span>
            </div>

            <h1 class="campus-hero-title">
                Membentuk Generasi Unggul, <br>
                <span>Berkarakter &amp; Berdaya Saing Global</span>
            </h1>

            <p class="campus-hero-desc">
                Pusat keunggulan pendidikan terpadu di Kepulauan Nias yang memadukan kedisiplinan karakter, kurikulum modern berbasis digital, dan kesiapan karir untuk jenjang Sekolah Menengah Atas, Pertama, dan Kejuruan.
            </p>

            <div class="campus-hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="campus-btn-primary">
                        <i class="fa-solid fa-gauge-high"></i> Buka Dashboard Sistem
                    </a>
                @else
                    <a href="{{ route('login') }}" class="campus-btn-primary">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal Sistem &rarr;
                    </a>
                @endauth

                <a href="#sekolah" class="campus-btn-secondary">
                    <i class="fa-solid fa-school" style="color:#2563eb;"></i> Profil 3 Unit Sekolah
                </a>
            </div>

        </div>

        {{-- 3 Floating Campus Unit Showcase Cards --}}
        <div class="campus-units-grid" data-aos="fade-up" data-aos-delay="100">
            
            {{-- UNIT 1: SMAS PEMBDA 1 --}}
            <div class="campus-unit-card unit-card-sma">
                <div>
                    <div class="campus-unit-badge badge-sma">
                        <i class="fa-solid fa-graduation-cap"></i> Sekolah Menengah Atas
                    </div>
                    <h3 class="campus-unit-name">SMAS PEMBDA 1</h3>
                    <p class="campus-unit-tagline">Fokus Akademik Sains &amp; Humaniora Menuju Perguruan Tinggi Negeri (PTN) &amp; Kedinasan.</p>
                    
                    <ul class="campus-unit-features">
                        <li><i class="fa-solid fa-circle-check" style="color:#2563eb;"></i> Kurikulum Merdeka &amp; Peminatan PTN</li>
                        <li><i class="fa-solid fa-circle-check" style="color:#2563eb;"></i> Bimbingan SNBP &amp; UTBK-SNBT Terarah</li>
                        <li><i class="fa-solid fa-circle-check" style="color:#2563eb;"></i> Laboratorium Sains &amp; Komputer Modern</li>
                    </ul>
                </div>

                <a href="#smas-pembda-1" class="campus-unit-btn btn-unit-sma">
                    <span>Lihat Profil SMAS 1</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            {{-- UNIT 2: SMPS PEMBDA 2 --}}
            <div class="campus-unit-card unit-card-smp">
                <div>
                    <div class="campus-unit-badge badge-smp">
                        <i class="fa-solid fa-seedling"></i> Sekolah Menengah Pertama
                    </div>
                    <h3 class="campus-unit-name">SMPS PEMBDA 2</h3>
                    <p class="campus-unit-tagline">Pondasi Karakter Unggul, Disiplin, Literasi Digital &amp; Pengembangan Potensi Siswa.</p>
                    
                    <ul class="campus-unit-features">
                        <li><i class="fa-solid fa-circle-check" style="color:#059669;"></i> Pembinaan Akhlak &amp; Karakter Mandiri</li>
                        <li><i class="fa-solid fa-circle-check" style="color:#059669;"></i> Pengenalan Teknologi &amp; Literasi Digital</li>
                        <li><i class="fa-solid fa-circle-check" style="color:#059669;"></i> Ragam Ekstrakurikuler Seni &amp; Olahraga</li>
                    </ul>
                </div>

                <a href="#smps-pembda-2" class="campus-unit-btn btn-unit-smp">
                    <span>Lihat Profil SMPS 2</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            {{-- UNIT 3: SMKS PEMBDA NIAS --}}
            <div class="campus-unit-card unit-card-smk">
                <div>
                    <div class="campus-unit-badge badge-smk">
                        <i class="fa-solid fa-gears"></i> Sekolah Menengah Kejuruan
                    </div>
                    <h3 class="campus-unit-name">SMKS PEMBDA NIAS</h3>
                    <p class="campus-unit-tagline">5 Konsentrasi Keahlian Vokasi, Sertifikasi Profesi, &amp; Kesiapan Kerja Industri (DUDI).</p>
                    
                    <ul class="campus-unit-features">
                        <li><i class="fa-solid fa-circle-check" style="color:#d97706;"></i> 5 Jurusan: RPL, TKR, TAB, TBSM, DPIB</li>
                        <li><i class="fa-solid fa-circle-check" style="color:#d97706;"></i> Bengkel Praktik &amp; Lab Komputer Standar</li>
                        <li><i class="fa-solid fa-circle-check" style="color:#d97706;"></i> Kemitraan Magang PKL Dunia Industri</li>
                    </ul>
                </div>

                <a href="#smks-pembda-nias" class="campus-unit-btn btn-unit-smk">
                    <span>Lihat 5 Jurusan SMKS</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

        </div>

        {{-- Trust & Ecosystem Statistics Bar --}}
        <div class="campus-trust-strip" data-aos="fade-up" data-aos-delay="200">
            <div class="campus-trust-item">
                <span class="campus-trust-num" style="color:#2563eb;" data-count="{{ $totalStudents }}">{{ number_format($totalStudents, 0, ',', '.') }}</span>
                <span class="campus-trust-label">Siswa Aktif Terdaftar</span>
            </div>
            <div class="campus-trust-item">
                <span class="campus-trust-num" style="color:#059669;" data-count="{{ $totalTeachers }}">{{ $totalTeachers }}</span>
                <span class="campus-trust-label">Tenaga Pendidik &amp; Staf</span>
            </div>
            <div class="campus-trust-item">
                <span class="campus-trust-num" style="color:#1e3a8a;">54+</span>
                <span class="campus-trust-label">Tahun Pengabdian (Sejak 1970)</span>
            </div>
            <div class="campus-trust-item">
                <span class="campus-trust-num" style="color:#d97706;">100%</span>
                <span class="campus-trust-label">Terintegrasi Presensi &amp; CBT</span>
            </div>
        </div>

    </div>
</section>
