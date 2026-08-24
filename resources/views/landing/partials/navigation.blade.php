{{-- NAVIGATION — Khan Academy Authentic Navbar --}}
<style>
    .khan-navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
        height: 68px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        transition: box-shadow 0.25s ease;
    }

    .khan-navbar.scrolled {
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
    }

    .khan-nav-inner {
        width: 100%;
        max-width: 1680px;
        margin: 0 auto;
        padding: 0 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    /* Left: Explore & Search */
    .khan-nav-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
    }

    .khan-explore-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14.5px;
        font-weight: 700;
        color: #1865f2;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 8px;
        transition: background 0.2s;
        text-decoration: none;
    }

    .khan-explore-btn:hover {
        background: #f0f4ff;
    }

    .khan-search-bar {
        position: relative;
        width: 100%;
        max-width: 240px;
    }

    .khan-search-input {
        width: 100%;
        height: 40px;
        background: #f0f4f8;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0 16px 0 38px;
        font-size: 13.5px;
        font-weight: 500;
        color: #0f172a;
        outline: none;
        transition: all 0.2s;
    }

    .khan-search-input:focus {
        background: #ffffff;
        border-color: #1865f2;
        box-shadow: 0 0 0 3px rgba(24, 101, 242, 0.15);
    }

    .khan-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 14px;
        pointer-events: none;
    }

    /* Center: Brand Logo */
    .khan-nav-center {
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .khan-brand-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0b2149;
        font-weight: 900;
        font-size: 20px;
        letter-spacing: -0.02em;
    }

    .khan-brand-logo span {
        color: #1865f2;
    }

    .khan-logo-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #10b981;
        font-size: 18px;
    }

    /* Right: Actions */
    .khan-nav-right {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 16px;
        flex: 1;
    }

    .khan-nav-link {
        font-size: 14px;
        font-weight: 700;
        color: #1865f2;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 8px;
        transition: background 0.2s;
    }

    .khan-nav-link:hover {
        background: #f0f4ff;
    }

    .khan-btn-signup {
        background: #1865f2;
        color: #ffffff !important;
        font-size: 14px;
        font-weight: 800;
        padding: 9px 20px;
        border-radius: 10px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .khan-btn-signup:hover {
        background: #144bc8;
        transform: translateY(-1px);
    }

    /* Mobile toggle */
    .khan-mobile-btn {
        display: none;
        background: none;
        border: none;
        font-size: 20px;
        color: #0b2149;
        cursor: pointer;
        padding: 6px;
    }

    @media (max-width: 960px) {
        .khan-search-bar, .khan-nav-right .khan-nav-link {
            display: none;
        }
        .khan-mobile-btn {
            display: block;
        }
    }
</style>

<nav class="khan-navbar" id="navbar">
    <div class="khan-nav-inner">
        
        {{-- Left: Explore & Search --}}
        <div class="khan-nav-left">
            <a href="#sekolah" class="khan-explore-btn">
                <span>Jelajahi</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 11px;"></i>
            </a>
            <div class="khan-search-bar">
                <i class="fa-solid fa-magnifying-glass khan-search-icon"></i>
                <input type="text" class="khan-search-input" placeholder="Cari materi, guru, jurusan..." id="navSearchInput" onkeydown="if(event.key==='Enter'){ window.location.href='#sekolah'; }">
            </div>
        </div>

        {{-- Center: Khan Academy Styled Brand Logo --}}
        <a href="{{ route('home') }}" class="khan-nav-center">
            <div class="khan-brand-logo">
                <div class="khan-logo-badge">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <div>Perguruan <span>PEMBDA</span></div>
            </div>
        </a>

        {{-- Right: Direct Actions --}}
        <div class="khan-nav-right">
            @auth
                <a href="{{ route('dashboard') }}" class="khan-btn-signup">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            @else
                <a href="{{ route('public.registration.check') }}" class="khan-nav-link" style="color:#64748b;">
                    Cek PSB
                </a>
                <a href="{{ route('login') }}" class="khan-nav-link">
                    Masuk
                </a>
                @if(isset($activeWave) && $activeWave)
                    <a href="{{ route('public.registration.index') }}" class="khan-btn-signup">
                        Daftar PSB
                    </a>
                @else
                    <a href="{{ route('login') }}" class="khan-btn-signup">
                        Masuk Portal
                    </a>
                @endif
            @endauth
            <button class="khan-mobile-btn" id="mobile-menu-btn">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

    </div>
</nav>

{{-- Mobile Menu Overlay --}}
<div class="mobile-overlay" id="mobile-overlay">
    <button class="mobile-close" id="mobile-close"><i class="fa-solid fa-xmark"></i></button>
    <a href="#beranda">Beranda</a>
    <a href="#features">Fitur Platform</a>
    <a href="#profil">Profil Yayasan</a>
    <a href="#sekolah">3 Unit Sekolah</a>
    <a href="#program">Program Unggulan</a>
    <a href="#berita">Berita &amp; Artikel</a>
    <a href="#kontak">Kontak</a>
    <div style="margin-top:24px; display:flex; flex-direction:column; align-items:center; gap:12px;">
        @auth
            <a href="{{ route('dashboard') }}" class="khan-btn-signup" style="padding:12px 32px; font-size:16px;">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="khan-btn-signup" style="padding:12px 32px; font-size:16px;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Portal
            </a>
        @endauth
    </div>
</div>
