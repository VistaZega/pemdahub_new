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
         KOLOM KIRI & TENGAH: FEED UTAMA (CERAH & SEGAR)
    ═══════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ── 1. Story Bar (Instagram-Style Cerah & Animatif) ── --}}
        @if($activeStories->isNotEmpty())
            <div class="bg-white rounded-3xl border border-slate-200/80 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-2">
                        <i class="fas fa-bolt text-amber-500 text-sm"></i>
                        <span>Story Warga Pembda (24 Jam)</span>
                    </span>
                    <span class="text-[11px] text-slate-400 font-semibold">{{ $activeStories->count() }} baru</span>
                </div>
                <div class="flex items-center gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    @foreach($activeStories as $story)
                        <a href="{{ route('circle.profile', ['username' => $story->user->circleProfile->username ?? $story->user->username]) }}" 
                           class="flex flex-col items-center gap-1.5 shrink-0 group focus:outline-none">
                            <div class="w-16 h-16 rounded-full p-[2.5px] story-ring-gradient group-hover:scale-105 transition-transform duration-200 shadow-sm">
                                <div class="w-full h-full rounded-full p-0.5 bg-white overflow-hidden">
                                    <img src="{{ $story->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $story->user->name }}"
                                         class="w-full h-full rounded-full object-cover bg-slate-100">
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 group-hover:text-indigo-600 max-w-[70px] truncate text-center transition-colors">
                                {{ $story->user->circleProfile->nickname ?? explode(' ', $story->user->name)[0] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 2. Filter Tab Bar & Mobile Switcher ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-3xl border border-slate-200/80 p-2.5 shadow-sm">
            {{-- Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-extrabold transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fas fa-layer-group text-[11px]"></i>
                    <span>Semua Feed</span>
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'portfolio'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-extrabold transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'portfolio' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fas fa-trophy text-[11px]"></i>
                    <span>Karya & Proyek</span>
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'wall'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-extrabold transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'wall' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fas fa-mug-hot text-[11px]"></i>
                    <span>Nongki & Seru</span>
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'reels'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-extrabold transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'reels' ? 'bg-pink-600 text-white shadow-md shadow-pink-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fas fa-clapperboard text-[11px]"></i>
                    <span>Reels</span>
                </a>
            </div>

            {{-- Mobile Unit Filter --}}
            <div class="flex md:hidden items-center gap-1 border-t sm:border-t-0 border-slate-100 pt-2 sm:pt-0">
                <a href="{{ route('circle.feed') }}" class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ !request('school') ? 'bg-indigo-100 text-indigo-700' : 'text-slate-500' }}">Semua</a>
                <a href="{{ route('circle.feed', ['school' => 'SMK']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ request('school') === 'SMK' ? 'bg-orange-500 text-white' : 'text-slate-500' }}">SMK</a>
                <a href="{{ route('circle.feed', ['school' => 'SMA']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ request('school') === 'SMA' ? 'bg-blue-600 text-white' : 'text-slate-500' }}">SMA</a>
                <a href="{{ route('circle.feed', ['school' => 'SMP']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ request('school') === 'SMP' ? 'bg-emerald-600 text-white' : 'text-slate-500' }}">SMP</a>
            </div>
        </div>

        {{-- ── 3. Post Cards Feed ── --}}
        @if($posts->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center shadow-sm">
                <div class="w-20 h-20 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
                    <i class="fas fa-sparkles"></i>
                </div>
                <h3 class="font-display font-black text-xl text-slate-900 mb-2 tracking-tight">Panggung Masih Menanti Ceritamu</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto mb-6 leading-relaxed">
                    Jadilah pelopor pertama yang membagikan momen nongki santai, unjuk keahlian bengkel/lab, atau karya terbaikmu hari ini!
                </p>
                @auth
                    @php
                        $myProfile = Auth::user()->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser(Auth::user());
                    @endphp
                    <a href="{{ route('circle.profile', ['username' => $myProfile->username]) }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold text-white transition-base shadow-md shadow-indigo-500/25 hover:scale-105"
                       style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                        <i class="fas fa-pen-nib"></i>
                        <span>Buka Profil Saya & Mulai Posting</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold text-white transition-base shadow-md shadow-indigo-500/25 hover:scale-105"
                       style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        <span>Masuk untuk Berpartisipasi</span>
                    </a>
                @endauth
            </div>
        @else
            <div class="space-y-6">
                @foreach($posts as $post)
                    <article class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200">
                        {{-- Post Header (Author Info & Badges) --}}
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                   class="shrink-0 focus:outline-none group">
                                    <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $post->user->name }}"
                                         class="w-11 h-11 rounded-2xl object-cover ring-2 ring-slate-100 group-hover:ring-indigo-500 transition-all">
                                </a>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                           class="font-display font-bold text-sm sm:text-base text-slate-900 hover:text-indigo-600 transition-colors truncate">
                                            {{ $post->user->name }}
                                        </a>
                                        @if($post->user->role === 'guru')
                                            <i class="fas fa-certificate text-blue-500 text-xs" title="Guru / Mentor Resmi"></i>
                                        @else
                                            <i class="fas fa-circle-check text-amber-500 text-xs" title="Siswa Aktif Pembda"></i>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5 flex-wrap mt-0.5">
                                        <span>{{ $post->created_at->diffForHumans() }}</span>
                                        @if($post->school)
                                            <span>•</span>
                                            <span class="font-semibold text-slate-700">{{ $post->school->name }}</span>
                                        @endif
                                        @if($post->location_tag)
                                            <span>•</span>
                                            <span class="text-indigo-600 font-medium"><i class="fas fa-map-pin text-[9px] mr-0.5"></i>{{ $post->location_tag }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Category Pill --}}
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 {{ $post->tab_category === 'portfolio' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700' }}">
                                {{ $post->tab_category === 'portfolio' ? 'Karya' : 'Nongki' }}
                            </span>
                        </div>

                        {{-- Mood Indicator (jika ada) --}}
                        @if($post->mood_text)
                            <div class="mb-3.5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-semibold shadow-2xs">
                                <span class="text-base">{{ $post->mood_emoji ?? '✨' }}</span>
                                <span>{{ $post->mood_text }}</span>
                            </div>
                        @endif

                        {{-- Post Title (khusus karya/project) --}}
                        @if($post->title)
                            <h3 class="font-display font-black text-base sm:text-lg text-slate-900 mb-2 leading-snug">
                                {{ $post->title }}
                            </h3>
                        @endif

                        {{-- Post Content Text --}}
                        @if($post->content)
                            <div class="text-sm text-slate-800 leading-relaxed whitespace-pre-line mb-4 font-normal">
                                {{ $post->content }}
                            </div>
                        @endif

                        {{-- Post Media (Foto/Video) --}}
                        @if($post->media->isNotEmpty())
                            <div class="rounded-2xl overflow-hidden mb-4 bg-slate-900 max-h-[500px] flex items-center justify-center shadow-xs">
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
                        <div class="flex items-center justify-between pt-3.5 border-t border-slate-100 text-xs text-slate-500">
                            {{-- Reaction Buttons --}}
                            <div class="flex items-center gap-2 sm:gap-3">
                                <button type="button" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl hover:bg-orange-50 text-slate-700 hover:text-orange-600 font-bold transition-base cursor-pointer">
                                    <span class="text-base">🔥</span>
                                    <span>{{ $post->likes_count > 0 ? $post->likes_count : 'Lit' }}</span>
                                </button>
                                <button type="button" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 font-semibold transition-base cursor-pointer">
                                    <span class="text-base">👏</span>
                                    <span class="hidden sm:inline">Apresiasi</span>
                                </button>
                                <button type="button" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl hover:bg-amber-50 text-slate-700 hover:text-amber-600 font-semibold transition-base cursor-pointer">
                                    <span class="text-base">💡</span>
                                    <span class="hidden sm:inline">Inspirasi</span>
                                </button>
                            </div>

                            {{-- Comments & Profile Link --}}
                            <div class="flex items-center gap-3">
                                <span class="text-[11px] text-slate-500 font-medium">
                                    <i class="far fa-comment-dots mr-1 text-slate-400"></i>{{ $post->comments_count }}
                                </span>
                                <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                                    <span>Lihat Profil</span>
                                    <i class="fas fa-chevron-right text-[10px]"></i>
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
         KOLOM KANAN: SIDEBAR WIDGETS (CERAH & SEGAR)
    ═══════════════════════════════════════════ --}}
    <div class="hidden lg:block space-y-6">

        {{-- ── 1. Hero Widget: Identity & Mission (Cerah & Menarik) ── --}}
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-3xl p-6 text-white shadow-lg shadow-indigo-500/20 relative overflow-hidden">
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/20 backdrop-blur-md text-white mb-4">
                    <i class="fas fa-bullseye text-[10px]"></i>
                    <span>The 1st Edu-Social Network</span>
                </div>
                <h3 class="font-display font-black text-xl text-white mb-2 tracking-tight leading-snug">
                    Panggung Bebas Berekspresi & Berkarya
                </h3>
                <p class="text-xs text-white/90 leading-relaxed mb-5 font-medium">
                    Tunjukkan pesona, gaya nyentrik, suasana hati, dan mahakarya terbaikmu kepada keluarga besar Pembda dan dunia industri!
                </p>
                <div class="p-3.5 rounded-2xl bg-black/20 backdrop-blur-md border border-white/15 text-[11px] font-semibold text-white/95 flex items-center gap-2.5">
                    <i class="fas fa-shield-halved text-amber-300 text-base shrink-0"></i>
                    <span>Nilai akademik, rapor & BK tetap terlindungi 100% aman & privat.</span>
                </div>
            </div>
            {{-- Hiasan bulat blur --}}
            <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/15 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        {{-- ── 2. Trending Profiles Widget (Cerah & Bersih) ── --}}
        @if($trendingProfiles->isNotEmpty())
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-2">
                        <i class="fas fa-fire text-orange-500"></i>
                        <span>Profil Populer</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-bold">Paling Banyak Dilihat</span>
                </div>
                <div class="space-y-3">
                    @foreach($trendingProfiles as $tp)
                        <a href="{{ route('circle.profile', ['username' => $tp->username]) }}" 
                           class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-indigo-50/60 border border-transparent hover:border-indigo-100 transition-base group">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $tp->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                     alt="{{ $tp->user->name }}"
                                     class="w-10 h-10 rounded-2xl object-cover ring-2 ring-slate-100 group-hover:ring-indigo-500 transition-all shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors truncate flex items-center gap-1.5">
                                        <span>{{ $tp->nickname ?? $tp->user->name }}</span>
                                        @if($tp->mood_emoji)
                                            <span class="text-[11px]">{{ $tp->mood_emoji }}</span>
                                        @endif
                                    </h4>
                                    <span class="text-[10px] text-slate-500 block truncate">
                                        {{ $tp->user->school?->name ?? 'Perguruan Pembda' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] font-extrabold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-xl shrink-0">
                                <i class="fas fa-eye text-[9px] mr-1 text-indigo-500"></i>{{ number_format($tp->views_count) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 3. Footer Mini ── --}}
        <div class="px-2 text-center text-[11px] text-slate-400 leading-relaxed">
            <p class="font-semibold text-slate-500">© {{ date('Y') }} Perguruan Pembda Gunungsitoli.</p>
            <p class="mt-0.5">Inovasi Ekosistem Edu-Social PembdaHUB.</p>
        </div>

    </div>

</div>
@endsection
