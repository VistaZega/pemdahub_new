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
         PROFILE HEADER CARD (PASTEL CANTIK & LUCU)
    ═══════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] shadow-xs overflow-hidden mb-7">

        {{-- ── 1. Cover Banner (Gradien Pastel Lembut & Manis) ── --}}
        <div class="h-48 sm:h-64 w-full relative overflow-hidden bg-gradient-to-r from-[#fce7f3] via-[#fffbeb] to-[#e0f2fe]">
            @if($profile->banner_path)
                <img src="{{ asset('storage/' . $profile->banner_path) }}" 
                     alt="Cover banner"
                     class="w-full h-full object-cover">
            @else
                {{-- Pola Polkadot Estetik Pastel --}}
                <div class="absolute inset-0 opacity-40 bg-[radial-gradient(#ec4899_1.5px,transparent_1.5px)] [background-size:20px_20px]"></div>
                <div class="absolute -right-16 -bottom-16 w-72 h-72 bg-pink-200/50 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-16 -top-16 w-72 h-72 bg-sky-200/50 rounded-full blur-2xl pointer-events-none"></div>
            @endif

            {{-- School Unit Badge on Top Right (Pastel Cantik) --}}
            <div class="absolute top-4 right-4 flex items-center gap-2">
                @if($user->school)
                    <span class="px-4 py-1.5 rounded-2xl text-xs font-black uppercase tracking-wider text-slate-950 bg-white/90 backdrop-blur-md border-2 border-pink-200 shadow-xs flex items-center gap-1.5">
                        <i class="fas fa-school text-pink-600"></i>
                        <span>{{ $user->school->name }}</span>
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
                        <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl p-1 bg-white border-4 border-white shadow-md">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-full h-full object-cover rounded-2xl bg-pink-50">
                        </div>
                        @if($activeStories->isNotEmpty())
                            {{-- Story Active Ring Indicator Lucu --}}
                            <div class="absolute -bottom-1 -right-1 w-9 h-9 rounded-full pastel-story-ring text-white flex items-center justify-center text-xs shadow-md border-2 border-white animate-pulse" 
                                 title="Memiliki Story 24 Jam">
                                <i class="fas fa-bolt text-yellow-100"></i>
                            </div>
                        @endif
                    </div>

                    <div class="pb-1 min-w-0">
                        <h1 class="font-display font-black text-xl sm:text-2xl text-slate-950 tracking-tight flex items-center gap-2 truncate">
                            <span>{{ $user->name }}</span>
                            @if($user->role === 'guru')
                                <span class="w-6 h-6 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-xs shadow-2xs" title="Pendidik Resmi Terverifikasi">
                                    <i class="fas fa-certificate"></i>
                                </span>
                            @else
                                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs shadow-2xs" title="Siswa Aktif Pembda">
                                    <i class="fas fa-check"></i>
                                </span>
                            @endif
                        </h1>
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 mt-1 flex-wrap">
                            <span class="text-pink-600 bg-pink-50 px-2.5 py-0.5 rounded-lg border border-pink-200">@<span>{{ $profile->username }}</span></span>
                            <span>•</span>
                            <span class="text-slate-900">{{ $user->role === 'siswa' ? 'Siswa Aktif' : ($user->role === 'guru' ? 'Guru / Mentor' : 'Warga Pembda') }}</span>
                            @if($user->student?->classroom)
                                <span>•</span>
                                <span class="bg-[#faedcd] text-[#7f5539] font-black px-2.5 py-0.5 rounded-lg border border-[#d4a373]">
                                    {{ $user->student->classroom->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Bar: Share & View Stats (Pastel Hijau & Pink) --}}
                <div class="flex items-center gap-2.5 self-start sm:self-end shrink-0">
                    <button onclick="shareProfile()" 
                            class="px-4 py-2 rounded-2xl text-xs font-black text-slate-950 bg-emerald-100 hover:bg-emerald-200 border-2 border-emerald-300 transition-base flex items-center gap-2 shadow-xs cursor-pointer hover:scale-105">
                        <i class="fab fa-whatsapp text-emerald-700 text-sm"></i>
                        <span>Bagikan Profil</span>
                    </button>
                    <div class="px-3.5 py-2 rounded-2xl bg-pink-100 border-2 border-pink-200 text-slate-950 text-xs font-black flex items-center gap-1.5 shadow-xs" 
                         title="Total Kunjungan Profil">
                        <i class="fas fa-eye text-pink-600 text-xs"></i>
                        <span>{{ number_format($profile->views_count) }}</span>
                    </div>
                </div>
            </div>

            {{-- Mood & Vibe Barometer (Kuning Pastel Manis) --}}
            @if($profile->mood_text)
                <div class="mb-4 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-50 border-2 border-amber-200 text-slate-950 text-xs font-black shadow-2xs">
                    <span class="text-base">{{ $profile->mood_emoji ?? '✨' }}</span>
                    <span>{{ $profile->mood_text }}</span>
                </div>
            @endif

            {{-- Bio Singkat (Hitam Pekat & Jelas Terbaca) --}}
            <p class="text-sm font-semibold text-slate-950 leading-relaxed max-w-2xl mb-4">
                {{ $profile->bio ?? 'Belum ada biodata singkat.' }}
            </p>

            {{-- Social Media Links --}}
            @if(!empty($profile->social_links))
                <div class="flex items-center gap-3 text-sm text-slate-600 mb-2">
                    @foreach($profile->social_links as $plat => $url)
                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" 
                               class="hover:text-pink-600 transition-colors p-1.5 rounded-xl bg-slate-50 border border-slate-200" 
                               title="{{ ucfirst($plat) }}">
                                <i class="fab fa-{{ $plat }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── 3. Dual-Tab Navigasi: Nongki vs Karya (Pastel Coklat vs Kuning) ── --}}
        <div class="flex border-t-2 border-[#f1e8dc] bg-[#faf8f5] px-6">
            {{-- Tab Dinding (Coklat Pastel) --}}
            <button @click="activeTab = 'wall'" 
                    :class="activeTab === 'wall' ? 'border-[#b08968] text-slate-950 font-black bg-white shadow-xs' : 'border-transparent text-slate-700 hover:text-slate-950 font-bold'"
                    class="flex items-center gap-2 py-4 px-5 border-b-3 text-sm transition-base -mb-[2px] cursor-pointer rounded-t-2xl">
                <span class="w-6 h-6 rounded-lg bg-[#faedcd] text-[#7f5539] flex items-center justify-center text-xs">
                    <i class="fas fa-mug-hot"></i>
                </span>
                <span class="font-display">Dinding Nongki & Momen</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-[#faedcd] text-[#7f5539] font-black border border-[#d4a373]">
                    {{ $wallPosts->count() }}
                </span>
            </button>
            {{-- Tab Karya (Kuning Pastel) --}}
            <button @click="activeTab = 'portfolio'" 
                    :class="activeTab === 'portfolio' ? 'border-amber-400 text-slate-950 font-black bg-white shadow-xs' : 'border-transparent text-slate-700 hover:text-slate-950 font-bold'"
                    class="flex items-center gap-2 py-4 px-5 border-b-3 text-sm transition-base -mb-[2px] cursor-pointer rounded-t-2xl">
                <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-xs">
                    <i class="fas fa-trophy"></i>
                </span>
                <span class="font-display">Karya & Portofolio</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-900 border border-amber-300 font-black">
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
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-10 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-[#faedcd] text-[#7f5539] border border-[#d4a373] flex items-center justify-center mx-auto mb-3.5 text-2xl shadow-xs">
                    <i class="fas fa-mug-hot"></i>
                </div>
                <h3 class="font-display font-black text-lg text-slate-950 mb-1">Dinding Masih Sepi</h3>
                <p class="text-xs font-bold text-slate-700 max-w-sm mx-auto leading-relaxed">
                    {{ $profile->nickname ?? $user->name }} belum membagikan status nongki atau cerita momen hari ini.
                </p>
            </div>
        @else
            @foreach($wallPosts as $post)
                <article class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-pink-200 transition-all duration-200">
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $user->name }}"
                                 class="w-11 h-11 rounded-2xl object-cover ring-2 ring-pink-100 shrink-0">
                            <div class="min-w-0">
                                <h4 class="font-display font-black text-sm sm:text-base text-slate-950 flex items-center gap-1.5 truncate">
                                    <span>{{ $user->name }}</span>
                                    @if($post->mood_emoji)
                                        <span class="text-xs" title="{{ $post->mood_text }}">{{ $post->mood_emoji }}</span>
                                    @endif
                                </h4>
                                <div class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $post->created_at->diffForHumans() }}</span>
                                    @if($post->location_tag)
                                        <span>•</span>
                                        <span class="text-pink-700 font-extrabold"><i class="fas fa-location-dot text-[9px] mr-1"></i>{{ $post->location_tag }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Content (Hitam & Sangat Mudah Dibaca) --}}
                    <div class="text-sm font-semibold text-slate-950 leading-relaxed whitespace-pre-line mb-4">
                        {{ $post->content }}
                    </div>

                    {{-- Media --}}
                    @if($post->media->isNotEmpty())
                        <div class="rounded-3xl overflow-hidden mb-4 bg-slate-950 border-2 border-[#f1e8dc] max-h-[460px]">
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
                    <div class="flex items-center justify-between pt-3.5 border-t-2 border-[#f8f4ed] text-xs">
                        <div class="flex items-center gap-2">
                            <button type="button" class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-slate-950 font-black transition-base cursor-pointer">
                                <span class="text-base">🔥</span>
                                <span>{{ $post->likes_count > 0 ? $post->likes_count : 'Lit' }}</span>
                            </button>
                            <button type="button" class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-slate-950 font-black transition-base cursor-pointer">
                                <span class="text-base">👏</span>
                                <span>Apresiasi</span>
                            </button>
                        </div>
                        <div class="text-xs font-bold text-slate-700">
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

        {{-- ── 1. Verified Skill Badges dari Guru (Hijau Pastel Cantik) ── --}}
        @if($skillEndorsements->isNotEmpty())
            <div class="bg-emerald-50/80 border-2 border-emerald-200 rounded-3xl p-5 sm:p-6 shadow-xs">
                <h3 class="text-xs font-black uppercase tracking-wider text-emerald-950 mb-3.5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-200 text-emerald-800 flex items-center justify-center text-xs">
                        <i class="fas fa-certificate"></i>
                    </span>
                    <span>Keahlian Resmi Terverifikasi Guru</span>
                </h3>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($skillEndorsements as $se)
                        <div class="px-3.5 py-2 rounded-2xl bg-white border-2 border-emerald-200 text-xs font-black text-slate-950 shadow-2xs flex items-center gap-2">
                            <i class="fas fa-medal text-amber-500"></i>
                            <span>{{ $se->skill_name }}</span>
                            <span class="text-[10px] text-emerald-900 font-black bg-emerald-100 border border-emerald-300 px-2 py-0.5 rounded-full" 
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

            {{-- A. Tugas Akhir / Penelitian Ilmiah (Biru Pastel) --}}
            @foreach($finalProjects as $fp)
                <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] shadow-xs overflow-hidden flex flex-col hover:shadow-md hover:border-sky-300 transition-base">
                    <div class="p-5 flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-sky-100 border border-sky-300 text-sky-950">
                                {{ $fp->project_type === 'project_akhir' ? 'Project Akhir SMK' : 'Penelitian Ilmiah SMA' }}
                            </span>
                        </div>
                        <h4 class="font-display font-black text-base text-slate-950 mb-2 leading-snug">
                            {{ $fp->title }}
                        </h4>
                        <p class="text-xs font-medium text-slate-800 line-clamp-3 leading-relaxed">
                            {{ $fp->abstract ?? $fp->description ?? 'Dokumentasi hasil karya ilmiah dan praktik terapan terverifikasi.' }}
                        </p>
                    </div>
                    <div class="px-5 py-3 bg-[#faf8f5] border-t-2 border-[#f1e8dc] flex items-center justify-between text-xs">
                        <span class="text-slate-700 font-bold text-[11px]"><i class="fas fa-calendar-alt mr-1 text-slate-500"></i>{{ $fp->created_at->format('M Y') }}</span>
                        <span class="text-emerald-900 font-black text-[10px] bg-emerald-100 border border-emerald-300 px-2.5 py-0.5 rounded-lg">
                            Tersertifikasi
                        </span>
                    </div>
                </div>
            @endforeach

            {{-- B. Praktik Kerja Lapangan (Coklat Pastel) --}}
            @foreach($approvedPklLogs as $pkl)
                <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] shadow-xs overflow-hidden flex flex-col hover:shadow-md hover:border-[#d4a373] transition-base">
                    @if($pkl->documentation_file)
                        <div class="h-44 w-full bg-slate-100 overflow-hidden">
                            <img src="{{ asset('storage/' . $pkl->documentation_file) }}" 
                                 alt="Dokumentasi PKL"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-[#faedcd] border border-[#d4a373] text-[#7f5539] mb-2.5 inline-block">
                            <i class="fas fa-briefcase mr-1"></i>Praktik Industri DUDI
                        </span>
                        <h4 class="font-display font-black text-sm text-slate-950 mb-1 leading-snug">
                            {{ $pkl->activity_description }}
                        </h4>
                    </div>
                    <div class="px-5 py-3 bg-[#faf8f5] border-t-2 border-[#f1e8dc] text-[11px] text-slate-700 font-bold flex items-center justify-between">
                        <span>{{ \Carbon\Carbon::parse($pkl->activity_date)->isoFormat('D MMMM Y') }}</span>
                        <span class="text-emerald-800 font-black"><i class="fas fa-check-circle mr-1 text-emerald-600"></i>Approved</span>
                    </div>
                </div>
            @endforeach

            {{-- C. Postingan Portfolio Circle Langsung (Pink Pastel) --}}
            @foreach($portfolioPosts as $port)
                <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] shadow-xs overflow-hidden flex flex-col hover:shadow-md hover:border-pink-300 transition-base">
                    @if($port->media->isNotEmpty())
                        <div class="h-48 w-full bg-slate-900 overflow-hidden">
                            @php $firstMedia = $port->media->first(); @endphp
                            @if($firstMedia->media_type === 'image')
                                <img src="{{ $firstMedia->url }}" alt="Portfolio media" class="w-full h-full object-cover">
                            @elseif($firstMedia->media_type === 'video')
                                <video src="{{ $firstMedia->url }}" controls class="w-full h-full object-cover"></video>
                            @endif
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-pink-100 border border-pink-300 text-pink-900 mb-2.5 inline-block">
                            {{ $port->post_type }}
                        </span>
                        <h4 class="font-display font-black text-sm text-slate-900 mb-1.5">{{ $port->title ?? 'Karya Unggulan' }}</h4>
                        <p class="text-xs font-semibold text-slate-800 line-clamp-3 leading-relaxed">{{ $port->content }}</p>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Empty State --}}
        @if($portfolioPosts->isEmpty() && $finalProjects->isEmpty() && $approvedPklLogs->isEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-10 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center mx-auto mb-3.5 text-2xl shadow-xs">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="font-display font-black text-lg text-slate-950 mb-1">Portofolio Sedang Dirakit</h3>
                <p class="text-xs font-bold text-slate-700 max-w-sm mx-auto leading-relaxed">
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
