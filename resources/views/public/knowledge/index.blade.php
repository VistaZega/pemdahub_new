@extends('layouts.app')
@section('title', 'Pembda Knowledge & Media - Repositori & Etalase Karya Guru')

@section('sidebar-menu')
    <div class="space-y-4">
        {{-- Kembali ke Dashboard Utama PembdaHUB --}}
        <div class="pb-2 border-b border-slate-200">
            @auth
                @if(auth()->user()->isGuru())
                    <a href="{{ route('guru.dashboard') }}" class="flex items-center justify-between px-3 py-2.5 bg-teal-50 hover:bg-teal-100 text-teal-800 rounded-xl text-xs font-black transition-all border border-teal-200 shadow-sm">
                        <span class="flex items-center gap-2.5"><i class="fas fa-arrow-left text-teal-600 text-sm"></i> Dashboard Guru</span>
                        <i class="fas fa-home text-teal-600"></i>
                    </a>
                @elseif(auth()->user()->isSiswa())
                    <a href="{{ route('siswa.dashboard') }}" class="flex items-center justify-between px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 rounded-xl text-xs font-black transition-all border border-indigo-200 shadow-sm">
                        <span class="flex items-center gap-2.5"><i class="fas fa-arrow-left text-indigo-600 text-sm"></i> Dashboard Siswa</span>
                        <i class="fas fa-home text-indigo-600"></i>
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 rounded-xl text-xs font-black transition-all border border-indigo-200 shadow-sm">
                        <span class="flex items-center gap-2.5"><i class="fas fa-arrow-left text-indigo-600 text-sm"></i> Admin Dashboard</span>
                        <i class="fas fa-home text-indigo-600"></i>
                    </a>
                @endif
            @else
                <a href="{{ url('/') }}" class="flex items-center justify-between px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-black transition-all border border-slate-200 shadow-sm">
                    <span class="flex items-center gap-2.5"><i class="fas fa-arrow-left text-slate-600 text-sm"></i> Beranda Utama</span>
                    <i class="fas fa-home text-slate-600"></i>
                </a>
            @endauth
        </div>

        {{-- Catalog Navigation Header --}}
        <div class="px-3 py-2 bg-gradient-to-r from-teal-600 to-indigo-600 rounded-xl text-white shadow-sm">
            <h3 class="text-xs font-black uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-cube text-teal-200"></i> Repositori Media
            </h3>
        </div>

        {{-- Menu Links (Dark text for white sidebar) --}}
        <div class="space-y-1">
            <a href="{{ route('knowledge.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold {{ !request('category') && !request('type') ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'text-slate-800 hover:bg-slate-100' }} transition-all">
                <span class="flex items-center gap-2.5"><i class="fas fa-globe text-teal-600 text-sm"></i> Semua Koleksi</span>
                <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
            <a href="{{ route('knowledge.index', ['category' => 'sekolah']) }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold {{ request('category') === 'sekolah' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'text-slate-800 hover:bg-slate-100' }} transition-all">
                <span class="flex items-center gap-2.5"><i class="fas fa-book text-indigo-600 text-sm"></i> Materi Sekolah</span>
                <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
            <a href="{{ route('knowledge.index', ['category' => 'umum']) }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold {{ request('category') === 'umum' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-slate-800 hover:bg-slate-100' }} transition-all">
                <span class="flex items-center gap-2.5"><i class="fas fa-lightbulb text-emerald-600 text-sm"></i> Umum & Hobi</span>
                <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
        </div>

        {{-- Media Types --}}
        <div class="pt-3 border-t border-slate-200 space-y-1">
            <p class="px-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Format Media</p>
            <a href="{{ route('knowledge.index', array_merge(request()->except('type'), ['type' => 'document'])) }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold {{ request('type') === 'document' ? 'bg-rose-50 text-rose-700 border border-rose-200 rounded-lg' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 rounded-lg' }}">
                <i class="fas fa-file-pdf text-rose-500 text-sm"></i> Dokumen PDF
            </a>
            <a href="{{ route('knowledge.index', array_merge(request()->except('type'), ['type' => 'video'])) }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold {{ request('type') === 'video' ? 'bg-rose-50 text-rose-700 border border-rose-200 rounded-lg' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 rounded-lg' }}">
                <i class="fas fa-play-circle text-rose-600 text-sm"></i> Video Edukasi
            </a>
            <a href="{{ route('knowledge.index', array_merge(request()->except('type'), ['type' => 'audio'])) }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold {{ request('type') === 'audio' ? 'bg-amber-50 text-amber-700 border border-amber-200 rounded-lg' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 rounded-lg' }}">
                <i class="fas fa-headphones text-amber-500 text-sm"></i> Podcast & Audio
            </a>
            <a href="{{ route('knowledge.index', array_merge(request()->except('type'), ['type' => 'link'])) }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold {{ request('type') === 'link' ? 'bg-sky-50 text-sky-700 border border-sky-200 rounded-lg' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 rounded-lg' }}">
                <i class="fas fa-globe text-sky-500 text-sm"></i> Tautan Eksternal
            </a>
        </div>

        {{-- User Role Shortcuts --}}
        @auth
            <div class="pt-3 border-t border-slate-200 space-y-1">
                <p class="px-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Ruang Kerja</p>
                @if(auth()->user()->isGuru())
                    <a href="{{ route('guru.knowledge.index') }}" class="flex items-center gap-2 px-3 py-2.5 bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-xl text-xs font-bold transition-all border border-teal-200">
                        <i class="fas fa-plus-circle"></i> Kelola Karya Guru
                    </a>
                @elseif(auth()->user()->isSuperAdmin() || auth()->user()->isAdminSekolah() || auth()->user()->isKepalaSekolah())
                    <a href="{{ route('admin.knowledge.monitoring') }}" class="flex items-center gap-2 px-3 py-2.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-bold transition-all border border-indigo-200">
                        <i class="fas fa-chart-line"></i> Pantauan Pimpinan
                    </a>
                @endif
            </div>
        @endauth
    </div>
@endsection

@section('content')
<div class="min-h-screen bg-slate-900 text-slate-100 pb-16">

    {{-- Hero Section --}}
    <div class="relative overflow-hidden pt-12 pb-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-teal-950 via-slate-900 to-slate-900 border-b border-slate-800">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[300px] bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-6xl mx-auto relative z-10 text-center space-y-6">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-teal-500/10 border border-teal-500/30 rounded-full text-teal-300 text-xs font-bold uppercase tracking-wider">
                <i class="fas fa-book-open text-teal-400"></i> Jurnal & Repositori Publikasi Civitas Akademika
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Jurnal & Publikasi <span class="bg-gradient-to-r from-teal-400 via-emerald-300 to-sky-400 bg-clip-text text-transparent">Perguruan Pembda</span>
            </h1>

            <p class="text-slate-300 max-w-3xl mx-auto text-sm sm:text-base leading-relaxed">
                Etalase terbuka repositori karya tulis ilmiah, jurnal, modul digital, artikel edukasi, hingga karya media civitas akademika Perguruan Pembda. Bebas dibaca dan diunduh oleh siswa dan publik.
            </p>

            {{-- Template Download Callout & Search Bar --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('knowledge.download_template') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-teal-300 text-xs font-bold rounded-xl border border-teal-500/30 shadow-lg transition-all flex items-center gap-2">
                    <i class="fas fa-file-download text-teal-400"></i> Unduh Template Jurnal Resmi (Opsional)
                </a>
            </div>

            <form action="{{ route('knowledge.index') }}" method="GET" class="max-w-2xl mx-auto flex items-center gap-2 p-2 bg-slate-800/90 backdrop-blur-xl border border-slate-700/80 rounded-2xl shadow-2xl">
                <div class="pl-3 text-slate-400">
                    <i class="fas fa-search text-lg"></i>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul jurnal, penulis, atau kata kunci..." class="flex-1 bg-transparent border-0 px-2 text-white placeholder-slate-400 focus:outline-none focus:ring-0 text-sm sm:text-base">
                <button type="submit" class="px-5 py-3 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg transition-all text-sm">
                    Cari Jurnal
                </button>
            </form>
        </div>
    </div>

    {{-- Filter Toolbar & Categories --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        {{-- Filter Pills & Selects --}}
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 bg-slate-800/70 p-4 rounded-2xl border border-slate-700/80 shadow-lg">
            {{-- Category Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 lg:pb-0 scrollbar-none">
                <a href="{{ route('knowledge.index', request()->except(['category', 'type'])) }}"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ !request('category') ? 'bg-teal-500 text-slate-950 shadow-lg shadow-teal-500/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
                    <i class="fas fa-globe mr-1"></i> Semua Koleksi
                </a>
                <a href="{{ route('knowledge.index', array_merge(request()->except(['category', 'type']), ['category' => 'sekolah'])) }}"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ request('category') === 'sekolah' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
                    📚 Materi Sekolah
                </a>
                <a href="{{ route('knowledge.index', array_merge(request()->except(['category', 'type']), ['category' => 'umum'])) }}"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ request('category') === 'umum' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
                    💡 Umum & Hobi
                </a>
            </div>

            {{-- Dropdown Filters --}}
            <form action="{{ route('knowledge.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                {{-- Type Filter --}}
                <select name="type" onchange="this.form.submit()" class="bg-slate-800 text-slate-300 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-700 focus:outline-none focus:border-teal-500">
                    <option value="">Semua Format Media</option>
                    <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>📄 Dokumen (PDF)</option>
                    <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>🎬 Video</option>
                    <option value="audio" {{ request('type') === 'audio' ? 'selected' : '' }}>🎧 Audio / Podcast</option>
                    <option value="link" {{ request('type') === 'link' ? 'selected' : '' }}>🔗 Link / Web</option>
                </select>

                {{-- Subject Filter --}}
                <select name="subject_id" onchange="this.form.submit()" class="bg-slate-800 text-slate-300 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-700 focus:outline-none focus:border-teal-500">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->subject_name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Content Grid --}}
        @if($materials->isEmpty())
            <div class="text-center py-16 space-y-4 bg-slate-800/40 rounded-3xl border border-slate-800">
                <div class="w-20 h-20 bg-slate-800 text-slate-400 rounded-full flex items-center justify-center text-4xl mx-auto border border-slate-700">
                    <i class="fas fa-folder-open text-amber-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-200">Belum ada materi ditemukan</h3>
                @if(request('type') || request('category') || request('q') || request('subject_id'))
                    <p class="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
                        Anda sedang memfilter 
                        @if(request('category')) <span class="font-bold text-emerald-400">Kategori: {{ request('category') }}</span> @endif
                        @if(request('type')) · <span class="font-bold text-sky-400">Format Media: {{ request('type') }}</span> @endif.
                        <br>Materi yang dicari mungkin tersimpan dalam format media lain (misalnya PDF/Dokumen).
                    </p>
                    <div class="flex items-center justify-center gap-2 pt-2">
                        <a href="{{ route('knowledge.index', request()->except('type')) }}" class="inline-block px-4 py-2 bg-teal-500 hover:bg-teal-400 text-slate-950 text-xs font-bold rounded-xl shadow-md transition-all">
                            <i class="fas fa-globe mr-1"></i> Tampilkan Semua Format Media
                        </a>
                        <a href="{{ route('knowledge.index') }}" class="inline-block px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700">
                            Reset Filter
                        </a>
                    </div>
                @else
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Belum ada koleksi materi yang dipublikasikan.</p>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($materials as $item)
                    <div class="bg-slate-800/70 rounded-2xl border border-slate-700/80 overflow-hidden flex flex-col hover:border-teal-500/60 hover:shadow-2xl hover:shadow-teal-950/40 transition-all duration-300 group">
                        
                        {{-- Thumbnail / Media Preview Card Header --}}
                        <div class="relative h-48 bg-slate-950 flex items-center justify-center overflow-hidden">
                            @if($item->thumbnail_url)
                                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="text-center p-4">
                                    @if($item->type === 'video')
                                        <i class="fas fa-file-video text-6xl text-rose-500/80 group-hover:scale-110 transition-transform"></i>
                                    @elseif($item->type === 'audio')
                                        <i class="fas fa-headphones text-6xl text-amber-500/80 group-hover:scale-110 transition-transform"></i>
                                    @elseif($item->type === 'link')
                                        <i class="fas fa-link text-6xl text-sky-500/80 group-hover:scale-110 transition-transform"></i>
                                    @else
                                        <i class="fas fa-file-pdf text-6xl text-teal-500/80 group-hover:scale-110 transition-transform"></i>
                                    @endif
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-3 left-3 flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider text-white shadow-lg backdrop-blur-md
                                    {{ $item->category_type === 'sekolah' ? 'bg-indigo-600/90' : 'bg-emerald-600/90' }}">
                                    {{ $item->category_type }}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-900/90 text-slate-200 shadow-lg backdrop-blur-md capitalize flex items-center gap-1">
                                    @if($item->type === 'video') <i class="fas fa-play text-rose-400"></i>
                                    @elseif($item->type === 'audio') <i class="fas fa-headphones text-amber-400"></i>
                                    @elseif($item->type === 'link') <i class="fas fa-globe text-sky-400"></i>
                                    @else <i class="fas fa-file-alt text-teal-400"></i>
                                    @endif
                                    {{ $item->type }}
                                </span>
                            </div>

                            {{-- Direct Play / Open Button Overlay --}}
                            <a href="{{ route('knowledge.show', $item->slug) }}" class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="px-4 py-2 bg-teal-500 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg transform -translate-y-2 group-hover:translate-y-0 transition-all flex items-center gap-2">
                                    <i class="fas fa-eye"></i> Buka Materi
                                </span>
                            </a>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-2">
                                @if($item->subject)
                                    <p class="text-[11px] font-extrabold text-teal-400 uppercase tracking-wider">{{ $item->subject->subject_name }}</p>
                                @endif

                                <h3 class="text-base font-bold text-white line-clamp-2 hover:text-teal-300 transition-colors">
                                    <a href="{{ route('knowledge.show', $item->slug) }}">{{ $item->title }}</a>
                                </h3>

                                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $item->description ?? 'Materi edukasi dan koleksi pembelajaran.' }}</p>
                            </div>

                            {{-- Author Info & 4 Complete Indicators (Views, Likes, Bookmarks, Downloads) --}}
                            <div class="pt-4 border-t border-slate-700/60 flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $item->teacher->photo_url ?? asset('images/default-student.jpg') }}" class="w-7 h-7 rounded-full object-cover border border-slate-600">
                                        <span class="text-xs text-slate-300 font-semibold truncate max-w-[140px]">{{ $item->teacher->full_name ?? 'Guru Pembda' }}</span>
                                    </div>
                                </div>

                                {{-- 4 Interaction Metrics Badge Bar --}}
                                <div class="grid grid-cols-4 gap-1 p-2 bg-slate-900/90 rounded-xl border border-slate-700/60 text-center text-[10px]">
                                    <div title="Views / Dilihat">
                                        <span class="text-slate-400 block text-[9px]">Views</span>
                                        <span class="font-extrabold text-sky-400"><i class="fas fa-eye text-[9px]"></i> {{ number_format($item->views_count) }}</span>
                                    </div>
                                    <div title="Likes / Disukai">
                                        <span class="text-slate-400 block text-[9px]">Suka</span>
                                        <span class="font-extrabold text-rose-400"><i class="fas fa-heart text-[9px]"></i> {{ number_format($item->likes_count) }}</span>
                                    </div>
                                    <div title="Bookmarks / Disimpan">
                                        <span class="text-slate-400 block text-[9px]">Simpan</span>
                                        <span class="font-extrabold text-indigo-400"><i class="fas fa-bookmark text-[9px]"></i> {{ number_format($item->bookmarks_count) }}</span>
                                    </div>
                                    <div title="Downloads / Diunduh">
                                        <span class="text-slate-400 block text-[9px]">Unduh</span>
                                        <span class="font-extrabold text-emerald-400"><i class="fas fa-download text-[9px]"></i> {{ number_format($item->downloads_count) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-6">
                {{ $materials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
