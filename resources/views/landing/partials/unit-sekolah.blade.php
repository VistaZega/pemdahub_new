{{-- UNIT SEKOLAH — Khan Academy Vibrant Style with Custom Silhouettes (SMA Hijau, SMP Biru, SMK Coklat) --}}
<style>
    .khan-units-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
    }

    @media (max-width: 1024px) {
        .khan-units-grid {
            grid-template-columns: 1fr;
            max-width: 620px;
            margin-left: auto;
            margin-right: auto;
            gap: 24px;
        }
    }

    .khan-unit-card {
        border-radius: 24px;
        padding: 40px 32px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 430px;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .khan-unit-card:hover {
        transform: translateY(-8px);
    }

    /* Silhouette Artwork on Bottom Right */
    .unit-silhouette {
        position: absolute;
        right: -10px;
        bottom: -10px;
        width: 170px;
        height: 220px;
        opacity: 0.12;
        color: #0f172a;
        pointer-events: none;
        z-index: 0;
        transition: all 0.35s ease;
    }

    .khan-unit-card:hover .unit-silhouette {
        opacity: 0.22;
        transform: scale(1.06) translateY(-4px);
    }

    .khan-unit-content {
        position: relative;
        z-index: 1;
    }

    .khan-unit-role {
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(15, 23, 42, 0.85);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .khan-unit-headline {
        font-size: clamp(17.5px, 1.8vw, 19.5px);
        font-weight: 800;
        line-height: 1.55;
        color: #0f172a;
        margin-bottom: 22px;
    }

    .khan-unit-checklist {
        list-style: none;
        padding: 0;
        margin: 0 0 28px 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .khan-unit-checklist li {
        font-size: 13.5px;
        font-weight: 700;
        color: rgba(15, 23, 42, 0.9);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .khan-unit-bottom {
        border-top: 1.5px solid rgba(15, 23, 42, 0.15);
        padding-top: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        position: relative;
        z-index: 1;
    }

    .khan-unit-author {
        font-size: 14.5px;
        font-style: italic;
        font-weight: 800;
        color: #0f172a;
    }

    .khan-unit-stat-chip {
        background: rgba(15, 23, 42, 0.12);
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 100px;
    }
</style>

<section id="sekolah" class="section" style="background:#ffffff; padding: 85px 0 95px 0;">
    <div class="fw">
        
        {{-- Section Header --}}
        <div style="text-align:center; margin-bottom:56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center; margin-bottom: 14px;">
                <div class="section-label-dot" style="background:#1e3a8a;"></div>
                <span class="section-label-text" style="color:#1e3a8a;">Unit Sekolah Terpadu</span>
            </div>
            <h2 class="h1" style="margin-bottom:14px; font-size:clamp(30px, 4vw, 46px); font-weight:900; color:#0f172a; letter-spacing:-0.025em;">
                Pusat Keunggulan 3 Unit Sekolah.
            </h2>
            <p class="body-lg" style="max-width:720px; margin:0 auto; color:#475569;">
                Masing-masing unit sekolah binaan Yayasan Perguruan PEMBDA Nias memiliki fokus kurikulum, keunggulan karakter, dan fasilitas modern yang siap mengantar siswa menuju masa depan gemilang.
            </p>
        </div>

        {{-- 3 Solid-Vibrant Khan Academy Cards with Custom School Silhouettes --}}
        <div class="khan-units-grid" data-aos="fade-up" data-aos-delay="100">
            
            {{-- CARD 1: SMAS PEMBDA 1 (HIJAU RESMI) --}}
            <div class="khan-unit-card" style="background:#34d399; box-shadow:0 16px 36px -8px rgba(16,185,129,0.32);" onmouseover="this.style.boxShadow='0 24px 50px -8px rgba(16,185,129,0.45)'" onmouseout="this.style.boxShadow='0 16px 36px -8px rgba(16,185,129,0.32)'">
                
                {{-- Siluet Siswa SMA (Pelajar Akademik / Toga Riset) --}}
                <svg class="unit-silhouette" viewBox="0 0 200 240" fill="currentColor">
                    <!-- Graduation Cap & Graduate Student Silhouette -->
                    <path d="M100 20 L20 60 L100 100 L180 60 Z"/>
                    <path d="M180 60 L180 120 C180 125 175 130 170 130 C165 130 160 125 160 120 L160 70"/>
                    <circle cx="100" cy="115" r="28"/>
                    <path d="M50 165 C50 135 72 135 100 135 C128 135 150 135 150 165 L155 240 L45 240 Z"/>
                    <!-- Book / Diploma in Hand -->
                    <path d="M30 185 L70 170 L70 220 L30 235 Z" opacity="0.8"/>
                    <path d="M70 170 L110 185 L110 235 L70 220 Z" opacity="0.6"/>
                </svg>

                <div class="khan-unit-content">
                    <div class="khan-unit-role">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>SEKOLAH MENENGAH ATAS (SMA)</span>
                    </div>

                    <p class="khan-unit-headline">
                        “Keseimbangan sejati antara keunggulan akademik dan non-akademik, di mana setiap siswa dibimbing dan diasah secara terarah sesuai bakat, minat, serta potensi terbaik yang dimilikinya.”
                    </p>

                    <ul class="khan-unit-checklist">
                        <li><i class="fa-solid fa-circle-check"></i> Bimbingan Bakat &amp; Minat Akademik / Non-Akademik</li>
                        <li><i class="fa-solid fa-circle-check"></i> Budaya Riset &amp; Penelitian Siswa Terarah</li>
                        <li><i class="fa-solid fa-circle-check"></i> Pendampingan Sukses Lolos PTN &amp; Sekolah Kedinasan</li>
                    </ul>
                </div>

                <div class="khan-unit-bottom">
                    <div class="khan-unit-author">
                        — SMAS Pembda 1 Gunungsitoli
                    </div>
                    <div class="khan-unit-stat-chip">
                        Akreditasi A • Unggul
                    </div>
                </div>
            </div>

            {{-- CARD 2: SMPS PEMBDA 2 (BIRU RESMI) --}}
            <div class="khan-unit-card" style="background:#60a5fa; box-shadow:0 16px 36px -8px rgba(37,99,235,0.32);" onmouseover="this.style.boxShadow='0 24px 50px -8px rgba(37,99,235,0.45)'" onmouseout="this.style.boxShadow='0 16px 36px -8px rgba(37,99,235,0.32)'">
                
                {{-- Siluet Siswa SMP (Pelajar Muda Bersemangat dengan Ransel & Buku) --}}
                <svg class="unit-silhouette" viewBox="0 0 200 240" fill="currentColor">
                    <!-- Junior High Student Silhouette with Backpack & Young Dynamic Posture -->
                    <circle cx="100" cy="55" r="28"/>
                    <!-- Hairstyle / Head silhouette -->
                    <path d="M72 50 C72 25 128 25 128 50 C128 55 72 55 72 50 Z"/>
                    <!-- Torso & School Uniform -->
                    <path d="M58 100 C58 85 75 80 100 80 C125 80 142 85 142 100 L150 240 L50 240 Z"/>
                    <!-- Backpack on Shoulders -->
                    <path d="M35 105 C35 90 50 90 58 105 L55 175 C55 185 40 185 35 175 Z" opacity="0.75"/>
                    <path d="M142 105 C150 90 165 90 165 105 L165 175 C160 185 145 185 145 175 Z" opacity="0.75"/>
                    <!-- Reading Book -->
                    <path d="M80 130 L120 130 L130 165 L70 165 Z" opacity="0.85"/>
                </svg>

                <div class="khan-unit-content">
                    <div class="khan-unit-role">
                        <i class="fa-solid fa-seedling"></i>
                        <span>SEKOLAH MENENGAH PERTAMA (SMP)</span>
                    </div>

                    <p class="khan-unit-headline">
                        “Membangun fondasi karakter yang kokoh, kedisiplinan tinggi, serta keunggulan potensi akademik dan minat bakat siswa secara holistik menuju jenjang pendidikan terdepan.”
                    </p>

                    <ul class="khan-unit-checklist">
                        <li><i class="fa-solid fa-circle-check"></i> Pembinaan Karakter, Budi Pekerti &amp; Akhlak</li>
                        <li><i class="fa-solid fa-circle-check"></i> Literasi Digital &amp; Pengenalan Teknologi Cerdas</li>
                        <li><i class="fa-solid fa-circle-check"></i> Pembinaan Bakat Sains, Seni &amp; Olahraga</li>
                    </ul>
                </div>

                <div class="khan-unit-bottom">
                    <div class="khan-unit-author">
                        — SMPS Pembda 2 Gunungsitoli
                    </div>
                    <div class="khan-unit-stat-chip">
                        Karakter &amp; Prestasi
                    </div>
                </div>
            </div>

            {{-- CARD 3: SMKS PEMBDA NIAS (COKLAT RESMI) --}}
            <div class="khan-unit-card" style="background:#d97706; box-shadow:0 16px 36px -8px rgba(217,119,6,0.32);" onmouseover="this.style.boxShadow='0 24px 50px -8px rgba(217,119,6,0.45)'" onmouseout="this.style.boxShadow='0 16px 36px -8px rgba(217,119,6,0.32)'">
                
                {{-- Siluet Siswa SMK (Teknisi Vokasi dengan Helm Proyek / Safety & Roda Gigi) --}}
                <svg class="unit-silhouette" viewBox="0 0 200 240" fill="currentColor">
                    <!-- Safety Hardhat Helmet -->
                    <path d="M68 50 C68 25 132 25 132 50 L145 58 C145 62 55 62 55 58 Z"/>
                    <circle cx="100" cy="70" r="24"/>
                    <!-- Technical Worker Torso -->
                    <path d="M55 110 C55 90 75 88 100 88 C125 88 145 90 145 110 L155 240 L45 240 Z"/>
                    <!-- Industrial Gear / Wrench Silhouette Accent -->
                    <g transform="translate(110, 130) scale(0.65)" opacity="0.8">
                        <circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" stroke-width="12"/>
                        <path d="M45 10 L55 10 L55 90 L45 90 Z"/>
                        <path d="M10 45 L10 55 L90 55 L90 45 Z"/>
                        <path d="M20 20 L28 28 L80 80 L72 72 Z"/>
                        <path d="M80 20 L72 28 L20 80 L28 72 Z"/>
                    </g>
                </svg>

                <div class="khan-unit-content">
                    <div class="khan-unit-role">
                        <i class="fa-solid fa-gears"></i>
                        <span>SEKOLAH MENENGAH KEJURUAN (SMK)</span>
                    </div>

                    <p class="khan-unit-headline">
                        “Pusat Pendidikan Vokasi Modern dengan 5 Konsentrasi Keahlian yang mencetak lulusan terampil, kompeten, siap kerja industri (DUDI), dan berjiwa wirausaha tangguh.”
                    </p>

                    <ul class="khan-unit-checklist">
                        <li><i class="fa-solid fa-circle-check"></i> 5 Jurusan: RPL, TKR, TAB, TBSM, DPIB</li>
                        <li><i class="fa-solid fa-circle-check"></i> Bengkel Praktik &amp; Lab Komputer Standar DUDI</li>
                        <li><i class="fa-solid fa-circle-check"></i> Kemitraan Magang PKL Industri Nasional</li>
                    </ul>
                </div>

                <div class="khan-unit-bottom">
                    <div class="khan-unit-author">
                        — SMKS Pembda Nias
                    </div>
                    <div class="khan-unit-stat-chip">
                        5 Jurusan Vokasi
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
