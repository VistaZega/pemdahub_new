@extends('mobile.layouts.app')

@section('title', $material->title . ' - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.show', $material->course_id) }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Modul {{ $material->course->course_name ?? 'Kursus' }}
    </a>

    <!-- Material Header Clay Card -->
    <div class="clay-card p-5 space-y-2.5">
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-purple">
                {{ $material->getContentTypeLabel() }}
            </span>
            @if($material->module)
                <span class="text-[10px] text-slate-500 font-bold">
                    <i class="fa-solid fa-folder-open mr-1 text-purple-500"></i>{{ $material->module->title ?? $material->module->name }}
                </span>
            @endif
        </div>

        <h1 class="text-base font-black text-slate-900 leading-snug">{{ $material->title }}</h1>
        <p class="text-[11px] text-slate-500 font-bold flex items-center gap-1.5">
            <i class="fa-solid fa-book-open text-purple-600"></i> {{ $material->course->course_name ?? '' }}
        </p>
    </div>

    <!-- MATERIAL CONTENT VIEWER -->

    <!-- 1. VIDEO (YOUTUBE / DIRECT VIDEO) -->
    @if($material->material_type === 'video' || $material->isYouTubeVideo() || $material->isDirectVideo())
        <div class="clay-card p-4 space-y-3">
            <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-circle-play text-red-500 text-sm"></i> Pemutar Video Pembelajaran
            </h3>

            @if($material->isYouTubeVideo())
                <div class="relative w-full rounded-2xl overflow-hidden shadow-lg border-2 border-slate-900" style="padding-top: 56.25%;">
                    <iframe class="absolute top-0 left-0 w-full h-full" 
                            src="{{ $material->getVideoEmbedUrl() }}" 
                            title="{{ $material->title }}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
            @elseif($material->isDirectVideo())
                <div class="rounded-2xl overflow-hidden shadow-lg border-2 border-slate-900 bg-black">
                    <video controls class="w-full h-auto">
                        <source src="{{ asset($material->file_path ?? $material->file_url) }}">
                        Browser Anda tidak mendukung pemutar video HTML5.
                    </video>
                </div>
            @else
                <div class="p-4 bg-purple-50 rounded-2xl border-2 border-purple-200 text-center space-y-2">
                    <p class="text-xs font-bold text-slate-700">Tautan Video Pembelajaran:</p>
                    <a href="{{ $material->file_url ?? $material->file_path }}" target="_blank" class="clay-btn inline-block py-2 px-4 text-xs font-black text-white">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Buka Video di Tab Baru
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- 2. PDF & DOCUMENT VIEWER -->
    @if(in_array($material->material_type, ['pdf', 'document']) || (!empty($material->file_path) && str_ends_with(strtolower($material->file_path), '.pdf')))
        <div class="clay-card p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-red-600 text-base"></i> Berkas PDF / Dokumen
                </h3>
                @if($material->file_path || $material->file_url)
                    <a href="{{ asset($material->file_path ?? $material->file_url) }}" target="_blank" download class="px-3 py-1.5 bg-purple-600 text-white font-black text-[10px] rounded-xl shadow-sm border border-purple-800 flex items-center gap-1">
                        <i class="fa-solid fa-download"></i> Unduh Berkas
                    </a>
                @endif
            </div>

            @if(!empty($material->file_path) && str_ends_with(strtolower($material->file_path), '.pdf'))
                <div class="w-full rounded-2xl overflow-hidden border-2 border-slate-200 shadow-inner bg-slate-100" style="height: 500px;">
                    <iframe src="{{ asset($material->file_path) }}" class="w-full h-full" frameborder="0"></iframe>
                </div>
            @else
                <div class="p-4 bg-slate-50 rounded-2xl border-2 border-slate-200 text-center space-y-2">
                    <p class="text-xs font-bold text-slate-700">Berkas Dokumen Pembelajaran Siap Dibuka:</p>
                    <a href="{{ asset($material->file_path ?? $material->file_url) }}" target="_blank" class="clay-btn inline-block py-2.5 px-4 text-xs font-black text-white">
                        <i class="fa-solid fa-file-arrow-down mr-1"></i> Buka / Unduh Dokumen
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- 3. TEXT & HTML CONTENT READ VIEW -->
    @if(!empty($material->content))
        <div class="clay-card p-5 space-y-3">
            <h3 class="text-xs font-black text-slate-900 flex items-center gap-2 border-b-2 border-slate-100 pb-2">
                <i class="fa-solid fa-newspaper text-purple-600"></i> Isi Teks & Ringkasan Pembelajaran
            </h3>
            
            <div class="prose prose-slate max-w-none text-xs leading-relaxed font-medium text-slate-800 space-y-2">
                {!! $material->content !!}
            </div>
        </div>
    @endif

    <!-- 4. EXTERNAL LINK -->
    @if($material->material_type === 'link' || (!empty($material->file_url) && !$material->isYouTubeVideo()))
        <div class="clay-card p-5 text-center space-y-3 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200">
            <div class="w-12 h-12 rounded-2xl clay-blue flex items-center justify-center text-white text-xl mx-auto shadow-md">
                <i class="fa-solid fa-link"></i>
            </div>
            <div>
                <h3 class="text-xs font-black text-slate-900">Tautan Eksternal Pembelajaran</h3>
                <p class="text-[11px] text-slate-500 font-bold mt-0.5">Klik tombol di bawah untuk membuka tautan materi</p>
            </div>
            <a href="{{ $material->file_url }}" target="_blank" class="clay-btn inline-block py-3 px-6 text-xs font-black text-white shadow-md">
                <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i> Buka Tautan Eksternal
            </a>
        </div>
    @endif

    <!-- Complete Status Indicator -->
    <div class="clay-card p-4 flex items-center justify-between bg-emerald-50 border-2 border-emerald-200">
        <span class="text-xs font-black text-emerald-900 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> Status Selesai Dibaca
        </span>
        <span class="px-3 py-1 bg-emerald-600 text-white font-black text-[10px] rounded-xl shadow-sm">
            Tersimpan
        </span>
    </div>
</div>
@endsection
