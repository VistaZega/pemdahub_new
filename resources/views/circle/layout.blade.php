<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#fdfbf7">
    <title>@yield('title', 'PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio Siswa Pembda')</title>

    @yield('og_meta')

    {{-- Fonts: Plus Jakarta Sans (Sangat Mudah Dibaca) + Space Grotesk (Display Cantik) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">

    {{-- FontAwesome 6 Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Space Grotesk"', 'ui-sans-serif', 'sans-serif'],
                    },
                    colors: {
                        pastel: {
                            pink: '#fce7f3',
                            pinkDark: '#db2777',
                            green: '#dcfce7',
                            greenDark: '#15803d',
                            yellow: '#fef9c3',
                            yellowDark: '#a16207',
                            blue: '#e0f2fe',
                            blueDark: '#0369a1',
                            brown: '#f5ebe0',
                            brownDark: '#7f5539',
                            cream: '#fdfbf7',
                        }
                    }
                }
            }
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            background-color: #fdfbf7;
            color: #0f172a; /* Teks Hitam Tegas (Sangat Mudah Dibaca) */
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100dvh;
        }

        /* ── Custom Scrollbar Pastel ── */
        ::-webkit-scrollbar { width: 7px; height: 7px; }
        ::-webkit-scrollbar-track { background: #fdfbf7; }
        ::-webkit-scrollbar-thumb { background: #e2d9cc; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbbfae; }

        /* ── Text Selection Pastel Pink ── */
        ::selection { background: #fbcfe8; color: #831843; }

        /* ── Navbar Glassmorphic Pastel Cream ── */
        .nav-pastel-glass {
            background: rgba(253, 251, 247, 0.92);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border-bottom: 2px solid #f1e8dc;
        }

        /* ── Latar Belakang Aura Pastel Cantik & Lembut ── */
        .pastel-aura-bg {
            background-color: #fdfbf7;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(252, 231, 243, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 90% 15%, rgba(224, 242, 254, 0.7) 0%, transparent 35%),
                radial-gradient(circle at 50% 50%, rgba(254, 249, 195, 0.5) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(220, 252, 231, 0.6) 0%, transparent 40%),
                radial-gradient(circle at 15% 85%, rgba(245, 235, 224, 0.7) 0%, transparent 40%);
            background-attachment: fixed;
        }

        /* ── Story Ring Pastel Pelangi Cantik ── */
        .pastel-story-ring {
            background: linear-gradient(135deg, #f472b6, #fbbf24, #4ade80, #38bdf8);
        }

        /* ── Badge / Chip Ikon Lucu ── */
        .icon-bubble {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            transition: transform 0.2s ease;
        }
        .icon-bubble:hover {
            transform: scale(1.1) rotate(4deg);
        }

        /* ── Transisi Mulus ── */
        .transition-base { transition: all 180ms cubic-bezier(0.4, 0, 0.2, 1); }

        [x-cloak] { display: none !important; }
    </style>

    @yield('head_styles')
</head>
<body class="pastel-aura-bg pb-24 md:pb-10">

{{-- ═══════════════════════════════════════════
     HEADER / TOP NAVBAR (PASTEL CANTIK & BERSIH)
═══════════════════════════════════════════ --}}
<header class="nav-pastel-glass sticky top-0 z-50 transition-all">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">

        {{-- Brand Logo Cantik & Lucu --}}
        <a href="{{ route('circle.feed') }}" class="flex items-center gap-2.5 group shrink-0">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-pink-600 bg-pink-100 border-2 border-pink-200 shadow-sm group-hover:rotate-12 transition-transform">
                <i class="fas fa-sparkles text-lg"></i>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="font-display font-extrabold text-xl text-slate-900 tracking-tight">Pembda</span>
                <span class="font-display font-black text-xl tracking-tight text-pink-600 bg-pink-50 px-2 py-0.5 rounded-xl border border-pink-200">CIRCLE</span>
            </div>
        </a>

        {{-- Unit Switcher Pills: Gelora 3 Unit Sekolah --}}
        <nav class="hidden md:flex items-center gap-2 bg-white/80 rounded-2xl p-1.5 border-2 border-[#f1e8dc] shadow-xs">
            <a href="{{ route('circle.feed') }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-base {{ !request('school') ? 'bg-pink-100 text-pink-900 border border-pink-300 shadow-xs' : 'text-slate-800 hover:text-pink-700 hover:bg-pink-50' }}">
                <i class="fas fa-shapes text-pink-500 mr-1.5"></i>Semua Gelora
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMK']) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-base flex items-center gap-1.5 {{ request('school') === 'SMK' ? 'bg-[#faedcd] text-[#7f5539] border border-[#d4a373] shadow-xs' : 'text-slate-800 hover:text-[#7f5539] hover:bg-[#faedcd]/60' }}">
                <span class="w-5 h-5 rounded-lg bg-[#e6ccb2] text-[#7f5539] flex items-center justify-center text-[10px]">
                    <i class="fas fa-wrench"></i>
                </span>
                <span>Reka Vokasi SMK</span>
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMA']) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-base flex items-center gap-1.5 {{ request('school') === 'SMA' ? 'bg-sky-100 text-sky-900 border border-sky-300 shadow-xs' : 'text-slate-800 hover:text-sky-700 hover:bg-sky-50' }}">
                <span class="w-5 h-5 rounded-lg bg-sky-200 text-sky-800 flex items-center justify-center text-[10px]">
                    <i class="fas fa-atom"></i>
                </span>
                <span>Riset Ilmiah SMA</span>
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMP']) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-base flex items-center gap-1.5 {{ request('school') === 'SMP' ? 'bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-xs' : 'text-slate-800 hover:text-emerald-700 hover:bg-emerald-50' }}">
                <span class="w-5 h-5 rounded-lg bg-emerald-200 text-emerald-800 flex items-center justify-center text-[10px]">
                    <i class="fas fa-seedling"></i>
                </span>
                <span>Tunas Muda SMP</span>
            </a>
        </nav>

        {{-- Auth & Check-out to Dashboard Bar --}}
        <div class="flex items-center gap-2.5 shrink-0">
            @auth
                @php
                    $__navUser = Auth::user();
                    $__navProfile = $__navUser->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser($__navUser);
                    
                    $__dashUrl = route('admin.dashboard');
                    if ($__navUser->role === 'siswa') {
                        $__dashUrl = route('siswa.dashboard');
                    } elseif ($__navUser->role === 'guru' || $__navUser->role === 'pegawai') {
                        $__dashUrl = route('guru.dashboard');
                    } elseif ($__navUser->role === 'orang_tua') {
                        $__dashUrl = route('orangtua.dashboard');
                    } elseif ($__navUser->isKetuaYayasan() || $__navUser->hasRole('ketua_yayasan')) {
                        $__dashUrl = route('yayasan.dashboard');
                    }
                @endphp

                {{-- TOMBOL MORAK & CERAH: KEMBALI KE MEJA KERJA DASHBOARD --}}
                <a href="{{ $__dashUrl }}" 
                   title="Keluar dari CIRCLE dan Kembali ke Meja Kerja Dashboard Utama"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl border-2 border-black transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-md cursor-pointer"
                   style="background: linear-gradient(135deg, #ffd600 0%, #34d399 100%) !important; color: #000000 !important; border: 2.5px solid #000000 !important; box-shadow: 0 4px 12px rgba(52, 211, 153, 0.45) !important;">
                    <i class="fas fa-compass text-xs" style="color: #000000 !important;"></i>
                    <span class="hidden sm:inline" style="color: #000000 !important; font-weight: 900 !important; font-size: 12px !important; letter-spacing: 0.05em !important; text-transform: uppercase !important;">KEMBALI KE MEJA KERJA</span>
                    <span class="sm:hidden" style="color: #000000 !important; font-weight: 900 !important; font-size: 11px !important;">Meja Kerja</span>
                </a>

                <a href="{{ route('circle.profile', ['username' => $__navProfile->username]) }}"
                   class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-2xl bg-white hover:bg-pink-50/80 border-2 border-pink-200 hover:border-pink-400 shadow-xs transition-base group">
                    <img src="{{ $__navUser->avatar_url ?? asset('images/default-avatar.svg') }}"
                         alt="{{ $__navUser->name }}"
                         class="w-7 h-7 rounded-xl object-cover ring-2 ring-pink-300">
                    <span class="text-xs font-black text-slate-950 max-w-[90px] truncate group-hover:text-pink-600 transition-colors">
                        {{ $__navProfile->nickname ?? explode(' ', $__navUser->name)[0] }}
                    </span>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-black text-slate-900 bg-pink-200 hover:bg-pink-300 border-2 border-pink-300 shadow-xs transition-base hover:scale-105">
                    <i class="fas fa-heart text-pink-600"></i>
                    <span>Masuk</span>
                </a>
            @endauth
        </div>

    </div>
</header>

{{-- ═══════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════ --}}
<main class="max-w-6xl mx-auto px-4 py-6">
    @yield('content')
</main>

{{-- ═══════════════════════════════════════════
     BOTTOM NAVIGATION MOBILE (PASTEL CANTIK & LUCU)
═══════════════════════════════════════════ --}}
<nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-t-2 border-[#f1e8dc] shadow-lg">
    <div class="flex items-center justify-around px-2 py-2">

        {{-- Beranda --}}
        <a href="{{ route('circle.feed') }}"
           class="flex flex-col items-center gap-1 min-w-[50px] transition-base {{ request()->routeIs('circle.feed') && !request('tab') ? 'text-pink-600 font-black' : 'text-slate-600 hover:text-slate-950 font-bold' }}">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ request()->routeIs('circle.feed') && !request('tab') ? 'bg-pink-100 text-pink-600' : 'text-slate-600' }}">
                <i class="fas fa-home text-base"></i>
            </div>
            <span class="text-[10px]">Beranda</span>
        </a>

        {{-- Reels --}}
        <a href="{{ route('circle.feed', ['tab' => 'reels']) }}"
           class="flex flex-col items-center gap-1 min-w-[50px] transition-base {{ request('tab') === 'reels' ? 'text-sky-600 font-black' : 'text-slate-600 hover:text-slate-950 font-bold' }}">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ request('tab') === 'reels' ? 'bg-sky-100 text-sky-600' : 'text-slate-600' }}">
                <i class="fas fa-play text-sm"></i>
            </div>
            <span class="text-[10px]">Reels</span>
        </a>

        {{-- Center Plus CTA Lucu --}}
        @auth
        <a href="{{ route('circle.profile', ['username' => optional(\App\Models\CircleProfile::where('user_id', Auth::id())->first())->username ?? Auth::user()->username]) }}"
           class="flex flex-col items-center gap-0 -mt-6 transition-base">
            <div class="w-13 h-13 rounded-3xl flex items-center justify-center text-slate-900 bg-pink-200 border-3 border-white shadow-md hover:scale-110 transition-transform">
                <i class="fas fa-plus text-xl text-pink-700"></i>
            </div>
            <span class="text-[10px] font-black text-slate-900 mt-1">Post</span>
        </a>
        @else
        <a href="{{ route('login') }}"
           class="flex flex-col items-center gap-0 -mt-6 transition-base">
            <div class="w-13 h-13 rounded-3xl flex items-center justify-center text-slate-900 bg-pink-200 border-3 border-white shadow-md hover:scale-110 transition-transform">
                <i class="fas fa-plus text-xl text-pink-700"></i>
            </div>
            <span class="text-[10px] font-black text-slate-900 mt-1">Post</span>
        </a>
        @endauth

        {{-- Karya --}}
        <a href="{{ route('circle.feed', ['tab' => 'portfolio']) }}"
           class="flex flex-col items-center gap-1 min-w-[50px] transition-base {{ request('tab') === 'portfolio' ? 'text-amber-700 font-black' : 'text-slate-600 hover:text-slate-950 font-bold' }}">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ request('tab') === 'portfolio' ? 'bg-amber-100 text-amber-700' : 'text-slate-600' }}">
                <i class="fas fa-trophy text-sm"></i>
            </div>
            <span class="text-[10px]">Karya</span>
        </a>

        {{-- Profil --}}
        @auth
        <a href="{{ route('circle.profile', ['username' => optional(\App\Models\CircleProfile::where('user_id', Auth::id())->first())->username ?? Auth::user()->username]) }}"
           class="flex flex-col items-center gap-1 min-w-[50px] transition-base {{ request()->routeIs('circle.profile') ? 'text-pink-600 font-black' : 'text-slate-600 hover:text-slate-950 font-bold' }}">
            <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}"
                 class="w-7 h-7 rounded-full object-cover ring-2 {{ request()->routeIs('circle.profile') ? 'ring-pink-500' : 'ring-slate-300' }}">
            <span class="text-[10px]">Profil</span>
        </a>

        {{-- Kembali ke Meja Kerja (Mobile) --}}
        <a href="{{ $__dashUrl }}"
           class="flex flex-col items-center gap-1 min-w-[48px] transition-base text-slate-950 font-black">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center border-2 border-black shadow-xs"
                 style="background: linear-gradient(135deg, #ffd600 0%, #34d399 100%) !important; color: #000000 !important;">
                <i class="fas fa-compass text-xs text-black"></i>
            </div>
            <span class="text-[9px] font-black text-slate-950">Meja Kerja</span>
        </a>
        @else
        <a href="{{ route('login') }}"
           class="flex flex-col items-center gap-1 min-w-[50px] transition-base text-slate-600 hover:text-slate-950 font-bold">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-600">
                <i class="fas fa-user-circle text-lg"></i>
            </div>
            <span class="text-[10px]">Masuk</span>
        </a>
        @endauth

    </div>
</nav>

<script>
window.toggleCircleReaction = async function(btn, postId, reactionType) {
    const card = btn.closest('article') || btn.closest('[data-post-id]');
    const countSpan = card ? card.querySelector('.reaction-count-' + postId) : null;
    
    // Visual pop feedback
    btn.classList.add('scale-125', 'transition-transform');
    setTimeout(() => btn.classList.remove('scale-125'), 180);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch("{{ route('circle.react') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                circle_post_id: postId,
                reaction_type: reactionType
            })
        });
        const data = await res.json();
        if (data && data.likes_count !== undefined && countSpan) {
            countSpan.textContent = data.likes_count > 0 ? data.likes_count : 'Lit';
        }
    } catch (e) {
        console.error('Reaction error:', e);
    }
};
</script>

@yield('scripts')
</body>
</html>
