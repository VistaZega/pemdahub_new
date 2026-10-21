@extends(auth()->user()->layout)

@section('title', 'Pembda Space')

@section('content')
<!-- Dynamic Google Fonts (Plus Jakarta Sans & JetBrains Mono) & Phosphor Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
{{-- Alpine.js sudah dimuat oleh layout parent (layouts/app.blade.php) — JANGAN duplikat di sini --}}

<!-- PWA Manifest & App Shell Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#121316">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Pembda Space">
<link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/sw.js').then(function(reg) {
        console.log('PembdaSpace PWA Registered:', reg.scope);
      }).catch(function(err) {
        console.log('PembdaSpace PWA Registration Failed:', err);
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
        --bg-warm: #faf8f5;
        --text-dark: #121316;
        --brand-red: #ff3823;
        --brand-yellow: #fde047;
        --brand-emerald: #10b981;
        --brand-blue: #2563eb;
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

    /* Graph Canvas Background */
    .graph-canvas {
        background-color: #faf8f5;
        background-image: radial-gradient(#d8d4c7 1.2px, transparent 1.2px);
        background-size: 20px 20px;
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
        background-color: #faf8f5 !important;
        border: 2px solid #121316 !important;
        box-shadow: 2px 2px 0px #121316 !important;
        color: #121316 !important;
    }

    /* Card Neo-Brutalist */
    .tactile-card {
        background: #ffffff;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        border-radius: 1rem;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .tactile-card:hover {
        transform: translate(-1px, -1px);
        box-shadow: 5px 5px 0px #121316;
    }
</style>

<!-- App Window Wrapper (Embedded in Global Layout) -->
<div class="w-full text-[#121316] font-['Plus_Jakarta_Sans',sans-serif] rounded-3xl overflow-hidden border-2 border-[#121316] shadow-[6px_6px_0px_#121316] bg-[#faf8f5]" style="min-height: 85vh;">
    
    <div class="flex flex-col md:flex-row h-full w-full" x-data="{ mobileSidebarOpen: false }">
        
        <!-- Mobile Header & Toggle -->
        <div class="md:hidden flex items-center justify-between bg-white p-4 border-b-2 border-[#121316]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#ff3823] border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center flex-shrink-0 text-white font-black text-lg">
                    <i class="ph-bold ph-chat-teardrop-dots text-white text-xl"></i>
                </div>
                <div class="flex flex-col justify-center">
                    <div class="flex items-center gap-1.5">
                        <h1 class="forum-hdr text-lg font-black text-[#121316] tracking-tight leading-tight m-0 p-0">Pembda Space</h1>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <span class="text-[10px] text-[#ff3823] font-mono-code font-black uppercase tracking-wider leading-none mt-0.5">COMMUNITY & STEAM LOUNGE</span>
                </div>
            </div>
            <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="p-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition">
                <i class="ph-bold ph-list text-xl"></i>
            </button>
        </div>

        <!-- CHANNEL SIDEBAR (Left) -->
        <div :class="mobileSidebarOpen ? 'block' : 'hidden'" class="md:block w-full md:w-64 lg:w-72 bg-white border-r-2 border-[#121316] flex-shrink-0 flex flex-col transition-all duration-300 relative z-20">
            
            <div class="p-5 h-full flex flex-col gap-6 max-h-[85vh] overflow-y-auto no-scrollbar">
                <!-- Logo (Desktop) -->
                <div class="hidden md:flex items-center gap-3 px-1 mb-1">
                    <div class="w-11 h-11 rounded-xl bg-[#ff3823] border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center flex-shrink-0 text-white font-black">
                        <i class="ph-bold ph-chat-teardrop-dots text-white text-2xl"></i>
                    </div>
                    <div class="flex flex-col justify-center min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h1 class="forum-hdr text-lg font-black text-[#121316] tracking-tight leading-tight m-0 p-0">Pembda Space</h1>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                        <span class="text-[10px] text-[#ff3823] font-mono-code font-black uppercase tracking-wider leading-none mt-0.5">SOCIAL & STEAM</span>
                    </div>
                </div>

                <nav class="flex-1 space-y-6">
                    <!-- All Channels -->
                    <div class="space-y-1">
                        <a href="{{ route('forum.index', array_filter(['search' => $search])) }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ !$category && !(isset($activeGroup) && $activeGroup) ? 'channel-active-tactile' : 'text-[#121316] font-bold channel-hover-tactile' }}">
                            <div class="flex items-center gap-2.5">
                                <i class="ph-bold ph-compass text-lg {{ !$category && !(isset($activeGroup) && $activeGroup) ? 'text-[#121316]' : 'text-[#ff3823]' }}"></i>
                                <span class="text-xs sm:text-sm font-extrabold">Semua Saluran</span>
                            </div>
                        </a>
                    </div>

                    <!-- Class Squads (Rombel & Guru) -->
                    @if(isset($userGroups) && count($userGroups) > 0)
                        <div class="space-y-1.5" x-data="{ expanded: true }">
                            <button @click="expanded = !expanded" class="w-full flex items-center justify-between px-2 py-1 text-[11px] font-mono-code font-black text-[#121316] uppercase tracking-wider group">
                                <span class="flex items-center gap-1.5">
                                    <i class="ph-bold ph-users-three text-[#2563eb] text-sm"></i>
                                    <span>👥 Class Squads</span>
                                </span>
                                <i class="ph-bold ph-caret-down text-[#121316] transition-transform duration-200" :class="expanded ? '' : '-rotate-90'"></i>
                            </button>
                            <div x-show="expanded" class="space-y-1">
                                @foreach($userGroups as $grp)
                                    @php
                                        $grpName = $grp->name ?? 'Class Squad';
                                        $cleanGrpName = trim(preg_replace('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\s]+/u', '', $grpName));
                                        $grpIcon = $grp->icon ?? '💬';
                                        $memberCount = $grp->calculated_member_count ?? count($grp->members ?? []);
                                        $isGroupActive = isset($activeGroup) && $activeGroup && $activeGroup->id === $grp->id;
                                    @endphp
                                    <a href="{{ route('forum.index', ['group' => $grp->id]) }}" 
                                       title="{{ $cleanGrpName }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-150 {{ $isGroupActive ? 'channel-active-tactile' : 'text-[#121316] font-bold channel-hover-tactile' }} group">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            <span class="text-sm shrink-0">{{ $grpIcon }}</span>
                                            <span class="text-xs truncate font-extrabold leading-tight">{{ $cleanGrpName }}</span>
                                        </div>
                                        @if($memberCount > 0)
                                            <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border border-[#121316] {{ $isGroupActive ? 'bg-[#121316] text-white' : 'bg-[#faf8f5] text-[#121316]' }} shrink-0 ml-1.5">{{ $memberCount }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @foreach($channelGroups as $groupName => $channels)
                    <div class="space-y-1.5" x-data="{ expanded: true }">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between px-2 py-1 text-[11px] font-mono-code font-black text-[#121316] uppercase tracking-wider group">
                            <span>{{ $groupName }}</span>
                            <i class="ph-bold ph-caret-down text-[#121316] transition-transform duration-200" :class="expanded ? '' : '-rotate-90'"></i>
                        </button>
                        <div x-show="expanded" class="space-y-1">
                            @foreach($channels as $catKey)
                                @php
                                    $catLabel = \App\Models\ForumThread::CATEGORIES[$catKey] ?? $catKey;
                                    $isActive = $category === $catKey;
                                    $count = $counts[$catKey] ?? 0;
                                    preg_match('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}]/u', $catLabel, $matches);
                                    $emoji = $matches[0] ?? '💬';
                                    $cleanLabel = trim(str_replace($emoji, '', $catLabel));
                                @endphp
                                <a href="{{ route('forum.index', array_filter(['category' => $catKey, 'search' => $search])) }}" 
                                   class="flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-150 {{ $isActive ? 'channel-active-tactile' : 'text-[#121316] font-bold channel-hover-tactile' }}">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <span class="text-sm shrink-0">{{ $emoji }}</span>
                                        <span class="text-xs truncate font-extrabold">{{ $cleanLabel }}</span>
                                    </div>
                                    @if($count > 0)
                                        <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border border-[#121316] {{ $isActive ? 'bg-[#121316] text-white' : 'bg-[#faf8f5] text-[#121316]' }}">{{ $count }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </nav>

                <!-- Stats Footer -->
                <div class="mt-auto pt-4 border-t-2 border-[#121316] pb-1 font-mono-code">
                    <div class="flex justify-between items-center px-1 text-[11px] font-bold text-[#121316]">
                        <div class="flex items-center gap-1.5" title="Online dalam 15 menit terakhir">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $onlineCount }} Online</span>
                        </div>
                        <div class="px-2 py-0.5 bg-[#faf8f5] border border-[#121316] rounded-md">{{ $totalThreads }} Topik</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN FEED (Center) -->
        <div class="flex-1 flex flex-col min-w-[300px] w-full graph-canvas p-4 sm:p-6 lg:p-7 max-h-[85vh] overflow-y-auto no-scrollbar">
            
            @if(isset($activeGroup) && $activeGroup)
                @php
                    $isEkskulGroup = $activeGroup->type === 'extracurricular' || $activeGroup->extracurricular;
                    $ekskulModel = $activeGroup->extracurricular;
                    $memberCount = $ekskulModel ? ($ekskulModel->activeMembers?->count() ?? count($activeGroup->members)) : count($activeGroup->members);
                @endphp
                <!-- SQUAD LOUNGE TACTILE CARD -->
                <div class="mb-6 rounded-2xl p-5 bg-white border-2 border-[#121316] shadow-[4px_4px_0px_#121316]">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                        <div class="flex items-start gap-4 min-w-0 flex-1">
                            <div class="w-14 h-14 rounded-2xl bg-[#fde047] border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-3xl shrink-0">
                                {{ $activeGroup->icon ?? '💬' }}
                            </div>

                            <div class="min-w-0 space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-[#121316] text-white">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5 shrink-0"></span>
                                        <span>{{ $isEkskulGroup ? 'SQUAD LOUNGE' : 'CLASS SQUAD' }}</span>
                                    </span>
                                    @if($isEkskulGroup && $ekskulModel)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-[#fde047] text-[#121316] border border-[#121316]">
                                            <span class="mr-1 text-amber-900 shrink-0">★</span>
                                            <span>{{ $ekskulModel->scope_label ?? 'Unit Ekskul' }}</span>
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-lg sm:text-xl font-black text-[#121316] tracking-tight truncate">
                                    {{ $activeGroup->name }}
                                </h2>
                                <p class="text-xs font-semibold text-[#555] line-clamp-1">
                                    {{ $activeGroup->description ?: 'Ruang koordinasi dan diskusi resmi anggota squad.' }}
                                </p>

                                <div class="flex items-center gap-2 pt-1 flex-wrap text-xs font-bold">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#faf8f5] border border-[#121316] text-[#121316] font-mono-code text-[11px]">
                                        <i class="ph-bold ph-users text-[#2563eb] text-sm mr-1.5 shrink-0"></i>
                                        <span>{{ $memberCount }} Anggota</span>
                                    </span>
                                    @if($isEkskulGroup && $ekskulModel)
                                        @if($ekskulModel->advisor && trim($ekskulModel->advisor->name ?? ''))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#faf8f5] border border-[#121316] text-[#121316] text-[11px]">
                                            <i class="ph-bold ph-chalkboard-teacher text-emerald-600 text-sm mr-1.5 shrink-0"></i>
                                            <span>Pembina: {{ $ekskulModel->advisor->name }}</span>
                                        </span>
                                        @endif
                                        @if($ekskulModel->leader && trim($ekskulModel->leader->full_name ?? ''))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#faf8f5] border border-[#121316] text-[#121316] text-[11px]">
                                            <i class="ph-bold ph-crown text-amber-600 text-sm mr-1.5 shrink-0"></i>
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
                                <i class="ph-bold ph-pencil-simple text-sm shrink-0"></i>
                                <span>Tulis Post</span>
                            </a>
                            @if($isEkskulGroup)
                            <a href="{{ route('siswa.ekskul.index') }}" 
                               class="btn-tactile-white px-3.5 py-2 rounded-xl text-xs font-black flex items-center gap-1.5 whitespace-nowrap">
                                <i class="ph-bold ph-sitemap text-sm text-[#2563eb] shrink-0"></i>
                                <span>Roster</span>
                            </a>
                            @endif
                            <a href="{{ route('forum.index') }}" 
                               class="btn-tactile-white px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-1 whitespace-nowrap"
                               title="Kembali ke Lobi Utama">
                                <i class="ph-bold ph-x text-xs shrink-0 text-rose-600"></i>
                                <span>Lobi</span>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- HERO LOUNGE PEMBDA SPACE (NEO-BRUTALIST) -->
                <div class="mb-6 relative rounded-2xl overflow-hidden border-2 border-[#121316] bg-white shadow-[4px_4px_0px_#121316] p-6 sm:p-7">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#fde047] border border-[#121316] rounded-full text-[#121316] text-[10px] sm:text-[11px] font-mono-code font-black tracking-wider mb-3 shadow-[1px_1px_0px_#121316]">
                                <span>✱ PEMBDA SPACE</span>
                                <span>·</span>
                                <span>SOCIAL & STEAM LOUNGE</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-black text-[#121316] tracking-tight leading-snug">
                                Ruang Kolaborasi, Diskusi & <span class="highlight-marker">Karya Nyata.</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-[#444] font-medium mt-1.5 max-w-xl leading-relaxed">
                                Wadah interaksi aktif siswa, bapak/ibu guru lintas SMAS Pembda 1, SMPS Pembda 2, dan SMKS Pembda Nias untuk bertukar ide, karya sains, persiapan karir, dan ekskul.
                            </p>
                            
                            <!-- Quick Stats Chips -->
                            <div class="flex items-center gap-2.5 mt-4 flex-wrap text-[11px] font-mono-code font-bold">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#faf8f5] border border-[#121316] text-[#121316]">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>{{ $onlineCount }} Online</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#faf8f5] border border-[#121316] text-[#121316]">
                                    <i class="ph-bold ph-chat-circle text-[#ff3823]"></i>
                                    <span>{{ $totalThreads }} Topik Diskusi</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#fef3c7] border border-[#121316] text-[#92400e]">
                                    <span>🧱 Menara Prestasi Aktif</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row md:flex-col items-stretch gap-2.5 shrink-0 w-full sm:w-auto">
                            <a href="{{ route('forum.create') }}" class="btn-tactile-red px-5 py-3 rounded-xl text-xs font-black flex items-center justify-center gap-2 whitespace-nowrap">
                                <i class="ph-bold ph-plus-circle text-base"></i>
                                <span>+ Buat Topik Baru</span>
                            </a>
                            <button onclick="triggerPwaInstall()" class="btn-tactile-white px-4 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 whitespace-nowrap">
                                <i class="ph-bold ph-device-mobile text-sm text-[#2563eb]"></i>
                                <span>Install App Mobile</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border-2 border-rose-600 text-rose-900 flex items-center justify-between shadow-[3px_3px_0px_#e11d48]">
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-warning-circle text-2xl text-rose-600 shrink-0"></i>
                        <span class="text-xs sm:text-sm font-black">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border-2 border-emerald-600 text-emerald-900 flex items-center justify-between shadow-[3px_3px_0px_#059669]">
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-check-circle text-2xl text-emerald-600 shrink-0"></i>
                        <span class="text-xs sm:text-sm font-black">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Header & Search Topbar (Neo-Brutalist) -->
            <div class="p-3.5 sm:p-4 mb-6 sticky top-0 z-30 flex flex-col sm:flex-row gap-3 items-center justify-between bg-white/95 backdrop-blur-md border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316]">
                <form method="GET" action="{{ route('forum.index') }}" class="w-full sm:max-w-md relative">
                    @if($category) <input type="hidden" name="category" value="{{ $category }}"> @endif
                    @if(isset($activeGroup) && $activeGroup) <input type="hidden" name="group" value="{{ $activeGroup->id }}"> @endif
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#121316]">
                        <i class="ph-bold ph-magnifying-glass text-base"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" 
                           class="w-full pl-10 pr-4 py-2 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] placeholder-[#777] shadow-[2px_2px_0px_#121316] focus:outline-none focus:shadow-[3px_3px_0px_#ff3823] transition" 
                           placeholder="{{ isset($activeGroup) && $activeGroup ? 'Cari di ' . $activeGroup->name . '...' : 'Cari obrolan, proyek, atau karya...' }}">
                </form>
                
                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    @if($search || $category || (isset($activeGroup) && $activeGroup))
                        <a href="{{ route('forum.index') }}" class="btn-tactile-white px-3 py-2 rounded-xl text-xs font-black flex items-center gap-1.5 whitespace-nowrap">
                            <i class="ph-bold ph-x-circle text-rose-600"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                    <a href="{{ route('forum.create', (isset($activeGroup) && $activeGroup) ? ['group' => $activeGroup->id] : []) }}" 
                       class="btn-tactile-red px-4 sm:px-5 py-2 rounded-xl text-xs font-black flex items-center gap-1.5 whitespace-nowrap">
                        <i class="ph-bold ph-plus text-sm"></i>
                        <span>{{ isset($activeGroup) && $activeGroup ? 'Tulis di Squad' : 'Buat Post' }}</span>
                    </a>
                </div>
            </div>

            <!-- Pembda Tower (Menara Prestasi) Widget Neo-Brutalist -->
            <div class="mb-6 bg-white border-2 border-[#121316] rounded-2xl overflow-hidden shadow-[4px_4px_0px_#121316]" x-data="pembdaTower()">
                <!-- Header Bar -->
                <div class="p-4 border-b-2 border-[#121316] flex justify-between items-center bg-[#fef08a] cursor-pointer hover:bg-[#fde047] transition" @click="toggleCollapse()">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#ff3823] border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-white font-black text-xl">
                            🧱
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="forum-hdr text-base sm:text-lg font-black text-[#121316] tracking-tight leading-tight">Menara Prestasi Pembda</h2>
                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full bg-[#121316] text-[#fde047] flex items-center gap-1">
                                    <span>TINGGI:</span> <span x-text="stats.total_height + ' LANTAI'"></span>
                                </span>
                            </div>
                            <span class="text-xs text-[#444] font-semibold hidden sm:inline">Bersama membangun menara motivasi & cita-cita Perguruan Pembda. 1 Bata / Hari.</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2" @click.stop>
                        <button @click="showBuildModal = true" 
                                class="btn-tactile-red text-xs font-black px-4 py-2 rounded-xl flex items-center gap-1.5 whitespace-nowrap">
                            <i class="ph-bold ph-plus-circle text-base"></i>
                            <span>Pasang Bata</span>
                        </button>
                        <button @click="toggleCollapse()" class="p-2 bg-white border-2 border-[#121316] rounded-xl text-[#121316] shadow-[2px_2px_0px_#121316] hover:bg-[#faf8f5] transition">
                            <i class="ph-bold ph-caret-down text-base transition-transform duration-300" :class="isCollapsed ? '' : 'rotate-180'"></i>
                        </button>
                    </div>
                </div>

                <!-- Body / Tower Wall -->
                <div x-show="!isCollapsed" x-transition.opacity.duration.300ms class="p-4 sm:p-6 bg-[#faf8f5] border-t border-[#121316]/10">
                    <!-- Status Banner -->
                    <div x-show="statusMessage" x-transition.opacity
                         class="mb-4 p-3 rounded-xl text-xs sm:text-sm font-black flex items-center justify-between border-2 border-emerald-600 bg-emerald-50 text-emerald-900 shadow-[2px_2px_0px_#059669]">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-check-circle text-emerald-600 text-lg"></i>
                            <span x-text="statusMessage"></span>
                        </div>
                        <button @click="statusMessage = ''" class="text-slate-500 hover:text-slate-900"><i class="ph-bold ph-x"></i></button>
                    </div>

                    <!-- Daily Status Banner -->
                    <div x-show="hasPlacedToday" class="mb-4 p-3 bg-amber-50 border-2 border-amber-600 rounded-xl text-xs font-black text-amber-900 flex items-center justify-between shadow-[2px_2px_0px_#d97706]">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-seal-check text-amber-600 text-lg"></i>
                            <span>Bata motivasi Anda telah terpasang hari ini di Menara Prestasi! (+10 Poin Tambahan)</span>
                        </div>
                        <span class="text-[10px] font-mono-code bg-amber-200 text-amber-950 px-2 py-0.5 rounded-full font-black">SELESAI HARI INI</span>
                    </div>

                    <!-- Pesan Terakhir -->
                    <template x-if="bricks.length > 0">
                        <div class="mb-6 mx-auto w-full max-w-2xl bg-white border-2 border-[#121316] rounded-xl p-4 shadow-[3px_3px_0px_#121316] text-center relative overflow-hidden">
                            <p class="text-[10px] text-[#ff3823] font-mono-code font-black mb-1 uppercase tracking-widest">★ MOTIVASI TERAKHIR DIPASANG</p>
                            <p class="text-sm sm:text-base font-bold italic text-[#121316]" x-text="`&quot;${bricks[bricks.length - 1].message}&quot;`"></p>
                            <p class="text-[11px] text-[#666] mt-2 font-mono-code font-semibold" x-text="`- ${bricks[bricks.length - 1].user_name} (${bricks[bricks.length - 1].school_name})`"></p>
                        </div>
                    </template>

                    <!-- TOWER VISUAL (Piramida — lebar bawah, mengecil ke atas) -->
                    <div class="flex flex-col items-center py-2">

                        <!-- Tower Spire / Puncak Segitiga -->
                        <div class="flex flex-col items-center w-full mt-2">
                            <div class="flex flex-col border-2 border-[#121316] shadow-[2px_2px_0px_#121316] animate-bounce hover:scale-125 transition-all cursor-pointer mb-1 z-10" style="width: 38px; height: 26px;" title="Perguruan Pembda Nias">
                                <div style="height: 50%; width: 100%; background-color: #ff3823;"></div>
                                <div style="height: 50%; width: 100%; background-color: #ffffff;"></div>
                            </div>
                            <div class="w-0 h-0 border-l-[32px] border-r-[32px] border-b-[32px] border-l-transparent border-r-transparent border-b-[#ff3823] drop-shadow-md"></div>
                            <div class="bg-[#121316] text-[#fde047] text-[10px] sm:text-xs font-mono-code font-black uppercase tracking-[0.2em] text-center py-1.5 shadow-md w-full max-w-[260px] rounded-b border-2 border-[#121316]">
                                ★ PUNCAK PRESTASI PEMBDA ★
                            </div>
                        </div>

                        <!-- Tower Wall — Bata piramida -->
                        <div class="w-full flex flex-col items-center" style="max-height: 420px; overflow-y: auto;">
                            <div class="w-full max-w-[550px] flex flex-col items-center gap-[3px] py-4 mx-auto">
                                <template x-for="(row, rowIdx) in pyramidRows" :key="rowIdx">
                                    <div class="flex justify-center gap-[3px] mx-auto" :style="'width: ' + (row.length * 10) + '%;'">
                                        <template x-for="(b, bIdx) in row" :key="bIdx">
                                            <div class="flex-1">
                                                <!-- Bata Terisi -->
                                                <template x-if="b">
                                                    <div @click="selectedBrick = b"
                                                         class="w-full h-6 sm:h-8 md:h-10 lg:h-12 cursor-pointer hover:-translate-y-1 hover:scale-105 hover:z-10 transition-all shadow-sm border-2 border-[#121316] rounded-md relative group flex items-center justify-center font-mono-code font-black"
                                                         :class="{
                                                             'bg-[#3b82f6] text-white': b.color === 'indigo' || b.color === 'cyan',
                                                             'bg-[#10b981] text-white': b.color === 'emerald',
                                                             'bg-[#fde047] text-[#121316]': b.color === 'amber',
                                                             'bg-[#ff3823] text-white': b.color === 'rose',
                                                             'bg-[#8b5cf6] text-white': b.color === 'purple'
                                                         }"
                                                         title="Klik untuk melihat pesan">
                                                        <span class="text-[7px] sm:text-[9px] select-none" x-text="b.brick_number"></span>
                                                    </div>
                                                </template>
                                                <!-- Bata Kosong -->
                                                <template x-if="!b">
                                                    <div class="w-full h-6 sm:h-8 md:h-10 lg:h-12 border-2 border-dashed border-[#121316]/30 bg-white/60 rounded-md"></div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Tower Foundation / Fondasi (Neo-Brutalist) -->
                        <div class="bg-[#121316] text-white text-xs sm:text-sm font-mono-code font-black uppercase tracking-wider text-center py-3.5 px-6 rounded-2xl shadow-[4px_4px_0px_#121316] border-2 border-[#121316] flex flex-wrap items-center justify-center gap-2 sm:gap-3 w-full max-w-xl">
                            <span class="text-white flex items-center gap-1.5"><span>🏗️</span> <span>FONDASI MENARA</span></span>
                            <span class="text-[#fde047]">·</span>
                            <span class="bg-[#fde047] text-[#121316] px-2.5 py-0.5 rounded-lg border border-[#121316] font-black">
                                <span x-text="stats.total_bricks"></span> BATA TERPASANG
                            </span>
                            <span class="text-[#fde047]">·</span>
                            <span class="bg-white text-[#121316] px-2.5 py-0.5 rounded-lg border border-[#121316] font-black">
                                TINGGI <span x-text="stats.total_height"></span> LANTAI
                            </span>
                        </div>

                    </div>
                </div>

                <!-- Build Brick Modal (Neo-Brutalist) -->
                <div x-show="showBuildModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
                    <div @click.away="showBuildModal = false" class="bg-white border-2 border-[#121316] rounded-2xl w-full max-w-md overflow-hidden shadow-[8px_8px_0px_#121316]">
                        <div class="p-4 border-b-2 border-[#121316] flex justify-between items-center bg-[#fde047]">
                            <h3 class="forum-hdr text-base font-black text-[#121316] flex items-center gap-2">
                                <span>🧱</span>
                                <span>Pasang Bata Motivasi Saya</span>
                            </h3>
                            <button @click="showBuildModal = false" class="w-8 h-8 rounded-lg bg-white border border-[#121316] text-[#121316] hover:bg-rose-50 flex items-center justify-center font-bold">
                                <i class="ph-bold ph-x text-base"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4 text-sm text-[#121316]">
                            <div x-show="hasPlacedToday" class="p-3 bg-amber-50 border-2 border-amber-600 rounded-xl text-xs font-black text-amber-900 text-center shadow-[2px_2px_0px_#d97706]">
                                ⚠️ Anda sudah meletakkan bata hari ini. Setiap pengguna memiliki 1 jatah bata per hari.
                            </div>

                            <div :class="hasPlacedToday ? 'opacity-50 pointer-events-none' : ''">
                                <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-wider mb-1.5">Pesan Motivasi & Harapan (Maks 140 Karakter):</label>
                                <textarea x-model="formMessage" 
                                          maxlength="140" 
                                          rows="3" 
                                          placeholder="Contoh: Semangat belajar untuk angkatan 2026! Perguruan Pembda Nias Jaya selalu."
                                          class="w-full p-3 rounded-xl border-2 border-[#121316] bg-[#faf8f5] text-xs font-bold text-[#121316] focus:outline-none focus:shadow-[3px_3px_0px_#ff3823] transition resize-none"></textarea>
                                <div class="text-right text-[10px] font-mono-code text-[#666] mt-1" x-text="formMessage.length + ' / 140 karakter'"></div>
                            </div>

                            <div :class="hasPlacedToday ? 'opacity-50 pointer-events-none' : ''">
                                <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-wider mb-2">Pilih Warna Bata Anda:</label>
                                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                                    <button @click="formColor = 'indigo'" type="button" class="py-2 px-1 rounded-xl font-mono-code font-black text-xs text-white bg-[#3b82f6] border-2 border-[#121316] flex items-center justify-center gap-1 shadow-[2px_2px_0px_#121316]" :class="formColor === 'indigo' ? 'ring-2 ring-[#121316] translate-y-0.5 shadow-none' : ''">
                                        <i x-show="formColor === 'indigo'" class="ph-bold ph-check"></i> Biru
                                    </button>
                                    <button @click="formColor = 'emerald'" type="button" class="py-2 px-1 rounded-xl font-mono-code font-black text-xs text-white bg-[#10b981] border-2 border-[#121316] flex items-center justify-center gap-1 shadow-[2px_2px_0px_#121316]" :class="formColor === 'emerald' ? 'ring-2 ring-[#121316] translate-y-0.5 shadow-none' : ''">
                                        <i x-show="formColor === 'emerald'" class="ph-bold ph-check"></i> Hijau
                                    </button>
                                    <button @click="formColor = 'amber'" type="button" class="py-2 px-1 rounded-xl font-mono-code font-black text-xs text-[#121316] bg-[#fde047] border-2 border-[#121316] flex items-center justify-center gap-1 shadow-[2px_2px_0px_#121316]" :class="formColor === 'amber' ? 'ring-2 ring-[#121316] translate-y-0.5 shadow-none' : ''">
                                        <i x-show="formColor === 'amber'" class="ph-bold ph-check"></i> Kuning
                                    </button>
                                    <button @click="formColor = 'rose'" type="button" class="py-2 px-1 rounded-xl font-mono-code font-black text-xs text-white bg-[#ff3823] border-2 border-[#121316] flex items-center justify-center gap-1 shadow-[2px_2px_0px_#121316]" :class="formColor === 'rose' ? 'ring-2 ring-[#121316] translate-y-0.5 shadow-none' : ''">
                                        <i x-show="formColor === 'rose'" class="ph-bold ph-check"></i> Merah
                                    </button>
                                    <button @click="formColor = 'purple'" type="button" class="py-2 px-1 rounded-xl font-mono-code font-black text-xs text-white bg-[#8b5cf6] border-2 border-[#121316] flex items-center justify-center gap-1 shadow-[2px_2px_0px_#121316]" :class="formColor === 'purple' ? 'ring-2 ring-[#121316] translate-y-0.5 shadow-none' : ''">
                                        <i x-show="formColor === 'purple'" class="ph-bold ph-check"></i> Ungu
                                    </button>
                                </div>
                            </div>

                            <div class="pt-3 border-t-2 border-[#121316]/10 flex items-center justify-end gap-2.5">
                                <button @click="showBuildModal = false" type="button" class="btn-tactile-white px-4 py-2 text-xs font-black">Batal</button>
                                <button @click="submitBrick()" 
                                        type="button" 
                                        :disabled="hasPlacedToday || isSubmitting || !formMessage.trim()"
                                        class="btn-tactile-red px-5 py-2 text-xs font-black disabled:opacity-50 flex items-center gap-1.5">
                                    <i x-show="isSubmitting" class="ph-bold ph-spinner animate-spin"></i>
                                    <span>🧱 Pasang Bata</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Brick Modal (Neo-Brutalist) -->
                <div x-show="selectedBrick" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
                    <div @click.away="selectedBrick = null" class="bg-white border-2 border-[#121316] rounded-2xl w-full max-w-sm overflow-hidden shadow-[8px_8px_0px_#121316]">
                        <div class="p-3.5 border-b-2 border-[#121316] flex justify-between items-center bg-[#faf8f5]">
                            <span class="text-xs font-mono-code font-black text-[#121316]" x-text="selectedBrick ? ('BATA #' + selectedBrick.brick_number) : ''"></span>
                            <button @click="selectedBrick = null" class="w-7 h-7 rounded-lg bg-white border border-[#121316] text-[#121316] hover:bg-rose-50 flex items-center justify-center font-bold">
                                <i class="ph-bold ph-x text-sm"></i>
                            </button>
                        </div>
                        <template x-if="selectedBrick">
                            <div class="p-5 flex flex-col items-center text-center gap-3">
                                <div class="w-12 h-12 rounded-xl border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-white font-black text-xl"
                                     :class="{
                                         'bg-[#3b82f6]': selectedBrick.color === 'indigo' || selectedBrick.color === 'cyan',
                                         'bg-[#10b981]': selectedBrick.color === 'emerald',
                                         'bg-[#fde047] text-[#121316]': selectedBrick.color === 'amber',
                                         'bg-[#ff3823]': selectedBrick.color === 'rose',
                                         'bg-[#8b5cf6]': selectedBrick.color === 'purple'
                                     }">🧱</div>
                                
                                <p class="text-sm font-bold text-[#121316] italic" x-text="`“${selectedBrick.message}”`"></p>

                                <div class="pt-3 border-t-2 border-[#121316]/10 w-full text-xs text-[#555] font-semibold space-y-1">
                                    <div>Diletakkan oleh: <strong class="text-[#121316]" x-text="selectedBrick.user_name"></strong></div>
                                    <div>Unit: <span class="font-bold text-[#2563eb]" x-text="selectedBrick.school_name"></span></div>
                                    <div class="text-[10px] font-mono-code text-[#777] mt-1" x-text="selectedBrick.time_ago"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- HIGHLIGHT SECTION (Neo-Brutalist) -->
            @if(isset($latestHighlight) || isset($trendingHighlight))
                <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Postingan Terbaru -->
                    @if(isset($latestHighlight))
                        <a href="{{ route('forum.show', $latestHighlight) }}" class="block bg-white border-2 border-[#121316] rounded-2xl p-4 sm:p-5 shadow-[3px_3px_0px_#121316] hover:shadow-[5px_5px_0px_#121316] transition group relative overflow-hidden">
                            <div class="flex items-center gap-2 mb-2.5">
                                <span class="bg-[#ff3823] text-white text-[10px] font-mono-code font-black uppercase tracking-wider px-2 py-0.5 rounded-md border border-[#121316] flex items-center gap-1">
                                    <i class="ph-bold ph-sparkle"></i> TERBARU
                                </span>
                                <span class="text-[11px] font-mono-code font-bold text-[#555]">{{ $latestHighlight->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-black text-[#121316] text-base sm:text-lg leading-tight mb-3 group-hover:text-[#ff3823] transition">{{ $latestHighlight->title }}</h3>
                            <div class="flex items-center gap-2 mt-auto pt-2 border-t border-[#121316]/10">
                                <img src="{{ $latestHighlight->user->avatar_url }}" class="w-6 h-6 rounded-full border border-[#121316]">
                                <span class="text-xs font-bold text-[#121316]">{{ $latestHighlight->user->name }}</span>
                            </div>
                        </a>
                    @endif

                    <!-- Postingan Ter-rame (Trending) -->
                    @if(isset($trendingHighlight))
                        <a href="{{ route('forum.show', $trendingHighlight) }}" class="block bg-white border-2 border-[#121316] rounded-2xl p-4 sm:p-5 shadow-[3px_3px_0px_#121316] hover:shadow-[5px_5px_0px_#121316] transition group relative overflow-hidden">
                            <div class="flex items-center gap-2 mb-2.5">
                                <span class="bg-[#fde047] text-[#121316] text-[10px] font-mono-code font-black uppercase tracking-wider px-2 py-0.5 rounded-md border border-[#121316] flex items-center gap-1">
                                    <i class="ph-bold ph-fire text-[#ff3823]"></i> HANGAT
                                </span>
                                <span class="text-[11px] font-mono-code font-black text-[#121316] bg-[#faf8f5] px-2 py-0.5 rounded border border-[#121316]">{{ $trendingHighlight->replies_count + $trendingHighlight->likes_count }} Interaksi</span>
                            </div>
                            <h3 class="font-black text-[#121316] text-base sm:text-lg leading-tight mb-3 group-hover:text-[#ff3823] transition">{{ $trendingHighlight->title }}</h3>
                            <div class="flex items-center gap-2 mt-auto pt-2 border-t border-[#121316]/10">
                                <img src="{{ $trendingHighlight->user->avatar_url }}" class="w-6 h-6 rounded-full border border-[#121316]">
                                <span class="text-xs font-bold text-[#121316]">{{ $trendingHighlight->user->name }}</span>
                            </div>
                        </a>
                    @endif
                </div>
            @endif

            <!-- Feed List (Neo-Brutalist Cards) -->
            <div class="space-y-4 pb-10">
                @forelse($threads as $thread)
                    @php
                        $author = $thread->user;
                        $isLiked = $thread->isLikedBy(auth()->user());
                        $catLabel = $thread->category_label;
                        
                        $catBadge = match($thread->category) {
                            'diskusi' => 'bg-[#e0e7ff] text-[#3730a3]',
                            'ekskul_showcase' => 'bg-[#f3e8ff] text-[#6b21a8]',
                            'ekskul_recruit' => 'bg-[#fef3c7] text-[#92400e]',
                            'sharing' => 'bg-[#d1fae5] text-[#065f46]',
                            'info' => 'bg-[#fef3c7] text-[#92400e]',
                            'performance' => 'bg-[#f3e8ff] text-[#6b21a8]',
                            'art_gallery' => 'bg-[#fce7f3] text-[#9d174d]',
                            'talent' => 'bg-[#ede9fe] text-[#5b21b6]',
                            'gaming' => 'bg-[#ffe4e6] text-[#9f1239]',
                            'tanya_jawab' => 'bg-[#cffafe] text-[#155e75]',
                            'trending' => 'bg-[#ffedd5] text-[#9a3412]',
                            'project_idea' => 'bg-[#dbeafe] text-[#1e40af]',
                            'committee' => 'bg-[#ccfbf1] text-[#115e59]',
                            'charity' => 'bg-[#fee2e2] text-[#991b1b]',
                            default => 'bg-[#f1f5f9] text-[#334155]'
                        };
                    @endphp
                    
                    <div class="tactile-card p-5 {{ $thread->is_pinned ? 'ring-2 ring-[#fde047]' : '' }}">
                        <a href="{{ route('forum.show', $thread) }}" class="block">
                            <!-- Author & Meta -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $author->avatar_url }}" 
                                         class="w-10 h-10 rounded-xl border-2 border-[#121316] shadow-[1px_1px_0px_#121316] object-cover">
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-extrabold text-[#121316] text-sm">{{ $author->name }}</span>
                                            <span class="text-[10px] font-mono-code font-black px-1.5 py-0.5 bg-[#faf8f5] text-[#121316] border border-[#121316] rounded uppercase tracking-wider">{{ $author->role }}</span>
                                            @if($author->ekskul_flair)
                                            <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border border-[#121316] bg-[#fde047] text-[#121316]" title="{{ $author->ekskul_flair['label'] }}">
                                                {{ $author->ekskul_flair['label'] }}
                                            </span>
                                            @endif
                                            <span class="text-xs text-[#666] font-mono-code">&bull; {{ $thread->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                            <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md border border-[#121316] {{ $catBadge }}">
                                                {{ $catLabel }}
                                            </span>
                                            @if($thread->category === 'ekskul_showcase')
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#fde047] text-[#121316] border border-[#121316] rounded-md flex items-center gap-1">
                                                    🏆 Official Ekskul
                                                </span>
                                            @elseif($thread->category === 'ekskul_recruit')
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#ff3823] text-white border border-[#121316] rounded-md flex items-center gap-1 animate-pulse">
                                                    📢 Open Recruitment
                                                </span>
                                            @endif
                                            @if($thread->category === 'tanya_jawab')
                                                @if($thread->hasAcceptedReply())
                                                    <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#10b981] text-white border border-[#121316] rounded-md flex items-center gap-1">
                                                        <i class="ph-bold ph-check-circle"></i> Terjawab
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#fde047] text-[#121316] border border-[#121316] rounded-md flex items-center gap-1">
                                                        <i class="ph-bold ph-question"></i> Bantu Jawab
                                                    </span>
                                                @endif
                                            @endif
                                            @if($thread->category === 'gaming' && $thread->game_name)
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-rose-100 text-rose-900 border border-[#121316] rounded-md flex items-center gap-1">
                                                    <i class="ph-bold ph-game-controller"></i> {{ $thread->game_name }}
                                                </span>
                                            @endif
                                            @if($thread->category === 'sharing' && $thread->file_category)
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-emerald-100 text-emerald-900 border border-[#121316] rounded-md flex items-center gap-1">
                                                    <i class="ph-bold ph-folder-open"></i> {{ $thread->file_category }}
                                                </span>
                                            @endif
                                            @if($thread->is_pinned)
                                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#fde047] text-[#121316] border border-[#121316] rounded-md flex items-center gap-1">
                                                    <i class="ph-bold ph-push-pin"></i> Tersemat
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions (Author / Admin) -->
                                @if(auth()->id() === $thread->user_id || auth()->user()->isSuperAdmin())
                                    <div class="flex items-center gap-1.5 flex-shrink-0" onclick="event.stopPropagation()">
                                        <a href="{{ route('forum.edit', $thread) }}" class="p-1.5 bg-[#faf8f5] hover:bg-[#fde047] border border-[#121316] rounded-lg text-[#121316] transition" title="Edit Post">
                                            <i class="ph-bold ph-pencil-simple text-base"></i>
                                        </a>
                                        <form action="{{ route('forum.destroy', $thread) }}" method="POST" onsubmit="return confirm('Yakin hapus postingan ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-[#faf8f5] hover:bg-rose-100 border border-[#121316] rounded-lg text-rose-600 transition" title="Hapus Post">
                                                <i class="ph-bold ph-trash text-base"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="space-y-2 mt-2">
                                <h3 class="forum-hdr text-lg sm:text-xl font-black text-[#121316] group-hover:text-[#ff3823] transition-colors leading-snug">
                                    {{ $thread->title }}
                                </h3>
                                
                                @php
                                    $contentLength = mb_strlen(strip_tags($thread->content ?? ''));
                                    $isLongContent = $contentLength > 350;
                                @endphp

                                <div class="text-xs sm:text-sm text-[#333] font-medium leading-relaxed">
                                    @if($isLongContent)
                                        <p class="line-clamp-3 text-[#333]">
                                            {!! nl2br(e(Str::limit(strip_tags($thread->content), 300))) !!}
                                        </p>
                                        <a href="{{ route('forum.show', $thread) }}" class="inline-flex items-center gap-1 text-xs font-black text-[#ff3823] hover:underline mt-1">
                                            <span>Baca Selengkapnya</span>
                                            <i class="ph-bold ph-arrow-right"></i>
                                        </a>
                                    @else
                                        <div class="space-y-1">
                                            {!! nl2br(e($thread->content)) !!}
                                        </div>
                                    @endif
                                </div>

                                @if($thread->image_path)
                                    <div class="mt-3 rounded-xl overflow-hidden border-2 border-[#121316] max-w-sm max-h-56 shadow-[2px_2px_0px_#121316]">
                                        <img src="{{ asset('storage/' . $thread->image_path) }}" class="w-full h-full object-cover">
                                    </div>
                                @endif

                                <!-- Bank File Direct Download Box -->
                                @if($thread->category === 'sharing' && $thread->attachment_path)
                                    <div class="mt-3 p-3 bg-[#faf8f5] border-2 border-[#121316] rounded-xl max-w-md flex items-center justify-between gap-3 shadow-[2px_2px_0px_#121316]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-[#fde047] border border-[#121316] flex items-center justify-center text-[#121316] font-mono-code font-black text-xs flex-shrink-0 uppercase">
                                                {{ strtoupper($thread->file_extension ?: 'FILE') }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-[#121316] truncate">{{ $thread->attachment_name ?? 'Berkas Lampiran' }}</div>
                                                <div class="text-[10px] font-mono-code text-[#065f46] font-bold">{{ $thread->file_category ?: 'Dokumen Pembelajaran' }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ asset('storage/' . $thread->attachment_path) }}" download onclick="event.stopPropagation()" class="btn-tactile-emerald px-3 py-1.5 rounded-lg text-xs font-black transition flex items-center gap-1 flex-shrink-0">
                                            <i class="ph-bold ph-download-simple"></i> Unduh
                                        </a>
                                    </div>
                                @endif

                                <!-- Gaming Mabar Lobby Card -->
                                @if($thread->category === 'gaming' && ($thread->game_name || $thread->game_room_code))
                                    <div class="mt-3 p-3 bg-rose-50 border-2 border-[#121316] rounded-xl max-w-md flex items-center justify-between gap-3 shadow-[2px_2px_0px_#121316]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-[#ff3823] border border-[#121316] flex items-center justify-center text-white text-xl flex-shrink-0">
                                                <i class="ph-bold ph-game-controller"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-[#121316] truncate">{{ $thread->game_name ?: 'Lobi Mabar' }}</div>
                                                <div class="text-[11px] font-mono-code font-bold text-[#444] truncate">ID/Kode: <span class="text-[#ff3823] font-black select-all">{{ $thread->game_room_code ?: 'Tanyakan di komentar' }}</span></div>
                                            </div>
                                        </div>
                                        @if($thread->game_room_code)
                                        <button onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText('{{ addslashes($thread->game_room_code) }}'); alert('Kode/ID Game berhasil disalin!');" class="btn-tactile-red px-3 py-1.5 rounded-lg text-xs font-black flex items-center gap-1 flex-shrink-0">
                                            <i class="ph-bold ph-copy"></i> Salin ID
                                        </button>
                                        @endif
                                    </div>
                                @endif

                                <!-- Ekskul Showcase & Recruitment Card -->
                                @if(in_array($thread->category, ['ekskul_showcase', 'ekskul_recruit']))
                                    <div class="mt-3 p-3.5 bg-gradient-to-r from-purple-50 via-indigo-50 to-pink-50 border-2 border-[#121316] rounded-xl flex items-center justify-between gap-3 shadow-[2px_2px_0px_#121316]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-[#fde047] border border-[#121316] flex items-center justify-center text-[#121316] text-lg flex-shrink-0">
                                                🏆
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-[#121316] truncate">{{ $thread->group ? $thread->group->name : 'Ekstrakurikuler Yayasan Pembda' }}</div>
                                                <div class="text-[11px] font-mono-code font-bold text-purple-700">Official Squad Showcase & Recruitment</div>
                                            </div>
                                        </div>
                                        <a href="{{ route('siswa.ekskul.index') }}" onclick="event.stopPropagation()" class="btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 flex-shrink-0">
                                            <i class="ph-bold ph-sitemap text-[#2563eb]"></i>
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
                                                <span class="w-7 h-7 rounded-lg bg-[#fde047] border border-[#121316] text-[#121316] flex items-center justify-center text-sm flex-shrink-0">
                                                    <i class="ph-bold ph-chart-bar"></i>
                                                </span>
                                                <span class="text-[10px] font-mono-code font-black tracking-wider uppercase text-[#121316] bg-[#faf8f5] border border-[#121316] px-2 py-0.5 rounded-md">📊 Polling Interaktif</span>
                                            </div>
                                            <span class="text-xs font-mono-code font-black text-[#121316] flex items-center gap-1">
                                                <i class="ph-bold ph-users text-[#2563eb]"></i>
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
                                                    <div class="absolute top-0 left-0 h-full {{ $isVoted ? 'bg-[#facc15]/60' : 'bg-[#e2e8f0]' }} transition-all duration-500 pointer-events-none" 
                                                         style="width: {{ $pct }}%" 
                                                         id="poll-feed-bg-{{ $opt->id }}"></div>
                                                    
                                                    <div class="relative z-10 flex items-center justify-between gap-3 text-xs">
                                                        <div class="flex items-center gap-2 font-bold text-[#121316]">
                                                            <span class="w-5 h-5 rounded-full flex items-center justify-center border border-[#121316] text-[10px] font-mono-code font-black flex-shrink-0 {{ $isVoted ? 'bg-[#121316] text-[#fde047]' : 'bg-white text-[#121316]' }}" id="poll-feed-check-{{ $opt->id }}">
                                                                @if($isVoted)
                                                                    <i class="ph-bold ph-check"></i>
                                                                @else
                                                                    {{ $loop->iteration }}
                                                                @endif
                                                            </span>
                                                            <span class="truncate font-black">{{ $opt->option_text }}</span>
                                                        </div>
                                                        <div class="font-mono-code font-black flex items-center gap-1.5 flex-shrink-0 text-[#121316]">
                                                            <span id="poll-feed-pct-{{ $opt->id }}">{{ $pct }}%</span>
                                                            <span class="text-[10px] text-[#666]" id="poll-feed-count-{{ $opt->id }}">({{ $opt->votes_count }})</span>
                                                        </div>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                        
                                        @if(!$poll->isOpen())
                                            <div class="text-[11px] font-mono-code font-black text-amber-700 flex items-center gap-1 pt-1">
                                                <i class="ph-bold ph-lock-key"></i> Polling ini telah ditutup
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </a>

                        <!-- Action Bar -->
                        <div class="mt-4 pt-3 border-t-2 border-[#121316]/10 flex items-center flex-wrap gap-2 text-xs font-mono-code font-bold text-[#121316]">
                            <!-- Upvote/Like -->
                            <button onclick="toggleLike(this, '{{ route('forum.like', $thread) }}')" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border-2 border-[#121316] shadow-[1px_1px_0px_#121316] transition {{ $isLiked ? 'bg-[#fde047] text-[#121316] font-black' : 'bg-white hover:bg-[#faf8f5] text-[#121316]' }}">
                                <i class="ph-bold ph-thumbs-up text-sm"></i> <span class="likes-count">{{ $thread->likes->count() }}</span>
                            </button>
                            
                            <!-- Replies count -->
                            <a href="{{ route('forum.show', $thread) }}#replies" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border-2 border-[#121316] bg-white hover:bg-[#faf8f5] shadow-[1px_1px_0px_#121316] transition text-[#121316]">
                                <i class="ph-bold ph-chat-circle text-sm text-[#ff3823]"></i> <span>{{ $thread->replies->count() }}</span>
                            </a>

                            <!-- Views -->
                            <div class="flex items-center gap-1.5 px-2.5 py-1.5 text-[#555] font-bold">
                                <i class="ph-bold ph-eye text-sm"></i> <span>{{ $thread->views_count }}</span>
                            </div>

                            @if(in_array($thread->category, ['project_idea', 'committee', 'charity']))
                                <div class="flex items-center gap-1.5 px-3 py-1.5 ml-auto bg-[#e0e7ff] text-[#3730a3] border border-[#121316] rounded-lg font-black text-[11px]">
                                    <i class="ph-bold ph-users text-sm"></i> {{ $thread->approvedMembers()->count() }} Tim
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white border-2 border-[#121316] rounded-3xl p-12 text-center mt-6 shadow-[4px_4px_0px_#121316]">
                        <div class="w-16 h-16 bg-[#faf8f5] border-2 border-[#121316] rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-[2px_2px_0px_#121316]">
                            <i class="ph-bold ph-ghost text-[#121316] text-3xl"></i>
                        </div>
                        <h4 class="forum-hdr text-xl font-black text-[#121316] mb-2">Belum Ada Topik di Saluran Ini</h4>
                        <p class="text-xs sm:text-sm text-[#555] font-semibold max-w-sm mx-auto">Jadilah yang pertama membuka obrolan, berbagi ide, atau membagikan karya Anda!</p>
                        <a href="{{ route('forum.create') }}" class="btn-tactile-red inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black mt-5">
                            <i class="ph-bold ph-plus-circle text-base"></i>
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
        </div>

        <!-- SIDEBAR WIDGETS (Right) -->
        <div class="hidden xl:flex flex-col w-80 flex-shrink-0 bg-white border-l-2 border-[#121316] max-h-[85vh] overflow-hidden">
            <!-- Pinned Top User Profile Card -->
            <div class="p-4 bg-white border-b-2 border-[#121316] shrink-0">
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
                <div class="rounded-2xl p-4 text-[#121316] bg-[#fde047] border-2 border-[#121316] shadow-[3px_3px_0px_#121316]">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="relative shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-white p-0.5 border-2 border-[#121316] shadow-[1px_1px_0px_#121316] overflow-hidden flex items-center justify-center">
                                <img src="{{ $user->avatar_url }}" 
                                     alt="{{ $user->name }}" 
                                     class="w-full h-full object-cover rounded-lg"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full items-center justify-center hidden bg-[#ff3823] text-white font-black text-xl">
                                    <i class="ph-bold ph-crown"></i>
                                </div>
                            </div>
                            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-[#ff3823] text-white rounded-full flex items-center justify-center text-[10px] shadow-sm font-black border border-[#121316]" title="Pimpinan Terverifikasi">
                                👑
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-[#121316] text-sm leading-snug tracking-tight truncate" title="{{ $user->name }}">
                                {{ $user->name }}
                            </h3>
                            <p class="text-[11px] font-mono-code font-bold text-[#121316]/80 truncate mt-0.5" title="{{ $school }}">
                                {{ $school }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t-2 border-[#121316]/20 font-mono-code">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-black bg-[#121316] text-[#fde047]">
                            <i class="ph-bold ph-shield-check text-xs"></i>
                            <span>Pimpinan Yayasan</span>
                        </span>
                        <span class="text-[10px] text-[#121316] font-black uppercase tracking-wider">
                            EKSEKUTIF
                        </span>
                    </div>
                </div>
                @else
                <!-- Regular User Profile Card (Neo-Brutalist) -->
                <div class="rounded-2xl p-4 text-[#121316] bg-white border-2 border-[#121316] shadow-[3px_3px_0px_#121316]">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-[#faf8f5] p-0.5 border-2 border-[#121316] shadow-[1px_1px_0px_#121316] overflow-hidden shrink-0 flex items-center justify-center">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-lg">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-[#121316] text-sm leading-snug tracking-tight truncate" title="{{ $user->name }}">
                                {{ $user->name }}
                            </h3>
                            <p class="text-[11px] text-[#555] font-semibold truncate mt-0.5" title="{{ $school }}">
                                {{ $school }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5 pt-2 border-t-2 border-[#121316]/10 font-mono-code">
                        <div class="flex justify-between items-center text-xs font-black text-[#121316]">
                            <span>{{ $rank }}</span>
                            <span class="text-[#ff3823]">{{ $pts }} / {{ $next }} Pts</span>
                        </div>
                        <div class="w-full bg-[#faf8f5] border-2 border-[#121316] h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-[#ff3823] h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
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
                            <i class="ph-bold ph-trophy text-[#ff3823] text-base"></i> Leaderboard
                        </h3>
                        <a href="{{ route('reputation.leaderboard') }}" class="text-[10px] font-mono-code font-black text-[#2563eb] hover:underline uppercase">SEMUA &rarr;</a>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($topStudents as $i => $s)
                            <div class="flex items-center gap-2.5">
                                <div class="w-5 h-5 rounded-md border border-[#121316] flex items-center justify-center text-[10px] font-mono-code font-black {{ $i === 0 ? 'bg-[#fde047] text-[#121316]' : ($i === 1 ? 'bg-slate-200 text-[#121316]' : ($i === 2 ? 'bg-amber-100 text-[#121316]' : 'bg-[#faf8f5] text-[#555]')) }}">
                                    {{ $i+1 }}
                                </div>
                                <img src="{{ $s->user->avatar_url }}" class="w-6 h-6 rounded-full border border-[#121316] object-cover">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-[#121316] truncate">{{ $s->user->name }}</div>
                                </div>
                                <div class="text-xs font-mono-code font-black text-[#ff3823]">{{ $s->total_points }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Collab Widget -->
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-4 shadow-[3px_3px_0px_#121316]">
                    <h3 class="forum-hdr text-xs font-mono-code font-black text-[#121316] flex items-center gap-2 mb-3.5 border-b-2 border-[#121316]/10 pb-2 uppercase tracking-wider">
                        <i class="ph-bold ph-handshake text-[#2563eb] text-base"></i> Cari Tim Collab
                    </h3>
                    <div class="space-y-2.5">
                        @forelse($activeCollabs as $c)
                            <a href="{{ route('forum.show', $c) }}" class="block p-3 bg-[#faf8f5] hover:bg-[#fef08a] rounded-xl border-2 border-[#121316] shadow-[1px_1px_0px_#121316] transition group">
                                <div class="text-[9px] font-mono-code text-[#2563eb] font-black uppercase mb-1">{{ $c->category_label }}</div>
                                <div class="text-xs font-black text-[#121316] line-clamp-2 mb-1.5 leading-snug">{{ $c->title }}</div>
                                <div class="flex justify-between items-center text-[10px] font-mono-code font-bold text-[#555]">
                                    <span class="truncate pr-2">{{ $c->user->name }}</span>
                                    <span class="flex-shrink-0 px-1.5 py-0.5 bg-white border border-[#121316] rounded text-[#121316]">{{ $c->approvedMembers()->count() }} Tim</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-[11px] font-bold text-[#777] italic text-center py-2">Belum ada kolaborasi aktif.</div>
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
                        checkEl.innerHTML = '<i class="ph-bold ph-check"></i>';
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
    <div class="bg-white border-2 border-[#121316] rounded-2xl w-full max-w-md overflow-hidden shadow-[8px_8px_0px_#121316]">
        <div class="p-4 sm:p-5 border-b-2 border-[#121316] flex justify-between items-center bg-[#fde047]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#ff3823] border border-[#121316] text-white flex items-center justify-center font-bold">
                    <i class="ph-bold ph-device-mobile text-xl"></i>
                </div>
                <div>
                    <h3 class="forum-hdr text-base font-black text-[#121316] leading-tight">Install Aplikasi PembdaHUB</h3>
                    <div class="text-xs font-mono-code text-[#121316] font-bold">Aplikasi Mobile Resmi PembdaHUB</div>
                </div>
            </div>
            <button onclick="document.getElementById('pwaGuideModal').style.display = 'none'" class="w-8 h-8 rounded-lg bg-white border border-[#121316] text-[#121316] hover:bg-rose-50 flex items-center justify-center font-bold">
                <i class="ph-bold ph-x text-base"></i>
            </button>
        </div>
        <div class="p-6 space-y-4 text-sm text-[#121316]">
            <div class="p-3 bg-[#faf8f5] border-2 border-[#121316] rounded-xl flex items-start gap-3 shadow-[2px_2px_0px_#121316]">
                <i class="ph-bold ph-info text-[#2563eb] text-xl flex-shrink-0 mt-0.5"></i>
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

function pembdaTower() {
    return {
        stats: { total_bricks: 0, total_height: 0 },
        bricks: [],
        pyramidRows: [],
        hasPlacedToday: false,
        isCollapsed: false,
        showBuildModal: false,
        selectedBrick: null,
        statusMessage: '',
        statusType: 'info',
        
        // Form data
        formMessage: '',
        formColor: 'indigo',
        isSubmitting: false,

        async init() {
            const savedState = localStorage.getItem('pembdaTowerCollapsed');
            if (savedState !== null) this.isCollapsed = savedState === 'true';
            
            await this.fetchState();
            setInterval(() => {
                if (!this.isCollapsed) this.fetchState();
            }, 6000);
        },

        setStatus(msg, type = 'info') {
            this.statusMessage = msg;
            this.statusType = type;
            if (type === 'success' || type === 'error') {
                setTimeout(() => {
                    if (this.statusMessage === msg) this.statusMessage = '';
                }, 6000);
            }
        },

        async fetchState() {
            try {
                const res = await fetch('{{ route("forum.tower.state") }}?_t=' + Date.now());
                const data = await res.json();
                console.log('[Menara] API response:', data);
                if (data.success) {
                    this.stats = data.stats;
                    let rawBricks = data.bricks || [];
                    if (!Array.isArray(rawBricks)) {
                        rawBricks = Object.values(rawBricks);
                    }
                    this.bricks = JSON.parse(JSON.stringify(rawBricks));
                    
                    // Bangun piramida
                    let rows = [];
                    let capacities = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // 10 tingkat (total 55)
                    let currentBrickIdx = 0;
                    for (let i = 9; i >= 0; i--) { // mulai isi dari bawah (kapasitas 10)
                        let rowCap = capacities[i];
                        let rowBricks = [];
                        for (let c = 0; c < rowCap; c++) {
                            if (currentBrickIdx < this.bricks.length) {
                                rowBricks.push(this.bricks[currentBrickIdx]);
                                currentBrickIdx++;
                            } else {
                                rowBricks.push(null);
                            }
                        }
                        rows.push(rowBricks);
                    }
                    rows.reverse(); // Balik array agar render dari tingkat atas ke bawah
                    this.pyramidRows = rows;

                    this.hasPlacedToday = !!data.has_placed_today;
                } else if (data.error) {
                    console.error('[Menara] Server error:', data.error);
                }
            } catch(e) {
                console.error('[Menara] Fetch error:', e);
            }
        },

        toggleCollapse() {
            this.isCollapsed = !this.isCollapsed;
            localStorage.setItem('pembdaTowerCollapsed', this.isCollapsed);
            if (!this.isCollapsed) this.fetchState();
        },

        async submitBrick() {
            if (!this.formMessage.trim()) {
                alert("Silakan tulis pesan motivasi singkat Anda.");
                return;
            }

            this.isSubmitting = true;
            try {
                const res = await fetch('{{ route("forum.tower.place") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        message: this.formMessage,
                        color: this.formColor
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.formMessage = '';
                    this.showBuildModal = false;
                    this.setStatus(data.message, 'success');
                    await this.fetchState();
                } else {
                    alert(data.message);
                }
            } catch(e) {
                alert("Terjadi kesalahan koneksi saat memasang bata.");
            } finally {
                this.isSubmitting = false;
            }
        },

        async toggleLike(brick) {
            try {
                const res = await fetch('/forum/tower/like/' + brick.id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': getCsrfToken()
                    }
                });
                const data = await res.json();
                if (data.success) {
                    brick.is_liked = data.liked;
                    brick.likes_count = data.likes_count;
                }
            } catch(e) {}
        }
    }
}
</script>

<!-- Mobile Floating Action Button (Buat Post) -->
<a href="{{ route('forum.create') }}" 
   class="md:hidden fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#ff3823] text-white border-2 border-[#121316] rounded-2xl flex items-center justify-center shadow-[4px_4px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-[2px_2px_0px_#121316] transition-all"
   title="Buat Status Baru">
    <i class="ph-bold ph-plus text-2xl"></i>
</a>
@endsection