@extends('layouts.guru')
@section('title', 'Pembda Knowledge & Media - Ruang Karya Guru')

@section('content')
<div class="space-y-6">

    {{-- Hero Header & Stat Summary (Explicit CSS Gradient for 100% Reliable Dark Background & High Contrast) --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 text-white shadow-xl" style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #1e1b4b 100%);">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full border border-white/20 text-emerald-200 text-xs font-bold uppercase tracking-wider">
                    <i class="fas fa-book-open text-amber-300"></i> Ruang Publikasi & Jurnal Civitas Akademika
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Jurnal & Repositori Perguruan Pembda</h1>
                <p class="text-emerald-100 text-sm max-w-xl leading-relaxed">
                    Wadah publikasi resmi jurnal ilmiah, artikel edukasi, modul digital, dan karya media Civitas Akademika Perguruan Pembda yang dapat diakses publik & siswa.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <a href="{{ route('knowledge.download_template') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-3 rounded-xl border border-white/20 shadow-md transition-all text-xs">
                    <i class="fas fa-file-download text-amber-300"></i>
                    <span>Template Jurnal (Opsional)</span>
                </a>
                <a href="{{ route('guru.knowledge.create') }}" class="inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black px-5 py-3 rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5 text-sm">
                    <i class="fas fa-plus-circle text-lg"></i>
                    <span>Publikasikan Karya Baru</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards (High Contrast & Sharp Colors) --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total Points Card --}}
        <div class="rounded-2xl p-4 text-white shadow-md flex items-center justify-between" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
            <div>
                <p class="text-xs font-bold text-amber-100 uppercase tracking-wider">Total Poin Karya</p>
                <h3 class="text-2xl font-black mt-1 text-white">{{ number_format($totalPoints) }}</h3>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-xl text-white backdrop-blur-md">
                <i class="fas fa-trophy"></i>
            </div>
        </div>

        {{-- Total Uploads --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Materi Terunggah</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalUploads }}</h3>
            </div>
            <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center text-lg font-bold border border-emerald-200">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>

        {{-- Likes --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Total Disukai</p>
                <h3 class="text-2xl font-black text-rose-600 mt-1">{{ $totalLikes }}</h3>
            </div>
            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-lg font-bold border border-rose-200">
                <i class="fas fa-heart"></i>
            </div>
        </div>

        {{-- Bookmarks --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Disimpan Favorit</p>
                <h3 class="text-2xl font-black text-indigo-600 mt-1">{{ $totalBookmarks }}</h3>
            </div>
            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-lg font-bold border border-indigo-200">
                <i class="fas fa-bookmark"></i>
            </div>
        </div>

        {{-- Views --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Total Pembaca</p>
                <h3 class="text-2xl font-black text-sky-600 mt-1">{{ number_format($totalViews) }}</h3>
            </div>
            <div class="w-10 h-10 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center text-lg font-bold border border-sky-200">
                <i class="fas fa-eye"></i>
            </div>
        </div>
    </div>

    {{-- Alert Success --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-900 rounded-xl text-sm font-bold flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Content List Grid --}}
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-layer-group text-teal-600"></i> Daftar Koleksi & Materi Anda
            </h2>
            <a href="{{ route('knowledge.index') }}" target="_blank" class="text-xs font-black text-teal-700 hover:text-teal-800 flex items-center gap-1 bg-teal-50 px-3 py-1.5 rounded-lg border border-teal-200">
                <span>Lihat Etalase Publik</span> <i class="fas fa-external-link-alt text-[10px]"></i>
            </a>
        </div>

        @if($materials->isEmpty())
            <div class="text-center py-12 text-slate-600 space-y-3">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-3xl mx-auto border border-slate-200">
                    <i class="fas fa-folder-plus"></i>
                </div>
                <p class="text-base font-bold text-slate-700">Belum ada materi atau karya yang Anda unggah.</p>
                <a href="{{ route('guru.knowledge.create') }}" class="inline-flex items-center gap-2 text-sm bg-teal-600 hover:bg-teal-700 text-white font-bold px-4 py-2.5 rounded-xl shadow-md transition-all">
                    <i class="fas fa-plus"></i> Unggah Materi Pertama
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($materials as $item)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col hover:border-teal-400 hover:shadow-lg transition-all">
                        {{-- Header / Thumbnail Badge --}}
                        <div class="relative h-40 bg-slate-900 flex items-center justify-center overflow-hidden">
                            @if($item->thumbnail_url)
                                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="text-center text-slate-400 p-4">
                                    @if($item->type === 'video')
                                        <i class="fas fa-file-video text-5xl text-rose-400"></i>
                                    @elseif($item->type === 'audio')
                                        <i class="fas fa-file-audio text-5xl text-amber-400"></i>
                                    @elseif($item->type === 'link')
                                        <i class="fas fa-link text-5xl text-sky-400"></i>
                                    @else
                                        <i class="fas fa-file-pdf text-5xl text-teal-400"></i>
                                    @endif
                                </div>
                            @endif

                            {{-- Type & Category Badges --}}
                            <div class="absolute top-3 left-3 flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black text-white shadow-md backdrop-blur-md uppercase tracking-wider
                                    {{ $item->category_type === 'sekolah' ? 'bg-indigo-600' : 'bg-emerald-600' }}">
                                    {{ $item->category_type }}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-900/90 text-slate-200 shadow-md backdrop-blur-md capitalize flex items-center gap-1">
                                    @if($item->type === 'video') <i class="fas fa-play text-rose-400"></i>
                                    @elseif($item->type === 'audio') <i class="fas fa-headphones text-amber-400"></i>
                                    @elseif($item->type === 'link') <i class="fas fa-globe text-sky-400"></i>
                                    @else <i class="fas fa-file-alt text-teal-400"></i>
                                    @endif
                                    {{ $item->type }}
                                </span>
                            </div>

                            {{-- Visibility Badge --}}
                            <div class="absolute top-3 right-3">
                                @if($item->is_public)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-500 text-white shadow" title="Dapat diakses Publik">
                                        <i class="fas fa-globe-asia"></i> Publik
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-700 text-white shadow" title="Private">
                                        <i class="fas fa-lock"></i> Privat
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-2">
                                @if($item->subject)
                                    <p class="text-xs font-black text-teal-700 uppercase tracking-wider">{{ $item->subject->subject_name }}</p>
                                @endif
                                <h3 class="text-base font-black text-slate-900 line-clamp-2 hover:text-teal-700 transition-colors">
                                    <a href="{{ route('knowledge.show', $item->slug) }}" target="_blank">{{ $item->title }}</a>
                                </h3>
                                <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $item->description ?? 'Tidak ada deskripsi.' }}</p>
                            </div>

                            {{-- Stats Footer --}}
                            <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-700 font-semibold">
                                <div class="flex items-center gap-3">
                                    <span title="Views / Dilihat"><i class="fas fa-eye text-sky-600"></i> {{ number_format($item->views_count) }}</span>
                                    <span title="Likes / Disukai"><i class="fas fa-heart text-rose-600"></i> {{ number_format($item->likes_count) }}</span>
                                    <span title="Bookmarks / Disimpan"><i class="fas fa-bookmark text-indigo-600"></i> {{ number_format($item->bookmarks_count) }}</span>
                                    <span title="Downloads / Diunduh"><i class="fas fa-download text-emerald-600"></i> {{ number_format($item->downloads_count) }}</span>
                                </div>
                                <span class="font-black text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md border border-amber-300">
                                    +{{ $item->points }} Poin
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <a href="{{ route('knowledge.show', $item->slug) }}" target="_blank" class="text-xs font-black text-slate-700 hover:text-teal-700 flex items-center gap-1">
                                    <i class="fas fa-external-link-alt"></i> Pratinjau
                                </a>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('guru.knowledge.edit', $item->id) }}" class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold rounded-lg border border-amber-300 transition-colors text-xs flex items-center gap-1" title="Edit Materi">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('guru.knowledge.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-900 font-bold rounded-lg border border-rose-300 transition-colors text-xs flex items-center gap-1" title="Hapus Materi">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $materials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
