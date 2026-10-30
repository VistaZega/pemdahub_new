<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <title>@yield('title', 'PembdaCIRCLE — Circle Pendidikan Pertama Indonesia')</title>

    @yield('og_meta')

    {{-- Fonts: Space Grotesk (display) + Plus Jakarta Sans (body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- FontAwesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Space Grotesk"', 'ui-sans-serif', 'sans-serif'],
                    },
                    colors: {
                        slate: {
                            850: '#172033',
                            950: '#0f172a',
                        }
                    },
                    animation: {
                        'spin-slow': 'spin 3s linear infinite',
                        'ring-pulse': 'ring-pulse 2s ease-in-out infinite',
                    },
                    keyframes: {
                        'ring-pulse': {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.6' },
                        }
                    }
                }
            }
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ── Reset & Root ── */
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            background-color: #0f172a;
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100dvh;
        }

        /* ── Custom Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* ── Text selection ── */
        ::selection { background: rgba(99,102,241,0.35); color: #e0e7ff; }

        /* ── Focus ring ── */
        :focus-visible { outline: 2px solid #6366f1; outline-offset: 3px; border-radius: 6px; }

        /* ── Story ring animation ── */
        @keyframes story-ring-spin {
            from { background-position: 0% 50%; }
            to { background-position: 100% 50%; }
        }
        .story-ring {
            background: linear-gradient(135deg, #ec4899, #8b5cf6, #6366f1, #06b6d4);
            background-size: 300% 300%;
            animation: story-ring-spin 3s linear infinite;
        }

        /* ── Navbar Glassmorphic ── */
        .nav-glass {
            background: rgba(15, 23, 42, 0.82);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }

        /* ── Subtle ambient glow background ── */
        .page-bg {
            background-color: #0f172a;
            background-image:
                radial-gradient(ellipse 60% 50% at 15% 40%, rgba(99,102,241,0.13) 0%, transparent 65%),
                radial-gradient(ellipse 50% 40% at 85% 15%, rgba(139,92,246,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 40% 35% at 60% 85%, rgba(6,182,212,0.05) 0%, transparent 55%);
        }

        /* ── Active nav indicator (mobile) ── */
        .mobile-nav-active { color: #818cf8; }
        .mobile-nav-active svg, .mobile-nav-active i { filter: drop-shadow(0 0 6px rgba(99,102,241,0.7)); }

        /* ── Gradient text helper ── */
        .text-gradient-circle {
            background: linear-gradient(135deg, #818cf8 0%, #a78bfa 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Transition defaults ── */
        .transition-base { transition: all 150ms cubic-bezier(0.4,0,0.2,1); }

        [x-cloak] { display: none !important; }
    </style>

    @yield('head_styles')
</head>
<body class="page-bg pb-20 md:pb-0">

{{-- ═══════════════════════════════════════════
     TOP NAVIGATION BAR
═══════════════════════════════════════════ --}}
<header class="nav-glass sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 h-15 flex items-center justify-between gap-3" style="height:60px">

        {{-- Brand Logo --}}
        <a href="{{ route('circle.feed') }}" class="flex items-center gap-2.5 group shrink-0">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center relative overflow-hidden"
                 style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                <i class="fas fa-circle-dot text-white text-sm"></i>
                <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </div>
            <div class="flex items-baseline gap-0.5">
                <span class="font-display font-bold text-lg text-slate-100 tracking-tight">Pembda</span>
                <span class="font-display font-black text-lg tracking-tight text-gradient-circle">CIRCLE</span>
            </div>
        </a>

        {{-- Unit Filter Pills (Desktop) --}}
        <nav class="hidden md:flex items-center gap-1 bg-slate-900/60 rounded-xl p-1 border border-slate-800/60">
            <a href="{{ route('circle.feed') }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-base {{ !request('school') ? 'bg-slate-700 text-slate-100 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                Semua
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMK']) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-base flex items-center gap-1.5 {{ request('school') === 'SMK' ? 'bg-orange-500/20 text-orange-300 border border-orange-500/25' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                <i class="fas fa-screwdriver-wrench text-[9px]"></i> SMK
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMA']) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-base flex items-center gap-1.5 {{ request('school') === 'SMA' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/25' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                <i class="fas fa-atom text-[9px]"></i> SMA
            </a>
            <a href="{{ route('circle.feed', ['school' => 'SMP']) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-base flex items-center gap-1.5 {{ request('school') === 'SMP' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/25' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                <i class="fas fa-seedling text-[9px]"></i> SMP
            </a>
        </nav>

        {{-- Auth Bar --}}
        <div class="flex items-center gap-2.5 shrink-0">
            @auth
                @php
                    $__navUser = Auth::user();
                    $__navProfile = $__navUser->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser($__navUser);
                @endphp
                <a href="{{ route('circle.profile', ['username' => $__navProfile->username]) }}"
                   class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-xl bg-slate-800/70 hover:bg-slate-700/70 border border-slate-700/50 hover:border-slate-600/50 transition-base group">
                    <img src="{{ $__navUser->avatar_url ?? asset('images/default-avatar.svg') }}"
                         alt="{{ $__navUser->name }}"
                         class="w-7 h-7 rounded-lg object-cover ring-1 ring-white/10">
                    <span class="text-xs font-semibold text-slate-200 max-w-[88px] truncate group-hover:text-white transition-colors">
                        {{ $__navProfile->nickname ?? explode(' ', $__navUser->name)[0] }}
                    </span>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white transition-base"
                   style="background:linear-gradient(135deg,#4f46e5,#7c3aed); box-shadow:0 4px 14px rgba(99,102,241,0.35);">
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
     MOBILE BOTTOM NAVIGATION (Instagram-style)
═══════════════════════════════════════════ --}}
<nav class="md:hidden fixed bottom-0 inset-x-0 z-50 nav-glass border-t border-white/[0.04]">
    <div class="flex items-center justify-around px-2 py-2 safe-area-inset-bottom">

        {{-- Home --}}
        <a href="{{ route('circle.feed') }}"
           class="flex flex-col items-center gap-1 min-w-[48px] transition-base {{ request()->routeIs('circle.feed') && !request('tab') ? 'mobile-nav-active' : 'text-slate-500 hover:text-slate-300' }}">
            <i class="fas fa-house text-[22px]"></i>
            <span class="text-[9px] font-semibold">Beranda</span>
        </a>

        {{-- Reels --}}
        <a href="{{ route('circle.feed', ['tab' => 'reels']) }}"
           class="flex flex-col items-center gap-1 min-w-[48px] transition-base {{ request('tab') === 'reels' ? 'mobile-nav-active' : 'text-slate-500 hover:text-slate-300' }}">
            <i class="fas fa-clapperboard text-[22px]"></i>
            <span class="text-[9px] font-semibold">Reels</span>
        </a>

        {{-- Post Button (center CTA) --}}
        @auth
        <a href="{{ route('circle.profile', ['username' => optional(\App\Models\CircleProfile::where('user_id', Auth::id())->first())->username ?? Auth::user()->username]) }}"
           class="flex flex-col items-center gap-0 -mt-4 transition-base">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30"
                 style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                <i class="fas fa-plus text-lg"></i>
            </div>
            <span class="text-[9px] font-semibold text-slate-500 mt-1">Post</span>
        </a>
        @else
        <a href="{{ route('login') }}"
           class="flex flex-col items-center gap-0 -mt-4 transition-base">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30"
                 style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                <i class="fas fa-plus text-lg"></i>
            </div>
            <span class="text-[9px] font-semibold text-slate-500 mt-1">Post</span>
        </a>
        @endauth

        {{-- Karya --}}
        <a href="{{ route('circle.feed', ['tab' => 'portfolio']) }}"
           class="flex flex-col items-center gap-1 min-w-[48px] transition-base {{ request('tab') === 'portfolio' ? 'mobile-nav-active' : 'text-slate-500 hover:text-slate-300' }}">
            <i class="fas fa-trophy text-[22px]"></i>
            <span class="text-[9px] font-semibold">Karya</span>
        </a>

        {{-- Profile --}}
        @auth
        <a href="{{ route('circle.profile', ['username' => optional(\App\Models\CircleProfile::where('user_id', Auth::id())->first())->username ?? Auth::user()->username]) }}"
           class="flex flex-col items-center gap-1 min-w-[48px] transition-base {{ request()->routeIs('circle.profile') ? 'mobile-nav-active' : 'text-slate-500 hover:text-slate-300' }}">
            <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}"
                 class="w-6 h-6 rounded-lg object-cover ring-1 {{ request()->routeIs('circle.profile') ? 'ring-indigo-500' : 'ring-white/10' }} transition-base">
            <span class="text-[9px] font-semibold">Profil</span>
        </a>
        @else
        <a href="{{ route('login') }}"
           class="flex flex-col items-center gap-1 min-w-[48px] transition-base text-slate-500 hover:text-slate-300">
            <i class="fas fa-user-circle text-[22px]"></i>
            <span class="text-[9px] font-semibold">Masuk</span>
        </a>
        @endauth

    </div>
</nav>

@yield('scripts')
</body>
</html>
