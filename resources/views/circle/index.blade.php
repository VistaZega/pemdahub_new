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
         KOLOM KIRI & TENGAH: FEED UTAMA
    ═══════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ── 1. Story Bar (24 Jam Ephemeral Moments) ── --}}
        @if($activeStories->isNotEmpty())
            <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800/80 p-4 shadow-xl shadow-black/20">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <i class="fas fa-bolt text-amber-400 text-xs"></i>
                        <span>Story Warga Pembda (24 Jam)</span>
                    </span>
                    <span class="text-[10px] text-slate-500 font-semibold">{{ $activeStories->count() }} update baru</span>
                </div>
                <div class="flex items-center gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    @foreach($activeStories as $story)
                        <a href="{{ route('circle.profile', ['username' => $story->user->circleProfile->username ?? $story->user->username]) }}" 
                           class="flex flex-col items-center gap-1.5 shrink-0 group focus:outline-none">
                            <div class="w-15 h-15 rounded-2xl p-[2.5px] story-ring group-hover:scale-105 transition-transform duration-200">
                                <div class="w-full h-full rounded-[14px] p-0.5 bg-slate-900 overflow-hidden">
                                    <img src="{{ $story->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $story->user->name }}"
                                         class="w-full h-full rounded-[12px] object-cover bg-slate-800">
                                </div>
                            </div>
                            <span class="text-[11px] font-medium text-slate-300 group-hover:text-white max-w-[68px] truncate text-center transition-colors">
                                {{ $story->user->circleProfile->nickname ?? explode(' ', $story->user->name)[0] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 2. Filter Tab Bar & Mobile Unit Filter ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800/80 p-2.5 shadow-xl shadow-black/20">
            {{-- Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-base shrink-0 flex items-center gap-1.5 {{ $tabFilter === 'all' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    <i class="fas fa-layer-group text-[11px]"></i>
                    <span>Semua Feed</span>
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'portfolio'])) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-base shrink-0 flex items-center gap-1.5 {{ $tabFilter === 'portfolio' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    <i class="fas fa-trophy text-[11px]"></i>
                    <span>Karya & Proyek</span>
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'wall'])) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-base shrink-0 flex items-center gap-1.5 {{ $tabFilter === 'wall' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    <i class="fas fa-mug-hot text-[11px]"></i>
                    <span>Nongki & Seru</span>
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'reels'])) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-base shrink-0 flex items-center gap-1.5 {{ $tabFilter === 'reels' ? 'bg-pink-600 text-white shadow-lg shadow-pink-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    <i class="fas fa-clapperboard text-[11px]"></i>
                    <span>Reels</span>
                </a>
            </div>

            {{-- Filter Unit (Mobile friendly pills) --}}
            <div class="flex md:hidden items-center gap-1 border-t sm:border-t-0 border-slate-800 pt-2 sm:pt-0">
                <a href="{{ route('circle.feed') }}" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ !request('school') ? 'bg-slate-700 text-white' : 'text-slate-400' }}">Semua</a>
                <a href="{{ route('circle.feed', ['school' => 'SMK']) }}" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ request('school') === 'SMK' ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30' : 'text-slate-400' }}">SMK</a>
                <a href="{{ route('circle.feed', ['school' => 'SMA']) }}" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ request('school') === 'SMA' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'text-slate-400' }}">SMA</a>
                <a href="{{ route('circle.feed', ['school' => 'SMP']) }}" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ request('school') === 'SMP' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400' }}">SMP</a>
            </div>
        </div>

        {{-- ── 3. Feed List (Post Cards) ── --}}
        @if($posts->isEmpty())
            <div class="bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-800/80 p-12 text-center shadow-xl shadow-black/20">
                <div class="w-20 h-20 rounded-3xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner">
                    <i class="fas fa-sparkles"></i>
                </div>
                <h3 class="font-display font-bold text-xl text-white mb-2 tracking-tight">Panggung Masih Menanti Karyamu</h3>
                <p class="text-sm text-slate-400 max-w-md mx-auto mb-6 leading-relaxed">
                    Belum ada konten di kategori ini. Jadilah pelopor yang membagikan momen nongki santai, unjuk keahlian bengkel/lab, atau karya terbaikmu hari ini!
                </p>
                @auth
                    @php
                        $myProfile = Auth::user()->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser(Auth::user());
                    @endphp
                    <a href="{{ route('circle.profile', ['username' => $myProfile->username]) }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white transition-base shadow-lg shadow-indigo-600/30 hover:scale-105"
                       style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                        <i class="fas fa-pen-nib"></i>
                        <span>Buka Profil & Mulai Posting</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white transition-base shadow-lg shadow-indigo-600/30 hover:scale-105"
                       style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        <span>Masuk untuk Berpartisipasi</span>
                    </a>
                @endauth
            </div>
        @else
            <div class="space-y-6">
                @foreach($posts as $post)
                    <article class="bg-slate-900/90 backdrop-blur-md rounded-3xl border border-slate-800/80 p-5 sm:p-6 shadow-xl shadow-black/30 hover:border-slate-700/80 transition-all duration-200">
                        {{-- Post Header (Author Info & Badges) --}}
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                   class="shrink-0 focus:outline-none group">
                                    <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $post->user->name }}"
                                         class="w-11 h-11 rounded-2xl object-cover ring-1 ring-white/10 group-hover:ring-indigo-500 transition-all">
                                </a>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                           class="font-display font-bold text-sm text-slate-100 hover:text-indigo-400 transition-colors truncate">
                                            {{ $post->user->name }}
                                        </a>
                                        @if($post->user->role === 'guru')
                                            <i class="fas fa-certificate text-blue-400 text-xs" title="Guru / Mentor Resmi"></i>
                                        @else
                                            <i class="fas fa-circle-check text-amber-400 text-xs" title="Siswa Aktif Pembda"></i>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5 flex-wrap mt-0.5">
                                        <span>{{ $post->created_at->diffForHumans() }}</span>
                                        @if($post->school)
                                            <span>•</span>
                                            <span class="text-slate-300 font-medium">{{ $post->school->name }}</span>
                                        @endif
                                        @if($post->location_tag)
                                            <span>•</span>
                                            <span class="text-indigo-400"><i class="fas fa-map-pin text-[9px] mr-1"></i>{{ $post->location_tag }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Category Pill --}}
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 {{ $post->tab_category === 'portfolio' ? 'bg-amber-500/15 text-amber-300 border border-amber-500/25' : 'bg-slate-800 text-slate-300 border border-slate-700/50' }}">
                                {{ $post->tab_category === 'portfolio' ? 'Karya' : 'Nongki' }}
                            </span>
                        </div>

                        {{-- Mood Indicator (jika ada) --}}
                        @if($post->mood_text)
                            <div class="mb-3 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs font-medium">
                                <span class="text-sm">{{ $post->mood_emoji ?? '✨' }}</span>
                                <span>{{ $post->mood_text }}</span>
                            </div>
                        @endif

                        {{-- Post Title (khusus karya/project) --}}
                        @if($post->title)
                            <h3 class="font-display font-bold text-base sm:text-lg text-white mb-2 leading-snug">
                                {{ $post->title }}
                            </h3>
                        @endif

                        {{-- Post Content Text --}}
                        @if($post->content)
                            <div class="text-sm text-slate-200 leading-relaxed whitespace-pre-line mb-4 font-normal">
                                {{ $post->content }}
                            </div>
                        @endif

                        {{-- Post Media (Foto/Video) --}}
                        @if($post->media->isNotEmpty())
                            <div class="rounded-2xl overflow-hidden mb-4 bg-slate-950 border border-slate-800/60 max-h-[500px] flex items-center justify-center">
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

                        {{-- Action & Reaction Bar --}}
                        <div class="flex items-center justify-between pt-3.5 border-t border-slate-800/80 text-xs text-slate-400">
                            {{-- Reaction Buttons --}}
                            <div class="flex items-center gap-3 sm:gap-4">
                                <button type="button" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-orange-400 transition-base cursor-pointer">
                                    <span class="text-sm">🔥</span>
                                    <span class="font-extrabold">{{ $post->likes_count > 0 ? $post->likes_count : 'Lit' }}</span>
                                </button>
                                <button type="button" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-indigo-400 transition-base cursor-pointer">
                                    <span class="text-sm">👏</span>
                                    <span class="font-semibold hidden sm:inline">Apresiasi</span>
                                </button>
                                <button type="button" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-amber-300 transition-base cursor-pointer">
                                    <span class="text-sm">💡</span>
                                    <span class="font-semibold hidden sm:inline">Inspirasi</span>
                                </button>
                            </div>

                            {{-- Comments & Profile Link --}}
                            <div class="flex items-center gap-3">
                                <span class="text-[11px] text-slate-400">
                                    <i class="far fa-comment-dots mr-1"></i>{{ $post->comments_count }}
                                </span>
                                <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                   class="inline-flex items-center gap-1 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors">
                                    <span>Profil</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
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
         KOLOM KANAN: SIDEBAR WIDGETS (DESKTOP)
    ═══════════════════════════════════════════ --}}
    <div class="hidden lg:block space-y-6">

        {{-- ── 1. Hero Widget: Identity & Mission ── --}}
        <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 rounded-3xl p-6 border border-indigo-500/20 shadow-2xl relative overflow-hidden">
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 mb-4">
                    <i class="fas fa-circle-dot text-[8px] animate-pulse"></i>
                    <span>The 1st Edu-Social Network</span>
                </div>
                <h3 class="font-display font-black text-xl text-white mb-2 tracking-tight leading-snug">
                    Panggung Bebas Berekspresi & Berkarya
                </h3>
                <p class="text-xs text-slate-300 leading-relaxed mb-5 font-normal">
                    Tunjukkan pesona, gaya nyentrik, suasana hati, dan mahakarya terbaikmu kepada keluarga besar Pembda dan mitra industri DUDI.
                </p>
                <div class="p-3 rounded-2xl bg-slate-900/60 border border-white/5 text-[11px] font-medium text-slate-300 flex items-center gap-2.5">
                    <i class="fas fa-shield-halved text-emerald-400 text-sm shrink-0"></i>
                    <span>Nilai akademik, rapor & BK tetap terlindungi 100% privat.</span>
                </div>
            </div>
            {{-- Ambient light inside card --}}
            <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        {{-- ── 2. Trending Profiles Widget ── --}}
        @if($trendingProfiles->isNotEmpty())
            <div class="bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-800/80 p-5 shadow-xl shadow-black/20">
                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <i class="fas fa-fire-flame-curved text-orange-400"></i>
                        <span>Profil Populer</span>
                    </h3>
                    <span class="text-[10px] text-slate-500 font-semibold">Banyak Dilihat</span>
                </div>
                <div class="space-y-3">
                    @foreach($trendingProfiles as $tp)
                        <a href="{{ route('circle.profile', ['username' => $tp->username]) }}" 
                           class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-slate-800/60 border border-transparent hover:border-slate-700/50 transition-base group">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $tp->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                     alt="{{ $tp->user->name }}"
                                     class="w-10 h-10 rounded-xl object-cover ring-1 ring-white/10 group-hover:ring-indigo-500 transition-all shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-200 group-hover:text-white transition-colors truncate flex items-center gap-1.5">
                                        <span>{{ $tp->nickname ?? $tp->user->name }}</span>
                                        @if($tp->mood_emoji)
                                            <span class="text-[11px]">{{ $tp->mood_emoji }}</span>
                                        @endif
                                    </h4>
                                    <span class="text-[10px] text-slate-400 block truncate">
                                        {{ $tp->user->school?->name ?? 'Perguruan Pembda' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] font-extrabold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-xl shrink-0">
                                <i class="fas fa-eye text-[9px] mr-1"></i>{{ number_format($tp->views_count) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 3. Footer Mini ── --}}
        <div class="px-2 text-center text-[11px] text-slate-500 leading-relaxed">
            <p class="font-medium">© {{ date('Y') }} Perguruan Pembda Gunungsitoli.</p>
            <p class="text-slate-600 mt-0.5">Ekosistem Edu-Social PembdaHUB.</p>
        </div>

    </div>

</div>
@endsection
