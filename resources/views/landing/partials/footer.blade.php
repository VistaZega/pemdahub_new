{{-- FOOTER — Bento/Apple Style --}}
<footer id="kontak" class="footer" style="padding-top:56px;">
    <div class="fw">
        <div class="footer-grid">
            {{-- Col 1: Brand --}}
            <div>
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo" style="width:100%; height:100%; object-fit:contain; padding:4px;"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fa-solid fa-graduation-cap\' style=\'color:#94a3b8; font-size:16px;\'></i>';">
                    </div>
                    <div>
                        <div class="footer-brand-name">Pembda<span>HUB</span></div>
                        <div class="footer-brand-sub">Smart School Management</div>
                    </div>
                </div>
                <p class="footer-desc">
                    Yayasan Perguruan Pembangunan Daerah Nias — berdiri sejak 1970, membangun generasi muda berkualitas melalui pendidikan bermutu di Gunungsitoli.
                </p>
                <div class="footer-socials">
                    <a href="#" class="footer-social-btn" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="footer-social-btn" aria-label="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="#" class="footer-social-btn" aria-label="YouTube">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
            </div>

            {{-- Col 2: Links --}}
            <div>
                <h4 class="footer-heading">Layanan & Fitur</h4>
                <div class="footer-link-list">
                    <a href="#beranda">Beranda</a>
                    <a href="#features">Modul Sistem</a>
                    <a href="{{ route('forum.index') }}"><i class="fa-solid fa-comments" style="color:var(--indigo-light); font-size:12px; margin-right:4px;"></i> Forum Akademik</a>
                    <a href="{{ route('alumni.tracer.form') }}"><i class="fa-solid fa-briefcase" style="color:var(--emerald); font-size:12px; margin-right:4px;"></i> Tracer Study Alumni</a>
                    <a href="{{ route('ika.register') }}"><i class="fa-solid fa-users" style="color:var(--violet); font-size:12px; margin-right:4px;"></i> Pendaftaran IKA PEMBDA</a>
                    <a href="{{ route('alumni.jobs.index') }}"><i class="fa-solid fa-bullhorn" style="color:var(--cyan); font-size:12px; margin-right:4px;"></i> Lowongan Kerja</a>
                    <a href="{{ route('login') }}"><i class="fa-solid fa-graduation-cap" style="color:var(--violet); font-size:12px; margin-right:4px;"></i> Tugas Akhir Siswa</a>
                    <a href="#pelatihan"><i class="fa-solid fa-book-open" style="color:var(--cyan); font-size:12px; margin-right:4px;"></i> Modul Pelatihan</a>
                    <a href="{{ asset('MANUAL_BOOK_PEMBDAHUB.pdf') }}" target="_blank" class="footer-manual-link"><i class="fa-solid fa-file-pdf"></i> Manual Book PDF</a>
                </div>
            </div>

            {{-- Col 3: Schools --}}
            <div>
                <h4 class="footer-heading">Unit Sekolah</h4>
                <div class="footer-school-list">
                    <div class="footer-school-item">
                        <div class="footer-school-name">SMAS Pembda 1</div>
                        <div class="footer-school-loc">Gunungsitoli</div>
                    </div>
                    <div class="footer-school-item">
                        <div class="footer-school-name">SMPS Pembda 2</div>
                        <div class="footer-school-loc">Gunungsitoli</div>
                    </div>
                    <div class="footer-school-item">
                        <div class="footer-school-name">SMKS Pembda Nias</div>
                        <div class="footer-school-loc">5 Program Keahlian</div>
                    </div>
                </div>
            </div>

            {{-- Col 4: Contact --}}
            <div>
                <h4 class="footer-heading">Kontak</h4>
                <div class="footer-contact-list">
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-location-dot" style="color:var(--amber); margin-top:3px;"></i>
                        <span>
                            Jl. Pelita No.09 Kelurahan Ilir<br>
                            Kec. Gunungsitoli<br>
                            Kota Gunungsitoli (22815)
                        </span>
                    </div>
                    <div class="footer-contact-item" style="align-items:center;">
                        <i class="fa-solid fa-envelope" style="color:var(--amber);"></i>
                        <a href="mailto:perguruanpembdanias@gmail.com">perguruanpembdanias@gmail.com</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Yayasan Perguruan PEMBDA Nias. Berdiri sejak 1970.</p>
            <p class="footer-powered">
                Powered by <strong>Pembda<span>HUB</span></strong>
            </p>
        </div>
    </div>
</footer>

<style>
    /* Footer Grid */
    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 40px;
        padding-bottom: 40px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    /* Brand block */
    .footer-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .footer-logo {
        width: 40px;
        height: 40px;
        background: #1e293b;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .footer-brand-name {
        font-size: 16px;
        font-weight: 800;
        color: #f1f5f9;
    }
    .footer-brand-name span {
        color: #ef4444;
    }
    .footer-brand-sub {
        font-size: 11px;
        color: #475569;
    }

    /* Description */
    .footer-desc {
        font-size: 14px;
        line-height: 1.7;
        color: #64748b;
        margin-bottom: 20px;
    }

    /* Social buttons */
    .footer-socials {
        display: flex;
        gap: 8px;
    }
    .footer-social-btn {
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    .footer-social-btn:hover {
        background: rgba(255,255,255,0.12);
        color: var(--gold-bright) !important;
        transform: translateY(-2px);
    }

    /* Headings */
    .footer-heading {
        font-size: 12px;
        font-weight: 700;
        color: #e2e8f0;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Link list */
    .footer-link-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .footer-link-list a {
        font-size: 14px;
    }
    .footer-manual-link {
        color: var(--gold-bright) !important;
        font-weight: 600;
    }

    /* School list */
    .footer-school-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .footer-school-name {
        font-size: 14px;
        color: #cbd5e1;
        font-weight: 600;
    }
    .footer-school-loc {
        font-size: 12px;
        color: #475569;
    }

    /* Contact list */
    .footer-contact-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .footer-contact-item {
        display: flex;
        gap: 10px;
    }
    .footer-contact-item i {
        font-size: 13px;
    }
    .footer-contact-item span,
    .footer-contact-item a {
        font-size: 13px;
        line-height: 1.5;
    }

    /* Bottom bar */
    .footer-bottom {
        padding: 20px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .footer-bottom p {
        font-size: 13px;
        color: #475569;
    }
    .footer-powered {
        font-size: 12px !important;
    }
    .footer-powered strong {
        color: #94a3b8;
    }
    .footer-powered span {
        color: #fbbf24;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .footer-grid {
            grid-template-columns: 1fr !important;
            gap: 32px !important;
        }
    }
</style>
