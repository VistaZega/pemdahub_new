<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio Siswa Pembda')</title>

    @yield('og_meta')

    {{-- Fonts: Plus Jakarta Sans (Utama & Bersih) + Space Grotesk (Display Modern) --}}
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
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
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
            background-color: #f8fafc;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100dvh;
        }

        /* ── Custom Scrollbar Cerah ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── Text Selection ── */
        ::selection { background: #c7d2fe; color: #312e81; }

        /* ── Glassmorphism Navbar Cerah ── */
        .nav-light-glass {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* ── Background Aura Halus & Cerah ── */
        .light-aura-bg {
            background-color: #f8fafc;
            background-image:
                radial-gradient(ellipse 50% 40% at 15% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 45% 35% at 85% 25%, rgba(236, 72, 153, 0.07) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 85%, rgba(14, 165, 233, 0.06) 0%, transparent 60%);
        }

        /* ── Story Ring Khas Instagram (Pink-Orange-Purple) ── */
        @keyframes instagram-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .story-ring-gradient {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        }

        /* ── Teks Gradien Brand ── */
        .text-gradient-brand {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Transisi Mulus ── */
        .transition-base { transition: all 180ms cubic-bezier(0.4, 0, 0.2, 1); }

        [x-cloak] { display: none !important; }
    </style>

    @yield('head_styles')
</head>
<body class="light-aura-bg pb-20 md:pb-8">

{{-- ═══════════════════════════════════════════
     HEADER / TOP NAVBAR (CERAH & ELEGAN)
═══════════════════════════════════════════ --}}
<header class="nav-light-glass sticky top-0 z-50 transition-all">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">

        {{-- Brand Logo --}}
        <a href="{{ route('circle.feed') }}" class="flex items-center gap-2.5 group shrink-0">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white shadow-md shadow-indigo-500/25 group-hover:scale-105 transition-transform"
                 style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%);">
                <i class="fas fa-bullseye text-base"></i>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="font-display font-extrabold text-xl text-slate-900 tracking-tight">Pembda</span>
                <span class="font-display font-black text-xl tracking-tight text-gradient-brand">CIRCLE</span>
            </div>
        </a>

        {{-- Unit Switcher Pills (Desktop) --}}
        <nav class="hidden md:flex items-center gap-1.5 bg-slate-100/90 rounded-2xl p-1.5 border border-slate-200/60 shadow-xs">
            <a href="{{ route('circle.feed') }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-base {{ !request('school') ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                Semua Unit
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMK']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition-base flex items-center gap-1.5 {{ request('school') === 'SMK' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-600 hover:text-orange-600 hover:bg-orange-50' }}">
                <i class="fas fa-screwdriver-wrench text-[10px]"></i>
                <span>SMK Pembda</span>
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMA']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition-base flex items-center gap-1.5 {{ request('school') === 'SMA' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-blue-600 hover:bg-blue-50' }}">
                <i class="fas fa-atom text-[10px]"></i>
                <span>SMA Pembda</span>
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMP']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition-base flex items-center gap-1.5 {{ request('school') === 'SMP' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' }}">
                <i class="fas fa-seedling text-[10px]"></i>
                <span>SMP Pembda</span>
            </a>
        </nav>

        {{-- Auth Bar --}}
        <div class="flex items-center gap-3 shrink-0">
            @auth
                @php
                    $__navUser = Auth::user();
                    $__navProfile = $__navUser->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser($__navUser);
                @endphp
                <a href="{{ route('circle.profile', ['username' => $__navProfile->username]) }}"
                   class="flex items-center gap-2.5 pl-1.5 pr-3.5 py-1.5 rounded-2xl bg-white hover:bg-indigo-50/70 border border-slate-200 hover:border-indigo-200 shadow-xs transition-base group">
                    <img src="{{ $__navUser->avatar_url ?? asset('images/default-avatar.svg') }}"
                         alt="{{ $__navUser->name }}"
                         class="w-7 h-7 rounded-xl object-cover ring-1 ring-slate-200">
                    <span class="text-xs font-bold text-slate-800 max-w-[100px] truncate group-hover:text-indigo-600 transition-colors">
                        {{ $__navProfile->nickname ?? explode(' ', $__navUser->name)[0] }}
                    </span>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white transition-base shadow-sm shadow-indigo-500/20 hover:scale-105"
                   style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                    <i class="fas fa-arrow-right-to-bracket"></i>
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
     BOTTOM NAVIGATION MOBILE (INSTAGRAM STYLE CERAH)
═══════════════════════════════════════════ --}}
<nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200/90 shadow-lg">
    <div class="flex items-center justify-around px-2 py-2">

        {{-- Beranda --}}
        <a href="{{ route('circle.feed') }}"
           class="flex flex-col items-center gap-0.5 min-w-[50px] transition-base {{ request()->routeIs('circle.feed') && !request('tab') ? 'text-indigo-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
            <i class="fas fa-house text-xl"></i>
            <span class="text-[10px] font-semibold">Beranda</span>
        </a>

        {{-- Reels --}}
        <a href="{{ route('circle.feed', ['tab' => 'reels']) }}"
           class="flex flex-col items-center gap-0.5 min-w-[50px] transition-base {{ request('tab') === 'reels' ? 'text-pink-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
            <i class="fas fa-clapperboard text-xl"></i>
            <span class="text-[10px] font-semibold">Reels</span>
        </a>

        {{-- Plus Center Action --}}
        @auth
        <a href="{{ route('circle.profile', ['username' => optional(\App\Models\CircleProfile::where('user_id', Auth::id())->first())->username ?? Auth::user()->username]) }}"
           class="flex flex-col items-center gap-0 -mt-5 transition-base">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 hover:scale-105"
                 style="background: linear-gradient(135deg, #4f46e5 0%, #ec4899 100%);">
                <i class="fas fa-plus text-lg"></i>
            </div>
            <span class="text-[9px] font-bold text-slate-600 mt-0.5">Post</span>
        </a>
        @else
        <a href="{{ route('login') }}"
           class="flex flex-col items-center gap-0 -mt-5 transition-base">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 hover:scale-105"
                 style="background: linear-gradient(135deg, #4f46e5 0%, #ec4899 100%);">
                <i class="fas fa-plus text-lg"></i>
            </div>
            <span class="text-[9px] font-bold text-slate-600 mt-0.5">Post</span>
        </a>
        @endauth

        {{-- Karya --}}
        <a href="{{ route('circle.feed', ['tab' => 'portfolio']) }}"
           class="flex flex-col items-center gap-0.5 min-w-[50px] transition-base {{ request('tab') === 'portfolio' ? 'text-amber-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
            <i class="fas fa-trophy text-xl"></i>
            <span class="text-[10px] font-semibold">Karya</span>
        </a>

        {{-- Profil --}}
        @auth
        <a href="{{ route('circle.profile', ['username' => optional(\App\Models\CircleProfile::where('user_id', Auth::id())->first())->username ?? Auth::user()->username]) }}"
           class="flex flex-col items-center gap-0.5 min-w-[50px] transition-base {{ request()->routeIs('circle.profile') ? 'text-indigo-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
            <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}"
                 class="w-6 h-6 rounded-full object-cover ring-2 {{ request()->routeIs('circle.profile') ? 'ring-indigo-600' : 'ring-slate-300' }}">
            <span class="text-[10px] font-semibold">Profil</span>
        </a>
        @else
        <a href="{{ route('login') }}"
           class="flex flex-col items-center gap-0.5 min-w-[50px] transition-base text-slate-500 hover:text-slate-800">
            <i class="fas fa-user-circle text-xl"></i>
            <span class="text-[10px] font-semibold">Masuk</span>
        </a>
        @endauth

    </div>
</nav>

@yield('scripts')
</body>
</html>
