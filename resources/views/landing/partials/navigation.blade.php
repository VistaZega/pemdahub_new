{{-- NAVIGATION — Bold Indigo Theme --}}
<nav class="navbar" id="navbar">
    <div class="fw">
        <div class="nav-inner">
            <a href="{{ route('home') }}" class="nav-brand">
                <div class="nav-logo">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo" style="width:100%; height:100%; object-fit:contain; padding:4px;"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fa-solid fa-graduation-cap\' style=\'color:#2563eb; font-size:18px;\'></i>';">
                </div>
                <div class="nav-brand-text">
                    <div class="nav-brand-name" style="color:#0f172a; font-weight:900;">Pembda<span style="color:#ef4444; font-weight:900;">HUB</span></div>
                    <div class="nav-brand-sub">Smart School Management</div>
                </div>
            </a>

            <div class="nav-links">
                <a href="#beranda" class="nav-link">Beranda</a>
                <a href="#features" class="nav-link">Fitur</a>
                <a href="#profil" class="nav-link">Profil</a>
                <a href="#sekolah" class="nav-link">Sekolah</a>
                <a href="#program" class="nav-link">Program</a>
                <a href="#berita" class="nav-link">Berita</a>
                <a href="#kontak" class="nav-link">Kontak</a>
            </div>

            <div class="nav-cta-actions">
                @auth
                <a href="{{ route('dashboard') }}" class="btn btn-gold nav-cta">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
                @else
                    @if(isset($activeWave) && $activeWave)
                    <a href="{{ route('login') }}" class="nav-link" style="font-size:13px;">Login</a>
                    <a href="{{ route('public.registration.index') }}" class="btn btn-gold nav-cta">
                        <i class="fa-solid fa-user-plus"></i> Daftar PSB
                    </a>
                    @else
                    <a href="{{ route('public.registration.check') }}" class="nav-link" style="font-size:13px;" title="Cek Status Pendaftaran">Cek PSB</a>
                    <a href="{{ route('login') }}" class="btn btn-gold nav-cta">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Portal
                    </a>
                    @endif
                @endauth
                <button class="mobile-menu-btn" id="mobile-menu-btn"><i class="fa-solid fa-bars"></i></button>
            </div>
        </div>
    </div>
</nav>

<div class="mobile-overlay" id="mobile-overlay">
    <button class="mobile-close" id="mobile-close"><i class="fa-solid fa-xmark"></i></button>
    <a href="#beranda">Beranda</a>
    <a href="#features">Fitur</a>
    <a href="#profil">Profil</a>
    <a href="#sekolah">Sekolah</a>
    <a href="#program">Program</a>
    <a href="#berita">Berita</a>
    <a href="#kontak">Kontak</a>
    <div style="margin-top:24px; display:flex; flex-direction:column; align-items:center; gap:12px;">
        @auth
        <a href="{{ route('dashboard') }}" class="btn btn-gold">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        @else
            @if(isset($activeWave) && $activeWave)
            <a href="{{ route('login') }}" class="mobile-login-link">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </a>
            <a href="{{ route('public.registration.index') }}" class="btn btn-gold">
                <i class="fa-solid fa-user-plus"></i> Daftar PSB
            </a>
            @else
            <a href="{{ route('login') }}" class="btn btn-gold" style="width: 100%; max-width: 240px; justify-content: center;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal
            </a>
            <a href="{{ route('public.registration.check') }}" class="mobile-login-link" style="font-size: 14px !important; padding: 10px 24px !important;">
                <i class="fa-solid fa-magnifying-glass"></i> Cek Status PSB
            </a>
            @endif
        @endauth
    </div>
</div>

<style>
    .nav-cta-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .nav-cta {
        padding: 10px 22px !important;
        font-size: 13px !important;
        border-radius: 12px !important;
    }
    .mobile-login-link {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: rgba(255,255,255,0.7) !important;
        border: 1.5px solid rgba(255,255,255,0.15);
        padding: 12px 36px !important;
        border-radius: 14px;
        transition: all 0.25s ease;
    }
    .mobile-login-link:hover {
        color: #fff !important;
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.3);
    }
</style>
