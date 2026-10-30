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

        {{-- Bilah Pencarian Gelora (Search Bar Pastel) --}}
        <form action="{{ route('circle.feed') }}" method="GET" class="hidden lg:flex items-center relative min-w-[200px] max-w-[260px]">
            @if(request('school'))
                <input type="hidden" name="school" value="{{ request('school') }}">
            @endif
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari karya, siswa, keahlian..."
                   class="w-full pl-8 pr-7 py-1.5 rounded-2xl bg-white border-2 border-[#f1e8dc] text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-300 shadow-2xs">
            <span class="absolute left-2.5 text-xs text-slate-400 pointer-events-none">🔍</span>
            @if(request('q'))
                <a href="{{ route('circle.feed', array_filter(['school' => request('school'), 'tab' => request('tab')])) }}" 
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
                            title="Genta Kabar PembdaCIRCLE">
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
                                <span class="text-xs font-black uppercase tracking-wider text-slate-950">Genta Kabar</span>
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
                                    }
                                @endphp
                                <a href="{{ $notifUrl }}" 
                                   class="block p-3 hover:bg-pink-50/60 transition {{ !$notif->is_read ? 'bg-amber-50/50' : '' }}">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-xl shrink-0 flex items-center justify-center text-xs shadow-2xs {{ str_contains($notif->type ?? '', 'endorsement') ? 'bg-amber-100 text-amber-800 border border-amber-300' : (str_contains($notif->type ?? '', 'reaction') ? 'bg-orange-100 text-orange-800 border border-orange-300' : 'bg-pink-100 text-pink-800 border border-pink-300') }}">
                                            @if(str_contains($notif->type ?? '', 'endorsement'))
                                                🏅
                                            @elseif(str_contains($notif->type ?? '', 'reaction'))
                                                🔥
                                            @elseif(str_contains($notif->type ?? '', 'comment'))
                                                💬
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
</script>

@yield('scripts')
</body>
</html>
