@extends('circle.layout')

@section('title', 'Beranda PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kolom Kiri & Tengah: Feed Utama -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Bar Stories 24 Jam (Instagram-Style Bubbles) -->
        @if($activeStories->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200/90 p-4 shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
                        <i class="fas fa-bolt text-amber-500"></i>
                        <span>Story Warga Pembda (24 Jam)</span>
                    </span>
                </div>
                <div class="flex items-center gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    @foreach($activeStories as $story)
                        <a href="{{ route('circle.profile', ['username' => $story->user->circleProfile->username ?? $story->user->username]) }}" 
                           class="flex flex-col items-center gap-1.5 shrink-0 group">
                            <div class="w-14 h-14 rounded-full p-0.5 bg-gradient-to-tr from-pink-500 via-purple-500 to-amber-500 shadow-md group-hover:scale-105 transition-transform">
                                <img src="{{ $story->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                     class="w-full h-full rounded-full object-cover border-2 border-white bg-white">
                            </div>
                            <span class="text-[11px] font-semibold text-gray-700 max-w-[64px] truncate text-center">
                                {{ $story->user->circleProfile->nickname ?? explode(' ', $story->user->name)[0] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Filter Tab Feed (Semua, Karya, Nongki, Reels) -->
        <div class="flex items-center justify-between bg-white rounded-2xl border border-gray-200/90 p-2 shadow-xs">
            <div class="flex items-center gap-1 text-xs font-bold">
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                   class="px-3.5 py-2 rounded-xl transition {{ $tabFilter === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="fas fa-stream mr-1.5"></i>Semua Feed
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'portfolio'])) }}" 
                   class="px-3.5 py-2 rounded-xl transition {{ $tabFilter === 'portfolio' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="fas fa-trophy mr-1.5 text-amber-300"></i>Karya & Proyek
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'wall'])) }}" 
                   class="px-3.5 py-2 rounded-xl transition {{ $tabFilter === 'wall' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="fas fa-coffee mr-1.5 text-orange-300"></i>Nongki & Seru
                </a>
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'reels'])) }}" 
                   class="px-3.5 py-2 rounded-xl transition {{ $tabFilter === 'reels' ? 'bg-pink-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="fas fa-play-circle mr-1.5"></i>Reels
                </a>
            </div>
        </div>

        <!-- Daftar Postingan Feed -->
        @if($posts->isEmpty())
            <div class="bg-white rounded-3xl border border-gray-200/90 p-12 text-center shadow-xs">
                <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-indigo-100 to-purple-100 text-indigo-600 flex items-center justify-center mx-auto mb-4 text-3xl shadow-xs">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Feed PembdaCIRCLE Siap Berlayar!</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto mb-5 leading-relaxed">
                    Jadilah yang pertama untuk membagikan karya, unjuk performa, atau sekadar cerita suasana nongki santaimu hari ini.
                </p>
                @auth
                    @php
                        $userProfile = Auth::user()->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser(Auth::user());
                    @endphp
                    <a href="{{ route('circle.profile', ['username' => $userProfile->username]) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-200 transition">
                        <i class="fas fa-pen"></i>
                        <span>Buka Profil Saya & Mulai Posting</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-200 transition">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk untuk Menulis Status</span>
                    </a>
                @endauth
            </div>
        @else
            <div class="space-y-5">
                @foreach($posts as $post)
                    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs p-5 hover:shadow-md transition">
                        <!-- Author Header -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}">
                                    <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         class="w-10 h-10 rounded-full object-cover border border-gray-200 hover:ring-2 hover:ring-indigo-500 transition">
                                </a>
                                <div>
                                    <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                                       class="text-sm font-bold text-gray-900 hover:text-indigo-600 transition flex items-center gap-1.5">
                                        <span>{{ $post->user->name }}</span>
                                        @if($post->user->role === 'guru')
                                            <i class="fas fa-check-circle text-blue-500 text-xs"></i>
                                        @endif
                                        @if($post->mood_emoji)
                                            <span class="text-xs">{{ $post->mood_emoji }}</span>
                                        @endif
                                    </a>
                                    <div class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                        <span>{{ $post->created_at->diffForHumans() }}</span>
                                        @if($post->school)
                                            <span>•</span>
                                            <span class="font-medium text-gray-600">{{ $post->school->name }}</span>
                                        @endif
                                        @if($post->location_tag)
                                            <span>•</span>
                                            <span class="text-indigo-600"><i class="fas fa-map-marker-alt text-[9px] mr-0.5"></i>{{ $post->location_tag }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $post->tab_category === 'portfolio' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ $post->tab_category === 'portfolio' ? 'Karya' : 'Nongki' }}
                            </span>
                        </div>

                        <!-- Post Title (jika ada) -->
                        @if($post->title)
                            <h4 class="text-base font-bold text-gray-900 mb-1.5">{{ $post->title }}</h4>
                        @endif

                        <!-- Post Body -->
                        <div class="text-sm text-gray-800 leading-relaxed mb-4 whitespace-pre-line">
                            {{ $post->content }}
                        </div>

                        <!-- Post Media -->
                        @if($post->media->isNotEmpty())
                            <div class="rounded-2xl overflow-hidden mb-4 bg-black max-h-[460px] flex items-center justify-center">
                                @php $m = $post->media->first(); @endphp
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" class="w-full h-auto object-cover max-h-[460px]" loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="w-full max-h-[460px]"></video>
                                @endif
                            </div>
                        @endif

                        <!-- Action Bar -->
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
                            <a href="{{ route('circle.profile', ['username' => $post->user->circleProfile->username ?? $post->user->username]) }}" 
                               class="text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                                <span>Kunjungi Profil</span>
                                <i class="fas fa-chevron-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination Links -->
                <div class="pt-4">
                    {{ $posts->links() }}
                </div>
            </div>
        @endif

    </div>

    <!-- Kolom Kanan: Widget & Trending Profiles -->
    <div class="space-y-6">

        <!-- Banner Selamat Datang -->
        <div class="bg-gradient-to-br from-indigo-700 via-purple-700 to-pink-600 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold tracking-wider bg-white/20 backdrop-blur-md uppercase inline-block mb-3">
                    ✱ The 1st Edu-Social Circle
                </span>
                <h3 class="text-lg font-black tracking-tight mb-2 leading-snug">
                    Panggung Bebas Berekspresi & Berkarya
                </h3>
                <p class="text-xs text-indigo-100 leading-relaxed mb-4">
                    Tunjukkan pesona, gaya nyentrik, suasana hati, dan mahakarya terbaikmu kepada seluruh keluarga besar Pembda dan dunia industri!
                </p>
                <div class="text-[11px] font-semibold text-white/80 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-amber-300"></i>
                    <span>Data nilai & rapor tetap privat 100% aman</span>
                </div>
            </div>
            <!-- Glow background -->
            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-pink-500/30 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Widget: Profil Terpopuler / Paling Banyak Dilihat -->
        @if($trendingProfiles->isNotEmpty())
            <div class="bg-white rounded-3xl border border-gray-200/90 p-5 shadow-xs">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2">
                    <i class="fas fa-fire text-orange-500"></i>
                    <span>Profil Paling Banyak Dikunjungi</span>
                </h3>
                <div class="space-y-3.5">
                    @foreach($trendingProfiles as $tp)
                        <a href="{{ route('circle.profile', ['username' => $tp->username]) }}" 
                           class="flex items-center justify-between p-2 rounded-2xl hover:bg-gray-50 transition group">
                            <div class="flex items-center gap-3">
                                <img src="{{ $tp->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                     class="w-10 h-10 rounded-full object-cover border border-gray-200 group-hover:ring-2 group-hover:ring-indigo-500 transition">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900 group-hover:text-indigo-600 transition flex items-center gap-1">
                                        <span>{{ $tp->nickname ?? $tp->user->name }}</span>
                                        <span class="text-[10px]">{{ $tp->mood_emoji }}</span>
                                    </h4>
                                    <span class="text-[10px] text-gray-400 block truncate max-w-[120px]">
                                        {{ $tp->user->school?->name ?? 'Perguruan Pembda' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] font-extrabold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-xl">
                                <i class="fas fa-eye mr-1 text-[9px]"></i>{{ number_format($tp->views_count) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Footer Mini -->
        <div class="px-3 text-center text-xs text-gray-400">
            <p>© {{ date('Y') }} Perguruan Pembda Gunungsitoli.</p>
            <p class="mt-1">Inovasi Ekosistem Digital PembdaHUB.</p>
        </div>

    </div>

</div>
@endsection
