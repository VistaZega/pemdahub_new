@extends('mobile.layouts.app')

@section('title', 'Surat Edaran - Informasi Sekolah Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div>
        <h2 class="text-xl font-black text-slate-900">Surat Edaran & Informasi 📜</h2>
        <p class="text-[11px] text-slate-500 font-bold">Pengumuman & Dokumen Resmi Yayasan PEMBDA</p>
    </div>

    <!-- Media & Announcements List -->
    <div class="space-y-3">
        @forelse($mediaList as $item)
            <div class="clay-card p-5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200 text-[9px] font-black uppercase">
                        {{ $item->category ?? 'Surat Edaran' }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold">
                        {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y') }}
                    </span>
                </div>

                <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $item->title }}</h3>
                @if(!empty($item->description))
                    <p class="text-[11px] text-slate-600 font-semibold line-clamp-3 leading-relaxed">
                        {{ strip_tags($item->description) }}
                    </p>
                @endif

                @if(!empty($item->file_path))
                    <div class="pt-2 border-t border-slate-100 flex justify-end">
                        <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" 
                           class="py-2 px-3 rounded-xl bg-blue-600 text-white text-[11px] font-black hover:bg-blue-700 transition flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-file-pdf text-xs"></i> Unduh / Baca Dokumen
                        </a>
                    </div>
                @endif
            </div>
        @empty
            @forelse($announcements as $anc)
                <div class="clay-card p-5 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200 text-[9px] font-black uppercase">
                            Pengumuman
                        </span>
                        <span class="text-[10px] text-slate-400 font-bold">
                            {{ \Carbon\Carbon::parse($anc->created_at)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $anc->title ?? $anc->subject ?? 'Pengumuman Resmi' }}</h3>
                    <p class="text-[11px] text-slate-600 font-semibold leading-relaxed">
                        {{ strip_tags($anc->content ?? $anc->body ?? '') }}
                    </p>
                </div>
            @empty
                <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                    <div class="text-3xl">📜</div>
                    <p>Belum ada Surat Edaran atau Pengumuman baru saat ini.</p>
                </div>
            @endforelse
        @endforelse
    </div>
</div>
@endsection
