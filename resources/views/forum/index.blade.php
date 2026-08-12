@extends(auth()->user()->layout)

@section('title', 'Pembda Space')

@section('content')
<!-- Dynamic Google Fonts & Phosphor Icons -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;650;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
{{-- Alpine.js sudah dimuat oleh layout parent (layouts/app.blade.php) — JANGAN duplikat di sini --}}

<!-- PWA Manifest & App Shell Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#6366f1">
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
    .forum-hdr { font-family: 'Space Grotesk', sans-serif; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* ── LIGHT THEME COLORS ── */
    .bg-forum-base   { background-color: #f8fafc !important; }
    .bg-forum-panel  { background-color: #ffffff !important; }
    .bg-forum-card   { background-color: #ffffff !important; }
    .bg-forum-card-80 { background-color: #ffffff !important; }
    .bg-forum-card-90 { background-color: #ffffff !important; }
    .text-forum-title { color: #000000 !important; }
    .text-forum-body  { color: #0f172a !important; }
    .text-forum-muted { color: #0f172a !important; }
    .border-forum       { border-color: #cbd5e1 !important; }
    .border-forum-light { border-color: #cbd5e1 !important; }
    .bg-forum-light-5  { background-color: #ffffff !important; }
    .bg-forum-light-10 { background-color: #f8fafc !important; }

    /* Thread cards */
    .forum-thread-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .forum-thread-card:hover {
        border-color: #fecdd3;
        box-shadow: 0 4px 20px rgba(225,29,72,0.08);
        transform: translateY(-1px);
    }

    /* Search bar */
    .forum-search {
        background: #f8fafc !important;
        border: 1.5px solid #e2e8f0 !important;
        color: #1e293b !important;
    }
    .forum-search:focus {
        border-color: #e11d48 !important;
        background: #fff !important;
        box-shadow: 0 0 0 3px rgba(225,29,72,0.1) !important;
    }
    .forum-search::placeholder { color: #94a3b8 !important; }

    /* Sidebar active channel */
    .channel-active {
        background: linear-gradient(135deg, #fef2f2, #fff1f2) !important;
        color: #dc2626 !important;
        font-weight: 700 !important;
    }
    .channel-hover:hover {
        background: #f8fafc !important;
        color: #334155 !important;
    }

    /* Sticky topbar */
    .forum-topbar {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .pixelated-canvas {
        image-rendering: pixelated;
        image-rendering: -moz-crisp-edges;
        image-rendering: crisp-edges;
    }
    .pixel-grid {
        background-image:
            linear-gradient(to right, rgba(99,102,241,0.08) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(99,102,241,0.08) 1px, transparent 1px);
        background-size: calc(100% / 100) calc(100% / 100);
        pointer-events: none;
    }
</style>

<!-- App Window Wrapper (Embedded in Global Layout) -->
<div class="w-full bg-forum-base text-forum-title font-['Inter'] rounded-3xl overflow-hidden border border-forum" style="min-height: 85vh; box-shadow: 0 4px 32px rgba(0,0,0,0.07);">
    
    <div class="flex flex-col md:flex-row h-full w-full" x-data="{ mobileSidebarOpen: false }">
        
        <!-- Mobile Header & Toggle -->
        <div class="md:hidden flex items-center justify-between bg-white p-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-red-600 via-red-500 to-red-700 shadow-md shadow-red-600/30 flex items-center justify-center flex-shrink-0">
                    <i class="ph-bold ph-flag-banner text-white text-xl"></i>
                </div>
                <div class="flex flex-col justify-center">
                    <h1 class="forum-hdr text-xl font-bold text-slate-900 tracking-tight leading-tight m-0 p-0">Pembda Space</h1>
                    <span class="text-[10px] text-red-600 font-black uppercase tracking-widest leading-none -mt-0.5">EDISI KEMERDEKAAN 🇮🇩</span>
                </div>
            </div>
            <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="p-2 bg-slate-100 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition">
                <i class="ph-bold ph-list text-2xl"></i>
            </button>
        </div>

        <!-- CHANNEL SIDEBAR (Left) -->
        <div :class="mobileSidebarOpen ? 'block' : 'hidden'" class="md:block w-full md:w-64 lg:w-72 bg-white border-r border-slate-100 flex-shrink-0 flex flex-col transition-all duration-300 relative z-20">
            
            <div class="p-5 h-full flex flex-col gap-6 max-h-[85vh] overflow-y-auto no-scrollbar">
                <!-- Logo (Desktop) -->
                <div class="hidden md:flex items-center gap-3 px-2 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-600 via-red-500 to-red-700 shadow-md shadow-red-600/30 flex items-center justify-center flex-shrink-0">
                        <i class="ph-bold ph-flag-banner text-white text-2xl"></i>
                    </div>
                    <div class="flex flex-col justify-center min-w-0">
                        <h1 class="forum-hdr text-xl font-bold text-slate-900 tracking-tight leading-tight m-0 p-0">Pembda Space</h1>
                        <span class="text-[10px] text-red-600 font-black uppercase tracking-widest leading-none -mt-0.5">EDISI KEMERDEKAAN 🇮🇩</span>
                    </div>
                </div>

                <nav class="flex-1 space-y-6">
                    <!-- All Channels -->
                    <div class="space-y-1">
                        <a href="{{ route('forum.index', array_filter(['search' => $search])) }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 {{ !$category ? 'channel-active' : 'text-slate-900 font-semibold channel-hover' }}">
                            <div class="flex items-center gap-3">
                                <i class="ph-bold ph-compass text-lg {{ !$category ? 'text-red-600' : '' }}"></i>
                                <span class="text-sm font-bold">Semua Saluran</span>
                            </div>
                        </a>
                    </div>

                    @foreach($channelGroups as $groupName => $channels)
                    <div class="space-y-1.5" x-data="{ expanded: true }">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between px-2 py-1 text-xs font-bold text-slate-900 hover:text-black transition uppercase tracking-wider group">
                            <span>{{ $groupName }}</span>
                            <i class="ph-bold ph-caret-down transition-transform duration-200" :class="expanded ? '' : '-rotate-90'"></i>
                        </button>
                        <div x-show="expanded" class="space-y-0.5">
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
                                   class="flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? 'channel-active border-l-2 border-indigo-500 font-bold' : 'text-slate-900 font-medium channel-hover border-l-2 border-transparent' }}">
                                    <div class="flex items-center gap-3">
                                        <span>{{ $emoji }}</span>
                                        <span class="text-sm truncate">{{ $cleanLabel }}</span>
                                    </div>
                                    @if($count > 0)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isActive ? 'bg-red-100 text-red-700' : 'bg-slate-200 text-slate-900' }}">{{ $count }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </nav>

                <!-- Stats footer -->
                <div class="mt-auto pt-4 border-t border-slate-200 pb-2">
                    <div class="flex justify-between items-center px-2 text-xs font-bold text-slate-900">
                        <div class="flex items-center gap-1.5" title="Online dalam 15 menit terakhir">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $onlineCount }} Online</span>
                        </div>
                        <div>{{ $totalThreads }} Topik</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN FEED (Center) -->
        <div class="flex-1 flex flex-col min-w-[300px] w-full bg-forum-base p-4 sm:p-6 lg:p-8 max-h-[85vh] overflow-y-auto" style="background-image: radial-gradient(rgba(220,38,38,0.03) 2px, transparent 2px); background-size: 24px 24px;">
            
            <!-- HERO BANNER KEMERDEKAAN -->
            <div class="mb-6 relative rounded-3xl overflow-hidden shadow-xl border border-red-200">
                <!-- Red & White Gradient Background -->
                <div class="absolute inset-0 bg-gradient-to-r from-red-600 via-red-500 to-red-600 opacity-95"></div>
                <!-- Subtle pattern -->
                <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 2px, transparent 2px, transparent 10px);"></div>
                <!-- Sunburst Effect (CSS purely) -->
                <div class="absolute -top-40 -left-40 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl mix-blend-overlay"></div>
                <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-white opacity-20 rounded-full blur-3xl mix-blend-overlay"></div>
                
                <div class="relative p-6 sm:p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex-1 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-white text-xs font-black tracking-widest mb-4 border border-white/30 shadow-sm">
                            <i class="ph-bold ph-flag"></i> HUT KEMRI KE-81
                        </div>
                        <h2 class="text-4xl md:text-5xl font-black text-white mb-3 tracking-tight leading-tight" style="font-family: 'Space Grotesk', sans-serif; text-shadow: 0 4px 12px rgba(220,38,38,0.4);">
                            Dirgahayu <span class="text-white italic relative inline-block"><span class="relative z-10">Indonesia!</span><span class="absolute bottom-1 left-0 w-full h-3 bg-red-800/50 -z-0 rounded-full"></span></span>
                        </h2>
                        <p class="text-red-50 text-sm md:text-base max-w-xl font-medium leading-relaxed">
                            Mari kobarkan semangat belajar dan gotong royong di Perguruan Pembda untuk menyongsong Indonesia yang lebih maju. Berkarya untuk negeri! 🇮🇩
                        </p>
                    </div>
                    
                    <!-- Decorative Element 81 Years -->
                    <div class="hidden md:flex flex-shrink-0 relative group">
                        <div class="w-36 h-36 rounded-full border-8 border-white flex items-center justify-center bg-red-600 shadow-2xl group-hover:scale-105 transition-transform duration-500">
                            <span class="text-7xl font-black text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">81</span>
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg border border-red-100 rotate-12 group-hover:rotate-0 transition-transform duration-500">
                            <span class="text-2xl font-black text-red-600">TH</span>
                        </div>
                        
                        <!-- Floating ribbons -->
                        <div class="absolute -top-4 -left-4 text-white text-4xl animate-pulse">🎊</div>
                        <div class="absolute -top-2 -right-8 text-white text-3xl animate-bounce" style="animation-delay: 500ms;">🎈</div>
                    </div>
                </div>
            </div>

            <!-- Header & Search -->
            <div class="forum-topbar p-4 mb-6 sticky top-0 z-30 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <form method="GET" action="{{ route('forum.index') }}" class="w-full sm:max-w-md relative">
                    @if($category) <input type="hidden" name="category" value="{{ $category }}"> @endif
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="ph-bold ph-magnifying-glass"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" 
                           class="forum-search w-full pl-11 pr-4 py-2.5 rounded-xl text-sm transition outline-none" 
                           placeholder="Cari obrolan, proyek, atau karya...">
                </form>
                
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    @if($search || $category)
                        <a href="{{ route('forum.index') }}" class="px-4 py-2 bg-rose-50 text-rose-500 hover:bg-rose-100 border border-rose-200 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                            <i class="ph-bold ph-x-circle"></i> Reset Filter
                        </a>
                    @endif
                    <button onclick="triggerPwaInstall()" 
                            class="flex px-4 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-md shadow-emerald-500/20 hover:scale-105 transition-all duration-200 items-center gap-2 whitespace-nowrap">
                        <i class="ph-bold ph-cellphone-charging text-lg text-white"></i> <span class="text-white font-extrabold">Install APK</span>
                    </button>
                    <a href="{{ route('forum.create') }}" 
                       class="flex px-6 py-2.5 rounded-full bg-gradient-to-r from-red-600 via-red-500 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-extrabold text-sm shadow-lg shadow-red-500/30 hover:shadow-xl hover:shadow-red-600/40 hover:scale-105 transition-all duration-200 items-center gap-2 whitespace-nowrap">
                        <i class="ph-bold ph-plus text-base text-white"></i> <span class="text-white font-extrabold">Buat Post</span>
                    </a>
                </div>
            </div>



            <!-- Pembda Tower (Menara Prestasi) Widget -->
            <div class="mb-6 bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm" x-data="pembdaTower()">
                <!-- Header Bar -->
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-red-500/10 via-rose-50/50 to-white cursor-pointer hover:bg-red-50/50 transition" @click="toggleCollapse()">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 via-red-500 to-rose-600 flex items-center justify-center shadow-md shadow-red-200 text-white font-black text-xl">
                            🧱
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="forum-hdr text-lg font-bold text-slate-800 tracking-tight leading-tight">Menara Prestasi Pembda</h2>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-red-100 text-red-800 border border-red-200 flex items-center gap-1">
                                    <span>Tinggi:</span> <span x-text="stats.total_height + ' Lantai'"></span>
                                </span>
                            </div>
                            <span class="text-xs text-slate-500 hidden sm:inline">Bersama membangun menara motivasi & cita-cita Perguruan Pembda. 1 Bata / Hari.</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2" @click.stop>
                        <button @click="showBuildModal = true" 
                                class="text-xs font-extrabold px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-md shadow-red-200 hover:shadow-lg transition flex items-center gap-2 active:scale-95 whitespace-nowrap">
                            <i class="ph-bold ph-plus-circle text-base"></i>
                            <span>Pasang Bata Saya</span>
                        </button>
                        <button @click="toggleCollapse()" class="text-slate-400 hover:text-slate-700 transition p-1.5 bg-slate-100 rounded-lg">
                            <i class="ph-bold ph-caret-down transition-transform duration-300" :class="isCollapsed ? '' : 'rotate-180'"></i>
                        </button>
                    </div>
                </div>

                <!-- Body / Tower Wall -->
                <div x-show="!isCollapsed" x-transition.opacity.duration.300ms class="p-4 bg-gradient-to-b from-sky-100 via-sky-50 to-amber-50/50">
                    <!-- Status Banner -->
                    <div x-show="statusMessage" x-transition.opacity
                         class="mb-4 p-3 rounded-xl text-sm font-semibold flex items-center justify-between shadow-sm bg-emerald-50 border border-emerald-200 text-emerald-800">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-check-circle text-emerald-600 text-lg"></i>
                            <span x-text="statusMessage"></span>
                        </div>
                        <button @click="statusMessage = ''" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
                    </div>

                    <!-- Daily Status Banner -->
                    <div x-show="hasPlacedToday" class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs font-bold text-indigo-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-seal-check text-indigo-600 text-lg"></i>
                            <span>Bata motivasi Anda telah terpasang hari ini di Menara Prestasi! (+10 Poin Tambahan)</span>
                        </div>
                        <span class="text-[10px] bg-indigo-200/80 text-indigo-900 px-2 py-0.5 rounded-full font-extrabold">Jatah Hari Ini Selesai</span>
                    </div>

                    <!-- Pesan Terakhir -->
                    <template x-if="bricks.length > 0">
                        <div class="mb-6 mx-auto w-full max-w-3xl bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center relative overflow-hidden">
                            <!-- Background decoration -->
                            <div class="absolute top-0 right-0 -mt-2 -mr-2 text-indigo-100 opacity-50">
                                <i class="ph-fill ph-quotes text-6xl"></i>
                            </div>
                            <p class="text-[10px] text-indigo-500 font-black mb-1.5 uppercase tracking-widest relative z-10">🌟 Motivasi Terakhir Dipasang</p>
                            <p class="text-sm sm:text-base font-medium italic text-slate-800 relative z-10" x-text="`&quot;${bricks[bricks.length - 1].message}&quot;`"></p>
                            <p class="text-[11px] text-slate-500 mt-2 font-semibold relative z-10" x-text="`- ${bricks[bricks.length - 1].user_name} (${bricks[bricks.length - 1].school_name})`"></p>
                        </div>
                    </template>

                    <!-- ============ TOWER VISUAL (Piramida — lebar bawah, mengecil ke atas) ============ -->
                    <div class="flex flex-col items-center py-2">

                        <!-- 🔺 Tower Spire / Puncak Segitiga (Edisi Kemerdekaan) -->
                        <div class="flex flex-col items-center w-full mt-2">
                            <!-- Bendera Indonesia CSS Murni -->
                            <div class="flex flex-col border border-slate-400 shadow-sm animate-bounce hover:scale-125 transition-all cursor-pointer mb-1 z-10" style="width: 36px; height: 24px;" title="Dirgahayu Kemerdekaan RI!">
                                <div style="height: 50%; width: 100%; background-color: #dc2626;"></div>
                                <div style="height: 50%; width: 100%; background-color: #ffffff;"></div>
                            </div>
                            <!-- Puncak -->
                            <div class="w-0 h-0 border-l-[30px] border-r-[30px] border-b-[30px] border-l-transparent border-r-transparent border-b-red-600 drop-shadow-md"></div>
                            <div class="bg-white border-b-2 border-red-600 text-red-600 text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-center py-1.5 shadow-md w-full max-w-[250px] rounded-b">
                                ⋆ PUNCAK KEMERDEKAAN ⋆
                            </div>
                        </div>

                        <!-- 🧱 Tower Wall — Bata tersusun piramida (bawah lebar, atas sempit) -->
                        <div class="w-full flex flex-col items-center" style="max-height: 420px; overflow-y: auto;">
                            <!-- flex-col: susun baris dari atas ke bawah -->
                            <div class="w-full max-w-[550px] flex flex-col items-center gap-[2px] sm:gap-[3px] py-4 mx-auto">
                                <template x-for="(row, rowIdx) in pyramidRows" :key="rowIdx">
                                    <div class="flex justify-center gap-[2px] sm:gap-[3px] mx-auto" :style="'width: ' + (row.length * 10) + '%;'">
                                        <template x-for="(b, bIdx) in row" :key="bIdx">
                                            <div class="flex-1">
                                                <!-- Bata Terisi -->
                                                <template x-if="b">
                                                    <div @click="selectedBrick = b"
                                                         class="w-full h-6 sm:h-8 md:h-10 lg:h-12 cursor-pointer hover:-translate-y-1 hover:scale-110 hover:z-10 transition-all shadow-md border border-white/30 rounded-[3px] relative group flex items-center justify-center"
                                                         :class="{
                                                             'bg-gradient-to-br from-indigo-500 to-indigo-700': b.color === 'indigo',
                                                             'bg-gradient-to-br from-emerald-500 to-teal-700': b.color === 'emerald',
                                                             'bg-gradient-to-br from-amber-400 to-orange-600': b.color === 'amber',
                                                             'bg-gradient-to-br from-rose-500 to-pink-700': b.color === 'rose',
                                                             'bg-gradient-to-br from-purple-500 to-violet-700': b.color === 'purple',
                                                             'bg-gradient-to-br from-cyan-500 to-blue-700': b.color === 'cyan'
                                                         }"
                                                         title="Klik untuk melihat pesan">
                                                        <span class="text-[7px] sm:text-[9px] font-black text-white/60 select-none" x-text="b.brick_number"></span>
                                                    </div>
                                                </template>
                                                <!-- Bata Kosong -->
                                                <template x-if="!b">
                                                    <div class="w-full h-6 sm:h-8 md:h-10 lg:h-12 border border-dashed border-slate-300 bg-white/40 rounded-[3px]"></div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 🏗️ Tower Foundation / Fondasi -->
                        <div class="bg-gradient-to-b from-amber-700 via-amber-800 to-amber-950 text-amber-200 text-[10px] font-black uppercase tracking-[0.12em] text-center py-2.5 px-6 rounded-b-xl shadow-lg border-t-4 border-amber-600" style="width: 100%;">
                            🏗️ Fondasi Menara · <span x-text="stats.total_bricks"></span> Bata Terpasang · Tinggi <span x-text="stats.total_height"></span> Lantai
                        </div>

                    </div>
                </div>

                <!-- Build Brick Modal -->
                <div x-show="showBuildModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
                    <div @click.away="showBuildModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
                        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-amber-50 to-orange-50">
                            <h3 class="forum-hdr text-base font-bold text-slate-800 flex items-center gap-2">
                                <span>🧱</span>
                                <span>Pasang Bata Motivasi Saya</span>
                            </h3>
                            <button @click="showBuildModal = false" class="text-slate-400 hover:text-slate-700 p-1"><i class="ph-bold ph-x text-lg"></i></button>
                        </div>

                        <div class="p-5 space-y-4 text-sm text-slate-700">
                            <div x-show="hasPlacedToday" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs font-bold text-amber-900 text-center">
                                ⚠️ Anda sudah meletakkan bata hari ini. Setiap pengguna memiliki 1 jatah bata per hari.
                            </div>

                            <div :class="hasPlacedToday ? 'opacity-50 pointer-events-none' : ''">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Pesan Motivasi & Harapan (Maks 140 Karakter):</label>
                                <textarea x-model="formMessage" 
                                          maxlength="140" 
                                          rows="3" 
                                          placeholder="Contoh: Semangat belajar untuk angkatan 2026! Perguruan Pembda Nias Jaya selalu."
                                          class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition resize-none"></textarea>
                                <div class="text-right text-[10px] text-slate-400 mt-1" x-text="formMessage.length + ' / 140 karakter'"></div>
                            </div>

                            <div :class="hasPlacedToday ? 'opacity-50 pointer-events-none' : ''">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Warna Bata Anda:</label>
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                    <button @click="formColor = 'indigo'" type="button" class="py-2 px-1 rounded-lg font-bold text-[11px] transition text-white bg-indigo-600 border border-indigo-700 flex items-center justify-center gap-1" :class="formColor === 'indigo' ? 'ring-2 ring-indigo-900 shadow-md font-black' : 'opacity-80 hover:opacity-100'">
                                        <i x-show="formColor === 'indigo'" class="ph-bold ph-check"></i> Nila
                                    </button>
                                    <button @click="formColor = 'emerald'" type="button" class="py-2 px-1 rounded-lg font-bold text-[11px] transition text-white bg-emerald-600 border border-emerald-700 flex items-center justify-center gap-1" :class="formColor === 'emerald' ? 'ring-2 ring-emerald-900 shadow-md font-black' : 'opacity-80 hover:opacity-100'">
                                        <i x-show="formColor === 'emerald'" class="ph-bold ph-check"></i> Hijau
                                    </button>
                                    <button @click="formColor = 'amber'" type="button" class="py-2 px-1 rounded-lg font-bold text-[11px] transition text-white bg-amber-500 border border-amber-600 flex items-center justify-center gap-1" :class="formColor === 'amber' ? 'ring-2 ring-amber-900 shadow-md font-black' : 'opacity-80 hover:opacity-100'">
                                        <i x-show="formColor === 'amber'" class="ph-bold ph-check"></i> Emas
                                    </button>
                                    <button @click="formColor = 'rose'" type="button" class="py-2 px-1 rounded-lg font-bold text-[11px] transition text-white bg-rose-600 border border-rose-700 flex items-center justify-center gap-1" :class="formColor === 'rose' ? 'ring-2 ring-rose-900 shadow-md font-black' : 'opacity-80 hover:opacity-100'">
                                        <i x-show="formColor === 'rose'" class="ph-bold ph-check"></i> Merah
                                    </button>
                                    <button @click="formColor = 'purple'" type="button" class="py-2 px-1 rounded-lg font-bold text-[11px] transition text-white bg-purple-600 border border-purple-700 flex items-center justify-center gap-1" :class="formColor === 'purple' ? 'ring-2 ring-purple-900 shadow-md font-black' : 'opacity-80 hover:opacity-100'">
                                        <i x-show="formColor === 'purple'" class="ph-bold ph-check"></i> Ungu
                                    </button>
                                    <button @click="formColor = 'cyan'" type="button" class="py-2 px-1 rounded-lg font-bold text-[11px] transition text-white bg-cyan-600 border border-cyan-700 flex items-center justify-center gap-1" :class="formColor === 'cyan' ? 'ring-2 ring-cyan-900 shadow-md font-black' : 'opacity-80 hover:opacity-100'">
                                        <i x-show="formColor === 'cyan'" class="ph-bold ph-check"></i> Biru
                                    </button>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                                <button @click="showBuildModal = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Batal</button>
                                <button @click="submitBrick()" 
                                        type="button" 
                                        :disabled="hasPlacedToday || isSubmitting || !formMessage.trim()"
                                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold text-xs shadow-md disabled:opacity-50 transition flex items-center gap-1.5">
                                    <i x-show="isSubmitting" class="ph-bold ph-spinner animate-spin"></i>
                                    <span>🧱 Pasang Bata ke Puncak</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Brick Modal -->
                <div x-show="selectedBrick" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
                    <div @click.away="selectedBrick = null" class="bg-white border border-slate-200 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl">
                        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <span class="text-xs font-extrabold text-slate-500" x-text="selectedBrick ? ('Bata #' + selectedBrick.brick_number) : ''"></span>
                            <button @click="selectedBrick = null" class="text-slate-400 hover:text-slate-700 p-1"><i class="ph-bold ph-x text-lg"></i></button>
                        </div>
                        <template x-if="selectedBrick">
                            <div class="p-5 flex flex-col items-center text-center gap-3">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-md"
                                     :class="{
                                         'bg-indigo-600': selectedBrick.color === 'indigo',
                                         'bg-emerald-600': selectedBrick.color === 'emerald',
                                         'bg-amber-500': selectedBrick.color === 'amber',
                                         'bg-rose-600': selectedBrick.color === 'rose',
                                         'bg-purple-600': selectedBrick.color === 'purple',
                                         'bg-cyan-600': selectedBrick.color === 'cyan'
                                     }">🧱</div>
                                
                                <p class="text-sm font-bold text-slate-800 italic" x-text="`“${selectedBrick.message}”`"></p>

                                <div class="pt-3 border-t border-slate-100 w-full text-xs text-slate-500">
                                    <div>Diletakkan oleh: <strong class="text-slate-800" x-text="selectedBrick.user_name"></strong></div>
                                    <div>Unit: <span class="font-semibold text-indigo-600" x-text="selectedBrick.school_name"></span></div>
                                    <div class="text-[10px] text-slate-400 mt-1" x-text="selectedBrick.time_ago"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- HIGHLIGHT SECTION -->
            @if(isset($latestHighlight) || isset($trendingHighlight))
                <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Postingan Terbaru -->
                    @if(isset($latestHighlight))
                        <a href="{{ route('forum.show', $latestHighlight) }}" class="block bg-gradient-to-br from-red-50 to-white border border-red-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-red-300 transition group relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition transform group-hover:scale-110">
                                <i class="ph-bold ph-sparkle text-6xl text-red-600"></i>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-red-600 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md flex items-center gap-1">
                                    <i class="ph-bold ph-sparkle"></i> Terbaru
                                </span>
                                <span class="text-xs font-semibold text-slate-500">{{ $latestHighlight->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base md:text-lg leading-tight mb-2 group-hover:text-red-600 transition">{{ $latestHighlight->title }}</h3>
                            <div class="flex items-center gap-2 mt-auto pt-2">
                                <img src="{{ $latestHighlight->user->avatar_url }}" class="w-6 h-6 rounded-full border border-red-200">
                                <span class="text-xs font-bold text-slate-700">{{ $latestHighlight->user->name }}</span>
                            </div>
                        </a>
                    @endif

                    <!-- Postingan Ter-rame (Trending) -->
                    @if(isset($trendingHighlight))
                        <a href="{{ route('forum.show', $trendingHighlight) }}" class="block bg-gradient-to-br from-rose-50 to-white border border-rose-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-rose-300 transition group relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition transform group-hover:scale-110">
                                <i class="ph-bold ph-fire text-6xl text-rose-600"></i>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-rose-600 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md flex items-center gap-1">
                                    <i class="ph-bold ph-fire"></i> Sedang Hangat
                                </span>
                                <span class="text-xs font-bold text-rose-600 bg-rose-100 px-2 py-0.5 rounded">{{ $trendingHighlight->replies_count + $trendingHighlight->likes_count }} Interaksi</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base md:text-lg leading-tight mb-2 group-hover:text-rose-600 transition">{{ $trendingHighlight->title }}</h3>
                            <div class="flex items-center gap-2 mt-auto pt-2">
                                <img src="{{ $trendingHighlight->user->avatar_url }}" class="w-6 h-6 rounded-full border border-rose-200">
                                <span class="text-xs font-bold text-slate-700">{{ $trendingHighlight->user->name }}</span>
                            </div>
                        </a>
                    @endif
                </div>
            @endif

            <!-- Feed List -->
            <div class="space-y-4 pb-10">
                @forelse($threads as $thread)
                    @php
                        $author = $thread->user;
                        $isLiked = $thread->isLikedBy(auth()->user());
                        $catLabel = $thread->category_label;
                        
                        $catColor = match($thread->category) {
                            'diskusi' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
                            'sharing' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                            'info' => 'bg-amber-50 text-amber-600 border-amber-200',
                            'performance' => 'bg-purple-50 text-purple-600 border-purple-200',
                            'art_gallery' => 'bg-pink-50 text-pink-600 border-pink-200',
                            'talent' => 'bg-violet-50 text-violet-600 border-violet-200',
                            'gaming' => 'bg-rose-50 text-rose-600 border-rose-200',
                            'tanya_jawab' => 'bg-cyan-50 text-cyan-600 border-cyan-200',
                            'trending' => 'bg-orange-50 text-orange-600 border-orange-200',
                            'project_idea' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'committee' => 'bg-teal-50 text-teal-600 border-teal-200',
                            'charity' => 'bg-red-50 text-red-600 border-red-200',
                            default => 'bg-slate-50 text-slate-500 border-slate-200'
                        };
                    @endphp
                    
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-200 transition group {{ $thread->is_pinned ? 'ring-1 ring-amber-400' : '' }}">
                        <a href="{{ route('forum.show', $thread) }}" class="block">
                            <!-- Author & Meta -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $author->avatar_url }}" 
                                         class="w-10 h-10 rounded-full border-2 border-slate-100 shadow-sm object-cover">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-sm">{{ $author->name }}</span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 bg-slate-200 text-slate-900 rounded uppercase tracking-wider">{{ $author->role }}</span>
                                            <span class="text-xs text-slate-900 font-medium">&bull; {{ $thread->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <span class="text-[10px] px-2 py-0.5 rounded-md border font-bold tracking-wider {{ $catColor }}">
                                                {{ $catLabel }}
                                            </span>
                                            @if($thread->category === 'tanya_jawab')
                                                @if($thread->hasAcceptedReply())
                                                    <span class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-md font-bold flex items-center gap-1">
                                                        <i class="ph-bold ph-check-circle"></i> Terjawab
                                                    </span>
                                                @else
                                                    <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300 rounded-md font-bold flex items-center gap-1">
                                                        <i class="ph-bold ph-question"></i> Bantu Jawab
                                                    </span>
                                                @endif
                                            @endif
                                            @if($thread->category === 'gaming' && $thread->game_name)
                                                <span class="text-[10px] px-2 py-0.5 bg-rose-100 text-rose-800 border border-rose-200 rounded-md font-bold flex items-center gap-1">
                                                    <i class="ph-bold ph-game-controller"></i> {{ $thread->game_name }}
                                                </span>
                                            @endif
                                            @if($thread->category === 'sharing' && $thread->file_category)
                                                <span class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-md font-bold flex items-center gap-1">
                                                    <i class="ph-bold ph-folder-open"></i> {{ $thread->file_category }}
                                                </span>
                                            @endif
                                            @if($thread->is_pinned)
                                                <span class="text-[10px] px-2 py-0.5 bg-amber-500/20 text-amber-900 border border-amber-500/40 rounded-md font-bold flex items-center gap-1">
                                                    <i class="ph-bold ph-push-pin"></i> Tersemat
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions (Author / Admin) -->
                                @if(auth()->id() === $thread->user_id || auth()->user()->isSuperAdmin())
                                    <div class="flex items-center gap-1 flex-shrink-0" onclick="event.stopPropagation()">
                                        <a href="{{ route('forum.edit', $thread) }}" class="p-2 text-slate-700 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition" title="Edit Status">
                                            <i class="ph-bold ph-pencil-simple text-lg"></i>
                                        </a>
                                        <form action="{{ route('forum.destroy', $thread) }}" method="POST" onsubmit="return confirm('Yakin hapus postingan ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-700 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus Status">
                                                <i class="ph-bold ph-trash text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="pl-13 space-y-3">
                                <h3 class="forum-hdr text-lg md:text-xl font-bold text-slate-900 group-hover:text-indigo-600 transition-colors leading-snug">
                                    {{ $thread->title }}
                                </h3>
                                <p class="text-sm text-slate-900 font-normal line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($thread->content), 200) }}
                                </p>

                                @if($thread->image_path)
                                    <div class="mt-3 rounded-xl overflow-hidden border border-slate-200 max-w-sm max-h-48">
                                        <img src="{{ asset('storage/' . $thread->image_path) }}" class="w-full h-full object-cover">
                                    </div>
                                @endif

                                <!-- Bank File Direct Download Box -->
                                @if($thread->category === 'sharing' && $thread->attachment_path)
                                    <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl max-w-md flex items-center justify-between gap-3 shadow-sm">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-800 font-bold text-xs flex-shrink-0 uppercase">
                                                {{ strtoupper($thread->file_extension ?: 'FILE') }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-slate-900 truncate">{{ $thread->attachment_name ?? 'Berkas Lampiran' }}</div>
                                                <div class="text-[10px] text-emerald-800 font-bold">{{ $thread->file_category ?: 'Dokumen Pembelajaran' }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ asset('storage/' . $thread->attachment_path) }}" download onclick="event.stopPropagation()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 flex-shrink-0 shadow-sm">
                                            <i class="ph-bold ph-download-simple"></i> Unduh
                                        </a>
                                    </div>
                                @endif

                                <!-- Gaming Mabar Lobby Card -->
                                @if($thread->category === 'gaming' && ($thread->game_name || $thread->game_room_code))
                                    <div class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl max-w-md flex items-center justify-between gap-3 shadow-sm">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center text-rose-700 text-xl flex-shrink-0">
                                                <i class="ph-bold ph-game-controller"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-rose-900 truncate">{{ $thread->game_name ?: 'Lobi Mabar' }}</div>
                                                <div class="text-[11px] font-bold text-slate-900 tracking-wider truncate">ID/Kode: <span class="text-rose-700 select-all">{{ $thread->game_room_code ?: 'Tanyakan di komentar' }}</span></div>
                                            </div>
                                        </div>
                                        @if($thread->game_room_code)
                                        <button onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText('{{ addslashes($thread->game_room_code) }}'); alert('Kode/ID Game berhasil disalin!');" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 flex-shrink-0 shadow-sm">
                                            <i class="ph-bold ph-copy"></i> Salin ID
                                        </button>
                                        @endif
                                    </div>
                                @endif

                                @if($thread->poll)
                                    <div class="mt-3 p-3 bg-indigo-50 border border-indigo-100 rounded-xl max-w-sm flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center"><i class="ph-bold ph-chart-bar text-indigo-600"></i></div>
                                        <div>
                                            <div class="text-xs text-indigo-700 font-bold">Polling Interaktif</div>
                                            <div class="text-sm text-slate-900 font-bold line-clamp-1">{{ $thread->poll->question }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </a>

                        <!-- Action Bar -->
                        <div class="pl-13 mt-4 flex items-center flex-wrap gap-2 text-xs font-bold text-slate-900">
                            <!-- Upvote/Like -->
                            <button onclick="toggleLike(this, '{{ route('forum.like', $thread) }}')" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border transition-colors {{ $isLiked ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-slate-300 hover:bg-slate-50 text-slate-900' }}">
                                <i class="ph-bold ph-thumbs-up"></i> <span class="likes-count">{{ $thread->likes->count() }}</span>
                            </button>
                            
                            <!-- Replies count -->
                            <a href="{{ route('forum.show', $thread) }}#replies" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 transition-colors text-slate-900">
                                <i class="ph-bold ph-chat-circle"></i> {{ $thread->replies->count() }}
                            </a>

                            <!-- Views -->
                            <div class="flex items-center gap-1.5 px-3 py-1.5 text-slate-900 font-bold">
                                <i class="ph-bold ph-eye"></i> {{ $thread->views_count }}
                            </div>

                            @if(in_array($thread->category, ['project_idea', 'committee', 'charity']))
                                <div class="flex items-center gap-1.5 px-3 py-1.5 ml-auto bg-blue-50 text-blue-600 border border-blue-100 rounded-lg">
                                    <i class="ph-bold ph-users"></i> {{ $thread->approvedMembers()->count() }} Tim
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-slate-200 rounded-3xl p-16 text-center mt-10">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-200">
                            <i class="ph-bold ph-ghost text-slate-400 text-3xl"></i>
                        </div>
                        <h4 class="forum-hdr text-xl font-bold text-slate-600 mb-2">Masih Sepi Nih</h4>
                        <p class="text-sm text-slate-400">Belum ada obrolan di saluran ini. Jadilah yang pertama!</p>
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
        <div class="hidden xl:flex flex-col w-72 flex-shrink-0 bg-white border-l border-slate-100 p-5 gap-5 max-h-[85vh] overflow-y-auto no-scrollbar">
            <!-- User Profile Card -->
            @php
                $user = auth()->user();
                $rep = $user->reputation;
                $pts = $rep->total_points ?? 0;
                $school = $user->school->name ?? 'Yayasan Perguruan Pembda Nias';
                
                $next = 100; $rank = 'Perintis';
                if ($pts >= 500) { $rank = 'Legenda 👑'; $next = 1000; }
                elseif ($pts >= 200) { $rank = 'Kontributor 💎'; $next = 500; }
                elseif ($pts >= 100) { $rank = 'Warga Aktif 🚀'; $next = 200; }
                $pct = min(100, max(5, round(($pts / $next) * 100)));
            @endphp
            <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-500 rounded-3xl p-5 shadow-lg shadow-purple-500/20 text-white relative overflow-hidden">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 border-2 border-white/40 shadow-inner flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-white truncate text-base tracking-tight">{{ $user->name }}</div>
                        <div class="text-[10px] text-white font-bold uppercase tracking-wider truncate">{{ $school }}</div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center mb-2 text-xs font-extrabold text-white">
                    <span>{{ $rank }}</span>
                    <span>{{ $pts }} / {{ $next }} Pts</span>
                </div>
                <div class="w-full bg-white/30 h-2 rounded-full overflow-hidden p-0.5">
                    <div class="bg-white h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                </div>
            </div>

            <!-- Leaderboard Widget -->
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="forum-hdr text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="ph-bold ph-trophy text-amber-500"></i> Leaderboard
                    </h3>
                    <a href="{{ route('reputation.leaderboard') }}" class="text-[10px] text-indigo-700 hover:text-indigo-900 uppercase tracking-wider font-bold">Semua</a>
                </div>
                <div class="space-y-3">
                    @foreach($topStudents as $i => $s)
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-900">{{ $i+1 }}</div>
                            <img src="{{ $s->user->avatar_url }}" class="w-6 h-6 rounded-full border border-slate-100 object-cover">
                            <div class="min-w-0 flex-1">
                                <div class="text-[11px] font-bold text-slate-900 truncate">{{ $s->user->name }}</div>
                            </div>
                            <div class="text-[11px] font-bold text-indigo-700">{{ $s->total_points }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Collab Widget -->
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <h3 class="forum-hdr text-sm font-bold text-slate-900 flex items-center gap-2 mb-4">
                    <i class="ph-bold ph-handshake text-indigo-600"></i> Cari Tim
                </h3>
                <div class="space-y-2.5">
                    @forelse($activeCollabs as $c)
                        <a href="{{ route('forum.show', $c) }}" class="block p-3 bg-slate-50 hover:bg-indigo-50 rounded-xl border border-slate-200 hover:border-indigo-300 transition">
                            <div class="text-[9px] text-indigo-700 font-bold uppercase mb-1">{{ $c->category_label }}</div>
                            <div class="text-xs font-bold text-slate-900 line-clamp-2 mb-1.5">{{ $c->title }}</div>
                            <div class="flex justify-between items-center text-[10px] text-slate-900 font-bold">
                                <span class="truncate pr-2">{{ $c->user->name }}</span>
                                <span class="flex-shrink-0">{{ $c->approvedMembers()->count() }} Tim</span>
                            </div>
                        </a>
                    @empty
                        <div class="text-[11px] text-slate-900 font-bold italic text-center py-2">Belum ada kolaborasi aktif.</div>
                    @endforelse
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
                btn.classList.remove('bg-white', 'border-slate-200', 'hover:bg-slate-50');
                btn.classList.add('bg-indigo-50', 'border-indigo-200', 'text-indigo-600');
            } else {
                btn.classList.remove('bg-indigo-50', 'border-indigo-200', 'text-indigo-600');
                btn.classList.add('bg-white', 'border-slate-200', 'hover:bg-slate-50');
            }
        }
    } catch (error) {
        alert('Like JS Catch Error: ' + error.message);
    } finally {
        btn.disabled = false;
    }
}

function pembdaColabs() {
    return {
        puzzle: null,
        board: [],
        inventory: [],
        hasPlacedToday: false,
        selectedPiece: null,
        showGuide: false,
        showTargetModal: false,
        isCollapsed: false,
        statusMessage: '',
        statusType: 'info',
        
        async init() {
            const savedState = localStorage.getItem('pembdaColabsCollapsed');
            if (savedState !== null) this.isCollapsed = savedState === 'true';
            
            await this.fetchState();
            setInterval(() => {
                if (!this.isCollapsed) this.fetchState();
            }, 5000);
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
                const res = await fetch('{{ route("forum.puzzle.state") }}?_t=' + Date.now());
                const data = await res.json();
                if (data.success) {
                    this.puzzle = data.puzzle;
                    this.board = data.board || [];
                    this.inventory = data.inventory || [];
                    this.hasPlacedToday = !!data.has_placed_today;
                } else if (data.message) {
                    this.setStatus(data.message, 'error');
                }
            } catch(e) {}
        },
        
        toggleCollapse() {
            this.isCollapsed = !this.isCollapsed;
            localStorage.setItem('pembdaColabsCollapsed', this.isCollapsed);
            if (!this.isCollapsed) this.fetchState();
        },
        
        getBgPos(index) {
            if (!this.puzzle) return '0 0';
            const col = index % this.puzzle.grid_x;
            const row = Math.floor(index / this.puzzle.grid_x);
            const x = this.puzzle.grid_x > 1 ? (col / (this.puzzle.grid_x - 1)) * 100 : 0;
            const y = this.puzzle.grid_y > 1 ? (row / (this.puzzle.grid_y - 1)) * 100 : 0;
            return `${x}% ${y}%`;
        },
        
        selectPiece(index) {
            if (this.hasPlacedToday) {
                this.setStatus("Anda sudah meletakkan kepingan hari ini! Kembali lagi besok.", 'info');
                return;
            }
            if (this.selectedPiece === index) {
                this.selectedPiece = null;
            } else {
                this.selectedPiece = index;
                this.setStatus("Kepingan dipilih! Sekarang klik kotak kosong di papan yang sesuai.", 'info');
            }
        },
        
        async placeAt(targetIndex) {
            if (this.selectedPiece === null) {
                this.setStatus("Pilih kepingan dari daftar sebelah kiri terlebih dahulu!", 'info');
                return;
            }
            
            const pieceIdx = this.selectedPiece;
            
            try {
                const res = await fetch('{{ route("forum.puzzle.place") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        puzzle_id: this.puzzle.id,
                        piece_index: pieceIdx,
                        target_index: targetIndex
                    })
                });
                
                const data = await res.json();
                if (data.success) {
                    this.selectedPiece = null;
                    this.hasPlacedToday = true;
                    this.setStatus(data.message, 'success');
                    await this.fetchState();
                } else {
                    this.setStatus(data.message, 'error');
                }
            } catch(e) {
                this.setStatus("Error koneksi saat meletakkan puzzle.", 'error');
            }
        },

        async resetPuzzleAdmin() {
            if (!confirm("Reset semua keping puzzle dan beri 10 keping acak awal dari sistem?")) return;
            try {
                const res = await fetch('{{ route("forum.puzzle.reset") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': getCsrfToken()
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.selectedPiece = null;
                    this.setStatus(data.message, 'success');
                    await this.fetchState();
                } else {
                    this.setStatus(data.message, 'error');
                }
            } catch(e) {
                this.setStatus("Gagal mereset puzzle.", 'error');
            }
        }
    }
}
</script>
<!-- PWA Install Guide Modal -->
<div id="pwaGuideModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
    <div class="bg-white border border-slate-200 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-indigo-50 to-purple-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold">
                    <i class="ph-bold ph-cellphone-charging text-xl"></i>
                </div>
                <div>
                    <h3 class="forum-hdr text-base font-bold text-slate-900 leading-tight">Install Aplikasi <span class="text-slate-900">Pembda</span><span class="text-red-600 font-black">HUB</span></h3>
                    <div class="text-xs text-indigo-700 font-bold">Aplikasi Mobile Resmi PembdaHUB</div>
                </div>
            </div>
            <button onclick="document.getElementById('pwaGuideModal').style.display = 'none'" class="text-slate-400 hover:text-slate-700 p-1">
                <i class="ph-bold ph-x text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4 text-sm text-slate-800">
            <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-start gap-3">
                <i class="ph-bold ph-info text-indigo-600 text-xl flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-indigo-900 font-medium">Aplikasi PembdaHUB Mobile akan terpasang di Layar Utama HP Anda dengan Logo Resmi Perguruan PEMBDA.</div>
            </div>
            
            <div class="space-y-3 pt-2">
                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Langkah Mudah di HP Android (Chrome):</h4>
                <div class="flex items-start gap-3 text-xs">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center flex-shrink-0">1</div>
                    <div class="pt-0.5 font-semibold text-slate-800">Klik <strong>Titik Tiga (⋮)</strong> di pojok kanan atas browser Chrome HP Anda.</div>
                </div>
                <div class="flex items-start gap-3 text-xs">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center flex-shrink-0">2</div>
                    <div class="pt-0.5 font-semibold text-slate-800">Pilih menu <strong>"Instal aplikasi"</strong> ATAU <strong>"Tambahkan ke Layar Utama"</strong>.</div>
                </div>
                <div class="flex items-start gap-3 text-xs">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center flex-shrink-0">3</div>
                    <div class="pt-0.5 font-semibold text-slate-800">Klik <strong>"Instal" / "Tambah"</strong>. Ikon PembdaHUB Mobile akan langsung muncul di HP Anda!</div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button onclick="document.getElementById('pwaGuideModal').style.display = 'none'" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">
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
                    // Force convert ke plain JS array — handle object, array, atau apapun
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

<!-- Mobile Floating Action Button (Buat Status / Post) -->
<a href="{{ route('forum.create') }}" 
   class="md:hidden fixed bottom-6 right-6 z-50 w-14 h-14 bg-gradient-to-tr from-red-600 to-red-500 text-white rounded-full flex items-center justify-center shadow-xl shadow-red-500/40 hover:scale-110 active:scale-95 transition-all"
   title="Buat Status Baru">
    <i class="ph-bold ph-plus text-2xl"></i>
</a>
@endsection
