@extends(auth()->user()->layout)

@section('title', $thread->title)

@section('content')
<!-- Dynamic Google Fonts (Plus Jakarta Sans & JetBrains Mono) & Phosphor Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
{{-- Alpine.js dimuat oleh master layout — JANGAN duplikasi --}}

<!-- PWA Manifest & App Shell Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#121316">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="PembdaSPACE">
<link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/sw.js');
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
<!-- MathJax for math equations -->
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
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
        background-color: #faf7f2;
        background-image: 
            radial-gradient(circle at 10% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 90% 85%, rgba(236, 72, 153, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 50% 50%, rgba(245, 158, 11, 0.05) 0%, transparent 60%),
            radial-gradient(#d1cfc7 1.2px, transparent 1.2px);
        background-size: auto, auto, auto, 22px 22px;
    }

    /* Tactile Cards */
    .tactile-card {
        background: #ffffff;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        border-radius: 1rem;
    }

    /* Compose Emoji Picker Custom CSS */
    .compose-emoji-picker {
        position: absolute !important;
        bottom: 100% !important;
        left: 0 !important;
        margin-bottom: 8px !important;
        padding: 10px !important;
        background-color: #ffffff !important;
        border: 2px solid #121316 !important;
        border-radius: 16px !important;
        box-shadow: 4px 4px 0px #121316 !important;
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        gap: 4px !important;
        z-index: 50 !important;
        width: 230px !important;
    }
    .compose-emoji-btn {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        transition: transform 0.15s !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        color: #121316 !important;
    }
    .compose-emoji-btn:hover {
        background-color: #fde047 !important;
        transform: scale(1.2) !important;
    }
</style>

<!-- App Window Wrapper -->
<div class="w-full text-[#121316] font-['Plus_Jakarta_Sans',sans-serif] rounded-3xl border-2 border-[#121316] mx-auto flex flex-col pt-4 px-4 sm:px-6 relative shadow-[6px_6px_0px_#121316] graph-canvas" style="min-height: 85vh; padding-bottom: 240px !important;" x-data="forumChat()">
    
    <!-- Top Nav Bar (Neo-Brutalist) -->
    <div class="flex items-center justify-between bg-white/95 backdrop-blur-xl p-3.5 sm:p-4 rounded-2xl border-2 border-[#121316] mb-6 sticky top-4 z-40 shadow-[3px_3px_0px_#121316]">
        <div class="flex items-center gap-3.5 min-w-0">
            <a href="{{ route('forum.index') }}" class="btn-tactile-white w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" title="Kembali ke Daftar">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div class="min-w-0">
                <div class="text-[10px] font-mono-code font-black text-[#ff3823] uppercase tracking-wider">{{ $thread->category_label }}</div>
                <h1 class="forum-hdr text-base sm:text-lg font-black text-[#121316] line-clamp-1 leading-snug">{{ $thread->title }}</h1>
            </div>
        </div>
        
        <div class="flex items-center gap-2 flex-shrink-0">
            @if(auth()->id() === $thread->user_id || auth()->user()->isSuperAdmin())
                <a href="{{ route('forum.edit', $thread) }}" class="btn-tactile-white w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center text-[#121316]" title="Edit Topik">
                    <i class="fa-solid fa-pen-to-square text-base sm:text-lg"></i>
                </a>
                <form action="{{ route('forum.destroy', $thread) }}" method="POST" onsubmit="return confirm('Yakin hapus postingan ini?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-tactile-white w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center text-rose-600 hover:text-rose-700" title="Hapus Topik">
                        <i class="fa-solid fa-trash text-base sm:text-lg"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border-2 border-emerald-600 text-emerald-900 flex items-center gap-3 shadow-[2px_2px_0px_#059669]">
        <i class="fa-solid fa-circle-check text-xl text-emerald-600"></i> <span class="text-xs sm:text-sm font-black">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border-2 border-rose-600 text-rose-900 flex items-center gap-3 shadow-[2px_2px_0px_#e11d48]">
        <i class="fa-solid fa-circle-xmark text-xl text-rose-600"></i> <span class="text-xs sm:text-sm font-black">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Topic Meta Breadcrumb Bar -->
    <div class="mb-6 w-full max-w-5xl mx-auto rounded-2xl bg-white border-2 border-[#121316] p-3 sm:p-4 flex flex-wrap items-center justify-between gap-3 shadow-[3px_3px_0px_#121316]">
        <div class="flex items-center gap-2 flex-wrap text-xs font-mono-code font-bold">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#fde047] border border-[#121316] rounded-lg text-[#121316] font-black">
                <span>✱ {{ $thread->category_label }}</span>
            </span>
            <span class="text-[#666]">• Diposting {{ $thread->created_at->format('d M Y, H:i') }}</span>
            @if($thread->group)
                <span class="text-[#666]">• di squad <strong>{{ $thread->group->name }}</strong></span>
            @endif
        </div>
        <div class="flex items-center gap-3 text-xs font-mono-code font-black text-[#121316]">
            <span class="flex items-center gap-1"><i class="fa-solid fa-comment-dots text-[#ff3823]"></i> {{ $thread->replies->count() }} Balasan</span>
            <span class="flex items-center gap-1 text-[#666]"><i class="fa-solid fa-eye"></i> {{ $thread->views_count }} Views</span>
        </div>
    </div>

    <!-- CHAT AREA -->
    <div class="space-y-6 flex-1 flex flex-col max-w-5xl mx-auto w-full">
        
        <!-- ORIGINAL POST (First Message) -->
        <div class="flex gap-3 sm:gap-4">
            <img src="{{ $thread->user->avatar_url }}" 
                 class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex-shrink-0 object-cover">
            <div class="flex-1 min-w-0 space-y-2">
                <!-- Meta -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-extrabold text-[#121316] text-sm sm:text-base">{{ $thread->user->name }}</span>
                    <span class="text-[10px] font-mono-code font-black px-1.5 py-0.5 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] uppercase tracking-wider">{{ $thread->user->role }}</span>
                    @if($thread->user->ekskul_flair)
                    <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border border-[#121316] bg-[#fde047] text-[#121316]" title="{{ $thread->user->ekskul_flair['label'] }}">
                        {{ $thread->user->ekskul_flair['label'] }}
                    </span>
                    @endif
                    <span class="text-xs font-mono-code text-[#666]">&bull; {{ $thread->created_at->format('H:i • d M Y') }}</span>
                </div>
                
                <!-- Bubble (Neo-Brutalist) -->
                <div class="bg-white border-2 border-[#121316] rounded-2xl rounded-tl-none p-5 sm:p-6 shadow-[4px_4px_0px_#121316] w-full max-w-3xl relative">
                    <h2 class="forum-hdr text-xl sm:text-2xl font-black text-[#121316] mb-3 leading-snug">{{ $thread->title }}</h2>
                    <div class="prose prose-slate prose-sm sm:prose-base max-w-none text-[#222] font-medium leading-relaxed">
                        {!! nl2br(e($thread->content)) !!}
                    </div>

                    @if($thread->image_path)
                        <div class="mt-4 rounded-xl overflow-hidden border-2 border-[#121316] max-w-lg shadow-[2px_2px_0px_#121316]">
                            <img src="{{ asset('storage/' . $thread->image_path) }}" class="w-full h-auto">
                        </div>
                    @endif

                    @if($thread->attachment_path)
                        <a href="{{ asset('storage/' . $thread->attachment_path) }}" download class="mt-4 flex items-center gap-3 p-3 bg-[#faf8f5] hover:bg-[#fde047] border-2 border-[#121316] rounded-xl transition max-w-sm shadow-[2px_2px_0px_#121316] group">
                            <div class="w-10 h-10 rounded-lg bg-white border border-[#121316] flex items-center justify-center text-[#121316] flex-shrink-0">
                                <i class="fa-solid fa-download text-xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-black text-[#121316] truncate">{{ $thread->attachment_name ?? 'Download Lampiran' }}</div>
                                <div class="text-[10px] font-mono-code text-[#555]">Klik untuk mengunduh</div>
                            </div>
                        </a>
                    @endif
                    
                    @if($thread->category === 'gaming' && ($thread->game_name || $thread->game_room_code))
                        <div class="mt-4 p-4 rounded-xl bg-rose-50 border-2 border-[#121316] flex items-center justify-between gap-4 shadow-[2px_2px_0px_#121316]">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl border-2 border-slate-900 flex items-center justify-center text-2xl flex-shrink-0" style="background-color: #dc2626 !important; color: #ffffff !important;">
                                    <i class="fa-solid fa-gamepad"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-mono-code font-black text-[#ff3823] uppercase tracking-wider">Lobi Mabar {{ $thread->game_name }}</div>
                                    <div class="text-sm font-black text-[#121316]">Kode / ID Room: <span class="text-[#ff3823] select-all">{{ $thread->game_room_code ?: 'Tidak ada kode' }}</span></div>
                                </div>
                            </div>
                            @if($thread->game_room_code)
                            <button onclick="navigator.clipboard.writeText('{{ addslashes($thread->game_room_code) }}'); alert('Kode Room / ID berhasil disalin!');" class="btn-tactile-red px-3.5 py-2 rounded-xl text-xs font-black flex items-center gap-1.5">
                                <i class="fa-solid fa-copy"></i> Salin ID
                            </button>
                            @endif
                        </div>
                    @endif

                    @if($thread->category === 'sharing' && $thread->file_category)
                        <div class="mt-4 p-3 bg-emerald-50 border-2 border-[#121316] rounded-xl flex items-center gap-3 shadow-[2px_2px_0px_#121316]">
                            <div class="w-9 h-9 rounded-lg border border-slate-900 flex items-center justify-center text-lg flex-shrink-0" style="background-color: #059669 !important; color: #ffffff !important;">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-mono-code font-black text-emerald-800 uppercase tracking-wider">Kategori Berkas Belajar</div>
                                <div class="text-xs font-black text-[#121316]">{{ $thread->file_category }}</div>
                            </div>
                        </div>
                    @endif

                    @if($perfCard)
                        <div class="mt-4 p-4 rounded-xl bg-purple-50 border-2 border-[#121316] flex items-start gap-4 shadow-[2px_2px_0px_#121316]">
                            <div class="w-12 h-12 rounded-xl bg-[#fde047] border border-[#121316] flex items-center justify-center text-[#121316] flex-shrink-0">
                                <i class="fa-solid fa-medal text-2xl"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-mono-code font-black text-purple-800 uppercase tracking-wider">Tautan Prestasi</div>
                                @if($thread->reference_type === \App\Models\Badge::class)
                                    <div class="text-sm font-black text-[#121316]">{{ $perfCard->name }}</div>
                                    <div class="text-xs text-[#555] font-semibold">{{ $perfCard->description }}</div>
                                @else
                                    <div class="text-sm font-black text-[#121316]">CBT: {{ $perfCard->exam->title ?? 'Ujian' }}</div>
                                    <div class="text-xs font-mono-code text-[#ff3823] font-black">Nilai: {{ $perfCard->final_score }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- EMBEDDED INTERACTIVE POLL -->
                    @if($thread->poll)
                        @php
                            $poll = $thread->poll;
                            $totalVotes = $poll->totalVotes();
                            $userVote = $poll->votes()->where('user_id', auth()->id())->first();
                            $userVotedOptionId = $userVote ? $userVote->forum_poll_option_id : null;
                        @endphp
                        <div class="mt-4 p-4 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] space-y-3.5" id="thread-detail-poll">
                            <div class="flex items-center justify-between gap-2 border-b-2 border-[#121316]/10 pb-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-[#fde047] border border-[#121316] text-[#121316] flex items-center justify-center text-sm flex-shrink-0">
                                        <i class="fa-solid fa-chart-simple"></i>
                                    </span>
                                    <span class="text-[10px] font-mono-code font-black tracking-wider uppercase text-[#121316] bg-[#faf8f5] border border-[#121316] px-2 py-0.5 rounded-md">📊 Polling Interaktif</span>
                                </div>
                                <span class="text-xs font-mono-code font-black text-[#121316] flex items-center gap-1">
                                    <i class="fa-solid fa-users text-[#2563eb]"></i>
                                    <span id="poll-total-votes">{{ $totalVotes }}</span> Suara
                                </span>
                            </div>

                            <h3 class="text-sm font-black text-[#121316] leading-snug">
                                {{ $poll->question }}
                            </h3>

                            <div class="space-y-2.5 pt-1" id="poll-options-container">
                                @foreach($poll->options as $option)
                                    @php 
                                        $pct = $totalVotes > 0 ? round(($option->votes_count / $totalVotes) * 100) : 0;
                                        $hasVoted = $userVotedOptionId === $option->id;
                                    @endphp
                                    <button type="button" onclick="votePoll({{ $option->id }})" class="w-full relative overflow-hidden rounded-xl border-2 text-left p-3 transition-all duration-200 group cursor-pointer {{ $hasVoted ? 'border-[#121316] bg-[#fde047] shadow-[2px_2px_0px_#121316]' : 'border-[#121316] bg-[#faf8f5] hover:bg-white shadow-[1px_1px_0px_#121316]' }}" id="poll-btn-{{ $option->id }}">
                                        <!-- Progress Bar -->
                                        <div class="absolute top-0 left-0 h-full {{ $hasVoted ? 'bg-[#facc15]/60' : 'bg-[#e2e8f0]' }} transition-all duration-500 pointer-events-none" style="width: {{ $pct }}%" id="poll-bg-{{ $option->id }}"></div>
                                        
                                        <div class="relative z-10 flex justify-between items-center text-xs font-bold">
                                            <div class="flex items-center gap-2.5 {{ $hasVoted ? 'text-[#121316]' : 'text-[#333]' }}">
                                                <span class="w-5 h-5 rounded-full flex items-center justify-center border border-[#121316] text-[10px] font-mono-code font-black flex-shrink-0 {{ $hasVoted ? 'bg-[#121316] text-[#fde047]' : 'bg-white text-[#121316]' }}" id="poll-check-{{ $option->id }}">
                                                    @if($hasVoted)
                                                        <i class="fa-solid fa-check"></i>
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </span>
                                                <span class="truncate font-black">{{ $option->option_text }}</span>
                                            </div>
                                            <div class="font-mono-code font-black flex items-center gap-1.5 flex-shrink-0 text-[#121316]">
                                                <span id="poll-pct-{{ $option->id }}">{{ $pct }}%</span>
                                                <span class="text-[10px] text-[#666]" id="poll-count-{{ $option->id }}">({{ $option->votes_count }})</span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            
                            @if(!$poll->isOpen())
                                <div class="text-[11px] font-mono-code font-black text-amber-700 flex items-center gap-1 pt-1">
                                    <i class="fa-solid fa-lock"></i> Polling ini telah ditutup
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Thread Reactions & Actions -->
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <!-- Picker Button -->
                    <div class="flex items-center relative">
                        <button @click="togglePicker('thread')" class="w-8 h-8 rounded-xl bg-white hover:bg-[#fde047] border-2 border-[#121316] shadow-[1px_1px_0px_#121316] flex items-center justify-center text-[#121316] transition">
                            <i class="fa-regular fa-face-smile text-base"></i>
                        </button>
                        <!-- Picker Dropdown (Inline) -->
                        <div x-show="pickerOpen === 'thread'" class="ml-2 p-1.5 bg-white border-2 border-[#121316] rounded-xl flex gap-1 shadow-[3px_3px_0px_#121316] z-20">
                            @foreach(\App\Models\ForumReaction::EMOJIS as $emoji => $name)
                                <button @click="reactThread('{{ $emoji }}')" class="w-8 h-8 rounded-lg hover:bg-[#faf8f5] flex items-center justify-center text-lg transition-transform hover:scale-125">
                                    {{ $emoji }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Existing Reactions -->
                    <div id="thread-reactions" class="flex flex-wrap gap-2">
                        @foreach($threadReactions as $emoji => $count)
                            <button onclick="reactThreadAjax('{{ $emoji }}')" class="flex items-center gap-1.5 px-2.5 py-1 bg-white hover:bg-[#faf8f5] border-2 border-[#121316] rounded-lg text-xs font-mono-code font-black text-[#121316] transition shadow-[1px_1px_0px_#121316]">
                                <span>{{ $emoji }}</span> <span>{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- COLLAB PANEL (if active) -->
        @if(in_array($thread->category, ['project_idea', 'committee']) && $thread->status !== 'completed')
            <div class="max-w-3xl ml-13 sm:ml-16 bg-white border-2 border-[#121316] rounded-2xl p-5 shadow-[3px_3px_0px_#121316] relative overflow-hidden">
                <h3 class="forum-hdr text-sm font-black text-[#121316] flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-handshake-angle text-[#2563eb] text-lg"></i> Rekrutmen Tim Kolaborasi
                </h3>
                @if(auth()->id() !== $thread->user_id && $thread->status === 'seeking_members')
                    @php $hasApplied = $thread->members()->where('user_id', auth()->id())->exists(); @endphp
                    @if(!$hasApplied)
                        <form action="{{ route('forum.join', $thread) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="notes" placeholder="Pesan singkat (opsional)..." class="flex-1 bg-[#faf8f5] border-2 border-[#121316] rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-[#121316] focus:outline-none">
                            <button type="submit" class="btn-tactile-red px-4 py-2 rounded-xl text-xs font-black">Gabung</button>
                        </form>
                    @else
                        <div class="text-xs font-mono-code font-black text-amber-700">Kamu sudah mendaftar. Menunggu persetujuan pemilik topik.</div>
                    @endif
                @endif
            </div>
        @endif

        <!-- REPLIES DIVIDER -->
        @if($thread->replies->count() > 0)
            <div class="flex items-center gap-4 my-4 max-w-3xl ml-13 sm:ml-16">
                <div class="h-0.5 flex-1 bg-[#121316]/20"></div>
                <span class="text-xs font-mono-code font-black text-[#121316] uppercase tracking-wider">{{ $thread->replies->count() }} Balasan</span>
                <div class="h-0.5 flex-1 bg-[#121316]/20"></div>
            </div>
        @endif

        <!-- REPLIES LIST -->
        @php
            $topLevelReplies = $thread->replies->filter(function($r) {
                return empty($r->parent_reply_id) || (int)$r->parent_reply_id === 0;
            });
        @endphp

        <div id="replies" class="space-y-6 pb-52 sm:pb-60">
            @foreach($topLevelReplies as $reply)
                <!-- Top-Level Comment Card -->
                <div class="flex gap-3 sm:gap-4" id="reply-{{ $reply->id }}" x-data="{ editing: false }">
                    <img src="{{ $reply->user->avatar_url }}" 
                         class="w-10 h-10 rounded-xl border-2 border-[#121316] shadow-[1px_1px_0px_#121316] flex-shrink-0 object-cover">
                    <div class="flex-1 min-w-0 space-y-2">
                        <!-- User Info & Edit/Delete -->
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                <span class="font-extrabold text-[#121316] text-sm">{{ $reply->user->name }}</span>
                                <span class="text-[10px] font-mono-code font-black px-1.5 py-0.5 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] uppercase tracking-wider">{{ $reply->user->role }}</span>
                                @if($reply->user->ekskul_flair)
                                <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full border border-[#121316] bg-[#fde047] text-[#121316]" title="{{ $reply->user->ekskul_flair['label'] }}">
                                    {{ $reply->user->ekskul_flair['label'] }}
                                </span>
                                @endif
                                <span class="text-xs font-mono-code text-[#666]">&bull; {{ $reply->created_at->format('H:i') }}</span>
                                @if($reply->is_accepted)
                                    <span class="text-[10px] font-mono-code font-black px-2 py-0.5 bg-[#fde047] text-[#121316] border border-[#121316] rounded-md flex items-center gap-1">
                                        <i class="fa-solid fa-star text-[#ff3823]"></i> Jawaban Terbaik
                                    </span>
                                @endif
                            </div>

                            <!-- Edit / Delete for Comment Author or Super Admin -->
                            @if(auth()->id() === $reply->user_id || auth()->user()->isSuperAdmin())
                                <div class="flex items-center gap-1 ml-auto">
                                    <button type="button" @click="editing = !editing" class="p-1 text-[#666] hover:text-[#121316] rounded-md transition text-xs font-black flex items-center gap-1" title="Edit Komentar">
                                        <i class="fa-solid fa-pen-to-square"></i> <span>Edit</span>
                                    </button>
                                    <form action="{{ route('forum.reply.destroy', $reply) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-[#666] hover:text-rose-600 rounded-md transition text-xs font-black flex items-center gap-1" title="Hapus Komentar">
                                            <i class="fa-solid fa-trash"></i> <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <!-- Normal Bubble (when NOT editing) -->
                        <div x-show="!editing">
                            <div class="{{ $reply->is_accepted ? 'bg-amber-50 border-2 border-amber-600 shadow-[3px_3px_0px_#d97706]' : 'bg-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316]' }} rounded-2xl rounded-tl-none p-4 max-w-2xl text-xs sm:text-sm text-[#222] font-medium leading-relaxed">
                                @if($reply->voice_note_path)
                                    <div class="mb-2 flex items-center gap-2 text-[#2563eb] font-mono-code font-black text-xs">
                                        <i class="fa-solid fa-microphone text-lg"></i> Pesan Suara
                                    </div>
                                    <audio controls class="w-full h-10 rounded-xl max-w-[250px] sm:max-w-xs mb-2 bg-[#faf8f5] border border-[#121316]" src="{{ asset('storage/' . $reply->voice_note_path) }}"></audio>
                                @endif
                                @if($reply->content)
                                    {!! nl2br(e($reply->content)) !!}
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center justify-between gap-2 mt-2 w-full max-w-2xl">
                                <!-- Left: Smiley & Reactions -->
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center relative">
                                        <button @click="togglePicker('reply-{{ $reply->id }}')" class="w-6 h-6 rounded-lg bg-white border border-[#121316] flex items-center justify-center text-[#666] hover:text-[#121316] transition">
                                            <i class="fa-regular fa-face-smile text-sm"></i>
                                        </button>
                                        <div x-show="pickerOpen === 'reply-{{ $reply->id }}'" class="ml-2 p-1 bg-white border-2 border-[#121316] rounded-xl flex gap-1 shadow-[2px_2px_0px_#121316] z-20">
                                            @foreach(\App\Models\ForumReaction::EMOJIS as $emoji => $name)
                                                <button @click="reactReply({{ $reply->id }}, '{{ $emoji }}')" class="w-6 h-6 rounded-lg hover:bg-[#faf8f5] flex items-center justify-center text-sm transition-transform hover:scale-125">
                                                    {{ $emoji }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div id="reply-reactions-{{ $reply->id }}" class="flex flex-wrap gap-1">
                                        @foreach($reply->getReactionCounts() as $emoji => $count)
                                            <button onclick="reactReplyAjax({{ $reply->id }}, '{{ $emoji }}')" class="flex items-center gap-1 px-2 py-0.5 bg-white border border-[#121316] rounded-md text-[10px] font-mono-code font-black text-[#121316] transition shadow-[1px_1px_0px_#121316]">
                                                <span>{{ $emoji }}</span> <span>{{ $count }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Right: Reply Button & Accept Button -->
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            data-reply-id="{{ $reply->id }}"
                                            data-user-name="{{ $reply->user->name }}"
                                            data-snippet="{{ Str::limit(strip_tags($reply->content), 40) }}"
                                            onclick="quoteReplyFromBtn(this)" 
                                            class="text-xs font-mono-code font-black text-[#ff3823] hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-reply"></i> Balas
                                    </button>

                                    @if(auth()->id() === $thread->user_id && $thread->category === 'tanya_jawab' && !$reply->is_accepted)
                                        <form action="{{ route('forum.reply.accept', $reply) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 bg-[#fde047] border border-[#121316] rounded-md text-[10px] font-mono-code font-black text-[#121316] hover:bg-[#facc15] flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i> Jadikan Terbaik
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form Bubble (when editing) -->
                        <div x-show="editing" class="bg-white border-2 border-[#121316] rounded-2xl p-4 max-w-2xl shadow-[3px_3px_0px_#121316]">
                            <form action="{{ route('forum.reply.update', $reply) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <textarea name="content" rows="3" class="w-full p-3 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] outline-none resize-y" required>{{ $reply->content }}</textarea>
                                <div class="flex items-center justify-end gap-2 mt-2">
                                    <button type="button" @click="editing = false" class="btn-tactile-white px-3 py-1.5 rounded-lg text-xs font-black">Batal</button>
                                    <button type="submit" class="btn-tactile-red px-4 py-1.5 rounded-lg text-xs font-black">Simpan</button>
                                </div>
                            </form>
                        </div>

                        <!-- CHILD REPLIES (Threaded Indented) -->
                        @if($reply->children && $reply->children->count() > 0)
                            <div class="mt-4 space-y-4 pl-4 sm:pl-6 border-l-2 border-[#121316]">
                                @foreach($reply->children as $child)
                                    <div class="flex gap-2.5 sm:gap-3" id="reply-{{ $child->id }}" x-data="{ editingChild: false }">
                                        <img src="{{ $child->user->avatar_url }}" class="w-8 h-8 rounded-lg border-2 border-[#121316] object-cover flex-shrink-0">
                                        <div class="flex-1 min-w-0 space-y-1.5">
                                            <div class="flex items-center justify-between gap-1 flex-wrap">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-extrabold text-[#121316] text-xs sm:text-sm">{{ $child->user->name }}</span>
                                                    <span class="text-[9px] font-mono-code font-bold px-1 py-0.2 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] uppercase">{{ $child->user->role }}</span>
                                                    <span class="text-[11px] font-mono-code text-[#666]">&bull; {{ $child->created_at->format('H:i') }}</span>
                                                </div>

                                                @if(auth()->id() === $child->user_id || auth()->user()->isSuperAdmin())
                                                    <div class="flex items-center gap-1 ml-auto">
                                                        <button type="button" @click="editingChild = !editingChild" class="text-[#666] hover:text-[#121316] text-xs font-black">Edit</button>
                                                        <form action="{{ route('forum.reply.destroy', $child) }}" method="POST" onsubmit="return confirm('Hapus komentar ini?');" class="inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-rose-600 text-xs font-black">Hapus</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>

                                            <div x-show="!editingChild">
                                                <div class="bg-white border-2 border-[#121316] rounded-xl rounded-tl-none p-3 max-w-xl text-xs text-[#222] font-medium leading-relaxed shadow-[1px_1px_0px_#121316]">
                                                    {!! nl2br(e($child->content)) !!}
                                                </div>
                                                <div class="flex items-center justify-between gap-2 mt-1.5 max-w-xl">
                                                    <div class="flex items-center gap-1.5">
                                                        <div class="flex items-center relative">
                                                            <button @click="togglePicker('reply-{{ $child->id }}')" class="w-5 h-5 rounded hover:bg-slate-200 flex items-center justify-center text-[#666]">
                                                                <i class="fa-regular fa-face-smile text-xs"></i>
                                                            </button>
                                                            <div x-show="pickerOpen === 'reply-{{ $child->id }}'" class="ml-1 p-1 bg-white border-2 border-[#121316] rounded-lg flex gap-1 shadow z-20">
                                                                @foreach(\App\Models\ForumReaction::EMOJIS as $emoji => $name)
                                                                    <button @click="reactReply({{ $child->id }}, '{{ $emoji }}')" class="w-5 h-5 flex items-center justify-center text-xs hover:scale-125">
                                                                        {{ $emoji }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div id="reply-reactions-{{ $child->id }}" class="flex flex-wrap gap-1">
                                                            @foreach($child->getReactionCounts() as $emoji => $count)
                                                                <button onclick="reactReplyAjax({{ $child->id }}, '{{ $emoji }}')" class="px-1.5 py-0.2 bg-white border border-[#121316] rounded text-[9px] font-mono-code font-black text-[#121316]">
                                                                    <span>{{ $emoji }}</span> <span>{{ $count }}</span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <button type="button" 
                                                            data-reply-id="{{ $reply->id }}"
                                                            data-user-name="{{ $child->user->name }}"
                                                            data-snippet="{{ Str::limit(strip_tags($child->content), 30) }}"
                                                            onclick="quoteReplyFromBtn(this)" 
                                                            class="text-[11px] font-mono-code font-black text-[#ff3823] hover:underline">
                                                        Balas
                                                    </button>
                                                </div>
                                            </div>

                                            <div x-show="editingChild" class="bg-white border-2 border-[#121316] rounded-xl p-3 max-w-xl shadow-[2px_2px_0px_#121316]">
                                                <form action="{{ route('forum.reply.update', $child) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <textarea name="content" rows="2" class="w-full p-2 bg-[#faf8f5] border border-[#121316] rounded-lg text-xs font-bold text-[#121316] outline-none" required>{{ $child->content }}</textarea>
                                                    <div class="flex items-center justify-end gap-1.5 mt-2">
                                                        <button type="button" @click="editingChild = false" class="btn-tactile-white px-2.5 py-1 text-[10px] font-black">Batal</button>
                                                        <button type="submit" class="btn-tactile-red px-3 py-1 text-[10px] font-black">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- STICKY BOTTOM REPLY COMPOSER -->
    @if(!$thread->is_locked)
    <div class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-md border-t-2 border-[#121316] p-3 sm:p-4 z-50 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]" id="compose-bar">
        <!-- Reply Context Banner -->
        <div class="max-w-5xl mx-auto mb-2">
            <div id="quote-preview" class="hidden p-2.5 bg-[#fde047] border-2 border-[#121316] rounded-xl flex items-center justify-between gap-3 shadow-[2px_2px_0px_#121316]">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-lg bg-[#121316] text-[#fde047] flex items-center justify-center text-sm flex-shrink-0">
                        <i class="fa-solid fa-reply"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-mono-code font-black uppercase px-2 py-0.5 rounded border border-slate-900" style="background-color: #0f172a !important; color: #fde047 !important;">MODE BALAS</span>
                            <div class="text-xs font-black text-[#121316] truncate" id="quote-user">Membalas @Pengguna</div>
                        </div>
                        <div class="text-xs text-[#121316]/80 truncate mt-0.5 italic font-bold" id="quote-text">...</div>
                    </div>
                </div>
                <button type="button" onclick="cancelQuote()" class="btn-tactile-white px-2.5 py-1 text-xs font-black flex items-center gap-1 flex-shrink-0">
                    <i class="fa-solid fa-xmark text-rose-600"></i> <span>Batal</span>
                </button>
            </div>
        </div>

        <form action="{{ route('forum.reply', $thread) }}" method="POST" enctype="multipart/form-data" class="flex gap-2.5 items-end max-w-5xl mx-auto" id="reply-form">
            @csrf
            <input type="hidden" name="parent_reply_id" id="parent_reply_id">
            <input type="file" name="voice_note" id="voice_note_input" style="display:none;" accept="audio/*">
            
            <!-- Emoji Picker Trigger -->
            <div class="relative flex-shrink-0 mb-0.5">
                <button type="button" onclick="toggleComposeEmojiVanilla(event)" class="compose-emoji-trigger btn-tactile-white w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center text-[#121316]">
                    <i class="fa-regular fa-face-smile text-xl"></i>
                </button>
                <div id="compose-emoji-picker" class="compose-emoji-picker" style="display: none;">
                    <button type="button" onclick="insertEmojiVanilla('😀')" class="compose-emoji-btn"><span>😀</span></button>
                    <button type="button" onclick="insertEmojiVanilla('😂')" class="compose-emoji-btn"><span>😂</span></button>
                    <button type="button" onclick="insertEmojiVanilla('😍')" class="compose-emoji-btn"><span>😍</span></button>
                    <button type="button" onclick="insertEmojiVanilla('👍')" class="compose-emoji-btn"><span>👍</span></button>
                    <button type="button" onclick="insertEmojiVanilla('🔥')" class="compose-emoji-btn"><span>🔥</span></button>
                    <button type="button" onclick="insertEmojiVanilla('👏')" class="compose-emoji-btn"><span>👏</span></button>
                    <button type="button" onclick="insertEmojiVanilla('❤️')" class="compose-emoji-btn"><span>❤️</span></button>
                    <button type="button" onclick="insertEmojiVanilla('💡')" class="compose-emoji-btn"><span>💡</span></button>
                    <button type="button" onclick="insertEmojiVanilla('🤔')" class="compose-emoji-btn"><span>🤔</span></button>
                    <button type="button" onclick="insertEmojiVanilla('🎉')" class="compose-emoji-btn"><span>🎉</span></button>
                    <button type="button" onclick="insertEmojiVanilla('🙏')" class="compose-emoji-btn"><span>🙏</span></button>
                    <button type="button" onclick="insertEmojiVanilla('✨')" class="compose-emoji-btn"><span>✨</span></button>
                </div>
            </div>

            <!-- Input Textarea & Voice Note Preview -->
            <div class="flex-1 bg-[#faf8f5] border-2 border-[#121316] rounded-xl overflow-hidden focus-within:bg-white shadow-[2px_2px_0px_#121316] flex flex-col justify-center min-h-[44px]">
                <div id="vn-preview" class="w-full bg-transparent text-[#121316] px-4 py-2 flex items-center gap-3" style="display: none;">
                    <i class="fa-solid fa-microphone text-[#ff3823] text-lg"></i>
                    <audio id="vn-audio" controls class="h-8 flex-1 max-w-[200px] sm:max-w-[300px]"></audio>
                    <button type="button" onclick="cancelVoiceNote()" class="p-1.5 bg-rose-100 text-rose-700 rounded-lg border border-rose-300 ml-auto" title="Hapus rekaman">
                        <i class="fa-solid fa-trash text-base"></i>
                    </button>
                </div>

                <div class="flex items-end">
                    <textarea name="content" rows="1" placeholder="Tulis komentar baru untuk postingan ini..." class="w-full bg-transparent text-[#121316] placeholder-[#777] font-bold px-3.5 py-2.5 outline-none resize-none min-h-[44px] max-h-32 text-xs sm:text-sm no-scrollbar" id="reply-textarea" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                    
                    <!-- Mic Button -->
                    <button type="button" id="record-btn" onclick="toggleRecord()" class="p-2.5 text-[#555] hover:text-[#ff3823] transition flex-shrink-0 mb-0.5 mr-0.5 rounded-lg flex items-center justify-center" title="Merekam voice note">
                        <i class="fa-solid fa-microphone text-lg"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-tactile-red w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center flex-shrink-0 mb-0.5" title="Kirim Komentar">
                <i class="fa-solid fa-paper-plane text-lg"></i>
            </button>
        </form>
    </div>
    @else
    <div class="fixed bottom-0 left-0 w-full bg-[#fde047] border-t-2 border-[#121316] p-4 z-50">
        <div class="max-w-[1200px] mx-auto text-center text-[#121316] font-black text-xs sm:text-sm font-mono-code">
            <i class="fa-solid fa-lock mr-2"></i> Topik ini telah dikunci.
        </div>
    </div>
    @endif
</div>

<script>
function forumChat() {
    return {
        pickerOpen: null,
        togglePicker(id) {
            this.pickerOpen = this.pickerOpen === id ? null : id;
        },
        reactThread(emoji) {
            reactThreadAjax(emoji);
            this.pickerOpen = null;
        },
        reactReply(replyId, emoji) {
            reactReplyAjax(replyId, emoji);
            this.pickerOpen = null;
        }
    }
}

// Vanilla JS Emoji Picker & Insertion
function toggleComposeEmojiVanilla(event) {
    event.stopPropagation();
    const picker = document.getElementById('compose-emoji-picker');
    if (!picker) return;
    if (picker.style.display === 'none') {
        picker.style.display = 'grid';
    } else {
        picker.style.display = 'none';
    }
}

let lastInsertTimeVanilla = 0;
function insertEmojiVanilla(emoji) {
    const now = Date.now();
    if (now - lastInsertTimeVanilla < 150) return;
    lastInsertTimeVanilla = now;

    const textarea = document.querySelector('textarea[name="content"]');
    if (textarea) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + emoji + text.substring(end);
        textarea.focus();
        textarea.style.height = '';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    const picker = document.getElementById('compose-emoji-picker');
    if (picker) picker.style.display = 'none';
}

// Close picker when clicking anywhere outside
document.addEventListener('click', function(event) {
    const picker = document.getElementById('compose-emoji-picker');
    if (!picker || picker.style.display === 'none') return;
    
    const trigger = event.target.closest('.compose-emoji-trigger');
    const insidePicker = event.target.closest('#compose-emoji-picker');
    
    if (!trigger && !insidePicker) {
        picker.style.display = 'none';
    }
});

// Quote functionality
function quoteReplyFromBtn(btn) {
    if (!btn) return;
    const id = btn.getAttribute('data-reply-id');
    const user = btn.getAttribute('data-user-name');
    const text = btn.getAttribute('data-snippet');
    quoteReply(id, user, text);
}

function quoteReply(id, user, text) {
    const parentInput = document.getElementById('parent_reply_id');
    const quoteUser = document.getElementById('quote-user');
    const quoteText = document.getElementById('quote-text');
    const quotePreview = document.getElementById('quote-preview');
    const textarea = document.getElementById('reply-textarea');

    if (parentInput) parentInput.value = id;
    if (quoteUser) quoteUser.textContent = 'Membalas @' + user;
    if (quoteText) quoteText.textContent = text ? '"' + text + '"' : 'Pesan';
    
    if (quotePreview) quotePreview.classList.remove('hidden');
    
    if (textarea) {
        textarea.placeholder = 'Tulis balasan untuk @' + user + '...';
        textarea.focus();
    }

    document.getElementById('compose-bar')?.scrollIntoView({ behavior: 'smooth', block: 'end' });
}

function cancelQuote() {
    document.getElementById('parent_reply_id').value = '';
    document.getElementById('quote-preview')?.classList.add('hidden');
    
    const textarea = document.getElementById('reply-textarea');
    if (textarea) {
        textarea.placeholder = 'Tulis komentar baru untuk postingan ini...';
    }
}

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
let isReacting = false;

// AJAX Reactions
async function reactThreadAjax(emoji) {
    if (isReacting) return;
    isReacting = true;
    try {
        const res = await fetch("{{ route('forum.react', $thread) }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ emoji: emoji })
        });
        if (!res.ok) {
            const text = await res.text();
            alert("React Thread Server Error (" + res.status + "): " + text.substring(0, 500));
            return;
        }
        const data = await res.json();
        if (data.success) {
            updateReactionUI('thread-reactions', data.counts, true);
        }
    } catch (e) { console.error("React Thread Error:", e); }
    finally { isReacting = false; }
}

async function reactReplyAjax(replyId, emoji) {
    if (isReacting) return;
    isReacting = true;
    try {
        const res = await fetch(`{{ url('/forum/reply') }}/${replyId}/react`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ emoji: emoji })
        });
        if (!res.ok) {
            const text = await res.text();
            alert("React Reply Server Error (" + res.status + "): " + text.substring(0, 500));
            return;
        }
        const data = await res.json();
        if (data.success) {
            updateReactionUI(`reply-reactions-${replyId}`, data.counts, false, replyId);
        }
    } catch (e) { console.error("React Reply Error:", e); }
    finally { isReacting = false; }
}

function updateReactionUI(containerId, counts, isThread, replyId = null) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    for (const [em, count] of Object.entries(counts)) {
        if (count > 0) {
            const btn = document.createElement('button');
            const clickFn = isThread ? `reactThreadAjax('${em}')` : `reactReplyAjax(${replyId}, '${em}')`;
            btn.setAttribute('onclick', clickFn);
            btn.className = isThread 
                ? 'flex items-center gap-1.5 px-2.5 py-1 bg-white hover:bg-[#faf8f5] border-2 border-[#121316] rounded-lg text-xs font-mono-code font-black text-[#121316] transition shadow-[1px_1px_0px_#121316]'
                : 'flex items-center gap-1 px-2 py-0.5 bg-white border border-[#121316] rounded-md text-[10px] font-mono-code font-black text-[#121316] transition shadow-[1px_1px_0px_#121316]';
            btn.innerHTML = `<span>${em}</span> <span>${count}</span>`;
            container.appendChild(btn);
        }
    }
}

// AJAX Poll
async function votePoll(optionId) {
    try {
        const res = await fetch(`{{ url('/forum/poll') }}/${optionId}/vote`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() }
        });
        if (!res.ok) {
            const text = await res.text();
            alert("Poll Server Error (" + res.status + "): " + text.substring(0, 500));
            return;
        }
        const data = await res.json();
        if (data.success) {
            const totalEl = document.getElementById('poll-total-votes');
            if (totalEl) totalEl.textContent = data.total_votes;
            
            data.options.forEach((opt, idx) => {
                const pctEl = document.getElementById(`poll-pct-${opt.id}`);
                const countEl = document.getElementById(`poll-count-${opt.id}`);
                const bgEl = document.getElementById(`poll-bg-${opt.id}`);
                const btnEl = document.getElementById(`poll-btn-${opt.id}`);
                const checkEl = document.getElementById(`poll-check-${opt.id}`);

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
                        checkEl.innerHTML = '<i class="fa-solid fa-check"></i>';
                    } else {
                        checkEl.className = 'w-5 h-5 rounded-full flex items-center justify-center border border-[#121316] text-[10px] font-mono-code font-black flex-shrink-0 bg-white text-[#121316]';
                        checkEl.textContent = (idx + 1);
                    }
                }
            });
        }
    } catch (e) { console.error("Poll Error:", e); }
}

// Voice Note Logic
let mediaRecorder;
let audioChunks = [];
let audioBlob;
let recordTimer;
let recordSeconds = 0;
let isRecording = false;

async function toggleRecord() {
    const recordBtn = document.getElementById('record-btn');
    const textarea = document.getElementById('reply-textarea');
    const vnPreview = document.getElementById('vn-preview');
    const vnAudio = document.getElementById('vn-audio');
    const fileInput = document.getElementById('voice_note_input');

    if (isRecording) {
        stopRecording();
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        
        mediaRecorder.ondataavailable = e => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };
        
        mediaRecorder.onstop = () => {
            audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            const audioUrl = URL.createObjectURL(audioBlob);
            vnAudio.src = audioUrl;
            
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([audioBlob], 'voicenote.webm', { type: 'audio/webm' }));
            fileInput.files = dataTransfer.files;
            
            textarea.style.display = 'none';
            vnPreview.style.display = 'flex';
            
            stream.getTracks().forEach(track => track.stop());
            
            recordBtn.innerHTML = '<i class="fa-solid fa-microphone text-lg"></i>';
            recordBtn.classList.remove('text-[#ff3823]', 'animate-pulse');
            recordBtn.style.display = 'none';
        };
        
        mediaRecorder.start();
        isRecording = true;
        recordSeconds = 0;
        
        recordBtn.innerHTML = '<i class="fa-solid fa-stop text-lg text-[#ff3823]"></i>';
        recordBtn.classList.add('text-[#ff3823]', 'animate-pulse');
        textarea.placeholder = "Merekam... (Maks 15 detik)";
        textarea.disabled = true;
        
        recordTimer = setInterval(() => {
            recordSeconds++;
            textarea.placeholder = `Merekam... 00:${recordSeconds.toString().padStart(2, '0')}`;
            if (recordSeconds >= 15) {
                stopRecording();
            }
        }, 1000);
        
    } catch (err) {
        alert("Tidak dapat mengakses mikrofon. Pastikan Anda telah memberikan izin di browser.");
    }
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        isRecording = false;
        clearInterval(recordTimer);
        const textarea = document.getElementById('reply-textarea');
        textarea.placeholder = "Tulis komentar baru untuk postingan ini...";
        textarea.disabled = false;
    }
}

function cancelVoiceNote() {
    const fileInput = document.getElementById('voice_note_input');
    const vnPreview = document.getElementById('vn-preview');
    const textarea = document.getElementById('reply-textarea');
    const recordBtn = document.getElementById('record-btn');
    
    fileInput.value = '';
    vnPreview.style.display = 'none';
    textarea.style.display = 'block';
    textarea.value = '';
    recordBtn.style.display = 'flex';
}
</script>
@endsection