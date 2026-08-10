@extends(auth()->user()->layout)

@section('title', 'Pembda Space')

@section('content')
<!-- Dynamic Google Fonts & Phosphor Icons -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;650;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

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
        border-color: #c7d2fe;
        box-shadow: 0 4px 20px rgba(99,102,241,0.08);
        transform: translateY(-1px);
    }

    /* Search bar */
    .forum-search {
        background: #f8fafc !important;
        border: 1.5px solid #e2e8f0 !important;
        color: #1e293b !important;
    }
    .forum-search:focus {
        border-color: #6366f1 !important;
        background: #fff !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
    }
    .forum-search::placeholder { color: #94a3b8 !important; }

    /* Sidebar active channel */
    .channel-active {
        background: linear-gradient(135deg, #eef2ff, #f5f3ff) !important;
        color: #4f46e5 !important;
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
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 shadow-md shadow-indigo-500/30 flex items-center justify-center flex-shrink-0">
                    <i class="ph-bold ph-lightning text-white text-xl"></i>
                </div>
                <div class="flex flex-col justify-center">
                    <h1 class="forum-hdr text-xl font-bold text-slate-900 tracking-tight leading-tight m-0 p-0">Pembda Space</h1>
                    <span class="text-[10px] text-slate-900 font-black uppercase tracking-widest leading-none -mt-0.5">COMMUNITY</span>
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
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 shadow-md shadow-indigo-500/30 flex items-center justify-center flex-shrink-0">
                        <i class="ph-bold ph-lightning text-white text-2xl"></i>
                    </div>
                    <div class="flex flex-col justify-center min-w-0">
                        <h1 class="forum-hdr text-xl font-bold text-slate-900 tracking-tight leading-tight m-0 p-0">Pembda Space</h1>
                        <span class="text-[10px] text-slate-900 font-black uppercase tracking-widest leading-none -mt-0.5">COMMUNITY</span>
                    </div>
                </div>

                <nav class="flex-1 space-y-6">
                    <!-- All Channels -->
                    <div class="space-y-1">
                        <a href="{{ route('forum.index', array_filter(['search' => $search])) }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 {{ !$category ? 'channel-active' : 'text-slate-900 font-semibold channel-hover' }}">
                            <div class="flex items-center gap-3">
                                <i class="ph-bold ph-compass text-lg {{ !$category ? 'text-indigo-500' : '' }}"></i>
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
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isActive ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-900' }}">{{ $count }}</span>
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
        <div class="flex-1 flex flex-col min-w-[300px] w-full bg-forum-base p-4 sm:p-6 lg:p-8 max-h-[85vh] overflow-y-auto">
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
                       class="flex px-6 py-2.5 rounded-full bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-extrabold text-sm shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-600/40 hover:scale-105 transition-all duration-200 items-center gap-2 whitespace-nowrap">
                        <i class="ph-bold ph-plus text-base text-white"></i> <span class="text-white font-extrabold">Buat Post</span>
                    </a>
                </div>
            </div>

            <!-- Pembda Colabs (Puzzle) Widget -->
            <div class="mb-6 bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm" x-data="pembdaColabs()">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 to-indigo-50/50 cursor-pointer hover:bg-indigo-50/70 transition" @click="toggleCollapse()">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center shadow-md shadow-blue-200">
                            <i class="ph-bold ph-puzzle-piece text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="forum-hdr text-lg font-bold text-slate-800 tracking-tight leading-tight">Pembda COLABS</h2>
                            <span class="text-xs text-slate-400 hidden sm:inline">Susun puzzle bersama angkatan. 1 Keping / Hari.</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click.stop="showGuide = true" class="text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                            <i class="ph-bold ph-book-open mr-1"></i> Panduan
                        </button>
                        @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin()))
                        <button @click.stop="resetPuzzleAdmin()" class="text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 px-2.5 py-1.5 rounded-lg hover:bg-rose-100 transition" title="Reset Puzzle (Admin)">
                            <i class="ph-bold ph-arrows-counter-clockwise mr-1"></i> Reset
                        </button>
                        @endif
                        <button class="text-slate-400 hover:text-slate-700 transition p-1.5 bg-slate-100 rounded-lg">
                            <i class="ph-bold ph-caret-down transition-transform duration-300" :class="isCollapsed ? '' : 'rotate-180'"></i>
                        </button>
                    </div>
                </div>
                
                <div x-show="!isCollapsed" x-transition.opacity.duration.300ms class="p-4">
                    <!-- Status Banner / Message Alert -->
                    <div x-show="statusMessage" x-transition.opacity
                         class="mb-4 p-3 rounded-xl text-sm font-semibold flex items-center justify-between shadow-sm"
                         :class="statusType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : (statusType === 'error' ? 'bg-rose-50 border border-rose-200 text-rose-800' : 'bg-indigo-50 border border-indigo-200 text-indigo-800')">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold" :class="statusType === 'success' ? 'ph-check-circle text-emerald-600 text-lg' : (statusType === 'error' ? 'ph-warning-circle text-rose-600 text-lg' : 'ph-info text-indigo-600 text-lg')"></i>
                            <span x-text="statusMessage"></span>
                        </div>
                        <button @click="statusMessage = ''" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
                    </div>

                    <template x-if="puzzle">
                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Left: Inventory (Available Pieces) -->
                            <div class="w-full md:w-1/3 bg-slate-50 rounded-xl border border-slate-200 p-3">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kepingan Tersedia</h3>
                                    <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full" x-text="inventory.length + ' keping'"></span>
                                </div>
                                
                                <div x-show="hasPlacedToday" class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs font-bold text-blue-700 mb-3 text-center">
                                    <i class="ph-bold ph-check-circle text-2xl mb-1 block text-blue-500"></i>
                                    Kamu sudah menaruh kepingan hari ini! Kembali besok.
                                </div>
                                
                                <div x-show="selectedPiece !== null && !hasPlacedToday" class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs font-bold text-amber-800 mb-3 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <i class="ph-bold ph-hand-tap text-amber-600 text-sm"></i>
                                        Kepingan dipilih! Klik kotak kosong di papan.
                                    </span>
                                    <button @click="selectedPiece = null" class="text-amber-600 hover:text-amber-800 underline text-[10px]">Batal</button>
                                </div>

                                <div class="flex flex-wrap gap-1.5 max-h-[320px] overflow-y-auto no-scrollbar justify-center p-1" :class="hasPlacedToday ? 'opacity-50 pointer-events-none' : ''">
                                    <template x-for="idx in inventory" :key="'inv-'+idx">
                                        <button @click="selectPiece(idx)" 
                                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-lg border-2 transition-all duration-200 relative overflow-hidden group shadow-sm"
                                                :class="selectedPiece === idx ? 'border-indigo-600 scale-110 z-10 ring-4 ring-indigo-200 shadow-md' : 'border-slate-200 hover:border-indigo-400 hover:scale-105'"
                                                :style="`background-image: url(${puzzle.image_url}); background-size: ${puzzle.grid_x * 100}% ${puzzle.grid_y * 100}%; background-position: ${getBgPos(idx)};`"
                                                :title="'Keping #' + (idx + 1)">
                                        </button>
                                    </template>
                                    <div x-show="inventory.length === 0" class="text-sm text-emerald-600 font-bold p-6 text-center w-full">
                                        🎉 Selamat! Puzzle Telah Selesai Disusun! 🎉
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right: Board -->
                            <div class="w-full md:w-2/3 bg-slate-100 rounded-xl border border-slate-200 p-3 flex flex-col items-center justify-center relative overflow-hidden">
                                <div class="w-full flex items-center justify-between mb-3">
                                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5" x-text="`${puzzle.title} (${puzzle.progress.percentage}%)`"></h3>
                                    <button @click="showReference = !showReference" 
                                            class="text-[11px] font-bold px-2.5 py-1 rounded-lg border transition flex items-center gap-1"
                                            :class="showReference ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'">
                                        <i class="ph-bold ph-eye"></i>
                                        <span x-text="showReference ? 'Sembunyikan Petunjuk' : 'Intip Bayangan'"></span>
                                    </button>
                                </div>
                                
                                <div class="w-full max-w-[500px] relative shadow-md bg-white border border-slate-300 rounded-xl overflow-hidden">
                                    <!-- Aspect ratio hack -->
                                    <div :style="`padding-bottom: ${(puzzle.grid_y / puzzle.grid_x) * 100}%;`"></div>
                                    
                                    <!-- Faint Background Overlay Reference Image -->
                                    <div class="absolute inset-0 bg-cover bg-no-repeat transition-opacity duration-300 pointer-events-none"
                                         :class="showReference ? 'opacity-40' : 'opacity-20'"
                                         :style="`background-image: url(${puzzle.image_url});`"></div>

                                    <div class="absolute inset-0"
                                         :style="`display: grid; grid-template-columns: repeat(${puzzle.grid_x}, 1fr); grid-template-rows: repeat(${puzzle.grid_y}, 1fr);`">
                                        <template x-for="(piece, i) in board" :key="'board-'+i">
                                        <div class="w-full h-full border-[0.5px] border-slate-300/40 relative group cursor-pointer"
                                             @click="placeAt(i)">
                                             
                                            <!-- Empty Slot -->
                                            <div x-show="!piece" 
                                                 class="absolute inset-0 hover:bg-indigo-500/25 transition flex items-center justify-center"
                                                 :class="selectedPiece !== null ? 'hover:ring-2 hover:ring-indigo-500 z-10' : ''">
                                                <i x-show="selectedPiece !== null" class="ph-bold ph-plus text-indigo-600 text-xs drop-shadow-sm"></i>
                                            </div>
                                            
                                            <!-- Placed Piece -->
                                            <div x-show="piece" class="absolute inset-0 shadow-[inset_0_0_2px_rgba(0,0,0,0.2)]"
                                                 :style="`background-image: url(${puzzle.image_url}); background-size: ${puzzle.grid_x * 100}% ${puzzle.grid_y * 100}%; background-position: ${getBgPos(i)};`">
                                            </div>
                                            
                                            <!-- Tooltip -->
                                            <div x-show="piece" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded shadow-lg pointer-events-none opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50">
                                                Oleh <span class="font-bold text-blue-300" x-text="piece ? (piece.placed_by || 'Sistem (Bonus)') : ''"></span>
                                            </div>
                                        </div>
                                    </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!puzzle">
                        <div class="text-center p-8 text-slate-400 flex flex-col items-center gap-2">
                            <i class="ph-bold ph-spinner animate-spin text-2xl text-indigo-500"></i>
                            <span>Sedang memuat puzzle...</span>
                        </div>
                    </template>
                </div>

                <!-- Guide Modal -->
                <div x-show="showGuide" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display: none;">
                    <div @click.away="showGuide = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
                        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <h3 class="forum-hdr text-lg font-bold text-slate-800"><i class="ph-bold ph-puzzle-piece text-indigo-500 mr-2"></i> Cara Main Pembda Colabs</h3>
                            <button @click="showGuide = false" class="text-slate-400 hover:text-slate-700"><i class="ph-bold ph-x text-xl"></i></button>
                        </div>
                        <div class="p-5 space-y-4 text-sm text-slate-600">
                            <p><strong class="text-slate-800">1. Pilih Kepingan:</strong> Di sebelah kiri, pilih satu kepingan puzzle yang tersedia.</p>
                            <p><strong class="text-slate-800">2. Letakkan dengan Benar:</strong> Klik salah satu kotak kosong di papan kanan. Jika posisinya salah, kepingan akan ditolak.</p>
                            <p><strong class="text-slate-800">3. Satu Hari, Satu Keping:</strong> Setiap siswa hanya memiliki hak meletakkan 1 kepingan puzzle per hari (berhasil ataupun tidak). Gunakan dengan bijak!</p>
                            <p><strong class="text-indigo-600">4. +10 Poin Reputasi:</strong> Jika kepingan diletakkan dengan benar, kamu mendapat tambahan poin!</p>
                            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                                <button @click="showGuide = false" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 text-white font-bold rounded-xl transition w-full shadow-md shadow-indigo-200">Mengerti, Ayo Main!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                @if(auth()->id() === $thread->user_id || auth()->user()->isSuperAdmin() || auth()->user()->isAdminSekolah() || auth()->user()->isGuru())
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
        pieces: [],
        hasPlacedToday: false,
        showGuide: false,
        showReference: true,
        isCollapsed: false,
        selectedPiece: null, 
        inventory: [], 
        board: [],
        statusMessage: '',
        statusType: 'info',
        
        async init() {
            const savedState = localStorage.getItem('pembdaColabsCollapsed');
            if (savedState !== null) this.isCollapsed = savedState === 'true';
            
            await this.fetchState();
            setInterval(() => {
                if(!this.isCollapsed) this.fetchState();
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
                const res = await fetch('{{ route("forum.puzzle.state") }}');
                const data = await res.json();
                if(data.success) {
                    this.puzzle = data.puzzle;
                    this.hasPlacedToday = data.has_placed_today;
                    this.pieces = data.pieces;
                    this.rebuildBoard();
                } else if (data.message) {
                    this.setStatus(data.message, 'error');
                }
            } catch(e) {}
        },
        
        rebuildBoard() {
            if(!this.puzzle) return;
            const total = this.puzzle.grid_x * this.puzzle.grid_y;
            let newBoard = new Array(total).fill(null);
            let placedIndices = new Set();
            
            this.pieces.forEach(p => {
                if(p.is_placed) {
                    newBoard[p.index] = p;
                    placedIndices.add(p.index);
                }
            });
            this.board = newBoard;
            
            let newInv = [];
            for(let i=0; i<total; i++) {
                if(!placedIndices.has(i)) newInv.push(i);
            }
            if(this.inventory.length !== newInv.length) {
                this.inventory = newInv.sort(() => Math.random() - 0.5);
            }
        },
        
        toggleCollapse() {
            this.isCollapsed = !this.isCollapsed;
            localStorage.setItem('pembdaColabsCollapsed', this.isCollapsed);
            if(!this.isCollapsed) this.fetchState();
        },
        
        getBgPos(index) {
            if(!this.puzzle) return '0 0';
            const col = index % this.puzzle.grid_x;
            const row = Math.floor(index / this.puzzle.grid_x);
            const x = this.puzzle.grid_x > 1 ? (col / (this.puzzle.grid_x - 1)) * 100 : 0;
            const y = this.puzzle.grid_y > 1 ? (row / (this.puzzle.grid_y - 1)) * 100 : 0;
            return `${x}% ${y}%`;
        },
        
        selectPiece(index) {
            if(this.hasPlacedToday) {
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
            if(this.selectedPiece === null) {
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
                if(data.success) {
                    this.selectedPiece = null;
                    this.hasPlacedToday = true;
                    this.setStatus(data.message, 'success');
                    this.fetchState();
                } else {
                    // Position wrong: keep selectedPiece active so user can retry another slot easily!
                    this.setStatus(data.message, 'error');
                }
            } catch(e) {
                this.setStatus("Error koneksi saat meletakkan puzzle.", 'error');
            }
        },

        async resetPuzzleAdmin() {
            if(!confirm("Reset semua keping puzzle dan beri 10 keping acak awal dari sistem?")) return;
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
                if(data.success) {
                    this.selectedPiece = null;
                    this.setStatus(data.message, 'success');
                    this.fetchState();
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
</script>

<!-- Mobile Floating Action Button (Buat Status / Post) -->
<a href="{{ route('forum.create') }}" 
   class="md:hidden fixed bottom-6 right-6 z-50 w-14 h-14 bg-gradient-to-tr from-indigo-500 to-fuchsia-500 text-white rounded-full flex items-center justify-center shadow-xl shadow-indigo-500/40 hover:scale-110 active:scale-95 transition-all"
   title="Buat Status Baru">
    <i class="ph-bold ph-plus text-2xl"></i>
</a>
@endsection
