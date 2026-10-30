@extends('circle.layout')

@section('title', ($profile->nickname ?? $user->name) . ' (@' . $profile->username . ') — PembdaCIRCLE')

@section('og_meta')
    <meta property="og:title" content="{{ $user->name }} di PembdaCIRCLE">
    <meta property="og:description" content="{{ $profile->bio ?? 'Lihat portofolio, kreasi, dan ekspresi resmi di Perguruan Pembda.' }}">
    <meta property="og:image" content="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="profile">
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ activeTab: '{{ request('tab', 'wall') }}' }">

    {{-- ═══════════════════════════════════════════
         PROFILE HEADER CARD (CINEMATIC DARK)
    ═══════════════════════════════════════════ --}}
    <div class="bg-slate-900/90 backdrop-blur-md rounded-3xl border border-slate-800/80 shadow-2xl shadow-black/40 overflow-hidden mb-7">

        {{-- ── 1. Cover Banner ── --}}
        <div class="h-44 sm:h-64 w-full relative overflow-hidden bg-gradient-to-r from-slate-950 via-indigo-950 to-purple-950">
            @if($profile->banner_path)
                <img src="{{ asset('storage/' . $profile->banner_path) }}" 
                     alt="Cover banner"
                     class="w-full h-full object-cover">
            @else
                {{-- Pola Geometri Minimalist & Ambient Glow --}}
                <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#818cf8_1px,transparent_1px)] [background-size:20px_20px]"></div>
                <div class="absolute -right-16 -bottom-16 w-72 h-72 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-16 -top-16 w-72 h-72 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
            @endif

            {{-- School Unit Badge on Top Right --}}
            <div class="absolute top-4 right-4 flex items-center gap-2">
                @if($user->school)
                    <span class="px-3.5 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider text-slate-100 bg-slate-950/75 backdrop-blur-md border border-white/10 shadow-lg">
                        <i class="fas fa-school mr-1.5 text-indigo-400"></i>{{ $user->school->name }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ── 2. Profile Details & Avatar Overlap ── --}}
        <div class="px-6 pb-6 pt-0 relative">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-5 -mt-16 sm:-mt-20 mb-5">
                {{-- Avatar & Identity --}}
                <div class="flex items-end gap-4 min-w-0">
                    <div class="relative shrink-0">
                        <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl p-1 bg-slate-900 border-2 border-slate-800 shadow-2xl">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-full h-full object-cover rounded-2xl bg-slate-800">
                        </div>
                        @if($activeStories->isNotEmpty())
                            {{-- Story Active Ring Indicator --}}
                            <div class="absolute -bottom-1.5 -right-1.5 w-8 h-8 rounded-full story-ring text-white flex items-center justify-center text-xs shadow-lg border-2 border-slate-900" 
                                 title="Memiliki Story 24 Jam">
                                <i class="fas fa-bolt text-amber-300"></i>
                            </div>
                        @endif
                    </div>

                    <div class="pb-1 min-w-0">
                        <h1 class="font-display font-black text-xl sm:text-2xl text-white tracking-tight flex items-center gap-2 truncate">
                            <span>{{ $user->name }}</span>
                            @if($user->role === 'guru')
                                <i class="fas fa-check-circle text-blue-400 text-base shrink-0" title="Pendidik Resmi Terverifikasi"></i>
                            @else
                                <i class="fas fa-certificate text-amber-400 text-base shrink-0" title="Siswa Aktif Pembda"></i>
                            @endif
                        </h1>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mt-0.5 flex-wrap">
                            <span class="text-indigo-400 font-bold">@<span>{{ $profile->username }}</span></span>
                            <span>•</span>
                            <span class="text-slate-300">{{ $user->role === 'siswa' ? 'Siswa' : ($user->role === 'guru' ? 'Guru' : 'Warga Pembda') }}</span>
                            @if($user->student?->classroom)
                                <span>•</span>
                                <span class="bg-slate-800 text-slate-300 px-2 py-0.5 rounded-md border border-slate-700/50 text-[11px]">
                                    {{ $user->student->classroom->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Bar: Share & View Stats --}}
                <div class="flex items-center gap-2.5 self-start sm:self-end shrink-0">
                    <button onclick="shareProfile()" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700/60 hover:border-slate-600 transition-base flex items-center gap-2 shadow-xs cursor-pointer">
                        <i class="fab fa-whatsapp text-emerald-400 text-sm"></i>
                        <span>Bagikan</span>
                    </button>
                    <div class="px-3.5 py-2 rounded-xl bg-indigo-500/10 border border-indigo-500/25 text-indigo-300 text-xs font-black flex items-center gap-2" 
                         title="Total Kunjungan Profil">
                        <i class="fas fa-eye text-indigo-400 text-[11px]"></i>
                        <span>{{ number_format($profile->views_count) }}</span>
                    </div>
                </div>
            </div>

            {{-- Mood & Vibe Barometer --}}
            @if($profile->mood_text)
                <div class="mb-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-300 text-xs font-semibold">
                    <span class="text-sm">{{ $profile->mood_emoji ?? '✨' }}</span>
                    <span>{{ $profile->mood_text }}</span>
                </div>
            @endif

            {{-- Bio Singkat --}}
            <p class="text-sm text-slate-300 leading-relaxed max-w-2xl mb-4 font-normal">
                {{ $profile->bio ?? 'Belum ada biodata singkat.' }}
            </p>

            {{-- Social Media Links --}}
            @if(!empty($profile->social_links))
                <div class="flex items-center gap-3 text-sm text-slate-400 mb-2">
                    @foreach($profile->social_links as $plat => $url)
                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" 
                               class="hover:text-indigo-400 transition-colors" 
                               title="{{ ucfirst($plat) }}">
                                <i class="fab fa-{{ $plat }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── 3. Dual-Tab Navigasi: Nongki vs Karya ── --}}
        <div class="flex border-t border-slate-800 bg-slate-950/50 px-6">
            <button @click="activeTab = 'wall'" 
                    :class="activeTab === 'wall' ? 'border-indigo-500 text-white font-black' : 'border-transparent text-slate-400 hover:text-slate-200 font-semibold'"
                    class="flex items-center gap-2 py-4 px-5 border-b-2 text-sm transition-base -mb-[1px] cursor-pointer">
                <i class="fas fa-mug-hot text-amber-400 text-sm"></i>
                <span class="font-display">Dinding Nongki & Momen</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 font-bold">
                    {{ $wallPosts->count() }}
                </span>
            </button>
            <button @click="activeTab = 'portfolio'" 
                    :class="activeTab === 'portfolio' ? 'border-indigo-500 text-white font-black' : 'border-transparent text-slate-400 hover:text-slate-200 font-semibold'"
                    class="flex items-center gap-2 py-4 px-5 border-b-2 text-sm transition-base -mb-[1px] cursor-pointer">
                <i class="fas fa-trophy text-indigo-400 text-sm"></i>
                <span class="font-display">Karya & Portofolio</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-black">
                    {{ $portfolioPosts->count() + $finalProjects->count() + $approvedPklLogs->count() }}
                </span>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         TAB 1: DINDING NONGKI & MOMEN (THE WALL)
    ═══════════════════════════════════════════ --}}
    <div x-show="activeTab === 'wall'" x-transition class="space-y-6">
        @if($wallPosts->isEmpty())
            <div class="bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-800/80 p-10 text-center shadow-xl shadow-black/20">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-3.5 text-2xl">
                    <i class="fas fa-mug-hot"></i>
                </div>
                <h3 class="font-display font-bold text-lg text-white mb-1">Dinding Masih Sepi</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                    {{ $profile->nickname ?? $user->name }} belum membagikan status nongki atau cerita momen hari ini.
                </p>
            </div>
        @else
            @foreach($wallPosts as $post)
                <article class="bg-slate-900/90 backdrop-blur-md rounded-3xl border border-slate-800/80 p-5 sm:p-6 shadow-xl shadow-black/30 hover:border-slate-700/80 transition-all duration-200">
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $user->name }}"
                                 class="w-10 h-10 rounded-2xl object-cover ring-1 ring-white/10 shrink-0">
                            <div class="min-w-0">
                                <h4 class="font-display font-bold text-sm text-slate-100 flex items-center gap-1.5 truncate">
                                    <span>{{ $user->name }}</span>
                                    @if($post->mood_emoji)
                                        <span class="text-xs" title="{{ $post->mood_text }}">{{ $post->mood_emoji }}</span>
                                    @endif
                                </h4>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $post->created_at->diffForHumans() }}</span>
                                    @if($post->location_tag)
                                        <span>•</span>
                                        <span class="text-indigo-400"><i class="fas fa-map-pin text-[9px] mr-1"></i>{{ $post->location_tag }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="text-sm text-slate-200 leading-relaxed whitespace-pre-line mb-4 font-normal">
                        {{ $post->content }}
                    </div>

                    {{-- Media --}}
                    @if($post->media->isNotEmpty())
                        <div class="rounded-2xl overflow-hidden mb-4 bg-slate-950 border border-slate-800/60 max-h-[460px]">
                            @foreach($post->media as $m)
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" alt="Media" class="w-full h-auto object-cover max-h-[460px]" loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="w-full max-h-[460px] bg-black"></video>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Reactions --}}
                    <div class="flex items-center justify-between pt-3.5 border-t border-slate-800/80 text-xs text-slate-400">
                        <div class="flex items-center gap-3">
                            <button type="button" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-orange-400 transition-base cursor-pointer">
                                <span class="text-sm">🔥</span>
                                <span class="font-extrabold">{{ $post->likes_count > 0 ? $post->likes_count : 'Lit' }}</span>
                            </button>
                            <button type="button" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-indigo-400 transition-base cursor-pointer">
                                <span class="text-sm">👏</span>
                                <span class="font-semibold">Apresiasi</span>
                            </button>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            <span>{{ $post->comments_count }} komentar</span>
                        </div>
                    </div>
                </article>
            @endforeach
        @endif
    </div>

    {{-- ═══════════════════════════════════════════
         TAB 2: KARYA & PORTOFOLIO (THE FLEX ZONE)
    ═══════════════════════════════════════════ --}}
    <div x-show="activeTab === 'portfolio'" x-transition class="space-y-6">

        {{-- ── 1. Verified Skill Badges dari Guru ── --}}
        @if($skillEndorsements->isNotEmpty())
            <div class="bg-slate-900/90 backdrop-blur-md border border-indigo-500/25 rounded-3xl p-5 sm:p-6 shadow-xl shadow-black/30">
                <h3 class="text-xs font-black uppercase tracking-wider text-indigo-300 mb-3.5 flex items-center gap-2">
                    <i class="fas fa-shield-check text-indigo-400 text-sm"></i>
                    <span>Keahlian Resmi Terverifikasi Guru</span>
                </h3>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($skillEndorsements as $se)
                        <div class="px-3.5 py-2 rounded-xl bg-slate-800/80 border border-slate-700/80 text-xs font-bold text-slate-200 shadow-sm flex items-center gap-2">
                            <i class="fas fa-medal text-amber-400"></i>
                            <span>{{ $se->skill_name }}</span>
                            <span class="text-[10px] text-emerald-300 font-extrabold bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 rounded-full" 
                                  title="Divalidasi oleh: {{ $se->teacher?->name }}">
                                ✓ Valid
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 2. Showcase Grid (Final Projects, PKL, & Circle Projects) ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- A. Tugas Akhir / Penelitian Ilmiah --}}
            @foreach($finalProjects as $fp)
                <div class="bg-slate-900/90 backdrop-blur-md rounded-3xl border border-slate-800/80 shadow-xl shadow-black/30 overflow-hidden flex flex-col hover:border-slate-700/80 transition-base">
                    <div class="p-5 flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-500/15 border border-indigo-500/30 text-indigo-300">
                                {{ $fp->project_type === 'project_akhir' ? 'Project Akhir SMK' : 'Penelitian Ilmiah SMA' }}
                            </span>
                        </div>
                        <h4 class="font-display font-bold text-base text-white mb-2 leading-snug">
                            {{ $fp->title }}
                        </h4>
                        <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed font-normal">
                            {{ $fp->abstract ?? $fp->description ?? 'Dokumentasi hasil karya ilmiah dan praktik terapan terverifikasi.' }}
                        </p>
                    </div>
                    <div class="px-5 py-3 bg-slate-950/60 border-t border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-slate-500 text-[11px]"><i class="fas fa-calendar-alt mr-1"></i>{{ $fp->created_at->format('M Y') }}</span>
                        <span class="text-emerald-400 font-extrabold text-[10px] bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 rounded-md">
                            Tersertifikasi
                        </span>
                    </div>
                </div>
            @endforeach

            {{-- B. Praktik Kerja Lapangan (PKL DUDI) --}}
            @foreach($approvedPklLogs as $pkl)
                <div class="bg-slate-900/90 backdrop-blur-md rounded-3xl border border-slate-800/80 shadow-xl shadow-black/30 overflow-hidden flex flex-col hover:border-slate-700/80 transition-base">
                    @if($pkl->documentation_file)
                        <div class="h-44 w-full bg-slate-950 overflow-hidden">
                            <img src="{{ asset('storage/' . $pkl->documentation_file) }}" 
                                 alt="Dokumentasi PKL"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-500/15 border border-amber-500/30 text-amber-300 mb-2.5 inline-block">
                            <i class="fas fa-briefcase mr-1"></i>Praktik Industri DUDI
                        </span>
                        <h4 class="font-display font-bold text-sm text-white mb-1 leading-snug">
                            {{ $pkl->activity_description }}
                        </h4>
                    </div>
                    <div class="px-5 py-3 bg-slate-950/60 border-t border-slate-800/80 text-[11px] text-slate-400 flex items-center justify-between">
                        <span>{{ \Carbon\Carbon::parse($pkl->activity_date)->isoFormat('D MMMM Y') }}</span>
                        <span class="text-emerald-400 font-semibold"><i class="fas fa-check-circle mr-1"></i>Approved Pembimbing</span>
                    </div>
                </div>
            @endforeach

            {{-- C. Postingan Portfolio Circle Langsung --}}
            @foreach($portfolioPosts as $port)
                <div class="bg-slate-900/90 backdrop-blur-md rounded-3xl border border-slate-800/80 shadow-xl shadow-black/30 overflow-hidden flex flex-col hover:border-slate-700/80 transition-base">
                    @if($port->media->isNotEmpty())
                        <div class="h-48 w-full bg-slate-950 overflow-hidden">
                            @php $firstMedia = $port->media->first(); @endphp
                            @if($firstMedia->media_type === 'image')
                                <img src="{{ $firstMedia->url }}" alt="Portfolio media" class="w-full h-full object-cover">
                            @elseif($firstMedia->media_type === 'video')
                                <video src="{{ $firstMedia->url }}" controls class="w-full h-full object-cover"></video>
                            @endif
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-purple-500/15 border border-purple-500/30 text-purple-300 mb-2.5 inline-block">
                            {{ $port->post_type }}
                        </span>
                        <h4 class="font-display font-bold text-sm text-white mb-1.5">{{ $port->title ?? 'Karya Unggulan' }}</h4>
                        <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed font-normal">{{ $port->content }}</p>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Empty State --}}
        @if($portfolioPosts->isEmpty() && $finalProjects->isEmpty() && $approvedPklLogs->isEmpty())
            <div class="bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-800/80 p-10 text-center shadow-xl shadow-black/20">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto mb-3.5 text-2xl">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="font-display font-bold text-lg text-white mb-1">Portofolio Sedang Dirakit</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Karya tugas akhir, dokumentasi magang DUDI, dan inovasi siswa ini akan segera dipamerkan di sini.
                </p>
            </div>
        @endif

    </div>

</div>

{{-- ── Toast & Native Share Script ── --}}
<script>
function shareProfile() {
    const url = window.location.href;
    const text = 'Lihat profil dan karya {{ $user->name }} di PembdaCIRCLE:\n' + url;
    if (navigator.share) {
        navigator.share({
            title: '{{ $user->name }} di PembdaCIRCLE',
            text: text,
            url: url
        }).catch(() => {});
    } else {
        const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank');
    }
}
</script>
@endsection
