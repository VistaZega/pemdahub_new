@extends('circle.layout')

@section('title', 'Beranda PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio')

@section('og_meta')
    <meta property="og:title" content="PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio Siswa Pembda">
    <meta property="og:description" content="Panggung sosial dan talenta digital pertama untuk siswa dan guru Perguruan Pembda.">
    <meta property="og:image" content="{{ asset('images/icons/icon-512x512.png') }}">
    <meta property="og:url" content="{{ route('circle.feed') }}">
    <meta property="og:type" content="website">
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-7">

    {{-- ═══════════════════════════════════════════
         KOLOM KIRI & TENGAH: FEED UTAMA (PASTEL SEGAR)
    ═══════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ── 1. Story Bar (Bubbles Pastel Cantik & Lucu) ── --}}
        @if($activeStories->isNotEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-4 shadow-xs">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-xs">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <span>Story Warga Pembda (24 Jam)</span>
                    </span>
                    <span class="text-[11px] font-bold text-pink-600 bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-full">
                        {{ $activeStories->count() }} baru ✨
                    </span>
                </div>
                <div class="flex items-center gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    @foreach($activeStories as $story)
                        <a href="{{ route('circle.profile', ['username' => $story->user->circleProfile->username ?? $story->user->username]) }}" 
                           class="flex flex-col items-center gap-1.5 shrink-0 group focus:outline-none">
                            <div class="w-16 h-16 rounded-full p-[3px] pastel-story-ring group-hover:scale-105 transition-transform duration-200 shadow-xs">
                                <div class="w-full h-full rounded-full p-0.5 bg-white overflow-hidden">
                                    <img src="{{ $story->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $story->user->name }}"
                                         class="w-full h-full rounded-full object-cover bg-pink-50">
                                </div>
                            </div>
                            <span class="text-[11px] font-extrabold text-slate-900 group-hover:text-pink-600 max-w-[70px] truncate text-center transition-colors">
                                {{ $story->user->circleProfile->nickname ?? explode(' ', $story->user->name)[0] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 2. Filter Tab Bar Lucu & Berwarna Pastel ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-3xl border-2 border-[#f1e8dc] p-2.5 shadow-xs">
            {{-- Tabs Pastel --}}
            <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
                {{-- Tab Semua --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'all' ? 'bg-pink-100 text-slate-950 border-2 border-pink-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-pink-200 text-pink-800 flex items-center justify-center text-[10px]">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <span>Semua Feed</span>
                </a>
                {{-- Tab Karya --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'portfolio'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'portfolio' ? 'bg-amber-100 text-slate-950 border-2 border-amber-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center text-[10px]">
                        <i class="fas fa-trophy"></i>
                    </span>
                    <span>Karya & Proyek</span>
                </a>
                {{-- Tab Nongki (Coklat Pastel) --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'wall'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'wall' ? 'bg-[#faedcd] text-slate-950 border-2 border-[#d4a373] shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-[#e6ccb2] text-[#7f5539] flex items-center justify-center text-[10px]">
                        <i class="fas fa-mug-hot"></i>
                    </span>
                    <span>Nongki & Seru</span>
                </a>
                {{-- Tab Reels (Biru Pastel) --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'reels'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'reels' ? 'bg-sky-100 text-slate-950 border-2 border-sky-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-sky-200 text-sky-800 flex items-center justify-center text-[10px]">
                        <i class="fas fa-play"></i>
                    </span>
                    <span>Reels</span>
                </a>
            </div>

            {{-- Mobile Switcher --}}
            <div class="flex md:hidden items-center gap-1.5 border-t sm:border-t-0 border-[#f1e8dc] pt-2 sm:pt-0">
                <a href="{{ route('circle.feed') }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ !request('school') ? 'bg-pink-100 text-slate-950 border border-pink-200' : 'text-slate-700' }}">Semua</a>
                <a href="{{ route('circle.feed', ['school' => 'SMK']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ request('school') === 'SMK' ? 'bg-[#faedcd] text-slate-950 border border-[#d4a373]' : 'text-slate-700' }}">SMK</a>
                <a href="{{ route('circle.feed', ['school' => 'SMA']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ request('school') === 'SMA' ? 'bg-sky-100 text-slate-950 border border-sky-200' : 'text-slate-700' }}">SMA</a>
                <a href="{{ route('circle.feed', ['school' => 'SMP']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ request('school') === 'SMP' ? 'bg-emerald-100 text-slate-950 border border-emerald-200' : 'text-slate-700' }}">SMP</a>
            </div>
        </div>

        {{-- ── 3. Post Cards Feed (Teks Hitam Terbaca Jelas & Kartu Lucu) ── --}}
        @if($posts->isEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-12 text-center shadow-xs">
                <div class="w-20 h-20 rounded-3xl bg-pink-100 text-pink-600 border-2 border-pink-200 flex items-center justify-center mx-auto mb-4 text-3xl shadow-xs">
                    <i class="fas fa-palette"></i>
                </div>
                <h3 class="font-display font-black text-xl text-slate-950 mb-2 tracking-tight">Panggung Masih Menanti Ceritamu</h3>
                <p class="text-sm font-medium text-slate-800 max-w-md mx-auto mb-6 leading-relaxed">
                    Jadilah pelopor pertama yang membagikan momen nongki santai, unjuk keahlian bengkel/lab, atau karya terbaikmu hari ini!
                </p>
                @auth
                    @php
                        $myProfile = Auth::user()->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser(Auth::user());
                    @endphp
                    <a href="{{ route('circle.profile', ['username' => $myProfile->username]) }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-black text-slate-950 bg-pink-200 hover:bg-pink-300 border-2 border-pink-300 shadow-xs transition-base hover:scale-105">
                        <i class="fas fa-pen-nib text-pink-700"></i>
                        <span>Buka Profil Saya & Mulai Posting</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-black text-slate-950 bg-pink-200 hover:bg-pink-300 border-2 border-pink-300 shadow-xs transition-base hover:scale-105">
                        <i class="fas fa-heart text-pink-600"></i>
                        <span>Masuk untuk Berpartisipasi</span>
                    </a>
                @endauth
            </div>
        @else
            <div class="space-y-6">
                @foreach($posts as $post)
                    <article x-data="{ showComments: false }" class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-pink-200 transition-all duration-200">
                        {{-- Post Header (Author Info & Badges) --}}
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                   class="shrink-0 focus:outline-none group">
                                    <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $post->user->name }}"
                                         class="w-12 h-12 rounded-2xl object-cover ring-2 ring-pink-100 group-hover:ring-pink-400 transition-all">
                                </a>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                           class="font-display font-black text-base text-slate-950 hover:text-pink-600 transition-colors truncate">
                                            {{ $post->user->name }}
                                        </a>
                                        @if($post->user->role === 'guru')
                                            <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-[10px]" title="Guru / Mentor Resmi">
                                                <i class="fas fa-certificate"></i>
                                            </span>
                                        @else
                                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]" title="Siswa Aktif Pembda">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5 flex-wrap mt-0.5">
                                        <span>{{ $post->created_at->diffForHumans() }}</span>
                                        @if($post->school)
                                            <span>•</span>
                                            <span class="text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">{{ $post->school->name }}</span>
                                        @endif
                                        @if($post->location_tag)
                                            <span>•</span>
                                            <span class="text-pink-700 font-bold"><i class="fas fa-location-dot text-[9px] mr-0.5"></i>{{ $post->location_tag }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Category Pill Pastel --}}
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 {{ $post->tab_category === 'portfolio' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-[#faedcd] text-[#7f5539] border border-[#d4a373]' }}">
                                @if($post->tab_category === 'portfolio')
                                    <i class="fas fa-trophy mr-1 text-amber-700"></i>Karya
                                @else
                                    <i class="fas fa-mug-hot mr-1 text-[#7f5539]"></i>Nongki
                                @endif
                            </span>
                        </div>

                        {{-- Mood Indicator Pastel Kuning --}}
                        @if($post->mood_text)
                            <div class="mb-3.5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-slate-950 text-xs font-black shadow-2xs">
                                <span class="text-base">{{ $post->mood_emoji ?? '✨' }}</span>
                                <span>{{ $post->mood_text }}</span>
                            </div>
                        @endif

                        {{-- Post Title --}}
                        @if($post->title)
                            <h3 class="font-display font-black text-lg text-slate-950 mb-2 leading-snug">
                                {{ $post->title }}
                            </h3>
                        @endif

                        {{-- Post Content Text (Hitam Pekat & Sangat Mudah Dibaca) --}}
                        @if($post->content)
                            <div class="text-sm font-semibold text-slate-950 leading-relaxed whitespace-pre-line mb-4">
                                {{ $post->content }}
                            </div>
                        @endif

                        {{-- Post Media --}}
                        @if($post->media->isNotEmpty())
                            <div class="rounded-3xl overflow-hidden mb-4 bg-slate-950 border-2 border-[#f1e8dc] max-h-[500px] flex items-center justify-center shadow-xs">
                                @php $m = $post->media->first(); @endphp
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" 
                                         alt="Media karya" 
                                         class="w-full h-auto object-cover max-h-[500px]" 
                                         loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="w-full max-h-[500px] bg-black"></video>
                                @endif
                            </div>
                        @endif

                        {{-- Action & Reaction Bar (Interactive AJAX & Lucu) --}}
                        <div class="flex items-center justify-between pt-3.5 border-t-2 border-[#f8f4ed] text-xs">
                            {{-- Chunky Pastel Reaction Buttons --}}
                            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                                {{-- Lit 🔥 --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'lit')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">🔥</span>
                                    <span class="reaction-count-{{ $post->id }}">{{ $post->likes_count > 0 ? $post->likes_count : 'Lit' }}</span>
                                </button>
                                {{-- Apresiasi 👏 (Hijau Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'clap')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">👏</span>
                                    <span class="hidden sm:inline">Apresiasi</span>
                                </button>
                                {{-- Inspirasi 💡 (Kuning Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'nongki')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">💡</span>
                                    <span class="hidden sm:inline">Inspirasi</span>
                                </button>
                                {{-- Love ❤️ (Pink Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'love')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">❤️</span>
                                    <span class="hidden sm:inline">Suka</span>
                                </button>
                            </div>

                            {{-- Comments & Profile Link --}}
                            <div class="flex items-center gap-3 shrink-0">
                                <button type="button" 
                                        @click="showComments = !showComments"
                                        class="text-xs font-bold text-slate-800 hover:text-pink-600 flex items-center gap-1.5 cursor-pointer px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-pink-50 transition">
                                    <i class="far fa-comment-dots text-pink-500"></i>
                                    <span>{{ $post->comments_count }} Komentar</span>
                                </button>
                                <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-pink-100 hover:bg-pink-200 text-pink-900 font-black transition-colors text-xs">
                                    <span>Lihat</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                        {{-- Collapsible Comment Section (Pastel Card) --}}
                        <div x-show="showComments" x-collapse x-transition class="mt-4 pt-4 border-t border-slate-100">
                            @if($post->comments->isNotEmpty())
                                <div class="space-y-2 mb-3 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    @foreach($post->comments as $cm)
                                        <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs flex items-start gap-2">
                                            <div class="w-6 h-6 rounded-full bg-pink-200 text-pink-800 font-black flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                                                {{ substr($cm->author_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-black text-slate-950 truncate">{{ $cm->author_name }}</span>
                                                    <span class="text-[10px] text-slate-500 shrink-0">{{ $cm->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-slate-800 font-medium mt-0.5 whitespace-pre-line">{{ $cm->comment_text }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Form Komentar Cepat --}}
                            <form action="{{ route('circle.comment.store') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="circle_post_id" value="{{ $post->id }}">
                                <input type="text" name="comment_text" placeholder="Beri apresiasi atau komentar manis..." required
                                       class="flex-1 px-3.5 py-2 rounded-2xl bg-white border-2 border-pink-200 text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-400 shadow-2xs">
                                <button type="submit" class="px-4 py-2 rounded-2xl bg-pink-200 hover:bg-pink-300 text-slate-950 text-xs font-black border-2 border-pink-300 transition-all hover:scale-105 shrink-0 flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                    <i class="fas fa-paper-plane text-pink-700"></i>
                                    <span>Kirim</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach

                {{-- Pagination Links --}}
                <div class="pt-4 flex justify-center">
                    {{ $posts->links() }}
                </div>
            </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════
         KOLOM KANAN: SIDEBAR WIDGETS (PASTEL CANTIK)
    ═══════════════════════════════════════════ --}}
    <div class="hidden lg:block space-y-6">

        {{-- ── 1. Hero Widget: Identity & Mission (Pink & Coklat Pastel) ── --}}
        <div class="bg-gradient-to-br from-[#fce7f3] via-[#fffbeb] to-[#faedcd] rounded-3xl p-6 border-2 border-pink-200/90 shadow-sm relative overflow-hidden">
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/80 border border-pink-300 text-pink-800 mb-4 shadow-2xs">
                    <i class="fas fa-star text-amber-500 text-[10px]"></i>
                    <span>The 1st Edu-Social Network</span>
                </div>
                <h3 class="font-display font-black text-xl text-slate-950 mb-2 tracking-tight leading-snug">
                    Panggung Bebas Berekspresi & Berkarya
                </h3>
                <p class="text-xs font-bold text-slate-900 leading-relaxed mb-5">
                    Tunjukkan pesona, gaya nyentrik, suasana hati, dan mahakarya terbaikmu kepada keluarga besar Pembda dan dunia industri!
                </p>
                <div class="p-3.5 rounded-2xl bg-white/90 border border-pink-200 text-xs font-black text-slate-950 flex items-center gap-3 shadow-2xs">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                        <i class="fas fa-shield-heart text-sm"></i>
                    </span>
                    <span>Nilai akademik, rapor & BK tetap 100% aman & privat.</span>
                </div>
            </div>
            {{-- Bulatan Hiasan Pastel --}}
            <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-pink-300/30 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        {{-- ── 2. Trending Profiles Widget (Biru & Mint Pastel) ── --}}
        @if($trendingProfiles->isNotEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-xs">
                            <i class="fas fa-fire"></i>
                        </span>
                        <span>Profil Populer</span>
                    </h3>
                    <span class="text-[10px] font-black text-slate-900 bg-amber-100 border border-amber-200 px-2 py-0.5 rounded-full">
                        Paling Ramai 🔥
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($trendingProfiles as $tp)
                        <a href="{{ route('circle.profile', ['username' => $tp->username]) }}" 
                           class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-pink-50/70 border border-transparent hover:border-pink-200 transition-base group">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $tp->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                     alt="{{ $tp->user->name }}"
                                     class="w-10 h-10 rounded-2xl object-cover ring-2 ring-pink-100 group-hover:ring-pink-400 transition-all shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-black text-slate-950 group-hover:text-pink-600 transition-colors truncate flex items-center gap-1.5">
                                        <span>{{ $tp->nickname ?? $tp->user->name }}</span>
                                        @if($tp->mood_emoji)
                                            <span class="text-[12px]">{{ $tp->mood_emoji }}</span>
                                        @endif
                                    </h4>
                                    <span class="text-[10px] font-bold text-slate-700 block truncate">
                                        {{ $tp->user->school?->name ?? 'Perguruan Pembda' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] font-black text-slate-950 bg-sky-100 border border-sky-200 px-2.5 py-1 rounded-xl shrink-0 flex items-center gap-1">
                                <i class="fas fa-eye text-[9px] text-sky-700"></i>{{ number_format($tp->views_count) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 3. Footer Mini ── --}}
        <div class="px-2 text-center text-xs font-bold text-slate-800 leading-relaxed">
            <p>© {{ date('Y') }} Perguruan Pembda Gunungsitoli.</p>
            <p class="text-slate-600 text-[11px] mt-0.5">Warna-Warni Kreativitas di PembdaHUB 🌸</p>
        </div>

    </div>

</div>
@endsection
