{{-- UNIT SEKOLAH — Khan Academy Vibrant 3-Color Card Style --}}
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
        min-height: 420px;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .khan-unit-card:hover {
        transform: translateY(-8px);
    }

    .khan-unit-role {
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(15, 23, 42, 0.8);
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
        color: rgba(15, 23, 42, 0.88);
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

<section id="sekolah" class="section" style="background:#ffffff;">
    <div class="fw">
        
        {{-- Section Header --}}
        <div style="text-align:center; margin-bottom:56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center;">
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

        {{-- 3 Solid-Vibrant Khan Academy Cards --}}
        <div class="khan-units-grid" data-aos="fade-up" data-aos-delay="100">
            
            {{-- CARD 1: SMAS PEMBDA 1 (HIJAU RESMI) --}}
            <div class="khan-unit-card" style="background:#34d399; box-shadow:0 16px 36px -8px rgba(16,185,129,0.32);" onmouseover="this.style.boxShadow='0 24px 50px -8px rgba(16,185,129,0.45)'" onmouseout="this.style.boxShadow='0 16px 36px -8px rgba(16,185,129,0.32)'">
                <div>
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
                <div>
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
                <div>
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
