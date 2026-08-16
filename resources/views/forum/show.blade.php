@extends(auth()->user()->layout)

@section('title', $thread->title)

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

    /* Compose Emoji Picker Custom CSS */
    .compose-emoji-picker {
        position: absolute !important;
        bottom: 100% !important;
        left: 0 !important;
        margin-bottom: 8px !important;
        padding: 10px !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 16px !important;
        box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.12) !important;
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        gap: 4px !important;
        z-index: 50 !important;
        width: 224px !important;
    }
    .compose-emoji-btn {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        transition: transform 0.2s !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        color: #1e293b !important;
    }
    .compose-emoji-btn:hover {
        background-color: #f1f5f9 !important;
        transform: scale(1.25) !important;
    }
</style>

<!-- App Window Wrapper -->
<div class="w-full bg-forum-base text-forum-title font-['Inter'] rounded-3xl border border-forum mx-auto flex flex-col pt-4 px-4 sm:px-6 relative shadow-sm" style="min-height: 85vh; padding-bottom: 240px !important; background-image: radial-gradient(rgba(220,38,38,0.03) 2px, transparent 2px); background-size: 24px 24px;" x-data="forumChat()">
    
    <!-- Top Nav Bar -->
    <div class="flex items-center justify-between bg-white/95 backdrop-blur-xl p-4 rounded-2xl border border-slate-200 mb-6 sticky top-4 z-40 shadow-sm">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('forum.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 transition flex-shrink-0">
                <i class="ph-bold ph-arrow-left text-xl"></i>
            </a>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-red-600 uppercase tracking-wider">{{ $thread->category_label }}</div>
                <h1 class="forum-hdr text-base sm:text-lg font-bold text-slate-800 line-clamp-1">{{ $thread->title }}</h1>
            </div>
        </div>
        
        <div class="flex items-center gap-2 flex-shrink-0">
            @if(auth()->id() === $thread->user_id || auth()->user()->isSuperAdmin())
                <a href="{{ route('forum.edit', $thread) }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-700 flex items-center justify-center text-slate-600 transition" title="Edit">
                    <i class="ph-bold ph-pencil-simple text-lg"></i>
                </a>
                <form action="{{ route('forum.destroy', $thread) }}" method="POST" onsubmit="return confirm('Yakin hapus postingan ini?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-rose-100 hover:text-rose-600 flex items-center justify-center text-slate-600 transition" title="Hapus">
                        <i class="ph-bold ph-trash text-lg"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3">
        <i class="ph-bold ph-check-circle text-xl"></i> <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-3">
        <i class="ph-bold ph-x-circle text-xl"></i> <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Small HUT KEMRI Banner -->
    <div class="mb-6 w-full max-w-5xl mx-auto rounded-2xl overflow-hidden shadow-sm border border-red-200 relative bg-gradient-to-r from-red-600 to-red-700 p-4 flex items-center justify-between">
        <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 2px, transparent 2px, transparent 10px);"></div>
        <div class="relative flex items-center gap-3">
            <span class="text-3xl">🇮🇩</span>
            <div>
                <h3 class="text-white font-bold text-sm">Semarak Kemerdekaan RI ke-81</h3>
                <p class="text-red-100 text-xs font-medium hidden sm:block">Perguruan Pembda Nias Jaya Berprestasi dan Berkarya untuk Negeri</p>
            </div>
        </div>
        <div class="relative text-white font-black text-2xl italic tracking-tighter opacity-50 pr-4">81 TH</div>
    </div>

    <!-- CHAT AREA -->
    <div class="space-y-6 flex-1 flex flex-col max-w-5xl mx-auto w-full">
        
        <!-- ORIGINAL POST (First Message) -->
        <div class="flex gap-4">
            <img src="{{ $thread->user->avatar_url }}" 
                 class="w-10 h-10 sm:w-12 sm:h-12 rounded-full border border-slate-200 shadow-sm flex-shrink-0 object-cover">
            <div class="flex-1 min-w-0 space-y-2">
                <!-- Meta -->
                <div class="flex items-baseline gap-2 flex-wrap">
                    <span class="font-bold text-slate-800 text-sm sm:text-base">{{ $thread->user->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-bold uppercase tracking-wider">{{ $thread->user->role }}</span>
                    <span class="text-xs text-slate-400">&bull; {{ $thread->created_at->format('H:i • d M Y') }}</span>
                </div>
                
                <!-- Bubble -->
                <div class="bg-white border border-red-100 rounded-2xl rounded-tl-none p-5 sm:p-6 shadow-sm shadow-red-100 w-full max-w-3xl relative">
                    <!-- Subtle red accent line at the top -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-600 to-red-500 rounded-tr-2xl"></div>
                    <h2 class="forum-hdr text-xl sm:text-2xl font-bold text-slate-800 mb-4">{{ $thread->title }}</h2>
                    <div class="prose prose-slate prose-sm sm:prose-base max-w-none text-slate-700 leading-relaxed">
                        {!! nl2br(e($thread->content)) !!}
                    </div>

                    @if($thread->image_path)
                        <div class="mt-4 rounded-xl overflow-hidden border border-slate-200 max-w-lg shadow-sm">
                            <img src="{{ asset('storage/' . $thread->image_path) }}" class="w-full h-auto">
                        </div>
                    @endif

                    @if($thread->attachment_path)
                        <a href="{{ asset('storage/' . $thread->attachment_path) }}" download class="mt-4 flex items-center gap-3 p-3 bg-slate-50 hover:bg-red-50 border border-slate-200 hover:border-red-200 rounded-xl transition max-w-sm">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                                <i class="ph-bold ph-file-arrow-down text-xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-slate-700 truncate">{{ $thread->attachment_name ?? 'Download Lampiran' }}</div>
                                <div class="text-[10px] text-slate-400">Klik untuk mengunduh</div>
                            </div>
                        </a>
                    @endif
                    
                    @if($thread->category === 'gaming' && ($thread->game_name || $thread->game_room_code))
                        <div class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 text-2xl flex-shrink-0">
                                    <i class="ph-bold ph-game-controller"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Lobi Mabar {{ $thread->game_name }}</div>
                                    <div class="text-sm font-bold text-slate-800">Kode / ID Room: <span class="text-rose-700 select-all">{{ $thread->game_room_code ?: 'Tidak ada kode' }}</span></div>
                                </div>
                            </div>
                            @if($thread->game_room_code)
                            <button onclick="navigator.clipboard.writeText('{{ addslashes($thread->game_room_code) }}'); alert('Kode Room / ID berhasil disalin!');" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                <i class="ph-bold ph-copy"></i> Salin ID
                            </button>
                            @endif
                        </div>
                    @endif

                    @if($thread->category === 'sharing' && $thread->file_category)
                        <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 text-lg flex-shrink-0">
                                <i class="ph-bold ph-folder-open"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Kategori Berkas Belajar</div>
                                <div class="text-xs font-bold text-slate-800">{{ $thread->file_category }}</div>
                            </div>
                        </div>
                    @endif

                    @if($perfCard)
                        <div class="mt-4 p-4 rounded-xl bg-purple-50 border border-purple-200 flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 flex-shrink-0">
                                <i class="ph-bold ph-medal text-2xl"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Tautan Prestasi</div>
                                @if($thread->reference_type === \App\Models\Badge::class)
                                    <div class="text-sm font-bold text-slate-800">{{ $perfCard->name }}</div>
                                    <div class="text-xs text-slate-600">{{ $perfCard->description }}</div>
                                @else
                                    <div class="text-sm font-bold text-slate-800">CBT: {{ $perfCard->exam->title ?? 'Ujian' }}</div>
                                    <div class="text-xs text-slate-600">Nilai: {{ $perfCard->final_score }}</div>
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
                        <div class="mt-4 p-4.5 bg-gradient-to-br from-indigo-50/90 via-purple-50/40 to-white border border-indigo-100 rounded-2xl shadow-sm space-y-3.5" id="thread-detail-poll">
                            <div class="flex items-center justify-between gap-2 border-b border-indigo-100/80 pb-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        <i class="ph-bold ph-chart-bar"></i>
                                    </span>
                                    <span class="text-[10px] font-black tracking-wider uppercase text-indigo-700 bg-indigo-100/80 px-2 py-0.5 rounded-md">📊 Polling Interaktif</span>
                                </div>
                                <span class="text-xs font-bold text-slate-500 flex items-center gap-1">
                                    <i class="ph-bold ph-users text-indigo-500"></i>
                                    <span id="poll-total-votes">{{ $totalVotes }}</span> Suara
                                </span>
                            </div>

                            <h3 class="text-sm font-black text-slate-900 leading-snug">
                                {{ $poll->question }}
                            </h3>

                            <div class="space-y-2.5 pt-1" id="poll-options-container">
                                @foreach($poll->options as $option)
                                    @php 
                                        $pct = $totalVotes > 0 ? round(($option->votes_count / $totalVotes) * 100) : 0;
                                        $hasVoted = $userVotedOptionId === $option->id;
                                    @endphp
                                    <button type="button" onclick="votePoll({{ $option->id }})" class="w-full relative overflow-hidden rounded-xl border text-left p-3 transition-all duration-300 group cursor-pointer {{ $hasVoted ? 'border-indigo-500 bg-indigo-50/90 ring-2 ring-indigo-400/30' : 'border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50' }}" id="poll-btn-{{ $option->id }}">
                                        <!-- Progress Bar -->
                                        <div class="absolute top-0 left-0 h-full {{ $hasVoted ? 'bg-indigo-200/60' : 'bg-indigo-100/50' }} transition-all duration-700 pointer-events-none" style="width: {{ $pct }}%" id="poll-bg-{{ $option->id }}"></div>
                                        
                                        <div class="relative z-10 flex justify-between items-center text-xs font-bold">
                                            <div class="flex items-center gap-2.5 {{ $hasVoted ? 'text-indigo-900' : 'text-slate-800' }}">
                                                <span class="w-5 h-5 rounded-full flex items-center justify-center border text-[11px] flex-shrink-0 {{ $hasVoted ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 group-hover:border-indigo-400 bg-white text-slate-500' }}" id="poll-check-{{ $option->id }}">
                                                    @if($hasVoted)
                                                        <i class="ph-bold ph-check"></i>
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </span>
                                                <span class="truncate">{{ $option->option_text }}</span>
                                            </div>
                                            <div class="font-extrabold flex items-center gap-1.5 flex-shrink-0 {{ $hasVoted ? 'text-indigo-700' : 'text-slate-600' }}">
                                                <span id="poll-pct-{{ $option->id }}">{{ $pct }}%</span>
                                                <span class="text-[10px] font-semibold text-slate-400" id="poll-count-{{ $option->id }}">({{ $option->votes_count }})</span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            
                            @if(!$poll->isOpen())
                                <div class="text-[11px] font-bold text-amber-600 flex items-center gap-1 pt-1">
                                    <i class="ph-bold ph-lock-key"></i> Polling ini telah ditutup
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Thread Reactions & Actions -->
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <!-- Picker Button -->
                    <div class="flex items-center relative">
                        <button @click="togglePicker('thread')" class="w-8 h-8 rounded-full bg-white hover:bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 transition shadow-sm">
                            <i class="ph-bold ph-smiley"></i>
                        </button>
                        <!-- Picker Dropdown (Inline) -->
                        <div x-show="pickerOpen === 'thread'" class="ml-2 p-1 bg-white border border-slate-200 rounded-xl flex gap-1 shadow-lg z-20">
                            @foreach(\App\Models\ForumReaction::EMOJIS as $emoji => $name)
                                <button @click="reactThread('{{ $emoji }}')" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-lg transition-transform hover:scale-125">
                                    {{ $emoji }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Existing Reactions -->
                    <div id="thread-reactions" class="flex flex-wrap gap-2">
                        @foreach($threadReactions as $emoji => $count)
                            <button onclick="reactThreadAjax('{{ $emoji }}')" class="flex items-center gap-1.5 px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 transition shadow-sm">
                                <span>{{ $emoji }}</span> <span>{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- COLLAB PANEL (if active) -->
        @if(in_array($thread->category, ['project_idea', 'committee']) && $thread->status !== 'completed')
            <div class="max-w-3xl ml-14 sm:ml-16 bg-white border border-rose-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-rose-500"></div>
                <h3 class="forum-hdr text-sm font-bold text-slate-800 flex items-center gap-2 mb-3">
                    <i class="ph-bold ph-handshake text-rose-600"></i> Rekrutmen Tim
                </h3>
                @if(auth()->id() !== $thread->user_id && $thread->status === 'seeking_members')
                    @php $hasApplied = $thread->members()->where('user_id', auth()->id())->exists(); @endphp
                    @if(!$hasApplied)
                        <form action="{{ route('forum.join', $thread) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="notes" placeholder="Pesan singkat (opsional)..." class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-rose-500 outline-none">
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-lg transition shadow-sm">Gabung</button>
                        </form>
                    @else
                        <div class="text-sm font-bold text-rose-600">Kamu sudah mendaftar. Menunggu persetujuan.</div>
                    @endif
                @endif
            </div>
        @endif

        <!-- REPLIES DIVIDER -->
        @if($thread->replies->count() > 0)
            <div class="flex items-center gap-4 my-4 max-w-3xl ml-14 sm:ml-16">
                <div class="h-px flex-1 bg-slate-200"></div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $thread->replies->count() }} Balasan</span>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>
        @endif

        <!-- REPLIES LIST -->
        @php
            // Type-safe grouping of top-level and child replies
            $topLevelReplies = $thread->replies->filter(function($r) {
                return empty($r->parent_reply_id) || (int)$r->parent_reply_id === 0;
            });

            $allTopLevelIds = $topLevelReplies->pluck('id')->map(fn($id) => (int)$id)->toArray();
            $allRepliesMap = [];
            foreach ($thread->replies as $r) {
                $allRepliesMap[(int)$r->id] = !empty($r->parent_reply_id) ? (int)$r->parent_reply_id : null;
            }
        @endphp

        <div id="replies" class="space-y-6 pb-52 sm:pb-60">
            @foreach($topLevelReplies as $reply)
                <!-- Top-Level Comment Card -->
                <div class="flex gap-3 sm:gap-4" id="reply-{{ $reply->id }}" x-data="{ editing: false }">
                    <img src="{{ $reply->user->avatar_url }}" 
                         class="w-10 h-10 rounded-full border border-slate-200 shadow-xs flex-shrink-0 object-cover">
                    <div class="flex-1 min-w-0 space-y-2">
                        <!-- User Info & Edit/Delete -->
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-baseline gap-2 flex-wrap min-w-0">
                                <span class="font-bold text-slate-800 text-sm">{{ $reply->user->name }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-bold uppercase tracking-wider">{{ $reply->user->role }}</span>
                                <span class="text-xs text-slate-400">{{ $reply->created_at->format('H:i') }}</span>
                                @if($reply->is_accepted)
                                    <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded flex items-center gap-1 font-bold">
                                        <i class="ph-bold ph-star text-amber-600"></i> Jawaban Terbaik
                                    </span>
                                @endif
                            </div>

                            <!-- Edit / Delete for Comment Author or Super Admin -->
                            @if(auth()->id() === $reply->user_id || auth()->user()->isSuperAdmin())
                                <div class="flex items-center gap-1 ml-auto">
                                    <button type="button" @click="editing = !editing" class="px-2 py-0.5 text-slate-400 hover:text-indigo-600 rounded-md hover:bg-slate-100 transition text-xs font-bold flex items-center gap-1" title="Edit Komentar">
                                        <i class="ph-bold ph-pencil-simple"></i> <span>Edit</span>
                                    </button>
                                    <form action="{{ route('forum.reply.destroy', $reply) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-0.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition text-xs font-bold flex items-center gap-1" title="Hapus Komentar">
                                            <i class="ph-bold ph-trash"></i> <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <!-- Normal Bubble (when NOT editing) -->
                        <div x-show="!editing">
                            <div class="{{ $reply->is_accepted ? 'bg-amber-50/80 border-amber-200 ring-1 ring-amber-300' : 'bg-white border-slate-200' }} border rounded-2xl rounded-tl-none p-4 max-w-2xl text-sm text-slate-800 shadow-sm">
                                @if($reply->voice_note_path)
                                    <div class="mb-2 flex items-center gap-2 text-indigo-600 font-bold text-xs">
                                        <i class="ph-bold ph-microphone text-lg"></i> Pesan Suara
                                    </div>
                                    <audio controls class="w-full h-10 rounded-xl max-w-[250px] sm:max-w-xs mb-2 bg-slate-100" src="{{ asset('storage/' . $reply->voice_note_path) }}"></audio>
                                @endif
                                @if($reply->content)
                                    {!! nl2br(e($reply->content)) !!}
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center justify-between gap-2 mt-2 w-full max-w-2xl">
                                <!-- Left: Smiley & Reactions -->
                                <div class="flex items-center gap-2">
                                    <!-- Reply Picker -->
                                    <div class="flex items-center relative">
                                        <button @click="togglePicker('reply-{{ $reply->id }}')" class="w-6 h-6 rounded-full hover:bg-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-800 transition">
                                            <i class="ph-bold ph-smiley"></i>
                                        </button>
                                        <!-- Picker Dropdown (Inline) -->
                                        <div x-show="pickerOpen === 'reply-{{ $reply->id }}'" class="ml-2 p-1 bg-white border border-slate-200 rounded-xl flex gap-1 shadow-lg z-20">
                                            @foreach(\App\Models\ForumReaction::EMOJIS as $emoji => $name)
                                                <button @click="reactReply('{{ $reply->id }}', '{{ $emoji }}')" class="w-6 h-6 rounded hover:bg-slate-100 flex items-center justify-center text-base transition-transform hover:scale-125">
                                                    {{ $emoji }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Reactions -->
                                    <div id="reply-reactions-{{ $reply->id }}" class="flex flex-wrap gap-1">
                                        @foreach($reply->getReactionCounts() as $emoji => $count)
                                            <button onclick="reactReplyAjax({{ $reply->id }}, '{{ $emoji }}')" class="flex items-center gap-1 px-1.5 py-0.5 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[10px] font-bold text-slate-700 transition shadow-sm">
                                                <span>{{ $emoji }}</span> <span>{{ $count }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Right: Balas & Terbaik -->
                                <div class="flex items-center gap-3 ml-auto">
                                    <!-- Reply Button with Data Attributes -->
                                    <button type="button" 
                                            data-reply-id="{{ $reply->id }}" 
                                            data-user-name="{{ e($reply->user->name) }}" 
                                            data-snippet="{{ e(Str::limit(preg_replace('/\s+/', ' ', strip_tags($reply->content ?? ($reply->voice_note_path ? 'Pesan Suara' : ''))), 80)) }}" 
                                            onclick="quoteReplyFromBtn(this)" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50/80 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200/70 transition shadow-xs">
                                        <i class="ph-bold ph-arrow-bend-up-left"></i> Balas
                                    </button>

                                    <!-- Accept Answer -->
                                    @if(!$reply->is_accepted && !$thread->replies->contains('is_accepted', true) && (auth()->id() === $thread->user_id || auth()->user()->isSuperAdmin()))
                                        <form action="{{ route('forum.reply.accept', $reply) }}" method="POST" class="inline-flex items-center">
                                            @csrf
                                            <button type="submit" class="text-[10px] font-bold text-slate-400 hover:text-amber-600 transition">
                                                TERBAIK
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Inline Edit Form (when editing == true) -->
                        <div x-show="editing" x-cloak class="max-w-2xl bg-slate-50 border border-indigo-200 rounded-2xl p-4 space-y-2.5 shadow-sm">
                            <form action="{{ route('forum.reply.update', $reply) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-xs font-bold text-indigo-950 flex items-center gap-1">
                                        <i class="ph-bold ph-pencil-simple text-indigo-600"></i> Edit Komentar
                                    </label>
                                    <button type="button" @click="editing = false" class="text-slate-400 hover:text-slate-600 text-xs">✕ Tutup</button>
                                </div>
                                <textarea name="content" rows="3" class="w-full text-sm p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white resize-y shadow-2xs">{{ $reply->content }}</textarea>
                                <div class="flex items-center justify-end gap-2 mt-2">
                                    <button type="button" @click="editing = false" class="px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-200 rounded-lg transition">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition flex items-center gap-1">
                                        <i class="ph-bold ph-check"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- NESTED / MENJOROK KE DALAM: Balasan untuk komentar ini -->
                        @php
                            $childReplies = $thread->replies->filter(function($r) use ($reply, $allRepliesMap) {
                                if (empty($r->parent_reply_id)) return false;
                                $pId = (int)$r->parent_reply_id;
                                if ($pId === (int)$reply->id) return true;
                                if (isset($allRepliesMap[$pId]) && $allRepliesMap[$pId] === (int)$reply->id) {
                                    return true;
                                }
                                return false;
                            });
                        @endphp

                        @if($childReplies->count() > 0)
                            <div class="mt-4 space-y-3 pl-4 sm:pl-8 border-l-2 border-indigo-300">
                                @foreach($childReplies as $child)
                                    <div class="relative" id="reply-{{ $child->id }}" x-data="{ editingChild: false }">
                                        <div class="bg-indigo-50/70 hover:bg-indigo-50/90 border border-indigo-200/80 rounded-2xl p-4 shadow-xs transition space-y-2 max-w-xl">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <img src="{{ $child->user->avatar_url }}" class="w-8 h-8 rounded-full border border-indigo-300 shadow-2xs flex-shrink-0 object-cover">
                                                    <div class="min-w-0 flex items-baseline gap-1.5 flex-wrap">
                                                        <span class="font-black text-slate-800 text-xs sm:text-sm truncate">{{ $child->user->name }}</span>
                                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-white text-slate-500 font-bold uppercase border border-slate-200">{{ $child->user->role }}</span>
                                                        <span class="text-[10px] text-slate-400 font-medium">{{ $child->created_at->format('H:i') }}</span>
                                                    </div>
                                                </div>

                                                <!-- Actions: Balas, Edit, Delete -->
                                                <div class="flex items-center gap-1.5 ml-auto">
                                                    <button type="button" 
                                                            data-reply-id="{{ $child->id }}" 
                                                            data-user-name="{{ e($child->user->name) }}" 
                                                            data-snippet="{{ e(Str::limit(preg_replace('/\s+/', ' ', strip_tags($child->content ?? ($child->voice_note_path ? 'Pesan Suara' : ''))), 80)) }}" 
                                                            onclick="quoteReplyFromBtn(this)" 
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-white hover:bg-indigo-100 border border-indigo-200 transition shadow-2xs">
                                                        <i class="ph-bold ph-arrow-bend-up-left"></i> Balas
                                                    </button>

                                                    @if(auth()->id() === $child->user_id || auth()->user()->isSuperAdmin())
                                                        <button type="button" @click="editingChild = !editingChild" class="p-1 text-slate-400 hover:text-indigo-600 rounded-md hover:bg-white transition text-xs font-bold" title="Edit Balasan">
                                                            <i class="ph-bold ph-pencil-simple"></i>
                                                        </button>
                                                        <form action="{{ route('forum.reply.destroy', $child) }}" method="POST" onsubmit="return confirm('Hapus balasan ini?');" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition text-xs font-bold" title="Hapus Balasan">
                                                                <i class="ph-bold ph-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Quoted Badge -->
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-indigo-100/90 border border-indigo-200 rounded-md text-[11px] text-indigo-800 font-bold shadow-2xs">
                                                <i class="ph-bold ph-arrow-bend-down-right text-indigo-600"></i>
                                                <span>Membalas <strong class="text-indigo-950">{{ $child->parent->user->name ?? $reply->user->name }}</strong></span>
                                            </div>

                                            <!-- Normal Child Bubble (when NOT editing) -->
                                            <div x-show="!editingChild" class="space-y-2">
                                                <div class="text-xs sm:text-sm text-slate-800 leading-relaxed bg-white p-3 rounded-xl border border-indigo-100/70 shadow-2xs">
                                                    @if($child->voice_note_path)
                                                        <div class="mb-1.5 flex items-center gap-1.5 text-indigo-600 font-bold text-xs">
                                                            <i class="ph-bold ph-microphone text-sm"></i> Pesan Suara
                                                        </div>
                                                        <audio controls class="w-full h-8 rounded-lg max-w-[220px] mb-1 bg-slate-50" src="{{ asset('storage/' . $child->voice_note_path) }}"></audio>
                                                    @endif
                                                    @if($child->content)
                                                        {!! nl2br(e($child->content)) !!}
                                                    @endif
                                                </div>

                                                <!-- Reactions -->
                                                <div class="flex items-center gap-2 pt-1">
                                                    <div class="flex items-center relative">
                                                        <button @click="togglePicker('reply-{{ $child->id }}')" class="w-5 h-5 rounded-full hover:bg-white flex items-center justify-center text-slate-400 hover:text-slate-700 transition text-xs">
                                                            <i class="ph-bold ph-smiley"></i>
                                                        </button>
                                                        <div x-show="pickerOpen === 'reply-{{ $child->id }}'" class="ml-1 p-1 bg-white border border-slate-200 rounded-xl flex gap-1 shadow-lg z-20">
                                                            @foreach(\App\Models\ForumReaction::EMOJIS as $emoji => $name)
                                                                <button @click="reactReply('{{ $child->id }}', '{{ $emoji }}')" class="w-5 h-5 rounded hover:bg-slate-100 flex items-center justify-center text-sm transition-transform hover:scale-125">
                                                                    {{ $emoji }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div id="reply-reactions-{{ $child->id }}" class="flex flex-wrap gap-1">
                                                        @foreach($child->getReactionCounts() as $emoji => $count)
                                                            <button onclick="reactReplyAjax({{ $child->id }}, '{{ $emoji }}')" class="flex items-center gap-1 px-1.5 py-0.5 bg-white hover:bg-slate-50 border border-indigo-100 rounded text-[10px] font-bold text-slate-700 transition shadow-2xs">
                                                                <span>{{ $emoji }}</span> <span>{{ $count }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Inline Edit Form for Child Reply -->
                                            <div x-show="editingChild" x-cloak class="bg-white border border-indigo-200 rounded-xl p-3 space-y-2 shadow-2xs">
                                                <form action="{{ route('forum.reply.update', $child) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="flex items-center justify-between mb-1">
                                                        <label class="text-[11px] font-bold text-indigo-950">Edit Balasan:</label>
                                                        <button type="button" @click="editingChild = false" class="text-slate-400 hover:text-slate-600 text-xs">✕ Tutup</button>
                                                    </div>
                                                    <textarea name="content" rows="2" class="w-full text-xs p-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-y">{{ $child->content }}</textarea>
                                                    <div class="flex items-center justify-end gap-2 mt-1.5">
                                                        <button type="button" @click="editingChild = false" class="px-2.5 py-1 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-md transition">
                                                            Batal
                                                        </button>
                                                        <button type="submit" class="px-3 py-1 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-xs transition">
                                                            Simpan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div> <!-- End Right Column -->
                </div> <!-- End Top-Level Card -->
            @endforeach

            <!-- Dedicated bottom clearance spacer so the last comment & all action buttons are never covered by the sticky bar -->
            <div style="height: 180px; width: 100%; pointer-events: none;" class="w-full flex-shrink-0" aria-hidden="true"></div>
        </div>
    </div>

<!-- STICKY COMPOSE BAR -->
@if(!$thread->is_locked)
<div class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-xl border-t border-slate-200 pb-safe z-50 transition-all duration-300 shadow-lg" id="compose-bar">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-3">
        <!-- Mode Label: Komentar Utama Baru (Default) -->
        <div id="general-comment-label" class="flex items-center justify-between gap-2 mb-2 max-w-5xl mx-auto px-1">
            <div class="inline-flex items-center gap-2 text-xs font-black text-slate-700 bg-slate-100/90 px-2.5 py-1 rounded-lg border border-slate-200/80">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <i class="ph-bold ph-chat-circle-text text-indigo-600 text-sm"></i>
                <span>Tulis Komentar Utama (Baru)</span>
            </div>
            <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">Komentar ini akan ditambahkan ke postingan ini</span>
        </div>

        <!-- Mode Label: Balas Komentar Spesifik (Aktif saat klik Balas) -->
        <div id="quote-preview" class="hidden mb-2.5 max-w-5xl mx-auto">
            <div class="bg-indigo-50 border-2 border-indigo-300 rounded-xl p-3 flex justify-between items-center gap-4 shadow-sm">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-base flex-shrink-0 shadow-xs">
                        <i class="ph-bold ph-arrow-bend-down-right"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-indigo-200 text-indigo-900">Mode Balas Komentar</span>
                            <div class="text-xs font-black text-indigo-950 truncate" id="quote-user">Membalas @Pengguna</div>
                        </div>
                        <div class="text-xs text-indigo-800/80 truncate mt-0.5 italic font-medium" id="quote-text">...</div>
                    </div>
                </div>
                <button type="button" onclick="cancelQuote()" class="px-3 py-1.5 bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-bold rounded-lg border border-slate-200 hover:border-rose-200 transition flex items-center gap-1.5 flex-shrink-0 shadow-xs" title="Batalkan balasan spesifik dan kembali ke komentar baru">
                    <i class="ph-bold ph-x font-black text-rose-500"></i> <span>Batal Balas</span>
                </button>
            </div>
        </div>

        <form action="{{ route('forum.reply', $thread) }}" method="POST" enctype="multipart/form-data" class="flex gap-3 items-end max-w-5xl mx-auto" id="reply-form">
            @csrf
            <input type="hidden" name="parent_reply_id" id="parent_reply_id">
            <input type="file" name="voice_note" id="voice_note_input" style="display:none;" accept="audio/*">
            
            <!-- Emoji Picker for Input (Vanilla JS) -->
            <div class="relative flex-shrink-0 mb-1">
                <button type="button" onclick="toggleComposeEmojiVanilla(event)" class="compose-emoji-trigger w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 transition">
                    <i class="ph-bold ph-plus text-xl"></i>
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

            <div class="flex-1 bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden focus-within:border-indigo-500 focus-within:bg-white transition-colors flex flex-col justify-center min-h-[48px]">
                
                <!-- Voice Note Preview -->
                <div id="vn-preview" class="w-full bg-transparent text-slate-800 px-4 py-2 flex items-center gap-3" style="display: none;">
                    <i class="ph-bold ph-microphone text-rose-500"></i>
                    <audio id="vn-audio" controls class="h-8 flex-1 max-w-[200px] sm:max-w-[300px]"></audio>
                    <button type="button" onclick="cancelVoiceNote()" class="p-1.5 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-200 transition ml-auto" title="Hapus rekaman">
                        <i class="ph-bold ph-trash text-lg"></i>
                    </button>
                </div>

                <div class="flex items-end">
                    <textarea name="content" rows="1" placeholder="Tulis komentar baru untuk postingan ini..." class="w-full bg-transparent text-slate-800 placeholder-slate-400 px-4 py-3 outline-none resize-none min-h-[48px] max-h-32 text-sm sm:text-base no-scrollbar" id="reply-textarea" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                    
                    <!-- Mic Button -->
                    <button type="button" id="record-btn" onclick="toggleRecord()" class="p-3 text-slate-400 hover:text-rose-500 transition flex-shrink-0 mb-0.5 mr-0.5 rounded-xl flex items-center justify-center" title="Merekam voice note">
                        <i class="ph-bold ph-microphone text-xl"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-indigo-200 hover:scale-105 transition flex-shrink-0 mb-1" title="Kirim Komentar">
                <i class="ph-bold ph-paper-plane-right text-xl"></i>
            </button>
        </form>
    </div>
</div>
@else
<div class="fixed bottom-0 left-0 w-full bg-rose-50 border-t border-rose-200 pb-safe z-50">
    <div class="max-w-[1200px] mx-auto px-4 py-4 text-center text-rose-600 font-bold text-sm">
        <i class="ph-bold ph-lock-key mr-2"></i> Topik ini telah dikunci.
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
    const generalLabel = document.getElementById('general-comment-label');
    const textarea = document.getElementById('reply-textarea');

    if (parentInput) parentInput.value = id;
    if (quoteUser) quoteUser.textContent = 'Membalas @' + user;
    if (quoteText) quoteText.textContent = text ? '"' + text + '"' : 'Pesan';
    
    // Switch labels: hide general comment, show reply preview
    if (generalLabel) generalLabel.classList.add('hidden');
    if (quotePreview) quotePreview.classList.remove('hidden');
    
    if (textarea) {
        textarea.placeholder = 'Tulis balasan untuk @' + user + '...';
        textarea.focus();
    }

    // Scroll smoothly to compose bar
    document.getElementById('compose-bar')?.scrollIntoView({ behavior: 'smooth', block: 'end' });
}

function cancelQuote() {
    document.getElementById('parent_reply_id').value = '';
    
    // Switch labels: show general comment, hide reply preview
    document.getElementById('quote-preview')?.classList.add('hidden');
    document.getElementById('general-comment-label')?.classList.remove('hidden');
    
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
            btn.className = 'flex items-center gap-1.5 px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 transition shadow-sm';
            if(!isThread) btn.className = 'flex items-center gap-1 px-1.5 py-0.5 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[10px] font-bold text-slate-700 transition shadow-sm';
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
                        btnEl.className = 'w-full relative overflow-hidden rounded-xl border text-left p-3 transition-all duration-300 group cursor-pointer border-indigo-500 bg-indigo-50/90 ring-2 ring-indigo-400/30';
                    } else {
                        btnEl.className = 'w-full relative overflow-hidden rounded-xl border text-left p-3 transition-all duration-300 group cursor-pointer border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50';
                    }
                }

                if (checkEl) {
                    if (isSelected) {
                        checkEl.className = 'w-5 h-5 rounded-full flex items-center justify-center border text-[11px] flex-shrink-0 border-indigo-600 bg-indigo-600 text-white';
                        checkEl.innerHTML = '<i class="ph-bold ph-check"></i>';
                    } else {
                        checkEl.className = 'w-5 h-5 rounded-full flex items-center justify-center border text-[11px] flex-shrink-0 border-slate-300 group-hover:border-indigo-400 bg-white text-slate-500';
                        checkEl.textContent = (idx + 1);
                    }
                }
            });
        }
    } catch (e) { console.error("Poll Error:", e); }
}

// --- Voice Note Logic ---
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
            
            recordBtn.innerHTML = '<i class="ph-bold ph-microphone text-xl"></i>';
            recordBtn.classList.remove('text-rose-500', 'animate-pulse');
            recordBtn.style.display = 'none';
        };
        
        mediaRecorder.start();
        isRecording = true;
        recordSeconds = 0;
        
        recordBtn.innerHTML = '<i class="ph-bold ph-stop text-xl"></i>';
        recordBtn.classList.add('text-rose-500', 'animate-pulse');
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
        textarea.placeholder = "Ketik pesan...";
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
