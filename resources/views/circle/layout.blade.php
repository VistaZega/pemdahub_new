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

        /* ── ANIMASI LOGO PEMBDACIRCLE ULTRA CANTIK & DINAMIS ── */
        .circle-spin-slow {
            animation: circleSpin 14s linear infinite;
            transform-origin: center;
        }
        .group:hover .circle-spin-slow {
            animation-duration: 4s;
        }

        .circle-spin-reverse {
            animation: circleSpinReverse 9s linear infinite;
            transform-origin: center;
        }
        .group:hover .circle-spin-reverse {
            animation-duration: 3s;
        }

        @keyframes circleSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes circleSpinReverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }

        .circle-sparkle-dance {
            animation: sparkleDance 3s ease-in-out infinite;
            transform-origin: center;
        }
        @keyframes sparkleDance {
            0%, 100% { transform: scale(1) rotate(0deg); color: #ec4899; }
            25% { transform: scale(1.2) rotate(12deg); color: #f43f5e; }
            50% { transform: scale(0.92) rotate(-8deg); color: #f59e0b; }
            75% { transform: scale(1.15) rotate(6deg); color: #8b5cf6; }
        }

        .circle-twinkle-1 {
            animation: twinkleOne 2.2s ease-in-out infinite;
        }
        @keyframes twinkleOne {
            0%, 100% { opacity: 0.3; transform: scale(0.75) translateY(0); }
            50% { opacity: 1; transform: scale(1.3) translateY(-3px) rotate(15deg); }
        }

        .circle-twinkle-2 {
            animation: twinkleTwo 2.8s ease-in-out infinite 0.7s;
        }
        @keyframes twinkleTwo {
            0%, 100% { opacity: 0.9; transform: scale(1.2) rotate(0deg); }
            50% { opacity: 0.2; transform: scale(0.6) rotate(-25deg); }
        }

        .circle-gradient-text {
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 25%, #8b5cf6 50%, #06b6d4 75%, #ec4899 100%);
            background-size: 300% 300%;
            animation: gradientFlow 5s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .circle-shimmer {
            animation: shimmerSlide 3.5s ease-in-out infinite;
        }
        @keyframes shimmerSlide {
            0% { transform: translateX(-150%); }
            45%, 100% { transform: translateX(200%); }
        }
    </style>

    @yield('head_styles')
</head>
<body class="pastel-aura-bg pb-24 md:pb-10">

{{-- ═══════════════════════════════════════════
     HEADER / TOP NAVBAR (PASTEL CANTIK & BERSIH)
═══════════════════════════════════════════ --}}
<header class="nav-pastel-glass sticky top-0 z-50 transition-all">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">

        {{-- Brand Logo Cantik, Hidup & Memikat (Animated Orbital Emblem) --}}
        <a href="{{ route('circle.feed') }}" class="flex items-center gap-2.5 sm:gap-3 group shrink-0 select-none" title="Beranda PembdaCIRCLE">
            {{-- Multi-ring Animated Orbital Orb --}}
            <div class="relative w-10 h-10 sm:w-11 sm:h-11 flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110">
                <!-- Glowing Pulsing Aura -->
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-tr from-pink-400 via-amber-300 to-sky-400 blur-md opacity-45 group-hover:opacity-90 transition-opacity duration-300 animate-pulse"></div>
                
                <!-- Orbital Ring 1 (Clockwise) -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none circle-spin-slow" viewBox="0 0 44 44" fill="none">
                    <defs>
                        <linearGradient id="circleOrbGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#ec4899" />
                            <stop offset="50%" stop-color="#f59e0b" />
                            <stop offset="100%" stop-color="#06b6d4" />
                        </linearGradient>
                    </defs>
                    <circle cx="22" cy="22" r="19" stroke="url(#circleOrbGrad1)" stroke-width="2" stroke-linecap="round" stroke-dasharray="9 6" opacity="0.85" />
                    <!-- Planet Satellite Beads -->
                    <circle cx="39" cy="14" r="2.8" fill="#f43f5e" />
                    <circle cx="5" cy="30" r="2.2" fill="#06b6d4" />
                </svg>

                <!-- Orbital Ring 2 (Counter-Clockwise) -->
                <svg class="absolute inset-1 w-8 h-8 sm:w-9 sm:h-9 pointer-events-none circle-spin-reverse" viewBox="0 0 36 36" fill="none">
                    <defs>
                        <linearGradient id="circleOrbGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#8b5cf6" />
                            <stop offset="100%" stop-color="#ec4899" />
                        </linearGradient>
                    </defs>
                    <circle cx="18" cy="18" r="15" stroke="url(#circleOrbGrad2)" stroke-width="1.6" stroke-linecap="round" stroke-dasharray="4 7" opacity="0.75" />
                    <!-- Mini Golden Bead -->
                    <circle cx="18" cy="3" r="2.4" fill="#fbbf24" />
                </svg>

                <!-- 3D Glossy Clay Glass Core -->
                <div class="relative w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-gradient-to-tr from-pink-500 via-rose-500 to-amber-400 p-[1.5px] shadow-sm overflow-hidden flex items-center justify-center">
                    <div class="absolute inset-0 bg-gradient-to-b from-white/50 via-transparent to-black/10 rounded-xl pointer-events-none"></div>
                    <div class="w-full h-full rounded-[10px] bg-white/95 backdrop-blur-xs flex items-center justify-center relative">
                        <i class="fas fa-sparkles text-xs sm:text-sm circle-sparkle-dance"></i>
                    </div>
                </div>

                <!-- Floating Micro Twinkle Stars -->
                <span class="absolute -top-1.5 -right-1 text-[11px] pointer-events-none circle-twinkle-1">✨</span>
                <span class="absolute -bottom-1 -left-1 text-[9px] pointer-events-none circle-twinkle-2">⭐</span>
            </div>

            {{-- Typography with Animated Shimmer Badge --}}
            <div class="flex items-center gap-1 sm:gap-1.5">
                <span class="font-display font-black text-lg sm:text-xl text-slate-950 tracking-tight">Pembda</span>
                <div class="relative inline-flex items-center gap-1 px-2 sm:px-2.5 py-0.5 rounded-xl border-2 border-pink-200/90 shadow-xs overflow-hidden group-hover:border-pink-400 transition-colors"
                     style="background: linear-gradient(135deg, #fff1f2 0%, #fdf2f8 50%, #fef9c3 100%);">
                    <!-- Shimmer Stripe -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/80 to-transparent -translate-x-full circle-shimmer pointer-events-none"></div>
                    <!-- Radar Live Dot -->
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-pink-500"></span>
                    </span>
                    <!-- Flowing Gradient Text -->
                    <span class="font-display font-black text-[11px] sm:text-xs tracking-wider uppercase circle-gradient-text">
                        CIRCLE
                    </span>
                </div>
            </div>
        </a>

        {{-- Bilah Pencarian Gelora (Search Bar Pastel Bersih) --}}
        <form action="{{ route('circle.feed') }}" method="GET" class="hidden md:flex items-center relative min-w-[220px] max-w-[340px] w-full">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari karya, siswa, keahlian..."
                   class="w-full pl-8 pr-7 py-1.5 rounded-2xl bg-white border-2 border-[#f1e8dc] text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-300 shadow-2xs">
            <span class="absolute left-2.5 text-xs text-slate-400 pointer-events-none">🔍</span>
            @if(request('q'))
                <a href="{{ route('circle.feed', array_filter(['tab' => request('tab')])) }}" 
                   class="absolute right-2.5 text-xs text-slate-400 hover:text-rose-600 font-black cursor-pointer" title="Bersihkan Pencarian">
                    ✕
                </a>
            @endif
        </form>

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

                    // Notifikasi Genta Kabar PembdaCIRCLE
                    $__circleNotifications = \App\Models\Notification::where('user_id', $__navUser->id)
                        ->where(function($q) {
                            $q->where('type', 'like', 'circle_%')
                              ->orWhereNull('type');
                        })
                        ->latest()
                        ->take(8)
                        ->get();
                    $__circleUnreadCount = $__circleNotifications->where('is_read', false)->count();
                @endphp

                {{-- Genta Kabar (Pusat Notifikasi Gelora) --}}
                <div x-data="{ 
                        openNotif: false, 
                        unread: {{ $__circleUnreadCount }},
                        markRead() {
                            if (this.unread > 0) {
                                this.unread = 0;
                                fetch('{{ route('circle.notifications.readAll') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                }).catch(() => {});
                            }
                        }
                    }" 
                    class="relative" 
                    @click.away="openNotif = false">
                    
                    <button type="button" 
                            @click="openNotif = !openNotif; if(openNotif) markRead();" 
                            class="relative w-9 h-9 rounded-2xl bg-white hover:bg-pink-100 border-2 border-pink-200 text-slate-800 hover:text-pink-600 flex items-center justify-center text-sm transition-all duration-200 cursor-pointer shadow-2xs hover:scale-105"
                            title="Kabar Terbaru">
                        <i class="fas fa-bell"></i>
                        <template x-if="unread > 0">
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center ring-2 ring-white animate-pulse"
                                  x-text="unread">
                            </span>
                        </template>
                    </button>

                    {{-- Dropdown Notifikasi --}}
                    <div x-show="openNotif" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-72 sm:w-88 bg-white rounded-3xl border-2 border-pink-200 shadow-2xl overflow-hidden z-50 text-left"
                         style="display: none;">
                         
                        {{-- Header Dropdown --}}
                        <div class="px-4 py-3 bg-gradient-to-r from-pink-100 via-rose-50 to-amber-50 border-b-2 border-pink-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-pink-200 text-pink-700 flex items-center justify-center text-xs">
                                    🔔
                                </span>
                                <span class="text-xs font-black uppercase tracking-wider text-slate-950">Kabar</span>
                            </div>
                            <button type="button" @click="markRead()" class="text-[11px] font-black text-pink-700 hover:text-pink-900 cursor-pointer">
                                Tandai Dibaca
                            </button>
                        </div>

                        {{-- Notif List --}}
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                            @forelse($__circleNotifications as $notif)
                                @php
                                    $notifUrl = route('circle.feed');
                                    if ($notif->related_model === 'CirclePost' && $notif->related_id) {
                                        $notifUrl = route('circle.feed', ['post' => $notif->related_id]) . '#post-' . $notif->related_id;
                                    } elseif ($notif->related_model === 'CircleSkillEndorsement') {
                                        $notifUrl = route('circle.profile', ['username' => $__navProfile->username]);
                                    } elseif ($notif->type === 'circle_visitor' && $notif->related_id) {
                                        $vUser = \App\Models\User::find($notif->related_id);
                                        $notifUrl = $vUser ? route('circle.profile', ['username' => $vUser->circle_username]) : route('circle.profile', ['username' => $__navProfile->username]);
                                    } elseif ($notif->type === 'circle_guestbook') {
                                        $notifUrl = route('circle.profile', ['username' => $__navProfile->username, 'tab' => 'guestbook']);
                                    } elseif ($notif->type === 'circle_dm' && $notif->related_id) {
                                        $notifUrl = '#dm-' . $notif->related_id;
                                    }
                                @endphp
                                <a href="{{ $notifUrl }}" 
                                   @if($notif->type === 'circle_dm' && $notif->related_id)
                                       onclick="event.preventDefault(); window.openCircleChatWith({{ $notif->related_id }});"
                                   @endif
                                   class="block p-3 hover:bg-pink-50/60 transition {{ !$notif->is_read ? 'bg-amber-50/50' : '' }}">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-xl shrink-0 flex items-center justify-center text-xs shadow-2xs {{ str_contains($notif->type ?? '', 'visitor') ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : (str_contains($notif->type ?? '', 'guestbook') ? 'bg-rose-100 text-rose-800 border border-rose-300' : (str_contains($notif->type ?? '', 'endorsement') ? 'bg-amber-100 text-amber-800 border border-amber-300' : (str_contains($notif->type ?? '', 'reaction') ? 'bg-orange-100 text-orange-800 border border-orange-300' : (str_contains($notif->type ?? '', 'dm') ? 'bg-sky-100 text-sky-800 border border-sky-300' : 'bg-pink-100 text-pink-800 border border-pink-300')))) }}">
                                            @if(str_contains($notif->type ?? '', 'visitor'))
                                                👀
                                            @elseif(str_contains($notif->type ?? '', 'guestbook'))
                                                💌
                                            @elseif(str_contains($notif->type ?? '', 'endorsement'))
                                                🏅
                                            @elseif(str_contains($notif->type ?? '', 'reaction'))
                                                🔥
                                            @elseif(str_contains($notif->type ?? '', 'comment'))
                                                💬
                                            @elseif(str_contains($notif->type ?? '', 'dm'))
                                                ✉️
                                            @else
                                                ✨
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <h5 class="text-xs font-black text-slate-950 truncate">{{ $notif->title }}</h5>
                                                <span class="text-[10px] font-bold text-slate-400 shrink-0">{{ $notif->created_at->diffForHumans(null, true) }}</span>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-800 mt-0.5 line-clamp-2 leading-relaxed">
                                                {{ $notif->message }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-6 text-center">
                                    <span class="text-2xl block mb-1.5">🌸</span>
                                    <p class="text-xs font-black text-slate-900 mb-0.5">Belum Ada Kabar Baru</p>
                                    <p class="text-[11px] font-bold text-slate-500">Terus aktif berbagi karya & inspirasi di Panggung Gelora!</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Footer --}}
                        <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center">
                            <a href="{{ route('circle.profile', ['username' => $__navProfile->username]) }}" class="text-[11px] font-black text-pink-700 hover:text-pink-900">
                                Buka Profil & Loker Portofolio Saya →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kotak Pesan Pribadi (Bisik) Icon Header --}}
                @php
                    $__circleUnreadDmCount = \App\Models\Message::where('receiver_id', $__navUser->id)->where('is_read', false)->count();
                @endphp
                <button type="button" 
                        onclick="window.dispatchEvent(new CustomEvent('open-circle-inbox'))" 
                        class="relative w-9 h-9 rounded-2xl bg-white hover:bg-sky-100 border-2 border-sky-200 text-slate-800 hover:text-sky-600 flex items-center justify-center text-sm transition-all duration-200 cursor-pointer shadow-2xs hover:scale-105"
                        title="Bisik (Pesan Pribadi)">
                    <i class="fas fa-paper-plane"></i>
                    @if($__circleUnreadDmCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-sky-500 text-white text-[10px] font-black flex items-center justify-center ring-2 ring-white animate-pulse">
                            {{ $__circleUnreadDmCount }}
                        </span>
                    @endif
                </button>

                {{-- TOMBOL MORAK & CERAH: DASHBOARD UTAMA --}}
                <a href="{{ $__dashUrl }}" 
                   title="Kembali ke Dashboard Utama"
                   class="group inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-2xl border-2 border-black transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-md cursor-pointer"
                   style="background: linear-gradient(135deg, #ffd600 0%, #10b981 60%, #06b6d4 100%) !important; color: #000000 !important; border: 2.5px solid #000000 !important; box-shadow: 0 0 16px rgba(16, 185, 129, 0.45), 0 4px 12px rgba(0, 0, 0, 0.2) !important;">
                    <i class="fas fa-gauge-high text-xs group-hover:rotate-12 transition-transform duration-300" style="color: #000000 !important;"></i>
                    <span style="color: #000000 !important; font-weight: 900 !important; font-size: 11px !important; letter-spacing: 0.05em !important; text-transform: uppercase !important;">Dashboard</span>
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

        {{-- Pesan Pribadi (Bisik) Mobile --}}
        @auth
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-circle-inbox'))"
                class="flex flex-col items-center gap-1 min-w-[46px] transition-base text-slate-600 hover:text-sky-600 font-bold relative cursor-pointer">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-sky-50 text-sky-600 border border-sky-200 relative">
                <i class="fas fa-paper-plane text-xs"></i>
                @if($__circleUnreadDmCount > 0)
                    <span class="absolute -top-1 -right-1 min-w-[15px] h-[15px] px-0.5 rounded-full bg-sky-500 text-white text-[8px] font-black flex items-center justify-center">
                        {{ $__circleUnreadDmCount }}
                    </span>
                @endif
            </div>
            <span class="text-[10px]">Bisik</span>
        </button>
        @endauth

        {{-- Profil --}}
        @auth
        <a href="{{ route('circle.profile', ['username' => optional(\App\Models\CircleProfile::where('user_id', Auth::id())->first())->username ?? Auth::user()->username]) }}"
           class="flex flex-col items-center gap-1 min-w-[50px] transition-base {{ request()->routeIs('circle.profile') ? 'text-pink-600 font-black' : 'text-slate-600 hover:text-slate-950 font-bold' }}">
            <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}"
                 class="w-7 h-7 rounded-full object-cover ring-2 {{ request()->routeIs('circle.profile') ? 'ring-pink-500' : 'ring-slate-300' }}">
            <span class="text-[10px]">Profil</span>
        </a>

        {{-- Kembali ke Dashboard (Mobile) --}}
        <a href="{{ $__dashUrl }}"
           class="flex flex-col items-center gap-1 min-w-[48px] transition-base text-slate-950 font-black group">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center border-2 border-black shadow-xs transition-transform group-hover:scale-105"
                 style="background: linear-gradient(135deg, #ffd600 0%, #10b981 100%) !important; color: #000000 !important; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4) !important;">
                <i class="fas fa-gauge-high text-xs text-black"></i>
            </div>
            <span class="text-[9px] font-black text-slate-950">Dashboard</span>
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

window.shareCirclePost = function(title, postUrl) {
    const text = 'Lihat karya/momen seru ini di PembdaCIRCLE:\n"' + title + '"\n\n' + postUrl;
    if (navigator.share) {
        navigator.share({
            title: title || 'Karya PembdaCIRCLE',
            text: text,
            url: postUrl
        }).catch(() => {});
    } else {
        const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank');
    }
};

@auth
function circleChatModal() {
    return {
        isOpen: false,
        view: 'inbox',
        threads: [],
        messages: [],
        activeContact: null,
        newMessage: '',
        searchQuery: '',
        searchResults: [],
        isLoadingThreads: false,
        isLoadingMessages: false,
        isSearching: false,
        isSending: false,

        openInbox() {
            this.isOpen = true;
            this.view = 'inbox';
            this.loadThreads();
        },

        close() {
            this.isOpen = false;
        },

        backToInbox() {
            this.view = 'inbox';
            this.loadThreads();
        },

        async loadThreads() {
            this.isLoadingThreads = true;
            try {
                const res = await fetch("{{ route('circle.messages.inbox') }}");
                const data = await res.json();
                if (data.status === 'success') {
                    this.threads = data.threads || [];
                }
            } catch (e) {
                console.error('Error fetching DM inbox:', e);
            } finally {
                this.isLoadingThreads = false;
            }
        },

        async searchUsers() {
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                return;
            }
            this.isSearching = true;
            try {
                const res = await fetch(`{{ route('circle.messages.searchUsers') }}?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();
                if (data.status === 'success') {
                    this.searchResults = data.users || [];
                }
            } catch (e) {
                console.error('Error searching users:', e);
            } finally {
                this.isSearching = false;
            }
        },

        selectUserToChat(user) {
            this.activeContact = user;
            this.view = 'conversation';
            this.messages = [];
            this.searchQuery = '';
            this.searchResults = [];
            this.loadConversation(user.id);
        },

        openChatWithContact(contact) {
            this.isOpen = true;
            this.view = 'conversation';
            this.activeContact = contact;
            this.messages = [];
            this.loadConversation(contact.id);
        },

        async loadConversation(userId) {
            this.isLoadingMessages = true;
            try {
                const res = await fetch(`{{ url('/circle/messages/conversation') }}/${userId}`);
                const data = await res.json();
                if (data.status === 'success') {
                    this.messages = data.messages || [];
                    if (data.contact) {
                        this.activeContact = data.contact;
                    }
                    this.scrollToBottom();
                }
            } catch (e) {
                console.error('Error fetching conversation:', e);
            } finally {
                this.isLoadingMessages = false;
                this.scrollToBottom();
            }
        },

        async sendMessage() {
            const text = this.newMessage.trim();
            if (!text || !this.activeContact || this.isSending) return;

            this.isSending = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const res = await fetch("{{ route('circle.messages.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        receiver_id: this.activeContact.id,
                        message: text
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.messages.push(data.data);
                    this.newMessage = '';
                    this.scrollToBottom();
                } else {
                    alert(data.message || 'Gagal mengirim pesan');
                }
            } catch (e) {
                console.error('Error sending message:', e);
            } finally {
                this.isSending = false;
                this.scrollToBottom();
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.chatBox;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    };
}
@endauth

window.openCircleChatWith = function(userId, name = '', avatar = '', username = '') {
    @auth
        window.dispatchEvent(new CustomEvent('open-circle-dm', {
            detail: {
                id: userId,
                name: name,
                avatar_url: avatar,
                username: username,
                role_label: 'Warga Pembda',
                school_name: 'Perguruan PEMBDA'
            }
        }));
    @else
        window.location.href = "{{ route('login') }}";
    @endauth
};
</script>

@auth
{{-- ═══════════════════════════════════════════
     PEMBDACIRCLE SLIDE-OVER DM / INBOX MODAL
═══════════════════════════════════════════ --}}
<div x-data="circleChatModal()" 
     x-cloak 
     @open-circle-inbox.window="openInbox()"
     @open-circle-dm.window="openChatWithContact($event.detail)"
     class="relative z-[90]">

    {{-- Backdrop Overlay --}}
    <div x-show="isOpen" 
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         style="display: none;"></div>

    {{-- Drawer Panel --}}
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-[#fdfbf7] shadow-2xl border-l-2 border-[#f1e8dc] flex flex-col z-[100] overflow-hidden"
         style="display: none;">

        {{-- ── HEADER MODAL ── --}}
        <div class="px-4 py-3.5 bg-white border-b-2 border-[#f1e8dc] flex items-center justify-between shrink-0 shadow-2xs">
            <!-- View: Inbox Header -->
            <div x-show="view === 'inbox'" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-sky-100 text-sky-700 border-2 border-sky-200 flex items-center justify-center text-sm shadow-2xs">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-950 leading-tight">Bisik ✉️</h3>
                    <p class="text-[10px] font-bold text-slate-500">Pesan langsung teman & guru</p>
                </div>
            </div>

            <!-- View: Conversation Header -->
            <div x-show="view === 'conversation'" class="flex items-center gap-2.5 min-w-0 flex-1">
                <button type="button" 
                        @click="backToInbox()" 
                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs shrink-0 cursor-pointer transition">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <template x-if="activeContact">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <img :src="activeContact.avatar_url || '{{ asset('images/default-avatar.svg') }}'" 
                             :alt="activeContact.name" 
                             class="w-9 h-9 rounded-2xl object-cover ring-2 ring-sky-300 shrink-0 bg-white">
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-black text-slate-950 truncate leading-tight" x-text="activeContact.name"></h4>
                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500 truncate">
                                <span class="text-sky-700 font-black" x-text="activeContact.role_label"></span>
                                <span>•</span>
                                <span class="truncate" x-text="activeContact.school_name"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Close Button -->
            <button type="button" 
                    @click="close()" 
                    class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 flex items-center justify-center text-xs shrink-0 ml-2 cursor-pointer transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ── BODY VIEW 1: INBOX & SEARCH CONTACTS ── --}}
        <div x-show="view === 'inbox'" class="flex-1 flex flex-col overflow-hidden">
            <!-- Search User Input -->
            <div class="p-3 bg-white/70 border-b border-[#f1e8dc] shrink-0">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="searchUsers()" 
                           placeholder="Cari warga untuk bisik..."
                           class="w-full pl-9 pr-8 py-2 rounded-2xl bg-white border-2 border-[#f1e8dc] text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-sky-300 shadow-2xs">
                    <span class="absolute left-3 top-2.5 text-xs text-slate-400 pointer-events-none">🔍</span>
                    <button type="button" 
                            x-show="searchQuery.length > 0" 
                            @click="searchQuery = ''; searchResults = []" 
                            class="absolute right-2.5 top-2 text-xs text-slate-400 hover:text-rose-500 font-black cursor-pointer">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Search Results Mode -->
            <div x-show="searchQuery.trim().length >= 2" class="flex-1 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                <div class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Hasil Pencarian Warga</div>
                <template x-if="isSearching">
                    <div class="p-6 text-center text-xs font-bold text-slate-400">
                        <i class="fas fa-spinner fa-spin mr-1.5 text-sky-600"></i> Mencari warga...
                    </div>
                </template>
                <template x-for="usr in searchResults" :key="'sr-' + usr.id">
                    <button type="button" 
                            @click="selectUserToChat(usr)"
                            class="w-full text-left p-2.5 rounded-2xl bg-white hover:bg-sky-50 border-2 border-transparent hover:border-sky-200 transition flex items-center justify-between gap-2.5 cursor-pointer shadow-2xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img :src="usr.avatar_url" class="w-10 h-10 rounded-2xl object-cover ring-2 ring-slate-100 shrink-0">
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-950 truncate" x-text="usr.name"></div>
                                <div class="text-[10px] font-bold text-slate-500 flex items-center gap-1.5 truncate">
                                    <span class="text-sky-700 font-extrabold" x-text="usr.role_label"></span>
                                    <span>•</span>
                                    <span class="truncate" x-text="usr.school_name"></span>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 px-2.5 py-1 rounded-xl bg-sky-100 text-sky-800 text-[10px] font-black border border-sky-300">
                            Bisik 💬
                        </div>
                    </button>
                </template>
                <template x-if="!isSearching && searchResults.length === 0 && searchQuery.trim().length >= 2">
                    <div class="p-8 text-center text-xs font-bold text-slate-500">
                        Tidak ada warga Pembda yang cocok dengan kata kunci tersebut.
                    </div>
                </template>
            </div>

            <!-- Normal Threads List Mode -->
            <div x-show="searchQuery.trim().length < 2" class="flex-1 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                <template x-if="isLoadingThreads">
                    <div class="p-8 text-center text-xs font-bold text-slate-400">
                        <i class="fas fa-spinner fa-spin mr-1.5 text-sky-600"></i> Memuat percakapan...
                    </div>
                </template>

                <template x-for="t in threads" :key="'thread-' + t.contact.id">
                    <button type="button" 
                            @click="selectUserToChat(t.contact)"
                            class="w-full text-left p-3 rounded-2xl bg-white hover:bg-sky-50/80 border-2 border-slate-100 hover:border-sky-200 transition flex items-center justify-between gap-2.5 cursor-pointer shadow-2xs group">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="relative shrink-0">
                                <img :src="t.contact.avatar_url" class="w-11 h-11 rounded-2xl object-cover ring-2 ring-sky-200">
                                <template x-if="t.unread_count > 0">
                                    <span class="absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 rounded-full bg-rose-500 text-white text-[9px] font-black flex items-center justify-center ring-2 ring-white animate-pulse"
                                          x-text="t.unread_count"></span>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <h4 class="text-xs font-black text-slate-950 truncate group-hover:text-sky-700 transition" x-text="t.contact.name"></h4>
                                    <span class="text-[9px] font-bold text-slate-400 shrink-0" x-text="t.last_message.time"></span>
                                </div>
                                <p class="text-[11px] font-semibold text-slate-600 truncate leading-tight" 
                                   :class="t.unread_count > 0 ? 'text-slate-950 font-black' : ''">
                                    <span x-show="t.last_message.is_me" class="text-sky-600 font-bold">Anda: </span>
                                    <span x-text="t.last_message.text || 'Mulai percakapan...'"></span>
                                </p>
                            </div>
                        </div>
                    </button>
                </template>

                <template x-if="!isLoadingThreads && threads.length === 0">
                    <div class="p-8 text-center my-auto">
                        <span class="text-3xl block mb-2">✉️</span>
                        <h4 class="text-xs font-black text-slate-900 mb-1">Belum Ada Obrolan</h4>
                        <p class="text-[11px] font-semibold text-slate-500 max-w-[240px] mx-auto mb-3">
                            Ketik nama teman sekelas, guru, atau siswa pada kolom pencarian di atas untuk memulai DM!
                        </p>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── BODY VIEW 2: ACTIVE CONVERSATION MESSAGES ── --}}
        <div x-show="view === 'conversation'" class="flex-1 flex flex-col overflow-hidden bg-slate-50/60">
            <!-- Messages Scrollable Box -->
            <div x-ref="chatBox" class="flex-1 overflow-y-auto p-3.5 space-y-3 custom-scrollbar">
                <template x-if="isLoadingMessages">
                    <div class="p-8 text-center text-xs font-bold text-slate-400">
                        <i class="fas fa-spinner fa-spin mr-1.5 text-sky-600"></i> Membuka riwayat obrolan...
                    </div>
                </template>

                <template x-if="!isLoadingMessages && messages.length === 0">
                    <div class="p-6 text-center my-auto bg-white/70 rounded-3xl border-2 border-dashed border-sky-200">
                        <span class="text-3xl block mb-2">👋</span>
                        <h4 class="text-xs font-black text-slate-900 mb-0.5">Sapa Sekarang!</h4>
                        <p class="text-[11px] font-semibold text-slate-600">
                            Mulai obrolan ramah, tanyakan pelajaran, apresiasi karyanya, atau ajak kolaborasi project!
                        </p>
                    </div>
                </template>

                <template x-for="m in messages" :key="'msg-' + m.id">
                    <div class="flex items-end gap-2" :class="m.is_me ? 'justify-end' : 'justify-start'">
                        <!-- Receiver avatar if not me -->
                        <template x-if="!m.is_me">
                            <img :src="activeContact?.avatar_url || '{{ asset('images/default-avatar.svg') }}'" 
                                 class="w-6 h-6 rounded-lg object-cover mb-1 shrink-0">
                        </template>

                        <!-- Bubble -->
                        <div class="max-w-[78%] rounded-2xl px-3.5 py-2.5 text-xs shadow-2xs relative leading-relaxed"
                             :class="m.is_me 
                                ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-br-none font-bold' 
                                : 'bg-white text-slate-950 border-2 border-[#f1e8dc] rounded-bl-none font-semibold'">
                            <p class="break-words whitespace-pre-wrap select-text" x-text="m.message"></p>
                            <div class="text-[9px] mt-1 flex items-center justify-end gap-1 opacity-75"
                                 :class="m.is_me ? 'text-sky-100' : 'text-slate-400'">
                                <span x-text="m.time"></span>
                                <template x-if="m.is_me">
                                    <i class="fas fa-check-double text-[8px]"></i>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Chat Input Footer -->
            <div class="p-3 bg-white border-t-2 border-[#f1e8dc] shrink-0">
                <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                    <div class="flex-1 relative">
                        <textarea x-model="newMessage" 
                                  @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                  placeholder="Tulis pesan ramah langsung... (Enter untuk kirim)" 
                                  rows="1"
                                  class="w-full px-3.5 py-2 rounded-2xl bg-slate-50 border-2 border-[#f1e8dc] text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-sky-400 focus:bg-white resize-none shadow-inner max-h-24 overflow-y-auto"></textarea>
                    </div>
                    <button type="submit" 
                            :disabled="isSending || !newMessage.trim()"
                            class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white flex items-center justify-center text-sm shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer shrink-0 active:scale-95">
                        <template x-if="isSending">
                            <i class="fas fa-spinner fa-spin"></i>
                        </template>
                        <template x-if="!isSending">
                            <i class="fas fa-paper-plane"></i>
                        </template>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endauth
</script>

@yield('scripts')
</body>
</html>
