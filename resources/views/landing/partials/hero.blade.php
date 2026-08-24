{{-- HERO SECTION — Exact Khan Academy Deep Navy & Interactive Portal Style --}}
<style>
    .khan-hero-container {
        position: relative;
        background: #0b2149;
        color: #ffffff;
        padding-top: 130px;
        padding-bottom: 90px;
        overflow: hidden;
    }

    .khan-hero-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 56px;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    @media (max-width: 1024px) {
        .khan-hero-grid {
            grid-template-columns: 1fr;
            gap: 48px;
        }
    }

    /* Left Narrative */
    .khan-hero-headline {
        font-family: 'Merriweather', Georgia, serif;
        font-size: clamp(34px, 4.2vw, 54px);
        font-weight: 700;
        line-height: 1.18;
        color: #ffffff;
        margin-bottom: 22px;
        letter-spacing: -0.01em;
    }

    .khan-hero-quote {
        font-size: 19px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 18px;
        line-height: 1.5;
    }

    .khan-hero-desc {
        font-size: 16px;
        line-height: 1.75;
        color: #d1d5db;
        margin-bottom: 20px;
        max-width: 600px;
    }

    .khan-hero-desc strong {
        color: #ffffff;
    }

    .khan-hero-author {
        font-size: 14.5px;
        color: #93c5fd;
        font-weight: 600;
        margin-bottom: 36px;
    }

    /* Artistic Profile Showcase with Stars */
    .khan-artistic-showcase {
        position: relative;
        display: inline-flex;
        align-items: center;
        margin-top: 10px;
    }

    .khan-circle-photo {
        width: 170px;
        height: 170px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ffffff;
        background: #1e3a8a;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        position: relative;
        z-index: 2;
    }

    /* Decorative Artistic Stickers & Stars (Khan Academy Signature) */
    .khan-star-sticker {
        position: absolute;
        top: -16px;
        right: -30px;
        color: #fef08a;
        font-size: 52px;
        z-index: 3;
        animation: spin-slow 20s linear infinite;
    }

    .khan-sparkle-1 {
        position: absolute;
        bottom: 20px;
        left: -32px;
        color: #93c5fd;
        font-size: 28px;
        z-index: 3;
    }

    .khan-sparkle-2 {
        position: absolute;
        bottom: -10px;
        right: 10px;
        width: 16px;
        height: 16px;
        background: #fde047;
        border-radius: 50%;
        z-index: 3;
    }

    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Right Interactive Widget Card (Exact Khan Academy UI) */
    .khan-widget-card {
        background: transparent;
        position: relative;
        z-index: 2;
    }

    .khan-widget-label {
        font-size: 14.5px;
        font-weight: 600;
        color: #ffffff;
        margin-bottom: 12px;
        display: block;
    }

    /* Segmented Frequency / Role Switcher */
    .khan-segmented-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }

    .khan-segment-btn {
        width: 100%;
        padding: 13px 20px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 700;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.4);
        background: transparent;
        color: #ffffff;
        text-decoration: none;
        display: block;
    }

    .khan-segment-btn.active {
        background: #ffffff;
        color: #1865f2;
        border-color: #ffffff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
    }

    .khan-segment-btn:hover:not(.active) {
        border-color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
    }

    /* Radio Frequency / Semester */
    .khan-radio-group {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 24px;
    }

    .khan-radio-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #ffffff;
        cursor: pointer;
    }

    .khan-radio-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #1865f2;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #0b2149;
    }

    .khan-radio-dot.active::after {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #1865f2;
    }

    /* Unit Chips (Exact $12, $20, $35, $75, Other Grid) */
    .khan-chips-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 22px;
    }

    .khan-chip-btn {
        padding: 12px 6px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        background: transparent;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 800;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: block;
    }

    .khan-chip-btn.active {
        background: #ffffff;
        color: #1865f2;
        border-color: #ffffff;
    }

    .khan-chip-btn:hover:not(.active) {
        border-color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
    }

    /* Big Khan Academy Purple/Blue CTA Button */
    .khan-btn-give-now {
        width: 100%;
        padding: 15px;
        border-radius: 8px;
        background: #5865f2;
        color: #ffffff;
        font-size: 16px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 4px 18px rgba(88, 101, 242, 0.4);
    }

    .khan-btn-give-now:hover {
        background: #4752c4;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(88, 101, 242, 0.55);
    }

    .khan-disclaimer-text {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 14px;
        text-align: center;
        line-height: 1.5;
    }

    .khan-disclaimer-text a {
        color: #ffffff;
        text-decoration: underline;
    }
</style>

<section id="beranda" class="khan-hero-container">
    <div class="fw">
        <div class="khan-hero-grid">
            
            {{-- LEFT COLUMN: Khan Academy Authentic Narrative & Quote --}}
            <div data-aos="fade-right">
                
                <h1 class="khan-hero-headline">
                    Pendidikan Berkualitas untuk Generasi Masa Depan.
                </h1>

                <div class="khan-hero-quote">
                    “Membina karakter, mengasah keunggulan akademik, dan membekali kecakapan teknologi masa depan.”
                </div>

                <p class="khan-hero-desc">
                    Dengan ekosistem digital terpadu, <strong>PembdaHUB</strong> menghubungkan <strong>{{ number_format($totalStudents, 0, ',', '.') }}+ siswa</strong>, <strong>{{ $totalTeachers }} tenaga pendidik</strong>, dan orang tua di <strong>SMAS Pembda 1</strong>, <strong>SMPS Pembda 2</strong>, dan <strong>SMKS Pembda Nias</strong> dalam satu platform pintar.
                </p>

                <div class="khan-hero-author">
                    — Yayasan Perguruan PEMBDA Nias (Didirikan Sejak 1970)
                </div>

                {{-- Artistic Photo Circle with Khan Academy Star Stickers --}}
                <div class="khan-artistic-showcase">
                    <img src="{{ asset('images/photo-profile.jpeg') }}" class="khan-circle-photo" alt="Perguruan PEMBDA Nias"
                         onerror="this.onerror=null; this.src='{{ asset('images/logo-pembda.png') }}';">
                    
                    {{-- Playful Decorative Star Stickers --}}
                    <div class="khan-star-sticker">★</div>
                    <div class="khan-sparkle-1">✦</div>
                    <div class="khan-sparkle-2"></div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Exact Khan Academy Interactive Portal Widget Card --}}
            <div data-aos="fade-left" data-aos-delay="100">
                <div class="khan-widget-card" x-data="{ role: 'siswa', unit: 'smas' }">
                    
                    <span class="khan-widget-label">Pilih Jenis Akses Portal:</span>

                    {{-- Segmented Controls (One time vs Recurring style) --}}
                    <div class="khan-segmented-group">
                        <button type="button" class="khan-segment-btn" :class="role === 'siswa' ? 'active' : ''" @click="role = 'siswa'">
                            🎓 Siswa &amp; Tenaga Pendidik
                        </button>
                        <button type="button" class="khan-segment-btn" :class="role === 'tamu' ? 'active' : ''" @click="role = 'tamu'">
                            👨‍👩‍👧 Orang Tua &amp; Pendaftar PSB
                        </button>
                    </div>

                    {{-- Radio Indicator: Active Semester --}}
                    <div class="khan-radio-group">
                        <label class="khan-radio-item" @click="role = 'siswa'">
                            <span class="khan-radio-dot active"></span>
                            <span>TP. 2026/2027 Ganjil</span>
                        </label>
                        <label class="khan-radio-item" @click="role = 'siswa'">
                            <span class="khan-radio-dot"></span>
                            <span>KBM &amp; Presensi Aktif</span>
                        </label>
                    </div>

                    {{-- Unit Selector Chips ($12, $20, $35, $75 style) --}}
                    <span class="khan-widget-label">Pilih Unit Sekolah:</span>
                    <div class="khan-chips-grid">
                        <button type="button" class="khan-chip-btn" :class="unit === 'smas' ? 'active' : ''" @click="unit = 'smas'">
                            SMAS 1
                        </button>
                        <button type="button" class="khan-chip-btn" :class="unit === 'smps' ? 'active' : ''" @click="unit = 'smps'">
                            SMPS 2
                        </button>
                        <button type="button" class="khan-chip-btn" :class="unit === 'smks' ? 'active' : ''" @click="unit = 'smks'">
                            SMKS
                        </button>
                        <button type="button" class="khan-chip-btn" :class="unit === 'semua' ? 'active' : ''" @click="unit = 'semua'">
                            Lainnya
                        </button>
                    </div>

                    {{-- Big Solid Royal Blue CTA Button (Give now style) --}}
                    <template x-if="role === 'siswa'">
                        <a href="{{ route('login') }}" class="khan-btn-give-now">
                            <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal PembdaHUB
                        </a>
                    </template>
                    <template x-if="role === 'tamu'">
                        @if(isset($activeWave) && $activeWave)
                            <a href="{{ route('public.registration.index') }}" class="khan-btn-give-now">
                                <i class="fa-solid fa-user-plus"></i> Daftar Siswa Baru (PSB)
                            </a>
                        @else
                            <a href="{{ route('public.registration.check') }}" class="khan-btn-give-now">
                                <i class="fa-solid fa-magnifying-glass"></i> Cek Status Pendaftaran PSB
                            </a>
                        @endif
                    </template>

                    {{-- Subtext Terms & Privacy --}}
                    <p class="khan-disclaimer-text">
                        Dengan masuk, Anda terhubung ke sistem terpadu <a href="#profil">Perguruan PEMBDA Nias</a> dan menyetujui <a href="#kontak">ketentuan layanan</a>.
                    </p>

                </div>
            </div>

        </div>
    </div>
</section>
