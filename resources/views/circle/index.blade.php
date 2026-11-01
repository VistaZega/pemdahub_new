@extends('circle.layout')

@php
    $__ogPayload = isset($sharedPost) && $sharedPost ? $sharedPost->share_payload : null;
    $__ogTitle = $__ogPayload
        ? ($__ogPayload['badge'] . ' • ' . $__ogPayload['headline'] . ' — ' . $__ogPayload['author'])
        : 'PembdaCIRCLE — CONNECT • CREATE • GROW | Panggung Edu-Social & Portofolio';
    $__ogDesc = $__ogPayload
        ? ('🗓️ ' . $__ogPayload['time'] . ' • 📍 ' . $__ogPayload['location'] . ($__ogPayload['news'] ? ' — "' . $__ogPayload['news'] . '"' : ''))
        : 'CONNECT (Jejaring Siswa & Industri) • CREATE (Portofolio Riset & Vokasi) • GROW (Lencana Keahlian & Prestasi). Edu-Social Network resmi Perguruan Pembda.';
    $__ogImage = $__ogPayload ? $__ogPayload['image_url'] : asset('images/circle/pembda-circle-logo.png');
    $__ogUrl = $__ogPayload ? $__ogPayload['url'] : route('circle.feed');
@endphp

@section('title', $__ogTitle)

@section('og_meta')
    <meta property="og:site_name" content="PembdaCIRCLE — Perguruan Pembda Nias">
    <meta property="og:title" content="{{ $__ogTitle }}">
    <meta property="og:description" content="{{ $__ogDesc }}">
    <meta property="og:image" content="{{ $__ogImage }}">
    <meta property="og:image:secure_url" content="{{ $__ogImage }}">
    <meta property="og:image:alt" content="{{ $__ogTitle }}">
    <meta property="og:url" content="{{ $__ogUrl }}">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $__ogTitle }}">
    <meta name="twitter:description" content="{{ $__ogDesc }}">
    <meta name="twitter:image" content="{{ $__ogImage }}">
@endsection

@section('content')
@php
    $storiesPayload = $activeStories->map(function ($st) {
        $firstMedia = $st->media->first();
        return [
            'id' => $st->id,
            'name' => $st->user->name,
            'nickname' => $st->user->circleProfile->nickname ?? explode(' ', $st->user->name)[0],
            'username' => $st->user->circleProfile->username ?? $st->user->username,
            'avatar' => $st->user->avatar_url ?? asset('images/default-avatar.svg'),
            'school' => $st->school?->name ?? ($st->user->school?->name ?? 'Perguruan Pembda'),
            'content' => $st->content,
            'mood_emoji' => $st->mood_emoji,
            'mood_text' => $st->mood_text,
            'time_ago' => $st->created_at->diffForHumans(),
            'media_type' => $firstMedia?->media_type,
            'media_url' => $firstMedia?->url,
            'likes_count' => $st->likes_count,
        ];
    })->values();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-7"
     x-data="{
        stories: {{ Js::from($storiesPayload) }},
        storyOpen: false,
        activeIdx: 0,
        progress: 0,
        timer: null,
        openStory(idx) {
            this.activeIdx = idx;
            this.storyOpen = true;
            this.startTimer();
        },
        closeStory() {
            this.storyOpen = false;
            clearInterval(this.timer);
        },
        nextStory() {
            if (this.activeIdx < this.stories.length - 1) {
                this.activeIdx++;
                this.startTimer();
            } else {
                this.closeStory();
            }
        },
        prevStory() {
            if (this.activeIdx > 0) {
                this.activeIdx--;
                this.startTimer();
            }
        },
        startTimer() {
            clearInterval(this.timer);
            this.progress = 0;
            const step = 2;
            this.timer = setInterval(() => {
                this.progress += step;
                if (this.progress >= 100) {
                    this.nextStory();
                }
            }, 100);
        },
        currStory() {
            return this.stories[this.activeIdx] || {};
        },
        getStoryBg(story) {
            const e = story.mood_emoji || '';
            if (e === '🎧' || e === '🎵' || e === '🎶') return 'from-purple-900 via-indigo-900 to-slate-950';
            if (e === '🔥' || e === '🚀' || e === '⚡') return 'from-rose-600 via-orange-600 to-amber-500';
            if (e === '☕' || e === '🧁' || e === '✨') return 'from-pink-600 via-rose-500 to-purple-700';
            if (e === '💡' || e === '📚' || e === '🔬') return 'from-blue-700 via-cyan-600 to-teal-700';
            if (e === '🌱' || e === '🌿' || e === '🍀') return 'from-emerald-700 via-teal-600 to-cyan-800';
            return 'from-pink-600 via-purple-600 to-indigo-700';
        }
     }">

    {{-- ═══════════════════════════════════════════
         KOLOM KIRI & TENGAH: FEED UTAMA (PASTEL SEGAR)
    ═══════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ── 0. PembdaCIRCLE Flagship Banner: CONNECT • CREATE • GROW (Pastel Radiant) ── --}}
        <div class="relative overflow-hidden rounded-3xl p-4 sm:p-5 border-2 border-pink-200/90 shadow-2xs"
             style="background: linear-gradient(135deg, #fdf2f8 0%, #faedcd 35%, #e0f2fe 70%, #dcfce7 100%) !important;">
            <div class="absolute -right-12 -bottom-12 w-36 h-36 bg-pink-200/60 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-12 -top-12 w-36 h-36 bg-sky-200/60 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <x-circle-logo size="md" variant="emblem" />
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-display font-black text-base sm:text-lg text-pink-950 tracking-tight">Pembda<span class="circle-gradient-text ml-1">CIRCLE</span></span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/90 border border-pink-200 text-pink-700 text-[9px] font-black tracking-wider uppercase shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-ping"></span>
                                Live Ecosystem
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-black tracking-widest uppercase mt-0.5 flex-wrap">
                            <span class="text-sky-700 flex items-center gap-1"><span>🌐</span> CONNECT</span>
                            <span class="text-pink-300">•</span>
                            <span class="text-rose-600 flex items-center gap-1"><span>💡</span> CREATE</span>
                            <span class="text-pink-300">•</span>
                            <span class="text-emerald-700 flex items-center gap-1"><span>🚀</span> GROW</span>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-2 shrink-0 text-[11px] font-bold text-slate-700 bg-white/80 border border-pink-200/80 px-3.5 py-1.5 rounded-2xl shadow-2xs">
                    <span class="text-amber-500">✨</span>
                    <span>Wadah Kreasi, Nongki & Portofolio Siswa</span>
                </div>
            </div>
        </div>

        {{-- ── 0B. PEMBERITAHUAN RINGKAS (OTOMATIS HILANG SETELAH 1 MINGGU): POIN BERDASARKAN APRESIASI ── --}}
        @php
            $__pointNotice = \App\Services\CirclePointService::getTransitionNoticeInfo();
        @endphp
        @if($__pointNotice['show'])
            <div x-data="{ openRules: false }" class="bg-gradient-to-r from-amber-50 via-white to-pink-50 rounded-2xl border-2 border-amber-300 px-3.5 py-2.5 shadow-2xs">
                <div class="flex items-center justify-between gap-2.5 flex-wrap">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded-lg bg-amber-400 text-slate-950 flex items-center justify-center text-xs font-black shrink-0">⚡</span>
                        <p class="text-xs font-extrabold text-slate-900">
                            Anda mendapatkan Point di <strong>PembdaCIRCLE</strong> berdasarkan apresiasi yang Anda terima ...
                            <button type="button" @click="openRules = !openRules" class="ml-1 text-pink-600 hover:text-pink-700 underline font-black cursor-pointer">
                                <span x-text="openRules ? 'Tutup Tabel ▴' : 'lihat tabel point prestasi ! ▾'">lihat tabel point prestasi ! ▾</span>
                            </button>
                        </p>
                    </div>
                    @auth
                        <a href="{{ route('circle.profile', ['username' => Auth::user()->circle_username ?? Auth::user()->username ?? 'user-' . Auth::id(), 'tab' => 'points']) }}"
                           class="px-2.5 py-1 rounded-xl bg-amber-200/90 hover:bg-amber-300 text-amber-950 border border-amber-400 text-[11px] font-black transition flex items-center gap-1 shrink-0">
                            <span>⚡ Jejak Poin ({{ number_format(Auth::user()->reputation?->total_points ?? 0) }})</span>
                        </a>
                    @endauth
                </div>

                {{-- Tabel Poin Apresiasi (Hanya muncul saat diklik) --}}
                <div x-show="openRules" x-collapse x-transition class="mt-2.5 pt-2.5 border-t border-amber-200">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px]">
                        <div class="p-2 rounded-xl bg-white border border-pink-200">
                            <strong class="text-slate-950 block">🔥 Reaksi Teman (+3 s/d +8 Pts)</strong>
                            <span class="text-slate-600">Menyala +3, Keren +4, Bangga +5, Ide +6 (+2 karya portofolio)</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white border border-sky-200">
                            <strong class="text-slate-950 block">👨‍🏫 Respon Guru (2x Lipat!)</strong>
                            <span class="text-slate-600">Reaksi Guru +6 s/d +16 Pts & Komentar Guru +10 Pts</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white border border-indigo-200">
                            <strong class="text-slate-950 block">💬 Komentar (+5 / +2 Pts)</strong>
                            <span class="text-slate-600">Karya dikomentari +5 Pts • Menanggapi teman +2 Pts</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white border border-emerald-200">
                            <strong class="text-slate-950 block">🏅 Lencana Guru (+50 Pts)</strong>
                            <span class="text-slate-600">Keahlian portofolio divalidasi resmi oleh Guru</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white border border-rose-200">
                            <strong class="text-slate-950 block">💌 Buku Tamu (+5 / +10 Pts)</strong>
                            <span class="text-slate-600">Dapat stiker dinding +5 Pts • Stiker di-Pin +10 Pts</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white border border-amber-300">
                            <strong class="text-slate-950 block">🚀 Karya Viral (+20 & +50 Pts)</strong>
                            <span class="text-slate-600">Bonus saat tembus 10 & 25 apresiasi unik</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 1. Story Bar: ⚡ KILAS (STORY 24 JAM) ── --}}
        @if($activeStories->isNotEmpty() || Auth::check())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-4 shadow-xs">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-xs">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <span>Kilas</span>
                    </span>
                    <span class="text-[11px] font-bold text-pink-600 bg-pink-50 border border-pink-200 px-2.5 py-0.5 rounded-full">
                        {{ $activeStories->count() }} kilas aktif ✨
                    </span>
                </div>
                <div class="flex items-center gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    @auth
                        {{-- Bulatan Heboh: Lepas Kilas Baru --}}
                        <button type="button" 
                                @click="document.getElementById('quick-post-box')?.scrollIntoView({behavior: 'smooth'}); setTimeout(() => { const btn = document.getElementById('btn-select-story'); if(btn) btn.click(); }, 300)"
                                class="flex flex-col items-center gap-1.5 shrink-0 group focus:outline-none cursor-pointer"
                                title="Unggah Kilas Baru">
                            <div class="relative w-16 h-16 rounded-full p-[2.5px] border-2 border-dashed border-pink-400 group-hover:scale-105 transition-transform duration-200">
                                <div class="w-full h-full rounded-full p-0.5 bg-white overflow-hidden">
                                    <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-full h-full rounded-full object-cover">
                                </div>
                                <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-pink-600 text-white border-2 border-white flex items-center justify-center text-[10px] font-black shadow-xs">
                                    +
                                </span>
                            </div>
                            <span class="text-[11px] font-black text-slate-950 group-hover:text-pink-600 max-w-[70px] truncate text-center">
                                + Kilas
                            </span>
                        </button>
                    @endauth

                    @php
                        $uniqueStoriesByUser = $activeStories->unique('user_id');
                    @endphp

                    @foreach($uniqueStoriesByUser as $story)
                        @php
                            $firstIndex = $activeStories->search(fn($item) => $item->id === $story->id);
                            $userStoriesCount = $activeStories->where('user_id', $story->user_id)->count();
                        @endphp
                        <button type="button" 
                                @click="openStory({{ $firstIndex }})" 
                                class="flex flex-col items-center gap-1.5 shrink-0 group focus:outline-none cursor-pointer">
                            <div class="relative w-16 h-16 rounded-full p-[3px] pastel-story-ring group-hover:scale-105 transition-transform duration-200 shadow-xs">
                                <div class="w-full h-full rounded-full p-0.5 bg-white overflow-hidden">
                                    <img src="{{ $story->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $story->user->name }}"
                                         class="w-full h-full rounded-full object-cover bg-pink-50">
                                </div>
                                @if($userStoriesCount > 1)
                                    <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-pink-600 text-white border-2 border-white flex items-center justify-center text-[10px] font-black shadow-xs" title="{{ $userStoriesCount }} Kilas aktif">
                                        {{ $userStoriesCount }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] font-extrabold text-slate-900 group-hover:text-pink-600 max-w-[70px] truncate text-center transition-colors">
                                {{ $story->user->circleProfile->nickname ?? explode(' ', $story->user->name)[0] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 2. RUANG EKSPRESI & UNJUK KARYA (KOTAK POSTING CEPAT LANGSUNG DI FEED) ── --}}
        @auth
            @include('circle.partials.post-composer')
        @else
            <div class="bg-gradient-to-r from-pink-50 via-amber-50 to-sky-50 rounded-3xl border-2 border-pink-200 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 text-center sm:text-left">
                    <div class="w-12 h-12 rounded-2xl bg-pink-200 text-pink-700 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <h4 class="font-display font-black text-sm text-slate-950">Ingin Ikutan Curhat & Pamer Karya? ✨</h4>
                        <p class="text-xs font-semibold text-slate-700">Masuk dengan akun siswa atau guru Anda untuk mulai mengunggah portofolio digital dan momen seru.</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" 
                   class="px-5 py-2.5 rounded-2xl text-xs font-black text-black border-2 border-black transition-all hover:scale-105 shrink-0 shadow-md"
                   style="background: linear-gradient(135deg, #ffd600 0%, #34d399 100%) !important; color: #000000 !important; border: 2px solid #000000 !important;">
                    <i class="fas fa-sign-in-alt mr-1"></i> Masuk Sekarang
                </a>
            </div>
        @endauth

        {{-- ── 2B. TANTANGAN HARI INI (DAILY VIRAL PROMPT) ── --}}
        @if(isset($todayChallenge))
            <div class="bg-gradient-to-r from-amber-100 via-pink-100 to-sky-100 rounded-3xl border-2 border-black p-4 sm:p-5 shadow-sm relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="w-12 h-12 rounded-2xl bg-white border-2 border-black flex items-center justify-center text-2xl shadow-xs shrink-0">
                            {{ $todayChallenge['emoji'] }}
                        </span>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-black text-yellow-300">
                                    🎯 TANTANGAN!
                                </span>
                                <span class="text-xs font-black text-slate-950">{{ $todayChallenge['title'] }}</span>
                            </div>
                            <p class="text-xs font-black text-slate-900 mt-1 leading-relaxed">
                                "{{ $todayChallenge['desc'] }}"
                            </p>
                        </div>
                    </div>
                    @auth
                        <button type="button" 
                                onclick="document.getElementById('quick-post-box')?.scrollIntoView({ behavior: 'smooth' }); const ta = document.querySelector('textarea[name=content]'); if(ta) { ta.value = '#TantanganPembda ({{ $todayChallenge['title'] }}): '; ta.focus(); ta.dispatchEvent(new Event('input')); }"
                                class="px-4 py-2 rounded-2xl border-2 border-black bg-white hover:bg-yellow-300 text-slate-950 font-black text-xs transition transform hover:scale-105 active:scale-95 shadow-xs shrink-0 flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
                            <span>Ikutan!</span> 🚀
                        </button>
                    @else
                        <a href="{{ route('login') }}" 
                            class="px-4 py-2 rounded-2xl border-2 border-black bg-white hover:bg-yellow-300 text-slate-950 font-black text-xs transition transform hover:scale-105 active:scale-95 shadow-xs shrink-0 flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
                            <span>Ikutan!</span> 🚀
                        </a>
                    @endauth
                </div>
            </div>
        @endif

        {{-- ── 2C. PINTASAN GRUP & SQUAD SAYA + BANNER LOUNGE KOMUNITAS AKTIF ── --}}
        @php
            $myCommList = isset($myCommunities) ? $myCommunities : collect();
            $countsByType = $communityCountsByType ?? ['all' => 0, 'class' => 0, 'sanggar' => 0, 'extracurricular' => 0, 'vocation' => 0, 'cohort' => 0, 'alumni' => 0, 'teachers' => 0];
            $activeGroupType = $groupTypeFilter ?? request('group_type', 'all');
        @endphp

        <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-black">
                        👥
                    </span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-950 leading-none">
                            Grup Kelas, Sanggar, Ekskul, Rumpun SMK, Angkatan & Alumni
                        </h3>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">
                            Klik grup untuk masuk ke ruang diskusi, berbagi modul, atau pamer karya bersama satu tim!
                        </p>
                    </div>
                </div>
                <a href="{{ route('circle.feed', ['tab' => 'groups']) }}"
                   class="px-3 py-1.5 rounded-xl bg-purple-100 hover:bg-purple-200 text-purple-950 border border-purple-300 text-[11px] font-black transition flex items-center gap-1.5 shrink-0">
                    <span>Jelajahi Semua Grup ({{ $countsByType['all'] ?? 0 }})</span>
                    <i class="fas fa-arrow-right text-[9px]"></i>
                </a>
            </div>

            {{-- Baris Filter Cepat Kategori Grup --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar">
                @php
                    $quickGroupTypes = [
                        'all'             => ['icon' => '🌐', 'label' => 'Semua Grup', 'count' => $countsByType['all'] ?? 0],
                        'class'           => ['icon' => '🏫', 'label' => 'Grup Kelas', 'count' => $countsByType['class'] ?? 0],
                        'sanggar'         => ['icon' => '🎭', 'label' => 'Sanggar & Seni', 'count' => $countsByType['sanggar'] ?? 0],
                        'extracurricular' => ['icon' => '🏆', 'label' => 'Ekstrakurikuler', 'count' => $countsByType['extracurricular'] ?? 0],
                        'vocation'        => ['icon' => '⚙️', 'label' => 'Rumpun SMK', 'count' => $countsByType['vocation'] ?? 0],
                        'cohort'          => ['icon' => '📅', 'label' => 'Angkatan', 'count' => $countsByType['cohort'] ?? 0],
                        'alumni'          => ['icon' => '🎓', 'label' => 'Ikatan Alumni', 'count' => $countsByType['alumni'] ?? 0],
                        'teachers'        => ['icon' => '👨‍🏫', 'label' => 'Ruang Guru', 'count' => $countsByType['teachers'] ?? 0],
                    ];
                @endphp
                @foreach($quickGroupTypes as $gKey => $gMeta)
                    @php
                        $isGActive = ($tabFilter === 'groups' && $activeGroupType === $gKey) || ($tabFilter !== 'groups' && $activeGroupType === $gKey && $gKey !== 'all');
                    @endphp
                    <a href="{{ route('circle.feed', ['tab' => 'groups', 'group_type' => $gKey]) }}"
                       class="px-3 py-1.5 rounded-2xl text-[11px] font-black border transition shrink-0 flex items-center gap-1.5 {{ $isGActive ? 'bg-purple-600 text-white border-purple-700 shadow-xs' : 'bg-slate-50 hover:bg-purple-50 text-slate-800 border-slate-200' }}">
                        <span>{{ $gMeta['icon'] }}</span>
                        <span>{{ $gMeta['label'] }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] {{ $isGActive ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-700' }}">
                            {{ $gMeta['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Pintasan Cepat "Grup Saya" jika sudah login --}}
            @if($myCommList->isNotEmpty())
                <div class="pt-2 border-t border-slate-100 flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-800 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200 shrink-0">
                        ⭐ Grup Saya:
                    </span>
                    @foreach($myCommList as $myComm)
                        @php
                            $isCurrComm = isset($activeCommunity) && $activeCommunity && $activeCommunity->id === $myComm->id;
                        @endphp
                        <a href="{{ route('circle.feed', ['community' => $myComm->slug]) }}"
                           class="px-3 py-1 rounded-xl text-[11px] font-black border transition shrink-0 flex items-center gap-1.5 {{ $isCurrComm ? 'bg-pink-600 text-white border-pink-700 shadow-xs' : 'bg-[#faf8f5] hover:bg-pink-50 text-slate-900 border-[#e8dfd1]' }}">
                            <span>{{ $myComm->display_icon }}</span>
                            <span>{{ $myComm->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── 2D. BANNER LOUNGE GRUP / KOMUNITAS AKTIF (JIKA SEDANG MEMBUKA SUATU GRUP) ── --}}
        @if(isset($activeCommunity) && $activeCommunity)
            @php
                $commTypeInfo = $activeCommunity->type_info;
                $isMemberOfActive = Auth::check() && $activeCommunity->members->contains('user_id', Auth::id());
                $myMemberRecord = Auth::check() ? $activeCommunity->members->firstWhere('user_id', Auth::id()) : null;
                $ekskulRef = $activeCommunity->extracurricular;
                $classroomRef = $activeCommunity->classroom;
                $sectionOptions = $ekskulRef ? ($ekskulRef->section_list ?? []) : [];
            @endphp
            <div x-data="{ showRosterModal: false, showJoinSection: false }"
                 class="bg-gradient-to-br from-purple-50 via-white to-amber-50 rounded-3xl border-2 border-purple-400 p-5 sm:p-6 shadow-md relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="w-16 h-16 rounded-3xl bg-white border-2 border-purple-300 flex items-center justify-center text-3xl shrink-0 shadow-xs">
                            {{ $activeCommunity->display_icon }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $commTypeInfo['badge_bg'] ?? 'bg-purple-100 text-purple-900 border-purple-300' }}">
                                    {{ $commTypeInfo['icon'] }} {{ $commTypeInfo['label'] }}
                                </span>
                                @if($activeCommunity->school)
                                    <span class="px-2.5 py-0.5 rounded-full bg-slate-900 text-white text-[10px] font-black">
                                        🏫 {{ $activeCommunity->school->name }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-950 border border-amber-400 text-[10px] font-black">
                                        🏛️ Lintas Unit Yayasan Pembda
                                    </span>
                                @endif
                                @if($myMemberRecord && $myMemberRecord->title_badge)
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300 text-[10px] font-black">
                                        🎖️ Peranmu: {{ $myMemberRecord->title_badge }}
                                    </span>
                                @endif
                            </div>

                            <h2 class="font-display font-black text-xl sm:text-2xl text-slate-950 leading-tight">
                                {{ $activeCommunity->name }}
                            </h2>
                            <p class="text-xs font-semibold text-slate-700 mt-1 leading-relaxed">
                                {{ $activeCommunity->description }}
                            </p>

                            {{-- Baris Info Pembina / Wali Kelas / Pengurus Inti --}}
                            <div class="mt-3 flex items-center gap-2 flex-wrap text-[11px]">
                                @if($classroomRef && $classroomRef->homeroomTeacher)
                                    <span class="px-2.5 py-1 rounded-xl bg-sky-100 text-sky-950 border border-sky-300 font-black flex items-center gap-1.5">
                                        <span>👨‍🏫 Wali Kelas:</span>
                                        <span>{{ $classroomRef->homeroomTeacher->full_name }}</span>
                                    </span>
                                @endif
                                @if($ekskulRef && $ekskulRef->advisor)
                                    <span class="px-2.5 py-1 rounded-xl bg-purple-100 text-purple-950 border border-purple-300 font-black flex items-center gap-1.5">
                                        <span>🎯 Pembina:</span>
                                        <span>{{ $ekskulRef->advisor->full_name }}</span>
                                    </span>
                                @endif
                                @if($ekskulRef && $ekskulRef->leader)
                                    <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-950 border border-amber-300 font-black flex items-center gap-1.5">
                                        <span>👑 Ketua:</span>
                                        <span>{{ $ekskulRef->leader->full_name }}</span>
                                    </span>
                                @endif
                                @if($ekskulRef && $ekskulRef->secretary)
                                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-950 border border-emerald-200 font-bold flex items-center gap-1">
                                        <span>📝 Sekretaris: {{ $ekskulRef->secretary->full_name }}</span>
                                    </span>
                                @endif
                                @if($ekskulRef && $ekskulRef->treasurer)
                                    <span class="px-2.5 py-1 rounded-xl bg-pink-50 text-pink-950 border border-pink-200 font-bold flex items-center gap-1">
                                        <span>💎 Bendahara: {{ $ekskulRef->treasurer->full_name }}</span>
                                    </span>
                                @endif
                                @if($ekskulRef && $ekskulRef->schedule_info)
                                    <span class="px-2.5 py-1 rounded-xl bg-white text-slate-800 border border-slate-200 font-bold">
                                        🗓️ Jadwal: {{ $ekskulRef->schedule_info }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons Kanan --}}
                    <div class="flex flex-wrap sm:flex-col items-stretch sm:items-end gap-2 shrink-0">
                        <a href="{{ route('circle.feed') }}"
                           class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-black transition flex items-center justify-center gap-1.5">
                            <span>✕ Kembali ke Feed Utama</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    @click="showRosterModal = !showRosterModal"
                                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-950 border-2 border-purple-300 text-xs font-black transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                                <i class="fas fa-users text-purple-600"></i>
                                <span>{{ $activeCommunity->members->count() }} Anggota</span>
                            </button>

                            @auth
                                @if($isMemberOfActive)
                                    <button type="button"
                                            onclick="document.getElementById('quick-post-box')?.scrollIntoView({ behavior: 'smooth' }); const ta = document.querySelector('textarea[name=content]'); if(ta) ta.focus();"
                                            class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black transition cursor-pointer flex items-center gap-1.5 shadow-xs">
                                        <i class="fas fa-pen-to-square"></i>
                                        <span>Tulis di Grup Ini</span>
                                    </button>
                                @else
                                    @if(!empty($sectionOptions))
                                        <button type="button"
                                                @click="showJoinSection = !showJoinSection"
                                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black transition cursor-pointer flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-user-plus"></i>
                                            <span>+ Gabung Sanggar/Ekskul</span>
                                        </button>
                                    @else
                                        <form action="{{ route('circle.community.join', $activeCommunity->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black transition cursor-pointer flex items-center gap-1.5 shadow-xs">
                                                <i class="fas fa-user-plus"></i>
                                                <span>+ Gabung Grup</span>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>

                {{-- Form Pilih Section/Instrumen jika Sanggar / Marching Band --}}
                @if(!empty($sectionOptions) && !$isMemberOfActive)
                    <div x-show="showJoinSection" x-collapse x-transition class="mt-4 pt-4 border-t-2 border-purple-200">
                        <form action="{{ route('circle.community.join', $activeCommunity->id) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 bg-white p-3.5 rounded-2xl border border-purple-200">
                            @csrf
                            <div class="text-xs font-black text-slate-900 shrink-0">🎺 Pilih Posisi / Section di {{ $activeCommunity->name }}:</div>
                            <select name="section" class="flex-1 px-3 py-2 rounded-xl bg-purple-50 border border-purple-300 text-xs font-black text-slate-950">
                                <option value="">-- Anggota Umum --</option>
                                @foreach($sectionOptions as $sec)
                                    <option value="{{ $sec }}">{{ $sec }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black cursor-pointer">
                                ✅ Konfirmasi Gabung (+15 Poin)
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Panel Daftar Anggota (Squad Roster) --}}
                <div x-show="showRosterModal" x-collapse x-transition class="mt-4 pt-4 border-t-2 border-purple-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                            <i class="fas fa-id-badge text-purple-600"></i>
                            <span>Daftar Warga & Pengurus {{ $activeCommunity->name }} ({{ $activeCommunity->members->count() }} Orang)</span>
                        </h4>
                        <button type="button" @click="showRosterModal = false" class="text-xs font-black text-slate-500 hover:text-rose-600 cursor-pointer">
                            ✕ Tutup Daftar
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-72 overflow-y-auto pr-1 custom-scrollbar">
                        @foreach($activeCommunity->members as $mem)
                            @if($mem->user)
                                <a href="{{ route('circle.profile', ['username' => $mem->user->circle_username ?? $mem->user->username ?? 'user-' . $mem->user_id]) }}"
                                   class="p-2.5 rounded-2xl bg-white hover:bg-purple-50 border border-purple-200/80 flex items-center justify-between gap-2 transition shadow-2xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img src="{{ $mem->user->avatar_url ?? asset('images/default-avatar.svg') }}"
                                             class="w-9 h-9 rounded-xl object-cover ring-2 {{ $mem->role === 'leader' ? 'ring-amber-400' : ($mem->role === 'moderator' ? 'ring-purple-400' : 'ring-slate-100') }} shrink-0">
                                        <div class="min-w-0">
                                            <div class="text-xs font-black text-slate-950 truncate">{{ $mem->user->name }}</div>
                                            <div class="text-[10px] font-bold text-slate-500 truncate">
                                                {{ $mem->user->student?->classroom?->name ?? ($mem->user->school?->name ?? 'Pembda') }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black shrink-0 {{ $mem->role === 'leader' ? 'bg-amber-200 text-amber-950' : ($mem->role === 'moderator' ? 'bg-purple-200 text-purple-950' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $mem->title_badge ?: ($mem->role === 'leader' ? '👑 Pembina/Wali' : ($mem->role === 'moderator' ? '⭐ Pengurus' : 'Anggota')) }}
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 3. Filter Tab Bar: Gelora Pastel (1 Kata: Semua, Karya, Nongki, Reels, Grup) ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-3xl border-2 border-[#f1e8dc] p-2.5 shadow-xs">
            {{-- Tabs Pastel Heboh --}}
            <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
                {{-- 1. Semua --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'all', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'all' ? 'bg-pink-100 text-slate-950 border-2 border-pink-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-pink-200 text-pink-800 flex items-center justify-center text-[10px]">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <span>Semua</span>
                </a>
                {{-- 2. Karya --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'portfolio', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'portfolio' ? 'bg-amber-100 text-slate-950 border-2 border-amber-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center text-[10px]">
                        <i class="fas fa-trophy"></i>
                    </span>
                    <span>Karya</span>
                </a>
                {{-- 3. Nongki --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'wall', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'wall' ? 'bg-[#faedcd] text-slate-950 border-2 border-[#d4a373] shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-[#e6ccb2] text-[#7f5539] flex items-center justify-center text-[10px]">
                        <i class="fas fa-mug-hot"></i>
                    </span>
                    <span>Nongki</span>
                </a>
                {{-- 4. Reels --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'reels', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'reels' ? 'bg-sky-100 text-slate-950 border-2 border-sky-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-sky-200 text-sky-800 flex items-center justify-center text-[10px]">
                        <i class="fas fa-play"></i>
                    </span>
                    <span>Reels</span>
                </a>
                {{-- 5. Galeri Media --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'gallery', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'gallery' ? 'bg-purple-100 text-slate-950 border-2 border-purple-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-purple-200 text-purple-800 flex items-center justify-center text-[10px]">
                        <i class="fas fa-photo-film"></i>
                    </span>
                    <span>Galeri</span>
                </a>
                {{-- 6. Wahana Inisiatif Siswa (Teka-Teki, Duel, Duet, Pantun) --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'interactive', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'interactive' ? 'bg-gradient-to-r from-violet-600 to-pink-600 text-white border-2 border-black shadow-xs' : 'text-violet-800 hover:bg-violet-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-[10px]">
                        <i class="fas fa-sparkles"></i>
                    </span>
                    <span>Wahana Inisiatif</span>
                </a>
                {{-- 7. Liputan Live --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'live', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'live' ? 'bg-rose-600 text-white border-2 border-black shadow-xs' : 'text-rose-700 hover:bg-rose-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-[10px] relative">
                        <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping absolute"></span>
                        <span class="w-2 h-2 rounded-full bg-rose-600 relative"></span>
                    </span>
                    <span>Liputan LIVE</span>
                </a>
                {{-- 7. Direktori Grup & Squad --}}
                <a href="{{ route('circle.feed', array_filter(['tab' => 'groups', 'q' => request('q')])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'groups' ? 'bg-indigo-600 text-white border-2 border-black shadow-xs' : 'text-indigo-800 hover:bg-indigo-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px]">
                        <i class="fas fa-users"></i>
                    </span>
                    <span>Grup & Squad</span>
                </a>
            </div>

            {{-- Mobile Search (Pencarian Rapi Tanpa Pemisah Sekolah) --}}
            <div class="sm:hidden border-t border-[#f1e8dc] pt-2 w-full">
                <form action="{{ route('circle.feed') }}" method="GET" class="relative w-full">
                    @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari karya, siswa, keahlian..."
                           class="w-full pl-8 pr-7 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-300">
                    <span class="absolute left-2.5 top-2.5 text-xs text-slate-400">🔍</span>
                </form>
            </div>
        </div>

        {{-- ── Banner Hasil Pencarian Gelora ── --}}
        @if(!empty($search))
            <div class="bg-gradient-to-r from-amber-50 via-pink-50 to-sky-50 rounded-3xl border-2 border-amber-300 p-4 sm:p-5 shadow-xs">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-amber-200 text-amber-900 flex items-center justify-center text-sm font-black shadow-2xs">
                            🔍
                        </span>
                        <div>
                            <h4 class="font-display font-black text-sm text-slate-950">
                                Hasil Pencarian Gelora: "<span class="text-pink-600 font-extrabold">{{ $search }}</span>"
                            </h4>
                            <p class="text-xs font-bold text-slate-700">
                                Ditemukan {{ $posts->total() }} karya & cerita yang relevan.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('circle.feed', array_filter(['tab' => request('tab')])) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-rose-50 border-2 border-rose-200 text-rose-700 text-xs font-black transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <span>✕ Bersihkan</span>
                    </a>
                </div>

                {{-- Warga Pembda Terkait yang Cocok --}}
                @if(isset($matchedProfiles) && $matchedProfiles->isNotEmpty())
                    <div class="mt-3.5 pt-3 border-t border-amber-200/80">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-900 block mb-2">Warga Pembda Terkait:</span>
                        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 custom-scrollbar">
                            @foreach($matchedProfiles as $mp)
                                <a href="{{ route('circle.profile', ['username' => $mp->username]) }}" 
                                   class="px-3 py-1.5 rounded-2xl bg-white border border-amber-200 hover:border-pink-300 text-xs flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                    <img src="{{ $mp->user->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-6 h-6 rounded-full object-cover">
                                    <div class="text-left">
                                        <div class="font-black text-slate-950 leading-tight">{{ $mp->nickname ?? $mp->user->name }}</div>
                                        <div class="text-[10px] text-slate-600">{{ $mp->user->school?->name ?? 'Pembda' }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ── 3A. DIREKTORI LENGKAP GRUP & SQUAD (Jika Tab = 'groups') ── --}}
        @if($tabFilter === 'groups')
            <div x-data="{ showCreateGroupModal: false }" class="bg-white rounded-3xl border-2 border-indigo-200 p-5 sm:p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-900 text-[10px] font-black uppercase tracking-wider mb-1">
                            <span>🏛️ Direktori Komunitas Resmi PembdaCIRCLE</span>
                        </div>
                        <h3 class="font-display font-black text-lg sm:text-xl text-slate-950">
                            Katalog Grup Kelas, Sanggar Seni, Ekskul, Rumpun SMK, Angkatan & Alumni
                        </h3>
                        <p class="text-xs font-semibold text-slate-600">
                            Menampilkan {{ isset($allCommunities) ? $allCommunities->count() : 0 }} ruang komunitas aktif yang terhubung langsung dengan data akademik & kesiswaan.
                        </p>
                    </div>
                    @auth
                        <button type="button"
                                @click="showCreateGroupModal = !showCreateGroupModal"
                                class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black transition cursor-pointer flex items-center gap-1.5 shrink-0 shadow-xs">
                            <i class="fas fa-plus-circle"></i>
                            <span>+ Buat Grup / Squad Baru</span>
                        </button>
                    @endauth
                </div>

                {{-- Form Buat Grup / Squad / Sanggar / Angkatan Baru --}}
                @auth
                    <div x-show="showCreateGroupModal" x-collapse x-transition class="p-4 rounded-2xl bg-indigo-50/80 border-2 border-indigo-300 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-wider text-indigo-950">✨ Buat Grup / Squad / Sanggar / Komunitas Baru</h4>
                            <button type="button" @click="showCreateGroupModal = false" class="text-xs font-black text-slate-500 hover:text-rose-600 cursor-pointer">✕ Batal</button>
                        </div>
                        <form action="{{ route('circle.community.create') }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-black text-slate-900 mb-1">Nama Grup / Komunitas:</label>
                                    <input type="text" name="name" required placeholder="Contoh: Sanggar Tari Nias / Klub Robotik / Ikatan Alumni 2020..."
                                           class="w-full px-3.5 py-2 rounded-xl bg-white border border-indigo-200 text-xs font-bold text-slate-950">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-slate-900 mb-1">Kategori Grup:</label>
                                    <select name="type" class="w-full px-3 py-2 rounded-xl bg-white border border-indigo-200 text-xs font-black text-slate-950">
                                        <option value="sanggar">🎭 Sanggar & Seni Budaya</option>
                                        <option value="extracurricular">🏆 Ekstrakurikuler & Klub</option>
                                        <option value="vocation">⚙️ Rumpun Kejuruan SMK</option>
                                        <option value="cohort">📅 Grup Angkatan</option>
                                        <option value="alumni">🎓 Ikatan Alumni (IKA)</option>
                                        <option value="squad">🚀 Squad / Kelompok Belajar</option>
                                        @if(Auth::user()->role === 'guru' || Auth::user()->isSuperAdmin())
                                            <option value="teachers">👨‍🏫 Ruang Guru / MGMP</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-black text-slate-900 mb-1">Ikon Emoji (Opsional):</label>
                                    <input type="text" name="icon" placeholder="Contoh: 🎭 / 🎺 / 🚀" maxlength="8"
                                           class="w-full px-3.5 py-2 rounded-xl bg-white border border-indigo-200 text-xs font-bold text-slate-950">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-slate-900 mb-1">Tahun Angkatan (Jika Grup Angkatan/Alumni):</label>
                                    <input type="number" name="cohort_year" placeholder="Contoh: 2024" min="1980" max="2035"
                                           class="w-full px-3.5 py-2 rounded-xl bg-white border border-indigo-200 text-xs font-bold text-slate-950">
                                </div>
                                <div class="flex items-end">
                                    <label class="flex items-center gap-2 text-xs font-black text-slate-900 cursor-pointer p-2 rounded-xl bg-white border border-indigo-200 w-full">
                                        <input type="checkbox" name="is_foundation" value="1" class="rounded text-indigo-600">
                                        <span>🏛️ Lintas Unit (Yayasan)</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-slate-900 mb-1">Deskripsi Singkat & Jadwal/Visi Grup:</label>
                                <textarea name="description" rows="2" placeholder="Jelaskan kegiatan, jadwal latihan, atau tujuan grup ini..."
                                          class="w-full p-3 rounded-xl bg-white border border-indigo-200 text-xs font-bold text-slate-950 resize-none"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black cursor-pointer shadow-xs">
                                    🚀 Aktifkan Grup Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                @endauth

                {{-- Grid Kartu Komunitas --}}
                @if(isset($allCommunities) && $allCommunities->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($allCommunities as $comm)
                            @php
                                $tInfo = $comm->type_info;
                                $isJoined = $myCommList->contains('id', $comm->id);
                            @endphp
                            <div class="p-4 rounded-3xl bg-[#faf8f5] hover:bg-white border-2 border-[#e8dfd1] hover:border-purple-300 transition-all flex flex-col justify-between gap-3 shadow-2xs hover:shadow-md">
                                <div class="flex items-start gap-3.5">
                                    <a href="{{ route('circle.feed', ['community' => $comm->slug]) }}"
                                       class="w-13 h-13 rounded-2xl bg-white border-2 border-purple-200 flex items-center justify-center text-2xl shrink-0 shadow-2xs hover:scale-105 transition">
                                        {{ $comm->display_icon }}
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border {{ $tInfo['badge_bg'] ?? 'bg-purple-100 text-purple-900 border-purple-300' }}">
                                                {{ $tInfo['label'] }}
                                            </span>
                                            @if($comm->school)
                                                <span class="px-2 py-0.5 rounded-md bg-slate-200/80 text-slate-800 text-[9px] font-bold">
                                                    {{ $comm->school->name }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[9px] font-bold">
                                                    🏛️ Yayasan
                                                </span>
                                            @endif
                                        </div>
                                        <a href="{{ route('circle.feed', ['community' => $comm->slug]) }}"
                                           class="font-display font-black text-sm text-slate-950 hover:text-purple-700 transition-colors block leading-snug">
                                            {{ $comm->name }}
                                        </a>
                                        <p class="text-[11px] font-semibold text-slate-600 line-clamp-2 mt-1 leading-relaxed">
                                            {{ $comm->description }}
                                        </p>
                                    </div>
                                </div>

                                <div class="pt-2.5 border-t border-[#ece3d5] flex items-center justify-between gap-2 text-[11px]">
                                    <div class="flex items-center gap-2 font-bold text-slate-600">
                                        <span>👥 {{ number_format($comm->members_count) }} Anggota</span>
                                        <span>•</span>
                                        <span>💬 {{ number_format($comm->posts_count) }} Post</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @auth
                                            @if(!$isJoined)
                                                <form action="{{ route('circle.community.join', $comm->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-950 border border-emerald-300 text-[10px] font-black cursor-pointer">
                                                        + Gabung
                                                    </button>
                                                </form>
                                            @else
                                                <span class="px-2 py-1 rounded-xl bg-purple-100 text-purple-900 text-[10px] font-black">
                                                    ✓ Anggota
                                                </span>
                                            @endif
                                        @endauth
                                        <a href="{{ route('circle.feed', ['community' => $comm->slug]) }}"
                                           class="px-3 py-1 rounded-xl bg-slate-900 hover:bg-purple-700 text-white text-[10px] font-black transition">
                                            Buka Ruang ➔
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-slate-500 text-xs font-bold">
                        Belum ada grup pada kategori ini. Klik tombol "+ Buat Grup / Squad Baru" untuk memulai!
                    </div>
                @endif
            </div>
        @endif

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
            @if($tabFilter === 'reels')
                {{-- ═══════════════════════════════════════════
                     REELS EXPLORE GRID (TIKTOK / INSTAGRAM 9:16)
                ═══════════════════════════════════════════ --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($posts as $post)
                        @php $m = $post->media->first(); @endphp
                        <div id="post-{{ $post->id }}" class="group relative rounded-3xl overflow-hidden aspect-[9/16] bg-slate-950 border-2 {{ request('post') == $post->id ? 'border-amber-400 ring-4 ring-amber-300 shadow-2xl' : 'border-[#f1e8dc]' }} shadow-md hover:shadow-xl hover:border-sky-300 transition-all duration-300 flex flex-col justify-between p-3.5">
                            
                            {{-- Background Video / Image --}}
                            <div class="absolute inset-0 z-0">
                                @if($m && $m->media_type === 'video')
                                    <video src="{{ $m->url }}" muted loop playsinline class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"></video>
                                @elseif($m && $m->media_type === 'image')
                                    <img src="{{ $m->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-indigo-900 to-sky-900 flex items-center justify-center p-4 text-center">
                                        <span class="text-white text-xs font-black">{{ $post->title ?? 'Reels Karya' }}</span>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-black/40"></div>
                            </div>

                            {{-- Top Pill: School & Reel badge & Share & Delete --}}
                            <div class="relative z-10 flex items-center justify-between gap-1.5">
                                <span class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider bg-sky-500 text-white shadow-2xs flex items-center gap-1">
                                    <i class="fas fa-play text-[8px]"></i>
                                    <span>Lensa Gerak</span>
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" 
                                            onclick="event.stopPropagation(); shareCirclePost({{ Js::from($post->share_payload) }})"
                                            class="w-6 h-6 rounded-lg bg-black/60 hover:bg-amber-500 text-white flex items-center justify-center text-[10px] transition cursor-pointer" 
                                            title="Siarkan Lensa Gerak ini">
                                        <i class="fas fa-share-nodes"></i>
                                    </button>
                                    @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isSuperAdmin()))
                                        <form action="{{ route('circle.post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Hapus reels ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-6 h-6 rounded-lg bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-[10px] transition cursor-pointer" title="Hapus Reels">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Center Play Icon --}}
                            <div class="relative z-10 flex items-center justify-center my-auto opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="w-12 h-12 rounded-full bg-white/30 backdrop-blur-sm text-white flex items-center justify-center text-lg shadow-lg">
                                    <i class="fas fa-play ml-1"></i>
                                </div>
                            </div>

                            {{-- Bottom Info & Stats --}}
                            <div class="relative z-10 text-white">
                                <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
                                   class="flex items-center gap-1.5 mb-1.5 group-hover:underline">
                                    <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-5 h-5 rounded-full object-cover border border-white/50">
                                    <span class="text-[11px] font-black truncate max-w-[110px]">{{ $post->user->circleProfile->nickname ?? $post->user->name }}</span>
                                </a>
                                <h4 class="text-xs font-black line-clamp-2 leading-tight text-white mb-2">{{ $post->title ?? $post->content }}</h4>
                                <div class="flex items-center justify-between text-[10px] text-slate-300 pt-1.5 border-t border-white/20">
                                    <span class="flex items-center gap-1 font-bold">
                                        <span>🔥</span>
                                        <span>{{ $post->likes_count }}</span>
                                    </span>
                                    <span class="flex items-center gap-1 font-bold">
                                        <i class="far fa-comment-dots"></i>
                                        <span>{{ $post->comments_count }}</span>
                                    </span>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
                {{-- Pagination Links --}}
                <div class="pt-4 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="space-y-6">
                    @foreach($posts as $post)
                        <article id="post-{{ $post->id }}" x-data="{ showComments: false }" class="bg-white rounded-3xl border-2 {{ request('post') == $post->id ? 'border-pink-500 ring-4 ring-pink-300 shadow-xl' : 'border-[#f1e8dc]' }} p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-pink-200 transition-all duration-200">
                            {{-- Post Header (Author Info & Badges) --}}
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
                                       class="shrink-0 focus:outline-none group">
                                        <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                             alt="{{ $post->user->name }}"
                                             class="w-12 h-12 rounded-2xl object-cover ring-2 ring-pink-100 group-hover:ring-pink-400 transition-all">
                                    </a>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
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
                                            @if(($post->user->reputation?->total_points ?? 0) > 0)
                                                <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id, 'tab' => 'points']) }}"
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 hover:bg-amber-200 border border-amber-300 text-amber-950 text-[10px] font-black transition shadow-2xs"
                                                   title="Total Poin Reputasi ({{ $post->user->reputation->level_name }}) — Klik untuk melihat Wall Jejak Poin">
                                                    <span>⚡</span>
                                                    <span>{{ number_format($post->user->reputation->total_points) }} Pts</span>
                                                </a>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5 flex-wrap mt-0.5">
                                            <span>{{ $post->created_at->diffForHumans() }}</span>
                                            @if($post->community)
                                                <span>•</span>
                                                <a href="{{ route('circle.feed', ['community' => $post->community->slug]) }}"
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-100 hover:bg-purple-200 text-purple-950 border border-purple-300 text-[10px] font-black transition">
                                                    <span>{{ $post->community->display_icon }}</span>
                                                    <span>{{ $post->community->name }}</span>
                                                </a>
                                            @endif
                                            @if($post->school)
                                                <span>•</span>
                                                <span class="text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">{{ $post->school->name }}</span>
                                            @endif
                                            @if($post->location_tag)
                                                <span>•</span>
                                                <span class="text-pink-700 font-bold"><i class="fas fa-location-dot text-[9px] mr-0.5"></i>{{ $post->location_tag }}</span>
                                            @endif
                                        </div>
                                        {{-- Lencana Identitas Akademik, Kelas, Sanggar, Ekskul, Angkatan & Alumni --}}
                                        @php
                                            $authorBadges = $post->user->circle_identity_badges ?? [];
                                        @endphp
                                        @if(!empty($authorBadges))
                                            <div class="flex items-center gap-1 flex-wrap mt-1">
                                                @foreach(array_slice($authorBadges, 0, 4) as $ab)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-md border text-[9.5px] font-black {{ $ab['color'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                                        <span>{{ $ab['icon'] ?? '' }}</span>
                                                        <span>{{ $ab['label'] }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Category Pill Pastel & Post Deletion --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($post->is_live || $post->post_type === 'live')
                                        @php $isLiveActive = in_array($post->live_status ?? 'live', ['live', 'active']); @endphp
                                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 {{ $isLiveActive ? 'bg-rose-600 text-white border border-black animate-pulse' : 'bg-slate-800 text-white border border-slate-700' }} flex items-center gap-1.5 shadow-2xs">
                                            <span class="w-2 h-2 rounded-full {{ $isLiveActive ? 'bg-white' : 'bg-emerald-400' }}"></span>
                                            <span>{{ $isLiveActive ? 'LIPUTAN LIVE' : 'REKAMAN LIVE' }}</span>
                                        </span>
                                    @elseif($post->post_type === 'report' || $post->event_name)
                                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 bg-emerald-100 text-emerald-950 border border-emerald-300 flex items-center gap-1 shadow-2xs">
                                            <i class="fas fa-clipboard-check text-emerald-700"></i>Laporan Kegiatan
                                        </span>
                                    @elseif($post->is_gallery || $post->tab_category === 'gallery')
                                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 bg-purple-100 text-purple-950 border border-purple-300 flex items-center gap-1 shadow-2xs">
                                            @if($post->gallery_category === 'video')
                                                <i class="fas fa-video text-purple-700"></i>Galeri Video
                                            @elseif($post->gallery_category === 'audio')
                                                <i class="fas fa-music text-purple-700"></i>Galeri Lagu
                                            @else
                                                <i class="fas fa-image text-purple-700"></i>Galeri Foto
                                            @endif
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 {{ $post->tab_category === 'portfolio' ? 'bg-amber-100 text-amber-950 border border-amber-300' : 'bg-[#faedcd] text-[#7f5539] border border-[#d4a373]' }}">
                                            @if($post->tab_category === 'portfolio')
                                                <i class="fas fa-trophy mr-1 text-amber-700"></i>Reka Cipta
                                            @else
                                                <i class="fas fa-mug-hot mr-1 text-[#7f5539]"></i>Cengkrama
                                            @endif
                                        </span>
                                    @endif

                                    @php
                                        $canModeratePost = Auth::check() && (
                                            Auth::id() === $post->user_id || 
                                            Auth::user()->isSuperAdmin() || 
                                            Auth::user()->isAdminSekolah() || 
                                            Auth::user()->isGuru()
                                        );
                                    @endphp
                                    @if($canModeratePost)
                                        <form action="{{ route('circle.post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Hapus postingan ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-7 h-7 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 flex items-center justify-center text-xs transition cursor-pointer border border-slate-200" title="{{ Auth::id() === $post->user_id ? 'Hapus Postingan Saya' : 'Moderasi / Hapus Postingan (Guru/Admin)' }}">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                        {{-- Auto-Broadcast Ribbon if Gallery Post --}}
                        @if($post->is_gallery || $post->tab_category === 'gallery')
                            <div class="mb-3 px-3.5 py-1.5 rounded-2xl bg-gradient-to-r from-purple-100 via-pink-100 to-amber-100 border border-purple-200 text-slate-900 text-xs font-black flex items-center gap-2 shadow-2xs">
                                <span class="text-sm">✨</span>
                                <span>Baru saja memajang {{ $post->gallery_category === 'video' ? 'video' : ($post->gallery_category === 'audio' ? 'lagu/rekaman audio' : 'foto') }} baru di Galeri Pribadi!</span>
                            </div>
                        @endif

                        {{-- Mood Indicator Pastel Kuning --}}
                        @if($post->mood_text)
                            <div class="mb-3.5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-slate-950 text-xs font-black shadow-2xs">
                                <span class="text-base">{{ $post->mood_emoji ?? '✨' }}</span>
                                <span>{{ $post->mood_text }}</span>
                            </div>
                        @endif

                        {{-- Unified Post Body (Live Report, Event GPS Card, Top/Bottom Layout, Smart Multi-Photo Collage & Lightbox) --}}
                        @include('circle.partials.post-card-body', ['post' => $post])

                        {{-- Polling Interaktif Warga Pembda --}}
                        @if($post->poll)
                            @php
                                $poll = $post->poll;
                                $userVotedOptionId = $poll->hasVoted(Auth::id(), request()->ip());
                                $totalVotes = $poll->totalVotes();
                            @endphp
                            <div x-data="{
                                    votedOptionId: {{ $userVotedOptionId ?? 'null' }},
                                    totalVotes: {{ $totalVotes }},
                                    options: {{ json_encode($poll->options->map(function($o) use ($poll, $userVotedOptionId) {
                                        return [
                                            'id' => $o->id,
                                            'option_text' => $o->option_text,
                                            'votes_count' => $o->votes_count,
                                            'percentage' => $o->percentage(),
                                            'is_selected' => $userVotedOptionId == $o->id,
                                        ];
                                    })) }},
                                    isVoting: false,
                                    async vote(optId) {
                                        if (this.votedOptionId || this.isVoting) return;
                                        this.isVoting = true;
                                        try {
                                            const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                                            const res = await fetch('{{ route('circle.poll.vote') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': csrf,
                                                },
                                                body: JSON.stringify({
                                                    circle_poll_id: {{ $poll->id }},
                                                    circle_poll_option_id: optId
                                                })
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.status === 'success') {
                                                this.votedOptionId = data.voted_option_id;
                                                this.totalVotes = data.total_votes;
                                                this.options = data.options;
                                            } else {
                                                alert(data.message || 'Terjadi kesalahan');
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.isVoting = false;
                                        }
                                    }
                                 }" 
                                 class="mb-4 p-4 rounded-3xl bg-purple-50/70 border-2 border-purple-200">
                                
                                <div class="flex items-center justify-between mb-3 px-1">
                                    <span class="text-xs font-black uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-lg bg-purple-200 text-purple-800 flex items-center justify-center text-[10px]">📊</span>
                                        <span>Jajak Pendapat Warga Pembda</span>
                                    </span>
                                    <span class="text-[11px] font-bold text-purple-900 bg-purple-100 border border-purple-300 px-2.5 py-0.5 rounded-full"
                                          x-text="totalVotes + ' Suara'">
                                        {{ $totalVotes }} Suara
                                    </span>
                                </div>

                                <h4 class="font-display font-black text-sm text-slate-950 mb-3 leading-snug">
                                    {{ $poll->question }}
                                </h4>

                                <div class="space-y-2">
                                    <template x-for="(opt, oIdx) in options" :key="opt.id">
                                        <div class="relative">
                                            {{-- Tombol jika belum vote --}}
                                            <template x-if="!votedOptionId">
                                                <button type="button" @click="vote(opt.id)" :disabled="isVoting"
                                                        class="w-full text-left px-4 py-2.5 rounded-2xl bg-white hover:bg-purple-100 border-2 border-purple-200 hover:border-purple-300 text-xs font-black text-slate-950 transition-all flex items-center justify-between group cursor-pointer hover:scale-[1.01] shadow-2xs">
                                                    <span x-text="opt.option_text"></span>
                                                    <i class="fas fa-chevron-right text-[10px] text-purple-400 group-hover:text-purple-700"></i>
                                                </button>
                                            </template>

                                            {{-- Tampilan persentase jika sudah vote --}}
                                            <template x-if="votedOptionId">
                                                <div class="relative overflow-hidden rounded-2xl bg-white border-2 p-2.5 text-xs font-black text-slate-950 shadow-2xs"
                                                     :class="opt.is_selected ? 'border-purple-400 ring-2 ring-purple-200' : 'border-slate-200'">
                                                    {{-- Progress Bar Pastel --}}
                                                    <div class="absolute inset-y-0 left-0 transition-all duration-700 rounded-xl"
                                                         :class="oIdx === 0 ? 'bg-pink-200/80' : (oIdx === 1 ? 'bg-amber-200/80' : (oIdx === 2 ? 'bg-emerald-200/80' : 'bg-sky-200/80'))"
                                                         :style="'width: ' + opt.percentage + '%'"></div>
                                                    
                                                    <div class="relative z-10 flex items-center justify-between px-1.5">
                                                        <div class="flex items-center gap-2">
                                                            <template x-if="opt.is_selected">
                                                                <span class="w-4 h-4 rounded-full bg-purple-600 text-white flex items-center justify-center text-[8px]">✓</span>
                                                            </template>
                                                            <span x-text="opt.option_text"></span>
                                                        </div>
                                                        <span class="text-xs font-black text-slate-950" x-text="opt.percentage + '%'"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endif

                        {{-- Action & Reaction Bar (Interactive AJAX & Lucu) --}}
                        <div class="flex items-center justify-between pt-3.5 border-t-2 border-[#f8f4ed] text-xs">
                            {{-- Chunky Pastel Reaction Buttons --}}
                            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                                {{-- Menyala! 🔥 (Oranye Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'lit')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">🔥</span>
                                    <span class="reaction-count-{{ $post->id }}">{{ $post->likes_count > 0 ? $post->likes_count : 'Menyala!' }}</span>
                                </button>
                                {{-- Keren Pol! 👏 (Hijau Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'clap')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">👏</span>
                                    <span class="hidden sm:inline">Keren Pol!</span>
                                </button>
                                {{-- Memicu Ide! 💡 (Kuning Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'nongki')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">💡</span>
                                    <span class="hidden sm:inline">Memicu Ide!</span>
                                </button>
                                {{-- Bangga! ❤️ (Pink Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'love')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">❤️</span>
                                    <span class="hidden sm:inline">Bangga!</span>
                                </button>
                            </div>

                            {{-- Comments, Share & Profile Link --}}
                            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0 flex-wrap">
                                <button type="button" 
                                        @click="showComments = !showComments"
                                        class="text-xs font-bold text-slate-800 hover:text-pink-600 flex items-center gap-1.5 cursor-pointer px-2.5 py-1.5 rounded-xl bg-slate-50 hover:bg-pink-50 transition border border-slate-200/80">
                                    <i class="far fa-comment-dots text-pink-500"></i>
                                    <span>{{ $post->comments_count }} <span class="hidden sm:inline">Komentar</span></span>
                                </button>
                                <button type="button" 
                                        onclick="shareCirclePost({{ Js::from($post->share_payload) }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-slate-950 border border-amber-300 font-black transition-all text-xs cursor-pointer hover:scale-105 shadow-2xs"
                                        title="Siarkan karya/postingan ini ke WhatsApp atau media sosial">
                                    <i class="fas fa-share-nodes text-amber-800 text-[11px]"></i>
                                    <span>Siarkan</span>
                                </button>
                                @if($post->is_gallery || $post->tab_category === 'gallery')
                                    <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id, 'tab' => 'gallery']) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-100 hover:bg-purple-200 text-purple-950 font-black transition-all text-xs border border-purple-300 shadow-2xs hover:scale-105">
                                        <i class="fas fa-photo-film text-purple-700 text-[10px]"></i>
                                        <span>Buka Galeri</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                @else
                                    <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-pink-100 hover:bg-pink-200 text-pink-900 font-black transition-colors text-xs border border-pink-200">
                                        <span>Lihat</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                @endif
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
        @endif

    </div>

    {{-- ═══════════════════════════════════════════
         KOLOM KANAN: SIDEBAR WIDGETS (PASTEL CANTIK)
    ═══════════════════════════════════════════ --}}
    <div class="hidden lg:block space-y-6">

        {{-- ── 1. Hero Widget: Identity & Mission (Flagship CONNECT • CREATE • GROW 100% Pastel) ── --}}
        <div class="relative overflow-hidden rounded-3xl p-5 sm:p-6 border-2 border-pink-200/90 shadow-2xs"
             style="background: linear-gradient(135deg, #fdf2f8 0%, #faedcd 40%, #e0f2fe 80%, #dcfce7 100%) !important;">
            <div class="absolute -top-16 -right-16 w-40 h-40 bg-pink-200/50 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-sky-200/50 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <!-- Header with Animated Pastel Emblem -->
                <div class="flex items-center gap-3.5 mb-4">
                    <x-circle-logo size="md" variant="emblem" />
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/90 border border-pink-200 text-pink-700 text-[9px] font-black tracking-wider uppercase mb-1 shadow-2xs">
                            <span class="animate-pulse">✨</span>
                            <span>The 1st Edu-Social Network</span>
                        </div>
                        <h3 class="font-display font-black text-lg text-slate-900 leading-tight">
                            Pembda<span class="circle-gradient-text ml-1">CIRCLE</span>
                        </h3>
                    </div>
                </div>

                <!-- Motto Badge Banner (Pastel) -->
                <div class="p-2.5 rounded-2xl bg-white/80 border border-pink-200/80 mb-4 flex items-center justify-between text-[10px] font-black tracking-wider uppercase shadow-2xs">
                    <span class="text-sky-700">CONNECT</span>
                    <span class="text-pink-300">•</span>
                    <span class="text-rose-600">CREATE</span>
                    <span class="text-pink-300">•</span>
                    <span class="text-emerald-700">GROW</span>
                </div>

                <!-- 3 Pillars Explanation (Pastel Cards) -->
                <div class="space-y-2 mb-4 text-xs">
                    <div class="flex items-start gap-2.5 p-2.5 rounded-2xl bg-white/85 border border-sky-200/80 shadow-2xs">
                        <span class="w-7 h-7 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-sm shrink-0 font-black">🌐</span>
                        <div class="leading-snug">
                            <strong class="text-sky-800 font-black text-[11px] block">CONNECT</strong>
                            <span class="text-[10px] text-slate-600 font-bold">Terhubung erat dengan siswa, guru 3 unit sekolah (SMP, SMA, SMK) & mitra DUDI.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 p-2.5 rounded-2xl bg-white/85 border border-rose-200/80 shadow-2xs">
                        <span class="w-7 h-7 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm shrink-0 font-black">💡</span>
                        <div class="leading-snug">
                            <strong class="text-rose-800 font-black text-[11px] block">CREATE</strong>
                            <span class="text-[10px] text-slate-600 font-bold">Rilis project kejuruan SMK, riset SMA, seni, hingga video reels portofolio.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 p-2.5 rounded-2xl bg-white/85 border border-emerald-200/80 shadow-2xs">
                        <span class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm shrink-0 font-black">🚀</span>
                        <div class="leading-snug">
                            <strong class="text-emerald-800 font-black text-[11px] block">GROW</strong>
                            <span class="text-[10px] text-slate-600 font-bold">Raih Lencana Keahlian terverifikasi guru pembina & melesat menjadi Jawara!</span>
                        </div>
                    </div>
                </div>

                <!-- Privacy Guarantee (Pastel Mint) -->
                <div class="p-2.5 rounded-2xl bg-white/90 border border-emerald-200 text-[10.5px] font-bold text-emerald-800 flex items-center gap-2 shadow-2xs">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <i class="fas fa-shield-heart text-xs"></i>
                    </span>
                    <span>Nilai akademik, rapor & BK tetap 100% aman & privat.</span>
                </div>
            </div>
        </div>

        {{-- ── 1A. WIDGET GRUP & SQUAD KOMUNITAS + PINTASAN PEMBDACHALLENGE ── --}}
        <div class="bg-white rounded-3xl border-2 border-purple-200 p-5 shadow-xs space-y-3.5">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs">
                        🏛️
                    </span>
                    <span>Ruang Grup & Squad</span>
                </h3>
                <a href="{{ route('circle.feed', ['tab' => 'groups']) }}" class="text-[10px] font-black text-purple-700 hover:underline">
                    Semua ({{ $countsByType['all'] ?? 0 }}) ➔
                </a>
            </div>

            @php
                $sidebarComms = ($myCommList && $myCommList->isNotEmpty())
                    ? $myCommList->take(6)
                    : (isset($allCommunities) ? $allCommunities->take(6) : collect());
            @endphp

            @if($sidebarComms->isNotEmpty())
                <div class="space-y-2">
                    @foreach($sidebarComms as $sComm)
                        <a href="{{ route('circle.feed', ['community' => $sComm->slug]) }}"
                           class="flex items-center justify-between p-2.5 rounded-2xl bg-[#faf8f5] hover:bg-purple-50 border border-[#ece3d5] hover:border-purple-200 transition group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-8 h-8 rounded-xl bg-white border border-purple-200 flex items-center justify-center text-base shrink-0">
                                    {{ $sComm->display_icon }}
                                </span>
                                <div class="min-w-0">
                                    <div class="text-xs font-black text-slate-950 truncate group-hover:text-purple-700">
                                        {{ $sComm->name }}
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-500 truncate">
                                        {{ $sComm->type_info['label'] }} • 👥 {{ $sComm->members_count }}
                                    </div>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-400 group-hover:text-purple-600"></i>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Pintasan Arena Game Edukasi PembdaCHALLENGE --}}
            @if(Route::has('forum.games'))
                <a href="{{ route('forum.games') }}"
                   class="block p-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white shadow-sm hover:scale-[1.02] transition transform">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg shrink-0">
                                🎮
                            </span>
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-wider text-amber-300">Arena Game Edukasi</div>
                                <div class="text-xs font-black leading-tight">PembdaCHALLENGE (5 Game)</div>
                                <div class="text-[10px] text-white/85 font-semibold">Math Blitz, Circuit Master, Nusantara</div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-white text-slate-950 text-[10px] font-black shrink-0">
                            Main ➔
                        </span>
                    </div>
                </a>
            @endif
        </div>

        {{-- ── 1B. 👑 JAWARA! (LEADERBOARD MENYALA) ── --}}
        @if(isset($topStudents) && $topStudents->isNotEmpty())
            <div class="bg-gradient-to-br from-amber-50 via-white to-pink-50 rounded-3xl border-2 border-amber-300 p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between mb-3.5 px-0.5">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center text-xs font-black">
                            👑
                        </span>
                        <span>Jawara!</span>
                    </h3>
                    <span class="text-[9px] font-black text-amber-900 bg-amber-200/80 px-2 py-0.5 rounded-full uppercase">
                        Menyala 🔥
                    </span>
                </div>

                <div class="space-y-2.5">
                    @foreach($topStudents as $idx => $st)
                        @php
                            $rawSchool = $st->user->school?->name ?? 'Siswa Pembda';
                            $schoolShort = trim(preg_replace('/\s+/', ' ', str_ireplace(['Gunungsitoli', 'Swasta'], '', $rawSchool)));
                            $stPoints = (int) ($st->reputation_points ?? $st->user?->reputation?->total_points ?? 0);
                        @endphp
                        <a href="{{ route('circle.profile', ['username' => $st->username ?? $st->user?->circle_username ?? 'user', 'tab' => 'points']) }}" 
                           class="flex items-center justify-between p-2.5 rounded-2xl bg-white hover:bg-amber-100/50 border border-amber-200 transition-all hover:scale-102 group shadow-2xs"
                           title="Klik untuk melihat profil & Wall Jejak Poin {{ $st->user->name }}">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                                <div class="relative shrink-0">
                                    <img src="{{ $st->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         class="w-10 h-10 rounded-xl object-cover ring-2 {{ $idx === 0 ? 'ring-amber-400' : ($idx === 1 ? 'ring-slate-400' : 'ring-orange-300') }}">
                                    <span class="absolute -top-1.5 -left-1.5 text-xs">
                                        {{ $idx === 0 ? '🥇' : ($idx === 1 ? '🥈' : ($idx === 2 ? '🥉' : '🌟')) }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-black text-slate-950 truncate group-hover:text-pink-600 transition-colors">
                                        {{ $st->nickname ?? $st->user->name }}
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-500 truncate" title="{{ $rawSchool }}">
                                        {{ $schoolShort }}
                                    </div>
                                    <div class="text-[10.5px] font-black text-amber-950 flex items-center gap-1 mt-0.5">
                                        <span class="text-amber-500 text-xs">⚡</span>
                                        <span class="bg-amber-100/90 text-amber-950 px-1.5 py-0.2 rounded border border-amber-300 text-[10px] font-black whitespace-nowrap shadow-2xs">
                                            {{ number_format($stPoints) }} Pts
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-[11px] font-black text-orange-600 shrink-0 flex items-center gap-1 bg-orange-50 px-2 py-1 rounded-xl border border-orange-200 self-center" title="Total Apresiasi Karya">
                                <span>🔥</span>
                                <span>{{ $st->total_likes ?? 0 }}</span>
                            </div>
                        </a>
                    @endforeach

                    {{-- Guru Pembina Teraktif --}}
                    @if(isset($topTeacher))
                        @php
                            $rawTeacherSchool = $topTeacher->user->school?->name ?? 'Guru Pembda';
                            $teacherSchoolShort = trim(preg_replace('/\s+/', ' ', str_ireplace(['Gunungsitoli', 'Swasta'], '', $rawTeacherSchool)));
                            $teacherPoints = (int) ($topTeacher->reputation_points ?? $topTeacher->user?->reputation?->total_points ?? 0);
                        @endphp
                        <div class="pt-2 border-t border-amber-200/80">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-700 block mb-1.5">Guru Pembina Teraktif:</span>
                            <a href="{{ route('circle.profile', ['username' => $topTeacher->username ?? $topTeacher->user?->circle_username ?? 'user', 'tab' => 'points']) }}" 
                               class="flex items-center justify-between p-2 rounded-2xl bg-sky-50 hover:bg-sky-100 border border-sky-200 transition group shadow-2xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1 mr-2">
                                    <img src="{{ $topTeacher->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         class="w-9 h-9 rounded-xl object-cover ring-2 ring-sky-300 shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-[11px] font-black text-slate-950 truncate group-hover:text-sky-700">
                                            {{ $topTeacher->nickname ?? $topTeacher->user->name }}
                                        </div>
                                        <div class="text-[10px] font-bold text-slate-500 truncate" title="{{ $rawTeacherSchool }}">
                                            Guru • {{ $teacherSchoolShort }}
                                        </div>
                                        <div class="text-[10.5px] font-black text-sky-950 flex items-center gap-1 mt-0.5">
                                            <span class="text-amber-500 text-xs">⚡</span>
                                            <span class="bg-white/90 text-sky-950 px-1.5 py-0.2 rounded border border-sky-300 text-[10px] font-black whitespace-nowrap shadow-2xs">
                                                {{ number_format($teacherPoints) }} Pts
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-black text-sky-800 bg-sky-100 px-2.5 py-1 rounded-xl border border-sky-300 shrink-0">
                                    ⭐ Mentor
                                </span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

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

    {{-- ═══════════════════════════════════════════
         24H STORY VIEWER MODAL (INSTAGRAM / WHATSAPP STYLE)
    ═══════════════════════════════════════════ --}}
    <div x-show="storyOpen" x-cloak 
         class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-2 sm:p-4"
         @keydown.escape.window="closeStory()"
         x-transition>
        
        <div class="relative w-full max-w-sm h-[85vh] max-h-[680px] bg-slate-900 rounded-[2.5rem] overflow-hidden border-2 border-white/20 shadow-2xl flex flex-col justify-between"
             @click.outside="closeStory()">
            
            {{-- Top Bars: Stories Progress Bar & Header --}}
            <div class="relative z-20 p-4 bg-gradient-to-b from-black/85 via-black/40 to-transparent">
                {{-- Multi-story Progress Indicators --}}
                <div class="flex items-center gap-1.5 mb-3">
                    <template x-for="(st, idx) in stories" :key="st.id">
                        <div class="h-1 flex-1 rounded-full bg-white/30 overflow-hidden">
                            <div class="h-full bg-white transition-all duration-100 ease-linear"
                                 :style="idx === activeIdx ? 'width: ' + progress + '%' : (idx < activeIdx ? 'width: 100%' : 'width: 0%')">
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Story Author Info & Close Button --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <img :src="currStory().avatar" class="w-9 h-9 rounded-full object-cover ring-2 ring-pink-400">
                        <div class="min-w-0 text-white">
                            <div class="flex items-center gap-1.5">
                                <span class="font-black text-xs truncate" x-text="currStory().name"></span>
                                <span class="text-[9px] bg-pink-500/80 px-1.5 py-0.2 rounded-md font-bold" x-text="currStory().school"></span>
                            </div>
                            <span class="text-[10px] text-slate-300 font-medium" x-text="currStory().time_ago"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="'/@' + currStory().username" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-white text-[10px] font-black transition">
                            Profil
                        </a>
                        <button type="button" @click="closeStory()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center text-sm transition cursor-pointer">
                            ✕
                        </button>
                    </div>
                </div>
            </div>

            {{-- Center Tap Zones (Left: Prev, Right: Next) --}}
            <div class="absolute inset-0 z-10 flex">
                <div class="w-1/3 h-full cursor-pointer" @click="prevStory()"></div>
                <div class="w-2/3 h-full cursor-pointer" @click="nextStory()"></div>
            </div>

            {{-- Story Media & Content --}}
            <div class="absolute inset-0 z-0 flex items-center justify-center bg-black">
                <template x-if="currStory().media_type === 'image'">
                    <img :src="currStory().media_url" class="w-full h-full object-contain">
                </template>
                <template x-if="currStory().media_type === 'video'">
                    <video :src="currStory().media_url" autoplay playsinline class="w-full h-full object-contain"></video>
                </template>
                <template x-if="!currStory().media_url">
                    <div class="p-8 text-center text-white flex flex-col items-center justify-center h-full w-full bg-gradient-to-br transition-all duration-300"
                         :class="getStoryBg(currStory())">
                        <span class="text-4xl mb-4" x-text="currStory().mood_emoji || '✨'"></span>
                        <p class="font-display font-black text-lg sm:text-xl leading-relaxed" x-text="currStory().content"></p>
                    </div>
                </template>
            </div>

            {{-- Bottom Overlay: Story Caption & Reactions --}}
            <div class="relative z-20 p-4 bg-gradient-to-t from-black/90 via-black/50 to-transparent text-white">
                <template x-if="currStory().media_url && currStory().content">
                    <p class="text-xs font-semibold text-slate-100 line-clamp-3 mb-3" x-text="currStory().content"></p>
                </template>

                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click.stop="toggleCircleReaction($el, currStory().id, 'lit')" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-xs font-black transition">
                            🔥 Lit
                        </button>
                        <button type="button" @click.stop="toggleCircleReaction($el, currStory().id, 'clap')" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-xs font-black transition">
                            👏 Apresiasi
                        </button>
                        <button type="button" @click.stop="toggleCircleReaction($el, currStory().id, 'love')" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-xs font-black transition">
                            ❤️ Suka
                        </button>
                    </div>
                    <span class="text-[10px] text-slate-300 font-bold" x-text="currStory().mood_text || 'Story 24 Jam'"></span>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
