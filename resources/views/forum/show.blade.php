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
<div class="w-full bg-forum-base text-forum-title font-['Inter'] rounded-3xl border border-forum mx-auto flex flex-col pt-4 pb-32 px-4 sm:px-6 relative shadow-sm" style="min-height: 85vh;" x-data="forumChat()">
    
    <!-- Top Nav Bar -->
    <div class="flex items-center justify-between bg-white/95 backdrop-blur-xl p-4 rounded-2xl border border-slate-200 mb-6 sticky top-4 z-40 shadow-sm">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('forum.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 transition flex-shrink-0">
                <i class="ph-bold ph-arrow-left text-xl"></i>
            </a>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">{{ $thread->category_label }}</div>
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
                <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-none p-5 sm:p-6 shadow-sm w-full max-w-3xl">
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
                        <a href="{{ asset('storage/' . $thread->attachment_path) }}" download class="mt-4 flex items-center gap-3 p-3 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 rounded-xl transition max-w-sm">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
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
            <div class="max-w-3xl ml-14 sm:ml-16 bg-white border border-blue-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>
                <h3 class="forum-hdr text-sm font-bold text-slate-800 flex items-center gap-2 mb-3">
                    <i class="ph-bold ph-handshake text-blue-600"></i> Rekrutmen Tim
                </h3>
                @if(auth()->id() !== $thread->user_id && $thread->status === 'seeking_members')
                    @php $hasApplied = $thread->members()->where('user_id', auth()->id())->exists(); @endphp
                    @if(!$hasApplied)
                        <form action="{{ route('forum.join', $thread) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="notes" placeholder="Pesan singkat (opsional)..." class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-blue-500 outline-none">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition shadow-sm">Gabung</button>
                        </form>
                    @else
                        <div class="text-sm font-bold text-blue-600">Kamu sudah mendaftar. Menunggu persetujuan.</div>
                    @endif
                @endif
            </div>
        @endif

        <!-- POLL PANEL (if exists) -->
        @if($thread->poll)
            <div class="max-w-3xl ml-14 sm:ml-16 bg-white border border-indigo-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-500"></div>
                <h3 class="forum-hdr text-base font-bold text-slate-800 mb-4">{{ $thread->poll->question }}</h3>
                <div class="space-y-3" id="poll-options-container">
                    @foreach($thread->poll->options as $option)
                        @php 
                            $pct = $option->percentage(); 
                            $hasVoted = $thread->poll->votes()->where('user_id', auth()->id())->where('forum_poll_option_id', $option->id)->exists();
                        @endphp
                        <button onclick="votePoll({{ $option->id }})" class="w-full relative overflow-hidden rounded-xl border {{ $hasVoted ? 'border-indigo-500 bg-indigo-50/80' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }} p-3 text-left transition group">
                            <!-- Progress Bar -->
                            <div class="absolute top-0 left-0 h-full bg-indigo-200/50 transition-all duration-1000" style="width: {{ $pct }}%" id="poll-bg-{{ $option->id }}"></div>
                            
                            <div class="relative z-10 flex justify-between items-center text-sm font-bold">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full border-2 {{ $hasVoted ? 'border-indigo-600 bg-indigo-600' : 'border-slate-400' }} flex items-center justify-center">
                                        @if($hasVoted)<div class="w-2 h-2 rounded-full bg-white"></div>@endif
                                    </div>
                                    <span class="{{ $hasVoted ? 'text-indigo-900' : 'text-slate-700' }}">{{ $option->option_text }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-500">
                                    <span id="poll-pct-{{ $option->id }}">{{ $pct }}%</span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
                <div class="mt-3 text-xs text-slate-400 font-bold text-right" id="poll-total-votes">Total Votes: {{ $thread->poll->totalVotes() }}</div>
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
        <div id="replies" class="space-y-6">
            @foreach($thread->replies as $reply)
                <div class="flex gap-4" id="reply-{{ $reply->id }}">
                    <img src="{{ $reply->user->avatar_url }}" 
                         class="w-10 h-10 rounded-full border border-slate-200 shadow-sm flex-shrink-0 object-cover">
                    <div class="flex-1 min-w-0 space-y-1.5">
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <span class="font-bold text-slate-800 text-sm">{{ $reply->user->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-bold uppercase tracking-wider">{{ $reply->user->role }}</span>
                            <span class="text-xs text-slate-400">{{ $reply->created_at->format('H:i') }}</span>
                            @if($reply->is_accepted)
                                <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded flex items-center gap-1 font-bold">
                                    <i class="ph-bold ph-star text-amber-600"></i> Jawaban Terbaik
                                </span>
                            @endif
                        </div>

                        <!-- Quote Parent -->
                        @if($reply->parent)
                            <div class="bg-slate-100 border-l-2 border-indigo-500 rounded-lg p-2.5 max-w-2xl text-xs text-slate-600 mb-2 cursor-pointer hover:bg-indigo-50 transition" onclick="document.getElementById('reply-{{ $reply->parent_id }}').scrollIntoView({behavior: 'smooth'})">
                                <div class="font-bold text-indigo-600 mb-1">Membalas {{ $reply->parent->user->name }}</div>
                                <div class="line-clamp-2">
                                    @if($reply->parent->voice_note_path)
                                        <i class="ph-bold ph-microphone"></i> Pesan Suara
                                    @else
                                        {!! strip_tags($reply->parent->content) !!}
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Bubble -->
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
                                <!-- Reply Button -->
                                <button @click="quoteReply({{ $reply->id }}, '{{ addslashes($reply->user->name) }}', '{{ addslashes(Str::limit(strip_tags($reply->content), 100)) }}')" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 transition">
                                    BALAS
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
                </div>
            @endforeach
        </div>
    </div>

<!-- STICKY COMPOSE BAR -->
@if(!$thread->is_locked)
<div class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-xl border-t border-slate-200 pb-safe z-50 transition-all duration-300 shadow-lg" id="compose-bar">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-3">
        <!-- Quote Preview Area -->
        <div id="quote-preview" class="hidden mb-2 ml-14 sm:ml-16 mr-14">
            <div class="bg-slate-100 border-l-2 border-indigo-500 rounded-lg p-2.5 flex justify-between items-start gap-4">
                <div class="min-w-0">
                    <div class="text-xs font-bold text-indigo-600 mb-0.5" id="quote-user"></div>
                    <div class="text-xs text-slate-600 line-clamp-1" id="quote-text"></div>
                </div>
                <button type="button" onclick="cancelQuote()" class="text-slate-400 hover:text-slate-700 p-1">
                    <i class="ph-bold ph-x"></i>
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
                    <textarea name="content" rows="1" placeholder="Ketik pesan..." class="w-full bg-transparent text-slate-800 placeholder-slate-400 px-4 py-3 outline-none resize-none min-h-[48px] max-h-32 text-sm sm:text-base no-scrollbar" id="reply-textarea" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                    
                    <!-- Mic Button -->
                    <button type="button" id="record-btn" onclick="toggleRecord()" class="p-3 text-slate-400 hover:text-rose-500 transition flex-shrink-0 mb-0.5 mr-0.5 rounded-xl flex items-center justify-center" title="Merekam voice note">
                        <i class="ph-bold ph-microphone text-xl"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-r from-indigo-500 to-fuchsia-500 flex items-center justify-center text-white shadow-md shadow-indigo-200 hover:scale-105 transition flex-shrink-0 mb-1">
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
function quoteReply(id, user, text) {
    document.getElementById('parent_reply_id').value = id;
    document.getElementById('quote-user').textContent = 'Membalas ' + user;
    document.getElementById('quote-text').textContent = text;
    document.getElementById('quote-preview').classList.remove('hidden');
    document.querySelector('textarea[name="content"]').focus();
}

function cancelQuote() {
    document.getElementById('parent_reply_id').value = '';
    document.getElementById('quote-preview').classList.add('hidden');
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
            document.getElementById('poll-total-votes').textContent = 'Total Votes: ' + data.total_votes;
            data.options.forEach(opt => {
                document.getElementById(`poll-pct-${opt.id}`).textContent = opt.percentage + '%';
                document.getElementById(`poll-bg-${opt.id}`).style.width = opt.percentage + '%';
                
                const btn = document.getElementById(`poll-bg-${opt.id}`).parentElement;
                const circle = btn.querySelector('.rounded-full.border-2');
                const text = btn.querySelector('.relative.z-10 span');
                
                if (data.voted && data.voted_option_id === opt.id) {
                    btn.className = 'w-full relative overflow-hidden rounded-xl border border-indigo-500 bg-indigo-50/80 p-3 text-left transition group';
                    circle.className = 'w-4 h-4 rounded-full border-2 border-indigo-600 bg-indigo-600 flex items-center justify-center';
                    circle.innerHTML = '<div class="w-2 h-2 rounded-full bg-white"></div>';
                    text.className = 'text-indigo-900';
                } else {
                    btn.className = 'w-full relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 p-3 text-left transition group';
                    circle.className = 'w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center';
                    circle.innerHTML = '';
                    text.className = 'text-slate-700';
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
