{{-- PLATFORM OVERVIEW — Bold Indigo Theme --}}
<style>
    .pillar-icon {
        margin: 0 auto 20px;
        width: 60px;
        height: 60px;
        border-radius: 18px;
        font-size: 26px;
    }
    .marquee-fade-left,
    .marquee-fade-right {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 80px;
        z-index: 2;
    }
    .marquee-fade-left {
        left: 0;
        background: linear-gradient(90deg, var(--bg-card, #ffffff), transparent);
    }
    .marquee-fade-right {
        right: 0;
        background: linear-gradient(-90deg, var(--bg-card, #ffffff), transparent);
    }
</style>

<section id="platform" class="section" style="background: var(--bg-card, #ffffff);">
    <div class="fw">
        <div style="max-width:900px; margin:0 auto; text-align:center;" data-aos="fade-up">
            {{-- Subtle intro text --}}
            <div style="display:inline-flex; align-items:center; gap:10px; background:linear-gradient(135deg, var(--indigo-bg), var(--violet-bg)); padding:10px 24px; border-radius:100px; margin-bottom:32px; border:1px solid rgba(99,102,241,0.2);">
                <i class="fa-solid fa-globe" style="color:var(--indigo-light); font-size:14px;"></i>
                <span style="font-size:13px; font-weight:600; color:var(--indigo);">perguruanpembda.com</span>
            </div>

            <h2 class="h2" style="margin-bottom:16px; line-height:1.3; color:var(--text-primary);">
                Lebih dari Sekadar Website —<br>
                <span style="background:linear-gradient(135deg, var(--indigo), var(--indigo-light)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Ekosistem Digital</span> Pengelolaan Pendidikan
            </h2>

            <p class="body-lg" style="max-width:720px; margin:0 auto 40px; line-height:1.8;">
                <strong style="color:var(--text-primary);">perguruanpembda.com</strong> bukan hanya profil Yayasan Perguruan PEMBDA Nias, tetapi juga platform <strong style="color:var(--text-primary);">PembdaHUB</strong> — sebuah aplikasi terintegrasi untuk pengelolaan pendidikan di seluruh unit sekolah, yang dapat diakses oleh siswa, guru, dan orang tua secara langsung.
            </p>
        </div>

        {{-- Three Pillars --}}
        <div class="bento bento-3" style="max-width:960px; margin:0 auto;" data-aos="fade-up" data-aos-delay="100">
            {{-- Pillar 1: Administrasi --}}
            <div class="bcard" style="text-align:center; padding:36px 28px;">
                <div class="icon-circle pillar-icon" style="background:var(--blue-bg); color:var(--blue);">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px;">Administrasi &amp; Kepegawaian</h3>
                <p class="body" style="font-size:13px;">Data pegawai, jabatan, surat-menyurat, dan pengelolaan SDM terintegrasi.</p>
            </div>

            {{-- Pillar 2: Keuangan --}}
            <div class="bcard" style="text-align:center; padding:36px 28px;">
                <div class="icon-circle pillar-icon" style="background:var(--emerald-bg); color:var(--emerald);">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px;">Keuangan</h3>
                <p class="body" style="font-size:13px;">Pembayaran SPP, tagihan digital, laporan keuangan, dan rekap otomatis.</p>
            </div>

            {{-- Pillar 3: Akademik --}}
            <div class="bcard" style="text-align:center; padding:36px 28px;">
                <div class="icon-circle pillar-icon" style="background:var(--violet-bg); color:var(--violet);">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px;">Akademik</h3>
                <p class="body" style="font-size:13px;">Pembelajaran, penilaian, penjadwalan, dan monitoring perkembangan siswa.</p>
            </div>
        </div>

        {{-- Feature keyword marquee --}}
        <div data-aos="fade-up" data-aos-delay="200" style="margin-top:48px; overflow:hidden; position:relative;">
            <div class="marquee-fade-left"></div>
            <div class="marquee-fade-right"></div>
            <div class="marquee-track">
                <div class="marquee-content">
                    <span class="marquee-item"><i class="fa-solid fa-id-card-clip"></i> Absensi RFID</span>
                    <span class="marquee-item"><i class="fa-solid fa-book-open-reader"></i> LMS</span>
                    <span class="marquee-item"><i class="fa-solid fa-laptop-code"></i> CBT</span>
                    <span class="marquee-item"><i class="fa-solid fa-comments"></i> Forum Diskusi</span>
                    <span class="marquee-item"><i class="fa-solid fa-briefcase"></i> PKL &amp; Alumni</span>
                    <span class="marquee-item"><i class="fa-solid fa-file-invoice"></i> Tugas Akhir</span>
                    <span class="marquee-item"><i class="fa-solid fa-star"></i> Reputasi &amp; Leaderboard</span>
                    <span class="marquee-item"><i class="fa-solid fa-money-check-dollar"></i> Pembayaran SPP</span>
                    <span class="marquee-item"><i class="fa-solid fa-calendar-days"></i> Penjadwalan</span>
                    <span class="marquee-item"><i class="fa-solid fa-shield-halved"></i> Parental Control</span>
                    <span class="marquee-item"><i class="fa-solid fa-chart-column"></i> Monitoring</span>
                    <span class="marquee-item"><i class="fa-solid fa-award"></i> Pembinaan</span>
                    <span class="marquee-item"><i class="fa-solid fa-user-gear"></i> Kepegawaian</span>
                    <span class="marquee-item"><i class="fa-solid fa-clipboard-list"></i> Perkembangan Siswa</span>
                    {{-- Duplicate for seamless loop --}}
                    <span class="marquee-item"><i class="fa-solid fa-id-card-clip"></i> Absensi RFID</span>
                    <span class="marquee-item"><i class="fa-solid fa-book-open-reader"></i> LMS</span>
                    <span class="marquee-item"><i class="fa-solid fa-laptop-code"></i> CBT</span>
                    <span class="marquee-item"><i class="fa-solid fa-comments"></i> Forum Diskusi</span>
                    <span class="marquee-item"><i class="fa-solid fa-briefcase"></i> PKL &amp; Alumni</span>
                    <span class="marquee-item"><i class="fa-solid fa-file-invoice"></i> Tugas Akhir</span>
                    <span class="marquee-item"><i class="fa-solid fa-star"></i> Reputasi &amp; Leaderboard</span>
                    <span class="marquee-item"><i class="fa-solid fa-money-check-dollar"></i> Pembayaran SPP</span>
                    <span class="marquee-item"><i class="fa-solid fa-calendar-days"></i> Penjadwalan</span>
                    <span class="marquee-item"><i class="fa-solid fa-shield-halved"></i> Parental Control</span>
                    <span class="marquee-item"><i class="fa-solid fa-chart-column"></i> Monitoring</span>
                    <span class="marquee-item"><i class="fa-solid fa-award"></i> Pembinaan</span>
                    <span class="marquee-item"><i class="fa-solid fa-user-gear"></i> Kepegawaian</span>
                    <span class="marquee-item"><i class="fa-solid fa-clipboard-list"></i> Perkembangan Siswa</span>
                </div>
            </div>
        </div>
    </div>
</section>
