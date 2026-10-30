@extends('circle.layout')

@section('title', ($profile->nickname ?? $user->name) . ' (@' . $profile->username . ') — PembdaCIRCLE')

@section('og_meta')
    <meta property="og:title" content="{{ $user->name }} di PembdaCIRCLE">
    <meta property="og:description" content="{{ $profile->bio ?? 'Lihat karya, aktivitas dan portofolio resmi di Perguruan Pembda.' }}">
    <meta property="og:image" content="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="profile">
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ activeTab: '{{ request('tab', 'wall') }}' }">

    <!-- Profil Header Card -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden mb-6">
        <!-- Banner Sampul -->
        <div class="h-44 sm:h-56 w-full relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 overflow-hidden">
            @if($profile->banner_path)
                <img src="{{ asset('storage/' . $profile->banner_path) }}" class="w-full h-full object-cover">
            @else
                <!-- Pola Dekoratif Default -->
                <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            @endif

            <!-- Unit Tag di Pojok Sampul -->
            <div class="absolute top-4 right-4 flex items-center gap-2">
                @if($user->school)
                    <span class="px-3 py-1 rounded-full text-xs font-bold text-white bg-black/40 backdrop-blur-md border border-white/20 shadow-xs">
                        {{ $user->school->name }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Info Bar Profil -->
        <div class="px-6 pb-6 pt-0 relative">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 -mt-16 sm:-mt-20 mb-4">
                <!-- Avatar Siswa / Guru -->
                <div class="flex items-end gap-4">
                    <div class="relative">
                        <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl p-1 bg-white shadow-xl">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-full h-full object-cover rounded-2xl bg-gray-100">
                        </div>
                        @if($activeStories->isNotEmpty())
                            <!-- Story Indicator Ring -->
                            <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-gradient-to-tr from-pink-500 to-amber-500 text-white flex items-center justify-center text-xs border-2 border-white shadow animate-pulse" title="Memiliki Story 24 Jam">
                                <i class="fas fa-bolt"></i>
                            </div>
                        @endif
                    </div>

                    <div class="pb-1">
                        <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                            <span>{{ $user->name }}</span>
                            @if($user->role === 'guru')
                                <i class="fas fa-check-circle text-blue-500 text-base" title="Pendidik Resmi Terverifikasi"></i>
                            @else
                                <i class="fas fa-certificate text-amber-500 text-base" title="Siswa Aktif Pembda"></i>
                            @endif
                        </h1>
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500">
                            <span class="text-indigo-600 font-bold">@<span>{{ $profile->username }}</span></span>
                            <span>•</span>
                            <span class="capitalize">{{ $user->role === 'siswa' ? 'Siswa Aktif' : ($user->role === 'guru' ? 'Guru / Mentor' : 'Warga Pembda') }}</span>
                            @if($user->student?->classroom)
                                <span>•</span>
                                <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-700 font-medium">{{ $user->student->classroom->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Button Share & Stats -->
                <div class="flex items-center gap-2 self-start sm:self-end">
                    <button onclick="shareProfile()" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition flex items-center gap-2 shadow-xs">
                        <i class="fab fa-whatsapp text-emerald-600 text-sm"></i>
                        <span>Bagikan Profil</span>
                    </button>
                    <div class="px-3 py-2 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-extrabold flex items-center gap-1.5" title="Total Kunjungan Profil">
                        <i class="fas fa-eye text-indigo-500"></i>
                        <span>{{ number_format($profile->views_count) }}</span>
                    </div>
                </div>
            </div>

            <!-- Mood & Suasana Hati Barometer -->
            @if($profile->mood_text)
                <div class="mb-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 text-amber-900 text-xs font-semibold shadow-xs">
                    <span class="text-base">{{ $profile->mood_emoji ?? '✨' }}</span>
                    <span>{{ $profile->mood_text }}</span>
                </div>
            @endif

            <!-- Bio Singkat -->
            <p class="text-sm text-gray-700 leading-relaxed max-w-2xl mb-4">
                {{ $profile->bio ?? 'Belum ada biodata singkat.' }}
            </p>

            <!-- Tautan Media Sosial Siswa -->
            @if(!empty($profile->social_links))
                <div class="flex items-center gap-3 text-sm text-gray-500 mb-2">
                    @foreach($profile->social_links as $plat => $url)
                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="hover:text-indigo-600 transition" title="{{ ucfirst($plat) }}">
                                <i class="fab fa-{{ $plat }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tab Navigasi: Dinding Nongki vs Karya & Portofolio -->
        <div class="flex border-t border-gray-200 bg-gray-50/50 px-6">
            <button @click="activeTab = 'wall'" 
                    :class="activeTab === 'wall' ? 'border-indigo-600 text-indigo-600 font-extrabold bg-white' : 'border-transparent text-gray-500 hover:text-gray-800 font-medium'"
                    class="flex items-center gap-2 py-3.5 px-5 border-b-2 text-sm transition -mb-[1px]">
                <i class="fas fa-coffee text-amber-500"></i>
                <span>Dinding Nongki & Momen</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-600">{{ $wallPosts->count() }}</span>
            </button>
            <button @click="activeTab = 'portfolio'" 
                    :class="activeTab === 'portfolio' ? 'border-indigo-600 text-indigo-600 font-extrabold bg-white' : 'border-transparent text-gray-500 hover:text-gray-800 font-medium'"
                    class="flex items-center gap-2 py-3.5 px-5 border-b-2 text-sm transition -mb-[1px]">
                <i class="fas fa-trophy text-indigo-500"></i>
                <span>Karya & Portofolio</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700 font-bold">
                    {{ $portfolioPosts->count() + $finalProjects->count() + $approvedPklLogs->count() }}
                </span>
            </button>
        </div>
    </div>

    <!-- TAB 1: DINDING NONGKI & MOMEN (THE SOCIAL WALL) -->
    <div x-show="activeTab === 'wall'" x-transition class="space-y-5">
        @if($wallPosts->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fas fa-mug-hot"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Dinding Masih Sepi</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    {{ $profile->nickname ?? $user->name }} belum membagikan status nongki atau momen hari ini.
                </p>
            </div>
        @else
            @foreach($wallPosts as $post)
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 hover:shadow-md transition">
                    <!-- Post Author Header -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                                    <span>{{ $user->name }}</span>
                                    @if($post->mood_emoji)
                                        <span class="text-xs" title="{{ $post->mood_text }}">{{ $post->mood_emoji }}</span>
                                    @endif
                                </h4>
                                <div class="text-[11px] text-gray-400 flex items-center gap-1">
                                    <span>{{ $post->created_at->diffForHumans() }}</span>
                                    @if($post->location_tag)
                                        <span>•</span>
                                        <span class="text-indigo-600"><i class="fas fa-map-marker-alt text-[9px] mr-0.5"></i>{{ $post->location_tag }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post Content -->
                    <div class="text-sm text-gray-800 leading-relaxed mb-4 whitespace-pre-line">
                        {{ $post->content }}
                    </div>

                    <!-- Post Media (Jika ada foto/video) -->
                    @if($post->media->isNotEmpty())
                        <div class="rounded-xl overflow-hidden mb-4 bg-gray-100 max-h-96">
                            @foreach($post->media as $m)
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" class="w-full h-auto object-cover max-h-96" loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="w-full max-h-96 bg-black rounded-xl"></video>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Reaction & Engagement Bar -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs text-gray-500">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1.5 hover:text-orange-600 transition cursor-pointer">
                                <span class="text-sm">🔥</span>
                                <span class="font-bold">{{ $post->likes_count }}</span>
                            </span>
                            <span class="flex items-center gap-1.5 hover:text-indigo-600 transition cursor-pointer">
                                <span class="text-sm">👏</span>
                                <span class="font-semibold">Apresiasi</span>
                            </span>
                        </div>
                        <div class="text-[11px] text-gray-400">
                            <span>{{ $post->comments_count }} komentar</span>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- TAB 2: KARYA & PORTOFOLIO (THE MASTERPIECE & FLEX ZONE) -->
    <div x-show="activeTab === 'portfolio'" x-transition class="space-y-6">

        <!-- Section: Verified Skill Badges dari Guru -->
        @if($skillEndorsements->isNotEmpty())
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl p-5 shadow-xs">
                <h3 class="text-xs font-black uppercase tracking-wider text-amber-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-check-double text-amber-600"></i>
                    <span>Keahlian Resmi Terverifikasi Guru</span>
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($skillEndorsements as $se)
                        <div class="px-3 py-1.5 rounded-xl bg-white border border-amber-200 text-xs font-bold text-gray-800 shadow-2xs flex items-center gap-2">
                            <i class="fas fa-medal text-amber-500"></i>
                            <span>{{ $se->skill_name }}</span>
                            <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200" title="Divalidasi oleh: {{ $se->teacher?->name }}">
                                ✓ Valid
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Grid Karya Legacy & Circle Projects -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- 1. Tugas Akhir SMK / Riset SMA (Dari Database Legacy) -->
            @foreach($finalProjects as $fp)
                <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
                    <div class="p-5 flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wide bg-indigo-100 text-indigo-800">
                                {{ $fp->project_type === 'project_akhir' ? 'Project Akhir SMK' : 'Penelitian Ilmiah SMA' }}
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 mb-2 leading-snug">{{ $fp->title }}</h4>
                        <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed mb-3">
                            {{ $fp->abstract ?? $fp->description ?? 'Dokumentasi hasil karya ilmiah dan praktik terapan.' }}
                        </p>
                    </div>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-500 text-[11px]"><i class="fas fa-calendar mr-1"></i>{{ $fp->created_at->format('M Y') }}</span>
                        <span class="text-emerald-700 font-bold bg-emerald-100/60 px-2 py-0.5 rounded text-[10px]">Tersertifikasi</span>
                    </div>
                </div>
            @endforeach

            <!-- 2. Portofolio PKL & Praktik Industri (Dari Database Legacy) -->
            @foreach($approvedPklLogs as $pkl)
                <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
                    @if($pkl->documentation_file)
                        <div class="h-44 w-full bg-gray-100 overflow-hidden">
                            <img src="{{ asset('storage/' . $pkl->documentation_file) }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-amber-100 text-amber-900 mb-2 inline-block">
                            <i class="fas fa-briefcase mr-1"></i>Praktik Industri DUDI
                        </span>
                        <h4 class="text-sm font-bold text-gray-900 mb-1 leading-snug">{{ $pkl->activity_description }}</h4>
                    </div>
                    <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100 text-[11px] text-gray-500 flex items-center justify-between">
                        <span>{{ \Carbon\Carbon::parse($pkl->activity_date)->isoFormat('D MMMM Y') }}</span>
                        <span class="text-emerald-600 font-semibold"><i class="fas fa-check-circle mr-1"></i>Approved Pembimbing</span>
                    </div>
                </div>
            @endforeach

            <!-- 3. Postingan Portfolio Langsung di Circle -->
            @foreach($portfolioPosts as $port)
                <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
                    @if($port->media->isNotEmpty())
                        <div class="h-48 w-full bg-black overflow-hidden">
                            @php $firstMedia = $port->media->first(); @endphp
                            @if($firstMedia->media_type === 'image')
                                <img src="{{ $firstMedia->url }}" class="w-full h-full object-cover">
                            @elseif($firstMedia->media_type === 'video')
                                <video src="{{ $firstMedia->url }}" controls class="w-full h-full object-cover"></video>
                            @endif
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-purple-100 text-purple-900 mb-2 inline-block">
                            {{ $port->post_type }}
                        </span>
                        <h4 class="text-sm font-bold text-gray-900 mb-1">{{ $port->title ?? 'Karya Unggulan' }}</h4>
                        <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed">{{ $port->content }}</p>
                    </div>
                </div>
            @endforeach

        </div>

        @if($portfolioPosts->isEmpty() && $finalProjects->isEmpty() && $approvedPklLogs->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Portofolio Sedang Disusun</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    Karya tugas akhir, dokumentasi magang, dan inovasi siswa ini akan segera dipamerkan di sini.
                </p>
            </div>
        @endif

    </div>

</div>

<!-- Modal / Toast Script untuk Share Link -->
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
