{{-- HERO SECTION — Konsep 2: Smart Education Portal (Gaya Belajar.id / Google for Education) --}}
<style>
    .smart-hero {
        position: relative;
        background: linear-gradient(180deg, #f0f7ff 0%, #ffffff 50%, #f8fafc 100%);
        padding-top: 130px;
        padding-bottom: 90px;
        overflow: hidden;
    }

    /* Ambient Soft Lighting */
    .smart-ambient-glow {
        position: absolute;
        top: -10%;
        left: 50%;
        transform: translateX(-50%);
        width: 900px;
        height: 400px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, rgba(16, 185, 129, 0.05) 50%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }

    .smart-hero-header {
        text-align: center;
        max-width: 920px;
        margin: 0 auto 40px auto;
        position: relative;
        z-index: 1;
    }

    .smart-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 22px;
        border-radius: 100px;
        background: #ffffff;
        border: 1.5px solid #bfdbfe;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
        font-size: 13px;
        font-weight: 800;
        color: #1d4ed8;
        margin-bottom: 20px;
    }

    .smart-hero-title {
        font-size: clamp(34px, 4.6vw, 54px);
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -0.03em;
        color: #0f172a;
        margin-bottom: 18px;
    }

    .smart-hero-title span {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .smart-hero-desc {
        font-size: 17.5px;
        line-height: 1.7;
        color: #334155;
        font-weight: 500;
        max-width: 780px;
        margin: 0 auto;
    }

    /* 4-Role Interactive Switcher Hub */
    .smart-portal-hub {
        max-width: 1060px;
        margin: 0 auto 48px auto;
        background: #ffffff;
        border-radius: 28px;
        border: 2px solid #e2e8f0;
        box-shadow: 0 20px 50px -10px rgba(37, 99, 235, 0.1), 0 4px 16px -2px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        position: relative;
        z-index: 2;
    }

    /* Tabs Header */
    .smart-tabs-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }

    @media (max-width: 860px) {
        .smart-tabs-bar {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .smart-tab-btn {
        padding: 18px 16px;
        font-size: 14.5px;
        font-weight: 800;
        color: #64748b;
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.25s ease;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
    }

    .smart-tab-btn:hover {
        color: #0f172a;
        background: #ffffff;
    }

    .smart-tab-btn.tab-siswa-active {
        color: #2563eb;
        background: #ffffff;
        border-bottom: 3px solid #2563eb;
    }

    .smart-tab-btn.tab-guru-active {
        color: #059669;
        background: #ffffff;
        border-bottom: 3px solid #059669;
    }

    .smart-tab-btn.tab-ortu-active {
        color: #d97706;
        background: #ffffff;
        border-bottom: 3px solid #d97706;
    }

    .smart-tab-btn.tab-dudi-active {
        color: #7c3aed;
        background: #ffffff;
        border-bottom: 3px solid #7c3aed;
    }

    /* Tab Content Area */
    .smart-tab-body {
        padding: 40px 48px;
    }

    @media (max-width: 768px) {
        .smart-tab-body {
            padding: 28px 20px;
        }
    }

    .smart-tab-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 40px;
        align-items: center;
    }

    @media (max-width: 900px) {
        .smart-tab-grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }
    }

    .smart-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 800;
        padding: 5px 14px;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 14px;
    }

    .smart-role-title {
        font-size: 26px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 12px;
        line-height: 1.25;
    }

    .smart-role-desc {
        font-size: 15.5px;
        color: #475569;
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .smart-feature-list {
        list-style: none;
        padding: 0;
        margin: 0 0 28px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    @media (max-width: 600px) {
        .smart-feature-list {
            grid-template-columns: 1fr;
        }
    }

    .smart-feature-item {
        font-size: 13.5px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Right Preview Box in Tab */
    .smart-preview-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .smart-preview-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
    }

    /* Quick Action Buttons */
    .smart-btn-action {
        padding: 14px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s ease;
    }

    .btn-action-siswa {
        background: #2563eb;
        color: #ffffff !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
    }
    .btn-action-siswa:hover { background: #1d4ed8; transform: translateY(-2px); }

    .btn-action-guru {
        background: #059669;
        color: #ffffff !important;
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
    }
    .btn-action-guru:hover { background: #047857; transform: translateY(-2px); }

    .btn-action-ortu {
        background: #d97706;
        color: #ffffff !important;
        box-shadow: 0 6px 20px rgba(217, 119, 6, 0.35);
    }
    .btn-action-ortu:hover { background: #b45309; transform: translateY(-2px); }

    .btn-action-dudi {
        background: #7c3aed;
        color: #ffffff !important;
        box-shadow: 0 6px 20px rgba(124, 58, 237, 0.35);
    }
    .btn-action-dudi:hover { background: #6d28d9; transform: translateY(-2px); }

    /* Bottom 3 Unit Strip */
    .smart-units-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        max-width: 1060px;
        margin: 0 auto 36px auto;
    }

    @media (max-width: 768px) {
        .smart-units-strip {
            grid-template-columns: 1fr;
        }
    }

    .smart-unit-item {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        transition: all 0.25s ease;
        text-decoration: none;
    }

    .smart-unit-item:hover {
        border-color: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
    }

    /* Trust Stats Strip */
    .smart-trust-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        max-width: 1060px;
        margin: 0 auto;
        text-align: center;
        border-top: 1.5px solid #e2e8f0;
        padding-top: 32px;
    }

    @media (max-width: 768px) {
        .smart-trust-strip {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .smart-trust-num {
        font-size: 28px;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .smart-trust-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
</style>

<section id="beranda" class="smart-hero">
    <div class="smart-ambient-glow"></div>

    <div class="fw">
        
        {{-- Hero Header Intro --}}
        <div class="smart-hero-header" data-aos="fade-up">
            <div class="smart-pill-badge">
                <i class="fa-solid fa-cloud" style="color:#2563eb;"></i>
                <span>Smart School Ecosystem • Terintegrasi Cloud &amp; RFID Biometrik</span>
            </div>

            <h1 class="smart-hero-title">
                Satu Portal Pintar untuk Seluruh <br>
                <span>Civitas Akademika Perguruan PEMBDA</span>
            </h1>

            <p class="smart-hero-desc">
                Akses cepat dan terpadu untuk kegiatan belajar mengajar, presensi biometrik RFID, penilaian ujian CBT, pemantauan orang tua, dan kemitraan magang industri untuk <strong>SMAS Pembda 1</strong>, <strong>SMPS Pembda 2</strong>, dan <strong>SMKS Pembda Nias</strong>.
            </p>
        </div>

        {{-- 4-Role Interactive Switcher Hub (Alpine.js) --}}
        <div class="smart-portal-hub" x-data="{ activeTab: 'siswa' }" data-aos="fade-up" data-aos-delay="100">
            
            {{-- Tabs Header Bar --}}
            <div class="smart-tabs-bar">
                <button type="button" class="smart-tab-btn" :class="activeTab === 'siswa' ? 'tab-siswa-active' : ''" @click="activeTab = 'siswa'">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Portal Siswa</span>
                </button>
                <button type="button" class="smart-tab-btn" :class="activeTab === 'guru' ? 'tab-guru-active' : ''" @click="activeTab = 'guru'">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <span>Portal Guru</span>
                </button>
                <button type="button" class="smart-tab-btn" :class="activeTab === 'ortu' ? 'tab-ortu-active' : ''" @click="activeTab = 'ortu'">
                    <i class="fa-solid fa-users-viewfinder"></i>
                    <span>Orang Tua</span>
                </button>
                <button type="button" class="smart-tab-btn" :class="activeTab === 'dudi' ? 'tab-dudi-active' : ''" @click="activeTab = 'dudi'">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>Mitra DUDI &amp; Alumni</span>
                </button>
            </div>

            {{-- TAB 1: PORTAL SISWA --}}
            <div class="smart-tab-body" x-show="activeTab === 'siswa'">
                <div class="smart-tab-grid">
                    <div>
                        <div class="smart-role-badge" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;">
                            <i class="fa-solid fa-book-open-reader"></i> Layanan Pembelajaran Siswa
                        </div>
                        <h2 class="smart-role-title">Ruang Belajar Digital, Tugas, Nilai &amp; Ujian CBT Online</h2>
                        <p class="smart-role-desc">Akses materi pelajaran interaktif, kerjakan tugas harian, ikuti ujian CBT aman, dan pantau grafik nilai akademik secara mandiri.</p>
                        
                        <ul class="smart-feature-list">
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#2563eb;"></i> Modul LMS &amp; Materi Pembelajaran</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#2563eb;"></i> Ujian Online CBT Anti-Curang</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#2563eb;"></i> Presensi Kartu RFID Digital</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#2563eb;"></i> Rapor &amp; Rekap Poin Prestasi</li>
                        </ul>

                        <div style="display:flex; gap:14px; flex-wrap:wrap;">
                            <a href="{{ route('login') }}" class="smart-btn-action btn-action-siswa">
                                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal Siswa &rarr;
                            </a>
                            <a href="{{ route('public.registration.check') }}" class="campus-btn-secondary" style="padding:14px 22px; font-size:14px;">
                                <i class="fa-solid fa-magnifying-glass"></i> Cek Status PSB
                            </a>
                        </div>
                    </div>

                    <div class="smart-preview-box">
                        <div style="font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Live Siswa Dashboard Preview</div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-id-card-clip" style="color:#2563eb;"></i> Status Presensi Hari Ini</span>
                            <span style="color:#15803d; background:#ecfdf5; padding:3px 10px; border-radius:6px; font-size:12px;">✓ Hadir 07:15 WIB</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-laptop-code" style="color:#2563eb;"></i> Modul LMS Aktif</span>
                            <span style="color:#1d4ed8; background:#eff6ff; padding:3px 10px; border-radius:6px; font-size:12px;">4 Pelajaran</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-star" style="color:#d97706;"></i> Poin Prestasi Siswa</span>
                            <span style="color:#b45309; background:#fffbeb; padding:3px 10px; border-radius:6px; font-size:12px;">1.450 Poin ⭐</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: PORTAL GURU --}}
            <div class="smart-tab-body" x-show="activeTab === 'guru'" style="display:none;">
                <div class="smart-tab-grid">
                    <div>
                        <div class="smart-role-badge" style="background:#ecfdf5; color:#15803d; border:1px solid #86efac;">
                            <i class="fa-solid fa-chalkboard-user"></i> Layanan Pendidik &amp; Staf
                        </div>
                        <h2 class="smart-role-title">Manajemen Kelas, Jurnal KBM &amp; Bank Soal Terpadu</h2>
                        <p class="smart-role-desc">Kelola absensi siswa per jam pelajaran, buat bank soal CBT, input nilai Rapor Kurikulum Merdeka, dan pantau jurnal mengajar harian.</p>
                        
                        <ul class="smart-feature-list">
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> Presensi Kelas &amp; Jurnal Harian</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> Pembuat Soal Ujian CBT &amp; Kisi-kisi</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> Penilaian Formatif &amp; Sumatif</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> Pengolahan Nilai Rapor Otomatis</li>
                        </ul>

                        <div style="display:flex; gap:14px; flex-wrap:wrap;">
                            <a href="{{ route('login') }}" class="smart-btn-action btn-action-guru">
                                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal Guru &rarr;
                            </a>
                        </div>
                    </div>

                    <div class="smart-preview-box">
                        <div style="font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Live Guru Dashboard Preview</div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-chalkboard" style="color:#059669;"></i> Jadwal Mengajar Hari Ini</span>
                            <span style="color:#15803d; background:#ecfdf5; padding:3px 10px; border-radius:6px; font-size:12px;">3 Kelas Aktif</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-users" style="color:#059669;"></i> Rekap Presensi Siswa</span>
                            <span style="color:#15803d; background:#ecfdf5; padding:3px 10px; border-radius:6px; font-size:12px;">98.4% Hadir</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-file-signature" style="color:#059669;"></i> Input Nilai Rapor</span>
                            <span style="color:#1d4ed8; background:#eff6ff; padding:3px 10px; border-radius:6px; font-size:12px;">Siap Diisi</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: PORTAL ORANG TUA --}}
            <div class="smart-tab-body" x-show="activeTab === 'ortu'" style="display:none;">
                <div class="smart-tab-grid">
                    <div>
                        <div class="smart-role-badge" style="background:#fffbeb; color:#b45309; border:1px solid #fde047;">
                            <i class="fa-solid fa-heart-pulse"></i> Pemantauan Orang Tua / Wali
                        </div>
                        <h2 class="smart-role-title">Pantau Kehadiran, Nilai &amp; Perkembangan Anak Real-time</h2>
                        <p class="smart-role-desc">Dapatkan notifikasi langsung saat anak tiba dan pulang sekolah, pantau grafik nilai ujian, serta kemudahan informasi administrasi SPP.</p>
                        
                        <ul class="smart-feature-list">
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#d97706;"></i> Notifikasi Presensi Masuk &amp; Pulang</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#d97706;"></i> Laporan Nilai Akademik &amp; Sikap</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#d97706;"></i> Transparansi Tagihan &amp; SPP Sekolah</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#d97706;"></i> Saluran Komunikasi Wali Kelas</li>
                        </ul>

                        <div style="display:flex; gap:14px; flex-wrap:wrap;">
                            <a href="{{ route('login') }}" class="smart-btn-action btn-action-ortu">
                                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal Orang Tua &rarr;
                            </a>
                        </div>
                    </div>

                    <div class="smart-preview-box">
                        <div style="font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Live Orang Tua Monitoring Preview</div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-bell" style="color:#d97706;"></i> Notifikasi Tap RFID Masuk</span>
                            <span style="color:#15803d; background:#ecfdf5; padding:3px 10px; border-radius:6px; font-size:12px;">07:12 WIB ✓</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-chart-line" style="color:#d97706;"></i> Rata-rata Nilai Semester</span>
                            <span style="color:#1d4ed8; background:#eff6ff; padding:3px 10px; border-radius:6px; font-size:12px;">88.5 (Sangat Baik)</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-receipt" style="color:#d97706;"></i> Status Administrasi SPP</span>
                            <span style="color:#15803d; background:#ecfdf5; padding:3px 10px; border-radius:6px; font-size:12px;">Lunas</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: PORTAL DUDI & ALUMNI --}}
            <div class="smart-tab-body" x-show="activeTab === 'dudi'" style="display:none;">
                <div class="smart-tab-grid">
                    <div>
                        <div class="smart-role-badge" style="background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe;">
                            <i class="fa-solid fa-handshake"></i> Kemitraan Industri &amp; Karir
                        </div>
                        <h2 class="smart-role-title">Portal Kerjasama Magang PKL &amp; Jejak Karir Alumni</h2>
                        <p class="smart-role-desc">Fasilitas bagi mitra Dunia Usaha dan Dunia Industri (DUDI) untuk memonitoring jurnal magang siswa SMK serta pusat informasi karir alumni.</p>
                        
                        <ul class="smart-feature-list">
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#7c3aed;"></i> Monitoring Jurnal Harian PKL Siswa</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#7c3aed;"></i> Penilaian Pembimbing Industri DUDI</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#7c3aed;"></i> Informasi Lowongan Kerja &amp; Magang</li>
                            <li class="smart-feature-item"><i class="fa-solid fa-circle-check" style="color:#7c3aed;"></i> Tracer Study &amp; Database Alumni</li>
                        </ul>

                        <div style="display:flex; gap:14px; flex-wrap:wrap;">
                            <a href="{{ route('login') }}" class="smart-btn-action btn-action-dudi">
                                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal DUDI &rarr;
                            </a>
                        </div>
                    </div>

                    <div class="smart-preview-box">
                        <div style="font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">Live Kemitraan DUDI Preview</div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-building" style="color:#7c3aed;"></i> Mitra DUDI Terdaftar</span>
                            <span style="color:#6d28d9; background:#f5f3ff; padding:3px 10px; border-radius:6px; font-size:12px;">30+ Industri</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-briefcase" style="color:#7c3aed;"></i> Siswa Aktif Magang PKL</span>
                            <span style="color:#15803d; background:#ecfdf5; padding:3px 10px; border-radius:6px; font-size:12px;">Tersalurkan</span>
                        </div>
                        <div class="smart-preview-row">
                            <span style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-user-tie" style="color:#7c3aed;"></i> Database Alumni</span>
                            <span style="color:#1d4ed8; background:#eff6ff; padding:3px 10px; border-radius:6px; font-size:12px;">Terhubung</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- 3 Unit Sekolah Quick Links Strip --}}
        <div class="smart-units-strip" data-aos="fade-up" data-aos-delay="150">
            <a href="#smas-pembda-1" class="smart-unit-item">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:10px; background:#eff6ff; color:#1d4ed8; display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <div style="font-size:14.5px; font-weight:800; color:#0f172a;">SMAS PEMBDA 1</div>
                        <div style="font-size:11.5px; color:#64748b;">Akademik &amp; Peminatan PTN</div>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right" style="color:#2563eb;"></i>
            </a>

            <a href="#smps-pembda-2" class="smart-unit-item">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:10px; background:#ecfdf5; color:#15803d; display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                    <div>
                        <div style="font-size:14.5px; font-weight:800; color:#0f172a;">SMPS PEMBDA 2</div>
                        <div style="font-size:11.5px; color:#64748b;">Karakter Unggul &amp; Disiplin</div>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right" style="color:#059669;"></i>
            </a>

            <a href="#smks-pembda-nias" class="smart-unit-item">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:10px; background:#fffbeb; color:#b45309; display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <i class="fa-solid fa-gears"></i>
                    </div>
                    <div>
                        <div style="font-size:14.5px; font-weight:800; color:#0f172a;">SMKS PEMBDA NIAS</div>
                        <div style="font-size:11.5px; color:#64748b;">5 Kejuruan Vokasi DUDI</div>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right" style="color:#d97706;"></i>
            </a>
        </div>

        {{-- Ecosystem Live Metrics Strip --}}
        <div class="smart-trust-strip" data-aos="fade-up" data-aos-delay="200">
            <div>
                <div class="smart-trust-num" style="color:#2563eb;" data-count="{{ $totalStudents }}">{{ number_format($totalStudents, 0, ',', '.') }}</div>
                <div class="smart-trust-label">Siswa Aktif Terdaftar</div>
            </div>
            <div>
                <div class="smart-trust-num" style="color:#059669;" data-count="{{ $totalTeachers }}">{{ $totalTeachers }}</div>
                <div class="smart-trust-label">Tenaga Pendidik &amp; Staf</div>
            </div>
            <div>
                <div class="smart-trust-num" style="color:#d97706;">3 Unit</div>
                <div class="smart-trust-label">SMA, SMP &amp; SMK Terpadu</div>
            </div>
            <div>
                <div class="smart-trust-num" style="color:#7c3aed;">100%</div>
                <div class="smart-trust-label">Cloud Presensi &amp; CBT</div>
            </div>
        </div>

    </div>
</section>
