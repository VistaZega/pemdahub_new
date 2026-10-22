@extends(auth()->user()->layout)

@section('title', 'PembdaSPACE - Community & STEAM Lounge')

@section('content')
<!-- Dynamic Google Fonts (Plus Jakarta Sans & JetBrains Mono) & FontAwesome 6 Pro -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
{{-- Alpine.js sudah dimuat oleh layout parent (layouts/app.blade.php) — JANGAN duplikat di sini --}}

<!-- PWA Manifest & App Shell Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#4f46e5">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="PembdaSPACE">
<link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/sw.js').then(function(reg) {
        console.log('PembdaSPACE PWA Registered:', reg.scope);
      }).catch(function(err) {
        console.log('PembdaSPACE PWA Registration Failed:', err);
      });
    });
  }
</script>

<script>
  if (typeof tailwind !== 'undefined') {
    tailwind.config = {
      corePlugins: {
        preflight: false,
      }
    }
  }
</script>

<style>
    :root {
        font-family: 'Plus Jakarta Sans', sans-serif;
        --bg-warm: #faf7f2;
        --text-dark: #121316;
        --brand-red: #ff3823;
        --brand-yellow: #fde047;
        --brand-emerald: #10b981;
        --brand-blue: #2563eb;
        --brand-purple: #7c3aed;
        --border-ink: #121316;
    }

    .forum-hdr { font-family: 'Plus Jakarta Sans', sans-serif; }
    .font-mono-code { font-family: 'JetBrains Mono', monospace; }
    .no-scrollbar::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    .no-scrollbar { -ms-overflow-style: none !important; scrollbar-width: none !important; }

    /* Yellow Highlighter Stroke Effect */
    .highlight-marker {
        background: linear-gradient(180deg, transparent 62%, #fde047 62%, #fde047 92%, transparent 92%);
        display: inline;
    }

    /* Tactile Solid Drop-Shadow Buttons */
    .btn-tactile-red {
        background: #ff3823;
        color: #ffffff;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-tactile-red:hover {
        transform: translate(1px, 1px);
        box-shadow: 2px 2px 0px #121316;
    }
    .btn-tactile-red:active {
        transform: translate(3px, 3px);
        box-shadow: 0px 0px 0px #121316;
    }

    .btn-tactile-yellow {
        background: #fde047;
        color: #121316;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-tactile-yellow:hover {
        transform: translate(1px, 1px);
        box-shadow: 2px 2px 0px #121316;
    }
    .btn-tactile-yellow:active {
        transform: translate(3px, 3px);
        box-shadow: 0px 0px 0px #121316;
    }

    .btn-tactile-white {
        background: #ffffff;
        color: #121316;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-tactile-white:hover {
        background: #faf8f5;
        transform: translate(1px, 1px);
        box-shadow: 2px 2px 0px #121316;
    }
    .btn-tactile-white:active {
        transform: translate(3px, 3px);
        box-shadow: 0px 0px 0px #121316;
    }

    .btn-tactile-emerald {
        background: #10b981;
        color: #ffffff;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-tactile-emerald:hover {
        transform: translate(1px, 1px);
        box-shadow: 2px 2px 0px #121316;
    }
    .btn-tactile-emerald:active {
        transform: translate(3px, 3px);
        box-shadow: 0px 0px 0px #121316;
    }

    /* Vibrant Teen Graph Canvas Background */
    .graph-canvas {
        background-color: #faf7f2;
        background-image: 
            radial-gradient(circle at 10% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 90% 85%, rgba(236, 72, 153, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 50% 50%, rgba(245, 158, 11, 0.05) 0%, transparent 60%),
            radial-gradient(#d1cfc7 1.2px, transparent 1.2px);
        background-size: auto, auto, auto, 22px 22px;
    }

    /* Sidebar Active Channel */
    .channel-active-tactile {
        background-color: #fde047 !important;
        color: #121316 !important;
        border: 2px solid #121316 !important;
        box-shadow: 2px 2px 0px #121316 !important;
        font-weight: 800 !important;
    }
    .channel-hover-tactile {
        border: 2px solid transparent;
        transition: all 0.15s ease;
    }
    .channel-hover-tactile:hover {
        background-color: #f3f0e8 !important;
        border: 2px solid #121316 !important;
        box-shadow: 2px 2px 0px #121316 !important;
        color: #121316 !important;
    }

    /* Tactile Card Neo-Brutalist with Youth Polish */
    .tactile-card {
        background: #ffffff;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        border-radius: 1.25rem;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .tactile-card:hover {
        transform: translate(-1px, -1px);
        box-shadow: 5px 5px 0px #121316;
    }

    /* Responsiveness: Auto-hide right sidebar on laptop screens (<1536px) if global app sidebar is open */
    @media (max-width: 1535px) {
        aside:not(.collapsed) ~ #main-content .forum-right-sidebar {
            display: none !important;
        }
    }
</style>

@php
    // Metadata Icon & Visual Warna-Warni Remaja Canggih FontAwesome 6
    $channelMeta = [
        'steam_challenge' => [
            'icon' => 'fa-solid fa-bullseye',
            'pill' => 'bg-rose-100 text-rose-600 border-rose-200',
            'badge' => 'bg-rose-50 text-rose-800 border-rose-300',
            'label' => 'Tantangan STEAM & Inovasi',
            'accent' => '#e11d48'
        ],
        'edutech_radar' => [
            'icon' => 'fa-solid fa-bolt',
            'pill' => 'bg-amber-100 text-amber-600 border-amber-200',
            'badge' => 'bg-amber-50 text-amber-800 border-amber-300',
            'label' => 'Radar Edukasi & Teknologi',
            'accent' => '#f59e0b'
        ],
        'diskusi' => [
            'icon' => 'fa-solid fa-comments',
            'pill' => 'bg-blue-100 text-blue-600 border-blue-200',
            'badge' => 'bg-blue-50 text-blue-800 border-blue-300',
            'label' => 'Wall Diskusi Publik',
            'accent' => '#3b82f6'
        ],
        'info' => [
            'icon' => 'fa-solid fa-bullhorn',
            'pill' => 'bg-rose-100 text-rose-600 border-rose-200',
            'badge' => 'bg-rose-50 text-rose-800 border-rose-300',
            'label' => 'Wall Pengumuman',
            'accent' => '#f43f5e'
        ],
        'ekskul_showcase' => [
            'icon' => 'fa-solid fa-trophy',
            'pill' => 'bg-amber-100 text-amber-600 border-amber-200',
            'badge' => 'bg-amber-50 text-amber-800 border-amber-300',
            'label' => 'Ekskul & Prestasi',
            'accent' => '#f59e0b'
        ],
        'ekskul_recruit' => [
            'icon' => 'fa-solid fa-user-plus',
            'pill' => 'bg-teal-100 text-teal-600 border-teal-200',
            'badge' => 'bg-teal-50 text-teal-800 border-teal-300',
            'label' => 'Open Recruitment Ekskul',
            'accent' => '#14b8a6'
        ],
        'tanya_jawab' => [
            'icon' => 'fa-solid fa-circle-question',
            'pill' => 'bg-indigo-100 text-indigo-600 border-indigo-200',
            'badge' => 'bg-indigo-50 text-indigo-800 border-indigo-300',
            'label' => 'Tanya Jawab & PR',
            'accent' => '#6366f1'
        ],
        'sharing' => [
            'icon' => 'fa-solid fa-folder-open',
            'pill' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
            'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
            'label' => 'Bank File & Modul',
            'accent' => '#10b981'
        ],
        'art_gallery' => [
            'icon' => 'fa-solid fa-palette',
            'pill' => 'bg-fuchsia-100 text-fuchsia-600 border-fuchsia-200',
            'badge' => 'bg-fuchsia-50 text-fuchsia-800 border-fuchsia-300',
            'label' => 'Karya Seni & Desain',
            'accent' => '#d946ef'
        ],
        'talent' => [
            'icon' => 'fa-solid fa-music',
            'pill' => 'bg-purple-100 text-purple-600 border-purple-200',
            'badge' => 'bg-purple-50 text-purple-800 border-purple-300',
            'label' => 'Musik & Bakat',
            'accent' => '#a855f7'
        ],
        'performance' => [
            'icon' => 'fa-solid fa-award',
            'pill' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-300',
            'label' => 'Hall of Fame',
            'accent' => '#eab308'
        ],
        'gaming' => [
            'icon' => 'fa-solid fa-gamepad',
            'pill' => 'bg-cyan-100 text-cyan-600 border-cyan-200',
            'badge' => 'bg-cyan-50 text-cyan-800 border-cyan-300',
            'label' => 'Gaming & Mabar',
            'accent' => '#06b6d4'
        ],
        'trending' => [
            'icon' => 'fa-solid fa-fire-flame-curved',
            'pill' => 'bg-orange-100 text-orange-600 border-orange-200',
            'badge' => 'bg-orange-50 text-orange-800 border-orange-300',
            'label' => 'Trending & Viral',
            'accent' => '#f97316'
        ],
        'project_idea' => [
            'icon' => 'fa-solid fa-lightbulb',
            'pill' => 'bg-amber-100 text-amber-600 border-amber-200',
            'badge' => 'bg-amber-50 text-amber-800 border-amber-300',
            'label' => 'Ide Proyek & STEAM',
            'accent' => '#f59e0b'
        ],
        'committee' => [
            'icon' => 'fa-solid fa-users-gear',
            'pill' => 'bg-sky-100 text-sky-600 border-sky-200',
            'badge' => 'bg-sky-50 text-sky-800 border-sky-300',
            'label' => 'Rekrut Panitia & Tim',
            'accent' => '#0ea5e9'
        ],
        'charity' => [
            'icon' => 'fa-solid fa-hand-holding-heart',
            'pill' => 'bg-rose-100 text-rose-600 border-rose-200',
            'badge' => 'bg-rose-50 text-rose-800 border-rose-300',
            'label' => 'Aksi Sosial & Peduli',
            'accent' => '#f43f5e'
        ],
    ];

    $groupMeta = [
        'WALL OBROLAN' => ['icon' => 'fa-solid fa-comments', 'color' => 'text-blue-500'],
        'EKSTRAKURIKULER' => ['icon' => 'fa-solid fa-trophy', 'color' => 'text-amber-500'],
        'AKADEMIK' => ['icon' => 'fa-solid fa-graduation-cap', 'color' => 'text-emerald-500'],
        'SHOWCASE' => ['icon' => 'fa-solid fa-sparkles', 'color' => 'text-fuchsia-500'],
        'HANGOUT' => ['icon' => 'fa-solid fa-gamepad', 'color' => 'text-cyan-500'],
        'KOLABORASI' => ['icon' => 'fa-solid fa-handshake-angle', 'color' => 'text-orange-500'],
    ];
@endphp

<!-- App Window Wrapper (Embedded in Global Layout) -->
<div class="w-full text-[#121316] font-['Plus_Jakarta_Sans',sans-serif] rounded-3xl overflow-hidden border-2 border-[#121316] shadow-[6px_6px_0px_#121316] bg-[#faf7f2]" style="min-height: 85vh;">
    
    <div class="flex flex-col md:flex-row h-full w-full" 
         x-data="{ 
             mobileSidebarOpen: false, 
             leftSidebarOpen: {{ request('view') === 'challenge' ? 'false' : 'true' }}, 
             rightSidebarOpen: {{ request('view') === 'challenge' ? 'false' : 'true' }} 
         }"
         @toggle-theatre-mode.window="leftSidebarOpen = !leftSidebarOpen; rightSidebarOpen = leftSidebarOpen;"
         @toggle-left-sidebar.window="leftSidebarOpen = !leftSidebarOpen"
         @toggle-right-sidebar.window="rightSidebarOpen = !rightSidebarOpen">
        
        <!-- Mobile Header & Toggle -->
        <div class="md:hidden flex items-center justify-between bg-white p-4 border-b-2 border-[#121316]">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-violet-600 via-indigo-600 to-pink-500 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center flex-shrink-0 text-white font-black text-lg">
                    <i class="fa-solid fa-bolt text-yellow-300 text-base"></i>
                </div>
                <div class="flex flex-col justify-center min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="forum-hdr text-base font-black text-[#121316] tracking-tight leading-tight m-0 p-0 whitespace-nowrap">PembdaSPACE</h1>
                        <span class="text-[9px] font-mono-code font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400 flex items-center gap-1 shadow-xs whitespace-nowrap shrink-0" title="{{ $onlineCount }} akun aktif di seluruh PembdaHUB">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $onlineCount }} Online</span>
                        </span>
                    </div>
                    <span class="text-[9px] text-indigo-600 font-mono-code font-black uppercase tracking-wider leading-none mt-0.5 whitespace-nowrap">COMMUNITY & STEAM LOUNGE</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('forum.index', ['view' => 'challenge']) }}" 
                   class="px-2.5 py-1.5 bg-[#fde047] border-2 border-[#121316] rounded-xl text-[#121316] shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition flex items-center gap-1.5 text-xs font-black shrink-0"
                   title="Buka PembdaCHALLENGE">
                    <span>⚡</span>
                    <span class="text-[10px] font-mono-code font-black whitespace-nowrap">CHALLENGE</span>
                    <span class="text-[8px] px-1 py-0.2 bg-purple-800 text-white rounded-md font-mono-code font-bold">+50</span>
                </a>
                <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="p-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        <!-- CHANNEL SIDEBAR (Left) - Bisa Buka/Tutup -->
        <div x-show="leftSidebarOpen || mobileSidebarOpen"
             :class="mobileSidebarOpen ? 'block' : (leftSidebarOpen ? 'md:block' : 'hidden')"
             class="w-full md:w-64 lg:w-64 bg-white border-r-2 border-[#121316] flex-shrink-0 flex flex-col transition-all duration-300 relative z-20">
            
            <div class="p-3.5 sm:p-4 h-full flex flex-col gap-4 max-h-[85vh] overflow-y-auto no-scrollbar">
                <!-- Sleek Compact Header & Logo (Desktop) -->
                <div class="hidden md:flex items-center justify-between gap-1.5 pb-3.5 border-b-2 border-[#121316]/10 px-0.5">
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-violet-600 via-indigo-600 to-pink-500 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center shrink-0 text-white font-black">
                            <i class="fa-solid fa-bolt text-yellow-300 text-sm"></i>
                        </div>
                        <div class="flex flex-col justify-center shrink-0">
                            <h1 class="forum-hdr text-sm font-black text-[#121316] tracking-tight leading-tight m-0 p-0 whitespace-nowrap">PembdaSPACE</h1>
                            <span class="text-[9px] text-indigo-600 font-mono-code font-black uppercase tracking-wider leading-none mt-0.5 whitespace-nowrap">COMMUNITY & STEAM</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <span class="text-[9px] font-mono-code font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400 flex items-center gap-1 shadow-xs whitespace-nowrap cursor-default" 
                              title="{{ $onlineCount }} akun aktif di seluruh sistem PembdaHUB">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $onlineCount }}</span>
                        </span>
                        <!-- Tombol Tutup Menu Saluran Kiri -->
                        <button type="button" 
                                @click="leftSidebarOpen = false" 
                                class="w-7 h-7 rounded-lg bg-[#faf8f5] hover:bg-slate-200 border border-[#121316] text-[#121316] flex items-center justify-center text-xs transition" 
                                title="Sembunyikan Menu Saluran (Perluas Layar)">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <nav class="flex-1 space-y-5">
                    <!-- All Channels (Semua Saluran) -->
                    <div class="space-y-1">
                        <a href="{{ route('forum.index', array_filter(['search' => $search])) }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-150 {{ !$category && !(isset($activeGroup) && $activeGroup) && request('view') !== 'challenge' ? 'channel-active-tactile' : 'text-[#121316] font-bold channel-hover-tactile' }}">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs shrink-0 {{ !$category && !(isset($activeGroup) && $activeGroup) && request('view') !== 'challenge' ? 'bg-[#121316] text-[#fde047]' : 'bg-indigo-100 text-indigo-600 border border-indigo-200' }}">
                                    <i class="fa-solid fa-compass"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-extrabold">Semua Saluran</span>
                            </div>
                            <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border {{ !$category && !(isset($activeGroup) && $activeGroup) && request('view') !== 'challenge' ? 'border-slate-900' : 'border-slate-300' }}" style="{{ !$category && !(isset($activeGroup) && $activeGroup) && request('view') !== 'challenge' ? 'background-color: #0f172a !important; color: #fde047 !important;' : 'background-color: #ffffff !important; color: #0f172a !important;' }}">
                                {{ $totalThreads }}
                            </span>
                        </a>

                        <!-- Menu Khusus: PembdaCHALLENGE Arena Game & Logika (Anti-Terpotong) -->
                        <a href="{{ route('forum.index', ['view' => 'challenge']) }}" 
                           @click="if (window.innerWidth < 768) mobileSidebarOpen = false;" 
                           class="block p-2.5 rounded-xl transition-all duration-150 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none group cursor-pointer {{ request('view') === 'challenge' ? 'bg-[#121316] text-[#fde047]' : 'bg-gradient-to-r from-amber-200 via-yellow-200 to-amber-100 text-[#121316]' }}">
                            <!-- Baris 1: Ikon + Nama Utuh Bebas Hambatan (100% Tidak Terpotong) -->
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs shrink-0 bg-[#121316] text-[#fde047] shadow-xs group-hover:scale-110 transition-transform">
                                    ⚡
                                </div>
                                <span class="text-xs font-black tracking-tight whitespace-nowrap {{ request('view') === 'challenge' ? 'text-[#fde047]' : 'text-[#121316]' }}">
                                    PembdaCHALLENGE
                                </span>
                            </div>
                            <!-- Baris 2: Sub-keterangan & Badge Poin -->
                            <div class="flex items-center justify-between text-[10px] font-mono-code font-bold pl-8">
                                <span class="{{ request('view') === 'challenge' ? 'text-amber-300' : 'text-indigo-900' }} truncate">
                                    Game Sains & Logika
                                </span>
                                <span class="text-[9px] font-mono-code font-black px-1.5 py-0.2 rounded-md bg-purple-700 text-white shadow-xs shrink-0 ml-1">
                                    +50 Poin
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- Class Squads (Rombel & Guru) -->
                    @if(isset($userGroups) && count($userGroups) > 0)
                        <div class="space-y-1.5" x-data="{ expanded: true }">
                            <button @click="expanded = !expanded" class="w-full flex items-center justify-between px-2 py-1 text-[11px] font-mono-code font-black text-[#121316] uppercase tracking-wider group">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-md bg-purple-100 text-purple-600 border border-purple-200 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-users-rectangle"></i>
                                    </span>
                                    <span>Class Squads</span>
                                </span>
                                <i class="fa-solid fa-chevron-down text-[#121316] text-[10px] transition-transform duration-200" :class="expanded ? '' : '-rotate-90'"></i>
                            </button>
                            <div x-show="expanded" class="space-y-1">
                                @foreach($userGroups as $grp)
                                    @php
                                        $grpName = $grp->name ?? 'Class Squad';
                                        $cleanGrpName = trim(preg_replace('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\s]+/u', '', $grpName));
                                        $memberCount = $grp->calculated_member_count ?? count($grp->members ?? []);
                                        $isGroupActive = isset($activeGroup) && $activeGroup && $activeGroup->id === $grp->id;
                                        
                                        // Tentukan icon canggih FontAwesome
                                        $squadFa = match(true) {
                                            str_contains(strtolower($grpName), 'lobi') => ['fa-solid fa-school', 'bg-purple-100 text-purple-600 border-purple-200'],
                                            str_contains(strtolower($grpName), 'guru') => ['fa-solid fa-chalkboard-user', 'bg-emerald-100 text-emerald-600 border-emerald-200'],
                                            default => ['fa-solid fa-users', 'bg-sky-100 text-sky-600 border-sky-200']
                                        };
                                    @endphp
                                    <a href="{{ route('forum.index', ['group' => $grp->id]) }}" 
                                       title="{{ $cleanGrpName }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-150 {{ $isGroupActive ? 'channel-active-tactile' : 'text-[#121316] font-bold channel-hover-tactile' }} group">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs shrink-0 {{ $isGroupActive ? 'bg-[#121316] text-[#fde047]' : $squadFa[1] }}">
                                                <i class="{{ $squadFa[0] }}"></i>
                                            </div>
                                            <span class="text-xs truncate font-extrabold leading-tight">{{ $cleanGrpName }}</span>
                                        </div>
                                        @if($memberCount > 0)
                                            <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border {{ $isGroupActive ? 'border-slate-900' : 'border-slate-300' }} shrink-0 ml-1.5" style="{{ $isGroupActive ? 'background-color: #0f172a !important; color: #fde047 !important;' : 'background-color: #ffffff !important; color: #0f172a !important;' }}">{{ $memberCount }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Categorized Channels -->
                    @foreach($channelGroups as $groupName => $channels)
                    @php
                        $cleanGroupName = trim(preg_replace('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\s]+/u', '', $groupName));
                        $gMeta = $groupMeta[$cleanGroupName] ?? ['icon' => 'fa-solid fa-folder', 'color' => 'text-slate-500'];
                    @endphp
                    <div class="space-y-1.5" x-data="{ expanded: true }">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between px-2 py-1 text-[11px] font-mono-code font-black text-[#121316] uppercase tracking-wider group">
                            <span class="flex items-center gap-1.5">
                                <i class="{{ $gMeta['icon'] }} {{ $gMeta['color'] }} text-xs"></i>
                                <span>{{ $cleanGroupName }}</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-[#121316] text-[10px] transition-transform duration-200" :class="expanded ? '' : '-rotate-90'"></i>
                        </button>
                        <div x-show="expanded" class="space-y-1">
                            @foreach($channels as $catKey)
                                @php
                                    $meta = $channelMeta[$catKey] ?? [
                                        'icon' => 'fa-solid fa-hashtag',
                                        'pill' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        'label' => $catKey
                                    ];
                                    $catLabel = $meta['label'];
                                    $isActive = $category === $catKey;
                                    $count = $counts[$catKey] ?? 0;
                                @endphp
                                <a href="{{ route('forum.index', array_filter(['category' => $catKey, 'search' => $search])) }}" 
                                   class="flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-150 {{ $isActive ? 'channel-active-tactile' : 'text-[#121316] font-bold channel-hover-tactile' }}">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs shrink-0 {{ $isActive ? 'bg-[#121316] text-[#fde047]' : $meta['pill'] }}">
                                            <i class="{{ $meta['icon'] }}"></i>
                                        </div>
                                        <span class="text-xs truncate font-extrabold">{{ $catLabel }}</span>
                                    </div>
                                    @if($count > 0)
                                        <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border {{ $isActive ? 'border-slate-900' : 'border-slate-300' }}" style="{{ $isActive ? 'background-color: #0f172a !important; color: #fde047 !important;' : 'background-color: #ffffff !important; color: #0f172a !important;' }}">{{ $count }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </nav>

                <!-- Stats Footer -->
                <div class="mt-auto pt-3 border-t-2 border-[#121316]/10 pb-1 font-mono-code">
                    <div class="flex justify-between items-center px-1 text-[11px] font-bold text-[#121316]">
                        <span class="text-[11px] text-[#666] font-extrabold flex items-center gap-1.5">
                            <i class="fa-solid fa-comments text-indigo-600"></i> Total Diskusi
                        </span>
                        <div class="px-2 py-0.5 bg-[#faf8f5] border border-[#121316] rounded-md font-black">{{ $totalThreads }} Topik</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN FEED (Center) -->
        <div id="forum-main-feed" class="flex-1 flex flex-col min-w-0 w-full graph-canvas p-3.5 sm:p-5 lg:p-6 max-h-[85vh] overflow-y-auto no-scrollbar">
            
            <!-- QUICK DOCK: TOMBOL BUKA/TUTUP SIDEBAR (MUNCUL JIKA SIDEBAR DITUTUP ATAU MODE GAME) -->
            <div class="mb-3 flex items-center justify-between gap-2 z-10 select-none" x-show="!leftSidebarOpen || !rightSidebarOpen || '{{ request('view') }}' === 'challenge'">
                <!-- Kiri: Buka Menu Saluran -->
                <div class="flex items-center gap-2">
                    <button type="button" 
                            x-show="!leftSidebarOpen" 
                            @click="leftSidebarOpen = true" 
                            class="btn-tactile-white px-2.5 py-1 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#121316] hover:bg-yellow-50"
                            title="Buka Menu Saluran Kiri">
                        <i class="fa-solid fa-bars text-indigo-600"></i>
                        <span class="text-[11px] font-mono-code font-bold">Saluran</span>
                    </button>

                    <!-- Toggle Mode Layar Penuh Arena Game -->
                    @if(request('view') === 'challenge')
                        <button type="button" 
                                @click="leftSidebarOpen = !leftSidebarOpen; rightSidebarOpen = leftSidebarOpen;" 
                                class="btn-tactile-yellow px-2.5 py-1 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#121316]"
                                title="Buka / Tutup Layar Penuh Arena Game">
                            <i class="fa-solid" :class="(!leftSidebarOpen && !rightSidebarOpen) ? 'fa-compress text-purple-800' : 'fa-expand text-slate-900'"></i>
                            <span class="text-[11px] font-mono-code font-black" x-text="(!leftSidebarOpen && !rightSidebarOpen) ? 'Tampilkan Menu Samping' : 'Mode Layar Penuh (Fokus Game)'"></span>
                        </button>
                    @endif
                </div>

                <!-- Kanan: Buka Widget Leaderboard & Profil -->
                <div class="flex items-center gap-2">
                    <button type="button" 
                            x-show="!rightSidebarOpen" 
                            @click="rightSidebarOpen = true" 
                            class="btn-tactile-white px-2.5 py-1 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#121316] hover:bg-yellow-50"
                            title="Buka Leaderboard & Profil Kanan">
                        <span class="text-[11px] font-mono-code font-bold">Leaderboard</span>
                        <i class="fa-solid fa-trophy text-amber-500"></i>
                    </button>
                </div>
            </div>

            @if(request('view') === 'challenge')
                <!-- Arena Permainan PembdaCHALLENGE Utama (Fokus Penuh pada Layar) -->
                @include('forum.partials.pembda_challenge')
            @else
            @if(isset($activeGroup) && $activeGroup)
                @php
                    $isEkskulGroup = $activeGroup->type === 'extracurricular' || $activeGroup->extracurricular;
                    $ekskulModel = $activeGroup->extracurricular;
                    $memberCount = $ekskulModel ? ($ekskulModel->activeMembers?->count() ?? count($activeGroup->members)) : count($activeGroup->members);
                @endphp
                <!-- SQUAD LOUNGE COLORFUL CARD -->
                <div class="mb-6 rounded-2xl p-5 bg-white border-2 border-[#121316] shadow-[4px_4px_0px_#121316]">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                        <div class="flex items-start gap-4 min-w-0 flex-1">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-400 to-yellow-300 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-2xl text-[#121316] shrink-0 font-black">
                                <i class="fa-solid fa-users-rectangle"></i>
                            </div>

                            <div class="min-w-0 space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse mr-1.5 shrink-0"></span>
                                        <span>{{ $isEkskulGroup ? 'SQUAD LOUNGE' : 'CLASS SQUAD' }}</span>
                                    </span>
                                    @if($isEkskulGroup && $ekskulModel)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-[#fde047] text-[#121316] border border-[#121316]">
                                            <i class="fa-solid fa-star text-[9px] mr-1 text-amber-900"></i>
                                            <span>{{ $ekskulModel->scope_label ?? 'Unit Ekskul' }}</span>
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-lg sm:text-xl font-black text-[#121316] tracking-tight truncate">
                                    {{ $activeGroup->name }}
                                </h2>
                                <p class="text-xs font-semibold text-slate-600 line-clamp-1">
                                    {{ $activeGroup->description ?: 'Ruang koordinasi dan diskusi resmi anggota squad.' }}
                                </p>

                                <div class="flex items-center gap-2 pt-1 flex-wrap text-xs font-bold">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-900 font-mono-code text-[11px]">
                                        <i class="fa-solid fa-users text-blue-600 text-xs mr-1.5 shrink-0"></i>
                                        <span>{{ $memberCount }} Anggota</span>
                                    </span>
                                    @if($isEkskulGroup && $ekskulModel)
                                        @if($ekskulModel->advisor && trim($ekskulModel->advisor->name ?? ''))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px]">
                                            <i class="fa-solid fa-chalkboard-user text-emerald-600 text-xs mr-1.5 shrink-0"></i>
                                            <span>Pembina: {{ $ekskulModel->advisor->name }}</span>
                                        </span>
                                        @endif
                                        @if($ekskulModel->leader && trim($ekskulModel->leader->full_name ?? ''))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px]">
                                            <i class="fa-solid fa-crown text-amber-600 text-xs mr-1.5 shrink-0"></i>
                                            <span>Ketua: {{ $ekskulModel->leader->full_name }}</span>
                                        </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 w-full md:w-auto pt-2 md:pt-0 justify-end flex-wrap sm:flex-nowrap">
                            <a href="{{ route('forum.create', ['group' => $activeGroup->id]) }}" 
                               class="btn-tactile-red px-4 py-2 rounded-xl text-xs font-black flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                <span>Tulis Post</span>
                            </a>
                            @if($isEkskulGroup)
                            <a href="{{ route('siswa.ekskul.index') }}" 
                               class="btn-tactile-white px-3.5 py-2 rounded-xl text-xs font-black flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fa-solid fa-sitemap text-xs text-blue-600"></i>
                                <span>Roster</span>
                            </a>
                            @endif
                            <a href="{{ route('forum.index') }}" 
                               class="btn-tactile-white px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-1 whitespace-nowrap"
                                title="Kembali ke Lobi Utama">
                                <i class="fa-solid fa-xmark text-xs text-rose-600"></i>
                                <span>Lobi</span>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- HERO LOUNGE PEMBDA SPACE (WARNA-WARNI REMAJA CERAH & RAMAI) -->
                <div class="mb-5 sm:mb-6 rounded-3xl border-2 border-[#121316] bg-gradient-to-r from-violet-600 via-indigo-600 to-fuchsia-600 text-white shadow-[4px_4px_0px_#121316] p-4 sm:p-6 relative">
                    <!-- Ambient Glow Circles -->
                    <div class="absolute -right-8 -bottom-8 w-36 h-36 rounded-full bg-pink-400/20 blur-2xl pointer-events-none"></div>
                    <div class="absolute -left-8 -top-8 w-36 h-36 rounded-full bg-yellow-300/20 blur-2xl pointer-events-none"></div>

                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 sm:gap-5 relative z-10 w-full min-w-0">
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1 bg-[#fde047] border-2 border-[#121316] rounded-full text-[#121316] text-[10px] sm:text-[11px] font-mono-code font-black tracking-wider shadow-[2px_2px_0px_#121316] max-w-full">
                                <i class="fa-solid fa-bolt text-amber-600 shrink-0"></i>
                                <span class="truncate">PEMBDASPACE</span>
                                <span class="hidden sm:inline">·</span>
                                <span class="hidden sm:inline">SOCIAL & STEAM LOUNGE</span>
                            </div>
                            <h2 class="text-lg sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-snug drop-shadow-xs">
                                Ruang Kolaborasi, Diskusi & <span class="bg-[#fde047] text-[#121316] px-2 py-0.5 rounded-lg border border-[#121316] shadow-xs">Karya Nyata.</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-indigo-100 font-medium leading-relaxed max-w-2xl">
                                Wadah seru civitas akademika Perguruan Pembda Nias: Siswa SMA, SMP, SMK & Guru. Diskusi pelajaran, pamer karya sains & seni, dan cari kawan kolaborasi.
                            </p>
                            
                            <!-- Quick Stats Chips -->
                            <div class="flex items-center gap-2 pt-1 flex-wrap text-[11px] font-mono-code font-bold">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl shadow-xs cursor-default" 
                                      title="Akun aktif di seluruh sistem PembdaHUB (LMS, CBT, KBM & PembdaSPACE) dalam 10 menit terakhir"
                                      style="background-color: rgba(15, 23, 42, 0.7) !important; color: #ffffff !important; border: 1.5px solid rgba(255, 255, 255, 0.25) !important;">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>{{ $onlineCount }} <span class="text-yellow-300 font-black">Online di PembdaHUB</span></span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl shadow-xs" style="background-color: rgba(15, 23, 42, 0.7) !important; color: #ffffff !important; border: 1.5px solid rgba(255, 255, 255, 0.25) !important;">
                                    <i class="fa-solid fa-comments text-yellow-300"></i>
                                    <span>{{ $totalThreads }} <span class="text-yellow-300 font-black">Topik</span></span>
                                </span>
                                <a href="{{ route('forum.index', ['view' => 'challenge']) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border-2 border-slate-900 font-black shadow-[2px_2px_0px_#121316] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition cursor-pointer group shrink-0" 
                                   style="background-color: #fde047 !important; color: #0f172a !important;"
                                   title="Klik untuk membuka dan memainkan PembdaCHALLENGE">
                                    <i class="fa-solid fa-brain text-purple-800 group-hover:scale-110 transition-transform"></i>
                                    <span class="whitespace-nowrap">🎮 Main PembdaCHALLENGE</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-purple-800 text-white font-mono-code font-black shrink-0">+50 POIN</span>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-row lg:flex-col items-center lg:items-stretch gap-2 shrink-0 w-full sm:w-auto">
                            <a href="{{ route('forum.create') }}" class="btn-tactile-yellow px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 flex-1 sm:flex-initial whitespace-nowrap shadow-[2px_2px_0px_#121316]">
                                <i class="fa-solid fa-plus-circle text-sm text-rose-600"></i>
                                <span>+ Buat Topik</span>
                            </a>
                            <button onclick="triggerPwaInstall()" class="btn-tactile-white px-3.5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 flex-1 sm:flex-initial whitespace-nowrap shadow-[2px_2px_0px_#121316]">
                                <i class="fa-solid fa-mobile-screen text-xs text-indigo-600"></i>
                                <span>Install App</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border-2 border-rose-600 text-rose-900 flex items-center justify-between shadow-[3px_3px_0px_#e11d48]">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-xl text-rose-600 shrink-0"></i>
                        <span class="text-xs sm:text-sm font-black">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border-2 border-emerald-600 text-emerald-900 flex items-center justify-between shadow-[3px_3px_0px_#059669]">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-xl text-emerald-600 shrink-0"></i>
                        <span class="text-xs sm:text-sm font-black">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Header & Search Topbar (Neo-Brutalist) -->
            <div class="p-3 sm:p-4 mb-5 sm:mb-6 sticky top-0 z-30 flex flex-col sm:flex-row gap-3 items-center justify-between bg-white/95 backdrop-blur-md border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316]">
                <form method="GET" action="{{ route('forum.index') }}" class="w-full sm:max-w-md relative flex-1 min-w-0">
                    @if($category) <input type="hidden" name="category" value="{{ $category }}"> @endif
                    @if(isset($activeGroup) && $activeGroup) <input type="hidden" name="group" value="{{ $activeGroup->id }}"> @endif
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#121316]">
                        <i class="fa-solid fa-magnifying-glass text-slate-500"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" 
                           class="w-full pl-10 pr-4 py-2 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] placeholder-slate-500 shadow-[2px_2px_0px_#121316] focus:outline-none focus:shadow-[3px_3px_0px_#ff3823] transition" 
                           placeholder="{{ isset($activeGroup) && $activeGroup ? 'Cari di ' . $activeGroup->name . '...' : 'Cari obrolan, ide, karya, tugas...' }}">
                </form>
                
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end shrink-0">
                    @if($search || $category || (isset($activeGroup) && $activeGroup))
                        <a href="{{ route('forum.index') }}" class="btn-tactile-white px-3 py-2 rounded-xl text-xs font-black flex items-center gap-1 whitespace-nowrap">
                            <i class="fa-solid fa-xmark text-rose-600"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                    <a href="{{ route('forum.create', (isset($activeGroup) && $activeGroup) ? ['group' => $activeGroup->id] : []) }}" 
                       class="btn-tactile-red px-3.5 sm:px-4 py-2 rounded-xl text-xs font-black flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>{{ isset($activeGroup) && $activeGroup ? 'Tulis' : 'Buat Post' }}</span>
                    </a>
                </div>
            </div>

            <!-- 🎯 TANTANGAN STEAM & INOVASI MINGGUAN -->
            @if(isset($weeklyChallenge))
                <div class="mb-5 sm:mb-6 bg-gradient-to-r from-rose-500 via-purple-600 to-indigo-600 border-2 border-[#121316] rounded-3xl p-4 sm:p-6 text-white shadow-[4px_4px_0px_#121316] relative">
                    <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-yellow-300/20 blur-xl pointer-events-none"></div>
                    
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 relative z-10 w-full min-w-0">
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-1 rounded-full bg-[#fde047] text-[#121316] border border-[#121316] text-[10px] font-mono-code font-black tracking-wider flex items-center gap-1.5 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                                    <span>TANTANGAN STEAM <span class="hidden sm:inline">MINGGU INI</span></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-400 text-slate-950 font-mono-code font-black text-[10px] border border-[#121316]">
                                    {{ $weeklyChallenge['points_reward'] }}
                                </span>
                                <span class="text-[11px] font-mono-code font-bold text-yellow-200">
                                    {{ $weeklyChallenge['deadline_text'] }}
                                </span>
                            </div>

                            <h3 class="text-base sm:text-lg lg:text-xl font-black text-white leading-snug">
                                {{ $weeklyChallenge['title'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-indigo-100 font-medium leading-relaxed max-w-2xl">
                                {{ $weeklyChallenge['description'] }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                            @if(!empty($weeklyChallenge['thread_id']))
                                <a href="{{ route('forum.show', $weeklyChallenge['thread_id']) }}" class="btn-tactile-yellow px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 w-full sm:w-auto whitespace-nowrap shadow-[2px_2px_0px_#121316]">
                                    <i class="fa-solid fa-paper-plane text-rose-600"></i>
                                    <span>Kirim Solusi</span>
                                </a>
                            @else
                                <a href="{{ route('forum.create', ['category' => 'steam_challenge', 'title' => 'Solusi: ' . $weeklyChallenge['title']]) }}" class="btn-tactile-yellow px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 w-full sm:w-auto whitespace-nowrap shadow-[2px_2px_0px_#121316]">
                                    <i class="fa-solid fa-paper-plane text-rose-600"></i>
                                    <span>Ikuti Tantangan</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- ⚡ RADAR EDUTECH & SAINS HARIAN (DAILY TECH BYTE) -->
            @if(isset($edutechInsights) && count($edutechInsights) > 0)
                <div class="mb-5 sm:mb-6 bg-white border-2 border-[#121316] rounded-3xl p-4 sm:p-6 shadow-[4px_4px_0px_#121316]" x-data="{ activeTab: 0 }">
                    <div class="flex items-center justify-between gap-3 border-b-2 border-[#121316]/10 pb-3 mb-4 flex-wrap">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-tr from-amber-400 via-orange-400 to-yellow-300 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-[#121316] text-lg sm:text-xl font-black shrink-0">
                                ⚡
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="forum-hdr text-sm sm:text-base lg:text-lg font-black text-[#121316] tracking-tight">Radar Edukasi, Riset & Teknologi</h2>
                                    <span class="text-[9px] font-mono-code font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400 shrink-0">HARIAN</span>
                                </div>
                                <p class="text-[11px] sm:text-xs text-slate-600 font-semibold mt-0.5 truncate">Wawasan sains, rekayasa kejuruan, dan tips karir masa depan untuk siswa & guru</p>
                            </div>
                        </div>

                        <!-- Pill Switcher -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach($edutechInsights as $idx => $insight)
                                @php
                                    $tabLabels = [
                                        'ai-prompt-engineering' => 'AI Digital',
                                        'ev-motor-revolution' => 'Vokasi EV',
                                        'iot-gempa-nias' => 'IoT Nias',
                                        'beasiswa-kip-kuliah-2026' => 'Beasiswa',
                                        'sains-energi-terbarukan-nias' => 'Energi EBT',
                                        'cloud-network-security' => 'Cyber Sec',
                                    ];
                                    $tLabel = $tabLabels[$insight['id']] ?? explode(' ', $insight['category_label'])[0];
                                @endphp
                                <button type="button" 
                                        @click="activeTab = {{ $idx }}" 
                                        :class="activeTab === {{ $idx }} ? 'bg-[#121316] text-[#fde047] shadow-[2px_2px_0px_#121316]' : 'bg-[#faf8f5] text-[#121316] hover:bg-slate-100'"
                                        class="px-2.5 py-1 rounded-xl border-2 border-[#121316] text-[10px] sm:text-[11px] font-mono-code font-black transition">
                                    <span>#{{ $idx + 1 }} {{ $tLabel }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Insight Content Cards -->
                    @foreach($edutechInsights as $idx => $insight)
                        <div x-show="activeTab === {{ $idx }}" x-transition:enter="transition ease-out duration-200" class="space-y-4">
                            <div class="p-5 sm:p-6 rounded-2xl border-2 border-[#121316] bg-gradient-to-br from-slate-50 via-white to-amber-50/40 shadow-[3px_3px_0px_#121316] space-y-4">
                                <div class="flex items-center justify-between gap-3 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono-code font-black border border-slate-900 {{ $insight['badge_color'] }}">
                                            <i class="{{ $insight['icon'] }} mr-1"></i> {{ $insight['category_label'] }}
                                        </span>
                                        <span class="text-xs font-mono-code font-bold text-slate-500">&bull; {{ $insight['read_time'] }} baca</span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600">Oleh: {{ $insight['author'] }}</span>
                                </div>

                                <h3 class="text-lg sm:text-xl font-black text-[#121316] leading-snug">
                                    {{ $insight['title'] }}
                                </h3>

                                <p class="text-xs sm:text-sm text-slate-700 font-medium leading-relaxed">
                                    {{ $insight['summary'] }}
                                </p>

                                <!-- Key Points -->
                                <div class="p-4 bg-white rounded-xl border-2 border-[#121316]/15 space-y-2">
                                    <div class="text-[10px] font-mono-code font-black text-indigo-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fa-solid fa-lightbulb text-amber-500"></i> Poin Kunci untuk Siswa & Pembelajaran:
                                    </div>
                                    <ul class="space-y-1.5 text-xs text-slate-800 font-semibold">
                                        @foreach($insight['key_points'] as $pt)
                                            <li class="flex items-start gap-2">
                                                <i class="fa-solid fa-check text-emerald-600 text-xs mt-0.5 shrink-0"></i>
                                                <span>{{ $pt }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Discussion Prompt & Action -->
                                <div class="p-4 rounded-xl bg-amber-50 border-2 border-amber-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <span class="text-lg shrink-0">💬</span>
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-mono-code font-black text-amber-800 uppercase tracking-wider">Pemantik Diskusi:</span>
                                            <p class="text-xs font-black text-[#121316] mt-0.5">{{ $insight['discussion_prompt'] }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('forum.create', ['category' => 'edutech_radar', 'title' => 'Diskusi EduTech: ' . $insight['title']]) }}" 
                                       class="btn-tactile-yellow px-4 py-2 rounded-xl text-xs font-mono-code font-black whitespace-nowrap shrink-0 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_#121316]">
                                        <i class="fa-solid fa-comments text-indigo-700"></i>
                                        <span>Buka Diskusi Topik Ini</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- PembdaCHALLENGE: Arena Logika & Sains (Misionaris vs Kanibal) -->
            @include('forum.partials.pembda_challenge')

            <!-- HIGHLIGHT SECTION (Warna-Warni Remaja Ceria) -->
            @if(isset($latestHighlight) || isset($trendingHighlight))
                <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Postingan Terbaru -->
                    @if(isset($latestHighlight))
                        <a href="{{ route('forum.show', $latestHighlight) }}" class="block bg-gradient-to-br from-rose-50 via-white to-orange-50 border-2 border-[#121316] rounded-2xl p-4 sm:p-5 shadow-[3px_3px_0px_#121316] hover:shadow-[5px_5px_0px_#121316] transition group relative overflow-hidden">
                            <div class="flex items-center gap-2 mb-2.5">
                                <span class="text-[10px] font-mono-code font-black uppercase tracking-wider px-2.5 py-0.5 rounded-lg border-2 border-slate-900 flex items-center gap-1 shadow-xs" style="background-color: #dc2626 !important; color: #ffffff !important;">
                                    <i class="fa-solid fa-sparkles text-[10px] text-yellow-300"></i> <span>TERBARU</span>
                                </span>
                                <span class="text-[11px] font-mono-code font-bold text-slate-600">{{ $latestHighlight->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-black text-[#121316] text-base sm:text-lg leading-tight mb-3 group-hover:text-rose-600 transition">{{ $latestHighlight->title }}</h3>
                            <div class="flex items-center gap-2 mt-auto pt-2 border-t border-[#121316]/10">
                                <img src="{{ $latestHighlight->user->avatar_url }}" onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';" class="w-6 h-6 rounded-full border border-[#121316] object-cover">
                                <span class="text-xs font-bold text-[#121316]">{{ $latestHighlight->user->name }}</span>
                            </div>
                        </a>
                    @endif

                    <!-- Postingan Ter-rame (Trending) -->
                    @if(isset($trendingHighlight))
                        <a href="{{ route('forum.show', $trendingHighlight) }}" class="block bg-gradient-to-br from-amber-50 via-white to-yellow-50 border-2 border-[#121316] rounded-2xl p-4 sm:p-5 shadow-[3px_3px_0px_#121316] hover:shadow-[5px_5px_0px_#121316] transition group relative overflow-hidden">
                            <div class="flex items-center gap-2 mb-2.5">
                                <span class="text-[10px] font-mono-code font-black uppercase tracking-wider px-2.5 py-0.5 rounded-lg border-2 border-slate-900 flex items-center gap-1 shadow-xs" style="background-color: #fde047 !important; color: #0f172a !important;">
                                    <i class="fa-solid fa-fire text-rose-600 text-xs"></i> <span>HANGAT</span>
                                </span>
                                <span class="text-[11px] font-mono-code font-black px-2 py-0.5 rounded-md border border-slate-300" style="background-color: #ffffff !important; color: #0f172a !important;">{{ $trendingHighlight->replies_count + $trendingHighlight->likes_count }} Interaksi</span>
                            </div>
                            <h3 class="font-black text-[#121316] text-base sm:text-lg leading-tight mb-3 group-hover:text-amber-700 transition">{{ $trendingHighlight->title }}</h3>
                            <div class="flex items-center gap-2 mt-auto pt-2 border-t border-[#121316]/10">
                                <img src="{{ $trendingHighlight->user->avatar_url }}" onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';" class="w-6 h-6 rounded-full border border-[#121316] object-cover">
                                <span class="text-xs font-bold text-[#121316]">{{ $trendingHighlight->user->name }}</span>
                            </div>
                        </a>
                    @endif
                </div>
            @endif

            <!-- FLOATING REALTIME NEW POSTS TICKER ALERT -->
            <div id="live-new-posts-banner" style="display: none;" class="mb-4 p-3.5 bg-gradient-to-r from-violet-600 via-indigo-600 to-fuchsia-600 text-white border-2 border-[#121316] rounded-2xl shadow-[4px_4px_0px_#121316] flex items-center justify-between gap-3 animate-pulse">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-[#fde047] text-[#121316] border-2 border-[#121316] flex items-center justify-center font-black text-base shadow-[1px_1px_0px_#121316] shrink-0">
                        ⚡
                    </span>
                    <div class="min-w-0">
                        <div class="text-xs sm:text-sm font-black text-white truncate" id="live-new-posts-text">
                            Ada postingan baru di PembdaSPACE!
                        </div>
                        <div class="text-[10px] font-mono-code text-yellow-200 font-bold truncate">
                            Civitas sekolah baru saja membagikan karya/diskusi baru
                        </div>
                    </div>
                </div>
                <button type="button" onclick="window.location.reload()" class="px-4 py-2 bg-[#fde047] hover:bg-yellow-300 text-[#121316] border-2 border-[#121316] rounded-xl text-xs font-mono-code font-black shadow-[2px_2px_0px_#121316] shrink-0 transition active:translate-x-0.5 active:translate-y-0.5">
                    Muat Sekarang ↻
                </button>
            </div>

            <!-- Feed List (Youth Tinted Cards) -->
            <div class="space-y-4 pb-10">
                @forelse($threads as $thread)
                    @php
                        $author = $thread->user;
                        $isLiked = $thread->isLikedBy(auth()->user());
                        $tMeta = $channelMeta[$thread->category] ?? [
                            'icon' => 'fa-solid fa-comments',
                            'pill' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'badge' => 'bg-slate-50 text-slate-800 border-slate-300',
                            'label' => $thread->category_label,
                            'accent' => '#64748b'
                        ];
                    @endphp
                    
                    <div class="tactile-card p-5 {{ $thread->is_pinned ? 'ring-2 ring-[#fde047]' : '' }} relative overflow-hidden">
                        <!-- Top Category Accent Ribbon Bar -->
                        <div class="absolute top-0 left-0 right-0 h-1.5" style="background-color: {{ $tMeta['accent'] }};"></div>

                        <a href="{{ route('forum.show', $thread) }}" class="block">
                            <!-- Author & Meta -->
                            <div class="flex justify-between items-start mb-3 pt-1">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $author->avatar_url }}" 
                                         alt="{{ $author->name }}"
                                         onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';"
                                         class="w-10 h-10 rounded-2xl border-2 border-[#121316] shadow-[1px_1px_0px_#121316] object-cover">
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-extrabold text-[#121316] text-sm">{{ $author->name }}</span>
                                            <span class="text-[10px] font-mono-code font-black px-1.5 py-0.5 bg-[#faf8f5] text-[#121316] border border-[#121316] rounded-md uppercase tracking-wider">{{ $author->role }}</span>
                                            @if($author->ekskul_flair)
                                            <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border border-[#121316] bg-[#fde047] text-[#121316]" title="{{ $author->ekskul_flair['label'] }}">
                                                {{ $author->ekskul_flair['label'] }}
                                            </span>
                                            @endif
                                            <span class="text-xs text-slate-500 font-mono-code">&bull; {{ $thread->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                            <span class="text-[10px] font-mono-code font-black px-2.5 py-0.5 rounded-lg border {{ $tMeta['badge'] }} flex items-center gap-1.5 shadow-2xs">
                                                <i class="{{ $tMeta['icon'] }} text-[10px]"></i>
                                                <span>{{ $tMeta['label'] }}</span>
                                            </span>
                                            @if($thread->category === 'ekskul_showcase')
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#fde047] text-[#121316] border border-[#121316] rounded-lg flex items-center gap-1">
                                                    <i class="fa-solid fa-trophy text-[9px] text-amber-900"></i> Official Ekskul
                                                </span>
                                            @elseif($thread->category === 'ekskul_recruit')
                                                <span class="text-[10px] font-mono-code font-black px-2.5 py-0.5 border-2 border-slate-900 rounded-lg flex items-center gap-1 animate-pulse" style="background-color: #dc2626 !important; color: #ffffff !important;">
                                                    <i class="fa-solid fa-bullhorn text-[9px] text-yellow-300"></i> <span>Open Recruitment</span>
                                                </span>
                                            @endif
                                            @if($thread->category === 'tanya_jawab')
                                                @if($thread->hasAcceptedReply())
                                                    <span class="text-[10px] font-mono-code font-black px-2.5 py-0.5 border-2 border-slate-900 rounded-lg flex items-center gap-1" style="background-color: #059669 !important; color: #ffffff !important;">
                                                        <i class="fa-solid fa-circle-check text-[9px] text-emerald-200"></i> <span>Terjawab</span>
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#fde047] text-[#121316] border border-[#121316] rounded-lg flex items-center gap-1">
                                                        <i class="fa-solid fa-circle-question text-[9px] text-amber-900"></i> Bantu Jawab
                                                    </span>
                                                @endif
                                            @endif
                                            @if($thread->category === 'gaming' && $thread->game_name)
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-cyan-100 text-cyan-900 border border-[#121316] rounded-lg flex items-center gap-1">
                                                    <i class="fa-solid fa-gamepad text-[9px]"></i> {{ $thread->game_name }}
                                                </span>
                                            @endif
                                            @if($thread->category === 'sharing' && $thread->file_category)
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-emerald-100 text-emerald-900 border border-[#121316] rounded-lg flex items-center gap-1">
                                                    <i class="fa-solid fa-folder-open text-[9px]"></i> {{ $thread->file_category }}
                                                </span>
                                            @endif
                                            @if($thread->reference_type === \App\Models\LmsSubmission::class)
                                                <span class="text-[10px] font-mono-code font-black px-2.5 py-0.5 rounded-lg border-2 border-slate-900 flex items-center gap-1 shadow-xs" style="background-color: #4338ca !important; color: #fde047 !important;">
                                                    <i class="fa-solid fa-graduation-cap text-[10px] text-yellow-300"></i> <span>KARYA LMS TERVERIFIKASI</span>
                                                </span>
                                            @elseif($thread->reference_type === \App\Models\FinalProject::class)
                                                <span class="text-[10px] font-mono-code font-black px-2.5 py-0.5 rounded-lg border-2 border-slate-900 flex items-center gap-1 shadow-xs" style="background-color: #0284c7 !important; color: #ffffff !important;">
                                                    <i class="fa-solid fa-microscope text-[10px] text-cyan-200"></i> <span>PROJECT AKHIR / RISET</span>
                                                </span>
                                            @endif
                                            @if($thread->is_pinned)
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#fde047] text-[#121316] border border-[#121316] rounded-lg flex items-center gap-1">
                                                    <i class="fa-solid fa-thumbtack text-[9px]"></i> Tersemat
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions (Author / Admin) -->
                                @if(auth()->id() === $thread->user_id || auth()->user()->isSuperAdmin())
                                    <div class="flex items-center gap-1.5 flex-shrink-0" onclick="event.stopPropagation()">
                                        <a href="{{ route('forum.edit', $thread) }}" class="p-1.5 bg-[#faf8f5] hover:bg-[#fde047] border border-[#121316] rounded-lg text-[#121316] transition" title="Edit Post">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </a>
                                        <form action="{{ route('forum.destroy', $thread) }}" method="POST" onsubmit="return confirm('Yakin hapus postingan ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-[#faf8f5] hover:bg-rose-100 border border-[#121316] rounded-lg text-rose-600 transition" title="Hapus Post">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="space-y-2 mt-2">
                                <h3 class="forum-hdr text-lg sm:text-xl font-black text-[#121316] group-hover:text-indigo-600 transition-colors leading-snug">
                                    {{ $thread->title }}
                                </h3>
                                
                                @php
                                    $contentLength = mb_strlen(strip_tags($thread->content ?? ''));
                                    $isLongContent = $contentLength > 350;
                                @endphp

                                <div class="text-xs sm:text-sm text-slate-700 font-medium leading-relaxed">
                                    @if($isLongContent)
                                        <p class="line-clamp-3 text-slate-700">
                                            {!! nl2br(e(Str::limit(strip_tags($thread->content), 300))) !!}
                                        </p>
                                        <a href="{{ route('forum.show', $thread) }}" class="inline-flex items-center gap-1 text-xs font-black text-indigo-600 hover:underline mt-1">
                                            <span>Baca Selengkapnya</span>
                                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                        </a>
                                    @else
                                        <div class="space-y-1">
                                            {!! nl2br(e($thread->content)) !!}
                                        </div>
                                    @endif
                                </div>

                                @if($thread->image_path)
                                    <div class="mt-3 rounded-2xl overflow-hidden border-2 border-[#121316] max-w-sm max-h-56 shadow-[2px_2px_0px_#121316]">
                                        <img src="{{ asset('storage/' . $thread->image_path) }}" class="w-full h-full object-cover">
                                    </div>
                                @endif

                                <!-- Bank File Direct Download Box -->
                                @if($thread->category === 'sharing' && $thread->attachment_path)
                                    <div class="mt-3 p-3 bg-emerald-50/70 border-2 border-[#121316] rounded-xl max-w-md flex items-center justify-between gap-3 shadow-[2px_2px_0px_#121316]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-emerald-200 border border-[#121316] flex items-center justify-center text-emerald-900 font-mono-code font-black text-xs flex-shrink-0 uppercase">
                                                {{ strtoupper($thread->file_extension ?: 'FILE') }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-[#121316] truncate">{{ $thread->attachment_name ?? 'Berkas Lampiran' }}</div>
                                                <div class="text-[10px] font-mono-code text-emerald-700 font-bold">{{ $thread->file_category ?: 'Dokumen Pembelajaran' }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ asset('storage/' . $thread->attachment_path) }}" download onclick="event.stopPropagation()" class="btn-tactile-emerald px-3 py-1.5 rounded-lg text-xs font-black transition flex items-center gap-1 flex-shrink-0">
                                            <i class="fa-solid fa-download text-xs"></i> Unduh
                                        </a>
                                    </div>
                                @endif

                                <!-- Gaming Mabar Lobby Card -->
                                @if($thread->category === 'gaming' && ($thread->game_name || $thread->game_room_code))
                                    <div class="mt-3 p-3 bg-cyan-50 border-2 border-[#121316] rounded-xl max-w-md flex items-center justify-between gap-3 shadow-[2px_2px_0px_#121316]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-cyan-500 border border-[#121316] flex items-center justify-center text-white text-base flex-shrink-0">
                                                <i class="fa-solid fa-gamepad"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-[#121316] truncate">{{ $thread->game_name ?: 'Lobi Mabar' }}</div>
                                                <div class="text-[11px] font-mono-code font-bold text-slate-700 truncate">ID/Kode: <span class="text-cyan-700 font-black select-all">{{ $thread->game_room_code ?: 'Tanyakan di komentar' }}</span></div>
                                            </div>
                                        </div>
                                        @if($thread->game_room_code)
                                        <button onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText('{{ addslashes($thread->game_room_code) }}'); alert('Kode/ID Game berhasil disalin!');" class="btn-tactile-red px-3 py-1.5 rounded-lg text-xs font-black flex items-center gap-1 flex-shrink-0">
                                            <i class="fa-solid fa-copy text-xs"></i> Salin ID
                                        </button>
                                        @endif
                                    </div>
                                @endif

                                <!-- Ekskul Showcase & Recruitment Card -->
                                @if(in_array($thread->category, ['ekskul_showcase', 'ekskul_recruit']))
                                    <div class="mt-3 p-3.5 bg-gradient-to-r from-purple-50 via-indigo-50 to-pink-50 border-2 border-[#121316] rounded-xl flex items-center justify-between gap-3 shadow-[2px_2px_0px_#121316]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-amber-400 border border-[#121316] flex items-center justify-center text-[#121316] text-base flex-shrink-0 font-black">
                                                <i class="fa-solid fa-trophy text-amber-900"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-[#121316] truncate">{{ $thread->group ? $thread->group->name : 'Ekstrakurikuler Yayasan Pembda' }}</div>
                                                <div class="text-[11px] font-mono-code font-bold text-purple-700">Official Squad Showcase & Recruitment</div>
                                            </div>
                                        </div>
                                        <a href="{{ route('siswa.ekskul.index') }}" onclick="event.stopPropagation()" class="btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 flex-shrink-0">
                                            <i class="fa-solid fa-sitemap text-blue-600 text-xs"></i>
                                            <span>Roster</span>
                                        </a>
                                    </div>
                                @endif

                                @if($thread->poll)
                                    @php
                                        $poll = $thread->poll;
                                        $totalVotes = $poll->totalVotes();
                                        $userVote = $poll->votes->where('user_id', auth()->id())->first();
                                        $userVotedOptionId = $userVote ? $userVote->forum_poll_option_id : null;
                                    @endphp
                                    <div class="mt-4 p-4 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] space-y-3 cursor-default" onclick="event.preventDefault(); event.stopPropagation();">
                                        <div class="flex items-center justify-between gap-2 border-b-2 border-[#121316]/10 pb-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="w-7 h-7 rounded-lg bg-[#fde047] border border-[#121316] text-[#121316] flex items-center justify-center text-xs flex-shrink-0">
                                                    <i class="fa-solid fa-chart-simple"></i>
                                                </span>
                                                <span class="text-[10px] font-mono-code font-black tracking-wider uppercase text-[#121316] bg-[#faf8f5] border border-[#121316] px-2 py-0.5 rounded-md">📊 Polling Interaktif</span>
                                            </div>
                                            <span class="text-xs font-mono-code font-black text-[#121316] flex items-center gap-1">
                                                <i class="fa-solid fa-users text-blue-600 text-xs"></i>
                                                <span id="poll-feed-total-{{ $poll->id }}">{{ $totalVotes }}</span> Suara
                                            </span>
                                        </div>

                                        <h4 class="text-sm font-black text-[#121316] leading-snug">
                                            {{ $poll->question }}
                                        </h4>

                                        <div class="space-y-2 pt-1" id="poll-feed-options-{{ $poll->id }}">
                                            @foreach($poll->options as $opt)
                                                @php
                                                    $pct = $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100) : 0;
                                                    $isVoted = $userVotedOptionId === $opt->id;
                                                @endphp
                                                <button type="button" 
                                                        onclick="voteFeedPoll(event, {{ $opt->id }}, {{ $poll->id }})" 
                                                        class="w-full relative overflow-hidden rounded-xl border-2 text-left p-3 transition-all duration-200 group cursor-pointer {{ $isVoted ? 'border-[#121316] bg-[#fde047] shadow-[2px_2px_0px_#121316]' : 'border-[#121316] bg-[#faf8f5] hover:bg-white shadow-[1px_1px_0px_#121316]' }}"
                                                        id="poll-feed-btn-{{ $opt->id }}">
                                                    
                                                    <!-- Progress Bar Fill -->
                                                    <div class="absolute top-0 left-0 h-full {{ $isVoted ? 'bg-[#facc15]/60' : 'bg-slate-200' }} transition-all duration-500 pointer-events-none" 
                                                         style="width: {{ $pct }}%" 
                                                         id="poll-feed-bg-{{ $opt->id }}"></div>
                                                    
                                                    <div class="relative z-10 flex items-center justify-between gap-3 text-xs">
                                                        <div class="flex items-center gap-2 font-bold text-[#121316]">
                                                            <span class="w-5 h-5 rounded-full flex items-center justify-center border border-[#121316] text-[10px] font-mono-code font-black flex-shrink-0 {{ $isVoted ? 'bg-[#121316] text-[#fde047]' : 'bg-white text-[#121316]' }}" id="poll-feed-check-{{ $opt->id }}">
                                                                @if($isVoted)
                                                                    <i class="fa-solid fa-check text-[9px]"></i>
                                                                @else
                                                                    {{ $loop->iteration }}
                                                                @endif
                                                            </span>
                                                            <span class="truncate font-black">{{ $opt->option_text }}</span>
                                                        </div>
                                                        <div class="font-mono-code font-black flex items-center gap-1.5 flex-shrink-0 text-[#121316]">
                                                            <span id="poll-feed-pct-{{ $opt->id }}">{{ $pct }}%</span>
                                                            <span class="text-[10px] text-slate-500" id="poll-feed-count-{{ $opt->id }}">({{ $opt->votes_count }})</span>
                                                        </div>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                        
                                        @if(!$poll->isOpen())
                                            <div class="text-[11px] font-mono-code font-black text-amber-700 flex items-center gap-1 pt-1">
                                                <i class="fa-solid fa-lock text-xs"></i> Polling ini telah ditutup
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </a>

                        <!-- Action Bar -->
                        <div class="mt-4 pt-3 border-t-2 border-[#121316]/10 flex items-center flex-wrap gap-2 text-xs font-mono-code font-bold text-[#121316]">
                            <!-- Upvote/Like -->
                            <button onclick="toggleLike(this, '{{ route('forum.like', $thread) }}')" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border-2 border-[#121316] shadow-[1px_1px_0px_#121316] transition {{ $isLiked ? 'bg-[#fde047] text-[#121316] font-black' : 'bg-white hover:bg-[#faf8f5] text-[#121316]' }}">
                                <i class="fa-solid fa-thumbs-up text-xs text-amber-600"></i> <span class="likes-count">{{ $thread->likes->count() }}</span>
                            </button>
                            
                            <!-- Replies count -->
                            <a href="{{ route('forum.show', $thread) }}#replies" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border-2 border-[#121316] bg-white hover:bg-[#faf8f5] shadow-[1px_1px_0px_#121316] transition text-[#121316]">
                                <i class="fa-solid fa-comment-dots text-xs text-rose-500"></i> <span>{{ $thread->replies->count() }}</span>
                            </a>

                            <!-- Views -->
                            <div class="flex items-center gap-1.5 px-2.5 py-1.5 text-slate-500 font-bold">
                                <i class="fa-solid fa-eye text-xs"></i> <span>{{ $thread->views_count }}</span>
                            </div>

                            @if(in_array($thread->category, ['project_idea', 'committee', 'charity']))
                                <div class="flex items-center gap-1.5 px-3 py-1.5 ml-auto bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-xl font-black text-[11px]">
                                    <i class="fa-solid fa-users text-xs"></i> {{ $thread->approvedMembers()->count() }} Tim
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white border-2 border-[#121316] rounded-3xl p-12 text-center mt-6 shadow-[4px_4px_0px_#121316]">
                        <div class="w-16 h-16 bg-[#faf8f5] border-2 border-[#121316] rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-[2px_2px_0px_#121316] text-3xl">
                            🎈
                        </div>
                        <h4 class="forum-hdr text-xl font-black text-[#121316] mb-2">Belum Ada Topik di Saluran Ini</h4>
                        <p class="text-xs sm:text-sm text-slate-600 font-semibold max-w-sm mx-auto">Jadilah yang pertama membuka obrolan, berbagi ide projek seru, atau membagikan karya Anda!</p>
                        <a href="{{ route('forum.create') }}" class="btn-tactile-red inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black mt-5">
                            <i class="fa-solid fa-circle-plus text-sm"></i>
                            <span>Buat Topik Pertama</span>
                        </a>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if($threads->hasPages())
                    <div class="pt-6 pb-10 flex justify-center">
                        {{ $threads->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
            @endif
        </div>

        <!-- SIDEBAR WIDGETS (Right) - Bisa Buka/Tutup -->
        <div x-show="rightSidebarOpen"
             :class="rightSidebarOpen ? 'xl:flex' : 'hidden'"
             class="hidden flex-col w-72 flex-shrink-0 bg-white border-l-2 border-[#121316] max-h-[85vh] overflow-hidden forum-right-sidebar transition-all duration-300 relative">
            <!-- Pinned Top User Profile Card -->
            <div class="p-3 sm:p-4 bg-white border-b-2 border-[#121316] shrink-0">
                <div class="flex items-center justify-between pb-1.5 mb-2 border-b border-[#121316]/10">
                    <span class="text-[10px] font-mono-code font-black text-slate-500 uppercase tracking-wider">PROFIL & PERINGKAT</span>
                    <button type="button" 
                            @click="rightSidebarOpen = false" 
                            class="w-6 h-6 rounded-md bg-[#faf8f5] hover:bg-slate-200 border border-[#121316] text-[#121316] flex items-center justify-center text-[10px] transition" 
                            title="Sembunyikan Widget Kanan (Perluas Layar)">
                        <i class="fa-solid fa-chevron-right text-[9px]"></i>
                    </button>
                </div>
                @php
                    $user = auth()->user();
                    $isYayasan = $user->username === 'yulzega' || str_contains(strtolower($user->name ?? ''), 'yulianus zega');
                    $rep = $user->reputation;
                    $pts = $rep->total_points ?? 0;
                    $school = $user->school->name ?? 'Yayasan Perguruan Pembda Nias';
                    
                    $next = 100; $rank = 'Perintis';
                    if ($pts >= 500) { $rank = 'Legenda 👑'; $next = 1000; }
                    elseif ($pts >= 200) { $rank = 'Kontributor 💎'; $next = 500; }
                    elseif ($pts >= 100) { $rank = 'Warga Aktif 🚀'; $next = 200; }
                    $pct = min(100, max(5, round(($pts / $next) * 100)));
                @endphp

                @if($isYayasan)
                <!-- Premium Yayasan Profile Card (Neo-Brutalist Gold) -->
                <div class="rounded-2xl p-4 text-[#121316] bg-gradient-to-br from-amber-400 via-yellow-300 to-amber-300 border-2 border-[#121316] shadow-[3px_3px_0px_#121316]">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="relative shrink-0">
                            <div class="w-12 h-12 rounded-2xl bg-white p-0.5 border-2 border-[#121316] shadow-[1px_1px_0px_#121316] overflow-hidden flex items-center justify-center">
                                <img src="{{ $user->avatar_url }}" 
                                     alt="{{ $user->name }}" 
                                     class="w-full h-full object-cover rounded-xl"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full items-center justify-center hidden bg-rose-500 text-white font-black text-xl">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                            </div>
                            <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-[10px] shadow-sm font-black border border-slate-900" style="background-color: #dc2626 !important; color: #ffffff !important;" title="Pimpinan Terverifikasi">
                                👑
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-[#121316] text-sm leading-snug tracking-tight truncate" title="{{ $user->name }}">
                                {{ $user->name }}
                            </h3>
                            <p class="text-[11px] font-mono-code font-bold text-slate-800 truncate mt-0.5" title="{{ $school }}">
                                {{ $school }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t-2 border-[#121316]/20 font-mono-code">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-black bg-[#121316] text-[#fde047]">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                            <span>Pimpinan Yayasan</span>
                        </span>
                        <span class="text-[10px] text-[#121316] font-black uppercase tracking-wider">
                            EKSEKUTIF
                        </span>
                    </div>
                </div>
                @else
                <!-- Regular User Profile Card (Neo-Brutalist) -->
                <div class="rounded-2xl p-4 text-[#121316] bg-gradient-to-br from-indigo-50/60 to-purple-50/60 border-2 border-[#121316] shadow-[3px_3px_0px_#121316]">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-white p-0.5 border-2 border-[#121316] shadow-[1px_1px_0px_#121316] overflow-hidden shrink-0 flex items-center justify-center">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';" class="w-full h-full object-cover rounded-xl">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-[#121316] text-sm leading-snug tracking-tight truncate" title="{{ $user->name }}">
                                {{ $user->name }}
                            </h3>
                            <p class="text-[11px] text-slate-600 font-semibold truncate mt-0.5" title="{{ $school }}">
                                {{ $school }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5 pt-2 border-t-2 border-[#121316]/10 font-mono-code">
                        <div class="flex justify-between items-center text-xs font-black text-[#121316]">
                            <span>{{ $rank }}</span>
                            <span class="text-rose-600">{{ $pts }} / {{ $next }} Pts</span>
                        </div>
                        <div class="w-full bg-white border-2 border-[#121316] h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-violet-500 to-rose-500 h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Scrollable Content: Leaderboard & Cari Tim Widgets -->
            <div class="flex-1 p-4 flex flex-col gap-4 overflow-y-auto no-scrollbar">
                <!-- Leaderboard Widget -->
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-4 shadow-[3px_3px_0px_#121316]">
                    <div class="flex items-center justify-between mb-3.5 border-b-2 border-[#121316]/10 pb-2">
                        <h3 class="forum-hdr text-xs font-mono-code font-black text-[#121316] flex items-center gap-2 uppercase tracking-wider">
                            <i class="fa-solid fa-trophy text-amber-500 text-sm"></i> Leaderboard
                        </h3>
                        <a href="{{ route('reputation.leaderboard') }}" class="text-[10px] font-mono-code font-black text-indigo-600 hover:underline uppercase">SEMUA &rarr;</a>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($topStudents as $i => $s)
                            <div class="flex items-center gap-2.5">
                                <div class="w-5 h-5 rounded-md border border-[#121316] flex items-center justify-center text-[10px] font-mono-code font-black {{ $i === 0 ? 'bg-[#fde047] text-[#121316]' : ($i === 1 ? 'bg-slate-200 text-[#121316]' : ($i === 2 ? 'bg-amber-100 text-[#121316]' : 'bg-[#faf8f5] text-slate-600')) }}">
                                    {{ $i+1 }}
                                </div>
                                <img src="{{ $s->user->avatar_url }}" alt="{{ $s->user->name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';" class="w-6 h-6 rounded-full border border-[#121316] object-cover">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-[#121316] truncate">{{ $s->user->name }}</div>
                                </div>
                                <div class="text-xs font-mono-code font-black text-rose-600">{{ $s->total_points }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Collab Widget -->
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-4 shadow-[3px_3px_0px_#121316]">
                    <h3 class="forum-hdr text-xs font-mono-code font-black text-[#121316] flex items-center gap-2 mb-3.5 border-b-2 border-[#121316]/10 pb-2 uppercase tracking-wider">
                        <i class="fa-solid fa-handshake-angle text-indigo-600 text-sm"></i> Cari Tim Collab
                    </h3>
                    <div class="space-y-2.5">
                        @forelse($activeCollabs as $c)
                            @php
                                $collabMeta = $channelMeta[$c->category] ?? [
                                    'badge' => 'bg-indigo-50 text-indigo-700',
                                    'label' => $c->category_label
                                ];
                            @endphp
                            <a href="{{ route('forum.show', $c) }}" class="block p-3 bg-slate-50 hover:bg-amber-50 rounded-xl border-2 border-[#121316] shadow-[1px_1px_0px_#121316] transition group">
                                <div class="text-[9px] font-mono-code text-indigo-600 font-black uppercase mb-1">{{ $collabMeta['label'] }}</div>
                                <div class="text-xs font-black text-[#121316] line-clamp-2 mb-1.5 leading-snug">{{ $c->title }}</div>
                                <div class="flex justify-between items-center text-[10px] font-mono-code font-bold text-slate-600">
                                    <span class="truncate pr-2">{{ $c->user->name }}</span>
                                    <span class="flex-shrink-0 px-1.5 py-0.5 bg-white border border-[#121316] rounded text-[#121316]">{{ $c->approvedMembers()->count() }} Tim</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-[11px] font-bold text-slate-500 italic text-center py-2">Belum ada kolaborasi aktif.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- AJAX Like Script -->
<script>
function getCsrfToken() {
    const name = "XSRF-TOKEN=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '{{ csrf_token() }}';
}

async function toggleLike(btn, url) {
    btn.disabled = true;
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken()
            }
        });
        if (!response.ok) {
            const text = await response.text();
            alert("Like Server Error (" + response.status + "): " + text.substring(0, 500));
            return;
        }
        const result = await response.json();
        if (result.success) {
            const countSpan = btn.querySelector('.likes-count');
            countSpan.textContent = result.likes_count;
            if (result.liked) {
                btn.classList.remove('bg-white', 'text-[#121316]');
                btn.classList.add('bg-[#fde047]', 'text-[#121316]', 'font-black');
            } else {
                btn.classList.remove('bg-[#fde047]', 'font-black');
                btn.classList.add('bg-white', 'text-[#121316]');
            }
        }
    } catch (error) {
        alert('Like JS Catch Error: ' + error.message);
    } finally {
        btn.disabled = false;
    }
}

async function voteFeedPoll(event, optionId, pollId) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    try {
        const res = await fetch(`{{ url('/forum/poll') }}/${optionId}/vote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-XSRF-TOKEN': getCsrfToken()
            }
        });
        
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            alert(err.message || 'Gagal mengirim pilihan polling.');
            return;
        }
        
        const data = await res.json();
        if (data.success) {
            // Update total votes count
            const totalEl = document.getElementById(`poll-feed-total-${pollId}`);
            if (totalEl) totalEl.textContent = data.total_votes;
            
            // Update each option in the poll
            data.options.forEach((opt, idx) => {
                const pctEl = document.getElementById(`poll-feed-pct-${opt.id}`);
                const countEl = document.getElementById(`poll-feed-count-${opt.id}`);
                const bgEl = document.getElementById(`poll-feed-bg-${opt.id}`);
                const btnEl = document.getElementById(`poll-feed-btn-${opt.id}`);
                const checkEl = document.getElementById(`poll-feed-check-${opt.id}`);
                
                if (pctEl) pctEl.textContent = opt.percentage + '%';
                if (countEl) countEl.textContent = '(' + opt.votes_count + ')';
                if (bgEl) bgEl.style.width = opt.percentage + '%';
                
                const isSelected = data.voted && data.voted_option_id === opt.id;
                
                if (btnEl) {
                    if (isSelected) {
                        btnEl.className = 'w-full relative overflow-hidden rounded-xl border-2 text-left p-3 transition-all duration-200 group cursor-pointer border-[#121316] bg-[#fde047] shadow-[2px_2px_0px_#121316]';
                    } else {
                        btnEl.className = 'w-full relative overflow-hidden rounded-xl border-2 text-left p-3 transition-all duration-200 group cursor-pointer border-[#121316] bg-[#faf8f5] hover:bg-white shadow-[1px_1px_0px_#121316]';
                    }
                }
                
                if (checkEl) {
                    if (isSelected) {
                        checkEl.className = 'w-5 h-5 rounded-full flex items-center justify-center border border-[#121316] text-[10px] font-mono-code font-black flex-shrink-0 bg-[#121316] text-[#fde047]';
                        checkEl.innerHTML = '<i class="fa-solid fa-check text-[9px]"></i>';
                    } else {
                        checkEl.className = 'w-5 h-5 rounded-full flex items-center justify-center border border-[#121316] text-[10px] font-mono-code font-black flex-shrink-0 bg-white text-[#121316]';
                        checkEl.textContent = (idx + 1);
                    }
                }
            });
        }
    } catch (e) {
        console.error('Poll Error:', e);
        alert('Terjadi kesalahan koneksi saat voting polling.');
    }
}
</script>

<!-- PWA Install Guide Modal -->
<div id="pwaGuideModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
    <div class="bg-white border-2 border-[#121316] rounded-3xl w-full max-w-md overflow-hidden shadow-[8px_8px_0px_#121316]">
        <div class="p-4 sm:p-5 border-b-2 border-[#121316] flex justify-between items-center bg-[#fde047]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 border border-[#121316] text-white flex items-center justify-center font-bold">
                    <i class="fa-solid fa-mobile-screen text-lg"></i>
                </div>
                <div>
                    <h3 class="forum-hdr text-base font-black text-[#121316] leading-tight">Install Aplikasi PembdaHUB</h3>
                    <div class="text-xs font-mono-code text-[#121316] font-bold">Aplikasi Mobile Resmi PembdaHUB</div>
                </div>
            </div>
            <button onclick="document.getElementById('pwaGuideModal').style.display = 'none'" class="w-8 h-8 rounded-xl bg-white border border-[#121316] text-[#121316] hover:bg-rose-50 flex items-center justify-center font-bold">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        <div class="p-6 space-y-4 text-sm text-[#121316]">
            <div class="p-3 bg-blue-50 border-2 border-[#121316] rounded-2xl flex items-start gap-3 shadow-[2px_2px_0px_#121316]">
                <i class="fa-solid fa-circle-info text-blue-600 text-lg flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-[#121316] font-bold leading-relaxed">Aplikasi PembdaHUB Mobile akan terpasang di Layar Utama HP Anda dengan Logo Resmi Perguruan PEMBDA.</div>
            </div>
            
            <div class="space-y-3 pt-1">
                <h4 class="font-mono-code font-black text-[#121316] text-xs uppercase tracking-wider">Langkah Mudah di HP Android (Chrome):</h4>
                <div class="flex items-start gap-3 text-xs">
                    <div class="w-6 h-6 rounded-full bg-[#fde047] border border-[#121316] text-[#121316] font-black flex items-center justify-center flex-shrink-0">1</div>
                    <div class="pt-0.5 font-bold text-[#121316]">Klik <strong>Titik Tiga (⋮)</strong> di pojok kanan atas browser Chrome HP Anda.</div>
                </div>
                <div class="flex items-start gap-3 text-xs">
                    <div class="w-6 h-6 rounded-full bg-[#fde047] border border-[#121316] text-[#121316] font-black flex items-center justify-center flex-shrink-0">2</div>
                    <div class="pt-0.5 font-bold text-[#121316]">Pilih menu <strong>"Instal aplikasi"</strong> ATAU <strong>"Tambahkan ke Layar Utama"</strong>.</div>
                </div>
                <div class="flex items-start gap-3 text-xs">
                    <div class="w-6 h-6 rounded-full bg-[#fde047] border border-[#121316] text-[#121316] font-black flex items-center justify-center flex-shrink-0">3</div>
                    <div class="pt-0.5 font-bold text-[#121316]">Klik <strong>"Instal" / "Tambah"</strong>. Ikon PembdaHUB Mobile akan langsung muncul di HP Anda!</div>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-[#121316]/10">
                <button onclick="document.getElementById('pwaGuideModal').style.display = 'none'" class="btn-tactile-red w-full py-2.5 rounded-xl text-xs font-black">
                    Mengerti, Siap Pasang!
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deferredPwaPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPwaPrompt = e;
    console.log('PWA install prompt ready');
});

function triggerPwaInstall() {
    if (deferredPwaPrompt) {
        deferredPwaPrompt.prompt();
        deferredPwaPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted PWA installation');
            }
            deferredPwaPrompt = null;
        });
    } else {
        document.getElementById('pwaGuideModal').style.display = 'flex';
    }
}

function openPembdaChallenge() {
    window.dispatchEvent(new CustomEvent('open-challenge'));
    const el = document.getElementById('pembda-challenge');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        el.classList.add('ring-4', 'ring-[#fde047]');
        setTimeout(() => el.classList.remove('ring-4', 'ring-[#fde047]'), 1500);
    }
}

// Cek jika halaman dibuka dengan anchor #pembda-challenge
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#pembda-challenge' || window.location.search.includes('challenge=1')) {
        setTimeout(openPembdaChallenge, 300);
    }
});

// Realtime Feed Live Ticker Engine
let latestThreadIdOnFeed = {{ (int)($threads->max('id') ?? 0) }};
async function checkLiveFeedUpdates() {
    try {
        const cat = "{{ request('category', '') }}";
        const grp = "{{ request('group', '') }}";
        const url = `{{ route('forum.live-ticker') }}?latest_thread_id=${latestThreadIdOnFeed}&category=${cat}&group=${grp}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;

        const data = await res.json();
        if (data.success && data.new_threads_count > 0) {
            const banner = document.getElementById('live-new-posts-banner');
            const textEl = document.getElementById('live-new-posts-text');
            if (banner && textEl) {
                textEl.textContent = `Ada ${data.new_threads_count} postingan baru di PembdaSPACE!`;
                banner.style.display = 'flex';
            }
        }
    } catch(e) {}
}

setInterval(checkLiveFeedUpdates, 12000);
</script>

<!-- Mobile Floating Action Button (Buat Post) -->
<a href="{{ route('forum.create') }}" 
   class="md:hidden fixed bottom-6 right-6 z-50 w-14 h-14 border-2 border-slate-900 rounded-2xl flex items-center justify-center shadow-[4px_4px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-[2px_2px_0px_#121316] transition-all"
   style="background-color: #dc2626 !important; color: #ffffff !important;"
   title="Buat Status Baru">
    <i class="fa-solid fa-plus text-xl"></i>
</a>
@endsection
