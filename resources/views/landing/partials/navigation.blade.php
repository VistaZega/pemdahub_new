{{-- NAVIGATION — Prestigious Campus & Modern School Style --}}
<style>
    .campus-navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
        height: 74px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .campus-navbar.scrolled {
        height: 66px;
        background: #ffffff;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
        border-bottom-color: #cbd5e1;
    }

    .campus-nav-inner {
        width: 100%;
        max-width: 1680px;
        margin: 0 auto;
        padding: 0 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    @media (max-width: 768px) {
        .campus-nav-inner {
            padding: 0 20px;
        }
    }

    /* Brand Logo */
    .campus-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
    }

    .campus-brand-logo {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        padding: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .campus-brand-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .campus-brand-info {
        display: flex;
        flex-direction: column;
    }

    .campus-brand-name {
        font-size: 17px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .campus-brand-name span {
        color: #2563eb;
    }

    .campus-brand-sub {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.02em;
    }

    /* Center Nav Links */
    .campus-nav-links {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .campus-nav-link {
        font-size: 14.5px;
        font-weight: 700;
        color: #334155;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .campus-nav-link:hover {
        color: #2563eb;
        background: #eff6ff;
    }

    /* Right Action Button */
    .campus-nav-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .campus-btn-login {
        background: #1e3a8a;
        color: #ffffff !important;
        font-size: 14px;
        font-weight: 800;
        padding: 10px 22px;
        border-radius: 12px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);
        transition: all 0.25s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .campus-btn-login:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.35);
    }

    .campus-mobile-btn {
        display: none;
        background: none;
        border: none;
        font-size: 22px;
        color: #0f172a;
        cursor: pointer;
        padding: 6px;
    }

    @media (max-width: 1024px) {
        .campus-nav-links {
            display: none;
        }
        .campus-mobile-btn {
            display: block;
        }
    }
</style>

<nav class="campus-navbar" id="navbar">
    <div class="campus-nav-inner">
        
        {{-- Brand Logo --}}
        <a href="{{ route('home') }}" class="campus-brand">
            <div class="campus-brand-logo">
                <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Perguruan PEMBDA Nias"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fa-solid fa-graduation-cap\' style=\'color:#1e3a8a; font-size:20px;\'></i>';">
            </div>
            <div class="campus-brand-info">
                <div class="campus-brand-name">Perguruan <span>PEMBDA</span> Nias</div>
                <div class="campus-brand-sub">Yayasan Pendidikan Sejak 1970 • 3 Unit Sekolah</div>
            </div>
        </a>

        {{-- Navigation Menu --}}
        <div class="campus-nav-links">
            <a href="#beranda" class="campus-nav-link">Beranda</a>
            <a href="#sekolah" class="campus-nav-link">3 Unit Sekolah</a>
            <a href="#features" class="campus-nav-link">Keunggulan KBM</a>
            <a href="#prestasi" class="campus-nav-link">Prestasi Siswa</a>
            <a href="#berita" class="campus-nav-link">Berita &amp; Artikel</a>
            <a href="#kontak" class="campus-nav-link">Hubungi Kami</a>
        </div>

        {{-- Right CTA Actions --}}
        <div class="campus-nav-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="campus-btn-login">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            @else
                <a href="{{ route('public.registration.check') }}" class="campus-nav-link" style="color:#64748b; font-size:13.5px;" title="Cek Status Pendaftaran">
                    Cek PSB
                </a>
                <a href="{{ route('login') }}" class="campus-btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk Portal
                </a>
            @endauth
            <button class="campus-mobile-btn" id="mobile-menu-btn">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

    </div>
</nav>

{{-- Mobile Menu Overlay --}}
<div class="mobile-overlay" id="mobile-overlay">
    <button class="mobile-close" id="mobile-close"><i class="fa-solid fa-xmark"></i></button>
    <a href="#beranda">Beranda</a>
    <a href="#sekolah">3 Unit Sekolah</a>
    <a href="#features">Keunggulan KBM</a>
    <a href="#prestasi">Prestasi Siswa</a>
    <a href="#berita">Berita &amp; Artikel</a>
    <a href="#kontak">Hubungi Kami</a>
    <div style="margin-top:24px; display:flex; flex-direction:column; align-items:center; gap:12px;">
        @auth
            <a href="{{ route('dashboard') }}" class="campus-btn-login" style="padding:12px 32px; font-size:16px;">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="campus-btn-login" style="padding:12px 32px; font-size:16px;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Portal
            </a>
        @endauth
    </div>
</div>
