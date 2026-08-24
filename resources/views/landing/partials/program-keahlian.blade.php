{{-- PROGRAM KEAHLIAN — Modern, Clean & Non-Rigid Bento Design --}}
<style>
    .program-section {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 85px 0 95px 0;
        position: relative;
    }

    .program-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    @media (max-width: 1200px) {
        .program-grid-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 640px) {
        .program-grid-4 {
            grid-template-columns: 1fr;
            gap: 18px;
        }
    }

    .program-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 24px;
        padding: 28px 24px;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.05);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .program-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.12);
        border-color: #cbd5e1;
    }

    .program-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .program-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .program-title {
        font-size: 18px;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -0.02em;
    }

    .program-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 6px;
        display: inline-block;
    }

    .subprogram-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }

    .subprogram-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        transition: all 0.2s ease;
    }

    .subprogram-item:hover {
        background: #ffffff;
        border-color: #93c5fd;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.06);
    }

    .subprogram-name {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .subprogram-desc {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        line-height: 1.5;
    }

    .program-card-footer {
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 800;
    }
</style>

<section id="program" class="program-section">
    <div class="fw">
        
        {{-- Section Header --}}
        <div style="text-align: center; max-width: 800px; margin: 0 auto 56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content: center; margin-bottom: 14px;">
                <div class="section-label-dot" style="background: #d97706;"></div>
                <span class="section-label-text" style="color: #b45309;">Pendidikan Vokasi Terpadu</span>
            </div>
            
            <h2 class="h1" style="font-size: clamp(30px, 4vw, 44px); font-weight: 900; color: #0f172a; letter-spacing: -0.025em; margin-bottom: 14px;">
                Konsentrasi Keahlian <span style="background: linear-gradient(135deg, #d97706, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">SMKS Pembda Nias</span>
            </h2>
            
            <p class="body-lg" style="color: #475569; max-width: 700px; margin: 0 auto;">
                Mencetak lulusan terampil, bersertifikasi keahlian, siap kerja langsung di dunia industri (DUDI), dan berdaya saing global di bidang rekayasa teknik dan teknologi informasi.
            </p>
        </div>

        {{-- 4 Modern Bento Program Cards --}}
        <div class="program-grid-4" data-aos="fade-up" data-aos-delay="100">
            
            {{-- 1. TEKNIK OTOMOTIF --}}
            <div class="program-card">
                <div>
                    <div class="program-card-header">
                        <div class="program-icon-box" style="background: #fff1f2; color: #e11d48;">
                            <i class="fa-solid fa-car-side"></i>
                        </div>
                        <div>
                            <h3 class="program-title">Teknik Otomotif</h3>
                            <span class="program-badge" style="background: #ffe4e6; color: #be123c;">2 Konsentrasi</span>
                        </div>
                    </div>

                    <div class="subprogram-list">
                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-truck-pickup" style="color: #e11d48; font-size: 13px;"></i>
                                <span>Teknik Kendaraan Ringan (TKR)</span>
                            </div>
                            <div class="subprogram-desc">Perawatan, overhaul mesin, sistem kelistrikan, &amp; diagnostik mobil roda empat modern.</div>
                        </div>

                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-motorcycle" style="color: #e11d48; font-size: 13px;"></i>
                                <span>Teknik Sepeda Motor (TBSM)</span>
                            </div>
                            <div class="subprogram-desc">Servis berkala, sistem transmisi otomatis, &amp; teknologi injeksi motor terkini.</div>
                        </div>
                    </div>
                </div>

                <div class="program-card-footer">
                    <span style="color: #059669;"><i class="fa-solid fa-circle-check"></i> Bengkel Praktik Standar</span>
                    <span style="color: #e11d48;">Siap Kerja &rarr;</span>
                </div>
            </div>

            {{-- 2. TEKNIK KOMPUTER & INFORMATIKA --}}
            <div class="program-card">
                <div>
                    <div class="program-card-header">
                        <div class="program-icon-box" style="background: #eff6ff; color: #2563eb;">
                            <i class="fa-solid fa-laptop-code"></i>
                        </div>
                        <div>
                            <h3 class="program-title">Teknologi Informasi</h3>
                            <span class="program-badge" style="background: #dbeafe; color: #1d4ed8;">2 Konsentrasi</span>
                        </div>
                    </div>

                    <div class="subprogram-list">
                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-code" style="color: #2563eb; font-size: 13px;"></i>
                                <span>Rekayasa Perangkat Lunak (RPL)</span>
                            </div>
                            <div class="subprogram-desc">Pemrograman web modern, aplikasi mobile, manajemen basis data, &amp; UI/UX design.</div>
                        </div>

                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-network-wired" style="color: #2563eb; font-size: 13px;"></i>
                                <span>Teknik Komputer &amp; Jaringan (TKJ)</span>
                            </div>
                            <div class="subprogram-desc">Administrasi server, instalasi jaringan fiber optic, routing, &amp; keamanan siber.</div>
                        </div>
                    </div>
                </div>

                <div class="program-card-footer">
                    <span style="color: #059669;"><i class="fa-solid fa-circle-check"></i> Lab Komputer Canggih</span>
                    <span style="color: #2563eb;">Digital Talent &rarr;</span>
                </div>
            </div>

            {{-- 3. TEKNIK ALAT BERAT & ELEKTRONIKA --}}
            <div class="program-card">
                <div>
                    <div class="program-card-header">
                        <div class="program-icon-box" style="background: #f5f3ff; color: #7c3aed;">
                            <i class="fa-solid fa-gears"></i>
                        </div>
                        <div>
                            <h3 class="program-title">Alat Berat &amp; Audio Video</h3>
                            <span class="program-badge" style="background: #ede9fe; color: #6d28d9;">Vokasi Khusus</span>
                        </div>
                    </div>

                    <div class="subprogram-list">
                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-tractor" style="color: #7c3aed; font-size: 13px;"></i>
                                <span>Teknik Alat Berat (TAB)</span>
                            </div>
                            <div class="subprogram-desc">Pemeliharaan sistem hidrolik, diesel industri, transmisi, &amp; alat berat konstruksi.</div>
                        </div>

                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-volume-high" style="color: #7c3aed; font-size: 13px;"></i>
                                <span>Teknik Audio Video (TAV)</span>
                            </div>
                            <div class="subprogram-desc">Instalasi, kalibrasi sistem elektro-akustik, &amp; pemeliharaan perangkat penyiaran digital.</div>
                        </div>
                    </div>
                </div>

                <div class="program-card-footer">
                    <span style="color: #059669;"><i class="fa-solid fa-circle-check"></i> Praktik Terpadu</span>
                    <span style="color: #7c3aed;">Ahli Mesin &rarr;</span>
                </div>
            </div>

            {{-- 4. TEKNIK KONSTRUKSI & DPIB --}}
            <div class="program-card">
                <div>
                    <div class="program-card-header">
                        <div class="program-icon-box" style="background: #fffbeb; color: #d97706;">
                            <i class="fa-solid fa-compass-drafting"></i>
                        </div>
                        <div>
                            <h3 class="program-title">Konstruksi &amp; Desain</h3>
                            <span class="program-badge" style="background: #fef3c7; color: #b45309;">Arsitektur CAD</span>
                        </div>
                    </div>

                    <div class="subprogram-list">
                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-building" style="color: #d97706; font-size: 13px;"></i>
                                <span>Desain Pemodelan Bangunan (DPIB)</span>
                            </div>
                            <div class="subprogram-desc">Perancangan arsitektur digital, gambar teknik CAD 2D/3D, BIM, &amp; perhitungan RAB gedung.</div>
                        </div>

                        <div class="subprogram-item">
                            <div class="subprogram-name">
                                <i class="fa-solid fa-helmet-safety" style="color: #d97706; font-size: 13px;"></i>
                                <span>Manajemen Konstruksi Lapangan</span>
                            </div>
                            <div class="subprogram-desc">Penerapan K3 konstruksi, pengawasan struktur, serta estimasi material bangunan.</div>
                        </div>
                    </div>
                </div>

                <div class="program-card-footer">
                    <span style="color: #059669;"><i class="fa-solid fa-circle-check"></i> Studio Desain CAD</span>
                    <span style="color: #d97706;">Arsitek Muda &rarr;</span>
                </div>
            </div>

        </div>

    </div>
</section>
